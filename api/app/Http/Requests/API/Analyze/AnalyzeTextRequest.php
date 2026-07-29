<?php

namespace App\Http\Requests\API\Analyze;

use App\Http\Requests\API\APIRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read string     $text
 */
final class AnalyzeTextRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }
}
