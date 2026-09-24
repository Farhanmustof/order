<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · {{ config('app.name') }}</title>
    @include('partials.head-assets')
</head>
<body class="d-grid" style="min-height: 100vh; place-items: center">
    <main class="text-center p-4" style="max-width: 460px">
        <div class="fw-bold" style="font-size: 4rem; color: var(--pandan); letter-spacing: -.04em">@yield('code')</div>
        <h1 class="h4 fw-bold">@yield('heading')</h1>
        <p class="text-muted">@yield('message')</p>
        <a href="{{ url('/') }}" class="btn btn-primary mt-2">Kembali ke beranda</a>
    </main>
</body>
</html>
