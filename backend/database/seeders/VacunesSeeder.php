<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Xuxes;

class VacunesSeeder extends Seeder
{
    public function run(): void
    {
        $vacunes = [
            [
                'nombre_xuxes' => 'Xocolatina',
                'descripcio'   => 'Vacuna no apilable. En usar-la en un Xuxemon treu "Bajón de azúcar".',
                'imagen'       => 'Imatges/Vacunes/Xocolatina.webp',
                'apilable'     => false,
            ],
            [
                'nombre_xuxes' => 'Xal de fruites',
                'descripcio'   => 'Vacuna no apilable. En usar-la en un Xuxemon treu "Atracón".',
                'imagen'       => 'Imatges/Vacunes/XalFrutas.webp',
                'apilable'     => false,
            ],
            [
                'nombre_xuxes' => 'Inxulina',
                'descripcio'   => 'Vacuna no apilable. En usar-la en un Xuxemon cura totes les malalties.',
                'imagen'       => 'Imatges/Vacunes/Inxulina.webp',
                'apilable'     => false,
            ],
        ];

        foreach ($vacunes as $vacuna) {
            Xuxes::updateOrCreate(
                ['nombre_xuxes' => $vacuna['nombre_xuxes']],
                $vacuna
            );
        }
    }
}
