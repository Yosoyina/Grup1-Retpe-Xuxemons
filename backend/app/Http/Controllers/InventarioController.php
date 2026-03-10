<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventario;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'xuxemon_id' => 'required|integer|exists:xuxemons,id',
        ]);

        $xuxemonId = $request->input('xuxemon_id');
        $items = Inventario::with(['xuxemon', 'xuxe']) ->where('xuxemon_id', $xuxemonId) ->get();
        $slotsUtilizados = Inventario::slotsUtilizados($xuxemonId);

        return response()->json([
            'xuxemon_id' => $xuxemonId,
            'slots_utilizados' => $slotsUtilizados,
            'max_slots' => Inventario::MAX_SLOTS,
            'free_slots' => Inventario::MAX_SLOTS - $slotsUtilizados,
            'items' => $items,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'xuxemon_id' => 'required|integer|exists:xuxemons,id',
            'xuxe_id'    => 'required|integer|exists:xuxas,id',
            'cantidad'    => 'required|integer|min:1|',
        ]);

        $xuxemonId = $request->input('xuxemon_id');
        $xuxaId = $request->input('xuxe_id');
        $cantidad = $request->input('cantidad');
        $descartado = 0;

        //Llenamos los stacks existentes de la misma Xuxe

        $itemsExistentes = Inventario::where('xuxemon_id', $xuxemonId)
            ->where('xuxe_id', $xuxaId)
            ->where('cantidad', '<', Inventario::MAX_STACK)
            ->get();

        foreach ($itemsExistentes as $item) {
            if($cantidad <= 0) continue;
            $espacioDisponible = Inventario::MAX_STACK - $item->cantidad;
            $agregar = min($cantidad, $espacioDisponible);
            $item->cantidad += $agregar;
            $item->save();
            $cantidad -= $agregar;
        }

        //Creamos nuevos stacks si aún queda cantidad por agregar

        while($cantidad > 0 && Inventario::slotsUtilizados($xuxemonId) < Inventario::MAX_SLOTS) {
            $agregar = min($cantidad, Inventario::MAX_STACK);
            Inventario::create([
                'xuxemon_id' => $xuxemonId,
                'xuxe_id' => $xuxaId,
                'cantidad' => $agregar,
            ]);
            $cantidad -= $agregar;
        }

        $descartado = $cantidad; // Si aún queda cantidad, es porque se ha descartado por falta de espacio

        $mensaje = $descartado > 0
            ? "Inventario lleno. Se han agregado algunos items, pero se han descartado $descartado por falta de espacio."
            : "Items agregados al inventario exitosamente.";

        return response()->json([
            'mensaje' => $mensaje,
            'descartado' => $descartado,
            'slots_utilizados' => Inventario::slotsUtilizados($xuxemonId), 
            'max_slots' => Inventario::MAX_SLOTS,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $item = Inventory::with(['xuxemon', 'xuxa'])->findOrFail($id);

        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = Inventario::findOrFail($id);

        $request->validate(['cantidad' => 'required|integer|min:1|max:' . Inventario::MAX_STACK]);

        $item->update($reqquest->only(['cantidad']));

        return response()->json([
            'mensaje' => 'Cantidad de Xuxes Actualizada',
            'item' => $item->load('xuxe'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $item = Inventario::findOrFail($id);
        $item->delete();

        return response()->json(['mensaje' => 'Item eliminado del inventario']);
    }

    public function listadosItems()
    {
        return response()->json([
            'xuxemons' => Xuxemons::all(['id', 'nombre_xuxemon', 'tipo_elemento', 'tamano']),
            'xuxes'    => Xuxa::all(['id', 'nombre']),
        ]);
    }

}
