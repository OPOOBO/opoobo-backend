<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RewardsController extends Controller
{
    /**
     * Placeholder rewards summary. Accrual and redemption are not implemented.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'points' => 2840,
                'tier' => 'Silver',
                'next_tier' => 'Gold',
                'next_tier_points' => 5000,
                'progress' => 0.57,
                'earned_label' => '2,840 pts earned',
                'goal_label' => '5,000 pts to Gold',
            ],
        ]);
    }
}
