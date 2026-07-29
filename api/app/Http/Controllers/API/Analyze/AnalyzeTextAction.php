<?php

namespace App\Http\Controllers\API\Analyze;

use App\Http\Requests\API\Analyze\AnalyzeTextRequest;
use App\Services\LLM\SentimentAnalyzerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class AnalyzeTextAction
{
    public function __construct(private SentimentAnalyzerService $analyzer) {}

    public function __invoke(AnalyzeTextRequest $request): JsonResponse
    {
        try {
            $sentiment = $this->analyzer->analyze($request->string('text')->toString());

            return response()->json([
                'sentiment' => $sentiment->value,
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());

            return response()->json([
                'message' => 'Error processing text analysis',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
