<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Xuxemons;

class XuxedexSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Asigna Xuxemons a los usuarios
     */
    public function run(): void
    {
        // Limpiar tabla xuxedex antes de insertar
        DB::table('xuxedex')->truncate();
        
        // Obtener el primer usuario
        $usuario = User::first();
        
        if (!$usuario) {
            return; // No hay usuarios, salir
        }

        // Obtener todos los Xuxemons
        $xuxemons = Xuxemons::all();

        // Asignar los primeros 5 Xuxemons al usuario con estado capturado
        $xuxemons->take(5)->each(function ($xuxemon) use ($usuario) {
            DB::table('xuxedex')->insert([
                'id_usuario' => $usuario->id,
                'id_xuxemon' => $xuxemon->id,
                'esta_capturado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        // Asignar los siguientes 3 Xuxemons al usuario con estado no capturado
        $xuxemons->slice(5, 3)->each(function ($xuxemon) use ($usuario) {
            DB::table('xuxedex')->insert([
                'id_usuario' => $usuario->id,
                'id_xuxemon' => $xuxemon->id,
                'esta_capturado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
