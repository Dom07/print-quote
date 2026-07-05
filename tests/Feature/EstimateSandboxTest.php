<?php

use App\Models\PricingCategory;
use App\Models\PricingItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('estimate sandbox page loads successfully', function () {
    seedSandboxPricingItems();

    $this->get('/estimate-sandbox')
        ->assertOk()
        ->assertSee('Estimate Sandbox')
        ->assertSee('Temporary calculation screen for testing paper inputs.')
        ->assertSee('Paper Rate')
        ->assertSee('Grey Back ORD')
        ->assertDontSee('₹41.5000')
        ->assertSee('Interest Slab')
        ->assertSee('1.25%')
        ->assertDontSee('Interest 1.25%')
        ->assertDontSee('1.2500%');
});

test('estimate sandbox calculates paper kilograms and paper pricing for valid data', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', [
        'length' => 20,
        'width' => 30,
        'gsm' => 100,
        'no_of_sheets' => 1000,
        'no_of_sheets_with_wastage' => 1100,
        'no_of_sheets_to_process' => 1200,
        'paper_pricing_item_id' => $paperItem->id,
        'interest_pricing_item_id' => $interestItem->id,
    ])
        ->assertOk()
        ->assertSee('38.7097')
        ->assertSee('42.5806')
        ->assertSee('46.4516')
        ->assertSee('KG for Sheets With Wastage')
        ->assertSee('Paper Pricing')
        ->assertSee('Selected Paper Rate')
        ->assertSee('Interest %')
        ->assertSee('Updated Paper Rate')
        ->assertSee('Price Per Sheet')
        ->assertSee('₹41.5000')
        ->assertSee('1.2500%')
        ->assertSee('₹42.0188')
        ->assertSee('₹1.7892')
        ->assertSee('Temporary paper weight calculation only. No values are saved.');
});

test('estimate sandbox returns validation errors for invalid data', function () {
    $this->post('/estimate-sandbox', [
        'length' => 0,
        'width' => 'wide',
        'gsm' => -1,
        'no_of_sheets' => 0,
        'no_of_sheets_with_wastage' => 1.5,
        'no_of_sheets_to_process' => -2,
        'paper_pricing_item_id' => '',
        'interest_pricing_item_id' => '',
    ])
        ->assertSessionHasErrors([
            'length',
            'width',
            'gsm',
            'no_of_sheets',
            'no_of_sheets_with_wastage',
            'no_of_sheets_to_process',
            'paper_pricing_item_id',
            'interest_pricing_item_id',
        ]);
});

test('estimate sandbox validates missing pricing selections', function () {
    seedSandboxPricingItems();

    $this->post('/estimate-sandbox', [
        'length' => 20,
        'width' => 30,
        'gsm' => 100,
        'no_of_sheets' => 1000,
        'no_of_sheets_with_wastage' => 1100,
        'no_of_sheets_to_process' => 1200,
        'paper_pricing_item_id' => '',
        'interest_pricing_item_id' => '',
    ])
        ->assertSessionHasErrors([
            'paper_pricing_item_id',
            'interest_pricing_item_id',
        ]);
});

function seedSandboxPricingItems(): array
{
    $paperCategory = PricingCategory::create([
        'name' => 'Paper',
        'slug' => 'paper',
        'is_active' => true,
    ]);

    $interestCategory = PricingCategory::create([
        'name' => 'Interest Slabs',
        'slug' => 'interest-slabs',
        'is_active' => true,
    ]);

    $paperItem = PricingItem::create([
        'pricing_category_id' => $paperCategory->id,
        'name' => 'Grey Back ORD',
        'slug' => 'grey-back-ord',
        'rate' => '41.5000',
        'rate_type' => 'per_kg',
        'is_selectable' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $interestItem = PricingItem::create([
        'pricing_category_id' => $interestCategory->id,
        'name' => 'Interest 1.25%',
        'slug' => 'interest-1-25',
        'rate' => '1.2500',
        'rate_type' => 'percentage',
        'is_selectable' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    return [$paperItem, $interestItem];
}
