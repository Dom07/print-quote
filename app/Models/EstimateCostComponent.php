<?php

namespace App\Models;

use App\Enums\RateType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateCostComponent extends Model
{
    protected $guarded = [];

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
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
            'rate_type' => RateType::class,
            'is_overridden' => 'boolean',
        ];
    }
}
