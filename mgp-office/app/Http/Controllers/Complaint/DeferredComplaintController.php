<?php

declare(strict_types=1);

namespace App\Http\Controllers\Complaint;

use App\Domains\Complaint\Models\ComplaintType;
use App\Domains\Complaint\Services\ComplaintWorkflowService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Saisie différée d'une fiche papier F01 (EF-11 / EF-09).
 *
 * La date de réception papier est distincte de la date de saisie : elle fonde le calcul
 * SLA. La fiche numérisée est jointe comme preuve (medialibrary, EF-09).
 */
class DeferredComplaintController extends Controller
{
    public function __construct(private readonly ComplaintWorkflowService $workflow) {}

    public function create(Request $request): View
    {
        $complaintTypes = ComplaintType::orderBy('sort_order')
            ->where('is_sensitive', false)
            ->get();

        return view('pages.complaints.deferred', compact('complaintTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'complaint_type_id'    => ['required', 'exists:complaint_types,id'],
            'received_at'          => ['required', 'date', 'before_or_equal:today'],
            'complainant_name'     => ['nullable', 'string', 'max:150'],
            'complainant_phone'    => ['nullable', 'string', 'max:20'],
            'complainant_locality' => ['nullable', 'string', 'max:150'],
            'is_anonymous'         => ['nullable', 'boolean'],
            'subject'              => ['nullable', 'string', 'max:300'],
            'description'          => ['required', 'string'],
            'scan'                 => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $type = ComplaintType::find($data['complaint_type_id']);
        if ($type?->is_sensitive) {
            return redirect()->route('vbg.create')
                ->with('info', "Les signalements VBG/EAS/HS sont traités via le circuit dédié.");
        }

        $complaint = $this->workflow->register([
            'complaint_type_id'    => $data['complaint_type_id'],
            'complainant_name'     => $data['complainant_name'] ?? null,
            'complainant_phone'    => $data['complainant_phone'] ?? null,
            'complainant_locality' => $data['complainant_locality'] ?? null,
            'is_anonymous'         => $request->boolean('is_anonymous'),
            'subject'              => $data['subject'] ?? 'Fiche papier F01',
            'description'          => $data['description'],
            'channel'              => 'letter',
            'received_at'          => $data['received_at'],
        ], $request->user());

        // Fiche numérisée jointe (preuve, EF-09)
        $complaint->addMediaFromRequest('scan')
            ->usingFileName("fiche-papier-{$complaint->registration_number}." . $request->file('scan')->getClientOriginalExtension())
            ->toMediaCollection('attachments');

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', "Fiche papier {$complaint->registration_number} saisie. "
                . "SLA calculé depuis la date de réception ({$complaint->received_at->format('d/m/Y')}).");
    }
}
