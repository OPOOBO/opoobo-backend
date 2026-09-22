<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    /**
     * Browse available mini-apps in the store.
     * Public endpoint — no auth required.
     *
     * GET /store?category=finance&featured=1&q=pay
     */
    public function index(Request $request): JsonResponse
    {
        $query = Module::where('is_active', true)
            ->whereNotNull('module_url');

        // Optional filters
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('developer_name', 'like', "%{$search}%");
            });
        }

        $modules = $query->orderBy('sort_order')
            ->orderBy('display_name')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'display_name' => $m->display_name,
                'description' => $m->description,
                'icon' => $m->icon,
                'icon_path' => $m->icon_path ? Storage::disk('public')->url($m->icon_path) : null,
                'module_url' => $m->module_url,
                'version' => $m->version,
                'category' => $m->category,
                'developer_name' => $m->developer_name,
                'developer_url' => $m->developer_url,
                'install_count' => $m->install_count,
                'is_featured' => (bool) $m->is_featured,
                'screenshots' => $m->screenshots ?? [],
                'ssl_valid' => (bool) $m->ssl_valid,
                'url_loads' => (bool) $m->url_loads,
                'last_preflight_status' => $m->last_preflight_status,
            ]);

        // Distinct categories for filter chips
        $categories = Module::where('is_active', true)
            ->whereNotNull('module_url')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'apps' => $modules,
                'categories' => $categories,
            ],
        ]);
    }

    /**
     * Developer submits a new mini-app for review.
     * Public endpoint — no auth required.
     *
     * POST /store/submit
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'module_url' => 'required|url|max:500',
            'developer_name' => 'required|string|max:200',
            'developer_url' => 'nullable|url|max:500',
            'category' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:100',
            'test_mode_url' => 'nullable|url|max:500',
            'screenshots' => 'nullable|array|max:5',
            'screenshots.*' => 'url|max:500',
            'required_bridge_apis' => 'nullable|array',
            'required_bridge_apis.*' => 'string|max:100',
        ]);

        // Auto-generate a slug name from display_name
        $name = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $validated['display_name']), '_'));

        // Check for duplicate names
        $existing = Module::where('name', $name)->first();
        if ($existing) {
            $name = $name . '_' . substr(uniqid(), -4);
        }

        $module = Module::create([
            'name' => $name,
            'display_name' => $validated['display_name'],
            'description' => $validated['description'],
            'icon' => $validated['icon'] ?? 'widgets_rounded',
            'module_url' => $validated['module_url'],
            'test_mode_url' => $validated['test_mode_url'] ?? null,
            'version' => '1.0.0',
            'category' => $validated['category'] ?? null,
            'developer_name' => $validated['developer_name'],
            'developer_url' => $validated['developer_url'] ?? null,
            'install_count' => 0,
            'is_active' => false, // Pending review
            'is_featured' => false,
            'sort_order' => 100,
            'required_fields' => [],
            'screenshots' => $validated['screenshots'] ?? [],
            'required_bridge_apis' => $validated['required_bridge_apis'] ?? [],
            'review_status' => 'pending',
        ]);

        Log::info('Store: new mini-app submitted', [
            'module_id' => $module->id,
            'name' => $module->name,
            'developer' => $module->developer_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'App submitted for review. We\'ll notify you once it\'s approved.',
            'data' => [
                'id' => $module->id,
                'name' => $module->name,
                'status' => 'pending',
            ],
        ], 201);
    }

    /**
     * Increment install count when a user opens the mini-app.
     *
     * POST /store/install/{id}
     */
    public function install(Request $request, int $id): JsonResponse
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

        $module->increment('install_count');

        return response()->json([
            'success' => true,
            'data' => ['install_count' => $module->fresh()->install_count],
        ]);
    }

    /**
     * Check if a newer version is available for a mini-app.
     *
     * GET /store/{name}/version?current=1.0.0
     */
    public function versionCheck(Request $request, string $name): JsonResponse
    {
        $module = Module::where('name', $name)
            ->where('is_active', true)
            ->whereNotNull('module_url')
            ->first();

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'App not found.',
            ], 404);
        }

        $current = $request->input('current', '0.0.0');
        $latest = $module->version ?? '1.0.0';
        $updateAvailable = version_compare($latest, $current, '>');

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $module->name,
                'current_version' => $current,
                'latest_version' => $latest,
                'update_available' => $updateAvailable,
                'module_url' => $module->module_url,
            ],
        ]);
    }
}
