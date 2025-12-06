<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // PENTING: Fix untuk masalah SSL/HTTPS saat hosting (Rule No. 4)
        // Jika aplikasi mendeteksi sedang di Production (Hosting), paksa gunakan HTTPS.
        // Jika di Localhost, tetap gunakan HTTP agar tidak error sertifikat.
        
        if($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}