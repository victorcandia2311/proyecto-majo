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
        Schema::create('producto', function (Blueprint $table) {
            $table->id('id_producto');
            $table->string('nombre_producto')->nullable();
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo_alerta')->default(0);
            $table->decimal('precio_venta', 10, 2)->nullable();
            $table->string('imagen')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('imagen_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
