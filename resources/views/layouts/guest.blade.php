<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#060c1b">
    <title>@yield('title', 'Acceso') · MAJO</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    <script src="{{ asset('js/auth.js') }}?v={{ filemtime(public_path('js/auth.js')) }}" defer></script>
    <script src="{{ asset('js/input-limits.js') }}?v={{ filemtime(public_path('js/input-limits.js')) }}" defer></script>
</head>
<body class="app-body auth-page">
    <main class="auth-shell">
        <section class="auth-card" aria-labelledby="auth-title">
            <header class="auth-brand">
                <div class="auth-logo" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none">
                        <defs><linearGradient id="majo-gradient" x1="7" y1="7" x2="42" y2="43" gradientUnits="userSpaceOnUse"><stop stop-color="#67e8f9"/><stop offset="1" stop-color="#3b82f6"/></linearGradient></defs>
                        <path d="M7 36V13l17 12 17-12v23M7 13l17-8 17 8M24 25v18" stroke="url(#majo-gradient)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="auth-wordmark">MAJO</div>
                <p>Sistema de inventario y ventas</p>
            </header>
            <div class="auth-content">
                <h1 id="auth-title">@yield('title')</h1>
                @if (session('status'))
                    <div class="auth-notice auth-success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i><span>{{ session('status') }}</span></div>
                @endif
                @if ($errors->any())
                    <div class="auth-errors" role="alert" aria-label="Revisa los datos ingresados">
                        @foreach ($errors->all() as $error)
                            <div class="auth-notice auth-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i><span>{{ $error }}</span></div>
                        @endforeach
                    </div>
                @endif
                @yield('content')
            </div>
            <footer class="auth-footer"><i class="bi bi-shield-lock" aria-hidden="true"></i> MAJO · Versión 1.01</footer>
        </section>
    </main>
</body>
</html>
