<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario;

/**
 * Controlador de vacunes.
 *
 * Gestiona l'aplicació de vacunes per curar les malalties dels Xuxemons.
 * Valida la compatibilitat entre la vacuna i la malaltia activa del Xuxemon.
 *
 * Mapa de vacunes:
 *   'Xocolatina'     → cura 'Bajon de azucar'
 *   'Xal de fruites' → cura 'Atracon'
 *   'Inxulina'       → cura qualsevol malaltia
 */
class VacunesController extends Controller
{
    // Aplica una vacuna de l'inventari de l'usuari per curar la malaltia d'un Xuxemon
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
            'Xocolatina'      => 'Bajon de azucar',
            'Xal de fruites'  => 'Atracon',
            'Inxulina'        => null, // null = cura totes
            default           => 'desconeguda',
        };
 
        if ($curaEnfermedad === 'desconeguda') {
            return response()->json([
                'message' => "La vacuna \"{$nombreVacuna}\" no és reconeguda al catàleg.",
            ], 422);
        }
 
        // ── Validar que el xuxemon pertany al jugador i està malalt ───────
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
 
        if (empty($entrada->enfermedad)) {
            return response()->json([
                'message' => 'Aquest xuxemon no té cap malaltia activa.',
            ], 422);
        }
 
        // ── Validar compatibilitat vacuna/malaltia ─────────────────────────
        if ($curaEnfermedad !== null) {
            $teLaMalaltia = $entrada->enfermedad === $curaEnfermedad;
 
            if (!$teLaMalaltia) {
                return response()->json([
                    'message' => "La vacuna \"{$nombreVacuna}\" cura \"{$curaEnfermedad}\" però el xuxemon no té aquesta malaltia.",
                ], 422);
            }
        }
 
        // ── Eliminar la vacuna de l'inventari ──────────────────────────────
        // Les vacunes no són apilables → s'elimina l'slot sencer
        $slot->delete();
 
        // ── Quitar la malaltia del xuxedex ─────────────────────────────────
        $malaltiaCurada = $entrada->enfermedad;
        
        DB::table('xuxedex')
            ->where('id', $request->xuxedex_id)
            ->update([
                'enfermedad' => null,
                'updated_at' => now(),
            ]);
 
        // ── Retornar l'estat actualitzat del xuxemon ───────────────────────
        return response()->json([
            'message'           => 'Vacuna aplicada correctament.',
            'vacuna'            => $nombreVacuna,
            'malalties_curades' => [$malaltiaCurada], // El frontend espera array
            'estat_xuxemon'     => [
                'xuxedex_id'   => $request->xuxedex_id,
                'esta_enfermo' => false,
                'malalties'    => [], // El frontend espera array
            ],
        ], 200);
    }
}
