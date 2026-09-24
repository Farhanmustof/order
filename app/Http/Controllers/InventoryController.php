<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\StockMovementType;
use App\Infrastructure\Persistence\Models\Product;
use App\Infrastructure\Persistence\Models\RawMaterial;
use App\Infrastructure\Queries\InventoryQuery;
use App\Infrastructure\Services\CsvExporter;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private InventoryQuery $query)
    {
    }

    public function index(Request $request)
    {
        return view('inventory.index', [
            'materials' => $this->query->materialsWithStock($request->input('q'), $request->input('filter')),
            'filters' => $request->only(['q', 'filter']),
            'warningDays' => InventoryQuery::EXPIRY_WARNING_DAYS,
        ]);
    }

    public function show(int $id)
    {
        $material = $this->query->materialsWithStock()->firstWhere('id', $id) ?? abort(404);

        return view('inventory.show', [
            'material' => $material,
            'batches' => $this->query->batchesOf($id),
            'movements' => $this->query->movements(['material_id' => $id], 15),
            'usedIn' => Product::whereHas('materials', fn ($q) => $q->where('raw_materials.id', $id))
                ->with(['materials' => fn ($q) => $q->where('raw_materials.id', $id)])
                ->orderBy('name')->get(),
        ]);
    }

    public function movements(Request $request)
    {
        $filters = $request->only(['material_id', 'type', 'from', 'to']);

        return view('inventory.movements', [
            'movements' => $this->query->movements($filters),
            'filters' => $filters,
            'materials' => RawMaterial::orderBy('name')->get(),
            'types' => StockMovementType::cases(),
        ]);
    }

    public function export(CsvExporter $csv)
    {
        return $csv->download('stok-bahan-baku', [
            'Kode', 'Bahan', 'Satuan', 'Stok total', 'Stok kedaluwarsa', 'Stok layak pakai', 'Stok minimum', 'Status', 'Kedaluwarsa terdekat',
        ], $this->query->materialsWithStock()->map(fn ($m) => [
            $m->code, $m->name, $m->unit, $m->stock, $m->expired_stock, $m->usable_stock, $m->minimum_stock,
            $m->is_low ? 'Perlu belanja' : 'Aman',
            $m->next_expiry ? tanggal($m->next_expiry, 'd/m/Y') : '-',
        ]));
    }

    public function exportMovements(Request $request, CsvExporter $csv)
    {
        $rows = $this->query->movementQuery($request->only(['material_id', 'type', 'from', 'to']))->get();

        return $csv->download('riwayat-stok', [
            'Waktu', 'Bahan', 'Jenis', 'Masuk', 'Keluar', 'Satuan', 'Batch', 'Rencana produksi', 'Catatan', 'Dicatat oleh',
        ], $rows->map(fn ($r) => [
            $r->created_at->format('d/m/Y H:i'), $r->material->name, $r->type->label(),
            $r->type->isOutgoing() ? '' : $r->quantity, $r->type->isOutgoing() ? $r->quantity : '',
            $r->material->unit, $r->batch?->batch_code, $r->plan?->number, $r->notes, $r->creator?->name,
        ]));
    }
}
