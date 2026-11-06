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
        $proveedores = Proveedor::with(['productosProveedores.producto'])
            ->orderBy('id_proveedor')
            ->paginate(10, ['*'], 'suppliers_page');
        $productos = Producto::query()
            ->select(['id_producto', 'nombre_producto'])
            ->orderBy('nombre_producto')
            ->get();
        $productosDisponiblesPorProveedor = $proveedores->getCollection()->mapWithKeys(function (Proveedor $proveedor) use ($productos): array {
            $productosAsignados = $proveedor->productosProveedores->pluck('id_producto');

            return [
                $proveedor->getKey() => $productos->reject(
                    fn (Producto $producto): bool => $productosAsignados->contains($producto->getKey())
                ),
            ];
        });

        return view('proveedores.index', compact('proveedores', 'productosDisponiblesPorProveedor'));
    }

    public function productos(Proveedor $proveedor): View
    {
        $proveedor->load(['productosProveedores.producto']);
        $productosDisponibles = Producto::query()
            ->whereNotIn('id_producto', $proveedor->productosProveedores()->select('id_producto'))
            ->orderBy('nombre_producto')
            ->get();

        return view('proveedores.productos', compact('proveedor', 'productosDisponibles'));
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

    public function guardarPrecioProveedor(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $data = $request->validate([
            'id_producto' => [
                'required',
                'integer',
                Rule::exists(Producto::class, 'id_producto')->whereNull('deleted_at'),
            ],
            'precio_compra' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ]);

        try {
            $proveedor->productosProveedores()->create($data);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages([
                'id_producto' => 'Este producto ya tiene un precio registrado para este proveedor.',
            ]);
        }

        $redirect = $request->boolean('_return_to_list')
            ? to_route('proveedores.index')->with('supplier_products_modal', $proveedor->getKey())
            : to_route('proveedores.productos', $proveedor);

        return $redirect->with('feedback_success', 'Producto y precio registrados correctamente.');
    }

    public function actualizarPrecio(Request $request, Proveedor $proveedor, Producto $producto): RedirectResponse
    {
        $data = $request->validate([
            'precio_compra' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ]);

        $precio = $proveedor->productosProveedores()->where('id_producto', $producto->getKey())->firstOrFail();
        $precio->update($data);

        $redirect = $request->boolean('_return_to_list')
            ? to_route('proveedores.index')
            : to_route('proveedores.productos', $proveedor);

        return $redirect->with('feedback_success', 'Precio de proveedor actualizado correctamente.');
    }

    public function eliminarPrecio(Request $request, Proveedor $proveedor, Producto $producto): RedirectResponse
    {
        $proveedor->productosProveedores()->where('id_producto', $producto->getKey())->firstOrFail()->delete();

        $redirect = $request->boolean('_return_to_list')
            ? to_route('proveedores.index')
            : to_route('proveedores.productos', $proveedor);

        return $redirect->with('feedback_success', 'Precio de proveedor eliminado correctamente.');
    }
}
