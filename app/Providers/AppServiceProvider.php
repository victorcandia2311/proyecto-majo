<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
    public function boot(): void
    {
        foreach (['login' => [5, 1, 20], 'recovery-send' => [1, 1, 5], 'recovery-reset' => [5, 15, 20]] as $name => [$attempts, $minutes, $ipAttempts]) {
            RateLimiter::for($name, function (Request $request) use ($attempts, $minutes, $ipAttempts): array {
                $email = $request->input('email');
                $key = hash('sha256', is_string($email) ? strtolower(trim($email)) : '');

                return [
                    Limit::perMinute($ipAttempts)->by('ip:'.$request->ip()),
                    Limit::perMinutes($minutes, $attempts)->by('email:'.$key),
                ];
            });
        }
    }
}
