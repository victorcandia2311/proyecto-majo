<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f1e7">
    <title>@yield('title', 'Acceso') · ROMA CLUB</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <script src="{{ asset('js/theme.js') }}?v={{ filemtime(public_path('js/theme.js')) }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/theme.css') }}?v={{ filemtime(public_path('css/theme.css')) }}" rel="stylesheet">
    <script src="{{ asset('js/auth.js') }}?v={{ filemtime(public_path('js/auth.js')) }}" defer></script>
    <script src="{{ asset('js/input-limits.js') }}?v={{ filemtime(public_path('js/input-limits.js')) }}" defer></script>
</head>
<body class="app-body auth-page">
    <main class="auth-shell">
        <section class="auth-card" aria-labelledby="auth-title">
            <div class="theme-control auth-theme-control">
                <label for="theme-selector">
                    <i class="bi bi-circle-half" aria-hidden="true"></i>
                    <span>Apariencia</span>
                </label>
                <select
                    id="theme-selector"
                    class="theme-select"
                    aria-label="Tema visual"
                    data-theme-selector
                    data-native-select
                >
                    <option value="light">Claro Imperial</option>
                    <option value="dark">Oscuro Imperial</option>
                    <option value="auto">Automático</option>
                </select>
                <span class="visually-hidden" data-theme-status role="status" aria-live="polite"></span>
            </div>
            <header class="auth-brand">
                <div class="auth-brand-identity">
                    <div class="auth-logo" aria-hidden="true">
                        <img
                            src="{{ asset('images/logo_roma.png') }}?v={{ filemtime(public_path('images/logo_roma.png')) }}"
                            alt=""
                            class="auth-logo-image"
                        >
                    </div>
                    <div class="auth-brand-copy">
                        <div class="auth-wordmark">ROMA CLUB</div>
                        <p class="auth-brand-tagline">PRIVÉ &amp; MANAGEMENT</p>
                    </div>
                </div>
                <p class="auth-brand-description">Sistema de inventario y ventas</p>
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
            <footer class="auth-footer"><i class="bi bi-shield-lock" aria-hidden="true"></i> ROMA CLUB · Versión 2.01</footer>
        </section>
    </main>
</body>
</html>