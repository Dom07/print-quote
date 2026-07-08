<?php

use App\Services\Estimates\PiecePricingCalculator;

test('it calculates number of pieces', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
    );

    expect($result['ups'])->toBe(4)
        ->and($result['number_of_pieces'])->toBe(4000);
});

test('it calculates price per piece', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
    );

    expect($result['price_per_piece'])->toBe(875.6348);
});

test('it rejects invalid ups', function () {
    (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 0,
        totalPricePerSheet: 3502.5392,
    );
})->throws(InvalidArgumentException::class, 'Ups must be at least 1.');
