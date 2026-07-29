<?php

namespace App\Http\Controllers\API\Analyze;

use App\Models\AnalysisResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final readonly class AnalyzeStatusAction
{
    public function __invoke(int $id): JsonResponse
    {
        $result = AnalysisResult::where('user_id', Auth::id())->findOrFail($id);

        return response()->json([
            'data' => [
                'id'        => $result->id,
                'status'    => $result->status,
                'sentiment' => $result->sentiment,
            ]
        ], Response::HTTP_OK);
    }
}
