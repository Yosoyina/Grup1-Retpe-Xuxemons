<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Afegeix la columna 'enfermedad' a la taula xuxedex.
     *
     * Valors possibles:
     *   null              → el xuxemon està sa
     *   'Bajon de azucar' → necessita +2 xuxes extra per créixer (5%)
     *   'Sobredosis'      → sobredosis de sucre (10%)
     *   'Atracon'         → no pot ser alimentat (15%)
     */
    public function up(): void
    {
        Schema::table('xuxedex', function (Blueprint $table) {
            $table->enum('enfermedad', ['Bajon de azucar', 'Sobredosis', 'Atracon'])
                  ->nullable()
                  ->default(null)
                  ->after('esta_capturado');
        });
    }

    /**
     * Elimina la columna 'enfermedad' de la taula xuxedex.
     */
    public function down(): void
    {
        Schema::table('xuxedex', function (Blueprint $table) {
            $table->dropColumn('enfermedad');
        });
    }
};
