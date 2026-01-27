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
        $credentialPath = storage_path('app/firebase_credentials.json');
        
        if (file_exists($credentialPath)) {
            putenv("GOOGLE_APPLICATION_CREDENTIALS=$credentialPath");
        }
    }
}
