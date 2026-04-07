<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_remitente');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_destinatario');
    }

}
