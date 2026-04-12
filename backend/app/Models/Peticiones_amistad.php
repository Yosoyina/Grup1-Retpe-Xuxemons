<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

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

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_remitente');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_destinatario');
    }

}
