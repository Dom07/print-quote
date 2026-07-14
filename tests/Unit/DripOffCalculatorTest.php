<?php

use App\Models\PricingCategory;
use App\Models\PricingItem;
use App\Services\Estimates\DripOffCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it calculates drip off rate when base cost exceeds minimum', function () {
    seedDripOffCalculatorItems();

    $result = (new DripOffCalculator)->calculate(
        length: 20,
        width: 30,
        quantity: 1000,
        flatAddOnAmount: 1600,
        flatAddOnName: 'New Job with Pasting',
    );

    expect($result['flat_add_on_name'])->toBe('New Job with Pasting')
        ->and($result['base_rate_per_sheet'])->toBe(4.5)
        ->and($result['base_cost'])->toBe(4500.0)
        ->and($result['minimum_adjusted_base_cost'])->toBe(4500.0)
        ->and($result['minimum_adjusted_base_rate_per_sheet'])->toBe(4.5)
        ->and($result['flat_add_on_amount'])->toBe(1600.0)
        ->and($result['flat_add_on_rate_per_sheet'])->toBe(1.6)
        ->and($result['final_rate_per_sheet'])->toBe(6.1);
});

test('it calculates drip off rate when minimum cost applies', function () {
    seedDripOffCalculatorItems();

    $result = (new DripOffCalculator)->calculate(
        length: 10,
        width: 10,
        quantity: 1000,
        flatAddOnAmount: 300,
        flatAddOnName: 'Repeat Job with Pasting',
    );

    expect($result['flat_add_on_name'])->toBe('Repeat Job with Pasting')
        ->and($result['base_rate_per_sheet'])->toBe(0.75)
        ->and($result['base_cost'])->toBe(750.0)
        ->and($result['minimum_adjusted_base_cost'])->toBe(2500.0)
        ->and($result['minimum_adjusted_base_rate_per_sheet'])->toBe(2.5)
        ->and($result['flat_add_on_amount'])->toBe(300.0)
        ->and($result['flat_add_on_rate_per_sheet'])->toBe(0.3)
        ->and($result['final_rate_per_sheet'])->toBe(2.8);
});

test('it divides flat add on amount by quantity', function () {
    seedDripOffCalculatorItems();

    $result = (new DripOffCalculator)->calculate(
        length: 20,
        width: 30,
        quantity: 2000,
        flatAddOnAmount: 1600,
        flatAddOnName: 'New Job with Pasting',
    );

    expect($result['flat_add_on_rate_per_sheet'])->toBe(0.8)
        ->and($result['final_rate_per_sheet'])->toBe(5.3);
});

test('it rejects zero quantity', function () {
    seedDripOffCalculatorItems();

    (new DripOffCalculator)->calculate(
        length: 20,
        width: 30,
        quantity: 0,
        flatAddOnAmount: 1600,
        flatAddOnName: 'New Job with Pasting',
    );
})->throws(InvalidArgumentException::class);

function seedDripOffCalculatorItems(): void
{
    $category = PricingCategory::create([
        'name' => 'Drip Off',
        'slug' => 'drip-off',
        'is_active' => true,
    ]);

    PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'Drip Off Coefficient',
        'slug' => 'drip-off-coefficient',
        'rate' => '0.7500',
        'rate_type' => 'formula_coefficient',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'Drip Off Minimum Charge',
        'slug' => 'drip-off-minimum-charge',
        'rate' => '2500.0000',
        'rate_type' => 'minimum_flat',
        'is_selectable' => false,
        'is_active' => true,
    ]);

    PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'New Job with Pasting',
        'slug' => 'drip-off-new-job-with-pasting',
        'rate' => '1600.0000',
        'rate_type' => 'flat',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'Repeat Job with Pasting',
        'slug' => 'drip-off-repeat-job-with-pasting',
        'rate' => '300.0000',
        'rate_type' => 'flat',
        'is_selectable' => true,
        'is_active' => true,
    ]);
}
