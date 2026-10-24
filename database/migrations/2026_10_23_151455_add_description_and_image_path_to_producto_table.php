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
        Schema::table('producto', function (Blueprint $table) {
            if (! Schema::hasColumn('producto', 'descripcion')) {
                $table->text('descripcion')->nullable();
            }

            if (! Schema::hasColumn('producto', 'imagen_path')) {
                $table->string('imagen_path')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $columns = array_filter(['descripcion', 'imagen_path'], fn (string $column): bool => Schema::hasColumn('producto', $column));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
