# RIZO Migration — Forward-Looking Migration Plan

**Scope:** this is the plan for the REMAINING migration work, built on top of `reports/current-state-report.md` (coverage + fidelity audit of the new Next.js app at `D:\Projects\RIZOMigration\rizo\` against the three legacy-analysis reports: `user-side-report.md`, `backend-report.md`, `code-logic-report.md`). It does not re-litigate what's already built — see current-state-report.md for that. It covers: (1) a fix-first list of discrepancies to resolve before building more on top of them, (2) the remaining migration sequenced by dependency, (3) a legacy-to-new mapping table with high-risk business rules for everything not yet started, (4) a data migration plan, and (5) every open question that needs a human decision.

---

## 1. Fix-First List

### Critical / Auth-blocking

1. **Password reset creates a permanent lockout** — Cluster 2.1 (Auth) — LIKELY BUG
   - Legacy: `code-logic-report.md` §7.12 intro — `reset_login_flag='Y'` routes user to a reset token/URL flow, not a dead end.
   - New app: `rizo/src/lib/auth.ts:58,126` treats `reset_login_flag==='Y'` as unconditional login rejection (indistinguishable from wrong password); `rizo/src/app/api/employees/access/[id]/route.ts:31-38` sets the flag to `'Y'` on admin password reset but nothing anywhere clears it back to `'N'`, and there is no employee-facing reset UI.
   - Fix requires: build a reset-token/URL completion flow (or an equivalent forced-change-on-next-login screen) and a code path that clears `reset_login_flag` back to `'N'` after completion. Highest-priority item in the whole report — every admin-initiated password reset currently locks the account permanently.

2. **Login lockout counter's daily-reset semantics unverified against legacy** — Cluster 2.1 (Auth) — NEEDS HUMAN DECISION / possible LIKELY BUG
   - Legacy: `code-logic-report.md` §7.12, `LoginManagementComponent.php:167-245` — 3-attempt lockout; exact reset condition not fully re-derivable from the reports.
   - New app: `rizo/src/lib/auth.ts:137-141` resets the failed-attempt counter to 1 whenever the last failed attempt wasn't today (day-boundary reset), reusing `user.end_date` to store "date of last failed attempt."
   - Fix requires: a direct diff against `LoginManagementComponent.php:167-245` to confirm whether legacy resets only on a successful login (making the new day-boundary logic a lockout weakening) or also confirm `end_date` isn't repurposed elsewhere in the new schema.

3. **`user_access`/`emp_menu` permission tree is UI-only, and its cascade logic was dropped** — Cluster 2.1 (Auth) — NEEDS HUMAN DECISION
   - Legacy: `code-logic-report.md` §7.12 §3.1 — asymmetric parent/child cascade (`UserAccessController.php:539-623`, `:835-923`); server-side enforcement never existed in legacy either (`backend-report.md` §3.3).
   - New app: `rizo/src/app/api/employees/menu-allocation/[id]/route.ts:80-101` implements flat independent per-node toggling, no cascade, no add-on/`menu_id=0` handling; no route anywhere reads `user_access`/`emp_menu` to gate access — same UI-only gap as legacy.
   - Fix requires: product decision on (a) whether losing the cascade UX is acceptable, and (b) whether to finally close the legacy server-enforcement gap in this rewrite or knowingly carry it forward.

### Data-integrity issues

4. **Employee status narrowed from a 3-value state machine to a 2-value admin toggle, with relabeled semantics** — Cluster 2.2 (Employee) — LIKELY BUG
   - Legacy: `code-logic-report.md:2357-2377` — `emp_details.status` = 0 inactive / 1 active / 2 terminated-pending-payroll (transient, set only by the Resignation workflow).
   - New app: `rizo/src/app/(dashboard)/employees/page.tsx:35,51-59,94,100-105,150-163` and `rizo/src/app/api/employees/[id]/route.ts:18-19` expose a direct admin "Deactivate/Activate" toggle restricted to `{1,2}` only — status `0` is unreachable, and status `2` is generically relabeled "resigned/inactive," risking collision with the Resignations cluster's own writes to the same column.
   - Fix requires: confirm against the Resignations implementation whether `status=2` writes can collide, and decide whether a direct list-page toggle should exist at all (legacy never exposed one outside the Resignation workflow).

5. **New/edit-employee form validation is drastically weaker than legacy** — Cluster 2.2 (Employee) — LIKELY BUG
   - Legacy: `user-side-report.md:111` — ~15 required fields (name, DOB, nationality, id_card, joining_date, emp_type, dept, designation, branch, shift, holidays, leave, salary, etc.).
   - New app: `rizo/src/app/(dashboard)/employees/new/page.tsx:113-114` marks only `first_name` as required; `rizo/src/app/api/employees/route.ts` POST has no server-side field validation (no zod schema found) beyond a duplicate-check. The separate onboarding/join path enforces more fields, so the app now has two employee-creation paths with very different rigor.
   - Fix requires: add client + server-side required-field validation matching (or deliberately redefining) legacy's field set on the direct-create path.

6. **Onboarding completion-percentage tracker and its completion email were dropped** — Cluster 2.2 (Employee) — LIKELY BUG / NEEDS HUMAN DECISION
   - Legacy: `code-logic-report.md:2457-2496` — `calculateOnboardingPercentage()`/`getOnboardingCompletion()` compute a weighted 7-section completion %, drive a progress ring, and fire `sendOnboardingMail()` on completion.
   - New app: `rizo/src/app/(dashboard)/employees/join/**` has no completion-percentage calculation, no `%` UI, and no completion email anywhere.
   - Fix requires: product decision on whether the new app's single-step onboarding form makes a progress tracker moot, or whether this is a genuine feature loss HR will notice.

7. **Grade's guarded-delete (block if employees assigned) was silently dropped** — Cluster 2.4 (Company Setup) — LIKELY BUG / NEEDS HUMAN DECISION
   - Legacy: Division/Section/Grade blocked deletion when employees were assigned (`code-logic-report.md` §3a); Branch/Department never had the guard.
   - New app: `rizo/src/app/api/setup/grades/[id]/route.ts:26-40` is a bare unconditional soft-delete with no employee-assignment check — a real regression since there is no FK constraint to catch dangling `emp_grade` references either.
   - Fix requires: either add an employee-assignment guard consistently across Branch/Department/Designation/Division/Section/Grade, or make an explicit decision that guards aren't needed (e.g. real FK constraints instead).

8. **Branch-code uniqueness is no longer enforced anywhere** — Cluster 2.4 (Company Setup) — NEEDS HUMAN DECISION
   - Legacy: `BranchController::savebranch()` code + `branches_bi` trigger raced to set `branch_code`, but the trigger at least guaranteed uniqueness via a running counter.
   - New app: `rizo/src/app/api/setup/branches/route.ts:27-40` takes `branch_code` directly from the request body with no duplicate check and no server-side derivation — a genuine improvement over the legacy race, but with no replacement uniqueness guarantee.
   - Fix requires: decide whether `branch_code` needs a uniqueness constraint/generation scheme in the new schema.

9. **32-column `FIELD1..FIELD32` denormalized attendance-day storage was retained as-is, not normalized** — Cluster 2.3 (Attendance) — LIKELY BUG risk-tier / NEEDS HUMAN DECISION
   - Legacy: `code-logic-report.md:2834-2840` explicitly recommends normalizing to `attendance_day(register_id, day_number, status_code)`.
   - New app: `rizo/src/lib/attendance.ts:56-60`, `rizo/src/app/api/attendance/register/route.ts:35`, `.../[dayIndex]/route.ts:44,92-95`, and `comp-off/route.ts:43` all directly read/write the same 32-column EAV layout (including a 32-way `OR` scan for comp-off search).
   - Fix requires: confirm this is a deliberate, permanent decision tied to the dual-DB/stored-procedure-reuse strategy (likely required, since the shared legacy DB and stored procs expect this layout) rather than a deferred cleanup item.

10. **`isdelete`-as-verification-flag polarity was carried forward literally, against the legacy report's own recommendation** — Cluster 2.3 (Attendance) — NEEDS HUMAN DECISION
    - Legacy: `code-logic-report.md:2826-2833` recommends renaming to `verification_status`/`is_verified` and un-inverting the polarity (`isdelete='Y'` = unverified is a confusing name).
    - New app: `rizo/src/app/api/attendance/register/route.ts:29`, `verify/route.ts`, `unverify/route.ts`, `[dayIndex]/route.ts:51`, `shift-planner/route.ts:58-96`, `regularisation/route.ts:76-82` all keep the exact legacy column name/polarity (mitigated by inline comments at each call site).
    - Fix requires: confirm this was a deliberate "don't fork the shared schema" call, not an oversight — likely required by the dual-DB architecture.

11. **Stored-procedure/trigger dependency for attendance math is unverified across tenant databases** — Cluster 2.3 (Attendance) — NEEDS HUMAN DECISION
    - The half-day/late-arrival calculation is correctly delegated to `insert_update_att_reg` and related stored procedures/triggers rather than reimplemented in TypeScript (a good architectural choice), but nothing in the TypeScript layer would catch a missing/renamed stored routine on a given tenant's DB except a runtime SQL error.
    - Fix requires: confirm `device_attandance_bi`, `insert_update_att_reg`, `device_logs_resync_fn`, `device_logs_iteration_fn`, `time_duration_check`, `ot_duration_register_date`, `calculate_ot_allowance_prc`, `leave_transaction_prc` are provisioned and unmodified on every company DB the multi-tenant app will point at.

### Scope / UX / product-decision issues

12. **Bulk-policy allocation has no `MSHIFT` (multishift) handler** — Cluster 2.2 (Employee) — NEEDS HUMAN DECISION
    - Legacy: `EmployeeConfigController` has a dedicated Multishift tab (`user-side-report.md:238,249`).
    - New app: `rizo/src/app/api/employees/bulk-policies/route.ts:11-19`'s `TYPE_CONFIG` map has no `MSHIFT` key — multishift bulk-allocation is unreachable through this endpoint.
    - Fix requires: confirm whether Multishift bulk-assignment is planned for a later pass or deliberately deprioritized; also confirm the double-write (app + DB trigger both writing `emp_proff`) for the 7 covered types isn't a race/ordering risk.

13. **Menu-allocation admin editor may duplicate a write-path that already exists elsewhere** — Cluster 2.2 (Employee) — NEEDS HUMAN DECISION
    - Legacy `EmployeeMenuController` is read-only (composes an ESS user's own nav); the actual `user_access` write-side isn't in this cluster's 15 controllers.
    - New app: `rizo/src/app/(dashboard)/employees/menu-allocation/page.tsx` + API is a full write-side admin tree editor for `user_access` rows.
    - Fix requires: confirm whether the excluded `employees/access` cluster already has a `user_access` write path (making `menu-allocation` a duplicate), and whether legacy's read-side `getDefaultMenus`/`getAddonMenus` (an ESS user's own live nav, with company-specific feature-key remapping) needs a dedicated port.

14. **Beneficiary feature not built; legacy's underlying schema mismatch (table absent from schema dump) is unresolved, not evidence of a fix** — Cluster 2.2 (Employee) — NEEDS HUMAN DECISION
    - Fix requires: verify against a live legacy DB whether the `beneficiary` table is real/populated before building anything, to avoid recreating the mismatch or dropping live data.

15. **No first-run/onboarding wizard exists for new tenants** — Cluster 2.4 (Company Setup) — NEEDS HUMAN DECISION
    - Legacy's `DbConfigController` wizard was already dead/orphaned in legacy, so not porting it directly is defensible, but the underlying "day-1 setup checklist for a brand-new tenant" product need may still be open.
    - Fix requires: confirm with product whether a guided first-run flow is needed, distinct from the always-available standing settings screens that now cover the wizard's individual seed steps.

16. **No plan/subscription/payment gating exists at all; `company-config.ts` is a narrower, explicitly-temporary hardcoded-allowlist shim, not a replacement** — Cluster 2.4 (Company Setup) — NEEDS HUMAN DECISION
    - Legacy `CompanySetupController` implemented Razorpay payment + plan-feature gating; the new app has zero equivalent (no `plan_id`, no payment flow).
    - Fix requires: confirm whether subscription-tier gating and paid upgrades are still needed, and whether `company-config.ts`'s five hardcoded company-code allowlists should migrate into the already-built `features.ts`/`plan_feature` system.

17. **Branch→Department→Designation→Division→Section→Grade hierarchy remains flat/unenforced, exactly as in legacy** — Cluster 2.4 (Company Setup) — NEEDS HUMAN DECISION
    - New app's setup tables carry no FK to `branches` or to each other, faithfully replicating (not fixing) the flatness the legacy report flagged as needing an explicit application-level decision.
    - Fix requires: confirm whether real FK-based hierarchy (e.g. `branch_fkey` on department, `dept_fkey` on designation) is in scope for this migration.

18. **Birthday/anniversary "Upcoming Events" widget reproduces legacy's own year-boundary date bug, not legacy's already-fixed version** — Cluster 2.5 (Dashboards) — LIKELY BUG
    - Legacy has both a buggy string-`BETWEEN` version (`AppController.php:112-116`) and a corrected CASE-based version (`DashboardNewController.php:570-638`) side by side.
    - New app: `rizo/src/app/api/dashboard/events/route.ts:13-31` and `dashboard/page.tsx:34-53` both copy the buggy `%m-%d` string-BETWEEN pattern — employees with birthdays/anniversaries in the last week of December won't appear in the widget around each year boundary.
    - Fix requires: swap to a day-of-year modulo or `CASE WHEN` rollover comparison, mirroring legacy's own existing fix. Low severity, zero-cost fix since a correct reference implementation already exists in the same legacy codebase.

19. **Tenant-specific hardcoded notifications (site-expiry, CTC-increment reminders) dropped entirely** — Cluster 2.5 (Dashboards) — NEEDS HUMAN DECISION
    - Legacy served these to a small hardcoded allowlist of real tenants (6 for site-expiry, 2 for CTC-increment).
    - Fix requires: confirm whether those specific tenants are still active customers who'd notice the regression, or whether this is acceptable to defer/externalize into a future feature-flag system.

20. **Hierarchy/manager dashboard and per-row approval-authority visibility are both absent; "Pending Leave Approvals" widget is gated to admin-only** — Cluster 2.5 (Dashboards) — NEEDS HUMAN DECISION
    - Legacy's pending-approval visibility was determined per-row by `ISAutherizedby`/`APPROVEDBY`, not by a coarse admin/employee split.
    - New app: `dashboard/page.tsx:111`, `stats/route.ts:19` gate the widget to `userGroup===1` only — non-admin managers with real approval authority in legacy currently see no approval widget at all.
    - Fix requires: confirm whether manager-level (non-admin) approval visibility is still a supported role, and whether this is temporary pending the not-yet-built hierarchy dashboard.

21. **Full procurement chain (PO/GRN/Vendor/Item-Master/Stock, 12 of 13 legacy Assets/Inventory controllers) is entirely unbuilt** — Cluster 2.6 (Assets & Documents) — NEEDS HUMAN DECISION
    - Fix requires: confirm with product whether Inventory/Purchasing is a later phase or intentionally out of scope for this rewrite; currently there is no path to buy/receive/track stock items at all, only to register/allocate discrete assets already in the register.

22. **Document-template placeholder set narrowed from ~65 legacy tokens to ~40 (Employee/Company/Branch only)** — Cluster 2.6 (Assets & Documents) — NEEDS HUMAN DECISION
    - `rizo/src/lib/documentMerge.ts:9-11` explicitly documents dropping Supplier/Customer/Others and image-composite/salary-breakup tokens.
    - Fix requires: confirm whether vendor-facing or other dropped template contexts are needed, since they're removed entirely rather than deferred.

23. **Promotions collapsed from per-approver-routed + manager self-service to single-tier HR-only approval** — Cluster 2.7 (Promotions & Resignations) — NEEDS HUMAN DECISION
    - Legacy routed each promotion request to a specific `approved_by` approver and let any manager (`user_group==2`) submit for their own direct reports.
    - New app: `rizo/src/app/api/promotions/route.ts:15,48`, `[id]/route.ts:22` gate everything to `userGroup !== 1` (admin/HR only); `approved_by` is hardcoded to 0 on insert.
    - Fix requires: confirm whether manager-initiated promotion requests are in scope or intentionally centralized to HR.

24. **Resignations collapsed from an employee self-service (ESS) flow to an HR-only data-entry tool** — Cluster 2.7 (Promotions & Resignations) — NEEDS HUMAN DECISION
    - Legacy: employees submitted/withdrew their own resignation via ESS; managers saw requests via `listleaves()`.
    - New app: every resignation route (`rizo/src/app/api/resignations/**`) is gated `userGroup !== 1` — no employee-facing submission path exists.
    - Fix requires: confirm ESS self-service resignation submission is planned for a later phase, not silently dropped.

25. **New hierarchy-reference cleanup on resignation finalize goes beyond legacy behavior, intent unconfirmed** — Cluster 2.7 (Promotions & Resignations) — NEEDS HUMAN DECISION
    - New app (`rizo/src/app/api/resignations/[id]/finalize/route.ts:47-49`) clears other employees' `emp_proff.attr1` hierarchy references and deactivates `HIERARCHY` `emp_config` rows for a terminated manager — legacy's `Terminate()` does not do this cleanup at all.
    - Fix requires: confirm this new behavior (likely desirable) is intended, since legacy may have left dangling references intentionally for audit-trail reasons.

---

---

## 2. Remaining Migration, Sequenced by Dependency

This sequencing follows the architecture already established by the built portion of the app (§3 below): pages under `src/app/(dashboard)/<feature>/page.tsx` mirrored 1:1 by `src/app/api/<feature>/route.ts`, `SetupCrudPage.tsx` reused for simple master-data entities, `react-query`+`DataTable` for lists, `react-hook-form`+`zod` for forms, raw `mysql2` against the same dual-DB (control + per-tenant company) architecture, and per-route `getServerSession` auth checks. No architecture change is proposed — the built 7 clusters demonstrate the pattern scales cleanly (e.g. `SetupCrudPage.tsx` alone collapsed ~8 near-duplicate legacy CRUD controllers into one component), so the remaining work should extend it rather than introduce a second pattern.

**Ground rule for every phase below**: nothing in Phase N should start until the Phase N-1 fix-first items that touch its dependencies are resolved (§1). In particular, do not start Payroll (Phase 3) until the Employee status state-machine question (§1, item tied to Fidelity 2.2) is settled — Payroll's exclusion logic is exactly the downstream consumer that report flagged as at risk.

### Phase 0 — Fix-first (blocking, do before any new module work)

See §1 for the full list. The two items that structurally block later phases:
- **Employee status semantics** (Fidelity 2.2, current-state-report.md) must be resolved before Phase 3 (Payroll), since payroll-run exclusion logic needs a reliable active/inactive/terminated-pending distinction.
- **`reset_login_flag` permanent-lockout bug** (F1) should be fixed before *any* further Access-screen usage in production, independent of module sequencing — it's already live and already blocking every module built so far.

### Phase 1 — Close out partially-built clusters' dependencies for later phases

Do these because Phase 2+ needs them, not because they're next in some arbitrary priority order:

1. **Bank master** (Company Setup, NOT STARTED) — Payroll (Phase 3) needs employee bank-account data for disbursement; build this alongside/just before Payroll setup, using the same `SetupCrudPage.tsx` pattern as Branch/Department/Grade.
2. **Contacts / Beneficiary master data** (Employee cluster, NOT STARTED) — needed as a foreign-key target before Advances/Loans (Phase 4, loan beneficiary/guarantor references) and before the Vendor-master piece of full Procurement (Phase 8). Resolve the legacy `beneficiary`-table-existence question (Fidelity 2.7) first — building against a wrong assumption either recreates the schema mismatch or drops live data.
3. **Subscription/payment plan-feature gating** (Company Setup, NOT STARTED) — extends the already-proven `src/lib/features.ts` (`plan_feature`/`features`) system. Do this before Reports (Phase 6) and before broad `Advances/Payroll` rollout, since plan-gating is the mechanism that will determine which tenants see which of the newly-built modules — retrofitting it after several modules ship means re-touching all of them.
4. **`user_access`/`emp_menu` server-side enforcement** (Fidelity F4, NEEDS HUMAN DECISION) — if the decision is to close this legacy gap rather than replicate it, do it now, before Reports (Phase 6) ships ~30 new report endpoints that will otherwise inherit the same UI-only-enforcement gap at 30x the surface area.

Independent, can run in parallel with the above or be deferred without blocking anything: multi-admin/company-switch login (Auth), `EmployeeManageController` tile visibility, `EmployeeUnderController` self-service screen, `EmployeeEmiController`, `DocumentManagerController` part (b) generic upload library, ESS self-service/mobile attendance, hierarchy/BI dashboards, tenant-specific hardcoded notifications.

### Phase 2 — Leave Management

Currently setup-only (leave policy groups exist; apply/authorize/approve/reject workflow does not). Sequenced before Payroll because: (a) Attendance already correctly delegates OT/regularisation to legacy stored procedures, and Leave's accrual/encashment logic is documented in code-logic-report.md as similarly stored-procedure-heavy — the team should tackle it while the "delegate to legacy SP, don't re-derive" pattern from Attendance is fresh; (b) Payroll's LOP (loss-of-pay) and leave-encashment calculations consume Leave's output data directly, so Leave must exist first or Payroll has nothing to read.

### Phase 3 — Payroll, Salary & Tax (run/calculation engine)

The largest and highest-business-risk remaining piece. Setup (salary structures, financial year, tax declarations) is already migrated — this phase is specifically the run/calculation engine: payslip generation, statutory deduction calculation, disbursement. Depends on: Employee (done), Attendance (done), Leave (Phase 2), Bank master (Phase 1). See §4 for the specific high-risk business rules here and their verification approach — this is the module where "diff new output against legacy stored-procedure output on the same input" matters most, given payroll errors are financially and legally visible in a way most other bugs in this app are not.

### Phase 4 — Advances, Loans & Expenses

Depends on Payroll (Phase 3) because legacy deducts these through the payroll run. Building this before Payroll exists would mean either building against a stub or having to retrofit the payroll-deduction linkage later — sequence it after.

### Phase 5 — Statutory Registers

Depends on Payroll (Phase 3) — PF/ESI/PT statutory computations and registers are derived from payroll-run output in legacy. Building Statutory before Payroll produces reports with no real data to report on.

### Phase 6 — Reports (~30 controllers)

Deliberately last among the "build a new module" phases, but **should be delivered incrementally alongside each phase above, not as one big-bang phase at the end** — e.g. Employee/Attendance reports can be built now (their source data already exists), while Payroll/Leave/Statutory reports must wait for those respective phases. Treat "Reports" as a cross-cutting workstream threaded through Phases 1-5 rather than a literal Phase 6 that starts after Phase 5 ends. This also means the `user_access` server-enforcement decision (Phase 1, item 4) should land before the first report ships, not after several have.

### Phase 7 — Site/Field Work & Project Management

Largely independent of the HR/payroll spine above (its main coupling point is that attendance/reports may reference site assignment). Can be resourced in parallel with Phases 2-6 by a separate workstream without blocking or being blocked by them, aside from a light Attendance-cluster touchpoint (site-scoped punches) that should be coordinated with whoever owns Phase 2/3.

### Phase 8 — Remaining partial-cluster gaps with no hard dependency

Performance-review cycle (Promotions/Resignations cluster; note legacy's own schema for this is itself flagged as missing/stale in code-logic-report.md, so this needs a legacy-side data audit before it can even be scoped) and full Procurement chain (PO/GRN/Vendor/Stock/Item-master — depends on Vendor master, which depends on Phase 1's Contacts work). Neither blocks nor is blocked by Phases 2-7; schedule opportunistically.

---
</content>
## 3. Mapping Table & High-Risk Business Rules — Remaining Work

Each subsection below covers legacy Controller/action or api/ endpoint mapped to the planned new route/page/API route, and legacy Model mapped to the planned data-layer entity, for everything not yet migrated. High-risk business rules (complex calculations, state machines, stored-procedure-backed logic) are called out per item with a suggested verification approach, satisfying both the mapping-table and high-risk-business-rules requirements together since they're most useful read side-by-side per feature.

### 3.1 Fully Not-Started Clusters (Leave, Payroll, Advances/Loans/Expenses, Reports, Site/Field, Statutory)

`backend-report.md` (controller inventory) and `code-logic-report.md` (business logic / stored procedures).
New-app conventions per `rizo/src/lib/db.ts`, `rizo/src/lib/auth.ts`,
`rizo/src/app/(dashboard)/employees/page.tsx`, `rizo/src/app/api/employees/route.ts`: pages under
`src/app/(dashboard)/<feature>/page.tsx`, APIs mirror 1:1 under `src/app/api/<feature>/route.ts`, list
pages use react-query + `DataTable`, simple master CRUD uses `SetupCrudPage.tsx`, forms use
react-hook-form+zod, auth is per-route `getServerSession` with `userGroup` 1=admin/2=employee.

---

### Leave Management

**Mapping table**

| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `LeaveRequestController` — apply/authorize/approve/reject/cancel (admin) | `LeaveRequests` (→ `leaveentries`) | `(dashboard)/leave/requests/page.tsx` | `api/leave/requests/route.ts`, `api/leave/requests/[id]/route.ts`, `api/leave/requests/[id]/action/route.ts` (grandLeave equivalent) | `leave_entries` |
| `EmployeeLeaveRequestController` — ESS apply/cancel (duplicate state machine) | same table via `LeaveRequests` | `(dashboard)/leave/apply/page.tsx` (userGroup 2 self-service) | `api/leave/requests/route.ts` (shared POST, gated by session role) | `leave_entries` |
| `EmployeeLeavesController` — read-side leave listing | `LeaveEntries` (dup model, same table) | `(dashboard)/leave/my-leaves/page.tsx` | `api/leave/my-leaves/route.ts` (read-only) | `leave_entries` (consolidate dup model into one entity) |
| `LeaveEncashmentRequestController` — single-step `is_approved` flip | `LeaveEncashmentMaster` | `(dashboard)/leave/encashment/page.tsx` | `api/leave/encashment/route.ts`, `api/leave/encashment/[id]/route.ts` | `leave_encashment_master` |
| `LeavePolicyController` — leave types, accrual, sanction hierarchy | `LeavePolicy`, `LeavePolicyGroup`, `LeaveType` (reuses `salary_head_items`) | Extend existing `(dashboard)/setup/leavepolicy-groups/page.tsx` with per-policy accrual/type/sanction-by config screen | Extend `api/setup/leavepolicy-groups/route.ts` + new `api/setup/leave-policies/route.ts` | `leave_policy`, `leave_policy_group` |
| `LeaveapiController` — OTP/login/curl utility endpoints (auth-bypassed in legacy) | none (utility) | n/a — do not port as unauthenticated; fold any still-needed OTP/mail utility into existing authenticated `lib/` helpers | n/a | n/a |
| `EmployeeLeaveUploadController` / `EmpleaveuploadController` — bulk leave upload | `EmployeeLeaveUpload`, `EmployeeLeaveBalanceUpload` | `(dashboard)/leave/bulk-upload/page.tsx` | `api/leave/bulk-upload/route.ts` | `emp_leave_upload`, `leave_balance_upload` (note: legacy model has no valid PK — assign a proper PK in new schema) |
| Leave transaction ledger (`emp_leave_transactions`, `emp_leave_info`) | `EmployeeLeaveTransaction`, `EmployeeLeaveInfo` | (internal — populated by leave action API, not a standalone page) | internal to `api/leave/requests/[id]/action/route.ts` | `emp_leave_transactions`, `emp_leave_info` |

**High-risk business rules not yet migrated**

1. **`grandLeave()` multi-step approval state machine** (2-level Authorize→Approve, optional 3rd-level `sanction_by` when `leavepolicy.leval_of_approval=3`; role gating by direct `ISAutherizedby`/`APPROVEDBY` employee-key match, not `user_group`) — duplicated verbatim in `LeaveRequestController.php:2301-2857` and `EmployeeLeaveRequestController.php:2402-2958`. Verification: build a state-transition test matrix covering all 9 documented `actionType` values (Authorize/Approve/Reject/Approve Cancellation/Authorize Cancellation/Cancelled) × starting status, run legacy `grandLeave()` and the new implementation against the same seeded `leaveentries` rows, and diff resulting `LEAVESTATUS`/`ISAPPROVED`/`Autherized_date`/`APPROVED_date` values (code-logic-report.md §7.3 3.1–3.2).
2. **`leave_transaction_prc` stored procedure propagation, with silently swallowed errors** — the PHP model wrapper `LeaveRequests::leaveTransactionPrc()` always returns `true` regardless of `@Perror_message`, so a rejected transition inside the procedure is invisible to the caller (code-logic-report.md §7.3 §2). Verification: run `CALL leave_transaction_prc(...)` directly against representative valid/invalid transitions in the legacy DB, capture `@Perror_message`, and confirm the new implementation surfaces the same failures instead of silently succeeding.
3. **Leave-day computation with half-day handling** (`(TODATE-FROMDATE)/86400+1`, adjusted by `FROMHALF`/`TOHALF` combinations) plus **overlap detection** filtered by `leave_session` and status — `LeaveRequestController.php:1967-2024`. Verification: unit test against a table of edge cases (same-day half leave, cross-month spans, FROMHALF=2/TOHALF=1 double-exclusion, overlapping vs cancelled prior leave) derived from code-logic-report.md §7.3 §3.3.
4. **Yearly vs monthly balance computation via `leave_balance_inthe_year_fn` / `leave_balance_inthe_month_fn`, gated by `ALLOW_NEGETIVE`, with the actual insufficiency check commented out (never enforced) in legacy** — decide explicitly whether to enforce balance limits in the new app (a deliberate improvement) or replicate the non-enforcement; either way run the stored functions against the same employee/month inputs and diff against the new balance calculation to confirm parity before adding enforcement (code-logic-report.md §7.3 3.2 item 3).
5. **Hardcoded tenant carve-out** (`KWMT, ABSG, MBCT, MRBS, STCL, AGNG, ESNP, VGNN, AYRK, VGFS, VSFS`) that zeroes/skips balance computation entirely for those company codes — verify with product owner whether this is still required per-tenant or should become a policy flag; if required, encode as a `company_config`/feature flag rather than a hardcoded list (code-logic-report.md §7.3 3.3).
6. **`leave_balance_upload_bi` BEFORE INSERT trigger** auto-computing `balance_bf_adj`/`adjustment` via `leave_balance_inthe_year_fn` — this logic lives only in a DB trigger, invisible to PHP. Verification: extract the trigger body, reimplement explicitly in the new API's upload handler, and diff computed `balance_bf_adj` against trigger output for a sample upload batch (code-logic-report.md §7.3 §4).
7. **Attendance-verified / punch pre-checks (`criterias()`)** blocking leave apply/edit over already-punched or already-verified-register days — must be preserved to avoid retroactively corrupting locked payroll-relevant attendance. Verification: integration test applying/cancelling leave against days with punches and against verified/unverified registers, confirming block behavior matches legacy `criterias()`/`criterias1()` (code-logic-report.md §7.3 §3.3).

---

### Payroll, Salary & Tax

**Mapping table**

| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `PayrollController` — live 2-stage process/approve payroll run | `Payrollmaster` | `(dashboard)/payroll/run/page.tsx` | `api/payroll/run/route.ts` (process), `api/payroll/run/[id]/approve/route.ts` | `payroll_master`, `emp_salary_slip` |
| `PayrollProcessController` — orphaned 6-stage pipeline (dead nav) | `Payrollmaster` | Not ported unless product resurrects multi-stage approval — flag for explicit sign-off | n/a | n/a |
| `SalaryProcessingController` — feature-hub landing | n/a (thin) | Fold into `(dashboard)/payroll/page.tsx` landing/tile page | n/a | n/a |
| `SalaryHeadsController` / `SalaryHeadItems` | `SalaryHeads`, `SalaryHeadItems` | `(dashboard)/setup/salary-heads/page.tsx` (SetupCrudPage pattern) | `api/setup/salary-heads/route.ts`, `api/setup/salary-head-items/route.ts` | `salary_heads`, `salary_head_items` |
| `SalaryStructureController` (per-employee CTC assignment; setup-only structure CRUD already migrated) | `SalaryStructures`, `SalaryStructureDetails`, `EmployeeSalaryStructure` | `(dashboard)/employees/[id]/salary-structure/page.tsx` | `api/employees/[id]/salary-structure/route.ts` | `emp_salary_structure` |
| `SalaryComponentUploadController` — bulk Excel component upload | `EmpSalaryCompUpload` | `(dashboard)/payroll/salary-components/upload/page.tsx` | `api/payroll/salary-components/upload/route.ts` | `emp_salcomp_upload` |
| `SalaryIncrementController` — increment cycles, component allocation | `SalaryIncrement`, `SalaryIncrementDetails`, `ComponentIncrement` (schema-absent, verify live) | `(dashboard)/payroll/increments/page.tsx` | `api/payroll/increments/route.ts` | `salary_increment`, `salary_increment_details` |
| `TaxController` / `TaxHeadsController` / `TaxationController` — tax-head config, slab computation | `TaxHead`, `TaxHeadDetail`, `TaxType` | `(dashboard)/setup/tax-heads/page.tsx` (SetupCrudPage) + `(dashboard)/payroll/tax/calculate/page.tsx` | `api/setup/tax-heads/route.ts`, `api/payroll/tax/calculate/route.ts` | `tax_heads`, `tax_heads_details`, `tax_type` |
| `EmpTaxController` / `EmployeeTaxController` — per-employee tax regime, computation engine (declaration capture already exists at `employees/[id]/tax-declarations`) | `EmpTaxRegime`, `TaxSave`, `EmployeeTaxTransactions`, `EmpTaxSalTrans(New)`, `EmployeeTaxsalsum(New)` | `(dashboard)/employees/[id]/tax-computation/page.tsx` | `api/employees/[id]/tax-computation/route.ts` | `emp_tax_regime`, `emp_tax_sal_trans`, `emp_tax_sal_trans_new`, `emp_tax_sal_trans_sum(_new)` |
| `FinancialYearController` (already MIGRATED — reference only) | `FinancialYear` | existing `(dashboard)/setup/financial-year` | existing | `fin_year` |
| `ArrearController` — arrear/back-pay run (mirrors 2-stage payroll flow; underlying table schema-absent) | `Payrollarrearmaster`, `PayrollArrearComponents` | `(dashboard)/payroll/arrears/page.tsx` — **verify `payroll_arrear_master` exists in live DB before building** | `api/payroll/arrears/route.ts` | `payroll_arrear_master` (unverified) |
| `VariableController` / `FixedPaymentUploadController` — variable/fixed pay bulk upload | `EmployeeVariableUpload`, `EmployeeFixedPaymentUpload` | `(dashboard)/payroll/variable-pay/page.tsx` | `api/payroll/variable-pay/route.ts` | `emp_variables_upload` |
| `EmployeeIncrementReportsController` | n/a | fold into Reports cluster (`(dashboard)/reports/increments`) | `api/reports/increments/route.ts` | — |
| `YearEndController` — annual leave/FY close-out | n/a (delegates to stored procs) | `(dashboard)/payroll/year-end/page.tsx` | `api/payroll/year-end/route.ts` | — |
| CTC/gross/deduction VIEWs (`MonthlyCTC`→`ctc_fixed_rate`, `GrossSalary`, `TotalDeductions`, `MonthlyAmount`) | `MonthlyCTC`, `GrossSalary`, `TotalDeductions`, `MonthlyAmount` (all SQL VIEWs modeled as writable tables in legacy) | derived/computed values only, no dedicated CRUD page | expose as read-only aggregation inside `api/payroll/run/route.ts` responses | not persisted — computed |

**High-risk business rules not yet migrated**

1. **`eval()`-based salary formula engine** (`structure_det_calequation`/`structure_formula`, token grammar `\d+_[A-Za-z_]+|monthsal`, cross-component dependency ordering within a payroll run; inconsistently whitelist-guarded across `PayrollController`/`ArrearController`/`SalaryComponentUploadController`/`SalaryIncrementController`) — code-logic-report.md §7.4 §3.1. Verification: replace with a real expression parser, then run the parser against every distinct `structure_det_calequation`/`structure_formula` string present in the seed/live data and diff computed amounts against legacy `eval()` output for a full payroll-month run per tenant.
2. **`calculate_salary_main_prc` / `salary_process_prc` payroll calculation stored procedures**, tenant-branched via the hardcoded `$specialCompanies` array (`PayrollController.php:818`) which has already drifted out of sync with the copy in `SalaryStructureController.php:258`. Verification: run each stored procedure directly against a snapshot of `emp_salary_structure`/`salary_structure_details` for both a "special" and "standard" tenant, and diff resulting `emp_salary_slip` rows against the new engine's output, run-for-run.
3. **Payroll-run state machine** (`payroll_master.action`: `NULL → Processed → (Verified?) → Approved`, parallel `Hold`, `removePayrollEntry` reset-to-NULL escape hatch that also soft-closes `emp_salary_slip` and resets `emp_variables_upload.status`) — code-logic-report.md §7.4 §3.2. Verification: state-transition test replaying every legal transition (including hold→resume and remove→reprocess) against a seeded payroll_master row set, diffing `action` and downstream side-effect columns.
4. **Old vs new tax-regime dual computation** — `tax_computation_fn`, `tax_salary_distribution_fn` (old regime), `tax_salary_distribution_new_fn` (new regime, 6/7-slab incl. `marginal_relief`), `tax_salary_process_prc`, `find_pf_tax_cal_fn`, `profession_tax_cal_fn` (code-logic-report.md §4.1 stored-proc catalog, lines 1257-1399). Verification: for a documented sample of employees under both `emp_tax_regime.option_type`, run legacy stored functions and new implementation against identical CTC/declaration inputs and diff computed tax, marginal relief, and per-slab portions line-by-line.
5. **ESI/EPF/EPS/EDLI statutory rates** — ESI 0.75%/3.25% (₹21,000 ceiling), EPF 12%/EPS 8.33% (₹15,000 ceiling, per-employee EPS opt-out via `payroll_master.eps`), EDLI = EPF−EPS, admin charges 0.5%/0% — currently only realized inside Reports cluster controllers but conceptually payroll-engine logic (code-logic-report.md §7.6 §3.1-3.2). Verification: unit test against the documented rate table and ceiling values, plus a historical-period test for the COVID-era 10% EPF relief window (2020-05 to 2020-07) to confirm the new engine can still reproduce compliance reports for that period if required.
6. **`payroll_master_approve` / `payroll_master_insert` stored procedures** driving the approve step per employee. Verification: run stored procedure directly and diff `payroll_master`/`emp_salary_slip` state against new implementation for a batch approve operation.
7. **Zero-decimal salary amounts (`decimal(10,0)`) combined with ad hoc rounding rules** (ESI uses `ceil()`, others `round()`, decided per component name string-match, not a declared rule) — code-logic-report.md §7.4 §4. Verification: enumerate every component's rounding behavior from legacy source, encode as an explicit per-component rounding rule table, and unit test each component type against legacy output for boundary values (x.5 cases).
8. **Two independently-computed CTC-distribution paths** (PHP in `SalaryIncrementController`/`SalaryComponentUploadController` vs MySQL `sal_structure_distribution_fn`) with no guarantee of consistency — code-logic-report.md §7.4 §3.3. Verification: pick the stored-function version as source of truth, then diff PHP-path output against the stored function for a sample of employees to confirm they historically agreed before deleting the PHP duplicate.

---

### Advances, Loans & Expenses

**Mapping table**

| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `EmployeeadvanceController` — Salary Advance (real, live) | `EmployeeAdvance`, `EmployeeAdvanceInfo` | `(dashboard)/advances/page.tsx` | `api/advances/route.ts`, `api/advances/[id]/route.ts`, `api/advances/eligibility/route.ts` | `emp_advance`, `emp_advance_info` |
| `AdvanceController` — generic petty-cash advance (`advance_expense` table absent from schema) | `Advance` | **Verify against live DB before building** — likely dead | n/a until confirmed live | `advance_expense` (unverified) |
| `EmployeeLoanController` — loan + amortized EMI schedule | `EmployeeLoan`, `EmployeeLoanInfo` | `(dashboard)/loans/page.tsx` | `api/loans/route.ts`, `api/loans/[id]/route.ts`, `api/loans/[id]/transfer-emi/route.ts`, `api/loans/[id]/lump-sum-payment/route.ts` | `emp_loan`, `emp_loan_info` |
| `EmployeeEmiController` / `LoanEmi` — separate flat EMI-override upload (near-duplicate of EmployeeLoanController's EMI upload) | `LoanEmi` | fold into `(dashboard)/loans/emi-upload/page.tsx` — verify which of the two legacy controllers is actually routed before deciding whether to keep this as distinct feature or consolidate | `api/loans/emi-upload/route.ts` | `emi_upload` |
| `EmployeeExpensesController` — 2-level Authorize→Approve expense workflow | `EmployeeExpenses` | `(dashboard)/expenses/page.tsx` | `api/expenses/route.ts`, `api/expenses/[id]/action/route.ts` | `emp_expense` |
| `ProjectExpensesController` — 1-level Approve/Reject, PO-flavored, ZWLK cascade | `EmployeeExpenses` (same table), plus PO sub-models depending on absent tables | `(dashboard)/expenses/project/page.tsx` — decide whether to unify with `EmployeeExpensesController` mapping or keep as distinct workflow | `api/expenses/project/route.ts` | `emp_expense` (shared table — needs a single unified state model) |
| `ExpenseTypeController` / `ExpenseTypesController` (duplicate pair) | `ExpenseType`, plus dependent `ExpenseHead`/`Allocate_expense` (tables absent) | `(dashboard)/setup/expense-types/page.tsx` (SetupCrudPage) | `api/setup/expense-types/route.ts` | `expense_type` |
| `ExpenseItemController` (`expense_item` table absent) | `ExpenseItem` | **Verify against live DB before building** | n/a until confirmed | `expense_item` (unverified) |
| `VehicleExpensesController` (`transportation_expense`/`transportation_payments` tables absent) | `VehicleExpenses`, phantom `TransportationPayment` | **Verify against live DB before building** | n/a until confirmed | `transportation_expense` (unverified) |
| `PaymentApprovalsController` | n/a (SaaS plan/feature-flag adjacent, per prior research out of core cluster) | out of scope for this cluster — confirm with product | — | — |

**High-risk business rules not yet migrated**

1. **Advance eligibility formula — "80% of monthly CTC," fully replaced (not combined) by an attendance-prorated cap for GLET/ABSG tenants**, implemented twice with subtle divergences (interactive `salary()` subtracts existing-month advances; bulk `uploadandsaveempctc()` adds a `+1` off-by-one to the ceiling and has a confirmed bug where the rejection is logged but never enforced — `$condition_statemnt = true` override at `EmployeeadvanceController.php:554`). Daily/hourly-wage employees are exempt entirely. Verification: build a documented edge-case table (GLET/ABSG vs other tenant, daily-wage exemption, existing-advance deduction, boundary at exactly 80%) and run legacy `salary()`/`uploadandsaveempctc()` alongside the new implementation against the same employee/month inputs, diffing both the computed ceiling and the actual accept/reject decision (code-logic-report.md §7.5 §2.1, §3.2).
2. **Two independent, non-shared expense-approval state machines on the same `emp_expense` table** — `EmployeeExpensesController` (2-tier, `status=2` on reject) vs `ProjectExpensesController` (1-tier, `status` stays `1` on reject, plus a ZWLK-tenant cascade voiding a schema-absent `advance_payment` table). Verification: decide on one unified state model, then run both legacy workflows against representative approve/reject/admin-shortcut scenarios and diff `expense_status`/`status`/timestamp columns against the new unified implementation, explicitly testing the inverted-boolean `reject='0'` convention and the ZWLK cascade case.
3. **Loan amortization / EMI schedule**: reducing-balance formula `emi = floor((P*r/12)*(1+r/12)^n / ((1+r/12)^n - 1))` (or flat division at 0% interest), materialized month-by-month into `emp_loan_info`, plus `update_transfer()` (moves an EMI between months) and `amount_pay()` (lump-sum payment walked backward against future `'A'` rows) — code-logic-report.md §7.5 §2.3. Verification: unit test the amortization formula against a documented set of principal/rate/tenure combinations from code-logic-report.md, then integration-test transfer and lump-sum payment against a seeded loan schedule, diffing `opening_balance`/`closing_balance`/`paid_status` per row against legacy output.
4. **`$this->setup($emp_fkey)` undefined-method bug** in legacy `EmployeeadvanceController::index()`, `EmployeeExpensesController::index()`, `ProjectExpensesController::index()` causing a fatal error for `user_group==2` (employee) landing pages — confirm the new app's employee self-service equivalents do not reproduce this crash; this is a "do not carry forward" item, not a rule to replicate (code-logic-report.md §7.5 §3.1).
5. **7 of 14 legacy models (plus 2 phantom table references) point at tables absent from the schema dump** (`advance_expense`, `expense_item`, `expense_heads`, `emp_expense_details`, `emp_expense_payment`, `transportation_expense`/`transportation_payments`, `allocate_expense`, `advance_payment`) — before building any of the affected screens (`AdvanceController`, `ExpenseItemController`, expense-head allocation, `VehicleExpensesController`), verify against the live/full DB dump whether these tables genuinely exist; do not port UI for confirmed-dead tables (code-logic-report.md §7.5 §0).

---

### Reports (all ~30 controllers)

**Mapping table**

Legacy report controllers largely follow one shared plumbing chain
(`hrreports→changereporttype→addreportcriteria→loadcriteriaitems→listcriteriaitems→generatereport`, driven
by generic `reportcriterias`/`report_audit` metadata) — current-state-report.md and code-logic-report.md
§7.6 both recommend a single generic "report runner" abstraction rather than 30 hand-ported pages.

| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Generic report-runner plumbing (`ReportsController`, `ReportController`, `hrreports`/`changereporttype`/`addreportcriteria`/`loadcriteriaitems`/`listcriteriaitems`/`generatereport` chain) | `ReportCriterias`, `ReportAudit` | `(dashboard)/reports/page.tsx` (report-type picker) + `(dashboard)/reports/[type]/page.tsx` (criteria builder + result) | `api/reports/route.ts` (list types), `api/reports/[type]/criteria/route.ts`, `api/reports/[type]/generate/route.ts` | `report_criterias` (config, safe to migrate as-is), `report_audit` (download/view audit log — must be preserved for statutory compliance traceability) |
| `AttendanceReportsController`, `SalaryReportController`/`SalaryReportsController` (massive, ~45k lines — the real salary report engine), `TaxReportController` | (query-proxy only, no dedicated model) | `(dashboard)/reports/attendance`, `/reports/salary`, `/reports/tax` | `api/reports/attendance/generate`, `api/reports/salary/generate`, `api/reports/tax/generate` | derived — reads `emp_salary_slip`, `attendance_register`, etc. |
| `StatutoryReportController` (~13k lines — real ESI/EPF/PT/TDS engine), `EsiEpfReportController` | `EmpCtcTransaction` (query-proxy) | `(dashboard)/reports/statutory` (ESI/EPF/PT/TDS/WPS sub-tabs) | `api/reports/statutory/generate` | derived — see business rules below |
| `HierarchyReportController` | n/a (org-chart/PDF export only) | `(dashboard)/reports/hierarchy` | `api/reports/hierarchy/generate` | derived |
| `EmployeeAdvanceReportsController`, `EmployeeLoanReportsController`, `EmployeeExpenseReportsController` (+ stale `_2018-*` duplicate variants — do not port duplicates) | n/a | `(dashboard)/reports/advances`, `/reports/loans`, `/reports/expenses` | `api/reports/advances/generate`, etc. | derived from Advances/Loans/Expenses cluster tables once built |
| `StockReportController` (dual-purpose: inventory + payroll reports — out of current inventory scope except payroll sub-reports) | n/a | `(dashboard)/reports/payroll-summary` (payroll-only sub-reports; inventory sub-reports deferred with the Assets/Inventory cluster) | `api/reports/payroll-summary/generate` | derived |
| Niche/consolidated: `AccessDetailReportController`, `ArrearsReportsController`, `AssetsReportsController`, `CompanyProfileReportController`, `ConfigReportController`, `EditedReportsController`, `EmpOtRegisterController` (actually a mutation workflow, not a report — model as API not report), `EmpreportController` (30-action ESS report portal), `EventReportsController`, `InteligenceReportsController`, `LopReportsController`, `MiscellaniousReportsController` (comp-off/leave-balance, heavy old/new/PSQUARE duplication — consolidate, don't port duplicates), `ReceiptReportController`, `ReconciliationReportController`, `ResighnedReportsController`, `SalarySlipReportsController`, `SiteReportsController`, `TrackingReportsController`/`TrackingReportsNewController` (GPS/km, external distance-API dependency), `VariableReportController` | mostly `ReportAudit`-only or query-proxy | Group by theme under `(dashboard)/reports/<theme>/page.tsx` (e.g. `/reports/audit`, `/reports/arrears`, `/reports/assets`, `/reports/leave-balance`, `/reports/tracking`) rather than 1:1 per legacy controller | `api/reports/<theme>/generate` | derived; `report_audit` write on every generate call |

**High-risk business rules not yet migrated**

1. **ESI/EPF/EPS/EDLI statutory formulas embedded in report controllers** (ESI 0.75% employee / 3.25% employer of ESI-wage capped ₹21,000; EPF 12% capped ₹15,000; EPS 8.33% capped ₹15,000 with per-employee opt-out; EDLI = EPF−EPS; EDLI insurance premium 0.5%; EPF admin charge 0.5%; EDLI admin charge hardcoded 0% (not date-gated); grand-total ECR formula) — code-logic-report.md §7.6 §3.1-3.2. Verification: unit test against the documented rate/ceiling table for a range of salaries (below/at/above ceiling, EPS-opted-out employee) and diff against `StatutoryReportController.php`/`EsiEpfReportController.php` output for the same inputs.
2. **`eval()`-based PF-formula-string evaluation** on `emp_salary_slip.remarks`/`remarks_2`/`EPF_earning` (e.g. `"(15000+2000+3000)*.12"`, stripped of the trailing multiplier and eval'd) — a direct code-injection surface if `remarks` is ever user-editable. Verification: replace with a safe parser and diff computed PF figures against legacy `eval()` output across the full seeded `emp_salary_slip.remarks` value set (code-logic-report.md §7.6 §3.2).
3. **COVID-19 relief-period EPF rate override (10% instead of 12%) hardcoded for month_year 2020-05 to 2020-07** — needed only for re-generating historical compliance reports for that period. Verification: confirm with product whether historical re-run capability is required; if so, unit test the date-range branch explicitly against that window and confirm the standard 12% applies outside it (code-logic-report.md §7.6 §3.2).
4. **Professional Tax sourced from `professional_tax_view`** (UNION of `emp_settle_slip` "settle" rows and `emp_salary_slip` "slip" rows keyed to `tax_salary_components_name='professional tax'`) with no in-app slab computation — PT amount is pre-computed elsewhere (payroll engine) and only unioned/displayed here. Verification: recreate the view logic (or query pattern) in the new data layer and diff output rows against the legacy view for a sample company/month (code-logic-report.md §7.6 §3.3).
5. **LOP/NCP day calculation**: `NCP = calendar_days − (present+leave+weekoff+holiday totals)`, `LOP = max(0, NCP)`, feeding both statutory wage reports and ESI eligible-days computation — code-logic-report.md §7.6 §3.5. Verification: unit test against a matrix of attendance-register totals (full month present, partial leave, resignation mid-month) and diff LOP/NCP output against legacy formula results.
6. **Payroll-type/attendance-period boundary switching** (`db_config.payroll_type` IN `('F1','F2')` → calendar month, else custom period via `att_start_end_fn`) directly affects which days are included in ESI/EPF wage aggregation. Verification: run `att_start_end_fn` and the new period-resolution logic against both payroll_type configurations for the same company/month and diff resulting date ranges (code-logic-report.md §7.6 §3.6).
7. **`report_audit` compliance-trail write on every report download/view** — must be preserved 1:1 for statutory/audit defensibility even as the report-runner is consolidated into one generic component. Verification: confirm every new `/api/reports/*/generate` call writes an equivalent audit row (report_type, criteria, mode, user attribution) and cross-check against the legacy field set in backend-report.md §3.1.

---

### Site / Field Work & Project Management

**Mapping table**

| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `SiteAttendanceController` — direct site CRUD + attendance punches (no approval step) | `SiteMaster`/`Site` (dup models, same table), `SiteTransactions`, `SiteAttendance`, `SiteHistory`, `Access_site` | `(dashboard)/sites/page.tsx`, `(dashboard)/sites/[id]/page.tsx` | `api/sites/route.ts`, `api/sites/[id]/route.ts`, `api/sites/[id]/attendance/route.ts` | `site`, `site_transactions`, `site_history`, `site_attendance` |
| `SiteAttendanceApplyController` — maker-checker variant of the same site CRUD (diffs recorded to `site_master_approval*` for approval, though legacy always auto-approves — see risk #3) | same + `SiteMasterApproval`, `SiteMasterApprovalDetails` | `(dashboard)/sites/apply/page.tsx` (if maker-checker retained) or merge into `sites/[id]` with a real approval flow | `api/sites/[id]/apply/route.ts`, `api/sites/approvals/route.ts` | `site_master_approval`, `site_master_approval_details` |
| `ProjectController` — same `site`/`site_transactions` tables, project-flavored front-end; adds `project_activity` work-log (table absent from schema dump — verify live) | `SiteMaster`, `SiteTransactions`, `SiteHistory`, `ProjectActivity` | `(dashboard)/projects/page.tsx` — **decide: unify with `/sites` as one entity with a view-mode toggle, since legacy confirms Project and Site Attendance are the same underlying entity** | `api/projects/route.ts` (thin wrapper over `api/sites` if unified) | `site` (shared), `project_activity` (unverified) |
| `SiteWorkController` / `FieldSurveyController` — `efsr_site`/`efsr_tickets`/`efsr_equipments_master` field-survey/equipment concept, distinct from `site` (all tables absent from schema dump) | `EfsrSite`/`SiteWork` (dup models), `EquipmentType`, `SurveyType`, `Equipments`, `EfsrTickets` | **Live-DB schema introspection required before building** — `(dashboard)/field-survey/page.tsx` pending confirmed schema | `api/field-survey/route.ts` pending schema | `efsr_site`, `efsr_tickets`, `efsr_equipments_master` (all unverified) |
| `MaterialController` / `MaterialRequestController` | `MaterialRequest`, `MaterialRequestDetails` | `(dashboard)/material-requests/page.tsx` | `api/material-requests/route.ts` | `material_request`, `mr_details` |
| `GatePassController` / `OutPassController` (`gate_pass` table absent from schema dump) | `GatePass`, `GatePassItems` | **Verify against live DB before building** | n/a until confirmed | `gate_pass`, `gate_pass_items` (unverified) |
| `SiteAttendanceManageController` / `SiteattendanceregisterController` — near-duplicate register/verify workflow (fork of same feature — decide canonical before porting) | `Siteattendanceregister`, `AttendanceRegister` | `(dashboard)/sites/register/page.tsx` — pick one canonical implementation | `api/sites/register/route.ts` | `site_attendance_register` |
| `ProjectIncomeController` — income/payment tracking against a project/site (`Vehicle` referenced but not declared in `$uses` — likely bug; SQL-injection-shaped `listproject` query) | `ProjectIncome`, `ProjectIncomePayment` | `(dashboard)/projects/[id]/income/page.tsx` | `api/projects/[id]/income/route.ts` | `project_income`, `project_income_payment` |
| `DeviceController` (site/field device management) | n/a | `(dashboard)/sites/devices/page.tsx` | `api/sites/devices/route.ts` | device tables (schema location tbd) |

**High-risk business rules not yet migrated**

1. **`site_rate_update_prc` — the actual wage-rate calculation for site-based workers**: looks up `sales_rate`/`emp_rate` from `site_transactions` (keyed by site+shift+designation, not employee) and writes them onto each `site_attendance` punch row, plus raw-hour `duration` with no break/overtime split (differs from the office-employee `day_time_procedures` calculation). Verification: run the stored procedure directly against a batch of closed (`status=3`) `site_attendance` rows and diff `sales_rate`/`emp_rate`/`duration` against the new implementation's output for the same site/shift/designation combinations (code-logic-report.md §7.8 §3.1).
2. **`mark_site_attendance_fn`/`mark_site_attendance_out_fn` two-step validate-then-write pattern** (function validates and returns a verdict, then a *separate* PHP round-trip performs the actual INSERT/UPDATE) — a race-condition risk between the two statements that the new implementation should collapse into one atomic operation rather than replicate. Verification: concurrency test (two simultaneous punch-ins for the same employee/site/day) against both implementations, confirming the new one prevents the race the legacy one doesn't.
3. **Site-master change-approval is not a real approval workflow** — the table defaults to `status='pending'` but the application code unconditionally writes `'approved'` at insert time with hardcoded remarks `'Approved by Admin'`; no code path ever writes `'rejected'` or leaves a row `'pending'`. Decide explicitly whether the new app should implement a *genuine* maker-checker approval (a product decision, not a technical port) or intentionally keep this as an audit-trail-only mechanism. Verification: if a real approval flow is built, test that pending changes are NOT auto-approved and that a distinct reviewer action is required, unlike legacy (code-logic-report.md §7.8 §3.2).
4. **`efsr_site`/`efsr_tickets`/`efsr_equipments_master`/`gate_pass`/`project_activity` tables entirely absent from the schema dump but referenced by actively-edited (2023-2025-dated) live code** — this is flagged as the single biggest schema-cross-check risk in the whole codebase. Verification: do not build any UI/API against these tables from the schema dump alone; run live-DB `SHOW CREATE TABLE` introspection first and confirm actual column sets before scoping (code-logic-report.md §7.8 §1.2).
5. **Gate-pass dual/overlapping update paths** (`savePass()` has a direct update branch; `printPass()` independently re-saves the entire gate-pass row + all items from inside a nominally read-only "print" action) — a data-integrity risk if both paths can run concurrently with different validation. Verification: confirm the new API has exactly one write path for gate-pass updates, and integration-test that "print" never mutates state (code-logic-report.md §7.8 §3.2).

---

### Statutory Registers

**Mapping table**

| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `StatutoryRegistersController::Generatewage()` — Wage Sheet (Form XI) | query-proxy over `emp_salary_slip`, `employee_info`, `emp_details`, `attendance_register`/`site_attendance_register`, `salary_head_items`, `salary_heads`, `branches`, `payroll_master`, `termination`, `emp_ctc_transaction`, `user_credentials` | `(dashboard)/statutory/wage-sheet/page.tsx` | `api/statutory/wage-sheet/generate/route.ts` | derived — no dedicated new table |
| `StatutoryRegistersController::generatemusterrollreport()` — Muster Roll | `AttendanceRegister`, `att_start_end_fn`, `insert_update_att_reg` proc, `branches`, `emp_details`, `termination`, `employee_info`, leave-type abbreviations from `salary_head_items` | `(dashboard)/statutory/muster-roll/page.tsx` | `api/statutory/muster-roll/generate/route.ts` | derived |
| `StatutoryRegistersController::generateServiceRecordReport()` — Service Record (recently added, hidden for tenant `HRBL`) | `payroll_master`, `employee_info`, `emp_details`, `termination`, `emp_salary_slip` ⋈ `tax_salary_components` (Basic) | `(dashboard)/statutory/service-record/page.tsx` — preserve the `HRBL` tenant-hide rule as an explicit feature flag, not a hardcoded string | `api/statutory/service-record/generate/route.ts` | derived |
| `StatutoryRegistersController::hrreports()`/`changereporttype()`/`addreportcriteria()`/`loadcriteriaitems()`/`listcriteriaitems()` — report-type/criteria picker plumbing | `ReportCriterias` | fold into shared `(dashboard)/statutory/page.tsx` landing + criteria builder (reuse Reports cluster's generic report-runner component) | `api/statutory/route.ts` (list types), `api/statutory/[type]/criteria/route.ts` | `reportcriterias` |
| `StatutoryRegistersController::downloadHistory()` — audit log of statutory report downloads | `ReportAudit` | n/a (internal) | write inside every `api/statutory/*/generate` call | `report_audit` |
| `StatutoryUploadsController::statutory()` — PF/ESI-style flat-file export (employees with non-blank `esi`) | query-proxy: `emp_salary_slip`, `tax_salary_components` (hardcoded PKs 10=EPF, 12=ESI, `14+1`=15=WWF — replace with named lookups, not literals), `emp_details.pf`/`.esi`, `device_attandance`, `termination.Reason` mapped to compliance codes | `(dashboard)/statutory/pf-esi-export/page.tsx` | `api/statutory/pf-esi-export/route.ts` | derived |
| `StatutoryUploadsController::pfdownload()` — near-duplicate of `statutory()` (different employee filter: `status='1'` vs non-blank `esi`; never actually streams a file in legacy — confirm whether this is intentional or an incomplete refactor before deciding to port) | same as above | fold into `pf-esi-export` as an alternate filter option, do not port as a separate broken feature | `api/statutory/pf-esi-export/route.ts?filter=active` | derived |
| `EsiEpfReportController::generate_wps_template()` — Wage Protection System bank-upload template | query-proxy | `(dashboard)/statutory/wps-template/page.tsx` | `api/statutory/wps-template/generate/route.ts` | derived |

**High-risk business rules not yet migrated**

1. **Wage Sheet / Muster Roll / Service Record generation logic is entirely raw-SQL string interpolation of `$_REQUEST`/`$arr_form_data` values with no parameterization** (confirmed SQL-injection surface, consistent with the rest of the codebase) — backend-report.md §2.12 (`StatutoryRegistersController.php` lines 430-2687). Verification: this is a security rewrite, not a like-for-like port — rebuild with parameterized queries, then diff output row-for-row against the legacy report for the same company/month/branch filter set to confirm no data drift was introduced during the rewrite.
2. **Hardcoded `tax_salary_components_fkey` lookups by literal PK (10=EPF, 12=ESI, `14+1`=15=WWF)** rather than named/config-driven lookups — a maintenance landmine if these PKs ever differ per tenant. Verification: replace with a name-based lookup (`tax_salary_components_name` = 'EPF'/'ESI'/'WWF') and diff resulting export rows against the legacy PK-based export for the same dataset (code-logic-report.md §7.12 §3.2).
3. **Muster Roll date-window resolution depends on `db_config.attendance_date`/`attendance_format` (per-company configurable attendance-cycle start day) and refreshes attendance via `insert_update_att_reg` before reading** — must run the same refresh-then-read sequence to avoid stale data. Verification: run the legacy refresh+read sequence and the new implementation against the same company/month and diff the resulting attendance totals per employee (code-logic-report.md §7.12 §3.2).
4. **`HRBL`-tenant hardcoded hide of the Muster Roll-only view, and other resignation-reason-to-compliance-code mappings** (`Resigned→2`, `Retrenchment→10`, `Retirement→3`, default→1) baked as literal string branches — encode as data-driven config/lookup tables in the new app rather than hardcoded conditionals, and unit test every mapped reason string against the documented code table (code-logic-report.md §7.12 §3.2, backend-report.md §2.12).
5. **`downloadHistory`/`report_audit` compliance-trail write must be preserved** for every statutory export, since these are the compliance-relevant download records (PF/ESI/WPS/wage-sheet exports are typically subject to audit). Verification: confirm every new `/api/statutory/*/generate` call writes an audit row with equivalent fields to legacy `report_audit`, cross-checked against backend-report.md §3.1's field table.

### 3.2 Not-Started Gaps Within Partially-Built Clusters

models). Fully-absent clusters (Leave, Payroll, Reports, Performance-standalone infra, etc.) are out of
scope here — see the separate not-started-clusters deliverable.

New-app conventions used below: pages `src/app/(dashboard)/<feature>/page.tsx`, APIs mirror 1:1 under
`src/app/api/<feature>/route.ts`, list pages use react-query+DataTable, simple master CRUD reuses
`src/components/setup/SetupCrudPage.tsx`, forms use react-hook-form+zod, auth via per-route
`getServerSession` (userGroup 1=admin/2=employee).

---

### Cluster 2.1 — Authentication & Authorization

#### Multi-admin / company-switch picker (parent cluster: Auth)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `SiteController::loginWithCentral()` — company-picker step for logins governing multiple tenant companies | `central_control` (control DB), `comp_contact_info` | `src/app/(auth)/select-company/page.tsx` (new step between credential submit and dashboard redirect) | `src/app/api/auth/companies/route.ts` (GET companies for a resolved user), extend `src/lib/auth.ts` `tryAdminLogin` to return a company list instead of a single `companyCode` when >1 match | `central_control` company-membership rows (control DB) |

**High-risk business rules not yet migrated**
1. Session must be re-scoped to the chosen company's DB pool (`getCompanyPool()`) only after the picker step, not at credential-verify time — verify by tracing `src/lib/auth.ts:47-90` (`tryAdminLogin`) against `SiteController.php` (`user-side-report.md` §1.2 item 4, `backend-report.md` §3.5) to confirm how legacy resolves the eventual `companyCode` used for all subsequent queries.

---

#### Login-by-employee-email (parent cluster: Auth)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Employee login via email lookup against `emp_device_comp_branch` (mobile-oriented alt login path) | `emp_device_comp_branch`, `user_credentials` | N/A (backend-only change) | Extend `src/app/api/auth/[...nextauth]/route.ts` / `src/lib/auth.ts` `tryEmployeeLogin` (`:92-171`) to fall back to an email lookup against `emp_device_comp_branch` when the submitted identifier isn't a `user_id` match | `emp_device_comp_branch` |

**High-risk business rules not yet migrated**
1. Need to confirm whether email-based lookup should also be admin-eligible or is mobile/employee-only — verify against `code-logic-report.md` §7.12 intro citation before implementing, since the report notes this citation without full method body.

---

#### Mobile-app-facing login/auth API (parent cluster: Auth)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `mob_user_credentials` plaintext-password-mirror mobile login surface (native app auth, distinct from the SSO-bridge `AccessController` — which should stay unported) | `mob_user_credentials` | N/A (headless API only) | New `src/app/api/mobile/auth/route.ts` (token/JWT issuance for native app), reusing `src/lib/auth.ts` password-verify logic but issuing an API token instead of a NextAuth session cookie | `mob_user_credentials` (locked/punchtype/device fields already partially managed via `src/app/(dashboard)/employees/access/page.tsx`) |

**High-risk business rules not yet migrated**
1. Legacy stored a **plaintext mirror** of the password in `mob_user_credentials` (`code-logic-report.md` §7.12 Schema quirks #2) — do not port that plaintext-storage pattern; verify by grepping the target schema for `mob_user_credentials.password` usage before writing the new endpoint, and use a hash/token scheme instead.
2. Confirm with product whether a native mobile app is even still planned before building this (flagged as an open question in `current-state-report.md` line 83).

---

#### `user_access`/`emp_menu` server-side permission enforcement (parent cluster: Auth)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Per-menu grant/revoke tree, never enforced server-side in legacy either (`backend-report.md` §3.3) | `user_access`, `emp_menu` | N/A (cross-cutting middleware, not a page) | Add a shared `requireMenuAccess(session, menuId)` helper called from protected `route.ts` handlers beyond `menu-allocation` itself — no such helper exists today (confirmed: only `src/app/api/employees/menu-allocation/[id]/route.ts` touches these tables) | `user_access`, `emp_menu` |

**High-risk business rules not yet migrated**
1. This is a **NEEDS HUMAN DECISION** item, not a strict gap — legacy itself never enforced this server-side either (`current-state-report.md` F4). Verify with product/eng whether closing this gap is in scope for this migration phase before building it, since it touches every protected route.
2. Legacy's asymmetric cascade logic (enabling a child force-enables parent; disabling a child only disables parent if last active sibling — `UserAccessController.php:539-623,835-923` per `code-logic-report.md` §7.12 §3.1) was dropped in the new app's flat menu-allocation editor; if server-enforcement is added, decide whether to also restore cascade semantics or keep it flat.

---

#### Forced password-reset flow — reset-token/URL generation (parent cluster: Auth, PARTIAL)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `LoginManagementComponent.php` — on `reset_login_flag='Y'`, generates a reset token/URL rather than dead-ending the login (`code-logic-report.md` §7.12 intro) | `user_credentials.reset_login_flag` | `src/app/(auth)/reset-password/[token]/page.tsx` (new employee-facing reset UI) | `src/app/api/auth/reset-password/route.ts` (issue token) + `[token]/route.ts` (consume token, clear flag) | `user_credentials` |

**High-risk business rules not yet migrated**
1. **LIKELY BUG, already shipped**: `src/app/api/employees/access/[id]/route.ts:31-38` sets `reset_login_flag='Y'` when an admin resets a password, but nothing anywhere clears it back to `'N'` — any employee whose password is admin-reset is **permanently locked out** (confirmed: `reset_login_flag` is written `'Y'` only there, and `'N'` only at row-creation in `employees/import/route.ts:147` and `employees/join/[id]/onboard/route.ts:137`). This is `current-state-report.md` Finding F1 — verify by re-testing the Access-screen password-reset path end-to-end after adding the reset-flow UI above.

---

### Cluster 2.2 — Employee Management

#### EmployeeManageController plan-gated tile visibility (parent cluster: Employee)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `EmployeeManageController::getEmployeeFeatures()` — landing shell that shows/hides employee sub-menu tiles by subscription plan (`user-side-report.md:218-232`) | plan/feature flags (fragmented across `central_control.plan`, `comp_contact_info.plan` per Finding 5 in §2.4) | Feature-tile row on `src/app/(dashboard)/employees/page.tsx` (or a dedicated landing) | `src/app/api/employees/features/route.ts` (GET enabled tiles for the current company) | plan/feature-flag table (TBD — see company-config note below) |

**High-risk business rules not yet migrated**
1. Legacy's plan-gating itself is fragmented across 4 unreconciled columns (`current-state-report.md` §2.4 Finding 5) — do not port the fragmentation; instead route through whatever single plan/feature mechanism gets built for `CompanySetupController` (see §2.4 items below), or fall back to `src/lib/company-config.ts`'s hardcoded allowlist pattern only as a stopgap, with an explicit TODO to replace it.

---

#### EmployeeUnderController — branch-locked self-service employee setup (parent cluster: Employee)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `EmployeeUnderController` — "Employees Setup" screen forced to the caller's own branch, no duplicate check (`user-side-report.md:313-335`) | `emp_details`, `emp_proff` (branch-scoped) | `src/app/(dashboard)/employees/under/page.tsx` (branch-manager-scoped variant of the main employee list) | `src/app/api/employees/under/route.ts` (branch pre-filtered by session's assigned branch) | `emp_details` |

**High-risk business rules not yet migrated**
1. Legacy explicitly **skips** the duplicate-check that the main `EmployeeController` enforces (`user-side-report.md:313-335`) — decide deliberately whether to replicate that gap or apply the same duplicate-check used in `src/app/api/employees/route.ts` before building this; do not silently omit either way.
2. Requires a "branch manager" actor/role concept that doesn't currently exist in the new app's `userGroup` model (1=admin/2=employee only) — confirm scope with product before implementation.

---

#### EmployeeEmiController — EMI processing (parent cluster: Employee)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `EmployeeEmiController` — monthly EMI processing, bulk EMI upload, replay-token guard (`user-side-report.md:402-434`) | `emi_upload`, `emp_loan`, `emp_loan_info` | `src/app/(dashboard)/employees/emi/page.tsx` | `src/app/api/employees/emi/route.ts` (list/process), `.../bulk-upload/route.ts` | `emp_loan`, `emp_loan_info`, `emi_upload` |

**High-risk business rules not yet migrated**
1. `code-logic-report.md:3928` flags `EmployeeEmiController` as a **likely duplicate fork** of `EmployeeLoanController`'s EMI methods (`getEmi`, `checkmonth`, `employeeemiloansave`, `uploadandsaveempemi`) — verify which controller is actually live in production before porting either, to avoid building two parallel EMI paths.
2. F&F settlement (`src/lib/settlement.ts`, already migrated per §2.7) reads `emp_loan_info.loan_emi` for payoff calculation — any new EMI-processing endpoint must keep `paid_status`/`is_completed` semantics consistent with what `settlement.ts` already expects (`code-logic-report.md:1072` describes the `paid_status`→`'P'` and `is_completed`→`'Y'` reconciliation logic).

---

#### DocumentManagerController part (b) — generic file-upload/allocate library (parent cluster: Employee / Assets)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `documentUpload()`/`documentAllocate()` — plain file upload + allocate-to-employees library, distinct from the template-merge engine (already migrated) and from `emp_passport_visa` (already migrated, different feature) | `document_upload`, `document_allocation` | `src/app/(dashboard)/documents/library/page.tsx` | `src/app/api/documents/library/route.ts` (upload), `.../allocate/route.ts` (bulk-allocate to employee list) | `document_upload`, `document_allocation` |

**High-risk business rules not yet migrated**
1. This is a real, clean gap (not a fidelity bug) — `current-state-report.md` confirms zero references to `document_upload`/`document_allocation` anywhere in `rizo/src`. When building, reuse the existing `src/app/api/upload/route.ts` file-handling primitive already used by `DocumentUploadField.tsx` rather than reimplementing upload plumbing.
2. Do not conflate with `emp_passport_visa` (`employees/[id]/documents/route.ts`) — that is a different legacy table/feature and is already correctly migrated; verify naming doesn't collide (e.g. avoid a second route literally named `employees/[id]/documents`).

---

#### ContactsController — Customer/Vendor master CRUD (parent cluster: Employee)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `ContactsController` — Customer/Vendor master CRUD, `organization_id`-scoped (`backend-report.md:591-607`) | `Contacts` (`Model/Contacts.php`, table `contacts`, PK `contact_id`) | `src/app/(dashboard)/setup/contacts/page.tsx` (candidate for `SetupCrudPage.tsx` reuse — simple CRUD, no validation in legacy) | `src/app/api/setup/contacts/route.ts` + `[id]/route.ts` | `contacts` |

**High-risk business rules not yet migrated**
1. Legacy's `listcontacts` action has a confirmed **SQL-injection risk** via raw string concatenation into `LIKE` clauses (`backend-report.md:602`) — verify the new route uses parameterized queries throughout (the pattern already established in `src/app/api/employees/route.ts` and confirmed safe elsewhere in the app) and does not port the raw-concat filter pattern.

---

#### BeneficiaryController (parent cluster: Employee)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `BeneficiaryController` — near-duplicate of Contacts (same code shape), plus Excel export/import (`backend-report.md:609-625`) | `Beneficiary` (`Model/Beneficiary.php`, table `beneficiary`, PK `contact_id`) | `src/app/(dashboard)/setup/beneficiaries/page.tsx` | `src/app/api/setup/beneficiaries/route.ts` + `[id]/route.ts`, `.../bulk-upload/route.ts` | `beneficiary` |

**High-risk business rules not yet migrated**
1. Legacy's own schema is suspect: `code-logic-report.md:2287-2295` flags the `beneficiary` table as absent from the schema dump the report was built against — verify the table actually exists in the target production schema before building against it; do not assume the legacy report's table reference is current.
2. Legacy's bulk-import (`uploadandsaveempctc`) has a confirmed result-check bug — both success and already-exists branches return `success=>1` inconsistently (`backend-report.md:622`) — do not port that bug; return distinct success/duplicate/failure states in the new bulk-upload endpoint.
3. Same SQL-injection risk in `listcontacts` as Contacts (item above) — verify parameterized queries.

---

#### EmployeeMenuController — ESS nav composition (parent cluster: Employee, PARTIAL/RESTRUCTURED)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `getDefaultMenus()`/`getAddonMenus()`/`setFeatureSession()` — computes what nav an *employee* (ESS user) actually sees, distinct from the admin allocation-editor already migrated | `emp_menu`, `user_access` | Consumed inside `src/app/(dashboard)/layout.tsx` (employee-side nav render) | `src/app/api/employees/menu/route.ts` (GET current employee's own resolved menu tree, read-only) | `emp_menu`, `user_access` |

**High-risk business rules not yet migrated**
1. Verify this is genuinely a distinct read endpoint from the already-migrated `menu-allocation/[id]/route.ts` (which is the *admin write* path) — `current-state-report.md` §2.2 explicitly flags that no employee-facing *read* of their own resolved nav exists today; confirm the employee-side layout doesn't already derive this some other way before building a duplicate mechanism.

---

#### EmployeeJoinController — onboarding completion-percentage + email (parent cluster: Employee, PARTIAL)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `calculateOnboardingPercentage()`, `getOnboardingCompletion()`, `sendOnboardingMail()` (`user-side-report.md:152-179`; `code-logic-report.md:2457-2496`) | `emp_join` and sub-resource tables (documents/education/experience/family) | Progress indicator on `src/app/(dashboard)/employees/join/[id]/page.tsx` | `src/app/api/employees/join/[id]/completion/route.ts` (GET percentage), notification hook on save | `emp_join` + sub-tables |

**High-risk business rules not yet migrated**
1. `current-state-report.md` §2.2 Fidelity §2.1 classifies this as a **DROPPED / LIKELY BUG** — the exact weighting algorithm for the percentage calc must be re-derived directly from `EmployeeJoinController.php` (not fully re-specified in the reports available) before re-implementing, since a wrong weighting would silently mis-represent onboarding progress to HR.

---

#### EmployeeController — promotion/increment workflow, OTP/device import, resume PDF (parent cluster: Employee, PARTIAL)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| OTP/device bulk import (distinct from promotions, which is its own migrated cluster) | `emp_device_comp_branch` / OTP device tables | `src/app/(dashboard)/employees/device-import/page.tsx` | `src/app/api/employees/device-import/route.ts` | device/OTP tables |
| Resume PDF download | `emp_details` + resume storage | Button on `src/app/(dashboard)/employees/[id]/page.tsx` | `src/app/api/employees/[id]/resume/route.ts` | file storage / `emp_details` |

**High-risk business rules not yet migrated**
None flagged beyond standard file-handling — these are additive, low-risk features (promotion/increment itself is already covered by the separately-migrated Promotions cluster, §2.7).

---

### Cluster 2.3 — Attendance & Time

#### ESS self-service attendance + mobile GPS tracking (parent cluster: Attendance)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `EmpattendanceController`/`EmpattendanceregisterController`/`EmployeeAttendanceregisterController` (3 competing "my register" controllers) + `EmpeditpunchesController` + employee-self path of `RegularisationController` | `attendance_register`, `device_attandance` | `src/app/(dashboard)/attendance/my-register/page.tsx` (employee-scoped, `userGroup===2`) | `src/app/api/attendance/my-register/route.ts` (self-scoped GET), `.../regularisation` extended to accept employee-initiated POSTs | `attendance_register` |
| `MobileLocationUpdateController` — GPS/mobile tracking report + map | `mob_user_tracking` (or equivalent) | `src/app/(dashboard)/attendance/mobile-tracking/page.tsx` | `src/app/api/attendance/mobile-tracking/route.ts` | `mob_user_tracking` |

**High-risk business rules not yet migrated**
1. This is an explicit, documented project decision already recorded in code: `src/app/api/attendance/regularisation/route.ts:9` states "admin-only per project decision (no employee self-service/hierarchy-manager actor in this pass)." Confirm with product whether this remains a deliberate phase-2 deferral before building — do not silently reverse the decision without sign-off.
2. Legacy had **three competing** ESS register controllers (`user-side-report.md:710-778`) — do not port all three; pick one canonical shape (the new app's single-register-book pattern already used for admin) and consolidate, matching the architectural improvement already made elsewhere in this cluster.

---

#### AttendanceCheckInOutController — remaining HR report types (parent cluster: Attendance, PARTIAL)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Misspunch report, status-report, break-report, early/late *duration* variants, Excel/PDF export (5 of ~11 report types already ported: daily/early-in/early-out/late-in/late-out) | `emp_early_in`, `emp_late_in`/`out`, `device_attandance` | Extend `src/app/(dashboard)/attendance/checkin/page.tsx` with additional report-type tabs | Extend `src/app/api/attendance/checkin-reports/route.ts` with `type=misspunch\|status\|break\|duration` params + an `/export` sub-route | same tables, plus a break-duration table if distinct |

**High-risk business rules not yet migrated**
None flagged as bugs — straightforward additive report types; verify column-level parity against `backend-report.md:1095-1121` when implementing each report type individually.

---

#### AttendanceSetupController — plan/feature-gating hub (parent cluster: Attendance)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `getAttendanceFeatures()` — plan-gated tile UI for the Attendance module (`backend-report.md:703-708`) | plan/feature flags | Tile row on `src/app/(dashboard)/attendance/page.tsx` (landing) | `src/app/api/attendance/features/route.ts` | plan/feature-flag table (same TBD mechanism as EmployeeManageController above) |

**High-risk business rules not yet migrated**
1. `backend-report.md:769` flags this controller as **possibly dead legacy cruft** — no inbound references found from other controllers, and its routes (`/attendanceregister/...`) don't align with the canonical register-book path already migrated. Verify with a live-routing/menu-entry check before investing in porting this at all.

---

#### DailyOvertimeVerifyNewController + ScheduledBreakOffController (parent cluster: Attendance)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Daily OT verify workbench with inline Scheduled-Break-Off (SBO) management (`user-side-report.md:805-833`; `backend-report.md:978-1023`) | `emp_ot_master` (daily grain), SBO table (`code-logic-report.md:937-951`) | `src/app/(dashboard)/attendance/overtime/daily-verify/page.tsx` | `src/app/api/attendance/overtime/daily-verify/route.ts` | `emp_ot_master`, `emp_shift_planner`-adjacent SBO table |

**High-risk business rules not yet migrated**
1. Verify this is genuinely distinct from the already-migrated monthly OT register (`src/app/(dashboard)/attendance/overtime/page.tsx`) rather than an overlapping duplicate — legacy's own controller naming suggests daily vs. monthly grain, confirm against `code-logic-report.md:2751-2769` before building a second OT surface.

---

#### RegularisationController — ESS and hierarchy-manager approval paths (parent cluster: Attendance, PARTIAL)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Employee self-service submission path + hierarchy-manager (non-admin) approval path, distinct from the already-migrated admin `adminindexnew` path | `attendance_register` (via `approved`/`status` columns already used by the migrated admin path) | `src/app/(dashboard)/attendance/my-regularisation/page.tsx` (employee), approval action added to a manager-scoped view | Extend `src/app/api/attendance/regularisation/route.ts` (self-scoped POST) and `[id]/decide/route.ts` (manager-scoped approve, gated on hierarchy not just `userGroup===1`) | `attendance_register` |

**High-risk business rules not yet migrated**
1. The existing admin approve/reject bug-fix (guard on `approved==='P' AND status===1`, `[id]/decide/route.ts:38`) must be preserved when adding the manager-approval path — do not let a new code path reopen the double-processing bug that was deliberately fixed for admin (`current-state-report.md` §2.3 Fidelity §2.5).
2. "Hierarchy-manager" as an approval actor requires resolving reporting lines via `EmployeeHierarchyController`'s already-migrated `emp_config` type=HIERARCHY structure (§2.2) — reuse that, do not reintroduce `emp_structure`/`emp_details.parent`.

---

#### DayTimeProcedureController — shift-exception sub-feature (parent cluster: Attendance, PARTIAL)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `saveException`/`getExceptions`/`deleteException`/`toggleExceptionStatus` (shift-master exceptions, distinct from the already-migrated shift CRUD) | `working_day_time_procedures` exceptions sub-table | Add "Exceptions" tab to `src/app/(dashboard)/setup/shifts/[id]/page.tsx` (or `ShiftForm.tsx`) | `src/app/api/setup/shifts/[id]/exceptions/route.ts` | shift-exceptions table (`code-logic-report.md:995-1021`) |

**High-risk business rules not yet migrated**
None flagged as bugs — straightforward sub-feature of an already-migrated master; verify exact column set against `code-logic-report.md:995-1021`.

---

#### EmployeeAttendanceUploadController — bulk attendance Excel upload (parent cluster: Attendance)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Bulk attendance entry via Excel, with leave-conflict pre-check (`checkleave`) (`backend-report.md:924-933`) | `attendance_register`/`device_attandance` (write path), leave tables (conflict check) | `src/app/(dashboard)/attendance/bulk-upload/page.tsx` | `src/app/api/attendance/bulk-upload/route.ts` | `attendance_register` |

**High-risk business rules not yet migrated**
1. `backend-report.md:897` notes a likely-superseded duplicate (`EmpattendanceuploadController`) — confirm `EmployeeAttendanceUploadController` (1610 lines, "canonical upload controller per naming/breadth") is the one to port, not its smaller sibling.
2. Must call the same `insert_update_att_reg` stored procedure the register-book flow already uses (`src/app/api/attendance/register/process/route.ts:30`) rather than reimplementing day-status computation, to stay consistent with the architectural decision already made for that cluster (§2.1 in the current-state report).

---

### Cluster 2.4 — Company / Organization Setup

#### Bank master CRUD (parent cluster: Company Setup)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `BankController` — bank lookup-table CRUD | `bank` | `src/app/(dashboard)/setup/banks/page.tsx` (reuse `SetupCrudPage.tsx`, same shape as Departments/Designations) | `src/app/api/setup/banks/route.ts` + `[id]/route.ts` | `bank` |

**High-risk business rules not yet migrated**
None flagged — simple master-data CRUD, no fidelity findings recorded against it. Follow the same soft-delete (`status`) pattern already used by Departments/Designations.

---

#### CompanySetupController — subscription/plan/Razorpay payment gating (parent cluster: Company Setup)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Plan/feature gating (`feature_key='company'`) + Razorpay payment integration | `central_control.plan`, `comp_contact_info.plan` | `src/app/(dashboard)/setup/subscription/page.tsx` | `src/app/api/setup/subscription/route.ts`, `.../payment/route.ts` (Razorpay webhook/checkout) | `central_control`, `comp_contact_info`, new `plan_feature` table (recommended consolidation target) |

**High-risk business rules not yet migrated**
1. Legacy's payment endpoint (`CompanySetupController.php:154-158`) has a **raw-SQL-interpolation SQL-injection surface and hardcoded test Razorpay API keys** (`current-state-report.md` §2.4 Finding 5) — these must not be ported; use parameterized queries and environment-sourced credentials.
2. Legacy has **four separate unreconciled plan columns** across the schema — before building, decide on one canonical plan/feature representation (do not replicate the fragmentation); `src/lib/company-config.ts`'s hardcoded-allowlist approach is explicitly a stopgap per its own code comment and should not be treated as the long-term design.

---

#### Company profile — compliance info, logo, policy store (parent cluster: Company Setup, PARTIAL)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `ComplianceInfo` (CIN/PAN/TAN/PF/ESI/PT), logo upload, generic `PolicyInfo` key/value store | `comp_contact_info` (compliance fields), logo file field, policy k/v table | Extend `src/app/(dashboard)/setup/company/page.tsx` with Compliance/Logo/Policy tabs | Extend `src/app/api/company/route.ts` or add `.../compliance/route.ts`, `.../logo/route.ts`, `.../policies/route.ts` | `comp_contact_info` + policy table |

**High-risk business rules not yet migrated**
None flagged as bugs — currently just missing fields on an otherwise-migrated endpoint; verify the exact compliance field set against `user-side-report.md:2576` and `backend-report.md:3052-3091`.

---

#### Branch save side effects — branch_code generation, fin_year auto-create (parent cluster: Company Setup, PARTIAL)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `branches_bi` trigger-generated `branch_code` (`concat(company_code,0,running_count)`); auto-created `fin_year` rows (Financial + Leave) per branch save | `branches`, `fin_year`, control-DB `company_branches` mirror | No new page — modify existing `src/app/(dashboard)/setup/branches/page.tsx` | Add server-side `branch_code` uniqueness/generation to `src/app/api/setup/branches/route.ts:27-40` | `branches`, `fin_year` |

**High-risk business rules not yet migrated**
1. `current-state-report.md` §2.4 Finding 1 classifies decoupling fin_year-from-branch-save as an **intentional net improvement**, but flags that `branch_code` currently has **no uniqueness check or generation scheme** at all in `branches/route.ts:27-40` — this is the one piece that needs fixing, not a full revert to legacy's racy trigger-based approach. Needs a human decision on the exact generation/uniqueness rule.

---

#### Division / Section admin CRUD UI (parent cluster: Company Setup, PARTIAL — API-only today)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `DivisionController`/`SectionController` — guarded-delete CRUD (delete blocked if employees assigned) | `division`, `section`, guard against `emp_vertical`/`emp_sep_priv` | `src/app/(dashboard)/setup/divisions/page.tsx`, `.../sections/page.tsx` (reuse `SetupCrudPage.tsx`) | Extend existing GET-only `src/app/api/setup/divisions/route.ts` and `sections/route.ts` with POST/PUT/DELETE + `[id]/route.ts` | `division`, `section` |

**High-risk business rules not yet migrated**
1. Legacy guarded these deletes against employee assignment; the current app's parallel Grade CRUD **dropped** that guard as an apparent regression (see next item) — when adding delete here, decide consistently across Branch/Department/Designation/Division/Section/Grade whether to add real guards (recommended) rather than repeating the Grade regression (`current-state-report.md` §2.4 Finding 3, NEEDS HUMAN DECISION / LIKELY BUG).

---

#### Grades — guarded-delete regression (parent cluster: Company Setup, PARTIAL)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `GradesController` delete guard — block deletion if `emp_grade` references the grade | `grade`, `emp_proff.emp_grade` | No new page — fix existing `src/app/(dashboard)/setup/grades/page.tsx` | Fix `src/app/api/setup/grades/[id]/route.ts:26-40` — add an `emp_proff.emp_grade` reference check before the soft-delete `UPDATE` | `grade` |

**High-risk business rules not yet migrated**
1. **LIKELY BUG (regression)**: current `DELETE` is a bare `UPDATE grade SET status=0` with no employee-assignment check at all (`current-state-report.md` §2.4 Finding 3) — legacy blocked this. Fix before shipping, since it currently allows dangling `emp_grade` references with zero relational-integrity backstop.

---

#### Notice Period — full CRUD (parent cluster: Company Setup, PARTIAL — downgraded to read-only)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `NoticePeriodController` — full CRUD (`listNotice`/`form`/`deleteNotice`), currently only a GET-only lookup in the new app | notice-period master table | `src/app/(dashboard)/setup/notice-periods/page.tsx` (reuse `SetupCrudPage.tsx`) | Extend `src/app/api/setup/notice-periods/route.ts` with POST/PUT, add `[id]/route.ts` for DELETE | notice-period table |

**High-risk business rules not yet migrated**
None flagged as bugs — straightforward CRUD upgrade of an existing read-only endpoint.

---

#### New-tenant onboarding wizard (parent cluster: Company Setup)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `DbConfigController` — 13-step first-run wizard (was orphaned/dead in legacy itself, per `user-side-report.md:2634-2637`) | seeds Department/Designation/Holiday/Shift tables (all now standing screens) | `src/app/(dashboard)/setup/wizard/page.tsx` (new guided first-run flow, if product wants one) | Orchestration-only route calling the already-migrated `setup/departments`, `setup/designations`, `setup/holidays`, `setup/shifts`, `setup/financial-year` APIs in sequence | no new tables — orchestrates existing ones |

**High-risk business rules not yet migrated**
1. `current-state-report.md` §2.4 Finding 2 is explicit **NEEDS HUMAN DECISION**: legacy's wizard was already dead/orphaned, so not porting it verbatim is defensible, but the underlying *product need* (a guided day-1 setup checklist for new tenants) may still be open. Confirm with product before building — this may not be needed at all if provisioning already happens via DB clone/seed scripts outside the app.

---

### Cluster 2.5 — Dashboards & Notifications

#### Hierarchy/BI dashboards (parent cluster: Dashboards)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `hierarchydashboard()` — manager/approver-scoped dashboard (recursive up-to-6-level subordinate tree, team pending leave) | `emp_config` type=HIERARCHY (already the canonical source per §2.2 migration) | `src/app/(dashboard)/dashboard/team/page.tsx` (manager-scoped view, third branch alongside admin/employee) | `src/app/api/dashboard/team-stats/route.ts` | `emp_config`, leave tables |
| `DashboardNewController` "Analytics" — KPI mega-widgets (salary trend, ESI/PF/PAN gaps, missing-nominee, age-donut, dept headcount, notice-period, retired count) | multiple (`emp_proff`, `emp_salary_slip`, etc.) | `src/app/(dashboard)/analytics/page.tsx` | `src/app/api/analytics/route.ts` (or split per-widget routes) | multiple |
| `BusinessDashboardController` — BI/executive dashboard (company profile, CTC breakup, top/bottom salaries, chart JSON) | `emp_salary_slip`, `comp_contact_info` | `src/app/(dashboard)/business-dashboard/page.tsx` | `src/app/api/business-dashboard/route.ts` | multiple |
| `AnalysisController` — duplicate-asset finder, salary-structure allocation issues, payroll data-quality tool | `asset_management`, salary-structure tables | `src/app/(dashboard)/analysis/page.tsx` | `src/app/api/analysis/route.ts` | multiple |

**High-risk business rules not yet migrated**
1. The **"Pending Leave Approvals" widget is currently gated to `userGroup===1` only** (`stats/route.ts:19`, `dashboard/page.tsx:111`) — non-admin managers/team-leads with approval authority (`ISAutherizedby`/`APPROVEDBY`) see **no** approval widget at all today. This is `current-state-report.md` §2.5 Finding 5, flagged as a possible **functional regression**, not just a missing nice-to-have — verify with product whether manager-level (non-admin) leave approval is still a supported role before treating the Hierarchy Dashboard as low priority.
2. `code-logic-report.md` §7.11 confirms `BusinessDashboardController` and `DashboardNewController::index()` overlap heavily/near-verbatim in legacy — do not port both as separate near-duplicates; consolidate, matching the single-dashboard architectural win already achieved for the admin/employee dashboard (§2.5 Finding 2, positive).

---

#### Tenant-specific hardcoded notifications — site-expiry, CTC-increment reminders (parent cluster: Dashboards)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Site-transaction end-date warning (`AppController.php:137-149`, hardcoded 6-tenant allowlist `vgfs/vsfs/gede/absg/demo/glet`) | `site_transactions` | Notification card on `src/app/(dashboard)/dashboard/page.tsx` | `src/app/api/dashboard/notifications/route.ts` (extend) | `site_transactions` |
| CTC-increment reminder (`AppController.php:150-176`, hardcoded 2-tenant allowlist `demo/glet`) | `next_increment_date` field (or equivalent) | Same notification card | Same route (extend) | employee CTC/increment table |

**High-risk business rules not yet migrated**
1. **NEEDS HUMAN DECISION** (`current-state-report.md` §2.5 Finding 3): these were real, live features serving specific named tenants in legacy, not dead code. If those tenants are still active, silently dropping this is a customer-visible regression. Do not implement as a hardcoded allowlist again — externalize into whatever feature-flag mechanism gets built for `CompanySetupController` (§2.4 item above), per the legacy report's own recommendation.

---

#### Send-Wish flow + menuAudit logging (parent cluster: Dashboards)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Birthday/anniversary "Send Wish" flow (`wish_modal`, PHPMailer wish emails, `Wish` model dedup) | `Wish` model / wish table | Modal on `src/app/(dashboard)/dashboard/page.tsx` (from the existing Upcoming Events card) | `src/app/api/dashboard/wish/route.ts` | wish table |
| `menuAudit` menu-click audit logging | audit log table | N/A (background instrumentation) | Middleware/hook on navigation, writing to `src/app/api/audit/menu/route.ts` | new or existing audit-log table |

**High-risk business rules not yet migrated**
None flagged as bugs — both are lower-priority additive features per the report's own summary ("reasonable prioritization... dead/duplicate legacy code, or lower-value").

---

#### Dropped dashboard widgets — who's-not-clocked-in, announcements, punch/attendance/salary-chart (parent cluster: Dashboards, PARTIAL)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Admin: "who's not clocked in" widget, subscription-plan display, announcements, Zoom-onboarding widget | `device_attandance` (not-clocked-in), plan field, announcements table | Extend `src/app/(dashboard)/dashboard/page.tsx` | Extend `src/app/api/dashboard/stats/route.ts` | multiple |
| Employee/ESS: last-punch, month-attendance grid, salary bar-chart | `device_attandance`, `attendance_register`, `emp_salary_slip` | Extend same page, employee branch | `src/app/api/dashboard/ess-widgets/route.ts` | multiple |

**High-risk business rules not yet migrated**
1. Fix the **birthday/anniversary date-wraparound bug** while touching this area regardless: `current-state-report.md` §2.5 Finding 1 confirms `src/app/api/dashboard/events/route.ts:13-31` copied legacy's **buggy** `%m-%d` string-`BETWEEN` pattern (breaks across the Dec→Jan boundary) rather than legacy's own already-fixed CASE-based version in `DashboardNewController.php:570-638`. Currently dormant (no year-boundary in the active window as of this audit) but will silently fail again ~Dec 25-31, 2026 — low effort, high-value fix, verify by testing with a mocked `CURDATE()` near year-end.

---

#### Notification bell / layout-wide notification surface (parent cluster: Dashboards, PARTIAL — rescoped)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `View/Layouts/default.ctp:550-635` — notification bell visible on every page, not just dashboard | notifications aggregated from multiple tables | `src/components/layout/Header.tsx` (add bell icon + dropdown) | `src/app/api/notifications/route.ts` (aggregate, reusable across pages) | multiple |

**High-risk business rules not yet migrated**
None flagged as bugs — this is a deliberate architecture change already noted as intentional-looking (re-scoped from every-page to dashboard-only); expanding it is additive, not a correctness fix.

---

### Cluster 2.6 — Assets & Documents

#### Full procurement chain — PO/GRN/Vendor/Stock/Item-master (parent cluster: Assets/Documents)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `ItemController` — Item Master | `item_master` | `src/app/(dashboard)/inventory/items/page.tsx` | `src/app/api/inventory/items/route.ts` + `[id]/route.ts` | `item_master` |
| `ItemSpecificationController` — Item Specification Master | item-spec table | `src/app/(dashboard)/inventory/item-specs/page.tsx` | `src/app/api/inventory/item-specs/route.ts` | item-spec table |
| `StockmanagementController` — Stock module landing/feature-gate | plan/feature flags | `src/app/(dashboard)/inventory/page.tsx` (landing) | `src/app/api/inventory/features/route.ts` | plan/feature table |
| `StoreController` — Store Master, Manual Stock Adjustment, PO Return | `store_master`, `stock_details` | `src/app/(dashboard)/inventory/stores/page.tsx`, `.../stock-adjustments/page.tsx`, `.../po-returns/page.tsx` | `src/app/api/inventory/stores/route.ts`, `.../stock-adjustments/route.ts`, `.../po-returns/route.ts` | `store_master`, `stock_details` |
| `SupplierMasterController` — Material Request CRUD (misnamed controller) | material-request table | `src/app/(dashboard)/inventory/material-requests/page.tsx` | `src/app/api/inventory/material-requests/route.ts` | material-request table |
| `VendorController` — Vendor/Contact Master | vendor table | `src/app/(dashboard)/inventory/vendors/page.tsx` | `src/app/api/inventory/vendors/route.ts` | vendor table |
| `VehicleController` — Vehicle Master | `vehicle_master` (confirmed **absent** from legacy schema dump — real legacy bug) | `src/app/(dashboard)/inventory/vehicles/page.tsx` | `src/app/api/inventory/vehicles/route.ts` | `vehicle_master` (verify table exists before building) |
| `PurchaseOrderController` — Purchase Order (MR→PO) | `purchase_order` | `src/app/(dashboard)/inventory/purchase-orders/page.tsx` | `src/app/api/inventory/purchase-orders/route.ts` | `purchase_order` |
| `DirectPurchaseOrderController` — Direct PO (no MR) | `purchase_order` (direct variant) | `src/app/(dashboard)/inventory/direct-po/page.tsx` | `src/app/api/inventory/direct-po/route.ts` | `purchase_order` |
| `GoodsReceivedNotesController` — GRN (stock increment) | `goods_receved_notes` | `src/app/(dashboard)/inventory/grn/page.tsx` | `src/app/api/inventory/grn/route.ts` | `goods_receved_notes`, `stock_details` |
| `StockTranferController` — Inter-store Stock Transfer | `stock_details` (stored-procedure-driven) | `src/app/(dashboard)/inventory/stock-transfers/page.tsx` | `src/app/api/inventory/stock-transfers/route.ts` | `stock_details` |
| `StockReportController` — Stock/GRN/Material/PO/PoReturn/StockTransfer reports | all inventory tables | `src/app/(dashboard)/inventory/reports/page.tsx` | `src/app/api/inventory/reports/route.ts` | all inventory tables |

**High-risk business rules not yet migrated**
1. **This is the single largest scope gap in the whole migration** (`current-state-report.md` §2.6): 12 of 13 legacy inventory/procurement controllers, zero coverage. **NEEDS HUMAN DECISION** before any of this is scheduled — confirm with product whether Inventory/Purchasing is a later phase or genuinely out of scope; nothing in the new app today supports buying/receiving/tracking stock, only registering/allocating already-owned discrete assets.
2. Legacy's stock-quantity tracking used **4 inconsistent mechanisms** across GRN (ORM `saveAll`), PO Return (ORM `save`), Stock Adjustment (raw SQL `INSERT`), and Stock Transfer (stored procedure) with no single source of truth (`code-logic-report.md` §7.9 §3) — do not replicate this inconsistency; pick one mechanism (stored-procedure-based, consistent with the dual-DB strategy already used for attendance/settlement) for all four write paths.
3. Legacy's `testdownloads()` in `PurchaseOrderController` has a confirmed `grn_status` mutation bug (`backend-report.md` §2.9) — verify and fix, do not port verbatim.
4. `vehicle_master` table is confirmed **missing from the legacy schema dump** (`code-logic-report.md` §7.9 §1.4) — verify it actually exists in the live production schema before building the Vehicle Master feature; if it truly doesn't exist, this may be dead/orphaned functionality not worth porting at all.
5. When porting asset-adjacent dual-table updates (allocate/return-equivalent flows in GRN/Stock Transfer), follow the transaction-wrapping pattern already established for `src/app/api/employees/assets/[id]/route.ts:26-48` (explicit `beginTransaction`/`commit`/`rollback`) rather than legacy's un-transacted multi-statement updates.

---

*(DocumentManagerController part (b) — file-upload/allocate library — is listed once under Cluster 2.2 above; it spans both the Employee and Assets/Documents clusters in the source report, but is not duplicated here to avoid double-counting.)*

---

### Cluster 2.7 — Promotions & Resignations

#### Performance-review cycle (parent cluster: Promotions/Resignations)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `SelfReviewController` — executive self-appraisal Part I/II, create/save/delete, duplicate-guard on `emp_fkey+fin_year`, bulk-create | `self_review_details`, `assessment_attributes_staff_details` | `src/app/(dashboard)/employees/reviews/self/page.tsx` | `src/app/api/employees/reviews/self/route.ts` + `[id]/route.ts`, `.../bulk/route.ts` | `self_review_details` |
| `TeamReviewController` — reporting/reviewing officer scoring (executive track) | `self_review_details` (role-filtered by reporting/reviewing officer) | `src/app/(dashboard)/employees/reviews/team/page.tsx` | `src/app/api/employees/reviews/team/route.ts` | `self_review_details` |
| `HierarchyReviewController` — staff/workmen assessment track | `assessment_attributes_staff_details` | `src/app/(dashboard)/employees/reviews/staff/page.tsx` | `src/app/api/employees/reviews/staff/route.ts` | `assessment_attributes_staff_details` |
| `PerformanceController` — HR reporting layer (PDF/Excel exports over review tables) | `assessment_summary_executive`, `self_review_details`, `termination`, `assessment_attributes_staff_details` | `src/app/(dashboard)/employees/reviews/reports/page.tsx` | `src/app/api/employees/reviews/reports/route.ts` | join of above tables |

**High-risk business rules not yet migrated**
1. **Schema availability is unconfirmed**: `code-logic-report.md` §7.10 §0 (line ~5161-5179) flags `self_review_details`, `assessment_attributes_*`, `assessment_summary_executive` as **absent from the schema dump entirely**, but likely a stale/incomplete export rather than evidence the feature is dead (the legacy controllers carry mid-2025 edit timestamps). Do not schedule implementation work until a fresh schema pull confirms these tables exist and matches the column assumptions in the legacy report.
2. `SelfReviewController::listreviews` computes a derived `status_label` via **client-side state-machine display logic** (`backend-report.md:4260`) — when porting, move this to a shared server-side/lib function (matching the new app's general pattern of centralizing business logic in `src/lib/`) rather than duplicating state-label logic per page.
3. `createBulkSelfReview` has a duplicate/branch-fin_year-mismatch skip rule (`backend-report.md:4273`) — verify this exact skip logic is preserved, since silently skipping employees without surfacing why could confuse HR bulk operations.

---

#### ExceptionRuleController — attendance exception → LOP/leave-deduction rule engine (parent cluster: Promotions/Resignations — legacy cluster grouping, functionally Attendance-adjacent)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| Exception-rule CRUD driving automatic LOP/leave deduction on attendance exceptions | `ExceptionRule` (`Model/ShiftExceptions.php`, class/filename mismatch — normalize in port) | `src/app/(dashboard)/setup/exception-rules/page.tsx` | `src/app/api/setup/exception-rules/route.ts` + `[id]/route.ts` | `shift_exceptions` (or renamed equivalent table) |

**High-risk business rules not yet migrated**
1. Legacy's `saveRule` defines a **global function inside the method body** as a `customRound()` helper (`backend-report.md:4418`) — a redeclaration-risk pattern in PHP; irrelevant to port literally but flag as evidence this controller needs careful line-by-line reading, not a quick skim, before implementation.
2. Legacy has **two independent status fields with confusing/inverted semantics** (`status=0` soft-delete flag vs. `activate_status`, `backend-report.md:4421`) — decide on one clean boolean before building the new schema/API, do not replicate the dual-flag confusion.
3. `updateRule` reads raw `php://input` JSON rather than normal request data and **duplicates the leave-type/LOP branching logic inline** instead of reusing `saveRule`'s helper (`backend-report.md:4420`) — consolidate into one shared function in the new implementation.

---

#### `Empagreed` — employee handover-terms agreement step (parent cluster: Promotions/Resignations, PARTIAL)

**Mapping table**
| Legacy item | Legacy Model(s) | Planned new route/page | Planned new API route | Planned data entity |
|---|---|---|---|---|
| `ResignationRequestController::Empagreed` — employee-side "agree to handover terms" step, distinct from the already-migrated manager checklist | `resignation_requests` (`agree` field — confirmed absent from the new app entirely) | Add to `src/app/(dashboard)/employees/resignations/[id]/page.tsx` (employee-visible if ESS resignation flow is ever added) | Extend `src/app/api/resignations/[id]/checklist/route.ts` or add `.../agree/route.ts` | `resignation_requests` |

**High-risk business rules not yet migrated**
1. This is entangled with the larger **NEEDS HUMAN DECISION** on ESS resignation flow: legacy's resignation flow was employee-initiated; the new app restructured it into an admin-only HR data-entry flow (`current-state-report.md` §2.2 Resignations, `userGroup!==1` gated throughout). Note also `current-state-report.md` §2.7 Finding "2.4" — legacy's `agree` no-op bug is **not reproduced only because the field doesn't exist at all**, not because it was fixed; if ESS self-service resignation is ever built, decide deliberately whether `agree` should gate anything functionally (legacy's version was a no-op bug) rather than accidentally reintroducing a dead field.

---

### Cross-cutting notes for planning

- Several items above depend on a **shared plan/feature-flag mechanism** that doesn't exist cleanly today (`EmployeeManageController` tiles, `AttendanceSetupController` tiles, `CompanySetupController` subscription gating, tenant-specific dashboard notifications). Recommend resolving the plan/feature-flag architecture question once, then wiring all four consumers to it, rather than building four bespoke gating mechanisms.
- The **guarded-delete inconsistency** found in Company Setup (Grades regression, Division/Section never implemented) should be resolved as one cross-cutting decision (add FK-based or app-level guards consistently across Branch/Department/Designation/Division/Section/Grade) rather than fixed piecemeal per entity.
- Several NOT-STARTED items (SSO-bridge, `InfoController` phpinfo, unauthenticated bypass controllers) are legacy vulnerabilities **correctly and deliberately not reproduced** — these are not gaps to close, they are confirmed non-issues, called out here only for completeness per the source report's own classification.

---

## 5. Data Migration Plan

### 5.1 Schema Deltas

The new app introduces **no new tables or columns** — every table/column referenced in `rizo/src/app/api/**/route.ts` and `rizo/src/lib/*.ts` (confirmed by grep against `mypayrol_trial.sql`/`mypayrol_control_db.sql`: `emp_config` L42530, `emp_ctc_upload` L42649, `emp_join` L43328, `resignation_requests` L45794, `termination` L47592, and all other FROM/JOIN/UPDATE/INTO targets — `emp_details`, `emp_proff`, `attendance_register`, `branches`, `designation`, `department`, `user_credentials`, `holidays`, `emp_documents`, `doc_template`, `device_attandance`, `asset_management`, `asset_allocate`, `emp_tax_transactions`, `emp_ot_master`, `fin_year`, `family`, `education`, `work_experience`, `working_day_time_procedures`, `emp_detail_timeattandance`, `user_access`, `mob_user_credentials`, `emp_shift_planner`, `shift_exceptions`, `holiday_group`, `grade`, `promotions`, `employee_regularaization`, `resignation_accept`, `emp_settle_slip`, `device_attandance_hist` — all exist in the legacy dump). **The delta is entirely semantic/behavioral**, not structural: the new app writes narrower or repurposed values into existing columns. This is more dangerous than a structural delta because nothing in the schema itself will flag the mismatch — only application logic on either side knows the "real" meaning of a value, and the two apps now disagree.

| Table.Column | Legacy semantics | New-app semantics/usage | Risk if unreconciled |
|---|---|---|---|
| `emp_join.status` | Legacy tracks onboarding as a percentage-complete progression (multi-step wizard with a completion-% indicator and completion email), per `code-logic-report.md` / `current-state-report.md` §2.1 (lines 212-217, 223). | Used purely as a binary in-progress(1)/promoted(0) flag: `UPDATE emp_join SET status = 0, emp_fkey = ?` (`rizo/src/app/api/employees/join/[id]/onboard/route.ts:142`). No `%` tracking, no completion email. | If legacy code (or a not-yet-migrated report) elsewhere reads `emp_join.status` expecting a percentage, it will misinterpret 0/1 as 0%/1%-complete rather than done/in-progress. Any employee onboarded via the new app will show wrong onboarding progress in legacy-owned screens still reading this column. |
| `emp_details.status` | 3-value state machine: `0=inactive, 1=active, 2=terminated-pending-payroll` (transient, set only by `EmployeeResignationController` during offboarding, per `current-state-report.md` §2.2, lines 227-231). | Narrowed to a 2-value admin-clickable toggle between `1` (Active) and `2` (relabeled generically "resigned/inactive"); `status=0` is never reachable through `rizo/src/app/api/employees/[id]/route.ts:18-19`, which hard-validates only `{1,2}`. | LIKELY BUG (already flagged, §2.2): conflates a manual admin deactivate/reactivate action with the payroll-settlement transient state. If the Resignations cluster (still legacy-owned per phase plan) or a not-yet-built Payroll stored procedure later reads `status=2` expecting "mid-offboarding, awaiting Full & Final," an admin's manual "Deactivate" click in the new app could trigger unverified-attendance-processing logic that was never intended to fire, or block legacy's real offboarding flow from ever reaching status 2 cleanly. `status=0` (true inactive) becoming unreachable also means any legacy report/query that filters `WHERE status=0` will silently stop seeing employees the new app "deactivated." |
| `user.reset_login_flag` | `'Y'` routes the user to an actual password-reset token/URL flow (`LoginManagementComponent.php`, per `code-logic-report.md` §7.12). | `rizo/src/lib/auth.ts:58,126` only checks the flag and rejects login (`return null`) — no reset-token/URL generation exists anywhere in `rizo/src/app`. | LIKELY BUG (already flagged as F1). Functionally this is a data-migration risk too: any row where an admin (via legacy or a future new-app "reset password" action) sets `reset_login_flag='Y'` becomes a **permanent lockout** under the new app, indistinguishable from a wrong password. If this happens during the coexistence period on an employee whose auth traffic has already cut over to the new app, there is no self-service path back in. |
| `user.end_date` | Presumed original purpose is an employment/account end-date field (not confirmed from reports, but named and typed like one). | Repurposed by the new app to store "date of last failed login attempt" — `rizo/src/lib/auth.ts:144-149`: `SET ... end_date = CURDATE()`, read back at `:101` to decide whether to reset the login-attempt counter for the day. | NEEDS CONFIRMATION before any Auth cutover: if any legacy code path (or a not-yet-migrated cluster) reads `user.end_date` for its original employment-end meaning, the new app is silently corrupting that field on every failed login. This must be resolved (rename/new column, or confirm the field is genuinely dead in legacy) before Auth fully cuts over, since both apps will be writing/reading `user_credentials`/`user` concurrently during transition. |
| `emp_details.parent`, `emp_structure` | Two of legacy's three uncoordinated hierarchy stores (the third is `emp_proff.attr1`, maintained by the `emp_config_bi`/`emp_config_au` triggers for `emp_config` rows with `type='HIERARCHY'`), per `current-state-report.md` §2.4 (lines 275-292). | Not written or read anywhere in `hierarchy/route.ts` or `HierarchyMover.tsx` (confirmed via grep) — the new app writes only `emp_config` (`type=HIERARCHY`) and relies on the existing trigger to sync `emp_proff.attr1`. | INTENTIONAL-LOOKING and a genuine consolidation (positive), but flagged as a cross-cluster risk: any **still-legacy-owned** screen that reads `emp_details.parent` or `emp_structure` directly (e.g. `HierarchyReportController` per `user-side-report.md:2493`, part of the not-yet-migrated Reports cluster) will silently diverge from the new canonical source (`emp_config`) the moment an employee's manager is reassigned through the new app — those two legacy-only columns simply stop being updated. Must be checked/reconciled before the Reports cluster is migrated, and ideally before Employee/Attendance cutover if legacy Reports remains live against the same DB during the transition. |
| `user_access` / `emp_menu` (permission tree) | Same architecture as new app: UI-advisory only, no server-side enforcement gating other endpoints, per `backend-report.md` §3.3. | `rizo/src/app/api/employees/menu-allocation/[id]/route.ts:80-101` replicates the same gap — flat upsert/deactivate of `user_access` rows, no cascade logic (no parent/child auto-enable, no add-on/`menu_id=0` handling that legacy's cascade had). | Not a schema delta per se, but a fidelity gap worth tracking here because it affects the same rows both apps write: an admin using the new app's flatter menu-allocation UI can produce `user_access` states (e.g. a child menu enabled with its parent disabled) that legacy's cascade logic never would have produced, and that legacy's own UI may render inconsistently if a user's admin session reverts to legacy mid-transition. |
| **NOT-STARTED clusters** (Leave, Payroll, Advances, Reports, Site-Field, Statutory) | Full stored-procedure-driven schema and logic — e.g. `calculate_monthly_salary_components_prc`, `calculate_statutory_components_prc`, `insert_update_att_reg`/`_hierarchy`/`_rep`/`_view`, `leave_transaction_prc`, `leave_balance_inthe_month_fn`, `leave_encash_prc`, `carryforward_insert_prc`, `mark_site_attendance_fn`, and ~80 others (see `code-logic-report.md` §procedure inventory) touching `attendance_register`, `emp_leave_balance_year`, `emp_leave_transactions`, `leavepolicy`, `salary_structure`, `emp_salary_structure`, `emp_ctc_transaction`, `payroll_master`, `site`, `site_attendance`, etc. | No code exists yet under `rizo/src` for these clusters. | No delta to reconcile today — nothing to compare. The requirement going forward is that this stored-procedure logic must be **carried over unchanged** (or reimplemented with byte-for-byte equivalent business rules) when this work begins, since the tables listed above are already being written by the migrated Employee/Attendance clusters — the incoming Leave/Payroll build-out inherits whatever `emp_details.status`, `emp_config` hierarchy, and `attendance_register` state the earlier clusters have already produced, including the deltas noted above. |

### 5.2 Cutover Approach

Because both apps target the **same physical MySQL database** (same `control_db` lookup pattern in `rizo/src/lib/db.ts`, same per-tenant company DB, same table names, no separate schema or replica for the new app), this cannot be a big-bang cutover — it has to be a **module-by-module traffic split against one shared, continuously-live dataset**, sequenced by the same dependency order already established for the build-out: Auth → Employee/Attendance (built) → Setup/master-data (built) → Documents/Promotions/Resignations (built) → Leave/Payroll/Advances/Reports/Site-Field/Statutory (not started, and largely dependent on Employee/Attendance data being correct first).

**Routing mechanism.** Cutover is a routing decision, not a data decision: a reverse-proxy/gateway (or DNS-level split by subdomain, e.g. `app.<tenant>.rizo` vs the legacy URL) directs a **user's entire session** to either the legacy CakePHP app or the new Next.js app based on which modules are "cut" for that tenant — not a per-request module router, since both apps re-derive session/auth state independently (`rizo/src/lib/auth.ts` vs `LoginManagementComponent.php`) and a user bouncing between the two mid-session would face two different login/lockout state machines reading the same `user_credentials` row. In practice this means Auth must be the **first and only irreversible-per-tenant cut**: once a tenant's users log in through the new app, the account-lockout/reset-flow bug (F1) and the `end_date` repurposing (§5.1) are live against that tenant's rows immediately, whether or not any other module has cut over yet.

**Coexistence risk during the transition window.** For any tenant partway through cutover (e.g. Employee/Attendance/Auth on the new app, Leave/Payroll still legacy), both apps are writing to overlapping tables:
- `emp_details`, `emp_proff`, `emp_config` are touched by Employee/Attendance (new) **and** by legacy's Leave/Payroll stored procedures (`calculate_monthly_salary_components_prc`, `insert_update_att_reg*`, etc., which all read `emp_details`/`emp_proff`/`attendance_register` as inputs). This is intentional and required — Leave/Payroll must see the new app's writes — but it means the schema-delta items in §5.1 (`status` narrowed to 2 values, hierarchy consolidated to `emp_config`) are live correctness risks the moment Employee cuts over, not deferred until Leave/Payroll is built. The legacy stored procedures were written assuming `emp_details.status` can be `0`; if the new app never produces a `0` again, any procedure with `WHERE status=0` branching silently stops matching rows it used to. This should be resolved (either the new app restores the 3rd status value, or the legacy procedures are audited for `status=0` dependencies) **before** Employee/Attendance cuts over for a given tenant, not treated as a Leave/Payroll-phase problem.
- `resignation_requests`/`resignation_accept`/`termination` are already written by the new app's Resignations cluster but are also read by legacy's offboarding-aware payroll procedures (`ot_duration_register_date`, `insert_update_att_reg` both JOIN `termination`). Sequencing Resignations' cutover ahead of Payroll's build-out means Payroll, when built, must be written against whatever shape the new app's Resignations flow actually produces — not the legacy `EmployeeResignationController` shape — which argues for validating the Resignations-cluster fidelity gaps (status=2 collision, per §5.1) before Payroll development starts, not after.

**Sequencing, concretely:**
1. **Auth** cuts over per-tenant first (already built), gated on resolving F1 (reset-flow dead-end) and confirming `end_date` reuse is safe — both are pre-existing bugs from the fidelity report, not new risk, but Auth is the point of no return for a tenant so they should be fixed first.
2. **Employee/Attendance/Setup/Documents/Promotions/Resignations** (the built clusters) cut over together per-tenant once the `emp_details.status` and hierarchy deltas are reconciled against any legacy code (Reports, Leave, Payroll) still reading those tables directly for that tenant.
3. **Leave/Payroll/Advances/Reports/Site-Field/Statutory** stay on legacy until built. During this window, legacy's stored procedures continue to be the sole writers/readers of `attendance_register`, `leavepolicy`, `salary_structure`, `payroll_master`, etc., but now consume `emp_details`/`emp_config`/`attendance_register` rows partly produced by the new app — making the §5.1 reconciliation a hard prerequisite, not a nice-to-have, before this phase's design is finalized.
4. Only once all six NOT-STARTED clusters are built and validated does a tenant's remaining legacy traffic (if any) get cut over, retiring the legacy app for that tenant.

### 5.3 Rollback Strategy

Because there is no schema migration tooling and both apps share one live database, "rollback" cannot mean restoring a database snapshot without losing every write either app made since cutover — that is only viable as a last-resort, whole-tenant, data-loss-accepting action. The realistic rollback unit is **traffic**, not data: per-module, flip the routing/feature-flag back to the legacy URL/subdomain for the affected tenant, exactly mirroring how cutover itself is routing-based (§5.2). This works because both apps read the same live tables — reverting traffic doesn't require reverting any row, only redirecting the next request to the app that used to serve it.

The risk is not "can we roll back" but **"does legacy still understand the rows the new app wrote while it was live"** — this varies sharply by module:

- **Auth rollback**: highest risk. If a tenant's users logged in through the new app after cutover, any row where `reset_login_flag='Y'` got set is now a dead-end under the new app (F1) — rolling back to legacy actually *fixes* this specific case, since legacy's reset-token flow still works. The bigger risk is `user.end_date`: if repurposed as "date of last failed login attempt" by the new app and legacy expects it as an employment end-date, rolling back traffic to legacy means legacy will read a corrupted value for any user who had a failed login while the new app was live. This must be resolved (§5.1) before Auth ever cuts over for a tenant, precisely because it's the hardest module to safely roll back from — every login attempt on the new app is a potential silent write to a field legacy may interpret differently.
- **Employee status rollback**: if an admin used the new app's list-page "Deactivate"/"Activate" toggle (writing `status=2` generically) and traffic then rolls back to legacy, legacy will interpret any `status=2` row as "terminated, pending payroll settlement" — a transient state that legacy's payroll-settlement flow expects to *resolve* (moving the employee to `status=0`). An employee the new app just "deactivated" via a simple toggle will appear to legacy as mid-offboarding and pending Full & Final settlement, potentially surfacing in legacy payroll-processing screens or blocking attendance processing in ways the admin never intended. This is the single highest-priority item to fix (or explicitly document as a known gap with a manual reconciliation script) before Employee/Attendance cutover, since it's directly triggered by routine post-cutover usage, not an edge case.
- **Hierarchy rollback**: if Employee cuts over and hierarchy changes are made only via `emp_config` (new app), then traffic rolls back to legacy, legacy code paths that read `emp_details.parent`/`emp_structure` directly (rather than `emp_config`/`emp_proff.attr1`) will show **stale** management-chain data — those two columns simply stopped being updated during the new-app window. Any legacy report run post-rollback using those columns needs a one-time backfill script (rebuild `emp_details.parent`/`emp_structure` from `emp_config` type=HIERARCHY rows) before it can be trusted again.
- **Onboarding rollback** (`emp_join.status`): low risk in isolation since it's a narrow binary flag, but if legacy's onboarding screens expect a percentage and instead see 0/1, any in-flight onboarding candidate mid-wizard when rollback happens will show a nonsensical progress value; acceptable to leave as a known cosmetic issue rather than block rollback.
- **Menu/permission (`user_access`) rollback**: lower risk since both apps already treat this as UI-only/advisory with no server-side enforcement on either side — a state that violates legacy's cascade invariants (child enabled, parent disabled) will just render oddly in legacy's tree UI post-rollback, not break access control, since neither app enforces it server-side today.

**General rollback principle for this project**: every module's rollback plan should be paired with a short reconciliation script (SQL, run once) that normalizes any rows the new app wrote in a legacy-incompatible shape, rather than assuming a routing flip alone is sufficient. Given the schema-delta table in §5.1, at minimum the Employee-status and hierarchy reconciliation scripts should be written and tested *before* those modules cut over — not authored reactively after a rollback is already needed.

---

## 6. Open Questions Needing Human Decision

1. Should password-reset via the Access screen require building a full reset-token/URL completion flow (matching legacy), or a simpler forced-change-on-next-login screen — either way, what clears `reset_login_flag` back to `'N'`?
2. Does legacy's login-lockout counter reset only on a successful login, or is a calendar-day-boundary reset (as newly implemented) an acceptable behavior change?
3. Is `emp_details.end_date` used elsewhere in the new schema for its original employment-end-date meaning, conflicting with its repurposing to store "date of last failed login attempt"?
4. Is losing the `user_access`/`emp_menu` parent/child cascade UX (auto-enable parent, last-sibling-disable) an acceptable simplification, or will admins notice and expect it back?
5. Should `user_access`/`emp_menu` become server-enforced in the new app (closing the legacy gap), or is UI-only enforcement acceptable for this migration's scope?
6. Should there be a manual admin "Deactivate/Activate" toggle on the employee list at all, given legacy only ever changed employee status through the Resignation workflow?
7. Does the Resignations cluster's write to `emp_details.status=2` collide with the Employee list page's manual toggle to the same value?
8. Was the drastically weaker new/edit-employee form validation (only `first_name` required) a deliberate simplification, or should it be brought back in line with legacy's ~15 required fields?
9. Was dropping the onboarding completion-percentage tracker and its completion email a deliberate simplification (given the new app's single-step onboarding form), or an overlooked feature loss?
10. Is Multishift (`MSHIFT`) bulk-policy allocation planned for a later pass, or deliberately deprioritized?
11. Does the app-side write to `emp_proff` plus the DB trigger firing on the same `emp_config` insert (for the 7 covered bulk-policy types) create a race/ordering risk that needs addressing?
12. Does the excluded `employees/access` cluster already implement a `user_access` write path, making the new `menu-allocation` admin editor a duplicate capability?
13. Does legacy's `getDefaultMenus`/`getAddonMenus` (an ESS user's own live-rendered nav, with company-specific feature-key remapping) need a dedicated read-side port distinct from the new admin allocation editor?
14. Is the `beneficiary` table real/populated in any live legacy tenant database, or genuinely dead — and should this be verified before building the feature at all?
15. Should `branch_code` get a uniqueness constraint or generation scheme in the new schema, now that the legacy trigger-based uniqueness guarantee is gone?
16. Is a guided "day-1 setup" wizard for brand-new tenants still a real product need, or are the current always-available standing settings screens (departments, holidays, shifts, etc.) sufficient?
17. Should employee-assignment delete-guards be added consistently across Branch/Department/Designation/Division/Section/Grade, or is it acceptable that none of them protect against deleting in-use records (and Grade specifically regressed from having a guard)?
18. Does the product still need subscription-tier/plan-based feature gating and a payment flow (legacy's Razorpay integration), given the new app has no equivalent at all?
19. Should `company-config.ts`'s five hardcoded company-code allowlists be migrated into the already-built `features.ts`/`plan_feature` system now, rather than left as a "temporary" shim?
20. Should the org-structure hierarchy (branch → department → designation → division → section → grade) get real FK-based relational enforcement in the new schema, or is the flat/unenforced structure (faithfully replicating legacy) acceptable long-term?
21. Are the tenant-specific hardcoded notifications (site-expiry warning, CTC-increment reminder) still needed by the specific legacy tenants they served (6 for site-expiry, 2 for CTC-increment), or can they be dropped/deferred?
22. Is manager-level (non-admin) leave-approval authority still a supported role in the new app, and should the "Pending Leave Approvals" dashboard widget be gated by actual per-row approval authority instead of `userGroup===1`?
23. Is full Inventory/Purchasing (Material Request → PO → GRN → Stock, Item/Vendor/Store masters) planned for a later migration phase, or intentionally out of scope for this rewrite?
24. Are the dropped Supplier/Customer/Others document-template contexts (and image-composite/salary-breakup tokens) needed, or is the reduced Employee/Company/Branch-only token set sufficient?
25. Are manager-initiated promotion requests (self-service submission for direct reports, routed to a specific approver) in scope for this app, or intentionally centralized to single-tier HR-only approval?
26. Is employee self-service (ESS) resignation submission planned for a later phase, or intentionally and permanently replaced by the current HR-only data-entry flow?
27. Is the new app's hierarchy-reference cleanup on resignation finalize (clearing other employees' manager references to a terminated employee) the intended behavior, or should dangling references be preserved for audit-trail purposes as legacy did?
28. Are the stored procedures/functions the attendance and settlement features depend on (`insert_update_att_reg`, `device_logs_resync_fn`, `device_logs_iteration_fn`, `calculate_ot_allowance_prc`, `leave_transaction_prc`, `final_settle_pay_prc`, `leave_encash_prc`, etc.) confirmed provisioned and unmodified on every tenant database the multi-tenant app will connect to?
29. Is the retained 32-column `FIELD1..FIELD32` denormalized attendance-day storage a permanent architectural decision (tied to the shared legacy DB/stored-procedure strategy), or should it eventually be normalized as the legacy report recommended?
30. Was keeping the `isdelete` column's literal legacy name and inverted polarity (`'Y'`=unverified) a deliberate "don't fork the shared schema" decision, or should the API layer expose a properly-named `is_verified` boolean going forward?
