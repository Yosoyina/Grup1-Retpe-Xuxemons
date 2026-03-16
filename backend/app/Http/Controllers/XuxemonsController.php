<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Xuxemons;
use Illuminate\Support\Facades\DB;

class XuxemonsController extends Controller
{
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

        $xuxemons = $query->get()
            ->map(fn($x) => [
                'id'             => $x->id,
                'nombre_xuxemon' => $x->esta_capturado ? $x->nombre_xuxemon : '???',
                'tipo_elemento'  => $x->tipo_elemento,
                'tamano'         => $x->esta_capturado ? $x->tamano : '???',
                'descripcio'     => $x->esta_capturado ? $x->descripcio : '',
                'imagen'         => $x->esta_capturado ? $x->imagen : null,
                'esta_capturado' => (bool) $x->esta_capturado,
                'bloquejat'      => !(bool) $x->esta_capturado,
            ]);

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

        $xuxemons = DB::table('xuxedex')
            ->join('xuxemons', 'xuxedex.id_xuxemon', '=', 'xuxemons.id')
            ->where('xuxedex.id_usuario', $userId)
            ->where('xuxedex.esta_capturado', true)
            ->select(
                'xuxemons.id',
                'xuxemons.nombre_xuxemon',
                'xuxemons.tipo_elemento',
                'xuxemons.tamano',
                'xuxemons.descripcio',
                'xuxemons.imagen',
            )
            ->get();

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

        $blockedEntry = DB::table('xuxedex')
            ->join('xuxemons', 'xuxedex.id_xuxemon', '=', 'xuxemons.id')
            ->where('id_usuario', $userId)
            ->where('esta_capturado', false)
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