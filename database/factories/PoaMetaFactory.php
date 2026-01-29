<?php

namespace Database\Factories;

use App\Models\PoaMeta;
use App\Models\PoaProyecto;
use Illuminate\Database\Eloquent\Factories\Factory;

class PoaMetaFactory extends Factory
{
    protected $model = PoaMeta::class;

    public function definition(): array
    {
        return [
            'poa_proyecto_id' => PoaProyecto::factory(),
            'descripcion' => $this->faker->randomElement([
                'Fortalecimiento Institucional',
                'Desarrollo Social',
                'Infraestructura Municipal',
                'Medio Ambiente',
            ]),
        ];
    }
}
