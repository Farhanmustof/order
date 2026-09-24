@extends('layouts.app')
@section('title', 'Pre-order')

@section('content')
<div class="page-head">
    <div>
        <h1>Pre-order</h1>
        <p class="sub">Pesanan pelanggan, diurutkan dari jatuh tempo terdekat. Pesanan yang sudah dikirim atau dibatalkan ada di bagian bawah.</p>
    </div>
    <div class="actions">
        <a href="{{ route('pre-orders.export', request()->query()) }}" class="btn btn-light"><x-icon name="download" width="16" height="16"/> Ekspor ke Excel</a>
        @can('manage-transactions')
            <a href="{{ route('pre-orders.create') }}" class="btn btn-primary"><x-icon name="plus" width="16" height="16"/> Pre-order baru</a>
        @endcan
    </div>
</div>

<section class="panel">
    <div class="panel-body border-bottom">
        <form method="GET" class="filters">
            <div>
                <label class="form-label" for="q">Cari</label>
                <input type="search" name="q" id="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Nomor, nama, atau telepon">
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
            <div>
                <label class="form-label" for="from">Jatuh tempo dari</label>
                <input type="date" name="from" id="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
            </div>
            <div>
                <label class="form-label" for="to">sampai</label>
                <input type="date" name="to" id="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
            </div>
            <button class="btn btn-outline-primary">Terapkan</button>
            @if (array_filter($filters))
                <a href="{{ route('pre-orders.index') }}" class="btn btn-link">Reset</a>
            @endif
        </form>
    </div>

    @if ($orders->isEmpty())
        <x-empty title="Belum ada pre-order yang cocok."
                 :action="auth()->user()->can('manage-transactions') ? 'Catat pre-order pertama' : null"
                 :href="route('pre-orders.create')">Ubah filter atau catat pesanan baru.</x-empty>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Produk</th>
                    <th>Jatuh tempo</th>
                    <th class="num">Total</th>
                    <th class="num">Sisa bayar</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($orders as $order)
                    @php $late = $order->due_date->lt(today()) && in_array($order->status, \App\Domain\PreOrder\PreOrderStatus::active(), true); @endphp
                    <tr>
                        <td>
                            <a href="{{ route('pre-orders.show', $order->id) }}" class="row-link">{{ $order->customer_name }}</a>
                            <span class="cell-sub">{{ $order->number }}{{ $order->customer_phone ? ' · '.$order->customer_phone : '' }}</span>
                        </td>
                        <td class="small">{{ $order->items->map(fn ($i) => $i->product->name.' '.qty($i->quantity))->implode(', ') }}</td>
                        <td class="text-nowrap {{ $late ? 'text-danger fw-semibold' : '' }}">
                            {{ tanggal($order->due_date) }}
                            @if ($late) <span class="cell-sub text-danger">terlambat</span> @endif
                        </td>
                        <td class="num">{{ rupiah($order->total) }}</td>
                        <td class="num">{{ $order->total - $order->down_payment > 0 ? rupiah($order->total - $order->down_payment) : 'Lunas' }}</td>
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
