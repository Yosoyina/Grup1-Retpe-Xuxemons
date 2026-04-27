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
 * Servei de recompenses diàries.
 *
 * Gestiona la lògica de les dues recompenses diàries de l'usuari:
 *   1. Xuxes: un lot aleatori de llaminadures de l'inventari.
 *   2. Xuxemon: desbloqueja un Xuxemon Petit aleatori del Xuxedex.
 *
 * Les hores de disponibilitat es llegeixen de SystemConfig.
 * Cada recompensa té el seu propi timestamp per ser independent.
 */
class DailyRewardService
{
    private const REWARD_TIMEZONE = 'Europe/Madrid';

    public function __construct(private XuxedexService $xuxedexService)
    {
    }

    // Comprova si hi ha recompenses disponibles i les atorga a l'usuari
    public function claimFor(User $user): array
    {
        $now = Carbon::now(self::REWARD_TIMEZONE);
        
        $xuxesHour = (int) SystemConfig::get('xuxes_hora_recompensa', 8);
        $xuxemonHour = (int) SystemConfig::get('xuxemon_hora_recompensa', 8);
        
        $xuxesAvailableAt = $now->copy()->startOfDay()->setHour($xuxesHour);
        if ($now->lessThan($xuxesAvailableAt)) {
            $xuxesAvailableAt->subDay();
        }
        $xuxemonAvailableAt = $now->copy()->startOfDay()->setHour($xuxemonHour);
        if ($now->lessThan($xuxemonAvailableAt)) {
            $xuxemonAvailableAt->subDay();
        }

        $lastXuxesRewardAt = $user->ultima_recompensa_at?->copy()->timezone(self::REWARD_TIMEZONE);
        $lastXuxemonRewardAt = $user->ultima_recompensa_xuxemon_at?->copy()->timezone(self::REWARD_TIMEZONE);

        $canClaimXuxes = !$lastXuxesRewardAt || $lastXuxesRewardAt->lessThan($xuxesAvailableAt);
                         
        $canClaimXuxemon = !$lastXuxemonRewardAt || $lastXuxemonRewardAt->lessThan($xuxemonAvailableAt);

        if (!$canClaimXuxes && !$canClaimXuxemon) {
            return [
                'status' => 'not_available_yet',
                'granted' => false,
                'message' => 'No hay recompensas disponibles en este momento.'
            ];
        }

        return DB::transaction(function () use ($user, $now, $canClaimXuxes, $canClaimXuxemon) {
            $response = [
                'status' => 'granted',
                'granted' => true,
                'message' => 'Has recibido recompensas diarias.'
            ];

            if ($canClaimXuxes) {
                $dailyXuxes = (int) SystemConfig::get('xuxes_quantitat_diaria', 10);
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

            $user->save();

            return $response;
        });
    }

    // Genera una col·lecció aleatòria de Xuxes del catàleg fins arribar al total diari
    private function buildRandomXuxesReward(int $totalXuxes = 10): Collection
    {
        $catalog = Xuxes::query()->get(['id', 'nombre_xuxes', 'imagen', 'apilable']);
        if ($catalog->isEmpty()) {
            return collect();
        }

        return collect(range(1, $totalXuxes))
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

    // Afegeix al inventari les Xuxes guanyades i retorna un resum d'añadides i descartades
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

    // Afegeix un ítem a l'inventari respectant MAX_STACK i MAX_SLOTS. Retorna les unitats añadides i descartades
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

    // Desbloqueja un Xuxemon Petit aleatori no capturat de l'usuari. Retorna null si ja els té tots
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
