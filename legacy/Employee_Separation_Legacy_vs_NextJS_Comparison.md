# Employee Separation (Resignation form) — Legacy vs. Next.js Comparison

**Legacy:** `Controller/EmployeeResignationController.php` — `form()` (L107), `getperiod()` (L93),
`Terminate()` (L2381) + `View/EmployeeResignation/form.ctp` (375 lines)
**Next.js:** `/employees/resignations` (`src/app/(dashboard)/employees/resignations/page.tsx`),
`src/app/api/resignations/route.ts`, `src/app/api/employees/[id]/route.ts`
**Date:** 2026-09-10
**Method:** source-code comparison. Scope is the two endpoints the admin "Separation" form hits —
`EmployeeResignation/form/0` (form load) and `EmployeeResignation/getperiod/{emp_fkey}` (notice
period on employee select). Downstream Full & Final settlement (`approves` / `setup` / `removeemps`)
is out of scope here.

---

## Headline

Next.js **fuses two legacy modules** — `EmployeeResignationController` (admin one-shot separation)
and `ResignationRequestController` (employee self-service request + approval) — into a single
`/api/resignations` pipeline. Because of that, the admin Separation form no longer creates an
already-approved `termination` row the way legacy's `Terminate()` does; it creates a **pending**
`resignation_requests` + `termination` pair that then moves through submit → checklist → approve →
finalize. Notice-period behaviour is functionally equivalent but the dedicated `getperiod`
endpoint was dropped in favour of reusing the full employee-detail endpoint, which also lost the
joining-date floor on the "Submitted on" date picker.

---

## 1. `EmployeeResignation/form/0` — Separation form load

### Legacy
- GET route renders `form.ctp` as a Bootstrap **modal** ("Employee Separation"), opened from the
  resignation index page's "New" button.
- `form($emp_pkey = 0)`:
  - **New** (`emp_pkey = 0`): employee `<select>` = active employees **`emp_pkey NOT IN
    (SELECT emp_fkey FROM termination WHERE status = 1)`**, ordered by `first_name`, label
    `first_name last_name - emp_company_id`.
  - **Edit** (`emp_pkey != 0`): loads the existing `termination` (status = 1) + `employee_info` +
    `emp_details.branch_code` row and pre-fills every field; the employee `<select>` collapses to
    the one locked option.
  - GLET / ABSG + `user_group = 2` → `branch_code` filter via `get_branch_code_abs_fn`.
- Fields: Select Employee\*, Reason For Leaving\* (19 options), Resignation Submitted on\*
  (`dd-mm-yyyy`), Last Applied working date\*, Notice Period\* (**readonly** number),
  Last working date `(as per notice days)`\* (**readonly**, JS-computed), Last Approved working
  date\*, Remarks\* (**required** textarea).
- `asper_notice()` (fires on submitted-date change and on employee change):
  `last_working_date = submitted_date + (notice_period − 1)` calendar days, formatted `dd-mm-yyyy`.
- Submit-time JS validation: employee selected; `applied_date >= submitted_date`;
  `approved_date >= submitted_date`; then `confirm("Do You Want To Save The Form")`.
- ajaxSubmit → `EmployeeResignation/Terminate`.

### Next.js
- No route/modal split. `/employees/resignations` page; **"New Resignation"** button opens an
  in-page React `<Modal>` ("New Resignation" / "Edit Resignation"). The whole flow is also
  embeddable inside an employee-profile tab (`embeddedEmpPkey` — employee locked, no header).
- Employee picker: `<EmployeeSearch>` → `GET /api/employees?search=&pageSize=10` (active-employee
  typeahead). **No exclusion of employees already in `termination`.** A duplicate is blocked only
  at POST time, and against a different table/condition: 409 if `resignation_requests` has a row
  with `status = 1 AND Resignation_status NOT IN ('Completed','Cancelled')`.
- Fields: Employee\*, Reason\* (same 19 options), **Reason Description** (textarea),
  Resignation Submitted On\*, Last Applied Working Date\*, Notice Period (readonly),
  Last Working Day `(as per notice)`\*, Last Approved Working Date\*, Remarks.
- Auto-fill effects (all overridable — `*Touched` flags stop the effect once the user edits):
  - `last_workingday = date_submitted + (noticeDays − 1)` days (same formula as `asper_notice()`).
  - `applied_date = date_submitted`.
  - `last_approved_workingday = last_workingday`.
- `date_submitted` defaults to today.
- Client validation: `applied >= submitted`, `approved >= submitted`, `confirm(...)` — matches
  legacy. **Also enforced server-side** in `POST /api/resignations` (legacy `Terminate()` has none).
- Save → `POST /api/resignations` (or `PUT /api/resignations/[id]` for edit).

### Field-by-field differences

| Field / behaviour | Legacy | Next.js |
|---|---|---|
| Form container | `form/0` route → Bootstrap modal | in-page React `<Modal>` |
| Employee dropdown | excludes anyone with `termination.status = 1` | any active employee; 409 at submit on `resignation_requests` |
| Reason list | 19 options | same 19 options |
| **Reason value stored** | raw option `value` — `"Dissmissed"`, `"Supernnuation"`, `"Death "` (trailing space), `"Death In Service"` | cleaned label — `"Dismissed"`, `"Superannuation"`, `"Death Away From Service"` … — **string mismatch for anything filtering on Reason** |
| Reason Description | not present on this form | present (`resignation_requests.Reason_Desc`) |
| "Last working date (as per notice)" | readonly | editable (auto-suggested) |
| Remarks | required | optional, truncated to `varchar(30)` |
| Submitted-date lower bound | datepicker `setStartDate` = joining date (see §2) | none |
| Date-order validation | client only | client + server |
| Edit mode | `form/{pkey}` re-render | `PUT /api/resignations/[id]` |

### Save path (`Terminate()` vs `POST /api/resignations`)

| | Legacy `Terminate()` | Next.js `POST /api/resignations` |
|---|---|---|
| Rows written | one `termination` row | **`resignation_requests` + `termination`** (mirrors `ResignationRequestController::Saverequests()`) |
| `is_authorized` / `is_approved` | **`'Y'` / `'Y'`** — instant, self-approved (`authorized_by = 0`, `approved_by = 0`) | **`'N'` / `'N'`** — enters the approval pipeline |
| Employee `status` | unchanged (the `updateAll(status = 2)` is commented out) | unchanged (set later, at finalize/`removeemps`) |
| Settlement columns | not set (filled at F&F) | seeded to `0` (NOT NULL, no default) |
| Server validation | none (try/catch swallows errors, always returns `success: 1`) | required fields + date ordering + duplicate guard |
| `terminate_pkey` | blank = insert, present = update | insert; edit is a separate `PUT` |

**Consequence:** in legacy, submitting the admin Separation form *is* the separation (auto-approved).
In Next.js it is only step 1 — the record still needs checklist + approve + finalize before the
employee is actually removed. This matches how the port has treated every other approval workflow
(Promotion, Remove Employee, Regularisation): flatten to a single admin actor but keep the explicit
stages.

---

## 2. `EmployeeResignation/getperiod/{emp_fkey}` — notice period on employee select

### Legacy
- Dedicated endpoint: `POST /EmployeeResignation/getperiod/{emp_fkey}` →
  `SELECT notice_days, joining_date FROM emp_proff WHERE emp_fkey = ?` →
  `{ success: 1, days: <notice_days>, dates: <joining_date> }` (else `{ success: 0, days: 0, dates: '' }`).
- Called from the employee `<select>` `.change()` handler. On response:
  1. `#notice_period` ← `days`
  2. `asper_notice()` → recompute "Last working date (as per notice)"
  3. `$('#submitted_dayte').datepicker('setStartDate', dates)` — **floors the "Submitted on"
     picker at the joining date**.
- (On first render the field also falls back to `$employee[0]['EmployeeProffessional']['notice_days']`
  when `termination.notice_period` is `'0'`.)

### Next.js
- **No dedicated endpoint.** The form reuses `GET /api/employees/{emp_fkey}` — full detail:
  `employee` + `professional` (`SELECT * FROM emp_proff`) + CTC/bank/etc. — and reads
  `selectedEmp.professional.notice_days`.
- A `useEffect` on `noticeDays` recomputes `last_workingday = date_submitted + (noticeDays − 1)`
  days — same formula, same result.
- `joining_date` **is** in the payload (`emp_proff.joining_date`) but is **not used** — there is
  no `min` on the "Resignation Submitted On" input.

| | Legacy `getperiod` | Next.js |
|---|---|---|
| Endpoint | dedicated, 2 columns | reuses `/api/employees/[id]` (full record) |
| notice_days → field | yes | yes (via effect) |
| recompute last-working-date | yes (`asper_notice`) | yes (effect, same formula) |
| joining-date floor on submitted date | **yes** | **no** (only functional gap here) |
| payload size | 2 columns | full employee detail |

---

## Gaps & divergences summary

| # | Item | Type | Notes |
|---|---|---|---|
| 1 | No joining-date lower bound on "Resignation Submitted On" | ~~**Functional gap**~~ **FIXED 2026-09-10** | `joiningDate` derived from `selectedEmp.professional.joining_date` in `page.tsx`; `min={joiningDate}` + inline "Employee joined on …" hint on the input; client guard in `handleFormSubmit`; server 400 in both `POST /api/resignations` and `PUT /api/resignations/[id]` (each now selects `joining_date` from `emp_proff` alongside `notice_days`). Null / `0000-00-00` joining dates skip the constraint. Server check is stricter than legacy's UI-only `setStartDate`, matching the port's existing date-order hardening. |
| 2 | Reason stored as cleaned label, not legacy's raw value | ~~**Data consistency**~~ **FIXED 2026-09-10** | `REASON_OPTIONS` in `page.tsx` is now `{ value, label }[]`: `value` = legacy's exact `<option value>` string (typos `'Dissmissed'` / `'Supernnuation'` and the **trailing space in `'Death '`** preserved verbatim so `StatutoryUploadsController`'s ESI reason switch — `'Retrenchment'`→10, `'Retirement'`→3, `'Resigned'`→2 — matches), `label` = display text. A `reasonLabel()` helper maps the stored value back to the friendly label for the list column and the F&F slip. Live GRTL data holds only `Resignation` / `Personal` / `Relocation` / `Better Opportunity` / `Resigned` — none of the divergent values — so no data migration was needed. **Open sub-decision:** trim `'Death '` → `'Death'` (a legacy defect; nothing string-matches it today). Left verbatim pending sign-off. |
| 3 | Admin separation enters an approval pipeline instead of being instant | **Deliberate re-model** | Legacy `Terminate()` sets `is_authorized/approved = 'Y'`. Consistent with the rest of the port; confirm this is the intended UX for admin-initiated separations. |
| 4 | Employee dropdown doesn't hide already-terminated employees | **Minor** | Legacy filters on `termination.status = 1`; Next.js guards at submit on `resignation_requests`. An employee with a stale `termination` row but no `resignation_requests` row is still selectable. |
| 5 | "Last working date (as per notice)" editable vs readonly | **Minor / arguably better** | Legacy locks it; Next.js auto-suggests and allows override. |
| 6 | Remarks optional (≤30 chars) vs required | **Minor** | `termination.remarks` is `varchar(30)`; legacy marks the field required, Next.js does not. |
| 7 | "Reason Description" field added | **Addition** | Not on legacy's `form.ctp` (belongs to the employee self-service request). Harmless; populates `resignation_requests.Reason_Desc`. |
| 8 | `getperiod` folded into `/api/employees/[id]` | **Acceptable** | Heavier payload, one fewer endpoint; no behavioural loss except item 1. |

### Recommended quick fixes
- **Item 1**: ✅ done 2026-09-10 (see the gap table row above).
- **Item 2**: ✅ done 2026-09-10 — stored value now matches legacy; friendly labels shown via
  `reasonLabel()`. One open sub-decision (trim `'Death '`).
- Items 3–7 are product decisions; list them for sign-off rather than changing silently.

### Notice period on employee select — verified complete (2026-09-10)
Legacy `getperiod($emp_fkey)` returns `emp_proff.notice_days` (int, nullable) + `joining_date`.
Next.js has no dedicated endpoint — on employee select the form's `selectedEmp` query hits
`GET /api/employees/[id]` (which returns `professional` = `SELECT * FROM emp_proff`), and
`noticeDays = selectedEmp.professional.notice_days` drives the readonly "Notice Period (days)"
field and the `last_workingday = submitted + (notice − 1)` recompute (legacy `asper_notice()`).
Same source column, same result; only difference is the heavier payload (item 8) — no behavioural
gap. `joining_date` from the same payload now also powers the item-1 date floor. One legacy
nicety not ported: when *editing* a row whose `termination.notice_period` is `0`, legacy falls
back to `emp_proff.notice_days`; the Next.js edit form shows the stored `notice_period` as-is
(immaterial for new entries).
