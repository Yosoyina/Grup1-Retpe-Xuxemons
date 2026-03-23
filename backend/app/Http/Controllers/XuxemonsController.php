<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Xuxemons;
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
        $tipo   = $request->query('tipo');
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
                'xuxedex.esta_capturado',
            );

        // Aplica filtres si l'usuari els ha enviat
        if ($tipo && $tipo !== 'Todos') {
            $query->where('xuxemons.tipo_elemento', $tipo);
        }

        if ($tamano) {
            $query->where('xuxemons.tamano', $tamano);
        }

        $xuxemons = $query->get()->map(function ($x) {
            // Obtenim les enfermedades del xuxemon
            $malalties = DB::table('malalties')
                ->where('xuxedex_id', $x->xuxedex_id)
                ->pluck('tipo_enfermedad');
 
            return [
                'id'             => $x->id,
                'nombre_xuxemon' => $x->esta_capturado ? $x->nombre_xuxemon : '???',
                'tipo_elemento'  => $x->tipo_elemento,
                'tamano'         => $x->esta_capturado ? $x->tamano : '???',
                'descripcio'     => $x->esta_capturado ? $x->descripcio : '',
                'imagen'         => $x->esta_capturado ? $x->imagen : null,
                'esta_capturado' => (bool) $x->esta_capturado,
                'bloquejat'      => !(bool) $x->esta_capturado,
                // Estat de salut: mostrat sempre que el xuxemon estigui capturat
                'esta_enfermo'    => $x->esta_capturado ? $malalties->isNotEmpty() : false,
                'malalties'      => $x->esta_capturado ? $malalties->values() : [],
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
            ->when($request->query('tamano'),        fn($q, $v) => $q->where('tamano', $v))
            ->get();

        return response()->json($xuxemons, 200);
    }

    /** POST /api/xuxemons */

    // Crea un nou xuxemon al catàleg, només per a administradors

    public function store(Request $request)
    {
        $xuxemon = Xuxemons::create($request->validate([
            'nombre_xuxemon' => 'required|string|max:50',
            'tipo_elemento'  => 'required|in:Aigua,Terra,Aire',
            'tamano'         => 'required|in:Petit,Mitja,Gran',
            'descripcio'     => 'nullable|string',
            'imagen'         => 'nullable|string',
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
            'tipo_elemento'  => 'sometimes|in:Aigua,Terra,Aire',
            'tamano'         => 'sometimes|in:Petit,Mitja,Gran',
            'descripcio'     => 'nullable|string',
            'imagen'         => 'nullable|string',
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
    // Consumeix una Xuxa EV i desbloqueja la següent etapa al xuxedex de l'usuari
    public function evolucionar(Request $request, string $id)
    {
        $xuxemon = Xuxemons::findOrFail($id);
        $userId  = $request->user()->id;

        // Comprabar si el Xuxemon tiene la Enfermedad Atracon para que no pueda evolucionar
        $xuxedexEntry = DB::table('xuxedex')
            ->where('id_usuario', $userId)
            ->where('id_xuxemon', $xuxemon->id)
            ->first();
 
        if ($xuxedexEntry) {
            $Atracon = DB::table('malalties')
                ->where('xuxedex_id', $xuxedexEntry->id)
                ->where('tipo_enfermedad', 'Atracon')
                ->exists();
 
            if ($Atracon) {
                return response()->json([
                    'message' => 'El xuxemon no pot evolucionar perquè té la malaltia Atracón activa.',
                ], 422);
            }
        }

        $nextTamano = match ($xuxemon->tamano) {
            'Petit' => 'Mitja',
            'Mitja' => 'Gran',
            default => null,
        };

        if (!$nextTamano) {
            return response()->json(['message' => "Ja es troba a l'estat màxim d'evolució."], 422);
        }

        $nextEvolution = Xuxemons::where('tipo_elemento', $xuxemon->tipo_elemento)
            ->where('evolucion_xuxemon', $xuxemon->evolucion_xuxemon)
            ->where('tamano', $nextTamano)
            ->first();

        if (!$nextEvolution) {
            return response()->json(['message' => "No s'ha trobat l'evolució."], 404);
        }

        // Comprova que l'usuari te una Xuxa EV a l'inventari
        $xuxaEv = DB::table('inventario')
            ->join('xuxes', 'inventario.xuxe_id', '=', 'xuxes.id')
            ->where('inventario.user_id', $userId)
            ->where('xuxes.nombre_xuxes', 'Xuxa EV')
            ->where('inventario.cantidad', '>', 0)
            ->select('inventario.id', 'inventario.cantidad')
            ->first();

        if (!$xuxaEv) {
            return response()->json(['message' => 'Necessites una Xuxa EV per evolucionar.'], 422);
        }

        // Consumeix 1 Xuxa EV
        if ($xuxaEv->cantidad > 1) {
            DB::table('inventario')->where('id', $xuxaEv->id)->decrement('cantidad');
        } else {
            DB::table('inventario')->where('id', $xuxaEv->id)->delete();
        }

        // Elimina l'evolució anterior del xuxedex de l'usuari
        DB::table('xuxedex')
            ->where('id_usuario', $userId)
            ->where('id_xuxemon', $xuxemon->id)
            ->delete();

        // Afegeix la següent evolució al xuxedex de l'usuari
        DB::table('xuxedex')->updateOrInsert(
            ['id_usuario' => $userId, 'id_xuxemon' => $nextEvolution->id],
            ['esta_capturado' => true, 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json([
            'message' => 'Evolució completada!',
            'xuxemon' => $nextEvolution,
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
            'total_etapes'     => $cadena->count(),
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
                'xuxemons.id',
                'xuxemons.nombre_xuxemon',
                'xuxemons.tipo_elemento',
                'xuxemons.tamano',
                'xuxemons.descripcio',
                'xuxemons.imagen',
            )
            ->get()
            ->map(function ($x) {
                $malalties = DB::table('malalties')
                    ->where('xuxedex_id', $x->xuxedex_id)
                    ->pluck('tipus');
 
                return [
                    'id' => $x->id,
                    'xuxedex_id' => $x->xuxedex_id,
                    'nombre_xuxemon' => $x->nombre_xuxemon,
                    'tipo_elemento' => $x->tipo_elemento,
                    'tamano' => $x->tamano,
                    'descripcio' => $x->descripcio,
                    'imagen' => $x->imagen,
                    'esta_enfermo' => $malalties->isNotEmpty(),
                    'malalties' => $malalties->values(),
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