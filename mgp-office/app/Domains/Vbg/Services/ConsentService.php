<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Services;

use App\Domains\Vbg\Models\VbgConsent;
use App\Domains\Vbg\Models\VbgReport;
use App\Models\User;

class ConsentService
{
    public function recordConsent(VbgReport $report, bool $given, User $by): VbgConsent
    {
        // Met à jour le report lui-même
        $report->consent_given    = $given;
        $report->consent_given_at = $given ? now() : null;
        $report->saveQuietly();

        return VbgConsent::updateOrCreate(
            ['vbg_report_id' => $report->id],
            [
                'consent_given' => $given,
                'recorded_by'   => $by->id,
                'consented_at'  => $given ? now() : null,
                'withdrawn_at'  => null,
            ],
        );
    }

    public function withdraw(VbgReport $report): void
    {
        $report->consent_given    = false;
        $report->consent_given_at = null;
        $report->saveQuietly();

        VbgConsent::where('vbg_report_id', $report->id)->update([
            'consent_given' => false,
            'withdrawn_at'  => now(),
        ]);
    }
}
