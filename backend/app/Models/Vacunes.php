<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Vacunes extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'vacunes';

    protected $fillable = [
        'Xocolatina',
        'Xal_frutas',
        'Inxulina',
    ];

    protected function casts(): array
    {
        return [
            'Xocolatina' => 'number',
            'Xal_frutas' => 'number',
            'Inxulina' => 'number',
        ];
    }
}
