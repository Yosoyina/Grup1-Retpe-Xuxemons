<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Amigos;
use App\Models\Peticiones_amistad;
use Illuminate\Http\JsonResponse;

class PeticionesAmistadController extends Controller
{
    public function __construct(private readonly AmigosController $amigosController) {}

    public function enviarPeticion(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_destinatario' => ['required', 'integer', 'exists:users,id'],
        ]);

        $friendRequest = $this->amigosController->crearPeticion(
            $request->user(),
            $data['id_destinatario']
        );

        return response()->json($friendRequest, 201);
    }

    public function aceptarPeticion(Request $request, int $requestId): JsonResponse
    {
        $this->amigosController->aceptarPeticion($request->user(), $requestId);

        return response()->json(['message' => 'Solicitud aceptada.']);
    }

    public function rechazarPeticion(Request $request, int $requestId): JsonResponse
    {
        $this->amigosController->rechazarPeticion($request->user(), $requestId);

        return response()->json(['message' => 'Solicitud rechazada.']);
    }

    public function peticionesPendientes(Request $request): JsonResponse
    {
        $requests = $this->amigosController->listarPendientes($request->user());

        return response()->json($requests);
    }

    public function listarAmigos(Request $request): JsonResponse
    {
        $friends = $this->amigosController->listarAmigos($request->user());

        return response()->json($friends);
    }

    public function eliminarAmigo(Request $request, int $friendId): JsonResponse
    {
        $this->amigosController->eliminarAmigo($request->user(), $friendId);

        return response()->json(['message' => 'Amigo eliminado.']);
    }
}
