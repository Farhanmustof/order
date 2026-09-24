<?php

namespace App\Http\Controllers;

use App\Application\PreOrder\ChangePreOrderStatus;
use App\Application\PreOrder\CreatePreOrder;
use App\Application\PreOrder\DeletePreOrder;
use App\Application\PreOrder\PreOrderInput;
use App\Application\PreOrder\UpdatePreOrder;
use App\Domain\PreOrder\PreOrderStatus;
use App\Infrastructure\Persistence\Models\Product;
use App\Infrastructure\Queries\PreOrderQuery;
use App\Infrastructure\Services\CsvExporter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PreOrderController extends Controller
{
    public function __construct(private PreOrderQuery $query)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'status', 'from', 'to']);

        return view('pre-orders.index', [
            'orders' => $this->query->paginate($filters),
            'filters' => $filters,
            'statuses' => PreOrderStatus::cases(),
        ]);
    }

    public function show(int $id)
    {
        return view('pre-orders.show', ['order' => $this->query->detail($id)]);
    }

    public function create()
    {
        return view('pre-orders.form', ['order' => null, 'products' => Product::orderBy('name')->get()]);
    }

    public function store(Request $request, CreatePreOrder $useCase)
    {
        $id = $useCase->execute(PreOrderInput::fromArray($this->validated($request)), $this->actorId());

        return redirect()->route('pre-orders.show', $id)->with('success', 'Pre-order tersimpan sebagai Draft. Konfirmasi bila pesanan sudah pasti.');
    }

    public function edit(int $id)
    {
        $order = $this->query->detail($id);
        if (! $order->status->isEditable()) {
            return redirect()->route('pre-orders.show', $id)->with('error', "Pre-order berstatus {$order->status->label()} tidak bisa diubah lagi.");
        }

        return view('pre-orders.form', ['order' => $order, 'products' => Product::orderBy('name')->get()]);
    }

    public function update(Request $request, int $id, UpdatePreOrder $useCase)
    {
        $useCase->execute($id, PreOrderInput::fromArray($this->validated($request)), $this->actorId());

        return redirect()->route('pre-orders.show', $id)->with('success', 'Perubahan pre-order tersimpan.');
    }

    public function changeStatus(Request $request, int $id, ChangePreOrderStatus $useCase)
    {
        $data = $request->validate(['status' => ['required', Rule::enum(PreOrderStatus::class)]]);
        $status = PreOrderStatus::from($data['status']);
        $useCase->execute($id, $status, $this->actorId());

        return back()->with('success', "Status pre-order sekarang {$status->label()}.");
    }

    public function destroy(int $id, DeletePreOrder $useCase)
    {
        $useCase->execute($id, $this->actorId());

        return redirect()->route('pre-orders.index')->with('success', 'Pre-order dihapus. Admin masih bisa memulihkannya dari Data terhapus.');
    }

    public function export(Request $request, CsvExporter $csv)
    {
        $orders = $this->query->query($request->only(['q', 'status', 'from', 'to']))->get();

        return $csv->download('pre-order', [
            'Nomor', 'Pelanggan', 'Telepon', 'Tanggal pesan', 'Jatuh tempo', 'Status', 'Produk', 'Total', 'DP', 'Sisa bayar',
        ], $orders->map(fn ($o) => [
            $o->number, $o->customer_name, $o->customer_phone, $o->order_date->format('d/m/Y'), $o->due_date->format('d/m/Y'),
            $o->status->label(),
            $o->items->map(fn ($i) => $i->product->name.' x '.qty($i->quantity))->implode(', '),
            (float) $o->total, (float) $o->down_payment, (float) ($o->total - $o->down_payment),
        ]));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'customer_address' => ['nullable', 'string', 'max:1000'],
            'order_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:order_date'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.*.product_id.distinct' => 'Produk yang sama dipilih lebih dari sekali.',
        ], [
            'items.*.product_id' => 'produk',
            'items.*.quantity' => 'jumlah',
            'items.*.unit_price' => 'harga satuan',
        ]);
    }
}
