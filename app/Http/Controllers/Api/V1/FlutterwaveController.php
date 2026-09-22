<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\FlutterwaveAuthorization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveController extends Controller
{
    private string $secretKey;
    private string $baseUrl = 'https://api.flutterwave.com/v3';

    public function __construct()
    {
        $this->secretKey = config('services.flutterwave.secret_key', '');
    }

    /**
     * Store a Flutterwave authorization after a successful payment.
     * Called from the client after Flutterwave charge succeeds.
     */
    public function storeAuthorization(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'authorization_code' => 'required|string',
            'card_type' => 'required|string',
            'last_four' => 'required|string|max:4',
            'exp_month' => 'nullable|string',
            'exp_year' => 'nullable|string',
            'bank_name' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        // Upsert — don't duplicate same authorization for same user
        $auth = FlutterwaveAuthorization::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'authorization_code' => $validated['authorization_code'],
            ],
            $validated
        );

        return response()->json([
            'success' => true,
            'data' => $auth,
        ]);
    }

    /**
     * List saved authorizations for the user.
     */
    public function index(Request $request): JsonResponse
    {
        $authorizations = $request->user()
            ->flutterwaveAuthorizations()
            ->where('is_reusable', true)
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $authorizations,
        ]);
    }

    /**
     * Charge a saved authorization (tokenized payment).
     * Uses Flutterwave's /charges endpoint with authorization_code.
     */
    public function chargeSaved(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'authorization_code' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|max:3',
            'email' => 'required|email',
            'tx_ref' => 'required|string',
        ]);

        if (empty($this->secretKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Flutterwave secret key not configured.',
            ], 500);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/charges", [
                'authorization_code' => $validated['authorization_code'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'email' => $validated['email'],
                'tx_ref' => $validated['tx_ref'],
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? '') === 'success') {
                return response()->json([
                    'success' => true,
                    'data' => $body['data'] ?? null,
                    'message' => 'Payment successful.',
                ]);
            }

            Log::warning('Flutterwave charge failed', ['response' => $body]);

            return response()->json([
                'success' => false,
                'message' => $body['message'] ?? 'Payment failed.',
                'data' => $body['data'] ?? null,
            ], 400);
        } catch (\Exception $e) {
            Log::error('Flutterwave charge error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Payment gateway error. Please try again.',
            ], 502);
        }
    }

    /**
     * Verify a transaction by tx_ref.
     */
    public function verifyTransaction(Request $request, string $txRef): JsonResponse
    {
        if (empty($this->secretKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Flutterwave secret key not configured.',
            ], 500);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])->get("{$this->baseUrl}/transactions/verify_by_reference", [
                'tx_ref' => $txRef,
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? '') === 'success') {
                return response()->json([
                    'success' => true,
                    'data' => $body['data'] ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Transaction not found or not successful.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed.',
            ], 502);
        }
    }

    /**
     * Delete a saved authorization.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $auth = $request->user()->flutterwaveAuthorizations()->findOrFail($id);
        $auth->delete();

        return response()->json([
            'success' => true,
            'message' => 'Authorization removed.',
        ]);
    }
}
