# Shift Planner — Legacy vs. Next.js Comparison

**Legacy:** `Controller/ShiftPlannerController.php` (258 lines) + `View/ShiftPlanner/index.ctp` (583 lines)
**Next.js:** `/attendance/shift-planner` (`src/app/(dashboard)/attendance/shift-planner/page.tsx`), `src/app/api/attendance/shift-planner/route.ts`
**Menu:** Attendance → Shift Planner (`src/lib/navItems.ts:66`)
**Date:** 2026-09-10 (updated same day — see "Fixes applied")
**Method:** source-code comparison (no roster was saved on either side).

---

## Headline

**Faithful port of the core workflow.** Pick an employee + month, get a per-day table pre-filled
with the employee's primary shift (`emp_config` type `SHIFT`), override any day from a dropdown of
primary + secondary/multi shifts (`emp_config` type `MSHIFT`), Save writes one active row per
`(emp, date)` into `emp_shift_planner`. The verified-attendance guard
(`attendance_register.isdelete = 'N'` → block save) is ported with the same message, and Next.js
additionally exposes a `locked` flag so the UI disables editing up-front.

**Behavioral gap — FIXED.** The day list was built from the **calendar month** (1 → last day)
instead of the company's attendance cycle. Legacy calls `att_start_end_fn($month_year, 1|2)` for
the cycle start/end. The route now calls `getAttPeriod(pool, month)` (`src/lib/attendance.ts:53`) —
the same `att_start_end_fn` wrapper the rest of the attendance module uses — so roster rows line up
with the attendance register on non-1st-to-last cycles.

**Shift-listing conditions — FIXED.** Both shift queries were missing legacy's
`working_day_time_procedures.active = 1` join condition, and the primary query carried an extra
`ORDER BY ec.id DESC LIMIT 1` that legacy does not have (legacy adds *every* matching primary row
to the dropdown, with `primary_shift[0]` as the default). Both are corrected — see "Fixes applied".

## Scope comparison

| Capability | Legacy | Next.js | Status |
|---|---|---|---|
| Select employee + month, load roster | `index()` + `listemployees()` | `GET /api/attendance/shift-planner` | **Ported** |
| Per-day table, pre-filled with primary shift | `populateRosterTable()` / `createShiftDropdown()` | `DataTable` + `columns` | **Ported** |
| Dropdown = primary `SHIFT` + secondary `MSHIFT` options | `primary_shift` + `secondary_shifts` queries | same two queries | **Ported** |
| Override a day, Save writes `emp_shift_planner` (one `status=1` row per emp+date) | `saveRoster()` | `POST /api/attendance/shift-planner` | **Ported** |
| Re-activate an existing matching row instead of duplicating | `SELECT planner_id … UPDATE status=1` else `INSERT` | same | **Ported** |
| Block save when attendance verified (`isdelete='N'`) | `saveRoster()` Step 1 | `POST` guard (409) + `GET` `locked` flag | **Ported + improved** (UI locks pre-emptively) |
| Highlight rows changed in this session (pre-save) | `changed-row` class via `onShiftChange` | `isRowSelected` (pending ≠ saved) | **Ported** |
| Highlight rows where **saved** roster already differs from primary | on load: `savedShiftId != primaryShiftId` → `changed-row` | not replicated | **Gap (minor, cosmetic)** |
| Day-of-week shown next to each date (`2026-09-01 (Mon)`) | `date('D', …)` in `attendanceData` | raw ISO date only | **Gap (minor, cosmetic)** |
| Per-row "eye" → Shift Details modal (name / on-duty / off-duty / duration / full-day minutes) | `showShiftModal()` | not replicated (dropdown + timings column only) | **Gap (minor)** — informational; `minutesPerDay` is fetched but unused, `working_time1`/duration is dropped |
| Day list spans the **attendance cycle** (`att_start_end_fn`) | `listemployees()` | `getAttPeriod(pool, month)` loop | **Ported** (fixed this pass; was calendar month) |
| Shift dropdown join requires `working_day_time_procedures.active = 1` | both shift queries | both shift queries | **Ported** (fixed this pass; was missing) |
| Primary query returns all matching rows, no `LIMIT` | `primary_shift` | all rows, `ORDER BY ec.id` | **Ported** (fixed this pass; was `ORDER BY ec.id DESC LIMIT 1`) |
| Month picker constrained to 2023‑01 … current + 2 months | `.ctp` month `<select>` builder | free `<input type="month">` (any month) | **Difference, by design** |
| Employee picker | full `<select>` of all `status=1` employees (`select2`) | `<EmployeeSearch>` typeahead | **Difference, by design** |
| Parameterised SQL | no — string-interpolated (`$emp_pkey`, `$date`, `$shiftId`) | yes — `?` placeholders | **Improvement** |
| Access control | none beyond `AppController` (view "Back" branches on `user_group` 1/2) | `userGroup === 1` required on GET + POST | **Difference** — Next.js is admin-only |
| Dead `emp_detail_timeattandance` query (`present`, `duration` fetched, never emitted) | `listemployees()` | correctly omitted | **Match** (intentional) |

## Notable implementation details

1. **`emp_shift_planner.month_year` is `'YYYY-MM'` here**, not the `'MM-YYYY'` seen in
   `payroll_master` / `emp_variables_upload`. Both sides pass the raw form value (`'YYYY-MM'`)
   straight through, so they agree — but keep the format quirk in mind for any migration script.
2. **Primary-shift pick — now matches legacy.** Legacy: `ec.status = 1 AND wdtp.active = 1`, no
   `ORDER BY`, JS takes `[0]` and lists all rows. Next.js now uses `ec.status = 1 AND wdt.active = 1
   ORDER BY ec.id` (ascending → oldest active row is the default, matching legacy's natural key
   order) and puts every matching primary row plus every `MSHIFT` row in the dropdown. The
   `wdt.active = 1` condition is now on both queries, so an inactive shift procedure can no longer
   surface.
3. **Save ordering is reordered but equivalent.** Legacy: `UPDATE status=0` → `SELECT existing`.
   Next.js: `SELECT existing` → `UPDATE status=0` → re-activate/insert. Same end state.
4. **No primary shift configured** → both render an empty dropdown and save nothing; attendance
   processing falls back to the `emp_config` primary as before.

## Fixes applied (2026-09-10)

All in `src/app/api/attendance/shift-planner/route.ts` GET:

1. **Attendance-cycle day range.** Replaced the calendar-month loop
   (`new Date(y, m, 0).getDate()`) with a day-by-day loop over `getAttPeriod(pool, month)`
   (`{ start, end }` from `att_start_end_fn`). Iteration is UTC epoch math (`+86_400_000` ms) to
   avoid DST drift.
2. **`working_day_time_procedures.active = 1`** added to the `SHIFT` and `MSHIFT` join conditions —
   inactive shift procedures no longer appear in the dropdown.
3. **Primary query** changed from `ORDER BY ec.id DESC LIMIT 1` to `ORDER BY ec.id` with no limit;
   every active primary row now goes into `shiftOptions`, and `shiftOptions[0]` (oldest active row,
   matching legacy's natural order) is the default — same as legacy's `primary_shift[0]`.

Typecheck (`tsc --noEmit`) and eslint pass. POST (`saveRoster`) was already a faithful port and is
unchanged.

## Remaining follow-ups (cosmetic / low priority)

| # | Item | Effort | Priority |
|---|---|---|---|
| 1 | Show day-of-week in the Date column (`2026-09-01 (Mon)`) | small | Low (cosmetic parity) |
| 2 | On load, highlight rows whose saved shift ≠ primary (legacy `changed-row`) | small | Low (cosmetic parity) |
| 3 | Optional: per-row shift-details popover (duration, full-day minutes) | small | Low (informational) |

## What this means for the migration

The part that mutates data — the `emp_shift_planner` write and the verified-attendance guard — is a
faithful port and is safe. With this pass the roster's day range and its shift-option list now match
legacy's conditions exactly. The only outstanding differences are cosmetic (day-of-week label,
saved-vs-primary highlight, details modal) or deliberate (free month input, typeahead employee
picker, admin-only access).
