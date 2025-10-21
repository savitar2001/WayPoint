<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // JWT 認證：不使用 Broadcast::routes()，改在 routes/api.php 中手動註冊
        // 這樣可以使用 'auth:api' middleware 而不是 'web' middleware
        \Illuminate\Support\Facades\Log::info('BroadcastServiceProvider boot method called (JWT mode).');

        require base_path('routes/channels.php');
    }
}
