# Salary Increment — Legacy vs. Next.js Comparison

**Legacy:** https://in.mypayrollmaster.online/Dashboard → Payroll → Salary Management (a.k.a. "Salary Increment" / "Salary Hike" / "Salary Update")
**Next.js:** `/payroll/increments` (Salary Increments)
**Date:** 2026-09-01 · **structure-change branch added 2026-09-04 (see note at end)**

**Method:** this is a **source-code comparison**, not a live behavioral test. Legacy was read from `Controller/SalaryIncrementController.php` (~5,800 lines, 40+ actions) plus its four views (`index.ctp`, `salary_increment.ctp`, `view.ctp`, `upload.ctp`). Next.js was read from `rizo/src/app/(dashboard)/payroll/increments/page.tsx`, `rizo/src/app/api/payroll/increments/route.ts`, `.../[id]/process/route.ts`, and `rizo/src/lib/increments.ts`. No increment was drafted or processed in either system.

---

## Headline answer

**Next.js implements a deliberately narrow slice of legacy's Salary Increment module — roughly 10–15% of its surface area — and the port is faithful within that slice.** Next.js covers exactly one path: a single employee, gross-level increment (`is_multiple='N'`, `item='N'`), drafted and then processed. That path is ported carefully and its own code comments say so. Everything else legacy does is absent: the per-component ("Item") increment editor, multi-employee batch increments, the CSV bulk-upload path, the live salary-structure recalculation preview, the "Revision Due / Overdue / No Structure" employee worklist with its dashboard counters, the structure-change branch that regenerates the whole salary structure, the statutory-field pre-checks (PAN/UAN/ESI/LWF), the arrear handling nuances, and both Excel reports. The migration decision to defer the component/batch editors is reasonable (they are a very large dynamic form), but the **worklist + dashboard** and **structure-change** pieces are core to how the feature is actually used and will need to come back.

---

## Scope: what each side covers

| Capability | Legacy | Next.js | Status |
|---|---|---|---|
| Single employee, **gross-level** increment (new monthly gross) | `saveIncrement()` (item='N' branch) | `createIncrementDraft()` in `increments.ts` | **Ported** |
| Process a gross increment → write CTC → regenerate structure | `process()` | `processIncrement()` + `/[id]/process` route | **Ported** (relies on DB trigger, see below) |
| Pending / Processed list of drafted increments | `employeelist()` (tabs: Not Processed / Processed) | `GET /api/payroll/increments?status=` | **Ported (reduced)** — 2 tabs vs legacy's 3 |
| Per-**component** increment editor ("I will update: Components") | `saveIncrement()` (item='Y'), `onIncrementChange()`, `onIncrementChangeNew()` | — | **Deferred** (documented in `increments.ts` header) |
| Multi-employee **batch** increment (pick a component + hike %, select many employees) | `saveItemIncrement()`, `processItem()`, `getTypeValues()`, `getEmployeesByTypeValue()` | — | **Deferred** |
| **CSV bulk upload** of increments (gross or item) | `fileUpload()` view, `uploadandsaveempctc()`, `uploadandsaveempctcitem()`, template downloads | — | **Not built** |
| **Structure-change** branch (employee moves to a different salary structure) | `alterSalaryStructure()` — statutory checks, `sal_structure_distribution_fn`, remarks-formula re-eval, `salary_structure_limit_prc` | `alterSalaryStructure()` in `src/lib/increments.ts` (2026-09-04) — same statutory checks, `sal_structure_distribution_fn`, remarks recompute, `salary_structure_limit_prc`, `emp_config` insert; runs before `processIncrement()`/`processItemIncrement()` when `structure_change='Y'` | **Ported** (live-verified; also covers first-time allocation) |
| Live structure recalculation preview while editing | `calcSalaryStructure()` (`calculate_emp_salary_breakup`), `getTempSalaryStructure()` (`structure_preview_prc`), `getSalaryStructure()`, `onEffectiveDateChange()` | — | **Not built** — Next.js form just takes a target gross number |
| "Revision Due / Overdue / No Structure" employee worklist | `employeelistPending()` — 44-day due window, overdue, no-structure buckets | — | **Not built** |
| Dashboard counters (Due / Overdue / No Structure) | `getSummaryValue()` + 3 cards on `index.ctp` | — | **Not built** |
| Arrear detection (payroll already Approved/Processed for the effective month) | `onEffectiveDateChange()` (live warning) + `saveIncrement()` sets `arrear_salary` | `createIncrementDraft()` sets `arrear_salary` from `payroll_master`; no live warning in the form | **Partial** — flag is set, UX hint is gone |
| **Item-wise Increment Report** (Excel) | `itemIncerementReport()` | — | **Not built** |
| **Increment Report** (Excel) | `incerementReport()` | — | **Not built** |
| **Cost-To-Company detailed report** download per batch | `download($salary_hike_pkey)` (PHPExcel) | — | **Not built** |
| Delete / soft-delete a drafted increment | `deleteIncrement()` (`salary_hike.status = 0`) | — | **Not built** |
| View a drafted batch before processing | `viewSalaryIncrementForm()` → `view.ctp` (grouped by employee, red = unprocessed) | Rows shown inline in the Pending table; no per-batch review modal | **Reduced** |

---

## Data model — same tables

Both sides write the same two tables, so drafts created in either system are mutually legible:

- **`salary_hike`** — one row per increment request: `is_multiple`, `item` (Y=component / N=gross), `structure_change`, `action` (NULL → `'Processed'`), `remarks`, `created_by`, `status`.
- **`salary_hike_details`** — one row per employee (or per employee×component when `item='Y'`): `emp_fkey`, `structure_id`, `with_effect_from`, `next_increment_date`, `payout_month`, `salary_head_item_fkey`, `current_amount`, `new_amount`, `increment_amount`, `increment_percentage`, `arrear_salary`, `processed`.

⚠️ The models `SalaryIncrement` (`salary_increment`), `SalaryIncrementDetails` (`salary_increment_details`) and `ComponentIncrement` (`component_increment`) are declared in the legacy controller's `$uses` array but **never referenced** — dead leftovers from an older design. Neither system uses them.

Processing writes downstream into `emp_ctc_upload` → (trigger) `emp_ctc_transaction` + `emp_salary_structure`.

---

## List / landing page comparison

| Aspect | Legacy (`index.ctp`) | Next.js (`page.tsx`) | Same? |
|---|---|---|---|
| Tabs | **Three:** Pending (employees *needing* an increment, from `employeelistPending`), Not Processed (drafted, `action` ≠ Processed), Processed | **Two:** Pending (`action IS NULL`), Processed (`action = 'Processed'`) | **No** — legacy's first tab is a *worklist of employees due for a raise*; Next.js's "Pending" is legacy's "Not Processed" (drafted-but-unprocessed requests). The employee worklist has no Next.js equivalent. |
| Dashboard cards | Revision Due (≤44 days), Revision Overdue, No Salary Structure — counts from `getSummaryValue()` | None | **No — gap.** These counters are the main "what needs attention" signal in legacy. |
| Filters | Branch, Salary Structure, Employee search, + a Due/Overdue/NoStructure radio filter panel | None on the list (employee search only exists inside the create form) | **No — gap.** |
| Grid | EasyUI datagrid, server-side pagination, per-row colouring (red = overdue, pink bg = no structure) | `DataTable` (TanStack), client-side pagination (page size 10/20/30/50) | Different toolkits; Next.js loads the whole result set for the tab and paginates in-browser. |
| Row actions | Select-then-act toolbar: Upload, Salary Update, View, Filter; Processed tab adds per-row Excel Download | Per-row "Process" button (Pending tab only); no view/download/delete | **No** — Next.js has direct per-row Process; legacy routes Process through the View modal. |
| Columns | Employee, ID, Next Increment Date, Created Date, Processed Date, Branch, Salary Structure, Download | Employee, Effective, Current, New, Increment (amt + %), Arrear? | **No** — Next.js surfaces the money columns inline (an improvement for the drafted-request view); legacy surfaces them only inside the View modal. |

---

## Create / draft form comparison

| Aspect | Legacy (`salary_increment.ctp` + `saveIncrement`) | Next.js (`page.tsx` + `POST /api/payroll/increments`) | Same? |
|---|---|---|---|
| Entry | Modal opened from toolbar; can be pre-filled with `emp_fkey` from the Pending worklist | Inline collapsible form on the same page | Different shell, same idea |
| Employee picker | `<select>` of all active employees, or locked when pre-filled | `EmployeeSearch` typeahead component | Next.js is nicer here |
| "I will update" | **Gross Salary** or **Components** | **Gross only** — no component mode | **No** — component mode deferred |
| "Increment Type" | New amount / Increment amount / Increment % (all three input modes) | Single "New Monthly Gross (₹)" number field | **No** — only the "New" mode exists |
| Salary structure | Dropdown; changing it triggers a live preview (`getTempSalaryStructure` → `structure_preview_prc`) and an Apply step | Dropdown; no preview, no Apply — the number is taken on faith | **No — gap.** Legacy shows the admin the resulting component breakdown before they commit. |
| Live recalculation | Editing any component re-runs `calculate_emp_component_breakup` and re-evaluates dependent formulas (`limit`/`limit_wl`/`limit_wg`/`fixed`, deduction sign) in PHP | None | **No** — not applicable without component mode |
| Effective-date arrear hint | `onEffectiveDateChange()` calls the server on date change; if payroll for that month is Approved/Processed it shows *"Payroll already … for this month. This will be included in arrear"* | None; `arrear_salary` is still computed server-side at save from `payroll_master` | **Partial** — outcome preserved, the inline warning is gone |
| Required fields | Employee, With Effect From, Next Increment Date, Payout Month, Remarks (all `required` in markup) | Employee, Salary Structure, New Gross, With Effect From, Next Increment Date (button `disabled` until set); Payout Month + Remarks optional | **Close** — Next.js makes Payout Month optional and defaults it; legacy marks it required in the form but also has server fallbacks |
| "Save as Item or Gross" prompt | SweetAlert on submit sets `is_item` | N/A — always gross | Deferred with component mode |
| Current-gross baseline | `SUM(structure_det_value)` over `emp_salary_structure` (head 1, Direct, Addition, active); new-joiner (`emp_ctc_transaction` empty) branch measures against the same sum and stores the delta as `new_amount` | `getCurrentGross()` — identical query; same new-joiner delta behaviour | **Yes — faithful port** |
| `structure_change` | `posted structure_id !== emp_proff.structure_id ? 'Y' : 'N'` | Identical | **Yes** |
| `increment_percentage` | `gross > 0 ? increment/gross*100 : 0` | Identical | **Yes** |
| Payout month placeholder | Legacy writes literal `'0000-00-00'` when blank | Next.js can't (strict SQL mode + parameterised insert) — defaults to the effective month's 1st; `processIncrement` still resolves a real payout month from payroll history | **Intentional divergence, documented in code** |

---

## Process flow comparison

| Step | Legacy (`process()`) | Next.js (`processIncrement()`) | Same? |
|---|---|---|---|
| Load details | `salary_hike_details` where `status=1` (all, commented-out `increment_amount != 0` filter) | `status=1 AND processed='N'` | **Close** — Next.js also filters `processed='N'` up front |
| Structure-change guard | If `structure_change='Y'` and employee's live `emp_proff.structure_id` ≠ row's target → skip that employee | Identical skip | **Yes** |
| `alterSalaryStructure()` pre-pass | On `structure_change='Y'`, the View modal first calls `alterSalaryStructure`: statutory-field checks (PAN/UAN/ESI/LWF for the target structure), create `emp_ctc_transaction` if missing, `sal_structure_distribution_fn`, re-evaluate `remarks` formulas, `salary_structure_limit_prc` | **Not ported.** Employees needing a structure change are simply skipped by the guard above; there is no path that actually moves someone to a new structure via an increment | **No — gap.** A structure-change increment is a no-op in Next.js today. |
| Joining-date guard | Skip if `joining_date` empty or `> with_effect_from`; collect into "not processed" list | Identical | **Yes** |
| Payout-month resolution | Blank → joining month (no structure) or latest `payroll_master` processed month +1 | Identical logic (note: legacy matches `action = 'processed'` lowercase; port uses `'Processed'`) | **Yes** (minor case-sensitivity nuance) |
| Salary-vs-structure guard | Skip if `salary_structure.structure_eg_amt > gross_salary`; collect into "invalid salary" list | Identical | **Yes** |
| Regenerate structure | `CALL copy_salary_structure_to_new(emp)` then `INSERT` into `emp_ctc_upload`; the real distribution happens in the `emp_ctc_upload` AFTER INSERT trigger (`sal_structure_distribution_fn`) | Identical — the port explicitly relies on that trigger rather than reimplementing distribution | **Yes** |
| Annual CTC | `gross * 12`, or `gross` for DAILY/HOURLY WAGES | Identical | **Yes** |
| `approved_by` | Legacy writes the string `login_user_id` into an `int` column (silent coercion under non-strict MySQL) | Port writes `NULL` rather than a fabricated number — noted in code as a real schema/code mismatch | **Intentional divergence** |
| Mark processed | `salary_hike_details.processed='Y'`, `payout_month` set; if any employee processed, `salary_hike.action='Processed'` | Identical | **Yes** |
| Result payload | `success`, `error_message` string listing skipped employees | `{ success, notProcessed[], invalidSalary[] }` — arrays instead of a pre-joined string; UI joins them with `; ` | Equivalent, cleaner shape |
| `processItem()` (component batch) | Full second processing path: `emp_salcomp_upload`, `ctc_component_update_and_upload_prc`, `emp_ctc_transaction` arrear update, `updateRemarkValue()` token substitution, `salary_structure_limit_prc` | — | **Deferred** |

---

## Stored-procedure dependencies

Legacy leans heavily on DB-side logic. Next.js's slice needs only two of these; the deferred features need the rest.

| Procedure / function | Used by legacy for | Needed by Next.js today? |
|---|---|---|
| `copy_salary_structure_to_new` | snapshot current structure before regenerating | **Yes** — called by `processIncrement` |
| `sal_structure_distribution_fn` (via `emp_ctc_upload` trigger) | regenerate `emp_salary_structure` from new CTC | **Yes** — fires DB-side on insert |
| `calculate_emp_salary_breakup` | live full-structure preview from a target gross | No (preview not built) |
| `calculate_emp_component_breakup` | live per-component recalculation | No (component mode deferred) |
| `structure_preview_prc` | structure-change preview | No |
| `ctc_component_update_and_upload_prc` | `processItem()` component upload | No (batch deferred) |
| `salary_structure_limit_prc` | post-distribution limit enforcement | **Eventually** — legacy runs it after every structure regen; the Next.js port does not call it |
| `att_start_end_fn` | month-boundary logic in `onEffectiveDateChange` | No |

⚠️ **`salary_structure_limit_prc` is not called anywhere in the Next.js port.** Legacy runs it after `alterSalaryStructure`, `processItem`, and the KWMT component paths. If limit enforcement matters for gross increments too (it plausibly does), that is a correctness gap, not just a missing feature.

---

## Formula / `eval()` surface

Legacy evaluates salary formulas with PHP `eval()` in several places — `alterSalaryStructure()` (remarks formulas, identifiers→0), `onIncrementChange[New]()` (dependent-component recalculation), `processItem()` (remarks re-eval), and `updateRemarkValue()` (position-based token→value substitution using `monthsal`, `rembalance`, `{itemKey}_{Desc}` tokens). **None of this is in the Next.js slice** because it all belongs to the deferred component/structure-change paths. Whenever those are built, a real expression parser will be needed on the Node side — `eval()` on DB-sourced strings should not be carried over.

---

## What this means for the migration

1. **The gross-level draft→process port is solid and low-risk.** Baseline gross query, `structure_change` detection, arrear flag, joining-date/structure-amount guards, payout-month resolution, `copy_salary_structure_to_new` + `emp_ctc_upload` insert, and the "mark processed" bookkeeping all match legacy line-for-line. The intentional divergences (`approved_by` NULL, payout placeholder) are documented in code and defensible.
2. **`salary_structure_limit_prc` should be added to `processIncrement()`.** Legacy never regenerates a structure without running it afterwards; the port skips it. Confirm with the team whether gross increments need limit enforcement — if yes, this is a bug.
3. **The employee worklist ("Revision Due / Overdue / No Structure") + dashboard counters are the biggest missing piece for day-to-day use.** Legacy's `employeelistPending()` / `getSummaryValue()` tell an admin *who needs a raise this month*; Next.js currently only shows increments an admin has already drafted. This is a self-contained port (two read queries + a 44-day date window + three counters) and worth prioritising.
4. **Structure-change increments are silently a no-op in Next.js.** The draft saves with `structure_change='Y'`, but `processIncrement()` skips those employees and there is no `alterSalaryStructure` equivalent. Either build the structure-change path or make the form reject a structure change with a clear message, so it doesn't look like it worked.
5. **Component mode and multi-employee batch are correctly deferred** — they are a large dynamic form plus a second processing pipeline plus `eval()`-based formula handling. But they are real features in legacy (the `salary_hike` sample data includes `item='Y'` batches), so plan for them rather than assuming gross-only is the whole feature.
6. **CSV bulk upload and the two Excel reports** (`itemIncerementReport`, `incerementReport`) plus the per-batch CTC `download` are not built. Lower priority than the worklist, but they exist in legacy and someone uses them.
7. **Restore the effective-date arrear warning in the form.** The `arrear_salary` flag is computed correctly at save, but legacy also warns the admin *at data-entry time* ("this will be included in arrear") via `onEffectiveDateChange`. Cheap to re-add, prevents surprise.

---

## 2026-09-04 update — structure-change branch ported

`alterSalaryStructure()` (legacy `SalaryIncrementController.php:2118`) is now ported as an export
of the same name in `src/lib/increments.ts`, plus a shared `src/lib/salaryStructureAllocate.ts`
(`allocateSalaryStructure()`) that the Bulk Policy Allocation SALARY tab and this path both use.

- `POST /api/payroll/increments/[id]/process` runs `alterSalaryStructure()` first whenever
  `salary_hike.structure_change='Y'` (mirrors `view.ctp`'s Process-button chain), then the
  unchanged `processIncrement()` / `processItemIncrement()`. Employees moved onto the target
  structure pass those functions' existing guard; employees blocked by the statutory or
  minimum-salary checks keep their current structure and are skipped — same net behaviour as
  legacy `process()`.
- Ported faithfully: the `arr_statuttory_fields` UNION pre-check (UAN/PF, ESI, LWF, PAN/TDS),
  the `structure_eg_amt > SUM(new_amount)` wrong-salary gate, the `emp_ctc_upload` seed for a
  no-structure + no-CTC employee (so `sal_structure_distribution_fn` has a CTC row), the
  distribution call, the remarks-formula recompute (ESI `ceil`/`round`, deduction sign — via
  `evaluateArithmetic`, never `eval`), and `salary_structure_limit_prc`. `emp_proff.structure_id`
  is left to the live `emp_config_bi`/`emp_config_au` triggers.
- Now also covers **first-time structure allocation** (employee with no structure), which legacy's
  `structure_id == 0` branch handles inside the same function.
- **Live-verified** against `mypayrol_mpm121` (GRTL) with transaction rollback: structure-change
  happy path (new `emp_salary_structure` rows, old end-dated, trigger sets `emp_proff.structure_id`,
  `processIncrement` guard passes), statutory block, wrong-salary gate, and the CTC-seed trigger.

Still not ported: multi-employee batch (`is_multiple='Y'`), CSV upload, both Excel reports.

## Notes on method

Source-code only for the 2026-09-01 baseline below; no live tenant was touched then. The
2026-09-04 structure-change work was verified against a live tenant (rollback). Legacy behaviour is inferred from controller logic and view markup, not from operating the screen. The Next.js port's own comments (`src/lib/increments.ts` header, and per-function "Mirrors …" notes) were taken at face value and spot-checked against the legacy source — they are accurate about what was and wasn't ported.
