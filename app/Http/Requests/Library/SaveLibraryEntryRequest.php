<?php

namespace App\Http\Requests\Library;

use App\Domain\Games\Models\Game;
use App\Domain\Library\Data\LibraryEntryData;
use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Models\LibraryEntry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared by "add to library" (route has {game}) and "edit entry" (route
 * has {entry}). Mirrors LibraryEntryData's invariants with friendly errors.
 */
class SaveLibraryEntryRequest extends FormRequest
{
    private const DATE_FORMAT = 'Y-m-d';

    public function authorize(): bool
    {
        $entry = $this->entry();

        if ($entry !== null) {
            return $this->user()?->can('update', $entry) ?? false;
        }

        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $finishedOn = [
            'nullable',
            'date_format:'.self::DATE_FORMAT,
            'before_or_equal:today',
            Rule::prohibitedIf(fn (): bool => ! ($this->status()?->allowsFinishDate() ?? false)),
        ];

        if ($this->filled('started_on')) {
            $finishedOn[] = 'after_or_equal:started_on';
        }

        return [
            'status' => ['required', Rule::enum(GameStatus::class)],
            'rating' => ['nullable', 'integer', 'between:'.LibraryEntryData::MIN_RATING.','.LibraryEntryData::MAX_RATING],
            'platform_id' => [
                'nullable',
                'integer',
                Rule::exists('game_platform', 'platform_id')->where('game_id', $this->game()->id),
            ],
            'started_on' => ['nullable', 'date_format:'.self::DATE_FORMAT, 'before_or_equal:today'],
            'finished_on' => $finishedOn,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'finished_on.prohibited' => __('validation.library.finish_date_not_allowed'),
        ];
    }

    public function toData(): LibraryEntryData
    {
        return new LibraryEntryData(
            status: GameStatus::from($this->string('status')->toString()),
            rating: $this->filled('rating') ? $this->integer('rating') : null,
            platformId: $this->filled('platform_id') ? $this->integer('platform_id') : null,
            startedOn: $this->date('started_on', self::DATE_FORMAT)?->toImmutable(),
            finishedOn: $this->date('finished_on', self::DATE_FORMAT)?->toImmutable(),
        );
    }

    public function game(): Game
    {
        $game = $this->route('game');

        if ($game instanceof Game) {
            return $game;
        }

        $entry = $this->entry();
        abort_if($entry === null, 404);

        return $entry->game;
    }

    private function entry(): ?LibraryEntry
    {
        $entry = $this->route('entry');

        return $entry instanceof LibraryEntry ? $entry : null;
    }

    private function status(): ?GameStatus
    {
        $status = $this->input('status');

        return is_string($status) ? GameStatus::tryFrom($status) : null;
    }
}
