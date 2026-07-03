<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Estimate extends Model
{
    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function input(): HasOne
    {
        return $this->hasOne(EstimateInput::class);
    }

    public function costComponents(): HasMany
    {
        return $this->hasMany(EstimateCostComponent::class);
    }

    public function total(): HasOne
    {
        return $this->hasOne(EstimateTotal::class);
    }
}
