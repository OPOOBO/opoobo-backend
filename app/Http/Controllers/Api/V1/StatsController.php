<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $linkedModules = $user->moduleUsers()->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'linked_modules_count' => $linkedModules->count(),
                'modules' => $linkedModules->map(fn ($m) => [
                    'name' => $m->module_name,
                    'linked_at' => $m->linked_at?->toISOString(),
                ]),
                'member_since' => $user->created_at->toISOString(),
                'last_login_at' => $user->last_login_at?->toISOString(),
                'membership_tier' => $user->membership_tier,
                'wallet_balance' => 0,
                'reward_points' => 0,
            ],
        ]);
    }
}
