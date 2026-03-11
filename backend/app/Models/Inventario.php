<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\Models\Xuxemons;
use App\Models\Xuxes;

class Inventario extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'inventario';

    const MAX_STACK = 5;   // Màximas unidades por espacio en el apilable
    const MAX_SLOTS = 20;  // Total de espacios disponibles que hay en la mochila

    protected $fillable = [
        'xuxemon_id',
        'xuxe_id',
        'quantity',
    ];

    // Relaciones de Xuxemon y Xuxes
    public function xuxemon()
    {
        return $this->belongsTo(Xuxemons::class, 'xuxemon_id');
    }

    public function xuxa()
    {
        return $this->belongsTo(Xuxes::class, 'xuxe_id');

    }

    // Espacios que ocupa aquest item
    public function slotsUsed(): int
    {
        return (int) ceil($this->quantity / self::MAX_STACK);
    }

    // Espais totals usats per un xuxemon
    public static function usedSlots(int $xuxemonId): int
    {
        return self::where('xuxemon_id', $xuxemonId)
            ->get()
            ->sum(fn($item) => $item->slotsUsed());
    }

}
