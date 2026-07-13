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
        ?float $pastingRate = null,
    ): array {
        $rawComponents = [
            'paper_price_per_sheet' => $paperPricePerSheet,
            'printing_cost' => $printingCost,
            'ink_cost' => $inkCost,
            'foiling_cost' => $foilingCost ?? 0.0,
            'punching_rate' => $punchingRate ?? 0.0,
            'lamination_value' => $laminationValue ?? 0.0,
            'spot_uv_value' => $spotUvValue ?? 0.0,
            'drip_off_rate' => $dripOffRate ?? 0.0,
            'pasting_rate' => $pastingRate ?? 0.0,
        ];

        $components = [
            'paper_price_per_sheet' => $this->rounder->money($rawComponents['paper_price_per_sheet']),
            'printing_cost' => $this->rounder->money($rawComponents['printing_cost']),
            'ink_cost' => $this->rounder->money($rawComponents['ink_cost']),
            'foiling_cost' => $this->rounder->money($rawComponents['foiling_cost']),
            'punching_rate' => $this->rounder->money($rawComponents['punching_rate']),
            'lamination_value' => $this->rounder->money($rawComponents['lamination_value']),
            'spot_uv_value' => $this->rounder->money($rawComponents['spot_uv_value']),
            'drip_off_rate' => $this->rounder->money($rawComponents['drip_off_rate']),
            'pasting_rate' => $this->rounder->money($rawComponents['pasting_rate']),
        ];

        return [
            'components' => $components,
            'total_price_per_sheet' => $this->rounder->money(array_sum($rawComponents)),
        ];
    }
}
