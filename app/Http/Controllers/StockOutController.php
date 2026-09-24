<?php

namespace App\Http\Controllers;

use App\Application\Inventory\IssueStock;
use App\Domain\Inventory\StockMovementType;
use App\Infrastructure\Persistence\Models\RawMaterial;
use App\Infrastructure\Persistence\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockOutController extends Controller
{
    public function create(Request $request)
    {
        $batches = StockBatch::where('quantity_remaining', '>', 0)
            ->orderByRaw('expiry_date is null')->orderBy('expiry_date')->orderBy('received_date')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'material' => $b->raw_material_id,
                'label' => ($b->batch_code ?: 'Batch #'.$b->id).' · sisa '.qty($b->quantity_remaining)
                    .($b->expiry_date ? ' · exp '.tanggal($b->expiry_date, 'd/m/Y').($b->isExpired() ? ' (kedaluwarsa)' : '') : ''),
            ]);

        return view('inventory.stock-out', [
            'materials' => RawMaterial::orderBy('name')->get(),
            'selectedMaterial' => $request->integer('bahan') ?: null,
            'selectedBatch' => $request->integer('batch') ?: null,
            'batches' => $batches,
            'types' => StockMovementType::manualOut(),
        ]);
    }

    public function store(Request $request, IssueStock $useCase)
    {
        $data = $request->validate([
            'raw_material_id' => ['required', 'integer', Rule::exists('raw_materials', 'id')->whereNull('deleted_at')],
            'type' => ['required', Rule::in(array_map(fn ($t) => $t->value, StockMovementType::manualOut()))],
            'stock_batch_id' => ['nullable', 'integer'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['required', 'string', 'max:1000'],
        ], [], ['stock_batch_id' => 'batch', 'notes' => 'alasan']);

        $useCase->execute(
            (int) $data['raw_material_id'],
            (float) $data['quantity'],
            StockMovementType::from($data['type']),
            ! empty($data['stock_batch_id']) ? (int) $data['stock_batch_id'] : null,
            $data['notes'],
            $this->actorId(),
        );

        return redirect()->route('inventory.show', $data['raw_material_id'])->with('success', 'Stok keluar tercatat.');
    }
}
