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
        'evolucion_xuxemon',
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
        ];
    }

    // Un xuxemon pertany a un usuari
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    // Xuxemons de la mateixa línia evolutiva
    public function liniaEvolutiva()
    {
        return $this->hasMany(Xuxemons::class, 'evolucion_xuxemon', 'evolucion_xuxemon');
    }

}
