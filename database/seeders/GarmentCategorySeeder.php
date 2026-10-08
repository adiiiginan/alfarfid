<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GarmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('garment_categories')->insert([
            [
                'category_name' => 'Baju Steril Standar',
                'default_max_cycle' => 40,
            ],
            [
                'category_name' => 'Jas Bedah',
                'default_max_cycle' => 30,
            ],
        ]);
    }
}
