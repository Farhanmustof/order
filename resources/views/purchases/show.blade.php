@extends('layouts.app')
@section('title', $order->number)

@php
    use App\Domain\Purchasing\PurchaseStatus;
    $flow = PurchaseStatus::flow();
    $currentIndex = array_search($order->status, $flow, true);
@endphp

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('purchases.index') }}">Pemesanan ke supplier</a></div>
        <h1>{{ $order->supplier->name }} <x-status :status="$order->status" class="align-middle ms-1"/></h1>
        <p class="sub">{{ $order->number }} · dipesan {{ tanggal($order->order_date, 'l, d M Y') }}</p>
    </div>
    <div class="actions">
        <button type="button" class="btn btn-light" onclick="window.print()">Cetak</button>
        @can('pembelian.kelola')
            @if ($order->status->isEditable())
                <a href="{{ route('purchases.edit', $order->id) }}" class="btn btn-light">Ubah</a>
            @endif
        @endcan
    </div>
</div>

<section class="panel mb-4">
    <div class="panel-body">
        @if ($order->status === PurchaseStatus::Cancelled)
            <div class="d-flex align-items-center gap-2"><x-status :status="$order->status"/> <span class="text-muted">Pemesanan ini dibatalkan. Stok tidak berubah.</span></div>
        @else
            <ol class="steps">
                @foreach ($flow as $i => $step)
                    <li @class(['done' => $i < $currentIndex || $order->status === PurchaseStatus::Received, 'current' => $i === $currentIndex && $order->status !== PurchaseStatus::Received])>{{ $step->label() }}</li>
                @endforeach
            </ol>
        @endif
    </div>

    @can('pembelian.kelola')
        @if ($order->status->canOrder() || $order->status->canCancel())
            <div class="panel-foot d-flex flex-wrap align-items-center gap-2 no-print">
                @if ($order->status->canOrder())
                    <span class="text-muted small me-auto">Kirim daftar ini ke supplier, lalu tandai sudah dipesan.</span>
                    <form method="POST" action="{{ route('purchases.ordered', $order->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-primary btn-sm">Tandai sudah dipesan</button>
                    </form>
                @else
                    <span class="text-muted small me-auto">Menunggu barang datang. Isi form penerimaan di bawah saat barang tiba.</span>
                @endif
                <form method="POST" action="{{ route('purchases.cancel', $order->id) }}" data-confirm="Batalkan pemesanan {{ $order->number }}?">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Batalkan</button>
                </form>
            </div>
        @endif
    @endcan
</section>

<div class="row g-4">
    <div class="col-lg-8">
        @php $canReceive = $order->status->canReceive() && auth()->user()->can('pembelian.kelola'); @endphp
        @if ($canReceive)
            <form method="POST" action="{{ route('purchases.receive', $order->id) }}"
                  data-confirm="Terima semua barang? Stok bahan baku akan bertambah sesuai jumlah yang dipesan.">
                @csrf
        @endif
        <section class="panel">
            <div class="panel-head">
                <h2>{{ $canReceive ? 'Penerimaan barang' : 'Barang yang dipesan' }}</h2>
                @if ($canReceive)<span class="hint">Isi tanggal kedaluwarsa sesuai kemasan, supaya stok dipakai dengan urutan FEFO.</span>@endif
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Bahan</th><th class="num">Jumlah</th><th class="num">Harga beli</th><th class="num">Subtotal</th>
                        @if ($canReceive)<th>Kedaluwarsa</th>@endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->material->name }}</td>
                            <td class="num">{{ qty($item->quantity) }} {{ $item->material->unit }}</td>
                            <td class="num">{{ rupiah($item->unit_cost) }}</td>
                            <td class="num">{{ rupiah($item->quantity * $item->unit_cost) }}</td>
                            @if ($canReceive)
                                <td style="min-width: 150px">
                                    <input type="date" name="expiry[{{ $item->id }}]" class="form-control form-control-sm"
                                           value="{{ old('expiry.'.$item->id) }}" aria-label="Kedaluwarsa {{ $item->material->name }}">
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-foot">
                <div class="money-summary tabular">
                    <span class="total">Total</span><span class="total text-end">{{ rupiah($order->total) }}</span>
                </div>
                @if ($canReceive)
                    <div class="d-flex flex-wrap align-items-end gap-2 justify-content-end mt-3 no-print">
                        <div>
                            <label class="form-label" for="received_date">Tanggal diterima</label>
                            <input type="date" name="received_date" id="received_date" class="form-control" required
                                   value="{{ old('received_date', today()->toDateString()) }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Terima barang &amp; tambah stok</button>
                    </div>
                @endif
            </div>
        </section>
        @if ($canReceive)
            </form>
        @endif
    </div>

    <div class="col-lg-4">
        <section class="panel">
            <div class="panel-head"><h2>Detail</h2></div>
            <div class="panel-body">
                <dl class="facts">
                    <dt>Supplier</dt><dd class="fw-semibold">{{ $order->supplier->name }}<span class="cell-sub">{{ $order->supplier->phone ?: '' }}</span></dd>
                    <dt>Perkiraan datang</dt><dd>{{ tanggal($order->expected_date, 'l, d M Y') }}</dd>
                    <dt>Diterima</dt><dd>{{ tanggal($order->received_date, 'l, d M Y') }}</dd>
                    <dt>Catatan</dt><dd>{{ $order->notes ?: '-' }}</dd>
                    <dt>Dicatat</dt><dd>{{ $order->creator?->name ?? '-' }}<span class="cell-sub">{{ $order->created_at->translatedFormat('d M Y H:i') }}</span></dd>
                </dl>
            </div>
            @can('pembelian.kelola')
                @if ($order->status->isDeletable())
                    <div class="panel-foot no-print">
                        <x-delete-button :action="route('purchases.destroy', $order->id)" label="Hapus pemesanan"
                                         confirm="Hapus pemesanan {{ $order->number }}?"/>
                    </div>
                @endif
            @endcan
        </section>
    </div>
</div>
@endsection
