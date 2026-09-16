# Allocate Assets — Development & Error Correction List

**Purpose:** consolidates `Allocate_Assets_Test_Cases.md` (Next.js positive/negative test pass) and `Allocate_Assets_Legacy_vs_NextJS_Comparison.md` (legacy vs. Next.js comparison) into one prioritized, actionable list for the dev team.

**Scope note:** the legacy pass in this comparison landed on a different tenant ("GREAT LEAP MPM") than prior Employee Join/Employee Access work ("GLET"), while Next.js remained on "GRTL." More importantly, the legacy module appears to be substantially broken in that tenant, which limited how much could be directly compared — see ASSET-004 through ASSET-006 below. Recommend a manual re-check of the legacy module on a known-good tenant before treating those three as confirmed, tenant-independent defects.

---

## P0 — Fix before this page ships / needs urgent triage

| ID | Title | Where | Description | Recommended fix |
|---|---|---|---|---|
| ASSET-001 | **Mark Returned has no confirmation step** | Next.js | Clicking "Mark Returned" fires immediately — no confirm dialog, and no way to undo it afterward (Returned rows have no further actions). This is the third instance of a state-changing action with no confirmation found in this app (after Reset Device in Employee Access, and the general pattern of Employee Join's Discard at least having one). | Add a confirm step ("Mark this asset as returned? This cannot be undone from here.") before the action fires. Given there's no recovery path afterward (ASSET-003), this is higher priority than a typical missing-confirm issue. |
| ASSET-002 | Allocated Date has no upper-bound validation | Next.js | Confirmed by allocating an asset with a date of 12 Mar 2030 — saved with no error. This is the same recurring gap already flagged for Date of Birth in Employee Join (JOIN-002), now confirmed on a third date field in the app (also seen as future dates in existing production-like data throughout this project). | Add a max-date constraint (can't allocate an asset in the future) client-side and server-side. Given this is now confirmed on 3+ separate date fields across the app, recommend fixing this as a shared date-input component/validator rather than field-by-field. |
| ASSET-003 | **Returned date is not validated against Allocated date, and there's no way to correct a bad allocation after the fact** | Next.js | Direct consequence of ASSET-001+ASSET-002: an asset allocated with a future date (2030) was successfully marked Returned with today's date (2026) — a logically backwards state — and there is no UI path to edit or delete an allocation record once created, only to allocate anew or mark returned. | Two separate fixes: (a) validate Returned date >= Allocated date at write time; (b) consider adding an edit/correct action for allocation records, since right now a mis-click or bad date entry has no recovery path except creating more, disconnected history entries. |
| ASSET-004 | **Legacy: "Add New Asset to Employee" appears to be non-functional** | Legacy | Reproduced twice: clicking this button (with Branch="All" and separately with a specific branch selected) triggers a native browser dialog that immediately freezes the connection used for this testing. The dialog's exact text couldn't be captured because of this, but the consistent, immediate freeze strongly suggests the button is either broken or blocked by an unexpected validation popup. This makes it impossible to create a new asset allocation in legacy in this tenant at all. | Needs a manual (non-automated) check directly in the legacy admin UI to see what this dialog actually says and whether it's tenant-specific or a live defect. If it's a live defect, this is a total blocker for that legacy workflow and should be escalated regardless of migration timeline, since legacy is presumably still in active use during any transition period. |
| ASSET-005 | **Legacy: main grid claims records exist but renders none** | Legacy | With Branch="All", the page footer reads "Displaying 1 to 10 of 10 items" but the table body is completely empty — confirmed on page load and again after manually clicking the grid's refresh control. | Needs investigation: likely a column/row-template binding bug specific to the "All" branch view (per-branch views correctly show empty state with "0 of 0", so the grid mechanism itself works — something about aggregating across branches breaks rendering). |

## P1 — Confirmed data/behavior gaps needing investigation

| ID | Title | Where | Description | Recommended action |
|---|---|---|---|---|
| ASSET-006 | Legacy: Employee filter dropdown has zero options | Legacy | Opening the "Employee" filter on the Asset Allocation page returned "No results found" for every query typed, despite the tenant clearly having employees (used elsewhere in the app in this same project). | Check whether this dropdown's data source (likely an AJAX employee-list endpoint scoped to this module) is broken, misconfigured for this tenant, or genuinely returns none for another reason (e.g. requires a branch to be selected first, which wasn't tested before the button freeze in ASSET-004 ended this line of testing). |
| ASSET-007 | Search does not cover the Asset column | Next.js | Confirmed: searching an exact, visible Asset name ("Dell bag") returns "No records found." — search is scoped to employee name/ID only, per its own placeholder. | Not necessarily a bug (matches its documented scope), but worth a product decision: an admin might reasonably want to search "who currently has X asset." Low priority. |

## P2 — Positive findings worth preserving (not action items, but relevant to migration decisions)

| ID | Title | Where | Description |
|---|---|---|---|
| ASSET-008 | Next.js's Asset dropdown correctly excludes already-allocated assets | Next.js | The "Allocate Asset" form's Asset select only offers assets not currently allocated to anyone — a structural safeguard against double-allocating the same physical item. Confirmed by inspecting the dropdown against the full list of assets shown in the table. Worth explicitly preserving in any future refactor of this page. |
| ASSET-009 | Next.js's required-field validation works correctly | Next.js | Submitting the Allocate Asset form empty correctly triggers native browser validation and blocks submission. No issue found here, unlike the more permissive validation seen on Employee Join's New Join form. |
| ASSET-010 | Next.js's empty-state messaging is clear | Next.js | "No records found." shown on no-match search, consistent with the better pattern already established in Employee Join and flagged as worth keeping across the app. |

---

## Suggested triage order for the next dev cycle

1. **ASSET-004** (legacy Add-asset button reproducibly freezes/fails) — needs a manual, non-automated check first to even understand what's happening; this may turn out to be a tenant-specific issue rather than a codebase-wide one, but it blocks the entire legacy create-flow until resolved either way.
2. **ASSET-005 / ASSET-006** (legacy grid and employee-filter both silently broken for "All" scope) — investigate together, likely related root causes (an "All"/aggregate-scope code path that isn't exercised as often as per-branch paths).
3. **ASSET-001 / ASSET-003** (Mark Returned: no confirm, no undo, no date-order validation) — these compound each other; fixing confirmation alone doesn't address the missing correction path, so scope both together.
4. **ASSET-002** (Allocated Date upper bound) — low effort, and should be bundled with the other date-validation fixes already queued from Employee Join (JOIN-002) as a shared component fix.
5. **ASSET-007** — needs a product decision, not urgent.

---

## Cross-references to prior dev-fix lists in this project

- **ASSET-001** (no confirm on a state-changing action) is the third confirmed instance of this pattern, after ACC-001 (Employee Access Reset Device) and general findings around Employee Join's Discard. Recommend the team treat "destructive/state-changing actions need a confirm step" as an app-wide checklist item, not a per-page fix.
- **ASSET-002** (no future-date upper bound) is the third confirmed instance of this exact gap, after JOIN-002 (Employee Join Date of Birth) and the underlying legacy date-picker behavior documented there. Strongly recommend a single shared date-validation utility/component rather than continuing to fix this field-by-field.
- **ASSET-004's native-dialog freeze** matches the same tooling-limitation pattern noted for Employee Join's Discard action (JOIN-014) — legacy's reliance on unstyled native `confirm()`/`alert()` dialogs continues to be both a UX concern and a recurring blocker for automated testing coverage of legacy.
