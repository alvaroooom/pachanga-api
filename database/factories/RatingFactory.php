<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'rated_id' => User::factory(),
            'game_id' => Game::factory(),
            'score' => fake()->numberBetween(1, 5),
            'comment' => fake()->word(),
        ];
    }
}
