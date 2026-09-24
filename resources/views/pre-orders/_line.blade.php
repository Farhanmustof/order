<div class="line grid-po">
    <select name="items[{{ $i }}][product_id]" class="form-select" data-product required aria-label="Produk">
        <option value="">Pilih produk…</option>
        @foreach ($products as $product)
            <option value="{{ $product->id }}" data-price="{{ $product->price + 0 }}" @selected((string) $item['product_id'] === (string) $product->id)>
                {{ $product->name }} ({{ $product->unit }})
            </option>
        @endforeach
    </select>
    <input type="number" name="items[{{ $i }}][quantity]" class="form-control" data-qty min="0.001" step="any" required
           value="{{ $item['quantity'] }}" aria-label="Jumlah" placeholder="0">
    <input type="number" name="items[{{ $i }}][unit_price]" class="form-control" data-unit-price min="0" step="1" required
           value="{{ $item['unit_price'] }}" aria-label="Harga satuan" placeholder="0">
    <div class="subtotal text-end tabular pt-2">Rp 0</div>
    <button type="button" class="remove" aria-label="Hapus baris"><x-icon name="x" width="18" height="18"/></button>
</div>
