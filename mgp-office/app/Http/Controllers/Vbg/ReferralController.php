<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vbg;

use App\Domains\Vbg\Enums\ReferralServiceType;
use App\Domains\Vbg\Models\VbgReferral;
use App\Domains\Vbg\Models\VbgReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vbg\StoreReferralRequest;
use App\Settings\SlaSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function __construct(
        private readonly SlaSettings $settings,
    ) {}

    public function create(VbgReport $report): View
    {
        $serviceTypes = ReferralServiceType::cases();

        return view('pages.vbg.referral', compact('report', 'serviceTypes'));
    }

    public function store(StoreReferralRequest $request, VbgReport $report): RedirectResponse
    {
        $data = $request->validated();

        VbgReferral::create([
            'vbg_report_id'    => $report->id,
            'service_type'     => $data['service_type'],
            'provider_name'    => $data['provider_name'],
            'provider_contact' => $data['provider_contact'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'referred_at'      => now(),
            'deadline_at'      => now()->addHours($this->settings->vbg_referral_hours),
            'referred_by'      => $request->user()->id,
        ]);

        return redirect()
            ->route('vbg.show', $report)
            ->with('success', "Référencement vers les services enregistré. Délai : {$this->settings->vbg_referral_hours}h.");
    }
}
