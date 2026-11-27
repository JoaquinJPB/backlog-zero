<?php

use App\Domain\Users\Support\Username;

test('normalize trims and lowercases', function () {
    expect(Username::normalize('  Ada_Lovelace '))->toBe('ada_lovelace');
});

test('reserved words are detected case-insensitively', function () {
    expect(Username::isReserved('Admin'))->toBeTrue()
        ->and(Username::isReserved('settings'))->toBeTrue()
        ->and(Username::isReserved('ada'))->toBeFalse();
});

test('slugFrom builds a valid candidate from free text', function (string $text, ?string $expected) {
    expect(Username::slugFrom($text))->toBe($expected);
})->with([
    'spaces and accents' => ['José Núñez', 'jose_nunez'],
    'symbols collapse' => ['  Mr. -- Game & Watch!! ', 'mr_game_watch'],
    'truncated to max' => ['a very long display name indeed', 'a_very_long_display'],
    'too short' => ['Al', null],
    'nothing usable' => ['¡¡ !! ??', null],
]);
