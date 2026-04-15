<?php
// © Atia Hegazy — atiaeno.com

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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
        // API rate limiting: 100 requests per hour for authenticated users
        RateLimiter::for('api', function (Request $request) {
            return Limit::perHour(100)->by($request->user()?->id ?: $request->ip());
        });

        // Stricter limit for token generation
        RateLimiter::for('api.tokens', function (Request $request) {
            return Limit::perHour(10)->by($request->user()?->id ?: $request->ip());
        });

        Vite::prefetch(concurrency: 3);
    }
}
