<?php

namespace App\Services\Estimates;

use App\Models\PricingItem;
use App\Models\PricingRule;

class LaminationCalculator
{
    public function calculate(
        string $mode,
        ?PricingItem $frontPricingItem,
        ?PricingItem $backPricingItem,
        int $quantity,
        float $length,
        float $width,
    ): ?array {
        $front = $frontPricingItem === null
            ? null
            : $this->calculateSide($frontPricingItem, $quantity, $length, $width);
        $back = $mode === 'both_sides' && $backPricingItem !== null
            ? $this->calculateSide($backPricingItem, $quantity, $length, $width)
            : null;

        return [
            'mode' => $mode,
            'front' => $front,
            'back' => $back,
            'combined_value' => ($front['value'] ?? 0.0) + ($back['value'] ?? 0.0),
        ];
    }

    private function calculateSide(PricingItem $pricingItem, int $quantity, float $length, float $width): ?array
    {
        $resolved = $this->resolveCoefficient($pricingItem, $quantity);

        if ($resolved === null) {
            return null;
        }

        $value = ($length * $width * $resolved['coefficient']) / 100;

        return [
            'name' => $pricingItem->name,
            'coefficient' => $resolved['coefficient'],
            'value' => $value,
            'source' => $resolved['source'],
        ];
    }

    private function resolveCoefficient(PricingItem $pricingItem, int $quantity): ?array
    {
        $rule = $pricingItem->pricingRules()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->first(fn (PricingRule $rule) => $this->ruleMatches($rule, $quantity));

        if ($rule !== null) {
            return [
                'coefficient' => (float) $rule->rate,
                'source' => 'rule',
            ];
        }

        if ($pricingItem->rate === null) {
            return null;
        }

        return [
            'coefficient' => (float) $pricingItem->rate,
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
