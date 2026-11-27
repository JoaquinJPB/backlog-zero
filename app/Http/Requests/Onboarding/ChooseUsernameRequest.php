<?php

namespace App\Http\Requests\Onboarding;

use App\Domain\Users\Rules\ValidUsername;
use App\Domain\Users\Support\Username;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChooseUsernameRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only users still in onboarding may pick a username here; changing it
        // later goes through the profile settings.
        return $this->user()?->username === null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', new ValidUsername, Rule::unique('users', 'username')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $username = $this->input('username');

        if (is_string($username)) {
            $this->merge(['username' => Username::normalize($username)]);
        }
    }
}
