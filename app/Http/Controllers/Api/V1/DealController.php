<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyDeal;
use Illuminate\Http\JsonResponse;

class DealController extends Controller
{
    public function index(): JsonResponse
    {
        $deals = DailyDeal::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'label', 'subtitle', 'tag']);

        return response()->json([
            'success' => true,
            'data' => $deals,
        ]);
    }
}
