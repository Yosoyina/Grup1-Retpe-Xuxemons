<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Xuxes extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'xuxes';

    protected $fillable = [
        'nombre_xuxes',
        'tipo_xuxe',
        'descripcio',
        'imagen',
    ];

    protected function casts(): array
    {
        return [
            'nombre_xuxes' => 'string',
            'tipo_xuxe' => 'string',
            'descripcio' => 'string',
            'imagen' => 'string',
        ];
    }
}
