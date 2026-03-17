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
            // Agua — cada parella comparteix evolucion_xuxemon
            [
                'nombre_xuxemon' => 'Goteta',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Petit',
                'descripcio' => "Una petita gota d'aigua que salta alegrement.",
                'imagen' => 'Imatges/Xuxemons/Aigua-Petit-Goteta.png',
                'evolucion_xuxemon' => 'linia-aigua-1',
            ],
            [
                'nombre_xuxemon' => 'Bulleta',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Petit',
                'descripcio' => 'Un bassiot profund ple de misteris aquàtics.',
                'imagen' => 'Imatges/Xuxemons/Aigua-Petit-Bulleta.png',
                'evolucion_xuxemon' => 'linia-aigua-2',
            ],
            [
                'nombre_xuxemon' => 'Esquitx',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Petit',
                'descripcio' => "Una laguna ancestral plena d'energia hidden.",
                'imagen' => 'Imatges/Xuxemons/Aigua-Petit-Esquitx.png',
                'evolucion_xuxemon' => 'linia-aigua-3',
            ],
            [
                'nombre_xuxemon' => 'Regalim',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Petit',
                'descripcio' => "Una petita gota d'aigua que salta alegrement.",
                'imagen' => 'Imatges/Xuxemons/Aigua-Petit-Regalim.png',
                'evolucion_xuxemon' => 'linia-aigua-4',
            ],
            [
                'nombre_xuxemon' => 'Gotim',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Petit',
                'descripcio' => 'Un bassiot profund ple de misteris aquàtics.',
                'imagen' => 'Imatges/Xuxemons/Aigua-Petit-Gotim.png',
                'evolucion_xuxemon' => 'linia-aigua-5',
            ],
            [
                'nombre_xuxemon' => 'Perleta',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Petit',
                'descripcio' => "Una laguna ancestral plena d'energia hidden.",
                'imagen' => 'Imatges/Xuxemons/Aigua-Petit-Perleta.png',
                'evolucion_xuxemon' => 'linia-aigua-6',
            ],

            // Aire

            [
                'nombre_xuxemon' => 'Bufet',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => 'Un sospir de vent calent que escampa les llavors.',
                'imagen' => 'Imatges/Xuxemons/Vent-Petit-Bufet.png',
                'evolucion_xuxemon' => 'linia-aire-1',
            ],
            [
                'nombre_xuxemon' => 'Briseta',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => 'Una ratxa de vent que apareix de forma inesperada.',
                'imagen' => 'Imatges/Xuxemons/Vent-Petit-Briseta.png',
                'evolucion_xuxemon' => 'linia-aire-2',
            ],
            [
                'nombre_xuxemon' => 'Xiulet',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => "Un ésser de l'estratosfera amb poder il·limitat.",
                'imagen' => 'Imatges/Xuxemons/Vent-Petit-Xiulet.png',
                'evolucion_xuxemon' => 'linia-aire-3',
            ],
            [
                'nombre_xuxemon' => 'Airós',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => 'Un sospir de vent calent que escampa les llavors.',
                'imagen' => 'Imatges/Xuxemons/Vent-Petit-Airos.png',
                'evolucion_xuxemon' => 'linia-aire-4',
            ],
            [
                'nombre_xuxemon' => 'Alenat',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => 'Una ratxa de vent que apareix de forma inesperada.',
                'imagen' => 'Imatges/Xuxemons/Vent-Petit-Alenat.png',
                'evolucion_xuxemon' => 'linia-aire-5',
            ],
            [
                'nombre_xuxemon' => 'Sospir',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => "Un ésser de l'estratosfera amb poder il·limitat.",
                'imagen' => 'Imatges/Xuxemons/Vent-Petit-Sospir.png',
                'evolucion_xuxemon' => 'linia-aire-6',
            ],

            // Terra

            [
                'nombre_xuxemon' => 'Fanguet',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => "Un fanguet tou que s'infiltra per qualsevol escletxa.",
                'imagen' => 'Imatges/Xuxemons/Terra-Petit-Fanguet.png',
                'evolucion_xuxemon' => 'linia-terra-1',
            ],
            [
                'nombre_xuxemon' => 'Graveta',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => 'Una graveta petita que salta alegrement.',
                'imagen' => 'Imatges/Xuxemons/Terra-Petit-Graveta.png',
                'evolucion_xuxemon' => 'linia-terra-2',
            ],
            [
                'nombre_xuxemon' => 'Grumoll',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => 'Un grumoll de terra compacta i sòlida.',
                'imagen' => 'Imatges/Xuxemons/Terra-Petit-Grumoll.png',
                'evolucion_xuxemon' => 'linia-terra-3',
            ],
            [
                'nombre_xuxemon' => 'Pedrot',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => 'Un pedrot rodó i lleuger que roda pels camps.',
                'imagen' => 'Imatges/Xuxemons/Terra-Petit-Pedrot.png',
                'evolucion_xuxemon' => 'linia-terra-4',
            ],
            [
                'nombre_xuxemon' => 'Sorreta',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => 'Una sorreta fina que flueix amb elegància.',
                'imagen' => 'Imatges/Xuxemons/Terra-Petit-Sorreta.png',
                'evolucion_xuxemon' => 'linia-terra-5',
            ],
            [
                'nombre_xuxemon' => 'Terros',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => 'Un terroset petit i acomodatici que viu sous terra.',
                'imagen' => 'Imatges/Xuxemons/Terra-Petit-Terros.png',
                'evolucion_xuxemon' => 'linia-terra-6',
            ],
        ];

        foreach ($xuxemons as $data) {
            Xuxemons::create($data);
        }
    }
}