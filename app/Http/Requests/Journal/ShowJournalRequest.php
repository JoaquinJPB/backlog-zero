<?php

namespace App\Http\Requests\Journal;

use App\Domain\Users\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShowJournalRequest extends FormRequest
{
    private const FIRST_YEAR = 1970;

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
            'year' => ['nullable', 'integer', 'between:'.self::FIRST_YEAR.','.now()->year],
            'platform' => ['nullable', 'integer', 'min:1'],
            'abandoned' => ['nullable', 'boolean'],
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

    public function year(): ?int
    {
        return $this->filled('year') ? $this->integer('year') : null;
    }

    public function platformId(): ?int
    {
        return $this->filled('platform') ? $this->integer('platform') : null;
    }

    public function includeAbandoned(): bool
    {
        return $this->boolean('abandoned');
    }
}
