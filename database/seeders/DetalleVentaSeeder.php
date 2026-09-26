<?php

namespace Database\Seeders;

use App\Models\DetalleVenta;
use Illuminate\Database\Seeder;

class DetalleVentaSeeder extends Seeder
{
    public function run(): void
    {
        DetalleVenta::factory()->count(5)->create();
    }
}
