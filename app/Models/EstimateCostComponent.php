<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateCostComponent extends Model
{
    protected $guarded = [];

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

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
            'rate' => 'decimal:4',
            'original_rate' => 'decimal:4',
            'is_overridden' => 'boolean',
        ];
    }
}
