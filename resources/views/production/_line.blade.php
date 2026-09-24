<div class="line grid-plan">
    <select name="items[{{ $i }}][product_id]" class="form-select" data-unit-source required aria-label="Produk">
        <option value="">Pilih produk…</option>
        @foreach ($products as $product)
            <option value="{{ $product->id }}" data-unit="{{ $product->unit }}" @selected((string) $item['product_id'] === (string) $product->id)>{{ $product->name }}</option>
        @endforeach
    </select>
    <div class="input-group">
        <input type="number" name="items[{{ $i }}][quantity]" class="form-control" min="0.001" step="any" required value="{{ $item['quantity'] }}" aria-label="Jumlah">
        <span class="input-group-text" data-unit-label>{{ $products->firstWhere('id', $item['product_id'])?->unit }}</span>
    </div>
    <button type="button" class="remove" aria-label="Hapus baris"><x-icon name="x" width="18" height="18"/></button>
</div>
