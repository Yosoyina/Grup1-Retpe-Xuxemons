<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\XuxedexService;
use App\Models\Peticiones_amistad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'avatar',
        'nombre',
        'apellidos',
        'id_jugador',
        'email',
        'password',
        'role',
        'actiu',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
            'actiu' => 'boolean',
            'last_reward_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (self $user): void {
            app(XuxedexService::class)->ensureStarterXuxedex($user->id);
        });
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }


    // Un usuari té molts xuxemons
    public function xuxemons()
    {
        return $this->hasMany(Xuxemons::class, 'user_id');
    }

    // ── Sistema de amigos ─────────────────────────────────────

    /* Solicitudes que este usuario ha enviado */
    public function sentRequests()
    {
        return $this->hasMany(Peticiones_amistad::class, 'id_remitente');
    }

    /* Solicitudes que este usuario ha recibido */
    public function receivedRequests()
    {
        return $this->hasMany(Peticiones_amistad::class, 'id_destinatario');
    }

    /* Lista de amigos (relación bidireccional) */
    public function friends()
    {
        return $this->belongsToMany(
            User::class,
            'friends',
            'user_id',
            'friend_id'
        );
    }
}
