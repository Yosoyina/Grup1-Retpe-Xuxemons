<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Xuxes;

class XuxesSeeder extends Seeder
{
    /**
     * Inserta les Xuxes base al sistema:
     *   - Xuxa EV      → apilable,     per evolucionar xuxemons
     *   - Xocolatina   → no apilable,  cura 'Bajon de azucar'
     *   - Xal de fruites → no apilable, cura 'Atracon'
     *   - Inxulina     → no apilable,  cura totes les malalties
     */
    public function run(): void
    {
        $xuxes = [
            [
                'nombre_xuxes' => 'Xuxa EV',
                'descripcio'   => 'Objecte apilable que permet fer evolucionar un Xuxemon.',
                'imagen'       => null,
                'apilable'     => true,
            ],
            [
                'nombre_xuxes' => 'Xocolatina',
                'descripcio'   => 'Vacuna no apilable. En usar-la en un Xuxemon treu "Bajón de azúcar".',
                'imagen'       => null,
                'apilable'     => false,
            ],
            [
                'nombre_xuxes' => 'Xal de fruites',
                'descripcio'   => 'Vacuna no apilable. En usar-la en un Xuxemon treu "Atracón".',
                'imagen'       => null,
                'apilable'     => false,
            ],
            [
                'nombre_xuxes' => 'Inxulina',
                'descripcio'   => 'Vacuna no apilable. En usar-la en un Xuxemon cura totes les malalties.',
                'imagen'       => null,
                'apilable'     => false,
            ],
        ];

        foreach ($xuxes as $xuxa) {
            // updateOrCreate per evitar duplicats si es torna a executar el seeder
            Xuxes::updateOrCreate(
                ['nombre_xuxes' => $xuxa['nombre_xuxes']],
                $xuxa
            );
        }
    }
}
