<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Inventario;
use App\Models\Xuxemons;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([UserSeeder::class]);
        // $this->call([InventarioSeeder::class]);
        $this->call([XuxemonsSeeder::class]);
    }
}
