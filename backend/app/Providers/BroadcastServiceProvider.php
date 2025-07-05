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
        Broadcast::routes(['middleware' => ['web']]); 
        \Illuminate\Support\Facades\Log::info('BroadcastServiceProvider boot method called.'); // 添加日誌

        require base_path('routes/channels.php');
    }
}
