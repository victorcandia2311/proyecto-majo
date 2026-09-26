<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleado', function (Blueprint $table): void {
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->enum('rol', ['admin', 'empleado'])->default('empleado');
            $table->string('codigo_verificacion')->nullable();
            $table->timestamp('codigo_expira_en')->nullable();
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::table('empleado', function (Blueprint $table): void {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'password', 'rol', 'codigo_verificacion', 'codigo_expira_en', 'remember_token']);
        });
    }
};
