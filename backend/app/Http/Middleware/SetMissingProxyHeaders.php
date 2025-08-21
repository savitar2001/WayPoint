<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetMissingProxyHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 如果缺少 X-Forwarded-Host，從 Host 標頭設置
        if (!$request->hasHeader('X-Forwarded-Host') && $request->hasHeader('Host')) {
            $request->headers->set('X-Forwarded-Host', $request->getHost());
        }
        
        // 如果缺少 X-Forwarded-Proto，但使用 HTTPS
        if (!$request->hasHeader('X-Forwarded-Proto') && $request->isSecure()) {
            $request->headers->set('X-Forwarded-Proto', 'https');
        }

        return $next($request);
    }
}
