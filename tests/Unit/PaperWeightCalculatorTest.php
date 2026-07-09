<?php

use App\Services\Estimates\PaperWeightCalculator;

test('it calculates paper kilograms for a known sheet count', function () {
    $calculator = new PaperWeightCalculator;

    $result = $calculator->calculate(length: 20, width: 30, gsm: 100, sheetCount: 1000);

    expect($result)->toBe(38.71);
});

test('it returns zero kilograms when sheet count is zero', function () {
    $calculator = new PaperWeightCalculator;

    $result = $calculator->calculate(length: 20, width: 30, gsm: 100, sheetCount: 0);

    expect($result)->toBe(0.0);
});
