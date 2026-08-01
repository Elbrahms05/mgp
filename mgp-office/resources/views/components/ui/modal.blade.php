@props([
    'id'    => null,
    'title' => '',
    'size'  => 'md',   // sm | md | lg | xl
])

@php
$sizeClass = match($size) {
    'sm'  => 'modal-sm',
    'lg'  => 'modal-lg',
    'xl'  => 'modal-xl',
    default => '',
};
$modalId = $id ?? 'modal-'.uniqid();
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog {{ $sizeClass }} modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            @if($title)
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">{{ $title }}</h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            @endif

            <div class="modal-body">
                {{ $slot }}
            </div>

            @isset($footer)
            <div class="modal-footer">{{ $footer }}</div>
            @endisset

        </div>
    </div>
</div>
