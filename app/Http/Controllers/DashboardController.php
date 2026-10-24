<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.index');
    }

    public function data(): JsonResponse
    {
        $inicioMes = now()->startOfMonth();
        $finMes = $inicioMes->copy()->addMonth();
        $inicioHistorial = $inicioMes->copy()->subMonths(11);
        $mesActual = $inicioMes->format('Y-m');
        $mesAnterior = $inicioMes->copy()->subMonth()->format('Y-m');
        $monthExpression = match (DB::connection()->getDriverName()) {
            'pgsql' => "to_char(venta.fecha_hora, 'YYYY-MM')",
            'mysql' => "DATE_FORMAT(venta.fecha_hora, '%Y-%m')",
            default => "strftime('%Y-%m', venta.fecha_hora)",
        };

        $monthlySales = Venta::query()
            ->where('venta.fecha_hora', '>=', $inicioHistorial)
            ->where('venta.fecha_hora', '<', $finMes)
            ->selectRaw("$monthExpression as mes, SUM(venta.total_venta) as total, COUNT(*) as cantidad")
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');
        $monthlyUnits = Venta::query()
            ->join('detalle_venta', 'detalle_venta.id_venta', '=', 'venta.id_venta')
            ->where('venta.fecha_hora', '>=', $inicioHistorial)
            ->where('venta.fecha_hora', '<', $finMes)
            ->selectRaw("$monthExpression as mes, SUM(detalle_venta.cantidad) as unidades")
            ->groupBy('mes')
            ->pluck('unidades', 'mes')
            ->map(fn (string|int|float $units): int => (int) $units);
        $months = collect(range(0, 11))->map(function (int $offset) use ($inicioHistorial, $monthlySales, $monthlyUnits): array {
            $month = $inicioHistorial->copy()->addMonths($offset)->format('Y-m');
            $monthlySale = $monthlySales->get($month);

            return [
                'mes' => $month,
                'total' => (float) ($monthlySale?->total ?? 0),
                'cantidad_ventas' => (int) ($monthlySale?->cantidad ?? 0),
                'unidades_vendidas' => $monthlyUnits->get($month, 0),
            ];
        });
        $actual = (float) ($monthlySales->get($mesActual)?->total ?? 0);
        $anterior = (float) ($monthlySales->get($mesAnterior)?->total ?? 0);
        $metodos = Venta::query()
            ->where('fecha_hora', '>=', $inicioMes)
            ->where('fecha_hora', '<', $finMes)
            ->selectRaw('metodo_pago, COUNT(*) as cantidad, SUM(total_venta) as total')
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get()
            ->map(fn (Venta $venta): array => [
                'metodo_pago' => $venta->metodo_pago,
                'cantidad' => (int) $venta->cantidad,
                'total' => (float) $venta->total,
                'porcentaje' => $actual > 0 ? round((float) $venta->total / $actual * 100, 2) : 0,
            ]);
        $ventasDelMes = fn (Builder $query): Builder => $query->whereHas(
            'venta',
            fn (Builder $query): Builder => $query->where('fecha_hora', '>=', $inicioMes)->where('fecha_hora', '<', $finMes),
        );
        $rankingProductos = Producto::whereHas('detallesVenta', $ventasDelMes)
            ->withSum(['detallesVenta as cantidad_vendida' => $ventasDelMes], 'cantidad')
            ->orderByDesc('cantidad_vendida')
            ->orderBy('id_producto')
            ->limit(5)
            ->get(['id_producto', 'nombre_producto'])
            ->map(fn (Producto $producto): array => [
                'id' => $producto->getKey(),
                'nombre' => $producto->nombre_producto,
                'cantidad' => (int) $producto->cantidad_vendida,
            ]);
        $stockBajo = Producto::whereColumn('stock_actual', '<=', 'stock_minimo_alerta')
            ->orderBy('stock_actual')
            ->limit(5)
            ->get(['id_producto', 'nombre_producto', 'stock_actual']);

        return response()->json([
            'resumen' => [
                'cantidad_ventas' => Venta::where('fecha_hora', '>=', $inicioMes)->where('fecha_hora', '<', $finMes)->count(),
                'ingresos' => $actual,
                'productos_vendidos' => (int) $monthlyUnits->get($mesActual, 0),
            ],
            'variacion' => [
                'actual' => $actual,
                'anterior' => $anterior,
                'porcentaje' => $anterior > 0 ? (($actual - $anterior) / $anterior) * 100 : ($actual > 0 ? 100 : 0),
            ],
            'meses' => $months,
            'metodos' => $metodos,
            'ranking' => $rankingProductos,
            'stock_bajo' => $stockBajo->map(fn (Producto $producto): array => [
                'id' => $producto->getKey(),
                'nombre' => $producto->nombre_producto,
                'stock' => $producto->stock_actual,
            ]),
            'cantidad_stock_bajo' => Producto::whereColumn('stock_actual', '<=', 'stock_minimo_alerta')->count(),
        ]);
    }
}
