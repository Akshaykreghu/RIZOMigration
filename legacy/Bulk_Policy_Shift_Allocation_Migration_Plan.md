# Bulk Policy Allocation — Shift Allocation tab: Migration Completion Plan

> **STATUS: CODE COMPLETE (P1–P3) 2026-09-10 — live-DB verification + reconciliation apply pending.**
> New route `rizo/src/app/api/employees/bulk-policies/shifts/route.ts` (GET/POST/DELETE),
> new component `rizo/src/components/employees/ShiftAllocator.tsx`, `page.tsx` rewired,
> `SHIFT` removed from the generic `SECTIONS`. Reconciliation script
> `rizo/scripts/reconcile-shift-allocations.mjs` written; dry run against `mypayrol_mpm121`
> found 1 multi-primary employee + 3 duplicate groups — **not applied** (pending §7 Q1).
> `tsc` + `eslint` clean. Still open: P1 end-to-end SQL verification against a real tenant,
> P4 reconciliation apply per tenant, P5 QA pass. See `PROGRESS.md` §2.37.
>
> Original problem: the Next.js `SHIFT` tab reused the generic `PolicySection` and was
> functionally wrong (see `Bulk_Policy_Shift_Allocation_Legacy_vs_NextJS_Comparison.md`, §3).
> This plan replaced it with a dedicated route + component, mirroring the
> `SalaryStructureAllocator` precedent.
>
> Sibling docs: `Bulk_Policy_Shift_Allocation_Legacy_vs_NextJS_Comparison.md` (gap analysis),
> `Bulk_Policy_Salary_Structure_Migration_Plan.md` (the pattern this follows).

---

## 0. Scope

**In scope** — legacy `EmployeeConfig/index.ctp` **`tab12` "Shift Allocation"** (the default
per-employee multi-shift manager) and its 5 endpoints:
`listemployeesforpolicy`, `listshiftsforemployees`, `listshiftsinemployees`, `addShiftToEmp`,
`removeShiftFromEmp` (`Controller/EmployeeConfigController.php` lines 4371-5038).

**Out of scope** (separate decision — see §7 Q2): the restricted-company variant
`tab1` "Shift" / `tab11` "Additional Shift" (`$restrictedCompanies` +
`HDFN/HDEQ/HDSC/HDCM`), which is a policy→many-employees bulk flow with different endpoints
(`addEmpToShift`, `addEmpToMultiShift`, …).

**Interaction model:** single employee selected, one shift added/removed per action — matches
legacy `tab12` and the `SalaryStructureAllocator` precedent. No multi-select. (See §7 Q3.)

---

## 1. Legacy behaviour — condensed

Full detail in the comparison doc §1. The essentials the port must reproduce:

- **`emp_config` rows** carry shift allocations: `type='SHIFT'` + `status=1` = the **primary**
  shift (exactly one per employee); `type='MSHIFT'` + `status=2` = secondary shifts;
  `status=0` = removed (soft-delete, never hard-deleted).
- **First** shift added to an employee → primary. **Subsequent** shifts (while a primary
  exists) → secondary. Removing the primary → **promote** the most-recently-modified secondary
  to primary; if none, clear.
- **`emp_proff.day_time_seq`** must always equal the current primary's `policy_id`, or `'0'`
  when the employee has no primary.
- **Unallocated list** = active `working_day_time_procedures` minus the shifts already on the
  employee. **Allocated list** = the employee's `SHIFT`/`MSHIFT` rows with `status IN (1,2)`,
  joined to an `active=1` shift, primary flagged.
- Re-adding a previously removed shift **reactivates** the existing `emp_config` row (updates
  `status`/`type`), it does not insert a duplicate.

---

## 2. CRITICAL — `emp_config` DB triggers (live on every tenant DB)

`schema/mypayrol_mpm121.sql` lines 76101-76150. These fire regardless of client, so the
Next.js route runs *with* them and must compensate exactly as legacy does.

### `emp_config_bi` — BEFORE INSERT
```
IF NEW.type = 'SHIFT' THEN
  UPDATE emp_proff SET day_time_seq = NEW.policy_id WHERE emp_fkey = NEW.emp_fkey;
```
- Fires **only for `type='SHIFT'`** (not `'MSHIFT'`). So inserting a primary auto-syncs
  `emp_proff.day_time_seq`; inserting a secondary does nothing to `emp_proff`. ✅ desired.

### `emp_config_au` — AFTER UPDATE
```
IF NEW.type = 'SHIFT' THEN
  UPDATE emp_proff SET day_time_seq = NULL, modified_by = NEW.modified_by WHERE emp_fkey = NEW.emp_fkey;
```
- Fires on **any UPDATE** of a row whose (new) `type='SHIFT'` — including our soft-delete
  (`status=0`) and our reactivation/promotion updates. It **nulls `day_time_seq`**.
- ⇒ **After every `emp_config` UPDATE that touches a `type='SHIFT'` row, the route must
  explicitly re-set `emp_proff.day_time_seq`** to the correct value (promoted shift, the
  reactivated shift, or `'0'`). This is exactly why legacy `addShiftToEmp` /
  `removeShiftFromEmp` re-write `emp_proff` after their `save()`/`updateAll()` calls.
- Updating a row to `type='MSHIFT'` → trigger is a no-op → `emp_proff` untouched.

**Implication for ordering inside the transaction:** do the `emp_config` write first, then the
compensating `emp_proff` write, so the trigger can't clobber the final value.

---

## 3. Target implementation

### 3.1 API route — `rizo/src/app/api/employees/bulk-policies/shifts/route.ts` (NEW)

Auth on every handler: `session.user.userGroup === 1` (matches salary route).
All writes in one `pool.getConnection()` + `beginTransaction()` / `commit` / `rollback`
(matches salary route).

---

#### `GET` (no `emp_fkey`) → employee list — port of `listemployeesforpolicy`

```sql
SELECT e.emp_pkey,
       e.first_name, e.middile_name, e.last_name,
       b.branch_name
FROM   emp_details e
LEFT JOIN branches b ON b.branch_code = e.branch_code
WHERE  e.status = 1
ORDER BY e.first_name, e.last_name
```
Return `{ employees: [{ emp_pkey, name, branch_name }] }` where
`name = [first, middile, last].filter(Boolean).join(' ')`.
**No pagination / no `pageSize` cap** (legacy has none — this fixes gap G8). The component
must not fall back to `/api/employees?pageSize=500`.

*(Optional: accept `?search=` and push a `LIKE` on name/branch for large tenants; legacy
filters client-side in the grid, so client-side filter in the component is acceptable for
parity.)*

---

#### `GET ?emp_fkey={id}` → `{ allocated, unallocated }`

**`unallocated`** — port of `listshiftsforemployees` (keep the two-branch shape):

```sql
-- 1. has any allocation?
SELECT policy_id FROM emp_config
WHERE emp_fkey = ? AND status IN (1,2) AND type IN ('SHIFT','MSHIFT');
-- hasAllocated = any row with non-empty policy_id
```
```sql
-- 2a. hasAllocated == false
SELECT day_time_seq, day_time_desc
FROM working_day_time_procedures
WHERE active = 1
ORDER BY day_time_seq;
```
```sql
-- 2b. hasAllocated == true
SELECT day_time_seq, day_time_desc
FROM working_day_time_procedures
WHERE active = 1
  AND day_time_seq NOT IN (
    SELECT policy_id FROM emp_config
    WHERE emp_fkey = ? AND policy_id IS NOT NULL
      AND status IN (1,2) AND type IN ('SHIFT','MSHIFT')
  )
ORDER BY day_time_seq;
```
> A single query using the guarded `NOT IN` sub-select (with `policy_id IS NOT NULL`) is
> behaviourally identical for the empty case and is acceptable; the two-branch form is spelled
> out only to keep the diff against legacy obvious.

**`allocated`** — port of `listshiftsinemployees`:

```sql
SELECT wdtp.day_time_seq, wdtp.day_time_desc, ec.status
FROM   working_day_time_procedures wdtp
JOIN   emp_config ec ON ec.policy_id = wdtp.day_time_seq
WHERE  ec.emp_fkey = ? AND ec.status IN (1,2) AND wdtp.active = 1
   AND ec.type IN ('SHIFT','MSHIFT')
ORDER BY wdtp.day_time_seq
```
Map each row to `{ day_time_seq, day_time_desc, primary: Number(status) === 1 }`.
> If an employee can have both a `SHIFT` and `MSHIFT` row for the same `policy_id` (legacy
> allows it), this join returns two rows for that shift. Legacy renders both. Recommend a
> `GROUP BY wdtp.day_time_seq` keeping `MAX(ec.status = 1)` as `primary` to de-dupe the UI;
> note this as a deliberate, minor improvement.

---

#### `POST { emp_fkey, day_time_seq }` → port of `addShiftToEmp` (single shift)

```
1. otherPrimary = SELECT id FROM emp_config
     WHERE emp_fkey = :emp AND type='SHIFT' AND status=1 AND policy_id <> :shift LIMIT 1
   hasOtherPrimary = otherPrimary != null

2. (status, type) = hasOtherPrimary ? (2, 'MSHIFT') : (1, 'SHIFT')

3. existing = SELECT id, status, type FROM emp_config
     WHERE emp_fkey = :emp AND policy_id = :shift LIMIT 1        -- no type/status filter (legacy)

4a. existing != null:
      setType = existing.status != status   -- legacy: flip type only when status changes
      UPDATE emp_config
        SET status = :status,
            modified_by = :userId, modification_date = NOW()
            [, type = :type IF setType]
        WHERE id = :existing.id
      -- emp_config_au fires: if resulting NEW.type='SHIFT' it nulls emp_proff.day_time_seq
      IF status == 1:
        UPDATE emp_proff
          SET day_time_seq = :shift, modified_by = :userId, modified_date = NOW()
          WHERE emp_fkey = :emp

4b. existing == null:
      INSERT INTO emp_config (type, company_code, branch_code, emp_fkey, policy_id,
                              created_by, creation_date, status)
        VALUES (:type, :companyCode, :branchCode, :emp, :shift, :userId, NOW(), :status)
      -- emp_config_bi fires for type='SHIFT' and sets emp_proff.day_time_seq = :shift
      IF status == 1:
        UPDATE emp_proff
          SET day_time_seq = :shift, modified_by = :userId, modified_date = NOW()
          WHERE emp_fkey = :emp                                  -- redundant w/ trigger, matches legacy
```
- `:branchCode` = `SELECT branch_code FROM emp_details WHERE emp_pkey = :emp`.
- **Deviation from legacy (accepted):** legacy omits `company_code`/`branch_code` on this
  insert; we populate both, matching the existing generic bulk route and every other Next.js
  `emp_config` writer. Triggers ignore these columns. (See §7 Q4.)
- Response: `{ success: true, action: 'assigned', primary: status === 1 }`.

---

#### `DELETE { emp_fkey, day_time_seq }` → port of `removeShiftFromEmp` (single shift, single employee)

```
1. current = SELECT id, status, type FROM emp_config
     WHERE emp_fkey = :emp AND policy_id = :shift AND type IN ('SHIFT','MSHIFT') LIMIT 1
   IF current == null: return { success: true, removed: 0 }        -- legacy silently no-ops

2. isPrimary = Number(current.status) === 1 && current.type === 'SHIFT'

3. UPDATE emp_config
     SET status = 0, modified_by = :userId, modification_date = NOW()
     WHERE emp_fkey = :emp AND policy_id = :shift AND type IN ('SHIFT','MSHIFT')
   -- emp_config_au fires for the SHIFT row -> emp_proff.day_time_seq = NULL

4. IF isPrimary:
     another = SELECT id, policy_id FROM emp_config
       WHERE emp_fkey = :emp AND status = 2 AND type = 'MSHIFT'
       ORDER BY modification_date DESC LIMIT 1
     IF another != null:
       UPDATE emp_config
         SET status = 1, type = 'SHIFT', modified_by = :userId, modification_date = NOW()
         WHERE id = :another.id
       -- emp_config_au fires again -> emp_proff.day_time_seq = NULL
       UPDATE emp_proff
         SET day_time_seq = :another.policy_id, modified_by = :userId, modified_date = NOW()
         WHERE emp_fkey = :emp
     ELSE:
       UPDATE emp_proff
         SET day_time_seq = '0', modified_by = :userId, modified_date = NOW()
         WHERE emp_fkey = :emp
   -- IF not isPrimary: removing a secondary; emp_config_au is a no-op for MSHIFT, nothing to fix
```
- Response: `{ success: true, removed: 1, promoted: another?.policy_id ?? null }`.

> **Column-name check during impl:** legacy uses `emp_proff.modified_date` (the `emp_config_au`
> trigger uses `emp_proff.modified_by`). Confirm the actual column is `modified_date` (not
> `modification_date`) on `emp_proff` before writing — grep the schema / other Next.js emp_proff
> writers.

---

### 3.2 Component — `rizo/src/components/employees/ShiftAllocator.tsx` (NEW)

Model on `SalaryStructureAllocator.tsx`. Three panels (matches the screenshot):

| Panel | Source | Behaviour |
|---|---|---|
| **Shift Allocation** (employees) | `GET /api/employees/bulk-policies/shifts` → `employees` | Single-select row (`emp_pkey`). Columns: Name, Branch. Client-side text filter over name+branch. Selecting a row enables the query below. |
| **SHIFTS** (unallocated) | `GET …/shifts?emp_fkey={id}` → `unallocated` | Single-select. **"Add →"** button → `POST { emp_fkey, day_time_seq }`. Text filter. |
| **Allocated SHIFTS** | same response → `allocated` | Single-select. **"← Remove"** button → `DELETE { emp_fkey, day_time_seq }`. Primary row shows a green **"Primary"** badge (`row.primary === true`). Text filter. |

- `useQuery(['bulk-shift','list'])` for employees; `useQuery(['bulk-shift', empPkey])` for the
  two shift panels, `enabled: empPkey != null`.
- `useMutation` for add / remove; `onSuccess` → invalidate `['bulk-shift', empPkey]` and
  `['employees']`, clear the shift-panel selection, show a success line.
- Empty/loading text per panel, same style as `SalaryStructureAllocator`.
- No policy `<select>` (the middle panel replaces it). No `window.confirm` needed (legacy has
  none for shift), but a light confirm on Remove-primary is a reasonable optional nicety.

### 3.3 Page wiring — `rizo/src/app/(dashboard)/employees/bulk-policies/page.tsx`

1. Remove the `SHIFT` entry from the `SECTIONS` array (line 22) so `PolicySection` no longer
   handles it.
2. Keep the `SHIFT` entry in `TABS` (label unchanged).
3. Add, next to the `SALARY` line (200):
   ```tsx
   {activeTab === 'SHIFT' && <ShiftAllocator key="SHIFT" />}
   ```
4. `activeSection` lookup now returns `undefined` for `SHIFT`, so `PolicySection` won't render
   for it — verify no crash on first load (`TABS[0].key` is `'SHIFT'`).

---

## 4. Phase plan

- [x] **P1 — API route.** Created `…/bulk-policies/shifts/route.ts` with the three handlers
      per §3.1. `tsc`/`eslint` clean. **Still to do:** verify each SQL statement against live
      `mypayrol_mpm121` (or the assigned test tenant) inside a transaction with a forced
      `ROLLBACK`, logging `emp_config` + `emp_proff.day_time_seq` before/after for:
      first-shift, second-shift, remove-secondary, remove-primary-with-secondary,
      remove-primary-no-secondary, re-add-removed-shift. Confirm trigger compensation leaves
      `emp_proff.day_time_seq` correct in every case.
- [x] **P2 — Component.** `ShiftAllocator.tsx` per §3.2.
- [x] **P3 — Wire-up.** Edited `page.tsx` per §3.3. **Still to do:** manual click-through of
      all 10 tabs to confirm nothing else regressed (needs an authenticated session).
- [~] **P4 — Data reconciliation** (§5). Script `rizo/scripts/reconcile-shift-allocations.mjs`
      written; dry run on `mypayrol_mpm121` reported the affected rows. **Not applied** —
      pending review + the §7 Q1 (`--extra=delete` vs `demote`) decision, then run per tenant.
- [ ] **P5 — QA + docs.** Run §6 test cases against a real tenant. `PROGRESS.md` §2.37 added
      and STATUS banners flipped (done); re-confirm after live verification.

Single PR for P1–P3 (branch off `main`); P4 is a separate operational step per tenant.

---

## 5. Data reconciliation (pre-cutover cleanup)

The generic route wrote `type='SHIFT', status=1` for **every** assignment and blind-inserted,
so affected tenants can have (a) multiple primary rows per employee and (b) duplicate active
rows for one `(emp_fkey, policy_id)`.

**Detect:**
```sql
-- multiple primaries
SELECT emp_fkey, COUNT(*) c, GROUP_CONCAT(id ORDER BY id) ids
FROM emp_config WHERE type='SHIFT' AND status=1
GROUP BY emp_fkey HAVING c > 1;

-- duplicate active rows for the same shift
SELECT emp_fkey, policy_id, COUNT(*) c, GROUP_CONCAT(id ORDER BY id) ids
FROM emp_config WHERE type IN ('SHIFT','MSHIFT') AND status IN (1,2)
GROUP BY emp_fkey, policy_id HAVING c > 1;
```

**Reconcile** (script under `rizo/scripts/`, `--dry-run` default, prints a per-employee plan,
requires an explicit `--apply`; follow the test-data-cleanup rule — no blind ID-range writes):

1. Per employee, choose the surviving primary = the row with the **greatest `id`** among
   `type='SHIFT' status=1` (the last one the UI wrote → matches what `emp_proff.day_time_seq`
   currently holds, so no visible change for the common case).
2. Extra `type='SHIFT' status=1` rows → **demote or delete** per §7 Q1.
3. Collapse `(emp_fkey, policy_id)` duplicates to one row (keep greatest `id`, set the rest
   `status=0`).
4. `UPDATE emp_proff SET day_time_seq = {surviving primary policy_id}` for each touched
   employee; for any employee left with zero primaries, `day_time_seq = '0'`.
5. Re-run the detect queries → expect zero rows.

---

## 6. Test cases

| # | Setup | Action | Expected |
|---|---|---|---|
| T1 | Employee with **no** shift rows | Add shift A | `emp_config`: 1 row `SHIFT/status=1/policy_id=A`. `emp_proff.day_time_seq = A`. Allocated panel shows A with **Primary** badge. Unallocated no longer lists A. |
| T2 | After T1 | Add shift B | New row `MSHIFT/status=2/policy_id=B`. `emp_proff.day_time_seq` **still A**. Panel: A primary, B secondary. |
| T3 | After T2 | Remove B | B row `status=0`. `emp_proff.day_time_seq` still A. B back in unallocated. |
| T4 | After T2 | Remove A (primary) | A row `status=0`; B promoted → `SHIFT/status=1`. `emp_proff.day_time_seq = B`. Panel: B primary, no secondaries. |
| T5 | Employee with only primary A | Remove A | A `status=0`. No secondary to promote → `emp_proff.day_time_seq = '0'`. Allocated panel empty. |
| T6 | After T3 (B removed, status=0) | Add B again | Existing B row **reactivated** (`status` set, no new row). Row count for `(emp,B)` stays 1. |
| T7 | Employee with primary A | Add A again | `otherPrimary` check excludes A itself → A stays `SHIFT/status=1`, `emp_config` unchanged-ish (idempotent), no duplicate row, `day_time_seq = A`. |
| T8 | Employee has 0 allocations | Open GET `?emp_fkey=` | Unallocated = **all** `active=1` shifts; allocated = empty. |
| T9 | Inactive shift (`active=0`) previously allocated | Open panels | Not shown in unallocated; not shown in allocated (join requires `wdtp.active=1`) — matches legacy. |
| T10 | Large tenant (>500 active employees) | Open employee panel | All active employees listed (no 500 cap). |
| T11 | Non–userGroup-1 session | Any handler | `401`. |
| T12 | Regression | Click through Leave / Holiday / Notice / Division / Section / Grade / Salary / Hierarchy / Leave Hierarchy tabs | Unchanged behaviour. |

---

## 7. Open questions for product / senior dev

- **Q1 — reconciliation of extra primaries (§5.2):** for an employee the broken tab gave
  multiple `SHIFT/status=1` rows, demote the non-surviving ones to `MSHIFT/status=2` (keep
  them as secondaries) or soft-delete them (`status=0`)? Default assumption: **soft-delete**
  (the extra assignments were unintended), but this loses the association.
- **Q2 — restricted-company variant:** are legacy `tab1` "Shift" and `tab11` "Additional
  Shift" (bulk policy→employees, for `$restrictedCompanies` + `HDFN/HDEQ/HDSC/HDCM`) in scope
  for the migration at all? If yes, they need their own plan (different endpoints, different
  UX). Default assumption: **out of scope** unless one of those company codes is a live
  Next.js tenant.
- **Q3 — bulk vs single:** keep single-employee (legacy `tab12` + salary precedent) or add a
  real multi-select "assign shift to N employees" mode that legacy never had? Default:
  **single-employee**.
- **Q4 — `company_code`/`branch_code` on insert:** confirmed acceptable to populate them
  (deviation from legacy, matches every other Next.js `emp_config` writer)? Default: **yes**.
- **Q5 — `emp_proff` date column:** confirm the writable modified-date column on `emp_proff`
  is `modified_date` (per legacy controller) and not `modification_date`.
