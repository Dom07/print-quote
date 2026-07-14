<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estimate Sandbox</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
        $input = $input ?? [];
        $result = $result ?? null;
        $paperPricingResult = $paperPricingResult ?? null;
        $punchingRateResult = $punchingRateResult ?? null;
        $laminationResult = $laminationResult ?? null;
        $spotUvResult = $spotUvResult ?? null;
        $dripOffResult = $dripOffResult ?? null;
        $pastingResult = $pastingResult ?? null;
        $totalPricePerSheetResult = $totalPricePerSheetResult ?? null;
        $piecePricingResult = $piecePricingResult ?? null;
        $marginResult = $marginResult ?? null;
        $selectedPaperItem = $selectedPaperItem ?? null;
        $selectedPunchingItem = $selectedPunchingItem ?? null;
        $selectedFrontLaminationItem = $selectedFrontLaminationItem ?? null;
        $selectedBackLaminationItem = $selectedBackLaminationItem ?? null;
        $selectedSpotUvItem = $selectedSpotUvItem ?? null;
        $selectedDripOffSetupItem = $selectedDripOffSetupItem ?? null;
        $paperOptions = $paperOptions ?? collect();
        $interestOptions = $interestOptions ?? collect();
        $punchingOptions = $punchingOptions ?? collect();
        $laminationOptions = $laminationOptions ?? collect();
        $spotUvOptions = $spotUvOptions ?? collect();
        $dripOffSetupOptions = $dripOffSetupOptions ?? collect();

        $fieldValue = fn (string $field) => old($field, $input[$field] ?? '');
        $formatKg = fn (?float $value) => $value === null ? '-' : number_format($value, 2);
        $formatMoney = fn (?float $value) => $value === null ? '-' : '₹'.number_format($value, 2);
        $formatPercent = fn (?float $value) => $value === null ? '-' : number_format($value, 4).'%';
        $formatDecimal = fn (?float $value) => $value === null ? '-' : number_format($value, 4);
        $formatDimension = fn (?float $length, ?float $width, string $unit) => $length === null || $width === null
            ? 'Not calculated'
            : number_format($length, 2).' &times; '.number_format($width, 2).' '.$unit;
        $formatDropdownPercent = fn (float $value) => rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.').'%';
        $laminationMode = fn (?string $mode) => match ($mode) {
            'front_only' => 'Front Only',
            'both_sides' => 'Both Sides',
            default => 'No Lamination',
        };
        $punchCostJobTypeLabel = fn (?string $type) => match ($type) {
            'repeat_job' => 'Repeat Job',
            'new_job' => 'New Job',
            default => '-',
        };
        $pricingModeLabel = fn (?string $type) => match ($type) {
            'minimum_flat' => 'Minimum Flat',
            'per_sheet' => 'Per Sheet',
            default => '-',
        };
        $needsFoiling = (string) $fieldValue('needs_foiling') === '1';
        $isPaperRateOverridden = (string) $fieldValue('override_paper_rate') === '1';
        $needsPunching = (string) $fieldValue('needs_punching') === '1';
        $needsLamination = (string) $fieldValue('needs_lamination') === '1';
        $needsSpotUv = (string) $fieldValue('needs_spot_uv') === '1';
        $needsDripOff = (string) $fieldValue('needs_drip_off') === '1';
        $needsPasting = (string) $fieldValue('needs_pasting') === '1';
        $needsPastingChecking = (string) $fieldValue('needs_pasting_checking') === '1';
        $needsLaceCost = (string) $fieldValue('needs_lace_cost') === '1';
        $punchCostJobType = $fieldValue('punch_cost_job_type');
        $showNewJobPunchCost = $punchCostJobType === 'new_job';
        $showLaminationBackSide = $needsLamination && $fieldValue('lamination_mode') === 'both_sides';
        $spotUvCalculationType = fn (?string $type) => $type === 'minimum_divided_by_quantity' ? 'Minimum / Quantity' : 'Per Sheet';
        $measurementUnit = old('measurement_unit', $input['measurement_unit'] ?? 'in');
        $measurementUnitLabel = fn (?string $unit) => $unit === 'cm' ? 'cm' : 'in';
    @endphp

    <main class="app-page estimate-sandbox-page">
        <div class="app-shell">
            <header class="app-header">
                <p class="app-eyebrow">Estimate Builder</p>
                <h1 class="app-title">Estimate Sandbox</h1>
                <p class="app-subtitle">Temporary calculation screen for testing paper inputs.</p>
            </header>

            <section class="app-grid app-grid--two-column">
                <form class="ui-card estimate-sandbox-form" method="POST" action="/estimate-sandbox" novalidate aria-label="Paper input form">
                    @csrf

                    <div class="ui-card__body">
                        <div class="form-grid">
                            <section class="form-section form-section--full" aria-labelledby="initial-paper-pricing-title">
                                <div class="form-section__header">
                                    <h2 class="form-section__title" id="initial-paper-pricing-title">Initial Paper Pricing</h2>
                                    <p class="form-section__subtitle">Base paper inputs used to calculate kilograms, updated paper rate, and price per sheet.</p>
                                </div>

                                <div class="form-section__fields">
                            <div class="form-group form-group--full">
                                <fieldset class="segmented-control measurement-unit-toggle" aria-label="Measurement Unit">
                                    <legend class="form-label">Measurement Unit</legend>
                                    <label class="segmented-control__option">
                                        <input type="radio" name="measurement_unit" value="in" data-measurement-unit @checked($measurementUnit === 'in')>
                                        <span>Inches</span>
                                    </label>
                                    <label class="segmented-control__option">
                                        <input type="radio" name="measurement_unit" value="cm" data-measurement-unit @checked($measurementUnit === 'cm')>
                                        <span>Centimeters</span>
                                    </label>
                                </fieldset>
                                @error('measurement_unit')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="length">Length</label>
                                <div class="input-with-suffix">
                                    <input class="form-input" id="length" name="length" type="number" step="any" min="0" value="{{ $fieldValue('length') }}">
                                    <span class="input-with-suffix__unit" data-measurement-unit-suffix>{{ $measurementUnitLabel($measurementUnit) }}</span>
                                </div>
                                @error('length')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="width">Width</label>
                                <div class="input-with-suffix">
                                    <input class="form-input" id="width" name="width" type="number" step="any" min="0" value="{{ $fieldValue('width') }}">
                                    <span class="input-with-suffix__unit" data-measurement-unit-suffix>{{ $measurementUnitLabel($measurementUnit) }}</span>
                                </div>
                                @error('width')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="gsm">GSM</label>
                                <input class="form-input" id="gsm" name="gsm" type="number" step="any" min="0" value="{{ $fieldValue('gsm') }}">
                                @error('gsm')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="no_of_sheets">No. of Sheets</label>
                                <input class="form-input" id="no_of_sheets" name="no_of_sheets" type="number" step="1" min="0" value="{{ $fieldValue('no_of_sheets') }}">
                                @error('no_of_sheets')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="no_of_sheets_with_wastage">No. of Sheets With Wastage</label>
                                <input class="form-input" id="no_of_sheets_with_wastage" name="no_of_sheets_with_wastage" type="number" step="1" min="0" value="{{ $fieldValue('no_of_sheets_with_wastage') }}">
                                @error('no_of_sheets_with_wastage')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="no_of_sheets_to_process">No. of Sheets To Process</label>
                                <input class="form-input" id="no_of_sheets_to_process" name="no_of_sheets_to_process" type="number" step="1" min="0" value="{{ $fieldValue('no_of_sheets_to_process') }}">
                                @error('no_of_sheets_to_process')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="paper_pricing_item_id">Paper Rate</label>
                                <select class="form-input" id="paper_pricing_item_id" name="paper_pricing_item_id">
                                    <option value="">Select paper rate</option>
                                    @foreach ($paperOptions as $paperOption)
                                        <option value="{{ $paperOption->id }}" @selected((string) $fieldValue('paper_pricing_item_id') === (string) $paperOption->id)>
                                            {{ $paperOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('paper_pricing_item_id')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror

                                <div class="addon-card__body ms-1">
                                    <label class="addon-card__checkbox" for="override_paper_rate">
                                        <input type="hidden" name="override_paper_rate" value="0">
                                        <input class="form-check-input" id="override_paper_rate" name="override_paper_rate" type="checkbox" value="1" data-override-paper-rate @checked($isPaperRateOverridden)>
                                        Override Paper Rate
                                    </label>
                                    @error('override_paper_rate')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror

                                    <div class="addon-card__body {{ $isPaperRateOverridden ? '' : 'is-hidden' }}" data-overridden-paper-rate-details>
                                        <label class="form-label" for="overridden_paper_rate">Overridden Paper Rate</label>
                                        <input class="form-input" id="overridden_paper_rate" name="overridden_paper_rate" type="number" step="any" min="0" value="{{ $fieldValue('overridden_paper_rate') }}">
                                        @error('overridden_paper_rate')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="interest_pricing_item_id">Interest Slab</label>
                                <select class="form-input" id="interest_pricing_item_id" name="interest_pricing_item_id">
                                    <option value="">Select interest slab</option>
                                    @foreach ($interestOptions as $interestOption)
                                        <option value="{{ $interestOption->id }}" @selected((string) $fieldValue('interest_pricing_item_id') === (string) $interestOption->id)>
                                            {{ $formatDropdownPercent((float) $interestOption->rate) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('interest_pricing_item_id')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                                </div>
                            </section>

                            <section class="form-section form-section--full" aria-labelledby="printing-ink-title">
                                <div class="form-section__header">
                                    <h2 class="form-section__title" id="printing-ink-title">Printing &amp; Ink</h2>
                                    <p class="form-section__subtitle">Manual per-sheet production costs included in total price per sheet.</p>
                                </div>

                                <div class="form-section__fields">
                            <div class="form-group">
                                <label class="form-label" for="printing_cost">Printing Cost</label>
                                <input class="form-input" id="printing_cost" name="printing_cost" type="number" step="any" min="0" value="{{ $fieldValue('printing_cost') }}">
                                @error('printing_cost')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="ink_cost">Ink Cost</label>
                                <input class="form-input" id="ink_cost" name="ink_cost" type="number" step="any" min="0" value="{{ $fieldValue('ink_cost') }}">
                                @error('ink_cost')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                                </div>
                            </section>

                            <section class="form-section form-section--full" aria-labelledby="sheet-add-ons-title">
                                <div class="form-section__header">
                                    <h2 class="form-section__title" id="sheet-add-ons-title">Sheet Add-ons</h2>
                                    <p class="form-section__subtitle">Optional sheet-level processes that contribute to total price per sheet.</p>
                                </div>

                                <div class="addon-panel-stack">
                                    <section class="addon-card" aria-labelledby="foiling-addon-title">
                                        <div class="addon-card__header">
                                            <div>
                                                <h3 class="addon-card__title" id="foiling-addon-title">Foiling</h3>
                                                <p class="addon-card__description">Add a manual per-sheet foiling cost.</p>
                                            </div>
                                            <label class="addon-card__checkbox" for="needs_foiling">
                                                <input type="hidden" name="needs_foiling" value="0">
                                                <input class="form-check-input" id="needs_foiling" name="needs_foiling" type="checkbox" value="1" data-needs-foiling @checked($needsFoiling)>
                                                <span>Apply Foiling</span>
                                            </label>
                                        </div>
                                        @error('needs_foiling')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror

                                        <div class="addon-card__body {{ $needsFoiling ? '' : 'is-hidden' }}" data-foiling-details>
                                            <div class="form-group">
                                                <label class="form-label" for="foiling_cost">Foiling Cost</label>
                                                <input class="form-input" id="foiling_cost" name="foiling_cost" type="number" step="any" min="0" value="{{ $fieldValue('foiling_cost') }}">
                                                @error('foiling_cost')
                                                    <p class="form-error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </section>

                                    <section class="addon-card" aria-labelledby="punching-addon-title">
                                        <div class="addon-card__header">
                                            <div>
                                                <h3 class="addon-card__title" id="punching-addon-title">Punching</h3>
                                                <p class="addon-card__description">Apply punching cost based on the selected punching type.</p>
                                            </div>
                                            <label class="addon-card__checkbox" for="needs_punching">
                                                <input type="hidden" name="needs_punching" value="0">
                                                <input class="form-check-input" id="needs_punching" name="needs_punching" type="checkbox" value="1" data-needs-punching @checked($needsPunching)>
                                                <span>Apply Punching</span>
                                            </label>
                                        </div>
                                        @error('needs_punching')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror

                                        <div class="addon-card__body {{ $needsPunching ? '' : 'is-hidden' }}" data-punching-details>
                                            <div class="form-group">
                                                <label class="form-label" for="punching_pricing_item_id">Punching Type</label>
                                                <select class="form-input" id="punching_pricing_item_id" name="punching_pricing_item_id">
                                                    <option value="">No Punching</option>
                                                    @foreach ($punchingOptions as $punchingOption)
                                                        <option value="{{ $punchingOption->id }}" @selected((string) $fieldValue('punching_pricing_item_id') === (string) $punchingOption->id)>
                                                            {{ $punchingOption->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('punching_pricing_item_id')
                                                    <p class="form-error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </section>

                                    <section class="addon-card" aria-labelledby="lamination-addon-title">
                                        <div class="addon-card__header">
                                            <div>
                                                <h3 class="addon-card__title" id="lamination-addon-title">Lamination</h3>
                                                <p class="addon-card__description">Apply front-only or both-side lamination to processed sheets.</p>
                                            </div>
                                            <label class="addon-card__checkbox" for="needs_lamination">
                                                <input type="hidden" name="needs_lamination" value="0">
                                                <input class="form-check-input" id="needs_lamination" name="needs_lamination" type="checkbox" value="1" data-needs-lamination @checked($needsLamination)>
                                                <span>Apply Lamination</span>
                                            </label>
                                        </div>
                                        @error('needs_lamination')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror

                                        <div class="addon-card__body {{ $needsLamination ? '' : 'is-hidden' }}" data-lamination-details>
                                            <div class="addon-card__nested-grid">
                                                <div class="form-group addon-card__nested-grid-full">
                                                    <label class="form-label" for="lamination_mode">Lamination Coverage</label>
                                                    <select class="form-input" style="max-width: 275px;" id="lamination_mode" name="lamination_mode" data-lamination-mode>
                                                        <option value="">Select coverage</option>
                                                        <option value="front_only" @selected($fieldValue('lamination_mode') === 'front_only')>Front Only</option>
                                                        <option value="both_sides" @selected($fieldValue('lamination_mode') === 'both_sides')>Both Sides</option>
                                                    </select>
                                                    @error('lamination_mode')
                                                        <p class="form-error">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label" for="lamination_front_pricing_item_id">Front Side Lamination Type</label>
                                                    <select class="form-input" id="lamination_front_pricing_item_id" name="lamination_front_pricing_item_id">
                                                        <option value="">Select lamination</option>
                                                        @foreach ($laminationOptions as $laminationOption)
                                                            <option value="{{ $laminationOption->id }}" @selected((string) $fieldValue('lamination_front_pricing_item_id') === (string) $laminationOption->id)>
                                                                {{ $laminationOption->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('lamination_front_pricing_item_id')
                                                        <p class="form-error">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                <div class="form-group {{ $showLaminationBackSide ? '' : 'is-hidden' }}" data-lamination-back-side>
                                                    <label class="form-label" for="lamination_back_pricing_item_id">Back Side Lamination Type</label>
                                                    <select class="form-input" id="lamination_back_pricing_item_id" name="lamination_back_pricing_item_id">
                                                        <option value="">Select lamination</option>
                                                        @foreach ($laminationOptions as $laminationOption)
                                                            <option value="{{ $laminationOption->id }}" @selected((string) $fieldValue('lamination_back_pricing_item_id') === (string) $laminationOption->id)>
                                                                {{ $laminationOption->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('lamination_back_pricing_item_id')
                                                        <p class="form-error">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <section class="addon-card" aria-labelledby="spot-uv-addon-title">
                                        <div class="addon-card__header">
                                            <div>
                                                <h3 class="addon-card__title" id="spot-uv-addon-title">Spot UV</h3>
                                                <p class="addon-card__description">Apply spot UV or raised UV cost to processed sheets.</p>
                                            </div>
                                            <label class="addon-card__checkbox" for="needs_spot_uv">
                                                <input type="hidden" name="needs_spot_uv" value="0">
                                                <input class="form-check-input" id="needs_spot_uv" name="needs_spot_uv" type="checkbox" value="1" data-needs-spot-uv @checked($needsSpotUv)>
                                                <span>Apply Spot UV</span>
                                            </label>
                                        </div>
                                        @error('needs_spot_uv')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror

                                        <div class="addon-card__body {{ $needsSpotUv ? '' : 'is-hidden' }}" data-spot-uv-details>
                                            <div class="form-group">
                                                <label class="form-label" for="spot_uv_pricing_item_id">UV Type</label>
                                                <select class="form-input" id="spot_uv_pricing_item_id" name="spot_uv_pricing_item_id">
                                                    <option value="">Select UV type</option>
                                                    @foreach ($spotUvOptions as $spotUvOption)
                                                        <option value="{{ $spotUvOption->id }}" @selected((string) $fieldValue('spot_uv_pricing_item_id') === (string) $spotUvOption->id)>
                                                            {{ $spotUvOption->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('spot_uv_pricing_item_id')
                                                    <p class="form-error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </section>

                                    <section class="addon-card" aria-labelledby="drip-off-addon-title">
                                        <div class="addon-card__header">
                                            <div>
                                                <h3 class="addon-card__title" id="drip-off-addon-title">Drip Off</h3>
                                                <p class="addon-card__description">Apply drip off based on configured drip-off rates.</p>
                                            </div>
                                            <label class="addon-card__checkbox" for="needs_drip_off">
                                                <input type="hidden" name="needs_drip_off" value="0">
                                                <input class="form-check-input" id="needs_drip_off" name="needs_drip_off" type="checkbox" value="1" data-needs-drip-off @checked($needsDripOff)>
                                                <span>Apply Drip Off</span>
                                            </label>
                                        </div>
                                        @error('needs_drip_off')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror

                                        <div class="addon-card__body {{ $needsDripOff ? '' : 'is-hidden' }}" data-drip-off-details>
                                            <div class="form-group">
                                                <label class="form-label" for="drip_off_setup_pricing_item_id">Drip Off Setup Option</label>
                                                <select class="form-input" id="drip_off_setup_pricing_item_id" name="drip_off_setup_pricing_item_id">
                                                    <option value="">Select setup option</option>
                                                    @foreach ($dripOffSetupOptions as $dripOffSetupOption)
                                                        <option value="{{ $dripOffSetupOption->id }}" @selected((string) $fieldValue('drip_off_setup_pricing_item_id') === (string) $dripOffSetupOption->id)>
                                                            {{ $dripOffSetupOption->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('drip_off_setup_pricing_item_id')
                                                    <p class="form-error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </section>

                                </div>
                            </section>

                            <section class="form-section form-section--full" aria-labelledby="price-per-piece-title">
                                <div class="form-section__header">
                                    <h2 class="form-section__title" id="price-per-piece-title">Price Per Piece</h2>
                                    <p class="form-section__subtitle">Inputs used after total price per sheet is calculated to determine piece cost, order total, and margin.</p>
                                </div>

                                <div class="form-section__fields">
                                    <section class="form-section form-section--full" aria-labelledby="piece-inputs-title">
                                        <div class="form-section__header">
                                            <h2 class="form-section__title" id="piece-inputs-title">Piece Inputs</h2>
                                            <p class="form-section__subtitle">Inputs used to convert sheet pricing into piece pricing.</p>
                                        </div>

                                        <div class="form-section__fields">
                                            <div class="form-group">
                                                <label class="form-label" for="ups">Ups</label>
                                                <input class="form-input" id="ups" name="ups" type="number" step="1" min="1" value="{{ $fieldValue('ups') }}">
                                                @error('ups')
                                                    <p class="form-error">{{ $message }}</p>
                                                @enderror
                                            </div>

                                        </div>
                                    </section>

                                    <section class="form-section form-section--full" aria-labelledby="piece-add-ons-title">
                                        <div class="form-section__header">
                                            <h2 class="form-section__title" id="piece-add-ons-title">Piece Add-ons</h2>
                                            <p class="form-section__subtitle">Optional piece-level add-ons applied after base price per piece is calculated.</p>
                                        </div>

                                        <div class="addon-panel-stack">
                                            <div class="form-group">
                                                <label class="form-label" for="window_labor_cost">Window &amp; Labor Cost</label>
                                                <input class="form-input" id="window_labor_cost" name="window_labor_cost" type="number" step="any" min="0" value="{{ $fieldValue('window_labor_cost') }}">
                                                @error('window_labor_cost')
                                                    <p class="form-error">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <section class="addon-card" aria-labelledby="pasting-addon-title">
                                                <div class="addon-card__header">
                                                    <div>
                                                        <h3 class="addon-card__title" id="pasting-addon-title">Pasting</h3>
                                                        <p class="addon-card__description">Add a per-piece pasting cost.</p>
                                                    </div>
                                                    <label class="addon-card__checkbox" for="needs_pasting">
                                                        <input type="hidden" name="needs_pasting" value="0">
                                                        <input class="form-check-input" id="needs_pasting" name="needs_pasting" type="checkbox" value="1" data-needs-pasting @checked($needsPasting)>
                                                        <span>Apply Pasting</span>
                                                    </label>
                                                </div>
                                                @error('needs_pasting')
                                                    <p class="form-error">{{ $message }}</p>
                                                @enderror

                                                <div class="addon-card__body {{ $needsPasting ? '' : 'is-hidden' }}" data-pasting-details>
                                                    <div class="form-group">
                                                        <label class="form-label" for="pasting_sides">Pasting Type</label>
                                                        <select class="form-input" id="pasting_sides" name="pasting_sides">
                                                            <option value="">Select pasting type</option>
                                                            <option value="four_sides" @selected($fieldValue('pasting_sides') === 'four_sides')>4 Sides</option>
                                                            <option value="eight_sides" @selected($fieldValue('pasting_sides') === 'eight_sides')>8 Sides</option>
                                                        </select>
                                                        @error('pasting_sides')
                                                            <p class="form-error">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <label class="addon-card__checkbox mt-5 ms-1" for="needs_pasting_checking">
                                                        <input type="hidden" name="needs_pasting_checking" value="0">
                                                        <input class="form-check-input" id="needs_pasting_checking" name="needs_pasting_checking" type="checkbox" value="1" @checked($needsPastingChecking)>
                                                        <span>Include Checking</span>
                                                    </label>
                                                    @error('needs_pasting_checking')
                                                        <p class="form-error">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </section>

                                            <section class="addon-card" aria-labelledby="lace-addon-title">
                                                <div class="addon-card__header">
                                                    <div>
                                                        <h3 class="addon-card__title" id="lace-addon-title">Lace</h3>
                                                        <p class="addon-card__description">Apply configured per-piece lace cost.</p>
                                                    </div>
                                                    <label class="addon-card__checkbox" for="needs_lace_cost">
                                                        <input type="hidden" name="needs_lace_cost" value="0">
                                                        <input class="form-check-input" id="needs_lace_cost" name="needs_lace_cost" type="checkbox" value="1" @checked($needsLaceCost)>
                                                        <span>Apply Lace</span>
                                                    </label>
                                                </div>
                                                @error('needs_lace_cost')
                                                    <p class="form-error">{{ $message }}</p>
                                                @enderror
                                            </section>
                                        </div>
                                    </section>

                                    <section class="form-section form-section--full" aria-labelledby="required-piece-costs-title">
                                        <div class="form-section__header">
                                            <h2 class="form-section__title" id="required-piece-costs-title">Required Piece Costs</h2>
                                            <p class="form-section__subtitle">These required fields are captured for later piece cost calculation.</p>
                                        </div>

                                        <div class="form-section__fields">
                                    <div class="form-group">
                                        <label class="form-label" for="punch_cost_job_type">Job Type</label>
                                        <select class="form-input" id="punch_cost_job_type" name="punch_cost_job_type" data-punch-cost-job-type>
                                            <option value="">Select job type</option>
                                            <option value="repeat_job" @selected($punchCostJobType === 'repeat_job')>Repeat Job</option>
                                            <option value="new_job" @selected($punchCostJobType === 'new_job')>New Job</option>
                                        </select>
                                        @error('punch_cost_job_type')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group {{ $showNewJobPunchCost ? '' : 'is-hidden' }}" data-new-job-punch-cost>
                                        <label class="form-label" for="new_job_punch_cost">New Job Punch Cost</label>
                                        <input class="form-input" id="new_job_punch_cost" name="new_job_punch_cost" type="number" step="any" min="0" value="{{ $fieldValue('new_job_punch_cost') }}">
                                        @error('new_job_punch_cost')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="expenses">Expenses</label>
                                        <input class="form-input" id="expenses" name="expenses" type="number" step="any" min="0" value="{{ $fieldValue('expenses') }}">
                                        @error('expenses')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                        </div>
                                    </section>
                                </div>
                            </section>
                        </div>

                        <div class="form-actions">
                            <button class="btn btn-primary" type="submit">Calculate</button>
                        </div>
                    </div>
                </form>

                <aside class="ui-card estimate-sandbox-results" aria-label="Paper kilogram results">
                    <div class="ui-card__header">
                        <h2 class="ui-card__title">Result</h2>
                        <p class="ui-card__subtitle">Paper kilograms calculated from the entered sheet counts.</p>
                    </div>

                    <div class="ui-card__body">
                        <div class="result-stack">
                            <div class="result-item">
                                <span class="result-label">Entered Size</span>
                                <span class="result-value result-value--dimension">{!! $formatDimension($result['entered_length'] ?? null, $result['entered_width'] ?? null, $measurementUnitLabel($result['measurement_unit'] ?? null)) !!}</span>
                            </div>

                            @if (($result['measurement_unit'] ?? null) === 'cm' && ($result['length_in_inches'] ?? null) !== null && ($result['width_in_inches'] ?? null) !== null)
                                <div class="result-item">
                                    <span class="result-label">Calculation Size</span>
                                    <span class="result-value result-value--dimension">{!! $formatDimension($result['length_in_inches'] ?? null, $result['width_in_inches'] ?? null, 'in') !!}</span>
                                </div>
                            @endif

                            <div class="result-item">
                                <span class="result-label">KG for No. of Sheets</span>
                                <span class="result-value">{{ $formatKg($result['no_of_sheets'] ?? null) }}</span>
                            </div>

                            <div class="result-item">
                                <span class="result-label">KG for Sheets With Wastage</span>
                                <span class="result-value">{{ $formatKg($result['no_of_sheets_with_wastage'] ?? null) }}</span>
                            </div>

                            <div class="result-item">
                                <span class="result-label">KG for Sheets To Process</span>
                                <span class="result-value">{{ $formatKg($result['no_of_sheets_to_process'] ?? null) }}</span>
                            </div>
                        </div>

                        <h3 class="result-section-title">Paper Pricing</h3>

                        <div class="result-stack">
                            <div class="result-item">
                                <span class="result-label">Selected Paper</span>
                                <span class="result-value">{{ $selectedPaperItem->name ?? '-' }}</span>
                            </div>

                            <div class="result-item">
                                <span class="result-label">Configured Paper Rate</span>
                                <span class="result-value">{{ $formatMoney($paperPricingResult['configured_paper_rate'] ?? null) }}</span>
                            </div>

                            <div class="result-item">
                                <span class="result-label">Paper Rate Overridden</span>
                                <span class="result-value">{{ ($paperPricingResult['is_paper_rate_overridden'] ?? false) ? 'Yes' : 'No' }}</span>
                            </div>

                            @if ($paperPricingResult['is_paper_rate_overridden'] ?? false)
                                <div class="result-item">
                                    <span class="result-label">Overridden Paper Rate</span>
                                    <span class="result-value">{{ $formatMoney($paperPricingResult['overridden_paper_rate'] ?? null) }}</span>
                                </div>
                            @endif

                            <div class="result-item">
                                <span class="result-label">Effective Paper Rate</span>
                                <span class="result-value">{{ $formatMoney($paperPricingResult['effective_paper_rate'] ?? null) }}</span>
                            </div>

                            <div class="result-item">
                                <span class="result-label">Interest %</span>
                                <span class="result-value">{{ $formatPercent($paperPricingResult['interest_percentage'] ?? null) }}</span>
                            </div>

                            <div class="result-item">
                                <span class="result-label">Updated Paper Rate</span>
                                <span class="result-value">{{ $formatMoney($paperPricingResult['updated_paper_rate'] ?? null) }}</span>
                            </div>

                            <div class="result-item">
                                <span class="result-label">Price Per Sheet</span>
                                <span class="result-value">{{ $formatMoney($paperPricingResult['price_per_sheet'] ?? null) }}</span>
                            </div>
                        </div>

                        @if (isset($input['printing_cost'], $input['ink_cost']))
                            <h3 class="result-section-title">Manual Costs</h3>

                            <div class="result-stack">
                                <div class="result-item">
                                    <span class="result-label">Printing Cost</span>
                                    <span class="result-value">{{ $formatMoney((float) $input['printing_cost']) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Ink Cost</span>
                                    <span class="result-value">{{ $formatMoney((float) $input['ink_cost']) }}</span>
                                </div>

                                @if (($input['needs_foiling'] ?? false) && ($input['foiling_cost'] ?? null) !== null)
                                    <div class="result-item">
                                        <span class="result-label">Foiling Cost</span>
                                        <span class="result-value">{{ $formatMoney((float) $input['foiling_cost']) }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <h3 class="result-section-title">Punching</h3>

                        <div class="result-stack">
                            @if (! $needsPunching)
                                <div class="result-item">
                                    <span class="result-label">Punching</span>
                                    <span class="result-value">No Punching</span>
                                </div>
                            @else
                                <div class="result-item">
                                    <span class="result-label">Punching Type</span>
                                    <span class="result-value">{{ $selectedPunchingItem?->name ?? '-' }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Pricing Type</span>
                                    <span class="result-value">{{ $pricingModeLabel($punchingRateResult['pricing_mode'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Configured Rate</span>
                                    <span class="result-value">{{ $formatMoney($punchingRateResult['configured_rate'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Effective Rate Per Sheet</span>
                                    <span class="result-value">{{ $formatMoney($punchingRateResult['rate'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Total Punching Charge</span>
                                    <span class="result-value">{{ $formatMoney($punchingRateResult['total_charge'] ?? null) }}</span>
                                </div>
                            @endif
                        </div>

                        <h3 class="result-section-title">Lamination</h3>

                        <div class="result-stack">
                            @if ($laminationResult === null)
                                <div class="result-item">
                                    <span class="result-label">Lamination</span>
                                    <span class="result-value">No Lamination</span>
                                </div>
                            @elseif (($laminationResult['mode'] ?? null) === 'front_only')
                                <div class="result-item">
                                    <span class="result-label">Lamination Mode</span>
                                    <span class="result-value">{{ $laminationMode($laminationResult['mode']) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Selected Lamination</span>
                                    <span class="result-value">{{ $laminationResult['front']['name'] ?? '-' }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Coefficient</span>
                                    <span class="result-value">{{ $formatDecimal($laminationResult['front']['coefficient'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Lamination Value</span>
                                    <span class="result-value">{{ $formatMoney($laminationResult['front']['value'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Combined Lamination Value</span>
                                    <span class="result-value">{{ $formatMoney($laminationResult['combined_value'] ?? null) }}</span>
                                </div>
                            @else
                                <div class="result-item">
                                    <span class="result-label">Lamination Mode</span>
                                    <span class="result-value">{{ $laminationMode($laminationResult['mode'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Front Side</span>
                                    <span class="result-value">{{ $laminationResult['front']['name'] ?? '-' }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Front Coefficient</span>
                                    <span class="result-value">{{ $formatDecimal($laminationResult['front']['coefficient'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Front Lamination Value</span>
                                    <span class="result-value">{{ $formatMoney($laminationResult['front']['value'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Back Side</span>
                                    <span class="result-value">{{ $laminationResult['back']['name'] ?? '-' }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Back Coefficient</span>
                                    <span class="result-value">{{ $formatDecimal($laminationResult['back']['coefficient'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Back Lamination Value</span>
                                    <span class="result-value">{{ $formatMoney($laminationResult['back']['value'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Combined Lamination Value</span>
                                    <span class="result-value">{{ $formatMoney($laminationResult['combined_value'] ?? null) }}</span>
                                </div>
                            @endif
                        </div>

                        <h3 class="result-section-title">Spot UV</h3>

                        <div class="result-stack">
                            @if (! $needsSpotUv)
                                <div class="result-item">
                                    <span class="result-label">Spot UV</span>
                                    <span class="result-value">No Spot UV</span>
                                </div>
                            @else
                                <div class="result-item">
                                    <span class="result-label">UV Type</span>
                                    <span class="result-value">{{ $selectedSpotUvItem?->name ?? '-' }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Quantity Used</span>
                                    <span class="result-value">{{ $spotUvResult['quantity'] ?? '-' }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Calculation Type</span>
                                    <span class="result-value">{{ $spotUvCalculationType($spotUvResult['calculation_type'] ?? null) }}</span>
                                </div>

                                @if (isset($spotUvResult['resolved_amount']))
                                    <div class="result-item">
                                        <span class="result-label">Resolved Amount</span>
                                        <span class="result-value">{{ $formatMoney($spotUvResult['resolved_amount']) }}</span>
                                    </div>
                                @endif

                                <div class="result-item">
                                    <span class="result-label">Spot UV Value</span>
                                    <span class="result-value">{{ $formatMoney($spotUvResult['value'] ?? null) }}</span>
                                </div>
                            @endif
                        </div>

                        <h3 class="result-section-title">Drip Off</h3>

                        <div class="result-stack">
                            @if (! $needsDripOff)
                                <div class="result-item">
                                    <span class="result-label">Drip Off</span>
                                    <span class="result-value">No Drip Off</span>
                                </div>
                            @else
                                <div class="result-item">
                                    <span class="result-label">Setup Option</span>
                                    <span class="result-value">{{ $dripOffResult['flat_add_on_name'] ?? $selectedDripOffSetupItem?->name ?? '-' }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Coefficient</span>
                                    <span class="result-value">{{ $formatDecimal($dripOffResult['coefficient'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Quantity Used</span>
                                    <span class="result-value">{{ $dripOffResult['quantity'] ?? '-' }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Base Rate Per Sheet</span>
                                    <span class="result-value">{{ $formatMoney($dripOffResult['base_rate_per_sheet'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Base Cost</span>
                                    <span class="result-value">{{ $formatMoney($dripOffResult['base_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Minimum Cost</span>
                                    <span class="result-value">{{ $formatMoney($dripOffResult['minimum_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Minimum Adjusted Base Cost</span>
                                    <span class="result-value">{{ $formatMoney($dripOffResult['minimum_adjusted_base_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Minimum Adjusted Base Rate Per Sheet</span>
                                    <span class="result-value">{{ $formatMoney($dripOffResult['minimum_adjusted_base_rate_per_sheet'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Flat Add-On Amount</span>
                                    <span class="result-value">{{ $formatMoney($dripOffResult['flat_add_on_amount'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Flat Add-On Rate Per Sheet</span>
                                    <span class="result-value">{{ $formatMoney($dripOffResult['flat_add_on_rate_per_sheet'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Final Drip Off Rate Per Sheet</span>
                                    <span class="result-value">{{ $formatMoney($dripOffResult['final_rate_per_sheet'] ?? null) }}</span>
                                </div>
                            @endif
                        </div>

                        @if ($totalPricePerSheetResult !== null)
                            <h3 class="result-section-title">Total Price Per Sheet</h3>

                            <div class="result-stack">
                                <div class="result-item">
                                    <span class="result-label">Paper Price Per Sheet</span>
                                    <span class="result-value">{{ $formatMoney($totalPricePerSheetResult['components']['paper_price_per_sheet'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Printing Cost</span>
                                    <span class="result-value">{{ $formatMoney($totalPricePerSheetResult['components']['printing_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Ink Cost</span>
                                    <span class="result-value">{{ $formatMoney($totalPricePerSheetResult['components']['ink_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Foiling Cost</span>
                                    <span class="result-value">{{ $formatMoney($totalPricePerSheetResult['components']['foiling_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Punching Rate</span>
                                    <span class="result-value">{{ $formatMoney($totalPricePerSheetResult['components']['punching_rate'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Lamination Value</span>
                                    <span class="result-value">{{ $formatMoney($totalPricePerSheetResult['components']['lamination_value'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Spot UV Value</span>
                                    <span class="result-value">{{ $formatMoney($totalPricePerSheetResult['components']['spot_uv_value'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Drip Off Rate</span>
                                    <span class="result-value">{{ $formatMoney($totalPricePerSheetResult['components']['drip_off_rate'] ?? null) }}</span>
                                </div>

                                <div class="result-item result-item--total">
                                    <span class="result-label">Total Price Per Sheet</span>
                                    <span class="result-value">{{ $formatMoney($totalPricePerSheetResult['total_price_per_sheet'] ?? null) }}</span>
                                </div>
                            </div>
                        @endif

                        @if ($piecePricingResult !== null)
                            <h3 class="result-section-title">Piece Pricing</h3>

                            <div class="result-stack">
                                <div class="result-item">
                                    <span class="result-label">Ups</span>
                                    <span class="result-value">{{ $piecePricingResult['ups'] ?? '-' }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Number of Pieces</span>
                                    <span class="result-value">{{ $piecePricingResult['number_of_pieces'] ?? '-' }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Base Price Per Piece</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['base_price_per_piece'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Window &amp; Labor Cost</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['window_labor_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Pasting Cost</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['pasting_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Lace Cost</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['lace_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Optional Piece Costs Total</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['optional_piece_costs_total'] ?? null) }}</span>
                                </div>
                            </div>

                            <h3 class="result-section-title">Required Piece Costs</h3>

                            <div class="result-stack">

                                <div class="result-item">
                                    <span class="result-label">Punch Cost Job Type</span>
                                    <span class="result-value">{{ $punchCostJobTypeLabel($piecePricingResult['punch_cost_job_type'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Punch Cost</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['punch_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Designing Cost</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['designing_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Expenses</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['expenses'] ?? null) }}</span>
                                </div>

                                <div class="result-item result-item--total">
                                    <span class="result-label">Required Piece Costs Total</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['required_piece_costs_total'] ?? null) }}</span>
                                </div>
                            </div>

                            <h3 class="result-section-title">Final Piece Totals</h3>

                            <div class="result-stack">
                                <div class="result-item result-item--total">
                                    <span class="result-label">Total Piece Cost</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['total_piece_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item result-item--total">
                                    <span class="result-label">Total Cost</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['total_cost'] ?? null) }}</span>
                                </div>
                            </div>
                        @endif

                        @if ($marginResult !== null)
                            <h3 class="result-section-title">Margin</h3>

                            <div class="result-stack">
                                <div class="result-item">
                                    <span class="result-label">Selected Margin Slab</span>
                                    <span class="result-value">{{ $marginResult['selected_margin_slab_name'] ?? '-' }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Margin Percentage</span>
                                    <span class="result-value">{{ $formatPercent($marginResult['margin_percentage'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Margin Amount</span>
                                    <span class="result-value">{{ $formatMoney($marginResult['margin_amount'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Total Cost With Margin</span>
                                    <span class="result-value">{{ $formatMoney($marginResult['total_cost_with_margin'] ?? null) }}</span>
                                </div>

                                <div class="result-item result-item--total">
                                    <span class="result-label">Selling Price</span>
                                    <span class="result-value">{{ $formatMoney($marginResult['selling_price'] ?? null) }}</span>
                                </div>
                            </div>
                        @endif

                        <p class="result-note">Temporary paper weight calculation only. No values are saved.</p>
                    </div>
                </aside>
            </section>
        </div>
    </main>
<script>
    (() => {
        const toggle = (elements, isVisible) => {
            elements.forEach((element) => element.classList.toggle('is-hidden', !isVisible));
        };

        const needsFoiling = document.querySelector('[data-needs-foiling]');
        const overridePaperRate = document.querySelector('[data-override-paper-rate]');
        const overriddenPaperRateDetails = document.querySelectorAll('[data-overridden-paper-rate-details]');
        const foilingDetails = document.querySelectorAll('[data-foiling-details]');
        const needsPunching = document.querySelector('[data-needs-punching]');
        const punchingDetails = document.querySelectorAll('[data-punching-details]');
        const needsLamination = document.querySelector('[data-needs-lamination]');
        const laminationMode = document.querySelector('[data-lamination-mode]');
        const laminationDetails = document.querySelectorAll('[data-lamination-details]');
        const laminationBackSide = document.querySelectorAll('[data-lamination-back-side]');
        const needsSpotUv = document.querySelector('[data-needs-spot-uv]');
        const spotUvDetails = document.querySelectorAll('[data-spot-uv-details]');
        const needsDripOff = document.querySelector('[data-needs-drip-off]');
        const dripOffDetails = document.querySelectorAll('[data-drip-off-details]');
        const needsPasting = document.querySelector('[data-needs-pasting]');
        const pastingDetails = document.querySelectorAll('[data-pasting-details]');
        const punchCostJobType = document.querySelector('[data-punch-cost-job-type]');
        const newJobPunchCost = document.querySelectorAll('[data-new-job-punch-cost]');
        const measurementUnits = document.querySelectorAll('[data-measurement-unit]');
        const measurementUnitSuffixes = document.querySelectorAll('[data-measurement-unit-suffix]');

        const syncFoiling = () => {
            toggle(foilingDetails, needsFoiling?.checked === true);
        };

        const syncPaperRateOverride = () => {
            toggle(overriddenPaperRateDetails, overridePaperRate?.checked === true);
        };

        const syncPunching = () => {
            toggle(punchingDetails, needsPunching?.checked === true);
        };

        const syncLamination = () => {
            const isNeeded = needsLamination?.checked === true;
            toggle(laminationDetails, isNeeded);
            toggle(laminationBackSide, isNeeded && laminationMode?.value === 'both_sides');
        };

        const syncSpotUv = () => {
            toggle(spotUvDetails, needsSpotUv?.checked === true);
        };

        const syncDripOff = () => {
            toggle(dripOffDetails, needsDripOff?.checked === true);
        };

        const syncPasting = () => {
            toggle(pastingDetails, needsPasting?.checked === true);
        };

        const syncNewJobPunchCost = () => {
            toggle(newJobPunchCost, punchCostJobType?.value === 'new_job');
        };

        const syncMeasurementUnit = () => {
            const selectedUnit = document.querySelector('[data-measurement-unit]:checked');
            const unit = selectedUnit?.value === 'cm' ? 'cm' : 'in';
            measurementUnitSuffixes.forEach((suffix) => {
                suffix.textContent = unit;
            });
        };

        needsFoiling?.addEventListener('change', syncFoiling);
        overridePaperRate?.addEventListener('change', syncPaperRateOverride);
        needsPunching?.addEventListener('change', syncPunching);
        needsLamination?.addEventListener('change', syncLamination);
        laminationMode?.addEventListener('change', syncLamination);
        needsSpotUv?.addEventListener('change', syncSpotUv);
        needsDripOff?.addEventListener('change', syncDripOff);
        needsPasting?.addEventListener('change', syncPasting);
        punchCostJobType?.addEventListener('change', syncNewJobPunchCost);
        measurementUnits.forEach((unit) => {
            unit.addEventListener('change', syncMeasurementUnit);
        });

        syncFoiling();
        syncPaperRateOverride();
        syncPunching();
        syncLamination();
        syncSpotUv();
        syncDripOff();
        syncPasting();
        syncNewJobPunchCost();
        syncMeasurementUnit();
    })();
</script>
</body>
</html>
