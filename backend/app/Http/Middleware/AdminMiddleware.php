<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('api')->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }
        return $next($request);
    }
}
