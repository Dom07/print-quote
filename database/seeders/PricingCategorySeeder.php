<?php

namespace Database\Seeders;

use App\Models\PricingCategory;
use Illuminate\Database\Seeder;

class PricingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Paper', 'slug' => 'paper'],
            ['name' => 'Interest Slabs', 'slug' => 'interest-slabs'],
            ['name' => 'Printing', 'slug' => 'printing'],
            ['name' => 'Ink', 'slug' => 'ink'],
            ['name' => 'Punching', 'slug' => 'punching'],
            ['name' => 'Lamination', 'slug' => 'lamination'],
            ['name' => 'Spot UV', 'slug' => 'spot-uv'],
            ['name' => 'Drip Off', 'slug' => 'drip-off'],
            ['name' => 'Foiling', 'slug' => 'foiling'],
            ['name' => 'Add-on Costs', 'slug' => 'add-on-costs'],
            ['name' => 'Required Costs', 'slug' => 'required-costs'],
            ['name' => 'Operational Expenses', 'slug' => 'operational-expenses'],
            ['name' => 'Transport', 'slug' => 'transport'],
        ];

        foreach ($categories as $sortOrder => $category) {
            PricingCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => null,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
        }
    }
}
