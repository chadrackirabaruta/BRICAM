<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockType;
use Illuminate\Support\Facades\DB;

class StockTypeSeeder extends Seeder
{
    public function run(): void
    {
       DB::table('stock_types')->insert([
    ['name' => 'mabisi', 'decrease_amount' => 0, 'increase_amount' => 1000, 'flow_stage' => 1],
    ['name' => 'gutwikwa', 'decrease_amount' => 300, 'increase_amount' => 0, 'flow_stage' => 2],
    ['name' => 'ahiye', 'decrease_amount' => 200, 'increase_amount' => 0, 'flow_stage' => 3],
]);

    }
}