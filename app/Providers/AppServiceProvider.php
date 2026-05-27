<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        // Register the 'layouts' namespace to resolve layout mapping issues
        // like 'layouts::app' used by Livewire in different versions.
        if (is_dir(resource_path('views/components/layouts'))) {
            View::addNamespace('layouts', resource_path('views/components/layouts'));
        }

        // Forzar HTTPS en URLs y Assets cuando se accede vía ngrok/proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
