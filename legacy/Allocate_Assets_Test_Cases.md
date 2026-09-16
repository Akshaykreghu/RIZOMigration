# Allocate Assets — Test Cases

**App:** RIZO — HR & Payroll (Next.js), `https://dev.rizo.one/employees/assets`
**Date:** 2026-08-07
**Method:** live UI walkthrough (list, search, Allocate Asset form, Mark Returned). One real allocation/return cycle was performed against a disposable, already-junk employee record ("Test", ID 1000197) using an available (not-currently-allocated) asset, so no other employee's data or currently-active allocation was touched. The cycle was completed (allocated, then immediately marked returned) to restore asset-availability state.

## Page summary

Columns: Employee (name + ID), Asset (name + secondary code/tag), Allocated (date), Returned (date, "—" if still out), Status (Allocated/Returned badge + "Mark Returned" action for still-allocated rows). Toolbar: "Allocate Asset" button, search box ("Search by employee name or ID"). 10 records at the start of this pass, 1 page.

"Allocate Asset" opens a modal: Employee (autocomplete search by name/ID, required), Asset (a `<select>` dropdown, required — populated only with assets that are currently **available**, i.e. not presently allocated to anyone), Allocated Date (native `type="date"`, required), Condition (dropdown: Good / Damaged But Working / Not Working, defaults to Good), Notes (optional free text).

---

## Positive test cases

| # | Title | Steps | Expected result |
|---|---|---|---|
| TC-01 | List loads correctly | Navigate to Allocate Assets | Table loads with Employee/Asset/Allocated/Returned/Status columns; footer reads "Page 1 of 1 (10 total)" |
| TC-02 | Search by employee name | Type `Subash` → Enter | Returns exactly one row: Subash Chandran; footer "Page 1 of 1 (1 total)" |
| TC-03 | Search by employee ID | Type `100091` → Enter | Returns the same single row as TC-02, confirming ID search works as documented |
| TC-04 | Clearing search restores the full list | Clear the box, submit | Full list returns |
| TC-05 | Employee field has live autocomplete | Open Allocate Asset, type `Test` into Employee | Dropdown suggests matching employees (name + ID) as you type, e.g. "Test (1000197)", "MOB TEST (1000187)" |
| TC-06 | Asset dropdown only offers currently-available assets, not ones already allocated | Open Allocate Asset, inspect Asset dropdown options | Only 2 of the ~8 distinct assets seen in the list were selectable ("Dell bag", "Technotip Black") — both of which show "Returned" status in the list. Assets currently shown as "Allocated" to someone did not appear as options. This is a sound design choice: it structurally prevents double-allocating the same physical asset to two people at once. |
| TC-07 | Required-field validation blocks empty submission | Open Allocate Asset, leave everything blank, click Allocate | Native browser validation fires ("Please select an item in the list." on the Asset field) — submission is blocked client-side |
| TC-08 | A full allocate → return cycle works end to end | Allocate "Technotip Black" to employee "Test", then click "Mark Returned" on that row | Row appears immediately after allocating (11 total); after Mark Returned, Returned date is auto-set to today and Status flips to "Returned"; the asset becomes available again in the Asset dropdown for future allocations |

## Negative test cases

| # | Title | Steps | Expected result | Observed |
|---|---|---|---|---|
| TC-09 | **Allocated Date accepts a future date with no upper-bound validation** | Allocate Asset → set Allocated Date to a date in 2030 → submit | Expect either a max-date constraint or a validation error, since an asset can't logically be allocated in the future | **Fail:** saved successfully with no error (`12 Mar 2030`). This is the same recurring gap already documented for Date of Birth in Employee Join (JOIN-002) — now confirmed on a third date field in this app. |
| TC-10 | **Mark Returned has no confirmation step** | Click "Mark Returned" on any allocated row | A state-changing action (ends an active allocation, timestamps it) should arguably ask for confirmation, especially since it can't be undone from the UI (no "un-return" action was found) | **Fail / risk found:** fires immediately on click, no confirm dialog, no undo. Same pattern already flagged for Reset Device in Employee Access (ACC-001) — this is now the third instance of a state-changing action with no confirmation step found in this app. |
| TC-11 | **Returned date is not validated against Allocated date** | Followed directly from TC-08/TC-09: an asset allocated with a (deliberately invalid) future Allocated Date of 12 Mar 2030 was marked Returned today, 07 Aug 2026 | Expect the system to either block this (Returned can't be before Allocated) or at least flag the inconsistency | **Fail / data-integrity gap found:** the system recorded a Returned date earlier than the Allocated date with no error or warning — a logically impossible state (returned before it was allocated). This is a direct downstream consequence of TC-09's missing validation, not a separate root cause. |
| TC-12 | Search does not cover the Asset column | Search `Dell bag` (an exact, visible Asset value) | Ambiguous by design — placeholder only claims "employee name or ID" | **Not a bug, but a scope gap:** returned "No records found." Since Asset is a prominent, visible column, an admin might reasonably expect to search "who has the Dell bag" — worth a product decision on whether Asset should be searchable too. |
| TC-13 | No-match search shows a clean empty state | (see TC-12) | "No records found." not a blank/error table | **Pass:** consistent with the clean empty-state pattern already seen in Employee Join and better than legacy's bare-table pattern |
| TC-14 | No visible way to un-return an asset once marked Returned | Observe the UI for any "re-allocate" or "undo return" action on a Returned row | If Mark Returned was clicked by mistake (see TC-10), expect some recovery path | **Gap confirmed:** Returned rows have no action buttons at all in this pass — the only way to get the asset back in use is to create a brand-new allocation record via "Allocate Asset," which means the original allocation's history (who had it before, for how long) becomes two separate entries rather than one corrected one. Reinforces why TC-10's missing confirmation matters. |

---

## Notes for the team

- **TC-10 combined with TC-09/TC-11 is the most concerning finding**: because Mark Returned has no confirmation and there's no undo, and because Allocated Date has no validation, it's possible to end up with impossible-looking allocation history (like the test record left behind: allocated 12 Mar 2030, returned 07 Aug 2026) with zero friction or warning anywhere in the flow.
- **A disposable test record was used for the live allocate/return cycle** (employee "Test", ID 1000197 — an existing junk/test record, not a real employee), and the asset used ("Technotip Black") was returned to available status immediately after, so there is no ongoing impact on real inventory. The test record's allocation history now shows one clearly-fake entry (12 Mar 2030 → 07 Aug 2026) which the team may want to clean up, though it's low-priority since the employee record itself was already test data.
- **TC-06 (asset dropdown only shows available assets) is a genuine strength** worth preserving during any refactor — it's a structural safeguard against double-allocation that doesn't rely on manual checking.
