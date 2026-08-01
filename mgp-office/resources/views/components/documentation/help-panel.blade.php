@php
    $guide = \App\Domains\Documentation\Models\PageGuide::where('route_name', $route)->first();
@endphp

@if ($guide)
<div x-data="helpPanel()" :class="{ 'hidden': !open }" class="help-panel">

    {{-- En-tête --}}
    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
        <div>
            <div class="fw-semibold d-flex align-items-center gap-1" style="color:#263238;">
                <i data-feather="help-circle" style="width:15px;height:15px;color:var(--pacipa-blue);"></i>
                {{ $guide->page_title }}
            </div>
            @if ($guide->mgp_reference)
            <small class="text-muted">Réf. MGP : {{ $guide->mgp_reference }}</small>
            @endif
        </div>
        <button type="button" class="btn btn-sm btn-icon btn-transparent-dark"
                @click="toggle()" aria-label="Fermer">
            <i data-feather="x" style="width:16px;height:16px;"></i>
        </button>
    </div>

    {{-- Corps --}}
    <div class="p-3">

        {{-- Résumé --}}
        <p class="small text-secondary">{{ $guide->summary }}</p>

        {{-- Alertes --}}
        @if ($guide->warnings)
        @foreach ($guide->warnings as $warning)
        <div class="alert {{ ($warning['type'] ?? '') === 'danger' ? 'alert-danger' : 'alert-info' }}
                    d-flex align-items-start gap-2 py-2 px-3 mb-2">
            <i data-feather="{{ ($warning['type'] ?? '') === 'danger' ? 'alert-triangle' : 'info' }}"
               class="flex-shrink-0" style="width:14px;height:14px;margin-top:2px;"></i>
            <small>{{ $warning['text'] }}</small>
        </div>
        @endforeach
        @endif

        {{-- Guide détaillé (accordion) --}}
        @if ($guide->detailed_guide)
        <div x-data="{ expanded: false }" class="mt-2">
            <a href="#" class="small text-primary d-flex align-items-center gap-1"
               @click.prevent="expanded = !expanded">
                <i data-feather="chevron-down" style="width:14px;height:14px;"
                   :style="expanded ? 'transform:rotate(180deg)' : ''"></i>
                <span x-text="expanded ? 'Réduire' : 'En savoir plus'"></span>
            </a>
            <div x-show="expanded" x-cloak class="mt-2 small text-muted">
                {!! nl2br(e($guide->detailed_guide)) !!}
            </div>
        </div>
        @endif

        {{-- Glossaire --}}
        @if ($guide->related_glossary)
        <div class="mt-3 pt-2 border-top">
            <div class="text-uppercase text-muted fw-bold mb-1" style="font-size:0.68rem;letter-spacing:.06em;">
                Sigles
            </div>
            @foreach ($guide->related_glossary as $sigle => $meaning)
            <div style="font-size:0.8rem;">
                <strong>{{ $sigle }}</strong> - {{ $meaning }}
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>

{{-- Badge (?) flottant --}}
<button class="help-badge-btn" x-data="helpPanel()" @click="toggle()" title="Aide contextuelle">?</button>
@endif
