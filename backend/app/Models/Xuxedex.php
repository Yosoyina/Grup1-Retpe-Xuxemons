<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Xuxedex extends Model
{
    use HasFactory;
 
    protected $table = 'xuxedex';
 
    protected $fillable = [
        'id_usuario',
        'id_xuxemon',
        'esta_capturado',
    ];
 
    protected function casts(): array
    {
        return [
            'esta_capturado' => 'boolean',
        ];
    }
 
    // Relació amb el xuxemon
    public function xuxemon()
    {
        return $this->belongsTo(Xuxemons::class, 'id_xuxemon');
    }
 
    // Relació amb l'usuari
    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
