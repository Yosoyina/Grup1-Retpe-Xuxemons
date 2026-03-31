<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_config', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('valor');
            $table->string('descripcio')->nullable();
            $table->timestamps();
        });

        // Valors per defecte
        DB::table('system_config')->insert([
            [
                'clave'     => 'xuxes_hora_recompensa',
                'valor'     => '8',
                'descripcio' => 'Hora del dia (0–23) en que els jugadors reben les Xuxes diàries.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave'     => 'xuxes_quantitat_diaria',
                'valor'     => '10',
                'descripcio' => 'Quantitat de Xuxes que es reparteixen cada dia.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave'     => 'xuxemon_hora_recompensa',
                'valor'     => '8',
                'descripcio' => 'Hora del dia (0–23) en que els jugadors reben el Xuxemon diari.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave'     => 'infeccio_bajon',
                'valor'     => '5',
                'descripcio' => 'Probabilitat (%) que un Xuxemon agafi Bajón de Azúcar en ser alimentat.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave'     => 'infeccio_sobredosis',
                'valor'     => '10',
                'descripcio' => 'Probabilitat (%) que un Xuxemon agafi Sobredosis en ser alimentat.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave'     => 'infeccio_atracon',
                'valor'     => '15',
                'descripcio' => 'Probabilitat (%) que un Xuxemon agafi Atracón en ser alimentat.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_config');
    }
};
