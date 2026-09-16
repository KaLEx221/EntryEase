<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DEORIS Portal Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for DEORIS portal integration and SSO behavior.
    |
    */

    /**
     * Portal URL - The single identity provider
     */
    'portal_url' => env('DEORIS_PORTAL_URL', 'https://deoris.onrender.com'),

    /**
     * Portal's public key for signature validation
     *
     * Used to verify that tokens were genuinely issued by portal
     * and not tampered with. Should be fetched from portal's JWKS
     * endpoint in production and cached.
     */
    'portal_public_key' => env('DEORIS_PORTAL_PUBLIC_KEY', null),

    /**
     * Session configuration for SSO
     */
    'session' => [
        /**
         * Session lifetime in minutes (independent of token lifetime)
         * Should be shorter or equal to portal session lifetime
         */
        'lifetime' => env('SSO_SESSION_LIFETIME', 120),

        /**
         * Whether to expire session when browser closes
         * Recommended: false for persistent sessions (user won't be logged out after browser restart)
         */
        'expire_on_close' => env('SSO_EXPIRE_ON_CLOSE', false),

        /**
         * Token exchange timeout in seconds
         * Module will wait this long for token exchange to complete
         */
        'exchange_timeout' => 8,

        /**
         * Token validity window in minutes
         * Tokens expire this long after portal issues them
         */
        'token_lifetime' => 5,

        /**
         * Token cleanup interval (minutes)
         * Expired tokens older than this are removed from database
         */
        'token_cleanup_interval' => 10,
    ],

    /**
     * Module iframe behavior
     */
    'iframe' => [
        /**
         * Whether to allow this module to be embedded in iframes
         * Should always be true for DEORIS modules
         */
        'allow_embed' => true,

        /**
         * CSP frame-ancestors directive
         * Only the portal origin should be allowed to embed this module
         */
        'frame_ancestors' => env('DEORIS_PORTAL_URL', 'https://deoris.onrender.com'),
    ],

    /**
     * Logging configuration
     */
    'logging' => [
        /**
         * Whether to log SSO operations
         * Useful for debugging, disable in production for security
         */
        'enabled' => env('SSO_LOGGING', true),

        /**
         * What to log: 'all', 'errors', 'none'
         * 'all' - log every operation
         * 'errors' - only log errors and warnings
         * 'none' - disable SSO logging
         */
        'level' => env('SSO_LOG_LEVEL', 'errors'),

        /**
         * Whether to include user details in logs
         * Set to false in production to avoid logging sensitive data
         */
        'include_user_details' => env('SSO_LOG_USER_DETAILS', false),
    ],
];
