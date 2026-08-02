<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vbg;

use App\Domains\Vbg\Models\TrustedPersonTraining;
use App\Domains\Vbg\Services\TrustedPersonComplianceService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Registre de formation / recyclage des personnes de confiance VBG (update.md 2.5).
 * Réservé à l'UGP (permission vbg.receive). Le recyclage est valide 6 mois.
 */
class TrustedPersonTrainingController extends Controller
{
    public function __construct(private readonly TrustedPersonComplianceService $compliance) {}

    public function index(): View
    {
        $trustedPersons = User::role('personne_confiance_vbg')
            ->with(['media'])
            ->get()
            ->map(fn (User $u) => [
                'user'        => $u,
                'valid_until' => $this->compliance->trainingValidUntil($u),
                'up_to_date'  => $this->compliance->isTrainingValid($u),
            ]);

        $trainings = TrustedPersonTraining::with(['user', 'recordedBy'])
            ->orderByDesc('trained_at')
            ->limit(100)
            ->get();

        return view('pages.vbg.trusted-person-trainings', compact('trustedPersons', 'trainings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id'    => ['required', 'exists:users,id'],
            'trained_at' => ['required', 'date', 'before_or_equal:today'],
            'topic'      => ['nullable', 'string', 'max:200'],
        ]);

        $user = User::findOrFail($data['user_id']);

        $this->compliance->recordTraining(
            $user,
            Carbon::parse($data['trained_at']),
            $data['topic'] ?? null,
            $request->user(),
        );

        return redirect()
            ->route('vbg.trusted-person.trainings')
            ->with('success', "Formation enregistrée pour {$user->name}. Recyclage valable 6 mois.");
    }
}
