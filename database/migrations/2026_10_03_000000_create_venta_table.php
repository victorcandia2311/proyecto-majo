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
        Schema::create('venta', function (Blueprint $table) {
            $table->id('id_venta');
            $table->dateTime('fecha_hora')->nullable();
            $table->string('cliente_nombre')->nullable();
            $table->string('cliente_documento', 20)->nullable();
            $table->string('metodo_pago')->nullable();
            $table->decimal('total_venta', 10, 2)->nullable();
            $table->decimal('subtotal_venta', 10, 2)->nullable();
            $table->decimal('igv_porcentaje', 10, 2)->nullable();
            $table->decimal('igv_monto', 10, 2)->nullable();
            $table->decimal('descuento_porcentaje', 10, 2)->nullable();
            $table->decimal('descuento_monto', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta');
    }
};
