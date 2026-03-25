<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventario;
use App\Models\Xuxes;
use App\Models\User;

class InventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $xuxes = [
            [
                'nombre_xuxes' => 'Cura Xuxe',
                'descripcio'   => 'Una xuxa ardent que escalfa la motxilla.',
                'imagen'       => 'Imatges/Xuxes/CuraXuxe.png',
                'apilable'     => true,
            ],
            [
                'nombre_xuxes' => "Xucolate",
                'descripcio'   => 'Una xuxa fresca i refrescant.',
                'imagen'       => 'Imatges/Xuxes/Xucolate.png',
                'apilable'     => true,
            ],
            [
                'nombre_xuxes' => 'Soda Prime',
                'descripcio'   => 'Una xuxa sòlida com una roca.',
                'imagen'       => 'Imatges/Xuxes/SodaPrime.png',
                'apilable'     => true,
            ],
            [
                'nombre_xuxes' => 'XuxEvo',
                'descripcio'   => 'Xuxe para evolucionar els teus xuxemons.',
                'imagen'       => 'Imatges/Xuxes/XuxEvos.png',
                'apilable'     => true,
            ],
        ];

        foreach ($xuxes as $data) {
            Xuxes::firstOrCreate(['nombre_xuxes' => $data['nombre_xuxes']], $data);
        }

        // Asigna las xuxes a todos los usuarios existentes
        $usuarios = User::all();

        foreach ($usuarios as $user) {
            foreach (Xuxes::all() as $xuxa) {
                Inventario::create([
                    'user_id'  => $user->id,
                    'xuxe_id'  => $xuxa->id,
                    'cantidad' => $xuxa->apilable ? rand(1, 5) : 1,
                ]);
            }
        }
    }
}
