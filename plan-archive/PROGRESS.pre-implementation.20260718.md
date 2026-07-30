# RIZO Migration — Execution Progress

**Reference:** See `MIGRATION_PLAN.md` for full technical details.  
**Rule:** Mark each task `[x]` immediately when completed. Never batch.

---

## Phase 0: Pre-Work
**Status: COMPLETE** (table names, stored procs, and controller mapping done in MIGRATION_PLAN.md sections 7 & 8)

- [x] Read complete `mypayrol_trial.sql` — table definitions extracted (MIGRATION_PLAN.md §7)
- [x] Map CakePHP Model `$useTable` to real table names (MIGRATION_PLAN.md §7)
- [x] Document stored procedure parameters (MIGRATION_PLAN.md §8)
- [x] Map all active (non-backup) controllers to features (MIGRATION_PLAN.md §2.2)
- [ ] Create local MySQL test instance with `mypayrol_control_db` + `mypayrol_trial` ← **manual step for user**
- [ ] Verify stored procedures callable from test script ← **manual step after MySQL is up**

**Note:** MySQL setup is a manual environment step. Code work begins at Phase 1.

---

## Phase 1: Infrastructure
**Status: ✅ COMPLETE** — Build passes clean. `npm run dev` will serve the app.

> **Note:** `create-next-app` installed Next.js **16.2.9** (not 14 as planned). Differences handled:
> - `src/middleware.ts` is now `src/proxy.ts` (Next.js 16 renamed the convention)
> - Turbopack is default in Next.js 16; webpack config replaced with `turbopack: {}`
> - `useSearchParams()` requires `<Suspense>` boundary (applied in login page)

### 1.1 Project Initialization
- [x] Initialize Next.js project (`rizo/`) — **v16.2.9** with TypeScript, Tailwind, App Router, src-dir
- [x] Install core dependencies (next-auth, mysql2, tanstack query/table, react-hook-form, zod, clsx, tailwind-merge)
- [x] Install export/chart deps (jspdf, jspdf-autotable, xlsx, recharts, lucide-react)
- [x] Install bcryptjs + type defs

### 1.2 Core Library Files
- [x] `src/lib/db.ts` — dual-pool connection manager (controlPool + per-company pool cache, global singleton for hot-reload)
- [x] `src/lib/auth.ts` — NextAuth CredentialsProvider + SHA1→bcrypt transparent upgrade + planId in JWT
- [x] `src/lib/utils.ts` — cn() + formatDate, formatCurrency, formatYearMonth, currentYearMonth
- [x] `src/lib/company-config.ts` — per-company feature flag helper (getCompanyConfig)
- [x] `src/lib/features.ts` — plan_feature gate helpers (isPlanFeatureEnabled, getPlanFeatures)
- [x] `src/types/next-auth.d.ts` — session + JWT type extensions

### 1.3 Auth & Routing
- [x] `src/proxy.ts` — route protection proxy (all routes except /login and /api/auth)
- [x] `src/app/api/auth/[...nextauth]/route.ts` — NextAuth handler
- [x] `src/app/(auth)/login/page.tsx` — login form (company code + username + password) with Suspense boundary

### 1.4 Base Layout & Shell Components
- [x] `src/app/(dashboard)/layout.tsx` — authenticated layout shell (server-side session check)
- [x] `src/components/layout/Sidebar.tsx` — collapsible sidebar with admin/employee visibility
- [x] `src/components/layout/Header.tsx` — top header with user info + logout
- [x] `src/app/(dashboard)/dashboard/page.tsx` — dashboard stub (stats placeholder)
- [x] `src/components/data-table/DataTable.tsx` — reusable TanStack Table v8 (client+server pagination, sorting)
- [x] `.env.local` — local dev environment variables

### 1.5 Providers & Config
- [x] `src/app/layout.tsx` — root layout with Providers (SessionProvider + QueryClientProvider)
- [x] `src/app/providers.tsx` — client providers wrapper
- [x] `src/app/page.tsx` — root redirect to /dashboard
- [x] `next.config.ts` — Turbopack config

**Milestone:** ✅ Build passes. Connect MySQL and run `npm run dev` to verify login → dashboard flow.

---

## Phase 2: Company Setup + Employee Core
**Status: ✅ COMPLETE (all setup screens done, including deferred cleanup items)**

### 2.1 Dashboard
- [x] `GET /api/dashboard/stats` — employee count, present today, pending leaves
- [x] `GET /api/dashboard/events` — birthdays and work anniversaries (7-day window)
- [x] `GET /api/dashboard/notifications` — pending leave + salary increment notifications
- [x] `/dashboard` page — admin view (stats cards, events, notifications); graceful fallback if DB not connected
- [ ] `/dashboard` page — employee view (personal summary, leave balance, attendance) ← Phase 9 polish

### 2.2 Organizational Setup (CRUD)
- [x] Branches — `GET/POST /api/setup/branches`, `PUT/DELETE /api/setup/branches/[id]`, `/setup/branches` page
- [x] Departments — `GET/POST /api/setup/departments`, `PUT/DELETE /[id]`, `/setup/departments` page
- [x] Designations — `GET/POST /api/setup/designations`, `PUT/DELETE /[id]`, `/setup/designations` page
- [x] Grades — `GET/POST /api/setup/grades`, `PUT/DELETE /[id]`, `/setup/grades` page
- [x] Financial Year — `GET/POST /api/setup/financial-year`, `PUT/DELETE /[id]`, `/setup/financial-year` page (branch-scoped, duplicate branch/status/type/year rejected via DB unique key)
- [x] Holiday Calendar — `holiday-groups` + `holidays` APIs, `/setup/holidays` page (group→holiday drill-down); group delete blocked if employees (`emp_proff.HOLIDAY_GROUP_ID`) assigned
- [x] Attendance Config (DB Config) — `GET/PUT /api/setup/attendance-config`, `/setup/attendance-config` page; validates `attendance_format` (A/B) and `attendance_date` (0-31), the two fields `att_start_end_fn` needs for Phase 3
- [x] Shifts — full parity port of `working_day_time_procedures` + `shift_exceptions`, `GET/POST /api/setup/shifts`, `PUT/DELETE /[id]`, `/setup/shifts` list + `/setup/shifts/new` + `/setup/shifts/[id]` bespoke form (weekday grid, 6 duty windows, grace/OT rules, conditional exceptions sub-table)
- [x] Reusable `SetupCrudPage` component (`src/components/setup/SetupCrudPage.tsx`) — extended with `type` (date/number/select/checkbox), `options`, and `queryParams` for scoped lists (used by Holidays)

### 2.3 Company Profile
- [x] `GET/PUT /api/company` — reads/updates `comp_contact_info`
- [x] `/setup/company` page

### 2.4 Employee Management
- [x] `GET /api/employees` — list (admin: all with pagination/search/status/branch filters; employee: self only)
- [x] `POST /api/employees` — create new employee (emp_details + emp_proff + emp_ctc_upload in transaction, duplicate id_card/lwf_code check)
- [x] `GET/PUT /api/employees/[id]` — view/edit profile (personal, professional, salary & statutory, bank details)
- [x] `PATCH /api/employees/[id]` — activate/deactivate (status 1/2)
- [x] `POST /api/upload` — generic file upload (used for employee photo)
- [x] `GET /api/setup/salary-structures` — lookup for structure assignment
- [ ] `GET/PUT /api/employees/[id]/access` — menu access (user_access table) ← Priority 3 of Employee gap-closure plan
- [x] `/employees` list page — status (Active/Resigned) + branch filters, Activate/Deactivate row action
- [x] `/employees/new` create page — Personal (+Gender/Blood Group/Marital Status/Photo), Professional (+Employment Type/Reporting Manager/Probation), Salary & Statutory (structure/CTC/PAN/PF/ESIC), Bank Details
- [x] `/employees/[id]` detail/edit page — same 4 sections, inline edit toggle
- [x] `DataTable` updated with `onRowClick` prop
- [x] New shared components: `EmployeeSearch`, `FileUploadField`

**Milestone:** ✅ Build passes. Can view/manage employees and org structure, with full personal/professional/salary/bank data entry matching live-site parity for Priority 1 of the Employee gap-closure plan (see gap report comparison against `in.mypayrollmaster.online`). Cost Center/UAN/PT Applicable/Bank Account Type intentionally excluded — no backing columns in the schema, and the decision was made not to alter the database. CSV import still open (needs `DataUploaderController.php` column mapping). Resignation, Menu Allocation, Bulk Policies, Document Upload/Generation, IT Declarations remain per the gap-closure plan's Priorities 2-4.

### 2.5 Employee Join (Priority 3.1 of the Employee gap-closure plan)
- [x] `GET/POST /api/employees/join` — list pending (`emp_join.status=1`) candidates / create new staging record
- [x] `GET/PUT/DELETE /api/employees/join/[id]` — fetch full staging record (join + documents + education + experience + family), update personal fields, discard (hard-delete, relies on `ON DELETE CASCADE` for child rows)
- [x] `POST/DELETE /api/employees/join/[id]/{documents,education,experience,family}[/[rowId]]` — child sub-tab CRUD on staging tables (`emp_documents`, `education`, `work_experience`, `family`, all keyed by `emp_join_fkey`)
- [x] `POST /api/employees/join/[id]/onboard` — transactional conversion: duplicate check (PAN/Aadhaar/ESI/UAN/LWF/account no. against active `emp_details`, plus `emp_id`/`emp_company_id`/username uniqueness) → insert `emp_details` (incl. the legacy `pf`/`company_pf` field swap) + `emp_proff` + optional `emp_ctc_upload` + `user_credentials` (bcrypt-hashed password, `access_allowed='n'`) → mark `emp_join.status=0` + `emp_fkey` → copy staged child rows to permanent tables (`education→qualifcations`, `work_experience→history`, `family→emp_family`, `emp_documents→emp_passport_visa`)
- [x] `GET /api/setup/nationalities` — lookup (`countries_nationality` table) for the join form's Nationality/Country of Origin selects
- [x] `/employees/join` list page — "Employee Joining" (pending candidates, Edit/Continue Onboarding/Discard actions) + "All Employees" tab (reuses the existing `/employees` list page component directly)
- [x] `/employees/join/new` — staging record creation form (Personal/Statutory/Bank sections)
- [x] `/employees/join/[id]` — staging record edit page with Documents/Education/Work Experience/Family sub-tabs (repeatable add/remove rows)
- [x] `/employees/join/[id]/onboard` — onboarding conversion form (Employee ID/Employee Company ID/Login Username+Password, Professional Details, Salary) — matches legacy: no auto-generated username/password, no onboarding email sent
- [x] New shared components: `RepeatableRows` (generic add/remove row list, used 3x for Education/Experience/Family), `DocumentUploadField` (generic file upload, reuses `/api/upload`)
- [x] Nav: added "Employee Join" under the Employees section in `Sidebar.tsx`

Schema-verified against the real `mypayrol_mpm121` dev DB before implementation (not just the schema dump): `emp_join`, `emp_documents`, `education`, `work_experience`, `family`, `qualifcations`, `history`, `emp_family`, `emp_passport_visa`, `countries_nationality` all confirmed to match the dump exactly. Curl-verified end-to-end as admin: created a staging record with all 4 child row types, ran the full onboarding conversion (confirmed the `pf`/`company_pf` swap landed correctly, CTC/professional/login rows all created, staging row correctly marked `status=0` with `emp_fkey` set, all 4 child tables copied to their permanent counterparts), confirmed the duplicate-PAN check blocks onboarding with 409, confirmed discard hard-deletes a staging record via cascade. All test rows cleaned up afterward (including the `emp_ctc_transaction` cascade row, same gotcha as documented in the critical fix below).

Per an explicit scope decision earlier in the plan, Education/Work Experience/Family sub-tabs were included in this pass (full legacy parity) rather than deferred, and onboarding matches legacy exactly for login creation (HR types both username and initial password on the conversion form — no auto-generation, no email).

**emp_id auto-generation (2026-07-10):** ported legacy's `loadEmpProfDetails()` auto-numbering (byte-identical in `EmployeeController.php`/`EmployeeJoinController.php`) as `src/lib/empId.ts`'s `generateNextEmpId()` — next sequential numeric ID (MAX+1, seeded at 1000 for a company's first employee), used by both `POST /api/employees` and `POST /api/employees/join/[id]/onboard` when `emp_id` is left blank. Deliberately implemented as plain-numeric (no company-code prefix) based on real dev data (`emp_id` values like `1000227`), not the letter-prefix reading suggested by a literal read of the legacy source — verified against real data before implementing, per the project's standing schema-drift discipline. Confirmed `emp_company_id` (a separate `emp_proff` field, also used as part of the login username) is NOT auto-generated in legacy — always manually typed by HR — so it correctly remains a required manual field in both forms.

**Gap-closure fixes vs. live site comparison (2026-07-10):** an external gap report comparing this module against `in.mypayrollmaster.online` surfaced several real bugs, since fixed:
- Edit page (`/employees/join/[id]`) was silently missing most fields present on the New form (Gender, Nationality, Country of Origin, Guardian/Relation, Photo, the entire Statutory Details and Bank Details sections) — a real data-loss risk since saving from Edit would have wiped anything not shown. Rebuilt to full parity with the New form.
- Date of Birth wasn't pre-filling on Edit — root cause was the API returning a full ISO datetime string directly into a `type="date"` input; fixed by slicing to `YYYY-MM-DD` when populating form state.
- Nationality/Country of Origin dropdowns were showing the wrong list (demonyms like "Afghan" instead of country names like "AFGHANISTAN", per legacy) and several `countries_nationality.nationality` values are genuinely corrupted in the real DB (mojibake, e.g. `BelarusianÂ¿Â¿orÂ¿Â¿Belarusan` — confirmed via `HEX()` inspection, a real data quality issue, not a display artifact). Fixed by switching both dropdowns to `country_name` (matches legacy and is clean across all 189 rows — no DB fix needed since the corrupted column is no longer used for display).
- "EPS" was a free-text field; legacy has it as a Yes/No eligibility checkbox — fixed to a checkbox (`Y`/`N`) on both New and Edit forms.
- Marital Status was missing Divorced/Widowed (legacy has all four); added, and made the Edit page use the same select control as New (was inconsistently free-text).
- Added a PAN format placeholder (`ABCDE1234D`) matching legacy.
- List page (`/employees/join`): added an avatar/photo column, a name/mobile search box, and real pagination (previously loaded all records unpaginated) — reused the existing `DataTable` component and added `search`/`page`/`pageSize` params to `GET /api/employees/join`.
- Added bulk import: `GET /api/employees/join/template` (downloads an `.xlsx` template with the exact legacy column order/headers) and `POST /api/employees/join/upload` (parses an uploaded `.xlsx`, validates mandatory fields per row, dedupes against active `emp_details` by Aadhaar/PAN, resolves Nationality/Country of Origin by name against `countries_nationality`, converts Yes/No columns, and reports per-row errors without failing the whole batch).
- Deliberately **not** built: the legacy toolbar's "Import" button turned out (per legacy source research) to be an unrelated third-party "MyProfile" OTP-based external API integration, not a file import — out of scope, no equivalent credentials/API exist for this migration. The toolbar's row-selection "OnBoarding" button was also skipped since it opens the same edit/onboarding screen already reachable via this page's per-row "Continue Onboarding" action — redundant UX, not new capability.

Curl-verified all of the above end-to-end as admin: created and edited a staging record confirming Nationality/EPS/Marital Status round-trip correctly, downloaded the template and confirmed it round-trips through the `xlsx` library, uploaded a 2-row file (one valid, one deliberately incomplete) and confirmed 1 inserted / 1 reported error with the correct row number, confirmed a genuine Aadhaar/PAN collision against real seed data is caught by the dedupe check. All test rows cleaned up.

### 2.6 Employee Access (Priority 2.1 of the Employee gap-closure plan)
- [x] `GET /api/employees/access` — list active employees joined to `user_credentials` (web login) + `mob_user_credentials` (mobile access/punch type) via `user_id`
- [x] `PUT /api/employees/access/[id]` — single combined save (matches legacy's one-form-submit design): toggles `user_credentials.access_allowed`/`locked` (implicitly zeroes `incorrect_login_attempt` on unlock), creates a new `user_credentials` row on demand if the employee has no login yet (username + password required), upserts `mob_user_credentials` (creating it on first save if missing, exactly as legacy does) for mobile access (`locked` field, inverted) and `punchtype`; password reset hashes via bcrypt and sets `reset_login_flag='Y'`/`locked=0`
- [x] `POST /api/employees/access/[id]/reset-device` — clears `mob_user_credentials` securitycode/macid/imei and sets `token='Y'` to force re-registration (legacy's `resetmobiles()`)
- [x] `/employees/access` page — grid with web login/mobile access/punch-type status chips, "Manage" modal (access/lock/mobile/punch-type/password reset), "Reset Device" action
- [x] Deliberate deviation from legacy: mobile password is not stored in plaintext on `mob_user_credentials.password` (legacy does this) — out of scope for this pass since no mobile client exists in this migration; mobile login itself is unaffected since it's a separate app hitting the same DB

Schema-verified: confirmed `user_credentials` has no `mobileaccess`/`punchtype` columns in the real dev DB (only `mob_user_credentials.punchtype` is real) — the legacy research flagged these as needing verification and they turned out to be dead/nonexistent columns in this schema, consistent with the project's recurring schema-drift gotcha. Punch type codes confirmed from legacy: `W`=Machine/biometric, `M`=Mobile from anywhere, `O`=Mobile from office only, `S`=Web (gates the employee dashboard's web check-in/out buttons). Curl-verified end-to-end as admin against real employee data: toggled web access/lock, reset a password (confirmed `reset_login_flag` flips to `Y`), set mobile access + punch type (confirmed a new `mob_user_credentials` row is created on demand), ran reset-device, then fully restored the test employee's original state (including deleting the newly-created `mob_user_credentials` row, since none existed originally). Found and fixed one bug during verification: the `locked` field arrived as the string `"0"` from the request body, which is truthy in JS — `body.locked ? 1 : 0` always evaluated true; fixed to `Number(body.locked) === 1 ? 1 : 0`.

### 2.7 Allocate Assets (Priority 2.3 of the Employee gap-closure plan)
- [x] `GET/POST /api/assets`, `PUT/DELETE /api/assets/[id]` — asset catalog CRUD (`asset_management`), soft-delete via `active='0'`
- [x] `GET /api/setup/asset-types` — lookup (`asset_types` table)
- [x] `GET/POST /api/employees/assets` — list allocations (joined to `emp_details` for holder name) / allocate a catalog asset to an employee (denormalizes name/model/brand/serial/warranty onto the `asset_allocate` row, syncs `asset_management.status='Allocated'`, blocks with 409 if the asset is already allocated)
- [x] `PUT /api/employees/assets/[id]` — mark returned: sets `asset_allocate.status='Returned'` + `retreived_date`, syncs `asset_management.status='Returned'` back (two-table sync, matches legacy `release()`)
- [x] `/assets` page — catalog CRUD via the reusable `SetupCrudPage` component
- [x] `/employees/assets` page — allocation list + "Allocate Asset" modal (`EmployeeSearch` + available-assets select + condition + date) + "Mark Returned" row action
- [x] Nav: "Assets" (catalog, already existed as a dead link in `Sidebar.tsx`, now has a real page) + "Allocate Assets" added under Employees

Schema-verified against the real dev DB before implementation — corrected an initial misreading of the schema dump: `asset_management.emp_fkey` looked like it might mean "current holder" but legacy research (`AssetController.php`) confirmed it's vestigial/never populated by the create-asset form — `asset_management` is a genuine shared catalog, and ownership lives entirely in `asset_allocate` (a reallocation log keyed by `emp_fkey` + `asset` = the catalog's `asset_pkey` as a string). Also confirmed `asset_allocate.pemp_fkey` is dead/unused in all legacy code paths — not modeled as anything in the port. `asset_state` codes confirmed: `1`=Good, `2`=Damaged But Working, `3`=Not Working. Curl-verified end-to-end as admin: created a catalog asset, allocated it to an employee (confirmed the catalog's `status` flipped to `Allocated`), confirmed a double-allocation attempt correctly 409s, marked it returned (confirmed both tables synced back to `Returned`), edited and soft-deleted the catalog entry. All test rows cleaned up.

### 2.8 Allocate Policies in Bulk (Priority 3.2 of the Employee gap-closure plan)
- [x] `POST /api/employees/bulk-policies` — single endpoint, `{ type, policy_id, emp_fkeys[] }`. For SHIFT/LEAVE/HOLIDAY/NOTICEPER: inserts an `emp_config` audit row per employee (mirrors legacy `addEmpTo{Shift,Leave,Holiday,Notice}`) and updates the live `emp_proff` column those features actually read from (`day_time_seq`/`LEAVEPOLICY_GROUP_ID`/`HOLIDAY_GROUP_ID`/`notice_days`, the last resolved from `notice_period.notice_days` since `emp_proff.notice_days` stores the day count directly, not the master's PK). For SALARY: calls the real legacy stored function `sal_structure_distribution_fn(company_code, emp_fkey, structure_id, user_id)` once per employee (never rewritten, per project policy on stored procs) and only records the `emp_config` audit row if the function returns success; reports employees where it returned failure (typically means no active CTC assigned yet) without failing the whole batch.
- [x] `GET /api/setup/notice-periods`, `GET /api/setup/leavepolicy-groups` — new lookups (reused existing `/api/setup/shifts`, `/api/setup/holiday-groups`, `/api/setup/salary-structures` for the other three sections)
- [x] `/employees/bulk-policies` page — 5 sections (Shift/Leave/Holiday/Notice Period/Salary Structure), each: policy select + filterable employee checkbox list + bulk assign button, reporting assigned/failed counts
- [x] Nav: "Allocate Policies in Bulk" added under Employees

Scope note: legacy's `EmployeeConfigController.php` actually has more `emp_config` types than originally covered (GRADE, DIVISION, SECTION, HIERARCHY/reporting-manager, LAPPR/leave-approval-hierarchy) — these were added in the follow-up gap-closure pass below (2.10). `MSHIFT`/additional-shift with primary-shift swap logic remains out of scope. Also confirmed `sal_structure_distribution_fn`'s actual signature takes one `emp_fkey` at a time (`RETURNS int`), not a comma-list as initially reported by research — verified directly via `SHOW CREATE FUNCTION` against the real DB before trusting it, consistent with the project's standing schema-drift discipline. Curl-verified end-to-end as admin: bulk-assigned Shift/Holiday/Leave/Notice Period to a real employee and confirmed both the `emp_config` audit row and the corresponding `emp_proff` column updated; called the salary function with an invalid `structure_id` (correctly returned 0/failed) and then a real one (correctly populated a full `emp_salary_structure` breakdown and the `emp_config` audit row). All test assignments cleaned up afterward.

### 2.9 Menu Allocation (Priority 3.3 of the Employee gap-closure plan)
- [x] `GET /api/employees/menu-allocation/[id]` — builds the full `emp_menu` hierarchy (`parent_id`-based tree, `active='Y'` only) plus which `menu_id`s are currently active for this employee via `user_access` (confirmed `user_access.user_fkey` references `emp_details.emp_pkey`, not `user_credentials.user_pkey` — verified against real data since a naive read of the schema doesn't disambiguate the two, given they're often numerically identical)
- [x] `PUT /api/employees/menu-allocation/[id]` — diffs the submitted `menu_ids[]` against existing `user_access` rows: reactivates/updates matches, inserts new rows (`organization_id` hardcoded to `'1'`, the only value observed in real data — multi-tenancy is handled at the DB level in this architecture, so this column looks vestigial), and soft-deactivates (`active='N', status=0`) anything removed
- [x] `/employees/menu-allocation` page — `EmployeeSearch` to pick an employee, then a checkbox tree (`<MenuTree>`, recursive component) built from the real hierarchy, Save button
- [x] New shared component: `MenuTree` (recursive checkbox tree)
- [x] Nav: "Menu Allocation" added under Employees

Curl-verified end-to-end as admin: fetched the real menu tree and an employee's real pre-existing assignment (48 active menu items), saved a small test set and confirmed the diff correctly deactivated everything not in the new set while activating the rest, then restored the employee's original 48-item assignment exactly. (Mid-verification the dev server had stopped responding for an unrelated reason and was restarted — session/login re-established before continuing, no impact on the changes made.)

### Maintenance pass (2026-07-10): fixes from an Employee-menu design comparison report
An external design-comparison report (browser review of legacy vs. Next.js) was mostly stale — it predated the Bulk Policies/Menu Allocation work above — but surfaced two real, confirmed issues:
- **Allocate Assets "Asset" column rendered empty** on rows created before this feature existed. Root cause: `asset_allocate.asset_name`/`model`/`brand` are empty strings (not NULL) on old rows, so a first-pass `COALESCE(a.asset_name, m.name)` fallback silently did nothing (`COALESCE` only substitutes on NULL, and an empty string isn't NULL) — caught this via curl-verification after the initial fix, then corrected to `COALESCE(NULLIF(a.asset_name, ''), m.name)` (`rizo/src/app/api/employees/assets/route.ts` GET), joined to `asset_management` on `a.asset = m.asset_pkey`. Confirmed via curl that every row now shows a real name/model/brand, old and new alike. No data migration needed.
- **User Access and Allocate Assets lacked search/pagination**, unlike `/employees` and `/employees/join`. Brought both up to the same standard: added `search`/`page`/`pageSize` params returning `{ data, total }` to `GET /api/employees/access` and `GET /api/employees/assets`, and replaced the hand-rolled `<table>` in both pages with the shared `<DataTable>` component plus a search box, matching the existing `/employees`/`/employees/join` pattern exactly (including the inlined `LIMIT/OFFSET` mysql2 workaround).

Explicitly not done, per your decisions: Resignation workflow stays out of scope (unchanged from the original plan decision), and no change to the Add Employee / Employee Join relationship (both are intentional, complementary paths — no code change needed). Curl-verified both endpoints' search and pagination against real data (160 real employees on Access, real asset allocations). `npm run build`/`tsc --noEmit` clean.

### 2.10 Bulk Policy Allocation gap-closure (2026-07-10): Division, Section, Grade, Employee Hierarchy, Leave Hierarchy
A screenshot comparison of the live legacy "Bulk Policy Allocation" page against the Next.js "Allocate Policies in Bulk" page showed 5 of legacy's 10 tabs were missing entirely (Division, Section, Grade, Employee Hierarchy, Leave Hierarchy) — a real gap I'd missed when originally scoping 2.8. Researched the real legacy controller before building:
- **Division / Section / Grade**: identical mechanism to each other and to the existing Shift/Leave/Holiday sections — pick a master record (`division`/`section`/`grade` tables) → bulk-assign employees → `emp_config` audit row (`type`='DIVISION'/'SECTION'/'GRADE') + denormalized `emp_proff` column (`emp_vertical`/`emp_sep_priv`/`emp_grade`). Added as 3 more sections to the existing checkbox-list UI and `TYPE_CONFIG` map (`rizo/src/app/api/employees/bulk-policies/route.ts`, `rizo/src/app/(dashboard)/employees/bulk-policies/page.tsx`), plus lookup routes `GET /api/setup/divisions`, `GET /api/setup/sections` (grades lookup already existed).
- **Employee Hierarchy** (reporting-manager tree) and **Leave Hierarchy** (leave-approver pairing): a genuinely different UI — a 3-panel mover (pick a manager/approver → non-allocated pool → currently-assigned list, with drag-free up/down reordering for Hierarchy only). New component `rizo/src/components/employees/HierarchyMover.tsx` and API `rizo/src/app/api/employees/hierarchy/route.ts` (GET available/assigned lists, POST add/remove/reorder against `emp_config` type `HIERARCHY`/`LAPPR`). Leave Hierarchy's "available" list excludes direct 1-level cycles (can't make someone your approver if you're already theirs).
- **Real bug found and fixed during verification**: an existing DB trigger (`emp_config_au`, fires `AFTER UPDATE` on any `emp_config` row) unconditionally NULLs the corresponding `emp_proff` denormalized column for that type — this is legacy's own mechanism for clearing the value on removal, but it fires on *any* update to the row, not just soft-deletes. My original `reorder` action updated `emp_config.hirc_leval` on every drag, which silently wiped `emp_proff.attr1` (the manager assignment) for every reordered employee, including pre-existing real assignments. Caught this by watching a real employee's `attr1` unexpectedly go from `'120'` to `NULL` mid-verification. Fixed by re-applying `emp_proff.attr1 = parentId` immediately after each `hirc_leval` update in the reorder handler, with a comment documenting the trigger's non-obvious behavior for future readers.
- Curl-verified end-to-end as admin against real data: DIVISION/SECTION/GRADE bulk-assign (via temporary test `division`/`section` rows, since those tables had no seed data — confirmed empty tables, not a bug), Employee Hierarchy add/reorder/remove (confirmed a real pre-existing assignment for employee 104 under manager 120 was preserved through reorder after the fix, having caught and reverted the corruption from the pre-fix version), Leave Hierarchy add/remove (confirmed cycle-prevention exclusion). All test data and the temporary `division`/`section` rows removed afterward; the real employee's `emp_proff`/`emp_config` state was restored exactly. `npm run build`/`tsc --noEmit` clean.

## Critical fix (2026-07-07): Setup + Employee routes were using wrong table/column names

Discovered while starting Employee module gap-closure work: `branches`, `departments`→`department`, `designations`, `grades`→`grade`, and `/api/employees` were all built against invented/simplified table and column names that don't match the real `mypayrol_mpm121` schema. Every one of these routes was silently 500ing (or, for `/api/setup/branches`, returning nothing) against real data. Root cause: never curl-verified against a populated DB after the original build (unlike the later Phase 2 cleanup features, which were).

Fixed:
- `departments` → `department` (real table name), `grades` → `grade`; PK columns corrected from invented `*_pkey` names to the real `id` (branches/departments/designations use `id`; grades' `grade_pkey` was actually already correct)
- `branches`: added required NOT NULL columns (`city`, `state`, `pincode`, `latitude`/`longitude` defaulted to 0) that the original INSERT omitted
- `/api/employees`: `emp_proff` stores branch/department/designation/grade as **varchar codes** (`emp_branch`, `emp_dept`, `designation`, `emp_grade`), not integer FKs — rewrote all joins/writes to match (`emp_branch = branches.branch_code`, etc.). Also fixed `e.mobile`/`e.personal_email` → real columns `mobile_no`/`email`
- Separately found and fixed a mysql2 `.execute()` quirk (`LIMIT ? OFFSET ?` placeholders throw `ER_WRONG_ARGUMENTS`) by inlining the already-sanitized numeric values into the SQL string

Verified end-to-end via curl (as both admin and employee sessions) against real `GRTL` data: list/search/branch-filter, create, detail view, update, and the 4 setup CRUD screens (create/update/soft-delete) all confirmed working with real returned data (159 real employees, real branch/dept/designation names resolving correctly).

**Lesson carried forward:** always curl-verify new routes against the populated dev DB, not just `npm run build` — TypeScript passing says nothing about whether table/column names are real.

### 2.11 Priority 4 gap-closure (2026-07-11): Document Upload + Employee Income Tax Declarations

Researched the real legacy controllers before building (background Explore agent + live schema verification), which corrected a wrong assumption from the original plan:

- **Document Upload** is NOT a standalone page in legacy — it's a "Documents" tab embedded directly on the employee profile, and it writes to **`emp_passport_visa`** (keyed by `emp_fkey` directly), not `emp_documents` (which is the Employee Join *staging* table, keyed by `emp_join_fkey`). Onboarding conversion already copies staged `emp_documents` rows into `emp_passport_visa`, so this gap was really "let HR keep managing that same table after onboarding." Built as a new section on `rizo/src/app/(dashboard)/employees/[id]/page.tsx` (reusing the existing `RepeatableRows`/`DocumentUploadField` components from Employee Join), backed by `rizo/src/app/api/employees/[id]/documents/route.ts` (GET/POST) and `[docId]/route.ts` (DELETE, soft `status=0`). `reccuring`/`remind` columns confirmed vestigial in legacy (no cron/reminder logic anywhere) — intentionally not exposed in the form.
- **Employee Income Tax Declarations** — new top-level page `rizo/src/app/(dashboard)/employees/tax-declarations/page.tsx` (`EmployeeSearch` picker, matching legacy's standalone-menu-item shape). Backed by `rizo/src/app/api/employees/[id]/tax-declarations/route.ts` (GET resolves the employee's branch's currently-open `fin_year` — picks the most-recently-started `OPEN`+`is_current_finyear='Y'` row, since real data can have more than one flagged current per branch — then returns `tax_heads`/`tax_heads_details` grouped by Income/Deductions merged with any existing `emp_tax_transactions`; POST upserts a declared value), `lock/route.ts` (admin-only, single-row or bulk lock/unlock, matching legacy's per-`(emp_fkey, tax_heads_fkey)` boolean toggle), and `upload/route.ts` (proof-document upload: MIME allow-list + 2MB cap mirroring legacy's `finfo`-based validation, though ours checks the browser-reported MIME type rather than sniffing real file bytes, since adding a magic-byte-sniffing dependency wasn't warranted for this pass; blocks upload once locked; appends to the comma-joined `file_name`/`file_type` columns like legacy does for multi-file-per-head support).
- Computed-tax preview (`tax_salary_distribution_fn`) intentionally **not** wired up in this pass — out of scope per decision, since it's a payroll-side computation rather than something the declaration/lock/upload workflow itself needs.
- **Bug caught during curl-verification**: `UPDATE emp_tax_transactions SET last_value = ...` threw a MySQL syntax error — `last_value` is a reserved word in MySQL 8 (the `LAST_VALUE()` window function). Fixed by backtick-quoting the column name.
- Curl-verified end-to-end against real data (employee 104, branch GRTL08): document add/list/soft-delete; tax declaration GET correctly resolved FY2026 as the open year; declare → lock → blocked-edit-while-locked (403) → unlock → edit-succeeds; proof upload accepted a real PDF and rejected a `.exe`; all test rows/files cleaned up afterward. `npm run build`/`tsc --noEmit` clean.
- **Generate Employee Documents deferred**, per decision — legacy has a real template/merge-field/Dompdf-PDF pipeline (`DocumentManagerController.php`: `doc_template`/`documents` tables, ~60 merge placeholders) worth porting (we'd use the already-installed `jsPDF` instead of Dompdf), but it's larger and less-defined than the other two and needs its own scoping pass.

### 2.12 Employee menu completion (2026-07-13): Import Employee, Promotion Approval, Generate Employee Documents, Remove Employee

Closed the remaining 4 legacy Employee-menu items (all 12 legacy items now have a Next.js equivalent). Researched each via background Explore agents before building, then verified every schema assumption against the real dev DB rather than trusting the research or the static schema dump.

**Import Employee** (`/employees/import`, `rizo/src/app/api/employees/import/route.ts` + `template/route.ts`) — bulk XLSX upload that writes **directly** to `emp_details`/`emp_proff`/`user_credentials` (not routed through the `emp_join` staging table — that's Employee Join's job). Legacy's real column template comes from a vendor helper class (`EmployeeCSVData.php`) not present in this repo checkout, so the template is our own design (kept legacy's 6 confirmed mandatory columns, added the common optional fields our own Add Employee form supports) rather than a byte-identical port. One branch applies to the whole uploaded file, matching legacy's `uploadandsaveempdetails($emp_branch)`. Uses the stronger PAN/Aadhaar duplicate check already used elsewhere in this app (legacy's own check is weaker: name+DOB only). `user_credentials.user_id`/`password` inserted as `NULL` (not `''`) to avoid colliding on `user_id`'s `UNIQUE` constraint across multiple imported rows. Curl-verified: real branch/designation/department resolution, duplicate-check rejection on re-upload, all three tables populated correctly; test rows cleaned up.

**Promotion Approval** (`/employees/promotions`, `rizo/src/app/api/promotions/route.ts` + `[id]/route.ts`) — ports `PromoController`. A single central approval step (not tiered manager→admin, matching legacy): request → approve (cascades designation/department/branch/employment-type as direct `emp_proff` updates, shift/leave as `emp_config`-audited assignments matching the Bulk Policies convention, a salary-structure change via the real `sal_structure_distribution_fn`, a CTC row, and a reporting-manager change matching the Employee Hierarchy mover's convention) or reject. Confirmed "Approvals (Probation/Promotion)" in legacy is really just Promotion — Probation approval doesn't exist anywhere in legacy (no controller, no table, nothing beyond a plain probation-days field used in payroll date math). **Bug caught during verification**: the salary-structure cascade was ordered before the CTC insert, but `sal_structure_distribution_fn` requires an active CTC to already exist (the same prerequisite documented for Bulk Policies' SALARY type) — fixed by inserting the CTC row first. Curl-verified end-to-end on a real employee (cascaded designation/department/shift/leave/salary-structure/CTC/hierarchy changes, reject flow, already-processed 409 guard); all changes restored to the employee's original state afterward.

**Generate Employee Documents** (`/employees/generate-documents`, `rizo/src/lib/documentMerge.ts`, `rizo/src/app/api/document-templates/*`, `rizo/src/app/api/employees/[id]/generate-document/route.ts`, `rizo/src/app/api/documents/*`) — HTML template/merge-field engine only, per decision (skipped legacy's separate image-composite certificate feature, which calls an external legacy microservice, and its birthday-email feature, which is a different feature area entirely). Templates are legacy's real, already-seeded `doc_template` catalog (74 real rows: offer letters, termination letters, experience certificates, company policies). Merge-field resolution (`buildMergeTokens`) pulls real employee/company/branch data using legacy's exact token spelling where confirmed (including its `empolyee_name` typo) and our own consistent naming for the rest (documented in-app via a placeholder reference panel). PDF output uses the browser's native print dialog (`window.print()` on a generated preview window) rather than adding an HTML-to-PDF rendering dependency. Curl-verified: real production template ("Employee Promotion letter") merged correctly against a real employee's name/designation/department/CTC; save persists to the real `documents` table (confirmed alongside real pre-existing production documents for that employee); template create/edit/delete all confirmed; test rows cleaned up.

**Remove Employee** (`/employees/resignations`, `rizo/src/app/api/resignations/**`) — the full 4-stage legacy workflow: (1) request (`resignation_requests` + a `termination` row created together, matching legacy's `Saverequests()`), (2) HR handover checklist (`resignation_accept`), (3) admin approval, which calls the real `final_settle_pay_prc` stored procedure and surfaces its result, (4) final removal (`emp_details.status=2`, clearing *other* employees' hierarchy references to the now-terminated manager, deactivating their `HIERARCHY` `emp_config` rows, marking outstanding payroll/settlement/leave-encashment rows settled) — plus a withdraw/cancel path. **Real blocker found and flagged before building**: `final_settle_pay_prc`'s actual signature (verified via `SHOW CREATE PROCEDURE` against the dev DB, not the schema dump) is `(branch_code, month_year, emp_pkey, presant_days, encash_days, user_id, OUT error_message)` and depends on `attendance_register` data for that employee/branch/month — infrastructure this port hasn't built yet (Attendance phase not started). Decision: call it anyway and surface `perror_message` to HR (e.g. "Attendance not verified") rather than silently no-op, so the workflow is ready for whenever Attendance/Payroll exist. **Two bugs caught during verification**: (1) a raw `new Date().toISOString()` string passed to a MySQL `datetime` column threw `ER_TRUNCATED_WRONG_VALUE` (needed `T`/`Z` stripped); (2) the approve route tried to update a nonexistent `termination.approved_date` column (confused with `resignation_accept`'s own `approved_date` column of the same name) — fixed by removing it. Curl-verified the full lifecycle end-to-end on a throwaway test employee (created and destroyed for this test, since the final step is a real irreversible termination): request → checklist → approve (confirmed real "Attendance not verified" response) → finalize (confirmed `emp_details.status=2` and all cascaded updates) — plus a separate withdraw/cancel test. All test data removed afterward.

All four: `npm run build`/`tsc --noEmit` clean. This closes out the Employee module gap-closure plan — all 12 legacy Employee-menu items now have a Next.js equivalent (Import Employee and the Promotion half of Approvals were previously paywalled/unbuilt in legacy itself; both are now real, working features here).

### 2.13 Remove Employee refinements (2026-07-14): flat list view + full legacy Reason list

A follow-up design-comparison review of Remove Employee against the live legacy site (`in.mypayrollmaster.online`) found the list layout diverged too far from legacy's single searchable table, and confirmed the Reason dropdown was missing 14 of legacy's 19 real options (no path for termination/dismissal/absconding/death/retirement in the UI).

- **List view** (`rizo/src/app/(dashboard)/employees/resignations/page.tsx`): replaced the 5-tab card layout with a single `DataTable` (same component/pattern as `/employees`) — columns match legacy (Full Name, Branch, Reason, Resignation Submitted, Last Applied Date, Last Approved, Remarks) plus a color-coded Status column, with status now a filter dropdown instead of separate tabs, and a name/ID/reason search box. `GET /api/resignations` (`rizo/src/app/api/resignations/route.ts`) extended with `search` (matches employee name/ID/reason) and an optional (rather than default-required) `status` param, plus a `branches` join for the Branch column.
- **Reason list**: replaced the 5-option voluntary-exit-only dropdown with legacy's real 19-option list, confirmed against `legacy/View/EmployeeResignation/form.ctp` (not the earlier gap report's text, which had garbled the 4 "Cessation (Short Service)" sub-options into unreadable fragments — verified against the actual `.ctp` source instead of trusting it).
- **Bug found and fixed**: `resignation_requests.Reason` was `varchar(20)` in the real dev DB — under `STRICT_TRANS_TABLES` (confirmed via `@@sql_mode`), inserting most of the real legacy reason strings (e.g. "Death Away From Service", any of the 4 "Cessation (Short Service)..." options, all >20 chars) would hard-fail with `ER_DATA_TOO_LONG`. Legacy's own `termination.Reason` column (which receives the identical value in the same request) is `varchar(100)`, so `resignation_requests.Reason` was too narrow for its own UI's option list — a genuine schema-drift bug, not a new-field decision. Fixed via `ALTER TABLE resignation_requests MODIFY Reason varchar(100) NOT NULL` to match.
- Curl-verified end-to-end as admin (temporary password override on the real `GRTLADMIN01` control-DB row, restored immediately after obtaining a session): flat-list fetch against 3 real resignation records (branch names resolved, no crash on legacy rows with no matching `termination` row), search and status-filter params both behave correctly, and a real submission using the longest new Reason option ("Cessation (Short Service) - The Employee Ill", 44 chars) round-tripped correctly post-widen. Test resignation/termination rows deleted afterward.
- `npm run build`/`tsc --noEmit` clean.
- **Follow-up bug**: the settlement button ("Approve & Settle") only rendered at the `HR Reviewed` stage, but nothing in the backend actually requires the Checklist step first, and all real data in this tenant sits at `Applied` — so the button was invisible for every real record, reported by the user as "I don't see a button." Fixed: renamed to **"Process Full & Final"** (matching legacy's label) and made it visible at both `Applied` and `HR Reviewed`, matching legacy's un-gated toolbar-style access rather than our own invented stage requirement. Curl-verified calling it directly from `Applied` (skipping Checklist) succeeds and returns the expected "Attendance not verified" message; test rows cleaned up.

### 2.14 Remove Employee deep build (2026-07-14): real Full & Final settlement engine

Prior passes only called `final_settle_pay_prc` and showed its bare return message — HR never saw what the procedure actually computed. Three background research agents fully mapped legacy's real "Process Full & Final" flow (controllers, `.ctp` view files, live-DB procedures/functions/triggers/schemas), scoped down to the GRTL/non-KWMT path (the `KWMT`/`DEMO`/`GLET`-only editable per-month tables and "metro" formal-memo printables are out of scope for this single-tenant deployment). Full plan recorded in the session's plan file before building.

- **`GET /api/resignations/[id]/eligibility`** (new) — ports legacy `setup()`'s hard-blocking gates: approved last-working-date not in the future, not already processed (`emp_settle_slip` ⨝ `emp_details.status=2`), shift + salary structure both assigned. The two attendance-window checks are ported with the same SQL shape legacy uses, but since `emp_detail_timeattandance` is empty until the Attendance phase exists, they resolve to "nothing to check yet" rather than blocking — the honest behavior of running legacy's own logic against currently-empty tables, confirmed deliberately rather than approximated. Per an explicit decision, this is a **hard block** (not just a warning) on all checks including attendance — meaning full settlement processing stays gated until Attendance is built and real attendance data exists for the resignation window.
- **Notice period auto-calc** on the New Resignation form — fetches the selected employee's `emp_proff.notice_days` and auto-suggests `Last Working Day = today + (notice_days − 1) days` (matches legacy's `asper_notice()` JS formula), always editable/overridable. **Bug found**: `POST /api/resignations` was hardcoding `termination.notice_period` to a literal `0` regardless of the employee's actual notice days — fixed to read `emp_proff.notice_days` at submission time.
- **`PUT /api/resignations/[id]/approve`** extended to close the central gap — legacy's real flow: (1) resolves each eligible leave head (`leavepolicy.is_leave_encash='Y'`) via `leave_balance_inthe_year_fn`, replaces any stale `leave_encashment_master` row, and calls `leave_encash_prc` to post the encashment (leave_encash_prc's 5th param is declared `IN` not `OUT` in the live DB — a legacy bug, not ported; we just call it and let a thrown SQL error surface via rollback); (2) calls `final_settle_pay_prc` as before; (3) **reads back** the resulting `emp_settle_slip` rows and returns a real Additions/Deductions/Net Salary breakdown instead of a bare message; (4) computes resignation-period day-count stats (`weekoff_days_count_fn` + a `holidays` count) and a display-only Notice Pay shortfall figure (`(annual_ctc/12)/notice_days` per-day rate × shortfall days), matching legacy's GRTL formula exactly — not persisted as its own settlement line, per an explicit decision; (5) surfaces read-only Loans (`emp_loan`/`emp_loan_info`) and damaged-Assets (`asset_allocate`) reference panels, with no auto-netting (matches legacy precedent — HR manually accounts for these).
- **Frontend rebuild** (`rizo/src/app/(dashboard)/employees/resignations/page.tsx`): the "Process Full & Final" button now calls the eligibility endpoint first, showing blockers in a modal if any and only opening the settlement modal if clear; the settlement-result modal now renders the full breakdown (day-count stat tiles, Additions/Deductions columns, Net Salary, Notice Pay, Loans/Assets reference lists) instead of a plain message.
- **`finalize/route.ts`** fix: its `emp_settle_slip` lock (`approved='Y'`) was missing legacy's `AND status = 'Y'` filter, meaning it would have flipped `approved` on cleared/reset rows too, not just active ones — fixed to match legacy's `removeemps()` exactly.
- **Real bug caught during verification**: mysql2 returns `DATE` columns as JS `Date` objects (no `dateStrings` config on the pool) — `String(dateObj).slice(0,10)` silently produces a garbled locale string, not an ISO date, corrupting every date comparison in both new routes. Caught because the eligibility gate blocked with a nonsensically-shifted date on a manual test. Fixed with a `toISODate()` helper (`instanceof Date ? .toISOString() : String(...)`) in both `eligibility/route.ts` and `approve/route.ts`.
- **Curl-verified end-to-end** on a disposable cloned test employee (emp_pkey 253, cloned from a real active employee's `emp_proff` config then fully deleted afterward, including the FK-cascaded `emp_ctc_transaction`/`emp_salary_structure` rows the CTC-insert trigger creates): eligibility correctly blocks on a future approved-date and correctly passes once backdated; notice period correctly propagates from `emp_proff.notice_days` into the new resignation; `leave_encash_prc` executes cleanly and posts a real `leave_encashment_master` row when the employee's leave policy group has encashable leave configured; `final_settle_pay_prc` continues to report "Attendance not verified" as expected; the settlement breakdown, Notice Pay, and day-count math all computed correctly; Finalize correctly flips `emp_details.status=2` and `Resignation_status='Completed'`. All test rows (across 7 tables) deleted afterward.
- `npm run build`/`tsc --noEmit` clean.
- **Explicitly out of scope for this pass** (per decisions made before building): employee self-service resignation submission (legacy's `ResignationRequestController` letter/agree flow) stays admin-only; Notice Pay stays display-only rather than persisted as an audit-trail line item.

### 2.15 Remove Employee refinement (2026-07-14): two-step preview + missing Encashable Leave Balance

A real production screenshot of legacy's live Remove Employee screen (`company_code = 'GRAT'`, a genuine non-KWMT/DEMO tenant, not a hypothetical) surfaced two concrete gaps against §2.14's build:

1. **Encashable Leave Balance was computed but never shown.** `computeLeaveEncashment` already calculated this internally but the API response and modal only surfaced 3 of legacy's 4 "Notice Period Adjustments" stat tiles.
2. **Legacy is a two-step flow, ours was one-click.** In legacy, clicking "Process Full & Final" in the list loads a preview screen (Resignation Details + Notice Period Adjustments stats + Loans/Assets) **without** calling `final_settle_pay_prc` yet — HR reviews the numbers, then a separate button inside that screen actually commits. Our single button did eligibility + leave-encashment + `final_settle_pay_prc` + breakdown all in one call.

Fixed both:
- **`rizo/src/lib/settlement.ts`** (new, shared) — factored the settlement logic into reusable pieces: `getTerminationContext` (the joined resignation/termination/proff/CTC query), `computeDayCountStats` (`weekoff_days_count_fn` + holidays, parameterized by `presantDays` so it works for both a 0-value preview and an admin-supplied commit), `computeNoticePay`, `previewEncashableLeaveBalance` (read-only sum, no writes — for the preview step), `commitLeaveEncashment` (the real write-and-call-procedure logic — for the commit step, now also returns the total days committed), and `getLoansAndAssets`.
- **`GET /api/resignations/[id]/preview`** (new) — the read-only first step: Resignation Details (submitted date, notice period, last working date, approved last working date), Notice Period Adjustment stats including Encashable Leave Balance, and Loans/Assets reference panels — computed with zero writes, mirroring legacy's `setup()` screen exactly.
- **`PUT /api/resignations/[id]/approve`** — refactored onto the shared lib (no behavior change to the commit itself), now also returns `encashableLeaveBalance` in the final result.
- **Frontend**: clicking "Process Full & Final" now runs eligibility → (if clear) fetches the preview and opens a modal showing Resignation Details, Notice Period Adjustments (now all 4 tiles), and Loans/Assets **before** the Settlement Month/Present Days/Leave Encashment Days inputs and the actual commit button — matching legacy's two-step structure. The post-commit settlement-result modal also gained the 4th (Encashable Leave Balance) tile.
- **Curl-verified end-to-end** on a second disposable cloned test employee (emp_pkey 254, deliberately given a leave-policy group with real encashable leave configured this time, to exercise the nonzero case) — confirmed the preview endpoint returns `encashableLeaveBalance: 12` with zero DB writes, and the subsequent commit step returns the identical `12` after actually persisting the `leave_encashment_master` row — i.e., preview and commit agree, which is the whole point of splitting them. Finalize confirmed as before. All test rows (8 tables) deleted afterward.
- `npm run build`/`tsc --noEmit` clean.

### 2.16 Remove Employee: Edit + View Slip (2026-07-14)

A dedicated deep-dive comparison report (Remove Employee vs. legacy, field-by-field across every form/button/process step) found two real remaining gaps once its stale headline claim was corrected (it had checked the All Employees Deactivate/Activate toggle rather than noticing `/employees/resignations`, which by this point already covered most of legacy's workflow): **Edit** an active resignation record, and **View Slip** (a printable Full & Final settlement document). Legacy's row-state rule — Edit/Delete lock once the employee is actually terminated — was confirmed to hinge on `emp_details.status = 2`, the same signal our own eligibility/settlement code already uses, not on any of the intermediate resignation-workflow stages.

- **`PUT /api/resignations/[id]`** (new) — updates `resignation_requests` (Reason/Reason_Desc/Comments_to_manager/Last_workingday/contact_no/authorised_to) and the matching `termination` row (Reason/Reason_desc/last_working_date/last_approved_working_date/act_last_working_day, plus re-deriving `notice_period` from `emp_proff.notice_days` in case it changed) together. Blocked with 409 once `Resignation_status = 'Completed'` — matches legacy's real gate exactly (tied to termination status, not to HR-Reviewed/Approved, both of which stay editable). The employee itself is not editable in edit mode (locked, matching legacy's own pre-filled/read-only "Select Employee" in its edit modal).
- **Frontend**: the New Resignation modal now doubles as an Edit modal (`editingId` state) — employee shown read-only when editing, submit button reads "Save Changes" instead of "Submit" (deliberately *not* carrying forward legacy's own flagged UX quirk of the button always saying "Submit Termination" even in edit mode), a new pencil-icon Edit action appears on any non-`Completed` row.
- **`GET /api/resignations/[id]/slip`** + **`/employees/resignations/[id]/slip`** (new) — a standalone printable Full & Final Settlement Statement, deliberately built as its own page outside the `(dashboard)` route group (no Sidebar/Header) rather than routed through the existing Generate-Employee-Documents template engine, per an explicit scoping decision — opens in a new tab via a `target="_blank"` link (mirroring legacy's own separate-window behavior), with a `window.print()` button. Reads the already-persisted `emp_settle_slip` rows from the last Process Full & Final run (read-only, no recompute) — available once a resignation reaches `Approved` or `Completed`, matching legacy's "View Slip enabled once already processed" rule.
- Both new endpoints/pages reuse the `rizo/src/lib/settlement.ts` helpers introduced in §2.15 (`getTerminationContext`, `computeDayCountStats`, `computeNoticePay`) rather than duplicating the settlement math a third time.
- **Curl-verified end-to-end** on a fresh disposable test employee: Edit updates both tables correctly; Edit correctly 409s once the record is `Completed`; View Slip returns a correct read-only breakdown once `Approved`, remains accessible after `Completed`; slip page itself renders (HTTP 200). All test rows across 8 tables deleted afterward.
- **Incidental fix**: a stale `.next` dev cache produced a spurious `LayoutRoutes` typed-route TypeScript error after adding the new print route group — resolved by clearing `.next` and restarting the dev server (not a real code issue).
- `npm run build`/`tsc --noEmit` clean.

### 2.17 Remove Employee: fix crash on orphaned real records (2026-07-14)

User-reported crash: clicking "Process Full & Final" on a real resignation record threw `Cannot read properties of undefined (reading 'map')` in `resignations/page.tsx`, crashing the whole page. Root cause: **all 3 real `resignation_requests` rows in this tenant** (Anas, Asna Mol T A, Gawtham — pre-existing data, created before this app's `POST /api/resignations`, which always creates a companion `termination` row) **have no matching `termination` row at all**. The eligibility endpoint returned a bare 404 `{error}` shape for these, which doesn't match the `{ok, blockers}` shape the frontend expects — `setBlockers(undefined)` then crashed on `.map()`.

- **`GET /api/resignations/[id]/eligibility`**: now returns `{ok: false, blockers: [...]}` instead of a 404 when no `termination` row exists, with an actionable message ("Use Edit to re-save it and create one").
- **`PUT /api/resignations/[id]`** (Edit): now upserts — if no `termination` row exists yet, it creates one (using the resignation's original `applied_date` as `submitted_date`, a historically-accurate backfill, not "today") instead of silently no-op'ing an `UPDATE` against zero rows. This makes the eligibility endpoint's advice actually true.
- **Frontend crash-proofing** (defense in depth, in case any endpoint ever returns an unexpected shape again): `checkEligibility`'s mutation now checks `res.ok` explicitly and coerces `blockers` to always be a real array before calling `setBlockers`; the modal's render guard changed from `!== null` to `!= null` (the actual bug — `undefined !== null` is `true` in JS, so a `null`-only check doesn't catch `undefined`).
- **Repaired one real record live**: re-submitted Gawtham's (Resignation_pkey 6) unchanged values through the fixed Edit endpoint, which created the missing `termination` row (confirmed eligibility now passes cleanly). The other 2 orphaned real records (Anas, Asna Mol T A) were deliberately left as-is — the fix is now self-serviceable via Edit in the UI, not something to unilaterally apply to more real employee records than needed for verification.
- `npm run build`/`tsc --noEmit` clean.

---

## Phase 3: Attendance
**Status: Register, Overtime, Shift Planner, Regularisation, Edit Punches, Check-in Reports, and Comp-off all complete (2026-07-15). Only `/setup/devices` remains unbuilt — no legacy device-config screen was ever found to port.**

- [x] `GET /api/attendance/period?month=YYYY-MM` — calls `att_start_end_fn`
- [x] `GET /api/attendance/register?month=YYYY-MM&branch=X&status=unverified|verified` — attendance grid data (FIELD1-32 + per-employee IN/OUT/duration + policy-leave color hints)
- [x] `POST /api/attendance/register/process` — calls `insert_update_att_reg`
- [x] `PUT /api/attendance/register/[registerId]/day/[dayIndex]` — single-cell edit, parameterized (legacy's `chnagestatus()` had a real SQL-injection surface via raw string concatenation — not ported)
- [x] `GET /api/attendance/register/[registerId]/leave-options` — per-employee leave codes + live balances for the edit modal
- [x] `POST /api/attendance/register/verify` — lock a month (blocks per-row on any miss-punched/blank date)
- [x] `POST /api/attendance/register/unverify` — unlock (blocks per-employee if payroll or OT already locked for that month)
- [x] `GET /api/attendance/overtime?month=YYYY-MM&branch=X&tab=not-approved|approved`
- [x] `POST /api/attendance/overtime/approve` — calls `calculate_ot_allowance_prc`
- [x] `src/components/attendance/AttendanceGrid.tsx` — calendar-grid (employees × days, color-coded, expandable IN/OUT/Duration rows)
- [x] `/attendance/register` page
- [x] `/attendance/overtime` page
- [x] `GET/POST /api/attendance/shift-planner` — per-day shift roster, locked once attendance verified
- [x] `GET/POST /api/attendance/regularisation`, `POST /api/attendance/regularisation/[id]/decide` — admin raise + approve/reject queue
- [x] `GET /api/attendance/comp-off` — read-only earned-vs-used report
- [x] `GET/POST /api/attendance/punches`, `POST /api/attendance/punches/sync`, `POST /api/attendance/punches/resync`, `PUT /api/attendance/punches/[id]` — device punch list/add/edit + device-log sync
- [x] `GET /api/attendance/checkin-reports` — daily/early-in/early-out/late-in/late-out reports
- [x] `/attendance/edit-punches` page
- [x] `/attendance/shift-planner` page
- [x] `/attendance/regularisation` page
- [x] `/attendance/checkin` page
- [x] `/attendance/comp-off` page
- [ ] `/setup/devices` page — no legacy device-config screen was ever found to port (`AttendanceSetupController` turned out to be a plan/feature-flag gate, not device config); left unbuilt, flagged for a future look if a real requirement surfaces.

**Milestone reached:** Can view, edit, and process attendance for a month (Register), approve Overtime once attendance is verified, plan shift rosters, raise/approve/reject attendance regularisations, sync/edit raw device punches, view Check-in reports, and view Comp-off balances. Phase 3 is functionally complete except for device setup.

### 3.1 Attendance Register + Overtime build (2026-07-14)

Three background research agents mapped legacy Attendance in depth (controllers, `.ctp` views, live DB schema/procedures) before building, per this project's standing practice. Key finding that overturned a prior assumption baked into the Remove Employee eligibility gate's comment: `attendance_register` (2,033 rows) and `emp_detail_timeattandance` (77,721 rows) are **not empty** — real historical data already exists for GRTL (migrated from legacy production), so this phase started from zero code, not zero data.

**Live vs. dead controllers** (confirmed via `EmployeeMenuController.php`'s hardcoded menu routing): ported `AttendanceRegisterNewController.php` (register/grid) and `OtAttendanceNewController.php` (OT — newer superset of `OtAttendanceController.php`, edits dated into 2026). Explicitly did not port: `AttendanceController.php` (plain, not menu-routed — superseded), the older `Attendance/` EasyUI-datagrid module (same concept, older UI), `OtAttendanceController.php` (superseded), `EmpeditpunchesController.php` (thinner duplicate, deferred with the rest of Edit Punches).

**Verified live signatures before wiring any call** (per this project's schema-drift discipline — a research agent naming a proc isn't the same as its arguments being confirmed): `att_start_end_fn`, `insert_update_att_reg` (confirmed 5-arg shape `(pbranch_code, pstart_date, pend_date, puserid, OUT poutput)` — a second call path in legacy's own model helper passes a different, incompatible arg shape and is dead/broken code, not ported), `leave_transaction_prc`, `leave_balance_inthe_year_fn`, `ot_duration_register_date`, `calculate_ot_allowance_prc`.

**Real proc-call logic ported**:
- **Process month** (`insert_update_att_reg`) does all the real per-day computation (holidays/weekoffs/leave transactions/device-attendance-derived defaults) — called as-is via `CALL`, not reimplemented in TypeScript, same "trust the stored proc" precedent as `final_settle_pay_prc`.
- **Cell edit** ports `chnagestatus()`'s real logic: blocks (409) if a leave is already Applied/Authorized/Approved for that date/session (`isLeaveAlreadyApplied`); if the new status is a leave code, auto-creates+approves a `leaveentries`/`emp_leave_transactions` row via `leave_transaction_prc` — ported now per an explicit decision, even though Leave Management (Phase 4) has no UI yet, since this is a direct backend proc call (same precedent as calling `leave_encash_prc` for Remove Employee without a Leave module existing).
- **Verify/lock** ports `checkifregistercanverify` + `verifyAttendance()`: blocks per-row if any calendar day is blank ("miss-punched date"), otherwise flips `isdelete` `'Y'→'N'` and recomputes OT duration per day via `ot_duration_register_date`.
- **Un-verify** ports `removeAttendance()`'s real per-employee skip semantics (not all-or-nothing): blocked if `payroll_master.action IN ('processed','approved')` or `emp_ot_master.is_verified='Y'` already exists for that month.
- **Overtime** ports `getNotApprovedData`/`getApprovedData` (OT only listed once attendance is verified — a real cross-module dependency, expressed via an `EXISTS` subquery, not a hard block) and `approves()` (upserts `emp_ot_master.set_duration`, flips `is_verified='Y'`, calls `calculate_ot_allowance_prc`).
- `attendance_register.isdelete` is a real legacy overload (`'Y'` means both "unverified" and, in a different code path, "soft-deleted") — kept the raw column since writes hit the same live table, but exposed to the frontend as an unambiguous `status: 'unverified' | 'verified'` instead of propagating the overload.

**Real bug found and fixed during verification** (not in legacy — a bug introduced and caught in this pass): the cell-edit route initially passed a single `half` value to both the `fromhalf`/`tohalf` parameters of `leave_transaction_prc` for a full-day leave, causing the procedure to treat it as a half-day and silently overwrite `leaveentries.leave_days` from `1` to `0.5`. Caught by inspecting the row after a live test call; fixed by computing distinct `fromHalf`/`toHalf` (matching the values already used for the `leaveentries` insert itself) and passing those instead. Also hit and fixed a NOT NULL constraint miss: `leaveentries.ISAutherizedby` has no default — added it (and `APPROVEDBY`) to the insert, sourced from the acting admin's `empFkey` (falls back to `0` for a pure-admin session with no employee record).

**Curl-verified end-to-end**: read-path checked against real historical data (a real verified `2026-06`/`GRTL08` month, 3 real employees, correct color/day values with zero writes). Write-path used a disposable cloned test employee (`emp_pkey=256`, cloned from a real active employee's `emp_proff` config) against a virgin month (`2020-01`) chosen specifically because it had zero pre-existing `attendance_register` rows for the branch. Verified: process-month (confirms `insert_update_att_reg` populates all 31 days correctly for every eligible employee in the branch — 12 real employees got real rows since the proc operates per-branch, not per-employee, matching legacy exactly), single-day edit (P/P), full-day and half-day leave-code edits (confirmed correct `leave_days`/`FROMHALF`/`TOHALF` after the bug fix above, and a real `emp_leave_transactions` row posted), the already-applied blocking gate, verify/lock (confirmed `isdelete` flip and blocked-edit-after-lock), un-verify, and the OT approve flow (confirmed OT only visible after attendance verified, `set_duration`/`is_verified` updated correctly, `calculate_ot_allowance_prc` executes cleanly). **All 12 real `attendance_register` rows created by the branch-wide process call, plus all test employee data (7 tables: `emp_details`, `emp_proff`, `leaveentries`, `emp_leave_transactions`, `emp_ot_master`, and the `attendance_register` rows themselves) were deleted afterward** — the virgin month was deliberately chosen so this cleanup fully restores the branch to its pre-test state.

`npm run build`/`tsc --noEmit` clean.

### 3.2 Shift Planner, Regularisation, Comp-off, Edit Punches, Check-in Reports (2026-07-15)

Closed out the rest of the Attendance sidebar menu. A targeted research agent mapped Regularisation and Comp-off in depth (the only two submenus not already researched in §3.1's pass); Shift Planner, Edit Punches, and Check-in Reports were covered by that earlier research. Scope decisions (via AskUserQuestion): Regularisation is **admin-only** — both raising a request on an employee's behalf and approving/rejecting a queue — since no employee self-service/hierarchy-manager login exists in this app (same precedent as Remove Employee); and this pass covers **all five** remaining submenus rather than splitting further.

**Live vs. dead variants**: ported `RegularisationController::adminindexnew`/`listadminregularizationnew`/`bulkupdate(true)` (confirmed live via `AttendanceSetupController::getAttendanceFeatures()`'s routing — `adminindex`/`listadminregularization` only matter for a hardcoded 16-company blocklist irrelevant to GRTL); `CompoffController::index()` (the `MiscellaniousReports/compoff*.ctp` variant and `ScheduledBreakOffController`'s bulk-grant workflow are a different, more complex feature, not ported); `EditPunchesController` (fuller live version — `EmpeditpunchesController` is a thinner self-service duplicate, not ported); `AttendanceCheckInOutController`'s report suite.

**Real legacy bug fixed, not replicated**: `RegularisationController::bulkupdate($adminUpdate=true)` — the live admin approve/reject path — skipped the "still pending" guard entirely and its Reject branch never checked for already-processed rows, allowing an admin to re-approve or re-reject an already-decided request (silently re-running the punch-flip logic a second time). Fixed by always requiring `approved='P' AND status=1` before any transition in `POST /api/attendance/regularisation/[id]/decide`, regardless of caller — curl-verified: re-deciding an already-approved test request correctly returned a 409 ("This request has already been processed").

**Real data-convention bug found and fixed during verification** (not a legacy bug — one introduced and caught in this pass): initial `device_attandance` inserts (both the Regularisation-approve punch and the Edit Punches manual-add punch) wrote direction into the `DIRECTION`/`ATTDIRECTION` columns and a placeholder string into `C1`. Inspecting real production punch rows during testing showed the actual live convention is the reverse — `C1` holds the direction (`'in'`/`'out'`), `DIRECTION`/`ATTDIRECTION` are always `NULL`, and `C2` carries a short source tag (`'MOB'` for the mobile app in real data). Fixed both insert paths to use `C1` for direction and `C2` for a source tag (`'REG'`/`'MAN'`), and fixed the regularisation-approve route's old-punch lookup (was filtering on `DIRECTION`, which is never populated) to filter on `C1` instead.

**Comp-off**: a pure read-only report (legacy has no comp-off request/approval table at all) — "used" days counted from `attendance_register.FIELD1..32 = 'COFF'` within the open `fin_year` window, "earned" days from `emp_detail_timeattandance` (full credit for `present='P/P'` on a weekoff/holiday, half credit for `P/A`/`A/P`) — moved the earned-minus-used arithmetic server-side (legacy left it to the view template). Generalized from legacy's hardcoded "only your own record" scope to an employee-picker, matching every other admin screen in this app.

**Shift Planner**: per-day roster (primary `emp_config` SHIFT + secondary MSHIFT options, saved overrides in `emp_shift_planner`), blocked (409) from saving once attendance for that employee/month is verified — matches legacy's real lock exactly ("re-iterate attendance in Edit Attendance to reflect shift changes").

**Edit Punches**: list/add/edit raw `device_attandance` punches with an explicit, narrow request body per route (closes a real legacy bug — `savepunch()` forwarded the entire raw `$_POST` blob to `save()`/`insert_func()` with no field whitelist, letting a caller set arbitrary columns); Sync/Re-sync call the confirmed-live `device_logs_iteration_fn`/`device_logs_resync_fn` (keyed by `emp_id`, the varchar company id, not `emp_pkey`); moving a punch's `SHIFTDATE` recomputes duration via the confirmed-live `time_duration_check`.

**Check-in Reports**: Daily Attendance built from `emp_detail_timeattandance` directly; Early/Late In/Out read from the confirmed-live `emp_early_in`/`emp_early_out`/`emp_late_in`/`emp_late_out` tables (real, pre-populated report tables with no PK — not written to by this port). Avoided `get_branch_code_abs_fn` (confirmed dead in the live DB in an earlier phase) that legacy's branch-hierarchy filtering depended on — built without branch-hierarchy scoping, consistent with this project's "GRTL is a single flat-branch tenant" simplification used elsewhere.

**Curl-verified end-to-end** on a fresh disposable test employee (`emp_pkey=257`, cloned `emp_proff`/`emp_config` SHIFT config from a real active employee) against virgin months chosen to avoid touching real data: Shift Planner read + roster save; Regularisation raise → approve (confirmed real `device_attandance` insert with the corrected `C1`/`C2` convention) → the double-processing 409 guard → a separate raise → reject; Edit Punches manual add, list, `SHIFTDATE` move, Sync and Re-sync (both procs executed cleanly); Comp-off read against real inserted `COFF`/earned test rows (confirmed correct used/earned/balance counts); Check-in Reports (daily + late-in) read against real historical production data with zero writes. **All test data (`emp_details`, `emp_proff`, `emp_config`, `emp_shift_planner`, `employee_regularaization`, `device_attandance`, `device_attandance_hist`, `attendance_register`, `emp_detail_timeattandance` — 9 tables) deleted afterward**, confirmed zero remaining rows.

`tsc --noEmit` and Turbopack's compile step both pass clean; the full `next build`'s later static-page-generation phase hit a Windows OS-level worker-thread/memory resource exhaustion (likely contention with the concurrently running dev server), not a code error — re-run `npm run build` in isolation (dev server stopped) to get a clean full build if needed.

---

## Phase 4: Leave Management
**Status: NOT STARTED**

- [ ] `GET /api/leave/requests` — my leave requests
- [ ] `POST /api/leave/requests` — apply for leave (calls `leave_transaction_prc`)
- [ ] `GET /api/leave/team-requests` — pending approvals for current user
- [ ] `POST /api/leave/team-requests/[id]/authorize` — calls `leave_transaction_prc`
- [ ] `POST /api/leave/team-requests/[id]/approve` — calls `leave_transaction_prc`
- [ ] `POST /api/leave/team-requests/[id]/reject` — calls `leave_transaction_prc`
- [ ] `GET /api/leave/balances` — leave balance per type per employee
- [ ] `GET /api/leave/types` — available leave types
- [ ] `GET /api/leave/authorizers?action=auth|apr` — calls `leave_auth_apr_person_fn`
- [ ] `/leave/my-requests` page
- [ ] `/leave/team-requests` page (with authorize/approve/reject actions)
- [ ] `/leave/balances` page
- [ ] `/setup/leave-policy` page
- [ ] `/leave/encashment` page

**Milestone:** Full leave workflow functional.

---

## Phase 5: Payroll Engine
**Status: NOT STARTED**

### 5.1 Salary Configuration
- [ ] `GET/POST /api/salary/heads` — salary heads CRUD
- [ ] `GET/POST /api/salary/heads/[id]/items` — items under a head
- [ ] `GET/POST /api/salary/structures` — salary structure CRUD
- [ ] `GET /api/salary/structures/[id]/breakup?gross=X` — calls `calculate_emp_salary_breakup`
- [ ] `GET/PUT /api/salary/employee/[empId]/structure` — employee's assigned structure
- [ ] `/setup/salary-heads` page
- [ ] `/setup/salary-structure` page

### 5.2 Payroll Processing
- [ ] `GET /api/payroll?branch=X&month=YYYY-MM` — list pending payroll_master
- [ ] `GET /api/payroll/processed?branch=X&month=YYYY-MM` — processed payroll
- [ ] `POST /api/payroll/process` — calls `payroll_master_insert`
- [ ] `POST /api/payroll/[id]/approve` — approve individual payroll
- [ ] `POST /api/payroll/[id]/reverse` — reverse approved payroll
- [ ] `GET /api/payroll/features` — plan feature check
- [ ] `GET /api/payroll/slip/[id]` — salary slip data
- [ ] `/payroll/process` page (branch + month selector → payroll list → process button)
- [ ] `/payroll/approve` page
- [ ] `/payroll/increments` page
- [ ] `/payroll/arrears` page
- [ ] `/payroll/year-end` page
- [ ] Salary slip PDF generator (jspdf-autotable)

### 5.3 Taxation
- [ ] `POST /api/taxation/process` — calls `tax_salary_process_prc`
- [ ] `/taxation` page
- [ ] `/setup/tax-heads` page
- [ ] `/employees/[id]/tax` page

**Milestone:** Can process payroll for a month and generate payslips.

---

## Phase 6: Reports
**Status: NOT STARTED**

- [ ] Salary reports — `/reports/salary`
- [ ] Salary slips (bulk) — `/reports/salary-slips`
- [ ] Attendance report — `/reports/attendance`
- [ ] ESI/EPF report — `/reports/statutory/esi-epf`
- [ ] Statutory registers — `/reports/statutory/registers`
- [ ] LOP report — `/reports/lop`
- [ ] Employee reports — `/reports/employees`
- [ ] Advance/loan/increment reports
- [ ] All reports: Excel (SheetJS) + PDF (jspdf-autotable) export

**Milestone:** All core reports functional with export.

---

## Phase 7: Loans, Advances, Assets
**Status: NOT STARTED**

- [ ] Employee loans (create, EMI schedule, track)
- [ ] Salary advances
- [ ] Asset management (assign, track, return)

---

## Phase 8: Plan-Gated / Company-Specific Modules
**Status: NOT STARTED**

- [ ] Performance management (if plan permits)
- [ ] Site & field operations (VGFS, VSFS, GEDE only)
- [ ] Procurement / inventory
- [ ] Mobile tracking

---

## Phase 9: Polish & Cutover
**Status: NOT STARTED**

- [ ] Migrate Slim PHP API v1 endpoints to Next.js API routes
- [ ] End-to-end test with real data (parallel run)
- [ ] Performance test (connection pool under load)
- [ ] UI responsiveness + mobile audit
- [ ] Error boundaries and loading states
- [ ] Production environment setup

---

## Environment Setup Checklist (Manual — User Action Required)
- [ ] MySQL running locally with `mypayrol_control_db` and at least one company DB (e.g. `mypayrol_trial`)
- [ ] Fill `.env.local` with actual DB credentials after project is created
- [ ] Run `npm install` in `rizo/` directory
- [ ] Run `npx shadcn-ui@latest init` in `rizo/` directory after npm install
- [ ] Run `npm run dev` to verify the dev server starts

---

## Login Rework (2026-07-06)

The original `authorize()` in `rizo/src/lib/auth.ts` was an invented simplification (required a "Company Code" field, queried a single unified `user_credentials` table in the control DB) that didn't match the legacy app and had a live bug (`central_control.plan_id` doesn't exist — any login attempt would SQL-error).

Reworked to match legacy's real two-path flow (`legacy/Controller/SiteController.php:268-360`, `legacy/Controller/Component/LoginManagementComponent.php`):
- **Admin path**: control DB `user_credentials` (by `user_id`/`email`) → `userGroup = 1`, `empFkey = null`, `planId` sourced from that row's own `plan_id` column (fixes the bug above).
- **Employee path** (fallback): company code derived by stripping digits from the username (e.g. `GRTL100011` → `GRTL`), looked up in `central_control`, then checked against that company's own `user_credentials` table via `getCompanyPool()` → `userGroup = 2` (hardcoded, matching legacy even though the company table's own `user_group` column holds different data), `empFkey` from the row.
- Login page no longer has a Company Code field — just Username + Password, matching legacy UX.
- Ported legacy's account lockout (3 failed attempts, resets daily) for the employee path; surfaced as a distinct "Account locked" message via a thrown `LOCKED` error from `authorize()`.

**Still deferred:** `reset_login_flag = 'Y'` blocks login but doesn't build the actual password-reset UX; email-based device login (`emp_device_comp_branch`) is out of scope.

---

*Last Updated: 2026-07-06 — Login rework complete*
