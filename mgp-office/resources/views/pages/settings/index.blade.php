<x-layout.app title="Paramétrage">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Paramétrage</li>
    </x-slot>

    <div x-data="{
        tab: window.location.hash.replace('#','') || 'project',
        init() {}
    }" x-init="init()" class="margin-row">

        

       

        <div class="row g-3">
            <div class="col-12 col-md-3">
                <div class="list-group" style="border-radius:8px;">
                    @foreach([
                        ['key' => 'project',  'icon' => 'briefcase',    'label' => 'Projet'],
                        ['key' => 'sla',      'icon' => 'clock',        'label' => 'Délais SLA'],
                        ['key' => 'sms',      'icon' => 'message-circle','label' => 'SMS / Ligne Verte'],
                        ['key' => 'odk',      'icon' => 'refresh-cw',   'label' => 'ODK / Sync'],
                        ['key' => 'security', 'icon' => 'shield',       'label' => 'Sécurité'],
                        ['key' => 'geography','icon' => 'map',          'label' => 'Géographie'],
                    ] as $item)
                    <a href="#" class="list-group-item list-group-item-action"
                       :class="tab === '{{ $item['key'] }}' ? 'active' : ''"
                       @click.prevent="tab = '{{ $item['key'] }}'; window.location.hash = '{{ $item['key'] }}'">
                        <i data-feather="{{ $item['icon'] }}" style="width:16px;height:16px;" class="me-2"></i>
                        {{ $item['label'] }}
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="col-12 col-md-9">

                {{-- Projet --}}
                <div x-show="tab === 'project'" x-cloak>
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">Informations projet</h5>
                            <form method="POST" action="{{ route('settings.project.update') }}">
                                @csrf @method('PUT')
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="p_name" class="form-label">Nom du projet *</label>
                                            <input type="text" id="p_name" name="name" value="{{ old('name', $project->name) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="p_code" class="form-label">Code projet *</label>
                                            <input type="text" id="p_code" name="code" value="{{ old('code', $project->code) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="p_donor" class="form-label">Bailleur *</label>
                                            <input type="text" id="p_donor" name="donor" value="{{ old('donor', $project->donor) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="p_project_id" class="form-label">ID Projet Banque mondiale *</label>
                                            <input type="text" id="p_project_id" name="project_id" value="{{ old('project_id', $project->project_id) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="p_start" class="form-label">Date début *</label>
                                            <input type="date" id="p_start" name="start_date" value="{{ old('start_date', $project->start_date) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="p_end" class="form-label">Date fin *</label>
                                            <input type="date" id="p_end" name="end_date" value="{{ old('end_date', $project->end_date) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="p_email" class="form-label">Email contact UGP</label>
                                            <input type="email" id="p_email" name="contact_email" value="{{ old('contact_email', $project->contact_email) }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="p_phone" class="form-label">Téléphone contact UGP</label>
                                            <input type="tel" id="p_phone" name="contact_phone" value="{{ old('contact_phone', $project->contact_phone) }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="mb-3">
                                            <label for="p_primary_color" class="form-label">Couleur primaire</label>
                                            <input type="color" id="p_primary_color" name="primary_color" value="{{ old('primary_color', $project->primary_color) }}" class="form-control form-control-color">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="mb-3">
                                            <label for="p_secondary_color" class="form-label">Couleur secondaire</label>
                                            <input type="color" id="p_secondary_color" name="secondary_color" value="{{ old('secondary_color', $project->secondary_color) }}" class="form-control form-control-color">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-success">
                                        <i data-feather="save" style="width:16px;height:16px;"></i> Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- SLA --}}
                <div x-show="tab === 'sla'" x-cloak>
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">Délais SLA</h5>
                            <div class="alert alert-warning" style="border-radius:6px;margin-bottom:16px;">
                                <i data-feather="alert-triangle" style="width:16px;height:16px;" class="me-1"></i>
                                Ces délais sont des engagements contractuels Banque mondiale (NES 10). Les modifier peut affecter la conformité.
                            </div>
                            <form method="POST" action="{{ route('settings.sla.update') }}">
                                @csrf @method('PUT')

                                <p class="fw-semibold text-muted mb-2">Comités locaux (jours)</p>
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="cbgp_exam" class="form-label">CBGP - Examen</label>
                                            <input type="number" id="cbgp_exam" name="cbgp_exam_days" min="1" max="30" value="{{ old('cbgp_exam_days', $sla->cbgp_exam_days) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="cbgp_inv" class="form-label">CBGP - Investigation</label>
                                            <input type="number" id="cbgp_inv" name="cbgp_investigation_days" min="1" max="30" value="{{ old('cbgp_investigation_days', $sla->cbgp_investigation_days) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="ccgp_exam" class="form-label">CCGP - Examen</label>
                                            <input type="number" id="ccgp_exam" name="ccgp_exam_days" min="1" max="30" value="{{ old('ccgp_exam_days', $sla->ccgp_exam_days) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="ccgp_inv" class="form-label">CCGP - Investigation</label>
                                            <input type="number" id="ccgp_inv" name="ccgp_investigation_days" min="1" max="30" value="{{ old('ccgp_investigation_days', $sla->ccgp_investigation_days) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="cngp_exam" class="form-label">CNGP - Examen</label>
                                            <input type="number" id="cngp_exam" name="cngp_exam_days" min="1" max="30" value="{{ old('cngp_exam_days', $sla->cngp_exam_days) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="cngp_inv" class="form-label">CNGP - Investigation</label>
                                            <input type="number" id="cngp_inv" name="cngp_investigation_days" min="1" max="30" value="{{ old('cngp_investigation_days', $sla->cngp_investigation_days) }}" required class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <p class="fw-semibold text-muted mb-2 mt-2">Délais VBG (heures / jours)</p>
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="vbg_notif" class="form-label">Notif. UGP (h)</label>
                                            <input type="number" id="vbg_notif" name="vbg_ugp_notification_hours" min="1" max="72" value="{{ old('vbg_ugp_notification_hours', $sla->vbg_ugp_notification_hours) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="vbg_ref" class="form-label">Référencement (h)</label>
                                            <input type="number" id="vbg_ref" name="vbg_referral_hours" min="1" max="168" value="{{ old('vbg_referral_hours', $sla->vbg_referral_hours) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="vbg_bm_eas" class="form-label">BM ESIRT (h)</label>
                                            <input type="number" id="vbg_bm_eas" name="vbg_worldbank_eas_hours" min="1" max="48" value="{{ old('vbg_worldbank_eas_hours', $sla->vbg_worldbank_eas_hours) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="vbg_enquiry" class="form-label">Commission (j)</label>
                                            <input type="number" id="vbg_enquiry" name="vbg_enquiry_days" min="1" max="90" value="{{ old('vbg_enquiry_days', $sla->vbg_enquiry_days) }}" required class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-success">
                                        <i data-feather="save" style="width:16px;height:16px;"></i> Enregistrer les délais
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- SMS --}}
                <div x-show="tab === 'sms'" x-cloak>
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">Passerelle SMS / Ligne Verte</h5>
                            <form method="POST" action="{{ route('settings.sms.update') }}">
                                @csrf @method('PUT')
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="sms_provider" class="form-label">Opérateur / Fournisseur</label>
                                            <select name="provider" id="sms_provider" class="form-select">
                                                @foreach(['africas_talking' => "Africa's Talking", 'twilio' => 'Twilio', 'orange_niger' => 'Orange Niger', 'airtel_niger' => 'Airtel Niger'] as $key => $label)
                                                <option value="{{ $key }}" {{ old('provider', $sms->provider) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="sms_sender" class="form-label">Nom expéditeur (11 car. max) *</label>
                                            <input type="text" id="sms_sender" name="sender_name" value="{{ old('sender_name', $sms->sender_name) }}" maxlength="11" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="sms_api_key" class="form-label">Clé API</label>
                                            <input type="text" id="sms_api_key" name="api_key" value="{{ old('api_key', $sms->api_key) }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="sms_username" class="form-label">Nom d'utilisateur API</label>
                                            <input type="text" id="sms_username" name="username" value="{{ old('username', $sms->username) }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="sms_short_code" class="form-label">Code court SMS (ex: 3xxx)</label>
                                            <input type="text" id="sms_short_code" name="short_code" value="{{ old('short_code', $sms->short_code) }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="sms_hotline" class="form-label">Numéro Ligne Verte</label>
                                            <input type="text" id="sms_hotline" name="hotline_number" value="{{ old('hotline_number', $sms->hotline_number) }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" id="sms_enabled" name="sms_enabled" value="1"
                                                   class="form-check-input"
                                                   {{ old('sms_enabled', $sms->sms_enabled ? '1' : '0') === '1' ? 'checked' : '' }}>
                                            <label for="sms_enabled" class="form-check-label">Activer l'envoi SMS automatique</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">
                                        <i data-feather="save" style="width:16px;height:16px;"></i> Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ODK - CRITICAL: keep serverType Alpine.js variable and showPwd intact --}}
                <div x-show="tab === 'odk'" x-cloak
                     x-data="{ showPwd: false, serverType: '{{ old('server_type', $odk->server_type) }}' }">
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">Serveur ODK / Synchronisation</h5>
                            <form method="POST" action="{{ route('settings.odk.update') }}">
                                @csrf @method('PUT')

                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="odk_type" class="form-label">Type de serveur</label>
                                            <select name="server_type" id="odk_type" x-model="serverType" class="form-select">
                                                @foreach(['kobo' => 'KoboToolbox', 'odk_central' => 'ODK Central', 'odk_aggregate' => 'ODK Aggregate'] as $key => $label)
                                                <option value="{{ $key }}" {{ old('server_type', $odk->server_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="odk_url" class="form-label">URL du serveur</label>
                                            <input type="url" id="odk_url" name="server_url"
                                                   value="{{ old('server_url', $odk->server_url) }}"
                                                   placeholder="https://central.example.org"
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>

                                {{-- ── ODK Central : email + mot de passe ── --}}
                                <div x-show="serverType === 'odk_central'">
                                    <div class="alert alert-info" style="border-radius:6px;margin-bottom:12px;">
                                        <p style="margin:0 0 6px;font-size:0.85rem;">
                                            <i data-feather="info" style="width:14px;height:14px;" class="me-1"></i>
                                            <strong>Authentification ODK Central</strong>
                                        </p>
                                        <p style="margin:0;font-size:0.82rem;">
                                            ODK Central utilise une session temporaire :
                                            le système appelle automatiquement <code>POST /v1/sessions</code>
                                            avec votre email et mot de passe pour obtenir un token valide <strong>24 h</strong>
                                            (mis en cache 23 h). Aucune manipulation manuelle du token n'est nécessaire.
                                        </p>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label for="odk_email" class="form-label">Email ODK Central *</label>
                                                <input type="email" id="odk_email" name="username"
                                                       value="{{ old('username', $odk->username) }}"
                                                       placeholder="admin@votreserveur.org"
                                                       class="form-control">
                                                <div class="form-text text-muted">Compte administrateur de l'instance ODK Central</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="mb-3" style="position:relative;">
                                                <label for="odk_password" class="form-label">
                                                    Mot de passe ODK Central
                                                    @if($odk->password) <span style="color:#009A44;">(déjà enregistré)</span>@endif
                                                </label>
                                                <div class="input-group">
                                                    <input :type="showPwd ? 'text' : 'password'"
                                                           id="odk_password" name="password"
                                                           autocomplete="new-password"
                                                           class="form-control">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                            @click="showPwd = !showPwd">
                                                        <i :data-feather="showPwd ? 'eye-off' : 'eye'" style="width:16px;height:16px;"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text text-muted">Laissez vide pour conserver le mot de passe actuel</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── KoboToolbox / ODK Aggregate : token statique ── --}}
                                <div x-show="serverType !== 'odk_central'">
                                    <div x-show="serverType === 'kobo'"
                                         class="alert alert-info" style="border-radius:6px;margin-bottom:12px;">
                                        <p style="margin:0;font-size:0.82rem;">
                                            <i data-feather="info" style="width:14px;height:14px;" class="me-1"></i>
                                            <strong>Token KoboToolbox :</strong>
                                            Connexion → icône utilisateur → <em>Account Settings</em>
                                            → onglet <em>API</em> → copier l'<em>API Token</em>.
                                        </p>
                                    </div>

                                    <div class="mb-3">
                                        <label for="odk_token" class="form-label">Token API</label>
                                        <div class="input-group">
                                            <input :type="showPwd ? 'text' : 'password'"
                                                   id="odk_token" name="api_token"
                                                   value="{{ old('api_token', $odk->api_token) }}"
                                                   autocomplete="new-password"
                                                   class="form-control">
                                            <button type="button" class="btn btn-outline-secondary"
                                                    @click="showPwd = !showPwd">
                                                <i :data-feather="showPwd ? 'eye-off' : 'eye'" style="width:16px;height:16px;"></i>
                                            </button>
                                        </div>
                                        @if($odk->api_token)
                                        <div class="form-text" style="color:#009A44;">
                                            <i data-feather="check-circle" style="width:12px;height:12px;"></i> Token enregistré
                                        </div>
                                        @else
                                        <div class="form-text text-muted">Laissez vide pour ne pas modifier</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="odk_timeout" class="form-label">Timeout (s)</label>
                                            <input type="number" id="odk_timeout" name="timeout_seconds"
                                                   min="5" max="120"
                                                   value="{{ old('timeout_seconds', $odk->timeout_seconds) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="odk_retry" class="form-label">Tentatives</label>
                                            <input type="number" id="odk_retry" name="retry_attempts"
                                                   min="1" max="10"
                                                   value="{{ old('retry_attempts', $odk->retry_attempts) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="odk_schedule" class="form-label">Schedule CRON</label>
                                            <input type="text" id="odk_schedule" name="sync_schedule"
                                                   value="{{ old('sync_schedule', $odk->sync_schedule) }}"
                                                   placeholder="*/15 * * * *"
                                                   class="form-control">
                                            <div class="form-text text-muted">Ex : <code>*/15 * * * *</code> = toutes les 15 min</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" id="sync_enabled" name="sync_enabled" value="1"
                                                   class="form-check-input"
                                                   {{ old('sync_enabled', $odk->sync_enabled ? '1' : '0') === '1' ? 'checked' : '' }}>
                                            <label for="sync_enabled" class="form-check-label">Activer la synchronisation automatique</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">
                                        <i data-feather="save" style="width:16px;height:16px;"></i> Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Sécurité --}}
                <div x-show="tab === 'security'" x-cloak>
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">Paramètres de sécurité</h5>
                            <form method="POST" action="{{ route('settings.security.update') }}">
                                @csrf @method('PUT')

                                <p class="fw-semibold text-muted mb-2">Sessions & connexion</p>
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="sec_session" class="form-label">Session (min.)</label>
                                            <input type="number" id="sec_session" name="session_lifetime_minutes" min="15" max="1440" value="{{ old('session_lifetime_minutes', $security->session_lifetime_minutes) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="sec_attempts" class="form-label">Tentatives max</label>
                                            <input type="number" id="sec_attempts" name="max_login_attempts" min="3" max="20" value="{{ old('max_login_attempts', $security->max_login_attempts) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="sec_lockout" class="form-label">Blocage (min.)</label>
                                            <input type="number" id="sec_lockout" name="lockout_duration_minutes" min="5" max="1440" value="{{ old('lockout_duration_minutes', $security->lockout_duration_minutes) }}" required class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <p class="fw-semibold text-muted mb-2 mt-2">Politique mots de passe</p>
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="sec_pwd_len" class="form-label">Longueur min.</label>
                                            <input type="number" id="sec_pwd_len" name="password_min_length" min="8" max="32" value="{{ old('password_min_length', $security->password_min_length) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="sec_pwd_exp" class="form-label">Expiration (j)</label>
                                            <input type="number" id="sec_pwd_exp" name="password_expiry_days" min="30" max="365" value="{{ old('password_expiry_days', $security->password_expiry_days) }}" required class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" id="pwd_upper" name="password_require_uppercase" value="1"
                                                   class="form-check-input"
                                                   {{ old('password_require_uppercase', $security->password_require_uppercase ? '1' : '0') === '1' ? 'checked' : '' }}>
                                            <label for="pwd_upper" class="form-check-label">Majuscule obligatoire</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" id="pwd_number" name="password_require_number" value="1"
                                                   class="form-check-input"
                                                   {{ old('password_require_number', $security->password_require_number ? '1' : '0') === '1' ? 'checked' : '' }}>
                                            <label for="pwd_number" class="form-check-label">Chiffre obligatoire</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" id="pwd_symbol" name="password_require_symbol" value="1"
                                                   class="form-check-input"
                                                   {{ old('password_require_symbol', $security->password_require_symbol ? '1' : '0') === '1' ? 'checked' : '' }}>
                                            <label for="pwd_symbol" class="form-check-label">Symbole obligatoire</label>
                                        </div>
                                    </div>
                                </div>

                                <p class="fw-semibold text-muted mb-2">Chiffrement VBG</p>
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="sec_vbg_rot" class="form-label">Rotation clé VBG (j)</label>
                                            <input type="number" id="sec_vbg_rot" name="vbg_key_rotation_days" min="30" max="730" value="{{ old('vbg_key_rotation_days', $security->vbg_key_rotation_days) }}" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-9 d-flex align-items-center">
                                        <p class="text-muted mb-0" style="font-size:0.82rem;">
                                            <i data-feather="lock" style="width:14px;height:14px;" class="me-1"></i>
                                            2FA obligatoire : super_admin, coordonnateur_ugp, specialiste_vbg (non modifiable)
                                        </p>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-success">
                                        <i data-feather="save" style="width:16px;height:16px;"></i> Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Géographie --}}
                <div x-show="tab === 'geography'" x-cloak>
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">Référentiel géographique</h5>
                            <p class="text-muted">
                                Données issues du package <code>elbrahms/niger-location</code>.
                                <a href="{{ route('geography.index') }}" style="color:#E05206;">Voir le détail complet</a>
                            </p>
                            <a href="{{ route('geography.index') }}" class="btn btn-primary">
                                <i data-feather="map" style="width:16px;height:16px;"></i> Gérer la géographie
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</x-layout.app>
