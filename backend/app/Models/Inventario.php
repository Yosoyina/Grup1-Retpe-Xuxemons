<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

/**
 * Model Inventario.
 *
 * Representa un slot de la motxilla d'un usuari.
 * Cada fila és un espai ocupat per un tipus de Xuxa.
 * - MAX_STACK: màxim d'unitats per slot en ítems apilables.
 * - MAX_SLOTS: màxim de slots totals per usuari.
 */
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
        'apilable',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Retorna la Xuxa associada a aquest slot
    public function xuxe()
    {
        return $this->belongsTo(Xuxes::class, 'xuxe_id');

    }

    // Retorna el nombre de slots ocupats per un usuari
    public static function slotsUtilizados(int $userId): int
    {
        return self::where('user_id', $userId)->count();
    }

}
