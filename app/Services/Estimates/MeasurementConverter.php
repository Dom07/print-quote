<?php

namespace App\Services\Estimates;

use InvalidArgumentException;

class MeasurementConverter
{
    private const CENTIMETERS_PER_INCH = 2.54;

    public function dimensionsToInches(float $length, float $width, string $unit): array
    {
        if ($length <= 0 || $width <= 0) {
            throw new InvalidArgumentException('Length and width must be greater than zero.');
        }

        return match ($unit) {
            'in' => [
                'length' => $length,
                'width' => $width,
            ],
            'cm' => [
                'length' => $length / self::CENTIMETERS_PER_INCH,
                'width' => $width / self::CENTIMETERS_PER_INCH,
            ],
            default => throw new InvalidArgumentException("Unsupported measurement unit [{$unit}]."),
        };
    }
}
