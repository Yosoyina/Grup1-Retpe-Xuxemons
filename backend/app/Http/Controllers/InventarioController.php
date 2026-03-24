<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Inventario;
use App\Models\Xuxemons;
use App\Models\Xuxes;

class InventarioController extends Controller
{
    // ── INVENTARIO DEL JUGADOR ───────────────────────────────────
    public function index(Request $request)
    {
        $userId = Auth::guard('api')->user()->id;
        $items = Inventario::with('xuxe')->where('user_id', $userId)->get();
        $slotsUtilizados = Inventario::slotsUtilizados($userId);

        // Mapears los items para incluir correctamente el campo apilable del xuxe
        $itemsFormateados = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'xuxe_id' => $item->xuxe_id,
                'cantidad' => $item->cantidad,
                'apilable' => $item->xuxe->apilable,
                'xuxe' => $item->xuxe,
            ];
        });

        return response()->json([
            'user_id' => $userId,
            'slots_utilizados' => $slotsUtilizados,
            'max_slots' => Inventario::MAX_SLOTS,
            'free_slots' => Inventario::MAX_SLOTS - $slotsUtilizados,
            'items' => $itemsFormateados,
        ]);
    }

    // ── AFEGIR ITEM AL INVENTARIO (ADMIN) ───────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'xuxe_id' => 'required|integer|exists:xuxes,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $userId = $request->input('user_id');
        $xuxeId = $request->input('xuxe_id');
        $cantidad = $request->input('cantidad');

        $xuxe = Xuxes::findOrFail($xuxeId);

        if ($xuxe->apilable) {
            // ── APILABLE (Xuxes)

            //Llenamos los stacks existentes del mismo tipo que no estén llenos
            $itemsExistents = Inventario::where('user_id', $userId)
                ->where('xuxe_id', $xuxeId)
                ->where('cantidad', '<', Inventario::MAX_STACK)
                ->get();

            foreach ($itemsExistents as $item) {
                if ($cantidad <= 0) break;
                $espai = Inventario::MAX_STACK - $item->cantidad;
                $agregar = min($cantidad, $espai);
                $item->cantidad += $agregar;
                $item->apilable = true;
                $item->save();
                $cantidad -= $agregar;
            }

            // En este apartado creeamos nuevos slots si todavia quedan cadtidades por agregar y hay espacio en el inventario
            while ($cantidad > 0 && Inventario::slotsUtilizados($userId) < Inventario::MAX_SLOTS) {
                $agregar = min($cantidad, Inventario::MAX_STACK);
                Inventario::create([
                    'user_id' => $userId,
                    'xuxe_id' => $xuxeId,
                    'cantidad' => $agregar,
                    'apilable' => true,
                ]);
                $cantidad -= $agregar;
            }

        } else {
            // ── NO APILABLE (Vacunas)

            while ($cantidad > 0 && Inventario::slotsUtilizados($userId) < Inventario::MAX_SLOTS) {
                Inventario::create([
                    'user_id' => $userId,
                    'xuxe_id' => $xuxeId,
                    'cantidad' => 1,
                    'apilable' => false,
                ]);
                $cantidad--;
            }
        }

        $descartado = $cantidad;

        $mensaje = $descartado > 0
            ? "Inventari ple. S'han descartat {$descartado} unitats per manca d'espai."
            : "Items afegits a l'inventari correctament.";

        return response()->json([
            'mensaje' => $mensaje,
            'descartado' => $descartado,
            'slots_utilizados' => Inventario::slotsUtilizados($userId),
            'max_slots' => Inventario::MAX_SLOTS,
        ], 201);
    }

    // ── MOSTRAR ITEM DEL INVENTARIO ───────────────────────────────────
    public function show(int $id)
    {
        $user = Auth::guard('api')->user();
        $item = Inventario::with('xuxe')->findOrFail($id);
 
        // Un usuari normal només pot veure els seus propis items
        if ($user->role !== 'admin' && $item->user_id !== $user->id) {
            return response()->json(['message' => 'No autoritzat'], 403);
        }   
 
        return response()->json($item);
    }

    // ── ACTUALITZAR ITEM DEL INVENTARIO (ADMIN) ───────────────────────────────────
    public function update(Request $request, int $id)
    {
        $item = Inventario::with('xuxe')->findOrFail($id);
        $maxCantidad = $item->xuxe->apilable ? Inventario::MAX_STACK : 1;

        $request->validate(['cantidad' => 'required|integer|min:1|max:' . $maxCantidad]);

        $item->update($request->only(['cantidad']));

        return response()->json([
            'mensaje' => 'Cantidad de Xuxes Actualizada',
            'item' => $item->load('xuxe'),
        ]);
    }

    // ── ELIMINAR ITEM DEL INVENTARIO (ADMIN) ───────────────────────────────────
    public function destroy(int $id)
    {
        Inventario::findOrFail($id)->delete();
        return response()->json(['mensaje' => 'Item eliminado del inventario']);
    }

    // ── LISTADOS DE ITEMS ───────────────────────────────────
    public function listadosItems()
    {
        return response()->json([
            'xuxemons' => Xuxemons::all(['id', 'nombre_xuxemon', 'tipo_elemento', 'tamano']),
            'xuxes' => Xuxes::all(['id', 'nombre_xuxes', 'imagen', 'apilable']),
        ]);
    }

    // ── LISTADO DE XUXES PARA SELECCIONAR EN EL INVENTARIO (ADMIN) ───────────────────────────────────
    public function listXuxes()
    {
        $xuxes = Xuxes::select('id', 'nombre_xuxes', 'imagen', 'apilable')
            ->get();

        return response()->json($xuxes, 200);
    }

}