# Domain Models

This document describes the current business/domain model of the print quote application as implemented in code.

## Domain overview

The application calculates print estimate pricing from:

1. product dimensions and sheet quantities,
2. paper selection and interest slab,
3. manual sheet-level costs such as printing, ink, and optional foiling,
4. optional sheet-level processes such as punching, lamination, spot UV, and drip off,
5. piece-level inputs and add-ons such as ups, window/labor, pasting, lace, designing, punch cost, and expenses,
6. margin slabs used to calculate the final selling price.

The active user-facing workflow is `/estimate-sandbox`. It calculates and displays results, but does **not** persist estimates.

## Live pricing configuration models

### `PricingCategory`

Table: `pricing_categories`

Purpose: groups pricing items by business area.

Important fields:

- `name`
- `slug` - unique category identifier used by services and validation
- `description`
- `sort_order`
- `is_active`

Relationships:

- `PricingCategory::pricingItems()` has many `PricingItem` records.

Current seeded category slugs include:

- `paper`
- `interest-slabs`
- `printing`
- `ink`
- `punching`
- `lamination`
- `spot-uv`
- `drip-off`
- `foiling`
- `pasting`
- `add-on-costs`
- `required-costs`
- `operational-expenses`
- `transport`

### `PricingItem`

Table: `pricing_items`

Purpose: stores configurable current rates and selectable options.

Important fields:

- `pricing_category_id`
- `name`
- `slug` - unique within a pricing category
- `description`
- `unit`
- `rate`
- `rate_type`
- `is_selectable`
- `sort_order`
- `is_active`

Relationships:

- `PricingItem::pricingCategory()` belongs to `PricingCategory`.
- `PricingItem::pricingRules()` has many `PricingRule` records.

Important behavior:

- Employee-facing sandbox dropdowns use active/selectable items from active categories.
- Some items are not selectable because they are internal rates used by resolvers, for example drip-off coefficient/minimum charge and pasting lookup items.
- Current pricing items are live configuration. They are not the historical source of truth for saved estimates.

### `PricingRule`

Table: `pricing_rules`

Purpose: stores tiered rates for a pricing item.

Important fields:

- `pricing_item_id`
- `name`
- `applies_to`
- `min_value`
- `max_value`
- `rate`
- `unit`
- `rate_type`
- `sort_order`
- `is_active`

Relationships:

- `PricingRule::pricingItem()` belongs to `PricingItem`.

Rule matching convention used by current calculators:

- `min = null`, `max != null`: match when value is `<= max`.
- `min != null`, `max = null`: match when value is `> min`.
- `min != null`, `max != null`: match when value is `> min && <= max`.
- rules are evaluated ordered by `sort_order`, then `id`.

Currently used for:

- standard punching quantity tiers,
- lamination coefficients by processed sheet quantity,
- spot UV / raised UV minimum-or-per-sheet rates.

### `MarginSlab`

Table: `margin_slabs`

Purpose: maps calculated total cost to a gross margin percentage.

Important fields:

- `name`
- `min_amount`
- `max_amount`
- `margin_percentage`
- `sort_order`
- `is_active`

Matching convention:

- `min = null`, `max != null`: total cost `<= max`.
- `min != null`, `max != null`: total cost `> min && <= max`.
- `min != null`, `max = null`: total cost `> min`.

Current seeded slabs:

- Below 20k: 17%
- 20k to 50k: 15%
- 50k to 1.5 Lac: 13%
- 1.5 Lac to 3.5 Lac: 12%
- Above 3.5 Lac: 10%

## Estimate persistence models

Persistence tables exist for future saved quotes. They are intentionally separate from the live calculation-only sandbox.

### `Estimate`

Table: `estimates`

Purpose: parent record for a saved estimate/quote.

Current intended fields:

- `quote_number`
- `status`
- `title`
- `notes`
- `quoted_at`
- timestamps

Relationships:

- `Estimate::input()` has one `EstimateInput`.
- `Estimate::costComponents()` has many `EstimateCostComponent` records.
- `Estimate::totals()` has one `EstimateTotal`.

Status enum: `EstimateStatus`

- `draft`
- `calculated`
- `approved`
- `rejected`

Important boundary:

- Customer/user ownership is not implemented in the current aligned persistence schema. Do not assume `customer_id`, `created_by`, authentication, or ownership relationships.

### `EstimateInput`

Table: `estimate_inputs`

Purpose: stores the input data needed to reconstruct/reopen a saved estimate.

Important fields:

- `estimate_id` - unique one-to-one link to `estimates`
- `product_type`
- `pieces_per_sheet`
- `production_sheets`
- `wastage_sheets`
- `total_sheets_with_wastage`
- `sheets_used_for_process`
- `raw_inputs` JSON

Relationships:

- `EstimateInput::estimate()` belongs to `Estimate`.

### `EstimateCostComponent`

Table: `estimate_cost_components`

Purpose: stores historical snapshots of cost components used for a saved estimate.

Important fields:

- `estimate_id`
- `name`
- `unit`
- `rate`
- `original_rate`
- `rate_type`
- `is_overridden`
- `calculation_note`
- `override_note`

Relationships:

- `EstimateCostComponent::estimate()` belongs to `Estimate`.

Important boundary:

- These records must snapshot the values used at quote time.
- They intentionally do not expose live pricing relationships.
- Do not reconstruct old quotes by looking up current `pricing_items` or `pricing_rules`.

### `EstimateTotal`

Table: `estimate_totals`

Purpose: stores final calculated total values for a saved estimate.

Important fields:

- `estimate_id` - unique one-to-one link to `estimates`
- `cost_per_sheet`
- `cost_per_piece`
- `subtotal`
- `margin_percentage`
- `margin_amount`
- `grand_total`

Relationships:

- `EstimateTotal::estimate()` belongs to `Estimate`.

Important boundary:

- Formulas live in calculation services, not in this model or table.

## Supporting enums

### `RateType`

Values:

- `per_sheet`
- `per_piece`
- `per_kg`
- `flat`
- `percentage`
- `formula_coefficient`
- `minimum_flat`

Used by pricing items, pricing rules, and saved estimate cost components.

### `PricingRuleAppliesTo`

Values:

- `production_sheets`
- `total_sheets_with_wastage`
- `sheets_used_for_process`
- `pieces`
- `total_value`

Current seed data sets this mostly to `production_sheets`; individual calculators currently pass the relevant quantity/value directly.

## Customer and User

`Customer` and Laravel `User` models/tables exist, but estimate ownership/customer assignment is not currently part of the aligned estimate persistence model. Future customer/user relationships should be additive and nullable unless explicitly specified otherwise.

## Quote numbers

Quote numbers are generated by `App\Services\Estimates\QuoteNumberGenerator`.

Rules:

- format: `Q-000001`
- six-digit zero-padded sequence
- generated only for newly-created estimates
- not generated during sandbox calculation/display
- not derived from database IDs
- existing quote numbers are preserved when editing
