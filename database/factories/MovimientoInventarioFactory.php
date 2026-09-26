<?php

namespace Database\Factories;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MovimientoInventario> */
class MovimientoInventarioFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'id_producto' => Producto::factory(),
            'tipo_movimiento' => 'ENTRADA',
            'cantidad' => fake()->numberBetween(1, 20),
            'motivo' => 'Ingreso de mercadería',
            'fecha_hora' => now(),
        ];
    }
}
