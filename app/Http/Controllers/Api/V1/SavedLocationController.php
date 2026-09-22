<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SavedLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedLocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $locations = $request->user()
            ->savedLocations()
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $validated['user_id'] = $request->user()->id;
        $location = SavedLocation::create($validated);

        return response()->json([
            'success' => true,
            'data' => $location,
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $location = $request->user()->savedLocations()->findOrFail($id);
        $location->delete();

        return response()->json([
            'success' => true,
            'message' => 'Location removed.',
        ]);
    }
}
