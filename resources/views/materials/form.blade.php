@extends('layouts.app')
@section('title', $material ? 'Ubah '.$material->name : 'Tambah bahan baku')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('inventory.index') }}">Stok bahan baku</a>@if ($material) / <a href="{{ route('inventory.show', $material->id) }}">{{ $material->name }}</a>@endif</div>
        <h1>{{ $material ? 'Ubah bahan baku' : 'Tambah bahan baku' }}</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 col-xl-6">
        <form method="POST" action="{{ $material ? route('materials.update', $material->id) : route('materials.store') }}" class="panel">
            @csrf
            @if ($material) @method('PUT') @endif
            <div class="panel-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label req" for="code">Kode</label>
                        <input type="text" name="code" id="code" class="form-control text-uppercase" required maxlength="30" value="{{ old('code', $material?->code) }}" placeholder="TPG">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label req" for="name">Nama bahan</label>
                        <input type="text" name="name" id="name" class="form-control" required maxlength="255" value="{{ old('name', $material?->name) }}" placeholder="Tepung terigu protein tinggi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label req" for="unit">Satuan stok</label>
                        <input type="text" name="unit" id="unit" class="form-control" required maxlength="20" list="unit-options" value="{{ old('unit', $material?->unit) }}">
                        <datalist id="unit-options">@foreach ($units as $u)<option value="{{ $u }}">@endforeach</datalist>
                        <div class="form-text">Pakai satuan yang sama dengan takaran di resep.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label req" for="minimum_stock">Stok minimum</label>
                        <input type="number" name="minimum_stock" id="minimum_stock" class="form-control" min="0" step="any" required value="{{ old('minimum_stock', $material ? $material->minimum_stock + 0 : 0) }}">
                        <div class="form-text">Di bawah angka ini, bahan ditandai perlu dibeli.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="notes">Catatan</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes', $material?->notes) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="panel-foot d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ $material ? 'Simpan perubahan' : 'Simpan bahan' }}</button>
                <a href="{{ $material ? route('inventory.show', $material->id) : route('inventory.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
