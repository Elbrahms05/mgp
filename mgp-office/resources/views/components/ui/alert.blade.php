@props([
    'type'    => 'info',   // success | error | warning | info
    'icon'    => null,
    'dismiss' => false,
])

@php
$map = [
    'success' => ['class' => 'alert-success',  'icon' => 'check-circle'],
    'error'   => ['class' => 'alert-danger',   'icon' => 'alert-circle'],
    'warning' => ['class' => 'alert-warning',  'icon' => 'alert-triangle'],
    'info'    => ['class' => 'alert-info',     'icon' => 'info'],
];
$cfg         = $map[$type] ?? $map['info'];
$alertClass  = $cfg['class'];
$featherIcon = $icon ?? $cfg['icon'];
@endphp

<div {{ $attributes->merge(['class' => "alert $alertClass d-flex align-items-start"]) }}
     role="alert"
     @if($dismiss) x-data="{ show: true }" x-show="show" @endif>
    <i data-feather="{{ $featherIcon }}" class="me-2 flex-shrink-0"
       style="width:16px;height:16px;margin-top:2px;"></i>
    <span class="flex-grow-1">{{ $slot }}</span>
    @if($dismiss)
    <button type="button" class="btn-close ms-2" @click="show=false" aria-label="Fermer"></button>
    @endif
</div>
