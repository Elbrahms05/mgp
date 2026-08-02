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

// Sauvegarde chiffrée quotidienne (BD + fichiers), rétention 90 j via config/backup.php (ENF-02)
// Nettoyage d'abord (purge hors rétention), puis nouvelle sauvegarde, puis surveillance de fraîcheur.
Schedule::command('backup:clean')
    ->dailyAt('01:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/backup.log'));

Schedule::command('backup:run')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/backup.log'));

Schedule::command('backup:monitor')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/backup.log'));

// Envoi automatique périodique des rapports aux personnes habilitées (EF-05/EF-14/ER-04)
// Tableau de bord mensuel : le 1er de chaque mois.
Schedule::command('reports:send-periodic --period=monthly')
    ->monthlyOn(1, '06:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/reports.log'));

// Rapport VBG agrégé trimestriel : 1er jan/avr/juil/oct.
Schedule::command('reports:send-periodic --period=quarterly')
    ->quarterlyOn(1, '06:30')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/reports.log'));

// Rapport annuel MGP : 5 janvier.
Schedule::command('reports:send-periodic --period=annual')
    ->yearlyOn(1, 5, '07:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/reports.log'));
