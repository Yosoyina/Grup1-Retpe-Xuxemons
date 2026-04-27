<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Amigos;
use App\Models\Peticiones_amistad;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

/**
 * Controlador intern de la lògica d'amistats.
 *
 * Conté la lògica de negoci per crear, acceptar, rebutjar i eliminar amistats.
 * No és cridat directament per les rutes: és un servei intern usat per PeticionesAmistadController.
 */
class AmigosController extends Controller
{
    // Crea una nova petició d'amistat del remitent cap al destinatari
    public function crearPeticion(User $remitente, int $destinatarioId): Peticiones_amistad
    {
        if ($remitente->id === $destinatarioId) {
            throw ValidationException::withMessages([
                'destinatarioId' => ['No puedes añadirte a ti mismo como amigo.'],
            ]);
        }

        $alreadyFriends = Amigos::where('user_id', $remitente->id)
            ->where('id_amigo', $destinatarioId)
            ->exists();

        if ($alreadyFriends) {
            throw ValidationException::withMessages([
                'destinatarioId' => ['Ya sois amigos.'],
            ]);
        }

        $existing = Peticiones_amistad::where(function ($q) use ($remitente, $destinatarioId) {
            $q->where('id_remitente', $remitente->id)->where('id_destinatario', $destinatarioId);
        })->orWhere(function ($q) use ($remitente, $destinatarioId) {
            $q->where('id_remitente', $destinatarioId)->where('id_destinatario', $remitente->id);
        })->whereIn('estado', ['pendiente', 'aceptado'])->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'destinatarioId' => ['Ya existe una solicitud pendiente o ya sois amigos.'],
            ]);
        }

        return Peticiones_amistad::create([
            'id_remitente'   => $remitente->id,
            'id_destinatario' => $destinatarioId,
            'estado'      => 'pendiente',
        ]);
    }

    // Accepta una petició d'amistat pendent i crea l'amistat bidireccional
    public function aceptarPeticion(User $user, int $requestId): void
    {
        $request = Peticiones_amistad::where('id', $requestId)
            ->where('id_destinatario', $user->id)
            ->where('estado', 'pendiente')
            ->firstOrFail();

        DB::transaction(function () use ($request) {
            $request->update(['estado' => 'aceptado']);

            // Amistad bidireccional
            Amigos::insert([
                ['user_id' => $request->id_remitente, 'id_amigo' => $request->id_destinatario, 'created_at' => now(), 'updated_at' => now()],
                ['user_id' => $request->id_destinatario, 'id_amigo' => $request->id_remitente,  'created_at' => now(), 'updated_at' => now()],
            ]);
        });
    }

    // Rebutja una petició d'amistat pendent
    public function rechazarPeticion(User $user, int $requestId): void
    {
        $request = Peticiones_amistad::where('id', $requestId)
            ->where('id_destinatario', $user->id)
            ->where('estado', 'pendiente')
            ->firstOrFail();

        $request->update(['estado' => 'rechazado']);
    }

    // Retorna totes les peticions d'amistat pendents rebudes per l'usuari
    public function listarPendientes(User $user)
    {
        return Peticiones_amistad::with('remitente')
            ->where('id_destinatario', $user->id)
            ->where('estado', 'pendiente')
            ->latest()
            ->get();
    }

    // Retorna la llista d'amics de l'usuari
    public function listarAmigos(User $user)
    {
        return $user->friends()->get();
    }

    // Elimina l'amistat entre dos usuaris (en les dues direccions)
    public function eliminarAmigo(User $user, int $friendId): void
    {
        Amigos::where('user_id', $user->id)->where('id_amigo', $friendId)->delete();
        Amigos::where('user_id', $friendId)->where('id_amigo', $user->id)->delete();
    }
}
