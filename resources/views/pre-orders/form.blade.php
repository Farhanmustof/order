@extends('layouts.app')
@section('title', $order ? 'Ubah '.$order->number : 'Pre-order baru')

@php
    $items = old('items', $order
        ? $order->items->map(fn ($i) => ['product_id' => $i->product_id, 'quantity' => $i->quantity + 0, 'unit_price' => $i->unit_price + 0])->all()
        : [['product_id' => '', 'quantity' => '', 'unit_price' => '']]);
@endphp

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('pre-orders.index') }}">Pre-order</a>@if ($order) / <a href="{{ route('pre-orders.show', $order->id) }}">{{ $order->number }}</a>@endif</div>
        <h1>{{ $order ? 'Ubah pre-order' : 'Pre-order baru' }}</h1>
        @unless ($order)<p class="sub">Pesanan disimpan sebagai Draft dulu. Konfirmasi setelah pelanggan memastikan pesanannya.</p>@endunless
    </div>
</div>

<form method="POST" action="{{ $order ? route('pre-orders.update', $order->id) : route('pre-orders.store') }}" data-po-form>
    @csrf
    @if ($order) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="panel">
                <div class="panel-head"><h2>Produk</h2><span class="hint">Harga terisi otomatis dari data produk, bisa diubah untuk harga khusus.</span></div>
                <div class="panel-body lines" data-lines data-min-one data-next="{{ count($items) }}">
                    <div class="line-head grid-po"><span>Produk</span><span>Jumlah</span><span>Harga satuan</span><span class="text-end">Subtotal</span><span></span></div>
                    <div data-lines-list>
                        @foreach ($items as $i => $item)
                            @include('pre-orders._line', ['i' => $i, 'item' => $item])
                        @endforeach
                    </div>
                    <template>@include('pre-orders._line', ['i' => '__i__', 'item' => ['product_id' => '', 'quantity' => '', 'unit_price' => '']])</template>
                    <button type="button" class="btn btn-light btn-sm mt-2" data-add-line><x-icon name="plus" width="14" height="14"/> Tambah produk</button>
                </div>
                <div class="panel-foot">
                    <div class="money-summary tabular">
                        <span>Total</span><span class="text-end" data-total>Rp 0</span>
                        <span class="total">Sisa setelah DP</span><span class="total text-end" data-remaining>Rp 0</span>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="panel">
                <div class="panel-head"><h2>Pelanggan &amp; jadwal</h2></div>
                <div class="panel-body">
                    <div class="mb-3">
                        <label class="form-label req" for="customer_name">Nama pelanggan</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control" required maxlength="255"
                               value="{{ old('customer_name', $order?->customer_name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="customer_phone">Telepon / WhatsApp</label>
                        <input type="tel" name="customer_phone" id="customer_phone" class="form-control" maxlength="30"
                               value="{{ old('customer_phone', $order?->customer_phone) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="customer_address">Alamat kirim</label>
                        <textarea name="customer_address" id="customer_address" rows="2" class="form-control">{{ old('customer_address', $order?->customer_address) }}</textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label req" for="order_date">Tanggal pesan</label>
                            <input type="date" name="order_date" id="order_date" class="form-control" required
                                   value="{{ old('order_date', $order?->order_date->toDateString() ?? today()->toDateString()) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label req" for="due_date">Jatuh tempo</label>
                            <input type="date" name="due_date" id="due_date" class="form-control" required
                                   value="{{ old('due_date', $order?->due_date->toDateString() ?? today()->addDays(3)->toDateString()) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="down_payment">DP (uang muka)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="down_payment" id="down_payment" class="form-control" min="0" step="1"
                                   value="{{ old('down_payment', $order ? $order->down_payment + 0 : 0) }}">
                        </div>
                    </div>
                    <div>
                        <label class="form-label" for="notes">Catatan</label>
                        <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Contoh: tulisan di kue, kemasan khusus">{{ old('notes', $order?->notes) }}</textarea>
                    </div>
                </div>
                <div class="panel-foot d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">{{ $order ? 'Simpan perubahan' : 'Simpan pre-order' }}</button>
                    <a href="{{ $order ? route('pre-orders.show', $order->id) : route('pre-orders.index') }}" class="btn btn-light">Batal</a>
                </div>
            </section>
        </div>
    </div>
</form>
@endsection
