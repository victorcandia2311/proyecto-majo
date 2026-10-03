<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleado', function (Blueprint $table) {
            $table->string('documento_identidad', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('empleado')->whereNull('documento_identidad')->exists()) {
            throw new RuntimeException('Cannot require employee identity numbers while records without a document exist.');
        }

        Schema::table('empleado', function (Blueprint $table) {
            $table->string('documento_identidad', 20)->nullable(false)->change();
        });
    }
};
