<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ── REGISTER ─────────────────────────────────────────

    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:25',
            'apellidos' => 'required|string|max:25',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.unique' => 'Aquest correu ja està registrat.',
        ]);

        // El primer usuari registrat serà admin
        $role = User::count() === 0 ? 'admin' : 'user';

        // Generar id_jugador únic: #NombreXXXX
        $nombre_sin_espacios = str_replace(' ', '', $request->nombre);
        do {
            $id_jugador = '#' . $nombre_sin_espacios . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('id_jugador', $id_jugador)->exists());

        $user = User::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'id_jugador' => $id_jugador,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'avatar' => 'avatarpordefecto.png',

        ]);

        $token = Auth::guard('api')->login($user);

        return response()->json([
            'message' => 'Usuari registrat correctament',
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
        ], 201);
    }

    // ── LOGIN ─────────────────────────────────────────────

    public function login(Request $request)
    {
        $request->validate([
            'id_jugador' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('id_jugador', $request->id_jugador)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credencials incorrectes',
            ], 401);
        }

        $token = Auth::guard('api')->login($user);

        return response()->json([
            'message' => 'Login correcte',
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
        ]);
    }

    // ── LOGOUT ────────────────────────────────────────────

    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'message' => 'Sessió tancada correctament',
        ]);
    }
}