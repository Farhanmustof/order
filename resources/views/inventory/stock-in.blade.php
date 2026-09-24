@extends('layouts.app')
@section('title', $batch ? 'Ubah stok masuk' : 'Catat stok masuk')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('inventory.index') }}">Stok bahan baku</a>@if ($batch) / <a href="{{ route('inventory.show', $batch->raw_material_id) }}">{{ $batch->material->name }}</a>@endif</div>
        <h1>{{ $batch ? 'Ubah stok masuk' : 'Catat stok masuk' }}</h1>
        <p class="sub">Setiap penerimaan dicatat sebagai satu batch agar tanggal kedaluwarsanya bisa dilacak.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-xl-7">
        <form method="POST" action="{{ $batch ? route('stock-in.update', $batch->id) : route('stock-in.store') }}" class="panel">
            @csrf
            @if ($batch) @method('PUT') @endif
            <div class="panel-body">
                @if ($batch && ! $batch->isUntouched())
                    <div class="alert alert-info">Batch ini sudah terpakai {{ qty($batch->quantity_initial - $batch->quantity_remaining) }} {{ $batch->material->unit }}, jadi jumlahnya tidak bisa diubah lagi. Data lain tetap bisa diperbaiki.</div>
                @endif
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label req" for="raw_material_id">Bahan baku</label>
                        <select name="raw_material_id" id="raw_material_id" class="form-select" required @disabled($batch) data-unit-source>
                            <option value="">Pilih bahan…</option>
                            @foreach ($materials as $m)
                                <option value="{{ $m->id }}" data-unit="{{ $m->unit }}" @selected(old('raw_material_id', $selectedMaterial) == $m->id)>{{ $m->name }} ({{ $m->unit }})</option>
                            @endforeach
                        </select>
                        @if ($batch)<input type="hidden" name="raw_material_id" value="{{ $batch->raw_material_id }}">@endif
                        @can('manage-master-data')
                            <div class="form-text">Bahan belum ada? <a href="{{ route('materials.create') }}">Tambah bahan baku</a>.</div>
                        @endcan
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="batch_code">Kode batch / no. nota</label>
                        <input type="text" name="batch_code" id="batch_code" class="form-control" maxlength="50" value="{{ old('batch_code', $batch?->batch_code) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label req" for="quantity">Jumlah</label>
                        <div class="input-group">
                            <input type="number" name="quantity" id="quantity" class="form-control" min="0.001" step="any" required
                                   value="{{ old('quantity', $batch ? $batch->quantity_initial + 0 : '') }}" @readonly($batch && ! $batch->isUntouched())>
                            <span class="input-group-text" data-unit-label>{{ $batch?->material->unit ?? $materials->firstWhere('id', old('raw_material_id', $selectedMaterial))?->unit }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="unit_cost">Harga beli per satuan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="unit_cost" id="unit_cost" class="form-control" min="0" step="any" value="{{ old('unit_cost', $batch ? $batch->unit_cost + 0 : '') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label req" for="received_date">Tanggal masuk</label>
                        <input type="date" name="received_date" id="received_date" class="form-control" required max="{{ today()->toDateString() }}"
                               value="{{ old('received_date', $batch?->received_date->toDateString() ?? today()->toDateString()) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="expiry_date">Tanggal kedaluwarsa</label>
                        <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="{{ old('expiry_date', $batch?->expiry_date?->toDateString()) }}">
                        <div class="form-text">Kosongkan bila tidak ada tanggal kedaluwarsa.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="supplier_id">Supplier</label>
                        <select name="supplier_id" id="supplier_id" class="form-select">
                            <option value="">Tanpa supplier</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}" @selected(old('supplier_id', $batch?->supplier_id) == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="notes">Catatan</label>
                        <input type="text" name="notes" id="notes" class="form-control" maxlength="1000" value="{{ old('notes', $batch?->notes) }}">
                    </div>
                </div>
            </div>
            <div class="panel-foot d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ $batch ? 'Simpan perubahan' : 'Simpan stok masuk' }}</button>
                <a href="{{ $batch ? route('inventory.show', $batch->raw_material_id) : route('inventory.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
