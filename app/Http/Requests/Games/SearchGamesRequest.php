<?php

namespace App\Http\Requests\Games;

use App\Domain\Games\Actions\SearchGamesAction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchGamesRequest extends FormRequest
{
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
            'q' => ['nullable', 'string', 'max:'.SearchGamesAction::MAX_QUERY_LENGTH],
        ];
    }

    public function searchQuery(): string
    {
        return trim($this->string('q')->toString());
    }
}
