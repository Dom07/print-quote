<?php

namespace App\Services\Estimates;

use App\Models\PricingItem;
use App\Models\PricingRule;

class PunchingRateResolver
{
    public function __construct(private ?EstimateRounder $rounder = null)
    {
        $this->rounder ??= new EstimateRounder;
    }

    public function resolve(?PricingItem $pricingItem, int $quantity): ?array
    {
        if ($pricingItem === null) {
            return null;
        }

        $rule = $pricingItem->pricingRules()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->first(fn (PricingRule $rule) => $this->ruleMatches($rule, $quantity));

        if ($rule !== null) {
            return [
                'name' => $pricingItem->name,
                'rate' => $this->rounder->money((float) $rule->rate),
                'source' => 'rule',
            ];
        }

        if ($pricingItem->rate === null) {
            return null;
        }

        return [
            'name' => $pricingItem->name,
            'rate' => $this->rounder->money((float) $pricingItem->rate),
            'source' => 'item',
        ];
    }

    private function ruleMatches(PricingRule $rule, int $quantity): bool
    {
        $minValue = $rule->min_value === null ? null : (float) $rule->min_value;
        $maxValue = $rule->max_value === null ? null : (float) $rule->max_value;

        if ($minValue === null && $maxValue !== null) {
            return $quantity <= $maxValue;
        }

        if ($minValue !== null && $maxValue === null) {
            return $quantity > $minValue;
        }

        if ($minValue !== null && $maxValue !== null) {
            return $quantity > $minValue && $quantity <= $maxValue;
        }

        return true;
    }
}
