<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Production-like environments
    |--------------------------------------------------------------------------
    |
    | If app.env is one of these, we treat it as production-like and block
    | APP_DEBUG=true.
    |
    */

    'production_envs' => [
        'production',
        'prod',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hostname patterns
    |--------------------------------------------------------------------------
    |
    | If the current host contains one of these strings, it is considered
    | production-like. Adjust for your own domains.
    |
    */

    'host_contains' => [
        'stellarsecurity.com',
        'stellarsecret.io',
        'stellarsecurity.ch',
    ],

    /*
    |--------------------------------------------------------------------------
    | WEBSITE_SITE_NAME patterns (Azure)
    |--------------------------------------------------------------------------
    |
    | If WEBSITE_SITE_NAME contains one of these strings, it is considered
    | production-like.
    |
    */

    'site_name_contains' => [
        'Prod',
        'prod',
    ],

    /*
    |--------------------------------------------------------------------------
    | Abort message
    |--------------------------------------------------------------------------
    |
    | Message returned when a misconfiguration is detected.
    |
    */

    'abort_message' => 'Stellar hardening blocked request: APP_DEBUG must be false on production-like hosts.',
];
