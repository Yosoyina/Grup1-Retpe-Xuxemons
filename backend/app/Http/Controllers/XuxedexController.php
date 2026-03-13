<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class XuxedexController extends Controller
{
    /*
     * En este aprtado devolvemos todos los xuxemons de todos los jugadores.
     */
    public function index()
    {
        $xuxedex = Xuxedex::with(['xuxemon', 'user'])
            ->get()
            ->map(fn($entrada) => [
                'id' => $entrada->id,
                'user_id' => $entrada->user_id,
                'nombre_usuario' => $entrada->user->name,
                'id_xuxemon' => $entrada->xuxemon->id,
                'nombre_xuxemon' => $entrada->xuxemon->nombre_xuxemon,
                'tipo_elemento' => $entrada->xuxemon->tipo_elemento,
                'tamano' => $entrada->xuxemon->tamano,
                'imagen' => $entrada->xuxemon->imagen,
            ]);
 
        return response()->json($xuxedex, 200);
    }

    /*
     * En este apartado el admin añade un xuxemon aleatoriamnente a un jugador.
     */
    public function xuxemonAleatorio(string $user_id)
    {
        $xuxemon = Xuxemons::inRandomOrder()->firstOrFail();
 
        $entrada = Xuxedex::create([
            'id_usuario' => $user_id,
            'id_xuxemon' => $xuxemon->id,
        ]);
 
        return response()->json([
            'message' => 'Xuxemon aleatori afegit correctament.',
            'entrada' => $entrada->load('xuxemon'),
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
    }
}
