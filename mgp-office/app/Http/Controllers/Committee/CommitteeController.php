<?php

declare(strict_types=1);

namespace App\Http\Controllers\Committee;

use App\Domains\Committee\Enums\CommitteeLevel;
use App\Domains\Committee\Models\Committee;
use App\Domains\Committee\Models\CommitteeMember;
use App\Domains\Evaluation\Models\CommitteeEvaluation;
use App\Domains\Geography\Models\Region;
use App\Http\Controllers\Controller;
use App\Http\Requests\Committee\StoreCommitteeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommitteeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Committee::with(['locality.commune.department.region'])
            ->withCount('members');

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('region_id')) {
            $query->whereHas('locality.commune.department', fn ($q) => $q->where('region_id', $request->region_id));
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where('name', 'ilike', "%{$q}%");
        }

        $committees = $query->orderBy('level')->orderBy('name')->paginate(20)->withQueryString();
        $regions    = Region::orderBy('name')->get();

        return view('pages.committees.index', compact('committees', 'regions'));
    }

    public function create(): View
    {
        $levels  = CommitteeLevel::cases();
        $regions = Region::orderBy('name')->get();

        return view('pages.committees.create', compact('levels', 'regions'));
    }

    public function store(StoreCommitteeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $members = $data['members'] ?? [];
        unset($data['members']);

        $data['is_active'] = $data['is_active'] ?? true;

        // CNGP n'a pas de localité obligatoire
        if ($data['level'] === CommitteeLevel::CNGP->value) {
            $data['locality_id'] = null;
        }

        $committee = Committee::create($data);

        foreach ($members as $member) {
            $committee->members()->create([
                'full_name'         => $member['full_name'],
                'role'              => $member['role'],
                'gender'            => $member['gender'],
                'phone'             => $member['phone'] ?? null,
                'election_mode'     => $member['election_mode'],
                'is_vbg_focal_point'=> ($member['is_vbg_focal_point'] ?? false) ? true : false,
                'is_active'         => true,
            ]);
        }

        return redirect()
            ->route('committees.show', $committee)
            ->with('success', "Comité \"{$committee->name}\" créé avec succès.");
    }

    public function show(Committee $committee): View
    {
        $committee->load([
            'locality.commune.department.region',
            'members',
        ]);

        $evaluations = CommitteeEvaluation::where('committee_id', $committee->id)
            ->orderByDesc('period_start')
            ->limit(5)
            ->get();

        return view('pages.committees.show', compact('committee', 'evaluations'));
    }

    public function edit(Committee $committee): View
    {
        $levels  = CommitteeLevel::cases();
        $regions = Region::orderBy('name')->get();

        $committee->load('locality.commune.department.region');

        $selected = [
            'region_id'     => $committee->locality?->commune?->department?->region_id,
            'department_id' => $committee->locality?->commune?->department_id,
            'commune_id'    => $committee->locality?->commune_id,
            'locality_id'   => $committee->locality_id,
        ];

        return view('pages.committees.edit', compact('committee', 'levels', 'regions', 'selected'));
    }

    public function update(StoreCommitteeRequest $request, Committee $committee): RedirectResponse
    {
        $data = $request->validated();
        unset($data['members']); // membres gérés séparément

        if ($data['level'] === CommitteeLevel::CNGP->value) {
            $data['locality_id'] = null;
        }

        $committee->update($data);

        return redirect()
            ->route('committees.show', $committee)
            ->with('success', "Comité \"{$committee->name}\" mis à jour avec succès.");
    }

    public function evaluate(Committee $committee): View
    {
        return view('pages.committees.evaluate', compact('committee'));
    }

    public function storeEvaluation(Request $request, Committee $committee): RedirectResponse
    {
        $data = $request->validate([
            'period_start'               => ['required', 'date'],
            'period_end'                 => ['required', 'date', 'after_or_equal:period_start'],
            'members_total'              => ['required', 'integer', 'min:0'],
            'members_operational'        => ['required', 'integer', 'min:0'],
            'complaints_registered'      => ['required', 'integer', 'min:0'],
            'complaints_processed'       => ['required', 'integer', 'min:0'],
            'complaints_resolved_local'  => ['required', 'integer', 'min:0'],
            'complaints_transmitted'     => ['required', 'integer', 'min:0'],
            'difficulties'               => ['nullable', 'string', 'max:2000'],
            'solutions'                  => ['nullable', 'string', 'max:2000'],
            'community_feedback'         => ['nullable', 'string', 'max:2000'],
            'improvement_suggestions'    => ['nullable', 'string', 'max:2000'],
        ]);

        $data['committee_id'] = $committee->id;
        $data['submitted_by'] = $request->user()->id;
        $data['submitted_at'] = now();

        CommitteeEvaluation::create($data);

        return redirect()
            ->route('committees.show', $committee)
            ->with('success', "Évaluation trimestrielle enregistrée.");
    }
}
