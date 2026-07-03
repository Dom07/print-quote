<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateTotal extends Model
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
            'cost_per_sheet' => 'decimal:4',
            'cost_per_piece' => 'decimal:4',
            'subtotal' => 'decimal:4',
            'margin_percentage' => 'decimal:4',
            'margin_amount' => 'decimal:4',
            'grand_total' => 'decimal:4',
        ];
    }
}
