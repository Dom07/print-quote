<?php

namespace Database\Seeders;

use App\Enums\RateType;
use App\Models\PricingCategory;
use App\Models\PricingItem;
use Illuminate\Database\Seeder;

class PricingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['paper', 'Grey Back ORD', 'grey-back-ord', '41.5000', 'kg', RateType::PerKg, true],
            ['paper', 'Grey Back Stock Lot', 'grey-back-stock-lot', '51.0000', 'kg', RateType::PerKg, true],
            ['paper', 'White Back ORD', 'white-back-ord', '46.0000', 'kg', RateType::PerKg, true],
            ['paper', 'White Back Stock Lot', 'white-back-stock-lot', '54.0000', 'kg', RateType::PerKg, true],
            ['paper', 'ITC', 'itc', '77.5000', 'kg', RateType::PerKg, true],
            ['interest-slabs', 'No Interest', 'no-interest', '0.0000', 'percent', RateType::Percentage, true],
            ['interest-slabs', 'Interest 1.25%', 'interest-1-25', '1.2500', 'percent', RateType::Percentage, true],
            ['interest-slabs', 'Interest 1.5%', 'interest-1-5', '1.5000', 'percent', RateType::Percentage, true],
            ['interest-slabs', 'Interest 2.5%', 'interest-2-5', '2.5000', 'percent', RateType::Percentage, true],
            ['interest-slabs', 'Interest 3%', 'interest-3', '3.0000', 'percent', RateType::Percentage, true],
            ['interest-slabs', 'Interest 3.75%', 'interest-3-75', '3.7500', 'percent', RateType::Percentage, true],
            ['interest-slabs', 'Interest 4.5%', 'interest-4-5', '4.5000', 'percent', RateType::Percentage, true],
            ['printing', 'Printing Cost', 'printing-cost', null, 'sheet', RateType::PerSheet, false],
            ['ink', 'Ink Cost', 'ink-cost', null, 'sheet', RateType::PerSheet, false],
            ['punching', 'Standard Punching', 'standard-punching', null, 'sheet', RateType::PerSheet, true],
            ['punching', 'Complicated Punching', 'complicated-punching', '0.7000', 'sheet', RateType::PerSheet, true],
            ['lamination', 'BOPP Lamination', 'bopp-lamination', null, 'coefficient', RateType::FormulaCoefficient, true],
            ['lamination', 'Matte Lamination', 'matte-lamination', null, 'coefficient', RateType::FormulaCoefficient, true],
            ['lamination', 'Gloss Lamination', 'gloss-lamination', null, 'coefficient', RateType::FormulaCoefficient, true],
            ['spot-uv', 'Spot UV', 'spot-uv', null, 'sheet', RateType::PerSheet, true],
            ['spot-uv', 'Raised UV', 'raised-uv', null, 'sheet', RateType::PerSheet, true],
            ['drip-off', 'Drip Off Coefficient', 'drip-off-coefficient', '0.7500', 'coefficient', RateType::FormulaCoefficient, true],
            ['drip-off', 'Drip Off Minimum Charge', 'drip-off-minimum-charge', '2500.0000', 'job', RateType::MinimumFlat, false],
            ['drip-off', 'Drip Off Setup Charge', 'drip-off-setup-charge', '1300.0000', 'job', RateType::Flat, false],
            ['foiling', 'Foiling Cost', 'foiling-cost', null, 'sheet', RateType::PerSheet, true],
            ['add-on-costs', 'Window & Labor Cost', 'window-labor-cost', null, 'piece', RateType::PerPiece, true],
            ['add-on-costs', 'Pasting Cost', 'pasting-cost', null, 'piece', RateType::PerPiece, true],
            ['add-on-costs', 'Lace Cost', 'lace-cost', '0.6700', 'piece', RateType::PerPiece, true],
            ['add-on-costs', 'Designing Cost', 'designing-cost', null, 'job', RateType::Flat, true],
            ['required-costs', 'Repeat Job Punch Cost', 'repeat-job-punch-cost', '250.0000', 'job', RateType::Flat, true],
            ['required-costs', 'New Job Punch Cost', 'new-job-punch-cost', null, 'job', RateType::Flat, true],
            ['operational-expenses', 'Plate Cost', 'plate-cost', null, 'job', RateType::Flat, false],
            ['operational-expenses', 'Transport Expense', 'transport-expense', null, 'job', RateType::Flat, false],
            ['operational-expenses', 'Box Cost', 'box-cost', null, 'job', RateType::Flat, false],
            ['operational-expenses', 'Box Packing Labor Cost', 'box-packing-labor-cost', null, 'job', RateType::Flat, false],
            ['operational-expenses', 'Internal Shifting Cost', 'internal-shifting-cost', null, 'job', RateType::Flat, false],
            ['transport', 'Transport Cost', 'transport-cost', null, 'job', RateType::Flat, false],
        ];

        foreach ($items as $sortOrder => [$categorySlug, $name, $slug, $rate, $unit, $rateType, $isSelectable]) {
            $category = PricingCategory::where('slug', $categorySlug)->firstOrFail();

            PricingItem::updateOrCreate(
                [
                    'pricing_category_id' => $category->id,
                    'slug' => $slug,
                ],
                [
                    'name' => $name,
                    'description' => null,
                    'unit' => $unit,
                    'rate' => $rate,
                    'rate_type' => $rateType,
                    'is_selectable' => $isSelectable,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
        }
    }
}
