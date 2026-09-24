@extends('layouts.app')
@section('title', 'Riwayat stok')

@section('content')
<div class="page-head">
    <div>
        <h1>Riwayat stok</h1>
        <p class="sub">Semua stok masuk dan keluar, termasuk pemakaian otomatis dari produksi.</p>
    </div>
    <div class="actions">
        <a href="{{ route('inventory.movements.export', request()->query()) }}" class="btn btn-light"><x-icon name="download" width="16" height="16"/> Ekspor ke Excel</a>
    </div>
</div>

<section class="panel">
    <div class="panel-body border-bottom">
        <form method="GET" class="filters">
            <div>
                <label class="form-label" for="material_id">Bahan</label>
                <select name="material_id" id="material_id" class="form-select">
                    <option value="">Semua bahan</option>
                    @foreach ($materials as $m)
                        <option value="{{ $m->id }}" @selected(($filters['material_id'] ?? '') == $m->id)>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="type">Jenis</label>
                <select name="type" id="type" class="form-select">
                    <option value="">Semua jenis</option>
                    @foreach ($types as $t)
                        <option value="{{ $t->value }}" @selected(($filters['type'] ?? '') === $t->value)>{{ $t->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="from">Dari</label>
                <input type="date" name="from" id="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
            </div>
            <div>
                <label class="form-label" for="to">sampai</label>
                <input type="date" name="to" id="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
            </div>
            <button class="btn btn-outline-primary">Terapkan</button>
            @if (array_filter($filters)) <a href="{{ route('inventory.movements') }}" class="btn btn-link">Reset</a> @endif
        </form>
    </div>
    @include('inventory._movements', ['movements' => $movements, 'showMaterial' => true])
</section>
@endsection
