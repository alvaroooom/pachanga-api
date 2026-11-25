<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Post;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Administrador del panel (para el login de la coleccion de Postman).
        User::updateOrCreate(
            ['email' => 'admin@email.com'],
            ['name' => 'Admin', 'password' => Hash::make('12345678'), 'role' => 'admin']
        );

        // Datos de ejemplo para ver algo en el panel.
        $users = User::factory(5)->create();
        $venues = Venue::factory(3)->create();

        Game::factory(4)->create([
            'user_id' => $users->random()->id,
            'venue_id' => $venues->random()->id,
        ]);

        Post::factory(5)->create(['user_id' => $users->random()->id]);
    }
}
