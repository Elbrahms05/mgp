<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'PACIPA - MGP - Connexion' }}</title>
    <!-- Feather Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"
        crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-primary">

<div class="container-xl">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">

            {{-- ── Logo ─────────────────────────────────────── --}}
            <div class="text-center mt-5 mb-4">
                <div class="mb-3">
                    <i data-feather="layers"
                       style="width:48px;height:48px;color:#ffffff;"></i>
                </div>
                <h4 class="text-white fw-bold mb-1">PACIPA &mdash; MGP</h4>
                <p class="text-white-50 small mb-0">
                    Mécanisme de Gestion des Plaintes<br>
                    Sensible aux VBG / EAS / HS
                </p>
            </div>

            {{-- ── Carte formulaire ─────────────────────────── --}}
            <div class="card shadow-lg border-0 rounded-lg mb-4">
                <div class="card-body p-4">
                    {{ $slot }}
                </div>
            </div>

            {{-- ── Pied de page ─────────────────────────────── --}}
            <div class="text-center text-white-50 small pb-4">
                Projet PACIPA &mdash; P179272 &nbsp;&bull;&nbsp;
                Banque mondiale / IDA &nbsp;&bull;&nbsp;
                République du Niger
            </div>

        </div>
    </div>
</div>

</body>
</html>
