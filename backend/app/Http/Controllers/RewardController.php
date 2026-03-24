<?php

namespace App\Http\Controllers;

use App\Services\DailyRewardService;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function __construct(private DailyRewardService $dailyRewardService)
    {
    }

    public function claim(Request $request)
    {
        return response()->json(
            $this->dailyRewardService->claimFor($request->user('api')),
            200
        );
    }
}
