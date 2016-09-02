<?php

namespace NotificationChannels\Gammu;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client as HttpClient;

class GammuServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->when(GammuChannel::class)
            ->give(function () {
                return new GammuChannel(new HttpClient);
            });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
