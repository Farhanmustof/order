@extends('layouts.app')
@section('title', 'Stok bahan baku')

@section('content')
<div class="page-head">
    <div>
        <h1>Stok bahan baku</h1>
        <p class="sub">Garis hitam pada meteran = stok minimum. Bagian bergaris kuning = stok kedaluwarsa yang tidak dipakai untuk produksi.</p>
    </div>
    <div class="actions">
        <a href="{{ route('inventory.export') }}" class="btn btn-light"><x-icon name="download" width="16" height="16"/> Ekspor ke Excel</a>
        @can('manage-master-data')
            <a href="{{ route('materials.create') }}" class="btn btn-light">Tambah bahan</a>
        @endcan
        @can('manage-transactions')
            <a href="{{ route('stock-out.create') }}" class="btn btn-light">Catat stok keluar</a>
            <a href="{{ route('stock-in.create') }}" class="btn btn-primary"><x-icon name="plus" width="16" height="16"/> Catat stok masuk</a>
        @endcan
    </div>
</div>

<section class="panel">
    <div class="panel-body border-bottom d-flex flex-wrap justify-content-between gap-3 align-items-end">
        <form method="GET" class="filters">
            <input type="hidden" name="filter" value="{{ $filters['filter'] ?? '' }}">
            <div>
                <label class="form-label" for="q">Cari bahan</label>
                <input type="search" name="q" id="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Nama atau kode">
            </div>
            <button class="btn btn-outline-primary">Cari</button>
        </form>
        <ul class="nav nav-tabs border-0">
            @foreach (['' => 'Semua', 'low' => 'Perlu dibeli', 'expiring' => 'Kedaluwarsa ≤ '.$warningDays.' hari'] as $key => $label)
                <li class="nav-item">
                    <a class="nav-link {{ ($filters['filter'] ?? '') === $key ? 'active' : '' }}" href="{{ route('inventory.index', array_filter(['filter' => $key, 'q' => $filters['q'] ?? null])) }}">{{ $label }}</a>
                </li>
            @endforeach
        </ul>
    </div>

    @if ($materials->isEmpty())
        <x-empty title="Tidak ada bahan baku yang cocok."
                 :action="auth()->user()->can('manage-master-data') ? 'Tambah bahan baku' : null"
                 :href="route('materials.create')">Ubah pencarian atau filter di atas.</x-empty>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Bahan</th>
                    <th class="num">Layak pakai</th>
                    <th style="min-width: 200px">Meteran stok</th>
                    <th>Kedaluwarsa terdekat</th>
                    <th class="text-end">Keterangan</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($materials as $m)
                    <tr>
                        <td>
                            <a href="{{ route('inventory.show', $m->id) }}" class="row-link">{{ $m->name }}</a>
                            <span class="cell-sub">{{ $m->code }} · min {{ qty($m->minimum_stock) }} {{ $m->unit }}</span>
                        </td>
                        <td class="num"><span class="stock-figure">{{ qty($m->usable_stock) }} <small>{{ $m->unit }}</small></span></td>
                        <td><x-gauge :material="$m"/></td>
                        <td class="text-nowrap">
                            @if ($m->next_expiry)
                                {{ tanggal($m->next_expiry) }}
                                <span class="cell-sub {{ $m->days_to_expiry <= $warningDays ? 'text-warn' : '' }}">{{ $m->days_to_expiry === 0 ? 'hari ini' : $m->days_to_expiry.' hari lagi' }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($m->is_low) <span class="pill pill-danger">perlu dibeli</span> @endif
                            @if ($m->expired_stock > 0) <span class="pill pill-warn">{{ qty($m->expired_stock) }} {{ $m->unit }} kedaluwarsa</span> @endif
                            @if (! $m->is_low && $m->expired_stock <= 0) <span class="pill pill-ok">aman</span> @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
