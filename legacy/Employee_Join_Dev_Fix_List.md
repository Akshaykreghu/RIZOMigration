# Employee Join — Development & Error Correction List

**Purpose:** consolidates `Employee_Join_Test_Cases.md` (Next.js positive/negative test pass) and `Employee_Join_Legacy_vs_NextJS_Comparison.md` (legacy vs. Next.js comparison) into one prioritized, actionable list for the dev team. Each item has an ID for ticket reference, a severity, and a recommended action.

**Scope note carried over from both source reports:** legacy and Next.js were checked on different tenants (legacy = "GLET" test data, Next.js = "GRTL" real-looking data plus one deliberately-created test record), so nothing here depends on the two datasets matching — these are behavioral/structural/functional findings.

---

## P0 — Fix before this page ships (real functional breakage, live-tested)

| ID | Title | Where | Description | Recommended fix |
|---|---|---|---|---|
| JOIN-001 | **A record saved with invalid data becomes stuck — Edit and Continue Onboarding silently no-op** | Next.js | Created a test record ("Test QA") with a future DOB, a 14-digit mobile number, and a non-numeric Aadhaar value — all accepted at save time with no error. Afterward, both "Edit" and "Continue Onboarding" produce no navigation and no error message on that specific row, while working normally on clean rows. | This is the single most important finding in this pass: bad input doesn't just create messy data, it can strand a record in a state the admin can't recover from through the UI. Reproduce by isolating each field (future DOB alone, then oversized mobile alone, then malformed Aadhaar alone) to find the specific trigger, then add both the missing input validation and a safe recovery path for any records already in this state. |
| JOIN-002 | Date of Birth has no upper-bound validation, confirmed in both apps | Both | Next.js: saved a DOB of `01-01-2030` with no error. Legacy: typed the same style of future date into the Birth Date field with no error (not saved, to avoid creating real data, but the client-side acceptance was confirmed). Pre-existing data in both apps' tenants already contains similar future-dated records. | Add a max-date constraint (DOB cannot be later than today, and arguably not later than ~13-16 years ago given minimum working age) on both the client and server. Since this is confirmed on both apps and already present in production-like data, this should be treated as a shared, pre-existing defect to fix in both codebases, not a Next.js regression. |
| JOIN-003 | Mobile and Aadhaar/ID fields accept malformed values with no format enforcement | Next.js (confirmed); legacy not separately re-tested but has no visibly different constraints on the equivalent fields | Mobile accepted a 14-digit value (`12345678901234`); Aadhaar (a *required* field) accepted non-numeric junk (`TESTAADHAAR1`). Existing data in both tenants shows the same pattern already (oversized mobile numbers, junk district values). | Add format validation (10-digit mobile, numeric Aadhaar of the correct length) client-side and server-side. Treat as high priority given JOIN-001 shows this isn't just cosmetic — it can break downstream functionality. |

## P1 — Feature/behavior gaps worth resolving before parity sign-off

| ID | Title | Where | Description | Recommended fix |
|---|---|---|---|---|
| JOIN-004 | Edit/onboarding interaction model differs between apps | Legacy vs Next.js | Legacy uses select-a-row-then-toolbar-action (opens one tabbed modal: Personal Info / Other Details / Onboarding). Next.js uses two separate flows: direct "Edit" and direct "Continue Onboarding" links, the latter going to its own page. Same underlying pattern difference already flagged in the Employee Access comparison (select-then-act vs. direct-per-row-action). | Get a product decision on which interaction pattern is the intended standard across the app — this is now confirmed as a recurring, not page-specific, difference between legacy and Next.js. Don't fix piecemeal per page. |
| JOIN-005 | Legacy requires Gender on Add Employee; Next.js does not | Legacy vs Next.js | Legacy's required fields: Name, Birth Date, Gender, Nationality, Aadhaar No (5). Next.js's required fields: First Name, Date of Birth, Aadhaar/ID Card, Nationality (4) — Gender is present but not required. | Confirm with product whether Gender should be required in Next.js too, or whether this was an intentional relaxation. |
| JOIN-006 | Save/Continue flow gets stuck on a "Loading…" state after creating a new record | Next.js | After Save & Continue on New Join, the page navigated to `/employees/join/44` but displayed a stuck "Loading…" with no content resolving; had to navigate back to the list manually to confirm the record was actually created. Possibly related to JOIN-001/JOIN-003 (same record). | Investigate independently of the stuck-record issue — even for a record that *isn't* otherwise broken, confirm this loading state resolves normally. If it's specific to malformed-data records, note that as the likely root cause once JOIN-001 is investigated. |

## P2 — UX inconsistencies worth resolving

| ID | Title | Where | Description | Recommended fix |
|---|---|---|---|---|
| JOIN-007 | No page-size control on Employee Joining list | Next.js | Legacy has a 10/20/30/40/50 selector; Next.js has a fixed page size. Same gap already flagged for Employee Access (ACC-007). | Low-effort addition; add a page-size dropdown for consistency across both pages that have this gap. |
| JOIN-008 | Legacy's search doesn't cover Phone despite it being a shown column | Legacy | Confirmed: searching an exact phone number visible in a row (`9846736453`) returned the unfiltered full list, not a match. Name search works correctly. Same gap already flagged for Employee Access (ACC-013) — now confirmed on a second legacy page. | Since Next.js's Employee Join search already correctly covers name **and** mobile (confirmed working, TC-03), no Next.js fix is needed here — flagging only so legacy's gap isn't assumed fixed by looking at Next.js, and so the pattern is recognized as systemic in legacy rather than one-off. |
| JOIN-009 | Legacy's two-step save inside the Onboarding modal tab is a UX foot-gun | Legacy | The Onboarding tab has its own "Save" button for Company Information, separate from the "Complete Onboarding" button below it. An admin could click Save and believe onboarding is complete when it isn't. | Not a Next.js issue (Next.js's single "Continue Onboarding" boundary is clearer) — flagging as a legacy usability note, low priority given legacy's role in the migration. |
| JOIN-010 | Empty-state messaging differs on no-match search | Legacy vs Next.js | Legacy: bare empty table, "Displaying 0 to 0 of 0 items", no message. Next.js: clear "No records found." Same gap already flagged for Employee Access (ACC-014), now confirmed here too. | No fix needed for Next.js — already the better pattern. No action needed. |

## P3 — Data quality / open questions (low urgency)

| ID | Title | Where | Description | Recommended action |
|---|---|---|---|---|
| JOIN-011 | Duplicate-looking rows in legacy's Employee Joining list | Legacy (existing data) | Multiple rows with identical Name/DOB/Phone/Email/District ("Gawtham KS" appears 6 times identically; several "Arun" rows share a phone number) | Likely test-data artifacts or a missing duplicate-detection check on save. Worth a data-cleanup pass; not something to specifically replicate-check in Next.js unless the same no-duplicate-check gap is confirmed there too. |
| JOIN-012 | All Employees tab column sets differ between apps | Legacy vs Next.js | Legacy shows Profile Completion % and a document download icon; Next.js shows Department and Mobile directly, plus a Deactivate action. Neither is a superset of the other. | Needs a product decision on which columns matter most for the Next.js All Employees view — not a bug, a design gap worth a deliberate choice rather than an accidental omission. |
| JOIN-013 | Legacy delete/discard behavior not verified | Legacy | Unlike Next.js (where a disposable "Test QA" record existed to safely test Discard), legacy had no equivalent disposable record, so delete-confirmation behavior wasn't tested live to avoid destroying real tenant data. | Follow-up pass recommended: create a throwaway legacy record specifically to test delete-confirmation parity with Next.js's Discard (which does show a native confirm dialog — see JOIN-014). |
| JOIN-014 | Next.js's Discard action's outcome couldn't be confirmed through normal means due to a testing-tool limitation | Next.js | Discard on the "Test QA" record triggered a native browser confirm() dialog (an improvement over Reset Device's total lack of confirmation on the Employee Access page). The dialog froze the browser-automation connection; after recovery, the record was still present, so no deletion occurred in this session — but this wasn't a deliberate "click Cancel" test. | Not an app bug — noting for the record that Discard does have a confirm step (good), and that the "Test QA" test record (id 44, future DOB, oversized mobile, malformed Aadhaar) is still live and intentionally left in place as a reproduction case for JOIN-001. Delete it manually only after JOIN-001 has been investigated. |

---

## Suggested triage order for the next dev cycle

1. **JOIN-001** (stuck records after invalid save) — highest priority; this is a functional break, not just a data-quality issue, and the reproduction case is already sitting live in the Next.js Employee Joining list (id 44) for the team to use.
2. **JOIN-002 / JOIN-003** (DOB upper bound, Mobile/Aadhaar format validation) — root cause of JOIN-001; fixing these should be done alongside investigating JOIN-001, not after.
3. **JOIN-006** (stuck "Loading…" after save) — verify independently once JOIN-001's cause is understood.
4. **JOIN-004** (edit/onboarding interaction model) — needs a product decision; this pattern now shows up on two pages (Employee Access and Employee Join), so it's worth deciding once at the app level rather than per page.
5. **JOIN-005** (Gender required or not) — quick product confirmation.
6. Everything else in P2/P3 — batch into a cleanup pass once the above are resolved.

---

## Resolution log

- **JOIN-005 (Gender required) — RESOLVED 2026-09-02.** Product decision taken to match legacy:
  Gender is now required on New Join (client `required` + submit guard in `NewJoinForm.tsx`), on the
  Edit form (`JoinDetail.tsx`, field-error styled), and server-side in `POST /api/employees/join`
  and `PUT /api/employees/join/[id]` (400 "Gender is required"). Import Employee's template column
  became `Gender *` and the import route now rejects rows with no Gender.
- **JOIN-004 (edit/onboarding interaction model)** — still open; deferred product decision, not
  actioned in the 2026-09-02 pass.

## Cross-references to the Employee Access dev-fix list

Three findings here are confirmed repeats of patterns already documented in `Employee_Access_Dev_Fix_List.md`, now seen on a second page:
- Select-then-act vs. direct-per-row-action interaction model (ACC pattern ↔ JOIN-004)
- Legacy search not covering a visibly-shown column (ACC-013 ↔ JOIN-008)
- Legacy's empty-state messaging vs. Next.js's clearer message (ACC-014 ↔ JOIN-010)
- Next.js missing a page-size selector that legacy has (ACC-007 ↔ JOIN-007)

Recommend the team treat these four as **app-wide patterns** to fix once (in a shared component or convention) rather than as four separate page-level tickets each.
