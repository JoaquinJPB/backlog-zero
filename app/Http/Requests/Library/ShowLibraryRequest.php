<?php

namespace App\Http\Requests\Library;

use App\Domain\Library\Enums\GameStatus;
use App\Domain\Users\Models\User;
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

    /**
     * Whose section is being viewed: the {user} in the URL, or the
     * signed-in user on the own-section routes (/journal, /library).
     */
    public function profileOwner(): User
    {
        $owner = $this->route('user');

        if ($owner instanceof User) {
            return $owner;
        }

        /** @var User $user */
        $user = $this->user();

        return $user;
    }

    public function statusFilter(): ?GameStatus
    {
        $status = $this->input('status');

        return is_string($status) ? GameStatus::tryFrom($status) : null;
    }
}
