<?php

namespace App\Services\LLM;

use App\Enums\Sentiment;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final readonly class SentimentAnalyzerService
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
        private string $model,
        private string $systemPrompt,
        private CacheRepository $cache
    ) {}

    /**
     * @throws ConnectionException
     */
    public function analyze(string $text): Sentiment
    {
        // 1. Нормализуем текст (убираем лишние пробелы по краям)
        $normalizedText = trim($text);

        // 2. Генерируем уникальный ключ (sha256 достаточно быстр и исключает коллизии)
        $cacheKey = 'sentiment:' . hash('sha256', $normalizedText);

        // 3. Пытаемся достать из кэша
        $cachedSentiment = $this->cache->get($cacheKey);
        if ($cachedSentiment !== null) {
            // Если вдруг в кэше лежит невалидное значение (что маловероятно), подстраховываемся
            $sentiment = Sentiment::tryFrom($cachedSentiment);
            if ($sentiment !== null) {
                Log::debug('Sentiment cache HIT', ['key' => $cacheKey]);
                return $sentiment;
            }
        }

        Log::debug('Sentiment cache MISS', ['key' => $cacheKey]);

        // 4. Если в кэше нет — идем в LLM
        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("$this->baseUrl/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt],
                    ['role' => 'user', 'content' => $normalizedText],
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

        if (preg_match('/(positive|negative|neutral)/i', $rawContent, $matches)) {
            $sentiment = Sentiment::from(strtolower($matches[0]));

            // 5. Сохраняем успешный результат в кэш на 30 дней
            $this->cache->put($cacheKey, $sentiment->value, now()->addDays(30));

            return $sentiment;
        }

        Log::warning('LLM returned unexpected format', ['raw' => $rawContent]);
        return Sentiment::Unknown;
    }
}
