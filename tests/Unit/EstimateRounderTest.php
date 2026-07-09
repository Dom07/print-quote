<?php

use App\Services\Estimates\EstimateRounder;

test('it standard half-up rounds money values to two decimals', function (float $value, float $expected) {
    expect((new EstimateRounder)->money($value))->toBe($expected);
})->with([
    [10.001, 10.00],
    [10.004, 10.00],
    [10.005, 10.01],
    [10.006, 10.01],
    [10.015, 10.02],
]);

test('it standard half-up rounds quantity values to two decimals', function (float $value, float $expected) {
    expect((new EstimateRounder)->quantity($value))->toBe($expected);
})->with([
    [10.001, 10.00],
    [10.004, 10.00],
    [10.005, 10.01],
    [10.006, 10.01],
    [10.015, 10.02],
]);
