<?php

declare(strict_types=1);

namespace App\Http\Controllers\Communication;

use App\Domains\Communication\Models\CommunicationCampaign;
use App\Domains\Geography\Models\Region;
use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreCommunicationCampaignRequest;
use App\Http\Requests\Communication\UpdateCommunicationCampaignRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationController extends Controller
{
    private const CAMPAIGN_TYPES = [
        'radio'     => 'Radio communautaire',
        'display'   => 'Affichage',
        'training'  => 'Formation',
        'sms_blast' => 'Campagne SMS',
        'event'     => 'Événement / Assemblée',
        'other'     => 'Autre',
    ];

    private const LANGUAGES = [
        'fr' => 'Français',
        'ha' => 'Haoussa',
        'zr' => 'Zarma/Djerma',
        'tq' => 'Tamasheq',
        'kr' => 'Kanouri',
        'ff' => 'Fulfulde/Peul',
    ];

    public function index(Request $request): View
    {
        $query = CommunicationCampaign::with('createdBy')->orderByDesc('start_date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($w) => $w
                ->where('title', 'ilike', "%{$q}%")
                ->orWhere('description', 'ilike', "%{$q}%")
            );
        }

        $campaigns     = $query->paginate(20)->withQueryString();
        $campaignTypes = self::CAMPAIGN_TYPES;

        return view('pages.communication.index', compact('campaigns', 'campaignTypes'));
    }

    public function create(): View
    {
        $regions       = Region::orderBy('name')->get();
        $campaignTypes = self::CAMPAIGN_TYPES;
        $languages     = self::LANGUAGES;

        return view('pages.communication.create', compact('regions', 'campaignTypes', 'languages'));
    }

    public function store(StoreCommunicationCampaignRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['created_by'] = $request->user()->id;

        $campaign = CommunicationCampaign::create($data);

        return redirect()
            ->route('communication.show', $campaign)
            ->with('success', "Campagne \"{$campaign->title}\" créée.");
    }

    public function show(CommunicationCampaign $campaign): View
    {
        $campaign->load('createdBy');
        $campaignTypes = self::CAMPAIGN_TYPES;
        $languages     = self::LANGUAGES;

        return view('pages.communication.show', compact('campaign', 'campaignTypes', 'languages'));
    }

    public function edit(CommunicationCampaign $campaign): View
    {
        $regions       = Region::orderBy('name')->get();
        $campaignTypes = self::CAMPAIGN_TYPES;
        $languages     = self::LANGUAGES;

        return view('pages.communication.edit', compact('campaign', 'regions', 'campaignTypes', 'languages'));
    }

    public function update(UpdateCommunicationCampaignRequest $request, CommunicationCampaign $campaign): RedirectResponse
    {
        $data = $request->validated();

        $campaign->update($data);

        return redirect()
            ->route('communication.show', $campaign)
            ->with('success', "Campagne \"{$campaign->title}\" mise à jour.");
    }
}
