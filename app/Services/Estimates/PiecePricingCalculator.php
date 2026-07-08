<?php

namespace App\Services\Estimates;

use InvalidArgumentException;

class PiecePricingCalculator
{
    public function calculate(int $noOfSheets, int $ups, float $totalPricePerSheet): array
    {
        if ($ups < 1) {
            throw new InvalidArgumentException('Ups must be at least 1.');
        }

        $numberOfPieces = $noOfSheets * $ups;
        $pricePerPiece = $totalPricePerSheet / $ups;

        return [
            'ups' => $ups,
            'number_of_pieces' => $numberOfPieces,
            'price_per_piece' => $pricePerPiece,
        ];
    }
}
