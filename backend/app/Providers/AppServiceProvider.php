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
        config([
            'session.domain' => 'localhost',
        ]);
    }
}
