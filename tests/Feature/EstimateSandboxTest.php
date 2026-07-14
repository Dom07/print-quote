<?php

use App\Models\MarginSlab;
use App\Models\PricingCategory;
use App\Models\PricingItem;
use App\Models\PricingRule;
use Database\Seeders\PricingCategorySeeder;
use Database\Seeders\PricingItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('estimate sandbox page loads successfully', function () {
    seedSandboxPricingItems();

    $this->get('/estimate-sandbox')
        ->assertOk()
        ->assertSee('Estimate Sandbox')
        ->assertSee('Temporary calculation screen for testing paper inputs.')
        ->assertSee('Initial Paper Pricing')
        ->assertSee('Base paper inputs used to calculate kilograms, updated paper rate, and price per sheet.')
        ->assertSee('Printing &amp; Ink', false)
        ->assertSee('Manual per-sheet production costs included in total price per sheet.')
        ->assertSee('Sheet Add-ons')
        ->assertSee('Optional sheet-level processes that contribute to total price per sheet.')
        ->assertSee('Price Per Piece')
        ->assertSee('Inputs used after total price per sheet is calculated to determine piece cost, order total, and margin.')
        ->assertSee('name="length"', false)
        ->assertSee('name="width"', false)
        ->assertSee('Measurement Unit')
        ->assertSee('name="measurement_unit"', false)
        ->assertSee('segmented-control measurement-unit-toggle', false)
        ->assertSee('type="radio" name="measurement_unit" value="in" data-measurement-unit checked', false)
        ->assertSee('type="radio" name="measurement_unit" value="cm" data-measurement-unit', false)
        ->assertSee('data-measurement-unit-suffix', false)
        ->assertSee('>in</span>', false)
        ->assertSee('Not calculated')
        ->assertSee('Entered Size')
        ->assertSee('class="result-item"', false)
        ->assertDontSee('Calculation Size')
        ->assertDontSee('dimension-result-block', false)
        ->assertDontSee('dimension-result__value', false)
        ->assertSee('Length')
        ->assertSee('Width')
        ->assertDontSee('Length (')
        ->assertDontSee('Width (')
        ->assertDontSee('Ã—')
        ->assertDontSee('Ãƒâ€”')
        ->assertSee('value="cm"', false)
        ->assertSee('name="gsm"', false)
        ->assertSee('name="no_of_sheets"', false)
        ->assertSee('name="no_of_sheets_with_wastage"', false)
        ->assertSee('name="no_of_sheets_to_process"', false)
        ->assertSee('name="paper_pricing_item_id"', false)
        ->assertSee('type="hidden" name="override_paper_rate" value="0"', false)
        ->assertSee('id="override_paper_rate" name="override_paper_rate" type="checkbox" value="1"', false)
        ->assertSee('name="overridden_paper_rate"', false)
        ->assertSee('name="interest_pricing_item_id"', false)
        ->assertSee('Paper Rate')
        ->assertSee('Grey Back ORD')
        ->assertDontSee('₹41.5000')
        ->assertSee('Interest Slab')
        ->assertSee('1.25%')
        ->assertDontSee('Interest 1.25%')
        ->assertDontSee('1.2500%')
        ->assertSee('name="printing_cost"', false)
        ->assertSee('name="ink_cost"', false)
        ->assertSee('name="needs_foiling"', false)
        ->assertSee('name="foiling_cost"', false)
        ->assertSee('name="needs_punching"', false)
        ->assertSee('name="punching_pricing_item_id"', false)
        ->assertSee('Foiling')
        ->assertSee('Add a manual per-sheet foiling cost.')
        ->assertSee('Apply Foiling')
        ->assertSee('type="hidden" name="needs_foiling" value="0"', false)
        ->assertSee('id="needs_foiling" name="needs_foiling" type="checkbox" value="1"', false)
        ->assertSee('Punching')
        ->assertSee('Apply punching cost based on the selected punching type.')
        ->assertSee('Apply Punching')
        ->assertSee('type="hidden" name="needs_punching" value="0"', false)
        ->assertSee('id="needs_punching" name="needs_punching" type="checkbox" value="1"', false)
        ->assertSee('No Punching')
        ->assertSee('Standard Punching')
        ->assertSee('Complicated Punching')
        ->assertDontSee('₹1.0000')
        ->assertDontSee('₹0.6000')
        ->assertDontSee('₹0.7000')
        ->assertSee('name="needs_lamination"', false)
        ->assertSee('name="lamination_mode"', false)
        ->assertSee('name="lamination_front_pricing_item_id"', false)
        ->assertSee('name="lamination_back_pricing_item_id"', false)
        ->assertSee('Lamination')
        ->assertSee('Apply front-only or both-side lamination to processed sheets.')
        ->assertSee('Apply Lamination')
        ->assertSee('type="hidden" name="needs_lamination" value="0"', false)
        ->assertSee('id="needs_lamination" name="needs_lamination" type="checkbox" value="1"', false)
        ->assertSee('Lamination Coverage')
        ->assertSee('Front Only')
        ->assertSee('Both Sides')
        ->assertSee('BOPP Lamination')
        ->assertSee('Matte Lamination')
        ->assertDontSee('Gloss Lamination')
        ->assertDontSee('0.3700')
        ->assertDontSee('0.3500')
        ->assertDontSee('0.4700')
        ->assertDontSee('0.4500')
        ->assertSee('name="needs_spot_uv"', false)
        ->assertSee('name="spot_uv_pricing_item_id"', false)
        ->assertSee('name="needs_drip_off"', false)
        ->assertSee('Apply spot UV or raised UV cost to processed sheets.')
        ->assertSee('Apply Spot UV')
        ->assertSee('type="hidden" name="needs_spot_uv" value="0"', false)
        ->assertSee('id="needs_spot_uv" name="needs_spot_uv" type="checkbox" value="1"', false)
        ->assertSee('Spot UV')
        ->assertSee('Raised UV')
        ->assertDontSee('₹1250.0000')
        ->assertDontSee('₹2800.0000')
        ->assertSee('Drip Off')
        ->assertSee('Apply drip off based on configured drip-off rates.')
        ->assertSee('Apply Drip Off')
        ->assertSee('type="hidden" name="needs_drip_off" value="0"', false)
        ->assertSee('id="needs_drip_off" name="needs_drip_off" type="checkbox" value="1"', false)
        ->assertSee('name="drip_off_setup_pricing_item_id"', false)
        ->assertSee('New Job with Pasting')
        ->assertSee('Repeat Job with Pasting')
        ->assertSee('Piece Inputs')
        ->assertSee('Inputs used to convert sheet pricing into piece pricing.')
        ->assertSee('Ups')
        ->assertSee('Window &amp; Labor Cost', false)
        ->assertSee('Piece Add-ons')
        ->assertSee('Optional piece-level add-ons applied after base price per piece is calculated.')
        ->assertSee('name="needs_pasting"', false)
        ->assertSee('name="pasting_sides"', false)
        ->assertSee('name="needs_pasting_checking"', false)
        ->assertSee('Add a per-piece pasting cost.')
        ->assertSee('Apply Pasting')
        ->assertSee('Lace')
        ->assertSee('Apply configured per-piece lace cost.')
        ->assertSee('Apply Lace')
        ->assertDontSee('>Lace Cost<', false)
        ->assertSee('name="ups"', false)
        ->assertSee('name="window_labor_cost"', false)
        ->assertSee('name="needs_lace_cost"', false)
        ->assertDontSee('name="lace_cost"', false)
        ->assertDontSee('name="needs_window_labor_cost"', false)
        ->assertSee('Design Cost')
        ->assertSee('Apply Design Cost')
        ->assertSee('name="needs_designing_cost"', false)
        ->assertDontSee('name="designing_pricing_item_id"', false)
        ->assertDontSee('name="designing_cost"', false)
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

test('estimate sandbox form sections render in calculation order', function () {
    seedSandboxPricingItems();

    $response = $this->get('/estimate-sandbox')->assertOk();
    $html = $response->getContent();

    $initialPaperPricingPosition = strpos($html, 'id="initial-paper-pricing-title"');
    $printingInkPosition = strpos($html, 'id="printing-ink-title"');
    $sheetAddOnsPosition = strpos($html, 'id="sheet-add-ons-title"');
    $pricePerPiecePosition = strpos($html, 'id="price-per-piece-title"');
    $pieceInputsPosition = strpos($html, 'id="piece-inputs-title"');
    $pieceAddOnsPosition = strpos($html, 'id="piece-add-ons-title"');
    $requiredPieceCostsPosition = strpos($html, 'id="required-piece-costs-title"');
    $formActionsPosition = strpos($html, 'class="form-actions"');

    expect($initialPaperPricingPosition)->not->toBeFalse()
        ->and($printingInkPosition)->not->toBeFalse()
        ->and($sheetAddOnsPosition)->not->toBeFalse()
        ->and($pricePerPiecePosition)->not->toBeFalse()
        ->and($pieceInputsPosition)->not->toBeFalse()
        ->and($pieceAddOnsPosition)->not->toBeFalse()
        ->and($requiredPieceCostsPosition)->not->toBeFalse()
        ->and($formActionsPosition)->not->toBeFalse()
        ->and($printingInkPosition)->toBeGreaterThan($initialPaperPricingPosition)
        ->and($sheetAddOnsPosition)->toBeGreaterThan($printingInkPosition)
        ->and($pricePerPiecePosition)->toBeGreaterThan($sheetAddOnsPosition)
        ->and($pieceInputsPosition)->toBeGreaterThan($pricePerPiecePosition)
        ->and($pieceAddOnsPosition)->toBeGreaterThan($pieceInputsPosition)
        ->and($requiredPieceCostsPosition)->toBeGreaterThan($pieceAddOnsPosition)
        ->and($requiredPieceCostsPosition)->toBeLessThan($formActionsPosition);
});

test('estimate sandbox preserves selected measurement unit after validation failure', function () {
    seedSandboxPricingItems();

    $response = $this->from('/estimate-sandbox')->post('/estimate-sandbox', [
        'measurement_unit' => 'cm',
    ]);

    $response->assertRedirect('/estimate-sandbox')
        ->assertSessionHasErrors(['length']);

    $this->get('/estimate-sandbox')
        ->assertOk()
        ->assertSee('name="measurement_unit" value="cm" data-measurement-unit checked', false)
        ->assertSee('>cm</span>', false);
});

test('estimate sandbox rejects invalid measurement unit', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'measurement_unit' => 'mm',
    ]))->assertInvalid('measurement_unit');
});

test('centimeter dimensions are normalized before dimension based calculations', function () {
    [$paperItem, $interestItem, , , $bopp] = seedSandboxPricingItems();

    $basePayload = [
        'needs_lamination' => 1,
        'lamination_mode' => 'front_only',
        'lamination_front_pricing_item_id' => $bopp->id,
        'needs_drip_off' => 1,
        'drip_off_setup_pricing_item_id' => PricingItem::where('slug', 'drip-off-repeat-job-with-pasting')->firstOrFail()->id,
    ];

    $inchResponse = $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, array_merge($basePayload, [
        'length' => 15,
        'width' => 25,
        'measurement_unit' => 'in',
    ])))->assertOk();

    $centimeterResponse = $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, array_merge($basePayload, [
        'length' => 38.1,
        'width' => 63.5,
        'measurement_unit' => 'cm',
    ])))->assertOk();

    foreach ([
        '24.19',
        '26.61',
        '29.03',
        'BOPP Lamination',
        '1.39',
        '3.06',
        '3,506.32',
    ] as $expectedValue) {
        $inchResponse->assertSee($expectedValue);
        $centimeterResponse->assertSee($expectedValue);
    }

    $centimeterResponse
        ->assertSee('Entered Size')
        ->assertSee('38.10 &times; 63.50 cm', false)
        ->assertSee('Calculation Size')
        ->assertSee('15.00 &times; 25.00 in', false)
        ->assertSee('class="result-value result-value--dimension"', false)
        ->assertDontSee('dimension-result-block', false)
        ->assertDontSee('Ã—')
        ->assertDontSee('Ãƒâ€”');
    $inchResponse
        ->assertSee('Entered Size')
        ->assertSee('15.00 &times; 25.00 in', false)
        ->assertDontSee('Calculation Size')
        ->assertDontSee('dimension-result-block', false);
});

test('pricing item seeder sets designing cost rate', function () {
    $this->seed(PricingCategorySeeder::class);
    $this->seed(PricingItemSeeder::class);

    $item = PricingItem::query()
        ->where('slug', 'designing-cost')
        ->whereHas('pricingCategory', fn ($query) => $query->where('slug', 'add-on-costs'))
        ->firstOrFail();

    expect($item->rate)->toBe('400.0000')
        ->and($item->is_selectable)->toBeTrue();
});

test('piece inputs section appears after drip off in the rendered html', function () {
    seedSandboxPricingItems();

    $response = $this->get('/estimate-sandbox')->assertOk();
    $html = $response->getContent();

    $dripOffPosition = strpos($html, 'id="needs_drip_off"');
    $pieceInputsPosition = strpos($html, 'id="piece-inputs-title"');
    $formActionsPosition = strpos($html, 'class="form-actions"');

    expect($dripOffPosition)->not->toBeFalse()
        ->and($pieceInputsPosition)->not->toBeFalse()
        ->and($formActionsPosition)->not->toBeFalse()
        ->and($pieceInputsPosition)->toBeGreaterThan($dripOffPosition)
        ->and($pieceInputsPosition)->toBeLessThan($formActionsPosition);
});

test('required piece costs section appears after piece add-ons in the rendered html', function () {
    seedSandboxPricingItems();

    $response = $this->get('/estimate-sandbox')->assertOk();
    $html = $response->getContent();

    $pieceInputsPosition = strpos($html, 'id="piece-inputs-title"');
    $pieceAddOnsPosition = strpos($html, 'id="piece-add-ons-title"');
    $requiredPieceCostsPosition = strpos($html, 'id="required-piece-costs-title"');
    $formActionsPosition = strpos($html, 'class="form-actions"');

    expect($pieceInputsPosition)->not->toBeFalse()
        ->and($pieceAddOnsPosition)->not->toBeFalse()
        ->and($requiredPieceCostsPosition)->not->toBeFalse()
        ->and($formActionsPosition)->not->toBeFalse()
        ->and($pieceAddOnsPosition)->toBeGreaterThan($pieceInputsPosition)
        ->and($requiredPieceCostsPosition)->toBeGreaterThan($pieceAddOnsPosition)
        ->and($requiredPieceCostsPosition)->toBeLessThan($formActionsPosition);
});

test('pasting controls render in piece add ons before lace', function () {
    seedSandboxPricingItems();

    $response = $this->get('/estimate-sandbox')->assertOk();
    $html = $response->getContent();

    $sheetAddOnsPosition = strpos($html, 'id="sheet-add-ons-title"');
    $pieceAddOnsPosition = strpos($html, 'id="piece-add-ons-title"');
    $windowLaborPosition = strpos($html, 'id="window_labor_cost"');
    $pastingPosition = strpos($html, 'id="needs_pasting"');
    $lacePosition = strpos($html, 'id="needs_lace_cost"');

    expect($sheetAddOnsPosition)->not->toBeFalse()
        ->and($pieceAddOnsPosition)->not->toBeFalse()
        ->and($windowLaborPosition)->not->toBeFalse()
        ->and($pastingPosition)->not->toBeFalse()
        ->and($lacePosition)->not->toBeFalse()
        ->and($pastingPosition)->toBeGreaterThan($pieceAddOnsPosition)
        ->and($pastingPosition)->toBeGreaterThan($windowLaborPosition)
        ->and($pastingPosition)->toBeLessThan($lacePosition)
        ->and($pastingPosition)->toBeGreaterThan($sheetAddOnsPosition);
});

test('estimate sandbox calculates paper kilograms and paper pricing for valid data', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', [
        'length' => 20,
        'width' => 30,
        'measurement_unit' => 'in',
        'gsm' => 100,
        'no_of_sheets' => 1000,
        'ups' => 4,
        'no_of_sheets_with_wastage' => 1100,
        'no_of_sheets_to_process' => 1200,
        'printing_cost' => 2500,
        'ink_cost' => 375.5,
        'needs_foiling' => 1,
        'foiling_cost' => 625.25,
        'needs_punching' => 0,
        'needs_lamination' => 0,
        'needs_spot_uv' => 0,
        'needs_drip_off' => 0,
        'needs_pasting' => 0,
        'pasting_sides' => null,
        'needs_pasting_checking' => 0,
        'window_labor_cost' => '',
        'needs_lace_cost' => 0,
        'needs_designing_cost' => 0,
        'punch_cost_job_type' => 'repeat_job',
        'new_job_punch_cost' => '',
        'expenses' => 0,
        'paper_pricing_item_id' => $paperItem->id,
        'override_paper_rate' => 0,
        'overridden_paper_rate' => null,
        'interest_pricing_item_id' => $interestItem->id,
    ])
        ->assertOk()
        ->assertSee('38.71')
        ->assertSee('42.58')
        ->assertSee('46.45')
        ->assertDontSee('38.7097')
        ->assertDontSee('42.5806')
        ->assertDontSee('46.4516')
        ->assertSee('KG for Sheets With Wastage')
        ->assertSee('Paper Pricing')
        ->assertSee('Selected Paper')
        ->assertSee('Configured Paper Rate')
        ->assertSee('Effective Paper Rate')
        ->assertSee('Interest %')
        ->assertSee('Updated Paper Rate')
        ->assertSee('Price Per Sheet')
        ->assertSee('Manual Costs')
        ->assertSee('Printing Cost')
        ->assertSee('Ink Cost')
        ->assertSee('Foiling Cost')
        ->assertSee('₹41.50')
        ->assertSee('1.2500%')
        ->assertSee('₹42.02')
        ->assertSee('₹1.79')
        ->assertSee('₹2,500.00')
        ->assertSee('₹375.50')
        ->assertSee('₹625.25')
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
        ->assertSee('Required Piece Costs')
        ->assertSee('Required Piece Costs Total')
        ->assertSee('Final Piece Totals')
        ->assertSee('Total Piece Cost')
        ->assertSee('Total Cost')
        ->assertSee('Margin')
        ->assertSee('Selected Margin Slab')
        ->assertSee('Margin Percentage')
        ->assertSee('Margin Amount')
        ->assertSee('Total Cost With Margin')
        ->assertSee('Selling Price')
        ->assertSee('4000')
        ->assertSee('875.64')
        ->assertSee('0.06')
        ->assertSee('875.70')
        ->assertSee('3,502,800.00')
        ->assertSee('Above 3.5 Lac')
        ->assertSee('10.0000%')
        ->assertSee('389,200.00')
        ->assertSee('3,892,000.00')
        ->assertSee('₹973.00')
        ->assertSee('Paper Price Per Sheet')
        ->assertSee('Lamination Value')
        ->assertSee('Drip Off Rate')
        ->assertSee('₹0.00')
        ->assertSee('₹3,502.54')
        ->assertSee('Temporary paper weight calculation only. No values are saved.');
});

test('sandbox margin changes based on calculated total cost', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'printing_cost' => 1,
        'ink_cost' => 1,
        'needs_foiling' => 0,
        'foiling_cost' => '',
        'punch_cost_job_type' => 'new_job',
        'new_job_punch_cost' => 0,
    ]))
        ->assertOk()
        ->assertSee('Margin')
        ->assertSee('Below 20k')
        ->assertSee('17.0000%')
        ->assertSee('Margin Amount')
        ->assertSee('Total Cost With Margin');
});

test('sandbox total cost with margin is calculated using gross margin percentage', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem))
        ->assertOk()
        ->assertSee('Total Cost')
        ->assertSee('3,502,800.00')
        ->assertSee('Margin Amount')
        ->assertSee('389,200.00')
        ->assertSee('Total Cost With Margin')
        ->assertSee('3,892,000.00')
        ->assertSee('Selling Price')
        ->assertSee('₹973.00');
});

test('checked apply lace uses the db pricing item rate in total piece cost', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_lace_cost' => 1,
    ]))
        ->assertOk()
        ->assertSee('Lace Cost')
        ->assertSee('0.67')
        ->assertSee('Optional Piece Costs Total')
        ->assertSee('Designing Cost')
        ->assertSee('0.00')
        ->assertSee('Total Piece Cost')
        ->assertSee('876.37')
        ->assertSee('3,505,480.00');
});

test('unchecked apply lace contributes zero to final price per piece', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_lace_cost' => 0,
    ]))
        ->assertOk()
        ->assertSee('Lace Cost')
        ->assertSee('Total Piece Cost')
        ->assertSee('875.70')
        ->assertDontSee('875.80');
});

test('selected design cost uses the db pricing item rate in total piece cost', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_designing_cost' => 1,
    ]))
        ->assertOk()
        ->assertSee('Designing Cost')
        ->assertSee('0.10')
        ->assertSee('Required Piece Costs Total')
        ->assertSee('0.16')
        ->assertSee('Total Piece Cost')
        ->assertSee('875.80')
        ->assertSee('3,503,200.00');
});

test('unselected design cost contributes zero', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_designing_cost' => 0,
    ]))
        ->assertOk()
        ->assertSee('Designing Cost')
        ->assertSee('0.00')
        ->assertSee('Total Piece Cost')
        ->assertSee('875.70')
        ->assertDontSee('875.80');
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
        ->assertSee('0.06')
        ->assertSee('Designing Cost')
        ->assertSee('0.00')
        ->assertSee('Required Piece Costs Total');
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
        ->assertSee('3.25')
        ->assertSee('878.89');
});

test('expenses are included in total piece cost', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'expenses' => 0.8,
    ]))
        ->assertOk()
        ->assertSee('Expenses')
        ->assertSee('0.80')
        ->assertSee('Required Piece Costs Total')
        ->assertSee('0.86')
        ->assertSee('Total Piece Cost')
        ->assertSee('876.50');
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
    [$paperItem, $interestItem, , , , , , , $newJobWithPasting] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'length' => 20,
        'width' => 30,
        'no_of_sheets_to_process' => 1000,
        'needs_drip_off' => 1,
        'drip_off_setup_pricing_item_id' => $newJobWithPasting->id,
    ]))
        ->assertOk()
        ->assertSee('Setup Option')
        ->assertSee('New Job with Pasting')
        ->assertSee('Coefficient')
        ->assertSee('0.7500')
        ->assertSee('Base Rate Per Sheet')
        ->assertSee('₹4.50')
        ->assertSee('Base Cost')
        ->assertSee('₹4,500.00')
        ->assertSee('Flat Add-On Amount')
        ->assertSee('₹1,600.00')
        ->assertSee('Flat Add-On Rate Per Sheet')
        ->assertSee('₹1.60')
        ->assertSee('Final Drip Off Rate Per Sheet')
        ->assertSee('₹6.10')
        ->assertSee('Drip Off Rate')
        ->assertSee('₹3,508.64');
});

test('estimate sandbox previews repeat job drip off when minimum applies', function () {
    [$paperItem, $interestItem, , , , , , , , $repeatJobWithPasting] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'length' => 10,
        'width' => 10,
        'no_of_sheets_to_process' => 1000,
        'needs_drip_off' => 1,
        'drip_off_setup_pricing_item_id' => $repeatJobWithPasting->id,
    ]))
        ->assertOk()
        ->assertSee('Setup Option')
        ->assertSee('Repeat Job with Pasting')
        ->assertSee('Minimum Adjusted Base Cost')
        ->assertSee('₹2,500.00')
        ->assertSee('Minimum Adjusted Base Rate Per Sheet')
        ->assertSee('₹2.50')
        ->assertSee('Flat Add-On Amount')
        ->assertSee('₹300.00')
        ->assertSee('Final Drip Off Rate Per Sheet')
        ->assertSee('₹2.80')
        ->assertSee('Drip Off Rate')
        ->assertSee('₹3,503.85');
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
        ->assertSee('₹1,250.00')
        ->assertSee('₹1.25')
        ->assertSee('Spot UV Value')
        ->assertSee('₹3,503.79');
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
        ->assertSee('₹1.25')
        ->assertSee('Spot UV Value')
        ->assertSee('₹3,503.79');
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
        ->assertSee('₹2,800.00')
        ->assertSee('₹2.80');
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
        ->assertSee('₹2.80');
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
        ->assertSee('₹2.22')
        ->assertSee('Combined Lamination Value')
        ->assertSee('Lamination Value')
        ->assertSee('₹3,504.76');
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
        ->assertSee('₹2.10');
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
        ->assertSee('₹5.04');
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
        ->assertSee('Pricing Type')
        ->assertSee('Per Sheet')
        ->assertSee('Configured Rate')
        ->assertSee('Effective Rate Per Sheet')
        ->assertSee('1.00')
        ->assertSee('Punching Rate')
        ->assertSee('Total Punching Charge')
        ->assertSee('2,000.00')
        ->assertSee('₹3,502.64');
});

test('estimate sandbox resolves standard punching minimum flat at five hundred sheets', function () {
    [$paperItem, $interestItem, $standardPunching] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets' => 500,
        'needs_punching' => 1,
        'punching_pricing_item_id' => $standardPunching->id,
    ]))
        ->assertOk()
        ->assertSee('Standard Punching')
        ->assertSee('Pricing Type')
        ->assertSee('Minimum Flat')
        ->assertSee('Configured Rate')
        ->assertSee('1,000.00')
        ->assertSee('Effective Rate Per Sheet')
        ->assertSee('2.00')
        ->assertSee('Total Punching Charge');
});

test('estimate sandbox resolves standard punching minimum flat at nine hundred ninety nine sheets', function () {
    [$paperItem, $interestItem, $standardPunching] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets' => 999,
        'needs_punching' => 1,
        'punching_pricing_item_id' => $standardPunching->id,
    ]))
        ->assertOk()
        ->assertSee('Standard Punching')
        ->assertSee('Minimum Flat')
        ->assertSee('Total Punching Charge')
        ->assertSee('1,000.00');
});

test('estimate sandbox resolves standard punching rate at exactly one thousand sheets', function () {
    [$paperItem, $interestItem, $standardPunching] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'no_of_sheets' => 1000,
        'needs_punching' => 1,
        'punching_pricing_item_id' => $standardPunching->id,
    ]))
        ->assertOk()
        ->assertSee('Standard Punching')
        ->assertSee('Pricing Type')
        ->assertSee('Per Sheet')
        ->assertSee('Configured Rate')
        ->assertSee('Effective Rate Per Sheet')
        ->assertSee('1.00')
        ->assertSee('Total Punching Charge')
        ->assertSee('1,000.00');
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
        ->assertSee('Pricing Type')
        ->assertSee('Per Sheet')
        ->assertSee('Configured Rate')
        ->assertSee('Effective Rate Per Sheet')
        ->assertSee('0.60')
        ->assertSee('Total Punching Charge')
        ->assertSee('1,200.60');
});

test('estimate sandbox resolves complicated punching item rate', function () {
    [$paperItem, $interestItem, , $complicatedPunching] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_punching' => 1,
        'punching_pricing_item_id' => $complicatedPunching->id,
    ]))
        ->assertOk()
        ->assertSee('Complicated Punching')
        ->assertSee('Pricing Type')
        ->assertSee('Per Sheet')
        ->assertSee('Configured Rate')
        ->assertSee('Effective Rate Per Sheet')
        ->assertSee('0.70')
        ->assertSee('Total Punching Charge');
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
        'needs_foiling' => 'maybe',
        'foiling_cost' => 'foil',
        'needs_punching' => 'maybe',
        'needs_lamination' => 'maybe',
        'window_labor_cost' => -1,
        'needs_lace_cost' => 'maybe',
        'needs_designing_cost' => 'maybe',
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
            'needs_foiling',
            'foiling_cost',
            'needs_punching',
            'needs_lamination',
            'window_labor_cost',
            'needs_lace_cost',
            'needs_designing_cost',
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
        'needs_foiling' => 1,
        'foiling_cost' => 625.25,
        'needs_punching' => 0,
        'needs_lamination' => 0,
        'needs_spot_uv' => 0,
        'needs_drip_off' => 0,
        'window_labor_cost' => '',
        'needs_lace_cost' => 0,
        'needs_designing_cost' => 0,
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
        'needs_designing_cost' => 0,
        'punch_cost_job_type' => 'repeat_job',
        'new_job_punch_cost' => '',
        'expenses' => 0,
    ])
        ->assertSessionHasErrors([
            'printing_cost',
            'ink_cost',
        ]);
});

test('estimate sandbox does not require foiling cost when apply foiling is unchecked', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $payload = validSandboxPayload($paperItem, $interestItem, [
        'needs_foiling' => 0,
    ]);
    unset($payload['foiling_cost']);

    $this->post('/estimate-sandbox', $payload)
        ->assertOk()
        ->assertSee('Manual Costs')
        ->assertSee('Printing Cost')
        ->assertSee('Ink Cost')
        ->assertSee('Foiling Cost')
        ->assertSee('₹0.00')
        ->assertSee('₹2,877.29')
        ->assertDontSee('₹625.25');
});

test('estimate sandbox requires foiling cost when apply foiling is checked', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_foiling' => 1,
        'foiling_cost' => '',
    ]))
        ->assertSessionHasErrors([
            'foiling_cost',
        ]);
});

test('unchecked apply foiling contributes zero even when a stale value is submitted', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_foiling' => 0,
        'foiling_cost' => 625.25,
    ]))
        ->assertOk()
        ->assertSee('Total Price Per Sheet')
        ->assertSee('2,877.29');
});

test('checked apply foiling includes submitted foiling cost in total price per sheet', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_foiling' => 1,
        'foiling_cost' => 625.25,
    ]))
        ->assertOk()
        ->assertSee('Foiling Cost')
        ->assertSee('625.25')
        ->assertSee('Total Price Per Sheet')
        ->assertSee('3,502.54');
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
        ->assertSee('0.00');
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
        'drip_off_setup_pricing_item_id' => PricingItem::where('slug', 'drip-off-new-job-with-pasting')->firstOrFail()->id,
    ]))
        ->assertSessionHasErrors([
            'no_of_sheets_to_process',
        ]);
});

test('estimate sandbox requires drip off setup option only when drip off is selected', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $payload = validSandboxPayload($paperItem, $interestItem, [
        'needs_drip_off' => 0,
        'drip_off_setup_pricing_item_id' => 'old_setup_charge',
    ]);

    $this->post('/estimate-sandbox', $payload)->assertOk();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_drip_off' => 1,
        'drip_off_setup_pricing_item_id' => '',
    ]))->assertSessionHasErrors([
        'drip_off_setup_pricing_item_id',
    ]);
});

test('estimate sandbox rejects invalid drip off setup option', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_drip_off' => 1,
        'drip_off_setup_pricing_item_id' => 'old_setup_charge',
    ]))->assertSessionHasErrors([
        'drip_off_setup_pricing_item_id',
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

test('paper rate override uses the submitted rate and applies interest to it', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'override_paper_rate' => '1',
        'overridden_paper_rate' => '50',
    ]))
        ->assertOk()
        ->assertSee('Paper Rate Overridden')
        ->assertSee('Yes')
        ->assertSee('Configured Paper Rate')
        ->assertSee('Effective Paper Rate')
        ->assertSee('₹41.50')
        ->assertSee('₹50.00')
        ->assertSee('₹50.63');
});

test('disabled paper rate override ignores a submitted override value', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'override_paper_rate' => '0',
        'overridden_paper_rate' => '50',
    ]))
        ->assertOk()
        ->assertSee('Paper Rate Overridden')
        ->assertSee('No')
        ->assertSee('₹41.50')
        ->assertSee('₹42.02')
        ->assertDontSee('₹50.00');
});

test('enabled paper rate override validates its rate', function (mixed $value) {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'override_paper_rate' => '1',
        'overridden_paper_rate' => $value,
    ]))->assertInvalid('overridden_paper_rate');
})->with([
    'missing' => null,
    'zero' => 0,
    'negative' => -1,
    'nonnumeric' => 'not-a-rate',
]);

test('enabled pasting requires a pasting type', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_pasting' => '1',
        'pasting_sides' => null,
    ]))->assertInvalid('pasting_sides');
});

test('disabled pasting ignores submitted selections', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_pasting' => '0',
        'pasting_sides' => 'eight_sides',
        'needs_pasting_checking' => '1',
    ]))
        ->assertOk()
        ->assertDontSee('Selected Pasting Rate')
        ->assertDontSee('Pasting Rate')
        ->assertSee('Pasting Cost')
        ->assertSee('₹0.00');
});

test('pasting combinations use their configured per-piece rates', function (string $sides, string $checking, string $label, string $formattedRate) {
    [$paperItem, $interestItem] = seedSandboxPricingItems();
    $expectedTotals = [
        'four_sides:0' => ['876.10', '3,504,400.00'],
        'four_sides:1' => ['876.15', '3,504,600.00'],
        'eight_sides:0' => ['876.60', '3,506,400.00'],
        'eight_sides:1' => ['876.70', '3,506,800.00'],
    ];
    [$pieceCost, $totalCost] = $expectedTotals["{$sides}:{$checking}"];

    $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_pasting' => '1',
        'pasting_sides' => $sides,
        'needs_pasting_checking' => $checking,
    ]))
        ->assertOk()
        ->assertSee('Pasting Cost')
        ->assertSee($label)
        ->assertSee($formattedRate)
        ->assertSee('Optional Piece Costs Total')
        ->assertSee('Total Piece Cost')
        ->assertSee($pieceCost)
        ->assertSee($totalCost);
})->with([
    ['four_sides', '0', '4 Sides', '₹0.40'],
    ['four_sides', '1', '4 Sides', '₹0.45'],
    ['eight_sides', '0', '8 Sides', '₹0.90'],
    ['eight_sides', '1', '8 Sides', '₹1.00'],
]);

test('piece add on results appear together before optional total and not sheet components', function () {
    [$paperItem, $interestItem] = seedSandboxPricingItems();

    $response = $this->post('/estimate-sandbox', validSandboxPayload($paperItem, $interestItem, [
        'needs_pasting' => '1',
        'pasting_sides' => 'four_sides',
        'needs_pasting_checking' => '0',
    ]))->assertOk();

    $html = $response->getContent();
    $piecePricingPosition = strpos($html, 'Piece Pricing');
    $pastingCostPosition = strpos($html, 'Pasting Cost');
    $laceCostPosition = strpos($html, 'Lace Cost');
    $designingCostPosition = strpos($html, 'Designing Cost');
    $optionalTotalPosition = strpos($html, 'Optional Piece Costs Total');

    expect($piecePricingPosition)->not->toBeFalse()
        ->and($pastingCostPosition)->not->toBeFalse()
        ->and($laceCostPosition)->not->toBeFalse()
        ->and($designingCostPosition)->not->toBeFalse()
        ->and($optionalTotalPosition)->not->toBeFalse()
        ->and($pastingCostPosition)->toBeGreaterThan($piecePricingPosition)
        ->and($pastingCostPosition)->toBeLessThan($laceCostPosition)
        ->and($laceCostPosition)->toBeLessThan($designingCostPosition)
        ->and($designingCostPosition)->toBeLessThan($optionalTotalPosition)
        ->and($html)->not->toContain('Pasting Rate');
});

function validSandboxPayload(PricingItem $paperItem, PricingItem $interestItem, array $overrides = []): array
{
    return array_merge([
        'length' => 20,
        'width' => 30,
        'measurement_unit' => 'in',
        'gsm' => 100,
        'no_of_sheets' => 1000,
        'ups' => 4,
        'no_of_sheets_with_wastage' => 1100,
        'no_of_sheets_to_process' => 1200,
        'printing_cost' => 2500,
        'ink_cost' => 375.5,
        'needs_foiling' => 1,
        'foiling_cost' => 625.25,
        'needs_punching' => 0,
        'needs_lamination' => 0,
        'needs_spot_uv' => 0,
        'needs_drip_off' => 0,
        'drip_off_setup_pricing_item_id' => null,
        'needs_pasting' => '0',
        'pasting_sides' => null,
        'needs_pasting_checking' => '0',
        'window_labor_cost' => '',
        'needs_lace_cost' => 0,
        'needs_designing_cost' => 0,
        'punch_cost_job_type' => 'repeat_job',
        'new_job_punch_cost' => '',
        'expenses' => 0,
        'paper_pricing_item_id' => $paperItem->id,
        'override_paper_rate' => '0',
        'overridden_paper_rate' => null,
        'interest_pricing_item_id' => $interestItem->id,
    ], $overrides);
}

function seedSandboxPricingItems(): array
{
    MarginSlab::create([
        'name' => 'Below 20k',
        'min_amount' => null,
        'max_amount' => '20000.0000',
        'margin_percentage' => '17.0000',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    MarginSlab::create([
        'name' => '20k to 50k',
        'min_amount' => '20000.0000',
        'max_amount' => '50000.0000',
        'margin_percentage' => '15.0000',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    MarginSlab::create([
        'name' => '50k to 1.5 Lac',
        'min_amount' => '50000.0000',
        'max_amount' => '150000.0000',
        'margin_percentage' => '13.0000',
        'sort_order' => 3,
        'is_active' => true,
    ]);

    MarginSlab::create([
        'name' => '1.5 Lac to 3.5 Lac',
        'min_amount' => '150000.0000',
        'max_amount' => '350000.0000',
        'margin_percentage' => '12.0000',
        'sort_order' => 4,
        'is_active' => true,
    ]);

    MarginSlab::create([
        'name' => 'Above 3.5 Lac',
        'min_amount' => '350000.0000',
        'max_amount' => null,
        'margin_percentage' => '10.0000',
        'sort_order' => 5,
        'is_active' => true,
    ]);

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

    $newJobWithPasting = PricingItem::create([
        'pricing_category_id' => $dripOffCategory->id,
        'name' => 'New Job with Pasting',
        'slug' => 'drip-off-new-job-with-pasting',
        'rate' => '1600.0000',
        'rate_type' => 'flat',
        'is_selectable' => true,
        'is_active' => true,
    ]);

    $repeatJobWithPasting = PricingItem::create([
        'pricing_category_id' => $dripOffCategory->id,
        'name' => 'Repeat Job with Pasting',
        'slug' => 'drip-off-repeat-job-with-pasting',
        'rate' => '300.0000',
        'rate_type' => 'flat',
        'is_selectable' => true,
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

    $pastingCategory = PricingCategory::create([
        'name' => 'Pasting',
        'slug' => 'pasting',
        'is_active' => true,
    ]);

    foreach ([
        ['4 Sides Pasting', 'four-sides-pasting', '0.4000'],
        ['4 Sides Pasting With Checking', 'four-sides-pasting-with-checking', '0.4500'],
        ['8 Sides Pasting', 'eight-sides-pasting', '0.9000'],
        ['8 Sides Pasting With Checking', 'eight-sides-pasting-with-checking', '1.0000'],
    ] as [$name, $slug, $rate]) {
        PricingItem::create([
            'pricing_category_id' => $pastingCategory->id,
            'name' => $name,
            'slug' => $slug,
            'rate' => $rate,
            'rate_type' => 'per_piece',
            'is_selectable' => false,
            'is_active' => true,
        ]);
    }

    PricingItem::create([
        'pricing_category_id' => $addOnCategory->id,
        'name' => 'Designing Cost',
        'slug' => 'designing-cost',
        'rate' => '400.0000',
        'rate_type' => 'flat',
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

    return [
        $paperItem,
        $interestItem,
        $standardPunching,
        $complicatedPunching,
        $bopp,
        $matte,
        $spotUv,
        $raisedUv,
        $newJobWithPasting,
        $repeatJobWithPasting,
    ];
}
