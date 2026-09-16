# Bulk Policy Allocation — Salary Structure tab: Migration Completion Plan

> **STATUS: IMPLEMENTED 2026-09-03.** All phases done. New route
> `rizo/src/app/api/employees/bulk-policies/salary/route.ts`, new component
> `rizo/src/components/employees/SalaryStructureAllocator.tsx`, generic route + page wired.
> SQL for assign/remove verified end-to-end against live `mypayrol_mpm121` with rollback.
> See `PROGRESS.md` §2.28. Decisions applied: formula recompute follows legacy; assign/remove
> are single-employee (no multi-select).

Scope: the **Salary Structure** tab of legacy `EmployeeConfig/index.ctp` ("Bulk Policy Allocation")
vs. the Next.js `/employees/bulk-policies` page. The other 7 policy tabs (Shift / Leave / Holiday /
Notice Period / Division / Section / Grade) and the two hierarchy movers are already ported and are
out of scope here except where noted.

---

## 1. Legacy behaviour (source of truth)

### 1.1 Screen layout — 3 columns (`index.ctp` tab5, lines 337-368)

| Column | Endpoint | Contents |
|---|---|---|
| **Salary** | `EmployeeConfig/listsalaryemployeesforconfig` | Active structures only (`structure_active = 1`). Row label = `structure_name - structure_eg_amt`. Row id = `structure_id`. |
| **Non-Allocated Employees** | `EmployeeConfig/listemployeesforsalary?emp_pkey={structure_id}` | See §1.4 |
| **Employees in selected salary structure** | `EmployeeConfig/listemployeesinsalary?emp_pkey={structure_id}` | See §1.5 |

Drag-and-drop grids (dhtmlx). **One employee moved per drag.** Middle → right calls
`addEmpToSallary`; right → middle calls `removeEmpFromSallary`. Each drag first calls
`checkEmpSalaryStructure` (see §1.2).

### 1.2 `checkEmpSalaryStructure?emp_id=92` (`EmployeeConfigController.php:2078`)

Returns `{ created_by: 'upload' | '' }` — `'upload'` if the employee has any
`emp_salary_structure` row with `created_by = 'upload'`.

**This endpoint is effectively dead.** The view JS ignores the response
(`if (true) { ... }` at `index.ctp:1633` and `:1714`); both confirm dialogs fire unconditionally.
**Decision: do not port.** Leave a code comment noting why.

### 1.3 DB routines & triggers the flow depends on

- **`sal_structure_distribution_fn(company_code, emp_fkey, structure_id, user_id) → int`**
  (`schema/mypayrol_mpm121.sql:23706`). The engine. Per call:
  - End-dates the employee's current `emp_salary_structure` rows
    (`end_date_effective = current_date`; if `ctc_upload_type = 2`, keeps `head_fkey = 1` rows).
  - Inserts one `emp_salary_structure` row per `salary_structure_details` row with a non-zero
    value, resolving `formula` / `limit` / `limit_wl` / `limit_wg` / `fixed` operators; applies
    deduction sign flip; computes the `rembalance` remainder row.
  - BLOCK5/BLOCK6: writes each formula head's `remarks` column as a **fully numeric** arithmetic
    string (head-item names already substituted with amounts, `Monthly Gross Salary` → monthly
    CTC number).
  - Sets `emp_ctc_transaction.emp_derived_anualctc` and `ctc_upload_type = 1`.
  - Returns `1` if at least one row was created for `(emp_fkey, structure_id)`, else `0`.
  - **Does NOT touch `emp_proff.structure_id`.**
- **`salary_structure_limit_prc(IN emp_fkey int, IN user_id varchar(30), OUT err_msg varchar(500))`**
  (`schema/mypayrol_mpm121.sql:23384`). Post-distribution limit adjustment. Called with the
  single emp id.
- **Trigger `emp_config_bi` BEFORE INSERT ON `emp_config`** (`schema:76101`): for
  `NEW.type = 'SALARY'` → `UPDATE emp_proff SET structure_id = NEW.policy_id WHERE emp_fkey = NEW.emp_fkey`.
- **Trigger `emp_config_au` AFTER UPDATE ON `emp_config`** (`schema:76126`): for
  `NEW.type = 'SALARY'` → `UPDATE emp_proff SET structure_id = NULL, modified_by = NEW.modified_by
  WHERE emp_fkey = NEW.emp_fkey`. Fires on **any** update to the row (legacy relies on this when it
  sets `status = 0`).

So in legacy, `emp_proff.structure_id` is maintained **only by these triggers**, never by PHP, for
the SALARY type. The "non-allocated" filter in §1.4 depends entirely on that column.

### 1.4 `listemployeesforsalary?emp_pkey={structure_id}` (`EmployeeConfigController.php:1766`)

Candidate list. Conditions:
- `emp_details.status = 1`
- `emp_proff.structure_id` is `''` or `NULL`
- **Eligibility:** employee monthly CTC `>=` the selected structure's `structure_eg_amt`.
  - `emp_anual_ctc` from `emp_ctc_transaction` where `end_date_effective IS NULL`.
  - If `emp_proff.emp_type = 'DAILY WAGES'` → monthly CTC = `emp_anual_ctc` (used as-is).
  - Else → monthly CTC = `emp_anual_ctc / 12`.
- Branch scope: if `user_group = 2` and `company_code IN ('GLET','ABSG')`, restrict to the user's
  branch via `get_branch_code_abs_fn`. (No-op for GRTL — port for parity, low priority.)

Columns: **Name, Branch, Designation, Gross Salary** (`round(monthlyCTC)`).

### 1.5 `listemployeesinsalary?emp_pkey={structure_id}` (`EmployeeConfigController.php:1680`)

Allocated list. Employees where `emp_config` has `type = 'SALARY'`, `policy_id = {structure_id}`,
`status = 1`. Same 4 columns; Gross = `round(emp_anual_ctc / 12)` (no DAILY WAGES branch here).
Same GLET/ABSG branch scope.

### 1.6 `addEmpToSallary?id=92&parent_emp_pkey=25` (`EmployeeConfigController.php:2527`)

`id` = emp id (PHP accepts CSV; **we will send one**). `parent_emp_pkey` = `structure_id`.

1. If `UPPER(company_code)` **not** in
   `['ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','GTRA','VGNN','SHYD','SRTS']`:
   `UPDATE emp_ctc_transaction SET ctc_upload_type = 1
    WHERE emp_fkey = ? AND ctc_upload_type = 2 AND end_date_effective IS NULL`
   (GRTL is not special → this runs for GRTL.)
2. `SELECT sal_structure_distribution_fn(company_code, emp, structure_id, login_user_id) AS fn` → `result`.
3. Formula-remark recompute (**decision: follow legacy exactly**):
   `SELECT emp_salary_structure_pkey, head_operator, remarks, salary_head_item_desc
    FROM emp_salary_structure
    WHERE emp_structure_id = ? AND emp_fkey = ? AND remarks IS NOT NULL AND end_date_effective IS NULL`
   For each row:
   - `formula = preg_replace('/\s+/', '', remarks)` → a pure numeric arithmetic string.
   - `salary_amount = eval(formula)`.
   - If `LOWER(TRIM(salary_head_item_desc))` is one of the ESI variants
     (`esi`, `esi - employee contribution`, `esi - employer contribution`):
     `head_operator = 'Deduction'` → `ceil`, else → `round`.
   - Else → `round`.
   - If `head_operator = 'Deduction'` → `salary_amount *= -1`.
   - `UPDATE emp_salary_structure SET structure_det_value = ? WHERE emp_salary_structure_pkey = ?`.
4. If `result == 1`: `INSERT` `emp_config` `(type='SALARY', emp_fkey, policy_id=structure_id,
   created_by=login_user_id)` (CakePHP `save()` — id 0, status defaults to 1).
   → trigger `emp_config_bi` sets `emp_proff.structure_id`.
5. `CALL salary_structure_limit_prc(emp, login_user_id, @perr_msg)` (wrapped in try/catch, errors swallowed).
6. Return `1` if `result == 1`, else `0`.

Note: the standalone `setStructureDetValueFromRemarks()` (`:2648`) is **commented out at the call
site** (`:2642`) — the inline block in step 3 is the live path.

### 1.7 `removeEmpFromSallary?id=92&parent_emp_pkey=25` (`EmployeeConfigController.php:2690`)

1. `UPDATE emp_config SET modified_by = ?, modification_date = NOW(), status = 0
    WHERE type = 'SALARY' AND emp_fkey = ? AND policy_id = ?`
   → trigger `emp_config_au` sets `emp_proff.structure_id = NULL`.
2. `UPDATE emp_salary_structure SET end_date_effective = DATE_FORMAT(NOW(),'%Y-%m-%d')
    WHERE emp_fkey = ? AND end_date_effective IS NULL`.

No re-distribution. Just detaches.

### 1.8 Gross-column privacy rule

Gross Salary column is hidden in columns 2 & 3 when
`(company_code IN ('LNWY','GAAR')) AND user_group != 1` (`index.ctp:1596`, `:1683`).
No-op for GRTL — port for parity, low priority.

---

## 2. Current Next.js state

- **Page:** `rizo/src/app/(dashboard)/employees/bulk-policies/page.tsx` — one generic
  `PolicySection` for all 8 policy types: a `<select>` (structure label = `structure_name` only) +
  a **checkbox** list of **all** active employees from `/api/employees?status=1&pageSize=500`
  (client text filter only) + an "Assign to N selected" button.
- **API:** `rizo/src/app/api/employees/bulk-policies/route.ts` — `POST` only.
  For `type = 'SALARY'`: per emp, `SELECT sal_structure_distribution_fn(...)`, then on success
  `INSERT emp_config (type='SALARY', company_code, branch_code, emp_fkey, policy_id, created_by, status=1)`.
  No transaction, no `ctc_upload_type` reset, no remark recompute, no `salary_structure_limit_prc`,
  no explicit `emp_proff.structure_id` write, no un-assign.
- **Structure lookup:** `GET /api/setup/salary-structures` → `structure_id, structure_name` where
  `structure_active = 1`. (Do not change its default shape — several screens depend on it.)
- **Reusable template:** `rizo/src/components/employees/HierarchyMover.tsx` +
  `GET/POST /api/employees/hierarchy` already implement the exact 3-panel
  "picker / available / assigned" pattern with per-panel search — model the new component on it.
- **Formula evaluator:** `rizo/src/lib/salaryFormula.ts` exports `evaluateArithmetic(expr)` — a safe
  (no `eval`/`Function`) parser for `+ - * / ( )` + numeric literals. This is exactly what legacy
  step 3's `eval()` needs (remarks is numeric by that point).

### Gap summary

| # | Gap | Plan phase |
|---|---|---|
| G1 | No "employees in selected structure" panel; no un-assign path | 3, 4 |
| G2 | Candidate list unfiltered (all active emps, cap 500) — no non-allocated filter, no CTC eligibility | 1, 4 |
| G3 | Assign skips `ctc_upload_type` 2→1 reset | 2 |
| G4 | Assign skips remark-formula recompute | 2 |
| G5 | Assign skips `salary_structure_limit_prc` | 2 |
| G6 | No transaction around the per-employee work | 2 |
| G7 | Missing Branch / Designation / Gross columns; structure label missing `- eg_amt` | 1, 4 |
| G8 | No confirm dialogs; no branch scope / gross-privacy rule | 2, 4 |
| G9 | `emp_proff.structure_id` not maintained for SALARY (depends on trigger existing) | 0, 2, 3 |

---

## 3. Decisions (confirmed)

- **Formula recompute:** follow legacy exactly (§1.6 step 3), using `evaluateArithmetic()`.
- **Batch:** **one employee at a time.** The new panels use single-select (radio / row click),
  not checkboxes. Assign/remove act on exactly one `emp_fkey`.
- Auth stays `userGroup === 1` (unchanged from current route).

---

## 4. Plan

### Phase 0 — Verify against live `mypayrol_mpm121` (GRTL tenant)

Before writing code, confirm on the live dev DB:

1. `SHOW TRIGGERS WHERE \`Table\` = 'emp_config';`
   → do `emp_config_bi` / `emp_config_au` exist?
   - **If yes:** the `INSERT` / `status=0 UPDATE` on `emp_config` are sufficient to maintain
     `emp_proff.structure_id`. Do **not** add explicit `emp_proff` writes (would double-run).
   - **If no:** Phases 2 & 3 must also `UPDATE emp_proff SET structure_id = ? / NULL` explicitly.
2. `SHOW CREATE PROCEDURE salary_structure_limit_prc;`
   → confirm signature `(IN int, IN varchar(30), OUT varchar(500))`.
3. Confirm `salary_structure` has `structure_eg_amt`; confirm `emp_proff.emp_type` value spelling
   (`'DAILY WAGES'`).
4. Confirm `GRTL` is not in the special-companies list (→ `ctc_upload_type` reset applies).

Record answers inline in this file before starting Phase 1.

**VERIFIED 2026-09-03 against `mypayrol_mpm121` (GRTL):**
1. **Triggers `emp_config_bi` (BEFORE INSERT) and `emp_config_au` (AFTER UPDATE) both EXIST** and
   handle `type='SALARY'` exactly as in the schema dump (`structure_id = new.policy_id` on insert;
   `structure_id = NULL, modified_by = new.modified_by` on any update).
   → **The app must NOT write `emp_proff.structure_id` for SALARY.** `INSERT emp_config` allocates;
   `UPDATE emp_config SET status=0` de-allocates. `emp_config_au` fires on *any* update and always
   nulls, so the app must only ever update a SALARY `emp_config` row to set `status=0`.
2. `salary_structure_limit_prc(IN pemp_fkey int, IN puser_id varchar(30), OUT perr_msg varchar(500))`
   — confirmed. `sal_structure_distribution_fn(varchar(30), int, int, varchar(30)) RETURNS int` — confirmed.
3. `salary_structure.structure_eg_amt` is `int`; `structure_active` is `int`. Confirmed present.
4. `emp_proff.emp_type` live values: `Permanent, Probation, HOURLY WAGES, DAILY WAGES, Contract,
   Temporary, Part-Time, NULL`. Legacy special-cases only `DAILY WAGES` — match that (HOURLY WAGES
   is *not* special-cased in legacy's fn or list query).
5. `emp_config` columns: `type` NOT NULL, `status` NOT NULL default 1, `creation_date` default
   `CURRENT_TIMESTAMP`. INSERT `(type, company_code, branch_code, emp_fkey, policy_id, created_by,
   status)` is sufficient (matches the existing non-SALARY insert in the generic route).
6. GRTL is **not** in the special-companies list → the `ctc_upload_type` 2→1 reset applies to GRTL.

### Phase 1 — Backend: list endpoint

New route `rizo/src/app/api/employees/bulk-policies/salary/route.ts`.

`GET ?structureId={id}` → `{ candidates: Row[], allocated: Row[] }` where
`Row = { emp_pkey, first_name, last_name, emp_id, branch_name, desig_name, gross }`.

- Look up `structure_eg_amt` for `structureId`.
- **candidates**: `emp_details e` JOIN `emp_proff p` (+ branches / designation) LEFT JOIN
  `emp_ctc_transaction t ON t.emp_fkey = e.emp_pkey AND t.end_date_effective IS NULL`, where
  `e.status = 1`, `(p.structure_id IS NULL OR p.structure_id = '' OR p.structure_id = 0)`, and
  `monthlyCTC >= structure_eg_amt` with
  `monthlyCTC = CASE WHEN UPPER(p.emp_type) = 'DAILY WAGES' THEN t.emp_anual_ctc ELSE t.emp_anual_ctc/12 END`.
  `gross = ROUND(monthlyCTC)`.
- **allocated**: same joins, plus `JOIN emp_config c ON c.emp_fkey = e.emp_pkey AND c.type='SALARY'
  AND c.policy_id = ? AND c.status = 1`. `gross = ROUND(t.emp_anual_ctc/12)`.
- Branch scope: if `session.user.userGroup === 2 && ['GLET','ABSG'].includes(companyCode)` apply the
  `get_branch_code_abs_fn` filter (parity; no-op for GRTL).
- Gross privacy: if `['LNWY','GAAR'].includes(companyCode) && userGroup !== 1` → return `gross: null`.
- `userGroup === 1` guard, same as the hierarchy route.

Removes dependency on `/api/employees?pageSize=500` for this tab (server-side filtered now).

### Phase 2 — Backend: rework assign

Replace the `type === 'SALARY'` branch in
`rizo/src/app/api/employees/bulk-policies/route.ts` (or move it into the new
`.../salary/route.ts` as `POST { action: 'assign', structureId, empFkey }` — preferred, keeps the
generic route generic). One `empFkey` per call.

Inside `pool.getConnection()` + `beginTransaction()`:

1. If `!SPECIAL_COMPANIES.includes(companyCode.toUpperCase())`
   (`SPECIAL_COMPANIES = ['ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','GTRA','VGNN','SHYD','SRTS']`):
   `UPDATE emp_ctc_transaction SET ctc_upload_type = 1
    WHERE emp_fkey = ? AND ctc_upload_type = 2 AND end_date_effective IS NULL`.
2. `const [[r]] = conn.execute('SELECT sal_structure_distribution_fn(?,?,?,?) AS fn',
    [companyCode, empFkey, structureId, loginUserId])`; `const ok = Number(r.fn) === 1`.
3. Remark recompute (only meaningful if `ok`, but legacy runs it regardless — match legacy, run it
   unconditionally):
   ```
   SELECT emp_salary_structure_pkey, head_operator, remarks, salary_head_item_desc
   FROM emp_salary_structure
   WHERE emp_structure_id = ? AND emp_fkey = ? AND remarks IS NOT NULL AND end_date_effective IS NULL
   ```
   For each row: `const raw = evaluateArithmetic(String(remarks));`
   - ESI-variant desc + `head_operator === 'Deduction'` → `Math.ceil(raw)`; ESI-variant otherwise →
     `Math.round(raw)`; non-ESI → `Math.round(raw)`.
   - `head_operator === 'Deduction'` → `amount = -amount`.
   - `UPDATE emp_salary_structure SET structure_det_value = ? WHERE emp_salary_structure_pkey = ?`.
   - Wrap each row's `evaluateArithmetic` in try/catch; on `FormulaError` skip that row (do not
     abort the transaction) and collect a warning.
4. If `ok`:
   - `INSERT INTO emp_config (type, company_code, branch_code, emp_fkey, policy_id, created_by, status)
      VALUES ('SALARY', ?, ?, ?, ?, ?, 1)` (branch_code from `emp_details`).
   - **If Phase 0 found no trigger:** also
     `UPDATE emp_proff SET structure_id = ?, modified_by = ? WHERE emp_fkey = ?`.
5. `CALL salary_structure_limit_prc(?, ?, @msg)` — try/catch, swallow errors (match legacy).
6. `commit()`. On any throw → `rollback()`.

Response: `{ success: ok, assigned: ok ? 1 : 0, failed: ok ? [] : [empFkey],
failedNote: ok ? undefined : 'sal_structure_distribution_fn returned 0 — the employee likely has no
active CTC row yet.', formulaWarnings }`.

### Phase 3 — Backend: un-assign

`POST { action: 'remove', structureId, empFkey }` on `.../salary/route.ts`.

Inside a transaction:
1. `UPDATE emp_config SET modified_by = ?, modification_date = NOW(), status = 0
    WHERE type = 'SALARY' AND emp_fkey = ? AND policy_id = ?`.
   - **If Phase 0 found no trigger:** also
     `UPDATE emp_proff SET structure_id = NULL, modified_by = ? WHERE emp_fkey = ?`.
2. `UPDATE emp_salary_structure SET end_date_effective = CURDATE()
    WHERE emp_fkey = ? AND end_date_effective IS NULL`.
3. `commit()`.

No re-distribution. Response `{ success: true }`.

### Phase 4 — Frontend: dedicated Salary Structure panel

New `rizo/src/components/employees/SalaryStructureAllocator.tsx`, modelled on `HierarchyMover.tsx`:

- **Structure picker** (left): list or `<select>` from `/api/setup/salary-structures`, but render
  the label as `` `${structure_name} - ${structure_eg_amt}` ``. Since the default lookup endpoint
  doesn't return `structure_eg_amt`, call `GET /api/setup/salary-structures?full=1` here (it already
  supports `?full=1` with `structure_eg_amt` + `structure_active`); filter to `structure_active === 1`
  client-side.
- On structure select → `useQuery(['bulk-salary', structureId])` →
  `GET /api/employees/bulk-policies/salary?structureId=`.
- **Non-Allocated Employees** (middle): table — Name, Branch, Designation, Gross. Per-column or
  single text filter (match `HierarchyMover`'s search input). **Single-select** (row click / radio;
  no checkboxes). "Assign →" button, disabled until a row is selected.
  - On click → `window.confirm("You are about to change the policy/rule/settings of the selected
    employee(s). The previous settings will be lost and new ones will be applied. Do you still need
    to proceed?")` → `POST { action:'assign', structureId, empFkey }` → toast → invalidate query.
- **Employees in selected salary structure** (right): same columns, single-select. "← Remove" button.
  - On click → `window.confirm("After completing the salary structure allocation, please upload any
    components again if you have them.")` → `POST { action:'remove', ... }` → toast → invalidate.
- If `gross` comes back `null` (privacy rule) → hide the Gross column.
- Reuse the app's existing toast helper (added in the ACC pass) for success/error.

Wire into `page.tsx`:
```tsx
{activeTab === 'SALARY'
  ? <SalaryStructureAllocator key="SALARY" />
  : activeSection && <PolicySection key={activeSection.type} {...activeSection} employees={employees} />}
```
Remove `SALARY` from the `SECTIONS` array (or leave it and just branch before render). Keep
`PolicySection` for the other 7 types unchanged.

### Phase 5 — Verify end-to-end (curl + DB, disposable data)

Against live GRTL, using a cloned/disposable employee:

1. `GET .../salary?structureId={S}` — confirm the target emp appears in `candidates` (and not if its
   `emp_proff.structure_id` is set, and not if its monthly CTC `< structure_eg_amt`).
2. `POST { action:'assign', structureId:S, empFkey:E }`. Then check:
   - `emp_salary_structure` — new rows for `(E, S)`, old rows end-dated.
   - `emp_proff.structure_id = S`.
   - `emp_ctc_transaction` for `E` (`end_date_effective IS NULL`): `ctc_upload_type = 1`,
     `emp_derived_anualctc` populated.
   - `emp_config` — new `('SALARY', E, S, status=1)` row.
   - Formula-head `structure_det_value`s match legacy for the same emp/structure (spot-check one ESI
     row for the `ceil` vs `round` sign behaviour).
3. `GET .../salary?structureId={S}` — emp now in `allocated`, gone from `candidates`.
4. `POST { action:'remove', structureId:S, empFkey:E }`. Then check:
   - `emp_config` row → `status = 0`, `modification_date` set.
   - `emp_proff.structure_id` → `NULL`.
   - `emp_salary_structure` rows for `E` → `end_date_effective = today`.
5. Delete all test rows / restore the cloned employee. Do not leave test state behind.

### Phase 6 — Docs

Update `PROGRESS.md` (new sub-section under Phase 2 / Company Setup or Phase 5 / Salary, wherever
bulk-policies currently lives) with: what was built, the Phase 0 trigger finding, the special-company
list, and the exact curl commands from Phase 5.

---

## 5. Explicitly NOT ported

- `checkEmpSalaryStructure` — dead in legacy (response ignored). Code comment only.
- `setStructureDetValueFromRemarks()` standalone method — commented out at its call site in legacy.
- Drag-and-drop UX — replaced by single-select + button (functionally equivalent, one emp at a time).
- Multi-employee assign/remove — deliberately single-select per confirmed decision.
