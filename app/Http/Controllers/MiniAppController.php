<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MiniAppController extends Controller
{
    /**
     * Get mini-app detail with screenshots and review info.
     * Public endpoint.
     */
    public function show(int $id): JsonResponse
    {
        $module = Module::where('id', $id)
            ->where('is_active', true)
            ->whereNotNull('module_url')
            ->first();

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'App not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $module->id,
                'name' => $module->name,
                'display_name' => $module->display_name,
                'description' => $module->description,
                'icon' => $module->icon,
                'icon_path' => $module->icon_path ? Storage::disk('public')->url($module->icon_path) : null,
                'module_url' => $module->module_url,
                'version' => $module->version,
                'category' => $module->category,
                'developer_name' => $module->developer_name,
                'developer_url' => $module->developer_url,
                'install_count' => $module->install_count,
                'is_featured' => (bool) $module->is_featured,
                'screenshots' => $module->screenshots ?? [],
                'required_bridge_apis' => $module->required_bridge_apis ?? [],
                'ssl_valid' => (bool) $module->ssl_valid,
                'url_loads' => (bool) $module->url_loads,
                'bridge_detected' => (bool) $module->bridge_detected,
                'last_preflight_status' => $module->last_preflight_status,
            ],
        ]);
    }

    /**
     * Run automated pre-flight check on a mini-app URL.
     * Admin endpoint.
     */
    public function preflight(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url',
        ]);

        $url = $validated['url'];
        $result = [
            'ssl_valid' => false,
            'url_loads' => false,
            'load_time_ms' => 0,
            'status_code' => 0,
            'content_type' => null,
            'errors' => [],
        ];

        // Check SSL
        $parsedUrl = parse_url($url);
        $result['ssl_valid'] = ($parsedUrl['scheme'] ?? '') === 'https';

        if (!$result['ssl_valid']) {
            $result['errors'][] = 'URL must use HTTPS.';
            return response()->json(['success' => true, 'data' => $result]);
        }

        // Try to load the URL
        try {
            $start = microtime(true);
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'OPOOBO-MiniApp-Checker/1.0',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($url);
            $elapsed = (int) ((microtime(true) - $start) * 1000);

            $result['load_time_ms'] = $elapsed;
            $result['status_code'] = $response->status();
            $result['url_loads'] = $response->successful();
            $result['content_type'] = $response->header('Content-Type');

            if (!$response->successful()) {
                $result['errors'][] = "URL returned HTTP {$result['status_code']}.";
            }

            if ($elapsed > 5000) {
                $result['errors'][] = "Slow load time: {$elapsed}ms (warn if > 5000ms).";
            }

            $ct = $result['content_type'] ?? '';
            if (strpos($ct, 'text/html') === false && strpos($ct, 'application/xhtml') === false) {
                $result['errors'][] = "Content-Type is not HTML: {$ct}";
            }
        } catch (\Exception $e) {
            $result['errors'][] = 'Failed to load URL: ' . $e->getMessage();
        }

        Log::info('Mini-app preflight check', [
            'url' => $url,
            'result' => $result,
        ]);

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Admin review: approve or reject a mini-app.
     */
    public function review(Request $request, int $id): JsonResponse
    {
        $module = Module::findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $module->update([
            'is_active' => $validated['action'] === 'approve',
            'review_status' => $validated['action'] === 'approve' ? 'approved' : 'rejected',
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => session('developer_name') ?? 'admin',
        ]);

        Log::info('Mini-app reviewed', [
            'module_id' => $module->id,
            'action' => $validated['action'],
            'reviewed_by' => session('developer_name'),
        ]);

        return response()->json([
            'success' => true,
            'message' => "\"{$module->display_name}\" {$validated['action']}d.",
        ]);
    }

    /**
     * Admin marks that they tested the app.
     */
    public function adminTest(int $id): JsonResponse
    {
        $module = Module::findOrFail($id);

        $module->update([
            'admin_tested' => true,
            'admin_tested_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test recorded.',
        ]);
    }

    /**
     * Developer withdraws a submission.
     */
    public function destroy(int $id): JsonResponse
    {
        $module = Module::findOrFail($id);

        if ($module->developer_id !== session('developer_id')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $module->delete();

        return response()->json([
            'success' => true,
            'message' => "\"{$module->display_name}\" withdrawn.",
        ]);
    }
}
