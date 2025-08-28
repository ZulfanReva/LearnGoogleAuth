<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // For development environment, disable SSL verification for HTTP clients
        if (app()->environment('local')) {
            $this->app->resolving(\GuzzleHttp\Client::class, function ($client) {
                $client->getConfig()['verify'] = false;
            });
        }
    }
}
