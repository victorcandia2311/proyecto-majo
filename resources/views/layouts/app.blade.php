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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}?v={{ filemtime(public_path('assets/css/responsive.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="app-body" @if (request()->routeIs('productos.index', 'inventario.index', 'ventas.index', 'empleados.index', 'proveedores.index', 'asistencia.index')) data-live-lists @endif>
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

            <nav class="sidebar-nav" aria-label="Navegación principal">

                {{-- ── 1. Principal ── --}}
                <div class="sidebar-section-label">PRINCIPAL</div>
                <a class="sidebar-link" href="{{ route('dashboard.index') }}"
                   @if(request()->routeIs('dashboard.*')) aria-current="page" @endif>
                    <i class="bi bi-speedometer2" aria-hidden="true"></i>
                    <span>Dashboard</span>
                </a>

                @if(auth()->user()->isAdmin())

                {{-- ── 2. Gestión de Inventario ── --}}
                @php
                    $inInventario = request()->routeIs('productos.*', 'inventario.*', 'proveedores.*');
                @endphp
                <div class="sidebar-section-label">INVENTARIO</div>
                <button
                    class="sidebar-link sidebar-group-toggle {{ $inInventario ? '' : 'collapsed' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#nav-inventario"
                    aria-expanded="{{ $inInventario ? 'true' : 'false' }}"
                    aria-controls="nav-inventario"
                >
                    <i class="bi bi-box-seam" aria-hidden="true"></i>
                    <span>Gestión de Inventario</span>
                    <i class="bi bi-chevron-down sidebar-chevron ms-auto" aria-hidden="true"></i>
                </button>
                <div class="collapse {{ $inInventario ? 'show' : '' }}" id="nav-inventario">
                    <div class="sidebar-submenu">
                        <a class="sidebar-sublink" href="{{ route('productos.index') }}"
                           @if(request()->routeIs('productos.*')) aria-current="page" @endif>
                            <i class="bi bi-box2" aria-hidden="true"></i>
                            <span>Productos</span>
                        </a>
                        <a class="sidebar-sublink" href="{{ route('inventario.index') }}"
                           @if(request()->routeIs('inventario.*')) aria-current="page" @endif>
                            <i class="bi bi-clipboard-data" aria-hidden="true"></i>
                            <span>Inventario</span>
                        </a>
                        <a class="sidebar-sublink" href="{{ route('proveedores.index') }}"
                           @if(request()->routeIs('proveedores.*')) aria-current="page" @endif>
                            <i class="bi bi-truck" aria-hidden="true"></i>
                            <span>Proveedores</span>
                        </a>
                    </div>
                </div>

                {{-- ── 3. Recursos Humanos ── --}}
                @php
                    $inRRHH = request()->routeIs('empleados.*', 'asistencia.*');
                @endphp
                <div class="sidebar-section-label">PERSONAS</div>
                <button
                    class="sidebar-link sidebar-group-toggle {{ $inRRHH ? '' : 'collapsed' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#nav-rrhh"
                    aria-expanded="{{ $inRRHH ? 'true' : 'false' }}"
                    aria-controls="nav-rrhh"
                >
                    <i class="bi bi-people" aria-hidden="true"></i>
                    <span>Recursos Humanos</span>
                    <i class="bi bi-chevron-down sidebar-chevron ms-auto" aria-hidden="true"></i>
                </button>
                <div class="collapse {{ $inRRHH ? 'show' : '' }}" id="nav-rrhh">
                    <div class="sidebar-submenu">
                        <a class="sidebar-sublink" href="{{ route('empleados.index') }}"
                           @if(request()->routeIs('empleados.*')) aria-current="page" @endif>
                            <i class="bi bi-person-badge" aria-hidden="true"></i>
                            <span>Empleados</span>
                        </a>
                        <a class="sidebar-sublink" href="{{ route('asistencia.index') }}"
                           @if(request()->routeIs('asistencia.*')) aria-current="page" @endif>
                            <i class="bi bi-calendar2-check" aria-hidden="true"></i>
                            <span>Asistencia</span>
                        </a>
                    </div>
                </div>

                @endif {{-- /isAdmin --}}

                {{-- ── 4. Comercial (visible para todos) ── --}}
                @php
                    $inComercial = request()->routeIs('ventas.*');
                @endphp
                <div class="sidebar-section-label">COMERCIAL</div>
                <button
                    class="sidebar-link sidebar-group-toggle {{ $inComercial ? '' : 'collapsed' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#nav-comercial"
                    aria-expanded="{{ $inComercial ? 'true' : 'false' }}"
                    aria-controls="nav-comercial"
                >
                    <i class="bi bi-graph-up-arrow" aria-hidden="true"></i>
                    <span>Comercial</span>
                    <i class="bi bi-chevron-down sidebar-chevron ms-auto" aria-hidden="true"></i>
                </button>
                <div class="collapse {{ $inComercial ? 'show' : '' }}" id="nav-comercial">
                    <div class="sidebar-submenu">
                        <a class="sidebar-sublink" href="{{ route('ventas.nueva') }}"
                           @if(request()->routeIs('ventas.nueva', 'ventas.pos')) aria-current="page" @endif>
                            <i class="bi bi-cart-plus" aria-hidden="true"></i>
                            <span>Punto de Venta</span>
                        </a>
                        <a class="sidebar-sublink" href="{{ route('ventas.index') }}"
                           @if(request()->routeIs('ventas.index', 'ventas.show')) aria-current="page" @endif>
                            <i class="bi bi-receipt" aria-hidden="true"></i>
                            <span>Historial de Ventas</span>
                        </a>
                    </div>
                </div>

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
                @yield('content')
            </div>
            <footer class="app-footer">MAJO · Sistema de inventario y ventas · Versión 1.01</footer>
        </main>
    </div>
    <script id="app-feedback-data" type="application/json">{!! Illuminate\Support\Js::encode([
        'errors' => $errors->all(),
        'success' => session('feedback_success') ?? (session('mensaje') ? ([
            'registrado' => 'Asistencia registrada correctamente.',
            'actualizado' => 'Asistencia actualizada correctamente.',
            'eliminado' => 'Asistencia eliminada correctamente.',
            'temporal_registrado' => 'Empleado temporal registrado correctamente.',
            'temporal_ingreso' => 'Ingreso del empleado temporal registrado correctamente.',
            'temporal_salida' => 'Salida del empleado temporal registrada correctamente.',
            'producto_eliminado' => 'El producto se quitó de la lista. Se conservó su historial de ventas e inventario.',
        ][session('mensaje')] ?? session('mensaje')) : null),
        'sale' => session('venta_exitosa') ? [
            'id' => session('venta_exitosa'),
            'receiptUrl' => route('ventas.pdf', session('venta_exitosa')),
        ] : null,
    ]) !!}</script>
    <div class="modal fade" id="confirm-action-modal" tabindex="-1" aria-labelledby="confirm-action-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="confirm-action-title"><i class="bi bi-exclamation-triangle-fill text-warning me-2" aria-hidden="true"></i>¿Confirmas esta acción?</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" data-confirm-message></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" data-confirm-submit>Confirmar</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" defer></script>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
    <script src="{{ asset('js/feedback.js') }}?v={{ filemtime(public_path('js/feedback.js')) }}" defer></script>
    <script src="{{ asset('js/input-limits.js') }}?v={{ filemtime(public_path('js/input-limits.js')) }}" defer></script>
    @if (request()->routeIs('productos.index', 'inventario.index', 'ventas.index', 'empleados.index', 'proveedores.index', 'asistencia.index'))
        <script src="{{ asset('js/live-lists.js') }}?v={{ filemtime(public_path('js/live-lists.js')) }}" defer></script>
    @endif
    @stack('scripts')
</body>
</html>
