@extends('layouts.app')
@section('title', $order ? 'Ubah '.$order->number : 'Pemesanan baru')

@php
    $items = old('items', $order
        ? $order->items->map(fn ($i) => ['raw_material_id' => $i->raw_material_id, 'quantity' => $i->quantity + 0, 'unit_cost' => $i->unit_cost + 0])->all()
        : [['raw_material_id' => '', 'quantity' => '', 'unit_cost' => '']]);
@endphp

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('purchases.index') }}">Pemesanan ke supplier</a>@if ($order) / <a href="{{ route('purchases.show', $order->id) }}">{{ $order->number }}</a>@endif</div>
        <h1>{{ $order ? 'Ubah pemesanan' : 'Pemesanan baru' }}</h1>
        @unless ($order)<p class="sub">Disimpan sebagai Draft dulu. Setelah dikirim ke supplier, tandai "Sudah dipesan".</p>@endunless
    </div>
</div>

@if ($suppliers->isEmpty())
    <div class="alert alert-warning">Belum ada data supplier. Tambahkan supplier dulu di menu <a href="{{ route('suppliers.index') }}">Supplier</a>.</div>
@endif

<form method="POST" action="{{ $order ? route('purchases.update', $order->id) : route('purchases.store') }}" data-po-form>
    @csrf
    @if ($order) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="panel">
                <div class="panel-head"><h2>Barang yang dipesan</h2><span class="hint">Harga beli boleh dikosongkan bila belum tahu.</span></div>
                <div class="panel-body lines" data-lines data-min-one data-next="{{ count($items) }}">
                    <div class="line-head grid-po"><span>Bahan</span><span>Jumlah</span><span>Harga beli satuan</span><span class="text-end">Subtotal</span><span></span></div>
                    <div data-lines-list>
                        @foreach ($items as $i => $item)
                            @include('purchases._line', ['i' => $i, 'item' => $item])
                        @endforeach
                    </div>
                    <template>@include('purchases._line', ['i' => '__i__', 'item' => ['raw_material_id' => '', 'quantity' => '', 'unit_cost' => '']])</template>
                    <button type="button" class="btn btn-light btn-sm mt-2" data-add-line><x-icon name="plus" width="14" height="14"/> Tambah barang</button>
                </div>
                <div class="panel-foot">
                    <div class="money-summary tabular">
                        <span class="total">Total</span><span class="total text-end" data-total>Rp 0</span>
                        <span data-remaining hidden></span>{{-- dibutuhkan skrip hitung total (app.js) --}}
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="panel">
                <div class="panel-head"><h2>Supplier &amp; jadwal</h2></div>
                <div class="panel-body">
                    <div class="mb-3">
                        <label class="form-label req" for="supplier_id">Supplier</label>
                        <select name="supplier_id" id="supplier_id" class="form-select" required>
                            <option value="">Pilih supplier…</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $order?->supplier_id) === (string) $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label req" for="order_date">Tanggal pesan</label>
                            <input type="date" name="order_date" id="order_date" class="form-control" required
                                   value="{{ old('order_date', $order?->order_date->toDateString() ?? today()->toDateString()) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="expected_date">Perkiraan datang</label>
                            <input type="date" name="expected_date" id="expected_date" class="form-control"
                                   value="{{ old('expected_date', $order?->expected_date?->toDateString()) }}">
                        </div>
                    </div>
                    <div>
                        <label class="form-label" for="notes">Catatan</label>
                        <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Contoh: kirim pagi, minta faktur">{{ old('notes', $order?->notes) }}</textarea>
                    </div>
                </div>
                <div class="panel-foot d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">{{ $order ? 'Simpan perubahan' : 'Simpan pemesanan' }}</button>
                    <a href="{{ $order ? route('purchases.show', $order->id) : route('purchases.index') }}" class="btn btn-light">Batal</a>
                </div>
            </section>
        </div>
    </div>
</form>
@endsection
