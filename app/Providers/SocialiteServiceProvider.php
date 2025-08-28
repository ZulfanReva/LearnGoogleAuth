<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;

class SocialiteServiceProvider extends ServiceProvider
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
        // Configure Google Socialite with custom Guzzle client for development
        if (app()->environment('local')) {
            Socialite::extend('google', function ($app) {
                $config = $app['config']['services.google'];

                // Create Guzzle client with SSL verification disabled for development
                $httpClient = new Client([
                    'verify' => false,
                    'timeout' => 30,
                ]);

                return Socialite::buildProvider(
                    \Laravel\Socialite\Two\GoogleProvider::class,
                    $config
                )->setHttpClient($httpClient);
            });
        }
    }
}
