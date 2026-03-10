<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventario;

class InventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $xuxes = [
            [
                'nombre' => 'Xuxa de Foc',
                'descripcion' => 'Una xuxa ardent que escalfa la motxilla.',
                'imagen' => null,
            ],
            [
                'nombre' => "Xuxa d'Aigua",
                'descripcion' => 'Una xuxa fresca i refrescant.',
                'imagen' => null,
            ],
            [
                'nombre' => 'Xuxa de Terra',
                'descripcion' => 'Una xuxa sòlida com una roca.',
                'imagen' => null,
            ],
        ];

        foreach ($xuxes as $data) {
            Xuxa::create($data);
        }
    }
}
