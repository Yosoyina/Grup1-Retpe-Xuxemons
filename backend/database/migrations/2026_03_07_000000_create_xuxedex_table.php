<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Taula pivot que relaciona usuaris amb els seus Xuxemons capturats
     * Emmagatzema informació de captura: estat de captura
     */
    public function up(): void
    {
        Schema::create('xuxedex', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_xuxemon')->constrained('xuxemons')->onDelete('cascade');
            $table->timestamps();
            
            // Una única entrada per usuari-xuxemon
            $table->unique(['id_usuario', 'id_xuxemon']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xuxedex');
    }
};
