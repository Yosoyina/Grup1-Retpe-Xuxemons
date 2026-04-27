<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\XuxedexService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Model User.
 *
 * Representa un usuari del sistema. Implementa JWTSubject per a l'autenticació amb JWT.
 * En crear-se, s'inicialitza automàticament el seu Xuxedex inicial via XuxedexService.
 * Inclou relacions per al sistema d'amistats i sol·licituds.
 */
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
            'email_verified_at'            => 'datetime',
            'password'                      => 'hashed',
            'actiu'                         => 'boolean',
            'ultima_recompensa_at'          => 'datetime',
            'ultima_recompensa_xuxemon_at'  => 'datetime',
        ];
    }

    // Inicialitza el Xuxedex de l'usuari automàticament en crear el compte
    protected static function booted(): void
    {
        static::created(function (self $user): void {
            app(XuxedexService::class)->ensureStarterXuxedex($user->id);
        });
    }

    // Requerit per JWT: retorna l'identificador único de l'usuari
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    // Requerit per JWT: retorna els claims addicionals del token (buit per defecte)
    public function getJWTCustomClaims()
    {
        return [];
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
            'amigos',
            'user_id',
            'id_amigo'
        );
    }
}
