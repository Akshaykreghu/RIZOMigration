# Employee Join & Onboarding — Consolidation & Parity Plan

**Date:** 2026-09-03
**Scope:** `/employees/join` — the New Join form, the Edit (JoinDetail) form, and the
Continue-Onboarding form, plus their API routes.
**Status:** plan only — nothing implemented yet.

Supersedes the open items in `Employee_Join_Dev_Fix_List.md` that concern the forms
themselves (JOIN-001, JOIN-002, JOIN-003, JOIN-004, JOIN-006). The list's list/search/
pagination items (JOIN-007…JOIN-014) are out of scope here.

---

## 1. Why

Three problems, all in the same module:

1. **Redundant data entry.** `NewJoinForm` collects Personal + Statutory + Bank, then on
   save hands off to `JoinDetail`, which re-presents those same three groups as steps 1–3
   (now pre-filled) and adds a 4th "Additional" step. The user fills the same fields twice.
2. **Onboarding form is missing legacy fields.** Notice Period, Shift Timings, Holiday
   Calendar, Leave Policy, Superior, the full Employee Type list, and the Contract-period /
   Probation-days conditionals are all absent, and none of the employment fields are
   required. See §6.
3. **Statutory validation is not wired in.** `panError` / `pincodeError` already exist in
   `src/lib/validation.ts` (Company Setup uses them) but the join forms and routes only
   call `dobError` / `mobileError` / `aadhaarError`. PAN, ESI, UAN, LWF, PF, account number
   and PIN have no format check on either the client or the server. See §7.

---

## 2. Current components

| Component | File | Shape | Fields |
|---|---|---|---|
| `NewJoinForm` | `src/components/employees/NewJoinForm.tsx` | single scroll | Personal · Statutory · Bank |
| `JoinDetail` | `src/components/employees/JoinDetail.tsx` | 4-step page-turn stepper | Personal · Statutory · Bank · Additional (documents / education / experience / family) |
| `OnboardForm` | `src/components/employees/OnboardForm.tsx` | single page, 3 sections | Identity & Login · Professional · Salary |

Routes involved:

- `POST /api/employees/join` — create join record
- `PUT  /api/employees/join/[id]` — update join record
- `POST /api/employees/join/[id]/onboard` — convert join record → active employee
- `GET  /api/setup/{branches,departments,designations,grades,notice-periods,shifts,holiday-groups,leavepolicy-groups,salary-structures,banks}` — all already exist

---

## 3. Target architecture

- **One join form.** Delete `NewJoinForm`. `JoinDetail` gains a create mode (`id` optional)
  and is the only join form — used for both "New Join" and "Edit".
- **One stepper shell.** Extract the page-turn / stepper machinery out of `JoinDetail`
  into a shared `SteppedForm` component. `JoinDetail` and `OnboardForm` both become step
  definitions + field renderers on top of it.
- **Two steppers, not one.** Join and onboarding stay separate flows — a pending applicant
  can sit for weeks, and onboarding needs data (username, password, branch, salary
  structure) HR often does not have on day 1. They share the shell, not the lifecycle.

```
SteppedForm  (shared shell: stepper header, page-turn transitions,
              sticky footer, per-step validation gate)
├── JoinDetail   steps: Personal → Statutory → Bank → Additional
└── OnboardForm  steps: Identity & Login → Employment → Policies & Rules → Salary
```

---

## 4. Phase 0 — consolidate

**0.0 Shared setup-lookup hook — DONE (2026-09-03).**
Root cause of the "branch dropdown sometimes blank" bug: every `/api/setup/*` list was
fetched ad hoc under a bare `queryKey: ['setup/x']` with divergent `queryFn` transforms
(raw rows / `{code,name}` / `{value,label}`). React Query keys the cache by key alone, so
the first screen to mount won the entry and every other consumer read a shape it could not
render. Fixed by `src/lib/setupOptions.ts` — `useSetupRows(path)` (raw) and
`useSetupOptions(path, valueKey, label)` (`select` → `{value,label}` with `value`/`label`
fallback), both keyed `[path]` with one shared raw `queryFn`. All 24 consumers migrated
(7 `useLookup` copies, `EmployeeDetail`'s local `useSetupOptions`, the inline `{value,label}`
and `{branch_code,branch_name}` mappers, and the raw `useQuery` callers). `assets` /
`expenses` setup lists left as-is (single-consumer, no collision).

**0.1 Extract `SteppedForm` — MOVED to Phase 1.** Deferred until `OnboardForm` exists as a
second consumer, so the extraction can be verified against both at once instead of
speculatively refactoring the one component that already works. `JoinDetail` keeps its
own inline stepper for now.

**0.2 `JoinDetail` create mode — DONE (2026-09-04).**
- Prop is now `id?: string`. No `id` → `isCreate`: `useQuery` disabled, `form` seeded from
  the in-file `EMPTY_FORM` (carried over from `NewJoinForm`), stepper renders normally.
- Step 1 "Save & Continue": no id yet → `POST /api/employees/join`; the returned
  `emp_join_pkey` is held in `createdId` and surfaced via a new `onCreated(id)` prop.
  Steps 2+ → `PUT /api/employees/join/{effectiveId}` as before.
- New `seeded` / `justCreated` refs stop the post-save refetch from clobbering edits and
  stop the create→edit `id`-prop transition from re-seeding the form.
- Step 4 (Additional) shows a "save previous steps first" note in create mode; its
  child-row endpoints use `effectiveId`.
- Partial records are still created on step 1 — no regression to "save and come back later".

**0.3 Rewire call sites — DONE (2026-09-04).**
- `employees/join/new/page.tsx` → `<JoinDetail onCreated={id => router.replace('/employees/join/'+id)} />`
  (no `id`). Same land-on-`/[id]` behaviour as the old `NewJoinForm`.
- `employees/join/page.tsx` → the **single** wide edit modal now hosts both create and edit:
  "New Join" does `setSelectedJoinId(null); setModalOpen(true)`; `handleJoinCreated` adopts
  the new id so the same open modal continues through the remaining steps. The separate
  `newJoinOpen` modal and `NewJoinForm` import are removed.
- `employees/join/[id]/page.tsx` → unchanged (still passes `id`).
- `src/components/employees/NewJoinForm.tsx` — **deleted.**
- `tsc` clean; ESLint 0 new errors on the touched files.

**0.4 Retest JOIN-006** — still to verify in-app (see test checklist). The `NewJoinForm →
JoinDetail` hand-off that caused the stuck `Loading…` no longer exists; in the modal path
there is no navigation at all, and the create-mode Loading guard is skipped once the form
is populated.

---

## 5. Phase 1 — onboarding parity

**5.0 Extract `SteppedForm`** (moved up from Phase 0.1). New
`src/components/employees/SteppedForm.tsx` owning: `steps[]` metadata, `step` / `completed`
state, the page-turn transition + height lock, the sticky header stepper, the sticky footer
(Back / Cancel / Save & Continue / Done), and a per-step `validateStep(idx)` gate.
`JoinDetail` and the new `OnboardForm` both build on it. Do this as a pure refactor of
`JoinDetail`'s current inline stepper first and diff it visually before wiring `OnboardForm`.

**5.1 Rebuild `OnboardForm` on `SteppedForm`**

Four steps:

| # | Step | Fields |
|---|---|---|
| 1 | Identity & Login | Employee ID, Login Username\*, Initial Password\* |
| 2 | Employment | Joining Date\*, Employee Type\*, Branch\*, Department\*, Designation\*, Grade, Category (`emp_category`, see 5.7), Notice Period\*, Probation Days (only when type = Probation), Contract Period start/end (only when type = Contract, end required), Reporting Manager / Superior |
| 3 | Policies & Rules | Shift Timings\*, Holiday Calendar\*, Leave Policy\* |
| 4 | Review | read-only summary of steps 1–3 before "Complete Onboarding" |

(`\*` = *marked* required in the UI; hard enforcement is **deferred** per §11 decision 2 —
Phase 2 only wires the statutory-format validators, not mandatory employment fields.)

**No Salary step.** Legacy's EmployeeJoin onboarding tab does not collect a salary
structure or CTC — salary structure is assigned afterward through **Bulk Policies →
Salary** (`/api/employees/bulk-policies/salary`, which runs `sal_structure_distribution_fn`
and writes `emp_salary_structure` + the `emp_config` SALARY row), and CTC through the
employee edit screen. The current `OnboardForm` "Salary" section (Salary Structure + Annual
/ Monthly CTC) is removed. See 5.6 for the matching route change.

**5.2 New fields (all from existing setup endpoints)**

| Field | Endpoint | Option value → column |
|---|---|---|
| Notice Period | `/api/setup/notice-periods` | `notice_days` → `emp_proff.notice_days` |
| Shift Timings | `/api/setup/shifts` | `day_time_seq` → `emp_proff.day_time_seq` |
| Holiday Calendar | `/api/setup/holiday-groups` | `HOLIDAY_GROUP_ID` → `emp_proff.HOLIDAY_GROUP_ID` |
| Leave Policy | `/api/setup/leavepolicy-groups` | `LEAVEPOLICY_GROUP_ID` → `emp_proff.LEAVEPOLICY_GROUP_ID` |

**5.3 Employee Type — replace the 4-item list with legacy's set**

`Permanent, Contract, Probation, Part-Time, Temporary, Consultant, DAILY WAGES,
HOURLY WAGES, Provisional, Deputation, other` (values verbatim from
`View/EmployeeJoin/onboarding.ctp`). Drop the non-legacy "Trainee" / "Intern".

**5.4 Grade value fix (bug)**

`OnboardForm` currently maps the grade option value to `grade_code`; legacy stores
`grade_pkey` in `emp_proff.emp_grade`. Change the option value to `grade_pkey`.

**5.5 Conditionals**

- Probation Days: show only when Employee Type = `Probation`.
- Contract Period (start = joining date, readonly; end = date, required): show only when
  Employee Type = `Contract`; validate end > start.

**5.6 `POST /api/employees/join/[id]/onboard` changes**

- Persist `notice_days`, `day_time_seq`, `HOLIDAY_GROUP_ID`, `LEAVEPOLICY_GROUP_ID` on the
  `emp_proff` INSERT.
- Mirror legacy's `EmployeeJoin/saveconfig` for the Policies & Rules + Superior side
  effects — the `emp_config` rows (`GRADE`, `HIERARCHY`, `MSHIFT` types) legacy writes.
  **Verify the exact writes against `EmployeeJoinController::saveconfig` /
  `saveconfigs()` before implementing** — don't guess the row shapes.
- Contract end date: store per whatever column legacy's onboarding uses (`end_date` /
  `start_date` on `emp_proff` — confirm).
- **Drop salary-structure/CTC handling.** Remove `structure_id`, `emp_anual_ctc` and
  `emp_monthly_ctc` from the request body, the `emp_proff` INSERT column list, and the
  `emp_ctc_upload` INSERT. `emp_proff.structure_id` must be left NULL — it is set later by
  the `emp_config_bi` trigger when Bulk Policies → Salary inserts the `emp_config` SALARY
  row. Writing it here directly is a latent bug: it gives the employee a structure pointer
  with **no `emp_salary_structure` rows** (so payroll computes nothing) and **no
  `emp_config` SALARY row** (so Bulk Policies shows them as neither allocated nor a
  candidate — `p.structure_id IS NOT NULL` excludes them — and you cannot fix it from that
  screen without first clearing the column).
- Keep the existing duplicate-check block (PAN / Aadhaar / ESI / UAN / LWF / account) — it
  already has parity.

**5.7 Category (`emp_category`)** — **in scope** (decided 2026-09-04). Add a Category
dropdown to the Employment step, sourced from the category table, persisted to
`emp_proff.emp_category`. Legacy's Category field is tenant-gated in `onboarding.ctp`
(commented `if ($user === 'DEMO' || 'KWMT')` block) and, when shown, drives a
category→grade cascade (`Employee/getGrade/{categoryId}`). Confirm before building whether
this tenant wants the plain dropdown or the full category→grade cascade.

---

## 6. Onboarding field gap reference

| # | Field | Legacy | Next.js now | Action |
|---|---|---|---|---|
| G1 | Notice Period | required select from `notice_period` | **DONE 2026-09-14** — select added to `OnboardForm` (`setup/notice-periods`), persisted to `emp_proff.notice_days` in `onboard/route.ts` | closed |
| G2 | Shift Timings | required select | **DONE 2026-09-14** — select added (`setup/shifts`), persisted to `emp_proff.day_time_seq` | closed |
| G3 | Holiday Calendar | required select | **DONE 2026-09-14** — select added (`setup/holiday-groups`), persisted to `emp_proff.HOLIDAY_GROUP_ID` | closed |
| G4 | Leave Policy | required select | **DONE 2026-09-14** — select added (`setup/leavepolicy-groups`), persisted to `emp_proff.LEAVEPOLICY_GROUP_ID` | closed |
| G5 | Superior / Reporting Manager | autocomplete → `attr1` + `emp_config` HIERARCHY | field present → `attr1` only | add `emp_config` write (5.6) |
| G6 | Employee Type options | 11 values | **DONE 2026-09-14** — `OnboardForm` now uses the full legacy 11-value list | closed |
| G7 | Contract Period start/end | shown + required when type = Contract | absent | add conditional (5.5) |
| G8 | Probation Days | shown only when type = Probation | always shown | gate on type (5.5) |
| G9 | Grade value | `grade_pkey` | `grade_code` (bug) | fix (5.4) |
| G10 | Required employment fields | Joining Date, Branch, Dept, Designation, Type, Notice all required | none required | Phase 2 |
| G11 | Category (`emp_category`) | present (tenant-gated), drives category→grade cascade | absent | add to Employment step (5.7) — decided in scope 2026-09-04 |
| G12 | Salary Structure / CTC | not on the onboarding tab — done later via Bulk Policies / CTC upload | `OnboardForm` has a Salary Structure select + CTC that half-saves (`emp_proff.structure_id` written directly, no `emp_salary_structure`, no `emp_config` SALARY) | **DONE 2026-09-04** — Salary Structure + Annual/Monthly CTC inputs removed from **all three** employee forms (`OnboardForm`, `employees/new`, `EmployeeDetail` edit mode; `EmployeeDetail` keeps a read-only display), and `structure_id` / `emp_ctc_upload` writes removed from `POST /api/employees/join/[id]/onboard`, `POST /api/employees`, and `PUT /api/employees/[id]`. Salary structure → Bulk Policies → Salary; CTC → CTC-upload step. |

---

## 7. Phase 2 — validation parity

**7.1 Add to `src/lib/validation.ts`**

| Function | Rule | Source |
|---|---|---|
| `esiError` | `^\d{10}$` | legacy `setup.ctp` submit check |
| `uanError` | `^\d{12}$` | legacy `setup.ctp` submit check |
| `lwfError` | `^[A-Za-z0-9]{5,15}$` | legacy `setup.ctp` submit check |

`panError` and `pincodeError` already exist — reuse as-is.

**7.2 Wire into the join form (`JoinDetail`) — step 1 & step 2**

| Field | Validator | Notes |
|---|---|---|
| PAN | `panError` | optional; validate only when non-empty |
| ESIC Number | `esiError` | optional |
| UAN (Company PF) | `uanError` | optional |
| LWF Code | `lwfError` | optional |
| Pincode | `pincodeError` | optional |
| Aadhaar / ID Card | `aadhaarError` | already wired; keep |
| Mobile | `mobileError` | already wired; keep |
| DOB | `dobError` | already wired; keep (better than legacy — no upper bound in legacy) |

Same field-error pattern already used for DOB / mobile / gender in `JoinDetail`
(`fieldErrors` + `ERROR_INPUT_CLASS` + `FieldError`).

**7.3 Enforce server-side**

- `POST /api/employees/join` and `PUT /api/employees/join/[id]`: run
  `panError / esiError / uanError / lwfError / pincodeError` (plus the existing three) and
  return `400` with the message on failure. Client checks are a convenience; the route is
  the gate (this is the JOIN-001 / JOIN-003 concern — bad input must not be able to strand
  a record).

**7.4 Required employment fields (onboarding) — DEFERRED** (§11 decision 2).

Not in the current build. Employment/policy fields are visually marked `*` but neither
`SteppedForm`'s `validateStep` nor `POST /api/employees/join/[id]/onboard` will hard-block
on them yet. When revisited: gate "Save & Continue" on Joining Date, Employee Type, Branch,
Department, Designation, Notice Period (Employment) and Shift, Holiday, Leave (Policies),
plus a matching `400` server-side — and decide then whether Policies & Rules is hard or
soft (warn-and-allow), since hard-blocking prevents saving a partially-onboarded record.

---

## 8. Phase 3 — consistency fixes

- **Marital Status casing.** Legacy stores lowercase (`single`, `married`, `divorced`,
  `widowed`); `JoinDetail` writes capitalised. Normalise the option values to legacy
  casing and lower-case on read so existing rows still match.
- **Blood Group** — `JoinDetail` already uses a dropdown (legacy is free text); keep.
- **District / State** — stay free text (no setup source; matches legacy).

---

## 9. Phase 4 — bank name typeahead (typable, with suggestions)

Bank Name stays a normal typable text input — **not** a forced dropdown. Add a
`<datalist>` of names from `/api/setup/banks` so the user gets suggestions while typing but
can enter any value.

- `JoinDetail` step 3 (Bank): `<input list="bank-names" {...f('bank')} />` +
  `<datalist id="bank-names">` populated from the banks query.
- Optional convenience: when the typed value exactly matches a suggestion, offer to
  auto-fill Bank Branch + IFSC from that row — user can still overwrite both.
- **No schema change.** `emp_join.bank` stays a name string, not an FK. This matches legacy
  (free text) and just layers suggestions on top.

---

## 10. API change summary

| Route | Change |
|---|---|
| `POST /api/employees/join` | add `panError / esiError / uanError / lwfError / pincodeError` → 400 |
| `PUT /api/employees/join/[id]` | same validation set → 400 |
| `POST /api/employees/join/[id]/onboard` | persist `notice_days`, `day_time_seq`, `HOLIDAY_GROUP_ID`, `LEAVEPOLICY_GROUP_ID`; write `emp_config` GRADE / HIERARCHY / MSHIFT rows (verify against legacy `saveconfig` first); store contract end date; require the employment + policy fields → 400; **remove `structure_id` / `emp_anual_ctc` / `emp_monthly_ctc` handling and the `emp_ctc_upload` INSERT — salary structure is a Bulk Policies step, and `emp_proff.structure_id` must stay NULL for its trigger to set it** |
| setup endpoints | none — all consumed endpoints already exist |

---

## 11. Decisions

1. **Onboarding shape** — **4-step stepper** (decided 2026-09-04): Identity & Login →
   Employment → Policies & Rules → *(no Salary step, per G12)*.
2. **Hard-required employment fields** — **deferred** (decided 2026-09-04). Phase 2 wires the
   format validators (PAN/ESI/UAN/LWF/pincode) but does **not** make branch/dept/designation/
   type/notice/shift/holiday/leave mandatory yet. Revisit later.
3. **Category (`emp_category`)** — **in use** (decided 2026-09-04). Add a Category dropdown to
   the Employment step (source: category table) and persist to `emp_proff.emp_category`.
   Confirm whether it's still tenant-gated in legacy or now global.
4. **`/employees/join/new` route** — **keep it** (decided 2026-09-04), repointed to render
   `<JoinDetail />` in create mode (no `id`); `router.replace` to `/employees/join/{id}` on
   first save. The list-page "New Join" modal stays as the quick-add path. Note: the route is
   currently orphaned (no UI links to it — the button opens the modal), so keeping it is a
   deliberate choice to provide a deep-linkable full-page create flow, better for the 4-step
   stepper than a modal.

**Decided (2026-09-03):** salary structure assignment is **not** part of onboarding — it is
done through Bulk Policies → Salary (`/api/employees/bulk-policies/salary`). The onboarding
form's Salary section is dropped and the onboard route stops touching `structure_id` / CTC
(G12, §5.1, §5.6).

**Implemented (2026-09-04):** scope confirmed as **all three employee forms** (onboard, Add
Employee, Edit Employee) and **CTC dropped too**, not just the structure. Salary Structure +
Annual/Monthly CTC inputs removed from `OnboardForm.tsx`, `employees/new/page.tsx`, and
`EmployeeDetail.tsx` (edit mode — read-only display kept). `structure_id` and the
`emp_ctc_upload` writes removed from `POST /api/employees/join/[id]/onboard`,
`POST /api/employees`, and `PUT /api/employees/[id]`. `tsc` clean, no new lint.

---

## 12. Risks

- **`SteppedForm` extraction** touches the one component that already works well
  (`JoinDetail`) — do 0.1 as a pure refactor with the existing form and verify no visual /
  behaviour change before 0.2.
- **`emp_config` writes in the onboard route** — legacy's `saveconfig` is a large,
  much-edited method. Read it end-to-end and match row shapes exactly; a wrong `type`
  string or missing row breaks shift / hierarchy / grade resolution downstream.
- **Grade value change (G9/5.4)** — any existing `emp_proff.emp_grade` rows written by the
  current Next.js onboard flow hold `grade_code`, not `grade_pkey`. Check whether any exist
  in dev data and backfill if so.
- **Required-field enforcement** vs. existing partially-onboarded records — a stricter
  route will reject edits to rows that predate the rule. Confirm none exist, or gate the
  new 400s to newly-created records.

---

## 13. Test checklist (per phase)

**Phase 0**
- Create a join record from `/employees/join/new` → lands on step 2, URL is
  `/employees/join/{id}`, refresh keeps you there.
- Create from the list-page "New Join" modal → modal stays open on step 2, list refreshes.
- Edit an existing record → all four steps load pre-filled, jump between completed steps.
- No `Loading…` hang after step 1 (JOIN-006).

**Phase 1**
- Every new dropdown lists Company Setup options and round-trips (save → reopen → value
  retained).
- Employee Type = Probation shows Probation Days only; = Contract shows Contract Period
  only and rejects end ≤ start.
- After Complete Onboarding, inspect `emp_proff` + `emp_config` — `notice_days`,
  `day_time_seq`, `HOLIDAY_GROUP_ID`, `LEAVEPOLICY_GROUP_ID`, grade pkey, hierarchy row all
  written.

**Phase 2**
- Each of PAN / ESI / UAN / LWF / Pincode: bad format blocked on the client *and* by a
  direct `curl` to the route (400).
- Onboard with a missing required employment field → blocked client-side and 400 from the
  route.

**Phase 3**
- Marital Status saved from the form matches legacy casing in the DB; an existing
  capitalised row still displays selected.

**Phase 4**
- Bank Name shows suggestions while typing; a value not in the list still saves.
- Picking a suggestion offers branch / IFSC autofill; both remain editable.
