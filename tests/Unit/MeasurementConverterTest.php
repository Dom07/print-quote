<?php

use App\Services\Estimates\MeasurementConverter;

test('it returns inch dimensions unchanged', function () {
    $result = (new MeasurementConverter)->dimensionsToInches(15, 25, 'in');

    expect($result['length'])->toBe(15.0)
        ->and($result['width'])->toBe(25.0);
});

test('it converts centimeter dimensions to inches', function () {
    $result = (new MeasurementConverter)->dimensionsToInches(38.1, 63.5, 'cm');

    expect($result['length'])->toBe(15.0)
        ->and($result['width'])->toBe(25.0);
});

test('it preserves decimal centimeter precision', function () {
    $result = (new MeasurementConverter)->dimensionsToInches(12.34, 56.78, 'cm');

    expect($result['length'])->toBeFloat()
        ->and($result['length'])->toBe(12.34 / 2.54)
        ->and($result['width'])->toBe(56.78 / 2.54);
});

test('it rejects unsupported units', function () {
    (new MeasurementConverter)->dimensionsToInches(15, 25, 'mm');
})->throws(InvalidArgumentException::class, 'Unsupported measurement unit [mm].');

test('it rejects zero length', function () {
    (new MeasurementConverter)->dimensionsToInches(0, 25, 'in');
})->throws(InvalidArgumentException::class, 'Length and width must be greater than zero.');

test('it rejects zero width', function () {
    (new MeasurementConverter)->dimensionsToInches(15, 0, 'in');
})->throws(InvalidArgumentException::class, 'Length and width must be greater than zero.');

test('it rejects negative length', function () {
    (new MeasurementConverter)->dimensionsToInches(-1, 25, 'in');
})->throws(InvalidArgumentException::class, 'Length and width must be greater than zero.');

test('it rejects negative width', function () {
    (new MeasurementConverter)->dimensionsToInches(15, -1, 'in');
})->throws(InvalidArgumentException::class, 'Length and width must be greater than zero.');
