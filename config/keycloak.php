<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Keycloak Server URL
    |--------------------------------------------------------------------------
    | Base URL of the Keycloak server (without trailing slash).
    */
    'server_url' => env('KEYCLOAK_SERVER_URL', 'https://account.opoobo.com'),

    /*
    |--------------------------------------------------------------------------
    | Keycloak Realm
    |--------------------------------------------------------------------------
    */
    'realm' => env('KEYCLOAK_REALM', 'opoobo'),

    /*
    |--------------------------------------------------------------------------
    | Client ID
    |--------------------------------------------------------------------------
    /* The client ID registered in Keycloak for the mobile app. */
    'client_id' => env('KEYCLOAK_CLIENT_ID', 'opoobo-mobile'),

    /*
    |--------------------------------------------------------------------------
    | JWKS Cache TTL (seconds)
    |--------------------------------------------------------------------------
    | How long to cache the JWKS keys before re-fetching.
    */
    'jwks_cache_ttl' => env('KEYCLOAK_JWKS_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Auto-provision users
    |--------------------------------------------------------------------------
    | If true, a local user will be created automatically on first login
    | based on the Keycloak token claims.
    */
    'auto_provision' => env('KEYCLOAK_AUTO_PROVISION', true),
];
