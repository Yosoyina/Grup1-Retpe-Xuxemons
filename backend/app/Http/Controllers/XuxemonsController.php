<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Xuxemons;
use Illuminate\Support\Facades\DB;

class XuxemonsController extends Controller
{
    /**
     * Obtenir tots els Xuxemons del usuari autenticat de la seva Xuxedex
     * Suporta filtres per tipus d'element
     * 
     * GET /api/xuxedex
     * GET /api/xuxedex?tipo=Aigua
     */
    public function getUserXuxedex(Request $request)
    {
        $userId = $request->user()->id;
        $tipo   = $request->query('tipo');

        // Tots els xuxemons del catàleg (filtrats per tipus si cal)
        $catalogQuery = DB::table('xuxemons');
        if ($tipo && $tipo !== 'Todos') {
            $catalogQuery->where('tipo_elemento', $tipo);
        }
        $cataleg = $catalogQuery->get();

        // Els xuxemons que té el jugador
        $meus = DB::table('xuxedex')
            ->where('id_usuario', $userId)
            ->pluck('esta_capturado', 'id_xuxemon'); // [id_xuxemon => esta_capturado]

        // Construir la resposta: els que té amb dades, els que no com a bloquejats
        $resultat = $cataleg->map(function ($xuxemon) use ($meus) {
            if ($meus->has($xuxemon->id)) {
                // El jugador el té: mostrar totes les dades
                return [
                    'id'             => $xuxemon->id,
                    'nombre_xuxemon' => $xuxemon->nombre_xuxemon,
                    'tipo_elemento'  => $xuxemon->tipo_elemento,
                    'tamano'         => $xuxemon->tamano,
                    'descripcio'     => $xuxemon->descripcio,
                    'imagen'         => $xuxemon->imagen,
                    'esta_capturado' => (bool) $meus[$xuxemon->id],
                    'bloquejat'      => false,
                ];
            } else {
                // El jugador NO el té: carta bloquejada sense dades
                return [
                    'id'             => $xuxemon->id,
                    'nombre_xuxemon' => '???',
                    'tipo_elemento'  => $xuxemon->tipo_elemento,
                    'tamano'         => '???',
                    'descripcio'     => '',
                    'imagen'         => null,
                    'esta_capturado' => false,
                    'bloquejat'      => true,
                ];
            }
        });

        return response()->json($resultat, 200);
    }

    /**
     * Display a listing of the resource.
     */

    // ── XUXEDEX ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $tipo   = $request->tipo_elemento;
        $tamano = $request->tamano;

        $xuxemons = DB::select("
            SELECT *
            FROM xuxemons
            WHERE (tipo_elemento = :tipo   OR :tipo   IS NULL)
            AND (tamano = :tamano OR :tamano IS NULL)
        ", [
            'tipo'   => $tipo,
            'tamano' => $tamano,
        ]);

        return response()->json($xuxemons, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_xuxemon' => 'required|string',
            'tipo_elemento' => 'required|in:Aigua,Terra,Aire',
            'tamano' => 'required|in:Petit,Mitja,Gran',
            'descripcio' => 'nullable|string',
            'imagen' => 'nullable|string',
        ]);

        DB::insert("
            INSERT INTO xuxemons (nombre_xuxemon, tipo_elemento, tamano, descripcio, imagen, created_at, updated_at)
            VALUES (:nombre_xuxemon, :tipo_elemento, :tamano, :descripcio, :imagen, NOW(), NOW())
        ", [
            'nombre_xuxemon' => $request->nombre_xuxemon,
            'tipo_elemento' => $request->tipo_elemento,
            'tamano' => $request->tamano,
            'descripcio' => $request->descripcio,
            'imagen' => $request->imagen,
        ]);

        return response()->json(['message' => 'Xuxemon creado correctamente'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
