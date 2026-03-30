<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Xuxemons;
use App\Models\SystemConfig;
use App\Services\XuxedexService;
use Illuminate\Support\Facades\DB;

class XuxemonsController extends Controller
{
    public function __construct(private XuxedexService $xuxedexService)
    {
    }

    /**
     * GET /api/xuxedex
     * GET /api/xuxedex?tipo=Aigua
     * GET /api/xuxedex?tipo=Aigua&tamano=Petit
     */

    // Retorna els xuxemons del jugador, els bloquejats surten com a ???
    public function getUserXuxedex(Request $request)
    {
        $userId = $request->user()->id;
        $tipo = $request->query('tipo');
        $tamano = $request->query('tamano');

        $query = DB::table('xuxedex')
            ->join('xuxemons', 'xuxedex.id_xuxemon', '=', 'xuxemons.id')
            ->where('xuxedex.id_usuario', $userId)
            ->select(
                'xuxedex.id as xuxedex_id',
                'xuxemons.id',
                'xuxemons.nombre_xuxemon',
                'xuxemons.tipo_elemento',
                'xuxemons.tamano',
                'xuxemons.descripcio',
                'xuxemons.imagen',
                'xuxemons.xuxes_per_pujar',
                'xuxedex.esta_capturado',
                'xuxedex.enfermedad',
            );

        // Aplica filtres si l'usuari els ha enviat
        if ($tipo && $tipo !== 'Todos') {
            $query->where('xuxemons.tipo_elemento', $tipo);
        }

        if ($tamano) {
            $query->where('xuxemons.tamano', $tamano);
        }

        $query->orderByDesc('xuxedex.esta_capturado')
              ->orderBy('xuxemons.id');

        $xuxemons = $query->get()->map(function ($x) {
            return [
                'xuxedex_id' => $x->xuxedex_id,
                'id' => $x->id,
                'nombre_xuxemon' => $x->esta_capturado ? $x->nombre_xuxemon : '???',
                'tipo_elemento' => $x->tipo_elemento,
                'tamano' => $x->esta_capturado ? $x->tamano : '???',
                'descripcio' => $x->esta_capturado ? $x->descripcio : '',
                'imagen' => $x->esta_capturado ? $x->imagen : null,
                'xuxes_per_pujar' => $x->xuxes_per_pujar,
                'esta_capturado' => (bool) $x->esta_capturado,
                'bloquejat' => !(bool) $x->esta_capturado,

                // Estat de salut: llegit directament de xuxedex.enfermedad
                'esta_enfermo' => $x->esta_capturado ? !is_null($x->enfermedad) : false,
                'enfermedad' => $x->esta_capturado ? $x->enfermedad : null,
            ];
        });
        return response()->json($xuxemons, 200);
    }


    /** GET /api/xuxemons */

    // Retorna tots els xuxemons del catàleg, amb filtres opcionals per tipus i mida
    public function index(Request $request)
    {
        $xuxemons = Xuxemons::query()
            ->when($request->query('tipo_elemento'), fn($q, $v) => $q->where('tipo_elemento', $v))
            ->when($request->query('tamano'), fn($q, $v) => $q->where('tamano', $v))
            ->get();

        return response()->json($xuxemons, 200);
    }

    /** POST /api/xuxemons */

    // Crea un nou xuxemon al catàleg, només per a administradors

    public function store(Request $request)
    {
        $xuxemon = Xuxemons::create($request->validate([
            'nombre_xuxemon' => 'required|string|max:50',
            'tipo_elemento' => 'required|in:Aigua,Terra,Aire',
            'tamano' => 'required|in:Petit,Mitja,Gran',
            'descripcio' => 'nullable|string',
            'imagen' => 'nullable|string',
        ]));

        return response()->json($xuxemon, 201);
    }


    /** GET /api/xuxemons/{id} */

    // Retorna un xuxemon pel seu ID, només per a administradors
    public function show(string $id)
    {
        return response()->json(Xuxemons::findOrFail($id), 200);
    }

    /** PUT /api/xuxemons/{id} */

    // Actualitza un xuxemon pel seu ID, només per a administradors
    public function update(Request $request, string $id)
    {
        $xuxemon = Xuxemons::findOrFail($id);
        $xuxemon->update($request->validate([
            'nombre_xuxemon' => 'sometimes|string|max:50',
            'tipo_elemento' => 'sometimes|in:Aigua,Terra,Aire',
            'tamano' => 'sometimes|in:Petit,Mitja,Gran',
            'descripcio' => 'nullable|string',
            'imagen' => 'nullable|string',
        ]));

        return response()->json($xuxemon, 200);
    }

    /** DELETE /api/xuxemons/{id} */

    // Elimina un xuxemon pel seu ID, només per a administradors
    public function destroy(string $id)
    {
        Xuxemons::findOrFail($id)->delete();
        return response()->json(['message' => 'Xuxemon eliminat correctament'], 200);

    }


    // POST /api/xuxemons/{id}/evolucionar
    // Consumeix una/tres Xuxa EV i desbloqueja la següent etapa al xuxedex de l'usuari.
    // Efectes de malalties:
    //   'Sobredosis'      → bloqueja l'evolució fins que es curi
    //   'Bajon de azucar' → necessita 3 Xuxa EV en lloc d'1
    public function evolucionar(Request $request, string $id)
    {
        $xuxemon = Xuxemons::findOrFail($id);
        $userId = $request->user()->id;

        $nextTamano = match ($xuxemon->tamano) {
            'Petit' => 'Mitja',
            'Mitja' => 'Gran',
            default => null,
        };

        if (!$nextTamano) {
            return response()->json(['message' => "Ja es troba a l'estat màxim d'evolució."], 422);
        }

        // Comprova l'estat de malaltia del xuxemon
        $entrada = DB::table('xuxedex')
            ->where('id_usuario', $userId)
            ->where('id_xuxemon', $xuxemon->id)
            ->where('esta_capturado', true)
            ->first();

        // Sobredosis → bloqueja l'evolució
        if ($entrada && $entrada->enfermedad === 'Sobredosis') {
            return response()->json([
                'message' => 'El Xuxemon té Sobredosis i no pot evolucionar fins que es curi.',
                'enfermedad' => 'Sobredosis',
            ], 422);
        }

        $baseCost = $xuxemon->xuxes_per_pujar ?? 1;

        // Bajón de azúcar → necessita més XuxEvo per evolucionar
        $xuxesNecessaries = ($entrada && $entrada->enfermedad === 'Bajon de azucar') ? $baseCost + 2 : $baseCost;

        $nextEvolution = Xuxemons::where('tipo_elemento', $xuxemon->tipo_elemento)
            ->where('evolucion_xuxemon', $xuxemon->evolucion_xuxemon)
            ->where('tamano', $nextTamano)
            ->first();

        if (!$nextEvolution) {
            return response()->json(['message' => "No s'ha trobat l'evolució."], 404);
        }

        // Comprova que l'usuari te prous XuxEvo a l'inventari
        $xuxaEves = \App\Models\Inventario::with('xuxe')
            ->where('user_id', $userId)
            ->whereHas('xuxe', function($query) {
                $query->where('nombre_xuxes', 'XuxEvo');
            })
            ->get();

        $totalEves = $xuxaEves->sum('cantidad');

        if ($totalEves < $xuxesNecessaries) {
            return response()->json([
                'message' => "Necessites {$xuxesNecessaries} XuxEvo per evolucionar" .
                    ($xuxesNecessaries > 1 ? ' (el teu xuxemon té Bajón de Azúcar).' : '.'),
            ], 422);
        }

        // Consumeix les XuxEvo necessàries
        $restant = $xuxesNecessaries;
        foreach ($xuxaEves as $item) {
            if ($restant <= 0) break;
            
            if ($item->cantidad <= $restant) {
                $restant -= $item->cantidad;
                $item->delete();
            } else {
                $item->decrement('cantidad', $restant);
                $restant = 0;
            }
        }

        // Elimina l'evolució anterior del xuxedex de l'usuari
        DB::table('xuxedex')
            ->where('id_usuario', $userId)
            ->where('id_xuxemon', $xuxemon->id)
            ->delete();

        // Afegeix la següent evolució al xuxedex de l'usuari
        DB::table('xuxedex')->updateOrInsert(
            ['id_usuario' => $userId, 'id_xuxemon' => $nextEvolution->id],
            ['esta_capturado' => true, 'enfermedad' => null, 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json([
            'message' => 'Evolució completada!',
            'xuxemon' => $nextEvolution,
            'xuxes_consumides' => $xuxesNecessaries,
        ], 200);
    }

    // Evoluciones Xuxemons

    public function Evoluciones(string $id)
    {
        $xuxemon = Xuxemons::findOrFail($id);

        $cadena = Xuxemons::where('tipo_elemento', $xuxemon->tipo_elemento)
            ->where('evolucion_xuxemon', $xuxemon->evolucion_xuxemon)
            ->orderByRaw("FIELD(tamano, 'Petit', 'Mitja', 'Gran')")
            ->get(['id', 'nombre_xuxemon', 'tamano', 'imagen']);

        return response()->json([
            'cadena_evolutiva' => $cadena,
            'total_etapes' => $cadena->count(),
        ], 200);
    }

    /**
     * POST /api/xuxemons/{id}/feed
     *
     *   5%  → 'Bajon de azucar'
     *   10% → 'Sobredosis'
     *   15% → 'Atracon'
     *   70% → sense infecció
     *
     * Si el xuxemon ja té 'Atracon', no es pot alimentar.
     * Si ja té qualsevol altra malaltia, no es pot infectar de nou.
     */
    public function feed(Request $request, string $id)
    {
        $request->validate([
            'xuxedex_id' => 'required|integer|exists:xuxedex,id',
            'cantidad'   => 'integer|min:1'
        ]);

        $cantidadAUsar = $request->input('cantidad', 1);
        $userId = $request->user()->id;
        $xuxemon = Xuxemons::findOrFail($id);

        // Busca l'entrada del xuxedex per aquest usuari i xuxemon
        $xuxedexId = $request->input('xuxedex_id');
        $entrada = DB::table('xuxedex')
            ->where('id', $xuxedexId)
            ->where('id_usuario', $userId)
            ->where('id_xuxemon', $xuxemon->id)
            ->where('esta_capturado', true)
            ->first();

        if (!$entrada) {
            return response()->json(['error' => 'No tens aquest Xuxemon.'], 404);
        }

        // Si té Atracón, no pot ser alimentat
        if ($entrada->enfermedad === 'Atracon') {
            return response()->json([
                'infectat' => false,
                'enfermedad' => 'Atracon',
                'message' => "Aquest Xuxemon té Atracón i no pot ser alimentat!",
                'bloquejat' => true,
            ], 422);
        }

        // Si ja té qualsevol altra malaltia, no es pot infectar de nou
        if (!is_null($entrada->enfermedad)) {
            return response()->json([
                'infectat' => false,
                'enfermedad' => $entrada->enfermedad,
                'message' => "Aquest Xuxemon ja està malalt ({$entrada->enfermedad}).",
                'bloquejat' => false,
            ], 422);
        }

        // Busca el menjar disponible (apilables, excepte XuxEvo) a l'inventari
        $items = \App\Models\Inventario::with('xuxe')
            ->where('user_id', $userId)
            ->whereHas('xuxe', function($query) {
                $query->where('apilable', true)
                      ->where('nombre_xuxes', '!=', 'XuxEvo');
            })
            ->get();

        $totalMenjar = $items->sum('cantidad');
        if ($totalMenjar < $cantidadAUsar) {
            return response()->json([
                'message' => "No tens prou menjar. Tens {$totalMenjar} i vols utilitzar {$cantidadAUsar}.",
            ], 422);
        }

        // Consumeix del primer que trobi fins a arribar a 0
        $restant = $cantidadAUsar;
        foreach ($items as $item) {
            if ($restant <= 0) break;
            
            if ($item->cantidad <= $restant) {
                $restant -= $item->cantidad;
                $item->delete();
            } else {
                $item->decrement('cantidad', $restant);
                $restant = 0;
            }
        }

        // Tirada aleatòria d'infecció (1–100) per cada caramel consumit?
        // O una sola tirada global per l'acció? Farem una per acció com estava, però amb la probabilitat multiplicada?
        // Ho deixem igual que estava, 1 tirada, independent de la cantitat
        $roll = rand(1, 100);

        $pBajon      = (int) SystemConfig::get('infeccio_bajon', 5);
        $pSobredosis = (int) SystemConfig::get('infeccio_sobredosis', 10);
        $pAtracon    = (int) SystemConfig::get('infeccio_atracon', 15);

        if ($roll <= $pBajon) {
            $nova = 'Bajon de azucar';
        } elseif ($roll <= $pBajon + $pSobredosis) {
            $nova = 'Sobredosis';
        } elseif ($roll <= $pBajon + $pSobredosis + $pAtracon) {
            $nova = 'Atracon';
        } else {
            $nova = null;
        }

        if ($nova !== null) {
            DB::table('xuxedex')
                ->where('id', $xuxedexId)
                ->where('id_usuario', $userId)
                ->update(['enfermedad' => $nova, 'updated_at' => now()]);
        }

        return response()->json([
            'infectat' => $nova !== null,
            'enfermedad' => $nova,
            'message' => $nova !== null
                ? "El teu Xuxemon {$xuxemon->nombre_xuxemon} ha agafat {$nova}!"
                : "{$xuxemon->nombre_xuxemon} ha menjat {$cantidadAUsar} xuxes i està ple d'energia.",
        ], 200);
    }

    /**
     * POST /api/xuxemons/{id}/curar
     *
     * Gasta una vacuna de l'inventari de l'usuari per curar la malaltia del xuxemon.
     *
     * Mapa de vacunes:
     *   'Xocolatina'     → cura 'Bajon de azucar'
     *   'Xal de fruites' → cura 'Atracon'
     *   'Inxulina'       → cura qualsevol malaltia
     */
    public function curar(Request $request, string $id)
    {
        $request->validate([
            'vacuna_id' => 'required|integer|exists:xuxes,id',
        ]);

        $userId = $request->user()->id;
        $xuxemon = Xuxemons::findOrFail($id);
        $vacunaId = $request->input('vacuna_id');

        // Comprova que el xuxemon pertany a l'usuari i que està malalt
        $entrada = DB::table('xuxedex')
            ->where('id_usuario', $userId)
            ->where('id_xuxemon', $xuxemon->id)
            ->where('esta_capturado', true)
            ->first();

        if (!$entrada) {
            return response()->json(['error' => 'No tens aquest Xuxemon.'], 404);
        }

        if (is_null($entrada->enfermedad)) {
            return response()->json(['error' => 'Aquest Xuxemon no està malalt.'], 422);
        }

        // Comprova que la vacuna existeix a l'inventari de l'usuari
        $slotVacuna = DB::table('inventario')
            ->where('user_id', $userId)
            ->where('xuxe_id', $vacunaId)
            ->first();

        if (!$slotVacuna) {
            return response()->json(['error' => 'No tens aquesta vacuna a l\'inventari.'], 422);
        }

        // Comprova que la vacuna és compatible amb la malaltia
        $vacuna = DB::table('xuxes')->where('id', $vacunaId)->first();
        $curesAll = $vacuna->nombre_xuxes === 'Inxulina';

        $mapaVacunes = [
            'Xocolatina' => 'Bajon de azucar',
            'Xal de fruites' => 'Atracon',
        ];

        $curaMalaltia = $mapaVacunes[$vacuna->nombre_xuxes] ?? null;

        if (!$curesAll && $curaMalaltia !== $entrada->enfermedad) {
            return response()->json([
                'error' => "La vacuna '{$vacuna->nombre_xuxes}' no cura '{$entrada->enfermedad}'.",
                'cura' => $curaMalaltia,
                'te' => $entrada->enfermedad,
            ], 422);
        }

        // Gasta la vacuna (no apilable → elimina el slot)
        DB::table('inventario')->where('id', $slotVacuna->id)->delete();

        // Cura el xuxemon
        $malaltiaActual = $entrada->enfermedad;
        DB::table('xuxedex')
            ->where('id_usuario', $userId)
            ->where('id_xuxemon', $xuxemon->id)
            ->update(['enfermedad' => null, 'updated_at' => now()]);

        return response()->json([
            'message' => "{$xuxemon->nombre_xuxemon} ha estat curat de {$malaltiaActual}!",
            'enfermedad' => null,
        ], 200);
    }

    /** GET /admin/xuxedex */

    // Retorna els xuxemons d'un usuari específic (per a administradors)
    public function getAdminXuxedex(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json(['error' => 'user_id requerido'], 400);
        }

        $this->xuxedexService->ensureStarterXuxedex((int) $userId);

        $xuxemons = DB::table('xuxedex')
            ->join('xuxemons', 'xuxedex.id_xuxemon', '=', 'xuxemons.id')
            ->where('xuxedex.id_usuario', $userId)
            ->where('xuxedex.esta_capturado', true)
            ->select(
                'xuxedex.id as xuxedex_id',
                'xuxedex.enfermedad',
                'xuxemons.id',
                'xuxemons.nombre_xuxemon',
                'xuxemons.tipo_elemento',
                'xuxemons.tamano',
                'xuxemons.descripcio',
                'xuxemons.imagen',
                'xuxemons.xuxes_per_pujar'
            )
            ->get()
            ->map(function ($x) {
                return [
                    'id' => $x->id,
                    'xuxedex_id' => $x->xuxedex_id,
                    'nombre_xuxemon' => $x->nombre_xuxemon,
                    'tipo_elemento' => $x->tipo_elemento,
                    'tamano' => $x->tamano,
                    'descripcio' => $x->descripcio,
                    'imagen' => $x->imagen,
                    'xuxes_per_pujar' => $x->xuxes_per_pujar,
                    'esta_enfermo' => !is_null($x->enfermedad),
                    'enfermedad' => $x->enfermedad,
                ];
            });

        return response()->json($xuxemons, 200);
    }

    /** POST /admin/xuxedex */

    // Afegeix un xuxemon aleatori a un usuari (per a administradors)
    public function addXuxemonToUser(Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json(['error' => 'user_id requerido'], 400);
        }

        $this->xuxedexService->ensureStarterXuxedex((int) $userId);

        // Només desbloquejem Xuxemons Petits; els Mitja i Gran s'obtenen per evolució
        $blockedEntry = DB::table('xuxedex')
            ->join('xuxemons', 'xuxedex.id_xuxemon', '=', 'xuxemons.id')
            ->where('id_usuario', $userId)
            ->where('esta_capturado', false)
            ->where('xuxemons.tamano', 'Petit')
            ->inRandomOrder()
            ->select(
                'xuxedex.id_xuxemon',
                'xuxemons.id',
                'xuxemons.nombre_xuxemon',
                'xuxemons.tipo_elemento',
                'xuxemons.tamano',
                'xuxemons.descripcio',
                'xuxemons.imagen'
            )
            ->first();

        if (!$blockedEntry) {
            return response()->json(['error' => 'El usuario ya tiene todos sus Xuxemons desbloqueados'], 400);
        }

        DB::table('xuxedex')
            ->where('id_usuario', $userId)
            ->where('id_xuxemon', $blockedEntry->id_xuxemon)
            ->update([
                'esta_capturado' => true,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Xuxemon agregado correctamente',
            'xuxemon' => [
                'id' => $blockedEntry->id,
                'nombre_xuxemon' => $blockedEntry->nombre_xuxemon,
                'tipo_elemento' => $blockedEntry->tipo_elemento,
                'tamano' => $blockedEntry->tamano,
                'descripcio' => $blockedEntry->descripcio,
                'imagen' => $blockedEntry->imagen,
            ]
        ], 201);
    }
}
