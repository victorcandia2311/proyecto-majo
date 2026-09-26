<?php

namespace Database\Factories;

use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Venta> */
class VentaFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'fecha_hora' => now(),
            'cliente_nombre' => null,
            'cliente_documento' => null,
            'metodo_pago' => 'EFECTIVO',
            'total_venta' => 0,
        ];
    }
}
