<x-layout.app title="Géographie"
description="Référentiel géographique - Niger"
icon="map-pin">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Paramétrage</a></li>
        <li class="breadcrumb-item active">Géographie</li>
    </x-slot>

    <div class="row g-3 margin-row">
        @foreach($regions as $region)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card" style="border-radius:8px;">
                <div class="card-body" style="padding:16px 20px;">
                    <h6 class="fw-semibold mb-2">
                        <i data-feather="map-pin" style="width:16px;height:16px;" class="me-1"></i>{{ $region->name }}
                    </h6>
                    <div style="display:flex;gap:16px;font-size:0.82rem;color:#757575;">
                        <span><i data-feather="git-branch" style="width:12px;height:12px;" class="me-1"></i> {{ $region->departments_count ?? 0 }} dept.</span>
                        <span><i data-feather="home" style="width:12px;height:12px;" class="me-1"></i> {{ $region->communes_count ?? 0 }} comm.</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="alert alert-info" style="border-radius:8px;">
        <i data-feather="info" style="width:16px;height:16px;" class="me-2"></i>
        Le référentiel géographique est en lecture seule. 
    </div>

</x-layout.app>
