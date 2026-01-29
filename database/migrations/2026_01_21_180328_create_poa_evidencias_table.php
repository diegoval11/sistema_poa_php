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
        Schema::create('poa_evidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poa_actividad_id')->constrained('poa_actividades')->onDelete('cascade');
            $table->string('tipo', 20); // PDF, FOTO, VIDEO, URL, MP3
            $table->string('archivo')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->integer('mes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poa_evidencias');
    }
};
