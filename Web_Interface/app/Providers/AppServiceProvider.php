<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ReportService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->singleton(ReportService::class, function ($app) {
            return new ReportService();
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \View::composer('components.analytics', \App\Http\ViewComposers\AnalyticsComposer::class);
        \View::composer('components.welcome', \App\Http\ViewComposers\WelcomeComposer::class);
        \View::composer('components.reports', \App\Http\ViewComposers\ReportsComposer::class);



    }
}
