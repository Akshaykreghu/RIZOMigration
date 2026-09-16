# Variable Upload — Next.js Migration Plan

**Legacy:** `Controller/VariableController.php` (662 lines, 9 actions) + model `Model/EmployeeVariableUpload.php` → table `emp_variables_upload`.
Views: `View/Variable/index.ctp` (list screen, menu title **"Variable Upload"**), `View/Variable/form.ctp` (New/Edit modal, title "Variable Salary Upload"). `variableupload.ctp` is a 0-byte stub — unused.
**Legacy menu:** under the user_group 1 "Salary Processing" menu (`SalaryProcessing/index`).
**Next.js target:** new route `/payroll/variable-upload`, new nav item under the **Payroll** section of `rizo/src/lib/navItems.ts` (`adminOnly: true`).
**Date:** 2026-09-08
**Companion doc:** this file is also the scope-gap analysis (no separate comparison doc).

**Hard constraint:** no schema changes. No `CREATE` / `ALTER` / migration, no new columns or indexes.
The port only does `INSERT` / `UPDATE` / `status = 0` soft-deletes on the existing `emp_variables_upload`
table (exactly as legacy does) and reads from existing `salary_head_items`, `salary_heads`,
`payroll_master`, `emp_details`, `emp_ctc_transaction`, `emp_salary_structure`, `user_credentials`.

**Status:** implemented 2026-09-08 (tsc + eslint clean; routes resolve on the dev server; not yet
live-DB-verified). Files added:
`rizo/src/lib/variableUpload.ts`, `rizo/src/app/api/payroll/variable-upload/{route.ts,head-items/route.ts,template/route.ts,upload/route.ts}`,
`rizo/src/app/(dashboard)/payroll/variable-upload/page.tsx`, nav item in `rizo/src/lib/navItems.ts` (Payroll section).
Deferred from this pass: server-side paging in the list UI (currently first 50 rows + "narrow filters" hint);
GLET/ABSG head-office branch scoping (screen is admin-only). Still to do: the live-DB verification in Phase 6.

Original gap analysis — the only existing references to `emp_variables_upload` in `rizo/` were *downstream consumers*:
- `rizo/src/lib/payroll.ts:8` — note that `emp_variables_upload.month_year` is `'MM-YYYY'`, and `monthYearToEvuFormat()` already converts `'YYYY-MM'` → `'MM-YYYY'`.
- `rizo/src/app/api/payroll/reprocess/route.ts:48` — `UPDATE emp_variables_upload SET status = 0 …` (resets the machine-generated `head_type='Fixed'` rows on reprocess).
- `rizo/src/lib/reports.ts:336` — mentions a different, unbuilt `emp_variable_pay_upload` config report; **not** this feature.

There is no screen, API, `lib/*`, or nav entry to create / import / list / edit / delete variable-pay rows.

---

## What the feature does

Lets payroll admins record **per-employee, per-month one-off pay components** (bonus, incentive, ad-hoc
addition/deduction) that are *not* part of the fixed salary structure. Each row targets a
`salary_head_items` entry whose head has `head_occurance = 'variable'`, `item_type = 'Manually'`,
`value = 'Y'`, `status = 1`.

**How payroll consumes it** (`calculate_salary_main_prc`, `mypayrol_mpm121.sql`):
- **BLOCK3** (line ~1342): `SELECT emp_fkey, salary_head_item_fkey, salary_head_item_desc, SUM(uploaded_amount), head_operator, head_type, item_part FROM emp_variables_upload WHERE status = 1 AND month_year = DATE_FORMAT(concat(pmonth,'-01'),'%m-%Y') GROUP BY …` — every `status = 1` row for the month is summed into that head and posted to the salary slip. **Multiple rows for the same (emp, head, month) are additive** — this is by design (that is how "upload again to top up" works).
- **BLOCK2** (line ~1332): payroll itself *inserts* rows here with `head_type = 'Fixed'`, `action = 'Salary Processing'` for fixed components that live in the variable section of a salary structure. Reprocess deletes them (`status = 0`, `lcase(head_type) = 'fixed'`). **The migrated UI must not let users edit/delete these machine rows.**

---

## Table shape — `emp_variables_upload`

| column | type | notes |
|---|---|---|
| `emp_variables_upload_pkey` | int PK AI | |
| `emp_fkey` | int | `emp_details.emp_pkey` |
| `salary_head_item_fkey` | int | `salary_head_items.salary_head_item_pkey` |
| `month_year` | varchar(20) | **`'MM-YYYY'`** (e.g. `'11-2019'`) — not ISO |
| `salary_head_item_desc` | varchar(100) | snapshot of `salary_head_items.item` at save time |
| `structure_det_value` | float NULL | only set by BLOCK2 machine rows; leave NULL from the UI |
| `uploaded_amount` | float NOT NULL | the entered amount (legacy allows `>= 0`) |
| `head_operator` | varchar(30) | snapshot of `salary_heads.head_operator` (`'Addition'` / `'Deduction'`) |
| `head_type` | varchar(30) | snapshot of `salary_head_items.item_type` — `'Manually'` for UI rows |
| `item_part` | varchar(30) | snapshot of `salary_head_items.item_part` (e.g. `'Direct'`) |
| `creation_date` | datetime | DB default `CURRENT_TIMESTAMP` |
| `created_by` | varchar(50) NOT NULL | login user id / email (`session.user.loginUserId`) |
| `remarks` | varchar(500) NULL | |
| `action` | varchar(30) NULL | `'uploaded by form'`, `'uploaded by excel'`, or `'Salary Processing'` (machine) |
| `status` | int | `1` active, `0` soft-deleted |

`CHARSET=latin1` — keep inserts ASCII-clean.

---

## Scope ledger (legacy action → status → plan item)

| Legacy action | Feature | Status | Plan item |
|---|---|---|---|
| `index` | List screen + Month / Salary-Head / Branch / Employee filters, `arr_headitems` dropdown source | ❌ missing | **VU-3** |
| `employeelistvariable` | jqGrid data feed for the list (joined `emp_details`, filters, paging) | ❌ missing | **VU-2.1**, VU-3 |
| `form($id)` | New/Edit modal — load one row + `arr_headitems` | ❌ missing | **VU-3.2** |
| `VariableSave` | Insert/update one row (resolve head snapshot cols; payroll-processed guard) | ❌ missing | **VU-1.2**, VU-2.2 |
| `deleteEmployees` | Soft-delete selected rows (`status = 0`) | ❌ missing | VU-2.3 |
| `downloadvariableuploadform($salaryhead,$branch,$emp_pkey)` | Excel **template** (User ID / Emp Company ID / Name / Gross / Monthly CTC / Amount / Remarks) | ❌ missing | **VU-4.1** |
| `uploadandsaveempvar($salary_head_item,$month)` | Excel **bulk import** — 1 row per employee, append rows | ❌ missing | **VU-4.2** |
| `variableupload()` | dead stub | n/a | — |
| GLET/ABSG HO branch restriction (user_group 2 path) | multi-tenant branch scoping | ⏭ out of scope | note only (screen is `adminOnly`) |

---

## Phase 1 — Data layer: `rizo/src/lib/variableUpload.ts`

### VU-1.1 — `listVariableUploads(pool, filters)`
Filters: `month?` (`'YYYY-MM'` from the UI → convert to `'MM-YYYY'` with `monthYearToEvuFormat`), `empFkey?`,
`branch?` (`emp_details.branch_code`), `salaryHeadItemFkey?`. Query mirrors legacy `employeelistvariable`:

```sql
SELECT vu.emp_variables_upload_pkey, vu.emp_fkey,
       CONCAT(ed.first_name,' ',ed.last_name) AS emp_name,
       vu.month_year, vu.salary_head_item_desc, vu.uploaded_amount,
       vu.head_operator, vu.head_type, vu.item_part, vu.remarks, vu.action
FROM emp_variables_upload vu
JOIN emp_details ed ON ed.emp_pkey = vu.emp_fkey
WHERE vu.status = 1
  [AND vu.month_year = ?] [AND vu.emp_fkey = ?]
  [AND ed.branch_code = ?] [AND vu.salary_head_item_fkey = ?]
ORDER BY vu.creation_date DESC
LIMIT ? OFFSET ?
```
Return rows + total count. Add a derived `is_machine_row` = `action = 'Salary Processing'` (or `LOWER(head_type) = 'fixed'`) so the UI can lock those.

### VU-1.2 — `getVariableHeadItems(pool)`
The dropdown source — copy legacy's query verbatim:

```sql
SELECT shi.salary_head_item_pkey, shi.item, shi.item_type, shi.item_part, shi.head_fkey
FROM salary_head_items shi
WHERE shi.status = 1 AND shi.item_type = 'Manually'
  AND shi.value = 'Y'
  AND shi.head_fkey IN (
    SELECT head_pkey FROM salary_heads
    WHERE LCASE(head_occurance) = 'variable' AND status = 1)
ORDER BY shi.item ASC
```

### VU-1.3 — `resolveHeadSnapshot(pool, salaryHeadItemFkey)`
Returns `{ item, item_type, item_part, head_operator }` — `salary_head_items` row joined to
`salary_heads.head_operator` via `head_fkey`. Used by save + import to fill the denormalized columns
exactly as legacy `VariableSave` does.

### VU-1.4 — `isPayrollProcessedForVariable(pool, empFkey, monthYearMMYYYY)`
Legacy `VariableSave` blocks on:
```sql
SELECT 1 FROM payroll_master
WHERE emp_fkey = ? AND month_year = ?  -- month_year here is 'YYYY-MM'
  AND action IN ('Approved','Processed')
```
Note legacy converts the form's `'MM-YYYY'` to `'YYYY-MM'` (`date("Y-m", strtotime('01-'.$month_year))`)
for this check because `payroll_master.month_year` is ISO. Keep both formats straight. This is **distinct**
from `payroll.ts:isPayrollAlreadyProcessed()` (which checks `emp_salary_slip`); replicate legacy's
`payroll_master` check here, message: **"Payroll Already Processed, Please Remove it before upload Variable"**.

### VU-1.5 — `createOrUpdateVariableUpload(pool, input, user)` / `softDeleteVariableUploads(pool, ids)`
- Create/update: resolve snapshot (VU-1.3), run the guard (VU-1.4), then INSERT (or UPDATE when
  `emp_variables_upload_pkey` given). Set `action = 'uploaded by form'`, `status = 1`,
  `created_by = user.loginUserId`. `uploaded_amount`: accept `>= 0` (legacy does).
- Delete: `UPDATE emp_variables_upload SET status = 0 WHERE emp_variables_upload_pkey IN (?)` — and
  **refuse machine rows** (`action = 'Salary Processing'`) even though legacy does not guard this.

---

## Phase 2 — API routes

### VU-2.1 — `GET /api/payroll/variable-upload`
Query params `month`, `empFkey`, `branch`, `salaryHeadItemFkey`, `page`, `rows`. Returns `{ data, total }`.
Also expose the head-item list: `GET /api/payroll/variable-upload/head-items` → `getVariableHeadItems`.
Session guard: `session.user.userGroup === 1` (mirror `attendance/upload/route.ts`).

### VU-2.2 — `POST /api/payroll/variable-upload`
Body: `{ emp_variables_upload_pkey?, empFkey, salaryHeadItemFkey, month /* YYYY-MM */, amount, remarks }`.
400 with the legacy message on the payroll-processed guard; 200 `{ success: true }` on save.

### VU-2.3 — `DELETE /api/payroll/variable-upload`
Body `{ ids: number[] }`. Soft-delete, skipping machine rows (report them back in `errors`).
*(Legacy JS bug for reference: the multi-row delete handler concatenates the wrong field name
`emp_ctc_upload_pkey` for rows after the first, so only the first id reliably deletes — the port
sends all ids correctly.)*

---

## Phase 3 — UI: `/payroll/variable-upload/page.tsx`

Single screen (legacy `index.ctp` + `form.ctp` as a dialog). Model it on `rizo/src/app/(dashboard)/advances/page.tsx`.

### VU-3.1 — Filter toolbar
`<input type="month">` (Month) + Salary-Head `<select>` (from head-items endpoint) + branch `<select>`
(reuse the branches source used by `advances`/`attendance`) + `EmployeeSearch`. Keep the React Query
cache key on all four.

### VU-3.2 — List + New/Edit dialog
Columns: Employee · Salary Head · Amount · Operator · Type · Item Part · Month · Remarks (matches
`index.ctp` grid). Row actions: Edit / Delete — **disabled when `is_machine_row`**, with a tooltip
("generated by payroll processing"). "New" opens the dialog:
- Employee (`EmployeeSearch`, required)
- Month (`<input type="month">`, required; default = next month, as legacy does)
- Salary Head Item (`<select>`, required)
- Amount (number, required, `>= 0`)
- Remark (textarea, optional)
Submit → `POST`; surface the payroll-processed 400 inline.

---

## Phase 4 — Excel template + bulk import

### VU-4.1 — `GET /api/payroll/variable-upload/template`
Params `salaryHeadItemFkey` (required — legacy alerts "select head item" otherwise), `branch?`, `empFkey?`.
Build with `exceljs` (as other template routes do). Columns for admin (user_group 1):
`User ID | Employee Company ID | Employee Name | Gross Salary | Monthly CTC | Amount | Remarks`, one
row per active employee (optionally branch/employee-filtered). Populate Gross = `emp_ctc_transaction.emp_anual_ctc/12`,
Monthly CTC = `SUM(emp_salary_structure.structure_det_value)` where `head_operator='ADDITION'` and
`LCASE(head_type) NOT IN ('manually','variable')` and `end_date_effective IS NULL` (legacy formula).
Filename `<company_code>_variable.xlsx`.

### VU-4.2 — `POST /api/payroll/variable-upload/upload`
`multipart/form-data`: the `.xlsx` file + `salaryHeadItemFkey` + `month` (`'YYYY-MM'`). Parse with `XLSX`
(as `attendance/upload/route.ts`). Per data row:
- read `User ID`, `Amount`, `Remarks`; skip rows with blank `User ID` (legacy `continue`s).
- skip rows with empty `Amount` (legacy: `!empty($amount)` — note bulk path is stricter than the form's `>= 0`).
- resolve `emp_fkey` from `user_credentials.user_id`.
- resolve head snapshot (VU-1.3) once, outside the loop.
- **INSERT a new row** (legacy appends — does not upsert), `action = 'uploaded by excel'`,
  `month_year = monthYearToEvuFormat(month)`.
- **Deliberate addition over legacy:** run the VU-1.4 payroll-processed guard per row and push a
  skipped-row error instead of silently writing into a locked month — same discipline applied in the
  attendance-upload port.
Return `{ success, imported, errors: [{ row, message }] }`.

---

## Phase 5 — Navigation & permissions

### VU-5.1 — nav item
`rizo/src/lib/navItems.ts`, Payroll section (`slug: 'payroll'`, already `adminOnly`), add:
```ts
{ label: 'Variable Upload', href: '/payroll/variable-upload',
  description: 'Record one-off variable pay (bonus, incentive, ad-hoc additions) per employee per month.' },
```

### VU-5.2 — access
All routes: `session.user.userGroup === 1`. The GLET/ABSG head-office branch-scoping in legacy's
`user_group == 2` path is out of scope (this menu is admin-only in the port).

---

## Phase 6 — Verification

- curl each route (list/filter, create, create into a `payroll_master` Approved month → expect 400,
  edit, soft-delete, delete a machine row → expect refusal, template download opens in Excel, bulk
  upload with 1 good + 1 bad + 1 locked row → `imported: 1`, 2 errors).
- End-to-end: create a variable row for an employee + month, run Process Payroll for that branch/month,
  confirm the amount lands on the salary slip under the right head and that two rows for the same head
  sum (BLOCK3 behavior).
- Reprocess that payroll row, confirm only the `head_type='Fixed'` machine rows were reset and the
  manual `'Manually'` rows survive (they should — `reprocess/route.ts` only touches fixed).

---

## Key gotchas

1. **`month_year` is `'MM-YYYY'`** in this table, `'YYYY-MM'` everywhere in the new app and in
   `payroll_master`. Convert at every boundary — `monthYearToEvuFormat()` already exists in `payroll.ts`.
2. **Denormalized snapshot columns** (`salary_head_item_desc`, `head_operator`, `head_type`, `item_part`)
   must be resolved from `salary_head_items` + `salary_heads` at save/import time and written literally —
   payroll's BLOCK3 reads them straight off this table, it does not re-join.
3. **Additive, not upsert.** Legacy never dedupes; payroll `SUM()`s. Match that — do not "helpfully"
   replace an existing row on re-upload unless a pkey is passed.
4. **Machine rows** (`action = 'Salary Processing'` / `head_type = 'Fixed'`) appear in the same list
   (`status = 1`). Lock them from edit/delete in the port even though legacy doesn't.
5. **Two different "payroll already processed" checks** — the form path hits `payroll_master`
   (`action IN ('Approved','Processed')`); do not substitute the `emp_salary_slip` check from `payroll.ts`.
6. **Bulk import has no month guard and no amount-`>=0` allowance in legacy** — port adds the month
   guard deliberately; keep the stricter non-empty `Amount` rule for the sheet path.
7. `CHARSET=latin1` on the table; `salary_head_item_desc` sample data has trailing spaces (`'Bonus    '`) —
   don't trim on read if you compare against payroll output, or trim consistently everywhere.
8. `structure_det_value` stays `NULL` for UI/Excel rows — it is a BLOCK2-only column.
