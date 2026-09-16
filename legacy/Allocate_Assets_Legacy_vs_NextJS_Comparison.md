# Allocate Assets — Legacy vs. Next.js Comparison

**Legacy:** https://in.mypayrollmaster.online/Dashboard → Employee → Allocate Assets to Employees
**Next.js:** https://dev.rizo.one/employees/assets
**Date:** 2026-08-07

**Data/tenant caveat — important and different from prior comparisons in this project:** the legacy session for this pass was authenticated against a **different tenant than previous Employee Join/Employee Access comparisons** — "GREAT LEAP MPM" rather than the earlier "GLET" test tenant — while Next.js remained on "GRTL." This wasn't a deliberate choice; it's whichever tenant the legacy session was already authenticated as. So this is, if anything, an even less direct comparison than prior passes — treat every finding below as behavioral/structural, and treat the legacy-side bugs as findings about that module's code, not about GLET's or GRTL's specific data.

---

## Headline answer

**Could not complete a like-for-like comparison, because the legacy module appears to be broken in this tenant.** The Next.js "Allocate Assets" page works cleanly end-to-end (list, search, allocate, return, all confirmed via a live test cycle). The legacy "Allocate Assets to Employees" page, by contrast, showed three separate problems in this pass: the main grid claims 10 records exist but renders zero rows when Branch is set to "All"; the Employee filter dropdown returns "No results found" with no employees loadable at all; and the primary "Add New Asset to Employee" button triggers a native browser dialog that consistently froze the testing tool, reproduced twice (once with Branch="All", once with a specific branch selected), so its exact message couldn't be captured. This is a substantially different situation from the two modules compared so far, where legacy was at least fully navigable even when it had UX gaps.

---

## List page comparison

| Aspect | Legacy | Next.js | Same? |
|---|---|---|---|
| Columns | Employee Name, Employee Code, Branch, Asset Name, Allocated Date, Condition, Type | Employee (name+ID), Asset (name+code), Allocated, Returned, Status | **Similar intent**, Next.js additionally surfaces Returned date and a clear Status badge directly; legacy's "Type" column purpose wasn't observable since no rows rendered |
| Filters | Branch dropdown (All + 8 branches), Employee dropdown (searchable, but returned "No results found" in this pass) | Single search box (name or ID) | **Different model** — legacy offers structured filters, Next.js offers free-text search; can't fully compare given legacy's Employee filter wasn't functional in this pass |
| **Grid renders records it claims exist** | **Fail — confirmed bug:** with Branch="All", the footer read "Displaying 1 to 10 of 10 items" but the table body was completely empty (verified twice, including after manually clicking the grid's refresh icon) | **Pass:** all 10 (then 11) records rendered correctly and matched the stated count throughout this pass | **No — legacy-specific defect.** This alone makes the legacy module effectively unusable for its core purpose (viewing existing allocations) in this tenant. |
| Branch-specific filtering | Selecting any individual branch (tried: EKM, Head Office, ALUVA (Demo Branch), Banglore) correctly returned "0 of 0 items" — consistent and correctly-behaving pagination, just always empty | Not applicable (Next.js has no branch filter on this page) | N/A for direct comparison, but notable: since every individual branch returns 0 while "All" claims 10, either the 10 records have no branch value at all (a data issue) or the "All" view has a rendering bug unrelated to per-branch views (a code issue) — worth the dev team checking both. |
| Employee filter dropdown | **Fail:** typing/opening the Employee select produced "No results found" — no employees loaded into this dropdown at all | N/A — Next.js's employee picker lives inside the Allocate Asset **form**, not as a list filter, and it worked correctly there (see below) | **No** — legacy's filter dropdown appears to have a broken data source independent of the grid issue above |

## Add/Allocate action comparison

| Aspect | Legacy | Next.js | Same? |
|---|---|---|---|
| Entry point | "Add New Asset to Employee" button | "Allocate Asset" button (opens a modal) | Same concept |
| **Button actually opens the create form** | **Fail — confirmed bug, reproduced twice:** clicking "Add New Asset to Employee" triggers a native browser dialog (alert or confirm) that immediately freezes the browser-automation connection (`Input.dispatchMouseEvent` timeout). This happened both with Branch="All" and with a specific branch (EKM) selected, so it isn't limited to the "All" view. Because the dialog blocks the entire connection, its exact wording couldn't be captured — but this matches the same native-dialog-freeze pattern seen elsewhere in this legacy app throughout the project (e.g. Employee Join's Discard). | **Pass:** "Allocate Asset" opened a clean modal every time, with Employee autocomplete, Asset dropdown (filtered to available assets only), Allocated Date, Condition, and Notes — fully functional, confirmed via a live allocate→return test cycle | **No — this is the most serious finding in this comparison.** The legacy module's primary create action does not appear to be usable at all in this tenant/session. |
| Required-field validation | Not observable — the form itself couldn't be reached | Confirmed: native browser validation blocks submission when Asset is left unselected | N/A |
| Asset selection constraint (only available assets offered) | Not observable | Confirmed: Asset dropdown only lists assets not currently allocated to anyone, preventing double-allocation | N/A |

## What could and couldn't be tested

Given the above, this pass could not compare: the Add-asset form's fields/required-fields, condition options, date validation, or a return/mark-returned action in legacy (no rows to act on, and no way to select an employee to create one). Everything on the Next.js side was fully testable and is documented in `Allocate_Assets_Test_Cases.md`.

---

## What this means for the migration

1. **This is not a "which app has more features" comparison this time — it's a "does the legacy module even work" finding.** Before drawing any parity conclusions, the team should verify whether the "GREAT LEAP MPM" tenant's Asset Allocation module is known-broken (e.g. a data/config issue specific to that tenant) or whether this reflects a genuine, currently-live defect in the legacy codebase that any tenant could hit.
2. **If this is a live legacy defect**, it's good news for the migration in one sense: Next.js's Allocate Assets page is already fully functional and doesn't inherit any of these three legacy problems. There's no legacy behavior to preserve here — the team should treat Next.js's current implementation as the reference, not legacy's.
3. **The native-dialog-freeze pattern recurring here (as it has on other legacy pages)** continues to suggest legacy leans on unstyled native `alert()`/`confirm()` popups in more places than have been fully inventoried in this project. If any legacy behavior does need to be ported forward, these dialogs' actual wording should be captured through the legacy admin UI directly (not through automation) since they can't be read through the tooling used across this project.
4. **Recommend a manual, human check of this same page** (Employee → Allocate Assets to Employees, Great Leap MPM tenant) to confirm these findings outside of automation, since all three problems were significant enough that they may simply reflect this specific account/tenant being in a broken state rather than representative of the legacy codebase generally.

---

## Notes on testing method

No employee or asset data was modified on the legacy side (the create form could never be reached to attempt a save). On the Next.js side, one real allocate→return cycle was performed against a disposable, pre-existing test employee record ("Test", ID 1000197) using an asset that was available for allocation, and the asset was returned immediately afterward to restore inventory availability — see `Allocate_Assets_Test_Cases.md` for details. The native-dialog freeze on legacy's "Add New Asset to Employee" button was encountered twice; each time, the tab was closed and a fresh tab created per the established recovery procedure in this project, since the dialog blocks the entire CDP connection until manually dismissed.
