<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Middleware\TrustHosts;
use Illuminate\Http\Request;

return Application::configure(dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        api:__DIR__.'/../routes/api.php',
    )   
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all proxies for Render deployment
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                    Request::HEADER_X_FORWARDED_HOST |
                    Request::HEADER_X_FORWARDED_PORT |
                    Request::HEADER_X_FORWARDED_PROTO |
                    Request::HEADER_X_FORWARDED_AWS_ELB
        );
        
        // Trust specific hosts
        $middleware->trustHosts(at: [
            'waypoint-backend-122x.onrender.com',
            'localhost',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();