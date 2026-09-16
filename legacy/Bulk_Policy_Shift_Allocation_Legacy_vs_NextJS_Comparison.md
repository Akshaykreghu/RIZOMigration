# Bulk Policy Allocation — Shift Allocation tab: Legacy vs Next.js Comparison

> Scope: the **Shift Allocation** tab of legacy `EmployeeConfig/index.ctp` ("Bulk Policy
> Allocation") vs. the Next.js `/employees/bulk-policies` page (`SHIFT` tab).
> Sibling doc to `Bulk_Policy_Salary_Structure_Migration_Plan.md`.
>
> **UPDATE 2026-09-10:** resolved — the `SHIFT` tab was rebuilt to legacy parity
> (dedicated route + `ShiftAllocator` component). See
> `Bulk_Policy_Shift_Allocation_Migration_Plan.md` and `PROGRESS.md` §2.37. The analysis
> below describes the pre-fix state.
>
> **Verdict: NOT a faithful port.** The Next.js `SHIFT` tab reuses the generic
> "one policy → many employees" `PolicySection` component. Legacy Shift Allocation is a
> completely different screen: a **per-employee multi-shift manager** with a primary/secondary
> shift model. The current Next.js behaviour silently breaks the "exactly one primary shift"
> invariant that shift-planner, attendance and resignation-eligibility all depend on.

---

## 1. Legacy behaviour (source of truth)

Legacy has **two** shift UIs on this page, chosen by company code
(`$restrictedCompanies = ABSG, VGFS, VSFS, DRRC, DJIC, AGNG, AYRK, GTRA, VGNN, SHYD, SRTS`;
plus `HDFN/HDEQ/HDSC/HDCM` special-cased):

| Company class | Tab(s) shown | Style |
|---|---|---|
| Restricted list (+ HDFN/HDEQ/HDSC/HDCM) | `tab1` "Shift", `tab11` "Additional Shift" | policy → many employees (bulk) |
| **Everyone else (default)** | `tab12` **"Shift Allocation"** | **employee → many shifts (per-employee)** |

The screenshot the request is about is `tab12` — the default. This doc covers `tab12`.
Controller: `Controller/EmployeeConfigController.php`. View: `View/EmployeeConfig/index.ctp`
lines 163-185 (markup) and 605-740 (JS).

### 1.1 Screen layout — 3 dhtmlx grids (`index.ctp` tab12)

| Column | Endpoint | Contents |
|---|---|---|
| **Shift Allocation** (employee list) | `EmployeeConfig/listemployeesforpolicy` | All `emp_details.status = 1` employees. Cols: `full_name`, `branch_name` (LEFT JOIN `branches` on `branch_code`). Row id = `emp_pkey`. Per-column text filters. **No pagination / no cap.** |
| **SHIFTS** (unallocated) | `EmployeeConfig/listshiftsforemployees?employee={emp_pkey}` | See §1.2. Multi-select, drag source. |
| **Allocated SHIFTS** | `EmployeeConfig/listshiftsinemployees?employee={emp_pkey}` | See §1.3. Drag source. Primary row rendered with green `(Primary Shift)` suffix. |

Interaction: select an employee row → both right grids reload for that employee.
Drag a row **SHIFTS → Allocated SHIFTS** = `addShiftToEmp`. Drag **Allocated SHIFTS →
SHIFTS** = `removeShiftFromEmp`. One shift per drag. After each drag both right grids reload.

### 1.2 `listshiftsforemployees` (unallocated shifts) — lines 4481-4537

1. Query `emp_config` for this employee where `status IN (1,2) AND type IN ('SHIFT','MSHIFT')`.
2. If the employee has **no** such row → return **all** `working_day_time_procedures WHERE active = 1`, ordered by `day_time_seq`.
3. Otherwise → return active shifts **minus** the ones already in `emp_config`
   (`day_time_seq NOT IN (SELECT policy_id ... status IN (1,2) AND type IN ('SHIFT','MSHIFT'))`).

Response shape: `{ rows: [ { id: day_time_seq, data: [day_time_desc] } ] }`.

### 1.3 `listshiftsinemployees` (allocated shifts) — lines 4574-4606

```sql
SELECT wdtp.day_time_seq, wdtp.day_time_desc, ec.status
FROM working_day_time_procedures wdtp
INNER JOIN emp_config ec ON ec.policy_id = wdtp.day_time_seq
WHERE ec.emp_fkey = {emp} AND ec.status IN (1,2) AND wdtp.active = 1
  AND ec.type IN ('SHIFT','MSHIFT')
ORDER BY wdtp.day_time_seq
```

If `ec.status == 1` → append `<span style="color:green;font-weight:bold;">(Primary Shift)</span>`
to the label. So **`status = 1` = primary, `status = 2` = secondary/multi**.

### 1.4 `addShiftToEmp` — lines 4708-4843

Params: `emp_fkey` (single), `shift` (comma list, but UI sends one).

Per shift id:
1. **Determine primary/secondary.** Find any *other* `emp_config` row for this employee with
   `type='SHIFT' AND status=1 AND policy_id != {shift}`.
   - If one exists → new row is `type='MSHIFT', status=2` (secondary).
   - If none → new row is `type='SHIFT', status=1` (primary).
2. **Look for an existing row** for `(emp_fkey, policy_id)` regardless of type/status.
   - **Exists** → `save()` update: set `status`, `modified_by`, `modification_date`. If
     `status` changed, also flip `type` (`status 1 → 'SHIFT'`, else `'MSHIFT'`). This is how a
     previously-removed (`status=0`) shift is *reactivated* rather than duplicated.
   - **New** → `create()` + `save()` insert: `type, emp_fkey, policy_id, status, created_by, creation_date`.
     Note: legacy does **not** write `company_code` / `branch_code` here (relies on a DB
     insert trigger for downstream sync).
3. **If the resulting row is primary (`status === 1`)** → `updateAll` on `emp_proff`:
   `day_time_seq = {shift}`, `modified_by`, `modified_date = NOW()` for `emp_fkey = {emp}`.
   (Comment in code: the trigger won't fire on UPDATE, so this is done explicitly.)

Returns a per-shift array of `{status, shift_id, message}`.

### 1.5 `removeShiftFromEmp` — lines 4939-5037

Params: `emp_fkey` (comma list), `shift` (single).

Per employee:
1. Find the `emp_config` row `(emp_fkey, policy_id=shift, type IN ('SHIFT','MSHIFT'))`;
   read `status`, `type`. `isPrimary = status==1 && type=='SHIFT'`.
2. **Soft-delete**: `updateAll status = 0, modified_by, modification_date = NOW()` for all
   rows matching `(emp_fkey, policy_id, type IN ('SHIFT','MSHIFT'))`.
3. **If the removed row was primary** → promote a replacement:
   - Find newest `emp_config` row `status=2 AND type='MSHIFT'` (`ORDER BY modification_date DESC`).
   - If found → `updateAll status = 1, type = 'SHIFT', modified_by, modification_date = NOW()`
     on that row, then `updateAll emp_proff.day_time_seq = {promoted policy_id}` for the employee.
   - If none found → `updateAll emp_proff.day_time_seq = '0'` for the employee (clears the shift).

### 1.6 Invariants legacy maintains

- **At most one** `emp_config` row per employee with `type='SHIFT' status=1` (the primary).
- Secondary shifts are `type='MSHIFT' status=2`.
- `emp_proff.day_time_seq` always tracks the current primary (or `'0'` / unchanged when none).
- No duplicate active `(emp_fkey, policy_id)` rows — re-adding reactivates the existing row.
- Removed shifts are `status=0`, never hard-deleted.

---

## 2. Next.js behaviour (as built)

- **Page**: `src/app/(dashboard)/employees/bulk-policies/page.tsx`. `SHIFT` is one of the
  generic `SECTIONS` entries (line 22), rendered by `<PolicySection>`.
- **UI** (`PolicySection`, lines 47-151): a single **Policy** `<select>` (options from
  `/api/setup/shifts`, `active <> 0`, label = `day_time_desc`), a text filter, a checkbox
  list of employees (`/api/employees?status=1&pageSize=500`), and one **"Assign to N
  selected"** button. No allocated panel, no de-allocation, no primary indicator.
- **API**: `POST /api/employees/bulk-policies` — `src/app/api/employees/bulk-policies/route.ts`.
  Body `{ type: 'SHIFT', policy_id, emp_fkeys: number[] }`. Auth: `userGroup === 1` only.
  In one transaction, for each `emp_fkey`:
  ```sql
  INSERT INTO emp_config (type, company_code, branch_code, emp_fkey, policy_id, created_by, status)
  VALUES ('SHIFT', {company}, {emp.branch_code}, {emp_fkey}, {policy_id}, {loginUserId}, 1);
  UPDATE emp_proff SET day_time_seq = {policy_id} WHERE emp_fkey = {emp_fkey};
  ```
  Returns `{ success: true, assigned: emp_fkeys.length }`.

---

## 3. Gap analysis

| # | Area | Legacy | Next.js | Severity |
|---|---|---|---|---|
| G1 | **Workflow model** | Per-employee: pick 1 employee, add/remove many shifts | Per-policy: pick 1 shift, assign to many employees; **no remove** | **Blocker** — wrong screen |
| G2 | **Primary vs secondary** | 1st shift → `SHIFT/status=1`; extra → `MSHIFT/status=2`; exactly one primary enforced | **Every** assignment written as `type='SHIFT', status=1` | **Blocker** — creates multiple primaries |
| G3 | **`emp_proff.day_time_seq`** | Overwritten only when the row is primary; on primary removal, promoted to next MSHIFT or `'0'` | Overwritten unconditionally on every assign, even if employee already had a curated primary | **High** |
| G4 | **Duplicate handling** | Reactivates existing `(emp_fkey, policy_id)` row (`save()` on found id) | Blind `INSERT` every time → duplicate `emp_config` rows on re-assign | **High** |
| G5 | **De-allocation** | `removeShiftFromEmp` — soft-delete + primary promotion | Not implemented at all | **High** |
| G6 | **Allocated / unallocated panels** | Middle grid excludes already-allocated shifts; right grid shows current allocations with green primary flag | Flat dropdown of all active shifts; no visibility of what an employee already has | **Medium** |
| G7 | **MSHIFT support** | Secondary shifts are first-class (`shift-planner` reads `type='MSHIFT' status=2`) | No way to create an MSHIFT row | **Medium** |
| G8 | **Employee list scope** | All `status=1` employees, no cap, with branch column + per-column filters | `status=1`, **capped at `pageSize=500`**, name filter only | **Medium** (silent truncation on large tenants) |
| G9 | **Restricted-company variant** | `tab1`/`tab11` (bulk "Shift" + "Additional Shift") for the restricted company list | Not represented; single generic tab for all companies | **Low** (document / confirm scope) |
| G10 | **`emp_config` columns** | Insert omits `company_code`/`branch_code` (trigger-synced) | Writes both explicitly | **Low** (harmless if trigger also present — verify no double-write) |
| G11 | **Reactivation type flip** | On reactivating a row whose status changes, `type` is flipped to match | N/A (no reactivation path) | Low — folded into G4 |

### 3.1 Concrete failure scenarios with the current Next.js tab

1. Employee already has a primary shift (set at onboarding / via Employee Detail).
   Admin assigns any shift here → a **second** `emp_config` row with `type='SHIFT' status=1`.
   `attendance/shift-planner` picks `primary_shift[0]` (order by `emp_config.id`) — now
   non-deterministic which shift wins; attendance minutes, OT threshold and resignation
   eligibility (`emp_proff.day_time_seq` + `structure_id` check) can all read the wrong shift.
2. Admin assigns the same shift twice (or the page is used repeatedly) → N duplicate active
   rows for one `(emp_fkey, policy_id)`; `listshiftsinemployees`-style joins fan out.
3. Admin wants to *change* an employee's shift → there is no remove; they can only pile on
   more `status=1` rows and repeatedly overwrite `emp_proff.day_time_seq`.
4. Admin wants to add a *secondary* shift for roster overrides → impossible; the row is
   always written as primary.

---

## 4. Recommendation

The generic `PolicySection` is the right pattern for Leave / Holiday / Notice / Division /
Section / Grade, but **Shift Allocation needs its own component**, mirroring the
`SalaryStructureAllocator` precedent (dedicated route + 3-panel allocate/de-allocate).

### 4.1 New API — `src/app/api/employees/bulk-policies/shifts/route.ts`

- `GET ?emp_fkey=` → `{ allocated: [{day_time_seq, day_time_desc, primary: boolean}], unallocated: [...] }`
  Port §1.2 + §1.3 verbatim (`type IN ('SHIFT','MSHIFT')`, `status IN (1,2)`, `wdtp.active=1`;
  "no allocation → show all active" branch).
- `POST { emp_fkey, day_time_seq }` → port `addShiftToEmp` §1.4 exactly:
  other-primary check → primary vs MSHIFT decision → find-existing → reactivate-or-insert →
  `emp_proff.day_time_seq` update only when primary. Keep it single-employee.
- `DELETE { emp_fkey, day_time_seq }` → port `removeShiftFromEmp` §1.5 exactly, incl. MSHIFT
  promotion (`ORDER BY modification_date DESC`) and the `day_time_seq = '0'` fallback.
- Decide `company_code`/`branch_code` on insert: check whether the live DB has the insert
  trigger legacy relied on; if yes, match legacy and omit them, else keep writing them but
  ensure the trigger (if any) doesn't double-apply.

### 4.2 New component — `src/components/employees/ShiftAllocator.tsx`

3 panels matching the screenshot: **Employee list** (reuse the 500-cap-free employee source —
raise/remove the cap or switch to a search-driven list; include Branch column + filter) →
**SHIFTS** (unallocated) → **Allocated SHIFTS** (with a green "Primary" badge on `primary:true`).
Add/remove by button or drag; refetch both right panels after each mutation.
Wire into `page.tsx` the same way `SALARY` is: `{activeTab === 'SHIFT' && <ShiftAllocator/>}`
and drop `SHIFT` from the generic `SECTIONS` array.

### 4.3 Data cleanup (pre-cutover)

Query for employees with `>1` active `type='SHIFT' status=1` `emp_config` row and for
duplicate active `(emp_fkey, policy_id)` rows created by the current tab; reconcile to one
primary each and re-sync `emp_proff.day_time_seq` before shipping the fix.

### 4.4 Confirm with product

- Is the restricted-company bulk "Shift" / "Additional Shift" variant (G9) in scope for the
  migration, or is only the default per-employee tab being ported?
- Should the new allocator stay single-employee (like legacy `tab12` and like
  `SalaryStructureAllocator`), or gain a true multi-select bulk mode? Legacy `tab12` is
  strictly per-employee.
