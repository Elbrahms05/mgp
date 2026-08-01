<?php

declare(strict_types=1);

namespace App\Http\Controllers\Complaint;

use App\Domains\Complaint\Enums\ComplaintChannel;
use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Models\ComplaintType;
use App\Domains\Complaint\Services\ComplaintWorkflowService;
use App\Domains\Complaint\Services\SlaMonitorService;
use App\Domains\Geography\Models\Region;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complaint\StoreComplaintRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function __construct(
        private readonly ComplaintWorkflowService $workflow,
        private readonly SlaMonitorService $slaMonitor,
    ) {}

    public function index(Request $request): View
    {
        $query = Complaint::with(['complaintType', 'site', 'registeredBy'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('complaint_type_id')) {
            $query->where('complaint_type_id', $request->complaint_type_id);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('sla_breach') && $request->sla_breach === '1') {
            $query->whereNotNull('sla_deadline')->where('sla_deadline', '<', now());
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($w) => $w
                ->where('registration_number', 'ilike', "%{$q}%")
                ->orWhere('subject', 'ilike', "%{$q}%")
                ->orWhere('complainant_name', 'ilike', "%{$q}%")
            );
        }

        $complaints    = $query->paginate(20)->withQueryString();
        $complaintTypes = ComplaintType::orderBy('sort_order')->get();
        $channels      = ComplaintChannel::cases();

        return view('pages.complaints.index', compact('complaints', 'complaintTypes', 'channels'));
    }

    public function create(Request $request): View
    {
        // Exclure type VBG (type sensible) sauf permission explicite
        $complaintTypes = ComplaintType::orderBy('sort_order')
            ->when(! $request->user()->can('vbg.receive'), fn ($q) => $q->where('is_sensitive', false))
            ->get();

        $channels = ComplaintChannel::cases();
        $regions  = Region::orderBy('name')->get();

        return view('pages.complaints.create', compact('complaintTypes', 'channels', 'regions'));
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Guard : type VBG → redirection
        $type = ComplaintType::find($data['complaint_type_id']);
        if ($type?->is_sensitive) {
            return redirect()
                ->route('vbg.create')
                ->with('info', "Les signalements VBG/EAS/HS sont traités via le circuit dédié.");
        }

        $complaint = $this->workflow->register($data, $request->user());

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', "Plainte {$complaint->registration_number} enregistrée avec succès.");
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load([
            'complaintType',
            'site.locality.commune.department.region',
            'registeredBy',
            'assignedUser',
            'histories.performedBy',
        ]);

        $hoursRemaining = $this->slaMonitor->hoursRemaining($complaint);
        $isBreached     = $this->slaMonitor->isBreached($complaint);

        return view('pages.complaints.show', compact('complaint', 'hoursRemaining', 'isBreached'));
    }

    public function edit(Complaint $complaint): View
    {
        $complaintTypes = ComplaintType::orderBy('sort_order')->where('is_sensitive', false)->get();
        $channels       = ComplaintChannel::cases();
        $regions        = Region::orderBy('name')->get();

        return view('pages.complaints.edit', compact('complaint', 'complaintTypes', 'channels', 'regions'));
    }

    public function update(StoreComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        $complaint->update($request->validated());

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Plainte mise à jour.');
    }

    // ── Actions workflow ────────────────────────────────────────────────

    public function accept(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->workflow->accept($complaint, $request->user());
        return back()->with('success', 'Plainte déclarée recevable.');
    }

    public function reject(Request $request, Complaint $complaint): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|min:5']);
        $this->workflow->reject($complaint, $request->reason, $request->user());
        return back()->with('success', 'Plainte rejetée.');
    }

    public function mediation(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->workflow->startMediation($complaint, $request->user());
        return back()->with('success', 'Médiation engagée.');
    }

    public function resolve(Request $request, Complaint $complaint): RedirectResponse
    {
        $request->validate(['comment' => 'nullable|string|max:500']);
        $this->workflow->resolve($complaint, $request->user(), $request->comment);
        return back()->with('success', 'Plainte marquée résolue.');
    }

    public function close(Request $request, Complaint $complaint): RedirectResponse
    {
        $request->validate(['comment' => 'nullable|string|max:500']);
        $this->workflow->close($complaint, $request->user(), $request->comment);
        return back()->with('success', 'Plainte clôturée.');
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        // Export CSV simple
        $complaints = Complaint::with(['complaintType', 'site'])
            ->orderByDesc('created_at')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plaintes_' . now()->format('Ymd') . '.csv"',
        ];

        $callback = function () use ($complaints) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'N° Plainte', 'Type', 'Site', 'Statut', 'Canal', 'Objet',
                'Date faits', 'Date enregistrement', 'Anonyme',
            ], ';');

            foreach ($complaints as $c) {
                fputcsv($handle, [
                    $c->registration_number,
                    $c->complaintType?->label,
                    $c->site?->name ?? 'Hors site',
                    $c->status->label(),
                    $c->channel?->label(),
                    $c->subject,
                    $c->incident_date?->format('d/m/Y'),
                    $c->created_at->format('d/m/Y H:i'),
                    $c->is_anonymous ? 'Oui' : 'Non',
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
