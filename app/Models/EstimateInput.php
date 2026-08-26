<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateInput extends Model
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
            'pieces_per_sheet' => 'integer',
            'production_sheets' => 'integer',
            'wastage_sheets' => 'integer',
            'total_sheets_with_wastage' => 'integer',
            'sheets_used_for_process' => 'integer',
            'raw_inputs' => 'array',
        ];
    }
}
