@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success d-flex align-items-center gap-2 py-2']) }} role="alert">
        <i data-feather="check-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
        <span class="small">{{ $status }}</span>
    </div>
@endif
