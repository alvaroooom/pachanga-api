<?php

namespace Tests\Feature\Admin;

use App\Models\Comment;
use App\Models\Game;
use App\Models\Post;
use App\Models\Rating;
use App\Models\Registration;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        return $admin;
    }

    // ---------- Acceso ----------

    public function test_invitado_recibe_401(): void
    {
        $this->getJson('/api/admin/stats')->assertUnauthorized();
    }

    public function test_usuario_normal_recibe_403(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/stats')->assertForbidden();
        $this->getJson('/api/admin/users')->assertForbidden();
        $this->deleteJson('/api/admin/posts/1')->assertForbidden();
    }

    public function test_login_con_token_real_y_logout(): void
    {
        User::factory()->create(['email' => 'admin@email.com', 'password' => '12345678', 'role' => 'admin']);

        $token = $this->postJson('/api/login', ['email' => 'admin@email.com', 'password' => '12345678'])
            ->assertOk()
            ->json('token');

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/admin/stats')
            ->assertOk();

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout')
            ->assertNoContent();
    }

    // ---------- Dashboard ----------

    public function test_estadisticas(): void
    {
        $this->admin();
        $venue = Venue::factory()->create();
        Game::factory()->create(['venue_id' => $venue->id, 'status' => 'open']);
        Game::factory()->create(['venue_id' => $venue->id, 'status' => 'cancelled']);

        $this->getJson('/api/admin/stats')
            ->assertOk()
            ->assertJsonPath('users', User::count())
            ->assertJsonPath('venues', 1)
            ->assertJsonPath('games', 2)
            ->assertJsonPath('games_by_status.open', 1)
            ->assertJsonPath('games_by_status.full', 0)
            ->assertJsonPath('games_by_status.cancelled', 1);
    }

    // ---------- Usuarios ----------

    public function test_usuarios_listar_buscar_editar(): void
    {
        $this->admin();
        $raul = User::factory()->create(['name' => 'Raul Perez', 'email' => 'raul@email.com']);
        User::factory()->create(['name' => 'Otro']);

        $this->getJson('/api/admin/users?search=raul')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'raul@email.com');

        $this->getJson("/api/admin/users/{$raul->id}")->assertOk()->assertJsonPath('name', 'Raul Perez');

        $this->putJson("/api/admin/users/{$raul->id}", ['name' => 'Raul Editado'])
            ->assertOk()
            ->assertJsonPath('name', 'Raul Editado');

        $this->putJson("/api/admin/users/{$raul->id}", ['email' => 'no-es-un-email'])->assertUnprocessable();
    }

    public function test_cambiar_rol_y_no_se_puede_cambiar_el_propio(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->patchJson("/api/admin/users/{$user->id}/role", ['role' => 'admin'])
            ->assertOk()
            ->assertJsonPath('role', 'admin');

        $this->patchJson("/api/admin/users/{$user->id}/role", ['role' => 'superman'])->assertUnprocessable();
        $this->patchJson("/api/admin/users/{$admin->id}/role", ['role' => 'user'])->assertUnprocessable();
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_borrar_usuario_en_cascada_y_no_a_uno_mismo(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        Post::factory()->create(['user_id' => $user->id]);

        $this->deleteJson("/api/admin/users/{$admin->id}")->assertUnprocessable();

        $this->deleteJson("/api/admin/users/{$user->id}")->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertSame(0, Post::count());
    }

    // ---------- Campos ----------

    public function test_crud_de_campos(): void
    {
        $this->admin();

        $id = $this->postJson('/api/admin/venues', [
            'name' => 'Polideportivo', 'address' => 'Calle 1', 'city' => 'Sevilla', 'surface' => 'artificial',
        ])->assertCreated()->json('id');

        $this->postJson('/api/admin/venues', ['name' => 'X', 'address' => 'Y', 'city' => 'Z', 'surface' => 'agua'])
            ->assertUnprocessable();

        $this->getJson('/api/admin/venues?city=Sevilla')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/admin/venues?city=Madrid')->assertOk()->assertJsonCount(0, 'data');

        $this->putJson("/api/admin/venues/$id", ['city' => 'Dos Hermanas'])
            ->assertOk()
            ->assertJsonPath('city', 'Dos Hermanas');

        $this->deleteJson("/api/admin/venues/$id")->assertNoContent();
        $this->assertDatabaseMissing('venues', ['id' => $id]);
    }

    // ---------- Partidos e inscripciones ----------

    public function test_partidos_filtrar_editar_cancelar_borrar(): void
    {
        $this->admin();
        $venue = Venue::factory()->create();
        $game = Game::factory()->create(['venue_id' => $venue->id, 'status' => 'open', 'date' => '2026-12-01 20:00:00']);
        Game::factory()->create(['venue_id' => $venue->id, 'status' => 'full', 'date' => '2026-12-02 20:00:00']);

        $this->getJson('/api/admin/games?status=open')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/admin/games?date=2026-12-02')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/admin/games/{$game->id}")->assertOk()->assertJsonStructure(['user', 'venue', 'registrations']);

        $this->putJson("/api/admin/games/{$game->id}", ['max_players' => 14, 'price' => 3.5])
            ->assertOk()
            ->assertJsonPath('max_players', 14);
        $this->putJson("/api/admin/games/{$game->id}", ['status' => 'raro'])->assertUnprocessable();

        $this->patchJson("/api/admin/games/{$game->id}/cancel")->assertOk()->assertJsonPath('status', 'cancelled');

        $this->deleteJson("/api/admin/games/{$game->id}")->assertNoContent();
        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    }

    public function test_inscripciones_listar_cambiar_estado_expulsar(): void
    {
        $this->admin();
        $game = Game::factory()->create();
        $reg = Registration::factory()->create(['game_id' => $game->id, 'status' => 'waitlist']);

        $this->getJson("/api/admin/games/{$game->id}/registrations")->assertOk()->assertJsonCount(1, 'data');

        $this->patchJson("/api/admin/registrations/{$reg->id}", ['status' => 'confirmed'])
            ->assertOk()
            ->assertJsonPath('status', 'confirmed');
        $this->patchJson("/api/admin/registrations/{$reg->id}", ['status' => 'otro'])->assertUnprocessable();

        $this->deleteJson("/api/admin/registrations/{$reg->id}")->assertNoContent();
        $this->assertSame(0, Registration::count());
    }

    // ---------- Moderacion ----------

    public function test_valoraciones_listar_filtrar_borrar(): void
    {
        $this->admin();
        $game = Game::factory()->create();
        $rated = User::factory()->create();
        $rating = Rating::factory()->create(['game_id' => $game->id, 'rated_id' => $rated->id, 'score' => 4]);
        Rating::factory()->create(['game_id' => $game->id]);

        $this->getJson('/api/admin/ratings')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson("/api/admin/ratings?rated_id={$rated->id}")->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/admin/ratings/{$rating->id}")->assertOk()->assertJsonPath('score', 4);

        $this->deleteJson("/api/admin/ratings/{$rating->id}")->assertNoContent();
        $this->assertDatabaseMissing('ratings', ['id' => $rating->id]);
    }

    public function test_posts_y_comentarios_moderacion(): void
    {
        $this->admin();
        $post = Post::factory()->create();
        $c1 = Comment::factory()->create(['post_id' => $post->id]);
        Comment::factory()->create(['post_id' => $post->id]);

        $this->getJson('/api/admin/posts')->assertOk()->assertJsonPath('data.0.comments_count', 2);
        $this->getJson("/api/admin/posts/{$post->id}")->assertOk()->assertJsonCount(2, 'comments');
        $this->getJson("/api/admin/comments?post_id={$post->id}")->assertOk()->assertJsonCount(2, 'data');

        $this->deleteJson("/api/admin/comments/{$c1->id}")->assertNoContent();
        $this->assertSame(1, Comment::count());

        $this->deleteJson("/api/admin/posts/{$post->id}")->assertNoContent();
        $this->assertSame(0, Post::count());
        $this->assertSame(0, Comment::count());
    }

    // ---------- Crear y editar (CRUD completo) ----------

    public function test_crear_usuario_partido_y_post_y_editar_post(): void
    {
        $admin = $this->admin();

        $this->postJson('/api/admin/users', [
            'name' => 'Nuevo', 'email' => 'nuevo@email.com', 'password' => '12345678', 'role' => 'user',
        ])->assertCreated()->assertJsonPath('email', 'nuevo@email.com');
        $this->assertDatabaseHas('users', ['email' => 'nuevo@email.com', 'role' => 'user']);

        $venue = Venue::factory()->create();
        $this->postJson('/api/admin/games', [
            'venue_id' => $venue->id, 'date' => '2030-01-01 18:00:00', 'max_players' => 10,
        ])->assertCreated()->assertJsonPath('user_id', $admin->id);

        $id = $this->postJson('/api/admin/posts', ['content' => 'Hola'])
            ->assertCreated()->json('id');
        $this->putJson("/api/admin/posts/{$id}", ['content' => 'Editado'])
            ->assertOk()->assertJsonPath('content', 'Editado');
    }
}
