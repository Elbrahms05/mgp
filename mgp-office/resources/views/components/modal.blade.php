@props([
    'name',
    'show'     => false,
    'maxWidth' => 'md',   // sm | md | lg | xl
])

@php
$sizeClass = match($maxWidth) {
    'sm'  => 'modal-sm',
    'lg'  => 'modal-lg',
    'xl'  => 'modal-xl',
    default => '',
};
@endphp

<div class="modal fade {{ $show ? 'show' : '' }}"
     id="{{ $name }}"
     tabindex="-1"
     aria-hidden="{{ $show ? 'false' : 'true' }}"
     style="{{ $show ? 'display:block;' : '' }}"
     x-on:open-modal.window="$event.detail == '{{ $name }}' && bootstrap.Modal.getOrCreateInstance($el).show()"
     x-on:close-modal.window="$event.detail == '{{ $name }}' && bootstrap.Modal.getInstance($el)?.hide()">
    <div class="modal-dialog {{ $sizeClass }} modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            {{ $slot }}
        </div>
    </div>
</div>
