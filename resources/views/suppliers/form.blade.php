@extends('layouts.app')
@section('title', $supplier ? 'Ubah supplier' : 'Tambah supplier')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('suppliers.index') }}">Supplier</a></div>
        <h1>{{ $supplier ? 'Ubah supplier' : 'Tambah supplier' }}</h1>
    </div>
</div>
<div class="row">
    <div class="col-lg-7 col-xl-6">
        <form method="POST" action="{{ $supplier ? route('suppliers.update', $supplier->id) : route('suppliers.store') }}" class="panel">
            @csrf
            @if ($supplier) @method('PUT') @endif
            <div class="panel-body">
                <div class="mb-3">
                    <label class="form-label req" for="name">Nama supplier</label>
                    <input type="text" name="name" id="name" class="form-control" required maxlength="255" value="{{ old('name', $supplier?->name) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">Telepon / WhatsApp</label>
                    <input type="tel" name="phone" id="phone" class="form-control" maxlength="30" value="{{ old('phone', $supplier?->phone) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address">Alamat</label>
                    <textarea name="address" id="address" rows="2" class="form-control">{{ old('address', $supplier?->address) }}</textarea>
                </div>
                <div>
                    <label class="form-label" for="notes">Catatan</label>
                    <input type="text" name="notes" id="notes" class="form-control" maxlength="1000" value="{{ old('notes', $supplier?->notes) }}" placeholder="Contoh: kirim setiap Senin, bayar tempo 14 hari">
                </div>
            </div>
            <div class="panel-foot d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ $supplier ? 'Simpan perubahan' : 'Simpan supplier' }}</button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
