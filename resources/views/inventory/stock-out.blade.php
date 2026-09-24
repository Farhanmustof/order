@extends('layouts.app')
@section('title', 'Catat stok keluar')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('inventory.index') }}">Stok bahan baku</a></div>
        <h1>Catat stok keluar</h1>
        <p class="sub">Untuk bahan yang rusak, kedaluwarsa, atau koreksi hasil hitung fisik. Pemakaian produksi tercatat otomatis, tidak perlu lewat sini.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-xl-7">
        <form method="POST" action="{{ route('stock-out.store') }}" class="panel" data-stock-out
              data-batches='@json($batches)' data-selected-batch="{{ old('stock_batch_id', $selectedBatch) }}">
            @csrf
            <div class="panel-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label req" for="raw_material_id">Bahan baku</label>
                        <select name="raw_material_id" id="raw_material_id" class="form-select" required>
                            <option value="">Pilih bahan…</option>
                            @foreach ($materials as $m)
                                <option value="{{ $m->id }}" data-unit="{{ $m->unit }}" @selected(old('raw_material_id', $selectedMaterial) == $m->id)>{{ $m->name }} ({{ $m->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label req" for="type">Jenis</label>
                        <select name="type" id="type" class="form-select" required>
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}" @selected(old('type', $selectedBatch ? 'waste' : '') === $t->value)>{{ $t->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label" for="stock_batch_id">Ambil dari batch</label>
                        <select name="stock_batch_id" id="stock_batch_id" class="form-select"></select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label req" for="quantity">Jumlah</label>
                        <div class="input-group">
                            <input type="number" name="quantity" id="quantity" class="form-control" min="0.001" step="any" required value="{{ old('quantity') }}">
                            <span class="input-group-text" data-unit-label></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label req" for="notes">Alasan</label>
                        <input type="text" name="notes" id="notes" class="form-control" required maxlength="1000" value="{{ old('notes') }}"
                               placeholder="Contoh: telur pecah saat pengiriman, hasil stock opname akhir bulan">
                    </div>
                </div>
            </div>
            <div class="panel-foot d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan stok keluar</button>
                <a href="{{ url()->previous() }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
