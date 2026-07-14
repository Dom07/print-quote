<?php

use App\Models\MarginSlab;
use App\Services\Estimates\MarginCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it selects slab where total cost is less than or equal to max value', function () {
    $slab = createMarginSlab('Below 20k', null, 20000, 17);
    createMarginSlab('20k to 50k', 20000, 50000, 15);

    $result = (new MarginCalculator)->calculate(20000, 100);

    expect($result['selected_margin_slab_id'])->toBe($slab->id)
        ->and($result['selected_margin_slab_name'])->toBe('Below 20k')
        ->and($result['margin_percentage'])->toBe(17.0);
});

test('it selects slab where total cost is greater than min and less than or equal to max', function () {
    createMarginSlab('Below 20k', null, 20000, 17);
    $slab = createMarginSlab('20k to 50k', 20000, 50000, 15);

    $result = (new MarginCalculator)->calculate(20000.01, 100);

    expect($result['selected_margin_slab_id'])->toBe($slab->id)
        ->and($result['min_value'])->toBe(20000.0)
        ->and($result['max_value'])->toBe(50000.0);
});

test('it selects open ended slab where total cost is greater than min value', function () {
    createMarginSlab('50k to 1.5 Lac', 50000, 150000, 13);
    $slab = createMarginSlab('Above 1.5 Lac', 150000, null, 10);

    $result = (new MarginCalculator)->calculate(150000.01, 100);

    expect($result['selected_margin_slab_id'])->toBe($slab->id)
        ->and($result['max_value'])->toBeNull();
});

test('it handles equality on the lower end side', function () {
    $lowerSlab = createMarginSlab('Below 20k', null, 20000, 17);
    createMarginSlab('20k to 50k', 20000, 50000, 15);

    $result = (new MarginCalculator)->calculate(20000, 100);

    expect($result['selected_margin_slab_id'])->toBe($lowerSlab->id);
});

test('it calculates gross margin amount and total cost with margin', function () {
    createMarginSlab('Below 20k', null, 20000, 17);

    $result = (new MarginCalculator)->calculate(10000, 100);

    expect($result['margin_amount'])->toBe(2048.19)
        ->and($result['total_cost_with_margin'])->toBe(12048.19);
});

test('it allows a zero margin percentage', function () {
    createMarginSlab('Below 20k', null, 20000, 0);

    $result = (new MarginCalculator)->calculate(10000, 100);

    expect($result['margin_amount'])->toBe(0.0)
        ->and($result['total_cost_with_margin'])->toBe(10000.0);
});

test('it calculates gross margin using a decimal margin percentage', function () {
    createMarginSlab('Below 20k', null, 20000, 12.5);

    $result = (new MarginCalculator)->calculate(10000, 100);

    expect($result['margin_amount'])->toBe(1428.57)
        ->and($result['total_cost_with_margin'])->toBe(11428.57);
});

test('it standard half-up rounds money outputs to two decimals', function () {
    createMarginSlab('Below 20k', null, 20000, 20);

    $result = (new MarginCalculator)->calculate(100.06, 100);

    expect($result['margin_amount'])->toBe(25.02)
        ->and($result['total_cost_with_margin'])->toBe(125.08);
});

test('it throws when no slab matches', function () {
    createMarginSlab('Below 20k', null, 20000, 17);

    (new MarginCalculator)->calculate(20000.01, 100);
})->throws(RuntimeException::class, 'No active margin slab matches total cost 20000.01.');

test('it throws when margin percentage is 100 percent', function () {
    createMarginSlab('Below 20k', null, 20000, 100);

    (new MarginCalculator)->calculate(10000, 100);
})->throws(RuntimeException::class, 'Margin percentage must be less than 100%; 100% configured.');

test('it throws when margin percentage is above 100 percent', function () {
    createMarginSlab('Below 20k', null, 20000, 125);

    (new MarginCalculator)->calculate(10000, 100);
})->throws(RuntimeException::class, 'Margin percentage must be less than 100%; 125% configured.');

test('it throws when margin percentage is negative', function () {
    createMarginSlab('Below 20k', null, 20000, -1);

    (new MarginCalculator)->calculate(10000, 100);
})->throws(RuntimeException::class, 'Margin percentage must be zero or greater; -1% configured.');

test('it calculates selling price from total cost with margin divided by number of pieces', function () {
    createMarginSlab('Below 20k', null, 20000, 17);

    $result = (new MarginCalculator)->calculate(10000, 400);

    expect($result['total_cost_with_margin'])->toBe(12048.19)
        ->and($result['selling_price'])->toBe(30.12);
});

test('it uses money rounding for selling price', function () {
    createMarginSlab('Below 20k', null, 20000, 20);

    $result = (new MarginCalculator)->calculate(100.06, 3);

    expect($result['total_cost_with_margin'])->toBe(125.08)
        ->and($result['selling_price'])->toBe(41.69);
});

test('it rejects zero number of pieces', function () {
    createMarginSlab('Below 20k', null, 20000, 17);

    (new MarginCalculator)->calculate(10000, 0);
})->throws(InvalidArgumentException::class, 'Number of pieces must be greater than zero.');

test('it rejects invalid negative number of pieces', function () {
    createMarginSlab('Below 20k', null, 20000, 17);

    (new MarginCalculator)->calculate(10000, -1);
})->throws(InvalidArgumentException::class, 'Number of pieces must be greater than zero.');

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
