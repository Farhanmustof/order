@extends('layouts.app')
@section('title', $plan ? 'Ubah '.$plan->number : 'Rencana produksi baru')

@php
    $selected = collect(old('pre_order_ids', $preselected))->map(fn ($v) => (int) $v)->all();
    $extras = old('items', $plan
        ? $plan->items->whereNull('pre_order_id')->map(fn ($i) => ['product_id' => $i->product_id, 'quantity' => $i->quantity + 0])->values()->all()
        : []);
@endphp

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('production.index') }}">Rencana produksi</a>@if ($plan) / <a href="{{ route('production.show', $plan->id) }}">{{ $plan->number }}</a>@endif</div>
        <h1>{{ $plan ? 'Ubah rencana produksi' : 'Rencana produksi baru' }}</h1>
        <p class="sub">Pilih pre-order yang akan dibuat, lalu tambahkan produksi lain bila perlu. Kecukupan bahan dicek setelah disimpan.</p>
    </div>
</div>

<form method="POST" action="{{ $plan ? route('production.update', $plan->id) : route('production.store') }}">
    @csrf
    @if ($plan) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="panel">
                <div class="panel-head"><h2>Pre-order yang dikonfirmasi</h2><span class="hint">{{ $readyOrders->count() }} siap diproduksi</span></div>
                @if ($readyOrders->isEmpty())
                    <x-empty title="Tidak ada pre-order yang menunggu produksi.">
                        Hanya pre-order berstatus Dikonfirmasi yang belum masuk rencana lain yang tampil di sini.
                    </x-empty>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th style="width:2.5rem"></th><th>Pelanggan</th><th>Produk</th><th>Jatuh tempo</th></tr></thead>
                            <tbody>
                            @foreach ($readyOrders as $order)
                                <tr>
                                    <td><input class="form-check-input" type="checkbox" name="pre_order_ids[]" value="{{ $order->id }}" id="po{{ $order->id }}" @checked(in_array($order->id, $selected, true))></td>
                                    <td><label for="po{{ $order->id }}" class="fw-semibold">{{ $order->customer_name }}</label><span class="cell-sub">{{ $order->number }}</span></td>
                                    <td class="small">{{ $order->items->map(fn ($i) => $i->product->name.' '.qty($i->quantity))->implode(', ') }}</td>
                                    <td class="text-nowrap {{ $order->due_date->lt(today()) ? 'text-danger' : '' }}">{{ tanggal($order->due_date) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Produksi tambahan</h2><span class="hint">Misalnya untuk stok etalase atau titipan toko</span></div>
                <div class="panel-body lines" data-lines data-next="{{ count($extras) }}">
                    <div class="line-head grid-plan"><span>Produk</span><span>Jumlah</span><span></span></div>
                    <div data-lines-list>
                        @foreach ($extras as $i => $item)
                            @include('production._line', ['i' => $i, 'item' => $item])
                        @endforeach
                    </div>
                    <template>@include('production._line', ['i' => '__i__', 'item' => ['product_id' => '', 'quantity' => '']])</template>
                    <button type="button" class="btn btn-light btn-sm mt-2" data-add-line><x-icon name="plus" width="14" height="14"/> Tambah produk</button>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="panel">
                <div class="panel-head"><h2>Jadwal</h2></div>
                <div class="panel-body">
                    <div class="mb-3">
                        <label class="form-label req" for="plan_date">Tanggal produksi</label>
                        <input type="date" name="plan_date" id="plan_date" class="form-control" required
                               value="{{ old('plan_date', $plan?->plan_date->toDateString() ?? today()->addDay()->toDateString()) }}">
                    </div>
                    <div>
                        <label class="form-label" for="notes">Catatan untuk tim dapur</label>
                        <textarea name="notes" id="notes" rows="4" class="form-control">{{ old('notes', $plan?->notes) }}</textarea>
                    </div>
                </div>
                <div class="panel-foot d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">{{ $plan ? 'Simpan perubahan' : 'Simpan rencana' }}</button>
                    <a href="{{ $plan ? route('production.show', $plan->id) : route('production.index') }}" class="btn btn-light">Batal</a>
                </div>
            </section>
        </div>
    </div>
</form>
@endsection
