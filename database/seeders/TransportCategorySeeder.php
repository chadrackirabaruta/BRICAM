<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransportCategory;

class TransportCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Amatafari', 'unit_price' => 500.00],
            ['name' => 'Ibumba', 'unit_price' => 1000.00],
            // add more if needed
        ];

        foreach ($categories as $category) {
            TransportCategory::create($category);
        }
    }
}
