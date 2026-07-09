<?php

namespace App\Services\Estimates;

use InvalidArgumentException;

class PiecePricingCalculator
{
    public function calculate(
        int $noOfSheets,
        int $ups,
        float $totalPricePerSheet,
        ?float $windowLaborCost = null,
        ?float $laceCost = null,
        ?float $designingCostRate = null,
        string $punchCostJobType = 'repeat_job',
        ?float $repeatJobPunchCost = null,
        ?float $newJobPunchCost = null,
        float $expenses = 0.0,
    ): array {
        if ($ups < 1) {
            throw new InvalidArgumentException('Ups must be at least 1.');
        }

        $numberOfPieces = $noOfSheets * $ups;
        $basePricePerPiece = $totalPricePerSheet / $ups;
        $windowLaborCost = $windowLaborCost ?? 0.0;
        $laceCost = $laceCost ?? 0.0;
        $designingCost = ($designingCostRate ?? 0.0) / $numberOfPieces;
        $optionalPieceCostsTotal = $windowLaborCost + $laceCost;
        $punchCost = match ($punchCostJobType) {
            'repeat_job' => ($repeatJobPunchCost ?? 0.0) / $numberOfPieces,
            'new_job' => $newJobPunchCost ?? 0.0,
            default => throw new InvalidArgumentException('Punch cost job type must be repeat_job or new_job.'),
        };
        $compulsoryPieceCostsTotal = $punchCost + $designingCost + $expenses;
        $totalPieceCost = $basePricePerPiece + $optionalPieceCostsTotal + $compulsoryPieceCostsTotal;

        return [
            'ups' => $ups,
            'number_of_pieces' => $numberOfPieces,
            'base_price_per_piece' => $this->money($basePricePerPiece),
            'window_labor_cost' => $this->money($windowLaborCost),
            'lace_cost' => $this->money($laceCost),
            'designing_cost' => $this->money($designingCost),
            'optional_piece_costs_total' => $this->money($optionalPieceCostsTotal),
            'punch_cost_job_type' => $punchCostJobType,
            'punch_cost' => $this->money($punchCost),
            'expenses' => $this->money($expenses),
            'compulsory_piece_costs_total' => $this->money($compulsoryPieceCostsTotal),
            'total_piece_cost' => $this->money($totalPieceCost),
            'total_cost_for_margin' => $totalPieceCost * $numberOfPieces,
            'total_cost' => $this->money($totalPieceCost * $numberOfPieces),
        ];
    }

    private function money(float $value): float
    {
        return round($value, 2, PHP_ROUND_HALF_UP);
    }
}
