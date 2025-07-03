<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;

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
        // Force redirect HTTPS to HTTP
        if (Request::secure()) {
            $httpUrl = 'http://' . Request::getHttpHost() . Request::getRequestUri();
            Redirect::to($httpUrl, 301)->send();
            exit; // Prevent further Laravel processing
        }
    }
}
