<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\LLM\SentimentAnalyzerService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SentimentAnalyzerService::class, function ($app) {
            return new SentimentAnalyzerService(
                baseUrl: config('services.llm.base_url'),
                apiKey: config('services.llm.api_key'),
                model: config('services.llm.model'),
                prompt: config('services.llm.prompt'),
            );
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
