<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\Xuxes;

/**
 * Servei d'inventari.
 *
 * Reservat per a la inicialització de l'inventari en el registre d'usuaris.
 * Actualment no assigna ítems inicials: les Xuxes s'obtenen únicament
 * a través de les recompenses diàries (DailyRewardService).
 */
class InventarioService
{
    // Comprova si l'usuari ja té inventari. Si no, no afegeix res (les xuxes es guanyen per recompenses)
    public function ensureStarterInventario(int $userId): bool
    {
        $existingEntries = Inventario::where('user_id', $userId)->exists();

        if ($existingEntries) {
            return true;
        }

        $xuxes = Xuxes::all();

        if ($xuxes->isEmpty()) {
            return false;
        }

        // No assignem xuxes aleatòries a l'hora del registre
        // S'aconsegueixen només amb recompenses diàries.

        return true;
    }
}
