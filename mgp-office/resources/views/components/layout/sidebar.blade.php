<nav class="sidenav shadow-right sidenav-light">
    <div class="sidenav-menu">
        <div class="nav accordion" id="accordionSidenav">

            {{-- ── Logo ─────────────────────────────── --}}
            <div class="sidenav-menu-heading text-white" style="padding:1.25rem 1rem 0.5rem;font-size:1rem;letter-spacing:0;">
                <i data-feather="layers" class="me-2" style="width:18px;height:18px;"></i>
                <span style="font-weight:700;">PACIPA - MGP</span>
                <div style="font-size:0.68rem;opacity:0.6;padding-left:26px;margin-top:2px;">
                    Gestion des Plaintes
                </div>
            </div>

            {{-- ── Dashboard ───────────────────────── --}}
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               href="{{ route('dashboard') }}">
                <div class="nav-link-icon"><i data-feather="grid"></i></div>
                Tableau de bord
            </a>

            {{-- ── Gestion des plaintes ────────────── --}}
            @canany(['complaints.create', 'complaints.view_all', 'complaints.view_own_region', 'vbg.receive', 'workers.view', 'mediation.view'])
            <div class="sidenav-menu-heading">Gestion des plaintes</div>
            @endcanany

            @canany(['complaints.create', 'complaints.view_all', 'complaints.view_own_region'])
            <a class="nav-link {{ request()->routeIs('complaints.*') ? 'active' : '' }}"
               href="{{ route('complaints.index') }}">
                <div class="nav-link-icon"><i data-feather="file-text"></i></div>
                Plaintes
            </a>
            @endcanany

            @can('vbg.receive')
            <a class="nav-link {{ request()->routeIs('vbg.*') ? 'active' : '' }}"
               href="{{ route('vbg.index') }}">
                <div class="nav-link-icon"><i data-feather="lock"></i></div>
                Plaintes VBG/EAS/HS
                <span class="badge bg-danger ms-auto" style="font-size:0.65rem;">Restreint</span>
            </a>
            @endcan

            @can('workers.view')
            <a class="nav-link {{ request()->routeIs('workers.*') ? 'active' : '' }}"
               href="{{ route('workers.index') }}">
                <div class="nav-link-icon"><i data-feather="tool"></i></div>
                Plaintes travailleurs
            </a>
            @endcan

            @can('mediation.view')
            <a class="nav-link {{ request()->routeIs('mediation.*') ? 'active' : '' }}"
               href="{{ route('mediation.index') }}">
                <div class="nav-link-icon"><i data-feather="users"></i></div>
                Médiation
            </a>
            @endcan

            {{-- ── Terrain ──────────────────────────── --}}
            @canany(['committees.view', 'sites.view_all', 'sites.view_own_region'])
            <div class="sidenav-menu-heading">Terrain</div>
            @endcanany

            @can('committees.view')
            <a class="nav-link {{ request()->routeIs('committees.*') ? 'active' : '' }}"
               href="{{ route('committees.index') }}">
                <div class="nav-link-icon"><i data-feather="user-check"></i></div>
                Comités
            </a>
            @endcan

            @canany(['sites.view_all', 'sites.view_own_region'])
            <a class="nav-link {{ request()->routeIs('sites.*') ? 'active' : '' }}"
               href="{{ route('sites.index') }}">
                <div class="nav-link-icon"><i data-feather="map-pin"></i></div>
                Sites PACIPA
            </a>
            @endcanany

            {{-- ── Analyse ──────────────────────────── --}}
            @canany(['statistics.view', 'reports.generate'])
            <div class="sidenav-menu-heading">Analyse</div>
            @endcanany

            @can('statistics.view')
            <a class="nav-link {{ request()->routeIs('statistics.*') ? 'active' : '' }}"
               href="{{ route('statistics.dashboard') }}">
                <div class="nav-link-icon"><i data-feather="bar-chart-2"></i></div>
                Statistiques
            </a>
            @endcan

            @can('reports.generate')
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
               href="{{ route('reports.index') }}">
                <div class="nav-link-icon"><i data-feather="file"></i></div>
                Rapports
            </a>
            @endcan

            {{-- ── Administration ───────────────────── --}}
            @canany(['forms.view', 'communication.view', 'users.manage', 'settings.manage', 'audit.view'])
            <div class="sidenav-menu-heading">Administration</div>
            @endcanany

            @can('forms.view')
            <a class="nav-link {{ request()->routeIs('forms.*') ? 'active' : '' }}"
               href="{{ route('forms.index') }}">
                <div class="nav-link-icon"><i data-feather="smartphone"></i></div>
                Formulaires ODK
            </a>
            @endcan

            @can('communication.view')
            <a hidden class="nav-link {{ request()->routeIs('communication.*') ? 'active' : '' }}"
               href="{{ route('communication.index') }}">
                <div class="nav-link-icon"><i data-feather="speaker"></i></div>
                Communication
            </a>
            @endcan

            @can('users.manage')
            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
               href="{{ route('users.index') }}">
                <div class="nav-link-icon"><i data-feather="user-check"></i></div>
                Utilisateurs
            </a>
            @endcan

            @can('companies.manage')
            <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}"
               href="{{ route('companies.index') }}">
                <div class="nav-link-icon"><i data-feather="briefcase"></i></div>
                Entreprises
            </a>
            @endcan

            @can('settings.manage')
            <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
               href="{{ route('settings.index') }}">
                <div class="nav-link-icon"><i data-feather="settings"></i></div>
                Paramétrage
            </a>
            @endcan

            @can('audit.view')
            <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"
               href="{{ route('audit.index') }}">
                <div class="nav-link-icon"><i data-feather="clock"></i></div>
                Journal d'audit
            </a>
            @endcan

            @role('super_admin')
            <a class="nav-link {{ request()->routeIs('documentation.*') ? 'active' : '' }}"
               href="{{ route('documentation.index') }}">
                <div class="nav-link-icon"><i data-feather="help-circle"></i></div>
                Guides contextuels
            </a>
            @endrole

            {{-- ── Séparateur + Déconnexion ─────────── --}}
            <div class="sidenav-menu-heading"></div>
            <div class="nav-link" style="padding-bottom:1.5rem;">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="#" onclick="this.closest('form').submit()"
                       class="nav-link text-danger" style="padding:0;">
                        <div class="nav-link-icon"><i data-feather="log-out"></i></div>
                        Déconnexion
                    </a>
                </form>
            </div>

        </div>
    </div>
</nav>
