<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarginSlab extends Model
{
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:4',
            'max_amount' => 'decimal:4',
            'margin_percentage' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
