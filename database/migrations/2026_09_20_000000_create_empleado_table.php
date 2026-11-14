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
        Schema::create('empleado', function (Blueprint $table) {
            $table->id('id_empleado');
            $table->string('nombres')->nullable();
            $table->boolean('es_eventual')->default(false);
            $table->string('password')->nullable();
            $table->string('rol')->default('empleado');
            $table->string('codigo_verificacion')->nullable();
            $table->timestamp('codigo_expira_en')->nullable();
            $table->rememberToken();
            $table->string('usuario', 50)->nullable()->unique();
            $table->string('documento_identidad', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleado');
    }
};
