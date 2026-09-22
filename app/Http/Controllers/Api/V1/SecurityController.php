<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\LoginHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function loginHistory(Request $request): JsonResponse
    {
        $history = $request->user()
            ->loginHistory()
            ->orderByDesc('is_current')
            ->orderByDesc('last_active_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    public function revokeSession(Request $request, int $id): JsonResponse
    {
        $session = $request->user()->loginHistory()->findOrFail($id);

        if ($session->is_current) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot revoke your current session.',
            ], 400);
        }

        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Session revoked.',
        ]);
    }

    public function recordLogin(Request $request): JsonResponse
    {
        // Mark previous current sessions as not current
        $request->user()->loginHistory()
            ->where('is_current', true)
            ->update(['is_current' => false]);

        LoginHistory::create([
            'user_id' => $request->user()->id,
            'device_name' => $request->header('User-Agent', 'Unknown'),
            'device_type' => str_contains($request->header('User-Agent', ''), 'Mobile')
                ? 'mobile'
                : 'desktop',
            'ip_address' => $request->ip(),
            'is_current' => true,
            'last_active_at' => now(),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
