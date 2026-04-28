<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\SystemConfig;
use App\Models\User;
use App\Models\Xuxes;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de recompensas diarias.
 *
 * Mantiene una respuesta simple para el frontend:
 * - Reparte como maximo 5 xuxes distintas.
 * - Puede devolver el ultimo resumen guardado aunque hoy ya este reclamado.
 */
class DailyRewardService
{
    private const REWARD_TIMEZONE = 'Europe/Madrid';
    private const MAX_REWARD_ITEMS = 5;

    public function __construct(private XuxedexService $xuxedexService)
    {
    }

    public function claimFor(User $user): array
    {
        $now = Carbon::now(self::REWARD_TIMEZONE);

        $xuxesHour = (int) SystemConfig::get('xuxes_hora_recompensa', 8);
        $xuxemonHour = (int) SystemConfig::get('xuxemon_hora_recompensa', 8);

        $xuxesAvailableAt = $this->resolveCurrentWindowStart($now, $xuxesHour);
        $xuxemonAvailableAt = $this->resolveCurrentWindowStart($now, $xuxemonHour);

        $lastXuxesRewardAt = $user->ultima_recompensa_at?->copy()->timezone(self::REWARD_TIMEZONE);
        $lastXuxemonRewardAt = $user->ultima_recompensa_xuxemon_at?->copy()->timezone(self::REWARD_TIMEZONE);

        $canClaimXuxes = !$lastXuxesRewardAt || $lastXuxesRewardAt->lessThan($xuxesAvailableAt);
        $canClaimXuxemon = !$lastXuxemonRewardAt || $lastXuxemonRewardAt->lessThan($xuxemonAvailableAt);

        $baseResponse = $this->buildBaseResponse($user, $xuxesAvailableAt, $xuxemonAvailableAt, $now);

        if (!$canClaimXuxes && !$canClaimXuxemon) {
            return array_merge($baseResponse, [
                'status' => 'not_available_yet',
                'granted' => false,
                'message' => 'Ya has reclamado la recompensa de hoy.',
            ]);
        }

        return DB::transaction(function () use ($user, $now, $canClaimXuxes, $canClaimXuxemon, $baseResponse) {
            $response = array_merge($baseResponse, [
                'status' => 'granted',
                'granted' => true,
                'message' => 'Has recibido la recompensa diaria.',
            ]);

            if ($canClaimXuxes) {
                $dailyXuxes = max(
                    1,
                    (int) SystemConfig::get('xuxes_quantitat_diaria', 10)
                );

                $rewardXuxes = $this->buildRandomXuxesReward($dailyXuxes);
                $xuxesSummary = $this->storeXuxesReward($user->id, $rewardXuxes);

                $response['xuxes'] = $xuxesSummary['items'];
                $response['xuxes_requested'] = $dailyXuxes;
                $response['xuxes_added'] = $xuxesSummary['added'];
                $response['xuxes_discarded'] = $xuxesSummary['discarded'];

                $user->ultima_recompensa_at = $now->copy()->setTimezone('UTC');
            }

            if ($canClaimXuxemon) {
                $this->xuxedexService->ensureStarterXuxedex($user->id);
                $xuxemonReward = $this->unlockRandomSmallXuxemon($user->id);

                $response['xuxemon'] = $xuxemonReward;
                $response['xuxemon_unlocked'] = $xuxemonReward !== null;

                $user->ultima_recompensa_xuxemon_at = $now->copy()->setTimezone('UTC');
            }

            $user->last_reward_summary = $this->extractRewardSummary($response);
            $user->save();

            return $response;
        });
    }

    private function resolveCurrentWindowStart(Carbon $now, int $hour): Carbon
    {
        $availableAt = $now->copy()->startOfDay()->setHour($hour);

        if ($now->lessThan($availableAt)) {
            $availableAt->subDay();
        }

        return $availableAt;
    }

    private function buildRandomXuxesReward(int $totalXuxes): Collection
    {
        $catalog = Xuxes::query()->get(['id', 'nombre_xuxes', 'imagen', 'apilable']);
        if ($catalog->isEmpty()) {
            return collect();
        }

        $rewardSize = min($totalXuxes, self::MAX_REWARD_ITEMS, $catalog->count());
        $selectedItems = $catalog->shuffle()->take($rewardSize)->values();
        $remainingXuxes = $totalXuxes;

        return $selectedItems
            ->map(function ($item, $index) use ($selectedItems, &$remainingXuxes) {
                $remainingSlots = $selectedItems->count() - $index;
                $cantidad = $remainingSlots === 1
                    ? $remainingXuxes
                    : random_int(1, $remainingXuxes - ($remainingSlots - 1));

                $remainingXuxes -= $cantidad;

                return [
                    'id' => $item->id,
                    'nombre_xuxes' => $item->nombre_xuxes,
                    'imagen' => $item->imagen,
                    'apilable' => (bool) $item->apilable,
                    'cantidad' => $cantidad,
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

    private function buildBaseResponse(User $user, Carbon $xuxesAvailableAt, Carbon $xuxemonAvailableAt, Carbon $now): array
    {
        $lastRewardSummary = $user->last_reward_summary ?? [];

        return [
            'status' => 'already_claimed',
            'granted' => false,
            'message' => 'No hay recompensas disponibles en este momento.',
            'available_at' => $xuxesAvailableAt->toIso8601String(),
            'next_available_at' => $this->resolveNextRewardAt($xuxesAvailableAt, $xuxemonAvailableAt, $now)->toIso8601String(),
            'xuxes' => $lastRewardSummary['xuxes'] ?? [],
            'xuxes_requested' => $lastRewardSummary['xuxes_requested'] ?? 0,
            'xuxes_added' => $lastRewardSummary['xuxes_added'] ?? 0,
            'xuxes_discarded' => $lastRewardSummary['xuxes_discarded'] ?? 0,
            'xuxemon' => $lastRewardSummary['xuxemon'] ?? null,
            'xuxemon_unlocked' => $lastRewardSummary['xuxemon_unlocked'] ?? false,
        ];
    }

    private function resolveNextRewardAt(Carbon $xuxesAvailableAt, Carbon $xuxemonAvailableAt, Carbon $now): Carbon
    {
        $nextXuxesAt = $xuxesAvailableAt->copy()->addDay();
        $nextXuxemonAt = $xuxemonAvailableAt->copy()->addDay();

        return $nextXuxesAt->lessThan($nextXuxemonAt) ? $nextXuxesAt : $nextXuxemonAt;
    }

    private function extractRewardSummary(array $response): array
    {
        return [
            'xuxes' => $response['xuxes'] ?? [],
            'xuxes_requested' => $response['xuxes_requested'] ?? 0,
            'xuxes_added' => $response['xuxes_added'] ?? 0,
            'xuxes_discarded' => $response['xuxes_discarded'] ?? 0,
            'xuxemon' => $response['xuxemon'] ?? null,
            'xuxemon_unlocked' => $response['xuxemon_unlocked'] ?? false,
        ];
    }
}
