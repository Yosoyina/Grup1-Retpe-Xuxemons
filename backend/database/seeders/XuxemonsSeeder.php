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
                'imagen' => 'Imatges/Xuxemons/Ev1-Aigua-Petit-Goteta.webp',
                'evolucion_xuxemon' => 'linia-aigua-1',
            ],
            [
                'nombre_xuxemon' => 'Bulleta',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Petit',
                'descripcio' => 'Un bassiot profund ple de misteris aquàtics.',
                'imagen' => 'Imatges/Xuxemons/Ev2-Aigua-Petit-Bulleta.webp',
                'evolucion_xuxemon' => 'linia-aigua-2',
            ],
            [
                'nombre_xuxemon' => 'Esquitx',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Petit',
                'descripcio' => "Una laguna ancestral plena d'energia hidden.",
                'imagen' => 'Imatges/Xuxemons/Ev3-Aigua-Petit-Esquitx.webp',
                'evolucion_xuxemon' => 'linia-aigua-3',
            ],
            [
                'nombre_xuxemon' => 'Regalim',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Petit',
                'descripcio' => "Una petita gota d'aigua que salta alegrement.",
                'imagen' => 'Imatges/Xuxemons/Ev6-Aigua-Petit-Regalim.webp',
                'evolucion_xuxemon' => 'linia-aigua-4',
            ],
            [
                'nombre_xuxemon' => 'Gotim',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Petit',
                'descripcio' => 'Un bassiot profund ple de misteris aquàtics.',
                'imagen' => 'Imatges/Xuxemons/Ev4-Aigua-Petit-Gotim.webp',
                'evolucion_xuxemon' => 'linia-aigua-5',
            ],
            [
                'nombre_xuxemon' => 'Perleta',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Petit',
                'descripcio' => "Una laguna ancestral plena d'energia hidden.",
                'imagen' => 'Imatges/Xuxemons/Ev5-Aigua-Petit-Perleta.webp',
                'evolucion_xuxemon' => 'linia-aigua-6',
            ],

            // Aire

            [
                'nombre_xuxemon' => 'Bufet',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => 'Un sospir de vent calent que escampa les llavors.',
                'imagen' => 'Imatges/Xuxemons/Ev16-Vent-Petit-Bufet.webp',
                'evolucion_xuxemon' => 'linia-aire-1',
            ],
            [
                'nombre_xuxemon' => 'Briseta',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => 'Una ratxa de vent que apareix de forma inesperada.',
                'imagen' => 'Imatges/Xuxemons/Ev15-Vent-Petit-Briseta.webp',
                'evolucion_xuxemon' => 'linia-aire-2',
            ],
            [
                'nombre_xuxemon' => 'Xiulet',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => "Un ésser de l'estratosfera amb poder il·limitat.",
                'imagen' => 'Imatges/Xuxemons/Ev18-Vent-Petit-Xiulet.webp',
                'evolucion_xuxemon' => 'linia-aire-3',
            ],
            [
                'nombre_xuxemon' => 'Airós',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => 'Un sospir de vent calent que escampa les llavors.',
                'imagen' => 'Imatges/Xuxemons/Ev13-Vent-Petit-Airos.webp',
                'evolucion_xuxemon' => 'linia-aire-4',
            ],
            [
                'nombre_xuxemon' => 'Alenat',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => 'Una ratxa de vent que apareix de forma inesperada.',
                'imagen' => 'Imatges/Xuxemons/Ev14-Vent-Petit-Alenat.webp',
                'evolucion_xuxemon' => 'linia-aire-5',
            ],
            [
                'nombre_xuxemon' => 'Sospir',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Petit',
                'descripcio' => "Un ésser de l'estratosfera amb poder il·limitat.",
                'imagen' => 'Imatges/Xuxemons/Ev17-Vent-Petit-Sospir.webp',
                'evolucion_xuxemon' => 'linia-aire-6',
            ],

            // Terra

            [
                'nombre_xuxemon' => 'Fanguet',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => "Un fanguet tou que s'infiltra per qualsevol escletxa.",
                'imagen' => 'Imatges/Xuxemons/Ev12-Terra-Petit-Fanguet.webp',
                'evolucion_xuxemon' => 'linia-terra-1',
            ],
            [
                'nombre_xuxemon' => 'Graveta',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => 'Una graveta petita que salta alegrement.',
                'imagen' => 'Imatges/Xuxemons/Ev11-Terra-Petit-Graveta.webp',
                'evolucion_xuxemon' => 'linia-terra-2',
            ],
            [
                'nombre_xuxemon' => 'Grumoll',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => 'Un grumoll de terra compacta i sòlida.',
                'imagen' => 'Imatges/Xuxemons/Ev8-Terra-Petit-Grumoll.webp',
                'evolucion_xuxemon' => 'linia-terra-3',
            ],
            [
                'nombre_xuxemon' => 'Pedrot',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => 'Un pedrot rodó i lleuger que roda pels camps.',
                'imagen' => 'Imatges/Xuxemons/Ev7-Terra-Petit-Pedrot.webp',
                'evolucion_xuxemon' => 'linia-terra-4',
            ],
            [
                'nombre_xuxemon' => 'Sorreta',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => 'Una sorreta fina que flueix amb elegància.',
                'imagen' => 'Imatges/Xuxemons/Ev9-Terra-Petit-Sorreta.webp',
                'evolucion_xuxemon' => 'linia-terra-5',
            ],
            [
                'nombre_xuxemon' => 'Terros',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Petit',
                'descripcio' => 'Un terroset petit i acomodatici que viu sous terra.',
                'imagen' => 'Imatges/Xuxemons/Ev10-Terra-Petit-Terros.webp',
                'evolucion_xuxemon' => 'linia-terra-6',
            ],

            // ── Evolucions Mitja ────────────────────────────────────────

            // Mitja Aigua
            [
                'nombre_xuxemon' => 'Rierol',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Mitja',
                'descripcio' => "Un rierol decidit que avança per camins rocosos.",
                'imagen' => 'Imatges/Xuxemons/Ev1-Aigua-Mitja-Rierol.webp',
                'evolucion_xuxemon' => 'linia-aigua-1',
            ],
            [
                'nombre_xuxemon' => 'Glopet',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Mitja',
                'descripcio' => "Un glop d'aigua que acumula força sota la superfície.",
                'imagen' => 'Imatges/Xuxemons/Ev2-Aigua-Mitja-Glopet.webp',
                'evolucion_xuxemon' => 'linia-aigua-2',
            ],
            [
                'nombre_xuxemon' => 'Bassot',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Mitja',
                'descripcio' => "Un bassot profund que reflecteix el cel nocturn.",
                'imagen' => 'Imatges/Xuxemons/Ev3-Aigua-MItja-Bassot.webp',
                'evolucion_xuxemon' => 'linia-aigua-3',
            ],
            [
                'nombre_xuxemon' => 'Torrent',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Mitja',
                'descripcio' => "Un torrent que s'escola ràpid entre les pedres.",
                'imagen' => 'Imatges/Xuxemons/Ev6-Aigua-Mitja-Torrent.webp',
                'evolucion_xuxemon' => 'linia-aigua-4',
            ],
            [
                'nombre_xuxemon' => 'Aiguat',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Mitja',
                'descripcio' => "Un aiguat sobtós que omple els barrancs.",
                'imagen' => 'Imatges/Xuxemons/Ev4-Aigua-Mitja-Aiguat.webp',
                'evolucion_xuxemon' => 'linia-aigua-5',
            ],
            [
                'nombre_xuxemon' => 'Remolí',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Mitja',
                'descripcio' => "Un remolí d'aigua que arrossega tot al seu pas.",
                'imagen' => 'Imatges/Xuxemons/Ev5-Aigua-Mitja-Remoli.webp',
                'evolucion_xuxemon' => 'linia-aigua-6',
            ],

            // Mitja Aire
            [
                'nombre_xuxemon' => 'Ventroll',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Mitja',
                'descripcio' => "Un ventroll potent que esculpeix les muntanyes.",
                'imagen' => 'Imatges/Xuxemons/Ev16-Vent-Mitja-Ventroll.webp',
                'evolucion_xuxemon' => 'linia-aire-1',
            ],
            [
                'nombre_xuxemon' => 'Ratxot',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Mitja',
                'descripcio' => "Una ratxa d'aire que dobla els arbres centenaris.",
                'imagen' => 'Imatges/Xuxemons/Ev15-Vent-Mitjar-Ratxot.webp',
                'evolucion_xuxemon' => 'linia-aire-2',
            ],
            [
                'nombre_xuxemon' => 'Remolins',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Mitja',
                'descripcio' => "Uns remolins sinuosos que danssen a l'aire.",
                'imagen' => 'Imatges/Xuxemons/Ev18-Vent-Mitja-Remolins.webp',
                'evolucion_xuxemon' => 'linia-aire-3',
            ],
            [
                'nombre_xuxemon' => 'Borrasca',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Mitja',
                'descripcio' => "Una borrasca que porta pluja de terra llunyana.",
                'imagen' => 'Imatges/Xuxemons/Ev13-Vent-Mitja-Borrasca.webp',
                'evolucion_xuxemon' => 'linia-aire-4',
            ],
            [
                'nombre_xuxemon' => 'Ventijol',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Mitja',
                'descripcio' => "Un ventijol capaç de desviar qualsevol trajecte.",
                'imagen' => 'Imatges/Xuxemons/Ev14-Vent-Mitjar-Ventijol.webp',
                'evolucion_xuxemon' => 'linia-aire-5',
            ],
            [
                'nombre_xuxemon' => 'Corrent',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Mitja',
                'descripcio' => "Un corrent d'aire que connecta cel i terra.",
                'imagen' => 'Imatges/Xuxemons/Ev17-Vent-Mitjar-Corrent.webp',
                'evolucion_xuxemon' => 'linia-aire-6',
            ],

            // Mitja Terra
            [
                'nombre_xuxemon' => 'Argilos',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Mitja',
                'descripcio' => "Un ésser d'argila que modela la terra al seu gust.",
                'imagen' => 'Imatges/Xuxemons/Ev12-Terra-Mitja-Argilos.webp',
                'evolucion_xuxemon' => 'linia-terra-1',
            ],
            [
                'nombre_xuxemon' => 'Escarpat',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Mitja',
                'descripcio' => "Un escarpat esmolat que resisteix qualsevol erosió.",
                'imagen' => 'Imatges/Xuxemons/Ev11-Terra-Mitja-Escarpat.webp',
                'evolucion_xuxemon' => 'linia-terra-2',
            ],
            [
                'nombre_xuxemon' => 'Codol',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Mitja',
                'descripcio' => "Un codol ben rodat que porta la memòria del riu.",
                'imagen' => 'Imatges/Xuxemons/Ev8-Terra-Mitja-Codol.webp',
                'evolucion_xuxemon' => 'linia-terra-3',
            ],
            [
                'nombre_xuxemon' => 'Rocall',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Mitja',
                'descripcio' => "Un rocall desglaçat d'una muntanya antiga.",
                'imagen' => 'Imatges/Xuxemons/Ev7-Terra-Mitja-Rocall.webp',
                'evolucion_xuxemon' => 'linia-terra-4',
            ],
            [
                'nombre_xuxemon' => 'Arenal',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Mitja',
                'descripcio' => "Un arenal canviant que mai conserva la mateixa forma.",
                'imagen' => 'Imatges/Xuxemons/Ev9-Terra-Mitja-Arenal.webp',
                'evolucion_xuxemon' => 'linia-terra-5',
            ],
            [
                'nombre_xuxemon' => 'Llotos',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Mitja',
                'descripcio' => "Un lotus de pedra que floreix a les zones humides.",
                'imagen' => 'Imatges/Xuxemons/Ev10-Terra-Mitja-Llotos.webp',
                'evolucion_xuxemon' => 'linia-terra-6',
            ],

            // ── Evolucions Gran ─────────────────────────────────────────

            // Gran Aigua
            [
                'nombre_xuxemon' => 'Maregot',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Gran',
                'descripcio' => "Un maregot colossal que sacseja els continents.",
                'imagen' => 'Imatges/Xuxemons/Ev1-Aigua-Gran-Maregot.webp',
                'evolucion_xuxemon' => 'linia-aigua-1',
            ],
            [
                'nombre_xuxemon' => 'Onada',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Gran',
                'descripcio' => "Una onada gegantina que ressona com un tro.",
                'imagen' => 'Imatges/Xuxemons/Ev2-Aigua-Gran-Onada.webp',
                'evolucion_xuxemon' => 'linia-aigua-2',
            ],
            [
                'nombre_xuxemon' => 'Cascada',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Gran',
                'descripcio' => "Una cascada interminable que neix al cim del món.",
                'imagen' => 'Imatges/Xuxemons/Ev3-Aigua-Gran-Cascada.webp',
                'evolucion_xuxemon' => 'linia-aigua-3',
            ],
            [
                'nombre_xuxemon' => 'Diluviet',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Gran',
                'descripcio' => "Un diluvi devastador que transforma el paisatge.",
                'imagen' => 'Imatges/Xuxemons/Ev6-Aigua-Gran-Diluviet.webp',
                'evolucion_xuxemon' => 'linia-aigua-4',
            ],
            [
                'nombre_xuxemon' => 'Abisme',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Gran',
                'descripcio' => "Un abisme aquàtic sense fi ple de secrets.",
                'imagen' => 'Imatges/Xuxemons/Ev4-Aigua-Gran-Abisme.webp',
                'evolucion_xuxemon' => 'linia-aigua-5',
            ],
            [
                'nombre_xuxemon' => 'Laguna',
                'tipo_elemento' => 'Aigua',
                'tamano' => 'Gran',
                'descripcio' => "Una laguna primordial plena d'energia ancestral.",
                'imagen' => 'Imatges/Xuxemons/Ev5-Aigua-Gran-Laguna.webp',
                'evolucion_xuxemon' => 'linia-aigua-6',
            ],

            // Gran Aire
            [
                'nombre_xuxemon' => 'Tempestos',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Gran',
                'descripcio' => "Una tempesta elèctrica que il·lumina la nit.",
                'imagen' => 'Imatges/Xuxemons/Ev16-Vent-Gran-Tempestos.webp',
                'evolucion_xuxemon' => 'linia-aire-1',
            ],
            [
                'nombre_xuxemon' => 'Ciclo',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Gran',
                'descripcio' => "Un cicló que neteja el camí davant seu.",
                'imagen' => 'Imatges/Xuxemons/Ev15-Vent-Gran-Ciclo.webp',
                'evolucion_xuxemon' => 'linia-aire-2',
            ],
            [
                'nombre_xuxemon' => 'Tifo',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Gran',
                'descripcio' => "Un tifó que neix al mar i creix sense aturar-se.",
                'imagen' => 'Imatges/Xuxemons/Ev18-Vent-Gran-Tifo.webp',
                'evolucion_xuxemon' => 'linia-aire-3',
            ],
            [
                'nombre_xuxemon' => 'Huracas',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Gran',
                'descripcio' => "Un huracà de força devastadora i incontrolable.",
                'imagen' => 'Imatges/Xuxemons/Ev13-Vent-Gran-Huracas.webp',
                'evolucion_xuxemon' => 'linia-aire-4',
            ],
            [
                'nombre_xuxemon' => 'Tornadas',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Gran',
                'descripcio' => "Un tornado que dansa sobre la terra erma.",
                'imagen' => 'Imatges/Xuxemons/Ev14-Vent-Gran-Tornadas.webp',
                'evolucion_xuxemon' => 'linia-aire-5',
            ],
            [
                'nombre_xuxemon' => 'Estratos',
                'tipo_elemento' => 'Aire',
                'tamano' => 'Gran',
                'descripcio' => "Un ésser de les capes altes que controla els vents.",
                'imagen' => 'Imatges/Xuxemons/Ev17-Vent-Gran-Estratos.webp',
                'evolucion_xuxemon' => 'linia-aire-6',
            ],

            // Gran Terra
            [
                'nombre_xuxemon' => 'Volcanot',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Gran',
                'descripcio' => "Un volcà colossal que dorm sota els continents.",
                'imagen' => 'Imatges/Xuxemons/Ev12-Terra-Gran-Volcanot.webp',
                'evolucion_xuxemon' => 'linia-terra-1',
            ],
            [
                'nombre_xuxemon' => 'Cimas',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Gran',
                'descripcio' => "Un cim que toca els núvols i guarda secrets antics.",
                'imagen' => 'Imatges/Xuxemons/Ev11-Terra-Gran-Cimas.webp',
                'evolucion_xuxemon' => 'linia-terra-2',
            ],
            [
                'nombre_xuxemon' => 'Megalit',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Gran',
                'descripcio' => "Un megalit que empeny la terra i desafia el temps.",
                'imagen' => 'Imatges/Xuxemons/Ev8-Terra-Gran-Megalit.webp',
                'evolucion_xuxemon' => 'linia-terra-3',
            ],
            [
                'nombre_xuxemon' => 'Terramunt',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Gran',
                'descripcio' => "Una terramunt invencible que trenca qualsevol roca.",
                'imagen' => 'Imatges/Xuxemons/Ev7-Terra-Gran-Terramunt.webp',
                'evolucion_xuxemon' => 'linia-terra-4',
            ],
            [
                'nombre_xuxemon' => 'Desertor',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Gran',
                'descripcio' => "Un desert viu que absorbeix tota la humitat del món.",
                'imagen' => 'Imatges/Xuxemons/Ev9-Terra-Gran-Desertor.webp',
                'evolucion_xuxemon' => 'linia-terra-5',
            ],
            [
                'nombre_xuxemon' => 'Pantanas',
                'tipo_elemento' => 'Terra',
                'tamano' => 'Gran',
                'descripcio' => "Unes pantanes eternes que amaguen criatures antigues.",
                'imagen' => 'Imatges/Xuxemons/Ev10-Terra-Gran-Pantanas.webp',
                'evolucion_xuxemon' => 'linia-terra-6',
            ],
        ];

        foreach ($xuxemons as $data) {
            if (!isset($data['xuxes_per_pujar'])) {
                $data['xuxes_per_pujar'] = match ($data['tamano']) {
                    'Petit' => 3,
                    'Mitja' => 5,
                    'Gran' => 0,
                    default => 3,
                };
            }
            Xuxemons::create($data);
        }
    }
}