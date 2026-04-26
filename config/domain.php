<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Domain Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your application's multi-domain setup here.
    | Set domains in .env:
    | MAIN_DOMAIN=eschool.test
    | APP_DOMAIN=app.eschool.test
    | AUTH_DOMAIN=auth.eschool.test
    |
    */

    'main' => env('MAIN_DOMAIN', 'localhost'),

    'auth' => env('AUTH_DOMAIN', 'auth.localhost'),

    'app' => env('APP_DOMAIN', 'app.localhost'),

    /*
    |--------------------------------------------------------------------------
    | Domain Aliases
    |--------------------------------------------------------------------------
    |
    | Map domain names to their purposes
    |
    */

    'aliases' => [
        'auth' => env('AUTH_DOMAIN', 'auth.localhost'),
        'app' => env('APP_DOMAIN', 'app.localhost'),
    ],
];
