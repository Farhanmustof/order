<?php

namespace App\Http\Controllers;

use App\Application\Catalog\DeleteRawMaterial;
use App\Application\Catalog\SaveRawMaterial;
use App\Infrastructure\Persistence\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RawMaterialController extends Controller
{
    public const UNITS = ['kg', 'gram', 'liter', 'ml', 'butir', 'pcs', 'pack', 'sak', 'karton'];

    public function create()
    {
        return view('materials.form', ['material' => null, 'units' => self::UNITS]);
    }

    public function store(Request $request, SaveRawMaterial $useCase)
    {
        $id = $useCase->execute(null, $this->validated($request), $this->actorId());

        return redirect()->route('inventory.show', $id)->with('success', 'Bahan baku ditambahkan. Catat stok masuk pertamanya dari halaman ini.');
    }

    public function edit(int $id)
    {
        return view('materials.form', ['material' => RawMaterial::findOrFail($id), 'units' => self::UNITS]);
    }

    public function update(Request $request, int $id, SaveRawMaterial $useCase)
    {
        $useCase->execute($id, $this->validated($request, $id), $this->actorId());

        return redirect()->route('inventory.show', $id)->with('success', 'Data bahan baku diperbarui.');
    }

    public function destroy(int $id, DeleteRawMaterial $useCase)
    {
        $useCase->execute($id, $this->actorId());

        return redirect()->route('inventory.index')->with('success', 'Bahan baku dihapus.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('raw_materials', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], ['code.unique' => 'Kode ini sudah dipakai bahan lain (termasuk yang sudah dihapus).']);
    }
}
