<x-layout.app title="PV de médiation">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('mediation.index') }}">Médiation</a></li>
        <li class="breadcrumb-item active">Session {{ $mediation->session_date?->format('d/m/Y') }}</li>
    </x-slot>

    <div class="row margin-row">
        <div class="col-12 col-md-8">

            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <h5 style="margin:0;">
                            <i data-feather="users" style="width:20px;height:20px;" class="me-2"></i>
                            PV de conciliation - Annexe 5
                        </h5>
                        @if($mediation->is_agreement_reached)
                        <span style="background:#e8f5e9;color:#009A44;border-radius:12px;padding:4px 12px;font-size:0.8rem;font-weight:600;">
                            <i data-feather="check-circle" style="width:14px;height:14px;"></i> Accord trouvé
                        </span>
                        @else
                        <span style="background:#ffebee;color:#DC3545;border-radius:12px;padding:4px 12px;font-size:0.8rem;font-weight:600;">
                            <i data-feather="x-circle" style="width:14px;height:14px;"></i> Sans accord
                        </span>
                        @endif
                    </div>

                    <div class="table-responsive">
                    <table class="table table-sm" style="font-size:0.88rem;">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width:35%;"><strong>Plainte :</strong></td>
                                <td>
                                    <a href="{{ route('complaints.show', $mediation->complaint) }}" style="color:#E05206;">
                                        {{ $mediation->complaint?->registration_number }}
                                    </a>
                                    - {{ $mediation->complaint?->subject }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Type plainte :</strong></td>
                                <td>{{ $mediation->complaint?->complaintType?->label ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Site :</strong></td>
                                <td>{{ $mediation->complaint?->site?->name ?? 'Hors site' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Date de la session :</strong></td>
                                <td>{{ $mediation->session_date?->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Présidée par :</strong></td>
                                <td>{{ $mediation->presided_by_name }} - <em>{{ $mediation->presided_by_role }}</em></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Facilitée par :</strong></td>
                                <td>{{ $mediation->facilitatedBy?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Enregistrée le :</strong></td>
                                <td>{{ $mediation->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3" style="font-size:1rem;">Compte-rendu de la session</h5>
                    <p style="white-space:pre-wrap;font-size:0.9rem;line-height:1.6;">{{ $mediation->result }}</p>
                </div>
            </div>

        </div>

        <div class="col-12 col-md-4">

            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3" style="font-size:1rem;">Statut de la plainte</h5>
                    @if($mediation->complaint)
                    <p>
                        <x-complaint.status-badge :status="$mediation->complaint->status" />
                    </p>
                    <a href="{{ route('complaints.show', $mediation->complaint) }}" class="btn btn-primary btn-sm">
                        <i data-feather="eye" style="width:14px;height:14px;"></i> Voir la plainte
                    </a>
                    @endif
                </div>
            </div>

            @if($mediation->is_agreement_reached)
            <div class="card" style="border-radius:8px;border-left:4px solid #009A44;background:#e8f5e9;">
                <div class="card-body">
                    <p style="color:#009A44;font-size:0.85rem;margin:0;">
                        <i data-feather="check-circle" style="width:16px;height:16px;" class="me-1"></i>
                        Accord trouvé lors de cette session. La plainte a été automatiquement marquée comme résolue.
                    </p>
                </div>
            </div>
            @endif

        </div>
    </div>

</x-layout.app>
