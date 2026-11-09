@extends('layouts.app')
@section('title', $user ? 'Ubah akun' : 'Tambah akun')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('users.index') }}">Pengguna</a></div>
        <h1>{{ $user ? 'Ubah akun '.$user->name : 'Tambah akun' }}</h1>
    </div>
</div>
<div class="row">
    <div class="col-lg-7 col-xl-6">
        <form method="POST" action="{{ $user ? route('users.update', $user->id) : route('users.store') }}" class="panel">
            @csrf
            @if ($user) @method('PUT') @endif
            <div class="panel-body">
                <div class="mb-3">
                    <label class="form-label req" for="name">Nama</label>
                    <input type="text" name="name" id="name" class="form-control" required maxlength="255" value="{{ old('name', $user?->name) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label req" for="email">Email (untuk login)</label>
                    <input type="email" name="email" id="email" class="form-control" required value="{{ old('email', $user?->email) }}" autocomplete="off">
                </div>
                <fieldset class="mb-3">
                    <legend class="form-label req">Level akun</legend>
                    @foreach ($roles as $role)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="role" id="role-{{ $role->value }}" value="{{ $role->value }}"
                                   @checked(old('role', $user?->role->value ?? 'user') === $role->value) @disabled($user && $user->id === auth()->id() && $user->role !== $role)>
                            <label class="form-check-label" for="role-{{ $role->value }}">
                                <strong>{{ $role->label() }}</strong>
                                <span class="d-block form-text mt-0">{{ $role->description() }}</span>
                            </label>
                        </div>
                    @endforeach
                </fieldset>
                {{-- Hak akses per menu --}}
                @php
                    $currentRole = old('role', $user?->role->value ?? 'user');
                    $checked = old('permissions', $user ? $user->permissionValues() : \App\Domain\Auth\Role::from($currentRole)->permissionValues());
                    $defaults = collect($roles)->mapWithKeys(fn ($r) => [$r->value => $r->permissionValues()]);
                @endphp
                <fieldset class="mb-4" id="permission-box" data-defaults='@json($defaults)'>
                    <legend class="form-label float-none">Hak akses per menu</legend>
                    <p class="form-text mt-0" id="permission-admin-note" @if ($currentRole !== 'admin') hidden @endif>
                        Admin selalu bisa membuka dan mengubah semua menu.
                    </p>
                    <div class="table-responsive" id="permission-table" @if ($currentRole === 'admin') hidden @endif>
                        <table class="table mb-1">
                            <thead><tr><th>Menu</th><th class="text-center">Lihat</th><th class="text-center">Kelola</th></tr></thead>
                            <tbody>
                            @foreach ($menus as $menu => [$view, $manage])
                                <tr>
                                    <td>{{ $menu }}</td>
                                    @foreach ([$view, $manage] as $permission)
                                        <td class="text-center">
                                            @if ($permission)
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->value }}"
                                                       aria-label="{{ $menu }}: {{ $loop->first ? 'lihat' : 'kelola' }}"
                                                       @if (! $loop->first && $view) data-needs="{{ $view->value }}" @endif
                                                       @checked(in_array($permission->value, $checked, true))>
                                            @else
                                                <span class="text-muted">–</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="form-text">Kelola = boleh menambah, mengubah, dan menghapus. Mencentang Kelola otomatis mencentang Lihat. Saat level akun diganti, centang kembali ke bawaan level itu.</div>
                    </div>
                </fieldset>

                <div class="form-check form-switch mb-4">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1"
                           @checked(old('is_active', $user?->is_active ?? true)) @disabled($user && $user->id === auth()->id())>
                    @if ($user && $user->id === auth()->id()) <input type="hidden" name="is_active" value="1"> @endif
                    <label class="form-check-label" for="is_active">Akun aktif (boleh login)</label>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label {{ $user ? '' : 'req' }}" for="password">{{ $user ? 'Kata sandi baru' : 'Kata sandi' }}</label>
                        <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" {{ $user ? '' : 'required' }} minlength="8">
                        <div class="form-text">{{ $user ? 'Kosongkan bila tidak diganti.' : 'Minimal 8 karakter.' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Ulangi kata sandi</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>
            </div>
            <div class="panel-foot d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ $user ? 'Simpan perubahan' : 'Buat akun' }}</button>
                <a href="{{ route('users.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    // Hak akses: ikut level akun yang dipilih, dan "Kelola" selalu membawa "Lihat".
    (function () {
        const box = document.getElementById('permission-box');
        const defaults = JSON.parse(box.dataset.defaults);
        const boxes = box.querySelectorAll('input[name="permissions[]"]');
        document.querySelectorAll('input[name="role"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                const isAdmin = radio.value === 'admin';
                document.getElementById('permission-table').hidden = isAdmin;
                document.getElementById('permission-admin-note').hidden = !isAdmin;
                boxes.forEach(function (cb) { cb.checked = defaults[radio.value].includes(cb.value); });
            });
        });
        boxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                if (cb.checked && cb.dataset.needs) {
                    box.querySelector('input[value="' + cb.dataset.needs + '"]').checked = true;
                }
                if (!cb.checked) {
                    box.querySelectorAll('input[data-needs="' + cb.value + '"]').forEach(function (m) { m.checked = false; });
                }
            });
        });
    })();
</script>
@endpush
@endsection
