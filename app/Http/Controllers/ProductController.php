<?php

namespace App\Http\Controllers;

use App\Application\Catalog\DeleteProduct;
use App\Application\Catalog\SaveProduct;
use App\Infrastructure\Persistence\Models\Product;
use App\Infrastructure\Persistence\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public const UNITS = ['pcs', 'loyang', 'bungkus', 'box', 'toples', 'porsi', 'kg', 'liter'];

    public function index(Request $request)
    {
        return view('products.index', [
            'products' => Product::with('materials')
                ->when($request->input('q'), fn ($q, $v) => $q->where(fn ($q) => $q->where('name', 'like', "%{$v}%")->orWhere('code', 'like', "%{$v}%")))
                ->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('products.form', ['product' => null, 'materials' => RawMaterial::orderBy('name')->get(), 'units' => self::UNITS]);
    }

    public function store(Request $request, SaveProduct $useCase)
    {
        $data = $this->validated($request);
        $useCase->execute(null, $data, $data['recipe'] ?? [], $this->actorId());

        return redirect()->route('products.index')->with('success', 'Produk dan resepnya tersimpan.');
    }

    public function edit(int $id)
    {
        return view('products.form', [
            'product' => Product::with('materials')->findOrFail($id),
            'materials' => RawMaterial::orderBy('name')->get(),
            'units' => self::UNITS,
        ]);
    }

    public function update(Request $request, int $id, SaveProduct $useCase)
    {
        $data = $this->validated($request, $id);
        $useCase->execute($id, $data, $data['recipe'] ?? [], $this->actorId());

        return redirect()->route('products.index')->with('success', 'Perubahan produk tersimpan.');
    }

    public function destroy(int $id, DeleteProduct $useCase)
    {
        $useCase->execute($id, $this->actorId());

        return redirect()->route('products.index')->with('success', 'Produk dihapus.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('products', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'recipe' => ['nullable', 'array'],
            'recipe.*.raw_material_id' => ['required', 'integer', 'distinct', Rule::exists('raw_materials', 'id')->whereNull('deleted_at')],
            'recipe.*.quantity' => ['required', 'numeric', 'gt:0'],
        ], [
            'code.unique' => 'Kode ini sudah dipakai produk lain (termasuk yang sudah dihapus).',
            'recipe.*.raw_material_id.distinct' => 'Bahan yang sama dipilih lebih dari sekali.',
        ], [
            'recipe.*.raw_material_id' => 'bahan',
            'recipe.*.quantity' => 'takaran',
        ]);
    }
}
