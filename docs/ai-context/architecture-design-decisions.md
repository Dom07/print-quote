# Architecture and Design Decisions

This document records the current architecture and confirmed design decisions for the print quote application.

## Framework and application shape

- Laravel 13 / PHP 8.3 application.
- Pest is used for feature and unit tests.
- Vite is used for frontend assets.
- The main custom workflow is `/estimate-sandbox`, implemented by:
  - `routes/web.php`
  - `App\Http\Controllers\EstimateSandboxController`
  - `App\Http\Requests\EstimateSandboxRequest`
  - `resources/views/estimate-sandbox.blade.php`
  - estimate services under `app/Services/Estimates`

## Core architectural direction

The project is intentionally built in small vertical slices.

Current architecture favors:

- thin controllers,
- Form Request validation,
- focused calculation services,
- simple Eloquent models,
- Blade views that are mostly presentational,
- CSS centralized in Vite CSS files.

The goal is to keep the calculation workflow understandable and replaceable while business rules are still being confirmed.

## Live calculation flow

The sandbox currently performs this sequence:

1. Validate request data in `EstimateSandboxRequest`.
2. Normalize dimensions to inches with `MeasurementConverter`.
3. Calculate paper weight/KG debug values with `PaperWeightCalculator`.
4. Resolve selected live pricing items from `pricing_items`.
5. Calculate paper pricing with `PaperPricingCalculator`.
6. Resolve optional sheet-level add-ons:
   - punching via `PunchingRateResolver`,
   - lamination via `LaminationCalculator`,
   - spot UV / raised UV via `SpotUvCalculator`,
   - drip off via `DripOffCalculator`,
   - foiling as a manual per-sheet value.
7. Sum sheet-level costs with `TotalPricePerSheetCalculator`.
8. Resolve optional/required piece-level costs:
   - pasting via `PastingRateResolver`,
   - lace/designing via `PieceLevelAddonResolver`,
   - repeat-job punch cost via `RequiredPieceCostResolver`,
   - manual new-job punch cost and expenses from the form.
9. Convert sheet price into piece price with `PiecePricingCalculator`.
10. Apply gross margin slab with `MarginCalculator`.
11. Render all results back to the sandbox view.

This flow does not save anything to estimate persistence tables.

## Calculation services and formulas

All formulas should live in service classes under `app/Services/Estimates`.

### Rounding

`EstimateRounder` centralizes rounding:

- money: `round($value, 2, PHP_ROUND_HALF_UP)`
- quantity: `round($value, 2, PHP_ROUND_HALF_UP)`

### Measurement conversion

`MeasurementConverter` accepts dimensions in inches or centimeters.

- `in`: unchanged.
- `cm`: divided by `2.54`.

Dimension-based calculations use inches after normalization.

### Paper weight

`PaperWeightCalculator` calculates approximate KG for a sheet count:

```text
value  = length * width * gsm
value1 = value / 3100
value2 = value1 / 5
value3 = 100 / value2
kg     = sheetCount / value3
```

The returned quantity is rounded to 2 decimals.

The sandbox calculates KG separately for:

- `no_of_sheets`,
- `no_of_sheets_with_wastage`,
- `no_of_sheets_to_process`.

### Paper pricing

`PaperPricingCalculator` calculates:

```text
updated_paper_rate = selected_paper_rate + ((interest_percentage / 100) * selected_paper_rate)
order_paper_cost   = updated_paper_rate * kgs_of_order
price_per_sheet    = order_paper_cost / no_of_sheets
```

Important details:

- `no_of_sheets` must be greater than zero.
- `selected_paper_rate` may be the configured pricing item rate or a user override.
- Override metadata is shown separately from the pricing formula.

### Punching

`PunchingRateResolver` uses the selected punching pricing item.

- It first looks for the first active matching pricing rule ordered by `sort_order`, then `id`.
- If no rule matches and the item has a direct rate, it uses the item rate.
- `minimum_flat` rules are converted into an effective per-sheet rate by dividing the configured flat amount by quantity.
- Per-sheet rules use the configured rate directly.

Current seeded standard punching rules:

- below/equal 999 sheets: minimum flat 1000 job charge,
- greater than 999 and up to 2000: 1.00 per sheet,
- greater than 2000: 0.60 per sheet.

Complicated punching currently uses direct item rate `0.70` per sheet.

### Lamination

`LaminationCalculator` supports:

- `front_only`,
- `both_sides`.

For each selected side:

```text
value = (length * width * coefficient) / 100
```

The coefficient is resolved from the first active matching pricing rule, falling back to item rate if present.

The combined lamination value is the sum of front and back side values.

The sandbox currently allows BOPP and Matte lamination options, not Gloss, even though Gloss exists in seed data.

### Spot UV / Raised UV

`SpotUvCalculator` resolves a per-sheet value from rules or item rate.

- Quantity must be greater than zero when selected.
- `minimum_flat` rules divide the minimum/job amount by quantity.
- `per_sheet` rules use the configured rate directly.

Current seeded rules:

- Spot UV: 1250 minimum up to/equal 1000 sheets; 1.25 per sheet above 1000.
- Raised UV: 2800 minimum up to/equal 1000 sheets; 2.80 per sheet above 1000.

### Drip off

`DripOffCalculator` uses active, non-selectable drip-off pricing items plus a selected setup/add-on item.

Formula:

```text
base_rate_per_sheet = (length * width * drip_off_coefficient) / 100
base_cost           = base_rate_per_sheet * quantity
adjusted_base_cost  = max(minimum_cost, base_cost)
adjusted_base_rate  = base_rate_per_sheet when base_cost >= minimum_cost, otherwise minimum_cost / quantity
flat_add_on_rate    = selected_flat_add_on_amount / quantity
final_rate_per_sheet = adjusted_base_rate + flat_add_on_rate
```

Current configured internal items:

- `drip-off-coefficient`: 0.75
- `drip-off-minimum-charge`: 2500

Current selectable add-on/setup options:

- New Job with Pasting: 1600 flat
- Repeat Job with Pasting: 300 flat

### Pasting

`PastingRateResolver` maps sandbox choices to active `pasting` pricing items:

- `four_sides` without checking: `four-sides-pasting`
- `four_sides` with checking: `four-sides-pasting-with-checking`
- `eight_sides` without checking: `eight-sides-pasting`
- `eight_sides` with checking: `eight-sides-pasting-with-checking`

The resolved rate is a direct per-piece cost.

### Total price per sheet

`TotalPricePerSheetCalculator` sums these sheet-level components:

- paper price per sheet,
- printing cost,
- ink cost,
- foiling cost,
- punching rate,
- lamination value,
- spot UV value,
- drip-off rate.

Null optional values are treated as zero.

### Piece pricing

`PiecePricingCalculator` calculates:

```text
number_of_pieces       = no_of_sheets * ups
base_price_per_piece   = total_price_per_sheet / ups
optional_piece_costs   = window_labor_cost + pasting_cost + lace_cost
repeat_job_punch_cost  = repeat_job_flat_amount / number_of_pieces
new_job_punch_cost     = manual direct per-piece amount
designing_cost         = designing_flat_amount / number_of_pieces
required_piece_costs   = punch_cost + designing_cost + expenses
total_piece_cost       = base_price_per_piece + optional_piece_costs + required_piece_costs
total_cost             = total_piece_cost * number_of_pieces
```

Important details:

- `ups` must be at least 1.
- Pasting is already per piece and is not divided by ups.
- Lace is a direct per-piece add-on when selected.
- Designing is a flat job amount divided by total number of pieces when selected.
- Repeat-job punch cost is a flat job amount divided by total number of pieces.
- New-job punch cost is a direct manual per-piece amount.
- Expenses are entered as a direct per-piece amount.

### Margin

`MarginCalculator` selects an active `MarginSlab` by total cost and applies gross margin:

```text
margin_rate            = margin_percentage / 100
remaining_rate         = 1 - margin_rate
total_cost_with_margin = total_cost / remaining_rate
margin_amount          = total_cost_with_margin - total_cost
selling_price          = total_cost_with_margin / number_of_pieces
```

Important details:

- Margin must be >= 0 and < 100.
- Number of pieces must be greater than zero.
- This is gross margin math, not simple markup.

## Validation decisions

`EstimateSandboxRequest` validates the sandbox input.

Notable rules:

- length/width/GSM must be positive numbers.
- measurement unit must be `in` or `cm`.
- `no_of_sheets` and `ups` must be positive integers.
- `no_of_sheets_with_wastage` and `no_of_sheets_to_process` may be zero, but `no_of_sheets_to_process` must be greater than zero when lamination, spot UV, or drip off is selected.
- pricing selections must exist, be active/selectable, and belong to the expected active category.
- foiling cost is required only when foiling is selected.
- lamination mode/front/back selections are required only when relevant.
- drip-off setup is required only when drip off is selected.
- new-job punch cost is required only when job type is `new_job`.

## Persistence architecture decision

Estimate persistence has been introduced incrementally, but is not used by the sandbox yet.

Confirmed persistence design:

- `estimates` is the parent quote record.
- `estimate_inputs` stores reconstruct/reopen inputs, including raw JSON.
- `estimate_cost_components` stores historical cost snapshots.
- `estimate_totals` stores final calculated totals.

Important decision:

- Saved estimates must not be reconstructed from current pricing configuration. Current pricing can change after a quote is saved. Historical estimate records must contain the actual rates and component metadata used at quote time.

The expected future create flow is one database transaction:

1. generate quote number,
2. create estimate,
3. save estimate inputs,
4. save historical cost component snapshots,
5. save totals.

That service has not yet been wired into the live sandbox unless a future task explicitly does so.

## UI and CSS decisions

- Do not use Filament for the sandbox or custom estimate screens.
- Keep UI labels employee-friendly and avoid exposing hidden rates in dropdowns.
- CSS is centralized:
  - `resources/css/app.css` imports/serves app CSS,
  - `resources/css/theme.css` contains theme tokens,
  - `resources/css/components.css` contains reusable component styles,
  - `resources/css/pages/estimate-sandbox.css` contains page-specific sandbox styles.

## Testing strategy

- Unit tests cover focused calculators/resolvers.
- Feature tests cover sandbox rendering, validation, calculation display, and UI behavior.
- Persistence model tests cover relationships/casts and confirm cost components do not expose live pricing relationships.
- Quote-number generator tests cover sequence behavior and confirm generation does not create or modify estimates.
