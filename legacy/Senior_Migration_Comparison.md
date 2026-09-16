# Senior's "New Rizo" Migration — Comparison Report

*Source reviewed: `C:\Users\aksha\Downloads\Rizo (1)\Rizo` (read-only). Compared against our migration at `D:\Projects\RIZOMigration` and the shared legacy schema in `legacy/schema/`.*

---

## 1. Executive Summary

The senior's migration is a **full re-architecture, not a parallel build**: MySQL 5.7 (database-per-tenant) becomes a **single PostgreSQL 15 database with `company_id`-column multi-tenancy**, CakePHP/PHP becomes **Node.js/Express + `pg`**, and every legacy stored procedure/function/trigger is **reimplemented as plain JS in controllers/services** rather than ported as Postgres functions — confirmed by reading the actual `payroll.service.js` and `tax.service.js` code, not just the docs. It is **not compatible with the live legacy MySQL DB in any form** (different engine, different schema, different tenancy model) — it requires a genuine one-time ETL migration. Critically, **that ETL has not actually been run or even coded**: the 2,113-line `_MIGRATION_GUIDE.md` is a detailed *plan* (scripts are shown as "template"/pseudocode), but no `migration/` directory, no `migration_id_map` table, and no runnable migration scripts exist anywhere in the repo. The Postgres schema itself is also not fully captured in code — only 7 tables are created via checked-in `CREATE TABLE` statements (`company`, `salary_structures`, `salary_structure_details`, `employee_promotions`, `event_reminders`, `attendance_exception_rules/applied`, `user_branch_access`); core tables like `employees`, `payroll_master`, `attendance`, `emp_tax_computation` are not defined anywhere in the codebase, meaning the working dev schema was hand-built and isn't reproducible from source control alone.

---

## 2. Schema / DB Changes

| Aspect | Legacy MySQL | Senior's New Rizo (PostgreSQL) |
|---|---|---|
| Engine | MySQL 5.7 | PostgreSQL 15 |
| Tenant isolation | One physical DB per tenant (`mypayrol_<code>`) | One shared DB (`rizo`), `company_id` column on every table |
| Control/routing DB | Separate `mypayrol_control_db` (`central_control`, `user_credentials`, cross-DB dynamic SQL) | Collapsed into the same single DB — `company`, `user_credentials`, `user_company_access` join table; auth is a plain JWT login (`auth.controller.js`) with no cross-DB routing at all |
| Primary keys | `int(11) AUTO_INCREMENT` | `bigserial`; old ID preserved as `original_id` per the migration plan (ID-remapping via `migration_id_map`, **not yet implemented**) |
| Booleans | `tinyint(1)` / `char(1)` `'Y'/'N'` | native `boolean` |
| `month_year` varchar dates | `'06-2024'` string | proper `date` column, first-of-month |
| Attendance model | `attendance_register` — 1 row/employee/month, `FIELD1`–`FIELD31` daily columns (anti-pattern) | 1 row/employee/day (`attendance` table) — genuine normalization improvement, still only a plan per docs (no unpivot script found in code) |
| Character encoding | `latin1` / `latin1_bin` | UTF-8 |
| Naming | `emp_details`, `middile_name` (typo), etc. | Renamed/cleaned: `employees`, `middle_name`, etc. (per `2_DATABASE_SCHEMA_MAPPING.md`) |
| Schema definition | Single canonical `.sql` dump, hand-authored procs/triggers | **No canonical schema file.** Only `New Rizo/db/init.sql` (40 lines, just `company`) + ad-hoc `CREATE TABLE IF NOT EXISTS` scattered across 5 controller files + runtime `ALTER TABLE ADD COLUMN IF NOT EXISTS` (e.g. `tax.service.js` `ensureDualColumns()`). Most core tables (employees, payroll_master, attendance, salary_heads, income_tax_slab, fin_year, emp_tax_declarations…) have no CREATE TABLE anywhere in the repo. |
| Migration tooling | N/A (source) | `migration_id_map` table and Node.js ETL scripts are **documented only** — zero migration code found under any path in the repo (`find . -iname "*migrat*"` outside `DOCS/` returns nothing) |

---

## 3. Stored Procedure / Function / Trigger Fate

Legend: **Ported** = exists as a Postgres function/procedure · **Reimplemented** = logic rewritten in JS · **Planned only** = documented "New-System Notes" intent, no code found · **Dropped** = intentionally not carried forward.

| Legacy object | Domain | Fate in New Rizo | Risk note |
|---|---|---|---|
| `calculate_emp_salary_breakup` | Salary breakup | **Reimplemented in JS** — `computeSalaryBreakup()` in `payroll.service.js` covers formula/fixed/limit/limit_wl/limit_wg/rembalance operators | Verified present and structurally close to legacy logic, but not tested against legacy output; rounding/order-of-operations subtleties can silently diverge |
| `calculate_salary_main_prc` (master orchestrator: attendance → base pay → holiday/shift/OT allowances → leave encashment → variables → **advances/loan EMI** → PF/ESI → PT → TDS) | Payroll | **Partially reimplemented** — `payroll.service.js::processEmployee()` only does attendance-based prorate + structure breakup + net total. Holiday/shift/OT allowance calc, leave encashment, and **advance/loan EMI deduction are absent** (`grep` for `emp_advance`/`emp_loan` across backend finds no matches) | High risk: real payroll runs with advances, loans, OT, or leave encashment will produce wrong net pay if migrated as-is |
| `salary_process_prc`, `tax_salary_process_prc` | Payroll (alt paths, limit post-pass) | **Not found** — no separate limit-correction post-pass observed in `payroll.service.js` (limit_wl/limit_wg handled inline in `computeSalaryBreakup`, which the docs themselves say is fine to "fold in") | Low-medium risk if inline handling is logically equivalent — unverified |
| `calculate_holiday_allowances_prc`, `calculate_shift_allowance_prc`, `calculate_ot_allowance_prc`, `calculate_leave_encashment_prc` | Payroll add-ons | **Not found in code** (docs list "New-System Notes" only) | Functional gap — these amounts are simply missing from computed payroll today |
| `calculate_statutory_components_prc` (PF/ESI) | Statutory | **Reimplemented, but as a report, not as part of payroll processing** — `statutory.controller.js::epfReport()`/`epfContribution()` compute EPF/EPS/EDLI from `payroll_master.gross_salary` post-hoc with hardcoded 12%/8.33%/3.67%/0.5% rates and a flat ₹15,000 wage ceiling | Medium-high risk: legacy nuances (VPF, admin charges edge cases, ceiling exceptions, employer-vs-employee split rules) are not verified; PF is a reporting artifact rather than a payroll deduction line item baked into `processEmployee()` |
| `profession_tax_cal_fn` | Statutory | **Not confirmed** — no dedicated PT calculation found in the read files (only referenced in docs) | Gap — needs verification |
| `tax_computation_fn`, `tax_salary_distribution_fn`, `tax_salary_distribution_new_fn` | Income tax (TDS) | **Reimplemented in JS** — `tax.service.js::computeTaxForEmployee()` implements full old/new-regime slab tax, rebate (§87A), marginal relief, cess, HRA exemption (3-way least-of), and monthly TDS spread | Most complete reimplementation found; still a from-scratch rewrite of complex statutory math with no cross-check against legacy `tax_computation_report` values shown in code |
| `vangrd_pfesi_update_fn` | Statutory | **Planned only** (docs) | — |
| `leave_end_process_fn`, `year_ending_fn`, `year_ending_mission_fn` (leave year-end carry-forward) | Leave | **Not found in code** — `leave.controller.js` (382 lines) has no carry-forward/year-end routine; docs describe replacing the control-DB cron (`leave_end_process_parent_prc`) with a job queue, but no scheduler/job code exists | High risk / functional gap: annual leave carry-forward, a correctness-sensitive area per our own migration's lessons, appears unimplemented |
| `leave_encash_insert_prc`, `leave_encash_prc`, `leave_transaction_prc`, `leave_taken_fn`, `leave_balance_inthe_year_fn`, `leave_rules_fn`, `Leave_balance_upload_fn` | Leave | **Planned only / not verified** — some CRUD exists in `leave.controller.js` but not confirmed to replicate rule engine (accrual, policy-based caps, negative balance handling) | Needs deeper verification before go-live |
| `att_start_end_fn`, `process_att_reg_fn`, `insert_update_att_reg*`, `weekoff_days_count_fn` | Attendance | **Reimplemented, simplified** — `countAttendanceDays()` in `payroll.service.js` does a basic P/L/LOP/WO-HO tally from a flat `attendance` table (one row/day) — much simpler than legacy's FIELD1-31 + hierarchy/rep/view variants | Simplification is arguably correct for the new normalized model, but half-day split-punch codes (`P/L`, `WO/P`, etc.) and holiday/OT interplay are not fully verified |
| Biometric/device sync procs (`time_duration_check*`, `Linkemp_deviceanddatabase`, `mark_site_attendance_fn`, `last_punch_fn`, etc.) | Biometric | `adms.controller.js` exists (ADMS integration) — **partially covered**, not deep-verified | Out of scope for this pass |
| Site/contract labour procs (`site_*`) | Site labour | **Not found** — no dedicated site-labour controller identified | Gap if this business line is in scope |
| `final_settle_pay_prc`, `payroll_master_approve` | Full & Final | **Present** — `fullFinal.controller.js` exists | Logic fidelity not deep-verified |
| `master_auidt_fn`, `user_access_firstime_only`, `insert_default_menu` | Utility | **Dropped/superseded** — docs correctly note RBAC/JWT replaces one-time-access setup | Intentional, low risk |
| Inventory/stock procs (`stock_bal_qty_fn`, `stock_tranfer_*`) | Inventory | **Out of scope** — migration guide explicitly excludes inventory/procurement tables | Intentional |
| **Control DB**: `CreateDatabasesAndUsers`, `generateDatabase`, `trial_signup_fn`, `single_signon_fn`, `emp_id_maping_prc`, `inactive_db_audit_prc`, `company_statistics_prc` | Platform admin | **Architecturally obsolete by design** — single-DB model has no per-tenant DB provisioning, no cross-DB dynamic SQL, no SSO-via-DB-name-lookup. Replaced conceptually by REST tenant management + JWT, but provisioning-service code not found | Intentional re-architecture — not a gap, but confirms the two systems can never share a control-plane concept |
| `devicelogs_bi`, `set_emp_id` (control DB triggers) | Biometric routing | **Not found** — would need an API-gateway equivalent per docs' own recommendation | Needs verification against `adms.controller.js` |
| Schema-maintenance procs (`alter_emp_advance_affected_month`, `update_income_tax_slab_2026`, etc.) | Ops/deploy | **Dropped** — correctly noted as candidates for a real migration framework (node-pg-migrate/Flyway), but **no such framework is actually wired in** (schema is ad-hoc `CREATE TABLE IF NOT EXISTS` in controllers) | See §2 — this is the single biggest structural risk in the whole system |

**Overall pattern:** No legacy MySQL procedure/function/trigger was "ported" as a Postgres function/procedure anywhere — Postgres is used purely as a dumb store; 100% of surviving business logic lives in Node.js. Where checked directly against code (salary breakup, income tax) the reimplementation is genuinely present and reasonably thorough. Where only checked against docs (leave carry-forward, holiday/OT/shift allowances, advances/loans, PT), the logic **appears to be missing from the actual codebase**, not merely "ported elsewhere."

---

## 4. Compatibility Verdict

**No — the senior's system cannot run against or alongside the live legacy MySQL database, under any configuration.**

- Different DB engine entirely (PostgreSQL vs MySQL) — the app's `pg` driver and SQL dialect (bigserial, boolean, ON CONFLICT, information_schema calls) cannot target a MySQL 5.7 connection.
- Different schema shape — renamed tables/columns, restructured attendance model (one row/day vs FIELD1-31), collapsed control DB, `company_id` multi-tenancy instead of physical DB-per-tenant.
- Different ID space — new system uses fresh `bigserial` PKs; legacy IDs only survive as an `original_id` reference column, resolved through a `migration_id_map` table.

This is a one-way, one-time ETL migration by design, exactly as their own `_MIGRATION_GUIDE.md` states (the doc's `4a`–`4h` transformation rules and the `migration_id_map` FK-resolution strategy only make sense as an export/transform/load process, not a live compatibility layer).

---

## 5. What's Needed to Go Live with the Senior's System

**The legacy database itself needs no schema changes** — it stays exactly as-is, used only as a **read-only export source** for one, or a few (delta), ETL runs. All the work is on the new-system side. Concretely, per their own `_MIGRATION_GUIDE.md` (§2, §10, Appendix B) — none of which has been executed yet:

1. **Build the migration tooling that doesn't yet exist** — a real `migration/` directory with Node.js scripts (`mysql2` + `pg`), the `migration_id_map` table, and the per-domain scripts the guide only sketches as pseudocode/templates.
2. **Finish the target Postgres schema** — currently only ~7 tables are defined in checked-in code; the rest of the schema that the running dev instance clearly has (employees, payroll_master, attendance, tax tables, etc.) needs to be captured as a real, versioned schema/migration set (guide recommends node-pg-migrate or Flyway — not currently used).
3. **Pre-migration checklist** (§2 of guide): full MySQL dumps + checksums, test restore, data-quality queries (orphaned `emp_proff`, mismatched leave balances, orphaned salary slips), UTF-8 conversion plan, and a signed-off freeze window (48h notice).
4. **Run and validate a full dry run** against a staging Postgres instance for at least one real tenant, using the guide's Section 8 validation queries (row counts, payroll totals, leave balances, attendance day counts, orphaned FKs, tax completeness, structure integrity).
5. **Cutover** (§10): freeze legacy app → delta-sync any rows created since the dry run → final validation → switch app config to Postgres → smoke tests (login, employee list, last payroll slip, leave balances, attendance register, PF report) → **15-day parallel run** comparing new-system payroll output against what legacy would have produced, with any >0.01% discrepancy blocking sign-off → 30-day decommission window before archiving MySQL dumps.
6. **Close the functional gaps found in §3 first** (advances/loans in payroll, holiday/shift/OT allowances, leave year-end carry-forward, PF/ESI as an inline deduction rather than a bolt-on report) — migrating data into a system that can't correctly recompute or display it is worse than not migrating.

In short: "going live" = stand up a new Postgres environment, actually write and run the ETL their own docs only describe, validate exhaustively, then cut over — with the legacy MySQL DB frozen and read-only throughout, never modified.

---

## 6. Open Risks / Gaps Worth Flagging

| Risk | Evidence |
|---|---|
| **Migration is 100% undesigned-but-undocumented-as-executed** — the 2,113-line guide is a plan, not a completed or even started implementation | No `migration/` directory, no `migration_id_map` table, no ETL scripts anywhere in the repo |
| **Schema not reproducible from source control** — the running dev DB has tables not defined in any checked-in file | Only 7 `CREATE TABLE` statements found in `New Rizo/backend/src/**` + `db/init.sql`; core tables (employees, payroll_master, attendance, tax tables) absent |
| **No schema migration framework** — tables/columns are created ad-hoc inside request handlers (`CREATE TABLE IF NOT EXISTS`, `ALTER TABLE ADD COLUMN IF NOT EXISTS` in `tax.service.js`) | `attendanceExceptions.controller.js`, `promotions.controller.js`, `salaryStructure.controller.js`, `eventReminders.controller.js`, `users.routes.js`, `tax.service.js::ensureDualColumns()` |
| **Payroll advances/loans not implemented** | No `emp_advance`/`emp_loan` handling found anywhere in `New Rizo/backend/src` |
| **Holiday/shift/OT allowances and leave encashment not found in payroll processing code** | `payroll.service.js::processEmployee()` only does base structure breakup off attendance-derived present days |
| **Leave year-end carry-forward not found** | `leave.controller.js` has no equivalent of `year_ending_fn`/`leave_end_process_fn` |
| **PF/ESI computed as a standalone report with hardcoded rates, not as part of the payroll run** | `statutory.controller.js::epfReport()` — flat 12%/8.33%/3.67%/0.5%, ₹15,000 ceiling, derived from `payroll_master.gross_salary` after the fact |
| **All reimplemented business logic (salary breakup, tax slabs, HRA exemption, PF/ESI) is unverified against legacy output** — no test fixtures or reconciliation reports found comparing New Rizo numbers to legacy procedure output | General absence of test/validation code in the areas reviewed |
| **Docs describe FIELD1-31 attendance unpivot and character-encoding conversion as known-risky** (their own §11 "Known Risks" table) — e.g. `month_year` format inconsistency across tenants, non-standard FIELD status codes per tenant, `structure_derived_perc` NULLs | `_MIGRATION_GUIDE.md` §11 — these are the senior team's own flagged risks, still unresolved since no migration has run |
| **Control-plane concept fully gone** — no equivalent of `central_control`, cross-tenant SSO, or trial-provisioning exists; if the business relies on any of those platform-admin workflows today, they'd need to be rebuilt from scratch, not migrated | `auth.controller.js` is a simple single-DB JWT login; no provisioning/SSO controller found |

---

*Prepared by reading `DOCS/_DB_PROCEDURES.md`, `DOCS/_MIGRATION_GUIDE.md`, `DOCS/_DATA_control.md`/`_DATA_tenant.md`/`_TECH_ARCHITECTURE.md`, `2_DATABASE_SCHEMA_MAPPING.md`, `New Rizo/db/init.sql`, `New Rizo/backend/src/config/db.js`, and spot-checking `payroll.service.js`, `tax.service.js`, `auth.controller.js`, `statutory.controller.js`, and controller CREATE TABLE statements against the documentation's claims. No files under `C:\Users\aksha\Downloads\Rizo (1)\Rizo` were modified.*
