@extends('layouts.app')
@section('title', 'Pengguna')

@section('content')
<div class="page-head">
    <div>
        <h1>Pengguna</h1>
        <p class="sub">Akun yang bisa masuk ke sistem beserta levelnya. Akun nonaktif tidak bisa login, tetapi riwayat aktivitasnya tetap tersimpan.</p>
    </div>
    <div class="actions"><a href="{{ route('users.create') }}" class="btn btn-primary"><x-icon name="plus" width="16" height="16"/> Tambah akun</a></div>
</div>

<section class="panel mb-4">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Nama</th><th>Level</th><th>Status</th><th>Terakhir masuk</th><th></th></tr></thead>
            <tbody>
            @foreach ($users as $u)
                <tr>
                    <td><span class="fw-semibold">{{ $u->name }}</span> @if ($u->id === auth()->id()) <span class="pill pill-ok">Anda</span> @endif<span class="cell-sub">{{ $u->email }}</span></td>
                    <td>{{ $u->role->label() }}</td>
                    <td>@if ($u->is_active) <span class="pill pill-ok">aktif</span> @else <span class="pill pill-danger">nonaktif</span> @endif</td>
                    <td class="text-nowrap small">{{ $u->last_login_at?->translatedFormat('d M Y H:i') ?? 'Belum pernah' }}</td>
                    <td class="text-end"><a href="{{ route('users.edit', $u->id) }}" class="small">Ubah</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><h2>Hak akses per level</h2></div>
    <div class="table-responsive">
        <table class="table">
            <tbody>
            @foreach ($roles as $role)
                <tr><td class="fw-semibold text-nowrap" style="width: 12rem">{{ $role->label() }}</td><td>{{ $role->description() }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
