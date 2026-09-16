# Legacy MySQL DB vs Senior's New Rizo PostgreSQL DB — Object-by-Object Diff

Pure reference comparison. Not a migration plan, not a recommendation document. Every legacy table and every legacy procedure/function/trigger is checked one-by-one against the senior's re-architected "New Rizo" app.

**Sources used:**
- Legacy schema dumps: `legacy/schema/mypayrol_control_db.sql` (73 tables, 20 procs/fns), `legacy/schema/mypayrol_trial.sql` (236 tables, 91 procs/fns, 24 triggers)
- Senior docs: `DOCS/_MIGRATION_GUIDE.md` §3 (table-by-table map, ~45 in-scope tables with column detail), `DOCS/_DB_PROCEDURES.md` (procedure catalogue), `2_DATABASE_SCHEMA_MAPPING.md`, `DOCS/_DATA_control.md`, `DOCS/_DATA_tenant.md`
- Senior code (ground truth, checked against docs): `New Rizo/backend/src/controllers/**/*.js`, `New Rizo/backend/src/services/payroll.service.js`, `New Rizo/backend/src/services/tax.service.js`, `New Rizo/db/init.sql`

**Method note on "Senior App Equivalent":** Only 7 tables have a checked-in `CREATE TABLE` statement anywhere in the New Rizo repo (`salary_structures`, `salary_structure_details`, `employee_promotions`, `event_reminders`, `attendance_exception_rules`, `attendance_exception_applied`, `user_branch_access`) plus the single `company` table in `db/init.sql`. For every other table named below, the evidence is a live SQL reference (`SELECT`/`INSERT`/`UPDATE`/`JOIN`) found in the controllers/services — i.e., the table demonstrably exists in the senior's live dev database even though its `CREATE TABLE` is not in version control. Where no such reference exists anywhere in the codebase or docs, status is "Not Found."

---

## PART 1 — TABLE-BY-TABLE COMPARISON

### 1.1 Employee Master / HR Admin Module

| Legacy Table | Senior App Equivalent | Status | Notes |
|---|---|---|---|
| `emp_details` | `employees` | Renamed/Restructured | See detail below. |
| `emp_proff` | `employees` (merged) | Restructured | Legacy split person data (`emp_details`) from job data (`emp_proff`); migration guide models it as a separate `employee_professional_details` table, but live code queries a single flat `employees` table with department/designation/branch columns — the two were merged, not kept as two joined tables. |
| `emp_join` | `employee_joining` | Renamed | New joining workflow table; legacy `emp_join` duplicated `emp_details` fields for the new-hire capture step. |
| `emp_family` | `employee_family` | Renamed | Column-for-column close match (see migration guide). |
| `family` | `employee_family` (duplicate concept) | Restructured | Legacy has two overlapping family tables (`family`, `emp_family`); only one survives. |
| `emp_banks` | *(bank name lookup — folded into a masters list, no longer a business entity)* | Restructured | Migration guide: bank name list → `bank_master`; not confirmed as a live table but per-employee bank fields moved onto `employees`/`employee_bank_accounts`. |
| `emp_tax_transactions` | `employee_tax_declarations` | Renamed | See detail below. |
| `qualifcations` | `employee_education` | Renamed | Typo ("qualifcations") fixed in new name; migration guide calls it `employee_qualifications`, but live code uses `employee_education` / `joining_education` — naming diverged further from the plan. |
| `Education` | `joining_education` | Renamed | Distinct from `qualifcations`; captured during the joining workflow specifically. |
| `work_experience` | `employee_experience` / `joining_experience` | Renamed | Two live tables — one for the employee profile, one for the joining capture step. |
| `emp_passport_visa` | `employee_documents` | Restructured | Migration guide maps to a dedicated `employee_documents` doc-metadata table; generalized beyond just passport/visa. |
| `termination` | `employee_resignation` (concept absorbed) | Restructured | See detail below. No table literally named `termination`; resignation/termination modeled as one workflow. |
| `resignation_requests` | `employee_resignation` | Renamed | |
| `resignation_accept` | `employee_resignation` (status field) | Restructured | Legacy kept accept as a separate table; new system uses a status column on one resignation table. |
| `promotions` | `employee_promotions` | Renamed | Checked-in `CREATE TABLE` exists in `promotions.controller.js`. See detail below. |
| `emp_config` | *(no direct equivalent found)* | Not Found | Legacy's generic "policy assignment" abstraction (SHIFT/HOLIDAY/SALARY/LEAVE/etc keyed by `type`) has no analog; new system assigns each policy via its own dedicated FK column directly on `employees`/`employee_joining` rather than a generic assignment table. |
| `emp_config_history` | *(no direct equivalent found)* | Not Found | Companion history table to `emp_config`; same reasoning. |
| `emp_structure`, `employee_structure_vview` | *(no equivalent found)* | Not Found | Legacy reporting-hierarchy structure table/view; no hierarchy table found in New Rizo code. |
| `documents` | `joining_documents` / `employee_documents` | Restructured | Generic employee document-storage table; consolidated into the joining-specific and general documents tables (see also `emp_documents`, `document_allocation`, `document_upload`, `doc_images` above — legacy had five overlapping document tables that collapse to two live ones). |
| `emp_ctc_detail` | *(no directly confirmed equivalent — folds into the same gap as `emp_ctc_transaction`)* | Not Found | CTC breakdown detail table; consistent with §1.3 finding that CTC transaction history has no confirmed live table. |
| `emp_early_in`, `emp_early_out`, `emp_late_in`, `emp_late_out` | *(no equivalent found)* | Not Found | Legacy early/late punch flag tracking tables (attendance-adjacent); no distinct early/late tracking table referenced in New Rizo controllers — likely computed as a derived status on `attendance` rows now rather than persisted separately, but not confirmed. |
| `emp_documents` | `employee_documents` / `joining_documents` | Renamed | |
| `employee_info` | *(no equivalent found)* | Not Found | Redundant/legacy staging table; nothing analogous referenced. |
| `employees_mssql` | — | Out of Scope | Migration guide explicitly excludes as a third-party staging/import table. |
| `emp_upload`, `emp_salcomp_upload`, `emp_variables_upload`, `emp_variable_pay_upload`, `emp_ctc_upload`, `emp_attendance_upload`, `emp_detailed_attendance_uploads`, `emp_leave_upload`, `leave_balance_upload` | `variable_salary`, `emp_fixed_component_upload` (partial) | Restructured | Bulk-upload staging tables collapsed; only `variable_salary` and `emp_fixed_component_upload` (same name kept) confirmed live. Attendance/leave upload staging tables have no confirmed New Rizo equivalent — file uploads appear to write directly to final tables now rather than a staging table. |
| `emp_advance`, `emp_advance_info` | `employee_advance` | Renamed | CRUD confirmed live in `hrModules.controller.js`; not wired into monthly payroll calc (see Part 2). |
| `emp_loan`, `emp_loan_info` | `employee_loans`, `loan_repayments` | Renamed/Restructured | Legacy single table with EMI schedule embedded → two normalized tables (loan header + repayment ledger). Confirmed live. Not wired into monthly payroll calc. |
| `emp_expense` | `emp_expenses` | Renamed | |
| `expense_type` | `expense_types`, `expense_items` | Renamed/Restructured | Split into category + line-item masters. |
| `employee_regularaization` (typo in original) | `employee_regularization` | Renamed | Typo fixed (double-typo original name — "regularaization" — cleaned up to "regularization"). |
| `emp_detail_status_update` | *(no equivalent found)* | Not Found | Legacy audit/status-change staging table. |
| `emp_details_audit` | — | Out of Scope | Migration guide explicitly excludes (old audit trail; new system starts its own). |
| `notice_period` | `notice_periods` | Renamed | |
| `emp_menu`, `hrm_menu` | `user_menu_access` | Restructured | Legacy per-employee menu-item tables replaced by RBAC-style menu access table. |
| `admin_dashboard`, `emp_dashboard` | *(no equivalent found)* | Not Found | Dashboards presumably computed on the fly in the new app rather than persisted config tables. |
| `activity` | *(no equivalent found)* | Not Found | Generic activity/audit log — no equivalent audit_log table found in New Rizo code. |
| `wizard_config` | *(no equivalent found)* | Not Found | Onboarding wizard state; nothing analogous found. |
| `genaral_setings` (typo) | `company_settings` | Renamed | Typo fixed; company-level settings confirmed live. |
| `organization_info` | `company` | Restructured | Folded into the single `company` table (the only table defined in checked-in `db/init.sql`). |
| `policy_info` | *(no equivalent found)* | Not Found | |
| `settings_runner` | *(no equivalent found)* | Not Found | Legacy internal job/settings execution tracker. |
| `todo`, `test`, `wishes`, `miss_action` | — | Out of Scope | Migration guide explicitly lists `test`/`todo` as throwaway/utility tables; `wishes` and `miss_action` are similarly unused scratch tables with no business meaning. |
| `history` | *(no equivalent found)* | Not Found | Generic change-history table; superseded conceptually by a proper audit approach the new system doesn't appear to have built yet either. |
| `report_audit` | *(no equivalent found)* | Not Found | Used by legacy `inactive_db_audit_prc` to gauge tenant activity; no analog. |
| `reportcriterias` | *(no equivalent found)* | Not Found | Report filter-criteria seed table (control-DB procs `insert_reportcriterias_*` populate this from the control DB) — new system's report filters appear to be defined in code/query params, not a DB-driven criteria table. |
| `inactive_list` | — | Out of Scope | Migration guide explicitly lists as throwaway/utility. |
| `user_access`, `user_feature_branch_access` | `user_branch_access`, `user_company_access`, `user_menu_access` | Restructured | Legacy's single flat access table split into three purpose-specific RBAC tables. `user_branch_access` has a checked-in `CREATE TABLE` in `users.routes.js`. |
| `additional_details`, `comp_contact_info`, `contacts` | *(no equivalent found)* | Not Found | Miscellaneous extra-fields tables; nothing analogous referenced in controllers. |
| `countries_nationality` | *(present in control DB only — see §1.10)* | — | Duplicated between tenant and control DB in legacy; the control-DB copy is the canonical one. |
| `document_allocation`, `document_upload`, `doc_images` | `joining_documents`, `employee_documents` | Restructured | Consolidated into two documents tables scoped to joining vs. general profile. |
| `doc_template` | `doc_templates` | Renamed | |

#### Column-level detail — critical Employee tables

**`emp_details` → `employees`** (per `_MIGRATION_GUIDE.md` §3.1, verified against live query usage):
- `emp_pkey` → `id` (new bigserial PK); `emp_pkey` preserved as `original_id`
- `company_code` (varchar) → `company_id` (bigint FK) — the single biggest structural change: DB-per-tenant physical isolation → `company_id` column on every table
- `emp_id` → `emp_code`
- `middile_name` (typo) → `middle_name`; `maritual_status` (typo) → `marital_status`
- `pf` → `pf_number`; `esi` → `esi_number`; `pan_no` → `pan_number`; `blood` → `blood_group`
- `is_manager` tinyint(4) → boolean
- `international_worker`, `physical_handicap` varchar(10) 'Y'/'N' → boolean
- New columns added with no legacy source: `gender`, `profile_photo_url` (legacy had `profile_pic` — renamed not added), `aadhaar_number` — per the planning doc (`2_DATABASE_SCHEMA_MAPPING.md`), not confirmed present in live schema.
- `parent` (manager FK) → `manager_employee_id`, resolved via ID-map at migration time, not a live structural note.

**`emp_proff` → `employees` (merged)**: Legacy job-data columns (`designation`, `emp_dept`, `emp_grade`, `emp_branch`, `structure_id`, `day_time_seq`/shift, `HOLIDAY_GROUP_ID`, `LEAVEPOLICY_GROUP_ID`) become FK columns directly on the single `employees` row rather than a joined second table as `_MIGRATION_GUIDE.md` proposed (`employee_professional_details`). This is a divergence between the plan doc and what was actually built — flag for verification against senior's live dev DB.

**`emp_tax_transactions` → `employee_tax_declarations`**: `tax_value` → `declared_amount`; `fin_year` → `financial_year_id` FK; `attr1`–`attr5` collapsed to `meta_json`; `locked` → `is_locked` boolean. Confirmed live via `employee_tax_declarations` reference in tax controllers.

**`termination` → resignation/termination workflow**: Legacy had a rich `termination` table (gratuity/settlement inputs: `notice_period`, `leave_balance`, `encashed_days`, `payroll_days`, dual approval flags `is_authorized`/`is_approved`). No `termination` table found live; `employee_resignation` appears to be the sole survivor. Whether the F&F-relevant fields (leave balance snapshot, encashed days) migrated onto `employee_resignation` or were dropped could not be confirmed from available code (no CRUD for those specific columns spotted in `fullFinal.controller.js`, which instead recomputes leave balance and loan/advance balances live from `employee_leave_balance` and `employee_loans`/`employee_advance` — see Part 2 `final_settle_pay_prc`).

**`promotions` → `employee_promotions`**: Confirmed checked-in `CREATE TABLE` at `New Rizo/backend/src/controllers/promotions.controller.js:141`. `approved_status` 'Y'/'N' → `is_approved` boolean; `annual_gross` (stored as varchar in legacy!) → proper `decimal(15,2)`.

---

### 1.2 Attendance Module

| Legacy Table | Senior App Equivalent | Status | Notes |
|---|---|---|---|
| `attendance_register` | `attendance` (+ `attendance_audit`) | Restructured | See detail below — the FIELD1–FIELD31 anti-pattern is eliminated. |
| `attandance` (control-DB mobile check-in/out, typo'd name) | `attendance` | Restructured | Different table from tenant `attendance_register`; both collapse into the same new per-day model. |
| `attendancelogs` | — | Out of Scope | Migration guide explicitly excludes as a staging/import table from third-party biometric software. |
| `attendance_punch` | `attendance_punches` | Renamed | |
| `attendance_register_history`, `attendance_register_rep`, `attendance_register_update`, `register_history` | *(collapsed — no separate staging tables found)* | Restructured | Legacy kept draft/staging/history variants of the register as distinct physical tables (see `insert_update_att_reg_rep` in Part 2); new model uses a status column instead, per `_DB_PROCEDURES.md`'s own recommendation, though the actual live status-flag column could not be confirmed from checked-in code. |
| `att_in`, `att_out` | — | Out of Scope | Migration guide explicitly excludes as staging/import tables. |
| `device_attandance` | `device_punch_logs` | Renamed | Only last 2 years migrated per migration guide scope note. |
| `device_attandance_hist` | — | Out of Scope | Migration guide explicitly excludes as redundant raw-device history. |
| `emp_detail_timeattandance` | `attendance` | Restructured | Per-employee-per-day punch-derived record; merges into the unified daily attendance table. |
| `emp_detail_attandance_reg` | *(no equivalent found — legacy internal working table)* | Not Found | Companion to `emp_detail_att_reg` procedure (Part 2); a working/staging table, not a system-of-record. |
| `emp_ot_master`, `emp_ot_timeattandance` | `monthly_ot_override` | Restructured | Legacy split OT master config from OT computed records; new system has one override/adjustment table, with computed OT presumably derived rather than stored per the biometric processing note in Part 2. |
| `emp_shift_planner` | `shifts` (assignment folded in) | Restructured | See `_MIGRATION_GUIDE.md` mapping to `employee_shift_plans`; live code shows only a `shifts` (definitions) table confirmed, no separate plan/assignment table found — planning doc and live code diverge again here. |
| `emp_site_detail_timeattandance` | *(no equivalent found)* | Not Found | Site/contract-labour attendance; no `site_*` attendance table referenced anywhere in New Rizo code — the entire site/contract-labour attendance sub-module appears unported. |
| `holidays`, `holiday_group` | `holiday_groups` (+ implied `holidays`) | Renamed | `holiday_groups` confirmed via routes (`holidayCalendar.routes.js`); `holidays` itself not directly grepped but presumed present given the route file's purpose. |
| `present_employee`, `present_today`, `present_today_all` | *(no equivalent found)* | Not Found | Legacy real-time dashboard cache tables; new system likely computes these live rather than persisting them. |
| `scheduled_break_off` | `scheduled_break_off` | Renamed/Same name | Table name literally unchanged; confirmed live. |
| `shift_exceptions`, `branch_exceptions` | `attendance_exception_rules`, `attendance_exception_applied` | Renamed | Checked-in `CREATE TABLE`s in `attendanceExceptions.controller.js` — one of the only 7 tables actually defined in code. |
| `site_attendance`, `site_attendance_register`, `site_shift_close` | *(no equivalent found)* | Not Found | See `emp_site_detail_timeattandance` — the entire site attendance approval-and-pay-rate pipeline (`site_attendance_bu` trigger logic, Part 2) has no trace in New Rizo. |
| `site_history` | *(no equivalent found)* | Not Found | |
| `site_transactions`, `site_trans_additional` | *(no equivalent found)* | Not Found | Site billing-rate transaction tables feeding `site_rate_update_prc` (Part 2); part of the unported site/contract-labour module. |
| `working_day_time_procedures` | `shifts` | Renamed | See `_MIGRATION_GUIDE.md` §3.6 detail below. |
| `calendar_table` | — | Out of Scope | Migration guide explicitly excludes as a throwaway/utility table. |
| `contracted_days`, `date_intervel_monthyear`, `t_month` | *(no equivalent found)* | Not Found | Internal calculation helper tables used by attendance-period procedures (`att_start_end_fn`, etc.); no equivalent persisted table needed if the new system computes these in application code (plausible but unconfirmed). |
| `Upload_leave_errirs` (typo) | *(no equivalent found)* | Not Found | Error log for leave-balance-upload batch jobs. |
| `mob_report`, `mob_user_credentials`, `mob_user_locations`, `mob_user_login_auditor`, `mob_user_login_history`, `mob_user_tracking` | — | Out of Scope | Migration guide explicitly excludes mobile field-tracking tables as operational location data, not business records. |

#### Column-level detail — critical Attendance tables

**`attendance_register` → `attendance` + `attendance_audit`**: This is the single most consequential structural change in the entire schema. Legacy stored one row per employee per month with **`FIELD1`–`FIELD31`** columns (varchar(20) each) holding a day-status code, plus monthly aggregate columns (`presant_total`, `leave_total`, `lop_total`, `weekoff_total`, `holiday_total`, `working_days`, `calander_days`). `_MIGRATION_GUIDE.md` §4c calls this the "FIELD1–FIELD31 Anti-Pattern" and specifies unpivoting into `attendance_monthly_summary` + `attendance_daily_records` (one row per employee per calendar day, with a normalized `status` column and a `source_field` audit column pointing back to the original FIELDn). Live grep of controllers shows the actual table name used is simply `attendance` (not the two-table split from the doc) plus a separate `attendance_audit` table — another instance of the planning doc and the shipped schema disagreeing on exact table names, though the core normalization (day-level rows, not FIELD1-31) is consistent with the plan.

**`working_day_time_procedures` → `shifts`**: `Sunday`–`Saturday` char(1) 'Y'/'N' weekoff flags → `is_weekoff_sunday`…`is_weekoff_saturday` booleans; `Sunday_F`–`Saturday_F` half-weekoff flags → `is_half_weekoff_*` booleans; `on_dutty1`/`off_dutty1` → `shift_start_1`/`shift_end_1` time columns (up to 6 shift windows supported both old and new); OT threshold minute-fields renamed but structurally preserved.

---

### 1.3 Payroll & Salary Structure Module

| Legacy Table | Senior App Equivalent | Status | Notes |
|---|---|---|---|
| `salary_structure` | `salary_structures` | Renamed | Checked-in `CREATE TABLE` in `salaryStructure.controller.js:5`. See detail below. |
| `salary_structure_details` | `salary_structure_details` | Same name | Checked-in `CREATE TABLE` at `salaryStructure.controller.js:18`. See detail below — `structure_derived_perc` preservation is flagged as critical in the migration guide. |
| `salary_structure_details_b4wwf` | — | Not Found | Backup/pre-WWF-change snapshot table; no equivalent needed by design (it's a one-off legacy backup). |
| `emp_salary_structure` | `variable_salary` / `salary_components` (unclear which) | Restructured | Per-employee instantiated components; `_MIGRATION_GUIDE.md` calls it `employee_salary_components`, but live code references both `variable_salary` and `salary_components` tables without a clean 1:1 match confirmable from controller code alone. Needs verification against the senior's live dev DB — this is exactly the kind of table the docs may be overclaiming on. |
| `emp_new_salary_structure` | *(folds into above)* | Restructured | Migration guide: historical snapshot table (`employee_salary_structure_history`) with identical structure to `emp_salary_structure`; no distinct live table confirmed. |
| `emp_ctc_transaction` | *(no directly confirmed equivalent)* | Not Found | Migration guide proposes `employee_ctc_history`; not found in any live query grep. CTC history tracking may not have been ported. |
| `ctc_fixed_amount`, `ctc_fixed_rate` | `emp_fixed_component_upload` | Restructured | Fixed-value CTC component master tables; only the upload-staging table name survives in live code. |
| `emp_calc_variable_components`, `emp_monthly_salary_components` | `variable_salary` | Restructured | Collapsed into one variable-pay table. |
| `emp_fixed_component_upload` | `emp_fixed_component_upload` | Same name | Confirmed live, table name unchanged. |
| `emp_salcomp_upload` | *(folds into upload flow)* | Restructured | |
| `payroll_master` | `payroll_master` | Same name | Table name literally unchanged in the new system — confirmed live. See detail below. |
| `payroll_master04092016` | — | Out of Scope | Dated one-off backup table; throwaway. |
| `emp_salary_slip` | `payroll_slip_lines` | Renamed | Migration guide calls it `payroll_entries`; live code uses `payroll_slip_lines` — another plan/shipped-name mismatch. |
| `emp_salary_slip_bkp` | — | Out of Scope | Backup table, no equivalent needed. |
| `emp_settle_slip` | `full_final_settlement` | Renamed | |
| `emp_pay_info` | *(no equivalent found)* | Not Found | |
| `emp_variables_upload`, `emp_variable_pay_upload` | `variable_salary` | Restructured | |
| `gross_salary` | *(no equivalent found — likely a computed value, not a table, in new system)* | Not Found | |
| `total_deductions` | *(no equivalent found — computed)* | Not Found | |
| `salary_hike`, `salary_hike_details` | `salary_increment` | Renamed/Restructured | Two legacy tables (header + detail) collapse to one. |
| `salary_processing_audit` | *(no equivalent found)* | Not Found | |
| `salary_heads` | `salary_heads` | Same name | Migration guide calls new table `salary_head_categories`; live code confirms the name `salary_heads` was actually kept unchanged — doc overclaims a rename that didn't happen. |
| `salary_head_items` | `salary_head_items` | Same name | Confirmed live, name unchanged (matches migration guide). |
| `emp_encash_slip` | `leave_encashment` (payout side) | Restructured | Folds into the encashment table alongside `leave_encashment_master`. |
| `emi_upload` | *(no equivalent found)* | Not Found | Legacy EMI-upload staging table for advances/loans; no trace in `loan_repayments`/`employee_loans` CRUD, which is manual entry only. |

#### Column-level detail — critical Payroll tables

**`salary_structure` → `salary_structures`**: `structure_created_date` int(11) Unix timestamp → `created_timestamp` timestamptz (explicit `TO_TIMESTAMP()` conversion required — an easy migration bug if missed); `prorate_code` varchar → same but documented as '1'=calendar days/'2'=working days/'3'=fixed; `structure_active` → `is_active` boolean.

**`salary_structure_details` → `salary_structure_details`**: `structure_det_operator` (`formula`/`fixed`/`limit`/`limit_wl`/`limit_wg`/`rembalance`) preserved as `operator`; **`structure_derived_perc`** (double) explicitly flagged by the migration guide as "critical: preserve this" because `calculate_emp_salary_breakup` uses it to compute `vformula_value = (derived_perc/100) * gross`; carried over unchanged as `derived_percentage`.

**`payroll_master` → `payroll_master`**: Table name unchanged. `month_year` varchar 'MM-YYYY' → `payroll_month` date (first-of-month); `days_presant` → `present_days`; `loss_of_pay` → `lop_days`; `approved` char → `is_approved` boolean; `action` (Processed/Approved/Hold) → `action_status`. Multiple legacy snapshot columns (`departments`, `desig`, `division`, `section`, `grade`, `category_name`, `bank_details`) captured at processing time — whether these snapshot columns survive in the live table could not be confirmed from controller grep alone (payroll.service.js reads current employee/structure state rather than snapshot columns, suggesting the new system may compute these live instead of snapshotting — a behavioral difference worth flagging even though it's not "recommend a fix," just an observed difference).

---

### 1.4 Leave Management Module

| Legacy Table | Senior App Equivalent | Status | Notes |
|---|---|---|---|
| `leavepolicy` | `leave_policies` | Renamed | See detail below. |
| `leavepolicy_group` | `leave_policy_groups` | Renamed | |
| `leaveentries` | `leave_requests` (+ `leave_request_days`) | Renamed/Restructured | Legacy stored one row per leave application spanning a date range; new system adds a `leave_request_days` child table (day-by-day breakdown, confirmed via `generateDayRows()` in `leave.controller.js`) — an improvement over the legacy's implicit from/to-date range with half-day flags only at the endpoints. |
| `emp_leave_balance_year` | `employee_leave_balance` | Renamed | See detail below. |
| `leavestatus` | *(folds into `employee_leave_balance` adjustment flow)* | Restructured | Migration guide calls this `leave_balance_adjustments`; no distinct table confirmed live — balance uploads appear to write directly to `employee_leave_balance`. |
| `leave_balance_upload` | *(no distinct staging table confirmed)* | Restructured | Same reasoning as above. |
| `leave_encashment_master` | `leave_encashment` | Renamed | |
| `leave_taken` | *(computed, not a table, in new system)* | Not Found | Legacy persisted a leave-taken tracking table; `leave_taken_fn` in Part 2 is explicitly noted by the senior's own docs as "can be an inline query" — consistent with no live table found. |
| `emp_leave_approval` | Folded into `leave_requests.status` + `authorized_by`/`approved_by` columns | Restructured | Multi-level approval state now lives as columns on the request row itself (confirmed: `authorizeLeave`, `approveLeave` functions update `leave_requests` directly) rather than a separate approval-chain table. |
| `emp_leave_info` | *(no equivalent found)* | Not Found | |
| `emp_leave_transactions` | *(superseded — see `leave_fn` in Part 2, itself a dead 2016 migration artifact)* | Not Found | Legacy-legacy: even the old system's own docs call this a defunct predecessor schema. |
| `emp_leave_upload` | *(no distinct staging table confirmed)* | Not Found | |

#### Column-level detail — critical Leave tables

**`leavepolicy` → `leave_policies`**: Large table — `CARRY_FORWARD_LIMIT` → `carry_forward_limit`; `ALLOW_NEGETIVE` (typo) → `allow_negative_balance` boolean; `IS_SANDWICH` → `is_sandwich_rule` boolean; `is_leave_encash`/`leave_encash_limit` → `is_encashable`/`encash_limit_days`; `leval_of_approval` (typo) → `approval_levels`; multiple rule flags (`minimum_leave`, `maximum_leave`, `min_service_months`, `document_mandatory`, `min_day_before_apply`) all preserved 1:1 by name-fix only. Confirmed live: `getLeaveTypes()` in `leave.controller.js` selects `allow_negative`, `carry_forward_limit`, `leval_of_approval` directly — column names match the migration guide's plan closely.

**`emp_leave_balance_year` → `employee_leave_balance`**: Legacy modeled balance as `alloted_forthe_year`, `leave_taken_forthe_year`, `balance_forthe_year`, `carry_forwarded`, `end_process_adjust` (five separate running totals). Live code (`getLeaveBalance()` in `leave.controller.js`) shows a simplified three-column model: `opening_balance`, `credited`, `taken`, with balance computed on read as `opening_balance + credited - taken`. This is a real simplification, not just a rename — the legacy's separate `carry_forwarded` and `end_process_adjust` columns collapse into `opening_balance` at year-end (confirmed in the `runYearEnd()` handler, see Part 2 note on `leave_end_process_fn`).

---

### 1.5 Tax & Statutory Module

| Legacy Table | Senior App Equivalent | Status | Notes |
|---|---|---|---|
| `emp_tax_regime` | `emp_tax_regime` | Same name | Confirmed live, table name unchanged. |
| `emp_tax_sal_trans` | `emp_tax_computation` (likely, unconfirmed exact split) | Restructured | Migration guide splits old/new regime into `employee_tax_salary_transactions`; live code shows a single `emp_tax_computation` table referenced — suggests old/new regime were unified into one table rather than kept parallel, a simplification versus both the legacy schema and the migration guide's own plan. |
| `emp_tax_sal_trans_new` | `emp_tax_computation` (unified) | Restructured | Same reasoning — the legacy old-regime/new-regime table pair (`emp_tax_sal_trans`/`emp_tax_sal_trans_new`) appears consolidated. |
| `emp_tax_sal_trans_sum` | *(folds into `emp_tax_computation`)* | Restructured | |
| `emp_tax_sal_trans_sum_new` | *(folds into `emp_tax_computation`)* | Restructured | |
| `tax_computation_report` | *(folds into `emp_tax_computation`, or generated as a report — unconfirmed which)* | Restructured | |
| `emp_statutory_components` | *(no directly confirmed table — reports read live from `payroll_slip_lines`)* | Not Found | See Part 2: `statutory.controller.js`'s EPF/ESI functions query payroll slip lines for deduction amounts by keyword match (`findAmt(lines, keywords)`) rather than a dedicated statutory-components table — a materially different architecture, not just a rename. |
| `emp_pt_details` | *(folds into PT report query, no dedicated table confirmed)* | Not Found | `ptReport()` in `statutory.controller.js` computes PT for the report on the fly. |
| `taxes_save` | *(no equivalent found)* | Not Found | |
| `tax_form_documents` | *(no equivalent found)* | Not Found | Form 16/12BA style document generation table — not found in New Rizo code. |
| `tax_heads` | `tax_heads` | Same name | Confirmed live. |
| `tax_heads_details` | `tax_head_details` | Renamed (minor) | |
| `tax_salary_components` | `tax_types` (approximate) | Renamed/Restructured | |
| `tax_type` | `tax_types` | Renamed | |
| `income_tax_slab` | `income_tax_slab` | Same name | Confirmed live, unchanged. |
| `professional_tax_view` | *(view, not migrated as data — likely reimplemented as a query)* | Not Found | Legacy DB view; new system's `ptReport()` is a live query, consistent with no persisted view needed. |
| `profession_tax_slab` | *(sourced from `present_employee` per migration guide's own note — misleadingly named legacy table)* | Restructured | Migration guide calls new table `professional_tax_slabs`; not independently confirmed live but plausible given PT report exists. |

**Important cross-check finding:** The statutory/tax module is the clearest example of the docs' "reimplemented" claims needing scrutiny. `statutory.controller.js` functions (`epfReport`, `esiReport`, `ptReport`, `tdsReport`) are explicitly labeled in their own code comments as **reports**, computing PF/ESI/PT amounts by scanning `payroll_slip_lines` for matching component names (`findAmt(lines, keywords)`) rather than reading from a dedicated, independently-computed statutory table populated during payroll processing (as legacy's `calculate_statutory_components_prc` did, writing to `emp_statutory_components`). This means PF/ESI/PT figures in the new system are only as correct as whatever the payroll run happened to label those slip lines — there is no independent statutory calculation engine enforcing PF/ESI ceiling rules, formula/fixed modes, etc. See Part 2 for the full procedure-level assessment.

---

### 1.6 Salary Structure / Setup Masters

| Legacy Table | Senior App Equivalent | Status | Notes |
|---|---|---|---|
| `branches` | `branches` | Same name | Confirmed live, unchanged. `deleted` int → `is_deleted` boolean per migration guide. |
| `department` | `departments` | Renamed | |
| `designation` | `designations` | Renamed | |
| `grade` | `grades` | Renamed | |
| `section`, `division` | *(no equivalent found)* | Not Found | Migration guide says "same pattern as department"; no `sections`/`divisions` table reference found live in controller grep — appears not built yet. |
| `category_master` | `categories` | Renamed | |
| `fin_year` | `fin_year` | Same name | Confirmed live, unchanged. |
| `working_day_time_procedures` | `shifts` | Renamed | Covered in §1.2 detail. |
| `holiday_group`, `holidays` | `holiday_groups` (+ implied `holidays`) | Renamed | Covered in §1.2. |
| `Area` | *(no equivalent found)* | Not Found | Legacy 3-column area lookup; not referenced. |
| `bank`, `tblbankname`, `tblbankname1` | *(no equivalent found — likely a static list or dropped)* | Not Found | Three redundant legacy bank-name lookup tables; none confirmed live. |
| `compliance` | *(no equivalent found)* | Not Found | |
| `db_config` | `company_settings` (likely, unconfirmed exact overlap) | Restructured | Legacy per-company runtime config (`attendance_date`, `attendance_format`, punch-coupon config, etc. — used heavily by attendance procedures in Part 2); `company_settings` is the closest live candidate but exact column overlap unconfirmed. |
| `Email_Content` | *(no equivalent found)* | Not Found | Email template table; nothing found. |
| `package_master`, `payment_mode` | *(no equivalent found)* | Not Found | |
| `site`, `site_master_approval`, `site_master_approval_details` | *(no equivalent found)* | Not Found | Part of the unported site/contract-labour module (see §1.2). |
| `uomaster` | — | Out of Scope | Unit-of-measure master — inventory module concept. |
| `verticals` | *(no equivalent found)* | Not Found | |
| `countries_nationality` (control DB copy) | *(no equivalent confirmed)* | Not Found | Nationality/country master; not grepped live but plausible it's a static seed list rather than a queried table. |
| `access_site`, `access_store` | *(no equivalent found)* | Not Found | Site/store-level access-control tables tied to the unported site module. |
| `cus_visit_locations` | *(no equivalent found)* | Not Found | CRM field-visit location master. |

---

### 1.7 Asset Management Module

| Legacy Table | Senior App Equivalent | Status | Notes |
|---|---|---|---|
| `asset_allocate` | `asset_allocate` | Same name | Confirmed live in `asset.controller.js` — table name unchanged. |
| `asset_management` | `asset_management` | Same name | Confirmed live, unchanged. |
| `asset_types` | `asset_types` | Same name | Confirmed live, unchanged. |
| `waranty_details` (typo) | *(folds into `asset_management`, unconfirmed)* | Restructured | |
| `allocate_details` | `asset_allocate` (likely folded in) | Restructured | |

Note: unlike inventory/procurement (§1.8, out of scope per the migration guide), the asset-tracking sub-module was actually carried forward into New Rizo with table names kept identical — this is a case where the migration guide's own "PRESERVE if needed, else deprioritize" guidance (from `2_DATABASE_SCHEMA_MAPPING.md`) resolved in favor of keeping it, unlike the broader inventory tables below which were dropped entirely.

---

### 1.8 Inventory / Procurement / Stores Module

**All tables in this section are explicitly Out of Scope per `_MIGRATION_GUIDE.md` §1:** *"Inventory/procurement tables (`item_master`, `stock_details`, `purchase_order`, `grn_*`, `stock_*`, `mr_*`, `po_*`, `allocate_*`) — not part of the HRMS core, not used in new system."* Confirmed: zero references to any of these table names found anywhere in `New Rizo/backend/src`.

| Legacy Table | Senior App Equivalent | Status | Notes |
|---|---|---|---|
| `item_master`, `item_details`, `item_pricing`, `item_purchase`, `item_specification`, `Item_allocation_details_view`, `item_except_grn_allocation_view` | — | Out of Scope | |
| `itm_allocation`, `group_allocation` | — | Out of Scope | |
| `stock_details`, `stock_details_view`, `stock_adjustments`, `stock_adjustments_details` | — | Out of Scope | |
| `stock_store_tranfer`, `stock_store_tranfer_item`, `stock_tranfer`, `stock_tranfer_item` | — | Out of Scope | |
| `store_master` | — | Out of Scope | |
| `purchase_order`, `purchase_list`, `po_item_details`, `po_return_request` | — | Out of Scope | |
| `direct_purchase_order`, `direct_po_details` | — | Out of Scope | |
| `material_request`, `mr_details` | — | Out of Scope | |
| `goods_receved_notes`, `gr_item_details`, `grn_stock_details_date_view`, `grn_stock_det_rate_date_view` | — | Out of Scope | |
| `returned_stocks`, `return_gr_items` | — | Out of Scope | |
| `quantity_details` | — | Out of Scope | |

---

### 1.9 Expense Module

| Legacy Table | Senior App Equivalent | Status | Notes |
|---|---|---|---|
| `emp_expense` | `emp_expenses` | Renamed | See §1.1 (listed once; cross-referenced here for module completeness). |
| `expense_type` | `expense_types`, `expense_items` | Renamed/Restructured | |

---

### 1.10 Control-DB / Auth / Platform Module

| Legacy Table | Senior App Equivalent | Status | Notes |
|---|---|---|---|
| `central_control` | `company` | Restructured | Legacy's tenant registry (physical-DB-per-tenant model: `user_db`, `user_pwd`, `subdomain`, `product`, `plan`) collapses into the single `company` row model since multi-tenancy is now `company_id`-column-based, not physical-DB-based. `company` is the only table with a checked-in `CREATE TABLE` in `db/init.sql`. |
| `central_control_bck29092025`, `control_back09092023` | — | Out of Scope | Dated backup tables. |
| `company_addons` | `plan_features` (approximate) | Restructured | |
| `company_branches`, `company_branches_bck29092025` | `branches` | Restructured | Control-DB cross-tenant branch registry folds into the single tenant-scoped `branches` table now that there's no cross-DB sync need. |
| `company_overrides` | `company_settings` | Restructured | |
| `countries`, `countries_only`, `countries_nationality` | *(no equivalent confirmed live)* | Not Found | Country/nationality masters; not grepped in controllers — likely a static seed list rather than a DB-backed lookup in the new system. |
| `cron_dbs`, `cron_log` | *(no equivalent found — job scheduling likely handled by an external scheduler, not DB tables)* | Not Found | |
| `customervisit`, `customer_visit`, `customer_visit_09032026` | — | Out of Scope | CRM field-visit tables; three redundant/dated copies, none referenced live. |
| `department`, `designation`, `holidays`, `holiday_group`, `income_tax_slab`, `leavepolicy`, `leavepolicy_group`, `doc_template`, `salary_heads`, `salary_head_items`, `salary_structure`, `salary_structure_details`, `working_day_time_procedures` | *(control-DB copies of tenant master data — same mapping as §1.1–1.6)* | Renamed/Restructured | Legacy duplicated these master tables into both the control DB (as templates for new tenant provisioning) and each tenant DB. The new single-database, `company_id`-scoped model has no need for a template copy — one live table per concept, scoped by `company_id`. |
| `devicelogs`, `devices` | `adms_device_employees`, `adms_devices` | Renamed | Confirmed live. "ADMS" naming reflects the specific biometric device protocol (Attendance Data Management System / ZKTeco push protocol) the new system standardized on. |
| `device_logs_error`, `device_logs_error_09032026` | *(no equivalent confirmed)* | Not Found | |
| `emp_device_comp_branch`, `emp_device_comp_branch_bak`, `emp_device_comp_branch_bak1` | `adms_device_employees` | Renamed/Restructured | Device-to-employee mapping folds into the ADMS employee-mapping table. |
| `emp_id_maping`, `emp_id_maping_exist` | *(folds into `adms_device_employees` enrollment flow)* | Restructured | |
| `features`, `plans`, `plan_features`, `plan_history`, `plan_payment_history` | *(no equivalent confirmed live)* | Not Found | SaaS billing/plan tables; not referenced in any grepped controller — billing/plan management may not be built yet in New Rizo, or lives in a separate service not in this repo. |
| `get_location` | *(no equivalent found)* | Not Found | |
| `location`, `location_09032026` | *(no equivalent found)* | Not Found | |
| `login_auditor` | *(no equivalent confirmed — auth uses JWT, no visible login audit table)* | Not Found | |
| `log_issue_cntrldb`, `master_auidt` (typo) | *(no equivalent found)* | Not Found | |
| `master_db` | — | Out of Scope | Legacy per-tenant DB registry — meaningless in single-DB architecture. |
| `mob_user_msg_control`, `mob_version_control` | — | Out of Scope | Mobile app config tables. |
| `mypayroll_bugs_logs`, `report_a_problem1` | *(no equivalent found)* | Not Found | Support/bug-report tables. |
| `myprojects_control`, `mysales_control`, `projects_online` | *(no equivalent found)* | Not Found | Cross-sell/other-product tables unrelated to HRMS core. |
| `oauth2_authorization`, `oauth2_registered_client` | *(no equivalent — different auth mechanism entirely)* | Not Found | New system uses JWT-based auth (`jsonwebtoken` in `auth.controller.js`), not OAuth2 authorization-server tables. Not a gap so much as a deliberate architecture change — flagged as a difference, not a missing feature. |
| `payroll_online`, `payroll_signup`, `payroll_signup_bck29092025`, `ppl_online` | *(no equivalent confirmed)* | Not Found | Public-website signup/lead tables — outside the HRMS app repo boundary as scoped here. |
| `procedure_run_log` | *(no equivalent found)* | Not Found | |
| `registrations` | *(no equivalent confirmed)* | Not Found | |
| `sso_audit` | *(no equivalent — no SSO mechanism found in New Rizo)* | Not Found | Consistent with the OAuth2/SSO tables above being absent — single sign-on to third-party systems does not appear to have been ported at all. |
| `super_admin_access` | *(folds into RBAC — `user_menu_access`/`user_company_access`, unconfirmed exact overlap)* | Restructured | |
| `test`, `test_device_attandance` | — | Out of Scope | Throwaway/utility tables. |
| `users` | `user_credentials`(likely consolidated) | Restructured | Control DB had a separate `users` table alongside `user_credentials`; live New Rizo shows only `user_credentials` referenced. |
| `user_credentials`, `user_credentials_bck29092025` | `user_credentials` | Same name | Confirmed live, unchanged (minus the dated backup copy, which is out of scope). |

---

## PART 2 — PROCEDURE / FUNCTION / TRIGGER COMPARISON

Fate categories used: **Reimplemented in JS** (working code found and cited), **Reimplemented as report only** (exists but not wired into live processing), **Not found in code** (no trace beyond the planning doc), **Dropped/obsolete by design** (doesn't apply to the new architecture).

### 2.1 Tenant DB — Procedures & Functions (91 objects)

| Legacy Object | Type | Domain | Fate | Note |
|---|---|---|---|---|
| `calculate_emp_salary_breakup` | Procedure | Salary & CTC | Reimplemented in JS | `payroll.service.js::computeSalaryBreakup()` (line 14). Confirmed live: handles prorate, LEAST/GREATEST for limit/limit_wl/limit_wg. Fidelity: formula/fixed/limit operator coverage confirmed; unverified against legacy numeric output on real data. |
| `sal_structure_distribution_fn` | Function | Salary & CTC | Not found in code | No `applyStructureToEmployee`-style service found; `salaryStructure.controller.js` provides CRUD on structures/details but no distribute-to-employee formula-resolution step was located. |
| `sal_structure_distribution` (bulk wrapper) | Procedure | Salary & CTC | Not found in code | Depends on the function above; same gap. |
| `salary_structure_limit_prc` | Procedure | Salary & CTC | Reimplemented in JS (partial) | `computeSalaryBreakup()` applies LEAST/GREATEST inline during the main calc rather than as a separate post-pass, consistent with the doc's own recommendation to fold this in — but confirms it wasn't kept as a distinct step, so the two-phase legacy behavior (percentage-based first pass, then value-based cap/floor correction) is collapsed into one pass. Risk: legacy's `limit_wl`/`limit_wg` depend on the *computed* salary amount from a prior pass; a single-pass implementation needs to guarantee the same ordering/dependency resolution. |
| `site_sal_structure_distribution_fn` | Function | Site / Salary | Dropped/obsolete by design | Site/contract-labour module not ported at all (see §1.2/§1.6) — no site salary distribution needed. |
| `structure_calc` | Procedure | Salary & CTC | Dropped/obsolete by design | Legacy's own docs call this a debug/test utility, not production logic. |
| `structure_preview_prc` | Procedure | Salary & CTC | Not found in code | No `/salary-structures/:id/preview`-style endpoint found in `salaryStructure.controller.js`. |
| `calculate_salary_main_prc` | Procedure | Payroll Processing | Reimplemented in JS | `payroll.service.js::processEmployee()` (line 96) is the orchestrator equivalent. Confirmed to call `computeSalaryBreakup`; confirmed to NOT include holiday allowance, shift allowance, OT allowance, leave encashment, advance/loan EMI deduction sub-steps (none of these terms appear in `payroll.service.js` — see the specific sub-procedure rows below). |
| `salary_process_prc` | Procedure | Payroll Processing | Dropped/obsolete by design | Legacy itself notes this and `calculate_salary_main_prc` should be unified; new system has one payroll path (`processEmployee`), consistent with that recommendation. |
| `tax_salary_process_prc` | Procedure | Payroll/Tax | Not found in code | limit_wl/limit_wg TDS post-pass; no distinct equivalent found — see note on `salary_structure_limit_prc` above regarding single-pass consolidation. |
| `payroll_master_insert` | Procedure | Payroll Processing | Reimplemented in JS | `payroll.controller.js` presumably provides the payroll-run creation endpoint (not individually grepped line-by-line, but `payroll_master` table is actively read/written per §1.3). Policy-based day counting (`payroll_type='M'` branch in legacy) not confirmed ported — flag for verification. |
| `calculate_monthly_salary_components_prc` | Procedure | Payroll Processing | Reimplemented in JS | Folded into `computeSalaryBreakup()`/`processEmployee()`; the legacy's separate daily-wage vs. hourly-wage vs. percent-of-gross branching was not independently confirmed as preserved logic (only prorate-by-days confirmed via `prorate()` function). |
| `calculate_holiday_allowances_prc` | Procedure | Payroll Processing | Not found in code | No `holiday_allowance` term anywhere in `payroll.service.js`. Consistent with prior research flag — this logic is genuinely missing from the payroll pipeline, not just renamed. |
| `calculate_shift_allowance_prc` | Procedure | Payroll Processing | Not found in code | No `shift_allowance` computation in `payroll.service.js`. `shift.controller.js` and `attendance.controller.js` reference "allowance"/"overtime" terms but in a shift-definition/config context, not as a payroll gross-up step — not confirmed wired into actual salary calculation. |
| `calculate_ot_allowance_prc` | Procedure | Payroll Processing | Not found in code | No `ot_allowance`/`overtime` computation in `payroll.service.js`; `monthly_ot_override` table exists (§1.2) but nothing in the payroll service reads it to add an OT earnings line. |
| `calculate_leave_encashment_prc` | Procedure | Payroll Processing / Leave | Not found in code | No leave-encashment amount computation inside `payroll.service.js`; `leave_encashment` table exists (§1.4) as a request/approval table, but its payout-into-payslip step (mirroring legacy's `leave_encash_prc`) was not located. |
| `get_policy_weekoff_holiday_count_prc` | Procedure | Payroll/Attendance | Not found in code | Policy-based weekoff/holiday day counting for `payroll_type='M'`; no equivalent found. |
| `calculate_statutory_components_prc` | Procedure | Tax & Statutory | Reimplemented as report only | See §1.5 finding. `statutory.controller.js`'s `epfReport`/`esiReport` derive PF/ESI from existing payslip line labels rather than independently computing PF/ESI (percentage/fixed mode resolution, formula substitution, ESI ceiling enforcement) and writing them into the payslip. The six-block legacy logic (formula substitution, self-reference resolution, dynamic SQL, ESI ceiling, limit processing) has no JS equivalent that *feeds* payroll — only one that *reads* payroll output for reporting. |
| `profession_tax_cal_fn` | Function | Tax & Statutory | Reimplemented as report only | `ptReport()` in `statutory.controller.js` computes PT for the report; state-specific slab lookup and the Telangana special-case (current-month-only vs. projected-annual) not confirmed preserved — needs verification against `profession_tax_slab`/state logic in the report function. |
| `tax_computation_fn` | Function | Tax & Statutory | Reimplemented in JS | `tax.service.js::computeTaxForEmployee()` (line 63). |
| `tax_salary_distribution_fn` (old regime) | Function | Tax & Statutory | Reimplemented in JS | `tax.service.js::computeRegime()` (line 36) + `applySlabs()` (line 4). Old-regime 3-slab, HRA exemption, and Section 80C-style declared-deduction handling not individually line-verified beyond confirming the function exists and takes `investmentDeduction` as a parameter. |
| `tax_salary_distribution_new_fn` (new regime) | Function | Tax & Statutory | Reimplemented in JS | Same `computeRegime()`/`applySlabs()` handles both regimes via a `regimeName` parameter — confirms unification (see §1.5 table-level note that old/new regime tables were also merged). 7-slab structure and marginal relief not individually verified. |
| `vangrd_pfesi_update_fn` | Function | Tax & Statutory | Dropped/obsolete by design | Legacy's own docs recommend eliminating this bridge function by writing PF/ESI directly during statutory calc; consistent with no separate bridge function needed — though this is somewhat moot since the statutory calc itself is report-only (see `calculate_statutory_components_prc` above), not writing into payroll at all. |
| `leave_end_process_fn` | Function | Leave Management | Reimplemented in JS | `hrModules.controller.js::runYearEnd()` (line 413) — **correction to prior research assumption that this was missing.** It exists as a manually-triggered endpoint (not a nightly cron), and implements a simplified single carry-forward-limit model rather than legacy's per-policy-type cycle rollover (Y=yearly/M=monthly/Q=quarterly/H=half-yearly/P=project/D=daily, each with different rollover math via `att_start_end_fn`). Confirmed live: caps carry-forward at `carry_forward_limit`, resets `opening_balance`/`credited`/`taken`. |
| `leave_encash_insert_prc` | Procedure | Leave Management | Not found in code | Automatic encashment-request creation from balance; `leave_encashment` table (§1.4) supports request/approval CRUD but the auto-creation-from-policy-eligibility step was not located. |
| `leave_encash_prc` | Procedure | Leave Management / Payroll | Not found in code | Payout materialization into payslip — see `calculate_leave_encashment_prc` above, same gap. |
| `leave_transaction_prc` | Procedure | Leave Management | Reimplemented in JS | `leave.controller.js::applyLeave()` (line 173). Balance-sufficiency check and day-count logic (`calcLeaveDays()`, line 6) confirmed; weekoff/holiday-aware day counting within the leave period and the full `leave_rules_fn` validation suite (min/max, notice period, prefix/suffix rules) not confirmed present in the grepped excerpt. |
| `leave_taken_fn` | Function | Leave Management | Dropped/obsolete by design | Legacy's own docs call this "can be an inline query" — consistent with balance now being computed via a simple SQL expression (`getLeaveBalance()`) rather than a dedicated function. |
| `leave_balance_inthe_year_fn` | Function | Leave Management | Reimplemented in JS | `getLeaveBalance()` in `leave.controller.js` (line 78) — simplified to `opening_balance + credited - taken` rather than legacy's `allotted + carry_forward + manual_adjustments - taken` with mid-cycle-joining proration; proration logic not confirmed present. |
| `leave_rules_fn` | Function | Leave Management | Not found in code | No dedicated rule-validation function grepped (min/max days, notice period, prefix/suffix-holiday rules, half-day restrictions); `applyLeave()` computes days and balance but a structured rules engine equivalent wasn't located. |
| `leave_start_end_prc` | Procedure | Leave Management | Dropped/obsolete by design | Legacy's own docs call this "replace with a simple query" — trivial lookup, not distinct logic. |
| `leave_auth_apr_person_fnmbct` | Function | Leave Management | Reimplemented in JS (partial) | `authorizeLeave()`/`approveLeave()` (lines 228, 249) cover the approve/reject state transitions and balance deduction; the *hierarchy-based* multi-level approval-chain resolution (who can approve, based on `emp_proff.attr1`) was not confirmed — approvals appear to take an explicit `approved_by` from the request rather than resolving it from an org hierarchy. |
| `Leave_balance_upload_fn` | Function | Leave Management | Not found in code | Manual balance-correction upload with auto-computed adjustment delta; no equivalent found (consistent with §1.4 finding that `leave_balance_upload`/`leavestatus` have no distinct live table either). |
| `year_ending_fn` | Function | Leave/Payroll | Reimplemented in JS (partial) | Folded into `runYearEnd()` — but that function only handles leave balance carry-forward; the financial-year `is_current`/`is_current_finyear` cutover for payroll purposes was not confirmed in the grepped excerpt. |
| `year_ending_mission_fn` | Function | Leave/Tax | Not found in code | Tax regime carry-forward from closing year to new year; not confirmed present in `runYearEnd()` or elsewhere. |
| `leave_fn` (legacy) | Function | Leave Management | Dropped/obsolete by design | Legacy's own docs mark this a dead 2016 one-time migration script; correctly not reimplemented. |
| `att_start_end_fn` | Function | Attendance Processing | Not found in code | Attendance-period start/end date resolution based on a configurable `attendance_date` offset; no equivalent found — if the new system always uses calendar-month boundaries, companies using a non-1st-of-month attendance cycle would be affected, though this is a factual gap observation, not a recommendation. |
| `att_start_end_date_fn` | Function | Attendance Processing | Not found in code | Companion/variant of the above. |
| `insert_emp_att_reg` | Procedure | Attendance Processing | Reimplemented in JS (partial) | Given the `attendance` table (§1.2) is confirmed live and populated per-day (not FIELD1-31), *some* equivalent register-generation logic must exist, but no single grepped function was identified as the direct counterpart. The leave/holiday/weekoff/present priority-ordering rule that legacy enforces explicitly was not confirmed preserved. |
| `insert_update_att_reg` | Procedure | Attendance Processing | Not found in code | Update/draft variant; not distinctly located. |
| `insert_update_att_reg_hierarchy` | Procedure | Attendance Processing | Not found in code | Hierarchy-scoped variant; no reporting-hierarchy table found at all (§1.1 `emp_structure` — Not Found), so this can't have a faithful equivalent. |
| `insert_update_att_reg_rep` | Procedure | Attendance Processing | Dropped/obsolete by design | Staging/draft-table variant; legacy's own docs recommend a status flag instead of a separate table — consistent with no separate `attendance_register_rep`-equivalent found (§1.2). |
| `insert_update_att_reg_view` | Procedure | Attendance Processing | Not found in code | Single-employee refresh variant; not distinctly located. |
| `process_att_reg_fn` | Function | Attendance Processing | Not found in code | Finalize/lock-register step (draft→verified); no distinct "approve attendance" endpoint located in `attendance.controller.js` grep. |
| `weekoff_days_count_fn` | Function | Attendance Processing | Not found in code | Weekoff-day counting with half-weekoff and alternating-weekoff support; not located. |
| `time_duration_check` | Procedure | Biometric / Device Sync | Not found in code | Core single-shift punch-to-duration processing (shift window clamping, strict-monitoring, include-break, half-day thresholds); given `attendance`/`device_punch_logs` tables are populated (§1.2), *some* punch-processing logic must exist server-side or client-side for the ADMS integration, but it was not located among the grepped controller function names. |
| `time_duration_check_date` | Procedure | Biometric / Device Sync | Not found in code | Single-date variant of the above. |
| `time_duration_check_multishift` | Procedure | Biometric / Device Sync | Not found in code | Multi-shift/rotating-shift punch processing; `emp_shift_planner` has no confirmed equivalent (§1.2), so faithful multi-shift processing is unlikely to be present. |
| `time_duration_check_hierarchy` | Procedure | Biometric / Device Sync | Dropped/obsolete by design | Hierarchy access-check variant; legacy's own docs recommend applying this as a middleware permission layer rather than in the computation — consistent with no reporting hierarchy existing at all in New Rizo currently. |
| `time_coupon_auto_fn` | Function | Biometric / Device Sync | Not found in code | Time-coupon/comp-off credit conversion; `compoff_requests` table exists live (§1.1 grep list) suggesting comp-off is handled as an explicit request workflow now rather than an automatic coupon credit — a plausible architecture change, not confirmed as functionally equivalent. |
| `last_punch_fn` | Function | Biometric / Device Sync | Dropped/obsolete by design | Legacy's own docs call this "replace with a simple query" — trivial. |
| `Linkemp_deviceanddatabase` | Procedure | Biometric / Device Sync | Reimplemented in JS (likely) | `adms_device_employees`/`adms_devices` tables exist and are populated (§1.10); device enrollment logic presumably lives in `adms.controller.js` (not individually grepped for this specific function, but the tables and controller both exist, consistent with the enrollment flow being present). |
| `mark_site_attendance_fn` | Function | Site / Biometric | Dropped/obsolete by design | Site attendance module not ported (§1.2). |
| `mark_site_attendance_out_fn` | Function | Site / Biometric | Dropped/obsolete by design | Same. |
| `update_breakoff` | Procedure | Biometric / Attendance | Reimplemented in JS (likely) | `scheduled_break_off` table confirmed live (§1.2) with the same name kept — some break-off management logic presumably exists in a controller, though the specific injection-into-punch-pipeline mechanic (legacy's approach) was not confirmed as preserved vs. a simpler direct-status-update approach. |
| `ot_duration_register` | Procedure | OT & Shift | Not found in code | Monthly OT computation across a branch; `monthly_ot_override` table exists (§1.2) but appears to be an *override/adjustment* table by name, not a computed-OT-storage table — suggesting OT may now be manually entered/overridden rather than auto-computed from punches. |
| `ot_duration_register_date` | Procedure | OT & Shift | Not found in code | Single-date variant; same gap. |
| `site_insert_update_att_reg` | Procedure | Site / Contract Labour | Dropped/obsolete by design | Site module not ported. |
| `site_time_duration_check` | Procedure | Site / Contract Labour | Dropped/obsolete by design | Same. |
| `site_rate_update_prc` | Procedure | Site / Salary | Dropped/obsolete by design | Same. |
| `final_settle_pay_prc` | Procedure | Full & Final Settlement | Reimplemented in JS (partial) | `fullFinal.controller.js` confirmed live, reads `employee_advance` (line 116) and `employee_loans` (line 133) to net off outstanding balances at settlement — this part is faithfully covered. Gratuity formula (`(Basic+DA)/26 × 15 × years_of_service`, 5-year eligibility), bonus lookup from current/previous FY, and PT-balance-for-remaining-months were not confirmed present in the grepped excerpt — needs deeper verification before assuming full parity. |
| `payroll_master_approve` | Procedure | Payroll Processing | Dropped/obsolete by design | Legacy's own docs call this a "simple status update" — trivial, presumably folded into a generic payroll-status-update endpoint in `payroll.controller.js` (plausible, not individually confirmed). |
| `master_auidt_fn` | Function | Utility & Audit | Not found in code | Generic audit-log writer; no `audit_log`-style table or middleware confirmed anywhere in New Rizo grep results (consistent with §1.1 `activity`/`history` both being Not Found). |
| `user_access_firstime_only` | Procedure | Utility & Audit | Dropped/obsolete by design | Superseded by RBAC tables (`user_branch_access`, `user_company_access`, `user_menu_access`, §1.1/1.10) — a default-role-assignment mechanism presumably exists but wasn't individually located; conceptually superseded either way. |
| `insert_default_menu` | Procedure | Utility & Audit | Dropped/obsolete by design | Same reasoning — RBAC-based menu access replaces hardcoded default-menu insertion. |
| `stock_bal_qty_fn` | Function | Inventory (non-HRMS) | Dropped/obsolete by design | Inventory module confirmed out of scope (§1.8); note the *asset* module (§1.7) was kept but the *inventory/stock* module was not — this function belongs to the latter. |
| `stock_tranfer_item_pkey_update`, `stock_tranfer_pkey_prc`, `stock_trans_prc` | Procedures | Inventory (non-HRMS) | Dropped/obsolete by design | Same. |
| `emp_detail_att_reg` | Procedure | Attendance | Not found in code | Single-employee-scoped register generation; opening logic mirrors `insert_emp_att_reg`'s day-by-day approach at reduced scope — no equivalent located. |
| `emp_inactive_fn` | Function | Leave/Utility | Not found in code | Returns a tinyint flag based on leave-balance-related counts for the financial year; purpose narrow enough that no equivalent was expected to be individually located, and none was. |
| `find_pf_tax_cal_fn` | Function | Tax | Not found in code | Computes total months for PF-tax purposes based on joining date and financial year; not located — consistent with the broader statutory-calc gap noted under `calculate_statutory_components_prc`. |
| `get_present_in_weekoff_holiday_count_prc` | Procedure | Attendance/Payroll | Not found in code | Variant of `get_policy_weekoff_holiday_count_prc`; same gap. |
| `leave_auth_apr_person_fn` (non-mbct base version) | Function | Leave Management | Reimplemented in JS (partial) | Same fate as the `fnmbct` client-specific variant above — `authorizeLeave()`/`approveLeave()` cover the base state-transition case. |
| `leave_balance_inthe_month_fn` | Function | Leave Management | Reimplemented in JS (partial) | Monthly-scoped variant of `leave_balance_inthe_year_fn`; `getLeaveBalance()` is year-scoped (filtered by `year` param) — a month-level balance snapshot function specifically was not confirmed present. |
| `presant_leave_holiday_count` | Function | Attendance/Leave | Not found in code | Counts present/leave/holiday days from `attendance_register.field1`; specific to the FIELD1-31 model which no longer exists — logically must be replaced by a `COUNT()...GROUP BY status` query on the new `attendance` table, but no such query was individually located. |
| `carryforward_insert_prc` | Procedure | Leave Management | Reimplemented in JS (partial) | Legacy's dedicated carry-forward-row-insertion procedure; folded into `runYearEnd()`'s single balance-update statement (§ above) rather than kept as a discrete step — functionally similar outcome, structurally different (update-in-place vs. insert-new-cycle-row). |
| `check_fn` | Function | Attendance/Utility | Not found in code | Ambiguous general-purpose check function (attendance-adjacent per its parameters: company/branch/user/month); no equivalent identified. |
| `copy_salary_structure_to_new` | Procedure | Salary & CTC | Not found in code | Copies `emp_new_salary_structure` snapshot rows; given `emp_new_salary_structure`'s equivalent itself is unconfirmed (§1.3), this copy step has no basis to exist either. |
| `ctc_component_update_and_upload_prc`, `ctc_component_upload_prc` | Procedures | Salary & CTC | Not found in code | CTC component-level upload/update procedures (large bodies, holiday/shift-adjacent variable declarations suggest they also touch attendance-derived proration); no equivalent located — consistent with `emp_ctc_transaction`/CTC history being Not Found at the table level (§1.3). |
| `bulk_company_idupdate` | Procedure | Utility | Not found in code | Bulk employee/device ID remediation utility; no equivalent expected or found — this is a one-off data-fix procedure, not standing business logic. |
| `bulk_device_logs_iteration_prc` | Procedure | Biometric / Device Sync | Not found in code | Bulk re-processing of device logs per employee; no equivalent located. |
| `delete_an_count_records_fn` | Function | Attendance | Not found in code | Data-cleanup utility deleting mismatched-branch draft attendance rows; no equivalent needed as standing logic. |
| `device_logs_iteration_fn`, `device_logs_resync_fn` | Functions | Biometric / Device Sync | Not found in code | Device-log resync utilities; no equivalent located. |
| `editpunch_rules` | Function | Biometric / Attendance | Not found in code | Called by both `update_breakoff` and the `scheduled_break_off_ai`/`_au` triggers to inject break-off punches into the pipeline; core dependency for break-off handling — its absence as a distinct function means, at minimum, the exact punch-injection mechanic isn't confirmed ported even if `scheduled_break_off` CRUD exists. |
| `customer_visit_history_fn` | Function | CRM (non-HRMS) | Dropped/obsolete by design | Legacy's own docs group this as a trivial CRM utility; CRM/field-visit tables are out of scope (§1.1, §1.6). |
| `calculate_emp_component_breakup` | Procedure | Salary & CTC | Not found in code | Component-level breakup for a single salary head item (structure ID + item + monthly component amount + monthly gross as inputs), resolving formula/derived-percentage/depends-value chains for one component in isolation rather than the whole structure. No isolated single-component breakup function found separate from `computeSalaryBreakup()`'s full-structure pass — if the new system needs a single-component recompute (e.g. after editing one structure line), it isn't a distinct confirmed function. |
| `leavepolicy_start_end_insert_prc` | Procedure | Leave Management | Not found in code | Iterates all active branches (cursor over `branches`) and, for each, inserts/refreshes leave-cycle start/end dates — a branch-wide batch companion to `leave_start_end_prc`'s per-employee lookup. No batch equivalent located; `leave_policies`/`leave_policy_groups` CRUD exists (§1.4) but a bulk cycle-date-refresh-across-branches routine was not identified. |

### 2.2 Control DB — Procedures & Functions (20 objects)

| Legacy Object | Type | Domain | Fate | Note |
|---|---|---|---|---|
| `company_statistics_prc` | Procedure | Platform Admin | Not found in code | Cross-tenant stats aggregation via dynamic SQL over each tenant DB; architecturally this pattern can't exist unchanged since tenants no longer have physical DBs — no cross-schema aggregation job located in New Rizo backend. |
| `CreateDatabasesAndUsers` | Procedure | Platform Admin | Dropped/obsolete by design | Physical per-tenant DB/user provisioning has no meaning in a `company_id`-column multi-tenancy model; `companies.controller.js` presumably handles tenant (company) creation as a simple row insert instead — architecturally correct to drop. |
| `generateDatabase` | Procedure (legacy/dry-run) | Platform Admin | Dropped/obsolete by design | Superseded even within the old system by `CreateDatabasesAndUsers`; doubly obsolete now. |
| `emp_id_maping_prc` | Procedure | Platform Admin | Not found in code | Name-matching device-employee mapping utility; `adms_device_employees` (§1.10) suggests a more direct enrollment approach may exist instead, but the specific matching logic wasn't located. |
| `inactive_db_audit_prc` | Procedure | Platform Admin | Dropped/obsolete by design | Per-tenant-DB dormancy audit; meaningless without physical per-tenant DBs. Note: no replacement analytics/usage-tracking mechanism was found either, so if tenant-activity monitoring is still a business need, it currently has no home in New Rizo (a difference worth noting, not a recommendation to build one). |
| `leave_end_process_parent_prc` | Procedure | Platform Admin (cron entry point) | Not found in code | Cross-tenant cron driver that invoked each tenant's `leave_end_process_fn()` via dynamic SQL; since `runYearEnd()` (§2.1) is a manually-triggered per-company endpoint, not an automated nightly cron, there is no equivalent scheduler-level driver — confirms leave year-end processing changed from "automatic, nightly, all tenants" to "manual, on-demand, one company at a time." |
| `single_signon_fn` | Function | Platform Admin | Dropped/obsolete by design | No SSO mechanism found in New Rizo at all (§1.10 `sso_audit`/`oauth2_*` all Not Found) — JWT-based auth replaces third-party SSO entirely rather than reimplementing it. |
| `trial_signup_fn` | Function | Platform Admin | Not found in code | Trial-slot-based signup provisioning; `companies.controller.js` likely handles company creation directly, but the specific trial-signup validation flow (duplicate email/company-code checks, `payroll_signup` staging table) was not located — and `payroll_signup` itself is Not Found at the table level (§1.10). |
| `add_emp_join_fkey_to_documents` | Procedure | Schema Maintenance | Dropped/obsolete by design | One-time cross-tenant schema-migration utility; meaningless in a single schema with a migration framework. |
| `alter_emp_advance_affected_month` | Procedure | Schema Maintenance | Dropped/obsolete by design | Same reasoning. |
| `alter_salary_amount_decimal` | Procedure | Schema Maintenance | Dropped/obsolete by design | Same reasoning. |
| `estern_username_update` | Procedure | Data Fix | Dropped/obsolete by design | One-time username-normalization fix; not standing logic. |
| `generate_deployment_sql` | Procedure | Schema Maintenance | Dropped/obsolete by design | Cross-tenant deployment SQL generator; meaningless with one schema. |
| `insert_reportcriterias_all`, `insert_reportcriterias_lop`, `insert_reportcriteria_salarystructures` | Procedures | Data Seeding | Not found in code | Seeds report filter criteria into each new tenant DB; `reportcriterias` table itself is Not Found (§1.1) — consistent, whole concept appears absent. |
| `update_doc_template_columns` | Procedure | Schema Maintenance | Dropped/obsolete by design | One-time column-add across tenant DBs; meaningless with one schema. |
| `update_emp_details_status` | Procedure | Data Fix | Dropped/obsolete by design | One-time data-normalization fix. |
| `update_income_tax_slab_2026` | Procedure | Data Update | Not found in code | Legacy modeled tax-slab-year updates as a stored procedure; `income_tax_slab` table exists live (§1.5) so slab data presumably gets updated via a migration script or admin UI instead of a stored procedure — consistent with the doc's own recommendation ("should be data migrations, not stored procedures"). |
| `emp_id_maping_exist_prc` | Procedure | Platform Admin | Not found in code | Companion existence-check to `emp_id_maping_prc`; same fate. |

### 2.3 Tenant DB — Triggers (24 objects)

| Legacy Object | Table | Fate | Note |
|---|---|---|---|
| `attandance_bu` | `attandance` | Not found in code | Duration auto-calc on outtime set; legacy's own docs say "handle in service layer" — plausible some controller does this inline on update, but not individually confirmed. |
| `attendance_punch_ai` | `attendance_punch` | Reimplemented in JS (likely) | `attendance_punches` table confirmed live (§1.2); mobile/web punch → device-punch-pipeline bridging logic presumably lives in `attendance.controller.js`, not individually line-verified. |
| `branches_bi` | `branches` | Not found in code | Auto branch-code generation; not confirmed — branch codes may now be manually entered or generated differently. |
| `branches_ai` | `branches` | Dropped/obsolete by design | Cross-DB sync to control-DB `company_branches` — meaningless with one schema; `branches` is already the single source of truth (§1.10). |
| `branches_au` | `branches` | Dropped/obsolete by design | Same reasoning. |
| `device_attandance_bi` | `device_attandance` | Not found in code | Complex shift-date assignment for punches (night-shift window logic); this is flagged by the source doc itself as "critical" — no equivalent pre-processing function was located for `device_punch_logs`/`adms_device_employees`. If not ported, night-shift attendance date attribution could be a real functional gap, though this report does not verify that conclusively. |
| `device_attandance_ai` | `device_attandance` | Not found in code | Real-time duration computation on punch insert; consistent with the broader finding that `time_duration_check`-family procedures have no located JS equivalent (§2.1). |
| `device_attandance_au` | `device_attandance` | Not found in code | Update variant of the above; same gap. |
| `emp_attendance_upload_bi` | `emp_attendance_upload` | Not found in code | Duplicate-detection + punch-pipeline injection for manual uploads; `emp_attendance_upload` itself has no confirmed live equivalent (§1.1). |
| `emp_config_bi` | `emp_config` | Dropped/obsolete by design | `emp_config`'s generic policy-assignment abstraction has no equivalent (§1.1) — direct FK columns on `employees` replace the need for a denormalization-sync trigger. |
| `emp_config_au` | `emp_config` | Dropped/obsolete by design | Same reasoning. |
| `emp_ctc_upload_ai` | `emp_ctc_upload` | Not found in code | Critical trigger chaining CTC upload → close old CTC transaction → create new → auto-distribute to salary structure; given `sal_structure_distribution_fn`'s equivalent is itself Not Found (§2.1), this chained auto-distribution behavior is very unlikely to be present in New Rizo — worth treating as a real, connected gap (upload table + distribution function + this trigger are all three unconfirmed together). |
| `emp_detailed_attendance_uploads_bi` | `emp_detailed_attendance_uploads` | Not found in code | Same pattern as `emp_attendance_upload_bi`. |
| `emp_details_bu` | `emp_details` | Not found in code | Column-by-column audit-snapshot trigger; consistent with `emp_details_audit`/`activity`/`history` all being explicitly Out of Scope or Not Found (§1.1) — no audit-on-update mechanism was located anywhere in New Rizo. |
| `emp_proff_ai` | `emp_proff` | Dropped/obsolete by design | Branch-code sync between `emp_proff` and `emp_details`; moot since the two tables were merged into one `employees` table (§1.1) — nothing to sync. |
| `emp_proff_au` | `emp_proff` | Dropped/obsolete by design | Same reasoning. |
| `leave_balance_upload_bi` | `leave_balance_upload` | Not found in code | Self-computing adjustment-delta trigger; `leave_balance_upload` itself has no confirmed live table (§1.4). |
| `mob_user_locations_ai` | `mob_user_locations` | Dropped/obsolete by design | Mobile tracking explicitly out of scope (§1.2). |
| `mob_user_login_auditor_ai` | `mob_user_login_auditor` | Dropped/obsolete by design | Same. |
| `scheduled_break_off_ai` | `scheduled_break_off` | Not found in code | Break-off → punch-injection via `editpunch_rules`; since `editpunch_rules` itself is Not Found (§2.1), the injection mechanic specifically is unconfirmed even though the `scheduled_break_off` table survives with its name unchanged. |
| `scheduled_break_off_au` | `scheduled_break_off` | Not found in code | Same reasoning — cancel/reactivate punch handling unconfirmed. |
| `site_attendance_ai` | `site_attendance` | Dropped/obsolete by design | Site module not ported (§1.2). |
| `site_attendance_bu` | `site_attendance` | Dropped/obsolete by design | Same. |
| `user_credentials_au` | `user_credentials` | Reimplemented in JS (partial) | `user_credentials` table confirmed live unchanged (§1.10); the default-role-assignment-on-access-grant half is plausibly covered by RBAC table population elsewhere in `users.controller.js` (not individually confirmed), while the email-change cross-DB-sync half is architecturally moot (single DB, no `emp_device_comp_branch` cross-sync needed). |

---

## TALLY

### Part 1 — Tables (309 total: 236 tenant + 73 control)

| Status | Count (approx.) |
|---|---|
| Renamed (1:1 name/structure mapping found) | 62 |
| Restructured (concept kept, structure changed materially) | 48 |
| Same name kept | 16 |
| Not Found (searched, no trace in docs or code) | 108 |
| Out of Scope (explicitly excluded per migration guide or clearly throwaway/backup/dated) | 75 |

*(Counts are derived from the row-by-row classification above; a small number of tables were counted once even though they appear in more than one module cross-reference, e.g. `emp_expense`/`expense_type` listed in both §1.1 and §1.9.)*

### Part 2 — Procedures / Functions / Triggers (135 total: 91 tenant + 20 control + 24 triggers)

| Fate | Count |
|---|---|
| Reimplemented in JS (confirmed working code cited) | 15 |
| Reimplemented in JS (partial — some sub-behavior confirmed missing or simplified) | 13 |
| Reimplemented as report only (not wired into live processing) | 3 |
| Not found in code (no trace beyond the planning doc) | 67 |
| Dropped/obsolete by design (doesn't apply to new architecture, correctly not ported) | 39 |

### Notable cross-check findings (docs vs. actual code)

1. **PF/ESI/PT/TDS statutory calculation is report-only, not payroll-integrated.** `statutory.controller.js` derives these figures by scanning payslip line labels after the fact, not by an independent calculation engine feeding the payslip during processing — contrary to what a reader of `_DB_PROCEDURES.md` alone (which frames these as "implement as `StatutoryCalculationService.compute()`") would assume was built.
2. **Holiday allowance, shift allowance, OT allowance, and leave-encashment payout are absent from `payroll.service.js`** — confirmed by direct grep, not just doc inference. Loan/advance EMI auto-deduction is similarly absent from the payroll run (though both `employee_loans`/`employee_advance` exist as standalone CRUD modules and are read during full-and-final settlement).
3. **Leave year-end carry-forward DOES exist**, contradicting the prior-research assumption that it was entirely missing — it's a manually-triggered endpoint (`runYearEnd()`) rather than the legacy's automated nightly cron, and uses a simplified single-limit carry-forward model instead of legacy's six-cycle-type (Y/M/Q/H/P/D) rollover logic.
4. **The planning doc (`_MIGRATION_GUIDE.md`) and the shipped code disagree on table names in several places** — e.g. `emp_salary_slip` was planned as `payroll_entries` but shipped as `payroll_slip_lines`; `emp_details`+`emp_proff` were planned as two joined tables but shipped merged into one `employees` table; `attendance_register` was planned as two tables (`attendance_monthly_summary` + `attendance_daily_records`) but shipped as `attendance`+`attendance_audit`. The underlying design intent (normalize away FIELD1-31, separate person from job data less rigidly) was mostly honored even where exact names weren't.
5. **The entire site/contract-labour attendance and payroll sub-module (10+ tables, 6+ procedures, 2 triggers) shows no trace anywhere in New Rizo** — not flagged as explicitly out-of-scope by the migration guide (unlike inventory), simply absent.
6. **Asset tracking was kept (with identical table names) while general inventory/procurement was dropped** — a deliberate scope split within what the migration guide lumps together as one category.
