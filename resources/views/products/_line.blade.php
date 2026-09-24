<div class="line grid-recipe">
    <select name="recipe[{{ $i }}][raw_material_id]" class="form-select" data-unit-source required aria-label="Bahan baku">
        <option value="">Pilih bahan…</option>
        @foreach ($materials as $m)
            <option value="{{ $m->id }}" data-unit="{{ $m->unit }}" @selected((string) $line['raw_material_id'] === (string) $m->id)>{{ $m->name }}</option>
        @endforeach
    </select>
    <div class="input-group">
        <input type="number" name="recipe[{{ $i }}][quantity]" class="form-control" min="0.0001" step="any" required value="{{ $line['quantity'] }}" aria-label="Takaran">
        <span class="input-group-text" data-unit-label>{{ $materials->firstWhere('id', $line['raw_material_id'])?->unit }}</span>
    </div>
    <button type="button" class="remove" aria-label="Hapus bahan"><x-icon name="x" width="18" height="18"/></button>
</div>
