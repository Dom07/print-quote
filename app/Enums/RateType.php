<?php

namespace App\Enums;

enum RateType: string
{
    case PerSheet = 'per_sheet';
    case PerPiece = 'per_piece';
    case Flat = 'flat';
    case Percentage = 'percentage';
    case FormulaCoefficient = 'formula_coefficient';
    case MinimumFlat = 'minimum_flat';
}
