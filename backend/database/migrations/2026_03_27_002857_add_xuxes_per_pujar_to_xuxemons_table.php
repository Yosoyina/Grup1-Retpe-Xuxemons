<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('xuxemons', function (Blueprint $table) {
            $table->unsignedInteger('xuxes_per_pujar')->default(3)->after('evolucion_xuxemon');
        });
 
        // Assigna els valors per defecte segons el tamany actual
        DB::table('xuxemons')->where('tamano', 'Petit')->update(['xuxes_per_pujar' => 3]);
        DB::table('xuxemons')->where('tamano', 'Mitja')->update(['xuxes_per_pujar' => 5]);
        DB::table('xuxemons')->where('tamano', 'Gran')->update(['xuxes_per_pujar' => 0]);
    }
 
    public function down(): void
    {
        Schema::table('xuxemons', function (Blueprint $table) {
            $table->dropColumn('xuxes_per_pujar');
        });
    }
};
