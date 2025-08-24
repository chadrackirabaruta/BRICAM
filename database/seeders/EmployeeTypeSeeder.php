<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       

    DB::table('employee_types')->insert([
        ['name' => 'Abikorezi'],
        ['name' => 'Ababumbyi'],
        ['name' => 'Service'],
    ]);
}


    
}
