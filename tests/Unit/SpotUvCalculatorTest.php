<?php

use App\Models\PricingCategory;
use App\Models\PricingItem;
use App\Models\PricingRule;
use App\Services\Estimates\SpotUvCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('null spot uv item returns null', function () {
    $result = (new SpotUvCalculator)->calculate(null, 1000);

    expect($result)->toBeNull();
});

test('spot uv below threshold uses minimum divided by quantity', function () {
    [$spotUv] = seedSpotUvCalculatorItems();

    $result = (new SpotUvCalculator)->calculate($spotUv, 999);

    expect($result['name'])->toBe('Spot UV')
        ->and($result['resolved_amount'])->toBe(1250.0)
        ->and($result['calculation_type'])->toBe('minimum_divided_by_quantity')
        ->and(abs($result['value'] - (1250 / 999)))->toBeLessThan(0.000001);
});

test('spot uv exact threshold uses minimum divided by quantity', function () {
    [$spotUv] = seedSpotUvCalculatorItems();

    $result = (new SpotUvCalculator)->calculate($spotUv, 1000);

    expect($result['resolved_amount'])->toBe(1250.0)
        ->and($result['value'])->toBe(1.25);
});

test('spot uv above threshold uses per sheet value', function () {
    [$spotUv] = seedSpotUvCalculatorItems();

    $result = (new SpotUvCalculator)->calculate($spotUv, 1001);

    expect($result['resolved_amount'])->toBe(1.25)
        ->and($result['calculation_type'])->toBe('per_sheet')
        ->and($result['value'])->toBe(1.25);
});

test('raised uv below threshold uses minimum divided by quantity', function () {
    [, $raisedUv] = seedSpotUvCalculatorItems();

    $result = (new SpotUvCalculator)->calculate($raisedUv, 999);

    expect($result['name'])->toBe('Raised UV')
        ->and($result['resolved_amount'])->toBe(2800.0)
        ->and($result['calculation_type'])->toBe('minimum_divided_by_quantity')
        ->and(abs($result['value'] - (2800 / 999)))->toBeLessThan(0.000001);
});

test('raised uv exact threshold uses minimum divided by quantity', function () {
    [, $raisedUv] = seedSpotUvCalculatorItems();

    $result = (new SpotUvCalculator)->calculate($raisedUv, 1000);

    expect($result['resolved_amount'])->toBe(2800.0)
        ->and($result['value'])->toBe(2.8);
});

test('raised uv above threshold uses per sheet value', function () {
    [, $raisedUv] = seedSpotUvCalculatorItems();

    $result = (new SpotUvCalculator)->calculate($raisedUv, 1001);

    expect($result['resolved_amount'])->toBe(2.8)
        ->and($result['calculation_type'])->toBe('per_sheet')
        ->and($result['value'])->toBe(2.8);
});

test('inactive rules are ignored', function () {
    [$spotUv] = seedSpotUvCalculatorItems();

    PricingRule::create([
        'pricing_item_id' => $spotUv->id,
        'name' => 'Inactive Rule',
        'min_value' => null,
        'max_value' => '1000.0000',
        'rate' => '9999.0000',
        'rate_type' => 'minimum_flat',
        'sort_order' => 0,
        'is_active' => false,
    ]);

    $result = (new SpotUvCalculator)->calculate($spotUv, 1000);

    expect($result['resolved_amount'])->toBe(1250.0);
});

test('fallback item rate works if no rule matches', function () {
    $category = PricingCategory::create([
        'name' => 'Spot UV',
        'slug' => 'spot-uv',
        'is_active' => true,
    ]);

    $item = PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'Fallback UV',
        'slug' => 'fallback-uv',
        'rate' => '3.2500',
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    $result = (new SpotUvCalculator)->calculate($item, 1001);

    expect($result['source'])->toBe('item')
        ->and($result['resolved_amount'])->toBe(3.25)
        ->and($result['value'])->toBe(3.25);
});

test('quantity zero throws exception', function () {
    [$spotUv] = seedSpotUvCalculatorItems();

    (new SpotUvCalculator)->calculate($spotUv, 0);
})->throws(InvalidArgumentException::class);

function seedSpotUvCalculatorItems(): array
{
    $category = PricingCategory::create([
        'name' => 'Spot UV',
        'slug' => 'spot-uv',
        'is_active' => true,
    ]);

    $spotUv = PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'Spot UV',
        'slug' => 'spot-uv',
        'rate' => null,
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $spotUv->id,
        'name' => 'Minimum Charge Below 1000 Sheets',
        'min_value' => null,
        'max_value' => '1000.0000',
        'rate' => '1250.0000',
        'rate_type' => 'minimum_flat',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $spotUv->id,
        'name' => 'Per Sheet 1000 And Above',
        'min_value' => '1000.0000',
        'max_value' => null,
        'rate' => '1.2500',
        'rate_type' => 'per_sheet',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $raisedUv = PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'Raised UV',
        'slug' => 'raised-uv',
        'rate' => null,
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $raisedUv->id,
        'name' => 'Minimum Charge Below 1000 Sheets',
        'min_value' => null,
        'max_value' => '1000.0000',
        'rate' => '2800.0000',
        'rate_type' => 'minimum_flat',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $raisedUv->id,
        'name' => 'Per Sheet 1000 And Above',
        'min_value' => '1000.0000',
        'max_value' => null,
        'rate' => '2.8000',
        'rate_type' => 'per_sheet',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    return [$spotUv, $raisedUv];
}
