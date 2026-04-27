<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

/**
 * Controlador d'usuaris.
 *
 * Gestiona el perfil, l'edició, l'eliminació i la cerca d'usuaris.
 * Inclou accions d'administrador per llistar, habilitar/deshabilitar i canviar el rol dels usuaris.
 */
class UserController extends Controller
{
    // ── HOME ──────────────────────────────────────────────
    // Retorna un missatge de benvinguda i les dades de l'usuari autenticat
    public function home()
    {
        return response()->json([
            'message' => 'Benvingut a la pàgina principal',
            'user' => Auth::guard('api')->user(),
        ]);
    }

    // ── PROFILE ───────────────────────────────────────────
    // Retorna les dades del perfil de l'usuari autenticat
    public function perfil()
    {
        return response()->json([
            'user' => Auth::guard('api')->user(),
        ]);
    }

    // ── UPDATE PROFILE ────────────────────────────────────
    // Actualitza les dades del perfil (nom, cognoms, email, password, avatar)
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
    // Inhabilita el compte de l'usuari i tanca la sessió activa
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
    // ── CERCA D'USUARIS PER ID ──────────────────────────────────────────
    // Cerca usuaris per id_jugador. Requereix mínim 3 caràcters per a la cerca
    public function search(Request $request)
    {
        $q = trim($request->query('q', ''));

        if (strlen($q) < 3) {
            return response()->json([]);
        }

        $currentUser = $request->user();

        $users = \App\Models\User::select('id', 'nombre', 'apellidos', 'id_jugador', 'avatar')
            ->where('id', '!=', $currentUser->id)
            ->where('actiu', true)
            ->where('id_jugador', 'like', '%' . $q . '%')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
    // ── LIST USERS (ADMIN) ────────────────────────────────
    // Retorna la llista completa d'usuaris per al panell d'administració
    public function listUsers()
    {
        $users = \App\Models\User::select('id', 'nombre', 'apellidos', 'email', 'id_jugador', 'role', 'actiu', 'avatar')
            ->get();

        return response()->json($users, 200);
    }

    // ── TOGGLE ACTIU (ADMIN) ──────────────────────────────

    public function toggleActiu(int $id)
    {
        $currentUser = \Illuminate\Support\Facades\Auth::guard('api')->user();

        if ((int)$id === $currentUser->id) {
            return response()->json(['message' => 'No pots deshabilitar el teu propi compte.'], 403);
        }

        $user = \App\Models\User::findOrFail($id);
        $user->actiu = !$user->actiu;
        $user->save();

        return response()->json([
            'message' => $user->actiu ? 'Usuari habilitat correctament' : 'Usuari deshabilitat correctament',
            'actiu'   => $user->actiu,
        ], 200);
    }

    // ── TOGGLE ROLE (ADMIN) ───────────────────────────────

    public function toggleRole($id)
    {
        $currentUser = \Illuminate\Support\Facades\Auth::guard('api')->user();

        if ((int)$id === $currentUser->id) {
            return response()->json(['message' => 'No pots canviar el teu propi rol.'], 403);
        }

        $user = \App\Models\User::findOrFail($id);
        $user->role = $user->role === 'admin' ? 'user' : 'admin';
        $user->save();

        return response()->json([
            'message' => $user->role === 'admin' ? 'Usuari ascendit a admin.' : 'Rol canviat a usuari.',
            'role'    => $user->role,
        ], 200);
    }
}
