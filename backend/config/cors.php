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
    ],

    'allowed_methods' => ['*'],  // 允許所有 HTTP 方法

    'allowed_origins' => [
        'http://new-project.local:3000',
        'http://localhost:3000',
        'https://waypoint-frontend-zdei.onrender.com'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],  // 允許所有 headers（開發階段先這樣設定）

    'exposed_headers' => [
        'Authorization'  // 允許前端讀取 Authorization header
    ],

    'max_age' => 3600,

    'supports_credentials' => false,  // JWT 不需要 credentials

];
