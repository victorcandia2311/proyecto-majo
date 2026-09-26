<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Producto> */
class ProductoFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'nombre_producto' => fake()->words(3, true),
            'stock_actual' => 0,
            'stock_minimo_alerta' => 10,
            'precio_venta' => fake()->randomFloat(2, 1, 500),
        ];
    }
}
