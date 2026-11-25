<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'venue_id' => Venue::factory(),
            'date' => fake()->dateTime(),
            'max_players' => fake()->numberBetween(-10000, 10000),
            'price' => fake()->randomFloat(2, 0, 9999.99),
            'description' => fake()->text(),
            'status' => fake()->randomElement(["open","full","cancelled"]),
        ];
    }
}
