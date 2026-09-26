<?php

namespace Database\Seeders;

use App\Models\MovimientoInventario;
use Illuminate\Database\Seeder;

class MovimientoInventarioSeeder extends Seeder
{
    public function run(): void
    {
        MovimientoInventario::factory()->count(5)->create();
    }
}
