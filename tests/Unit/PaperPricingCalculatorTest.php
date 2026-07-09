<?php

use App\Services\Estimates\PaperPricingCalculator;

test('it calculates paper pricing values', function () {
    $calculator = new PaperPricingCalculator;

    $result = $calculator->calculate(
        selectedPaperRate: 41.5,
        interestPercentage: 1.25,
        kgsOfOrder: 10,
        noOfSheets: 1000,
    );

    expect($result['selected_paper_rate'])->toBe(41.5)
        ->and($result['interest_percentage'])->toBe(1.25)
        ->and($result['updated_paper_rate'])->toBe(42.02)
        ->and($result['price_per_sheet'])->toBe(0.42);
});

test('zero interest keeps updated paper rate equal to selected paper rate', function () {
    $calculator = new PaperPricingCalculator;

    $result = $calculator->calculate(
        selectedPaperRate: 41.5,
        interestPercentage: 0,
        kgsOfOrder: 10,
        noOfSheets: 1000,
    );

    expect($result['updated_paper_rate'])->toBe(41.5);
});

test('it throws when no of sheets is zero', function () {
    $calculator = new PaperPricingCalculator;

    $calculator->calculate(
        selectedPaperRate: 41.5,
        interestPercentage: 1.25,
        kgsOfOrder: 10,
        noOfSheets: 0,
    );
})->throws(InvalidArgumentException::class);
