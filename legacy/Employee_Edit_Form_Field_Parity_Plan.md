# Employee Edit Form — Field Parity Plan

**Date:** 2026-09-14
**Scope:** close the field-coverage gap between the Next.js Edit Employee form
(`EmployeeDetail.tsx` + `PUT /api/employees/[id]`) and legacy's actual edit screens —
`Controller/EmployeeController.php::setups()` / `View/Employee/setups.ctp` (Personal,
Statutory, Bank, Documents) and `EmployeeJoinController::allonboard()` /
`View/EmployeeJoin/allonboard.ctp` (Professional/Policies). Found via direct comparison,
documented in the preceding conversation turn — see that message for the full table.
**Status:** Implemented 2026-09-14. §2 decision: **remove `name_as_per_bank`** (kept
`branch_address` — only removal explicitly requested).

Note: `Employee/setup.ctp` (singular) and its controller action `setup()` were initially
mistaken for the live edit screen — they are not (`setup()` is only reached for a regular
employee's own self-view redirect). `setups()` (plural, "edited by bindu") is the real one.
This plan is built against the correct pair.

---

## 1. Findings recap

**Extra in Next.js, not in either legacy edit screen** (pre-existing, predate this session's
redesign):
- Bank tab: `name_as_per_bank`, `branch_address`

**Missing from Next.js, present in `setups.ctp`:**
- Personal: `address`, `district`, `state`, `pincode`, `guradian` (Guardian Name),
  `relation_guardian`, `international_worker` (checkbox), `country` (Country of Origin —
  shown only when International Worker is checked), `physical_handicap` (checkbox),
  `locomotive`/`hearing`/`visual` (checkboxes, shown only when Physical Handicap is checked),
  `wps_code`, `previous_member_id`
- A whole **Family/Nominee** management block (`#familyModal` in `setups.ctp`): add/list
  family members — Name, Relation, DOB, Age (auto-computed), Blood Group, Gender,
  Nationality, Contact Number, Alternative Number, Mark as Nominee. `EmployeeDetail.tsx` has
  no Family section today (only Documents).

**Professional tab (`allonboard.ctp`):** no gap — already fully covered by the existing
Professional tab (confirmed Joining Date, 11-value Employee Type incl. exact `DAILY
WAGES`/`HOURLY WAGES` casing, Branch, Department, Designation, Grade, Shift, Holiday
Calendar, Leave Policy, Probation Days, Superior).

Out of scope (footnote only, not part of either screen the comparison was scoped to): a
*third* legacy screen, `empprofdetails.ctp`, has a "Vertical" field neither `allonboard.ctp`
nor Next.js has. Not addressed here.

---

## 2. Decision needed — the two extra fields

`name_as_per_bank` and `branch_address` exist in the Next.js Edit *and* New Employee forms
today, in both cases predating this conversation's redesign work. Legacy's `setups.ctp` save
handler (`EmployeeController.php:749`) actually accepts `name_as_per_bank` in principle —
but since no legacy view ever renders an input for it, it's dead capability there, always
saved blank. `branch_address` has no legacy handler reference at all in the edit path.

Options:
1. **Remove both** — true legacy parity, nothing rendered that legacy never captured.
2. **Keep both** — deliberate improvement beyond legacy (the DB columns are real and already
   used elsewhere — `emp_details.name_as_per_bank`/`branch_address`).
3. **Keep, but wire up legacy's own "smart default"** — legacy's `setup.ctp` (singular, the
   *other* form) has a JS default: `name_as_per_bank` auto-fills with the employee's full
   name when left blank, and the `saveemployeesetupnew()` handler does the same server-side
   fallback. Not present in `setups.ctp` (the real edit screen) but is present in
   `saveemployeesetupnew()`'s counterpart save flow for the other form family. Optional,
   independent of the remove/keep decision.

No recommendation forced here — flagging for your call before touching code.

---

## 3. Phase A — Personal tab gap fields

### 3a. `EmployeeDetail.tsx`

- Add to the seed mapping (the `useEffect` that builds `form` from `data.employee`):
  `address`, `district`, `state`, `pincode`, `guradian`, `relation_guardian`,
  `international_worker` (default `'N'`), `country`, `physical_handicap` (default `'N'`),
  `locomotive`/`hearing`/`visual` (default `'N'`), `wps_code`, `previous_member_id`. All of
  these are already returned by `GET /api/employees/[id]` (`SELECT e.*`), so no API read-side
  change is needed — this is purely wiring the already-available data into `form` and the
  JSX.
- Add fields to the Personal tab grid: Address (textarea, full width, matching
  `JoinDetail.tsx`'s pattern), District, State, Pincode, Guardian Name, Relation to Guardian,
  WPS ID, Previous Member ID — plain inputs, no new validators needed (none of these carry
  format rules in legacy either).
- Add the two conditional checkbox blocks, mirroring `JoinDetail.tsx`'s existing
  `checkbox(key, label)` helper (already defined there, not in `EmployeeDetail.tsx` — port it
  over):
  - International Worker checkbox; when checked, show a Country of Origin `<select>`
    (`useSetupOptions` on whatever setup endpoint backs `nationality_id`/`country` — confirm
    the correct one, likely `setup/nationalities` reused, or a separate `setup/countries` if
    one exists).
  - Physical Handicap checkbox; when checked, show Locomotive/Hearing/Visual checkboxes
    (three separate Y/N toggles, matching `JoinDetail.tsx`'s existing implementation of the
    same three fields — copy that pattern directly).

### 3b. `PUT /api/employees/[id]/route.ts`

Add the 12 columns above to the `UPDATE emp_details SET ...` column list and its bound
values array, each through the existing `nn()` `'' → null` normalizer. No new validation
required (legacy has none on these fields either).

No `POST /api/employees` (New Employee) changes needed for consistency **unless** you also
want these on that form — legacy's add flow is the same `setups()` action, so the same gap
technically applies there too, but the original request was scoped to the Edit form. Flagging
as an open question rather than assuming.

---

## 4. Phase B — Family/Nominee section

New third-party section beyond a tab-field addition — this is a repeatable-record CRUD block
like Documents, not a handful of scalar inputs.

### 4a. New API routes (mirror the existing Documents pattern exactly)

- `src/app/api/employees/[id]/family/route.ts`
  - `GET` — `SELECT emp_family_pkey, name, DOB, gender, blood_group, relation, nationality, contact_number, alternate_number, emergency_contact, is_nominee, remarks FROM emp_family WHERE emp_fkey = ? AND status = 1 ORDER BY emp_family_pkey DESC` (mirrors `documents/route.ts` GET).
  - `POST` — `INSERT INTO emp_family (emp_fkey, name, DOB, gender, blood_group, relation, nationality, contact_number, alternate_number, emergency_contact, remarks, is_nominee, created_by, status) VALUES (?, ..., 1)` — same column set as the existing `onboard/route.ts` INSERT (line ~152) and the Join-side `family/route.ts` POST, just keyed by `emp_fkey` instead of `emp_join_fkey` and a real `created_by`.
- `src/app/api/employees/[id]/family/[rowId]/route.ts`
  - `DELETE` — soft delete: `UPDATE emp_family SET status = 0 WHERE emp_family_pkey = ? AND emp_fkey = ?` (matches the Documents `[docId]/route.ts` soft-delete pattern, since `emp_family` is the permanent post-onboarding table, not the join-time staging `family` table which the Join routes hard-delete from).

### 4b. `EmployeeDetail.tsx`

- New `useQuery` for `['employee', id, 'family']` → `/api/employees/${id}/family`, alongside
  the existing `documents` query.
- New "Family" tab (6th tab) or a new subsection under an existing tab — recommend a 6th tab
  for consistency with how Documents already gets its own tab, rather than crowding Personal.
- Render as cards, same visual language as the redesigned Documents tab (icon, name +
  relation, DOB/age, nominee badge if `is_nominee = 'Y'`, Delete action) — no "Replace"
  concept needed here (family records don't have a file to swap), so simpler than Documents:
  View isn't applicable either (no file), just Delete + Add.
  - Add-family expandable panel: Name*, Relation* (select — legacy shows a fixed relation
    list, pull exact values from `setups.ctp`'s `#familyModal` relation `<select>` options),
    DOB* (date), Age (readonly, auto-computed from DOB — matches legacy's behavior), Blood
    Group, Gender* (select), Nationality*, Contact Number*, Alternative Number, "Mark as
    Nominee" checkbox → `is_nominee`.
  - Required set matches what `JoinDetail.tsx`'s `RepeatableRows` already marks required for
    its own Family section (added earlier this conversation) — reuse those exact fields:
    name, relation, gender, DOB, contact_number required; nationality optional (per the
    existing `[[Employee_Forms_Validation_Parity_Plan]]`-driven required set already in
    `JoinDetail.tsx`).

---

## 5. Verification checklist (when implemented)

- `npx tsc --noEmit` + `eslint` clean on every touched file, same baseline-diff method used
  in prior phases of this work (`git stash` comparison, not just "0 errors").
- Loading an employee whose `emp_details` row has values in the newly-wired columns shows
  them correctly on first render (no re-seed-clobber regression — reuse the `seeded` ref
  guard already in place).
- Saving after editing one of the new Personal fields persists correctly and round-trips on
  reload.
- International Worker / Physical Handicap conditional reveal behaves like `JoinDetail.tsx`'s
  existing implementation of the same toggles.
- Family: add a member, confirm it appears immediately (query invalidation) and survives a
  reload; delete a member, confirm it disappears (soft-delete, not visible in the `status = 1`
  filtered GET).
- Decide and apply the §2 outcome for `name_as_per_bank`/`branch_address` before or alongside
  this work, so the Bank tab doesn't need a second pass.

---

## 6. Touch list

| File | Phase | Change |
|---|---|---|
| `src/components/employees/EmployeeDetail.tsx` | A | Personal tab: 12 new fields + 2 conditional blocks (port `checkbox()` helper from `JoinDetail.tsx`) |
| `src/app/api/employees/[id]/route.ts` | A | `UPDATE emp_details` — add 12 columns |
| `src/app/api/employees/[id]/family/route.ts` (new) | B | GET, POST |
| `src/app/api/employees/[id]/family/[rowId]/route.ts` (new) | B | DELETE (soft) |
| `src/components/employees/EmployeeDetail.tsx` | B | new Family tab, query, cards, add-panel |
| `src/components/employees/EmployeeDetail.tsx`, `employees/new/page.tsx` | §2 | **DONE** — `name_as_per_bank` removed from both forms + both API routes (`POST /api/employees`, `PUT /api/employees/[id]`). `branch_address` kept (not requested). |

---

## 7. Execution log — 2026-09-14

All of Phase A and Phase B implemented, plus the §2 decision.

- **§2** — `name_as_per_bank` removed from `EmployeeDetail.tsx`, `employees/new/page.tsx`,
  `POST /api/employees`, `PUT /api/employees/[id]` (JSX, seed/state, SQL column list, and
  bound value all removed together). `branch_address` untouched.
- **Phase A** — added to `EmployeeDetail.tsx`'s Personal tab: Address (textarea), District,
  State, Pincode, Guardian Name, Relation to Guardian, WPS ID, Previous Member ID, plus the
  two conditional blocks (International Worker → Country of Origin select; Physical
  Handicap → Locomotive/Hearing/Visual checkboxes), using the `checkbox()` helper ported from
  `JoinDetail.tsx`. One correction versus the plan: legacy's "District" label actually
  persists to `emp_details.city`, not a `district` column (`emp_details` has no such column —
  confirmed against schema). Kept `district` as the Next.js form-state key for clarity but
  bound it to the `city` SQL column in `PUT /api/employees/[id]`, the same pattern already
  used for `bank_branch_name` → `branch_name`. All 12 fields already came back from
  `GET /api/employees/[id]` via its existing `SELECT e.*` — no read-side API change needed.
- **Phase B** — new `src/app/api/employees/[id]/family/route.ts` (GET, POST) and
  `.../family/[rowId]/route.ts` (DELETE, soft via `status = 0`), mirroring the Documents route
  pattern. New 6th "Family" tab in `EmployeeDetail.tsx`: cards (name, relation, computed age,
  contact number, nominee star + badge), Add Family Member panel (Name*, Relation* — select
  with legacy's exact 8-value list: Self/Mother/Father/Sister/Brother/Cousin/Spouse/Other —
  Gender*, DOB*, Blood Group, Nationality, Contact Number*, Alternative Number, Mark as
  Nominee, Mark as Emergency Contact). No "age" column exists on `emp_family` — legacy's Age
  field is a client-side-only display value, so it's computed from DOB for the card display,
  not stored.
- **Verified:** `npx tsc --noEmit` clean; `npx eslint` on every touched file returns **zero**
  errors/warnings — notably including `EmployeeDetail.tsx`, which had 36 pre-existing
  `react-hooks/static-components` errors before this session's earlier redesign removed the
  `InfoRow`/`SectionHeader` in-render component definitions that caused them. Full
  `npx next build` succeeds across all routes, including the two new family API routes.
- **Not verified:** no in-browser smoke test performed.

**Follow-up 2026-09-14 (same day):** `name_as_on_pan` was found to have the exact same
problem as `name_as_per_bank` — present in the not-actually-used `Employee/setup.ctp`
(singular) but absent from `Employee/setups.ctp` (the real edit screen), `EmployeeJoin/setup.ctp`,
and the `emp_join` schema. Removed from `EmployeeDetail.tsx`, `employees/new/page.tsx`,
`POST /api/employees`, and `PUT /api/employees/[id]` (JSX, seed/state, SQL column list, and
bound value each). Verified `tsc --noEmit` and `eslint` clean on all four files.
