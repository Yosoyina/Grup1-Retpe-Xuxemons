<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Xuxemons;
use Illuminate\Support\Facades\DB;

class XuxemonsController extends Controller
{
    public function getUserXuxedex(Request $request)
    {
        $userId = $request->user()->id;
        $tipo   = $request->query('tipo');

        $tipus = ($tipo && $tipo !== 'Todos')
            ? [$tipo]
            : ['Aigua', 'Terra', 'Aire'];

        $resultat = collect();

        foreach ($tipus as $t) {
            // Els 6 xuxemons del jugador per aquest tipus
            // esta_capturado=true → desbloquejat, false → bloquejat
            $xuxemons = DB::table('xuxedex')
                ->join('xuxemons', 'xuxedex.id_xuxemon', '=', 'xuxemons.id')
                ->where('xuxedex.id_usuario', $userId)
                ->where('xuxemons.tipo_elemento', $t)
                ->select(
                    'xuxemons.id',
                    'xuxemons.nombre_xuxemon',
                    'xuxemons.tipo_elemento',
                    'xuxemons.tamano',
                    'xuxemons.descripcio',
                    'xuxemons.imagen',
                    'xuxedex.esta_capturado'
                )
                ->limit(6)
                ->get()
                ->map(fn($x) => [
                    'id'             => $x->id,
                    // Si està bloquejat amaguem el nom i la imatge
                    'nombre_xuxemon' => $x->esta_capturado ? $x->nombre_xuxemon : '???',
                    'tipo_elemento'  => $x->tipo_elemento,
                    'tamano'         => $x->esta_capturado ? $x->tamano : '???',
                    'descripcio'     => $x->esta_capturado ? $x->descripcio : '',
                    'imagen'         => $x->esta_capturado ? $x->imagen : null,
                    'esta_capturado' => (bool) $x->esta_capturado,
                    'bloquejat'      => !(bool) $x->esta_capturado,
                ]);

            $resultat = $resultat->concat($xuxemons);
        }

        return response()->json($resultat->values(), 200);
    }

    public function index(Request $request)
    {
        $tipo   = $request->tipo_elemento;
        $tamano = $request->tamano;

        $xuxemons = DB::select("
            SELECT * FROM xuxemons
            WHERE (tipo_elemento = :tipo   OR :tipo   IS NULL)
            AND   (tamano        = :tamano OR :tamano IS NULL)
        ", ['tipo' => $tipo, 'tamano' => $tamano]);

        return response()->json($xuxemons, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_xuxemon' => 'required|string',
            'tipo_elemento'  => 'required|in:Aigua,Terra,Aire',
            'tamano'         => 'required|in:Petit,Mitja,Gran',
            'descripcio'     => 'nullable|string',
            'imagen'         => 'nullable|string',
        ]);

        DB::insert("
            INSERT INTO xuxemons (nombre_xuxemon, tipo_elemento, tamano, descripcio, imagen, created_at, updated_at)
            VALUES (:nombre_xuxemon, :tipo_elemento, :tamano, :descripcio, :imagen, NOW(), NOW())
        ", [
            'nombre_xuxemon' => $request->nombre_xuxemon,
            'tipo_elemento'  => $request->tipo_elemento,
            'tamano'         => $request->tamano,
            'descripcio'     => $request->descripcio,
            'imagen'         => $request->imagen,
        ]);

        return response()->json(['message' => 'Xuxemon creat correctament'], 201);
    }

    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
