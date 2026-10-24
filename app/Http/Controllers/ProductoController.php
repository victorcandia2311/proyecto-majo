<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $data = $request->validated();
        unset($data['imagen']);

        if ($request->hasFile('imagen')) {
            $data['imagen_path'] = $request->file('imagen')->store('productos', 'public');
        }

        Producto::create($data);

        return to_route('productos.index')->with('feedback_success', 'Producto registrado correctamente.');
    }

    public function edit(Producto $producto): View
    {
        return view('productos.editar', compact('producto'));
    }

    public function update(ProductoRequest $request, Producto $producto): RedirectResponse
    {
        $data = $request->validated();
        unset($data['imagen']);

        if ($request->hasFile('imagen')) {
            $previousImage = $producto->imagen_path;
            $data['imagen_path'] = $request->file('imagen')->store('productos', 'public');
            $producto->update($data);

            if ($previousImage !== null) {
                Storage::disk('public')->delete($previousImage);
            }
        } else {
            $producto->update($data);
        }

        return to_route('productos.index')->with('feedback_success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        DB::transaction(function () use ($producto): void {
            $producto = Producto::lockForUpdate()->findOrFail($producto->getKey());
            $producto->delete();
        });

        return to_route('productos.index')->with('mensaje', 'producto_eliminado');
    }
}
