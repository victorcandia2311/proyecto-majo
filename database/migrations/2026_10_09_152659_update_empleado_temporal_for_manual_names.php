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
        Schema::table('empleado_temporal', function (Blueprint $table) {
            if (! Schema::hasColumn('empleado_temporal', 'nombre_temp')) {
                $table->string('nombre_temp', 100)->nullable();
            }

            $table->integer('id_empleado')->nullable()->change();
            $table->dropForeign('fk_empleado_temporal_empleado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleado_temporal', function (Blueprint $table) {
            $table->dropColumn('nombre_temp');
            $table->foreign('id_empleado', 'fk_empleado_temporal_empleado')
                ->references('id_empleado')->on('empleado')->cascadeOnDelete();
        });
    }
};
