<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Cookie;
use App\Repositories\Notification\NotificationRepositoryInterface;
use App\Repositories\Notification\NotificationRawSqlRepository;
use App\DataMappers\Notification\NotificationMapperInterface; 
use App\DataMappers\Notification\NotificationRawSqlMapper; 
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            NotificationRepositoryInterface::class,
            NotificationRawSqlRepository::class
        );

        $this->app->bind(
            NotificationMapperInterface::class,
            NotificationRawSqlMapper::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        config([
            'session.domain' => env('SESSION_DOMAIN', null), // Use environment variable for session domain
        ]);
    }
}
