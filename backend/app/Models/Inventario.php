<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\Models\User;
use App\Models\Xuxes;

class Inventario extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'inventario';

    const MAX_STACK = 5;   // Màximas unidades por espacio en el apilable
    const MAX_SLOTS = 20;  // Total de espacios disponibles que hay en la mochila

    protected $fillable = [
        'user_id',
        'xuxe_id',
        'cantidad',
    ];

    // Relaciones de Xuxemon y Xuxes
    public function xuxemon()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function xuxe()
    {
        return $this->belongsTo(Xuxes::class, 'xuxe_id');

    }

    // Espacios que ocupa aquest item

    public static function slotsUtilizados(int $userId): int
    {
        return self::where('user_id', $userId)->count();
    }

}
