<?php

namespace App\Http\Controllers\API\Analyze;

use App\Http\Requests\API\Analyze\AnalyzeTextRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Analyze', description: 'Sentiment Analysis operations')]
final class AnalyzeTextAction
{
    #[OA\Post(
        path: '/api/analyze',
        description: 'Анализ тональности текста с использованием локальной LLM (Qwen 2.5).',
        summary: 'Analyze text sentiment',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/AnalyzeTextRequest')
            )
        ),
        tags: ['Analyze'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Успешный анализ текста',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'sentiment', type: 'string', enum: ['positive', 'negative', 'neutral'], example: 'positive'),
                            new OA\Property(property: 'raw_response', type: 'string', example: 'positive', description: 'Сырой ответ модели (для дебага)'),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Ошибка валидации'
            ),
            new OA\Response(
                response: Response::HTTP_TOO_MANY_REQUESTS,
                description: 'Превышен лимит запросов (Rate Limit)'
            ),
        ]
    )]
    public function __invoke(AnalyzeTextRequest $request): JsonResponse
    {
        $text = $request->string('text')->toString();
        $baseUrl = config('services.llm.base_url', env('LLM_BASE_URL'));
        $apiKey = config('services.llm.api_key', env('LLM_API_KEY'));
        $model = config('services.llm.model', env('LLM_MODEL'));

        // Строгий системный промпт для Qwen
        $systemPrompt = <<<'PROMPT'
Ты — автоматическая система анализа тональности текста.
Твоя задача: проанализировать текст пользователя и определить его тональность.
Ответь ТОЛЬКО одним словом в нижнем регистре: positive, negative или neutral.
Не пиши никаких других слов, знаков препинания или объяснений.
PROMPT;

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60) // Локальная LLM может думать долго
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $text],
                    ],
                    'temperature' => 0.0,
                ]);
            Log::debug('OpenAI response: ' . $response->body());

            if ($response->failed()) {
                return response()->json([
                    'message' => 'LLM service error',
                    'error' => $response->body(),
                ], Response::HTTP_BAD_GATEWAY);
            }

            $llmResult = $response->json('choices.0.message.content');

            $sentiment = strtolower(trim($llmResult));

            return response()->json([
                'sentiment'    => $sentiment,
                'raw_response' => $llmResult, // Оставляем для отладки промптов
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to connect to LLM service',
                'error' => $e->getMessage(),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
