<?php

use App\Enums\EstimateStatus;
use App\Enums\RateType;
use App\Models\Estimate;
use App\Models\EstimateCostComponent;
use App\Models\EstimateInput;
use App\Models\EstimateTotal;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('estimate relationships resolve to persisted estimate records', function () {
    $estimate = Estimate::create([
        'quote_number' => 'Q-TEST-001',
        'status' => EstimateStatus::Draft,
        'title' => 'Sample Quote',
    ]);

    $input = EstimateInput::create([
        'estimate_id' => $estimate->id,
        'product_type' => 'box',
        'pieces_per_sheet' => 4,
        'raw_inputs' => ['length' => 20, 'width' => 30],
    ]);

    $component = EstimateCostComponent::create([
        'estimate_id' => $estimate->id,
        'name' => 'Paper',
        'rate_type' => RateType::PerSheet,
        'rate' => '1.2500',
        'is_overridden' => true,
    ]);

    $totals = EstimateTotal::create([
        'estimate_id' => $estimate->id,
        'grand_total' => '1250.0000',
    ]);

    expect($estimate->input->is($input))->toBeTrue()
        ->and($estimate->costComponents)->toHaveCount(1)
        ->and($estimate->costComponents->first()->is($component))->toBeTrue()
        ->and($estimate->totals->is($totals))->toBeTrue();
});

test('estimate persistence models expose expected inverse relationships and casts', function () {
    $estimate = Estimate::create([
        'quote_number' => 'Q-TEST-002',
        'status' => EstimateStatus::Draft,
        'quoted_at' => '2026-08-26 10:30:00',
    ]);

    $input = EstimateInput::create([
        'estimate_id' => $estimate->id,
        'pieces_per_sheet' => 8,
        'production_sheets' => 100,
        'wastage_sheets' => 10,
        'total_sheets_with_wastage' => 110,
        'sheets_used_for_process' => 120,
        'raw_inputs' => ['gsm' => 300],
    ]);

    $component = EstimateCostComponent::create([
        'estimate_id' => $estimate->id,
        'name' => 'Lamination',
        'rate_type' => RateType::FormulaCoefficient,
        'rate' => '0.3500',
        'original_rate' => '0.3700',
        'is_overridden' => false,
    ]);

    $totals = EstimateTotal::create([
        'estimate_id' => $estimate->id,
        'cost_per_sheet' => '12.3456',
        'cost_per_piece' => '3.0864',
        'subtotal' => '1234.5600',
        'margin_percentage' => '10.0000',
        'margin_amount' => '123.4560',
        'grand_total' => '1358.0160',
    ]);

    expect($estimate->status)->toBe(EstimateStatus::Draft)
        ->and($estimate->quoted_at)->not->toBeNull()
        ->and($input->estimate->is($estimate))->toBeTrue()
        ->and($input->raw_inputs)->toBe(['gsm' => 300])
        ->and($input->pieces_per_sheet)->toBeInt()
        ->and($component->estimate->is($estimate))->toBeTrue()
        ->and($component->rate_type)->toBe(RateType::FormulaCoefficient)
        ->and($component->rate)->toBe('0.3500')
        ->and($component->original_rate)->toBe('0.3700')
        ->and($component->is_overridden)->toBeFalse()
        ->and($totals->estimate->is($estimate))->toBeTrue()
        ->and($totals->grand_total)->toBe('1358.0160');
});

test('estimate cost components do not expose live pricing relationships', function () {
    expect(method_exists(EstimateCostComponent::class, 'pricingItem'))->toBeFalse();
});
