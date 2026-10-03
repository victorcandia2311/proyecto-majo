<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $inicioMes = now()->startOfMonth();
        $finMes = $inicioMes->copy()->addMonth();
        $mesActual = $inicioMes->format('Y-m');
        $mesAnterior = $inicioMes->copy()->subMonth()->format('Y-m');
        $resumen = ['cantidad_ventas' => 0, 'ingresos' => 0];
        $productosVendidos = ['productos_vendidos' => 0];
        $totalesPorMes = [];
        $unidadesPorMes = [];
        $metodos = [];

        foreach (Venta::select(['id_venta', 'fecha_hora', 'total_venta', 'metodo_pago'])
            ->withSum('detalles as unidades_vendidas', 'cantidad')->lazyById(500, 'id_venta') as $venta) {
            $mes = $venta->fecha_hora->format('Y-m');
            $importe = (float) $venta->total_venta;
            $unidades = (int) $venta->unidades_vendidas;
            $totalesPorMes[$mes] = ($totalesPorMes[$mes] ?? 0) + $importe;
            $unidadesPorMes[$mes] = ($unidadesPorMes[$mes] ?? 0) + $unidades;

            if ($mes === $mesActual) {
                $resumen['cantidad_ventas']++;
                $resumen['ingresos'] += $importe;
                $productosVendidos['productos_vendidos'] += $unidades;
                $metodo = $venta->metodo_pago;
                $metodos[$metodo] ??= ['metodo_pago' => $metodo, 'cantidad' => 0, 'total' => 0];
                $metodos[$metodo]['cantidad']++;
                $metodos[$metodo]['total'] += $importe;
            }
        }

        $actual = $totalesPorMes[$mesActual] ?? 0;
        $anterior = $totalesPorMes[$mesAnterior] ?? 0;
        $variacionVentas = [
            'ventas_actual' => $actual,
            'ventas_anterior' => $anterior,
            'variacion' => $anterior > 0 ? (($actual - $anterior) / $anterior) * 100 : ($actual > 0 ? 100 : 0),
        ];
        $ventasPorMes = collect($totalesPorMes)->sortKeys()
            ->map(fn (float|int $total, string $mes): array => compact('mes', 'total'))->values();
        $productosVendidosPorMes = collect($unidadesPorMes)->sortKeys()
            ->map(fn (int $unidades, string $mes): array => ['mes' => $mes, 'unidades_vendidas' => $unidades])->values();
        $ventasPorMetodo = collect($metodos)->map(fn (array $metodo): array => [
            ...$metodo,
            'porcentaje' => $actual > 0 ? round($metodo['total'] / $actual * 100, 2) : 0,
        ])->sortByDesc('total')->values();

        $ventasDelMes = fn (Builder $query): Builder => $query->whereHas(
            'venta',
            fn (Builder $query): Builder => $query->where('fecha_hora', '>=', $inicioMes)->where('fecha_hora', '<', $finMes),
        );
        $rankingProductos = Producto::whereHas('detallesVenta', $ventasDelMes)
            ->withSum(['detallesVenta as cantidad_vendida' => $ventasDelMes], 'cantidad')
            ->orderByDesc('cantidad_vendida')->orderBy('id_producto')->limit(5)->get();
        $productoMasVendido = $rankingProductos->first();
        $productosStockBajo = Producto::whereColumn('stock_actual', '<=', 'stock_minimo_alerta')
            ->orderBy('stock_actual')->get();
        $cantidadProductosStockBajo = $productosStockBajo->count();

        return view('dashboard.index', compact(
            'resumen', 'productosVendidos', 'productosVendidosPorMes', 'variacionVentas',
            'productoMasVendido', 'rankingProductos', 'ventasPorMes', 'ventasPorMetodo',
            'productosStockBajo', 'cantidadProductosStockBajo',
        ));
    }
}
