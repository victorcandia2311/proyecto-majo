<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#111827">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · MAJO</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}?v={{ filemtime(public_path('assets/css/responsive.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="app-body" @if (request()->routeIs('dashboard.index', 'productos.index', 'inventario.index', 'ventas.index', 'empleados.index', 'proveedores.index', 'asistencia.index')) data-live-lists @endif>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    <div class="app-wrapper">
        <header class="main-header">
            <div class="header-inner d-flex align-items-center justify-content-between w-100">
                <div class="header-start d-flex align-items-center">
                <button class="sidebar-toggle" type="button" aria-controls="appSidebar" aria-expanded="false" aria-label="Mostrar navegación" data-sidebar-toggle>
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
                <a class="header-brand" href="{{ route(auth()->user()->homeRoute()) }}">
                    <i class="bi bi-box-seam-fill" aria-hidden="true"></i>
                    <span>Sistema de Inventario</span>
                </a>
                </div>
                <div class="header-account d-flex align-items-center ms-auto">
                    <div class="user-capsule">
                        <span class="user-avatar" aria-hidden="true">{{ Illuminate\Support\Str::upper(Illuminate\Support\Str::substr(trim(auth()->user()->nombres), 0, 1)) }}</span>
                        <div class="user-identity">
                            <span class="user-name" title="{{ auth()->user()->nombres }}">{{ auth()->user()->nombres }}</span>
                            <span class="user-role">{{ auth()->user()->isAdmin() ? 'Administrador' : 'Vendedor' }}</span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="header-logout" aria-label="Cerrar sesión" title="Cerrar sesión">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                            <span class="header-logout-label">Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>
        <aside class="main-sidebar" id="appSidebar" aria-label="Menú del sistema">
            <div class="sidebar-brand">
                <span class="brand-mark"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i></span>
                <span>MAJO</span>
                <button class="sidebar-close" type="button" aria-label="Cerrar navegación" data-sidebar-close>×</button>
            </div>
            <div class="sidebar-label">NAVEGACIÓN PRINCIPAL</div>
            <nav class="sidebar-nav" aria-label="Navegación principal">
                @foreach ([
                    ['dashboard', 'Dashboard', 'speedometer2'],
                    ['productos', 'Productos', 'box2'],
                    ['inventario', 'Inventario', 'clipboard-data'],
                    ['proveedores', 'Proveedores', 'truck'],
                    ['ventas', 'Ventas', 'receipt'],
                    ['empleados', 'Empleados', 'people'],
                    ['asistencia', 'Asistencia', 'calendar2-check'],
                ] as [$modulo, $etiqueta, $icono])
                    @continue(! auth()->user()->isAdmin() && $modulo !== 'ventas')
                    <a class="sidebar-link" href="{{ route($modulo.'.index') }}" @if(request()->routeIs($modulo.'.*')) aria-current="page" @endif>
                        <i class="bi bi-{{ $icono }}" aria-hidden="true"></i><span>{{ $etiqueta }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>
        <div class="sidebar-overlay" data-sidebar-close aria-hidden="true"></div>
        <main class="main-content" id="contenido" tabindex="-1">
            @unless (request()->routeIs('dashboard.index'))
                <div class="content-header">
                    <div class="content-header-inner">
                        <span class="content-kicker">GESTIÓN EMPRESARIAL</span>
                        <span class="content-path"><i class="bi bi-house-door" aria-hidden="true"></i> / @yield('title', 'Panel')</span>
                    </div>
                </div>
            @endunless
            <div class="content-body">
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <strong>No se pudo guardar. Revisa los siguientes datos:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('mensaje'))
                    <div class="alert alert-success" role="status">
                        {{ ['registrado' => 'Asistencia registrada correctamente.', 'actualizado' => 'Asistencia actualizada correctamente.', 'eliminado' => 'Asistencia eliminada correctamente.'][session('mensaje')] ?? session('mensaje') }}
                    </div>
                @endif
                @if (session('venta_exitosa'))
                    <div class="alert alert-success d-flex flex-wrap align-items-center gap-3" role="status">
                        <span>Venta #{{ session('venta_exitosa') }} registrada correctamente.</span>
                        <a href="{{ route('ventas.pdf', session('venta_exitosa')) }}" class="btn btn-success btn-sm">Descargar recibo PDF</a>
                    </div>
                @endif
                @yield('content')
            </div>
            <footer class="app-footer">MAJO · Sistema de inventario y ventas · Versión 1.01</footer>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
    <script src="{{ asset('js/input-limits.js') }}?v={{ filemtime(public_path('js/input-limits.js')) }}" defer></script>
    @if (request()->routeIs('dashboard.index', 'productos.index', 'inventario.index', 'ventas.index', 'empleados.index', 'proveedores.index', 'asistencia.index'))
        <script src="{{ asset('js/live-lists.js') }}?v={{ filemtime(public_path('js/live-lists.js')) }}" defer></script>
    @endif
    @stack('scripts')
</body>
</html>
