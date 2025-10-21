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

    'paths' => [
        'api/*',
        'broadcasting/auth',
        'login',
        'logout',
        'register',
        'verify'
    ],

    'allowed_methods' => ['POST', 'GET', 'OPTIONS', 'DELETE', 'PUT', 'PATCH'],

    'allowed_origins' => [
        'http://new-project.local:3000',
        'http://localhost:3000',
        'https://waypoint-frontend-zdei.onrender.com'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',  // JWT Token 最重要！
        'X-Requested-With',
        'Accept',
        'Origin',
        'Access-Control-Request-Method',
        'Access-Control-Request-Headers'
    ],

    'exposed_headers' => [
        'Authorization'  // 允許前端讀取 Authorization header
    ],

    'max_age' => 3600,

    'supports_credentials' => false,  // JWT 不需要 credentials

];
