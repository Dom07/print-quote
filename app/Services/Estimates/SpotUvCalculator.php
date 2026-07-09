<?php

namespace App\Services\Estimates;

use App\Enums\RateType;
use App\Models\PricingItem;
use App\Models\PricingRule;
use InvalidArgumentException;

class SpotUvCalculator
{
    public function __construct(private ?EstimateRounder $rounder = null)
    {
        $this->rounder ??= new EstimateRounder;
    }

    public function calculate(?PricingItem $pricingItem, int $quantity): ?array
    {
        if ($pricingItem === null) {
            return null;
        }

        if ($quantity === 0) {
            throw new InvalidArgumentException('No. of sheets to process must be greater than 0 for Spot UV.');
        }

        $rule = $pricingItem->pricingRules()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->first(fn (PricingRule $rule) => $this->ruleMatches($rule, $quantity));

        if ($rule !== null) {
            return $this->resultFromRule($pricingItem, $rule, $quantity);
        }

        if ($pricingItem->rate === null) {
            return null;
        }

        return [
            'name' => $pricingItem->name,
            'quantity' => $quantity,
            'resolved_amount' => $this->rounder->money((float) $pricingItem->rate),
            'calculation_type' => 'per_sheet',
            'value' => $this->rounder->money((float) $pricingItem->rate),
            'source' => 'item',
        ];
    }

    private function resultFromRule(PricingItem $pricingItem, PricingRule $rule, int $quantity): array
    {
        $resolvedAmount = $this->rounder->money((float) $rule->rate);
        $calculationType = $this->calculationType($rule);
        $value = $this->rounder->money($calculationType === 'minimum_divided_by_quantity'
            ? $resolvedAmount / $quantity
            : $resolvedAmount);

        return [
            'name' => $pricingItem->name,
            'quantity' => $quantity,
            'resolved_amount' => $resolvedAmount,
            'calculation_type' => $calculationType,
            'value' => $value,
            'source' => 'rule',
        ];
    }

    private function calculationType(PricingRule $rule): string
    {
        if ($rule->rate_type === RateType::MinimumFlat) {
            return 'minimum_divided_by_quantity';
        }

        if ($rule->rate_type === RateType::PerSheet) {
            return 'per_sheet';
        }

        return $rule->max_value !== null && $rule->min_value === null
            ? 'minimum_divided_by_quantity'
            : 'per_sheet';
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
