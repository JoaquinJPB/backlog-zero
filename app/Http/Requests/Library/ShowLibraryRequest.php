<?php

namespace App\Http\Requests\Library;

use App\Domain\Library\Enums\GameStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowLibraryRequest extends FormRequest
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
            'status' => ['nullable', Rule::enum(GameStatus::class)],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function statusFilter(): ?GameStatus
    {
        $status = $this->input('status');

        return is_string($status) ? GameStatus::tryFrom($status) : null;
    }
}
