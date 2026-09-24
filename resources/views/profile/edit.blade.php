@extends('layouts.app')
@section('title', 'Akun saya')

@section('content')
<div class="page-head">
    <div>
        <h1>Akun saya</h1>
        <p class="sub">{{ $user->name }} · {{ $user->email }} · {{ $user->role->label() }}</p>
    </div>
</div>
<div class="row">
    <div class="col-lg-6 col-xl-5">
        <form method="POST" action="{{ route('profile.password') }}" class="panel">
            @csrf @method('PUT')
            <div class="panel-head"><h2>Ganti kata sandi</h2></div>
            <div class="panel-body">
                <div class="mb-3">
                    <label class="form-label req" for="current_password">Kata sandi saat ini</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" required autocomplete="current-password">
                </div>
                <div class="mb-3">
                    <label class="form-label req" for="password">Kata sandi baru</label>
                    <input type="password" name="password" id="password" class="form-control" required minlength="8" autocomplete="new-password">
                    <div class="form-text">Minimal 8 karakter.</div>
                </div>
                <div>
                    <label class="form-label req" for="password_confirmation">Ulangi kata sandi baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
            </div>
            <div class="panel-foot"><button type="submit" class="btn btn-primary">Ganti kata sandi</button></div>
        </form>
    </div>
</div>
@endsection
