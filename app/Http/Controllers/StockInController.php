<?php

namespace App\Http\Controllers;

use App\Application\Inventory\DeleteStockBatch;
use App\Application\Inventory\ReceiveStock;
use App\Application\Inventory\StockBatchInput;
use App\Application\Inventory\UpdateStockBatch;
use App\Infrastructure\Persistence\Models\RawMaterial;
use App\Infrastructure\Persistence\Models\StockBatch;
use App\Infrastructure\Persistence\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockInController extends Controller
{
    public function create(Request $request)
    {
        return view('inventory.stock-in', [
            'batch' => null,
            'selectedMaterial' => $request->integer('bahan') ?: null,
            'materials' => RawMaterial::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ReceiveStock $useCase)
    {
        $input = StockBatchInput::fromArray($this->validated($request));
        $useCase->execute($input, $this->actorId());

        return redirect()->route('inventory.show', $input->rawMaterialId)->with('success', 'Stok masuk tercatat.');
    }

    public function edit(int $id)
    {
        $batch = StockBatch::with('material')->findOrFail($id);

        return view('inventory.stock-in', [
            'batch' => $batch,
            'selectedMaterial' => $batch->raw_material_id,
            'materials' => RawMaterial::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, int $id, UpdateStockBatch $useCase)
    {
        $input = StockBatchInput::fromArray($this->validated($request));
        $useCase->execute($id, $input, $this->actorId());

        return redirect()->route('inventory.show', $input->rawMaterialId)->with('success', 'Data batch diperbarui.');
    }

    public function destroy(int $id, DeleteStockBatch $useCase)
    {
        $materialId = StockBatch::findOrFail($id)->raw_material_id;
        $useCase->execute($id, $this->actorId());

        return redirect()->route('inventory.show', $materialId)->with('success', 'Catatan stok masuk dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'raw_material_id' => ['required', 'integer', Rule::exists('raw_materials', 'id')->whereNull('deleted_at')],
            'supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            'batch_code' => ['nullable', 'string', 'max:50'],
            'received_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'expiry_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:received_date'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
