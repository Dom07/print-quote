<?php

namespace App\Services\Estimates;

use App\Models\MarginSlab;
use RuntimeException;

class MarginCalculator
{
    public function calculate(float $totalCost): array
    {
        $slab = $this->resolveSlab($totalCost);
        $marginPercentage = (float) $slab->margin_percentage;
        $marginAmount = $totalCost * ($marginPercentage / 100);
        $totalCostWithMargin = $totalCost + $marginAmount;

        return [
            'selected_margin_slab_id' => $slab->id,
            'selected_margin_slab_name' => $slab->name,
            'min_value' => $slab->min_amount === null ? null : (float) $slab->min_amount,
            'max_value' => $slab->max_amount === null ? null : (float) $slab->max_amount,
            'margin_percentage' => $marginPercentage,
            'margin_amount' => $this->money($marginAmount),
            'total_cost_with_margin' => $this->money($totalCostWithMargin),
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

    private function money(float $value): float
    {
        return round($value, 2, PHP_ROUND_HALF_UP);
    }
}
