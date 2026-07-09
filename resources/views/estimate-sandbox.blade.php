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
        $totalPricePerSheetResult = $totalPricePerSheetResult ?? null;
        $piecePricingResult = $piecePricingResult ?? null;
        $marginResult = $marginResult ?? null;
        $selectedPunchingItem = $selectedPunchingItem ?? null;
        $selectedFrontLaminationItem = $selectedFrontLaminationItem ?? null;
        $selectedBackLaminationItem = $selectedBackLaminationItem ?? null;
        $selectedSpotUvItem = $selectedSpotUvItem ?? null;
        $paperOptions = $paperOptions ?? collect();
        $interestOptions = $interestOptions ?? collect();
        $punchingOptions = $punchingOptions ?? collect();
        $laminationOptions = $laminationOptions ?? collect();
        $spotUvOptions = $spotUvOptions ?? collect();

        $fieldValue = fn (string $field) => old($field, $input[$field] ?? '');
        $formatKg = fn (?float $value) => $value === null ? '-' : number_format(round($value, 2, PHP_ROUND_HALF_UP), 2);
        $formatMoney = fn (?float $value) => $value === null ? '-' : '₹'.number_format($value, 2);
        $formatPercent = fn (?float $value) => $value === null ? '-' : number_format($value, 4).'%';
        $formatDecimal = fn (?float $value) => $value === null ? '-' : number_format($value, 4);
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
        $needsPunching = (string) $fieldValue('needs_punching') === '1';
        $needsLamination = (string) $fieldValue('needs_lamination') === '1';
        $needsSpotUv = (string) $fieldValue('needs_spot_uv') === '1';
        $needsDripOff = (string) $fieldValue('needs_drip_off') === '1';
        $needsLaceCost = (string) $fieldValue('needs_lace_cost') === '1';
        $punchCostJobType = $fieldValue('punch_cost_job_type');
        $showNewJobPunchCost = $punchCostJobType === 'new_job';
        $showLaminationBackSide = $needsLamination && $fieldValue('lamination_mode') === 'both_sides';
        $spotUvCalculationType = fn (?string $type) => $type === 'minimum_divided_by_quantity' ? 'Minimum / Quantity' : 'Per Sheet';
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
                            <div class="form-group">
                                <label class="form-label" for="length">Length (inches)</label>
                                <input class="form-input" id="length" name="length" type="number" step="any" min="0" value="{{ $fieldValue('length') }}">
                                @error('length')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="width">Width (inches)</label>
                                <input class="form-input" id="width" name="width" type="number" step="any" min="0" value="{{ $fieldValue('width') }}">
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

                            <div class="form-group">
                                <label class="form-label" for="foiling_cost">Foiling Cost</label>
                                <input class="form-input" id="foiling_cost" name="foiling_cost" type="number" step="any" min="0" value="{{ $fieldValue('foiling_cost') }}">
                                @error('foiling_cost')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="needs_punching">Need Punching?</label>
                                <select class="form-input" id="needs_punching" name="needs_punching" data-needs-punching>
                                    <option value="0" @selected(! $needsPunching)>No</option>
                                    <option value="1" @selected($needsPunching)>Yes</option>
                                </select>
                                @error('needs_punching')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group {{ $needsPunching ? '' : 'is-hidden' }}" data-punching-details>
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

                            <div class="form-group">
                                <label class="form-label" for="needs_lamination">Need Lamination?</label>
                                <select class="form-input" id="needs_lamination" name="needs_lamination" data-needs-lamination>
                                    <option value="0" @selected(! $needsLamination)>No</option>
                                    <option value="1" @selected($needsLamination)>Yes</option>
                                </select>
                                @error('needs_lamination')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group {{ $needsLamination ? '' : 'is-hidden' }}" data-lamination-details>
                                <label class="form-label" for="lamination_mode">Lamination Coverage</label>
                                <select class="form-input" id="lamination_mode" name="lamination_mode" data-lamination-mode>
                                    <option value="">Select coverage</option>
                                    <option value="front_only" @selected($fieldValue('lamination_mode') === 'front_only')>Front Only</option>
                                    <option value="both_sides" @selected($fieldValue('lamination_mode') === 'both_sides')>Both Sides</option>
                                </select>
                                @error('lamination_mode')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group {{ $needsLamination ? '' : 'is-hidden' }}" data-lamination-details>
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

                            <div class="form-group">
                                <label class="form-label" for="needs_spot_uv">Need Spot UV?</label>
                                <select class="form-input" id="needs_spot_uv" name="needs_spot_uv" data-needs-spot-uv>
                                    <option value="0" @selected(! $needsSpotUv)>No</option>
                                    <option value="1" @selected($needsSpotUv)>Yes</option>
                                </select>
                                @error('needs_spot_uv')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group {{ $needsSpotUv ? '' : 'is-hidden' }}" data-spot-uv-details>
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

                            <div class="form-group">
                                <label class="form-label" for="needs_drip_off">Need Drip Off?</label>
                                <select class="form-input" id="needs_drip_off" name="needs_drip_off">
                                    <option value="0" @selected(! $needsDripOff)>No</option>
                                    <option value="1" @selected($needsDripOff)>Yes</option>
                                </select>
                                @error('needs_drip_off')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <section class="form-section form-section--full" aria-labelledby="piece-level-costs-title">
                                <div class="form-section__header">
                                    <h2 class="form-section__title" id="piece-level-costs-title">Piece-Level Costs</h2>
                                    <p class="form-section__subtitle">These fields apply after the base price per piece is calculated.</p>
                                </div>

                                <div class="form-section__fields">
                                    <div class="form-group">
                                        <label class="form-label" for="ups">Ups</label>
                                        <input class="form-input" id="ups" name="ups" type="number" step="1" min="1" value="{{ $fieldValue('ups') }}">
                                        @error('ups')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="window_labor_cost">Window &amp; Labor Cost</label>
                                        <input class="form-input" id="window_labor_cost" name="window_labor_cost" type="number" step="any" min="0" value="{{ $fieldValue('window_labor_cost') }}">
                                        @error('window_labor_cost')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-check">
                                        <input type="hidden" name="needs_lace_cost" value="0">
                                        <input class="form-check-input" id="needs_lace_cost" name="needs_lace_cost" type="checkbox" value="1" @checked($needsLaceCost)>
                                        <label class="form-check-label" for="needs_lace_cost">Apply Lace</label>
                                        @error('needs_lace_cost')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror
                                    </div>
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
                                <span class="result-label">Selected Paper Rate</span>
                                <span class="result-value">{{ $formatMoney($paperPricingResult['selected_paper_rate'] ?? null) }}</span>
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

                                @if (($input['foiling_cost'] ?? null) !== null)
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
                                    <span class="result-label">Punching Rate</span>
                                    <span class="result-value">{{ $formatMoney($punchingRateResult['rate'] ?? null) }}</span>
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
                                    <span class="result-label">Lace Cost</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['lace_cost'] ?? null) }}</span>
                                </div>

                                <div class="result-item">
                                    <span class="result-label">Optional Piece Costs Total</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['optional_piece_costs_total'] ?? null) }}</span>
                                </div>

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

                                <div class="result-item">
                                    <span class="result-label">Compulsory Piece Costs Total</span>
                                    <span class="result-value">{{ $formatMoney($piecePricingResult['compulsory_piece_costs_total'] ?? null) }}</span>
                                </div>

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

                                <div class="result-item result-item--total">
                                    <span class="result-label">Total Cost With Margin</span>
                                    <span class="result-value">{{ $formatMoney($marginResult['total_cost_with_margin'] ?? null) }}</span>
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

        const needsPunching = document.querySelector('[data-needs-punching]');
        const punchingDetails = document.querySelectorAll('[data-punching-details]');
        const needsLamination = document.querySelector('[data-needs-lamination]');
        const laminationMode = document.querySelector('[data-lamination-mode]');
        const laminationDetails = document.querySelectorAll('[data-lamination-details]');
        const laminationBackSide = document.querySelectorAll('[data-lamination-back-side]');
        const needsSpotUv = document.querySelector('[data-needs-spot-uv]');
        const spotUvDetails = document.querySelectorAll('[data-spot-uv-details]');
        const punchCostJobType = document.querySelector('[data-punch-cost-job-type]');
        const newJobPunchCost = document.querySelectorAll('[data-new-job-punch-cost]');

        const syncPunching = () => {
            toggle(punchingDetails, needsPunching?.value === '1');
        };

        const syncLamination = () => {
            const isNeeded = needsLamination?.value === '1';
            toggle(laminationDetails, isNeeded);
            toggle(laminationBackSide, isNeeded && laminationMode?.value === 'both_sides');
        };

        const syncSpotUv = () => {
            toggle(spotUvDetails, needsSpotUv?.value === '1');
        };

        const syncNewJobPunchCost = () => {
            toggle(newJobPunchCost, punchCostJobType?.value === 'new_job');
        };

        needsPunching?.addEventListener('change', syncPunching);
        needsLamination?.addEventListener('change', syncLamination);
        laminationMode?.addEventListener('change', syncLamination);
        needsSpotUv?.addEventListener('change', syncSpotUv);
        punchCostJobType?.addEventListener('change', syncNewJobPunchCost);

        syncPunching();
        syncLamination();
        syncSpotUv();
        syncNewJobPunchCost();
    })();
</script>
</body>
</html>
