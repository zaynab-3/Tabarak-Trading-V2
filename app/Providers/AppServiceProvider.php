<?php

namespace App\Providers;

use App\Services\Imports\GeminiProductImageAnalyzer;
use App\Services\Imports\OpenAiProductImageAnalyzer;
use App\Services\Imports\PlaceholderProductImageAnalyzer;
use App\Services\Imports\ProductImageAnalyzerInterface;
use App\Services\Imports\OcrProductImageAnalyzer;
use App\Services\Imports\ResilientProductImageAnalyzer;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductImageAnalyzerInterface::class, function ($app) {
            $primary = match (config('imports.analyzer')) {
                'gemini' => $app->make(GeminiProductImageAnalyzer::class),
                'openai' => $app->make(OpenAiProductImageAnalyzer::class),
                default => $app->make(PlaceholderProductImageAnalyzer::class),
            };

            return new ResilientProductImageAnalyzer(
                $primary,
                $app->make(OcrProductImageAnalyzer::class),
                $app->make(PlaceholderProductImageAnalyzer::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
