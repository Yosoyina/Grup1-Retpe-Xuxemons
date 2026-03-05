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
             'nom' => 'Goteta',
             'tipus' => 'Aigua',
             'mida' => 'Petit',
             'descripcio' => "Una petita gota d'aigua que salta alegrement.",
            ],
            [
             'nom' => 'Bassot',
             'tipus' => 'Aigua',
             'mida' => 'Mitjà',
             'descripcio' => 'Un bassiot profund ple de misteris aquàtics.',
            ],
            [
             'nom' => 'Laguna',
             'tipus' => 'Aigua',
             'mida' => 'Gran',
             'descripcio' => "Una laguna ancestral plena d'energia hidden.",
            ],

            // Aire

            [
             'nom' => 'Sospir',
             'tipus' => 'Aire',
             'mida' => 'Petit',
             'descripcio' => 'Un sospir de vent calent que escampa les llavors.',
            ],
            [
             'nom' => 'Ratxot',
             'tipus' => 'Aire',
             'mida' => 'Mitjà',
             'descripcio' => 'Una ratxa de vent que apareix de forma inesperada.',
            ],
            [
             'nom' => 'Estratós',
             'tipus' => 'Aire',
             'mida' => 'Gran',
             'descripcio' => "Un ésser de l'estratosfera amb poder il·limitat.",
            ],

            // Terra

            [
             'nom' => 'Fanguet',
             'tipus' => 'Terra',
             'mida' => 'Petit',
             'descripcio' => "Un fanguet tou que s'infiltra per qualsevol escletxa.",
            ],
            [
             'nom' => 'Escarpat',
             'tipus' => 'Terra',
             'mida' => 'Mitjà',
             'descripcio' => 'Un escarpat rocós que escala qualsevol superfície.',
            ],
            [
             'nom' => 'Terramut',
             'tipus' => 'Terra',
             'mida' => 'Gran',
             'descripcio' => 'Un colossal guardià de terra que fa trontollar el sòl.',
            ],
            [
             'nom' => 'Megalit',
             'tipus' => 'Terra',
             'mida' => 'Gran',
             'descripcio' => 'Un megalit antic amb poder ancestral immens.',
            ],
        ];

        foreach ($xuxemons as $data) {
            Xuxemons::create($data);
        }
    }
}
