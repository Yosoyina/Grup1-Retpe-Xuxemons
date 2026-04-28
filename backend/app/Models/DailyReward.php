<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model DailyReward.
 *
 * Guarda el resum de l'última recompensa diària de cada usuari.
 * Relació 1-a-1 amb User.
 */
class DailyReward extends Model
{
    protected $fillable = [
        'user_id',
        'xuxes',
        'xuxes_requested',
        'xuxes_added',
        'xuxes_discarded',
        'xuxemon',
        'xuxemon_unlocked',
    ];

    protected function casts(): array
    {
        return [
            'xuxes'            => 'array',
            'xuxemon'          => 'array',
            'xuxemon_unlocked' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
