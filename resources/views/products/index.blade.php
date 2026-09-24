@extends('layouts.app')
@section('title', 'Produk & resep')

@section('content')
<div class="page-head">
    <div>
        <h1>Produk &amp; resep</h1>
        <p class="sub">Resep menentukan berapa bahan baku yang dipotong dari stok untuk setiap 1 satuan produk.</p>
    </div>
    @can('manage-master-data')
        <div class="actions"><a href="{{ route('products.create') }}" class="btn btn-primary"><x-icon name="plus" width="16" height="16"/> Tambah produk</a></div>
    @endcan
</div>

<section class="panel">
    <div class="panel-body border-bottom">
        <form method="GET" class="filters">
            <div>
                <label class="form-label" for="q">Cari produk</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" class="form-control" placeholder="Nama atau kode">
            </div>
            <button class="btn btn-outline-primary">Cari</button>
        </form>
    </div>
    @if ($products->isEmpty())
        <x-empty title="Belum ada produk."
                 :action="auth()->user()->can('manage-master-data') ? 'Tambah produk pertama' : null"
                 :href="route('products.create')">Tambahkan produk beserta resepnya agar kebutuhan bahan bisa dihitung.</x-empty>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Produk</th><th class="num">Harga</th><th>Resep per 1 satuan</th><th></th></tr></thead>
                <tbody>
                @foreach ($products as $p)
                    <tr>
                        <td class="text-nowrap"><span class="fw-semibold">{{ $p->name }}</span><span class="cell-sub">{{ $p->code }} · per {{ $p->unit }}</span></td>
                        <td class="num">{{ rupiah($p->price) }}</td>
                        <td class="small">
                            @if ($p->materials->isEmpty())
                                <span class="pill pill-warn">belum ada resep</span>
                            @else
                                {{ $p->materials->map(fn ($m) => $m->name.' '.qty($m->pivot->quantity, 4).' '.$m->unit)->implode(' · ') }}
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @can('manage-master-data')
                                <a href="{{ route('products.edit', $p->id) }}" class="small me-2">Ubah</a>
                                <x-delete-button :action="route('products.destroy', $p->id)" confirm="Hapus produk {{ $p->name }}?" class="small"/>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
