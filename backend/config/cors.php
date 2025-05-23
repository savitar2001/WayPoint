<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['*','api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['POST', 'GET', 'OPTIONS', 'DELETE', 'PUT', 'PATCH'],

    'allowed_origins' => [
        'http://new-project.local:3000', // 保留原有的 (如果還需要)
        'http://localhost:3000'          // 新增前端的 URL
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-CSRF-TOKEN', 'X-Requested-With', 'Authorization', 'Accept','X-XSRF-TOKEN'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
