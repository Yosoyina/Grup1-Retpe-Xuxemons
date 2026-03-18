<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\XuxedexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private XuxedexService $xuxedexService)
    {
    }

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

        $role = User::count() === 0 ? 'admin' : 'user';

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

        $this->xuxedexService->ensureStarterXuxedex($user->id);

        $token = Auth::guard('api')->login($user);

        return response()->json([
            'message' => 'Usuari registrat correctament',
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'id_jugador' => 'required|string',
            'password' => 'required|string',
        ]);

        // Permet login amb id_jugador (#Marc1234) o amb email.
        // També accepta id_jugador sense el prefix '#', per a usuaris que no l'han copiat.
        $login = trim($request->id_jugador);
        $query = User::where('email', $login);

        // Si sembla un id_jugador (no té @), intenta amb i sense '#'
        if (!str_contains($login, '@')) {
            $query = $query->orWhere('id_jugador', $login);
            $query = $query->orWhere('id_jugador', "#{$login}");
        }

        $user = $query->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credencials incorrectes'], 401);
        }

        if (!$user->actiu) {
            return response()->json(['message' => 'Aquest compte ha estat inhabilitat'], 403);
        }

        $token = Auth::guard('api')->login($user);

        return response()->json([
            'message' => 'Login correcte',
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
        ]);
    }

    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json(['message' => 'Sessió tancada correctament']);
    }
}
