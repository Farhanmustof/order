<?php

namespace App\Http\Controllers;

use App\Application\Production\CancelProduction;
use App\Application\Production\CompleteProduction;
use App\Application\Production\CreateProductionPlan;
use App\Application\Production\DeleteProductionPlan;
use App\Application\Production\GetMaterialRequirements;
use App\Application\Production\ProductionPlanInput;
use App\Application\Production\StartProduction;
use App\Application\Production\UpdateProductionPlan;
use App\Domain\Production\ProductionStatus;
use App\Infrastructure\Persistence\Models\Product;
use App\Infrastructure\Queries\PreOrderQuery;
use App\Infrastructure\Queries\ProductionPlanQuery;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductionPlanController extends Controller
{
    public function __construct(private ProductionPlanQuery $query, private PreOrderQuery $orders)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'from', 'to']);

        return view('production.index', [
            'plans' => $this->query->paginate($filters),
            'filters' => $filters,
            'statuses' => ProductionStatus::cases(),
        ]);
    }

    public function show(int $id, GetMaterialRequirements $requirements)
    {
        $plan = $this->query->detail($id);
        $totals = $this->query->productTotals($plan);

        return view('production.show', [
            'plan' => $plan,
            'totals' => $totals,
            'requirements' => $plan->status->isOpen()
                ? $requirements->execute($totals->mapWithKeys(fn ($t) => [$t->product->id => $t->quantity])->all())
                : null,
        ]);
    }

    public function create(Request $request)
    {
        return view('production.form', [
            'plan' => null,
            'preselected' => array_filter([$request->integer('pre_order')]),
            'readyOrders' => $this->orders->readyForProduction(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, CreateProductionPlan $useCase)
    {
        $id = $useCase->execute(ProductionPlanInput::fromArray($this->validated($request)), $this->actorId());

        return redirect()->route('production.show', $id)->with('success', 'Rencana produksi tersimpan. Periksa kecukupan bahan di bawah.');
    }

    public function edit(int $id)
    {
        $plan = $this->query->detail($id);
        if ($plan->status !== ProductionStatus::Planned) {
            return redirect()->route('production.show', $id)->with('error', 'Hanya rencana berstatus Direncanakan yang bisa diubah.');
        }

        return view('production.form', [
            'plan' => $plan,
            'preselected' => $plan->items->pluck('pre_order_id')->filter()->unique()->values()->all(),
            'readyOrders' => $this->orders->readyForProduction($id),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, int $id, UpdateProductionPlan $useCase)
    {
        $useCase->execute($id, ProductionPlanInput::fromArray($this->validated($request)), $this->actorId());

        return redirect()->route('production.show', $id)->with('success', 'Perubahan rencana produksi tersimpan.');
    }

    public function start(int $id, StartProduction $useCase)
    {
        $useCase->execute($id, $this->actorId());

        return back()->with('success', 'Produksi dimulai. Pre-order terkait kini berstatus Diproduksi.');
    }

    public function complete(int $id, CompleteProduction $useCase)
    {
        $useCase->execute($id, $this->actorId());

        return back()->with('success', 'Produksi selesai. Stok bahan baku sudah dipotong dan pre-order terkait berstatus Selesai.');
    }

    public function cancel(int $id, CancelProduction $useCase)
    {
        $useCase->execute($id, $this->actorId());

        return back()->with('success', 'Rencana produksi dibatalkan. Pre-order terkait kembali ke Dikonfirmasi.');
    }

    public function destroy(int $id, DeleteProductionPlan $useCase)
    {
        $useCase->execute($id, $this->actorId());

        return redirect()->route('production.index')->with('success', 'Rencana produksi dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'plan_date' => ['required', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'pre_order_ids' => ['nullable', 'array'],
            'pre_order_ids.*' => ['integer'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ], [], [
            'items.*.product_id' => 'produk',
            'items.*.quantity' => 'jumlah',
        ]);
    }
}
