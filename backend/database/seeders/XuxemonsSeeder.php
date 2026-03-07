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
             'imagen' => 'Xuxemons/Aigua-Petit-Graveta.png',
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

            // Terra - Petit (con imágenes)

            [
             'nombre_xuxemon' => 'Fanguet',
             'tipo_elemento' => 'Terra',
             'tamano' => 'Petit',
             'descripcio' => "Un fanguet tou que s'infiltra per qualsevol escletxa.",
             'imagen' => 'Imatges/Xuxemons/Terra-Petit-Fanguet.png',
            ],
            [
             'nombre_xuxemon' => 'Graveta',
             'tipo_elemento' => 'Terra',
             'tamano' => 'Petit',
             'descripcio' => 'Una graveta petita que salta alegrement.',
             'imagen' => 'Imatges/Xuxemons/Terra-Petit-Graveta.png',
             ],
            [
             'nombre_xuxemon' => 'Grumoll',
             'tipo_elemento' => 'Terra',
             'tamano' => 'Petit',
             'descripcio' => 'Un grumoll de terra compacta i sòlida.',
             'imagen' => 'Imatges/Xuxemons/Terra-Petit-Grumoll.png',
            ],
            [
             'nombre_xuxemon' => 'Pedrot',
             'tipo_elemento' => 'Terra',
             'tamano' => 'Petit',
             'descripcio' => 'Un pedrot rodó i lleuger que roda pels camps.',
             'imagen' => 'Imatges/Xuxemons/Terra-Petit-Pedrot.png',
            ],
            [
             'nombre_xuxemon' => 'Sorreta',
             'tipo_elemento' => 'Terra',
             'tamano' => 'Petit',
             'descripcio' => 'Una sorreta fina que flueix amb elegància.',
             'imagen' => 'Imatges/Xuxemons/Terra-Petit-Sorreta.png',
            ],
            [
             'nombre_xuxemon' => 'Terros',
             'tipo_elemento' => 'Terra',
             'tamano' => 'Petit',
             'descripcio' => 'Un terroset petit i acomodatici que viu sous terra.',
             'imagen' => 'Imatges/Xuxemons/Terra-Petit-Terros.png',
            ],

            // Terra - Mitja i Gran

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
