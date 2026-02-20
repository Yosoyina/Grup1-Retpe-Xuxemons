<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // ── HOME ──────────────────────────────────────────────

    public function home()
    {
        return response()->json([
            'message' => 'Benvingut a la pàgina principal',
            'user'    => Auth::guard('api')->user(),
        ]);
    }

    // ── PROFILE ───────────────────────────────────────────

    public function perfil()
    {
        return response()->json([
            'user' => Auth::guard('api')->user(),
        ]);
    }

    // ── UPDATE PROFILE ────────────────────────────────────

    public function updatePerfil(Request $request)
    {
        $user = Auth::guard('api')->user();

        $request->validate([
            'nombre'    => 'sometimes|string|max:25',
            'apellidos' => 'sometimes|string|max:25',
            'email'     => 'sometimes|email|unique:users,email,' . $user->id,
            'password'  => 'sometimes|string|min:6|confirmed',
        ]);

        if ($request->has('nombre'))    $user->nombre    = $request->nombre;
        if ($request->has('apellidos')) $user->apellidos = $request->apellidos;
        if ($request->has('email'))     $user->email     = $request->email;
        if ($request->has('password'))  $user->password  = Hash::make($request->password);

        $user->save();

        return response()->json([
            'message' => 'Perfil actualitzat correctament',
            'user'    => $user,
        ]);
    }

    // ── DELETE USER ───────────────────────────────────────

    public function eliminarUsuario()
    {
        $user = Auth::guard('api')->user();

        Auth::guard('api')->logout();
        $user->delete();

        return response()->json([
            'message' => 'Usuari eliminat correctament',
        ]);
    }
    
}
