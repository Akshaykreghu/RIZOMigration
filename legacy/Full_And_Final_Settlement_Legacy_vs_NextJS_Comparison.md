# Full & Final Settlement (Process Full & Final) — Legacy vs. Next.js Comparison

**Legacy:** `Controller/EmployeeResignationController.php` — `setup()` (L1985), `approves()` (L336),
`save_heads()` (L170), `leaveencash()` (L1682), `get_emp_settle_slip()` (L809), `removeemps()` (L1090),
`Viewslip()` / `get_emp_full_and_final_settle_slip()` (L1216 / L2434), `get_complete()` (L862),
`downloads()` (L1440) + views `setup.ctp`, `approves.ctp`, `slip.ctp`, `download.ctp`
**Next.js:** `/employees/resignations` page (F&F modal, `page.tsx` L336-457, L828-1130) +
`src/app/api/resignations/[id]/{eligibility,preview,approve,settlement-heads,finalize,slip}/route.ts`
+ `src/lib/settlement.ts`
**Date:** 2026-09-10
**Method:** source-code comparison. Scope = the "Process Full & Final" toolbar action of the
Remove Employee screen and everything it triggers, for the **GRTL (non-KWMT/DEMO/GLET/ABSG) path**.

---

## Headline

**This is already a careful, heavily-documented port** — most of legacy's non-tenant-specific
behaviour is reproduced, often with the legacy line numbers cited in the route comments and the
legacy quirks preserved verbatim (`leaveencash()`'s always-head[0] bug, the `holidays.status`
filter that differs between `setup()` and `removeemps()`, the leave-encash head `111` exclusion
from slip Net, etc.). The gaps are a small number of **deliberate scope cuts** plus **three real
functional reductions**, and one large **cross-cutting dependency** (the settlement engine can't
produce real numbers until the Attendance phase exists).

---

## Workflow shape

| | Legacy | Next.js |
|---|---|---|
| Screens | `setup.ctp` (preview + interactive leave-adjust + asset checkboxes) → `approves.ctp` (settlement table, editable OTHERS, "Generate Full and Final Slip") → slip screen with a separate **"Submit Termination"** button (`removeemps`) | One 2-step modal: **step 1** preview (`GET /eligibility` + `GET /preview`), **step 2** settlement (`PUT /approve`), then one "Generate Full and Final Slip" click chains `POST /settlement-heads` → `POST /finalize` → opens slip |
| Explicit commit clicks | 3 (Approve encashment / Save adjustment on setup; then Submit Termination on slip) | 1 (Generate Full and Final Slip = save heads + finalize) |
| Status writes | `approves()` writes nothing; `removeemps()` flips `emp_details.status = 2` | `PUT /approve` writes nothing; `POST /finalize` flips `emp_details.status = 2` + `Resignation_status = 'Completed'` |

Match on the important point: **approve recomputes, finalize terminates.**

---

## What is faithfully ported

| Legacy behaviour | Next.js | Notes |
|---|---|---|
| `setup()` gate: `last_approved_working_date > today` | `/eligibility` blocker 1 | `>` not `>=`, matches |
| `setup()` gate: already processed (`emp_settle_slip` ⨝ `emp_details.status = 2`) | `/eligibility` blocker 2 | |
| `setup()` gate: shift + salary structure both assigned | `/eligibility` blocker 3 | |
| `setup()` gate: attendance months processed / approved (`attendance_register`) | `/eligibility` blocker 4a / 4b | ported with the same SQL quirk (the "all employees" month count has no `emp_fkey` filter); resolves to no-op while `emp_detail_timeattandance` is empty |
| `workingattendnacedays()` leave-year check → "provide a Leave Year" | `hasCurrentLeaveYear()` → `preview.leaveYearWarning` | |
| Day-count math: `offs_actual = |weekoff_days_count_fn(emp, approved+1, submitted-1)| + holidays(submitted..approved)`; `resignation_period_working_days = notice - offs` | `computeDayCountStats()` | `setup()` filters `holidays.status = 1`; `removeemps()` does not — **both divergences preserved** (`computeDayCountStats` filters, `computeRemovalDays` doesn't) |
| `removeemps()`: `diffDays - offs` / `diffDays - (LOP + offs)`, `offs += 1` when `offs > 0` | `computeRemovalDays()` | LOP from `emp_detail_timeattandance` (→ 0 while empty) |
| `leaveencash($emp, $applied, 0)` — every quirk (loop always processes `head[0]`, inserted `salary_head_item_fkey = 0`, per-iteration `DELETE ... remarks='terminate'`, `is_approved='Y'`, `approved_by='0'`, `leave_encash_prc` called per iteration) | `commitLeaveEncashment()` | reproduced verbatim with an explanatory comment |
| `CALL final_settle_pay_prc(branch, month, emp, '0', '0', user, @err)` | `PUT /approve` | month derived from `last_approved_working_date`; present/encash days `'0'` — matches GRTL path |
| `approves()` formula re-eval Pass A (`emp_settle_slip`, `action='P'`) + Pass B (`emp_salary_slip` via the leaked `payroll_master_fkey`, ESI `ceil`, `Deduction` sign flip) | `reevalSettleSlipFormulas()` + `reevalSalarySlipFormulas()` | `eval()` → project's safe arithmetic evaluator; non-arithmetic `remarks` rows skipped |
| `approves()` leave-encashment amount: `emp_salary_structure.remarks` for the 'leave encashment' head, `eval()`, `* leave_balance`, round → `UPDATE emp_settle_slip ... type='ENCASHMENT'` | folded into `final_settle_pay_prc` + re-eval path | GRTL path |
| `get_emp_settle_slip()` 4-way split: SALARY additions / SALARY deductions / OTHERS additions / OTHERS deductions (by `type` and sign) | `/approve` response `additions|deductions|otherAdditions|otherDeductions` | |
| `save_heads()`: additions `SET salary_amount = <val>`, deductions `SET salary_amount = 0 - ABS(val)`, both `approved = 'Y'`, scoped `status='Y'` + employee | `POST /settlement-heads` | employee-scoped, matches |
| `Viewslip()` / `get_emp_full_and_final_settle_slip()` non-KWMT split, leave-encash head `111` excluded from slip Net, `slip.ctp` head-desc cleanup ("for the month…" trimmed, trailing `YYYY-MM` → `MM-YYYY`) | `GET /slip` | |
| `setup.ctp` "Balance Recovery" loan table + "Total Amount Balance" | `/preview` `balanceRecovery` | |
| Allocated-assets list | `/preview` `allocatedAssets` (`getAllocatedAssets`) | read-only (see gap 4) |
| `removeemps()` cascade: `status=2`, clear other employees' `emp_proff.attr1` manager refs, deactivate their HIERARCHY `emp_config`, approve `payroll_master`, settle `emp_settle_slip` + `leave_encashment_master` | `POST /finalize` | matches (minor `payroll_master` filter diff, below) |

---

## Deliberate scope cuts (GRTL-only tenant — not gaps)

- **KWMT / DEMO / GLET / ABSG variants**: `approves_metro` / `metro_slip` / `metro_download`,
  the editable **per-month** settlement table, the **ID Card** / **Notice Pay** / **Other
  Deduction** / **"Amount paid by the employee"** inputs in `save_heads()`, per-month
  `emp_settle_slip` "for the month-X" synthesis, `salary_process_prc` / `tax_salary_process_prc`
  reprocessing loop, ABSG `site_attendance_register` gate, and the **F&F Email Slip** toolbar
  button (`sendEmail()`, KWMT/DEMO/GLET only). All explicitly out of scope; GRTL never hits them.
- **Manager-routed queue**: consistent with every other approval workflow in this port.

---

## Real gaps

| # | Gap | Type | Detail |
|---|---|---|---|
| 1 | **No interactive leave-adjustment against notice period** | **Functional reduction** | `setup.ctp` gives the admin a `#leave_adjusted` number input, a live `#balance_after_adjustment` readback, a **"Save"** (`approveLeaveAdjustment()`) and a **"Final Encashment Leave Days" → Approve** (`approveencash()`) — i.e. the admin chooses how many encashable-leave days to apply against the notice shortfall, and that number becomes `approves($emp, $leaves)`'s `$leaves`. Next.js `PUT /approve` **hardcodes** `appliedLeaveDays = previewEncashableLeaveBalance()` (the full tile total) and forwards it to `commitLeaveEncashment()` — no input, no adjusted-balance readback, no separate approve. An admin who wants to encash fewer days than the raw balance cannot. |
| 2 | **"Resignation Period Present Days" is always 0** | **Functional gap (Attendance-blocked)** | `setup()` computes `resignaion_period_present_days` from `attendance_register` `FIELD1..FIELD32` day-status columns (~150 lines of pattern matching: `P/P`→1, `P/A`→0.5, …). `computeDayCountStats(pool, ctx, presantDays)` takes it as a **parameter** and both `/preview` and `/approve` pass `0`. Consequence: the "Balance Working Days" tile (`working − present`) always equals "Resignation Period Working Days". Genuinely blocked on the Attendance phase (the `FIELDx` data doesn't exist yet), but the tile reads a silent 0 meanwhile with no "not available yet" hint. |
| 3 | **`noticePay` computed & returned on the GRTL path** | **Over-port / noise** | Legacy computes `notice_pay` **only** inside `approves()`'s `if ($str_company_code == 'KWMT')` block — GRTL slips have no notice-pay line. Next.js `computeNoticePay()` (the KWMT formula: `annualCtc/12/noticePeriod` per-day × `max(notice − offs − workingDaysSettled − leaveBalance, 0)`, negated) runs unconditionally and `/approve` always returns `noticePay` in `SettlementResult`. Either dead payload or, if any UI surfaces it for GRTL, a line legacy never shows. Decide: drop it from the GRTL response, or gate it to KWMT if/when that tenant is in scope. |
| 4 | **Allocated-assets checkboxes not wired** | **Minor** | `setup.ctp` renders each allocated asset with a checkbox ("uncheck if you want to skip this"). `/preview` returns the asset list read-only; there's no per-asset skip/acknowledge that feeds a deduction or a return record. Low value — legacy's `setup()` itself only loads assets with `damaged_amout IS NOT NULL`, and loans/assets are documented as "HR accounts for these manually via a generic deduction". |
| 5 | **Settlement engine non-functional until Attendance exists** | **Cross-cutting dependency** | `final_settle_pay_prc` reads `attendance_register` / `emp_salary_slip` for the terminated month(s). The Attendance phase and the terminated-month payroll path that feed it aren't built, so on GRTL today `PUT /approve` will typically get "attendance not verified" back from the proc and produce an empty/partial settlement. The route degrades gracefully (surfaces `settlementMessage`, doesn't 500) — but "Process Full & Final" on GRTL does not yet yield a real slip. Not a resignation-module defect; note it wherever F&F readiness is tracked. |
| 6 | **`get_complete()` / `downloads()` not separately verified** | **Verify** | Legacy has `get_complete($emp_pkey)` (a completion render) and `downloads()` (`download.ctp` PDF). Next.js has `GET /slip` (JSON) + a client PDF (`src/lib/resignationSlipPdf.ts`). The toolbar "View Slip" maps to `Viewslip`, not `get_complete`; `get_complete` appears to be a dead/secondary path but wasn't traced end-to-end this pass. Confirm nothing user-facing depends on it. |
| 7 | **`finalize` `payroll_master` filter** | **Immaterial** | `removeemps()` approves `payroll_master` where `approved='N' AND month_year > '2018-04'`; `/finalize` uses `approved <> 'Y'` with no month bound. No effect on GRTL (all data post-2018). |

---

## Recommended actions

- **Gap 3** (quick): drop `noticePay` from the GRTL `/approve` response (or gate to KWMT). No UI
  work if nothing renders it.
- **Gap 1**: decide whether admin control over encashed leave days is required for GRTL. If yes,
  add a `leaveDays` input on step 1 that overrides `previewEncashableLeaveBalance()` when set, and
  pass it through `PUT /approve` → `commitLeaveEncashment()`. If no, record it as an accepted
  reduction.
- **Gap 2**: when the Attendance phase lands, wire `resignationPeriodPresentDays` from
  `attendance_register` `FIELDx` (port `setup()`'s pattern lists). Until then, show "—"/"pending
  attendance" instead of `0` on that tile so it doesn't read as a real zero.
- **Gaps 4, 6**: verify/triage; low priority.
- **Gap 5**: track under Attendance-phase readiness, not here.
