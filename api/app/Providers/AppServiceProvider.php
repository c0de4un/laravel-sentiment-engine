<?php

namespace App\Providers;

use App\Services\LLM\SentimentAnalyzerService;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SentimentAnalyzerService::class, function ($app) {
            return new SentimentAnalyzerService(
                baseUrl: config('services.llm.base_url'),
                apiKey: config('services.llm.api_key'),
                model: config('services.llm.model'),
                systemPrompt: config('services.llm.prompt'),
                cache: $app->make(CacheRepository::class) // <-- Передаем кэш
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
