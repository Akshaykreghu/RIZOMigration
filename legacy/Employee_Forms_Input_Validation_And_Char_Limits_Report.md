# Employee Forms — Input Validation & Character-Limit Parity Report

Date: 2026-09-14
Scope: 4 Next.js employee forms vs their legacy CakePHP counterparts.

| # | Next.js form | Legacy counterpart | Legacy validation quality |
|---|---|---|---|
| 1 | `employees/new` (New Employee) | `View/Employee/setup.ctp`, add mode | **Weak** — almost no `maxlength`/regex, just a few `required` |
| 2 | `JoinDetail.tsx` (Employee Join) | `View/EmployeeJoin/setup.ctp` | **Strong** — real `maxlength` + regex/onkeypress filters on most statutory/contact fields |
| 3 | `OnboardForm.tsx` (Continue Onboarding) | `View/EmployeeJoin/onboarding.ctp` | **Weak** — no `maxlength`/regex, but many fields `required` |
| 4 | `EmployeeDetail.tsx` (Edit Employee) | `View/Employee/setup.ctp`, edit mode | **Weak** — identical template to #1, same near-absence of validation |

Important structural fact confirmed by both agents: **no CakePHP model (`EmployeeDetails`, `EmployeeProfessionalDetails`, `EmployeeJoin`) defines a `$validate` array.** Legacy has zero server-side/model-level validation anywhere in this flow — every check that exists is either an HTML attribute, inline JS, or a manual duplicate-value query in the controller. The database column width is the only real backstop legacy has ever had.

---

## 1. Headline gap

The single most important finding: **`EmployeeJoin/setup.ctp` (legacy form #2) is the only legacy template with real validation, and Next.js's `JoinDetail.tsx` only partially reproduces it.**

| Field | Legacy `EmployeeJoin/setup.ctp` | Next.js `JoinDetail.tsx` |
|---|---|---|
| Mobile No | maxlength 10, digits-only | ✅ maxlength 10, `mobileError` (`^\d{10}$`) |
| Aadhaar (`id_card`) | maxlength 12, digits-only, `^\d{12}$` | ✅ maxlength 12, `aadhaarError` (`^\d{12}$`) |
| Date of Birth | age/format not enforced in legacy JS | ✅ Next.js is actually **stricter** — `dobError` blocks future dates & under-18 |
| Name | maxlength 100, `pattern="[A-Za-z0-9.\s]+"` | ❌ no maxlength, no pattern |
| PAN No | maxlength 10, `^[A-Z]{5}[0-9]{4}[A-Z]{1}$`, auto-uppercase | ❌ no maxlength, no format check (`panError` exists in `validation.ts` but is never called) |
| ESI No | maxlength 10, digits-only, `^\d{10}$` | ❌ no maxlength, no format |
| UAN (`company_pf`) | maxlength 12, digits-only, `^\d{12}$` | ❌ no maxlength, no format |
| PF No | maxlength 22, alphanumeric-only | ❌ no maxlength, no format |
| LWF Code | maxlength 15, `^[A-Za-z0-9]{5,15}$`, auto-uppercase | ❌ no maxlength, no format |
| IFSC Code | maxlength 12, alphanumeric-only | ❌ no maxlength, no format |
| Account No | maxlength 18, digits-only | ❌ no maxlength, no format |
| Pincode | maxlength 6, digits-only | ❌ no maxlength, no format (`pincodeError` exists, never called) |
| Guardian Name / Relation | `pattern="[A-Za-z\s]+"` | ❌ unrestricted |
| Address | `pattern="[A-Za-z0-9\s,./#()-]+"` | ❌ unrestricted |
| Previous Member ID | maxlength 15, digits-only | ❌ no maxlength |
| WPS ID | maxlength 15, digits-only | ❌ no maxlength |

So on the one form where legacy actually enforces character limits and formats, Next.js currently keeps only 2 of ~15 checks (mobile, Aadhaar). Everything else is free-text with no bound.

---

## 2. The other 3 forms — roughly at parity, but both sides are weak

Forms #1, #3, #4 (New Employee, Onboarding, Edit Employee) are a wash: **legacy itself barely validates these three**, so the Next.js versions aren't regressing much beyond what legacy already tolerated. The notable differences:

**New Employee (#1) / Edit Employee (#4):**
- Legacy: `required` on Name, Gender, DOB, Nationality (add/edit form) with no format check beyond that; server does a duplicate check on `id_card`/`lwf_code` (edit mode self-excludes the current row).
- Next.js: only `first_name` is `required` (New Employee only — Edit Employee has **zero** `required` fields at all, and zero server-side checks beyond null-coercion). No duplicate check exists in `PUT /api/employees/[id]` at all — legacy's edit-mode has one, Next.js doesn't.
- Neither side enforces `maxlength` on PAN/IFSC/ESI/UAN/account fields in this template pair — this gap is inherited from legacy, not introduced by the port.

**Continue Onboarding (#3):**
- Legacy: `Branch, Department, Designation, Employee Type, Notice Period` are all `required`; Shift/Holiday/Leave are all `required` selects. Contract Period conditionally required when type=Contract.
- Next.js: **none** of these are `required` — even after today's fix adding the Shift/Holiday/Leave/Notice Period fields (previous conversation turn), they're optional. This is the "required-field enforcement" gap already tracked as G10 in `Employee_Join_Onboarding_Gap_Plan.md`, deferred by your 2026-09-04 decision — still open.
- Neither side has maxlength/format checks on this form's fields (probation days, employee ID) — matches legacy's own laxness.

---

## 3. DB column-length backstop (the real limits, since app-level enforcement is thin on both sides)

From `legacy/schema/mypayrol_mpm121.sql`:

| Column | Table | Length | Notes |
|---|---|---|---|
| `first_name`, `last_name` | emp_details | varchar(100) | |
| `mobile_no` | emp_details / emp_join | varchar(50) | UI limits to 10 digits in join form only |
| `id_card` (Aadhaar) | emp_details / emp_join | varchar(100) | UI limits to 12 digits in join form only |
| `pan_no` | emp_details / emp_join | varchar(50) | UI limits to 10 chars in join form only |
| `esi` | emp_details / emp_join | varchar(200) | UI limits to 10 digits in join form only |
| `company_pf` (UAN) | emp_details / emp_join | varchar(100) | UI limits to 12 digits in join form only |
| `pf` (PF No) | emp_details / emp_join | varchar(100) | UI limits to 22 chars in join form only |
| `lwf_code` | emp_details / emp_join | varchar(50) | UI limits to 15 chars in join form only |
| `ifsc_code` | emp_details / emp_join | varchar(30) | UI limits to 12 chars in join form only |
| `account_no` | emp_details / emp_join | varchar(30) | UI limits to 18 digits in join form only |
| `pincode` | emp_details / emp_join | varchar(50) | UI limits to 6 digits in join form only |
| `emp_company_id` | emp_proff | varchar(30) | no UI limit anywhere |

None of the Next.js forms enforce any of these lengths client-side, and none of the API routes enforce them server-side either — a value longer than the column width would currently fail at the MySQL layer with a raw driver error rather than a friendly message, on both new-employee and edit-employee paths equally (this was already true in legacy for those two forms too).

---

## 4. Recommendation

Given the pattern, the fix isn't "add validation to 4 forms independently" — it's:

1. **Port the `EmployeeJoin/setup.ctp` validation set into `JoinDetail.tsx`'s Statutory/Personal steps** — this is the one legacy form that actually had rules worth preserving (PAN/ESI/UAN/PF/LWF/IFSC/Account/Pincode formats + lengths), and `validation.ts` already has `panError`/`pincodeError`/`emailError` written but unused — this is mostly wiring, not new logic.
2. **Add `maxLength` matching the DB column widths above to the statutory/bank fields across all 4 forms** — cheap, consistent, and catches the "silent MySQL truncation/rejection" class of bug on both New Employee and Edit Employee, which legacy also never protected but which is worth doing now since you're already touching this code.
3. **Onboarding's required-field enforcement (G10)** — still explicitly deferred per your decision; not re-raising it, just noting it's the other open piece in this area.

No code has been changed for this report — it's read-only, per usual practice for a "check and report" request. Let me know if you want me to implement item 1 and/or 2 (they're independent — I can do either or both).
