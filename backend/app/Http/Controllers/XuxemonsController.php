<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Xuxemons;
use Illuminate\Support\Facades\DB;

class XuxemonsController extends Controller
{
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


    //── XUXEMONS ─────────────────────────────────────────────

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
        $existe = DB::select("SELECT id FROM xuxemons WHERE id = :id", ['id' => $id]);

        if (empty($existe)) {
            return response()->json(['message' => 'Xuxemon no encontrado'], 404);
        }

        $request->validate([
            'nombre_xuxemon' => 'sometimes|string',
            'tipo_elemento' => 'sometimes|in:Aigua,Terra,Aire',
            'tamano' => 'sometimes|in:Petit,Mitja,Gran',
            'descripcio' => 'nullable|string',
            'imagen' => 'nullable|string',
        ]);

        DB::update("
            UPDATE xuxemons
            SET nombre_xuxemon = COALESCE(:nombre_xuxemon, nombre_xuxemon),
                tipo_elemento = COALESCE(:tipo_elemento, tipo_elemento),
                tamano = COALESCE(:tamano, tamano),
                descripcio = COALESCE(:descripcio, descripcio),
                imagen = COALESCE(:imagen, imagen),
                updated_at = NOW()
            WHERE id = :id
        ", [
            'nombre_xuxemon' => $request->nombre_xuxemon,
            'tipo_elemento' => $request->tipo_elemento,
            'tamano' => $request->tamano,
            'descripcio' => $request->descripcio,
            'imagen' => $request->imagen,
            'id' => $id,
        ]);

        return response()->json(['message' => 'Xuxemon actualizado correctamente'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $existe = DB::select("SELECT id FROM xuxemons WHERE id = :id", ['id' => $id]);

        if (empty($existe)) {
            return response()->json(['message' => 'Xuxemon no encontrado'], 404);
        }

        DB::delete(" DELETE FROM xuxemons WHERE id = :id", ['id' => $id,]);
        
        return response()->json(['message' => 'Xuxemon eliminado correctamente'], 200);
    }
}