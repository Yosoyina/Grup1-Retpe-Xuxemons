<?php

namespace App\Http\Controllers;

use App\Services\DailyRewardService;
use Illuminate\Http\Request;

/**
 * Controlador de recompenses diàries.
 *
 * Gestiona la reclamació de la recompensa diària de l'usuari.
 * Delega tota la lògica al DailyRewardService.
 */
class RewardController extends Controller
{
    public function __construct(private DailyRewardService $dailyRewardService)
    {
    }

    // Reclama la recompensa diària de l'usuari. Retorna error si ja ha estat reclamada avui
    public function claim(Request $request)
    {
        return response()->json(
            $this->dailyRewardService->claimFor($request->user('api')),
            200
        );
    }
}
