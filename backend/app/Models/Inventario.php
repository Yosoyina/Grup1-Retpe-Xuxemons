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

    protected $fillable = [
        'xuxemon_id',
        'tipo_item',
        'xuxe_id',
        'cantidad',
    ];

    protected function casts(): array
    {
        return [
            'xuxemon_id' => 'integer',
            'xuxe_id' => 'integer',
            'cantidad' => 'integer',
        ];
    }
}
