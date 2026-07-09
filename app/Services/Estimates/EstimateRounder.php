<?php

namespace App\Services\Estimates;

class EstimateRounder
{
    public function money(float $value): float
    {
        return round($value, 2, PHP_ROUND_HALF_UP);
    }

    public function quantity(float $value): float
    {
        return round($value, 2, PHP_ROUND_HALF_UP);
    }
}
