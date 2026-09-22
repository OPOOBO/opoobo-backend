<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiChatRequest;
use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;

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
}
