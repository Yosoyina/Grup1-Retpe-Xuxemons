<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\XuxemonsController;
use App\Http\Controllers\InventarioController;

// ── RUTES PUBLIQUES ───────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ── RUTES PROTEGIDES ──────────────────────────────────────
Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/home', [UserController::class, 'home']);
    Route::get('/profile', [UserController::class, 'perfil']);
    Route::put('/profile', [UserController::class, 'updatePerfil']);
    Route::delete('/profile', [UserController::class, 'eliminarUsuario']);
    Route::post('/reward', [RewardController::class, 'claim']);

    // ── XUXEDEX ROUTES ───────────────────────────────────
    Route::get('/xuxedex', [XuxemonsController::class, 'getUserXuxedex']);
    //Route::apiResource('/xuxemons', XuxemonsController::class);

    // ── POKEDEX DE XUXEMONS ───────────────────────────────────
    Route::get('/xuxemons', [XuxemonsController::class, 'index']);
    Route::get('/xuxemons/{id}', [XuxemonsController::class, 'show']);
    Route::get('/xuxemons/{id}/evolucions', [XuxemonsController::class, 'Evoluciones']);
    Route::post('/xuxemons/{id}/evolucionar', [XuxemonsController::class, 'evolucionar']);
    Route::post('/xuxemons/{id}/feed', [XuxemonsController::class, 'feed']);
    Route::post('/xuxemons/{id}/curar', [XuxemonsController::class, 'curar']);

    // ── iNVENTARIO DEL JUGADOR ───────────────────────────────────

    Route::get('/inventario', [InventarioController::class, 'index']);
    Route::get('/inventario/{id}', [InventarioController::class, 'show']);
    Route::get('/xuxes', [InventarioController::class, 'listXuxes']);

    // ── RUTES ADMIN ───────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->group(function () {
 
        // ── USUARIOS ───────────────────────────────────────
        Route::get('/usuarios', [UserController::class, 'listUsers']);
        Route::put('/usuarios/{id}/toggle', [UserController::class, 'toggleActiu']);

        // ── POKEDEX DE XUXEMONS ADMIN ───────────────────────────────────
        Route::get('/xuxedex', [XuxemonsController::class, 'getAdminXuxedex']);
        Route::post('/xuxedex', [XuxemonsController::class, 'addXuxemonToUser']);
 
        // ── POKEDEX DE XUXEMONS ADMIN ───────────────────────────────────
        Route::post('/xuxemons', [XuxemonsController::class, 'store']);
        Route::put('/xuxemons/{id}', [XuxemonsController::class, 'update']);
        Route::delete('/xuxemons/{id}', [XuxemonsController::class, 'destroy']);
 
        // ── INVENTARIO DEL JUGADOR (ADMIN) ───────────────────────────────────
        Route::get('/inventario/items', [InventarioController::class, 'listadosItems']);
        Route::post('/inventario', [InventarioController::class, 'store']);
        Route::put('/inventario/{id}', [InventarioController::class, 'update']);
        Route::delete('/inventario/{id}', [InventarioController::class, 'destroy']);
    });

});
