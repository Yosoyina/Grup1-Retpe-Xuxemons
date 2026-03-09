<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Inventario extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'inventario';

    const MAX_STACK = 5;   // Màximas unidades por espacio en el apilable
    const MAX_SLOTS = 20;  // Total de espacios disponibles que hay en la mochila

    protected $fillable = [
        'xuxemon_id',
        'xuxa_id',
        'quantity',
    ];

}
