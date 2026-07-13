<?php

namespace App\Services\Estimates;

use App\Models\MarginSlab;
use RuntimeException;

class MarginCalculator
{
    public function __construct(private ?EstimateRounder $rounder = null)
    {
        $this->rounder ??= new EstimateRounder;
    }

    public function calculate(float $totalCost): array
    {
        $totalCost = $this->rounder->money($totalCost);
        $slab = $this->resolveSlab($totalCost);
        $marginPercentage = (float) $slab->margin_percentage;
        $marginRate = $marginPercentage / 100;
        $remainingRate = 1 - $marginRate;

        if ($marginPercentage < 0) {
            throw new RuntimeException("Margin percentage must be zero or greater; {$marginPercentage}% configured.");
        }

        if ($remainingRate <= 0) {
            throw new RuntimeException("Margin percentage must be less than 100%; {$marginPercentage}% configured.");
        }

        $totalCostWithMargin = $totalCost / $remainingRate;
        $marginAmount = $totalCostWithMargin - $totalCost;

        return [
            'selected_margin_slab_id' => $slab->id,
            'selected_margin_slab_name' => $slab->name,
            'min_value' => $slab->min_amount === null ? null : (float) $slab->min_amount,
            'max_value' => $slab->max_amount === null ? null : (float) $slab->max_amount,
            'margin_percentage' => $marginPercentage,
            'margin_amount' => $this->rounder->money($marginAmount),
            'total_cost_with_margin' => $this->rounder->money($totalCostWithMargin),
        ];
    }

    private function resolveSlab(float $totalCost): MarginSlab
    {
        $slab = MarginSlab::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->first(function (MarginSlab $slab) use ($totalCost) {
                $min = $slab->min_amount === null ? null : (float) $slab->min_amount;
                $max = $slab->max_amount === null ? null : (float) $slab->max_amount;

                if ($min === null && $max !== null) {
                    return $totalCost <= $max;
                }

                if ($min !== null && $max !== null) {
                    return $totalCost > $min && $totalCost <= $max;
                }

                if ($min !== null && $max === null) {
                    return $totalCost > $min;
                }

                return false;
            });

        if (! $slab instanceof MarginSlab) {
            throw new RuntimeException("No active margin slab matches total cost {$totalCost}.");
        }

        return $slab;
    }
}
