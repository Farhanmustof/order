@if ($movements->isEmpty())
    <x-empty title="Belum ada pergerakan stok."></x-empty>
@else
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>Waktu</th>
                @if ($showMaterial) <th>Bahan</th> @endif
                <th>Jenis</th>
                <th class="num">Jumlah</th>
                <th>Keterangan</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($movements as $mv)
                <tr>
                    <td class="text-nowrap">{{ $mv->created_at->translatedFormat('d M Y') }}<span class="cell-sub">{{ $mv->created_at->format('H:i') }} · {{ $mv->creator?->name ?? '-' }}</span></td>
                    @if ($showMaterial)
                        <td><a href="{{ route('inventory.show', $mv->raw_material_id) }}" class="row-link">{{ $mv->material->name }}</a></td>
                    @endif
                    <td><x-status :status="$mv->type"/></td>
                    <td class="num fw-semibold {{ $mv->type->isOutgoing() ? 'text-danger' : '' }}">
                        {{ $mv->type->isOutgoing() ? '−' : '+' }}{{ qty($mv->quantity) }} <small class="text-muted fw-normal">{{ $mv->material->unit }}</small>
                    </td>
                    <td class="small">
                        {{ $mv->batch?->batch_code ?: ($mv->stock_batch_id ? 'Batch #'.$mv->stock_batch_id : '') }}
                        @if ($mv->plan) · <a href="{{ route('production.show', $mv->production_plan_id) }}">{{ $mv->plan->number }}</a> @endif
                        @if ($mv->notes) <span class="cell-sub">{{ $mv->notes }}</span> @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if ($movements instanceof \Illuminate\Contracts\Pagination\Paginator && $movements->hasPages())
        <div class="panel-foot">{{ $movements->links() }}</div>
    @endif
@endif
