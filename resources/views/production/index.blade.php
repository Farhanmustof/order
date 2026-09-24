@extends('layouts.app')
@section('title', 'Rencana produksi')

@section('content')
<div class="page-head">
    <div>
        <h1>Rencana produksi</h1>
        <p class="sub">Satu rencana = satu sesi produksi. Saat ditandai selesai, bahan baku terpotong otomatis sesuai resep.</p>
    </div>
    @can('manage-transactions')
        <div class="actions"><a href="{{ route('production.create') }}" class="btn btn-primary"><x-icon name="plus" width="16" height="16"/> Rencana baru</a></div>
    @endcan
</div>

<section class="panel">
    <div class="panel-body border-bottom">
        <form method="GET" class="filters">
            <div>
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="from">Tanggal dari</label>
                <input type="date" name="from" id="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
            </div>
            <div>
                <label class="form-label" for="to">sampai</label>
                <input type="date" name="to" id="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
            </div>
            <button class="btn btn-outline-primary">Terapkan</button>
            @if (array_filter($filters)) <a href="{{ route('production.index') }}" class="btn btn-link">Reset</a> @endif
        </form>
    </div>

    @if ($plans->isEmpty())
        <x-empty title="Belum ada rencana produksi."
                 :action="auth()->user()->can('manage-transactions') ? 'Buat rencana produksi' : null"
                 :href="route('production.create')">Rencana bisa berisi beberapa pre-order sekaligus plus produksi tambahan untuk stok toko.</x-empty>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Tanggal produksi</th><th>Produk</th><th class="num">Pre-order</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($plans as $plan)
                    <tr>
                        <td class="text-nowrap">
                            <a href="{{ route('production.show', $plan->id) }}" class="row-link">{{ tanggal($plan->plan_date, 'D, d M Y') }}</a>
                            <span class="cell-sub">{{ $plan->number }}</span>
                        </td>
                        <td class="small">{{ $plan->items->groupBy('product_id')->map(fn ($r) => $r->first()->product->name.' '.qty($r->sum('quantity')))->implode(', ') }}</td>
                        <td class="num">{{ $plan->items->pluck('pre_order_id')->filter()->unique()->count() }}</td>
                        <td><x-status :status="$plan->status"/></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if ($plans->hasPages()) <div class="panel-foot">{{ $plans->links() }}</div> @endif
    @endif
</section>
@endsection
