# Employee Loans — Next.js Migration Completion Plan

**Legacy:** `Controller/EmployeeLoanController.php` (2,241 lines, 31 actions) + `Controller/EmployeeLoanReportsController.php` (1,217 lines)
Views: `index.ctp`, `form.ctp`, `viewloan.ctp`, `update.ctp`, `emi_upload.ctp`, `ctcupload.ctp`, `upload.ctp`, `download.ctp`
**Next.js target:** `/loans` (routed in `rizo/src/lib/navItems.ts:119`) + `/reports/loans-advances`
**Date:** 2026-09-08
**Companion doc:** this file also serves as the scope-gap analysis (no separate comparison doc).

**Status of what already shipped (Phase 7, 2026-07-22):** the single-loan transactional core is
complete and curl-verified — `createLoan` (+ full EMI schedule generation), `listLoans`,
`payLoanAmount` (additional payment), `markLoanCompleted`, soft-delete, and the detail-mode Loan
Report. Files: `rizo/src/lib/loans.ts`, `rizo/src/app/api/loans/**`, `rizo/src/app/(dashboard)/loans/page.tsx`,
`rizo/src/lib/loanAdvanceReports.ts`.

**What this plan covers:** the operational tooling around a loan that was not ported — per-loan
ledger view, EMI month-transfer, per-loan PDF/Excel, the 3-day edit window, payroll-processed
guards, and the deferred bulk-upload paths.

**Implementation status:**
- **Phase 0 (2026-09-08) — done, not yet live-verified.** `markLoanCompleted` settles `amount_paid`;
  `payLoanAmount` takes `userRemarks` + the three `amount_pay()` guards via `LoanValidationError`;
  `isPayrollAlreadyProcessed` moved to `lib/payroll.ts`; `/loans` list filter toolbar added.
- **Phase 1 (2026-09-08) — done, not yet live-verified.** `getLoanDetail()`, `GET /api/loans/[id]`,
  `/loans/[id]` ledger page; list rows link through, pay/complete moved off the list.
- Phases 2–7: not started.

---

## Scope ledger (legacy action → status)

| Legacy action(s) | Feature | Status | Plan item |
|---|---|---|---|
| `employeeloansave` | Create loan + generate EMI schedule | ✅ done | — |
| `employeeloanlist` | Loan list | ✅ done (filters API-only) | EL-0.3 |
| `amount_pay` | Additional lump-sum payment | ✅ done (missing `user_remarks`, over-balance guard) | EL-0.2, EL-0.4 |
| `completed` | Mark loan completed | ✅ done (missing `amount_paid = loan_emi` write) | EL-0.1 |
| `deleteEmployeeloan` | Soft-delete | ✅ done | — |
| `jsons` / `jsons_form` | Employee autocomplete | ✅ done (`EmployeeSearch`) | — |
| `generateemployeeeloan` | Loan Report (detail) | ✅ done | EL-5 (summary mode) |
| `viewloan`, `update` | **Per-loan EMI ledger / detail screen** | ❌ missing | **EL-1** |
| `update_transfer`, `checkmonth`, `getEmi` | **EMI month-transfer / deferral** | ❌ missing | **EL-2** |
| `downloads` | Per-loan PDF export | ❌ missing | EL-3 |
| `downloadexcels` | Per-loan Excel export | ❌ missing | EL-3 |
| `form` (edit branch), `update($id)` | Edit loan within 3 days of creation | ❌ missing | EL-4 |
| `salarycheck`, `payroll_check_amount_pay` | "Payroll already processed" guard | ❌ missing | EL-0.4 |
| `getLoanBalance` | Loan-balance lookup endpoint | ❌ missing | EL-6 |
| `generatesummaryreport` | Loan Report — summary mode | ❌ missing | EL-5 |
| `uploadandsaveempemi`, `downloademploanformat`, `emi_upload`, `employeelist`, `deleteEmployeesemi` | **Bulk EMI Excel upload** + template | ❌ deferred | EL-7 |
| `uploadandsaveempctc`, `ctcupload`, `downloadempctcformat` | **Bulk CTC Excel upload** (lives in this controller) | ❌ deferred | EL-7 |
| `emptaxationdetails`, `getcontactsbysite`, `getEmi`, `salarycheck`, `employeeemiloansave` | AJAX helpers for the deferred screens | ❌ deferred | EL-7 |

---

## Phase 0 — Correctness fixes to shipped code (small, do first)

Bundle as one PR. Each is a few lines; all are exercised by the Phase 1 detail view, so land them
together.

### EL-0.1 — `markLoanCompleted` must also settle the schedule rows
`rizo/src/lib/loans.ts` `markLoanCompleted()` only sets `emp_loan.is_completed='Y'` +
`emp_loan_info.paid_status='P'`. Legacy `completed()` additionally runs
`UPDATE emp_loan_info SET amount_paid = loan_emi WHERE loan_pkey = ? AND status = 1`.
Without it, the detail view's "paid" / "balance" / "completion %" (all `SUM(amount_paid)`-based)
under-report on a force-completed loan, and the Loan Report shows a non-zero balance on a closed
loan. **Fix:** add the `amount_paid = loan_emi` update. Keep the `WHERE status = 1` filter legacy
uses.

### EL-0.2 — Capture `user_remarks` on additional payment
Legacy `amount_pay()` reads `remarks` from the POST body and stores it as `emp_loan_info.user_remarks`
on the payment row (`update.ctp` has a required remarks `<textarea>`). Current port sends `amount`
only. **Fix:** add `remarks` to `POST /api/loans/[id]/pay` body, thread it into `payLoanAmount()`,
write it to `user_remarks` on the inserted `'S'` row. Add the field to the pay UI (Phase 1 moves
that UI to the detail page — coordinate).

### EL-0.3 — Surface the list filters
`listLoans()` / `GET /api/loans` already accept `empFkey`, `branch`, `month`; the page renders no
controls for them. Legacy `employeeloanlist` has an employee + branch + month toolbar. **Fix:** add
an `EmployeeSearch` + branch `<select>` (reuse the branches source used elsewhere) + `<input type="month">`
to `loans/page.tsx`, pass them as query params, key the React Query cache on them.

### EL-0.4 — Additional-payment guards
Legacy `amount_pay()` blocks two cases the port does not:
1. **Over-balance:** `(loan_amount − (amount_paid + emi_this_month)) < amount` → "Total Additional
   Payment exceed Balance Amount".
2. **Payroll processed this month:** `emp_salary_slip` row for `(emp_fkey, current month, end_date_effective IS NULL)`
   → "Payroll Already Processed". Reuse `isPayrollAlreadyProcessed()` already in
   `rizo/src/lib/advances.ts:25` (move it to a shared `lib/payroll.ts` or import cross-module).
**Fix:** add both checks to `payLoanAmount()`, return a 400 with the legacy message text. Legacy
treats #2 as a hard block here (unlike advances, where it is advisory) — match that.

---

## Phase 1 — Per-loan detail / EMI ledger view  ← highest value

Ports `viewloan.ctp` (read-only ledger + progress) and `update.ctp` (same, plus the inline
additional-payment box and "Update Loan as completed" button). One screen in the port.

### EL-1.1 — `getLoanDetail(pool, loanPkey)` in `lib/loans.ts`
Returns:
- **master:** employee name, `loan_amount`, `tenure`, `intrest_rate`, `emi_start_month`, `emi_end_month`,
  `remarks`, `is_completed`, `created_date`.
- **schedule:** `emp_loan_info` rows for the loan, `status=1`, `ORDER BY loan_month` (legacy order).
- **derived per row** (mirror `viewloan.ctp` lines 84–113 exactly):
  running `opening_balance` (first row seeded from `loan_amount`), `closing_balance = opening − amount_paid`,
  carry `closing` into next `opening`; **skip** rows where `loan_emi = 0 AND paid_status IN ('A','P')`
  (transferred/absorbed months) so serial numbers match legacy.
- **totals:** `paid = SUM(amount_paid)`, `balance_amount = loan_amount − paid` (floor at 0; legacy
  also treats `<= 12` as 0 — replicate that rounding quirk or document dropping it),
  `completion_pct = ceil(paid / loan_amount * 100)` capped at 100.

### EL-1.2 — `GET /api/loans/[id]`
Auth guard identical to the other loan routes (`session.user.userGroup === 1`). 404 when the loan
is missing or `status = 0`.

### EL-1.3 — `/loans/[id]/page.tsx`
- Header card: employee, amount, tenure, interest, start month, remarks.
- "Amount to be paid" + progress bar (`completion_pct`), matching `viewloan.ctp`.
- Schedule table: Sl No, Month (`MM-YYYY`), Opening Balance, EMI, Paid Amount, Closing Balance,
  Monthly Status (`remarks`), User Remarks (`user_remarks`).
- Inline actions (from `update.ctp`), only when `balance_amount > 0`:
  - **Additional payment:** amount + remarks (required) → `POST /api/loans/[id]/pay` (EL-0.2/0.4).
  - **Update Loan as completed** → `POST /api/loans/[id]/complete`.
- PDF / Excel buttons (Phase 3).
- EMI-transfer panel (Phase 2), hidden when `completion_pct > 90` (legacy `update.ctp:101`).

### EL-1.4 — Wire the list
`loans/page.tsx` row → navigate to `/loans/[id]`. Remove the per-row pay/complete/delete icon
cluster from the list (or keep delete only) now that pay + complete live on the detail page — match
legacy, where the list only links through to the detail screen.

---

## Phase 2 — EMI month-transfer

Ports `update_transfer()` + `checkmonth()`. Lets HR move a scheduled EMI from one month to a later
one (e.g. employee on unpaid leave that month).

### EL-2.1 — `transferEmi(pool, loanPkey, takenMonth, affectedMonth, userId)`
Guards (return `{ success: 0, msg }` with legacy's exact text):
- `takenMonth` must be a real `status=1` schedule row → "Please Select a Month Within The Loan Period".
- `takenMonth` must have `paid_status='A'` (unpaid, still scheduled) → same message.
- No open `emp_salary_slip` for `(emp_fkey, takenMonth)` → "Payroll Already Processed on Taken Month".
- No open `emp_salary_slip` for `(emp_fkey, affectedMonth)` → "Payroll Already Processed on Affecting Month".
- No `paid_status='P' AND emi_transfer!='Y'` row at `takenMonth` → "Loan Already Processed From Taken Month".

Mutation (mirror `update_transfer` lines 636–653):
1. `UPDATE emp_loan_info SET loan_emi=0, amount_to_paid=0, principle=0, paid_status='P', emi_transfer='Y',
   remarks='EMI Transferred from {taken} to {affected}' WHERE emp_loan_info_pkey = {taken row} AND status=1`.
2. `INSERT` a new `emp_loan_info` row at `affectedMonth` copying `loan_emi` / `amount_to_paid` /
   `principle` from the taken row, `remarks='EMI Transferred (from {taken} to {affected}) Rs.{emi}'`,
   `created_by = userId`. (Legacy's `else` branch that merges into an existing affected-month row is
   commented out — do **not** port it.)

Wrap in a transaction (legacy does not, but it issues two writes that must both land).

### EL-2.2 — `POST /api/loans/[id]/transfer-emi`  body `{ takenMonth, affectedMonth }`

### EL-2.3 — UI on the detail page
- **From** `<select>`: schedule months with `loan_emi != 0 AND paid_status='A'` (legacy `$loan_months`).
- **To** `<select>`: the other such months + one month past the last scheduled month
  (`checkmonth()` logic — port as a pure client-side derivation from the schedule, no extra
  endpoint needed).
- Client validation: both required, must differ.
- Hidden entirely when `completion_pct > 90`.

---

## Phase 3 — Per-loan PDF + Excel export

Ports `downloads()` (PDF) and `downloadexcels()` (Excel). Legacy generates the file server-side;
the Reports module in this project does it client-side via `rizo/src/lib/reportExport.ts`.

**Recommendation:** client-side, consistent with Reports. Add two buttons to `/loans/[id]` that
build a loan-statement layout (header block: employee, amount, tenure, interest, start month,
balance, completion % + the schedule table) and call `jsPDF`/`autoTable` and `xlsx` directly. If
byte-parity with the legacy file is required by the client, add a `GET /api/loans/[id]/export?format=pdf|xlsx`
server route instead — but that is a larger lift for little user benefit; get a product decision
before choosing it.

---

## Phase 4 — Edit an existing loan (3-day window)

Ports `form()`'s edit branch + `update($loan_pkey)`'s save. Legacy allows editing a loan only while
`created_date` is within 3 days **and** no EMI has been paid; past that the form shows
"This is not editable".

### EL-4.1 — `updateLoan(pool, loanPkey, input, userId)`
- Load the loan. Reject (409, "Loan is no longer editable") if `NOW() - created_date > 3 days` OR
  `SUM(amount_paid) > 0` OR `is_completed='Y'`.
- Update `emp_loan` header, **delete** the existing `status=1` `emp_loan_info` rows (hard delete is
  what legacy effectively does by regenerating), regenerate the schedule via the same loop as
  `createLoan()` (factor the schedule-generation body of `createLoan` into a private
  `generateSchedule(conn, loanPkey, input, userId)` and call it from both).

### EL-4.2 — `PUT /api/loans/[id]`

### EL-4.3 — UI
- "Edit" action on the list row / detail header, enabled only inside the window (compute
  client-side from `created_date` + `paid` returned by `getLoanDetail`).
- Reuse the create form component, pre-filled; on submit call `PUT`.

---

## Phase 5 — Loan Report: summary mode  (bundle into a Reports pass)

`generatereport()` dispatches detail vs. summary; only detail is ported. `generatesummaryreport()`
returns per-branch (or per-criteria-group) aggregates: loan count, total sanctioned, total paid,
total outstanding. **Fix:** add a "Summary / Detail" toggle to `/reports/loans-advances` for the
Loan type, a `mode` param to `POST /api/reports/loans-advances`, and a `generateLoanSummary()` in
`lib/loanAdvanceReports.ts` doing the `GROUP BY` aggregation. Low priority; batch with the other
outstanding Reports summary modes.

---

## Phase 6 — `getLoanBalance` endpoint

`getLoanBalance($emp_pkey)` returns an employee's total outstanding loan balance. In-app the only
current consumer of "employee's loans" is resignation settlement, which already has its own
`getLoansAndAssets()` (`rizo/src/lib/settlement.ts`). **Action:** grep legacy for
`getLoanBalance` callers; if the only ones are screens already ported with their own logic, mark
this **won't-port** and document why. Otherwise add `GET /api/loans/balance?empFkey=` →
`SUM(loan_amount) − SUM(amount_paid)` over active loans. Est. 30 min including the grep.

---

## Phase 7 — Bulk uploads  (deferred — confirm with product before starting)

Three legacy screens, ~700 lines combined, all Excel-round-trip:
- **Bulk EMI upload:** `uploadandsaveempemi()` (lines 2012–2198), `downloademploanformat()` (template),
  `emi_upload.ctp`, `employeelist()`, `deleteEmployeesemi()`.
- **Bulk CTC upload:** `uploadandsaveempctc()` (lines 111–426), `ctcupload()`,
  `downloadempctcformat()`. This is really a *salary/CTC* bulk-load feature that happens to live in
  the loan controller and overlaps the CTC-upload work tracked under the Salary Structure /
  Bulk Policy plans — **do not build it here without reconciling with those.**
- `uploadandsaveempctc()` contains the buggy inlined copy of the EMI-generation loop noted in
  `PROGRESS.md` (the "0% interest static balance" anomaly). If ported, port the **corrected**
  `lib/loans.ts` `generateSchedule()`, not legacy's inline copy.

**Recommendation:** keep deferred. If required, scope as its own plan with a decision on the
Excel-parsing library, template format, per-row validation, and error reporting (the same shape as
the other bulk-upload screens in the project).

---

## Recommended sequence & rough effort

| Order | Item | Effort | Notes |
|---|---|---|---|
| 1 | Phase 0 (EL-0.1 – 0.4) | 0.5 day | one PR; unblocks Phase 1 math |
| 2 | Phase 1 (detail view) | 2–3 days | the main gap; new route + page + lib fn |
| 3 | Phase 2 (EMI transfer) | 1.5–2 days | guard-heavy; needs live payroll data to test |
| 4 | Phase 3 (PDF/Excel) | 1 day | client-side, reuse `reportExport.ts` |
| 5 | Phase 4 (edit window) | 1 day | factor `generateSchedule` out of `createLoan` first |
| 6 | Phase 6 (`getLoanBalance`) | 0.5 day | mostly a grep + decision |
| 7 | Phase 5 (report summary) | 0.5 day | fold into next Reports pass |
| — | Phase 7 (bulk uploads) | separate plan | deferred pending product |

**Total for full parity minus bulk uploads: ~8–10 dev-days.**

---

## Testing (per existing project convention)

- Curl each new endpoint against live GRTL data inside a transaction with rollback; delete any
  test `emp_loan` / `emp_loan_info` rows afterward. Admin session via the standing
  temporary-password-override-on-`GRTLADMIN01` technique, hash restored exactly afterward.
- Phase 1: cross-check the running opening/closing balance and completion % by hand against a real
  multi-EMI loan; verify a force-completed loan (EL-0.1) now shows 100% / zero balance.
- Phase 2: test all five guard paths; verify the taken-month row goes `emi_transfer='Y'` and a new
  row appears at the affected month with the same EMI; verify a payroll-processed month is blocked.
- Phase 4: verify the 3-day / paid-EMI / completed rejections; verify schedule regeneration matches
  a fresh `createLoan` for the same inputs.
- `npx tsc --noEmit`, `eslint` on touched files, and `next build` clean before each PR.
- Update `PROGRESS.md` Phase 7 section with an implementation-log entry per phase.
