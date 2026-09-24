@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
<div class="page-head">
    <div>
        <h1>Halo, {{ strtok(auth()->user()->name, ' ') }}</h1>
        <p class="today">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    @can('manage-transactions')
        <div class="actions">
            <a href="{{ route('stock-in.create') }}" class="btn btn-light">Catat stok masuk</a>
            <a href="{{ route('production.create') }}" class="btn btn-light">Buat rencana produksi</a>
            <a href="{{ route('pre-orders.create') }}" class="btn btn-primary"><x-icon name="plus" width="16" height="16"/> Pre-order baru</a>
        </div>
    @endcan
</div>

<div class="figures mb-4">
    <a class="figure" href="{{ route('pre-orders.index') }}">
        <div class="label">Pre-order berjalan</div>
        <div class="value">{{ $open_orders }}</div>
        @if ($overdue_orders)
            <div class="note text-danger">{{ $overdue_orders }} lewat jatuh tempo</div>
        @else
            <div class="note text-muted">Tidak ada yang terlambat</div>
        @endif
    </a>
    <a class="figure" href="{{ route('pre-orders.index', ['from' => today()->startOfMonth()->toDateString()]) }}">
        <div class="label">Nilai pesanan bulan ini</div>
        <div class="value">{{ rupiah($month_revenue) }}</div>
        <div class="note text-muted">Tanpa pesanan dibatalkan</div>
    </a>
    <a class="figure" href="{{ route('inventory.index', ['filter' => 'low']) }}">
        <div class="label">Bahan perlu dibeli</div>
        <div class="value {{ $low_stock->count() ? 'text-danger' : '' }}">{{ $low_stock->count() }}</div>
        <div class="note text-muted">dari {{ $materials->count() }} bahan baku</div>
    </a>
    <a class="figure" href="{{ route('inventory.index', ['filter' => 'expiring']) }}">
        <div class="label">Batch hampir/sudah kedaluwarsa</div>
        <div class="value {{ $expiring->count() ? 'text-warn' : '' }}">{{ $expiring->count() }}</div>
        <div class="note text-muted">dalam 14 hari ke depan</div>
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <section class="panel">
            <div class="panel-head">
                <h2>Jatuh tempo 7 hari ke depan</h2>
                <a href="{{ route('pre-orders.index') }}" class="small">Semua pre-order</a>
            </div>
            @if ($upcoming_orders->isEmpty())
                <x-empty title="Tidak ada pesanan yang jatuh tempo minggu ini.">Pesanan baru akan muncul di sini sesuai tanggal jatuh temponya.</x-empty>
            @else
                <ul class="due-list">
                    @foreach ($upcoming_orders as $order)
                        @php $days = (int) today()->diffInDays($order->due_date, false); @endphp
                        <li>
                            <div @class(['due-date', 'late' => $days < 0, 'soon' => $days >= 0 && $days <= 1])>
                                <b>{{ $order->due_date->format('d') }}</b>
                                <span>{{ $order->due_date->translatedFormat('M') }}</span>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <a href="{{ route('pre-orders.show', $order->id) }}" class="fw-semibold text-decoration-none text-reset">{{ $order->customer_name }}</a>
                                <span class="cell-sub">
                                    {{ $order->number }} ·
                                    @if ($days < 0) <span class="text-danger">terlambat {{ abs($days) }} hari</span>
                                    @elseif ($days === 0) <span class="text-warn">hari ini</span>
                                    @elseif ($days === 1) <span class="text-warn">besok</span>
                                    @else {{ $days }} hari lagi @endif
                                </span>
                            </div>
                            <x-status :status="$order->status"/>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2>Produksi terjadwal</h2>
                <a href="{{ route('production.index') }}" class="small">Semua rencana</a>
            </div>
            @if ($plans->isEmpty())
                <x-empty title="Belum ada produksi yang dijadwalkan."
                         :action="auth()->user()->can('manage-transactions') ? 'Buat rencana produksi' : null"
                         :href="route('production.create')">Gabungkan pre-order yang sudah dikonfirmasi menjadi satu rencana.</x-empty>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Tanggal</th><th>Produk</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($plans as $plan)
                            <tr>
                                <td class="text-nowrap">
                                    <a href="{{ route('production.show', $plan->id) }}" class="row-link">{{ tanggal($plan->plan_date, 'D, d M') }}</a>
                                    <span class="cell-sub">{{ $plan->number }}</span>
                                </td>
                                <td>{{ $plan->items->groupBy('product_id')->map(fn ($r) => $r->first()->product->name.' '.qty($r->sum('quantity')))->implode(', ') }}</td>
                                <td><x-status :status="$plan->status"/></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <div class="col-lg-5">
        <section class="panel">
            <div class="panel-head">
                <h2>Stok yang perlu diperhatikan</h2>
                <a href="{{ route('inventory.index') }}" class="small">Semua stok</a>
            </div>
            @php $watch = $materials->filter(fn ($m) => $m->is_low || $m->expired_stock > 0)->sortBy(fn ($m) => $m->usable_stock / max($m->minimum_stock, 0.001))->take(6); @endphp
            @if ($watch->isEmpty())
                <x-empty title="Semua stok aman.">Tidak ada bahan di bawah stok minimum.</x-empty>
            @else
                <div class="panel-body d-grid gap-3">
                    @foreach ($watch as $m)
                        <div>
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <a href="{{ route('inventory.show', $m->id) }}" class="fw-semibold text-reset text-decoration-none">{{ $m->name }}</a>
                                <span class="tabular small">{{ qty($m->usable_stock) }} / min {{ qty($m->minimum_stock) }} {{ $m->unit }}</span>
                            </div>
                            <x-gauge :material="$m"/>
                        </div>
                    @endforeach
                    <div class="gauge-legend">
                        <span><i style="background:var(--pandan)"></i>Layak pakai</span>
                        <span><i style="background:repeating-linear-gradient(-45deg,var(--kunyit-bright) 0 3px,#F6DCA0 3px 6px)"></i>Kedaluwarsa</span>
                        <span><i style="background:var(--ink);width:2px"></i>Batas minimum</span>
                    </div>
                </div>
            @endif
        </section>

        <section class="panel">
            <div class="panel-head"><h2>Kedaluwarsa terdekat</h2></div>
            @if ($expiring->isEmpty())
                <x-empty title="Tidak ada batch yang akan kedaluwarsa dalam 14 hari."></x-empty>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                        @foreach ($expiring->take(8) as $batch)
                            @php $d = $batch->daysToExpiry(); @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('inventory.show', $batch->raw_material_id) }}" class="row-link">{{ $batch->material->name }}</a>
                                    <span class="cell-sub">{{ $batch->batch_code ?: 'Batch #'.$batch->id }} · sisa {{ qty($batch->quantity_remaining) }} {{ $batch->material->unit }}</span>
                                </td>
                                <td class="text-end">
                                    @if ($d < 0) <span class="pill pill-danger">lewat {{ abs($d) }} hari</span>
                                    @elseif ($d === 0) <span class="pill pill-danger">hari ini</span>
                                    @else <span class="pill pill-warn">{{ $d }} hari lagi</span> @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
