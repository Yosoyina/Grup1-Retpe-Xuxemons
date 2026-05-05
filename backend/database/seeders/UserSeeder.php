<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Services\XuxedexService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $xuxedexService = app(XuxedexService::class);

        $admin = User::create([
            'nombre'    => 'admin',
            'apellidos' => 'admin',
            'email'     => 'admin@gmail.com',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
        ]);
        $xuxedexService->ensureStarterXuxedex($admin->id);

        User::factory()->count(10)->create()
            ->each(fn (User $user) => $xuxedexService->ensureStarterXuxedex($user->id));
    }
}
