<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Amigos.
 *
 * Representa una relació d'amistat entre dos usuaris.
 * L'amistat és bidireccional: quan s'accepta una petició,
 * es creen dues files (A→B i B→A) per facilitar les consultes.
 */
class Amigos extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'amigos';

    protected $fillable = [
        'user_id',
        'id_amigo',
    ];

    // Retorna l'usuari propietari d'aquesta relació d'amistat
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Retorna l'usuari amic
    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_amigo');
    }
}
