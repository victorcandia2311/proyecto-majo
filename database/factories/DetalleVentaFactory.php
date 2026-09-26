<?php

namespace Database\Factories;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DetalleVenta> */
class DetalleVentaFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'id_venta' => Venta::factory(),
            'id_producto' => Producto::factory(),
            'cantidad' => 1,
            'precio_unitario' => fake()->randomFloat(2, 1, 500),
        ];
    }
}
