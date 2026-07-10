<?php

use App\Models\PricingCategory;
use App\Models\PricingItem;
use App\Services\Estimates\PastingRateResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it resolves every pasting combination', function (string $sides, bool $checking, string $slug, float $rate) {
    seedPastingItems();

    $result = (new PastingRateResolver)->resolve($sides, $checking);

    expect($result['pasting_sides'])->toBe($sides)
        ->and($result['with_checking'])->toBe($checking)
        ->and($result['rate'])->toBe($rate)
        ->and($result['pricing_item_name'])->toBe(PricingItem::where('slug', $slug)->value('name'));
})->with([
    ['four_sides', false, 'four-sides-pasting', 0.4],
    ['four_sides', true, 'four-sides-pasting-with-checking', 0.45],
    ['eight_sides', false, 'eight-sides-pasting', 0.9],
    ['eight_sides', true, 'eight-sides-pasting-with-checking', 1.0],
]);

test('it respects an admin-edited database rate', function () {
    seedPastingItems();
    PricingItem::where('slug', 'four-sides-pasting')->update(['rate' => 0.52]);

    expect((new PastingRateResolver)->resolve('four_sides', false)['rate'])->toBe(0.52);
});

test('it fails clearly when the required item is inactive', function () {
    seedPastingItems();
    PricingItem::where('slug', 'four-sides-pasting')->update(['is_active' => false]);

    (new PastingRateResolver)->resolve('four_sides', false);
})->throws(RuntimeException::class, 'Active pasting pricing item');

function seedPastingItems(): void
{
    $category = PricingCategory::create(['name' => 'Pasting', 'slug' => 'pasting', 'is_active' => true]);

    foreach ([
        ['4 Sides Pasting', 'four-sides-pasting', 0.4],
        ['4 Sides Pasting With Checking', 'four-sides-pasting-with-checking', 0.45],
        ['8 Sides Pasting', 'eight-sides-pasting', 0.9],
        ['8 Sides Pasting With Checking', 'eight-sides-pasting-with-checking', 1.0],
    ] as [$name, $slug, $rate]) {
        PricingItem::create([
            'pricing_category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'rate' => $rate,
            'rate_type' => 'per_sheet',
            'is_selectable' => false,
            'is_active' => true,
        ]);
    }
}
