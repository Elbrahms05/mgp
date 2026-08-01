<nav class="topnav navbar navbar-expand shadow bg-white" id="sidenavAccordion">
    <div class="container-fluid px-3">

        {{-- ── Burger sidebar toggle ─────────────────────────── --}}
        <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-0 me-lg-2"
                id="sidebarToggle">
            <i data-feather="menu"></i>
        </button>

        {{-- ── Brand (mobile only) ───────────────────────────── --}}
        <a class="navbar-brand pe-3 ps-4 ps-lg-0 order-0" href="{{ route('dashboard') }}">
            <span style="color:#263238;font-weight:700;">PACIPA&mdash;MGP</span>
        </a>

        {{-- ── Droite ────────────────────────────────────────── --}}
        <ul class="navbar-nav align-items-center ms-auto">

            {{-- ── Sync ODK (forms.manage uniquement) ───────── --}}
            @can('forms.manage')
            <li class="nav-item me-1"
                x-data="odkSync()"
                x-init="init()"
                title="Synchroniser les soumissions ODK Central">

                <button type="button"
                        class="btn btn-icon btn-transparent-dark position-relative"
                        @click="run"
                        :disabled="syncing"
                        :title="syncing ? 'Synchronisation en cours…' : 'Synchroniser ODK Central'">

                    {{-- Icône avec spin --}}
                    <span x-show="!syncing">
                        <i data-feather="refresh-cw" style="width:18px;height:18px;"></i>
                    </span>
                    <span x-show="syncing" x-cloak>
                        <svg style="width:18px;height:18px;animation:spin 1s linear infinite;"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                        </svg>
                    </span>

                    {{-- Badge résultat --}}
                    <span x-show="badge !== null && !syncing"
                          x-cloak
                          :class="badgeOk ? 'bg-success' : 'bg-danger'"
                          class="position-absolute top-0 end-0 badge rounded-pill"
                          style="font-size:0.55rem;padding:2px 2px;min-width:16px;"
                          x-text="badge">
                    </span>
                </button>

                {{-- Toast de résultat --}}
                <div x-show="toast"
                     x-cloak
                     x-transition.duration.300ms
                     :class="badgeOk ? 'border-success' : 'border-danger'"
                     class="position-fixed bottom-0 end-0 m-3 toast show border"
                     style="z-index:9999;min-width:260px;max-width:340px;">
                    <div class="toast-header border-0 py-2"
                         :class="badgeOk ? 'bg-success text-white' : 'bg-danger text-white'">
                        <i data-feather="refresh-cw" style="width:14px;height:14px;" class="me-2"></i>
                        <strong class="me-auto small">Sync ODK Central</strong>
                        <button type="button" class="btn-close btn-close-white ms-2"
                                @click="toast = false"></button>
                    </div>
                    <div class="toast-body small py-2 bg-white" x-text="toastMsg"></div>
                </div>
            </li>
            @endcan

            {{-- Sélecteur de langue --}}
            <li class="nav-item dropdown no-caret me-2">
                <a class="nav-link dropdown-toggle d-flex align-items-center"
                   href="#" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <i data-feather="globe" style="width:18px;height:18px;" class="me-1"></i>
                    <span class="small fw-semibold">{{ strtoupper(app()->getLocale()) }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <a class="dropdown-item {{ app()->getLocale() === 'fr' ? 'active' : '' }}"
                           href="{{ route('locale.set', 'fr') }}">
                            Français
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ app()->getLocale() === 'ha' ? 'active' : '' }}"
                           href="{{ route('locale.set', 'ha') }}">
                            Haoussa
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ app()->getLocale() === 'zr' ? 'active' : '' }}"
                           href="{{ route('locale.set', 'zr') }}">
                            Zarma
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Notifications --}}
            <li class="nav-item dropdown no-caret me-3 me-lg-4">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle"
                   href="#" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <i data-feather="bell"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow" style="min-width:20rem;">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2
                                border-bottom">
                        <span class="fw-semibold small text-uppercase">Notifications</span>
                    </div>
                    <div class="px-3 py-3 text-muted small text-center">
                        Aucune notification pour l'instant.
                    </div>
                </div>
            </li>

            {{-- Utilisateur --}}
            <li class="nav-item dropdown no-caret">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle d-flex align-items-center"
                   href="#" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex flex-column text-end me-2 d-none d-lg-block">
                        <div class="small fw-semibold" style="color:#263238;line-height:1.2;">
                            {{ auth()->user()?->name ?? 'Utilisateur' }}
                        </div>
                        <div class="small text-muted" style="font-size:0.7rem;line-height:1.2;">
                            {{ auth()->user()?->email ?? '' }}
                        </div>
                    </div>
                    <i data-feather="user" style="width:20px;height:20px;"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                    <li>
                        <h6 class="dropdown-header d-flex align-items-center">
                            <i data-feather="user" class="me-2" style="width:14px;height:14px;"></i>
                            {{ auth()->user()?->name ?? 'Utilisateur' }}
                        </h6>
                    </li>
                    <li><hr class="dropdown-divider m-0"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i data-feather="settings" class="me-2" style="width:14px;height:14px;"></i>
                            Mon profil
                        </a>
                    </li>
                    <li><hr class="dropdown-divider m-0"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="#" class="dropdown-item text-danger"
                               onclick="this.closest('form').submit()">
                                <i data-feather="log-out" class="me-2" style="width:14px;height:14px;"></i>
                                Déconnexion
                            </a>
                        </form>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</nav>
