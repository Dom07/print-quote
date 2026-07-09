<?php

use App\Models\PricingCategory;
use App\Models\PricingItem;
use App\Models\PricingRule;
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
        ->assertDontSee('1.2500%')
        ->assertSee('Need Punching?')
        ->assertSee('No Punching')
        ->assertSee('Standard Punching')
        ->assertSee('Complicated Punching')
        ->assertDontSee('₹1.0000')
        ->assertDontSee('₹0.6000')
        ->assertDontSee('₹0.7000')
        ->assertSee('Need Lamination?')
        ->assertSee('Lamination Coverage')
        ->assertSee('No Lamination')
        ->assertSee('Front Only')
        ->assertSee('Both Sides')
        ->assertSee('BOPP Lamination')
        ->assertSee('Matte Lamination')
        ->assertDontSee('Gloss Lamination')
        ->assertDontSee('0.3700')
        ->assertDontSee('0.3500')
        ->assertDontSee('0.4700')
        ->assertDontSee('0.4500')
        ->assertSee('Need Spot UV?')
        ->assertSee('Spot UV')
        ->assertSee('Raised UV')
        ->assertDontSee('₹1250.0000')
        ->assertDontSee('₹2800.0000')
        ->assertSee('Need Drip Off?')
        ->assertSee('Ups')
        ->assertSee('Piece-Level Costs')
        ->assertSee('These fields apply after the base price per piece is calculated.')
        ->assertSee('Window &amp; Labor Cost', false)
        ->assertSee('Apply Lace')
        ->assertDontSee('>Lace Cost<', false)
        ->assertSee('Designing Cost')
        ->assertSee('name="ups"', false)
        ->assertSee('name="window_labor_cost"', false)
        ->assertSee('name="needs_lace_cost"', false)
        ->assertSee('name="designing_cost"', false)
        ->assertDontSee('name="needs_window_labor_cost"', false)
        ->assertDontSee('name="needs_designing_cost"', false)
        ->assertSee('Required Piece Costs')
        ->assertSee('Job Type')
        ->assertSee('Repeat Job')
        ->assertSee('New Job')
        ->assertSee('New Job Punch Cost')
        ->assertSee('Expenses')
        ->assertSee('name="punch_cost_job_type"', false)
        ->assertSee('name="new_job_punch_cost"', false)
        ->assertSee('name="expenses"', false);
});

test('piece level section appears after drip off in the rendered html', function () {
    seedSandboxPricingItems();

    $response = $this->get('/estimate-sandbox')->assertOk();
    $html = $response->getContent();

    $dripOffPosition = strpos($html, 'id="needs_drip_off"');
    $pieceLevelPosition = strpos($html, 'id="piece-level-costs-title"');
    $formActionsPosition = strpos($html, 'class="form-actions"');

    expect($dripOffPosition)->not->toBeFalse()
        ->and($pieceLevelPosition)->not->toBeFalse()
        ->and($formActionsPosition)->not->toBeFalse()
        ->and($pieceLevelPosition)->toBeGreaterThan($dripOffPosition)
        ->and($pieceLevelPosition)->toBeLessThan($formActionsPosition);
});

test('required piece costs section appears after piece level costs in the rendered html', function () {
    seedSandboxPricingItems();

    $response = $this->get('/estimate-sandbox')->assertOk();
    $html = $response->getContent();

    $pieceLevelPosition = strpos($html, 'id="piece-level-costs-title"');
    $requiredPieceCostsPosition = strpos($html, 'id="required-piece-costs-title"');
    $formActionsPosition = strpos($html, 'class="form-actions"');

    expect($pieceLevelPosition)->not->toBeFalse()
        ->and($requiredPieceCostsPosition)->not->toBeFalse()
        ->and($formActionsPosition)->not->toBeFalse()
        ->and($requiredPieceCostsPosition)->toBeGreaterThan($pieceLevelPosition)
        ->and($requiredPieceCostsPosition)->toBeLessThan($formActionsPosition);
});

test('estimate sandbox calculates paper kilograms and paper pricing for valid data', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', [
        'length' => 20,
        'width' => 30,
        'gsm' => 100,
        'no_of_sheets' => 1000,
        'ups' => 4,
        'no_of_sheets_with_wastage' => 1100,
        'no_of_sheets_to_process' => 1200,
        'printing_cost' => 2500,
        'ink_cost' => 375.5,
        'foiling_cost' => 625.25,
        'needs_punching' => 0,
        'needs_lamination' => 0,
        'needs_spot_uv' => 0,
        'needs_drip_off' => 0,
        'window_labor_cost' => '',
        'needs_lace_cost' => 0,
        'designing_cost' => '',
        'punch_cost_job_type' => 'repeat_job',
        'new_job_punch_cost' => '',
        'expenses' => 0,
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
        ->assertSee('Manual Costs')
        ->assertSee('Printing Cost')
        ->assertSee('Ink Cost')
        ->assertSee('Foiling Cost')
        ->assertSee('₹41.5000')
        ->assertSee('1.2500%')
        ->assertSee('₹42.0188')
        ->assertSee('₹1.7892')
        ->assertSee('₹2,500.0000')
        ->assertSee('₹375.5000')
        ->assertSee('₹625.2500')
        ->assertSee('Punching')
        ->assertSee('No Punching')
        ->assertSee('Lamination')
        ->assertSee('No Lamination')
        ->assertSee('Spot UV')
        ->assertSee('No Spot UV')
        ->assertSee('Drip Off')
        ->assertSee('No Drip Off')
        ->assertSee('Total Price Per Sheet')
        ->assertSee('Piece Pricing')
        ->assertSee('Ups')
        ->assertSee('Number of Pieces')
        ->assertSee('Base Price Per Piece')
        ->assertSee('Window &amp; Labor Cost', false)
        ->assertSee('Lace Cost')
        ->assertSee('Designing Cost')
        ->assertSee('Optional Piece Costs Total')
        ->assertSee('Punch Cost Job Type')
        ->assertSee('Punch Cost')
        ->assertSee('Expenses')
        ->assertSee('Compulsory Piece Costs Total')
        ->assertSee('Total Piece Cost')
        ->assertSee('Total Cost')
        ->assertSee('4000')
        ->assertSee('875.6348')
        ->assertSee('0.0625')
        ->assertSee('875.6973')
        ->assertSee('3,502,789.1855')
        ->assertSee('Paper Price Per Sheet')
        ->assertSee('Punching Rate')
        ->assertSee('Lamination Value')
        ->assertSee('Drip Off Rate')
        ->assertSee('₹0.0000')
        ->assertSee('₹3,502.5392')
        ->assertSee('Temporary paper weight calculation only. No values are saved.');
});

test('checked apply lace uses the db pricing item rate in total piece cost', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_lace_cost' => 1,
    ]))
        ->assertOk()
        ->assertSee('Lace Cost')
        ->assertSee('0.6700')
        ->assertSee('Optional Piece Costs Total')
        ->assertSee('Total Piece Cost')
        ->assertSee('876.3673')
        ->assertSee('3,505,469.1855');
});

test('unchecked apply lace contributes zero to final price per piece', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_lace_cost' => 0,
    ]))
        ->assertOk()
        ->assertSee('Lace Cost')
        ->assertSee('Total Piece Cost')
        ->assertSee('875.6973')
        ->assertDontSee('876.3673');
});

test('repeat job uses db repeat job punch cost divided by number of pieces', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'punch_cost_job_type' => 'repeat_job',
        'new_job_punch_cost' => '',
    ]))
        ->assertOk()
        ->assertSee('Punch Cost Job Type')
        ->assertSee('Repeat Job')
        ->assertSee('Punch Cost')
        ->assertSee('0.0625')
        ->assertSee('Compulsory Piece Costs Total');
});

test('new job uses manual new job punch cost', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'punch_cost_job_type' => 'new_job',
        'new_job_punch_cost' => 3.25,
    ]))
        ->assertOk()
        ->assertSee('Punch Cost Job Type')
        ->assertSee('New Job')
        ->assertSee('3.2500')
        ->assertSee('878.8848');
});

test('expenses are included in total piece cost', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'expenses' => 0.8,
    ]))
        ->assertOk()
        ->assertSee('Expenses')
        ->assertSee('0.8000')
        ->assertSee('Compulsory Piece Costs Total')
        ->assertSee('0.8625')
        ->assertSee('Total Piece Cost')
        ->assertSee('876.4973');
});

test('estimate sandbox validates apply lace as boolean', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $response = $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_lace_cost' => 'maybe',
    ]));

    $response->assertSessionHasErrors([
        'needs_lace_cost' => 'The apply lace field must be true or false.',
    ]);
});

test('estimate sandbox requires ups', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $payload = validSandboxPayload($paperItem, $interestItem);
    unset($payload['ups']);

    $this->post('/estimate-sandbox', $payload)
        ->assertSessionHasErrors([
            'ups',
        ]);
});

test('estimate sandbox requires ups to be at least one', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'ups' => 0,
    ]))
        ->assertSessionHasErrors([
            'ups',
        ]);
});

test('estimate sandbox requires ups to be an integer', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'ups' => 1.5,
    ]))
        ->assertSessionHasErrors([
            'ups',
        ]);
});

test('estimate sandbox rejects negative window labor cost', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'window_labor_cost' => -1,
    ]))
        ->assertSessionHasErrors([
            'window_labor_cost',
        ]);
});

test('estimate sandbox rejects negative designing cost', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'designing_cost' => -1,
    ]))
        ->assertSessionHasErrors([
            'designing_cost',
        ]);
});

test('estimate sandbox requires punch cost job type', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $payload = validSandboxPayload($paperItem, $interestItem);
    unset($payload['punch_cost_job_type']);

    $this->post('/estimate-sandbox', $payload)
        ->assertSessionHasErrors([
            'punch_cost_job_type',
        ]);
});

test('estimate sandbox only accepts known punch cost job types', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'punch_cost_job_type' => 'first_job',
    ]))
        ->assertSessionHasErrors([
            'punch_cost_job_type',
        ]);
});

test('estimate sandbox requires new job punch cost only for new jobs', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'punch_cost_job_type' => 'new_job',
        'new_job_punch_cost' => '',
    ]))
        ->assertSessionHasErrors([
            'new_job_punch_cost',
        ]);
});

test('estimate sandbox does not require new job punch cost for repeat jobs', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'punch_cost_job_type' => 'repeat_job',
        'new_job_punch_cost' => '',
    ]))
        ->assertOk()
        ->assertSee('Piece Pricing');
});

test('estimate sandbox validates new job punch cost amount', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'punch_cost_job_type' => 'new_job',
        'new_job_punch_cost' => -1,
    ]))
        ->assertSessionHasErrors([
            'new_job_punch_cost',
        ]);
});

test('estimate sandbox requires new job punch cost to be numeric', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'punch_cost_job_type' => 'new_job',
        'new_job_punch_cost' => 'manual',
    ]))
        ->assertSessionHasErrors([
            'new_job_punch_cost',
        ]);
});

test('estimate sandbox requires expenses', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $payload = validSandboxPayload($paperItem, $interestItem);
    unset($payload['expenses']);

    $this->post('/estimate-sandbox', $payload)
        ->assertSessionHasErrors([
            'expenses',
        ]);
});

test('estimate sandbox requires expenses to be numeric', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'expenses' => 'many',
    ]))
        ->assertSessionHasErrors([
            'expenses',
        ]);
});

test('estimate sandbox rejects negative expenses', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'expenses' => -1,
    ]))
        ->assertSessionHasErrors([
            'expenses',
        ]);
});

test('estimate sandbox previews drip off when base cost exceeds minimum', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'length' => 20,
        'width' => 30,
        'no_of_sheets_to_process' => 1000,
        'needs_drip_off' => 1,
    ]))
        ->assertOk()
        ->assertSee('Coefficient')
        ->assertSee('0.7500')
        ->assertSee('Base Rate Per Sheet')
        ->assertSee('₹4.5000')
        ->assertSee('Base Cost')
        ->assertSee('₹4,500.0000')
        ->assertSee('Flat Add-On Rate Per Sheet')
        ->assertSee('₹1.3000')
        ->assertSee('Final Drip Off Rate Per Sheet')
        ->assertSee('₹5.8000')
        ->assertSee('Drip Off Rate')
        ->assertSee('₹3,508.3392');
});

test('estimate sandbox previews drip off when minimum applies', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'length' => 10,
        'width' => 10,
        'no_of_sheets_to_process' => 1000,
        'needs_drip_off' => 1,
    ]))
        ->assertOk()
        ->assertSee('Minimum Adjusted Base Cost')
        ->assertSee('₹2,500.0000')
        ->assertSee('Minimum Adjusted Base Rate Per Sheet')
        ->assertSee('₹2.5000')
        ->assertSee('Final Drip Off Rate Per Sheet')
        ->assertSee('₹3.8000')
        ->assertSee('Drip Off Rate')
        ->assertSee('₹3,504.8482');
});

test('estimate sandbox previews spot uv at exact threshold', function () {
    [$paperItem, $interestItem, , , , , $spotUv] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets_to_process' => 1000,
        'needs_spot_uv' => 1,
        'spot_uv_pricing_item_id' => $spotUv->id,
    ]))
        ->assertOk()
        ->assertSee('UV Type')
        ->assertSee('Spot UV')
        ->assertSee('Quantity Used')
        ->assertSee('1000')
        ->assertSee('Minimum / Quantity')
        ->assertSee('₹1,250.0000')
        ->assertSee('₹1.2500')
        ->assertSee('Spot UV Value')
        ->assertSee('₹3,503.7892');
});

test('estimate sandbox previews spot uv above threshold', function () {
    [$paperItem, $interestItem, , , , , $spotUv] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets_to_process' => 1001,
        'needs_spot_uv' => 1,
        'spot_uv_pricing_item_id' => $spotUv->id,
    ]))
        ->assertOk()
        ->assertSee('Per Sheet')
        ->assertSee('₹1.2500')
        ->assertSee('Spot UV Value')
        ->assertSee('₹3,503.7892');
});

test('estimate sandbox previews raised uv at exact threshold', function () {
    [$paperItem, $interestItem, , , , , , $raisedUv] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets_to_process' => 1000,
        'needs_spot_uv' => 1,
        'spot_uv_pricing_item_id' => $raisedUv->id,
    ]))
        ->assertOk()
        ->assertSee('Raised UV')
        ->assertSee('Minimum / Quantity')
        ->assertSee('₹2,800.0000')
        ->assertSee('₹2.8000');
});

test('estimate sandbox previews raised uv above threshold', function () {
    [$paperItem, $interestItem, , , , , , $raisedUv] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets_to_process' => 1001,
        'needs_spot_uv' => 1,
        'spot_uv_pricing_item_id' => $raisedUv->id,
    ]))
        ->assertOk()
        ->assertSee('Raised UV')
        ->assertSee('Per Sheet')
        ->assertSee('₹2.8000');
});

test('estimate sandbox previews one side bopp lamination at lower threshold', function () {
    [$paperItem, $interestItem, , , $bopp] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'length' => 20,
        'width' => 30,
        'no_of_sheets_to_process' => 5000,
        'needs_lamination' => 1,
        'lamination_mode' => 'front_only',
        'lamination_front_pricing_item_id' => $bopp->id,
    ]))
        ->assertOk()
        ->assertSee('Lamination Mode')
        ->assertSee('Front Only')
        ->assertSee('Selected Lamination')
        ->assertSee('BOPP Lamination')
        ->assertSee('0.3700')
        ->assertSee('₹2.2200')
        ->assertSee('Combined Lamination Value')
        ->assertSee('Lamination Value')
        ->assertSee('₹3,504.7592');
});

test('estimate sandbox previews one side bopp lamination above threshold', function () {
    [$paperItem, $interestItem, , , $bopp] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'length' => 20,
        'width' => 30,
        'no_of_sheets_to_process' => 5001,
        'needs_lamination' => 1,
        'lamination_mode' => 'front_only',
        'lamination_front_pricing_item_id' => $bopp->id,
    ]))
        ->assertOk()
        ->assertSee('0.3500')
        ->assertSee('₹2.1000');
});

test('estimate sandbox previews both side bopp and matte lamination', function () {
    [$paperItem, $interestItem, , , $bopp, $matte] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'length' => 20,
        'width' => 30,
        'no_of_sheets_to_process' => 5000,
        'needs_lamination' => 1,
        'lamination_mode' => 'both_sides',
        'lamination_front_pricing_item_id' => $bopp->id,
        'lamination_back_pricing_item_id' => $matte->id,
    ]))
        ->assertOk()
        ->assertSee('Both Sides')
        ->assertSee('Front Side')
        ->assertSee('BOPP Lamination')
        ->assertSee('Back Side')
        ->assertSee('Matte Lamination')
        ->assertSee('₹5.0400');
});

test('estimate sandbox resolves standard punching rate at two thousand sheets', function () {
    [$paperItem, $interestItem, $standardPunching] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets' => 2000,
        'needs_punching' => 1,
        'punching_pricing_item_id' => $standardPunching->id,
    ]))
        ->assertOk()
        ->assertSee('Punching')
        ->assertSee('Standard Punching')
        ->assertSee('₹1.0000')
        ->assertSee('Punching Rate')
        ->assertSee('₹3,502.6446');
});

test('estimate sandbox resolves standard punching rate above two thousand sheets', function () {
    [$paperItem, $interestItem, $standardPunching] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets' => 2001,
        'needs_punching' => 1,
        'punching_pricing_item_id' => $standardPunching->id,
    ]))
        ->assertOk()
        ->assertSee('Standard Punching')
        ->assertSee('₹0.6000');
});

test('estimate sandbox resolves complicated punching item rate', function () {
    [$paperItem, $interestItem, , $complicatedPunching] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_punching' => 1,
        'punching_pricing_item_id' => $complicatedPunching->id,
    ]))
        ->assertOk()
        ->assertSee('Complicated Punching')
        ->assertSee('₹0.7000');
});

test('estimate sandbox returns validation errors for invalid data', function () {
    $this->post('/estimate-sandbox', [
        'length' => 0,
        'width' => 'wide',
        'gsm' => -1,
        'no_of_sheets' => 0,
        'ups' => 0,
        'no_of_sheets_with_wastage' => 1.5,
        'no_of_sheets_to_process' => -2,
        'printing_cost' => -1,
        'ink_cost' => 'ink',
        'foiling_cost' => 'foil',
        'needs_punching' => 'maybe',
        'needs_lamination' => 'maybe',
        'window_labor_cost' => -1,
        'needs_lace_cost' => 'maybe',
        'designing_cost' => -1,
        'punch_cost_job_type' => 'first_job',
        'new_job_punch_cost' => -1,
        'expenses' => -1,
        'lamination_mode' => 'bad',
        'paper_pricing_item_id' => '',
        'interest_pricing_item_id' => '',
    ])
        ->assertSessionHasErrors([
            'length',
            'width',
            'gsm',
            'no_of_sheets',
            'ups',
            'no_of_sheets_with_wastage',
            'no_of_sheets_to_process',
            'printing_cost',
            'ink_cost',
            'foiling_cost',
            'needs_punching',
            'needs_lamination',
            'window_labor_cost',
            'needs_lace_cost',
            'designing_cost',
            'punch_cost_job_type',
            'new_job_punch_cost',
            'expenses',
            'needs_spot_uv',
            'needs_drip_off',
            'lamination_mode',
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
        'ups' => 4,
        'no_of_sheets_with_wastage' => 1100,
        'no_of_sheets_to_process' => 1200,
        'printing_cost' => 2500,
        'ink_cost' => 375.5,
        'foiling_cost' => 625.25,
        'needs_punching' => 0,
        'needs_lamination' => 0,
        'needs_spot_uv' => 0,
        'needs_drip_off' => 0,
        'window_labor_cost' => '',
        'needs_lace_cost' => 0,
        'designing_cost' => '',
        'punch_cost_job_type' => 'repeat_job',
        'new_job_punch_cost' => '',
        'expenses' => 0,
        'paper_pricing_item_id' => '',
        'interest_pricing_item_id' => '',
    ])
        ->assertSessionHasErrors([
            'paper_pricing_item_id',
            'interest_pricing_item_id',
        ]);
});

test('estimate sandbox validates missing manual costs', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', [
        'length' => 20,
        'width' => 30,
        'gsm' => 100,
        'no_of_sheets' => 1000,
        'ups' => 4,
        'no_of_sheets_with_wastage' => 1100,
        'no_of_sheets_to_process' => 1200,
        'paper_pricing_item_id' => $paperItem->id,
        'interest_pricing_item_id' => $interestItem->id,
        'needs_punching' => 0,
        'needs_lamination' => 0,
        'needs_spot_uv' => 0,
        'needs_drip_off' => 0,
        'window_labor_cost' => '',
        'needs_lace_cost' => 0,
        'designing_cost' => '',
        'punch_cost_job_type' => 'repeat_job',
        'new_job_punch_cost' => '',
        'expenses' => 0,
    ])
        ->assertSessionHasErrors([
            'printing_cost',
            'ink_cost',
        ]);
});

test('estimate sandbox allows missing foiling cost', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $payload = validSandboxPayload($paperItem, $interestItem);
    unset($payload['foiling_cost']);

    $this->post('/estimate-sandbox', $payload)
        ->assertOk()
        ->assertSee('Manual Costs')
        ->assertSee('Printing Cost')
        ->assertSee('Ink Cost')
        ->assertSee('Foiling Cost')
        ->assertSee('₹0.0000')
        ->assertSee('₹2,877.2892')
        ->assertDontSee('₹625.2500');
});

test('estimate sandbox requires no of sheets to process', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $payload = validSandboxPayload($paperItem, $interestItem);
    unset($payload['no_of_sheets_to_process']);

    $this->post('/estimate-sandbox', $payload)
        ->assertSessionHasErrors([
            'no_of_sheets_to_process',
        ]);
});

test('estimate sandbox allows zero sheets to process when process sections are off', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets_to_process' => 0,
        'needs_lamination' => 0,
        'needs_spot_uv' => 0,
        'needs_drip_off' => 0,
    ]))
        ->assertOk()
        ->assertSee('KG for Sheets To Process')
        ->assertSee('0.0000');
});

test('estimate sandbox rejects zero sheets to process when lamination is selected', function () {
    [$paperItem, $interestItem, , , $bopp] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets_to_process' => 0,
        'needs_lamination' => 1,
        'lamination_mode' => 'front_only',
        'lamination_front_pricing_item_id' => $bopp->id,
    ]))
        ->assertSessionHasErrors([
            'no_of_sheets_to_process',
        ]);
});

test('estimate sandbox rejects zero sheets to process when spot uv is selected', function () {
    [$paperItem, $interestItem, , , , , $spotUv] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets_to_process' => 0,
        'needs_spot_uv' => 1,
        'spot_uv_pricing_item_id' => $spotUv->id,
    ]))
        ->assertSessionHasErrors([
            'no_of_sheets_to_process',
        ]);
});

test('estimate sandbox rejects zero sheets to process when drip off is selected', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets_to_process' => 0,
        'needs_drip_off' => 1,
    ]))
        ->assertSessionHasErrors([
            'no_of_sheets_to_process',
        ]);
});

test('estimate sandbox validates missing back side lamination for both sides mode', function () {
    [$paperItem, $interestItem, , , $bopp] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_lamination' => 1,
        'lamination_mode' => 'both_sides',
        'lamination_front_pricing_item_id' => $bopp->id,
    ]))
        ->assertSessionHasErrors([
            'lamination_back_pricing_item_id',
        ]);
});

test('estimate sandbox validates missing punching type when punching is needed', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_punching' => 1,
        'punching_pricing_item_id' => '',
    ]))
        ->assertSessionHasErrors([
            'punching_pricing_item_id',
        ]);
});

test('estimate sandbox validates missing uv type when spot uv is needed', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_spot_uv' => 1,
        'spot_uv_pricing_item_id' => '',
    ]))
        ->assertSessionHasErrors([
            'spot_uv_pricing_item_id',
        ]);
});

test('estimate sandbox validates missing lamination coverage when lamination is needed', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_lamination' => 1,
        'lamination_mode' => '',
    ]))
        ->assertSessionHasErrors([
            'lamination_mode',
        ]);
});

test('estimate sandbox validates missing front lamination when lamination is needed', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_lamination' => 1,
        'lamination_mode' => 'front_only',
        'lamination_front_pricing_item_id' => '',
    ]))
        ->assertSessionHasErrors([
            'lamination_front_pricing_item_id',
        ]);
});

test('estimate sandbox validates invalid lamination item', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $otherCategory = PricingCategory::create([
        'name' => 'Other Lamination',
        'slug' => 'other-lamination',
        'is_active' => true,
    ]);

    $otherItem = PricingItem::create([
        'pricing_category_id' => $otherCategory->id,
        'name' => 'Other Lamination',
        'slug' => 'other-lamination',
        'rate' => '0.9900',
        'rate_type' => 'formula_coefficient',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_lamination' => 1,
        'lamination_mode' => 'front_only',
        'lamination_front_pricing_item_id' => $otherItem->id,
    ]))
        ->assertSessionHasErrors([
            'lamination_front_pricing_item_id',
        ]);
});

test('estimate sandbox validates invalid punching selection', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $otherCategory = PricingCategory::create([
        'name' => 'Other',
        'slug' => 'other',
        'is_active' => true,
    ]);

    $otherItem = PricingItem::create([
        'pricing_category_id' => $otherCategory->id,
        'name' => 'Other Item',
        'slug' => 'other-item',
        'rate' => '1.0000',
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_punching' => 1,
        'punching_pricing_item_id' => $otherItem->id,
    ]))
        ->assertSessionHasErrors([
            'punching_pricing_item_id',
        ]);
});

test('estimate sandbox validates invalid spot uv selection', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $otherCategory = PricingCategory::create([
        'name' => 'Other UV',
        'slug' => 'other-uv',
        'is_active' => true,
    ]);

    $otherItem = PricingItem::create([
        'pricing_category_id' => $otherCategory->id,
        'name' => 'Other UV',
        'slug' => 'other-uv',
        'rate' => '1.0000',
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_spot_uv' => 1,
        'spot_uv_pricing_item_id' => $otherItem->id,
    ]))
        ->assertSessionHasErrors([
            'spot_uv_pricing_item_id',
        ]);
});

function validSandboxPayload(PricingItem $paperItem, PricingItem $interestItem, array $overrides = []): array
{
    return array_merge([
        'length' => 20,
        'width' => 30,
        'gsm' => 100,
        'no_of_sheets' => 1000,
        'ups' => 4,
        'no_of_sheets_with_wastage' => 1100,
        'no_of_sheets_to_process' => 1200,
        'printing_cost' => 2500,
        'ink_cost' => 375.5,
        'foiling_cost' => 625.25,
        'needs_punching' => 0,
        'needs_lamination' => 0,
        'needs_spot_uv' => 0,
        'needs_drip_off' => 0,
        'window_labor_cost' => '',
        'needs_lace_cost' => 0,
        'designing_cost' => '',
        'punch_cost_job_type' => 'repeat_job',
        'new_job_punch_cost' => '',
        'expenses' => 0,
        'paper_pricing_item_id' => $paperItem->id,
        'interest_pricing_item_id' => $interestItem->id,
    ], $overrides);
}

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

    $punchingCategory = PricingCategory::create([
        'name' => 'Punching',
        'slug' => 'punching',
        'is_active' => true,
    ]);

    $standardPunching = PricingItem::create([
        'pricing_category_id' => $punchingCategory->id,
        'name' => 'Standard Punching',
        'slug' => 'standard-punching',
        'rate' => null,
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    PricingRule::create([
        'pricing_item_id' => $standardPunching->id,
        'name' => 'Up to 2000 Sheets',
        'min_value' => null,
        'max_value' => '2000.0000',
        'rate' => '1.0000',
        'rate_type' => 'per_sheet',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    PricingRule::create([
        'pricing_item_id' => $standardPunching->id,
        'name' => 'Above 2000 Sheets',
        'min_value' => '2000.0000',
        'max_value' => null,
        'rate' => '0.6000',
        'rate_type' => 'per_sheet',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $complicatedPunching = PricingItem::create([
        'pricing_category_id' => $punchingCategory->id,
        'name' => 'Complicated Punching',
        'slug' => 'complicated-punching',
        'rate' => '0.7000',
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
        'sort_order' => 2,
    ]);

    PricingItem::create([
        'pricing_category_id' => $punchingCategory->id,
        'name' => 'Hidden Punching',
        'slug' => 'hidden-punching',
        'rate' => '9.0000',
        'rate_type' => 'per_sheet',
        'is_selectable' => false,
        'is_active' => true,
        'sort_order' => 3,
    ]);

    $laminationCategory = PricingCategory::create([
        'name' => 'Lamination',
        'slug' => 'lamination',
        'is_active' => true,
    ]);

    $bopp = PricingItem::create([
        'pricing_category_id' => $laminationCategory->id,
        'name' => 'BOPP Lamination',
        'slug' => 'bopp-lamination',
        'rate' => null,
        'rate_type' => 'formula_coefficient',
        'is_selectable' => true,
        'is_active' => true,
        'sort_order' => 1,
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
        'pricing_category_id' => $laminationCategory->id,
        'name' => 'Matte Lamination',
        'slug' => 'matte-lamination',
        'rate' => null,
        'rate_type' => 'formula_coefficient',
        'is_selectable' => true,
        'is_active' => true,
        'sort_order' => 2,
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

    PricingItem::create([
        'pricing_category_id' => $laminationCategory->id,
        'name' => 'Gloss Lamination',
        'slug' => 'gloss-lamination',
        'rate' => '0.9900',
        'rate_type' => 'formula_coefficient',
        'is_selectable' => true,
        'is_active' => true,
        'sort_order' => 3,
    ]);

    $spotUvCategory = PricingCategory::create([
        'name' => 'Spot UV',
        'slug' => 'spot-uv',
        'is_active' => true,
    ]);

    $spotUv = PricingItem::create([
        'pricing_category_id' => $spotUvCategory->id,
        'name' => 'Spot UV',
        'slug' => 'spot-uv',
        'rate' => null,
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
        'sort_order' => 1,
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
        'pricing_category_id' => $spotUvCategory->id,
        'name' => 'Raised UV',
        'slug' => 'raised-uv',
        'rate' => null,
        'rate_type' => 'per_sheet',
        'is_selectable' => true,
        'is_active' => true,
        'sort_order' => 2,
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

    $dripOffCategory = PricingCategory::create([
        'name' => 'Drip Off',
        'slug' => 'drip-off',
        'is_active' => true,
    ]);

    PricingItem::create([
        'pricing_category_id' => $dripOffCategory->id,
        'name' => 'Drip Off Coefficient',
        'slug' => 'drip-off-coefficient',
        'rate' => '0.7500',
        'rate_type' => 'formula_coefficient',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    PricingItem::create([
        'pricing_category_id' => $dripOffCategory->id,
        'name' => 'Drip Off Minimum Charge',
        'slug' => 'drip-off-minimum-charge',
        'rate' => '2500.0000',
        'rate_type' => 'minimum_flat',
        'is_selectable' => false,
        'is_active' => true,
    ]);

    PricingItem::create([
        'pricing_category_id' => $dripOffCategory->id,
        'name' => 'Drip Off Setup Charge',
        'slug' => 'drip-off-setup-charge',
        'rate' => '1300.0000',
        'rate_type' => 'flat',
        'is_selectable' => false,
        'is_active' => true,
    ]);

    $addOnCategory = PricingCategory::create([
        'name' => 'Add-on Costs',
        'slug' => 'add-on-costs',
        'is_active' => true,
    ]);

    PricingItem::create([
        'pricing_category_id' => $addOnCategory->id,
        'name' => 'Lace Cost',
        'slug' => 'lace-cost',
        'rate' => '0.6700',
        'rate_type' => 'per_piece',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    $requiredCostsCategory = PricingCategory::create([
        'name' => 'Required Costs',
        'slug' => 'required-costs',
        'is_active' => true,
    ]);

    PricingItem::create([
        'pricing_category_id' => $requiredCostsCategory->id,
        'name' => 'Repeat Job Punch Cost',
        'slug' => 'repeat-job-punch-cost',
        'rate' => '250.0000',
        'rate_type' => 'flat',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    return [$paperItem, $interestItem, $standardPunching, $complicatedPunching, $bopp, $matte, $spotUv, $raisedUv];
}
