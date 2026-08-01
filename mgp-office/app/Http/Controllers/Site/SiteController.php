<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Domains\Committee\Enums\CommitteeLevel;
use App\Domains\Committee\Models\Committee;
use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Geography\Models\Region;
use App\Domains\Site\Enums\SiteCategory;
use App\Domains\Site\Enums\SiteStatus;
use App\Domains\Site\Models\Site;
use App\Domains\Site\Models\SiteType;
use App\Domains\Site\Services\SiteRiskAnalysisService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreSiteRequest;
use App\Http\Requests\Site\UpdateSiteRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function __construct(
        private readonly SiteRiskAnalysisService $riskService,
    ) {}

    public function index(Request $request): View
    {
        $query = Site::with(['siteType', 'locality.commune.department.region', 'responsibleUser'])
            ->withCount([
                'complaints as open_complaints_count' => fn ($q) => $q->whereNotIn('status', [
                    ComplaintStatus::RESOLVED->value,
                    ComplaintStatus::CLOSED->value,
                    ComplaintStatus::REJECTED->value,
                ]),
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        if ($request->filled('locality_id')) {
            $query->where('locality_id', $request->locality_id);
        }

        if ($request->filled('site_type_id')) {
            $query->where('site_type_id', $request->site_type_id);
        }

        if ($request->filled('region_id')) {
            $query->whereHas('locality.commune.department', fn ($q) => $q->where('region_id', $request->region_id));
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($w) => $w->where('name', 'ilike', "%{$q}%")->orWhere('code', 'ilike', "%{$q}%"));
        }

        $sites     = $query->orderBy('name')->paginate(20)->withQueryString();
        $siteTypes = SiteType::orderBy('label')->get();
        $regions   = Region::orderBy('name')->get();

        return view('pages.sites.index', compact('sites', 'siteTypes', 'regions'));
    }

    public function create(): View
    {
        $siteTypes  = SiteType::orderBy('label')->get();
        $regions    = Region::orderBy('name')->get();
        $users      = User::orderBy('name')->get(['id', 'name', 'email']);
        $statuses   = SiteStatus::cases();
        $categories = SiteCategory::cases();

        return view('pages.sites.create', compact('siteTypes', 'regions', 'users', 'statuses', 'categories'));
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Auto-assign CBGP committee of the locality if one exists
        $committee = Committee::where('locality_id', $data['locality_id'])
            ->where('level', CommitteeLevel::CBGP)
            ->where('is_active', true)
            ->first();

        if ($committee) {
            $data['committee_id'] = $committee->id;
        }

        $data['risk_level'] = 'low';

        $site = Site::create($data);

        return redirect()
            ->route('sites.show', $site)
            ->with('success', "Site \"{$site->name}\" créé avec succès.");
    }

    public function show(Site $site): View
    {
        $site->load([
            'siteType',
            'locality.commune.department.region',
            'responsibleUser',
            'committee',
            'contractors',
        ]);

        $site->loadCount([
            'complaints as total_complaints_count',
            'complaints as open_complaints_count' => fn ($q) => $q->whereNotIn('status', [
                ComplaintStatus::RESOLVED->value,
                ComplaintStatus::CLOSED->value,
                ComplaintStatus::REJECTED->value,
            ]),
            'complaints as resolved_complaints_count' => fn ($q) => $q->whereIn('status', [
                ComplaintStatus::RESOLVED->value,
                ComplaintStatus::CLOSED->value,
            ]),
        ]);

        $recentComplaints = $site->complaints()
            ->with('complaintType')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Répartition par type (pour onglet indicateurs)
        $complaintsByType = $site->complaints()
            ->selectRaw('complaint_type_id, count(*) as count')
            ->with('complaintType:id,label')
            ->groupBy('complaint_type_id')
            ->get();

        return view('pages.sites.show', compact(
            'site',
            'recentComplaints',
            'complaintsByType',
        ));
    }

    public function edit(Site $site): View
    {
        $siteTypes  = SiteType::orderBy('label')->get();
        $regions    = Region::orderBy('name')->get();
        $users      = User::orderBy('name')->get(['id', 'name', 'email']);
        $statuses   = SiteStatus::cases();
        $categories = SiteCategory::cases();

        $site->load('locality.commune.department.region');

        $selected = [
            'region_id'     => $site->locality->commune->department->region_id ?? null,
            'department_id' => $site->locality->commune->department_id ?? null,
            'commune_id'    => $site->locality->commune_id ?? null,
            'locality_id'   => $site->locality_id,
        ];

        return view('pages.sites.edit', compact('site', 'siteTypes', 'regions', 'users', 'statuses', 'categories', 'selected'));
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();

        // Si la localité a changé, mettre à jour le comité rattaché
        if ((int) $data['locality_id'] !== $site->locality_id) {
            $committee = Committee::where('locality_id', $data['locality_id'])
                ->where('level', CommitteeLevel::CBGP)
                ->where('is_active', true)
                ->first();

            $data['committee_id'] = $committee?->id;
        }

        $site->update($data);

        // Recalculer le niveau de risque après mise à jour
        $this->riskService->recalculate($site);

        return redirect()
            ->route('sites.show', $site)
            ->with('success', "Site \"{$site->name}\" mis à jour avec succès.");
    }

    public function destroy(Site $site): RedirectResponse
    {
        $name = $site->name;
        $site->delete();

        return redirect()
            ->route('sites.index')
            ->with('success', "Site \"{$name}\" supprimé avec succès.");
    }

    public function map(): View
    {
        return view('pages.sites.map');
    }

    public function mapData(): JsonResponse
    {
        $sites = Site::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotIn('status', [SiteStatus::CLOSED->value])
            ->withCount([
                'complaints as open_complaints_count' => fn ($q) => $q->whereNotIn('status', [
                    ComplaintStatus::RESOLVED->value,
                    ComplaintStatus::CLOSED->value,
                    ComplaintStatus::REJECTED->value,
                ]),
            ])
            ->get(['id', 'code', 'name', 'latitude', 'longitude', 'risk_level', 'status', 'open_complaints_count']);

        return response()->json($sites);
    }
}
