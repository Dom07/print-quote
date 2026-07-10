<?php

namespace App\Services\Estimates;

use App\Models\PricingItem;
use InvalidArgumentException;
use RuntimeException;

class DripOffCalculator
{
    public function __construct(private ?EstimateRounder $rounder = null)
    {
        $this->rounder ??= new EstimateRounder;
    }

    public function calculate(float $length, float $width, int $quantity): array
    {
        if ($quantity === 0) {
            throw new InvalidArgumentException('No. of sheets to process must be greater than 0 for Drip Off.');
        }

        $coefficient = $this->rateFor('drip-off-coefficient');
        $minimumCost = $this->rateFor('drip-off-minimum-charge');
        $flatAddOnAmount = $this->rateFor('drip-off-setup-charge');

        $baseRatePerSheet = $this->rounder->money(($length * $width * $coefficient) / 100);
        $baseCost = $this->rounder->money($baseRatePerSheet * $quantity);
        $minimumAdjustedBaseCost = $this->rounder->money(max($minimumCost, $baseCost));
        $minimumAdjustedBaseRatePerSheet = $minimumAdjustedBaseCost >= $minimumCost 
            ? $baseRatePerSheet 
            : ($minimumCost / $quantity);
        $flatAddOnRatePerSheet = $this->rounder->money($flatAddOnAmount / $quantity);
        $finalRatePerSheet = $this->rounder->money($minimumAdjustedBaseRatePerSheet + $flatAddOnRatePerSheet);

        return [
            'coefficient' => $coefficient,
            'minimum_cost' => $this->rounder->money($minimumCost),
            'flat_add_on_amount' => $this->rounder->money($flatAddOnAmount),
            'quantity' => $quantity,
            'base_rate_per_sheet' => $baseRatePerSheet,
            'base_cost' => $baseCost,
            'minimum_adjusted_base_cost' => $minimumAdjustedBaseCost,
            'minimum_adjusted_base_rate_per_sheet' => $minimumAdjustedBaseRatePerSheet,
            'flat_add_on_rate_per_sheet' => $flatAddOnRatePerSheet,
            'final_rate_per_sheet' => $finalRatePerSheet,
        ];
    }

    private function rateFor(string $slug): float
    {
        $item = PricingItem::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('pricingCategory', function ($query) {
                $query
                    ->where('slug', 'drip-off')
                    ->where('is_active', true);
            })
            ->first();

        if ($item === null || $item->rate === null) {
            throw new RuntimeException("Missing active Drip Off pricing item [{$slug}].");
        }

        return (float) $item->rate;
    }
}
