<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Xuxemons;

class XuxemonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Aqui estan los xuxxemons por defecto
     */   
    public function run(): void
    {
        Xuxemons::factory()->count(10)->create();
    }
}
