@props([
    'title'  => null,
    'icon'   => null,
    'color'  => null,
    'noPad'  => false,
])

<div {{ $attributes->merge(['class' => 'card mb-4']) }}>
    @if ($title || isset($actions))
    <div class="card-header d-flex align-items-center justify-content-between">
        <span>
            @if ($icon)
            <i data-feather="{{ $icon }}" class="me-2"
               style="width:16px;height:16px;{{ $color ? 'color:'.$color.';' : '' }}"></i>
            @endif
            {{ $title }}
        </span>
        @isset($actions)
        <span>{{ $actions }}</span>
        @endisset
    </div>
    @endif

    <div class="card-body {{ $noPad ? 'p-0' : '' }}">
        {{ $slot }}
    </div>

    @isset($footer)
    <div class="card-footer">{{ $footer }}</div>
    @endisset
</div>
