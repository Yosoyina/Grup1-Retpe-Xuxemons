<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventario;
use App\Models\Xuxes;

class InventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $xuxes = [
            [
                'nombre_xuxes' => 'Xuxa de Foc',
                'descripcio' => 'Una xuxa ardent que escalfa la motxilla.',
                'imagen' => 'Imatges/Xuxes/CuraXuxe.png',
            ],
            [
                'nombre_xuxes' => "Xuxa d'Aigua",
                'descripcio' => 'Una xuxa fresca i refrescant.',
                'imagen' => 'Imatges/Xuxes/Xucolate.png',
            ],
            [
                'nombre_xuxes' => 'Xuxa de Terra',
                'descripcio' => 'Una xuxa sòlida com una roca.',
                'imagen' => 'Imatges/Xuxes/SodaPrime.png',
            ],
            [
                'nombre_xuxes' => 'Xuxa EV',
                'descripcio' => 'Xuxe para evolucionar els teus xuxemons.',
                'imagen' => 'Imatges/Xuxes/XuxEvos.png',
            ],
        ];

        foreach ($xuxes as $data) {
            Xuxes::create($data);
        }
    }
}
