@props(['status'])

@php
    $label = $status->label();
    $css   = 'badge-'.$status->value;
@endphp

<span class="badge {{ $css }}" style="font-size:0.72rem;font-weight:500;border-radius:12px;padding:4px 10px;">
    {{ $label }}
</span>
