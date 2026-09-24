@props(['material'])
@php
    // Skala meteran: 2x stok minimum atau stok saat ini, mana yang lebih besar.
    $scale = max($material->stock, $material->minimum_stock * 2, 0.001);
    $usablePct = min(100, $material->usable_stock / $scale * 100);
    $expiredPct = min(100 - $usablePct, $material->expired_stock / $scale * 100);
    $minPct = min(100, $material->minimum_stock / $scale * 100);
@endphp
<div @class(['gauge', 'is-low' => $material->is_low]) role="img"
     aria-label="Stok layak pakai {{ qty($material->usable_stock) }} {{ $material->unit }}, minimum {{ qty($material->minimum_stock) }}">
    <div class="fill" style="width: {{ $usablePct }}%"></div>
    @if ($expiredPct > 0)
        <div class="expired" style="left: {{ $usablePct }}%; width: {{ $expiredPct }}%"></div>
    @endif
    @if ($material->minimum_stock > 0)
        <div class="min" style="left: calc({{ $minPct }}% - 1px)" title="Stok minimum"></div>
    @endif
</div>
