<?php

namespace App\Http\Requests\Settings;

use App\Domain\Users\Rules\ValidUsername;
use App\Domain\Users\Support\Username;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public const NAME_MAX_LENGTH = 50;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:'.self::NAME_MAX_LENGTH],
            'username' => [
                'required',
                'string',
                new ValidUsername,
                Rule::unique('users', 'username')->ignore($this->user()?->getAuthIdentifier()),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $username = $this->input('username');

        $this->merge(array_filter([
            'name' => is_string($name) ? trim($name) : null,
            'username' => is_string($username) ? Username::normalize($username) : null,
        ], fn (?string $value): bool => $value !== null));
    }
}
