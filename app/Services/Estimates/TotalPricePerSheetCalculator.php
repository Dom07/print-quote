<?php

namespace App\Services\Estimates;

class TotalPricePerSheetCalculator
{
    public function __construct(private ?EstimateRounder $rounder = null)
    {
        $this->rounder ??= new EstimateRounder;
    }

    public function calculate(
        float $paperPricePerSheet,
        float $printingCost,
        float $inkCost,
        ?float $foilingCost = null,
        ?float $punchingRate = null,
        ?float $laminationValue = null,
        ?float $spotUvValue = null,
        ?float $dripOffRate = null,
    ): array {
        $components = [
            'paper_price_per_sheet' => $this->rounder->money($paperPricePerSheet),
            'printing_cost' => $this->rounder->money($printingCost),
            'ink_cost' => $this->rounder->money($inkCost),
            'foiling_cost' => $this->rounder->money($foilingCost ?? 0.0),
            'punching_rate' => $this->rounder->money($punchingRate ?? 0.0),
            'lamination_value' => $this->rounder->money($laminationValue ?? 0.0),
            'spot_uv_value' => $this->rounder->money($spotUvValue ?? 0.0),
            'drip_off_rate' => $this->rounder->money($dripOffRate ?? 0.0),
        ];

        return [
            'components' => $components,
            'total_price_per_sheet' => $this->rounder->money(array_sum($components)),
        ];
    }
}
