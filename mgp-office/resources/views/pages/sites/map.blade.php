<x-layout.app title="Carte des sites"
description="Carte des sites PACIPA - Niger"
icon="map">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sites.index') }}">Sites</a></li>
        <li class="breadcrumb-item active">Carte</li>
    </x-slot>
    <x-slot name="actions">
        <a href="{{ route('sites.index') }}" class="btn btn-secondary btn-sm">
            <i data-feather="list" style="width:16px;height:16px;"></i> Vue liste
        </a>
        @can('sites.create')
        <a href="{{ route('sites.create') }}" class="btn btn-success btn-sm ms-2">
            <i data-feather="plus" style="width:16px;height:16px;"></i> Nouveau site
        </a>
        @endcan
    </x-slot>
    <div x-data="siteMap()" x-init="init()" class="margin-row">

        

        {{-- Légende --}}
        <div class="card mb-2" style="border-radius:8px;">
            <div class="card-body" style="padding:12px 20px;">
                <div style="display:flex;gap:24px;flex-wrap:wrap;align-items:center;">
                    <span style="font-weight:500;font-size:0.85rem;">Niveau de risque :</span>
                    <span style="display:flex;align-items:center;gap:6px;font-size:0.8rem;">
                        <span style="width:14px;height:14px;border-radius:50%;background:#009A44;display:inline-block;"></span> Faible
                    </span>
                    <span style="display:flex;align-items:center;gap:6px;font-size:0.8rem;">
                        <span style="width:14px;height:14px;border-radius:50%;background:#F97B3D;display:inline-block;"></span> Moyen
                    </span>
                    <span style="display:flex;align-items:center;gap:6px;font-size:0.8rem;">
                        <span style="width:14px;height:14px;border-radius:50%;background:#E05206;display:inline-block;"></span> Élevé
                    </span>
                    <span style="display:flex;align-items:center;gap:6px;font-size:0.8rem;">
                        <span style="width:14px;height:14px;border-radius:50%;background:#DC3545;display:inline-block;"></span> Critique
                    </span>
                    <span style="display:flex;align-items:center;gap:6px;font-size:0.8rem;">
                        <span style="width:14px;height:14px;border-radius:50%;background:#9e9e9e;display:inline-block;"></span> Sans coordonnées
                    </span>
                    <span class="text-muted ms-auto" style="font-size:0.8rem;">
                        <span x-text="sitesCount"></span> site(s) avec coordonnées GPS
                    </span>
                </div>
            </div>
        </div>

        {{-- Carte --}}
        <div class="card" style="border-radius:8px;overflow:hidden;">
            <div id="map-container" style="height:600px;width:100%;"></div>
        </div>

        {{-- Panel info site sélectionné --}}
        <div x-show="selectedSite" x-transition class="card mt-2" style="border-radius:8px;">
            <div class="card-body" style="padding:16px 24px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h6 style="margin:0 0 4px;" x-text="selectedSite?.name"></h6>
                        <div class="text-muted" style="font-size:0.85rem;">
                            <code x-text="selectedSite?.code"></code>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <template x-if="selectedSite">
                            <span :style="'background:' + riskBadgeStyle(selectedSite.risk_level).bg + ';color:' + riskBadgeStyle(selectedSite.risk_level).color + ';padding:2px 12px;border-radius:12px;font-size:0.8rem;font-weight:600;'">
                                <span x-text="riskLabel(selectedSite.risk_level)"></span>
                            </span>
                        </template>
                    </div>
                </div>
                <div style="margin-top:10px;display:flex;gap:24px;flex-wrap:wrap;">
                    <span class="text-muted" style="font-size:0.85rem;">
                        <i data-feather="alert-circle" style="width:14px;height:14px;"></i>
                        <span x-text="selectedSite?.open_complaints_count ?? 0"></span> plainte(s) ouverte(s)
                    </span>
                </div>
                <div style="margin-top:12px;">
                    <template x-if="selectedSite">
                        <a :href="'/sites/' + selectedSite.id" class="btn btn-primary btn-sm">
                            <i data-feather="eye" style="width:14px;height:14px;"></i> Voir le site
                        </a>
                    </template>
                    <button @click="selectedSite = null" class="btn btn-secondary btn-sm ms-2">
                        Fermer
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    function siteMap() {
        return {
            map: null,
            sitesCount: 0,
            selectedSite: null,

            riskColors: {
                low:      '#009A44',
                medium:   '#F97B3D',
                high:     '#E05206',
                critical: '#DC3545',
            },

            riskLabel(level) {
                return { low: 'Faible', medium: 'Moyen', high: 'Élevé', critical: 'Critique' }[level] ?? level;
            },

            riskBadgeStyle(level) {
                const styles = {
                    low:      { bg: '#e8f5e9', color: '#009A44' },
                    medium:   { bg: '#fff3e0', color: '#F97B3D' },
                    high:     { bg: '#fbe9e7', color: '#E05206' },
                    critical: { bg: '#ffebee', color: '#DC3545' },
                };
                return styles[level] ?? { bg: '#f5f5f5', color: '#6C757D' };
            },

            async init() {
                // Centré sur le Niger
                this.map = L.map('map-container').setView([17.5, 8.5], 6);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                    maxZoom: 18,
                }).addTo(this.map);

                const resp = await fetch('{{ route('sites.map.data') }}');
                const sites = await resp.json();
                this.sitesCount = sites.length;

                sites.forEach(site => {
                    const color = this.riskColors[site.risk_level] ?? '#9e9e9e';
                    const icon = L.divIcon({
                        className: '',
                        html: `<div style="
                            width:18px;height:18px;border-radius:50%;
                            background:${color};border:2px solid white;
                            box-shadow:0 1px 4px rgba(0,0,0,0.4);
                            cursor:pointer;
                        "></div>`,
                        iconSize: [18, 18],
                        iconAnchor: [9, 9],
                    });

                    const marker = L.marker([site.latitude, site.longitude], { icon })
                        .addTo(this.map)
                        .bindPopup(`
                            <div style="min-width:180px;">
                                <div style="font-weight:600;margin-bottom:4px;">${site.name}</div>
                                <div style="font-size:0.8rem;color:#666;margin-bottom:6px;">
                                    <code>${site.code}</code>
                                </div>
                                <div style="font-size:0.85rem;margin-bottom:8px;">
                                    Risque : <strong style="color:${color}">${this.riskLabel(site.risk_level)}</strong>
                                </div>
                                <div style="font-size:0.8rem;color:#888;">
                                    ${site.open_complaints_count} plainte(s) ouverte(s)
                                </div>
                                <div style="margin-top:8px;">
                                    <a href="/sites/${site.id}"
                                       style="display:inline-block;padding:4px 12px;background:#E05206;color:white;border-radius:4px;font-size:0.8rem;text-decoration:none;">
                                        Voir le site
                                    </a>
                                </div>
                            </div>
                        `);

                    marker.on('click', () => {
                        this.selectedSite = site;
                    });
                });
            },
        };
    }
    </script>
    @endpush

</x-layout.app>
