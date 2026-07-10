<?php

namespace App\Services\Estimates;

use InvalidArgumentException;

class PiecePricingCalculator
{
    public function __construct(private ?EstimateRounder $rounder = null)
    {
        $this->rounder ??= new EstimateRounder;
    }

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
        $basePricePerPiece = $this->rounder->money($totalPricePerSheet / $ups);
        $windowLaborCost = $this->rounder->money($windowLaborCost ?? 0.0);
        $laceCost = $this->rounder->money($laceCost ?? 0.0);
        $designingCost = $this->rounder->money(($designingCostRate ?? 0.0) / $numberOfPieces);
        $optionalPieceCostsTotal = $this->rounder->money($windowLaborCost + $laceCost);
        $punchCost = match ($punchCostJobType) {
            'repeat_job' => $this->rounder->money(($repeatJobPunchCost ?? 0.0) / $numberOfPieces),
            'new_job' => $this->rounder->money($newJobPunchCost ?? 0.0),
            default => throw new InvalidArgumentException('Punch cost job type must be repeat_job or new_job.'),
        };
        $expenses = $this->rounder->money($expenses);
        $requiredPieceCostsTotal = $this->rounder->money($punchCost + $designingCost + $expenses);
        $totalPieceCost = $this->rounder->money($basePricePerPiece + $optionalPieceCostsTotal + $requiredPieceCostsTotal);

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
            'required_piece_costs_total' => $requiredPieceCostsTotal,
            'total_piece_cost' => $totalPieceCost,
            'total_cost' => $this->rounder->money($totalPieceCost * $numberOfPieces),
        ];
    }
}
