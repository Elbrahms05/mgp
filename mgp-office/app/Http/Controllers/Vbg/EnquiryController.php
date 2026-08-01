<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vbg;

use App\Domains\Vbg\Models\VbgEnquiry;
use App\Domains\Vbg\Models\VbgReport;
use App\Domains\Vbg\Services\WorldBankNotificationService;
use App\Http\Controllers\Controller;
use App\Settings\SlaSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    public function __construct(
        private readonly SlaSettings $settings,
        private readonly WorldBankNotificationService $worldBank,
    ) {}

    public function create(VbgReport $report): View
    {
        return view('pages.vbg.enquiry', compact('report'));
    }

    public function store(Request $request, VbgReport $report): RedirectResponse
    {
        $data = $request->validate([
            'findings'        => ['nullable', 'string', 'max:3000'],
            'recommendations' => ['nullable', 'string', 'max:2000'],
        ]);

        VbgEnquiry::updateOrCreate(
            ['vbg_report_id' => $report->id],
            [
                'opened_at'    => now(),
                'deadline_at'  => now()->addDays($this->settings->vbg_enquiry_days),
                'findings'     => $data['findings'],
                'recommendations' => $data['recommendations'],
                'opened_by'    => $request->user()->id,
                'project_link_confirmed' => false,
            ],
        );

        // Notification Banque mondiale (PEES)
        $this->worldBank->notifyPees($report);

        return redirect()
            ->route('vbg.show', $report)
            ->with('success', "Commission d'enquête ouverte. Délai : {$this->settings->vbg_enquiry_days} jours. Banque mondiale notifiée.");
    }
}
