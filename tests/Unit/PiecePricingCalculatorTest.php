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

    expect($result['base_price_per_piece'])->toBe(875.63);
});

test('it treats nullable optional costs as zero', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        windowLaborCost: null,
        laceCost: null,
        designingCostRate: null,
    );

    expect($result['window_labor_cost'])->toBe(0.0)
        ->and($result['lace_cost'])->toBe(0.0)
        ->and($result['designing_cost'])->toBe(0.0)
        ->and($result['optional_piece_costs_total'])->toBe(0.0);
});

test('it includes window labor and lace in optional piece costs total', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        windowLaborCost: 1.25,
        laceCost: 0.67,
        designingCostRate: 400,
    );

    expect($result['window_labor_cost'])->toBe(1.25)
        ->and($result['lace_cost'])->toBe(0.67)
        ->and($result['designing_cost'])->toBe(0.1)
        ->and($result['optional_piece_costs_total'])->toBe(1.92);
});

test('it divides designing cost rate by number of pieces', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        designingCostRate: 400,
    );

    expect($result['designing_cost'])->toBe(0.1);
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
        ->and($result['punch_cost'])->toBe(0.06);
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
        designingCostRate: 400,
        expenses: 0.75,
    );

    expect($result['expenses'])->toBe(0.75)
        ->and($result['punch_cost'])->toBe(0.06)
        ->and($result['designing_cost'])->toBe(0.1)
        ->and($result['compulsory_piece_costs_total'])->toBe(0.91);
});

test('it calculates total piece cost', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        windowLaborCost: 1.25,
        laceCost: 0.67,
        designingCostRate: 400,
        punchCostJobType: 'new_job',
        newJobPunchCost: 3,
        expenses: 1.2,
    );

    expect($result['total_piece_cost'])->toBe(881.85);
});

test('it calculates total cost', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.5392,
        windowLaborCost: 1.25,
        laceCost: 0.67,
        designingCostRate: 400,
        punchCostJobType: 'new_job',
        newJobPunchCost: 3,
        expenses: 1.2,
    );

    expect($result['total_cost'])->toBe(3527400.0);
});

test('it does not return a separate raw total cost for margin', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 4,
        totalPricePerSheet: 3502.54,
    );

    expect($result)->not->toHaveKey('total_cost_for_margin')
        ->and($result['total_cost'])->toBe(3502560.0);
});

test('it standard half-up rounds money outputs to two decimals', function () {
    $result = (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 1,
        totalPricePerSheet: 10.005,
        windowLaborCost: 10.004,
        laceCost: 10.015,
        designingCostRate: 10.001,
        punchCostJobType: 'new_job',
        newJobPunchCost: 10.006,
        expenses: 10.011,
    );

    expect($result['base_price_per_piece'])->toBe(10.01)
        ->and($result['window_labor_cost'])->toBe(10.0)
        ->and($result['lace_cost'])->toBe(10.02)
        ->and($result['designing_cost'])->toBe(0.01)
        ->and($result['punch_cost'])->toBe(10.01)
        ->and($result['expenses'])->toBe(10.01);
});

test('it rejects invalid ups', function () {
    (new PiecePricingCalculator)->calculate(
        noOfSheets: 1000,
        ups: 0,
        totalPricePerSheet: 3502.5392,
    );
})->throws(InvalidArgumentException::class, 'Ups must be at least 1.');
