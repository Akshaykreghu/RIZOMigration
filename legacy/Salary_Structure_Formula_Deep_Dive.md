# Salary Structure — Formula Engine & Calculation-Type Deep Dive

**Scope:** a field-by-field, type-by-type deep dive into Next.js's 7 salary component calculation types and the "Preview Breakup" calculator, testing whether it computes results consistent with legacy. This extends `Salary_Structure_Test_Cases.md` / `..._Legacy_vs_NextJS_Comparison.md` / `..._Dev_Fix_List.md` (SAL-001 through SAL-010) rather than replacing them.

**App:** RIZO — HR & Payroll (Next.js), `http://localhost:3000/setup/salary-structure`
**Date:** 2026-08-08
**Method:** Live inspection of three real structures — a purpose-built 7-type test structure (id 66, "QA Deep Dive Structure"), and two pre-existing/migrated structures with real data ("BDH-With ESI and PF-13751", id 35; "basic only", id 59) — including two live Preview Breakup runs on structure 59 at different Monthly Gross values (₹10,000 and ₹20,000) to isolate what's actually being computed versus echoed.

---

## Headline answer

**The Preview Breakup calculator does not reproduce legacy's payroll math, and for most existing structures it doesn't compute most of what's actually in them.** Two concrete, reproducible mechanisms cause this: (1) most components on migrated structures carry either empty formula fields or legacy-syntax formula text (e.g. `"Monthly Gross Salary * . 40"`) that the new engine cannot parse, and instead of erroring or showing ₹0, it silently omits those components from the breakdown entirely; (2) the "Net" figure is a plain arithmetic sum of whatever *is* shown, with no distinction between employee take-home items and employer-side contributions — so employer contributions (EPF-Employer, ESI-Employer, WWF-Employer) get added into "Net" as if they were part of the employee's pay, inflating Net above Gross whenever employer and employee contribution amounts aren't equal. Both mechanisms were reproduced on two independent real structures with matching arithmetic. Separately, SAL-001 (new structures lose all components on save) means this entire calculator is currently untestable on anything created from today going forward — everything below applies only to structures that already existed before that bug's window.

**Update (Part 4):** a direct, live test of legacy's own Formula builder — building a fresh formula from scratch rather than reading one that already existed — found that legacy is not a clean, fully-working reference either. Legacy's current formula-builder tool has its own reproducible bug (it inserts a bare digit instead of a working reference to Monthly Gross Salary when a new formula is composed), and its edit-save flow silently failed to persist a subsequent change with no error shown. Pre-existing/older legacy structures still compute correctly and remain a valid ground truth for Defects A–C below; legacy's present-day formula-entry UI does not.

**Update (Part 5):** the same apples-to-apples test was then run directly against Next.js, using its own documented token syntax rather than reading old data. The result reverses part of the original headline: **when given a correctly-formatted formula, Next.js's calculation engine is exactly correct** — `monthsal * . 70` against a Gross of 10,000 returned ₹7,000, precisely right, and closer to correct than legacy's own current formula builder managed on the identical test (₹3.50). Two new defects were also found: components with an empty formula are dropped silently at *save* time, not just hidden from the preview; and once any component is added to a structure, the item picker permanently locks to that component's category, making it structurally impossible to add components from more than one category (e.g. an earning plus an employer contribution) to the same structure. See Part 5 for full detail — this second issue is now the more urgent blocker, since it prevents building any complete, real-world salary structure at all, and also means Defect C (Net wrongly including employer contributions) can no longer even be reproduced from a freshly-built structure.

**Update (Part 6, 2026-08-10):** following developer changes to the Next.js Salary Structure module, all three P0 issues from Part 5 were re-tested live. **All three are now fixed.** The item picker no longer locks to a single category — the New/Edit form now shows six category sections simultaneously, each with its own "Add" button, and a structure spanning three categories (an earning, an employer contribution, and a statutory deduction) was built and saved successfully (SAL-022, fixed). With that unblocked, Defect C could finally be re-tested from fresh data: Net now correctly excludes the employer-side contribution, and Preview Breakup adds a new, explicit "Employer contributions (not included above)" line for clarity (SAL-013, fixed). Saving a component with an empty formula is now blocked outright with a clear validation message, rather than silently dropping the component (SAL-021, fixed — upgraded from silent data loss to a hard validation error). The "Limit (lesser of value or formula)" type also now exposes both a Value and a Formula field, matching its own label and legacy's equivalent (SAL-014, fixed). One gap remains open: Next.js still has no equivalent to legacy's "components must sum to Gross" save-blocking validation (SAL-019) — a structure with components summing to well under its declared Example Monthly Gross saved without warning. See Part 6 for full detail and a consolidated status table.

**Update (Part 7, 2026-08-10, continued):** a further round of changes closed the remaining gap. **SAL-019 is now fixed**, with a validation message that states the actual shortfall and suggests the fix. Two more issues, not yet re-tested in Part 6, were confirmed fixed on direct re-test: **SAL-001** (new structures losing all components on save) no longer reproduces on a fresh multi-component structure that survived a hard reload intact, and **SAL-011/SAL-016** (components silently omitted from Preview Breakup) are fixed even on old migrated data — every component now shows, with ₹0 for anything that doesn't compute, instead of vanishing. One defect remains genuinely open: **SAL-012**, migrated structures still carrying old plain-English formula text (e.g. `"Monthly Gross Salary * . 40"`) still silently fall back to their bound value instead of evaluating — this is a data-compatibility issue specific to old migrated records, not a bug in how Next.js computes its own formulas, and is best fixed as a one-time data cleanup rather than permanent parser work. Part 7 also includes a set of UI/UX recommendations gathered across this entire testing effort.

---

## Part 1 — Calculation-type field inventory (7 types)

| Type | Fields exposed | Behaves as documented? |
|---|---|---|
| **Fixed Amount** | One numeric Amount field | **Yes.** Confirmed via WWF-Employer/WWF-Employee on structure 59: ₹50 / -₹50 regardless of Monthly Gross entered (unchanged across a 10,000 → 20,000 test). Static, gross-independent, as expected. |
| **Formula (% of gross / expression)** | One text field, hint text: *"Space-separated tokens: numbers, + - * / . ( ), monthsal(example gross), or another item's token below."* | **Structurally yes, but functionally broken on real data.** The syntax itself works (confirmed by the in-app hint and token system, e.g. `1_Basic`, `84_EPF_-_Employee_Contribution`). The problem is what's actually stored: every pre-existing Formula-type component inspected across two structures (Conveyance Allowance, National/Festival Holidays, Dearness Allowance, House Rent Allowance, ESI-Employee Contribution on structure 59; Conveyance Allowance, Special Allowance on structure 35) has an **empty** formula field. None of these appeared in either structure's Preview Breakup output — not as ₹0, just absent. |
| **Limit (lesser of value or formula)** | **Only one field** — a formula-style text box | **No — cannot do what its own label says.** A "lesser of value or formula" comparison requires two inputs (a value, and a formula) to compare. Only the formula field exists; there is nowhere to enter the "value" side. This type cannot currently be configured to do what it claims. Confirmed on EPF-Employee Contribution (structure 59), which uses this type with an empty formula and was silently absent from Preview Breakup. |
| **Limit — With Lower Bound** | Two fields: formula text box + numeric bound | **Correctly structured**, not exercised against a populated formula in this pass (no example found with both fields filled). |
| **Limit — With Upper Bound** | Two fields: formula text box + numeric bound | **Correctly structured**, but see Part 2 below — when the formula is unparseable legacy-syntax text, Preview Breakup returns exactly the bound value, suggesting the formula is silently dropped and only the bound survives. |
| **Remaining Balance** | One numeric "Amount" field in the edit form (looks like a static input; earlier testing in this pass confirmed it's typable/editable, accepting an arbitrary value like "999") | **The Amount field is misleading, but the actual Preview Breakup math is sound.** Decisive test: on structure 59, the "Basic" component (type Remaining Balance, Amount field showing 10000) returned ₹10,000 in Preview Breakup at Monthly Gross = 10,000, then **jumped to exactly ₹20,000 when Monthly Gross was changed to 20,000 and recalculated** — proving Preview Breakup is genuinely computing Gross-minus-other-components at calculation time, not echoing the stored Amount field. The UI is the problem, not the math: an admin editing the structure sees an editable number that implies "this is the fixed remaining-balance value," but that number is apparently disregarded in favor of live computation. Worth a dev confirmation of what the stored Amount field is even for. |
| **Manually Entered** | One numeric Amount field | **Static as expected**, but see Part 2 — all "Manually Entered" components left at their default value of 0 (Canteen/Food Deductions, Store Deductions, Sunday allowance, Fine, Test, Performance Incentive, Overtime Allowance(OT), Shift Allowance, Bonus, Notice period pay, Arrear Salary, Holiday Wages, Mobile/Internet Allowance, Peeling Gift, Advance, Conveyance — 15 components on structure 59 alone) were silently absent from the Preview Breakup output rather than shown as ₹0 lines. |

---

## Part 2 — Preview Breakup vs. legacy math: the two core defects

### Defect A — Components with empty or unparseable formulas are silently dropped, not zeroed or flagged

Structure 59 ("basic only") has roughly 34 defined components. At Monthly Gross = 10,000, Preview Breakup returned exactly **three** lines:

| Component | Type | Result |
|---|---|---|
| WWF - Employer contribution | Fixed Amount | ₹50 |
| WWF - Employee Contribution | Fixed Amount | -₹50 |
| Basic | Remaining Balance | ₹10,000 |
| **Net** | | **₹10,000** |

Every other component — HRA, Conveyance Allowance, EPF-Employer, ESI-Employer, EPF-Employee, ESI-Employee, Dearness Allowance, House Rent Allowance, National/Festival Holidays, Leave encashment, and all 15 zero-valued "Manually Entered" items — is completely absent. For Formula/Limit types this is because their formula field is empty; the new engine appears to treat "no parseable formula" as "omit this line" rather than "compute as 0" or "flag as an error." An admin reviewing this breakdown would have no way to tell the difference between "this component evaluates to zero" and "this component was silently skipped because its formula is broken" — the output looks identical to a much simpler, correct structure.

The same pattern held on structure 35 ("BDH-With ESI and PF-13751," ~28 components): only 8 lines appeared in its Preview Breakup output (Basic, ESI-Employer, EPF-Employer, EPF-Employee, ESI-Employee, WWF-Employee, Leave encashment, HRA); the remaining ~20 components (Conveyance Allowance, Special Allowance, Dearness Allowance ×2, Professional Tax, Bonus, Overtime, and others) never appeared.

### Defect B — Legacy-syntax formula text gets silently discarded, not converted or rejected

Structure 35's Basic component is stored as type "Limit — With Upper Bound," bound = 9500, formula = `"Monthly Gross Salary * . 40"` — plain-English legacy syntax, not the new engine's `monthsal` / underscore-token syntax. Preview Breakup returned **Basic = ₹9,500 exactly**, i.e. precisely the bound value with no trace of the formula having been evaluated (0.40 × 13,751 would be ₹5,500 — nowhere close to what was shown, confirming the formula truly wasn't applied, not just miscalculated). The most likely mechanism: the parser fails silently on unrecognized tokens like "Monthly," "Gross," "Salary," and falls back to just the numeric bound. This means every migrated structure carrying legacy-syntax formulas is silently having those formulas ignored rather than being flagged for re-entry.

### Defect C — "Net" sums employer contributions into what should be an employee take-home figure

This is the most consequential finding for payroll correctness. In both structures tested, **Net = the literal sum of every line actually displayed**, earnings positive and deductions negative, with no distinction between employee-facing pay and employer-cost-only contributions:

- **Structure 59** (Gross ₹10,000): Basic (10,000) + WWF-Employer (+50) + WWF-Employee (-50) = **10,000**, matching the displayed Net exactly. The employer contribution's distorting effect is invisible here only because WWF-Employer and WWF-Employee happen to be numerically equal (₹50 each), so they cancel out.
- **Structure 35** (Gross ₹13,751): Basic (9,500) + ESI-Employer (447) + EPF-Employer (715) + EPF-Employee (-660) + ESI-Employee (-103) + WWF-Employee (-50) + Leave encashment (529) + HRA (4,251) = **14,629**, again matching the displayed Net exactly — but here it's ₹878 *above* the entered Gross, because the employer-side contributions (₹1,162 total) weren't offset by equal employee-side deductions (₹813 total).

In a real payroll structure, employer contributions (the employer's EPF/ESI/WWF match) are a cost to the company, not part of what the employee receives — they should never be added into an employee's Net pay figure. The fact that structure 35 shows Net exceeding Gross by exactly the unbalanced portion of the employer contributions confirms this isn't a rounding artifact; it's the calculator's actual arithmetic, and it will produce an inflated Net on any real structure where employer and employee contribution rates differ (the normal case — EPF/ESI employer and employee rates are rarely identical).

### Legacy comparison baseline

Legacy's Salary Structure view shows a live Head/Value/Formula table (no single "Net" total is computed — each line is shown with its own Value and Formula, organized under 5 category headings including a distinct "Employer Contributions" category, keeping employer-cost items visually and structurally separate from employee-facing pay). Earlier live testing in this project (see `Salary_Structure_Legacy_vs_NextJS_Comparison.md`) confirmed legacy formulas evaluate correctly against their own stored values — e.g. a "Salary 15K-21K" structure's Basic (`Monthly Gross Salary * .5`) matched its displayed computed Value. Legacy's category separation is itself an implicit safeguard against the Net-inflation problem found in Next.js: because legacy never sums employer contributions into a single take-home figure, defect C simply can't occur on the legacy side by construction, not because legacy solved a summation problem Next.js failed to solve.

**Important correction, see Part 4 below:** this baseline holds for pre-existing structures, but a direct, live test of legacy's own Formula builder tool (building a brand-new formula from scratch, rather than reading one that already existed) found that legacy's *current* formula-building UI has its own, separate correctness bug. Legacy is not a clean, fully-working reference implementation — see Part 4 for the decisive test and what it means for the comparison above.

---

## Part 4 — Addendum: a direct, apples-to-apples test against legacy's own Formula builder

Everything in Parts 1–3 compared Next.js's output against *already-existing* legacy data (either the read-mode Head/Value/Formula table, or formula text sitting in migrated records). None of it involved actually operating legacy's own formula-entry tool. Prompted by a direct question about whether the calculators had really been compared apples-to-apples, this addendum builds the same formula from scratch in both systems and compares live results.

### Test setup

A brand-new structure was created in legacy ("QA Formula Test," Min Salary/Wage = 10,000) via Company → Salary Structure → New. The "Basic" component's calculation type was set to Formula, and legacy's own **Formula builder** popup (a modal with a token dropdown and a numeric/operator keypad) was used to build a formula equivalent to "70% of Monthly Gross Salary": selecting **"Monthly Gross Salary"** from the dropdown, then `*`, `.`, `7`, `0` from the keypad, then clicking Ok.

### Finding 1 — Legacy's own formula builder inserts a bare digit, not a resolvable reference

Selecting "Monthly Gross Salary" from the dropdown did not insert the text "Monthly Gross Salary," and did not insert a token that resolves to the structure's actual Min Salary/Wage (10,000). It inserted the literal digit **`5`**. The resulting formula string, both in the live builder and in the saved/reloaded record, is `5 * . 70` — and the "live preview" amount legacy itself displays next to the component is **3.50** (i.e. `5 × 0.70`, treating the inserted `5` as the number five, not as a reference to 10,000). This is a bug in legacy's current formula-building flow, not a Next.js-specific problem: legacy's own builder is not correctly wiring "Monthly Gross Salary" through to the structure's actual gross value when a formula is newly composed through this UI.

### Finding 2 — But pre-existing structures compute correctly, and store formulas differently

While investigating this, a second existing structure ("test 101," Min Salary/Wage = 1,000, created previously and not touched during this test) was inspected for comparison. Its Basic component shows Value = **500** with Formulae = **"Monthly Gross Salary * . 5"** (500 = 0.5 × 1,000 — correct), and its HRA shows Value = **200** with Formulae = "Monthly Gross Salary * . 2" (200 = 0.2 × 1,000 — also correct). Critically, this record's formula displays as **human-readable text**, not the raw `5 * . 5` seen on the newly-built QA test formula.

This means the plain-English formula strings documented earlier in this report (e.g. `"Monthly Gross Salary * . 40"` on migrated structure 35) are not necessarily evidence of a hand-typed, free-text formula format — they may simply be older/correctly-functioning records, while formulas built fresh through legacy's *current* Formula builder produce a different, broken internal representation (a raw digit instead of a working reference). This looks like a regression in legacy's own formula-builder component, not a stable, working feature that Next.js failed to replicate faithfully.

### Finding 3 — Legacy enforces a hard validation Next.js completely lacks

Attempting to save a structure where the components don't numerically sum to the Min Salary/Wage produces a real, blocking validation message: **"Remaining amount for applied formula should be zero."** This is a genuine, working guardrail — Next.js has no equivalent validation anywhere in its Salary Structure form (see SAL-002: Next.js accepts and submits a fully empty form with only a raw leaked JS error on failure, and separately per SAL-001, currently drops all components regardless of validation). Legacy is doing something correctly here that's worth preserving in any redesign.

### Finding 4 — Legacy itself silently failed to save a subsequent edit

After the first save (with Basic's amount manually overridden to 10,000 to satisfy the remaining-amount-zero rule, confirmed saved and visible in the list as record #24), a second edit was attempted: leaving Basic at its formula-derived preview value (3.50) untouched, and setting HRA to 9,997 (Manually) to balance the remaining amount to zero, then clicking Save again. On reload, the structure still showed the **first** save's values (Basic 10,000, HRA 0) — the second edit did not persist, with no error message shown. This suggests legacy's own edit-save flow can silently drop changes under some conditions (specifically: when a Formula-type field's value comes from the builder's live preview rather than being directly typed by the user) — a legacy-side bug in the same spirit as Next.js's SAL-001, though the underlying mechanism appears different.

### What this changes about the earlier analysis

Parts 1–3 of this report characterize Next.js's Preview Breakup as broken relative to a working legacy baseline. That comparison still holds for **already-existing/migrated data** — legacy's stored, pre-existing formulas (like "test 101" and, going by its stable presentation, likely "Salary 15K-21K") do compute correctly against their own Head/Value/Formula view, and Next.js cannot reproduce that math (Defects A/B/C stand as documented). But this addendum shows legacy is not a fully reliable reference implementation going forward: its *own* current formula-building tool has a live, reproducible correctness bug for newly-created formulas, and its save flow can silently drop edits. Recommend the dev team treat "fix legacy's formula builder" as a parallel, not sequential, workstream to fixing Next.js — porting Next.js's calculation logic to match legacy's *current* behavior would import this bug, not avoid it. The safer reference point is legacy's older, already-correct stored formulas (and their evaluated Values), not legacy's present-day formula-entry UI.

| ID | Title | Severity | Description |
|---|---|---|---|
| SAL-017 | **Legacy's own Formula builder inserts a bare digit instead of a working reference to Monthly Gross Salary, when building a new formula** | P0 (legacy) | Selecting "Monthly Gross Salary" from the builder's dropdown inserts the literal digit `5`, not a resolvable token. A formula of "Monthly Gross Salary * .70" therefore evaluates as `5 * .70 = 3.50` instead of `10,000 * .70 = 7,000`, both in the live preview and in the saved/reloaded record. Reproduced consistently. |
| SAL-018 | **Legacy silently fails to persist an edit to an existing structure under some conditions** | P0 (legacy) | A second save on an existing structure (changing a Formula-derived preview value and a Manually-entered value) did not persist — reload showed the prior saved state with no error message. Needs reproduction by the legacy dev team to isolate the trigger condition. |
| SAL-019 | **Legacy does enforce a "components must sum to Gross" validation that Next.js lacks entirely** | Positive finding | Real, working guardrail in legacy (blocking save with a clear message) that has no equivalent in Next.js's Salary Structure form. Worth porting forward as a validation rule. |

---

## Part 5 — Addendum: the same direct test against Next.js

Following the legacy Formula builder test in Part 4, the identical test was run against Next.js directly: build a fresh structure, enter a formula for Basic using Next.js's own documented syntax rather than relying on old migrated data, save it, and run Preview Breakup.

### Finding 1 — Next.js's formula engine computes exactly correctly, when given valid syntax

A new structure ("QA Formula Test JS," Example Monthly Gross = 10,000) was created with a single component: Basic, type Formula, formula text `monthsal * . 70`, entered by hand using the syntax documented directly under the field. After saving, Preview Breakup at Monthly Gross = 10,000 returned:

- Basic: **₹7,000**
- Net (employee take-home): **₹7,000**

This is exactly correct (10,000 × 0.70 = 7,000), with no discrepancy at all — a materially better result than legacy's own current Formula builder produced on the same test in Part 4 (₹3.50, wrong). This significantly narrows the original headline finding: **Next.js's calculation engine itself is not the problem.** The defects documented in Parts 1–3 (silently omitted components, unparseable legacy-syntax formulas, Net including employer contributions) are about what happens with pre-existing/migrated data and with the surrounding UI, not about the core arithmetic being wrong. Also notable: the Preview Breakup label now reads **"Net (employee take-home)"**, more specific wording than the plain "Net" seen during the original Part 1–3 pass — worth confirming with the dev team whether this reflects an actual fix to Defect C, since this single-component test (no employer contributions present) couldn't exercise that specific bug.

### Finding 2 — Components with an empty formula are dropped silently at save time, not just hidden from the preview

A second component (Dearness Allowance, type Formula, formula left empty) was added to the same structure and saved. On reload, only the original Basic component was present — the empty-formula component was gone entirely, not just excluded from Preview Breakup's output as documented in Defect A. This is a stricter, more consequential version of SAL-011: the data itself never reaches the database, so there is no way to later find and fix an incomplete component — it simply vanishes on save with no warning.

### Finding 3 — Once any component is added, the item picker permanently locks to that component's category, blocking multi-category structures entirely

This is the most severe new finding. On both the New-structure form and the Edit view, adding the *first* component to a structure (e.g. Basic, in the "Monthly Salary Components" category) causes the "Add" button and item dropdown for every subsequent component to be scoped to *only that same category* — confirmed by inspecting the underlying `<select>` element, which offered just 7 options (Basic, House Rent Allowance (HRA), Conveyance Allowance, Service Weightage, Special Allowance ×2, Dearness Allowance (DA) — all "Monthly Salary Components") instead of the full 52-item list (which includes WWF/EPF/ESI Employer and Employee contributions, statutory deductions, leave types, and more) that's available before any component has been added.

In practice this means: **a salary structure built through the current Next.js UI can only ever contain components from a single category.** There is no visible mechanism to add an Employer Contribution or Statutory Deduction component once an earning component like Basic already exists in the structure — the section only ever shows one category heading ("MONTHLY SALARY COMPONENTS" in every test performed), never the multiple category sections legacy displays by default (Monthly Salary Components, Employer Contributions, Statutory Deductions, Variable Salary Benefits, Variable Deductions from Salary). This blocks the creation of any complete, real-world payroll structure, since real structures always combine earnings with statutory employer/employee contributions.

A direct consequence: Defect C (Net incorrectly summing employer contributions into the employee take-home figure, documented in Part 2) can no longer be freshly reproduced through the current UI, since an employer-contribution component can't be added alongside an earning component in the same structure at all. The bug may still exist in the underlying calculation logic — it simply can't be exercised via a newly-built structure right now. This should be treated as a new blocking issue in its own right, not a fix to Defect C.

| ID | Title | Severity | Description |
|---|---|---|---|
| SAL-020 | **Next.js's formula engine computes correctly given valid, current-syntax formulas** | Positive finding | `monthsal * . 70` against Gross 10,000 returned exactly ₹7,000 in Preview Breakup. The core calculation engine works; the defects found elsewhere in this report are about data compatibility and UI, not the arithmetic itself. |
| SAL-021 | **Components with an empty formula are silently dropped at save time**, not merely hidden from Preview Breakup | P0 | A Dearness Allowance component (Formula type, empty formula) was completely absent after a hard reload, following a successful save. Stricter than SAL-011: the component is lost from storage entirely, with no warning and no way to recover it later. |
| SAL-022 | **The component item picker permanently locks to a single category once any component is added**, making it impossible to build a structure spanning more than one category | P0 | Confirmed via DOM inspection: after adding "Basic" (Monthly Salary Components), the item dropdown for every subsequent "Add" only offers the same 7 Monthly-Salary-category items, never the full 52-item list that includes Employer Contributions, Statutory Deductions, etc. No UI path was found to add a second category's component to the same structure. Blocks creation of any complete payroll structure and prevents retesting SAL-013/Defect C from fresh data. |

---

## Part 6 — Addendum: post-fix re-test (2026-08-10)

**Prompt for this pass:** "I have made changes in the Next JS salary structure. I want you to test if it can do everything that legacy can." All three P0/P1 findings from Part 5 (SAL-013, SAL-021, SAL-022, SAL-014) were re-tested live against `http://localhost:3000/setup/salary-structure`, using a fresh disposable structure built through the UI exactly as an admin would.

### Finding 1 — SAL-022 (category lock) is fixed: structures can now span multiple categories

The New Salary Structure form no longer shows a single category that locks after the first component is added. It now shows **six category sections simultaneously by default** — Monthly Salary Components, Variable Salary Benefits, Variable Deductions From Salary, Employer Contributions, Statutory Deductions, Leave Types — each with its own independent "+ Add to [Category]" button and its own item dropdown.

A test structure ("QA Category Test," Example Monthly Gross = 10,000) was built with:
- **Basic** (Monthly Salary Components, Formula, `monthsal * . 70`)
- **WWF - Employer contribution** (Employer Contributions, Fixed Amount, ₹200)
- **WWF - Employee Contribution** (Statutory Deductions, Fixed Amount, ₹50)

This saved successfully as structure id 72. This is the first structure in this project's testing to span three categories in Next.js — confirming SAL-022 is resolved. Worth noting: each category's "Add" button now auto-defaults to a sensible item for that category (e.g. "Add to Employer Contributions" pre-selected WWF-Employer contribution) rather than a blank picker, which is a small but genuine usability improvement over the earlier behavior.

### Finding 2 — SAL-013 (Net including employer contributions) is fixed, and now re-confirmed on fresh data

With multi-category structures possible again, Defect C could finally be re-tested from scratch rather than relying on stale migrated data. Structure 72's Preview Breakup at Monthly Gross = 10,000:

| Line | Value |
|---|---|
| Basic | ₹7,000 |
| WWF - Employer contribution *(tagged "Employer cost")* | ₹200 |
| WWF - Employee Contribution | -₹50 |
| **Net (employee take-home)** | **₹6,950** |
| Employer contributions (not included above) | ₹200 |

₹6,950 = ₹7,000 − ₹50, correctly **excluding** the ₹200 employer contribution — this is the first time in this project's testing that Next.js has produced a correct Net figure on a structure containing both employee and employer-side amounts that don't happen to cancel out. Two supporting changes reinforce this: employer-contribution lines are now visually tagged "Employer cost," and a new explicit "Employer contributions (not included above)" summary line was added below Net. SAL-013 is fixed.

### Finding 3 — SAL-021 (empty-formula components) upgraded from silent data loss to a hard validation error

A component (Conveyance, Variable Salary Benefits, Formula type) was added with its formula left empty, then Save Structure was clicked. Instead of the component silently vanishing on save (the Part 5 finding), the save was **blocked outright** with a clear, specific error: *"A formula is required for item 'Conveyance' (operator formula)."* This is a meaningful upgrade — not only is data no longer lost silently, the admin is now told exactly which component and why. SAL-021 is fixed (in the stricter sense: prevented, not just no-longer-silent).

### Finding 4 — SAL-014 (Limit type missing its value field) is fixed

Switching a component to "Limit (lesser of value or formula)" now renders **two** fields — a "Value (compared against the...)" numeric input and a separate formula box with Builder/Edit-as-text — matching both the type's own label and legacy's two-field Limit type. Previously only the formula box existed. SAL-014 is fixed.

### Finding 5 — SAL-019 (components-must-sum-to-Gross validation) remains open

After removing the deliberately-broken Conveyance component, structure 72 (components summing to ₹7,150 — Basic ₹7,000 + WWF-Employer ₹200 − WWF-Employee ₹50) was saved against a declared Example Monthly Gross of ₹10,000, a ₹2,850 mismatch. The save succeeded with no warning of any kind. Legacy's equivalent guardrail ("Remaining amount for applied formula should be zero," documented in Part 4) still has no counterpart in Next.js. This gap is unchanged from the original report.

### Finding 6 — List-level UX parity has also improved (positive, incidental finding)

The Salary Structures list page now has a "Search by name" box and real pagination ("Page 1 of 3" with first/previous/next/last controls) — neither was confirmed present in earlier passes of this project. This closes at least part of the structural gaps noted in the original `Salary_Structure_Dev_Fix_List.md` (SAL-003–SAL-010 range); a full re-audit of that list was out of scope for this pass but is recommended as a follow-up.

### Consolidated status — "does it do everything legacy can" as of 2026-08-10

| ID | Finding | Status |
|---|---|---|
| SAL-013 | Net wrongly includes employer contributions | **Fixed** — confirmed on fresh multi-category data (Finding 2) |
| SAL-014 | Limit type missing its value field | **Fixed** (Finding 4) |
| SAL-020 | Formula engine computes correctly given valid syntax | Still holds — reconfirmed via `monthsal * . 70` on structure 72 |
| SAL-021 | Empty-formula components dropped/lost | **Fixed** — now a blocking validation instead of silent loss (Finding 3) |
| SAL-022 | Item picker locks to one category | **Fixed** — multi-category structures now buildable and saveable (Finding 1) |
| SAL-019 | No "components sum to Gross" validation (legacy has one) | **Still open** (Finding 5) |
| SAL-011 / SAL-012 | Preview Breakup silently omits empty/unparseable-formula components (on *migrated* data) | Not re-tested this pass — these describe pre-existing/migrated structures, not newly-built ones, and were out of scope here. Recommend a separate re-check against the same structures used in Part 1–3 (ids 35, 59). |
| SAL-001 (prior report) | New structures lost all components on save | Not re-tested this pass — structure 72 saved and reloaded with all 3 components intact, which is a strong incidental sign this may also be fixed, but wasn't tested against the original repro steps directly. |

**Bottom line for the user's question:** as of this re-test, Next.js can now do the specific things legacy does that were previously broken or blocked — building a multi-category structure, computing a correct employee-only Net, configuring a proper Limit-type comparison, and refusing to silently lose data on an incomplete formula. The one capability legacy still has that Next.js doesn't is the hard "components must equal Gross" save-time check (SAL-019). Recommend confirming SAL-001/SAL-011/SAL-012 in a follow-up pass before calling the module fully at parity.

---

## Part 7 — Addendum: second re-test round (2026-08-10, continued) + UI/UX recommendations

Following another round of developer changes, the remaining open items from Part 6 (SAL-019, plus the not-yet-re-tested SAL-001/SAL-011/SAL-012) were tested directly, using both a new from-scratch structure and the same two migrated structures (35, 59) used throughout this report.

### Finding 1 — SAL-019 (Gross-sum validation) is now fixed, with an unusually good error message

A new structure ("QA Regression Test 2," Example Monthly Gross = 10,000) was built with only a Basic component at `monthsal * . 30` (₹3,000 — deliberately far short of Gross). Save was blocked with: *"Monthly Salary Components must sum to the Example Monthly Gross (currently ₹3000.00, expected ₹10000.00). Add a Remaining Balance component or adjust amounts so they add up exactly."* This is a genuine fix, and it's a better message than legacy's equivalent — it states the actual numbers involved and suggests the specific fix. Following the message's own suggestion (adding a House Rent Allowance component set to "Remaining Balance"), the structure saved successfully, and Preview Breakup confirmed Basic ₹3,000 + HRA ₹7,000 = Net ₹10,000 exactly. SAL-019 is fixed.

### Finding 2 — The Remaining Balance type now has clear inline guidance (fixes SAL-015 as a side effect)

Selecting "Remaining Balance" as a component's type now immediately shows an inline note: *"This is auto-computed as Gross minus other Monthly Salary Components at calculation time — the Amount below is not used and can be left as-is."* This directly resolves SAL-015 from Part 1, where the Amount field looked editable and authoritative but was actually ignored — admins now get the actual mechanics explained at the point of confusion, not left to infer it from behavior.

### Finding 3 — SAL-001 (new structures losing components on save) is fixed

The "QA Regression Test 2" structure above (2 components, built entirely from scratch) was hard-reloaded after saving. Both components — Basic (Formula, `Monthly Gross Salary * . 30`) and House Rent Allowance (Remaining Balance) — were still present with their configuration intact, including the auto-computed Remaining Balance amount (7000) persisted into the Amount field. This is a clean, direct repro-equivalent of the original SAL-001 bug, and it no longer reproduces. SAL-001 is fixed.

### Finding 4 — SAL-011 and SAL-016 (silently omitted / zero-valued components) are fixed on migrated data too

Re-checking structure 59 ("basic only," ~34 components, most with empty formulas) at Monthly Gross = 10,000: Preview Breakup now lists **every single component**, including the ~30 that were previously silently omitted — each showing ₹0 where its formula is empty, rather than being absent from the list entirely. Zero-valued Manually Entered components (Shift Allowance, Bonus, Fine, Test, etc.) are also now shown explicitly as ₹0. This is a meaningful transparency fix: an admin can now see at a glance exactly which components are contributing nothing, instead of not being able to tell "computes to zero" apart from "silently broken." Net (employee take-home) on this structure computed correctly as ₹9,950 (₹10,000 Basic − ₹50 WWF-Employee, correctly excluding the ₹50 WWF-Employer contribution). SAL-011 and SAL-016 are fixed.

### Finding 5 — SAL-012 (legacy-syntax formulas silently discarded) is still open

Structure 35's Basic component is unchanged: type "Limit — With Upper Bound," formula still stored as the plain-English legacy string `"Monthly Gross Salary * . 40"`, bound = 9500. At Monthly Gross = 13,751, Preview Breakup still returns **Basic = ₹9,500 exactly** — the same bound-only result documented in the original Part 2 (Defect B). 0.40 × 13,751 = ₹5,500.40, nowhere close to what's shown, confirming the legacy-syntax formula is still not being evaluated; the parser is still silently falling back to the bound value. This specific defect was not touched by the recent fixes. Worth noting as a side effect: because this formula still doesn't evaluate, and structure 35 also has a duplicate item ("Dearness Allowance" and "Dearness Allowance (DA)" both present, only one populated) left over from migration, Net on this specific structure still comes out well above Gross (₹17,717 vs. ₹13,751) — but this is now caused by SAL-012 and the leftover duplicate-item data quality issue, not by employer contributions being wrongly included (those are correctly excluded and separately reported, per Finding 4's mechanism). **SAL-012 remains open** — recommend the parser either learns to read the plain-English legacy phrasing, or (more robustly) a one-time migration script that rewrites stored legacy-syntax formula strings into the new `monthsal`/token syntax, since patching the live parser to understand two formula dialects indefinitely is fragile.

### Consolidated status update (supersedes the Part 6 table)

| ID | Finding | Status |
|---|---|---|
| SAL-001 | New structures lose all components on save | **Fixed** (Finding 3) |
| SAL-011 | Preview Breakup silently omits empty/unparseable-formula components | **Fixed** — confirmed on migrated structure 59 (Finding 4) |
| SAL-013 | Net wrongly includes employer contributions | **Fixed** (Part 6) |
| SAL-014 | Limit type missing its value field | **Fixed** (Part 6) |
| SAL-015 | Remaining Balance Amount field misleading | **Fixed** (Finding 2) |
| SAL-016 | Zero-valued Manually Entered components omitted | **Fixed** (Finding 4) |
| SAL-019 | No "components sum to Gross" validation | **Fixed** (Finding 1) |
| SAL-020 | Formula engine computes correctly given valid syntax | Confirmed, still holds |
| SAL-021 | Empty-formula components silently dropped | **Fixed** (Part 6) |
| SAL-022 | Item picker locks to one category | **Fixed** (Part 6) |
| SAL-012 | Legacy-syntax migrated formulas silently discarded (bound-only fallback) | **Still open** (Finding 5) |

**Bottom line:** as of this round, every P0 defect specific to Next.js's own logic is fixed. The one remaining gap (SAL-012) is specifically about *reading old legacy-format formula text* — a data-migration/compatibility problem, not a bug in how Next.js computes its own formulas. Recommend treating it as a one-time data-cleanup task (rewrite the ~handful of legacy-syntax formulas still in migrated structures) rather than permanent parser work.

---

## Part 8 — UI/UX design recommendations

These are drawn from everything observed testing this module across legacy and Next.js — some are gaps against legacy, most are just opportunities noticed while exercising the UI directly, independent of the bug-fixing work above.

**1. Extend the new "tell them what to do" validation pattern everywhere.** The SAL-019 message ("...currently ₹3000.00, expected ₹10000.00... Add a Remaining Balance component or adjust amounts") is a genuinely strong pattern — it states the actual numbers and suggests the fix, not just "this is wrong." Right now it's inconsistent: the SAL-021 formula-required error just names the field, with no suggested action. Worth standardizing this style (state the gap, suggest the fix) across every validation message in the form.

**2. Give Preview Breakup category grouping and subtotals, matching the edit form above it.** The edit form now organizes components into six clear category sections, but Preview Breakup below it renders everything as one long flat list (20-30+ rows on a typical migrated structure). An admin has to manually mentally group "which of these are employer costs vs. employee earnings vs. deductions" by scanning for the orange "Employer cost" tag and red text color. Recommend mirroring the edit form's category headings in the breakup, with a subtotal per category — this would also make legacy's category-based mental model (which never had a Net-inflation problem, partly *because* it never collapsed categories into one number) easier to carry over.

**3. Add a live running total instead of only validating at Save.** Right now the "components must sum to Gross" check only fires as a blocking error after clicking Save — an admin builds the whole structure, then finds out at the end it's off by some amount. A small live indicator near the Example Monthly Gross field (e.g., "Remaining to allocate: ₹2,850") updating as components are added, similar in spirit to legacy's Remaining Amount concept but fixed to not require exact-zero mid-edit, would let admins self-correct as they go rather than after the fact.

**4. Prevent or flag duplicate item selection within one structure.** Structure 35 (migrated) has two separate "Special Allowance" entries and both "Dearness Allowance" and "Dearness Allowance (DA)" in the same structure — one populated, one not. The item dropdown doesn't currently filter out items already present in the structure, so this is easy to create by accident and easy to miss in a long list. Recommend either graying out/hiding already-added items in the picker, or showing an inline warning badge next to duplicate item names.

**5. Reconsider the fallback "PICK AN ITEM…" section at the bottom of the New Structure form.** In addition to the six named category "Add" buttons, a generic catch-all row auto-appears below Leave Types with its own "Select item…" dropdown and "Add to structure" button. It's not clear what category a component added here lands in, and it duplicates functionality the six category sections already cover more clearly. Recommend removing it, or clarifying what it's for if it's intentional (e.g., items that don't map to any of the six categories).

**6. Add live formula preview at edit time, not just at save time.** The formula field now nicely converts `monthsal * . 30` into readable text like "Monthly Gross Salary * . 30" — a good touch. Taking this further: showing a live "= ₹3,000" preview next to the formula box, computed against the Example Gross as the admin types, would let them catch a typo'd formula immediately instead of via a Save → error → fix loop (or worse, via a silently-wrong Preview Breakup number later).

**7. Add sorting and status filtering to the Salary Structures list.** Search and pagination are now present (a good recent addition), but with 60+ structures in this tenant alone, being able to sort by column (Name, Example Gross, Status) or filter to Active-only would meaningfully speed up finding a specific structure. Consider also a "Clone structure" action — many list entries are clearly near-duplicates of each other (e.g., "BDC-With ESI-13751" / "BDC-With ESI-15101," "BDH-With PF-13751" / "BDH-With PF-21000"), suggesting admins are already manually recreating similar structures from scratch; a clone-and-edit flow would remove that repetition.

**8. Add hover tooltips to the list's action icons.** The pencil (edit) and circle-slash (deactivate, active rows only) icons in the Salary Structures list have no visible label — a first-time admin has to guess or hover-and-hope. A simple title/tooltip on each would remove the ambiguity, especially since deactivating is a state-changing action worth being unambiguous about before clicking.

---

## Part 3 — Consolidated new findings (extends SAL-001 through SAL-010)

| ID | Title | Severity | Description |
|---|---|---|---|
| SAL-011 | **Preview Breakup silently omits any component with an empty or unparseable formula, instead of showing ₹0 or an error** | P0 | Confirmed on 2 real structures — only 3 of ~34 and 8 of ~28 components appeared, respectively. Makes the calculator actively misleading: a structure missing most of its math looks identical to one that's simply small. |
| SAL-012 | **Migrated legacy-syntax formulas (e.g. "Monthly Gross Salary * . 40") are silently discarded rather than converted or flagged** | P0 | Confirmed on structure 35's Basic: Limit-With-Upper-Bound returned exactly the bound (9500) with zero evidence the formula was evaluated. Every migrated structure carrying pre-migration formula text is affected. |
| SAL-013 | **"Net" incorrectly sums employer-side contributions into the employee take-home figure** | P0 | Confirmed via exact arithmetic reconciliation on 2 structures. Will inflate Net on any real structure where employer and employee contribution rates differ, which is the normal case for EPF/ESI in India. This is a payroll-correctness bug, not a cosmetic one. |
| SAL-014 | **"Limit (lesser of value or formula)" type is missing its "value" field** | P1 | Only a formula box is exposed; the type's own name promises a comparison against two inputs that don't both exist in the UI. Cannot currently be configured as documented. |
| SAL-015 | **"Remaining Balance" component's Amount field is editable but appears to be ignored at calculation time** | P2 | Confirmed the underlying Preview Breakup math correctly recomputes Remaining Balance against whatever Monthly Gross is entered (₹10,000 → ₹20,000 test produced an exact 2x jump). But the edit form presents Amount as a plain typable number with no indication it's disregarded, which will confuse anyone editing the structure directly. |
| SAL-016 | **Zero-valued "Manually Entered" components are omitted from Preview Breakup rather than shown as ₹0** | P2 | Consistent with SAL-011 but distinct root cause (intentional value of 0, not a broken formula) — worth deciding whether the calculator should show all defined components regardless of value, for transparency. |

---

## What this means for the migration

**Status as of Part 6 (2026-08-10) — see Part 6 for full detail and the consolidated status table.**

1. **SAL-022 (category lock), SAL-013 (Net including employer contributions), SAL-021 (empty-formula components), and SAL-014 (Limit type missing its value field) are all now fixed and re-confirmed on fresh, freshly-built data.** The dev team's changes resolved every P0/P1 item that was open as of Part 5. This is the headline update: as of this pass, Next.js can build a real, multi-category salary structure, compute a correct employee-only Net, configure a proper Limit comparison, and can no longer silently lose an incomplete component.
2. **The calculation engine itself was already sound (SAL-020)** — `monthsal * . 70` computed exactly right in both Part 5 and Part 6. The fixes since Part 5 were about validation, categorization, and Net's exclusion logic around that engine, not the arithmetic itself.
3. **One gap remains open: SAL-019, the "components must sum to Gross" save-time validation that legacy enforces.** A structure with components summing to well under its declared gross saved without any warning in Next.js. This is the one specific capability legacy still has that Next.js doesn't, as of this pass.
4. **Not re-tested in Part 6, and worth a follow-up pass**: SAL-011/SAL-012 (Preview Breakup silently omitting components with empty/legacy-syntax formulas) describe *migrated* structures specifically (ids 35, 59) — Part 6 only tested newly-built structures, so these should be re-checked against the same migrated records before considering them resolved. SAL-001 (new structures losing all components on save) also wasn't directly re-tested against its original repro steps, though structure 72 saving and reloading intact in Part 6 is a good incidental sign.
5. **Recommend closing this out with one follow-up pass**: (a) add the Gross-sum validation (SAL-019) to reach full parity with legacy's guardrail; (b) re-check SAL-011/SAL-012 against structures 35 and 59 specifically, since those were never re-tested after the fixes; (c) re-run the original SAL-001 repro steps to confirm that bug is actually fixed and not just incidentally avoided.

---

## Notes on testing method

Parts 1–3 were read-only (no structures modified or saved) — findings came from inspecting existing component configurations and running non-destructive Preview Breakup calculations. Parts 4 and 5 involved live, disposable test structures created in both legacy ("QA Formula Test," Great Leap MPM tenant) and Next.js ("QA Formula Test JS," id 69, GRTL tenant) specifically to test formula-building from scratch rather than reading pre-existing data; both were left in place as reproduction cases for the respective dev teams. Two Monthly Gross values (10,000 and 20,000) were run against structure 59 in Part 1 to distinguish genuinely-computed values from static echoes, which is what surfaced the Remaining Balance finding (SAL-015) as a false alarm relative to initial suspicion. Part 6 (2026-08-10) followed the same live/disposable-structure method against Next.js only, after developer-side changes to the module: a new multi-category structure ("QA Category Test," id 72, GRTL tenant) was built from scratch, saved, and used to re-run Preview Breakup and re-test the empty-formula and Limit-type behavior documented in Part 5; it was left in place as a reproduction/reference case. Legacy was not re-tested in Part 6, since the user's request was specifically to verify the Next.js changes. Part 7 (2026-08-10, continued) followed a further round of developer changes: a second disposable structure ("QA Regression Test 2," id 77, GRTL tenant) was built from scratch to re-test SAL-019/SAL-001/SAL-015, and the same two migrated structures used in Part 1 (ids 35 and 59) were re-inspected directly to re-test SAL-011/SAL-012/SAL-016 against real pre-existing data rather than newly-built structures — this is what surfaced SAL-012 as the one defect that's still open.
