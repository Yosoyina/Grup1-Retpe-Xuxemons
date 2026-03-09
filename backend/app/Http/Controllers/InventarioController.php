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
        $items     = Inventario::with(['xuxemon', 'xuxa'])
                        ->where('xuxemon_id', $xuxemonId)
                        ->get();
        $slotsUtilizados = Inventario::slotsUtilizados($xuxemonId);

        return response()->json([
            'xuxemon_id' => $xuxemonId,
            'slots_utilizados' => $slotsUtilizados,
            'max_slots'  => Inventario::MAX_SLOTS,
            'free_slots' => Inventario::MAX_SLOTS - $slotsUtilizados,
            'items'      => $items,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
