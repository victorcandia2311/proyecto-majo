<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoProveedor;
use App\Models\Proveedor;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(): View
    {
        $proveedores = Proveedor::orderBy('id_proveedor')->paginate(10, ['*'], 'suppliers_page');
        $proveedoresOptions = Proveedor::orderBy('nombre_empresa')->get();
        $productos = Producto::orderBy('nombre_producto')->get();
        $precios = ProductoProveedor::query()
            ->join('producto', 'producto.id_producto', '=', 'producto_proveedor.id_producto')
            ->join('proveedor', 'proveedor.id_proveedor', '=', 'producto_proveedor.id_proveedor')
            ->select('producto_proveedor.*')
            ->with(['producto', 'proveedor'])
            ->orderBy('producto.nombre_producto')
            ->orderBy('proveedor.nombre_empresa')
            ->paginate(10, ['*'], 'prices_page');

        return view('proveedores.index', compact('proveedores', 'proveedoresOptions', 'productos', 'precios'));
    }

    public function asignarProducto(): View
    {
        $productos = Producto::orderBy('nombre_producto')->get();
        $proveedores = Proveedor::orderBy('nombre_empresa')->get();

        return view('proveedores.asignar_producto', compact('productos', 'proveedores'));
    }

    public function store(Request $request): RedirectResponse
    {
        Proveedor::create($request->validate([
            'nombre_empresa' => ['required', 'string', 'max:100'],
        ]));

        return to_route('proveedores.index')->with('feedback_success', 'Proveedor registrado correctamente.');
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update($request->validate([
            'nombre_empresa' => ['required', 'string', 'max:100'],
        ]));

        return to_route('proveedores.index')->with('feedback_success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $proveedor->delete();

        return to_route('proveedores.index')->with('feedback_success', 'Proveedor eliminado correctamente.');
    }

    public function guardarPrecio(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_producto' => ['required', 'integer', Rule::exists(Producto::class, 'id_producto')],
            'id_proveedor' => ['required', 'integer', Rule::exists(Proveedor::class, 'id_proveedor')],
            'precio_compra' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ]);

        try {
            ProductoProveedor::create($data);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages([
                'id_producto' => 'Este producto ya tiene un precio registrado para este proveedor.',
            ]);
        }

        return to_route('proveedores.index')->with('feedback_success', 'Precio de proveedor registrado correctamente.');
    }

    public function actualizarPrecio(Request $request, Proveedor $proveedor, Producto $producto): RedirectResponse
    {
        $data = $request->validate([
            'precio_compra' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ]);

        $precio = $proveedor->productosProveedores()->where('id_producto', $producto->getKey())->firstOrFail();
        $precio->update($data);

        return to_route('proveedores.index')->with('feedback_success', 'Precio de proveedor actualizado correctamente.');
    }

    public function eliminarPrecio(Proveedor $proveedor, Producto $producto): RedirectResponse
    {
        $proveedor->productosProveedores()->where('id_producto', $producto->getKey())->firstOrFail()->delete();

        return to_route('proveedores.index')->with('feedback_success', 'Precio de proveedor eliminado correctamente.');
    }
}
