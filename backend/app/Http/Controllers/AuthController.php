<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Xuxemons;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
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

        // Per cada tipus: agafar exactament 6 xuxemons aleatoris del catàleg
        // i assignar-los tots al jugador: alguns capturats, la resta bloquejats
        foreach (['Aigua', 'Terra', 'Aire'] as $tipus) {
            $sis = Xuxemons::where('tipo_elemento', $tipus)
                ->inRandomOrder()
                ->limit(6)
                ->get();

            // Nombre aleatori de desbloquejats: entre 1 i 5 (mai 0 ni tots 6)
            $numDesbloquejats = rand(1, min(5, $sis->count() - 1));

            foreach ($sis as $index => $xuxemon) {
                DB::table('xuxedex')->insert([
                    'id_usuario' => $user->id,
                    'id_xuxemon' => $xuxemon->id,
                    'esta_capturado' => $index < $numDesbloquejats,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

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

        $user = User::where('id_jugador', $request->id_jugador)->first();

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
