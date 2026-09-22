<?php

namespace App\Guards;

use App\Models\User;
use App\Services\KeycloakService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KeycloakGuard implements Guard
{
    private ?User $user = null;
    private bool $hasAttemptedResolve = false;

    public function __construct(
        protected UserProvider $provider,
        protected KeycloakService $keycloak,
        protected Request $request,
    ) {}

    /**
     * Determine if the current user is authenticated.
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Determine if the current user is a guest.
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     */
    public function user(): ?User
    {
        if ($this->hasAttemptedResolve) {
            return $this->user;
        }

        $this->hasAttemptedResolve = true;
        $token = $this->getTokenFromRequest();

        if (!$token) {
            return null;
        }

        try {
            $decoded = $this->keycloak->verifyToken($token);
            $keycloakSub = $decoded->sub ?? null;

            if (!$keycloakSub) {
                return null;
            }

            // Find or create local user
            $this->user = $this->findOrCreateUser($decoded);

            return $this->user;
        } catch (\Exception $e) {
            Log::warning('Keycloak token verification failed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            return null;
        }
    }

    /**
     * Determine if the guard has a user instance.
     * Required by Illuminate\Contracts\Auth\Guard (Laravel 12).
     */
    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    /**
     * Get the ID for the currently authenticated user.
     */
    public function id(): ?string
    {
        $user = $this->user();
        return $user?->getKey();
    }

    /**
     * Set the current user.
     */
    public function setUser($user): void
    {
        $this->user = $user instanceof User ? $user : null;
        $this->hasAttemptedResolve = true;
    }

    /**
     * Validate a user's credentials (not used for Keycloak).
     */
    public function validate(array $credentials = []): bool
    {
        return false;
    }

    /**
     * Validate credentials using the given user (not used for Keycloak).
     */
    public function validateCredentials($user, array $credentials): bool
    {
        return false;
    }

    /**
     * Retrieve the Bearer token from the request.
     */
    private function getTokenFromRequest(): ?string
    {
        $bearer = $this->request->bearerToken();
        if ($bearer) {
            return $bearer;
        }

        // Also check query parameter for WebSocket/EventSource connections
        return $this->request->query('token');
    }

    /**
     * Find an existing user by Keycloak sub, or create one.
     */
    private function findOrCreateUser(object $decoded): User
    {
        $keycloakSub = $decoded->sub;
        $email = $decoded->email ?? null;
        $name = $decoded->name
            ?? trim(($decoded->given_name ?? '') . ' ' . ($decoded->family_name ?? ''))
            ?? $decoded->preferred_username
            ?? 'User';

        // Try to find by keycloak_sub
        $user = User::where('keycloak_sub', $keycloakSub)->first();

        if ($user) {
            // Update last login
            $user->update(['last_login_at' => now()]);
            return $user;
        }

        // Try to find by email (link existing account)
        if ($email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Link Keycloak sub to existing account
                $user->update([
                    'keycloak_sub' => $keycloakSub,
                    'last_login_at' => now(),
                ]);
                return $user;
            }
        }

        // Auto-provision new user
        if (config('keycloak.auto_provision', true)) {
            $user = User::create([
                'keycloak_sub' => $keycloakSub,
                'name' => $name,
                'email' => $email,
                'email_verified_at' => isset($decoded->email_verified) && $decoded->email_verified ? now() : null,
                'phone' => $decoded->phone_number ?? null,
                'last_login_at' => now(),
                'status' => 'active',
            ]);

            return $user;
        }

        return null;
    }

    /**
     * Reset the resolved user so the next call to user() re-evaluates.
     */
    public function resetResolvedUser(): void
    {
        $this->user = null;
        $this->hasAttemptedResolve = false;
    }
}
