<?php

namespace App\Services\LLM;

use App\Enums\Sentiment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final readonly class SentimentAnalyzerService
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
        private string $model,
        private string $systemPrompt
    ) {}

    public function analyze(string $text): Sentiment
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt],
                    ['role' => 'user', 'content' => $text],
                ],
                'temperature' => 0.0,
            ]);

        if ($response->failed()) {
            Log::error('LLM request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new RuntimeException('LLM service unavailable');
        }

        $rawContent = $response->json('choices.0.message.content', '');

        // ЗАЩИТА ОТ ГАЛЛЮЦИНАЦИЙ: ищем строгое совпадение
        if (preg_match('/(positive|negative|neutral)/i', $rawContent, $matches)) {
            return Sentiment::from(strtolower($matches[0]));
        }

        Log::warning('LLM returned unexpected format', ['raw' => $rawContent]);
        return Sentiment::Unknown;
    }
}
