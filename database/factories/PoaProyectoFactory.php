<?php

namespace Database\Factories;

use App\Models\PoaProyecto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PoaProyectoFactory extends Factory
{
    protected $model = PoaProyecto::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nombre' => $this->faker->sentence(4),
            'anio' => $this->faker->numberBetween(2024, 2026),
            'objetivo_unidad' => $this->faker->paragraph(),
            'estado' => 'BORRADOR',
            'aprobado_por' => null,
            'fecha_aprobacion' => null,
        ];
    }

    public function enviado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'ENVIADO',
        ]);
    }

    public function aprobado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'APROBADO',
            'aprobado_por' => User::factory(),
            'fecha_aprobacion' => now(),
        ]);
    }
}
