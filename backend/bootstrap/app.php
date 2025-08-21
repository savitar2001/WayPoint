<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

return Application::configure(dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        api:__DIR__.'/../routes/api.php',
    )   
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                    Request::HEADER_X_FORWARDED_HOST |
                    Request::HEADER_X_FORWARDED_PORT |
                    Request::HEADER_X_FORWARDED_PROTO |
                    Request::HEADER_X_FORWARDED_AWS_ELB
        );
        
        // 確保 CSRF 驗證正確處理
        $middleware->validateCsrfTokens(except: [
            // 如果需要，可以在這裡排除某些路由
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();