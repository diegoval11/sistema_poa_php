<?php

namespace Database\Factories;

use App\Models\PoaActividad;
use App\Models\PoaMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

class PoaActividadFactory extends Factory
{
    protected $model = PoaActividad::class;

    public function definition(): array
    {
        return [
            'poa_meta_id' => PoaMeta::factory(),
            'descripcion' => $this->faker->sentence(6),
            'unidad_medida' => $this->faker->randomElement(['Informe', 'Documento', 'Persona', 'Servicio']),
            'es_cuantificable' => true,
            'cantidad_programada_total' => $this->faker->numberBetween(10, 100),
            'medio_verificacion' => $this->faker->sentence(4),
            'recursos' => $this->faker->sentence(5),
            'costo_estimado' => $this->faker->randomFloat(2, 1000, 50000),
            'es_no_planificada' => false,
            // estado_aprobacion solo para actividades no planificadas - dejar sin especificar
        ];
    }

    public function noCuantificable(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_cuantificable' => false,
            'cantidad_programada_total' => 0,
        ]);
    }

    public function noPlanificada(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_no_planificada' => true,
            'estado_aprobacion' => 'PENDIENTE',
        ]);
    }
}
