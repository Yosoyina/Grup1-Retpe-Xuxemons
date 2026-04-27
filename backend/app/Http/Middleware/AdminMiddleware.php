<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware d'administrador.
 *
 * Protegeix les rutes que només poden ser accedides per usuaris amb rol 'admin'.
 * Si l'usuari no està autenticat o no té rol d'admin, retorna un error 403.
 * S'aplica a totes les rutes del grup /api/admin.
 */
class AdminMiddleware
{
    // Comprova que l'usuari autenticat té rol 'admin' abans de continuar
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('api')->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }
        return $next($request);
    }
}
