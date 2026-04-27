<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * Model Peticiones_amistad.
 *
 * Representa una sol·licitud d'amistat entre dos usuaris.
 * Els estats possibles són: 'pendiente', 'aceptado', 'rechazado'.
 * Un cop acceptada, es creen les files corresponents al model Amigos.
 */
class Peticiones_amistad extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'peticiones_amistad';

     protected $fillable = [
        'id_remitente',
        'id_destinatario',
        'estado',
    ];

    // Retorna l'usuari que ha enviat la petició
    public function remitente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_remitente');
    }

    // Retorna l'usuari que ha rebut la petició
    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_destinatario');
    }

}
