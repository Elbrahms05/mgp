<?php

declare(strict_types=1);

namespace App\Http\Controllers\Complaint;

use App\Domains\Committee\Enums\CommitteeLevel;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Services\ComplaintWorkflowService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complaint\EscalateComplaintRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EscalationController extends Controller
{
    public function __construct(
        private readonly ComplaintWorkflowService $workflow,
    ) {}

    public function create(Complaint $complaint): View
    {
        $currentLevel = $complaint->current_level ?? CommitteeLevel::CBGP;

        // Niveaux accessibles (supérieurs au niveau actuel uniquement)
        $order = [
            CommitteeLevel::CBGP->value => 1,
            CommitteeLevel::CCGP->value => 2,
            CommitteeLevel::CNGP->value => 3,
        ];

        $availableLevels = array_filter(
            CommitteeLevel::cases(),
            fn ($l) => ($order[$l->value] ?? 0) > ($order[$currentLevel->value] ?? 0),
        );

        return view('pages.complaints.escalate', compact('complaint', 'availableLevels'));
    }

    public function store(EscalateComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        $newLevel = CommitteeLevel::from($request->validated('new_level'));
        $comment  = $request->validated('comment');

        $this->workflow->escalate($complaint, $newLevel, $comment, $request->user());

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', "Plainte escaladée vers le {$newLevel->label()}.");
    }
}
