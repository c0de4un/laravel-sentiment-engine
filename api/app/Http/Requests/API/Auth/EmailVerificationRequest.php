<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\API\APIRequest;
use App\Models\User;

/**
 * @property-read string  $id
 * @property-read string  $hash
 */
final class EmailVerificationRequest extends APIRequest
{
    public function authorize(): bool
    {
        if (!$this->hasValidSignature()) {
            return false;
        }

        $user = User::findOrFail($this->route('id'));
        if (!hash_equals((string) $this->route('hash'), sha1($user->getEmailForVerification()))) {
            return false;
        }

        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $this->setUserResolver(fn () => $user);

        return true;
    }
    public function rules(): array
    {
        return [
            'id'   => ['required', 'integer'], // Пример: строго число
            'hash' => ['required', 'string', 'size:40'], // sha1 всегда 40 символов
        ];
    }

    public function fulfill(): void
    {
        $user = $this->user();
        $user->markEmailAsVerified();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id'   => $this->route('id'),
            'hash' => $this->route('hash'),
        ]);
    }

}
