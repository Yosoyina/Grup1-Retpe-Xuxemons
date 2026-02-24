<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ── RUTES PUBLICAS ───────────────────────────────────────
Route::post('/register',[AuthController::class, 'register']);
Route::post('/login',[AuthController::class, 'login']);

// ── RUTES PROTEGIDAS ──────────────────────────────────────
Route::middleware('auth:api')->group(function () {

    Route::post('/logout',[AuthController::class, 'logout']);

    Route::get('/home',[UserController::class, 'home']);
    Route::get('/profile',[UserController::class, 'perfil']);
    Route::put('/profile',[UserController::class, 'updatePerfil']);
    Route::delete('/profile',[UserController::class, 'eliminarUsuario']);

});
