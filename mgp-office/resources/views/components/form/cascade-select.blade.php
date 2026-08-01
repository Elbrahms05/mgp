{{--
  Cascade géographique : Région → Département → Commune → Localité [→ Site]
  Props :
    :selected         = ['region_id' => x, 'department_id' => y, 'commune_id' => z, 'locality_id' => w, 'site_id' => v]
    :regions          = collection Region
    :showSite         = bool (afficher le sélecteur de site, défaut true)
    :siteNullableLabel = texte option nulle pour le site (ex : "Plainte hors site")
    :required         = bool
--}}

@props([
    'selected'          => [],
    'regions'           => [],
    'showSite'          => true,
    'siteNullableLabel' => null,
    'required'          => false,
])

<div x-data="cascadeSelect(@js($selected))" class="cascade-select-wrapper">

    <div class="row g-3">

        {{-- Région --}}
        <div class="col-12 col-md-6">
            <label for="region_id" class="form-label">Région</label>
            <select name="region_id" id="region_id"
                    class="form-select"
                    @change="loadDepartments($event.target.value)"
                    x-model="region_id"
                    {{ $required ? 'required' : '' }}>
                <option value="">- Sélectionner une région -</option>
                @foreach($regions as $region)
                <option value="{{ $region->id }}"
                        {{ ($selected['region_id'] ?? '') == $region->id ? 'selected' : '' }}>
                    {{ $region->name }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Département --}}
        <div class="col-12 col-md-6">
            <label for="department_id" class="form-label">Département</label>
            <select name="department_id" id="department_id"
                    class="form-select"
                    @change="loadCommunes($event.target.value)"
                    x-model="department_id"
                    :disabled="departments.length === 0"
                    {{ $required ? 'required' : '' }}>
                <option value="">- Département -</option>
                <template x-for="dept in departments" :key="dept.id">
                    <option :value="dept.id"
                            :selected="dept.id == initialDepartmentId"
                            x-text="dept.name"></option>
                </template>
            </select>
        </div>

        {{-- Commune --}}
        <div class="col-12 col-md-6">
            <label for="commune_id" class="form-label">Commune</label>
            <select name="commune_id" id="commune_id"
                    class="form-select"
                    @change="loadLocalities($event.target.value)"
                    x-model="commune_id"
                    :disabled="communes.length === 0"
                    {{ $required ? 'required' : '' }}>
                <option value="">- Commune -</option>
                <template x-for="commune in communes" :key="commune.id">
                    <option :value="commune.id"
                            :selected="commune.id == initialCommuneId"
                            x-text="commune.name"></option>
                </template>
            </select>
        </div>

        {{-- Localité --}}
        <div class="col-12 col-md-6">
            <label for="locality_id" class="form-label">Localité</label>
            <select name="locality_id" id="locality_id"
                    class="form-select"
                    @if($showSite) @change="loadSites($event.target.value)" @endif
                    x-model="locality_id"
                    :disabled="localities.length === 0"
                    {{ $required ? 'required' : '' }}>
                <option value="">- Localité -</option>
                <template x-for="locality in localities" :key="locality.id">
                    <option :value="locality.id"
                            :selected="locality.id == initialLocalityId"
                            x-text="locality.name"></option>
                </template>
            </select>
        </div>

        @if($showSite)
        {{-- Site --}}
        <div class="col-12">
            <label for="site_id" class="form-label">Site PACIPA</label>
            <select name="site_id" id="site_id"
                    class="form-select"
                    x-model="site_id"
                    :disabled="sites.length === 0">
                <option value="">- Site concerné -</option>
                @if($siteNullableLabel)
                <option value="none">{{ $siteNullableLabel }}</option>
                @endif
                <template x-for="site in sites" :key="site.id">
                    <option :value="site.id"
                            :selected="site.id == initialSiteId"
                            x-text="site.name + ' (' + site.code + ')'"></option>
                </template>
            </select>
        </div>
        @endif

    </div>{{-- .row --}}
</div>{{-- x-data --}}

@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cascadeSelect', (initial) => ({
        region_id:     initial.region_id     || '',
        department_id: initial.department_id || '',
        commune_id:    initial.commune_id    || '',
        locality_id:   initial.locality_id   || '',
        site_id:       initial.site_id       || '',

        initialDepartmentId: initial.department_id || null,
        initialCommuneId:    initial.commune_id    || null,
        initialLocalityId:   initial.locality_id   || null,
        initialSiteId:       initial.site_id       || null,

        departments: [],
        communes:    [],
        localities:  [],
        sites:       [],

        async init() {
            if (this.region_id)     await this.loadDepartments(this.region_id,     false);
            if (this.department_id) await this.loadCommunes(this.department_id,    false);
            if (this.commune_id)    await this.loadLocalities(this.commune_id,     false);
            if (this.locality_id)   await this.loadSites(this.locality_id,         false);
        },

        async loadDepartments(regionId, reset = true) {
            if (reset) { this.departments = []; this.communes = []; this.localities = []; this.sites = []; }
            if (!regionId) return;
            const r = await fetch(`/api/geography/departments?region_id=${regionId}`);
            this.departments = await r.json();
        },

        async loadCommunes(deptId, reset = true) {
            if (reset) { this.communes = []; this.localities = []; this.sites = []; }
            if (!deptId) return;
            const r = await fetch(`/api/geography/communes?department_id=${deptId}`);
            this.communes = await r.json();
        },

        async loadLocalities(communeId, reset = true) {
            if (reset) { this.localities = []; this.sites = []; }
            if (!communeId) return;
            const r = await fetch(`/api/geography/localities?commune_id=${communeId}`);
            this.localities = await r.json();
        },

        async loadSites(localityId, reset = true) {
            if (reset) { this.sites = []; }
            if (!localityId) return;
            const r = await fetch(`/api/geography/sites?locality_id=${localityId}`);
            this.sites = await r.json();
        },
    }));
});
</script>
@endpush
@endonce
