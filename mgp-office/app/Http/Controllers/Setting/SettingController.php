<?php

declare(strict_types=1);

namespace App\Http\Controllers\Setting;

use App\Domains\Geography\Models\Region;
use App\Domains\OdkBridge\Services\OdkApiClient;
use App\Http\Controllers\Controller;
use App\Settings\NotificationSettings;
use App\Settings\OdkSettings;
use App\Settings\ProjectSettings;
use App\Settings\SecuritySettings;
use App\Settings\SlaSettings;
use App\Settings\SmsSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        private readonly ProjectSettings      $projectSettings,
        private readonly SlaSettings          $slaSettings,
        private readonly SmsSettings          $smsSettings,
        private readonly OdkSettings          $odkSettings,
        private readonly SecuritySettings     $securitySettings,
        private readonly NotificationSettings $notificationSettings,
    ) {}

    public function index(): View
    {
        return view('pages.settings.index', [
            'project'       => $this->projectSettings,
            'sla'           => $this->slaSettings,
            'sms'           => $this->smsSettings,
            'odk'           => $this->odkSettings,
            'security'      => $this->securitySettings,
            'notifications' => $this->notificationSettings,
        ]);
    }

    public function updateProject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'code'          => ['required', 'string', 'max:20'],
            'donor'         => ['required', 'string', 'max:255'],
            'project_id'    => ['required', 'string', 'max:20'],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['required', 'date', 'after_or_equal:start_date'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'primary_color' => ['nullable', 'string', 'max:10'],
            'secondary_color'=> ['nullable', 'string', 'max:10'],
        ]);

        $this->projectSettings->fill($data)->save();

        return back()->with('success', 'Paramètres projet mis à jour.');
    }

    public function updateSla(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cbgp_exam_days'                      => ['required', 'integer', 'min:1', 'max:30'],
            'cbgp_investigation_days'             => ['required', 'integer', 'min:1', 'max:30'],
            'ccgp_exam_days'                      => ['required', 'integer', 'min:1', 'max:30'],
            'ccgp_investigation_days'             => ['required', 'integer', 'min:1', 'max:30'],
            'cngp_exam_days'                      => ['required', 'integer', 'min:1', 'max:30'],
            'cngp_investigation_days'             => ['required', 'integer', 'min:1', 'max:30'],
            'vbg_ugp_notification_hours'          => ['required', 'integer', 'min:1', 'max:72'],
            'vbg_referral_hours'                  => ['required', 'integer', 'min:1', 'max:168'],
            'vbg_worldbank_eas_hours'             => ['required', 'integer', 'min:1', 'max:48'],
            'vbg_worldbank_incident_hours'        => ['required', 'integer', 'min:1', 'max:72'],
            'vbg_enquiry_days'                    => ['required', 'integer', 'min:1', 'max:90'],
            'worker_direct_level1_days'           => ['required', 'integer', 'min:1', 'max:30'],
            'worker_direct_level2_days'           => ['required', 'integer', 'min:1', 'max:30'],
            'worker_contractor_level1_working_days'=> ['required', 'integer', 'min:1', 'max:30'],
            'worker_contractor_level2_days'       => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $this->slaSettings->fill($data)->save();

        return back()->with('success', 'Délais SLA mis à jour. Engagements Banque mondiale respectés.');
    }

    public function updateSms(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider'       => ['required', 'string', 'in:africas_talking,twilio,orange_niger,airtel_niger'],
            'api_key'        => ['nullable', 'string', 'max:255'],
            'username'       => ['nullable', 'string', 'max:100'],
            'sender_name'    => ['required', 'string', 'max:11'],
            'short_code'     => ['nullable', 'string', 'max:10'],
            'hotline_number' => ['nullable', 'string', 'max:20'],
            'sms_enabled'    => ['boolean'],
        ]);

        $this->smsSettings->fill($data)->save();

        return back()->with('success', 'Paramètres SMS mis à jour.');
    }

    public function updateOdk(Request $request, OdkApiClient $odkClient): RedirectResponse
    {
        $data = $request->validate([
            'server_type'     => ['required', 'string', 'in:kobo,odk_central,odk_aggregate'],
            'server_url'      => ['nullable', 'url', 'max:255'],
            'api_token'       => ['nullable', 'string', 'max:500'],
            'username'        => ['nullable', 'string', 'email', 'max:100'],
            'password'        => ['nullable', 'string', 'max:255'],
            'sync_schedule'   => ['nullable', 'string', 'max:50'],
            'sync_enabled'    => ['boolean'],
            'timeout_seconds' => ['required', 'integer', 'min:5', 'max:120'],
            'retry_attempts'  => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        // Ne pas écraser le mot de passe ODK Central s'il est laissé vide
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $credentialsChanged = $data['server_url'] !== $this->odkSettings->server_url
            || ($data['username'] ?? '') !== $this->odkSettings->username
            || isset($data['password']);

        $this->odkSettings->fill($data)->save();

        // Invalider le token de session caché si les credentials ont changé
        if ($credentialsChanged) {
            $odkClient->clearSessionToken();
        }

        return back()->with('success', 'Paramètres ODK mis à jour.');
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'session_lifetime_minutes'    => ['required', 'integer', 'min:15', 'max:1440'],
            'password_min_length'         => ['required', 'integer', 'min:8', 'max:32'],
            'password_expiry_days'        => ['required', 'integer', 'min:30', 'max:365'],
            'max_login_attempts'          => ['required', 'integer', 'min:3', 'max:20'],
            'lockout_duration_minutes'    => ['required', 'integer', 'min:5', 'max:1440'],
            'vbg_key_rotation_days'       => ['required', 'integer', 'min:30', 'max:730'],
            'password_require_uppercase'  => ['boolean'],
            'password_require_number'     => ['boolean'],
            'password_require_symbol'     => ['boolean'],
        ]);

        $this->securitySettings->fill($data)->save();

        return back()->with('success', 'Paramètres de sécurité mis à jour.');
    }

    // ── Géographie ──────────────────────────────────────────────────────

    public function geography(): View
    {
        $regions = Region::withCount(['departments', 'communes'])->orderBy('name')->get();

        return view('pages.settings.geography', compact('regions'));
    }
}
