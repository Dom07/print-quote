<?php

namespace App\Services\Estimates;

class PaperWeightCalculator
{
    public function forNoOfSheets(array $input): float
    {
        return $this->calculate(
            (float) $input['length'],
            (float) $input['width'],
            (float) $input['gsm'],
            (int) $input['no_of_sheets'],
        );
    }

    public function forNoOfSheetsWithWastage(array $input): float
    {
        return $this->calculate(
            (float) $input['length'],
            (float) $input['width'],
            (float) $input['gsm'],
            (int) $input['no_of_sheets_with_wastage'],
        );
    }

    public function forNoOfSheetsToProcess(array $input): float
    {
        return $this->calculate(
            (float) $input['length'],
            (float) $input['width'],
            (float) $input['gsm'],
            (int) $input['no_of_sheets_to_process'],
        );
    }

    public function calculate(float $length, float $width, float $gsm, int $sheetCount): float
    {
        if ($sheetCount === 0) {
            return 0.0;
        }

        $value = $length * $width * $gsm;
        $value1 = $value / 3100;
        $value2 = $value1 / 5;
        $value3 = 100 / $value2;

        return $sheetCount / $value3;
    }
}
