<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

/**
 * Model Xuxemons.
 *
 * Representa un Xuxemon del catàleg global del joc.
 * Cada Xuxemon té un tipus d'element (Aigua/Terra/Aire), una mida (Petit/Mitja/Gran)
 * i pertany a una línia evolutiva identificada pel camp 'evolucion_xuxemon'.
 * El camp 'xuxes_per_pujar' indica quantes Xuxes cal consumir per fer-lo evolucionar.
 */
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
        'evolucion_xuxemon',
        'xuxes_per_pujar',
    ];

    protected function casts(): array
    {
        return [
            'nombre_xuxemon' => 'string',
            'tipo_elemento' => 'string',
            'tamano' => 'string',
            'descripcio' => 'string',
            'imagen' => 'string',
            'evolucion_xuxemon' => 'string',
            'xuxes_per_pujar' => 'integer',
        ];
    }

}
