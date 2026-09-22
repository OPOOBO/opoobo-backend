<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key as RSAKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KeycloakService
{
    private string $serverUrl;
    private string $realm;
    private string $clientId;

    /** In-memory memoization so one request never fetches twice. */
    private static ?array $discovery = null;
    private static ?array $jwks = null;

    public function __construct()
    {
        $this->serverUrl = config('keycloak.server_url');
        $this->realm = config('keycloak.realm');
        $this->clientId = config('keycloak.client_id');
    }

    /**
     * Get the OpenID Connect discovery document.
     *
     * Cached on the FILE store (never the database) so a DB/cache
     * outage can't take down authentication. Falls back to a direct
     * fetch if the cache itself is unavailable.
     */
    public function getDiscoveryDocument(): array
    {
        if (self::$discovery !== null) {
            return self::$discovery;
        }

        $cacheKey = 'keycloak_discovery_' . $this->realm;

        try {
            self::$discovery = Cache::store('file')->remember($cacheKey, 86400, function () {
                return $this->fetchDiscoveryDocument();
            });
        } catch (\Throwable $e) {
            Log::warning('Keycloak discovery cache unavailable, fetching directly', [
                'error' => $e->getMessage(),
            ]);
            self::$discovery = $this->fetchDiscoveryDocument();
        }

        return self::$discovery;
    }

    private function fetchDiscoveryDocument(): array
    {
        $url = $this->serverUrl . '/realms/' . $this->realm . '/.well-known/openid-configuration';
        $response = Http::timeout(10)->get($url);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to fetch Keycloak discovery document');
        }

        return $response->json();
    }

    /**
     * Get the JWKS (JSON Web Key Set) from Keycloak.
     *
     * Same file-store + direct-fetch fallback as the discovery doc.
     */
    public function getJwks(): array
    {
        if (self::$jwks !== null) {
            return self::$jwks;
        }

        $cacheKey = 'keycloak_jwks_' . $this->realm;
        $ttl = config('keycloak.jwks_cache_ttl', 3600);

        try {
            self::$jwks = Cache::store('file')->remember($cacheKey, $ttl, function () {
                return $this->fetchJwks();
            });
        } catch (\Throwable $e) {
            Log::warning('Keycloak JWKS cache unavailable, fetching directly', [
                'error' => $e->getMessage(),
            ]);
            self::$jwks = $this->fetchJwks();
        }

        return self::$jwks;
    }

    private function fetchJwks(): array
    {
        $discovery = $this->getDiscoveryDocument();
        $response = Http::timeout(10)->get($discovery['jwks_uri']);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to fetch Keycloak JWKS');
        }

        return $response->json();
    }

    /**
     * Verify and decode a JWT token.
     *
     * @return object The decoded token payload
     */
    public function verifyToken(string $token): object
    {
        $jwks = $this->getJwks();
        $keys = JWK::parseKeySet($jwks);

        // Decode header to find the key ID
        $headerB64 = explode('.', $token)[0] ?? '';
        $header = json_decode(base64_decode(strtr($headerB64, '-_', '+/')) ?: '{}');
        $kid = $header->kid ?? null;

        if (!$kid || !isset($keys[$kid])) {
            throw new \RuntimeException('Unable to find matching key for token');
        }

        /** @var RSAKey $key */
        $key = $keys[$kid];

        $decoded = JWT::decode($token, $key);

        // Verify issuer
        $expectedIssuer = $this->serverUrl . '/realms/' . $this->realm;
        if (isset($decoded->iss) && $decoded->iss !== $expectedIssuer) {
            throw new \RuntimeException('Invalid token issuer');
        }

        // Verify audience (client_id). Keycloak ACCESS tokens carry the
        // client in `azp` (aud is e.g. ["master-realm","account"]), while
        // ID tokens carry it in `aud`. Accept either; if neither claim
        // is present, signature + issuer + expiry still apply.
        $allowed = [];
        if (isset($decoded->aud)) {
            $aud = is_array($decoded->aud) ? $decoded->aud : [$decoded->aud];
            $allowed = array_merge($allowed, $aud);
        }
        if (isset($decoded->azp)) {
            $azp = is_array($decoded->azp) ? $decoded->azp : [$decoded->azp];
            $allowed = array_merge($allowed, $azp);
        }
        if (!empty($allowed) && !in_array($this->clientId, $allowed, true)) {
            throw new \RuntimeException('Invalid token audience');
        }

        return $decoded;
    }

    /**
     * Get the Keycloak user info endpoint.
     */
    public function getUserInfoUrl(): string
    {
        return $this->serverUrl . '/realms/' . $this->realm . '/protocol/openid-connect/userinfo';
    }

    /**
     * Get the authorization endpoint for redirect.
     */
    public function getAuthorizationEndpoint(): string
    {
        $discovery = $this->getDiscoveryDocument();
        return $discovery['authorization_endpoint'];
    }
}
