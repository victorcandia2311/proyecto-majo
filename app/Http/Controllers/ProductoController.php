<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(): View
    {
        $productos = Producto::orderBy('id_producto')->get();

        return view('productos.index', compact('productos'));
    }

    public function create(): View
    {
        return view('productos.crear');
    }

    public function store(ProductoRequest $request): RedirectResponse
    {
        Producto::create($request->validated());

        return to_route('productos.index');
    }

    public function edit(Producto $producto): View
    {
        return view('productos.editar', compact('producto'));
    }

    public function update(ProductoRequest $request, Producto $producto): RedirectResponse
    {
        $producto->update($request->validated());

        return to_route('productos.index');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        DB::transaction(function () use ($producto): void {
            $producto = Producto::lockForUpdate()->findOrFail($producto->getKey());

            if ($producto->detallesVenta()->exists() || $producto->movimientosInventario()->exists()) {
                throw ValidationException::withMessages([
                    'producto' => 'No se puede eliminar un producto con ventas o movimientos de inventario.',
                ]);
            }

            $producto->delete();
        });

        return to_route('productos.index');
    }
}
