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
        $paperOptions = $paperOptions ?? collect();
        $interestOptions = $interestOptions ?? collect();

        $fieldValue = fn (string $field) => old($field, $input[$field] ?? '');
        $formatKg = fn (?float $value) => $value === null ? '-' : number_format($value, 4);
        $formatMoney = fn (?float $value) => $value === null ? '-' : '₹'.number_format($value, 4);
        $formatPercent = fn (?float $value) => $value === null ? '-' : number_format($value, 4).'%';
        $formatDropdownPercent = fn (float $value) => rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.').'%';
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
                        </div>

                        <div class="form-actions">
                            <button class="btn btn-primary" type="submit">Calculate KG</button>
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

                        <p class="result-note">Temporary paper weight calculation only. No values are saved.</p>
                    </div>
                </aside>
            </section>
        </div>
    </main>
</body>
</html>
