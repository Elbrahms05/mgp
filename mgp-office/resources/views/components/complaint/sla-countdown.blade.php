{{--
  Composant SLA countdown
  Props : $complaint (Complaint), $hoursRemaining (float), $isBreached (bool)
--}}
@props(['complaint', 'hoursRemaining', 'isBreached'])

@if(!$complaint->sla_deadline || $complaint->status->isTerminal())
    <span class="text-muted" style="font-size:0.8rem;">-</span>
@elseif($isBreached)
    <span class="sla-countdown overdue d-flex align-items-center gap-1">
        <i data-feather="alarm-off" style="width:14px;height:14px;"></i>
        Dépassé de {{ abs((int)$hoursRemaining) }}h
    </span>
@else
    @php
        $h       = (int) $hoursRemaining;
        $days    = intdiv($h, 24);
        $hours   = $h % 24;
        $warn    = $h <= 24;
    @endphp
    <span class="sla-countdown d-flex align-items-center gap-1"
          style="color: {{ $warn ? '#e65100' : '#2e7d32' }};">
        <i data-feather="clock" style="width:14px;height:14px;"></i>
        @if($days > 0)
            {{ $days }}j {{ $hours }}h restant(s)
        @else
            {{ $hours }}h restant(s)
        @endif
    </span>
@endif
