<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Malalties extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'malalties';

    protected $fillable = [
        'Bajon_Azucar',
        'Atracon',
    ];

    protected function casts(): array
    {
        return [
            'Bajon_Azucar' => 'number',
            'Atracon' => 'number',
        ];
    }
}
