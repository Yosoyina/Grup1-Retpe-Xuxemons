<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Services\XuxedexService;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $xuxedexService = app(XuxedexService::class);

        User::factory()->count(10)->create()
            ->each(fn (User $user) => $xuxedexService->ensureStarterXuxedex($user->id));
    }
}
