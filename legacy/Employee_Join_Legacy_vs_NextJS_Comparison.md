# Employee Join — Legacy vs. Next.js Comparison

**Legacy:** https://in.mypayrollmaster.online/Dashboard → Employee → 2. Employee Join
**Next.js:** https://dev.rizo.one/employees/join
**Date:** 2026-08-06

**Data caveat:** same as prior comparisons in this project — legacy is on tenant "GLET" (27 Employee Joining records, many obviously junk/test rows), Next.js is on tenant "GRTL" (15 records, more real-looking but including one deliberately-created test row). Treat this as a **behavioral/structural comparison**, not a record-for-record data check.

---

## Headline answer

**Structurally very close.** Both apps split this module into two tabs — a "pending onboarding" list and a full employee master list — and both let an admin add a new joiner with a large personal-details form where only a handful of fields (name, DOB, Aadhaar, nationality, plus gender in legacy) are actually required. Both apps share the **same core validation gap**: Date of Birth accepts future dates with no upper bound, confirmed independently in both apps. Where they differ most is in onboarding flow shape (legacy combines edit + onboarding into one tabbed modal; Next.js uses two separate pages) and in search scope (legacy's search doesn't cover Phone despite it being a shown column — Next.js's does).

---

## Tab structure comparison

| Aspect | Legacy | Next.js | Same? |
|---|---|---|---|
| Tabs | "All Employees" (full master list, 1865 total in this tenant) / "Employee Joining" (pending onboarding, 27 total) | "All Employees" (full master list, 159 total) / "Employee Joining" (pending onboarding, 15 total) | **Same structure** — both apps split pending-onboarding records from the full employee master via two tabs with matching names |
| All Employees columns | Employee ID, Full Name, Designation, Joined Date, Branch Name, **Profile Completion %**, Doc (download icon) | Employee ID, Name, Branch, Department, Designation, Joining Date, Mobile, Actions (Deactivate) | **No** — legacy shows a Profile Completion progress bar and a per-row document download; Next.js shows Department and Mobile directly instead. Neither is a strict superset of the other. |
| All Employees extra tools | View, Remove, History, Filter, Select Branch dropdown, dashboard cards (Total Count, Active Employees, No Salary Structure, Joined This Month) | Status filter (Active/Inactive/Resigned), branch filter, Add Employee button, per-row Deactivate | **Partial** — legacy has richer summary cards and a History action; Next.js's filter set (by status) is something legacy's toolbar doesn't obviously expose in the same place |

## Employee Joining list comparison

| Aspect | Legacy | Next.js | Same? |
|---|---|---|---|
| Columns | Image, Name, Date of Birth, Phone No, Mail Id, District, Action (delete only) | Name, Email, Mobile, Date of Birth, District, Actions (Edit, Continue Onboarding) | **Similar fields**, different action set — legacy's row action is delete-only; editing/onboarding happens via toolbar selection instead (see below) |
| Add-new entry point | "Add Employee" toolbar button (+ separate "Import" button for bulk) | "New Join" toolbar button (+ separate "Upload File"/"Download Template" for bulk) | **Same concept**, different labels; both separate single-add from bulk-import |
| Edit / Continue onboarding interaction | Select a row (click it), then click the **"OnBoarding"** toolbar button → opens one modal with three tabs: Personal Info, Other Details, Onboarding | Direct per-row links: "Edit" and "Continue Onboarding" open as two separate flows/pages | **No — different interaction model.** Legacy's select-then-act pattern (same pattern used in Employee Access) combines editing personal details and completing onboarding into a single tabbed modal; Next.js splits these into a one-shot creation form plus a distinct two-stage onboarding page. Neither is strictly worse, but they're not equivalent flows — worth a product decision on which pattern the team wants to standardize on across the app (legacy's select-then-act pattern already showed up as a difference in the Employee Access comparison too). |
| Pagination | Page-size selector (10/20/30/40/50) + first/prev/next/last + "Page X of Y" | Fixed page size, first/prev/next/last + "Page X of Y (N total)" | **No** — Next.js is missing the page-size selector, same gap already flagged for Employee Access (ACC-007) |
| Search scope | Single box "Search by Employee Name or ID" (shared across both tabs, top-right) — confirmed matches Name, **does not match Phone** despite Phone being a shown column | Single box "Search by name or mobile" — confirmed matches **both** name and mobile | **No — Next.js is better here.** This is the same search-scope gap already flagged for Employee Access (ACC-013), reproduced independently on this page. Worth noting Next.js has already fixed it for Employee Join specifically. |
| No-match empty state | Bare empty table, "Displaying 0 to 0 of 0 items", no message | "No records found." message shown | **No** — Next.js gives clearer feedback, consistent with the same finding from Employee Access (ACC-014) |

## Add / New Join form comparison

| Field | Legacy ("Add Employee" → Personal Info) | Next.js ("New Join") | Same? |
|---|---|---|---|
| Required fields | Name*, Birth Date*, **Gender***, Nationality*, Aadhaar No* (5 required) | First Name*, Date of Birth*, Aadhaar/ID Card*, Nationality* (4 required) | **No — legacy also requires Gender**, Next.js does not. Worth confirming whether Gender being optional in Next.js is intentional (e.g. collected later during onboarding) or a dropped requirement. |
| Birth Date field type | Custom JS date-picker widget bound to a **free-text input** (placeholder `yyyy-mm-dd`) — typing a value directly bypasses the picker entirely | Native HTML `type="date"` input | **No**, different implementation, **but the same defect either way**: confirmed by direct test that legacy's Birth Date field accepts a typed future date (`2030-01-01`) with no validation error, no different from Next.js's confirmed acceptance of DOB `01-01-2030`. This is a shared, cross-app gap, not a regression. |
| Other personal fields | Email, Phone, Address, Pin Code, District, State, Marital Status, Guardian Name, Relation, Blood Group, PAN No, Bank Name, Branch, IFSC Code, Account Number, ESI No, ESI Dispensary, PF No, and more — all optional, all free text | Similarly large optional field set across Personal/Statutory/Bank Details sections | **Broadly equivalent** in scope; exact field-by-field parity not fully itemized in this pass, but no obvious major omissions noticed either direction |
| Onboarding fields (2nd stage) | Legacy's "Onboarding" tab inside the same modal: Joining Date*, Employee ID, Branch*, Department*, Designation*, Employee Type*, Notice Period*, plus a "Policies & Rules" section (Shift Timings, Holiday Calendar, Leave Policy, Superior) — with **two separate save actions** ("Save" for Company Information, then "Complete Onboarding" for the whole thing) | Next.js's separate `/employees/join/{id}/onboard` page: Identity & Login, Professional Details sections | **Similar intent** (both collect employment/company details as step 2), but legacy's two-button save-within-a-tab is a UX quirk worth flagging — an admin could click "Save" on Company Information and believe onboarding is complete when "Complete Onboarding" hasn't been clicked yet. |

## Destructive actions

| Action | Legacy | Next.js | Same? |
|---|---|---|---|
| Delete a joining record | Red trash icon per row, direct in the Action column | Trash/"Discard" icon per row | **Not fully compared** — legacy's delete was intentionally **not tested live** in this pass to avoid permanently deleting real tenant data (no disposable test record existed on the legacy side, unlike Next.js where a purpose-made "Test QA" record was used). Recommend a follow-up pass with an explicitly disposable legacy record if delete-confirmation behavior needs verifying there too. |

---

## What this means for the migration

1. **Structural parity is strong**: both apps use the same two-tab concept (pending joins vs. full employee list), similar required-field sets, and share the exact same underlying defect — no upper bound on Date of Birth — which confirms this is a genuine, longstanding gap in the business logic/data model, not something introduced during the Next.js rewrite.
2. **The biggest behavioral difference is the edit/onboarding interaction model**: legacy's select-a-row-then-use-toolbar pattern (also seen in Employee Access) vs. Next.js's direct per-row links. This is worth a deliberate product decision rather than treating either as "the bug" — but it does mean QA/training material can't assume the two apps behave the same way here.
3. **Next.js's search and empty-state handling are measurably better** (matches mobile, not just name; explicit "No records found." message) — worth keeping, not regressing, as this parallels the same wins already noted in the Employee Access comparison.
4. **Legacy requiring Gender but Next.js not** is a small but real parity gap worth a quick product confirmation.
5. **Legacy's two-button save inside the Onboarding tab** (Save vs. Complete Onboarding) is a UX foot-gun worth keeping in mind if any workflow documentation gets carried over from legacy — Next.js's model (one boundary between "New Join" and "Continue Onboarding") is actually clearer and shouldn't be complicated to match legacy's split.

---

## Notes on testing method

Consistent with prior passes in this project: no record was created or saved on the legacy side during this comparison (the Add Employee modal was opened, a future date was typed into Birth Date to confirm the same validation gap as Next.js, then the modal was closed without submitting). No delete was attempted on legacy, since — unlike the Next.js pass — there was no purpose-made disposable record to safely test against.
