<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Xuxemons>
 */
class XuxemonsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_xuxemon' => fake()->name(),
            'tipo_elemento' => fake()->randomElement(['Aigua', 'Terra', 'Aire']),
            'tamano' => fake()->randomElement(['Petit', 'Mitja', 'Gran']),
            'descripcio' => fake()->sentence(),
            'imagen' => null,
        ];
    }
}
