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
        'xuxedex_id',
        'tipo_enfermedad',
    ];

    protected function casts(): array
    {
        return [
            'xuxedex_id' => 'integer',
            'tipo_enfermedad' => 'string',
        ];
    }

    /*
     * Una enfermedad pertenece a un xuxemon (xuxedex)
     */
    public function xuxedex()
    {
        return $this->belongsTo(Xuxedex::class, 'xuxedex_id');
    }
}
