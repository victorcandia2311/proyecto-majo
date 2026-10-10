<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function index(): View
    {
        $productos = Producto::orderBy('nombre_producto')->get();
        $movimientos = MovimientoInventario::with('producto')->orderByDesc('fecha_hora')->paginate(15);

        return view('inventario.index', compact('productos', 'movimientos'));
    }

    public function aumentar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_producto' => ['required', 'integer', Rule::exists(Producto::class, 'id_producto')],
            'cantidad' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'motivo' => ['nullable', 'string', 'max:200'],
        ]);

        DB::transaction(function () use ($data): void {
            $producto = Producto::lockForUpdate()->findOrFail($data['id_producto']);

            if ($producto->stock_actual + $data['cantidad'] > 2147483647) {
                throw ValidationException::withMessages(['cantidad' => 'El stock excede el límite permitido.']);
            }

            $producto->increment('stock_actual', $data['cantidad']);
            MovimientoInventario::create([
                'id_producto' => $producto->getKey(),
                'tipo_movimiento' => 'ENTRADA',
                'cantidad' => $data['cantidad'],
                'motivo' => $data['motivo'] ?? 'Reabastecimiento',
                'fecha_hora' => now(),
            ]);
        });

        return to_route('inventario.index');
    }
}
