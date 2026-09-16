# Salary Structure — Development & Error Correction List

**Purpose:** consolidates `Salary_Structure_Test_Cases.md` (Next.js positive/negative test pass) and `Salary_Structure_Legacy_vs_NextJS_Comparison.md` (legacy vs. Next.js comparison) into one prioritized, actionable list for the dev team.

**Scope note:** legacy and Next.js were checked on different tenants ("GREAT LEAP MPM" vs. "GRTL"), so record counts aren't comparable — these are behavioral/structural/functional findings.

---

## P0 — Fix before this page ships (critical functional breakage)

| ID | Title | Where | Description | Recommended fix |
|---|---|---|---|---|
| SAL-001 | **Salary components are not saved when creating a new structure** | Next.js | Created a test structure with one component (Basic, Fixed Amount, ₹5,000). The structure header (name, gross, dates) saved correctly, but the Components section came back completely empty on both the resulting Edit page and a fresh list reload. A salary structure with zero components cannot be used for actual payroll calculation. | Highest priority in this entire pass. Reproduce immediately and check how the save handler processes the components array — likely a payload-shape mismatch between what the form sends and what the API expects, or a silent failure in a downstream insert. The disposable test record ("QA Test Structure," id 65, now Inactive) was left in place specifically so the team has a live example to debug against. |
| SAL-002 | **Submitting the form with no data produces a raw, unhandled JS error instead of validation** | Next.js | Clicking "Save Structure" with every field empty bypasses all client-side validation and sends a request that fails server-side, surfacing the literal text `Failed to execute 'json' on 'Response': Unexpected end of JSON input` directly on the page. Legacy, by contrast, shows a clean inline "This value is required." under the empty field and never reaches the server. | Add client-side required-field validation matching legacy's pattern (inline error text, no submission), and fix the error-handling path so a server-side failure never surfaces a raw fetch/JSON error to the end user regardless of cause. |

## P1 — Structural/UX gaps materially behind legacy

| ID | Title | Where | Description | Recommended action |
|---|---|---|---|---|
| SAL-003 | Components have no categorization | Next.js | Legacy groups components into 5 clear categories (Monthly Salary Components, Employer Contributions, Statutory Deductions, Variable Salary Benefits, Variable Deductions from Salary), each pre-listing the standard applicable heads. Next.js has one flat 52-item dropdown with no grouping, requiring an admin to manually add every single line item from scratch with no guidance on what's typically included. | Consider adopting legacy's categorized, template-style layout — it's more self-documenting and reduces the chance of an admin forgetting a standard component (e.g. forgetting ESI/EPF contributions entirely, which Next.js's blank-slate approach makes easy to do). |
| SAL-004 | No live computed breakdown in the default view | Next.js | Legacy shows a full Head/Value/Formula table (e.g. "Basic → 7500 → Monthly Gross Salary * .5") immediately when viewing any existing structure, with no extra step. Next.js has a separate "Preview Breakup" calculator requiring a manual Monthly Gross entry and a Calculate click, and it's disconnected from the main edit view. | At minimum, verify Preview Breakup actually works correctly (untested in this pass). Longer-term, consider surfacing a live breakdown by default the way legacy does, since it lets an admin sanity-check a structure without extra steps. |
| SAL-005 | No pagination or search on a 47+ item list | Next.js | All salary structures load in one continuous scroll with no page control and no search box. Legacy has a page-size selector (2/5/10/50/100) and proper pagination. With many near-duplicate names already in the list (multiple "ESI"/"PF" variants), this makes finding a specific structure harder than it needs to be. | Add pagination and/or a search box, matching the pattern already recommended for other list pages in this project. |

## P2 — Misleading UI / behavior mismatches

| ID | Title | Where | Description | Recommended fix |
|---|---|---|---|---|
| SAL-006 | **Trash icon doesn't delete — it deactivates, with no warning** | Next.js | Clicking the trash icon and confirming ("Confirm · Cancel," a good inline pattern) on the disposable test record did not remove it from the list — it only flipped Status from Active to Inactive. The row stayed exactly where it was. | Either change the icon/label to reflect what actually happens (e.g. "Deactivate" with a different icon), or add a genuinely separate delete action if permanent removal is ever needed. As-is, an admin expecting a trash icon to delete will be confused when the record persists. |
| SAL-007 | New structures default to Active with no visible status control | Next.js | No Status/Active-Inactive field exists anywhere in the create or edit form; status appears to be derived from whether today falls within the Start/End Date range rather than being directly settable. This wasn't stated anywhere in the UI. | Confirm this is intentional with the product team, and if so, document/label it clearly (e.g. "Status is automatically determined by the date range") so it isn't mistaken for a missing field. |

## P3 — Data quality / positive findings worth preserving

| ID | Title | Where | Description |
|---|---|---|---|
| SAL-008 | Duplicate-looking entries in existing data | Next.js (existing data) | Two identical "NEW ESI" rows (₹1,000, 30 days, Inactive), and two Components dropdown entries both labeled "Special Allowance" (different underlying IDs). Consistent with the duplicate/junk-data pattern already seen elsewhere in this project — a cleanup candidate, not a new defect. |
| SAL-009 | Calculation-type concepts map closely between apps | Both | Next.js's 7 calculation types (Fixed Amount, Formula, Limit variants, Remaining Balance, Manually Entered) correspond closely to legacy's apparent set (formula/limit/manually/fixed/rembalance, with a "Whichever is lesser/greater" toggle matching Next.js's separate Lower/Upper Bound options). This is one of the stronger parity results found in this project — worth confirming as intentional and complete, but not something to "fix." |
| SAL-010 | Next.js's inline Confirm/Cancel delete pattern is worth reusing elsewhere | Next.js | Independent of SAL-006's mislabeling issue, the underlying mechanism (row switches to inline "Confirm · Cancel" text instead of a native browser dialog) is a good pattern — it avoids the native-`confirm()` freezing issues documented for several legacy pages throughout this project, and is better than some other Next.js pages that still have no confirmation at all (Reset Device in Employee Access, Mark Returned in Allocate Assets). Recommend using this as the template when adding confirmations to those other actions. |

---

## Suggested triage order for the next dev cycle

1. **SAL-001** (components not saving) — blocking; makes the entire "create structure" flow non-functional for its core purpose.
2. **SAL-002** (no validation, raw error leak) — should be fixed alongside SAL-001 since both live in the same save path.
3. **SAL-006** (trash icon mislabeling) — quick fix, but important since it could lead an admin to believe they've deleted something they haven't (or vice versa, believe an "inactive" record is still fully live).
4. **SAL-003 / SAL-004** (categorization, live breakdown) — larger UX investment; scope as a deliberate redesign task informed by legacy's existing, better-organized pattern rather than small patches.
5. **SAL-005 / SAL-007** — lower urgency, bundle into a general list-page usability pass alongside the pagination/search gaps already flagged for other modules in this project.

---

## Cross-references to prior dev-fix lists in this project

- **SAL-002's validation gap** matches the same permissive-validation pattern already flagged repeatedly (Employee Join's JOIN-002/003, Allocate Assets' ASSET-002) — but is notably worse here because it also leaks a raw technical error, which none of the earlier instances did. Recommend escalating "no client-side validation + raw error on failure" as its own category of finding when reporting to engineering leadership, since this occurrence is more severe than the earlier date-validation gaps.
- **SAL-006 (trash icon doesn't delete)** is a new pattern not seen elsewhere in this project — the opposite problem from Employee Access/Allocate Assets, where destructive actions fire with *no* confirmation at all. Here there's a confirmation step, but it doesn't do what it claims to. Worth noting both failure modes (unconfirmed-but-real deletion, and confirmed-but-fake deletion) exist somewhere in the app.
- **SAL-005 (no pagination/search)** is the fourth confirmed instance of this exact gap, after Employee Access (ACC-007), Employee Join (JOIN-007), and general list-page patterns noted throughout. Strongly recommend this be fixed once as a shared list-page component rather than continuing to find and re-flag it module by module.
