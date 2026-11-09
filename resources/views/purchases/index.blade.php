@extends('layouts.app')
@section('title', 'Pemesanan ke supplier')

@section('content')
<div class="page-head">
    <div>
        <h1>Pemesanan ke supplier</h1>
        <p class="sub">Barang yang dipesan gudang ke supplier. Saat barang datang, tandai diterima dan stok bahan baku bertambah otomatis.</p>
    </div>
    @can('pembelian.kelola')
        <div class="actions">
            <a href="{{ route('purchases.create') }}" class="btn btn-primary"><x-icon name="plus" width="16" height="16"/> Pemesanan baru</a>
        </div>
    @endcan
</div>

<section class="panel">
    <div class="panel-body border-bottom">
        <form method="GET" class="filters">
            <div>
                <label class="form-label" for="q">Cari</label>
                <input type="search" name="q" id="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Nomor atau nama supplier">
            </div>
            <div>
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-outline-primary">Terapkan</button>
            @if (array_filter($filters))
                <a href="{{ route('purchases.index') }}" class="btn btn-link">Reset</a>
            @endif
        </form>
    </div>

    @if ($orders->isEmpty())
        <x-empty title="Belum ada pemesanan ke supplier."
                 :action="auth()->user()->can('pembelian.kelola') ? 'Buat pemesanan pertama' : null"
                 :href="route('purchases.create')">Catat barang yang akan dipesan ke supplier di sini.</x-empty>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Barang</th>
                    <th>Tanggal pesan</th>
                    <th>Perkiraan datang</th>
                    <th class="num">Total</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('purchases.show', $order->id) }}" class="row-link">{{ $order->supplier->name }}</a>
                            <span class="cell-sub">{{ $order->number }}</span>
                        </td>
                        <td class="small">{{ $order->items->map(fn ($i) => $i->material->name.' '.qty($i->quantity).' '.$i->material->unit)->implode(', ') }}</td>
                        <td class="text-nowrap">{{ tanggal($order->order_date) }}</td>
                        <td class="text-nowrap">{{ $order->status->value === 'received' ? 'Diterima '.tanggal($order->received_date) : tanggal($order->expected_date) }}</td>
                        <td class="num">{{ rupiah($order->total) }}</td>
                        <td><x-status :status="$order->status"/></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="panel-foot">{{ $orders->links() }}</div>
        @endif
    @endif
</section>
@endsection
