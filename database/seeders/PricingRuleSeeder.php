<?php

namespace Database\Seeders;

use App\Enums\PricingRuleAppliesTo;
use App\Enums\RateType;
use App\Models\PricingItem;
use App\Models\PricingRule;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            ['standard-punching', 'Below 1000 Sheets', null, '999.0000', '1000.0000', 'job', RateType::MinimumFlat],
            ['standard-punching', '1000 to 2000 Sheets', '999.0000', '2000.0000', '1.0000', 'sheet', RateType::PerSheet],
            ['standard-punching', 'Above 2000 Sheets', '2000.0000', null, '0.6000', 'sheet', RateType::PerSheet],
            ['bopp-lamination', 'Up to 5000 Sheets', null, '5000.0000', '0.3700', 'coefficient', RateType::FormulaCoefficient],
            ['bopp-lamination', 'Above 5000 Sheets', '5000.0000', null, '0.3500', 'coefficient', RateType::FormulaCoefficient],
            ['matte-lamination', 'Up to 5000 Sheets', null, '5000.0000', '0.4700', 'coefficient', RateType::FormulaCoefficient],
            ['matte-lamination', 'Above 5000 Sheets', '5000.0000', null, '0.4500', 'coefficient', RateType::FormulaCoefficient],
            ['spot-uv', 'Minimum Charge Below 1000 Sheets', null, '1000.0000', '1250.0000', 'job', RateType::MinimumFlat],
            ['spot-uv', 'Per Sheet 1000 And Above', '1000.0000', null, '1.2500', 'sheet', RateType::PerSheet],
            ['raised-uv', 'Minimum Charge Below 1000 Sheets', null, '1000.0000', '2800.0000', 'job', RateType::MinimumFlat],
            ['raised-uv', 'Per Sheet 1000 And Above', '1000.0000', null, '2.8000', 'sheet', RateType::PerSheet],
        ];

        $standardPunching = PricingItem::where('slug', 'standard-punching')->firstOrFail();
        PricingRule::query()
            ->where('pricing_item_id', $standardPunching->id)
            ->whereNotIn('name', [
                'Below 1000 Sheets',
                '1000 to 2000 Sheets',
                'Above 2000 Sheets',
            ])
            ->delete();

        foreach ($rules as $sortOrder => [$itemSlug, $name, $minValue, $maxValue, $rate, $unit, $rateType]) {
            $item = PricingItem::where('slug', $itemSlug)->firstOrFail();

            PricingRule::updateOrCreate(
                [
                    'pricing_item_id' => $item->id,
                    'name' => $name,
                ],
                [
                    'applies_to' => PricingRuleAppliesTo::ProductionSheets,
                    'min_value' => $minValue,
                    'max_value' => $maxValue,
                    'rate' => $rate,
                    'unit' => $unit,
                    'rate_type' => $rateType,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
        }
    }
}
