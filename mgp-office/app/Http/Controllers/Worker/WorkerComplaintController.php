<?php

declare(strict_types=1);

namespace App\Http\Controllers\Worker;

use App\Domains\Company\Models\Company;
use App\Domains\Worker\Enums\WorkerComplaintStatus;
use App\Domains\Worker\Models\WorkerComplaint;
use App\Domains\Worker\Services\WorkerComplaintWorkflowService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\StoreWorkerComplaintRequest;
use App\Http\Requests\Worker\TransitionWorkerComplaintRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkerComplaintController extends Controller
{
    public function __construct(
        private readonly WorkerComplaintWorkflowService $workflow,
    ) {}

    public function index(Request $request): View
    {
        $query = WorkerComplaint::with('registeredBy', 'company')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('worker_type')) {
            $query->where('worker_type', $request->worker_type);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($w) => $w
                ->where('registration_number', 'ilike', "%{$q}%")
                ->orWhere('worker_name', 'ilike', "%{$q}%")
                ->orWhere('employer_name', 'ilike', "%{$q}%")
                ->orWhere('subject', 'ilike', "%{$q}%")
            );
        }

        $workers = $query->paginate(20)->withQueryString();

        return view('pages.workers.index', compact('workers'));
    }

    public function create(): View
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        return view('pages.workers.create', compact('companies'));
    }

    public function store(StoreWorkerComplaintRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status']              = WorkerComplaintStatus::NEW;
        $data['level']               = 1;
        $data['registered_by']       = $request->user()->id;
        $data['registration_number'] = $this->generateNumber();

        $worker = WorkerComplaint::create($data);

        return redirect()
            ->route('workers.show', $worker)
            ->with('success', "Plainte travailleur {$worker->registration_number} enregistrée.");
    }

    public function show(WorkerComplaint $worker): View
    {
        $worker->load('registeredBy', 'company', 'histories.performedBy');
        return view('pages.workers.show', compact('worker'));
    }

    public function edit(WorkerComplaint $worker): View
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        return view('pages.workers.edit', compact('worker', 'companies'));
    }

    public function update(Request $request, WorkerComplaint $worker): RedirectResponse
    {
        $request->validate([
            'worker_name'   => ['required', 'string', 'max:255'],
            'worker_phone'  => ['nullable', 'string', 'max:20'],
            'subject'       => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string', 'min:10'],
            'incident_date' => ['required', 'date'],
        ]);

        $worker->update($request->only(['worker_name', 'worker_phone', 'subject', 'description', 'incident_date']));

        return redirect()
            ->route('workers.show', $worker)
            ->with('success', 'Plainte travailleur mise à jour.');
    }

    /**
     * POST /workers/{worker}/transition
     * Exécute une transition de statut contrôlée.
     */
    public function transition(TransitionWorkerComplaintRequest $request, WorkerComplaint $worker): RedirectResponse
    {
        $action  = $request->validated('action');
        $comment = $request->validated('comment');
        $by      = $request->user();

        try {
            match ($action) {
                'start_review' => $this->workflow->startReview($worker, $by, $comment),
                'escalate'     => $this->workflow->escalate($worker, $by, $comment ?? 'Escaladée au niveau 2'),
                'resolve'      => $this->workflow->resolve($worker, $by, $comment ?? 'Résolue'),
                'close'        => $this->workflow->close($worker, $by, $comment),
            };
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('workers.show', $worker)
            ->with('success', "Transition effectuée : {$worker->fresh()->status->label()}.");
    }

    private function generateNumber(): string
    {
        $year = now()->year;
        $seq  = str_pad((string) (WorkerComplaint::whereYear('created_at', $year)->count() + 1), 4, '0', STR_PAD_LEFT);
        return "TRV-{$year}-{$seq}";
    }
}
