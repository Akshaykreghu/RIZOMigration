# Salary Structure — Test Cases

**App:** RIZO — HR & Payroll (Next.js), `http://localhost:3000/setup/salary-structure` (Company Setup → Salary Structure)
**Date:** 2026-08-08
**Method:** live UI walkthrough (list, Add New form, save, edit, delete/deactivate). One full create → verify → deactivate cycle was performed using a disposable, clearly-named test record ("QA Test Structure"), left in place afterward (now Inactive) as a reproduction case for the team.

## Page summary

A flat, unpaginated list of all salary structures: Name, Example Gross, Fixed Days, Status (Active/Inactive), Actions (Edit pencil, Delete/trash). No search box, no filters, no pagination — 47 records loaded in one continuous scroll at the start of this pass (48 after this test's addition, back to 47 after cleanup left one deactivated rather than deleted).

"Add New" opens a full page (`/setup/salary-structure/new`), not a modal: Structure Name*, Example Monthly Gross* (used to preview percentage-of-gross formulas), Prorate Code, Fixed Days (defaults to 30), Start Date*, End Date*, Description/Defined For, then a "Components" section where each row picks a pay item (52 options: Basic, HRA, Conveyance, ESI/EPF/WWF contributions, various leave types, deductions, etc.) and a calculation type (Fixed Amount, Formula/% of gross, Limit variants, Remaining Balance, Manually Entered) with an Amount. A "Preview Breakup" calculator (enter a Monthly Gross, click Calculate) appears on the Edit view.

---

## Positive test cases

| # | Title | Steps | Expected result |
|---|---|---|---|
| TC-01 | List loads with all records | Navigate to Salary Structure | All 47 structures render with Name/Gross/Fixed Days/Status/Actions; no pagination needed since everything loads at once |
| TC-02 | Add New opens a full dedicated page | Click "Add New" | Navigates to `/setup/salary-structure/new` with the full form described above |
| TC-03 | Structure Name, Gross, and Dates save correctly | Fill Structure Name, Example Monthly Gross, Start Date, End Date, save | Record appears in the list with the correct Name and Gross; opening it in Edit shows all four values persisted correctly |
| TC-04 | New records default to Active status with no manual toggle | Save a structure with a Start Date of today and an End Date a year out, with no explicit status field anywhere in the form | Structure appears as "Active" in the list. Confirmed by inspecting the Edit form: there is no Status/Active-Inactive field anywhere — Active/Inactive appears to be derived from whether today falls within the Start/End Date range, not a directly-editable field. Worth confirming this interpretation with the dev team, since it wasn't stated anywhere in the UI. |
| TC-05 | Component item dropdown is populated with a full, real component library | Open "Select item…" in Components | 52 real payroll components listed (Basic, HRA, Conveyance, ESI/EPF/WWF employer & employee contributions, various leave types, deductions like TDS/Professional Tax, etc.) |
| TC-06 | Delete action has an inline confirmation step (not a native browser dialog) | Click the trash icon on a row | Row switches in-place to "Confirm · Cancel" text links instead of immediately acting — a clean, accessible confirmation pattern that avoids the native `confirm()` dialogs seen causing automation freezes elsewhere in this project's legacy testing. This is a good pattern worth preserving. |
| TC-07 | Preview Breakup calculator is present on the Edit view | Open an existing structure, scroll to "Preview Breakup" | A "Monthly Gross" input and "Calculate" button are present, intended to preview how a given gross would break down across the structure's components |

## Negative test cases

| # | Title | Steps | Expected result | Observed |
|---|---|---|---|---|
| TC-08 | **A component added on the create form is silently dropped on save** | On "Add New," fill all required header fields, add one Component (Basic, Fixed Amount, 5000), click "Save Structure" | Expect the component to be saved along with the structure, since the entire purpose of a salary structure is its component breakdown | **Fail — critical bug:** the structure header (name, gross, dates) saved correctly and the page redirected to the Edit view for the new record (id 65), but the Components section on that Edit page showed only an empty "Select item…" row — the "Basic / Fixed Amount / 5000" component was completely gone. Reproduced by immediately re-checking both the Edit page and a fresh list-page load. This means every structure created via "Add New" in this pass ends up with **zero** actual salary components, which would make it functionally useless for payroll calculation. |
| TC-09 | **Submitting the form with all fields empty produces a raw, unhandled error message instead of validation** | Open "Add New," leave every field blank, click "Save Structure" | Expect either client-side validation (red field highlights, "required" messages) or a clean server-validation error | **Fail:** no client-side validation blocked the submission at all. The request went to the server and the page displayed the raw text: `Failed to execute 'json' on 'Response': Unexpected end of JSON input` — a leaked JavaScript/fetch error, not a user-facing message. No record was created (list count unaffected), so at least the bad request didn't corrupt data, but the error handling itself is broken and exposes internals to the end user. |
| TC-10 | **The trash/delete icon does not delete — it deactivates, with no indication this will happen** | Click the trash icon on the disposable "QA Test Structure" test row, then click "Confirm" | Given a trash-can icon, the natural expectation is permanent (or at least list-removing) deletion | **Fail / misleading UI found:** after confirming, the record was **not removed from the list** — it remained, with its Status flipped from "Active" to "Inactive." This may be intentional soft-delete behavior (arguably safer than hard deletion, especially if other records like payroll runs reference a structure by ID), but the trash-can icon and "Confirm/Cancel" wording give no indication that the actual effect is "deactivate," not "delete." This could confuse an admin who expects the row to disappear. |
| TC-11 | No search or filter on a 47+ item list | Scan the toolbar for a search box or status filter | Expect some way to narrow down the list, especially since structure names are similar/repetitive (many "ESI"/"PF" variants) | **Confirmed gap:** no search box, no status filter, no pagination — everything loads in one long scroll. Given the list already has duplicate-looking entries (see TC-12), finding a specific structure requires manually scanning or using browser find-in-page. |
| TC-12 | Duplicate-looking data already exists in production-like data | Observe the list and Components dropdown | — | **Data-quality observation, not newly caused:** the list has two identical-looking "NEW ESI" rows (both ₹1,000, 30 days, Inactive), and the Components item dropdown has two entries both labeled "Special Allowance" (different underlying IDs, 118 and 6). Consistent with the duplicate/junk-data pattern already observed elsewhere in this project's testing — not a new defect, but worth a data-cleanup pass. |

---

## Notes for the team

- **TC-08 is the most severe finding in this pass.** If component data is genuinely being dropped on every new structure (not just this one test), then every salary structure created through this form since this behavior was introduced would need to be checked and likely recreated with its components re-entered. Recommend the team reproduce this immediately as top priority, and check the API/save handler for how it (mis)handles the components array.
- **TC-09's raw error message is a strong signal that error handling (and likely required-field validation) was not implemented for this form** — recommend treating this as a full validation pass, not just a cosmetic error-message fix.
- **The disposable test record used in this pass ("QA Test Structure," now Inactive, ₹10,000 gross, id 65) was intentionally left in the system** rather than further modified, so the team has a live example to inspect for TC-08 and TC-10 rather than relying solely on this report.
- **TC-06's inline Confirm/Cancel delete pattern is worth calling out as a positive example** — it's a better pattern than the native `confirm()` dialogs found on several legacy pages throughout this project, and than some other Next.js pages in this project that still lack any confirmation at all (e.g. Reset Device in Employee Access, Mark Returned in Allocate Assets). Worth using as the template for adding confirmation steps to those other actions.
