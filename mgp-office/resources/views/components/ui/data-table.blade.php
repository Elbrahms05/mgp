@props([
    'headers'    => [],
    'empty'      => 'Aucun enregistrement.',
    'striped'    => false,
    'responsive' => true,
])

<div {{ $attributes->merge(['class' => $responsive ? 'table-responsive' : '']) }}>
    <table class="table table-hover {{ $striped ? 'table-striped' : '' }} align-middle mb-0">
        @if(count($headers))
        <thead>
            <tr>
                @foreach($headers as $header)
                <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        @endif
        <tbody>
            @if(isset($body) && trim($body) !== '')
                {{ $body }}
            @elseif(isset($slot) && trim($slot) !== '')
                {{ $slot }}
            @else
            <tr>
                <td colspan="{{ count($headers) ?: 1 }}"
                    class="text-center text-muted py-5">
                    <i data-feather="inbox"
                       style="width:32px;height:32px;opacity:0.4;display:block;margin:0 auto 8px;"></i>
                    {{ $empty }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
