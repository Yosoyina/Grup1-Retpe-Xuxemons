<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peticiones_amistad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_remitente')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_destinatario')->constrained('users')->cascadeOnDelete();
            $table->enum('estado', ['pendiente', 'aceptado', 'rechazado'])->default('pendiente');
            $table->timestamps();

            $table->unique(['id_remitente', 'id_destinatario']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peticiones_amistad');
    }
};
