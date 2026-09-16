# Salary Increment — Next.js Migration Plan

**Legacy:** `Controller/SalaryIncrementController.php` (~5,800 lines, 40+ actions) + views `salary_increment.ctp`, `view.ctp`, `index.ctp`, `upload.ctp`
**Next.js target:** `/payroll/increments` (already routed in `src/lib/navItems.ts:92`)
**Date:** 2026-09-01
**Companion docs:** `Salary_Increment_Legacy_vs_NextJS_Comparison.md` (scope gap analysis)

**Status:** Phases 0 + 1 + 2 + 3 + 4 implemented. Phases 5–7 (multi-employee batch, CSV upload, reports) not started.

### Implementation log

- **2026-09-04 — Phase 3 (structure-change branch)**
  - New `rizo/src/lib/salaryStructureAllocate.ts` — `allocateSalaryStructure(conn, {...})`, the
    verified core of `bulk-policies/salary/route.ts` `assign()` extracted so both screens share
    it (ctc_upload_type reset → `sal_structure_distribution_fn` → remark recompute → `emp_config`
    insert → `salary_structure_limit_prc`). `assign()` now delegates to it.
  - New export `alterSalaryStructure(pool, hikePkey, userId, companyCode)` in
    `rizo/src/lib/increments.ts` — port of `SalaryIncrementController::alterSalaryStructure()`:
    per `(emp_fkey, structure_id)` draft pair, resolve dates (`resolveEffectiveDates` helper),
    joining guard, `total = SUM(new_amount)` (Direct/head 1), **statutory pre-check** (UAN/PF,
    ESI, LWF, PAN/TDS — legacy's `arr_statuttory_fields` UNION ported parameterised),
    **wrong-salary gate** (`structure_eg_amt > total`), `emp_ctc_upload` seed for no-structure +
    no-CTC employees, then `allocateSalaryStructure()`. Per-employee transaction. Returns
    `{ perEmployee, missingFields, wrongSalary }`.
  - `POST /api/payroll/increments/[id]/process` runs `alterSalaryStructure()` before
    `processIncrement` / `processItemIncrement` when `structure_change='Y'`; returns
    `structureResult`. Those two process functions are unchanged — their existing guard now
    passes for aligned employees and still skips blocked ones (matches legacy `process()`).
  - `page.tsx`: removed the "not yet available" warning; Process result surfaces
    `structureResult` messages.
  - Verified against live `mypayrol_mpm121` (GRTL) with transaction rollback: happy path
    (emp 240 struct 16→22 — fn=1, 9 new rows, old end-dated, `emp_config_bi` set
    `emp_proff.structure_id`, guard passes), statutory block (emp 229→16 → "ESI No"),
    wrong-salary gate, first-time `emp_ctc_upload`→`emp_ctc_transaction` trigger seed.
  - `tsc` clean; `eslint` clean on touched files (pre-existing unrelated error at
    `increments/page.tsx:228` left as-is); `next build` compiles + types clean.

- **2026-09-01 — Phase 0 + Phase 1**
  - `rizo/src/lib/increments.ts`: added `CALL salary_structure_limit_prc` after the `emp_ctc_upload` insert in `processIncrement()` (INC-P0-1); added `getIncrementBatch`, `deleteIncrementDraft`, `checkArrear`, `getIncrementSummary`, `listPendingEmployees`.
  - `rizo/src/app/api/payroll/increments/route.ts`: pending filter now `action IS NULL OR action <> 'Processed'` (INC-P0-5).
  - New routes: `increments/[id]/route.ts` (GET batch / DELETE soft-delete), `increments/arrear-check`, `increments/pending-employees`, `increments/summary`.
  - `rizo/src/app/(dashboard)/payroll/increments/page.tsx`: added "Revision Due" tab (3 counter cards + Due/Overdue/NoStructure filter + worklist with "Draft Increment" action), per-row View modal (mirrors `view.ctp`, flags structure-change batches as not-yet-processable), per-row soft-delete with inline confirm, and the effective-date arrear hint in the create form.
  - `npx tsc --noEmit` and `eslint` clean. Not yet exercised against a live DB.
  - INC-P0-2 (payout-month `'processed'` casing) left as-is — MySQL's default `latin1_swedish_ci` collation makes `action = 'Processed'` case-insensitive, so it already matches legacy's lowercase literal. INC-P0-3 (`approved_by`) still open pending team decision.

- **2026-09-01 — Phase 2 (structure preview) + entry-button rework**
  - Entry button relabeled **"New Increment" → "Salary Update"**, now opens the form in the shared large **`Modal`** (`components/ui/Modal`, `max-w-5xl`, backdrop/Esc/X close, Cancel + Save Draft footer) — matches legacy's `showLargeModalForm(... salaryIncrementForm)` `modal-lg`. Form titled **"Salary Update Form"**. The worklist's per-row **"Draft Increment"** (legacy `#salaryManagetBtn` select-then-act) opens the same modal pre-filled.
  - `rizo/src/lib/increments.ts`: added `getEmployeeCurrentStructure()` — mirrors `getSalaryStructure()` / `getGrossByEmp()` (current structure_id, monthly gross, annual CTC, next-increment-on-record, designation/branch/dept/joining, current component values).
  - New route `increments/employee/[empFkey]/structure` (GET).
  - `page.tsx`: picking an employee now loads their current state and pre-fills structure + new gross + next-increment date, shows an employee-info strip, and renders a **Current / New / Change** preview table for the target gross by calling the existing shared `/api/setup/salary-structures/[id]/breakup?gross=` endpoint (wraps `calculate_emp_salary_breakup`). Below-structure-minimum warning added.
  - `tsc` + `eslint` clean (note: this repo's eslint bans `setState` inside `useEffect` — the prefill runs in the employee-select event handler via an imperative `fetch`, not an effect).
  - Still deferred at this point: the **"Upload" (CSV) button** flow (Phase 6) and the **Single/Multiple** radio + multi-employee `itemIncrementForm` (Phase 5).

- **2026-09-01 — Phase 4 (single-employee component-level increment)**
  - `rizo/src/lib/salaryFormula.ts`: added `evaluateArithmetic(expr)` — safe eval of a plain `+-*/()` numeric string (for callers doing their own token substitution first, like the component recalc).
  - `rizo/src/lib/increments.ts`: `getEmployeeCurrentStructure()` now also returns `head_fkey` per component. New:
    - `recalcComponentBreakup()` — mirrors `onIncrementChangeNew()`: `CALL calculate_emp_component_breakup(structure, item, newValue, gross)`, then re-evaluate each dependent row's `structure_det_calequation` (replace `monthsal` / `rembalance` / `<id>_<word>` → value, `evaluateArithmetic`, apply `limit`/`limit_wl`/`limit_wg`/`fixed` + deduction sign). Handles both flat and `salary_breakup_temp`-nested proc rows.
    - `createItemIncrementDraft()` — mirrors `saveIncrement()` item='Y' branch: `salary_hike` (`item='Y'`) + one `salary_hike_details` per **changed** component.
    - `processItemIncrement()` — mirrors `processItem()`: `copy_salary_structure_to_new` → per-component `emp_salcomp_upload` upsert (old `status=0`, new `rate=new_amount`) → `CALL ctc_component_update_and_upload_prc` → `UPDATE emp_ctc_transaction` (arrear/payout/effective/next-increment/`ctc_upload_type=1`) → `reevaluateStructureRemarks()` (ports `updateRemarkValue()` token substitution + the plain-arithmetic remarks eval, no `eval`) → `CALL salary_structure_limit_prc` → mark processed.
  - Routes: `POST /api/payroll/increments` branches on `components[]` → item draft; `POST .../[id]/process` branches on `salary_hike.item` → `processItemIncrement`; new `POST .../component-recalc`. List `GET` now `GROUP BY salary_hike_pkey` (item hikes have N detail rows) with `MAX`/`SUM` and a `component_count`.
  - `page.tsx`: **"I will update: Gross total / Individual components"** toggle in the modal. Components mode shows an editable Current / New / Change grid built from the employee's live structure; editing a row calls `component-recalc` and applies the returned dependent values. Only changed rows are saved. List shows "· N components" on item rows.
  - `tsc`, `eslint`, and `next build` all clean. **Not yet run against a live DB** — `calculate_emp_component_breakup` row shape (flat vs. table-nested), `ctc_component_update_and_upload_prc` behaviour, and the remarks re-eval parity all need a scratch-tenant check.
  - Deferred within Phase 4: legacy's 3 entry modes (New / +Amount / +%) per row — only direct "New" editing is wired; Δ / Δ% are display-only.

- **2026-09-01 — Phase 4 correction: which components show / are editable**
  Checked `getSalaryStructure()` + `renderSalaryStructure()` + `changeItem()`. Legacy shows **three
  filtered groups**, not all `emp_salary_structure` rows:
  | Group | Filter | Editable? |
  |---|---|---|
  | `structure` — Monthly Salary Components | `head_operator='Addition'` AND `item_part='Direct'` AND `salary_heads.head_pkey = 1` | **Yes** (only this group; `changeItem` un-readonlies `.increment-*` which are on Direct rows only) |
  | `structure_indirect` — Indirect / employer | `Addition` AND `Indirect` AND `head_pkey <> 1` AND `salary_head_items.head_fkey IN (4,10)` | No — read-only, recomputed by `onIncrementChangeNew` |
  | `emp_contribution` — Statutory deductions | `Deduction` AND `Direct` AND `head_pkey <> 1` AND `salary_head_items.head_fkey = 5` | No — read-only, recomputed |
  Plus display-only Gross/CTC/Yearly summary rows. `changeItem`'s other mode ("I will update: Gross Salary") makes only the single Gross summary input editable.
  Fixes made: `getEmployeeCurrentStructure()` now returns `structure` / `structure_indirect` / `emp_contribution` as three arrays mirroring those exact queries (was: one flat list of every active row). The form renders three sections — Monthly Salary Components with editable New inputs, the other two read-only — and `recalcComponent` sends only the Direct group's values as `new_values` / `grossAmount` (matches legacy `sendIncrementData`, which only collects `new_value_*`). Save still writes a `salary_hike_details` row for every changed row across all three groups (matches `saveIncrement`'s `desc_*` loop).

- **2026-09-01 — "Update Salary" modal redesign (UI only, no logic change)**
  Rebuilt the modal as a guided workflow: fixed header ("Update Salary" + `emp_name · id` + Unsaved-changes badge) / scrolling body / sticky Cancel+Save footer, inside the shared `Modal` (`!max-w-[1160px] !p-0 !overflow-hidden flex flex-col`). Sections: compact employee summary (metric tiles + "Change employee"), segmented **Update salary by** control (Gross Salary / Individual Components, each with a one-line description, selected = solid primary), two-column inputs ("Effective From" label), **Salary Impact** (Monthly Gross / Annual CTC / Take Home current→new + Annual Impact — all derived from `empStructure` + the breakup/recalc results, nothing hardcoded), **What changed** (only when there are changes), and a **Salary breakdown** grouped into collapsible Earnings / Employer Contributions / Employee Deductions with per-group change counts; Earnings rows stay editable in components mode. Added `emp_name` and `all_current` (full current-value map, for the preview's Current column) to `getEmployeeCurrentStructure()`. No API/validation/calculation changes. `tsc` + `eslint` + `next build` clean.

---

## 1. Current state

Already built (`rizo/src/lib/increments.ts`, `src/app/api/payroll/increments/route.ts`, `.../[id]/process/route.ts`, `src/app/(dashboard)/payroll/increments/page.tsx`):

- **Draft** a single-employee, gross-level increment (`salary_hike` + `salary_hike_details`, `is_multiple='N'`, `item='N'`).
- **Process** it: joining-date / structure-amount guards → `copy_salary_structure_to_new` → insert `emp_ctc_upload` (structure regen happens DB-side via the `emp_ctc_upload` AFTER INSERT trigger → `sal_structure_distribution_fn`) → mark `processed='Y'`, `salary_hike.action='Processed'`.
- **List** with Pending / Processed tabs.

The port is faithful within that slice. Everything else in this plan is net-new.

---

## 2. Conventions to follow (observed in the codebase)

| Concern | Established pattern | Reference |
|---|---|---|
| DB access | `getCompanyPool(session.user.companyCode)` from `@/lib/db`; parameterised `pool.execute`/`pool.query` | `increments.ts`, all API routes |
| Auth | `getServerSession(authOptions)`; payroll routes gate on `session.user.userGroup === 1` | `api/payroll/increments/route.ts:10` |
| Stored proc + OUT param | `CALL proc(?, ?, @err)` then `SELECT @err AS err`; return the string, let caller decide | `src/lib/payroll.ts:22-27` |
| Formula evaluation | **Never `eval()`.** Use `evaluateSalaryFormula()` / `buildItemToken()` / `formatFormulaForDisplay()` from `@/lib/salaryFormula` (recursive-descent parser, legacy-token-grammar compatible) | `src/lib/salaryFormula.ts` |
| Calling `calculate_emp_salary_breakup` + app-layer breakup fixups | Existing wrapper: `CALL calculate_emp_salary_breakup(0, structureId, gross)` then cross-reference `salary_structure_details`, split Employer Contributions (head_pkey 4) out of Net, flag un-parseable formulas | `src/app/api/setup/salary-structures/[id]/breakup/route.ts` |
| Business logic location | Pure logic in `src/lib/<feature>.ts`; thin route handlers that do auth + pool + call lib | `increments.ts` ↔ its routes |
| Client data | TanStack Query (`useQuery`/`useMutation`), `queryClient.invalidateQueries` | `increments/page.tsx` |
| Tables/lists | `DataTable` from `@/components/data-table/DataTable` (client-side pagination) | `increments/page.tsx:229` |
| Employee picker | `<EmployeeSearch value onChange />` (`/api/employees?search=`) | `increments/page.tsx:167` |
| Excel/PDF export | Client-side via `@/lib/reportExport` (`exportReportToExcel`, `toDataTableColumns`) — not PHPExcel server streaming | `src/lib/reportExport.ts` |
| Port provenance comments | Each function carries a `// Mirrors SalaryIncrementController::<fn>()` note + any deliberate divergence spelled out | `increments.ts` throughout |
| "This is NOT the Next.js you know" | Check `node_modules/next/dist/docs/` before using Next APIs; heed deprecations | `rizo/AGENTS.md` |

---

## 3. Correctness fixes to fold into the existing slice (Phase 0)

These are bugs/divergences already identified in the current port. Do them first — small, same files.

| ID | Fix | Where | Notes |
|---|---|---|---|
| INC-P0-1 | **Call `salary_structure_limit_prc('$emp','$user','@err')` after the `emp_ctc_upload` insert** in `processIncrement()` | `src/lib/increments.ts` | Legacy runs this after *every* structure regeneration (`process` relies on the trigger, but `alterSalaryStructure`/`processItem` both call the limit proc explicitly). Confirm with team whether gross increments need limit enforcement; if yes this is a real bug. Verify proc signature via `SHOW CREATE PROCEDURE`. |
| INC-P0-2 | Payout-month history lookup uses `action = 'Processed'` in the port but legacy matches `action = 'processed'` (lowercase) | `increments.ts` `processIncrement()` | Legacy's `payroll_master.action` values seen in schema data are `'Approved'`/`'Processed'` (capitalised) elsewhere, but `process()`/`processItem()` specifically query lowercase `'processed'`. Check live data casing; align. Low risk but affects payout month resolution for structured employees. |
| INC-P0-3 | `approved_by` written as `NULL` (port) vs legacy's string-into-int coercion | `increments.ts` | Already documented in code. Decide: keep NULL, or write `session.user.loginUserId` cast to a number if it is numeric, or change column type. Team decision, then remove the ambiguity comment. |
| INC-P0-4 | No "effect date falls in an already-processed month → arrear" hint in the form | `increments/page.tsx` + new tiny route | Port `onEffectiveDateChange()`: `GET /api/payroll/increments/arrear-check?empFkey=&date=` → `{ isArrear, message }`. Show inline when `withEffectFrom` changes. `arrear_salary` is already computed correctly at save; this is UX parity only. |
| INC-P0-5 | Pending list filter is `sh.action IS NULL`; legacy "Not Processed" is `action IS NULL OR action != 'Processed'` | `api/payroll/increments/route.ts:15` | Align so a row with a non-Processed non-null action still appears. Minor. |
| INC-P0-6 | No per-batch review before Process, no Delete | `increments/page.tsx` | Add: (a) a row-expand or modal showing `salary_hike_details` grouped by employee (mirrors `view.ctp`), (b) `DELETE /api/payroll/increments/[id]` → `UPDATE salary_hike SET status = 0` (mirrors `deleteIncrement()`), Pending tab only. |

**Acceptance:** existing gross draft→process flow still passes its manual test; limit proc runs; arrear hint shows; a drafted increment can be viewed and soft-deleted.

---

## 4. Phased build

Phases are independent enough to ship one at a time; suggested order is by value-to-effort. Each phase = a `src/lib/increments*.ts` addition + route(s) + UI.

### Phase 1 — Employee revision worklist + dashboard counters  *(highest value, self-contained)*

**Legacy:** `employeelistPending()`, `getSummaryValue()`, the 3 cards + Due/Overdue/NoStructure radio filter in `index.ctp`.

**Build:**
- `src/lib/increments.ts`: `listPendingEmployees(pool, { status, branch, structureId, empFkey, empName, page, pageSize })` and `getIncrementSummary(pool)`.
  - Buckets (from `emp_ctc_transaction.next_increment_date`, `end_date_effective IS NULL`, joined to `emp_proff`):
    - **Due:** `DATEDIFF(next_increment_date, CURDATE()) BETWEEN 0 AND 44`
    - **Overdue:** `next_increment_date < CURDATE()` (and not `'0000-00-00'`/NULL)
    - **NoStructure:** `emp_proff.structure_id IS NULL`
    - default = union of all three
  - `getIncrementSummary` = the single `SUM(CASE …)` query in `getSummaryValue()`.
- `GET /api/payroll/increments/pending-employees?status=&branch=&structure=&emp=` and `GET /api/payroll/increments/summary`.
- UI: third tab **"Revision Due"** before Pending/Processed; three stat cards above the table (reuse whatever card component `payroll/process` or the reports pages use); Due/Overdue/NoStructure filter; row action "Draft Increment" that opens the create form pre-filled with `empFkey` (form already accepts an employee).

**Risks:** `employee_info` view is used by legacy (`ei.EmpName`, `ei.employee_id`, `ei.branch`) — confirm it exists in the target DB or substitute `emp_details`/`emp_proff` joins. Date arithmetic in SQL, not JS, to match legacy exactly.

**Acceptance:** counts on cards equal `getSummaryValue()` for the same tenant; each bucket lists the same employees legacy lists; "Draft Increment" round-trips into an existing gross draft.

---

### Phase 2 — Live salary-structure preview in the create form

**Legacy:** `getSalaryStructure($emp_pkey)`, `getGrossByEmp($emp_pkey)`, `getTempSalaryStructure($emp,$structure,$gross)` (`structure_preview_prc`), `calcSalaryStructure()` (`calculate_emp_salary_breakup`), the `renderSalaryStructure()` / `recalcSalaryStructure()` JS in `salary_increment.ctp`.

**Build:**
- `GET /api/payroll/increments/employee-structure?empFkey=` → current structure_id, current monthly gross (`SUM(structure_det_value)` head 1 / Direct / Addition / active — reuse `getCurrentGross`), annual CTC, designation/branch/department, current per-component values (Direct additions, Indirect, employee-contribution deductions), `next_increment_date`. Mirrors `getSalaryStructure()`.
- `GET /api/payroll/increments/structure-preview?empFkey=&structureId=&gross=` → computed breakup for a *target* gross. **Reuse the existing `calculate_emp_salary_breakup` wrapper pattern** from `api/setup/salary-structures/[id]/breakup/route.ts` almost verbatim; add the `emp_ctc_transaction` empty → baseline-against-`salary_structure.structure_eg_amt` branch from `calcSalaryStructure()`/`getTempSalaryStructure()`. Prefer the app-layer `calculate_emp_salary_breakup` route over introducing `structure_preview_prc` unless a diff shows they disagree.
- UI: after employee + structure + target gross are set, show a read-only Head / Current / New / Δ table (like the salary-structure Preview Breakup). Show gross-vs-`structure_eg_amt` validation inline ("below structure minimum").

**Risks:** DAILY/HOURLY WAGES employees: annual = monthly, not ×12 (repeated 4+ places in legacy — centralise in a helper). `structure_preview_prc` vs `calculate_emp_salary_breakup` may not be identical; if preview must match what `process()` ultimately produces, verify against a real employee before choosing.

**Acceptance:** preview for employee X at their current gross reproduces their current `emp_salary_structure`; preview at a raised gross matches what `process()` then actually writes (spot-check one employee end-to-end on a scratch tenant).

---

### Phase 3 — Structure-change branch (`alterSalaryStructure`)

**Legacy:** `alterSalaryStructure()` (lines ~2118-2494) + the `structure_change === 'Y'` path in `view.ctp`'s process button.

Currently `processIncrement()` **skips** any employee whose live structure ≠ the drafted target — so a structure-change increment silently does nothing. This phase makes it real.

**Build:** `src/lib/increments.ts`: `alterSalaryStructure(pool, hikePkey, userId)` returning `{ perEmployee: [{empFkey, ok, missingFields?, wrongSalary?}], ... }`. Per employee in the batch with `structure_id != 0`:
1. Resolve `payout_month` (drafted value, else joining month if no current structure, else latest processed `payroll_master` month +1) and `with_effect_from` (joining date if no current structure).
2. Skip if `joining_date` empty or `> with_effect_from` (collect).
3. **Statutory field pre-check** (`arr_statuttory_fields` UNION query): if the target structure has non-zero EPF/PF → require `emp_details.pf`/`company_pf`; ESI → `emp_details.esi`; LWF → `lwf_code`; TDS component → `pan_no`. Collect `missing_fields` per employee; skip.
4. Check `salary_structure.structure_eg_amt <= Σ new_amount (Direct additions)` else "incorrect salary"; skip.
5. If no `emp_ctc_transaction`, create one (annual = monthly×12 or monthly for daily/hourly).
6. If structure actually changed: `SELECT sal_structure_distribution_fn(company, emp, structureId, userId)`, then re-evaluate `emp_salary_structure.remarks` formulas — **use `evaluateSalaryFormula` / a remarks helper, not `eval`** — apply ESI `ceil` vs others `round`, negate deductions, `updateAll structure_det_value`. Then update `emp_config` (type=`SALARY`, deactivate old, insert new) and `CALL salary_structure_limit_prc`.
- Route: fold into `POST /api/payroll/increments/[id]/process` — when `structure_change='Y'`, run `alterSalaryStructure` first, surface `missingFields`/`wrongSalary` messages, then run the normal `processIncrement` (which will now find the structures aligned and proceed).
- UI: `view.ctp` shows a "structure update results" panel before the process result — reproduce that with the returned per-employee messages.

**Risks:** highest-risk phase. `sal_structure_distribution_fn`, `salary_structure_limit_prc`, `emp_config` writes all mutate live payroll structure. The remarks-formula re-evaluation is the part most likely to drift from legacy — port it against `src/lib/salaryStructureSave.ts` (which already does remarks/formula handling for structure save) and diff outputs on real data. Do this phase only after Phase 2 gives a trustworthy preview to check against.

**Acceptance:** on a scratch tenant, moving an employee to a new structure via an increment produces the same `emp_salary_structure` rows as doing it through the legacy screen; statutory pre-checks block the same employees legacy blocks.

---

### Phase 4 — Component-level ("Item") increment, single employee

**Legacy:** `saveIncrement()` `item='Y'` branch, `onIncrementChange()` / `onIncrementChangeNew()` (`calculate_emp_component_breakup` + dependent-formula recalculation), `processItem()`, `updateRemarkValue()`, and the big dynamic component table + "I will update: Components" UI in `salary_increment.ctp`.

**Build:**
- `GET /api/payroll/increments/component-recalc` — wraps `calculate_emp_component_breakup(structureId, itemPkey, newValue, grossAmount)`, then recomputes only dependent rows: replace `monthsal`/`rembalance` + `<pkey>_<Item>` tokens, evaluate with `evaluateSalaryFormula`, apply operator (`limit`/`limit_wl`/`limit_wg`/`fixed`) and deduction sign. This is a direct, mechanical port of `onIncrementChangeNew()` minus `eval`.
- `src/lib/increments.ts`: extend `createIncrementDraft` (or add `createItemIncrementDraft`) to write one `salary_hike_details` row per edited component (`salary_head_item_fkey` set, `current_amount`/`new_amount`/`increment_amount`/`increment_percentage` per row, `item='Y'`).
- `processItemIncrement(pool, hikePkey, userId)` — port `processItem()`: per employee `copy_salary_structure_to_new`; per component upsert `emp_salcomp_upload` (old rows `status=0`, new row `rate=new_amount`); `CALL ctc_component_update_and_upload_prc(emp)`; `UPDATE emp_ctc_transaction` arrear/payout/effective/next-increment/`ctc_upload_type=1`; re-evaluate remarks formulas (reuse Phase 3 helper); `CALL salary_structure_limit_prc`; mark `processed='Y'`.
- Route: `POST /api/payroll/increments/[id]/process` branches on `salary_hike.item` — `'Y'` → `processItemIncrement`, else existing path (mirrors `view.ctp`'s `is_item === 'Y' ? processItem : process`).
- UI: add "I will update: Gross / Components" toggle to the create form; when Components, render the editable component grid (current / new / Δ / Δ%), wiring each edit to `component-recalc`. "Save as Item or Gross" prompt → sets `item`.

**Risks:** large dynamic form. `updateRemarkValue()`'s position-based token→number substitution is fiddly — port it carefully with unit tests against known remarks strings. `emp_salcomp_upload` / `ctc_component_update_and_upload_prc` behaviour must be verified live.

**Acceptance:** editing a single component and processing yields the same `emp_salary_structure` + `emp_salcomp_upload` as the legacy Item flow for the same input.

---

### Phase 5 — Multi-employee batch component increment

**Legacy:** `saveItemIncrement()`, `getTypeValues()`, `getEmployeesByTypeValue()`, `employeelist()` "Multiple" rows, `processItem()` (already covers batch), plus the `componentAllocate()` / `saveComponentAllocate()` / `component_increment_allocate` sub-flow behind `#salaryManagetBtn`.

**Build:**
- `GET /api/payroll/increments/type-values?type=` (branch/dept/designation/grade lists) and `GET /api/payroll/increments/employees-by-type?type=&value=` — thin lookup routes.
- `createBatchItemIncrement(pool, { componentItemId, formulaItemId, hikePercent, empFkeys, dates }, userId)` — port `saveItemIncrement()`: reject employees missing either component (`HAVING SUM(CASE…)=0` query), for the rest `new_value = value_of(formulaItemId) * hikePercent/100`, one `salary_hike` (`is_multiple='Y'`, `item='Y'`) with N detail rows; return `{ hikeId, rejectedEmployees[] }`.
- Processing already handled by `processItemIncrement` from Phase 4 (its loop is per-employee).
- Decide with team whether the `component_increment` / `component_increment_allocate` "allocate a reusable component-hike definition to employees" sub-flow is still in use. If yes, it is its own mini-module (`componentAllocate`, `saveComponentAllocate`, `getEmployeesByTypeValue`, `removeAllocate`, `saveComponentUploads`, `saveComponentAllocateKWMT`) and should get its own plan section; if no, drop it.
- UI: "Multiple employees" mode in the create form — component + formula-component + hike% + employee multi-select (by type/value); list shows "Multiple" batch rows that open a per-employee breakdown.

**Risks:** the `component_increment*` sub-flow is genuinely separate and partly tenant-specific (`saveComponentAllocateKWMT`); scope it only after confirming usage. `validEmpKeysStr` interpolation in legacy is injection-prone — parameterise.

**Acceptance:** a batch hike of X% on component C for a branch produces the same detail rows and, after processing, the same structures as legacy, for every non-rejected employee; rejected list matches.

---

### Phase 6 — CSV bulk upload

**Legacy:** `fileUpload()` view, `uploadandsaveempctc()` (gross), `uploadandsaveempctcitem()` (item), `downloadempctcformat*()` templates.

**Build:**
- `GET /api/payroll/increments/template?type=gross|item[&branch&structure]` → CSV (build server-side; reuse `reportExport` column helpers or a small CSV writer).
- `POST /api/payroll/increments/upload` (multipart) — parse CSV, validate rows (employee exists, structure allowed, amounts numeric), then reuse `createIncrementDraft` / `createItemIncrementDraft` per row inside one `salary_hike`. Return per-row success/error like legacy's `{success,msg}`.
- UI: an upload panel (drag/drop) + template download buttons, matching the existing "Upload File / Download Template" pattern used by Employee Join per the comparison docs.

**Risks:** legacy's CSV parsing is loose; define a strict, documented column contract instead of bug-for-bug matching. Large files — cap row count, run inserts in a transaction per file.

**Acceptance:** a template filled for 3 employees imports as one batch with 3 detail rows; malformed rows are reported, not silently dropped.

---

### Phase 7 — Reports

**Legacy:** `incerementReport()`, `itemIncerementReport()` (both PHPExcel), `download($salary_hike_pkey)` (CTC detail PHPExcel).

**Build:**
- `GET /api/payroll/increments/[id]/report?type=gross|item` → JSON rows (employee, dates, arrear, payout month, processed status, and for item: component/current/new).
- `GET /api/payroll/increments/[id]/ctc-report` → JSON of the per-employee CTC breakdown (`emp_salary_structure` + `emp_variable_pay_upload` union from `download()`).
- UI: client-side `exportReportToExcel` from `@/lib/reportExport` (project standard — no server PHPExcel streaming). Add export buttons to the per-batch view (Phase 0's view modal) and a Download action on Processed rows.

**Risks:** low. Column parity with the legacy spreadsheets is nice-to-have, not load-bearing.

**Acceptance:** exported sheet has the same rows/columns as the legacy report for the same batch.

---

## 5. Sequencing & dependencies

```
Phase 0 (fixes)  ──►  ship independently, anytime
Phase 1 (worklist) ─► independent, do next (best value/effort)
Phase 2 (preview) ─► prerequisite for trusting Phase 3 & 4
Phase 3 (structure-change) ─► needs Phase 2; shares remarks-formula helper with Phase 4
Phase 4 (item, single) ─► needs Phase 2; builds remarks-formula helper Phase 3 also uses
Phase 5 (batch) ─► needs Phase 4 (reuses processItemIncrement)
Phase 6 (CSV) ─► needs Phase 4 for the item path (gross path only needs Phase 0)
Phase 7 (reports) ─► needs the data from whichever phases are live
```

Shared helper to extract early (used by 3, 4, 5): **remarks-formula re-evaluation** — port from `salaryStructureSave.ts` + `salaryFormula.ts`, never `eval`.
Shared helper (2, 3, 4, processItem): **daily/hourly-wages annual-CTC rule** (`annual = monthly` for `emp_type IN ('DAILY WAGES','HOURLY WAGES')` else `×12`).

---

## 6. Open questions for the team

1. Does `salary_structure_limit_prc` need to run for **gross** increments (Phase 0), or only structure-change/item? Legacy's `process()` doesn't call it; every other legacy path does.
2. Is the `component_increment` / `component_increment_allocate` "reusable component-hike allocation" sub-flow still used by any tenant? (`#salaryManagetBtn`, `saveComponentAllocate*`.) If not, it drops out of Phase 5.
3. `payroll_master.action` casing for the payout-month lookup — is live data `'processed'` or `'Processed'`? (INC-P0-2.)
4. `emp_ctc_upload.approved_by` is `int` but legacy writes a string login id. Fix the column, the write, or leave NULL? (INC-P0-3.)
5. Which tenants (if any) need the special `saveComponentAllocateKWMT` / VDA-specific component logic? GRTL appears not to (matches the Payroll port's finding that GRTL isn't a `$specialCompanies` tenant).
6. Should the create form require Payout Month (legacy markup does) or keep it optional-with-fallback (current port does)?
7. Reports: are the exact legacy spreadsheet layouts contractually needed (e.g. sent to auditors), or is "same data, our standard export" fine?

---

## 7. Explicitly out of scope

- Changing any shared stored procedure (`calculate_emp_salary_breakup`, `sal_structure_distribution_fn`, `copy_salary_structure_to_new`, `salary_structure_limit_prc`, `ctc_component_update_and_upload_prc`) — all are called by other legacy controllers; wrap/correct in the app layer only, as the salary-structure breakup route already does.
- The dead `salary_increment` / `salary_increment_details` / `component_increment` *models* (unused in legacy too).
- Legacy company-specific dead branches already `if(false)`'d out in `salaryIncrementForm()`.
- Porting legacy's client-side `eval()` — replaced everywhere by `@/lib/salaryFormula`.

---

## 8. Rough effort shape (relative, not calendar)

| Phase | Size | Main cost |
|---|---|---|
| 0 — fixes | S | limit-proc verification, small UI additions |
| 1 — worklist + counters | S–M | SQL bucketing, `employee_info` view check, 3 cards |
| 2 — preview | M | reuse breakup route; new-joiner baseline branch; daily-wage rule |
| 3 — structure-change | L | statutory checks, distribution fn, remarks re-eval, limit proc, live-data diffing |
| 4 — item (single) | L | dynamic component grid, `component-recalc`, `updateRemarkValue` port, `emp_salcomp_upload` |
| 5 — batch | M | mostly lookups + reuse of Phase 4 processing; `component_allocate` decision |
| 6 — CSV | M | template + parser + per-row validation |
| 7 — reports | S | client export of JSON rows |
