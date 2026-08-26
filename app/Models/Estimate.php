<?php

namespace App\Models;

use App\Enums\EstimateStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Estimate extends Model
{
    protected $guarded = [];

    public function input(): HasOne
    {
        return $this->hasOne(EstimateInput::class);
    }

    public function costComponents(): HasMany
    {
        return $this->hasMany(EstimateCostComponent::class);
    }

    public function totals(): HasOne
    {
        return $this->hasOne(EstimateTotal::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EstimateStatus::class,
            'quoted_at' => 'datetime',
        ];
    }
}
