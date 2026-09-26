<?php

namespace Database\Factories;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Empleado> */
class EmpleadoFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'nombres' => fake()->name(),
            'documento_identidad' => fake()->unique()->numerify('########'),
            'es_eventual' => false,
        ];
    }

    public function withAccess(string $role = 'empleado'): static
    {
        return $this->state(fn (): array => [
            'email' => fake()->unique()->safeEmail(),
            'password' => 'AccesoSeguro123!',
            'rol' => $role,
        ]);
    }
}
