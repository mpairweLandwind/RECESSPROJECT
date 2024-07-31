<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; 
use Illuminate\Routing\UrlGenerator;

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
    public function boot(UrlGenerator $url)
    {
        // Register view composers
        View::composer('components.analytics', \App\Http\ViewComposers\AnalyticsComposer::class);
        View::composer('components.welcome', \App\Http\ViewComposers\WelcomeComposer::class);
        View::composer('components.reports', \App\Http\ViewComposers\ReportsComposer::class);


        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }
    }
}
