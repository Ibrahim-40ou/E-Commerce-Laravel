<?php

namespace App\Providers; 

use App\Mail\Transport\BrevoTransport;
use Illuminate\Support\Facades\Mail;
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
        // Register custom Brevo synchronous driver
        Mail::extend('brevo', function (array $config) {
            return new BrevoTransport($config['key'] ?? env('BREVO_API_KEY'));
        });
    }
}
