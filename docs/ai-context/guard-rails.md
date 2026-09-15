# AI Guard Rails

This document captures project-specific working rules for AI-assisted development in this Laravel print quote application.

## Current project posture

- The application is live and regularly used.
- Work in small, sequential vertical slices.
- Prefer simple, readable Laravel code over broad abstractions.
- Avoid broad refactors unless explicitly requested.
- Do not change confirmed formulas unless the task explicitly asks for a formula change.

## Production and database safety

- Never run or recommend destructive database commands for normal feature work:
  - `migrate:fresh`
  - `migrate:refresh`
  - destructive resets
  - destructive reseeding against the live app
- Do not edit migrations that may already have run in production.
- Make schema changes with new additive migrations only.
- Preserve existing data.
- Do not create or alter database tables unless the task explicitly requires it.

## Estimate sandbox boundaries

The `/estimate-sandbox` flow is currently a calculation-only workflow.

- It must not save estimates, estimate inputs, totals, or cost components unless a task explicitly wires persistence into it.
- It may read current pricing configuration from `pricing_categories`, `pricing_items`, `pricing_rules`, and `margin_slabs`.
- It may display debug/tallying values while formulas are being confirmed.
- Keep paper kilogram/debug values separate from pricing results.
- Do not use Filament for the sandbox or custom estimate screens.
- Employee-facing dropdowns should show choices only, not hidden rates or pricing internals.

## Estimate persistence boundaries

Persistence exists, but it is intentionally not wired into the live calculator yet.

- Existing persistence tables are:
  - `estimates`
  - `estimate_inputs`
  - `estimate_cost_components`
  - `estimate_totals`
- Do not introduce replacement persistence tables or alternate schemas.
- Do not add customer/user ownership, `customer_id`, `created_by`, authentication requirements, or user relationships unless explicitly requested.
- Saved estimate cost components are historical snapshots. They must not depend on live `pricing_items` or `pricing_rules` for historical reconstruction.
- Do not add `pricing_item_id` or `pricing_rule_id` dependencies to saved cost components unless explicitly requested.
- `estimate_inputs.raw_inputs` is the flexible place for raw/reopen input payloads. Do not arbitrarily redesign the persistence structure.
- Use the existing Eloquent relationship names:
  - `Estimate::input()`
  - `Estimate::costComponents()`
  - `Estimate::totals()`
  - inverse `estimate()` relationships on persistence child models
- Preserve the project mass-assignment convention: `protected $guarded = [];`.

## Quote number rules

- Quote-number generation lives only in `App\Services\Estimates\QuoteNumberGenerator`.
- Format is `Q-000001`: `Q-` prefix plus a six-digit zero-padded sequence.
- Generate quote numbers only when creating a new estimate.
- Editing an existing estimate must preserve its existing quote number.
- Do not generate quote numbers during ordinary calculation/display.
- Do not derive displayed quote numbers from the database ID.
- Use the generator inside a future create transaction; it uses `lockForUpdate()` and the database unique constraint remains final protection.

## Laravel structure rules

- Keep controllers thin.
- Put validation in Form Request classes.
- Put calculation logic in focused service classes under `app/Services/Estimates`.
- Do not put formulas in controllers or Blade views.
- Views should remain mostly presentational, with only light formatting.

## CSS and UI rules

- Use `resources/css/app.css` as the Vite CSS entry file.
- Keep global theme tokens in `resources/css/theme.css`.
- Keep reusable classes in `resources/css/components.css`.
- Keep page-only tweaks in `resources/css/pages/*.css`.
- Do not add large inline `<style>` blocks to Blade views.
- Reuse existing component classes before adding page-specific styles.

## Testing rules

- Add unit tests for calculation services.
- Add feature tests for user-facing sandbox behavior.
- Run relevant tests after changes.
- Run Pint after PHP changes when practical.

## Communication checklist after changes

Report concisely:

- files created or changed
- behavior added or updated
- formulas/services touched
- tests run and results
- anything intentionally not changed, especially database writes or formulas
