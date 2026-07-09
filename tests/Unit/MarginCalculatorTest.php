<?php

use App\Models\MarginSlab;
use App\Services\Estimates\MarginCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it selects slab where total cost is less than or equal to max value', function () {
    $slab = createMarginSlab('Below 20k', null, 20000, 17);
    createMarginSlab('20k to 50k', 20000, 50000, 15);

    $result = (new MarginCalculator)->calculate(20000);

    expect($result['selected_margin_slab_id'])->toBe($slab->id)
        ->and($result['selected_margin_slab_name'])->toBe('Below 20k')
        ->and($result['margin_percentage'])->toBe(17.0);
});

test('it selects slab where total cost is greater than min and less than or equal to max', function () {
    createMarginSlab('Below 20k', null, 20000, 17);
    $slab = createMarginSlab('20k to 50k', 20000, 50000, 15);

    $result = (new MarginCalculator)->calculate(20000.01);

    expect($result['selected_margin_slab_id'])->toBe($slab->id)
        ->and($result['min_value'])->toBe(20000.0)
        ->and($result['max_value'])->toBe(50000.0);
});

test('it selects open ended slab where total cost is greater than min value', function () {
    createMarginSlab('50k to 1.5 Lac', 50000, 150000, 13);
    $slab = createMarginSlab('Above 1.5 Lac', 150000, null, 10);

    $result = (new MarginCalculator)->calculate(150000.01);

    expect($result['selected_margin_slab_id'])->toBe($slab->id)
        ->and($result['max_value'])->toBeNull();
});

test('it handles equality on the lower end side', function () {
    $lowerSlab = createMarginSlab('Below 20k', null, 20000, 17);
    createMarginSlab('20k to 50k', 20000, 50000, 15);

    $result = (new MarginCalculator)->calculate(20000);

    expect($result['selected_margin_slab_id'])->toBe($lowerSlab->id);
});

test('it calculates margin amount and total cost with margin', function () {
    createMarginSlab('Below 20k', null, 20000, 17);

    $result = (new MarginCalculator)->calculate(10000);

    expect($result['margin_amount'])->toBe(1700.0)
        ->and($result['total_cost_with_margin'])->toBe(11700.0);
});

test('it standard half-up rounds money outputs to two decimals', function () {
    createMarginSlab('Below 20k', null, 20000, 12.5);

    $result = (new MarginCalculator)->calculate(100.04);

    expect($result['margin_amount'])->toBe(12.51)
        ->and($result['total_cost_with_margin'])->toBe(112.55);
});

test('it throws when no slab matches', function () {
    createMarginSlab('Below 20k', null, 20000, 17);

    (new MarginCalculator)->calculate(20000.01);
})->throws(RuntimeException::class, 'No active margin slab matches total cost 20000.01.');

function createMarginSlab(string $name, ?float $minAmount, ?float $maxAmount, float $marginPercentage): MarginSlab
{
    return MarginSlab::create([
        'name' => $name,
        'min_amount' => $minAmount,
        'max_amount' => $maxAmount,
        'margin_percentage' => $marginPercentage,
        'sort_order' => MarginSlab::count() + 1,
        'is_active' => true,
    ]);
}
