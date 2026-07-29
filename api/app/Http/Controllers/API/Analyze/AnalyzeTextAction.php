<?php

namespace App\Http\Controllers\API\Analyze;

use App\Http\Requests\API\Analyze\AnalyzeTextRequest;
use App\Jobs\AnalyzeTextJob;
use App\Models\AnalysisResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final readonly class AnalyzeTextAction
{
    public function __invoke(AnalyzeTextRequest $request): JsonResponse
    {
        $text = $request->string('text')->toString();
        $user = Auth::user();

        $result = AnalysisResult::create([
            'user_id'   => $user->id,
            'text'      => $text,
            'text_hash' => hash('sha256', trim($text)),
            'status'    => 'pending',
        ]);

        AnalyzeTextJob::dispatch($result);
        return response()->json([
            'message' => 'Текст принят в обработку.',
            'data' => [
                'id'     => $result->id,
                'status' => $result->status,
            ]
        ], Response::HTTP_ACCEPTED);
    }
}
