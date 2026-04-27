<?php

namespace App\Http\Controllers;

use App\Models\SystemConfig;
use App\Models\Xuxemons;
use Illuminate\Http\Request;

/**
 * Controlador de configuració global del sistema (admin).
 *
 * Permet als administradors consultar i modificar els paràmetres del joc:
 * hores de recompensa, quantitats diàries i probabilitats d'infecció.
 * També gestiona les Xuxes necessàries per fer créixer cada Xuxemon.
 */
class AdminConfigController extends Controller
{
    // Claus vàlides i els seus tipus de validació
    private const CLAUS_VALIDES = [
        'xuxes_hora_recompensa'  => 'integer|min:0|max:23',
        'xuxes_quantitat_diaria' => 'integer|min:1|max:999',
        'xuxemon_hora_recompensa'=> 'integer|min:0|max:23',
        'infeccio_bajon'         => 'integer|min:0|max:100',
        'infeccio_sobredosis'    => 'integer|min:0|max:100',
        'infeccio_atracon'       => 'integer|min:0|max:100',
    ];

    /**
     * GET /api/admin/config
     * Retorna tota la configuració del sistema.
     */
    public function index()
    {
        $config = SystemConfig::all(['clave', 'valor', 'descripcio']);
        return response()->json($config, 200);
    }

    /**
     * PUT /api/admin/config/{clave}
     * Actualitza una clau de configuració.
     */
    public function update(Request $request, string $clave)
    {
        if (!array_key_exists($clave, self::CLAUS_VALIDES)) {
            return response()->json(['error' => "Clau de configuració desconeguda: {$clave}"], 422);
        }

        $regla = self::CLAUS_VALIDES[$clave];

        // Validació per a % d'infecció: la suma no ha de superar 100
        $request->validate(['valor' => "required|{$regla}"]);

        if (str_starts_with($clave, 'infeccio_')) {
            $this->validarPercentatgesInfeccio($request, $clave);
        }

        SystemConfig::set($clave, $request->input('valor'));

        return response()->json([
            'message' => "Configuració '{$clave}' actualitzada correctament.",
            'clave'   => $clave,
            'valor'   => (string) $request->input('valor'),
        ], 200);
    }

    /**
     * GET /api/admin/xuxemons-nivell
     * Retorna tots els Xuxemons amb el seu xuxes_per_pujar.
     */
    public function llistarXuxemonsNivell()
    {
        $xuxemons = Xuxemons::query()
            ->whereIn('tamano', ['Petit', 'Mitja'])
            ->orderByRaw("FIELD(tamano, 'Petit', 'Mitja')")
            ->orderBy('tipo_elemento')
            ->orderBy('nombre_xuxemon')
            ->get(['id', 'nombre_xuxemon', 'tipo_elemento', 'tamano', 'xuxes_per_pujar', 'imagen']);

        return response()->json($xuxemons, 200);
    }

    /**
     * PUT /api/admin/xuxemons-nivell/{id}
     * Actualitza les xuxes necessàries per fer créixer un xuxemon concret.
     */
    public function updateXuxesNivell(Request $request, string $id)
    {
        $xuxemon = Xuxemons::findOrFail($id);

        $request->validate([
            'xuxes_per_pujar' => 'required|integer|min:1|max:999',
        ]);

        $xuxemon->update(['xuxes_per_pujar' => $request->input('xuxes_per_pujar')]);

        return response()->json([
            'message'        => "Xuxes per pujar de '{$xuxemon->nombre_xuxemon}' actualitzades.",
            'xuxemon_id'     => $xuxemon->id,
            'xuxes_per_pujar'=> $xuxemon->xuxes_per_pujar,
        ], 200);
    }

    // ── Privat ──────────────────────────────────────────────────────────────

    private function validarPercentatgesInfeccio(Request $request, string $clauActual): void
    {
        $altresClaus = ['infeccio_bajon', 'infeccio_sobredosis', 'infeccio_atracon'];

        $total = 0;
        foreach ($altresClaus as $clau) {
            if ($clau === $clauActual) {
                $total += (int) $request->input('valor');
            } else {
                $total += (int) SystemConfig::get($clau, 0);
            }
        }

        if ($total > 100) {
            abort(422, "La suma dels percentatges d'infecció ({$total}%) supera el 100%.");
        }
    }
}
