<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ?? 'PACIPA - MGP' }}</title>
    <!-- Font Awesome -->
    <script data-search-pseudo-elements defer
        src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js"
        crossorigin="anonymous"></script>
    <!-- Feather Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"
        crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="nav-fixed">

    {{-- ── Topnav ──────────────────────────────────────────── --}}
    <x-layout.topbar />

    <div id="layoutSidenav">

        {{-- ── Sidebar ─────────────────────────────────────── --}}
        <div id="layoutSidenav_nav">
            <x-layout.sidebar />
        </div>

        {{-- ── Contenu principal ───────────────────────────── --}}
        <div id="layoutSidenav_content">
            <main>

                {{-- Page header (optionnel via slot) --}}
                @isset($header)
                {{ $header }}
                @else
                <header class="page-header page-header-dark page-header-pacipa pb-6">
                    <div class="container-fluid px-4">
                        <div class="page-header-content pt-3">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mt-2">
                                    {{-- Titre avec icône (optionnel) --}}
                                    @if ($title)
                                    <h1 class="page-header-title">
                                        @if ($icon)
                                        <div class="page-header-icon">
                                            <i data-feather="{{ $icon }}"></i>
                                        </div>
                                        @endif
                                        {{ $title }}
                                    </h1>
                                    @endif
                                    {{-- Description (optionnelle) --}}
                                    @if ($description)
                                    <div class="page-header-subtitle">{{ $description }}</div>
                                    @endif
                                </div>
                                {{-- Actions/Boutons à droite (optionnel) --}}
                                @isset($actions)
                                <div class="col-auto mt-2">
                                    <div class="d-flex gap-2 align-items-center">
                                        {{ $actions }}
                                    </div>
                                </div>
                                @endisset
                            </div>
                            {{-- Breadcrumb (optionnel) --}}
                            @isset($breadcrumb)
                            <nav class="mt-4 rounded" aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-light px-3 py-2 rounded mb-0">
                                    {{ $breadcrumb }}
                                </ol>
                            </nav>
                            @endisset
                        </div>
                    </div>
                </header>
                @endisset

                {{-- Flash messages --}}
                @if (session('success'))
                <div class="container-fluid px-4 mt-3">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-feather="check-circle" class="me-2" style="width:16px;height:16px;"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
                @endif

                @if (session('error'))
                <div class="container-fluid px-4 mt-3">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i data-feather="alert-circle" class="me-2" style="width:16px;height:16px;"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
                @endif

                {{-- Contenu page --}}
                <div class="container-fluid px-4">
                    {{ $slot }}
                </div>

            </main>

            <footer class="footer-admin mt-auto footer-light">
                <div class="container-xl px-4">
                    <div class="d-flex align-items-center justify-content-between small py-2">
                        <span class="text-muted">
                            Mécanisme de Gestion des Plaintes &mdash; Banque mondiale / IDA
                        </span>
                        <span class="text-muted">
                            République du Niger &copy; {{ date('Y') }}
                        </span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    {{-- Panneau aide contextuelle --}}
    
    @stack('scripts')
</body>
</html>
