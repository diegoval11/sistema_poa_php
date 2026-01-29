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
        Schema::table('poa_proyectos', function (Blueprint $table) {
            $table->text('motivo_rechazo')->nullable()->after('fecha_aprobacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poa_proyectos', function (Blueprint $table) {
            $table->dropColumn('motivo_rechazo');
        });
    }
};
