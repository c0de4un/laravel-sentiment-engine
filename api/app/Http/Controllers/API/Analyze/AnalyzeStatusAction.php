<?php

namespace App\Http\Controllers\API\Analyze;

use App\Models\AnalysisResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AnalyzeStatusAction
{
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $result = AnalysisResult::where('user_id', $user->id)->find($id);

        if (!$result) {
            return response()->json(['message' => 'Analysis not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => [
                'id'        => $result->id,
                'status'    => $result->status,
                'sentiment' => $result->sentiment,
            ]
        ], Response::HTTP_OK);
    }
}
