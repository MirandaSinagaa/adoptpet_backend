<?php

return [

/*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // PERBAIKAN: Gunakan env() agar dinamis, atau '*' untuk mengizinkan semua (sementara)
    'allowed_origins' => [env('CORS_ALLOWED_ORIGINS', '*')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
