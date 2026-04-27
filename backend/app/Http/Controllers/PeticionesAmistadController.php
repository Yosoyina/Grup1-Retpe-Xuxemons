<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controlador HTTP per al sistema d'amistats.
 *
 * Actua com a capa de presentació: valida les peticions HTTP i delega
 * tota la lògica de negoci a AmigosController.
 */
class PeticionesAmistadController extends Controller
{
    public function __construct(private readonly AmigosController $amigosController) {}

    // Envia una nova petició d'amistat a un altre usuari
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

    // Accepta una petició d'amistat rebuda
    public function aceptarPeticion(Request $request, int $requestId): JsonResponse
    {
        $this->amigosController->aceptarPeticion($request->user(), $requestId);

        return response()->json(['message' => 'Solicitud aceptada.']);
    }

    // Rebutja una petició d'amistat rebuda
    public function rechazarPeticion(Request $request, int $requestId): JsonResponse
    {
        $this->amigosController->rechazarPeticion($request->user(), $requestId);

        return response()->json(['message' => 'Solicitud rechazada.']);
    }

    // Retorna les peticions d'amistat pendents de l'usuari autenticat
    public function peticionesPendientes(Request $request): JsonResponse
    {
        $requests = $this->amigosController->listarPendientes($request->user());

        return response()->json($requests);
    }

    // Retorna la llista d'amics de l'usuari autenticat
    public function listarAmigos(Request $request): JsonResponse
    {
        $friends = $this->amigosController->listarAmigos($request->user());

        return response()->json($friends);
    }

    // Elimina un amic de la llista de l'usuari autenticat
    public function eliminarAmigo(Request $request, int $friendId): JsonResponse
    {
        $this->amigosController->eliminarAmigo($request->user(), $friendId);

        return response()->json(['message' => 'Amigo eliminado.']);
    }
}
