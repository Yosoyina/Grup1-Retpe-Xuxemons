<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\Xuxes;

class InventarioService
{
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
