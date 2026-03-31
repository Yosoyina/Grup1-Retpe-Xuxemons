<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $table = 'system_config';

    protected $fillable = ['clave', 'valor', 'descripcio'];

    /**
     * Retorna el valor d'una clau de configuració.
     * Si no existeix, retorna $default.
     */
    public static function get(string $clave, mixed $default = null): mixed
    {
        $row = static::where('clave', $clave)->first();
        return $row ? $row->valor : $default;
    }

    /**
     * Estableix (o crea) el valor d'una clau de configuració.
     */
    public static function set(string $clave, mixed $valor): void
    {
        static::updateOrCreate(
            ['clave' => $clave],
            ['valor' => (string) $valor]
        );
    }
}
