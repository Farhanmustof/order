<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · {{ config('app.name') }}</title>
    @include('partials.head-assets')
</head>
<body>
<div class="auth">
    <section class="auth-side">
        <div>
            <div class="fw-semibold mb-4" style="color:#9DBBA9">{{ config('app.name') }}</div>
            <h1>Dari pesanan masuk sampai bahan terpakai, tercatat di satu tempat.</h1>
            <ol class="auth-flow">
                <li><b>1</b><span>Catat pre-order pelanggan beserta DP-nya.</span></li>
                <li><b>2</b><span>Gabungkan pesanan ke rencana produksi dan cek kecukupan bahan.</span></li>
                <li><b>3</b><span>Produksi selesai, stok bahan terpotong otomatis, yang kedaluwarsa terdekat dipakai lebih dulu.</span></li>
            </ol>
        </div>
        <p class="small mb-0">Lupa kata sandi? Minta admin untuk mengaturnya ulang.</p>
    </section>

    <section class="auth-form">
        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            <h2 class="h4 fw-bold mb-1">Masuk</h2>
            <p class="text-muted mb-4">Gunakan email dan kata sandi dari admin.</p>

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Kata sandi</label>
                <input type="password" name="password" id="password" required autocomplete="current-password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
        </form>
    </section>
</div>
</body>
</html>
