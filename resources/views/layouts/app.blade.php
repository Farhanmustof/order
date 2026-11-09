<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') · {{ config('app.name') }}</title>
    @include('partials.head-assets')
</head>
<body>
<div class="app">
    <aside class="sidebar" aria-label="Menu utama">
        <a href="{{ route('dashboard') }}" class="brand">
            <strong>{{ config('app.name') }}</strong>
            <span>Pesanan, produksi &amp; stok bahan</span>
        </a>

        @php $is = fn (string ...$p) => request()->routeIs(...$p) ? 'active' : ''; @endphp

        <nav class="nav-group">
            <a href="{{ route('dashboard') }}" class="side-link {{ $is('dashboard') }}"><x-icon name="home"/> Beranda</a>
            @can('pre-order.lihat')
                <a href="{{ route('pre-orders.index') }}" class="side-link {{ $is('pre-orders.*') }}">
                    <x-icon name="order"/> Pre-order
                    @if (($navCounts['overdue'] ?? 0) > 0)
                        <span class="count" title="Pre-order lewat jatuh tempo">{{ $navCounts['overdue'] }}</span>
                    @endif
                </a>
            @endcan
            @can('produksi.lihat')
                <a href="{{ route('production.index') }}" class="side-link {{ $is('production.*') }}"><x-icon name="oven"/> Rencana produksi</a>
            @endcan
            @can('stok.lihat')
                <a href="{{ route('inventory.index') }}" class="side-link {{ $is('inventory.index', 'inventory.show', 'stock-in.*', 'stock-out.*', 'materials.*') }}"><x-icon name="box"/> Stok bahan baku</a>
            @endcan
            @can('pembelian.lihat')
                <a href="{{ route('purchases.index') }}" class="side-link {{ $is('purchases.*') }}"><x-icon name="truck"/> Pemesanan supplier</a>
            @endcan
        </nav>

        @canany(['produk.lihat', 'supplier.lihat', 'stok.lihat'])
            <nav class="nav-group">
                <div class="nav-group-title">Data pendukung</div>
                @can('produk.lihat')
                    <a href="{{ route('products.index') }}" class="side-link {{ $is('products.*') }}"><x-icon name="recipe"/> Produk &amp; resep</a>
                @endcan
                @can('supplier.lihat')
                    <a href="{{ route('suppliers.index') }}" class="side-link {{ $is('suppliers.*') }}"><x-icon name="truck"/> Supplier</a>
                @endcan
                @can('stok.lihat')
                    <a href="{{ route('inventory.movements') }}" class="side-link {{ $is('inventory.movements') }}"><x-icon name="history"/> Riwayat stok</a>
                @endcan
            </nav>
        @endcanany

        @canany(['pengguna.kelola', 'riwayat-aktivitas.lihat', 'data-terhapus.kelola'])
            <nav class="nav-group">
                <div class="nav-group-title">Pengawasan</div>
                @can('pengguna.kelola')
                    <a href="{{ route('users.index') }}" class="side-link {{ $is('users.*') }}"><x-icon name="users"/> Pengguna</a>
                @endcan
                @can('riwayat-aktivitas.lihat')
                    <a href="{{ route('audit.index') }}" class="side-link {{ $is('audit.*') }}"><x-icon name="shield"/> Riwayat aktivitas</a>
                @endcan
                @can('data-terhapus.kelola')
                    <a href="{{ route('trash.index') }}" class="side-link {{ $is('trash.*') }}"><x-icon name="trash"/> Data terhapus</a>
                @endcan
            </nav>
        @endcanany

        <div class="me">
            <div class="fw-semibold text-white">{{ auth()->user()->name }}</div>
            <div class="role mb-2">{{ auth()->user()->role->label() }}</div>
            <a href="{{ route('profile.edit') }}" class="me-3">Akun saya</a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link p-0 align-baseline" style="font-size: inherit">Keluar</button>
            </form>
        </div>
    </aside>

    <div class="main">
        <div class="topbar">
            <button type="button" data-toggle-sidebar aria-label="Buka menu"><x-icon name="menu" width="24" height="24"/></button>
            <strong>{{ config('app.name') }}</strong>
        </div>

        <main class="content">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Periksa kembali isian berikut:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach (collect($errors->all())->unique() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
@stack('scripts')
</body>
</html>
