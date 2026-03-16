<?php

namespace App\Services;

use App\Models\Xuxemons;
use Illuminate\Support\Facades\DB;

class XuxedexService
{
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
            $xuxemons = Xuxemons::where('tipo_elemento', $tipus)
                ->inRandomOrder()
                ->limit(6)
                ->get();

            if ($xuxemons->isEmpty()) {
                return false;
            }

            $numDesbloquejats = $xuxemons->count() === 1
                ? 1
                : rand(1, min(5, $xuxemons->count() - 1));

            foreach ($xuxemons as $index => $xuxemon) {
                $starterEntries[] = [
                    'id_usuario' => $userId,
                    'id_xuxemon' => $xuxemon->id,
                    'esta_capturado' => $index < $numDesbloquejats,
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
