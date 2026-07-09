<?php

namespace App\Services\Estimates;

use App\Models\PricingItem;
use RuntimeException;

class RequiredPieceCostResolver
{
    public function repeatJobPunchCost(): float
    {
        $item = PricingItem::query()
            ->where('slug', 'repeat-job-punch-cost')
            ->where('is_active', true)
            ->whereHas('pricingCategory', function ($query) {
                $query
                    ->where('slug', 'required-costs')
                    ->where('is_active', true);
            })
            ->first();

        if ($item === null || $item->rate === null) {
            throw new RuntimeException('Missing active Required Costs pricing item [repeat-job-punch-cost].');
        }

        return (float) $item->rate;
    }
}
