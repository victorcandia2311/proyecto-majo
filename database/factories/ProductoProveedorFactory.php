<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\ProductoProveedor;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductoProveedor> */
class ProductoProveedorFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'id_producto' => Producto::factory(),
            'id_proveedor' => Proveedor::factory(),
            'precio_compra' => fake()->randomFloat(2, 1, 300),
        ];
    }
}
