<?php

use App\Services\Estimates\TotalPricePerSheetCalculator;

test('it adds only required base components', function () {
    $result = (new TotalPricePerSheetCalculator)->calculate(
        paperPricePerSheet: 1.7892,
        printingCost: 2500,
        inkCost: 375.5,
    );

    expect($result['components']['paper_price_per_sheet'])->toBe(1.79)
        ->and($result['components']['printing_cost'])->toBe(2500.0)
        ->and($result['components']['ink_cost'])->toBe(375.5)
        ->and($result['components']['foiling_cost'])->toBe(0.0)
        ->and($result['components']['punching_rate'])->toBe(0.0)
        ->and($result['components']['lamination_value'])->toBe(0.0)
        ->and($result['components']['spot_uv_value'])->toBe(0.0)
        ->and($result['components']['drip_off_rate'])->toBe(0.0)
        ->and($result['total_price_per_sheet'])->toBe(2877.29);
});

test('it adds all optional components', function () {
    $result = (new TotalPricePerSheetCalculator)->calculate(
        paperPricePerSheet: 1.7892,
        printingCost: 2500,
        inkCost: 375.5,
        foilingCost: 625.25,
        punchingRate: 1,
        laminationValue: 2.22,
        spotUvValue: 1.25,
        dripOffRate: 5.8,
    );

    expect($result['total_price_per_sheet'])->toBe(3512.81);
});

test('it returns the expected component keys', function () {
    $result = (new TotalPricePerSheetCalculator)->calculate(
        paperPricePerSheet: 1,
        printingCost: 2,
        inkCost: 3,
    );

    expect(array_keys($result['components']))->toBe([
        'paper_price_per_sheet',
        'printing_cost',
        'ink_cost',
        'foiling_cost',
        'punching_rate',
        'lamination_value',
        'spot_uv_value',
        'drip_off_rate',
    ]);
});
