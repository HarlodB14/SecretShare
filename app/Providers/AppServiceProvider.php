<?php

namespace App\Providers;

use App\Contracts\SecretServiceInterface;
use App\Services\SecretService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SecretServiceInterface::class, SecretService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
