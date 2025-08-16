<?php
use Illuminate\Http\Request;

return [
    'proxies' => '*', // Trust all proxies

    'headers' => Request::HEADER_X_FORWARDED_FOR |
                 Request::HEADER_X_FORWARDED_HOST |
                 Request::HEADER_X_FORWARDED_PROTO |
                 Request::HEADER_X_FORWARDED_PORT |
                 Request::HEADER_X_FORWARDED_TRAFFIC,
];
