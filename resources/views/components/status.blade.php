@props(['status'])
@php
    $tone = match ($status->value) {
        'draft', 'planned' => 'neutral',
        'confirmed' => 'info',
        'in_production', 'in_progress', 'adjustment' => 'warn',
        'completed', 'in' => 'ok',
        'delivered', 'production' => 'done',
        'cancelled', 'waste' => 'danger',
        default => 'neutral',
    };
@endphp
<span {{ $attributes->class(['status', 't-'.$tone]) }}>{{ $status->label() }}</span>
