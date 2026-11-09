<div class="line grid-po">
    <select name="items[{{ $i }}][raw_material_id]" class="form-select" required aria-label="Bahan">
        <option value="">Pilih bahan…</option>
        @foreach ($materials as $material)
            <option value="{{ $material->id }}" @selected((string) $item['raw_material_id'] === (string) $material->id)>
                {{ $material->name }} ({{ $material->unit }})
            </option>
        @endforeach
    </select>
    <input type="number" name="items[{{ $i }}][quantity]" class="form-control" data-qty min="0.001" step="any" required
           value="{{ $item['quantity'] }}" aria-label="Jumlah" placeholder="0">
    <input type="number" name="items[{{ $i }}][unit_cost]" class="form-control" data-unit-price min="0" step="1"
           value="{{ $item['unit_cost'] }}" aria-label="Harga beli satuan" placeholder="0">
    <div class="subtotal text-end tabular pt-2">Rp 0</div>
    <button type="button" class="remove" aria-label="Hapus baris"><x-icon name="x" width="18" height="18"/></button>
</div>
