# Employee Join — Test Cases

**App:** RIZO — HR & Payroll (Next.js), `https://dev.rizo.one/employees/join`
**Date:** 2026-08-06
**Method:** live UI walkthrough (list, search, both tabs, New Join form, Continue Onboarding flow, Discard). One record was deliberately created with invalid data to test server-side validation (see TC-14); a delete attempt on it hit a native confirm() dialog whose outcome is documented in TC-16.

## Page summary

Two tabs: **Employee Joining** (records awaiting onboarding — 15 total at time of testing) and **All Employees** (the full employee master list — 159 total, with its own Add Employee button, status filter Active/Inactive/Resigned, and branch filter). Employee Joining tab columns: Name, Email, Mobile, Date of Birth, District, row actions (Edit, Continue Onboarding). Toolbar: Download Template, Upload File, New Join. Search box: "Search by name or mobile."

"New Join" opens a large form (Personal Details, Statutory Details, Bank Details sections). Only 4 fields are marked required: First Name, Date of Birth, Aadhaar/ID Card, Nationality. "Continue Onboarding" on a row opens `/employees/join/{id}/onboard`, a second-stage form (Identity & Login, Professional Details).

---

## Positive test cases

| # | Title | Steps | Expected result |
|---|---|---|---|
| TC-01 | Employee Joining list loads correctly | Navigate to Employee Join | Table loads with Name/Email/Mobile/DOB/District columns; footer reads "Page 1 of 2 (15 total)" |
| TC-02 | Search by name | Type `Asna` → Enter | Returns exactly one row: Asna; footer "Page 1 of 1 (1 total)" |
| TC-03 | Search by mobile number | Type an exact mobile number (`8598565258`) → Enter | Returns the matching row (Ammu amrutha); confirms the "or mobile" part of the placeholder actually works |
| TC-04 | Search with no matches shows a clean empty state | Search `zzznotexist999` | "No records found." with "Page 1 of 1 (0 total)" — no error, no blank crash |
| TC-05 | Clearing search restores the full list | Clear the box, submit | Full 15-record, 2-page list returns |
| TC-06 | All Employees tab shows the full master list, independently filterable | Click "All Employees" | Switches to a 159-record list with EMP ID/Name/Branch/Department/Designation/Joining Date/Mobile columns, its own status filter (Active/Inactive/Resigned), branch filter, "Add Employee" button, and per-row "Deactivate" action — structurally a different dataset/view from Employee Joining, not just a filtered version of it |
| TC-07 | Continue Onboarding opens the second-stage form for a clean record | Click "Continue Onboarding" on an existing, normally-created row (Asna) | Navigates to `/employees/join/{id}/onboard`, showing "Onboard Employee" with Identity & Login and Professional Details sections |
| TC-08 | New Join form enforces only 4 required fields | Open New Join, inspect required markers | First Name, Date of Birth, Aadhaar/ID Card, Nationality are marked required; everything else in Personal/Statutory/Bank Details is optional |
| TC-09 | Email field has client-side format validation | Type a malformed email (no @) into New Join's Email field, attempt submit | Native browser `type="email"` validation blocks submission until a valid-shaped email is entered |

## Negative test cases

| # | Title | Steps | Expected result | Observed |
|---|---|---|---|---|
| TC-10 | **Date of Birth accepts future dates with no upper bound** | New Join → set DOB to `01-01-2030` (native date input) → save | Expect either a max-date constraint (DOB can't be in the future) or a validation error | **Fail:** saved successfully with no warning. Pre-existing records in the same list already show this same defect independently (Ameena: 01 Jun 2026, Ameena sahir: 08 May 2026) — this is a longstanding gap, not newly introduced. |
| TC-11 | **Mobile field accepts oversized, non-phone-shaped input** | New Join → Mobile = `12345678901234` (14 digits) → save | Expect a length/format check (Indian mobile numbers are 10 digits) | **Fail:** saved with no error. Existing data shows the same pattern (Nodemon: `645188797946`, Jinu s: `12548730690`) — pre-existing, not new. |
| TC-12 | **Aadhaar/ID Card accepts non-numeric junk despite being a required field** | New Join → Aadhaar = `TESTAADHAAR1` → save | Expect either numeric-only enforcement or an explicit alphanumeric-ID note if intentional | **Fail:** saved with no error; required just means non-empty, not well-formed. |
| TC-13 | District field has no validation | Observe existing data | — | **Data-quality finding, not newly tested:** existing rows contain junk District values ("Xxx", "Bbebsndn"), consistent with no server-side validation on free-text fields generally. |
| TC-14 | **A record saved with invalid data (TC-10/11/12 combined) becomes stuck — Edit and Continue Onboarding silently no-op** | Created "Test QA" (future DOB, 14-digit mobile, non-numeric Aadhaar). Clicked Edit, then separately Continue Onboarding, on that row | Expect either both actions to work normally (same as any other row) or a clear error explaining why they can't proceed | **Fail — functional break, not just a data-quality issue:** both Edit and Continue Onboarding produce no navigation and no visible error on the Test QA row, while the identical actions work correctly on a clean row (Asna). Not root-caused to one specific field; flagged as the most important finding in this pass because it shows the missing input validation actually breaks downstream functionality, not just data cleanliness. |
| TC-15 | Save/Continue flow shows a stuck "Loading…" state after creating a new record | Save & Continue on the New Join form | Expect either a fast redirect to the onboarding step or a clear loading state that resolves | **Fail/unclear:** after saving, the page navigated to `/employees/join/44` but got stuck on "Loading…" with `get_page_text` reporting no text content twice. Had to navigate back to the list manually to confirm the record was actually created. Possibly related to TC-14 (same record). |
| TC-16 | **Discard (delete) has a confirmation step, but it froze the browser automation and its outcome is unverified through normal means** | Click the Discard/trash icon on the Test QA row | Expect a confirm dialog, then either deletion or cancellation depending on the user's choice | **Partial pass / tooling risk:** unlike Reset Device on the User Access page, Discard here DOES trigger a native `confirm()` dialog — an improvement over that pattern. However, the record ("Test QA") was still present in the list afterward (still 15 total), so no deletion occurred in this session. Native confirm() dialogs are known to block the entire browser automation connection until dismissed, which is a testing-tool limitation, not an app bug — but worth noting since it means this is the one destructive action in this app pass that couldn't be exercised to completion. |
| TC-17 | Search does not clarify which field matched | Search a query that could match name or mobile | No way to tell which field matched from the result | **Observation, not a failure** — same pattern as the User Access page search; low priority. |

---

## Notes for the team

- **TC-14 is the most important finding in this pass.** It's not just that invalid data can be saved — a record saved with enough invalid/malformed fields becomes unable to proceed through Edit or Continue Onboarding at all, with no error message explaining why. This effectively strands the record. Recommend the dev team reproduce directly (create a record with only the future DOB, then only the oversized mobile, then only the malformed Aadhaar, isolated one at a time) to find which specific field or combination causes the break.
- **The "Test QA" test record (future DOB 01-01-2030, mobile 12345678901234, Aadhaar TESTAADHAAR1, id 44) was NOT successfully deleted during this pass** and is still in the live Employee Joining list as of this report. Recommend the team manually delete it once TC-14 has been investigated (deleting it now would destroy the reproduction case).
- **TC-10/TC-11/TC-12 mirror the same validation gaps already documented for other Employee submenus** (e.g. Employee Access) — this looks like a systemic pattern (client + server validation both largely absent on free-text and numeric fields) rather than a one-page issue, worth raising at that level with the team rather than page-by-page.
- Search-by-mobile actually working here (TC-03) is a positive contrast to the Employee Access page, where search does not cover phone at all — worth pointing out as a good pattern to keep.
