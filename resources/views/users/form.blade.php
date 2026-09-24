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
@endsection
