<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class TuserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    /* En la funcion obtenemos el usuario autenticado usando el guard 'api' y verificamos si su rol es 'tuser'. Si no es así, devolvemos una respuesta JSON con un mensaje de acceso denegado y un código de estado 403.
    */

    public function handle(Request $request, Closure $next): Response
    {

        $user = Auth::guard('api')->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Acces denegat'], 403);
        }
        return $next($request);
    }
}
