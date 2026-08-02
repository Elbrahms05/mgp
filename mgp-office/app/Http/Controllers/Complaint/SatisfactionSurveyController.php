<?php

declare(strict_types=1);

namespace App\Http\Controllers\Complaint;

use App\Domains\Complaint\Models\Complaint;
use App\Domains\Evaluation\Models\SatisfactionSurvey;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Enquête de satisfaction du plaignant à la clôture (EF-08).
 */
class SatisfactionSurveyController extends Controller
{
    public function create(Complaint $complaint): View|RedirectResponse
    {
        if ($complaint->satisfactionSurvey()->exists()) {
            return redirect()
                ->route('complaints.show', $complaint)
                ->with('info', "Une enquête de satisfaction a déjà été enregistrée pour cette plainte.");
        }

        return view('pages.complaints.satisfaction', compact('complaint'));
    }

    public function store(Request $request, Complaint $complaint): RedirectResponse
    {
        $data = $request->validate([
            'overall_rating'    => ['required', 'integer', 'between:1,5'],
            'timeliness_rating' => ['nullable', 'integer', 'between:1,5'],
            'staff_rating'      => ['nullable', 'integer', 'between:1,5'],
            'resolution_rating' => ['nullable', 'integer', 'between:1,5'],
            'would_recommend'   => ['nullable', 'boolean'],
            'comment'           => ['nullable', 'string', 'max:2000'],
            'channel'           => ['required', 'string', 'max:30'],
        ]);

        SatisfactionSurvey::updateOrCreate(
            ['complaint_id' => $complaint->id],
            [
                ...$data,
                'would_recommend' => $request->boolean('would_recommend'),
                'collected_by'    => $request->user()->id,
                'submitted_at'    => now(),
            ],
        );

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', "Enquête de satisfaction enregistrée. Merci.");
    }
}
