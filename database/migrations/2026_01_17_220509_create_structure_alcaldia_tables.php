<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up(): void
{
    // 1. Crear tabla Unidades (Reemplaza al modelo Unidad de Django)
    Schema::create('unidades', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->boolean('activa')->default(true);
        $table->boolean('sin_reporte')->default(false); // Para unidades que no reportan POA
        $table->timestamps();
    });

    // 2. Modificar tabla Users (Reemplaza al modelo Usuario de Django)
    Schema::table('users', function (Blueprint $table) {
        // El rol ya lo habíamos agregado, pero aseguramos
        if (!Schema::hasColumn('users', 'role')) {
            $table->enum('role', ['admin', 'unidad'])->default('unidad');
        }

        // Relación con Unidad (puede ser nulo para el superadmin global)
        $table->foreignId('unidad_id')->nullable()->constrained('unidades')->onDelete('set null');

        // Bandera para cambio de contraseña obligatorio
        $table->boolean('debe_cambiar_clave')->default(true);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structure_alcaldia_tables');
    }
};
