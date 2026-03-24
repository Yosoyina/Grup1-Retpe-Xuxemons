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

        foreach ($xuxes as $xuxa) {
            Inventario::create([
                'user_id'  => $userId,
                'xuxe_id'  => $xuxa->id,
                'cantidad' => $xuxa->apilable ? rand(1, 5) : 1,
            ]);
        }

        return true;
    }
}
