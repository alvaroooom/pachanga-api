<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'game_id' => Game::factory(),
            'status' => fake()->randomElement(["confirmed","waitlist"]),
        ];
    }
}
