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
            ['email' => 'admin@majo.com', 'rol' => 'admin', 'nombres' => 'Administrador MAJO', 'documento_identidad' => 'MAJO-ADMIN'],
            ['email' => 'vendedor@majo.com', 'rol' => 'empleado', 'nombres' => 'Vendedor MAJO', 'documento_identidad' => 'MAJO-VENDEDOR'],
        ] as $data) {
            if (Empleado::where('email', $data['email'])->exists()) {
                continue;
            }
            $password = Str::random(20).'aA1!';
            $empleado = new Empleado;
            $empleado->forceFill([...$data, 'es_eventual' => false, 'password' => Hash::make($password)])->save();
            $this->command?->info($data['email'].' — Contraseña inicial: '.$password);
        }
    }
}
