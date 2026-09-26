<?php

namespace Database\Factories;

use App\Models\Asistencia;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Asistencia> */
class AsistenciaFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'id_empleado' => Empleado::factory(),
            'fecha' => fake()->date(),
            'hora_ingreso' => '08:00:00',
            'hora_salida' => null,
        ];
    }
}
