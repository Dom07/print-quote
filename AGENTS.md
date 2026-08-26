# Project Rules

These rules capture the working conventions established while building the print quote / estimate app.

## General Approach

- Build the app sequentially in small vertical slices.
- Keep each step simple, focused, and easy to replace later.
- Avoid premature full-system abstractions until the workflow is confirmed.
- Do not create or modify database tables unless the current task explicitly requires it.
- Do not write estimate records unless the current task explicitly requires persistence.
- Prefer readable, boring code over clever code.
- Avoid broad refactors unless the current task explicitly asks for them.

## Laravel Structure

- Keep controllers thin.
- Put validation in Form Request classes.
- Put calculation logic in service classes.
- Do not put formulas directly in controllers or Blade views.
- Use services for estimate-related calculations as much as possible.
- Keep each service focused on one responsibility.
- Keep temporary sandbox code easy to delete or replace.

## Estimate Sandbox

- The existing sandbox/calculation flow must not save estimates, inputs, totals, or cost components unless a task explicitly wires persistence into it.
- Persistence services and supporting infrastructure may be created when explicitly requested, but creating that infrastructure does not mean the sandbox/form should start writing records.
- The sandbox may show debug/tallying values while calculation flows are being confirmed.
- Keep KG debug values separate from pricing results.
- Do not use Filament for the sandbox or custom estimate screens.
- Do not change confirmed formulas unless the task explicitly asks for it.
- Employee-facing dropdowns should show choices only, not hidden rates or pricing values.

## Estimate Persistence

- Estimate persistence is being introduced incrementally alongside the existing live calculator.
- The current live workflow is still calculation-only: users enter inputs, the app reads current pricing/configuration, services calculate the estimate, results display, and no estimate is saved.
- Do not wire persistence into the live estimate form until the task explicitly requires it.
- Do not alter confirmed calculation behavior while implementing persistence unless explicitly requested.
- Existing persistence tables are `estimates`, `estimate_inputs`, `estimate_cost_components`, and `estimate_totals`.
- Do not create replacement tables or alternate persistence schemas without explicit instruction.
- The intended `estimates` persistence structure includes `quote_number`, `status`, `title`, `notes`, `quoted_at`, and timestamps.
- Customer and authenticated-user ownership are not implemented yet. Do not assume or introduce `customer_id`, `created_by`, user ownership, or authentication requirements unless a future task explicitly adds them.
- Future customer/user relationships should be added through new additive migrations, initially nullable where necessary so existing saved estimates remain valid.
- Live pricing configuration is stored in `pricing_categories`, `pricing_items`, and `pricing_rules`.
- Saved estimate cost components must snapshot the actual historical values used for that estimate, including concepts such as name, unit, rate, original rate, rate type, override state, calculation note, and override note.
- Do not reconstruct an old quote by looking up current pricing rates.
- Do not add `pricing_item_id` or `pricing_rule_id` dependencies to saved cost components unless a future explicit requirement changes this architecture.
- `estimate_inputs` stores the inputs needed to reconstruct/reopen an estimate, including structured fields plus `raw_inputs` JSON. Do not arbitrarily expand or redesign this structure unless asked.
- `estimate_totals` stores calculated totals associated with the saved estimate. Keep calculation formulas in calculation services/code, not persistence models or database tables.
- Established estimate model relationships are `Estimate::input()`, `Estimate::costComponents()`, `Estimate::totals()`, and inverse `estimate()` relationships on `EstimateInput`, `EstimateCostComponent`, and `EstimateTotal`.
- Do not add customer/user/pricing relationships to estimate persistence models unless explicitly requested.
- The project model mass-assignment convention is `protected $guarded = [];`; preserve it rather than introducing a competing approach.

## Quote Numbers

- Quote-number generation lives in `App\Services\Estimates\QuoteNumberGenerator`.
- Quote format is `Q-000001`: prefix `Q-`, sequential numeric portion, zero-padded to 6 digits.
- Quote numbers are generated only when a new estimate is created.
- Editing an existing estimate must preserve its existing quote number.
- Do not generate quote numbers during ordinary calculation/display.
- Do not derive the displayed quote number directly from the estimate database ID.
- The generator does not itself create estimates.
- The generator uses database locking intended to be used inside the future estimate-create transaction.
- The database unique constraint remains final uniqueness protection.
- Do not duplicate quote-number generation logic elsewhere.

## Production Safety

- The application is live and regularly used.
- Never use `migrate:fresh`, `migrate:refresh`, destructive resets, or reseeding against the live application as part of normal feature work.
- Do not edit previously-run migrations to alter production schema.
- Make production schema changes with new additive migrations.
- Preserve existing data.
- Do not perform broad database changes unless explicitly required.

## Current Estimate Persistence Checkpoint

- Complete: estimate persistence schema alignment.
- Complete: Eloquent persistence models and relationships.
- Complete: quote-number generator with focused tests.
- Next planned persistence step: create a focused service for saving a brand-new estimate from already-calculated data.
- The upcoming create/save flow is expected to happen inside one database transaction: generate quote number, create estimate, save estimate inputs, save historical cost component snapshots, and save estimate totals.
- The next step should initially cover new estimate creation only.
- Do not assume the next step includes editing existing estimates, reopening estimates, controllers, routes, UI save buttons, estimate listing, customer integration, authentication, or full status-transition workflows.

## Naming

- Use neutral naming when the business unit is not confirmed.
- Avoid public-facing names such as `paper_rate_per_kg` unless the unit has been confirmed.
- Prefer names like:
  - `selected_paper_rate`
  - `interest_percentage`
  - `updated_paper_rate`
  - `price_per_sheet`
- UI labels should not expose internal assumptions such as `/ kg` or `per kg` unless confirmed.

## CSS And UI

- Keep CSS centralized and consistent.
- Use `resources/css/app.css` as the main Vite CSS entry file.
- Keep global theme tokens in `resources/css/theme.css`.
- Keep reusable classes in `resources/css/components.css`.
- Keep page-only tweaks in `resources/css/pages/*.css`.
- Do not add large inline `<style>` blocks to Blade views.
- Reuse existing CSS component classes before creating new page-specific styles.
- Keep custom HTML/CSS clean, flat, and professional.

## Views

- Keep Blade views simple and mostly presentational.
- Avoid business logic in views.
- Small formatting helpers in Blade are acceptable for temporary sandbox screens.
- Employee-facing form controls should avoid exposing internal rates or calculation details unless specifically required.

## Testing

- Add unit tests for calculation services.
- Add feature tests for user-facing sandbox behavior.
- Keep tests aligned with behavior without weakening assertions.
- Run the relevant test suite after changes.
- Use formatting checks such as Pint after PHP changes.

## Communication

- Always report a concise summary after completing changes.
- Include:
  - files created or changed
  - behavior added or updated
  - formulas or services touched
  - tests run and results
  - confirmation of anything intentionally not changed, such as database writes or formulas
