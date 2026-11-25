<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Indica si el usuario puede entrar al panel de administracion. */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Partidos que ha creado como organizador. */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    /** Inscripciones del usuario en partidos. */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /** Valoraciones que ha puesto a otros jugadores. */
    public function ratingsGiven(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /** Valoraciones que ha recibido de otros jugadores. */
    public function ratingsReceived(): HasMany
    {
        return $this->hasMany(Rating::class, 'rated_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
