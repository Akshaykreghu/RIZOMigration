# Legacy Database vs. Senior's "New Rizo" Database — Comparison Report

**Purpose:** decision-support document — what's actually different between the legacy MySQL
database (source of truth for both migrations) and the senior's PostgreSQL database, and what
that means for any plan to combine, migrate, or interoperate between the two systems.

**Sources:** direct read of `legacy/schema/mypayrol_control_db.sql` and `mypayrol_trial.sql`
(confirmed byte-identical to the copy carried inside the senior's own project folder), plus a
full read of the senior's `DOCS/_DB_PROCEDURES.md`, `DOCS/_MIGRATION_GUIDE.md`,
`DOCS/_DATA_control.md`/`_DATA_tenant.md`, `New Rizo/db/init.sql`, `New Rizo/backend/src/config/db.js`,
and spot-checked controller/service code (`payroll.service.js`, `tax.service.js`,
`auth.controller.js`, `statutory.controller.js`). No files in the senior's project were modified
to produce this report.

---

## 1. Executive Summary

| Question | Answer |
|---|---|
| Same database engine? | **No.** Legacy = MySQL 5.7. Senior's = PostgreSQL 15. |
| Same schema shape? | **No.** Tables/columns renamed, attendance model restructured, tenancy model collapsed. |
| Can they run against each other live? | **No, under any configuration.** |
| Is this a parallel build or a re-architecture? | **Full re-architecture** — not a like-for-like port. |
| Has a migration actually been built? | **No.** It's fully documented as a plan (2,113-line guide) but zero migration code, zero ID-mapping table, zero completed schema exist in the senior's repo. |
| Does legacy need to change for any path forward? | **No, in every scenario below.** It stays frozen/read-only as the source. |

The two systems started from the exact same legacy schema (verified byte-identical), but the
senior's project deliberately re-architected everything on top of it: different engine, different
tenancy model (physical DB-per-company → single DB with a `company_id` column), renamed
tables/columns, and — most importantly for a functional standpoint — **every legacy stored
procedure's business logic was reimplemented from scratch in JavaScript, not ported**, and several
payroll-critical pieces of that reimplementation are missing outright (see §4).

---

## 2. Schema-Level Differences

| Aspect | Legacy MySQL | Senior's New Rizo (PostgreSQL) |
|---|---|---|
| Engine | MySQL 5.7 | PostgreSQL 15 |
| Tenant isolation | One physical database per tenant (`mypayrol_<code>`) | One shared database (`rizo`), `company_id` column on every table |
| Control/routing layer | Separate `mypayrol_control_db` (`central_control` tenant lookup, `user_credentials`, cross-DB dynamic SQL) | Collapsed into the same single DB — plain JWT login, no cross-DB routing at all |
| Primary keys | `int(11) AUTO_INCREMENT` | `bigserial`; old ID meant to be preserved as `original_id` via a `migration_id_map` table (**not built**) |
| Booleans | `tinyint(1)` / `char(1)` `'Y'/'N'` | native `boolean` |
| `month_year` field | `varchar`, e.g. `'06-2024'` | proper `date` column (first-of-month) |
| Attendance model | `attendance_register` — one row per employee per month, `FIELD1`–`FIELD31` daily columns | One row per employee per day (genuine normalization improvement — but still only a documented plan, no unpivot script exists in code) |
| Character encoding | `latin1` / `latin1_bin` | UTF-8 |
| Naming | `emp_details`, `middile_name` (typo), `maritual_status` (typo) | Cleaned/renamed: `employees`, `middle_name`, `marital_status` |
| Schema definition | Single canonical `.sql` dump; hand-authored procedures/triggers | **No canonical schema file exists.** Only 7 tables have a checked-in `CREATE TABLE` anywhere in the repo; core tables (`employees`, `payroll_master`, `attendance`, tax tables) exist only in the live dev instance, undocumented in source control |
| Migration tooling | N/A (this *is* the source) | Documented only — no `migration/` directory, no ETL scripts, no `migration_id_map` table found anywhere in the repo |

**Practical implication:** a raw data export from legacy cannot be loaded directly into the
senior's database. Every table needs a transform pass (column rename, type conversion, attendance
unpivot, encoding conversion), and every foreign-key relationship needs ID remapping — none of
which currently exists as runnable code, only as a written plan.

---

## 3. Stored Procedure / Function / Trigger Fate

No legacy MySQL procedure, function, or trigger was ported as an equivalent Postgres object.
100% of surviving business logic was rewritten as plain JavaScript in Express controllers/services.
Where checked directly against code, some of that rewrite is genuinely solid; where only described
in docs, the logic frequently turns out to be **absent from the actual codebase**, not merely
relocated.

| Legacy object | Domain | Fate | Risk |
|---|---|---|---|
| `calculate_emp_salary_breakup` | Salary breakup | **Reimplemented** (`computeSalaryBreakup()`) — covers formula/fixed/limit operators | Present and structurally close to legacy, but unverified against legacy output |
| `calculate_salary_main_prc` (master payroll orchestrator) | Payroll | **Partially reimplemented** — attendance prorate + structure breakup only | **High risk** — holiday/shift/OT allowances, leave encashment, and **advance/loan EMI deduction are absent entirely** (no `emp_advance`/`emp_loan` handling found anywhere) |
| `calculate_holiday_allowances_prc`, `calculate_shift_allowance_prc`, `calculate_ot_allowance_prc`, `calculate_leave_encashment_prc` | Payroll add-ons | **Not found in code** | Functional gap — these amounts are simply missing from computed payroll |
| `calculate_statutory_components_prc` (PF/ESI) | Statutory | **Reimplemented as a standalone report**, not part of payroll processing — hardcoded 12%/8.33%/3.67%/0.5% rates, flat ₹15,000 ceiling | PF/ESI is a reporting artifact, not a real payroll deduction line item |
| `tax_computation_fn`, `tax_salary_distribution_fn`/`_new_fn` | Income tax (TDS) | **Reimplemented** — full old/new-regime slabs, §87A rebate, marginal relief, HRA exemption, monthly TDS spread | Most complete reimplementation found; still unverified against legacy `tax_computation_report` output |
| `leave_end_process_fn`, `year_ending_fn` (leave carry-forward) | Leave | **Not found in code** | **High risk** — annual leave carry-forward appears unimplemented |
| Attendance procs (`att_start_end_fn`, `process_att_reg_fn`, etc.) | Attendance | **Reimplemented, simplified** — basic P/L/LOP/WO-HO tally from a flat one-row-per-day table | Arguably a correct simplification, but half-day/split-punch codes not fully verified |
| Control-DB procs (`CreateDatabasesAndUsers`, `trial_signup_fn`, `single_signon_fn`, etc.) | Platform admin | **Architecturally obsolete by design** — no per-tenant provisioning concept in a single-DB model | Confirms the two systems can never share a control-plane concept |

Full per-procedure detail is in `legacy/Senior_Migration_Comparison.md` §3.

---

## 4. Functional Gaps That Matter for Planning

These are the specific reasons why "the data could theoretically move over" is not the same as
"the receiving system could correctly process it":

1. **Payroll advances/loans** — no handling found anywhere in the senior's backend.
2. **Holiday/shift/OT allowances and leave encashment** — absent from payroll processing.
3. **Leave year-end carry-forward** — no equivalent of `year_ending_fn` exists.
4. **PF/ESI** — computed post-hoc as a report with hardcoded rates, not wired into payroll as a
   real deduction.
5. **No schema migration framework** — tables are created ad-hoc via `CREATE TABLE IF NOT EXISTS`
   inside request handlers at runtime, not via a versioned migration tool.
6. **No reconciliation/testing** — none of the reimplemented business logic (salary breakup, tax,
   PF/ESI) has been verified against legacy's actual computed output for real employees.

---

## 5. Compatibility Verdict

**The senior's system cannot run against, alongside, or be fed directly from the live legacy
MySQL database, under any configuration.** Three reasons, all structural rather than
configuration-level:

1. Different engine — the app's `pg` driver and SQL dialect cannot target MySQL.
2. Different schema shape — renamed tables/columns, restructured attendance model, collapsed
   control DB, different tenancy model.
3. Different ID space — fresh `bigserial` PKs vs. legacy's `AUTO_INCREMENT` ints, with no working
   ID-remapping mechanism yet built.

This is a one-way, one-time ETL migration by design — exactly what the senior's own
`_MIGRATION_GUIDE.md` describes — never a live compatibility layer.

---

## 6. Options Going Forward

| Option | What it involves | Verdict |
|---|---|---|
| **A. Complete the ETL migration as planned** | Build the migration tooling that doesn't exist yet (scripts, `migration_id_map`), finish the target Postgres schema, close the functional gaps in §4, run a full dry run + validation + parallel run, then cut over | Architecturally the "correct" path per their own docs, but substantial unbuilt work remains — this is a project, not a task |
| **B. Rewrite senior's app to target MySQL/legacy schema instead** | Keep their Node/Express UI and business logic, swap `pg`→`mysql2`, repoint every query at real legacy table/column names and the per-tenant-DB model | Not really "compatibility" — comparable effort to redoing our own migration's schema-mapping work in reverse |
| **C. Live sync/replication between the two DBs** | Dual-write or CDC pipeline keeping both databases in sync during a transition period | Not recommended — legacy's triggers/cascades have side effects a naive sync would miss or double-apply; payroll/tax data can't tolerate silent drift |
| **D. Raw export as ETL input only** | Use `mysqldump`/CSV exports from legacy as the *source material* for building Option A's transform scripts | Legitimate and necessary first step for Option A — but the export itself is not something the senior's system can consume directly |

**In every option, the legacy database itself needs zero changes** — it stays frozen and read-only
as the source of truth throughout.

---

## 7. Recommended Next Step

Given the current state (functional gaps in payroll-critical logic, no working migration tooling,
no reconciliation testing), the lowest-risk next step is **not** to start moving data yet. Instead:

1. Decide, as a business/product decision, whether the senior's re-architecture (Option A) is the
   intended long-term direction, or whether this project's own migration (which keeps MySQL and
   the legacy stored procedures as source of truth) is the one going to production.
2. If Option A is chosen: prioritize closing the six functional gaps in §4 before any data
   migration work, since migrating data into a system that miscalculates payroll is worse than not
   migrating.
3. Either way, no action is required against the legacy database itself right now — it remains the
   stable, unmodified source for whichever direction is chosen.

---

*This report consolidates and reframes findings from `legacy/Senior_Migration_Comparison.md`
(full per-procedure detail) for planning purposes. No files in either project were modified to
produce it.*
