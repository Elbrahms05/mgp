<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Planification des tâches PACIPA-MGP
|--------------------------------------------------------------------------
*/

// Purge des signalements VBG directs non pris en charge après 72h (conformité ESIRT)
Schedule::command('vbg:purge-stale --hours=72')
    ->everyThreeHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/vbg-purge.log'));

// Analyse quotidienne du niveau de risque des sites
Schedule::command('sites:recalculate-risk')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();

// Vérification SLA + auto-escalade des plaintes (alerte à 80% du délai, escalade si dépassé)
Schedule::command('complaints:check-sla --escalate')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/sla.log'));

// Vérification SLA + auto-escalade N1→N2 des plaintes travailleurs
Schedule::command('workers:check-sla --escalate')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/sla-workers.log'));

// Synchronisation ODK Central → back-office (formulaires non-VBG)
Schedule::command('odk:sync')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/odk-sync.log'));

// Synchronisation formulaires VBG (F03) - log canal dédié
Schedule::command('odk:sync --vbg')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/odk-sync-vbg.log'));
