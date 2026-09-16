# Income Tax Declarations — Legacy vs. Next.js Comparison

**Legacy:** `Controller/TaxController.php::Tabs()` / `::setup()` (+ `EmployeeTaxController.php`, `TaxationController.php`), `tabs.ctp`, `setup.ctp`
**Next.js:** `/employees/tax-declarations`, `src/app/api/employees/[id]/tax-declarations/**`, `.../tax-compute/route.ts`, `src/lib/taxation.ts`
**Date:** 2026-09-02, updated 2026-09-14
**Method:** source-code comparison, plus fixes applied this pass. No code under either tree changed between the first two passes (verified via `git log`), so the 2026-09-02 findings held; 2026-09-14 added the `Tabs()` landing screen, the self-service scoping fix, the New-Regime "Details" drill-down fix, Marginal Relief, Compute Projection refresh scope, Tax Deducted/Balance Tax, per-slab Income/Tax breakdown (both regimes), and a Form-16 link — all implemented, no DB schema/data changes throughout.

---

## Headline

The declare / lock / proof-upload workflow plus old-vs-new regime compute was already ported. The
2026-09-02 pass added the **read-only projection worksheet** that makes up the rest of legacy's
`setup()` screen, and surfaced the **statutory deduction ceiling** (`tax_heads.attr1`) legacy
applies. The 2026-09-14 pass looked at the **landing/selection screen** (`Tax/Tabs`) and found the
picker is reworked (not a straight port) and one New-Regime summary line
(**reimbursement cap**) is still missing from the Next.js compute.

## Scope comparison

| Piece | Legacy | Next.js | Status |
|---|---|---|---|
| Per-`tax_heads` / `tax_heads_details` declared value, keyed to the branch's open `fin_year` | `saveemployeetaxheads()` / `savetaxdetail()` | `GET`/`POST /api/employees/[id]/tax-declarations` | **Ported** |
| Admin lock / unlock (per head, and bulk) | `EmployeeTaxTransactions.locked` | `.../tax-declarations/lock` | **Ported** |
| Proof-document upload (MIME allow-list, size cap, blocked once locked, comma-joined multi-file) | `uploadFile()` | `.../tax-declarations/upload` | **Ported** |
| Old vs New regime computation + regime selection | `Calculate()` / `Calculate_new()` / `Choosetax()` via `tax_salary_distribution_fn` / `_new_fn` | `.../tax-compute`, `.../tax-regime` | **Ported** |
| **Deduction ceiling** — effective deduction = `min(attr1, declared)` | `setup()` `$tottax` loop | `cap` per head in `GET`; `cappedDeductionTotal` returned; "max ₹X" hint + over-cap highlight in the UI. Declared values stay stored uncapped (matches legacy). | **Ported this pass** |
| Income-from-other-sources total | `setup()` `$other_sources` | `otherIncomeTotal` in `GET` | **Ported this pass** |
| Month-wise TDS deducted + gross paid (processed/approved payroll only) | `setup()` per-month `emp_salary_slip` queries | `GET .../tax-declarations/worksheet` | **Ported this pass** |
| Projected / actual / taxable salary for the FY | `setup()` `emp_tax_sal_trans` aggregates | `worksheet` route `totals` | **Ported this pass** |
| Exempt-allowance breakdown, old regime (`emp_tax_sal_trans` × `tax_salary_components` × `salary_head_items`) | `setup()` `$taxcomponents` / `setupshow()` "Details" toggle | `worksheet` route `components` | **Ported this pass** |
| Exempt-allowance breakdown, **new regime** (`emp_tax_sal_trans_new` × same joins) — the "Details" button under the New Tax Regime card | `setupshow_new($emp_pkey)` ([TaxController.php:641](../legacy/Controller/TaxController.php#L641)), toggle-loaded fragment | `worksheet` route `componentsNew` (added 2026-09-14); rendered as "Exempt allowances — New regime" table on the page, always visible rather than toggle-gated | **Ported 2026-09-14** — was previously missing entirely; Next.js had no query against `emp_tax_sal_trans_new` at all |
| New-regime slab table for the FY | `setup()` `income_tax_slab` query | `worksheet` route `slabs` | **Ported this pass** |
| HRA exemption sub-worksheet (rent, metro flag) | inside `tax_salary_distribution_fn` + surfaced on `setup.ctp` | shown via the regime-compare card (`hra1/hra2/hra3`), not as a standalone input worksheet | **Reduced** — the calc is the SQL function's job; no separate HRA input form |

## Landing/selection screen (`Tax/Tabs`) — added 2026-09-14

| Piece | Legacy | Next.js | Status |
|---|---|---|---|
| Admin (`user_group==1`): branch dropdown (only branches with an OPEN, current, `vattr1=1` `fin_year`) → AJAX-filtered employee dropdown (`getEmployeesByBranch`) → "View" loads `Tax/setup/<emp_pkey>` into a panel | `Tabs()` + `tabs.ctp` | Single global `EmployeeSearch` (name/ID search across **all** employees, no branch step, no open-fin-year filter) feeding the same declarations panel | **Reworked, not ported** — end result (pick an employee, see their declarations) is equivalent; the branch-scoped, open-fin-year-gated dropdown flow is not replicated |
| Employee self-service (`user_group==2`): `Tabs()` skips the picker entirely and calls `setup($emp_fkey)` for the logged-in employee's own record | `if ($user_group == 2) { $this->setup($emp_fkey); }` (session `emp_fkey`, no ownership check in `setup()` itself — see note) | `TaxDeclarationsPage` now reads `session.user.empFkey` and skips `EmployeeSearch` for non-admins, matching legacy's behavior (fixed 2026-09-14, [page.tsx](../rizo/src/app/(dashboard)/employees/tax-declarations/page.tsx)) | **Ported** |
| No-open-fin-year guard for self-service employees (`render('no_fin_year')`) | `Tabs()`→`setup()` | `noFinYear` flag / empty state in `DeclarationData` | **Ported** (confirmed already in the 09-02 pass) |

## New-Regime "reimbursement" line — checked 2026-09-14, not a gap

Legacy's `setup()` computes `$reimbursemnetded` in PHP — the combined, per-item-capped value of
"Telephone Reimbursement" + "Car maintenance/Petrol expenses" under the *Perquisite Sec 17(2) —
New Regime* tax head — purely to redisplay it as its own line on the New Regime summary card:

```
Total Taxable Income (New) = Taxable Salary + Other Income − Reimbursement(capped) − 75,000 (std. deduction)
```

No equivalent app-code was found in `.../tax-declarations/route.ts`, `.../worksheet/route.ts`,
`.../tax-compute/route.ts`, or `src/lib/taxation.ts` — but checking the actual source of the
numbers resolves this: `tax_salary_distribution_new_fn` (`legacy/schema/mypayrol_trial.sql:23810`),
the DB function both legacy and Next.js call for the New Regime figures, independently computes the
same per-item-capped reimbursement sum in its own `BLOCK5` (capped via `tax_heads_details2`) and
folds it into `vtaxable_income` (line 392) *before* running the slab calculation — i.e. before
either side ever sees the number. `$reimbursemnetded` in the `.ctp` is legacy re-deriving, in PHP,
a value the DB function already applied — solely so it has something to print as a labeled line
item on screen.

**Status: Not a gap.** Next.js gets the correct, already-capped New Regime taxable income via the
DB function's return value. The only thing missing is the standalone "Reimbursement" display line
itself — cosmetic, not a correctness issue, and low priority to add.

## New-Regime "Details" drill-down — fixed 2026-09-14

Screenshots of the live legacy screen showed a "Details" button under the New Tax Regime summary
card (`Tax/setupshow_new/<emp_pkey>`). Checked `TaxController.php` + its view files
(`setupshow.ctp`, `setupshow_new.ctp`): both actions are near-identical toggle-loaded fragments
("Income from salary" — per-component item/availed/upper-limit/taxable + Gross Total), differing
only in source table (`emp_tax_sal_trans` old regime vs. `emp_tax_sal_trans_new` new regime); the
rest of both `.ctp` files (deductions breakdown, a flat-rate tax recompute) is HTML-commented out —
dead code, never rendered.

Next.js's `worksheet` route had a `components` query against `emp_tax_sal_trans` (→ old-regime
`setupshow`, already ported), but **no equivalent query against `emp_tax_sal_trans_new`** — the
new-regime "Details" view had no Next.js counterpart at all. Fixed by adding a second, identically-
shaped query (`componentsNew`) to `worksheet/route.ts` and a matching "Exempt allowances — New
regime" table to the page. Read-only addition — no DB schema or data changes.

## Marginal Relief + Compute Projection refresh — fixed 2026-09-14

Both closed out in [page.tsx](../rizo/src/app/(dashboard)/employees/tax-declarations/page.tsx):

- **Marginal Relief**: `emp_tax_sal_trans_sum_new.marginal_relief` was already coming back from
  `getTaxSummary()`'s `SELECT *`, just not declared/rendered client-side. Added `marginal_relief` to
  `TaxSummaryRow` and a "Marginal relief" line under the New Regime card, with an "(NA)" suffix
  matching legacy's `total_taxable_income_new` band check (₹12,00,001–₹12,75,000).
- **Compute Projection refresh scope**: `compute`'s mutation now invalidates
  `['employees', empId, 'tax-declarations']` on success — React Query's default prefix matching
  means this also invalidates the `worksheet` query (key `[..., 'tax-declarations', 'worksheet']`),
  so declared values and the worksheet both refresh after computing, closer to legacy's full-page
  reload after "Process" (still a targeted refetch rather than legacy's literal whole-screen reload,
  but the same data ends up current).

## Tax Deducted / Balance Tax + per-slab Income/Tax breakdown — fixed 2026-09-14

Traced from a side-by-side screenshot comparison. Both closed out in
[page.tsx](../rizo/src/app/(dashboard)/employees/tax-declarations/page.tsx), no DB changes:

- **Tax Deducted / Balance Tax**: legacy computes these client-side (`setup.ctp` ~1562-1580) as
  `sum(TDS deducted this FY)` vs. `(tax_yearly + surcharge + cess) − TDS deducted`, gated by
  legacy's **own** hardcoded rebate-threshold re-check (taxable income ≤ ₹5,00,000 old /
  ≤ ₹7,00,000 new → force both to 0) — a separate check from whatever rebate the DB function itself
  applied to `tax_yearly`. Per explicit instruction, replicated legacy's logic exactly, thresholds
  included, rather than trusting the DB's own rebate outcome — added as a `taxSettlement()` helper
  reusing `worksheet.monthly` (TDS-so-far, same source both regimes use) and rendered under each
  regime card.
- **Per-slab Income/Tax breakdown, both regimes**: turned out to be pure display, not
  re-derivation — legacy's "Income Tax Slabs" table (`setup.ctp` ~400-477 new, ~898-958 old) just
  zips the ordered slab schedule against already-computed `first_portion`…`seventh_portion` /
  `..._tax` columns on the summary row (confirmed in `emp_tax_sal_trans_sum(_new)` schema, already
  returned by `getTaxSummary()`'s `SELECT *`). Added Income/Tax columns to the existing New-regime
  slabs table (zipped by index against `compute.data.summary.new`) and a new Old-regime slabs table
  — **hardcoded 4 bands** (₹0-2.5L 0% / ₹2.5-5L 5% / ₹5-10L 20% / ₹10L+ 30%) matching legacy exactly,
  since legacy's old-regime table isn't DB-sourced either (`income_tax_slab` is only ever queried
  for `regime = 'NEW'`).
- **Form-16 link**: added a "Form 16" button next to Lock all/Unlock all, linking to the existing
  `/taxation/form16` page. Note: that page doesn't yet accept a query param to pre-select the
  employee, so this is a navigation shortcut only, not a scoped deep-link — flag if you want that
  page extended to accept `?empId=`.

## "Total" row + projected-month fallback — fixed 2026-09-14

Found via a further field-by-field screenshot audit after the pass above. Both closed out in
[page.tsx](../rizo/src/app/(dashboard)/employees/tax-declarations/page.tsx) / [worksheet/route.ts](../rizo/src/app/api/employees/[id]/tax-declarations/worksheet/route.ts), no DB changes — and, per instruction, laid out to mirror legacy's structure/labels/order so the two can be checked side by side directly:

- **"Total" row**: legacy's Tax Details box has two distinct tax figures — "Total Tax" (`tax_yearly`
  alone) and a separate "**Total**" (`tax_yearly + surcharge + cess`, zeroed by the same rebate
  threshold as Tax Deducted/Balance Tax). Next.js only ever showed the first. `taxSettlement()` now
  also returns `total`; the regime card's Tax Details block is reordered to match legacy's exact
  row order — Total Tax, Cess, Surcharge, Rebate, **Total**, Tax Deducted, Balance Tax, Monthly Tax
  (legacy puts Cess before Surcharge; Next.js previously had them swapped).
- **Per-regime "Salary for the Year" table with projected-month fallback**: legacy shows this table
  inside *each* regime card (setup.ctp ~698-812 new, ~1160-1268 old) — for a month with no
  processed/approved payroll yet, it doesn't show 0, it falls back to that regime's `tax_monthly_proj`
  shown in **red**, vs. black for months with real deducted TDS. Next.js's worksheet route previously
  collapsed "no row for this month" and "row summed to 0" into the same `tds: 0` — added an `actual:
  boolean` flag per month (`byMonth.has(m)`) so the frontend can tell them apart. Added a
  "Salary for the Year" table inside each regime card (Month / Salary / Tax), red for `!actual`
  months (using that regime's `tax_monthly_proj`), matching legacy's own per-regime duplication of
  this table rather than a single shared one.

## Still open

- **Self-service ownership check**: `setup()`/the `GET .../tax-declarations` route both take a bare
  `emp_pkey`/`id` with no check against the caller's session — true in legacy too, so not a
  regression, but worth tightening in Next.js if desired since it's no longer purely mirroring
  legacy behavior once the picker was scoped.

## Notes

- The worksheet route is entirely read-only and guarded per-block (`safe()` / `firstRow()`), so a
  tenant missing an optional table (`emp_tax_sal_trans`, `income_tax_slab`, …) still returns the
  rest instead of 500-ing.
- `hasPayroll = false` drives an explicit "No processed payroll yet for this employee" empty state —
  common in dev data.
- Curl-verified against real GRTL employee 104 (FY2026): month-wise gross populated from
  `emp_salary_slip`, slab table returned, `cap = 60000` surfaced on "Medical Insurance : Sec 80D".
