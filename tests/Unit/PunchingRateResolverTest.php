<?php

use App\Models\PricingCategory;
use App\Models\PricingItem;
use App\Models\PricingRule;
use App\Services\Estimates\PunchingRateResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('standard punching resolves to minimum flat at one sheet', function () {
    [$standardPunching] = seedPunchingResolverItems();

    $result = (new PunchingRateResolver)->resolve($standardPunching, 1);

    expect($result['name'])->toBe('Standard Punching')
        ->and($result['source'])->toBe('rule')
        ->and($result['rate_type'])->toBe('minimum_flat')
        ->and($result['pricing_mode'])->toBe('minimum_flat')
        ->and($result['configured_rate'])->toBe(1000.0)
        ->and($result['rate'])->toBe(1000.0)
        ->and($result['total_charge'])->toBe(1000.0)
        ->and($result['matched_rule_name'])->toBe('Below 1000 Sheets');
});

test('standard punching resolves to minimum flat at five hundred sheets', function () {
    [$standardPunching] = seedPunchingResolverItems();

    $result = (new PunchingRateResolver)->resolve($standardPunching, 500);

    expect($result['rate_type'])->toBe('minimum_flat')
        ->and($result['configured_rate'])->toBe(1000.0)
        ->and($result['rate'])->toBe(2.0)
        ->and($result['total_charge'])->toBe(1000.0);
});

test('standard punching minimum flat effective rate is not rounded prematurely', function () {
    [$standardPunching] = seedPunchingResolverItems();

    $result = (new PunchingRateResolver)->resolve($standardPunching, 999);

    expect($result['rate_type'])->toBe('minimum_flat')
        ->and($result['total_charge'])->toBe(1000.0)
        ->and(round($result['rate'] * 999, 2, PHP_ROUND_HALF_UP))->toBe(1000.0)
        ->and($result['rate'])->toBeGreaterThan(1.0);
});

test('standard punching resolves to one rupee at exactly one thousand sheets', function () {
    [$standardPunching] = seedPunchingResolverItems();

    $result = (new PunchingRateResolver)->resolve($standardPunching, 1000);

    expect($result['rate_type'])->toBe('per_sheet')
        ->and($result['pricing_mode'])->toBe('per_sheet')
        ->and($result['rate'])->toBe(1.0)
        ->and($result['total_charge'])->toBe(1000.0)
        ->and($result['matched_rule_name'])->toBe('1000 to 2000 Sheets');
});

test('standard punching resolves to one rupee below two thousand sheets', function () {
    [$standardPunching] = seedPunchingResolverItems();

    $result = (new PunchingRateResolver)->resolve($standardPunching, 1999);

    expect($result['name'])->toBe('Standard Punching')
        ->and($result['source'])->toBe('rule')
        ->and($result['rate'])->toBe(1.0)
        ->and($result['total_charge'])->toBe(1999.0);
});

test('standard punching resolves to one rupee at exactly two thousand sheets', function () {
    [$standardPunching] = seedPunchingResolverItems();

    $result = (new PunchingRateResolver)->resolve($standardPunching, 2000);

    expect($result['rate'])->toBe(1.0)
        ->and($result['total_charge'])->toBe(2000.0);
});

test('standard punching resolves to sixty paise above two thousand sheets', function () {
    [$standardPunching] = seedPunchingResolverItems();

    $result = (new PunchingRateResolver)->resolve($standardPunching, 2001);

    expect($result['rate'])->toBe(0.6)
        ->and($result['total_charge'])->toBe(1200.6);
});

test('standard punching resolves to sixty paise at three thousand sheets', function () {
    [$standardPunching] = seedPunchingResolverItems();

    $result = (new PunchingRateResolver)->resolve($standardPunching, 3000);

    expect($result['rate'])->toBe(0.6)
        ->and($result['total_charge'])->toBe(1800.0);
});

test('complicated punching resolves to item rate', function () {
    [, $complicatedPunching] = seedPunchingResolverItems();

    $result = (new PunchingRateResolver)->resolve($complicatedPunching, 2001);

    expect($result['name'])->toBe('Complicated Punching')
        ->and($result['source'])->toBe('item')
        ->and($result['rate'])->toBe(0.7)
        ->and($result['configured_rate'])->toBe(0.7)
        ->and($result['rate_type'])->toBe('per_sheet')
        ->and($result['total_charge'])->toBe(1400.7);
});

test('null punching item resolves to null', function () {
    $result = (new PunchingRateResolver)->resolve(null, 0);

    expect($result)->toBeNull();
});

test('zero quantity throws when punching item is provided', function () {
    [$standardPunching] = seedPunchingResolverItems();

    (new PunchingRateResolver)->resolve($standardPunching, 0);
})->throws(InvalidArgumentException::class, 'Punching quantity must be at least 1.');

test('negative quantity throws when punching item is provided', function () {
    [$standardPunching] = seedPunchingResolverItems();

    (new PunchingRateResolver)->resolve($standardPunching, -1);
})->throws(InvalidArgumentException::class, 'Punching quantity must be at least 1.');

test('inactive rules are ignored', function () {
    [$standardPunching] = seedPunchingResolverItems();

    PricingRule::create([
        'pricing_item_id' => $standardPunching->id,
        'name' => 'Inactive Rule',
        'min_value' => null,
        'max_value' => '2000.0000',
        'rate' => '9.9900',
        'rate_type' => 'per_sheet',
        'sort_order' => 0,
        'is_active' => false,
    ]);

    $result = (new PunchingRateResolver)->resolve($standardPunching, 1000);

    expect($result['rate'])->toBe(1.0);
});

test('fallback rule resolves when present', function () {
    $category = PricingCategory::create([
        'name' => 'Punching',
        'slug' => 'punching',
        'is_active' => true,
    ]);

    $fallbackPunching = PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'Fallback Punching',
        'slug' => 'fallback-punching',
        'rate' => null,
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $fallbackPunching->id,
        'name' => 'Fallback Rule',
        'min_value' => null,
        'max_value' => null,
        'rate' => '2.5000',
        'rate_type' => 'per_sheet',
        'is_active' => true,
    ]);

    $result = (new PunchingRateResolver)->resolve($fallbackPunching, 5000);

    expect($result['source'])->toBe('rule')
        ->and($result['rate'])->toBe(2.5)
        ->and($result['total_charge'])->toBe(12500.0);
});

function seedPunchingResolverItems(): array
{
    $category = PricingCategory::create([
        'name' => 'Punching',
        'slug' => 'punching',
        'is_active' => true,
    ]);

    $standardPunching = PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'Standard Punching',
        'slug' => 'standard-punching',
        'rate' => null,
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $standardPunching->id,
        'name' => 'Below 1000 Sheets',
        'min_value' => null,
        'max_value' => '999.0000',
        'rate' => '1000.0000',
        'rate_type' => 'minimum_flat',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $standardPunching->id,
        'name' => '1000 to 2000 Sheets',
        'min_value' => '999.0000',
        'max_value' => '2000.0000',
        'rate' => '1.0000',
        'rate_type' => 'per_sheet',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $standardPunching->id,
        'name' => 'Above 2000 Sheets',
        'min_value' => '2000.0000',
        'max_value' => null,
        'rate' => '0.6000',
        'rate_type' => 'per_sheet',
        'sort_order' => 3,
        'is_active' => true,
    ]);

    $complicatedPunching = PricingItem::create([
        'pricing_category_id' => $category->id,
        'name' => 'Complicated Punching',
        'slug' => 'complicated-punching',
        'rate' => '0.7000',
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    return [$standardPunching, $complicatedPunching];
}
