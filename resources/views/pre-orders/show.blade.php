@extends('layouts.app')
@section('title', $order->number)

@php
    use App\Domain\PreOrder\PreOrderStatus;
    $flow = PreOrderStatus::flow();
    $currentIndex = array_search($order->status, $flow, true);
    $openPlan = $order->planItems->map->plan->filter(fn ($p) => $p && $p->status->isOpen())->first();
    $plans = $order->planItems->map->plan->filter()->unique('id');
@endphp

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('pre-orders.index') }}">Pre-order</a></div>
        <h1>{{ $order->customer_name }}</h1>
        <p class="sub">{{ $order->number }} · dipesan {{ tanggal($order->order_date) }}</p>
    </div>
    <div class="actions">
        <button type="button" class="btn btn-light" onclick="window.print()">Cetak</button>
        @can('manage-transactions')
            @if ($order->status->isEditable() && ! $openPlan)
                <a href="{{ route('pre-orders.edit', $order->id) }}" class="btn btn-light">Ubah</a>
            @endif
        @endcan
    </div>
</div>

<section class="panel mb-4">
    <div class="panel-body">
        @if ($order->status === PreOrderStatus::Cancelled)
            <div class="d-flex align-items-center gap-2"><x-status :status="$order->status"/> <span class="text-muted">Pesanan ini dibatalkan dan tidak akan diproduksi.</span></div>
        @else
            <ol class="steps">
                @foreach ($flow as $i => $step)
                    <li @class(['done' => $i < $currentIndex, 'current' => $i === $currentIndex])>{{ $step->label() }}</li>
                @endforeach
            </ol>
        @endif
    </div>

    @can('manage-transactions')
        @if ($order->status->nextStatuses())
            <div class="panel-foot d-flex flex-wrap align-items-center gap-2 no-print">
                @if ($openPlan)
                    <span class="text-muted small">Status diatur otomatis oleh rencana produksi
                        <a href="{{ route('production.show', $openPlan->id) }}">{{ $openPlan->number }}</a>.</span>
                @else
                    <span class="text-muted small me-auto">
                        @switch($order->status)
                            @case(PreOrderStatus::Draft) Pesanan sudah pasti? Konfirmasi agar bisa dimasukkan ke rencana produksi. @break
                            @case(PreOrderStatus::Confirmed) Masukkan ke rencana produksi, atau ubah status manual bila diproduksi di luar sistem. @break
                            @case(PreOrderStatus::Completed) Pesanan siap. Tandai dikirim setelah diterima pelanggan. @break
                            @default Langkah berikutnya:
                        @endswitch
                    </span>
                    @if ($order->status === PreOrderStatus::Confirmed)
                        <a href="{{ route('production.create', ['pre_order' => $order->id]) }}" class="btn btn-primary btn-sm">Masukkan ke rencana produksi</a>
                    @endif
                    @foreach ($order->status->nextStatuses() as $next)
                        <form method="POST" action="{{ route('pre-orders.status', $order->id) }}"
                              @if ($next === PreOrderStatus::Cancelled) data-confirm="Batalkan pre-order ini?" @endif>
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $next->value }}">
                            <button type="submit" @class([
                                'btn btn-sm',
                                'btn-outline-danger' => $next === PreOrderStatus::Cancelled,
                                'btn-light' => $next === PreOrderStatus::InProduction,
                                'btn-primary' => ! in_array($next, [PreOrderStatus::Cancelled, PreOrderStatus::InProduction], true) && $order->status !== PreOrderStatus::Confirmed,
                            ])>
                                @switch($next)
                                    @case(PreOrderStatus::Confirmed) Konfirmasi pesanan @break
                                    @case(PreOrderStatus::InProduction) Tandai diproduksi (manual) @break
                                    @case(PreOrderStatus::Completed) Tandai selesai @break
                                    @case(PreOrderStatus::Delivered) Tandai sudah dikirim @break
                                    @case(PreOrderStatus::Cancelled) Batalkan @break
                                @endswitch
                            </button>
                        </form>
                    @endforeach
                @endif
            </div>
        @endif
    @endcan
</section>

<div class="row g-4">
    <div class="col-lg-8">
        <section class="panel">
            <div class="panel-head"><h2>Produk yang dipesan</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Produk</th><th class="num">Jumlah</th><th class="num">Harga satuan</th><th class="num">Subtotal</th></tr></thead>
                    <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name }} @if ($item->product->trashed()) <span class="pill pill-warn">produk dihapus</span> @endif</td>
                            <td class="num">{{ qty($item->quantity) }} {{ $item->product->unit }}</td>
                            <td class="num">{{ rupiah($item->unit_price) }}</td>
                            <td class="num">{{ rupiah($item->quantity * $item->unit_price) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-foot">
                <div class="money-summary tabular">
                    <span>Total</span><span class="text-end">{{ rupiah($order->total) }}</span>
                    <span>DP dibayar</span><span class="text-end">{{ rupiah($order->down_payment) }}</span>
                    <span class="total">Sisa pembayaran</span>
                    <span class="total text-end">{{ $order->total - $order->down_payment > 0 ? rupiah($order->total - $order->down_payment) : 'Lunas' }}</span>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-4">
        <section class="panel">
            <div class="panel-head"><h2>Detail</h2></div>
            <div class="panel-body">
                <dl class="facts">
                    <dt>Jatuh tempo</dt><dd class="fw-semibold">{{ tanggal($order->due_date, 'l, d M Y') }}</dd>
                    <dt>Telepon</dt><dd>{{ $order->customer_phone ?: '-' }}</dd>
                    <dt>Alamat</dt><dd>{{ $order->customer_address ?: '-' }}</dd>
                    <dt>Catatan</dt><dd>{{ $order->notes ?: '-' }}</dd>
                    <dt>Produksi</dt>
                    <dd>
                        @forelse ($plans as $plan)
                            <a href="{{ route('production.show', $plan->id) }}">{{ $plan->number }}</a> <span class="text-muted small">({{ $plan->status->label() }})</span><br>
                        @empty - @endforelse
                    </dd>
                    <dt>Dicatat</dt><dd>{{ $order->creator?->name ?? '-' }}<span class="cell-sub">{{ $order->created_at->translatedFormat('d M Y H:i') }}</span></dd>
                    <dt>Diubah</dt><dd>{{ $order->editor?->name ?? '-' }}<span class="cell-sub">{{ $order->updated_at->translatedFormat('d M Y H:i') }}</span></dd>
                </dl>
            </div>
            @can('manage-transactions')
                @if ($order->status->isDeletable() && ! $openPlan)
                    <div class="panel-foot no-print">
                        <x-delete-button :action="route('pre-orders.destroy', $order->id)" label="Hapus pre-order"
                                         confirm="Hapus pre-order {{ $order->number }}? Admin masih bisa memulihkannya."/>
                    </div>
                @endif
            @endcan
        </section>
    </div>
</div>
@endsection
