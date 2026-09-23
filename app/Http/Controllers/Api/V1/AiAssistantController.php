<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiChatRequest;
use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function chat(AiChatRequest $request, AiAssistantService $assistant): JsonResponse
    {
        $result = $assistant->chat($request->validated('messages'));

        return response()->json([
            'success' => true,
            'data' => [
                'reply' => $result['reply'],
                'actions' => $result['actions'],
            ],
        ]);
    }

    public function scan(Request $request, AiAssistantService $assistant): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $file = $request->file('image');
        $binary = $file?->get();
        $mime = $file?->getMimeType() ?: 'image/jpeg';

        if (! is_string($binary) || $binary === '') {
            return response()->json([
                'success' => false,
                'message' => 'Product description is unavailable.',
            ], 422);
        }

        $result = $assistant->describeProduct($binary, $mime);
        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Product description is unavailable.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
