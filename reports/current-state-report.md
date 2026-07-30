# RIZO Migration — Current-State Report

**Scope:** compares the in-progress Next.js rewrite at `D:\Projects\RIZOMigration\rizo\` against the three prior legacy-analysis reports (`reports/user-side-report.md`, `reports/backend-report.md`, `reports/code-logic-report.md`) covering the legacy CakePHP app at `D:\Projects\RIZOMigration\legacy\`. This report is itself the required input for the next deliverable — a forward-looking migration plan — so every finding below is written to be directly actionable for that purpose: coverage status per legacy feature, fidelity discrepancies classified by what kind of decision they need, and a ranked risk list.

**Method:** nine research passes. Eight parallel deep-comparison passes each took one feature-area cluster (matching the same clustering used across the three legacy reports, so citations line up directly): Authentication & Authorization, Employee Management, Attendance & Time, Company/Organization Setup, Dashboards & Notifications, Assets & Documents, Promotions & Resignations, and a sweep confirming NOT STARTED status for every legacy cluster with zero footprint in the new app (Leave Management, Payroll/Salary/Tax, Advances/Loans/Expenses, Reports, Site/Field/Project, Statutory Registers, remaining Purchasing/Inventory, and clarifying Timesheet's fate). A ninth pass (architecture assessment) was done directly rather than delegated. Every claim below cites **both sides**: the legacy report + section, and the exact `rizo/src/...:line`.

**Important framing**: the new app is early-stage and intentionally narrower in scope than the legacy app today — that is expected and not itself a finding. What matters for the migration plan is (a) whether what **has** been built is behaviorally correct against the legacy reports, and (b) whether the architecture the built parts establish is sound enough to keep building on. Both questions come back largely positive, with specific, fixable exceptions detailed below.

---

## 1. Coverage Overview

The new app currently has real (not stub) coverage in **7 of the legacy app's 13 feature-area clusters**: Employee Management, Attendance & Time, Company/Organization Setup, Dashboards & Notifications, part of Assets/Inventory (asset allocation only, not procurement), Performance/HR (resignation + promotion only, not performance review), and Authentication (which cuts across all clusters). **6 clusters have zero footprint**: Leave Management, Payroll/Salary/Tax, Advances/Loans/Expenses, Reports, Site/Field/Project Management, and Statutory Registers — full detail and legacy citations for each are in §2.8 below. Timesheet is not a gap — it's implemented under the Attendance cluster's "register" feature, which covers the same monthly-grid concept legacy split across two controllers.

| Feature-area cluster | New-app coverage | Detail |
|---|---|---|
| Authentication & Authorization | Core login/lockout/permission-admin MIGRATED; multi-admin switch, employee-email login NOT STARTED | §2.1 |
| Employee Management | Core CRUD, hierarchy, bulk-policies, doc-templates MIGRATED; onboarding validation/notifications weakened (PARTIAL); Contacts/Beneficiary/EMI NOT STARTED | §2.2 |
| Attendance & Time | Register, punches, OT, regularisation, comp-off, shift-planner MIGRATED and delegate correctly to legacy stored procedures; ESS self-service and mobile tracking NOT STARTED | §2.3 |
| Company/Organization Setup | Branch/Dept/Designation/Grade/Holiday/Shift/FinYear MIGRATED via a shared reusable CRUD component; Bank master, subscription/payment gating NOT STARTED | §2.4 |
| Dashboards & Notifications | Consolidated to one dashboard (legacy had three); pending-leave and birthday widgets MIGRATED but reproduce a legacy date bug; hierarchy/BI dashboards NOT STARTED | §2.5 |
| Assets & Documents | Asset allocation MIGRATED (improved transactionality); full procurement chain (PO/GRN/Vendor/Stock/Item-master) NOT STARTED; document generation MIGRATED | §2.6 |
| Promotions & Resignations | Both MIGRATED, correctly delegating F&F settlement math to legacy stored procedures; Performance-review cycle NOT STARTED (legacy schema for it is itself missing/stale) | §2.7 |
| Leave Management | Setup-only (leave policy groups); the actual apply/authorize/approve/reject workflow is NOT STARTED | §2.8 |
| Payroll, Salary & Tax | Setup-only (salary structures, financial year); the actual payroll run/calculation engine is NOT STARTED | §2.8 |
| Advances, Loans & Expenses | NOT STARTED | §2.8 |
| Reports (all ~30 controllers) | NOT STARTED | §2.8 |
| Site/Field Work & Project Mgmt | NOT STARTED | §2.8 |
| Statutory Registers | NOT STARTED | §2.8 |

---


## 2. Fidelity Check by Feature Area

Each subsection below covers one feature-area cluster: the itemized coverage table (legacy item → legacy report reference → new app location → status: MIGRATED / PARTIAL / NOT STARTED / NEW), followed by the fidelity-check findings for everything MIGRATED or PARTIAL, each classified **INTENTIONAL-LOOKING** (a clear, deliberate improvement), **LIKELY BUG** (behavior silently differs in a way that looks unintentional), or **NEEDS HUMAN DECISION** (ambiguous — a genuine scope or design choice needing product/eng sign-off). Every finding cites both the legacy report section and the exact `rizo/src/...:line`.


### 2.1 Authentication & Authorization

# Auth & Authorization — Legacy vs. New App Coverage Audit

Scope: login flows (admin/employee), multi-admin company switch, password reset, account
lockout, `user_access`/`menu_id` permission-tree admin UI, SSO-bridge endpoint, mobile API auth.

Legacy sources read in full:
- `reports/user-side-report.md` — "## 1. Roles & Authentication Model" (lines 11-119)
- `reports/backend-report.md` — "## 4. Authentication & Authorization" (lines 5318-5364, §3.1-3.5)
- `reports/code-logic-report.md` — "### 1.6 Password/auth data-model confirmation" (line 60) and
  "### 7.12 Statutory & Access Admin" (lines 5696-5822, full `user_access`/`emp_menu` data model)

New app sources read in full:
- `rizo/src/lib/auth.ts`
- `rizo/src/app/(auth)/login/page.tsx`
- `rizo/src/app/(dashboard)/layout.tsx`
- `rizo/src/app/api/auth/[...nextauth]/route.ts`
- `rizo/src/app/(dashboard)/employees/access/page.tsx` + `rizo/src/app/api/employees/access/route.ts` + `[id]/route.ts` + `[id]/reset-device/route.ts`
- `rizo/src/app/(dashboard)/employees/menu-allocation/page.tsx` + `rizo/src/app/api/employees/menu-allocation/[id]/route.ts` (this — not a page literally named `employees/access` for menus — is the actual `UserAccessController` equivalent)
- `rizo/src/components/employees/MenuTree.tsx`
- All 96 files under `rizo/src/app/api/**/route.ts` grepped for `getServerSession` and `userGroup`

---

## 1. Coverage table

| Legacy item | Legacy report ref | New app location | Status |
|---|---|---|---|
| Admin login (username/password → `user_group=1`) | user-side-report.md §1.2 (`SiteController.php:268-479`); backend-report.md §3.2 | `rizo/src/lib/auth.ts:47-90` (`tryAdminLogin`) | MIGRATED |
| Employee login (username/password → `user_group=2`) | user-side-report.md §1.2; backend-report.md §3.2 | `rizo/src/lib/auth.ts:92-171` (`tryEmployeeLogin`) | MIGRATED |
| Coarse `user_group` session gate on every page | user-side-report.md §1.1 (`AppController.php:39-46`); backend-report.md §3.1 | `rizo/src/app/(dashboard)/layout.tsx:12-13` (session presence only) + per-API-route `session.user.userGroup !== 1` checks (83/96 routes) | MIGRATED (see fidelity notes — centralization is a design change, not a gap) |
| Password hashing (SHA-1, unsalted-per-row) | code-logic-report.md §1.6 / §7.12 Schema quirks #1; backend-report.md §3.2 | `rizo/src/lib/auth.ts:22-45` (`verifyAndUpgradePassword`) | MIGRATED + upgraded (see NEW capability below) |
| Account lockout after N failed attempts (legacy: 3, `LoginManagementComponent.php:167-245`) | code-logic-report.md §7.12 intro ("Implements account lockout after 3 failed attempts") | `rizo/src/lib/auth.ts:8` `MAX_LOGIN_ATTEMPTS = 3`, logic at `:136-153` | MIGRATED |
| Forced password-reset flow (`reset_login_flag='Y'` → token/URL) | code-logic-report.md §7.12 intro ("Implements forced password reset flow… generates a reset token/URL") | `rizo/src/lib/auth.ts:58,126` only checks the flag and rejects login; no reset-token/URL generation or employee-facing reset page exists anywhere under `rizo/src/app` | PARTIAL — see Fidelity finding F1 (likely bug, not just missing feature) |
| Multi-admin / multi-company picker (`loginWithCentral()`) | user-side-report.md §1.2 item 4; backend-report.md §3.5 | No `company` picker/switch route or UI found (`grep` for company-switch/companies_list across `rizo/src` returns nothing) | NOT STARTED |
| Login-by-employee-email via `emp_device_comp_branch` | code-logic-report.md §7.12 intro | `tryEmployeeLogin` only queries `user_credentials.user_id = ?` (`rizo/src/lib/auth.ts:111-117`) — no email-based lookup | NOT STARTED |
| Company-code-hardcoded post-login redirect exceptions (`GLET`/`ABSG` → `Dashboard/index`) | user-side-report.md §1.2 item 3 | Not applicable — new app has a single dashboard route, no per-role landing-page branching found in `layout.tsx` | NOT STARTED (arguably moot given unified dashboard design) |
| Menu-based permission grant/revoke admin UI (`UserAccessController`, `emp_menu`/`user_access`) | code-logic-report.md §7.12 §3.1 | `rizo/src/app/(dashboard)/employees/menu-allocation/page.tsx` + `rizo/src/app/api/employees/menu-allocation/[id]/route.ts` + `rizo/src/components/employees/MenuTree.tsx` | MIGRATED (simplified — see fidelity notes) |
| `user_access`/`menu_id` server-side enforcement on protected actions | code-logic-report.md §7.12 §3.1 ("no controller action anywhere re-checks `user_access` server-side"); backend-report.md §3.3 | No route under `rizo/src/app/api/**` reads `user_access`/`emp_menu` to gate any non-menu-allocation endpoint (only `menu-allocation` itself touches those tables) | NOT STARTED — legacy gap knowingly/unknowingly replicated; see Fidelity finding F4 (NEEDS HUMAN DECISION) |
| Employee-facing "Access" admin screen (web login enable/disable, lock, mobile access, punch type, password reset) | code-logic-report.md §7.12 (`UserCredentialsController`/`UserController`) | `rizo/src/app/(dashboard)/employees/access/page.tsx` + `rizo/src/app/api/employees/access/route.ts`/`[id]/route.ts` | MIGRATED |
| Mobile device reset (`securitycode`/`macid`/`imei` clear) | code-logic-report.md §7.12 (`mob_user_credentials`) | `rizo/src/app/api/employees/access/[id]/reset-device/route.ts` | MIGRATED |
| SSO-bridge endpoint (`AccessController.php`, password-in-URL, raw `mysql_*`, SQL-injection) | user-side-report.md §1.4; backend-report.md §3.4; code-logic-report.md §7.12 item 9 (Re-confirmed findings) | No `AccessController` equivalent route exists anywhere under `rizo/src/app/api` (confirmed by grep for `sso`/`bridge`/password-in-querystring patterns — no genuine hits) | NOT STARTED — confirmed **not reintroduced** (deliberately not migrated; correct call) |
| `LeaveapiController`/`InfoController` unauthenticated bypass controllers | user-side-report.md §1.4; backend-report.md §3.4 | No unauthenticated API route found: every `route.ts` except the NextAuth handler itself calls `getServerSession` (95/96, confirmed by `comm` diff) | NOT STARTED (in the sense that the vulnerability itself is not reproduced — the coverage is effectively total) |
| Mobile API auth (`mob_user_credentials`, plaintext password mirror) | code-logic-report.md §7.12 Schema quirks #2 | New app only manages `mob_user_credentials.locked`/`punchtype`/device fields via the Access admin screen; no mobile-app-facing login/auth API route found under `rizo/src/app/api` (no `api/v1`-style mobile endpoint set) | NOT STARTED (out of scope for admin-web rewrite so far — flag for product to confirm whether a native mobile API is planned) |
| CSRF protection (legacy: none, `SecurityComponent` never registered) | backend-report.md §3.2 | Next.js/NextAuth JWT + same-site cookies provide baseline CSRF protection by default (framework-level, not custom code found) | NEW / MIGRATED-as-improvement (framework default, not a ported legacy capability) |
| bcrypt-upgrade-on-login (SHA-1 legacy hash transparently rehashed to bcrypt on next successful login) | — (no legacy counterpart; legacy never had a hash-upgrade path per code-logic-report.md §1.6: "No bcrypt/PBKDF2 upgrade path exists") | `rizo/src/lib/auth.ts:22-45` | NEW |

---

## 2. Fidelity check findings

### F1 — Forced password-reset flag becomes a permanent lockout, not a reset flow (LIKELY BUG)

- **Legacy**: `code-logic-report.md` §7.12 intro states `LoginManagementComponent.php` "Implements forced password reset flow: if `reset_login_flag == 'Y'`, generates a reset token/URL instead of logging in" — i.e. the flag routes the user to an actual reset UI, not a dead end.
- **New app**: `rizo/src/lib/auth.ts:58` (`tryAdminLogin`) and `:126` (`tryEmployeeLogin`) both do `if (user.reset_login_flag === 'Y') return null;` — this is indistinguishable from a wrong-password failure to the caller. The login page (`rizo/src/app/(auth)/login/page.tsx:42-48`) only special-cases a `LOCKED` error string; anything else (including this reset-required case) renders "Invalid username or password."
- **Compounding bug**: `rizo/src/app/api/employees/access/[id]/route.ts:31-38` sets `reset_login_flag = 'Y'` whenever an admin sets a new password for a user via the Access screen. Grepping the entire `rizo/src` tree for `reset_login_flag` shows it is **written to `'Y'` in one place and to `'N'` only at row-creation time** (`rizo/src/app/api/employees/import/route.ts:147`, `rizo/src/app/api/employees/join/[id]/onboard/route.ts:137`) — no code path anywhere clears it back to `'N'` after it's set to `'Y'`. Net effect: any employee whose password an admin resets via the Access UI is **permanently unable to log in** (every future login attempt hits the `reset_login_flag === 'Y'` branch and silently fails) until someone manually flips the DB column, since there is no employee-facing "you must reset your password" screen to complete the flow.
- **Classification**: LIKELY BUG — looks like an incomplete migration (the flag-setting half of the feature was ported, the flag-clearing/reset-UI half was not), not an intentional design choice.

### F2 — Account lockout threshold matches (3 attempts) but the "streak" logic differs subtly (NEEDS HUMAN DECISION / possible LIKELY BUG)

- **Legacy**: `code-logic-report.md` §7.12 confirms 3-attempt lockout in `LoginManagementComponent.php:167-245`, exact reset semantics not fully detailed in the reports (report text: "locks via `data['locked'] = 1`").
- **New app**: `rizo/src/lib/auth.ts:137-141` resets the attempt counter to 1 unless the previous failed attempt's `end_date` (reused as a "last-attempt date" column) is today's date:
  ```
  const attempts = lastAttemptDate === today ? (user.incorrect_login_attempt || 0) + 1 : 1;
  ```
  This means the 3-strikes counter is **daily**, not a rolling/absolute counter — a user who fails twice today, then fails again tomorrow, gets `attempts = 1` (reset), not `3`. Legacy's exact reset condition (day-boundary vs. never-reset-until-success) isn't fully re-derivable from the reports available in this pass (only the line-range citation `LoginManagementComponent.php:167-245` is given, without the reset condition spelled out), so I cannot confirm whether this day-boundary behavior matches legacy exactly or is a new invention. **Flag for a human to diff against `LoginManagementComponent.php:167-245` directly** — if legacy's counter resets only on a *successful* login (not on a new calendar day), this is a fidelity gap that weakens the lockout (an attacker gets 3 fresh guesses every day indefinitely, rather than 3 total until admin/self-recovery).
- Also note: `user.end_date` is being repurposed to store "date of last failed attempt" (`rizo/src/lib/auth.ts:144-149`, `SET ... end_date = CURDATE()`) — worth confirming this column isn't also used elsewhere in the new schema for its original (presumably employment end-date-ish) meaning; not confirmed one way or the other from the reports read in this pass.

### F3 — Centralized page-level auth gate vs. legacy's per-controller duplicated check (INTENTIONAL-LOOKING, positive)

- **Legacy**: `backend-report.md` §3.1 — every one of ~223 controllers re-implements the `user_group` session check via `AppController::beforeFilter()`; a handful of controllers (`InfoController`, `LeaveapiController`, `AccessController`) extend the bare `Controller` class and **skip it entirely**, creating real unauthenticated-access vulnerabilities (§3.4).
- **New app**: `rizo/src/app/(dashboard)/layout.tsx:12-13` centralizes the "must be logged in" check for all pages under `(dashboard)`, and 95/96 API routes independently re-check `getServerSession` (only the NextAuth handler itself doesn't, correctly). No route was found that skips authentication the way legacy's `InfoController`/`LeaveapiController`/`AccessController` did.
- **Classification**: INTENTIONAL-LOOKING improvement — centralized layout gate + consistent per-route re-check is strictly safer than legacy's ad hoc per-controller pattern, and the specific unauthenticated-bypass vulnerability class from legacy was not reproduced anywhere in the 96 routes checked.

### F4 — `user_access`/`emp_menu` permission tree is UI-only in the new app too, and the "UI" itself is now less capable than legacy's (NEEDS HUMAN DECISION)

- **Legacy**: `code-logic-report.md` §7.12 §3.1 documents an elaborate permission model: per-menu grant/revoke with **asymmetric cascade logic** (enabling a child menu unconditionally force-enables its parent; disabling a child only disables the parent if it was the last active sibling — `UserAccessController.php:539-623` and `:835-923`), a separate `status` soft-delete column layered on top of `active`, and a distinct "add-on/feature" permission model overloading `menu_id=0` rows. Critically, `backend-report.md` §3.3 confirms this whole layer is **UI-only** — no legacy controller ever re-checks `user_access` before executing a protected action.
- **New app**: `rizo/src/app/api/employees/menu-allocation/[id]/route.ts` implements a flat "set of checked menu_ids → upsert/deactivate `user_access` rows" (`:80-101`) with **no cascade logic at all** (no parent/child auto-enable or last-sibling-disable), and no add-on/feature (`menu_id=0`) handling. `rizo/src/components/employees/MenuTree.tsx` renders a simple recursive checkbox tree with independent per-node toggling.
- The new app's server-side situation is the **same as legacy**: `grep` across all API routes in `rizo/src/app/api` found no route (other than `menu-allocation` itself) that reads `user_access`/`emp_menu` to gate access to any other endpoint — the permission tree remains purely advisory/UI-only, exactly replicating legacy's confirmed gap (`backend-report.md` §3.3: "a significant design decision the Next.js app should not blindly replicate").
- **Classification**: NEEDS HUMAN DECISION on two independent axes:
  1. Whether losing the enable/disable cascade behavior is an acceptable simplification (likely fine — the report itself calls the legacy cascade asymmetry "a real inconsistency," so simplifying to flat set-based toggling may be a deliberate, reasonable choice) or a functional regression admins will notice (e.g. previously toggling a submenu auto-granted the parent for free; now an admin must check both explicitly).
  2. Whether `user_access`/`emp_menu` should become server-enforced in the new app (closing the legacy gap) or whether replicating the legacy UI-only-enforcement gap is acceptable for this migration's scope — this is the same question the task brief flags, and the research confirms the new app currently makes the **same** choice legacy did (gap replicated, not closed).

### F5 — SHA-1→bcrypt upgrade-on-login (INTENTIONAL-LOOKING, clear improvement)

- **Legacy**: `code-logic-report.md` "### 1.6 Password/auth data-model confirmation" and code-logic-report.md §7.12 "Re-confirmed prior findings" — `user_credentials.password` is `varchar(100)` holding CakePHP's default SHA-1 hash (via `Security::hash($x, null, true)`), salted only by a single app-wide `Security.salt` constant, "No bcrypt/PBKDF2 upgrade path exists."
- **New app**: `rizo/src/lib/auth.ts:22-45` (`verifyAndUpgradePassword`) detects a stored value matching the 40-hex-char SHA-1 pattern, verifies against it, and — if valid — **transparently rehashes with bcrypt (`bcrypt.hash(plainPassword, 12)`) and writes it back** (`:35-39`) before the next call would need to go through the SHA-1 path again. New credentials created directly in the new app (`rizo/src/app/api/employees/access/[id]/route.ts:32,72`) are bcrypt from creation.
- **Classification**: NEW / INTENTIONAL-LOOKING — a textbook, low-risk password-hash-migration pattern; no legacy counterpart, and correctly flagged as NEW in the coverage table above.

### F6 — SSO-bridge / password-in-URL vulnerability (confirmed NOT reintroduced)

- **Legacy**: `code-logic-report.md` §7.12 item 9 and `backend-report.md` §3.4 — `AccessController.php:59-87` uses deprecated `mysql_*` calls, hardcoded plaintext DB credentials, unescaped `$_GET` concatenated into SQL, and redirects with the plaintext `mob_user_credentials.password` value in the URL query string (`:80`).
- **New app**: No `AccessController`-equivalent route exists. A grep of the entire `rizo/src` tree for password-in-querystring patterns (`password` near `searchParams`/`query`/`url` in any `route.ts`) returned zero hits, and no route path resembling an SSO bridge was found.
- **Classification**: Confirmed NOT STARTED / not reintroduced — this is the correct outcome and should be called out explicitly as a non-issue rather than an oversight.

### F7 — Multi-admin/company-switch flow entirely absent (NOT STARTED, scope question)

- **Legacy**: `user-side-report.md` §1.2 item 4 and `backend-report.md` §3.5 describe `SiteController::loginWithCentral()` — logins that govern multiple tenant companies get a company-picker step before landing in a specific company's session.
- **New app**: `rizo/src/lib/auth.ts` has no equivalent code path; a single `tryAdminLogin`/`tryEmployeeLogin` resolves exactly one `companyCode` per login, and no company-switch UI or API route was found anywhere in `rizo/src/app`.
- **Classification**: NOT STARTED. Not classified as a bug since it's a clean absence rather than a broken partial port, but flagged since it's a real legacy capability with no coverage at all — worth a product decision on whether any current users rely on multi-company admin logins before this migration ships.

---

## 3. Summary for follow-up

**High-priority items for a human/eng decision:**
1. F1 (reset_login_flag dead-end) — this looks like a genuine bug that will lock out real users the first time an admin uses the "reset password" feature in the Access screen; recommend fixing before this screen ships, not just noting it.
2. F2 (lockout counter's daily-reset semantics) — needs a direct diff against `LoginManagementComponent.php:167-245` (not fully re-derivable from the three reports alone) to confirm whether the new day-boundary reset logic is a deliberate simplification or an unintentional weakening of the lockout.
3. F4 (server-side enforcement of `user_access`/`emp_menu`) — product/eng call on whether to close the legacy gap now or explicitly accept replicating it.
4. F7 (multi-company admin login) — confirm whether any current tenant relies on this before deprioritizing.

**Confirmed non-issues (legacy vulnerabilities correctly NOT reproduced):**
- SSO-bridge/password-in-URL endpoint (F6).
- Unauthenticated bypass controllers (`InfoController`/`LeaveapiController`-style gaps) — every API route except the NextAuth handler itself checks `getServerSession`.

---

### 2.2 Employee Management

# Employee Management — Coverage & Fidelity Audit

Scope: Legacy CakePHP Employee Management cluster vs. new Next.js app under
`rizo/src/app/(dashboard)/employees/**` and `rizo/src/app/api/employees/**`.

Legacy sources read in full:
- `reports/user-side-report.md` lines 82-541 ("## 2. Employee Management")
- `reports/backend-report.md` lines 55-673 ("### 2.1 Employee Management")
- `reports/code-logic-report.md` lines 2216-2599 ("### 7.1 Employee Management")

---

## 1. Coverage table

| Legacy item | Legacy report ref | New app location | Status |
|---|---|---|---|
| **EmployeeController** (core CRUD: list/setup/save, id_card+lwf duplicate check, bulk CTC upload, promotion/increment, OTP device import, resume PDF) | user-side-report.md:100-136; code-logic-report.md:2498-2507 (bulk import) | `app/(dashboard)/employees/page.tsx`, `[id]/page.tsx`, `new/page.tsx`; `app/api/employees/route.ts`, `[id]/route.ts` | **PARTIAL** — list/detail/edit/create + id_card/lwf/emp_id duplicate check ported; promotion workflow, salary-increment workflow, OTP/device import, resume PDF download NOT ported (covered separately or missing — promotions listed as own cluster below) |
| **EmployeeDetailController** (empty stub, no `.ctp` views, no behavior) | user-side-report.md:139-149 | — | **N/A** — legacy stub with zero implemented behavior; nothing to migrate |
| **EmployeeJoinController** — onboarding wizard, `calculateOnboardingPercentage()`, `getOnboardingCompletion()`, `sendOnboardingMail()` | user-side-report.md:152-179; code-logic-report.md:2457-2496 | `app/(dashboard)/employees/join/**`, `app/api/employees/join/**` | **PARTIAL** — join-record CRUD, sub-resource capture (documents/education/experience/family), Excel bulk-join-upload, and onboard→promote-to-employee all ported. **Completion-percentage calculation and its email notification are absent entirely** (see Fidelity Findings §1) |
| **EmployeeRegisterController** (attendance punch register/correction, NOT employee registration) | user-side-report.md:182-215 | — (belongs to Attendance cluster) | **out of scope** — despite the name this is an attendance-punch feature, correctly not part of this cluster's new-app surface; not evaluated here |
| **EmployeeManageController** (landing shell + `getEmployeeFeatures()` plan-gated tile visibility) | user-side-report.md:218-232 | — | **NOT STARTED** — no plan/feature-flag-driven employee sub-menu visibility endpoint found in new app |
| **EmployeeConfigController** ("Bulk Policy Allocation": Shift/Holiday/Leave/Salary/Notice/Division/Section/Grade tabs + `getEmployeeTree()`) | user-side-report.md:235-260; code-logic-report.md:2423-2456 (trigger cascade) | `app/(dashboard)/employees/bulk-policies/page.tsx`, `app/api/employees/bulk-policies/route.ts` | **MIGRATED** — all 7 non-hierarchy policy types (SHIFT/LEAVE/HOLIDAY/NOTICEPER/DIVISION/SECTION/GRADE) plus SALARY (via `sal_structure_distribution_fn`) ported as one generic bulk endpoint; per-employee success/failure returned (legacy bug fixed, see Fidelity §5) |
| **EmployeeMenuController** (`getDefaultMenus`/`getAddonMenus`/`setFeatureSession` — ESS nav composition) | user-side-report.md:263-284 | `app/(dashboard)/employees/menu-allocation/page.tsx`, `app/api/employees/menu-allocation/[id]/route.ts` | **PARTIAL / RESTRUCTURED** — new app exposes an *admin-facing* per-employee `user_access`/`emp_menu` allocation editor (tree + assign), which is new UI surface; legacy's `getDefaultMenus`/`getAddonMenus` (what an ESS user sees rendered) is not directly ported as a distinct read endpoint for the employee's own nav — see Fidelity §6 |
| **EmployeeHierarchyController** (reporting-line tree editor, `EmployeeStructure` + `emp_details.parent`) | user-side-report.md:288-310; code-logic-report.md:2400-2421 (three-representation problem) | `app/api/employees/hierarchy/route.ts`, `components/employees/HierarchyMover.tsx` | **MIGRATED, REDESIGNED** — collapses to a single authoritative source (`emp_config` type=HIERARCHY with `hirc_leval` ordering), denormalizing to `emp_proff.attr1` only as a read cache. `emp_structure` and `emp_details.parent` are not used at all. See Fidelity §4 (positive finding) |
| **EmployeeUnderController** (branch-scoped "Employees Setup", forces branch to caller's own, no duplicate check) | user-side-report.md:313-335 | — | **NOT STARTED** — no branch-locked self-service employee-setup screen found; main `employees/page.tsx` + `[id]` are admin-only (`userGroup !== 1` restricted to viewing self only, no editing) |
| **EmployeeResignationController** ("Remove Employee" — offboarding/Full & Final) | user-side-report.md:338-372; code-logic-report.md:2379-2399 (status 3-value state machine) | `app/(dashboard)/employees/resignations/**` | **covered in Resignations cluster** — per task instructions, not analyzed for fidelity here; noted only for coverage completeness |
| **NoticePeriodController** (notice-period master CRUD) | user-side-report.md:376-398 | `app/api/setup/notice-periods/route.ts` (confirmed via grep) | **MIGRATED** — moved to Setup/master-data cluster rather than Employee cluster; not analyzed in depth (out of this cluster's page/route set) |
| **EmployeeEmiController** (monthly EMI processing, bulk EMI upload, replay-token guard) | user-side-report.md:402-434 | — (no matches for `EmployeeEmi`/loan-EMI anywhere in `app/`) | **NOT STARTED** |
| **DocumentManagerController** — (a) template engine (create/preview/generate per-employee PDF), (b) plain file-upload/document library (`documentUpload`/`documentAllocate`) | user-side-report.md:438-476 | (a) `app/(dashboard)/employees/generate-documents/page.tsx` + `app/api/document-templates/**` + `app/api/employees/[id]/generate-document/route.ts`; (b) — | **PARTIAL** — part (a), the template-merge/generate/history flow, is fully ported (Generate/Templates/History tabs). Part (b), the generic file-upload-and-allocate-to-employees library, has **no equivalent** found anywhere in the new app |
| **ContactsController** (Customer/Vendor master CRUD) | user-side-report.md:479-501 | — (no matches for "contacts" as a master-data entity anywhere in `app/`) | **NOT STARTED** |
| **BeneficiaryController** (near-duplicate of Contacts, pointed at a table absent from the schema dump; Excel export/import) | user-side-report.md:505-527; code-logic-report.md:2287-2295 | — (no matches for "beneficiary" anywhere in `app/`) | **NOT STARTED** — the legacy `Beneficiary`-table-doesn't-exist mismatch is neither reflected nor resolved because the feature simply hasn't been built yet in the new app (see Fidelity §7) |
| Employee `tax-declarations` (per-employee tax-head/declaration entry against open financial year, locking) | not a named legacy controller in this cluster's scope — behaviorally closest to a `Tax`/`Taxation` controller referenced only implicitly | `app/(dashboard)/employees/tax-declarations/page.tsx`, `app/api/employees/[id]/tax-declarations/**` | **NEW (relative to this cluster)** — genuinely a different controller's territory (Payroll/Tax cluster), not one of the 15 controllers enumerated for this scope; flagged per instructions as "figure out if new" — this is a **renamed/relocated legacy feature** (tax head declarations existed in legacy under Payroll/Tax controllers), not a net-new capability, but out of primary scope for this audit |
| `employees/access` | — | `app/(dashboard)/employees/access/**` | excluded per task instructions — covered in the auth cluster |
| `employees/assets` | — | `app/(dashboard)/employees/assets/**` | excluded per task instructions — covered in Assets cluster |
| `employees/promotions` | user-side-report.md:121 (`EmployeeController::promotion()`/`savepromotions()`/`approvepromotion()`) | `app/(dashboard)/employees/promotions/**` | excluded per task instructions — covered in Promotions cluster |
| `employees/resignations` | see EmployeeResignationController row above | `app/(dashboard)/employees/resignations/**` | excluded per task instructions — covered in Resignations cluster |

---

## 2. Fidelity check findings

### 2.1 Onboarding completion-percentage algorithm — DROPPED (LIKELY BUG / NEEDS HUMAN DECISION)

Legacy `calculateOnboardingPercentage()`/`getOnboardingCompletion()`
(`code-logic-report.md:2457-2496`, ultimately `Controller/EmployeeJoinController.php:10235-10388`)
computes a 7-section weighted-average completion percentage (personal fields, conditional
company-data fields by `emp_type`, family/education/experience binary checks, config-type count,
documents), drives a progress ring in the UI, and triggers `sendOnboardingMail()` to the admin
when onboarding completes.

The new app's onboarding flow — `rizo/src/app/(dashboard)/employees/join/page.tsx`,
`rizo/src/app/(dashboard)/employees/join/[id]/onboard/page.tsx`, and
`rizo/src/app/api/employees/join/[id]/onboard/route.ts` — has **no completion-percentage
calculation anywhere**. The join list page (`join/page.tsx:69-126`) shows only Name/Email/Mobile/DOB/
District columns and an "Continue Onboarding"/"Edit"/"Discard" action set — no `%` column, no
progress indicator. `emp_join.status` is used only as a binary in-progress(1)/promoted(0) flag
(`app/api/employees/join/[id]/onboard/route.ts:142`: `UPDATE emp_join SET status = 0, emp_fkey = ?`),
not as a percentage tracker. There is also no completion-email notification anywhere in the onboard
route.

This is a genuine feature loss (HR could previously see per-candidate completion progress and get
notified on completion) rather than a data-model simplification — classify as **LIKELY BUG /
NEEDS HUMAN DECISION**: confirm with product whether this was a deliberate simplification (the new
app's onboarding is a single-step "fill everything, click Complete Onboarding" form rather than a
progressive multi-session wizard, which may make a % tracker less meaningful) or an overlooked gap.

### 2.2 Employee status — narrowed from 3 values to a 2-value toggle, and semantics changed (LIKELY BUG)

Legacy: `emp_details.status` is `0=inactive, 1=active, 2=terminated-pending-payroll`
(`code-logic-report.md:2357-2377`, `2364-2371`). The real state machine is
`Active(1) → [resignation] → Terminated-pending(2) → [payroll settlement] → Inactive(0)`; status
`2` is a *transient* mid-offboarding state gating "unverified attendance processing" checks, set
only by `EmployeeResignationController`.

New app: `rizo/src/app/(dashboard)/employees/page.tsx:35,51-59,94,100-105,150-163` implements
`toggleStatus` as a straight admin-clickable flip between **status 1 ("Active") and status 2**,
labeled "Deactivate"/"Activate" and filtered by a two-option "Active"/"Resigned" tab. The
corresponding API, `rizo/src/app/api/employees/[id]/route.ts:18-19`
(`if (body.status !== 1 && body.status !== 2) return ... 'status must be 1 (active) or 2
(resigned/inactive)'`), hard-validates only `{1,2}` — **status `0` (true "inactive") is never
reachable through this endpoint**, and status `2` is relabeled generically as "resigned/inactive"
rather than preserved as the legacy's specific "terminated, pending payroll" transient state.

This conflates two distinct legacy concepts (a manual admin deactivate/reactivate toggle, which
legacy never actually exposed as a simple binary — legacy's own reachable UI path to status changes
was exclusively through the Resignation workflow, not a direct list-page toggle) with the payroll-
settlement transient state. Any code elsewhere in the new app that expects `status = 2` to mean
"mid-offboarding, awaiting Full & Final" (e.g. if the Resignations cluster reads/writes this same
column) could now collide with an admin's manual "Deactivate" click on the employee list.
Classify: **LIKELY BUG / NEEDS HUMAN DECISION** — confirm against the Resignations-cluster
implementation whether `status=2` writes overlap, and whether the list-page toggle should exist at
all given legacy had no such direct control.

### 2.3 New-employee/edit-employee form validation — substantially weaker than legacy (LIKELY BUG)

Legacy `Employee/setup.ctp` marks as required: `first_name`, `last_name`, `classification`,
`date_of_birth`, `nationality_id`, nominee `name`/`relation`/`date_of_birth_nominee`, `id_card`,
`country` (conditional), `joining_date`, `emp_type`, `notice_days`, `emp_dept`, `designation`,
`emp_branch`, `shift`, `holidays`, `leave`, `salary` (`user-side-report.md:111`).

New app `rizo/src/app/(dashboard)/employees/new/page.tsx:113-114` marks **only `first_name`** as
`required` (`<input required className="input" {...f('first_name')} />`); every other field
(`last_name`, `date_of_birth`, `joining_date`, `emp_type`, `emp_branch`, `designation`, `emp_dept`,
etc.) has no `required` attribute. `rizo/src/app/api/employees/route.ts` `POST` has no server-side
validation beyond the duplicate id_card/lwf_code/emp_id check (`:81-94`) — no `zod` schema exists
anywhere under `app/api/employees/` or `app/(dashboard)/employees/` (confirmed via
`grep -rl zod app/api/employees app/(dashboard)/employees` → no matches). Both client and server
validation are strictly weaker than legacy's HTML5-required field set.

Classify: **LIKELY BUG** — an employee record can currently be created in the new app with only a
first name, which was not possible (at the client-validation layer, at least) in legacy. This is
also inconsistent with the onboarding path (`join` flow), which does mark `emp_company_id`,
`username`, `password` as required at `rizo/src/app/(dashboard)/employees/join/[id]/onboard/page.tsx:117,121,125`
and enforces them server-side (`rizo/src/app/api/employees/join/[id]/onboard/route.ts:31-36`) — i.e.
the new app has two employee-creation paths with very different rigor.

### 2.4 Hierarchy — collapsed to one representation, a genuine improvement (INTENTIONAL-LOOKING, positive)

Legacy has **three uncoordinated hierarchy stores** (`code-logic-report.md:2400-2421`):
`emp_details.parent` (written by `saveHeirarchy()`), `emp_structure` (written by
`EmployeeHierarchyController::add()/remove()`), and `emp_proff.attr1` (written by the `emp_config_bi`
MySQL trigger for `type='HIERARCHY'` rows).

New app: `rizo/src/app/api/employees/hierarchy/route.ts` treats **`emp_config` (type=`HIERARCHY`,
ordered by `hirc_leval`) as the single source of truth**, and explicitly denormalizes into
`emp_proff.attr1` as a read cache — with an inline comment documenting *why*:
`rizo/src/app/api/employees/hierarchy/route.ts:169-171` — "`emp_config_au` (an existing DB
trigger) unconditionally NULLs `emp_proff.attr1` on ANY update to an `emp_config` row of type
HIERARCHY, not just removals — restore the manager assignment immediately after each reorder
update or it gets silently wiped." This shows the migration author read and understood the trigger
behavior documented in `code-logic-report.md:2443-2445` (`emp_config_au` nulls the corresponding
`emp_proff` column on update) and coded around it correctly. `emp_details.parent` and
`emp_structure` are not referenced anywhere in `hierarchy/route.ts` or `HierarchyMover.tsx` —
confirmed via `grep` no other employees-cluster file writes those columns/tables.

Classify: **INTENTIONAL-LOOKING** and a genuine fidelity/architecture improvement over legacy — flag
positively rather than as a discrepancy, but note it as a decision item: legacy code elsewhere
(outside this cluster, e.g. Reports' `HierarchyReportController` per
`user-side-report.md:2493`) may still read `emp_details.parent` or `emp_structure` directly: if any
other ported cluster in the new app queries those columns instead of `emp_config`, they will now
silently diverge from the new canonical source. Worth a cross-cluster check.

### 2.5 Bulk policy allocation — legacy's "only-last-employee-response" bug fixed (INTENTIONAL-LOOKING, positive)

Legacy `EmployeeConfigController::addEmpToShift()` overwrites a single `$response` variable inside
its per-employee loop, so only the last employee's `{status, message}` is ever returned to the
client for a bulk assignment (`code-logic-report.md` cross-reference via `user-side-report.md:247`
— "Returns `{status, emp_fkey, message}` per employee (only the last employee's response is
actually echoed since `$response` is overwritten each loop iteration — likely a real bug for bulk
assignment...)").

New app `rizo/src/app/api/employees/bulk-policies/route.ts:36-64` (SALARY path) and `:81-111`
(all other policy types) both correctly accumulate **per-employee results** and return an
aggregate (`assigned`/`failed` counts, with a `failedNote` for the SALARY case). This fixes the
legacy bug rather than reproducing it.

Classify: **INTENTIONAL-LOOKING** (bug fix, not a fidelity gap) — flagged as a deliberate
improvement, not something requiring reconciliation.

### 2.6 Menu allocation — restructured from ESS nav-composition to an admin allocation editor (NEEDS HUMAN DECISION)

Legacy `EmployeeMenuController` (`user-side-report.md:263-284`) is **read-only from the employee's
own perspective**: `getDefaultMenus()`/`getAddonMenus()` compute and return the menu tree an ESS
user is entitled to see (driven by `user_access`/`user_feature_branch_access` rows that are
presumably populated elsewhere — no `save`/`add`/`assign` action exists in this controller itself).

New app `rizo/src/app/(dashboard)/employees/menu-allocation/page.tsx` +
`rizo/src/app/api/employees/menu-allocation/[id]/route.ts` is the **write side**: an admin-facing
tree editor that lets HR directly assign/revoke `user_access` rows per employee (`PUT` handler at
`rizo/src/app/api/employees/menu-allocation/[id]/route.ts:56-111`, upserting/deactivating
`user_access` rows keyed by `menu_id`). This is plausibly the missing "where do `user_access` rows
actually get populated" piece that legacy's own `EmployeeMenuController` doesn't answer (the report
explicitly flags `user_access` as read-only-consumed in this controller) — i.e. legacy has this
write capability *somewhere* (not found in this cluster's 15 controllers), and the new app may have
consolidated it here. Cannot confirm without checking the Access/UserAccess-focused cluster
(excluded from this audit's scope per instructions).

Classify: **NEEDS HUMAN DECISION** — confirm whether the write-side of `user_access` already exists
in the excluded `employees/access` cluster (in which case `menu-allocation` may be a **duplicate**
capability, not a gap-fill), and whether legacy's `getDefaultMenus`/`getAddonMenus` (an ESS user's
own live-rendered nav, which also has company-specific overrides at
`user-side-report.md:274`, feature_key `81` remapping) needs a dedicated read-side port distinct
from this admin editor.

### 2.7 Beneficiary — feature not built at all; legacy's schema mismatch neither surfaces nor resolves (NEEDS HUMAN DECISION)

Legacy: `BeneficiaryController` is fully wired and reachable, but its model points at a `beneficiary`
table **absent from the authoritative schema dump** (`code-logic-report.md:2287-2295`,
`:2588-2589` — "needs verification against a live database, not assumed dead").

New app: no `beneficiary`-related file exists anywhere under `app/` (confirmed via
`grep -rli beneficiary app --include="*.tsx" --include="*.ts"` → no matches).

Classify: **NEEDS HUMAN DECISION** — this is not evidence the mismatch was "resolved" (there's
nothing to compare); it simply hasn't been migrated yet. Before building it, the team must first
verify against a live legacy DB whether `beneficiary` is a real, populated table (schema-dump gap)
or genuinely dead — building against the wrong assumption would either recreate the mismatch or
silently drop live data if the table does exist and holds records.

### 2.8 Duplicate-check strategy diverges from legacy by design, documented inline (INTENTIONAL-LOOKING, positive)

Legacy's `EmployeeController::saveemployeesetupnew()` duplicate check
(`user-side-report.md:115`, `EmployeeController.php:3195-3221`) checks `id_card` and `lwf_code`
only, and has an internal inconsistency (a `lwf_code`-only duplicate still saves the record while
reporting an error — `:3211-3218`). The bulk `DataUploaderController::uploadandsaveempdetails`
legacy path apparently used a weaker `first_name+last_name+date_of_birth` check per the new app's
own comment.

New app: `rizo/src/app/api/employees/route.ts:80-94` checks `id_card`, `lwf_code`, AND `emp_id`
together (stronger than legacy, and does not exhibit legacy's save-anyway-but-report-error
inconsistency — it correctly blocks with a 409 before any insert). The Import flow
(`rizo/src/app/api/employees/import/route.ts:9-14`) has an explicit code comment acknowledging the
divergence from legacy: *"Legacy's duplicate check is weak (first_name+last_name+date_of_birth
only) — we use the stronger PAN/Aadhaar check ... which is strictly safer and consistent with the
rest of this app, not a functional gap against legacy."*

Classify: **INTENTIONAL-LOOKING** — a deliberate, well-documented improvement; no action needed,
though the join/onboard duplicate check (`rizo/src/app/api/employees/join/[id]/onboard/route.ts:38-57`)
additionally checks `esi` and `account_no`, which is broader still — worth confirming this broader
set doesn't produce false-positive blocks in practice (e.g., shared family bank accounts) since
legacy never checked `account_no` for duplicates in this specific flow.

### 2.9 `emp_config` trigger-cascade columns not fully covered by direct-write endpoints (NEEDS HUMAN DECISION)

`code-logic-report.md:2429-2445` documents `emp_config_bi`/`emp_config_au` triggers writing to
`emp_proff` columns for types `SHIFT/MSHIFT/HOLIDAY/SALARY/LEAVE/HIERARCHY/NOTICEPER/DIVISION/
SECTION/GRADE`. The new app's `bulk-policies` endpoint
(`rizo/src/app/api/employees/bulk-policies/route.ts:11-19`) covers 7 of these types
(`SHIFT, LEAVE, HOLIDAY, NOTICEPER, DIVISION, SECTION, GRADE`) plus `SALARY` via a DB function, and
`hierarchy/route.ts` covers `HIERARCHY`. **`MSHIFT` (multishift) has no handler in
`TYPE_CONFIG`** (`rizo/src/app/api/employees/bulk-policies/route.ts:11-19` — the map has no
`MSHIFT` key), so multishift bulk-allocation is not reachable through this endpoint even though
legacy's `EmployeeConfigController` has a dedicated Multishift tab (`user-side-report.md:238,249`
lists "Multishift" among the ten tabs). If a Next.js insert into `emp_config` with `type='MSHIFT'`
happens through some other code path, the app itself no longer performs the corresponding
`emp_proff.multishift` write (since MySQL triggers are presumably still active on the shared DB —
per `code-logic-report.md:2447-2450`, trigger cascade continues to work at the DB layer regardless
of which app writes the row, so this may be a non-issue as long as inserts go through raw SQL that
still hits the trigger; **but** the new app also directly sets `emp_proff` columns itself in the
loop, meaning for the 7 covered types the value is doubly-written by both the app and the trigger —
redundant but not incorrect, EXCEPT for MSHIFT which is simply unhandled).

Classify: **NEEDS HUMAN DECISION** — confirm whether Multishift assignment is planned for a future
pass or was deliberately deprioritized, and confirm the double-write (app writes `emp_proff`
directly AND the DB trigger fires on the same `emp_config` insert) doesn't produce a race/ordering
issue.

---

## Summary

- **Missing features (NOT STARTED):** EmployeeManageController's plan-gated feature-flag endpoint,
  EmployeeUnderController's branch-locked self-service setup, EmployeeEmiController (loan EMI
  processing), ContactsController, BeneficiaryController, DocumentManagerController's plain
  file-upload/allocate library (part b).
- **Dropped business logic (LIKELY BUG):** onboarding completion-percentage algorithm and its
  completion email; weaker create/edit form validation than legacy; employee-status semantics
  narrowed from 3 values to 2 with relabeled meaning.
- **Genuine improvements (INTENTIONAL-LOOKING):** hierarchy collapsed to one authoritative
  representation with documented trigger-awareness; bulk-policy-allocation per-employee response
  bug fixed; duplicate-check strategy strengthened and explicitly documented as an intentional
  divergence.
- **Needs product/human decision:** menu-allocation's relationship to the excluded
  employees/access cluster (possible duplicate write-path); Beneficiary schema-mismatch
  verification before building; MSHIFT bulk-allocation gap and emp_config double-write question.

---

### 2.3 Attendance & Time

# Attendance & Time — Coverage & Fidelity Audit

Scope: legacy Attendance cluster (CakePHP) vs. new Next.js app under `rizo/src/`.

## 1. Coverage table

| Legacy item | Legacy report ref | New app location | Status |
|---|---|---|---|
| **AttendanceRegisterNewController** (register book: `indexneww`, `registerbook`, `chnagestatus`, `verifyAttendance`, `removeAttendance`, `AddLeave`, `editPunch`) — canonical register controller | user-side-report.md:580-606; backend-report.md:734-767; code-logic-report.md:1764-1813 (`insert_update_att_reg`), 2695-2749 (status state machine) | `rizo/src/app/(dashboard)/attendance/register/page.tsx`; `app/api/attendance/register/route.ts` (GET list); `.../process/route.ts` (POST, calls `insert_update_att_reg`); `.../verify/route.ts`; `.../unverify/route.ts`; `.../[registerId]/day/[dayIndex]/route.ts` (cell edit); `.../[registerId]/leave-options/route.ts` | MIGRATED |
| `AttendanceController.php` / `AttendanceregisterController.php` (legacy/dead duplicates of register book) | user-side-report.md:566-578, 608-610 | — (correctly not ported; legacy report itself flags these dead) | N/A (legacy dead code) |
| ESS "my register" (3 competing controllers: `EmpattendanceController`, `EmpattendanceregisterController`, `EmployeeAttendanceregisterController`) | user-side-report.md:710-778 | No employee self-service attendance UI found — `register/route.ts` and all attendance routes require `session.user.userGroup !== 1` → 401 (admin-only) | NOT STARTED (project decision confirmed in code comment: regularisation/route.ts:9 "admin-only per project decision (no employee self-service/hierarchy-manager actor in this pass)") |
| **AttendanceCheckInOutController** (HR reports engine: early-in/out, late-in/out, daily/status reports) | user-side-report.md:614-632; backend-report.md:1095-1121 | `app/api/attendance/checkin-reports/route.ts` + `app/(dashboard)/attendance/checkin/page.tsx` — covers `daily`, `early-in`, `early-out`, `late-in`, `late-out` types via `emp_early_in/out`/`emp_late_in/out` tables | PARTIAL (5 of legacy's ~11 report types covered — misspunch, status-report, break-report, early/late *duration* variants, and Excel/PDF export not found) |
| **AttendanceSetupController** (plan/feature-gating hub, tile UI) | user-side-report.md:635-653; backend-report.md:703-708 | No equivalent found — no `AttendanceSetup`-like feature-tile page/route in `rizo/src` | NOT STARTED (plausibly intentional: plan-gating UI likely superseded by a different nav/entitlement mechanism elsewhere in the app — not verified in this pass) |
| **EditAttendanceController / EditPunchesController** (punch editor: list/edit/save punches, shift-date reassignment, sync) | user-side-report.md:656-707; backend-report.md:791-849 | `app/(dashboard)/attendance/edit-punches/page.tsx`; `app/api/attendance/punches/route.ts` (GET list / POST add); `.../[id]/route.ts` (PUT shift-date move, ports `updateShiftDate`); `.../resync/route.ts` (ports `Syncame`→`device_logs_resync_fn`); `.../sync/route.ts` (ports `Iterateame`→`device_logs_iteration_fn`) | MIGRATED (core flows; `sendmemo()` dead stub correctly not ported) |
| **DailyOvertimeVerifyNewController** (daily OT verify workbench, SBO inline mgmt) | user-side-report.md:805-833; backend-report.md:978-1023 | No dedicated "daily OT verify" grid found; OT coverage is via `OtAttendanceNewController`-equivalent monthly register only (see below); no Scheduled-Break-Off (SBO) equivalent found | NOT STARTED |
| **OtAttendanceNewController** (monthly OT register/approval: `approves`, `getNotApprovedData`, `getApprovedData`, `getProcessedData`) | code-logic-report.md:2751-2769 (§3.2 approval workflow); user-side-report.md:835-843 | `app/(dashboard)/attendance/overtime/page.tsx`; `app/api/attendance/overtime/route.ts` (GET not-approved/approved tabs); `.../overtime/approve/route.ts` (POST, ports `approves()`, calls `calculate_ot_allowance_prc`) | PARTIAL (not-approved/approved tabs covered; no explicit `getProcessedData`/processed-tab equivalent found, though may be reachable via `tab` param extension) |
| **RegularisationController** (`adminindexnew` canonical admin approval path; employee self-service and hierarchy-manager paths) | code-logic-report.md:2771-2797 (§3.3); user-side-report.md:886-915 | `app/(dashboard)/attendance/regularisation/page.tsx`; `app/api/attendance/regularisation/route.ts` (GET list / POST create); `.../[id]/decide/route.ts` (POST approve/reject) | PARTIAL (admin approve/reject path migrated and bug-fixed — see Fidelity §2.5; employee self-service and hierarchy-manager approval paths explicitly out of scope per code comment) |
| **ShiftPlannerController** (`listemployees`, `saveRoster`, verified-month lock) | code-logic-report.md:2798-2821 (§3.4); user-side-report.md (not separately sectioned, covered under cluster) | `app/(dashboard)/attendance/shift-planner/page.tsx`; `app/api/attendance/shift-planner/route.ts` (GET/POST) | MIGRATED |
| **CompoffController** (read-only comp-off earned/used summary) | code-logic-report.md:954-969 | `app/(dashboard)/attendance/comp-off/page.tsx`; `app/api/attendance/comp-off/route.ts` | MIGRATED (generalized from self-scoped ESS view to admin employee-picker — noted intentionally in code comment) |
| **HolidayCalendarController** (holiday + holiday-group CRUD) | code-logic-report.md:972-992 | `app/(dashboard)/setup/holidays/page.tsx`; `app/api/setup/holidays/route.ts` + `[id]/route.ts`; `app/api/setup/holiday-groups/route.ts` + `[id]/route.ts` | MIGRATED — under **Setup** cluster, not Attendance nav (as anticipated by task brief) |
| **DayTimeProcedureController** (shift/working-day-time-procedure master + exceptions) | code-logic-report.md:995-1021 | `app/api/setup/shifts/route.ts` + `[id]/route.ts` (referenced by `shift-planner/route.ts` joins to `working_day_time_procedures`) | MIGRATED (shift master CRUD found under Setup cluster); shift-*exception* sub-feature (`saveException`/`getExceptions`/`deleteException`/`toggleExceptionStatus`) not found in new app — likely NOT STARTED for that sub-piece |
| **MobileLocationUpdateController** (GPS/mobile tracking report + map) | code-logic-report.md:1023-1044 | No `mob_user_tracking`/`MobileLocation` reference found anywhere in `rizo/src` | NOT STARTED |
| **DailyActivityController** (site/project daily-activity tracking — distinct domain) | backend-report.md:940-976 | Out of requested scope for this cluster's new-app search (not one of the 9 named legacy controllers in the task); no attendance-cluster route touches `daily_activity`/site tables | NOT STARTED / out of scope |
| **EmployeeAttendanceUploadController** (bulk attendance Excel upload) | user-side-report.md:748-767 | Not found among the listed attendance routes/pages in this pass | NOT STARTED (not in the explicitly-scoped file list; not verified elsewhere in the app) |
| **ScheduledBreakOffController** (SBO day-off management) | code-logic-report.md:937-951 | No `emp_shift_planner`-adjacent SBO route found | NOT STARTED |

## 2. Fidelity check findings

### 2.1 Half-day / late-arrival threshold calculation — MOST IMPORTANT CHECK

**Verdict: correctly NOT reimplemented in TypeScript — delegated to the same MySQL stored procedure/trigger, which is the right call given the dual-DB architecture.**

- Legacy: `code-logic-report.md:2725-2749` (§3.1) — the P/P vs P/A vs A/A determination happens entirely inside the `device_attandance_bi` BEFORE INSERT trigger (`schema/mypayrol_trial.sql:41006-41864`), using per-shift-policy `minuts_calc_perday`/`minutes_per_half`/`strict_monitorings` columns from `working_day_time_procedures`. No PHP constant anywhere implements "N minutes late = half day."
- Legacy day-status build algorithm (priority cascade Weekoff→Holiday→Leave→Attendance→NA→default) lives in the `insert_update_att_reg` stored procedure — `code-logic-report.md:1764-1813` (§18).
- New app: `rizo/src/app/api/attendance/register/process/route.ts:30` — `await pool.query('CALL insert_update_att_reg(?, ?, ?, ?, @poutput)', ...)`. The route's own comment (lines 8-12) explicitly states: "this proc does all the real per-day computation ... we just trigger it and read the result message."
- Punch ingestion into `device_attandance` (which fires the `device_attandance_bi` trigger) is also delegated: `rizo/src/app/api/attendance/punches/route.ts:65-75` (raw `INSERT INTO device_attandance`), `rizo/src/app/api/attendance/punches/sync/route.ts:25-27` (`CALL device_logs_iteration_fn`), `.../resync/route.ts:23-26` (`CALL device_logs_resync_fn`).
- `rizo/src/lib/attendance.ts` — read in full; contains only UI-color logic (`getCellColor`, mirrors legacy's `.ctp` JS palette), period-boundary lookup via `att_start_end_fn`, leave-option/balance lookups via `leave_balance_inthe_year_fn`, and `isLeaveAlreadyApplied`. **No half-day/duration-threshold arithmetic of any kind exists in this file or anywhere else searched in `rizo/src`.**

**Classification: INTENTIONAL-LOOKING.** This is the correct architectural choice, not a gap — the legacy MySQL schema/procs/triggers are retained (per the dual-DB migration strategy in project memory), so calling `CALL insert_update_att_reg(...)` reuses the exact same trigger-driven computation rather than risking divergent reimplementation. The one residual risk (not a bug in the Next.js code, but a deployment/ops concern): this only works correctly if the target company database still has the `device_attandance_bi` trigger and `insert_update_att_reg`/`device_logs_iteration_fn`/`device_logs_resync_fn`/`time_duration_check`/`ot_duration_register_date`/`calculate_ot_allowance_prc`/`leave_transaction_prc` procedures/functions deployed and unmodified — **NEEDS HUMAN DECISION**: confirm these are provisioned in every company's DB the new app will point at (multi-tenant `getCompanyPool()` architecture), since nothing in the TypeScript layer would catch a missing/renamed stored routine except a runtime SQL error.

### 2.2 Attendance status vocabulary

**Verdict: matches legacy exactly, not simplified.**

- Legacy vocabulary (`code-logic-report.md:2695-2723`, §3.1): `$nonLeave = ['P','A','NA','WO','HO','LOP']` plus any other code treated as a leave code; half-day pairs joined by `/` (e.g. `P/A`, `HO/WO`, `NA/HO`).
- New app: `rizo/src/lib/attendance.ts:156-160` — `STANDARD_CODES = new Set(['P','A','WO','HO','NA','LOP',''])`; `isLeaveCode()` returns true for anything not in that set — identical logic to legacy's `$nonLeave` inversion. `getCellColor()` (lines 9-28) explicitly handles `WO/WO`, `HO/HO`, `P/P`, `LOP/LOP`, generic `parts.some(p => p.includes('LOP'))` half-pair detection — same half-day-pair model as legacy.
- `ATTENDANCE_LEGEND` (lines 30-35) is a UI-only subset (P/HO/WO/LOP) for a legend key, not the actual vocabulary enforcement — the enforcement (`STANDARD_CODES`/`isLeaveCode`) covers the full set.

**Classification: INTENTIONAL / faithful port.** No discrepancy found.

### 2.3 `isdelete`-as-verification-flag semantics

**Verdict: preserved literally, including the inverted polarity and the original column/value name — not renamed.**

- Legacy: `code-logic-report.md:2826-2833` (§4) — `isdelete='Y'` = unverified/pending, `isdelete='N'` = verified/locked; report explicitly recommends renaming this to `verification_status`/`is_verified` in migration and **not** carrying the polarity forward literally.
- New app: `rizo/src/app/api/attendance/register/route.ts:29` — `const isdelete = statusParam === 'verified' ? 'N' : 'Y';` (same polarity). `verify/route.ts:29,44` — selects `WHERE ... isdelete = 'Y'`, then `UPDATE ... SET isdelete = 'N'`. `unverify/route.ts:27,54` — inverse. `[dayIndex]/route.ts:51` — `if (reg.isdelete === 'N') return ... 409 'verified/locked'`. `shift-planner/route.ts:58-62,88-96` and `regularisation/route.ts:76-82` also both directly query `attendance_register.isdelete` with the same polarity to gate edits.

**Classification: NEEDS HUMAN DECISION.** The report's explicit prior recommendation (rename + un-invert) was not followed — the new app keeps the exact legacy column name and inverted `'Y'`=unverified/`'N'`=verified semantics, reproduced identically across at least 5 route files. This is defensible if the DB schema itself was retained as-is (consistent with the dual-DB/stored-procedure-reuse strategy seen in §2.1 — `insert_update_att_reg` and `saveRoster`'s lock-check both read this same column, so an app-side rename would require a compatibility view or dual-write). Every call site does carry an inline comment naming the legacy behavior being ported, which mitigates the "misread as soft-delete" footgun for future maintainers reading the code — but no `is_verified`/`verificationStatus` field or computed boolean is exposed at the API-response layer either (e.g., `register/route.ts`'s response includes `status: statusParam` string, not a semantically-named boolean field on each row) which is a reasonable mitigation. Flag for the team: confirm this was a deliberate "don't fork the schema" call, not an oversight.

### 2.4 FIELD1..FIELD32 denormalized day-column design

**Verdict: NOT normalized — the new app still reads/writes the legacy 32-column EAV layout directly.**

- Legacy: `code-logic-report.md:2834-2840` (§4) explicitly recommends normalizing to `attendance_day(register_id, day_number, status_code)`.
- New app: `rizo/src/lib/attendance.ts:56-60` — `FIELD_COLUMNS = Array.from({length: 32}, (_, i) => 'FIELD'+(i+1))`; `fieldsToArray()` maps a raw DB row's 32 columns into an array. `rizo/src/app/api/attendance/register/route.ts:35` — SQL literally selects `FIELD1, ar.FIELD2, ... ar.FIELD32`. `[dayIndex]/route.ts:44,92-95` — `const fieldCol = 'FIELD'+dayIdx; ... UPDATE attendance_register SET ${fieldCol} = ? WHERE registerid = ?` (dynamic column name interpolated into SQL, parameterized only for the value — not a SQL-injection risk since `dayIdx` is validated as `1-32` integer at line 28, but still structurally a 32-column EAV write). `comp-off/route.ts:43` builds an `OR`-chain across all 32 `FIELD` columns (`FIELD1 = 'COFF' OR FIELD2 = 'COFF' OR ...`) to search for comp-off codes — a direct port of the legacy pattern the report flagged as needing normalization.
- The API layer *does* present a normalized view to the frontend (`days: AttendanceDay[]` array in the JSON response, `AttendanceGrid.tsx:6-13`), so the **API contract** is already day-row-shaped — but the underlying **storage** is unchanged.

**Classification: LIKELY BUG risk-tier / NEEDS HUMAN DECISION**, not because the current code is wrong (it faithfully mirrors legacy and works), but because: (a) it forgoes the architectural improvement the legacy report explicitly recommended, (b) the `comp-off` route's 32-way `OR` scan is O(columns) query complexity that would trivially become a single `WHERE status_code='COFF'` row-filter on a normalized table, and (c) if the underlying `attendance_register` table is genuinely shared with the legacy app in production during a migration window (dual-DB strategy), normalizing was never really an option here — so this is consistent with, and likely required by, the "reuse legacy DB + stored procs" decision already confirmed in §2.1. Flag for the team to confirm this is accepted as permanent (not just a migration-phase shim), since the task brief called this out as "a likely and reasonable architectural improvement" that was evidently not taken.

### 2.5 Overtime and Regularisation approval-state semantics

**Verdict: both correctly preserved, with one confirmed bug-fix improvement over legacy.**

- **OT two-state (`is_verified` Y/N)**: Legacy `code-logic-report.md:2751-2769` (§3.2) — `emp_ot_master.is_verified` flips `N`→`Y` on `approves()`, no reject/reopen state. New app: `rizo/src/app/api/attendance/overtime/approve/route.ts:38` (`UPDATE emp_ot_master SET set_duration = ?, is_verified = 'Y'`) and insert-path line 44 (`'Y'` on insert-if-missing) — same two-state model, same `calculate_ot_allowance_prc` call at line 49 matching legacy's `calculate_ot_allowance_prc` call (`code-logic-report.md:2760`). `overtime/route.ts:38` (`eot.is_verified = ?` filter for approved/not-approved tabs) matches legacy's `Toapproved()`/`getNotApprovedData()`/`getApprovedData()` split (`code-logic-report.md:2766-2769`).
- **Regularisation three-state (P/A/R)**: Legacy `code-logic-report.md:2771-2789` (§3.3) — `approved` column: `'P'`(pending)/`'A'`(approved)/`'R'`(rejected), gated by `status` (1 active / 0 archived). New app: `rizo/src/app/api/attendance/regularisation/[id]/decide/route.ts:38` (`if (reg.approved !== 'P' || reg.status !== 1) return 409`), line 46 (`SET approved='R', status=0` on reject), line 97 (`SET approved='A'` on approve, status left at 1) — exact match to legacy's state transitions documented at `code-logic-report.md:2779-2783`.
- **Bug-fix noted in code comment**: `[id]/decide/route.ts:7-10` states legacy's live `bulkupdate($adminUpdate=true)` "skipped the 'approved=P AND status=1' guard entirely and its Reject branch didn't check for already-processed rows, allowing double-processing" — the new route's explicit `if (reg.approved !== 'P' || reg.status !== 1)` guard (line 38) closes that gap. This is a deliberate, documented improvement, not a silent behavior change.
- **Leave-in-flight guard preserved**: Legacy's `isLeaveAlreadyApplied()` cross-domain rule (`code-logic-report.md:2790-2796`, "attendance status cannot be hand-edited while a leave request is in flight") is ported at `rizo/src/lib/attendance.ts:139-154` and called from both `[dayIndex]/route.ts:57` (register cell edit) and `regularisation/route.ts:84` (new regularisation submission) — correctly applied to both call sites the legacy report identified needing it.

**Classification: INTENTIONAL-LOOKING** (faithful port + documented, deliberate bug fix). No discrepancy.

### 2.6 SQL-injection surfaces closed (secondary finding, worth noting)

Not part of the explicit checklist but surfaced repeatedly in code comments and worth flagging as a positive fidelity note: legacy's `RegularisationController::updateStatus()` built raw `UPDATE ... SET status = $status` from unescaped `$_POST` (`user-side-report.md:907`), and `insert_func()`/`savepunch()` forwarded raw `$_POST` blobs to `save()` (`user-side-report.md` EditPunches section). New app's equivalents (`punches/route.ts:8-11`, `regularisation/[id]/decide/route.ts:11-13`) explicitly narrow the request body to named fields and use parameterized `pool.execute()` throughout — confirmed not just by absence of string concatenation but by inline comments acknowledging the legacy vulnerability was intentionally not replicated.

### 2.7 Gaps not covered by the explicit checklist but worth flagging

- **`sendmemo()` dead stubs**: legacy had non-functional hardcoded-recipient email stubs in `EditAttendanceController`, `EditPunchesController`, `RegularisationController` (`user-side-report.md:670,704,912`). None ported — correct, since the report itself confirms they were dead/test code never wired to real events.
- **No employee self-service (ESS) attendance surface**: every attendance API route checked requires `session.user.userGroup !== 1` → 401. Legacy had (confusingly) three competing ESS register controllers plus ESS punch-edit (`EmpeditpunchesController`) and ESS-scoped Regularisation (`index()`/employee self-service path). None of this is present in the new app per the explicit code comment in `regularisation/route.ts:9`. This is a large scope reduction from legacy (admin/HR only) — **NEEDS HUMAN DECISION**: confirm ESS attendance/regularisation self-service is either out of scope permanently or simply not yet built (ordering/phasing question), since legacy employees could view their own register and submit regularisation requests themselves.

---

### 2.4 Company / Organization Setup

# Company / Organization Setup — Coverage & Fidelity Audit

Scope: legacy `Controller/{Company,CompanyNew,CompanySetup,Branch,Department,Designation,Division,Section,
Grade,Grades,GradesNew,Category,CategoryMaster,Vertical,DbConfig,Template,Uniform,Bank}Controller.php`
vs new app `rizo/src/app/(dashboard)/setup/**` + `rizo/src/app/api/{company,setup}/**`.

Legacy sources: `reports/user-side-report.md` §8 (lines 2552-2758), `reports/backend-report.md` §2.7
(lines 3052-~3400), `reports/code-logic-report.md` §7.7 (lines 4174-4500).

---

## 1. Coverage table

| Legacy item | Legacy report ref | New app location | Status |
|---|---|---|---|
| `CompanyController` (profile/compliance/policy hub) | user-side-report.md:2576 ("## 1. CompanyController"); backend-report.md:3052-3091 | `app/(dashboard)/setup/company/page.tsx` + `app/api/company/route.ts` | PARTIAL — only `comp_contact_info` fields (name/address/city/state/pincode/phone/email/website) are ported. `ComplianceInfo` (CIN/PAN/TAN/PF/ESI/PT), the Attendance-Cycle/Payroll-day fields (`db_config.attendance_format/attendance_date/payroll_type`, now split off into `attendance-config`), logo upload, and the generic `PolicyInfo` key/value store are all absent from this endpoint (attendance/payroll config re-lands separately in `setup/attendance-config`, see §2 below; logo/compliance/policy have no new-app equivalent found). |
| `CompanyNewController` (dead duplicate of CompanyController) | user-side-report.md:2605; backend-report.md:3091-3095; code-logic:4174 (flag #1 doesn't apply, cross-ref only) | — | NOT STARTED (correctly — legacy source itself flags this as dead/orphaned cruft; no action needed) |
| `CompanySetupController` (plan/feature gate + Razorpay payment, NOT a wizard) | user-side-report.md:2613-2634; backend-report.md:3095-3115 | `lib/company-config.ts` (partial conceptual analog, see §2 finding 5) | NOT STARTED — no plan/subscription/payment screen, no Razorpay integration, no `feature_key`/`plan_id` gating exists anywhere in `rizo/src` (confirmed via search — no `razorpay`, `plan_id`, or `CompanySetup` hits outside company-config.ts, which is a different, narrower mechanism). |
| `BranchController` | user-side-report.md §4 "Branch" bullet; backend-report.md:3115-3178 | `app/(dashboard)/setup/branches/page.tsx`, `app/api/setup/branches/route.ts`, `[id]/route.ts` | PARTIAL — basic CRUD present, but branch_code auto-generation logic, the `fin_year` auto-create-two-rows-per-branch side effect, and the control-DB `company_branches` mirror write are all absent (see §2 finding 1). |
| `DepartmentController` | user-side-report.md §4; backend-report.md:3178-3195 | `app/(dashboard)/setup/departments/page.tsx`, `app/api/setup/departments/route.ts`, `[id]/route.ts` | MIGRATED (same no-guard delete behavior as legacy, see §2 finding 3) |
| `DesignationController` | user-side-report.md §4; backend-report.md:3195-3210 | `app/(dashboard)/setup/designations/page.tsx`, `app/api/setup/designations/route.ts`, `[id]/route.ts` | MIGRATED (misnamed-method bug from legacy is naturally gone since routes are RESTfully named; no-guard delete preserved) |
| `DivisionController` (guarded delete) | user-side-report.md §4; backend-report.md:3210-3225 (`emp_vertical` field-name mismatch flag) | `app/api/setup/divisions/route.ts` only — **no `page.tsx`** found under `setup/` | PARTIAL / API-only — GET-only lookup endpoint (feeds a dropdown elsewhere), no POST/PUT/DELETE, no admin UI to create/edit/delete divisions at all. Guarded-delete logic (and its legacy field-mismatch bug) is moot since delete isn't implemented. |
| `SectionController` (guarded delete) | user-side-report.md §4; backend-report.md:3225-3240 | `app/api/setup/sections/route.ts` only — **no `page.tsx`** | PARTIAL / API-only — same as Division: GET-only, no CRUD UI, no guarded delete. |
| `GradesController` (canonical grade CRUD, guarded delete) | user-side-report.md §4 "Grades" bullet; backend-report.md:3253-3290 | `app/(dashboard)/setup/grades/page.tsx`, `app/api/setup/grades/route.ts`, `[id]/route.ts` | PARTIAL — CRUD is present and correctly uses `grade_pkey` (not `id`) as the model's real PK, matching legacy's schema mismatch finding. But the guarded-delete check (block if `EmployeeProfessionalDetails.emp_grade` references the grade) is **not implemented** — see §2 finding 3. |
| `GradeController` (misleadingly named — actually Holiday Calendar) | user-side-report.md §4 "GradeController" bullet; backend-report.md:3178-3210 (Grade Controller table) | `app/(dashboard)/setup/holidays/**` (holiday-calendar concept), `app/api/setup/holidays*`, `app/api/setup/holiday-groups*` | MIGRATED (as Holidays, correctly separated from Grades — the new app does NOT carry over the legacy naming confusion; holiday-calendar and grade-master are cleanly split into `setup/holidays` and `setup/grades` respectively) |
| `GradesNewController` (3rd grade CRUD, dead duplicate w/ category+pay_scale) | backend-report.md:3290-3305 | — | NOT STARTED (correctly — legacy source flags as unreferenced duplicate; category linkage is dead anyway per Category finding below) |
| `CategoryController` (targets nonexistent `category` table / `grade.category_fkey`) | user-side-report.md §4 "Category" bullet; backend-report.md:3305-3335; code-logic-report.md:4174 §1 "category table does not exist" | — | NOT STARTED (correctly — see §2 finding 4: no Category feature exists anywhere in `rizo/src`, consistent with the table not existing) |
| `CategoryMasterController` (separate `category_master` table, generic lookup/tagging) | user-side-report.md §4 "CategoryMaster" bullet; backend-report.md:3335-3350 | — | NOT STARTED (out of Company-Setup cluster proper per legacy report's own classification — a generic masters feature, not org-structure) |
| `VerticalController` (orphaned — no view directory, no inbound links in legacy) | user-side-report.md §4 "Vertical" bullet; backend-report.md:3350-3365 | — | NOT STARTED (correctly — legacy confirms this was already dead/unused before migration) |
| `DbConfigController` (13-step onboarding wizard, orphaned from routing) | user-side-report.md "Company Setup Wizard" section (2634-2758); backend-report.md:3365-3450; code-logic-report.md:4174 §2/§3c, §"3c" | — (no wizard) — but its constituent seed steps are individually covered: designation/department creation (MIGRATED, see above), holiday-group creation (MIGRATED via `setup/holidays` + `setup/holiday-groups`, directly-authored rather than copy-from-template), shift/day-time-procedure creation (MIGRATED via `setup/shifts`) | PARTIAL — no unified first-run/onboarding wizard exists in the new app (no `wizard`, `onboard*`, or `DbConfig`-equivalent route found under `app/` except an unrelated employee-join onboarding flow). Individual building blocks the wizard used to seed (dept/design, holidays, shifts) now exist as normal always-available setup screens instead of a guided first-run flow — see §2 finding 2. |
| `TemplateController` (dead duplicate of Bank, mis-wired `$uses`) | user-side-report.md §5; backend-report.md:3450-3465 | — | NOT STARTED (correctly — confirmed dead in legacy) |
| `UniformController` (uniform inventory/allocation — out of org-setup scope per legacy report itself) | user-side-report.md §6; backend-report.md:3465-3480 | — | NOT STARTED (out of scope per legacy report's own classification; not part of this audit's expected surface) |
| `BankController` | user-side-report.md §4 "Bank" bullet; backend-report.md §"BankController.php" | — (no `setup/banks` or `api/setup/banks` found) | NOT STARTED — no bank-master CRUD found anywhere in `rizo/src` (searched; only payroll/salary bank-account-number fields exist elsewhere, not the `bank` lookup-table CRUD). |
| Financial Year (`FinancialYear`/`fin_year`, tab inside `Company/index.ctp`, auto-created by Branch save in legacy) | user-side-report.md:2550 (AJAX list in §1 endpoint list); code-logic-report.md §3b | `app/(dashboard)/setup/financial-year/page.tsx`, `app/api/setup/financial-year/route.ts`, `[id]/route.ts` | MIGRATED — reimplemented as an explicit, independently-manageable CRUD screen rather than an automatic branch-save side effect (see §2 finding 1). |
| Attendance/Payroll cycle config (`db_config.attendance_format/attendance_date/payroll_type`, part of `CompanyController::savecompanysetup`) | backend-report.md:3072-3091 (CompanyController savecompanysetup row) | `app/(dashboard)/setup/attendance-config/page.tsx`, `app/api/setup/attendance-config/route.ts` | MIGRATED — split into its own dedicated screen with real server-side validation (`attendance_format` must be A/B, `attendance_date` 0-31 integer) that legacy never had. |
| Shift / working-day-time-procedure master (`working_day_time_procedures`, seeded via `DbConfig::save_shift`/copy-from-template in legacy) | code-logic-report.md §"3c" step 8 | `app/(dashboard)/setup/shifts/**`, `components/setup/ShiftForm.tsx`, `app/api/setup/shifts/route.ts`, `[id]/route.ts` | MIGRATED — full bespoke CRUD (not the generic `SetupCrudPage`, justified given ~50 shift columns) directly authors shifts rather than copying a control-DB template row, a cleaner design than legacy's wizard-only seeding path. |
| Notice Period (own tab in legacy `Company/index.ctp`, full CRUD via `NoticePeriodController`) | user-side-report.md:2550 (AJAX endpoint list, `NoticePeriod/listNotice`+`/form`+`/deleteNotice`) | `app/api/setup/notice-periods/route.ts` only | PARTIAL — downgraded from full CRUD in legacy to a GET-only lookup list in the new app; no admin UI to add/edit/delete notice periods. |
| Asset types, leave-policy groups, nationalities, salary structures (lookup dropdowns feeding other modules, not standalone legacy Company-Setup tabs per se) | n/a — not primary Company-Setup controllers in legacy report scope | `app/api/setup/asset-types`, `leavepolicy-groups`, `nationalities`, `salary-structures` (all GET-only, no page.tsx) | NEW / PARTIAL — these are read-only lookup APIs with no legacy-cluster CRUD counterpart traced in this scope; flagged API-only per the task brief. |

---

## 2. Fidelity check findings

### Finding 1 — Branch save no longer auto-generates `branch_code`, auto-creates `fin_year` rows, or mirrors to control DB
- Legacy: `BranchController::savebranch()` (code-logic-report.md §1 "branches DB triggers..." and §3b; backend-report.md:3115-3178) computed `branch_code` from `substr($branch_name,0,3).strtotime("now")`, but this was **silently overwritten by the `branches_bi` trigger** (`concat(company_code, 0, running_count)`). The same method also inserted a mirror row into `mypayrol_control_db.company_branches` and created two `fin_year` rows (Financial + Leave year) per branch.
- New app: `rizo/src/app/api/setup/branches/route.ts:27-40` inserts a branch with `branch_code` taken directly from the request body (`body.branch_code ?? ''`) — no server-generated code, no trigger equivalent (there are no DB triggers in the target MySQL either, per the legacy report's own migration note: "Next.js/ORM layer must decide whether to replicate the trigger's branch_code-generation... logic"). No control-DB write occurs (`rizo/src/app/api/setup/branches/route.ts` has no cross-DB call at all — reasonable, since the new app is presumably single-DB or has abandoned the control-DB mirror concept, but this wasn't verified against `lib/db.ts`). `fin_year` creation is **not** wired to branch save — it is instead a fully separate, manually-driven CRUD screen (`rizo/src/app/(dashboard)/setup/financial-year/page.tsx`, `rizo/src/app/api/setup/financial-year/route.ts:28-64`) where the admin explicitly picks a branch from a dropdown and a Year/Leave type.
- Classification: **INTENTIONAL-LOOKING (net improvement) with one gap**. Decoupling fin_year creation from branch save and making branch_code freely admin-supplied resolves the exact three-way race the legacy report flagged (PHP `BranchController` vs PHP `DbConfigController` vs MySQL trigger all racing to set `branch_code`). However, nothing enforces `branch_code` uniqueness or a generation convention in the new API (`rizo/src/app/api/setup/branches/route.ts:27-40` — no duplicate check, no server-side derivation), so a duplicate/blank `branch_code` from two different admins is now possible where legacy's trigger at least guaranteed uniqueness via a running counter. **NEEDS HUMAN DECISION**: confirm whether `branch_code` should get a uniqueness constraint/generation scheme in the new schema.

### Finding 2 — No onboarding wizard exists; legacy's orphaned `DbConfigController` is not replaced by an equivalent, but its seed *targets* are now standing CRUD screens
- Legacy: `DbConfigController` (13-step wizard: welcome → config → designation_departments → save_holidays → policy → save_policies → leave_heads → salary_policy(stub) → holidays → emp_upload(stub) → load_config(stub) → emp_login(stub)/login_cred → completed_setup / SetupComplete) was confirmed orphaned from `routes.php` and any live menu (user-side-report.md:2634-2637; code-logic-report.md §3c). Legacy report explicitly calls this "a template for onboarding-flow design, not a working reference implementation" and flags several steps as unimplemented stubs.
- New app: searched `rizo/src/app` for `wizard`, `onboard`, `DbConfig`, `first-run` — the only hit is an unrelated employee-record onboarding flow (`app/(dashboard)/employees/join/[id]/onboard`, `app/api/employees/join/[id]/onboard`), which is about onboarding an *employee*, not a new *tenant/company*. There is no multi-step "set up your new company" flow anywhere in `rizo/src`.
- Classification: **NEEDS HUMAN DECISION**. Company/tenant setup is not addressed as a first-run experience at all in the new app — each of departments, designations, holidays, shifts, financial-year, attendance-config exists as an always-available standing settings screen (a reasonable "no wizard needed, just always-editable settings" design choice for a system with an existing seed/clone-DB provisioning process), but if the product still needs a guided "day-1 setup" experience for brand-new tenants (the closest legacy intent, however broken/orphaned), nothing in the current codebase serves that purpose. Given `DbConfigController` was already dead in legacy, not porting it is defensible, but the *underlying product need* (a new-tenant setup checklist) may still be open and should be confirmed with the product owner rather than assumed resolved.

### Finding 3 — Guarded-delete inconsistency: legacy's Division/Section/Grade guards are NOT present in the new app; Branch/Department (unguarded in legacy) remain unguarded — net result is now uniformly "no guard everywhere," not fixed, not faithfully replicated either
- Legacy: Division/Section/Grade blocked deletion when employees were assigned (`emp_vertical`/`emp_sep_priv`/`emp_grade` checks against `EmployeeProfessionalDetails`); Branch/Department did not (code-logic-report.md §3a).
- New app:
  - `rizo/src/app/api/setup/grades/[id]/route.ts:26-40` — `DELETE` does a bare `UPDATE grade SET status = 0 WHERE grade_pkey = ?` with **no employee-assignment check**.
  - `rizo/src/app/api/setup/departments/[id]/route.ts:26-40` and `rizo/src/app/api/setup/designations/[id]/route.ts:26-40` and `rizo/src/app/api/setup/branches/[id]/route.ts:26-40` — same bare unconditional soft-delete pattern, consistent with legacy's (already-unguarded) behavior for these three.
  - Division and Section have **no DELETE route at all** (`rizo/src/app/api/setup/divisions/route.ts` and `sections/route.ts` are GET-only) — so their legacy guard is trivially "preserved" only in the sense that deletion isn't possible through the API at all yet.
- Classification: **LIKELY BUG / NEEDS HUMAN DECISION**. The new app did not consciously resolve legacy's flagged inconsistency (code-logic-report.md's own recommendation: "the Next.js port should decide deliberately... rather than blindly replicating the inconsistency"). Instead, Grade's guard was silently dropped (a **regression** — Grade could previously not be deleted while in use; now it can, causing dangling `emp_grade` references with no relational integrity to catch it), while Division/Section's guard question is moot because those entities have no delete capability yet. This should be flagged to a human: either add employee-assignment guards consistently across Branch/Department/Designation/Division/Section/Grade (the legacy report's own recommendation), or explicitly decide guards aren't needed (e.g., if the new schema will add real FK constraints instead — see Finding 6).

### Finding 4 — No Category feature exists in the new app, correctly reflecting `CategoryController`'s dead-code status
- Legacy: `CategoryController`/`Model/Category.php` target a `category` table and `grade.category_fkey` column that do not exist in any of the three legacy schema dumps (code-logic-report.md §1, "category table does not exist").
- New app: no `category`, `Category`, or `category_fkey` reference found anywhere in `rizo/src` (searched `app/api/setup`, `app/(dashboard)/setup`, and general grep for "categor" — only unrelated hits like `asset_type`/`category_master`-adjacent concepts were absent too, confirming zero Category surface).
- Classification: **INTENTIONAL-LOOKING (correct)**. This is the expected/correct outcome — the legacy feature was already broken/orphaned pointing at nonexistent tables, and the new app simply has no equivalent, which is the right call. No action needed.

### Finding 5 — `company-config.ts` is not a replacement for legacy's plan-gating; it's a different, narrower hardcoded-company-code feature-flag mechanism, and legacy's plan/subscription/payment flow (`CompanySetupController`) has no new-app equivalent at all
- Legacy: `CompanySetupController` implements subscription-plan feature gating sourced from **`central_control.plan`** (control DB, confirmed typo default `'standerd'`) plus `comp_contact_info.plan` (read in `CompanyController::index()`) — i.e., the "four separate unreconciled plan columns" fragmentation already documented elsewhere in the code-logic report, plus a full Razorpay payment integration with hardcoded test API keys and a raw-SQL-interpolation SQLi surface (`CompanySetupController.php:154-158`).
- New app: `rizo/src/lib/company-config.ts:1-21` is explicitly commented "Per-company feature flags extracted from legacy hard-coded if-else chains... Long-term: replace with plan_feature records in control DB." It hardcodes company-code allowlists (`['GLET','ABSG']`, `['ABSG','GLET','GAAR']`, etc.) for five unrelated toggles (`showHierarchyDashboard`, `useAbsHierarchyBranch`, `siteManagementEnabled`, `incrementNotifications`, `siteEndDateNotifications`) — none of which correspond to `CompanySetupController`'s `feature_key='company'` plan-feature concept. No plan_id, no subscription tier, no payment flow, no `Features`/`PlanFeature` table read anywhere in `rizo/src` (searched, zero hits for `razorpay`, `plan_id`, `PlanFeature`).
- Classification: **NEEDS HUMAN DECISION** on two separate points: (a) `company-config.ts` itself is simpler than legacy's four-column fragmentation in the narrow sense that it's centralized in one file rather than scattered if-else chains across controllers, so it does not repeat that specific fragmentation bug — but it is still the same fundamentally fragile pattern (hardcoded company-code allowlists baked into source, requiring a code deploy to onboard a new tenant into any of these five behaviors), and its own comment acknowledges this is a stopgap, not a real fix. (b) Separately and more significantly: legacy's actual plan/subscription/payment gating (`CompanySetupController`) has **no migrated equivalent whatsoever** — if the product still needs subscription-tier feature gating and paid upgrades, that entire capability is presently unimplemented in the new app, not merely simplified.

### Finding 6 — Branch→Department→Designation hierarchy remains flat/unenforced in the new app, same as legacy
- Legacy: `department`/`designation`/`division`/`section`/`grade`/`bank` are flat lookup tables with no FK columns to each other or to `branches` (code-logic-report.md §1, "No branch → department → designation → division → section hierarchy in the DB").
- New app: confirmed by reading the actual SQL in every setup API route — `rizo/src/app/api/setup/departments/route.ts:27-30` inserts into `department (dept_name, dept_code, status)` with **no branch_fkey or company/branch scoping column at all**; same for `rizo/src/app/api/setup/designations/route.ts:27-30` (`designation (desig_name, desig_code, status)`), `rizo/src/app/api/setup/grades/route.ts:27-30` (`grade (grade_name, grade_code, status)`). None of these tables carry a foreign key to `branches`, to each other, or to any parent org-unit — the new app queries the exact same flat schema legacy used (table/column names are unchanged: `department`, `designation`, `division`, `section`, `grade`).
- Classification: **INTENTIONAL-LOOKING, but unresolved from a product standpoint**. The new app makes no attempt to introduce relational integrity for the org hierarchy the UI still visually implies (each is still its own top-level "Setup" nav item, e.g. `setup/branches`, `setup/departments`, `setup/designations`, `setup/grades` as siblings, mirroring legacy's tab structure). This is a faithful **replication of the flatness**, not an improvement — the legacy report's own summary flag #3 ("whatever hierarchy the UI implies must be a purely application-level design decision for the new system, not something being carried over from the legacy schema") was not acted on. **NEEDS HUMAN DECISION**: confirm whether introducing real FK-based hierarchy (branch_fkey on department, dept_fkey on designation, etc.) is in scope for this migration or intentionally deferred.

### Finding 7 — Financial Year screen correctly resolves legacy's `vattr1` dual-purpose ambiguity, and adds a real DB-level uniqueness guard legacy lacked
- Legacy: `fin_year.vattr1` (0=leave year, 1=financial year) was an undocumented dual-purpose flag inferable only from controller code, with a compound unique key `(company_code, branch_code, fin_year, Year_status, vattr1, status)` (code-logic-report.md "Schema quirks").
- New app: `rizo/src/app/(dashboard)/setup/financial-year/page.tsx:43-53` surfaces `vattr1` explicitly as a labeled "Type" select (`Leave` / `Financial`), and `rizo/src/app/api/setup/financial-year/route.ts:55-63` catches `ER_DUP_ENTRY` and returns a clean 409 with a human-readable message ("A financial year already exists for this branch/status/type") rather than the silent/undocumented behavior implied by legacy's raw-SQL insert/update pattern.
- Classification: **INTENTIONAL-LOOKING (improvement)**. No further action needed; worth calling out as a genuine fidelity win — the new app made an implicit legacy schema convention explicit and user-facing.

### Finding 8 — `SetupCrudPage.tsx` is a genuine generic, reusable CRUD component — an architectural strength
- `rizo/src/components/setup/SetupCrudPage.tsx` is parameterized by `apiPath`, `fields` (with types text/date/number/select/checkbox), `primaryKey`, `displayKey`, `columns`, and optional `queryParams`, and implements list/create/edit/delete with a shared modal form and inline delete-confirm UX. It is reused as-is (not copy-pasted) by Branches, Departments, Designations, Grades, Holiday Groups, Holidays, and Financial Year (`rizo/src/app/(dashboard)/setup/branches/page.tsx`, `departments/page.tsx`, `designations/page.tsx`, `grades/page.tsx`, `holidays/page.tsx`, `financial-year/page.tsx`).
- Classification: **INTENTIONAL-LOOKING (improvement)**. This directly resolves the exact problem the legacy report flagged as "one shared pattern" reimplemented six-plus times with copy-paste inconsistencies (return-instead-of-echo bugs in some but not all, tautology bugs in Grade/Category's exists-checks, misnamed methods like `DesignationController::deleteDepartment`). The new app has one implementation instead of N copies. Worth noting as a positive migration-quality signal, though it also means any bug in `SetupCrudPage.tsx` itself would now affect all six+ entities at once (a tradeoff, not a flaw).

---

## Summary

- **Solidly migrated with net improvements**: Departments, Designations, Grades (CRUD only — see delete-guard regression), Holidays/Holiday-Groups, Shifts, Financial Year, Attendance-Config. The shared `SetupCrudPage` component is a real architectural win over legacy's six-plus copy-pasted CRUD controllers.
- **Partial / API-only, no admin UI**: Divisions, Sections, Notice Periods (downgraded from full CRUD in legacy to read-only lookups), Asset Types, Leave-Policy Groups, Nationalities, Salary Structures.
- **Correctly not started** (legacy itself flagged as dead/orphaned): CompanyNewController, GradesNewController, CategoryController, CategoryMasterController, VerticalController, TemplateController.
- **Not started, unclear if in scope**: Bank master (no trace found), Uniform module (legacy report itself scoped this out), full Company profile fields beyond the eight basic contact fields (compliance numbers, logo, generic policy store).
- **Not started, needs a product decision**: CompanySetupController's plan/subscription/Razorpay-payment gating has no equivalent at all (company-config.ts is a different, narrower mechanism); no new-tenant onboarding wizard exists to replace legacy's orphaned DbConfigController, though its individual seed steps (dept/design, holidays, shifts) are now always-available settings screens.
- **Regressions to flag**: Grade's guarded-delete (block if employees assigned) was dropped without an explicit design decision — this is a LIKELY BUG, not an intentional simplification, since nothing in the new code path replaces the protection (no FK constraint, no application check).
- **Flat/unenforced org hierarchy**: faithfully replicated (not fixed) — department/designation/division/section/grade remain schema-flat, exactly as in legacy, with no branch/parent FK anywhere in the new API routes either.

---

### 2.5 Dashboards & Notifications

# Dashboards & Notifications — Coverage/Fidelity Audit

Legacy root: `D:\Projects\RIZOMigration\legacy`
New app root: `D:\Projects\RIZOMigration\rizo\src`

## 1. Coverage Table

| Legacy item | Legacy report ref | New app location | Status |
|---|---|---|---|
| Admin dashboard (employee count, present-today, pending leaves) | user-side-report.md §12 DashboardController::index (`Controller/DashboardController.php:43-153`); code-logic-report.md §7.11 | `rizo/src/app/(dashboard)/dashboard/page.tsx`, `rizo/src/app/api/dashboard/stats/route.ts` | PARTIAL (core KPI tiles ported; "who's not clocked in", subscription plan, announcements, Zoom-onboarding widget dropped) |
| Employee/ESS self-service dashboard (last punch, YTD leave, month attendance, salary bar-chart) | user-side-report.md §12 `empdashboard()` (`DashboardController.php:785-`) | Same `dashboard/page.tsx` (single unified page, gated by `session.user.userGroup`) | PARTIAL (headline stats + own pending leaves + upcoming events only; punch/attendance/salary-chart widgets not present) |
| Hierarchy Dashboard for approvers/managers | user-side-report.md §12 `hierarchydashboard()` (`DashboardController.php:298-733`); also duplicated in `DashboardNewController` | No `hierarchydashboard` route/query found anywhere in `rizo/src` (grep clean) | NOT STARTED |
| Pending-leave-approval notification (2-stage authorize/approve) | code-logic-report.md §7.11 3.1 step 4 (`AppController.php:105`); user-side-report.md §12 | `rizo/src/app/api/dashboard/stats/route.ts:19-28`, `rizo/src/app/api/dashboard/notifications/route.ts:14-24`, `dashboard/page.tsx:19-32` | MIGRATED (query logic faithfully ported, now parameterized instead of raw string concat) |
| Birthday / joining-anniversary "next 7 days" notification | code-logic-report.md §7.11 3.1 step 4 (`AppController.php:112-116`) | `rizo/src/app/api/dashboard/events/route.ts:13-31`, duplicated inline in `dashboard/page.tsx:34-53` | MIGRATED, but see Fidelity Finding #1 (date-wraparound bug reproduced, not fixed) |
| Team-leave-visibility flag (fail-open `user_access` menu check) | code-logic-report.md §7.11 3.1 step 4 (`AppController.php:117-124`) | Not found in `rizo/src` (no `Team Leave Requests` / `menu_id` visibility-flag logic located) | NOT STARTED |
| Site-transaction end-date warning (hardcoded 6-tenant allowlist: vgfs/vsfs/gede/absg/demo/glet) | code-logic-report.md §7.11 3.1 step 5 (`AppController.php:137-149`) | grep for `site_transactions` / tenant codes across `rizo/src` returns nothing relevant (only unrelated `demo`/`glet`-style substrings in `resignations` routes and `lib/company-config.ts`/`lib/db.ts`, which are generic tenant-DB config, not this feature) | NOT STARTED |
| CTC-increment reminder (hardcoded 2-tenant allowlist: demo/glet) | code-logic-report.md §7.11 3.1 step 6 (`AppController.php:150-176`) | grep for `next_increment_date` returns nothing | NOT STARTED |
| Notification bell / layout-wide notification surface (`View/Layouts/default.ctp:550-635`) | user-side-report.md §12 intro | No bell/notification icon in `rizo/src/components/layout/Header.tsx` or `Sidebar.tsx` — notifications only appear as in-page dashboard cards | PARTIAL (re-scoped from "every page" to "dashboard page only" — a deliberate architecture change, not a 1:1 port) |
| Birthday/anniversary "Send Wish" flow (`wish_modal`, `convertimage`, PHPMailer wish emails, `Wish` model dedup) | user-side-report.md §12 items 1-2 (`DashboardController.php:3287-3605`, `DashboardNewController.php:3908-4283`) | grep for `Wish`/`wish_modal` returns nothing in `rizo/src` | NOT STARTED |
| `menuAudit` menu-click audit logging | code-logic-report.md §7.11 §6 (`DashboardController.php:283`, `DashboardNewController.php:1058`) | Not found | NOT STARTED |
| `DashboardController` (legacy/frozen admin+employee dashboard, `/Dashboard/index`) | user-side-report.md §12 item 1 | Consolidated into single `dashboard/page.tsx` | MIGRATED (consolidated, not duplicated — see Finding #2) |
| `DashboardNewController` ("Analytics" dashboard at `/Analytics`, KPI mega-widgets: salary trend, ESI/PF/PAN gaps, missing-nominee, age-donut, dept headcount, notice-period, retired count, etc.) | user-side-report.md §12 item 2; code-logic-report.md §7.11 (index() 1000-line action) | No `/analytics` route or equivalent widget queries found in `rizo/src/app` | NOT STARTED |
| `BusinessDashboardController` (BI/executive dashboard — company profile, CTC breakup, top/bottom salaries, chart JSON endpoints) | user-side-report.md §12 item 3 | No `BusinessDashboard`-equivalent route found | NOT STARTED |
| `MailBoxController` (empty stub, dead feature) | user-side-report.md §12 item 4; code-logic-report.md §7.11 §6 | No equivalent — correctly not ported | NOT STARTED (intentional — legacy dead code) |
| `EventHandlerController` (empty stub, dead feature) | user-side-report.md §12 item 5; code-logic-report.md §7.11 §6 | No equivalent | NOT STARTED (intentional — legacy dead code) |
| `InfoController` (unauthenticated `phpinfo()` dump — security bug) | user-side-report.md §12 item 6; code-logic-report.md §7.11 §5 | grep for `phpinfo` across `rizo/src` returns nothing | NOT STARTED — **security bug NOT reintroduced, confirmed** |
| `ActivityController` (project/timesheet activity board) | backend-report.md §2.11 item "ActivityController.php" (~line 4680) | Out of dashboard scope; not checked in `rizo/src` in this pass (no dashboard-tagged route found for it) | NOT STARTED (not in dashboard-scope files reviewed; flag for the Activity/Projects feature audit instead) |
| `AnalysisController` (duplicate-asset finder, salary-structure allocation issues, payroll data-quality tool) | code-logic-report.md §7.11 §6 | grep clean | NOT STARTED |
| `plan` mechanism (`comp_contact_info.plan`, queried in beforeFilter + duplicated in Dashboard controllers) | code-logic-report.md §7.11 §3.2 | Not present in any dashboard file reviewed (no `plan` selects in stats/events/notifications routes or page.tsx) | NOT STARTED (arguably fine — SaaS-tier gating appears unused/single-valued in legacy data anyway, per report note that only `'standard'` was ever seeded) |
| Absent-today card (derived, not a direct legacy widget) | — | `dashboard/page.tsx:84` — `Math.max(0, totalEmployees - presentToday)` | NEW (simple derived stat, no legacy equivalent computed this way — legacy has a separate `present_today_all`/date-filtered absent query) |

## 2. Fidelity Check Findings

### Finding 1 — Birthday/anniversary date-wraparound bug REPLICATED, not fixed
**Classification: LIKELY BUG**

- Legacy reference: `reports/code-logic-report.md` §7.11 3.1 step 4, citing `AppController.php:112-116` — the buggy version does a lexicographic string `BETWEEN` on `DATE_FORMAT(date, '%m-%d')`, which breaks across a year boundary (e.g. Dec 28 → Jan 04 window becomes `BETWEEN '12-28' AND '01-04'`, which matches nothing because `'01-04' < '12-28'` as strings).
- The SAME report also notes (§7.11 §6, `DashboardNewController` bullet, and Summary item 4) that `DashboardNewController.php:570-638` uses a **different, correct** CASE-based year-rollover implementation — i.e. legacy itself has both a buggy and a fixed version coexisting.
- New app: `rizo/src/app/api/dashboard/events/route.ts:13-31` and the duplicated inline query in `rizo/src/app/(dashboard)/dashboard/page.tsx:34-53` both use:
  ```sql
  WHERE DATE_FORMAT(date_of_birth,'%m-%d')
        BETWEEN DATE_FORMAT(CURDATE(),'%m-%d')
        AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY),'%m-%d')
  ```
  This is a **verbatim structural copy of the buggy `AppController.php:112-116` version** (same `%m-%d` string-BETWEEN pattern), not the corrected `DashboardNewController` CASE-based version. The wraparound bug is fully present: any employee with a birthday/anniversary in the last week of December will not appear in the "Upcoming Events" widget until the string-comparison edge case resolves itself in January.
- Today's date in this audit context is 2026-07-17, so the bug is currently dormant (no year-boundary in the active window) but will silently fail again around Dec 25–31, 2026.
- Recommendation: replace with day-of-year modulo arithmetic or a `CASE WHEN` rollover check (mirroring the fix legacy already has in `DashboardNewController`), e.g. compare `(DAYOFYEAR(date) - DAYOFYEAR(CURDATE()) + 365) % 365` against `[0,7]`.

### Finding 2 — Single dashboard implementation (clean consolidation, no duplication)
**Classification: INTENTIONAL-LOOKING (positive finding)**

- Legacy had three parallel dashboard controllers (`DashboardController`, `DashboardNewController` at `/Analytics`, `BusinessDashboardController`) with heavy copy-paste overlap (backend-report.md §2.11 items 1-3; code-logic-report.md §7.11 confirms `BusinessDashboardController` "overlaps heavily with DashboardNewController::index() ... copy-pasted almost verbatim").
- New app has exactly one dashboard route (`rizo/src/app/(dashboard)/dashboard/page.tsx`) plus three small, single-purpose API routes (`stats`, `events`, `notifications` under `rizo/src/app/api/dashboard/`). No sign of a second/third dashboard implementation, no `/analytics` or `/business-dashboard` route, no stray `_bkup`/`_old` files.
- This is a genuine architectural improvement over legacy and shows no early signs of the same duplication pattern re-emerging. Note: `dashboard/page.tsx:8-62` (`getDashboardData`) and `app/api/dashboard/stats/route.ts:7-35` currently duplicate the same three queries (employee count, present-today, pending leaves) rather than the page calling the API route — worth flagging as a minor DRY issue (page fetches directly from DB instead of reusing the `/api/dashboard/stats` handler), but this is a much smaller-scale duplication than legacy's three-controller mess and not a fidelity risk.

### Finding 3 — Tenant-specific hardcoded allowlist notifications (site-expiry, CTC-increment) entirely dropped
**Classification: NEEDS HUMAN DECISION**

- Legacy: `code-logic-report.md` §7.11 3.1 steps 5-6, `AppController.php:137-149` (site-transaction end-date warning, allowlist `vgfs/vsfs/gede/absg/demo/glet`) and `AppController.php:150-176` (CTC-increment reminder, allowlist `demo/glet`). Also user-side-report.md §12 top-of-section summary lines 4300-4301.
- New app: grep across `rizo/src` for `site_transactions`, `next_increment_date`, and the tenant codes (case-insensitive) finds no matching feature logic — the only tenant-code-like hits are in `rizo/src/app/api/resignations/[id]/eligibility/route.ts`, `rizo/src/app/api/resignations/[id]/approve/route.ts`, `rizo/src/lib/company-config.ts`, and `rizo/src/lib/db.ts`, which are unrelated generic tenant/company-config plumbing, not this notification feature.
- This is ambiguous: these were tenant-specific hardcoded business rules serving a small number of real customers (6 tenants for site-expiry, 2 for CTC-increment) in legacy. If those tenants are still active customers of the new app, dropping these notifications is a silent feature regression for them specifically (they would lose a warning about expiring site contracts and upcoming CTC increments). If those tenants are inactive/deprecated in the new system, or if this was intentionally deferred as "legacy cruft to externalize into a feature-flag system later" (as the report itself recommends in code-logic-report.md §7.11 Summary item 5), then omission is fine for now. Flagging for explicit product/business confirmation before considering this "complete" scope for Dashboards.

### Finding 4 — `InfoController` phpinfo() security bug NOT reintroduced
**Classification: INTENTIONAL-LOOKING (confirmed absence, positive finding)**

- Legacy: `code-logic-report.md` §7.11 §5, confirmed unauthenticated `phpinfo()` dump at `Controller/InfoController.php:65`, reachable at `/info` with no auth because the controller extends bare `Controller` instead of `AppController`.
- New app: grep for `phpinfo` across all of `rizo/src` returns zero matches. No `/info` or diagnostic route of any kind was found under `rizo/src/app`.
- **Security bug NOT reintroduced, confirmed.**

### Finding 5 — Hierarchy Dashboard (manager/approver view) and "Team Leave Requests" visibility flag both absent
**Classification: NEEDS HUMAN DECISION**

- Legacy: `hierarchydashboard()` is a substantial, actively-used manager-scoped dashboard (recursive up-to-6-level subordinate tree, today's-birthdays-among-direct-reports, team pending leave, `getEvents()`) duplicated across `DashboardController.php:298-733` and `DashboardNewController.php:1073+` (user-side-report.md §12 items 1-2; code-logic-report.md §7.11 §6). It is reached via the `user_access menu_id='0'` toggle check that every one of the three legacy dashboard controllers (and even the cookie-based auto-login path in `PagesController`) re-implements.
- New app: no `hierarchydashboard` logic, no `menu_id='0'` access-flag check, and no manager/subordinate-scoped variant of the dashboard was found — `dashboard/page.tsx` only branches on `userGroup === 1` (admin) vs not, with no third "manager/approver" branch.
- Similarly, the "Team Leave Requests" fail-open visibility flag (`AppController.php:117-124`) has no equivalent.
- Since managers/approvers are a real, presumably still-needed user segment (the "Pending Leave Approvals" widget in the new dashboard is currently gated to `userGroup === 1` only — see `dashboard/page.tsx:111`, `stats/route.ts:19`, `notifications/route.ts` — meaning **non-admin managers currently see NO pending-approval widget at all** in the new app, a possible functional gap for any team-lead/manager who is `userGroup === 2` but has approval authority via `ISAutherizedby`/`APPROVEDBY`). This should be confirmed with product: is manager-level (non-admin) leave approval still a supported role in the new app, and if so, the pending-leave query's `userGroup === 1` gate (rather than checking whether the logged-in user actually has any leaves assigned to approve) looks like a functional regression, not just a missing "nice-to-have" dashboard variant.

## Summary

- The new app has a clean, consolidated single dashboard (a genuine improvement over legacy's three-controller mess) covering the two most business-critical legacy items faithfully in query shape: pending leave approvals and the 7-day birthday/anniversary window.
- The birthday/anniversary query is a literal structural copy of legacy's *buggy* string-BETWEEN implementation (not legacy's own already-fixed CASE-based version elsewhere in the same codebase) — this is the most concrete, easily-fixed bug found in this audit.
- Everything past those two core widgets — hierarchy/manager dashboard, Analytics KPI mega-dashboard, BusinessDashboard BI suite, Send-Wish email flow, menu audit logging, tenant-specific site-expiry and CTC-increment allowlisted notifications, and the notification bell UI pattern — is not yet started. Most of that is reasonable prioritization (dead/duplicate legacy code, or lower-value BI charts), but the tenant-specific allowlist notifications and the manager/approver leave-approval gap are flagged as needing an explicit business decision rather than being assumed out of scope.
- The `InfoController` unauthenticated `phpinfo()` security bug is confirmed NOT present anywhere in the new app.

---

### 2.6 Assets & Documents

# Assets & Document Management — Coverage/Fidelity Audit

Scope: legacy Assets/Inventory/Purchasing cluster (`AssetController`, `ItemController`, `ItemSpecificationController`, `StockmanagementController`, `StoreController`, `SupplierMasterController`, `VendorController`, `VehicleController`, `PurchaseOrderController`, `DirectPurchaseOrderController`, `GoodsReceivedNotesController`, `StockTranferController`, `StockReportController`) vs. legacy Document Management (`DocumentManagerController`/`DocumentManagersController`) — compared against the new Next.js app's `assets`, `employees/assets`, `document-templates`, `employees/generate-documents`, `documents`, and `employees/[id]/documents` features.

## Headline finding

The new app implements **only two narrow slices** of this legacy scope:
1. **Simple company asset register + employee allocation** (a faithful, improved port of `AssetController`'s core CRUD/allocate/return flow).
2. **The document-template "merge and generate" engine** (a faithful port of `DocumentManagerController`'s part (a): CKEditor-template → placeholder-fill → PDF/print).

**Everything else in the legacy Assets/Inventory/Purchasing cluster — the entire procurement chain (Material Request → Purchase Order → Goods Received Note), Item Master, Store Master, Stock Adjustment, Stock Transfer, Vendor Master, Vehicle Master, and Stock Reports — is NOT STARTED.** There is no `stock_details`, `item_master`, `purchase_order`, `goods_receved_notes`, `store_master`, or `vehicle_master` reference anywhere in the new app's `src` tree for this scope. This is a deliberate, large scope reduction (or at minimum a large deferred scope), not a partial/buggy port — the new app simply doesn't attempt inventory/procurement yet.

Separately, legacy `DocumentManagerController`'s part (b) — the plain file-upload/allocate-to-employee library (`document_upload`/`document_allocation` tables) — is also **NOT STARTED** in the new app. What the new app calls "employee documents" (`app/api/employees/[id]/documents/route.ts`) is actually a different legacy table/feature: `emp_passport_visa` (personal-document records captured at onboarding, from the Employee Management cluster's `EmpDocument`/onboarding flow), not DocumentManager's upload library.

---

## Deliverable 1 — Coverage Table

| Legacy item | Legacy report ref | New app location | Status |
|---|---|---|---|
| `AssetController` — asset master (add/edit/list assets) | user-side-report.md §10.1 (`AssetController.php`); backend-report.md §2.9 (`AssetController.php` action table); code-logic-report.md §7.9 (Assets model analysis) | `rizo/src/app/(dashboard)/assets/page.tsx`; `rizo/src/app/api/assets/route.ts`; `rizo/src/app/api/assets/[id]/route.ts` | **MIGRATED** (core CRUD only — see fidelity notes on scope reduction of dropdown/branch-access logic) |
| `AssetController` — allocate/return to employee | user-side-report.md §10.1 steps 4–9; backend-report.md §2.9 | `rizo/src/app/(dashboard)/employees/assets/page.tsx`; `rizo/src/app/api/employees/assets/route.ts`; `rizo/src/app/api/employees/assets/[id]/route.ts` | **MIGRATED** (improved — see fidelity notes) |
| `AssetController` — asset types master data | user-side-report.md §10.1 ("asset types loaded from `asset_types`") | `rizo/src/app/api/setup/asset-types/route.ts` | **MIGRATED** (read-only lookup; no dedicated CRUD UI found for `asset_types` itself in files reviewed) |
| `ItemController` — Item Master | user-side-report.md §10.2; backend-report.md §2.9; code-logic-report.md §7.9 §2 (ItemController) | none found | **NOT STARTED** |
| `ItemSpecificationController` — Item Specification Master | user-side-report.md §10.3; backend-report.md §2.9 | none found | **NOT STARTED** |
| `StockmanagementController` — Stock module landing/feature-gate | user-side-report.md §10.4; backend-report.md §2.9 | none found | **NOT STARTED** |
| `StoreController` — Store Master | user-side-report.md §10.5 (Store Master sub-flow); backend-report.md §2.9 | none found | **NOT STARTED** |
| `StoreController` — Manual Stock Adjustment | user-side-report.md §10.5 (Stock Adjustment sub-flow); code-logic-report.md §7.9 §3 (Path 3) | none found | **NOT STARTED** |
| `StoreController` — PO Return (return goods to supplier) | user-side-report.md §10.5 (PO Return sub-flow); code-logic-report.md §7.9 §3 (Path 2) | none found | **NOT STARTED** |
| `SupplierMasterController` — Material Request CRUD (misnamed controller) | user-side-report.md §10.6; backend-report.md §2.9 | none found | **NOT STARTED** |
| `VendorController` — Vendor/Contact Master + legacy item-purchase mini-ledger | user-side-report.md §10.7; backend-report.md §2.9 (flags the item_purchase/item_allocate path as likely-abandoned parallel subsystem) | none found | **NOT STARTED** |
| `VehicleController` — Vehicle Master | user-side-report.md §10.8; backend-report.md §2.9; code-logic-report.md §7.9 §1.4 (`vehicle_master` table missing from schema — real legacy bug/gap) | none found | **NOT STARTED** (also means the missing-table bug is moot for now — see fidelity Q2) |
| `PurchaseOrderController` — Purchase Order (MR→PO) | user-side-report.md §10.9; backend-report.md §2.9 (`testdownloads()` grn_status mutation bug); code-logic-report.md §7.9 §2 | none found | **NOT STARTED** |
| `DirectPurchaseOrderController` — Direct PO (no MR) | user-side-report.md §10.10; backend-report.md §2.9 (confirmed disconnected from stock) | none found | **NOT STARTED** |
| `GoodsReceivedNotesController` — GRN (goods receipt, stock increment) | user-side-report.md §10.11; backend-report.md §2.9; code-logic-report.md §7.9 §2–3 (the "correct"/canonical stock-increment path) | none found | **NOT STARTED** |
| `StockTranferController` — Inter-store Stock Transfer | user-side-report.md §10.12; backend-report.md §2.9 (stored-procedure-dependent); code-logic-report.md §7.9 §3 (Path 4) | none found | **NOT STARTED** |
| `StockReportController` — inventory-relevant report types (Stock/GRN/Material/PO/PoReturn/StockTransfer reports) | user-side-report.md §10.13; backend-report.md §2.9 | none found | **NOT STARTED** |
| `DocumentManagerController`/`DocumentManagersController` part (a) — document-template engine (CKEditor templates, placeholder fill, PDF preview) | user-side-report.md §2 §13(a); backend-report.md §2.1 (Employee Mgmt) item 13 | `rizo/src/app/(dashboard)/employees/generate-documents/page.tsx`; `rizo/src/app/api/document-templates/route.ts` + `[id]/route.ts`; `rizo/src/app/api/employees/[id]/generate-document/route.ts`; `rizo/src/app/api/documents/route.ts` + `[id]/route.ts`; `rizo/src/lib/documentMerge.ts` | **MIGRATED** (curated subset of ~65 legacy placeholder tokens → ~40 tokens across Employee/Company/Branch groups only; Supplier/Customer/Others/image-composite tokens explicitly dropped, per `documentMerge.ts:9-11` comment) |
| `DocumentManagerController`/`DocumentManagersController` part (b) — plain file-upload/document library, allocate uploaded PDFs to employees | user-side-report.md §2 §13(b); backend-report.md §2.1 item 13 | none found (`document_upload`/`document_allocation` tables not referenced anywhere in `rizo/src`) | **NOT STARTED** |
| `EmpDocument`/`emp_passport_visa` — post-onboarding personal document records (passport/visa/ID-type docs) — a *different* legacy concept from DocumentManager, part of Employee Management cluster (code-logic-report.md §7.1) | code-logic-report.md §7.1 lines 2311-2317, 2484-2485 | `rizo/src/app/api/employees/[id]/documents/route.ts` + `[docId]/route.ts` (writes/reads `emp_passport_visa`); `rizo/src/components/employees/DocumentUploadField.tsx`; `rizo/src/app/api/upload/route.ts` | **MIGRATED** (correctly scoped to the right table — see fidelity note) |
| `TemplateController` (Company Setup cluster) — considered as possible analog for `document-templates` | user-side-report.md §8 (line 2696-2698): confirmed dead/orphaned duplicate of `BankController`, no view folder, "entirely dead/orphaned" | n/a | Not a real legacy analog — confirms `document-templates`/`documentMerge.ts` should be assessed against `DocumentManagerController` (its actual analog, found), not `TemplateController` (a red herring/dead code) |

**Summary count:** Of the 13 controllers in the legacy Assets/Inventory/Purchasing scope, only `AssetController` has any coverage (partial — allocation only, no full asset-lifecycle features like `Create_asset` branch-scoping variants). The other 12 controllers (100% of the procurement/inventory chain) are NOT STARTED. Of the 2 sub-features in `DocumentManagerController`, only the template-engine half (a) is migrated; the file-library half (b) is NOT STARTED.

---

## Deliverable 2 — Fidelity Check Findings

### Q1. Does the new app's asset feature include the full PO/vendor/GRN procurement chain, or only simple asset-allocation?

**Confirmed: scope reduction to simple asset-allocation only — classified INTENTIONAL-LOOKING (a scoped rewrite), but undocumented as such anywhere in the new app.**

- Legacy: `AssetController.php` (user-side-report.md §10.1) is one piece of a 13-controller cluster implementing full procurement: Material Request (`SupplierMasterController`) → Purchase Order (`PurchaseOrderController`) → Goods Received Note (`GoodsReceivedNotesController`), with Item Master, Store Master, Stock Adjustment, Stock Transfer, Vendor Master, Vehicle Master, and inventory reports as supporting/downstream modules (user-side-report.md §10, full "Procurement Workflow" section at lines 3538-3569).
- New app: `rizo/src/app/(dashboard)/assets/page.tsx` + `rizo/src/app/api/assets/route.ts` only implement a flat `asset_management` catalog (add/edit/soft-delete) and `rizo/src/app/(dashboard)/employees/assets/page.tsx` + `rizo/src/app/api/employees/assets/route.ts` implement allocate/return against `asset_allocate`. No `item_master`, `purchase_order`, `material_request`, `goods_receved_notes`, `store_master`, `stock_details`, `stock_tranfer`, or `vehicle_master` table is referenced anywhere in `rizo/src` (confirmed via repo-wide grep for these table/model names — zero hits).
- **This is the single largest scope gap in this domain**: the entire procurement chain (12 of 13 legacy controllers) has not been started. **NEEDS HUMAN DECISION**: confirm with product whether Inventory/Purchasing is planned for a later phase or intentionally out of scope for this rewrite — the new app currently has no path for buying/receiving/tracking stock items at all, only for registering and allocating discrete company assets (laptops, phones, etc.) already in the asset register.

### Q2. Does the new `assets` API avoid the legacy `Vehicle`/`vehicle_master` missing-table bug, or does an equivalent gap exist?

**Classified INTENTIONAL-LOOKING / not applicable — the new app avoids the bug by not building the feature at all.**

- Legacy bug (code-logic-report.md §7.9 §1.4): `Model/Vehicle.php` declares `$useTable = 'vehicle_master'`, and `VehicleController.php` issues raw SQL against `vehicle_master` in 6+ places, but `schema/mypayrol_trial.sql` has **no `CREATE TABLE vehicle_master`** anywhere — a genuine schema/code mismatch (either a stale schema dump or a fully orphaned module).
- New app: `VehicleController`'s domain (Vehicle Master) has **no equivalent anywhere in `rizo/src`** — confirmed via repo-wide search, no `vehicle` references in scope. Since the feature was never started, there is no missing-table bug to inherit. `rizo/src/app/api/assets/route.ts:13-14` correctly targets `asset_management` (a table confirmed present in schema at `schema/mypayrol_trial.sql:28817` per code-logic-report.md §7.9 §1.1), and `rizo/src/app/api/employees/assets/route.ts` correctly targets `asset_allocate` (confirmed present at `schema/mypayrol_trial.sql:28789`). No equivalent table-mismatch gap was found in the new Asset code reviewed.
- Also worth noting: the new app avoids the legacy's **triplicate model problem** (`Assets`/`AssetsModel`/`AssetsName` all pointing at `asset_management`, code-logic-report.md §7.9 §1.2) simply by having one clean route file with no ORM-model duplication — there's nothing to duplicate since raw SQL/`mysql2` is used directly.

### Q3. Is stock-quantity tracking implemented via ONE consistent mechanism, or not applicable because stock/inventory isn't built yet?

**Not applicable — stock/inventory (item-quantity ledger, `stock_details`) is not built at all in the new app.** There is nothing to compare against legacy's 4-mechanism inconsistency (GRN receipt via ORM `saveAll`, PO Return via ORM `save`, Stock Adjustment via raw SQL `INSERT`, Stock Transfer via stored procedure — code-logic-report.md §7.9 §3) because the new app has no `stock_details`-equivalent concept.

However, the **asset-allocation** flow (which *does* exist in the new app) is worth noting as a genuine, narrower analog to "quantity/status tracking," and here the new app **is** an improvement:
- Legacy `AssetController::AllocatenewAsset()`/`release()` (user-side-report.md §10.1 steps 6-7; code-logic-report.md §7.9 "AssetController.php" section) update `asset_allocate.status` and `asset_management.status` via **two separate raw SQL `updateAll`/`query()` calls with no transaction wrapping** — code-logic-report.md §7.9 explicitly flags this: *"No transaction wrapping either statement — partial failure leaves the two tables inconsistent."*
- New app: `rizo/src/app/api/employees/assets/route.ts:71-100` (POST/allocate) and `rizo/src/app/api/employees/assets/[id]/route.ts:26-48` (PUT/return) both wrap the two-table update (`asset_allocate` + `asset_management`) in an explicit `connection.beginTransaction()` / `commit()` / `rollback()` block.
- **Classification: INTENTIONAL-LOOKING IMPROVEMENT.** Cite: legacy `AssetController.php:949-957` (`release()`, per backend-report.md §2.9 action table) vs. `rizo/src/app/api/employees/assets/[id]/route.ts:26-48`. This is a genuine correctness improvement over legacy's un-transacted dual-table update, worth calling out positively — but it's a narrow win since it only covers allocation status, not the broader stock-quantity-ledger problem which remains entirely unaddressed (see Q1).

### Q4. Does document generation (`generateDocument`/`documentMerge.ts`) correspond to a legacy feature, or is it genuinely new?

**Classified as MIGRATED, not NEW — it directly corresponds to legacy `DocumentManagerController`'s template-fill sub-feature (part a), with an intentionally reduced placeholder set.** Confirm via:
- Legacy: user-side-report.md §2 §13, "(a) Document templates," steps 1-8 (lines 450-458): CKEditor template body + `placeholders` comma-list, `saveTemplate()` (`DocumentManagerController.php:123-187`), `createDocument()`/`getData()`/`getPlaceholder()`/`saveDocument()` (`:202-205, 310+, 792, 889`) fill placeholder tokens for a chosen employee/company/branch context, `docPreview()` (`:331-791`) renders HTML/PDF preview.
- New app: `rizo/src/lib/documentMerge.ts` implements the same conceptual pipeline — `buildMergeTokens()` (lines 13-80) resolves employee/company/branch data into a token map, `mergeTemplate()` (lines 82-84) does `{:token}` substitution (same `{:token}` bracket syntax as legacy, confirmed by the file's own top-of-file comment at lines 4-11: *"Mirrors legacy DocumentManagerController::getPlaceholder()/saveDocument()... using legacy's exact spelling where confirmed (including its `empolyee_name` typo)"*). `rizo/src/app/api/employees/[id]/generate-document/route.ts` mirrors `createDocument()`/`saveDocument()`, and `rizo/src/app/(dashboard)/employees/generate-documents/page.tsx` mirrors the template-picker/preview/print flow (`docPreview()`).
- **The `empolyee_name` typo preservation** (`documentMerge.ts:40` and `:88`) is a deliberate, documented fidelity choice — the code comment explicitly says this is to match legacy's exact token spelling for any templates that might get re-authored from legacy content. Classified **INTENTIONAL-LOOKING** (good practice: documented, not accidental).
- **Scope reduction is explicit and documented**: `documentMerge.ts:9-11` states the new app implements "a curated subset of legacy's ~65 tokens (Employee/Company/Branch groups only — Supplier/Customer/Others and the image-composite/salary-breakup tokens are out of scope for this pass)." This is the single clearest example in this whole audit of a scope-reduction being self-documented in the migrated code itself — good practice, but **NEEDS HUMAN DECISION** on whether Supplier/Customer/Others template contexts (used by legacy for e.g. vendor-facing documents) are needed, since those are dropped entirely, not just deferred.
- Legacy's part (b) — plain file upload/allocate library (`documentUpload()`, `documentAllocate()`, `document_upload`/`document_allocation` tables, user-side-report.md §2 §13(b) lines 460-465) — has **no equivalent** in the new app. This is a real NOT STARTED gap, distinct from the template-engine which is migrated.

### Additional fidelity note: `employees/[id]/documents` targets the correct (different) legacy table

**Classified INTENTIONAL-LOOKING and correctly scoped**, but worth flagging because it's easy to conflate with DocumentManager's file-upload feature:
- `rizo/src/app/api/employees/[id]/documents/route.ts:7-11` contains a code comment explicitly clarifying: *"Legacy's 'Document Upload' is a tab embedded on the employee profile... it writes to `emp_passport_visa`... a separate table from `emp_documents`."*
- This matches code-logic-report.md §7.1 lines 2311-2317, 2484-2485, which documents `EmpDocument`/`emp_documents` (onboarding staging, `emp_join_fkey`-keyed, cascade-deletes with the onboarding row) as distinct from `emp_passport_visa` (the permanent post-onboarding record).
- The new app correctly targets `emp_passport_visa` for post-onboarding document management (`rizo/src/app/api/employees/[id]/documents/route.ts:22-30` GET, `:46-57` POST) and even preserves the legacy soft-delete-vs-hard-delete distinction between the two tables (comment at `[docId]/route.ts:18-19`: *"Soft delete (status=0): emp_passport_visa is the permanent, post-onboarding record, unlike the staging emp_documents table which is hard-deleted on discard."*).
- This is **not** the same feature as `DocumentManagerController`'s file-upload library (`document_upload`/`document_allocation`) — that remains NOT STARTED (see coverage table). The developer's own comments show this distinction was understood and deliberate, not an accidental substitution.

---

## Summary of Classifications

| Finding | Classification |
|---|---|
| Full PO/GRN/Vendor procurement chain (12 of 13 legacy controllers) not started | NEEDS HUMAN DECISION (scope confirmation) |
| `vehicle_master` missing-table bug — moot, feature not built | N/A (avoided by omission) |
| Asset allocate/return wrapped in DB transaction (legacy wasn't) | INTENTIONAL-LOOKING IMPROVEMENT |
| Stock-quantity ledger (4-mechanism inconsistency in legacy) — not applicable, not built | N/A (nothing to compare) |
| Document-template merge engine ported with `empolyee_name` typo preserved | INTENTIONAL-LOOKING (documented fidelity choice) |
| Document-template token set reduced from ~65 to ~40 (Employee/Company/Branch only) | NEEDS HUMAN DECISION (Supplier/Customer/Others contexts dropped) |
| DocumentManager part (b) file-upload/allocate library not started | NOT STARTED (real gap) |
| `employees/[id]/documents` correctly targets `emp_passport_visa`, not DocumentManager's tables | INTENTIONAL-LOOKING (correctly scoped, self-documented in code) |
| `TemplateController` red-herring analog ruled out (confirmed dead in legacy) | No action needed — correctly not treated as an analog |

No LIKELY BUG classifications were found in the new app's Assets/Document code reviewed — the migrated pieces (asset allocation, document-template merge) are both better-behaved than their legacy counterparts (transactional writes, documented scope reductions). The dominant finding in this domain is breadth, not correctness: 12 of 13 legacy inventory/procurement controllers and one of two DocumentManager sub-features are simply not yet built.

---

### 2.7 Promotions & Resignations

# Promotions & Resignations — Legacy vs. New App Coverage/Fidelity Audit

Scope: `PromoController`, `ResignationRequestController`, `FullandFinalsettlementController`,
`SelfReviewController`, `TeamReviewController`, `HierarchyReviewController`, `PerformanceController`,
`ExceptionRuleController` vs. `rizo/src/app/(dashboard)/employees/{promotions,resignations}`,
`rizo/src/app/api/{promotions,resignations}`, `rizo/src/lib/settlement.ts`.

---

## 1. Coverage table

| Legacy item | Legacy report ref | New app location | Status |
|---|---|---|---|
| `PromoController` (promotion request + approval fan-out: designation/dept/branch/type/shift/leave/salary/CTC/hierarchy) | user-side-report.md §11 "6. PromoController" (line ~4020); backend-report.md §2.10 "PromoController" (line ~4348) | `rizo/src/app/(dashboard)/employees/promotions/page.tsx`, `rizo/src/app/api/promotions/route.ts`, `rizo/src/app/api/promotions/[id]/route.ts` | **MIGRATED** (single-tier approval, simplified from legacy's per-approver-routed queue — see Fidelity §2.1) |
| `ResignationRequestController::Saverequests` (employee-initiated resignation, creates `resignation_requests` + `termination` rows) | user-side-report.md §11; backend-report.md §2.10 "ResignationRequestController" (line ~4389); code-logic-report.md §7.10 §3c/§5 (line ~5347, ~5388) | `rizo/src/app/(dashboard)/employees/resignations/page.tsx` (New Resignation modal), `rizo/src/app/api/resignations/route.ts` POST | **MIGRATED**, restructured from self-service ESS flow into an admin-only HR data-entry flow (session gate is `userGroup !== 1` throughout — see Fidelity §2.2) |
| `ResignationRequestController::withd` (withdrawal) | code-logic-report.md §7.10 §3c (line ~5372) | `rizo/src/app/api/resignations/[id]/withdraw/route.ts` | **MIGRATED** |
| `ResignationRequestController::grandrequest`/`Empagreed` (manager checklist + employee agreement) | user-side-report.md; backend-report.md line ~4404-4405; code-logic-report.md §7.10 §2 (line ~5284-5285) | `rizo/src/app/api/resignations/[id]/checklist/route.ts` | **PARTIAL** — checklist (`chek_formalities`/`chek_assets`/`chek_leave`/handover) is migrated and collapses legacy's separate authorise-vs-accept sub-steps into one action; the employee-side `Empagreed` "agree to handover terms" step has no equivalent (no `agree` field anywhere in the new app — see Fidelity §2.4) |
| `FullandFinalsettlementController::Terminate` (admin termination path, direct `termination` upsert + `emp_details.status='2'` flip) | backend-report.md line ~4375; code-logic-report.md §7.10 §3c (line ~5364) | `rizo/src/app/api/resignations/[id]/finalize/route.ts` | **MIGRATED**, folded into the finalize step of the unified resignation state machine rather than kept as a separate ad-hoc HR action (see Fidelity §2.3) |
| `FullandFinalsettlementController::get_complete` / `final_settle_pay_prc` (F&F pay computation) | backend-report.md line ~4382; code-logic-report.md §4.1 "final_settle_pay_prc" (line ~1001) | `rizo/src/app/api/resignations/[id]/approve/route.ts`, `rizo/src/lib/settlement.ts` | **MIGRATED** — calls the real stored procedure, does not reimplement the tax/gratuity math in JS (see Fidelity §2.5, high-value finding) |
| `FullandFinalsettlementController::approveencash`/`leaveadjustment` (leave encashment) | backend-report.md line ~4383, ~4385 | `rizo/src/lib/settlement.ts` (`commitLeaveEncashment`, `previewEncashableLeaveBalance`) calling real `leave_encash_prc` | **MIGRATED** |
| `FullandFinalsettlementController::Assets` (allocated asset list pending return) | backend-report.md line ~4377 | `rizo/src/lib/settlement.ts` (`getLoansAndAssets`) | **MIGRATED** (read-only reference panel, not auto-netted — matches legacy) |
| Resignation letter (`letter()`/`letteredit()`) | code-logic-report.md §7.10 §2/§4 (line ~5282, ~5383) | `rizo/src/app/(print)/employees/resignations/[id]/slip/page.tsx` | **NEW/DIFFERENT SHAPE** — new app has a "Full & Final Settlement Statement" print slip (via `[id]/slip/route.ts`), not a resignation-acceptance letter; legacy's `letter`/`letteredit` (with divergent, possibly-broken join keys) has no direct counterpart found |
| `SelfReviewController` (executive self-appraisal Part I/II) | user-side-report.md §11 "1." (line ~3591); backend-report.md §2.10 (line ~4252); code-logic-report.md §7.10 §0/§2 (line ~5161, ~5222) | none found | **NOT STARTED** |
| `TeamReviewController` (reporting/reviewing officer scoring, executive track) | user-side-report.md §11 "2." (line ~3685); backend-report.md (line ~4275); code-logic-report.md §7.10 §2/§3a (line ~5244, ~5308) | none found | **NOT STARTED** |
| `HierarchyReviewController` (staff/workmen assessment track) | user-side-report.md §11 "3." (line ~3771); backend-report.md (line ~4289); code-logic-report.md §7.10 §2/§3b (line ~5254, ~5334) | none found | **NOT STARTED** |
| `PerformanceController` (HR reporting layer over the review tables — PDF/Excel exports) | backend-report.md (line ~4231); code-logic-report.md §7.10 §2 (line ~5271) | none found | **NOT STARTED** |
| `ExceptionRuleController` (attendance exception → LOP/leave-deduction rule engine) | backend-report.md (line ~4409) | none found | **NOT STARTED** |

Note on the Performance Appraisal cluster (SelfReview/TeamReview/HierarchyReview/Performance): per prior-confirmed
finding, its backing tables (`self_review_details`, `assessment_attributes_*`, `assessment_summary_executive`) are
**absent from the schema dump entirely** (code-logic-report.md §7.10 §0, line ~5161-5179) — this is flagged there as
likely a stale/incomplete schema export, not evidence the feature is dead, since the controllers carry mid-2025
edit timestamps. Its NOT STARTED status in the new app should be read in that light: there is no schema to build
against yet without a fresh DB pull, independent of migration priority.

---

## 2. Fidelity check findings

### 2.1 Promotions: single-tier approval vs. legacy's per-approver routed queue — NEEDS HUMAN DECISION

Legacy (`user-side-report.md` line ~4038-4051, `backend-report.md` line ~4355): `listemployees()` filters to
`Promotion.status=1 AND approved_by=me AND promotion_status='APPLIED'` — each promotion request is routed to a
**specific approver** (`approved_by` set at request time), and that approver alone sees/acts on it. Any employee in
`user_group==2` can submit a request for their own direct reports (`getautocompletions_superior` restricts the
employee-picker to `emp_proff.attr1 = current emp_fkey`).

New app (`rizo/src/app/api/promotions/route.ts:15,48`, `[id]/route.ts:22`): both `GET` and `PUT` are gated
`session.user.userGroup !== 1` — i.e. only Admin/HR can create or see promotion requests at all; there is no
per-approver routing, no `approved_by` targeting at creation (`route.ts:61` hardcodes `approved_by = 0` on insert),
and no manager-submits-for-direct-reports self-service path. This is a real behavioral narrowing: legacy's
manager-initiated promotion request flow (any `user_group==2` manager proposing a promotion for a report, routed to
a specific approver) has no equivalent — the new app is HR-initiated and HR-approved only, single-tier.
Classify: **NEEDS HUMAN DECISION** — confirm whether manager-initiated promotion requests are in scope for this
app or intentionally centralized to HR.

### 2.2 Resignations: self-service ESS flow replaced with HR-managed flow — NEEDS HUMAN DECISION

Legacy (`code-logic-report.md` §7.10 §3c, line ~5357; user-side-report.md §11): `ResignationRequestController` is
an **employee self-service** flow — the employee submits their own resignation (`Saverequests`), views their own
request (`Request`), and withdraws it (`withd`) via ESS. Managers see requests via `listleaves()` (`authorised_to =
me` or `forwarded = me`).

New app (`rizo/src/app/api/resignations/route.ts:15,57`, all sub-routes): every route — GET list, POST create, PUT
edit, checklist, approve, finalize, withdraw, slip — is gated `session.user.userGroup !== 1`. There is no
employee-facing submission path; this is entirely an HR/admin data-entry tool where HR records a resignation on an
employee's behalf, not an ESS self-service flow. This is a significant behavioral change from legacy, consistent
with how this migration phase appears scoped (admin console, ESS not yet built) but worth flagging explicitly since
it changes who can act at each stage.
Classify: **NEEDS HUMAN DECISION** — confirm ESS self-service resignation submission is planned for a later phase,
not silently dropped.

### 2.3 Employee status flip on FINALIZE — correctly reproduces the *admin* path, and structurally avoids legacy's employee-initiated-path gap

Legacy bug/gap (code-logic-report.md §7.10 §3c, line ~5364-5370): only `FullandFinalsettlementController::Terminate`
flips `emp_details.status='2'`; `ResignationRequestController::Saverequests` (employee-initiated) never does. An
employee who resigns via ESS alone stays `status=1` (active) indefinitely unless HR separately runs `Terminate`.

New app: `rizo/src/app/api/resignations/route.ts` POST (submit) does **not** touch `emp_details.status` — matches
legacy's employee-initiated path correctly not flipping status at submission time. The flip happens only in
`rizo/src/app/api/resignations/[id]/finalize/route.ts:46` (`UPDATE emp_details SET status = 2 WHERE emp_pkey = ?`),
which is gated behind `is_approved === 'Y'` (`finalize/route.ts:34-36`) — i.e. structurally requires the
`approve` step (which itself requires passing `eligibility` checks) before finalize is reachable. Since the new app
has collapsed legacy's two independent creation paths (self-service vs. admin-Terminate) into **one single unified
workflow** (submit → checklist → eligibility/approve → finalize), the specific two-path divergence bug from legacy
cannot recur structurally — there's only one path to a status flip, and it's gated the same way legacy's *admin*
path was (auto-approved by system in legacy `Terminate()`, but explicitly gated behind a real `is_approved` check
here — arguably an improvement).
Classify: **INTENTIONAL-LOOKING** (correct behavior, and a defensible fix of the legacy inconsistency by unifying
the two paths rather than replicating both).

The new app also goes further than legacy at finalize: it clears other employees' `emp_proff.attr1` hierarchy
references to the now-terminated manager and deactivates their `HIERARCHY` `emp_config` rows
(`finalize/route.ts:47-49`) — legacy's `Terminate()` (backend-report.md line ~4375) does not do this reference
cleanup at all. This is a genuine improvement beyond fidelity, not a discrepancy to flag as a bug, but worth noting
since it's new behavior not present in legacy — confirm it's desired (e.g., does legacy leave dangling hierarchy
references intentionally, perhaps for audit-trail reasons).
Classify: **NEEDS HUMAN DECISION** (new behavior beyond legacy parity, likely desirable but not verified as intended).

### 2.4 `agree` no-op bug — not reproduced, because the field doesn't exist in the new app

Legacy bug (code-logic-report.md §7.10 §4, line ~5379; §6, line ~5470): `ResignationRequestController::Empagreed`
(`legacy/Controller/ResignationRequestController.php:588`) has `$arr_form_data['agree'] == 1;` — a no-op
comparison instead of assignment, so `resignation_requests.agree` is never actually set to `1` despite the UI
implying the employee agreed to handover terms.

New app: grep for "agree" across `rizo/src/app` returns no matches — there is no `Empagreed`-equivalent endpoint,
no `agree` field read or written anywhere in the resignation flow. The `agree` column read/write path from legacy
has been dropped entirely, not reproduced with the same bug. This class of bug (assignment vs. comparison
confusion) is also not structurally reproducible in TypeScript in the same silent way — `if (x == 1)` as a
standalone statement would be flagged by TS/ESLint as an unused expression, and none of the reviewed files contain
that pattern.
Classify: **INTENTIONAL-LOOKING** (bug class avoided by omission — but see Coverage table: the underlying
"employee formally agrees to handover terms" feature itself is simply not migrated, which is a product gap distinct
from the bug).

### 2.5 F&F settlement math: `lib/settlement.ts` calls the real stored procedure — HIGH-VALUE finding, no drift risk

This is the most consequential check in scope. Legacy's `final_settle_pay_prc` (code-logic-report.md §4.1, line
~1001-1043) is a large, hand-rolled MySQL stored procedure that reimplements: PT true-up (half-year window split),
leave encashment pull, loan/advance payoff netting, gratuity ((Basic+DA)/26×15×years, 5-year eligibility gate), a
bonus true-up, and a **full progressive Indian income-tax slab calculation** (hardcoded slabs, hardcoded ₹50,000
standard deduction, hardcoded surcharge/cess/§87A rebate constants) — all with several already-documented
discrepancies from the live monthly TDS calculator (cess basis differs, code-logic-report.md line ~1022, ~1392).

New app: `rizo/src/app/api/resignations/[id]/approve/route.ts:56-61` calls
`CALL final_settle_pay_prc(?, ?, ?, ?, ?, ?, @perror)` directly against the company DB, then reads back
`@perror` and the resulting `emp_settle_slip` rows (lines 69-77) rather than reimplementing any of the tax/gratuity
math in TypeScript. `lib/settlement.ts` only contains **auxiliary, non-overlapping** calculations: notice-period
day-count stats (`computeDayCountStats`), notice-pay shortfall (`computeNoticePay`), and leave-encashment
eligibility/commit (`commitLeaveEncashment`/`previewEncashableLeaveBalance`, which itself calls the real
`leave_encash_prc` stored procedure, not a JS reimplementation). None of these duplicate what
`final_settle_pay_prc` computes internally (PT/gratuity/TDS/bonus) — there is no parallel formula to drift out of
sync with the stored procedure, because the stored procedure is the actual source of truth being invoked, not
reverse-engineered.
Classify: **INTENTIONAL-LOOKING / best-practice** — this is the correct approach and eliminates the class of risk
the audit brief was most concerned about (F&F math errors from a JS reimplementation silently diverging from the
documented algorithm). The code comment at `approve/route.ts:18-22` self-documents that the procedure depends on
`attendance_register` data not yet built in this app phase, and will likely return "Attendance not verified" until
Attendance is implemented — this is flagged as expected, not a bug, and is visible to the HR user via
`settlementResult.settlementMessage` in the UI (`page.tsx:644-646`).

One caveat: `leave_encash_prc`'s 5th parameter is documented in the code comment (`approve/route.ts:24-26`) as
`IN` not `OUT` in the live DB, meaning no result is read back from it (matching legacy's own behavior of never
checking its output) — this is a knowingly-ported legacy limitation, not a new gap.
Classify: **INTENTIONAL-LOOKING** (documented, matches legacy's own silent-failure pattern, not a regression).

### 2.6 `termination.remarks varchar(30)` truncation bug — structurally avoided in the new code paths (not by widening the column)

Legacy bug (code-logic-report.md §7.10 §6, line ~5464): `termination.remarks` is `varchar(30)` but legacy code
writes full `Reason_Desc` strings (sourced from a `varchar(600)` column) into it, silently truncating.

New app: grepped every `remarks` write in the resignation flow —
- `rizo/src/app/api/resignations/route.ts:100` (POST, initial insert): `remarks` is hardcoded to `''` (empty
  string) in the `INSERT INTO termination` values list. The actual reason description is stored correctly in
  `Reason_desc` (a separate, correctly-sized column — `resignations/[id]/route.ts:63` also writes to
  `Reason_desc`, never `remarks`).
- `rizo/src/app/api/resignations/[id]/withdraw/route.ts:30`: `remarks = 'Employee cancelled'` (19 characters) —
  fits comfortably within `varchar(30)`.
No other write path touches `termination.remarks`. So although the column itself was not widened/migrated to
`text`, the bug class is avoided because the new code never routes long free-text into that column — it correctly
uses `Reason_desc` for the actual reason text and only ever writes short fixed strings (or empty) into `remarks`.
Classify: **INTENTIONAL-LOOKING** (bug avoided by correct column usage, though note this is fragile: if a future
change starts writing `reason_desc` into `remarks` — e.g. by copy-pasting the withdraw pattern with a longer
string — the truncation would resurface since the underlying schema constraint is unchanged. Migration should
still consider widening `remarks` or documenting that it's meant to be short-code only).

### 2.7 `$str_company_code = 'KWMT'` assignment-as-condition bug — not applicable; new app has no company-code branch in this logic at all

Legacy bug (code-logic-report.md §7.10 §4, line ~5380): `FullandFinalsettlementController::leaveadjustment`
(`:496`) has `if ($str_company_code == 'DEMO' || $str_company_code = 'KWMT')` — assignment instead of comparison,
making the `else` branch permanently unreachable for all non-DEMO/non-KWMT tenants, silently changing
`working_days_settled`/`payroll_days` update behavior.

New app: `rizo/src/app/api/resignations/[id]/finalize/route.ts:56-67` writes `working_days_settled`,
`leave_balance`, `approved_balance`, `days_attendance`, `encashed_days`, `payroll_days`,
`amt_paid_by_empaddition`, `amt_paid_by_empdeduction` unconditionally from admin-supplied form input
(`finalizeForm` in `page.tsx:740-755`) with **no company-code branching at all** — every tenant gets the same
single code path. This is a structural improvement: TypeScript's `=` vs `==`/`===` in an `if` condition is also a
compile error (not silently truthy) unlike PHP, so this exact bug class cannot occur, and the new implementation
sidesteps it entirely by not having tenant-specific branches to begin with.
Classify: **INTENTIONAL-LOOKING** (bug class structurally impossible in TS, and the tenant-hardcoding this bug was
embedded in has been removed rather than ported — a genuine simplification, though also a loss of whatever
DEMO/KWMT-specific behavior *was* intended, unverified either way per the legacy report's own caveat that the bug
makes the intended behavior unknowable from the code alone).

### 2.8 Resignation workflow state machine — restructured, not identical to legacy, but internally consistent

Legacy state machine (code-logic-report.md §7.10 §3c): binary `termination.status` (1=active/0=cancelled) plus
separate `is_authorized`/`is_approved` Y/N flags on `termination`, and a `Resignation_status` on
`resignation_requests` (`Applied` default, `Cancelled` on withdrawal) — with the significant gap that nothing in
legacy code ever sets an intermediate "approved" or "completed" status string; the workflow's real state lives in
the flag combination rather than a clean status enum, and (per §3c, line ~5370) there is no code path that
"finalizes" a resignation into a terminal state at all beyond the ad-hoc `Terminate()` admin action.

New app (`rizo/src/app/(dashboard)/employees/resignations/page.tsx:32`,
`rizo/src/app/api/resignations/[id]/{checklist,approve,finalize}/route.ts`): the new app defines an explicit,
5-value `Resignation_status` state machine: `Applied → HR Reviewed → Approved → Completed`, plus `Cancelled` via
withdraw. Each transition is a distinct, named endpoint:
- Submit → `Applied` (`resignations/route.ts` POST)
- Checklist → `HR Reviewed` (`checklist/route.ts:50`, also sets `termination.is_authorized='Y'`)
- Eligibility check (read-only gate, `eligibility/route.ts`) + Approve → `Approved`
  (`approve/route.ts:90`, sets `termination.is_approved='Y'` and runs the real F&F computation)
- Finalize → `Completed` (`finalize/route.ts:69`, flips `emp_details.status=2`)
- Withdraw → `Cancelled` at any point before Completed (`withdraw/route.ts:26`)

This is a genuine restructuring of legacy's implicit flag-soup into an explicit named state machine — a designed
improvement, not a 1:1 port. It does preserve the underlying legacy field semantics (`is_authorized`/`is_approved`
Y/N flags are still set at the corresponding steps, `code comment in checklist/route.ts:7-9` explicitly notes it
"collapses legacy's separate authorise-vs-accept sub-steps into one checklist action for simplicity"). The new
state machine's approve→finalize split is also new: legacy's `Terminate()` did authorize+approve+status-flip in
one shot; the new app requires two separate confirmed steps (`approve` then `finalize`) before the employee is
actually deactivated — this is stricter/safer than legacy, not less.
Classify: **INTENTIONAL-LOOKING** (documented, deliberate redesign — confirm with product that the 4-stage named
flow is the desired UX rather than a 1:1 legacy port, but nothing here looks like an accidental behavior change).

One quirk worth flagging: `resignations/[id]/route.ts:60-88` (PUT edit) has a defensive branch for
"resignation_requests rows that predate this app and lack a companion termination row" — backfilling a termination
row on edit if missing. This anticipates data created outside the app's own POST flow (e.g., during a cutover/
migration import) and is a reasonable defensive measure, not a bug.

### 2.9 Promotion approval fan-out — matches legacy's helper-by-helper design, executed transactionally (improvement)

Legacy (`user-side-report.md` line ~4057-4063, `backend-report.md` line ~4359-4362): `approvesave()` conditionally
calls `chnageType`/`changeDesignation`/`changeDepartment`/`changeBranch`/`addToShift`/`addToLeave`/`promotionWage`/
`addToSuperior`/`addToSalary` as **separate, non-transactional** calls — a partial failure partway through leaves
the employee record in a mixed state (some fields updated, others not). Also flagged: `chnageType` inconsistently
skips mirroring into `emp_config` (commented out), and `savepromotions()` references an undefined `$result`
variable (backend-report.md line ~4485).

New app (`rizo/src/app/api/promotions/[id]/route.ts:54-145`): the entire approval fan-out (direct `emp_proff`
field updates, `emp_config` SHIFT/LEAVE/SALARY policy inserts, CTC insert, `sal_structure_distribution_fn` call,
HIERARCHY `emp_config` insert + `attr1` update) runs inside a single `connection.beginTransaction()` /
`commit()`/`rollback()` block — a partial failure rolls back the whole approval atomically, which legacy does not
guarantee. This is a clear improvement over legacy's non-transactional fan-out.
Classify: **INTENTIONAL-LOOKING / improvement** (no discrepancy to flag as a bug; note for completeness that this
means new-app promotion approval is strictly safer than legacy under partial-failure scenarios).

---

## Summary

- **Resignation → Full & Final Settlement is migrated with high fidelity to the legacy schema and, critically, to
  the legacy settlement math** — `lib/settlement.ts` and the `approve` route call the real `final_settle_pay_prc`
  and `leave_encash_prc` stored procedures rather than reimplementing the tax/gratuity/PT formulas in TypeScript,
  which eliminates the highest-risk category of discrepancy the audit was designed to catch.
- All three concrete legacy bugs cited in the brief (`agree` no-op, `KWMT` assignment-as-condition,
  `remarks varchar(30)` truncation) are **not reproduced** in the new app — either structurally impossible in TS,
  or avoided by not routing long text into the narrow column, or the underlying feature was dropped rather than
  ported buggy.
- The employee-status-flip gap (employee-initiated path never flipping `emp_details.status` in legacy) **cannot
  recur** in the new app because the two legacy creation paths were unified into one workflow with a single,
  gated finalize step.
- Two significant **scope/access-model changes** need product confirmation, not because they look like bugs, but
  because they're real behavioral departures from legacy: (1) Promotions collapsed from a per-approver-routed,
  manager-self-service queue to a single-tier HR-only approval; (2) Resignations collapsed from an employee
  self-service (ESS) flow to an HR-only data-entry flow. Both are plausibly intentional given this migration
  phase's scope (admin console before ESS), but should be explicitly confirmed rather than assumed.
- PromoController and ResignationRequestController/FullandFinalsettlementController are **MIGRATED**;
  SelfReview/TeamReview/HierarchyReview/Performance/ExceptionRule clusters are **NOT STARTED**, consistent with
  the prior finding that their backing tables are entirely absent from the available schema dump.

## Files referenced (new app)

- `rizo/src/app/(dashboard)/employees/promotions/page.tsx`
- `rizo/src/app/api/promotions/route.ts`
- `rizo/src/app/api/promotions/[id]/route.ts`
- `rizo/src/app/(dashboard)/employees/resignations/page.tsx`
- `rizo/src/app/api/resignations/route.ts`
- `rizo/src/app/api/resignations/[id]/route.ts`
- `rizo/src/app/api/resignations/[id]/approve/route.ts`
- `rizo/src/app/api/resignations/[id]/checklist/route.ts`
- `rizo/src/app/api/resignations/[id]/eligibility/route.ts`
- `rizo/src/app/api/resignations/[id]/finalize/route.ts`
- `rizo/src/app/api/resignations/[id]/preview/route.ts`
- `rizo/src/app/api/resignations/[id]/slip/route.ts`
- `rizo/src/app/api/resignations/[id]/withdraw/route.ts`
- `rizo/src/app/(print)/employees/resignations/[id]/slip/page.tsx`
- `rizo/src/lib/settlement.ts`

---

### 2.8 Not-Started Clusters (Full Sweep)

# Not-Started Sweep — Legacy Feature Clusters Absent From New App

Scope: confirm and catalog every legacy feature-area cluster that has zero (or only
setup/master-data) presence in the new Next.js app at `rizo/src`, for the coverage-map table.
Confirmed against the given route inventory plus spot-check greps run directly against
`rizo/src/app` (`leave`, `payroll`, `salary`, `advance`/`loan`, `expense`, `report`,
`site`/`project`, `gatepass`/`outpass`, `statutory`, `timesheet`, `purchase`/`vendor`/`stock`/`supplier`).
Every hit returned was an incidental keyword match inside `attendance/register`, `employees/*`,
`resignations/*`, `promotions`, `setup/*`, `dashboard`, or `login` — none is a dedicated
route/page/API for the clusters below. No `gatepass`, `outpass`, `timesheet`, or
`purchase/vendor/stock/supplier` hits at all.

Report section references use the shared cluster numbering across all three reports:
`user-side-report.md` §N, `backend-report.md` §2.N, `code-logic-report.md` §7.N.

---

## Coverage Table

| Legacy item | Legacy report ref | New app location | Status |
|---|---|---|---|
| **Leave Management** | | | |
| LeaveRequestController (apply/authorize/approve/reject/cancel, admin side) | user-side §4 (L1068); backend §2.3 (L1299); code-logic §7.3 (L2920) | none | NOT STARTED |
| EmployeeLeaveRequestController (ESS leave apply/cancel, duplicate `grandLeave()` state machine of LeaveRequestController) | user-side §4; backend §2.3; code-logic §7.3 | none | NOT STARTED |
| EmployeeLeavesController (read-side leave listing, uses `LeaveEntries` model) | user-side §4; backend §2.3; code-logic §7.3 | none | NOT STARTED |
| LeaveEncashmentRequestController (separate, simpler single-step `is_approved` flip on `leave_encashment_master`) | user-side §4; backend §2.3; code-logic §7.3 | none | NOT STARTED |
| LeavePolicyController (policy configuration: leave types, accrual rules, sanction-by hierarchy) | user-side §4; backend §2.3; code-logic §7.3 | `app/api/setup/leavepolicy-groups/route.ts` exists (group-level master data only; per-policy accrual/type config not covered) | **PARTIAL** |
| LeaveapiController (OTP/login/curl utility endpoints — not `AppController`-gated, mislabeled as "leave" but is auth/mail utility) | code-logic §7.3 (L2920, headline finding 4) | none | NOT STARTED |
| **Payroll, Salary & Tax** | | | |
| PayrollController (live payroll run screen — the actually-reachable payroll flow) | user-side §5 (L1708); backend §2.4 (L1832); code-logic §7.4 (L3313), state machine at code-logic §3.2 (L3589) | none | NOT STARTED |
| PayrollProcessController (orphaned 6-stage pipeline, not reachable from live nav — flagged dead-ish in reports) | user-side §5 (L1749); backend §2.4 (L1864); code-logic §7.4 | none | NOT STARTED |
| SalaryProcessingController (payroll feature hub / plan-gated launcher) | user-side §5 (L1772); backend §2.4; code-logic §7.4 | none | NOT STARTED |
| SalaryHeadsController / SalaryHeadItems (pay-component master + line items) | user-side §5; backend §2.4; code-logic §7.4 | none | NOT STARTED |
| SalaryStructureController (structure definition/versioning) | user-side §5; backend §2.4; code-logic §7.4 | `app/api/setup/salary-structures/route.ts` exists (setup-only master data; per-employee salary-structure assignment/CTC breakup not covered) | **PARTIAL** |
| SalaryComponentUploadController (bulk Excel upload of salary components) | backend §2.4; code-logic §7.4 | none | NOT STARTED |
| SalaryIncrementController (increment cycles, `SalaryIncrement`/`SalaryIncrementDetails`) | user-side §5; backend §2.4; code-logic §7.4 | none | NOT STARTED |
| TaxController / TaxHeadsController / TaxationController (tax-head config, slab computation) | user-side §5, "Tax-related controllers" (L1880); backend §2.4; code-logic §7.4 | none | NOT STARTED |
| EmpTaxController / EmployeeTaxController (per-employee tax regime, declarations, tax transaction ledgers `EmpTaxSalTrans(New)`, `EmployeeTaxsalsum(New)`) | user-side §5; backend §2.4; code-logic §7.4 | Note: `employees/[id]/tax-declarations` exists in new app per the given inventory but that is the employee-facing declaration capture only, not the tax computation/slab engine | NOT STARTED (computation engine) |
| FinancialYearController | user-side §5; backend §2.4; code-logic §7.4 | `app/(dashboard)/setup/financial-year` exists | **MIGRATED/PARTIAL** |
| ArrearController (arrear/back-pay processing, mirrors live 2-stage PayrollController flow; underlying `payroll_arrear_master` table confirmed **absent from schema dump** — code-logic flags as possibly stale/unreachable) | user-side §5, "ArrearController.php — parallel mini payroll-run" (L1938); backend §2.4 (L2125); code-logic §7.4 | none | NOT STARTED |
| VariableController (variable-pay upload) | backend §2.4; code-logic §7.4 (uncovered-models sweep, code-logic §7.13 L5820 also lists `EmployeeVariableUpload`) | none | NOT STARTED |
| FixedPaymentUploadController (`EmployeeFixedPaymentUpload`) | code-logic §7.13 (L5820, uncovered-models list) | none | NOT STARTED |
| EmployeeIncrementReportsController | user-side §5/§7; backend §2.4/2.6; code-logic §7.4/§7.6 | none | NOT STARTED |
| YearEndController (annual leave/financial year-end closing) | user-side §13, "YearEndController.php — annual leave/financial year-end closing" (L2015); backend §2.4; code-logic §7.4 | none | NOT STARTED |
| **Advances, Loans & Expenses** | | | |
| AdvanceController (petty-cash-style admin advances against `advance_expense`; table confirmed **absent from schema dump** — likely dead/at-risk feature) | user-side §6 (L2109), "1. AdvanceController" (L2109+); backend §2.5 (L2284); code-logic §7.5 (L3740) | none | NOT STARTED |
| EmployeeadvanceController ("Salary Advance" — real, live surface; `emp_advance` table confirmed present; has a company-specific eligibility formula for GLET/ABSG and a suspected `$this->setup()` fatal-error bug for `user_group==2`) | user-side §6, "2. EmployeeadvanceController" (L2109+); backend §2.5; code-logic §7.5 | none | NOT STARTED |
| EmployeeLoanController / EmployeeLoanInfo / LoanEmi (EMI schedules) | user-side §6; backend §2.5; code-logic §7.5 | none | NOT STARTED |
| EmployeeExpensesController (core two-level approval expense workflow) | user-side §6, "7. EmployeeExpensesController — the core two-level approval workflow" (L2218); backend §2.5; code-logic §7.5 | none | NOT STARTED |
| ExpenseItemController / ExpenseTypeController / ExpenseHead (item/category catalog; `expense_item`, `expense_heads`, `ExpenseTypeDetails`, `ExpenseTypePaymentDetails` tables confirmed **absent from schema dump** — flagged as possibly dead in current tenant) | user-side §6, "9. ExpenseItemController" (L2261); backend §2.5; code-logic §7.5 | none | NOT STARTED |
| ProjectExpensesController (parallel single-level-approval + PO features) / ProjectExpenseReportController | user-side §6, "13. ProjectExpensesController" (L2322), "14. ProjectExpenseReportController" (L2347); backend §2.5; code-logic §7.5 | none | NOT STARTED |
| VehicleExpensesController (`transportation_expense`/`transportation_payments` tables confirmed **absent from schema dump**) | user-side §6; backend §2.5; code-logic §7.5 | none | NOT STARTED |
| PaymentApprovalsController | user-side §6 (scope list, L2113); backend §2.5; code-logic §7.5 | none | NOT STARTED |
| **Reports subsystem** — ~30 controllers, all NOT STARTED (ReportsController, ReportController, AttendanceReportsController, SalaryReportsController, StatutoryReportController, TaxReportController, HierarchyReportController, EsiEpfReportController, EmployeeAdvanceReportsController, EmployeeLoanReportsController, EmployeeExpenseReportsController, StockReportController, and ~20 other niche per-cluster report controllers) — single consolidated row per instructions | user-side §7 "Reports" (L2412) and the many per-cluster "*Report*" controller entries scattered through the doc (e.g. L3521 StockReportController, L2347 ProjectExpenseReportController); backend §2.6 (L2692); code-logic §7.6 (L3980) | none — no `/reports` route/API of any kind found in the given inventory or grep sweep | NOT STARTED |
| **Site / Field Work & Project Management** | | | |
| SiteAttendanceController + SiteAttendanceApplyController (job-site attendance/rates; `site`/`site_transactions`/`site_history` tables) | user-side §9 (L2759); backend §2.8 (L3428); code-logic §7.8 (L4649) | none | NOT STARTED |
| ProjectController (confirmed by all 3 reports to reuse the **same** `site` table as SiteAttendanceController — "Project" and "Site Attendance" are the same entity with two front-ends, per user-side §9 L2777-2779; adds a `project_activity` work-log table, itself absent from the schema dump) | user-side §9 (L2777); backend §2.8; code-logic §7.8 | none | NOT STARTED |
| SiteWorkController / FieldSurveyController (separate `efsr_site`/`efsr_tickets`/`efsr_equipments_master` concept — field-survey/equipment site master, distinct from `site`; these tables also confirmed **absent from schema dump**, flagged as the single biggest schema-cross-check risk in the cluster) | user-side §9 (L2919, L2946); backend §2.8; code-logic §7.8 (L4649, "1.2 efsr_site cluster") | none | NOT STARTED |
| MaterialController / MaterialRequestController (`material_request`/`mr_details`) | user-side §9; backend §2.8; code-logic §7.8/§7.9 | none | NOT STARTED |
| GatePassController / OutPassController (`gate_pass` table also absent from schema dump) | user-side §9; backend §2.8; code-logic §7.8, cross-cutting headline note (L28) | none | NOT STARTED |
| DeviceController (site/field device management) | user-side §9 (scope list, L2763); backend §2.8; code-logic §7.8 | none | NOT STARTED |
| **Statutory & Access Admin (non-auth parts)** | | | |
| StatutoryRegistersController (PF/ESI/Wage-Sheet/Muster-Roll compliance exports) | user-side §13, "Statutory, Access Admin & Mobile API" (L4672); backend §2.12 (L4836); code-logic §7.12 (L5696) | none | NOT STARTED |
| StatutoryUploadsController (statutory compliance file uploads) | user-side §13; backend §2.12; code-logic §7.12 | none | NOT STARTED |
| (UserAccessController/UserCredentialsController — account/permission admin) | code-logic §7.12 | — | out of scope here, see auth-cluster research pass |
| **Timesheet** | | | |
| TimesheetController — `registerbook()`/`empregisterbook()` (monthly per-day attendance grid, present/leave/holiday/LOP counts, editable-lock via `attendance_register.isdelete`), `listregisterentries()`/`listverifiedregisterentries()` (unverified/verified tabs), `verifyregisterentries()`, `checkifregistercanverify()`, `editPunch()`/`bulkipdatestatus()` (delegates actual mutation to `EditAttendanceController::chnagestatus()`) | user-side §14, "Timesheet & Full Controller Coverage Check" (L4900); backend §2.13 (L5047); code-logic §7.13 (L5820) | `app/(dashboard)/attendance/register/page.tsx` + `app/api/attendance/register/*` — confirmed same concept: month picker, unverified/verified tabs, per-day grid edit, verify/unverify actions, leave-options lookup for a day cell — this is a strong conceptual match to legacy `TimesheetController`'s "register book" | **MIGRATED-under-a-different-name** — cross-reference to the `attendance/register` feature (covered by another research pass); no separate "Timesheet" menu/route is needed in the new app since it is the same monthly-attendance-grid concept as `AttendanceRegister`. Note: legacy `TimesheetController` had a second landing/branch-picker UI (`index`/`empindex`) and its own copy of the verify/edit logic duplicated from `EditAttendanceController` — worth a diff-check by the attendance-register research pass to ensure no unique legacy behavior (e.g. the bulk-update audit-only write-path noted in code-logic §7.13) was dropped, but the core feature is not a gap. |
| **Full & Purchasing chain beyond Assets** | | | |
| ItemController (`item_master`) | backend §2.9 (L3832); code-logic §7.9 (L4966) | none beyond the simple top-level `assets` feature | NOT STARTED |
| PurchaseOrderController / DirectPurchaseOrderController (`purchase_order`, `po_item_details`, `DirectPurchaseOrder`/`DirectPurchaseOrderDetails`) | user-side §7, "9. PurchaseOrderController.php" (L3411); backend §2.9; code-logic §7.9 | none | NOT STARTED |
| GoodsReceivedNotesController (`goods_receved_notes`, `gr_item_details` — duplicate models `GrItemDetails`/`GoodsReceivedNotesitem` on same table) | backend §2.9; code-logic §7.9 | none | NOT STARTED |
| VendorController / SupplierMasterController (`Controller/VendorController.php` — Vendor/Contact Master, Vendor Item Purchase & Allocation) | user-side §7, "7. VendorController.php" (L3363); backend §2.9; code-logic §7.9 | none | NOT STARTED |
| StockReportController (generic report engine, HR/Payroll + Inventory reports — also part of the Reports consolidated row above) | user-side §13, "13. StockReportController.php" (L3521); backend §2.9/§2.6; code-logic §7.6/§7.9 | none | NOT STARTED |
| StockTranferController / StockmanagementController / StockDetails / StockAdjustment(Details) (stock transfer, adjustments, on-hand tracking) | backend §2.9; code-logic §7.9 | none | NOT STARTED |
| StoreController (`store_master`) | backend §2.9; code-logic §7.9 | none | NOT STARTED |
| VehicleController (`vehicle_master` table confirmed **absent from schema dump**) | backend §2.9; code-logic §7.9, cross-cutting headline note (L28) | none | NOT STARTED |

---

## Relative complexity / risk estimate for scoping the eventual migration plan

- **Highest complexity/risk: Payroll, Salary & Tax.** The reports describe a live `PayrollController` 2-stage run plus an orphaned 6-stage `PayrollProcessController` pipeline, an `eval()`-based (per code-logic §4.1 formula catalog) salary/tax computation engine, 91-plus stored procedures/functions backing calculations (leave/attendance/salary interplay), four SQL VIEWs (`MonthlyCTC`/`GrossSalary`/`TotalDeductions`/`MonthlyAmount`) being treated as writable models in Cake, old-vs-new tax-regime slab tables with structurally different column counts, and multiple schema-dump mismatches (`payroll_arrear_master`, `emp_new_salary_slip`, `component_increment` all referenced by live code but absent from the schema dump — status unverified against the live DB). This cluster has the deepest, most tenant-customized business logic of anything surveyed and should be scoped last/with the most buffer.

- **Moderately-high complexity: Leave Management.** A genuine multi-step approval state machine (`grandLeave()`, duplicated verbatim between admin and ESS controllers), inconsistent column casing/spelling baked into the schema (`ISAutherizedby` typo, `LEAVESTATUS` vs `Leavestatus`), two models mapped to the same table needing consolidation, and an auth-bypassed utility controller (`LeaveapiController`) that needs security re-verification. Moderate volume, but the state-machine logic needs careful behavioral parity testing.

- **Moderately-high complexity: Advances, Loans & Expenses.** Multiple approval-workflow variants (two-level for `EmployeeExpensesController`, single-level for `ProjectExpensesController`), a company-specific (GLET/ABSG) eligibility-ceiling formula involving attendance/prorate logic, and — notably — **7 of 14 models plus 2 phantom table references point at tables absent from the schema dump**, meaning a non-trivial fraction of this cluster may already be dead in the live tenant and needs live-DB verification before any migration effort is spent on it. Actual buildable scope may be smaller than the controller count suggests once that's confirmed.

- **Large but mechanical: Reports subsystem.** ~30 controllers, but the reports note a strongly repeated common pattern (`hrreports→changereporttype→addreportcriteria→loadcriteriaitems→listcriteriaitems→generatereport`) driven by generic `reportcriterias`/`report_audit` metadata tables rather than per-report bespoke logic. High controller count but low logic novelty per controller — a good candidate for a single generic "report runner" abstraction in the new app rather than 30 hand-ported pages. Risk is more about faithfully cataloging every report's specific criteria/columns than algorithmic complexity.

- **Moderate complexity, data-model risk: Site/Field Work & Project Management.** Logic itself (site rate tables, work-log activity) is not deeply complex, but the reports flag this as the **biggest schema-cross-check risk in the entire codebase** — `efsr_site`, `efsr_tickets`, `efsr_equipments_master`, `gate_pass`, and `project_activity` are all referenced by live, actively-developed code (recent "Edited by ..." comments) but entirely absent from `schema/mypayrol_trial.sql`. Building this without live-DB schema introspection first would be guesswork.

- **Lower complexity: Statutory Registers/Uploads and Purchasing/Inventory chain.** Both are largely CRUD-plus-export patterns over well-defined master/transaction tables (PO→GRN→Stock chain is schema-clean per code-logic §7.9, "zero validate/associations but consistent structure"). Purchasing chain has some duplicate-model noise (`Assets`/`AssetsModel`/`AssetsName` triple, `GrItemDetails`/`GoodsReceivedNotesitem` duplicate) to clean up during modeling but no unusual business-logic risk.

- **Not a gap: Timesheet.** As detailed above, this is effectively already covered by the `attendance/register` feature under a different legacy name; treat as a cross-reference/parity-check item for that other research pass rather than new scope.

---

## 3. Architecture Assessment

**This section was written directly from reading the new app's code** (not delegated), covering `rizo/package.json`, `src/lib/*.ts`, `src/types/next-auth.d.ts`, representative `page.tsx`/`route.ts` files, and `src/app/(dashboard)/layout.tsx`.

### 3.1 Stack

- **Next.js 16 (App Router)**, React 19, TypeScript, Tailwind CSS 4.
- **Auth**: NextAuth v4, JWT session strategy, a single `CredentialsProvider` in `src/lib/auth.ts` that internally branches into `tryAdminLogin`/`tryEmployeeLogin` — a direct, deliberate port of legacy's dual admin/employee login (`userGroup` 1 vs 2) rather than two separate NextAuth providers. Session/JWT callbacks propagate `controlPkey`, `companyCode`, `userGroup`, `empFkey`, `loginUserId`, `planId` onto every session (`src/lib/auth.ts:195-217`, typed in `src/types/next-auth.d.ts`) — this is the single source of tenant/role context threaded through the whole app, replacing legacy's session-variable soup (`$this->Session->read('user_group')` etc. scattered through `AppController::beforeFilter()`).
- **Data access**: **no ORM** — raw parameterized SQL via `mysql2/promise`. `src/lib/db.ts` reproduces the legacy dual-database architecture faithfully: one long-lived `controlPool` for the control DB, and a lazily-created, cached-in-a-global-Map per-tenant pool (`getCompanyPool(companyCode)`) that looks up `central_control.user_db`/`Admin_name`/`user_pwd` on first use — the direct TypeScript analog of legacy's `AppController.php:48-95` dynamic `ConnectionManager::create('companydb', ...)`, but using a real connection pool instead of a raw `mysql_connect()` per request, and (in production) using the per-tenant DB credentials from `central_control` rather than one shared credential.
- **Forms**: `react-hook-form` + `@hookform/resolvers` + `zod` for schema validation — this is the intended replacement for legacy's inconsistent (often absent) client/server validation; confirmed present in at least the login page.
- **Data fetching (client)**: `@tanstack/react-query` for all list/detail data in client components (`'use client'` pages call `fetch('/api/...')` inside `useQuery`/`useMutation`, e.g. `src/app/(dashboard)/employees/page.tsx:39-58`), with `queryClient.invalidateQueries` on mutation success. This is a consistent, modern pattern — no ad hoc `useEffect`+`fetch` data loading observed in the files read.
- **Data fetching (server)**: server components (e.g. `(dashboard)/layout.tsx`) call `getServerSession` directly and do server-side redirects — no client-side auth-flicker.
- **Tables**: `@tanstack/react-table` wrapped in a shared `components/data-table/DataTable.tsx` component, used consistently across list pages (employees, and presumably others per cluster reports below).
- **Shared CRUD pattern**: `components/setup/SetupCrudPage.tsx` is a generic reusable component for simple master-data CRUD (branches, departments, designations, grades, etc.) — this is a real architectural strength worth calling out: legacy had ~10+ nearly-identical hand-copied CRUD controllers for this exact category of entity (Branch/Department/Designation/Division/Section/Grade/Category/Bank), and the new app collapses that into one parameterized component + one API-route-per-entity, which is both less code and structurally enforces consistency the legacy app never had.
- **PDF/Excel**: `jspdf`/`jspdf-autotable` for PDF generation (e.g. resignation slip), `xlsx` for import/export (employee bulk import) — replacing legacy's HTML2PDF/PHPExcel.
- **Charts**: `recharts` for dashboard visualizations.
- **Password hashing**: `bcryptjs`, with an explicit **lazy migration-on-login** strategy (`src/lib/auth.ts:22-45`, `verifyAndUpgradePassword`): if a stored hash matches the SHA-1 pattern (legacy's unsalted `Security::hash` output), it's verified against SHA-1 and then immediately re-hashed with bcrypt(12) and written back; otherwise it's verified as bcrypt directly. This is a clean, low-risk password-migration pattern — no forced mass rehash needed, and it correctly targets one of the three legacy reports' explicitly-flagged security gaps (unsalted SHA-1).

### 3.2 Route structure

- Three route groups: `(auth)` (public, login only), `(dashboard)` (authenticated app, gated once at `layout.tsx`), `(print)` (print-friendly document views, e.g. resignation slip — presumably intentionally outside the dashboard chrome).
- API routes under `src/app/api/**/route.ts` largely mirror the page route structure 1:1 (e.g. `(dashboard)/employees/page.tsx` ↔ `api/employees/route.ts`), which is the idiomatic Next.js App Router convention and should be followed for all remaining migration work.
- **No `middleware.ts`.** Page-level auth is centralized once at `(dashboard)/layout.tsx` (a single `getServerSession`+`redirect`, replacing legacy's per-controller-inherited `AppController::beforeFilter()` check). API-route-level auth is **not** centralized — every `route.ts` file re-implements its own `getServerSession` check at the top of each handler (confirmed pattern in `api/employees/route.ts:20-21`). This is a deliberate, defensible choice (Next.js Route Handlers don't share a controller base class the way CakePHP controllers do), but it does mean **auth-check omission is a per-file risk** rather than a structural guarantee — every new API route added during the remaining migration must remember to add this check; there is no framework-level safety net if one is forgotten. (The Fidelity Check section below reports whether any existing route already forgot it.)

### 3.3 Role/permission enforcement pattern

`session.user.userGroup` (1=admin, 2=employee) is checked ad hoc per-route, matching legacy's own coarse-only enforcement model (see `code-logic-report.md` §1: legacy's fine-grained `user_access`/`menu_id` permission layer was UI-only, never server-enforced, across the *entire* legacy app). The new app's `employees/access`/`employees/menu-allocation` features are where this gets tested — see the Auth cluster findings below for whether the new app perpetuates or resolves that legacy gap.

### 3.4 Config-driven behavior: a clean two-tier pattern already emerging

Two parallel mechanisms exist, and the codebase is explicit about the intended relationship between them:

1. **`src/lib/features.ts`** — a proper DB-backed feature-flag system: `plan_feature`/`features` tables in the control DB, keyed by `planId` (already threaded through the session, `src/types/next-auth.d.ts:11,24,36`) and a `featureKey` string. This is the correct, scalable answer to legacy's confirmed mess of "four separate, unreconciled plan columns across different tables" (`comp_contact_info.plan`, `central_control`, `hrm_menu`, control-DB `user_credentials` — see `code-logic-report.md` §1.4).
2. **`src/lib/company-config.ts`** — a small, explicitly-labeled **temporary** shim: a hardcoded `companyCode`-keyed lookup table reproducing five specific legacy tenant-hardcoded behaviors (hierarchy-dashboard visibility, an "ABS-style" branch-filtering variant, site-management, increment/site-end-date notifications) verbatim from legacy's `if (company_code == 'GLET' || ...)` chains. The file's own header comment states the intended end-state: **"Long-term: replace with plan_feature records in control DB"** — i.e. migrate these five flags into the `features.ts` system over time. This is a sound, self-documenting pattern: **new work should default to `features.ts`/`plan_feature`, and only fall back to extending `company-config.ts` for behaviors that are provably tenant-identity-specific rather than plan-tier-specific** (the distinction legacy never made, per `code-logic-report.md` §1.5's finding of 60+ hardcoded company-code branches with no consistent semantic behind them).

### 3.5 Fidelity-to-legacy discipline

Source comments in at least `src/lib/empId.ts` explicitly cite the legacy controller/method they port (`"Mirrors legacy loadEmpProfDetails() (EmployeeController.php / EmployeeJoinController.php, byte-identical in both)"`) and document a deliberate, evidence-based deviation from a literal reading of the legacy source (numeric-only `emp_id` generation, confirmed against real dev data rather than assumed from a stale code comment). This is a good convention to continue for the remaining migration: **cite the legacy source location in a comment whenever porting a non-trivial business rule**, and note explicitly when/why the new implementation deviates.

### 3.6 Inconsistencies already present worth flagging

(Full detail in the per-cluster fidelity sections below; noted here as they bear on "which convention to follow" for remaining work.)

- The `employees/assets` vs top-level `assets` split (two API surfaces for what may be one underlying concept) needs a clear ownership decision before more Asset/Inventory work is built on top of it — see §7.6 below.
- Whether `SetupCrudPage.tsx`'s generic pattern is used for *every* simple master-data entity or only some (a few setup API routes exist with no corresponding UI page yet, e.g. `divisions`, `sections`) should be resolved — either finish the UI for the ones missing it, or document why they're intentionally API-only.

---

## 4. Risk List

Ranked by likelihood of causing a real production incident (data corruption, user lockout, or silent financial/compliance error) if the app were deployed with the current gaps as-is. Each entry cites the fidelity-check finding it's drawn from.

### 4.1 Critical — will cause a support incident on first real use

1. **Password reset permanently locks the user out.** `rizo/src/app/api/employees/access/[id]/route.ts:31-38` sets `reset_login_flag='Y'` on admin-initiated password reset, but nothing anywhere in the codebase ever clears it back to `'N'`, and `src/lib/auth.ts:58,126` treats `reset_login_flag==='Y'` as an unconditional login rejection — indistinguishable from a wrong password, with no reset-link/token flow to complete the reset. **Every password reset an admin performs today permanently locks that account.** This is the single highest-priority fix in this entire report — it blocks the most basic account-recovery workflow the app has. (§2.1, Auth cluster.)

### 4.2 High — silent correctness/data-integrity risk

2. **Employee onboarding validation is drastically weaker than legacy.** The new join/onboard flow requires only `first_name`; legacy required ~15 fields before allowing onboarding to proceed, and computed/emailed an onboarding-completion percentage that no longer exists. Risk: incomplete employee records reach "active" status with missing data that downstream payroll/statutory features (once built) will assume is present. (§2.2, Employee cluster.)
3. **Grade's guarded-delete was silently dropped.** Legacy blocked deleting a Grade still assigned to employees; the new app's grade-delete route has no such check, so a grade in active use can be deleted, leaving employee records with a dangling by-convention foreign key (the schema has no enforced FK constraints anywhere, so this fails silently rather than erroring). Branch/Department/Designation never had this guard in legacy either (an inconsistency legacy itself had), but Grade specifically regressed. (§2.4, Company Setup cluster.)
4. **Branch-code uniqueness enforcement was dropped along with the race-condition fix.** The new Branch save no longer auto-generates `branch_code` via the legacy trigger-and-PHP-both-write race, which is a genuine fix — but nothing replaced the underlying uniqueness guarantee, so duplicate branch codes are now possible where legacy (accidentally, via its trigger) prevented them. (§2.4, Company Setup cluster.)
5. **Employee status state machine narrowed from 3 values to a 2-value toggle** that can never reach legacy's status `0` (a distinct "inactive" state from "terminated") — the new app's "resigned" employees are presumably being written with whatever generic non-1 value the toggle uses. This will matter as soon as Payroll (not yet built) needs to distinguish "temporarily inactive" from "terminated" employees for exclusion logic. Not an active bug yet because nothing downstream depends on the distinction — but it's a data-model decision baked in now that will be expensive to unwind later. Flag before Payroll work begins. (§2.2, Employee cluster.)

### 4.3 Medium — functional regression, not data-integrity risk

6. **The "Pending Leave Approvals" dashboard widget is gated to `userGroup===1` (admin) only**, while legacy's approval authority (and hence who should see this widget) was determined per-row by `ISAutherizedby`/`APPROVEDBY` — meaning non-admin managers/team-leads with real approval authority in legacy currently see no approval widget in the new dashboard at all. Tied to the not-yet-built hierarchy dashboard, so may resolve naturally once that's built — but worth confirming it's understood as temporary, not final. (§2.5, Dashboard cluster.)
7. **Birthday/anniversary notification reproduces legacy's exact year-boundary date-wraparound bug** (`rizo/src/app/api/dashboard/events/route.ts:13-31`) rather than the already-fixed CASE-based rollover logic that exists elsewhere in the *same legacy codebase* (`DashboardNewController.php:570-638` per code-logic-report.md). Low severity (a cosmetic notification silently not firing for ~1 week around each year boundary) but zero-cost to fix now since a correct reference implementation already exists in the legacy code. (§2.5, Dashboard cluster.)
8. **`user_access`/`emp_menu` permission-tree cascade logic was dropped**, and the underlying permission system remains — as in legacy — never actually enforced server-side (only UI-hidden). This is not a regression (legacy had the identical gap), but it's flagged as medium risk because the new app is the opportunity to close a gap legacy never did, and every week it stays open is a week more surface area (more routes) it needs to be retrofitted onto later. (§2.1, Auth cluster.)

### 4.4 Low — scope/completeness gaps needing a decision, not a fix

9. Tenant-specific hardcoded notifications (site-expiry, CTC-increment reminders) dropped entirely — ambiguous whether the specific tenants that relied on them still need them (§2.5).
10. Document-merge token set narrowed from ~65 legacy placeholders to ~40 (Employee/Company/Branch only; Supplier/Customer/Others/image-composite dropped) (§2.6).
11. Promotions narrowed from per-approver-routed + manager self-service to single-tier HR-only approval; Resignations narrowed from employee self-service to HR-only data entry — both plausibly intentional for this migration phase, need explicit product sign-off before treating as final (§2.7).
12. `termination.remarks` truncation bug (legacy: `varchar(30)` silently truncates) is not reproduced today (new code uses a different column) but the underlying column is unchanged — fragile, not future-proofed, if anything ever writes to that column directly (§2.7).

### 4.5 Confirmed non-issues (explicitly verified, not reintroduced)

For completeness — these were legacy security bugs specifically checked for and confirmed **absent** from the new app:
- `InfoController`'s unauthenticated `phpinfo()` dump — no equivalent route exists (§2.5).
- SSO-bridge password-in-URL pattern (legacy `AccessController.php`) — no equivalent route exists (§2.1).
- Unauthenticated leave-API endpoints (legacy `LeaveapiController`) — moot, Leave Management is NOT STARTED (§2.8).
- 95 of 96 API route files correctly call `getServerSession`; the one exception is the NextAuth handler itself, which is correct by design (§2.1).

---
