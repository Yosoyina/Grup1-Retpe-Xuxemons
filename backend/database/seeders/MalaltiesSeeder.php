<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class MalaltiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $malalties = [
            ['xuxedex_id' => 1, 'tipo_enfermedad' => 'Bajon de azucar'],
            ['xuxedex_id' => 2, 'tipo_enfermedad' => 'Sobredosis'],
            ['xuxedex_id' => 3, 'tipo_enfermedad' => 'Atracon'],
        ];
 
        foreach ($malalties as $malaltia) {
            DB::table('malalties')->insertOrIgnore($malaltia);
        }
    }
}
