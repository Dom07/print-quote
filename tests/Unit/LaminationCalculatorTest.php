<?php

use App\Models\PricingCategory;
use App\Models\PricingItem;
use App\Models\PricingRule;
use App\Services\Estimates\LaminationCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('front only without a selected item returns null result', function () {
    $result = (new LaminationCalculator)->calculate(
        mode: 'front_only',
        frontPricingItem: null,
        backPricingItem: null,
        quantity: 5000,
        length: 20,
        width: 30,
    );

    expect($result['front'])->toBeNull()
        ->and($result['combined_value'])->toBe(0.0);
});

test('bopp one side resolves lower threshold coefficient and value', function () {
    [$bopp] = seedLaminationCalculatorItems();

    $result = (new LaminationCalculator)->calculate('front_only', $bopp, null, 5000, 20, 30);

    expect($result['front']['coefficient'])->toBe(0.37)
        ->and($result['front']['value'])->toBe(2.22)
        ->and($result['front']['source'])->toBe('rule')
        ->and($result['combined_value'])->toBe(2.22);
});

test('bopp one side resolves above threshold coefficient and value', function () {
    [$bopp] = seedLaminationCalculatorItems();

    $result = (new LaminationCalculator)->calculate('front_only', $bopp, null, 5001, 20, 30);

    expect($result['front']['coefficient'])->toBe(0.35)
        ->and($result['front']['value'])->toBe(2.1);
});

test('matte one side resolves lower threshold coefficient and value', function () {
    [, $matte] = seedLaminationCalculatorItems();

    $result = (new LaminationCalculator)->calculate('front_only', $matte, null, 5000, 20, 30);

    expect($result['front']['coefficient'])->toBe(0.47)
        ->and($result['front']['value'])->toBe(2.82);
});

test('matte one side resolves above threshold coefficient and value', function () {
    [, $matte] = seedLaminationCalculatorItems();

    $result = (new LaminationCalculator)->calculate('front_only', $matte, null, 5001, 20, 30);

    expect($result['front']['coefficient'])->toBe(0.45)
        ->and($result['front']['value'])->toBe(2.7);
});

test('both sides bopp and bopp combines values', function () {
    [$bopp] = seedLaminationCalculatorItems();

    $result = (new LaminationCalculator)->calculate('both_sides', $bopp, $bopp, 5000, 20, 30);

    expect($result['combined_value'])->toBe(4.44);
});

test('both sides matte and matte combines values', function () {
    [, $matte] = seedLaminationCalculatorItems();

    $result = (new LaminationCalculator)->calculate('both_sides', $matte, $matte, 5000, 20, 30);

    expect($result['combined_value'])->toBe(5.64);
});

test('both sides bopp and matte combines values', function () {
    [$bopp, $matte] = seedLaminationCalculatorItems();

    $result = (new LaminationCalculator)->calculate('both_sides', $bopp, $matte, 5000, 20, 30);

    expect($result['combined_value'])->toBe(5.04);
});

test('inactive rules are ignored', function () {
    [$bopp] = seedLaminationCalculatorItems();

    PricingRule::create([
        'pricing_item_id' => $bopp->id,
        'name' => 'Inactive Rule',
        'min_value' => null,
        'max_value' => '5000.0000',
        'rate' => '9.0000',
        'rate_type' => 'formula_coefficient',
        'sort_order' => 0,
        'is_active' => false,
    ]);

    $result = (new LaminationCalculator)->calculate('front_only', $bopp, null, 5000, 20, 30);

    expect($result['front']['coefficient'])->toBe(0.37);
});

test('fallback item rate works if no rules match', function () {
    $category = PricingCategory::create([
        'name' => 'Lamination',
        'slug' => 'lamination',
        'is_active' => true,
    ]);

    $item = PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'Fallback Lamination',
        'slug' => 'fallback-lamination',
        'rate' => '0.2500',
        'rate_type' => 'formula_coefficient',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    $result = (new LaminationCalculator)->calculate('front_only', $item, null, 5000, 20, 30);

    expect($result['front']['source'])->toBe('item')
        ->and($result['front']['coefficient'])->toBe(0.25)
        ->and($result['front']['value'])->toBe(1.5);
});

test('exact threshold quantity uses lower rule', function () {
    [$bopp] = seedLaminationCalculatorItems();

    $result = (new LaminationCalculator)->calculate('front_only', $bopp, null, 5000, 20, 30);

    expect($result['front']['coefficient'])->toBe(0.37);
});

function seedLaminationCalculatorItems(): array
{
    $category = PricingCategory::create([
        'name' => 'Lamination',
        'slug' => 'lamination',
        'is_active' => true,
    ]);

    $bopp = PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'BOPP Lamination',
        'slug' => 'bopp-lamination',
        'rate' => null,
        'rate_type' => 'formula_coefficient',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $bopp->id,
        'name' => 'BOPP Up to 5000',
        'min_value' => null,
        'max_value' => '5000.0000',
        'rate' => '0.3700',
        'rate_type' => 'formula_coefficient',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $bopp->id,
        'name' => 'BOPP Above 5000',
        'min_value' => '5000.0000',
        'max_value' => null,
        'rate' => '0.3500',
        'rate_type' => 'formula_coefficient',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $matte = PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'Matte Lamination',
        'slug' => 'matte-lamination',
        'rate' => null,
        'rate_type' => 'formula_coefficient',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $matte->id,
        'name' => 'Matte Up to 5000',
        'min_value' => null,
        'max_value' => '5000.0000',
        'rate' => '0.4700',
        'rate_type' => 'formula_coefficient',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $matte->id,
        'name' => 'Matte Above 5000',
        'min_value' => '5000.0000',
        'max_value' => null,
        'rate' => '0.4500',
        'rate_type' => 'formula_coefficient',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    return [$bopp, $matte];
}
