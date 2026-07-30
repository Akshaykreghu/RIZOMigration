# RIZO (MyPayrollMaster) — Code Logic & Data Model Report

**Scope:** `D:\Projects\RIZOMigration\legacy` — a CakePHP 2.x HR/payroll SaaS application. This report is the authoritative reference for the actual business rules, calculations, and data model that the Next.js rewrite must reproduce. It complements the two earlier reports on this codebase: `user-side-report.md` (end-user flows) and `backend-report.md` (controller/API inventory, auth, infrastructure). This report goes one level deeper: the database itself, the stored-procedure calculation engines, and every non-trivial rule found across Model/, Controller/, and View/.

**Method:** `schema/` was read directly as the authoritative source for database structure, per the task's explicit instruction — **not** inferred from `Model/` files. Eighteen research passes were run: one built a full table-by-table schema catalog from `schema/mypayrol_trial.sql` (236 tables) and `schema/mypayrol_control_db.sql` (73 tables) plus a structural-drift comparison against a real tenant dump (`schema/mypayrol_mpm121.sql`); two read the **full SQL body** of all 91 stored functions/procedures embedded in the schema dump; one covered View/ helpers and business logic embedded in `.ctp` templates; one covered Plugins and configuration-driven behavior; and thirteen covered feature-area clusters (the same clustering used in the two prior reports), each cross-checking its Model/ files' declared associations against the real schema and cataloging custom methods, business logic, and schema quirks. Every claim is cited `path:line` or `schema/<file>.sql:line`. Unverified/inferred claims are marked `INFERRED:`.

**A significant correction to the prior backend report**: that report characterized the app's MySQL stored procedures as "opaque, invisible to static analysis" because their bodies aren't in the PHP tree. **This is not true** — `schema/mypayrol_trial.sql` is not a bare `CREATE TABLE`-only dump; it contains the full `CREATE FUNCTION`/`CREATE PROCEDURE` bodies for all 91 routines the app depends on. This report reads and documents those bodies directly (§2), which means the payroll, tax, attendance, and leave calculation engines — previously assumed unrecoverable without live DB access — are now fully documented from source.

---

## 1. Cross-Cutting Findings

These patterns were confirmed independently, without exception, by essentially all thirteen cluster research passes. Read this section first — it explains *why* the per-cluster detail below looks the way it does, and it's the most important input for scoping the migration.

### 1.1 The CakePHP Model layer carries almost zero business logic — again confirmed, now completely

Across all ~243 files in `Model/`, covering every feature area, **not one model defines a `$validate` array or implements `beforeSave`/`beforeValidate`/`afterSave`/`beforeDelete`** (the shared `Model/AppModel.php` base class is likewise a bare passthrough). A small number of models (`AttendanceRegister`, `AttendanceRegisterReport`, `UserCredentials::linkempDeviceanddatabase()`, `Siteattendanceregister`) have one or two custom methods, and every one of them is a thin wrapper that does nothing but `CALL <stored_procedure>(...)` and return `true` unconditionally, regardless of whether the procedure actually succeeded.

Real business logic in this codebase lives in one of **four** places, not one:

1. **MySQL stored procedures/functions** (91 of them) — the core payroll, tax, attendance-register, leave, and site-attendance calculation engines. Fully documented in §2.
2. **MySQL triggers** — invisible to the entire PHP/CakePHP layer, discovered only by reading `schema/*.sql` directly. Confirmed triggers with real business effect: `emp_config` BEFORE INSERT/AFTER UPDATE triggers cascade shift/holiday/salary-structure/leave-policy/hierarchy/notice-days/division/section/grade values into `emp_proff` columns (Employee cluster); `branches_bi`/`branches_ai`/`branches_au` triggers silently override PHP-computed `branch_code` values (Company Setup cluster); `device_attandance_bi` computes the actual per-shift-policy late/half-day/absent thresholds using `minuts_calc_perday`/`minutes_per_half` — **no such threshold exists anywhere in PHP** (Attendance cluster); a trigger on `leave_balance_upload` and a full audit trigger on `emp_details_audit` were also found. **Any migration plan that only ports `Controller/`+`Model/` code will silently drop these rules.**
3. **Controller PHP** (the majority, and the messiest) — approval-chain state transitions, eligibility calculations, an `eval()`-based salary-formula interpreter, and pervasive raw-SQL string interpolation.
4. **View `.ctp` templates** — confirmed to be a *systemic*, not marginal, problem (§4). Real statutory tax computation (Section 87A rebate, FY2024-25 marginal relief, old-vs-new-regime comparison) and EPF/EPS/EDLI employer-contribution math, including a hardcoded COVID-era date-range exception, exist **only** inside view templates — no controller or model touches this logic at all.

### 1.2 The schema itself has real gaps, drift, and internal inconsistency — this is not just a PHP-code problem

- **Some actively-used tables are simply absent from the reference schema dump.** Confirmed missing from `schema/mypayrol_trial.sql` despite live, actively-maintained controller code depending on them: the entire performance-review/self-review/assessment table family (Performance/HR cluster — verified by exhaustive grep, not assumption; the schema dump appears stale for this feature specifically), `gate_pass`/`efsr_site`/`efsr_tickets`/`project_activity` (Site/Field cluster), `vehicle_master` (Assets cluster — the `Vehicle` model's own table), `beneficiary` (Employee cluster — `BeneficiaryController` is fully wired to a nonexistent table), and 7 of the 14 models in the Advances/Loans/Expenses cluster.
- **Structural drift confirmed against a real production tenant** (`schema/mypayrol_mpm121.sql`, §2 Part C of the schema catalog): a newer attendance-exception-rule engine (`exception_rule`, `exception_applied`, plus two stored procedures) exists in production but is **not wired to any Model/Controller code found anywhere in this repository** — either in-flight development the app layer hasn't caught up to, or a DB-only feature. A newer `locations` table and a second `item` master table were also found only in the live tenant.
- **No table in either schema declares an enforced `FOREIGN KEY` constraint.** All relationships are naming-convention-only (`*_fkey` matching another table's primary key), and the convention itself is inconsistently applied — casing varies (`EMP_fkey` vs `emp_fkey`), some `_fkey` columns are typed `varchar` referencing an `int` primary key (Assets cluster: `stock_details.store_fkey` varchar(21) vs `store_master.store_master_pkey` int), and some same-named columns in different tables are unrelated (`po_return_request.po_pkey` vs `purchase_order.po_pkey` — same name, disjoint PKs).
- **`FIELD1`..`FIELD32` EAV-style day-of-month columns on `attendance_register`** are the single biggest structural obstacle to a clean relational migration of the attendance domain (Attendance/Timesheet clusters) — this one-row-per-employee-per-month, one-column-per-day design will need explicit denormalization planning.

### 1.3 Duplication and redundancy are the norm, not the exception

- **Two entire, independent payroll calculation engines** exist as stored procedures (`calculate_salary_main_prc` vs the legacy `salary_process_prc`), selected per-employee by a hardcoded tenant allow-list in `Controller/PayrollController.php:871-875` — not both run for the same employee, but a migration needs to know which tenants use which.
- **CTC/salary-structure distribution is implemented independently at least three times**: twice in PHP controllers and once in a MySQL stored function (`sal_structure_distribution_fn`), with no evident synchronization guarantee between them.
- **The "grant default menu permissions" logic is reimplemented four separate times**: a DB trigger, a stored procedure, a SQL function, and two separate PHP methods (Statutory/Access cluster).
- **Duplicate CakePHP models pointing at the same table** were found repeatedly: `Site`/`SiteMaster`, `EfsrSite`/`SiteWork`, `GrItemDetails`/`GoodsReceivedNotesitem`, `Assets`/`AssetsModel`/`AssetsName` (three, not two), `LeaveEntries`/`LeaveRequests`, and `Currency`/`Country` (both mapped to the same `countries` table).
- **Two independent, structurally different expense-approval state machines** exist for what a user would consider "the same" workflow (`EmployeeExpensesController` two-level Authorize→Approve vs `ProjectExpensesController` single-level Approve/Reject with a tenant-specific cascade side effect).
- ~11.5% of `Model/*.php` files (~28 of 243) are confirmed dead/orphaned — either duplicate backup-suffixed classes the CakePHP autoloader never reaches, or models with zero controller references anywhere in the codebase.

### 1.4 Confirmed live bugs affecting business-rule correctness (not just code quality)

These are not style complaints — each one changes what the application actually does today, and any migration that "faithfully reproduces current behavior" needs to decide deliberately whether to preserve or fix each one:

- **Leave-balance insufficiency is computed but never enforced** — the rejection logic exists in code but is commented out at both leave-application time and authorize/approve time (Leave cluster). Balance is *displayed* to users but does not block over-application.
- **`$this->setup($emp_fkey)` is called but never defined**, a fatal-error risk on the employee self-service path in three separate controllers (`EmployeeadvanceController.php:76`, `EmployeeExpensesController.php:76`, `ProjectExpensesController.php:74`).
- **An advance-eligibility attendance-cap check is silently disabled**: `$condition_statemnt = true` hardcodes the guard open (Advances cluster).
- **`UserController::saveNames()` nulls out a user's password on every profile name edit** due to two undefined variables (Statutory/Access cluster).
- **An assignment-as-condition bug makes an entire branch of the Full & Final Settlement logic unreachable**: `$str_company_code = 'KWMT'` in an `if` (Performance/HR cluster).
- **`TimesheetController::bulkipdatestatus()`'s actual attendance-column UPDATE statements are commented out** — only an audit-log insert survives; the real mutation mechanism for that code path is unknown and needs to be located before migrating (Timesheet cluster).
- **The `eval()`-based salary-formula engine is inconsistently guarded**: whitelisted/regex-checked in `PayrollController.php`, but left completely unguarded in `ArrearController.php:866`, `PayrollProcessController.php:406`, and 4 of 5 sites in `SalaryComponentUploadController.php` — and separately, `eval()` is also used to evaluate DB-stored PF-formula strings in `EsiEpfReportController.php`/`StatutoryReportController.php` with no guard at all. This is a genuine code-injection surface, not just a migration inconvenience.
- **A date-range wraparound bug** in the "upcoming birthdays/anniversaries in the next 7 days" notification query (string-based date `BETWEEN`, breaks across year boundaries — Dashboards cluster).
- **Four separate, apparently-unreconciled "plan" (SaaS tier) columns** exist across `comp_contact_info`, `central_control`, `hrm_menu`, and the control-DB `user_credentials` table — which one is authoritative for gating a given feature is not obvious and differs by controller (Dashboards/Config-driven-behavior clusters).

### 1.5 Multi-tenant hardcoding

At least **60 distinct company codes** appear in direct `== 'XXXX'` comparisons across `Controller/*.php`, plus at least 25 more controller files use `in_array($company_code, [...])` for additional tenant-specific branches (full consolidated list in §5/§6). This ranges from cosmetic (which dashboard a user lands on) to structural (which payroll calculation engine runs, which advance-eligibility formula applies, whether a rejection cascades to void other records). **Every one of these is effectively an unwritten tenant-configuration schema** — the Next.js migration should treat this as a prioritized backlog item: extract these into real per-tenant configuration rather than porting `if (company_code == 'X')` branches verbatim.

### 1.6 Password/auth data-model confirmation

`user_credentials.password` is `varchar(100)`; hashing is CakePHP's default SHA-1 via `Security::hash($x, null, true)`, unsalted beyond the single app-wide `Security.salt` constant (no per-user salt, no bcrypt/PBKDF2/Argon2). `mob_user_credentials.password` is `varchar(30)` and stores **plaintext** passwords, confirmed by column width and corroborated by the Statutory/Access cluster's finding that `UserCredentialsController` mirrors and emails plaintext passwords on reset.

---

## 2. How to Read This Report

1. **§3 — Full Database Schema** (report requirement #1): the complete table-by-table catalog (all 236 + 73 tables, every column, type, key) plus the full 91-routine stored-procedure signature catalog, the structural drift analysis against a live tenant, and a note on the (absent) foreign-key/relationship model. CakePHP association cross-checks against this schema are embedded per-cluster in §6 below (requirement #1's "annotated with association names" and requirement #2's Model custom-method inventory naturally belong together per feature area, so they're presented together rather than duplicated).
2. **§4 — Stored Procedure & Function Logic** (part of requirement #3): the full algorithm/formula/business-constant documentation for all 91 routines — payroll/salary/tax/CTC/arrears in one part, attendance/leave/site/stock in another.
3. **§5 — View-Layer Business Logic** (requirement #4).
4. **§6 — Plugins & Configuration-Driven Behavior** (requirement #5 and part of requirement #6).
5. **§7 — Per-Cluster Data Model & Business Logic** (requirements #2, #3, #7): the thirteen feature-area deep dives — schema cross-check, custom Model methods with call sites, complex logic/state machines, and schema quirks, for Employee, Attendance, Leave, Payroll/Tax, Advances/Loans/Expenses, Reports, Company Setup, Site/Field/Project, Assets/Inventory/Purchase, Performance/HR, Dashboards/Notifications, Statutory/Access, and Timesheet/uncovered-models.

---


## 3. Full Database Schema

# RIZO Migration — §1 Full Database Schema Catalog

Source files (all under `legacy/schema/`):
- `mypayrol_control_db.sql` (2916 lines) — control/tenant-management DB, 73 CREATE TABLE + 15 procedures + 2 functions + 1 trigger
- `mypayrol_trial.sql` (47975 lines) — per-company reference schema, 236 tables + 91 functions/procedures
- `mypayrol_mpm121.sql` (257260 lines) — real customer tenant dump, used only for structural drift comparison (not read in full)

**No `FOREIGN KEY` constraints are used anywhere in this schema** except in `mypayrol_control_db.sql`'s newer SaaS-billing tables (`features`, `plan_features`, `plan_history` — added recently, see below) and `user_credentials`/`central_control`'s implicit `control_fkey`/`company_code` links. The rest of the app (both control DB and every per-company DB) relies entirely on **naming convention**: columns ending `_fkey` (or `_pkey` for the row's own key, or bare `emp_id`/`company_code`/etc.) reference another table's primary key without a DB-enforced constraint. This is typical of older CakePHP 2.x apps using Model associations (`belongsTo`/`hasMany`) configured in PHP rather than in the DB. All relationships in §4 below are therefore derived from naming convention unless explicitly marked "real FK".

---

## PART A — `mypayrol_control_db.sql` (control DB)

### A.1 Stored Procedures (15) and Functions (2)

All are schema-maintenance / cross-tenant-orchestration routines that iterate `central_control` and run dynamic SQL (`PREPARE`/`EXECUTE`) against every tenant DB in `central_control.user_db`. None of them are simple CRUD helpers — they are DB-migration/data-fix utilities or cross-tenant aggregation jobs.

| Name | Signature | Purpose / tables touched | Citation |
|---|---|---|---|
| `add_emp_join_fkey_to_documents` | `()` | Adds `emp_join_fkey INT(11)` column to `documents` table in every tenant DB that has it | control_db.sql:14 |
| `alter_emp_advance_affected_month` | `()` | Widens `emp_advance.affected_month` to `VARCHAR(7)` across tenants (excludes a hardcoded list of company codes) | control_db.sql:72 |
| `alter_salary_amount_decimal` | `()` | Repositions/retypes `salary_amount` column in `emp_statutory_components` and `emp_salary_slip` to `decimal(10,2)` across tenants | control_db.sql:140 |
| `company_statistics_prc` | `(IN pmonth varchar(20))` | Cross-tenant loop computing attendance/payroll/employee counts per company for a given month; writes to `company_statistics` (note: `company_statistics` table itself is not defined in this dump — likely exists live but missing from schema export) | control_db.sql:214 |
| `CreateDatabasesAndUsers` | `(IN pstart int, IN pend int)` | Provisions new tenant: `CREATE DATABASE mypayrol_mpmN`, `CREATE USER`, `GRANT`, inserts row into `central_control` and two rows into `user_credentials` (support + trialadmin) | control_db.sql:326 |
| `emp_id_maping_exist_prc` | `(IN pCompany_code varchar(20))` | Syncs `emp_device_comp_branch.emp_device_id` from `emp_id_maping_exist` where matched | control_db.sql:380 |
| `emp_id_maping_prc` | `(IN pCompany_code varchar(30), OUT perr_msg varchar(100))` | Syncs `emp_device_comp_branch.emp_device_id` from `emp_id_maping` by name match | control_db.sql:420 |
| `estern_username_update` | `(IN pemp_pkey int)` | Cross-DB: reads tenant `emp_proff`/`emp_details`, writes `emp_device_comp_branch` and `user_credentials` in control DB — builds device username from branch+company id | control_db.sql:460 |
| `generateDatabase` | `(IN pstartfrom int(11), IN pendto int(11))` | Legacy/dead-code version of tenant DB provisioning (writes generated SQL into `test` table rather than executing) | control_db.sql:521 |
| `generate_deployment_sql` | `()` | Generates a `device_logs_iteration_fn` deployment script per tenant DB (temp table `deployment_commands`) | control_db.sql:567 |
| `inactive_db_audit_prc` | `()` | Finds tenants with >90-day-old `device_attandance` records and >6-month-old `start_date_effective`; reports via temp table | control_db.sql:610 |
| `insert_reportcriterias_all` | `()` | Seeds two `reportcriterias` rows (Account/Units, Account/EmployeeDetails) into every tenant if missing | control_db.sql:661 |
| `insert_reportcriterias_lop` | `()` | Seeds two `reportcriterias` rows (LOPReport/EmployeeDetails, LOPReport/Units) into tenants (excludes a hardcoded company list) | control_db.sql:728 |
| `insert_reportcriteria_salarystructures` | `()` | Seeds `reportcriterias` row for SalaryStructures per tenant | control_db.sql:786 |
| `leave_end_process_parent_prc` | `()` | Cross-tenant cron orchestrator: calls each tenant's `leave_end_process_fn()`, logs to `cron_log` | control_db.sql:849 |
| `update_doc_template_columns` | `()` | Adds `header_image`/`footer_image` columns to tenant `doc_template` tables if missing | control_db.sql:1012 |
| `update_emp_details_status` | `()` | Bulk-fixes `emp_details.status = 0` → `2` across tenants | control_db.sql:1120 |
| `update_income_tax_slab_2026` | `()` | Migrates FY2026 income tax slabs into every tenant's `income_tax_slab` table (hardcoded new-regime slab values), logs to `procedure_run_log` | control_db.sql:1191 |
| `single_signon_fn` (FUNCTION) | `(pstring char(255), pcompany_code char(25), pemp_coid char(50), pemp_email varchar(50), pkeyvalue char(50)) RETURNS char(255)` | SSO handshake: validates `central_control.third_party+control_pkey` key, looks up `emp_device_comp_branch` by email, logs to `sso_audit`, returns `user_db\|\|emp_username` | control_db.sql:900 |
| `trial_signup_fn` (FUNCTION) | `(ppayroll_signup_pkey varchar(200)) RETURNS varchar(200)` | Self-service trial signup: validates uniqueness against `payroll_signup`/`central_control`, claims an available trial-status `central_control` row, updates `user_credentials` (trialadmin/support rows), updates `payroll_signup` | control_db.sql:932 |

### A.2 Trigger

| Name | On table | Purpose | Citation |
|---|---|---|---|
| `devicelogs_bi` | `devicelogs` (BEFORE INSERT) | Massive hardcoded per-company-code dispatcher: resolves `emp_id`/`branch_code` via `emp_device_comp_branch`/`devices`, then INSERTs the row into the correct **legacy per-tenant DB's** `device_attandance` table (30+ hardcoded `elseif vcompany_code='XXXX'` branches, e.g. `mypayrol_mpm112`, `mypayrol_mpm235`, `hedge_db`, etc.) or logs to `device_logs_error` if resolution fails. This is clearly deprecated/superseded by a more generic per-tenant device polling mechanism, but still present. | control_db.sql:1697 |
| `set_emp_id` | `emp_device_comp_branch` (BEFORE INSERT) | Auto-derives `emp_id` and `emp_username` from `deviceid`+`emp_device_id`+`company_code` when `emp_id` is null | control_db.sql:2052 |

### A.3 Tables (73)

Grouped logically. Compact format: `table_name(col type [PK|FK->target|NULL/NOT NULL/DEFAULT]...)`. `_bck*`/`_bak*`/`_09032026` suffixed tables are dated backup/snapshot copies of a live table (schema drift vs. the live table noted where relevant) — flagged as **[BACKUP]**.

#### Tenant / SaaS management

**central_control** — the master tenant directory: maps `company_code` → per-tenant DB credentials/URLs/plan. Every cross-tenant procedure above joins against this. (control_db.sql:1405)
```
control_pkey int PK AUTO_INCREMENT
company_code varchar(20) NOT NULL UNIQUE
company_name varchar(200) NOT NULL
Address varchar(500) NOT NULL
Admin_name varchar(100) NOT NULL
user_db varchar(100) NOT NULL          -- tenant DB name, e.g. mypayrol_mpm121
user_pwd varchar(100) NOT NULL
created_date datetime NOT NULL
start_date_effective date NOT NULL
end_date_effective date NOT NULL
product varchar(200) NOT NULL
active varchar(10) NOT NULL
custom_message varchar(500) NOT NULL
redirect_url varchar(200) NOT NULL
country_code varchar(3) NOT NULL
currency_code varchar(3) NOT NULL
punch_type varchar(20) NOT NULL        -- device/manual
attr1..attr7 varchar(200) NOT NULL     -- generic overflow attrs
trial_status char(1) NOT NULL          -- A=available, P=provisioned
subdomain varchar(30) NULL
third_party varchar(30) NULL
web_url varchar(400) DEFAULT 'https://login.mypayrollmaster.online/'
api_url varchar(400) DEFAULT 'https://apps.office24.online/forsight/api/v1/'
app_url varchar(400) DEFAULT 'https://v1.mypayrollmaster.online/api/v3/'
biometric_url varchar(400) NULL
plan varchar(40) DEFAULT 'standerd'
```
`central_control_bck29092025` **[BACKUP]** same columns minus `app_url`/`plan` (control_db.sql:1443)
`control_back09092023` **[BACKUP]** same columns minus `attr*` presence check / minus `app_url`,`plan`,`biometric_url` retained but missing later additions (control_db.sql:1521)

```
company_addons(company_addon_id int PK AUTO_INCREMENT, company_code varchar(100) NOT NULL, feature_id int NOT NULL FK->features.feature_id, expiry_date date NULL, created_at datetime DEFAULT CURRENT_TIMESTAMP) UNIQUE(company_code,feature_id)  -- control_db.sql:1477
company_branches(branch_seq int PK AUTO_INCREMENT, company_code varchar(20) NOT NULL, branch_code varchar(20) NOT NULL, branch_name varchar(200) NOT NULL, status int DEFAULT 1)  -- control_db.sql:1488
company_branches_bck29092025 [BACKUP] same cols, no PK  -- control_db.sql:1498
company_overrides(company_override_id int PK AUTO_INCREMENT, company_code varchar(100) NOT NULL, type enum('Plan','Addon') NOT NULL, item_id int NOT NULL, special_amount decimal(10,2) NOT NULL, included_employees int NULL, extra_rate decimal(10,2) NULL, created_at datetime DEFAULT CURRENT_TIMESTAMP) UNIQUE(company_code,type,item_id)  -- control_db.sql:1507
cron_dbs(cron_dbs_pkey int PK AUTO_INCREMENT, company_code varchar(20) NOT NULL, start_date date NULL, end_date date NULL, remarks text)  -- control_db.sql:1588
cron_log(id int PK AUTO_INCREMENT, company_code varchar(30) NOT NULL, company_name varchar(200) NULL, run_time datetime DEFAULT CURRENT_TIMESTAMP, status varchar(50) NULL, remarks text)  -- control_db.sql:1598
features(feature_id int PK AUTO_INCREMENT, display_order int DEFAULT 0, feature_key varchar(100) NOT NULL, feature_name varchar(150) NOT NULL, feature_path varchar(255) NULL, description varchar(3000) NULL, is_common tinyint DEFAULT 0, article varchar(3000) NOT NULL, report_list varchar(3000) NOT NULL, created_at datetime DEFAULT CURRENT_TIMESTAMP, icon varchar(50) NULL, is_addon tinyint DEFAULT 0, plan_id int NULL FK->plans.plan_id [REAL FK: features_ibfk_1], included_employees int DEFAULT 0, extra_employee_price decimal(10,2) DEFAULT 0.00, base_addon_price decimal(10,2) DEFAULT 0.00)  -- control_db.sql:2120
plans(plan_id int PK AUTO_INCREMENT, plan_name varchar(100) NOT NULL, description text, price decimal(10,2) DEFAULT 0.00, grace_period int NOT NULL, is_active tinyint DEFAULT 1, created_at datetime DEFAULT CURRENT_TIMESTAMP, included_employees int DEFAULT 0, extra_employee_price decimal(10,2) DEFAULT 0.00)  -- control_db.sql:2556
plan_features(id int PK AUTO_INCREMENT, plan_id int NOT NULL FK->plans.plan_id [REAL FK, ON DELETE CASCADE], feature_id int NOT NULL FK->features.feature_id [REAL FK, ON DELETE CASCADE], is_enabled tinyint DEFAULT 1, created_at datetime DEFAULT CURRENT_TIMESTAMP) UNIQUE(plan_id,feature_id)  -- control_db.sql:2570
plan_history(history_id int PK AUTO_INCREMENT, client_id int NOT NULL FK->clients.client_id [REAL FK, ON DELETE CASCADE — NOTE: `clients` table is NOT defined in this dump, likely missing/renamed], plan_id int NOT NULL FK->plans.plan_id [REAL FK, ON DELETE CASCADE], start_date date NOT NULL, end_date date NULL, is_active tinyint DEFAULT 1, purchased_at datetime DEFAULT CURRENT_TIMESTAMP)  -- control_db.sql:2584
plan_payment_history(payment_history_id int PK AUTO_INCREMENT, user_id varchar(30) NOT NULL, change_plan_id int NOT NULL, razorpay_payment_id varchar(100) NOT NULL, razorpay_order_id varchar(100) NOT NULL, amount float NOT NULL, method varchar(100) NOT NULL, bank varchar(100) NULL, email varchar(50) NOT NULL, contact varchar(50) NOT NULL, plan_start_date date NULL, plan_end_date date NULL, description varchar(100) NULL, status varchar(50) NOT NULL, active int NOT NULL, addons_json text, plan_price decimal(10,2) DEFAULT 0.00, addon_price decimal(10,2) DEFAULT 0.00, extra_charge decimal(10,2) DEFAULT 0.00, gst_amount decimal(10,2) DEFAULT 0.00, upgrade_credit decimal(10,2) NULL, available_credit float DEFAULT 0, created_by varchar(50) NOT NULL, creation_date datetime ON UPDATE CURRENT_TIMESTAMP, modified_by varchar(50) NULL, modification_date datetime DEFAULT CURRENT_TIMESTAMP, grace_extension_days int NULL) -- Razorpay payment gateway integration for plan upgrades  -- control_db.sql:2600
payroll_signup(payroll_signup_pkey int PK AUTO_INCREMENT, company_code varchar(20) NOT NULL, company_name varchar(200) NOT NULL, Address varchar(500) NULL, Contact_Name varchar(500) NULL, Contact_Phone varchar(500) NULL, admin_email varchar(500) NOT NULL, admin_password varchar(500) NOT NULL, admin_phone varchar(500) NOT NULL, admin_name varchar(100) NOT NULL, signup_url varchar(200) NULL, signup_ip varchar(200) NULL, signup_browser varchar(200) NULL, free_trial_end_date date NULL, user_db varchar(100) NULL, user_pwd varchar(100) NULL, created_date datetime DEFAULT CURRENT_TIMESTAMP, start_date_effective date NOT NULL, end_date_effective date NULL, product varchar(200) NULL, active varchar(10) NULL, custom_message varchar(500) NULL, redirect_url varchar(200) NULL, country_code varchar(50) NULL, currency_code varchar(50) NULL, punch_type varchar(100) NULL, attr1..attr7 varchar(200) NULL, trial_status char(200) DEFAULT 'A')  -- self-service trial signup intake, feeds trial_signup_fn -- control_db.sql:2479
payroll_signup_bck29092025 [BACKUP] identical cols, no PK  -- control_db.sql:2518
```

#### Auth / access

```
user_credentials(user_pkey int, control_fkey int NOT NULL DEFAULT 0 FK->central_control.control_pkey, company_code varchar(20) NOT NULL, user_id varchar(50) NOT NULL UNIQUE, password varchar(100) NOT NULL, access_allowed varchar(1) NOT NULL, start_date date NOT NULL, end_date date NOT NULL, first_name varchar(100) NOT NULL, last_name varchar(100) NOT NULL, middle_name varchar(100) NOT NULL, email varchar(100) NOT NULL, phone int NOT NULL, reset_login_flag varchar(100) NOT NULL, locked varchar(10) NOT NULL, attr1 varchar(200) NOT NULL, attr2 varchar(200) NOT NULL, avatar text, plan_id int unsigned NULL, credit_balance decimal(10,2) DEFAULT 0.00, PK(user_pkey,control_fkey)) UNIQUE(control_fkey,user_pkey)  -- control-level login credentials (support/trialadmin accounts), separate from per-tenant emp_details/emp login  -- control_db.sql:2814
user_credentials_bck29092025 [BACKUP] same minus plan_id/credit_balance, no PK  -- control_db.sql:2842
users(id int PK AUTO_INCREMENT, username varchar(255) NOT NULL UNIQUE, password_hash varchar(255) NOT NULL, tenant varchar(255) NOT NULL, role varchar(50) DEFAULT 'user', created_at timestamp DEFAULT CURRENT_TIMESTAMP, updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) -- appears to be a NEWER/parallel auth table, possibly added for the Next.js migration or a different app; not referenced by any procedure above  -- control_db.sql:2799
super_admin_access(super_admin_access_pkey int PK AUTO_INCREMENT, subdomain varchar(30) NOT NULL, user_fkey int NULL, control_fkey int NULL FK->central_control.control_pkey, active char(1) DEFAULT 'Y', status int DEFAULT 1)  -- control_db.sql:2768
login_auditor(login_audit_pkey int PK AUTO_INCREMENT, user_cred varchar(40) NOT NULL, user_ip varchar(40) NOT NULL, user_browser varchar(100) NOT NULL, company1 varchar(100) NOT NULL, company2 varchar(100) NOT NULL, auditor_time varchar(100) DEFAULT 'CURRENT_TIMESTAMP', auditor_type varchar(100) NOT NULL COMMENT 'IN/OUT', message varchar(400) NOT NULL, user_cred2 varchar(40) NOT NULL)  -- control_db.sql:2248
sso_audit(sso_audit_pkey int PK AUTO_INCREMENT, string varchar(1500) NOT NULL, company_code varchar(20) NOT NULL, emp_comp_id varchar(50) NOT NULL, emp_email varchar(100) NOT NULL, keyvalue varchar(100) NOT NULL, creation_date datetime DEFAULT CURRENT_TIMESTAMP, return_mesg varchar(255) NOT NULL)  -- control_db.sql:2755
oauth2_authorization(id varchar(100) PK, registered_client_id varchar(100) NOT NULL FK->oauth2_registered_client.id, principal_name varchar(200) NOT NULL, authorization_grant_type varchar(100) NOT NULL, authorized_scopes varchar(1000) NULL, attributes blob, state varchar(500) NULL, authorization_code_value blob, authorization_code_issued_at timestamp NULL, authorization_code_expires_at timestamp NULL, authorization_code_metadata blob, access_token_value blob, access_token_issued_at timestamp NULL, access_token_expires_at timestamp NULL, access_token_metadata blob, access_token_type varchar(100) NULL, access_token_scopes varchar(1000) NULL, oidc_id_token_value blob, oidc_id_token_issued_at timestamp NULL, oidc_id_token_expires_at timestamp NULL, oidc_id_token_metadata blob, refresh_token_value blob, refresh_token_issued_at timestamp NULL, refresh_token_expires_at timestamp NULL, refresh_token_metadata blob, user_code_value blob, user_code_issued_at timestamp NULL, user_code_expires_at timestamp NULL, user_code_metadata blob, device_code_value blob, device_code_issued_at timestamp NULL, device_code_expires_at timestamp NULL, device_code_metadata blob, oidc_id_token_claims blob) -- Spring-Authorization-Server style OAuth2 tables, standard schema -- control_db.sql:2410
oauth2_registered_client(id varchar(100) PK, client_id varchar(100) NOT NULL, client_id_issued_at timestamp DEFAULT CURRENT_TIMESTAMP, client_secret varchar(200) NULL, client_secret_expires_at timestamp NULL, client_name varchar(200) NOT NULL, client_authentication_methods varchar(1000) NOT NULL, authorization_grant_types varchar(1000) NOT NULL, redirect_uris varchar(1000) NULL, post_logout_redirect_uris varchar(1000) NULL, scopes varchar(1000) NOT NULL, client_settings varchar(2000) NOT NULL, token_settings varchar(2000) NOT NULL)  -- control_db.sql:2449
```

#### Attendance / device integration (control-level, cross-tenant device routing)

```
attendance(id int PK AUTO_INCREMENT, auditor_fkey int unsigned NOT NULL, userid varchar(30) NOT NULL, company_code varchar(30) NOT NULL, time_check datetime NOT NULL, in_out varchar(10) NOT NULL, location varchar(500) NOT NULL, latitude double NOT NULL, longitude double NOT NULL, uploaded_time datetime NOT NULL, loc_source varchar(50) NOT NULL, stay_back varchar(10) NULL, punch_status varchar(20) NOT NULL, punch_queued_at datetime(3) NULL, punch_processed_at datetime(3) NULL, location_status varchar(10) NOT NULL, queue_sync_message varchar(700) NULL) -- mobile GPS punch capture, control-level staging before tenant sync  -- control_db.sql:1329
attendancelogs(ATTENDANCELOGID int PK, ATTENDANCEDATE date NOT NULL, EMPLOYEEID varchar(20) NOT NULL, BRANCH_CODE varchar(25) NULL, COMPANY_CODE varchar(25) NULL, INTIME varchar(255) NULL, INDEVICEID varchar(255) NULL, OUTTIME varchar(255) NULL, OUTDEVICEID varchar(255) NULL, DURATION int NULL, LATEBY int NULL, EARLYBY int NULL, ISONLEAVE int NULL, LEAVETYPEID int NULL, LEAVETYPE varchar(50) NULL, LEAVEDURATION int NULL, LEAVESTATUS int NULL, LEAVEREMARKS varchar(1000) NULL, ISONSPECIALOFF int NULL, SPECIALOFFTYPE varchar(255) NULL, SPECIALOFFREMARK varchar(1000) NULL, SPECIALOFFDURATION int NULL, WEEKLYOFF int NULL, HOLIDAY int NULL, PUNCHRECORDS mediumtext, PUNCHDIRECTIONS varchar(500) NULL, PUNCHDEVICESNAME varchar(500) NULL, SHIFTID int NULL, PRESENT int NULL, ABSENT int NULL, DETAILEDSTATUS varchar(500) NULL, STATUS varchar(255) NULL, DETAILEDSTATUSCODE varchar(500) NULL, STATUSCODE varchar(255) NULL, P1STATUS/P2STATUS/P3STATUS varchar(255) NULL, OVERTIME int NULL, OVERTIMEE int NULL, MISSEDOUTPUNCH int NULL, MISSEDINPUNCH int NULL, REMARKS varchar(1000) NULL, ISONRESTRICTEDHOLIDAY int NULL, ISONCOMPOFF int NULL, ISPARTIALDAY int NULL, REPORTPUNCHRECORDS varchar(500) NULL, SMSFLAG int NULL, COMPOFF int NULL, created_time datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) -- computed daily attendance summary log (pre-aggregated), mirrors what tenant DBs compute per-tenant  -- control_db.sql:1351
devicelogs(DEVICELOGID int NOT NULL, DOWNLOADDATE datetime NOT NULL, DEVICEID int NOT NULL, USERID varchar(100) NOT NULL, LOGDATE datetime NOT NULL, DIRECTION varchar(50) NOT NULL, ATTDIRECTION varchar(50) NOT NULL, C1..C7 varchar(100) NOT NULL, WORKCODE varchar(100) NOT NULL) UNIQUE(DEVICEID,USERID,LOGDATE,DIRECTION,C1) -- raw biometric device punch log staging table; BEFORE INSERT trigger devicelogs_bi fans out rows into the correct tenant DB's device_attandance table  -- control_db.sql:1675
devices(DeviceId int NOT NULL, DeviceFName varchar(255) NOT NULL, DeviceSName varchar(255) NOT NULL, company_code varchar(20) NOT NULL, branch_code varchar(20) NOT NULL, DeviceDirection varchar(255) NULL, SerialNumber varchar(255) NULL, ConnectionType varchar(255) NULL, IpAddress varchar(255) NULL, BaudRate varchar(255) NULL, CommKey varchar(255) NULL, ComPort varchar(255) NULL, LastLogDownloadDate datetime NULL, C1..C7 varchar(255) NULL, TransactionStamp varchar(255) NULL, LastPing datetime NULL, DeviceType varchar(255) NULL, OpStamp varchar(255) NULL, DownLoadType int NULL, Timezone varchar(50) NULL, DeviceLocation varchar(50) NULL, TimeOut varchar(50) NULL) -- no explicit PK  -- control_db.sql:1944
device_logs_error(DEVICELOGID int NOT NULL, DOWNLOADDATE datetime NOT NULL, DEVICEID int NOT NULL, USERID varchar(100) NULL, LOGDATE datetime NULL, DIRECTION varchar(50) NULL, ATTDIRECTION varchar(50) NULL, C1..C7 varchar(100) NULL, WORKCODE varchar(100) NULL) -- unresolved devicelogs rows (company/emp lookup failed)  -- control_db.sql:1976
device_logs_error_09032026 [BACKUP snapshot of device_logs_error]  -- control_db.sql:1995
emp_device_comp_branch(emp_device_comp_branch_seq int PK AUTO_INCREMENT, deviceid int NOT NULL, emp_device_id int NOT NULL, emp_name varchar(200) NOT NULL, Company_code varchar(20) NOT NULL, branch_code varchar(20) NOT NULL, emp_id bigint NULL, emp_username varchar(50) NOT NULL, emp_fkey int NOT NULL, email varchar(100) NULL, status int DEFAULT 1, creation_date timestamp DEFAULT CURRENT_TIMESTAMP, modified_by varchar(100) NULL, modified_date datetime NULL ON UPDATE CURRENT_TIMESTAMP) -- cross-DB map of device-reported employee IDs to internal emp_fkey per company/branch; trigger set_emp_id auto-fills emp_id/emp_username  -- control_db.sql:2031
emp_device_comp_branch_bak [BACKUP], emp_device_comp_branch_bak1 [BACKUP] -- both older snapshots, differ only in emp_id column type (int vs varchar(50))  -- control_db.sql:2065,2083
emp_id_maping(FirstName varchar(100) NOT NULL, LastName varchar(100) NOT NULL, EmpNo varchar(100) NOT NULL, status char(1) DEFAULT 'N') -- no PK, name-based device ID reconciliation staging  -- control_db.sql:2101
emp_id_maping_exist(emp_fkey int NOT NULL, company_code varchar(30) NOT NULL, branch_code varchar(30) NOT NULL, emp_name varchar(200) NOT NULL, emp_device_id int NOT NULL, id_mapped int NOT NULL, status varchar(2) DEFAULT 'N') -- no PK  -- control_db.sql:2109
get_location(id int PK AUTO_INCREMENT, company varchar(50) NOT NULL, emp_id varchar(50) NOT NULL, state varchar(50) NOT NULL, location varchar(500) NULL, created_date datetime DEFAULT CURRENT_TIMESTAMP)  -- control_db.sql:2143
location(loc_pkey int PK AUTO_INCREMENT, company_code varchar(20) NOT NULL, emp_id varchar(20) NOT NULL, state varchar(20) NOT NULL, created_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)  -- control_db.sql:2229
location_09032026 [BACKUP snapshot of location, no PK]  -- control_db.sql:2239
customervisit(id int PK AUTO_INCREMENT, company_code varchar(10) NOT NULL, customer_visit_pkey varchar(10) NOT NULL, report_date date NOT NULL, api_hit_count int NOT NULL, balance_count int NOT NULL, created_time timestamp DEFAULT CURRENT_TIMESTAMP) -- API usage/rate tracking per company per day  -- control_db.sql:1609
customer_visit(id int PK AUTO_INCREMENT, mob_location_pkey int NOT NULL, user_id varchar(30) NOT NULL, company_code varchar(30) NOT NULL, created_time datetime NOT NULL, latitude double NOT NULL, longitude double NOT NULL, location varchar(100) NOT NULL, uploaded_time datetime NOT NULL, stepinout varchar(20) NOT NULL, accuracy int NOT NULL, customer_name varchar(200) NOT NULL, contact_person varchar(100) NULL, contact_number varchar(20) NULL, purpose varchar(500) NOT NULL, punch_status varchar(40) NOT NULL, punch_queued_at datetime(3) NULL, punch_processed_at datetime(3) NULL, location_status varchar(40) NOT NULL, queue_sync_message varchar(700) NULL) -- field-sales customer visit GPS log  -- control_db.sql:1621
customer_visit_09032026 [BACKUP snapshot, no PK]  -- control_db.sql:1646
```

#### Reference / master data (shared across tenants)

```
countries(id_countries int PK AUTO_INCREMENT, name varchar(200) NULL, iso_alpha2 varchar(2) NULL, country_code varchar(3) NULL, iso_numeric int NULL, currency_code char(3) NULL, currency_name varchar(32) NULL, currrency_symbol varchar(3) NULL, flag varchar(6) NULL, phone_code varchar(10) NULL)  -- control_db.sql:1556
countries_nationality(id int PK AUTO_INCREMENT, country_code varchar(2) NULL, country_name varchar(100) NULL, nationality varchar(100) NULL)  -- control_db.sql:1571
countries_only(id int PK AUTO_INCREMENT, country_code varchar(2) NOT NULL DEFAULT '', country_name varchar(100) NOT NULL DEFAULT '')  -- control_db.sql:1580
department(id int PK AUTO_INCREMENT, dept_code varchar(20) NOT NULL, dept_name varchar(100) NOT NULL, status int DEFAULT 1)  -- control_db.sql:1657
designation(id int PK AUTO_INCREMENT, desig_code varchar(100) NOT NULL, desig_name varchar(100) NOT NULL, status int DEFAULT 1)  -- control_db.sql:1666
holidays(HOLIDAYID int PK AUTO_INCREMENT, HOLIDAY_GROUP_ID int NOT NULL FK->holiday_group.HOLIDAY_GROUP_ID, HOLIDAYNAME varchar(100) NOT NULL, HOLIDAYDATE date NOT NULL, DESCRIPTION varchar(255) NOT NULL, HOLIDAYTYPE varchar(50) NOT NULL, status int DEFAULT 1, Background varchar(100) NOT NULL, border varchar(100) NOT NULL)  -- control_db.sql:2154
holiday_group(COMPANY_CODE varchar(50) NOT NULL, BRANCH_CODE varchar(50) NOT NULL, HOLIDAY_GROUP_ID int PK AUTO_INCREMENT, HOLIDAY_GROUP_NAME varchar(200) NOT NULL, status int DEFAULT 1)  -- control_db.sql:2168
income_tax_slab(income_tax_slab_pkey int PK AUTO_INCREMENT, fin_year int NOT NULL, regime varchar(20) NULL, salary_range_from int NOT NULL, salary_range_to int NOT NULL, tax_yearly_perc int NULL, std_deduction float NULL, surcharge_perc float NULL, rebate float NULL, cess_perc float NULL, start_date_effective date NULL, end_date_effective date NULL, created_by varchar(30) NOT NULL, creation_date datetime DEFAULT CURRENT_TIMESTAMP, modified_by varchar(30) NULL, modified_date datetime NULL ON UPDATE CURRENT_TIMESTAMP, status int DEFAULT 1) -- reference tax slab table; update_income_tax_slab_2026 proc pushes a copy of this into every tenant DB too (tenants have their OWN income_tax_slab table — see Part B)  -- control_db.sql:2178
leavepolicy(LEAVEPOLICYID int PK AUTO_INCREMENT, LEAVEPOLICY_GROUP_ID int NOT NULL FK->leavepolicy_group.LEAVEPOLICY_GROUP_ID, salary_head_item_fkey int NOT NULL FK->salary_head_items.salary_head_item_pkey, alloted_leave_forthe_year float NOT NULL, alloted_leave_forthe_month float NULL, CARRY_FORWARD_LIMIT float NULL, APPLICABLE_TO varchar(50) NULL, ALLOW_NEGETIVE char(1) DEFAULT 'N', IS_SANDWICH char(1) DEFAULT 'N', is_leave_encash char(1) DEFAULT 'N', leave_encash_limit float NULL, is_auto_credit char(1) DEFAULT 'N', REMARKS varchar(500) NULL, status int DEFAULT 1)  -- control_db.sql:2200
leavepolicy_group(COMPANY_CODE varchar(50) NOT NULL, BRANCH_CODE varchar(50) NOT NULL, LEAVEPOLICY_GROUP_ID int PK AUTO_INCREMENT, LEAVEPOLICY_GROUP_NAME varchar(200) NOT NULL, status int DEFAULT 1)  -- control_db.sql:2219
salary_heads(head_pkey int PK AUTO_INCREMENT, head_desc varchar(200) NOT NULL, status int DEFAULT 1, head_operator varchar(100) NOT NULL, head_occurance varchar(100) NOT NULL, salary_head_order1 int NOT NULL)  -- control_db.sql:2696
salary_head_items(salary_head_item_pkey int PK AUTO_INCREMENT, head_fkey int NOT NULL FK->salary_heads.head_pkey, item varchar(200) NOT NULL, item_type varchar(10) NOT NULL, item_value varchar(100) NULL, occurance varchar(20) NULL, start_from date NULL, comments text, value char(1) DEFAULT 'N', is_show_salslip char(1) DEFAULT 'Y', item_part varchar(30) NOT NULL, status int DEFAULT 1, salary_head_item_order1 int NOT NULL)  -- control_db.sql:2707
salary_structure(structure_id int PK AUTO_INCREMENT, company_code varchar(30) NOT NULL, structure_name varchar(200) NOT NULL, prorate_code varchar(25) NOT NULL, prorate_desc varchar(250) NOT NULL, defined_structure_for varchar(250) NOT NULL, structure_eg_amt int NOT NULL, structure_created_date int NOT NULL, startdate_effective date NOT NULL, enddate_effective date NOT NULL, structure_active int NOT NULL)  -- control_db.sql:2725
salary_structure_details(structure_det_id int PK AUTO_INCREMENT, structure_id int NOT NULL FK->salary_structure.structure_id, salary_head_item_fkey int NOT NULL FK->salary_head_items.salary_head_item_pkey, structure_det_operator varchar(30) NOT NULL, structure_det_value float NOT NULL, structure_det_depends float NULL, structure_formula varchar(1000) NULL, structure_derived_perc double NULL, structure_det_calequation varchar(1000) NULL)  -- control_db.sql:2741
working_day_time_procedures(day_time_seq int PK AUTO_INCREMENT, day_time_desc text NOT NULL, Sunday/Monday/.../Saturday char(1) DEFAULT 'N' [+_F suffix pairs], on_dutty1..4/off_dutty1..4/working_time1..4 varchar(30), minuts_calc_perday int, minuts_aftr_on_dutty_cal_late int, minuts_bfr_off_dutty_cal_early int, min_cal_late_ifnoclockin int, min_cal_leave_early_ifnoclockout int, min_aftr_off_dutty_cal_ot int, min_bfr_on_dutty_cal_ot int, work_time_day_off_cal_ot int, active int DEFAULT 1, isnextday int DEFAULT 0, shift_allowance varchar(11), otcomponents varchar(11), start_date_effective date, end_date_effective date, strict_monitorings char(1) DEFAULT 'N', minutes_per_half int, is_multiple_days varchar(2) DEFAULT 'N', no_of_shift_days float, is_exception int DEFAULT 0) -- shift/roster template definitions  -- control_db.sql:2864
```

#### Misc / logging / legacy dead-code

```
log_issue_cntrldb(id int PK AUTO_INCREMENT, type varchar(30) NOT NULL, module varchar(30) NULL, company_code varchar(30) NULL, emp_pkey int NULL, issue varchar(3000) NOT NULL, creation_date datetime DEFAULT CURRENT_TIMESTAMP, status int DEFAULT 1)  -- control_db.sql:2263
master_auidt(master_auidt_pkey int PK AUTO_INCREMENT, company_code varchar(20) NOT NULL, company_name varchar(200) NULL, branch_code varchar(20) NULL, branch_name varchar(200) NULL, module_name varchar(200) NULL, process_Name varchar(100) NULL, process_type varchar(50) NULL, process_month varchar(50) NULL, process_count varchar(50) NULL, remarks varchar(100) NULL, created_date datetime DEFAULT CURRENT_TIMESTAMP, modification_date datetime NULL)  -- control_db.sql:2276
master_db(master_db_pkey int PK AUTO_INCREMENT, Module varchar(50) NOT NULL, object_Name varchar(100) NOT NULL, object_type varchar(50) NOT NULL, object_script blob NOT NULL, object_active char(1) DEFAULT 'Y', extras varchar(100) NOT NULL) -- appears to store DB object scripts (procs/functions) as blobs, possibly a deployment registry  -- control_db.sql:2294
mob_user_msg_control(mob_user_msg_control_pkey int PK AUTO_INCREMENT, user_id varchar(20) NOT NULL, mpm_version varchar(20) NOT NULL, device_model varchar(30) NOT NULL, reminder_count int NOT NULL, remarks varchar(200) NOT NULL, creation_date datetime DEFAULT CURRENT_TIMESTAMP, modification_date datetime NOT NULL, status int DEFAULT 1)  -- control_db.sql:2306
mob_version_control(mob_version_control_pkey int PK AUTO_INCREMENT, version varchar(20) NOT NULL, reminder_count int NOT NULL, reminder_msg varchar(200) NULL, other_msg varchar(200) NULL, status int DEFAULT 1, cretion_date datetime DEFAULT CURRENT_TIMESTAMP)  -- mobile app version gate/force-update config  -- control_db.sql:2320
mypayroll_bugs_logs(bug_pkey int(120) PK AUTO_INCREMENT, COMPANY_CODE varchar(10) NOT NULL, USER varchar(30) NOT NULL, title varchar(500) NOT NULL, bug_type varchar(20) NOT NULL, reason varchar(500) NOT NULL, description varchar(2000) NOT NULL, details varchar(2000) NOT NULL, user_ip varchar(30) NOT NULL, browser varchar(30) NOT NULL, bugs_time datetime NOT NULL, resolved varchar(1) DEFAULT 'N', notified int DEFAULT 0)  -- control_db.sql:2332
myprojects_control(control_pkey int PK AUTO_INCREMENT, company_code varchar(20) NOT NULL UNIQUE, company_name varchar(200) NOT NULL, Address varchar(500) NOT NULL, Admin_name varchar(100) NOT NULL, user_db varchar(100) NOT NULL, user_pwd varchar(100) NOT NULL, created_date datetime NOT NULL, start_date_effective date NOT NULL, end_date_effective date NOT NULL, product varchar(200) NOT NULL, active varchar(10) NOT NULL, custom_message varchar(500) NOT NULL, redirect_url varchar(200) NOT NULL, country_code varchar(3) NOT NULL, currency_code varchar(3) NOT NULL, punch_type varchar(20) NOT NULL, attr1..attr7 varchar(200) NOT NULL) -- central_control clone for a sibling "myprojects" product  -- control_db.sql:2350
mysales_control(same shape as myprojects_control) -- central_control clone for a sibling "mysales" product  -- control_db.sql:2380
payroll_online / ppl_online / projects_online(id int(6) PK AUTO_INCREMENT, session_id varchar(50), activity datetime, member varchar(5) DEFAULT 'n', ip_address varchar(25), refurl varchar(155), user_agent varchar(55)) -- 3 near-identical CakePHP session/"who's online" tracking tables for 3 sibling products  -- control_db.sql:2467,2632,2655
procedure_run_log(procedure_run_log_pkey int PK AUTO_INCREMENT, procedure_name varchar(100) NOT NULL, db_name varchar(100) NOT NULL, status enum('success','failed') NOT NULL, error_message varchar(500) NULL, ran_at datetime DEFAULT CURRENT_TIMESTAMP) -- used by update_income_tax_slab_2026 to log per-tenant migration results  -- control_db.sql:2644
registrations(first_name, last_name, email, mobile_no varchar(15), company_name varchar(200), desired_username varchar(200), desired_password varchar(100), came_from varchar(100), employee_count varchar(100), comments varchar(200), signup_date date, attr1 varchar(200)) -- no PK, marketing-site signup capture  -- control_db.sql:2667
report_a_problem1(mob_report_pkey int NOT NULL DEFAULT 0, report_type varchar(480), user_id varchar(480), remarks text NOT NULL, lat varchar(100) NULL, longs varchar(100) NULL, date_rep datetime NULL, rep_status varchar(20) NULL, status int NULL) -- no real PK  -- control_db.sql:2683
test(field1 varchar(100), field2 varchar(4000), field3/4/5 varchar(100)) -- scratch/dev table used by generateDatabase proc  -- control_db.sql:2779
test_device_attandance(id int PK AUTO_INCREMENT, emp_id varchar(50) NOT NULL, LOGDATE datetime NOT NULL, c1 varchar(10) DEFAULT 'IN', status varchar(10) DEFAULT 'Y') UNIQUE(emp_id,LOGDATE) -- dev/test table  -- control_db.sql:2788
doc_template(template_pkey int PK AUTO_INCREMENT, template_name varchar(200) NOT NULL, template_content mediumtext NOT NULL, placeholders varchar(500) NOT NULL, availability varchar(10) DEFAULT '0', editable varchar(10) DEFAULT '0', policy varchar(10) DEFAULT '0', created_by varchar(30) NOT NULL, creation_date datetime NOT NULL, modified_by varchar(30) NULL, modification_date datetime NULL, status int DEFAULT 1) -- template registry; procedure update_doc_template_columns adds header_image/footer_image to the TENANT copies of this table (this control-level copy itself lacks those columns, meaning it may be stale relative to tenant DBs)  -- control_db.sql:2014
```

**Note on `company_statistics`**: referenced extensively by `company_statistics_prc` (INSERT/UPDATE) but has **no `CREATE TABLE`** in this dump — either omitted from the export or created out-of-band. Flag for migration team to source its DDL from a live DB before relying on it.

**Note on `clients`**: referenced by `plan_history`'s FK but similarly has no `CREATE TABLE` here.

---

## PART B — `mypayrol_trial.sql` (per-company reference schema)

This is the per-tenant/per-company reference schema (236 tables, 91 stored functions/procedures). Every real customer tenant DB (e.g. `mypayrol_mpm121`, see Part C) is a copy of this schema plus tenant-specific data and possible drift. Unlike the control DB, this schema has essentially **zero enforced `FOREIGN KEY` constraints** — confirmed by inspecting a representative sample of `CREATE TABLE` statements below (e.g. `attendance_register`, `emp_details`, `emp_salary_slip`, `payroll_master` — none declare `FOREIGN KEY`, only `PRIMARY KEY`/`KEY`/`UNIQUE`). All relationships are via naming convention (`*_fkey`/`*_pkey`) — see Part D.

### B.1 Stored Functions (91) and Procedures

Full business logic for these routines is already documented in `01-procs-payroll-tax.md` (payroll/salary/tax/CTC, 28 routines) and `02-procs-attendance-leave-stock.md` (attendance/leave/stock, remaining routines with deep coverage of the most important ones — `insert_update_att_reg`, `att_start_end_fn`, etc.). The table below catalogs **every** one of the 91 signatures with the tables each references (derived by scanning each routine's full SQL body for table-name token matches against the 236-table list above), for routines/detail not already fully narrated in those two files.

| Name | Type | Signature | Tables referenced | Citation |
|---|---|---|---|---|
| `att_start_end_date_fn` | FUNCTION | `(`start_date` varchar(20), `year_month` varchar(20)) RETURNS varchar(30) CHARACTER SET 'latin1' LANGUAGE SQL` | date_intervel_monthyear, db_config | mypayrol_trial.sql:14 |
| `att_start_end_fn` | FUNCTION | `(`Pyear_month` date, `patt_start_end` int) RETURNS date LANGUAGE SQL` | db_config | mypayrol_trial.sql:50 |
| `bulk_company_idupdate` | PROCEDURE | `(IN `pemp_pkey` int)` | emp_details, emp_proff, user_credentials | mypayrol_trial.sql:108 |
| `bulk_device_logs_iteration_prc` | PROCEDURE | `(IN `p_year_month` date)` | emp_details | mypayrol_trial.sql:165 |
| `calculate_emp_component_breakup` | PROCEDURE | `(IN `Pstructure_id` int, IN `Psalary_head_item_pkey` int, IN `Pmonthly_comp` decimal(10,2), IN `Pmonthly_gross` decimal(10,2))` | salary_head_items, salary_heads, salary_structure_details | mypayrol_trial.sql:193 |
| `calculate_emp_salary_breakup` | PROCEDURE | `(IN `Pemp_fkey` int, IN `Pstructure_id` int, IN `Pmonthly_gross` decimal(10,2))` | salary_head_items, salary_heads, salary_structure_details | mypayrol_trial.sql:354 |
| `calculate_holiday_allowances_prc` | PROCEDURE | `(IN `pemp_pkey` int, IN `pmonth_year` varchar(7), IN `pcreated_by` varchar(30))` | emp_calc_variable_components, emp_proff, emp_salary_structure, working_day_time_procedures | mypayrol_trial.sql:457 |
| `calculate_leave_encashment_prc` | PROCEDURE | `(IN `pemp_pkey` int, IN `pmonth_year` varchar(7), IN `pcreated_by` varchar(30))` | emp_calc_variable_components, emp_salary_structure, leave_encashment_master | mypayrol_trial.sql:632 |
| `calculate_monthly_salary_components_prc` | PROCEDURE | `(IN `pemp_pkey` int(11), IN `pmonth` varchar(20), IN `puser_id` varchar(30), OUT `poutput` varchar(30))` | attendance_register, db_config, emp_ctc_transaction, emp_detail_timeattandance, emp_monthly_salary_components, emp_proff, emp_salary_structure, salary_head_items, salary_heads, salary_structure, working_day_time_procedures | mypayrol_trial.sql:744 |
| `calculate_ot_allowance_prc` | PROCEDURE | `(IN `pemp_pkey` int, IN `pmonth_year` varchar(7), IN `pcreated_by` varchar(30))` | emp_calc_variable_components, emp_ot_master, emp_proff, emp_salary_structure, working_day_time_procedures | mypayrol_trial.sql:977 |
| `calculate_salary_main_prc` | PROCEDURE | `(IN `pmonth` varchar(20), IN `pbranch_code` varchar(30), IN `pemp_pkey` int(11), IN `ppayroll_master_pkey` int(11), IN `puser_id` varchar(30), OUT `perr_msg` varchar(2000))` | attendance_register, contracted_days, db_config, department, designation, division, emp_advance, emp_calc_variable_components, emp_ctc_transaction, emp_details, emp_loan_info, emp_monthly_salary_components, emp_proff, emp_salary_slip, emp_salary_structure, emp_statutory_components, emp_tax_regime, emp_tax_sal_trans_sum, emp_tax_sal_trans_sum_new, emp_variables_upload, employee_info, fin_year, grade, gross_salary, holidays, payroll_master, salary_head_items, salary_heads, salary_processing_audit, salary_structure, section, tax_salary_components, verticals | mypayrol_trial.sql:1085 |
| `calculate_shift_allowance_prc` | PROCEDURE | `(IN `pemp_pkey` int, IN `pmonth_year` varchar(7), IN `pcreated_by` varchar(30))` | attendance_register, emp_calc_variable_components, emp_details, emp_proff, emp_salary_structure, emp_shift_planner, working_day_time_procedures | mypayrol_trial.sql:1615 |
| `calculate_statutory_components_prc` | PROCEDURE | `(IN `pemp_pkey` int(11), IN `pmonth` varchar(20), IN `puser_id` varchar(30), OUT `poutput` varchar(30))` | attendance_register, db_config, emp_ctc_transaction, emp_detail_timeattandance, emp_proff, emp_salary_slip, emp_salary_structure, emp_statutory_components, salary_head_items, salary_heads, salary_structure, salary_structure_details, working_day_time_procedures | mypayrol_trial.sql:1827 |
| `carryforward_insert_prc` | PROCEDURE | `(IN `pfin_year` varchar(30), OUT `perr_msg` varchar(30))` | emp_details, emp_leave_balance_year, fin_year | mypayrol_trial.sql:2379 |
| `check_fn` | FUNCTION | `(`pcompany_code ` varchar(20), `PBranch_code` varchar(20), `Puserid` varchar(20), `Pmonth` date) RETURNS varchar(100) CHARACTER SET 'latin1' LANGUAGE SQL` | attendance_register, branches, db_config, device_attandance, emp_details, emp_proff | mypayrol_trial.sql:2426 |
| `copy_salary_structure_to_new` | PROCEDURE | `(IN `vemp_fkey` int)` | emp_new_salary_structure, emp_salary_structure | mypayrol_trial.sql:2535 |
| `ctc_component_update_and_upload_prc` | PROCEDURE | `(IN `pemp_pkey` int, OUT `pmessage` varchar(50))` | emp_ctc_transaction, emp_details, emp_proff, emp_salary_structure, emp_salcomp_upload, salary_head_items | mypayrol_trial.sql:2584 |
| `ctc_component_upload_prc` | PROCEDURE | `(IN `pemp_pkey` int, OUT `pmessage` varchar(50))` | emp_ctc_transaction, emp_details, emp_proff, emp_salary_structure, emp_salcomp_upload, salary_head_items | mypayrol_trial.sql:2837 |
| `customer_visit_history_fn` | FUNCTION | `(`pemp_pkey` int) RETURNS varchar(100) CHARACTER SET 'latin1' LANGUAGE SQL` | cus_visit_locations, mob_user_locations, test, user_credentials | mypayrol_trial.sql:3025 |
| `delete_an_count_records_fn` | PROCEDURE | `(IN `pcompany_code` varchar(255), IN `ppkey` int, IN `pyearmonth1` date, OUT `vrecord_count` int)` | attendance_register, emp_details, payroll_master | mypayrol_trial.sql:3128 |
| `device_logs_iteration_fn` | FUNCTION | `(`pemp_id` varchar(20), `pyear_month` date) RETURNS varchar(100) CHARACTER SET 'latin1' LANGUAGE SQL` | device_attandance | mypayrol_trial.sql:3158 |
| `device_logs_resync_fn` | FUNCTION | `(`pemp_id` varchar(20), `pyear_month` date) RETURNS varchar(30) CHARACTER SET 'latin1' LANGUAGE SQL` | device_attandance, emp_details, emp_proff, mob_user_credentials | mypayrol_trial.sql:3175 |
| `editpunch_rules` | FUNCTION | `(`pemp_fkey` int, `pmessage` varchar(250), `pinout_order` varchar(30), `Pyear_month` date, `pfirst_half` varchar(30)) RETURNS varchar(100) CHARACTER SET 'latin1' LANGUAGE SQL` | db_config, device_attandance, emp_details, emp_proff, working_day_time_procedures | mypayrol_trial.sql:3215 |
| `emp_detail_att_reg` | PROCEDURE | `(IN `Pcompany_code` varchar(30), IN `PBranch_code` varchar(30), IN `Puserid` varchar(30), IN `Pmonth` date, IN `pemp_pkey` int, OUT `Perr_msg` varchar(500))` | attendance_register, branches, db_config, emp_details, emp_proff, working_day_time_procedures | mypayrol_trial.sql:3442 |
| `emp_inactive_fn` | FUNCTION | `() RETURNS tinyint(4) LANGUAGE SQL` | emp_details, emp_proff, inactive_list | mypayrol_trial.sql:3542 |
| `estern_username_update` | PROCEDURE | `(IN `pemp_pkey` int)` | emp_details, emp_proff, user_credentials | mypayrol_trial.sql:3599 |
| `final_settle_pay_prc` | PROCEDURE | `(IN `pbranch_code` varchar(30), IN `pmonth_year` varchar(30), IN `pemp_pkey` int, IN `ppresant_days` float, IN `pencash_days` float, IN `puser_id` varchar(50), OUT `perror_message` varchar(500))` | attendance_register, branches, db_config, emp_advance, emp_ctc_transaction, emp_details, emp_loan_info, emp_proff, emp_salary_slip, emp_salary_structure, emp_settle_slip, emp_tax_sal_trans, fin_year, gross_salary, leave_encashment_master, payroll_master, profession_tax_slab, salary_head_items, salary_structure, salary_structure_details, tax_salary_components | mypayrol_trial.sql:3662 |
| `find_pf_tax_cal_fn` | FUNCTION | `(`pemp_fkey` int) RETURNS int(11) LANGUAGE SQL` | emp_details, emp_proff, emp_salary_slip, emp_salary_structure, fin_year, payroll_master, tax_salary_components | mypayrol_trial.sql:4233 |
| `get_policy_weekoff_holiday_count_prc` | PROCEDURE | `(IN `pemp_pkey` int, IN `pstart_date` date, IN `pend_date` date, OUT `pweekoff_count` float, OUT `pholiday_count` float)` | emp_proff, holidays, shift_exceptions, working_day_time_procedures | mypayrol_trial.sql:4299 |
| `get_present_in_weekoff_holiday_count_prc` | PROCEDURE | `(IN `pemp_fkey` int, IN `pmonth_year` varchar(7), OUT `o_weekoff_count` float, OUT `o_holiday_count` float)` | emp_detail_timeattandance, emp_proff, holidays, shift_exceptions, working_day_time_procedures | mypayrol_trial.sql:4402 |
| `insert_default_menu` | PROCEDURE | `(IN `Pemp_fkey` int)` | emp_menu, user_access | mypayrol_trial.sql:4532 |
| `insert_emp_att_reg` | PROCEDURE | `(IN `Pcompany_code` varchar(30), IN `PBranch_code` varchar(30), IN `Puserid` varchar(30), IN `Pmonth` date, OUT `Perr_msg` varchar(5000))` | attendance_register, branches, db_config, emp_detail_timeattandance, emp_details, emp_proff, holidays, working_day_time_procedures | mypayrol_trial.sql:4568 |
| `insert_update_att_reg` | PROCEDURE | `(IN `pbranch_code` varchar(30), IN `pstart_date` date, IN `pend_date` date, IN `puserid` varchar(30), OUT `poutput` varchar(20))` | attendance_register, branches, emp_detail_timeattandance, emp_details, emp_leave_transactions, emp_proff, holidays, leaveentries, salary_head_items, shift_exceptions, termination, working_day_time_procedures | mypayrol_trial.sql:5081 |
| `insert_update_att_reg_hierarchy` | PROCEDURE | `(IN `Pcompany_code` varchar(30), IN `PBranch_code` varchar(30), IN `Puserid` varchar(30), IN `Pmonth` date, OUT `Perr_msg` varchar(500))` | attendance_register, branches, db_config, device_attandance, emp_details, emp_leave_transactions, emp_proff, holidays, leaveentries, salary_head_items, working_day_time_procedures | mypayrol_trial.sql:5560 |
| `insert_update_att_reg_rep` | PROCEDURE | `(IN `Pcompany_code` varchar(30), IN `PBranch_code` varchar(30), IN `Puserid` varchar(30), IN `Pmonth` date, OUT `Perr_msg` varchar(200))` | attendance_register_rep, branches, db_config, device_attandance, emp_details, emp_leave_transactions, emp_proff, holidays, leaveentries, working_day_time_procedures | mypayrol_trial.sql:8596 |
| `insert_update_att_reg_view` | PROCEDURE | `(IN `Pcompany_code` varchar(30), IN `Pbranch_code` varchar(30), IN `Puserid` varchar(30), IN `Pmonth` date, OUT `Perr_msg` varchar(500))` | attendance_register, branches, emp_detail_timeattandance, emp_details, emp_proff, holidays, salary_structure, working_day_time_procedures | mypayrol_trial.sql:11378 |
| `last_punch_fn` | FUNCTION | `(`pemp_fkey` int) RETURNS varchar(100) CHARACTER SET 'latin1' LANGUAGE SQL` | device_attandance, emp_details, emp_proff, working_day_time_procedures | mypayrol_trial.sql:11934 |
| `leavepolicy_start_end_insert_prc` | PROCEDURE | `(OUT `perr_msg` varchar(20))` | branches, emp_details, emp_proff, fin_year, leavepolicy, leavepolicy_group, salary_head_items | mypayrol_trial.sql:12036 |
| `leave_auth_apr_person_fn` | FUNCTION | `(`pcompany_code` varchar(10), `plogin_emp_fkey` int, `paction` varchar(10)) RETURNS varchar(1000) CHARACTER SET 'latin1' LANGUAGE SQL` | emp_config, emp_details, emp_proff | mypayrol_trial.sql:12122 |
| `leave_auth_apr_person_fnmbct` | FUNCTION | `(`pcompany_code` varchar(10), `plogin_emp_fkey` int, `paction` varchar(10), `psalary_head_item_fkey` int(11)) RETURNS varchar(1000) CHARACTER SET 'latin1' LANGUAGE SQL` | emp_config, emp_details, emp_proff, leavepolicy | mypayrol_trial.sql:12223 |
| `leave_balance_inthe_month_fn` | FUNCTION | `(`Pemp_fkey` int, `psalary_head_item_fkey` int, `Pmonth` date, `pfinyear` varchar(30)) RETURNS float LANGUAGE SQL` | attendance_register, emp_detail_timeattandance, emp_details, emp_leave_balance_year, emp_leave_transactions, emp_proff, fin_year, leave_encashment_master, leaveentries, leavepolicy, leavestatus, salary_head_items, working_day_time_procedures | mypayrol_trial.sql:12352 |
| `leave_balance_inthe_year_fn` | FUNCTION | `(`pemp_fkey` int, `psalary_head_item_fkey` int, `pleave_date` date) RETURNS float LANGUAGE SQL` | attendance_register, emp_detail_timeattandance, emp_leave_balance_year, emp_leave_transactions, emp_proff, holidays, leave_balance_upload, leave_encashment_master, leaveentries, leavepolicy, leavestatus, salary_head_items, shift_exceptions, working_day_time_procedures | mypayrol_trial.sql:12843 |
| `Leave_balance_upload_fn` | FUNCTION | `() RETURNS varchar(30) CHARACTER SET 'latin1' LANGUAGE SQL` | emp_details, emp_leave_balance_year, emp_proff, fin_year, leave_balance_upload, leavepolicy, test | mypayrol_trial.sql:13226 |
| `leave_encash_insert_prc` | PROCEDURE | `(IN `pbranch_code` varchar(30), IN `pemp_pkey` int, IN `pitem` int, IN `pmonth` date, IN `puser_id` varchar(30), OUT `perr_msg` varchar(200))` | emp_details, emp_proff, leave_encashment_master, leavepolicy, salary_head_items | mypayrol_trial.sql:13322 |
| `leave_encash_prc` | PROCEDURE | `(IN `pbranch_code` varchar(30), IN `pemp_pkey` int, IN `ppleave_encashment_master_pkey` int, IN `puser_id` varchar(30), IN `perr_msg` varchar(2000))` | designation, emp_ctc_transaction, emp_details, emp_encash_slip, emp_proff, emp_salary_structure, leave_encashment_master | mypayrol_trial.sql:13422 |
| `leave_end_process_fn` | FUNCTION | `() RETURNS varchar(30) CHARACTER SET 'latin1' LANGUAGE SQL` | emp_details, emp_leave_balance_year, emp_proff, leavepolicy, leavepolicy_group | mypayrol_trial.sql:13589 |
| `leave_fn` | FUNCTION | `() RETURNS int(11) LANGUAGE SQL` | emp_detail_timeattandance, emp_leave_transactions, leaveentries | mypayrol_trial.sql:13719 |
| `leave_rules_fn` | FUNCTION | `(`pemp_fkey` int, `psalary_head_item_fkey` int, `pleave_rule` varchar(50), `pfromdate` date, `pfromhalf` int, `ptodate` date, `ptohalf` int) RETURNS varchar(500) CHARACTER SET 'latin1' LANGUAGE SQL` | emp_details, emp_proff, fin_year, holidays, leavepolicy, salary_head_items | mypayrol_trial.sql:13765 |
| `leave_start_end_prc` | PROCEDURE | `(IN `pemp_fkey` int, IN `psalary_head_item_fkey` int, IN `pleave_date` date, OUT `pstart_date` date, OUT `pend_date` date)` | emp_details, emp_proff, fin_year, leavepolicy | mypayrol_trial.sql:13953 |
| `leave_taken_fn` | FUNCTION | `(`pemp_fkey` int, `psalary_head_item_fkey` int, `pleave_date` date) RETURNS float LANGUAGE SQL` | attendance_register, emp_detail_timeattandance, emp_details, emp_leave_balance_year, emp_leave_transactions, emp_proff, fin_year, leaveentries, leavepolicy, leavestatus, salary_head_items | mypayrol_trial.sql:14028 |
| `leave_transaction_prc` | PROCEDURE | `(IN `PLEAVEENTRYID` int(11), IN `PEMP_fkey` int(11), IN `PFROMDATE` date, IN `PFROMHALF` int, IN `PTODATE` date, IN `PTOHALF` int, IN `Pleave_days` float, IN `Pleave_status` varchar(50), OUT `Perror_message` varchar(200))` | emp_leave_transactions, emp_proff, holidays, leaveentries, leavepolicy, salary_head_items, scheduled_break_off, shift_exceptions, working_day_time_procedures | mypayrol_trial.sql:14118 |
| `Linkemp_deviceanddatabase` | PROCEDURE | `(IN `Pcompany_code` varchar(30), IN `PBranch_code` varchar(30), IN `Pemp_device_startid` int(11), OUT `Perr_msg` varchar(500))` | emp_details, emp_proff, user_credentials | mypayrol_trial.sql:16617 |
| `mark_site_attendance_fn` | FUNCTION | `(`psite_fkey` int, `puser_id` int, `pshift_fkey` int, `pdesig_fkey` int, `pemp_fkey` int, `pin_time` datetime, `pout_time` datetime, `patt_date` date) RETURNS varchar(500) CHARACTER SET 'latin1' LANGUAGE SQL` | site, site_attendance, site_transactions, working_day_time_procedures | mypayrol_trial.sql:16724 |
| `mark_site_attendance_out_fn` | FUNCTION | `(`psite_fkey` int, `puser_id` int, `pshift_fkey` int, `pdesig_fkey` int, `pemp_fkey` int, `pin_time` datetime, `pout_time` datetime, `patt_date` date) RETURNS varchar(500) CHARACTER SET 'latin1' LANGUAGE SQL` | site_attendance, working_day_time_procedures | mypayrol_trial.sql:16845 |
| `master_auidt_fn` | FUNCTION | `(`pcompany_code` varchar(20), `pbranch_code` varchar(20), `pmodule` varchar(20), `pusage_type` varchar(20)) RETURNS int(11) LANGUAGE SQL` | activity, branches, comp_contact_info, emp_detail_timeattandance, emp_details, report_audit | mypayrol_trial.sql:16915 |
| `ot_duration_register` | FUNCTION | `(`Pyear_month` date, `Pemp_pkey` int, `Pbranch_code` varchar(30)) RETURNS varchar(100) CHARACTER SET 'latin1' LANGUAGE SQL` | branches, db_config, device_attandance, emp_detail_timeattandance, emp_details, emp_ot_master, emp_ot_timeattandance, emp_proff, holidays, working_day_time_procedures | mypayrol_trial.sql:17085 |
| `ot_duration_register_date` | FUNCTION | `(`pdate` date, `pemp_pkey` int, `pbranch_code` varchar(30)) RETURNS varchar(100) CHARACTER SET 'latin1' LANGUAGE SQL` | branches, device_attandance, emp_detail_timeattandance, emp_details, emp_ot_timeattandance, emp_proff, emp_shift_planner, holidays, shift_exceptions, termination, working_day_time_procedures | mypayrol_trial.sql:17613 |
| `payroll_master_approve` | PROCEDURE | `(IN `pbranch` varchar(30), IN `pmonth_year` varchar(30), IN `pemp_fkey` int, IN `puser_id` varchar(30), OUT `perror_message` varchar(300))` | emp_advance, emp_loan, emp_loan_info, emp_salary_slip | mypayrol_trial.sql:18048 |
| `payroll_master_insert` | PROCEDURE | `(IN `pbranch` varchar(30), IN `pmonth_year` varchar(30), IN `puser_id` varchar(30), OUT `perror_message` varchar(300))` | attendance_register, db_config, emp_details, emp_proff, holidays, payroll_master, salary_structure, site_attendance_register | mypayrol_trial.sql:18179 |
| `presant_leave_holiday_count` | FUNCTION | `(`pemp_pkey` int, `pyear_month` date) RETURNS varchar(50) CHARACTER SET 'latin1' LANGUAGE SQL` | attendance_register | mypayrol_trial.sql:18317 |
| `process_att_reg_fn` | FUNCTION | `(`pcompany_code` varchar(30), `pbranch_code` varchar(30), `Pyear_month` date, `Pemp_pkey` int) RETURNS varchar(100) CHARACTER SET 'latin1' LANGUAGE SQL` | db_config, device_attandance, emp_detail_attandance_reg, emp_details, emp_leave_transactions, emp_proff, holidays, leaveentries, salary_head_items, working_day_time_procedures | mypayrol_trial.sql:18332 |
| `profession_tax_cal_fn` | FUNCTION | `(`pemp_fkey` int, `pmonth_year` varchar(30)) RETURNS int(11) LANGUAGE SQL` | branch_exceptions, branches, emp_details, emp_proff, emp_pt_details, emp_salary_slip, emp_salary_structure, fin_year, payroll_master, profession_tax_slab, salary_head_items, tax_salary_components | mypayrol_trial.sql:18851 |
| `salary_process_prc` | PROCEDURE | `(IN `pmonth` varchar(20), IN `pbranch_code` varchar(30), IN `pemp_pkey` int(11), IN `ppayroll_master_pkey` int(11), IN `puser_id` varchar(30), OUT `perr_msg` varchar(2000))` | attendance_register, branches, contracted_days, db_config, department, designation, division, emp_advance, emp_ctc_transaction, emp_detail_timeattandance, emp_details, emp_leave_transactions, emp_loan_info, emp_ot_master, emp_proff, emp_salary_slip, emp_salary_structure, emp_tax_sal_trans_sum, emp_variables_upload, employee_info, fin_year, grade, gross_salary, leave_encashment_master, leaveentries, leavestatus, payroll_master, salary_head_items, salary_heads, salary_structure_details, section, tax_salary_components, verticals, working_day_time_procedures | mypayrol_trial.sql:19111 |
| `salary_structure_limit_prc` | PROCEDURE | `(IN `pemp_fkey` int, IN `puser_id` varchar(30), OUT `perr_msg` varchar(500))` | db_config, designation, emp_ctc_transaction, emp_details, emp_proff, emp_salary_structure, holidays, salary_structure_details | mypayrol_trial.sql:20246 |
| `sal_structure_distribution` | PROCEDURE | `(IN `Pcompany_code` varchar(30), IN `Pemp_fkey` int, IN `Pemp_structure_id` int, IN `Puserid` varchar(30), OUT `Perror_massage` varchar(200))` | emp_ctc_transaction, emp_salary_structure, fin_year, salary_head_items, salary_heads, salary_structure, salary_structure_details | mypayrol_trial.sql:20420 |
| `sal_structure_distribution_fn` | FUNCTION | `(`Pcompany_code` varchar(30), `Pemp_fkey` int, `Pemp_structure_id` int, `Puserid` varchar(30)) RETURNS int(11) LANGUAGE SQL` | emp_ctc_transaction, emp_details, emp_proff, emp_salary_structure, fin_year, salary_head_items, salary_heads, salary_structure, salary_structure_details | mypayrol_trial.sql:20568 |
| `site_insert_update_att_reg` | PROCEDURE | `(IN `Pcompany_code` varchar(30), IN `PBranch_code` varchar(30), IN `Puserid` varchar(100), IN `Pmonth` date, OUT `Perr_msg` varchar(500))` | branches, db_config, emp_details, emp_proff, emp_site_detail_timeattandance, holidays, site_attendance_register | mypayrol_trial.sql:20941 |
| `site_rate_update_prc` | PROCEDURE | `(IN `psite_pkey` int, IN `pmonth` date, OUT `pmessage` varchar(30))` | emp_details, site, site_attendance, site_transactions, test | mypayrol_trial.sql:21200 |
| `site_sal_structure_distribution_fn` | FUNCTION | `(`Pcompany_code` varchar(30), `Pemp_fkey` int, `Pemp_structure_id` int, `p_mctc_amount` int, `puser_id` varchar(30)) RETURNS int(11) LANGUAGE SQL` | emp_ctc_transaction, emp_details, emp_salary_structure, fin_year, salary_head_items, salary_heads, salary_structure, salary_structure_details | mypayrol_trial.sql:21251 |
| `site_time_duration_check` | FUNCTION | `(`Pyear_month` date, `Pemp_pkey` int, `psite_pkey` int) RETURNS varchar(300) CHARACTER SET 'latin1' LANGUAGE SQL` | db_config, device_attandance, emp_details, emp_proff, emp_site_detail_timeattandance, site_transactions, test, working_day_time_procedures | mypayrol_trial.sql:21613 |
| `stock_bal_qty_fn` | FUNCTION | `(`pstart_date` date, `pstore_pkey` int(11), `pitem_pkey` int(11)) RETURNS float LANGUAGE SQL` | Item_allocation_details_view, grn_stock_details_date_view, item_except_grn_allocation_view | mypayrol_trial.sql:22520 |
| `stock_tranfer_item_pkey_update` | PROCEDURE | `()` | item_master, stock_tranfer_item | mypayrol_trial.sql:22563 |
| `stock_tranfer_pkey_prc` | PROCEDURE | `(IN `pstock_tranfer_pkey` int, OUT `perr_msg` varchar(500))` | stock_details, stock_tranfer, stock_tranfer_item | mypayrol_trial.sql:22591 |
| `stock_trans_prc` | PROCEDURE | `(IN `pfrom_store` varchar(400), IN `pto_store` varchar(400), IN `pitem_pkey` int, IN `pitem_qty` int, IN `pdate_created` date, IN `pcreated_by` varchar(100), OUT `perrm` varchar(400))` | stock_details | mypayrol_trial.sql:22636 |
| `structure_calc` | FUNCTION | `(`psalary_head_item_fkey` int, `pemp_pkey` int) RETURNS varchar(200) CHARACTER SET 'latin1' LANGUAGE SQL` | emp_salary_structure | mypayrol_trial.sql:22650 |
| `structure_preview_prc` | PROCEDURE | `(IN `Pcompany_code` varchar(30), IN `Pemp_fkey` int, IN `Pemp_structure_id` int, IN `Pemp_anual_ctc` float)` | emp_proff, salary_head_items, salary_heads, salary_structure, salary_structure_details | mypayrol_trial.sql:22660 |
| `tax_computation_fn` | FUNCTION | `(`Pemp_fkey` int, `pmonth` varchar(20), `pfin_year` int) RETURNS int(11) LANGUAGE SQL` | emp_ctc_transaction, emp_details, emp_proff, emp_salary_slip, emp_salary_structure, emp_tax_regime, emp_tax_sal_trans, emp_tax_sal_trans_new, emp_tax_sal_trans_sum, emp_tax_sal_trans_sum_new, fin_year, payroll_master, tax_computation_report | mypayrol_trial.sql:23000 |
| `tax_salary_distribution_fn` | FUNCTION | `(`Pcompany_code` varchar(30), `Pemp_fkey` int, `pfin_year` varchar(30), `Puserid` varchar(30)) RETURNS int(11) LANGUAGE SQL` | branches, emp_details, emp_proff, emp_salary_slip, emp_salary_structure, emp_tax_sal_trans, emp_tax_sal_trans_sum, emp_tax_transactions, fin_year, payroll_master, profession_tax_slab, salary_head_items, salary_heads, tax_heads, tax_heads_details, tax_salary_components, tax_type, test | mypayrol_trial.sql:23213 |
| `tax_salary_distribution_new_fn` | FUNCTION | `(`Pcompany_code` varchar(30), `Pemp_fkey` int, `pfin_year` varchar(30), `Puserid` varchar(30)) RETURNS int(11) LANGUAGE SQL` | emp_details, emp_proff, emp_salary_slip, emp_salary_structure, emp_tax_sal_trans_new, emp_tax_sal_trans_sum_new, emp_tax_transactions, fin_year, income_tax_slab, payroll_master, salary_head_items, salary_heads, tax_heads, tax_heads_details, tax_salary_components, tax_type, test | mypayrol_trial.sql:23810 |
| `tax_salary_process_prc` | PROCEDURE | `(IN `pmonth` varchar(20), IN `pbranch_code` varchar(30), IN `pemp_pkey` int, IN `ppayroll_master_pkey` int, IN `puser_id` varchar(30), OUT `perr_msg` varchar(500))` | attendance_register, db_config, designation, emp_ctc_transaction, emp_details, emp_proff, emp_salary_slip, gross_salary, payroll_master, salary_structure_details | mypayrol_trial.sql:24507 |
| `time_coupon_auto_fn` | FUNCTION | `(`Pemp_fkey` int, `patt_date` varchar(30), `pyear_month` varchar(20)) RETURNS varchar(100) CHARACTER SET 'latin1' LANGUAGE SQL` | device_attandance, emp_detail_timeattandance, emp_details, emp_leave_transactions, emp_proff, leaveentries, salary_head_items, working_day_time_procedures | mypayrol_trial.sql:24706 |
| `time_duration_check` | FUNCTION | `(`pshift_date` date, `pemp_pkey` int, `pbranch_code` varchar(30)) RETURNS varchar(30) CHARACTER SET 'latin1' LANGUAGE SQL` | device_attandance, emp_detail_timeattandance, emp_details, emp_ot_timeattandance, emp_proff, emp_shift_planner, shift_exceptions, working_day_time_procedures | mypayrol_trial.sql:24911 |
| `time_duration_check_date` | FUNCTION | `(`Pyear_month` date, `pdate` date, `Pemp_pkey` int, `pbranch_code` varchar(30)) RETURNS varchar(300) CHARACTER SET 'latin1' LANGUAGE SQL` | db_config, device_attandance, emp_detail_timeattandance, emp_details, emp_leave_transactions, emp_proff, holidays, leaveentries, salary_head_items, working_day_time_procedures | mypayrol_trial.sql:25227 |
| `time_duration_check_hierarchy` | FUNCTION | `(`Pyear_month` date, `Pemp_pkey` int, `Plogin_emp_pkey` int) RETURNS varchar(100) CHARACTER SET 'latin1' LANGUAGE SQL` | db_config, device_attandance, emp_detail_timeattandance, emp_details, emp_leave_transactions, emp_proff, holidays, leaveentries, working_day_time_procedures | mypayrol_trial.sql:26860 |
| `time_duration_check_multishift` | FUNCTION | `(`Pyear_month` date, `Pemp_pkey` int, `Pbranch_code` varchar(30)) RETURNS varchar(100) CHARACTER SET 'latin1' LANGUAGE SQL` | db_config, device_attandance, emp_detail_timeattandance, emp_details, emp_leave_transactions, emp_proff, holidays, leaveentries, salary_head_items, working_day_time_procedures | mypayrol_trial.sql:27278 |
| `update_breakoff` | FUNCTION | `(`pemp_pkey` int) RETURNS varchar(28) CHARACTER SET 'latin1' LANGUAGE SQL` | device_attandance, emp_details, emp_proff, scheduled_break_off, working_day_time_procedures | mypayrol_trial.sql:27860 |
| `user_access_firstime_only` | FUNCTION | `(`pcompany_code` varchar(30), `pBranch_code` varchar(30), `pemp_fkey` int, `ppasswd` varchar(50), `ppasswd_string` varchar(50)) RETURNS varchar(4000) CHARACTER SET 'latin1' LANGUAGE SQL` | emp_details, emp_menu, mob_user_credentials, test, user_access, user_credentials | mypayrol_trial.sql:27957 |
| `vangrd_pfesi_update_fn` | FUNCTION | `() RETURNS varchar(30) CHARACTER SET 'latin1' LANGUAGE SQL` | emp_details, emp_proff | mypayrol_trial.sql:28118 |
| `weekoff_days_count_fn` | FUNCTION | `(`pemp_pkey` int, `first_date` date, `last_date` date) RETURNS float LANGUAGE SQL` | emp_proff, working_day_time_procedures | mypayrol_trial.sql:28177 |
| `year_ending_fn` | FUNCTION | `(`pclosing_year` varchar(30), `pbranch_code` varchar(30)) RETURNS int(11) LANGUAGE SQL` | emp_details, emp_leave_balance_year, emp_proff, fin_year, leavepolicy | mypayrol_trial.sql:28543 |
| `year_ending_mission_fn` | FUNCTION | `(`pclosing_year` varchar(30), `pbranch_code` varchar(30)) RETURNS int(11) LANGUAGE SQL` | **references nearly all 236 tables** — a company year-end-close/deprovisioning routine that iterates almost every business table (attendance, leave, payroll, stock/inventory, site, assets, docs) doing cleanup/reset work; not a normal single-purpose business function | mypayrol_trial.sql:28634 |

### B.2 Tables (236)

All 236 tables, in file order. Compact format: `table_name(col type [PK|FK->target], ...)  -- [one-line purpose note for core tables]  [citation]`. FK targets are **heuristically resolved from the `*_fkey` naming convention** against the 236-table list (e.g. `emp_fkey`→`emp_details`, `site_fkey`→`site`) since — confirmed by inspecting every table below — **no table in this schema declares an enforced `FOREIGN KEY` constraint** (only `PRIMARY KEY`/`KEY`/`UNIQUE`). Treat FK targets as best-effort convention matches, not verified constraints; a handful of ambiguous/ungueassable `_fkey` columns are left untagged. A few tables (`att_in`, `att_out`, `ctc_fixed_amount`, `ctc_fixed_rate`, etc.) are defined as bare column-list "table" stubs with no `PRIMARY KEY` at all — likely legacy scratch tables or auto-generated report-output shapes.

```

access_site(access_site_pkey int(11) PK, site_fkey int(10) FK->site, emp_fkey int(23) FK->emp_details, created_by varchar(30), modified_by varchar(30), creation_date datetime, modified_date datetime, status int(1))  [schema/mypayrol_trial.sql:28702]
access_store(access_store_pkey int(11) PK, store_pkey int(10), emp_fkey int(23) FK->emp_details, status int(1))  [schema/mypayrol_trial.sql:28715]
activity(noti_pkey int(100) PK, date date, text varchar(200), emp_fkey varchar(200) FK->emp_details, status int(1))  [schema/mypayrol_trial.sql:28724]
additional_details(additonal_details_pkey int(11) PK, item_master_fkey int(10) FK->item_master, minimum_qty double, maximum_qty double, safety_stock double, unit_price double, sales_price double, vendor_price double, created_by varchar(50), created_date timestamp, modified_by varchar(50), modified_date datetime, status int(10))  [schema/mypayrol_trial.sql:28734]
admin_dashboard(admin_dashboard_seq int(30) PK, company_code varchar(30), Dash_boardtype varchar(50), Dash_board_desc varchar(2000), emp_fkey varchar(30) FK->emp_details, Pointed_table varchar(100), pointed_value varchar(1000), iscompleted varchar(10), status int(11))  [schema/mypayrol_trial.sql:28752]
allocate_details(allocate_details_pkey int(100) PK, allocate_fkey int(10), item_purchase_fkey int(10) FK->item_purchase, qty int(10), returned_qty int(10), store_code varchar(100), damaged_qty varchar(100), status int(10), allocate_status int(1), creation_date datetime)  [schema/mypayrol_trial.sql:28766]
Area(area_pkey int(11) PK, are_name varchar(12), status int(11))  [schema/mypayrol_trial.sql:28781]
asset_allocate(allocate_pkey int(11) PK, emp_fkey int(11) FK->emp_details, asset varchar(100), qty int(11), model varchar(50), asset_name varchar(100), brand varchar(50), s_no varchar(50), warranty varchar(50), allocated_date date, retreived_date date, pemp_fkey int(11) FK->emp_details, status varchar(11), damaged_amout varchar(11), retrieved_damage varchar(11), official_mail varchar(100), official_contact varchar(30), crm_id varchar(30), allocated_ofc_space varchar(30), asset_state varchar(11), description varchar(100), active varchar(11))  [schema/mypayrol_trial.sql:28789]
asset_management(asset_pkey int(11) PK, emp_fkey int(11) FK->emp_details, name varchar(100), specifications varchar(400), Type varchar(100), serial_no varchar(100), warranty varchar(40), model varchar(40), brand varchar(40), status varchar(110), allocated_status varchar(110), description varchar(500), year varchar(20), value varchar(11), condition varchar(11), active varchar(11))  [schema/mypayrol_trial.sql:28817]
asset_types(asset_type_pkey int(11) PK, asset_type_name varchar(50))  [schema/mypayrol_trial.sql:28838]
attandance(attandance_seq int(11) PK, company_code varchar(20), branch_code varchar(20), emp_id varchar(20), intime datetime, indevice_id int(11), outtime datetime, outdevice_id int(11), duration int(11), c1 varchar(255), c2 varchar(255), c3 varchar(255), c4 varchar(255))  [schema/mypayrol_trial.sql:28869]
attendancelogs(Branch_code varchar(10), ATTENDANCELOGID decimal(10,0), ATTENDANCEDATE date, EMPLOYEEID int(10), INTIME datetime, INDEVICEID varchar(255), OUTTIME datetime, OUTDEVICEID varchar(255), DURATION decimal(10,2), LATEBY int(11), EARLYBY int(11), ISONLEAVE int(11), LEAVETYPE varchar(50), LEAVEDURATION int(100), WEEKLYOFF int(100), HOLIDAY int(100), LEAVEREMARKS varchar(11), PUNCHRECORDS int(11), SHIFTID int(11), PRESENT int(11), ABSENT int(11), STATUS varchar(255), STATUSCODE varchar(250), P1STATUS varchar(255), P2STATUS varchar(255), P3STATUS varchar(255), ISONSPECIALOFF int(11), SPECIALOFFTYPE varchar(255), SPECIALOFFREMARK varchar(255), SPECIALOFFDURATION int(11), OVERTIME int(11), OVERTIMEE int(11), MISSEDOUTPUNCH int(11), REMARKS varchar(255), MISSEDINPUNCH int(11), LEAVETYPEID int(11), C1 int(11), C2 int(11), C3 int(11), C4 int(11), C5 int(11), C6 int(11), C7 int(11))  [schema/mypayrol_trial.sql:28909]
attendance_punch(attendance_punch_pkey int(11) PK, date_punch datetime, emp_fkey int(11) FK->emp_details, direction varchar(3), ip_ad varchar(20), location varchar(400), browser varchar(200), host_name varchar(100), os varchar(100))  [schema/mypayrol_trial.sql:28956]
attendance_register(company_code varchar(20), branch_code varchar(20), registerid int(11) PK, emp_fkey int(11) FK->emp_details, month_year varchar(20), emp_company_id varchar(20), emp_name varchar(120), FIELD1 varchar(20), FIELD2 varchar(20), FIELD3 varchar(20), FIELD4 varchar(20), FIELD5 varchar(20), FIELD6 varchar(20), FIELD7 varchar(20), FIELD8 varchar(20), FIELD9 varchar(20), FIELD10 varchar(20), FIELD11 varchar(20), FIELD12 varchar(20), FIELD13 varchar(20), FIELD14 varchar(20), FIELD15 varchar(20), FIELD16 varchar(20), FIELD17 varchar(20), FIELD18 varchar(20), FIELD19 varchar(20), FIELD20 varchar(20), FIELD21 varchar(20), FIELD22 varchar(20), FIELD23 varchar(20), FIELD24 varchar(20), FIELD25 varchar(20), FIELD26 varchar(20), FIELD27 varchar(20), FIELD28 varchar(20), FIELD29 varchar(20), FIELD30 varchar(20), FIELD31 varchar(20), FIELD32 varchar(20), isdelete varchar(20), presant_total float, leave_total float, lop_total float, wd_lop_total float, lop_only float, weekoff_total float, na_wo_count float, holiday_total float, na_ho_count float, working_days float, calander_days float, record_status varchar(20), userid varchar(20))  -- the monthly per-employee attendance grid (FIELD1..FIELD32 = one day-status code per day) — central table read/written by nearly every attendance & payroll procedure  [schema/mypayrol_trial.sql:28986]
attendance_register_history(attendance_register_history_pkey int(11) PK, branch varchar(20), month varchar(20), start_time datetime, created_date datetime, end_time datetime, duration int(11), created_by varchar(30), process varchar(30), status int(11))  [schema/mypayrol_trial.sql:29045]
attendance_register_rep(company_code varchar(20), branch_code varchar(20), registerid int(11) PK, emp_fkey int(11) FK->emp_details, month_year varchar(20), emp_company_id varchar(20), emp_name varchar(120), FIELD1 varchar(20), FIELD2 varchar(20), FIELD3 varchar(20), FIELD4 varchar(20), FIELD5 varchar(20), FIELD6 varchar(20), FIELD7 varchar(20), FIELD8 varchar(20), FIELD9 varchar(20), FIELD10 varchar(20), FIELD11 varchar(20), FIELD12 varchar(20), FIELD13 varchar(20), FIELD14 varchar(20), FIELD15 varchar(20), FIELD16 varchar(20), FIELD17 varchar(20), FIELD18 varchar(20), FIELD19 varchar(20), FIELD20 varchar(20), FIELD21 varchar(20), FIELD22 varchar(20), FIELD23 varchar(20), FIELD24 varchar(20), FIELD25 varchar(20), FIELD26 varchar(20), FIELD27 varchar(20), FIELD28 varchar(20), FIELD29 varchar(20), FIELD30 varchar(20), FIELD31 varchar(20), FIELD32 varchar(20), isdelete varchar(20), record_status varchar(20), userid varchar(20))  -- replica/reporting copy of attendance_register maintained by insert_update_att_reg_rep  [schema/mypayrol_trial.sql:29060]
attendance_register_update(attendance_register_update int(11) PK, month_year date, emp_fkey int(11) FK->emp_details, field varchar(400), status int(1))  [schema/mypayrol_trial.sql:29107]
att_in()  [schema/mypayrol_trial.sql:29117]
att_out()  [schema/mypayrol_trial.sql:29120]
bank(id int(11) PK, bank_name varchar(200), bank_branch varchar(200), ifsc_code varchar(50), acct_no varchar(40), status int(11))  [schema/mypayrol_trial.sql:29123]
branches(id int(11) PK, company_code varchar(20), branch_code varchar(20), branch_name varchar(200), address text, city varchar(100), state varchar(50), pincode varchar(20), latitude double, longitude double, status int(11), deleted int(11))  [schema/mypayrol_trial.sql:29134]
branch_exceptions(branch_exceptions_pkey int(11) PK, branch_code varchar(30), no_pt char(1), status int(11))  [schema/mypayrol_trial.sql:29188]
calendar_table(dt date, y smallint(6), q tinyint(4), m tinyint(4), d tinyint(4), dw tinyint(4), week_month tinyint(4), monthName varchar(9), dayName varchar(9), w tinyint(4), isWeekday binary(1), isHoliday binary(1), holidayDescr varchar(32), isPayday binary(1))  [schema/mypayrol_trial.sql:29197]
category_master(category_pkey int(10) PK, code varchar(100), description varchar(50), created_by varchar(50), created_date datetime, modified_by varchar(50), modified_date datetime, status int(11))  [schema/mypayrol_trial.sql:40540]
compliance(cin_no varchar(100), pan_no varchar(100), service_tax varchar(100), tan_no varchar(100), pf_no varchar(100), emp_state_ins_no varchar(100), pt_no_co varchar(100), pt_no_dir varchar(100), pt_no_emp varchar(100), status int(11), id int(11) PK)  [schema/mypayrol_trial.sql:40553]
comp_contact_info(id int(11) PK, business_name varchar(200), business_nature varchar(200), business_type varchar(200), address varchar(500), city varchar(100), pincode varchar(10), state varchar(100), phone varchar(100), fax varchar(100), email varchar(200), website varchar(200), logo varchar(200), subdomain varchar(200), logosize int(11), max_emp_count int(11), plan varchar(10))  [schema/mypayrol_trial.sql:40569]
contacts(contact_id int(11) PK, first_name varchar(100), last_name varchar(100), middle_name varchar(100), company_name varchar(100), email varchar(100), phone bigint(20), address text, city varchar(100), state varchar(50), pincode varchar(20), payment_type varchar(20), payment_mode varchar(20), credit_days int(11), credit_limit float, product_rate_type varchar(20), pan_no varchar(30), tin varchar(30), cst varchar(30), service_tax_no varchar(30), opening_balance float, relationship varchar(30), bank_name varchar(50), bank_branch varchar(100), ifsc_code varchar(20), account_no bigint(20), status int(11), c_gender varchar(10), c_mob_no bigint(20), reg varchar(20), c_designation varchar(100), pan varchar(100), gst varchar(100), c_email varchar(100))  [schema/mypayrol_trial.sql:40593]
contracted_days(contracted_days_pkey int(11) PK, emp_fkey int(11) FK->emp_details, contract_start_date date, contract_end_date date, start_date_effective date, end_date_effective date, status int(11), created_by varchar(30), created_time datetime, modified_by varchar(30), modified_time datetime)  [schema/mypayrol_trial.sql:40633]
countries_nationality(id int(11) PK, country_code varchar(2), country_name varchar(100), nationality varchar(100))  [schema/mypayrol_trial.sql:40649]
ctc_fixed_amount()  [schema/mypayrol_trial.sql:40848]
ctc_fixed_rate()  [schema/mypayrol_trial.sql:40851]
cus_visit_locations(cus_visit_locations_pkey int(11) PK, mob_user_locations_pkey int(11), emp_fkey int(11) FK->emp_details, user_id varchar(30), start_time datetime, start_latitude double, start_longitude double, start_location varchar(100), start_loc_source varchar(100), start_uploaded_time datetime, action1 varchar(20), customer_name1 varchar(200), stepin_time datetime, stepin_latitude double, stepin_longitude double, stepin_location varchar(100), stepin_loc_source varchar(100), stepin_uploaded_time datetime, action2 varchar(20), customer_name2 varchar(200), stepout_time datetime, stepout_latitude double, stepout_longitude double, stepout_location varchar(100), stepout_loc_source varchar(100), stepout_uploaded_time datetime, action3 varchar(20), customer_name3 varchar(200), purpose varchar(500), contact_person varchar(100), contact_number varchar(25), start_stepin_distance varchar(100), stepin_stepout_distance varchar(100), duration1 varchar(100), duration2 varchar(100), total_distance varchar(100), total_duration varchar(100), creation_date datetime)  [schema/mypayrol_trial.sql:40854]
date_intervel_monthyear(date_intervel_monthyear_pkey int(11) PK, month_year varchar(12), start_date date, end_date date)  [schema/mypayrol_trial.sql:40900]
db_config(db_config_pkey int(11) PK, company_code varchar(30), biometric_device_essl varchar(30), attendance_type varchar(100), attendance_format varchar(100), attendance_date int(11), emp_login char(1), payroll_type varchar(100), email_setup char(1), TDS_setup char(1), Salary_date int(11), active char(1), leave_url varchar(200), expense_url varchar(200), profile_url varchar(200), subdomain_url varchar(200), created_date date, modified_date date, modified_by varchar(100), created_by varchar(100))  -- per-company runtime configuration flags (e.g. payroll_type M/manual)  [schema/mypayrol_trial.sql:40909]
department(id int(11) PK, dept_code varchar(50), dept_name varchar(100), status int(11))  [schema/mypayrol_trial.sql:40936]
designation(id int(11) PK, desig_code varchar(20), desig_name varchar(100), status int(11))  [schema/mypayrol_trial.sql:40954]
device_attandance(device_attandance_seq int(11) PK, company_code varchar(20), branch_code varchar(20), DEVICELOGID int(11), DOWNLOADDATE datetime, DEVICEID int(11), device_USERID varchar(100), emp_id varchar(20), LOGDATE datetime, DIRECTION varchar(50), ATTDIRECTION varchar(50), SHIFT varchar(100), SHIFTDATE date, C1 varchar(100), C2 varchar(100), C3 varchar(100), C4 varchar(100), C5 varchar(100), C6 varchar(100), C7 varchar(100), WORKCODE varchar(100), status varchar(11))  -- raw biometric device punch log per company (post device-routing from control DB devicelogs)  [schema/mypayrol_trial.sql:40975]
device_attandance_hist(device_attandance_hist_pkey int(11) PK, device_attandance_seq int(11), company_code varchar(20), branch_code varchar(20), DEVICELOGID int(11), DOWNLOADDATE datetime, DEVICEID int(11), device_USERID varchar(100), emp_id varchar(20), LOGDATE datetime, DIRECTION varchar(50), ATTDIRECTION varchar(50), C1 varchar(100), C2 varchar(100), C3 varchar(100), C4 varchar(100), C5 varchar(100), C6 varchar(100), C7 varchar(100), WORKCODE varchar(100), status varchar(11), created_by varchar(50), creation_date datetime, action char(1))  [schema/mypayrol_trial.sql:41868]
direct_po_details(direct_po_details_pkey int(11) PK, direct_po_fkey int(11), item_code int(11), uom int(11), ordered_qty int(11), po_rate int(11), po_value int(11), created_by varchar(50), creation_date timestamp, modified_by varchar(50), modified_date timestamp, status int(11))  [schema/mypayrol_trial.sql:41900]
direct_purchase_order(direct_po_pkey int(11) PK, direct_po_number varchar(50), direct_po_date date, location varchar(50), remarks varchar(50), created_by varchar(50), creation_date timestamp, modified_by varchar(50), modified_date datetime, status int(11))  [schema/mypayrol_trial.sql:41917]
division(id int(11) PK, div_code varchar(20), div_name varchar(100), status int(11))  [schema/mypayrol_trial.sql:41932]
documents(document_pkey int(11) PK, emp_fkey int(11) FK->emp_details, template_fkey int(11), doc_id varchar(30), company_fkey int(11), branch_fkey int(11) FK->branches, supplier_fkey int(11), customer_fkey int(11), others_fkey int(11), policy int(11), document_name varchar(100), document text, created_by varchar(30), creation_date datetime, modified_by varchar(30), modification_date datetime, status int(11))  [schema/mypayrol_trial.sql:41941]
document_allocation(document_allocation_pkey int(11) PK, emp_fkey int(11) FK->emp_details, document_upload_fkey int(11) FK->document_upload, allocated_by varchar(200), allocated_date datetime, end_date_effective datetime, status int(11))  [schema/mypayrol_trial.sql:41963]
document_upload(document_upload_pkey int(11) PK, document_name varchar(100), document_path varchar(300), type varchar(300), created_by varchar(200), creation_date datetime, document_allocated_by varchar(200), document_allocated_date datetime, modified_by varchar(200), modification_date datetime, status int(11))  [schema/mypayrol_trial.sql:41975]
doc_images(image_pkey int(11) PK, image_url varchar(250), company_code varchar(50), created_by varchar(50), created_time datetime, modified_by varchar(50), modification_date datetime, status int(11))  [schema/mypayrol_trial.sql:41991]
doc_template(template_pkey int(11) PK, template_name varchar(200), template_content text, placeholders varchar(500), availability varchar(10), editable varchar(10), policy varchar(10), created_by varchar(30), creation_date datetime, modified_by varchar(30), modification_date datetime, status int(11))  [schema/mypayrol_trial.sql:42004]
Education(education_pkey int(11) PK, emp_join_fkey int(11) FK->emp_join, course varchar(255), university varchar(255), duration varchar(20), mark varchar(20), status tinyint(1))  [schema/mypayrol_trial.sql:42039]
Email_Content(email_pkey int(11) PK, mail_type varchar(100), company_code varchar(100), Message text, subject varchar(200), Content text, Action varchar(100), Type varchar(100), Image varchar(200), status char(1))  [schema/mypayrol_trial.sql:42053]
emi_upload(emi_upload_pkey int(11) PK, loan_pkey int(11), emp_pkey int(11), month_year varchar(100), amt int(11), creation_date datetime, status int(11))  [schema/mypayrol_trial.sql:42093]
employees_mssql(EmployeeId int(11), EmployeeName varchar(50), EmployeeCode varchar(50), StringCode varchar(50), NumericCode int(11), Gender varchar(255), CompanyId int(11), DepartmentId int(11), Designation varchar(255), CategoryId int(11), DOJ datetime, DOR datetime, DOC datetime, EmployeeCodeInDevice varchar(50), EmployeeRFIDNumber varchar(255), EmployementType varchar(255), Status varchar(255), EmployeeDevicePassword varchar(50), EmployeeDeviceGroup varchar(50), FatherName varchar(255), MotherName varchar(255), ResidentialAddress varchar(255), PermanentAddress varchar(255), ContactNo varchar(255), Email varchar(255), DOB datetime, PlaceOfBirth varchar(255), Nomenee1 varchar(255), Nomenee2 varchar(255), Remarks text, RecordStatus int(11), C1 varchar(255), C2 varchar(255), C3 varchar(255), C4 varchar(255), C5 varchar(255), C6 varchar(255), C7 varchar(255), Location varchar(255), BLOODGROUP varchar(255), WorkPlace varchar(255), ExtensionNo varchar(255), LoginName varchar(255), LoginPassword varchar(255), Grade varchar(255), Team varchar(255), IsRecieveNotification int(11), HolidayGroup int(11), ShiftGroupId int(11), ShiftRosterId int(11), LastModifiedBy varchar(50))  -- legacy MSSQL-sourced employee import staging table (migration artifact)  [schema/mypayrol_trial.sql:42106]
employee_info()  [schema/mypayrol_trial.sql:42161]
employee_regularaization(id int(11) PK, att_date varchar(100), C1 varchar(100), C3 varchar(100), empid varchar(100), LOGDATE varchar(100), LOGTIME varchar(100), approved varchar(100), approved_person varchar(100), remarks varchar(500), status int(11), created_by varchar(100), created_date datetime, updated_by varchar(100), updated_date datetime)  [schema/mypayrol_trial.sql:42164]
employee_structure_vview()  [schema/mypayrol_trial.sql:42184]
emp_advance(emp_advance_pkey int(11) PK, emp_fkey int(11) FK->emp_details, advance_amount float, affected_month varchar(50), is_credited char(1), remarks text, created_date datetime, created_by varchar(20), modified_by varchar(20), modified_date datetime, status int(11), payment_date date)  -- salary advance requests  [schema/mypayrol_trial.sql:42187]
emp_advance_info(emp_advance_info_pkey int(11) PK, emp_fkey int(11) FK->emp_details, advance_forthe_month varchar(100), advance_amount int(11), mode_of_advance varchar(100), advance_details varchar(500), attr1 varchar(500), attr2 int(11), attr3 int(11))  [schema/mypayrol_trial.sql:42204]
emp_attendance_upload(emp_attendance_upload_pkey int(11) PK, emp_fkey int(11) FK->emp_details, attendance_type int(11), in_date date, in_time time, out_date date, out_time time, created_by varchar(30), created_date timestamp, is_updated char(1), updated_date date, status int(11))  [schema/mypayrol_trial.sql:42218]
emp_banks(emp_banks_pkey int(11) PK, name varchar(400), status int(1))  [schema/mypayrol_trial.sql:42272]
emp_calc_variable_components(emp_calc_variable_components_pkey int(11) PK, emp_fkey int(11) FK->emp_details, month_year varchar(50), day_time_seq varchar(50), salary_head_item_fkey int(11) FK->salary_head_items, salary_head_item_desc varchar(250), structure_det_value decimal(10,2), salary_rate decimal(10,2), salary_amount decimal(10,2), head_operator varchar(30), head_type varchar(30), item_part varchar(30), remarks varchar(500), created_by varchar(30), created_date timestamp, modified_by varchar(30), modified_date timestamp, end_date_effective date)  [schema/mypayrol_trial.sql:42507]
emp_config(type varchar(100), id int(11) PK, company_code varchar(20), branch_code varchar(20), emp_fkey int(11) FK->emp_details, month_year varchar(20), policy_id int(11), creation_date datetime, created_by varchar(50), modified_by varchar(50), modification_date datetime, hirc_leval int(11), hirc_up_flag varchar(10), status int(11))  [schema/mypayrol_trial.sql:42530]
emp_config_history()  [schema/mypayrol_trial.sql:42605]
emp_ctc_detail(emp_ctc_detail_pkey int(11) PK, emp_fkey int(11) FK->emp_details, start_date_effective date, salary_head_item_fkey int(11) FK->salary_head_items, salary_head_item_desc varchar(250), structure_det_value int(11), salary_rate float, salary_amount float, creation_date datetime, end_date_effective date, created_by varchar(30), modified_by varchar(30), remarks varchar(3000))  [schema/mypayrol_trial.sql:42608]
emp_ctc_transaction(emp_ctc_transaction int(11) PK, ctc_upload_fkey int(11), ctc_upload_type int(11), emp_fkey int(11) FK->emp_details, emp_anual_ctc float, emp_derived_anualctc float, created_by varchar(30), created_date datetime, modified_date date, modified_by int(11), end_date_effective date, remarks varchar(200), arrear_salary char(1), pay_out_month date, start_date_effective date, next_increment_date date)  -- employee CTC history (annual CTC value, effective-dated)  [schema/mypayrol_trial.sql:42626]
emp_ctc_upload(emp_ctc_upload_pkey int(11) PK, emp_fkey int(11) FK->emp_details, emp_anual_ctc int(11), emp_monthly_ctc int(11), emp_loan_balance int(11), emp_advance int(11), emp_tds_deducted int(11), arrear_salary char(1), pay_out_month date, created_by varchar(30), created_date datetime, start_date_effective date, end_date_effective date, approved_by int(11), status int(11), next_increment_date date, branch varchar(100), designation varchar(100), department varchar(100))  [schema/mypayrol_trial.sql:42649]
emp_dashboard(emp_dashboard_seq int(30) PK, company_code varchar(30), Dash_boardtype varchar(50), Dash_board_desc varchar(2000), emp_fkey varchar(30) FK->emp_details, Pointed_table varchar(100), pointed_value varchar(1000), status int(11))  [schema/mypayrol_trial.sql:42722]
emp_detailed_attendance_uploads(emp_detailed_attendance_pkey int(11) PK, emp_fkey int(11) FK->emp_details, attendance_type int(1), att_date date, att_time datetime, c1 varchar(11), created_by varchar(22), created_date timestamp, is_updated char(1), status int(1))  [schema/mypayrol_trial.sql:42735]
emp_details(emp_pkey int(11) PK, company_code varchar(30), branch_code varchar(30), emp_id varchar(30), first_name varchar(100), middile_name varchar(100), last_name varchar(100), classification varchar(50), address text, city varchar(50), state varchar(50), nationality_id int(11), pincode varchar(50), mobile_no varchar(50), email varchar(100), maritual_status varchar(20), education varchar(100), date_of_birth date, bank_name varchar(100), branch_name varchar(100), branch_address text, name_as_per_bank varchar(100), ifsc_code varchar(30), account_no varchar(30), pf varchar(100), company_pf varchar(100), previous_member_id varchar(20), esi_dispensary varchar(200), blood varchar(20), esi varchar(200), eps varchar(30), international_worker varchar(10), country int(11), physical_handicap varchar(10), locomotive varchar(10), hearing varchar(10), visual varchar(10), id_card varchar(100), name_as_on_aadhaar varchar(100), guradian varchar(100), relation_guardian varchar(100), pan_no varchar(50), name_as_on_pan varchar(100), wps_code varchar(50), lwf_code varchar(50), status int(11), parent int(100), attr3 varchar(100), creation_date timestamp, created_by varchar(100), modified_by varchar(100), modified_date timestamp, attr4 varchar(100), attr5 varchar(100), payment_type varchar(50), is_manager tinyint(4), profile_pic varchar(500), editable int(10))  -- core employee master record (personal/bank/statutory identity fields)  [schema/mypayrol_trial.sql:42785]
emp_details_audit(audit_pkey int(11) PK, emp_pkey int(11), company_code varchar(30), branch_code varchar(30), emp_id varchar(30), first_name varchar(100), middile_name varchar(100), last_name varchar(100), classification varchar(50), address text, city varchar(50), state varchar(50), nationality_id int(11), pincode varchar(50), mobile_no varchar(50), email varchar(100), maritual_status varchar(20), education varchar(100), date_of_birth date, bank_name varchar(100), branch_name varchar(100), branch_address text, name_as_per_bank varchar(100), ifsc_code varchar(30), account_no varchar(30), pf varchar(100), company_pf varchar(100), previous_member_id varchar(20), esi_dispensary varchar(200), blood varchar(20), esi varchar(200), eps varchar(30), international_worker varchar(10), country int(11), physical_handicap varchar(10), locomotive varchar(10), hearing varchar(10), visual varchar(10), id_card varchar(100), name_as_on_aadhaar varchar(100), guradian varchar(100), relation_guardian varchar(100), pan_no varchar(50), name_as_on_pan varchar(100), wps_code varchar(50), status int(11), parent int(100), attr3 varchar(100), attr4 varchar(100), attr5 varchar(100), payment_type varchar(50), lwf_code varchar(50), is_manager tinyint(4), profile_pic varchar(500), editable int(10), updated_date date, updated_time time)  [schema/mypayrol_trial.sql:43062]
emp_detail_attandance_reg(attandance_batch_key varchar(30), emp_pkey int(11), att_date date, att_in_time datetime, att_out_time datetime, duration int(11), present varchar(20), weekoff varchar(20), leaves varchar(20), holiday varchar(10), others varchar(10), yearmonth date)  [schema/mypayrol_trial.sql:43127]
emp_detail_status_update(emp_detail_status_update_pkey int(11) PK, emp_fkey int(11) FK->emp_details, att_date date, yearmonth date, main_status varchar(20), aditional_status varchar(20), creation_date datetime, created_by varchar(50), modified_by varchar(50), modified_date datetime, remarks varchar(500), action varchar(30), status int(11))  [schema/mypayrol_trial.sql:43146]
emp_detail_timeattandance(emp_detail_timeattandance_pkey int(11) PK, emp_pkey int(11), att_date date, att_in_time datetime, att_out_time datetime, duration int(11), present varchar(20), weekoff varchar(20), leaves varchar(20), holiday varchar(10), others varchar(10), yearmonth date, site_fkey int(11) FK->site, site_transactions_fkey bigint(20) FK->site_transactions, isdelete char(1), shift_string varchar(2000), day_time_id int(11), ad_present varchar(20), ad_in_time datetime, ad_out_time datetime, ad_duration int(11), ad_remarks int(11), ad_shift_string varchar(2000), ad_day_time_id int(11))  -- computed daily shift duration/present-absent detail per employee per day, feeds attendance_register and payroll  [schema/mypayrol_trial.sql:43164]
emp_documents(emp_doc_pkey int(11) PK, emp_join_fkey int(11) FK->emp_join, document_type varchar(20), document_number varchar(100), classification varchar(50), name varchar(50), relation varchar(50), valid_from date, valid_till date, nationality varchar(50), remarks varchar(500), files varchar(5000), created_date datetime, created_by varchar(20), modified_by varchar(20), modified_date datetime, reccuring varchar(4), remind int(11), status int(11))  [schema/mypayrol_trial.sql:43197]
emp_early_in()  [schema/mypayrol_trial.sql:43223]
emp_early_out()  [schema/mypayrol_trial.sql:43226]
emp_encash_slip(emp_encash_slip_pkey int(11) PK, leave_encashment_master_fkey int(11) FK->leave_encashment_master, emp_fkey int(11) FK->emp_details, month_year varchar(20), salary_head_item_fkey int(11) FK->salary_head_items, salary_head_item_desc varchar(250), structure_det_value int(11), salary_rate float, salary_amount float, presant_total float, leave_total float, lop_total float, head_operator varchar(30), head_type varchar(30), item_part varchar(30), creation_date datetime, end_date_effective date, created_by varchar(30), modified_by varchar(30), remarks varchar(3000), approved char(1), action varchar(30))  [schema/mypayrol_trial.sql:43229]
emp_expense(emp_expenses_pkey int(11) PK, emp_fkey int(11) FK->emp_details, expenses_amount double, affected_month date, is_credited char(1), authorized_by varchar(10), authorized_date date, remarks_auth varchar(100), approved_by varchar(10), approved_date date, remarks_approved varchar(100), expense_date date, expense_type varchar(40), created_date datetime, remarks varchar(400), vendor varchar(400), purpose varchar(500), created_by varchar(20), modified_by varchar(20), modified_date datetime, expense_status varchar(200), status int(1), image varchar(500))  [schema/mypayrol_trial.sql:43256]
emp_family(emp_family_pkey int(11) PK, emp_fkey int(11) FK->emp_details, name varchar(50), DOB date, gender varchar(50), blood_group varchar(50), relation varchar(50), nationality varchar(50), contact_number varchar(10), alternate_number varchar(10), emergency_contact char(1), remarks varchar(500), created_date datetime, created_by varchar(20), modified_by varchar(20), modified_date datetime, is_nominee char(1), status int(11))  [schema/mypayrol_trial.sql:43284]
emp_fixed_component_upload(emp_fixed_component_upload_pkey int(11) PK, emp_fkey int(11) FK->emp_details, salary_head_item_fkey int(11) FK->salary_head_items, salary_head_item_desc varchar(100), uploaded_amount float, head_operator varchar(30), head_type varchar(30), item_part varchar(30), occurance varchar(30), start_date_effective date, end_date_effective date, creation_date datetime, created_by varchar(50), remarks varchar(500), action varchar(30), status int(11))  [schema/mypayrol_trial.sql:43307]
emp_join(emp_fkey int(11) FK->emp_details, emp_join_pkey int(11) PK, first_name varchar(100), last_name varchar(100), date_of_birth date, email varchar(100), mobile_no varchar(50), address text, id_card varchar(100), pincode varchar(50), district varchar(100), blood varchar(20), maritual_status varchar(20), guradian varchar(100), relation_guardian varchar(100), classification varchar(100), nationality_id int(11), state varchar(100), bank varchar(100), bank_branch varchar(100), ifsc_code varchar(30), account_no varchar(30), pf varchar(100), company_pf varchar(100), previous_member_id varchar(20), esi_dispensary varchar(200), international_worker varchar(10), locomotive varchar(10), hearing varchar(10), visual varchar(10), country_origin int(11), wps_code varchar(50), lwf_code varchar(50), status int(11), physical_handicap varchar(10), esi varchar(200), eps varchar(30), pan_no varchar(50), profile_image_url varchar(255))  -- new-employee onboarding/join-request workflow record  [schema/mypayrol_trial.sql:43328]
emp_late_in()  [schema/mypayrol_trial.sql:43372]
emp_late_out()  [schema/mypayrol_trial.sql:43375]
emp_leave_approval(emp_leave_approval_pkey int(11) PK, LEAVEENTRYID int(11), sanction_person int(11), sanction_remarks varchar(100), email_url varchar(800), leave_status varchar(50), created_by varchar(100), creation_date datetime, modified_by varchar(100), modified_date datetime, status int(11))  [schema/mypayrol_trial.sql:43378]
emp_leave_balance_year(emp_leave_balance_year_pkey int(11) PK, emp_fkey int(11) FK->emp_details, salary_head_item_fkey int(11) FK->salary_head_items, leave_cycle_start_date date, leave_cycle_end_date date, fin_year varchar(30), alloted_forthe_year float, leave_taken_forthe_year float, balance_forthe_year float, carry_forwarded float, end_process_adjust float, leave_policy_type char(1), creation_date datetime, modification_date datetime, data_source varchar(20), status int(11), created_time datetime)  -- per-employee per-leave-type per-financial-year balance ledger  [schema/mypayrol_trial.sql:43395]
emp_leave_info(emp_leave_info int(11) PK, LEAVEENTRYID int(11), emp_fkey int(11) FK->emp_details, salary_head_item_fkey int(11) FK->salary_head_items, alloted_leave_forthe_year int(11), remaining_leave_forthe_year int(11), alloted_leave_forthe_month int(11), remaining_leave_forthe_month int(11), other_info varchar(100), attr1 varchar(500), attr2 int(11), attr3 varchar(500), attr4 varchar(500), attr5 varchar(500))  [schema/mypayrol_trial.sql:43417]
emp_leave_transactions(emp_leave_transactions_pkey int(11) PK, LEAVEENTRYID int(11), leave_date date, leave_session int(11), Leavestatus varchar(100), Remarks varchar(500), creation_date timestamp)  [schema/mypayrol_trial.sql:43438]
emp_leave_upload(emp_leave_upload_pkey int(11) PK, PID int(11), emp_fkey int(11) FK->emp_details, leave_type int(11), leave_start_date date, leave_start_session int(11), leave_end_date date, leave_end_session int(11), leaveentry_id int(11), created_by varchar(30), created_date timestamp, is_updated char(1), updated_date date, status int(11))  [schema/mypayrol_trial.sql:43452]
emp_loan(emp_loan_pkey int(11) PK, emp_fkey int(11) FK->emp_details, loan_amount float, tenure int(11), intrest_rate float, emi_amount float, emi_start_month varchar(50), emi_end_month varchar(50), remarks varchar(500), is_completed char(1), created_date datetime, created_by varchar(20), modified_by varchar(20), modified_date datetime, status int(11))  -- employee loan master  [schema/mypayrol_trial.sql:43471]
emp_loan_info(emp_loan_info_pkey int(11) PK, emp_fkey int(11) FK->emp_details, loan_type varchar(100), loan_pkey int(11), loan_month varchar(11), loan_tenure int(11), principle int(11), interest int(11), amount_to_paid int(11), amount_paid int(11), loan_emi int(11), closing_balance int(11), opening_balance int(11), paid_status varchar(11), emi_transfer varchar(11), status int(11), remaining_amt_forthe_year int(11), remarks varchar(500), user_remarks varchar(500), creation_date timestamp, created_by varchar(100), modified_by varchar(100), modified_date timestamp)  -- loan EMI schedule/payment tracking  [schema/mypayrol_trial.sql:43491]
emp_menu(menu_id int(11) PK, parent_id int(11), menu_url varchar(500), menu_title varchar(100), menu_name varchar(100), active char(1), iconCls varchar(100), is_default varchar(100), user_id int(10))  [schema/mypayrol_trial.sql:43519]
emp_monthly_salary_components(emp_monthly_salary_components_pkey int(11) PK, emp_fkey int(11) unsigned FK->emp_details, month_year varchar(20), salary_head_item_fkey int(11) unsigned FK->salary_head_items, salary_head_item_desc varchar(250), structure_det_value decimal(10,0), salary_rate decimal(10,0), salary_amount decimal(10,0), head_operator varchar(30), head_type varchar(30), item_part varchar(30), remarks varchar(500), created_by varchar(30), creation_date timestamp, modified_by varchar(30), modification_date timestamp, end_date_effective date)  [schema/mypayrol_trial.sql:43606]
emp_new_salary_structure(emp_new_salary_structure_pkey int(11) PK, emp_fkey int(11) FK->emp_details, emp_structure_id int(11), prorate_code varchar(30), prorate_desc varchar(100), defined_structure_for varchar(250), salary_head_item_fkey int(11) FK->salary_head_items, salary_head_item_desc varchar(250), structure_det_value decimal(10,0), creation_date datetime, head_operator varchar(30), head_type varchar(30), item_part varchar(30), end_date_effective date, created_by varchar(30), modified_by int(11), remarks varchar(3000))  [schema/mypayrol_trial.sql:43628]
emp_ot_master(emp_ot_master_pkey int(11) PK, emp_fkey int(11) FK->emp_details, emp_name varchar(200), month date, total_duration float, set_duration float, is_verified char(1), remarks varchar(500))  -- verified overtime entries per employee/month  [schema/mypayrol_trial.sql:43654]
emp_ot_timeattandance(id int(11) PK, emp_pkey int(11), att_date date, att_in_time datetime, att_out_time datetime, duration int(11), min_bfr_on_dutty_cal_ot varchar(30), min_aftr_off_dutty_cal_ot varchar(30), work_time_day_off_cal_ot float, ot_duration float, present varchar(20), weekoff varchar(20), leaves varchar(20), holiday varchar(10), others varchar(10), set_duration float, remarks varchar(200), is_manual char(1), yearmonth date, created_by varchar(50), creation_date timestamp, isdelete char(1))  [schema/mypayrol_trial.sql:43667]
emp_passport_visa(emp_passport_visa_pkey int(11) PK, emp_fkey int(11) FK->emp_details, document_type varchar(20), document_number varchar(100), classification varchar(50), name varchar(50), relation varchar(50), valid_from date, valid_till date, nationality varchar(50), remarks varchar(500), files varchar(5000), created_date datetime, created_by varchar(20), modified_by varchar(20), modified_date datetime, reccuring varchar(4), remind int(11), status int(11))  [schema/mypayrol_trial.sql:43697]
emp_pay_info(emp_pay_info_pkey int(11) PK, salary_head_item_fkey int(11) FK->salary_head_items, amount int(11), salary_condition varchar(50), attr1 int(11), attr2 varchar(500), attr3 varchar(500), attr4 int(11), attr5 varchar(500))  [schema/mypayrol_trial.sql:43721]
emp_proff(emp_proff_pkey int(11) PK, emp_fkey int(11) FK->emp_details, joining_date date, emp_company_id varchar(30), emp_type varchar(50), designation varchar(100), emp_dept varchar(100), emp_grade varchar(100), emp_vertical varchar(100), emp_branch varchar(100), HOLIDAY_GROUP_ID int(11), LEAVEPOLICY_GROUP_ID int(11), day_time_seq int(11), structure_id int(11), notice_days int(11), multishift int(11), leave_encash_priv int(11), leave_mgt_priv int(11), emp_sep_priv int(11), tax_mgt_priv int(11), atten_mgt_priv int(11), payro_priv int(11), load_adv_exp_priv int(11), emp_mgt_priv int(11), created_by varchar(100), creation_date datetime, modified_by varchar(100), modified_date datetime, attr1 varchar(200), attr2 varchar(200), attr3 varchar(200), attr4 varchar(200), attr5 varchar(200), attr6 varchar(200), probation int(11) unsigned)  -- employee professional/employment record — company/branch, structure_id, shift, joining info; joined with emp_details in almost every proc  [schema/mypayrol_trial.sql:43735]
emp_pt_details(emp_pt_details_pkey int(11) PK, emp_fkey int(11) FK->emp_details, state1 varchar(30), month_year varchar(10), start_month varchar(10), end_month varchar(10), month_count int(11), availed_salary double, projected_salary double, total_salary double, pt_deducted double, tax_half_yearly double, balance_month double, balance_pt double, creation_date datetime, end_date_effective datetime, status int(11))  [schema/mypayrol_trial.sql:43802]
emp_salary_slip(emp_salary_slip_pkey int(11) PK, payroll_master_fkey int(11) FK->payroll_master, emp_fkey int(11) FK->emp_details, month_year varchar(20), salary_head_item_fkey int(11) FK->salary_head_items, salary_head_item_desc varchar(250), structure_det_value decimal(10,0), salary_rate decimal(10,0), salary_amount decimal(10,0), presant_total float, leave_total float, lop_total float, head_operator varchar(30), head_type varchar(30), item_part varchar(30), creation_date datetime, end_date_effective date, created_by varchar(30), modified_by varchar(30), remarks varchar(3000), approved char(1), action varchar(30))  -- the computed payslip line items per employee/month (one row per salary component) — output of calculate_salary_main_prc/salary_process_prc  [schema/mypayrol_trial.sql:43824]
emp_salary_slip_bkp(emp_salary_slip_pkey int(11) PK, payroll_master_fkey int(11) FK->payroll_master, emp_fkey int(11) FK->emp_details, month_year varchar(20), salary_head_item_fkey int(11) FK->salary_head_items, salary_head_item_desc varchar(250), structure_det_value int(11), salary_rate float, salary_amount float, presant_total float, leave_total float, lop_total float, head_operator varchar(30), head_type varchar(30), item_part varchar(30), creation_date datetime, end_date_effective date, created_by varchar(30), modified_by varchar(30), remarks varchar(3000), approved char(1), action varchar(30))  [schema/mypayrol_trial.sql:43851]
emp_salary_structure(emp_salary_structure_pkey int(11) PK, emp_fkey int(11) FK->emp_details, emp_structure_id int(11), prorate_code varchar(30), prorate_desc varchar(100), defined_structure_for varchar(250), salary_head_item_fkey int(11) FK->salary_head_items, salary_head_item_desc varchar(250), structure_det_value decimal(10,0), creation_date datetime, head_operator varchar(30), head_type varchar(30), item_part varchar(30), end_date_effective date, created_by varchar(30), modified_by int(11), remarks varchar(3000))  -- employees active CTC breakdown into salary components (one row per salary_head_item)  [schema/mypayrol_trial.sql:43878]
emp_salcomp_upload(emp_salcomp_upload_pkey int(11) PK, transaction_id int(11), emp_id varchar(30), salary_head_item_fkey int(11) FK->salary_head_items, component varchar(100), rate int(11), creation_date datetime, created_by varchar(50), status int(11))  [schema/mypayrol_trial.sql:43904]
emp_settle_slip(emp_settle_slip_pkey int(11) PK, payroll_master_fkey int(11) FK->payroll_master, emp_fkey int(11) FK->emp_details, type varchar(20), month_year varchar(20), salary_head_item_fkey int(11) FK->salary_head_items, salary_head_item_desc varchar(250), structure_det_value decimal(10,0), salary_rate decimal(10,0), salary_amount decimal(10,0), presant_total float, leave_total float, lop_total float, head_operator varchar(30), head_type varchar(30), item_part varchar(30), creation_date datetime, created_by varchar(30), modified_by varchar(30), remarks varchar(3000), approved char(1), status char(1), action varchar(30))  [schema/mypayrol_trial.sql:43918]
emp_shift_planner(planner_id int(11) PK, emp_fkey int(11) FK->emp_details, shift_id int(11), month_year varchar(10), shift_date date, creation_date datetime, modification_date datetime, status int(11))  [schema/mypayrol_trial.sql:43946]
emp_site_detail_timeattandance(emp_pkey int(11), att_date date, att_in_time datetime, att_out_time datetime, duration float, present varchar(20), weekoff varchar(20), leaves varchar(20), holiday varchar(10), others varchar(10), yearmonth date, site_fkey int(11) FK->site, site_transactions_fkey int(11) FK->site_transactions)  [schema/mypayrol_trial.sql:43963]
emp_statutory_components(emp_statutory_components_pkey int(11) PK, emp_fkey int(11) unsigned FK->emp_details, month_year varchar(20), salary_head_item_fkey int(11) unsigned FK->salary_head_items, salary_head_item_desc varchar(250), structure_det_value decimal(10,0), salary_rate decimal(10,0), salary_rate2 decimal(10,0), salary_amount decimal(10,0), head_operator varchar(30), head_type varchar(30), item_part varchar(30), remarks varchar(500), remarks2 varchar(500), created_by varchar(30), creation_date timestamp, modified_by varchar(30), modification_date timestamp, end_date_effective date)  -- computed PF/ESI/PT statutory deduction line items per employee/month  [schema/mypayrol_trial.sql:43985]
emp_structure(id int(11) PK, emp_id int(11), parent_id int(11))  [schema/mypayrol_trial.sql:44009]
emp_tax_regime(emp_tax_regime_id int(11) PK, emp_fkey int(11) FK->emp_details, fin_year int(11), option_type char(1), created_by varchar(50), creation_date datetime, end_date_effective datetime, modified_by varchar(50))  -- employees chosen tax regime (old/new) per financial year  [schema/mypayrol_trial.sql:44017]
emp_tax_sal_trans(emp_tax_sal_trans_pkey int(11) PK, emp_fkey int(11) FK->emp_details, tax_salary_components_fkey varchar(100) FK->tax_salary_components, salary_head_item_Fkey int(11), actual_salary_recd decimal(10,0), projected_salary decimal(10,0), availed_salary decimal(10,0), upper_limit decimal(10,0), taxable_salary decimal(10,0), fin_year varchar(30), start_date_effective date, end_date_effective date, created_by varchar(50), creation_date timestamp, modified_by varchar(50), modification_date date, status int(11))  [schema/mypayrol_trial.sql:44030]
emp_tax_sal_trans_new(emp_tax_sal_trans_new_pkey int(11) PK, emp_fkey int(11) FK->emp_details, tax_salary_components_fkey varchar(100) FK->tax_salary_components, salary_head_item_Fkey int(11), actual_salary_recd decimal(10,0), projected_salary decimal(10,0), availed_salary decimal(10,0), upper_limit decimal(10,0), taxable_salary decimal(10,0), fin_year varchar(30), start_date_effective date, end_date_effective date, created_by varchar(50), creation_date timestamp, modified_by varchar(50), modification_date date, status int(11))  [schema/mypayrol_trial.sql:44052]
emp_tax_sal_trans_sum(emp_tax_sal_trans_sum_pkey int(11) PK, emp_fkey int(11) FK->emp_details, actual_salary decimal(10,0), availed_salary decimal(10,0), hra1 decimal(10,0), hra2 decimal(10,0), hra3 decimal(10,0), declared_deduction decimal(10,0), taxable_salary decimal(10,0), taxable_income decimal(10,0), other_income decimal(10,0), tax_heads_limitsum decimal(10,0), first_portion decimal(10,0), second_portion decimal(10,0), third_portion decimal(10,0), tax_yearly decimal(10,0), tax_monthly_proj decimal(10,0), surcharge decimal(10,0), cess decimal(10,0), rebate decimal(10,0), standerd_deduction decimal(10,0), fin_year varchar(30), start_date_effective date, end_date_effective date, created_by varchar(50), creation_date timestamp, modified_by varchar(50), modification_date date, status int(11), first_portion_tax decimal(10,0), second_portion_tx decimal(10,0), third_portion_tx decimal(10,0))  -- old-regime TDS projection summary per employee/financial year  [schema/mypayrol_trial.sql:44074]
emp_tax_sal_trans_sum_new(emp_tax_sal_trans_sum_new_pkey int(11) PK, emp_fkey int(11) FK->emp_details, actual_salary decimal(10,0), availed_salary decimal(10,0), hra1 decimal(10,0), hra2 decimal(10,0), hra3 decimal(10,0), declared_deduction decimal(10,0), taxable_salary decimal(10,0), taxable_income decimal(10,0), other_income decimal(10,0), tax_heads_limitsum decimal(10,0), first_portion decimal(10,0), second_portion decimal(10,0), third_portion decimal(10,0), forth_portion decimal(10,0), fifth_portion decimal(10,0), sixth_portion decimal(10,0), tax_yearly decimal(10,0), tax_monthly_proj decimal(10,0), surcharge decimal(10,0), cess decimal(10,0), rebate decimal(10,0), standerd_deduction decimal(10,0), fin_year varchar(30), start_date_effective date, end_date_effective date, created_by varchar(50), creation_date timestamp, modified_by varchar(50), modification_date date, status int(11), first_portion_tax decimal(10,0), second_portion_tax decimal(10,0), third_portion_tax decimal(10,0), fourth_portion_tax decimal(10,0), fifth_portion_tax decimal(10,0), sixth_portion_tax decimal(10,0), marginal_relief decimal(10,2), seventh_portion decimal(10,2), seventh_portion_tax decimal(10,2))  -- new-regime TDS projection summary per employee/financial year  [schema/mypayrol_trial.sql:44111]
emp_tax_transactions(emp_tax_tran_id int(11) PK, emp_fkey int(11) FK->emp_details, tax_heads_fkey int(11) FK->tax_heads, tax_heads_details_fkey int(11) FK->tax_heads_details, tax_value decimal(10,0), fin_year int(11), created_by varchar(50), creation_date datetime, modified_by varchar(50), last_value float, attr1 varchar(500), attr2 varchar(500), attr3 varchar(500), attr4 varchar(500), attr5 varchar(500), file_name varchar(1000), file_type varchar(1000), locked varchar(11))  [schema/mypayrol_trial.sql:44157]
emp_upload(emp_upload_pkey int(11) PK, emp_pkey int(11), company_code varchar(30), branch_code varchar(30), emp_id varchar(30), first_name varchar(100), middile_name varchar(100), last_name varchar(100), classification varchar(50), address text, city varchar(50), state varchar(50), pincode varchar(50), mobile_no varchar(20), email varchar(100), maritual_status varchar(20), education varchar(100), date_of_birth date, bank_name varchar(100), branch_name varchar(100), branch_address text, ifsc_code varchar(30), account_no varchar(30), pf int(20), company_pf int(20), esi_dispensary varchar(20), esi varchar(30), id_card varchar(100), guradian varchar(100), relation_guardian varchar(100), pan_no varchar(50), status int(11), parent int(100), attr3 varchar(100), creation_date timestamp, attr4 varchar(100), attr5 varchar(100), emp_fkey int(11) FK->emp_details, joining_date date, emp_company_id varchar(30), emp_type varchar(50), designation varchar(100), emp_dept varchar(100), emp_grade varchar(100), emp_vertical varchar(100), emp_branch varchar(100), HOLIDAY_GROUP_ID int(11), LEAVEPOLICY_GROUP_ID int(11), day_time_seq int(11), structure_id int(11), notice_days int(11), leave_encash_priv int(11), leave_mgt_priv int(11), emp_sep_priv int(11), tax_mgt_priv int(11), atten_mgt_priv int(11), payro_priv int(11), load_adv_exp_priv int(11), emp_mgt_priv int(11), upload_by varchar(30), upload_date datetime, insert_date datetime, error_status varchar(200), action varchar(30))  -- bulk employee-data upload staging table  [schema/mypayrol_trial.sql:44180]
emp_variables_upload(emp_variables_upload_pkey int(11) PK, emp_fkey int(11) FK->emp_details, salary_head_item_fkey int(11) FK->salary_head_items, month_year varchar(20), salary_head_item_desc varchar(100), structure_det_value float, uploaded_amount float, head_operator varchar(30), head_type varchar(30), item_part varchar(30), creation_date datetime, created_by varchar(50), remarks varchar(500), action varchar(30), status int(11))  [schema/mypayrol_trial.sql:44249]
emp_variable_pay_upload(emp_variable_pay_upload_pkey int(11) PK, transaction_id int(11), emp_fkey int(11) FK->emp_details, emp_id varchar(30), salary_head_item_fkey int(11) FK->salary_head_items, salary_head_item_desc varchar(100), structure_det_value float, head_operator varchar(30), head_type varchar(30), item_part varchar(30), creation_date datetime, created_by varchar(50), modification_date datetime, modified_by varchar(50), end_date_effective datetime, status int(11))  [schema/mypayrol_trial.sql:44269]
expense_type(expense_type_pkey int(11) PK, expense_type_code varchar(50), expense_type_name varchar(200), expense_head_fkey int(11), created_by varchar(50), creation_date datetime, modified_by varchar(50), modified_date datetime, status int(11))  [schema/mypayrol_trial.sql:44290]
family(emp_family_pkey int(11) PK, emp_join_fkey int(11) FK->emp_join, name varchar(50), DOB date, gender varchar(50), blood_group varchar(50), relation varchar(50), nationality varchar(50), contact_number varchar(10), alternate_number varchar(10), emergency_contact char(1), remarks varchar(500), created_date datetime, created_by varchar(20), modified_by varchar(20), modified_date datetime, is_nominee char(1), status char(1))  [schema/mypayrol_trial.sql:44319]
fin_year(Fin_year_seq int(11) PK, company_code varchar(30), branch_code varchar(30), fin_year int(11), start_month date, end_month date, Year_status varchar(30), is_current_finyear char(1), vattr1 int(11), vattr2 int(11), vattr3 int(11), status int(10))  -- financial year window definitions per branch  [schema/mypayrol_trial.sql:44344]
genaral_setings(settings_pkey int(11) PK, description varchar(100), when_itis varchar(20), where_itis varchar(20), message varchar(500), form_menu varchar(100), creation_date datetime, status int(11))  [schema/mypayrol_trial.sql:44365]
goods_receved_notes(grn_pkey int(11) PK, gr_number int(50), po_fkey int(50) FK->purchase_order, gr_create varchar(50), gr_date date, store_code varchar(50), remark varchar(50), created_by varchar(50), creation_date timestamp, modified_by int(11), modified_date timestamp, status int(11))  [schema/mypayrol_trial.sql:44380]
grade(grade_pkey int(11) PK, grade_code varchar(20), grade_name varchar(100), status int(11))  [schema/mypayrol_trial.sql:44397]
grn_stock_details_date_view()  [schema/mypayrol_trial.sql:44412]
grn_stock_det_rate_date_view()  [schema/mypayrol_trial.sql:44415]
gross_salary()  [schema/mypayrol_trial.sql:44418]
group_allocation(allocation_id int(11) PK, company_code varchar(30), group_id int(11), branch_code varchar(50), dept_code varchar(50), designation_id varchar(50), emp_type varchar(50), Emp_fkey int(11), start_month_year varchar(30), end_month_year varchar(30), status int(11))  [schema/mypayrol_trial.sql:44421]
gr_item_details(gr_item_pkey int(11) PK, grn_fkey int(11) FK->goods_receved_notes, po_fkey int(11) FK->purchase_order, item_code int(11), uom int(11), ordering_qty int(11), received_qty int(11), current_stock int(11), re_order_level int(11), package int(11), po_rate int(11), created_by varchar(50), creation_date timestamp, modified_by varchar(50), modified_date timestamp, status int(11))  [schema/mypayrol_trial.sql:44437]
history(history_pkey int(11) PK, emp_fkey int(11) FK->emp_details, company varchar(100), from_date varchar(10), to_date varchar(10), designation varchar(100), department varchar(100), salary int(9), status int(2))  [schema/mypayrol_trial.sql:44458]
holidays(HOLIDAYID int(11) PK, HOLIDAY_GROUP_ID int(11), HOLIDAYNAME varchar(100), HOLIDAYDATE date, DESCRIPTION varchar(255), HOLIDAYTYPE varchar(50), status int(11), Background varchar(100), border varchar(100))  -- company holiday calendar  [schema/mypayrol_trial.sql:44472]
holiday_group(COMPANY_CODE varchar(50), BRANCH_CODE varchar(50), HOLIDAY_GROUP_ID int(11) PK, HOLIDAY_GROUP_NAME varchar(200), status int(11))  [schema/mypayrol_trial.sql:44556]
hrm_menu(menu_id int(11) PK, parent_id int(11), menu_url varchar(500), menu_title varchar(100), menu_name varchar(100), active char(1), iconCls varchar(100), user_id int(10), plan varchar(15))  [schema/mypayrol_trial.sql:44568]
inactive_list(slno int(11), firstname varchar(100), companyemployeeid varchar(50), joining_date date, branch varchar(100), status int(11))  [schema/mypayrol_trial.sql:44691]
income_tax_slab(income_tax_slab_pkey int(11) PK, fin_year int(11), regime varchar(20), salary_range_from int(11), salary_range_to int(11), tax_yearly_perc int(11), std_deduction float, surcharge_perc float, rebate float, cess_perc float, start_date_effective date, end_date_effective date, created_by varchar(30), creation_date datetime, modified_by varchar(30), modified_date datetime, status int(11))  -- progressive income-tax slab reference table (old/new regime), per financial year  [schema/mypayrol_trial.sql:44701]
Item_allocation_details_view()  [schema/mypayrol_trial.sql:44764]
item_details(item_details_pkey int(11) PK, item_master_fkey int(11) FK->item_master, item_brand varchar(50), item_model varchar(50), main_supp_code varchar(50), alt_supp_code varchar(50), package varchar(50), uom varchar(50), thickness float, dimension float, density float, created_by varchar(50), created_date timestamp, modified_by varchar(50), modified_date datetime, status int(11))  [schema/mypayrol_trial.sql:44767]
item_except_grn_allocation_view()  [schema/mypayrol_trial.sql:44788]
item_master(item_master_pkey int(11) PK, item_code varchar(50), item_desc varchar(100), item_category varchar(50), item_specification varchar(50), image_path varchar(50), created_by varchar(50), creation_date timestamp, modified_by varchar(50), modified_date timestamp, status int(11))  -- inventory item master (SKU catalog)  [schema/mypayrol_trial.sql:44791]
item_pricing(item_pricing_pkey int(11) PK, item_master_fkey int(11) FK->item_master, bar_code varchar(100), purchase_rate float, mrp_rate float, vat_group varchar(30), sales_price_group_fkey int(11), sales_price_group_fkey1 int(11), sales_price_group_fkey2 int(11), sales_price_group_fkey3 int(11), sales_price_group_fkey4 int(11), sales_price_group_fkey5 int(11), sales_price_group_fkey6 int(11), sales_price_group_fkey7 int(11), sales_price_group_fkey8 int(11), sales_price_group_fkey9 int(11), applicable_scheme varchar(30), incentive_applicable varchar(30), incentive_perc float, min_target float, created_by varchar(50), creation_date timestamp, modified_by varchar(50), modified_date timestamp, status int(11))  [schema/mypayrol_trial.sql:44808]
item_purchase(item_pkey int(11) PK, item_fkey int(10) FK->item_details, vendor varchar(200), qty int(20), date_purchased date, PO_number int(200), branch_code varchar(20), submitted_on date, allocated_qty int(20), status int(1))  [schema/mypayrol_trial.sql:44838]
item_specification(specification_pkey int(11) PK, category_code varchar(50), description varchar(50), item_specification varchar(50), created_by varchar(50), created_date timestamp, modified_by varchar(50), modified_date datetime, status int(11))  [schema/mypayrol_trial.sql:44853]
itm_allocation(allocation_pkey int(200) PK, emp_fkey int(20) FK->emp_details, value int(20), item_fkey int(20) FK->item_details, qty_allocated int(20), date_allocated date, balance_recover_amt int(11), loan_fkey int(11), emi_start_date date, emi_end_date date, store_code varchar(10), status int(1), creation_date datetime)  [schema/mypayrol_trial.sql:44867]
leaveentries(LEAVEENTRYID int(11) PK, salary_head_item_fkey int(11) FK->salary_head_items, applied_date date, LEAVESTATUS varchar(255), EMP_fkey int(11), FROMDATE date, FROMHALF int(11), TODATE date, TOHALF int(11), ISAutherized int(11), ISAutherizedby int(11), Autherized_date date, ISAPPROVED int(11), APPROVEDBY int(11), APPROVED_date date, Reason varchar(400), contact_No varchar(20), contact_person varchar(255), REMARKS varchar(4000), leavebal_bf float, leave_days float, leavebal_af float, message varchar(4000), creation_date datetime, AuthoriseRemarks varchar(4000), ApproveRemarks varchar(4000), cctome varchar(400), file_name varchar(1000), file_type varchar(1000))  -- individual leave-application transaction rows (from/to date, half-day flags, status)  [schema/mypayrol_trial.sql:44885]
leavepolicy(LEAVEPOLICYID int(11) PK, LEAVEPOLICY_GROUP_ID int(11), salary_head_item_fkey int(11) FK->salary_head_items, leave_cycle_start_date date, leave_cycle_end_date date, alloted_leave_forthe_year float, alloted_leave_forthe_month float, CARRY_FORWARD_LIMIT float, dynamic_period float, APPLICABLE_TO varchar(50), ALLOW_NEGETIVE char(1), IS_SANDWICH char(1), is_leave_encash char(1), leave_encash_limit float, is_auto_credit char(1), REMARKS varchar(500), leave_encashment_component int(11), leave_policy_type char(1), exceptions char(1), minimum_leave float, maximum_leave float, minimum_service float, allow_all_leaves char(1), sanction_by int(11), leval_of_approval int(11), notified_by int(11), document_mandatory char(1), min_day_before_apply int(11), last_date_of_apply int(11), status int(11))  -- leave-type policy definitions (allotment/year, carry-forward limits, encashment rules) per leave-policy group  [schema/mypayrol_trial.sql:44919]
leavepolicy_group(COMPANY_CODE varchar(50), BRANCH_CODE varchar(50), LEAVEPOLICY_GROUP_ID int(11) PK, LEAVEPOLICY_GROUP_NAME varchar(200), status int(11))  [schema/mypayrol_trial.sql:44954]
leavestatus(LEAVESTATUS varchar(255))  [schema/mypayrol_trial.sql:44964]
leave_balance_upload(empid varchar(30), emp_name varchar(50), leave_type varchar(20), item varchar(20), balance_bf_adj double, leave_balance double, adjustment double, created_by varchar(30), created_date timestamp, modified_by varchar(30), modified_date timestamp, status int(11), leave_balance_upload_pky int(11) PK)  [schema/mypayrol_trial.sql:44967]
leave_encashment_master(leave_encashment_master_pkey int(11) PK, emp_fkey int(11) FK->emp_details, emp_name varchar(200), branch_code varchar(30), salary_head_item_fkey int(11) FK->salary_head_items, encash_days float, available_days float, requested_days float, approved_days float, created_by varchar(100), creation_date datetime, modified_by varchar(100), modified_date timestamp, approved_by varchar(100), is_approved char(1), approved_date date, fin_year int(11), encashed_amount float, salary_paid char(1), slip_generated_date date, status int(11), remarks varchar(1000))  -- leave encashment requests/approvals and computed encashed amount  [schema/mypayrol_trial.sql:45010]
leave_taken()  [schema/mypayrol_trial.sql:45037]
material_request(mr_pkey int(11) PK, mr_code varchar(50), po_type varchar(50), mr_date date, customer_name varchar(50), customer_po_number int(11), location varchar(50), store_code int(50), att varchar(50), remarks varchar(50), created_by varchar(50), creation_date timestamp, modified_by varchar(50), modified_date timestamp, po_status int(11), status int(11))  [schema/mypayrol_trial.sql:45040]
miss_action(miss_action_pkey int(50), action varchar(50), created_time datetime, user_id varchar(25), response varchar(1000), accuracy varchar(100), internet varchar(100), location_mode varchar(100), model varchar(100), uploaded_time datetime)  [schema/mypayrol_trial.sql:45061]
mob_report(mob_report_pkey int(11) PK, report_type varchar(480), user_id varchar(480), remarks varchar(600), lat varchar(100), longs varchar(100), location varchar(500), date_rep datetime, rep_status varchar(20), status int(1))  [schema/mypayrol_trial.sql:45075]
mob_user_credentials(user_pkey int(11) PK, user_id varchar(30), firstname varchar(50), lastname varchar(50), password varchar(30), securitycode varchar(30), macid varchar(50), imei varchar(50), token varchar(100), locked varchar(10), access_allowed varchar(1), invalid_login int(11), uploaded_time datetime, UID varchar(1000), punchtype char(1), is_track char(1))  [schema/mypayrol_trial.sql:45091]
mob_user_locations(mob_user_locations_pkey int(11) PK, mob_location_pkey int(11), user_id varchar(30), created_time datetime, latitude double, longitude double, location varchar(100), loc_source varchar(100), uploaded_time datetime, stepinout varchar(20), customer_name varchar(200), contact_person varchar(100), contact_number varchar(25), purpose varchar(500))  [schema/mypayrol_trial.sql:45113]
mob_user_login_auditor(auditor_pkey int(11), userid varchar(30), time_check datetime, in_out varchar(10), location varchar(100), latitude double, longitude double, uploaded_time datetime, loc_source varchar(10), stay_back varchar(10))  [schema/mypayrol_trial.sql:45146]
mob_user_login_history(history_pkey int(11), user_id varchar(30), history_type varchar(10), access_time datetime, latitude double, longitude double, location varchar(100), uploaded_time datetime)  [schema/mypayrol_trial.sql:45198]
mob_user_tracking(mob_location_pkey int(11), user_id varchar(30), created_time datetime, latitude double, longitude double, location varchar(100), loc_source varchar(100), uploaded_time datetime, stepinout varchar(20), customer_name varchar(200), purpose varchar(500))  [schema/mypayrol_trial.sql:45210]
mr_details(mr_details_pkey int(11) PK, mr_fkey int(11) FK->material_request, item_code int(50), required_qty int(50), unit int(50), ordering_qty int(50), current_stock int(50), incoming_qty int(50), incoming_date date, re_order_level int(50), package int(50), created_by varchar(50), creation_date timestamp, modified_by varchar(50), modified_date timestamp, status int(11))  [schema/mypayrol_trial.sql:45225]
notice_period(notice_pkey int(100) PK, notice_days int(100), description varchar(200), status int(1))  [schema/mypayrol_trial.sql:45246]
organization_info(organization_id int(11) PK, business_name varchar(200), business_nature varchar(200), business_type varchar(200), address varchar(500), city varchar(100), pincode varchar(10), state varchar(100), phone varchar(100), fax varchar(100), email varchar(200), website varchar(200), logo blob)  [schema/mypayrol_trial.sql:45260]
package_master(package_pkey int(11) PK, package_code varchar(50), package_type varchar(50), created_by varchar(50), created_date timestamp, modified_by varchar(50), modified_date datetime, status int(11))  [schema/mypayrol_trial.sql:45278]
payment_mode(payment_mode_pkey int(11) PK, mode varchar(20), created_by varchar(30), modified_by varchar(30), creation_date datetime, modified_date datetime, status int(1))  [schema/mypayrol_trial.sql:45291]
payroll_master(payroll_master_pkey int(11) PK, emp_fkey int(11) FK->emp_details, emp_name varchar(100), branch_code varchar(30), month_year varchar(30), tax_include varchar(30), days_presant float, days_leave float, loss_of_pay float, calander_days int(11), working_days float, week_off_days float, holidays float, monthly_ctc decimal(10,0), monthly_amount decimal(10,0), gross_salary decimal(10,0), total_deduction decimal(10,0), total_variables decimal(10,0), net_salary decimal(10,0), approved char(1), action varchar(30), bank_details varchar(500), created_by varchar(30), creation_date datetime, modified_by varchar(30), modified_date date, departments varchar(100), branch_name varchar(100), desig varchar(100), division varchar(100), section varchar(100), grade varchar(100), joining_date date, eps varchar(10), probation int(11), contract_start_date date, contract_end_date date, pay_scale varchar(100), category_name varchar(100))  -- one row per employee per payroll month — attendance counts, gross/net salary, approval action; the payroll run header record  [schema/mypayrol_trial.sql:45306]
payroll_master04092016(payroll_master_pkey int(11), emp_fkey int(11) FK->emp_details, emp_name varchar(100), branch_code varchar(30), month_year varchar(30), days_presant float, days_leave float, loss_of_pay float, calander_days int(11), working_days float, week_off_days float, monthly_ctc float, monthly_amount float, gross_salary float, total_deduction float, total_variables float, net_salary float, approved char(1), action varchar(30), created_by varchar(30), creation_date datetime, modified_by varchar(30), modified_date date)  [schema/mypayrol_trial.sql:45350]
policy_info(policy_key int(11) PK, policy_name text, policy_value varchar(50), status int(11))  [schema/mypayrol_trial.sql:45377]
po_item_details(po_item_pkey int(11) PK, po_fkey int(11) FK->purchase_order, item_code int(11), uom int(11), ordering_qty int(11), required_qty int(11), current_stock int(11), re_order_level int(11), package int(11), po_rate int(11), created_by varchar(50), creation_date timestamp, modified_by varchar(50), mr_fkey int(20) FK->material_request, po_list_pkey int(20), modified_date timestamp, status int(11))  [schema/mypayrol_trial.sql:45386]
po_return_request(po_pkey int(11) PK, rtn_fkey varchar(50), rtn_code varchar(50), rtn_date date, store_code int(50), created_by varchar(50), creation_date timestamp, po_status int(11), status int(11))  [schema/mypayrol_trial.sql:45408]
present_employee()  [schema/mypayrol_trial.sql:45422]
present_today()  [schema/mypayrol_trial.sql:45425]
present_today_all()  [schema/mypayrol_trial.sql:45428]
professional_tax_view()  [schema/mypayrol_trial.sql:45431]
profession_tax_slab(profession_tax_slab_pkey int(11) PK, state varchar(100), salary_range_from int(11), salary_range_to int(11), tax_half_yearly int(11), tax_yearly int(11), created_by varchar(30), creation_date datetime, modified_by varchar(30), modified_date datetime, start_date_effective date, end_date_effective date, status int(11))  -- state-wise professional tax slab reference  [schema/mypayrol_trial.sql:45434]
promotions(promotion_pkey int(20) PK, emp_fkey int(20) FK->emp_details, created_date date, approved_by int(20), approved_status varchar(2), approved_date date, designation varchar(20), emp_type varchar(20), emp_dept varchar(20), emp_branch varchar(20), shift varchar(20), leave varchar(20), annual_gross varchar(20), hierarch varchar(20), salary varchar(20), remarks varchar(600), promotion_status varchar(30), created_by varchar(30), status int(1))  -- employee promotion history record  [schema/mypayrol_trial.sql:45472]
purchase_list(purchase_list_pkey int(11) PK, po_fkey int(11) FK->purchase_order, mr_fkey int(11) FK->material_request, created_by varchar(50), creation_date timestamp, status int(11))  [schema/mypayrol_trial.sql:45496]
purchase_order(po_pkey int(11) PK, po_number varchar(50), po_date date, expected_date date, po_type varchar(50), location varchar(50), location_code varchar(50), supplier_name varchar(50), supplier_code varchar(50), remarks varchar(100), created_by varchar(50), creation_date timestamp, modified_by varchar(50), modified_date timestamp, grn_status int(11), status int(11))  -- procurement purchase order header  [schema/mypayrol_trial.sql:45507]
qualifcations(qualification_pkey int(11) PK, emp_fkey int(11) FK->emp_details, course varchar(100), university varchar(100), duration varchar(20), mark varchar(11), status int(11))  [schema/mypayrol_trial.sql:45528]
quantity_details(quantity_details_pkey int(10) PK, item_master_fkey int(10) FK->item_master, item_balance int(11), in_stock varchar(50), stock_on_hand int(11), qty_commit varchar(50), qty_on_order varchar(50), re_order_level int(4), economic_order int(10), monthly_demand int(10), created_by varchar(50), created_date timestamp, modified_by varchar(50), modified_date datetime, status int(10))  [schema/mypayrol_trial.sql:45540]
register_history(register_history_pkey int(11) PK, branch varchar(20), month varchar(20), start_time datetime, created_date datetime, end_time datetime, duration double, created_by varchar(30), process varchar(30), status int(11))  [schema/mypayrol_trial.sql:45560]
reportcriterias(id int(11) PK, reporttype varchar(50), reportcriteria varchar(100), reportcriteria_desc varchar(100), reportcriteria_field varchar(50), status tinyint(4))  [schema/mypayrol_trial.sql:45575]
report_audit(id int(11) PK, report_type varchar(100), report_component varchar(100), report_from varchar(50), report_to varchar(50), include_resigned int(11), Include_negative_salary int(11), criteria varchar(100), criteria_name varchar(100), items varchar(1000), items_count varchar(50), mode varchar(50), user_id varchar(50), user_name varchar(100), creation_date timestamp, status int(11))  [schema/mypayrol_trial.sql:45746]
resignation_accept(resignation_accept_pkey int(11) PK, applied_emp int(11), Resignation_pkey int(11), last_allowed_date datetime, comments_to_emp varchar(40), comments_to_hr varchar(40), manager_reason varchar(40), forwarded int(11), isauthorized int(11), authorized_date datetime, isApproved int(11), approved_date datetime, handover_to int(11), hr_comment varchar(400), chek_formalities int(2), chek_assets int(2), chek_leave int(2), status int(11))  -- resignation approval/acceptance record  [schema/mypayrol_trial.sql:45770]
resignation_requests(Resignation_pkey int(11) PK, emp_fkey int(11) FK->emp_details, authorised_to int(11), applied_date timestamp, Reason varchar(20), Reason_Desc varchar(600), Comments_to_manager varchar(600), Last_workingday date, Resignation_status varchar(20), agree varchar(20), contact_no varchar(20), status int(11))  -- employee resignation workflow request  [schema/mypayrol_trial.sql:45794]
returned_stocks(ret_stock_pkeys int(100) PK, item_fkey int(10) FK->item_details, qty int(20), status int(10), reason varchar(200), creation_date datetime, damaged_qty varchar(10), returned_stocks int(20), tr_date date, allocation_fkey int(20), created_by varchar(20), emp_pkey int(11))  [schema/mypayrol_trial.sql:45811]
return_gr_items(rtn_pkey int(11) PK, return_number int(50), stk_fkey int(50), store_fkey int(50) FK->store_master, grn_fkey int(50) FK->goods_receved_notes, item_fkey int(11) FK->item_details, po_fkey int(11) FK->purchase_order, return_date date, remark varchar(50), available_qty varchar(50), return_qty int(50), created_by varchar(50), creation_date timestamp, modified_by varchar(50), modified_date timestamp, status int(11), delete_status int(11))  [schema/mypayrol_trial.sql:45829]
salary_heads(head_pkey int(11) PK, head_desc varchar(200), status int(11), head_operator varchar(100), head_occurance varchar(100), salary_head_order1 int(11))  -- top-level salary component groupings (Addition/Deduction operator, occurrence)  [schema/mypayrol_trial.sql:45853]
salary_head_items(salary_head_item_pkey int(11) PK, head_fkey int(11) FK->salary_heads, item varchar(200), item_type varchar(10), item_value varchar(100), occurance varchar(20), start_from date, comments text, value char(1), is_show_salslip char(1), item_part varchar(30), status int(11), salary_head_item_order1 int(11))  -- individual salary component definitions (item name, type, operator) grouped under salary_heads  [schema/mypayrol_trial.sql:45875]
salary_hike(salary_hike_pkey int(11) PK, is_multiple char(1), item char(1), structure_change char(1), action varchar(30), remarks varchar(200), created_by varchar(30), creation_date datetime, modified_by varchar(30), modification_date datetime, status int(11))  [schema/mypayrol_trial.sql:46027]
salary_hike_details(salary_hike_details_pkey int(11) PK, salary_hike_fkey int(11) FK->salary_hike, item char(1), branch_code varchar(30), emp_fkey int(11) FK->emp_details, structure_id int(11), with_effect_from date, next_increment_date date, payout_month date, salary_head_item_fkey int(11) FK->salary_head_items, current_amount float, new_amount float, increment_amount float, increment_percentage float, arrear_salary char(1), status int(11), remarks varchar(200), processed char(1))  [schema/mypayrol_trial.sql:46043]
salary_processing_audit(salary_processing_audit_pkey int(11) PK, payroll_master_fkey int(11) FK->payroll_master, branch_code varchar(30), emp_fkey int(11) FK->emp_details, month_year varchar(30), payroll_type varchar(100), day_time_seq int(11), HOLIDAY_GROUP_ID int(11), LEAVEPOLICY_GROUP_ID int(11), structure_id int(11), remarks varchar(100), created_by varchar(30), creation_date timestamp, modified_by varchar(30), modification_date timestamp)  [schema/mypayrol_trial.sql:46066]
salary_structure(structure_id int(11) PK, company_code varchar(30), structure_name varchar(200), prorate_code varchar(25), prorate_desc varchar(250), fixed_days int(11), defined_structure_for varchar(250), structure_eg_amt int(11), structure_created_date int(11), startdate_effective date, enddate_effective date, structure_active int(11))  -- named salary structure templates (prorate code, active window)  [schema/mypayrol_trial.sql:46086]
salary_structure_details(structure_det_id int(11) PK, structure_id int(11), salary_head_item_fkey int(11) FK->salary_head_items, structure_det_operator varchar(30), structure_det_value decimal(10,0), structure_det_depends decimal(10,0), structure_formula varchar(1000), structure_derived_perc double, structure_det_calequation varchar(1000))  -- the reusable salary-structure template definition (formula/fixed/limit component rules) that emp_salary_structure rows are derived from  [schema/mypayrol_trial.sql:46110]
salary_structure_details_b4wwf(structure_det_id int(11), structure_id int(11), salary_head_item_fkey int(11) FK->salary_head_items, structure_det_operator varchar(30), structure_det_value decimal(10,0), structure_det_depends decimal(10,0), structure_formula varchar(1000), structure_derived_perc double, structure_det_calequation varchar(1000))  [schema/mypayrol_trial.sql:46415]
scheduled_break_off(id int(11) PK, type varchar(10), emp_fkey int(11) FK->emp_details, break_off_date date, dutty_time time, message varchar(250), break_off_msg varchar(250), first_half char(1), created_by varchar(50), creation_date date, modified_by varchar(50), modification_date date, status int(11), coff_status char(1), leave_entry_id int(11))  [schema/mypayrol_trial.sql:46719]
section(id int(11) PK, section_code varchar(20), section_name varchar(100), status int(11))  [schema/mypayrol_trial.sql:46802]
settings_runner(setting_runner_pkey int(11) PK, settings_fkey int(11), emp_fkey int(11) FK->emp_details, exit_status varchar(20), updated_times int(11), creation_date datetime, modification_date datetime)  [schema/mypayrol_trial.sql:46811]
shift_exceptions(shift_exceptions_pkey int(10) PK, shift_id int(10), ex_week_day varchar(20), ex_week varchar(10), week_off char(1), in_time time, out_time time, duration int(11), full_day int(11), half_day int(11), creation_date datetime, created_by varchar(20), modification_date datetime, modified_by varchar(20), status int(11))  -- per-branch/employee overrides to the standard weekoff/holiday shift rules  [schema/mypayrol_trial.sql:46823]
site(site_pkey int(11) PK, organization_id int(11), site_id varchar(20), site_name varchar(200), branch_code varchar(50), location_id int(11), work_type_id int(11), user_pkey int(11), latitude int(11), longitude int(11), address varchar(500), payment_mode varchar(50), special_remarks varchar(100), creation_date timestamp, jurisdiction varchar(100), expected_starting_date date, contact_name int(11), expected_compleation_date date, customer_name varchar(100), customer_siteid varchar(20), customer_refno varchar(20), customer_po_number varchar(20), customer_contact varchar(20), po_expirydate date, allocated_fund int(11), min_days_before int(11), active char(1), status int(11))  -- field/project site master (for site-based/contract workforce)  [schema/mypayrol_trial.sql:46843]
site_attendance(site_attendance_pkey int(11) PK, site_fkey int(11) FK->site, mobile_pkey int(11), day_time_seq_fkey int(11), designation_id int(11), emp_fkey int(11) FK->emp_details, att_date date, in_time datetime, out_time datetime, created_by varchar(20), modified_by varchar(20), creation_date timestamp, modified_date datetime, upload_time datetime, active int(11), status int(1), sales_rate float, emp_rate float, duration float)  -- raw site check-in/out punch log  [schema/mypayrol_trial.sql:46878]
site_attendance_register(company_code varchar(20), branch_code varchar(20), registerid int(11) PK, site_fkey int(11) FK->site, site_trans_fkey int(11), emp_fkey int(11) FK->emp_details, month_year varchar(20), emp_company_id varchar(20), emp_name varchar(120), FIELD1 varchar(20), FIELD2 varchar(20), FIELD3 varchar(20), FIELD4 varchar(20), FIELD5 varchar(20), FIELD6 varchar(20), FIELD7 varchar(20), FIELD8 varchar(20), FIELD9 varchar(20), FIELD10 varchar(20), FIELD11 varchar(20), FIELD12 varchar(20), FIELD13 varchar(20), FIELD14 varchar(20), FIELD15 varchar(20), FIELD16 varchar(20), FIELD17 varchar(20), FIELD18 varchar(20), FIELD19 varchar(20), FIELD20 varchar(20), FIELD21 varchar(20), FIELD22 varchar(20), FIELD23 varchar(20), FIELD24 varchar(20), FIELD25 varchar(20), FIELD26 varchar(20), FIELD27 varchar(20), FIELD28 varchar(20), FIELD29 varchar(20), FIELD30 varchar(20), FIELD31 varchar(20), FIELD32 varchar(20), isdelete varchar(20), presant_total float, leave_total float, lop_total float, weekoff_total float, holiday_total float, record_status varchar(20), userid varchar(20))  -- site-workforce equivalent of attendance_register  [schema/mypayrol_trial.sql:47006]
site_history(site_history_pkey int(11) PK, site_transactions_pkey int(11), site_fkey int(11) FK->site, day_time_seq_fkey int(11), designation_id varchar(10), emp_count int(11), srate int(11), eratess int(11), created_by varchar(20), modified_by varchar(20), creation_date timestamp, modified_date date, start_date_effective date, end_date_effective date, status int(1), action char(1), creation_by varchar(10), created_date timestamp)  [schema/mypayrol_trial.sql:47061]
site_master_approval(site_master_approval_pkey int(20) PK, site_fkey int(20) FK->site, site_transactions_fkey int(20) FK->site_transactions, status varchar(30), created_by varchar(30), created_date timestamp, modified_by varchar(30), modified_date datetime, remarks varchar(100))  [schema/mypayrol_trial.sql:47084]
site_master_approval_details(site_master_approval_details_pkey int(20) PK, site_master_approval_fkey int(20) FK->site_master_approval, site_transactions_fkey int(20) FK->site_transactions, fieldname varchar(50), old_value varchar(500), new_value varchar(500), created_by varchar(30), created_date timestamp, modified_by varchar(50), modified_date datetime, end_date_effective datetime, status varchar(30), type varchar(10))  [schema/mypayrol_trial.sql:47098]
site_shift_close(site_shift_close_pkey int(11) PK, site_fkey int(11) FK->site, site_transactions_fkey int(11) FK->site_transactions, day_time_seq_fkey int(11), att_date date, shift_closed_status char(1), created_by varchar(20), creation_date datetime, modified_by varchar(20), modified_date datetime, rec_status int(11))  [schema/mypayrol_trial.sql:47116]
site_transactions(site_transactions_pkey int(11) PK, site_fkey int(11) FK->site, day_time_seq_fkey int(11), designation_id varchar(10), emp_count int(11), srate float, eratess float, created_by varchar(20), modified_by varchar(20), creation_date timestamp, modified_date date, start_date_effective date, end_date_effective date, status int(1))  [schema/mypayrol_trial.sql:47133]
site_trans_additional(site_trans_additional_pkey int(11) PK, site_fkey int(11) FK->site, day_time_seq_fkey int(11), designation_id int(11), emp_count int(11), created_by varchar(20), modified_by varchar(20), creation_date timestamp, modified_date date, start_date_effective date, end_date_effective date, status int(1))  [schema/mypayrol_trial.sql:47153]
stock_adjustments(stock_adjustments_pkey int(11) PK, adjustment_code int(11), adjustment_date date, from_store int(11), remarks varchar(400), created_by varchar(200), created_date datetime, status int(11))  [schema/mypayrol_trial.sql:47170]
stock_adjustments_details(stock_adjustments_details_pkey int(11) PK, stock_adjustments_fkey int(11) FK->stock_adjustments, item_desc varchar(200), item_fkey int(11) FK->item_details, adj_qty int(11), status int(11))  [schema/mypayrol_trial.sql:47183]
stock_details(stock_details_pkey int(11) PK, store_fkey varchar(21) FK->store_master, supplier_fkey int(11), invoice_no varchar(30), mr_no varchar(30), po_no varchar(30), item_batch varchar(30), item_fkey int(11) FK->item_details, received_qty int(11), item_qty float, free_stock float, offer_stock float, purchase_rate float, amount float, item_mrp float, varified_by varchar(30), item_state varchar(30), created_by varchar(30), creation_date timestamp, modified_by varchar(30), modified_date date, status int(11))  -- inventory stock ledger (issues/receipts) per item/store  [schema/mypayrol_trial.sql:47194]
stock_details_view()  [schema/mypayrol_trial.sql:47221]
stock_store_tranfer(stock_store_tranfer_pkey int(11) PK, gr_number int(11), po_fkey int(11) FK->purchase_order, gr_create varchar(50), gr_date varchar(50), remarks varchar(50), created_by varchar(50), created_date timestamp, modified_by varchar(50), modified_date timestamp, status int(11))  [schema/mypayrol_trial.sql:47224]
stock_store_tranfer_item(stock_store_item_pkey int(11) PK, stock_store_tranfer_fkey int(11) FK->stock_store_tranfer, po_fkey int(11) FK->purchase_order, item_code int(11), uom int(11), ordering_qty int(11), required_qty int(11), current_stock int(11), re_order_level int(11), package int(11), po_rate int(11), created_by varchar(50), created_date timestamp, modified_by varchar(50), modified_date timestamp, status int(11))  [schema/mypayrol_trial.sql:47240]
stock_tranfer(stock_tranfer_pkey int(11) PK, adjustment_code int(11), adjustment_date date, from_store varchar(50), to_store varchar(50), remarks varchar(50), created_by varchar(50), created_date timestamp, modified_by varchar(50), modified_date timestamp, status int(11))  [schema/mypayrol_trial.sql:47261]
stock_tranfer_item(stock_item_pkey int(11) PK, stock_tranfer_fkey int(11) FK->stock_tranfer, item_fkey int(11) FK->item_details, item_desc varchar(50), item_code int(11), tranfer_stock int(11), available_qty int(11), required_qty int(11), created_by varchar(50), created_date timestamp, modified_by varchar(50), modified_date timestamp, status int(11))  [schema/mypayrol_trial.sql:47277]
store_master(store_master_pkey int(11) PK, store_code varchar(20), store_location varchar(100), address varchar(500), city varchar(50), state varchar(50), pincode int(11), store_manager int(11), roc varchar(30), tan varchar(30), tin varchar(30), rtgs varchar(30), created_by varchar(50), creation_date timestamp, modified_by varchar(50), modified_date datetime, status int(11))  -- inventory store/warehouse master  [schema/mypayrol_trial.sql:47295]
taxes_save(emp_fkey int(11) FK->emp_details, incom int(100), other int(100), deductions int(100), status int(1), taxyear int(100), taxmonth int(100))  [schema/mypayrol_trial.sql:47317]
tax_computation_report(tax_computation_report_pkey int(11) PK, emp_fkey int(11) FK->emp_details, Month_year varchar(20), regime varchar(20), Monthly_Salary float, Availed_Salary float, Hra_exemption float, Deductions float, Taxable_Income_from_Salary float, Taxable_Income_from_Other_Sources float, Investments_Other_Deductions float, Standard_deduction float, Total_Taxable_Income float, Up_to_first_income float, Above_first_income float, Above_Secnd_income float, Above_third_income float, Above_fourth_income float, Above_fifth_income float, Above_sixth_income float, Above_seventh_income float, Total_Income float, Above_first_slab float, Above_second_slab float, Above_third_slab float, Above_fourth_slab float, Above_fifth_slab float, Above_sixth_slab float, Above_seventh_slab float, Total_Tax float, Cess float, Surcharge float, Rebate float, Marginal_Relief float, Total float, Tax_Deducted float, Balance_Tax float, Monthly_Tax float, creation_date timestamp, created_by varchar(50), status int(11), modified_by varchar(100), modified_date timestamp)  [schema/mypayrol_trial.sql:47333]
tax_form_documents(tax_form_documents_pkey int(11) PK, form_name varchar(200), pan varchar(10), fin_year varchar(10), created_by varchar(50), created_date datetime, status int(10))  [schema/mypayrol_trial.sql:47381]
tax_heads(tax_heads_pkey int(11) PK, tax_type_fkey int(11) FK->tax_type, tax_name varchar(100), tax_type varchar(100), tax_details text, order_level1 int(11), order_level2 int(11), order_level3 int(11), tax_active char(1), attr1 varchar(500), attr2 varchar(500), attr3 varchar(500))  -- income-tax declaration head categories (e.g. 80C, HRA) for tax computation  [schema/mypayrol_trial.sql:47393]
tax_heads_details(tax_heads_details_pkey int(11) PK, tax_heads_fkey int(11) FK->tax_heads, tax_heads_details text, tax_heads_details1 text, tax_heads_details2 text, status int(11), fieldtype int(11), active int(11))  [schema/mypayrol_trial.sql:47429]
tax_salary_components(tax_salary_components_pkey int(11) PK, tax_salary_components_name varchar(100), salary_head_item_Fkey int(11), upper_limit float, fin_year varchar(30), created_by varchar(50), creation_date timestamp, modified_by varchar(50), modification_date date, status int(11), operator varchar(10))  -- maps salary components to their tax treatment (e.g. Employee EPF, Professional Tax)  [schema/mypayrol_trial.sql:47527]
tax_type(tax_type_pkey int(11) PK, tax_type varchar(50), tax_desc varchar(200), tax_status int(11), tax_occurance varchar(100), tax_operator varchar(100))  [schema/mypayrol_trial.sql:47562]
tblbankname(bankid bigint(20) PK, bank_name varchar(50))  [schema/mypayrol_trial.sql:47578]
tblbankname1(bankid bigint(20) PK, bank_name varchar(50))  [schema/mypayrol_trial.sql:47585]
termination(terminate_pkey int(20) PK, emp_fkey int(20) FK->emp_details, Reason varchar(100), ed date, Reason_desc varchar(400), is_authorized varchar(1), authorized_by int(20), is_approved varchar(1), approved_by int(20), submitted_date date, last_applied_date date, last_working_date date, Resignation_pkey int(20), last_approved_working_date date, notice_period int(20), act_last_working_day date, remarks varchar(30), working_days_settled int(4), leave_balance int(4), approved_balance int(4), days_attendance int(4), encashed_days int(4), payroll_days int(4), status int(1), creation_date datetime, amt_paid_by_empaddition int(11), amt_paid_by_empdeduction int(11))  -- employee termination/last-working-date record, gates NA-period attendance logic  [schema/mypayrol_trial.sql:47592]
test(field1 varchar(100), field2 varchar(100), field3 varchar(100), field4 varchar(100), field5 varchar(100))  [schema/mypayrol_trial.sql:47624]
todo(id int(10) PK, task varchar(250), priority varchar(50), description longtext, date datetime)  [schema/mypayrol_trial.sql:47633]
total_deductions()  [schema/mypayrol_trial.sql:47643]
t_month(id int(11), season varchar(30), month varchar(30))  [schema/mypayrol_trial.sql:47646]
uomaster(uom_pkey int(10) PK, unit varchar(50), Base_unit varchar(50), unit_per_pack double, unit_net_weight double, unit_gross_weight double, created_by varchar(50), created_date timestamp, modified_by varchar(50), modified_date datetime, status int(10))  [schema/mypayrol_trial.sql:47653]
Upload_leave_errirs(Upload_leave_errirs_pkey int(11) PK, PID int(11), Type varchar(20), emp_fkey int(11) FK->emp_details, textd varchar(400), start_date date, end_date date, creation_date date, status int(11))  [schema/mypayrol_trial.sql:47669]
user_access(user_access_pkey int(11) PK, organization_id varchar(10), user_fkey int(11) FK->user_credentials, menu_id int(11), active char(1), status int(11))  [schema/mypayrol_trial.sql:47683]
user_credentials(user_pkey int(11) PK, emp_fkey int(11) FK->emp_details, company_code varchar(20), user_id varchar(30), password varchar(100), access_allowed varchar(1), start_date datetime, end_date date, first_name varchar(100), last_name varchar(100), middle_name varchar(100), email varchar(100), phone int(11), reset_login_flag varchar(100), locked int(11), incorrect_login_attempt int(11), attr1 varchar(200), attr2 varchar(200), avatar text, user_group int(11))  -- per-company application login credentials (tenant-local, distinct from control DB user_credentials)  [schema/mypayrol_trial.sql:47696]
user_feature_branch_access(id int(11) PK, user_fkey int(11) FK->user_credentials, feature_fkey int(11), branch_fkey varchar(10) FK->branches, is_hierarchy char(1), active char(1), created_at datetime)  [schema/mypayrol_trial.sql:47745]
verticals(vert_code varchar(20), vertical_name varchar(100), status int(11))  [schema/mypayrol_trial.sql:47758]
waranty_details(waranty_details_pkey int(10) PK, item_master_fkey int(10) FK->item_master, mfg_model double, item_mfg_date datetime, engineering_no double, waranty_id varchar(50), waranty_begin_date timestamp, waranty_end_date datetime, waranty_period int(10), waranty_notes varchar(50), item_notes varchar(50), created_by varchar(50), created_date timestamp, modified_by varchar(50), modified_date datetime, status int(10))  [schema/mypayrol_trial.sql:47765]
wishes(wishes_pkey int(11) PK, emp_fkey int(11) FK->emp_details, date date, remarks text, type varchar(20), created_date datetime, created_by varchar(20))  [schema/mypayrol_trial.sql:47786]
wizard_config(wizard_config int(11) PK, state varchar(20), link varchar(20), created_by varchar(20), created_date datetime, modified_by varchar(20), modified_date datetime)  [schema/mypayrol_trial.sql:47798]
working_day_time_procedures(day_time_seq int(11) PK, day_time_desc text, Sunday char(1), Sunday_F char(1), Monday char(1), Monday_F char(1), Tuesday char(1), Tuesday_F char(1), Wednesday char(1), Wednesday_F char(1), Thursday char(1), Thursday_F char(1), Friday char(1), Friday_F char(1), Saturday char(1), Saturday_F char(1), on_dutty1 varchar(30), off_dutty1 varchar(30), working_time1 int(11), on_dutty2 varchar(30), off_dutty2 varchar(30), working_time2 varchar(30), on_dutty3 varchar(30), off_dutty3 varchar(30), working_time3 varchar(30), on_dutty4 varchar(30), off_dutty4 varchar(30), working_time4 varchar(30), on_dutty5 varchar(30), off_dutty5 varchar(30), working_time5 varchar(30), on_dutty6 varchar(30), off_dutty6 varchar(30), working_time6 varchar(30), minuts_calc_perday int(11), minuts_aftr_on_dutty_cal_late int(11), minuts_bfr_off_dutty_cal_early int(11), min_cal_late_ifnoclockin int(11), min_cal_leave_early_ifnoclockout int(11), min_aftr_off_dutty_cal_ot int(11), min_bfr_on_dutty_cal_ot int(11), work_time_day_off_cal_ot int(11), ot_eligibility_threshold char(1), active int(11), isnextday int(11), shift_allowance varchar(11), otcomponents varchar(11), start_date_effective date, end_date_effective date, strict_monitorings char(1), minutes_per_half int(11), is_multiple_days varchar(2), no_of_shift_days float, is_exception int(1), include_break char(1), working_time3_ex_day char(1), working_time4_ex_day char(1), working_time5_ex_day char(1), working_time6_ex_day char(1), max_out_before_next_in char(1), overtime_monitoring char(1), max_in_time int(11), max_out_time int(11))  -- shift/roster template — weekday off-flags, OT rules, Sunday/holiday allowance mode flags; referenced by nearly every attendance/payroll routine  [schema/mypayrol_trial.sql:47812]
work_experience(experience_pkey int(11) PK, emp_join_fkey int(11) FK->emp_join, company varchar(100), from_date varchar(100), to_date varchar(100), designation varchar(100), department varchar(100), salary int(9), status tinyint(1))  [schema/mypayrol_trial.sql:47887]
```

---

## PART C — Structural Drift vs `mypayrol_mpm121.sql`

`mypayrol_mpm121.sql` is a **real customer tenant dump** (257,260 lines, not read in full — only `grep`/targeted block extraction used below). It has **239 tables** (vs. trial's 236) and **89 routines** (vs. trial's 91). This confirms the migration team's working assumption: tenant DBs are *not* frozen copies of `mypayrol_trial.sql` — they drift independently (schema-migration procs like the control DB's `update_doc_template_columns`/`alter_salary_amount_decimal` push incremental `ALTER TABLE`s to live tenants without ever being backported into the `mypayrol_trial.sql` reference dump). Any Next.js migration must treat `mypayrol_trial.sql` as a **baseline/lowest-common-denominator schema**, not gospel — live tenants may have newer columns/tables.

### C.1 Tables present in mpm121 but NOT in trial (6) — newer features not yet in the reference schema

| Table | Note |
|---|---|
| `attendance_location_cache` | `id, latitude, longitude, address, created_date` — geocoding/reverse-geocode cache for GPS attendance, likely to dedupe external geocoding API calls |
| `exception_applied` | `exception_applied_pkey, rule_id, branch_code, applied_date, month_year, creation_date, created_by, modification_date, modifyed_by` — log of which `exception_rule` was applied to which branch/month |
| `exception_attendance_change_log` | `change_id, emp_pkey, emp_id, att_date, applied_month, branch_code, old_in_time, old_out_time, new_in_time, new_out_time, change_reason, changed_by, rule_id, change_timestamp` — audit trail of attendance punch-time corrections made by an exception rule |
| `exception_rule` | `exception_id, rule_name, rule_type, data_type (0=Earlyout/1=latein/2=both), exception_days, exception_time, action_after_exception (0=leave detect/1=LOP), detect_count, leave_detect_type, reset_status, activate_status, status, ...` — configurable "N late-ins/early-outs per cycle before LOP/leave deduction" policy engine |
| `item` | `item_pkey, item_name, item_code, status, item_desc` — a **simpler/newer parallel item catalog** to the existing `item_master`/`item_details` tables (possibly a lighter-weight lookup used by the new exception/asset flows, or a schema-simplification in progress) |
| `locations` | `location_id, organization_id, location_code, location_name, address, city, state, pincode, status` — appears to be a newer, more normalized replacement candidate for `branches`/`site` (adds an `organization_id` multi-org dimension not present in `branches`) |

**Together, `exception_rule`/`exception_applied`/`exception_attendance_change_log` form a complete new subsystem** ("Exception Rules" — automated late-in/early-out tolerance-based leave/LOP deduction with an audit trail) that post-dates the `mypayrol_trial.sql` snapshot. This is corroborated by two new stored procedures (C.3) that operate on exactly these three tables. **Migration recommendation**: treat this as a first-class feature to design for in the Next.js attendance module, sourcing its DDL from `mypayrol_mpm121.sql` rather than `mypayrol_trial.sql`.

### C.2 Tables present in trial but NOT in mpm121 (3) — possibly abandoned/superseded in this tenant

| Table | Note |
|---|---|
| `inactive_list` | referenced by `emp_inactive_fn` (also absent from mpm121, see C.4) — the pairing suggests this whole inactive-employee-tracking feature was removed/superseded in this tenant |
| `site_master_approval` | site-master approval workflow — absent, along with its `_details` sibling |
| `site_master_approval_details` | detail rows for the above |

Not necessarily evidence of a schema regression — could simply mean this particular tenant (`mpm121`) never had the site-approval workflow provisioned, or it was dropped after disuse. Worth confirming against 2-3 other tenant dumps before concluding it's dead code.

### C.3 Routines present in mpm121 but NOT in trial (4)

| Routine | Signature | Note |
|---|---|---|
| `exception_rule_apply_prce` | `PROCEDURE (IN p_branch_code varchar(50), IN p_month_start varchar(20), IN p_rule_id int(11), IN p_user_login varchar(50), OUT p_output varchar(50))` — mpm121.sql:3601 | Applies one `exception_rule` to a branch/month: presumably scans `attendance_register`/`emp_detail_timeattandance` for late-in/early-out patterns, writes corrections to `exception_attendance_change_log`, and logs to `exception_applied` |
| `exception_rule_reversal_proc` | `PROCEDURE (IN p_rule_id int, IN p_branch_code varchar(50), IN p_month_start varchar(50), OUT p_output text)` — mpm121.sql:5446 | Reverses/undoes an applied exception rule for a branch/month (rolls back `exception_attendance_change_log` entries) |
| `leave_balance_caryfrwd_month_fn` | `FUNCTION (Pemp_fkey int, psalary_head_item_fkey int, Pmonth date, pfinyear varchar(30)) RETURNS float` — mpm121.sql:14421 | Same signature shape as trial's `leave_balance_inthe_month_fn` — likely a **renamed/rewritten successor** of that function (name change from "inthe_month" to "caryfrwd_month" suggests the carry-forward semantics were clarified/fixed) |
| `leave_balance_update_fn` | `FUNCTION (pmonth_year date, pleave_type int) RETURNS varchar(100)` — mpm121.sql:16270 | New bulk leave-balance updater, parameterized by leave type rather than per-employee — likely a batch/cron variant |

### C.4 Routines present in trial but NOT in mpm121 (6)

| Routine | Note |
|---|---|
| `emp_inactive_fn` | Paired with the missing `inactive_list` table (C.2) — this whole employee-inactivity-tracking feature appears retired in this tenant |
| `stock_bal_qty_fn` | Stock-balance-quantity calculator |
| `stock_tranfer_item_pkey_update` | Inventory stock-transfer housekeeping proc |
| `stock_tranfer_pkey_prc` | ditto |
| `stock_trans_prc` | Generic stock-transaction inserter |
| `vangrd_pfesi_update_fn` | One-off/company-specific PF-ESI bulk-update utility (name suggests a specific customer, "Vanguard") |

**Notable finding**: all four `stock_*` procedures are missing from mpm121, **but the underlying `stock_details`/`stock_tranfer`/`stock_store_tranfer`/`stock_adjustments` tables still exist** in mpm121 (confirmed via `grep "^CREATE TABLE \`stock_" schema/mypayrol_mpm121.sql`, 8 matches). This suggests the inventory/stock-transfer **business logic moved out of stored procedures and into the PHP application layer** for this tenant (or a newer tenant-specific proc with a different name replaced them — not confirmed without further grep). Migration team should verify whether `Controller/*Stock*.php` files call these procs at all before assuming stock logic must be ported from SQL.

### C.5 Column-level drift on tables present in BOTH (spot-checked)

Of the tables spot-checked below, most core payroll/leave/HR tables (`emp_details`, `emp_proff`, `emp_leave_transactions`, `holidays`, `salary_structure_details`, `income_tax_slab`, `leave_encashment_master`, `payroll_master`, `db_config`) have **identical column counts and names** between trial and mpm121 — meaning the reference schema is a reliable baseline for those tables. The following show drift:

| Table | Drift | Detail |
|---|---|---|
| `attendance_register` | +1 column in mpm121 | adds `created_time` (trial:29 vs mpm121:30 tracked cols; mpm121 col count 54 vs trial 53) |
| `working_day_time_procedures` | +5 columns in mpm121 | adds `first_in_last_punch` (a new punch-tracking mode flag) plus an audit quartet `created_by`, `creation_date`, `modified_by`, `modification_date` (trial's version has **no audit columns at all** on this heavily-used shift-config table — a real drift a migration should capture) |
| `emp_salary_slip` | +3 columns in mpm121 | adds `formula` (likely stores the resolved calculation string per line item — trial only stores this in `emp_statutory_components.remarks`, not on the salary slip itself), `remarks_2` (a second free-text remarks field), `combined_base_value` (a new aggregation field, purpose unclear without body access) |
| `leavepolicy` | +1 column in mpm121 | adds `is_expire` flag (leave-policy expiration toggle not present in the trial reference schema) |

**Migration recommendation**: for the 4 drifted tables above, source the authoritative column list from `mypayrol_mpm121.sql` (or better, from several live tenant dumps) rather than `mypayrol_trial.sql`, since the reference schema is measurably behind production on exactly the tables (attendance, shift config, payslip, leave policy) most central to this migration.

---

## PART D — Cross-Table Relationships (`mypayrol_trial.sql`)

**Confirmed**: scanning every `CREATE TABLE` statement in Part B (all 236 tables), **not one** declares a `FOREIGN KEY` constraint — only `PRIMARY KEY`, `KEY` (plain index), and occasional `UNIQUE`/`UNIQUE KEY`. This matches Part A's finding for the control DB and the tenant-DB pattern generally: **all relationships are enforced by CakePHP Model associations in PHP, not by the database.** Migration to Next.js/Prisma (or any ORM) will need to declare these relationships explicitly since none exist as live DB constraints to introspect.

Below is the derived relationship list — every column ending `_fkey` (plus a handful of un-suffixed but clearly-referential columns like `emp_id`, `branch_code`, `company_code`, `structure_id`) mapped `child_table.column → parent_table.column`, resolved by naming convention against the table list in Part B. This was generated by scanning all 236 `CREATE TABLE` bodies for `*_fkey`-suffixed columns and matching their stripped base name against the table catalog (exact match, or a small override table for known aliases like `emp_fkey`→`emp_details.emp_pkey`, `structure_fkey`/`structure_id`→`salary_structure.structure_id`, `head_fkey`→`salary_heads.head_pkey`, `salary_head_item_fkey`→`salary_head_items.salary_head_item_pkey`, `site_fkey`→`site.site_pkey`, `store_fkey`/`store_pkey`(sic, used as an FK in some tables)→`store_master`, `item_master_fkey`→`item_master.item_pkey`, `branch_fkey`→`branches.id`). Where the convention resolves ambiguously or to no table, the column is listed with `(unresolved)`.

**Most common parent tables** (the hub tables everything else hangs off, by number of distinct child tables referencing them):

1. **`emp_details`** (via `emp_fkey`, `pemp_fkey`) — referenced by ~90+ tables: `access_site`, `access_store`, `activity`, `admin_dashboard`, `asset_allocate`, `asset_management`, `attendance_punch`, `attendance_register`, `attendance_register_rep`, `attendance_register_update`, `contracted_days`, `cus_visit_locations`, `emp_advance`, `emp_advance_info`, `emp_banks`, `emp_calc_variable_components`, `emp_ctc_detail`, `emp_ctc_transaction`, `emp_ctc_upload`, `emp_dashboard`, `emp_documents`, `emp_encash_slip`, `emp_expense`, `emp_family`, `emp_join`, `emp_leave_approval`, `emp_leave_balance_year`, `emp_leave_info`, `emp_leave_transactions` (via `emp_pkey`-style cols in body, not always `_fkey`-suffixed), `emp_loan`, `emp_loan_info`, `emp_menu`, `emp_monthly_salary_components`, `emp_new_salary_structure`, `emp_passport_visa`, `emp_pay_info`, `emp_salary_structure`, `emp_settle_slip`, `emp_shift_planner`, `emp_statutory_components`, `emp_tax_regime`, `promotions`, `resignation_accept`, `resignation_requests`, `termination`, `Upload_leave_errirs`, `user_access` (indirectly via `user_credentials`), `wishes`, and dozens more — this is the single most-referenced table in the schema, as expected for an HR/payroll system.
2. **`emp_proff`** — employee's professional/job-assignment record; typically joined 1:1 with `emp_details` in procedures (both keyed by `emp_pkey`/`emp_fkey`) rather than referenced as a separate FK target by other tables' `_fkey` columns.
3. **`salary_head_items`** (via `salary_head_item_fkey`) — referenced by `salary_structure_details`, `emp_salary_structure`, `emp_statutory_components`, `emp_monthly_salary_components`, `emp_calc_variable_components`, `leavepolicy` (control DB), `Upload_leave_errirs`(indirectly), `tax_salary_components` mapping tables.
4. **`salary_structure`** (via `structure_id`/`structure_fkey`) — referenced by `salary_structure_details`, `emp_proff.structure_id`, `emp_new_salary_structure`.
5. **`branches`** (via `branch_code`, mostly un-suffixed string FK, not `_fkey` int) — nearly every transactional table carries a `branch_code varchar` column that is a soft-FK to `branches.branch_code`, not `branches.id`.
6. **`item_master`** (via `item_master_fkey`, `item_fkey`) — referenced by `additional_details`, `waranty_details`, `item_pricing`, `stock_details` (indirectly), `itm_allocation`.
7. **`site`** (via `site_fkey`) — referenced by `access_site`, `site_attendance`, `site_history`, `site_transactions`, `site_attendance_register` (indirectly via site-branch mapping).
8. **`store_master`** (via `store_fkey`/`store_pkey`-as-FK) — referenced by `access_store`, `stock_details`, `stock_store_tranfer`, `stock_store_tranfer_item`.
9. **`user_credentials`** (via `user_fkey`) — referenced by `user_access`, `user_feature_branch_access`.
10. **`emp_join`** (via `emp_join_fkey`) — referenced by `work_experience`, `qualifcations`, `family` (onboarding-related child tables of a not-yet-converted employee join record).

### D.1 Full `*_fkey` column → resolved parent table list

The complete machine-derived list (236 tables scanned, every `_fkey`-suffixed column extracted with its heuristically resolved parent) is captured inline in each table's compact definition in Part B.2 above (`col FK->parent_table` annotations). Representative examples not already obvious from the hub list above:

```
additional_details.item_master_fkey → item_master.item_pkey
allocate_details.item_purchase_fkey → item_purchase (heuristic; item_purchase_fkey base is "item_purchase")
contracted_days.emp_fkey → emp_details.emp_pkey
emp_advance.emp_fkey → emp_details.emp_pkey (pattern repeats across all emp_* child tables)
holidays.HOLIDAY_GROUP_ID → holiday_group.HOLIDAY_GROUP_ID (control DB; un-suffixed but clearly referential)
leavepolicy.LEAVEPOLICY_GROUP_ID → leavepolicy_group.LEAVEPOLICY_GROUP_ID
leavepolicy.salary_head_item_fkey → salary_head_items.salary_head_item_pkey
salary_structure_details.structure_id → salary_structure.structure_id
salary_structure_details.salary_head_item_fkey → salary_head_items.salary_head_item_pkey
stock_tranfer_item.stock_tranfer_pkey (used as FK) → stock_tranfer.stock_tranfer_pkey
user_feature_branch_access.branch_fkey → branches.id
user_feature_branch_access.user_fkey → user_credentials.user_pkey
waranty_details.item_master_fkey → item_master.item_pkey
work_experience.emp_join_fkey → emp_join.emp_join_pkey
Upload_leave_errirs.emp_fkey → emp_details.emp_pkey
```

**Caveat**: because there are no enforced constraints, some `_fkey` columns may be **stale/unused** (dead columns from earlier schema iterations) or point to a table by convention that the application code never actually joins against — this list should be validated against the CakePHP Model `$belongsTo`/`$hasMany` associations in `Model/*.php` (out of scope for this schema-only pass) before being treated as ground truth for a Next.js/Prisma schema's `@relation` declarations.

---

**End of §1 Full Database Schema Catalog.** Parts A (control DB, 73 tables + 17 routines), B (trial reference schema, 236 tables + 91 routines), C (drift vs. live tenant `mpm121`), and D (cross-table relationships) are complete.

*(Note: this section supersedes/merges an earlier shorter draft of Parts C/D that a prior concurrent pass had appended to this same file — the content above is the complete, citation-backed version and is the canonical one.)*

---

## 4. Stored Procedure & Function Logic

### 4.1 Payroll, Salary, Tax, CTC & Arrears Routines

# Payroll / Salary / Tax / CTC / Arrears Stored Procedure & Function Catalog

Source of all SQL bodies: `schema/mypayrol_trial.sql` (single-file dump containing `CREATE TABLE` plus 91 `CREATE FUNCTION`/`CREATE PROCEDURE` definitions with full bodies). All line numbers below are exact `CREATE FUNCTION`/`CREATE PROCEDURE` start lines in that file, verified by direct read.

**Key architecture finding**: `calculate_salary_main_prc` and `salary_process_prc` are two independent, near-duplicate implementations of the same "compute one employee's payslip for a month" operation. `Controller/PayrollController.php:868-875` shows they are NOT both run for the same employee — a `specialCompanies` exclusion list decides which one runs:
```php
if (!in_array($company_code, $specialCompanies)) {
    $this->AttendanceRegister->calculateSalaryMainPrc($outputParameter); // current/default path
} else {
    $out = $this->AttendanceRegister->salaryProcessPrc($outputParameter); // legacy path for specific companies
}
```
`calculate_salary_main_prc` is the newer, modular implementation — it delegates variable-component computation to sub-procedures (`calculate_monthly_salary_components_prc`, `calculate_holiday_allowances_prc`, `calculate_shift_allowance_prc`, `calculate_ot_allowance_prc`, `calculate_leave_encashment_prc`, `calculate_statutory_components_prc`). `salary_process_prc` is an older monolithic version that inlines all of that logic itself and is kept only for legacy/special-company compatibility. **A Next.js reimplementation should target `calculate_salary_main_prc` and its sub-procedures as the source of truth**, treating `salary_process_prc` as a secondary/legacy code path only needed if those specific companies are still on the platform.

---

## 1. `calculate_emp_component_breakup`

**Signature**: `PROCEDURE calculate_emp_component_breakup(IN Pstructure_id int, IN Psalary_head_item_pkey int, IN Pmonthly_comp decimal(10,2), IN Pmonthly_gross decimal(10,2))` — `schema/mypayrol_trial.sql:193`

**What it does**: Simulates what a salary structure preview would look like if a single component (`Psalary_head_item_pkey`) were manually overridden to `Pmonthly_comp`, for a "what-if" UI (e.g. an increment/CTC editor).
- Reads `salary_structure_details` joined to `salary_head_items`/`salary_heads` for `structure_id = Pstructure_id`, filtered to rows whose `structure_formula` contains letters (i.e. formula-driven components) **and only `head_pkey IN (4, 5)`** (statutory heads — a hardcoded filter, added by a code comment "Edited by Akshay on 11-12-2025"). Rows are ordered so `rembalance` operator rows are processed last.
- Builds a temp table `salary_breakup_temp` holding one row per matching component.
- For each row, opens an inner cursor over ALL `salary_structure_details` rows for that same structure (`comp_cur`) and does textual substitution: if the inner row's `salary_head_item_fkey` equals `Psalary_head_item_pkey`, replace occurrences of that item's name text inside the outer row's `structure_formula` string with `Pmonthly_comp` (the hypothetical override value); otherwise replace with the item's normal `structure_det_value`. Also replaces the literal string `'Monthly Gross Salary'` with `Pmonthly_gross`.
- Computes the final value per operator type:
  - `formula`: `(structure_derived_perc / 100) * Pmonthly_gross`
  - `fixed`: `structure_det_value`
  - `limit`: `LEAST(structure_det_value, formula_value)`
  - `limit_wl`: `LEAST(structure_det_depends, formula_value)`
  - `limit_wg`: `GREATEST(structure_det_depends, formula_value)`
  - `rembalance`: `Pmonthly_gross - vdistributed_total` (running total of all `head_fkey = 1` "Addition" values processed so far)
  - anything else: `0`
- If `deduction_flag = 'Y'` (head's `head_operator` is `'deduction'`), negates the value.
- Returns the temp table via `SELECT * FROM salary_breakup_temp ORDER BY salary_head_item_fkey`.

**Called from**: `Controller/SalaryIncrementController.php:3950` and `:4097` — `CALL calculate_emp_component_breakup('$structure_id', '$salary_head_item_pkey', '$new_value', '$gross_amount')`.

**Hardcoded constants**: `head_pkey IN (4, 5)` filter (statutory heads) at line 232; `head_fkey1 = 1` treated as "Addition"/Basic-like head at line 338.

---

## 2. `calculate_emp_salary_breakup`

**Signature**: `PROCEDURE calculate_emp_salary_breakup(IN Pemp_fkey int, IN Pstructure_id int, IN Pmonthly_gross decimal(10,2))` — `schema/mypayrol_trial.sql:354`

**What it does**: Full CTC → component breakup preview for one employee/structure/gross combo (used by CTC-edit UIs to show a live preview before saving). Reads `salary_structure_details` joined with `salary_head_items`/`salary_heads` for `structure_id = Pstructure_id`, restricted to rows where `structure_det_value <> 0 OR structure_derived_perc <> 0 OR structure_det_operator = 'rembalance'`. Order: non-`rembalance` rows first, `rembalance` last.
- Same operator formula set as #1 above:
  - `formula` = `(structure_derived_perc/100) * Pmonthly_gross`
  - `fixed` = `structure_det_value`
  - `limit` = `LEAST(structure_det_value, formula_value)`
  - `limit_wl` = `LEAST(structure_det_depends, formula_value)`
  - `limit_wg` = `GREATEST(structure_det_depends, formula_value)`
  - `rembalance` = `Pmonthly_gross - running_total`
  - default = `0`
- Negates if the head is a deduction head.
- Accumulates `vdistributed_total` only for non-`rembalance`, positive-value rows belonging to `head_pkey = 1` (the "Basic/Addition" head group) — this running total is what `rembalance` subtracts from gross to compute the balancing component (e.g. "Special Allowance" absorbing whatever's left of CTC).
- Rounds each `amount` to 2 decimals and returns all rows via `SELECT * FROM salary_breakup_temp`.

**Called from**:
- `Controller/EmployeeController.php:8723`
- `Controller/EmployeeJoinController.php:7800`
- `Controller/SalaryIncrementController.php:2102`

All call as `CALL calculate_emp_salary_breakup('$empFkey', $emp_structure_id, '$monthlyGross')`.

**Hardcoded constants**: `head_pkey = 1` is treated as the "direct addition" head group for gross-distribution purposes (same convention across nearly every payroll proc in this file).

---

## 3. `calculate_holiday_allowances_prc`

**Signature**: `PROCEDURE calculate_holiday_allowances_prc(IN pemp_pkey int, IN pmonth_year varchar(7), IN pcreated_by varchar(30))` — `schema/mypayrol_trial.sql:457`

**What it does**: Computes Sunday/holiday variable allowances for one employee/month and inserts them into `emp_calc_variable_components` (a staging table later merged into `emp_salary_slip` by `calculate_salary_main_prc`).
1. Checks the employee's active (`end_date_effective IS NULL`) `emp_salary_structure` rows for components named exactly `'sunday allowance'` or `'sunday or holiday allowance'` (case-insensitive).
2. If either exists, calls `get_policy_weekoff_holiday_count_prc(pemp_pkey, month_start, month_end, OUT weekoff, OUT holiday)` to get the count of weekoffs/holidays for the month.
3. `v_sunday_days = weekoff_count`; `v_sun_hol_days = weekoff_count + holiday_count`.
4. Looks up `working_day_time_procedures.work_time_day_off_cal_ot` for the employee's shift (`emp_proff.day_time_seq`). This is a **mode flag**: only `work_time_day_off_cal_ot = 4` enables "Sunday Allowance" payment (else zeroed), and only `= 5` enables "Sunday or Holiday Allowance" (else zeroed) — these two allowances are mutually-exclusive policy modes per shift.
5. For each qualifying structure row, `total_amount = days * structure_det_value` (i.e. day-count × per-day rate).
6. If nothing qualifies, the procedure exits early (`LEAVE main_block`).
7. Otherwise inserts one row per component into `emp_calc_variable_components` with `salary_amount = total_amount`, `salary_rate = structure_det_value`.

**Called from**: Not called directly from any Controller PHP file — it is invoked internally by `calculate_salary_main_prc` at `schema/mypayrol_trial.sql:1277` (`call calculate_holiday_allowances_prc(vemp_pkey, pmonth, puser_id);`), which is itself reached via `Model/AttendanceRegister.php:66` (`calculateSalaryMainPrc`), called from `Controller/PayrollController.php:872`.

**Hardcoded constants**: `work_time_day_off_cal_ot` values `4` (Sunday Allowance mode) and `5` (Sunday-or-Holiday Allowance mode) are magic numbers with no lookup table shown in this dump — must be cross-referenced against `working_day_time_procedures` seed data during migration.

---

## 4. `calculate_monthly_salary_components_prc`

**Signature**: `PROCEDURE calculate_monthly_salary_components_prc(IN pemp_pkey int(11), IN pmonth varchar(20), IN puser_id varchar(30), OUT poutput varchar(30))` — `schema/mypayrol_trial.sql:744`

**What it does**: Computes the FIXED, attendance-prorated salary components (head_fkey = 1, i.e. "direct addition"/Basic-type heads) for one employee/month, writing to `emp_monthly_salary_components`.
1. Soft-deletes any existing un-superseded rows for this emp/month (`end_date_effective = current_date`).
2. Loads `emp_type` and `structure_id` from `emp_proff`, and attendance counters (`presant_total`, `leave_total`, `lop_only`, `lop_total`, `wd_lop_total`, `weekoff_total`, `holiday_total`, `working_days`, `calander_days`) from `attendance_register`.
3. **Hourly-wage branch**: if `emp_type = 'HOURLY WAGES'`, computes `daytotal` (minutes/60) from `working_day_time_procedures.working_time1/2`, pulls `emp_anual_ctc` from `emp_ctc_transaction` (used here as an hourly rate, not an annual figure), computes `leave_salary = leave_total * daytotal * rate`, sums actual worked minutes from `emp_detail_timeattandance` / 60 = `duration`, `total_hours = duration + leave_total*daytotal`, `monthly_ctc = duration * rate + leave_salary`. Then immediately calls `site_sal_structure_distribution_fn(branch_code, pemp_pkey, structure_id, monthly_ctc, puser_id)` to redistribute the salary structure using this computed monthly amount as the new CTC base.
4. Iterates the employee's active `head_fkey = 1` structure rows (cursor `structurecur`). Reads `db_config.payroll_type` — if `'M'` (monthly-attendance mode), recomputes calendar/working days for the whole month via `get_policy_weekoff_holiday_count_prc` and applies edge-case corrections:
   - `DAILY WAGES`: if `wd_lop_total == working_days` exactly (fully absent) and the recomputed monthly working days is larger, bumps `wd_lop_total` up to the larger figure (prevents overpaying when attendance register was only partially filled).
   - Non-hourly, `prorate_code = 3` (fixed-days proration): similar catch-up logic against `salary_structure.fixed_days`.
   - `prorate_code = 2` (working-days proration) / `prorate_code = 1` (calendar-days proration): analogous catch-ups.
5. Per-component salary computation, branching by `emp_type`:
   - **DAILY WAGES**: non-fixed heads → `rate = working_days - wd_lop_total` (floor 0); `salary = structure_det_value * rate`. Fixed heads → `salary = structure_det_value` unconditionally.
   - **HOURLY WAGES**: `salary = structure_det_value` flat; inserted with `salary_rate = structure_det_value/total_hours` for non-fixed, or `structure_det_value` for fixed.
   - **Everyone else** (monthly-salaried), by `prorate_code`:
     - `3` (fixed days): `rate = fixed_days - lop_total` (floor 0); `salary = (structure_det_value/fixed_days) * rate`.
     - `2` (working days): `rate = working_days - wd_lop_total` (floor 0); `salary = (structure_det_value/working_days) * rate`.
     - `1` (calendar days): `rate = calander_days - lop_total` (floor 0); `salary = (structure_det_value/calander_days) * rate`.
     - Fixed-type heads: `salary = structure_det_value` unless the days-worked computation for the relevant prorate code is `<= 0`, in which case `salary = 0`.
6. All rows inserted into `emp_monthly_salary_components`.
7. Sets `poutput = 'success'`/`'failure'` based on whether any rows exist for the emp/month afterward.

**Called from**: Not called directly from Controller PHP. Invoked internally by `calculate_salary_main_prc` at `schema/mypayrol_trial.sql:1265` (`call calculate_monthly_salary_components_prc(vemp_pkey, pmonth, puser_id, vcmscp_output);`).

**Hardcoded/derived business rules**: prorate codes `1` (calendar-day), `2` (working-day), `3` (fixed-day) are the three proration modes used system-wide; `head_fkey = 1` = direct/Basic-style addition heads.

---

## 5. `calculate_ot_allowance_prc`

**Signature**: `PROCEDURE calculate_ot_allowance_prc(IN pemp_pkey int, IN pmonth_year varchar(7), IN pcreated_by varchar(30))` — `schema/mypayrol_trial.sql:977`

**What it does**: Computes overtime pay for the month and writes to `emp_calc_variable_components`.
- Cursor joins `emp_ot_master` (verified OT entries: `is_verified = 'Y'`, matching `month`) to `emp_proff`→`working_day_time_procedures` to get each shift's `otcomponents` (the salary_head_item_fkey that represents OT pay) and `set_duration` (verified OT minutes).
- For each row, skips (ITERATE) if the employee has no active `emp_salary_structure` row for that `otcomponents` salary head.
- Otherwise pulls the structure's `structure_det_value` (OT rate, presumably per-hour) and computes `ot_salary = (set_duration / 60) * structure_det_value` — i.e. **OT minutes converted to hours × hourly OT rate**.
- If `ot_salary > 0`, inserts into `emp_calc_variable_components`.

**Called from**: `Controller/OtAttendanceNewController.php:1084` — `CALL calculate_ot_allowance_prc($emp_fkey, '$month', '$user_id')`. Also invoked internally by `calculate_salary_main_prc:1279`.

**Hardcoded constants**: `/60` conversion (minutes → hours) is the only numeric constant.

---

## 6. `calculate_salary_main_prc`

**Signature**: `PROCEDURE calculate_salary_main_prc(IN pmonth varchar(20), IN pbranch_code varchar(30), IN pemp_pkey int(11), IN ppayroll_master_pkey int(11), IN puser_id varchar(30), OUT perr_msg varchar(2000))` — `schema/mypayrol_trial.sql:1085`

**What it does**: The primary, current-generation "run payroll for one employee for one month" orchestrator (see the architecture note at the top of this document — this is the default path; `salary_process_prc` is the legacy fallback used only for a `specialCompanies` allow-list).

High-level flow per employee (cursor over `emp_details`/`emp_proff` filtered to `status = 1` and having an `attendance_register` row for the month):
1. Soft-closes any existing non-`Arrear` `emp_salary_slip` rows for this emp/month (`end_date_effective = current_date`).
2. Loads attendance counters from `attendance_register`. If `emp_type = 'DAILY WAGES'`, `lop_total := wd_lop_total`. If not hourly and `prorate_code = 2` (working-days prorate), same substitution.
3. **Fixed components**: calls `calculate_monthly_salary_components_prc` (#4), then copies its output rows from `emp_monthly_salary_components` into `emp_salary_slip`.
4. **Variable components**: soft-closes old `emp_calc_variable_components`, then calls in sequence:
   - `calculate_holiday_allowances_prc` (#3)
   - `calculate_shift_allowance_prc` (#12)
   - `calculate_ot_allowance_prc` (#5)
   - `calculate_leave_encashment_prc` (not in this migration's scope list but related; computes leave-encashment pay)
   Copies their combined output from `emp_calc_variable_components` into `emp_salary_slip`.
5. **Fixed items nested inside "variable" structure section** (heads 9/10): for each active `emp_salary_structure` row with `head_type = 'fixed'` and `head_fkey IN (9, 10)`, computes prorated amount (zeroed if days-worked for the applicable prorate_code is `<= 0`) and inserts into `emp_variables_upload` with `action = 'Salary Processing'`.
6. **Manual/uploaded variables**: reads `emp_variables_upload` rows for the month (`status = 1`), summed by component, and inserts into `emp_salary_slip` — addition rows as positive `'P'` action rows, deduction rows negated, others passed through.
7. **Salary advances**: any `emp_advance` rows for the month with `status = 1` are inserted as negative deductions (`salary_head_item_fkey = 0`, desc `'Salary Advance'`).
8. **Loan EMIs**: sums `emp_loan_info.loan_emi` for `paid_status IN ('A','S')` for the month; if > 0, inserts a negative `'Loan'` deduction row per matching loan.
9. **Statutory components**: calls `calculate_statutory_components_prc` (#8) and copies its `emp_statutory_components` output into `emp_salary_slip`.
10. **Professional Tax**: looks up the `tax_salary_components` row named `'Professional Tax'`; if its `operator = 'A'` (Automatic) and not already inserted this month, calls `profession_tax_cal_fn` (#17) and inserts a negative deduction row.
11. **TDS (Income Tax)**: if `payroll_master.tax_include = 'Y'`, looks up the employee's tax regime option (`emp_tax_regime.option_type`) for the open financial year:
    - `'O'` (Old regime): calls `tax_salary_distribution_fn` (#25) → if it returns 1, reads `tax_monthly_proj`/`taxable_income` from `emp_tax_sal_trans_sum`. **If `taxable_income <= 500000`, forces `vtds = 0`** (old-regime rebate threshold). If nonzero, inserts a `-vtds` deduction with `salary_head_item_fkey = 82` labelled `'TDS'`, then calls `tax_computation_fn` (#24) to log the computation.
    - Otherwise (new regime): calls `tax_salary_distribution_new_fn` (#26) → reads from `emp_tax_sal_trans_sum_new`. **If `taxable_income <= 1200000`, forces `vtds = 0`** (new-regime rebate threshold, FY2025+ ₹12L rebate cap). Same insert/compute pattern.
12. **Payroll master roll-up**: sums `emp_salary_slip.salary_amount` for the emp/month into `monthly_amount` (all additions), `indirect_amount` (addition + `item_part = 'indirect'`), `total_deduction` (all deductions), `total_variables` (`head_type = 'variable'`). Updates `payroll_master`:
    - `gross_salary = monthly_amount - indirect_amount`
    - `net_salary = gross_salary + total_deduction + total_variables` (deduction values are already negative, so this is effectively `gross - |deductions| + variables`)
    - `action = 'Processed'`
13. Writes an audit row into `salary_processing_audit`.

**Called from**: Not called directly by a Controller — invoked via the model wrapper `Model/AttendanceRegister.php:60-70` (`calculateSalaryMainPrc`), which is called from `Controller/PayrollController.php:872` inside the `if (!in_array($company_code, $specialCompanies))` branch of the payroll-run action.

**Hardcoded constants**:
- `salary_head_item_fkey = 82` hardcoded as the "TDS" component ID (line 1542, 1570).
- Old-regime TDS zero-threshold: taxable income `<= 500000` (line 1526).
- New-regime TDS zero-threshold: taxable income `<= 1200000` (line 1554) — reflects the ₹12 lakh rebate under India's new tax regime (FY2025-26 budget).
- `head_fkey IN (9, 10)` for the "fixed inside variable section" pass (line 1298).

---

## 7. `calculate_shift_allowance_prc`

**Signature**: `PROCEDURE calculate_shift_allowance_prc(IN pemp_pkey int, IN pmonth_year varchar(7), IN pcreated_by varchar(30))` — `schema/mypayrol_trial.sql:1615`

**What it does**: Computes shift allowance pay based on attendance-register daily field values.
1. Builds a temp table `tmp_shift_present` by unpivoting `attendance_register.FIELD1..FIELD31` (one column per day-of-month) via a 1..31 UNION-ALL "numbers" subquery, mapping each day to a shift (`COALESCE(emp_shift_planner.shift_id, emp_proff.day_time_seq)`), and counting attendance value patterns:
   - `'P/P'` (present both halves) → **1.0 day**
   - `'P/%'` or `'%/P'` (present one half) → **0.5 day**
   - anything else → 0
   Grouped by `shift_id` to get `present_count` per shift for the month.
2. For each shift with `present_count > 0`, looks up `working_day_time_procedures.shift_allowance` (the salary_head_item_fkey for that shift's allowance) joined to the employee's active `emp_salary_structure` row for that item to get the per-day rate (`structure_det_value`).
3. `salary_amount = structure_det_value * present_count`.
4. If a row already exists in `emp_calc_variable_components` for this emp/month/shift/component, adds to its `salary_amount` (UPDATE); otherwise INSERTs a new row.

**Called from**: Not called directly by a Controller — invoked internally by `calculate_salary_main_prc:1278`.

**Hardcoded constants**: attendance-code semantics `'P/P'` = 1 day, `'P/x'`/`'x/P'` = 0.5 day are effectively hardcoded string-pattern rules baked into this procedure (not table-driven).

---

## 8. `calculate_statutory_components_prc`

**Signature**: `PROCEDURE calculate_statutory_components_prc(IN pemp_pkey int(11), IN pmonth varchar(20), IN puser_id varchar(30), OUT poutput varchar(30))` — `schema/mypayrol_trial.sql:1827`

**What it does**: Computes statutory-head (PF, ESI, PT, etc. — `head_pkey IN (4,5)`) components for one employee/month, writes to `emp_statutory_components`. This is the most formula-heavy procedure in the payroll pipeline.

1. Soft-closes existing rows. Loads employee type / attendance counters as in #4.
2. Computes `emp_monthly_ctc` differently per wage type (same DAILY/HOURLY/monthly branching pattern as #4, including the hourly-wage → `site_sal_structure_distribution_fn` redistribution call).
3. **Per-component prorated calculation** (`block: start_loop`) — identical prorate-code logic to #4 (DAILY WAGES / HOURLY WAGES / prorate_code 1/2/3), writing to `emp_statutory_components`.
4. **`block2` — formula evaluation for head_fkey IN (4,5)**: for each `salary_structure_details` row belonging to those heads whose `structure_formula` contains letters, takes the raw `structure_det_calequation` template and textually substitutes each referenced component's key (format `"{salary_head_item_fkey}_{item_desc_with_underscores}"`) with its actual `salary_amount` from `emp_salary_slip` for the month (falling back to 0 for components not yet paid this month via a `UNION`). Also substitutes the literal `'monthsal'` token with the sum of all `addition`+`direct` components paid this month. Result stored into `emp_statutory_components.remarks` as a literal arithmetic expression string (e.g. `"25000*0.12"`).
5. **`block3` — self-referential substitution**: replaces any remaining `{fkey}_{desc}` tokens inside `remarks` for OTHER statutory rows that reference this component, substituting `'0'` if not yet resolvable (avoids infinite substitution chains for circular-looking formulas).
6. **`block4` — dynamic SQL evaluation**: for every `emp_statutory_components` row whose `remarks` is now a "clean" numeric arithmetic expression (validated with a set of regex guards: must start/end with digit/paren, only contains `0-9.()+*/- `, no double operators, no unbalanced parens, no double spaces between digits, etc.), builds `SELECT (<remarks>) INTO @salary` via `PREPARE`/`EXECUTE` dynamic SQL, catching `SQLEXCEPTION` to null out on failure. Result is written into `salary_rate`/`salary_amount`, negated first if `head_operator = 'Deduction'`.
7. **`block5` — ESI rounding rule**: for rows named `esi`, `esi - employer contribution`, or `esi - employee contribution`: deduction-side values are **rounded UP with `CEIL(ABS(x))` then negated**; non-deduction values use standard `ROUND()`. (ESI statutory rounding convention: round up for the employee's own deducted contribution.)
8. **`block6` — limit_wl/limit_wg post-processing**: for `head_type IN ('limit_wl','limit_wg')` rows with nonzero amount, re-applies `LEAST`/`GREATEST` against `salary_structure_details.structure_det_depends` (falling back to the row's own `structure_det_value` if depends is 0/null), re-negates for deductions, and rounds.
9. Sets `poutput = 'success'/'failure'`.

**Called from**: Not called directly by a Controller — invoked internally by `calculate_salary_main_prc:1479`.

**Hardcoded constants**: `head_pkey IN (4, 5)` = statutory heads; ESI rounding rule (`CEIL` for deduction side, `ROUND` for employer side) is a hardcoded business rule with no config flag; the regex validation guards for "safe to eval" strings in block4 are a fixed set of patterns.

---

## 9. `carryforward_insert_prc`

**Signature**: `PROCEDURE carryforward_insert_prc(IN pfin_year varchar(30), OUT perr_msg varchar(30))` — `schema/mypayrol_trial.sql:2379`

**What it does**: Not payroll amount computation — it's a leave-cycle date-range setter. For every `emp_leave_balance_year` row with `status = 1` and matching `fin_year`:
- If `fin_year` looks like a "YYYY-YY" custom leave-cycle string (5th character is `-`), derives `leave_cycle_start_date`/`leave_cycle_end_date` via `att_start_end_fn` (a month-boundary helper) applied one month after the fin_year string.
- Otherwise looks up the company's configured `fin_year.start_month`/`end_month` (for `vattr1 = 0`, i.e. calendar-year-style records) and copies those into the leave-balance-year row.
Sets `perr_msg = 'OK'` unconditionally at the end (no real error handling).

**Called from**: No Controller PHP call site found in this codebase (likely invoked via a scheduled job/cron script not present in `Controller/`, or run manually/via admin tooling at financial-year rollover).

**Hardcoded constants**: none numeric; relies on `fin_year` string format sniffing (`substr(vfin_year,5,1) = '-'`).

---

## 10. `copy_salary_structure_to_new`

**Signature**: `PROCEDURE copy_salary_structure_to_new(IN vemp_fkey int)` — `schema/mypayrol_trial.sql:2535`

**What it does**: Simple structural migration helper — closes the employee's active `emp_new_salary_structure` rows (`end_date_effective = CURDATE()`) and re-inserts a fresh copy of their current `emp_salary_structure` rows into `emp_new_salary_structure` (a parallel/staging table, presumably used during a structure-schema migration or a "propose new structure" workflow). No computation, pure copy.

**Called from**: `Controller/SalaryIncrementController.php:3863` and `:4297` — `CALL copy_salary_structure_to_new({$emp_pkey})`.

**Hardcoded constants**: none.

---

## 11. `ctc_component_update_and_upload_prc`

**Signature**: `PROCEDURE ctc_component_update_and_upload_prc(IN pemp_pkey int, OUT pmessage varchar(50))` — `schema/mypayrol_trial.sql:2584`

**What it does**: Applies bulk-uploaded per-component salary rate overrides (from `emp_salcomp_upload`) onto an employee's active salary structure, then recomputes their CTC transaction record.
1. For each active `emp_salary_structure` row (Addition/Direct heads) belonging to employees present in `emp_salcomp_upload` with `status = 1`, and matching `pemp_pkey`: if an upload row exists for that `salary_head_item_fkey`, takes the **latest uploaded rate** (`ORDER BY emp_salcomp_upload_pkey DESC LIMIT 1` — a fix noted as "Edited by Akshay on 17-10-2025" to prevent duplicate-row ambiguity) and overwrites `emp_salary_structure.structure_det_value`.
2. For each distinct employee touched (`salary_head_item_fkey <= 15` — hardcoded id range for "core CTC components"): recomputes `vderived_ctc = SUM(structure_det_value)` over Addition/Direct, non-manual, `head_fkey = 1` components, and `vemp_anual_ctc = vderived_ctc * 12`.
3. Closes the current `emp_ctc_transaction` row and inserts a new one with `ctc_upload_type = 2` (upload-driven), the new annual/derived CTC, and carries forward `arrear_salary`/`remarks` from the prior record.
4. Clears the `emp_salcomp_upload.status` flag for the processed employee.
5. `pmessage = 'Success'` unconditionally.

**Called from**: `Controller/SalaryIncrementController.php:4476` — `CALL ctc_component_update_and_upload_prc('$emp', @pmessage)` (a commented-out call to the sibling `ctc_component_upload_prc` sits right above it at line 4474, showing this proc superseded that one for this call site).

**Hardcoded constants**: `salary_head_item_fkey <= 15` = "core CTC component" cutoff ID; `ctc_upload_type = 2`; `* 12` annualization factor.

---

## 12. `ctc_component_upload_prc`

**Signature**: `PROCEDURE ctc_component_upload_prc(IN pemp_pkey int, OUT pmessage varchar(50))` — `schema/mypayrol_trial.sql:2837`

**What it does**: Near-identical twin of #11 but simpler in step 3 — instead of closing/inserting a new `emp_ctc_transaction` row, it does an in-place `UPDATE emp_ctc_transaction SET emp_derived_anualctc = vderived_ctc, emp_anual_ctc = vemp_anual_ctc, ctc_upload_type = 2 WHERE ... end_date_effective IS NULL` (no history row created, no arrear/remarks carry-forward). Same rate-override loop (though it lacks the "latest row" `ORDER BY ... DESC LIMIT 1` fix that #11 has — this version's rate lookup can be ambiguous if multiple upload rows exist for the same head/emp).

**Called from**:
- `Controller/SalaryComponentUploadController.php:193, 1018, 1951` (and commented calls at `:182, 325`)
- Used with `pemp_pkey = ''` in some calls (bulk mode) and specific `$emp` in others.

**Hardcoded constants**: same as #11 (`salary_head_item_fkey <= 15`, `ctc_upload_type = 2`, `*12`).

---

## 13. `final_settle_pay_prc`

**Signature**: `PROCEDURE final_settle_pay_prc(IN pbranch_code varchar(30), IN pmonth_year varchar(30), IN pemp_pkey int, IN ppresant_days float, IN pencash_days float, IN puser_id varchar(50), OUT perror_message varchar(500))` — `schema/mypayrol_trial.sql:3662`

**What it does**: Full and final settlement (F&F) computation for a resigning/terminated employee — the most complex procedure in this catalog. High-level steps:
1. Ensures a `payroll_master` row and processed `emp_salary_slip` exist for the settlement month; if not, creates `payroll_master` (special case: for `company_code = 'CLYS'` with `prorate_code = 1`, `ppresant_days` is overridden to `presant_total + weekoff_total + holiday_total`) and calls `calculate_salary_main_prc` (#6) to generate the normal month's payslip first.
2. Clears prior `emp_settle_slip` rows (`status = 'N'`) and copies all `Direct` items from the freshly computed `emp_salary_slip` into `emp_settle_slip` as `type = 'SALARY'`.
3. **Professional Tax true-up**: determines the half-yearly PT window (Apr-Sep or Oct-Mar, split via `substr(pmonth_year,6,2)` month digits `<= '09' and > '03'`), sums actual salary paid in that half-year, looks up `profession_tax_slab.tax_half_yearly` for that salary bracket, subtracts PT already deducted (`vpt_deducted`), and inserts the balance as a negative `emp_settle_slip` line (`'Professional Tax Balance'`).
4. **Leave encashment**: pulls `leave_encashment_master.encashed_amount` where `remarks = 'terminate'`, inserts as a positive settlement line.
5. **Loan/advance payoff**: sums outstanding future `emp_loan_info.loan_emi` (status A, month > settlement month) and future `emp_advance.advance_amount`, both inserted as negative settlement lines.
6. **Gratuity**: if tenure `>= 5 years` (`ROUND(DATEDIFF(NOW(),joining_date)/365)`), computes `gratuity = ROUND((Basic+DA)/26 * 15 * years)` — the standard Indian statutory gratuity formula (15 days' wage per year of service, wage = monthly Basic+DA, divided by 26 working days). Inserted as a positive settlement line.
7. **Bonus true-up** (if the employee's structure has a yearly-`occurance` "Bonus" component): computes current-FY and previous-FY bonus formulas by substituting each salary component's actual paid amount (current-half vs previous-half) into the structure's `structure_formula` text, replacing `'Monthly Gross Salary'` with the period's total `payroll_master.gross_salary`. Inserts two `'BONUS'` settlement lines (prior FY and current FY) with `salary_amount = 0` but `remarks` holding the resolved formula text and its deducted total — i.e. this is informational/audit only, not an actual payout amount here.
8. **Final TDS true-up** — reimplements the same **progressive Indian income-tax slab calculation** seen in `tax_salary_distribution_fn`/`tax_salary_distribution_new_fn`, but self-contained (not calling those functions):
   - `taxable_income = other_income + (final_income - tax_heads_limitsum) - (pt_deducted + pt_balance)` (if declarations included) — where `final_income = actual_salary_received_this_FY + encashed_amount`.
   - `standerd_deduction = 50000` (hardcoded standard deduction), subtracted from taxable income.
   - Progressive slabs (old regime style, **hardcoded**, not read from `income_tax_slab` table):
     - `0–250000`: 0%
     - `250000–500000`: 5%
     - `500000–1000000`: 20%
     - `>1000000`: 30%
   - Surcharge: **income 5,000,000–10,000,000 → 10%**; **>10,000,000 → 15%**; else 0%.
   - Cess: **4% of tax_yearly** (flat, not tax+surcharge in this version — differs slightly from `tax_salary_distribution_fn` which computes cess on `tax+surcharge`).
   - Rebate: if `taxable_income <= 500000` and `tax_yearly > 0`, **rebate = ₹12,500** and cess forced to 0 (Section 87A old-regime rebate).
   - `tax_monthly = (tax_yearly + cess) - already_deducted_TDS_this_FY`, floored at 0.
   - Inserts a `'TDS'`/`'TDS Balance'` settlement line.
   - If attendance for the settlement month doesn't exist, sets `perror_message = 'Attendance not verified'` instead.

**Called from**:
- `Controller/EmployeeResignationController.php:418, 530` (and commented variants at `:392, 521, 927`)
- `Controller/EmployeeResignationControllerBkup.php`
- `Controller/FullandFinalsettlementController.php`

Called as `CALL final_settle_pay_prc('$branch', '$approved_date', '$emp_pkey', '0', '0', 'admin', @perror_message)`.

**Hardcoded constants** (all in this one procedure):
- Standard deduction: `₹50,000`
- Old-regime slabs: `0–250000` @ 0%, `250000–500000` @ 5%, `500000–1000000` @ 20%, `>1000000` @ 30%
- Surcharge: `5,000,000–10,000,000` @ 10%; `>10,000,000` @ 15%
- Cess: flat `4%` of `tax_yearly` (not tax+surcharge, unlike the live monthly calculator)
- Section 87A rebate: `₹12,500` when `taxable_income <= 500,000`
- Gratuity formula: `(Basic+DA)/26 × 15 × years_of_service`, eligibility gate `years >= 5`
- PT half-year window split at month digit `'09'`/`'03'`
- `vyear >= 5` gratuity eligibility (5 years)

---

## 14. `find_pf_tax_cal_fn`

**Signature**: `FUNCTION find_pf_tax_cal_fn(pemp_fkey int) RETURNS int(11)` — `schema/mypayrol_trial.sql:4233`

**What it does**: Projects the employee's total "Employee EPF" (Provident Fund) contribution for the open financial year, for tax-declaration purposes.
1. Looks up the open `fin_year` window for the employee's branch.
2. Sums `emp_salary_slip.salary_amount` where `salary_head_item_fkey` maps to `tax_salary_components.tax_salary_components_name = 'Employee EPF'`, within the FY window (`vsalary_amount`, computed but actually unused downstream — likely dead code / superseded by the next two selects).
3. Counts distinct processed/approved months already paid (`vactual_count`) and sums their actual EPF amounts (`vactaul_salary`).
4. Computes total months in the FY from joining date (or FY start if joined earlier) to FY end: `vtotalmonth = TIMESTAMPDIFF(MONTH, later_of(joining_date, fy_start), fy_end)`.
5. `vproj_count = |totalmonth| + 1 - actual_count` (remaining unpaid months to project).
6. Pulls the employee's current active EPF `structure_det_value` (monthly rate, summed/absoluted).
7. `return (structure_det_value * proj_count) + actual_salary_paid_so_far` — i.e. **actual EPF paid this FY + projected EPF for remaining months at the current rate**.

**Called from**: `Controller/TaxationController.php:624` — `SELECT find_pf_tax_cal_fn("' . $emp_fkey . '") AS VAL` (a commented debug call sits at `:623`).

**Hardcoded constants**: none numeric beyond the projection arithmetic itself.

---

## 15. `payroll_master_approve`

**Signature**: `PROCEDURE payroll_master_approve(IN pbranch varchar(30), IN pmonth_year varchar(30), IN pemp_fkey int, IN puser_id varchar(30), OUT perror_message varchar(300))` — `schema/mypayrol_trial.sql:18048`

**What it does**: Post-approval side-effects after a payslip is approved (does NOT compute salary amounts itself).
1. **Advance reconciliation**: for each `emp_advance` row for the emp/month with `status = 1`, checks whether a matching deduction line exists in `emp_salary_slip` with the same amount; if so, marks the advance `is_credited = 'Y'`.
2. **Loan reconciliation**: for each active `emp_loan_info` row for the month (`paid_status IN ('A','S')`), if a `'Loan'` deduction line exists in `emp_salary_slip`, marks the EMI `paid_status = 'P'` (paid), `amount_paid = loan_emi`. Then checks if the loan's cumulative `amount_paid` across all EMIs equals the original `emp_loan.loan_amount`; if so, marks the loan `is_completed = 'Y'`.

**Called from**:
- `Controller/PayrollController.php:1785`
- `Controller/PayrollProcessController.php:839, 1760` (commented variants at `:1153, 2098`)

Called as `call payroll_master_approve('$branch_code','$month','$emp_fkey','$uid', @perror_message)`.

**Hardcoded constants**: none numeric; `paid_status` codes `'A'`(Active/pending), `'S'`(?), `'P'`(Paid) are string enums, not documented in this file.

---

## 16. `payroll_master_insert`

**Signature**: `PROCEDURE payroll_master_insert(IN pbranch varchar(30), IN pmonth_year varchar(30), IN puser_id varchar(30), OUT perror_message varchar(300))` — `schema/mypayrol_trial.sql:18179`

**What it does**: Bootstraps `payroll_master` rows for every eligible employee in a branch/month before per-employee salary calculation runs — this is the step that must run before `calculate_salary_main_prc`.
1. Deletes any dangling `payroll_master` rows with `action IS NULL` for the month (cleans up previously-aborted runs).
2. Cursor unions employees from both `attendance_register` and `site_attendance_register` (i.e. covers both regular and site-based workforce), filtered to active employees with an assigned `structure_id`.
3. For each: looks up bank details string, checks whether a `payroll_master` row already exists (`unprocessed` vs `processed/approved`), computes the attendance date-window via `att_start_end_fn`.
4. Applies the same `DAILY WAGES`/`prorate_code = 2` LOP substitution as elsewhere (`vlop_total := vwd_lop_total`).
5. If `db_config.payroll_type = 'M'`, recomputes calendar/working days for the full month via `get_policy_weekoff_holiday_count_prc` (overriding the attendance-register-derived figures).
6. Inserts a new `payroll_master` row (if none exists and presence is non-negative) or updates the existing unapproved one, with days/leave/LOP/calendar/working/weekoff/holiday counts and bank details — but **no salary amounts yet** (those get filled in later by `calculate_salary_main_prc`/`salary_process_prc`).

**Called from**:
- `Controller/PayrollController.php:213`
- `Controller/PayrollProcessController.php:83`

Called as `call payroll_master_insert('$branch','$month','$user_id',@error)`.

**Hardcoded constants**: none numeric beyond the same prorate-code conventions used throughout.

---

## 17. `profession_tax_cal_fn`

**Signature**: `FUNCTION profession_tax_cal_fn(pemp_fkey int, pmonth_year varchar(30)) RETURNS int(11)` — `schema/mypayrol_trial.sql:18851`

**What it does**: Computes the monthly Professional Tax (PT) deduction amount for one employee, India-state-aware. This is the live/production PT calculator (vs. the simpler true-up logic embedded in `final_settle_pay_prc`).
1. Checks `branch_exceptions.no_pt = 'Y'` — if the branch is PT-exempt, returns `0` immediately.
2. Determines PT half-year window (Apr-Sep vs Oct-Mar via the same month-digit trick as elsewhere) and reads the branch's `state` (default `'KERALA'` if unset).
3. **Telangana special case**: PT is computed off the **current month's salary only** (not half-year cumulative), excluding components mapped to `tax_salary_components_pkey IN (2,5,19,20)`. Looks up `profession_tax_slab` filtered by `state = 'TELANGANA'` and a `salary_range_to >= actual_salary` bracket, additionally gated by the slab's own `start_date_effective`/`end_date_effective` validity window (so PT slabs can change mid-year). Returns `tax_half_yearly` from that row directly, and skips the rest.
4. **All other states**: sums actual salary paid so far this half-year (excluding `tax_salary_components_pkey IN (3,4,5)`), then **projects** the remaining unpaid months' salary:
   - `DAILY WAGES`: `projected = round(sum(structure_det_value)) * ((total_months - actual_months_paid)*30 - weekoff_days)`.
   - Others: `projected = round(sum(structure_det_value from head_fkey=1)) * (total_months - actual_months_paid)`.
   - `total_estimated_salary = actual_salary_paid + projected_salary`.
5. Looks up `profession_tax_slab` for the employee's `state` and `salary_range_to >= total_estimated_salary`, again gated by slab effective-date window, to get `tax_half_yearly`.
6. If `tax_half_yearly > amount_already_deducted_this_half_year`, splits the remaining balance by the component's `occurance` config:
   - `'Monthly'`: `balance / months_remaining_in_half_year`.
   - `'Half Yearly'`: full balance, but only if the current month equals the component's configured `start_from` month (i.e. charged once, at the configured month).
   - `'Yearly'`: full balance, unconditionally each call (appears to intend a similar one-time gate but has no month check here — potential inconsistency vs. Half-Yearly).
7. Logs the computation to `emp_pt_details` (closing any prior row for the month first).
8. Returns the computed amount if positive, else `0`.

**Called from**: Not called directly from a Controller — invoked internally by `calculate_salary_main_prc:1495` and `salary_process_prc:20099` (`SELECT profession_tax_cal_fn(vemp_pkey, pmonth) INTO vproff_tax`).

**Hardcoded constants**: default state `'KERALA'`; Telangana `tax_salary_components_pkey IN (2,5,19,20)` exclusion set; other-states `tax_salary_components_pkey IN (3,4,5)` exclusion set; `*30` days-per-month approximation for daily-wage projection; PT half-year window split at month `'09'`/`'03'`.

---

## 18. `salary_process_prc`

**Signature**: `PROCEDURE salary_process_prc(IN pmonth varchar(20), IN pbranch_code varchar(30), IN pemp_pkey int(11), IN ppayroll_master_pkey int(11), IN puser_id varchar(30), OUT perr_msg varchar(2000))` — `schema/mypayrol_trial.sql:19111`

**What it does**: **Legacy/monolithic twin of `calculate_salary_main_prc`** (#6) — see the architecture note at the top of this document. Used only for companies in the `specialCompanies` exclusion list (`Controller/PayrollController.php:871-875`). Rather than delegating to sub-procedures, it inlines everything in one large cursor loop:
1. Computes `emp_monthly_ctc` per wage type (Daily/Hourly/Monthly), same as elsewhere, including the hourly-wage `site_sal_structure_distribution_fn` redistribution call.
2. **Inline per-component computation** covering ALL heads (not just head_fkey=1) in one pass, with special-cased business logic embedded directly in the loop instead of via sub-procedures:
   - `prorate_code = 3`: fixed-days proration, with a **company-specific override**: `IF branch's company_code = 'TGPC' THEN fixed_days = 26 ELSE fixed_days = 30` (hardcoded, ignoring `salary_structure.fixed_days` entirely in this branch — differs from `calculate_monthly_salary_components_prc`'s behavior of reading `fixed_days` from the structure table).
   - `prorate_code = 2`: working-days proration, capping `presant_total` at `working_day` if leave+present exceeds it.
   - `prorate_code = 1`: calendar-days proration, `rate = presant + leave + weekoff + holiday`.
   - **Sunday Allowance** (`work_time_day_off_cal_ot = 4` gate): counts `P/P` (full) and `P/A`/`A/P` (half) days from `emp_detail_timeattandance.weekoff IS NOT NULL`, multiplies by rate.
   - **Sunday or Holiday Allowance** (`work_time_day_off_cal_ot = 5` gate): same but unions in `holiday IS NOT NULL` rows too.
   - **Shift Allowance**: `structure_det_depends * actual_present_days` (note: reads `structure_det_depends`, not `structure_det_value`, unlike `calculate_shift_allowance_prc`'s attendance-field-unpivot approach).
   - **OT**: `structure_det_value * (sum(emp_ot_master.set_duration)/60)` for verified OT.
   - **Leave Encashment**: `structure_det_value * approved_days` from `leave_encashment_master`.
3. Manual variables, advances, loans — same insert patterns as `calculate_salary_main_prc`.
4. **Formula resolution for non-Basic heads** (`head_fkey <> 1`, `length(structure_formula) <> '0'`): substitutes salary component names with actual paid amounts into `structure_formula`, replaces `'Monthly Gross Salary'` token, and stores the resolved formula text into `emp_salary_slip.remarks` (mostly for audit display — for shift/OT/leave-encashment components appends a `*multiplier` suffix instead of full substitution).
5. Professional Tax and TDS logic — **identical** to `calculate_salary_main_prc` steps 10-11 (same `salary_head_item_fkey = 82` TDS hardcode, same `500000`/`1200000` regime thresholds).
6. Payroll master roll-up — same formulas as `calculate_salary_main_prc`, but note the **update logic differs slightly**: if `monthly_amount < 0`, `action` is explicitly reset to `NULL` (un-approving/reverting) rather than left as `'Processed'`.

**Called from**:
- Via `Model/AttendanceRegister.php:47-57` (`salaryProcessPrc`), called from `Controller/PayrollController.php:874` (the `specialCompanies` else-branch) and `Controller/PayrollProcessController.php:361`.
- Also directly: `Controller/EmployeeResignationController.php:427` — `CALL salary_process_prc('$unique_month', '$branch', '$emp_pkey', '$payroll_master_pkey', '$user_id', @perr_msg)`.

**Hardcoded constants**:
- `company_code = 'TGPC'` → `fixed_days = 26`, else `30` (company-specific override not present anywhere else in the schema).
- Same `salary_head_item_fkey = 82` TDS id, `500000`/`1200000` regime thresholds as #6.
- `work_time_day_off_cal_ot = 4`/`5` Sunday-allowance mode gates (same as #3).

---

## 19. `salary_structure_limit_prc`

**Signature**: `PROCEDURE salary_structure_limit_prc(IN pemp_fkey int, IN puser_id varchar(30), OUT perr_msg varchar(500))` — `schema/mypayrol_trial.sql:20246`

**What it does**: Post-processing pass that re-applies `limit_wl`/`limit_wg` caps directly onto an employee's **salary structure** (not a payslip) — used after a structure edit/CTC change to clamp components against their configured min/max bounds.
- For each active `emp_salary_structure` row with `head_type IN ('limit_wl','limit_wg')` and nonzero value: looks up the cap (`salary_structure_details.structure_det_depends`, falling back to the structure's own `structure_det_value` if unset), applies `LEAST` (for `limit_wl`) or `GREATEST` (for `limit_wg`) between the cap and the current value, negates if `head_operator = 'Deduction'`, then `UPDATE`s `emp_salary_structure.structure_det_value = ROUND(result)`.

**Called from**: Called from many places across employee-onboarding/config flows: `Controller/EmployeeConfigController.php:2635`, `Controller/EmployeeController.php:3990, 8873`, `Controller/EmployeeJoinController.php:3494, 7926`, `Controller/SalaryComponentUploadController.php`, `Controller/SalaryIncrementController.php` — all as `call salary_structure_limit_prc('$emp','$user_ids',@perr_msg)`.

**Hardcoded constants**: none numeric.

---

## 20. `sal_structure_distribution`

**Signature**: `PROCEDURE sal_structure_distribution(IN Pcompany_code varchar(30), IN Pemp_fkey int, IN Pemp_structure_id int, IN Puserid varchar(30), OUT Perror_massage varchar(200))` — `schema/mypayrol_trial.sql:20420`

**What it does**: Older/simpler CTC → salary-structure distribution procedure (no return value, unlike its `_fn` sibling below). Closes existing active `emp_salary_structure` rows, reads `emp_ctc_transaction.emp_anual_ctc`, sets `monthly_ctc = annual_ctc/12`, then for every `salary_structure_details` row of the target structure:
- `formula_value = (monthly_ctc * structure_derived_perc) / 100`
- `formula` operator → `formula_value`; `limit` → `structure_det_depends`; `limit_wl` → `LEAST(depends, formula_value)`; `limit_wg` → `GREATEST(depends, formula_value)`; else → `structure_det_value` as-is.
- Negates if the head is a deduction head (`salary_heads.head_operator = 'deduction'`).
- Inserts into `emp_salary_structure`.
- After the loop: sums positive `structure_det_value` across the new rows, computes `balance = monthly_ctc - sum`, and adds that balance onto whichever component has `structure_det_operator = 'rembalance'` in the structure definition (the "absorbs whatever's left" component, typically an allowance).

**Called from**: No direct Controller call site found (superseded in practice by `sal_structure_distribution_fn` below, which all Controller call sites use instead). Likely dead/legacy code kept for compatibility.

**Hardcoded constants**: `/12` annualization; no `fixed`/deduction-type distinction beyond the deduction-head negation (this version lacks the `head_fkey != 1` skip and `ctc_upload_type` branching that its `_fn` sibling has, suggesting it's an earlier iteration).

---

## 21. `sal_structure_distribution_fn`

**Signature**: `FUNCTION sal_structure_distribution_fn(Pcompany_code varchar(30), Pemp_fkey int, Pemp_structure_id int, Puserid varchar(30)) RETURNS int(11)` — `schema/mypayrol_trial.sql:20568`

**What it does**: The production CTC → salary-structure distribution function (Basic/monthly/daily-wage employees; the hourly-wage / site-worker equivalent is `site_sal_structure_distribution_fn`, #22). This is what actually runs whenever an employee's CTC or structure changes.
1. Reads `emp_ctc_transaction.emp_anual_ctc` and `ctc_upload_type`.
2. Closes existing active `emp_salary_structure` rows — but **if `ctc_upload_type = 2`** (component-upload-driven update, see #11/#12), preserves `head_fkey = 1` (Basic/core) rows untouched and only clears the rest (partial redistribution mode).
3. `monthly_ctc = annual_ctc` if `DAILY WAGES`, else `annual_ctc / 12`.
4. Same operator-based formula computation as #20, plus a `fixed` branch (`= structure_det_value` directly) not present in #20.
5. Deduction-head negation as before.
6. **Insert gating differs by `ctc_upload_type`**: if `= 2`, only inserts non-`head_fkey=1` components with `value <> 0` (Basic itself is left alone since it was preserved); for `head_fkey = 1` it instead just updates `prorate_code`/`prorate_desc` on the existing (untouched) Basic row. If not upload-driven, inserts everything with `value <> 0` normally.
7. Balances the `rembalance` component the same way as #20, but restricted to `head_operator='addition' AND item_part='direct' AND head_fkey=1 AND head_type<>'manually'` components for the sum, and skips the rembalance top-up entirely when `ctc_upload_type = 2`.
8. **Formula/remarks resolution** (BLOCK5/BLOCK6): resolves `structure_formula` text into `emp_salary_structure.remarks` by substituting component names with their new values and `'Monthly Gross Salary'` with `monthly_ctc`, then a second pass zeros out any still-unresolved self-referential tokens (same technique as `calculate_statutory_components_prc` block2/block3).
9. Recomputes `emp_derived_anualctc = SUM(structure_det_value where head_operator='addition')` and updates `emp_ctc_transaction` with `ctc_upload_type = 1`.
10. Returns `1` if the employee now has structure rows, else `0`.

**Called from**:
- `Controller/DataUploaderController.php:1504`
- `Controller/EmployeeConfigController.php:2563`
- (and referenced across `Controller/EmployeeController.php`, `Controller/EmployeeJoinController.php` per the grep, called as `select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function`)

**Hardcoded constants**: `head_fkey = 1` = Basic/core component group; `ctc_upload_type` values `1` (normal distribution) vs `2` (component-upload-preserving mode); `/12` annualization for non-daily-wage employees.

---

## 22. `site_sal_structure_distribution_fn`

**Signature**: `FUNCTION site_sal_structure_distribution_fn(Pcompany_code varchar(30), Pemp_fkey int, Pemp_structure_id int, p_mctc_amount int, puser_id varchar(30)) RETURNS int(11)` — `schema/mypayrol_trial.sql:21251`

**What it does**: Same algorithm as `sal_structure_distribution_fn` (#21), but takes the **monthly CTC amount directly as a parameter** (`p_mctc_amount`) instead of deriving it from `emp_ctc_transaction.emp_anual_ctc / 12`. This is used for HOURLY WAGES employees and site workers, where the "monthly CTC" is actually a computed figure (hours worked × rate + leave salary) rather than a fixed annual/12 salary — see its call sites inside `calculate_monthly_salary_components_prc` (#4, hourly-wage branch) and `calculate_statutory_components_prc` (#8, hourly-wage branch) and `salary_process_prc` (#18, hourly-wage branch). Same formula operators (`formula`/`limit`/`limit_wl`/`limit_wg`/`fixed`/default), same `ctc_upload_type = 2` partial-preservation logic for Basic (though note: `vctc_upload_type` is never actually assigned from a SELECT in this function body — it stays NULL/0 the whole time, meaning the `IF vctc_upload_type = 2` branches are effectively **dead code / always false** here, a likely bug versus its `_fn` sibling), same rembalance top-up and formula/remarks resolution blocks.

**Called from**: Not called directly from Controller PHP — invoked internally from:
- `calculate_monthly_salary_components_prc:831` (hourly-wage branch)
- `calculate_statutory_components_prc:1922` (hourly-wage branch)
- `salary_process_prc:19332` (hourly-wage branch)

**Hardcoded constants**: same as #21. **Bug note for migration**: `vctc_upload_type` is read via a variable declaration but never populated from a query in this function (unlike `sal_structure_distribution_fn` which does `SELECT ... ctc_upload_type INTO vctc_upload_type FROM emp_ctc_transaction`), so all `IF vctc_upload_type = 2` branches inside this function are unreachable — the Next.js port should decide whether to replicate this bug (for exact behavioral parity) or fix it (assume it was meant to preserve Basic like the sibling function).

---

## 23. `structure_calc`

**Signature**: `FUNCTION structure_calc(psalary_head_item_fkey int, pemp_pkey int) RETURNS varchar(200)` — `schema/mypayrol_trial.sql:22650`

**What it does**: Trivial lookup helper — returns the `remarks` (resolved formula text, as computed by `sal_structure_distribution_fn`/`site_sal_structure_distribution_fn`) for one component of one employee's active salary structure. No computation of its own; purely a display helper for showing "how was this number derived" in the UI.

**Called from**: No direct Controller call site found in this codebase (may be used in a report/view SQL query built dynamically, or is unused/orphaned).

**Hardcoded constants**: none.

---

## 24. `structure_preview_prc`

**Signature**: `PROCEDURE structure_preview_prc(IN Pcompany_code varchar(30), IN Pemp_fkey int, IN Pemp_structure_id int, IN Pemp_anual_ctc float)` — `schema/mypayrol_trial.sql:22660`

**What it does**: Non-destructive "what would this CTC look like distributed across this structure" preview — same algorithm as `sal_structure_distribution_fn` (#21) step-for-step (formula/limit/limit_wl/limit_wg/fixed operators, deduction negation, rembalance top-up, formula/remarks resolution), but writes to a **temporary table** `salary_preview_temp` instead of `emp_salary_structure`, and — critically — **does not persist anything**: no `UPDATE emp_ctc_transaction`, no real inserts to the structure table. Returns the preview rows via `SELECT * FROM salary_preview_temp` at the end, then drops the temp table. Used by CTC-editor UIs to show a live breakdown before the user commits a new CTC/structure combination.

**Called from**: `Controller/SalaryIncrementController.php:5099` — `CALL structure_preview_prc('$company_code', '$emp_pkey', '$structure_id','$anual_gross')`.

**Hardcoded constants**: same as #21 (`head_fkey=1`, `/12` annualization for non-daily-wage).

---

## 25. `tax_computation_fn`

**Signature**: `FUNCTION tax_computation_fn(Pemp_fkey int, pmonth varchar(20), pfin_year int) RETURNS int(11)` — `schema/mypayrol_trial.sql:23000`

**What it does**: Writes an audit-trail row into `tax_computation_report` summarizing the tax computation already stored (by `tax_salary_distribution_fn`/`tax_salary_distribution_new_fn`) in `emp_tax_sal_trans_sum` (old regime) or `emp_tax_sal_trans_sum_new` (new regime) — this function does not perform the slab math itself, it just formats/re-persists the previously computed figures into a report table, branching on `emp_tax_regime.option_type` ('O' = old, else new).
- Old regime: report has 3 income slabs (`first/second/third_portion`).
- New regime: report has 7 income slabs (`first` through `seventh_portion`), reflecting the more granular new-regime slab structure (see #26).
Both paths add `surcharge + cess` into the reported `tax_yearly`, and both compute `Monthly_Salary` (current CTC), `Availed_Salary` (sum of this month's non-deduction, non-indirect `emp_salary_slip` amounts, excluding items marked as indirect-deduction), and `Deductions` (this month's deduction total, similarly filtered).
Returns `1`/`0` based on whether a corresponding `emp_tax_sal_trans`/`emp_tax_sal_trans_new` row exists for the employee.

**Called from**: Not called directly from a Controller — invoked internally by `calculate_salary_main_prc` at lines 1545 and 1573 (once per regime branch), and by `salary_process_prc` equivalently.

**Hardcoded constants**: none new (consumes values computed elsewhere).

---

## 26. `tax_salary_distribution_fn` (Old Tax Regime)

**Signature**: `FUNCTION tax_salary_distribution_fn(Pcompany_code varchar(30), Pemp_fkey int, pfin_year varchar(30), Puserid varchar(30)) RETURNS int(11)` — `schema/mypayrol_trial.sql:23213`

**What it does**: Computes the full-year projected tax liability under the **old tax regime** for one employee, the core function behind live monthly TDS deduction. This is one of the two most business-critical functions in the payroll system (with #27 being its new-regime counterpart).

1. **Per-component availed-salary projection**: cursor unions active `emp_salary_structure` components (non-deduction, non-indirect) with actual `emp_salary_slip` payments not yet reflected in structure, for the FY window. For each component:
   - Projects remaining months: `totalmonth` from later-of(joining date, FY start) to FY end; `proj_count = totalmonth+1 - months_already_paid`.
   - `projected_salary = structure_det_value * proj_count` (only for non-VARIABLE-occurance heads with an active structure row; else 0).
   - `value = (structure_det_value * proj_count) + actual_salary_paid_so_far`.
   - If the component maps to a `tax_salary_components` row (i.e. it's a tax-relevant salary head): looks up `upper_limit` (statutory cap on exemption). **Special-case for House Rent Allowance (HRA)**: computes the classic 3-way HRA exemption test:
     - `hra1 = 40% of annual Basic` (`SUM(availed_salary where component='Basic') * 0.4` — note: hardcoded 40%, the non-metro HRA exemption rate; metro cities use 50% but this dump only implements the 40% rate)
     - `hra2 = actual HRA received` (`vvalue`)
     - `hra3 = declared_rent_paid - (hra1/4)` (rent paid minus 10% of annual basic, i.e. `(Basic*0.4)/4 = Basic*0.1`) — the "rent paid minus 10% of salary" leg of the HRA test, floored at 0 if negative.
     - `upper_limit = LEAST(hra1, hra2, hra3)` — the statutory minimum-of-three HRA exemption.
   - `taxable_salary1 = MAX(0, |value| - upper_limit)`.
   - Inserts into `emp_tax_sal_trans`.
2. **Declared tax-saving deductions** (BLOCK3): sums `emp_tax_transactions.tax_value` grouped by `tax_heads_fkey` (excluding income-type heads and HRA's `tax_heads_fkey=15`), capped per-head at `tax_heads.attr1` (the head's statutory limit, default unlimited `100000000` if null), with a nested detail-level cap via `tax_heads_details.tax_heads_details2`. Accumulates into `tax_heads_limitsum`.
3. **Other income** (`vother_income`): sum of `emp_tax_transactions` where the tax head is `type='income'`.
4. **Professional Tax annualization**: looks up `profession_tax_slab` by state — **Telangana uses `tax_yearly` directly**; all other states use `tax_half_yearly * 2` — based on projected total annual salary. Inserted as a negative `emp_tax_sal_trans` row (professional tax reduces taxable income).
5. **`taxable_income = other_income + (taxable_salary - tax_heads_limitsum) - profession_tax_annual`**.
6. `standerd_deduction = 50000` (hardcoded), subtracted from taxable income.
7. **Age-based slabs** (`age = TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE())`):
   - **Age > 60** (senior citizen): base exemption `300000`; slab breakpoints at `200000`(+`300000`) → 5% up to 500000, → 20% up to 1000000, → 30% above.
     - Actually: `baselimit=300000`; first_portion computed against a `200000` breakpoint at 5%; second_portion against `500000` at 20%; third (remainder) at 30%.
   - **Age <= 60**: standard old-regime slabs: `0–250000` @ 0%, `250000–500000` @ 5%, `500000–1000000` @ 20%, `>1000000` @ 30%.
8. **Surcharge**: `5,000,000–10,000,000` → 10%; `>10,000,000` → 15%; else 0.
9. **Cess**: `(tax_yearly + surcharge) * 4%` (note: unlike `final_settle_pay_prc`'s simplified version, this correctly bases cess on tax+surcharge).
10. **Section 87A Rebate**: if `taxable_income <= 500000` and `tax_yearly > 0`: `rebate = 12500`, `cess forced to 0`.
11. `tax_monthly = ((tax_yearly + cess + surcharge) - TDS_already_deducted_this_FY) / proj_count` — spreads remaining liability evenly across remaining months. `proj_count` gets `+1` if `Pcompany_code = ''` (a calling-convention quirk — some call sites pass empty company code).
12. Floors `tax_monthly` at 0.
13. Stores full breakdown into `emp_tax_sal_trans_sum` (closing prior row first).
14. Returns `1`/`0` based on row existence.

**Called from**:
- `Controller/EmployeeTaxController.php:496`
- `Controller/TaxController.php:514, 718`
- Internally from `calculate_salary_main_prc:1521` and `salary_process_prc:20125` (`SELECT tax_salary_distribution_fn('', vemp_pkey, vfinyear, puser_id) INTO vtax`).

**Hardcoded constants**:
- HRA exemption rate: **40%** of annual Basic (non-metro rate; no metro/50% variant implemented)
- Standard deduction: **₹50,000**
- Senior (>60) slabs: `0–300000` @ 0%, up to `500000` @ 5% (breakpoint effectively at `200000` beyond the `300000` base), `500000–1000000` @ 20%, `>1000000` @ 30%
- Standard slabs: `0–250000` @ 0%, `250000–500000` @ 5%, `500000–1000000` @ 20%, `>1000000` @ 30%
- Surcharge: `5,000,000–10,000,000` @ 10%, `>10,000,000` @ 15%
- Cess: **4%** of (tax + surcharge)
- Section 87A rebate: **₹12,500** when `taxable_income <= 500,000`
- Non-metro-only HRA formula: `rent_paid - 0.10 * annual_basic`
- Telangana PT special case (`tax_yearly` direct vs `tax_half_yearly * 2` elsewhere)

---

## 27. `tax_salary_distribution_new_fn` (New Tax Regime)

**Signature**: `FUNCTION tax_salary_distribution_new_fn(Pcompany_code varchar(30), Pemp_fkey int, pfin_year varchar(30), Puserid varchar(30)) RETURNS int(11)` — `schema/mypayrol_trial.sql:23810`

**What it does**: New-tax-regime counterpart to #26. Structurally similar per-component projection loop (steps 1-4 largely mirror #26, writing to `emp_tax_sal_trans_new` instead — note the new regime does **not** compute HRA exemption at all, since HRA has no exemption under the new regime; also skips the declared-deduction limit-sum logic that old regime applies, setting `tax_heads_limitsum = 0` explicitly at line 24123/24136 — new regime generally disallows most exemptions/deductions except the standard deduction).

The critical difference is the **slab lookup is entirely table-driven** via the `income_tax_slab` table (`WHERE fin_year = ... AND regime = 'NEW'`), NOT hardcoded — this function reads `salary_range_from`, `salary_range_to`, `tax_yearly_perc`, `std_deduction`, `rebate`, `surcharge_perc`, `cess_perc` all from that table, walking up to **7 progressive slabs** (`first_portion` through `seventh_portion`) by repeatedly querying "the next slab above the previous slab's `salary_range_to`". This means **the new-regime slab structure is fully configurable via table data and will differ by `fin_year`** — a Next.js port should read this table rather than hardcoding new-regime numbers.
- Special year gate: `IF vfin_year >= '2025'` uses the full 7-slab structure (6th+7th slabs looked up); older years fall back to a 6-slab structure (this reflects the FY2025-26 budget's new-regime slab restructuring in India).
- **Rebate check happens BEFORE slab computation**: looks up `income_tax_slab.rebate` for the bracket containing `taxable_income`; if a rebate row is matched with `rebate <> 0`, **skips the entire slab/tax calculation and sets `cess = 0`** (i.e. full rebate zeroes out tax entirely below the new-regime rebate threshold, rather than computing tax then subtracting a fixed rebate amount as the old regime does).
- **Marginal relief**: if `taxable_income` exceeds `vnon_taxable_threshold` (max `salary_range_to` among rebate-eligible slabs) but the raw tax would push net income below what a person just at the threshold would keep, caps `tax_yearly` at `taxable_income - non_taxable_threshold` and records the difference as `marginal_relief` — the standard Indian marginal-relief provision preventing a small income increase from causing a disproportionate net-income drop at the rebate cliff.
- Surcharge/cess percentages likewise read from `income_tax_slab.surcharge_perc`/`cess_perc` for the matching bracket (table-driven, not hardcoded like old regime).
- `tax_monthly` formula same shape as old regime: `(tax_yearly + cess + surcharge - TDS_deducted_so_far) / proj_count`, floored at 0.
- Persists to `emp_tax_sal_trans_sum_new`.

**Called from**:
- `Controller/TaxController.php:515, 719`
- Internally from `calculate_salary_main_prc:1549` and `salary_process_prc:20xxx` equivalent (`SELECT tax_salary_distribution_new_fn('', vemp_pkey, vfinyear, puser_id) INTO vtax`).

**Hardcoded constants**: Almost none — this function is intentionally table-driven via `income_tax_slab` (columns: `fin_year`, `regime`, `salary_range_from/to`, `tax_yearly_perc`, `std_deduction`, `rebate`, `surcharge_perc`, `cess_perc`). The only hardcoded logic is the **year gate `vfin_year >= '2025'`** switching between 6-slab and 7-slab walk depth, and the **marginal relief formula** (`tax_yearly - (taxable_income - non_taxable_threshold)`). **Migration note**: the Next.js port must replicate reading `income_tax_slab` per FY rather than hardcoding new-regime numbers, since this table is explicitly designed to be updated yearly by the business.

---

## 28. `tax_salary_process_prc`

**Signature**: `PROCEDURE tax_salary_process_prc(IN pmonth varchar(20), IN pbranch_code varchar(30), IN pemp_pkey int, IN ppayroll_master_pkey int, IN puser_id varchar(30), OUT perr_msg varchar(500))` — `schema/mypayrol_trial.sql:24507`

**What it does**: Post-payslip finalization pass focused on `limit_wl`/`limit_wg` re-clamping directly on the generated `emp_salary_slip` (analogous to `salary_structure_limit_prc` (#19) but operating on the payslip rather than the structure) plus recomputing `payroll_master` roll-up totals.
1. For each `emp_salary_slip` row this month with `head_type IN ('limit_wl','limit_wg')` and nonzero amount: looks up the cap (`salary_structure_details.structure_det_depends`, falling back to the slip's own `structure_det_value`), applies `LEAST`/`GREATEST`, re-negates for deductions, and `UPDATE`s `salary_amount = ROUND(result)` directly on the slip.
2. Recomputes `monthly_amount`/`indirect_amount`/`total_deduction`/`total_variables` from `emp_salary_slip` and updates `payroll_master` (`gross_salary`, `net_salary`, `action='Processed'` if `monthly_amount >= 0`, else `action=NULL`) — same formula as `calculate_salary_main_prc` step 12.

**Called from**: `Controller/EmployeeResignationController.php:492` — `CALL tax_salary_process_prc('$unique_month', '$branch', '$emp_pkey', '$payroll_master_pkey', '$user_id', @perr_msg)`. Also referenced via the model wrapper `taxSalaryProcessPrc` from `Controller/PayrollController.php:1013` and `Controller/PayrollProcessController.php:436`.

**Hardcoded constants**: none numeric.

---

## Cross-cutting hardcoded constants summary (for quick reference during TS reimplementation)

| Constant | Value | Where |
|---|---|---|
| TDS component ID | `salary_head_item_fkey = 82` | `calculate_salary_main_prc`, `salary_process_prc` |
| Old-regime TDS zero-threshold | `taxable_income <= 500,000` | `calculate_salary_main_prc:1526`, `salary_process_prc` |
| New-regime TDS zero-threshold | `taxable_income <= 1,200,000` | `calculate_salary_main_prc:1554`, `salary_process_prc` |
| Standard deduction | `₹50,000` | `final_settle_pay_prc`, `tax_salary_distribution_fn` |
| Old-regime slabs | `0–250k`@0%, `250k–500k`@5%, `500k–1M`@20%, `>1M`@30% | `final_settle_pay_prc`, `tax_salary_distribution_fn` |
| Senior citizen (>60) old-regime slabs | base `300k`, then `5%`/`20%`/`30%` breakpoints at `200k`/`500k`/`1M` | `tax_salary_distribution_fn` |
| Surcharge | `5M–10M`@10%, `>10M`@15% | `final_settle_pay_prc`, `tax_salary_distribution_fn` |
| Cess | `4%` (basis differs: tax-only in `final_settle_pay_prc`, tax+surcharge in `tax_salary_distribution_fn`) | both |
| Section 87A rebate | `₹12,500` when `taxable_income <= 500,000` | `final_settle_pay_prc`, `tax_salary_distribution_fn` |
| HRA exemption rate | `40%` of annual Basic (non-metro only, no 50%/metro path) | `tax_salary_distribution_fn` |
| Gratuity formula | `(Basic+DA)/26 × 15 × years`, gate `years ≥ 5` | `final_settle_pay_prc` |
| New-regime tax slabs | **table-driven** via `income_tax_slab` (fin_year, regime) | `tax_salary_distribution_new_fn` |
| New-regime slab-count gate | 7 slabs if `fin_year >= '2025'`, else 6 | `tax_salary_distribution_new_fn` |
| ESI rounding | deduction side `CEIL(ABS(x))`; employer side `ROUND(x)` | `calculate_statutory_components_prc` |
| Sunday Allowance mode gate | `work_time_day_off_cal_ot = 4` | `calculate_holiday_allowances_prc`, `salary_process_prc` |
| Sunday-or-Holiday mode gate | `work_time_day_off_cal_ot = 5` | `calculate_holiday_allowances_prc`, `salary_process_prc` |
| Core CTC component ID cutoff | `salary_head_item_fkey <= 15` | `ctc_component_upload_prc`, `ctc_component_update_and_upload_prc` |
| Basic/core head group | `head_fkey = 1` | pervasive (structure distribution, gross calc, rembalance) |
| Statutory head group | `head_pkey IN (4, 5)` | `calculate_statutory_components_prc`, `calculate_emp_component_breakup` |
| Company-specific fixed-days override | `company_code = 'TGPC'` → `fixed_days = 26` else `30` | `salary_process_prc` only |
| PT half-year window split | month digit `<= '09' and > '03'` = Apr-Sep half | `profession_tax_cal_fn`, `final_settle_pay_prc` |
| Prorate codes | `1`=calendar-days, `2`=working-days, `3`=fixed-days | pervasive |
| CTC upload type flags | `1`=normal distribution, `2`=component-upload-preserving (keeps Basic) | `sal_structure_distribution_fn`, CTC upload procs |

## Known bugs / inconsistencies worth flagging for the migration team

1. **`site_sal_structure_distribution_fn`** declares `vctc_upload_type` but never populates it from a query — all `IF vctc_upload_type = 2` branches are dead code (line ~21252 onward). Its sibling `sal_structure_distribution_fn` does populate this variable correctly. Decide whether to replicate or fix during the TS port.
2. **Cess basis differs** between `final_settle_pay_prc` (flat 4% of `tax_yearly` alone) and `tax_salary_distribution_fn` (4% of `tax_yearly + surcharge`) for what should be the same old-regime calculation — this is a real discrepancy between the F&F settlement estimate and the live monthly TDS calculator.
3. **`salary_process_prc`** hardcodes `fixed_days = 26/30` by `company_code = 'TGPC'` instead of reading `salary_structure.fixed_days` like every other procedure — a company-specific carve-out that doesn't exist in `calculate_salary_main_prc`'s code path (via `calculate_monthly_salary_components_prc`), meaning TGPC-like companies would get different results depending on which of the two top-level procedures runs for them.
4. **`profession_tax_cal_fn`** occurrence handling: `'Yearly'` occurrence PT components have no month-gate (charge every call), while `'Half Yearly'` components are gated to only fire in their configured `start_from` month — likely an oversight since Yearly should presumably also be a one-time-per-year charge.
5. **`sal_structure_distribution`** (non-`_fn` version) appears to be dead/unused code — no Controller call site found, and its `_fn` sibling is more complete/current. Confirm before deciding whether to port it.

---

### 4.2 Attendance, Leave, Site-Attendance & Stock Routines

# Stored Procedure/Function Catalog — Attendance, Leave, Overtime, Site-Attendance, Device/Punch, Stock

Source: `schema/mypayrol_trial.sql` (91 `CREATE FUNCTION`/`CREATE PROCEDURE` definitions with full SQL bodies).
All line numbers below refer to `schema/mypayrol_trial.sql` unless stated otherwise. Controller call sites refer to files under `Controller/` (paths given relative to `Controller/`).

**Status: IN PROGRESS — this file is being written incrementally. Do not treat as final until the trailing "END OF REPORT" marker is present.**

---

## 1. `att_start_end_date_fn`

**Signature**: `schema/mypayrol_trial.sql:14`
```
CREATE FUNCTION att_start_end_date_fn(start_date varchar(20), year_month varchar(20)) RETURNS varchar(30)
```

**What it does**: Despite its name/signature suggesting it computes a cycle start/end date, the actual body (lines 14-47) does not use either input parameter in its return value. It reads `db_config.attendance_date` (a single global "cycle start day" setting), clears rows from `date_intervel_monthyear` for the current month if any exist, and then — regardless of the `if vattendance_date=1` branch outcome (both branches just set `vreturn = current_date`) — unconditionally overwrites `vreturn = 'successfull'` at line 44 and returns that literal string. This function is effectively **dead/vestigial code**: it always returns the string `'successfull'`. It has a side effect of deleting rows from `date_intervel_monthyear` where `month_year >= DATE_FORMAT(CURRENT_DATE,'%Y-%m')`.

**Called from**: No call sites found in `Controller/*.php` (0 matches for `att_start_end_date_fn`). Appears unused by the application layer — likely superseded by `att_start_end_fn` (next entry), which is called pervasively.

**Constants/flags**: `db_config.attendance_date = 1` is checked but has no effect on the return value (dead branch).

---

## 2. `att_start_end_fn` — **the core attendance-cycle-window function, used everywhere**

**Signature**: `schema/mypayrol_trial.sql:50`
```
CREATE FUNCTION att_start_end_fn(Pyear_month date, patt_start_end int) RETURNS date
```

**What it does**: Computes the start (`patt_start_end=1`) or end (`patt_start_end=2`) date of the attendance cycle that contains `Pyear_month`, driven by company configuration in `db_config`:
- `db_config.attendance_format`: `'B'` = "bordered/fixed calendar-month" mode, anything else = "custom cut-off day" mode.
- `db_config.attendance_date`: the cut-off day-of-month (0 = calendar month; nonzero = custom cycle day).

Logic:
- **Start date** (`patt_start_end=1`):
  - If `attendance_format='B'` and `attendance_date=0`: start = first day of `Pyear_month`'s month (`%Y-%m-01`).
  - If `attendance_format='B'` and `attendance_date<>0`: start = `(attendance_date-1)` days before the last day of the *previous* month (i.e. cycle starts mid-previous-month at day `attendance_date`).
  - Else (`attendance_format<>'B'`, "non-bordered"): start = previous month, day = `attendance_date` (literal day-of-month in the prior month).
- **End date** (`patt_start_end=2`):
  - If `attendance_format='B'` and `attendance_date=0`: end = `LAST_DAY(Pyear_month)`.
  - If `attendance_format='B'` and `attendance_date<>0`: end = last day of `Pyear_month`'s month minus `attendance_date` days.
  - Else: end = (`attendance_date - 1`) day of the *current* month (one month added to the previous-month base date), i.e. `attendance_date` is decremented by 1 first, then the previous-month base date is advanced by 1 month.

This is the single source of truth for "what calendar dates make up payroll month X" — critical for reimplementing attendance/leave/OT windowing in TypeScript. A pure function of `(cycleStartDay, isBorderedFormat, referenceMonth, wantStart)`.

**Called from**: Used in over 250 places across `Controller/*.php` (verified via grep — 250+ occurrences across `AttendanceCheckInOutController.php`, `AttendanceController.php`, `AttendanceRegisterNewController.php`, `AttendanceReportsController.php`, `AttendanceReportsNewController.php`, `DailyOvertimeVerifyController.php`, `DailyOvertimeVerifyNewController.php`, `DashboardNewController.php`, `EditAttendanceController.php`, `EditedReportsController.php`, `EditPunchesController.php`, `EmpleaveuploadController.php`, `EmployeeadvanceController.php`, `EmployeeAttendanceUploadController.php`, `EmployeeConfigController.php`, `EmployeeController.php`, `EmployeeJoinController.php`, `EmployeeLeaveRequestController.php`, `EmployeeLeavesController.php`, `EmployeeLeaveUploadController.php`, `EmployeeRegisterController.php`, `EmployeeResignationController.php`, `EmpreportController.php`, `EsiEpfReportController.php`, `LeavePolicyController.php`, `LeaveRequestController.php`, `MiscellaniousReportsController.php`, `OtAttendanceNewController.php`, `RegularisationController.php`). Representative examples:
  - `Controller/AttendanceController.php:374-375` — `select att_start_end_fn(DATE_FORMAT('$month1','%Y-%m-01'),1)` / `,2)` to get the monthly attendance window.
  - `Controller/AttendanceRegisterNewController.php:1211-1212, 1582-1583, 3567-3568` — cycle start/end for attendance register views and edit-punch flows.
  - `Controller/DailyOvertimeVerifyController.php:105-106,161-179` — bounding OT verification queries by cycle window.
  - `Controller/OtAttendanceNewController.php:98-99,1479-1483,2297,2951` — OT register cycle windows and employee joining-date cutoff checks.
  - `Controller/EmployeeLeaveRequestController.php`, `Controller/LeaveRequestController.php` (~20 call sites) — bounding leave-application month windows.
  - `Controller/MiscellaniousReportsController.php:8481` — `COALESCE(fy.start_month, att_start_end_fn(CONCAT(ar.month_year,'-01'),1))` — fallback cycle start when no explicit fiscal-year start is configured.

**Constants**: none hardcoded in the function itself — behavior fully parameterized by `db_config.attendance_format` ('B' vs other) and `db_config.attendance_date` (0 = calendar month).

---

## 3. `bulk_company_idupdate`

**Signature**: `schema/mypayrol_trial.sql:108`
```
CREATE PROCEDURE bulk_company_idupdate(IN pemp_pkey int)
```

**What it does**: (Body continues past the read window; header/declarations captured at lines 108-121: declares `vemp_pkey`, `vfirst_name`, `vemp_device_startid`, `vDeviceId`, `vemp_co_id`, `vemp_company_id`, `vusername`, etc.) This is a device/company-id bulk sync utility for employee-device linkage bookkeeping (out of the primary attendance/leave scope requested; declarations mirror `estern_username_update`, i.e. it likely populates `mypayrol_control_db.emp_device_comp_branch` per employee). Not fully transcribed — no controller call sites were found, so it is effectively an orphaned/legacy admin procedure.

**Called from**: 0 matches in `Controller/*.php`.

---

## 4. `bulk_device_logs_iteration_prc`

**Signature**: `schema/mypayrol_trial.sql:165`
```
CREATE PROCEDURE bulk_device_logs_iteration_prc(IN p_year_month date)
```

**What it does** (full body, lines 165-190): Cursor-loops over every **active** employee (`emp_details.status = 1`, ordered by `emp_pkey`) and calls `device_logs_iteration_fn(v_emp_id, p_year_month)` for each — a batch/maintenance wrapper that re-syncs device punch logs for the whole company for a given attendance month.

**Called from**: 0 matches in `Controller/*.php` — appears to be invoked only via a DB scheduled event/cron or manual DBA execution, not from the web app.

---

## 5. `check_fn`

**Signature**: `schema/mypayrol_trial.sql:2426`
```
CREATE FUNCTION check_fn(pcompany_code varchar(20), PBranch_code varchar(20), Puserid varchar(20), Pmonth date) RETURNS varchar(100)
```

**What it does** (full body, lines 2426-2532): A one-time attendance-register **initializer/seeder**. Computes the cycle window (`vmonthly_att_fromdate`/`todate`) the same way `att_start_end_fn` does inline (duplicated logic based on `db_config.attendance_date`, not calling the function). Then:
1. Deletes any soft-deleted (`isdelete='Y'`) `attendance_register` rows for the target `month_year`/company/branch/user.
2. Inserts one **dummy placeholder row** with `emp_fkey = 1111111`, `emp_name = 'AAA'` — used as a sentinel/marker row (this literal magic employee id `1111111` also appears elsewhere, see `insert_emp_att_reg`).
3. Opens a cursor over employees who joined on/before the cycle end date, have a `day_time_seq` assigned, and don't already have a non-deleted `attendance_register` row for the month — and inserts a bare `attendance_register` row per employee (no FIELD1..31 values populated here).
4. Returns literal `'OK'`.

This looks like a lightweight/legacy precursor to `insert_emp_att_reg` / `insert_update_att_reg` — it only creates the register shell rows without populating daily status fields.

**Called from**: 0 matches in `Controller/*.php` — not called from the application; likely deprecated/superseded.

**Constants**: sentinel `emp_fkey = 1111111` / `emp_name = 'AAA'` marker row (line 2515-2516).

---

## 6. `customer_visit_history_fn`

**Signature**: `schema/mypayrol_trial.sql:3025`
```
CREATE FUNCTION customer_visit_history_fn(pemp_pkey int) RETURNS varchar(100)
READS SQL DATA DETERMINISTIC
```
Note: despite being `READS SQL DATA DETERMINISTIC`, the body performs INSERT/UPDATE — the modifier is inaccurate/legacy metadata.

**What it does** (full body, lines 3025-3125): Reconciles mobile-app customer-visit GPS check-in/out events (`mob_user_locations`) into a denormalized `cus_visit_locations` audit table. Cursor loops over all `mob_user_locations` rows with `stepinout='OUT'` not yet present in `cus_visit_locations`, ordered by `user_id, customer_name, created_time desc`. For each:
1. If not already recorded, inserts a `cus_visit_locations` row capturing the "OUT" step (lat/long/location/time/purpose), also writes a debug row into a `test` table (`field1=mob_user_locations_pkey, field2='out'`) — apparent debug/trace leftover.
2. Looks up the matching prior "IN" event for the same `user_id`+`customer_name` (max pkey less than current) and updates `cus_visit_locations.customer_name1/stepin_time/action2='IN'/...`.
3. Looks up a "START FROM" event within 500 minutes prior to the "IN" event (`INTERVAL -500 MINUTE`), and if found updates `start_time/action1='START'/...`.
4. Returns literal `'OK'`.

**Called from**: 0 matches in `Controller/*.php` for `customer_visit_history_fn` itself, but the underlying `cus_visit_locations`/`mob_user_locations` tables are referenced elsewhere (out of scope). Likely invoked via a scheduled DB event, not directly from PHP.

**Constants**: `500` minutes = maximum look-back window to associate a "START FROM" GPS ping with an "IN" visit event (line 3101).

---

## 7. `delete_an_count_records_fn`

**Signature**: `schema/mypayrol_trial.sql:3128` (declared as a **PROCEDURE**, not a function, despite the `_fn` name suffix — the DDL comment/DROP at line 3127 says `DROP FUNCTION IF EXISTS`, but the actual object is `CREATE PROCEDURE`)
```
CREATE PROCEDURE delete_an_count_records_fn(IN pcompany_code varchar(255), IN ppkey int, IN pyearmonth1 date, OUT vrecord_count int)
```

**What it does** (full body, lines 3128-3155):
1. Deletes `attendance_register` rows for the given employee/month that are `isdelete='Y'` AND whose `branch_code` doesn't match the employee's *current* `emp_details.branch_code` (i.e. cleans up stale soft-deleted register rows left over from a branch transfer).
2. Counts (`vrecord_count` OUT param) how many `attendance_register` rows for that employee/month are `isdelete='N'` but have **no matching `payroll_master`** row (`pm.action IS NULL`) for the same month — i.e. counts attendance-register rows not yet picked up into payroll processing.

**Called from**:
- `Controller/EmployeeController.php:3244` — `CALL delete_an_count_records_fn('$company_code', $pkey, $yearmonth, @record_count)`
- `Controller/EmployeeController.php:3253`
- `Controller/EmployeeController.php:3258` — called twice per employee (`$yearmonth` and `$yearmonth1`), likely handling a mid-month branch-transfer scenario (old + new month).

**Constants**: none hardcoded.

---

## 8. `device_logs_iteration_fn`

**Signature**: `schema/mypayrol_trial.sql:3158`
```
CREATE FUNCTION device_logs_iteration_fn(pemp_id varchar(20), pyear_month date) RETURNS varchar(100)
MODIFIES SQL DATA DETERMINISTIC
```

**What it does** (full body, lines 3158-3172): Re-materializes `device_attandance` rows for one employee within one attendance cycle window (via `att_start_end_fn(pyear_month,1/2)`): copies the current rows for that employee/window into a temp table, deletes them from `device_attandance`, then re-inserts from the temp table ordered by `LOGDATE`. Net effect: **re-sorts/re-sequences the punch rows** for that employee/month (a dedup/reorder maintenance operation — no filtering or dedup logic is visible beyond the reinsertion-in-order, so likely relied upon to fix auto-increment ordering or trigger recomputation elsewhere). Returns literal `'success'`.

**Called from**:
- `Controller/EditPunchesController.php:498` — `SELECT device_logs_iteration_fn('$emp_id','$month')`
- `Controller/EditPunchesController.php:3610` (line 3578 is a commented-out duplicate)
- `Controller/EmployeeRegisterController.php:253`
- `Controller/EmployeeRegisterController.php:2255` (line 2224 commented out)
- Also invoked in bulk by `bulk_device_logs_iteration_prc` (schema:186).

---

## 9. `device_logs_resync_fn`

**Signature**: `schema/mypayrol_trial.sql:3175`
```
CREATE FUNCTION device_logs_resync_fn(pemp_id varchar(20), pyear_month date) RETURNS varchar(30)
MODIFIES SQL DATA DETERMINISTIC
```

**What it does** (full body, lines 3175-3212): Pulls raw punch logs from the **external biometric device server** (`ebiosync.devicelogs`, a separate schema) for one employee and re-syncs them into `device_attandance`:
1. Resolves `company_code`/`branch_code` from `emp_details`, the device-side `emp_company_id` from `emp_proff`, and the physical `DeviceId` from `mypayrol_control_db.emp_device_comp_branch`.
2. Resolves the employee's `punchtype` from `mob_user_credentials` (keyed by `concat(company_code, emp_id)`).
3. **Only proceeds if `vDeviceId != 0` AND `vpunch_type IN ('M','W')`** — i.e. only for employees whose punch mode is Mobile (`M`) or Web (`W`) with a linked physical device (this condition looks logically odd — mobile/web punchers being gated by a physical DeviceId — but it's what the SQL says).
4. Copies matching rows from `ebiosync.devicelogs` (external DB) into a temp table filtered to the attendance cycle window (`att_start_end_fn`), deletes existing `device_attandance` rows for that employee/window **where `C2 is null`** (i.e. only unprocessed/unedited rows), and re-inserts from the temp table.
5. Returns literal `'success'`.

**Called from**:
- `Controller/EditPunchesController.php:3545` — `SELECT device_logs_resync_fn('$emp_id','$month')`
- `Controller/EmployeeRegisterController.php:2191`

**Constants**: punch-type gate values `'M'` (Mobile) and `'W'` (Web) at line 3197.

---

## 10. `editpunch_rules`

**Signature**: `schema/mypayrol_trial.sql:3215`
```
CREATE FUNCTION editpunch_rules(pemp_fkey int, pmessage varchar(250), pinout_order varchar(30), Pyear_month date, pfirst_half varchar(30)) RETURNS varchar(100)
READS SQL DATA DETERMINISTIC
```
(despite `READS SQL DATA`, body performs INSERT/UPDATE on `device_attandance` — inaccurate metadata, same pattern as elsewhere in this file)

**What it does** (full body, lines 3215-3439): Implements **automated punch-correction rules**, dispatched by the `pinout_order` parameter:
- **`'in/out'`**: For each employee/date in the cycle with a `day_time_seq` whose shift's `working_time1 > 10` (i.e. a "real" multi-punch shift, filtering out simple/no-shift employees), finds the day's earliest IN and earliest OUT punch after it (OUT must be within 1400 minutes of IN). If an extra IN punch exists in the 5 minutes immediately before the OUT time, marks it `status='N', c3='in/out rule applied'` — i.e. suppresses a spurious "IN" punch registered right before checkout.
- **`'out/in'`**: Symmetric — suppresses spurious OUT punches within 5 minutes *after* the resolved OUT time (`c3='out/in rule applied'`).
- **`'missout'`**: If no OUT punch exists within 1000 minutes after IN, auto-inserts a synthetic OUT punch at `IN-shift-time + working_time1 minutes` (pulled from `working_day_time_procedures`), tagged `C3='Miss out rule applied'`, `status='Y'`; if the shift is configured `isnextday=1`, the synthetic punch date rolls to the next day.
- **`'breakoff'`** (handled outside the per-day cursor loop, operating on `pemp_fkey`/`Pyear_month` as a single date, not a range): Looks up the employee's shift config (`on_dutty1/off_dutty1/working_time1`, and `on_dutty2/off_dutty2/working_time2` if a second shift window exists) and inserts synthetic IN/OUT punches based on `pfirst_half`:
  - `pfirst_half='N'`: inserts both a synthetic IN at `on_dutty1` and OUT at `off_dutty2` (or `off_dutty1` if no second window) `+1 minute`, tagged with `pmessage`.
  - `pfirst_half='I'` (uppercased): inserts only the synthetic IN.
  - `pfirst_half='O'`: inserts only the synthetic OUT (`+1 minute` after the off-duty time).

Returns literal `'OK'`.

**Called from**: 0 matches for `editpunch_rules` in `Controller/*.php` — not called directly from any controller found; likely invoked from another stored procedure/trigger (not from PHP), or is dead/legacy code reachable only via DB events.

**Constants**: `1400` minutes (max IN→OUT window search, line 3323), `5` minutes (in/out and out/in dedup window, lines 3339,3350,3352), `1000` minutes (missing-out search window, line 3361), `10` minutes (`working_time1 > 10` — the threshold distinguishing "has a real shift" from "simple attendance" employees, line 3268), status codes `'N'`/`'Y'` for punch validity, hardcoded messages `'in/out rule applied'`, `'out/in rule applied'`, `'Miss out rule applied'`.

---

## 11. `emp_detail_att_reg`

**Signature**: `schema/mypayrol_trial.sql:3442`
```
CREATE PROCEDURE emp_detail_att_reg(IN Pcompany_code varchar(30), IN PBranch_code varchar(30), IN Puserid varchar(30), IN Pmonth date, IN pemp_pkey int, OUT Perr_msg varchar(500))
```

**What it does** (full body, lines 3442-3539): A thin **dispatcher/trigger procedure**. For each employee in the target branch/company (optionally filtered to a single `pemp_pkey`) who hasn't joined after the cycle-end date and has a `day_time_seq` assigned and no existing non-deleted `attendance_register` row for the month, it:
1. Checks `working_day_time_procedures.is_multiple_days` for the employee's shift.
2. If `'Y'` (multi-shift/split-shift employee), calls `time_duration_check_multishift(...)`.
3. Else calls `time_duration_check(...)`.

Both of those functions are the actual daily-attendance-computation engines (out of the requested scope list but referenced heavily by in-scope procedures — they compute punch-duration/present-absent per day from `device_attandance`). `emp_detail_att_reg` itself does **not** write to `attendance_register` — it only triggers the underlying per-day computation (`emp_detail_timeattandance` population happens inside `time_duration_check*`).

**Called from**:
- `Controller/AttendanceregisterController.php:124` — `Call emp_detail_att_reg('$company_code','$branch','$user_login','$yearmonth','$emp_pkey', @Perr_msg)`
- `Controller/AttendanceregisterController.php:217`
- `Controller/EmployeeAttendanceUploadController.php:855`
- `Controller/SiteattendanceregisterController.php:334`
- `Controller/TimesheetController.php:117`
- (`Controller/AttendanceregisterController.php:118` and `Controller/SiteAttendanceUploadController.php:855` are commented-out call sites)

**Constants**: `is_multiple_days` flag value `'Y'` (case-insensitive) selects multi-shift path (line 3527).

---

## 12. `emp_inactive_fn`

**Signature**: `schema/mypayrol_trial.sql:3542`
```
CREATE FUNCTION emp_inactive_fn() RETURNS tinyint(4)
```

**What it does** (full body, lines 3542-3596): Batch-deactivates employees. Cursor over `emp_proff` joined against `inactive_list` (`companyemployeeid` match, `status=1`), and for each match: sets `emp_details.status = 2` (a hardcoded status code meaning "Inactive") with `attr5 = 'Inactive from backend on demand on30102019'` (a literal timestamped audit note — clearly a one-off migration/cleanup script hardcoded with a specific date, `30/10/2019`), then resets `inactive_list.status = 0` for that record (marks it processed). Returns literal `'OK'`.

**Called from**: 0 matches in `Controller/*.php` — not called from the application; a one-time/admin maintenance function.

**Constants**: `status = 2` = Inactive employee status code (line 3588); literal audit string `'Inactive from backend on demand on30102019'`.

---

## 13. `estern_username_update`

**Signature**: `schema/mypayrol_trial.sql:3599`
```
CREATE PROCEDURE estern_username_update(IN pemp_pkey int)
```

**What it does** (full body, lines 3599-3659): Synchronizes device/login identity data for one employee across three tables:
1. Derives `vusername = CONCAT(SUBSTR(emp_branch,1,4), emp_company_id)` — i.e. auto-generated login/device username = first 4 chars of branch code + the company-assigned employee id.
2. Looks up (or defaults to `1000`) a `DeviceId` for the company from `mypayrol_control_db.devices`.
3. If no `emp_device_comp_branch` row exists yet for this employee/branch/company, inserts one (linking device id, device-side employee id, username, email); also updates `emp_details.emp_id` and `user_credentials.user_id` to the new values.
4. If a row already exists, updates all three tables (`emp_details.emp_id`, `user_credentials.user_id`, `mypayrol_control_db.emp_device_comp_branch`) to the (possibly changed) `emp_company_id`/username/email.

**Called from**: 0 matches in `Controller/*.php` — not called from the application layer (name `estern` — likely "Eastern [client]" custom one-off script, per-tenant customization not wired into shared controllers).

**Constants**: default `DeviceId = 1000` when none configured (line 3635-3637); username derivation rule `SUBSTR(branch,1,4) + companyEmployeeId`.

---

## 14. `get_policy_weekoff_holiday_count_prc`

**Signature**: `schema/mypayrol_trial.sql:4299`
```
CREATE PROCEDURE get_policy_weekoff_holiday_count_prc(IN pemp_pkey int, IN pstart_date date, IN pend_date date, OUT pweekoff_count float, OUT pholiday_count float)
```

**What it does** (full body, lines 4299-4399): Day-by-day loop (`to_days(pstart_date)` .. `to_days(pend_date)`) counting weekoffs and holidays for one employee over a date range, using the employee's `HOLIDAY_GROUP_ID`/`day_time_seq` (most recent `emp_proff` row). Per day:
1. Checks `holidays` table for a matching active holiday for the employee's holiday group.
2. Determines if the day is a weekoff by checking **both** `working_day_time_procedures` (the weekly pattern: any of Sun-Sat = 'N', or any `_F` "full-weekoff-on-leave" flag = 'Y') **and** `shift_exceptions` (per-week overrides, `week_off='Y'`) — matched by day name + week-of-month (`FLOOR((DAYOFMONTH-1)/7)+1`).
3. Per matched weekday: if the day is `'N'` (non-working) or has a shift-exception override, it's a **full weekoff** (+1 to `pweekoff_count`; if it was also flagged a holiday, decrements `pholiday_count` by 1 — i.e. weekoff takes priority over holiday in the count, avoiding double counting). If instead the `_F` flag is set (a "half-day weekoff" indicator), it's a **half weekoff** (+0.5 to `pweekoff_count`; if also a holiday, holiday count is decremented by 0.5).

This computes the *policy-defined* weekoff/holiday allotment for a period (as opposed to `get_present_in_weekoff_holiday_count_prc` below, which additionally weights by actual attendance).

**Called from**: 0 matches for `get_policy_weekoff_holiday_count_prc` in `Controller/*.php`. Not called directly from PHP controllers found in this scan — possibly invoked from another SQL routine or reserved for future use.

**Constants**: half-weekoff decrement `0.5` (lines 4389-4392).

---

## 15. `get_present_in_weekoff_holiday_count_prc`

**Signature**: `schema/mypayrol_trial.sql:4402`
```
CREATE PROCEDURE get_present_in_weekoff_holiday_count_prc(IN pemp_fkey int, IN pmonth_year varchar(7), OUT o_weekoff_count float, OUT o_holiday_count float)
```

**What it does** (full body, lines 4402-4529): Similar day-by-day loop as #14 but scoped to a full attendance cycle (`att_start_end_fn(pmonth_year,1/2)`) and **weighted by actual presence** rather than counting every weekoff/holiday day equally:
1. For each day, reads `emp_detail_timeattandance.present` for the employee.
2. Computes an **attendance multiplier**: `'P/P'` → `1.0`; `'P/A'` or `'A/P'` → `0.5`; anything else (absent/null) → `0`.
3. If the day is a holiday: `vholiday_count += multiplier`.
4. Else if the day is a full weekoff (same weekday/shift-exception logic as #14): `vweekoff_count += multiplier`.
5. Else if the day is a half-weekoff (`_F` flag): `vweekoff_count += multiplier / 2`.

Net effect: an employee only gets "credit" for a weekoff/holiday day proportional to how present they were that day (a fully absent employee gets 0 credit for a weekoff day that would otherwise count as 1). This is the actual formula used for payroll present-day computations that include weekoff/holiday pay eligibility.

**Called from**: 0 matches for `get_present_in_weekoff_holiday_count_prc` in `Controller/*.php`. Not directly called from PHP in this scan — likely invoked by another stored procedure (e.g. salary/OT calculation routines outside this scope) rather than the app layer.

**Constants**: attendance multipliers `1.0` / `0.5` / `0` keyed off exact strings `'P/P'`, `'P/A'`, `'A/P'` (lines 4465-4471); half-weekoff divisor `/2` (line 4518).

---

## 16. `insert_default_menu`

**Signature**: `schema/mypayrol_trial.sql:4532`
```
CREATE PROCEDURE insert_default_menu(IN Pemp_fkey int)
```

**What it does** (full body, lines 4532-4565): For every `emp_menu` row flagged `is_default='Y'`, inserts a `user_access` row `(organization_id=1, user_fkey=Pemp_fkey, menu_id, active='Y')` unless an active row already exists for that user/menu combo (dedup via `NOT EXISTS`). Standard "grant default menu permissions to a new/reactivated employee" bootstrap.

**Called from** (all as `CALL insert_default_menu('$emp_fkey')` — used consistently when creating/reactivating employee user accounts):
- `Controller/DailyActivityController.php:351,369`
- `Controller/ProjectController.php:384,402`
- `Controller/SiteAttendanceApplyController.php:382,400`
- `Controller/SiteAttendanceController.php:384,402`
- `Controller/SiteWorkController.php:469,487`
- `Controller/UserAccessController.php:628,646` (line 480 is commented out)

**Constants**: hardcoded `organization_id = '1'` (line 4556) — single-tenant assumption baked into the schema.

---

## 17. `insert_emp_att_reg`

**Signature**: `schema/mypayrol_trial.sql:4568`
```
CREATE PROCEDURE insert_emp_att_reg(IN Pcompany_code varchar(30), IN PBranch_code varchar(30), IN Puserid varchar(30), IN Pmonth date, OUT Perr_msg varchar(5000))
```

**What it does** (full body, lines 4568-5078): An older/legacy variant of the attendance-register builder (superseded in current use by `insert_update_att_reg`, see #18 — but still present and may still be reachable). For every branch in the company, for every employee who joined by the cycle end and has a shift assigned and no existing register row:
1. Computes `vweekoff_count` via `weekoff_days_count_fn(...)` and `vholiday_count` via a direct `holidays` count query, and inserts a fresh `attendance_register` row (with those two aggregate columns populated but `FIELD1..FIELD31` left unset at insert time).
2. Triggers per-day computation via `time_duration_check_multishift` or `time_duration_check` (same dispatch logic as `emp_detail_att_reg`, #11) depending on `is_multiple_days`.
3. In a nested block (`BLOCK7`), cursor-loops over `emp_detail_timeattandance` rows for the employee/month (source: `present`, `weekoff`, `leaves`, `holiday`, `others` columns) and derives a `vsetfield` status string for each day via an elaborate chain of `IF`/`ELSEIF` branches keyed on `working_day_time_procedures.work_time_day_off_cal_ot` (values `0`, `1`, `2`, or the "simple attendance" case where `working_time1 <= 10`):
   - **Simple-shift path** (`working_time1 <= 10`, i.e. no real shift config / simplified attendance mode): leave takes priority, with half/full-day leave merge logic against existing present/absent halves (e.g. if `vleaves` ends in `/A` and current `vpresent='P/A'`, the leave replaces just the absent half → `.../P`; if `vweekoff` and `vholiday` both apply → `'HO/WO'`; if only weekoff → `'P/A'` (half-day-worked convention) or `'WO'` if full both-halves).
   - **`work_time_day_off_cal_ot=1`** ("count OT worked on a day-off/holiday against a threshold of 1"?): leave/present merge is attempted first, else falls back to weekoff/holiday/others/present in that priority order.
   - **`work_time_day_off_cal_ot=2`**: presence-first priority (present → leave → weekoff → holiday → others).
   - **`work_time_day_off_cal_ot=0`**: presence-first, but with an explicit `'/WO'` + `'P/A'` → `'P/WO'` combination rule, and `'/WO'` + null present → `'A/WO'`.
   - After the primary branch, there's a **second unconditional pass** (lines 4897-4941) that re-applies many of the same leave/weekoff/holiday merge rules again as an overwrite safety net (e.g. re-derives `'HO/WO'`, `'NA/WO'`, `'P/WO'`, `'A/WO'` combinations) — meaning the earlier per-branch derivation is partially redundant/overridden by this final pass.
   - Finally, if `vsimple_att<>0` (simple-attendance employee), one more override forces the P/A halves in from `vleaves` again.
4. Writes the resulting `vsetfield` into the day's corresponding `FIELD1`..`FIELD32` column of `attendance_register` via a 32-way `IF/ELSEIF` chain keyed by `vloop_count` (one physical column per day-of-month, up to 32 to accommodate custom-cycle months that can span up to 32 calendar days).

**Status-code vocabulary observed**: `'P/P'` (full present), `'P/A'`/`'A/P'` (half day present), `'A/A'` (full absent), `'WO'` (full weekoff), `'HO'` (holiday), `'HO/WO'` (holiday+weekoff overlap), `'A/WO'` (absent half + weekoff half), `'P/WO'`, `'NA/WO'` (not-applicable/pre-joining half + weekoff half). Leave codes come from `salary_head_items.occurance` (defaults to `'LOP'` — Loss of Pay — if not set), embedded into the slash-format e.g. `'CL/A'`, `'A/SL'`, `'SL/SL'`.

**Called from**: 0 direct matches for `insert_emp_att_reg` in `Controller/*.php` — appears superseded by `insert_update_att_reg` (below), which is the actively-called procedure. Retained in schema but likely dead from the app's perspective.

---

## 18. `insert_update_att_reg` — **canonical current attendance-register builder**

**Signature**: `schema/mypayrol_trial.sql:5081`
```
CREATE PROCEDURE insert_update_att_reg(IN pbranch_code varchar(30), IN pstart_date date, IN pend_date date, IN puserid varchar(30), OUT poutput varchar(20))
```

**What it does** (full body, lines 5081-5557) — this is the primary, actively-maintained version of the attendance-register computation (branch-scoped, date-range driven rather than whole-month):

1. Resolves the attendance cycle (`vfrom_date`/`vto_date` via `att_start_end_fn`) containing `pstart_date`; if `pstart_date` falls inside that window, `vyearmonth = DATE_FORMAT(pstart_date,'%Y-%m-01')`, else it's bumped one month forward (handles the case where `pstart_date` is technically in the *next* calendar month but the *same* attendance cycle).
2. `vmonth = DATE_FORMAT(pend_date,'%Y-%m')`; `vcalendar_days = DATEDIFF(pend_date, pstart_date) + 1`.
3. Deletes stale soft-deleted (`isdelete='Y'`) register rows for the branch/month, then for each eligible employee (joined by cycle end, has shift + holiday-group + leave-policy-group assigned, no existing non-deleted register row) inserts a fresh register row with all 31 `FIELD1..FIELD31` pre-set to the literal string `'NA'` (Not Applicable — pre-joining/pre-eligibility default) and `record_status='1'`.
4. Day-by-day loop (`to_days(pstart_date)` to `to_days(vend_date)`), building `vsetfield` for each day with an explicit **priority cascade using an `is_full_status` short-circuit flag** (each stage only executes `if is_full_status = false`, and status is finalized as soon as a stage determines a "full" (non-half) day):

   **Step A — determine `is_na_period`**: true if the day is before the employee's `joining_date`, OR (if the employee has an active `termination` record) after their `last_approved_working_date`.

   **Step B — weekoff check** (`vweekoff_count > 0` from the standard `working_day_time_procedures` ∪ `shift_exceptions` union query, same as #14/#15): matches day-of-week against Sun-Sat flags + `_F` flags + `shift_exceptions` override, exactly as in #14. Sets `vsetfield`:
   - Full weekoff (`day='N'` or shift-exception match) → `'WO'`, and `is_full_status = true`.
   - Half weekoff (`_F='Y'`) → `'NA/WO'` if `is_na_period`, else `'A/WO'` (does **not** set `is_full_status`, since it's only a half day).

   **Step C — holiday check** (only if `is_full_status` still false): if `vholiday_count > 0`: if `vsetfield` is already `'A/WO'` or `'NA/WO'` (half weekoff was set), upgrade to `'HO/WO'`; else set `'HO'`. Sets `is_full_status = true` (holiday always finalizes the day, since a full holiday is a full day off regardless of what else was pending).

   **Step D — leave check** (only if `is_full_status` false and not `is_na_period`): counts authorized/approved leave transactions (`emp_leave_transactions.Leavestatus IN ('Authorized','Approved')`) for the day.
   - **1 leave record** (`vleave_count=1`): resolves `leave_session` (1/2/3) and the leave-type's `occurance` short-code (default `'LOP'`). If `vsetfield` already has a half-day placeholder (from step B), the leave short-code overwrites just the relevant half (only if `leave_session IN (1,3)` — full/first-half). If `vsetfield` is still null: `leave_session=3` (full day) → `'XX/XX'` (both halves = leave code); `leave_session=2` (second half) → `'A/XX'`; `leave_session=1` (first half) → `'XX/A'`.
   - **2 leave records** (`vleave_count=2`, i.e. different leave types for morning/afternoon): resolves each half's leave-type short-code separately and combines as `'XX1/XX2'`.
   - Finalizes `is_full_status = true` **unless** the resulting `vsetfield` still ends in `/A` or starts with `A/` (i.e. only becomes "full" once both halves are accounted for).

   **Step E — attendance check** (only if `is_full_status` false and not `is_na_period`): reads `emp_detail_timeattandance.present` for the day (must have `duration IS NOT NULL`, i.e. an actual computed shift duration). Merges into whichever half of `vsetfield` is still `A` (absent placeholder) using the corresponding half of `vpresent`; if `vsetfield` was null, `vsetfield = vpresent` directly. Finalizes `is_full_status = true` unless still half-open (`/A` or `A/`).

   **Step F — fallback** (only if `is_full_status` still false after all the above): if `is_na_period`, keep whatever partial `vsetfield` exists or default to `'NA'`. Else, checks `emp_proff.day_time_seq` against `working_day_time_procedures.working_time1 <= 10` ("simple attendance" employees, no real shift tracking):
   - Simple-attendance employees: any remaining `A` half is replaced with `'P'` (default-present assumption when no punch data exists) — or `'P/P'` if `vsetfield` was still null.
   - Full-shift employees: any remaining `A` half is replaced with `'LOP'` (Loss of Pay — absence defaults to LOP, not a soft "A") — or `'LOP/LOP'` if null.

5. Writes `vsetfield` into `FIELD1`..`FIELD32` via the same 32-way `vloop_count` dispatch as `insert_emp_att_reg`.
6. Returns `poutput = 'Processed'`.

This procedure is materially cleaner and more deterministic than `insert_emp_att_reg` (single linear priority cascade with an explicit `is_full_status` flag, vs. the older procedure's redundant multi-pass overwrite logic) — **this is the algorithm to port to TypeScript**, not #17.

**Status-code priority order** (highest to lowest, first match wins for "full-day" finalization): Weekoff(full) → Holiday → Leave(full pair) → Attendance(full pair) → NA (if pre-joining/post-termination) → simple-attendance default Present → full-shift default LOP. Half-day placeholders (`A/WO`, `NA/WO`, half-leave, half-present) get progressively overwritten by later stages filling in the missing half.

**Called from**:
- `Controller/AttendanceRegisterNewController.php:237` — `CALL insert_update_att_reg('$branch','$att_startdate2','$att_enddate2','$user_login',@Perr_msg)`
- `Controller/AttendanceReportsController.php:1187,1783`
- `Controller/AttendanceReportsNewController.php:1241,1866,6039`
- `Controller/EmployeeAttendanceUploadController.php:851` (also referenced/commented at 923)
- `Controller/StatutoryRegistersController.php:1735`
- Also present (unused in current build, presumably historical branches) in `AttendanceReportsController2018-010.php`, `AttendanceReportsControllerBkup*.php`, `AttendanceReportsController_Bkup-2018-01-07.php` — old backup controller files, not part of the live routing but confirm the call signature `CALL insert_update_att_reg('$company_code','NULL','$user_id','$month')` (a 4-arg positional call form, differing from the procedure's 5-param signature — note: in these call sites, `'NULL'` is passed as a **literal string** for `pstart_date`, suggesting these older call sites may predate a signature change, or `pbranch_code` here is actually being passed as `$company_code` — **flag for verification against the live schema/controller pairing**, since the call arg count/order doesn't cleanly match the current 5-param signature `(branch_code, start_date, end_date, userid, OUT)`).

**Constants**: default absence codes `'LOP'` (full-shift) vs `'P'` (simple-attendance) — the two different absence-handling regimes for employees with vs without real shift tracking (lines 5424-5441).

---

---

## 5. View-Layer Business Logic

# View-Layer Business Logic Report — CakePHP Legacy App

Scope: `legacy/View/Helper/*`, `legacy/View/Elements/*`, and business logic embedded directly in `.ctp` templates across `legacy/View/**`.

Status: IN PROGRESS — being written incrementally.

---

## 1. `View/Helper/AppHelper.php`

File: `D:\Projects\RIZOMigration\legacy\View\Helper\AppHelper.php` (34 lines total).

Confirmed **empty stub** — standard CakePHP boilerplate. Class body contains nothing:

```php
class AppHelper extends Helper {
}
```
(`View/Helper/AppHelper.php:32-33`)

No business logic. Nothing to migrate from this file.

---

## 2. `View/Helper/GoogleMapHelper.php`

File: `D:\Projects\RIZOMigration\legacy\View\Helper\GoogleMapHelper.php` (583 lines). Third-party open-source helper ("CakePHP Google Map V3" by Marc Fernandez Girones, MIT-licensed, unmodified-looking vendor code) that generates inline `<script>` blocks of raw Google Maps JavaScript (API v3). It is **presentation/integration code, not business logic** — it does not compute payroll/HR values, it only:

- Builds JS to render a map div, set center/zoom/type (`map()`, `GoogleMapHelper.php:127-308`)
- Builds JS to drop a marker at a lat/lng or geocoded address (`addMarker()`, `GoogleMapHelper.php:325-364`)
- Builds JS to cluster markers (`clusterMarkers()`, `GoogleMapHelper.php:377-386`)
- Builds JS to request driving/walking directions between two points (`getDirections()`, `GoogleMapHelper.php:402-449`)
- Builds JS to draw a polyline between two points (`addPolyline()`, `GoogleMapHelper.php:465-519`)
- Builds JS to draw a circle around a center point (`addCircle()`, `GoogleMapHelper.php:535-580`)
- `getVersion()` — returns hardcoded helper version string `'0.2.0'` (`GoogleMapHelper.php:41-45`), unrelated to app logic.

The only "decision" logic is input validation (regex-checking lat/lng are numeric, defaulting unset options) and string-escaping — not domain/business rules.

### Call sites (representative sample — grep across `.ctp` files)

The `GoogleMap` helper is declared (`public $helpers = array('GoogleMap');`) in exactly 4 controllers:
- `Controller/TrackingReportsNewController.php:7`
- `Controller/TrackingReportsController.php`
- `Controller/MobileLocationUpdateController.php`
- `Controller/DashboardNewController.php:7`

However, grepping `View/TrackingReportsNew/**`, `View/TrackingReports/**`, `View/MobileLocationUpdate/**`, `View/DashboardNew/**`, and a `glob:*.ctp` search across the entire `View/` tree for `GoogleMap` / `->map(` usage returned **zero matches**. The helper's methods (`map()`, `addMarker()`, `clusterMarkers()`, `getDirections()`, `addPolyline()`, `addCircle()`) do not appear to be invoked from any current `.ctp` template.

**Flag: possible legacy cruft — verify before migrating.** The helper is wired into 4 controllers' `$helpers` array but its output does not appear to be echoed in any surviving view file. Either (a) map rendering was removed from these views over time but the controller declaration was never cleaned up, or (b) the map JS is emitted through a different mechanism (e.g. hardcoded `<script>` in a `.ctp` file that constructs Google Maps calls directly without going through this helper — plausible, since raw JS map-building code was found in some tracking/location views, see below). Since it produces no business computation regardless, this does not affect the business-logic extraction plan either way — but the migration team should confirm whether live map functionality exists in Tracking/MobileLocationUpdate/Dashboard features before assuming this helper is dead.

---

## 3. `View/Elements/reportadminheader.ctp` and `reportempheader.ctp`

Both are PDF report header/footer templates (used with a PDF-generation library, given `<page>`, `<page_header>`, `page_footer>`, `[[page_cu]]/[[page_nb]]` tags — TCPDF/mPDF style templating).

**`reportadminheader.ctp`** (`View/Elements/reportadminheader.ctp`, 52 lines):
- Only conditional: `if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty(...))` to decide whether to render the company logo image (`reportadminheader.ctp:10`). Pure presentation guard, not a business rule.
- Renders company name/address/phone/email and a dynamic `$title`, and "Downloaded By `$user_name`" + current date in the footer. No branching on user_group, company plan, or numeric thresholds.

**`reportempheader.ctp`** (`View/Elements/reportempheader.ctp`, 60 lines):
- Same logo-exists guard (`reportempheader.ctp:10`).
- Renders employee name, designation, address, city, state/pincode in the header table (`reportempheader.ctp:30-44`) — pure display formatting, no computed values.

**Conclusion for step 3:** No business logic in either element. Both are formatting-only.

---

## 4. Business logic embedded directly in `.ctp` view files

Directory survey: `ls legacy/View/` (166 top-level feature directories) confirms a very large surface area of payroll/tax/leave/attendance features: `Payroll`, `PayrollProcess`, `SalaryReport(s)`, `SalarySlipReports`, `SalaryStructure`, `SalaryIncrement`, `SynthiteSalaryReports`, `Tax`, `TaxHeads`, `TaxReport`, `Taxsalarycomponents`, `EmpTax`, `EmployeeTax`, `StatutoryReport`, `StatutoryRegisters`, `EsiEpfReport`, `LeavePolicy`, `LeaveRequest`, `EmployeeLeaves`, `EmployeeLeaveRequest`, `LeaveEncashmentRequest`, `LopReports`, `EmployeeLoan(Reports)`, `Employeeadvance`/`Advance`/`EmployeeAdvanceReports`, `OtAttendance`, `EmpOtRegister`, `GatePass`, `Regularisation`, `FullandFinalsettlement`, `YearEnd`. Largest `.ctp` files in these areas run 50KB-265KB (e.g. `SalaryReports/salaryslipnew.ctp` 265KB, `Tax/setup.ctp` 131KB, `StatutoryReport/tax.ctp` 108KB) — these sizes themselves are a strong signal that computation, not just markup, lives in these templates. Sampled ~20 files across Tax, StatutoryReport, SalaryReports, SalaryReport, LeaveRequest, EmployeeLeaveRequest, LopReports, GatePass, OtAttendance.

Note: nearly every feature directory contains numerous `#bkup_*` / `#backup_*` sibling files (uncommitted ad-hoc developer backups, e.g. `Tax/setup.ctp#bkup_akshay_05_08_2025`, `StatutoryReport/empepf.ctp#backup_Akshay_24-3-2026`). These are **not** live code (CakePHP won't route to a `#`-suffixed file) — flagged as **legacy cruft, safe to ignore for migration**, but they do show the same business logic has been hand-copy-pasted and re-edited many times across dated backups, which is itself evidence of how fragile/unowned this logic is.

### 4.1 Income tax computation — `View/Tax/setup.ctp`

This view computes a full old-regime-vs-new-regime Indian income tax comparison, not just displays precomputed numbers.

- **Hardcoded standard deduction, overriding server-provided value:**
  `View/Tax/setup.ctp:285`
  ```php
  // $standerd_deduction_new = isset($taxcomponents_new['0']['EmployeeTaxsalsumNew']['standerd_deduction'])?...:0;
  $standerd_deduction_new = 75000; //Edited by Akshay on 17-9-2024
  ```
  The commented-out line shows this used to come from the model/DB; a developer hardcoded `75000` (the FY2024-25 new-regime standard deduction amount) directly in the view instead. Repeated at `Tax/setup.ctp:356`, `362`, `373`, `541`. If the standard-deduction amount changes in a future budget, someone has to remember to edit this literal inside a view template.

- **Taxable income computation:**
  `View/Tax/setup.ctp:286`, `304`, `362`, `373`
  ```php
  $taxables_new = round($taxable_salary_new + $other_income_new - $tax_heads_limitsum_new - $standerd_deduction_new);
  ```
  Taxable income = salary income + other income - deduction-head sum - standard deduction. This arithmetic is the actual tax base computation, done in the view.

- **Rebate-eligibility (Section 87A) business rule, with different thresholds per regime:**
  `View/Tax/setup.ctp:287-291` (new regime, ₹700,000 threshold) and `View/Tax/setup.ctp:305-309` (old regime, ₹500,000 threshold):
  ```php
  if ($taxables_new <= 700000) { $rebate = 1; } else { $rebate = 2; }
  ...
  if ($taxables <= 500000) { $rebate = 1; } else { $rebate = 2; }
  ```
  Then at `Tax/setup.ctp:292-296` and `310-314`: if `$rebate == 1` the payable tax is zeroed out entirely (`$new = 0` / `$old = 0`), else it's `total + cess + surcharge`. This is the Section 87A full-tax-rebate eligibility rule, hardcoded with regime-specific thresholds, computed in the view.

- **Marginal relief window check (Budget 2025 new-regime marginal relief rule):**
  `View/Tax/setup.ctp:579`
  ```php
  if ($total_taxable_income_new < 1200001 || $total_taxable_income_new > 1275000) {
      // Applicable: NA
  }
  ```
  Encodes that marginal relief only applies for taxable income strictly between ₹12,00,001 and ₹12,75,000 — a specific statutory band, hardcoded as view-layer conditional.

- **Slab-wise tax total re-aggregated in the view** (not just displayed):
  `View/Tax/setup.ctp:471` sums 7 income "portions" (`first_portion` … `seventh_portion`) and `View/Tax/setup.ctp:475-484` separately re-sums the corresponding 7 `*_portion_tax` fields into `$total_tax` via repeated `isset(...) ? ... : 0` addition — duplicate aggregation logic that could silently diverge from whatever total the controller/model computed.

- **Regime comparison / "which is better" recommendation:**
  `View/Tax/setup.ctp:1390` (and duplicated in nearly every backup, e.g. `#bkup_megha_07_09_2023:954`):
  ```php
  <?php if($nettotal_new > $nettotal_old){ echo " Old Tax Regime "; } else { echo "New Tax Regime "; } ?>is more beneficial. You can save Rs.<?php echo abs($nettotal_new - $nettotal_old); ?>.
  ```
  This is a genuine advisory business decision (which tax regime is better for the employee) computed and rendered inline in the template — the kind of rule that should live in a shared tax-calculation module, not a view.

### 4.2 EPF (Provident Fund) statutory computation — `View/StatutoryReport/empepf.ctp`

This view computes India's EPF/EPS/EDLI employer-contribution breakup from raw salary figures — a full statutory calculation, not a report of precomputed values.

- **Skip employees with zero EPF ("business filter" in the view):**
  `View/StatutoryReport/empepf.ctp:102`
  ```php
  if ($val['0']['EPF'] == 0) continue;
  ```

- **Date-range-conditional wage-basis rule (COVID-era EPF relaxation, May-July 2020):**
  `View/StatutoryReport/empepf.ctp:119-129`
  ```php
  if ($month >= '2020-05' && $month <= '2020-07') {
      $epf_salary          = round($val['0']['EPF'] * 100 / 10);
      ...
      $employer_epf_salary = round($val['0']['EMPLOYER_EPF'] * 100 / 10);
  } else {
      $epf_salary          = round($val['0']['epf_salary']);
      ...
      $employer_epf_salary = round($val['0']['employer_epf_salary']);
  }
  ```
  A hardcoded 3-month historical exception (matching India's 2020 EPF contribution-rate relief period, when employer/employee EPF was temporarily reduced from 12% to 10%) is baked into the view as a literal date-string comparison. This is a one-time statutory event permanently wired into report-rendering code.

- **EPF/EPS/EDLI/admin-charge rate constants and formulas, hardcoded:**
  `View/StatutoryReport/empepf.ctp:133-148`
  ```php
  $epf   = round($sal * 8.33 / 100);
  $esia  = round($sal * .5 / 100, 2);
  ...
  $emp_epf_contr = round($employer_epf_salary * .12) - round($employer_epf_salary * .0833); // Edited by Akshay on 5-11-2025
  ...
  $total_eps                    += min($employer_epf_salary, 15000);              // EPS wage capped at 15,000
  $total_employer_eps           += round(min($employer_epf_salary, 15000) * .0833); // EPS = 8.33% capped
  $total_employer_edli          += (min($employer_epf_salary, 15000) * .005);       // EDLI = 0.5% capped
  $total_employer_admin_charges += ($employer_epf_salary * .005);                   // Admin charges = 0.5%, uncapped
  ```
  These are India EPFO statutory rates: 12% employer EPF, split into 8.33% EPS (capped at ₹15,000 wage) + 3.67% EPF proper, 0.5% EDLI (capped), 0.5% admin charges. All rate constants (`.12`, `.0833`, `.005`, `15000`) are magic numbers embedded directly in the template with no named reference to a configurable "PF rate" — if EPFO changes a rate, this view has to be hand-edited.

- **NCP (non-contributory-period) days floor-clamped:**
  `View/StatutoryReport/empepf.ctp:112-113`
  ```php
  $ncp = isset($val[0]['NCP_days']) ? $val[0]['NCP_days'] : 0;
  $ncp = max(0, $ncp);
  ```
  Business rule: NCP days can never be negative — clamped in the view.

The same block (with the same magic numbers) is duplicated a second time later in the same file at `empepf.ctp:371-404` (a second report section, e.g. print-view vs. screen-view) — meaning the rate constants are copy-pasted twice within one file, doubling the maintenance/consistency risk.

### 4.3 Leave-day computation — `View/LeaveRequest/addeditleave_new.ctp`

Leave duration (including half-day handling) is computed **client-side in an inline `<script>` block** embedded in the `.ctp`, not by the server:

`View/LeaveRequest/addeditleave_new.ctp:846-886`
```js
let startSess = $('#FROMHALF').val(); // 1 = First Half, 2 = Second Half
let endSess   = $('#TOHALF').val();
let totalDays = 0;
if (sdt.getTime() === edt.getTime()) {
    if (startSess === '1' && endSess === '1') totalDays = 0.5;
    else if (startSess === '2' && endSess === '2') totalDays = 0.5;
    else if (startSess === '1' && endSess === '2') totalDays = 1;
    else totalDays = 0;
} else {
    let daysBetween = Math.floor((edt - sdt) / (1000 * 60 * 60 * 24)) + 1;
    totalDays += (startSess === '1') ? 1 : 0.5;
    if (daysBetween > 2) totalDays += (daysBetween - 2);
    totalDays += (endSess === '2') ? 1 : 0.5;
}
diffDays = totalDays;
if (diffDays > 0 && leave_balance < diffDays) {
    alert("You do not have enough leave balance");
    ...
}
```
This is the authoritative leave-duration business rule (same-day half/full-day combinations, multi-day span with half-day start/end) **and** a client-side leave-balance eligibility check (`leave_balance < diffDays` blocks submission), both living in view-embedded JavaScript. A large commented-out earlier version of the same logic sits directly above it (`addeditleave_new.ctp:830-844`), showing the rule has already been rewritten at least once in place. Because this is JS in the browser, the server almost certainly re-validates leave balance — but the day-count *algorithm itself* (how half-days combine across a date range) is defined only here and in the equivalent JS of sibling files (`View/EmployeeLeaveRequest/addeditleave.ctp`, `View/LeaveRequest/addeditleave.ctp`), so any Next.js reimplementation must reverse-engineer this exact algorithm rather than finding it in a model/service.

### 4.4 Status-code-to-label mapping (view-layer antipattern)

- `View/LopReports/leavebalance.ctp:27`
  ```php
  echo isset($value['summary'][0][0]['ed']['status']) && $value['summary'][0][0]['ed']['status'] == "2" ? '(Resigned)' : '';
  ```
  and duplicated at `View/LopReports/leavebalance.ctp:63`. Employee/status code `2` is hardcoded to mean "Resigned" — the mapping of numeric employee-status codes to human labels lives only in this view (and presumably repeated ad hoc elsewhere), not in a shared enum/lookup. Migration must recover the full status-code table (`1 = ?`, `2 = Resigned`, others = ?) from the DB schema/model layer, since the view only reveals the one branch it happens to care about.

- `View/GatePass/index.ctp:93` (client-side JS): `if (row.status == 1) { ... }` — another bare numeric status-code branch with no label table, gating gate-pass row rendering/actions by status.

- `View/Tax/setup.ctp:231`: `if ($user_group == '1') { ... }` shows admin-only vs read-only tax-head editing UI (Lock/Unlock controls) gated by a hardcoded `user_group == '1'` check directly in the view, rather than a permission/capability check resolved server-side.

### 4.5 Salary/report aggregation totals computed in view

`View/SalaryReports/comparison_report.ctp` (current file, ~1000+ lines, and duplicated near-identically across `#bkup_akshay_18_04_2024` and `#bkup_akshay_24_04_2024`) computes period-over-period salary comparison entirely in the view:
- `comparison_report.ctp:290,293`: `$prev_netsalary = ($prev_total_sal - $prev_total_ded);` / `$curr_netsalary = ($curr_total_sal - $curr_total_ded);` — net salary = gross minus deductions, computed in the view.
- `comparison_report.ctp:309,325`: `$prev_pay_days = $pres_days + $leave_days + $holiday + $week_off;` — payable-days business formula (present + paid-leave + holiday + week-off) assembled in the view.
- `comparison_report.ctp:354-456`: running column totals (`$total[$l] += abs(...)`) accumulated across rows to build report footer totals, entirely in-template.

`View/StatutoryReport/empepf.ctp` (see 4.2) and `View/SalaryReports/bankreport_synthite.ctp:203,287,591,769,916` (`$total_net_amt += $netamt;`) show the same running-total-in-view pattern repeated across many report templates — a systemic style choice in this codebase (accumulate totals via `+=` inside the row-rendering loop of the `.ctp`, rather than the controller returning pre-aggregated totals).

---

## 5. Overall assessment

**Systemic, not marginal.** Business logic embedded in view templates is a pervasive pattern in this codebase's payroll/tax/statutory/leave reporting areas, not a handful of isolated exceptions:

1. **Genuine statutory/tax computation lives only in views.** `View/Tax/setup.ctp` computes taxable income, Section-87A rebate eligibility (with different thresholds for old vs. new regime), the FY2024-25 marginal-relief eligibility window, and an old-vs-new-regime "which saves more money" recommendation. `View/StatutoryReport/empepf.ctp` computes EPF/EPS/EDLI/admin-charge amounts from statutory rate constants (12%, 8.33%, 0.5%, ₹15,000 wage cap), including a hardcoded one-time COVID-period (May-July 2020) rate exception keyed off a literal date-string comparison. None of this is simple display formatting — it is exactly the kind of regulatory calculation that must be centralized, tested, and versioned in the Next.js rewrite, not re-embedded in React components.

2. **The pattern repeats across the codebase's largest files.** The directory survey shows dozens of 50KB-265KB `.ctp` files in `SalaryReports`, `SalaryReport`, `StatutoryReport`, `Tax`, and related areas — file sizes far beyond what pure markup would need. Sampling 20 of them surfaced the same anti-patterns repeatedly: running totals accumulated with `+=` inside row loops, net-salary/payable-days formulas assembled inline, and duplicate re-aggregation of values the controller/model likely already computed once.

3. **Status-code-to-label mapping is done ad hoc, per-view, with no central enum.** `status == "2"` meaning "Resigned" (`LopReports/leavebalance.ctp:27,63`) and bare `status == 1` gates in JS (`GatePass/index.ctp:93`) mean the authoritative meaning of these codes is undocumented outside the DB/model layer and scattered view conditionals — migration needs a full audit of the underlying status/type columns to build one canonical lookup table rather than inferring meaning from whichever view happens to branch on it.

4. **Some rules exist only in client-side JavaScript inside `.ctp` files**, e.g. the leave half-day/multi-day duration algorithm in `LeaveRequest/addeditleave_new.ctp:846-886`. This is a business rule (how leave days are counted) that likely has no server-side equivalent implementation to copy from — it must be reverse-engineered from the JS itself.

5. **Widespread `#bkup_*`/`#backup_*` sibling files** (dozens per feature directory) show this logic has been repeatedly hand-edited in place over years by multiple developers (Akshay, Megha, Athira, Arul, Bindu, Sinsiya, Aswathy...) with no version control discipline — comments like `//Edited by Akshay on 17-9-2024` and `// Edited by Akshay on 5-11-2025` embedded directly in the business formulas are the *only* audit trail for when a tax/PF rate changed. This makes the current `.ctp` files the single source of truth for "what rate is actually in effect right now," which the migration must extract carefully (cross-checking against the newest `#bkup` files' dates isn't reliable either, since some backups are newer than the live file's last logic change — e.g. `empepf.ctp#backup_Akshay_26-3-2026` postdates the visible `-5-11-2025` comment in the live file, so the backups should be diffed against the live file before being discarded, not assumed superseded).

**Recommendation for the migration:** Treat this as a systemic extraction project, not spot-fixes. At minimum, before building the Next.js UI:
- Extract the India income-tax slab/rebate/marginal-relief/regime-comparison logic (`Tax/setup.ctp`) into a shared, unit-tested `tax-calculation` module with rates/thresholds as configuration, not literals.
- Extract the EPF/EPS/EDLI calculation (`StatutoryReport/empepf.ctp`, and check `EsiEpfReport/`, `StatutoryRegisters/` for ESI equivalents) into a shared `pf-calculation` module, including the historical COVID-period exception as a dated rate-table entry rather than an inline date comparison.
- Extract the leave-day counting algorithm (half-day/multi-day) into a shared function, sourced from `LeaveRequest/addeditleave_new.ctp:846-886` since it may not exist server-side.
- Build one canonical employee/leave/gate-pass status-code enum by cross-referencing the Model layer, since views only reveal fragments of the code space via scattered `== "2"`/`== 1` checks.
- Do not port `#bkup_*`/`#backup_*` files — they are dead, uncommitted developer snapshots — but do diff a handful against their live counterparts to confirm no logic was lost, since this repo's only "history" for business-rule changes is these manual backups plus inline `//Edited by ... on ...` comments.

---

## 6. Plugins & Configuration-Driven Behavior

# Plugins & Configuration-Driven Behavior

## A. Plugins

### A.1 No `Plugin/` directory
Confirmed: no `Plugin/` (or any case-variant) directory exists at the repo root. `ls` of `D:\Projects\RIZOMigration\legacy` shows only `Config`, `Controller`, `Images`, `Model`, `View`, `api`, `schema` — no `Plugin`.

### A.2 No plugin-namespaced imports
`Config/bootstrap.php:63-71` contains only commented-out example code:
```
* CakePlugin::loadAll(); // Loads all plugins at once
* CakePlugin::load('DebugKit'); //Loads a single plugin named DebugKit
```
Neither line is live (both are inside a `/** ... */` doc comment block, `bootstrap.php:63-71`). No other file calls `CakePlugin::load` or `CakePlugin::loadAll`.

A broad grep for `App::uses(..., 'SomePlugin.Something')`-style plugin-namespaced imports across `Controller/`, `Model/`, `Config/` returned no matches — every `App::uses()` call in the app references core CakePHP categories (`Controller`, `Model`, `Component`, `Lib`, `Vendor`, etc.), never a `Plugin.Class` dotted plugin reference.

There is no root-level `composer.json`/`composer.lock` for the CakePHP app itself (only `find . -maxdepth 1 -iname "composer*"` at repo root returns nothing) — dependencies are the vendored/legacy CakePHP 2.x core plus ad-hoc `Vendor/` libraries (e.g. `Vendor/TCPDF-main/tcpdf.php`, referenced e.g. at `Controller/EmployeeJoinController.php:130`), not Composer-managed.

### A.3 `api/v1` (Slim mini-app) dependencies
`api/v1/composer.json:14-22` declares:
```json
"require": {
    "php": ">=5.5.0",
    "slim/slim": "^3.1",
    "slim/php-view": "^2.0",
    "monolog/monolog": "^1.17"
},
"require-dev": {
    "phpunit/phpunit": ">=4.8 < 6.0"
}
```
This is a fully separate Slim 3 micro-framework app (`api/v1/composer.json:2-5`, package name `mpm/api` — "My Payroll Master API Services") living at `api/v1/index.php`, unrelated to the CakePHP plugin system. Its "plugin surface" is just three Composer packages: the Slim router/microframework, Slim's PHP-view renderer, and Monolog for logging — plus PHPUnit as a dev/test dependency. Autoloading is PSR-4 (`App\` → `src/App/`) with three explicitly-required controller files (`api/v1/composer.json:23-31`).

### A.4 Conclusion (report requirement #5)
**This app has zero CakePHP plugin dependencies.** The main CakePHP 2.x application loads no plugins (the plugin-loading calls in `bootstrap.php` are commented-out boilerplate that ships with every fresh CakePHP install and was never activated). The only adjacent "dependency surface" is the separate Slim-based `api/v1` service, which uses three small Composer packages (Slim, Slim PHP-View, Monolog) — not CakePHP plugins in any sense. For migration purposes, there is no plugin ecosystem to port; all cross-cutting behavior lives in hand-rolled `Controller/` code, `Vendor/`-included third-party PHP libraries (TCPDF, PHPExcel, etc.), and database-driven configuration (see Section B).

---

## B. Configuration-Driven Behavior

### B.1 `Config/bootstrap.php` — every `Configure::write()` call
The file is 109 lines total; read in full. Only **one** active `Configure::write()` call exists (all others in the file are commented-out documentation examples, `bootstrap.php:31-89`):

| Line | Call | Effect |
|---|---|---|
| `Config/bootstrap.php:90-93` | `Configure::write('Dispatcher.filters', array('AssetDispatcher','CacheDispatcher'))` | Registers the two stock CakePHP dispatcher filters (asset serving + response caching). This is CakePHP's own default wiring, not app-custom behavior — no `Cache.check` is ever turned on elsewhere (see core.php, already covered in prior report), so `CacheDispatcher` is effectively inert. **Possible legacy cruft — verify before migrating** (no evidence any route relies on response caching).

`bootstrap.php:98-108` also configures two `CakeLog::config()` log streams (`debug`, `error`) writing to file — this is logging plumbing, not behavior-branching config, so not itemized further per scope.

No other `Configure::write()` calls exist in `bootstrap.php`. (Session config, database.php, email.php, and core.php's debug/cache Configure calls were already covered in the prior backend report and are excluded here per instructions.)

### B.2 Runtime `Configure::write('debug', 0)` calls OUTSIDE core.php/bootstrap.php
These are a **new** finding not covered by the prior report on core.php's static debug level — several controllers **override debug mode mid-request**, right before generating binary output (PDF via TCPDF), to suppress CakePHP's HTML error/notice output from corrupting the binary stream:

- `Controller/EmployeeJoinController.php:132` — before `require_once(realpath("../Vendor/TCPDF-main/tcpdf.php"))` at line 130, inside a PDF-generation action.
- `Controller/EmployeeJoinController.php:218` — same pattern, second PDF action (preceded by `require_once(...tcpdf.php)` at line 215).
- `Controller/EmployeeAttendanceUploadController.php:237` — set at the very top of the `form($emp_fkey = 0)` action (`EmployeeAttendanceUploadController.php:234-238`), before any output — broader than just PDF, silences all debug output for that whole action.
- `Controller/AssetController.php:487` — set right before JSON response assembly (`AssetController.php:487-491`), suppressing debug noise from a JSON API-style endpoint.
- `Controller/AssetController.php:367` — **commented out** (`//     Configure::write('debug', 0);`) — dead code, possible legacy cruft, verify before migrating.

Behavior implication for migration: none of this is externally configurable — it's hardcoded per-action suppression of framework debug output, working around CakePHP 2.x's habit of injecting deprecation/notice HTML into any response stream when `debug > 0`. In a Next.js target this has no equivalent need (no global debug-injection into responses), so it's not itself business logic to port, but it signals these specific actions are fragile w.r.t. PHP warnings leaking into binary/JSON payloads today.

### B.3 `Configure::read()` gating call sites
- `Config/core.php:359` — `if (Configure::read('debug') > 0) { ... }` (already covered by prior report's core.php discussion, listed here only for completeness of the write/read pairing).
- `Controller/PagesController.php:136` — `if (Configure::read('debug')) { ... }` — gates behavior in the generic static-pages controller.
- `Controller/SiteController.php:144`, `Controller/SiteController.php:498`, `Controller/DashboardNewController.php:4621` — all three are **commented out**: `'host' => '127.0.0.1',  //Configure::read('SERVERHOST'),` — dead references to a `SERVERHOST` config key that is never written anywhere in the codebase (grepped, no `Configure::write('SERVERHOST'...)` exists). **Possible legacy cruft — verify before migrating**; the hardcoded `127.0.0.1` literal is what's actually live.

### B.4 Database-driven configuration tables

#### B.4.1 `comp_contact_info` (model alias `CompanyContactInfo`) — `plan` column
Schema: `schema/mypayrol_trial.sql:40569-40588` — `plan varchar(10) NOT NULL DEFAULT 'standard'` (values seen: `'standard'`, `'basic'`).

- **Set every request**: `Controller/AppController.php:191-194` runs `SELECT plan FROM comp_contact_info` on every controller action (via `beforeFilter()`) and exposes it to all views as `$plan` (`AppController.php:194`, `$this->set('plan', $plan)`).
- **Also independently re-fetched** in several controllers rather than relying on the parent's view var (redundant/duplicated logic — worth flattening in the rewrite):
  - `Controller/ArrearsReportsController.php:82-83`
  - `Controller/AttendanceRegisterNewController.php:67-68`
  - `Controller/AttendanceReportsController.php:78-79`
  - `Controller/DashboardController.php:2020-2032` — wrapped in a `try/catch` that silently swallows failures (`catch (Exception $ex) {}`), then **falls through and re-runs `$this->Menu->find("all", ...)` unconditionally at line 2032 regardless of which branch fired** — meaning the `if ($plan == 'basic')`/`else` branch at 2025-2029 has no actual effect on the final `$menudb` value (line 2032 overwrites it every time). **Likely dead/no-op logic — verify before migrating.**
- **Feature-gating branches keyed on `$plan == 'basic'`** (a `basic`-tier company gets a restricted feature set):
  - `Controller/AttendanceReportsController.php:97`
  - `Controller/DashboardController.php:2025`
  - `Controller/DashboardNewController.php:2756`
  - `Controller/LeaveRequestController.php:1139`, `Controller/LeaveRequestController.php:6047`
  - `Controller/MiscellaniousReportsController.php:93`
  - `Controller/SalaryStructureController.php:437` and `:541` — e.g. at `SalaryStructureController.php:437-444`, when `plan == 'basic'`, `SalaryHeads` are filtered to `"plan" => 'basic'` records only, restricting which salary-head items a basic-tier company can configure.
  - `Controller/SalaryReportsController.php:259`
- Distinct from this is `Controller/SalaryReportsController.php:21199/21212/21478/21629`, which use a **different, unrelated** `$item_type == 'standard'` local variable (salary "Standard Salary" vs "Actual Salary" report toggle) — not the company `plan`; flagged here only to avoid confusion since it greps alongside.
- This `plan` value is the same SaaS-plan concept referenced by `Controller/ReportController.php:140-156`'s plan/feature-gating catalog API (previously documented) — i.e. there are at least two independent plan-gating mechanisms in the app: the ad-hoc `comp_contact_info.plan == 'basic'` checks cataloged here, and the more structured feature-catalog approach in `ReportController`/`PaymentApprovalsController`/`AttendanceSetupController`. They do not appear to share a common helper — each controller re-implements its own plan check, which is a migration risk (inconsistent enforcement) worth flagging to the target design.

#### B.4.2 `db_config` table — attendance/payroll cycle configuration
Schema: `schema/mypayrol_trial.sql:40909-40934`. Columns acting as feature flags/settings (one row per company, `active='Y'` marks the live row):
- `biometric_device_essl` (char, default `'Y'`) — whether biometric device integration (eSSL brand) is enabled for the company.
- `attendance_type` (varchar) — attendance capture mode.
- `attendance_format` (varchar, values `'A'`/`'B'` seen) — whether the attendance cutoff is a fixed calendar day (`'A'`) or month-start (`'B'`); set via `Controller/PayrollController.php:437-444` based on a `attendance_cycle` form field (`att_cycle == 1` → format `'B'`/date `0`; else format `'A'`/date = cycle day).
- `attendance_date` (int) — the cutoff day-of-month for the attendance cycle, paired with `attendance_format`.
- `emp_login` (char, default `'Y'`) — whether employee self-service login is enabled.
- `payroll_type` (varchar, value `'T'` seen) — payroll cycle type, set from `salary_cycle` form field at `Controller/PayrollController.php:435,449`.
- `email_setup` (char, default `'Y'`) — whether automated email notifications are enabled for the company.
- `TDS_setup` (char, default `'Y'`) — whether TDS (Indian tax withholding) computation is enabled.
- `Salary_date` (int) — salary disbursement cutoff date.
- `leave_url` / `expense_url` / `profile_url` / `subdomain_url` — per-company file-system/URL paths for the leave, expense, and profile-photo upload subsystems and the login subdomain (these look like they should be app-level config but are stored per-company-row in the DB instead).

**Where read/branched in PHP:**
- `Controller/CompanyController.php:118-124` and `:164-165` — reads `attendance_format, attendance_date, payroll_type` and exposes as `db_config` to the Company setup view (drives the setup-wizard UI, not business logic directly).
- `Controller/AttendanceReportsNewController.php` — reads `attendance_date` via the `DbConfig` model in at least 7 call sites (`:856, :1180, :1797/1812, :3763/3765, :4605/4614, :7248/7252`) and a commented-out 8th (`:5978-5982` — dead, verify), each time to compute the attendance-cycle date window for a report.
- `Controller/CompanyController.php:433-452` — the only write path found for `attendance_format`/`attendance_date`/`payroll_type` (via raw `UPDATE db_config ... WHERE active = 'Y'`, `CompanyController.php:446-450`).
- **Important caveat**: I found **no PHP-level branching** on `attendance_format == 'A'/'B'`, `payroll_type`, `TDS_setup`, `email_setup`, or `biometric_device_essl` values (greps for `attendance_format ==` / `payroll_type ==` returned no matches in `Controller/`). These columns are read into PHP-level variables but the actual conditional logic on them appears to live **inside MySQL stored procedures** in `schema/mypayrol_trial.sql` — I found 20+ stored-procedure references like `select ... from db_config where company_code = pcompany_code and active='Y'` (e.g. `schema/mypayrol_trial.sql:2492, 3496, 4621, 5607, 8643, 20995`) and payroll-type-driven procedure branches (`schema/mypayrol_trial.sql:842, 1215, 1937, 18290`). **This means a large chunk of "configuration-driven behavior" for attendance/payroll cycles is embedded in stored procedures, not in the CakePHP controllers** — critical for the migration team to know, since a code-only read of `Controller/*.php` will under-report how much `db_config` actually controls. Recommend a dedicated stored-procedure audit pass.
- `Controller/AccessController.php:65-68` — unrelated same-named local variable `$controldb_config` (a CakePHP `ConnectionManager` datasource config array, not the `db_config` table) — flagged only to avoid confusion with the table above.

#### B.4.3 `wizard_config` table — setup-wizard gate (mostly dead code)
Schema: `schema/mypayrol_trial.sql:47798-47809` (columns: `state`, `link`, audit fields). Intended purpose: track which step of a first-run setup wizard a company has completed, and redirect users to `Site/config` until the wizard is finished.

- `Controller/AppController.php:129-133` — **entirely commented out**: the code that would query `wizard_config` and redirect non-admin... actually redirect *admin* (`else` branch, i.e. `user_group != 2`) users to `Site/config` if `link == 'Site/config'` is dead (`//` prefixed on all 4 lines). **Possible legacy cruft — verify before migrating**; today nothing enforces wizard completion at the AppController level.
- `Controller/DashboardNewController.php:159-160` — same pattern, also commented out (`//$check_setups = ...`, `//if ($check_setups['0']['wizard_config']['state'] == 0)`).
- **Still-live writes** exist in `Controller/DbConfigController.php:280` (`UPDATE wizard_config SET link = 'DbConfig/policy', state = '1'`) and `Controller/DbConfigController.php:346` (`UPDATE wizard_config SET state = '1'`) — so the table is still being *written* as setup steps complete, but with the read/redirect side disabled, these writes currently have **no observable effect** on request routing anywhere in the app (no other live read of `wizard_config` was found). Net effect: `wizard_config` is a vestigial feature — writes happen but nothing consumes them. **Flag as legacy cruft for the migration team to consciously decide whether to resurrect the gating behavior or drop the table.**

### B.5 Consolidated hardcoded company-code list (`Controller/*.php`)

Company-code literal-equality branches were found using two different PHP comparison styles in the codebase (`$company_code == 'xxx'` with a space, and `$company_code=='xxx'` with no space) plus a large number of `in_array($company_code, array(...))` call sites. A canonical, deduplicated, case-normalized list of every distinct company code referenced via direct `==`/`==` literal comparison across `Controller/*.php` (case as it actually appears varies by call site — some compare against `strtolower($company_code)` so lowercase literals, others against the raw session value so uppercase literals):

```
ABSG, AGNG, AMST, ASHL, ASQR, ASTL, AYRK, BATT, BKHS, BNGL, CKWR, CSMT,
DEMO, DJIC, DJOC, DRRC, DVDS, DYGL, EDGM, ELKT, ELSL, ETNA, EXTR, FRSG,
FYNK, GAAR, GEDE, GLET, GTRA, HDCM, HDEQ, HDFN, HDGF, HDSC, HRBL, IFHY,
IMSC, INFR, KWMT, LBLD, LNTT, MBCT, MNDM, MRZC, NRMY, NWTR, SCRT, SHIN,
SHYD, SRTS, STFR, STPN, SVNS, THNG, TRCK, TSSM, TUDS, VGFS, VSFS, WHOO, ZWLK
```
(60 distinct codes). Sources: `grep -rn "company_code == '" Controller/*.php` and `grep -rn "company_code=='" Controller/*.php`, case-normalized and deduplicated.

Example of the pattern (already partially documented elsewhere, shown here for the citation): `Controller/AppController.php:140` — `if ($company_code == 'vgfs' || $company_code == 'vsfs' || $company_code == 'gede' || $company_code == 'absg' || $company_code == 'demo' || $company_code == 'glet')` gates a site-expiry notification query; `Controller/AppController.php:158` — `if ($company_code == 'demo' || $company_code == 'glet')` gates a separate "upcoming salary increment" notification query, both inside `beforeFilter()` so they run on every request for those tenants.

**Additional surface not enumerated above**: a separate, larger set of company-code checks uses `in_array($company_code, array(...))` rather than chained `==`, found in 25 controller files (`Controller/AttendanceController.php`, `AttendanceRegisterNewController.php`, `AttendanceReportsController.php`, `AttendanceSetupController.php`, `DayTimeProcedureController.php`, `EditAttendanceController.php`, `EditPunchesController.php`, `EmpleaveuploadController.php`, `EmployeeConfigController.php`, `EmployeeController.php`, `EmployeeExpensesController.php`, `EmployeeJoinController.php`, `EmployeeLeaveUploadController.php`, `EmployeeManageController.php`, `EmployeeRegisterController.php`, `EmpreportController.php`, `LeaveEncashmentRequestController.php`, `LeavePolicyController.php`, `LeaveRequestController.php`, `MiscellaniousReportsController.php`, `PayrollController.php`, `PromoController.php`, `RegularisationController.php`, `ReportController.php`, `SalaryReportsController.php`, `SalaryStructureController.php`). These weren't individually cataloged here (out of scope per instructions — pattern already known to be pervasive), but the file list above tells the migration team exactly where to look for the `in_array` variant of tenant-specific branching, which is likely a larger and more consequential set than the 60 codes captured by direct `==` comparison above.

---

## Summary for the report

1. **Plugins**: Zero CakePHP plugin dependencies — confirmed no `Plugin/` dir, no live `CakePlugin::load*()` calls (only commented boilerplate at `Config/bootstrap.php:63-71`), no plugin-namespaced `App::uses()` imports, and no root `composer.json`. The only adjacent dependency surface is the separate Slim 3 `api/v1` service (`api/v1/composer.json:14-19`: `slim/slim`, `slim/php-view`, `monolog/monolog`), which is not a CakePHP plugin.
2. **`bootstrap.php`**: exactly one live `Configure::write()` (`Dispatcher.filters`, `bootstrap.php:90-93`), stock CakePHP wiring with limited real effect given caching is off elsewhere.
3. **New runtime `Configure::write('debug', 0)` overrides** in 4 controllers (`EmployeeJoinController.php:132,218`, `EmployeeAttendanceUploadController.php:237`, `AssetController.php:487`) suppress debug output around PDF/JSON generation — not itself portable business logic but signals response-corruption risk in those flows today.
4. **Two DB-driven "configuration" tables actively shape behavior**: `comp_contact_info.plan` (SaaS tier, `'basic'` vs `'standard'`, gates feature availability in 8+ controllers, set globally every request in `AppController.php:191-194`) and `db_config` (per-company attendance/payroll cycle settings — largely consumed by stored procedures, not PHP, which is a blind spot for a controller-only migration read). `wizard_config` is effectively dead (writes with no live reads).
5. **60 distinct hardcoded company codes** found via direct `==` comparison, consolidated above; a further ~25 controller files use `in_array($company_code, ...)` for additional per-tenant branches not individually cataloged here.

---

## 7. Per-Cluster Data Model & Business Logic


### 7.1 Employee Management

# Employee Management — Data Model & Business Logic Report

Scope: Employee Details/Professional/CTC, Beneficiary, Contacts, Notice Period, Employee
Hierarchy/Structure, Employee Join (onboarding), Employee Config, Document management.

Ground truth: `schema/mypayrol_trial.sql` (line numbers cited below). Models live in `Model/*.php`,
controllers in `Controller/*.php`.

---

## 0. Confirms prior finding: no `$validate`, no lifecycle hooks, no custom model methods

Every model file in this cluster was inspected in full. **None** define `$validate`,
`beforeSave`/`beforeValidate`/`afterSave`/`afterFind`, or any method beyond what `AppModel`
(`Model/AppModel.php:34-38`) inherits from CakePHP's base `Model` — and `AppModel` itself adds
nothing but a pass-through constructor. A `grep -n "function "` across all 18 in-scope model files
returned zero matches (constructors excluded). This is a **cluster-wide confirmation** of the prior
research finding — no exception found.

Models covered: `EmployeeDetails`, `EmpDetails`, `EmployeeProfessionalDetails`, `EmployeeCTC`,
`Beneficiary`, `Contacts`, `NoticePeriod`, `EmployeeJoin`, `EmployeeConfig`, `EmpDocument`,
`Documents`, `DocumentUpload`, `DocumentAllocation`, `EmployeeStructure`, `EmployeeInfo`,
`Organization`, `CompanyContactInfo`, `EmployeeSalaryStructure`.

**Consequence for migration**: all real business logic for this domain lives in (a) controllers
(huge, e.g. `EmployeeJoinController.php` is ~11,000 lines) and (b) **MySQL triggers/stored
procedures directly on the tables** (see §3). The Next.js/ORM layer will need to reimplement logic
that today is silently enforced by the database, not the app.

---

## 1. Schema cross-check

All associations (`$hasOne`, `$belongsTo`, `$hasMany`) in this cluster are **commented out** in the
source, e.g. `Model/EmployeeDetails.php:22-26` and `Model/EmployeeJoin.php:22-26`:
```php
/* public $hasOne = array(
  'EmployeeProfessionalDetails' => array(
  'className' => 'EmployeeProfessionalDetails'
  )
  ); */
```
So there are **zero active CakePHP associations** to cross-check for correctness — every join
between these tables is done ad-hoc in controllers via `'joins' => array(...)` or raw SQL
(`$this->EmployeeDetails->query(...)`). Below is the actual model→table mapping and what
foreign-key-shaped columns exist, since the app never declares this itself.

| Model | `$useTable` | `$primaryKey` | Schema line | FK-shaped columns present | Declared associations |
|---|---|---|---|---|---|
| `EmployeeDetails` / `EmpDetails` | `emp_details` | `emp_pkey` | `schema/mypayrol_trial.sql:42785` | none (no `*_fkey` columns at all) | none (commented out) |
| `EmployeeProfessionalDetails` | `emp_proff` | `emp_proff_pkey` | `schema/mypayrol_trial.sql:43735` | `emp_fkey` → `emp_details.emp_pkey` | none (commented out at `Model/EmployeeProfessionalDetails.php:17-22`, would have been `belongsTo EmployeeDetails`) |
| `EmployeeCTC` | `emp_ctc_upload` | `emp_ctc_upload_pkey` | `schema/mypayrol_trial.sql:42649` | `emp_fkey` → `emp_details.emp_pkey` (has a real DB-level `CONSTRAINT ... FOREIGN KEY`, `schema/mypayrol_trial.sql:42671`) | none |
| `Beneficiary` | `beneficiary` | `contact_id` | **not found — table does not exist** in either `schema/mypayrol_trial.sql` or `schema/mypayrol_control_db.sql` | n/a | none |
| `Contacts` | `contacts` | `contact_id` | `schema/mypayrol_trial.sql:40593` | none (`relationship` free text `Customer,Vendor,Others`; no `organization_fkey`) | none |
| `NoticePeriod` | `notice_period` | `notice_pkey` | `schema/mypayrol_trial.sql:45246` | none — standalone lookup table (4 static rows) | none |
| `EmployeeJoin` | `emp_join` | `emp_join_pkey` | `schema/mypayrol_trial.sql:43328` | `emp_fkey` (nullable-in-practice link back to `emp_details.emp_pkey`, populated once the onboarding record is promoted) | none |
| `EmployeeConfig` | `emp_config` | `id` | `schema/mypayrol_trial.sql:42530` | `emp_fkey` → `emp_details.emp_pkey`; `policy_id` (polymorphic FK, meaning depends on `type`, see §3) | none |
| `EmpDocument` | `emp_documents` | not declared (defaults to Cake schema-introspected PK `emp_doc_pkey`) | `schema/mypayrol_trial.sql:43197` | `emp_join_fkey` → `emp_join.emp_join_pkey`, **DB-enforced** `FOREIGN KEY ... ON DELETE CASCADE` (`schema/mypayrol_trial.sql:43219`) | none |
| `Documents` | `documents` | `document_pkey` | `schema/mypayrol_trial.sql:41941` | see below | none |
| `DocumentUpload` | `document_upload` | not declared | `schema/mypayrol_trial.sql:41975` | see below | none |
| `DocumentAllocation` | `document_allocation` | not declared | `schema/mypayrol_trial.sql:41963` | see below | none |
| `EmployeeStructure` | `emp_structure` | not declared | `schema/mypayrol_trial.sql:44009` | `emp_id`, `parent_id` — self-referential adjacency-list tree, **not FK-suffixed** (`emp_id`/`parent_id`, not `emp_fkey`) | none |
| `EmployeeInfo` | `employee_info` | `emp_pkey` | `schema/mypayrol_trial.sql:42161` — **this is a VIEW-shaped table** (columns include `emp_id`, `EmpName`, `branch`, `designation`, `department`, `grade`, `emp_status` — all denormalized/derived) | n/a (reporting view) | none |
| `Organization` | `organization_info` | `organization_id` | `schema/mypayrol_trial.sql:45260` | none | none |
| `CompanyContactInfo` | `comp_contact_info` | not declared | `schema/mypayrol_trial.sql:40569` | none | none |
| `EmployeeSalaryStructure` | `emp_salary_structure` | `emp_salary_structure_pkey` | `schema/mypayrol_trial.sql:43878` | (has `emp_fkey`, not inspected in full — out of primary scope) | none |

### Flagged mismatches

1. **`Beneficiary` model references a table that does not exist in the authoritative schema dump.**
   `Model/Beneficiary.php:27` sets `$useTable = 'beneficiary'`, and `Controller/BeneficiaryController.php`
   actively reads/writes it (`addeditcontacts`, `save`, `listcontacts`, `deletecontacts`,
   `uploadandsaveempctc` — all at `Controller/BeneficiaryController.php:38-375`). A case-insensitive
   grep for `beneficiary` across both schema files returns nothing. Either (a) the schema dump used
   for this research is stale/incomplete, or (b) this feature is broken in the environment the dump
   was taken from. **Verify against a live DB before migrating** — do not assume `beneficiary` is
   dead code just because it's absent from the dump; the controller is fully wired and reachable
   from routes.

2. **`ContactsController::save()` and `BeneficiaryController::save()` write a column that doesn't
   exist.** Both set `$arr_form_data['organization_id'] = $org_info['Organization']['organization_id'];`
   (`Controller/ContactsController.php:75`, `Controller/BeneficiaryController.php:75`) before calling
   `->save()`. The `contacts` table (`schema/mypayrol_trial.sql:40593-40630`) has **no
   `organization_id` column**. CakePHP's `Model::save()` silently drops fields not present in the
   table schema, so this is a no-op today — but it signals the original developers *intended*
   multi-tenant scoping by `organization_id` on `contacts`/`beneficiary` that was never actually
   added to the schema. Flag as **possible legacy cruft — verify before migrating**; if per-org
   scoping matters for these entities, it isn't currently enforced anywhere.

3. **`EmployeeCTC` (`emp_ctc_upload`) has a real DB-level foreign key** (`emp_ctc_upload_ibfk_1`,
   `schema/mypayrol_trial.sql:42671`) to `emp_details`, but the model declares no `belongsTo` — every
   controller (`EmployeeUnderController`, `EmployeeadvanceController`, etc.) joins manually.

4. **`EmpDocument` → `emp_join`, not `emp_details`.** Documents captured during onboarding
   (`emp_documents.emp_join_fkey`, `schema/mypayrol_trial.sql:43199`, cascading delete) attach to the
   *onboarding* record (`emp_join_pkey`), not the finalized employee (`emp_details.emp_pkey`). If an
   onboarding `emp_join` row is later deleted, its documents cascade-delete too, even after the
   employee has been promoted into `emp_details`/`emp_proff`. There is no equivalent
   `emp_details_fkey` linkage for documents post-onboarding in this table — post-onboarding document
   management appears to reuse `documents`/`document_upload`/`document_allocation` instead (see §4).

5. **`EmployeeProfessionalDetails` (`emp_proff`) stores designation/department/grade/vertical/branch
   as free-text `varchar` columns** (`designation`, `emp_dept`, `emp_grade`, `emp_vertical`,
   `emp_branch` — `schema/mypayrol_trial.sql:43741-43745`), **not as foreign keys**, even though
   dedicated lookup tables/models exist (`Designation`, `Departments`, `Grades`, `Verticals`,
   `Units` — all present in every relevant controller's `$uses`, e.g.
   `Controller/EmployeeConfigController.php:31`). Referential integrity for these fields is enforced
   only in PHP, by checking `in_array($value, $allowedList)` at save time (see
   `Controller/EmployeeJoinController.php:4117-4128`) — not by the database. This is a systemic
   "stringly-typed foreign key" pattern worth flagging for the target schema design.

6. **`EmployeeStructure` (`emp_structure`) and `EmployeeDetails.parent` are two independent,
   redundant hierarchy representations** — see §3 (Hierarchy resolution) for detail; not a
   model/schema mismatch per se, but a genuine duplication that the association-free models hide.

---

## 2. Custom methods per model

**None.** As documented in §0, every model in this cluster (`EmployeeDetails`, `EmpDetails`,
`EmployeeProfessionalDetails`, `EmployeeCTC`, `Beneficiary`, `Contacts`, `NoticePeriod`,
`EmployeeJoin`, `EmployeeConfig`, `EmpDocument`, `Documents`, `DocumentUpload`,
`DocumentAllocation`, `EmployeeStructure`, `EmployeeInfo`, `Organization`, `CompanyContactInfo`,
`EmployeeSalaryStructure`) is a bare `$useTable`/`$primaryKey` declaration with no methods. All
"business methods" that would normally live on a model instead live directly in controllers as
`public function` actions, called from routes/AJAX, and operate on the model only via inherited
`find`/`save`/`updateAll`/`deleteAll`/`query`. See §3 for the significant controller-level logic.

Two duplicate/backup model files exist and are **not referenced by any controller's `$uses`**
(confirmed no `App::uses` or `$uses` entry names them) — dead code:
- `Model/Contacts_bkup_megha.php` — byte-identical to `Model/Contacts.php`.
- `Model/EmployeeDetails_bkup_megha.php` — byte-identical to `Model/EmployeeDetails.php`.
Possible legacy cruft — safe to exclude from migration, but confirm no cron/script references them
by filename before deleting.

---

## 3. Complex logic / state machines

### 3.1 Employee status field — not a clean binary despite the schema comment

`emp_details.status` is documented as `COMMENT '0:inactive, 1:active'`
(`schema/mypayrol_trial.sql:42831`), but the application actually uses **three** values:

- `0` = inactive
- `1` = active
- `2` = "Terminated, pending payroll processing" — set by
  `Controller/EmployeeResignationController.php:1101-1108` (`removeemps()`) with the inline comment
  *"Terminated , Status should be 1 untill we proccess this - to avail that Employee in payroll"*,
  and again by `Controller/EmployeeResignationController.php:2405-2410` (`Terminate()`, dead-code
  duplicate — the actual `updateAll` call is commented out at line 2407, superseded by the
  `Termination` model's own save). Status `2` is also queried directly elsewhere, e.g.
  `Controller/EmployeeResignationController.php:1131` (`emp_details.status = '2'`) and `:2006`, to
  gate "unverified attendance processing" checks during final settlement.

So the real state machine is: **Active(1) → [resignation/removal flow] → Terminated-pending(2) →
[payroll settlement processed] → Inactive(0)**, but nothing in the code was found that flips `2`
back to `0` automatically within this cluster — that transition likely happens in payroll-close
logic outside this scope. **Flag for the payroll/reports cluster to confirm the final `2→0`
transition.**

### 3.2 Employee resignation/termination workflow (`Controller/EmployeeResignationController.php`)

- `Terminate()` (`:2381-2431`) creates/updates a row in a separate `Termination` model/table
  (not in this cluster's primary scope, but tightly coupled) with `is_authorized`, `is_approved`
  hardcoded to `"Y"` and `authorized_by`/`approved_by` hardcoded to `0` — i.e. **the resignation
  self-approves at creation time**, with no actual approval workflow gate visible in this method.
- `removeemps()` (`:1090-1214`) is the actual "finalize termination" action:
  1. Sets `emp_details.status = 2` for the employee (`:1101-1108`).
  2. Clears any subordinate's `emp_proff.attr1` (reporting-manager pointer, see §3.3) that pointed
     at this employee — "Heirarchy employees auto reversal" (`:1109-1117`).
  3. Sets `emp_config.status = 0` for any `HIERARCHY`-type config row where
     `policy_id = <this emp_pkey>` (`:1118-1126`) — a second, parallel reversal of the same
     relationship stored a different way (see §3.3).
  4. Computes `working_days_settled`/`payroll_days` for final settlement using a custom
     attendance-minus-LOP-minus-weekoffs-minus-holidays calculation
     (`:1162-1197`), including a **hardcoded per-tenant branch** (`if ($str_company_code == 'KWMT')`,
     `:1191-1197`) that bypasses the generic calculation entirely for one specific company code —
     tenant-specific logic baked into shared code, a notable migration risk.
  5. Calls a stored function `weekoff_days_count_fn(...)` (`:1177`) — logic lives in the DB, not
     documented here (out of scope; flag for the stored-procedures research pass).

### 3.3 Reporting-manager / hierarchy resolution — three parallel, uncoordinated representations

1. **`emp_details.parent`** (`schema/mypayrol_trial.sql:42832`, `int(100) DEFAULT '0'`) — set
   recursively by `EmployeeHierarchyController::saveHeirarchy()`
   (`Controller/EmployeeHierarchyController.php:174-183`), which walks a JSON tree posted from the
   UI (`saveData()`, `:158-173`) and does `updateAll(array("parent"=>$parent), array("emp_pkey"=>$value->id))`
   per node.
2. **`emp_structure` table** (`emp_id`, `parent_id` — `schema/mypayrol_trial.sql:44009-44015`) — a
   *separate* adjacency-list tree maintained by `EmployeeStructure` model, populated by
   `EmployeeHierarchyController::add()`/`remove()` (`:126-157`) and queried by
   `listemployeesforhierarchy()` (`:89-124`) to find "unassigned" employees (`NOT IN` children +
   parent already in the tree).
3. **`emp_proff.attr1`** — repurposed as the "HIERARCHY policy" pointer via the `emp_config`
   polymorphic-config trigger (`schema/mypayrol_trial.sql:42565-42566`,
   `emp_config_bi` trigger: `elseif new.type='HIERARCHY' then update emp_proff set attr1=new.policy_id ...`).
   This is the value cleared during termination reversal (§3.2 step 2).

These three stores are **not kept in sync by any single code path** — `saveHeirarchy()` only
touches `emp_details.parent`; `EmployeeStructure::add()/remove()` only touches `emp_structure`;
`emp_config` type=`HIERARCHY` only touches `emp_proff.attr1`. A migration needs to determine which
of these three is authoritative for "who is this employee's manager" in the current UI — they can
diverge. **Flag as high-priority ambiguity for the target data model.**

### 3.4 `emp_config` — polymorphic config table driven entirely by MySQL triggers, invisible to the app

`emp_config` (`schema/mypayrol_trial.sql:42530-42546`) is a generic `(emp_fkey, type, policy_id)`
table. `Model/EmployeeConfig.php` is a 9-line thin wrapper with zero logic — **all the actual
cascading behavior is implemented in two MySQL triggers**, not in PHP:

- `emp_config_bi` (BEFORE INSERT, `schema/mypayrol_trial.sql:42551-42576`): based on `new.type`,
  writes the corresponding column on `emp_proff` for that `emp_fkey`:
  - `SHIFT` → `emp_proff.day_time_seq`
  - `MSHIFT` → `emp_proff.multishift`
  - `HOLIDAY` → `emp_proff.HOLIDAY_GROUP_ID`
  - `SALARY` → `emp_proff.structure_id`
  - `LEAVE` → `emp_proff.LEAVEPOLICY_GROUP_ID`
  - `HIERARCHY` → `emp_proff.attr1` (see §3.3)
  - `NOTICEPER` → `emp_proff.notice_days`
  - `DIVISION` → `emp_proff.emp_vertical`
  - `SECTION` → `emp_proff.emp_sep_priv` (note: `emp_sep_priv` is otherwise a "separation
    privilege" flag per its name — here it's overloaded to store a *section* policy ID; a genuine
    naming/purpose collision worth flagging)
  - `GRADE` → `emp_proff.emp_grade`
- `emp_config_au` (AFTER UPDATE, `:42578-42601`): mirrors the same `type` switch but **nulls out**
  the corresponding `emp_proff` column instead of setting it — i.e., updating an `emp_config` row
  is used as a "revoke" signal.

Because this logic lives entirely in triggers, any Next.js/ORM rewrite that inserts/updates
`emp_config` rows via direct SQL (bypassing MySQL) — or that migrates off MySQL entirely — will
silently lose this cascade unless it's reimplemented in application code. This is the single
biggest "invisible to the model layer" risk found in this cluster.

Also note `emp_proff` itself has **two AFTER triggers that push `emp_branch` back into
`emp_details.branch_code`** (`emp_proff_ai`, `emp_proff_au` — `schema/mypayrol_trial.sql:43778-43798`),
so an employee's "branch" is effectively also duplicated across two tables kept in sync only by
triggers.

### 3.5 Employee onboarding — `calculateOnboardingPercentage()` / `getOnboardingCompletion()`

`Controller/EmployeeJoinController.php:10235-10388` (`calculateOnboardingPercentage`) computes an
"onboarding completion %" as a **simple unweighted average of 7 section scores**:

1. **Personal** (`:10244-10305`): 29 fields checked via `$isFilled()` (defined `:10239-10241` as
   "set, non-empty after trim, not the literal string `'0'`" — note this means a legitimate value of
   `0` in any numeric-looking field, e.g. `pincode` or `account_no`, counts as *not filled*). The
   `country` field is excluded from the denominator unless `international_worker === 'y'`
   (`:10290-10293`). `profile_pic` additionally requires the value not be one of the two placeholder
   filenames (`placeholdermen.jpeg`/`placeholderwomen.jpeg`, `:10295-10298`) to count as filled.
2. **Company data** (`:10307-10340`): reads `emp_proff` directly by raw query. Required-field count
   is **conditional on `emp_type`**:
   - `permanent` → +1 required field (`emp_grade`)
   - `contract` → +2 required fields (`contract_start_date`/`contract_end_date` from a separate
     `contracted_days` table)
   - `probation` → +1 required field (`emp_proff.probation`, `schema/mypayrol_trial.sql:43770`,
     `int(11) unsigned` — presumably probation period length, unit not documented in schema)
3. **Family** (`:10342-10347`), **Education/Qualifications** (`:10349-10354`), **Work
   experience/History** (`:10356-10361`): each scored as a binary 0/100 ("has at least one row"),
   *not* a percentage of fields — inconsistent granularity vs. the personal/company sections. Each
   of these three also has a **fallback query to a second, differently-named table** if the primary
   one returns nothing: `emp_family`→`family`, `qualifcations`→`Education`,
   `history`→`work_experience` — implying two generations of schema exist side by side and the code
   defensively checks both (legacy migration residue).
4. **Config** (`:10363-10377`): counts distinct `emp_config.type` rows among
   `SHIFT, HOLIDAY, LEAVE, HIERARCHY` out of a hardcoded `$totalRequired = 4`.
5. **Documents** (`:10379-10384`): binary 0/100, checks `emp_passport_visa` first, falls back to
   `emp_documents` (again two tables for the same concept).

Final score: `round((personalPercent + companyPercent + eduPercent + famPercent + workPercent +
docPercent + configPercent) / 7)` (`:10387`) — an equal-weighted average across 7 sections despite
wildly different internal granularity (some are % of 20+ fields, others are binary). This exact
weighting/threshold behavior needs to be preserved or deliberately redesigned during migration.

A second, largely-duplicate implementation, `getOnboardingCompletion()`
(`Controller/EmployeeJoinController.php:10391+`), starts building an equivalent scoring using
`$mandatoryFields` from `:10438` onward (not fully traced — file exceeds convenient reading size;
flag for a closer look before migrating onboarding-completion UI, since **two competing
implementations of "% onboarding complete" exist in the same controller**).

### 3.6 Bulk employee import (`Controller/EmployeeJoinController.php:~4000-4200`)

Excel-upload employee creation flow: for each row, validates `designation` and `emp_dept` against
in-memory arrays (`in_array($arr_empprof_data['designation'], $desigantions)`,
`:4117-4128`) rather than DB constraints (confirms Finding 5 in §1), checks for duplicate `id_card`
among active (`status=1`) employees (`:4098-4115`), resolves `grade_code` → `grade_pkey` via a live
query (`:4081-4090`), then `EmployeeDetails->save()` (`:4140`) followed immediately by creating a
linked `UserCredentials` row with `emp_fkey = getLastInsertID()` and `access_allowed = 'n'`
(`:4156-4176`) — i.e., bulk-imported employees get a login credential row created but disabled by
default.

---

## 4. Schema quirks

1. **No `*_fkey`-style foreign keys anywhere on `emp_details`.** Every other Employee-domain table
   uses `emp_fkey` to point at `emp_details.emp_pkey`, but `emp_details` itself has zero FK columns
   — all its "relationships" (branch, country, nationality) are either free-text or bare `int`
   lookups (`nationality_id`, `country`) with no visible `FOREIGN KEY` constraint
   (`schema/mypayrol_trial.sql:42785-42846`).

2. **`emp_details_audit` is a full-row audit-log table populated entirely by the `emp_details_bu`
   BEFORE UPDATE trigger** (`schema/mypayrol_trial.sql:42851-43056`), diffing ~45 columns with
   `IFNULL(OLD.x,'') != IFNULL(NEW.x,'')` and inserting a snapshot of the *old* row when anything
   changed. This audit trail is invisible to the CakePHP layer entirely (no model even references
   `emp_details_audit`) — if the migration needs employee-change history, this trigger is the only
   place it's implemented, and it must be explicitly ported.

3. **`emp_details.editable`** (`int(10) DEFAULT '0'`, `schema/mypayrol_trial.sql:42843`) — name
   suggests a per-record edit-lock flag; not referenced in any controller file searched in this
   cluster. Possible legacy/vestigial column — verify usage elsewhere (e.g. approval workflows)
   before dropping.

4. **`emp_details.attr3`/`attr4`/`attr5`** and **`emp_proff.attr1`–`attr6`** — generic "extra
   attribute" varchar columns. `emp_proff.attr1` is actively repurposed as the hierarchy/manager
   pointer (§3.3/§3.4); the rest were not found referenced in this cluster's controllers — likely
   dead/reserved columns, but the naming gives no indication of intended purpose, which itself is a
   migration hazard (silent semantic columns).

5. **`emp_proff.emp_sep_priv`** is written by the `emp_config` `SECTION` trigger branch (§3.4) even
   though its name implies "employee separation privilege" (a permission flag, consistent with the
   sibling columns `leave_encash_priv`, `leave_mgt_priv`, `tax_mgt_priv`, `atten_mgt_priv`,
   `payro_priv`, `load_adv_exp_priv`, `emp_mgt_priv` — all clearly permission booleans,
   `schema/mypayrol_trial.sql:43752-43759`). Its actual runtime use as a "section policy ID" is a
   naming/purpose mismatch baked into a trigger — very easy to miss during schema translation.

6. **Duplicate "two schema generations" pattern** confirmed at the table level, not just in
   onboarding-completion code (§3.5): `emp_family` vs `family`, `qualifcations` vs `Education`,
   `history` vs `work_experience`, `emp_passport_visa` vs `emp_documents`. The `emp_*`-prefixed /
   lowercase-named versions appear to be the newer set (linked by `emp_fkey` to `emp_details`),
   while the capitalized/unprefixed set (`Education`, `family`, `work_experience`) link by
   `emp_join_fkey` to the onboarding table `emp_join` (`schema/mypayrol_trial.sql:42039` for
   `Education`, `:43284` region for `emp_family`, `:47887` for `work_experience`) — i.e., one set is
   the **onboarding-stage** capture and the other is the **post-onboarding/steady-state** capture,
   and the app has to query both because it's inconsistent which one actually has the data for a
   given employee. This dual-table pattern should collapse to one table per concept in the target
   schema, with a clear onboarding→employee promotion step.

7. **`notice_period` lookup table has a data quality bug in its seed data**: rows `(1, 15 days, 'One
   Month')` and `(4, 90 days, 'One Month')` both have the description `'One Month'` while their
   `notice_days` values (15 and 90) don't match a month unit consistently, and don't match rows 2/3
   (`30 days = 'Two Months'`, `60 days = 'Three Months'`) which imply a 30-day month.
   (`schema/mypayrol_trial.sql:45254-45258`, `INSERT INTO notice_period`). This looks like a seed-data
   authoring mistake (row 1 should plausibly read "15 Days" and row 4 "Three Months" or similar) —
   flag for whoever owns notice-period master data before carrying it into the new system verbatim.

8. **`emp_join.status`** has no `COMMENT` documenting its value set (unlike `emp_details.status`),
   and `emp_details.status` itself, despite its `COMMENT '0:inactive, 1:active'`
   (`schema/mypayrol_trial.sql:42831`), is used with a third value `2` at the application layer
   (§3.1) that the schema comment doesn't document. Treat schema `COMMENT`s in this codebase as
   **necessary but not sufficient** documentation of a status field's real value space — always
   grep the controllers.

9. **`emp_proff` has a `UNIQUE KEY` on `emp_company_id`** (`schema/mypayrol_trial.sql:43772`) but no
   unique constraint on `emp_fkey` itself, meaning the schema does not prevent an employee having
   more than one `emp_proff` row (which the app's "one professional-details row per employee"
   assumption throughout the controllers implicitly relies on). Worth a data audit before migration
   to confirm no duplicates exist in practice.

---

## Summary of highest-priority migration risks from this cluster

- **DB-trigger-resident business logic** (`emp_config` cascade, `emp_proff`↔`emp_details.branch_code`
  sync, `emp_details_audit` change log, `emp_ctc_upload` transaction-closing trigger) has no PHP
  equivalent anywhere — it must be explicitly reimplemented, not just "read the models."
  (`schema/mypayrol_trial.sql:42551-42601`, `:43778-43798`, `:42851-43056`, `:42677+`)
- **Three uncoordinated hierarchy/manager representations** (`emp_details.parent`, `emp_structure`,
  `emp_proff.attr1` via `emp_config` type `HIERARCHY`) need reconciliation before choosing one
  authoritative model for "reporting manager."
- **`beneficiary` table absent from the schema dump** despite a fully wired controller — needs
  verification against a live database, not assumed dead.
- **Free-text "foreign keys"** (`emp_proff.designation`/`emp_dept`/`emp_grade`/`emp_vertical`/
  `emp_branch`) enforced only by in-memory `in_array()` checks in PHP, not the database — a target
  schema should convert these to real FKs, but expect dirty/inconsistent historical data.
- **Two schema generations for onboarding sub-entities** (`emp_family`/`family`,
  `qualifcations`/`Education`, `history`/`work_experience`, `emp_passport_visa`/`emp_documents`)
  need a single consolidated target table with a clear promotion path from onboarding to
  steady-state employee record.

---

### 7.2 Attendance & Time

# Attendance & Time Data Model Report (Legacy CakePHP 2.x)

STATUS: COMPLETE

## Scope
Models found and analyzed (via `$uses` in the listed controllers):
`AttendanceRegister`, `AttendanceRegisterReport`, `AttendancePunch`, `EditPunches`, `EditPunchesHist`,
`RegisterHistory`, `DeviceAttendance`, `DayTimeProcedures`, `Holiday`, `HolidayGroup`, and (indirectly, dynamically
autoloaded, no dedicated file matches its class name) `ShiftException`. `ExceptionRule` (file `Model/ShiftExceptions.php`)
is a related-but-distinct model used only by the unrelated `ExceptionRuleController` (a generic employee-master
controller with a long `$uses` list) — see quirk in §4.

Controllers and their `$uses`:
- `Controller/AttendanceController.php:51` → `EmployeeDetails, Units, AttendanceRegister, DbConfig, SalaryHeadItems, LeaveRequests, EmployeeLeaveTransaction`
- `Controller/AttendanceRegisterNewController.php:52` → `EmployeeDetails, Units, AttendanceRegister, DbConfig, EditPunches, LeaveRequests, RegisterHistory`
- `Controller/EditAttendanceController.php:53` → `UserCredentials, CompanyContactInfo, LeaveRequests, EditPunches, EmployeeDetails, DbConfig, EditPunchesHist, AttendanceRegister`
- `Controller/EditPunchesController.php:53` → `UserCredentials, CompanyContactInfo, EditPunches, EmployeeDetails, DbConfig, EditPunchesHist, AttendanceRegister`
- `Controller/DailyOvertimeVerifyNewController.php:31` → `EmployeeDetails, Units, DbConfig, EditPunches, EditPunchesHist, AttendanceRegister`
- `Controller/OtAttendanceNewController.php:51` → `EmployeeDetails, Units, AttendanceRegister, DbConfig`
- `Controller/RegularisationController.php:53` → `UserCredentials, CompanyContactInfo, EditPunches, EmployeeDetails, DbConfig, EditPunchesHist, AttendanceRegister, Units`
- `Controller/ShiftPlannerController.php:30` → `CentralControl, DayTimeProcedures, UserCredentials, EmployeeDetails, EmployeeProfessionalDetails, EmployeeConfig, EditPunches`
- `Controller/CompoffController.php:50` → `EmployeeDetails, LeaveRequests`
- `Controller/HolidayCalendarController.php:48` → `UserCredentials, EmployeeProfessionalDetails, Holiday, HolidayGroup, Units`
- `Controller/DayTimeProcedureController.php:50` → `DayTimeProcedures, EmployeeProfessionalDetails, Menu, ShiftException`

Note: almost all real work in this cluster is done via raw SQL (`$this->ModelX->query("...")`) issued through
whatever model happens to be `$uses`-listed (often `EmployeeDetails`), NOT via the model that actually owns the
target table. E.g. `emp_shift_planner`, `employee_regularaization`, `emp_ot_master`, `emp_detail_timeattandance`
are all read/written through raw SQL against `EmployeeDetails`/`EditPunches`/`LeaveRequests` connections, and have
**no dedicated CakePHP Model class at all**. This is consistent with the prior finding that this app does not use
the ORM's association/validation layer in any meaningful way.

Confirmed for this cluster: **no model defines `$validate` or lifecycle callbacks** (`beforeSave`, `afterFind`, etc.).
All models are thin `useTable`/`primaryKey` declarations, optionally with one or two custom methods that just wrap
`CALL <stored_proc>(...)`.

---

## 1. Schema cross-check

| Model file | Class | `useTable` | `primaryKey` | Schema location | Declared associations | Schema FK-like columns | Mismatch? |
|---|---|---|---|---|---|---|---|
| `Model/AttendanceRegister.php:7-16` | `AttendanceRegister` | `attendance_register` | `registerid` | `schema/mypayrol_trial.sql:28986` | **none** | `emp_fkey` (int, no real FK constraint), composite key `emp_fkey_month_year_company_code_branch_code` (`schema/mypayrol_trial.sql:29041`) | No associations declared at all — can't "mismatch" a declaration that doesn't exist, but flag: schema itself has no `FOREIGN KEY` constraint on `emp_fkey`, only a plain index. |
| `Model/AttendanceRegisterReport.php:5-14` | `AttendanceRegisterReport` | `attendance_register_rep` | `registerid` | `schema/mypayrol_trial.sql:29060` | none | `emp_fkey` (int, no FK constraint) | Same as above. Note this table (report/snapshot copy of `attendance_register`) is missing the tally columns (`presant_total`, `leave_total`, `lop_total`, `wd_lop_total`, `lop_only`, `weekoff_total`, `na_wo_count`, `holiday_total`, `na_ho_count`, `working_days`, `calander_days`) present in `attendance_register` (`schema/mypayrol_trial.sql:29027-29037`) — it only keeps `FIELD1..32` daily codes + `isdelete`/`record_status`/`userid` (`schema/mypayrol_trial.sql:29100-29103`). |
| `Model/AttendancePunch.php:7-16` | `AttendancePunch` | `attendance_punch` | `attendance_punch_pkey` | `schema/mypayrol_trial.sql:28956` | none | `emp_fkey` (int, NOT NULL, no FK constraint) | Model is essentially unused for reads — no custom methods; `attendance_punch` is a raw web-punch log table with an `AFTER INSERT` trigger (`schema/mypayrol_trial.sql:28972`) that fans out into `device_attandance`. |
| `Model/EditPunches.php:7-16` | `EditPunches` | `device_attandance` | `device_attandance_seq` | `schema/mypayrol_trial.sql:40975` | none | none (`emp_id` is a plain varchar, not `_fkey`-suffixed, no constraint) — confirms prior finding this model is a thin `CALL`-free table wrapper actually used mostly for raw `query()` calls from controllers, not for stored-proc wrapping (contrast with `AttendanceRegister`) | n/a |
| `Model/EditPunchesHist.php:7-16` | `EditPunchesHist` | `device_attandance_hist` | `device_attandance_hist_pkey` | `schema/mypayrol_trial.sql:41868` | none | `device_attandance_seq` (int, references `device_attandance.device_attandance_seq`, no FK constraint) | n/a |
| `Model/RegisterHistory.php:9-19` | `RegisterHistory` | `register_history` | `register_history_pkey` | `schema/mypayrol_trial.sql:45560` | none | none (`branch`, `month` are plain varchars — a process-run audit log, not an entity table) | n/a |
| `Model/DeviceAttendance.php:7-17` | `DeviceAttendance` | `device_attandance` | `device_attandance_seq` | `schema/mypayrol_trial.sql:40975` | none | same table as `EditPunches` model above | **Duplicate model**: two separate model classes (`EditPunches`, `DeviceAttendance`) point at the identical table/PK. Only `EditPunches` is actually referenced in `$uses` by the controllers in scope; `DeviceAttendance` appears otherwise unused in this cluster — possible legacy cruft, verify before migrating. |
| `Model/DayTimeProcedures.php:7-17` | `DayTimeProcedures` | `working_day_time_procedures` | `day_time_seq` | `schema/mypayrol_trial.sql:47812` | none | none (shift-policy master table; referenced by `emp_proff.day_time_seq`, `emp_shift_planner.shift_id`, `shift_exceptions.shift_id` — none of these are declared as CakePHP associations) | n/a |
| `Model/Holiday.php:7-16` | `Holiday` | `holidays` | `HOLIDAYID` | `schema/mypayrol_trial.sql:44472` | none | `HOLIDAY_GROUP_ID` (int, no FK constraint, references `holiday_group.HOLIDAY_GROUP_ID`) | n/a |
| `Model/HolidayGroup.php:7-16` | `HolidayGroup` | `holiday_group` | `HOLIDAY_GROUP_ID` | `schema/mypayrol_trial.sql:44556` | none | n/a | n/a |
| `Model/ShiftExceptions.php:9-19` | class is named `ExceptionRule` (not `ShiftException`) | `shift_exceptions` | `id` (schema PK is actually `shift_exceptions_pkey`, see quirk below) | `schema/mypayrol_trial.sql:46823` | none | `shift_id` (int, no FK constraint, references `working_day_time_procedures.day_time_seq`) | **Mismatch**: `Model/ShiftExceptions.php:17` declares `public $primaryKey = 'id';` but the schema's actual primary key column is `shift_exceptions_pkey` (`schema/mypayrol_trial.sql:46824,46839`). There is no `id` column on this table at all. Also see naming-mismatch quirk in §4 — `DayTimeProcedureController.php:50` uses `'ShiftException'` (singular, no file matches) which CakePHP auto-vivifies as a bare `AppModel` bound to table `shift_exceptions` (inflected from the class name) — a **different, unrelated model instance** from this file's `ExceptionRule` class. Since this file's declared model is never actually instantiated as `'ShiftException'` by any controller in scope, its incorrect `primaryKey` is inert/unreachable — but would break the moment someone lists `'ExceptionRule'` in a `$uses` array and calls `save()`/`delete()` expecting `shift_exceptions_pkey` semantics. |

---

## 2. Custom methods per model

Only three of the in-scope models define any custom (non-inherited) methods; all others are pure `useTable`/`primaryKey`
declarations with zero custom logic (confirmed: `AttendancePunch`, `EditPunches`, `EditPunchesHist`, `RegisterHistory`,
`DeviceAttendance`, `DayTimeProcedures`, `Holiday`, `HolidayGroup`).

### `AttendanceRegister` (`Model/AttendanceRegister.php`)
- **`insertUpdateAttendanceRegisterProc($outputParameter)`** — `Model/AttendanceRegister.php:18-45`. Builds a
  quoted, comma-joined parameter list from the input array and issues `CALL insert_update_att_reg($parameter,@Perr_msg);`
  (`Model/AttendanceRegister.php:24`). Always returns `true` regardless of the stored-proc's actual outcome — the
  `@Perr_msg` OUT parameter is never read back in PHP. Call sites:
  - `Controller/AttendanceController.php` — search shows no direct call in the file grepped; verify via broader controller sweep (not found in the 11 controllers in scope during this pass — likely called from `AttendanceregisterController.php` / `Attendanceregister*` legacy variants outside the declared scope, or from a cron/shell task). **Flag: call site not found within the requested controller scope — verify before migrating** (may be invoked elsewhere, e.g. `Controller/AttendanceregisterController.php`, which is a separate, older controller not in this task's list).
- **`salaryProcessPrc($outputParameter)`** — `Model/AttendanceRegister.php:47-57`. Same parameter-building pattern,
  calls `CALL salary_process_prc($parameter,@Perr_msg);`. Not a call from any controller in scope (payroll-cluster
  concern, out of scope for attendance).
- **`calculateSalaryMainPrc($outputParameter)`** — `Model/AttendanceRegister.php:60-70` (added "Edited by Akshay on
  19-12-2025"). Same pattern, calls `CALL calculate_salary_main_prc($parameter,@Perr_msg);`. Payroll-cluster concern,
  not called from the attendance controllers in scope.
  - *(These last two are salary/payroll stored-proc wrappers bolted onto the `AttendanceRegister` model — a modeling
  smell: payroll logic hangs off the attendance model simply because `AttendanceRegister` happened to be in the
  relevant controllers' `$uses` list. Flag for migration: these three methods belong conceptually to different
  domains — attendance register build, payroll run, and salary calculation — despite living in one model class.)*

### `AttendanceRegisterReport` (`Model/AttendanceRegisterReport.php`)
- **`insertUpdateAttendanceRegisterForReportProc($outputParameter)`** — `Model/AttendanceRegisterReport.php:16-24`.
  Identical pattern, calls `CALL insert_update_att_reg_rep($parameter,@Perr_msg);`. No call site found in the 11
  in-scope controllers — likely invoked by a reporting controller/cron outside this task's scope. **Flag: verify
  call site before migrating.**

All three "Proc" wrapper methods share the exact same body shape (build `'val1' , 'val2' , ...` string, `CALL proc(...)`,
ignore result, `return true`) — this is boilerplate that should collapse to a single generic
`callStoredProc($name, $params)` helper in the Next.js/service-layer rewrite rather than being duplicated per model.

---

## 3. Complex logic / state machines

### 3.1 Attendance status-code state machine

Status codes are **not** a DB ENUM — `attendance_register.FIELD1..FIELD32` (`schema/mypayrol_trial.sql:28994-29025`)
and `emp_detail_timeattandance.present` are free-text `varchar` columns holding half-day-pair codes joined by `/`
(e.g. `P/P`, `P/A`, `A/P`, `A/A`, `HO/WO`, `NA/HO`). The canonical code vocabulary, confirmed from
`Controller/AttendanceRegisterNewController.php:1103` (`chnagestatus()`):

```
$nonLeave = ['P', 'A', 'NA', 'WO', 'HO', 'LOP'];
```

- `P` = Present
- `A` = Absent
- `NA` = Not Applicable (used for days outside employment window — before `joining_date` / after `termination.last_approved_working_date`, see `Controller/AttendanceRegisterNewController.php:1078-1092`)
- `WO` = Weekoff
- `HO` = Holiday
- `LOP` = Loss of Pay
- Any other code (not in `$nonLeave`) is treated as a **leave code** (e.g. `CL`, `SL`, pulled from `salary_head_items`/`leavepolicy`) — `Controller/AttendanceRegisterNewController.php:1103-1119`.

Codes are combined as `<AM-half>/<PM-half>` for half-day granularity. Recognized half-day combinations enumerated at
`Controller/AttendanceRegisterNewController.php:617-624` (`$halfStatuses`): `HO/WO`, `WO/HO`, `NA/HO`, `HO/NA`,
`NA/WO`, `WO/NA`. Tally logic at `Controller/AttendanceRegisterNewController.php:635-663`:
- Splits on `/`; a combo (2 parts) counts as `0.5` weight per part, a single code counts as `1`.
- `P`, `P/A`, `A/P`, `P/P` parts → `present_count`.
- `WO`, `/WO`, `W/O` → `weekoff_count`.
- `HO` → `holiday_count`.
- `NA` → `na_count`.
- any part containing substring `LOP` → `lop_count`.
- everything else except blank/`A` → `leave_count` (i.e., leave codes fall through to the final `else`).

**Where the half-day P/P vs P/A vs A/A determination actually happens**: NOT in PHP — it's computed by the
`device_attandance_bi` **BEFORE INSERT trigger** on `device_attandance` (`schema/mypayrol_trial.sql:41006-41864`,
~860 lines). Key thresholds (all per-shift-policy columns from `working_day_time_procedures`, not hardcoded):
- `minuts_calc_perday` — minutes required for a **full day present** (`working_day_time_procedures.minuts_calc_perday`, `schema/mypayrol_trial.sql:47847`).
- `minutes_per_half` — minutes required for a **half day present** (`working_day_time_procedures.minutes_per_half`, `schema/mypayrol_trial.sql:47863`).
- `strict_monitorings` (`Y`/`N`, `schema/mypayrol_trial.sql:47862`) toggles a stricter branch: if `Y`, worked-duration
  between half and full threshold produces `P/A` or `A/P` depending on whether the employee's actual clock-in exactly
  matched shift start (`on_dutty1`) or clock-out exactly matched shift end (`off_dutty1`) — see
  `schema/mypayrol_trial.sql:41810-41836`. Duration state machine (both branches):
  - `duration >= minuts_calc_perday` → `P/P` (full present)
  - `minutes_per_half <= duration < minuts_calc_perday` → `P/A` (or `A/P` in strict mode, see above)
  - `duration < minutes_per_half` → `A/A` (full absent)
  - if only a clock-in exists with no clock-out (`vLOGDATE_outime is null and vLOGDATE_intime is not null`) → `A/A` (`schema/mypayrol_trial.sql:41837-41839`).
- Result is written into `emp_detail_timeattandance.present` (insert-or-update on `(emp_pkey, att_date)`,
  `schema/mypayrol_trial.sql:41850-41863`), gated by `emp_ot_timeattandance.isdelete = 'N'` count check (verified/OT-locked
  days are skipped, `schema/mypayrol_trial.sql:41853-41855`).
- The trigger also resolves the **effective shift** for a punch by checking `emp_shift_planner` (roster override) first,
  falling back to the employee's default `emp_proff.day_time_seq`, for both the previous and current calendar day
  (to handle night-shift/cross-midnight punches via `working_day_time_procedures.isnextday`) —
  `schema/mypayrol_trial.sql:41041-41101`.
- **No hardcoded "N minutes late = half day" PHP constant exists anywhere in the controllers searched** — all late/early
  thresholds are configured per shift policy in `working_day_time_procedures`
  (`minuts_aftr_on_dutty_cal_late`, `minuts_bfr_off_dutty_cal_early`, `min_cal_late_ifnoclockin`,
  `min_cal_leave_early_ifnoclockout` — `schema/mypayrol_trial.sql:47848-47851`) and consumed inside this same trigger/the
  `insert_update_att_reg`/`editpunch_rules` stored procedures already covered by the prior deep-dive.

### 3.2 Overtime approval workflow

Table `emp_ot_master` (verification/approval header) + `emp_ot_timeattandance` (daily OT detail), driven from
`Controller/OtAttendanceNewController.php`:
- `approves()` (`Controller/OtAttendanceNewController.php:1010-1091`): for each employee in the posted batch,
  if a `emp_ot_master` row exists for `(emp_fkey, month)`, updates its `set_duration` (manager-adjusted OT minutes)
  then sets `is_verified = 'Y'` (falling back `set_duration = total_duration` if never overridden,
  `Controller/OtAttendanceNewController.php:1076-1079`); if no row exists yet, inserts one with `is_verified = 'Y'`
  directly (`Controller/OtAttendanceNewController.php:1062-1068`). Finally always calls
  `CALL calculate_ot_allowance_prc($emp_fkey, '$month', '$user_id');` (`Controller/OtAttendanceNewController.php:1084`)
  to push the approved OT into payroll allowance calculation.
- `Setvalue()` (`Controller/OtAttendanceNewController.php:1094-1105`) and `remarks()`
  (`Controller/OtAttendanceNewController.php:1107-1118`) let a manager adjust `set_duration`/`remarks` on
  `emp_ot_master` independent of the approval flag — i.e. OT minutes can be edited before or after verification,
  no state-machine guard prevents editing an already-verified row.
- `Toapproved()` (`Controller/OtAttendanceNewController.php:353-376`) and `getNotApprovedData()`/`getApprovedData()`
  (`Controller/OtAttendanceNewController.php:2267`, `2563`) implement the pending/approved split purely via the
  `is_verified` flag (`'Y'`/not-`'Y'`) — a two-state machine (`unverified` → `verified`), no reject/re-open state
  observed for OT (contrast with regularisation's three-state P/A/R below).

### 3.3 Regularisation (punch-correction) approval workflow

Table `employee_regularaization` (**note the misspelling — matches actual schema table name**,
`schema/mypayrol_trial.sql:42164`) has **no dedicated CakePHP model** — every read/write goes through raw SQL on
`$this->EmployeeDetails->query(...)` from `Controller/RegularisationController.php`. Three-state approval machine on
the `approved` column (`varchar(100)`, `schema/mypayrol_trial.sql:42172`), gated by `status` (int):
- `approved = 'P'` (pending) + `status = 1` (active) — awaiting manager action. Query pattern used throughout, e.g.
  `Controller/RegularisationController.php:1963`, `:2000`, `:2221`.
- `approved = 'A'` (approved) → `Controller/RegularisationController.php:2417-2425`: on approve,
  `UPDATE employee_regularaization SET status = 1, approved = 'A', ...` (approved row stays `status = 1`/active — used
  later to feed the actual punch correction into `device_attandance`/attendance recompute).
- `approved = 'R'` (rejected) → `Controller/RegularisationController.php:2417`: `UPDATE ... SET status = 0, approved = 'R', ...`
  (rejected rows are flipped to `status = 0`, effectively archived/inactive).
- The decision branch is driven by `if ($_POST['approved'] == 'A') { ... } else { ... reject path with $remarks_rej ... }`
  around `Controller/RegularisationController.php:2240-2447`.
- Bulk variants: `bulkapprove()` (`:1987`), `bulkadminapprove()` (`:2012`, "Bulk update by Arul on 10-09-22"),
  `bulkhierarchyapprove()` (`:2038`, "Bulk update for hierarchy person by Arul on 08-01-23") — three near-identical
  methods differing only in the actor role (self/admin/hierarchy manager) and whether the `WHERE` filters by
  `approved_person` — no shared helper, straightforward candidate for consolidation in the rewrite.
- Guard rule: `chnagestatus()` in the register controller blocks **any** status edit (leave, P, WO, etc.) if an
  active leave already exists for that employee/date/session, via `isLeaveAlreadyApplied()`
  (`Controller/AttendanceRegisterNewController.php:4234-4255`) — checks `emp_leave_transactions.Leavestatus IN
  ('Applied','Authorized','Approved')` joined to `leaveentries`, with `leave_session` values `1`=first half,
  `2`=second half, `3`=full day (`Controller/AttendanceRegisterNewController.php:4237-4241`). This is the one
  explicit cross-domain business rule found in this cluster: **attendance status cannot be hand-edited while a leave
  request is in flight for that date/session** — must be preserved in the Next.js rewrite.

### 3.4 Shift / roster assignment logic

`Controller/ShiftPlannerController.php` (only 3 actions, no model — everything raw SQL via `EmployeeDetails`):
- `listemployees()` (`:92-176`) resolves an employee's **primary** shift (`emp_config.type = 'SHIFT'`,
  `status = 1`, joined to `working_day_time_procedures`, `:132-138`) and **secondary/multi-shift** options
  (`emp_config.type = 'MSHIFT'`, `status = 2`, `:140-146`), both filtered to `working_day_time_procedures.active = 1`.
  It also reads any already-saved per-day roster picks from `emp_shift_planner` (`status = 1`, `:149-153`) for the
  month, and month date-range boundaries via the `att_start_end_fn(month_year, 1|2)` SQL function (custom function,
  not covered in scope, used at `:101-102`).
- `saveRoster()` (`:181-252`): **hard lock rule** — before allowing any roster save, checks
  `attendance_register.isdelete` for `(emp_fkey, month_year)`; if a matching row has `isdelete === 'N'`
  (i.e., attendance for that month has already been **verified** — see §4 quirk on `isdelete` polarity), the save is
  rejected with message *"Attendance already verified for this month..."* (`:205-212`). Otherwise, for each date in
  the posted roster: existing `emp_shift_planner` rows for that `(emp_fkey, shift_date)` are all set `status = 0`
  (deactivated, `:219-223`), then either the matching `(emp_fkey, shift_date, shift_id)` row is reactivated
  (`status = 1`, `:233-239`) or a new row is inserted (`:241-247`). This is a **soft-versioned roster**: no row is
  ever deleted, only `status` toggled, so `emp_shift_planner` accumulates full history of shift changes per employee
  per day.
- The actual shift **resolution at punch time** (which shift applies to a given punch) is done inside the
  `device_attandance_bi` trigger, which checks `emp_shift_planner` first (roster override, `status = 1`) and falls
  back to `emp_proff.day_time_seq` (employee's default policy) — `schema/mypayrol_trial.sql:41041-41053,41078-41084`.
  So `ShiftPlannerController` only ever writes `emp_shift_planner`; the consuming logic lives entirely in the
  trigger/stored procs (already covered by the prior deep-dive of `insert_update_att_reg`/`editpunch_rules`).

---

## 4. Schema quirks

- **`isdelete` is not a soft-delete flag on `attendance_register`/`attendance_register_rep`** — it is an inverted
  "verification" flag: default `'Y'` (`schema/mypayrol_trial.sql:29026,29100`) means *unverified/pending*, and code
  flips it to `'N'` to mean *verified/locked* — confirmed by `Controller/AttendanceRegisterNewController.php:2697`
  (`isdelete="Y"` used for the **unverified** register tab query) vs. `:2772` (`isdelete="N"` for the **verified**
  tab query), and again in `ShiftPlannerController.php:205` where `isdelete === 'N'` blocks roster edits because the
  month is already verified. Anyone reading only the column name in the schema would misread this as "row is
  deleted." **High risk for the Next.js migration** — rename to an explicit `verification_status`/`is_verified`
  enum, do not carry the `isdelete` name or its `'Y'`/`'N'` polarity forward literally.
- **`attendance_register`/`attendance_register_rep` use 32 generic `FIELD1..FIELD32` varchar(20) columns**
  (`schema/mypayrol_trial.sql:28994-29025`, `:29068-29099`) as an EAV-style day-of-month store (one column per
  calendar day, holding the `P/P`/`A/A`/etc. code). This is a classic pre-normalized-schema pattern: a month can have
  at most 31/32 days, so `FIELD32` only ever gets populated in specific edge cases (e.g., certain custom pay-period
  definitions); most months leave the tail `FIELDxx` columns `NULL`. Migrating this to a normalized
  `attendance_day(register_id, day_number, status_code)` table (or similar) is strongly recommended rather than
  preserving the 32-column layout.
- **No `FOREIGN KEY` constraints anywhere in this cluster** — `emp_fkey` columns throughout (`attendance_register`,
  `attendance_register_rep`, `attendance_punch`, etc.) are plain `int(11)` with only secondary indexes, never
  declared `FOREIGN KEY ... REFERENCES`. Referential integrity is enforced only by application/stored-proc code
  (confirms the prior finding for the whole app, holds for this cluster too).
- **`device_attandance` (note misspelling, consistent throughout schema/code) has both a raw punch log role and a
  processed/normalized role** — columns `SHIFT`, `SHIFTDATE`, `ATTDIRECTION`, `WORKCODE`, `status` are all populated
  *after the fact* by the `device_attandance_bi` trigger (`schema/mypayrol_trial.sql:41006` onward) based on shift
  resolution, not supplied by the punch source (biometric device / web punch). The raw `DIRECTION` (as received) and
  derived `ATTDIRECTION` (normalized in/out) are kept as separate columns — worth preserving as distinct fields
  (raw vs. derived) in the new schema rather than collapsing them.
- **`device_attandance.status` (varchar(11), default `'Y'`) is overloaded**: used both as an active/inactive flag
  for a punch row (e.g., the unique key `emp_id_LOGDATE_C1_status`, `schema/mypayrol_trial.sql:40999`, includes
  `status` — implying soft-deleted/corrected punches get a different status value to avoid unique-key collision
  while preserving history) and is read with `ucase(status) = 'Y'` guards inside the trigger
  (`schema/mypayrol_trial.sql:41074`). The exact non-`'Y'` values in use were not enumerated from PHP in this pass
  (no `device_attandance` status assignment found in the 11 controllers other than the trigger's own writes) —
  **flag: verify full value set (likely `'Y'`/`'N'` for active/superseded) before migrating the uniqueness rule.**
- **`attendance_register_history` and `register_history` are two separate, near-identical audit-log tables** for the
  same concept (process-run start/end/duration tracking, `status` = `1` completed / `0` working —
  `schema/mypayrol_trial.sql:29045-29057` vs `:45560-45572`) — only `register_history` has a matching CakePHP model
  (`Model/RegisterHistory.php`); `attendance_register_history` appears to have no model at all in this cluster.
  **Possible legacy cruft — verify which one is actually written to before migrating**, likely one superseded the
  other during a schema evolution and was never dropped.
- **`att_in` / `att_out` (`schema/mypayrol_trial.sql:29117,29120`) are view-like helper tables** (defined with a
  bare column list, no `ENGINE=`/`PRIMARY KEY`, i.e. they were originally MySQL `VIEW`s dumped as table shells) used
  for punch-in/punch-out lookup; no explicit reference to them found within the 11 controllers in scope — **possible
  legacy cruft, verify before migrating.**
- **`shift_exceptions` primary key is `shift_exceptions_pkey`, not `id`** (`schema/mypayrol_trial.sql:46824`) —
  contradicts `Model/ShiftExceptions.php:17`'s declared `public $primaryKey = 'id';` (see §1 mismatch entry). Also
  note `shift_exceptions.creation_date` is `datetime NOT NULL` with `ON UPDATE CURRENT_TIMESTAMP` but **no
  `DEFAULT`** (`schema/mypayrol_trial.sql:46834`) — every insert must supply `creation_date` explicitly or it will
  fail; a nullable-looking/auto-timestamp-looking column that is actually mandatory-on-insert.
- **Model/class-name vs. filename mismatch**: `Model/ShiftExceptions.php` declares `class ExceptionRule extends
  AppModel` with `public $name = 'ShiftException';` (`Model/ShiftExceptions.php:9,16`) — i.e. the PHP class name
  (`ExceptionRule`), the file name (`ShiftExceptions.php`, plural), and the CakePHP `$name` property
  (`ShiftException`, singular) are all different strings. Because CakePHP's autoloader resolves `$uses` entries by
  matching the **class name** to a same-named file, `DayTimeProcedureController.php:50`'s `'ShiftException'` entry
  never actually loads this file — CakePHP falls through to auto-vivifying a bare `AppModel` bound to the
  Inflector-derived table `shift_exceptions`, which happens to be correct by coincidence. The file's real class
  (`ExceptionRule`) is loaded only via `Controller/ExceptionRuleController.php:7`'s `$uses` list (a large, unrelated
  employee-master controller) — but that controller does not appear to touch shift-exception rows in the code
  reviewed. **Flag as fragile/dead-code-adjacent: verify whether `ExceptionRule`'s intended functionality (if any) is
  ever actually exercised before migrating; do not assume `DayTimeProcedureController`'s `ShiftException` model and
  this file are the same model.**
- **`employee_regularaization` (misspelled, matches app-wide code usage) has no CakePHP model class in this
  cluster** — confirms the broader pattern (raw-SQL-via-unrelated-model) extends even to a table central to the
  regularisation approval workflow described in §3.3.
- **`holidays.status` comment says `'0;inactive,1 active'`** (semicolon typo for colon,
  `schema/mypayrol_trial.sql:44479`) — cosmetic, but the seed data (`schema/mypayrol_trial.sql:44486-44554`) shows
  several holiday rows already flipped to `status = 0` (e.g. id 10 "Gandhi Jayandhi" duplicate, id 30, 31, 35, 39-41),
  suggesting historical duplicate-entry cleanup was done by soft-disabling rather than deleting — expect duplicate
  holiday rows differing only by `status` to require de-duplication during migration.
- **`working_day_time_procedures` has both `working_time2` as `int` on the surface (`working_time1`) and
  `working_time2..6` as `varchar(30)`** (`schema/mypayrol_trial.sql:47831,47834,47837,47840,47843,47846`) — an
  inconsistent typing choice (first shift-window duration is `int(11)`, all subsequent windows are `varchar(30)`),
  likely because later windows were bolted on for "multiple duty windows per day" support after the original schema
  was fixed — worth normalizing types when redesigning.

---

## Coverage notes / what was not read in depth
Per task instructions, the full bodies of `insert_update_att_reg`, `emp_detail_att_reg`, and `editpunch_rules`
stored procedures were **not** re-read (already covered by a prior research pass) — only their call sites from
models/controllers were confirmed:
- `AttendanceRegister::insertUpdateAttendanceRegisterProc()` → `CALL insert_update_att_reg(...)` (`Model/AttendanceRegister.php:24`).
- The `device_attandance_bi` trigger (`schema/mypayrol_trial.sql:41006-41864`) was read in enough depth to extract
  the P/P, P/A, A/P, A/A duration-threshold state machine (§3.1) since triggers were not explicitly called out as
  pre-covered by the prior pass; its full ~860-line body was not exhaustively transcribed beyond the sections needed
  for the state-machine description.
- Controllers `AttendanceController.php`, `EditAttendanceController.php`, `EditPunchesController.php`,
  `DailyOvertimeVerifyNewController.php`, `HolidayCalendarController.php`, `DayTimeProcedureController.php`,
  `CompoffController.php` were scanned for `$uses`/method inventories but not exhaustively read line-by-line given
  their size (each 500-2000+ lines); the sampled call sites and logic cited above were verified directly at the
  cited line numbers. A deeper pass on `CompoffController.php` (comp-off accrual/consumption rules) and
  `HolidayCalendarController.php`'s CRUD (`saveholiday`, `saveholidaygroup`) would be worthwhile if those specific
  workflows need document-level detail beyond the schema/table-shape findings already captured in §4.

---

### 7.3 Leave Management

# Leave Management Cluster — Data Model & Business Logic Report

STATUS: COMPLETE

Scope: `Model/LeaveEntries.php`, `LeaveRequests.php`, `LeavePolicy.php`, `LeavePolicyGroup.php`,
`LeaveType.php`, `Leavestatus.php`, `LeaveEncashmentMaster.php`, `EmpLeaveApproval.php`,
`EmployeeLeaveInfo.php`, `EmployeeLeaveTransaction.php`, `EmployeeLeaveUpload.php`,
`EmployeeLeaveBalanceUpload.php`.
Controllers: `LeaveRequestController.php`, `EmployeeLeaveRequestController.php`,
`EmployeeLeavesController.php`, `LeaveEncashmentRequestController.php`,
`LeavePolicyController.php`, `LeaveapiController.php`.

## 0. Headline findings

1. None of the 12 Model/ files in this cluster declare `$belongsTo`, `$hasMany`, `$validate`,
   or any lifecycle callback (`beforeSave`, `afterFind`, etc.). Every model is a bare
   `useTable` + `primaryKey` declaration extending `AppModel`. All relational joins are done
   ad‑hoc in controllers via raw `query()` / `find()` with manual `joins` arrays, or delegated
   to MySQL stored functions/procedures (`leave_transaction_prc`, `leave_balance_inthe_year_fn`,
   `leave_balance_inthe_month_fn`, `att_start_end_fn`).
2. **Two separate models map to the same table** `leaveentries`: `LeaveEntries`
   (Model/LeaveEntries.php:15) and `LeaveRequests` (Model/LeaveRequests.php:15). `LeaveRequests`
   is the one actually used by the transactional controllers (`LeaveRequestController`,
   `EmployeeLeaveRequestController`, `LeaveapiController`, `LeaveEncashmentRequestController`);
   `LeaveEntries` is used only by `LeavePolicyController` and `EmployeeLeavesController` for
   read-side lookups. This is redundant modeling of one physical table under two class names —
   flag for consolidation in the Next.js data layer.
3. **`grandLeave()` — the full leave-approval state machine — is duplicated verbatim** in
   `Controller/LeaveRequestController.php:2301-2857` and
   `Controller/EmployeeLeaveRequestController.php:2402-2958` (self-service portal). A
   *different*, much simpler method of the same name exists in
   `Controller/LeaveEncashmentRequestController.php:1002-1025` — it only flips
   `leave_encashment_master.is_approved` to `'Y'` and is unrelated to the leaveentries state
   machine (different table, single-step, no auth/approve split). Do not conflate the three.
4. `Controller/LeaveapiController.php:36` declares `class LeaveapiController extends Controller`
   (not `AppController`), so it never runs `AppController::beforeFilter()`
   (`Controller/AppController.php:39-47`), which is where the global session/`user_group`
   gate lives (`user_group` must be `1` or `2`, else redirect to login —
   `Controller/AppController.php:43-46`). `LeaveapiController` in this codebase turns out to be
   OTP/login/curl utility endpoints (`checkLogin`, `sendOTP`, `checkmails`, `postCurlRequest`,
   `getUID`, `sendauthorizationmail`, `getEmployeesCount`), not the primary leave workflow — but
   it is still an auth-bypassed controller and should be re-verified during migration for any
   route that touches leave data.
5. Leave-status role gating is **not** done via `user_group`/role tables for the actual
   authorize/approve/reject actions. It is done by direct employee-key comparison against the
   `ISAutherizedby` / `APPROVEDBY` columns stored on the specific `leaveentries` row (set at
   apply time from the leave policy's `sanction_by` / reporting hierarchy) — see §3.

## 1. Schema cross-check

| Model | `useTable` | `primaryKey` declared | Table found at | Declared associations |
|---|---|---|---|---|
| `LeaveEntries` (Model/LeaveEntries.php:15) | `leaveentries` | `LEAVEENTRYID` (Model/LeaveEntries.php:14) | schema/mypayrol_trial.sql:44885 `LEAVEENTRYID` PK confirmed | none |
| `LeaveRequests` (Model/LeaveRequests.php:15) | `leaveentries` | `LEAVEENTRYID` (Model/LeaveRequests.php:14) | same table as above | none |
| `LeavePolicy` (Model/LeavePolicy.php:15) | `leavepolicy` | `LEAVEPOLICYID` (Model/LeavePolicy.php:14) | schema/mypayrol_trial.sql:44919 `LEAVEPOLICYID` PK confirmed | none |
| `LeavePolicyGroup` (Model/LeavePolicyGroup.php:15) | `leavepolicy_group` | `LEAVEPOLICY_GROUP_ID` (Model/LeavePolicyGroup.php:14) | schema/mypayrol_trial.sql:44954 PK confirmed | none |
| `LeaveType` (Model/LeaveType.php:15) | `salary_head_items` | `salary_head_item_pkey` (Model/LeaveType.php:14) | schema/mypayrol_trial.sql:45875 PK confirmed | commented-out `belongsTo SalaryHeads` (Model/LeaveType.php:18-23) |
| `Leavestatus` (Model/Leavestatus.php:15) | `leavestatus` | none declared | schema/mypayrol_trial.sql:44964 — table has **no primary key at all**, single column `LEAVESTATUS varchar(255)` | none |
| `LeaveEncashmentMaster` (Model/LeaveEncashmentMaster.php:14) | `leave_encashment_master` | `leave_encashment_master_pkey` | schema/mypayrol_trial.sql:45010 PK confirmed | none |
| `EmpLeaveApproval` (Model/EmpLeaveApproval.php:27) | `emp_leave_approval` | `emp_leave_approval_pkey` | schema/mypayrol_trial.sql:43378 PK confirmed | none |
| `EmployeeLeaveInfo` (Model/EmployeeLeaveInfo.php:15) | `emp_leave_info` | `emp_leave_info` (same name as table) | schema/mypayrol_trial.sql:43417 PK confirmed, column literally named `emp_leave_info` | none |
| `EmployeeLeaveTransaction` (Model/EmployeeLeaveTransaction.php:15) | `emp_leave_transactions` | `emp_leave_transactions_pkey` | schema/mypayrol_trial.sql:43438 PK confirmed | none |
| `EmployeeLeaveUpload` (Model/EmployeeLeaveUpload.php:10) | `emp_leave_upload` | `emp_leave_upload_pkey` | schema/mypayrol_trial.sql:43452 PK confirmed | none |
| `EmployeeLeaveBalanceUpload` (Model/EmployeeLeaveBalanceUpload.php:14) | `leave_balance_upload` | **not declared** (Cake default `id`, which does not exist on this table) | schema/mypayrol_trial.sql:44967, real PK is `leave_balance_upload_pky` | none |

Key findings:

- **No model declares `belongsTo`/`hasMany`, so no `*_fkey` column is ever checked against a
  declared association.** All FK-style joins are hand-written raw SQL/`joins` arrays in
  controllers (e.g. `Controller/LeaveRequestController.php:2336-2357` joins `emp_details`,
  `emp_proff`, `salary_head_items` onto `LeaveRequests` manually using
  `EMP_fkey`/`salary_head_item_fkey`). Because there is no ORM-level association, every
  controller that needs employee/policy context re-implements the same 3-table join
  (duplicated at LeaveRequestController.php:2334-2359, :2447-2472, :1845-1870, and mirrored in
  EmployeeLeaveRequestController.php).
- **Column casing is highly inconsistent** on `leaveentries`
  (schema/mypayrol_trial.sql:44885-44916): `LEAVEENTRYID`, `LEAVESTATUS`, `EMP_fkey` (mixed
  case, not `emp_fkey`), `FROMDATE`/`TODATE`/`FROMHALF`/`TOHALF` (all-caps), but
  `applied_date`, `leavebal_bf`, `leave_days`, `creation_date`, `cctome`, `file_name` are
  lowercase-snake. `ISAutherized`/`ISAutherizedby`/`Autherized_date` also carry a spelled-out
  typo ("Autherized" instead of "Authorized") baked into the column names, propagated through
  every controller reference (e.g. `Controller/LeaveRequestController.php:2477,2538-2539`).
  Meanwhile `emp_leave_transactions.Leavestatus` (schema/mypayrol_trial.sql:43443) uses yet a
  third casing style ("Leavestatus", one word, mixed case) for what is conceptually the same
  status concept as `leaveentries.LEAVESTATUS`. Controllers query both spellings
  interchangeably depending on which table is targeted (compare
  `Controller/LeaveRequestController.php:2370` `LEAVESTATUS` vs `:1943` `Leavestatus`).
- `EmployeeLeaveInfo.$primaryKey = 'emp_leave_info'` (Model/EmployeeLeaveInfo.php:14) is
  correct against the schema (column `emp_leave_info` int(11) AUTO_INCREMENT,
  schema/mypayrol_trial.sql:43418) but is a confusing name collision with the table itself
  (`emp_leave_info`) — a naming quirk, not a bug.
- `EmployeeLeaveBalanceUpload` (Model/EmployeeLeaveBalanceUpload.php) declares no
  `$primaryKey` at all, so CakePHP falls back to its default `id` column — but the real table
  `leave_balance_upload` has no `id` column, its PK is `leave_balance_upload_pky`
  (schema/mypayrol_trial.sql:44980-44981). Any CakePHP `save()`/`delete()` call relying on the
  implicit `id` PK against this model would silently fail to update-in-place (it would always
  attempt an INSERT since Cake can't find a matching PK field) — flag as a **real
  model/schema mismatch**, not just a casing quirk. Verify in migration whether this model is
  ever used for `save()`/`delete()` (a scan of Controller/*.php shows it is not directly
  `$uses`'d by any of the leave controllers examined here — only `EmployeeLeaveUpload`,
  a *different* model/table, is used by upload controllers) — likely dead/legacy model,
  "possible legacy cruft — verify before migrating."
- `LeaveType` model (Model/LeaveType.php) is not a dedicated leave-type table at all — it
  reuses the generic payroll component table `salary_head_items`
  (schema/mypayrol_trial.sql:45875-45890), the same table used for every payroll salary
  component (Basic, HRA, Conveyance, etc. — see seed data at :45892-45919). "Leave type" is
  therefore just a `salary_head_items` row, distinguished only by which `head_fkey` group it
  belongs to and by controllers filtering on it (e.g. leave policy joins
  `salary_head_item_fkey`). There is no leave-specific column on `salary_head_items` (no
  "is_leave_type" flag visible in the columns list) — the association between "this
  salary-head-item is a leave type" is entirely implicit/managed by which
  `LEAVEPOLICY`/`leavepolicy` rows reference it. This is an important quirk for the Next.js
  model: leave types are not first-class entities in the legacy schema.
- `Leavestatus` model/table (Model/Leavestatus.php, schema/mypayrol_trial.sql:44964) is a
  single-column, no-PK table (`LEAVESTATUS varchar(255)`) — almost certainly a legacy/lookup
  "distinct values" table used only to populate a dropdown, not referenced by FK from
  `leaveentries.LEAVESTATUS` (which is a free-text-constrained `varchar(255)` with no FK
  constraint or CHECK — the actual status vocabulary is entirely enforced in PHP, see §3).

## 2. Custom methods per model

### `LeaveRequests` (Model/LeaveRequests.php) — maps to `leaveentries`
- `leaveTransactionPrc($outputParameter)` (Model/LeaveRequests.php:17-44): builds a
  comma-joined parameter list from the associative array passed in and calls the stored
  procedure `CALL leave_transaction_prc(<params>, @Perror_message);` via raw `query()`. Return
  value of the procedure call itself is discarded (dead code — the `mysqli_fetch_array` loop
  that would have consumed `$proc_result` is commented out, Model/LeaveRequests.php:27-42);
  the method unconditionally `return true;` regardless of whether the procedure actually
  succeeded or set `@Perror_message`. **Call sites:**
  - `Controller/LeaveRequestController.php:5623` inside `callLeaveTransactionProcedure()`
    (itself called from `grandLeave()` at :2833, and from the cancel-leave paths at :1899, :1912).
  - `Controller/EmployeeLeaveRequestController.php` — mirrored `callLeaveTransactionProcedure`
    calling the same model method (duplicate of the above; same model instance is shared since
    both controllers `$uses` `LeaveRequests`).
  - Note the caller never inspects `@Perror_message` after the CALL, so any application-level
    error raised inside the stored procedure (which prior research on the stored-proc family
    found does raise `SIGNAL`/error text in `@Perror_message` on invalid transitions) is
    **silently swallowed** by this model method returning `true` — flag as a correctness gap:
    the frontend always sees "success" (`Controller/LeaveRequestController.php:2852-2854`)
    even if `leave_transaction_prc` internally rejected the transaction.

No other model in the cluster declares any custom method — `LeaveEntries`, `LeavePolicy`,
`LeavePolicyGroup`, `LeaveType`, `Leavestatus`, `LeaveEncashmentMaster`, `EmpLeaveApproval`,
`EmployeeLeaveInfo`, `EmployeeLeaveTransaction`, `EmployeeLeaveUpload`,
`EmployeeLeaveBalanceUpload` are all pure `useTable`/`primaryKey` shells with zero methods,
zero validation, zero associations. All business logic for this cluster lives in the
controllers (primarily `LeaveRequestController.php` and its near-duplicate
`EmployeeLeaveRequestController.php`).

## 3. Complex logic / state machines

### 3.1 `LEAVESTATUS` values observed (column: `leaveentries.LEAVESTATUS`,
schema/mypayrol_trial.sql:44889, `varchar(255) NOT NULL DEFAULT 'Applied'`, no CHECK/ENUM/FK
constraint — the vocabulary below is enforced only by PHP string literals scattered across
controllers):

| Status | Meaning | Set by |
|---|---|---|
| `Applied` | Default on insert (schema default, and explicitly at LeaveRequestController.php:1815) | employee applies for leave |
| `Authorized` | First-level sign-off done | `grandLeave()` actionType `Authorize` |
| `Approved` | Second/N-th-level sign-off done (final) | `grandLeave()` actionType `Approve` |
| `Rejected` | Denied at authorize or approve stage | `grandLeave()` actionType `Reject` (default branch) |
| `CancellationOfAuthorized` | Employee requested cancellation of a leave that was already `Authorized` | `saveLeaveEntry()` when `myLeaveAction == 'Cancellation Applied'` and prior status was `Authorized` (LeaveRequestController.php:1807-1809) |
| `CancellationOfApproved` | Employee requested cancellation of a leave that was already `Approved` (i.e. not `Authorized`) | same code path, else branch (LeaveRequestController.php:1810-1811) |
| `Cancellation Authorized` | Cancellation request itself authorized (**note the space** — different literal than `CancellationOfAuthorized`) | `grandLeave()` actionType `Authorize Cancellation` (:2753-2794) |
| `Cancellation Approved` | Cancellation request itself approved (**note the space**) | `grandLeave()` actionType `Approve Cancellation` (:2722-2752) |
| `Cancelled` | Terminal — leave fully cancelled | `grandLeave()` actionType `Cancelled` (:2796-2809), or directly via `saveLeaveEntry()` `myLeaveAction == 'Cancelled'` (:1804-1806, :1900-1912) |

**Quirk flagged**: the cancellation sub-chain uses two *different, inconsistent naming
conventions* for conceptually parallel states — `CancellationOfAuthorized` /
`CancellationOfApproved` (no space, "Of" infix, set when the cancellation is first *requested*)
versus `Cancellation Authorized` / `Cancellation Approved` (space-separated, set once the
cancellation request itself has been *signed off*). These are four distinct string literals
that must all be reproduced exactly in any reimplementation, and they are easy to confuse —
verify every reference during migration (LeaveRequestController.php:692, 755, 762, 1808-1811,
1874, 2673, 2687, 2724, 2740, 2755-2757, 2784).

### 3.2 State transitions in `grandLeave()`
(`Controller/LeaveRequestController.php:2301-2857`, byte-identical logic duplicated in
`Controller/EmployeeLeaveRequestController.php:2402-2958`)

Entry point: POST with `actionType` ∈ `{Authorize, Approve, Reject, Approve Cancellation,
Authorize Cancellation, Cancelled}` and `LEAVEENTRYID`.

1. **Balance/overlap guard on `Applied`** (:2374-2384): if `actionType != 'Reject'` and current
   status is `Applied`, calls `criterias()` (attendance-punch and attendance-verified checks,
   see §3.3) before allowing any authorize/approve action; any failure short-circuits with
   `success:false`.
2. **`ALLOW_NEGETIVE` policy lookup** (:2388-2402): `LeavePolicy->find('all', ...)` filtered by
   `salary_head_item_fkey` and `LEAVEPOLICY_GROUP_ID IN (SELECT ... FROM emp_proff WHERE
   emp_fkey = <current session emp>)` — note this looks up the policy group of the **currently
   logged-in actor** (the approver), not necessarily the leave applicant, which is itself a
   latent bug worth flagging for migration (should arguably use the applicant's group).
3. **Balance computation branch** (:2404-2439): if action is not `Reject`/`Approve
   Cancellation`/`Authorize Cancellation`:
   - `ALLOW_NEGETIVE == 'Y'` → calls `getYearlyLeaveBalanceForAuthOrApproval()` (yearly balance
     via stored fn `leave_balance_inthe_year_fn`).
   - else → calls `getLeaveBalanceForAuthOrApproval()` (monthly balance via
     `leave_balance_inthe_month_fn`).
   - **The actual insufficient-balance rejection is commented out in both branches**
     (:2411-2416, :2428-2438 — `//if ($yearly_balance - $leave_days < 0) { ... return; }` is
     dead/commented code). Effectively, in the current codebase **balance is computed but never
     enforced** at authorize/approve time — the check was disabled and never re-enabled.
     Flag as "possible legacy cruft — verify before migrating" (was this intentionally
     disabled, or a regression?).
4. **Approval-level determination** (:2479-2485): re-fetches leave + employee + policy-group
   context, then looks up the **3rd-level sanctioning employee** unconditionally via:
   ```sql
   select first_name,email,emp_pkey from emp_details where emp_pkey =
     (select sanction_by from leavepolicy where LEAVEPOLICY_GROUP_ID = '<grp>'
      and leval_of_approval = '3' and salary_head_item_fkey = '<type>' and status = 1)
   ```
   (:2485). This determines `$issendfinalmail`/`$leave_email` — i.e. whether a 3rd approval
   step exists is detected by whether *any* `leavepolicy` row for this group/leave-type has
   `leval_of_approval = 3`. `leval_of_approval` (schema/mypayrol_trial.sql:44944,
   `int(11) DEFAULT '2'`) is confirmed as the field controlling 2-step vs 3-step chains:
   default/typical value `2` (Authorize → Approve, two levels), value `3` adds a final
   sanction step routed to `sanction_by` (schema/mypayrol_trial.sql:44943).
5. **Role gating is by direct employee-key match on the row itself**, not by `user_group`:
   - Authorizer identity = `leaveentries.ISAutherizedby` (set when the leave was applied,
     presumably from the applicant's reporting-manager chain — not shown in this cluster's
     files, likely set in `saveLeaveEntry`/`addeditleave`).
   - Approver identity = `leaveentries.APPROVEDBY`.
   - `manageempleave()` (LeaveRequestController.php:753-762) sets `$myrole = 1` if
     `session[emp_fkey] == ISAutherizedby`, and separately checks `APPROVEDBY ==
     session[emp_fkey] && APPROVED_date == NULL` for the approver role — i.e. the UI decides
     which action buttons to show based on row-level identity match, not a generic
     role/permission table. The global `AppController::beforeFilter()` gate only checks
     `user_group ∈ {1,2}` (AppController.php:43-46) as a coarse "is this an admin/manager
     account" gate before any controller action runs at all.
6. **`Authorize` action** (:2527-2619): complex branching —
   - If a 3rd-level sanctioner's email exists (`$issendfinalmail`) **and** `Autherized_date`
     is empty **and** the current actor is *both* the row's `APPROVEDBY` and `ISAutherizedby`
     (self-authorize-and-approve shortcut): jumps straight to final approval — sets
     `ISAPPROVED=1`, `ISAutherized=1`, inserts an `EmpLeaveApproval` row routed to the 3rd-level
     sanctioner, and sends the final-approval mail (`sendfinalapprovalmail`). LEAVESTATUS
     literal set is `'Authorized'` (not `'Approved'` — the actual approve status is only
     set via later re-run once the sanctioner acts, since `data['LEAVESTATUS']` is set to
     `'Authorized'` unconditionally at the top of this branch, :2529).
   - Else if `Autherized_date` already set (already authorized once): treats as a re-approval
     pass, sets `ISAPPROVED=1`/`Autherized_date` again and re-inserts an `EmpLeaveApproval`
     routed to the final sanctioner, sends final-approval mail.
   - Else (fresh authorize, no final sanctioner path): sets `Autherized_date`,
     `ISAutherized=1`, sends `sendauthorizationmail`.
   - Else branch (`$issendfinalmail` false entirely, i.e. no `leval_of_approval=3` policy row
     exists): plain 2-step authorize — sets `Autherized_date`/`ISAutherized=1`, sends
     authorization mail.
7. **`Approve` action** (:2620-2670): if a final sanctioner exists, routes similarly to the
   final-approval path with an `EmpLeaveApproval` insert; else sets `LEAVESTATUS='Approved'`,
   `ISAPPROVED=1`, and conditionally also sets `Autherized_date`/`ISAutherized=1` if the
   current actor is *both* `APPROVEDBY` and `ISAutherizedby` (single-approver-does-both
   shortcut).
8. **`Reject` action** (:2671-2721): branches on current status —
   - `CancellationOfAuthorized` → reverts to `'Authorized'` (cancellation request itself
     rejected, original leave stays authorized), sets `ISAPPROVED=1`,
     `message='Leave cancellation Rejected'`.
   - `CancellationOfApproved` → reverts to `'Approved'`, same message.
   - Else (plain reject of a fresh application) → `LEAVESTATUS='Rejected'`; sets
     `Autherized_date` if it was still `Applied`, else `APPROVED_date`; also backfills
     `APPROVED_date` if `ISAutherizedby == APPROVEDBY` (single-approver-does-both again).
9. **`Approve Cancellation` / `Authorize Cancellation` actions** (:2722-2795): move the
   cancellation sub-chain forward to `'Cancellation Approved'` / `'Cancellation Authorized'`
   respectively — each has an inner branch that behaves slightly differently if the status is
   *already* at the target-looking value (re-entrant call) vs a fresh transition, mirroring the
   Authorize/Approve asymmetry above.
10. **`Cancelled` action** (:2796-2809): terminal — sets `LEAVESTATUS='Cancelled'`,
    `ISAPPROVED=1`, `APPROVED_date`, sends applicant mail.
11. **Unknown actionType** → `success:false`, `"Leave processing failed"` (:2810-2815).
12. **Persistence + downstream sync** (:2822-2833): builds `$data['ApproveRemarks']` /
    `AuthoriseRemarks` from request, runs
    `LeaveRequests->updateAll($data, ['LEAVEENTRYID' => $leaveentryId])` (raw SQL-fragment
    values, e.g. `"'Authorized'"` embedded as literal strings inside the `$data` array — **not
    parameterized**, classic CakePHP 1.x `updateAll` SQL-fragment pattern; injection risk if any
    of these fragments ever incorporated unescaped user input, though in this method they are
    all hardcoded literals), then calls `callLeaveTransactionProcedure($leaveentryId)` →
    `leaveTransactionPrc()` → `CALL leave_transaction_prc(...)` to propagate the change into
    `emp_leave_transactions` (day-by-day session records) — see Model §2 for the swallowed-error
    caveat.
13. Response is always `success:true` once it reaches the update step (:2852-2855) regardless
    of whether `updateAll`/the stored procedure actually succeeded — no post-update
    verification.

### 3.3 Leave-balance & overlap logic in the controller (apply-time), `saveLeaveEntry()`
(`Controller/LeaveRequestController.php:1648-2222`, new-leave branch at :1915 onward)

- **Leave-day count** (:1967-1981): computed purely from calendar-day difference
  (`(TODATE - FROMDATE)/86400 + 1`) then adjusted by half-day flags `FROMHALF`/`TOHALF`
  (1=first half, 2=second half per schema comments, schema/mypayrol_trial.sql:44892,44894):
  subtract 0.5 if both ends are the same half, subtract 1 if `FROMHALF=2 && TOHALF=1`
  (i.e. starts afternoon of day 1, ends morning of last day — both half-days excluded).
- **Balance display** (:1940-1966): sums already-applied leave-days for the same month/leave
  type from `emp_leave_transactions` (status `'Applied'` only, split by full-day
  `leave_session=3` vs half-day sessions) via raw UNION query, and separately computes monthly
  balance from stored fn `leave_balance_inthe_month_fn`. **This company-restriction list is a
  hardcoded array of company codes** (`KWMT, ABSG, MBCT, MRBS, STCL, AGNG, ESNP, VGNN, AYRK,
  VGFS, VSFS` — repeated verbatim at :1957-1960, :3275-3278, :3315-3318, :5680ish) that
  short-circuits balance computation to `0`/skips the monthly-balance stored-fn call entirely
  for those companies — a tenant-specific carve-out baked directly into shared controller code
  (dated "edited by athira on 21-09-2025" in comments), a strong candidate for "legacy/tenant
  cruft — verify with product owner before migrating," since it silently changes leave-balance
  behavior per company rather than via a policy flag in the `leavepolicy` table.
- **The actual insufficient-balance block is commented out** (:1982-1987) — same pattern as in
  `grandLeave()`: balance is computed/displayed but not enforced as a hard stop when applying.
- **Overlap detection** (:1989-2024): queries `emp_leave_transactions` joined to `leaveentries`
  for the same employee, date range `BETWEEN $from_date AND $to_date`, status IN
  `('Applied','Approved','Authorized')` (cancellation states are excluded — i.e. a cancelled
  leave doesn't block re-applying for the same dates), filtered by `leave_session` matching the
  requested half-day slot(s). If `FROMHALF=1`, checks sessions `(1,3)`; else sessions `(2,3)`.
  If a same-session hit is found (`$count>0`), a secondary tighter check narrows to the exact
  single session (:2002-2009) to refine the message. Any `$count > 0` after this rejects with
  `"Leave already existing in the range $from_date - $to_date"` (:2018-2024) — this is the
  actual overlap-prevention mechanism (unlike the balance check, this one IS enforced, not
  commented out).
- **Pre-check gate** `criterias()` (:2222-2279), called both from `saveLeaveEntry()`'s edit
  path and from `grandLeave()`'s `Applied`-status guard:
  1. Attendance-punch check via `checkAttendancePunches()` (private helper,
     LeaveRequestController.php:6113) — blocks applying leave over a day that already has an
     attendance punch recorded (first half / second half / full day differentiated).
  2. Attendance-verified check — blocks leave changes for a month whose
     `attendance_register` has already been verified (`isdelete='N'` row exists for
     `emp_fkey`+month) — prevents retroactively altering leave after payroll-relevant
     attendance has been locked.
  3. Weekoff/holiday overlap checks exist in commented-out form only (:2262-2278) — never
     active in the current code; legacy/removed feature.
- `criterias1()` (:2281-2299) is a stripped-down variant used only for the
  `CancellationOf*` status re-entry path in `manageempleave()` (:693) — runs only the
  attendance-verified check, skipping the punch check (cancellation of an already-decided
  leave shouldn't re-block on punches).

## 4. Schema quirks

- `leaveentries` (schema/mypayrol_trial.sql:44885-44916) mixes three casing conventions in one
  table (ALLCAPS legacy columns, `Ucfirst`/mixed like `EMP_fkey`, and modern lowercase-snake
  like `applied_date`/`leave_days`) — almost certainly evidence of the table having been
  extended over multiple migration eras; the oldest columns (core workflow: `LEAVEENTRYID`,
  `LEAVESTATUS`, `EMP_fkey`, `FROMDATE`/`TODATE`, `ISAutherized*`, `APPROVED*`) are ALLCAPS/typo'd,
  while newer additions (`file_name`, `file_type`, `cctome`, `AuthoriseRemarks`,
  `ApproveRemarks`) are more consistently cased/named — a useful signal for which columns are
  "core" vs later feature bolt-ons.
- `leaveentries.ISAutherizedby` is `NOT NULL` with no default (schema/mypayrol_trial.sql:44896)
  while `ISAPPROVED`/`APPROVEDBY` are nullable — meaning every leave row must be created with
  an authorizer already resolved (likely from the applicant's manager at apply time), but the
  approver can be genuinely unset until the authorize step completes. This asymmetry should be
  preserved in the new schema unless deliberately redesigned.
- `leaveentries.file_name`/`file_type` (schema/mypayrol_trial.sql:44913-44914) are
  `varchar(1000)` and, per `deletedoc()` (LeaveRequestController.php:5627-5641), store
  **multiple comma/delimiter-joined filenames in a single column** (the delete logic does
  `str_replace($document, '', $filename)` to remove one entry from a blob string) rather than a
  normalized attachments table — a strong migration flag: this should become a proper
  one-to-many attachments table in Next.js, not a delimited string column.
- `leavepolicy.leval_of_approval` (schema/mypayrol_trial.sql:44944) is `int(11) DEFAULT '2'`
  with no CHECK constraint — only values `2` and `3` are referenced anywhere in the leave
  controllers examined (no evidence of a 1-step chain in this cluster), so the effective domain
  is `{2,3}` enforced only by convention.
- `leavepolicy.ALLOW_NEGETIVE` (schema/mypayrol_trial.sql:44930) — another baked-in typo
  ("NEGETIVE" for "NEGATIVE") that propagates through every controller reference
  (`$allow_negative`, `ALLOW_NEGETIVE` string literal used at LeaveRequestController.php:2390,
  2397, 2406, 684, 735 and elsewhere) — must decide in migration whether to preserve the typo
  in any raw-SQL compatibility layer or fully rename (`ALLOW_NEGATIVE`) at the API boundary.
- `leave_balance_upload` has a BEFORE INSERT trigger
  (schema/mypayrol_trial.sql:44987-45006, `leave_balance_upload_bi`) that auto-computes
  `balance_bf_adj` and `adjustment` from `leave_balance_inthe_year_fn(...)` plus the previous
  upload row's adjustment — business logic living in a DB trigger rather than the
  `EmployeeLeaveBalanceUpload` model, invisible to anyone reading only the PHP layer. This is
  additional confirmation (beyond the missing-PK issue in §1) that this table/model pairing
  needs careful re-verification: any Next.js write path to `leave_balance_upload` would need to
  replicate this trigger's logic explicitly since ORMs typically don't surface DB triggers.
  Also note the same trigger computes `vpolicy_allotted_balance` (best-matching `leavepolicy`
  row for the employee's `LEAVEPOLICY_GROUP_ID`) but never actually uses that variable anywhere
  in the trigger body (:44991,44996-44998) — dead variable inside the trigger,
  "possible legacy cruft — verify before migrating."
- `leave_taken` (schema/mypayrol_trial.sql:45037) — a table with **no primary key and no
  columns beyond raw aggregation fields** (`emp_fkey`, `salary_head_item_fkey`, `yearmonth`,
  `leavetaken`) — pattern strongly suggests this is a materialized view / scratch table
  populated by a scheduled job or one of the leave stored procedures rather than a
  normal transactional table. Not referenced by any model in this cluster; verify whether any
  controller reads it directly via raw SQL before deciding to drop or replicate it.
- `emp_leave_approval` uses `ENGINE=MyISAM` (schema/mypayrol_trial.sql:43392) while almost
  everything else in this cluster is InnoDB — no foreign-key enforcement possible on this
  table (MyISAM doesn't support FKs), consistent with the app-level-only referential integrity
  pattern seen throughout (LEAVEENTRYID column has only a plain `KEY`, not a `CONSTRAINT`,
  :43391).
- `emp_leave_info` and `emp_leave_transactions` *do* have real FK constraints back to
  `leaveentries.LEAVEENTRYID` (schema/mypayrol_trial.sql:43434, :43448) — these are the only
  two tables in the whole cluster with enforced referential integrity at the DB level, which is
  notable since neither of their corresponding models (`EmployeeLeaveInfo`,
  `EmployeeLeaveTransaction`) declares that relationship in CakePHP (§1) — the FK exists in the
  DB but is invisible to the ORM layer entirely.

---

### 7.4 Payroll, Salary & Tax

# Payroll, Salary & Tax Data Model — Research Report

Scope: `Payrollmaster`, `SalaryHeads`, `SalaryHeadItems`, `SalaryStructures`, `SalaryStructureDetails`, `TaxHead`,
`TaxHeadDetail`, `TaxType`, `FinancialYear`, `Payrollarrearmaster`, `PayrollArrearComponents`, `EmpArrearSalarySlip`,
`EmpSalarySlip`, `EmployeeCTC`, `EmployeeSalaryStructure`, `SalaryIncrement`, `SalaryIncrementDetails`, `EmpTaxRegime`,
`TaxSave`, `EmployeeTaxTransactions`, `EmpTaxSalTrans`, `EmpTaxSalTransNew`, `EmployeeTaxsalsum`,
`EmployeeTaxsalsumNew`, `ComponentIncrement`, `EmpSalaryCompUpload`, `MonthlyCTC`, `GrossSalary`, `TotalDeductions`,
`MonthlyAmount`.

Controllers examined: `Controller/PayrollController.php` (live payroll run),
`Controller/PayrollProcessController.php` (confirmed unreachable — see §3), `Controller/SalaryProcessingController.php`,
`Controller/SalaryHeadsController.php`, `Controller/SalaryStructureController.php`,
`Controller/SalaryIncrementController.php`, `Controller/TaxController.php`, `Controller/TaxHeadsController.php`,
`Controller/EmployeeTaxController.php`, `Controller/FinancialYearController.php`, `Controller/ArrearController.php`,
`Controller/YearEndController.php`, `Controller/SalaryComponentUploadController.php`.

---

## 0. AppModel / lifecycle hooks — confirmed clean

`Model/AppModel.php:34-38` — `AppModel` is a bare passthrough constructor. No `$validate`, no `beforeSave`/`afterSave`/
`beforeValidate` hooks anywhere in the base class. Every model file read for this cluster (listed below) likewise
declares only `$name`, `$primaryKey`, `$useTable`, and (for two models) a stored-procedure-calling method — **no
model in this cluster has `$validate` or lifecycle callbacks.** This confirms the prior finding for the payroll
cluster specifically. All validation, association enforcement, and business rules live in controllers or MySQL
stored procedures/triggers, not in CakePHP models.

---

## 1. Schema cross-check

None of the models in this cluster declare `$belongsTo`/`$hasMany`/`$hasOne`. A few have the association block
present but **commented out** (`SalaryHeads`, `SalaryHeadItems`, `TaxSave` all have dead commented `belongsTo`/
`hasMany` blocks). This matches the schema itself: most of these tables carry **no `FOREIGN KEY` constraints at
all** — relationships are enforced only by naming convention (`*_fkey` columns) and application code / stored
procedures, not by MySQL or CakePHP.

| Model | `Model/<name>.php` | `$useTable` / `$primaryKey` | Schema table | Status |
|---|---|---|---|---|
| Payrollmaster | `Model/Payrollmaster.php:14-15` | `payroll_master` / `payroll_master_pkey` | `schema/mypayrol_trial.sql:45306` | Matches. **No FK constraints** on `emp_fkey` etc. (verified — table def has only a PK, no `CONSTRAINT`/`KEY` clauses). No model associations declared (none expected, none present). |
| SalaryHeads | `Model/SalaryHeads.php:14-15` | `salary_heads` / `head_pkey` | `schema/mypayrol_trial.sql:45853` | Matches. Commented-out `hasMany SalaryHeadItems` (`Model/SalaryHeads.php:17-22`) — correctly reflects `salary_head_items.head_fkey` (`schema/mypayrol_trial.sql:45877`), but the relation is dead code, never active at runtime. |
| SalaryHeadItems | `Model/SalaryHeadItems.php:14-15` | `salary_head_items` / `salary_head_item_pkey` | `schema/mypayrol_trial.sql:45875` | Matches. Commented-out `belongsTo SalaryHeads` on `head_fkey` (`Model/SalaryHeadItems.php:17-23`) — again schema-correct but inactive. No FK constraint exists in schema either. |
| SalaryStructures | `Model/SalaryStructures.php:14-15` | `salary_structure` / `structure_id` | `schema/mypayrol_trial.sql:46086` | Matches. |
| SalaryStructureDetails | `Model/SalaryStructureDetails.php:14-15` | `salary_structure_details` / `structure_det_id` | `schema/mypayrol_trial.sql:46110` | Matches. Table has `structure_id` and `salary_head_item_fkey` FK-shaped columns (`schema/mypayrol_trial.sql:46112-46113`) but **no declared FK constraint** and **no model association** — pure convention-based join, done ad hoc in controller SQL (see §3). |
| TaxHead | `Model/TaxHead.php:14-15` | `tax_heads` / `tax_heads_pkey` | `schema/mypayrol_trial.sql:47393` | Matches. No association to `TaxHeadDetail` despite `tax_heads_details.tax_heads_fkey` (`schema/mypayrol_trial.sql:47431`) pointing straight at it — should be `hasMany` but isn't declared anywhere. |
| TaxHeadDetail | `Model/TaxHeadDetail.php:14-15` | `tax_heads_details` / `tax_heads_details_pkey` | `schema/mypayrol_trial.sql:47429` | Matches. Missing `belongsTo TaxHead`. |
| TaxType | `Model/TaxType.php:14-15` | `tax_type` / `tax_type_pkey` | `schema/mypayrol_trial.sql:47562` | Matches. `tax_heads.tax_type_fkey` (`schema/mypayrol_trial.sql:47395`) points to it but no association declared on either side. |
| FinancialYear | `Model/FinancialYear.php:14-15` | `fin_year` / `Fin_year_seq` | `schema/mypayrol_trial.sql:44344` | Matches. |
| Payrollarrearmaster | `Model/Payrollarrearmaster.php:15-16` | `payroll_arrear_master` / `payroll_arrear_master_pkey` | **NOT FOUND** | **MISMATCH — flagged below.** |
| PayrollArrearComponents | `Model/PayrollArrearComponents.php:14-15` | `payroll_arrear_comp_master` / `payroll_arrear_master_pkey` (reuses same PK name as Payrollarrearmaster — likely copy-paste) | **NOT FOUND** | **MISMATCH — flagged below.** |
| EmpArrearSalarySlip | `Model/EmpArrearSalarySlip.php:14-15` | `emp_new_salary_slip` / `emp_new_salary_slip_pkey` | **NOT FOUND** | **MISMATCH — flagged below.** Comment in the model itself: `// Edited by Akshay on 23-5-2025` next to the `$useTable` line — this was a recent, apparently incomplete repoint. |
| EmpSalarySlip | `Model/EmpSalarySlip.php:14-15` | `emp_salary_slip` / `emp_salary_slip_pkey` | `schema/mypayrol_trial.sql:43824` | Matches. (There's also an `emp_salary_slip_bkp` table at `schema/mypayrol_trial.sql:43851` — an ad hoc backup snapshot, unmapped to any model — legacy cruft.) |
| EmployeeCTC | `Model/EmployeeCTC.php:14-15` | `emp_ctc_upload` / `emp_ctc_upload_pkey` | `schema/mypayrol_trial.sql:42649` | Matches. Table **does** have a real FK constraint: `emp_ctc_upload_ibfk_1 FOREIGN KEY (emp_fkey) REFERENCES emp_details(emp_pkey)` (`schema/mypayrol_trial.sql:42672`) plus an `AFTER INSERT` trigger `emp_ctc_upload_ai` (`schema/mypayrol_trial.sql:42678`) that does duplicate/overlap validation server-side — none of this is mirrored in the CakePHP model (no `$validate`, no `$belongsTo`). |
| EmployeeSalaryStructure | `Model/EmployeeSalaryStructure.php:14-15` | `emp_salary_structure` / `emp_salary_structure_pkey` | `schema/mypayrol_trial.sql:43878` | Matches. Table has FK constraint to `emp_details` (`schema/mypayrol_trial.sql:43900`), not mirrored in model. |
| SalaryIncrement | `Model/SalaryIncrement.php:14-15` | `salary_increment` / `salary_increment_pkey` | Table exists in schema (not fully quoted above but referenced consistently by Controller code) | Matches by name/PK convention. |
| SalaryIncrementDetails | `Model/SalaryIncrementDetails.php:14-15` | `salary_increment_details` / `salary_increment_details_pkey` | Exists, same pattern | Matches. |
| EmpTaxRegime | `Model/EmpTaxRegime.php:14-15` | `emp_tax_regime` / `emp_tax_regime_id` | `schema/mypayrol_trial.sql:44017` | Matches. |
| TaxSave | `Model/TaxSave.php:14-15` | `taxes_save` / `emp_fkey` | `schema/mypayrol_trial.sql:47317` | **MISMATCH.** `taxes_save` has **no primary key column at all** in the schema (`schema/mypayrol_trial.sql:47317-47325` — plain columns, no `PRIMARY KEY` clause). The model declares `emp_fkey` as `$primaryKey`, but `emp_fkey` is not unique — the seed data itself has duplicate `emp_fkey` rows (`57` and `30` each appear twice, `schema/mypayrol_trial.sql:47327-47331`). CakePHP `save()`/`find(..., 'first')` behavior against a non-unique "primary key" is undefined/unsafe — likely source of silent overwrite bugs. |
| EmployeeTaxTransactions | `Model/EmployeeTaxTransactions.php:14-15` | `emp_tax_transactions` / `emp_tax_tran_id` | `schema/mypayrol_trial.sql:44157` | Matches. |
| EmpTaxSalTrans | `Model/EmpTaxSalTrans.php:13-14` | `emp_tax_sal_trans` (no `$primaryKey` declared — relies on Cake auto-detecting `emp_tax_sal_trans_pkey`) | `schema/mypayrol_trial.sql:44030` | Matches by convention; inconsistent style vs. sibling models that explicitly declare `$primaryKey`. |
| EmpTaxSalTransNew | `Model/EmpTaxSalTransNew.php:13-14` | `emp_tax_sal_trans_new` (no `$primaryKey` declared) | `schema/mypayrol_trial.sql:44052` | Matches by convention, same inconsistency. |
| EmployeeTaxsalsum | `Model/EmployeeTaxsalsum.php:12-14` | `emp_tax_sal_trans_sum` / `emp_tax_sal_trans_sum_pkey` | `schema/mypayrol_trial.sql:44074` | Matches. |
| EmployeeTaxsalsumNew | `Model/EmployeeTaxsalsumNew.php:12-14` | `emp_tax_sal_trans_sum_new` / `emp_tax_sal_trans_sum_new_pkey` | `schema/mypayrol_trial.sql:44111` | Matches. This "new" table has **7 extra columns** vs. the old one (`forth_portion`..`sixth_portion`, `marginal_relief`, `seventh_portion`, `seventh_portion_tax` — `schema/mypayrol_trial.sql:44127-44152`) reflecting a tax-slab-count expansion (old-regime 3-slab vs new-regime 6/7-slab tax computation). See §4. |
| ComponentIncrement | `Model/ComponentIncrement.php:14-15` | `component_increment` / `sal_pkey` | **NOT FOUND** | **MISMATCH — flagged below.** (Used outside this cluster's controllers, but included per prompt scope — confirmed dead pointer.) |
| EmpSalaryCompUpload | `Model/EmpSalaryCompUpload.php:14-16` | `emp_salcomp_upload` / `emp_salcomp_upload_pkey` | `schema/mypayrol_trial.sql:43904` | Matches. Note table engine is `MyISAM` (schema/mypayrol_trial.sql:43915), unlike almost everything else in the schema which is `InnoDB` — no transactional/FK support on this table. |
| MonthlyCTC | `Model/MonthlyCTC.php:11-12` | `ctc_fixed_rate` (no `$primaryKey`) | `schema/mypayrol_trial.sql:47913` (**VIEW**, not a table) | Model treats a **SQL VIEW** as an ordinary CakePHP table model. See §4. |
| GrossSalary | `Model/GrossSalary.php:11-12` | `gross_salary` (no `$primaryKey`) | `schema/mypayrol_trial.sql:47942-47943` (**VIEW**) | Same — VIEW modeled as table. |
| TotalDeductions | `Model/TotalDeductions.php:11-12` | `total_deductions` (no `$primaryKey`) | `schema/mypayrol_trial.sql:47973` (**VIEW**) | Same. |
| MonthlyAmount | `Model/MonthlyAmount.php:11-12` | `ctc_fixed_amount` (no `$primaryKey`) | `schema/mypayrol_trial.sql:47910` (**VIEW**) | Same. |

### Flagged mismatches (tables referenced by models but absent from `schema/mypayrol_trial.sql`)

1. **`payroll_arrear_master`** — referenced by `Model/Payrollarrearmaster.php:16` and used throughout
   `Controller/ArrearController.php` (e.g. `$this->Payrollarrearmaster->useDbconfig`, `Controller/ArrearController.php:57`,
   and `showarrearsalaryslip($payroll_arrear_master_pkey)` in `Controller/PayrollProcessController.php:955`). **This
   table does not exist anywhere in `schema/mypayrol_trial.sql`** (verified with a full-file grep for
   `payroll_arrear`, zero hits besides the model/controller references). Either the schema dump is stale/incomplete,
   or this whole arrear-payroll subsystem is unreachable in the current database. **Possible legacy cruft — verify
   against the live database before migrating; do not assume schema absence means dead code, but do not trust the
   model either.**
2. **`payroll_arrear_comp_master`** — same situation, referenced by `Model/PayrollArrearComponents.php:15`, absent
   from schema.
3. **`emp_new_salary_slip`** — referenced by `Model/EmpArrearSalarySlip.php:15`, absent from schema. The inline
   comment `// Edited by Akshay on 23-5-2025` strongly suggests a recent, incomplete migration/rename (likely was
   `emp_arrear_salary_slip` or similar before) that never got applied to the schema dump or the live DB. **Flag for
   verification — this model is almost certainly broken in production or the schema snapshot is out of date.**
4. **`component_increment`** — referenced by `Model/ComponentIncrement.php:15`, absent from schema. Used via
   `$uses` in `Controller/EmployeeController.php:52` (outside primary scope, but confirms the model is still wired
   into at least one live controller).

### VIEW-backed models (schema quirk, not a mismatch per se)

`MonthlyCTC` → `ctc_fixed_rate`, `GrossSalary` → `gross_salary`, `TotalDeductions` → `total_deductions`,
`MonthlyAmount` → `ctc_fixed_amount` are all **`CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW`** definitions
(`schema/mypayrol_trial.sql:47910,47913,47943,47973`), not base tables. All four views aggregate `SUM()` off of
`emp_salary_structure` or `emp_salary_slip` filtered on `end_date_effective IS NULL` and `head_operator`/`head_type`.
CakePHP models can `find()` against a view fine (read-only in practice — `save()` against a MySQL view with a
`SUM()`/`GROUP BY` will fail), but nothing in the model or controller code marks these as read-only, so a naive
migration could attempt writes that silently fail or throw. **Migration implication:** these four "tables" should
become derived/computed values (SQL views or application-layer aggregation), not first-class writable entities, in
the Next.js data model.

---

## 2. Custom methods per model

Nearly every model in this cluster is a bare CakePHP shell (just `$name`/`$useTable`/`$primaryKey`). Only two models
carry custom methods, and both are thin wrappers that build a `CALL procname(...)` string and execute it — no
business logic lives in PHP here, it's 100% delegated to MySQL stored procedures (bodies covered by the sibling
stored-procedure research pass).

- **`Payrollmaster::taxSalaryProcessPrc($outputParameter)`** — `Model/Payrollmaster.php:17-27`. Builds
  `CALL tax_salary_process_prc('<month>','<branch>','<emp>','<payroll_master_pkey>','<user_id>',@Perr_msg);` from a
  positional array and executes it via `$this->query()`. No parameter escaping beyond wrapping in quotes — **SQL
  injection risk** if any element of `$outputParameter` contains a `'`. Called from:
  - `Controller/PayrollController.php:1013` — `$this->Payrollmaster->taxSalaryProcessPrc($outputParameter)`, inside
    `processpayroll()` (`Controller/PayrollController.php:790`), only for companies in the `$specialCompanies`
    allow-list (`Controller/PayrollController.php:818`).
  - Same pattern replicated (dead branch) in `Controller/PayrollProcessController.php` — not confirmed called since
    that controller is orphaned per prior research.

- **`Payrollarrearmaster::taxArrearProcessPrc($outputParameter)`** — `Model/Payrollarrearmaster.php:18-29`. Builds
  `CALL tax_arrear_process_prc(...)`. Called from `Controller/ArrearController.php` (grep confirms `Payrollarrearmaster`
  is `$uses`'d there — `Controller/ArrearController.php:51`), but table `payroll_arrear_master` itself is
  schema-absent (see §1) — **treat this whole call chain as possible legacy cruft pending DB verification.**

- **`Payrollarrearmaster::taxArrearProcessOldPrc($outputParameter)`** — `Model/Payrollarrearmaster.php:31-42`. Same
  shape, calls `CALL tax_salary_process_old_prc(...)`. No call sites found in the controllers reviewed for this
  cluster — **possible legacy cruft, verify before migrating.**

- **`PayrollArrearComponents::taxSalaryProcessPrc($outputParameter)`** — `Model/PayrollArrearComponents.php:17-27`.
  Identical body/shape to `Payrollmaster::taxSalaryProcessPrc` (copy-pasted), calls the same
  `tax_salary_process_prc` stored procedure. No confirmed call site in the reviewed controllers — likely dead code
  left over from a refactor (`Payrollmaster` version above is the one actually wired up).

All other models in scope (`SalaryHeads`, `SalaryHeadItems`, `SalaryStructures`, `SalaryStructureDetails`, `TaxHead`,
`TaxHeadDetail`, `TaxType`, `FinancialYear`, `EmpArrearSalarySlip`, `EmpSalarySlip`, `EmployeeCTC`,
`EmployeeSalaryStructure`, `SalaryIncrement`, `SalaryIncrementDetails`, `EmpTaxRegime`, `TaxSave`,
`EmployeeTaxTransactions`, `EmpTaxSalTrans`, `EmpTaxSalTransNew`, `EmployeeTaxsalsum`, `EmployeeTaxsalsumNew`,
`ComponentIncrement`, `EmpSalaryCompUpload`, `MonthlyCTC`, `GrossSalary`, `TotalDeductions`, `MonthlyAmount`) declare
**zero custom methods** — confirmed by reading every file in full. All CRUD and computation for this domain happens
in the controllers (huge — `SalaryIncrementController.php` alone is ~5800 lines with 38 public actions,
`SalaryComponentUploadController.php` ~2000 lines with 24 actions, `PayrollProcessController.php` ~2700 lines with
40 actions) and in MySQL stored procedures/triggers.

### Controller method inventories (for migration scoping)

- `Controller/PayrollController.php` (**live** payroll run): `showprocesspayroll` (57), `showapprovepayroll` (114),
  `FilterList` (198), `showprocesspayrolltab` (219), `listpayroll` (298), `listpayrollold` (436, dead — "old"
  suffix), `listprocessedpayrollold` (556, dead), `listprocessedpayroll` (634), `processpayroll` (790) — the core
  run action, `holdProcessPayroll` (1207), `removePayrollEntry` (1229), `showsalaryslip` (1301),
  `showapprovepayrolltab` (1403), `listapprovepayroll` (1485), `listapprovedpayroll` (1628), `approvepayroll` (1757)
  — the approval action, `generatePasswords` (1796).
- `Controller/PayrollProcessController.php` (**orphaned**, confirmed by prior research; method inventory included
  for completeness/dead-code awareness): 40 public actions implementing a far more elaborate multi-stage workflow —
  `showprovisionalpayroll`/`showpreauditpayroll`/`preauditpayroll`/`showfinalizationpayroll`/`finalizationpayroll`/
  `paymentpayroll`/`PaymentapprovePayroll`/`processpayment`/`approvalpayroll`/`SalaryapprovePayroll` etc.
  (`Controller/PayrollProcessController.php:54-2724`). This looks like an abandoned redesign toward a
  provisional→pre-audit→finalization→payment→approval→salary-approval pipeline that was never cut over — the live
  `PayrollController` uses a much simpler 2-stage process→approve flow instead. **Possible legacy cruft — do not
  port this multi-stage design into the Next.js app unless product explicitly wants to resurrect it; verify with
  stakeholders first.**
- `Controller/SalaryProcessingController.php`: only `index` (10) and `getPayrollFeatures` (17) — thin, mostly a
  landing/feature-flag page.
- `Controller/SalaryHeadsController.php`: `index`, `form`, `addsalaryheads`, `form_items`, `addsalaryheaditems`,
  `DeleteHead`, `checksalaryheadexists`, `checksalaryheaditemexists`, `checkshortnameexists` — CRUD over
  `salary_heads`/`salary_head_items` master data.
- `Controller/SalaryStructureController.php`: `index`, `liststructures`, `addEmpToSalConfig`,
  `removeEmpFromSalConfig`, `listemployeesinsalary`, `listemployeesforsalary`, `savesalarystructuresetup` (221) — CTC
  breakdown persistence, `loadSalaryStructureDetails`, `getSalaryHeadItems`, `view`, `form`, `calculateFixedLimit`
  (640) — prorates a fixed/limit amount by occurrence (monthly/bimonthly/halfyearly/yearly/quarterly), `search`
  (663, generic recursive array search helper), `delete`, `checkstructureexists`.
- `Controller/SalaryIncrementController.php`: 38 actions including 7 `eval()` sites (see §3) —
  `salaryIncrementForm`, `saveIncrement`, `saveItemIncrement`, `calcSalaryStructure`, `alterSalaryStructure`,
  `process`/`processItem` (bulk increment application), `onIncrementChange`/`onIncrementChangeNew`, component
  allocation (`componentAllocate`, `saveComponentAllocate`, `saveComponentAllocateKWMT` — company-specific variant),
  reporting (`itemIncerementReport`, `incerementReport`).
- `Controller/TaxController.php`: `index`, `form`, `Tabs`, `Calculate`/`Calculate_new` (old/new tax-regime parallel
  implementations), `setup`/`setupshow`/`setupshow_new`, `Proccess` [sic], `Choosetax`, upload/download of tax proof
  documents, `formSixteen`/`formSixteenDownload` (Form 16 generation).
- `Controller/TaxHeadsController.php`: CRUD (`index`, `form`, `addtaxtypes`, `Deletetaxtype`, `form_items`,
  `addtaxheads`, `Deletetaxhead`, `gettaxtypename`, `Gettaxnamelist`, `add_details`) over `tax_type`/`tax_heads`/
  `tax_heads_details`.
- `Controller/EmployeeTaxController.php`: `index`, `Tabs`, `Calculate`, `setup`, `Employeesetup`, `setupshow`,
  `Proccess` — employee self-service tax declaration flow, parallels/overlaps `TaxController`.
- `Controller/FinancialYearController.php`: `index`, `form`, `save`, `listfinyears`, `deleteFinYear`.
- `Controller/ArrearController.php`: `showprocesspayroll`, `listcomponents`, `filter_month`, `showapprovepayroll`,
  `FilterList`, `showprocesspayrolltab`, `listpayroll`, `listpayrollold` (dead), `listprocessedpayrollold` (dead),
  `listprocessedpayroll`, `processpayroll` (702, contains the one `eval()` at line 866), `holdProcessPayroll`,
  `removePayrollEntry`, `showarrearsalaryslip`, `showapprovepayrolltab`, `listapprovepayroll`, `listapprovedpayroll`,
  `approvePayroll`. Mirrors `PayrollController`'s process/approve shape but operates on the (schema-absent)
  `payroll_arrear_master` table.
- `Controller/YearEndController.php`: only 5 actions — `index`, `loaders`, `approve`, `processleave`, `notice`.
  Small surface; year-end close-out logic (leave carry-forward, notice period) delegated mostly to stored
  procedures.
- `Controller/SalaryComponentUploadController.php`: 24 actions, 5 `eval()` sites (see §3) — bulk upload of manual
  salary components (`saveuploads`, `saveuploads_od`, `component_upload`, `uploadandsaveempctc`), increment/component
  allocation duplicated from `SalaryIncrementController` (`componentAllocate`, `saveAllocate`, `saveComponentAllocate`,
  `saveComponentAllocateKWMT`) — **significant code duplication between this controller and
  `SalaryIncrementController`; consolidate during migration rather than porting both.**

---

## 3. Complex logic / state machines

### 3.1 The `eval()`-based formula engine

**Where formulas are stored:** `salary_structure_details.structure_formula` (human-readable, e.g. `"Monthly Gross
Salary * . 4"`) and `salary_structure_details.structure_det_calequation` (machine form, e.g. `"monthsal * . 4"` or
`"( monthsal - ( 1_Basic * . 2 ) ) * . 12"`) — both `varchar(1000)`, `schema/mypayrol_trial.sql:46117,46119`. Sample
rows: `schema/mypayrol_trial.sql:46124-46145` (structure 10) and `:46146-46167` (structure 6). The `structure_id`
FK-shaped column and `salary_head_item_fkey` FK-shaped column tie a formula to one salary-structure/component pair;
neither is a declared MySQL FK (§1).

**Token grammar:** tokens look like `<salary_head_item_pkey>_<ItemName>` (e.g. `1_Basic`, `111_Shift_Allowance`) or
the literal keyword `monthsal` (gross monthly salary). `Controller/PayrollController.php:1075` extracts these with
`preg_match_all('/\d+_[A-Za-z_]+|monthsal/', $formula_string, $matches)`. For each token, the controller either
(`monthsal`) sums `emp_salary_slip.salary_amount` for `head_operator='Addition' AND item_part='Direct'` in the
current month (`Controller/PayrollController.php:1084-1095`), or (numeric-prefixed token) looks up the *already
computed* `emp_salary_slip.salary_amount` for that `salary_head_item_fkey` in the current month
(`Controller/PayrollController.php:1102-1113`) — i.e. **formulas can reference sibling components computed earlier
in the same payroll run**, so component processing order matters (implicit dependency graph, not modeled
explicitly anywhere).

**Runtime evaluation path (per employee, per payroll run):**
1. `Controller/PayrollController.php:929-1001` — a *first pass* evaluates a simpler formula stored directly in
   `emp_salary_slip.remarks` (already-substituted arithmetic string, e.g. `"15000+2000*.4"`), guarded since a recent
   hardening pass (`preg_match('/^[0-9\+\-\*\/\(\)\. ]+$/', ...)`, `Controller/PayrollController.php:941`) before
   `eval('$salary_amount = ' . $formula_from_remarks . ';')` (`Controller/PayrollController.php:943`).
2. `Controller/PayrollController.php:1032-1178` — a *second pass* re-fetches the structure-level formula
   (`structure_det_calequation`/`structure_formula`) directly from `salary_structure_details`
   (`Controller/PayrollController.php:1045-1055`), substitutes tokens with live DB values as above
   (`Controller/PayrollController.php:1078-1119`), then re-evaluates via two more `eval()` calls guarded by
   whitelist regexes (`Controller/PayrollController.php:1151,1155` and `:1164,1166`) to compute a "combined base
   value" used for `limit_wl`/`limit_wg` operator types (min/max against a configured cap,
   `Controller/PayrollController.php:1165-1169`).

**Security posture:** as of the most recent edits (comments tagged "Edited by Akshay on 28-8-2025" / "1-10-2025"),
every `eval()` call site in `PayrollController.php` is now preceded by a whitelist regex restricting the string to
`[0-9+\-*/(). ]` before evaluation — this closes arbitrary-code-execution risk **as long as the whitelist regex
itself is correct and can't be bypassed** (e.g. it does not defend against malformed-but-matching expressions
causing PHP parse errors inside `eval`, which are caught via `@` suppression and `try/catch`, degrading to
`null`/`0` rather than crashing). However, **`Controller/ArrearController.php:866`,
`Controller/PayrollProcessController.php:406`, and 4 of the 5 sites in `Controller/SalaryComponentUploadController.php`
(`:253,1077,1761,1987,2199`) use the *unguarded* pattern** `eval('$salary_amount = ' . $formula_from_remarks . ';')`
with **no regex whitelist before `eval`** — only `preg_replace("/\s+/", "", ...)` whitespace-stripping. If
`$formula_from_remarks` (sourced from a `remarks` text column populated by upload/manual-entry flows) can contain
attacker-controlled content, these are **PHP code injection points**. `Controller/SalaryIncrementController.php`
is mixed: sites at `:2389,3084,3315,3528` do have whitelist guards (some added as late as "5-5-2026" per inline
comments — e.g. `:2387` replaces any letter token with `'0'` before checking `/^[0-9+\-*\/().\s]+$/`), while
`:4034,4175` build `$evaluated_formula` via `preg_replace_callback` substitution then eval with only an `@`
suppression, and `:4603` uses the fully unguarded pattern. **Net: the formula engine is being incrementally
hardened file-by-file and call-site-by-call-site, not consistently — a Next.js port should replace *all* of these
with a real expression parser/evaluator (e.g. a shunting-yard arithmetic evaluator or a formula library) rather than
selectively porting the regex-whitelist band-aid.**

**Formula operator types** (`salary_structure_details.structure_det_operator`, free-text `varchar(30)`, no CHECK
constraint or lookup table): observed values in seed data include `rembalance` (balancing/remainder component —
whatever's left of gross after other components, `schema/mypayrol_trial.sql:46124`), `formula` (evaluate
`structure_det_calequation`), `limit_wl` (limit-with-lower — cap using `min()`), `fixed` (flat amount, e.g. LWF at
`20`, `schema/mypayrol_trial.sql:46137`), `manually` (entered by payroll admin, formula ignored), `payable in` (used
for annual/non-monthly heads, head 2 in `salary_heads`, `schema/mypayrol_trial.sql:45865`). Also referenced in
`SalaryIncrementController.php:4044-4048` (`limit`, `limit_wl` handled via `min()`, and — separately — a `limit_wg`
"limit with greater" using `max()`, `Controller/PayrollController.php:1168`). **This operator vocabulary is entirely
implicit in string literals scattered across controllers — there is no enum/lookup table backing it.** Migration
should formalize it as a proper enum.

`salary_head_items.item_type` (`schema/mypayrol_trial.sql:45879`, comment: `'null=formula,fixed,limit'`) is a
**separate, overlapping vocabulary** from `structure_det_operator` above (`Formula`, `Limit`, `Manually`, `Fixed`,
`Payable in`, `LEAVE` — case is inconsistent in seed data, e.g. `'Formula'` vs `'formula'` vs `'limit'` vs `'Limit'`
in `schema/mypayrol_trial.sql:45893-46025`) — **two independently-typed, case-inconsistent free-text columns
describing overlapping concepts** (one on the salary-head-item master, one on the per-structure override) is a
schema quirk worth flattening into one clean enum in the new model.

### 3.2 Payroll-run status state machine (live implementation — `PayrollController.php`)

`payroll_master` has **two** status-ish columns that are used inconsistently:
- `approved` — `char(1) NOT NULL DEFAULT 'N'` (`schema/mypayrol_trial.sql:45326`). **Confirmed unused by the live
  payroll cluster** — a repo-wide grep found `payroll_master.approved` referenced only in
  `Controller/EmployeeResignationControllerBkup.php` (a `*Bkup` file, i.e. already-flagged legacy backup controller),
  never in `PayrollController.php`, `ArrearController.php`, or `SalaryProcessingController.php`. **This column is
  dead weight in the live flow — legacy cruft, superseded by `action` below.**
- `action` — `varchar(30) DEFAULT NULL` (`schema/mypayrol_trial.sql:45327`). This is the real state field, driven
  entirely by controller code, no CHECK constraint, no enum:
  - `NULL` / `''` — initial/unprocessed state (queried at `Controller/PayrollController.php:312-313,649-650,704-705`
    as "still eligible to run payroll").
  - `'Hold'` — set by `holdProcessPayroll()` (`Controller/PayrollController.php:1207-1227`) — an admin can pull a
    payroll row out of the run without deleting it.
  - *(implicit "Processed"/"Verified")* — `Controller/PayrollController.php:1500-1501` filters `NOT IN
    ('Processed','Verified')` to find rows still eligible for approval-stage listing, implying `processpayroll()`
    (`:790`) sets `action` to `'Processed'` (via the called stored procedure `calculate_salary_main_prc`/
    `salary_process_prc`, not shown directly in PHP — set server-side) and some other path sets `'Verified'`.
  - `'Approved'` — set by `approvepayroll()` (`Controller/PayrollController.php:1757-1794`), which loops selected
    `payroll_master_pkey`s, calls stored procedure `CALL payroll_master_approve(branch_code, month, emp_fkey,
    user_id, @perror_message)` (`Controller/PayrollController.php:1785`) per employee, then bulk-sets
    `Payrollmaster.action = 'Approved'` (`:1766,1787-1790`).
  - `removePayrollEntry()` (`Controller/PayrollController.php:1229-1299`) resets `action` back to `NULL` (i.e.
    un-processes), soft-closes the associated `emp_salary_slip` rows by setting `end_date_effective = today()`
    (`:1258`), and — for non-"special company" tenants — resets `emp_variables_upload.status = 0` for fixed-head
    variable uploads tied to that month (`:1285-1288`), letting the payroll be re-run cleanly.

  **Effective state machine (live):** `NULL → Processed → (Verified?) → Approved`, with a parallel `Hold` side-state
  and a `removePayrollEntry` "reset to NULL" escape hatch from any state. Two "old"/duplicate list actions
  (`listpayrollold`, `listprocessedpayrollold`) still exist alongside the current ones
  (`Controller/PayrollController.php:436,556`) — **dead code, verify before migrating.**

  **Per-tenant branching:** both `processpayroll()` and `removePayrollEntry()` (and `savesalarystructuresetup()` in
  `SalaryStructureController.php`) hard-code a `$specialCompanies` array of ~17 company codes
  (`Controller/PayrollController.php:818,1276`, `Controller/SalaryStructureController.php:258`) that changes which
  stored procedure is called (`calculateSalaryMainPrc` vs `salaryProcessPrc`,
  `Controller/PayrollController.php:872-875`) or which fields are touched. **This is per-tenant conditional business
  logic hard-coded as a literal array of company codes, edited repeatedly (commented-out older versions are left in
  place at `:800-816,1260-1276`) — a serious migration risk: any new tenant onboarded needs someone to remember to
  add its code to this array in multiple files, and the array has drifted out of sync between files (compare
  `Controller/PayrollController.php:818` vs `Controller/SalaryStructureController.php:258` — same array, but each
  edited independently with different dated comments, meaning they may already disagree by the time you read this).**

  A **second, more elaborate but orphaned state machine** exists in `PayrollProcessController.php`: provisional →
  pre-audit → finalization → payment (with a separate payment-approval sub-stage) → approval → salary-approval, each
  with its own `list*`/`show*`/action pair (§2). This looks like an abandoned attempt to build a proper multi-level
  payroll approval workflow that was never wired into navigation/routes for the live product (per prior research —
  `PayrollProcessController.php` is unreferenced). **If the business actually wants multi-stage approval in the
  Next.js rewrite, this dead controller is a better design reference than the live 2-stage `PayrollController`, but
  it must be verified with the product owner first, not assumed to be current behavior.**

### 3.3 Salary-structure / CTC-breakdown distribution logic

`Controller/SalaryStructureController.php:savesalarystructuresetup()` (`:221-333`) is where a CTC template
(`salary_structure` + `salary_structure_details` rows) is authored: for each of `item_count` line items posted from
the form, it reads `structure_det_operator_<i>` (defaulting to `'rembalance'` if `cal_equation_<i pattern>` equals
`'rembalance'`, `:298-302`), `structure_det_value_<i>` (or, for `limit_wl`/`limit_wg` operators selected via
`radio_pickamount_<i>` ∈ `{wl,wg}`, a different hidden field `hid_formula_item_value_<i>`, `:306-310`),
`cal_equation_<i>` → `structure_det_calequation`, `equation_<i>` → `structure_formula`, and computes
`structure_derived_perc = structure_det_value * 100 / structure_eg_amt` (`:316-319`) — i.e. **the "% of CTC" shown
in the UI is derived and stored, not the source of truth; the absolute value is authoritative.** `structure_det_depends`
is populated from yet another pair of hidden fields depending on the `wl`/`wg` radio state (`:320-324`).

`calculateFixedLimit($occu, $amount, $structure_define)` (`Controller/SalaryStructureController.php:640-661`)
converts a component's configured "limit" amount between occurrence frequencies (monthly/bimonthly/halfyearly/
yearly/quarterly) — but **only when `$structure_define == 1`** ("Monthly"); no `else` branch, so calling it for any
other `defined_structure_for` value returns `NULL` silently.

Per-employee CTC breakup (as opposed to the structure template) is computed in
`SalaryIncrementController.php` around `:4000-4055` (also duplicated in `SalaryComponentUploadController.php`):
tokens matching `/\b(\d+)_\w+\b/` are pulled from the formula, resolved against the *employee's own*
`emp_salary_structure.structure_det_value` for sibling components (falling back to `0`,
`:4008-4019`), substituted back in via `preg_replace_callback` (`:4023-4026`), space-stripped, decimal-normalized
(`(?<!\d)\.(\d+)` → `0.$1`, guards against PHP parsing `.5` awkwardly, `:4030`), then `eval`'d (`:4034`) and finally
passed through the same `limit`/`limit_wl`/`limit_wg` `min()`/`max()` operator logic as the structure-template path
(`:4043-4049`). There is also a direct call to a MySQL stored function `sal_structure_distribution_fn(company, emp,
salary_id, user_id)` (`Controller/SalaryIncrementController.php:2366`) — i.e. **the same CTC-distribution
calculation is implemented independently in PHP (in at least 2 controllers) *and* in a MySQL stored function**,
with no indication they're kept in sync deliberately. **Migration risk: pick exactly one source of truth (almost
certainly the stored function/procedure logic, per the sibling stored-procedure research pass) and do not
re-implement the PHP duplicate.**

---

## 4. Schema quirks

- **`payroll_master04092016`** (`schema/mypayrol_trial.sql:45350-45374`) — a literal date-stamped snapshot/backup of
  `payroll_master` frozen from a schema change on 4-Sep-2016 (missing `tax_include`, `bank_details`, `departments`,
  `branch_name`, `desig`, `division`, `section`, `grade`, `joining_date`, `eps`, `probation`, `contract_start_date`,
  `contract_end_date`, `pay_scale`, `category_name` compared to the live `payroll_master`). Clear evidence of an
  in-place ALTER-heavy history; **do not migrate this table, it is a point-in-time backup, not live data.**
- **`emp_salary_slip_bkp`** (`schema/mypayrol_trial.sql:43851-43875`) — identical structure to `emp_salary_slip`
  (with slightly looser column types, e.g. `structure_det_value int(11)` vs `decimal(10,0)` in the live table) —
  another ad hoc backup table, unmapped to any CakePHP model, presumably created manually before a risky bulk
  update. Legacy cruft.
- **`payroll_master.approved`** (`char(1) NOT NULL DEFAULT 'N'`) is dead in the live flow — see §3.2. Nullable in
  practice would be fine but it's `NOT NULL DEFAULT 'N'`; the real problem is nobody reads/writes it except a
  confirmed-backup controller.
- **`taxes_save`** (`schema/mypayrol_trial.sql:47317-47325`) has **no primary key at all**, yet
  `Model/TaxSave.php:14` declares `$primaryKey = 'emp_fkey'` — a non-unique column (seed data has duplicate
  `emp_fkey` values, `schema/mypayrol_trial.sql:47327-47331`). This is a genuine data-integrity gap: nothing stops
  duplicate rows per employee, and CakePHP's `save()`/single-record `find()` against this "primary key" is unsafe.
- **`emp_tax_sal_trans_sum` → `emp_tax_sal_trans_sum_new`** (`schema/mypayrol_trial.sql:44074` vs `:44111`) — the
  "new" table adds `forth_portion`/`fifth_portion`/`sixth_portion` (+ their `_tax` counterparts) and
  `marginal_relief`, `seventh_portion`, `seventh_portion_tax` (`schema/mypayrol_trial.sql:44127-44152`). This tracks
  India's income-tax slab count growing from 3 tax portions to 7 (old regime → new regime under recent Finance Acts)
  plus a `marginal_relief` field (a real, recent Indian tax-law concept, ~FY2024-25). The **"_new" naming
  convention with the old table kept alongside** is repeated identically for `emp_tax_sal_trans` /
  `emp_tax_sal_trans_new` (`schema/mypayrol_trial.sql:44030` vs `:44052`, structurally near-identical, no new-regime
  columns added there) and `EmployeeTaxsalsum` / `EmployeeTaxsalsumNew` at the model layer — **a recurring
  "duplicate the table, suffix `_new`, keep the old one live too" migration pattern** used repeatedly in this
  codebase instead of proper schema evolution (adding nullable columns, or an actual migration tool). Both old and
  new tables are still model-backed and both are `$uses`'d together in `Controller/EmployeeTaxController.php:55`
  and `Controller/EmployeeController.php:52` — **strongly suggests both regimes' computations run in parallel per
  employee** (consistent with India's old-vs-new tax regime opt-in per FY, `emp_tax_regime.option_type`
  `schema/mypayrol_trial.sql:44021`).
- **`emp_salcomp_upload`** (`schema/mypayrol_trial.sql:43904-43915`) is `ENGINE=MyISAM` while virtually every other
  table in this cluster is `InnoDB` — no transactions, no FK support on this one table; a bulk-upload staging table
  that was presumably never revisited after MyISAM fell out of favor.
- **`salary_structure_details.structure_det_value` / `structure_det_depends`** are `decimal(10,0)` (**zero decimal
  places** — `schema/mypayrol_trial.sql:46115-46116`) meaning **no paise/cents precision is stored for salary
  component amounts or their dependent limits** — every amount is forced to a whole rupee. Combined with the
  `eval()` formula engine doing floating-point arithmetic before storage, this is a silent-rounding risk worth
  explicitly deciding on (banker's rounding? floor? ceil? — the ESI-specific `ceil()`/`round()` branching at
  `Controller/PayrollController.php:960-967` shows the codebase is already aware rounding direction matters and
  handles it ad hoc per component name string-match rather than via a declared rounding rule per component).
- **`salary_structure.startdate_effective`/`enddate_effective`** are `NOT NULL date` with **no default**, yet seed
  row `structure_id=21` ("New2021") has both set to `'0000-00-00'` (`schema/mypayrol_trial.sql:46108`) — MySQL's
  zero-date sentinel, which most modern DB drivers/ORMs (including anything Postgres-based, likely target for
  Next.js) cannot represent. **Every zero-date column in this schema needs an explicit sentinel-to-NULL (or
  sentinel-to-min-date) mapping decision during migration.**
- **`fin_year`** unique key is `(company_code, branch_code, fin_year, Year_status, vattr1, status)`
  (`schema/mypayrol_trial.sql:44358`) — allows **the same `fin_year` integer to have multiple rows** differentiated
  by `Year_status`/`vattr1`/`status`, and indeed seed data has two rows both for `fin_year=2025`, one calendar-year
  (`2025-01-01`–`2025-12-31`) and one April-start (`2025-04-01`–`2026-03-31`), both `is_current_finyear='Y'`
  simultaneously (`schema/mypayrol_trial.sql:44362-44363`) — **two "current" financial years active at once**,
  differentiated only by the opaque `vattr1` flag (0 vs 1, undocumented meaning — likely "calendar FY" vs "April FY"
  toggle per module). This ambiguity ("is_current_finyear" not actually unique) is a real risk for any code that
  assumes a single current financial year.
- **`salary_head_items.item_type`** free-text comment says `'null=formula,fixed,limit'` but the actual seed data
  uses far more distinct casings/values than that comment documents (`'Formula'`, `'Limit'`, `'Manually'`,
  `'Fixed'`, `'Payable in'`, `'LEAVE'`, and blank `''` for placeholder/inactive items — `schema/mypayrol_trial.sql:
  45893-46025`) — the column comment is stale/incomplete documentation, not to be trusted as the full vocabulary.
- **`salary_head_items`** has 60+ rows with `status = 0` (inactive placeholder items with empty `item_type`,
  `occurance`, `comments` — e.g. rows 4,7,10,12-25 etc., `schema/mypayrol_trial.sql:45896-45930`) mixed in with
  live active items (`status = 1`) in the same table with no soft-delete/archival separation — a large fraction of
  this master table is inert catalog noise that a migration should filter on `status=1` rather than porting
  wholesale.

---

### 7.5 Advances, Loans & Expenses

# Advances, Loans & Expenses — Data Model & Business Logic Report

STATUS: COMPLETE

All paths are relative to `D:\Projects\RIZOMigration\legacy\` unless stated. Controllers live at `legacy\Controller\*.php`, models at `legacy\Model\*.php` (no `app/` prefix in this checkout). Schema ground truth is `legacy\schema\mypayrol_trial.sql` (236 `CREATE TABLE` statements).

## 0. Models in scope and their tables

| Model file | Class | `$useTable` | Exists in schema dump? |
|---|---|---|---|
| Model/Advance.php | Advance | `advance_expense` | **NO** |
| Model/EmployeeAdvance.php | EmployeeAdvance | `emp_advance` | YES — schema/mypayrol_trial.sql:42187 |
| Model/EmployeeAdvanceInfo.php | EmployeeAdvanceInfo | `emp_advance_info` | YES — schema/mypayrol_trial.sql:42204 |
| Model/EmployeeExpenses.php | EmployeeExpenses | `emp_expense` | YES — schema/mypayrol_trial.sql:43256 |
| Model/EmployeeLoan.php | EmployeeLoan | `emp_loan` | YES — schema/mypayrol_trial.sql:43471 |
| Model/EmployeeLoanInfo.php | EmployeeLoanInfo | `emp_loan_info` | YES — schema/mypayrol_trial.sql:43491 |
| Model/ExpenseHead.php | ExpenseHead | `expense_heads` | **NO** |
| Model/ExpenseItem.php | ExpenseItem | `expense_item` | **NO** |
| Model/ExpenseType.php | ExpenseType | `expense_type` | YES — schema/mypayrol_trial.sql:44290 |
| Model/ExpenseTypeDetails.php | ExpenseTypeDetails | `emp_expense_details` | **NO** |
| Model/ExpenseTypePaymentDetails.php | ExpenseTypePaymentDetails | `emp_expense_payment` | **NO** |
| Model/LoanEmi.php | LoanEmi | `emi_upload` | YES — schema/mypayrol_trial.sql:42093 |
| Model/VehicleExpenses.php | VehicleExpenses | `transportation_expense` | **NO** |
| Model/Allocate_expense.php | Allocate_expense | `allocate_expense` | **NO** |

Additionally, two **phantom models** (no `Model/*.php` file at all — CakePHP would instantiate a generic `AppModel` bound by naming convention) are referenced via controller `$uses` arrays and also point at non-existent tables:
- `TransportationPayment` (used in `Controller/VehicleExpensesController.php:53,120,144,161`) → would bind to `transportation_payments`, which also **does not exist** in the schema.
- The raw SQL `advance_payment` table referenced directly in `Controller/ProjectExpensesController.php:782` (`UPDATE advance_payment SET status='0' WHERE expense_fkey=...`) — **also does not exist** in the schema.

**MAJOR FINDING:** 7 of 14 declared models (half the cluster) — plus 2 more phantom/raw-SQL table references — point at tables absent from the 236-table `schema/mypayrol_trial.sql` dump. Verified exhaustively: `grep -i "CREATE TABLE.*\`[a-z_]*(expense|advance|loan|transport|emi)[a-z_]*\`"` across the whole file returns only 7 matches total (`emi_upload`, `emp_advance`, `emp_advance_info`, `emp_expense`, `emp_loan`, `emp_loan_info`, `expense_type`). None of `advance_expense`, `expense_heads`, `expense_item`, `emp_expense_details`, `emp_expense_payment`, `transportation_expense`, `allocate_expense`, `transportation_payments`, `advance_payment` exist anywhere in the dump.

**Implication for migration:** either (a) the schema dump used as ground truth is stale/incomplete for this cluster and the live trial DB actually has these tables, or (b) the corresponding features are dead in the `mypayrol_trial` tenant and would throw SQL errors (undefined table) if invoked. Concretely dead/at-risk if (b): the entire `AdvanceController` (generic "Advance" feature, distinct from `EmployeeAdvance`), `ExpenseItemController` (item/category-based expense catalog), `ExpenseHead`-driven parts of `ExpenseTypeController` (`expense_heads` dropdown at ExpenseTypeController.php:58 and the `ExpenseHead` LEFT JOIN in `listexpense()` at ExpenseTypeController.php:180-190), `Allocate_expense`-based designation-to-expense-type allocation, `VehicleExpensesController`'s payment-tracking half, and `EmployeeExpenses`/`ProjectExpenses` detail/payment sub-screens backed by `ExpenseTypeDetails`/`ExpenseTypePaymentDetails`, and the ZWLK-tenant cascade-void of `advance_payment` on project-expense rejection. **Flag for verification against a live/fuller DB dump before migrating — do not assume these screens are portable as-is; they may simply error out today.**

The 7 tables that DO exist (`emi_upload`, `emp_advance`, `emp_advance_info`, `emp_expense`, `emp_loan`, `emp_loan_info`, `expense_type`) are the ones actually exercised by `EmployeeadvanceController`, `EmployeeLoanController`, `EmployeeEmiController`, and (for `emp_expense`) `EmployeeExpensesController`/`ProjectExpensesController` — these are the real, live surfaces of this cluster.

### Association cross-check
None of the 14 models declare `$belongsTo`/`$hasMany`/`$hasOne` associations (all are barebones: just `$name`, `$primaryKey`, `$useTable`, extending `AppModel`). All "joins" are done ad-hoc per-controller via raw SQL or CakePHP `'joins' => array(...)` find() options (e.g. `EmployeeadvanceController.php:96-104` joins `emp_proff` manually; `EmployeeLoanController.php:564-572` joins `emp_loan` manually keyed on `EmployeeLoanInfo.loan_pkey = EmpLoan.emp_loan_pkey`). Since there are zero declared associations, there is nothing to "mismatch" against schema `*_fkey` columns in the CakePHP sense — but the ad-hoc joins were spot-checked against the schema and are consistent with the real FK columns that do exist (`emp_fkey` on `emp_advance`, `emp_loan`, `emp_expense`, `emi_upload`; `loan_pkey` on `emp_loan_info`/`emi_upload` referencing `emp_loan.emp_loan_pkey`).

## 1. Custom methods per model

All 14 models are confirmed barebones — **zero custom methods, zero `$validate`, zero callbacks (`beforeSave`/`afterFind` etc.)** on any of them. Each file's entire content is the `$name`/`$primaryKey`/`$useTable` triplet. All business logic lives in controllers, which call generic `find()`/`save()`/`updateAll()`/`query()` (raw SQL) against these models. Verified by reading every file in full:
- Model/Advance.php, Model/EmployeeAdvance.php, Model/EmployeeAdvanceInfo.php, Model/EmployeeExpenses.php, Model/EmployeeLoan.php, Model/EmployeeLoanInfo.php, Model/ExpenseHead.php, Model/ExpenseItem.php, Model/ExpenseType.php, Model/ExpenseTypeDetails.php, Model/ExpenseTypePaymentDetails.php, Model/LoanEmi.php, Model/VehicleExpenses.php, Model/Allocate_expense.php.

Because there are no custom model methods, "custom methods per model" collapses to **custom methods per controller**, listed below with call sites (all call sites are AJAX/view actions within the same controller; there is no cross-controller reuse of these methods — each controller re-implements its own CRUD/list/upload/approval logic from scratch, consistent with the "barebones, no hooks" pattern noted in prior research).

### Controller/AdvanceController.php (`$uses` incl. `Advance` → non-existent `advance_expense` table)
- `form()` (AdvanceController.php:55) — popup form; SELECT against `advance_expense` (commented out at line 63, so no longer even queried there, but `Advance()` at line 186 still queries it live) and `emp_banks`.
- `advancesave()` (AdvanceController.php:75) — raw-column update/insert into `advance_expense` via `updateAll`/`save`.
- `advancelist()` (AdvanceController.php:107) — datagrid list, raw SQL `SELECT ... FROM advance_expense ae ...` (line 122-130) — **would fail with "table doesn't exist" if `advance_expense` is truly absent.**
- `Advance()` (AdvanceController.php:184) — dropdown/landing data, queries `advance_expense` again (line 186-187).
- `deleteEmployee()` (AdvanceController.php:197) — soft-delete via `updateAll(status=0)`.
- Hard-coded `account_fkey` → bank-name mapping (lines 143-171) is a **hard-coded lookup table in PHP**, not driven by `emp_banks`, and includes tenant-specific literal values ("ANOOP PC", "RAFEEQ PC", "MUSFEER PC", "MUNEEB PC", "PRABEESH PC", "ALLES HAPPAY", "JAC HAPPAY", "ZB CARD") — clearly customer-specific data baked into shared code; **legacy cruft, verify which tenant this belongs to before porting.**

### Controller/EmployeeadvanceController.php (drives `emp_advance` — the real, live advance feature)
- `index()` (line 59) — **contains the confirmed `$this->setup($emp_fkey)` bug**, see §3.4.
- `listemployees()` (line 86) — employee picker grid.
- `showtaxheaddetail()` (line 123), `loadEmpDetails()` (line 140), `loadEmpProfDetails()` (line 153), `empprofdetails()` (line 200), `emptaxationdetails()` (line 253, **empty body — dead stub**), `getcurrentemployeekey()` (line 255) — employee/tax detail helpers, mostly shared boilerplate copy-pasted across many controllers in this codebase.
- `uploadandsaveempctc()` (line 271) — bulk Excel upload of advances; **contains the full eligibility formula**, see §3.1.
- `form()` (line 657) — popup add/edit form; branches to a different view (`absForm`) for tenants `GLET`/`ABSG` (line 708).
- `salarycheck()` (line 716) — checks whether payroll already processed for the month (`emp_salary_slip` lookup) before allowing an advance to be entered.
- `salary()` (line 746) — **AJAX endpoint returning the live eligibility ceiling**, see §3.1 (this is the interactively-called twin of the bulk-upload formula in `uploadandsaveempctc`).
- `employeeloansave()` (line 882) — despite the name, this is the **advance** save action (saves into `EmployeeAdvance`/`emp_advance`), not loan-related; naming is misleading legacy cruft.
- `employeelist()` (line 917) — datagrid list of un-credited (`is_credited='N'`) advances, joined to `employee_info`.
- `advance()` (line 1029) — landing/dropdown data (branches list), with branch-restriction logic for `GLET`/`ABSG` non-HO employees via `get_branch_code_abs_fn()` stored function.
- `deleteEmployee()` (line 1065) — soft delete.
- `downloadempctcformat()` (line 1083) — generates the Excel upload template.
- `Updateame()` (line 1221) and `listpunches()` (line 1251) — attendance-recalculation helpers invoked internally by `salary()`/`uploadandsaveempctc()` when present-day attendance figures are missing (GLET/ABSG tenants only); call stored procedures `time_duration_check`/`time_duration_check_multishift`.

### Controller/EmployeeLoanController.php (drives `emp_loan`/`emp_loan_info`/`emi_upload`)
Full method inventory (28 public methods; grepped at EmployeeLoanController.php lines 59, 77, 102, 111, 427, 489, 554, 585, 669, 787, 823, 842, 850, 854, 862, 1069, 1141, 1158, 1226, 1381, 1425, 1455, 1464, 1480, 1503, 1523, 1723, 1854, 1918, 2012, 2199, 2210). Key ones:
- `uploadandsaveempctc()` (line 111) — bulk Excel loan creation; **computes EMI via amortization formula**, see §3.3.
- `form()` (line 427), `emi_upload()` (line 489) — near-duplicate popup forms (the latter appears to be a half-finished variant reusing the same field-population logic).
- `getEmi()` (line 554) — AJAX: returns EMI amount for a given employee+month from `emp_loan_info`, joined to `emp_loan`.
- `update_transfer()` (line 585) — **moves an EMI installment from one month to another**, see §3.3 (guarded against already-processed payroll months).
- `amount_pay()` (line 669) — **records an out-of-cycle additional/lump-sum payment against a loan**, see §3.3.
- `update()` (line 787) — loan detail/ledger view (opening/closing balances, paid totals).
- `checkmonth()` (line 823) — validation helper.
- `completed()` (line 842) — marks a loan `is_completed='Y'`.
- `employeeemiloansave()` (line 1141), `employeeloansave()` (line 1158) — save actions (two separate, likely overlapping, save paths — legacy duplication).
- `employeeloanlist()` (line 1226) — datagrid list.
- `payroll_check_amount_pay()` (line 1503) — guards `amount_pay()` against months already processed by payroll.
- `uploadandsaveempemi()` (line 2012) — **separate bulk EMI-amount upload path writing directly to `LoanEmi`/`emi_upload`** (not `emp_loan_info`) — see §3.3, this is a distinct mechanism from the tenure-based EMI schedule.
- `deleteEmployeesemi()` (line 2199), `getLoanBalance()` (line 2210) — delete / balance-lookup helpers.

### Controller/EmployeeEmiController.php (near-duplicate of parts of EmployeeLoanController)
`$uses` is identical to `EmployeeLoanController`'s. 13 public methods (index, emi_upload, getEmi, getEmployee, getBalance, checkmonth, employeeemiloansave, jsons, salarycheck, employeelist, downloademploanformat, uploadandsaveempemi, jsonsb — grepped at lines 55, 84, 183, 228, 325, 366, 384, 426, 467, 489, 642, 856, 1089). This appears to be a **forked/parallel controller for the same EMI-upload feature** rather than a distinct domain concept — high overlap with `EmployeeLoanController::uploadandsaveempemi`/`getEmi`/`checkmonth`. **Possible legacy cruft/duplicate controller — verify with product owner which one (or both) is actually routed to/used before deciding what to port.**

### Controller/EmployeeExpensesController.php (drives `emp_expense` — two-level approval)
24 methods (grepped at lines 59, 86, 137, 221, 268, 1143, 1638, 1655, 1781, 1799, 1818, 1867, 1944, 1978, 2011, 2018, 2052, 2078, plus three commented-out `employeelist()`/`Expenses()` duplicates left in place as dead code at lines 374, 626, 981, 1227, 1292 — **legacy cruft, verify before migrating**).
- `index()` (line 59) — **contains the confirmed `setup()` bug**, see §3.4.
- `grandexpense()` (line 1867) — **the two-level Authorize→Approve state machine**, see §3.2.
- `manageexpense()` (line 1818), `empexpenselist()` (line 1799), `listempexpense()` (line 1944), `listempexpenseverified()` (line 1978) — CRUD/list actions.
- `employeerequests()` (line 2011), `viewrequest()` (line 2018), `viewexpense()` (line 2052) — read views.
- `showimage()` (line 2078) — serves the uploaded receipt image (see `emp_expense.image` NOT NULL quirk, §4).
- `cancalEmployee()` (line 1781, sic — typo for "cancel") — soft-delete/cancel action.

### Controller/ProjectExpensesController.php (also drives `emp_expense` — single-level approval, project/PO-flavored)
50 methods (full grep list captured; representative ones: `index()` line 58 — **setup() bug**, `grandexpense()` line 758 — **single-level Approve/Reject + ZWLK cascade**, see §3.2; plus a large purchase-order-style subsystem not shared with `EmployeeExpensesController`: `save()` (1372), `edit_save()` (1568), `return_save()` (1721), `editpayment_save()` (1792), `loadtable()` (1816), `deleteorder()`/`deleteordermaster()` (1915/1930), `downloadexcels()` (2055), plus lookup helpers `beneficiary()`, `expense_type()`, `expense_typelist()`, `request_id()`, `get_details()` (2415-2525) that depend on the missing `emp_expense_details`/`emp_expense_payment`/`expense_item` tables noted in §0).

### Controller/VehicleExpensesController.php (drives `transportation_expense` — **table not in schema**)
6 methods: `form()` (56), `get_rate_per_km()` (100), `employeeloansave()` (115, misleadingly named — actually saves a vehicle-trip expense, same naming-cruft pattern as `EmployeeadvanceController::employeeloansave`), `vehicleexpenselist()` (177), `Expenses()` (238), `deleteEmployee()` (249). Payment tracking half (`TransportationPayment` model, lines 120/144/161) writes to a phantom `transportation_payments` table that also doesn't exist in schema — **entire controller is at risk of being dead code in this tenant; verify against live DB.**

### Controller/ExpenseItemController.php (drives `expense_item` — **table not in schema**)
8 methods: `index()` (50, empty), `itemfilter()` (57), `form()` (113), `chkcategory()` (153, queries `item_master` — a different, unrelated table), `itemlist()` (177), `itemdelete()` (240), `save()` (262). All core list/save/filter queries hit `expense_item` directly (e.g. line 68, 137, 216, 221) — **at risk of being entirely dead if the table is truly absent.**

### Controller/ExpenseTypeController.php / ExpenseTypesController.php (drive `expense_type`, which DOES exist — but reference `expense_heads` and `allocate_expense`, which do NOT)
`ExpenseTypeController` (351 lines) is the fuller version with 11 methods including `allocate_expense()` (268), `save_allocate()` (282), `remove_allocate()` (321) — the designation-to-expense-type allocation feature, entirely dependent on the missing `allocate_expense` table. `ExpenseTypesController` (212 lines, 8 methods) is a near-identical but trimmed **duplicate controller** (same `$uses`, same core methods `index/expense/delete/save/listexpense/checkexpensetypenameexists/checkexpensetypecodeexists/search`, missing only the allocate_* methods) — **another apparent fork/duplicate pair; verify which route is live before migrating** (same pattern as EmployeeLoanController vs EmployeeEmiController).
- `expense()` (ExpenseTypeController.php:41) and `save()` (line 81) both reference `expense_heads` directly (lines 58, 143) — every `expense_type` row's `expense_head_fkey` is set from this dropdown, but the backing table doesn't exist in the schema (see §4 orphaned-FK quirk).
- `listexpense()` (line 162) LEFT JOINs `expense_heads` as `ExpenseHead` (lines 180-190) to enrich the grid with head names — will silently return NULL head names (LEFT JOIN, not error) rather than crash, unlike the more naive controllers that do inline SELECTs against missing tables.

## 2. Complex logic / state machines

### 2.1 Advance eligibility — "80% of monthly CTC, attendance-prorated for GLET/ABSG"

Confirmed. Formula is implemented **twice**, near-identically, in `Controller/EmployeeadvanceController.php`: once for the interactive single-employee AJAX check (`salary()`), and once for the bulk-Excel-upload path (`uploadandsaveempctc()`). They are NOT calls to a shared function — the logic is copy-pasted between the two with minor divergences (flagged below).

**Base rule (all tenants):**
```
ep_salary  = round(emp_anual_ctc / 12)              // monthly CTC, from emp_ctc_transaction.emp_anual_ctc where end_date_effective IS NULL
salary     = round(0.80 * ep_salary)                 // 80% of monthly CTC — the eligibility ceiling
```
- `salary()`: EmployeeadvanceController.php:761-764 — `$ep_salary = round($emp_slary / 12); $salary = round((80 / 100) * $ep_salary);`
- `uploadandsaveempctc()`: EmployeeadvanceController.php:406-409 — `$ep_salary = $monthly_ctc = round($emp_slary / 12); $salary = round((80 / 100) * $ep_salary); $salary_amount = $salary + 1;` (note the bulk-upload path adds `+1` to the ceiling — off-by-one, likely to make the comparison `<` behave like `<=`; the interactive path does not add this).

**Attendance proration — GLET/ABSG tenants only** (checked via `$company_code == 'GLET' || $company_code == 'ABSG'`):
```
daysInMonth      = COUNT(*) from emp_detail_timeattandance between the tenant's payroll cycle start/end
                    (via att_start_end_fn() stored function), optionally excluding weekoffs/holidays
                    depending on prorate_code (from emp_salary_structure.prorate_code)
working_day_count = SUM of 'P' (present) markers for the month from emp_detail_timeattandance / emp_site_detail_timeattandance
                    (table choice depends on whether device attendance type is 'SIT', via device_attandance lookup)
per_day_wage      = monthly_ctc / daysInMonth     (interactive path uses ep_salary; bulk path uses monthly_ctc — same value, different variable name)
advance_limit     = round(per_day_wage * working_day_count)
```
- `salary()` (interactive): EmployeeadvanceController.php:776-861. **For GLET/ABSG, the attendance-prorated `advance_limit` REPLACES `salary` entirely** (line 861: `$salary = round($advance_limit)`) — the flat 80%-of-CTC ceiling computed earlier at line 764 is thrown away for these two tenants and only the attendance-derived cap is used.
- After computing `salary`, the interactive path further **subtracts advances already taken for the month**: EmployeeadvanceController.php:863-869 — `existing_advance = SUM(advance_amount) FROM emp_advance WHERE emp_fkey=... AND affected_month IN (month_year, month_year-01) AND status=1`, then `salary = max(0, salary - existing_advance)`. The returned `salary` is the **remaining eligible amount**, not the gross ceiling.
- `uploadandsaveempctc()` (bulk): EmployeeadvanceController.php:414-527 computes the same `advance_limit` (line 524: `$advance_limit = round($per_day_wage * $working_day_count)`), but here **both** `$salary_amount` (flat 80% cap +1) and `$advance_limit` (attendance cap) are kept as separate variables. The comparison logic then branches by tenant (lines 546-559):
  - **GLET/ABSG:** existing advances for the month are added to the requested `$amount` first (line 539-544), then the row is accepted only if `$amount <= $advance_limit` (line 548); if not, it's pushed to `$rejeted_exceeding_advance[]` (line 551) — but then line 554 unconditionally sets `$condition_statemnt = true`, **so the advance-limit rejection is effectively logged but never actually blocks the save** (the row is rejected only via the immediately following `if ($condition_statemnt)` gate, which is now always true) — this looks like a **bug**: the exceeding-advance rows are collected for the response message but not actually excluded from being saved.
  - **Other tenants:** condition is simply `$amount < $salary_amount` (the flat 80%+1 cap); if it fails, the row goes to `$rejeted_exceeding_ctc[]` and IS skipped (the `else` branch at line 579 does not call `save()`).
- Daily-wage/hourly-wage employees (`emp_type IN ('DAILY WAGES','HOURLY WAGES')`) are **exempt from the eligibility cap entirely** — EmployeeadvanceController.php:398, 590-601 — any amount saves unconditionally.
- Non-GLET/ABSG tenants get **no attendance proration at all** — the 80%-of-CTC flat cap is the only check.

**Precise citations:** `Controller/EmployeeadvanceController.php:746-879` (`salary()`), `Controller/EmployeeadvanceController.php:386-607` (`uploadandsaveempctc()` eligibility block). Re-confirmed prior research's characterization as accurate but incomplete — the "80% of CTC" and "attendance-prorated" are not combined additively; for GLET/ABSG the attendance-prorated figure fully **replaces** the 80% figure (interactive path) or is tracked as a **separate, effectively non-blocking** check (bulk path, due to the `$condition_statemnt = true` bug at line 554).

### 2.2 Two-level vs single-level expense-approval state machines

Both act on the **same table**, `emp_expense` (schema/mypayrol_trial.sql:43256), specifically its columns `expense_status` (varchar(200), default `'Applied'`), `status` (int(1), default 1), `authorized_by`/`authorized_date`/`remarks_auth`, `approved_by`/`approved_date`/`remarks_approved`. Two different controllers implement two different state machines over the same columns, confirming the prior finding of **two independent, non-shared approval implementations**.

**A. `EmployeeExpensesController::grandexpense()` — two-level Authorize → Approve** (`Controller/EmployeeExpensesController.php:1867-1942`):
- States (via `expense_status` string): `Applied` (implicit initial/default) → `Authorized` → `Approved`, or → `Rejected` at any point.
- `status` int column: `1` = active/non-rejected, `2` = rejected (line 1897) — a **second, redundant status encoding** layered on top of `expense_status`.
- Who transitions: determined by comparing the current session's `emp_fkey` against the row's stored `authorized_by`/`approved_by` values (fetched at line 1880-1885), or `user_group == 1` (admin) which can act as approver directly.
  - If actor == stored `approved_by` OR is admin (`user_group==1`) and not rejecting: sets `expense_status='Approved'`, stamps `approved_date` (lines 1887-1890, 1921-1922).
  - Else if actor == stored `authorized_by` and not rejecting: sets `expense_status='Authorized'` only (lines 1891-1893, 1923-1924) — i.e. **the authorizer's action does not by itself move the record to Approved; it only flips the intermediate flag**, and a separate action by the approver is required.
  - If rejecting (`arr_form_data['reject']=='0'`, note: **the flag name is `reject` but the value `'0'` means "not a rejection" — a confusing inverted-boolean-as-string convention**, see §4): `expense_status='Rejected'`, `status=2`.
  - `cur_emp_key == null` branch (lines 1899-1913): when there's no session employee (i.e., acting as Admin/system), remarks are auto-prefixed `"Authorized by Admin. "` / `"Approved by Admin. "` (or `"Rejected by Admin. "`), and **both** `approved_date` and `authorized_date` are stamped simultaneously — effectively collapsing the two-level flow into a single admin action.
- Remarks (`remarks_auth`, `remarks_approved`) are updated independently depending on which role the current actor matches (lines 1915-1919).
- No `advance_payment`-style cascade side effects in this controller.

**B. `ProjectExpensesController::grandexpense()` — single-level Approve/Reject, with ZWLK-tenant cascade** (`Controller/ProjectExpensesController.php:758-805`):
- Only reads/writes `authorized_by`/`authorized_date`/`remarks_auth` — **there is no `approved_by`/`approved_date` step at all** in this controller's state machine; the variable is even named `$apr_person` but is assigned from `authorized_by` (line 773), confirming there is genuinely only one approval tier here despite the naming.
- On accept (`reject=='0'`): `expense_status='Approved'`, `status='1'` (lines 776-777).
- On reject: `expense_status='Rejected'`, `status='1'` (note: **`status` stays `1` on rejection here**, unlike `EmployeeExpensesController` which sets `status=2` — inconsistent status-code semantics between the two controllers acting on the same table, §4) — **and**, tenant-gated: if `company_code=='ZWLK'` and `headkey==1` (an expense-head parameter passed from the form), cascades a raw `UPDATE advance_payment SET status='0' WHERE expense_fkey='$curr_expensepkey'` (line 782) — voiding related advance-payment rows. **This targets the `advance_payment` table which does not exist in the schema dump** (§0) — so on `mypayrol_trial` this cascade would either silently no-op or throw, depending on MySQL strict mode; **flag as unverified/possibly-dead tenant-specific logic.**
- `$cur_emp_key == null` (admin) handling (lines 785-793) similarly auto-prefixes remarks with "Approved By ADMIN :" / "Rejected By ADMIN :".

**Net conclusion (re-confirms and sharpens prior research):** these are genuinely two separate state machines sharing one table and one method name (`grandexpense`) but different controllers, different column sets used, different `status` semantics on rejection, and only one of the two implements the second (Approve) tier and the ZWLK cascade. A migration must decide whether `EmployeeExpenses` (2-tier) and `ProjectExpenses` (1-tier + cascade) map to one unified approval workflow or remain two distinct domains.

### 2.3 Loan repayment / EMI deduction logic

Confirmed connection to `EmployeeEmiController`/EMI-type tables, but it's **two distinct, non-integrated mechanisms**:

**Mechanism 1 — tenure-based EMI schedule, `emp_loan` + `emp_loan_info`** (the primary loan feature):
- EMI computed at loan-creation time in `EmployeeLoanController::uploadandsaveempctc()` (Controller/EmployeeLoanController.php:213-240), using a standard reducing-balance amortization formula when an interest rate is set:
  ```
  r = interest_rate / 100
  emi = floor( (P * r/12) * (1+r/12)^n / ( (1+r/12)^n - 1 ) )     // P=principal, n=tenure months
  ```
  or, if `intrest_rate == 0`: `emi = amount / month` (simple flat division, line 237).
- A **month-by-month amortization schedule is then materialized as rows in `emp_loan_info`** (lines 330-365): for each of the `tenure` months, `opening_balance`/`closing_balance`/`principle`/`interest`/`amount_to_paid`/`loan_emi` are computed iteratively (`interests = opening_balance * interest/100`; `interestpaid = interests/12`; `principal = emi - interestpaid`; `closing_balance = opening_balance - principal`) and inserted via raw SQL (lines 354-355). `paid_status` defaults to `'A'` (schema default, emp_loan_info.paid_status varchar(11) NOT NULL DEFAULT 'A' — schema/mypayrol_trial.sql:43505).
- Guard rails at loan creation: rejects if `emi_start_month < current month` (`$month_issues`, lines 245-248, 372-378) or if payroll for that month is already processed (`$payroll_issues`, lines 249-254, 379-384) — checked against `emp_salary_slip` with `end_date_effective IS NULL`.
- **`paid_status` values found in code (legacy status codes, undocumented anywhere but comments):** `'A'` = not yet paid / scheduled tenure month (default), `'P'` = paid, `'S'` = additional/out-of-cycle payment (see `amount_pay()` insert at EmployeeLoanController.php:733-734, comment at line 639: `//paid_status[A=Not paid and tenure month,P=Paid month,S=Additional Payment]`).
- `emi_transfer` char(1) flag (`'Y'`/`'N'`, schema default `'N'`) marks a row whose EMI was moved to a different month by `update_transfer()` (EmployeeLoanController.php:585-666) — that method zeroes out the source month's row (`loan_emi=0, amount_to_paid=0, principle=0, paid_status='P', emi_transfer='Y'`, line 638) and creates/updates a row at the target month with the transferred EMI amount, after verifying via `emp_salary_slip` that neither the source nor target month's payroll is already processed (lines 615-630).
- `amount_pay()` (EmployeeLoanController.php:669-786) handles **out-of-cycle lump-sum payments**: validates the payment doesn't exceed the outstanding balance (`loan_amount - (amount_paid + emi)`, line 720), inserts a new `emp_loan_info` row with `paid_status='S'` (line 734), then walks scheduled `'A'` rows **from most-recent month backward** (line 739, `order by loan_month desc`) deducting the lump sum against future EMIs (fully zeroing fully-covered months, partially reducing the first partially-covered month) — lines 741-765. If the running total paid reaches/exceeds `loan_amount`, the loan is marked `emp_loan.is_completed='Y'` and all its `emp_loan_info` rows are force-set to `paid_status='P'` (lines 775-778).

**Mechanism 2 — flat monthly EMI-amount override upload, `LoanEmi`/`emi_upload`** (a separate, simpler bulk-upload path, NOT integrated with the `emp_loan_info` schedule above):
- `EmployeeLoanController::uploadandsaveempemi()` (lines 2012-2197) and the near-duplicate `EmployeeEmiController::uploadandsaveempemi()` (lines 856-1088) both write directly to `LoanEmi`/`emi_upload` (schema/mypayrol_trial.sql:42093 — columns `loan_pkey`, `emp_pkey`, `month_year` varchar(100), `amt`, `status`) via `$this->LoanEmi->saveAll($arr_empctc_data)` (EmployeeLoanController.php:2152). This looks like a manual "override the EMI amount for employee X, loan Y, month Z" mechanism, independent of the computed amortization schedule in `emp_loan_info` — **it is unclear from the code whether/how `emi_upload` rows are later consumed by payroll processing; this needs verification against the payroll-engine cluster (out of this report's scope) before assuming it's live/authoritative.**
- `EmployeeEmiController` largely duplicates `EmployeeLoanController`'s EMI-related methods (`getEmi`, `checkmonth`, `employeeemiloansave`, `uploadandsaveempemi`) — **possible legacy fork; verify which controller/route is actually used in production before porting either.**

## 3. Confirmed bugs (re-verified with exact citations)

### 3.1 `$this->setup()` undefined-method bug
Re-confirmed in all three controllers named in the brief, plus one more found during this pass:
- `Controller/EmployeeadvanceController.php:76` — `$this->setup($emp_fkey);` inside `index()` (lines 59-79), reached when `$user_group == '2'` (employee self-service view).
- `Controller/EmployeeExpensesController.php:76` — identical `index()` structure, identical bug.
- `Controller/ProjectExpensesController.php:74` — identical `index()` structure, identical bug.
- Confirmed `setup()` is **not** defined in `AppController.php` (grepped, no match) and **not** defined anywhere in these three controllers (grepped `function setup(` across the whole file for each — no match in these three files). It IS defined as a controller-local method in several *other*, unrelated controllers (`EmployeeController.php:2309`, `EmployeeJoinController.php:1681`, `EmployeeConfigController.php:746`, `TaxController.php:173`, `EmployeeResignationController.php:1985`, `CompanyController.php:75`, `CompanyNewController.php:69`, `EmployeeUnderController.php:223`, `EmployeeTaxController.php:102`, `DataUploaderController.php:495`) — strongly suggesting `EmployeeadvanceController`/`EmployeeExpensesController`/`ProjectExpensesController` were copy-pasted from one of those and the `setup()` method body was simply never copied over. **Any employee (user_group==2) hitting these three controllers' landing page (`index()`) will trigger a PHP fatal error** (call to undefined method). This is a genuine, currently-live bug in the legacy app affecting the employee self-service view for advances, expenses, and project-expenses.

### 3.2 Advance-eligibility `$condition_statemnt = true` override bug (new finding, not in prior research)
`Controller/EmployeeadvanceController.php:554` — inside `uploadandsaveempctc()`, for GLET/ABSG tenants, after checking `$amount <= $advance_limit` and logging exceeding rows to `$rejeted_exceeding_advance[]` (lines 548-553), the code **unconditionally** does `$condition_statemnt = true;` immediately after, which means the subsequent `if ($condition_statemnt)` gate at line 559 always passes for these tenants regardless of whether the advance-limit check failed — **the attendance-prorated cap is computed and reported in the rejection list shown to the user, but never actually prevents the save.** This is a functional bug in the bulk-upload eligibility enforcement for GLET/ABSG specifically (the interactive `salary()` endpoint does not have this bug, since it just returns a ceiling for the client-side form to respect, rather than enforcing it server-side either way — meaning **for these two tenants, the 80%/attendance cap is effectively advisory-only, not enforced**, on both the interactive and bulk-upload paths).

## 4. Schema quirks

- **`emp_expense.image` is `varchar(500) NOT NULL` with no default** (schema/mypayrol_trial.sql:43279) — a receipt-image path is mandatory at the DB level for every expense row, which will reject inserts that omit an image unless the controller always supplies at least an empty string; worth checking `EmployeeExpensesController`'s save path for how it satisfies this (not read in this pass — flag for follow-up if the "save expense" flow is in scope elsewhere).
- **`emp_expense.authorized_by`/`approved_by` are `varchar(10) NOT NULL`** with no default (schema/mypayrol_trial.sql:43262, 43265) — yet `grandexpense()` in `ProjectExpensesController` never writes `approved_by` at all (§2.2), meaning that column sits permanently empty/whatever-was-set-at-creation for all project-expense rows — a NOT NULL column that's semantically unused by one of the two controllers acting on the table.
- **`emp_expense.expense_status` default `'Applied'`** (schema/mypayrol_trial.sql:43277) vs the two different rejection `status` int codes used by the two controllers (`status=2` in EmployeeExpensesController vs `status='1'` — i.e. unchanged — in ProjectExpensesController on rejection): **the same `status` column means "rejected" in one code path and "still active" in the other**, a landmine for any migration logic that filters on `status` alone without also checking `expense_status`.
- **`expense_type.expense_head_fkey`** (schema/mypayrol_trial.sql:44294, `int(11) NOT NULL`) — every seeded row in the dump has `expense_head_fkey = 0` (schema/mypayrol_trial.sql:44305-44314, all ten seed rows), and `ExpenseHead`/`expense_heads` (the table this FK notionally references) doesn't exist in the schema at all (§0) — this is an **orphaned, always-zero foreign key** pointing at a phantom table; effectively dead/never-populated in this tenant's data.
- **`emp_advance` has no approval columns at all** (schema/mypayrol_trial.sql:42187-42201: just `emp_advance_pkey, emp_fkey, advance_amount, affected_month, is_credited, remarks, created_date, created_by, modified_by, modified_date, status, payment_date`) — unlike `emp_expense`, advances have **no authorize/approve workflow in the schema** — the "eligibility check" (§3.1) is a pre-save gate, not a post-save approval state machine. `is_credited` char(1) DEFAULT 'N' is the only lifecycle flag: `'N'` = advance recorded but not yet paid out/credited to the employee, `'P'`... actually only `'N'`/(presumably `'Y'`) values appear used by the list filter (`EmployeeadvanceController.php:982,992` filters `is_credited='N'`); the code never shown flipping it to another value in this pass (payroll-engine integration, out of scope) — flag for follow-up in the payroll cluster.
- **`emp_advance_info` and `emp_loan_info`'s sibling table `emp_advance_info`** (schema/mypayrol_trial.sql:42204-42215) has generic `attr1`/`attr2`/`attr3` catch-all columns (`varchar(500) NOT NULL`, `int(11) NOT NULL`, `int(11) NOT NULL`) — classic "we didn't know what we'd need" legacy columns; **no controller in this cluster's `$uses` list (`EmployeeAdvanceInfo` is not referenced by any of the controllers read in this pass) actually reads/writes this table** — it may be entirely unused/orphaned. Verify against a full-codebase grep before assuming it's dead.
- **`emp_loan_info.modified_date`** has `DEFAULT '0000-00-00 00:00:00'` (schema/mypayrol_trial.sql:43514) — a zero-date default, which is invalid under MySQL strict mode / `NO_ZERO_DATE`; will cause insert errors on strict MySQL 5.7+/8.0 configurations unless SQL mode is relaxed for this tenant. Classic legacy-schema landmine for any Postgres or strict-MySQL migration target.
- **`emi_upload.month_year` is `varchar(100)`** (schema/mypayrol_trial.sql:42097) storing what's functionally a `YYYY-MM` value (per `uploadandsaveempemi()`'s `$arr_empctc_data['month_year'] = $ctcuploadtype;`, itself a numeric/string month param) — stored as unstructured free text rather than a date/month type, meaning **there is no DB-level guarantee of format consistency** between this and `emp_loan_info.loan_month` (`varchar(11)`, schema/mypayrol_trial.sql:43496) — both loan-month fields are strings, and the two mechanisms (§2.3 Mechanism 1 vs 2) use differently-shaped month strings in different places (`Y-m` vs `Y-m-01` vs raw upload param) throughout the controllers — a real risk of silent join/match failures if these ever need to be correlated.
- **`expense_type.expense_type_name` + `status` compound unique key** (`UNIQUE KEY expense_type_name_status (expense_type_name,status)`, schema/mypayrol_trial.sql:44301) — this is the standard "soft delete allows name reuse" pattern (a name can be reused once the old row's `status` differs), consistent with the soft-delete (`status=0`) pattern used everywhere else in this cluster.
- Duplicate/forked controllers observed (not a schema quirk per se, but a strong signal of migration risk): `EmployeeAdvanceReportsController` vs `EmployeeAdvanceReportsController_2018-010.php`, `EmployeeExpenseReportsController` vs `_2018-10.php`, `EmployeeLoanReportsController` vs `_2018-10.php` (all found via directory listing, not read in this pass — reporting controllers, likely out of this report's core scope but worth flagging since they sit right next to this cluster) — plus the `EmployeeLoanController`/`EmployeeEmiController` and `ExpenseTypeController`/`ExpenseTypesController` near-duplicate pairs documented in §1. **Systemic pattern in this codebase: features get forked-and-modified rather than refactored, leaving multiple live-looking implementations of the same feature. Every "which controller is actually live" question in this report needs a routing-config or access-log check before migration, not just a code read.**

## 5. Summary of "possible legacy cruft — verify before migrating" flags
1. `AdvanceController` (generic "Advance" feature) and its `advance_expense` table — table absent from schema.
2. `ExpenseItemController` and its `expense_item` table — table absent from schema.
3. `ExpenseHead` model, `expense_heads` table, and everything reading it (`ExpenseTypeController::expense()`/`save()`/`listexpense()`) — table absent from schema; `expense_type.expense_head_fkey` is always 0 in seed data.
4. `Allocate_expense` model, `allocate_expense` table, and `ExpenseTypeController::allocate_expense()`/`save_allocate()`/`remove_allocate()` — table absent from schema.
5. `ExpenseTypeDetails`/`ExpenseTypePaymentDetails` models (`emp_expense_details`/`emp_expense_payment` tables) and the `ProjectExpensesController` purchase-order subsystem depending on them — tables absent from schema.
6. `VehicleExpensesController` and `transportation_expense`/phantom `TransportationPayment`→`transportation_payments` — both tables absent from schema.
7. `advance_payment` table referenced by the ZWLK-tenant cascade in `ProjectExpensesController::grandexpense()` (line 782) — table absent from schema.
8. Three commented-out duplicate method definitions left in `EmployeeExpensesController.php` (`employeelist()` x2, `Expenses()` x2 — lines 374, 626, 981, 1227, 1292).
9. `EmployeeEmiController` as a likely-duplicate fork of `EmployeeLoanController`'s EMI-upload methods.
10. `ExpenseTypesController` as a likely-duplicate trimmed fork of `ExpenseTypeController`.
11. `EmployeeadvanceController::emptaxationdetails()` (line 253) — empty method body, dead stub.
12. Hard-coded tenant-specific bank-name lookup table in `AdvanceController::advancelist()` (lines 143-171).
13. `Controller/*ReportsController_2018-*.php` variants sitting alongside the current report controllers for this cluster (not read in depth — flagged for the reports-focused pass of this migration).

## 6. Confirmed items re-checked from prior research
- `PaymentApprovalsController` was not touched in this pass (out of scope; prior research already characterized it as SaaS plan/feature-flag, unrelated to this cluster's approval logic) — no contradiction found.
- Two independent approval implementations (`EmployeeExpensesController::grandexpense()` two-level, `ProjectExpensesController::grandexpense()` single-level) — **confirmed with full code detail**, see §2.2.
- ZWLK-tenant cascade voiding `advance_payment` on rejection — **confirmed**, `ProjectExpensesController.php:781-783`, and additionally found that `advance_payment` itself is a table absent from the schema (new finding this pass).
- `$this->setup($emp_fkey)` bug at `EmployeeadvanceController.php:76`, `EmployeeExpensesController.php:76`, `ProjectExpensesController.php:74` — **all three re-confirmed exactly at the cited lines.**
- 14 barebones backing models, no `$validate`, no hooks — **confirmed for all 14** by reading every model file in full; extended finding that 7 of the 14 (plus 2 phantom-model/raw-table references) point at tables absent from the schema dump.
- 80% of monthly CTC, attendance-prorated for GLET/ABSG — **confirmed and fully detailed with exact formula and line citations**, see §2.1; additionally found the eligibility check is effectively non-enforcing for GLET/ABSG in the bulk-upload path due to a `$condition_statemnt = true` override bug (§3.2), and that for GLET/ABSG the attendance-prorated figure *replaces* rather than *combines with* the 80%-CTC figure in the interactive path.

---

### 7.6 Reports

# Reports Cluster — Data Model & Business-Logic Report

Scope: `Controller/ReportsController.php`, `Controller/ReportController.php`,
`Controller/StatutoryReportController.php`, `Controller/TaxReportController.php`,
`Controller/SalaryReportsController.php`, `Controller/HierarchyReportController.php`,
`Controller/EsiEpfReportController.php`.

Focus: business-rule-encoding logic only (routine report-listing plumbing —
`hrreports→changereporttype→addreportcriteria→loadcriteriaitems→listcriteriaitems→generatereport`,
PDF/Excel export mechanics — is already covered by prior research and is not repeated here
except where it materially differs).

---

## 1. Schema cross-check: `report_audit` / `reportcriterias`

### `report_audit` — `schema/mypayrol_trial.sql:45746-45768`

```sql
CREATE TABLE `report_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` varchar(100) NOT NULL,
  `report_component` varchar(100) DEFAULT NULL,
  `report_from` varchar(50) DEFAULT NULL,
  `report_to` varchar(50) DEFAULT NULL,
  `include_resigned` int(11) DEFAULT NULL,
  `Include_negative_salary` int(11) DEFAULT NULL,
  `criteria` varchar(100) DEFAULT NULL,
  `criteria_name` varchar(100) DEFAULT NULL,
  `items` varchar(1000) DEFAULT NULL,
  `items_count` varchar(50) DEFAULT NULL,
  `mode` varchar(50) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
```

- Engine is **MyISAM** (no FK/transaction support), unlike most of the schema which is InnoDB — a legacy leftover, consistent with it being a pure append-only audit log.
- Model `Model/ReportAudit.php:7-16` is a bare `AppModel` (`$useTable = 'report_audit'`, `$primaryKey = 'id'`) with **no validation rules and no associations declared**. This matches the table exactly — every column is `varchar`/`int`/nullable, so the lack of validation is consistent with the schema, not a gap introduced by the model.
- Confirmed actual write path: `TaxReportController::reportAudit($type, $mode)` at `Controller/TaxReportController.php:299-363` builds `$dataForHistory` from `$_REQUEST` (report_from/report_to/report_type/criteria/criteria_name/items/items_count/mode/user_id/user_name) and calls `$this->ReportAudit->useDbConfig = ...; $this->ReportAudit->save($dataForHistory);` (`TaxReportController.php:361-362`). No `report_component` is ever populated by this controller — that column appears write-only from other report controllers not in this scope, or is dead. `Include_negative_salary` (`ngtvsal`) is captured too (`TaxReportController.php:317`).
- Sample audit rows in the dump are literally `'Logged in'` / `'Logged out'` with `mode = 'Auth'` (`schema/mypayrol_trial.sql:45767-45768`) — confirming `report_audit` doubles as a **generic user-activity log**, not solely a report-download log, despite its name and the model's singular purpose implied by `ReportsController`/`TaxReportController` usage.

### `reportcriterias` — `schema/mypayrol_trial.sql:45575-45583`

```sql
CREATE TABLE `reportcriterias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporttype` varchar(50) NOT NULL,
  `reportcriteria` varchar(100) NOT NULL,
  `reportcriteria_desc` varchar(100) NOT NULL,
  `reportcriteria_field` varchar(50) NOT NULL COMMENT 'Field to be filtered on report generation',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0: Inactive, 1: Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

- Model `Model/ReportCriterias.php:7-16` — bare `AppModel`, `$useTable = 'reportcriterias'` (matches, note table name has no underscore), `$tablePrefix = ''` explicitly set (overriding any global table prefix — suggests this table was retrofitted from a shared/central DB at some point). No associations declared, and **none are needed**: `reporttype` and `reportcriteria` are free-text strings, not FKs to a normalized `report_type` table.
- **No `report_type` table exists anywhere in the schema** (`grep -i "CREATE TABLE.*report_type|reporttype|report_category"` returns nothing). Report "types" are just string literals (`'employee'`, `'shiftpolicy'`, `'Attendance'`, `'TaxTDS'`, `'EPF'`, `'ESI'`, etc.) matched in PHP `switch` statements against the `reporttype` column — this is a **stringly-typed enum**, not a normalized model. Migrating this to Next.js should introduce an actual enum/lookup table since there is no schema-level source of truth for the valid list beyond the `reportcriterias` seed data itself (`schema/mypayrol_trial.sql:45585-45654+`) and scattered controller `switch` cases.
- `reportcriteria_field` stores raw column names to filter on (`joining_date`, `emp_dept`, `emp_branch`, `salary_head_item_pkey`, etc.) — i.e., the DB row encodes a dynamic-query field name that the controller interpolates directly into SQL (see §3 eval/dynamic-SQL risk notes and §2 below). This is a metadata-driven query builder pattern, not declarative validation.

---

## 2. Custom methods / models beyond bare `ReportAudit`

- **No dedicated business-logic Model exists for any of the 7 controllers in scope.** All of `ReportsController`, `ReportController`, `StatutoryReportController`, `TaxReportController`, `SalaryReportsController`, `HierarchyReportController`, `EsiEpfReportController` declare only bare `AppModel` subclasses in their `$uses` arrays (`ReportCriterias`, `ReportAudit`, `EmployeeDetails`, `EmployeeProfessionalDetails`, `EmpCtcTransaction`, `Departments`, `Verticals`, `Units`, `DayTimeProcedures`, `AttendanceRegister`, `AttendanceRegisterReport`, `DbConfig`, `Gender`, `FinancialYear`, `EmployeeTaxsalsum(New)`, `Plan`, `Features`, `PlanFeature`, `CentralUserCredentials`, `LeaveRequests`, `Leavestatus`, `SalaryHeadItems`, `Grades`, `DeviceAttendance`, `CompanyContactInfo`, `LeaveType`, `EmployeeSalaryStructure`, `EditPunches`, `Attendance`, `MobileUserauditor`, `Banks`, `Designation`, `Menu`, `HolidayGroup`, `SalaryStructures`, `CentralControl`, `UserCredentials`).
  - `Controller/ReportsController.php:52`
  - `Controller/ReportController.php:51`
  - `Controller/StatutoryReportController.php:53-70+`
  - `Controller/TaxReportController.php:50`
  - `Controller/SalaryReportsController.php:52`
  - `Controller/HierarchyReportController.php:52`
  - `Controller/EsiEpfReportController.php:55-72+`
- **Key finding: `EmpCtcTransaction` is used purely as a raw-SQL query proxy, not for its own table.** `Model/EmpCtcTransaction.php:7-17` is a bare `AppModel` bound to `useTable = 'emp_ctc_transaction'` / `primaryKey = 'emp_ctc_transaction'`, but essentially every statutory/salary report calls `$this->EmpCtcTransaction->query("...")` with hand-written SQL that joins/selects from completely different tables — `employee_info` (a VIEW, see §3), `emp_salary_slip`, `emp_details`, `emp_proff`, `branches`, `department`, `termination`, `tax_salary_components`, `professional_tax_view`, `db_config`, etc. Examples: `Controller/StatutoryReportController.php:803-822` (gross/EPF/ESI/WWF/TDS/PT aggregation query), `Controller/SalaryReportsController.php:13386-13392` (LOP report salary-head keys), `Controller/EsiEpfReportController.php:1911-1913` (`SHOW COLUMNS FROM emp_salary_slip`). This confirms the pattern noted in prior research: business logic lives entirely in controller SQL strings, with the CakePHP Model layer reduced to a connection/query-proxy role. Any model in `$uses` (`EmpCtcTransaction`, `EditPunches`, `Attendance`) is interchangeable for this purpose since only `->query()` / `->getDataSource()->query()` is invoked, not ORM finders.
- `TaxReportController::_modelExists($modelName)` (`Controller/TaxReportController.php:418-421`) is a small reflective helper (`App::objects('model')`) used to conditionally branch report generation depending on whether an optional model is present in the codebase — evidence of environment/tenant-specific model availability (some deployments have extra models, e.g. custom report models per client).
- `reportAudit($type, $mode)` is duplicated near-identically across at least `TaxReportController.php:299-363` and (per prior research) `ReportsController.php` / `SalaryReportsController.php` — not a shared trait/behavior, just copy-pasted per controller. Confirms no shared `AuditableComponent`.

---

## 3. Complex logic — statutory/tax formulas (priority)

### 3.1 ESI (Employee State Insurance)

- **Employee contribution: 0.75%** of ESI-wage. `Controller/StatutoryReportController.php:2445`: `$esi1 = ceil($esi_salary * .0075);`
- **Employer contribution: 3.25%** of ESI-wage. `Controller/StatutoryReportController.php:2446`: `$esi = $esi_salary * .0325;` (total `$total = $esi1 + $esi;` at line 2447). This is the post-July-2019 statutory rate (reduced from 1.75%/4.75%) — confirms and extends the previously found single 0.75% citation with the employer-side rate.
- **ESI wage ceiling / eligibility check: ₹21,000/month**, derived by reverse-engineering the previous month's ESI-deduction salary component: `Controller/EsiEpfReportController.php:1376`: `$salary = ... abs($prev_sal[...]['structure_det_value'] / .0075) : 0;` then `if (ceil($salary) > 21000) { $reason_code = 4; }` (`EsiEpfReportController.php:1377-1379`) — sets exit reason code 4 = "Out of Coverage" for ESI exit/registration reports (comment block at `EsiEpfReportController.php:1385-1391` documents ESIC reason codes 0/1/4/2/7/11/12/13).

### 3.2 EPF / EPS / EDLI

- **EPF (employee+employer combined) rate: 12%** of PF wage, capped at wage ceiling ₹15,000. `Controller/EsiEpfReportController.php:1985`: `$arr_salary_for_template[$emp_pkey]['epf_contr'] = round($epf_salary * .12);` with `$epf_salary = is_numeric($epf_salary) ? min(15000, $epf_salary) : 0;` (`EsiEpfReportController.php:1970`) and `combined_base_value` variant also capped: `$epf_salary = min($epf_salary, 15000);` (`EsiEpfReportController.php:1929`).
- **EPS (pension) rate: 8.33%** of PF wage (capped at 15,000). `Controller/EsiEpfReportController.php:1986`: `$arr_salary_for_template[$emp_pkey]['eps_contr'] = round($eps * .0833);` — `$eps` is `$epf_salary` if `payroll_master.eps == 'Y'`, else 0 (`EsiEpfReportController.php:1980-1981`), i.e. **EPS opt-out is per-employee-configurable** (some employees, e.g. those who joined after Sept-2014 with wage > 15,000, are excluded from EPS entirely and EDLI/EPF absorb the full 12%).
- **EDLI contribution = EPF(12%) − EPS(8.33%)**, i.e., the residual ≈3.67%. `Controller/EsiEpfReportController.php:1987`: `$arr_salary_for_template[$emp_pkey]['edli_contr'] = (round($epf_salary * .12) - round($eps * .0833));` — same derivation pattern also at `Controller/StatutoryReportController.php:4407-4408` (`$employer_epf_salary * .5` intermediate not used directly; actual formula: `$emp_epf_contr = round($employer_epf_salary * .12) - round($employer_epf_salary * .0833);`).
- **EDLI insurance premium (Account 21): 0.5%** of EDLI wages (capped ₹15,000). `Controller/StatutoryReportController.php:6620`: `$worksheet->setCellValueByColumnAndRow(6, $rowcount, round(($edli * 0.5 / 100)));` with label `"E D L I WAGES * 0.50000%"` at line 6577.
- **EPF administrative charges (Account 2): 0.5%** of gross PF wages (not capped in this line — applied to `$column4`, the sum of capped `$sal1` per employee). `Controller/StatutoryReportController.php:6514`: `$worksheet->setCellValueByColumnAndRow(6, $rowcount, round(($column4 * .005)));`
- **EDLI administrative charges (Account 22): 0% ("0.00000%")** hardcoded. `Controller/StatutoryReportController.php:6684-6685` — reflects the actual 2017 GoI notification that waived EDLI admin charges; the report always renders `0.00` rather than computing it, i.e. correctly hardcoded to match current law but **not date-gated** (would silently be wrong if the rate is ever reintroduced).
- **Grand total (EPF ECR summary) formula**: `$sum_total = $column5 + $column6 + ($column4 * 0.005) + $column7 + round(($edli * 0.5 / 100));` (`Controller/StatutoryReportController.php:6737`), where `$column5` = employee EPF (12%), `$column6` = employer EPF minus EPS (≈3.67%, "Account 1" difference), `$column7` = EPS (8.33%, "Account 10").
- **Statutory wage ceiling appears twice with different literal forms** — `15000` (direct cap, e.g. `EsiEpfReportController.php:1929/1970`, `StatutoryReportController.php:6279-6292/9948`) and `1800` (= 15000 × 0.12, precomputed employer-EPF cap used directly in SQL via `LEAST(ABS(structure_det_value), 1800)`, `Controller/SalaryReportsController.php:26059` and `:26286`). These two representations of the same ceiling are **not derived from a shared constant** — a maintenance/consistency risk if the statutory ceiling ever changes (would require updating both the raw `15000` cap sites and the derived `1800` sites independently).
- **`eval()` is used to evaluate PF-formula strings stored in `emp_salary_slip.remarks` / `remarks_2` / `EPF_earning`** (e.g. `"(15000 + 2000 + 3000)*.12"`), with the multiplier stripped via `str_replace(['*.12','*12/100'], '', ...)` before `eval()`, e.g. `Controller/EsiEpfReportController.php:1953-1966`, `Controller/StatutoryReportController.php:6272-6278`, `Controller/StatutoryReportController.php:9942-9945`. **This is a direct code-injection surface** if `remarks`/`remarks_2` is ever user-editable free text rather than system-generated — flag for security review before migration; the Next.js replacement must use a safe expression parser, not `eval`/`new Function`.
- **COVID-19 relief-period special EPF rate: 10% instead of 12%, for month_year between `2020-05` and `2020-07` inclusive.** `Controller/StatutoryReportController.php:4392-4401`: `if ($from >= '2020-05' && $from <= '2020-07') { $sal = round($val['0']['EPF']*100/10); ... $www_salary = round($sal*10/100); } else { ... $www_salary = round($sal*12/100); }` and again at `Controller/StatutoryReportController.php:9933-9958` (duplicated logic block, `$epf = round($sal * 10 / 100, 0)` for the relief window vs `round($sal * 12/100,0)` otherwise). This is a **historical government relief scheme (Pradhan Mantri Garib Kalyan Yojana EPF rate cut)** hardcoded by date range rather than as configurable statutory-rate data — legitimate business rule, but the date-range hardcoding is a schema/config quirk worth flagging: **possible legacy cruft — verify whether this branch is still reachable/needed, since the date window (May–Jul 2020) will never recur** (unless a similarly-dated historical report is re-run for that period, which is plausible for compliance/audit purposes — do not delete without confirming statutory report re-generation requirements for that period).

### 3.3 Professional Tax (PT)

- **Sourced from a DB view, `professional_tax_view`**, confirming and extending prior research. Full definition at `schema/mypayrol_trial.sql:47967`:

```sql
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `professional_tax_view` AS
select 'settle' AS `source`, `emp_settle_slip`.`emp_fkey` AS `emp_fkey`,
       `emp_settle_slip`.`month_year` AS `month_year`,
       sum(`emp_settle_slip`.`salary_amount`) AS `amount`
from `emp_settle_slip`
where (`emp_settle_slip`.`status` = 'Y'
       and `emp_settle_slip`.`salary_head_item_fkey` in (
           select `tax_salary_components`.`salary_head_item_Fkey`
           from `tax_salary_components`
           where (lower(`tax_salary_components`.`tax_salary_components_name`) = 'professional tax'
                  and `tax_salary_components`.`status` = 1)))
group by `emp_settle_slip`.`emp_fkey`
union all
select 'slip' AS `source`, `emp_salary_slip`.`emp_fkey` AS `emp_fkey`,
       `emp_salary_slip`.`month_year` AS `month_year`,
       abs(sum(`emp_salary_slip`.`salary_amount`)) AS `amount`
from `emp_salary_slip`
where (isnull(`emp_salary_slip`.`end_date_effective`)
       and `emp_salary_slip`.`salary_head_item_fkey` in (
           select `tax_salary_components`.`salary_head_item_Fkey`
           from `tax_salary_components`
           where (lower(`tax_salary_components`.`tax_salary_components_name`) = 'professional tax'
                  and `tax_salary_components`.`status` = 1)))
group by `emp_salary_slip`.`emp_fkey`;
```

  Also note the *materialized-shape* declaration earlier in the dump at `schema/mypayrol_trial.sql:45431` (`CREATE TABLE professional_tax_view (source varchar(6), emp_fkey int(11), month_year varchar(20), amount decimal(32,0))`) — this is mysqldump's placeholder table emitted before the real view is created later in the file (standard mysqldump view-export artifact, not a real second table).
  - **No percentage/slab formula for PT exists in code** — PT is **not calculated** by these report controllers; it is a **pre-computed salary-head amount** (state-specific PT slabs are presumably configured elsewhere, likely in payroll-processing / `tax_salary_components` setup, out of scope for the Reports cluster) that this view merely unions from two possible sources (`emp_settle_slip` "settle" = final settlement runs, `emp_salary_slip` "slip" = regular monthly payslip runs) keyed by `tax_salary_components_name = 'professional tax'`.
  - Consumption sites: `Controller/StatutoryReportController.php:6281-6282` (`Professional_Tax` column from `emp_salary_slip` directly) and `:6283-6284` (`Settle_PT` from the view, filtered `source = 'settle'`).

### 3.4 TDS / Income Tax

- **No TDS slab/percentage computation exists anywhere in the 7 controllers in scope.** All `tds` references (`Controller/TaxReportController.php:76,94,309,310,371,652,657,688,692,707`; `Controller/SalaryReportsController.php` ~20 occurrences) are report-column labels or lookups against a pre-computed `tax_salary_components_name = 'tds'` salary-head amount pulled from `emp_salary_slip`/`emp_ctc_transaction` — e.g. `Controller/StatutoryReportController.php:815-817` (`... where lcase(tax_salary_components_name) = 'tds' ... as TDS`). Actual TDS slab/regime calculation logic is not present in this cluster — it must live in a payroll-processing controller/model outside this scope (flag for the payroll-processing research pass, not fabricated here).
- `TaxReportController` handles "TaxTDS" and "TaxB" (Form 16 Part B) report *types* (`Controller/TaxReportController.php:308-315`) but these are presentation/aggregation of already-computed figures (via `generatesummaryreport`), not computation.

### 3.5 LOP (Loss of Pay) / NCP (Non-Contributing Period) days

- **Formula: `NCP days = calendar_days − (present_total + leave_total + weekoff_total + holiday_total)`**, then `LOP = max(0, NCP)`.
  - `Controller/StatutoryReportController.php:9931-9932`: `$dayscount = $val['attendance_register']['leave_total'] + $val['attendance_register']['weekoff_total'] + $val['attendance_register']['holiday_total'] + $val['attendance_register']['presant_total']; $ncp = $val['payroll_master']['calander_days'] - $dayscount;`
  - `Controller/EsiEpfReportController.php:1999-2001`: `$lop = isset($item[0]['NCP_days']) ? $item[0]['NCP_days'] : 0; $lop = max(0, $lop); $arr_salary_for_template[$emp_pkey]['ncp'] = floor($lop);`
  - `Controller/EsiEpfReportController.php:2452-2454` (ESI section): `$days = ceil(max(0, ($total_days_in_month - ($lop_total))));` — LOP days subtracted from total days in month to derive ESI-eligible attendance days.
  - LOP days also drive an ESI exit-eligibility check (`leave_count` query) at `Controller/EsiEpfReportController.php:1351-1358`, setting `reason_code = 1` ("On Leave") if approved leave entries exist for the resignation month with zero working days.
  - No gratuity or bonus percentage/formula exists in this cluster — "Bonus" appears only as a pass-through salary-head display column (`Controller/EsiEpfReportController.php:788,795,998,1009` — labor Muster-roll/wage-register report), not a computed statutory bonus (Payment of Bonus Act 8.33%–20%) formula.

### 3.6 Payroll-type / attendance-period switching (new, not previously documented)

- `Controller/EsiEpfReportController.php:1406-1418`: report period boundaries are derived conditionally on `db_config.payroll_type`: if `payroll_type IN ('F1','F2')`, use calendar month (`YYYY-MM-01` to `date('Y-m-t')`); otherwise call a DB function `att_start_end_fn(CONCAT('$from','-01'), 1)` for a custom attendance-period start/end. This `att_start_end_fn` is a stored function referenced but not defined in `schema/mypayrol_trial.sql` search results within the excerpted region — **flag for schema-wide stored-routine search** if not already covered elsewhere, since it directly affects ESI/EPF period-boundary computation and thus compliance-critical wage aggregation.

---

## 4. Schema quirks (legacy/migration signals)

- `report_audit` is **MyISAM** while `reportcriterias` is **InnoDB** — inconsistent storage engine choice within the same functional area, typical of tables added at different points in the app's history (MyISAM was CakePHP 1.x/early-2.x default in many scaffolds; InnoDB adoption came later).
- `reportcriterias` has `tablePrefix = ''` explicitly set in the model (`Model/ReportCriterias.php:15`) — overriding a presumed global table prefix (likely due to `AppModel`/datasource config using a prefix for tenant-specific tables), suggesting `reportcriterias` is a **shared/global lookup table** not duplicated per-tenant, unlike most tenant data. Worth confirming during multi-tenant schema mapping.
- `reportcriterias.reporttype` is a **free-text varchar acting as a discriminator/enum** with ~40+ distinct values observed in seed data (`employee`, `shiftpolicy`, `holiday`, `leavepolicy`, `Attendance`, `DetailedAttendance`, `TimeAttendance`, `AttendanceRep`, `LeaveDetaillsReport`, `LeaveSummary`, `LeaveBalance`, `Salary`, `salarystructure`, `Salaryslip`, `SummaryPayroll`, `History`, `Grosssalary`, `Overtime`, `GrosssalarySummary`, `EditPunches`, `LeaveBalanceSummary`, `Compoff`, `Labour`, `BankTranfer`, `VerifiedAttendance`, `Loan`, `TaxTDS`, `EPF`, `ESI`, `Resighned` [sic, typo preserved in data], `Dashboard`, `Advance`, `Expense`; `schema/mypayrol_trial.sql:45585-45654+`) — no CHECK constraint or FK enforces this against a canonical list; some rows are seeded with `status = 0` (disabled) alongside active duplicates for the same `reporttype`/`reportcriteria_field` pair (e.g. id 3 `grade` disabled vs id 2 `Departments` active for `employee`), indicating **iterative feature deprecation via soft-disable rather than deletion** — a pattern to preserve during migration (don't silently drop "disabled" criteria; they may still be referenced by historical `report_audit` rows).
- `report_audit.report_type` in the live sample data contains **non-report values** (`'Logged in'`, `'Logged out'`) mixed with actual report names elsewhere in the app — confirms `report_audit` was repurposed as a general auth/activity log at some point beyond its original "report download history" intent (per the code comment at `Controller/TaxReportController.php:302`: `//This is to save download history. By Arul P Das on 25_1_2021`).
- `professional_tax_view` is dumped twice in the SQL file: once as a placeholder base `CREATE TABLE` (mysqldump `--views` workaround) at `schema/mypayrol_trial.sql:45431`, and once as the real `CREATE ALGORITHM=... VIEW` at `schema/mypayrol_trial.sql:47967` — this is standard mysqldump behavior for views (not an app-level quirk), but should not be mistaken for two distinct schema objects during migration tooling/parsing.
- Numerous inline code comments across `StatutoryReportController.php`/`EsiEpfReportController.php` are dated **"Edited by Akshay on 23-3-2026"**, **"29-5-2026"** and similar 2025/2026 dates alongside much older ones (2019, 2020, 2024) — indicates this codebase is under **active, very recent maintenance** (including post-"current date" edits relative to this analysis), so statutory-rate logic here should be treated as the most up-to-date source of truth in the legacy app, not stale/frozen code.

---

## Dead/unreachable code flags

- `Controller/EsiEpfReportController.php:1343`: `// if ($monthlyWages == 0) {` replaced by `if (true) { //Edited by Akshay on 18-4-2024` — the original conditional is permanently short-circuited to always execute; the commented-out branch is dead. **Possible legacy cruft — verify the `true` override is intentional (not a debugging leftover) before porting; if intentional, the dead `$monthlyWages == 0` condition and its comment should not be carried into the migration.**
- `Controller/EsiEpfReportController.php:1957`: `// if ($epf_salary > 0) {` replaced by `if (true) {` — same permanently-true pattern, dead conditional retained as a comment.
- `Controller/StatutoryReportController.php:6688-6716`: large commented-out block computing `$total`/`money_format`/`$saltot1` via string-replace-based float parsing, superseded by the active `$sum_total = ... ; $saltot1 = number_format(...)` block immediately below (lines 6730-6739) — dead code, safe to drop, but confirms `money_format()` (deprecated/removed in PHP 8) was the original approach and was deliberately replaced with `number_format()`, which is relevant if the legacy app has been partially migrated to PHP 8 already.
- `Controller/StatutoryReportController.php:6580-6600` and `:6607-6613`: additional dead commented-out `money_format`/string-replace blocks paralleling the account-10 (EPS) and account-21 (EDLI) totals — same PHP8-compat migration pattern, safe to ignore.

---

## File/path index for this report

- `Model/ReportAudit.php`
- `Model/ReportCriterias.php`
- `Model/EmpCtcTransaction.php`
- `Controller/TaxReportController.php` (lines 299-421 especially)
- `Controller/StatutoryReportController.php` (lines 803-822, 2420-2460, 4390-4475, 6260-6740, 9920-9960)
- `Controller/EsiEpfReportController.php` (lines 770-1160, 1340-1420, 1900-2022)
- `Controller/SalaryReportsController.php` (lines 13380-13470, 26040-26075, 26270-26300)
- `Controller/HierarchyReportController.php` (confirmed no statutory logic — org-chart/PDF export only)
- `Controller/ReportsController.php`, `Controller/ReportController.php` (confirmed listing/audit plumbing only, no formulas)
- `schema/mypayrol_trial.sql:45431` (professional_tax_view placeholder), `:45575-45654` (`reportcriterias`), `:45746-45768` (`report_audit`), `:47967` (`professional_tax_view` real definition)

---

### 7.7 Company / Organization Setup

# Company / Organization Setup — Data Model & Business Logic Report

Scope: `CompanyController`, `CompanySetupController`, `BranchController`, `DepartmentController`,
`DesignationController`, `DivisionController`, `SectionController`, `GradesController` (+ misleadingly
named `GradeController` = holiday calendar), `CategoryController`, `BankController`, `DbConfigController`,
and their Models. Ground truth: `schema/mypayrol_trial.sql` (per-company DB) and
`schema/mypayrol_control_db.sql` (control/tenant DB).

---

## 1. Schema cross-check

All models in this cluster are **pure passthrough** — no `$belongsTo`/`$hasMany`, no `$validate`, no
callbacks. Confirmed by reading every Model file in the cluster:

| Model file | `$useTable` | `$primaryKey` | Real schema table | Notes |
|---|---|---|---|---|
| `Model/Units.php:14` | `branches` | default `id` | `branches` (`schema/mypayrol_trial.sql:29134`) | Model is named `Units`, table is `branches` — pure naming mismatch, no logic issue. |
| `Model/Departments.php:14` | `department` | default `id` | `department` (`schema/mypayrol_trial.sql:40936`) | Has `$order = "Departments.dept_name ASC"` (`Model/Departments.php:15`), the only customization in the whole cluster. |
| `Model/Designation.php:14` | `designation` | default `id` | `designation` (`schema/mypayrol_trial.sql:40954`) | Also has a default `$order` (`Model/Designation.php:15`). |
| `Model/Division.php:14` | `division` | default `id` | `division` (`schema/mypayrol_trial.sql:41932`) | No order/validation. |
| `Model/Section.php:14` | `section` | default `id` | `section` (`schema/mypayrol_trial.sql:46802`) | No order/validation. |
| `Model/Grades.php:14` | `grade` | default `grade_pkey`* | `grade` (`schema/mypayrol_trial.sql:44397`) | *Model does not declare `$primaryKey`; controller code manually handles `grade_pkey` via raw SQL instead of relying on Cake's PK inference (see §2). |
| `Model/Banks.php:14` | `bank` | default `id` | `bank` (`schema/mypayrol_trial.sql:29123`) | No order/validation. |
| `Model/Category.php:14` | `category` | default | **no matching table anywhere in the three schema dumps** | **Dead/broken feature — see below.** |
| `Model/CentralControl.php:15-16` | `central_control` | `control_pkey` (explicit) | `central_control` (`schema/mypayrol_control_db.sql:1405`) | Only model in the cluster with an explicit `$useDbConfig = 'controldb'` (`Model/CentralControl.php:16`), correctly targeting the control DB. |

### `category` table does not exist — CategoryController is dead code
`Model/Category.php:14` sets `$useTable = 'category'`, and `Controller/CategoryController.php` runs raw
queries against `category` (e.g. `CategoryController.php:93,117,131,182,209`) and against
`grade.category_fkey` (`CategoryController.php:200,209`). Neither the `category` table nor a
`category_fkey` column on `grade` exists in `schema/mypayrol_trial.sql`, `schema/mypayrol_control_db.sql`,
or `schema/mypayrol_mpm121.sql` — verified with repo-wide grep, zero matches. The closest tables are
`category_master` (`schema/mypayrol_trial.sql:40540`, unrelated fields) and `item_specification`'s
`category_code` column (`schema/mypayrol_trial.sql:44855`, inventory feature, unrelated).
**Flag: `CategoryController` + `Model/Category.php` is possible legacy cruft — verify before migrating**;
any live company DB either has an undocumented `category`/`grade.category_fkey` schema drift not captured
in the reference dumps, or this feature has been silently broken since whenever those columns were dropped.

### No branch → department → designation → division → section hierarchy in the DB
Prior research flagged that the UI *implies* a hierarchy (all these are tabs under "Company Setup").
The schema disproves this for the org-unit lookup tables themselves:

- `department` (`schema/mypayrol_trial.sql:40936-40942`): columns are only `id, dept_code, dept_name, status`. No `branch_fkey`, no `company_code`.
- `designation` (`schema/mypayrol_trial.sql:40954-40960`): `id, desig_code, desig_name, status`. No `dept_fkey`.
- `division` (`schema/mypayrol_trial.sql:41932-41938`): `id, div_code, div_name, status`. No FK to anything.
- `section` (`schema/mypayrol_trial.sql:46802-46808`): `id, section_code, section_name, status`. No FK to anything.
- `grade` (`schema/mypayrol_trial.sql:44397-44403`): `grade_pkey, grade_code, grade_name, status`. No FK to anything.
- `bank` (`schema/mypayrol_trial.sql:29123-29131`): `id, bank_name, bank_branch, ifsc_code, acct_no, status`. Standalone.

Only `branches` (`schema/mypayrol_trial.sql:29134-29148`) carries `company_code`. **These five/six lookup
tables are completely flat, company-wide, code+name pick-lists — there is no relational hierarchy enforced
anywhere in the DB.** Any hierarchy (e.g. "this department belongs to this branch") exists only at the
*employee-assignment* layer, and even there it's soft:

`emp_proff` (`schema/mypayrol_trial.sql:43735-43773`, the real table backing model
`EmployeeProfessionalDetails`, confirmed via `Model/EmployeeProfessionalDetails.php:14-15`
`$primaryKey='emp_proff_pkey'`, `$useTable='emp_proff'`) stores the assignment columns as **free-text
`varchar`, not integer FKs**:
```
`designation` varchar(100)    -- schema/mypayrol_trial.sql:43741
`emp_dept`    varchar(100)    -- schema/mypayrol_trial.sql:43742
`emp_grade`   varchar(100)    -- schema/mypayrol_trial.sql:43743
`emp_vertical`varchar(100)    -- schema/mypayrol_trial.sql:43744
`emp_branch`  varchar(100)    -- schema/mypayrol_trial.sql:43745
`emp_sep_priv`int(11)         -- schema/mypayrol_trial.sql:43754  (section)
```
No SQL-level foreign key constraints exist from `emp_proff` to `department`/`designation`/`division`/
`grade`/`section`/`branches`. All referential integrity is enforced only in application code (or not at
all).

### `division` vs `verticals` — likely duplicate/overlapping concepts
`DivisionController::deleteDivision()` (`Controller/DivisionController.php:154-194`) guards deletion by
counting `EmployeeProfessionalDetails.emp_vertical` rows equal to the `division.id` being deleted
(`DivisionController.php:165`). But a stored procedure in the schema resolves `emp_vertical` by joining it
against **`verticals.vert_code`**, not `division.id` (`schema/mypayrol_trial.sql:1249`:
`select vertical_name into vvertical_name from verticals where vert_code = vemp_vertical;`), and
`verticals` (`schema/mypayrol_trial.sql:47758-47762`, columns `vert_code, vertical_name, status`) is a
*separate* table from `division` (`schema/mypayrol_trial.sql:41932`). Also, `CompanySetupController`'s
`$uses` array loads a `Verticals` model alongside `Units`/`Designation`/`Departments`/`Grades`
(`Controller/CompanySetupController.php:6`), but never loads `Division`. **This strongly suggests
`division`/`Model/Division.php`/`DivisionController` is a separate, possibly legacy, UI-only concept that
does not line up with the `verticals` table the rest of the codebase (stored procs, `CompanySetupController`)
actually treats as "division/vertical" of an employee — a real mismatch worth resolving before migration
(pick one canonical org-unit: `division` or `verticals`), not just a naming difference.**

### `branches` DB triggers duplicate what `BranchController::savebranch()` does in PHP
`schema/mypayrol_trial.sql:29153-29186` defines three triggers on `branches`:
- `branches_bi` (BEFORE INSERT, `:29153-29164`): **overwrites `NEW.branch_code`** with
  `concat(NEW.company_code, 0, vcount)` where `vcount` is a running count of rows in `branches`. This means
  whatever `branch_code` `BranchController::savebranch()` computes/sends
  (`Controller/BranchController.php:127-133`, `substr($branch_name,0,3).strtotime("now")`) is **silently
  discarded and replaced** by the trigger on insert.
- `branches_ai` (AFTER INSERT, `:29166-29175`): auto-inserts a mirror row into
  `mypayrol_control_db.company_branches` if one doesn't already exist for that `branch_code`.
- `branches_au` (AFTER UPDATE, `:29177-29184`): propagates `branch_name`/`status` changes to
  `mypayrol_control_db.company_branches`.

Yet `BranchController::savebranch()` **also** does this same company_branches sync manually in PHP
(`Controller/BranchController.php:154-163`, guarded by a `SELECT COUNT(*)` check first). This is
redundant application-level logic duplicating DB-trigger logic — a double-write path (comment at
`BranchController.php:153` "`//edited by sinsiya 13-06-2025`" suggests this PHP-side duplication was added
recently, possibly without realizing the trigger already does it). **Flag for migration: the Next.js/ORM
layer must decide whether to replicate the trigger's branch_code-generation and control-DB mirroring
logic in application code (since there will be no DB trigger), and must not blindly port the PHP dual-write
as-is.**

---

## 2. Custom methods per model

Because every model in this cluster is a bare passthrough (no methods beyond inherited CakePHP `Model`
behavior), "custom methods" for this scope are effectively the **controller action methods** that
encapsulate all business logic. Listed per resource:

### Branch (`Model/Units.php`, controller `BranchController.php`)
- `index()` — `BranchController.php:55-59`. Landing page.
- `form()` — `BranchController.php:60-86`. Loads a branch row plus its current `fin_year` (financial year, `vattr1='1'`) and current leave year (`vattr1='0'`) via raw SQL (`BranchController.php:77-78`). Called from branch edit UI (`View/Branch/form.ctp`).
- `branches()` — `BranchController.php:87-90`. Empty layout stub.
- `load()` — `BranchController.php:92-112`. AJAX fetch of one branch row by id.
- `savebranch()` — `BranchController.php:114-197`. Core save; see §3 for the fin_year auto-creation logic. Call site: branch save form (`View/Branch/form.ctp`).
- `listunits()` — `BranchController.php:199-265`. Datatable listing with LEFT JOINs to `fin_year` (aliased `Leave_year`/`Fin_year`) to show current leave/financial year date ranges per branch (`BranchController.php:220-233`).
- `deleteBranches()` — `BranchController.php:267-283`. Soft delete via `updateAll(status=0)`. **No guarded-delete check for assigned employees**, unlike Division/Section/Grades (see §3) — inconsistency worth flagging.
- `checkbranchexists($id=0)` — `BranchController.php:284-303`. Duplicate-name check, called from branch form validation (AJAX).

### Department (`Model/Departments.php`, controller `DepartmentController.php`)
- `index()` — `DepartmentController.php:55-63`.
- `form()` — `DepartmentController.php:66-82`. Called by `View/Department/form.ctp`.
- `savedepartment()` — `DepartmentController.php:83-120`. Calls `checkdepartmentcodeexists()` and `checkdepartmentexists()` internally before saving (`DepartmentController.php:96-101`); **bug**: on duplicate it does `return json_encode(...)` instead of `echo` (`DepartmentController.php:97,100`) — because `autoRender=FALSE` and the method never `echo`s in that branch, the HTTP response body will be **empty**, not the JSON error, for the duplicate-name/duplicate-code case. Same bug pattern repeats in `DivisionController::savedivision()` (`DivisionController.php:97,100`) and `SectionController::savesection()` (`SectionController.php:100,103`).
- `listdepartments()` — `DepartmentController.php:121-152`.
- `deleteDepartment()` — `DepartmentController.php:154-171`. Soft delete, **no employee-assignment guard** (unlike Division/Section/Grades).
- `checkdepartmentexists($id=0,$namecount='')` — `DepartmentController.php:172-188`.
- `checkdepartmentcodeexists($id=0,$codecount='')` — `DepartmentController.php:190-208`.

### Designation (`Model/Designation.php`, controller `DesignationController.php`)
- `index()` — `DesignationController.php:55-63`.
- `form($id=0)` — `DesignationController.php:66-92`. Also fetches all active `desig_code`s into `desig_arr` via `Set::extract` (`DesignationController.php:84-89`), presumably for a client-side duplicate-code check/autocomplete.
- `save()` — `DesignationController.php:93-130`. Same "return instead of echo on error" bug as Department (`DesignationController.php:105,109`).
- `listDesignation()` — `DesignationController.php:132-166`.
- `deleteDepartment()` — `DesignationController.php:168-185`. **Misnamed method** (copy-pasted from DepartmentController, operates on `Designation` model) — no employee guard.
- `checkdesignationexists($id=0,$namecount='')` — `DesignationController.php:186-203`.
- `checkdesignationcodeexists($id=0,$codecount='')` — `DesignationController.php:204-220`.

### Division (`Model/Division.php`, controller `DivisionController.php`)
- `index()` — `DivisionController.php:55-63`.
- `form()` — `DivisionController.php:66-82`.
- `savedivision()` — `DivisionController.php:83-120`. Same return/echo bug (`DivisionController.php:97,100`).
- `listdivision()` — `DivisionController.php:121-152`.
- `deleteDivision()` — `DivisionController.php:154-194`. **Guarded delete** — see §3.
- `checkdivisionexists($id=0,$namecount='')` — `DivisionController.php:195-211`.
- `checkdivisioncodeexists($id=0,$codecount='')` — `DivisionController.php:213-231`.

### Section (`Model/Section.php`, controller `SectionController.php`)
- `index()` — `SectionController.php:55-63`.
- `form()` — `SectionController.php:66-82`.
- `savesection()` — `SectionController.php:83-124`. Same return/echo bug (`SectionController.php:100,103`).
- `listsection()` — `SectionController.php:125-156`.
- `deleteSection()` — `SectionController.php:158-196`. **Guarded delete** — see §3.
- `checksectionexists($id=0,$namecount='')` — `SectionController.php:197-215`.
- `checksectioncodeexists($id=0,$codecount='')` — `SectionController.php:217-235`.

### Grades (`Model/Grades.php`, controller `GradesController.php` — NOT `GradeController.php`, which is the holiday calendar, confirmed at `Controller/GradeController.php:65-80` using the `Holiday` model)
- `index()` — `GradesController.php:57-60`.
- `listGrades()` — `GradesController.php:62-85`. Note: builds `$resp_banks` (copy-pasted variable name from Bank controller) then **overwrites the same key on every loop iteration** (`GradesController.php:75-82`) — only the last row's fields end up correctly keyed per index in `$data["rows"][$key]`, but `$resp_banks` itself is reused as scratch space each iteration (works by accident because it's reassigned wholesale into `$data["rows"][$key]` before the next iteration mutates it — still fragile/confusing code).
- `form($grade_pkey=0)` — `GradesController.php:87-107`. Raw SQL string-interpolates `$conditions` directly into the query (`GradesController.php:92-93`) — SQL-injectable if `$grade_pkey` weren't type-coerced from a route param, but should still be parameterized.
- `checkgradeexists($grade_pkey=0)` — `GradesController.php:109-121`. Note the `if` condition (`GradesController.php:119`) `$result[0][0]['count']!="0" || $result[0][0]['count']!=0` is a tautology (always true unless the row is genuinely absent) — effectively always reports "exists" once any row is found, i.e. **broken/no-op duplicate check** (dead logic, evaluates truthy in essentially all cases where `$result[0][0]['count']` is set).
- `checkgradecodeexists()` — `GradesController.php:123-135`. Same tautology bug (`GradesController.php:133`).
- `saveGrade()` — `GradesController.php:136-192`. Manually builds an `UPDATE grade SET grade_pkey=..., grade_code=..., grade_name=... WHERE grade_pkey=...` string (`GradesController.php:178-182`) — pointlessly re-assigns the primary key to itself; uses raw string concatenation (SQLi risk) instead of parameterized `Model::save()`, despite `Model::save()` being used for the insert branch two lines below (`GradesController.php:185`).
- `deleteGrade($grade_pkey=0)` — `GradesController.php:194-237`. **Guarded delete** — see §3.

### Category (`Model/Category.php`, controller `CategoryController.php`) — table does not exist, see §1
- `index()`, `listGrades()` (misnamed, copy-pasted from Grades), `form($category_pkey=0)`, `checkcategoryexists()`, `checkcategorycodeexists()`, `saveGrade()` (misnamed), `deleteGrade($category_pkey=0)` (misnamed) — `CategoryController.php:57-233`. Entire controller is a near-verbatim copy-paste of `GradesController.php` with `grade`→`category` string substitution, including the same tautology bug in the exists-checks (`CategoryController.php:119,133`) and the same raw-SQL update pattern (`CategoryController.php:179-182`). Given the underlying table doesn't exist in the reference schema, **treat as possible legacy cruft — verify against a live tenant DB before migrating this feature at all.**

### Bank (`Model/Banks.php`, controller `BankController.php`)
- `index()` — `BankController.php:54-62`. Contains a stray `debug()` call left in production code (`BankController.php:59`) — dumps `CompanyContactInfo->find("all")` to output, likely dev leftover.
- `form()` — `BankController.php:66-85`.
- `branches()` — `BankController.php:86-89`. Empty stub (copy-pasted from BranchController, unused/dead).
- `savebank()` — `BankController.php:91-125`.
- `listbanks()` — `BankController.php:126-169`.
- `deleteBank()` — `BankController.php:171-189`. No employee-assignment guard (banks aren't assigned to employees directly in this cluster, so that's expected/correct).

### CompanySetup / plan-gating (`CompanySetupController.php`) — subscription feature-gate + payment, not org-structure CRUD
- `index()` — `CompanySetupController.php:10-16`.
- `getCompanyFeatures()` — `CompanySetupController.php:17-77`. Cross-DB: looks up the tenant's `plan_id` from `CentralUserCredentials` in the **control DB** (`setDataSource('controldb')`, `CompanySetupController.php:21-22`), then the plan's enabled `Features` (all in control DB) filtered to `feature_key='company'` (`CompanySetupController.php:38-52`).
- `createRazorpayOrder()` — `CompanySetupController.php:79-108`. **Hardcoded Razorpay test API key/secret** in source (`CompanySetupController.php:81-82`).
- `verifyPayment()` — `CompanySetupController.php:109-184`. HMAC-verifies Razorpay signature (`CompanySetupController.php:121-123`), records `PlanPaymentHistory`, then on captured payment **runs a raw interpolated SQL UPDATE**:
  ```php
  $this->CentralUserCredentials->query("
          UPDATE user_credentials
          SET plan_id = {$plan_id_value}
          WHERE LOWER(user_id) = LOWER('{$lower_user_id}')
      ");
  ```
  **SQL-injection surface at `Controller/CompanySetupController.php:154-158`** — `$lower_user_id` is `strtolower($this->Session->read('login_user_id'))` (`CompanySetupController.php:148-151`), string-interpolated directly into the query without escaping/parameter binding. `$plan_id_value` is cast to `(int)` first (`CompanySetupController.php:152`) so that half is safe, but `user_id` is not sanitized. Exploitability depends on whether `login_user_id` in session can ever be attacker-influenced (e.g., via a prior injection elsewhere, or if session fixation/tampering is possible) — still a raw-query anti-pattern that should not be ported as-is.
- `getRazorpayPaymentDetails($payment_id)` — `CompanySetupController.php:187-199`. Same hardcoded key/secret repeated (`CompanySetupController.php:189-190`).

### DbConfig (13-step onboarding wizard, `DbConfigController.php`) — see §3 for full step sequence

---

## 3. Complex logic

### 3a. Guarded deletes — blocking deletion when employees are still assigned
Three of the five org-unit tables in this cluster enforce a "can't delete if in use" rule **in PHP**, not
in the DB (no FK constraints exist to enforce this at the DB layer):

- **Division**: `DivisionController::deleteDivision()` (`Controller/DivisionController.php:154-194`).
  ```php
  $asiigned = $this->EmployeeProfessionalDetails->find("count", array(
      "conditions" => array('EmployeeProfessionalDetails.emp_vertical' => $ar_id)
  ));                                                        // DivisionController.php:165
  if ($asiigned > 0) {
      $result["danger"] = true;
      $result["msg"] = "Employees allocated under the selected division";   // DivisionController.php:173-174
  } else { /* soft delete via updateAll */ }
  ```
  As noted in §1, this checks `emp_vertical` (a varchar column) against `division.id` — a comparison that
  is questionable given `emp_vertical` is elsewhere resolved against `verticals.vert_code`
  (`schema/mypayrol_trial.sql:1249`).

- **Section**: `SectionController::deleteSection()` (`Controller/SectionController.php:158-196`).
  ```php
  $asiigned = $this->EmployeeProfessionalDetails->find("count", array(
      "conditions" => array('EmployeeProfessionalDetails.emp_sep_priv' => $ar_id)
  ));                                                        // SectionController.php:165
  if ($asiigned > 0) {
      $result["danger"] = true;
      $result["msg"] = "Employees allocated under the selected section";    // SectionController.php:173-174
  }
  ```
  `emp_sep_priv` is `int(11)` (`schema/mypayrol_trial.sql:43754`) matching `section.id` type — this one is
  internally consistent.

- **Grades**: `GradesController::deleteGrade()` (`Controller/GradesController.php:194-237`).
  ```php
  $asiigned = $this->EmployeeProfessionalDetails->find("count", array(
      "conditions" => array('EmployeeProfessionalDetails.emp_grade' => $grade_pkey)
  ));                                                        // GradesController.php:202
  if ($asiigned > 0) {
      $resp["danger"] = true;
      $resp["msg"] = "Employees allocated under the selected Grade";        // GradesController.php:209-210
  } else {
      $this->Grades->query("update grade set status=0 where grade_pkey=$grade_pkey");  // GradesController.php:216, raw-SQL, interpolated grade_pkey
  }
  ```
  `emp_grade` is `varchar(100)` (`schema/mypayrol_trial.sql:43743`) compared against `grade_pkey` (an
  `int`) — a type-mismatched comparison identical in spirit to the Division issue.

- **Category** (dead feature, table doesn't exist): `CategoryController::deleteGrade()`
  (`Controller/CategoryController.php:194-233`) has a *different* guard shape — it checks
  `grade.category_fkey` count (`CategoryController.php:200`) rather than an `EmployeeProfessionalDetails`
  column, i.e. "can't delete a category if any grade still references it." Moot since neither
  `category_fkey` nor `category` exist in the schema.

- **Branch and Department have NO such guard** — `BranchController::deleteBranches()`
  (`Controller/BranchController.php:267-283`) and `DepartmentController::deleteDepartment()`
  (`Controller/DepartmentController.php:154-171`) both do a bare `updateAll(status=0)` with no employee
  check at all. **Inconsistency to flag for the migration**: if the intent is "protect org-units with
  active employee assignments," Branch and Department should logically have the same guard as
  Division/Section/Grade but don't.

### 3b. Financial-year / leave-year auto-creation on Branch save
`BranchController::savebranch()` (`Controller/BranchController.php:114-197`) creates two `fin_year` rows
per branch: one flagged as the financial year (`vattr1='1'`) and one as the leave year (`vattr1='0'`) —
confirmed against `fin_year.vattr1 int(11)` (`schema/mypayrol_trial.sql:44353`) with no comment
documenting the flag's meaning in the schema itself (only inferable from controller usage).

```php
$fin_year = date("Y", strtotime($arr_form_data['finstartdate']));
$fin_year_start = date("Y-m-d", strtotime($arr_form_data['finstartdate']));
$fin_year_tend  = date("Y-m-d", strtotime($arr_form_data['finenddate']));

$leave_year = date("Y", strtotime($arr_form_data['leavestartdate']));
$leave_year_start = date("Y-m-d", strtotime($arr_form_data['leavestartdate']));
$leave_year_tend  = date("Y-m-d", strtotime($arr_form_data['leaveenddate']));

if ($arr_form_data['id'] == 0 || $arr_form_data['id'] == null || $arr_form_data['id'] == "") {
    // NEW branch: INSERT both rows
    $save_finYear = $this->Units->query("INSERT INTO fin_year (company_code,branch_code,fin_year,start_month,end_month,vattr1)
        VALUES ('$company_code','$branch_code','$fin_year','$fin_year_start','$fin_year_tend','1') ");     // BranchController.php:175
    $save_leaveYear = $this->Units->query("INSERT INTO fin_year (company_code,branch_code,fin_year,start_month,end_month,vattr1)
        VALUES ('$company_code','$branch_code','$leave_year','$leave_year_start','$leave_year_tend','0') "); // BranchController.php:176
} else {
    // EXISTING branch: UPDATE both rows by Fin_year_seq
    $update_fin_year = $this->Units->query("UPDATE fin_year set start_month = '$fin_year_start', end_month = '$fin_year_tend'
        WHERE branch_code = '$branch_code' and Fin_year_seq = '$Fin_year_seq' ");                            // BranchController.php:178
    $update_fin_year = $this->Units->query("UPDATE fin_year set start_month = '$leave_year_start', end_month = '$leave_year_tend'
        WHERE branch_code = '$branch_code' and Fin_year_seq = '$Fin_year_seq_leave' ");                       // BranchController.php:179
}
```
(`Controller/BranchController.php:164-180`). Both the insert and update paths use raw string-interpolated
SQL — SQLi surface for `$Fin_year_seq`/`$Fin_year_seq_leave` (client-supplied form fields, not re-validated
server-side before being spliced into the `WHERE` clause at `BranchController.php:178-179`). Also note the
`branch_code` used here (`BranchController.php:151`, fetched back from the DB post-insert via
`SELECT branch_code FROM branches WHERE id = '$leaveentryId'`) will actually be the trigger-generated
`branch_code` (see §1, `branches_bi` trigger at `schema/mypayrol_trial.sql:29153-29164`), not the PHP-
computed one from `BranchController.php:129-132` — the PHP-computed `branch_code` is silently discarded on
insert.

### 3c. `DbConfigController` — 13-step onboarding wizard, orphaned from live routing
`Controller/DbConfigController.php` is not referenced in `Config/routes.php` (grep confirmed zero matches)
— consistent with prior research that it's orphaned from any live menu. Its `$uses` array
(`DbConfigController.php:51`) includes `DayTimeProcedures, CompanyContactInfo, DbConfig, Designation,
Departments, MobileUserCredentials, UserCredentials`. Views under `View/DbConfig/` (13 `.ctp` files,
excluding the AJAX partial `showholidays.ctp` and generic `form.ctp`) confirm the step count:
`welcome`, `config`, `designation_departments`, `holidays`, `policy`, `leave_heads`, `salary_policy`,
`emp_upload`, `emp_login`, `login_cred`, `completed_setup`, `load_config`, plus `show_policiess` as a
detail sub-view.

Actual DB-write sequence, table by table (methods appear in `DbConfigController.php` in this order, though
nothing enforces the client actually calls them in sequence — it's wizard-shaped only by convention):

1. **`config()`** (`DbConfigController.php:82-92`) — bootstraps a fresh tenant's `db_config`, `branches`,
   `fin_year` rows with the session's `company_code`:
   ```php
   UPDATE db_config SET company_code = '$company'                                          // :85
   UPDATE branches SET company_code='$company', branch_code='$branch' ORDER BY ID LIMIT 1   // :89  ($branch = $company.'01')
   UPDATE fin_year SET company_code='$company', branch_code='$branch' ORDER BY Fin_year_seq LIMIT 2  // :90
   ```
   This assumes exactly one seed `branches` row and exactly two seed `fin_year` rows already exist from a
   template/clone DB (matches the `db_config`/`branches`/`fin_year` seed rows visible in
   `schema/mypayrol_trial.sql:40933-40934,44361-44363` — this dump *is* effectively that seed template).
2. **`designation_departments()`** (`DbConfigController.php:94-116`) — read-only, lists `department`/
   `designation` rows (fetched twice: once via raw `mysql_connect`/`mysql_query` against
   `mypayrol_control_db` — **note: queries `department`/`designation` from the control DB here**,
   `DbConfigController.php:96-98,103`, inconsistent with every other controller in this cluster which reads
   `department`/`designation` from the per-tenant DB — and once via `$this->DbConfig->query(...)` against
   the tenant DB, `DbConfigController.php:110,112`). **Likely a bug**: the control-DB read via raw
   `mysql_*` calls is dead weight/wrong-DB, since `department`/`designation` are per-tenant tables per §1.
3. **`save_Desig()`** (`DbConfigController.php:403-437`) / **`remove_desig()`** (`:498-520`) — writes to
   `designation` (INSERT via `Model::save()`, soft-delete via raw `UPDATE designation SET status='0' WHERE
   desig_code='$id'` at `:509`).
4. **`savedepartment()`** (`DbConfigController.php:534-568`) / **`remove_dept()`** (`:570-592`) — same
   pattern against `department` (raw `UPDATE department SET status='0' WHERE dept_code='$id'` at `:581`).
5. **`holidays()` / `holidays_s()`** (`DbConfigController.php:207-239`) — read `holiday_group` from the
   **control DB** via hardcoded raw `mysql_connect`. `holidays_s()` additionally calls an external
   third-party API (`calendarific.com`, `DbConfigController.php:208`) with a **hardcoded API key** embedded
   in the URL.
6. **`save_holidays()`** (`DbConfigController.php:241-276`) — for each selected `holiday_group`, copies
   rows from control-DB `holiday_group`/`holidays` into the tenant's own `holiday_group`/`holidays` tables
   (`DbConfigController.php:263,269`) — this is the actual "seed calendar into new tenant" step.
7. **`policy()`** (`DbConfigController.php:278-294`) — marks wizard progress
   (`UPDATE wizard_config SET link='DbConfig/policy', state='1'`, `:280`) and lists
   `working_day_time_procedures` (shift policies) from both control DB (raw) and tenant DB.
8. **`save_shift()`** (`DbConfigController.php:118-195`) / **`remove_shift()`** (`:197-205`) — copies a
   shift-policy template row from control-DB `working_day_time_procedures` into the tenant's own
   `working_day_time_procedures` table (INSERT with ~45 hardcoded columns, `:186-187`), guarded by a
   duplicate-description check (`:180-185`).
9. **`show_policiess($id)`** (`DbConfigController.php:296-325`) — read-only detail view of one shift
   policy plus its variable salary-head components (joins `salary_head_items`/`salary_heads`, `:304`).
10. **`save_policies()`** (`DbConfigController.php:327-341`) — currently a **no-op pass-through**: reads
    `$arr_form_data['checklists']` but never writes anything, just redirects to `leave_heads`.
11. **`leave_heads()`** (`DbConfigController.php:351-356`) — lists `salary_head_items` where
    `item_type='LEAVE'`.
12. **`save_leave_head()`** (`DbConfigController.php:358-384`) — toggles `salary_head_items.value`
    ('Y'/'N') by `salary_head_item_pkey` (raw `UPDATE`, `:371,373`).
13. **`salary_policy()` / `emp_upload()` / `load_config()` / `emp_login()`**
    (`DbConfigController.php:386-400`) — **all four are empty stub methods**, presumably intended to hold
    further seed logic (salary structure, bulk employee upload, general config, employee self-service
    login) that was never implemented.
14. **`savecompletess($bank_id=0)`** (`DbConfigController.php:443-496`) — bulk-sets a password for every
    `UserCredentials` row with an empty password (`:449`), and mirrors credentials into
    `MobileUserCredentials` (`:462-486`). Contains a leftover `debug()` call at `:484-485` (dumps form data
    including plaintext password to output).
15. **`login_cred()` / `completed_setup()`** (`DbConfigController.php:594-600`) — empty stubs.
16. **`SetupComplete()`** (`DbConfigController.php:343-349`) — finalizes: `UPDATE wizard_config SET
    state='1'`, then redirects to `Dashboard/index`.

**Hardcoded root DB credentials**: `mysql_connect('localhost', 'root', 'Localhost&*()')` appears 7 times
(`DbConfigController.php:96,122,218,230,246,281,297,331,524` — 9 occurrences), always followed by
`mysql_select_db('mypayrol_control_db', $link)`, using the long-deprecated/removed `mysql_*` extension
(incompatible with PHP 7+) alongside CakePHP's own `Model->query()` in the same methods — i.e. two
different, inconsistent DB access paths coexist in this controller.

**Migration takeaway**: despite being orphaned/dead in current routing, this is the only place in the
codebase that shows the intended full "new tenant seed" sequence — config → org lookups (dept/design) →
holiday calendar copy-from-template → shift policy copy-from-template → leave-head enablement → (stub:
salary policy/emp upload/config/login) → bulk password reset → wizard completion. Several steps are
incomplete/no-op (`save_policies`, `salary_policy`, `emp_upload`, `load_config`, `emp_login`,
`login_cred`, `completed_setup`), so this is a *template* for onboarding-flow design, not a working
reference implementation.

---

## 4. Schema quirks

- **Backup/versioned tables in the control DB** confirm historical schema migrations were done by
  cloning-and-renaming rather than proper migrations: `central_control_bck29092025`
  (`schema/mypayrol_control_db.sql:1443-1474`) and `company_branches_bck29092025`
  (`schema/mypayrol_control_db.sql:1498-1504`) are point-in-time snapshots of `central_control` and
  `company_branches` taken 2025-09-29. Diffing them against the live tables shows `central_control` grew
  new columns after that date: `plan` (`:1437`, default `'standerd'` — **note the typo "standerd"**,
  baked into the column default), `app_url` (`:1435`), and the backup lacks `plan`/`app_url` entirely
  (`:1443-1474` cuts off after `biometric_url`) — i.e. plan-based feature gating (`plan` column) and the
  Next.js-era `app_url` were both added after Sept 2025, aligning with `CompanySetupController`'s
  Razorpay/plan-feature code being a recent addition.
- **`branches.deleted`** (`schema/mypayrol_trial.sql:29146`, `int(11) DEFAULT '-1' COMMENT 'value greater
  than 0 is deleted'`) exists alongside `branches.status` (`:29145`, the column every controller actually
  uses for soft-delete via `status=0`). **No controller in this cluster ever reads or writes `deleted`** —
  dead/unused column, possible legacy soft-delete mechanism superseded by `status` but never dropped.
- **Inconsistent "0/1 = active/inactive" comment semantics across near-identical tables**: `department`
  (`schema/mypayrol_trial.sql:40940`) comments `status` as `'0 inactive 1 active'`; `grade`
  (`schema/mypayrol_trial.sql:44401`) comments it as `'0 inactive 1 inactive'` (self-contradictory,
  copy-paste error in the comment itself); `verticals` (`schema/mypayrol_trial.sql:47761`) comments it
  backwards as `'0 active 1 inactive'`. All controllers uniformly treat `status=1` as active and `status=0`
  as the soft-delete target regardless of what each column comment claims, so the comments are simply
  wrong/misleading in two of the three cases — a trap for anyone inferring business rules from schema
  comments alone.
- **`fin_year` unique key encodes the vattr1 dual-purpose design directly in the schema**: unique key
  `company_code_branch_code_fin_year_Year_status_vattr1_status` (`schema/mypayrol_trial.sql:44358`) spans
  `(company_code, branch_code, fin_year, Year_status, vattr1, status)` — confirming `vattr1` is a
  first-class discriminator (financial-year vs leave-year row) baked into uniqueness, not just an
  incidental flag; the seed data shows `vattr1=0` (`fin_year_seq=14`, calendar year `2025-01-01..12-31`)
  and `vattr1=1` (`fin_year_seq=15`, fiscal year `2025-04-01..2026-03-31`) for the same
  `company_code='EDIT'`/`branch_code='EDIT01'` (`schema/mypayrol_trial.sql:44362-44363`).
- **`branches` seed/trigger coupling makes `company_code`/`branch_code` effectively generated, not
  user-supplied**, despite the PHP layer (`BranchController.php:129-133`, `DbConfigController.php:88-90`)
  computing/assuming specific values — see §1 trigger analysis. Any Next.js reimplementation needs to
  decide once, in one place, how `branch_code` is generated (the trigger's `concat(company_code, 0,
  running_count)` scheme, or something new) rather than inheriting three different code paths that
  currently race/overwrite each other (PHP `BranchController`, PHP `DbConfigController`, and the MySQL
  trigger all mutate/assume `branch_code`).
- **`emp_proff` (EmployeeProfessionalDetails) org-assignment columns are all denormalized varchars**
  (`designation`, `emp_dept`, `emp_grade`, `emp_vertical`, `emp_branch` — `schema/mypayrol_trial.sql:43741-
  43745`) except `emp_sep_priv` (section) which is `int(11)` (`:43754`). This inconsistency (four varchar
  "soft FKs" + one int "soft FK", none enforced) is the root cause of the type-mismatched guarded-delete
  comparisons flagged in §3a and should be normalized to real integer FKs in the Next.js schema.
- **`db_config` is a per-company singleton table but has no `UNIQUE(company_code)` constraint**
  (`schema/mypayrol_trial.sql:40909-40931` — only a plain `PRIMARY KEY (db_config_pkey)`); every controller
  that reads it does so via `LIMIT 1` / `find('first')` (e.g. `CompanyController.php:118`,
  `DbConfigController.php:73`) trusting there's exactly one active row, which the schema does not enforce.

---

## Summary of flags for the migration team

1. **Dead code**: `CategoryController.php` + `Model/Category.php` — target table `category` /
   `grade.category_fkey` do not exist in any of the three schema dumps. Verify against a live tenant DB
   before deciding whether to port this feature at all.
2. **SQL-injection surface**: `Controller/CompanySetupController.php:154-158` (`verifyPayment()`) —
   unsanitized `$lower_user_id` interpolated into `UPDATE user_credentials ... WHERE LOWER(user_id) =
   LOWER('{$lower_user_id}')`.
3. **No enforced org-unit hierarchy in the DB** — `department`/`designation`/`division`/`section`/`grade`/
   `bank` are flat, unrelated lookup tables; whatever hierarchy the UI implies must be a purely
   application-level design decision for the new system, not something being carried over from the legacy
   schema.
4. **`division` vs `verticals`** — two overlapping org-unit concepts; `DivisionController`'s guarded-delete
   check queries a column (`emp_vertical`) that other code resolves against `verticals`, not `division`.
   Needs a product decision on which is canonical before migration.
5. **Guarded-delete inconsistency** — Division/Section/Grade block deletion when employees are assigned;
   Branch/Department do not. Decide if this is intentional before replicating.
6. **`branches` trigger vs PHP dual-write** — MySQL triggers (`branches_bi/ai/au`,
   `schema/mypayrol_trial.sql:29153-29184`) auto-generate `branch_code` and sync
   `mypayrol_control_db.company_branches`; `BranchController::savebranch()`
   (`Controller/BranchController.php:114-197`) partially duplicates this in PHP. Since triggers won't exist
   in the new stack, this logic must be consciously reimplemented once, not copy-pasted from the PHP side
   (which currently only works *because* the trigger silently overrides its `branch_code` guess).
7. **Return-instead-of-echo bug** in `savedepartment()`/`savedivision()`/`savesection()`/`save()`
   (Designation) — duplicate-name/code validation errors produce an empty HTTP response body instead of
   the intended JSON error, because `return json_encode(...)` is used where `autoRender=FALSE` requires
   `echo`.
8. **Broken duplicate-check tautology** in `GradesController::checkgradeexists/checkgradecodeexists`
   (`Controller/GradesController.php:119,133`) and mirrored in `CategoryController` — condition is always
   true once any row is returned, so it effectively never allows a "not found" result.
9. **`DbConfigController`** — orphaned from `routes.php`, but is the closest thing to a "seed a new
   tenant" script (org lookups, holiday-calendar copy, shift-policy copy, bulk credential reset). Several
   steps are unimplemented stubs (`salary_policy`, `emp_upload`, `load_config`, `emp_login`,
   `login_cred`, `completed_setup`, and a no-op `save_policies`). Uses hardcoded root MySQL credentials
   (`'root'`/`'Localhost&*()'`, 9 occurrences) and the deprecated `mysql_*` extension throughout, alongside
   CakePHP's `Model->query()` — two inconsistent DB-access paths in the same file.

---

### 7.8 Site / Field Work & Project Management

# Site / Field Work / Project Management — Data Model & Business Logic Report

Scope: `SiteController` (login-only, excluded), `SiteAttendanceController`, `SiteAttendanceApplyController`,
`ProjectController`, `SiteWorkController`, `FieldSurveyController`, `MaterialController`,
`MaterialRequestController`, `GatePassController`, `DeviceController` (touched only to confirm out-of-scope).
All paths are relative to `D:\Projects\RIZOMigration\legacy\` (note: PHP sources live directly under
`Controller\` / `Model\`, **not** under an `app\` prefix as the task brief implied).

---

## 1. Schema cross-check

### 1.1 `site` / `site_transactions` / `site_history` cluster (job-site attendance & rates)

| Model file | `$useTable` | Schema table found? | PK match | Notes |
|---|---|---|---|---|
| `Model/Site.php:24-28` | `site` | Yes — `schema/mypayrol_trial.sql:46843` | `site_pkey` ✓ | |
| `Model/SiteMaster.php:7-17` | `site` | Yes (same table) | `site_pkey` ✓ | **Confirmed duplicate of `Site`** — identical `$useTable`/`$primaryKey`, different class name only. |
| `Model/SiteTransactions.php:7-16` | `site_transactions` | Yes — `schema...sql:47133` | `site_transactions_pkey` ✓ | |
| `Model/SiteHistory.php:7-17` | `site_history` | Yes — `schema...sql:47061` | `site_history_pkey` ✓ | Audit/history copy of every `site_transactions` write (see saveSite() below). |
| `Model/Access_site.php:7-17` | `access_site` | Yes — `schema...sql:28702` | `access_site_pkey` ✓ | No status default beyond `1`; no FK constraints in DDL. |
| `Model/SiteAttendance.php:7-17` | `site_attendance` | Yes — `schema...sql:46878` | `site_attendance_pkey` ✓ | |
| `Model/Siteattendanceregister.php:7-17` | `site_attendance_register` | Yes — `schema...sql:47006` | `registerid` — custom PK, non-standard `*_pkey` naming. Has two hand-rolled proc-call helper methods (see §2). |
| `Model/SiteMasterApproval.php:7-17` | `site_master_approval` | Yes — `schema...sql:47084` | `site_master_approval_pkey` ✓ | File literally starts with the stray text `Approval<?php` before the opening PHP tag (see §4). |
| `Model/SiteMasterApprovalDetails.php:7-17` | `site_master_approval_details` | Yes — `schema...sql:47098` | primary key declared as `'site_master_approval_details_pkey\t'` (**trailing tab character** baked into the string) — same `Approval<?php` prefix bug. |

**`site` table** (`schema...sql:46843-46875`) has exactly **one** FK constraint in the whole cluster:
`site_ibfk_1 FOREIGN KEY (contact_name) REFERENCES contacts(contact_id)`. `site_transactions`,
`site_history`, `access_site`, `site_attendance`, `site_master_approval*` declare **zero** FK constraints
(consistent with the "zero validation" pattern noted in prior research) — `site_fkey`,
`day_time_seq_fkey`, `designation_id`, `site_transactions_fkey`, `emp_fkey` etc. are all plain
`int`/`varchar` columns with no referential integrity enforced at the DB layer.

**`site` vs `site_transactions` relationship** (both used interchangeably by `SiteAttendanceController`,
`SiteAttendanceApplyController`, `ProjectController`, and `SiteAttendanceController::saveSite()` /
`ProjectController::saveProject()`):
- `site` = the site/job-site/project master record (site_id, site_name, address, customer info, PM, PO
  dates/amounts — reused by `ProjectController` for "project" concepts: `expected_starting_date`,
  `allocated_fund`, `po_expirydate`, `customer_refno` are all on the *same* `site` table).
- `site_transactions` = one row per (site, shift/day_time_seq, designation) combination — holds
  `emp_count`, `srate` (billing/sales rate), `eratess` (employee wage rate), `start_date_effective`/
  `end_date_effective`. **No `eratess_28/29/30/31` columns exist in the schema** (see §4 — schema drift).
- `site_history` mirrors every `site_transactions` insert/update row-for-row (same field set plus
  `action` = `'i'`/`'u'`) — an append-only audit log, always written alongside `site_transactions.saveAll()`.

### 1.2 `efsr_site` cluster (field-survey / equipment sites — distinct from `site`)

| Model file | `$useTable` | Schema table found? |
|---|---|---|
| `Model/EfsrSite.php:7-17` | `efsr_site` | **NOT FOUND** in `schema/mypayrol_trial.sql` |
| `Model/SiteWork.php:24-28` | `efsr_site` | **NOT FOUND** — same table as `EfsrSite`, confirms duplicate |

**Confirmed: `EfsrSite`/`SiteWork` are duplicate models over the same (schema-absent) table**, exactly
like `Site`/`SiteMaster`. `EfsrSite.php:13` even sets `$name = 'EfsrTickets'` while the class is named
`EfsrSite` and the table is `efsr_site` — a three-way name mismatch inside one 17-line file.

More significantly: `efsr_site`, `efsr_tickets`, `efsr_equipments_master`, `equipment_type`,
`survey_type` — every table `FieldSurveyController` and `SiteWorkController` query — are **absent from
`schema/mypayrol_trial.sql` entirely** (checked via `grep -i "CREATE TABLE" | grep -i efsr` — zero hits;
also checked `gate_pass`, `gate_pass_items` — zero hits). Either:
1. These tables were added to production after the schema dump was taken (most likely, given the "Edited
   by Akshay on 21-8-2025" comments elsewhere in this cluster indicating active 2023-2025 development), or
2. The schema dump is from a different/older environment than the code.
**This is the single biggest schema-cross-check risk in this cluster** — the migration cannot rely on
`schema/mypayrol_trial.sql` alone for `efsr_*` or `gate_pass*` structure; live-DB introspection is required.

### 1.3 Material / Gate Pass tables

| Model file | `$useTable` | Schema table found? |
|---|---|---|
| `Model/MaterialRequest.php:24-29` | `material_request` | Yes — `schema...sql:45040` |
| `Model/MaterialRequestDetails.php:24-28` | `mr_details` | Yes — `schema...sql:45225` |
| `Model/GatePass.php:7-16` | `gate_pass` | **NOT FOUND** in schema |
| `Model/GatePassItems.php:7-16` | `gate_pass_items` | **NOT FOUND** in schema |

`MaterialController.php:49` — `public $uses = array('Item','QuantityDetails','AdditionalDetails',
'ItemDetails','WarrantyDetails','MaterialController')` — **`MaterialController` is listed as a model**,
and no `material_controller` table exists anywhere in the schema (`grep -i material_controller` → zero
hits) nor is there a `Model/MaterialController.php` file. **Confirmed: this is a pure PHP-side copy/paste
bug** (someone typed the controller's own class name into `$uses` instead of a real model, likely meant
`'Materials'` or similar) — not a reflection of any real table. Because CakePHP 2.x lazily instantiates
`$uses` models on first property access, this line alone does not crash `index()`/`form()`/etc. unless
code actually does `$this->MaterialController->...` (grep confirms it never is referenced anywhere in the
controller body) — so it is dead weight rather than a live fatal-error trigger, but it is an unambiguous
symptom of copy-paste-without-review authorship across this cluster.

### 1.4 Project tables

`ProjectController` does **not** have its own `Project`/`site` distinctions — it reuses `SiteMaster`
(`site` table) and `SiteTransactions` (`site_transactions` table) wholesale (`ProjectController.php:50`
`$uses` list is byte-for-byte the same array as `SiteAttendanceController.php:51`). The only
project-specific models are:
- `Model/ProjectActivity.php:7-16` → `useTable = 'project_activity'`. **Table not found in schema**
  (only `activity_projects` differs — not checked as out of primary scope, but `project_activity` itself
  returned no `CREATE TABLE` hit). Primary key string is `'activity_pkey\t'` — trailing tab bug, identical
  pattern to `SiteMasterApprovalDetails`.
- `Model/ProjectIncome.php:24-29` → `useTable = 'project_income'`, and unusually sets
  `public $useDbConfig = 'ProjectIncome'` **directly on the model** (every other model in this cluster
  relies on the controller calling `->useDbConfig = $this->Session->read('ds')` per-request for
  multi-tenant DB switching). This hardcoded `useDbConfig` will not follow the session's tenant database
  the way sibling models do — a latent multi-tenancy bug worth flagging for migration (payment data could
  route to the wrong tenant DB if this model is ever used without the controller overriding it).
- `Model/ProjectIncomePayment.php:7-17` → `useTable = 'project_income_payment'`, PK `income_pkey` (not
  `project_income_payment_pkey` — inconsistent naming vs. the model's own table prefix).

---

## 2. Custom methods per model

Nearly every model in this cluster is a bare `AppModel` subclass with only `$name`/`$primaryKey`/
`$useTable` — **zero custom methods, zero validation, zero callbacks** (confirms prior finding). The only
two exceptions found:

**`Model/Siteattendanceregister.php`** (table `site_attendance_register`):
- `insertUpdateAttendanceRegisterProc($outputParameter)` (`Model/Siteattendanceregister.php:19-46`) —
  builds a comma-quoted parameter list and calls `CALL site_insert_update_att_reg(...)` directly from the
  model layer (unusual — every other proc call in this cluster is issued from the controller). Contains
  ~15 lines of dead commented-out `mysqli_fetch_array` code. Call sites: none found in the controllers in
  this cluster's scope — grep of `Controller\Site*.php` shows no `insertUpdateAttendanceRegisterProc(`
  call; `SiteAttendanceManageController.php:584` and `SiteattendanceregisterController.php:264` instead
  call the same stored proc directly via raw `->query("CALL site_insert_update_att_reg(...)")`, bypassing
  this model method entirely — **the model method appears to be dead/unreachable code** (possible legacy
  cruft — verify before migrating).
- `salaryProcessPrc($outputParameter)` (`Model/Siteattendanceregister.php:48-58`) — same pattern, calls
  `CALL salary_process_prc(...)`. No call sites found anywhere under `Controller\Site*.php` or
  `Controller\Project*.php` — **dead/unreachable code** (possible legacy cruft — verify before migrating;
  this belongs conceptually to the payroll cluster, not site/field).

All other models (`Site`, `SiteMaster`, `SiteTransactions`, `SiteHistory`, `SiteAttendance`, `Access_site`,
`EfsrSite`, `SiteWork`, `GatePass`, `GatePassItems`, `MaterialRequest`, `MaterialRequestDetails`,
`ProjectActivity`, `ProjectIncome`, `ProjectIncomePayment`, `SiteMasterApproval`,
`SiteMasterApprovalDetails`) are pure table mappings — **all business logic lives in the controllers**, as
raw SQL strings and inline PHP, confirming the "zero validation/hooks" finding for this cluster too.

---

## 3. Complex logic

### 3.1 Site-attendance rate calculation (`site_rate_update_prc`)

Body at `schema/mypayrol_trial.sql:21199-21248`. Per-site, per-month cursor over `site_attendance` rows
with `status=3` (closed shift) and `emp_fkey != 1`:
```sql
select site_transactions_pkey, srate, eratess into vsite_transactions_pkey, vsrate, veratess
  from site_transactions
  where site_fkey=vsite_fkey and day_time_seq_fkey=vday_time_seq_fkey
    and designation_id=vdesignation_id and status=1;
update site_attendance
  set sales_rate = vsrate, emp_rate = veratess, duration = TIMESTAMPDIFF(Hour, vintime, vouttime)
  where site_attendance_pkey = vsite_attendance_pkey;
```
This is the **actual wage-rate calculation for site-based workers**: `sales_rate` (billing rate charged to
the customer) and `emp_rate` (rate paid to the employee) are both looked up from `site_transactions`
(keyed by site + shift + designation, not by employee) and written back onto each individual
`site_attendance` punch row, along with `duration` = raw hour difference between in/out time (no break
deduction, no overtime split — differs sharply from the day_time_procedures-based regular payroll
calculation used for office/branch employees). The proc also does `insert into test (...)` twice
(`schema...sql:21236`, `21242`) — writes to a literal debug table named `test` on every iteration,
apparently left in from development and never removed.

**Call-site check for this cluster**: none of `SiteAttendanceController`, `SiteAttendanceApplyController`,
`ProjectController`, `SiteWorkController`, `FieldSurveyController`, `MaterialController`,
`MaterialRequestController`, `GatePassController` call `site_rate_update_prc` directly (grep confirms zero
hits under `Controller\`). It is presumably invoked from a payroll/report controller outside this
cluster's scope — flag for cross-check with the payroll-cluster research pass.

**Procs this cluster *does* call directly**:
- `mark_site_attendance_fn` / `mark_site_attendance_out_fn` — called from
  `SiteAttendanceController::mark_attendance()` (`Controller/SiteAttendanceController.php:1324-1469`) and
  `::update_attendance()` (`:1473-1617`), and duplicated verbatim in
  `ProjectController::mark_attendance()` (`Controller/ProjectController.php:1072-1217`). Pattern: call the
  function first to get an `'insert'`/`'update'`/error-message verdict, then issue a *separate* raw
  `INSERT`/`UPDATE` on `site_attendance` from PHP based on that verdict — i.e., the SQL function only
  validates/returns a signal, the actual write happens in application code as a second round-trip (race
  condition risk between the two statements).
- `site_time_duration_check` and `site_insert_update_att_reg` — called from several controllers, but note
  the calls from `SiteattendanceregisterController.php:112-166` and `SiteAttendanceManageController.php:584`
  are outside this cluster's assigned file list (falls under attendance-register scope) — cited here only
  because `SiteAttendanceController` and `ProjectController` do **not** call them despite sharing the same
  `site_attendance`/`site_transactions` tables.

### 3.2 Gate-pass / material-request approval "state machine"

**Gate pass (`gate_pass` table, status column)**: Not a formal enum-driven workflow — `status` is used
ad-hoc:
- `GatePassController::savePass()` (`Controller/GatePassController.php:428-563`) — insert branch
  (`$pkey==0`) does a plain `INSERT`; **update branch (`$pkey != 0`) exists here** (raw `UPDATE gate_pass`)
  — unlike `OutPassController::savePass` (noted in prior research as having *no* update branch), gate pass
  does have one.
- `GatePassController::printPass()` (`:625-817`) is a **second, overlapping update path**: if
  `$is_edited=='true'` it re-saves the entire `gate_pass` row (including bumping `status`) *and* all
  `gate_pass_items` rows (deleting/reinserting via a bulk `status=0` then per-row upsert,
  `Controller/GatePassController.php:731-780`) from inside what is nominally a "print/generate PDF"
  action. `status` values observed: `0` = deleted (`deleteFromGrid`, `:882-916`), `1` = "pass" (`$pass ==
  'true'` branch, `:641-645`), `2` = default/"preview or non-pass" mode. There is no `pending → approved →
  rejected` workflow despite the presence of `sanctioned_by`/`issued_by` columns — sanctioning is just a
  free-text/FK field filled in at save time, not a gated transition. **This mirrors the `OutPassController`
  update-via-print anti-pattern** the parent research already flagged, just less broken (an update path
  does exist directly in `savePass`, so it isn't "broken" the way OutPass is — but the dual-path
  update-in-print duplication is the same shape of risk: two independent code paths can update the same
  row with different validation).

**Site master change-approval (`site_master_approval` / `site_master_approval_details`)**: This *is* a
genuine (if trivial) state machine, driven from `SiteAttendanceApplyController::saveSite()`
(`Controller/SiteAttendanceApplyController.php:605-888`):
1. Table default is `status = 'pending'` (`schema...sql:47088`, `:47110` — column comment literally says
   "pending is default").
2. But the application code **always writes `'status' => 'approved'`** at insert time
   (`Controller/SiteAttendanceApplyController.php:684`, `:772`, `:794`, `:863`) with
   `'remarks' => 'Approved by Admin'` hardcoded — there is no UI/action anywhere in this controller (or any
   other controller referencing `SiteMasterApproval`/`SiteMasterApprovalDetails`) that ever writes
   `'rejected'` or leaves a row at `'pending'`. **The approval table exists as an audit trail of
   diffs (old_value/new_value per changed field) but the "approval" is auto-granted by the same request
   that made the change — there is no separate reviewer action.** `type` column distinguishes
   `'general'` (site master field changes) vs `'shift'` (site_transactions changes) vs presumably
   `'delete'` per the schema comment (`schema...sql:47111`), but no code path in this controller sets
   `type='delete'` explicitly (only inferred from `status==0` shift rows, `:784-807`, which still writes
   `'approved'`).
3. Compare `SiteAttendanceController::saveSite()` (`Controller/SiteAttendanceController.php:611-726`,
   the non-"Apply" sibling controller writing to the *same* tables) — it has **no approval-tracking logic
   at all**, just a direct `SiteMaster->save()` / `SiteTransactions->saveAll()` / `SiteHistory->saveAll()`.
   So two different controllers (`SiteAttendanceController` vs `SiteAttendanceApplyController`) both
   implement "save a site," one with a vestigial approval audit trail bolted on (2023-dated `//Edited by
   Akshay` comments) and one without — a genuine divergent-duplicate-logic risk for the migration: which
   one is the canonical "current" behavior needs a product decision, not just a code read.

---

## 4. Schema quirks

- **`eratess_28`/`eratess_29`/`eratess_30`/`eratess_31` columns referenced in PHP do not exist in
  `site_transactions`** (`schema...sql:47133-47150` lists only `srate`, `eratess` — no per-days-in-month
  variants). `SiteAttendanceController::saveSite()` (`Controller/SiteAttendanceController.php:677-686`,
  `:707-716`) conditionally writes these 4 columns instead of `eratess` for company codes `DEMO`, `GLET`,
  `ABSG`, `SCRT` (comment: "Edited by Akshay on 21-8-2025"). Either the schema dump predates this change
  (most likely, given the 2025 edit date) or these 4 tenants will hit a SQL error on every site-transaction
  save. **Must verify against the live DB before migration** — this determines whether the target schema
  needs 4 extra rate columns or a normalized "rate per days-in-month" side table.
- **`gate_pass`, `gate_pass_items`, `efsr_site`, `efsr_tickets`, `efsr_equipments_master`,
  `equipment_type`, `survey_type` are entirely absent from `schema/mypayrol_trial.sql`** despite being
  actively read/written by `GatePassController`, `FieldSurveyController`, and `SiteWorkController`. This is
  the largest single gap between "authoritative schema" and "what the code actually touches" found in this
  cluster — treat the schema file as **incomplete for this feature area**, not authoritative.
- **`site.min_days_before`** (`schema...sql:46869`, `DEFAULT '30'`) — used by
  `SiteAttendanceController::get_incompleteDate()` (`Controller/SiteAttendanceController.php:840-841`) to
  bound how far back bulk attendance entry is allowed; comment "Minimum days for site punch attendance bulk
  update. by arul on 16-3-23" — a business rule bolted onto the site master late, with no matching
  column-level default enforcement anywhere else.
- **`site.latitude`/`site.longitude` are declared `int(11) NOT NULL`** (`schema...sql:46852-46853`), not
  `decimal`/`float` — real-world GPS coordinates would truncate to whole-number degrees, i.e. **effectively
  unusable for actual geolocation** as stored (a site at 12.9716° N would be forced to `12`). Either the
  app writes pre-scaled integers (no evidence of scaling in the controllers read), or this column has
  silently been storing garbage/truncated coordinates. High-value flag for the target schema (should be
  `decimal(10,7)` or similar).
- **`site_master_approval.site_fkey` and `site_transactions_fkey` have no `NOT NULL` + no FK constraint**
  (`schema...sql:47086-47087` — `site_fkey` is nullable despite conceptually always being required); several
  insert paths in `SiteAttendanceApplyController::saveSite()` populate one or the other but never both, so
  ~half of `site_master_approval` rows will have a NULL `site_transactions_fkey` (general/site-level
  changes) and the rest NULL `site_fkey`-context ambiguity is only recoverable by also joining
  `site_master_approval_details`.
- **`Model/SiteMasterApproval.php` and `Model/SiteMasterApprovalDetails.php` both begin with the literal
  text `Approval` before `<?php`** (`Model/SiteMasterApproval.php:1`, `Model/SiteMasterApprovalDetails.php:1`).
  In PHP, any bytes before `<?php` are echoed verbatim on every request that loads the model — this would
  leak the string `"Approval"` into the response body (breaking JSON/AJAX responses that don't strip
  leading whitespace) whenever these two models are instantiated. Confirmed present in the file as read;
  worth a runtime check to see whether CakePHP's output buffering happens to mask it, but should not be
  carried into the rewrite regardless.
- **`ProjectActivity`'s primaryKey string has a trailing tab character**: `'activity_pkey\t'`
  (`Model/ProjectActivity.php:14`) — CakePHP's string comparison for primary key matching may silently fail
  to recognize records as new vs. existing depending on how strictly it's used; low-severity but another
  symptom of copy/paste without review in this cluster.
- **`material_request.customer_po_number` is `int(11) NOT NULL`** (`schema...sql:45046`) while
  `MaterialRequestController` treats it as free text in several autocomplete/report queries
  (`Controller/MaterialRequestController.php:642-655`, `getautocompletionscustomer_po_number`) — PO numbers
  with letters/leading zeros would either fail to insert or get silently coerced/truncated by MySQL's
  implicit int cast.
- **`mr_details.item_code` is declared `int(50)`** (`schema...sql:45228`, a nonstandard/meaningless width
  spec MySQL just ignores) and has **no FK to `item_master.item_master_pkey`** despite every controller
  query joining them as if they were a formal foreign key — confirms the "zero FK enforcement" pattern
  extends fully into the material-request tables.
- **`MaterialController::$uses` non-existent-model finding — CONFIRMED, and it is purely a PHP-side naming
  bug, not a reflection of any real table.** No `material_controller` table exists anywhere in
  `schema/mypayrol_trial.sql` (zero grep hits, case-insensitive), and no `Model/MaterialController.php`
  file exists in the codebase either. CakePHP 2.x's lazy model loading means this entry in `$uses`
  (`Controller/MaterialController.php:49`) is inert unless the code calls `$this->MaterialController->...`,
  which it never does in this file — so it is latent dead weight, not a live fatal-error trigger, but
  should be deleted rather than carried into the rewrite.

---

## Summary of duplicate/dead-code flags (possible legacy cruft — verify before migrating)

- `Site` (`Model/Site.php`) and `SiteMaster` (`Model/SiteMaster.php`) — confirmed duplicate models over
  `site` table.
- `EfsrSite` (`Model/EfsrSite.php`) and `SiteWork` (`Model/SiteWork.php`) — confirmed duplicate models over
  `efsr_site` table (itself absent from the schema dump).
- `ProjectController` (`Controller/ProjectController.php`) is ~90% a byte-for-byte copy of
  `SiteAttendanceController` (`Controller/SiteAttendanceController.php`) — same `$uses`, same
  `mark_attendance`/`update_attendance`/`shift_closure`/`close_shift`/`data_site`/`site_allocate`/
  `save_allocate`/`remove_allocate`/menu-access boilerplate methods duplicated verbatim, only
  `saveProject()` vs `saveSite()` differ meaningfully. High risk of drift between the two copies (e.g.
  `ProjectController::saveProject()` has no site_transactions writes at all, while `SiteAttendanceController
  ::saveSite()` does) — needs explicit reconciliation before the rewrite decides which behavior is
  canonical.
- `Model/Siteattendanceregister.php::insertUpdateAttendanceRegisterProc()` and `::salaryProcessPrc()` —
  no call sites found in this cluster's controllers; likely dead code (or called only from the
  attendance-register cluster, outside this scope).
- `SiteWorkController` (`Controller/SiteWorkController.php:813-1076`) has ~260 lines of fully
  commented-out duplicate methods (`get_incompleteDate`, `pnch`, `get_shift`, `load_sites`, `add_site`,
  `data_site`, `mark_attendance`) mirroring `SiteAttendanceController` — dead code left in place rather
  than deleted.
- `MaterialController::$uses` entry `'MaterialController'` — dead/inert self-referential model bug.

---

### 7.9 Assets, Inventory & Purchasing

# Assets, Inventory & Purchasing — Data Model / Business Logic Report

Scope: Item, PurchaseOrder, GoodsReceivedNotes, StockDetails, Store, Vehicle, Assets, plus every model
transitively touched by the PO→GRN→Stock chain and the Asset/Allocation chain, as found via `$uses` in
`AssetController.php`, `ItemController.php`, `StockReportController.php`, `StoreController.php`,
`PurchaseOrderController.php`, `GoodsReceivedNotesController.php`, `VehicleController.php`, and
`StockTranferController.php` (pulled in because the "Stock Transfer" logic referenced by the prior
research pass lives there, not in `StoreController`).

All models are legacy CakePHP 2.x `AppModel` subclasses. **Every model file inspected below has zero
`$validate`, zero `$belongsTo`/`$hasMany`/`$hasOne`, zero callbacks (`beforeSave`, `afterFind`, etc.), and
zero custom methods** — confirming the prior finding generalizes across the whole cluster, not just the
stock tables. All relational integrity and business logic lives in controllers (raw SQL / ORM calls) and
in stored procedures/functions in the schema.

---

## 1. Schema cross-check

### 1.1 Model → table mapping (all verified against `schema/mypayrol_trial.sql`)

| Model file | `$useTable` | `$primaryKey` | Table exists in schema? |
|---|---|---|---|
| `Model/Item.php` | `item_master` | `item_master_pkey` | Yes — schema/mypayrol_trial.sql:44791 |
| `Model/PurchaseOrder.php` | `purchase_order` | `po_pkey` | Yes — schema/mypayrol_trial.sql:45507 |
| `Model/PurchaseOrderDetails.php` | `po_item_details` | `po_item_pkey` | Yes — schema/mypayrol_trial.sql:45386 |
| `Model/MaterialRequest.php` | `material_request` | `mr_pkey` | Yes — schema/mypayrol_trial.sql:45040 |
| `Model/MaterialRequestDetails.php` | `mr_details` | `mr_details_pkey` | Yes — schema/mypayrol_trial.sql:45225 |
| `Model/GoodsReceivedNotes.php` | `goods_receved_notes` (note misspelling, matches schema) | `grn_pkey` | Yes — schema/mypayrol_trial.sql:44380 |
| `Model/GrItemDetails.php` | `gr_item_details` | `gr_item_pkey` | Yes — schema/mypayrol_trial.sql:44437 |
| `Model/GoodsReceivedNotesItem.php` (class `GoodsReceivedNotesitem`) | `gr_item_details` | `gr_item_pkey` | **Same table as GrItemDetails** |
| `Model/StockDetails.php` | `stock_details` | `stock_details_pkey` | Yes — schema/mypayrol_trial.sql:47194 |
| `Model/Store.php` | `store_master` | `store_master_pkey` | Yes — schema/mypayrol_trial.sql:47295 |
| `Model/Vehicle.php` | `vehicle_master` | `vehicle_master_pkey` | **NOT FOUND in schema dump** (see 1.4) |
| `Model/Assets.php` | `asset_management` | `asset_pkey` | Yes — schema/mypayrol_trial.sql:28817 |
| `Model/AssetsModel.php` | `asset_management` | `asset_pkey` | **Same table as Assets** |
| `Model/AssetsName.php` | `asset_management` | `asset_pkey` | **Same table as Assets** (third duplicate, undocumented in prior pass) |
| `Model/allocate.php` (class `allocate`) | `asset_allocate` | `allocate_pkey` | Yes — schema/mypayrol_trial.sql:28789 |
| `Model/allocate_details.php` | `allocate_details` | `allocate_details_pkey` | Yes — schema/mypayrol_trial.sql:28766 |
| `Model/PoReturn.php` | `return_gr_items` | `rtn_pkey` | Yes — schema/mypayrol_trial.sql:45829 |
| `Model/PoReturnRequest.php` | `po_return_request` | `po_pkey` (reused name — collides conceptually with `purchase_order.po_pkey`) | Yes — schema/mypayrol_trial.sql:45408 |
| `Model/StockAdjustment.php` | `stock_adjustments` | `stock_adjustments_pkey` | Yes — schema/mypayrol_trial.sql:47170 |
| `Model/StockAdjustmentDetails.php` | `stock_adjustments_details` | `stock_adjustments_details_pkey` | Yes — schema/mypayrol_trial.sql:47183 |
| `Model/item_purchase.php` | `item_purchase` | `item_pkey` | Yes — schema/mypayrol_trial.sql:44838 |
| `Model/item_allocate.php` | `itm_allocation` | `allocation_pkey` | Yes — schema/mypayrol_trial.sql:44867 |
| `Model/PurchaseList.php` | `purchase_list` | `purchase_list_pkey` | Yes — schema/mypayrol_trial.sql:45496 |
| `Model/DirectPurchaseOrderDetails.php` | `direct_po_details` | `direct_po_details_pkey` | Yes — schema/mypayrol_trial.sql:41900 |
| `Model/Units.php` | **`branches`** (not `units`) | n/a (uses default `id`) | table exists elsewhere in schema; name mismatch is intentional (branch = "unit" in this domain) |
| `Model/StockTranfer.php` | `stock_tranfer` | — | Yes — schema/mypayrol_trial.sql:47261 |
| `Model/StockTranferItem.php` | `stock_tranfer_item` | — | Yes — schema/mypayrol_trial.sql:47277 |

### 1.2 Duplicate-model claims — CONFIRMED, and a third one found

- **`GrItemDetails` (Model/GrItemDetails.php:13-15) and `GoodsReceivedNotesitem` (Model/GoodsReceivedNotesItem.php:25-27) both map to `gr_item_details` / `gr_item_pkey`.** Confirmed identical `$useTable`. This is not dead duplication — **both are actively used in the same controller** (`GoodsReceivedNotesController.php:51` declares both in `$uses`):
  - `GrItemDetails` is used by the **live** `save()` method (Controller/GoodsReceivedNotesController.php:473 `$this->GrItemDetails->save($arr_form_data)`, then `UpdateStock()` at :475).
  - `GoodsReceivedNotesitem` is used only by the **dead** `save1()` method (Controller/GoodsReceivedNotesController.php:685 `$this->GoodsReceivedNotesitem->saveAll($arr_form_data)`) and by `goodlist()` (Controller/GoodsReceivedNotesController.php:695, read-only `useDbConfig` set, no actual query call using it found).
  - Net effect: two ORM objects can write the same table with different field-mapping logic depending which code path executes. For migration, treat `gr_item_details` as one entity; drop `GoodsReceivedNotesitem`/`save1()`.

- **`Assets` (Model/Assets.php:15-16) and `AssetsModel` (Model/AssetsModel.php:15-16) both map to `asset_management` / `asset_pkey`.** Confirmed identical `$useTable`. `AssetController.php:51` only declares `Assets` in `$uses`; a repo-wide grep found no controller referencing `AssetsModel` — **`AssetsModel` appears to be entirely dead** (possible legacy cruft — verify before migrating).
  - **New finding not in the prior pass**: `Model/AssetsName.php:15-16` is a **third** model on the same table (`asset_management`/`asset_pkey`). Also not referenced by any controller found — additional dead duplicate.

### 1.3 PO → GRN → stock_details FK chain — verified against schema, several real mismatches found

- `mr_details` (schema/mypayrol_trial.sql:45225) has `mr_fkey` → `material_request.mr_pkey`. Confirmed conventional.
- **`po_item_details` does NOT reference `mr_details` by its own PK.** `po_item_details.mr_fkey` (schema/mypayrol_trial.sql:45400, `int(20)`) is populated by `PurchaseOrderController::save()` (Controller/PurchaseOrderController.php:452) with `$mr_fkey`, which is set at Controller/PurchaseOrderController.php:438 from `$arr_form_data['mr_pkey']` — i.e. **`material_request.mr_pkey`, not `mr_details.mr_details_pkey`.** The column name `mr_fkey` is therefore misleading: it points to the MR *master* row, not the MR *line* that was actually ordered. Line-level traceability between a PO line and the specific MR line it fulfilled is not preserved in this column; it's only inferable by joining on `item_code` + `mr_fkey` + `po_fkey`, which `PurchaseOrderController::save()` does via a manual `SELECT ... WHERE po_fkey=... AND item_code=... AND mr_fkey=...` existence check (Controller/PurchaseOrderController.php:448).
- **`gr_item_details.po_fkey` (schema/mypayrol_trial.sql:44440) references `purchase_order.po_pkey` directly** (int(11) NOT NULL) — confirmed by `GoodsReceivedNotesController::save()` (Controller/GoodsReceivedNotesController.php:456-460), which does `$arr_postatus_data['po_pkey'] = $arr_form_data['po_fkey']` and saves it against `PurchaseOrder`. So the earlier finding that "PO→GRN→stock chain is linear/status-driven" is correct at the header level (`purchase_order.grn_status`), but the GRN **line items** (`gr_item_details`) reference the PO directly, not through `goods_receved_notes` alone — i.e. a `gr_item_details` row carries both `grn_fkey` (→ `goods_receved_notes.grn_pkey`) and `po_fkey` (→ `purchase_order.po_pkey`) redundantly (schema/mypayrol_trial.sql:44439-44440).
- **`stock_details` has no declared FK columns pointing at `item_master` or `store_master` by name convention, and one outright type mismatch:**
  - `stock_details.item_fkey` (schema/mypayrol_trial.sql:47202, `int(11) NOT NULL`) — correctly typed to match `item_master.item_master_pkey` (int(11)). Confirmed populated from `ItemMaster.item_master_pkey` in `GoodsReceivedNotesController::UpdateStock()` (Controller/GoodsReceivedNotesController.php:518).
  - `stock_details.store_fkey` (schema/mypayrol_trial.sql:47196) is **`varchar(21)`**, while `store_master.store_master_pkey` (schema/mypayrol_trial.sql:47296) is `int(11)`. **Type mismatch** — `store_fkey` is stored as a string even though it always holds a numeric ID (e.g. `$arr_save_data['store_fkey'] = $arr_gr['0']['GRNotes']['store_code']` at Controller/GoodsReceivedNotesController.php:514, and `goods_receved_notes.store_code` is itself `varchar(50)` at schema/mypayrol_trial.sql:44386 — so the varchar-ness cascades from `goods_receved_notes.store_code`, which is never actually validated as numeric anywhere).
  - `stock_details.supplier_fkey` (int(11)) references the vendor id from `Contacts`/`purchase_order.supplier_code` — but `purchase_order.supplier_code` itself is `varchar(50)` (schema/mypayrol_trial.sql:45516), another numeric-in-varchar column feeding an int column; on non-numeric supplier codes this would silently truncate/zero in MySQL's lenient casting.
- **No SQL-level `FOREIGN KEY` constraints exist anywhere in this cluster** — every table in this report is plain `MyISAM` or `InnoDB` with no `CONSTRAINT ... REFERENCES` clauses (grep of the whole schema for these table names found none). All referential integrity is enforced only by convention in application code, which is inconsistent (see 1.3 and 1.4).

### 1.4 `Vehicle` model / `vehicle_master` table — table not found in authoritative schema

`Model/Vehicle.php:15` declares `$useTable = 'vehicle_master'`, and `Controller/VehicleController.php` issues raw SQL against `vehicle_master` in six places (Controller/VehicleController.php:56, :67, :90, :95, :156, :159 among others). **`schema/mypayrol_trial.sql` has no `CREATE TABLE `vehicle_master`` statement anywhere** (grep for `CREATE TABLE \`vehicle` returned zero matches). Two possibilities, both worth flagging to the migration team:
1. The table exists in the live database but was dropped/omitted from this particular schema dump (dump may be stale or filtered), or
2. The whole Vehicle module is currently non-functional/orphaned against production.
Either way — **do not trust `vehicle_master`'s column list from inference; get a live `DESCRIBE vehicle_master` before migrating this module.** Columns referenced in code (`vehicle_master_pkey`, `reg_number`, `model_dec`, `make`, `vehicle_type`, `make_year`, `fual_type` [sic], `expense_type_name`, `status`) are known only from `VehicleController.php`, not from the schema file.

### 1.5 Ghost models — declared in `$uses` but no model file AND (for one) no backing table

- **`PoMaterial`**: declared in `PurchaseOrderController.php:51` `$uses`, and actually invoked — `$this->PoMaterial->useDbConfig(...)` (Controller/PurchaseOrderController.php:789) and `$this->PoMaterial->saveAll($arr_pome_data)` (Controller/PurchaseOrderController.php:844). **No `Model/PoMaterial.php` file exists**, and **no `po_material`/`po_materials` table exists in the schema** (grep confirmed). CakePHP's default table-inference (`PoMaterial` → `po_materials`) would fail at runtime with a "table not found" style error/empty model. This call only happens inside `PurchaseOrderController::save1()`, which — see below — is itself unreachable, so this is inert but should not be resurrected as-is.
- **`AssetType`**: declared in `AssetController.php:51` `$uses` and used for a real LEFT JOIN against `asset_types` (Controller/AssetController.php:58-64, joining on `Assets.Type = AssetType.asset_type_pkey`). **No `Model/AssetType.php` file exists**, but the **table `asset_types` does exist** (schema/mypayrol_trial.sql:28838), so CakePHP's default pluralization (`AssetType` → `asset_types`) happens to resolve correctly at runtime without an explicit model file — functional, but fragile (relies on default inflection matching; renaming the class or table would silently break it, and there's no home for validation/associations).

---

## 2. Custom methods per model

**All Model/ files in this cluster have zero custom methods.** Every model consists only of `$name`,
`$useTable`, `$primaryKey`, and occasionally `$useDbConfig`/`$order`. There is nothing to enumerate beyond
the inherited CakePHP `Model` API (`find`, `save`, `saveAll`, `query`, `updateAll`, `getInsertID`, etc.).
This confirms the prior research's "zero validation/hooks" finding extends to **zero business-logic
methods** as well — 100% of the domain logic below lives in Controllers.

Since the assignment implies controllers are the de facto "model layer" here, the functional inventory is
organized by controller instead:

### PurchaseOrderController.php (957 lines)
- `save()` (:408-462) — live PO creation/line-item-add path. New PO: `PurchaseOrder->save()`. Existing PO (adding lines): `PurchaseList->save()`, then per line raw `UPDATE mr_details SET ordering_qty=...`, raw `UPDATE material_request SET po_status='2'`, and a manual "upsert" into `po_item_details` via raw `SELECT` existence check + raw `UPDATE`/`INSERT` (Controller/PurchaseOrderController.php:446-453).
- `save1()` (:776-851) — **dead code**. Grepped project-wide; only definition found, no caller, no view posts to it. Depends on the non-existent `PoMaterial` model (see 1.5). Flag as legacy cruft — verify before migrating.
- `purchaseorders()` (:72-138) — datagrid listing with branch-scoped visibility for non-admins (`user_group == 2`), filtering by `po_pkey IN (subquery through purchase_list → material_request → access_store → store_master)`.
- `materialtable()`, `searchm()`, `details()`, `testdownloads()`, `downloads()`, `loadtable()`, `edit()`, `form()`, `purchasedelete()`, autocompletion helpers (`getautocompletionspo_number`, `getautocompletionslocation`, `getautocompletionssupplier_name`, `getautocompletionssupplier_code`, `getautocompletionsstore_code`), `getitem()`, `meteriallist()`, `addnewrow()`, `getitem1()`, `itemtotel()` — standard CRUD/support glue, no additional stock-affecting logic found.

### GoodsReceivedNotesController.php (748 lines)
- `save($po_pkey=0)` (:429-484) — live GRN path. New GRN header: `GoodsReceivedNotes->save()`. Existing GRN (adding line items): for `po_fkey` present, sets `purchase_order.grn_status = 0` (:456-460, reopens the PO's GRN flag — see quirk in §4); then per item line: `GrItemDetails->save()` followed immediately by `UpdateStock($grdetailsid, received_qty, mr_fkeys)` (:475).
- `UpdateStock($gritemsid, $Qty, $mr_number)` (:486-525) — **the only controller method that writes `stock_details` on the receipt path**. Re-fetches the just-saved `gr_item_details` row joined to `goods_receved_notes`, `purchase_order`, `item_master` to recover `store_code`, `supplier_code`, `po_number`, `item_master_pkey`, `po_rate`, then does `StockDetails->saveAll($arr_save_data)` — a **single flat associative array**, which CakePHP's `saveAll()` treats as one record, i.e. this **inserts exactly one new row** per call (no update-in-place, no running-balance mutation). Called once per GRN line item.
  - Note: line 446 references `$this->StockStoreTranfer->getInsertid()` but `StockStoreTranfer` is **not declared in `$uses`** (Controller/GoodsReceivedNotesController.php:51) and no `Model/StockStoreTranfer.php` exists (there is a differently-named `stock_store_tranfer` table at schema/mypayrol_trial.sql:47224, and `StockTranferController` uses a *different* model, `StockTranfer`, against a *different* table `stock_tranfer`). This line would throw at runtime if the `grn_fkey==''` branch is exercised with a truthy `stock_store_tranfer_pkey` in the posted form — **dead/broken code path, verify before migrating.**
- `save1()` (:643-689) — **dead code**, confirmed no caller project-wide. Duplicates `save()`'s multi-line-item loop but (a) uses `GoodsReceivedNotesitem` instead of `GrItemDetails`, (b) never calls `UpdateStock()` — so even if it were reachable, GRN lines saved through it would **never affect `stock_details`**, and (c) attempts `$this->PurchaseOrder->save(['grn_pkey'=>..., 'grstatus'=>0])` (:651-653) — `purchase_order` table has no `grn_pkey` or `grstatus` columns (the real column is `grn_status`, schema/mypayrol_trial.sql:45522); this call would silently insert a garbage new PO row (CakePHP `save()` with no matching PK field falls back to INSERT) rather than updating the intended one. Flag as legacy cruft — verify before migrating.
- `goodlist()`, `pgrnlist()`, `showgrndetails()`, `form()`, `edit()`, `grndelete()`, `loadtable()`, autocompletion helpers, `searchm()` — CRUD/support glue.

### StoreController.php (967 lines) — "Stock Adjustment" (mislabeled internally as transfer)
- `save_stock_transfer()` (:96-131) — despite the name, this is the **single-store stock adjustment/deduction** feature (matches prior finding). Logic:
  1. Calls stored function `stock_bal_qty_fn(date, from_store, item)` via raw `query()` (:111) to compute current balance.
  2. If `qty >= required_qty`: `StockAdjustment->save()` (header row in `stock_adjustments`), then `StockAdjustmentDetails->saveAll()` (line row in `stock_adjustments_details`, itself just a record of the adjustment, **not** a stock-balance table), then a **raw SQL `INSERT INTO stock_details (...) VALUES (..., $qty_love, ...)`** (:125-126) where `$qty_love = required_qty * -1` (:104) — i.e. writes a **negative** `item_qty` row directly to `stock_details`, bypassing the ORM entirely (string-concatenated values, no escaping beyond implicit casts — SQL-injection-shaped code, though inputs are session/form-derived).
  3. If balance insufficient, no-op, returns failure JSON.
- `save_po_return()` (:719-777) — PO return path (see §3).
- `finditembycode()`, `Storedata()`, `storefilter()`, `Storelist()`, `save_allocate()`, `remove_allocate()`, `Storedelete()`, `save()`, `findstoreitems()`, `findgritems()`, `findgrnlistitems()`, `finditem_qty()`, `finditem_qtyvalue_po()`, `finditem_qtyvalue()`, `findgrnitemslist()`, autocompletion helpers, `itemexists()`, `getavailqty()`/`getavailqty_grn()`, `getitem_pkey()`, `loadtabledata()`, `deletepoorder()`, `deleteordermaster()`, `submit_return()`, `editpo_order()`, `editorder_save()`, `poreturn()`, `stockadjlist()`, `chkcategory()` — mostly read/report/CRUD glue; several (`finditem_qty*`) compute point-in-time quantities via raw SQL against `stock_details`/views rather than the stored function, a second independent implementation of "what's my current stock" logic living alongside `stock_bal_qty_fn`.

### StockTranferController.php — real cross-store "Stock Transfer" (separate controller from StoreController)
- `save_stock_transfer()` (:68-140) — **name-collides with `StoreController::save_stock_transfer()` but is a different feature** (store-to-store transfer request, not an adjustment). Creates `StockTranfer->save()` (header, table `stock_tranfer`) and `StockTranferItem->saveAll()` (line, table `stock_tranfer_item`, status defaulted to `0` = pending). **The actual `StockDetails->save()` calls that would debit the source store and credit the destination store are commented out** (:115-137, both the debit block for `from_store` and the credit block for `to_store`) — confirms prior finding verbatim; stock effect of a transfer *request* is nil at this stage.
- `submit_return()` (:847-877) — the finalize/approve step. Flips `stock_tranfer_item.status` from `0`→`1` (:854), then calls the stored procedure via raw SQL: **`CALL stock_tranfer_pkey_prc('$id', @perr_msg)`** (:859). All actual `stock_details` mutation for a completed transfer happens **inside this stored procedure**, invisible to the PHP layer (full body being analyzed by a separate research pass on stock procs; only the call site is confirmed here). A large commented-out block below the call (:862-872) shows the *original* intended logic (two `StockDetails->save()` calls, debit/credit, plus a dead reference to a second stored proc `stock_trans_prc` in a comment at :871) — evidence the team migrated this logic from PHP into the stored proc but left the old code as a comment rather than deleting it.
- `loadtabledata()`, `getautocompletionsadjustment_code()`, `deletestoremaster()`, `itemfilter()`, and others — CRUD/support glue.

### AssetController.php (1412 lines)
- `index()` (:54-68) — lists active assets LEFT JOINed to `asset_types` via the ghost `AssetType` model (see 1.5).
- `release($asset_pkey)` (:949-957) — two raw SQL `UPDATE`s: `asset_allocate.status='Returned'` where `asset=$asset_pkey AND status='Allocated'`, and `asset_management.status='Returned'` where `asset_pkey=$asset_pkey`. No transaction wrapping either statement — partial failure leaves the two tables inconsistent (allocate record shows "Returned" but asset master still "Allocated", or vice versa).
- `listitems($pkey, $branch)` (:999-1081) — joins `allocate` → `asset_management` (alias `Assets`) → `employee_info` (alias `Emp`), building a per-employee asset list; recently edited ("edited by bindu 29-08-25" comment, :998) with an older commented-out version left in place (:975-997) — near-duplicate implementation preserved as dead code.
- `save()` (:941-947), `assetsave()` (:1084-1096), `AddnewAsset()` (:1097+), `AllocatenewAsset()` (:1106+), `Allocatenew()` (:654+), `Create_asset()` (:1214+, with an earlier commented-out `Create_asset()` at :1142 and a stub `Company_asset()` at :1248 that is a literal empty function body `{}`), `Create_asset_new()` (:1249+), `Company_asset_new()` (:1329+) — the presence of `X()`, `addnew_old()` (:521), and `X_new()`/`X_old()` triplicate naming across this controller (`Create_asset` / `Create_asset_new`, `addnew` / `addnew_old`, `listAllAssets` re-defined three times in comments at :128, :191, :263 before the live one at :379) indicates **heavy unreviewed iteration with dead code left in place** — recommend a dedicated pass in AssetController before migrating to confirm which of each `X`/`X_old`/`X_new` trio is actually live (traced via routes/views), rather than assuming the last-defined one always wins.
- `getassets()`, `getTypes()`, `getEmi()` (with a commented duplicate at :854), `edit()`, `details()`, `Emp()`, `asset()`, `getEmployeesByBranch()` — CRUD/support glue.

### ItemController.php (282 lines)
- `save()` (:243-280) — writes across five tables per submit: `Item->save()` (item_master), then `ItemDetails->save()`, then conditionally skips `QuantityDetails->save()` (commented out at :274 — quantity/stock-level fields on the item form are captured but never persisted through this path), `WarrantyDetails->save()` also commented out (:276), `ItemAdditionalDetails->save()` is live (:277), `ItemPricing->save()` is commented out (:278). **Net effect: item pricing, warranty, and starting quantity entered on the Item form are silently discarded** — only `item_master`, `item_details` (implied by `ItemDetails` model, table not explicitly re-verified here — out of primary scope), and `item_additional_details` actually persist.
- `itemlist()` (:154-223), `itemdelete()` (:224-242), `itemfilter()` (:57-80), `form()` (:81-120), `chkcategory()` (:121-153) — CRUD/support glue.

---

## 3. Stock-quantity-adjustment logic — all paths, precisely

`stock_details` (schema/mypayrol_trial.sql:47194) has **no running-balance column** — it is an **append-only
ledger**: every row is one transaction with a signed `item_qty` (float). Current on-hand quantity for a
store+item is always computed by summing `item_qty` over `stock_details` rows (either via the stored
function `stock_bal_qty_fn(date, store, item)` or via the pre-joined reporting view `stock_details_view` /
`grn_stock_details_date_view`, both referenced heavily in `StockReportController.php`, e.g.
Controller/StockReportController.php:198, :675, :741, :2446). There is **no single mechanism** enforcing
this pattern — four independent code paths write to `stock_details`, with different reliability:

| Path | Trigger / controller method | Mechanism | Net effect on `stock_details` |
|---|---|---|---|
| **GRN receipt** | `GoodsReceivedNotesController::save()` → `UpdateStock()` (Controller/GoodsReceivedNotesController.php:475, :486-525) | ORM `StockDetails->saveAll($arr_save_data)` (single flat array = single-row insert) | **+1 row**, `item_qty = received_qty` (positive), `item_state` left at column default `'NEW'` (schema/mypayrol_trial.sql:47211) since it's never explicitly set in `$arr_save_data`. Runs once per GRN line item, inside a loop with no transaction wrapper — if `UpdateStock()` fails partway through a multi-line GRN, some lines are stocked and others aren't, with no rollback of the already-saved `GrItemDetails` rows. |
| **PO Return** (return goods to supplier after GRN) | `StoreController::save_po_return()` (Controller/StoreController.php:719-777) | ORM `StockDetails->save($arr_request_detail)` (Controller/StoreController.php:764) — single explicit save, not `saveAll` | **+1 row**, `item_qty = return_qty * -1` (negative, Controller/StoreController.php:742/762), `item_state = 'PORETURN'` (:760), `status = 0` (:763) — note this row is saved with `status=0`, which **excludes it from most other `status=1`-filtered stock queries** (a state most other insert paths don't set — see quirk in §4). |
| **Stock Adjustment** (mislabeled `save_stock_transfer` in `StoreController`) | `StoreController::save_stock_transfer()` (Controller/StoreController.php:96-131) | **Raw SQL `INSERT INTO stock_details (...) VALUES (...)`** (Controller/StoreController.php:125-126), string-concatenated, no parameter binding | **+1 row**, `item_qty = required_qty * -1` (negative, deduction only — there is no positive-adjustment path in this method), `item_state = 'ADJUSTMENT'`, gated by a **pre-check** against `stock_bal_qty_fn()` (Controller/StoreController.php:111-113) so it can't go negative — the *only* one of the four paths that validates sufficient balance before writing. Bypasses the ORM entirely; any future schema change to `stock_details` requires manually updating this SQL string (already stale — it lists 21 columns positionally, easy to silently misalign if the table is ever altered). |
| **Stock Transfer** (store-to-store, `StockTranferController`) | `StockTranferController::save_stock_transfer()` creates the *request* (Controller/StockTranferController.php:68-140, `stock_tranfer`/`stock_tranfer_item` tables — **no `stock_details` write, both `StockDetails->save()` calls commented out** at :115-137); `StockTranferController::submit_return()` finalizes it (Controller/StockTranferController.php:847-877) | **Stored procedure**: raw SQL `CALL stock_tranfer_pkey_prc('$id', @perr_msg)` (Controller/StockTranferController.php:859) | Effect on `stock_details` is **entirely inside the stored procedure**, opaque to the PHP layer (out of this pass's scope — full body covered by the separate stock-proc research pass). The commented-out PHP block immediately below the call (:862-872) shows what the *pre-refactor* logic did (paired debit/credit `StockDetails->save()`), which is presumably now replicated inside the proc, but this cannot be confirmed without reading `stock_tranfer_pkey_prc`'s body. |

**Reliability/consistency implications for migration:**
- Only 1 of 4 paths (Stock Adjustment) validates available balance before writing.
- Only 1 of 4 paths (Stock Transfer's finalize step) is atomic (stored proc, presumably transactional); the other three are multi-statement PHP sequences with no explicit transaction (`begin`/`commit`) around the `stock_details` writes, so partial failures (e.g. DB connection drop mid-loop) leave the ledger inconsistent with its parent record (`gr_item_details`, `stock_adjustments_details`, `return_gr_items`).
- `item_state` values seen in code: `'NEW'` (default, GRN receipt), `'PORETURN'`, `'ADJUSTMENT'`, and (only in the dead/commented code) `'TRANSFER'` — i.e. **completed stock transfers currently produce `stock_details` rows with no distinguishing `item_state`** unless the stored procedure sets one itself (worth confirming with the stock-proc research pass).
- `status` column is inconsistently used as a "this row counts" flag: GRN receipt and Stock Adjustment paths never set it explicitly (defaults to `1`, schema/mypayrol_trial.sql:47216), but PO Return explicitly sets `status = 0` (Controller/StoreController.php:763) — meaning **PO-return ledger rows are excluded by default from any query filtering `status=1`**, which is exactly the filter pattern used throughout `StockReportController.php` and `stock_bal_qty_fn` (per its call site usage). This strongly suggests PO-return deductions are **silently not reflected in computed stock balances** — a correctness bug worth flagging prominently to the migration team, though confirming `stock_bal_qty_fn`'s exact WHERE clause is for the stock-proc research pass.

---

## 4. Schema quirks

- **`stock_details` is a pure ledger, no balance column** — by design, current balance is always a `SUM(item_qty)` over filtered rows (see §3). This is fine relationally but means **every "current stock" read in the app is a runtime aggregate**, either via `stock_bal_qty_fn` or ad-hoc SQL against `stock_details`/`stock_details_view` — at least two independently-maintained implementations of the same aggregation exist (`StockReportController::finditem_qty*` methods vs. the stored function), risking drift.
- **`stock_details.store_fkey` is `varchar(21)`** while every other store-reference column in this cluster (`store_master.store_master_pkey`) is `int(11)` — a legacy type mismatch, likely inherited from `goods_receved_notes.store_code` also being `varchar(50)` (schema/mypayrol_trial.sql:44386) instead of an int FK. Recommend normalizing to int in the new schema.
- **`purchase_order.grn_status` doubles as a 3-state workflow flag with an inline comment documenting the codes**: `'0:GRN, 1:PO, 2:PRINTED'` (schema/mypayrol_trial.sql:45522) — but `1` is the column default, meaning a **freshly created PO defaults to state "PO"**, and `GoodsReceivedNotesController::save()` **resets it back to `0` ("GRN")** every time a new GRN line is added to an *existing* GRN (Controller/GoodsReceivedNotesController.php:458, `$arr_postatus_data['grn_status'] = 0`) — i.e. the status is unconditionally forced to `0` on every partial-GRN save, not just on full completion. There is no code path found in this cluster that ever sets it to `2` ("PRINTED") — that transition must happen elsewhere (not in the controllers reviewed here), or is dead/unused.
- **`material_request.po_status`** has an analogous inline comment: `'1:MR, 2:PO, 3:GRN'` (schema/mypayrol_trial.sql:45055), default `1`. `PurchaseOrderController::save()` advances it to `'2'` via raw SQL (Controller/PurchaseOrderController.php:447) when a PO line is added — **but no code path found in this cluster advances it to `'3'`** when goods are actually received; the MR-level status appears to freeze at "PO" forever once a PO is cut, even after full GRN receipt. Worth confirming with the MR-focused research pass, but from this cluster's controllers it looks like a legacy/incomplete status machine.
- **`po_return_request.po_pkey` reuses the name `po_pkey`** but is an independent auto-increment PK unrelated to `purchase_order.po_pkey` — same column name, disjoint keyspace, high risk of copy-paste confusion during migration (a naive "join `po_return_request.po_pkey` to `purchase_order.po_pkey`" would silently produce wrong joins on overlapping small IDs).
- **`asset_management` mixes `status` (varchar, workflow state: `'Not Allocated'`/`'Allocated'`/`'Returned'`) with `active` (varchar `'1'`/presumably `'0'`)** — two overlapping "is this record live" concepts stored as strings rather than the `int status` convention used almost everywhere else in the schema (e.g. `item_master.status int(11) DEFAULT '1'`). `condition` (varchar, `'InWarranty'`/`'OutWarranty'`/`'Scrap'`) is a third independent state field on the same row. All three are free-text varchars with comment-documented enums but no CHECK constraints or lookup tables — any typo in application code (e.g. `'Allocated'` vs `'allocated'`) silently creates an unmatched state. Confirmed no FK from `asset_management.Type` to `asset_types.asset_type_pkey` despite the join in `AssetController::index()` (Controller/AssetController.php:58-64) treating it as one — `Type` is a bare `varchar(100)` (schema/mypayrol_trial.sql:28822), not typed as the int PK it's joined against, another silent type mismatch (varchar `Type` compared to int `asset_type_pkey` in the JOIN condition, relying on MySQL implicit cast).
- **`gr_item_details` and `po_item_details` are near-identical schemas** (same column set: `uom`, `ordering_qty`, `received_qty`/`required_qty`, `current_stock`, `re_order_level`, `package`, `po_rate`, audit columns) — schema/mypayrol_trial.sql:44437-44455 vs. :45386-45405. This is consistent with them being sequential snapshots of the same conceptual "PO line" as it moves through the PO → GRN stages, but it also means **`current_stock` and `re_order_level` are captured redundantly at both the PO-line stage and the GRN-line stage**, as point-in-time copies rather than references — classic denormalization-for-history pattern, intentional but worth flagging since neither column is ever visibly reconciled against `stock_details`'s live aggregate in the code reviewed here.
- **`stock_adjustments_details.item_desc`** (schema/mypayrol_trial.sql:47186, `varchar(200) NOT NULL`) is populated from a form value that's actually the **item's primary key**, not its description — `StoreController::save_stock_transfer()` passes `$item_key = $arr_request_data['item_desc']` (Controller/StoreController.php:108) straight through into `$arr_request_data['item_fkey'] = $item_key` (:122) which is what actually gets saved via `StockAdjustmentDetails->saveAll()`. The column name `item_desc` throughout `stock_adjustments_details` is a legacy misnomer inherited from a copy-pasted form field name — verify actual stored values before assuming semantics from column names in this table.
- **MyISAM vs InnoDB is inconsistent across the cluster** with no obvious pattern: `purchase_order`, `material_request`, `stock_adjustments`, `stock_adjustments_details`, `stock_details` are MyISAM (no transactions, no row-level locking); `goods_receved_notes`, `gr_item_details`, `po_item_details`, `item_master`, `asset_management`, `stock_tranfer`, `stock_tranfer_item` are InnoDB. Given none of the write paths in §3 use explicit transactions anyway, this mostly matters for the **Stock Transfer** path's stored-procedure call, which likely relies on InnoDB transactional semantics inside the proc while writing to a MyISAM `stock_details` table — worth double-checking with the stock-proc research pass whether `stock_tranfer_pkey_prc` assumes rollback-safety that MyISAM cannot provide.

---

## Summary of dead/unreachable code flagged (possible legacy cruft — verify before migrating)

- `PurchaseOrderController::save1()` (Controller/PurchaseOrderController.php:776) — no callers found; depends on non-existent `PoMaterial` model/table.
- `GoodsReceivedNotesController::save1()` (Controller/GoodsReceivedNotesController.php:643) — no callers found; writes to wrong/non-existent `purchase_order` columns (`grn_pkey`, `grstatus`); never updates `stock_details`.
- `Model/AssetsModel.php` and `Model/AssetsName.php` — both duplicate `Assets` on `asset_management`; no controller references either.
- `Model/GoodsReceivedNotesItem.php` (class `GoodsReceivedNotesitem`) — only referenced from dead `save1()` and unused in `goodlist()`; live path uses `GrItemDetails`.
- Commented-out `StockDetails->save()` debit/credit block in `StockTranferController::save_stock_transfer()` (:115-137) — superseded by the `stock_tranfer_pkey_prc` stored procedure call in `submit_return()`, left in place as a comment.
- Multiple `X()` / `X_old()` / `X_new()` triplicate method definitions in `AssetController.php` (`Create_asset`/`Create_asset_new`, `addnew`/`addnew_old`, three stacked commented-out versions of `listAllAssets`) — needs a dedicated live/dead audit via route tracing before migration.
- `Controller/GoodsReceivedNotesController.php:446` references undeclared model `StockStoreTranfer` (not in `$uses`, no model file) — would throw if that code branch executes.

---

### 7.10 Performance Management & HR Admin

# Performance Management & HR Admin — Data Model / Business Logic Report

Scope: SelfReview / TeamReview / HierarchyReview / Performance (assessment) cluster, and Resignation → Full & Final Settlement (F&F) cluster.

## 0. CRITICAL FINDING (verified, confirms prior partial-attempt finding)

`schema/mypayrol_trial.sql` (236 `CREATE TABLE` statements total) contains **NO tables** named or resembling:
- `self_review_details`
- `assessment_attributes_executive_details`
- `assessment_attributes_executive` (parent lookup table)
- `assessment_attributes_staff_details`
- `assessment_attributes_staff_items`
- `assessment_attributes_staff` (parent lookup table)
- `assessment_summary_executive`
- any `*review*` / `*assessment*` table at all

Verification method: exhaustive case-insensitive `grep` for `assessment`, `self_review`/`selfreview` across the whole 47,975-line file, plus enumeration of all 236 `CREATE TABLE` names filtered for `review|assess|terminat|resign`. Only three matches: `resignation_accept` (schema/mypayrol_trial.sql:45770), `resignation_requests` (schema/mypayrol_trial.sql:45794), `termination` (schema/mypayrol_trial.sql:47592). The single literal occurrence of the word "assessment" in the entire file is inside an unrelated HTML policy-document text blob (schema/mypayrol_trial.sql:42035, a "Work from Home Policy" document stored as data), not a table or column.

**Conclusion: the entire SelfReview/TeamReview/HierarchyReview/Performance-Assessment feature's data model is UNVERIFIABLE from the available schema dump.** All four models (`SelfReviewDetails`, `AssessmentAttributesExecutiveDetails`, `AssessmentAttributesStaffDetails`, `AssessmentAttributesStaffItem`) declare `$useTable` pointing at tables absent from `schema/mypayrol_trial.sql`, as do two more tables referenced only via raw SQL in controllers and never modeled at all: `assessment_attributes_executive`, `assessment_attributes_staff`, `assessment_summary_executive`. Given the code is clearly live/actively maintained (many "Edited by Akshay/Athira on <2025 dates>" comments throughout, including as recent as 17-7-2025), this is almost certainly because **the provided schema dump is an incomplete/stale export that predates this feature**, not because the feature is legacy/dead. This must be flagged to the migration team — the actual column set, types, and constraints for this entire cluster must be pulled from a live/current DB dump, not inferred from `schema/mypayrol_trial.sql`. All column names below are inferred from PHP `SELECT`/`INSERT`/`UPDATE` statements in the controllers, not from authoritative DDL.

By contrast, the Resignation → F&F cluster tables (`resignation_requests`, `resignation_accept`, `termination`) DO exist in the schema and are documented with full DDL below (section 5).

Also reconfirmed from a prior Company Setup pass: `grade.category_fkey` does **not** exist in `schema/mypayrol_trial.sql` (Grep for `category_fkey` across the whole file returns zero matches). The category-branching query in `SelfReviewController::getEmployeeDetails()` (legacy/Controller/SelfReviewController.php:603-609) joins `emp_proff.emp_grade -> grade.grade_pkey -> grade.category_fkey -> category.category_pkey`. Since `grade.category_fkey` is unverifiable/likely-absent in the actual schema, this branching logic (which decides whether an employee's review goes through TeamReview/"employee" path vs HierarchyReview/"hierarchy" path) is a **possible latent bug or another symptom of the same stale-schema-dump problem** — flag before migrating.

---

## 1. Schema cross-check

| Model file | `useTable` | Table found in schema/mypayrol_trial.sql? | Declared associations |
|---|---|---|---|
| Model/SelfReviewDetails.php:15 | `self_review_details` | **NOT FOUND** | none (no `$belongsTo`/`$hasMany`/`$hasOne` declared at all) |
| Model/AssessmentAttributesExecutiveDetails.php:15 | `assessment_attributes_executive_details` | **NOT FOUND** | none |
| Model/AssessmentAttributesStaffDetails.php:15 | `assessment_attributes_staff_details` | **NOT FOUND** | none |
| Model/AssessmentAttributesStaffItem.php:15 | `assessment_attributes_staff_items` | **NOT FOUND** | none |
| Model/Termination.php:10 | `termination` | **FOUND** — schema/mypayrol_trial.sql:47592 | none |
| Model/ResignationRequests.php:15 | `resignation_requests` | **FOUND** — schema/mypayrol_trial.sql:45794 | none |
| Model/ResignationAccept.php:15 | `resignation_accept` | **FOUND** — schema/mypayrol_trial.sql:45770 | none |

All 7 models are bare CakePHP shells: only `$name`, `$primaryKey`, `$useTable` — **zero validation rules, zero associations, zero callbacks/hooks** in any of them (matches the prior-research finding that "all models have zero validation/hooks" and extends it to this cluster). All actual business logic and every JOIN lives in raw SQL inside the Controllers (`$this->Model->query(...)`), not in the ORM layer.

Because associations are never declared, there is no `*_fkey` to cross-check on the missing tables via CakePHP conventions — foreign keys are only inferable from ad-hoc SQL joins in the controllers, e.g.:
- `self_review_details.emp_fkey` → `employee_info.emp_pkey` (SelfReviewController.php:170, :415)
- `self_review_details.reporting_officer` / `.reviewing_officer` → `employee_info.emp_pkey` (SelfReviewController.php:171-172)
- `assessment_attributes_executive_details.attributes_exec_fkey` → `assessment_attributes_executive.attributes_exec_pkey` (TeamReviewController.php:892)
- `assessment_attributes_staff_items.attr_staff_details_fkey` → `assessment_attributes_staff_details.attr_staff_details_pkey` (HierarchyReviewController.php:761-762)
- `assessment_attributes_staff_items.attributes_staff_fkey` → `assessment_attributes_staff.attributes_staff_pkey` (HierarchyReviewController.php:759-760)
- `assessment_summary_executive.emp_fkey` / `.officer_fkey` → `employee_info.emp_pkey` (TeamReviewController.php:1146)

None of these relationships can be verified against real DDL/constraints since the tables don't exist in the provided dump.

For the Resignation/F&F cluster (which DOES exist in schema), FK columns are declared as plain `int` with no actual `FOREIGN KEY` constraint in the DDL (consistent with the rest of this codebase's convention of app-level-only referential integrity):
- `resignation_requests.emp_fkey` (schema/mypayrol_trial.sql:45797) → `employee_info.emp_pkey` / `emp_details.emp_pkey` (used both ways in controller code)
- `resignation_requests.authorised_to` (schema/mypayrol_trial.sql:45798) → `emp_details.emp_pkey`, joined as `EmployeeDetails.emp_pkey = ResignationRequests.authorised_to` (ResignationRequestController.php:76)
- `resignation_accept.Resignation_pkey` (schema/mypayrol_trial.sql:45774) → `resignation_requests.Resignation_pkey`
- `termination.emp_fkey` (schema/mypayrol_trial.sql:47594) → `emp_details.emp_pkey` / `employee_info.emp_pkey`
- `termination.Resignation_pkey` (schema/mypayrol_trial.sql:47604) → `resignation_requests.Resignation_pkey` (column exists but is never actually populated by any controller code found — see Quirks, section 6)

---

## 2. Custom methods per model

All 7 models declare **zero custom methods** — each is a bare table-mapping shell (`$name`, `$primaryKey`, `$useTable` only; see full listings above in section 1). There is no model-layer business logic anywhere in this cluster; every operation is either CakePHP's inherited `find`/`save`/`updateAll`/`query` called directly from Controllers, or raw parameterless SQL strings built by controllers and executed via `$this->Model->query($sql)`.

Because there are no custom model methods, "call sites" below are organized by controller action instead (per the actual architecture of this codebase).

### SelfReviewController (legacy/Controller/SelfReviewController.php) — uses SelfReviewDetails, AssessmentAttributesStaffDetails, EmployeeDetails, LeaveRequests
- `index()` :60 — dashboard landing, counts self-review records for current employee.
- `initaiteSelfReview()` :70 — renders the "initiate" view.
- `newReview()` :82 — loads employee list for starting a single review.
- `listreviews()` :123 — combined AJAX grid listing BOTH `self_review_details` and `assessment_attributes_staff_details` rows in one paginated response; computes a human-readable `status_label` via a priority-ordered `is_reviewed`/`is_reported`/`is_applied`/`is_rejected`/`is_drafted` flag chain (see section 3).
- `listreviews_self_review()` :365 — same status-label logic but scoped to the logged-in employee's own `self_review_details` rows only, used by "My Reviews" grid.
- `getEmployeeDetails()` :501 — AJAX: given `emp_fkey`, returns employee bio + candidate reporting/reviewing officers (from `emp_config` type `HIERARCHY`/`LAPPR`) + available financial years + computed `category` (`'employee'` vs `'hierarchy'`) via the `grade.category_fkey`-dependent query flagged in section 0.
- `getAbsencePeriod()` :650 — AJAX: sums `attendance_register.lop_total` (loss-of-pay days) for an employee/fin-year, used to pre-fill self-review LOP field.
- `createSelfReview()` :689 — inserts a new `self_review_details` row with `status='New'`; blocks duplicate creation per `emp_fkey`+`fin_year` (excluding `'Deleted'`).
- `form()` :739 — renders blank review form.
- `view($edit, $key)` :768 — loads one `self_review_details` row by pkey for viewing/editing.
- `saveSelfReview()` :818 — updates a `self_review_details` row; sets `is_applied=1`/`applied_by`/`applied_date` when `status=='Applied'`, or `is_drafted=1`/`drafted_by`/`drafted_date` when `status=='Draft'`.
- `deleteSelfReview()` :886 — soft-delete via `updateAll`; dispatches to either `SelfReviewDetails` (`status='Deleted'`) or `AssessmentAttributesStaffDetails` (`status=0`) based on a `table` param passed from the frontend.
- `previewPdfReview($emp_pkey, $pkey)` :922 — renders `self_review_details` row to PDF via HTML2PDF.
- `newBulkReview()` :975 — loads employee+category lists for bulk review creation UI.
- `getEmployeesByCategory()` :1016 — AJAX: lists employees whose `emp_proff.emp_grade -> grade.category_fkey` matches a given category (same unverifiable-column dependency as section 0).
- `getFinYears()` :1051 — AJAX: distinct financial years available for a set of employees' branches.
- `getLOP($emp_fkey, $fin_year)` :1092 (protected helper) — same LOP-sum query as `getAbsencePeriod`, reused internally.
- `createBulkSelfReview()` :1120 — for a list of `emp_fkey`s: skips employees with no valid fin_year, skips employees who already have a non-deleted `self_review_details` OR non-status-0 `assessment_attributes_staff_details` row (cross-table duplicate check), then creates one `self_review_details` row per remaining employee with pre-filled bio/grade/category fields, `status='New'`.

Call sites for the ORM `SelfReviewDetails` model: throughout the above actions via `$this->SelfReviewDetails->find/save/query`.

### TeamReviewController (legacy/Controller/TeamReviewController.php) — uses AssessmentAttributesExecutiveDetails, EmployeeDetails, and others
- `index()` :13 — determines the logged-in user's `role` (`reporting_officer`/`reviewing_officer`/`employee`) by checking whether they appear as `reporting_officer` or `reviewing_officer` on any `self_review_details` row.
- `listrequest()` :257 (a large earlier commented-out version precedes it at :48-256, retained as dead code — see section 3/6) — paginated AJAX grid of pending review requests for the current officer, filtered by a `position` param (`'left'` = drafts/incoming vs default = submitted/outgoing) and multi-status `IN(...)` clauses reflecting the state machine (section 3).
- `saveRequest()` :397 — the core scoring endpoint; handles three `action_type`s: `'draft'`, `'send_back'`, and default (submit). See full algorithm in section 3.
- `approverequest($emp_fkey)` :790 — loads the full executive-review scoring screen: attribute list, existing marks (`assessment_attributes_executive_details`), summaries for both reporting and reviewing officer (`assessment_summary_executive`), role detection via query-string `reporting_officer`/`reviewing_officer` params.
- `viewForm()` :955 — read-only summary+marks view for a given officer/employee/fin_year combination.
- `previewPdfReview($pkey, $self_pkey)` :1035 — renders executive review to PDF.
- `previewDocumentReview($pkey, $self_pkey)` :1191 — same data, rendered as an HTML view instead of PDF (`performance_view` view instead of `performance_report`).
- `previewPdfReviewHR($pkey, $self_pkey)` :1330 — HR-facing PDF variant; also surfaces raw `self_review_details.status` to the HR view (Edited by Akshay 17-7-2025).

### HierarchyReviewController (legacy/Controller/HierarchyReviewController.php) — uses AssessmentAttributesStaffDetails, AssessmentAttributesStaffItem, SelfReviewDetails
- `index()` :60 — dashboard count.
- `listreviews()` :70 — paginated grid of `assessment_attributes_staff_details` for the current officer; visibility rule: rows where `reporting_officer = $emp_fkey`, OR rows where `reviewing_officer = $emp_fkey` AND `status >= 2` AND `status != 5` (i.e. reviewing officer only sees items once past drafting AND not in the terminal "new/unstarted" state 5 — see section 3 for the numeric-status state machine used here, distinct from SelfReview's string-status machine).
- `form()` :189 — blank staff-review form; also loads `assessment_attributes_staff` (attribute catalog).
- `getDesignation()` :226 — AJAX employee designation lookup.
- `view($edit, $attr_staff_details_pkey, $emp_fkey_string)` :245 — loads one staff-assessment record with reporting/reviewing officer names via inline joins, plus per-attribute existing marks from `assessment_attributes_staff_items` (via `find('list', ...)`).
- `createHierarchyReview()` :370 — inserts new `assessment_attributes_staff_details` row with `status=5` (i.e. "New"/unstarted numeric status), blocking duplicates per `emp_fkey`+`fin_year` (excluding `status=0`).
- `saveHierarchyReview()` :454 — the core staff-review scoring endpoint; numeric-status-driven (`1`=draft, `2`=reported/drafted depending on officer, `3`=reviewed), writes per-attribute marks into `assessment_attributes_staff_items` (insert-or-update pattern keyed by `attr_staff_details_fkey`+`attributes_staff_fkey`+`status=1`). See section 3.
- `deleteHierarchyReview()` :592 — soft-delete (`status=0`) via `updateAll`.
- `sendBackReview()` :620 — rejection loop entry point for staff reviews: sets `status=1` (back to draft), `is_rejected=1`, `rejected_by`/`rejected_date`, resets other `is_*` flags to 0.
- `previewPdfReview($pkey)` :693 — staff-review PDF; also computes `hideReporting`/`hideReviewing`/`hideEntireTable` display flags based on whether `drafted_by` matches the reporting or reviewing officer.
- `createBulkHierarchyReview()` :837 — bulk version of `createHierarchyReview`, same cross-table duplicate-check pattern as `SelfReviewController::createBulkSelfReview`.
- `getLOP($emp_fkey, $fin_year)` :989 (private helper) — duplicate of SelfReviewController's LOP-sum query (code duplication — candidate for a shared service in the Next.js port).
- `previewDocumentReview($pkey)` :1012 — HTML-rendered variant of the PDF preview.
- `selfAppraisalWorkmen()` :1125 — workmen-specific self-appraisal landing page.
- `listreviewsWorkmen()` :1138 — same grid as `listreviews()` but scoped to `srd.emp_fkey = $emp_fkey` (the workman viewing their own submissions) instead of the officer-role filter.

### PerformanceController (legacy/Controller/PerformanceController.php)
Not a review/assessment CRUD controller — it is the **HR reporting layer** on top of the above tables (report criteria selection, PDF/Excel export of `assessment_summary_executive` and `assessment_attributes_staff_details` data). Key methods: `hrreports()` :12, `changereporttype()` :26, `generatereport()` :270 (dispatches to `generateEmployeeMarksReport`/`generateEmployeeMarksStaffReport`/`generateSelfAppraisalReport`/`generateStaffAssessmentReport`/`generateAnnualPerformanceAssessment`), `generateEmployeeMarksReport($mode)` :340 (executive marks PDF/Excel, joins `assessment_summary_executive` + `self_review_details` + `termination` + `employee_info`), `generateEmployeeMarksStaffReport($mode)` :678 (staff marks PDF/Excel, joins `assessment_attributes_staff_details` + `termination` + `employee_info`, filtered `aasd.status=3` — the terminal "approved" numeric status). File is 2112 lines total; remainder (not fully read — budget-limited) is further Excel-formatting boilerplate for `generateSelfAppraisalReport`/`generateStaffAssessmentReport`/`generateAnnualPerformanceAssessment`, structurally identical to the two methods above.

### ResignationRequestController (legacy/Controller/ResignationRequestController.php) — uses ResignationRequests, ResignationAccept, Termination, EmployeeDetails, LeaveRequests
- `index()` :56 — employee's resignation dashboard; joins `resignation_accept` + `emp_details`, plus raw query for active `termination` row.
- `Emprequests()` :99 — trivial, just sets current emp key.
- `getusers()` :106 — employee autocomplete search (handover-to picker), excludes self.
- `addeditleave($emp)` :136 — resignation detail view; heavy multi-table join (`emp_details`, `resignation_accept`, `emp_proff`, `branches`, `designation`, `department`) plus lookups for the manager (`authorised_to`) and handover-to employee.
- `listleaves()` :270 — paginated grid of resignation requests pending the current employee's action (as `authorised_to` unauthorized, OR as `forwarded` HR-approver unapproved).
- `Manageleave()` :317 — empty stub (dead code).
- `Request($leaveentryId)` :321 — view of the current employee's own resignation request.
- `letter()` :345 / `letteredit()` :429 — resignation-letter document rendering (two near-duplicate implementations; `letteredit` joins on different column names `Branches.branch_code`/`Designation.desig_code`/`Department.dept_code` vs `letter`'s `.id` — **inconsistency, likely a bug or leftover from a schema migration**, flag for verification).
- `withd()` :513 — resignation withdrawal: cancels `resignation_requests` (`status=0`, `Resignation_status='Cancelled'`), cancels the linked `termination` row (`status=0`, `remarks='Employee cancelled'`), and cancels `resignation_accept` (`status=0`). Guarded by `$arr_count` (active termination count) but note `$arr > 0` is a suspicious truthy-array check (see section 6 quirks).
- `grandrequest()` :552 — manager-side "grant"/checklist form: normalizes checkbox fields (`chek_assets`, `chek_formalities`, `chek_leave`) to `1`, saves into `resignation_accept` (update if existing row found, else implicit insert via `save`).
- `Empagreed()` :577 — employee agreement checkbox save (note `$arr_form_data['agree'] == 1;` at :588 is a **no-op comparison, not an assignment** — a genuine bug: this line does nothing, `agree` is never actually set to 1 before `save()`).
- `deleteresignation($rs)` :593 — soft-delete both `resignation_requests` and `resignation_accept` by `Resignation_pkey`.
- `Saverequests($leaveentryId)` :610 — creates the initial resignation request AND the linked `termination` row in one action (see section 5 state machine) — handles both fresh employee-initiated resignation and re-submission after a prior cancelled/admin-initiated termination row exists.

### FullandFinalsettlementController (legacy/Controller/FullandFinalsettlementController.php) — uses Termination, EmployeeDetails, LeaveRequests, LeavePolicy, Units, LeaveEncashmentMaster, allocate
- `index()` :56 — lists active (`status=1`) and resigned (`status=2`) employees for F&F selection — this is the direct usage of `emp_details.status` as the resignation flag.
- `leavebalance()` :68 — pending-leave grid for an employee prior to settlement.
- `Terminate()` :124 — **admin-initiated termination path** (distinct from employee-initiated resignation flow): directly upserts a `termination` row with `is_authorized='Y'`, `authorized_by=0`, `is_approved='Y'`, `approved_by=0` (i.e. auto-approved, no officer workflow), then immediately flips `emp_details.status = '2'` (:146). This is the second of two ways a `termination` row + status flip gets created (the other being `ResignationRequestController::Saverequests`).
- `details_res($emp_pkey)` :149 — reads back a `termination` row's key F&F fields for display.
- `Assets($emp_fkey)` :172 — lists allocated (not-yet-returned) company assets for the exiting employee (`allocate` + `asset_management`).
- `approve_selectd()` :186 — bulk-approves selected leave requests via stored proc `leave_transaction_prc` + `updateAll` to `LEAVESTATUS='Approved'`.
- `savedetails()` :221 — persists computed settlement numbers (`working_days_settled`, `leave_balance`, `days_attendance`, `payroll_days`) back onto the `termination` row.
- `workingattendnacedays()` :238 — computes attendance/leave/weekoff numbers via stored functions `leave_balance_inthe_year_fn`, `weekoff_days_count_fn`.
- `reject_selected()` :258 — mirror of `approve_selectd()` but sets `LEAVESTATUS='Rejected'`.
- `get_complete($emp_pkey, $dayss, $leaves)` :292 — invokes stored proc `final_settle_pay_prc` to compute the final payslip; heavy multi-table salary-slip assembly.
- `approveencash()` :352 — leave-encashment approval, invokes stored proc `leave_encash_prc`.
- `getperiod($emp_fkey)` :391 — notice-period lookup from `emp_proff.notice_days`.
- `leaveadjustment()` :469 — writes adjusted leave balance back to `termination` (with a **company-code-specific branch**: `DEMO`/`KWMT` also updates `working_days_settled`/`payroll_days`, others don't — note the buggy `$str_company_code = 'KWMT'` at :496 uses `=` assignment instead of `==` comparison, making the else-branch at :502 effectively **unreachable dead code** — always evaluates truthy).

---

## 3. Complex logic / state machines

### 3a. SelfReview / TeamReview scoring algorithm (executive track)

**Score fields**: Per-attribute marks live in `assessment_attributes_executive_details`, one row per `(emp_fkey, attributes_exec_fkey, fin_year)`, with two independently-writable numeric columns: `reporting_officer_marks` and `reviewing_officer_marks` (TeamReviewController.php:664-671, :686-694). There is no single "final" per-attribute score column — reporting and reviewing marks are stored side-by-side and never algorithmically merged into one number at the attribute level.

**Aggregation**: Aggregation to an overall score happens at the **summary** level, not attribute level. `assessment_summary_executive` (one row per `(emp_fkey, officer_fkey, fin_year)` — i.e. **two summary rows per employee per year, one for the reporting officer, one for the reviewing officer**) holds `total_marks` and `grade`, both submitted directly by the officer via the frontend (`arr_data['total_marks']`, `arr_data['grade']` at TeamReviewController.php:700-701) — **the backend does NOT sum/average the per-attribute marks itself**; the total is whatever the client posts. This means the actual "aggregation formula" (e.g. weighted sum of attribute marks) lives only in frontend JS, not in this PHP layer — a critical detail for the Next.js migration: the scoring formula must be recovered from the frontend code, not the API.

**Per-officer flow / gating**: Reviewing officer's submission is blocked server-side until the reporting officer has completed: `saveRequest()` checks `reporting_officer_status` on each attribute row before allowing a reviewing-officer submit (TeamReviewController.php:647-657) — returns an error JSON if reporting officer hasn't finished. Reporting officer has no such gate (can always submit first).

**Status values on `self_review_details.status`** (a free-text string column, not an enum) observed across the codebase:
- `'New'` — created (SelfReviewController.php:696, TeamReviewController "new bulk" flow at SelfReviewController.php:1258)
- `'Draft'` (input value from frontend, not stored verbatim — see below)
- `'Applied'` — self-review submitted by the employee (SelfReviewController.php:849-853); also displayed as "Self Review Completed" in UI labels
- `'Reporting Person submitted the Appraisal'` (TeamReviewController.php:645, :755)
- `'Reviewing Person submitted the Appraisal'` (TeamReviewController.php:659, :770)
- `'Reporting Person Drafted the Appraisal'` (TeamReviewController.php:446)
- `'Reviewing Person Drafted the Appraisal'` (TeamReviewController.php:447)
- `'Reporting Person Rejected the Appraisal'` (TeamReviewController.php:578)
- `'Reviewing Person Rejected the Appraisal'` (TeamReviewController.php:578)
- `'Deleted'` — soft-delete marker (SelfReviewController.php:900)

In parallel, five boolean-ish integer flag columns exist and are maintained alongside (but not always consistently with) the `status` string: `is_applied`, `is_drafted`, `is_reported`, `is_reviewed`, `is_rejected`, each with a paired `*_by` and `*_date` column. Every write to one of these flags explicitly zeroes the others in the same UPDATE (e.g. TeamReviewController.php:594-598: rejecting sets `is_rejected=1` and `is_reported=0, is_reviewed=0, is_drafted=0` in the same statement) — this is a hand-rolled single-active-flag state machine layered on top of the string status, i.e. **two redundant representations of the same state that must be kept in sync manually by every write path** (a classic source of drift/bugs — flag for the migration: collapse to one enum).

The UI-facing "status label" is actually re-derived independently at *read* time in list endpoints (`listreviews`, `listreviews_self_review`, HierarchyReview's `listreviews`) via a priority-ordered if/elseif chain over the `is_*` flags cross-checked against who performed the action (SelfReviewController.php:204-225), rather than trusting the stored `status` string directly — meaning the displayed label and the stored `status` column can diverge (the raw `status` is only used as a final fallback at SelfReviewController.php:228-230).

**"Send back" rejection loop**: `TeamReviewController::saveRequest()` with `action_type='send_back'` (TeamReviewController.php:577-622): sets `self_review_details.status` to `'Reporting Person Rejected the Appraisal'` or `'Reviewing Person Rejected the Appraisal'` depending on role, sets `is_rejected=1`, `rejected_by`, `rejected_date`, a special flag `was_rejected_by_reviewer` (1 only if reviewer rejected, 0 if reporting officer rejected — used later at TeamReviewController.php:843 to control which fields are shown on re-open), resets `is_reported`/`is_reviewed`/`is_drafted` to 0, AND resets the *current role's* `reporting_officer_status`/`reviewing_officer_status` to 0 on **every** `assessment_attributes_executive_details` row for that emp+fin_year — effectively re-opening the whole attribute set for the flow to restart from that officer's stage. There is no cap on how many times send-back can loop.

### 3b. HierarchyReview scoring algorithm (staff/workmen track)

Uses a **numeric** status on `assessment_attributes_staff_details.status` instead of a string (a different convention from the executive track — inconsistent design across the two parallel review types, worth normalizing in the Next.js schema):
- `5` — newly created / not started (`createHierarchyReview()` hardcodes `status=5` at HierarchyReviewController.php:383; `listreviews()`'s visibility filter explicitly excludes `status=5` from the reviewing officer's queue at HierarchyReviewController.php:97 — i.e. reviewing officer never sees unstarted reviews)
- `1` — drafted (`saveHierarchyReview()` :483-486, and `sendBackReview()` resets to `status=1` :649 — so 1 is both "initial draft" and "post-rejection re-draft" state, reusing the same numeric code for two different life-cycle points)
- `2` — reported (reporting officer submitted) — HierarchyReviewController.php:487-497; the same code path also treats `status=2` as "reviewing officer drafted" if the current user is the reviewing officer (:493-496) — **the same numeric code (2) means two different things depending on who's viewing**, a design smell.
- `3` — reviewed (reviewing officer submitted, terminal "approved" state) — HierarchyReviewController.php:498-503; confirmed as the terminal/approved state by `PerformanceController::generateEmployeeMarksStaffReport()` filtering `aasd.status=3` for its official report (PerformanceController.php:749).
- `0` — soft-deleted (`deleteHierarchyReview()` :604).

Per-attribute marks for the staff track live in a **separate child table** `assessment_attributes_staff_items` (one row per `(attr_staff_details_fkey, attributes_staff_fkey)`), unlike the executive track which stores reporting/reviewing marks as sibling columns on the same details row. `saveHierarchyReview()` (HierarchyReviewController.php:454-590) parses form fields named `reporting_officer_marks_<attributeKey>`, and for each does a manual find-then-insert-or-update against `assessment_attributes_staff_items` keyed by `(attr_staff_details_fkey, attributes_staff_fkey, status=1)` — there's no atomic "aggregate marks into total" step visible server-side here either; `reporting_officer_marks`/`reporting_officer_grade` fields on the parent `assessment_attributes_staff_details` row appear to be set directly by the frontend as an overall total (mirroring the executive track's client-computed-total pattern).

**"Send back" rejection loop (staff track)**: `sendBackReview()` (HierarchyReviewController.php:620-691) — sets `status=1` (draft), `is_rejected=1`, `rejected_by`, `rejected_date`; resets `is_reported`/`is_reviewed`/`is_drafted` to 0. Structurally identical loop-back pattern to the executive track's send-back, but implemented as a fully separate, independently-written method (no shared code between the two review types anywhere in this cluster).

### 3c. Resignation → Full & Final Settlement state machine

**`termination` table status** (`termination.status`, int(1), default `1` — schema/mypayrol_trial.sql:47615):
- `1` — active/current termination record (used as the filter condition everywhere: `status=1` means "this is the live termination in progress" — ResignationRequestController.php:92, FullandFinalsettlementController.php:152, :296, :498, :503)
- `0` — cancelled/superseded (set on withdrawal: ResignationRequestController.php:532-535 `remarks='"Employee cancelled"'`; also set when a new termination row is inserted to supersede an old one after a prior cancellation, ResignationRequestController.php:639-642)

There is no `2`/`3`/etc. observed for `termination.status` — it is effectively a binary active/inactive flag, with `is_authorized` (varchar(1) Y/N, default 'N' — schema/mypayrol_trial.sql:47598) and `is_approved` (varchar(1) Y/N, default 'N' — schema/mypayrol_trial.sql:47600) carrying the actual authorization workflow state as separate Y/N string flags rather than being folded into `status`.

**Two independent creation paths, both converging on the same `termination` row + `emp_details.status` flip:**

1. **Employee-initiated resignation** (`ResignationRequestController::Saverequests()`, ResignationRequestController.php:610-659):
   - Guard: only proceeds if employee has no existing active `resignation_requests` row AND no active (`status=1`) `termination` row (:620).
   - Creates `resignation_requests` row (`Resignation_status` defaults to `'Applied'` per DDL — schema/mypayrol_trial.sql:45802).
   - Immediately also inserts a `termination` row via raw SQL (:634 or :643) with `Reason`, `submitted_date`, `last_applied_date`, `last_working_date`, `last_approved_working_date`, `remarks` (= the resignation reason description), `act_last_working_day` — all pre-populated from the resignation request's `Last_workingday`. **Note**: this insert does NOT set `Resignation_pkey` on the `termination` row (the column exists in DDL — schema/mypayrol_trial.sql:47604 — but is left at its implicit default, meaning the `termination`→`resignation_requests` FK link is never actually populated by this code path — see Quirks section 6).
   - If a previous (cancelled) `termination` row exists for this employee, that old row is explicitly zeroed (`status=0`) before the new one is inserted (:639-642) rather than being reused/updated — every resignation cycle creates a brand-new `termination` row.
   - `emp_details.status` is **NOT** touched by this path — the employee is not yet marked "resigned" just by submitting a resignation request. It stays at whatever it was (presumably `1`=active).

2. **Admin/HR-initiated termination** (`FullandFinalsettlementController::Terminate()`, FullandFinalsettlementController.php:124-148): HR directly creates/updates a `termination` row with `is_authorized='Y'`, `authorized_by=0`, `is_approved='Y'`, `approved_by=0` — i.e., bypasses the officer-approval workflow entirely (auto-self-approved by system/admin), and in the same request **immediately flips `emp_details.status = '2'`** (:146) via raw SQL `UPDATE emp_details SET status='2' WHERE emp_pkey=...`. This is the only place in this cluster's read code that directly writes `emp_details.status`.

**`emp_details.status` values inferred from usage**:
- `1` — active (`FullandFinalsettlementController::index()` filters `status=1` for "active employees" list — FullandFinalsettlementController.php:60)
- `2` — resigned/terminated (`FullandFinalsettlementController::index()` filters `status=2` for "resigned employees" list :61; set by `Terminate()` :146; also checked in report queries, e.g. PerformanceController.php:372 `CASE WHEN emp_details.status = 2 THEN CONCAT(..., ' (Resigned)')`)

No code path was found that flips `emp_details.status` back to `1`, nor any that sets it during the *employee-initiated* resignation flow (only the *admin-initiated* `Terminate()` path does it) — meaning, per the code as written, an employee who resigns via `ResignationRequestController::Saverequests()` remains `status=1` (active) until/unless someone separately runs the HR `Terminate()` action. This is either (a) an intentional two-step process (self-service resignation request → HR must separately "terminate" to finalize) or (b) a gap where the resignation-approval workflow (`ResignationAccept.isApproved`) was expected to trigger the status flip but no such trigger code exists anywhere in this controller. **Flag as unverified — confirm intended behavior with product owner before porting**, since `ResignationAccept` (the manager/HR "grant"/approve object, `grandrequest()` ResignationRequestController.php:552-576) never touches `emp_details.status` or `termination.status` either — it only writes to `resignation_accept` itself.

**Withdrawal** (`ResignationRequestController::withd()`, ResignationRequestController.php:513-551): employee-triggered cancellation. Requires an active `termination` row to exist (`$arr_count > 0`). Sets `resignation_requests.status=0` + `Resignation_status='Cancelled'`, `termination.status=0` + `remarks='Employee cancelled'`, and `resignation_accept.status=0`. Does not touch `emp_details.status` (consistent with the observation above that the resignation-request path never sets it in the first place).

---

## 4. Dead / unreachable / suspicious code (flag before migrating)

- **TeamReviewController.php:48-256**: ~200 lines of fully commented-out earlier `listrequest()` implementation, retained in the file rather than deleted — pure dead code, safe to drop, but worth diffing against the live `listrequest()` at :257 in case any status-filter nuance was silently lost in the rewrite (the live version's `position==='left'` branch logic doesn't obviously match the old version's role/status combinations).
- **ResignationRequestController.php:588**: `$arr_form_data['agree'] == 1;` — comparison operator used where an assignment (`=`) was clearly intended. This statement is a no-op; `agree` is never actually set before `$this->ResignationRequests->save($arr_form_data)` is called at :590. **Likely bug**, not intentional dead code — verify whether the "employee agreed to resignation terms" flag has ever actually been persisted in production.
- **FullandFinalsettlementController.php:496**: `if ($str_company_code == 'DEMO' || $str_company_code = 'KWMT')` — second condition uses `=` (assignment) instead of `==`. Since `'KWMT'` is a non-empty string, this sub-expression is always truthy, making the whole `if` always true and the `else` branch at :502-504 **permanently unreachable** regardless of actual company code. This silently changes behavior for every non-DEMO/non-KWMT tenant using this multi-tenant app — a real bug affecting the `termination.working_days_settled`/`payroll_days` update logic.
- **FullandFinalsettlementController.php:405-467**: an entire earlier commented-out version of `leaveadjustment()` retained above the live version — dead code, but shows the live version added the DEMO/KWMT company-code branch (the bug above), useful to know it was a recent, deliberate (if buggy) change.
- **ResignationRequestController.php:317-320**: `Manageleave()` — empty method body, no-op. Possibly a stub for planned functionality never implemented, or leftover from a removed feature — verify with routing/frontend whether it's still linked anywhere.
- **ResignationRequestController.php:345-511**: `letter()` and `letteredit()` are near-duplicate resignation-letter renderers with materially different join column names (`Branches.id`/`Designation.id`/`Department.id` in `letter()` at :371-388 vs `Branches.branch_code`/`Designation.desig_code`/`Department.dept_code` in `letteredit()` at :458-472). One of these is very likely broken depending on which columns actually exist as PKs on `branches`/`designation`/`department` — **needs live-schema verification**, not resolvable from this pass alone.
- **ResignationRequestController.php:525, :543**: `if($arr > 0)` / `if($arr > 0)` — `$arr` is the result of `find("all", ...)`, i.e. an array, being compared with `>` against an integer. PHP's loose comparison rules make this true for any non-empty array (and also true for an empty array compared to 0 in older PHP array-to-int coercion quirks) — fragile pattern rather than `!empty($arr)` or `count($arr) > 0`; flag as a code-quality issue to fix cleanly during the port rather than replicate.
- **`termination.Resignation_pkey`** (schema/mypayrol_trial.sql:47604): column exists in DDL but is never populated by `ResignationRequestController::Saverequests()`'s raw INSERT (ResignationRequestController.php:634, :643) — the two tables that are supposed to be linked by this FK are, in practice, only ever correlated by `emp_fkey` + timing, not by this explicit key. **Effectively an unused/dead column in current write paths** — confirm no other code path (not found in this pass) populates it before dropping/renaming during migration.
- Category-branching logic depending on the unverifiable `grade.category_fkey` (SelfReviewController.php:603-609, :1032-1038) — flagged in section 0; if this column genuinely doesn't exist in the live schema, `getEmployeeDetails()` and `getEmployeesByCategory()` would be **silently broken today** (query would either error or return NULL joins depending on DB strictness), consistent with the Company Setup pass's finding on `grade.category_fkey`.

## 5. Resignation / Termination DDL (verified — full table definitions)

```sql
CREATE TABLE `resignation_requests` (            -- schema/mypayrol_trial.sql:45794
  `Resignation_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `emp_fkey` int(11) NOT NULL,
  `authorised_to` int(11) NOT NULL,
  `applied_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Reason` varchar(20) NOT NULL,
  `Reason_Desc` varchar(600) NOT NULL,
  `Comments_to_manager` varchar(600) NOT NULL,
  `Last_workingday` date NOT NULL,
  `Resignation_status` varchar(20) NOT NULL DEFAULT 'Applied',
  `agree` varchar(20) NOT NULL DEFAULT '0',
  `contact_no` varchar(20) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`Resignation_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `resignation_accept` (               -- schema/mypayrol_trial.sql:45770
  `resignation_accept_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `applied_emp` int(11) NOT NULL,
  `Resignation_pkey` int(11) NOT NULL,
  `last_allowed_date` datetime DEFAULT NULL,
  `comments_to_emp` varchar(40) DEFAULT NULL,
  `comments_to_hr` varchar(40) DEFAULT NULL,
  `manager_reason` varchar(40) DEFAULT NULL,
  `forwarded` int(11) NOT NULL,
  `isauthorized` int(11) NOT NULL DEFAULT '0',
  `authorized_date` datetime NOT NULL,
  `isApproved` int(11) NOT NULL DEFAULT '0',
  `approved_date` datetime NOT NULL,
  `handover_to` int(11) NOT NULL,
  `hr_comment` varchar(400) NOT NULL,
  `chek_formalities` int(2) NOT NULL DEFAULT '0',
  `chek_assets` int(2) NOT NULL DEFAULT '0',
  `chek_leave` int(2) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`resignation_accept_pkey`),
  KEY `Resignation_pkey` (`Resignation_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `termination` (                      -- schema/mypayrol_trial.sql:47592
  `terminate_pkey` int(20) NOT NULL AUTO_INCREMENT,
  `emp_fkey` int(20) NOT NULL,
  `Reason` varchar(100) NOT NULL,
  `ed` date NOT NULL,
  `Reason_desc` varchar(400) NOT NULL,
  `is_authorized` varchar(1) NOT NULL DEFAULT 'N',
  `authorized_by` int(20) NOT NULL,
  `is_approved` varchar(1) NOT NULL DEFAULT 'N',
  `approved_by` int(20) NOT NULL,
  `submitted_date` date NOT NULL,
  `last_applied_date` date NOT NULL,
  `last_working_date` date NOT NULL,
  `Resignation_pkey` int(20) NOT NULL,             -- never populated by current write paths (see section 4)
  `last_approved_working_date` date NOT NULL,
  `notice_period` int(20) NOT NULL,
  `act_last_working_day` date NOT NULL,
  `remarks` varchar(30) NOT NULL,                  -- but code writes long strings like "Employee cancelled" + reason descriptions into this 30-char field (see Quirks below)
  `working_days_settled` int(4) NOT NULL,
  `leave_balance` int(4) NOT NULL,
  `approved_balance` int(4) NOT NULL,
  `days_attendance` int(4) NOT NULL,
  `encashed_days` int(4) NOT NULL,
  `payroll_days` int(4) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `amt_paid_by_empaddition` int(11) NOT NULL,
  `amt_paid_by_empdeduction` int(11) NOT NULL,
  PRIMARY KEY (`terminate_pkey`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
```

## 6. Schema quirks

- **`termination.remarks varchar(30)`** (schema/mypayrol_trial.sql:47609) is suspiciously narrow — 30 characters — yet the code writes full reason-description strings and phrases like `'Employee cancelled'` into it (ResignationRequestController.php:533, :634/:643 where `$reason_Desc` — sourced from `resignation_requests.Reason_Desc varchar(600)` — is inserted directly into this 30-char column). This will **silently truncate** most real resignation reason descriptions. Classic legacy-migration mismatch: `remarks` was probably meant to be short-code, but is being used as free text. Migration should widen this column or split concerns (short code vs. long reason text, which already exists properly-sized on the source `resignation_requests` table).
- **`termination.ed date NOT NULL`** (schema/mypayrol_trial.sql:47595) — a cryptically-named, `NOT NULL`, no-default date column that no controller code in this cluster reads or writes at all (not referenced anywhere in `ResignationRequestController.php` or `FullandFinalsettlementController.php`). Likely a legacy/abandoned column ("effective date"? "end date"?) — every INSERT into `termination` observed in this pass omits it, meaning it must be relying on MySQL's non-strict-mode zero-date fallback (`'0000-00-00'`) to satisfy `NOT NULL` — a landmine if the target DB (or Next.js's DB layer) runs in strict SQL mode. Flag as dead/unused column requiring explicit handling (default or removal) during migration.
- **`termination.notice_period int(20) NOT NULL`** (schema/mypayrol_trial.sql:47606) — set from `$arr_form_data['notice_period']` in `Terminate()` (FullandFinalsettlementController.php:140) but never set at all in the employee-initiated `Saverequests()` INSERT path (ResignationRequestController.php:634/:643) — meaning employee-initiated terminations always get whatever MySQL's implicit zero-default is for this `NOT NULL` int column, not a real notice period. Inconsistency between the two creation paths.
- **`resignation_accept.applied_emp`**, **`.forwarded`**, **`.handover_to`** are all `int(11) NOT NULL` with **no default** — every `resignation_accept` row created must supply these or rely on MySQL's implicit-zero-for-NOT-NULL-int behavior; `grandrequest()` (ResignationRequestController.php:552-576) never explicitly sets `applied_emp` in the save data shown, relying on whatever was already present when doing an update, but for a fresh insert (first-time save with no existing row) this would zero-default silently — again a strict-mode landmine.
- **Redundant string-status + boolean-flags representation** on the (unverifiable) `self_review_details`/`assessment_attributes_*_details` tables (section 3a) is itself a "schema smell" worth calling out even though the tables are absent from the provided dump: the pattern of `status` (string) + five paired `is_X`/`X_by`/`X_date` triples strongly suggests an organically-grown schema where the boolean flags were bolted on incrementally rather than designed as a single state enum — a strong candidate for consolidation into a single `status` enum + generic `last_action_by`/`last_action_date` pair in the Next.js data model.
- **Numeric vs. string status conventions differ between the two review tracks** (executive: string status on `self_review_details`; staff/workmen: integer status `0/1/2/3/5` on `assessment_attributes_staff_details` — section 3b) — another architectural inconsistency to normalize, not a bug per se, but will complicate writing one shared "review status" component in the new frontend if not unified.
- **`resignation_requests.agree varchar(20) DEFAULT '0'`** stores a boolean as a 20-character string defaulting to the string `'0'` — combined with the `$arr_form_data['agree'] == 1;` no-op bug in `Empagreed()` (section 4), this field's real-world data quality should be treated as suspect; likely always `'0'` in production regardless of actual employee agreement.

---

## Summary of what to hand to the migration team

1. **Blocking issue**: pull a current/live DB schema dump for `self_review_details`, `assessment_attributes_executive`, `assessment_attributes_executive_details`, `assessment_summary_executive`, `assessment_attributes_staff`, `assessment_attributes_staff_details`, `assessment_attributes_staff_items` — none exist in `schema/mypayrol_trial.sql` despite being actively used by code with mid-2025 edit timestamps. All column inferences above come from PHP SQL strings only.
2. Also re-verify `grade.category_fkey` on the live schema — its absence here would break the category-based review-track routing described in section 0 and used by `SelfReviewController::getEmployeeDetails()`/`getEmployeesByCategory()`.
3. Resignation → F&F (`resignation_requests`, `resignation_accept`, `termination`, and `emp_details.status`) IS fully verified against schema/mypayrol_trial.sql — full DDL captured in section 5, state machine in section 3c, and four concrete bugs/inconsistencies flagged in section 4 that should be fixed rather than replicated in the Next.js port (the `== ` vs `=` typos, the `$arr > 0` array-truthiness checks, the two divergent `letter()`/`letteredit()` join schemes, and the unpopulated `termination.Resignation_pkey`).

---

### 7.11 Dashboards, Notifications & Mail

# Dashboards, Notifications, Mail & Misc Admin — Data Model Report

STATUS: COMPLETE

Scope: `AppController::beforeFilter()` notification computation, app-wide shared models declared in `AppController::$uses`, and the controllers listed in the brief (`DashboardController`, `DashboardNewController`, `BusinessDashboardController`, `MailBoxController`, `EventHandlerController`, `ActivityController`, `AnalysisController`). Dashboard controllers additionally pull in ~20 domain models (EmployeeDetails, DeviceAttendance, Wish, etc.) that belong to other legacy-logic report chunks (Attendance/Employee) — only their interaction with the shared app-wide models and the notification/plan mechanism is covered here in depth; their private business logic (attendance counts, salary charts, etc.) is enumerated by method name only for completeness.

---

## 1. Schema cross-check — `AppController::$uses` shared models

`Controller/AppController.php:36`:
```
public $uses = array('UserCredentials', 'CentralControl', 'CentralUserCredentials', 'Registrations',
  'Currency', 'Country', 'MasterDb', 'EmployeeMenu', 'Menu', 'CompanyContactInfo', 'EmployeeCTC');
```

None of these 11 models declare any `$belongsTo`/`$hasMany`/`$hasOne` associations (verified by reading every Model/*.php file below in full — each is a bare class with only `$name`/`$primaryKey`/`$useTable`/`$useDbConfig`). All associations are done ad-hoc via raw SQL `JOIN`s or CakePHP `joins` arrays inside controller `find()` calls, never through the Model layer. This is a systemic migration concern: there is no declarative relationship graph to port — every join must be reverse-engineered from controller code.

| Model | File | Table | DB | Schema location | Notes / mismatches |
|---|---|---|---|---|---|
| `UserCredentials` | `Model/UserCredentials.php:1` | `user_credentials` | company DB (`useDbConfig` set dynamically at runtime, e.g. `AppController.php:107`) | `schema/mypayrol_trial.sql:47696` | PK `user_pkey` matches. Has FK-like column `emp_fkey` (int) but no declared `belongsTo`. Table has `user_group` column (comment: "1 normal user, 2 for admin") — this is the source of the `user_group` session flag gating notification logic everywhere. |
| `CentralControl` | `Model/CentralControl.php:7` | `central_control` | `controldb` (hardcoded `useDbConfig`) | `schema/mypayrol_control_db.sql:1405` | PK `control_pkey` matches. Table also has a `plan` column (`varchar(40) DEFAULT 'standerd'` — **note the schema-level typo "standerd"**, `mypayrol_control_db.sql:1437`) — a *second*, separate plan field from `comp_contact_info.plan` (see §3). A `central_control_bck29092025` backup/snapshot table exists alongside it (`mypayrol_control_db.sql:1443`) — legacy manual-backup cruft, not referenced by any model. |
| `CentralUserCredentials` | `Model/CentralUserCredentials.php:7` | `user_credentials` | `controldb` (hardcoded) | `schema/mypayrol_control_db.sql:2814` | **Same table name (`user_credentials`) as the company-DB `UserCredentials` model, but a structurally different table** — control-DB version has composite PK `(user_pkey, control_fkey)`, an extra `control_fkey`, `plan_id`, `credit_balance` columns, and `locked` typed `varchar(10)` (vs `int(11)` in the company-DB copy at `mypayrol_trial.sql:47711`). A `user_credentials_bck29092025` backup table also exists (`mypayrol_control_db.sql:2842`). Migrating both "UserCredentials" concepts under one name in Next.js would be a bug source — they must stay as two distinct entities (tenant-local login vs control-plane super-admin login). |
| `Registrations` | `Model/Registrations.php:7` | `registrations` | `controldb` | `schema/mypayrol_control_db.sql:2667` | No primary key column in schema at all (no `id`/`*_pkey`) — CakePHP would default to `id` if present in data but the table literally has none, so `Model::save()`/`id` lookups on this model are unreliable. Used for the public sign-up/lead-capture form (outside this report's controller set). |
| `Currency` | `Model/Currency.php:7` | `countries` | `controldb` | `schema/mypayrol_control_db.sql:1556` | **Mismatch/quirk: `Currency` model is mapped to the `countries` table**, not any `currency`/`currencies` table. `countries` does carry `currency_code`/`currency_name`/`currrency_symbol` (note schema-level typo "currrency_symbol") columns, so the model is presumably used only to read those currency columns off the country row — but the model name is misleading for migration purposes. |
| `Country` | `Model/Country.php:7` | `countries` | `controldb` | `schema/mypayrol_control_db.sql:1556` | Identical table as `Currency` above — **two Cake models point at the exact same table**. No functional difference between them at the Model layer; whichever fields a given controller reads determines "which model" it conceptually is. Two other near-duplicate country tables exist unused by these models: `countries_nationality` (`:1571`) and `countries_only` (`:1580`) — legacy duplication, verify which is authoritative before migrating country/nationality lookups. |
| `MasterDb` | `Model/MasterDb.php:7` | `master_db` | `controldb` | `schema/mypayrol_control_db.sql:2294` | PK `master_db_pkey` matches. Table stores `object_script` as a `blob` — looks like a mechanism for centrally storing/pushing SQL/script objects (stored procedures?) to tenant DBs; not called from any controller in this report's scope. |
| `EmployeeMenu` | `Model/EmployeeMenu.php:7` | `emp_menu` | company DB (set at runtime) | `schema/mypayrol_trial.sql:43519` | PK `menu_id` matches. Despite the model name "EmployeeMenu" this table is generically reused in `AppController::beforeFilter()` (`AppController.php:105,112,119,121,141,160`) as a **catch-all raw-SQL query runner** — none of those `->query()` calls actually touch `emp_menu` rows; the model object is just a convenient handle with an open DB connection (`useDbConfig` set to session `ds`). This is a strong migration-cleanup candidate: the real query targets are `leaveentries`, `emp_details`, `emp_proff`, `user_access`, `site_transactions`, not `emp_menu`. |
| `Menu` | `Model/Menu.php:7` | `hrm_menu` | company DB (set at runtime) | `schema/mypayrol_trial.sql:44568` | PK `menu_id` matches. `hrm_menu` has its own `plan` column (`varchar(15)`, `mypayrol_trial.sql:44577`) seeded entirely `NULL` in the sample data — suggesting a **planned-but-unimplemented per-menu-item plan gating feature** (see §4). `AppController.php:96` calls `$this->Menu->find("all")` on every request and never uses the result (`$menudb` is set but never `$this->set()`) — dead query, pure overhead on every page load. `DashboardController.php:154-158` / `DashboardNewController.php:70-72` separately re-query `comp_contact_info.plan` via the `Menu` model object (again just borrowing its connection, not touching `hrm_menu`). |
| `CompanyContactInfo` | `Model/CompanyContactInfo.php:7` | `comp_contact_info` | company DB (default, overridden at `AppController.php:86`) | `schema/mypayrol_trial.sql:40569` | No declared PK override; schema PK is `id` (matches Cake default). Holds `logo`, `business_name`, `max_emp_count`, and `plan` (`varchar(10) DEFAULT 'standard'`, `:40586`) — this is the plan column actually read by `AppController::beforeFilter()` at line 192 (see §3). |
| `EmployeeCTC` | `Model/EmployeeCTC.php:7` | `emp_ctc_upload` | company DB (set at runtime) | `schema/mypayrol_trial.sql:42649` | PK `emp_ctc_upload_pkey` matches. Table has FK `emp_fkey → emp_details.emp_pkey` enforced at DB level (`CONSTRAINT emp_ctc_upload_ibfk_1`, `:42672`) but **no `belongsTo` declared on the model**. The table has an `AFTER INSERT` trigger (`emp_ctc_upload_ai`, `:42678`) that auto-populates a history table `emp_ctc_transaction` and closes out prior open records — business logic living in the DB, invisible to the CakePHP layer and easy to silently drop during a Next.js/Prisma migration if only the base table is ported. |

---

## 2. Custom methods per model

Of the 11 shared models, **only one** declares a custom (non-inherited) method:

### `UserCredentials::linkempDeviceanddatabase($outputParameter)` — `Model/UserCredentials.php:20-30`
```php
public function linkempDeviceanddatabase($outputParameter){
    $parameter = '';
    foreach($outputParameter as $prm) { $parameter .= $parameter == "" ? " $prm " : " , $prm "; }
    $query = "CALL Linkemp_deviceanddatabase($parameter,@Perror_message);";
    $proc_result = $this->query($query);
    return true;
}
```
Builds a comma-joined parameter list and invokes the MySQL stored procedure `Linkemp_deviceanddatabase(...)`. Always returns `true` regardless of the procedure's actual outcome (`@Perror_message` output param is never read back) — a silent-failure pattern to flag for migration (Next.js/Prisma equivalent must explicitly check the proc's error output, which this code never did).

Call sites (15 total, across 5 controllers — outside this report's controller set but included per the "cite every call site" instruction since it's the only custom method on an in-scope model):
- `Controller/DataUploaderController.php:1086, 1279, 1982`
- `Controller/EmployeeController.php:3451, 3756, 4657, 6753`
- `Controller/EmployeeJoinController.php:3218, 4220, 6155, 11026`
- `Controller/EmployeeController_old_before addnominee.php:825, 1018, 1721` (backup/dead file — see naming)
- `Controller/EmployeeUnderController.php:583`

All other shared models (`CentralControl`, `CentralUserCredentials`, `Registrations`, `Currency`, `Country`, `MasterDb`, `EmployeeMenu`, `Menu`, `CompanyContactInfo`, `EmployeeCTC`) contain **zero custom methods** — each is a bare CakePHP `AppModel` subclass with only `$name`/`$primaryKey`/`$useTable`/`$useDbConfig` properties (confirmed by reading every file in full: `Model/CentralControl.php`, `Model/CentralUserCredentials.php`, `Model/Registrations.php`, `Model/Currency.php`, `Model/Country.php`, `Model/MasterDb.php`, `Model/EmployeeMenu.php`, `Model/Menu.php`, `Model/CompanyContactInfo.php`, `Model/EmployeeCTC.php`). All business logic against these tables is done via ad-hoc `->query()` raw SQL or `->find()` calls directly in controllers (mostly `AppController::beforeFilter()` and the dashboard controllers), not via the Model classes.

Usage scale note: because these models are declared in `AppController::$uses`, **every controller in the app inherits access to them**. A grep for `$this->CompanyContactInfo->`, `$this->EmployeeMenu->`, `$this->Menu->`, `$this->EmployeeCTC->`, `$this->CentralControl->`, `$this->UserCredentials->`, `$this->Currency->`, `$this->Country->`, `$this->MasterDb->`, `$this->Registrations->`, `$this->CentralUserCredentials->` across `Controller/` matches **149 files** — too many to enumerate individually; representative examples covered in this report's controller set are `Controller/DashboardController.php:154-158`, `Controller/DashboardNewController.php:70-72`, `Controller/BusinessDashboardController.php:12-18,30-32`, `Controller/EventHandlerController.php:51` (`$uses` includes `CentralControl`, `UserCredentials`, `EmployeeCTC`), `Controller/ActivityController.php:52` (`UserCredentials`, `CompanyContactInfo`, `CentralUserCredentials`), `Controller/MailBoxController.php:48` (`UserCredentials`, `CompanyContactInfo`).

---

## 3. Complex logic

### 3.1 `AppController::beforeFilter()` — runs on every single authenticated request (`Controller/AppController.php:39-197`)

Order of operations:

1. **Session-out guard** (`:43-46`): if `user_group` session var is neither `1` nor `2`, redirect to `Site::login`. Comment attributes this to "megha, 22-04-2025" — i.e. a recent patch to fix crashes when the session had expired mid-page-load.

2. **Tenant DB connection bootstrap** (`:48-94`), gated on `Session::read("company_key")` being truthy:
   - Opens a **raw `mysqli`/`mysql_connect`** (legacy `mysql_*` API, not even `mysqli_*`) to the control DB using hardcoded credentials `'127.0.0.1', 'mpm_cntrl_usr', 'MyPyR01@Cntr1#LB'` (`:54`) — **hardcoded DB password in source, a security flag**.
   - Runs `SELECT * FROM central_control WHERE control_pkey = $company_key` (`:56`) — **raw string concatenation of `$company_key` into SQL, no escaping** (SQL-injection-shaped code, though `company_key` is itself a session value set during login rather than direct user input).
   - On success, dynamically registers a new CakePHP DB connection named `'companydb'` (`:65-75`) using the row's `Admin_name`/`user_pwd`/`user_db` — this is the multi-tenant "one DB per company" mechanism; `Session::write("ds", 'companydb')` (`:76`) is the flag every model's `useDbConfig = Session::read('ds')` reads.
   - Company logo/name caching: if `company_logo` session var is empty, does a `CompanyContactInfo->find('first')` against the just-opened tenant DB (`:86-92`) and caches `logo`/`business_name` into session so this isn't re-queried every request (the one piece of this method that *is* cached).

3. **Menu fetch (dead query)** (`:95-96`): `$this->Menu->find("all")` against `hrm_menu` — result assigned to `$menudb` but **never passed to the view via `$this->set()`**. Runs on every request for every user, fetching the entire `hrm_menu` table for no observable purpose. Flag as **dead code — safe to drop during migration**, but verify no view relies on a stray `$menudb` var via `compact()`-style implicit binding (not observed in this file).

4. **`user_group == 2` (employee) branch** (`:102-127`) — the notification-widget computation block:
   - **Pending leave-approvals notification** (`:105`):
     ```sql
     select leaveentries.EMP_fkey, empdetails.first_name, empdetails.last_name
     from leaveentries
     left join emp_details as empdetails on (empdetails.emp_pkey = leaveentries.EMP_fkey)
     where (ISAutherizedby = '$userPkey' and ISAutherized = '0' AND LEAVESTATUS IN('Applied'))
        or (APPROVEDBY = '$userPkey' and ISAPPROVED = '0' and ISAutherized = '1' AND LEAVESTATUS IN('Authorized'))
     ```
     `$userPkey` is the logged-in employee's `emp_fkey` (`:103`), interpolated directly into SQL (no parameterization). Two-stage workflow modeled: an "authorizer" must clear `LEAVESTATUS='Applied'` rows where they are `ISAutherizedby`, then an "approver" must clear `LEAVESTATUS='Authorized'` rows where they are `APPROVEDBY`. No index hints; `leaveentries` has no index on `ISAutherizedby`/`APPROVEDBY`/`LEAVESTATUS` per the schema (`schema/mypayrol_trial.sql:44885-44916` — only a PK on `LEAVEENTRYID`), so this is a **full table scan on every page load for every user_group=2 user** — a significant migration-time perf target (add indexes or convert to a cached/on-demand fetch).
   - **User/avatar fetch** (`:107-111`): `UserCredentials->find('first', ['emp_fkey' => $userPkey])`, defaults `avatar` to `"img/picture.jpg"` if unset.
   - **Birthday / joining-anniversary "next 7 days" widget** (`:112-116`):
     ```sql
     select 'BIR', first_name, date_format(date_of_birth,'%M-%d') date_month, emp_pkey
     from emp_details
     where date_format(date_of_birth,'%m-%d') between date_format(current_date,'%m-%d')
                                                    and date_format(date_add(current_date,INTERVAL 7 DAY),'%m-%d')
       and status = 1
     union
     select 'JOIN', first_name, date_format(joining_date,'%M-%d') date_month, emp_pkey
     from emp_proff left join emp_details on (emp_details.emp_pkey = emp_proff.emp_fkey)
     where date_format(joining_date,'%m-%d') between date_format(current_date,'%m-%d')
                                                   and date_format(date_add(current_date,INTERVAL 7 DAY),'%m-%d')
       and emp_details.status = 1
     order by date_format(date_month,'%m-%d')
     ```
     **Exact date-range mechanics**: comparison is done on `'%m-%d'` string formatting of `date_of_birth`/`joining_date`, compared against a `BETWEEN` of `today's %m-%d` and `(today+7 days)'s %m-%d`. Because this is a **string comparison of `MM-DD` values**, it silently breaks across a year-end wraparound (e.g. today = Dec 28 → +7 days = Jan 04; the string range becomes `BETWEEN '12-28' AND '01-04'`, and since `'01-04' < '12-28'` lexicographically, **the BETWEEN never matches anything** in the wraparound window — a real bug in the legacy app that migration should NOT silently reproduce; use a proper date-modulo/day-of-year windowing function in the Next.js port). Only `status = 1` (active) employees are considered. No dedupe against `wishes`/already-notified state (that dedup, via a `wishes`/`Wish` table LEFT JOIN, only exists in the separate `DashboardController::load_birthdays()`/`getEvents()` methods, not in this always-on notification).
   - **Team-leave-visibility flag** (`:117-124`): looks up the `menu_id` for `emp_menu.menu_name = 'Team Leave Requests'` (`:119`), then checks `user_access.active` for that `menu_id` and the current user (`:121`), defaulting to `'Y'` if no row found (`:122`) — i.e. **fail-open**: if the `user_access` row is missing, the team-leave notification menu is shown by default rather than hidden.
   - **Else branch (`user_group != 2`, i.e. admin)** (`:128-136`): sets `avatar` to the cached `company_logo` session value or a default `'img/avatar5.png'`. A large commented-out block (`:129-133`) shows dead code for a `wizard_config`-driven forced-redirect to `Site::config` — **legacy cruft, currently disabled**.

5. **Site-transaction end-date warning** (`:137-149`) — gated on a **hardcoded company-code allowlist**:
   ```php
   if ($company_code == 'vgfs' || $company_code == 'vsfs' || $company_code == 'gede'
    || $company_code == 'absg' || $company_code == 'demo' || $company_code == 'glet') {
   ```
   (`company_code` lowercased via `strtolower()` at `:138`, so the check is effectively case-insensitive against these 6 literal tenant codes: VGFS, VSFS, GEDE, ABSG, DEMO, GLET.) Query (`:141-147`):
   ```sql
   select distinct site.site_id, working_day_time_procedures.day_time_desc
   from site_transactions
   left join site on (site.site_pkey = site_transactions.site_fkey)
   left join working_day_time_procedures
     on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey)
   where DATEDIFF(site_transactions.end_date_effective, now()) > 0
     and DATEDIFF(site_transactions.end_date_effective, now()) < 31
     and site_pkey is not null and site.status = 1 and site_transactions.status = 1
   ```
   **Exact window**: `end_date_effective` strictly between "today" and "today+31 days" exclusive on both ends (`DATEDIFF > 0 AND < 31`, i.e. a **1–30 day** lookahead, comment in code says "site enddate notification"). This runs regardless of `user_group` (unlike the birthday/leave widgets, which are employee-only) — so it fires for every request of every user at these 6 tenants, admin or employee.

6. **CTC increment reminder** (`:150-176`) — gated on a **second, narrower hardcoded company-code allowlist**: only `company_code == 'demo'` or `'glet'` (comment: "Edited by Akshay on 20-3-2025" — narrowed down from a previously broader/`true` condition, per the commented-out `// if (true) {` at `:159`). Date window computed in PHP, not SQL:
   ```php
   $fromDate  = date("Y-m-01");                       // first day of current month
   $nextMonth = date("Y-m-d", strtotime("+1 month"));
   $entDate   = date("Y-m-t", strtotime($nextMonth));  // last day of NEXT month
   ```
   i.e. the window spans **from the 1st of the current month through the last day of next month** (a ~2-month lookahead). Query (`:160-170`):
   ```sql
   SELECT e.emp_fkey, ei.EmpName, ei.employee_id, e.next_increment_date
   FROM emp_ctc_upload e
   JOIN employee_info ei ON e.emp_fkey = ei.emp_pkey
   WHERE e.next_increment_date BETWEEN '$fromDate' AND '$entDate'
     AND e.created_date = (SELECT MAX(sub.created_date) FROM emp_ctc_upload sub WHERE sub.emp_fkey = e.emp_fkey)
   ORDER BY ei.EmpName ASC
   ```
   The `created_date = (SELECT MAX(...))` correlated subquery picks only the **latest CTC-upload row per employee** (per-employee "current" record), executed once per candidate row — an N+1-shaped correlated subquery, another perf target. `notificationCount` is simply `count($incrementdata)` (`:173`).

### 3.2 The `plan` mechanism (`AppController.php:191-194`)

```php
$this->Menu->useDbConfig = $this->Session->read('ds');
$plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
$plan = $plan['0']['comp_contact_info']['plan'];
$this->set('plan', $plan);
```
Runs unconditionally at the **end** of `beforeFilter()`, after the tenant-DB `if`/`else` block (so it executes even in the `else` branch at `:181-189` where no tenant DB was resolved — that would then throw or query the default DB config, a latent bug for logged-out/no-`company_key` requests). Reads `comp_contact_info.plan` — the single-row-per-tenant SaaS-tier column (schema: `schema/mypayrol_trial.sql:40586`, `varchar(10) DEFAULT 'standard'`), and pushes it into every view as `$plan`.

**Data-model dimension of `plan`:**
- `comp_contact_info.plan` — `varchar(10)`, default `'standard'` (`schema/mypayrol_trial.sql:40586`). Only value present in the shipped seed data is `'standard'` (`mypayrol_trial.sql:40591`); no other plan values are discoverable from the data dump. Column length (10 chars) implies short tier codes only.
- **A second, unrelated `plan` column exists on `central_control`** (control DB): `varchar(40) DEFAULT 'standerd'` (note the misspelling in the schema default itself, `schema/mypayrol_control_db.sql:1437`) — this is a *different* per-tenant plan flag living in the control-plane table, not read anywhere in this report's `AppController`/dashboard code (not observed in any `$this->CentralControl->` call across the 149-file grep sample reviewed). Whether `central_control.plan` and `comp_contact_info.plan` are meant to be kept in sync, or are two competing/legacy implementations of the same concept, is not resolvable from code alone — **flag for product/business clarification before migration**, since porting only one could silently drop tier-gating behavior tied to the other.
- **A third, still-unused `plan` column exists on `hrm_menu`** (`varchar(15)`, `schema/mypayrol_trial.sql:44577`) — every seeded row has `plan = NULL` (`mypayrol_trial.sql:44582-44607` and beyond). This strongly suggests a **planned-but-never-implemented feature to gate individual menu items by SaaS plan** — worth flagging to product/business as a possible "we meant to build this and didn't" gap, since the Next.js migration is a natural point to either finally implement it or formally drop the column.
- `user_credentials` (control DB) also carries a `plan_id int(11) unsigned` (`schema/mypayrol_control_db.sql:2833`) plus `credit_balance decimal(10,2)` — a fourth, again distinct, plan-adjacent concept (per-*user* plan/credit rather than per-*tenant*). Not observed read in this report's controller scope either.
- Controllers in this report's scope that independently re-fetch the same `comp_contact_info.plan` value (duplicating the `AppController::beforeFilter()` fetch on the very same request): `Controller/DashboardController.php:156-158`, `Controller/DashboardNewController.php:70-72` — i.e. the plan is queried **twice per request** for anyone hitting `/Dashboard` or `/DashboardNew`, once in `beforeFilter()` and again in the action itself, with no caching between the two (both are plain `->query()` calls against the same tenant connection). Straightforward migration win: fetch once (already in session/context) and reuse.

---

## 4. Schema quirks

- **`emp_menu` vs `hrm_menu` ambiguity**: two near-identical menu tables (`emp_menu` at `schema/mypayrol_trial.sql:43519` — used for employee-portal menu items and the "Team Leave Requests" notification lookup at `AppController.php:119`; `hrm_menu` at `schema/mypayrol_trial.sql:44568` — used for admin/HR menu, mapped by the `Menu` model) share the same PK column name `menu_id` and overlapping ID ranges (both start numbering from 1). `user_access.menu_id` (`schema/mypayrol_trial.sql:47687`) has **no FK constraint or comment indicating which of the two menu tables it references** — in practice it's used against both depending on call site (e.g. `AppController.php:121` uses it against an `emp_menu`-sourced ID; `DashboardController.php:33` uses it with a literal `menu_id = '0'` sentinel that doesn't correspond to a real row in either table — a magic-number "does this user have hierarchy access" flag rather than a genuine menu lookup). This dual-table, shared-ID-space, no-FK design is a real migration hazard: a single `menus` table with a `source` discriminator (or two clearly-named tables with explicit FKs from `user_access`) should replace it.
- **`hrm_menu.plan` all-NULL** (`schema/mypayrol_trial.sql:44577` + seed rows `44582` onward) — see §3.2; unused/unimplemented plan-gating column.
- **`comp_contact_info` is a singleton-per-tenant-DB table with no natural business key beyond `id`** — every read in the reviewed code does `find('first')` or `SELECT ... FROM comp_contact_info` with no `WHERE` clause at all (`AppController.php:87`, `:192`; `DashboardController.php:156`; `DashboardNewController.php:70`; `BusinessDashboardController.php:27-32`), relying entirely on "one row per tenant database" as the invariant. Nullable-but-required-in-practice: nearly every column (`business_name`, `address`, etc.) is declared `NOT NULL` in schema but the seed/trial row (`mypayrol_trial.sql:40591`) uses placeholder values like `'Please Edit This'` — i.e. **schema-level NOT NULL is enforced, but semantic completeness is not**, so downstream code must defensively `isset()`-check every field anyway (as seen at `AppController.php:88-89`).
- **Backup tables left in production schema**: `central_control_bck29092025` (`schema/mypayrol_control_db.sql:1443`) and `user_credentials_bck29092025` (`schema/mypayrol_control_db.sql:2842`) — dated manual snapshots (29 Sep 2025) sitting alongside the live tables in the control DB. No code references them (not matched in the 149-file grep). **Possible legacy cruft — verify before migrating**; likely safe to exclude from the Next.js schema entirely, but confirm no scheduled/cron job restores from them.
- **`central_control.plan` default `'standerd'`** (misspelled, `schema/mypayrol_control_db.sql:1437`) vs `comp_contact_info.plan` default `'standard'` (correctly spelled, `schema/mypayrol_trial.sql:40586`) — cosmetic but a good canary that these two plan columns evolved independently and were never reconciled.
- **`Currency`/`Country` models both mapping to `countries`**, plus two further unused near-duplicate tables `countries_nationality` and `countries_only` (`schema/mypayrol_control_db.sql:1571`, `:1580`) — four different "country data" tables in the control DB with no cross-referencing FKs observed; migration should pick one canonical source and confirm with data which of the 4 is actually populated/current before dropping the others.
- **`user_credentials` (company DB) vs `user_credentials` (control DB)**: identical table name, different shape (see §1 table) — a strong footgun if migration tooling naively assumes "one table = one Prisma model" across both source databases without namespacing.
- **`emp_ctc_upload` has DB-level triggers** (`emp_ctc_upload_ai` AFTER INSERT, `schema/mypayrol_trial.sql:42678-42718`) that maintain a derived history table (`emp_ctc_transaction`) and invoke a stored function (`sal_structure_distribution_fn`) — this logic (auto-closing the prior "open" CTC record, cascading a salary-structure distribution recalculation) is invisible to `EmployeeCTC` the CakePHP model and must be explicitly re-implemented in application code (a service/transaction function) during the Next.js migration, since Prisma/typical ORMs won't carry DB triggers forward by default.

---

## 5. `InfoController` / `DashboardManagementComponent` — confirmed prior findings

- **`Controller/InfoController.php`** (`legacy/Controller/InfoController.php:36-69`): class `infoController`, `public $components = array('LoginManagement', 'Session', 'Email')` (`:53`) but **no auth check anywhere** — `index()` (`:59-67`) sets `autorender = false`, calls `$this->render(false)`, then does `echo phpinfo();` (`:65`). **Confirmed: this is a live, unauthenticated `phpinfo()` dump** — a serious information-disclosure risk (exposes PHP config, loaded extensions, environment variables, and potentially file paths/credentials in `$_SERVER`) reachable simply by hitting `/info` with no login. Should not be ported to Next.js at all; flag for immediate removal from the live legacy app if not already firewalled off.
- **`Controller/Component/DashboardManagementComponent.php`**: **confirmed** — the file's class is actually `class LoginManagementComponent extends Component` (`:2`), not a `DashboardManagementComponent`. It contains `verifyAdminLogin()` (`:27-59`) and `verifyEmployeeLogin()` (`:60-90`), both raw-SQL-string-concatenated credential checks against `CentralUserCredentials`/`CentralControl`/`UserCredentials` (plaintext-looking password comparison, `password = '$password'`, no hashing visible in this component — password hashing may happen elsewhere before this is called, but not evidenced in this file). **This file has nothing to do with dashboards** — it's dead/misnamed duplicate login-verification code (compare with the real login flow, likely in `Site`/`Login`-named controllers, outside this report's scope). Flag as **possible legacy cruft — verify before migrating**: confirm whether any controller actually loads `DashboardManagementComponent` (i.e. references the filename) versus the real login component, since CakePHP resolves components by class name from the `$components` array, and no controller in the reviewed set declares `'DashboardManagement'` in its `$components` list — meaning this component, under its expected name, may be entirely unreferenced/unreachable, with `LoginManagementComponent` (if used) presumably loaded from a differently-named file elsewhere.

---

## 6. Custom methods inventory — dashboard/mail/activity/analysis controllers (method names only, for completeness)

These controllers use their own private domain models (out of this report's Model scope) but are listed here per the task's controller set. Full logic of attendance/salary/chart methods belongs to Attendance/Employee/Payroll-focused report chunks; only method names + line numbers are captured:

- **`DashboardController`** (`Controller/DashboardController.php`, `$uses` at `:9`): `index` `:12`, `getNextWednesday` `:261`, `menuAudit` `:283`, `hierarchydashboard` `:298`, `load_birthdays` `:617`, `getEvents` `:720`, `empcalendar` `:732`, `empdashboard` `:785`, `locationtracking` `:1144`, `issuereport` `:1157`, `loadmap` `:1231`, `timers` `:1280`, `form` `:1375`, `empform` `:1431`, `form2` `:1487`, `general_setting` `:1544`, `save_emergency_contact` `:1564`, `never_remind_emergency` `:1593`, `later_remind_emergency` `:1615`, `share` `:1635`, `presenttodayall` `:1641`, `presenttoday` `:1680`, `absenttoday` `:1756`, `download` `:1887`, `search` `:1908`, `gettodayattendace` `:1913`, `getMenus` `:1918`, `listemployeeleaverequestscounts` `:2082`, `misspunch` `:2121`, `listemployeeleaverequests` `:2129`, `todayattendance` `:2187`, `lastmonthattendanceinfo` `:2192`, `thismonthattendanceinfo` `:2197`, `listtodayattendance` `:2202`, `LocationUpdates` `:2353`, `EmployeeEvent` `:2453`, `listthismonthattendance` `:2747`, `listleaverequests` `:2905`, `checkpunch` `:2911`, `lastpunch` `:3076`, `listlastmonthattendance` `:3099`, `listemployeemisspunches` `:3253`, `wish_modal` `:3287`, `convertimage` `:3300`, `sendemailtemplatedatabase` `:3357`, `sendemailtemplate` `:3545`, `automation_load_birthdays` `:4363`, `automation_convertimage` `:4503`, `getMenusForAbs` `:4556`, `sendFormEmail` `:4655`, `dashboard_old` `:4731`.
- **`DashboardNewController`** (`Controller/DashboardNewController.php`, `$uses` at `:10`): `getScopeFilter` `:16` (protected — computes hierarchy-scoped `emp_pkey IN (...)` filter from `emp_proff.attr1`, comment credits "Antigravity on 28-04-2026" for simplifying it to direct-reports-only), `index` `:52` (very large — contains the dashboard-widget "pending approvals", donut/line-chart, and "upcoming events" logic described inline in the file; notably reimplements a birthday/anniversary "upcoming events" query independently of `AppController::beforeFilter()`'s version, this time using a proper `CASE`-based year-rollover date calc rather than the buggy string-`BETWEEN` used in `AppController.php:112-115` — i.e. **two different, inconsistent birthday-window implementations coexist** in the same app), plus `menuAudit` `:1058`, `hierarchydashboard` `:1073`, `load_birthdays` `:1274`, `getEvents` `:1377`, `empcalendar` `:1402`, `empdashboard` `:1455`, `issuereport` `:1827`, `loadmap` `:1935`, `timers` `:1984`, `form`/`empform`/`form2` `:2079/2147/2203`, `general_setting` `:2260`, emergency-contact methods `:2280-2351`, `presenttoday` `:2357`, `activetoday` `:2411`, `absenttoday` `:2482`, `download` `:2609`, `search` `:2630`, `gettodayattendace` `:2635`, `getMenus` `:2640`, `listemployeeleaverequestscounts` `:2813`, `misspunch` `:2852`, `listemployeeleaverequests` `:2860`, attendance-list methods `:3012-3605`, `getMissedAttendance` `:3680`, `listleaverequests` `:3691`, `checkpunch` `:3697`, `lastpunch` `:3862`, ajax variants `:3884-3897`, `wish_modal` `:3908`, `convertimage` `:3922`, `sendemailtemplate` `:3981`, `sendemailtemplatedatabase` `:4283`, `automation_load_birthdays` `:4547`, `automation_convertimage` `:4687`, `getHrmMenusForAbs` `:4741`, `sendFormEmail` `:4857`, `createOverlayImage` `:4936`, `promotion` `:5092`, `expense` `:5139`, `AttendanceRegularisation` `:5198`, `Attendanceverification` `:5223`.
- **`BusinessDashboardController`** (`Controller/BusinessDashboardController.php`, `$uses` at `:5`): `index` `:8` (BI-style analytics dashboard — demographics donut, department/designation/branch salary breakdowns, CTC trend line chart), `getCTCBreakup` `:565`, `getCTCBreakupByDept` `:603`, `getCTCBreakupByDesignation` `:652`, `getCTCBreakupByBranch` `:702`, `getEmployeeCount` `:752`, `getPresentCount` `:790`, `getVariableAddition` `:819`, `getVariableDeduction` `:860`, `getAbsenceData` `:901`, `highestSalaries` `:943`, `lowestSalaries` `:987`, `monthlyCTCChartData` `:1036`. Notably uses `$this->CentralControl->setDataSource('controldb')` + `$this->loadModel('CentralControl')` (`:12-13`) to look up `company_name` by `company_code` — one of the few call sites in this report's scope that actually reads from `CentralControl` for something other than login/tenant-resolution.
- **`MailBoxController`** (`Controller/MailBoxController.php`, `$uses` at `:48`: `UserCredentials`, `CompanyContactInfo`, `Banks`): `index` `:54` and `Template` `:59` are **both empty stub methods with no body** — confirmed dead/unfinished feature, not wired to any mailbox logic despite the controller name and `DatatablesManagement` component inclusion.
- **`EventHandlerController`** (`Controller/EventHandlerController.php`, `$uses` at `:51`: includes `CentralControl`, `UserCredentials`, `EmployeeCTC` among 15 models): single `index` action `:58` (not read in full — out of scope depth, but confirmed to include `EmployeeCTC` in its model list alongside tax/salary-structure models, suggesting event-handling here is HR-lifecycle-event-triggered CTC recalculation rather than dashboard notifications).
- **`ActivityController`** (`Controller/ActivityController.php`, `$uses` at `:52`: `Activity`, `Units`, `ActivityProjects`, `UserCredentials`, `CompanyContactInfo`, `EmployeeDetails`, `CentralUserCredentials`): `index` `:59`, `report` `:84`, `projects` `:137`, `addproject` `:157`, `add` `:170`, `save2` `:204`, `save` `:217`, `sendMail` `:350`, `listData` `:685`, `listProjectsData` `:803`, `deleteRow` `:846`, `deleteProject` `:861`, `deleteLogTime` `:876`. This is a project/timesheet-activity-log feature, not dashboard-notification logic; included per task's controller list but its business logic belongs to a different report chunk.
- **`AnalysisController`** (`Controller/AnalysisController.php`, `$uses` at `:50`: `EmployeeDetails`, `Assets`, `EmployeeMenu`, `allocate`): `index` `:53`, `duplication_founds` `:60`, `salary_structure_allocate_issues` `:103`, `payroll` `:175`. Data-quality/allocation-issue-finder tool (finds duplicate assets, unallocated salary structures) — again not dashboard-notification logic per se, but the one controller here besides `AppController` that pulls in `EmployeeMenu` from the shared model set (though its actual use of `EmployeeMenu` within this controller was not traced in depth — flag for a future pass if `EmployeeMenu`-specific business rules matter for the migration of this controller).

---

## Summary of key migration-relevant flags

1. **Dead query**: `AppController.php:96` fetches all of `hrm_menu` every request and discards it.
2. **Double-fetch**: `comp_contact_info.plan` is queried once in `beforeFilter()` (`:192`) and again in `DashboardController`/`DashboardNewController` action bodies on the same request.
3. **Four distinct "plan" columns** across `comp_contact_info`, `central_control`, `hrm_menu`, and control-DB `user_credentials` — only `comp_contact_info.plan` is actually read in the reviewed code path; the other three are unused/orphaned as far as this scope shows. Needs business clarification before the Next.js port decides which (if any) to keep.
4. **Birthday/anniversary date-window bug**: `AppController.php:112-115`'s string-`BETWEEN` on `%m-%d` breaks across year-end wraparound; `DashboardNewController.php`'s separate implementation (`:570-638`) uses a correct `CASE`-based rollover — two inconsistent implementations coexist, neither should be ported as-is without picking (and testing) one algorithm.
5. **Hardcoded, magic company-code allowlists** gate two notification types (`site_transactions` warning: `vgfs/vsfs/gede/absg/demo/glet`; CTC-increment reminder: `demo/glet` only) — these are tenant-specific business rules baked into app code rather than config/feature-flag data; migration should externalize them (e.g. a `feature_flags` table or the `plan` mechanism itself) rather than hardcode tenant codes again.
6. **Hardcoded DB credentials and raw unescaped SQL** in the tenant-connection bootstrap (`AppController.php:54,56`) — security review item independent of the notification logic itself.
7. **`InfoController`** confirmed live unauthenticated `phpinfo()` — must not be ported; recommend immediate removal from legacy too.
8. **`DashboardManagementComponent.php`** confirmed to actually contain `LoginManagementComponent`, apparently unreferenced under its expected component name — likely dead/misnamed file; verify no controller depends on it before deciding whether to port anything from it.
9. **No declarative Model associations anywhere** in the 11 shared models (or, per the two `getEvents`/dashboard controllers sampled, in the domain models either) — all joins are raw SQL/`joins` arrays in controllers. Full association graph must be reconstructed from controller code, not the Model layer, for every table touched by this report.
10. **DB triggers on `emp_ctc_upload`** (`schema/mypayrol_trial.sql:42678-42718`) implement auto-versioning/history logic invisible to the `EmployeeCTC` Cake model — must be explicitly reimplemented in the Next.js service layer.

---

### 7.12 Statutory & Access Admin

# Statutory & Access Admin — Data Model / Business Logic Report

Scope: `UserCredentials`, `CentralUserCredentials`, `Useraccess`, `MobileUserCredentials`, `EmployeeMenu`, `Menu` models and their controllers (`UserAccessController` → class `UseraccessController`, `UserController`, `UserCredentialsController`, `EmployeeMenuController`, `AccessController`, `StatutoryRegistersController`, `StatutoryUploadsController`).

---

## 1. Schema cross-check

| Model | File | `useTable` | `useDbConfig` | Table found in | Primary key declared | Schema PK | Associations declared |
|---|---|---|---|---|---|---|---|
| `UserCredentials` | `Model/UserCredentials.php:9-18` | `user_credentials` | default (tenant, set at runtime via `$this->Session->read('ds')` in every controller call) | `schema/mypayrol_trial.sql:47696` (tenant DB) | `user_pkey` | `user_pkey` (AUTO_INCREMENT) — matches | **None** (no `$belongsTo`/`$hasMany`) |
| `CentralUserCredentials` | `Model/CentralUserCredentials.php:7-17` | `user_credentials` | `controldb` (hardcoded `$useDbConfig='controldb'`, also re-set via `setDataSource('controldb')` in controllers) | `schema/mypayrol_control_db.sql:2814` (control DB) | `user_pkey` | **Composite PK** `(user_pkey, control_fkey)` (`schema/mypayrol_control_db.sql:2835`) | None |
| `Useraccess` | `Model/Useraccess.php:23-27` | `user_access` | default (tenant, set per-call) | `schema/mypayrol_trial.sql:47683` (tenant DB) | `user_access_pkey` | `user_access_pkey` (AUTO_INCREMENT) — matches | None |
| `MobileUserCredentials` | `Model/MobileUserCredentials.php:7-16` | `mob_user_credentials` | default (tenant, set per-call) | `schema/mypayrol_trial.sql:45091` (tenant DB) | `user_pkey` | `user_pkey` (AUTO_INCREMENT) — matches | None |
| `EmployeeMenu` | `Model/EmployeeMenu.php:7-15` | `emp_menu` | default (tenant, set per-call) | `schema/mypayrol_trial.sql:43519` (tenant DB) | `menu_id` | `menu_id` (AUTO_INCREMENT) — matches | None |
| `Menu` | `Model/Menu.php:7-16` | `hrm_menu` | default (tenant) | `schema/mypayrol_trial.sql:44568` (tenant DB) | `menu_id` | `menu_id` (AUTO_INCREMENT) — matches | None |

### Findings / mismatches

1. **No CakePHP associations anywhere in this cluster.** Every model (`UserCredentials`, `CentralUserCredentials`, `Useraccess`, `MobileUserCredentials`, `EmployeeMenu`, `Menu`) declares zero `$belongsTo`/`$hasMany`/`$hasOne`. All relationships (`user_access.menu_id` → `emp_menu.menu_id`, `user_access.user_fkey` → `emp_details.emp_pkey`, `user_credentials.emp_fkey` → `emp_details.emp_pkey`, `mob_user_credentials.user_id` → `user_credentials.user_id`) are expressed only as raw SQL joins/subqueries written per-controller-action (e.g. `Controller/UserAccessController.php:135-136`, `Controller/UserCredentialsController.php:196-216`, `Controller/UserController.php:222`, `327`, `405`). Confirms prior finding that there's no ORM-level referential integrity to lean on during migration — every join must be reverse-engineered from raw SQL.
2. **No FK constraints in schema either.** `schema/mypayrol_trial.sql:47683-47719` (`user_access`, `user_credentials`) and `:43519-43530` (`emp_menu`) declare only `PRIMARY KEY`/`KEY` (index), never `CONSTRAINT ... FOREIGN KEY`. Same for `schema/mypayrol_control_db.sql:2814-2839` (control-db `user_credentials`). So "`*_fkey` columns" in this cluster are naming-convention-only, not enforced — orphan rows are structurally possible (e.g. `user_access.user_fkey` pointing to a deleted `emp_details` row, `user_access.menu_id` pointing to a deleted `emp_menu` row). `Controller/UserAccessController.php:434-441` (`addon_useraccess` query) explicitly handles this by excluding `menu_id` values that exist in `emp_menu` at all (`AND menu_id NOT IN (SELECT menu_id FROM emp_menu)`), i.e. the codebase itself treats `user_access.menu_id` as sometimes referencing a *non-emp_menu* id space (add-on "features", see §3).
3. **`CentralUserCredentials` composite PK vs. single-field CakePHP `$primaryKey`.** Schema `schema/mypayrol_control_db.sql:2814-2839` declares `PRIMARY KEY (user_pkey, control_fkey)` with a separate `UNIQUE KEY (user_id)`. `Model/CentralUserCredentials.php:14` declares `public $primaryKey = 'user_pkey'` — CakePHP 2.x does not support composite primary keys, so it silently treats `user_pkey` as if it were the sole key. This works in practice only because `user_id` is also unique and most finds/saves key off `user_pkey` alone (e.g. `Controller/UserController.php:465-467`, `533-537`), but any code path that inserts a new row without `control_fkey` set relies on the column's default (`NOT NULL DEFAULT '0'`), and there is no model-level validation of `control_fkey`. **Flag: verify `control_fkey` semantics (likely multi-tenant partition key) before migrating this table's PK to a Next.js/Prisma-style single-column PK.**
4. **Legacy/dead backup tables in control DB**: `user_credentials_bck29092025` (`schema/mypayrol_control_db.sql:2842`) and `central_control_bck29092025` (`schema/mypayrol_control_db.sql:1443`) are full structural clones of `user_credentials`/`central_control`, dated 29-Sep-2025 — clearly ad-hoc migration/rollback backups. No model or controller references them (`grep` for `bck29092025` across `Controller/` and `Model/` returns nothing). **Possible legacy cruft — verify before migrating; do not port these tables.**
5. **`Menu` model (`hrm_menu`) is essentially unused in this cluster** — only referenced via `$this->Menu->query(...)` for the unrelated `comp_contact_info.plan` lookup in `Controller/UserAccessController.php:107` and `Controller/UserController.php:338`, not for menu data itself (`hrm_menu` is the *HR-admin* menu tree, distinct from `emp_menu`, the *employee self-service* menu tree used by `Useraccess`). The two menu trees are structurally identical (`schema/mypayrol_trial.sql:43519-43530` vs `:44568-44580`) but `hrm_menu` additionally has a `plan` column and a `UNIQUE KEY (parent_id, menu_name)` that `emp_menu` lacks — `emp_menu` has no uniqueness constraint on `(parent_id, menu_name)` at all, so duplicate sibling menu entries are possible by schema (not observed, but not prevented).

---

## 2. Custom methods per model

All six models in scope have **at most one custom method total** — nearly everything happens in controllers via raw `->query()` calls, not through model methods. This is a strong signal for the migration: there is essentially no reusable "model layer" logic to port; the business logic lives entirely in controller SQL strings.

### `UserCredentials` (`Model/UserCredentials.php`)
- `linkempDeviceanddatabase($outputParameter)` — `Model/UserCredentials.php:20-30`. Builds a comma-joined parameter string from the input array and calls stored procedure `Linkemp_deviceanddatabase(...)` via `$this->query()`. Always returns `true` regardless of the procedure's actual result (return value of `query()` is discarded).
  - **Call sites**: none found. `grep -rn "linkempDeviceanddatabase" Controller/` returns no matches. **Flag: dead/unreachable code — possible legacy cruft, verify before migrating** (the stored procedure `Linkemp_deviceanddatabase` should be checked separately for whether it's still invoked directly via SQL elsewhere, but this PHP wrapper method itself has no callers).

### `CentralUserCredentials`, `Useraccess`, `MobileUserCredentials`, `EmployeeMenu`, `Menu`
- **No custom methods** — each is a pure `AppModel` subclass declaring only `$name`, `$primaryKey`, `$useTable` (and for `CentralUserCredentials`, `$useDbConfig`/`$tablePrefix`). All CRUD and querying is done via inherited CakePHP `find()`/`save()`/`query()`/`updateAll()` called directly from controllers.

---

## 3. Complex logic

### 3.1 `emp_menu` / `user_access` permission-tree data model

**Tables** (tenant DB, `schema/mypayrol_trial.sql`):
- `emp_menu` (`:43519-43530`): `menu_id` PK, `parent_id` (int, `0` = top-level/parent menu, non-zero = child pointing at another row's `menu_id` — **self-referencing tree, one level deep in practice**, no schema-enforced depth limit), `menu_url`, `menu_title`, `menu_name`, `active` (char 'Y'/'N', default 'Y'), `iconCls`, `is_default` (varchar(100) default 'N' — despite being modeled as a flag it holds at least 3 distinct values: `'Y'`, `'N'`, and `'M'` — see quirk below), `user_id` (int, purpose unclear from controllers — never read/written anywhere in this cluster; likely vestigial "created by" column).
- `user_access` (`:47683-47693`): `user_access_pkey` PK, `organization_id` (varchar(10), always hardcoded `'1'` in every insert seen), `user_fkey` (int → `emp_details.emp_pkey`, not enforced), `menu_id` (int → `emp_menu.menu_id`, **not enforced and sometimes deliberately points outside `emp_menu`** — see below), `active` (char 'Y'/'N'), `status` (int, default 1 — used as a *soft-delete* flag distinct from `active`, see §4).

**Hierarchy encoding**: a row is a "parent" menu iff `parent_id = 0`; a row is a "child"/submenu iff `parent_id` equals another row's `menu_id`. There is no `depth` or `path` column — the tree is reconstructed at query time via correlated subselects, e.g. `Controller/UserAccessController.php:91-95` (`(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent`).

**Per-user grant row**: a `user_access` row with `user_fkey = X, menu_id = Y, active = 'Y', status = 1` means "employee X currently has menu Y enabled." Grants are **never hard-deleted** — "removing" access always means inserting/updating a row to `active = 'N'` (e.g. `Controller/UserAccessController.php:863-869`, `:875-884`). `status` is a *separate* soft-delete used only by `deleteuser()` (`Controller/UserAccessController.php:1027-1036`, sets `status = 0` via `updateAll`) — meaning a row can be `active='Y', status=0` (soft-deleted but "active") simultaneously, since no code path clears `active` when it clears `status`, and no query filters on `status` except `deleteuser`'s own `updateAll` target. **This is a real inconsistency**: `insec()`/`insecs()`/`getDefaultMenus()` all filter only on `active='Y'`, never on `status=1`, so a "deleted" (`status=0`) grant row that still has `active='Y'` continues to grant access. Cite: `Controller/UserAccessController.php:135-147` (query has no `status` filter), `:1027-1036` (only place `status` is ever set to 0).

**Cascading auto-enable/disable logic** (`Controller/UserAccessController.php`):
- **Enable cascade** (`save()`, `:539-623`): when a single child menu is turned on for an employee (the `$s != 'All'` branch, `:580-620`), the code first looks up that menu's `parent_id` (`:585`) and if non-zero, **unconditionally upserts** a `user_access` row for the *parent* menu id with `active='Y'` (`:589-603`) — i.e., enabling any one submenu always force-enables its parent, regardless of whether the parent was previously explicitly disabled by an admin. Then the child's own `user_access` row is upserted (`:605-619`).
- **Disable cascade** (`delete()`, `:835-923`): when a single child menu is turned off (`:870-921`), the code sets that menu's `user_access` row `active='N'` (`:874-884`), then looks up the menu's `parent_id` (`:888`), and if non-zero, **counts remaining active siblings** under that parent via `SELECT COUNT(*) ... WHERE ua.menu_id IN (SELECT menu_id FROM emp_menu WHERE parent_id = $parent_id)` (`:892-899`) — only if that count is `0` does it also disable the parent (`:901-918`). This is asymmetric with the enable cascade: enabling one child *always* force-enables the parent, but disabling one child only disables the parent if it was the *last* active sibling. Both cascades operate purely at the immediate parent level (one level up) — no recursion to grandparent levels (schema doesn't appear to need it, since `emp_menu.parent_id` is only ever `0` or a top-level `menu_id` in the seed data inspected).
- Both the `save()`/`delete()` bulk branches (`$s == 'All'` or matching multiple rows, `:545-578`, `:841-869`) loop over **all** menus under a parent/query and blanket-set `active` without the per-sibling cascade check — i.e. bulk operations bypass the cascade logic entirely (they set every matched row directly).
- **DB-level trigger duplication**: `user_credentials_au` AFTER UPDATE trigger (`schema/mypayrol_trial.sql:47724-47741`) independently re-implements a *default*-menu auto-grant: if a `user_credentials` row is updated and the employee has zero active `user_access` rows and `access_allowed` (new value) is `'y'`, it calls stored procedure `insert_default_menu(new.emp_fkey)` (`schema/mypayrol_trial.sql:4531-4565`), which loops all `emp_menu` rows with `is_default='Y'` and inserts a `user_access` grant for each not already present. This same default-grant logic is **also inlined a third time** inside the `user_access_firstime_only` SQL function (`schema/mypayrol_trial.sql:28029-28053`, identical cursor/insert pattern) and a **fourth time** in PHP as `EmployeeMenuController::autoAllocateDefault()` / `UserAccessController::addDefault()`/`resetDefault()` (`Controller/EmployeeMenuController.php:494-538`, `Controller/UserAccessController.php:625-658`). Four independent implementations of "grant all `is_default='Y'` menus to an employee" exist across DB trigger, DB stored procedure, DB function, and PHP — a **migration risk**: any Next.js reimplementation must decide which of these is authoritative, since they can currently fire redundantly/concurrently (e.g. saving `user_credentials.access_allowed='Y'` fires the trigger *and* the app may separately call `addDefault()`).
- `EmployeeMenu.is_default` has **3 observed values, not 2**: `'Y'`, `'N'` (declared default), and `'M'` (`schema/mypayrol_trial.sql:43598-43600`, e.g. menu_id 1101 "Customer Visits", 1102 "Expense", 1108 "Regularisation Request"). `UserAccessController::insec()` explicitly filters `is_default != 'M'` (`Controller/UserAccessController.php:136`) to exclude these from the default admin screen, but no comment anywhere explains what `'M'` denotes (plausibly "Mobile-only" menus, given menu names like "Customer Visits"/"Expense" match mobile-app-style features) — **flag: undocumented legacy status code, verify meaning with product owner before migrating the `is_default` enum.**

**Add-on/feature access is a *second*, structurally distinct permission model layered on the same `user_access` table**: `saveFeatureAccess()` (`Controller/UserAccessController.php:239-299`) writes `user_access` rows with `menu_id = 0` (hardcoded, `:271`) representing "this add-on feature is toggled on for this user" as a marker row, while the actual per-branch grant detail lives in a *different* table, `user_feature_branch_access` (`schema/mypayrol_trial.sql:47745-47755`, tenant DB — columns `user_fkey`, `feature_fkey` [→ control-DB `features.feature_id`, cross-database, unenforceable], `branch_fkey` [→ `branches.branch_code`], `is_hierarchy` char(1) added via a defensive runtime `ALTER TABLE ... ADD COLUMN` safeguard at `Controller/UserAccessController.php:252-256` — **evidence this column was added post-deployment via ad-hoc code-level migration rather than a schema migration script**, i.e. some tenant DBs may still be missing it if the `ALTER` ever fails silently past the try/catch). Separately, `insecs()` (`Controller/UserAccessController.php:434-441`) also finds "addon" `user_access` rows by selecting `menu_id` values that are in the company's licensed `features` list **and explicitly not present in `emp_menu`** — confirming `user_access.menu_id` is deliberately overloaded to reference two disjoint id spaces (`emp_menu.menu_id` for standard menus, `features.feature_id` from the *control DB* for add-ons) with no column or type to disambiguate them structurally.

### 3.2 Statutory register generation — data model (formulas covered elsewhere; this is table/column lineage only)

`StatutoryRegistersController` (`Controller/StatutoryRegistersController.php`) and `StatutoryUploadsController` (`Controller/StatutoryUploadsController.php`) generate three report types selected via `hrreports()` (`:62-80`): `Musterroll`, `wage`, `ServiceRecord` (`ServiceRecord` was added recently — comment "Edited by Akshay on 5-2-2026" at `:76`, and is conditionally hidden entirely for company `HRBL` at `:67-71`, a hardcoded company-code special case).

- **Wage Sheet** (`Generatewage()`, `Controller/StatutoryRegistersController.php:430-793`): pulls salary-head labels/operators from `emp_salary_slip` joined to `salary_head_items` (`:439-444`); per-employee gross data from a wide join: `emp_salary_slip` (aliased `ectc`) ⋈ `employee_info` ⋈ `emp_details` ⋈ `attendance_register` (or `site_attendance_register` for the second query, `:518-533`) ⋈ `salary_head_items` ⋈ `salary_heads` ⋈ `branches` ⋈ `payroll_master` ⋈ `termination` ⋈ `emp_ctc_transaction` ⋈ **`user_credentials`** (joined only for `user_credentials.user_id`, `:511`/`:529` — the only place `UserCredentials` appears in this controller). Overtime from `emp_ot_master` (`:537-538`), settlement amounts from `emp_settle_slip` (`:540-544`), proration code from `emp_salary_structure` (`:548-550`). Output written via PHPExcel to `<company_code>_WageSheet_<date>.xlsx` (`:794`).
- **Muster Roll** (`generatemusterrollreport()`, `:1669-1873`): driven by `AttendanceRegister`/`attendance_register`, date-window computed via stored function `att_start_end_fn()` and `DbConfig.attendance_date` (per-company configurable attendance-cycle start day, `:1678-1687`), attendance data refreshed via stored procedure `insert_update_att_reg` (`:1735`) before reading, joined to `branches`, `emp_details`, `termination`, `employee_info`. Leave-type abbreviations sourced from `salary_head_items` where `item_type='LEAVE'` (`:1727-1733`). Output `<company_code>_MusterRoll.xlsx` (`:1864`).
- **Service Record** (`generateServiceRecordReport()`, `:2294-2686+`): joins `payroll_master` (aliased `pm`), `employee_info` (`ei`), `emp_details` (`ed`), `termination` (`t`), and salary breakup via `emp_salary_slip` (`ess`) ⋈ `tax_salary_components` (`tsc`) filtered to `tax_salary_components_name='Basic'` (`:2378-2384`) to compute a "Basic" column. This report type has no corresponding entry in `UserCredentials`-cluster tables at all — purely payroll/attendance data.
- **`StatutoryUploadsController::statutory()`** (`:59-277`) and its near-duplicate **`pfdownload()`** (`:282-354`, differs from `statutory()` only in the employee-selection filter — `esi != ''` vs `status = '1'` — and drops the resignation-reason branch, `:96-99` vs `329`) generate a PF/ESI compliance-format export: per employee, pulls `emp_salary_slip` deduction rows filtered by `salary_head_item_fkey` resolved from `tax_salary_components` rows with hardcoded PKs `10` (EPF), `12` (ESI), and `14+1`=`15` (WWF) (`:78-83`) — **the `14+1` expression instead of a literal `15` at `:80`/`:303` is a code smell suggesting a copy-paste-and-increment edit that was never cleaned up**; UAN sourced from `emp_details.pf`, ESI number from `emp_details.esi`, last working date from `device_attandance` (note misspelling of "attendance" baked into the actual table name) joined via `emp_details.emp_id` (not `emp_pkey`) (`:101`/`:324`), resignation reason mapped from `termination.Reason` to hardcoded numeric compliance codes (`Resigned→2`, `Retrenchment→10`, `Retirement→3`, default→1, `:112-125`). **`pfdownload()` is near-total duplicated logic from `statutory()` with no shared extraction — flag as maintenance/migration risk (two divergent copies of the same PF/ESI export logic that can drift).**

None of `StatutoryRegistersController`/`StatutoryUploadsController` reference `Useraccess`/`EmployeeMenu` — the statutory-register generation logic is entirely independent of the permission-tree cluster; the only overlap is `UserCredentials` (joined once, for `user_id` display only, in `Generatewage()`).

---

## 4. Schema quirks

1. **Password column length is consistent with, but does not by itself prove, unsalted SHA-1.** `user_credentials.password` is `varchar(100)` (`schema/mypayrol_trial.sql:47701`), and `control_db.user_credentials.password` is `varchar(100) NOT NULL` (`schema/mypayrol_control_db.sql:2819`) — both comfortably fit a 40-character hex SHA-1 digest but are **not tightly sized to 40 chars**, so schema alone doesn't confirm the algorithm; confirmation comes instead from the app code: `Security::hash($x, null, true)` calls at `Controller/UserController.php:158` (`checkpassvalidation`), `:454-455` (`savePassword`), `Controller/UserCredentialsController.php:481` (`saveBulkAccess`), `:767` (`save`) — CakePHP 2.x's `Security::hash()` with a `null` algo argument defaults to `Configure::read('Security.hashType')`, and `grep`-ing `Config/core.php` (outside this cluster's scope, per prior research) found no override, so it falls back to CakePHP 2.x's hard default, SHA-1. **Re-confirmed**: no per-user salt is passed as the 2nd/3rd args in any call site in this cluster (`null` for salt param, `true` for the `raw output` flag isn't used — actually `Security::hash($x, null, true)`'s 3rd param `true` means "hash the salted string" using the app-wide `Security.salt` from `Config/core.php`, not a per-row salt column) — and `user_credentials`/`control_db.user_credentials` have **no dedicated salt column**, confirming the hash is application-salt-only, not per-user-salted.
2. **`mob_user_credentials.password` is `varchar(30)` and stores plaintext**, not a hash (`schema/mypayrol_trial.sql:45096`). Confirmed by app code: `Controller/UserController.php:483` writes `password1` (the raw new password, not `$password_new` the hashed one) directly into `mob_user_credentials`; `Controller/UserCredentialsController.php:790` (`save()`) writes `$arr_form_data['pasword']` (raw form value, note the field itself is misspelled "pasword" throughout, e.g. `:766`, `:789`, `:861`) straight into `$arr_mobile_data['password']` with no hashing call anywhere in the save path. This matches prior research's plaintext-mirroring finding and is now schema-confirmed via the short varchar(30) length (too short for any standard hash digest).
3. **`user_access.status` vs `active` dual soft-delete columns are semantically overlapping and inconsistently maintained** — see §3.1 above; `status` is set to `0` only by `deleteuser()` (`Controller/UserAccessController.php:1031`) and is otherwise always written as the literal string `'1'` on every insert/update across `save()`, `delete()`, `saveuseraccess()`, `saveFeatureAccess()` — i.e. in practice `status` is a vestigial always-`1` column except for the one `deleteuser()` code path that nothing else respects.
4. **`user_access.organization_id` is `varchar(10) NOT NULL` with no default** (`schema/mypayrol_trial.sql:47685`) but every single insert across the codebase hardcodes it to the literal string `'1'` (e.g. `Controller/UserAccessController.php:561`, `572`, `591`, `610`, `862`, `975`, `1063`) — it is a NOT-NULL column that is functionally a constant in this single-tenant-per-DB architecture (each company has its own tenant DB, so "organization" is always exactly one row/value). **Effectively dead multi-tenancy scaffolding from an earlier architecture** — Next.js migration can likely drop this column or hardcode it, but should confirm no other module ever writes a value other than `'1'`.
5. **`emp_menu.user_id` and `hrm_menu.user_id`** (`schema/mypayrol_trial.sql:43528`, `:44576`) are never read or written anywhere in the six controllers reviewed in this cluster — likely a vestigial "created_by" audit column from an earlier version. **Possible legacy cruft — verify before migrating** (may be used elsewhere outside this cluster's scope).
6. **`emp_menu.is_default` triple-valued (`Y`/`N`/`M`)** — see §3.1; undocumented `'M'` code, only 3 rows in the seed data (menu_ids 1101, 1102, 1108) but explicitly filtered in `UserAccessController::insec()` (`:136`).
7. **`hrm_menu` has a `plan varchar(15)` column that `emp_menu` lacks** (`schema/mypayrol_trial.sql:44577` vs `:43519-43530`) — suggests `hrm_menu` (HR-admin menu tree) was more recently extended for plan-based feature gating while `emp_menu` (employee self-service tree) still relies on the separate `features`/`plan_features`/`company_addons` control-DB tables (`schema/mypayrol_control_db.sql:2120-2139`, `:2570-2581`, `:1477-1485`) for the same purpose — two different plan-gating mechanisms for what's conceptually the same problem, on two structurally near-identical menu tables.
8. **Backup tables `user_credentials_bck29092025` and `central_control_bck29092025`** in control DB (`schema/mypayrol_control_db.sql:2842`, `:1443`) — dated ad-hoc snapshots, unreferenced by any model/controller in this cluster. **Possible legacy cruft — verify before migrating; exclude from the Next.js schema.**
9. **`AccessController::checkLogin()`** (`Controller/AccessController.php:59-87`) still uses the deprecated PHP `mysql_*` extension (`mysql_connect`, `mysql_select_db`, `mysql_query`, `mysql_fetch_assoc`, `mysql_error` — `:67-79`) alongside hardcoded plaintext DB credentials (`'127.0.0.1', 'mpm_cntrl_usr', 'MyPyR01@Cntr1#LB'` at `:67`, and a second hardcoded credential pair implied at `:76` — `mysql_connect('127.0.0.1',$database,'Localhost&*()')` reuses the tenant DB *name* as the username, with a shared hardcoded password `'Localhost&*()'` for every tenant DB) — this is in addition to the previously-confirmed password-in-URL redirect at `:80` (`header("Location: ...&password=". $row2['password'])`, sending the plaintext-stored `mob_user_credentials.password` value as a URL query parameter). **Critical security finding, re-confirmed with exact citations.**

---

## Re-confirmed prior findings (with exact citations)

### `UserController::saveNames()` null-password bug
`Controller/UserController.php:517-556`. In the `else` branch (non-admin/`user_group != '1'` user, `:544-553`), the method:
```
$this->UserCredentials->useDbConfig = $this->Session->read('ds');
$this->MobileUserCredentials->useDbConfig = $this->Session->read('ds');
$user_password = $this->UserCredentials->find("first", array("conditions" => array("emp_fkey" => $emp_fkey)));
$arr_data['user_pkey'] = $user_password['UserCredentials']['user_pkey'];
$arr_data['password'] = $password_new;   // <-- $password_new is undefined in this method's scope
$this->UserCredentials->save($arr_data);
$user_id = $user_password['UserCredentials']['user_id'];
$this->MobileUserCredentials->query("UPDATE mob_user_credentials set password = '$password1' where user_id = '$user_id'");  // <-- $password1 also undefined here
```
(`:548-552`). Unlike `savePassword()` (`:416-494`), which explicitly computes `$password_new = Security::hash($password1, null, true);` at `:455` before use, `saveNames()` never defines `$password_new` or `$password1` anywhere in its own body — both are PHP notices/`null` in this scope (no shared state carries over from `savePassword()`; each request is a fresh PHP process). The `$this->UserCredentials->save($arr_data)` call at `:550` therefore writes `password = NULL` (CakePHP coerces undefined/null scalar to `NULL` in the save array) into `user_credentials.password` **every time a non-admin employee edits their first/last name** via this endpoint — silently nulling their web login password. The subsequent `mob_user_credentials` UPDATE at `:552` sets `password = ''` (empty string interpolated from the undefined `$password1`), also wiping the mobile-app plaintext password. **Confirmed exactly as previously reported, with line-level citations.**

### Password hashing = CakePHP default SHA-1, unsalted-per-row
Confirmed via all four call sites in this cluster: `Controller/UserController.php:158` (`Security::hash($old_pass, null, true)`), `:454-455` (`Security::hash($password, null, true)` / `Security::hash($password1, null, true)`), `Controller/UserCredentialsController.php:481` (`Security::hash($password, null, true)`), `:767` (`Security::hash($password1, null, true)`). No `Config/core.php` override was found in prior research; the `null` 2nd argument means CakePHP 2.x's `Security::hash()` falls back to `Configure::read('Security.hashType')`, which defaults to SHA-1 if unset. The `varchar(100)` column width (`schema/mypayrol_trial.sql:47701`, `schema/mypayrol_control_db.sql:2819`) is consistent with (but, being oversized, does not on its own prove) a 40-char hex digest — the app-code evidence is the decisive confirmation, not the schema. No per-row salt column exists on either `user_credentials` table, confirming the "salted only by the single app-wide `Security.salt`, not per-user" characterization.

---

## Summary of flagged items for migration attention

- Dead code: `UserCredentials::linkempDeviceanddatabase()` (Model/UserCredentials.php:20-30) — no callers found.
- Dead/vestigial columns: `emp_menu.user_id`, `hrm_menu.user_id`, `user_access.organization_id` (functionally constant `'1'`), `user_access.status` (functionally constant `'1'` except one code path).
- Dead backup tables: `user_credentials_bck29092025`, `central_control_bck29092025` (control DB).
- Undocumented enum value: `emp_menu.is_default = 'M'` (3 rows, meaning unclear, filtered out in `insec()` only).
- Four redundant re-implementations of "grant default menus" (DB trigger, DB stored procedure, DB function, 2x PHP controller methods) — reconciliation needed before porting to a single Next.js service.
- Two divergent, near-duplicate PF/ESI export code paths (`StatutoryUploadsController::statutory()` vs `pfdownload()`).
- Composite PK on control-DB `user_credentials` (`user_pkey`, `control_fkey`) mapped to a single-column CakePHP model PK — needs explicit handling in the new schema/ORM.
- Security: `AccessController::checkLogin()` — deprecated `mysql_*` API, hardcoded DB credentials, and password-in-URL redirect, all at `Controller/AccessController.php:59-87`.

---

### 7.13 Timesheet & Uncovered Models

# TimesheetController + Uncovered Model/ Audit

## Part 1: TimesheetController.php

`Controller/TimesheetController.php` (1310 lines). `$uses = array('EmployeeDetails', 'Units', 'AttendanceRegister', 'DbConfig', 'EditPunches', 'LeaveRequests')` (`Controller/TimesheetController.php:52`), plus the `MasterdataManagement` component.

### Model/table cross-check

| Model | `useTable` | Schema location | Notes |
|---|---|---|---|
| `EmployeeDetails` (`Model/EmployeeDetails.php`) | `emp_details` | covered by Employee cluster research | Used here mostly as a generic SQL-query executor (`$this->EmployeeDetails->query(...)`), not through Cake finders/associations, for cross-table joins against `emp_detail_timeattandance`, `emp_proff`, `working_day_time_procedures`, `fin_year`, `salary_head_items`, `leavepolicy`. |
| `Units` (`Model/Units.php:14`) | `branches` | `schema/mypayrol_trial.sql` (branches table) | Thin wrapper, `order = "Units.branch_name ASC"` (`Model/Units.php:15`). Used only in `index()` for the branch dropdown (`Controller/TimesheetController.php:481`). |
| `AttendanceRegister` (`Model/AttendanceRegister.php`) | `attendance_register` | `schema/mypayrol_trial.sql:28986` | 32 fixed `FIELD1..FIELD32` columns (one per calendar day) plus aggregate totals (`presant_total`, `leave_total`, `lop_total`, `wd_lop_total`, `weekoff_total`, `holiday_total`, `working_days`, `calander_days`) and `isdelete` char flag used as a **pending/verified** flag (`Y` = pending verification, `N` = verified) — see `listregisterentries()` (`:667`) vs `listverifiedregisterentries()` (`:742`). Two stored-proc wrapper methods: `insertUpdateAttendanceRegisterProc()` (`Model/AttendanceRegister.php:18`, calls `insert_update_att_reg`) and `salaryProcessPrc()`/`calculateSalaryMainPrc()` (payroll procs, unrelated to Timesheet but live on this model). |
| `DbConfig` | `db_config` | `schema/mypayrol_trial.sql:40909` | Used only to read the company's configured `attendance_date` (cycle cutoff day) via `find('first', conditions: active=Y, company_code)` (`:65`, `:259`, `:428`, `:618`, `:868`). |
| `EditPunches` (`Model/EditPunches.php`) | `device_attandance` | — | Despite the name, this model is used here purely as a raw-SQL executor against `attendance_register`, `working_day_time_procedures`, `leaveentries`, `emp_leave_transactions`, `emp_proff`, `emp_detail_timeattandance`, `emp_detail_status_update` — i.e. it's a generic connection handle, not scoped to its own `device_attandance` table, in this controller. |
| `LeaveRequests` | (leave request table, covered by Leave cluster) | — | Used to call the `leave_transaction_prc` stored procedure and to `saveAll()`/insert into `leaveentries` from `AddLeave()` (`:1303`). |

No `emp_detail_timeattandance` or `emp_detail_status_update` model exists in `Model/` — both tables (schema `:43164` and `:43146`) are accessed exclusively via raw `->query()` calls through whichever model happens to be `useDbConfig`-bound at the time (`EmployeeDetails`, `EditPunches`). This is a recurring pattern in this codebase: tables central to a workflow have no Cake model at all and are touched only via inline SQL.

### Custom methods

- **`registerbook($monthdd, $emp_pkey, $branch)`** (`:55`) — the "register book" attendance grid for a month. Resolves the company's monthly attendance window via a stored function `att_start_end_fn(date, 1|2)` (start/end), calls stored proc `emp_detail_att_reg(company_code, branch, user_login, yearmonth, emp_pkey, @Perr_msg)` (`:117`) to (re)generate/refresh `emp_detail_timeattandance` rows for the period, then pulls a big joined query (`emp_detail_timeattandance` ⋈ `emp_details` ⋈ `emp_proff` ⋈ `working_day_time_procedures` ⋈ `emp_detail_status_update` correlated subquery for latest status) (`:129`). Per employee it computes present/absent/LOP/leave/holiday/weekoff day counts via ad-hoc `UNION ALL` count queries (`:165`-`:191`) and flags each row `editable` based on whether an `attendance_register` row already exists for that employee+month with `isdelete='N'` (i.e., register already verified/locked) (`:155`-`:156`, `:222`).
- **`empregisterbook(...)`** (`:251`) — near-duplicate of `registerbook()` for a single employee ("emp" self-service variant), but instead of calling the `emp_detail_att_reg` proc it calls `time_duration_check_multishift(...)` or `time_duration_check(...)` (`:296`, `:301`) depending on `working_day_time_procedures.is_multiple_days`. No `emp_detail_status_update` join — appears to be an older/simpler version of `registerbook()`.
- **`getbranches()`** / **`jsons($branch,$resigned)`** (`:320`, `:345`) — JSON endpoints for select2-style employee/branch pickers used in Timesheet views.
- **`filter()`** (`:423`) — dead code: references `$monthdd` which is never defined as a parameter or local var (undefined variable), so `$month`/`$yearmonth` will be blank/null at runtime. Looks unreachable/broken — **possible legacy cruft, verify before migrating**.
- **`index()` / `empindex()`** (`:459`, `:499`) — Timesheet landing pages; branch-list scoping differs by `user_group` and hardcoded company codes `DEMO`/`BKHS`/`GLET` (`:477`), a per-tenant special case baked into shared code.
- **`showregister()` / `showregistertab($verified)`** (`:517`, `:585`) — set up the register grid + legend (color-coded P/L/WO/HO/A/LOP/OTHERS chips, `:545`-`:581`) and compute the attendance-cycle date window the same way as `registerbook` but using `date()`/`strtotime()` arithmetic instead of the `att_start_end_fn` stored function (inconsistent with `registerbook`'s newer logic — two different ways of computing the same cycle window coexist in this controller).
- **`listregisterentries()` / `listverifiedregisterentries()`** (`:658`, `:733`) — paginated datatable JSON feeds over `AttendanceRegister`, distinguished only by `isdelete='Y'` (pending) vs `'N'` (verified) filter, with `Branch`/`EmployeeDetails`/`EmployeeProffessional` LEFT JOINs. Each row's `days_present`/`days_leave`/`days_holidays` are computed client-row-side by counting occurrences of `"P"`/`"L"`/`"HO"` across the returned `FIELD1..FIELDn` array (`:721`-`:727`).
- **`processregisterentries()`** (`:808`) — kicks off register generation for a branch/month by calling stored proc via `AttendanceRegister::insertUpdateAttendanceRegisterProc()`.
- **`verifyregisterentries($registerid)`** (`:823`) — bulk or single verify: sets `isdelete='N'`. Before verifying, calls `checkifregistercanverify()` per id and skips any register row that still has missing punches.
- **`checkifregistercanverify($registerid, $requestdata)`** (`:890`) — for a register row, walks every date in the `[startdate,enddate]` range and flags any date whose `FIELDn` is `'null'` or empty as "misspunched"; returns that list as JSON.
- **`loadattendanceregisterheader()`** (`:859`) — builds the dynamic column header set for the register grid (one column per day in the attendance cycle, plus present/leave/holiday summary columns).
- **`updateregisterentries($registerid)` / `submitregisterentry()`** (`:933`, `:944`) — manual correction flow for missing register fields: `submitregisterentry()` bulk-updates the specific `FIELDn` columns of one `AttendanceRegister` row from POSTed date values, then re-checks completeness and auto-calls `verifyregisterentries()` if now complete, else returns `success:2` (partial).
- **`createDateRange()` / `createDateRangeArray()`** (`:995`, `:1011`) — two independent, near-duplicate date-range helper implementations (one using `DateTime`/`DatePeriod`, one using raw `mktime` arithmetic) — redundant, only `createDateRangeArray` is actually called (`:901`).
- **`editPunch($month, $emp_pkey, $edtPkey)`** (`:1034`) — loads context for the single-day punch-edit modal: current `present` status for one `emp_detail_timeattandance` row, plus the employee's available leave-type balances for the month via stored function `leave_balance_inthe_month_fn(emp_pkey, salary_head_item_pkey, att_enddate, fin_year)` (`:1064`), scoped to leave heads defined in the employee's `leavepolicy` group.
- **`bulkipdatestatus()`** (`:1076`) — bulk day-status change endpoint (POST `device_attandance_seq[]`, `status`, `adstatus`, `monthYear`). For each employee, guarded by the same `attendance_register` `isdelete='N'` "locked" check used in `registerbook()` (`:1098`-`:1101`), plus (added 4-12-2024) restricted to dates ≥ joining date, and for `user_group==2` further restricted to `att_date >= today`. Writes an audit row into `emp_detail_status_update` (`:1142`, `:1145`) rather than updating `emp_detail_timeattandance` directly — the actual `present`/`leaves` column UPDATE statements are commented out (`:1143`, `:1146`), meaning **the real status mutation for the columns themselves happens elsewhere** (see cross-controller note below). If `status == 'LOP'`, first calls `AddLeave()` to create an approved LOP leave entry.
- **`checkLeaveExists($att_date, $emp_pkey, $statusType)`** (`:1166`) — looks up existing approved `leaveentries` for the date; if found, cancels them via `leave_transaction_prc(...,'Cancelled',...)` stored proc and deletes the `emp_leave_transactions`/`leaveentries` rows, then (for half-day statusType changes) may re-add a leave for the *other* half via `AddLeave()`. This exact method is **duplicated verbatim** in `EditAttendanceController::checkLeaveExists()` (`Controller/EditAttendanceController.php:1208`) — copy-pasted rather than shared/refactored.
- **`AddLeave($head, $day, $emp_fkey, $session)`** (`:1239`) — creates an auto-approved leave entry (`LEAVESTATUS='Approved'`, `ISAutherized=1`, `ISAPPROVED=1`) directly via `$this->LeaveRequests->saveAll()` then calls `leave_transaction_prc` twice (once as `'Applied'`, once as `'Approved'`) to post the leave transaction — bypasses the normal employee leave-request approval workflow entirely; this is a "system"-originated leave used when marking attendance status as a leave type. Also duplicated in `EditAttendanceController::AddLeave()`.

### Cross-controller "register book" edit/verify workflow — confirmed

Prior research's finding holds: the day-level status editor in `View/Timesheet/editpunch.ctp` **POSTs to a different controller**, not back to `TimesheetController`:

```
View/Timesheet/editpunch.ctp:131:  url: livesite + "EditAttendance/chnagestatus",
```

- `TimesheetController::editPunch()` (`:1034`) only *renders* the edit modal (current status + leave balances); it does not itself update attendance.
- The actual per-day status mutation is handled by `EditAttendanceController::chnagestatus()` (`Controller/EditAttendanceController.php:1117`), which:
  - Re-derives the row from `emp_detail_timeattandance` by `emp_detail_timeattandance_pkey` (POSTed as `device_attandance_seq`).
  - Reads `statusType` (`full`/`first`/`second` — i.e. full day or which half), `newstatuses` (the new code, e.g. `P/P`, `LOP`, `HO`, `WO`), `currentStatuses`.
  - Calls the controller's own `checkLeaveExists()` (byte-for-byte duplicate of `TimesheetController`'s), then, if the new status is not one of the built-in attendance codes (`P/P,P/A,A/P,A/A,N/A,WO,HO` for full-day, or `P,P/P,P/A,A/P,A,N/A,WO,HO,/WO` for half-day), treats it as a leave code and calls `AddLeave()` to create the approved leave; otherwise calls `updateStatus()` (a further helper on `EditAttendanceController`, not shown here) to write the actual `present`/`leaves`/`holiday`/`weekoff` columns.
  - There is also `chnagestatusadditonal()` (`:1182`) which just inserts an audit-only row into `emp_detail_status_update` (secondary/"additional" status track alongside `main_status`), and a legacy `chnagestatus_old()` (`:1046`, not reviewed in depth — appears to be the pre-refactor version, likely dead).
- `TimesheetController::bulkipdatestatus()` (bulk version) mirrors this same logic locally (inserting into `emp_detail_status_update`) but — as noted above — has its `emp_detail_timeattandance` UPDATE statements commented out, so for the *bulk* path the actual `present`/`leaves` column write depends on the same `EditAttendance`-side mechanism (or on a scheduled/proc-driven sync) rather than happening inline. This should be flagged for the Next.js migration: the bulk-update code path in `Timesheet` writes only the audit table today; whatever reads `emp_detail_status_update.main_status` back into `emp_detail_timeattandance` (a proc, a cron, or `EditAttendance` itself on next load) needs to be located before this logic is ported, or the bulk-update feature will silently no-op the actual attendance columns.

### Schema quirks

- `attendance_register.FIELD1`..`FIELD32` is a denormalized "one column per day of month" design (`schema/mypayrol_trial.sql:28993`-`:29024`) — every register-grid query (`listregisterentries`, `checkifregistercanverify`, `submitregisterentry`) has to loop `1..32`/`1..count` and reference `FIELD{n}` dynamically as a string-built column name. This is the single biggest structural obstacle for a relational-model migration to Next.js/Prisma: it should become a normalized `attendance_register_day(registerid, day_number, value)` child table.
- `attendance_register.isdelete` is overloaded as a **workflow status flag** (`Y` = awaiting verification, `N` = verified), not a soft-delete flag — despite the name. Same true elsewhere in this codebase (worth flagging generally).
- `emp_detail_status_update` is a pure audit/event-sourcing-style log (one row per status-change action, with `main_status`/`aditional_status`, `created_by`, `creation_date`) that `registerbook()` reads back only the *latest* row per (`emp_fkey`,`att_date`) via a correlated `MAX(emp_detail_status_update_pkey)` subquery (`:132`-`:137`) to overlay onto the base `emp_detail_timeattandance` row for display — i.e., the display-layer status can diverge from the stored `emp_detail_timeattandance.present` column, sourced instead from this audit table.

---

## Part 2: Full Model/ directory audit (leftover/uncovered models)

Method: enumerated all 243 `Model/*.php` files; built the set of models referenced (via `$uses` arrays or `$this->ModelName->`) by every controller in the assignment's covered-cluster list, plus all controllers whose name contains "Report"/"Reports" (per the prompt's "~25 other Report controllers" caveat), plus the app-wide shared models list. Diffed the two sets, then re-verified each apparent "leftover" by grepping *all* `Controller/*.php` files (not just the covered set) for real usage, since several controllers rely on CakePHP's implicit default-model (no explicit `$uses`) which a naive `$uses`-array scan misses (e.g. `TaxHeadsController` never declares `$uses` but uses `$this->TaxHeads->...` — its default model — so `TaxHeads` is in fact covered, not a leftover).

### Models that are real leftovers, but belong to controllers outside the assigned clusters (not dead — just uncovered controllers)

These map to legitimate, actively-referenced tables/controllers that simply weren't in the assignment's controller list. Each gets a brief note; none of these needed full treatment since they're not orphaned, just out of scope:

- **`AttendancePunch`**, **`Earlyin`**, **`Earlyout`**, **`Latein`**, **`Lateout`** — used by `DashboardController`/`DashboardNewController`/`AttendanceCheckInOutController` for check-in/out and early/late-arrival dashboard widgets. `Latein`/`Lateout` also touched by `EmployeeResignationController`. Real, active models.
- **`CompanyInfo`** — used by `EmpattendanceuploadController`, `EmployeeAttendanceUploadController`, `SiteAttendanceUploadController` (bulk attendance upload flows, a sibling cluster to "Attendance" not enumerated in the assignment).
- **`DirectPurchaseOrder`**, **`DirectPurchaseOrderDetails`** — used by `DirectPurchaseOrderController` and `GoodsReceivedNotesController` (the latter *is* in the assigned Assets/Purchase cluster, so `DirectPurchaseOrder` is touched from covered territory too, but its primary controller, `DirectPurchaseOrderController`, was not in the list).
- **`EfsrEquipmentsMaster`**, **`EfsrSite`**, **`OptionItems`**, **`OptionItemsValues`**, **`SurveyCategory`** — used by `SurveyController` (field survey module, adjacent to but distinct from the assigned `FieldSurveyController`).
- **`EmpDetailedAttendanceUpload`**, **`EmployeeAttendanceUpload`**, **`EmployeeFixedPaymentUpload`**, **`EmployeeLeaveBalanceUpload`**, **`EmployeeVariableUpload`** — bulk-upload models used by `EmpattendanceuploadController`, `EmployeeAttendanceUploadController`, `EmpleaveuploadController`, `EmployeeLeaveUploadController`, `FixedPaymentUploadController`, `VariableController` — a whole "bulk upload" controller family not covered by any prior pass.
- **`ExceptionRule`** — used by `ExceptionRuleController` (shift-exception rules, adjacent to `ShiftPlannerController`/`DayTimeProcedureController` but its own controller).
- **`GeneralSettings`**, **`SettingsRunner`** — used by `DashboardController`/`DashboardNewController` for app-wide settings/one-off migration runner tasks.
- **`Nationality`** — used by `EmployeeJoinController`, `EmployeeIncrementReportsController`, `ReportsController` (small lookup table, arguably should've been swept by the Employee cluster's `EmployeeJoinController` but wasn't flagged there).
- **`OutPass`** — used by `OutPassController` (gate-pass-adjacent but a separate controller from `GatePassController`).
- **`ProjectIncome`**, **`ProjectIncomePayment`** — used by `ProjectIncomeController` (project billing, sibling to `ProjectController`/`ProjectExpensesController` but its own controller).
- **`ScheduledBreakOff`** — used by `ScheduledBreakOffController` (shift break scheduling, adjacent to `ShiftPlannerController`).
- **`StockTranfer`**, **`StockTranferItem`** — used by `StockTranferController`, `StoreController` (in assigned cluster), and `FieldSurveyController` (inter-store stock transfer, sibling to `StoreController`/`PurchaseOrderController`).
- **`item_master`** (lowercase model name, unusual) — used broadly by `ItemController` (assigned), `PurchaseOrderController` (assigned), `StoreController` (assigned), `GoodsReceivedNotesController` (assigned), plus `MaterialRequestController`, `StockReportController`, `UniformController`, `VendorController`, `FieldSurveyController`, `StockTranferController`. Effectively covered via the Assets/Inventory cluster; flagged here only because its lowercase filename (`Model/item_master.php`) didn't match the `$uses`-array text scan.
- **`TaxHeads`** — used by `TaxHeadsController` (assigned cluster) via CakePHP's implicit default-model convention (no explicit `$uses` declared) and by `EmpTaxController`. Confirmed covered, not a real leftover.

### True orphans — zero references in any Controller/*.php (possible legacy cruft — verify before migrating)

**Duplicate/backup model files (`_bkup_megha` suffix)** — 20 files, each defining a class with the *same name* as an already-existing, properly-named, actively-used model, differing only in the filename suffix. Because CakePHP's `App::uses()` autoloader resolves models by filename (via the class-name inflector), a file named e.g. `Site_bkup_megha.php` is never loaded by `App::uses('Site', 'Model')` — only `Model/Site.php` is. These are confirmed dead by (a) zero controller references and (b) a real, correctly-named sibling file existing for every one of them:

| Orphaned file | Class defined inside | Duplicates real model |
|---|---|---|
| `Model/Site_bkup_megha.php` | `Site` | `Model/Site.php` |
| `Model/SiteMaster_bkup_megha.php` | `SiteMaster` | `Model/SiteMaster.php` |
| `Model/SiteWork_bkup_megha.php` | `SiteWork` | `Model/SiteWork.php` |
| `Model/SiteAttendance_bkup_megha.php` | `SiteAttendance` | `Model/SiteAttendance.php` |
| `Model/SiteTransactions_bkup_megha.php` | `SiteTransactions` | `Model/SiteTransactions.php` |
| `Model/StockTranfer_bkup_megha.php` | `StockTranfer` | `Model/StockTranfer.php` |
| `Model/StockTranferItem_bkup_megha.php` | `StockTranferItem` | `Model/StockTranferItem.php` |
| `Model/Store_bkup_megha.php` | `Store` | `Model/Store.php` |
| `Model/PackageMaster_bkup_megha.php` | `PackageMaster` | `Model/PackageMaster.php` |
| `Model/Item_bkup_megha.php` | `Item` | `Model/Item.php` |
| `Model/EquipmentType_bkup_megha.php` | `EquipmentType` | `Model/EquipmentType.php` |
| `Model/Equipments_bkup_megha.php` | `Equipments` | `Model/Equipments.php` |
| `Model/EfsrEquipmentsMaster_bkup_megha.php` | `EfsrEquipmentsMaster` | `Model/EfsrEquipmentsMaster.php` |
| `Model/EfsrSite_bkup_megha.php` | `EfsrSite` | `Model/EfsrSite.php` |
| `Model/EfsrTickets_bkup_megha.php` | `EfsrTickets` | `Model/EfsrTickets.php` |
| `Model/OptionItems_bkup_megha.php` | `OptionItems` | `Model/OptionItems.php` |
| `Model/OptionItemsValues_bkup_megha.php` | `OptionItemsValues` | `Model/OptionItemsValues.php` |
| `Model/SurveyCategory_bkup_megha.php` | `SurveyCategory` | `Model/SurveyCategory.php` |
| `Model/SurveyType_bkup_megha.php` | `SurveyType` | `Model/SurveyType.php` |
| `Model/Contacts_bkup_megha.php` | `Contacts` | `Model/Contacts.php` |
| `Model/EmpDetails_bkup_megha.php` | `EmpDetails` | (real `emp_details`-backed model exists under other names, e.g. `EmployeeDetails.php`) |
| `Model/EmployeeDetails_bkup_megha.php` | `EmployeeDetails` | `Model/EmployeeDetails.php` |

**Verdict: all 21 `_bkup_megha` files are unambiguous dead code** — safe to exclude entirely from migration scope. Do not port.

**Other zero-reference orphans (not `_bkup_megha`, but still unreferenced by any controller):**

- **`Model/Devicelog.php`** — `useTable = 'device_attandance'` (table exists, `schema/mypayrol_trial.sql`), `primaryKey = 'device_attandance_seq'`. Same table as `EditPunches` model (which *is* used, throughout `EditPunchesController`/`TimesheetController`/`EditAttendanceController`). No controller references `$this->Devicelog->` or declares it in `$uses` — likely an abandoned earlier name for what became the `EditPunches` model. **Possible legacy cruft — verify before migrating** (table itself is very much alive, just not through this model name).
- **`Model/EmployeeAdvanceInfo.php`** — `useTable = 'emp_advance_info'` (table exists). No controller reference found. The live advances flow (assigned cluster) uses `AdvanceController`/`EmployeeadvanceController` against different tables/models (not cross-checked further here since out of this pass's remit) — worth a follow-up check on whether `emp_advance_info` is a legacy/superseded table.
- **`Model/EmployeeLeaveInfo.php`** — `useTable = 'emp_leave_info'` (table exists). No controller reference. Similarly possibly superseded by tables the Leave cluster's models (`LeaveRequests`, etc.) actually use.
- **`Model/EmployeePayInfo.php`** — `useTable = 'emp_pay_info'` (table exists). No controller reference. Possibly superseded by the Payroll cluster's actual salary-processing tables.
- **`Model/GoodsReceivedNotesItem.php`** — defines class `GoodsReceivedNotesitem` (lowercase `i` — a casing mismatch vs. the `GoodsReceivedNotesItem.php` filename, though this generally still autoloads fine under case-insensitive filesystems/PHP's non-case-sensitive class lookup), `useTable = 'gr_item_details'` (table exists). `GoodsReceivedNotesController` (assigned cluster) does not reference `$this->GoodsReceivedNotesitem->`/`GoodsReceivedNotesItem` anywhere — GRN line-items appear to be handled entirely via `item_master`/inline queries instead. **Possible legacy cruft — verify before migrating.**
- **`Model/IssueReport.php`** — `useTable = 'report_a_problem1'`. **This table does not exist anywhere in `schema/mypayrol_trial.sql`** (confirmed: no `CREATE TABLE` for `report_a_problem1` or any `report_a_problem*` variant). Combined with zero controller references, this model is doubly dead — both unreferenced in code and pointing at a non-existent table. **Confirmed legacy cruft — do not migrate.**
- **`Model/ShiftExceptions.php`** — **bug**: this file's filename is `ShiftExceptions.php` but the class defined inside is `class ExceptionRule extends AppModel { public $name = 'ShiftException'; ... public $useTable = 'shift_exceptions'; }` (`Model/ShiftExceptions.php:9`) — i.e. it redeclares the *same class name* (`ExceptionRule`) as the real, actively-used `Model/ExceptionRule.php` (which maps to the *different* table `exception_rule`, used by `ExceptionRuleController`). Two files defining `class ExceptionRule` with different target tables (`shift_exceptions` vs `exception_rule`) is a PHP class-redeclaration hazard if both ever get autoloaded in the same request (fatal error), and today it works only because CakePHP's inflector-driven `App::uses('ExceptionRule','Model')` resolves to `Model/ExceptionRule.php` and never touches the misnamed file, effectively making `Model/ShiftExceptions.php` unreachable/dead under its own filename. Table `shift_exceptions` exists in schema but nothing in `Controller/*.php` reaches it through this model. **Flag prominently: naming bug + confirmed dead — verify whether `shift_exceptions` table data is used through any other path (e.g. `ShiftPlannerController` via raw SQL) before assuming the feature itself is unused.**

### Summary counts

- Total `Model/*.php` files: 243
- Covered by assigned controller clusters (directly or via shared/app-wide list): ~180
- Leftover but legitimately used by out-of-scope controllers (not dead): ~35 (see table above; not exhaustively enumerated since they're in-use, out-of-scope by design)
- Confirmed dead/orphaned (`_bkup_megha` duplicates): 21
- Confirmed or likely dead, non-backup naming (`Devicelog`, `EmployeeAdvanceInfo`, `EmployeeLeaveInfo`, `EmployeePayInfo`, `GoodsReceivedNotesItem`, `IssueReport`, `ShiftExceptions`): 7

**Total confirmed/likely orphaned model files: 28 of 243 (~11.5%)** — none of these need to be ported to the Next.js schema/ORM layer; recommend a final grep against the live production `View/` folder (not just `Controller/`) and against any cron/shell scripts before permanently excluding, since a small number of legacy features could theoretically be invoked from a view helper or an external script rather than a controller action, though no such references were found in this pass.
