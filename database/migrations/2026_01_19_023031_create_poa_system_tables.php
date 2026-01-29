<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Proyectos (Cabecera del POA)
        if (!Schema::hasTable('poa_proyectos')) {
            Schema::create('poa_proyectos', function (Blueprint $table) {
                $table->id();
                // Relación con User (Unidad)
                $table->foreignId('user_id')->constrained()->onDelete('restrict');

                $table->string('nombre', 200)->nullable(); // Opcional al inicio según requerimiento
                $table->year('anio')->index();
                $table->text('objetivo_unidad')->nullable();

                // Estado controlado por Enum en PHP
                $table->enum('estado', ['BORRADOR', 'ENVIADO', 'APROBADO', 'RECHAZADO', 'OBSERVADO'])
                      ->default('BORRADOR');

                // Auditoría básica de aprobación
                $table->foreignId('aprobado_por')->nullable()->constrained('users');
                $table->timestamp('fecha_aprobacion')->nullable();

                $table->timestamps();
                $table->softDeletes(); // Para "papelera" en lugar de borrado físico inmediato

                // Un proyecto por Unidad por Año (Regla de negocio implícita, ajustable)
                // $table->unique(['user_id', 'anio']);
            });
        }

        // 2. Metas del Proyecto
        if (!Schema::hasTable('poa_metas')) {
            Schema::create('poa_metas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('poa_proyecto_id')->constrained()->onDelete('cascade');
                $table->text('descripcion'); // Texto final (ya sea de catálogo o custom)
                $table->timestamps();
            });
        }

        // 3. Actividades
        if (!Schema::hasTable('poa_actividades')) {
            Schema::create('poa_actividades', function (Blueprint $table) {
                $table->id();
                $table->foreignId('poa_meta_id')->constrained()->onDelete('cascade');

                $table->string('descripcion', 500);
                $table->string('unidad_medida', 100);
                $table->boolean('es_cuantificable')->default(true);

                // Cantidad total programada (Suma de los meses debe igualar esto)
                $table->decimal('cantidad_programada_total', 10, 2)->default(0);

                $table->text('medio_verificacion');
                $table->text('recursos')->nullable();
                // Decimales precisos para dinero (Django usaba DecimalField)
                $table->decimal('costo_estimado', 15, 2)->default(0);

                $table->timestamps();
            });
        }

        // 4. Programación Mensual (Equivalent a AvanceMensual)
        if (!Schema::hasTable('poa_programaciones')) {
            Schema::create('poa_programaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('poa_actividad_id')->constrained('poa_actividades')->onDelete('cascade');

                $table->unsignedTinyInteger('mes'); // 1-12
                $table->year('anio');

                // Planificación
                $table->decimal('cantidad_programada', 10, 2)->default(0);

                // Ejecución (Para el futuro, ya dejamos la estructura lista)
                $table->decimal('cantidad_ejecutada', 10, 2)->default(0);
                $table->text('causal_desvio')->nullable();
                $table->boolean('es_extraordinaria')->default(false); // No planificada

                $table->timestamps();

                // Índices para velocidad en reportes
                $table->unique(['poa_actividad_id', 'mes', 'anio']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('poa_programaciones');
        Schema::dropIfExists('poa_actividades');
        Schema::dropIfExists('poa_metas');
        Schema::dropIfExists('poa_proyectos');
    }
};
