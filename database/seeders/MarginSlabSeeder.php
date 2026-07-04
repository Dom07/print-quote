<?php

namespace Database\Seeders;

use App\Models\MarginSlab;
use Illuminate\Database\Seeder;

class MarginSlabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slabs = [
            ['Below 20k', null, '20000.0000', '17.0000'],
            ['20k to 50k', '20000.0000', '50000.0000', '15.0000'],
            ['50k to 1.5 Lac', '50000.0000', '150000.0000', '13.0000'],
            ['1.5 Lac to 3.5 Lac', '150000.0000', '350000.0000', '12.0000'],
            ['Above 3.5 Lac', '350000.0000', null, '10.0000'],
        ];

        foreach ($slabs as $sortOrder => [$name, $minAmount, $maxAmount, $marginPercentage]) {
            MarginSlab::updateOrCreate(
                ['name' => $name],
                [
                    'min_amount' => $minAmount,
                    'max_amount' => $maxAmount,
                    'margin_percentage' => $marginPercentage,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
        }
    }
}
