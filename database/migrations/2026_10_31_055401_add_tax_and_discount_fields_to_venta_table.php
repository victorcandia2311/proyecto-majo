<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('venta', function (Blueprint $table): void {
            $table->decimal('subtotal_venta', 10, 2)->nullable();
            $table->decimal('igv_porcentaje', 5, 2)->nullable();
            $table->decimal('igv_monto', 10, 2)->nullable();
            $table->decimal('descuento_porcentaje', 5, 2)->nullable();
            $table->decimal('descuento_monto', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venta', function (Blueprint $table): void {
            $table->dropColumn([
                'subtotal_venta',
                'igv_porcentaje',
                'igv_monto',
                'descuento_porcentaje',
                'descuento_monto',
            ]);
        });
    }
};
