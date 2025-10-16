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
        // Register services
        $this->app->bind(\App\Services\PropertyService::class, function ($app) {
            return new \App\Services\PropertyService();
        });

        $this->app->bind(\App\Services\AuthService::class, function ($app) {
            return new \App\Services\AuthService();
        });

        $this->app->bind(\App\Services\AppointmentService::class, function ($app) {
            return new \App\Services\AppointmentService();
        });

        $this->app->bind(\App\Services\InquiryService::class, function ($app) {
            return new \App\Services\InquiryService();
        });

        $this->app->bind(\App\Services\UserService::class, function ($app) {
            return new \App\Services\UserService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}