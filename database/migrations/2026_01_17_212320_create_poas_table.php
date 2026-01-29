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
    Schema::create('poas', function (Blueprint $table) {
        $table->id();
        $table->string('nombre_proyecto');
        $table->text('descripcion')->nullable();

        $table->decimal('presupuesto', 10, 2)->default(0.00);

        $table->integer('anio');
        $table->enum('estado', ['Pendiente', 'Aprobado', 'Rechazado'])->default('Pendiente');

        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poas');
    }
};
