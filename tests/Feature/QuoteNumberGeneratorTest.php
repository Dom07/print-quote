<?php

use App\Enums\EstimateStatus;
use App\Models\Estimate;
use App\Services\Estimates\QuoteNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns the first quote number when no estimates exist', function () {
    $generator = new QuoteNumberGenerator;

    expect($generator->generate())->toBe('Q-000001');
});

test('it increments from the highest existing quote number', function () {
    Estimate::create([
        'quote_number' => 'Q-000001',
        'status' => EstimateStatus::Draft,
    ]);

    $generator = new QuoteNumberGenerator;

    expect($generator->generate())->toBe('Q-000002');
});

test('it increments larger quote number sequences', function () {
    Estimate::create([
        'quote_number' => 'Q-000125',
        'status' => EstimateStatus::Draft,
    ]);

    $generator = new QuoteNumberGenerator;

    expect($generator->generate())->toBe('Q-000126');
});

test('it keeps the numeric portion padded to six digits', function () {
    Estimate::create([
        'quote_number' => 'Q-000009',
        'status' => EstimateStatus::Draft,
    ]);

    $generator = new QuoteNumberGenerator;

    expect($generator->generate())->toBe('Q-000010');
});

test('it does not modify existing estimates or create a new estimate record', function () {
    $estimate = Estimate::create([
        'quote_number' => 'Q-000001',
        'status' => EstimateStatus::Draft,
        'title' => 'Original title',
    ]);

    $generator = new QuoteNumberGenerator;

    expect($generator->generate())->toBe('Q-000002')
        ->and(Estimate::count())->toBe(1)
        ->and($estimate->fresh()->quote_number)->toBe('Q-000001')
        ->and($estimate->fresh()->title)->toBe('Original title');
});

test('it ignores invalid quote number values when finding the next valid sequence', function () {
    Estimate::create([
        'quote_number' => 'Q-DRAFT',
        'status' => EstimateStatus::Draft,
    ]);

    Estimate::create([
        'quote_number' => 'Q-000003',
        'status' => EstimateStatus::Draft,
    ]);

    $generator = new QuoteNumberGenerator;

    expect($generator->generate())->toBe('Q-000004');
});
