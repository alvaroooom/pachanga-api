<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'address' => fake()->word(),
            'city' => fake()->city(),
            'surface' => fake()->randomElement(["artificial","natural","indoor"]),
        ];
    }
}
