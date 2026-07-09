<?php

namespace App\Services\Estimates;

use InvalidArgumentException;

class PaperPricingCalculator
{
    public function __construct(private ?EstimateRounder $rounder = null)
    {
        $this->rounder ??= new EstimateRounder;
    }

    public function calculate(
        float $selectedPaperRate,
        float $interestPercentage,
        float $kgsOfOrder,
        int $noOfSheets,
    ): array {
        if ($noOfSheets === 0) {
            throw new InvalidArgumentException('No. of sheets must be greater than 0.');
        }

        $selectedPaperRate = $this->rounder->money($selectedPaperRate);
        $updatedPaperRate = $this->rounder->money($selectedPaperRate + (($interestPercentage / 100) * $selectedPaperRate));
        $orderPaperCost = $updatedPaperRate * $kgsOfOrder;
        $pricePerSheet = $this->rounder->money($orderPaperCost / $noOfSheets);

        return [
            'selected_paper_rate' => $selectedPaperRate,
            'interest_percentage' => $interestPercentage,
            'updated_paper_rate' => $updatedPaperRate,
            'price_per_sheet' => $pricePerSheet,
        ];
    }
}
