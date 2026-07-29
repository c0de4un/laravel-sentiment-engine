<?php

namespace App\Http\Requests\API\Analyze;

use App\Http\Requests\API\APIRequest;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read string $text
 */
#[OA\Schema(
    schema: 'AnalyzeTextRequest',
    required: ['text'],
    properties: [
        new OA\Property(
            property: 'text',
            description: 'Текст комментария для анализа тональности',
            type: 'string',
            example: 'Этот сервис просто потрясающий, все работает быстро!'
        ),
    ]
)]
final class AnalyzeTextRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }
}
