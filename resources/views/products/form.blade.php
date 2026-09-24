@extends('layouts.app')
@section('title', $product ? 'Ubah '.$product->name : 'Tambah produk')

@php
    $recipe = old('recipe', $product
        ? $product->materials->map(fn ($m) => ['raw_material_id' => $m->id, 'quantity' => $m->pivot->quantity + 0])->all()
        : [['raw_material_id' => '', 'quantity' => '']]);
@endphp

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('products.index') }}">Produk &amp; resep</a></div>
        <h1>{{ $product ? 'Ubah produk' : 'Tambah produk' }}</h1>
    </div>
</div>

<form method="POST" action="{{ $product ? route('products.update', $product->id) : route('products.store') }}">
    @csrf
    @if ($product) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-5">
            <section class="panel">
                <div class="panel-head"><h2>Data produk</h2></div>
                <div class="panel-body">
                    <div class="row g-3">
                        <div class="col-4">
                            <label class="form-label req" for="code">Kode</label>
                            <input type="text" name="code" id="code" class="form-control text-uppercase" required maxlength="30" value="{{ old('code', $product?->code) }}" placeholder="RTC">
                        </div>
                        <div class="col-8">
                            <label class="form-label req" for="name">Nama produk</label>
                            <input type="text" name="name" id="name" class="form-control" required maxlength="255" value="{{ old('name', $product?->name) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label req" for="unit">Satuan jual</label>
                            <input type="text" name="unit" id="unit" class="form-control" required maxlength="20" list="unit-options" value="{{ old('unit', $product?->unit ?? 'pcs') }}">
                            <datalist id="unit-options">@foreach ($units as $u)<option value="{{ $u }}">@endforeach</datalist>
                        </div>
                        <div class="col-6">
                            <label class="form-label req" for="price">Harga jual</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" id="price" class="form-control" min="0" step="1" required value="{{ old('price', $product ? $product->price + 0 : '') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">Deskripsi</label>
                            <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $product?->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-7">
            <section class="panel">
                <div class="panel-head"><h2>Resep</h2><span class="hint">Takaran bahan untuk membuat 1 satuan produk</span></div>
                <div class="panel-body lines" data-lines data-next="{{ count($recipe) }}">
                    <div class="line-head grid-recipe"><span>Bahan baku</span><span>Takaran</span><span></span></div>
                    <div data-lines-list>
                        @foreach ($recipe as $i => $line)
                            @include('products._line', ['i' => $i, 'line' => $line])
                        @endforeach
                    </div>
                    <template>@include('products._line', ['i' => '__i__', 'line' => ['raw_material_id' => '', 'quantity' => '']])</template>
                    <button type="button" class="btn btn-light btn-sm mt-2" data-add-line><x-icon name="plus" width="14" height="14"/> Tambah bahan</button>
                    <p class="form-text mb-0 mt-3">Contoh: 1 loyang brownies butuh 0,15 kg tepung → isi 0.15 dengan bahan Tepung terigu (kg). Gunakan titik untuk desimal.</p>
                </div>
                <div class="panel-foot d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ $product ? 'Simpan perubahan' : 'Simpan produk' }}</button>
                    <a href="{{ route('products.index') }}" class="btn btn-light">Batal</a>
                </div>
            </section>
        </div>
    </div>
</form>
@endsection
