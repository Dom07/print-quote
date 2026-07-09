<?php

namespace App\Services\Estimates;

use App\Models\PricingItem;
use RuntimeException;

class PieceLevelAddonResolver
{
    public function laceCost(): float
    {
        $item = PricingItem::query()
            ->where('slug', 'lace-cost')
            ->where('is_active', true)
            ->whereHas('pricingCategory', function ($query) {
                $query
                    ->where('slug', 'add-on-costs')
                    ->where('is_active', true);
            })
            ->first();

        if ($item === null || $item->rate === null) {
            throw new RuntimeException('Missing active Add-on Costs pricing item [lace-cost].');
        }

        return (float) $item->rate;
    }
}
