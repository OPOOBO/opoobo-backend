<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ModuleController extends Controller
{
    /**
     * Decode an endpoint response that may contain stray output (PHP
     * warnings/HTML) before the actual JSON, which breaks json_decode.
     */
    private function decodeResponse(\Illuminate\Http\Client\Response $response): array
    {
        $body = $response->body();
        $jsonStart = strpos($body, '{');
        if ($jsonStart === false) {
            $jsonStart = strpos($body, '[');
        }
        if ($jsonStart !== false) {
            $body = substr($body, $jsonStart);
        }

        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $modules = Module::orderBy('sort_order')->orderBy('name')->get();

        $result = $modules->map(function ($module) use ($user) {
            $linked = $user->moduleUsers()
                ->where('module_name', $module->name)
                ->where('is_active', true)
                ->first();

            return [
                'id' => $module->id,
                'name' => $module->name,
                'display_name' => $module->display_name,
                'description' => $module->description,
                'icon' => $module->icon,
                'website_url' => $module->website_url,
                'is_active' => $module->is_active,
                'is_featured' => (bool) $module->is_featured,
                'sort_order' => (int) $module->sort_order,
                // Mini-app fields: when module_url is set the app opens it
                // in the WebView container instead of native screens.
                'module_url' => $module->module_url,
                'version' => $module->version,
                'permissions' => $module->permissions,
                'can_update_password' => $module->change_password_endpoint !== null,
                'is_linked' => $linked !== null,
                'module_uid' => $linked?->module_uid,
                'linked_at' => $linked?->linked_at?->toISOString(),
                'required_fields' => $module->required_fields,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => ['modules' => $result],
        ]);
    }

    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $modules = Module::where('is_active', true)
            ->whereNotNull('check_endpoint')
            ->get();

        $results = [];

        foreach ($modules as $module) {
            try {
                $response = Http::timeout(10)
                    ->post($module->api_base_url . $module->check_endpoint, [
                        'email' => $email,
                    ]);

            $data = $this->decodeResponse($response);

            if (!($data['Result'] ?? null) && !isset($data['token'])) {
                Log::error('Module SSO auto-link failed', [
                    'module' => $module->name,
                    'http_status' => $response->status(),
                    'upstream' => $data['ResponseMsg'] ?? $data['message'] ?? 'no message',
                ]);
            }
                $found = isset($data['Result']) && strtolower($data['Result']) === 'true';

                $results[] = [
                    'module' => $module->name,
                    'display_name' => $module->display_name,
                    'exists' => $found,
                    'module_uid' => $data['uid'] ?? null,
                    'name' => $data['name'] ?? null,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'module' => $module->name,
                    'display_name' => $module->display_name,
                    'exists' => false,
                    'module_uid' => null,
                    'name' => null,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => ['results' => $results],
        ]);
    }

    public function link(Request $request): JsonResponse
    {
        $request->validate([
            'module_name' => 'required|string|exists:modules,name',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = $request->user();
        $module = Module::where('name', $request->module_name)->first();

        if (!$module || !$module->check_endpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Module does not support account checking.',
            ], 400);
        }

        // Verify credentials against module API
        try {
            $response = Http::timeout(10)
                ->post($module->api_base_url . $module->check_endpoint, [
                    'email' => $request->email,
                ]);

            $data = $this->decodeResponse($response);
            if (!(isset($data['Result']) && strtolower($data['Result']) === 'true')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email on ' . $module->display_name,
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to ' . $module->display_name . ' service.',
            ], 502);
        }

        // Now verify password by attempting login
        $loginEndpoint = $module->api_base_url . '/user_login.php';
        try {
            $loginResponse = Http::timeout(10)
                ->post($loginEndpoint, [
                    'mobile' => $request->email,
                    'password' => $request->password,
                ]);

            $loginData = $this->decodeResponse($loginResponse);
            if (!(isset($loginData['Result']) && strtolower($loginData['Result']) === 'true')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password for ' . $module->display_name . ' account.',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify credentials with ' . $module->display_name,
            ], 502);
        }

        // Link the account
        ModuleUser::updateOrCreate(
            ['user_id' => $user->id, 'module_name' => $module->name],
            [
                'module_uid' => $data['uid'] ?? $loginData['UserLogin']['id'] ?? '',
                'module_email' => $request->email,
                'is_active' => true,
                'linked_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $module->display_name . ' account linked successfully.',
        ]);
    }

    public function createAccount(Request $request): JsonResponse
    {
        $request->validate([
            'module_name' => 'required|string|exists:modules,name',
            'extra_fields' => 'required|array',
        ]);

        $user = $request->user();
        $module = Module::where('name', $request->module_name)->first();

        if (!$module || !$module->create_endpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Module does not support account creation.',
            ], 400);
        }

        // Build payload using OPOOBO user data + extra fields
        $payload = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $request->input('extra_fields.password', ''),
            'mobile' => $request->input('extra_fields.mobile', $user->phone ?? ''),
            'ccode' => $request->input('extra_fields.ccode', '+234'),
            'user_type' => 'USER',
        ];

        // Add any additional required fields
        $extraFields = $request->input('extra_fields', []);
        foreach ($extraFields as $key => $value) {
            if (!in_array($key, ['password', 'mobile', 'ccode'])) {
                $payload[$key] = $value;
            }
        }

        try {
            $response = Http::timeout(15)
                ->post($module->api_base_url . $module->create_endpoint, $payload);

            $data = $this->decodeResponse($response);
            if (!(isset($data['Result']) && strtolower($data['Result']) === 'true')) {
                return response()->json([
                    'success' => false,
                    'message' => $data['ResponseMsg'] ?? 'Failed to create account on ' . $module->display_name,
                ], 400);
            }

            // Link the newly created account
            $moduleUid = $data['UserLogin']['id'] ?? $data['uid'] ?? '';
            ModuleUser::updateOrCreate(
                ['user_id' => $user->id, 'module_name' => $module->name],
                [
                    'module_uid' => (string) $moduleUid,
                    'module_email' => $user->email,
                    'is_active' => true,
                    'linked_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => $module->display_name . ' account created and linked successfully.',
                'data' => [
                    'module_uid' => $moduleUid,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to ' . $module->display_name . ' service.',
            ], 502);
        }
    }

    public function autoLink(Request $request): JsonResponse
    {
        $user = $request->user();
        $email = $user->email;
        $name = $user->name;
        $phone = $user->phone ?? '';
        $ccode = '+234';
        $idToken = $request->input('id_token');

        // Parse phone for country code if it starts with +
        if ($phone && str_starts_with($phone, '+')) {
            $matches = [];
            if (preg_match('/^\+(\d{1,3})/', $phone, $matches)) {
                $ccode = '+' . $matches[1];
                $phone = substr($phone, strlen($ccode));
            }
        }

        $modules = Module::where('is_active', true)->get();
        $results = [];

        foreach ($modules as $module) {
            // Already linked?
            $existing = $user->moduleUsers()
                ->where('module_name', $module->name)
                ->where('is_active', true)
                ->first();

            if ($existing) {
                $results[] = [
                    'module' => $module->name,
                    'display_name' => $module->display_name,
                    'linked' => true,
                    'status' => 'already_linked',
                    'module_uid' => $existing->module_uid,
                ];
                continue;
            }

            // ── SSO-based modules (bus, market) ──────────────────────
            if ($module->sso_endpoint && $idToken && $module->api_base_url) {
                $result = $this->autoLinkViaSso($module, $idToken, $email);
                $results[] = $result;

                if ($result['linked']) {
                    // Extract UID + token from SSO response (persist
                    // best-effort so a DB hiccup never 500s auto-link).
                    $moduleUid = $result['module_uid'] ?? '';
                    $attrs = [
                        'module_uid' => (string) $moduleUid,
                        'module_email' => $email,
                        'is_active' => true,
                        'linked_at' => now(),
                    ];
                    if (!empty($result['module_token'])) {
                        $attrs['module_token'] = $result['module_token'];
                    }
                    try {
                        ModuleUser::updateOrCreate(
                            ['user_id' => $user->id, 'module_name' => $module->name],
                            $attrs
                        );
                    } catch (\Throwable $e) {
                        Log::error('Module link persist failed', [
                            'module' => $module->name,
                            'error' => $e->getMessage(),
                        ]);
                        $result['linked'] = false;
                        $result['status'] = 'persist_failed';
                    }
                }
                continue;
            }

            // ── API-based modules (check + create) ──────────────────
            if (!$module->check_endpoint || !$module->create_endpoint || !$module->api_base_url) {
                $results[] = [
                    'module' => $module->name,
                    'display_name' => $module->display_name,
                    'linked' => false,
                    'status' => 'not_configured',
                ];
                continue;
            }

            // Step 1: Check if account exists on module
            $moduleUid = null;
            try {
                $checkResponse = Http::timeout(10)
                    ->post($module->api_base_url . $module->check_endpoint, [
                        'email' => $email,
                    ]);

                $checkData = $this->decodeResponse($checkResponse);
                if (isset($checkData['Result']) && strtolower($checkData['Result']) === 'true') {
                    $moduleUid = $checkData['uid'] ?? null;
                }
            } catch (\Exception $e) {
                $results[] = [
                    'module' => $module->name,
                    'display_name' => $module->display_name,
                    'linked' => false,
                    'status' => 'check_failed',
                    'error' => 'Could not check account status',
                ];
                continue;
            }

            // Step 2: If not found, create the account
            if (!$moduleUid) {
                $password = substr(bin2hex(random_bytes(16)), 0, 16);

                $payload = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'mobile' => $phone,
                    'ccode' => $ccode,
                    'user_type' => 'USER',
                ];

                // Add any extra required fields with defaults
                $requiredFields = $module->required_fields ?? [];
                foreach ($requiredFields as $field) {
                    if (!isset($payload[$field]) || $payload[$field] === '') {
                        $payload[$field] = match ($field) {
                            'mobile' => $phone,
                            'ccode' => $ccode,
                            default => '',
                        };
                    }
                }

                try {
                    $createResponse = Http::timeout(15)
                        ->post($module->api_base_url . $module->create_endpoint, $payload);

                    $createData = $this->decodeResponse($createResponse);
                    if (!(isset($createData['Result']) && strtolower($createData['Result']) === 'true')) {
                        $results[] = [
                            'module' => $module->name,
                            'display_name' => $module->display_name,
                            'linked' => false,
                            'status' => 'create_failed',
                            'error' => $createData['ResponseMsg'] ?? 'Account creation failed',
                        ];
                        continue;
                    }

                    $moduleUid = $createData['UserLogin']['id'] ?? $createData['uid'] ?? null;
                } catch (\Exception $e) {
                    $results[] = [
                        'module' => $module->name,
                        'display_name' => $module->display_name,
                        'linked' => false,
                        'status' => 'create_failed',
                        'error' => 'Could not connect to service',
                    ];
                    continue;
                }
            }

            // Step 3: Link the account
            if ($moduleUid) {
                ModuleUser::updateOrCreate(
                    ['user_id' => $user->id, 'module_name' => $module->name],
                    [
                        'module_uid' => (string) $moduleUid,
                        'module_email' => $email,
                        'is_active' => true,
                        'linked_at' => now(),
                    ]
                );

                $results[] = [
                    'module' => $module->name,
                    'display_name' => $module->display_name,
                    'linked' => true,
                    'status' => 'linked',
                    'module_uid' => (string) $moduleUid,
                ];
            } else {
                $results[] = [
                    'module' => $module->name,
                    'display_name' => $module->display_name,
                    'linked' => false,
                    'status' => 'no_uid',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'modules' => $results,
            ],
        ]);
    }

    /**
     * Auto-link a module via its SSO endpoint (bus, market).
     * Sends the id_token to the module's SSO login endpoint.
     */
    private function autoLinkViaSso(Module $module, string $idToken, string $email): array
    {
        try {
            $ssoUrl = rtrim($module->api_base_url, '/') . '/' . ltrim($module->sso_endpoint, '/');

            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($ssoUrl, ['id_token' => $idToken]);

            $data = $this->decodeResponse($response);

            // Bus format: { Result: "true", UserLogin: { id: ... } }
            if (isset($data['Result']) && strtolower($data['Result']) === 'true') {
                $moduleUid = $data['UserLogin']['id'] ?? $data['UserLogin']['uid'] ?? null;
                return [
                    'module' => $module->name,
                    'display_name' => $module->display_name,
                    'linked' => true,
                    'status' => 'linked',
                    'module_uid' => (string) ($moduleUid ?? ''),
                ];
            }

            // Market format: { token: "...", data: { id: ... } }
            if (isset($data['token']) && isset($data['data'])) {
                $moduleUid = $data['data']['id'] ?? null;
                return [
                    'module' => $module->name,
                    'display_name' => $module->display_name,
                    'linked' => true,
                    'status' => 'linked',
                    'module_uid' => (string) ($moduleUid ?? ''),
                    'module_token' => $data['token'],
                ];
            }

            return [
                'module' => $module->name,
                'display_name' => $module->display_name,
                'linked' => false,
                'status' => 'sso_failed',
                'error' => $data['ResponseMsg'] ?? $data['message'] ?? 'SSO login failed',
            ];
        } catch (\Exception $e) {
            return [
                'module' => $module->name,
                'display_name' => $module->display_name,
                'linked' => false,
                'status' => 'sso_failed',
                'error' => 'Could not connect to ' . $module->display_name . ' SSO',
            ];
        }
    }

    public function unlink(Request $request): JsonResponse
    {
        $request->validate([
            'module_name' => 'required|string',
        ]);

        $user = $request->user();
        $deleted = ModuleUser::where('user_id', $user->id)
            ->where('module_name', $request->module_name)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Module unlinked successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No linked module found.',
        ], 404);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'module_name' => 'required|string|exists:modules,name',
            'new_password' => 'required|string|min:6',
        ]);

        $user = $request->user();
        $module = Module::where('name', $request->module_name)->first();
        $moduleUser = $user->moduleUsers()
            ->where('module_name', $request->module_name)
            ->where('is_active', true)
            ->first();

        if (!$moduleUser) {
            return response()->json([
                'success' => false,
                'message' => 'No linked account found for this module.',
            ], 404);
        }

        if (!$module->change_password_endpoint) {
            return response()->json([
                'success' => false,
                'message' => $module->display_name . ' does not support password updates.',
            ], 400);
        }

        try {
            $response = Http::timeout(10)
                ->post($module->api_base_url . $module->change_password_endpoint, [
                    'uid' => $moduleUser->module_uid,
                    'new_password' => $request->new_password,
                ]);

            $data = $this->decodeResponse($response);
            if (!(isset($data['Result']) && strtolower($data['Result']) === 'true')) {
                return response()->json([
                    'success' => false,
                    'message' => $data['ResponseMsg'] ?? 'Failed to update password.',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully on ' . $module->display_name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to ' . $module->display_name . ' service.',
            ], 502);
        }
    }
}
