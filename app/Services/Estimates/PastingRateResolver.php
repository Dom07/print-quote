<?php

namespace App\Services\Estimates;

use App\Models\PricingItem;
use RuntimeException;

class PastingRateResolver
{
    private const ITEM_SLUGS = [
        'four_sides' => [false => 'four-sides-pasting', true => 'four-sides-pasting-with-checking'],
        'eight_sides' => [false => 'eight-sides-pasting', true => 'eight-sides-pasting-with-checking'],
    ];

    public function __construct(private ?EstimateRounder $rounder = null)
    {
        $this->rounder ??= new EstimateRounder;
    }

    public function resolve(string $pastingSides, bool $withChecking): array
    {
        $slug = self::ITEM_SLUGS[$pastingSides][$withChecking] ?? null;

        if ($slug === null) {
            throw new RuntimeException("Unsupported pasting combination: {$pastingSides}.");
        }

        $pricingItem = PricingItem::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('pricingCategory', fn ($query) => $query
                ->where('slug', 'pasting')
                ->where('is_active', true))
            ->first();

        if ($pricingItem === null || $pricingItem->rate === null) {
            throw new RuntimeException("Active pasting pricing item [{$slug}] was not found.");
        }

        return [
            'pricing_item_id' => $pricingItem->id,
            'pricing_item_name' => $pricingItem->name,
            'pasting_sides' => $pastingSides,
            'pasting_sides_label' => $pastingSides === 'four_sides' ? '4 Sides' : '8 Sides',
            'with_checking' => $withChecking,
            'rate' => $this->rounder->money((float) $pricingItem->rate),
            'rate_type' => 'per_piece',
        ];
    }
}
