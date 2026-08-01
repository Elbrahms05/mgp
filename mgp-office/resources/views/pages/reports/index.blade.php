<x-layout.app title="Rapports" 
description="Génération de rapports MGP - Rapports conformes Banque mondiale (NES 10, Tableau 7)">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Rapports</li>
    </x-slot>

    <div x-data="{
        reportType: '{{ old('report_type', '') }}'
    }" class="margin-row">

        

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle" class="me-2" style="width:16px;height:16px;"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="alert-circle" class="me-2" style="width:16px;height:16px;"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row g-3">

            {{-- Formulaire de génération --}}
            <div class="col-12 col-md-6">
                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Générer un rapport</h5>

                        <form method="POST" action="{{ route('reports.generate') }}" target="_blank">
                            @csrf

                            <div class="mb-3 mt-3">
                                <label for="report_type" class="form-label">Type de rapport *</label>
                                <select name="report_type" id="report_type" required
                                        @change="reportType = $event.target.value"
                                        class="form-select {{ $errors->has('report_type') ? 'is-invalid' : '' }}">
                                    <option value="">-- Sélectionner le type --</option>
                                    @foreach($reportTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('report_type') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('report_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="date_from" class="form-label">Du *</label>
                                        <input type="date" id="date_from" name="date_from"
                                               value="{{ old('date_from', now()->startOfMonth()->format('Y-m-d')) }}" required
                                               class="form-control {{ $errors->has('date_from') ? 'is-invalid' : '' }}">
                                        @error('date_from')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="date_to" class="form-label">Au *</label>
                                        <input type="date" id="date_to" name="date_to"
                                               value="{{ old('date_to', now()->format('Y-m-d')) }}" required
                                               class="form-control {{ $errors->has('date_to') ? 'is-invalid' : '' }}">
                                        @error('date_to')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="format" class="form-label">Format *</label>
                                <select name="format" id="format" required class="form-select">
                                    <option value="html" {{ old('format', 'html') === 'html' ? 'selected' : '' }}>HTML (aperçu)</option>
                                    <option value="csv" {{ old('format') === 'csv' ? 'selected' : '' }}>CSV (télécharger)</option>
                                </select>
                            </div>

                            {{-- Avertissement VBG --}}
                            <div x-show="reportType === 'quarterly_vbg'" x-cloak
                                 class="alert alert-danger" style="border-radius:6px;">
                                <i data-feather="lock" style="width:16px;height:16px;" class="me-1"></i>
                                Rapport VBG : données agrégées anonymisées uniquement.
                                Accès réservé : Coordonnateur UGP et Spécialiste VBG.
                            </div>

                            <div class="d-flex justify-content-end mt-2">
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="file-text" style="width:16px;height:16px;"></i> Générer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Référence rapports obligatoires --}}
            <div class="col-12 col-md-6">
                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Rapports réglementaires MGP</h5>
                        <div class="table-responsive">
                        <table class="table table-sm table-striped" style="font-size:0.82rem;">
                            <thead>
                                <tr>
                                    <th>Rapport</th>
                                    <th>Fréquence</th>
                                    <th>Format</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([
                                    ['Tableau de bord mensuel', 'Mensuel', 'PDF + Excel'],
                                    ['Évaluation comités', 'Trimestriel', 'PDF'],
                                    ['Rapport MGP', 'Semestriel + Annuel', 'PDF + Excel'],
                                    ['Rapport VBG agrégé', 'Trimestriel', 'PDF (chiffré)'],
                                    ['Fiche suivi Annexe 6', 'Continue', 'PDF/plainte'],
                                    ['PV conciliation Annexe 5', 'Par événement', 'PDF'],
                                ] as $rapport)
                                <tr>
                                    <td>{{ $rapport[0] }}</td>
                                    <td class="text-muted">{{ $rapport[1] }}</td>
                                    <td class="text-muted">{{ $rapport[2] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Rappels Banque mondiale --}}
        <div class="card mt-3" style="border-radius:8px;background:#fff8f0;">
            <div class="card-body" style="padding:16px 24px;">
                <h6 style="color:#E05206;">
                    <i data-feather="bell" style="width:16px;height:16px;" class="me-1"></i>
                    Notifications obligatoires Banque mondiale
                </h6>
                <div class="row g-3 mt-1">
                    @foreach([
                        ['event' => 'Cas EAS/HS ou décès', 'delay' => '24h (ESIRT)', 'color' => '#DC3545'],
                        ['event' => 'Tout incident E&S', 'delay' => '48h (PEES)', 'color' => '#F97B3D'],
                        ['event' => 'Résultat commission enquête', 'delay' => '30 jours max', 'color' => '#E05206'],
                    ] as $notif)
                    <div class="col-12 col-md-4">
                        <div style="text-align:center;padding:12px;background:{{ $notif['color'] }};border-radius:8px;">
                            <div style="color:white;font-weight:600;font-size:0.82rem;">{{ $notif['event'] }}</div>
                            <div style="font-size:1.1rem;font-weight:700;color:#333;">{{ $notif['delay'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</x-layout.app>
