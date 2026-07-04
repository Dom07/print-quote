<?php

namespace App\Models;

use App\Enums\PricingRuleAppliesTo;
use App\Enums\RateType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    protected $guarded = [];

    public function pricingItem(): BelongsTo
    {
        return $this->belongsTo(PricingItem::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'applies_to' => PricingRuleAppliesTo::class,
            'min_value' => 'decimal:4',
            'max_value' => 'decimal:4',
            'rate' => 'decimal:4',
            'rate_type' => RateType::class,
            'is_active' => 'boolean',
        ];
    }
}
