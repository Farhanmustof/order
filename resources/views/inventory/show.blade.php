@extends('layouts.app')
@section('title', $material->name)

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('inventory.index') }}">Stok bahan baku</a></div>
        <h1>{{ $material->name }}</h1>
        <p class="sub">{{ $material->code }} · satuan {{ $material->unit }}{{ $material->notes ? ' · '.$material->notes : '' }}</p>
    </div>
    <div class="actions">
        @can('manage-master-data')
            <a href="{{ route('materials.edit', $material->id) }}" class="btn btn-light">Ubah data bahan</a>
        @endcan
        @can('manage-transactions')
            <a href="{{ route('stock-out.create', ['bahan' => $material->id]) }}" class="btn btn-light">Catat stok keluar</a>
            <a href="{{ route('stock-in.create', ['bahan' => $material->id]) }}" class="btn btn-primary"><x-icon name="plus" width="16" height="16"/> Catat stok masuk</a>
        @endcan
    </div>
</div>

<section class="panel mb-4">
    <div class="panel-body">
        <div class="row g-4 align-items-center">
            <div class="col-md-3">
                <div class="text-muted small">Layak pakai</div>
                <div class="stock-figure fs-3">{{ qty($material->usable_stock) }} <small>{{ $material->unit }}</small></div>
            </div>
            <div class="col-md-6">
                <x-gauge :material="$material"/>
                <div class="gauge-legend mt-2">
                    <span>Minimum {{ qty($material->minimum_stock) }} {{ $material->unit }}</span>
                    @if ($material->expired_stock > 0)<span class="text-warn">{{ qty($material->expired_stock) }} {{ $material->unit }} kedaluwarsa, sebaiknya dicatat sebagai stok keluar</span>@endif
                </div>
            </div>
            <div class="col-md-3 text-md-end">
                @if ($material->is_low) <span class="pill pill-danger">Perlu dibeli</span> @else <span class="pill pill-ok">Stok aman</span> @endif
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><h2>Batch stok</h2><span class="hint">Urutan pemakaian otomatis: kedaluwarsa terdekat dulu (FEFO)</span></div>
    @if ($batches->isEmpty())
        <x-empty title="Belum ada stok masuk untuk bahan ini."
                 :action="auth()->user()->can('manage-transactions') ? 'Catat stok masuk' : null"
                 :href="route('stock-in.create', ['bahan' => $material->id])"></x-empty>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Batch</th><th>Masuk</th><th>Kedaluwarsa</th><th class="num">Awal</th><th class="num">Sisa</th><th class="num">Harga beli</th><th></th></tr></thead>
                <tbody>
                @foreach ($batches as $b)
                    @php $d = $b->daysToExpiry(); @endphp
                    <tr @class(['text-muted' => $b->quantity_remaining <= 0])>
                        <td>
                            <span class="fw-semibold">{{ $b->batch_code ?: 'Batch #'.$b->id }}</span>
                            <span class="cell-sub">{{ $b->supplier?->name ?? 'Tanpa supplier' }}</span>
                        </td>
                        <td class="text-nowrap">{{ tanggal($b->received_date) }}</td>
                        <td class="text-nowrap">
                            {{ tanggal($b->expiry_date) }}
                            @if ($b->quantity_remaining > 0 && $d !== null)
                                @if ($d < 0) <span class="pill pill-danger">kedaluwarsa</span>
                                @elseif ($d <= 14) <span class="pill pill-warn">{{ $d === 0 ? 'hari ini' : $d.' hari lagi' }}</span> @endif
                            @endif
                        </td>
                        <td class="num">{{ qty($b->quantity_initial) }}</td>
                        <td class="num fw-semibold">{{ $b->quantity_remaining > 0 ? qty($b->quantity_remaining) : 'habis' }}</td>
                        <td class="num">{{ rupiah($b->unit_cost) }}</td>
                        <td class="text-end text-nowrap">
                            @can('manage-transactions')
                                <a href="{{ route('stock-in.edit', $b->id) }}" class="small me-2">Ubah</a>
                                @if ($b->quantity_remaining > 0 && $d !== null && $d < 0)
                                    <a href="{{ route('stock-out.create', ['bahan' => $material->id, 'batch' => $b->id]) }}" class="small me-2">Buang</a>
                                @endif
                                @if ($b->isUntouched())
                                    <x-delete-button :action="route('stock-in.destroy', $b->id)" confirm="Hapus catatan stok masuk ini?" class="small"/>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<div class="row g-4 mt-0">
    <div class="col-lg-8">
        <section class="panel">
            <div class="panel-head"><h2>Pergerakan terakhir</h2><a href="{{ route('inventory.movements', ['material_id' => $material->id]) }}" class="small">Lihat semua</a></div>
            @include('inventory._movements', ['movements' => $movements, 'showMaterial' => false])
        </section>
    </div>
    <div class="col-lg-4">
        <section class="panel">
            <div class="panel-head"><h2>Dipakai di resep</h2></div>
            @if ($usedIn->isEmpty())
                <x-empty title="Belum dipakai di resep produk mana pun."></x-empty>
            @else
                <ul class="due-list">
                    @foreach ($usedIn as $product)
                        <li class="justify-content-between">
                            <span>{{ $product->name }}</span>
                            <span class="tabular small text-muted">{{ qty($product->materials->first()->pivot->quantity, 4) }} {{ $material->unit }} / {{ $product->unit }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
        @can('manage-master-data')
            <div class="mt-3 text-end">
                <x-delete-button :action="route('materials.destroy', $material->id)" label="Hapus bahan baku ini"
                                 confirm="Hapus bahan {{ $material->name }}? Hanya bisa bila stok kosong dan tidak dipakai resep."/>
            </div>
        @endcan
    </div>
</div>
@endsection
