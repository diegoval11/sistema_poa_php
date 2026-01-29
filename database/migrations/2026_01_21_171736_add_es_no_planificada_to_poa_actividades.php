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
        Schema::table('poa_actividades', function (Blueprint $table) {
            $table->boolean('es_no_planificada')->default(false)->after('es_cuantificable');
            $table->enum('estado_aprobacion', ['PENDIENTE', 'APROBADO', 'RECHAZADO'])
                  ->default('APROBADO')
                  ->after('es_no_planificada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poa_actividades', function (Blueprint $table) {
            $table->dropColumn(['es_no_planificada', 'estado_aprobacion']);
        });
    }
};
