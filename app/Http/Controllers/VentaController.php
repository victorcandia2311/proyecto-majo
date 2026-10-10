<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'fecha_desde' => ['nullable', 'date_format:Y-m-d'],
            'fecha_hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_desde'],
        ]);
        $productos = Producto::where('stock_actual', '>', 0)->orderBy('nombre_producto')->get();
        $fechaDesde = $filtros['fecha_desde'] ?? null;
        $fechaHasta = $filtros['fecha_hasta'] ?? null;
        $ventasQuery = Venta::query()
            ->when($fechaDesde, fn ($query) => $query->whereDate('fecha_hora', '>=', $fechaDesde))
            ->when($fechaHasta, fn ($query) => $query->whereDate('fecha_hora', '<=', $fechaHasta));
        $filtrandoPorFecha = $fechaDesde !== null || $fechaHasta !== null;
        $totalVentasFiltradas = $filtrandoPorFecha ? (clone $ventasQuery)->sum('total_venta') : null;
        $ventas = $ventasQuery->orderByDesc('fecha_hora')->paginate(15)->withQueryString();
        $carrito = $request->session()->get('carrito', []);
        $totalCarrito = collect($carrito)->sum(fn (array $item): float => $item['cantidad'] * (float) $item['precio_unitario']);

        return view('ventas.index', compact(
            'productos',
            'ventas',
            'carrito',
            'totalCarrito',
            'fechaDesde',
            'fechaHasta',
            'filtrandoPorFecha',
            'totalVentasFiltradas',
        ));
    }

    public function create(Request $request): View
    {
        $productos = Producto::where('stock_actual', '>', 0)->orderBy('nombre_producto')->get();
        $carrito = $request->session()->get('carrito', []);
        $totalCarrito = collect($carrito)->sum(fn (array $item): float => $item['cantidad'] * (float) $item['precio_unitario']);

        return view('ventas.nueva', compact('productos', 'carrito', 'totalCarrito'));
    }

    public function agregar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_producto' => ['required', 'integer', Rule::exists(Producto::class, 'id_producto')],
            'cantidad' => ['required', 'integer', 'min:1', 'max:2147483647'],
        ]);
        $producto = Producto::findOrFail($data['id_producto']);
        $carrito = $request->session()->get('carrito', []);
        $cantidad = ($carrito[$producto->getKey()]['cantidad'] ?? 0) + $data['cantidad'];

        if ($cantidad > $producto->stock_actual) {
            throw ValidationException::withMessages(['cantidad' => 'La cantidad solicitada supera el stock disponible.']);
        }

        $carrito[$producto->getKey()] = [
            'id_producto' => $producto->getKey(),
            'nombre_producto' => $producto->nombre_producto,
            'cantidad' => $cantidad,
            'precio_unitario' => $producto->precio_venta,
        ];
        $request->session()->put('carrito', $carrito);

        return to_route('ventas.index');
    }

    public function eliminar(Request $request, int $producto): RedirectResponse
    {
        $request->session()->forget('carrito.'.$producto);

        return to_route('ventas.index');
    }

    public function vaciar(Request $request): RedirectResponse
    {
        $request->session()->put('carrito', []);

        return to_route('ventas.index');
    }

    public function confirmar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cliente_nombre' => ['nullable', 'string', 'max:100'],
            'cliente_documento' => ['nullable', 'regex:/^(?:[0-9]{8}|[0-9]{11})$/'],
            'metodo_pago' => ['required', Rule::in(['Efectivo', 'Yape', 'Plin', 'Tarjeta'])],
        ]);
        $carrito = $request->session()->get('carrito', []);

        if (empty($carrito)) {
            throw ValidationException::withMessages(['carrito' => 'El carrito está vacío.']);
        }

        Validator::make(['carrito' => $carrito], [
            'carrito' => ['required', 'array'],
            'carrito.*.id_producto' => ['required', 'integer', 'distinct'],
            'carrito.*.cantidad' => ['required', 'integer', 'min:1', 'max:2147483647'],
        ])->validate();

        $venta = DB::transaction(function () use ($data, $carrito): Venta {
            $productos = Producto::whereIn('id_producto', array_column($carrito, 'id_producto'))
                ->orderBy('id_producto')->lockForUpdate()->get()->keyBy('id_producto');
            $venta = Venta::create([
                ...$data,
                'fecha_hora' => now(),
                'total_venta' => '0.00',
            ]);
            $totalCentimos = 0;

            foreach ($carrito as $item) {
                $producto = $productos->get($item['id_producto']);
                $cantidad = (int) $item['cantidad'];

                if (! $producto || $producto->stock_actual < $cantidad) {
                    throw ValidationException::withMessages(['carrito' => 'Un producto ya no está disponible o no tiene stock suficiente.']);
                }

                [$entero, $decimal] = explode('.', $producto->precio_venta);
                $precioCentimos = (int) $entero * 100 + (int) $decimal;

                if (str_starts_with($producto->precio_venta, '-') || ($precioCentimos > 0 && $cantidad > intdiv(9999999999 - $totalCentimos, $precioCentimos))) {
                    throw ValidationException::withMessages(['carrito' => 'El importe de la venta excede el límite permitido o contiene un precio inválido.']);
                }

                $totalCentimos += $precioCentimos * $cantidad;
                $venta->detalles()->create([
                    'id_producto' => $producto->getKey(),
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio_venta,
                ]);
                $producto->decrement('stock_actual', $cantidad);
                $producto->movimientosInventario()->create([
                    'tipo_movimiento' => 'SALIDA',
                    'cantidad' => $cantidad,
                    'motivo' => 'Venta #'.$venta->getKey(),
                    'fecha_hora' => now(),
                ]);
            }

            $venta->update(['total_venta' => sprintf('%d.%02d', intdiv($totalCentimos, 100), $totalCentimos % 100)]);

            return $venta;
        });

        $request->session()->put('carrito', []);

        return to_route('ventas.index')->with('venta_exitosa', $venta->getKey());
    }

    public function show(Venta $venta): View
    {
        $venta->load('detalles.producto');
        $detalles = $venta->detalles;

        return view('ventas.detalle', compact('venta', 'detalles'));
    }

    public function pdf(Venta $venta): Response
    {
        $venta->load('detalles.producto');
        $detalles = $venta->detalles;

        return Pdf::loadView('ventas.factura', compact('venta', 'detalles'))
            ->setPaper('a4')
            ->download('Recibo_Venta_'.$venta->getKey().'.pdf');
    }
}
