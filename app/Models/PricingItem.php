<?php

namespace App\Models;

use App\Enums\RateType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingItem extends Model
{
    protected $guarded = [];

    public function pricingCategory(): BelongsTo
    {
        return $this->belongsTo(PricingCategory::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    public function estimateCostComponents(): HasMany
    {
        return $this->hasMany(EstimateCostComponent::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'rate_type' => RateType::class,
            'is_selectable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
