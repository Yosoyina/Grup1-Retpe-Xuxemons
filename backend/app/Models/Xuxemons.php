<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Xuxemons extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'xuxemons';

    protected $fillable = [
        'nombre_xuxemon',
        'tipo_elemento',
        'tamano',
        'descripcio',
        'imagen',
    ];

    protected function casts(): array
    {
        return [
            'nombre_xuxemon' => 'string',
            'tipo_elemento' => 'string',
            'tamano' => 'string',
            'descripcio' => 'string',
            'imagen' => 'string',
        ];
    }
}
