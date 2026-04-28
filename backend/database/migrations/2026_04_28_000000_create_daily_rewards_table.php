<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('xuxes')->nullable();
            $table->unsignedSmallInteger('xuxes_requested')->default(0);
            $table->unsignedSmallInteger('xuxes_added')->default(0);
            $table->unsignedSmallInteger('xuxes_discarded')->default(0);
            $table->json('xuxemon')->nullable();
            $table->boolean('xuxemon_unlocked')->default(false);
            $table->timestamps();
        });

        // Eliminar la columna JSON del resumen de la tabla users
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_reward_summary')) {
                $table->dropColumn('last_reward_summary');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_rewards');

        Schema::table('users', function (Blueprint $table) {
            $table->json('last_reward_summary')->nullable()->after('ultima_recompensa_xuxemon_at');
        });
    }
};
