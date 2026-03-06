<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Xuxemons;

class XuxemonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Aqui estan los xuxxemons por defecto
     */   
    public function run(): void
    {
        $xuxemons = [
            // Aigua
            [
             'nombre_xuxemon' => 'Goteta',
             'tipo_elemento' => 'Aigua',
             'tamano' => 'Petit',
             'descripcio' => "Una petita gota d'aigua que salta alegrement.",
             'imagen' => null,
            ],
            [
             'nombre_xuxemon' => 'Bassot',
             'tipo_elemento' => 'Aigua',
             'tamano' => 'Mitja',
             'descripcio' => 'Un bassiot profund ple de misteris aquàtics.',
             'imagen' => null,
            ],
            [
             'nombre_xuxemon' => 'Laguna',
             'tipo_elemento' => 'Aigua',
             'tamano' => 'Gran',
             'descripcio' => "Una laguna ancestral plena d'energia hidden.",
             'imagen' => null,
            ],

            // Aire

            [
             'nombre_xuxemon' => 'Sospir',
             'tipo_elemento' => 'Aire',
             'tamano' => 'Petit',
             'descripcio' => 'Un sospir de vent calent que escampa les llavors.',
             'imagen' => null,
            ],
            [
             'nombre_xuxemon' => 'Ratxot',
             'tipo_elemento' => 'Aire',
             'tamano' => 'Mitja',
             'descripcio' => 'Una ratxa de vent que apareix de forma inesperada.',
             'imagen' => null,
            ],
            [
             'nombre_xuxemon' => 'Estratós',
             'tipo_elemento' => 'Aire',
             'tamano' => 'Gran',
             'descripcio' => "Un ésser de l'estratosfera amb poder il·limitat.",
             'imagen' => null,
            ],

            // Terra

            [
             'nombre_xuxemon' => 'Fanguet',
             'tipo_elemento' => 'Terra',
             'tamano' => 'Petit',
             'descripcio' => "Un fanguet tou que s'infiltra per qualsevol escletxa.",
             'imagen' => null,
            ],
            [
             'nombre_xuxemon' => 'Escarpat',
             'tipo_elemento' => 'Terra',
             'tamano' => 'Mitja',
             'descripcio' => 'Un escarpat rocós que escala qualsevol superfície.',
             'imagen' => null,
             ],
            [
             'nombre_xuxemon' => 'Terramut',
             'tipo_elemento' => 'Terra',
             'tamano' => 'Gran',
             'descripcio' => 'Un colossal guardià de terra que fa trontollar el sòl.',
             'imagen' => null,
            ],
            [
             'nombre_xuxemon' => 'Megalit',
             'tipo_elemento' => 'Terra',
             'tamano' => 'Gran',
             'descripcio' => 'Un megalit antic amb poder ancestral immens.',
             'imagen' => null,
            ],
        ];

        foreach ($xuxemons as $data) {
            Xuxemons::create($data);
        }
    }
}
