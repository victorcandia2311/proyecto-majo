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
                $username = $request->input('usuario');
                $isRegistrationCodeRequest = $request->routeIs('admin.register.code');
                $isRegistrationCodeVerification = $request->routeIs('admin.register.code.verify');
                $identity = $isRegistrationCodeRequest
                    ? 'registro-envio:'.$request->session()->getId()
                    : ($isRegistrationCodeVerification
                        ? 'registro-verificacion:'.hash('sha256', (string) $request->session()->get('admin_registration_code_hash', ''))
                        : (is_string($username) && $username !== ''
                            ? 'usuario:'.strtolower(trim($username))
                            : 'email:'.(is_string($email) ? strtolower(trim($email)) : '')));
                $key = hash('sha256', $identity);

                return [
                    Limit::perMinute($ipAttempts)->by('ip:'.$request->ip()),
                    Limit::perMinutes($minutes, $attempts)->by($key),
                ];
            });
        }
    }
}
