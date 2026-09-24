<?php

namespace App\Http\Controllers;

use App\Application\Catalog\DeleteSupplier;
use App\Application\Catalog\SaveSupplier;
use App\Infrastructure\Persistence\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        return view('suppliers.index', [
            'suppliers' => Supplier::query()
                ->withCount('batches')
                ->when($request->input('q'), fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
                ->orderBy('name')->paginate(25)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('suppliers.form', ['supplier' => null]);
    }

    public function store(Request $request, SaveSupplier $useCase)
    {
        $useCase->execute(null, $this->validated($request), $this->actorId());

        return redirect()->route('suppliers.index')->with('success', 'Supplier ditambahkan.');
    }

    public function edit(int $id)
    {
        return view('suppliers.form', ['supplier' => Supplier::findOrFail($id)]);
    }

    public function update(Request $request, int $id, SaveSupplier $useCase)
    {
        $useCase->execute($id, $this->validated($request), $this->actorId());

        return redirect()->route('suppliers.index')->with('success', 'Data supplier diperbarui.');
    }

    public function destroy(int $id, DeleteSupplier $useCase)
    {
        $useCase->execute($id, $this->actorId());

        return redirect()->route('suppliers.index')->with('success', 'Supplier dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
