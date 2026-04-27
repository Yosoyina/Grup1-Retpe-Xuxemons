<?php

namespace App\Services;

use App\Models\Xuxemons;
use Illuminate\Support\Facades\DB;

/**
 * Servei del Xuxedex.
 *
 * S'encarrega d'inicialitzar el Xuxedex d'un usuari nou.
 * En registrar-se, s'afegeixen al Xuxedex tots els Xuxemons Petits
 * de cada tipus (Aigua/Terra/Aire) en estat 'no capturat'.
 * Els Xuxemons Mitja i Gran s'obtenen per evolució.
 */
class XuxedexService
{
    // Crea les entrades inicials del Xuxedex per a un usuari nou. No fa res si ja existeixen
    public function ensureStarterXuxedex(int $userId): bool
    {
        $existingEntries = DB::table('xuxedex')
            ->where('id_usuario', $userId)
            ->exists();

        if ($existingEntries) {
            return true;
        }

        $starterEntries = [];

        foreach (['Aigua', 'Terra', 'Aire'] as $tipus) {
            // Només afegim Xuxemons Petits; els Mitja i Gran s'obtenen per evolució
            $xuxemons = Xuxemons::where('tipo_elemento', $tipus)
                ->where('tamano', 'Petit')
                ->inRandomOrder()
                ->get();

            if ($xuxemons->isEmpty()) {
                continue;
            }

            foreach ($xuxemons as $xuxemon) {
                $starterEntries[] = [
                    'id_usuario' => $userId,
                    'id_xuxemon' => $xuxemon->id,
                    'esta_capturado' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($starterEntries)) {
            return false;
        }

        DB::table('xuxedex')->insert($starterEntries);

        return true;
    }
}