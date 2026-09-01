<?php

namespace App\Providers;

use App\Services\Imports\PlaceholderProductImageAnalyzer;
use App\Services\Imports\ProductImageAnalyzerInterface;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductImageAnalyzerInterface::class, PlaceholderProductImageAnalyzer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
