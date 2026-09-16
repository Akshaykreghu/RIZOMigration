# Employee Forms — Mandatory Field & Type-Check Parity Plan

**Date:** 2026-09-07
**Scope:** bring the Next.js Employee Join form, the Continue-Onboarding form, and the
Employee edit form (plus their API routes) up to the legacy forms' mandatory-field and
format-validation behaviour.
**Status:** Phases 0, 1, 2, 5, 6 implemented 2026-09-14 (see §11). Phase 3 (Onboarding) stays
deferred, blocked on the `Employee_Join_Onboarding_Gap_Plan.md` §5 `SteppedForm` rebuild.

### Resolved decisions (2026-09-07)

1. **Aadhaar required on the Join form** — yes.
2. **Aadhaar on the Employee edit form** — **mandatory**, always. Editing a legacy row that
   has no Aadhaar is blocked until one is entered (not allowed-if-untouched). Same for the
   Join edit (`PUT /api/employees/join/[id]`): Aadhaar is required even on partial save.
3. **Statutory format checks on the Employee edit form** — yes, add them (legacy senior form
   has none).
4. **Uniqueness checks** — on **both** join-save time and onboard time (PAN / Aadhaar / ESI /
   UAN / LWF / account vs active `emp_details`).
5. **Onboarding** — **Phase 3-A** (fold validation into the `Employee_Join_Onboarding_Gap_Plan.md`
   §5 `SteppedForm` rebuild). Chosen because the rebuilt form carries the full legacy field
   set (notice period, shift/holiday/leave, contract conditionals, 11-value type list), so
   3-A is closer to legacy than bolting `required` onto the stale form.
6. **Red asterisk on every mandatory field** (2026-09-07) — every field that is
   hard-required (client gate and/or server `400`) must render a red `*` next to its label,
   across all three forms and their sub-modals. This includes First Name, which is now
   mandatory everywhere it appears. Conditionally-required fields (e.g. Contract end date)
   show the `*` only while the condition holds.

Complements `Employee_Join_Onboarding_Gap_Plan.md` (§7 "validation parity" — deferred there).
This plan is the validation-only slice. The onboarding *field-gap* work (notice period,
shift/holiday/leave, contract conditionals, the 11-value employee-type list) stays in that
document; §Phase 3 below notes where the two must be sequenced.

---

## 1. What legacy actually enforces (reference)

Extracted from `View/EmployeeJoin/setup.ctp`, `View/EmployeeJoin/onboarding.ctp`,
`View/Employee/setup.ctp`, and `EmployeeJoinController::checkIdCard/checkPan/checkESI/checkUAN/checkLWF`.

### Employee Join — "New Join / Edit" (`setup.ctp`, form `#employee-form`)

Only legacy form with real statutory format validation.

**Mandatory (`required`):** First Name, Date of Birth, Gender (`classification`),
Nationality (`nationality_id`), Aadhaar (`id_card`).

**Format checks** (submit handler `setup.ctp:2919`, re-run server-side in the `check*` actions
which also do a uniqueness check vs active `emp_details`):

| Field | Rule | Required? |
|---|---|---|
| Aadhaar (`id_card`) | `/^\d{12}$/` | **yes** + uniqueness |
| PAN (`pan_no`) | `/^[A-Z]{5}[0-9]{4}[A-Z]$/`, auto-uppercased | optional; checked if filled + uniqueness |
| ESI (`esi`) | `/^\d{10}$/` | optional; checked if filled + uniqueness |
| UAN (`company_pf`, id `uan`) | `/^\d{12}$/` | optional; checked if filled + uniqueness (vs `pf` col) |
| LWF (`lwf_code`) | `/^[A-Za-z0-9]{5,15}$/`, auto-uppercased | optional; checked if filled + uniqueness |
| Account No (`account_no`) | `/^\d+$/` | optional; live-validate only, not in submit gate |
| PF (`pf`) | `/^[A-Za-z0-9]+$/` | optional; live-validate only |
| Email | `type=email` + strips non `[a-zA-Z0-9@._-]` | optional |
| IFSC (`ifsc_code`) | **no format regex** — strips non-alnum, maxlength 12 | optional |

`maxlength` caps: Aadhaar 12, PAN 10, ESI 10, UAN 12, LWF 15, account 18, PF 22, mobile 10,
pincode 6. DOB datepicker `endDate: '-18y'` (picker-only 18+ guard, bypassable by typing).

**Sub-modals (all fields `required`):**
- Education: Course, University, Duration, Marks
- Experience: Company, Department, Designation, From Date, To Date, Salary
- Family: Name, Relation, DOB, Age, Blood Group, Gender, Nationality, Primary Contact
- Documents: Document Name, Document Type, Document Number, Relation, Valid From, File

### Employee Join — "Continue Onboarding" (`onboarding.ctp`)

No statutory fields, no format validation.

**Mandatory:** Company Info form — Joining Date, Branch, Department, Designation, Employee
Type, Notice Period. Policies & Rules form — Shift Timings, Holiday Calendar, Leave Policy.
Not required: Employee ID, Grade, Superior.

**Conditionals:** Probation Days shown only when type = `Probation`; Contract Period
(`end_date` **required**, `end > start`) shown only when type = `Contract`; Category
(`emp_category`) `required` only for tenants DEMO/KWMT; Area (`attr4`) `required` only for
VGFS/VSFS. Employee Type has 11 values (`Permanent, Contract, Probation, Part-Time,
Temporary, Consultant, DAILY WAGES, HOURLY WAGES, Provisional, Deputation, other`).

### Legacy senior Employee edit (`View/Employee/setup.ctp`)

Weakest of the three.

**Mandatory:** First Name, Last Name, Gender, Date of Birth, Nationality, ID/Aadhaar, plus
nominee block (Nominee Name, Relation, Nominee DOB); Joining Date, Employee Type, Notice
Period, Department, Designation, Branch; Shift, Holiday, Leave, Salary Structure; Country of
Origin only if "International Worker" checked.

**Type checks:** DOB vs joining date — submit blocked unless the person is **≥18 at the
joining date** (`setup.ctp:1001-1037`); for Contract type it instead requires `end_date` and
`start < end`. Name field `/^[a-zA-Z\s]*$/`. **No Aadhaar/PAN/ESI/UAN/PF/IFSC/account/LWF
format check at all** — Aadhaar is only `required`.

### Server-side

`EmployeeJoinController::saveemployeesetup()` / `saveconfigs()` persist `request->data` as-is
— no validation. The only server-side format enforcement is inside the `check*` AJAX
endpoints, invoked only by client JS. A direct POST bypasses every check.

---

## 2. Current Next.js state

- `src/lib/validation.ts` has: `futureDateError`, `dobError` (**already includes an 18-year
  floor**), `mobileError` (`\d{10}`), `aadhaarError` (`\d{12}`), `panError`, `tanError`,
  `cinError`, `pincodeError` (`[1-9]\d{5}`), `emailError`, `websiteError`,
  `landlinePhoneError`. **Missing:** ESI, UAN, LWF, account-number, PF-number helpers.
- `JoinDetail.tsx` — `validateStep0` checks `date_of_birth` (dobError), `mobile_no`,
  `id_card` (format only, **not required**), `classification` (required). Steps 1 (Statutory)
  and 2 (Bank) have **no validation**. Additional-step `RepeatableRows` has no `required`
  support.
- `POST /api/employees/join` — `dobError || mobileError || aadhaarError`, plus
  `classification` required. `PUT /api/employees/join/[id]` — same three, each guarded by
  `body.x !== undefined`, plus "Gender required when submitted".
- `OnboardForm.tsx` — **pre-gap-plan**: `Trainee`/`Intern` types, no notice period, no
  Policies section, `<style jsx>`. Only `username` + `password` marked `required` (no client
  gate on the professional fields). `POST /api/employees/join/[id]/onboard` — checks only
  `username`/`password`; already does the PAN/Aadhaar/ESI/UAN/LWF/account uniqueness check
  vs active `emp_details`.
- `EmployeeDetail.tsx` — **no client validation at all**. `PUT /api/employees/[id]` — coerces
  `'' → null` via `nn()`, returns JSON errors, but runs **no** required/format checks.

---

## 3. Phase 0 — shared validators (`src/lib/validation.ts`)

Add, following the existing pattern (return `null` for empty so blank is never "invalid"):

| Helper | Rule | Legacy source |
|---|---|---|
| `esiError` | `/^\d{10}$/` | `setup.ctp:2971`, `checkESI()` |
| `uanError` | `/^\d{12}$/` | `setup.ctp:2977`, `checkUAN()` |
| `lwfError` | `/^[A-Za-z0-9]{5,15}$/` (test uppercased) | `setup.ctp:2985`, `checkLWF()` |
| `accountNoError` | `/^\d+$/` | `setup.ctp:2907` |
| `pfNumberError` | `/^[A-Za-z0-9]+$/` | `setup.ctp:2911` |

Plus one composite to avoid repeating the chain in 5 call sites:

```ts
// Runs every statutory-format check legacy's Employee Join form enforces.
// Returns the first error message, or null. `aadhaarRequired` toggles required
// behaviour (join form requires Aadhaar; edit form only format-checks when present).
export function statutoryFieldErrors(
  v: {
    id_card?: string; pan_no?: string; esi?: string; company_pf?: string;
    lwf_code?: string; account_no?: string; pf?: string; pincode?: string;
  },
  opts?: { aadhaarRequired?: boolean }
): string | null
```

Plus, for the senior-form "18 at joining date" rule (Phase 2):

```ts
// DOB must be >= `MIN_WORKING_AGE_YEARS` before `onDate` (the joining date). Legacy
// View/Employee/setup.ctp:1001-1037.
export function ageAtDateError(dob: string, onDate: string): string | null
```

`dobError` already enforces "at least 18 today" — stricter than legacy's picker-only
`endDate: '-18y'` on the new-join form. Keep it; flag for sign-off (decision 1).

### 3z. Required-field marker convention (decision 6)

Every hard-required field gets a red `*` next to its label. Use one shared marker so it is
consistent and greppable:

```tsx
// src/components/ui/RequiredMark.tsx
export const RequiredMark = () => (
  <span className="text-[color:var(--color-danger)]"> *</span>
);
```

Rules:
- Render `<RequiredMark />` for a field iff that field has a client gate and/or a server
  `400` for being empty.
- Conditionally-required fields (Contract end date, tenant-gated Category/Area) render the
  mark only while the condition is active.
- Format-only fields (PAN, ESI, UAN, LWF, account, PF, pincode when optional) get **no**
  mark — a blank value is valid.
- The per-form mandatory lists in Phases 1–3 below are the authoritative set of fields that
  must carry the mark.

---

## 4. Phase 1 — Employee Join form

### 4a. `JoinDetail.tsx`

**Mandatory fields (each gets `<RequiredMark />`):**
- Step 0 Personal: **First Name**, Date of Birth, Gender (`classification` — already marked),
  Aadhaar / ID Card (`id_card`), Nationality (`nationality_id`).
- Step 1 Statutory / Step 2 Bank: none (format-only — no marks).
- Step 3 sub-modals: Education — Course, University, Duration, Marks; Experience — Company,
  Designation, Department, From Date, To Date, Salary; Family — Name, Relation, Date of Birth,
  Gender, Contact Number; Documents — Type, Number, Name on Document, Relation, Valid From.

**Work:**
- **`validateStep0`** — add required: `first_name`, `nationality_id`, `date_of_birth`; make
  `id_card` **required** (currently format-only). Keep `classification`, `mobile_no`.
- **New `validateStep1`** (Statutory) — `statutoryFieldErrors({ pan_no, esi, company_pf,
  lwf_code })`; wire per-field messages into the existing `fieldErrors` / `FieldError` /
  `ERROR_INPUT_CLASS` machinery.
- **New `validateStep2`** (Bank) — `accountNoError(account_no)`, `pfNumberError(pf)`.
- `saveAndContinue()` / `finish()` — dispatch to `validateStep{0,1,2}` by `step` (today only
  step 0 is gated).
- Add `<RequiredMark />` to the First Name, Date of Birth, Aadhaar and Nationality labels
  (Gender already has one).
- `maxLength`: PAN 10, ESI 10, UAN 12, LWF 15, account 18, PF 22, pincode 6. Uppercase-on-input
  for `pan_no` and `lwf_code`.
- **Additional step** — add `required?: boolean` to `RepeatableFieldDef`; `RepeatableRows.tsx`
  renders `<RequiredMark />` on those column headers and `handleAdd` refuses a draft with any
  required field blank. Mark the sub-modal fields listed above.

### 4b. `POST /api/employees/join/route.ts`

```ts
const err = dobError(body.date_of_birth ?? '')
  || mobileError(body.mobile_no ?? '')
  || statutoryFieldErrors(body, { aadhaarRequired: true })
  || (!body.first_name ? 'First name is required' : null)
  || (!body.classification ? 'Gender is required' : null)
  || (!body.nationality_id ? 'Nationality is required' : null);
if (err) return NextResponse.json({ error: err }, { status: 400 });
```

### 4c. `PUT /api/employees/join/[id]/route.ts`

Format checks run **only when the field is present** (`body.x !== undefined`) so partial
save keeps working — **except Aadhaar**, which is `aadhaarRequired: true` here too (decision
2): if `id_card` is submitted it must be a valid 12-digit value, and a save that would leave
the record without an Aadhaar is rejected. Keep "Gender required when submitted".

### 4d. Uniqueness — in scope, both places (decision 4)

Legacy re-checks PAN / Aadhaar / ESI / UAN / LWF / account vs active `emp_details` on submit;
the onboard route already does this at onboarding time. Add the same check at **join-save
time** — in 4b (POST) and 4c (PUT):

```sql
SELECT 1 FROM emp_details
WHERE status = 1
  AND (pan_no = ? OR id_card = ? OR esi = ? OR company_pf = ? OR lwf_code = ? OR account_no = ?)
```

- Bind only the values that are non-empty in the body (skip empty terms so a blank field
  doesn't match every blank `emp_details` row).
- On PUT there is no self-row to exclude (`emp_join` and `emp_details` are separate tables),
  so no `emp_pkey !=` clause is needed here — unlike the legacy `check*` actions which run
  against `emp_details` for both create and edit.
- Return `409` with `An active employee already exists with a matching PAN / Aadhaar / ESI /
  UAN / LWF / account number`.
- Keep the existing onboard-time check in `onboard/route.ts` as-is (defence in depth — the
  applicant may sit for weeks and a colliding employee could be added in between).

---

## 5. Phase 2 — Employee edit form

Bring the edit form up to the modern join-form bar — statutory format checks added even
though the legacy senior form has none (decision 3), and Aadhaar mandatory always (decision
2).

### 5a. `EmployeeDetail.tsx`

**Mandatory fields (each gets `<RequiredMark />` — this form has no marks today):**
**First Name**, Last Name, Date of Birth, Gender (`classification`), ID Card / Aadhaar
(`id_card`).

**Work:**
- Add `fieldErrors` state + inline error rendering (none today), and `<RequiredMark />` on
  the five labels above.
- Precondition on `update.mutate()`: required `first_name`, `last_name`, `classification`,
  `date_of_birth`, `id_card` (Aadhaar — always, even if the loaded row came in blank);
  `dobError(date_of_birth)`; when `joining_date` is set,
  `ageAtDateError(date_of_birth, joining_date)`; `statutoryFieldErrors(form, { aadhaarRequired: true })`.
- Fix stale `emp_type` list (`Trainee`/`Intern` → the 11 legacy values via the Phase 3
  shared constant).

### 5b. `PUT /api/employees/[id]/route.ts`

After `nn()` normalisation: required `first_name`/`last_name`/`classification`/
`date_of_birth`/`id_card` whenever the key is present in the body **and** `id_card` required
on the final state (reject a save that leaves the row without an Aadhaar); `dobError`;
`statutoryFieldErrors(body, { aadhaarRequired: true })`; `ageAtDateError` when both DOB and
`joining_date` present. Return `400` with the message (route already returns JSON errors).

> Migration note (decision 2): existing `emp_details` rows with a null/blank `id_card` can no
> longer be saved from the edit form until an Aadhaar is entered. Confirm the dev/prod row
> count before shipping and decide whether a one-off backfill or a data-cleanup pass is
> needed — this is an accepted consequence, not a blocker.

---

## 6. Phase 3 — Onboarding form (Phase 3-A, decision 5)

Validation parity lands **with** the `Employee_Join_Onboarding_Gap_Plan.md` §5 `SteppedForm`
rebuild — that rebuilt form carries the full legacy field set, so gating it is closer to
legacy than bolting `required` onto the stale `OnboardForm.tsx`. Do **not** wire validation
into the current form; it is being replaced.

When the rebuild happens, add these `validateStep` gates (mirrors `onboarding.ctp`). Every
field listed here carries `<RequiredMark />`; the conditional ones show it only while their
condition holds:

- **Identity & Login step** requires Login Username, Initial Password (already marked).
- **Employment step** requires `joining_date`, `emp_branch`, `emp_dept`, `designation`,
  `emp_type`, `notice_days`.
- **Policies & Rules step** requires `shift`, `holidays`, `leave`.
- Contract `end_date` required + `end > start` when `emp_type === 'Contract'` (mark shown
  only for Contract).
- `probation` numeric, shown/required only when `emp_type === 'Probation'` (mark shown only
  then).
- `emp_category` required only for tenants DEMO/KWMT; `attr4` (Area) required only for
  VGFS/VSFS (tenant-gated, as in legacy — mark shown only for those tenants).

`onboard/route.ts`: add required-field `400`s for the employment + policy fields and the
Contract `end_date` rule, alongside the existing username/password check. The
PAN/Aadhaar/ESI/UAN/LWF/account uniqueness check already present here **stays** (decision 4 —
checked at both join-save and onboard time).

**Sequencing:** Phase 3 is blocked on the gap-plan rebuild. Phases 0–2 and 4 do not depend
on it and can ship first.

**Shared constant** — `src/lib/employeeOptions.ts` → `EMP_TYPES` = the 11 legacy values.
Consume in `OnboardForm`, `EmployeeDetail`, `employees/new`.

---

## 7. Phase 4 — verification

- `npx tsc --noEmit` + `eslint` on every touched file.
- Every mandatory field in §4a / §5a / §6 renders a red `*`; no format-only field does. Blank
  First Name → blocked on the Join form, the Employee edit form, and (post-rebuild) onboarding.
- Each of PAN / ESI / UAN / LWF / account / pincode: bad format blocked client-side **and**
  by a direct `curl` (400). Blank optional statutory field still saves.
- Partial join save (step 1 only) still works after 4c.
- Aadhaar blank: new join → blocked; edit of a legacy row with no Aadhaar → **blocked**
  (decision 2, mandatory always) — must enter an Aadhaar to save.
- Duplicate PAN/Aadhaar/ESI/UAN/LWF/account: blocked at join-save (`409`) **and** at onboard
  (`409`). Verify a `curl` to `POST /api/employees/join` with a number already on an active
  `emp_details` row is rejected.
- Count existing `emp_details` rows with null/blank `id_card` (decision 2 migration note) and
  existing `emp_join` / `emp_details` rows that violate any new required rule — spot-check
  before shipping; decide backfill vs cleanup.

---

## 8. Decisions — resolved 2026-09-07

See "Resolved decisions" block at the top of this document.

1. Aadhaar required on the Join form — **yes**.
2. Aadhaar on the Employee edit form — **mandatory always** (blocked if missing, even on an
   untouched legacy row).
3. Statutory format checks on the Employee edit form — **yes, add them**.
4. Uniqueness checks — **both** join-save time and onboard time.
5. Onboarding — **Phase 3-A** (with the gap-plan `SteppedForm` rebuild; closer to legacy).

---

## 9. Touch list

| File | Phase | Change |
|---|---|---|
| `src/components/ui/RequiredMark.tsx` (new) | 0 | shared red-`*` marker for mandatory-field labels |
| `src/lib/validation.ts` | 0 | `esiError`, `uanError`, `lwfError`, `accountNoError`, `pfNumberError`, `statutoryFieldErrors`, `ageAtDateError` |
| `src/lib/employeeOptions.ts` (new) | 3 | `EMP_TYPES` (11 legacy values) |
| `src/components/employees/JoinDetail.tsx` | 1 | `validateStep0` additions (incl. `first_name` required), new `validateStep1`/`validateStep2`, dispatch, `<RequiredMark />` on First Name / DOB / Aadhaar / Nationality, `maxLength`, uppercase-on-input |
| `src/components/employees/RepeatableRows.tsx` | 1 | `required?` on `RepeatableFieldDef` + `<RequiredMark />` on headers + `handleAdd` guard |
| `src/app/api/employees/join/route.ts` | 1 | expanded POST validation + `409` dup check |
| `src/app/api/employees/join/[id]/route.ts` | 1 | expanded PUT validation (present-key-guarded; Aadhaar always) + `409` dup check |
| `src/components/employees/EmployeeDetail.tsx` | 2 | `fieldErrors` state, `<RequiredMark />` on First Name / Last Name / DOB / Gender / Aadhaar, precondition checks (Aadhaar mandatory), `emp_type` list |
| `src/app/api/employees/[id]/route.ts` | 2 | required + format + age-at-joining checks; Aadhaar required on final state |
| `src/components/employees/OnboardForm.tsx` (rebuild) | 3 | `validateStep` gates added during the gap-plan `SteppedForm` rebuild |
| `src/app/api/employees/join/[id]/onboard/route.ts` | 3 | required employment/policy fields, Contract `end_date` rule (dup check already present) |
| `src/app/(dashboard)/employees/new/page.tsx` | 3 | consume `EMP_TYPES` |
| `src/app/(dashboard)/employees/new/page.tsx` | 5 | `RequiredMark`, precondition checks, `statutoryFieldErrors`, DB-width `maxLength` |
| `src/app/api/employees/route.ts` | 5 | required-field `400`s (First Name, Last Name, Gender, DOB, Aadhaar), `statutoryFieldErrors` |
| `src/components/employees/JoinDetail.tsx`, `EmployeeDetail.tsx`, `employees/new/page.tsx` | 6 | DB-width `maxLength` on every statutory/bank text input (see §11) |

---

## 10. Addendum 2026-09-14 — New Employee form + DB-width character limits

Prompted by `Employee_Forms_Input_Validation_And_Char_Limits_Report.md` (2026-09-14), which
audited `maxlength`/format/required parity across all 4 forms including character limits not
covered above. Two gaps found that this plan didn't already close:

### 10a. New Employee form (`employees/new`) has zero validation plan

Phases 1–3 above cover Join, Edit, and Onboarding. The **New Employee** form
(`employees/new/page.tsx` → `POST /api/employees`) was never brought into this plan — it
currently has only `first_name` marked `required` client-side and **no** server-side check
beyond duplicate `id_card`/`lwf_code` (matches legacy `Employee/setup.ctp` add-mode, which
also has no statutory format checks — confirmed in the char-limits report, §"no CakePHP model
validation anywhere").

**Phase 5 — bring New Employee to the same bar as Phase 2 (Employee edit), since both map to
the same legacy template (`Employee/setup.ctp`, add vs edit mode) and should behave
identically:**

- Add `RequiredMark` + precondition checks on `handleSubmit`: **First Name**, Last Name, Gender
  (`classification`), Date of Birth, Aadhaar (`id_card`) — same five fields as §5a, since
  legacy's `setup()` action renders the same required fields in both add and edit mode.
- `dobError(date_of_birth)`; `statutoryFieldErrors(form, { aadhaarRequired: true })` — same
  composite used in Phases 1–2, no new logic needed.
- `POST /api/employees/route.ts`: mirror the required + format checks added to `PUT
  /api/employees/[id]` in §5b. Keep the existing `id_card`/`lwf_code` duplicate check
  (matches legacy) — do **not** add the PAN/ESI/UAN/account uniqueness check from §4d here,
  since legacy's add-mode controller only ever checked `id_card`/`lwf_code` (confirmed by the
  legacy-side agent); adding more would be new behavior beyond parity, not a gap fix.
- Consume the Phase 3 `EMP_TYPES` shared constant (already in the touch list).

### 10b. DB-width `maxLength` across all 4 forms

The char-limits report's §3 table gives the real backstop — DB `varchar` widths — since app-
level `maxlength` is inconsistent even in legacy (only `EmployeeJoin/setup.ctp` has any).
**This is an enhancement beyond legacy parity**, not a parity requirement — legacy itself
never bounded these fields on the New Employee or Edit Employee templates. Flagging as a
deliberate improvement, not "restoring" missing legacy behavior, because add/edit currently
have zero client-side or server-side length guard and a too-long value fails as a raw MySQL
driver error instead of a clean message.

**Phase 6 — add `maxLength` (JSX) matching DB column width, on the text inputs below, in all
forms where the field appears (`JoinDetail.tsx` step 1/2, `EmployeeDetail.tsx`, `employees/new/page.tsx`):**

| Field | DB column | DB width | maxLength to add |
|---|---|---|---|
| `first_name` / `last_name` | emp_details | varchar(100) | 100 |
| `mobile_no` | emp_details | varchar(50) | keep existing UI cap of 10 (stricter, format-driven) |
| `id_card` (Aadhaar) | emp_details | varchar(100) | keep existing UI cap of 12 (stricter, format-driven) |
| `pan_no` | emp_details | varchar(50) | keep existing UI cap of 10 (stricter, format-driven) |
| `esi` | emp_details | varchar(200) | keep existing UI cap of 10 (stricter, format-driven) |
| `company_pf` (UAN) | emp_details | varchar(100) | keep existing UI cap of 12 (stricter, format-driven) |
| `pf` (PF No) | emp_details | varchar(100) | keep existing UI cap of 22 (stricter, format-driven) |
| `lwf_code` | emp_details | varchar(50) | keep existing UI cap of 15 (stricter, format-driven) |
| `ifsc_code` | emp_details | varchar(30) | 30 (no format regex exists for IFSC — see open question below) |
| `account_no` | emp_details | varchar(30) | keep existing UI cap of 18 (stricter, format-driven) |
| `pincode` | emp_details | varchar(50) | keep existing UI cap of 6 (stricter, format-driven) |
| `bank_name`, `branch_name`, `branch_address`, `name_as_per_bank`, `name_as_on_pan` | emp_details | varchar(100) | 100 |
| `emp_company_id` | emp_proff | varchar(30) | 30 |

Where §4a (Phase 1) already sets a stricter format-driven `maxLength` on `JoinDetail.tsx`
(e.g. PAN 10, not DB's 50), **keep the stricter one** — this table only fills in the fields
that currently have *no* `maxLength` anywhere, chiefly on `employees/new/page.tsx` and
`EmployeeDetail.tsx`, which have none today.

**Open question — IFSC format:** none of the legacy forms, the current validators, or this
addendum define an IFSC regex (`^[A-Z]{4}0[A-Z0-9]{6}$` is the standard format used by every
Indian bank). Legacy only strips non-alphanumerics + caps length at 12; `pincodeError` and
friends in `validation.ts` don't cover it. Worth asking before Phase 6 ships: add a proper
`ifscError` validator (a genuine improvement, no legacy precedent) or leave it as a bare
`maxLength` per the table above (matches "no regression beyond legacy" for this one field).

### 10c. Sequencing

Phase 5 has no dependency on Phases 0–4 and can ship independently (it reuses helpers Phase 0
already adds). Phase 6 touches the same files as Phases 1, 2, and 5, so doing it in the same
pass as those phases (rather than as a separate later change) avoids re-touching each file
twice — recommend folding Phase 6's `maxLength` additions into the same edits as Phases 1, 2,
and 5 rather than a standalone pass.

Nothing in this addendum has been implemented — plan only, per instruction.

---

## 11. Execution log — 2026-09-14

Phases 0, 1, 2, 5, and 6 implemented (Phase 3 stays deferred — see §6). `npx tsc --noEmit` and
`npx eslint` run clean on every touched file (pre-existing `react-hooks/static-components`
errors in `EmployeeDetail.tsx`, 36 of them, confirmed unchanged via `git stash` diff — not
introduced by this pass).

- **Phase 0** — `src/lib/validation.ts`: added `esiError`, `uanError`, `lwfError`,
  `accountNoError`, `pfNumberError`, `statutoryFieldErrors`, `ageAtDateError`.
  `src/components/ui/RequiredMark.tsx` (new). `src/lib/employeeOptions.ts` (new) — `EMP_TYPES`,
  also consumed by `OnboardForm.tsx` (dedupes the inline list added 2026-09-14 earlier the same
  day) even though Onboarding's own validation gate stays deferred.
- **Phase 1** — `JoinDetail.tsx`: `validateStep0` already had first_name/DOB/Aadhaar/gender/
  nationality required from a prior pass; added `pincode` format check. New `validateStep1`
  (PAN/PF/UAN/ESI/LWF format) and `validateStep2` (account number format), dispatched from
  `saveAndContinue`/`finish` via a new `validateStep(idx)` switch. `maxLength` added: PAN 10,
  PF 22, UAN 12, ESI 10, LWF 15, previous-member-id 15, WPS 15, IFSC 12, account 18, pincode 6.
  Uppercase-on-input for PAN and LWF. `RepeatableRows.tsx`: added `required?` on
  `RepeatableFieldDef`, a red-`*` marker on required column headers, and a `handleAdd` guard
  that blocks adding a row with a blank required field (with an inline message). Marked the
  legacy-required sub-modal fields (Education: all 4; Experience: all 6; Family: 5 of 6,
  Nationality optional; Documents: 5 of 7, Nationality/Valid Till optional).
  `POST /api/employees/join`: added `first_name`/`nationality_id` required checks,
  `statutoryFieldErrors(body, { aadhaarRequired: true })`, and the PAN/Aadhaar/ESI/UAN/LWF/
  account uniqueness check (409) against active `emp_details`.
  `PUT /api/employees/join/[id]`: same statutory format checks (present-key-guarded via the
  composite's own blank-is-valid behaviour), Aadhaar enforced on **every** save — when
  `id_card` isn't in the body, the current DB value is read and the save is rejected if it's
  blank — plus the same uniqueness check.
- **Phase 2** — `EmployeeDetail.tsx`: added `fieldErrors`/`formError` state, `RequiredMark` on
  First Name/Last Name/DOB/Gender/ID Card, a `validateAndSave` precondition (those 5 required +
  full statutory format chain + `ageAtDateError` against `joining_date` when set) wired to the
  Save Changes button, `maxLength` across Personal/Statutory/Bank matching Phase 1's limits,
  and `emp_type` now consumes `EMP_TYPES`. The `update` mutation now surfaces server error
  messages (previously silently dropped). `PUT /api/employees/[id]`: mirrors the same
  required + format + age-at-joining checks server-side, Aadhaar mandatory on final state
  (same DB-read-if-absent pattern as the Join PUT).
- **Phase 5** — `employees/new/page.tsx`: added `fieldErrors` state, `RequiredMark` on First
  Name/Last Name/DOB/Gender/ID Card, a `validate()` precondition wired to `handleSubmit`
  (same 5 required fields + statutory format chain), `maxLength` on the same fields as Phase 1/2,
  `emp_type` now consumes `EMP_TYPES`. `POST /api/employees`: mirrors the same required +
  format checks; the existing `id_card`/`lwf_code`/`emp_id` duplicate check is unchanged (not
  widened to PAN/ESI/UAN/account, per §10a — matches legacy's actual add-mode behavior).
- **Phase 6** — folded into Phases 1/2/5 above rather than a separate pass, per §10c.
- **Not done / explicitly deferred:** Phase 3 (Onboarding `validateStep` gates) — still
  blocked on the `SteppedForm` rebuild. The open IFSC-format question from §10b was left
  unresolved — `maxLength={12}` added everywhere IFSC appears, but no `ifscError` validator
  was written (no format check beyond length, matching legacy's own laxness on this one
  field).
- **Not verified:** none of this has been exercised in a running browser session (no dev
  server smoke test performed) — the §7 verification checklist (blank-field blocking, curl
  checks against the API routes, duplicate-value 409s, partial-save behaviour, existing-row
  migration counts) is still open.
- **Correction 2026-09-14 (same day):** Last Name was wrongly made mandatory on Edit Employee
  and New Employee (§5a/§10a listed it alongside First Name — user caught this as wrong).
  Removed the `required` check, `RequiredMark`, and error state for `last_name` from
  `EmployeeDetail.tsx`, `employees/new/page.tsx`, `PUT /api/employees/[id]`, and
  `POST /api/employees`. Mandatory set for these two forms is now: First Name, Gender, DOB,
  Aadhaar/ID Card only. `JoinDetail.tsx` was never affected (it never required Last Name).
