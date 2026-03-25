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
        Schema::create('malalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xuxedex_id')->constrained('xuxedex')->onDelete('cascade');
            $table->string('tipo_enfermedad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('malalties');
    }
};
