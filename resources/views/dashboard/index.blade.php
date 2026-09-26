@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-4"><h1 class="dashboard-title">Dashboard</h1><p class="text-muted mb-0">Resumen general del sistema de inventario.</p></div>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="card dashboard-card h-100"><div class="card-body">
        <h2 class="h6 text-muted">Ventas del mes</h2><p class="h3 mb-0">{{ $resumen['cantidad_ventas'] }}</p>
    </div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card dashboard-card h-100"><div class="card-body">
        <h2 class="h6 text-muted">Ingresos del mes</h2><p class="h3">S/ {{ number_format((float) $resumen['ingresos'], 2) }}</p>
        <p class="small mb-0 {{ $variacionVentas['variacion'] < 0 ? 'text-danger' : 'text-success' }}">
            {{ $variacionVentas['variacion'] > 0 ? '+' : '' }}{{ number_format((float) $variacionVentas['variacion'], 2) }}% respecto al mes anterior
        </p>
    </div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card dashboard-card h-100"><div class="card-body">
        <h2 class="h6 text-muted">Productos vendidos del mes</h2><p class="h3 mb-0">{{ $productosVendidos['productos_vendidos'] }}</p>
    </div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card dashboard-card h-100 border-warning"><div class="card-body">
        <h2 class="h6 text-muted">Productos con stock bajo</h2><p class="h3">{{ $cantidadProductosStockBajo }}</p>
        <p class="small mb-0">En el mínimo configurado o por debajo.</p>
    </div></div></div>
</div>
<div class="row g-4 mb-4">
    <div class="col-lg-6"><div class="card h-100">
        <div class="card-header"><h2 class="h6 mb-0">Ventas por mes</h2></div>
        <div class="card-body">
            @if ($ventasPorMes->isEmpty())
                <p class="text-muted mb-0">No hay ventas registradas.</p>
            @else
                <div class="chart-container"><canvas id="graficoVentas" role="img" aria-label="Ventas mensuales en soles. Valores disponibles en la tabla siguiente."></canvas></div>
                <details class="mt-3"><summary>Ver datos de ventas mensuales</summary>
                    <div class="table-responsive mt-2"><table class="table table-sm">
                        <thead><tr><th>Mes</th><th>Total (S/)</th></tr></thead>
                        <tbody>@foreach ($ventasPorMes as $mes)<tr><td>{{ $mes['mes'] }}</td><td>{{ number_format((float) $mes['total'], 2) }}</td></tr>@endforeach</tbody>
                    </table></div>
                </details>
            @endif
        </div>
    </div></div>
    <div class="col-lg-6"><div class="card h-100">
        <div class="card-header"><h2 class="h6 mb-0">Productos vendidos por mes</h2></div>
        <div class="card-body">
            @if ($productosVendidosPorMes->isEmpty())
                <p class="text-muted mb-0">No hay productos vendidos.</p>
            @else
                <div class="chart-container"><canvas id="graficoProductosVendidos" role="img" aria-label="Unidades vendidas por mes. Valores disponibles en la tabla siguiente."></canvas></div>
                <details class="mt-3"><summary>Ver unidades por mes</summary>
                    <div class="table-responsive mt-2"><table class="table table-sm">
                        <thead><tr><th>Mes</th><th>Unidades</th></tr></thead>
                        <tbody>@foreach ($productosVendidosPorMes as $mes)<tr><td>{{ $mes['mes'] }}</td><td>{{ $mes['unidades_vendidas'] }}</td></tr>@endforeach</tbody>
                    </table></div>
                </details>
            @endif
        </div>
    </div></div>
</div>
<div class="row g-4 mb-4">
    <div class="col-xl-6"><div class="card h-100">
        <div class="card-header"><h2 class="h6 mb-0">Alerta de stock bajo</h2></div>
        <div class="table-responsive"><table class="table table-bordered align-middle">
            <thead class="table-warning"><tr><th>Producto</th><th>Stock actual</th><th>Stock mínimo</th></tr></thead>
            <tbody>
                @forelse ($productosStockBajo as $producto)
                    <tr><td><a href="{{ route('productos.edit', $producto) }}">{{ $producto->nombre_producto }}</a></td><td>{{ $producto->stock_actual }}</td><td>{{ $producto->stock_minimo_alerta }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No hay productos con stock bajo.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="card-body"><a href="{{ route('inventario.index') }}" class="btn btn-outline-primary">Revisar inventario</a></div>
    </div></div>
    <div class="col-xl-6"><div class="card h-100">
        <div class="card-header"><h2 class="h6 mb-0">Ventas por método de pago</h2></div>
        @if ($ventasPorMetodo->isNotEmpty())
            <div class="card-body"><div class="chart-container"><canvas id="graficoMetodos" role="img" aria-label="Ventas por método de pago. Valores disponibles en la tabla siguiente."></canvas></div></div>
        @endif
        <div class="table-responsive"><table class="table table-bordered align-middle">
            <thead class="table-dark"><tr><th>Método</th><th>Cantidad</th><th>Total</th><th>Porcentaje</th></tr></thead>
            <tbody>
                @forelse ($ventasPorMetodo as $metodo)
                    <tr><td>{{ $metodo['metodo_pago'] }}</td><td>{{ $metodo['cantidad'] }}</td><td class="text-nowrap">S/ {{ number_format((float) $metodo['total'], 2) }}</td><td>{{ number_format((float) $metodo['porcentaje'], 2) }}%</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No hay ventas este mes.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div></div>
</div>
<div class="card mb-4"><div class="card-body">
    <h2 class="h6 text-muted">Producto más vendido del mes</h2>
    @if ($productoMasVendido)
        <p class="h4">{{ $productoMasVendido->nombre_producto }}</p><p class="mb-0">{{ $productoMasVendido->cantidad_vendida }} unidades vendidas</p>
    @else
        <p class="text-muted mb-0">No hay ventas este mes.</p>
    @endif
</div></div>
<div class="card">
    <div class="card-header"><h2 class="h6 mb-0">Top 3 productos más vendidos del mes</h2></div>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead class="table-dark"><tr><th>Puesto</th><th>Producto</th><th>Unidades vendidas</th></tr></thead>
        <tbody>
            @forelse ($rankingProductos as $producto)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $producto->nombre_producto }}</td><td>{{ $producto->cantidad_vendida }}</td></tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">No hay productos vendidos durante este mes.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>
@endsection

@push('scripts')
    <script>
        window.majooDashboard = {
            ventas: {{ Illuminate\Support\Js::from($ventasPorMes) }},
            productos: {{ Illuminate\Support\Js::from($productosVendidosPorMes) }},
            metodos: {{ Illuminate\Support\Js::from($ventasPorMetodo) }}
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script src="{{ asset('js/dashboard.js') }}" defer></script>
@endpush
