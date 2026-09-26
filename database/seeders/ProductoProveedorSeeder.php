<?php

namespace Database\Seeders;

use App\Models\ProductoProveedor;
use Illuminate\Database\Seeder;

class ProductoProveedorSeeder extends Seeder
{
    public function run(): void
    {
        ProductoProveedor::factory()->count(5)->create();
    }
}
