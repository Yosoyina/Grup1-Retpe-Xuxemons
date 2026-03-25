<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario;

class VacunesController extends Controller
{
    public function aplicar(Request $request)
    {
        $userId = Auth::guard('api')->user()->id;
 
        $request->validate([
            'inventario_id' => 'required|integer|exists:inventario,id',
            'xuxedex_id'    => 'required|integer|exists:xuxedex,id',
        ]);
 
        // ── Validar que l'usuari té la vacuna i buscar el slot ─────────
        $slot = Inventario::with('xuxe')
            ->where('id', $request->inventario_id)
            ->where('user_id', $userId)
            ->first();
 
        if (!$slot) {
            return response()->json([
                'message' => 'No tens aquest ítem a l\'inventari.',
            ], 403);
        }
 
        // Comprova que l'ítem és una vacuna (no apilable)
        if ($slot->xuxe->apilable) {
            return response()->json([
                'message' => 'Aquest ítem no és una vacuna.',
            ], 422);
        }
 
        // Determina quina malaltia cura aquesta vacuna segons el nom
        $nombreVacuna = $slot->xuxe->nombre_xuxes;
        $curaEnfermedad = match ($nombreVacuna) {
            'Xocolatina'      => 'Bajon_Azucar',
            'Xal de fruites'  => 'Atracon',
            'Inxulina'        => null, // null = cura totes
            default           => 'desconeguda',
        };
 
        if ($curaEnfermedad === 'desconeguda') {
            return response()->json([
                'message' => "La vacuna \"{$nombreVacuna}\" no és reconeguda al catàleg.",
            ], 422);
        }
 
        // ── Validar que el xuxemon pertany al jugador ──────────────────────
        $entrada = DB::table('xuxedex')
            ->where('id', $request->xuxedex_id)
            ->where('id_usuario', $userId)
            ->where('esta_capturado', true)
            ->first();
 
        if (!$entrada) {
            return response()->json([
                'message' => 'Xuxemon no trobat al teu xuxedex.',
            ], 404);
        }
 
        // ── Validar que el xuxemon té alguna malaltia activa ───────────────
        $malalties = DB::table('malalties')
            ->where('xuxedex_id', $request->xuxedex_id)
            ->get();
 
        if ($malalties->isEmpty()) {
            return response()->json([
                'message' => 'Aquest xuxemon no té cap malaltia activa.',
            ], 422);
        }
 
        // ── Validar compatibilitat vacuna/malaltia ─────────────────────────
        if ($curaEnfermedad !== null) {
            $teLaMalaltia = $malalties
                ->where('tipo_enfermedad', $curaEnfermedad)
                ->isNotEmpty();
 
            if (!$teLaMalaltia) {
                return response()->json([
                    'message' => "La vacuna \"{$nombreVacuna}\" cura \"{$curaEnfermedad}\" però el xuxemon no té aquesta malaltia.",
                ], 422);
            }
        }
 
        // ── Eliminar la vacuna de l'inventari ──────────────────────────────
        // Les vacunes no són apilables → s'elimina l'slot sencer
        $slot->delete();
 
        // ── Quitar la/les malalties corresponents ──────────────────────────
        if ($curaEnfermedad === null) {
            // Inxulina → cura totes les malalties
            DB::table('malalties')
                ->where('xuxedex_id', $request->xuxedex_id)
                ->delete();
 
            $malaltiesCurades = $malalties->pluck('tipo_enfermedad')->values();
        } else {
            // Xocolatina / Xal de fruites → cura només la malaltia compatible
            DB::table('malalties')
                ->where('xuxedex_id', $request->xuxedex_id)
                ->where('tipo_enfermedad', $curaEnfermedad)
                ->delete();
 
            $malaltiesCurades = collect([$curaEnfermedad]);
        }
 
        // ── Retornar l'estat actualitzat del xuxemon ───────────────────────
        $malaltiesRestants = DB::table('malalties')
            ->where('xuxedex_id', $request->xuxedex_id)
            ->pluck('tipo_enfermedad')
            ->values();
 
        return response()->json([
            'message'           => 'Vacuna aplicada correctament.',
            'vacuna'            => $nombreVacuna,
            'malalties_curades' => $malaltiesCurades,
            'estat_xuxemon'     => [
                'xuxedex_id'   => $request->xuxedex_id,
                'esta_enfermo' => $malaltiesRestants->isNotEmpty(),
                'malalties'    => $malaltiesRestants,
            ],
        ], 200);
    }
}
