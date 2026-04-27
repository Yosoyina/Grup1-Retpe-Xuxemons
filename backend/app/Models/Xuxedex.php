<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model Xuxedex.
 *
 * Representa l'entrada d'un Xuxemon al Xuxedex d'un usuari concret.
 * Cada fila indica si el Xuxemon està capturat (esta_capturado)
 * i si té una malaltia activa (enfermedad).
 * Els Xuxemons no capturats apareixen com a '???' al frontend.
 */
class Xuxedex extends Model
{
    use HasFactory;
 
    protected $table = 'xuxedex';
 
    protected $fillable = [
        'id_usuario',
        'id_xuxemon',
        'esta_capturado',
        'enfermedad',
    ];

    protected function casts(): array
    {
        return [
            'esta_capturado' => 'boolean',
        ];
    }
 
    // Retorna el Xuxemon del catàleg associat a aquesta entrada
    public function xuxemon()
    {
        return $this->belongsTo(Xuxemons::class, 'id_xuxemon');
    }

    // Retorna l'usuari propietari d'aquesta entrada del Xuxedex
    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
