<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vbg;

use App\Domains\Geography\Models\Region;
use App\Domains\Vbg\Enums\GbvimsClassification;
use App\Domains\Vbg\Enums\VbgAuthorLink;
use App\Domains\Vbg\Enums\VbgChannel;
use App\Domains\Vbg\Models\VbgReport;
use App\Domains\Vbg\Services\VbgEncryptionService;
use App\Domains\Vbg\Services\WorldBankNotificationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vbg\StoreVbgReportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VbgReportController extends Controller
{
    public function __construct(
        private readonly VbgEncryptionService $encryption,
        private readonly WorldBankNotificationService $worldBank,
    ) {}

    public function index(Request $request): View
    {
        $query = VbgReport::with(['site', 'receivedBy'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        // Filtrer par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reports  = $query->paginate(20)->withQueryString();
        $channels = VbgChannel::cases();

        return view('pages.vbg.index', compact('reports', 'channels'));
    }

    public function create(): View
    {
        $channels        = VbgChannel::cases();
        $ageGroups       = ['girl_lt18' => 'Fille (< 18 ans)', 'woman_gte18' => 'Femme (≥ 18 ans)', 'boy_lt18' => 'Garçon (< 18 ans)', 'man_gte18' => 'Homme (≥ 18 ans)'];
        $authorLinks     = VbgAuthorLink::cases();
        $classifications = GbvimsClassification::cases();
        $regions         = Region::orderBy('name')->get();

        return view('pages.vbg.create', compact('channels', 'ageGroups', 'authorLinks', 'classifications', 'regions'));
    }

    public function store(StoreVbgReportRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Chiffrer le téléphone si fourni
        $callerPhone = $data['caller_phone'] ?? null;
        unset($data['caller_phone']);

        if ($callerPhone) {
            $data['caller_phone_encrypted'] = $this->encryption->encryptPhone($callerPhone);
        }

        $data['is_anonymous']  = (bool) ($data['is_anonymous'] ?? false);
        $data['consent_given'] = (bool) ($data['consent_given'] ?? false);
        $data['received_by']   = $request->user()->id;
        $data['status']        = 'new';
        $data['code']          = $this->generateCode();

        $report = VbgReport::create($data);

        // Notification UGP dans les 24h (ESIRT)
        $this->worldBank->notifyEsirt($report);

        return redirect()
            ->route('vbg.show', $report)
            ->with('success', "Signalement VBG {$report->code} enregistré. Notification UGP déclenchée.");
    }

    public function show(VbgReport $report): View
    {
        $report->load(['site', 'receivedBy', 'referrals.referredBy', 'enquiry.openedBy', 'consent']);

        $canDecrypt = request()->user()->can('vbg.view_decrypted');

        $decryptedPhone = null;
        if ($canDecrypt && $report->caller_phone_encrypted) {
            try {
                $decryptedPhone = $this->encryption->decryptPhone($report->caller_phone_encrypted);
            } catch (\Exception) {
                $decryptedPhone = '[Erreur de déchiffrement]';
            }
        }

        return view('pages.vbg.show', compact('report', 'canDecrypt', 'decryptedPhone'));
    }

    private function generateCode(): string
    {
        $year = now()->year;
        $seq  = str_pad((string) (VbgReport::whereYear('created_at', $year)->count() + 1), 5, '0', STR_PAD_LEFT);
        return "VBG-{$year}-{$seq}";
    }
}
