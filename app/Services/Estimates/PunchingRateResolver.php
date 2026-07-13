<?php

namespace App\Services\Estimates;

use App\Enums\RateType;
use App\Models\PricingItem;
use App\Models\PricingRule;
use InvalidArgumentException;

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

        if ($quantity < 1) {
            throw new InvalidArgumentException('Punching quantity must be at least 1.');
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

        $rate = $this->rounder->money((float) $pricingItem->rate);

        return [
            'name' => $pricingItem->name,
            'rate' => $rate,
            'configured_rate' => $rate,
            'rate_type' => $pricingItem->rate_type?->value,
            'pricing_mode' => $pricingItem->rate_type?->value,
            'total_charge' => $this->rounder->money($rate * $quantity),
            'matched_rule_name' => null,
            'source' => 'item',
        ];
    }

    private function resultFromRule(PricingItem $pricingItem, PricingRule $rule, int $quantity): array
    {
        $configuredRate = (float) $rule->rate;
        $rateType = $rule->rate_type;

        if ($rateType === RateType::MinimumFlat) {
            $totalCharge = $configuredRate;
            $effectiveRate = $totalCharge / $quantity;
        } else {
            $effectiveRate = $configuredRate;
            $totalCharge = $configuredRate * $quantity;
        }

        return [
            'name' => $pricingItem->name,
            'rate' => $effectiveRate,
            'configured_rate' => $this->rounder->money($configuredRate),
            'rate_type' => $rateType?->value,
            'pricing_mode' => $rateType?->value,
            'total_charge' => $this->rounder->money($totalCharge),
            'matched_rule_name' => $rule->name,
            'source' => 'rule',
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
