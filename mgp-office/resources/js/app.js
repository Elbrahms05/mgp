import './bootstrap';

import Alpine from 'alpinejs';
import Chart  from 'chart.js/auto';
import * as bootstrap from 'bootstrap';
import feather from 'feather-icons';

window.Alpine    = Alpine;
window.Chart     = Chart;
window.bootstrap = bootstrap;
window.feather   = feather;

/* ── Composants Alpine réutilisables ─────────────────────── */

Alpine.data('cascadeSelect', () => ({
    departments: [],
    communes:    [],
    localities:  [],
    sites:       [],

    async loadDepartments(regionId) {
        this.departments = [];
        this.communes    = [];
        this.localities  = [];
        this.sites       = [];
        if (!regionId) return;
        const res = await fetch(`/api/geography/departments?region_id=${regionId}`);
        this.departments = await res.json();
    },

    async loadCommunes(deptId) {
        this.communes   = [];
        this.localities = [];
        this.sites      = [];
        if (!deptId) return;
        const res = await fetch(`/api/geography/communes?department_id=${deptId}`);
        this.communes = await res.json();
    },

    async loadLocalities(communeId) {
        this.localities = [];
        this.sites      = [];
        if (!communeId) return;
        const res = await fetch(`/api/geography/localities?commune_id=${communeId}`);
        this.localities = await res.json();
    },

    async loadSites(localityId) {
        this.sites = [];
        if (!localityId) return;
        const res = await fetch(`/api/geography/sites?locality_id=${localityId}`);
        this.sites = await res.json();
    },
}));

Alpine.data('slaCountdown', (deadline) => ({
    deadline:  new Date(deadline),
    remaining: '',
    isOverdue: false,
    interval:  null,

    init() {
        this.update();
        this.interval = setInterval(() => this.update(), 60000);
    },

    update() {
        const diff     = this.deadline - new Date();
        this.isOverdue = diff < 0;
        const abs      = Math.abs(diff);
        const days     = Math.floor(abs / 86400000);
        const hours    = Math.floor((abs % 86400000) / 3600000);
        this.remaining = `${days}j ${hours}h`;
    },

    destroy() { clearInterval(this.interval); },
}));

Alpine.data('helpPanel', () => ({
    open: false,

    init() {
        const key  = `help_panel_${window.location.pathname}`;
        this.open  = localStorage.getItem(key) !== 'hidden';
    },

    toggle() {
        this.open = !this.open;
        localStorage.setItem(
            `help_panel_${window.location.pathname}`,
            this.open ? 'visible' : 'hidden'
        );
    },
}));

Alpine.data('complaintFilter', () => ({
    status:   '',
    type:     '',
    region:   '',
    dateFrom: '',
    dateTo:   '',

    apply() {
        const params = new URLSearchParams();
        if (this.status)   params.set('status',    this.status);
        if (this.type)     params.set('type',       this.type);
        if (this.region)   params.set('region',     this.region);
        if (this.dateFrom) params.set('date_from',  this.dateFrom);
        if (this.dateTo)   params.set('date_to',    this.dateTo);
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    },

    reset() { window.location.href = window.location.pathname; },
}));

Alpine.data('chartDashboard', () => ({
    charts: {},

    renderBar(id, labels, data, label) {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        this.charts[id] = new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets: [{ label, data, backgroundColor: '#0DB02B' }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });
    },

    renderDoughnut(id, labels, data) {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        this.charts[id] = new Chart(ctx, {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: ['#0DB02B','#2e7d32','#e65100','#c62828','#546e7a','#f57f17','#00695c','#37474f'] }] },
            options: { responsive: true },
        });
    },
}));

Alpine.data('odkSync', () => ({
    syncing:  false,
    badge:    null,
    badgeOk:  true,
    toast:    false,
    toastMsg: '',
    _timer:   null,

    init() {
        // Ajouter l'animation spin via CSS si absente
        if (!document.getElementById('odk-spin-style')) {
            const s = document.createElement('style');
            s.id = 'odk-spin-style';
            s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
            document.head.appendChild(s);
        }
    },

    async run() {
        if (this.syncing) return;
        this.syncing  = true;
        this.toast    = false;
        this.badge    = null;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        try {
            const res  = await fetch('/odk/sync', {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const data = await res.json();

            this.badgeOk  = data.success;
            this.toastMsg = data.message ?? (data.success ? 'Synchronisation terminée.' : 'Erreur.');
            this.badge    = data.result?.processed ?? (data.success ? '✓' : '!');
        } catch (e) {
            this.badgeOk  = false;
            this.toastMsg = 'Erreur réseau - ODK Central inaccessible.';
            this.badge    = '!';
        } finally {
            this.syncing = false;
            this.toast   = true;
            feather.replace();

            // Masquer le badge et le toast après 8 secondes
            clearTimeout(this._timer);
            this._timer = setTimeout(() => { this.toast = false; this.badge = null; }, 8000);
        }
    },
}));

/* ── Initialisation globale ──────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    // Feather Icons
    feather.replace();

    // Bootstrap tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    // Bootstrap popovers
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        new bootstrap.Popover(el);
    });

    // Sidebar toggle (SB Admin Pro)
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        // Restore sidebar state
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.body.classList.add('sidenav-toggled');
        }
        sidebarToggle.addEventListener('click', e => {
            e.preventDefault();
            document.body.classList.toggle('sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sidenav-toggled'));
        });
    }

    // Fermer sidebar au clic sur le contenu (mobile)
    const layoutContent = document.getElementById('layoutSidenav_content');
    if (layoutContent) {
        layoutContent.addEventListener('click', () => {
            if (window.innerWidth < 992 && document.body.classList.contains('sidenav-toggled')) {
                document.body.classList.remove('sidenav-toggled');
            }
        });
    }
});

Alpine.start();
