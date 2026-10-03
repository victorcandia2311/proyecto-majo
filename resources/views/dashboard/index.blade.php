@extends('layouts.app')

@section('title', 'Resumen General')

@section('content')
<div class="dashboard-page">
    <header class="dashboard-heading">
        <div>
            <p class="dashboard-eyebrow">MAJO <span aria-hidden="true">/</span> INDICADORES</p>
            <h1>Resumen General</h1>
        </div>
        <div class="dashboard-clock" aria-label="Fecha y hora de Perú">
            <span class="dashboard-clock-time" id="dashboard-clock-time">--:--:--</span>
            <time class="dashboard-clock-date" id="dashboard-clock-date">Cargando fecha…</time>
        </div>
    </header>

    <section class="dashboard-metrics" aria-label="Resumen del mes" data-live-region="dashboard-metrics">
        <article class="metric-card">
            <div class="metric-card-top"><span>Ingresos del Mes</span><span class="metric-icon metric-icon-indigo" aria-hidden="true">S/</span></div>
            <p class="metric-value">S/ {{ number_format((float) $resumen['ingresos'], 2) }}</p>
            <p class="metric-foot {{ $variacionVentas['variacion'] < 0 ? 'is-negative' : 'is-positive' }}">{{ $variacionVentas['variacion'] > 0 ? '+' : '' }}{{ number_format((float) $variacionVentas['variacion'], 1) }}% <span>vs. mes anterior</span></p>
        </article>
        <article class="metric-card">
            <div class="metric-card-top"><span>Ventas Realizadas</span><span class="metric-icon metric-icon-blue" aria-hidden="true"><i class="bi bi-receipt"></i></span></div>
            <p class="metric-value">{{ number_format((int) $resumen['cantidad_ventas']) }}</p>
            <p class="metric-foot">Durante el mes actual</p>
        </article>
        <article class="metric-card">
            <div class="metric-card-top"><span>Artículos Vendidos</span><span class="metric-icon metric-icon-green" aria-hidden="true"><i class="bi bi-box-seam"></i></span></div>
            <p class="metric-value">{{ number_format((int) $productosVendidos['productos_vendidos']) }}</p>
            <p class="metric-foot">Unidades despachadas este mes</p>
        </article>
        <a class="metric-card metric-stock {{ $cantidadProductosStockBajo > 0 ? 'has-alerts' : 'no-alerts' }}" href="{{ route('inventario.index') }}">
            <div class="metric-card-top"><span>Alertas de Stock</span><span class="metric-icon" aria-hidden="true"><i class="bi {{ $cantidadProductosStockBajo > 0 ? 'bi-exclamation-triangle' : 'bi-check-circle' }}"></i></span></div>
            <p class="metric-value">{{ number_format($cantidadProductosStockBajo) }}</p>
            <p class="metric-foot">{{ $cantidadProductosStockBajo > 0 ? 'Productos por reponer' : 'Inventario al día' }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i></p>
        </a>
    </section>

    <section class="dashboard-charts" aria-label="Gráficos de ventas">
        <article class="dashboard-panel dashboard-panel-wide">
            <header class="dashboard-panel-heading">
                <div><p class="dashboard-panel-kicker">TENDENCIA</p><h2>Evolución de Ingresos</h2></div>
                <span class="chart-period"><i aria-hidden="true"></i> Historial</span>
            </header>
            <p class="chart-demo-note" data-demo-note="income" hidden>Datos ilustrativos: aún no hay ingresos registrados.</p>
            <div class="dashboard-chart dashboard-chart-main"><canvas id="graficoIngresos" role="img" aria-label="Gráfico de área con la evolución mensual de ingresos en soles"></canvas></div>
        </article>

        <article class="dashboard-panel">
            <header class="dashboard-panel-heading">
                <div><p class="dashboard-panel-kicker">DISTRIBUCIÓN</p><h2>Métodos de Pago</h2></div>
            </header>
            <p class="chart-demo-note" data-demo-note="payments" hidden>Datos ilustrativos mientras se registran ventas.</p>
            <div class="dashboard-chart dashboard-chart-donut"><canvas id="graficoMetodos" role="img" aria-label="Gráfico de anillo con la distribución por método de pago"></canvas></div>
        </article>

        <article class="dashboard-panel">
            <header class="dashboard-panel-heading">
                <div><p class="dashboard-panel-kicker">UNIDADES DEL MES</p><h2>Top 5 Productos Más Vendidos</h2></div>
            </header>
            <p class="chart-demo-note" data-demo-note="products" hidden>Datos ilustrativos mientras se registran ventas.</p>
            <div class="dashboard-chart dashboard-chart-bars"><canvas id="graficoProductos" role="img" aria-label="Gráfico de barras horizontales con los cinco productos más vendidos y sus cantidades"></canvas></div>
        </article>
    </section>

    <section class="stock-followup" aria-labelledby="stock-followup-title" data-live-region="dashboard-stock">
        <div class="stock-followup-heading">
            <div><p class="dashboard-panel-kicker">INVENTARIO</p><h2 id="stock-followup-title">Productos que requieren reposición</h2></div>
            <a href="{{ route('inventario.index') }}">Ver inventario <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        @if ($productosStockBajo->isNotEmpty())
            <ul class="stock-alert-list">
                @foreach ($productosStockBajo->take(5) as $producto)
                    <li><a href="{{ route('productos.edit', $producto) }}"><span>{{ $producto->nombre_producto }}</span><strong>{{ $producto->stock_actual }} <small>en stock</small></strong></a></li>
                @endforeach
            </ul>
        @else
            <p class="text-muted mb-0">No hay productos con alertas de stock.</p>
        @endif
    </section>
</div>
@endsection

@push('scripts')
    <script id="dashboard-chart-data" type="application/json">{!! Illuminate\Support\Js::encode([
        'ventas' => $ventasPorMes,
        'metodos' => $ventasPorMetodo,
        'ranking' => $rankingProductos->map(fn ($producto): array => ['nombre' => $producto->nombre_producto, 'cantidad' => (int) $producto->cantidad_vendida])->values(),
    ]) !!}</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js" defer></script>
    <script src="{{ asset('js/dashboard.js') }}?v={{ filemtime(public_path('js/dashboard.js')) }}" defer></script>
@endpush
