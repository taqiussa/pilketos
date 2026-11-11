<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\RateLimiter;
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
    public function boot(UrlGenerator $url): void
    {
        if (config('app.env') !== 'local') {
            $url->forceScheme('https');
        } else {
            $url->forceScheme('http');
        }

        RateLimiter::for('pilketos-token', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip().'|'.$request->session()->getId());
        });
    }
}
