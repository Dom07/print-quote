# Project Rules

These rules capture the working conventions established while building the print quote / estimate app.

## General Approach

- Build the app sequentially in small vertical slices.
- Keep each step simple, focused, and easy to replace later.
- Avoid premature full-system abstractions until the workflow is confirmed.
- Do not create or modify database tables unless the current task explicitly requires it.
- Do not write estimate records unless the current task explicitly requires persistence.
- Prefer readable, boring code over clever code.

## Laravel Structure

- Keep controllers thin.
- Put validation in Form Request classes.
- Put calculation logic in service classes.
- Do not put formulas directly in controllers or Blade views.
- Use services for estimate-related calculations as much as possible.
- Keep each service focused on one responsibility.
- Keep temporary sandbox code easy to delete or replace.

## Estimate Sandbox

- The sandbox is temporary and must not save estimates, inputs, totals, or cost components.
- The sandbox may show debug/tallying values while calculation flows are being confirmed.
- Keep KG debug values separate from pricing results.
- Do not use Filament for the sandbox or custom estimate screens.
- Do not change confirmed formulas unless the task explicitly asks for it.
- Employee-facing dropdowns should show choices only, not hidden rates or pricing values.

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
  - Avoid broad refactors unless the current task explicitly asks for them.
