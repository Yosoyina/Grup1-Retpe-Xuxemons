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
        //
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
}
