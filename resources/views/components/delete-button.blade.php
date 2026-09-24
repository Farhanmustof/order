@props(['action', 'confirm' => 'Hapus data ini?', 'label' => 'Hapus'])
<form method="POST" action="{{ $action }}" data-confirm="{{ $confirm }}" class="d-inline">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => 'btn-link-danger']) }}>{{ $label }}</button>
</form>
