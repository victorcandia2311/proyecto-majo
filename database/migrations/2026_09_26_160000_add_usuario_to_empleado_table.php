<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleado', function (Blueprint $table): void {
            $table->string('usuario', 50)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('empleado', function (Blueprint $table): void {
            $table->dropUnique(['usuario']);
            $table->dropColumn('usuario');
        });
    }
};
