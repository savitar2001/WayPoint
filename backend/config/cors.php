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

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // 包含需要 CORS 的路徑

    'allowed_methods' => ['*'], // 允許所有 HTTP 方法

    'allowed_origins' => [
        'http://new-project.local:3000', // 保留原有的 (如果還需要)
        'http://localhost:3000',          // 新增前端的 URL
        'https://waypoint-frontend-zdei.onrender.com' // 允許的前端網域
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // 允許所有標頭

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // 啟用 Cookie 傳遞

];
