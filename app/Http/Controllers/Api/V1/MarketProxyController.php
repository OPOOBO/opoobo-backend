<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ModuleUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketProxyController extends Controller
{
    private const MARKET_BASE = 'https://app.opoobo.market/api';

    private function marketGet(string $endpoint, array $query = [], ?string $token = null): mixed
    {
        try {
            $http = Http::timeout(15);
            if ($token) {
                $http = $http->withToken($token);
            }
            $response = $http->get(self::MARKET_BASE . '/' . ltrim($endpoint, '/'), $query);
            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function marketPost(string $endpoint, array $data = [], ?string $token = null): mixed
    {
        try {
            $http = Http::timeout(15);
            if ($token) {
                $http = $http->withToken($token);
            }
            $response = $http->post(self::MARKET_BASE . '/' . ltrim($endpoint, '/'), $data);
            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getMarketToken(Request $request): ?string
    {
        $user = $request->user();
        $moduleUser = ModuleUser::where('user_id', $user->id)
            ->where('module_name', 'market')
            ->where('is_active', true)
            ->first();
        return $moduleUser?->module_token;
    }

    private function ensureMarketToken(Request $request): ?string
    {
        $token = $this->getMarketToken($request);
        if ($token) return $token;

        $minted = $this->mintMarketToken($request);
        if (!$minted) {
            Log::error('Market auth unavailable', [
                'user' => $request->user()?->id,
                'path' => $request->path(),
            ]);
        }
        return $minted;
    }

    /**
     * Best-effort token for public endpoints: stored token when present,
     * otherwise mint via the request's id_token (also stored for later).
     * Guests simply get null and see the public view.
     */
    private function resolveMarketToken(Request $request): ?string
    {
        $token = $this->getMarketToken($request);
        if ($token) return $token;

        return $this->mintMarketToken($request);
    }

    /**
     * Mint a market Sanctum token via SSO using the request's id_token
     * and persist it on the user's module link.
     */
    private function mintMarketToken(Request $request): ?string
    {
        // Get a new token via SSO
        $user = $request->user();
        $idToken = $request->input('id_token');
        if (!$idToken || !$user) {
            Log::warning('Market mint skipped', [
                'user' => $user?->id,
                'has_id_token' => !empty($idToken),
            ]);
            return null;
        }

        $result = $this->marketPost('sso/login', ['id_token' => $idToken]);
        if (isset($result['token'])) {
            // Persist best-effort: a DB hiccup must never break the
            // current request — the fresh token still works right now.
            try {
                ModuleUser::where('user_id', $user->id)
                    ->where('module_name', 'market')
                    ->update(['module_token' => $result['token']]);
            } catch (\Throwable $e) {
                Log::error('Market token persist failed', [
                    'user' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
            return $result['token'];
        }
        Log::error('Market mint failed', [
            'user' => $user->id,
            'upstream' => is_array($result) ? ($result['message'] ?? 'no message') : 'no response',
        ]);
        return null;
    }

    // ── Public endpoints ──────────────────────────────────────────

    public function home(): JsonResponse
    {
        // Upstream is GET get-home-screen (returns sections config)
        $data = $this->marketGet('get-home-screen');
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function items(Request $request): JsonResponse
    {
        // Upstream is GET get-item. This app is buyer-only: default to
        // approved items (upstream applies NO default status filter).
        $params = $request->only([
            'category_id', 'sub_category_id', 'child_category_id',
            'custom_fields', 'sort_by', 'price_min', 'price_max',
            'latitude', 'longitude', 'radius', 'page', 'limit', 'status',
            'excluded_item_id',
        ]);
        $params['status'] = $params['status'] ?? 'approved';
        $token = $this->resolveMarketToken($request);
        $data = $this->marketGet('get-item', $params, $token);
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function itemDetail(Request $request, string $slug): JsonResponse
    {
        // Upstream has no single-item endpoint; get-item accepts id/slug
        // filters (get-item-slug is only a slug listing).
        $token = $this->resolveMarketToken($request);
        $params = is_numeric($slug) ? ['id' => $slug] : ['slug' => $slug];
        $data = $this->marketGet('get-item', $params, $token);
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function categories(): JsonResponse
    {
        $data = $this->marketGet('get-categories');
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function reels(Request $request): JsonResponse
    {
        // Upstream is GET get-reels
        $data = $this->marketGet('get-reels', $request->only(['page']));
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function jobs(Request $request): JsonResponse
    {
        $data = $this->marketGet('get-item', ['category_id' => 'jobs', 'status' => 'approved']);
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function search(Request $request): JsonResponse
    {
        // Upstream is GET get-item (approved only, like items()).
        $params = $request->only(['search', 'page', 'limit', 'status']);
        $params['status'] = $params['status'] ?? 'approved';
        $data = $this->marketGet('get-item', $params);
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    // ── Auth-required endpoints ───────────────────────────────────

    public function notifications(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        // Upstream is GET get-notification-list
        $data = $this->marketGet('get-notification-list', [], $token);
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function myItems(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        // Upstream is GET my-items
        $data = $this->marketGet('my-items', $request->only(['page']), $token);
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function postItem(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        $data = $this->marketPost('add-item', $request->all(), $token);
        return response()->json($data ?? ['error' => 'Failed to post item']);
    }

    public function paymentIntent(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        $data = $this->marketPost('payment-intent', $request->all(), $token);
        return response()->json($data ?? ['error' => 'Payment failed']);
    }

    public function chatList(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        // Upstream is GET chat-list (type=buyer|seller)
        $data = $this->marketGet('chat-list', $request->only(['page', 'type', 'item_id', 'search', 'item_offer_id']), $token);
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function chatMessages(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        // Upstream is GET chat-messages
        $data = $this->marketGet('chat-messages', $request->only(['item_offer_id', 'page']), $token);
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        // Upstream is POST send-message (text only; no file upload via proxy)
        $data = $this->marketPost('send-message', $request->only(['item_offer_id', 'message']), $token);
        return response()->json($data ?? ['error' => 'Failed to send']);
    }

    public function itemOffer(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        // Upstream is POST item-offer
        $data = $this->marketPost('item-offer', $request->only(['item_id', 'amount']), $token);
        return response()->json($data ?? ['error' => 'Failed to create offer']);
    }

    public function offerList(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        // Buyer-only: this app never sells, so always the buyer's offers.
        // Upstream is GET item-offer-list.
        $data = $this->marketGet('item-offer-list', ['type' => 'buyer'] + $request->only(['search']), $token);
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function reportReasons(): JsonResponse
    {
        // Upstream is GET get-report-reasons (public).
        $data = $this->marketGet('get-report-reasons');
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }

    public function reportItem(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        // Upstream is POST add-reports.
        $data = $this->marketPost('add-reports', $request->only(['item_id', 'report_reason_id', 'other_message']), $token);
        return response()->json($data ?? ['error' => 'Failed to report']);
    }

    public function submitReview(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        // Upstream is POST add-item-review. Note: upstream only accepts
        // reviews from buyers of sold-out items; its message is surfaced.
        $data = $this->marketPost('add-item-review', $request->only(['item_id', 'ratings', 'review']), $token);
        return response()->json($data ?? ['error' => 'Failed to submit review']);
    }

    public function jobApply(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        $data = $this->marketPost('job-apply', $request->all(), $token);
        return response()->json($data ?? ['error' => 'Failed to apply']);
    }

    public function favourite(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        $data = $this->marketPost('manage-favourite', $request->all(), $token);
        return response()->json($data ?? ['error' => 'Failed']);
    }

    public function favourites(Request $request): JsonResponse
    {
        $token = $this->ensureMarketToken($request);
        if (!$token) {
            return response()->json(['error' => 'Market auth required'], 401);
        }
        // Upstream is GET get-favourite-item — same table the main app reads,
        // so hearts sync both ways automatically.
        $data = $this->marketGet('get-favourite-item', $request->only(['page', 'limit']), $token);
        return response()->json($data ?? ['error' => 'Failed to fetch']);
    }
}
