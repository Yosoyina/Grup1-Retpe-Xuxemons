<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\User;
use App\Models\Xuxes;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DailyRewardService
{
    private const REWARD_HOUR = 8;
    private const REWARD_TIMEZONE = 'Europe/Madrid';
    private const DAILY_XUXES = 10;

    public function __construct(private XuxedexService $xuxedexService)
    {
    }

    public function claimFor(User $user): array
    {
        $now = Carbon::now(self::REWARD_TIMEZONE);
        $availableAt = $now->copy()->startOfDay()->setHour(self::REWARD_HOUR);

        if ($now->lt($availableAt)) {
            return [
                'status' => 'not_available_yet',
                'granted' => false,
                'message' => 'La recompensa diaria estará disponible a las 08:00.',
                'available_at' => $availableAt->toIso8601String(),
                'next_available_at' => $availableAt->toIso8601String(),
            ];
        }

        $lastRewardAt = $user->last_reward_at?->copy()->timezone(self::REWARD_TIMEZONE);

        if ($lastRewardAt && $lastRewardAt->greaterThanOrEqualTo($availableAt)) {
            return [
                'status' => 'already_claimed',
                'granted' => false,
                'message' => 'La recompensa diaria de hoy ya fue recibida.',
                'available_at' => $availableAt->toIso8601String(),
                'next_available_at' => $availableAt->copy()->addDay()->toIso8601String(),
            ];
        }

        return DB::transaction(function () use ($user, $now, $availableAt) {
            $rewardXuxes = $this->buildRandomXuxesReward();
            $xuxesSummary = $this->storeXuxesReward($user->id, $rewardXuxes);

            $this->xuxedexService->ensureStarterXuxedex($user->id);
            $xuxemonReward = $this->unlockRandomSmallXuxemon($user->id);

            $user->forceFill([
                'last_reward_at' => $now->copy()->setTimezone('UTC'),
            ])->save();

            return [
                'status' => 'granted',
                'granted' => true,
                'message' => 'Has recibido tu recompensa diaria.',
                'available_at' => $availableAt->toIso8601String(),
                'next_available_at' => $availableAt->copy()->addDay()->toIso8601String(),
                'xuxes' => $xuxesSummary['items'],
                'xuxes_requested' => self::DAILY_XUXES,
                'xuxes_added' => $xuxesSummary['added'],
                'xuxes_discarded' => $xuxesSummary['discarded'],
                'xuxemon' => $xuxemonReward,
                'xuxemon_unlocked' => $xuxemonReward !== null,
            ];
        });
    }

    private function buildRandomXuxesReward(): Collection
    {
        $catalog = Xuxes::query()->get(['id', 'nombre_xuxes', 'imagen', 'apilable']);

        if ($catalog->isEmpty()) {
            return collect();
        }

        return collect(range(1, self::DAILY_XUXES))
            ->map(fn () => $catalog->random())
            ->groupBy('id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'id' => $first->id,
                    'nombre_xuxes' => $first->nombre_xuxes,
                    'imagen' => $first->imagen,
                    'apilable' => (bool) $first->apilable,
                    'cantidad' => $items->count(),
                ];
            })
            ->values();
    }

    private function storeXuxesReward(int $userId, Collection $rewardXuxes): array
    {
        $items = [];
        $totalAdded = 0;
        $totalDiscarded = 0;

        foreach ($rewardXuxes as $rewardItem) {
            $result = $this->addItemToInventory($userId, $rewardItem['id'], $rewardItem['cantidad']);

            $items[] = [
                'id' => $rewardItem['id'],
                'nombre_xuxes' => $rewardItem['nombre_xuxes'],
                'imagen' => $rewardItem['imagen'],
                'cantidad' => $rewardItem['cantidad'],
                'added' => $result['added'],
                'discarded' => $result['discarded'],
            ];

            $totalAdded += $result['added'];
            $totalDiscarded += $result['discarded'];
        }

        return [
            'items' => $items,
            'added' => $totalAdded,
            'discarded' => $totalDiscarded,
        ];
    }

    private function addItemToInventory(int $userId, int $xuxeId, int $cantidad): array
    {
        $xuxe = Xuxes::findOrFail($xuxeId);
        $pending = $cantidad;

        if ($xuxe->apilable) {
            $existingItems = Inventario::where('user_id', $userId)
                ->where('xuxe_id', $xuxeId)
                ->where('cantidad', '<', Inventario::MAX_STACK)
                ->get();

            foreach ($existingItems as $item) {
                if ($pending <= 0) {
                    break;
                }

                $space = Inventario::MAX_STACK - $item->cantidad;
                $toAdd = min($pending, $space);
                $item->cantidad += $toAdd;
                $item->save();
                $pending -= $toAdd;
            }

            while ($pending > 0 && Inventario::slotsUtilizados($userId) < Inventario::MAX_SLOTS) {
                $toAdd = min($pending, Inventario::MAX_STACK);

                Inventario::create([
                    'user_id' => $userId,
                    'xuxe_id' => $xuxeId,
                    'cantidad' => $toAdd,
                ]);

                $pending -= $toAdd;
            }
        } else {
            while ($pending > 0 && Inventario::slotsUtilizados($userId) < Inventario::MAX_SLOTS) {
                Inventario::create([
                    'user_id' => $userId,
                    'xuxe_id' => $xuxeId,
                    'cantidad' => 1,
                ]);

                $pending--;
            }
        }

        return [
            'added' => $cantidad - $pending,
            'discarded' => $pending,
        ];
    }

    private function unlockRandomSmallXuxemon(int $userId): ?array
    {
        $blockedEntry = DB::table('xuxedex')
            ->join('xuxemons', 'xuxedex.id_xuxemon', '=', 'xuxemons.id')
            ->where('id_usuario', $userId)
            ->where('esta_capturado', false)
            ->where('xuxemons.tamano', 'Petit')
            ->inRandomOrder()
            ->select(
                'xuxedex.id_xuxemon',
                'xuxemons.id',
                'xuxemons.nombre_xuxemon',
                'xuxemons.tipo_elemento',
                'xuxemons.tamano',
                'xuxemons.descripcio',
                'xuxemons.imagen'
            )
            ->first();

        if (!$blockedEntry) {
            return null;
        }

        DB::table('xuxedex')
            ->where('id_usuario', $userId)
            ->where('id_xuxemon', $blockedEntry->id_xuxemon)
            ->update([
                'esta_capturado' => true,
                'updated_at' => now(),
            ]);

        return [
            'id' => $blockedEntry->id,
            'nombre_xuxemon' => $blockedEntry->nombre_xuxemon,
            'tipo_elemento' => $blockedEntry->tipo_elemento,
            'tamano' => $blockedEntry->tamano,
            'descripcio' => $blockedEntry->descripcio,
            'imagen' => $blockedEntry->imagen,
        ];
    }
}
