<?php

namespace App\Services\Estimates;

class TotalPricePerSheetCalculator
{
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
            'paper_price_per_sheet' => $paperPricePerSheet,
            'printing_cost' => $printingCost,
            'ink_cost' => $inkCost,
            'foiling_cost' => $foilingCost ?? 0.0,
            'punching_rate' => $punchingRate ?? 0.0,
            'lamination_value' => $laminationValue ?? 0.0,
            'spot_uv_value' => $spotUvValue ?? 0.0,
            'drip_off_rate' => $dripOffRate ?? 0.0,
        ];

        return [
            'components' => $components,
            'total_price_per_sheet' => array_sum($components),
        ];
    }
}
