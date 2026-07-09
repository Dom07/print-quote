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
        ?float $designingCost = null,
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
        $designingCost = $designingCost ?? 0.0;
        $optionalPieceCostsTotal = $windowLaborCost + $laceCost + $designingCost;
        $punchCost = match ($punchCostJobType) {
            'repeat_job' => ($repeatJobPunchCost ?? 0.0) / $numberOfPieces,
            'new_job' => $newJobPunchCost ?? 0.0,
            default => throw new InvalidArgumentException('Punch cost job type must be repeat_job or new_job.'),
        };
        $compulsoryPieceCostsTotal = $punchCost + $expenses;
        $totalPieceCost = $basePricePerPiece + $optionalPieceCostsTotal + $compulsoryPieceCostsTotal;
        return [
            'ups' => $ups,
            'number_of_pieces' => $numberOfPieces,
            'base_price_per_piece' => $basePricePerPiece,
            'window_labor_cost' => $windowLaborCost,
            'lace_cost' => $laceCost,
            'designing_cost' => $designingCost,
            'optional_piece_costs_total' => $optionalPieceCostsTotal,
            'punch_cost_job_type' => $punchCostJobType,
            'punch_cost' => $punchCost,
            'expenses' => $expenses,
            'compulsory_piece_costs_total' => $compulsoryPieceCostsTotal,
            'total_piece_cost' => $totalPieceCost,
            'total_cost' => $totalPieceCost * $numberOfPieces,
        ];
    }
}
