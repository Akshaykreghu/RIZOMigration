# Process Payroll — Formula/Remarks Backfill: Migration Plan

> **STATUS: NOT STARTED — gap identified 2026-09-14, confirmed live against `mypayrol_mpm121`
> (read-only checks only, no writes made).**
>
> Sibling doc: `Process_Payroll_Legacy_vs_NextJS_Report.md` (an earlier, UI-level comparison from
> 2026-08-11 that predates this finding and doesn't cover it — that report focuses on the
> seed/list bug; this plan covers a separate, code-level gap inside the processing step itself).

---

## 0. Scope

**In scope:** `Controller/PayrollController.php::processpayroll()`, specifically the block at
lines **1019-1193** (`// Edited by Akshay on 2-9-2025`), plus two smaller gaps found in the same
comparison pass (§4). Target: `rizo/src/lib/payroll.ts` (`processPayrollEmployee`) and
`rizo/src/app/api/payroll/process/route.ts`.

**Out of scope (confirmed intentional, not a gap):** the `if (in_array($company_code,
$specialCompanies))` block at lines 871-875 (else branch → `salaryProcessPrc`) and 910-1014
(`taxSalaryProcessPrc` + its ESI-specific formula handling). These only run for the
special-tenant list (`HRBL, KWMT, AIMA, ESNP, MBCT, MRBS, STCL, VGNN, ABSG, VGFS, VSFS, DRRC,
DJIC, AGNG, AYRK, SRTS, SHYD, GTRA`), which does not include GRTL — the existing comment in
`payroll.ts` already documents this exclusion correctly.

---

## 1. The actual gap

Lines 1019-1193 sit **outside** the `if(in_array($company_code, $specialCompanies))` block (it
closes at line 1014, before this section starts) — confirmed by brace-matching the file. That
means this logic runs **unconditionally, for every company**, not just the special-tenant list.
It is currently **not ported anywhere** in `payroll.ts` / `route.ts`.

### What it does (legacy, lines 1019-1193)

For each processed payroll (`payroll_master_pkey` from the request loop):

1. Query `emp_salary_slip` rows where `payroll_master_fkey = :payrollMasterPkey`,
   `remarks IS NOT NULL`, `end_date_effective IS NULL`, `head_type <> 'Arrear'`.
2. For each row, look up the employee's active salary structure formula:
   ```sql
   SELECT structure_formula, structure_det_calequation, structure_det_depends,
          structure_det_operator
   FROM salary_structure_details
   WHERE salary_head_item_fkey = :salary_head_item
     AND structure_id = (
       SELECT DISTINCT emp_structure_id FROM emp_salary_structure
       WHERE emp_fkey = :emp_fkey AND end_date_effective IS NULL
     )
   ```
3. Tokenize `structure_det_calequation` with `/\d+_[A-Za-z_]+|monthsal/` — each token is either
   `monthsal` (literal) or `{salary_head_item_pkey}_{anything}` (e.g. `1023_basic`).
4. Resolve each token to a number:
   - `monthsal` → `SUM(salary_amount)` from `emp_salary_slip` for that employee/month where
     `head_operator = 'Addition' AND item_part = 'Direct' AND end_date_effective IS NULL`.
   - `{pkey}_...` → `salary_amount` from `emp_salary_slip` for that employee/item/month
     (`LIMIT 1`, `end_date_effective IS NULL`).
   - Substitute the token's literal text with the resolved number in the formula string
     (`str_replace`, not a proper tokenizer — see §5 note).
5. **Gate on column existence:** `SHOW COLUMNS FROM emp_salary_slip LIKE 'remarks_2'`. Confirmed
   present on `mypayrol_mpm121` (and `mypayrol_trial`) — see §2. If present:
   - `remarks_2` = the fully-substituted formula string (e.g. `"15000+2000+3000"`).
   - `formula` = the original `structure_formula` (human-readable label), or `NULL`.
   - `combined_base_value`: split the substituted string on the **last** `*` into
     `sum_part` (base) and `multiplier_part`; validate `sum_part` matches
     `/^[0-9+\-().]+$/` and eval it; if `structure_det_operator` is `limit_wl`/`limit_wg`, also
     eval `multiplier_part` (default `1` if invalid/zero), divide `structure_det_depends` by it
     to get an effective limit, and clamp `sum_part`'s value against that limit with
     `min`/`max` (`limit_wg` → `max`, `limit_wl` → `min`).
6. `UPDATE emp_salary_slip SET remarks_2=…, formula=…, combined_base_value=… WHERE
   emp_salary_slip_pkey = :emp_salary_slip_pkey` — only if the accumulated update array is
   non-empty. Wrapped in its own `try/catch` (legacy logs and continues past a bad row rather
   than aborting the whole run).

### Why it matters

This isn't dead/rare code: on `mypayrol_mpm121`, **1,183** `emp_salary_slip` rows currently have
non-null `remarks` (i.e. are formula-driven), and **334** `salary_structure_details` rows have
`structure_det_calequation` set. Every payroll run that touches a formula-driven salary item is
silently skipping this backfill in Next.js — `remarks_2`, `formula`, and `combined_base_value`
stay stale/`NULL` after processing, for every tenant, not just the special ones.

**Open question (needs product/senior-dev input, not code archaeology):** what UI or downstream
process actually *reads* `remarks_2`/`formula`/`combined_base_value`? Neither the legacy payslip
view nor `SalarySlipModal.tsx` were seen referencing them in this pass — if nothing currently
reads these columns, this may be write-only bookkeeping (audit trail / future feature) rather
than something with a visible symptom. That changes the priority but not whether it should be
ported (silent data drift from legacy is a migration-parity bug either way).

---

## 2. Live-DB confirmation (read-only, no writes)

Checked against `mypayrol_mpm121` and `mypayrol_trial` via `SHOW COLUMNS` / `SELECT COUNT(*)`
only — no `INSERT`/`UPDATE`/`CALL` executed:

| Check | mypayrol_mpm121 | mypayrol_trial |
|---|---|---|
| `emp_salary_slip.remarks_2` exists | ✅ `varchar(3000)` | ✅ |
| `emp_salary_slip.combined_base_value` exists | ✅ `decimal(10,2)` | ✅ |
| `emp_salary_slip.formula` exists | ✅ `varchar(1000)` | ✅ |
| `payroll_master.desig` exists | ✅ `varchar(100)` | not checked |
| Rows with non-null `remarks` | 1,183 | not checked |
| `salary_structure_details` rows with `structure_det_calequation` set | 334 | not checked |

So `hasRemarks2` would evaluate `true` on both checked tenants — the legacy write path is live,
not conditionally dead code for these tenants.

---

## 3. Target implementation

### 3.1 New helper — `rizo/src/lib/payroll.ts`

```ts
// Mirrors PayrollController::processpayroll()'s unconditional formula/remarks backfill
// (lines 1019-1193 — NOT inside the specialCompanies guard, runs for every tenant). Recomputes
// remarks_2/formula/combined_base_value from each formula-driven emp_salary_slip row's
// structure_det_calequation after calculate_salary_main_prc has written the raw amounts.
export async function backfillFormulaRemarks(
  pool: Pool,
  payrollMasterPkey: number,
): Promise<void> {
  const hasRemarks2 = await columnExists(pool, 'emp_salary_slip', 'remarks_2'); // cache per-request if called in a loop
  if (!hasRemarks2) return;

  const [rows] = await pool.execute<RowDataPacket[]>(
    `SELECT emp_salary_slip_pkey, emp_fkey, salary_head_item_fkey, month_year, remarks
     FROM emp_salary_slip
     WHERE payroll_master_fkey = ? AND remarks IS NOT NULL AND end_date_effective IS NULL
       AND head_type <> 'Arrear'`,
    [payrollMasterPkey],
  );

  for (const row of rows) {
    try {
      await backfillOneRow(pool, row);
    } catch (e) {
      console.error('backfillFormulaRemarks: row failed', row.emp_salary_slip_pkey, e);
      // matches legacy: log and continue to the next row
    }
  }
}
```

Key implementation notes / deviations to make deliberately, not accidentally:

- **Column-existence check should be cached**, not re-run per row (legacy does it per-row too,
  but it's wasteful — fine to hoist to once-per-call since the schema can't change mid-request).
- **Do not use `eval`.** Legacy's `eval("return $sum_part;")` and the `head_operator`
  arithmetic-eval calls must become a safe arithmetic evaluator in TS (the `+-()` character
  class is already validated by regex before eval in legacy — reuse that same validation, then
  parse with a small recursive-descent evaluator or `mathjs`'s `evaluate` in a restricted mode;
  do **not** use `Function()`/`eval()` even with the regex guard — validate *and* use a real
  parser).
- **Token substitution must not use naive string replace.** Legacy's `str_replace($token,
  $amount, $replaced_formula_string)` can double-substitute or corrupt formulas when one token
  is a substring of another (e.g. `1023_basic` vs `10233_something`) or when a substituted
  numeric value itself matches a later token pattern. Port the *intent* (substitute each
  resolved token) with a regex-based single-pass replace keyed on token boundaries, not
  sequential `str_replace` calls — this is a legacy bug worth quietly fixing, not reproducing.
- **`structure_id` lookup uses `DISTINCT ... WHERE end_date_effective IS NULL`** — if an
  employee somehow has more than one active structure row, legacy's subquery would return
  multiple rows and MySQL's implicit "just use one" behavior differs from a strict TS query;
  add `LIMIT 1` explicitly and log a warning if more than one active structure is found (data
  integrity issue, not something to silently paper over).

### 3.2 Wire into `processPayrollEmployee`

In `route.ts`'s per-row loop, after `processPayrollEmployee` succeeds for a row, call:

```ts
await backfillFormulaRemarks(pool, row.payroll_master_pkey);
```

Keep it as a **separate function call**, not inlined into `processPayrollEmployee` — legacy
also treats it as a distinct pass over `emp_salary_slip` (a second, broader query executed after
the special-company branch), and separating it keeps the "runs for everyone" semantics visually
obvious in the code, matching the analysis in §1.

---

## 4. Secondary gaps (same comparison pass, smaller)

### 4.1 Per-employee error isolation

Legacy wraps `calculateSalaryMainPrc`/`salaryProcessPrc` + the designation update in
`try/catch` (lines 868-892) and continues to the next employee on failure (`debug($e)`, loop
keeps going). `route.ts`'s `for (const row of rows)` loop has **no try/catch** around
`processPayrollEmployee` — an exception on one employee (e.g. a procedure error for one bad
record) throws and aborts the whole request, leaving the rest of the batch unprocessed with no
partial-success response.

**Fix:** wrap the per-row call in `route.ts`:
```ts
for (const row of rows) {
  try {
    const err = await processPayrollEmployee(...);
    await backfillFormulaRemarks(pool, row.payroll_master_pkey);
    if (err) errors.push({ payroll_master_pkey: row.payroll_master_pkey, error: err });
  } catch (e) {
    errors.push({ payroll_master_pkey: row.payroll_master_pkey, error: String(e) });
  }
}
```
This matches legacy's per-employee resilience and makes the existing `errors[]` response shape
(already returned by `route.ts`) actually capture hard failures, not just the procedure's own
`@err` output parameter.

### 4.2 `payroll_master.desig` schema guard

Legacy checks `isset($schema['desig'])` before updating `Payrollmaster.desig` (line 883) — a
no-op on tenants without that column. `processPayrollEmployee` in `payroll.ts` always attempts
the `UPDATE payroll_master SET desig = ?` when a designation is found, with no existence check.
Confirmed the column **does** exist on `mypayrol_mpm121`, so this isn't live-broken today, but
it's a latent failure for any tenant DB missing the column (schema drift across tenants is a
known risk in this project — see `Legacy_vs_Senior_DB_Comparison_Report.md`).

**Fix (low priority, cheap insurance):** add the same `columnExists(pool, 'payroll_master',
'desig')` check used in §3.1 before the `UPDATE`, or wrap the `UPDATE` in try/catch and log
rather than throw.

---

## 5. Known legacy quirks to preserve (not "fix" silently)

- `if (!is_numeric($formula_from_remarks)) { ...eval... } else { $salary_amount = 0; }` — this
  is *inverted* from what the variable names suggest: a **purely numeric** remarks string
  (`is_numeric` true) is discarded (`$salary_amount = 0`), only a non-numeric (i.e. an
  *expression*) string gets evaluated. This governs the ESI/general formula block **inside the
  specialCompanies guard**, which is out of scope per §0 — noted here only so a future reader
  doesn't assume it's part of §1's logic.
- The `remarks_2`/`combined_base_value` block (§1) does **not** have this inversion — it always
  attempts substitution regardless of whether `formula_from_remarks` looks numeric, since it's
  working from `structure_det_calequation`, not `remarks`, for the actual formula text.

---

## 6. Test cases

| # | Setup | Action | Expected |
|---|---|---|---|
| T1 | Employee with a formula-driven salary item (`structure_det_calequation` set, tokens resolve to real `emp_salary_slip` amounts) | Process payroll | `remarks_2` = substituted numeric expression; `formula` = original label; `combined_base_value` = evaluated result, matching legacy's output for the same employee/month run through the legacy screen (read-only comparison, do not double-process the same employee in both systems). |
| T2 | Formula includes `monthsal` token | Process payroll | `monthsal` resolved to `SUM(salary_amount)` where `head_operator='Addition' AND item_part='Direct'` for that employee/month — verify against a hand-summed total. |
| T3 | `structure_det_operator` = `limit_wl` or `limit_wg` with a multiplier segment after the last `*` | Process payroll | `combined_base_value` clamped correctly (`min` for `limit_wl`, `max` for `limit_wg`) against `structure_det_depends / multiplier`. |
| T4 | Row with `remarks IS NOT NULL` but `head_type = 'Arrear'` | Process payroll | Row excluded from the backfill (matches legacy's `NOT head_type='Arrear'` filter). |
| T5 | One employee's procedure call throws (forced failure) | Process a batch of 3 | The other 2 employees still process successfully; failed one appears in `errors[]`; response is 200, not a thrown 500 (§4.1). |
| T6 | Tenant DB without `remarks_2` column (simulate if possible, or code-review only) | Process payroll | Backfill is a no-op, no error thrown (§1 step 5 gate). |
| T7 | Formula with two tokens where one is a substring of another (e.g. `102_x` and `1023_y`) | Process payroll | Correct, independent substitution for each — regression check for the naive-`str_replace` bug called out in §3.1. |

---

## 7. Open questions for product / senior dev

- **Q1 — is `remarks_2`/`combined_base_value` read anywhere downstream?** Needed to set
  priority: if it's genuinely write-only today, this is a parity/audit-trail fix, not a
  user-visible bug fix. Grep legacy's `.ctp` views and any reporting queries before committing
  engineering time — this plan does not claim to have found a consumer.
- **Q2 — acceptable to fix the naive `str_replace` token-substitution bug (§3.1) while porting,**
  or should the port intentionally reproduce the legacy bug for byte-for-byte parity with
  historical data some other process may depend on? Default assumption: **fix it** (data
  correctness > bug-for-bug parity for a write-only-seeming column), but flag explicitly since
  this project's precedent (`Bulk_Policy_Shift_Allocation_Migration_Plan.md` §7 Q1) treats this
  kind of call as needing sign-off.
- **Q3 — `eval` replacement approach:** restricted recursive-descent hand-rolled evaluator vs.
  a vetted dependency (e.g. `mathjs` in restricted/no-scope mode)? Given the input is
  pre-validated by regex to `[0-9+\-*/().\s]` only, a small hand-rolled evaluator is likely
  simpler to audit than pulling in a dependency — but flagging for a decision rather than
  assuming.
