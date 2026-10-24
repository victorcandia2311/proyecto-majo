@extends('layouts.app')

@section('title', 'Resumen General')

@section('content')
<div class="dashboard-page" data-dashboard-endpoint="{{ route('dashboard.data') }}">
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

    <div class="alert alert-danger" id="dashboard-error" role="alert" hidden>
        No se pudieron cargar los indicadores. <button type="button" class="btn btn-link p-0 align-baseline" data-dashboard-retry>Intentar de nuevo</button>
    </div>

    <section class="dashboard-metrics" aria-label="Resumen del mes" aria-busy="true" data-dashboard-metrics>
        <article class="metric-card">
            <div class="metric-card-top"><span>Ingresos del Mes</span><span class="metric-icon metric-icon-indigo" aria-hidden="true">S/</span></div>
            <p class="metric-value" data-dashboard-value="ingresos"><span class="dashboard-skeleton"></span></p>
            <p class="metric-foot" data-dashboard-variation><span class="dashboard-skeleton dashboard-skeleton-short"></span></p>
            <svg class="metric-sparkline" data-dashboard-sparkline="ingresos" viewBox="0 0 120 32" aria-hidden="true"></svg>
        </article>
        <article class="metric-card">
            <div class="metric-card-top"><span>Ventas Realizadas</span><span class="metric-icon metric-icon-blue" aria-hidden="true"><i class="bi bi-receipt"></i></span></div>
            <p class="metric-value" data-dashboard-value="ventas"><span class="dashboard-skeleton"></span></p>
            <p class="metric-foot">Durante el mes actual</p>
            <svg class="metric-sparkline" data-dashboard-sparkline="ventas" viewBox="0 0 120 32" aria-hidden="true"></svg>
        </article>
        <article class="metric-card">
            <div class="metric-card-top"><span>Artículos Vendidos</span><span class="metric-icon metric-icon-green" aria-hidden="true"><i class="bi bi-box-seam"></i></span></div>
            <p class="metric-value" data-dashboard-value="productos"><span class="dashboard-skeleton"></span></p>
            <p class="metric-foot">Unidades despachadas este mes</p>
            <svg class="metric-sparkline" data-dashboard-sparkline="productos" viewBox="0 0 120 32" aria-hidden="true"></svg>
        </article>
        <a class="metric-card metric-stock no-alerts" href="{{ route('inventario.index') }}">
            <div class="metric-card-top"><span>Alertas de Stock</span><span class="metric-icon" data-stock-icon aria-hidden="true"><i class="bi bi-check-circle"></i></span></div>
            <p class="metric-value" data-dashboard-value="stock"><span class="dashboard-skeleton"></span></p>
            <p class="metric-foot" data-dashboard-stock-label>Cargando inventario…</p>
        </a>
    </section>

    <section class="dashboard-charts" aria-label="Gráficos de ventas">
        <article class="dashboard-panel dashboard-panel-wide">
            <header class="dashboard-panel-heading">
                <div><p class="dashboard-panel-kicker">TENDENCIA</p><h2>Evolución de Ingresos</h2></div>
                <span class="chart-period"><i aria-hidden="true"></i> Últimos 12 meses</span>
            </header>
            <div class="dashboard-chart dashboard-chart-main is-loading" data-dashboard-chart>
                <span class="chart-skeleton" aria-hidden="true"></span>
                <canvas id="graficoIngresos" role="img" aria-label="Gráfico de área con la evolución mensual de ingresos en soles"></canvas>
                <p class="chart-empty" data-dashboard-empty="income" hidden>No hay ventas registradas en este periodo.</p>
            </div>
        </article>

        <article class="dashboard-panel">
            <header class="dashboard-panel-heading">
                <div><p class="dashboard-panel-kicker">DISTRIBUCIÓN</p><h2>Métodos de Pago</h2></div>
            </header>
            <div class="dashboard-chart dashboard-chart-donut is-loading" data-dashboard-chart>
                <span class="chart-skeleton" aria-hidden="true"></span>
                <canvas id="graficoMetodos" role="img" aria-label="Gráfico de anillo con la distribución por método de pago"></canvas>
                <p class="chart-empty" data-dashboard-empty="payments" hidden>No hay pagos registrados este mes.</p>
            </div>
        </article>

        <article class="dashboard-panel">
            <header class="dashboard-panel-heading">
                <div><p class="dashboard-panel-kicker">UNIDADES DEL MES</p><h2>Top 5 Productos Más Vendidos</h2></div>
            </header>
            <div class="dashboard-chart dashboard-chart-bars is-loading" data-dashboard-chart>
                <span class="chart-skeleton" aria-hidden="true"></span>
                <canvas id="graficoProductos" role="img" aria-label="Gráfico de barras horizontales de productos vendidos"></canvas>
                <p class="chart-empty" data-dashboard-empty="products" hidden>No hay productos vendidos este mes.</p>
            </div>
        </article>
    </section>

    <section class="stock-followup" aria-labelledby="stock-followup-title">
        <div class="stock-followup-heading">
            <div><p class="dashboard-panel-kicker">INVENTARIO</p><h2 id="stock-followup-title">Productos que requieren reposición</h2></div>
            <a href="{{ route('inventario.index') }}">Ver inventario <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        <ul class="stock-alert-list" id="dashboard-stock-list" aria-live="polite" data-product-edit-pattern="{{ route('productos.edit', ['producto' => 'PRODUCT_ID']) }}">
            <li class="stock-skeleton-row"><span class="dashboard-skeleton"></span><span class="dashboard-skeleton dashboard-skeleton-short"></span></li>
            <li class="stock-skeleton-row"><span class="dashboard-skeleton"></span><span class="dashboard-skeleton dashboard-skeleton-short"></span></li>
            <li class="stock-skeleton-row"><span class="dashboard-skeleton"></span><span class="dashboard-skeleton dashboard-skeleton-short"></span></li>
        </ul>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js" defer></script>
<script src="{{ asset('js/dashboard.js') }}?v={{ filemtime(public_path('js/dashboard.js')) }}" defer></script>
@endpush
