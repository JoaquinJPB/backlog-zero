<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Games not synced for a week are refreshed from IGDB in the background.
Schedule::command('games:refresh-stale')->dailyAt('04:00')->withoutOverlapping()->onOneServer();
