<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalaryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
  
    DB::table('salary_types')->insert([
        ['name' => 'Ku cyumweru'],
        ['name' => 'Ku kwezi'],
        ['name' => 'Ku gikorwa'],
    ]);
}
    }

