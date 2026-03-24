<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Corrige los valores de apilable en la tabla inventario
     * basándose en el tipo de xuxe relacionado
     */
    public function up(): void
    {
        // Actualiza los items cuyo xuxe NO es apilable
        DB::statement('
            UPDATE inventario
            SET apilable = 0
            WHERE xuxe_id IN (
                SELECT id FROM xuxes WHERE apilable = 0
            )
        ');

        // Actualiza los items cuyo xuxe SÍ es apilable
        DB::statement('
            UPDATE inventario
            SET apilable = 1
            WHERE xuxe_id IN (
                SELECT id FROM xuxes WHERE apilable = 1
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Por seguridad, la operación inversa solo resetea apilable a true
        DB::table('inventario')->update(['apilable' => true]);
    }
};
