{{-- Icône par type de plainte (sort_order 1–8) --}}
@props(['type'])

@php
    $icons = [
        1 => 'info',
        2 => 'award',
        3 => 'tool',
        4 => 'briefcase',
        5 => 'home',
        6 => 'tag',
        7 => 'book-open',
        8 => 'shield',
    ];
    $colors = [
        1 => '#0DB02B',
        2 => '#00695c',
        3 => '#e65100',
        4 => '#f57f17',
        5 => '#6d4c41',
        6 => '#7b1fa2',
        7 => '#00838f',
        8 => '#c62828',
    ];
    $order = $type?->sort_order ?? 1;
    $icon  = $icons[$order]  ?? 'help-circle';
    $color = $colors[$order] ?? '#546e7a';
@endphp

<i data-feather="{{ $icon }}"
   style="width:14px;height:14px;color:{{ $color }};"
   title="{{ $type?->label }}"></i>
