@extends('layouts.app')
@section('title', $plan->number)

@php
    use App\Domain\Production\ProductionStatus;
    $flow = ProductionStatus::flow();
    $currentIndex = array_search($plan->status, $flow, true);
    $shortages = $requirements ? collect($requirements['rows'])->where('shortage', '>', 0) : collect();
    $orders = $plan->items->pluck('preOrder')->filter()->unique('id');
@endphp

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('production.index') }}">Rencana produksi</a></div>
        <h1>Produksi {{ tanggal($plan->plan_date, 'l, d M Y') }}</h1>
        <p class="sub">{{ $plan->number }} · dibuat oleh {{ $plan->creator?->name ?? '-' }}</p>
    </div>
    <div class="actions">
        <button type="button" class="btn btn-light" onclick="window.print()">Cetak</button>
        @can('manage-transactions')
            @if ($plan->status === ProductionStatus::Planned)
                <a href="{{ route('production.edit', $plan->id) }}" class="btn btn-light">Ubah</a>
            @endif
        @endcan
    </div>
</div>

<section class="panel mb-4">
    <div class="panel-body">
        @if ($plan->status === ProductionStatus::Cancelled)
            <div class="d-flex align-items-center gap-2"><x-status :status="$plan->status"/><span class="text-muted">Rencana ini dibatalkan. Stok tidak berubah.</span></div>
        @else
            <ol class="steps">
                @foreach ($flow as $i => $step)
                    <li @class(['done' => $i < $currentIndex || $plan->status === ProductionStatus::Completed, 'current' => $i === $currentIndex && $plan->status !== ProductionStatus::Completed])>{{ $step->label() }}</li>
                @endforeach
            </ol>
        @endif
    </div>
    @can('manage-transactions')
        @if ($plan->status->isOpen())
            <div class="panel-foot d-flex flex-wrap align-items-center gap-2 no-print">
                @if ($plan->status === ProductionStatus::Planned)
                    <span class="text-muted small me-auto">Mulai produksi saat adonan mulai dibuat. Pre-order terkait akan berstatus Diproduksi.</span>
                    <form method="POST" action="{{ route('production.start', $plan->id) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-primary btn-sm">Mulai produksi</button>
                    </form>
                @else
                    <span class="text-muted small me-auto">Tandai selesai setelah produk jadi. Bahan baku akan dipotong dari stok sesuai resep.</span>
                    <form method="POST" action="{{ route('production.complete', $plan->id) }}"
                          data-confirm="Tandai produksi selesai? Stok bahan baku akan dipotong dan tidak bisa dibatalkan.">
                        @csrf @method('PATCH')
                        <button class="btn btn-primary btn-sm" @disabled($shortages->isNotEmpty())>Tandai selesai &amp; potong stok</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('production.cancel', $plan->id) }}" data-confirm="Batalkan rencana produksi ini? Pre-order terkait kembali ke Dikonfirmasi.">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-danger btn-sm">Batalkan rencana</button>
                </form>
            </div>
        @endif
    @endcan
</section>

@if ($requirements)
    @if ($requirements['missing_recipes'])
        <div class="alert alert-warning">
            Produk berikut belum punya resep, sehingga kebutuhan bahannya tidak terhitung: <strong>{{ implode(', ', $requirements['missing_recipes']) }}</strong>.
            @can('manage-master-data') Lengkapi di menu <a href="{{ route('products.index') }}">Produk &amp; resep</a>. @else Minta admin melengkapinya. @endcan
        </div>
    @endif
    @if ($shortages->isNotEmpty())
        <div class="alert alert-danger">
            <strong>{{ $shortages->count() }} bahan kurang.</strong> Produksi belum bisa ditandai selesai sampai stoknya cukup.
            @can('manage-transactions') <a href="{{ route('stock-in.create') }}">Catat stok masuk</a>. @endcan
        </div>
    @endif
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <section class="panel">
            <div class="panel-head"><h2>Yang diproduksi</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Produk</th><th class="num">Untuk pre-order</th><th class="num">Tambahan</th><th class="num">Total</th></tr></thead>
                    <tbody>
                    @foreach ($totals as $row)
                        <tr>
                            <td class="fw-semibold">{{ $row->product->name }}</td>
                            <td class="num">{{ qty($row->from_orders) }}</td>
                            <td class="num">{{ qty($row->extra) }}</td>
                            <td class="num fw-semibold">{{ qty($row->quantity) }} {{ $row->product->unit }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2>{{ $plan->status === ProductionStatus::Completed ? 'Bahan yang terpakai' : 'Kebutuhan bahan baku' }}</h2>
                @if ($requirements)<span class="hint">Stok tersedia tidak termasuk batch kedaluwarsa</span>@endif
            </div>
            @if ($requirements)
                @if (empty($requirements['rows']))
                    <x-empty title="Belum ada kebutuhan bahan yang bisa dihitung."></x-empty>
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>Bahan</th><th class="num">Butuh</th><th class="num">Tersedia</th><th class="num">Kekurangan</th></tr></thead>
                            <tbody>
                            @foreach ($requirements['rows'] as $r)
                                <tr>
                                    <td><a href="{{ route('inventory.show', $r['material_id']) }}" class="row-link">{{ $r['name'] }}</a></td>
                                    <td class="num">{{ qty($r['needed']) }} {{ $r['unit'] }}</td>
                                    <td class="num">{{ qty($r['available']) }} {{ $r['unit'] }}</td>
                                    <td class="num">
                                        @if ($r['shortage'] > 0)
                                            <span class="pill pill-danger">kurang {{ qty($r['shortage']) }} {{ $r['unit'] }}</span>
                                        @else
                                            <span class="pill pill-ok">cukup</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @elseif ($plan->movements->isEmpty())
                <x-empty title="Tidak ada pemakaian bahan yang tercatat."></x-empty>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Bahan</th><th>Batch</th><th class="num">Terpakai</th></tr></thead>
                        <tbody>
                        @foreach ($plan->movements as $mv)
                            <tr>
                                <td><a href="{{ route('inventory.show', $mv->raw_material_id) }}" class="row-link">{{ $mv->material->name }}</a></td>
                                <td class="small text-muted">{{ $mv->batch?->batch_code ?: 'Batch #'.$mv->stock_batch_id }}</td>
                                <td class="num">{{ qty($mv->quantity) }} {{ $mv->material->unit }}</td>
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
            <div class="panel-head"><h2>Pre-order di rencana ini</h2></div>
            @if ($orders->isEmpty())
                <x-empty title="Tidak ada pre-order.">Rencana ini hanya berisi produksi tambahan.</x-empty>
            @else
                <ul class="due-list">
                    @foreach ($orders as $order)
                        <li>
                            <div class="flex-grow-1">
                                <a href="{{ route('pre-orders.show', $order->id) }}" class="fw-semibold text-reset text-decoration-none">{{ $order->customer_name }}</a>
                                <span class="cell-sub">{{ $order->number }} · jatuh tempo {{ tanggal($order->due_date) }}</span>
                            </div>
                            <x-status :status="$order->status"/>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="panel">
            <div class="panel-head"><h2>Catatan</h2></div>
            <div class="panel-body">{!! $plan->notes ? nl2br(e($plan->notes)) : '<span class="text-muted">Tidak ada catatan.</span>' !!}</div>
            @can('manage-transactions')
                @if (in_array($plan->status, [ProductionStatus::Planned, ProductionStatus::Cancelled], true))
                    <div class="panel-foot no-print">
                        <x-delete-button :action="route('production.destroy', $plan->id)" label="Hapus rencana"
                                         confirm="Hapus rencana {{ $plan->number }}?"/>
                    </div>
                @endif
            @endcan
        </section>
    </div>
</div>
@endsection
