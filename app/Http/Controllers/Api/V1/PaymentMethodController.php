<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $methods = $request->user()
            ->paymentMethods()
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:card,bank',
            'provider' => 'required|string|max:50',
            'last_four' => 'required|string|max:4',
            'expiry_month' => 'nullable|string|max:2',
            'expiry_year' => 'nullable|string|max:4',
            'bank_name' => 'nullable|string|max:100',
            'account_number_masked' => 'nullable|string|max:20',
        ]);

        $validated['user_id'] = $request->user()->id;

        if ($request->boolean('is_default')) {
            $request->user()->paymentMethods()->update(['is_default' => false]);
        }

        $method = PaymentMethod::create($validated);

        return response()->json([
            'success' => true,
            'data' => $method,
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $method = $request->user()->paymentMethods()->findOrFail($id);
        $method->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment method removed.',
        ]);
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $request->user()->paymentMethods()->update(['is_default' => false]);
        $method = $request->user()->paymentMethods()->findOrFail($id);
        $method->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default payment method updated.',
        ]);
    }
}
