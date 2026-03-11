<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\Models\Inventario;

class Xuxes extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'xuxes';

    protected $fillable = [
        'nombre_xuxes',
        'descripcio',
        'imagen',
    ];

    protected function casts(): array
    {
        return [
            'nombre_xuxes' => 'string',
            'descripcio' => 'string',
            'imagen' => 'string',
        ];
    }

    // Relació: una Xuxa pot estar en molts inventaris
    public function inventario()
    {
        return $this->hasMany(Inventario::class, 'xuxe_id');
    }
}
