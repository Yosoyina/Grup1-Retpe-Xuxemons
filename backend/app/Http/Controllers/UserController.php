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
            'user' => Auth::guard('api')->user(),
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
            'avatar' => 'sometimes|string|max:25',
            'nombre' => 'sometimes|string|max:25',
            'apellidos' => 'sometimes|string|max:25',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6|confirmed',
            'password_confirmation' => 'sometimes|string|min:6',
        ], [
            'email.unique' => 'Aquest correu ja està registrat.',
        ]);

        if ($request->has('avatar'))
            $user->avatar = $request->avatar;
        if ($request->has('nombre'))
            $user->nombre = $request->nombre;
        if ($request->has('apellidos'))
            $user->apellidos = $request->apellidos;
        if ($request->has('email'))
            $user->email = $request->email;
        if ($request->has('password'))
            $user->password = Hash::make($request->password);

        $user->save();

        return response()->json([
            'message' => 'Perfil actualitzat correctament',
            'user' => $user,
        ]);
    }

    // ── DELETE USER ───────────────────────────────────────

    public function eliminarUsuario()
    {
        $user = Auth::guard('api')->user();

        $user->actiu = false;
        $user->save();

        Auth::guard('api')->logout();

        return response()->json([
            'message' => 'Compte inhabilitat correctament',
        ]);
    }

    // ── LIST USERS (ADMIN) ────────────────────────────────

    public function listUsers()
    {
        $users = \App\Models\User::select('id', 'nombre', 'apellidos', 'email', 'id_jugador', 'role', 'actiu', 'avatar')
            ->get();

        return response()->json($users, 200);
    }

    // ── TOGGLE ACTIU (ADMIN) ──────────────────────────────

    public function toggleActiu($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->actiu = !$user->actiu;
        $user->save();

        return response()->json([
            'message' => $user->actiu ? 'Usuari habilitat correctament' : 'Usuari deshabilitat correctament',
            'actiu'   => $user->actiu,
        ], 200);
    }
}
