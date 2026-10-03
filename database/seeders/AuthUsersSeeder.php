<?php

namespace Database\Seeders;

use App\Models\Empleado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthUsersSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['email' => 'admin@majo.com', 'usuario' => 'admin', 'rol' => 'admin', 'nombres' => 'Administrador MAJO', 'documento_identidad' => 'MAJO-ADMIN'],
            ['email' => 'vendedor@majo.com', 'usuario' => 'vendedor', 'rol' => 'empleado', 'nombres' => 'Vendedor MAJO', 'documento_identidad' => 'MAJO-VENDEDOR'],
        ] as $data) {
            $existingEmployee = Empleado::where('email', $data['email'])->first();
            if ($existingEmployee) {
                if (! $existingEmployee->usuario && ! Empleado::where('usuario', $data['usuario'])->exists()) {
                    $existingEmployee->forceFill(['usuario' => $data['usuario']])->save();
                }

                continue;
            }
            $password = $data['rol'] === 'admin' ? Str::random(20).'aA1!' : null;
            $empleado = new Empleado;
            $empleado->forceFill([
                ...$data,
                'es_eventual' => false,
                'password' => $password === null ? null : Hash::make($password),
            ])->save();
            if ($password !== null) {
                $this->command?->info('Usuario inicial: '.$data['usuario'].' — Contraseña inicial: '.$password);
            }
        }
    }
}
