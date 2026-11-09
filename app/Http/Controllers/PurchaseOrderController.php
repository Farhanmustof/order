<?php

namespace App\Http\Controllers;

use App\Application\Purchasing\ManagePurchaseOrder;
use App\Application\Purchasing\ReceivePurchaseOrder;
use App\Domain\Purchasing\PurchaseStatus;
use App\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Infrastructure\Persistence\Models\RawMaterial;
use App\Infrastructure\Persistence\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Pemesanan barang ke supplier. */
class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'status']);
        $orders = PurchaseOrder::with(['supplier', 'items.material'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('number', 'like', "%{$term}%")
                ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$term}%"))))
            ->orderByRaw("CASE status WHEN 'draft' THEN 0 WHEN 'ordered' THEN 1 ELSE 2 END")
            ->orderByDesc('order_date')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        return view('purchases.index', ['orders' => $orders, 'filters' => $filters, 'statuses' => PurchaseStatus::cases()]);
    }

    public function create()
    {
        return view('purchases.form', ['order' => null] + $this->choices());
    }

    public function store(Request $request, ManagePurchaseOrder $useCase)
    {
        $id = $useCase->create($this->validated($request), $this->actorId());

        return redirect()->route('purchases.show', $id)->with('success', 'Pemesanan disimpan sebagai Draft. Tandai "Sudah dipesan" setelah dikirim ke supplier.');
    }

    public function show(int $id)
    {
        $order = PurchaseOrder::with(['supplier', 'items.material', 'creator'])->findOrFail($id);

        return view('purchases.show', ['order' => $order]);
    }

    public function edit(int $id)
    {
        $order = PurchaseOrder::with('items')->findOrFail($id);
        if (! $order->status->isEditable()) {
            return redirect()->route('purchases.show', $id)->with('error', 'Pemesanan yang sudah dipesan tidak bisa diubah lagi.');
        }

        return view('purchases.form', ['order' => $order] + $this->choices());
    }

    public function update(Request $request, int $id, ManagePurchaseOrder $useCase)
    {
        $useCase->update($id, $this->validated($request), $this->actorId());

        return redirect()->route('purchases.show', $id)->with('success', 'Pemesanan diperbarui.');
    }

    public function markOrdered(int $id, ManagePurchaseOrder $useCase)
    {
        $useCase->markOrdered($id, $this->actorId());

        return back()->with('success', 'Pemesanan ditandai sudah dipesan ke supplier.');
    }

    public function receive(Request $request, int $id, ReceivePurchaseOrder $useCase)
    {
        $data = $request->validate([
            'received_date' => ['required', 'date'],
            'expiry' => ['array'],
            'expiry.*' => ['nullable', 'date'],
        ]);
        $useCase->execute($id, $data['received_date'], $data['expiry'] ?? [], $this->actorId());

        return back()->with('success', 'Barang diterima dan sudah masuk ke stok bahan baku.');
    }

    public function cancel(int $id, ManagePurchaseOrder $useCase)
    {
        $useCase->cancel($id, $this->actorId());

        return back()->with('success', 'Pemesanan dibatalkan.');
    }

    public function destroy(int $id, ManagePurchaseOrder $useCase)
    {
        $useCase->delete($id, $this->actorId());

        return redirect()->route('purchases.index')->with('success', 'Pemesanan dihapus.');
    }

    private function choices(): array
    {
        return [
            'suppliers' => Supplier::orderBy('name')->get(),
            'materials' => RawMaterial::orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.raw_material_id' => ['required', Rule::exists('raw_materials', 'id')->whereNull('deleted_at')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Tambahkan minimal satu barang.',
            'items.*.raw_material_id.required' => 'Pilih bahan pada setiap baris.',
            'items.*.quantity.gt' => 'Jumlah setiap barang harus lebih dari 0.',
        ]);
    }
}
