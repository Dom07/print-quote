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

test('it calculates base price per piece', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
    );

    expect($result['base_price_per_piece'])->toBe(875.6348);
});

test('it treats nullable optional costs as zero', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        windowLaborCost: null,
        laceCost: null,
        designingCost: null,
    );

    expect($result['window_labor_cost'])->toBe(0.0)
        ->and($result['lace_cost'])->toBe(0.0)
        ->and($result['designing_cost'])->toBe(0.0)
        ->and($result['optional_piece_costs_total'])->toBe(0.0);
});

test('it includes window labor lace and designing in optional piece costs total', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        windowLaborCost: 1.25,
        laceCost: 0.67,
        designingCost: 2.5,
    );

    expect($result['window_labor_cost'])->toBe(1.25)
        ->and($result['lace_cost'])->toBe(0.67)
        ->and($result['designing_cost'])->toBe(2.5)
        ->and(round($result['optional_piece_costs_total'], 4))->toBe(4.42);
});

test('it divides repeat job punch cost by number of pieces', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        punchCostJobType: 'repeat_job',
        repeatJobPunchCost: 250,
    );

    expect($result['punch_cost_job_type'])->toBe('repeat_job')
        ->and($result['punch_cost'])->toBe(0.0625);
});

test('it uses new job punch cost as a direct per piece value', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        punchCostJobType: 'new_job',
        newJobPunchCost: 3.25,
    );

    expect($result['punch_cost_job_type'])->toBe('new_job')
        ->and($result['punch_cost'])->toBe(3.25);
});

test('it includes punch cost and expenses in compulsory piece costs total', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        punchCostJobType: 'repeat_job',
        repeatJobPunchCost: 250,
        expenses: 0.75,
    );

    expect($result['expenses'])->toBe(0.75)
        ->and($result['punch_cost'])->toBe(0.0625)
        ->and($result['compulsory_piece_costs_total'])->toBe(0.8125);
});

test('it calculates total piece cost', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        windowLaborCost: 1.25,
        laceCost: 0.67,
        designingCost: 2.5,
        punchCostJobType: 'new_job',
        newJobPunchCost: 3,
        expenses: 1.2,
    );

    expect(round($result['total_piece_cost'], 4))->toBe(884.2548);
});

test('it calculates total cost', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        windowLaborCost: 1.25,
        laceCost: 0.67,
        designingCost: 2.5,
        punchCostJobType: 'new_job',
        newJobPunchCost: 3,
        expenses: 1.2,
    );

    expect(round($result['total_cost'], 4))->toBe(3537019.2);
});

test('it rejects invalid ups', function () {
    (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 0,
        totalPricePerSheet: 3502.5392,
    );
})->throws(InvalidArgumentException::class, 'Ups must be at least 1.');
