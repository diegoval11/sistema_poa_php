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
        // Cambiar cantidad_programada_total en poa_actividades de decimal a integer
        Schema::table('poa_actividades', function (Blueprint $table) {
            $table->integer('cantidad_programada_total')->default(0)->change();
        });

        // Cambiar cantidad_programada y cantidad_ejecutada en poa_programaciones de decimal a integer
        Schema::table('poa_programaciones', function (Blueprint $table) {
            $table->integer('cantidad_programada')->default(0)->change();
            $table->integer('cantidad_ejecutada')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a decimal(10,2)
        Schema::table('poa_actividades', function (Blueprint $table) {
            $table->decimal('cantidad_programada_total', 10, 2)->default(0)->change();
        });

        Schema::table('poa_programaciones', function (Blueprint $table) {
            $table->decimal('cantidad_programada', 10, 2)->default(0)->change();
            $table->decimal('cantidad_ejecutada', 10, 2)->default(0)->change();
        });
    }
};
