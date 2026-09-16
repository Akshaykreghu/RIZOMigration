# Salary Structure — Legacy vs. Next.js Comparison

**Legacy:** https://in.mypayrollmaster.online/Dashboard → Company → 7. Salary Structure
**Next.js:** http://localhost:3000/setup/salary-structure (Company Setup → Salary Structure)
**Date:** 2026-08-08

**Data/tenant caveat:** both sessions in this pass happened to be on the same legacy tenant used for the Allocate Assets comparison ("GREAT LEAP MPM," 23 structures) while Next.js was on "GRTL" (47 structures) — different tenants, not a record-for-record comparison. Treat everything below as behavioral/structural.

---

## Headline answer

**Legacy's Salary Structure module is materially more capable and more carefully built than Next.js's current implementation.** Legacy organizes salary components into five clear categories (Monthly Salary Components, Employer Contributions, Statutory Deductions, Variable Salary Benefits, Variable Deductions from Salary), shows a live computed Head/Value/Formula breakdown for the selected structure without any extra step, and has working required-field validation with clean inline error text. Next.js, by contrast, uses one flat 52-item dropdown with no categorization, has no live breakdown integrated into the main view, and — most importantly — appears to silently drop the components an admin adds when saving a new structure (see `Salary_Structure_Test_Cases.md`, TC-08). This is the widest capability gap found between legacy and Next.js across all the modules compared in this project so far.

---

## List/selection page comparison

| Aspect | Legacy | Next.js | Same? |
|---|---|---|---|
| Layout | Two-panel: a paginated structure list on the left (Name, Min Monthly Gross Salary), a details/edit panel on the right that loads in place when a row is clicked | Single flat page: all structures listed top-to-bottom (Name, Example Gross, Fixed Days, Status, Actions); "Add New" and "Edit" navigate to separate pages | **No — different interaction model.** Legacy's master-detail pattern scales better for browsing many structures; Next.js's flat list requires full page navigation for every view/edit. |
| Pagination | Page-size selector (2/5/10/50/100) + first/prev/next/last, "Page X of Y", "Displaying 1 to 10 of 23 items" | **None** — all 47 records render in one continuous scroll, no page control at all | **No — confirmed gap.** With lists this size (and both already showing signs of accumulating duplicate/junk entries), Next.js's lack of any pagination or search is a bigger usability problem here than on other pages compared earlier in this project, since salary structure names are often near-identical (many "ESI"/"PF" variants). |
| Search/filter | None observed beyond pagination | None | **Same** — neither has a search box on this particular list, though legacy's smaller page sizes make manual scanning more manageable. |
| Toolbar actions | "New", "Remove" (row-selection based) | "Add New" only; per-row Edit/Delete icons | Different interaction pattern (select-then-act vs. direct-per-row-action) — same recurring difference already seen on Employee Access and Employee Join in this project. |

## Structure details/edit form comparison

| Aspect | Legacy | Next.js | Same? |
|---|---|---|---|
| Required fields | Structure Name*, Pay Period* (Monthly/Daily), Salary Calculation Logic* (Calendar/Working days/Fixed days), Method Explanation* (auto-filled based on logic) | Structure Name*, Example Monthly Gross*, Start Date*, End Date* | **Different concepts required** — legacy requires a calculation-logic choice; Next.js requires an example gross figure and a date range instead. Neither is a strict superset. |
| **Required-field validation** | **Pass:** submitting with Structure Name empty shows a clean inline error, "This value is required.", directly under the field | **Fail:** submitting completely empty produces no client-side validation at all, and the server request fails ungracefully with a raw leaked error (`Failed to execute 'json' on 'Response': Unexpected end of JSON input`) shown directly on the page — see `Salary_Structure_Test_Cases.md` TC-09 | **No — legacy is meaningfully better here.** This is the same kind of gap seen with Employee Join's permissive validation, but worse: Next.js doesn't just accept bad data silently, it surfaces a raw JS/fetch error to the end user. |
| Component organization | Components are grouped into 5 labeled categories (Monthly Salary Components, Employer Contributions, Statutory Deductions, Variable Salary Benefits, Variable Deductions from Salary), each pre-listing the relevant components as fixed rows (some with checkboxes to include, like WWF Employer Contribution) rather than an ad-hoc "add item" flow | One undifferentiated "Components" section; each row is manually added via "Add Component," picking from a single flat 52-item dropdown with no grouping at all | **No — legacy is structurally clearer.** An admin building a structure in legacy sees the full shape of what's available (all standard heads, contributions, deductions) up front; in Next.js, they only see whatever they've manually added, with no categorized guidance on what's typically included. |
| Calculation types | Formula, Limit (with "Whichever is lesser/greater" radio), Fixed, Manually, Remaining Balance — inferred from the component labels shown (e.g. "(formula)", "(limit)", "(manually)") | Fixed Amount, Formula (% of gross/expression), Limit (lesser of value or formula), Limit — With Lower Bound, Limit — With Upper Bound, Remaining Balance, Manually Entered | **Close conceptual parity** — this is actually one of the stronger matches found: Next.js's 7 calculation types map closely onto legacy's apparent set, suggesting the underlying calculation engine concepts were carried over faithfully even though the surrounding UI wasn't. |
| **Live computed breakdown** | **Pass:** viewing an existing structure (read mode, before clicking Edit) shows a full Head / Value / Formula table computed against the structure's own Min Salary/Wage — e.g. "Basic - (formula) - 0 → 7500 → Monthly Gross Salary * .5" — visible immediately, no extra action needed | **Partial:** a separate "Preview Breakup" calculator exists on the Edit view (enter a Monthly Gross, click Calculate) but is a distinct, manual step rather than an always-visible computed breakdown; its correctness wasn't verified in this pass since it wasn't exercised | **No — legacy's default view is more informative out of the box.** Worth checking whether Next.js's Preview Breakup, once actually used, produces equivalent output — but even if it does, requiring an extra manual step for what legacy shows by default is a regression in information density. |
| **Components actually persist on save** | Not independently re-verified in this pass (no new structure was saved in legacy, to avoid creating clutter in a live tenant), but the read-mode breakdown for an *existing* structure ("Salary 15K-21K") correctly showed real formulas and computed values, implying its components did save correctly at some point | **Fail — confirmed bug:** see `Salary_Structure_Test_Cases.md` TC-08. A component added on the create form (Basic, Fixed Amount, 5000) was completely absent after saving, both on the resulting Edit page and after a fresh list reload. | **No — this is the single most important functional gap found in this comparison.** Legacy structures visibly retain their components; the one Next.js structure created live in this pass did not. |

## Destructive actions

| Action | Legacy | Next.js | Same? |
|---|---|---|---|
| Delete a structure | "Remove" (row-selection based) — not tested live in this pass to avoid disrupting the shared legacy tenant, especially given the freezing issues already encountered with native dialogs elsewhere in this project | Trash icon on each row → inline "Confirm · Cancel" text (not a native dialog) | Not directly comparable since legacy's Remove wasn't exercised, but worth noting Next.js's confirmation pattern here is good — see next row. |
| **What "Delete" actually does** | Not observed | **Confirmed to not delete** — clicking the trash icon and confirming only changed the test record's Status from Active to Inactive; the row remained in the list. See `Salary_Structure_Test_Cases.md` TC-10. | N/A, but flagging since the icon (trash) doesn't match the actual behavior (deactivate) regardless of how legacy's Remove behaves. |

---

## What this means for the migration

1. **The components-not-saving bug (TC-08) should be treated as the top-priority item from this entire pass.** A salary structure with no components is not usable for payroll — if this is a widespread, current defect (not specific to this one test), it would affect anyone creating new salary structures in Next.js today.
2. **Legacy's categorized component layout and live breakdown are worth deliberately porting forward**, not just matching feature-for-feature. They make the tool self-documenting (an admin can see what a normal structure looks like) in a way Next.js's flat dropdown doesn't.
3. **Legacy's inline required-field validation is the standard to match.** Next.js's raw leaked error on empty submission is a regression that should be fixed alongside the broader validation pass already flagged for other pages in this project (Employee Join, Allocate Assets).
4. **The calculation-type parity (Fixed/Formula/Limit/Manually/Remaining Balance) is a genuine strength** — this suggests the payroll calculation engine's concepts were carried over correctly even though the surrounding editing UI needs work. Worth confirming with the team that this mapping is intentional and complete (e.g., legacy's "Whichever is lesser/greater" toggle maps to Next.js's separate "Limit — With Lower Bound" / "Limit — With Upper Bound" options — same idea, different UI shape).
5. **Next.js's list needs pagination or search before the list of structures grows much further** — at 47 records with several near-duplicate names already, this is close to becoming unmanageable without it.

---

## Notes on testing method

One real structure was created in Next.js ("QA Test Structure," disposable/clearly-named, ₹10,000 gross, Aug 2026–Aug 2027 date range) to test the create flow end to end; it was left in the system afterward in a deactivated state (via the trash-icon action, which turned out to deactivate rather than delete — see TC-10) as a reproduction case for the components bug. No structure was created or deleted in legacy during this pass, to avoid adding clutter to a shared tenant and given the native-dialog freezing risk already documented elsewhere in this project for destructive legacy actions.
