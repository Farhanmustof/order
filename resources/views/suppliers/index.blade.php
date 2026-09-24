@extends('layouts.app')
@section('title', 'Supplier')

@section('content')
<div class="page-head">
    <div>
        <h1>Supplier</h1>
        <p class="sub">Pemasok bahan baku. Dipilih saat mencatat stok masuk.</p>
    </div>
    @can('manage-master-data')
        <div class="actions"><a href="{{ route('suppliers.create') }}" class="btn btn-primary"><x-icon name="plus" width="16" height="16"/> Tambah supplier</a></div>
    @endcan
</div>

<section class="panel">
    <div class="panel-body border-bottom">
        <form method="GET" class="filters">
            <div>
                <label class="form-label" for="q">Cari supplier</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" class="form-control">
            </div>
            <button class="btn btn-outline-primary">Cari</button>
        </form>
    </div>
    @if ($suppliers->isEmpty())
        <x-empty title="Belum ada supplier."
                 :action="auth()->user()->can('manage-master-data') ? 'Tambah supplier' : null"
                 :href="route('suppliers.create')"></x-empty>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Nama</th><th>Telepon</th><th>Alamat</th><th class="num">Batch diterima</th><th></th></tr></thead>
                <tbody>
                @foreach ($suppliers as $s)
                    <tr>
                        <td class="fw-semibold">{{ $s->name }} @if ($s->notes)<span class="cell-sub">{{ $s->notes }}</span>@endif</td>
                        <td class="text-nowrap">{{ $s->phone ?: '-' }}</td>
                        <td class="small">{{ $s->address ?: '-' }}</td>
                        <td class="num">{{ $s->batches_count }}</td>
                        <td class="text-end text-nowrap">
                            @can('manage-master-data')
                                <a href="{{ route('suppliers.edit', $s->id) }}" class="small me-2">Ubah</a>
                                <x-delete-button :action="route('suppliers.destroy', $s->id)" confirm="Hapus supplier {{ $s->name }}?" class="small"/>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if ($suppliers->hasPages()) <div class="panel-foot">{{ $suppliers->links() }}</div> @endif
    @endif
</section>
@endsection
