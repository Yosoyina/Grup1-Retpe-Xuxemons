<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Xuxedex;
use App\Models\Xuxemons;
use Illuminate\Support\Facades\DB;

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

    /*
     *  En este apartado el admin elimina un xuxemon del xuxedex de un jugador.
     */
    public function destroy(Request $request, string $id)
    {
        $entrada = Xuxedex::where('id', $id)
            ->where('id_usuario', $request->user()->id)
            ->firstOrFail();
 
        $entrada->delete();
 
        return response()->json([
            'message' => 'Xuxemon eliminat del xuxedex.',
        ], 200);
    }
}
