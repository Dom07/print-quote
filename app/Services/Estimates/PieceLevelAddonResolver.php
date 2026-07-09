<?php

namespace App\Services\Estimates;

use App\Models\PricingItem;
use RuntimeException;

class PieceLevelAddonResolver
{
    public function laceCost(): float
    {
        return $this->rateFor('lace-cost');
    }

    public function designingCost(): float
    {
        return $this->rateFor('designing-cost');
    }

    private function rateFor(string $slug): float
    {
        $item = PricingItem::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('pricingCategory', function ($query) {
                $query
                    ->where('slug', 'add-on-costs')
                    ->where('is_active', true);
            })
            ->first();

        if ($item === null || $item->rate === null) {
            throw new RuntimeException("Missing active Add-on Costs pricing item [{$slug}].");
        }

        return (float) $item->rate;
    }
}
