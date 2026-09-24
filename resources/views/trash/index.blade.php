@extends('layouts.app')
@section('title', 'Data terhapus')

@section('content')
<div class="page-head">
    <div>
        <h1>Data terhapus</h1>
        <p class="sub">Data yang dihapus tidak benar-benar hilang. Pulihkan bila terhapus tidak sengaja.</p>
    </div>
</div>

<ul class="nav nav-tabs mb-0">
    @foreach ($types as $key => $label)
        <li class="nav-item"><a class="nav-link {{ $type === $key ? 'active' : '' }}" href="{{ route('trash.index', ['jenis' => $key]) }}">{{ $label }}</a></li>
    @endforeach
</ul>

<section class="panel" style="border-top-left-radius: 0">
    @if ($records->isEmpty())
        <x-empty title="Tidak ada {{ strtolower($types[$type]) }} yang terhapus."></x-empty>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Data</th><th>Dihapus</th><th></th></tr></thead>
                <tbody>
                @foreach ($records as $r)
                    <tr>
                        <td>
                            <span class="fw-semibold">
                                @switch($type)
                                    @case('pre_order') {{ $r->number }} · {{ $r->customer_name }} @break
                                    @case('production_plan') {{ $r->number }} · {{ tanggal($r->plan_date) }} @break
                                    @case('stock_batch') {{ $r->batch_code ?: 'Batch #'.$r->id }} · {{ $r->material->name }} {{ qty($r->quantity_initial) }} {{ $r->material->unit }} @break
                                    @default {{ $r->name }}
                                @endswitch
                            </span>
                        </td>
                        <td class="text-nowrap">{{ $r->deleted_at->translatedFormat('d M Y H:i') }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('trash.restore', [$type, $r->id]) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-light btn-sm">Pulihkan</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if ($records->hasPages()) <div class="panel-foot">{{ $records->links() }}</div> @endif
    @endif
</section>
@endsection
