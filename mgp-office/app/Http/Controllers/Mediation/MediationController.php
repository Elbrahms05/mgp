<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mediation;

use App\Domains\Committee\Models\Committee;
use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Services\ComplaintWorkflowService;
use App\Domains\Mediation\Models\Mediation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mediation\StoreMediationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediationController extends Controller
{
    public function __construct(
        private readonly ComplaintWorkflowService $workflow,
    ) {}

    public function index(Request $request): View
    {
        $query = Mediation::with(['complaint.complaintType', 'facilitatedBy'])
            ->orderByDesc('session_date');

        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }

        if ($request->filled('is_agreement_reached')) {
            $query->where('is_agreement_reached', (bool) $request->is_agreement_reached);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->whereHas('complaint', fn ($w) => $w
                ->where('registration_number', 'ilike', "%{$q}%")
                ->orWhere('subject', 'ilike', "%{$q}%")
            );
        }

        $mediations = $query->paginate(20)->withQueryString();

        return view('pages.mediation.index', compact('mediations'));
    }

    public function create(Request $request): View
    {
        // Uniquement les plaintes recevables et en médiation
        $complaints = Complaint::whereIn('status', [
                ComplaintStatus::ACCEPTED->value,
                ComplaintStatus::IN_MEDIATION->value,
            ])
            ->whereDoesntHave('mediations', fn ($q) => $q->where('is_agreement_reached', true))
            ->with('site') // site.committee_id peut être null → fallback locality
            ->orderByDesc('created_at')
            ->get();

        // Collecter les locality_id des sites sans committee_id direct
        $localityIds = $complaints
            ->filter(fn ($c) => $c->site && ! $c->site->committee_id)
            ->map(fn ($c) => $c->site->locality_id)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Charger les comités actifs pour ces localités (+ ceux liés directement)
        $directIds = $complaints
            ->filter(fn ($c) => $c->site?->committee_id)
            ->map(fn ($c) => $c->site->committee_id)
            ->unique()
            ->values()
            ->toArray();

        $committees = Committee::whereIn('id', $directIds)
            ->orWhere(fn ($q) => $q->whereIn('locality_id', $localityIds)->where('is_active', true))
            ->with('members')
            ->get();

        // Index par id et par locality_id pour lookup rapide
        $byId       = $committees->keyBy('id');
        $byLocality = $committees->keyBy('locality_id');

        // Map complaint_id (string) → données comité pour Alpine.js
        $committeeMap = $complaints->mapWithKeys(function ($c) use ($byId, $byLocality) {
            $site = $c->site;
            if (! $site) {
                return [(string) $c->id => null];
            }

            $committee = ($site->committee_id ? $byId->get($site->committee_id) : null)
                ?? ($site->locality_id       ? $byLocality->get($site->locality_id) : null);

            if (! $committee) {
                return [(string) $c->id => null];
            }

            return [
                (string) $c->id => [
                    'committee_name' => $committee->name,
                    'members'        => $committee->members
                        ->where('is_active', true)
                        ->values()
                        ->map(fn ($m) => [
                            'id'   => $m->id,
                            'name' => $m->full_name,
                            'role' => $m->role,
                        ])
                        ->toArray(),
                ],
            ];
        })->toArray();

        $preselectedComplaint = $request->filled('complaint_id')
            ? Complaint::find($request->complaint_id)
            : null;

        return view('pages.mediation.create', compact('complaints', 'committeeMap', 'preselectedComplaint'));
    }

    public function store(StoreMediationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['is_agreement_reached'] = (bool) ($data['is_agreement_reached'] ?? false);
        $data['facilitated_by']       = $request->user()->id;

        $mediation = Mediation::create($data);

        // Si accord trouvé → passer la plainte en RESOLVED
        if ($mediation->is_agreement_reached) {
            $complaint = Complaint::find($data['complaint_id']);
            if ($complaint) {
                $this->workflow->resolve(
                    $complaint,
                    $request->user(),
                    "Médiation du {$mediation->session_date->format('d/m/Y')} - Accord trouvé."
                );
            }
        }

        return redirect()
            ->route('mediation.show', $mediation)
            ->with('success', 'Session de médiation enregistrée.' .
                ($data['is_agreement_reached'] ? ' Accord trouvé - plainte marquée résolue.' : ''));
    }

    public function show(Mediation $mediation): View
    {
        $mediation->load(['complaint.complaintType', 'complaint.site', 'facilitatedBy']);

        return view('pages.mediation.show', compact('mediation'));
    }
}
