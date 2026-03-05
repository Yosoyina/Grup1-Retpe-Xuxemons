<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Xuxemons extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'xuxemons';

    protected $fillable = [
        'nombre_xuxemon',
        'tipo_elemento',
        'grandeza',
    ];

    protected function casts(): array
    {
        return [
            'nombre_xuxemon' => 'string',
            'tipo_elemento' => 'string',
            'grandeza' => 'string',
        ];
    }
}
