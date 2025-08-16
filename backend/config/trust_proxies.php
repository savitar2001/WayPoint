<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Set the trusted proxies for your application. This is useful when your
    | application is behind a load balancer or reverse proxy, ensuring that
    | Laravel correctly detects the client's IP address and HTTPS status.
    |
    */

    'proxies' => '*', // Trust all proxies

    /*
    |--------------------------------------------------------------------------
    | Headers for Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | These headers are used to detect proxy-related information. The default
    | value is suitable for most applications, but you can customize it if
    | needed.
    |
    */

    'headers' => Illuminate\Http\Request::HEADER_X_FORWARDED_ALL,
];
