# RIZO (MyPayrollMaster) — Backend Behavior Report

**Scope:** `D:\Projects\RIZOMigration\legacy` — a CakePHP 2.x HR/payroll SaaS application, plus the separate `api/` mini-app. This report documents backend structure and behavior (controllers, models, auth, background work, integrations, caching/logging) to inform the Next.js migration. It complements the earlier user-facing behavior report (`user-side-report.md`), which covered end-user flows; this one covers the machinery underneath.

**Method:** Assembled from 15 parallel deep-read passes: one dedicated to the `api/` subsystem, one covering Components/Behaviors/Console-Shells/background-jobs/external-integrations, and 13 covering feature-area controller clusters (same clustering as the prior user-facing report) each producing a full controller action inventory (action, HTTP method, response type) plus model-layer validation/business-rule findings. Every claim is cited `path:line`; unverified/inferred claims are marked `INFERRED:`. Dead/deprecated/unreachable code is flagged `possible legacy cruft — verify before migrating` per the request.

**Codebase scale:** ~223 controllers (`Controller/*.php`), ~190 models (`Model/*.php`), a separate `api/v1/` mini-app. No `Console/`, `Plugin/`, or `Vendor/` directories exist in this checkout — only the app-level `Controller/`, `Model/`, `View/`, `Config/`, `api/`, and `schema/` folders are present (CakePHP core/vendor files are not part of this repo).

---

## 1. Architecture Overview & How the Subsystems Relate

### 1.1 Two independent backends, one product

This codebase is really **two separate backend systems that happen to share a database**:

1. **The main CakePHP 2.x app** (`Controller/`, `Model/`, `View/`, `Config/`) — a traditional server-rendered MVC app. All authentication is a hand-rolled session model (§3), all business logic lives in ~223 fat controllers and a mix of raw SQL / stored procedures, and nearly every backing model is an empty passthrough shell (§7 — this was independently confirmed by all 13 cluster passes: **zero `$validate` arrays, zero `beforeSave`/`beforeValidate`/`afterSave` hooks found anywhere in the Model/ layer across the entire app**, including the shared `Model/AppModel.php` base class).
2. **`api/v1/`** — a separate Slim 3 micro-framework app reachable at a different URL path, apparently built for the mobile app (attendance punch-in/out, leave requests from mobile, expense receipt photo upload — see §2). **This checkout is missing its `src/` and `vendor/` directories** — only `index.php`, `composer.json`/`composer.lock`, log files, and loose uploaded images exist. Its actual route/endpoint definitions could not be read from source; §2 reconstructs the endpoint catalog from `api/v1/logs/request.txt` access logs instead, and every claim there is marked `INFERRED: from access logs, not source`.

### 1.2 How they relate

- **No shared authentication.** The main app authenticates via CakePHP session (`user_group` in `$_SESSION`). `api/v1` shows no evidence of returning a session token/JWT on login (confirmed by inspecting example login request/response payloads in the access logs) — it appears to rely on some other mechanism (possibly a fixed API key, or per-request credentials, or nothing) that could not be verified from the available source. This is a genuine gap to close with the client before migration: **do not assume `api/v1`'s auth model without asking**, since it's inferred from traffic, not code.
- **No shared PHP code.** `api/v1` does not `require`/`use` any class from `Controller/` or `Model/` — it's a fully separate PHP process/framework. There is no evidence of a shared ORM layer or shared model classes between the two.
- **Likely shared database, unconfirmed shared credentials.** `api/v1` almost certainly reads/writes the same MySQL tables the main app uses (attendance punches, leave requests, expense records) based on action-name overlap in the logs (`markattendance`, leave-related actions, expense-with-photo actions) and confirmed model-level table overlap (e.g. `legacy/Model/EmployeeExpenses.php` maps to the same table the main app's `EmployeeExpensesController.php`/`ExpenseReportController.php`/`ProjectExpensesController.php` read). Whether it connects via the same dual-DB architecture (control DB → per-company DB) as the main app, or via separate/hardcoded credentials, was not confirmed from source since `src/` is missing — flagged for verification.
- **A third, undocumented API surface exists.** `schema/mypayrol_control_db.sql:1433-1436` shows the `central_control` table stores both an `api_url` (v1, defaulting to `apps.office24.online/forsight/api/v1/`) and a separate `app_url` (v3, defaulting to `v1.mypayrollmaster.online/api/v3/`) — **on different hostnames**. A "v3" API is referenced in configuration but does not exist anywhere in this checkout. Additionally, the main CakePHP app itself contains a second, independent mobile-integration surface — `Controller/MobileLocationUpdateController.php` and `Controller/LeaveapiController.php` (the latter already flagged in the prior user-facing report as unauthenticated) — separate from both `api/v1` and the phantom `v3`. **There appear to be at least three distinct "API" surfaces referenced across this codebase/config, only one of which (`api/v1`) has any code present.** This needs product/client clarification before the Next.js migration scopes its API layer.

### 1.3 Cross-cutting findings (confirmed independently by multiple research passes)

- **The Model/ layer carries almost no business logic.** Across every one of the ~190 models touched by the 13 cluster passes, none define `$validate`, and none implement `beforeSave`/`beforeValidate`/`afterSave`/`beforeDelete` hooks beyond a handful of narrow exceptions noted per-cluster below. Business rules — eligibility calculations, approval chains, stock adjustments, tax formulas — live entirely in controller PHP (often raw SQL) or in MySQL stored procedures called via one-line model wrappers. **This means "migrate the models" is not a meaningful phase for this codebase** — the real business logic has to be re-derived from controllers and, in some cases, from stored procedures not visible in the PHP tree at all (Payroll, Attendance, Site-Attendance clusters all confirmed opaque stored-proc dependencies — see §5, §3, §9).
- **No CakePHP `AuthComponent`/ACL is used** despite the scaffolding existing (`Config/acl.php`, `Config/Schema/db_acl.php`). Real authorization is the hand-rolled `user_group` session check in `Controller/AppController.php:39-46`, with a second, **UI-only** fine-grained permission layer (`emp_menu`/`user_access` tables) that is never re-checked server-side by any of the ~223 controllers surveyed.
- **No in-repo background job mechanism exists.** No `Console/`/Shell directory anywhere in the tree; no queue-table patterns found. Any periodic/scheduled processing (if it exists in production) must be external — e.g. a system crontab hitting a URL — and cannot be confirmed or denied from source alone (§6).
- **Hand-rolled inline PHPMailer, not CakePHP's `EmailComponent`, is the dominant email mechanism**, and it's genuinely dangerous from a secrets-hygiene standpoint: at least 4 distinct hardcoded SMTP credential sets (Gmail, two Zoho variants, Oracle Cloud OCI) are scattered across 15+ controller files (§4, §6).
- **Response format is almost never content-negotiated** — the vast majority of "JSON" actions are `autoRender=false` + `echo json_encode(...)`, not CakePHP's `RequestHandler`/REST conventions, and HTTP-method enforcement is inconsistent (most actions accept GET or POST interchangeably; only a minority explicitly check `$this->request->is('post')`).
- **Raw SQL string interpolation (SQL-injection-shaped code) is pervasive**, not isolated to one or two controllers — every cluster pass flagged multiple instances. This is a systemic pattern, not a one-off bug, and should inform how aggressively the Next.js rewrite adopts parameterized queries/an ORM from day one.

### 1.4 How to read this report

1. **§2 — Controller Inventory by Feature Area** (report requirement #1: every controller, its actions, HTTP methods, HTML vs JSON). Organized into the same 13 feature-area clusters as the prior user-facing report (Employee, Attendance, Leave, Payroll/Tax, Advances/Loans/Expenses, Reports, Company Setup, Site/Field/Project, Assets/Inventory/Purchase, Performance/HR, Dashboards/Notifications, Statutory/Access, Timesheet). Because model validation is naturally scoped per feature area, each cluster's **Model Validation & Business Rules** findings are embedded directly after its controller table (this also answers report requirement #7 — see the cross-cutting summary in §8 for the headline pattern across all clusters).
2. **§3 — The `api/` Subsystem** (requirement #2), kept fully separate as requested.
3. **§4 — Authentication & Authorization** (requirement #3).
4. **§5 — Components & Behaviors** (requirement #4).
5. **§6 — Background / Scheduled Work** (requirement #5).
6. **§7 — External Integrations** (requirement #6).
7. **§8 — Model-Layer Validation & Business Rules: cross-cutting summary** (requirement #7 — the headline finding across all of §2's per-cluster detail).
8. **§9 — Caching & Error Handling / Logging** (requirement #8).

---


## 2. Controller Inventory by Feature Area

_Each subsection below is one feature-area cluster: full per-controller action inventory (action, HTTP method, response type, purpose, path:line), followed by that cluster's Model-Layer Validation & Business Rules findings._


### 2.1 Employee Management

# Employee Management Cluster — Backend Technical Report

Scope: `Controller/EmployeeController.php`, `EmployeeDetailController.php`, `EmployeeJoinController.php`, `EmployeeRegisterController.php`, `EmployeeManageController.php`, `EmployeeConfigController.php`, `EmployeeMenuController.php`, `EmployeeHierarchyController.php`, `EmployeeUnderController.php`, `EmployeeResignationController.php`, `NoticePeriodController.php`, `EmployeeEmiController.php`, `DocumentManagerController.php` / `DocumentManagersController.php`, `ContactsController.php`, `BeneficiaryController.php`, and their backing models.

All paths below are relative to `D:\Projects\RIZOMigration\legacy\`.

## 0. Cross-cutting findings (apply to every controller in this cluster)

1. **No per-action server-side authorization anywhere in this cluster.** None of the 16 controllers define `beforeFilter()` or `isAuthorized()`. Access control is entirely the session-based `AppController` login gate (`Controller/AppController.php:39-46`, checks `user_group` 1=admin/2=employee at the app level only). Inside actions, `user_group`/`company_code` reads (e.g. `Controller/EmployeeController.php:68-114`, `Controller/EmployeeConfigController.php:40-109`, `Controller/EmployeeResignationController.php:143-146`, `Controller/EmployeeEmiController.php:64-67`, `Controller/DocumentManagerController.php:1021-1167`) are used only to branch **what data is returned/rendered** (admin view vs. self-service view, or hard-coded company-code allow-lists like `GLET`/`ABSG`/`GAAR`), never to reject a request. **Any authenticated session (admin or employee) can call any action in these controllers**, including other employees' records, by knowing the URL/params — verified by absence of any denial path. Flag for migration: every one of these ~450 endpoints needs real authorization added in the Next.js layer; nothing here can be trusted as-is.
2. **Models are essentially empty wrappers.** Every model backing this cluster (`Model/EmployeeDetails.php`, `EmpDetails.php`, `EmployeeProfessionalDetails.php`, `EmployeeCTC.php`, `EmployeeJoin.php`, `Beneficiary.php`, `Contacts.php`, `NoticePeriod.php`, `EmpDocument.php`, `EmpFam.php`, `Family.php`, `passport.php`, `qualifcations.php`, `history.php`, `ResignationRequests.php`, `Termination.php`, `EmployeeLoanInfo.php`, `LoanEmi.php`, `EmployeeLoan.php`, `Documents.php`, `DocumentUpload.php`, `DocumentAllocation.php`, `EmployeeStructure.php`, `Organization.php`) declares only `$name`, `$primaryKey`, `$useTable`, and occasionally `$virtualFields`/`$order`. **None contain a `$validate` array, `beforeSave()`, `beforeValidate()`, `afterSave()`, or an active `belongsTo`/`hasMany`/`hasOne` association** — see `Model/AppModel.php:34-38` (base class is a bare passthrough with no shared hooks either). Two models (`EmployeeDetails.php:22-26`, `EmployeeJoin.php:22-26`, `EmpFam.php:20-24`) have a `hasOne EmployeeProfessionalDetails` association present only as a **commented-out block**. **Conclusion: there is no model-layer validation or business-rule enforcement anywhere in this cluster.** All field validation (mandatory fields, format checks, duplicate checks) is done ad hoc inline in controller action bodies via `isset()`/`empty()` checks and raw SQL `count(*)` queries (e.g. `Controller/NoticePeriodController.php:119-183` re-implements uniqueness checks by hand with string-interpolated SQL). This is a major migration risk: business rules must be reverse-engineered from controller code, not models, and there is no reusable validation layer to port — a Next.js/Zod (or similar) validation layer must be built from scratch.
3. **Multi-tenant datasource switching per request.** Nearly every action calls `$this->{Model}->useDbConfig = $this->Session->read('ds');` before querying — the CakePHP model's DB connection is switched per-request based on a session value (`ds`), consistent with the dual-DB/multi-tenant architecture noted in project memory. This pattern must be replicated in the Next.js API layer (e.g., resolving tenant DB connection per authenticated session) for every migrated endpoint.
4. **Widespread SQL injection risk.** Several actions build raw SQL by string concatenation of request data with no escaping, e.g. `Controller/NoticePeriodController.php:94-119` (`"and notice_pkey=$notice_pkey"`, `"where description='$description'"`), `Controller/EmployeeUnderController.php:97,123,126` (`'branch_code="'.$sitepkey_array.'"'`), `Controller/EmployeeMenuController.php:210-215` (`WHERE user_fkey = '$emp_fkey'` inside `$this->EmployeeDetails->query()`). Flag every raw `->query("...$var...")` call for rewrite using parameterized queries in the new backend.
5. **Duplicate/legacy controller files excluded from full trace per instructions** (noted only): `Controller/EmployeeController_old_before addnominee.php` (legacy backup of EmployeeController), `Controller/EmployeeResignationControllerBkup.php` (1017-line backup of EmployeeResignationController), `Controller/EmployeeAdvanceReportsController_2018-010.php`, `EmployeeExpenseReportsController_2018-10.php`, `EmployeeLoanReportsController_2018-10.php` (dated duplicates, out of scope but same pattern family).
6. **`DocumentManagerController.php` and `DocumentManagersController.php` are near-duplicate controllers** — same class shape, same ~48 action names, same code (the "s" variant adds `Country` to `$uses`, an extra `getEmployeesJoin()` action, and a couple of small session/company-code tweaks e.g. `Controller/DocumentManagersController.php:259-261`). This looks like an in-progress fork/rename; **verify with the team which one is actually routed/live before migrating** — do not port both.
7. **Dead/commented-out duplicate actions.** Several controllers contain an old implementation of a method left as a `/* ... */` or `// ...` block immediately next to (usually just above) a live, uncommented version with the identical name. These were detected by scanning for `public function` occurring inside comments; confirmed dead (not just visually adjacent) by reading the surrounding lines. Listed per-controller below as "possible legacy cruft — verify before migrating" but not traced further.

---

## 1. EmployeeController.php (`Controller/EmployeeController.php`, 9234 lines, 119 `public function` matches)

`$uses` (`Controller/EmployeeController.php:52`): `Menu, CentralControl, EmployeeSalaryStructure, EmployeeConfig, Family, passport, Promotion, NoticePeriod, qualifcations, history, EmployeeTaxTransactions, EmpTaxSalTrans, FinancialYear, UserCredentials, EmployeeDetails, Designation, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, TaxHead, EmployeeCTC, EmpAlterationDetails, ReportCriterias, SalaryIncrement, SalaryIncrementDetails, ComponentIncrement, EditPunches, WorkExperience, EmpDocument, EmpFam, Education, EmployeeJoin`

This is the primary "confirmed employee" record controller — CRUD for personal/professional details, tax setup, salary/CTC upload, promotions, salary increments, profile documents (qualifications/passport/family/history), profile-picture/import (photo/biometric API), and PDF resume/payslip generation.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (no check) | HTML view | Employee landing/list view | Controller/EmployeeController.php:59 |
| setups | GET/POST | redirect-only (die() at end) | Legacy admin-vs-employee setup gate branching on `user_group`; terminates with `die()` | Controller/EmployeeController.php:123 |
| downloadempdataformatjoin | GET/POST | file download (autoRender=false) | Streams an Excel template for bulk-join employee upload | Controller/EmployeeController.php:388 |
| saveToDetails | POST only (`is('post')`) | JSON | Saves employee-details form fields (AJAX save) | Controller/EmployeeController.php:577 |
| updateEditable (dead) | POST | JSON | Commented-out old inline-edit save handler | Controller/EmployeeController.php:831 (commented, see note) |
| updateEditable | POST only | JSON | Live inline-edit (x-editable grid) field update handler | Controller/EmployeeController.php:857 |
| savedata | GET/POST | JSON | Saves a single field/record keyed by `$pkey` | Controller/EmployeeController.php:1096 |
| empDataThirdparty_update | GET/POST | JSON | Pushes/updates employee data to a third-party integration | Controller/EmployeeController.php:1125 |
| companyHistory_add | GET/POST | JSON | Appends a company-history record for an employee | Controller/EmployeeController.php:1269 |
| setupprofile | GET/POST | HTML view | Renders employee self-profile setup screen | Controller/EmployeeController.php:1297 |
| listimported | GET/POST | HTML view | Lists employees imported via bulk-import/biometric flow | Controller/EmployeeController.php:1459 |
| employeesunder | GET/POST | HTML view | Lists employees reporting to the logged-in user | Controller/EmployeeController.php:1501 |
| testfunction | GET/POST | none (autoRender=false, empty) | Dead debug stub | Controller/EmployeeController.php:1609 |
| checkProff | GET/POST | none (autoRender=false) | Checks if professional-details record exists | Controller/EmployeeController.php:1616 |
| getstages | GET/POST | HTML fragment (autoRender=false) | Returns `<option>` HTML for a branch/stage dropdown | Controller/EmployeeController.php:1624 |
| getautohierarchycompletions | GET/POST | JSON | Autocomplete for hierarchy parent selection | Controller/EmployeeController.php:1650 |
| getautohierarchycompletionsvgfs | GET/POST | JSON | Same as above, VGFS/VSFS company variant | Controller/EmployeeController.php:1695 |
| getautocompletions | GET/POST | JSON | General employee-name autocomplete | Controller/EmployeeController.php:1736 |
| listemployeesProfileImporteds | GET/POST | JSON | Datagrid list of imported-profile employees | Controller/EmployeeController.php:1791 |
| listemployees | GET/POST | JSON | Main employee datagrid list (paged, filtered) | Controller/EmployeeController.php:1946 |
| jsons | GET/POST | JSON (autoRender=false) | Branch-filtered employee list for dropdown/grid | Controller/EmployeeController.php:2180 |
| jsonss | GET/POST | JSON (autoRender=false) | Employee-filtered variant of jsons | Controller/EmployeeController.php:2225 |
| removeProfileImage | GET/POST | JSON | Deletes an employee's profile photo | Controller/EmployeeController.php:2267 |
| jsonsb | GET/POST | JSON (autoRender=false) | Branch-filtered list variant (used elsewhere e.g. `jsons_getemps`) | Controller/EmployeeController.php:2281 |
| setup | GET/POST | HTML view (die() present elsewhere in fn) | Main add/edit employee setup form (personal+professional+tax) | Controller/EmployeeController.php:2309 |
| getGrade | GET/POST | JSON | Returns grade info for a category | Controller/EmployeeController.php:2574 |
| showtaxheaddetail | GET/POST | HTML fragment | Renders tax-head detail sub-form | Controller/EmployeeController.php:2593 |
| loadEmpDetails | GET/POST | view var set (no direct output) | Loads personal-info into view context; helper used by `setup` | Controller/EmployeeController.php:2620 |
| loadEmpProfDetails | GET/POST | view var set | Loads professional-info + auto-generates next `emp_id`; helper | Controller/EmployeeController.php:2638 |
| Finyear | GET/POST | none (autoRender=false, empty body) | Dead stub | Controller/EmployeeController.php:2712 |
| promotion | GET/POST | HTML view | Renders promotion form for an employee | Controller/EmployeeController.php:2717 |
| getautocompletions_superior | GET/POST | JSON | Autocomplete for "reports to" / superior selection | Controller/EmployeeController.php:2798 |
| savepromotions | GET/POST | JSON | Saves a promotion record | Controller/EmployeeController.php:2849 |
| approvepromotion | GET/POST | HTML view | Approves a pending promotion | Controller/EmployeeController.php:2879 |
| empprofdetails | GET/POST | view vars set | Loads combo lists + professional profile for a hard-coded `emp_pkey=1` (**bug/dead — always pkey 1**, `Controller/EmployeeController.php:2969`) | Controller/EmployeeController.php:2967 |
| emptaxationdetails | GET/POST | empty (no body) | Dead stub | Controller/EmployeeController.php:3020 |
| uploadProfileImage | GET/POST | view vars set | Handles profile-photo upload | Controller/EmployeeController.php:3022 |
| saveemployeesetupnew | GET/POST | JSON | Newer combined save handler for employee setup (large, 530 lines) | Controller/EmployeeController.php:3097 |
| restsave | GET/POST | none (helper) | Resets/re-saves helper invoked from setup flow | Controller/EmployeeController.php:3629 |
| saveemployeesetup | GET/POST | JSON | Original combined save for `EmployeeDetails`/`EmployeeProfessionalDetails` by dynamic `$model` switch; also auto-provisions `UserCredentials` and calls `linkempDeviceanddatabase` stored routine | Controller/EmployeeController.php:3645 |
| addToNotice | GET/POST | JSON | Adds an employee to a notice-period shift | Controller/EmployeeController.php:3770 |
| jsons_getemps | GET/POST | JSON (autoRender=false) | Employee list for a given branch (combo data) | Controller/EmployeeController.php:3793 |
| saveconfigs | GET/POST | JSON | Bulk-saves employee config assignments (large, 240 lines) | Controller/EmployeeController.php:3832 |
| downloadempdataformat | GET/POST | file download | Streams Excel template for bulk employee-data upload (PHPExcel) | Controller/EmployeeController.php:4075 |
| mailsend | GET/POST | mixed | Sends an email (e.g. credentials) to a named employee | Controller/EmployeeController.php:4334 |
| uploadandsaveempdetails | GET/POST | JSON | Parses uploaded Excel and bulk-inserts employees (large, 330 lines) | Controller/EmployeeController.php:4365 |
| getcurrentemployeekey | GET/POST | JSON | Returns current session employee's pkey | Controller/EmployeeController.php:4695 |
| showsalaryupload | GET/POST | HTML view | Renders salary/CTC upload screen | Controller/EmployeeController.php:4706 |
| downloadempctcformat | GET/POST | file download | Streams Excel CTC-upload template | Controller/EmployeeController.php:4717 |
| uploadandsaveempctc | GET/POST | JSON | Parses uploaded CTC Excel and bulk-saves (large, 257 lines) | Controller/EmployeeController.php:4904 |
| form | GET/POST | HTML view | Simple add/edit popup form | Controller/EmployeeController.php:5161 |
| employeesave | GET/POST | JSON | Save handler used by `form` popup | Controller/EmployeeController.php:5234 |
| employeelist | GET/POST | JSON | Alternate employee datagrid (used by dropdown popup) | Controller/EmployeeController.php:5306 |
| ctcupload | GET/POST | HTML view | Renders CTC upload landing page | Controller/EmployeeController.php:5475 |
| deleteEmployees | GET/POST | JSON | Soft-deletes selected employees | Controller/EmployeeController.php:5537 |
| deleteEmp | GET/POST | JSON | Soft-deletes a single employee | Controller/EmployeeController.php:5555 |
| activeEmp | GET/POST | JSON | Reactivates an employee | Controller/EmployeeController.php:5574 |
| addqualification | GET/POST | HTML view | Renders add-qualification sub-form | Controller/EmployeeController.php:5598 |
| addfamily | GET/POST | HTML view | Renders add-family sub-form | Controller/EmployeeController.php:5605 |
| passport | GET/POST | HTML view | Renders add-passport sub-form | Controller/EmployeeController.php:5612 |
| savehistory (live) | GET/POST | JSON (autoRender=false) | Saves employment-history record | Controller/EmployeeController.php:5619 |
| savefamily (live) | GET/POST | JSON | Saves family-member record | Controller/EmployeeController.php:5629 |
| savepassport (live) | GET/POST | JSON | Saves passport/visa record | Controller/EmployeeController.php:5638 |
| savequalifications (live) | GET/POST | JSON | Saves qualification record | Controller/EmployeeController.php:5647 |
| history | GET/POST | HTML view | Renders history sub-form | Controller/EmployeeController.php:5658 |
| savequalifications (dead) | — | — | Entire old implementation commented out (lines 5666-5701) — superseded by live version at :5647 | Controller/EmployeeController.php:5666 |
| savepassport (dead) | — | — | Commented out (5703-5765), superseded by :5638 | Controller/EmployeeController.php:5703 |
| savefamily (dead) | — | — | Commented out (5767-5793), superseded by :5629 | Controller/EmployeeController.php:5767 |
| savehistory (dead) | — | — | Commented out (5795-5816), superseded by :5619 | Controller/EmployeeController.php:5795 |
| getEducation | GET/POST | JSON | Fetches qualification/education records | Controller/EmployeeController.php:5818 |
| getExperience | GET/POST | JSON | Fetches work-experience records | Controller/EmployeeController.php:5836 |
| getFamily | GET/POST | JSON | Fetches family records | Controller/EmployeeController.php:5856 |
| getDocument | GET/POST | JSON | Fetches uploaded-document records | Controller/EmployeeController.php:5876 |
| savequalificationsjoin | GET/POST | JSON | Saves qualification for a "join"/candidate record | Controller/EmployeeController.php:5896 |
| savepassportjoin | GET/POST | JSON | Saves passport for a join record | Controller/EmployeeController.php:5933 |
| savefamilyjoin | GET/POST | JSON | Saves family for a join record | Controller/EmployeeController.php:5997 |
| savehistoryjoin | GET/POST | JSON | Saves history for a join record | Controller/EmployeeController.php:6025 |
| lstfamilies | GET/POST | JSON | Lists family records for an employee | Controller/EmployeeController.php:6050 |
| listhistory | GET/POST | JSON | Lists history records | Controller/EmployeeController.php:6071 |
| getusers | GET/POST | JSON | Looks up user-credential info by `emp_fkey` | Controller/EmployeeController.php:6096 |
| listqualifications | GET/POST | JSON | Lists qualification records | Controller/EmployeeController.php:6150 |
| listpassports | GET/POST | JSON | Lists passport records | Controller/EmployeeController.php:6168 |
| savefile | GET/POST | JSON | Uploads/saves a document file for an employee | Controller/EmployeeController.php:6198 |
| deletequal | GET/POST | none (autoRender=false) | Deletes a qualification record | Controller/EmployeeController.php:6288 |
| deletepassports | GET/POST | none | Deletes a passport record | Controller/EmployeeController.php:6298 |
| deletehist | GET/POST | none | Deletes a history record | Controller/EmployeeController.php:6308 |
| deletefdetails | GET/POST | none | Deletes a family record | Controller/EmployeeController.php:6319 |
| mark_nominee | GET/POST | none | Marks a family member as nominee | Controller/EmployeeController.php:6335 |
| mark_emergency | GET/POST | JSON | Marks a family member as emergency contact | Controller/EmployeeController.php:6349 |
| importProfile | GET/POST | HTML view + JSON (die() present) | Imports a candidate profile (biometric/HR-tech integration) into employee records | Controller/EmployeeController.php:6384 |
| requestPermissionAccess | GET/POST | HTML view | Requests device/branch access permission | Controller/EmployeeController.php:6514 |
| getprofileinfo | GET/POST | JSON + HTML view (mixed) | Fetches profile info from an external HR API | Controller/EmployeeController.php:6544 |
| getDataFromAPI | GET/POST | none (autoRender=false) | Calls external API for profile data | Controller/EmployeeController.php:6849 |
| getUID | GET/POST | JSON | Resolves a unique ID from the external API by email | Controller/EmployeeController.php:6878 |
| showimportresponse | GET/POST | HTML view | Shows bulk-import results/errors summary | Controller/EmployeeController.php:6912 |
| sendpasswordemail | GET/POST | mixed | Emails login credentials to a user | Controller/EmployeeController.php:6937 |
| updateSalStructureDistributionFn (dead) | — | — | Entirely commented out | Controller/EmployeeController.php:7153 |
| downloadResume | GET/POST | PDF file download | Generates & streams employee resume PDF (TCPDF) | Controller/EmployeeController.php:7184 |
| downloadResume2 | GET/POST | PDF file download | Alternate resume PDF template | Controller/EmployeeController.php:7476 |
| currentctctake | GET/POST | JSON | Returns current CTC breakdown for an employee | Controller/EmployeeController.php:7891 |
| changeBranchAr | GET/POST | JSON | Changes branch for arrears/CTC calc context | Controller/EmployeeController.php:7956 |
| incrimentdatapdf | GET/POST | none (autoRender=false) | Generates increment-data PDF | Controller/EmployeeController.php:7999 |
| vdaRevisionForm | GET/POST | HTML view | Renders VDA (variable dearness allowance) revision form | Controller/EmployeeController.php:8150 |
| employeeincrement | GET/POST | none | Increment-flow helper/stub | Controller/EmployeeController.php:8164 |
| loadcriteriaitems | GET/POST | HTML view | Loads report-criteria items | Controller/EmployeeController.php:8179 |
| listcriteriaitems | GET/POST | JSON | Lists report-criteria items | Controller/EmployeeController.php:8198 |
| generatereport | GET/POST | none (autoRender=false) | Generates a report given type/mode | Controller/EmployeeController.php:8293 |
| generateemployeincrement | GET/POST | HTML view | Generates increment report view | Controller/EmployeeController.php:8306 |
| salaryIncrementForm | GET/POST | HTML view | Renders salary increment form | Controller/EmployeeController.php:8594 |
| getSalaryStructure | GET/POST | JSON | Fetches an employee's salary structure | Controller/EmployeeController.php:8611 |
| calcSalaryStructure | GET/POST | JSON | Recalculates salary structure figures | Controller/EmployeeController.php:8701 |
| changeIncrementType | GET/POST | HTML view (autoRender=false too, mixed) | Switches increment calc type (percentage/flat/item) | Controller/EmployeeController.php:8738 |
| alterSalaryStructure | GET/POST | JSON | Applies an altered salary structure | Controller/EmployeeController.php:8763 |
| onEffectiveDateChange | GET/POST | JSON | Recomputes figures when increment effective date changes | Controller/EmployeeController.php:8898 |
| saveIncrement | GET/POST | JSON | Saves a salary increment record | Controller/EmployeeController.php:8931 |
| itemIncrementForm | GET/POST | HTML view | Renders item-level increment form | Controller/EmployeeController.php:9135 |
| saveItemIncrement | GET/POST | JSON | Saves item-level increment | Controller/EmployeeController.php:9142 |
| Header (MYPDF class, not controller) | n/a | n/a | TCPDF page header override, not an HTTP action | Controller/EmployeeController.php:9185 |
| Footer (MYPDF class) | n/a | n/a | TCPDF page footer override, not an HTTP action | Controller/EmployeeController.php:9224 |

**Notes:** `empprofdetails` (Controller/EmployeeController.php:2967) hard-codes `$emp_pkey=1` at line 2969 regardless of the parameter passed in — **possible legacy cruft / bug, verify before migrating**. `Finyear`, `emptaxationdetails`, `testfunction` are empty stubs — dead code. The four "…join" save endpoints (`savequalificationsjoin` etc., lines 5896-6048) write against the same family/qualification/passport/history tables used by the plain `save*` actions but presumably keyed off `emp_join_pkey` for onboarding candidates — cross-check with `EmployeeJoinController` equivalents before consolidating.

---

## 2. EmployeeDetailController.php (`Controller/EmployeeDetailController.php`, 62 lines)

`$uses` (line 51): `CentralControl, EmployeeConfig, Family, passport, NoticePeriod, qualifcations, history, EmployeeTaxTransactions, EmpTaxSalTrans, FinancialYear, UserCredentials, EmployeeDetails, Designation, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, TaxHead, EmployeeCTC`

Single action, empty body — **entirely dead/scaffold code**, not wired to anything functional.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | HTML view (empty body) | Placeholder — no logic implemented | Controller/EmployeeDetailController.php:58 |

---

## 3. EmployeeJoinController.php (`Controller/EmployeeJoinController.php`, 13261 lines, 148 `public function` matches)

`$uses` (line 6): `CentralControl, EmpDocument, EmpFam, Education, WorkExperience, EmployeeJoin, EmployeeSalaryStructure, EmployeeConfig, Family, passport, Promotion, NoticePeriod, qualifcations, history, EmployeeTaxTransactions, EmpTaxSalTrans, FinancialYear, UserCredentials, EmployeeDetails, Designation, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, TaxHead, EmployeeCTC, EmpAlterationDetails, ReportCriterias, SalaryIncrement, SalaryIncrementDetails, ComponentIncrement, EditPunches`

This is the **pre-hire / onboarding-candidate parallel of `EmployeeController`**, operating on the `emp_join` table (via the `EmployeeJoin` model) instead of `emp_details`. It largely re-implements the same feature set as `EmployeeController` (setup, upload, CTC, increments, resume, promotions) plus an onboarding-specific workflow (`onboarding`, `allonboard`, `saveonboarding`, `sendOnboardingMail`, `calculateOnboardingPercentage`) and per-field validators (`checkPF`, `checkPan`, `checkESI`, `checkLWF`, `checkUAN`, `checkAccountNo`, `checkIdCard`) used during candidate data entry. Given the size (148 raw matches, ~40% of which are duplicate/dead — see below), the table below groups by purpose rather than listing all individually; every action is still HTTP GET/POST (no explicit method check found anywhere in this file except `saveToJoin` at :2583 which checks `is('post')`, and the four `updateOnboardData`/`saveAllOnboard`/`savedocument` actions at :8794/:8884/:9075/:9337 which also check `is('post')`).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | HTML view | Join/candidate landing view | Controller/EmployeeJoinController.php:13 |
| downloadMissingSalary | GET/POST | file download | Excel of candidates missing salary data | Controller/EmployeeJoinController.php:106 |
| thisMonthJoining | GET/POST | PDF download | PDF list of this-month joiners | Controller/EmployeeJoinController.php:191 |
| setupprofile, listimported, employeesunder, testfunction, checkProff, getstages, getautohierarchycompletions(vgfs), getautocompletions, listemployeesProfileImporteds | GET/POST | mixed HTML/JSON | Same purposes as identically-named `EmployeeController` actions, applied to join/candidate records | Controller/EmployeeJoinController.php:281-750 |
| listjoinedemployees (dead) | — | — | Commented-out old datagrid list | Controller/EmployeeJoinController.php:895 |
| listjoinedemployees (live) | GET/POST | JSON | Main candidate/onboarding datagrid list | Controller/EmployeeJoinController.php:981 |
| listemployees | GET/POST | JSON | Employee list (join-side) | Controller/EmployeeJoinController.php:1083 |
| listjoinedemployees (dead, 2nd copy) | — | — | Another commented duplicate | Controller/EmployeeJoinController.php:1255 |
| listemployees (dead) | — | — | Commented-out duplicate | Controller/EmployeeJoinController.php:1384 |
| jsons, jsonss, removeProfileImage, jsonsb | GET/POST | JSON | List/lookup/photo-removal helpers, same shape as EmployeeController equivalents | Controller/EmployeeJoinController.php:1550-1679 |
| setup | GET/POST | HTML view (die() present) | Candidate add/edit setup form | Controller/EmployeeJoinController.php:1681 |
| getGrade, showtaxheaddetail, loadEmpDetails, loadEmpProfDetails, Finyear, promotion, getautocompletions_superior, savepromotions, approvepromotion, empprofdetails, emptaxationdetails, uploadProfileImage | GET/POST | mixed | Same purposes as EmployeeController equivalents, candidate-side | Controller/EmployeeJoinController.php:1966-2581 |
| saveToJoin | POST only (`is('post')`) | JSON | Primary "create/update candidate" save handler | Controller/EmployeeJoinController.php:2583 |
| saveExperience, saveEducation (live), saveFam, saveDoc | GET/POST | JSON | Save sub-records (experience/education/family/document) for the candidate | Controller/EmployeeJoinController.php:2771-3089 |
| saveEducation (dead) | — | — | Commented-out duplicate | Controller/EmployeeJoinController.php:2924 |
| restsave, saveemployeesetup, addToNotice, jsons_getemps, saveconfigs, downloadempdataformat, mailsend, uploadandsaveempdetails, getcurrentemployeekey, showsalaryupload, downloadempctcformat | GET/POST | mixed | Same purposes as EmployeeController equivalents, candidate-side | Controller/EmployeeJoinController.php:3091-4454 |
| uploadandsaveempctc (dead) | — | — | Commented-out old CTC-upload handler | Controller/EmployeeJoinController.php:4456 |
| uploadandsaveempctc (live) | GET/POST | JSON | Bulk CTC upload for candidates | Controller/EmployeeJoinController.php:4664 |
| form, employeesave, employeelist, ctcupload, deleteEmployees | GET/POST | mixed | Same purposes as EmployeeController equivalents | Controller/EmployeeJoinController.php:4888-5266 |
| isLastAddedEmployee | GET/POST | JSON | Checks whether given record is the most-recently-added candidate | Controller/EmployeeJoinController.php:5268 |
| deleteEmp | GET/POST | JSON | Deletes a candidate record | Controller/EmployeeJoinController.php:5293 |
| activateEmp | GET/POST | JSON | Reactivates a candidate record | Controller/EmployeeJoinController.php:5317 |
| addqualification, addfamily, passport, savefamily, savepassport, savequalifications, history, savehistory, lstfamilies, listhistory, getusers, listqualifications, listpassports, savefile, deletequal, deletepassports, deletehist, deletefdetails, mark_nominee, mark_emergency | GET/POST | mixed | Sub-record CRUD identical in shape to EmployeeController equivalents | Controller/EmployeeJoinController.php:5344-5743 |
| importProfile | GET/POST | HTML view + JSON | Imports external profile data into a join/candidate record | Controller/EmployeeJoinController.php:5745 |
| requestPermissionAccess, getprofileinfo, getDataFromAPI, getUID, showimportresponse, sendpasswordemail | GET/POST | mixed | External-API integration helpers, candidate-side | Controller/EmployeeJoinController.php:5936-6332 |
| updateSalStructureDistributionFn (dead) | — | — | Commented-out | Controller/EmployeeJoinController.php:6548 |
| downloadResume | GET/POST | PDF download | Candidate resume PDF | Controller/EmployeeJoinController.php:6579 |
| incrimentdatapdf (dead) | — | — | Commented-out (whole block) | Controller/EmployeeJoinController.php:6950 |
| employeeincrement, changeIncrementType, loadcriteriaitems, listcriteriaitems, generatereport, generateemployeincrement, currentctctake, changeBranchAr | GET/POST | mixed | Increment/report actions, candidate-side | Controller/EmployeeJoinController.php:7088-7534 |
| confirmation | GET/POST | HTML view | Renders confirmation-of-employment form | Controller/EmployeeJoinController.php:7660 |
| saveConfirmation | GET/POST | JSON | Saves confirmation-of-employment record | Controller/EmployeeJoinController.php:7667 |
| salaryIncrementForm, getSalaryStructure, calcSalaryStructure, alterSalaryStructure, onEffectiveDateChange, saveIncrement, itemIncrementForm, saveItemIncrement | GET/POST | mixed | Same purposes as EmployeeController equivalents | Controller/EmployeeJoinController.php:7675-8194 |
| onboarding | GET/POST | HTML view (die() present) | Renders the onboarding checklist/wizard for a candidate | Controller/EmployeeJoinController.php:8231 |
| allonboard | GET/POST | HTML view (die() present) | Renders "all onboarding items" consolidated view | Controller/EmployeeJoinController.php:8484 |
| updateOnboardData (dead) | — | — | Commented-out old version | Controller/EmployeeJoinController.php:8794 |
| updateOnboardData (live) | POST only (`is('post')`) | JSON | Updates a single onboarding-checklist item | Controller/EmployeeJoinController.php:8884 |
| saveAllOnboard | POST only (`is('post')`) | JSON | Bulk-saves all onboarding checklist items | Controller/EmployeeJoinController.php:9075 |
| savedocument | POST only (`is('post')`) | JSON | Saves an onboarding document upload | Controller/EmployeeJoinController.php:9337 |
| saveconfig (live) | GET/POST | JSON | Saves onboarding config item | Controller/EmployeeJoinController.php:9404 |
| editconfig | GET/POST | JSON | Edits onboarding config item | Controller/EmployeeJoinController.php:9554 |
| saveconfig (dead) | — | — | Commented-out duplicate | Controller/EmployeeJoinController.php:9796 |
| sendOnboardingMail | GET/POST | JSON | Sends onboarding-invite/status email | Controller/EmployeeJoinController.php:9882 |
| calculateOnboardingPercentage | GET/POST | none (return value, helper) | Computes % completion of onboarding checklist | Controller/EmployeeJoinController.php:10235 |
| getOnboardingCompletion | GET/POST | JSON | Returns onboarding completion status | Controller/EmployeeJoinController.php:10391 |
| saveonboarding | GET/POST | JSON | Primary onboarding-save handler (large, 746 lines) | Controller/EmployeeJoinController.php:10622 |
| restsave | GET/POST | none | Reset/re-save helper | Controller/EmployeeJoinController.php:11370 |
| uploadandsaveempdetail (4 dead copies) | — | — | Four successive commented-out old implementations | Controller/EmployeeJoinController.php:11391, 11476, 11582, 11835 |
| uploadandsaveempdetail (live) | GET/POST | JSON | Converts an accepted candidate (`emp_join`) into a live employee record — **the actual "join → employee" promotion logic** (large, 414 lines) | Controller/EmployeeJoinController.php:12190 |
| deleteJoin | GET/POST | JSON | Deletes a candidate/join record | Controller/EmployeeJoinController.php:12606 |
| checkAccountNo, checkPF, checkIdCard (live) | GET/POST | JSON | Field-level format/duplicate validators used during candidate data entry | Controller/EmployeeJoinController.php:12627, 12672, 12717 |
| checkIdCard (dead) | — | — | Commented-out duplicate | Controller/EmployeeJoinController.php:12765 |
| checkPan (live), checkESI (live), checkLWF (live), checkUAN (live) | GET/POST | JSON | Same, PAN/ESI/LWF/UAN validators | Controller/EmployeeJoinController.php:12798, 12842, 12886, 12927 |
| checkPan, checkESI, checkLWF, checkUAN (all dead) | — | — | Commented-out duplicates of the four above | Controller/EmployeeJoinController.php:12975, 13015, 13049, 13080 |
| getEducationData, getExperienceData, getFamilyData, getDocumentData | GET/POST | JSON | "Check emp_join first, then employee_details" generic lookup helpers (comment at :13110) — used once a candidate has been promoted to employee | Controller/EmployeeJoinController.php:13111-13180 |
| Header, Footer (MYPDF class) | n/a | n/a | TCPDF overrides, not HTTP actions | Controller/EmployeeJoinController.php:13212, 13251 |

**Notes:** This controller is effectively a fork-and-extend of `EmployeeController.php` for the onboarding pipeline, with roughly 25% of its raw function matches being commented-out earlier revisions of the same method name — **flag every "(dead)" row above as legacy cruft, confirmed by reading the surrounding comment markers**, not to be ported. The live `uploadandsaveempdetail` (:12190) is the key "convert candidate to employee" business action and deserves careful review during migration since it likely writes across `emp_join`, `emp_details`, `emp_proff`, and `user_credentials` in one pass (consistent with the analogous logic in `EmployeeController::saveemployeesetup`, Controller/EmployeeController.php:3645-3768, which does the credential-provisioning + stored-routine call).

---

## 4. EmployeeRegisterController.php (`Controller/EmployeeRegisterController.php`, 2278 lines, 24 `public function` matches)

`$uses` (line 52): `UserCredentials, CompanyContactInfo, EditPunches, EmployeeDetails, DbConfig, EditPunchesHist, AttendanceRegister`

**Naming is misleading** — despite "Register" in the name, this controller is not employee self-registration; it is the **attendance/punch register** (raw in/out punches, amendments, sync from biometric devices). Not part of the "employee onboarding" surface.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | HTML view | Attendance register landing view for an employee | Controller/EmployeeRegisterController.php:60 |
| jsons | GET/POST | JSON (autoRender=false) | Employee list for register dropdown | Controller/EmployeeRegisterController.php:127 |
| indexload | GET/POST | HTML view | Loads register data into an existing view (ajax partial) | Controller/EmployeeRegisterController.php:206 |
| Iterateame | GET/POST | none (die() present) | Batch-iterates AME (attendance-monitoring-engine?) sync — dead-ends with `die()` | Controller/EmployeeRegisterController.php:234 |
| hierarchy | GET/POST | HTML view | Renders reporting hierarchy for register context | Controller/EmployeeRegisterController.php:260 |
| employeeeditpunch | GET/POST | none | Punch-edit entry point | Controller/EmployeeRegisterController.php:282 |
| listpunches (dead, block-commented) | — | — | Old implementation inside `/* ... */` | Controller/EmployeeRegisterController.php:297 |
| listpunches (live) | GET/POST | JSON (die() present) | Main punch-list datagrid | Controller/EmployeeRegisterController.php:346 |
| listpunchesnew | GET/POST | JSON (die() present) | Newer punch-list variant | Controller/EmployeeRegisterController.php:1094 |
| editpunch | GET/POST | HTML view | Edit-punch form for a date/employee/site | Controller/EmployeeRegisterController.php:1512 |
| listpunchesbydate | GET/POST | JSON | Punches filtered by date | Controller/EmployeeRegisterController.php:1582 |
| Updateame | GET/POST | JSON (die() present) | Pushes AME punch updates | Controller/EmployeeRegisterController.php:1661 |
| Updateamendmens | GET/POST | none (die() present) | Saves punch amendments | Controller/EmployeeRegisterController.php:1738 |
| form | GET/POST | HTML view | Add/edit punch popup form | Controller/EmployeeRegisterController.php:1793 |
| remove | GET/POST | JSON | Removes a punch record | Controller/EmployeeRegisterController.php:1862 |
| insert_func | GET/POST | none (internal helper) | Generic insert helper parameterized by table name — **note:** dynamic table-name helper, verify no injection path | Controller/EmployeeRegisterController.php:1879 |
| savenew | GET/POST | JSON | Saves a new punch entry | Controller/EmployeeRegisterController.php:1899 |
| savepunch (live) | GET/POST | JSON | Saves/updates a punch | Controller/EmployeeRegisterController.php:1997 |
| savepunch (dead) | — | — | Commented-out old `savepunch($empid=0)` | Controller/EmployeeRegisterController.php:2040 |
| getmonths | GET/POST | JSON | Returns month list for filters | Controller/EmployeeRegisterController.php:2109 |
| sendmemo | GET/POST | none | Sends a memo related to attendance discrepancy | Controller/EmployeeRegisterController.php:2123 |
| updateShiftDate | GET/POST | JSON | Updates shift date for punches | Controller/EmployeeRegisterController.php:2136 |
| Syncame (dead-leaning) | GET/POST | none (die() present) | AME sync helper — file ends immediately after, verify reachability | Controller/EmployeeRegisterController.php:2172 |
| SyncAttendance | GET/POST | JSON | Syncs attendance from device/API | Controller/EmployeeRegisterController.php:2231 |

---

## 5. EmployeeManageController.php (`Controller/EmployeeManageController.php`, 151 lines, 2 actions)

`$uses` (line 51): `Menu, LeavePolicyGroup, CentralControl, Banks, UserCredentials, EmployeeDetails, EmployeeProfessionalDetails, Departments, EmployeeGrossDetails, Verticals, Units, ReportCriterias, DayTimeProcedures, EmpCtcTransaction, LeaveRequests, Designation, DbConfig, ReportAudit, FinancialYear, EmployeeTaxsalsumNew, EmployeeTaxsalsum, Gender, Plan, Features, PlanFeature, CentralUserCredentials`

Small, recently-added controller (references `controldb` central plan/feature tables — plan-based feature gating).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | HTML view (empty body) | Placeholder landing view | Controller/EmployeeManageController.php:54 |
| getEmployeeFeatures | GET/POST | JSON | Returns the "employee" feature-set enabled for the tenant's subscription plan, with a hard-coded company-code exclusion list swapping certain feature paths (`UserAccess/indexnew`→`UserAccess`, `Asset/Create_asset_new`→`Asset/Create_asset`) | Controller/EmployeeManageController.php:58 |

**Note:** `getEmployeeFeatures` (line 58) reads plan/feature data from the central `controldb` datasource (`$this->Plan->setDataSource('controldb')`, line 62) — this is plan/subscription-gating logic, distinct from the tenant-DB pattern used elsewhere in the cluster. Relevant if the Next.js migration needs to replicate plan-based feature flags.

---

## 6. EmployeeConfigController.php (`Controller/EmployeeConfigController.php`, 5039 lines, 86 `public function` matches)

`$uses` (line 31): `CentralControl, SalaryStructures, DayTimeProcedures, UserCredentials, Grades, Section, EmployeeDetails, Division, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, EmployeeConfig, NoticePeriod, EmployeeSalaryStructure, EditPunches`

This controller is the **bulk assignment surface**: dual-list ("available" / "assigned") grids for wiring employees to master-data entities — shift, holiday calendar, leave policy, salary structure, org hierarchy, notice period, division, section, grade, leave hierarchy, multi-shift. The pattern repeats ~13 times: `listemployeesin<X>` (currently assigned), `listemployeesfor<X>` (available to assign), `addEmpTo<X>`, `removeEmpFrom<X>`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | JSON (autoRender=false) | Config landing / initial data | Controller/EmployeeConfigController.php:34 |
| addEmpToShift / removeEmpFromShift | GET/POST | JSON / none | Assign/unassign employee(s) to a shift | Controller/EmployeeConfigController.php:167, 302 |
| listemployeesinshift / listemployeesforshift | GET/POST | JSON | Grid data: assigned / assignable employees for shift | Controller/EmployeeConfigController.php:321, 409 |
| listemployeesinholiday / listemployeesforholiday | GET/POST | JSON | Same, for holiday calendar | Controller/EmployeeConfigController.php:516, 606 |
| addEmpToHoliday / removeEmpFromHoliday | GET/POST | none | Assign/unassign holiday calendar | Controller/EmployeeConfigController.php:697, 721 |
| setup | GET/POST | HTML view | Employee config setup form | Controller/EmployeeConfigController.php:746 |
| loadEmpDetails / loadEmpProfDetails | GET/POST | JSON | Helpers loading personal/professional details for setup | Controller/EmployeeConfigController.php:793, 815 |
| empprofdetails / emptaxationdetails | GET/POST | HTML view / empty | Sub-form renders (emptaxationdetails is a dead stub) | Controller/EmployeeConfigController.php:866, 919 |
| saveemployeesetup | GET/POST | JSON | Combined save (mirrors EmployeeController version) | Controller/EmployeeConfigController.php:921 |
| deleteEmployees | GET/POST | JSON | Soft-delete employees | Controller/EmployeeConfigController.php:1031 |
| downloadempdataformat / uploadandsaveempdetails | GET/POST | file / JSON | Bulk Excel template + bulk upload (mirrors EmployeeController) | Controller/EmployeeConfigController.php:1045, 1101 |
| getcurrentemployeekey | GET/POST | JSON | Current session employee pkey | Controller/EmployeeConfigController.php:1264 |
| addEmpToLeave / removeEmpFromLeave | GET/POST | none | Assign/unassign leave policy | Controller/EmployeeConfigController.php:1275, 1295 |
| listemployeesinleave / listemployeesforleave | GET/POST | JSON | Grid data for leave-policy assignment | Controller/EmployeeConfigController.php:1317, 1408 |
| listemployeesforconfig / listsalaryemployeesforconfig | GET/POST | JSON | Generic + salary-scoped employee lists for config screens | Controller/EmployeeConfigController.php:1499, 1550 |
| listemployeesforhierarchy | GET/POST | JSON | Employee list for hierarchy config | Controller/EmployeeConfigController.php:1583 |
| listemployeesinsalary / listemployeesforsalary | GET/POST | JSON | Grid data for salary-structure assignment | Controller/EmployeeConfigController.php:1680, 1766 |
| listemployeesinhierarchy | GET/POST | JSON | Grid data: employees placed in org hierarchy | Controller/EmployeeConfigController.php:1885 |
| addEmpToHierarchy / removeEmpFromHierarchy / reOrderEmps | GET/POST | none | Assign/unassign/reorder in org hierarchy | Controller/EmployeeConfigController.php:1982, 2053, 2017 |
| checkEmpSalaryStructure, checkEmpShift, checkEmpMultiShift, checkEmpLeavepolicy, checkEmpHoliday, checkEmphierarchy, checkEmpNoticedays, checkEmpDivision, checkEmpSection, checkEmpgrade, checkEmpleavehierarchy | GET/POST | JSON (all die() present) | Pre-assignment existence/conflict checks (e.g. "does this employee already have a salary structure") before allowing a new assignment | Controller/EmployeeConfigController.php:2078-2525 |
| addEmpToSallary / removeEmpFromSallary | GET/POST | none | Assign/unassign salary structure | Controller/EmployeeConfigController.php:2527, 2690 |
| setStructureDetValueFromRemarks | GET/POST | none | Sets a salary-structure detail value from a remarks field | Controller/EmployeeConfigController.php:2648 |
| listnoticemaster / form / savenoticeperiod | GET/POST | JSON / HTML / JSON | Notice-period master list, form, save | Controller/EmployeeConfigController.php:2718, 2752, 2753 |
| listemployeesforperiod / listemployeesinperiod | GET/POST | JSON | Grid data for notice-period assignment | Controller/EmployeeConfigController.php:2786, 2896 |
| addEmpToNotice / removeEmpFromNotice | GET/POST | none | Assign/unassign notice period | Controller/EmployeeConfigController.php:2980, 3003 |
| listdivmaster / listemployeesfordiv / listemployeesindiv / addEmpToDiv / removeEmpFromDiv | GET/POST | JSON / none | Division master + assignment grid pattern | Controller/EmployeeConfigController.php:3024-3269 |
| listsectionmaster / listemployeesforsection / listemployeesinsection / addEmpToSection / removeEmpFromSection | GET/POST | JSON / none | Section master + assignment grid pattern | Controller/EmployeeConfigController.php:3271-3516 |
| listgrademaster / listemployeesforgrade / listemployeesingrade / addEmpToGrade / removeEmpFromGrade | GET/POST | JSON / none | Grade master + assignment grid pattern | Controller/EmployeeConfigController.php:3518-3761 |
| listemployeesinleavehierarchy / addEmpToLeaveHierarchy / removeEmpFromLeaveHierarchy / listemployeesforleavehierarchy | GET/POST | JSON / none | Leave-hierarchy assignment grid pattern | Controller/EmployeeConfigController.php:3763-3897 |
| listemployeesforleaveconfig | GET/POST | JSON | Employee list for leave-policy config | Controller/EmployeeConfigController.php:4018 |
| listemployeesinmultishift / listemployeesformultishift | GET/POST | JSON | Grid data for multi-shift assignment | Controller/EmployeeConfigController.php:4071, 4172 |
| removeEmpFromMultiShift / addEmpToMultiShift | GET/POST | none / JSON | Assign/unassign multi-shift | Controller/EmployeeConfigController.php:4293, 4315 |
| listemployeesforpolicy | GET/POST | JSON | Employee list for a generic policy config | Controller/EmployeeConfigController.php:4371 |
| listshiftsforemployees (dead) | — | — | Commented-out old version | Controller/EmployeeConfigController.php:4423 |
| listshiftsforemployees (live) | GET/POST | JSON | Shift list for employee(s) | Controller/EmployeeConfigController.php:4481 |
| listshiftsinemployees (dead) | — | — | Commented-out old version | Controller/EmployeeConfigController.php:4540 |
| listshiftsinemployees (live) | GET/POST | JSON | Shifts currently assigned to employees | Controller/EmployeeConfigController.php:4574 |
| addShiftToEmp (dead) | — | — | Commented-out old version | Controller/EmployeeConfigController.php:4608 |
| addShiftToEmp (live) | GET/POST | JSON | Assigns a shift to employee(s) | Controller/EmployeeConfigController.php:4708 |
| removeShiftFromEmp (dead) | — | — | Commented-out old version | Controller/EmployeeConfigController.php:4848 |
| removeShiftFromEmp (live) | GET/POST | none | Removes a shift from employee(s) | Controller/EmployeeConfigController.php:4939 |

**Note:** the `checkEmp*` family (lines 2078-2525) is the closest thing to real "business rule" enforcement in the entire cluster — but it lives in the controller, not a model, and only prevents *duplicate config assignment* (e.g., can't assign two salary structures), not general data validity.

---

## 7. EmployeeMenuController.php (`Controller/EmployeeMenuController.php`, 306 lines, 8 `public function` matches — 4 live, rest commented alternates)

`$uses` (line 17): `Menu, EmployeeMenu, Useraccess, EmployeeDetails, Features, Plan, PlanFeature, CentralUserCredentials, EmployeeProfessionalDetails`

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | HTML view | Employee-workspace menu landing view | Controller/EmployeeMenuController.php:23 |
| addon | GET/POST | HTML view | Renders `addon.ctp` view for addon-feature access | Controller/EmployeeMenuController.php:27 |
| getDefaultMenus | GET/POST | JSON | Returns default menus allocated to the employee via `user_access` join (**scoped to the logged-in employee's own `emp_fkey`** — this is the one action in the cluster that does look like real per-user access enforcement, though it's a data-scoping join, not a deny-on-failure check) | Controller/EmployeeMenuController.php:36 |
| getDefaultMenus (dead, 2 variants) | — | — | Two earlier commented-out implementations using raw `query()` string interpolation with `$emp_fkey` — **note the raw-SQL variants show clearer SQL-injection shape** (`WHERE ua.user_fkey = '$emp_fkey'`) even though dead | Controller/EmployeeMenuController.php:84, 112 |
| getAddonMenus (dead) | — | — | Earlier commented-out implementation | Controller/EmployeeMenuController.php:138 |
| getAddonMenus (live) | GET/POST | JSON | Fetches addon/feature menus for the employee from central `controldb`, with company-code-based path substitution (feature_key 81 → `AttendanceRegisterNew/indexneww` unless company is in a hard-coded exclusion list) | Controller/EmployeeMenuController.php:197 |
| setFeatureSession | GET/POST | JSON | Writes/clears `current_feature_id` in session (feature-context switcher) | Controller/EmployeeMenuController.php:288 |

**Note:** `getDefaultMenus`/`getAddonMenus` use raw string-interpolated SQL via `$this->EmployeeDetails->query("... WHERE user_fkey = '$emp_fkey' ...")` (Controller/EmployeeMenuController.php:210-215) — `$emp_fkey` comes from session, not user input, so injection risk is low but the pattern is fragile; flag for parameterization in the rewrite anyway.

---

## 8. EmployeeHierarchyController.php (`Controller/EmployeeHierarchyController.php`, 199 lines, 5 actions)

`$uses` (line 29): `CentralControl, UserCredentials, EmployeeDetails, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, EmployeeStructure`

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| listemployees | GET/POST | JSON | Lists all active employees for hierarchy tree (has leftover `debug($arr_emp)` at line 42) | Controller/EmployeeHierarchyController.php:32 |
| listchildEmployee | GET/POST | JSON | Lists direct children of a given parent employee in `EmployeeStructure` | Controller/EmployeeHierarchyController.php:57 |
| listemployeesforhierarchy | GET/POST | JSON | Lists employees not yet placed under the given parent (candidates for hierarchy placement) | Controller/EmployeeHierarchyController.php:89 |
| add | GET/POST | none (autoRender=false) | Adds employee(s) as children of a parent in the hierarchy tree | Controller/EmployeeHierarchyController.php:126 |
| remove | GET/POST | none | Removes employee(s) from a parent in the hierarchy tree | Controller/EmployeeHierarchyController.php:143 |
| saveData | GET/POST | JSON | Recursively saves a full hierarchy tree (from a JS tree widget) via `saveHeirarchy` helper | Controller/EmployeeHierarchyController.php:158 |
| saveHeirarchy (helper, not a controller action per se but public) | GET/POST | none | Recursive helper called by `saveData` | Controller/EmployeeHierarchyController.php:174 |
| display_tree (helper) | GET/POST | HTML echo | Debug/print helper for rendering a tree recursively | Controller/EmployeeHierarchyController.php:185 |

**Note:** `listemployees` (line 32) leaves a `debug($arr_emp)` call in production code path (line 42) — minor cruft, verify removed/guarded before migration.

---

## 9. EmployeeUnderController.php (`Controller/EmployeeUnderController.php`, 590 lines, 11 actions)

`$uses` (line 49): `CentralControl, UserCredentials, EmployeeDetails, Designation, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, TaxHead, EmployeeCTC`

Renders/serves the "employees under me" reporting-line view for a manager.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | HTML view (explicit `$this->render('index')`) | Landing view listing active employee count + branch info for the logged-in manager | Controller/EmployeeUnderController.php:55 |
| getstages | GET/POST | HTML fragment (autoRender=false) | `<option>` HTML for branch-filtered employee dropdown | Controller/EmployeeUnderController.php:85 |
| getautocompletions | GET/POST | JSON | Autocomplete for employee-under search (branch + name filter) | Controller/EmployeeUnderController.php:114 |
| listemployees | GET/POST | JSON | Datagrid of employees under the manager's branch, filtered by employee/designation | Controller/EmployeeUnderController.php:144 |
| setup | GET/POST | HTML view | Add/edit setup form for an employee-under record | Controller/EmployeeUnderController.php:223 |
| showtaxheaddetail | GET/POST | HTML view | Renders tax-head sub-form | Controller/EmployeeUnderController.php:320 |
| loadEmpDetails / loadEmpProfDetails | GET/POST | view vars set | Helpers for `setup` (mirrors EmployeeController pattern) | Controller/EmployeeUnderController.php:336, 348 |
| empprofdetails | GET/POST | view vars set | Loads professional-profile combo data | Controller/EmployeeUnderController.php:395 |
| emptaxationdetails | GET/POST | empty | Dead stub | Controller/EmployeeUnderController.php:446 |
| saveemployeesetup | GET/POST | JSON (`return json_encode(...)`, not `echo`) | Combined save handler — **note: uses `return` instead of `echo`/`autoRender=false` for JSON output** (Controller/EmployeeUnderController.php:586), which in CakePHP means the JSON string is likely wrapped by the default view renderer rather than output raw — **possible response-format bug, verify actual output before porting** | Controller/EmployeeUnderController.php:449 |

---

## 10. EmployeeResignationController.php (`Controller/EmployeeResignationController.php`, 2884 lines, 28 actions)

`$uses` (line 52): `CentralControl, ResignationRequests, EmployeeConfig, qualifcations, EmpSalarySlip, Termination, LeaveEncashmentMaster, EmpTaxSalTrans, FinancialYear, UserCredentials, EmployeeDetails, Designation, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, TaxHead, EmployeeCTC`

Handles resignation requests, approval workflow, full-and-final settlement calculation, termination, and settlement-slip PDF/email generation. Note: `Controller/EmployeeResignationControllerBkup.php` (1017 lines) is a dated backup of this file — not traced further per instructions.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | HTML view | Resignation landing view for an employee | Controller/EmployeeResignationController.php:59 |
| getstages | GET/POST | none (autoRender=false) | Branch/stage dropdown helper | Controller/EmployeeResignationController.php:67 |
| getperiod | GET/POST | JSON | Returns notice-period info for an employee | Controller/EmployeeResignationController.php:93 |
| form | GET/POST | HTML view | Resignation request form | Controller/EmployeeResignationController.php:107 |
| save_heads | GET/POST | none (autoRender=false) | Saves resignation request record (large, 164 lines) | Controller/EmployeeResignationController.php:170 |
| approves | GET/POST | HTML view | Approves a resignation request (large, 471 lines — computes settlement figures inline) | Controller/EmployeeResignationController.php:336 |
| get_emp_settle_slip | GET/POST | none | Builds full-and-final settlement slip data | Controller/EmployeeResignationController.php:809 |
| get_common_codes | GET/POST | empty | Dead stub | Controller/EmployeeResignationController.php:843 |
| get_leave_encashment / get_leave_encashment_amt | GET/POST | none | Computes leave-encashment amount for settlement | Controller/EmployeeResignationController.php:845, 853 |
| get_complete | GET/POST | HTML view | Renders "resignation complete" confirmation (large, 226 lines) | Controller/EmployeeResignationController.php:862 |
| removeemps | GET/POST | none (autoRender=false) | Removes employee(s) from resignation list | Controller/EmployeeResignationController.php:1090 |
| Viewslip | GET/POST | HTML view (PDF-style view) | Displays settlement slip | Controller/EmployeeResignationController.php:1216 |
| downloads | GET/POST | file download + HTML view (mixed) | Downloads settlement documents | Controller/EmployeeResignationController.php:1440 |
| leaveencash | GET/POST | none (autoRender=false) | Processes leave encashment on resignation | Controller/EmployeeResignationController.php:1682 |
| details_res | GET/POST | JSON | Returns resignation-request details for an employee | Controller/EmployeeResignationController.php:1752 |
| getautocompletions | GET/POST | JSON | Autocomplete employee search | Controller/EmployeeResignationController.php:1776 |
| listemployees | GET/POST | JSON | Datagrid of resigned/resigning employees | Controller/EmployeeResignationController.php:1814 |
| DeleteEmployeeResignation | GET/POST | JSON (die() present) | Deletes a resignation request | Controller/EmployeeResignationController.php:1880 |
| workingattendnacedays | GET/POST | JSON | Computes working/attendance days for settlement calc | Controller/EmployeeResignationController.php:1909 |
| setup | GET/POST | HTML view (die() present) | Add/edit resignation setup form | Controller/EmployeeResignationController.php:1985 |
| Terminate | GET/POST | JSON | Terminates an employee (writes to `Termination` model) | Controller/EmployeeResignationController.php:2381 |
| get_emp_full_and_final_settle_slip | GET/POST | none | Builds F&F settlement slip data | Controller/EmployeeResignationController.php:2434 |
| get_emp_settle_slip_watermt | GET/POST | none (returns `$datas`) | Watermarked settlement-slip variant | Controller/EmployeeResignationController.php:2470 |
| paysalary | GET/POST | JSON | Marks final salary as paid | Controller/EmployeeResignationController.php:2486 |
| load_birthdays | GET/POST | JSON | Loads upcoming employee birthdays (unrelated utility living in this controller) | Controller/EmployeeResignationController.php:2508 |
| sendEmail | GET/POST | JSON | Sends settlement/resignation-related email | Controller/EmployeeResignationController.php:2600 |
| generateFinalSlipPDF | GET/POST | PDF download | Generates final settlement slip PDF | Controller/EmployeeResignationController.php:2666 |

**Note:** `arr_data['created_by']`/`['modified_by']` are set from `$this->Session->read('user_name')` (Controller/EmployeeResignationController.php:1713-1715) — one of the only audit-trail patterns observed in this cluster; worth preserving in the migrated schema.

---

## 11. NoticePeriodController.php (`Controller/NoticePeriodController.php`, 250 lines, 8 actions)

`$uses` (line 51): `UserCredentials, CompanyContactInfo, NoticePeriod, Holiday`

Simple master-data CRUD for notice-period definitions (days + description), with hand-rolled uniqueness checks (no model validation).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | HTML view | Notice-period landing view | Controller/NoticePeriodController.php:59 |
| listNotice | GET/POST | JSON | Paged datagrid list of active notice periods | Controller/NoticePeriodController.php:64 |
| form | GET/POST | HTML view | Add/edit form, populated via raw `query()` when editing | Controller/NoticePeriodController.php:89 |
| checknotice_periodexists | GET/POST | JSON | Checks duplicate `description` via raw SQL `count(*)` (**string-interpolated SQL**, Controller/NoticePeriodController.php:119) | Controller/NoticePeriodController.php:111 |
| checknotice_periodcodeexists | GET/POST | JSON | Checks duplicate `notice_days` via raw SQL `count(*)` (same injection pattern) | Controller/NoticePeriodController.php:129 |
| savenotice_period | GET/POST | JSON | Insert/update — re-validates both uniqueness checks inline, then either raw `UPDATE` SQL (string-interpolated, Controller/NoticePeriodController.php:190-193) or `$this->NoticePeriod->save($data)` for insert | Controller/NoticePeriodController.php:147 |
| deleteNotice | GET/POST | JSON | Soft-delete via raw `UPDATE ... SET status=0` SQL (string-interpolated `$notice_pkey`) | Controller/NoticePeriodController.php:205 |

**Business rules (control-layer, not model):** notice-period `description` must be unique among active rows; `notice_days` must be unique among active rows (both re-implemented per-call as raw SQL `SELECT count(*)`, not DB constraints or CakePHP validation) — Controller/NoticePeriodController.php:119, 137, 163-183.

---

## 12. EmployeeEmiController.php (`Controller/EmployeeEmiController.php`, 1130 lines, 13 actions)

`$uses` (line 52): `CentralControl, UserCredentials, EmployeeDetails, EmployeeLoanInfo, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, LoanEmi, EmployeeCTC, EmployeeLoan, SalarySlip`

Employee loan/EMI management: bulk EMI upload, balance tracking, salary-deduction check, EMI datagrid.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | HTML view (empty) | Landing view | Controller/EmployeeEmiController.php:55 |
| emi_upload | GET/POST | JSON | Handles EMI-schedule upload form submit | Controller/EmployeeEmiController.php:84 |
| getEmi | GET/POST | JSON | Fetches EMI record(s) for an employee/loan | Controller/EmployeeEmiController.php:183 |
| getEmployee | GET/POST | JSON | Employee list filtered by month (for EMI processing) | Controller/EmployeeEmiController.php:228 |
| getBalance | GET/POST | JSON | Returns outstanding loan balance | Controller/EmployeeEmiController.php:325 |
| checkmonth | GET/POST | JSON | Validates whether EMI already processed for a given from-month/loan | Controller/EmployeeEmiController.php:366 |
| employeeemiloansave | GET/POST | JSON | Saves a loan/EMI record for an employee | Controller/EmployeeEmiController.php:384 |
| jsons | GET/POST | JSON | Branch-filtered employee list | Controller/EmployeeEmiController.php:426 |
| salarycheck | GET/POST | JSON | Checks whether salary can support the EMI deduction | Controller/EmployeeEmiController.php:467 |
| employeelist | GET/POST | JSON | Main EMI datagrid list (large, 152 lines) | Controller/EmployeeEmiController.php:489 |
| downloademploanformat | GET/POST | file download (Excel via PHPExcel) | Streams bulk EMI-upload template | Controller/EmployeeEmiController.php:642 |
| uploadandsaveempemi | GET/POST | JSON | Parses uploaded Excel and bulk-saves EMI schedules (large, 231 lines) | Controller/EmployeeEmiController.php:856 |
| jsonsb | GET/POST | JSON | Branch-filtered list variant | Controller/EmployeeEmiController.php:1089 |

---

## 13. DocumentManagerController.php / DocumentManagersController.php

`$uses`: DocumentManagerController.php:57 → `DocTemplate, TemplatesDetails, Templates, Documents, EmpDetails, Units, EmployeeDetails, EmployeeExpenses, DocumentAllocation, DocumentUpload`; DocumentManagersController.php:57 adds `Country`.

**These two controllers are ~95% identical** (same 47/48 action names, same bodies, verified by diffing the comment-context extraction — DocumentManagersController.php additionally has `getEmployeesJoin` and references `Country`). Table below documents the action set once; line numbers given for `DocumentManagerController.php` with the `DocumentManagersController.php` line noted only where it differs meaningfully.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | empty (HTML view) | Landing view | Controller/DocumentManagerController.php:61 |
| form | GET/POST | HTML view | Document-template add/edit form | Controller/DocumentManagerController.php:68 |
| getTemplates | GET/POST | JSON | Paged datagrid of document templates | Controller/DocumentManagerController.php:89 |
| saveTemplate | GET/POST | JSON | Saves a document template | Controller/DocumentManagerController.php:123 |
| deleteTemplate | GET/POST | JSON | Deletes a document template | Controller/DocumentManagerController.php:188 |
| createDocument | GET/POST | HTML view | Renders create-document screen | Controller/DocumentManagerController.php:202 |
| addimage | GET/POST | HTML view | Renders add-image sub-screen for templates | Controller/DocumentManagerController.php:206 |
| deleteImage | GET/POST | JSON (`echo json_encode(1)`) | Deletes a template image | Controller/DocumentManagerController.php:213 |
| getDocuments | GET/POST | JSON | Paged datagrid of generated documents | Controller/DocumentManagerController.php:227 |
| documentForm | GET/POST | HTML view | Document creation form | Controller/DocumentManagerController.php:295 |
| getData | GET/POST | JSON | Fetches data for a document/template by id | Controller/DocumentManagerController.php:310 |
| docPreview | GET/POST | JSON (die() present) | Renders a document preview (PDF/HTML modes per param) — large, 459 lines | Controller/DocumentManagerController.php:331 |
| getPlaceholder | GET/POST | none | Resolves placeholder tokens for template rendering | Controller/DocumentManagerController.php:792 |
| saveDocument | GET/POST | JSON | Saves a generated document | Controller/DocumentManagerController.php:889 |
| deleteDocument | GET/POST | JSON | Deletes a generated document | Controller/DocumentManagerController.php:925 |
| getEmployees | GET/POST | none (helper, returns array) | Employee lookup helper for document placeholders | Controller/DocumentManagerController.php:939 |
| getEmployeesJoin (DocumentManagersController only) | GET/POST | none | Same, for join/candidate records | Controller/DocumentManagersController.php:1335 |
| getCompanies / getBraches / getContacts | GET/POST | none (helpers) | Lookup helpers for placeholders (company/branch/contact) | Controller/DocumentManagerController.php:965, 977, 989 |
| getTemplatePlaceholers | GET/POST | JSON | Returns placeholder tokens + template content for a template id | Controller/DocumentManagerController.php:1000 |
| savefile | GET/POST | JSON | Uploads/saves a document file | Controller/DocumentManagerController.php:1063 |
| documentMaster | GET/POST | HTML view | Document master list view | Controller/DocumentManagerController.php:1149 |
| uploadForm | GET/POST | HTML view (die() present) | Document upload form | Controller/DocumentManagerController.php:1156 |
| documentAllocate | GET/POST | HTML view | Allocate-document-to-employees screen | Controller/DocumentManagerController.php:1230 |
| save_allocate | GET/POST | JSON | Saves document allocation to selected employees | Controller/DocumentManagerController.php:1284 |
| documentUpload | GET/POST | JSON | Handles document upload submission | Controller/DocumentManagerController.php:1391 |
| getDocumentsFromDatabase | GET/POST | JSON | Datagrid of uploaded documents | Controller/DocumentManagerController.php:1458 |
| documentPreview | GET/POST | JSON | Preview of an uploaded document by file key | Controller/DocumentManagerController.php:1572 |
| openFileInNewTab | POST only (`is('post')`) | redirect | Opens an uploaded file in a new browser tab | Controller/DocumentManagerController.php:1610 |
| deleteDocumentFromGrid | GET/POST | JSON | Deletes a document row from the grid | Controller/DocumentManagerController.php:1634 |
| remove_allocate | GET/POST | JSON | Removes an employee's document allocation | Controller/DocumentManagerController.php:1670 |
| pdfWindow | GET/POST | empty | Dead stub / placeholder view | Controller/DocumentManagerController.php:1697 |
| viewDocument | GET/POST | none (autoRender=false) | Streams a document for viewing | Controller/DocumentManagerController.php:1699 |
| fetch_employee_details | GET/POST | JSON | Fetches employee details for document context | Controller/DocumentManagerController.php:1712 |
| listitems | GET/POST | HTML view | Lists items (context-dependent) | Controller/DocumentManagerController.php:1737 |
| template | GET/POST | HTML view | Renders a template picker | Controller/DocumentManagerController.php:1744 |
| save_template | GET/POST | JSON | Saves template metadata | Controller/DocumentManagerController.php:1759 |
| sendemailtemplate | GET/POST | HTML view (die() present) | Sends an email using a template (e.g. birthday) | Controller/DocumentManagerController.php:1795 |
| convertimage | GET/POST | HTML view | Converts/renders an image for a template | Controller/DocumentManagerController.php:1848 |
| renderImageTempalte / renderImageTempaltePrew | GET/POST | HTML view (image render) | Renders (and preview-renders) an image-based template merged with employee data | Controller/DocumentManagerController.php:1891, 1971 |
| saveImage | GET/POST | JSON | Saves a rendered template image | Controller/DocumentManagerController.php:2045 |
| templateform | GET/POST | HTML view | Birthday/image template add-edit form | Controller/DocumentManagerController.php:2101 |
| saveBirthdayTemplate | GET/POST | JSON | Saves birthday-template config | Controller/DocumentManagerController.php:2115 |
| getTerminations | GET/POST | none (helper) | Looks up termination dates for an employee (cross-reference into resignation cluster) | Controller/DocumentManagerController.php:2137 |
| printPreview | GET/POST | none (autoRender=false) | Print-preview rendering of a document | Controller/DocumentManagerController.php:2152 |
| getEmployeesforABS | GET/POST | none (helper) | Employee lookup scoped by branch code (for "ABS" — likely a company-specific view) | Controller/DocumentManagerController.php:2207 |

**Note:** confirm with the team which of `DocumentManagerController.php` / `DocumentManagersController.php` is the routed/live version before porting — this looks like an in-progress migration/fork within the legacy app itself (see cross-cutting note #6).

---

## 14. ContactsController.php (`Controller/ContactsController.php`, 145 lines, 4 actions)

`$uses` (line 38): `Contacts, Organization`

Simple master-data CRUD for customer/vendor contacts, scoped to a single `Organization` record (looked up via `find('first')`, not by any tenant filter beyond the per-session `useDbConfig`).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | HTML view (empty) | Contacts landing view | Controller/ContactsController.php:43 |
| addeditcontacts | GET/POST | HTML view | Add/edit contact form (`$contact_id=0` ⇒ add mode) | Controller/ContactsController.php:50 |
| save | GET/POST | JSON | Saves a contact, auto-attaching the tenant's single `organization_id` | Controller/ContactsController.php:69 |
| listcontacts | GET/POST | JSON | Paged/sortable/filterable datagrid; filter clauses built with raw string concatenation into `LIKE '%...%'` (**SQL injection risk** — `$contact`, `$relationship` come directly from request data into the conditions array as raw strings, Controller/ContactsController.php:103,106) | Controller/ContactsController.php:80 |
| deletecontacts | GET/POST | JSON | Soft-deletes a contact (`status=0`) | Controller/ContactsController.php:135 |

**Model:** `Contacts` (`Model/Contacts.php`) — `useTable='contacts'`, `primaryKey='contact_id'`, no validation, no associations.

---

## 15. BeneficiaryController.php (`Controller/BeneficiaryController.php`, 375 lines, 6 actions)

`$uses` (line 38): `Beneficiary, Organization`

Structurally identical to `ContactsController` (same code shape, `Contacts`→`Beneficiary` renamed), plus two extra bulk-Excel actions.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST | HTML view (empty) | Beneficiary landing view | Controller/BeneficiaryController.php:43 |
| addeditcontacts | GET/POST | HTML view | Add/edit beneficiary form | Controller/BeneficiaryController.php:50 |
| save | GET/POST | JSON | Saves a beneficiary, auto-attaching tenant's `organization_id` | Controller/BeneficiaryController.php:69 |
| listcontacts | GET/POST | JSON | Paged/filterable datagrid; same raw-string `LIKE` injection risk as ContactsController (lines 103,106) | Controller/BeneficiaryController.php:80 |
| deletecontacts | GET/POST | JSON | Soft-deletes a beneficiary | Controller/BeneficiaryController.php:134 |
| downloadempctcformat | GET/POST | file download (Excel via PHPExcel) | Streams a beneficiary-list Excel template (function name is a leftover copy-paste from CTC-upload code — **misnamed, verify before porting**) | Controller/BeneficiaryController.php:144 |
| uploadandsaveempctc | GET/POST | JSON | Parses uploaded beneficiary Excel and bulk-saves via `saveAll` — result-check bug: both success and "already exists" branches return `success=>1` inconsistently with the `$result1` check (Controller/BeneficiaryController.php:339-345); dead code present (commented-out `$empcsvdata`/field-name lookups, lines 283-286) | Controller/BeneficiaryController.php:227 |

**Model:** `Beneficiary` (`Model/Beneficiary.php`) — `useTable='beneficiary'`, `primaryKey='contact_id'`, no validation, no associations.

---

## 16. Model Validation & Business Rules Summary

As established in cross-cutting finding #2, **no model in this cluster defines `$validate`, `beforeSave()`, `beforeValidate()`, `afterSave()`, or an active association**. Full inventory of models used by this cluster's controllers, all confirmed empty of validation/business logic by direct read:

| Model | File | Table | Primary Key | Notes |
|---|---|---|---|---|
| EmployeeDetails | Model/EmployeeDetails.php | emp_details | emp_pkey | `virtualFields.emp_name` = concat(first,last); commented-out `hasOne EmployeeProfessionalDetails` (lines 22-26) |
| EmpDetails | Model/EmpDetails.php | emp_details | emp_pkey | Duplicate model pointing at same table as EmployeeDetails — verify which controllers use which name to avoid confusion during migration |
| EmployeeProfessionalDetails | Model/EmployeeProfessionalDetails.php | emp_proff | emp_proff_pkey | Commented-out `belongsTo EmployeeDetails` (lines 17-22) |
| EmployeeCTC | Model/EmployeeCTC.php | emp_ctc_upload | emp_ctc_upload_pkey | — |
| EmployeeJoin | Model/EmployeeJoin.php | emp_join | emp_join_pkey | `virtualFields.emp_name`; commented-out `hasOne EmployeeProfessionalDetails` |
| EmployeeStructure | Model/EmployeeStructure.php | emp_structure | (default `id`) | Backs the hierarchy tree (parent_id/emp_id pairs) |
| EmpFam | Model/EmpFam.php | family | emp_family_pkey | Commented-out `hasOne EmployeeProfessionalDetails` |
| Family | Model/Family.php | emp_family | emp_family_pkey | Distinct table from EmpFam despite similar purpose — verify which is authoritative |
| passport | Model/passport.php | emp_passport_visa | (default) | — |
| qualifcations | Model/qualifcations.php | qualifcations | (default) | Table name matches class name typo ("qualifcations") — must preserve or migrate carefully |
| history | Model/history.php | history | (default) | — |
| EmpDocument | Model/EmpDocument.php | emp_documents | (default) | — |
| ResignationRequests | Model/ResignationRequests.php | resignation_requests | Resignation_pkey | — |
| Termination | Model/Termination.php | termination | terminate_pkey | — |
| EmployeeLoanInfo | Model/EmployeeLoanInfo.php | emp_loan_info | emp_loan_info_pkey | — |
| LoanEmi | Model/LoanEmi.php | emi_upload | emi_upload_pkey | — |
| EmployeeLoan | Model/EmployeeLoan.php | emp_loan | emp_loan_pkey | — |
| Documents | Model/Documents.php | documents | document_pkey | — |
| DocumentUpload | Model/DocumentUpload.php | document_upload | (default) | `$order` = creation_date DESC |
| DocumentAllocation | Model/DocumentAllocation.php | document_allocation | (default) | `$order` = allocated_date DESC |
| NoticePeriod | Model/NoticePeriod.php | notice_period | notice_pkey | — |
| Contacts | Model/Contacts.php | contacts | contact_id | — |
| Beneficiary | Model/Beneficiary.php | beneficiary | contact_id | Shares primary-key name with Contacts despite different table |
| Organization | Model/Organization.php | organization_info | organization_id | Looked up via `find('first')` in Contacts/Beneficiary save — implies single-organization-per-tenant assumption |
| AppModel (base) | Model/AppModel.php | n/a | n/a | Empty passthrough constructor only — no shared validation/callbacks for any model in the app |

**Business rules that exist, all implemented at controller level (not model level), by source:**
- Notice-period `description` and `notice_days` uniqueness — hand-rolled `SELECT count(*)` before save/update, `Controller/NoticePeriodController.php:119,137,163-183`.
- Config-assignment conflict checks (`checkEmpSalaryStructure`, `checkEmpShift`, etc.) — prevent double-assigning an employee to certain master-data categories, `Controller/EmployeeConfigController.php:2078-2525`.
- Employee-ID auto-generation on first save (`company_code` + incrementing suffix, or `company_code + "1000"` for the first employee) — `Controller/EmployeeUnderController.php:348-394`, mirrored in `Controller/EmployeeController.php` setup flow.
- Auto-provisioning of `UserCredentials` row when a new `EmployeeDetails` record is created, with device-linked vs. manual `emp_id` branching based on `CentralControl.punch_type` — `Controller/EmployeeUnderController.php:449-588`, `Controller/EmployeeController.php:3645-3768`.
- Mandatory-field checks on bulk Excel imports (loops over a hard-coded `$array_mandatory_column_names` list and rejects the whole batch if any row is missing a required cell) — e.g. `Controller/BeneficiaryController.php:252-277`, same pattern in `EmployeeController::uploadandsaveempdetails`/`uploadandsaveempctc`.
- Field-format validators for onboarding (`checkPF`, `checkPan`, `checkESI`, `checkLWF`, `checkUAN`, `checkAccountNo`, `checkIdCard`) — `Controller/EmployeeJoinController.php:12627-12927`, each a standalone endpoint called from the client during form entry rather than enforced on save.

None of these are reusable/shared — each controller re-implements its own version inline, so the Next.js migration will need to consolidate them into a proper shared validation layer rather than porting them 1:1.

---

### 2.2 Attendance & Time

# Attendance & Time — Backend Technical Report

Scope: `Controller/*Attendance*`, `EditAttendance`, `EditPunches`, `Empeditpunches`, `DailyActivity`, `DailyOvertimeVerify(New)`, `OtAttendance(New)`, `Overtime`, `Regularisation`, `ShiftPlanner`, `ScheduledBreakOff`, `Compoff`, `HolidayCalendar`, `DayTimeProcedure`, `MobileLocationUpdate`, plus backing models.

All paths relative to `D:\Projects\RIZOMigration\legacy\`.

## 0. Cross-cutting findings

- **Auth**: confirmed only session-based `user_group` (1=admin/2=employee) gating via `AppController.php:39-46`. No controller in this cluster calls a per-action ACL/`user_access` check — grepped all 26 controllers for `user_access`/`menu_id`/`Auth->` patterns backing individual actions; none found. Access control is UI-only (menu visibility), matching the prior full-app finding. This means every action below is reachable by any authenticated session (admin or employee) unless the action itself reads `$this->Session->read('emp_fkey')`/`user_group` and branches logic (several do, e.g. `Compoff::index()` scopes to `emp_fkey`, but does not reject other roles).
- **Plan/feature gating hub confirmed**: `Controller/AttendanceSetupController.php:17-103` (`getAttendanceFeatures`) is a pure JSON endpoint (`autoRender=false`, `header('Content-Type: application/json')`) that reads `CentralUserCredentials.plan_id` (from `controldb` datasource) and cross-references `Features`/`PlanFeature` tables to build a feature list for the Attendance menu. It performs **only two** explicit legacy/new path swaps, both keyed on a hardcoded `$not_allowed_companies` array (company codes like `KWMT`,`ABSG`,`MBCT`, etc., line 66-69):
  - `Regularisation/adminindexnew` → `Regularisation/adminindex` (line 80-84)
  - `AttendanceRegisterNew/indexneww` → `AttendanceRegisterNew/index` (line 87-91)
  There is **no equivalent swap logic in code** for `DailyOvertimeVerifyNew` vs `DailyOvertimeVerify`, `OtAttendanceNew` vs `OtAttendance`, or `EditPunches` vs `EditAttendance` — the rest of the "New is canonical" designation from the prior pass is driven by the `Features.feature_path` **data row itself** (stored in the DB, not visible in this codebase), not by conditional code here. Treat the "New variant is canonical" claim as correct for Regularisation/AttendanceRegisterNew (code-verified) but **unverified from static code** for the other pairs — confirm via the `features`/`plan_feature` DB tables before assuming.
- **Business logic lives in stored procedures, not models.** Every model in this cluster (see §2) is a bare table-mapping class — none declare `$validate`, `beforeSave`, `beforeValidate`, `afterSave`, or `afterFind`. The two models with any custom methods (`AttendanceRegister`, `AttendanceRegisterReport`) only wrap raw `CALL <procname>(...)` SQL to MySQL stored procedures (`insert_update_att_reg`, `salary_process_prc`, `calculate_salary_main_prc`, `insert_update_att_reg_rep`). This means **all attendance-register business rules (leave/overtime/compoff computation, punch validation, status transitions) are implemented either inline in the controller PHP or inside DB stored procedures outside this codebase** — a major migration risk: those procedures must be located and ported/replicated, they are not visible via static PHP analysis.
- **Response pattern**: overwhelmingly `$this->autoRender = false;` + `echo json_encode(...)` for AJAX/data actions (hundreds of instances), with a handful of index/landing actions that `$this->set(...)` and render a `.ctp` view normally (no autoRender=false nearby). Very few actions do an explicit `$this->request->is('post')` check (see per-controller notes) — CakePHP does not restrict HTTP method by default, so most "endpoints" accept both GET and POST unless guarded.
- **Backup/duplicate files present, excluded from action inventory per instructions** (one-line note each):
  - `Controller/AttendanceControllerBkup-22-01.php` (1297 lines), `Controller/AttendanceController_bkup-28-10.php` (1184 lines) — stale copies of `AttendanceController.php`.
  - `Controller/AttendanceReportsController2018-010.php`, `AttendanceReportsControllerBkup-6-12.php`, `AttendanceReportsControllerBkup.php`, `AttendanceReportsController_Bkup-2018-01-07.php` — dated/backup duplicates of `AttendanceReportsController.php` (not itself in requested scope, noted for completeness); `AttendanceReportsNewController.php` also exists but is out of requested scope.
  - `Controller/EditPunchesController_bkup-24-10-2017.php` (515 lines) — stale copy of `EditPunchesController.php`.
  - `Controller/DayTimeProcedureController_nimisha_edited_backup.php`, `DayTimeProcedureControllerbackupnimishas.php` — stale copies of `DayTimeProcedureController.php`.
  - Site-attendance family (`SiteAttendanceApplyController.php`, `SiteAttendanceController.php`, `SiteAttendanceManageController.php`, `SiteAttendanceUploadController.php`, `SiteattendanceregisterController.php`) and `Model/SiteAttendance_bkup_megha.php`, `Model/SiteTransactions_bkup_megha.php` exist under `Controller/` but were **not** in the requested scope list — flagged only, not inventoried.

---

## 1. Controller Action Inventory

Legend: **method** = HTTP method actually enforced by code (`request->is()`/`REQUEST_METHOD`); "GET/POST (unrestricted)" = CakePHP default, no server-side method check found. **Response** = `JSON` (autoRender=false + json_encode/echo), `View` (renders a `.ctp`), `Redirect`, `Mixed` (branches between JSON and view/redirect), `Void/helper` (private helper, not directly routable — CakePHP `private function` prefixed with `_`/private keyword is not reachable as an action but is still listed since it's the actual report-generation logic).

### AttendanceSetupController.php (106 lines)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Landing page, sets branch list combo | `AttendanceSetupController.php:10` |
| getAttendanceFeatures | GET/POST (unrestricted) | JSON | Plan/feature gating hub — see §0 | `AttendanceSetupController.php:17` |

### AttendanceController.php (2750 lines) — legacy register controller, superseded by `AttendanceRegisterNewController` per feature-gate default
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| showregister | GET/POST (unrestricted) | View | Attendance register landing (self-service) | `AttendanceController.php:54` |
| showregisteradmin | GET/POST (unrestricted) | View | Attendance register landing (admin) | `AttendanceController.php:190` |
| showregistertab | GET/POST (unrestricted) | View | Register tab (verified/unverified) | `AttendanceController.php:297` |
| listregisterentries | GET/POST (unrestricted) | JSON | Grid data for unverified register entries | `AttendanceController.php:413` |
| listverifiedregisterentries | GET/POST (unrestricted) | JSON | Grid data for verified entries | `AttendanceController.php:575` |
| processregisterentries | GET/POST (unrestricted) | JSON | Bulk process register rows | `AttendanceController.php:666` |
| viewregisterentries | GET/POST (unrestricted) | Mixed | Detail view of a register row | `AttendanceController.php:731` |
| verifyregisterentries | GET/POST (unrestricted) | Mixed | Verify one register (~415 lines of business logic) | `AttendanceController.php:1173` |
| loadattendanceregisterheader | GET/POST (unrestricted) | JSON | Column header metadata | `AttendanceController.php:1588` |
| checkifregistercanverify | GET/POST (unrestricted) | JSON (return, not echo) | Pre-verify validation, returns json string | `AttendanceController.php:1619` |
| updateregisterentries | GET/POST (unrestricted) | Mixed | Update register row(s) | `AttendanceController.php:1666` |
| updateLeave | POST only (`request->is('post')` check line 1772) | JSON | Update a leave entry inline on register | `AttendanceController.php:1767` |
| submitregisterentry | GET/POST (unrestricted) | Mixed | Submit new register entry | `AttendanceController.php:1887` |
| createDateRange | n/a (helper, not JSON/view) | Void/helper | Date range util | `AttendanceController.php:2121` |
| AddLeave | GET/POST (unrestricted) | JSON | Add leave day to register | `AttendanceController.php:2137` |
| AddHalfLeave | GET/POST (unrestricted) | JSON | Add half-day leave | `AttendanceController.php:2228` |
| createDateRangeArray | n/a (helper) | Void/helper | Date range util | `AttendanceController.php:2294` |
| verifiedpdf | GET/POST (unrestricted) | Mixed (PDF stream) | Generates verified register PDF | `AttendanceController.php:2315` |
| removeAttendanceEntry | GET/POST (unrestricted) | JSON | Delete a register row | `AttendanceController.php:2589` |
| checkprocessingstatus | GET/POST (unrestricted) | JSON | Poll salary-processing status | `AttendanceController.php:2691` |
| checkprocessinglaststatus | GET/POST (unrestricted) | JSON | Poll last processing run status | `AttendanceController.php:2721` |

### AttendanceRegisterNewController.php (4259 lines) — canonical register controller (code-verified default per §0)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| indexneww | GET/POST (unrestricted) | View | Canonical register landing (new UI) | `AttendanceRegisterNewController.php:55` |
| registerbook | GET/POST (unrestricted) | View | Monthly register grid, `$isdelete`/`$skipProc` params drive stored-proc calls | `AttendanceRegisterNewController.php:212` |
| updateStatus | n/a (internal helper called by other actions) | Void/helper | Shared status-update logic | `AttendanceRegisterNewController.php:794` |
| chnagestatus | GET/POST (unrestricted) | Mixed | Change attendance status for a cell [sic, typo preserved from source] | `AttendanceRegisterNewController.php:1063` |
| removeAttendance | GET/POST (unrestricted) | JSON | Remove attendance row | `AttendanceRegisterNewController.php:1410` |
| verifyAttendance | **POST only** (`if (!$this->request->is('post'))` line 1565) | JSON | Bulk-verify selected employees' attendance | `AttendanceRegisterNewController.php:1560` |
| empregisterbook | GET/POST (unrestricted) | View | Employee-scoped register view | `AttendanceRegisterNewController.php:2164` |
| getbranches | GET/POST (unrestricted) | JSON | Branch combo data | `AttendanceRegisterNewController.php:2233` |
| jsons | GET/POST (unrestricted) | JSON | Employee list JSON for combo | `AttendanceRegisterNewController.php:2336` |
| filter | GET/POST (unrestricted) | Redirect (implied) | Filter form submit handler | `AttendanceRegisterNewController.php:2452` |
| index | GET/POST (unrestricted) | View | Legacy-path register landing (used when company in `not_allowed_companies`) | `AttendanceRegisterNewController.php:2488` |
| empindex | GET/POST (unrestricted) | View | Employee index landing | `AttendanceRegisterNewController.php:2529` |
| showregister | GET/POST (unrestricted) | View | Register view | `AttendanceRegisterNewController.php:2547` |
| showregistertab | GET/POST (unrestricted) | View | Register tab | `AttendanceRegisterNewController.php:2615` |
| listregisterentries | GET/POST (unrestricted) | JSON | Grid data | `AttendanceRegisterNewController.php:2688` |
| listverifiedregisterentries | GET/POST (unrestricted) | JSON | Grid data (verified) | `AttendanceRegisterNewController.php:2763` |
| processregisterentries | GET/POST (unrestricted) | JSON | Bulk process | `AttendanceRegisterNewController.php:2838` |
| verifyregisterentries | GET/POST (unrestricted) | JSON | Verify single register | `AttendanceRegisterNewController.php:2853` |
| loadattendanceregisterheader | GET/POST (unrestricted) | JSON | Header metadata | `AttendanceRegisterNewController.php:2889` |
| checkifregistercanverify | GET/POST (unrestricted) | JSON (return) | Pre-verify check | `AttendanceRegisterNewController.php:2920` |
| updateregisterentries | GET/POST (unrestricted) | JSON | Update rows | `AttendanceRegisterNewController.php:2963` |
| submitregisterentry | GET/POST (unrestricted) | JSON | Submit entry | `AttendanceRegisterNewController.php:2974` |
| createDateRange / createDateRangeArray | n/a (helpers) | Void/helper | Date utils | `AttendanceRegisterNewController.php:3025`, `:3041` |
| editPunch | GET/POST (unrestricted) | Mixed | Edit individual punch from register cell | `AttendanceRegisterNewController.php:3172` |
| bulkipdatestatus | GET/POST (unrestricted) | JSON | Bulk status update [sic typo] | `AttendanceRegisterNewController.php:3552` |
| checkLeaveExists | n/a (helper) | Void/helper | Leave-conflict check used by AddLeave | `AttendanceRegisterNewController.php:3959` |
| AddLeave | GET/POST (unrestricted) | JSON | Add leave (returns updates array) | `AttendanceRegisterNewController.php:4033` |
| checkprocessingstatus | GET/POST (unrestricted) | JSON | Poll processing status | `AttendanceRegisterNewController.php:4160` |
| markprocesscomplete | GET/POST (unrestricted) | JSON | Mark salary-processing run complete | `AttendanceRegisterNewController.php:4212` |
| isLeaveAlreadyApplied | n/a (private helper) | Void/helper | Leave-dup check | `AttendanceRegisterNewController.php:4234` |

### AttendanceregisterController.php (997 lines) — possible legacy cruft, near-duplicate of `AttendanceController`/`AttendanceRegisterNewController`
This controller is functionally identical (same action names/bodies pattern) to `AttendanceController.php`'s register* actions but under a different route (`/attendanceregister/...` lowercase). No inbound references found from other controllers via redirect; **flag entire controller as possible legacy cruft — verify live routing/menu entries before migrating**, since `AttendanceSetupController` gating never references `Attendanceregister/*` paths.
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| registerbook | GET/POST (unrestricted) | View | Monthly register grid | `AttendanceregisterController.php:54` |
| registerbookless | GET/POST (unrestricted) | View | Reduced-column register grid | `AttendanceregisterController.php:161` |
| empregisterbook | GET/POST (unrestricted) | View | Employee-scoped register | `AttendanceregisterController.php:244` |
| jsons | GET/POST (unrestricted) | JSON | Employee combo data | `AttendanceregisterController.php:311` |
| filter | GET/POST (unrestricted) | Redirect (implied) | Filter submit | `AttendanceregisterController.php:392` |
| index | GET/POST (unrestricted) | View | Landing | `AttendanceregisterController.php:427` |
| empindex | GET/POST (unrestricted) | View | Employee landing | `AttendanceregisterController.php:478` |
| showregister | GET/POST (unrestricted) | View | Register view | `AttendanceregisterController.php:495` |
| showregistertab | GET/POST (unrestricted) | View | Register tab | `AttendanceregisterController.php:559` |
| listregisterentries | GET/POST (unrestricted) | JSON | Grid data | `AttendanceregisterController.php:628` |
| listverifiedregisterentries | GET/POST (unrestricted) | JSON | Grid data (verified) | `AttendanceregisterController.php:702` |
| processregisterentries | GET/POST (unrestricted) | JSON | Bulk process | `AttendanceregisterController.php:776` |
| verifyregisterentries | GET/POST (unrestricted) | JSON | Verify | `AttendanceregisterController.php:797` |
| loadattendanceregisterheader | GET/POST (unrestricted) | JSON | Header metadata | `AttendanceregisterController.php:830` |
| checkifregistercanverify | GET/POST (unrestricted) | JSON (return) | Pre-verify check | `AttendanceregisterController.php:860` |
| updateregisterentries | GET/POST (unrestricted) | JSON | Update rows | `AttendanceregisterController.php:902` |
| submitregisterentry | GET/POST (unrestricted) | JSON | Submit entry | `AttendanceregisterController.php:912` |
| createDateRange / createDateRangeArray | n/a (helpers) | Void/helper | Date utils | `AttendanceregisterController.php:961`, `:976` |

### EditAttendanceController.php (1592 lines) — likely superseded by `EditPunchesController`
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Landing, `$emp_id` optional param | `EditAttendanceController.php:66` |
| hierarchy | GET/POST (unrestricted) | View | Manager-hierarchy punch view | `EditAttendanceController.php:81` |
| employeeeditpunch | GET/POST (unrestricted) | View | Self-service punch edit entry | `EditAttendanceController.php:93` |
| listpunches | GET/POST (unrestricted) | JSON | Grid data of punches for period | `EditAttendanceController.php:158` |
| AddLeave | GET/POST (unrestricted) | JSON (return) | Add leave from punch screen | `EditAttendanceController.php:436` |
| editpunch | GET/POST (unrestricted) | View/Mixed | Edit a single punch | `EditAttendanceController.php:508` |
| listpunchesbydate | GET/POST (unrestricted) | JSON | Punches for a date | `EditAttendanceController.php:552` |
| Updateame | GET/POST (unrestricted) | JSON | Update attendance-monitoring-entry (AME) record | `EditAttendanceController.php:620` |
| Updateamendmens | GET/POST (unrestricted) | JSON | Update amendments | `EditAttendanceController.php:662` |
| form | GET/POST (unrestricted) | View | Punch edit form | `EditAttendanceController.php:705` |
| remove | GET/POST (unrestricted) | JSON | Delete punch | `EditAttendanceController.php:775` |
| insert_func | n/a (helper) | Void/helper | Generic insert helper | `EditAttendanceController.php:793` |
| savenew | GET/POST (unrestricted) | JSON | Save new punch | `EditAttendanceController.php:814` |
| savepunch | GET/POST (unrestricted) | JSON | Save punch edit | `EditAttendanceController.php:921` |
| getmonths | GET/POST (unrestricted) | JSON (return) | Month combo data | `EditAttendanceController.php:1031` |
| chnagestatus_old | GET/POST (unrestricted) | JSON | Old status-change impl (dead code candidate) | `EditAttendanceController.php:1046` |
| chnagestatus | GET/POST (unrestricted) | JSON | Status change | `EditAttendanceController.php:1117` |
| chnagestatusadditonal | GET/POST (unrestricted) | JSON | Additional status change variant | `EditAttendanceController.php:1182` |
| checkLeaveExists | n/a (helper) | Void/helper | Leave conflict check | `EditAttendanceController.php:1208` |
| bulkipdatestatus | GET/POST (unrestricted) | JSON | Bulk status update | `EditAttendanceController.php:1282` |
| getstatus | GET/POST (unrestricted) | JSON (return, empty body — **dead/stub**) | Returns nothing meaningful | `EditAttendanceController.php:1334` |
| updateStatus | n/a (helper) | Void/helper | Shared update logic | `EditAttendanceController.php:1338` |
| sendmemo | GET/POST (unrestricted) | JSON | Send memo email (uses CakeEmail) | `EditAttendanceController.php:1349` |
| getmonthsList | GET/POST (unrestricted) | JSON | Month list | `EditAttendanceController.php:1363` |
| ealry_out_late_in | GET/POST (unrestricted) | JSON [sic typo] | Early-out/late-in report data | `EditAttendanceController.php:1509` |
| lesshoursave | GET/POST (unrestricted) | JSON | Save "less hours" adjustment | `EditAttendanceController.php:1562` |

`chnagestatus_old` (line 1046) is explicitly superseded by `chnagestatus` (line 1117) by naming convention — **possible legacy cruft, verify before migrating**. `getstatus` (line 1334) has no meaningful body — **dead/unreachable action, verify before migrating**.

### EditPunchesController.php (3632 lines) — canonical per prior-pass claim (unverified in this pass; see §0)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Landing, `$emp_id` optional | `EditPunchesController.php:113` |
| jsons | GET/POST (unrestricted) | JSON | Employee combo | `EditPunchesController.php:341` |
| indexload | GET/POST (unrestricted) | View | AJAX partial load of index | `EditPunchesController.php:441` |
| Iterateame | GET/POST (unrestricted) | Mixed | Iterate AME records | `EditPunchesController.php:479` |
| hierarchy | GET/POST (unrestricted) | View | Manager hierarchy punch view | `EditPunchesController.php:505` |
| employeeeditpunch | GET/POST (unrestricted) | View | Self-service entry | `EditPunchesController.php:528` |
| listpunches | GET/POST (unrestricted) | JSON | Grid data (large, ~950 lines of logic) | `EditPunchesController.php:1442` |
| listpunchesnew | GET/POST (unrestricted) | JSON | Newer variant of listpunches | `EditPunchesController.php:2394` |
| editpunch | GET/POST (unrestricted) | Mixed | Edit single punch | `EditPunchesController.php:2822` |
| listpunchesbydate | GET/POST (unrestricted) | JSON | Punches by date | `EditPunchesController.php:2901` |
| Updateame | GET/POST (unrestricted) | JSON | Update AME record | `EditPunchesController.php:2993` |
| Updateamendmens | GET/POST (unrestricted) | JSON | Update amendments | `EditPunchesController.php:3080` |
| form | GET/POST (unrestricted) | View | Punch edit form | `EditPunchesController.php:3144` |
| remove | GET/POST (unrestricted) | JSON | Delete punch | `EditPunchesController.php:3214` |
| insert_func | n/a (helper) | Void/helper | Generic insert | `EditPunchesController.php:3232` |
| savenew | GET/POST (unrestricted) | JSON | Save new punch | `EditPunchesController.php:3253` |
| savepunch | GET/POST (unrestricted) | JSON | Save punch edit | `EditPunchesController.php:3350` |
| getmonths | GET/POST (unrestricted) | JSON (return) | Month combo | `EditPunchesController.php:3460` |
| sendmemo | GET/POST (unrestricted) | JSON | Send memo | `EditPunchesController.php:3475` |
| updateShiftDate | GET/POST (unrestricted) | JSON | Update shift date on a punch | `EditPunchesController.php:3489` |
| Syncame | GET/POST (unrestricted) | JSON | Sync AME data | `EditPunchesController.php:3526` |
| SyncAttendance | GET/POST (unrestricted) | JSON | Sync attendance (device import trigger) | `EditPunchesController.php:3585` |

Note: `listpunches` (line 1442, ~950 lines) and `listpunchesnew` (line 2394, ~430 lines) coexist — naming suggests `listpunchesnew` is the newer variant; no in-code branch found selecting between them (both appear directly callable) — **verify which is actually wired to the current view/JS before migrating; likely the older one is legacy cruft**.

### EmpattendanceController.php (1211 lines) — employee self-service register variant
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| showregister | GET/POST (unrestricted) | View | Self-service register landing | `EmpattendanceController.php:53` |
| showregistertab | GET/POST (unrestricted) | View | Register tab | `EmpattendanceController.php:176` |
| listregisterentries | GET/POST (unrestricted) | JSON | Grid data | `EmpattendanceController.php:252` |
| listverifiedregisterentries | GET/POST (unrestricted) | JSON | Grid (verified) | `EmpattendanceController.php:389` |
| processregisterentries | GET/POST (unrestricted) | JSON | Bulk process | `EmpattendanceController.php:466` |
| verifyregisterentries | GET/POST (unrestricted) | JSON | Verify | `EmpattendanceController.php:480` |
| loadattendanceregisterheader | GET/POST (unrestricted) | JSON | Header metadata | `EmpattendanceController.php:638` |
| checkifregistercanverify | GET/POST (unrestricted) | JSON | Pre-verify check | `EmpattendanceController.php:668` |
| updateregisterentries | GET/POST (unrestricted) | JSON | Update rows | `EmpattendanceController.php:742` |
| submitregisterentry | GET/POST (unrestricted) | JSON | Submit entry | `EmpattendanceController.php:755` |
| createDateRange / createDateRangeArray | n/a | Void/helper | Date utils | `EmpattendanceController.php:833`, `:848` |
| verifiedpdf | GET/POST (unrestricted) | Mixed (PDF) | Verified register PDF | `EmpattendanceController.php:869` |
| Getbranchname | n/a (helper) | Void/helper | Branch name lookup | `EmpattendanceController.php:1145` |
| getcolor | n/a (helper) | Void/helper | UI color-coding helper for status | `EmpattendanceController.php:1155` |

### EmpattendanceregisterController.php (159 lines) — thin/likely legacy cruft
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| registerbook | GET/POST (unrestricted) | View | Register grid (minimal) | `EmpattendanceregisterController.php:55` |
| filter | GET/POST (unrestricted) | Redirect (implied) | Filter submit | `EmpattendanceregisterController.php:103` |
| index | GET/POST (unrestricted) | View | Landing | `EmpattendanceregisterController.php:143` |

Only 3 actions vs. 14+ in `EmployeeAttendanceregisterController.php` (near-identical name) — **possible legacy cruft, verify routing before migrating.**

### EmployeeAttendanceregisterController.php (763 lines)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| registerbook | GET/POST (unrestricted) | View | Register grid | `EmployeeAttendanceregisterController.php:54` |
| jsons | GET/POST (unrestricted) | JSON | Employee combo | `EmployeeAttendanceregisterController.php:116` |
| filter | GET/POST (unrestricted) | Redirect (implied) | Filter submit | `EmployeeAttendanceregisterController.php:183` |
| index | GET/POST (unrestricted) | View | Landing | `EmployeeAttendanceregisterController.php:221` |
| showregister | GET/POST (unrestricted) | View | Register view | `EmployeeAttendanceregisterController.php:261` |
| showregistertab | GET/POST (unrestricted) | View | Register tab | `EmployeeAttendanceregisterController.php:326` |
| listregisterentries | GET/POST (unrestricted) | JSON | Grid data | `EmployeeAttendanceregisterController.php:398` |
| listverifiedregisterentries | GET/POST (unrestricted) | JSON | Grid (verified) | `EmployeeAttendanceregisterController.php:472` |
| processregisterentries | GET/POST (unrestricted) | JSON | Bulk process | `EmployeeAttendanceregisterController.php:546` |
| verifyregisterentries | GET/POST (unrestricted) | JSON | Verify | `EmployeeAttendanceregisterController.php:560` |
| loadattendanceregisterheader | GET/POST (unrestricted) | JSON | Header metadata | `EmployeeAttendanceregisterController.php:592` |
| checkifregistercanverify | GET/POST (unrestricted) | JSON | Pre-verify check | `EmployeeAttendanceregisterController.php:622` |
| updateregisterentries | GET/POST (unrestricted) | JSON | Update rows | `EmployeeAttendanceregisterController.php:664` |
| submitregisterentry | GET/POST (unrestricted) | JSON | Submit entry | `EmployeeAttendanceregisterController.php:674` |
| createDateRange / createDateRangeArray | n/a | Void/helper | Date utils | `EmployeeAttendanceregisterController.php:724`, `:740` |

### EmpattendanceuploadController.php (529 lines) — likely superseded by `EmployeeAttendanceUploadController`
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Upload landing | `EmpattendanceuploadController.php:26` |
| getemployeenames | GET/POST (unrestricted) | JSON | Employee combo | `EmpattendanceuploadController.php:48` |
| form | GET/POST (unrestricted) | View | Upload form, `$emp_fkey` param | `EmpattendanceuploadController.php:77` |
| attendancesave | GET/POST (unrestricted) | JSON | Save uploaded row | `EmpattendanceuploadController.php:122` |
| load | GET/POST (unrestricted) | JSON | Load data | `EmpattendanceuploadController.php:164` |
| listattendance | GET/POST (unrestricted) | JSON | Grid of uploaded attendance | `EmpattendanceuploadController.php:185` |
| deleteattendance | GET/POST (unrestricted) | JSON | Delete uploaded row | `EmpattendanceuploadController.php:274` |
| downloadempctcformat | GET/POST (unrestricted) | Mixed (file download) | Downloads Excel template | `EmpattendanceuploadController.php:293` |
| uploadandsaveempctc | GET/POST (unrestricted) | JSON | Bulk import from uploaded Excel | `EmpattendanceuploadController.php:402` |

### EmpeditpunchesController.php (282 lines) — thin variant of EditPunches, likely legacy cruft
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Landing | `EmpeditpunchesController.php:58` |
| listpunches | GET/POST (unrestricted) | JSON | Grid data | `EmpeditpunchesController.php:73` |
| form | GET/POST (unrestricted) | View | Edit form | `EmpeditpunchesController.php:121` |
| remove | GET/POST (unrestricted) | JSON | Delete punch | `EmpeditpunchesController.php:126` |
| savenew | GET/POST (unrestricted) | JSON | Save new | `EmpeditpunchesController.php:142` |
| savepunch | GET/POST (unrestricted) | JSON | Save edit | `EmpeditpunchesController.php:166` |
| getmonths | GET/POST (unrestricted) | JSON (return) | Month combo | `EmpeditpunchesController.php:176` |
| listempemployees | GET/POST (unrestricted) | JSON | Employee combo | `EmpeditpunchesController.php:189` |

Much smaller feature set than `EditPunchesController.php`/`EditAttendanceController.php` — **possible legacy cruft, verify before migrating.**

### EmployeeAttendanceUploadController.php (1610 lines) — canonical upload controller per naming/breadth
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Upload landing | `EmployeeAttendanceUploadController.php:27` |
| employeefilter | GET/POST (unrestricted) | JSON | Employee filter by branch | `EmployeeAttendanceUploadController.php:76` |
| getemployeenames | GET/POST (unrestricted) | JSON | Employee combo | `EmployeeAttendanceUploadController.php:104` |
| form | GET/POST (unrestricted) | View | Upload/entry form | `EmployeeAttendanceUploadController.php:234` |
| checkleave | GET/POST (unrestricted) | JSON | Leave-conflict check for date range | `EmployeeAttendanceUploadController.php:348` |
| attendancesave | GET/POST (unrestricted) | JSON | Save entry | `EmployeeAttendanceUploadController.php:528` |
| load | GET/POST (unrestricted) | JSON | Load data | `EmployeeAttendanceUploadController.php:582` |
| listattendance | GET/POST (unrestricted) | JSON | Grid of uploads | `EmployeeAttendanceUploadController.php:603` |
| deleteattendance | GET/POST (unrestricted) | JSON | Delete row | `EmployeeAttendanceUploadController.php:719` |
| downloadempattendanceformat | GET/POST (unrestricted) | Mixed (file) | Excel template download | `EmployeeAttendanceUploadController.php:766` |
| downloadempctcformat | GET/POST (unrestricted) | Mixed (file) | CTC template download | `EmployeeAttendanceUploadController.php:1125` |
| uploadandsaveempctc | GET/POST (unrestricted) | JSON | Bulk import | `EmployeeAttendanceUploadController.php:1410` |

### DailyActivityController.php (1256 lines) — site/project daily-activity tracking (distinct domain from register attendance)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Landing | `DailyActivityController.php:53` |
| activity_form | GET/POST (unrestricted) | View | Activity edit form | `DailyActivityController.php:77` |
| activity_save | GET/POST (unrestricted) | JSON | Save activity | `DailyActivityController.php:96` |
| filtersite | GET/POST (unrestricted) | View/Mixed | Filter by site | `DailyActivityController.php:143` |
| delete | GET/POST (unrestricted) | JSON | Delete activity | `DailyActivityController.php:197` |
| checkprojectexists | GET/POST (unrestricted) | JSON | Duplicate-project check | `DailyActivityController.php:210` |
| form2 | GET/POST (unrestricted) | View | Secondary form | `DailyActivityController.php:230` |
| lists | GET/POST (unrestricted) | View/Mixed | List activities | `DailyActivityController.php:245` |
| save | GET/POST (unrestricted) | Mixed | Save (emp/site scoped) | `DailyActivityController.php:292` |
| addDefault | GET/POST (unrestricted) | JSON | Add default activity for employee | `DailyActivityController.php:348` |
| resetDefault | GET/POST (unrestricted) | JSON | Reset default | `DailyActivityController.php:365` |
| deletemens | GET/POST (unrestricted) | JSON | Delete assignment(s) | `DailyActivityController.php:383` |
| listuseraccess | GET/POST (unrestricted) | View | List `Useraccess` rows for site-attendance admin | `DailyActivityController.php:400` |
| Employee | GET/POST (unrestricted) | Mixed | Employee lookup | `DailyActivityController.php:464` |
| saveProject | GET/POST (unrestricted) | JSON | Save project | `DailyActivityController.php:495` |
| admin | GET/POST (unrestricted) | View | Admin landing | `DailyActivityController.php:536` |
| get | GET/POST (unrestricted) | JSON | Generic getter | `DailyActivityController.php:568` |
| deleteuser | GET/POST (unrestricted) | JSON | Delete `Useraccess` row | `DailyActivityController.php:589` |
| saveuseraccess | GET/POST (unrestricted) | JSON | Save/update site access grant | `DailyActivityController.php:600` |
| get_incompleteDate | GET/POST (unrestricted) | JSON (return) | Incomplete-shift-date lookup | `DailyActivityController.php:635` |
| get_shift | GET/POST (unrestricted) | JSON | Shift lookup | `DailyActivityController.php:686` |
| load_sites | GET/POST (unrestricted) | JSON | Site list for shift/date | `DailyActivityController.php:719` |
| add_site | GET/POST (unrestricted) | JSON | Add site to shift/date | `DailyActivityController.php:734` |
| shift_closure | GET/POST (unrestricted) | View | Shift closure landing | `DailyActivityController.php:773` |
| close_shift | GET/POST (unrestricted) | JSON (return) | Close a shift | `DailyActivityController.php:824` |
| data_site | GET/POST (unrestricted) | JSON | Site attendance data | `DailyActivityController.php:863` |
| mark_activestatus | GET/POST (unrestricted) | JSON | Mark employee active | `DailyActivityController.php:983` |
| mark_deactivestatus | GET/POST (unrestricted) | JSON | Mark employee inactive | `DailyActivityController.php:1010` |
| mark_attendance | GET/POST (unrestricted) | JSON | Mark site attendance for employee | `DailyActivityController.php:1039` |
| site_allocate | GET/POST (unrestricted) | View | Site allocation form | `DailyActivityController.php:1186` |
| save_allocate | GET/POST (unrestricted) | JSON | Save site allocation | `DailyActivityController.php:1205` |
| remove_allocate | GET/POST (unrestricted) | JSON | Remove allocation | `DailyActivityController.php:1230` |

Note: uses `Model/Useraccess.php` and `Model/Access_site.php`/`SiteHistory.php`/`SiteTransactions.php` — an in-app site-level access grant system (`user_access` table) distinct from the "no per-action ACL" auth model; this table gates *site data visibility inside DailyActivity*, not controller-action routing.

### DailyOvertimeVerifyController.php (1423 lines) — likely superseded by `DailyOvertimeVerifyNewController` (unverified in code; see §0)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Landing | `DailyOvertimeVerifyController.php:34` |
| listbreakoffdatesforsbo | GET/POST (unrestricted) | JSON | Scheduled break-off dates | `DailyOvertimeVerifyController.php:70` |
| listbreakoffdatesforsbo_verified | GET/POST (unrestricted) | JSON | Verified SBO dates | `DailyOvertimeVerifyController.php:126` |
| listpunches | GET/POST (unrestricted) | JSON | Punch grid (~420 lines) | `DailyOvertimeVerifyController.php:205` |
| listpunchesverify | GET/POST (unrestricted) | JSON | Verify-mode punch grid | `DailyOvertimeVerifyController.php:627` |
| listpunchesbydate | GET/POST (unrestricted) | JSON | Punches by date | `DailyOvertimeVerifyController.php:947` |
| editpunch | GET/POST (unrestricted) | Mixed | Edit punch | `DailyOvertimeVerifyController.php:1016` |
| form | GET/POST (unrestricted) | View | Edit form | `DailyOvertimeVerifyController.php:1062` |
| savenew | GET/POST (unrestricted) | JSON | Save new punch | `DailyOvertimeVerifyController.php:1134` |
| insert_func | n/a (helper) | Void/helper | Generic insert | `DailyOvertimeVerifyController.php:1232` |
| savepunch | GET/POST (unrestricted) | JSON | Save punch edit | `DailyOvertimeVerifyController.php:1253` |
| remove | GET/POST (unrestricted) | JSON | Delete punch | `DailyOvertimeVerifyController.php:1295` |
| verify | GET/POST (unrestricted) | JSON | Verify single | `DailyOvertimeVerifyController.php:1312` |
| verifyregisterentries | GET/POST (unrestricted) | JSON | Verify entries | `DailyOvertimeVerifyController.php:1330` |
| removeentries | GET/POST (unrestricted) | JSON | Remove entries | `DailyOvertimeVerifyController.php:1354` |
| updateSetDuration | GET/POST (unrestricted) | JSON | Update OT duration | `DailyOvertimeVerifyController.php:1384` |
| setRemarks | GET/POST (unrestricted) | JSON | Set remarks | `DailyOvertimeVerifyController.php:1404` |

### DailyOvertimeVerifyNewController.php (1924 lines)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Landing | `DailyOvertimeVerifyNewController.php:34` |
| listbreakoffdatesforsbo | GET/POST (unrestricted) | JSON | SBO dates | `DailyOvertimeVerifyNewController.php:72` |
| listbreakoffdatesforsbo_verified | GET/POST (unrestricted) | JSON | Verified SBO dates | `DailyOvertimeVerifyNewController.php:137` |
| listpunches | GET/POST (unrestricted) | JSON | Punch grid | `DailyOvertimeVerifyNewController.php:221` |
| listpunchesverify | GET/POST (unrestricted) | JSON | Verify-mode grid | `DailyOvertimeVerifyNewController.php:655` |
| listpunchesbydate | GET/POST (unrestricted) | JSON | Punches by date | `DailyOvertimeVerifyNewController.php:974` |
| editpunch | GET/POST (unrestricted) | Mixed | Edit punch | `DailyOvertimeVerifyNewController.php:1043` |
| form | GET/POST (unrestricted) | View | Edit form | `DailyOvertimeVerifyNewController.php:1100` |
| savenew | GET/POST (unrestricted) | JSON | Save new punch | `DailyOvertimeVerifyNewController.php:1169` |
| insert_func | n/a (helper) | Void/helper | Generic insert | `DailyOvertimeVerifyNewController.php:1271` |
| savepunch | GET/POST (unrestricted) | JSON | Save edit | `DailyOvertimeVerifyNewController.php:1292` |
| remove | GET/POST (unrestricted) | JSON | Delete punch | `DailyOvertimeVerifyNewController.php:1336` |
| verify | GET/POST (unrestricted) | JSON | Verify single | `DailyOvertimeVerifyNewController.php:1353` |
| verifyregisterentries | GET/POST (unrestricted) | JSON | Verify entries | `DailyOvertimeVerifyNewController.php:1377` |
| removeentries | GET/POST (unrestricted) | JSON | Remove entries | `DailyOvertimeVerifyNewController.php:1539` |
| updateSetDuration | GET/POST (unrestricted) | JSON | Update OT duration | `DailyOvertimeVerifyNewController.php:1630` |
| setRemarks | GET/POST (unrestricted) | JSON | Set remarks | `DailyOvertimeVerifyNewController.php:1797` |
| updateOvertime | n/a (helper, called internally) | Void/helper | OT update logic | `DailyOvertimeVerifyNewController.php:1824` |
| overtimeDataUpdate | GET/POST (unrestricted) | JSON | Update OT data | `DailyOvertimeVerifyNewController.php:1831` |
| getEmployeesByBranch | GET/POST (unrestricted) | JSON | Employee combo by branch | `DailyOvertimeVerifyNewController.php:1900` |

New adds 3 actions (`updateOvertime`, `overtimeDataUpdate`, `getEmployeesByBranch`) not present in the old controller — consistent with "New" being the actively developed variant, supporting (but not proving from code alone) the canonical-New claim.

### OtAttendanceController.php (804 lines) — likely superseded by `OtAttendanceNewController` (unverified; see §0)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| Register | GET/POST (unrestricted) | View | OT register landing | `OtAttendanceController.php:54` |
| Approved | GET/POST (unrestricted) | View | Approved OT list | `OtAttendanceController.php:239` |
| form | GET/POST (unrestricted) | View | OT entry form | `OtAttendanceController.php:331` |
| save | GET/POST (unrestricted) | Redirect (implied) | Save OT entry | `OtAttendanceController.php:344` |
| savedata | GET/POST (unrestricted) | JSON | Save OT data (ajax) | `OtAttendanceController.php:364` |
| Toapproved | GET/POST (unrestricted) | JSON | Move to approved | `OtAttendanceController.php:389` |
| index | GET/POST (unrestricted) | View | Landing | `OtAttendanceController.php:413` |
| subtable | GET/POST (unrestricted) | JSON (return) | Sub-table data | `OtAttendanceController.php:439` |
| subtablegenpdf | GET/POST (unrestricted) | Mixed (PDF) | Generate sub-table PDF | `OtAttendanceController.php:450` |
| approves | GET/POST (unrestricted) | JSON | Approve selected OT rows | `OtAttendanceController.php:477` |
| Setvalue | GET/POST (unrestricted) | JSON | Set OT value | `OtAttendanceController.php:499` |
| remarks | GET/POST (unrestricted) | JSON | Set remarks | `OtAttendanceController.php:512` |
| counting | GET/POST (unrestricted) | JSON (return) | Count OT records | `OtAttendanceController.php:524` |
| remove | GET/POST (unrestricted) | JSON | Delete OT record | `OtAttendanceController.php:535` |
| removenew | GET/POST (unrestricted) | JSON | Newer delete variant | `OtAttendanceController.php:583` |
| otprocess | GET/POST (unrestricted) | JSON | Run OT processing | `OtAttendanceController.php:626` |
| process | GET/POST (unrestricted) | JSON | OT processing (annotated "Edited by Akshay 26-7-2024") | `OtAttendanceController.php:729` |

`remove` (535) vs `removenew` (583) — naming suggests `removenew` supersedes `remove`; **verify which is wired before migrating, flag `remove` as possible legacy cruft.**

### OtAttendanceNewController.php (2969 lines) — canonical per naming/breadth and recent edit comments
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| Register | GET/POST (unrestricted) | View | OT register landing | `OtAttendanceNewController.php:54` |
| Approved | GET/POST (unrestricted) | View | Approved OT list | `OtAttendanceNewController.php:206` |
| form | GET/POST (unrestricted) | View | OT entry form | `OtAttendanceNewController.php:295` |
| save | GET/POST (unrestricted) | Redirect (implied) | Save entry | `OtAttendanceNewController.php:308` |
| savedata | GET/POST (unrestricted) | JSON | Save OT data | `OtAttendanceNewController.php:328` |
| Toapproved | GET/POST (unrestricted) | JSON | Move to approved | `OtAttendanceNewController.php:353` |
| index | GET/POST (unrestricted) | View | Landing | `OtAttendanceNewController.php:377` |
| subtable | GET/POST (unrestricted) | JSON (return) | Sub-table data | `OtAttendanceNewController.php:404` |
| subtablegenpdf | GET/POST (unrestricted) | Mixed (PDF) | Sub-table PDF | `OtAttendanceNewController.php:693` |
| approves | GET/POST (unrestricted) | JSON | Approve rows (param-free variant vs old) | `OtAttendanceNewController.php:1010` |
| Setvalue | GET/POST (unrestricted) | JSON | Set OT value | `OtAttendanceNewController.php:1094` |
| remarks | GET/POST (unrestricted) | JSON | Set remarks | `OtAttendanceNewController.php:1107` |
| counting | GET/POST (unrestricted) | JSON (return) | Count records | `OtAttendanceNewController.php:1119` |
| remove | GET/POST (unrestricted) | JSON | Delete record | `OtAttendanceNewController.php:1130` |
| removenew | GET/POST (unrestricted) | JSON | Newer delete variant | `OtAttendanceNewController.php:1209` |
| otprocess | GET/POST (unrestricted) | JSON | Run OT processing | `OtAttendanceNewController.php:1252` |
| process | GET/POST (unrestricted) | JSON | OT processing | `OtAttendanceNewController.php:1355` |
| getDurationRegister | GET/POST (unrestricted) | JSON (large, ~835 lines) | Duration register data | `OtAttendanceNewController.php:1432` |
| getNotApprovedData | GET/POST (unrestricted) | JSON (large, ~300 lines) | Not-approved OT data | `OtAttendanceNewController.php:2267` |
| getApprovedData | GET/POST (unrestricted) | JSON (large, ~180 lines) | Approved OT data | `OtAttendanceNewController.php:2563` |
| getProcessedData | GET/POST (unrestricted) | JSON | Processed OT data | `OtAttendanceNewController.php:2740` |
| jsons | GET/POST (unrestricted) | JSON | Employee combo | `OtAttendanceNewController.php:2881` |

New controller adds 5 substantial data-fetch actions (`getDurationRegister`, `getNotApprovedData`, `getApprovedData`, `getProcessedData`, `jsons`) absent from the old one — strong evidence of active development on "New," consistent with prior-pass claim, though the actual selection-by-company logic is DB-driven (see §0).

### OvertimeController.php (2657 lines) — OT reporting (distinct from OtAttendance* register controllers)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| hrreports | GET/POST (unrestricted) | View | Reports landing | `OvertimeController.php:60` |
| changereporttype | GET/POST (unrestricted) | JSON/View mixed (sets vars, no autoRender=false — actually renders `.ctp` partial) | Switch report-type criteria UI | `OvertimeController.php:74` |
| addreportcriteria | GET/POST (unrestricted) | View | Add a filter criterion row | `OvertimeController.php:107` |
| loadcriteriaitems | GET/POST (unrestricted) | View | Load criteria items combo | `OvertimeController.php:132` |
| listcriteriaitems | GET/POST (unrestricted) | JSON | Criteria items JSON (~310 lines) | `OvertimeController.php:155` |
| reportAudit | GET/POST (unrestricted) | Mixed | Log/query report-generation audit trail | `OvertimeController.php:468` |
| downloadHistory | GET/POST (unrestricted) | Mixed (file) | Download prior report run | `OvertimeController.php:534` |
| generatereport | GET/POST (unrestricted) | JSON | Dispatches to one of the `generate*` report builders below | `OvertimeController.php:643` |
| listemployeefields | GET/POST (unrestricted) | JSON | Employee field list for report columns | `OvertimeController.php:664` |
| _modelExists | n/a (private helper) | Void/helper | Runtime model-existence check | `OvertimeController.php:698` |
| generateOvertimeSynthietreport | n/a (dispatched, not routable directly — called from generatereport) | Mixed (Excel export, ~440 lines) | OT synthesis report [sic typo] | `OvertimeController.php:704` |
| generateOtSynthietreport | n/a (dispatched) | Mixed (Excel export, ~490 lines) | OT synthesis report v2 | `OvertimeController.php:1682` |
| generateOtMonthwiseReport | n/a (dispatched) | Mixed (Excel export) | Monthwise OT report | `OvertimeController.php:2173` |

Report generation follows the same "dispatch hub + private generator methods" pattern seen in `AttendanceCheckInOutController` below — the `generate*` methods are `public` (routable in CakePHP by default unless prefixed `_`) but are only intended to be called internally from `generatereport`; treat as **not directly user-navigable but technically reachable via URL** — a minor security note (no auth/param validation guarantees if hit directly).

### AttendanceCheckInOutController.php (6679 lines) — largest controller in cluster; HR reports engine
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| hrreports | GET/POST (unrestricted) | View | Reports landing, sets `arr_reporttypes` combo | `AttendanceCheckInOutController.php:79` |
| changereporttype | GET/POST (unrestricted) | View (autoRender=false but `$this->set()` + implicit render off — actually AJAX partial) | Switch criteria UI per report type | `AttendanceCheckInOutController.php:104` |
| addreportcriteria | GET/POST (unrestricted) | View | Add criterion row | `AttendanceCheckInOutController.php:191` |
| month | GET/POST (unrestricted) | View | Month picker partial | `AttendanceCheckInOutController.php:208` |
| loadcriteriaitems | GET/POST (unrestricted) | View | Load criteria combo | `AttendanceCheckInOutController.php:223` |
| listcriteriaitems | GET/POST (unrestricted) | JSON | Criteria items (~150 lines) | `AttendanceCheckInOutController.php:242` |
| reportAudit | GET/POST (unrestricted) | Mixed | Audit trail read/query | `AttendanceCheckInOutController.php:389` |
| generatereport | GET/POST (unrestricted) | JSON | Dispatch hub — routes to one of 10 `generate*` report builders below by `$type` | `AttendanceCheckInOutController.php:482` |
| _modelExists | n/a (private helper) | Void/helper | Model-existence check | `AttendanceCheckInOutController.php:546` |
| generatebreakreport | n/a (private, dispatched only) | Mixed (Excel, ~440 lines) | Break report | `AttendanceCheckInOutController.php:551` |
| generateearlyinreport | n/a (private, dispatched only) | Mixed (Excel, ~370 lines) | Early-in report | `AttendanceCheckInOutController.php:995` |
| generateearlyoutreport | n/a (private, dispatched only) | Mixed (Excel, ~155 lines) | Early-out report | `AttendanceCheckInOutController.php:1363` |
| generatelateinreport | n/a (private, dispatched only) | Mixed (Excel, ~380 lines) | Late-in report | `AttendanceCheckInOutController.php:1799` |
| generatelateoutereport | n/a (private, dispatched only) | Mixed (Excel, ~410 lines) | Late-out report [sic typo] | `AttendanceCheckInOutController.php:2181` |
| generatestatusreport | n/a (private, dispatched only) | Mixed (Excel, ~440 lines) | Attendance-status report | `AttendanceCheckInOutController.php:2588` |
| generatemisspunchreport | n/a (private, dispatched only) | Mixed (Excel, ~290 lines) | Miss-punch report | `AttendanceCheckInOutController.php:3029` |
| generateearlyindurationreport | n/a (private, dispatched only) | Mixed (Excel, ~310 lines) | Early-in duration report | `AttendanceCheckInOutController.php:3319` |
| generateearlyoutdurationreport | n/a (private, dispatched only) | Mixed (Excel, ~305 lines) | Early-out duration report | `AttendanceCheckInOutController.php:3632` |
| generatelateoutdurationreport | n/a (private, dispatched only) | Mixed (Excel, ~305 lines) | Late-out duration report | `AttendanceCheckInOutController.php:3939` |
| generatelateindurationreport | n/a (private, dispatched only) | Mixed (Excel, ~310 lines) | Late-in duration report | `AttendanceCheckInOutController.php:4246` |
| generatDailyAttendanceReport | n/a (private, dispatched only) | Mixed (Excel, ~480 lines) | Daily attendance report [sic typo] | `AttendanceCheckInOutController.php:4558` |
| generatDailyAttendanceReportEXTR | n/a (private, dispatched only, ~1600 lines to EOF) | Mixed (Excel) | Extended daily attendance report variant | `AttendanceCheckInOutController.php:5042` |

All `generate*` methods are `private` (verified keyword at each declaration) — genuinely unreachable except via `generatereport`'s internal dispatch, unlike `OvertimeController`'s public equivalents. This is the single largest file in the cluster (6679 lines) — almost entirely Excel-report-building logic with heavy inline SQL; migrating this will be the highest-effort item in the cluster.

### RegularisationController.php (2963 lines)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Self-service landing, `$emp_id` param | `RegularisationController.php:60` |
| hierarchyindex | GET/POST (unrestricted) | View | Manager-hierarchy landing | `RegularisationController.php:164` |
| adminindex | GET/POST (unrestricted) | View | Admin landing (legacy path per feature-gate swap) | `RegularisationController.php:217` |
| adminindexnew | GET/POST (unrestricted) | View | Admin landing (canonical per feature-gate, code-verified §0) | `RegularisationController.php:266` |
| hierarchybulkindex | GET/POST (unrestricted) | View | Bulk hierarchy landing | `RegularisationController.php:368` |
| hierarchy | GET/POST (unrestricted) | View | Hierarchy view | `RegularisationController.php:405` |
| employeeeditpunch | GET/POST (unrestricted) | View | Self-service entry | `RegularisationController.php:417` |
| listpunches | GET/POST (unrestricted) | JSON (~565 lines) | Punch grid, `$hierarchy` flag toggles scope | `RegularisationController.php:432` |
| listregularization | GET/POST (unrestricted) | JSON | Regularization list | `RegularisationController.php:998` |
| listadminregularizationnew | GET/POST (unrestricted) | JSON | Admin list (new) | `RegularisationController.php:1071` |
| listadminregularization | GET/POST (unrestricted) | JSON | Admin list (legacy) | `RegularisationController.php:1236` |
| listhierarchyregularization | GET/POST (unrestricted) | JSON | Hierarchy-scoped list | `RegularisationController.php:1336` |
| editpunch | GET/POST (unrestricted) | Mixed | Edit punch | `RegularisationController.php:1452` |
| viewattendancedetails | GET/POST (unrestricted) | Mixed | View punch/attendance detail | `RegularisationController.php:1498` |
| listpunchesbydate | GET/POST (unrestricted) | JSON | Punches by date, `$shownewbutton` flag | `RegularisationController.php:1546` |
| Updateame | GET/POST (unrestricted) | JSON | Update AME record | `RegularisationController.php:1704` |
| Updateamendmens | GET/POST (unrestricted) | JSON | Update amendments | `RegularisationController.php:1831` |
| form | GET/POST (unrestricted) | View | Edit form | `RegularisationController.php:1868` |
| bulkform | GET/POST (unrestricted) | View | Bulk edit form | `RegularisationController.php:1950` |
| bulkapprove | GET/POST (unrestricted) | JSON | Bulk approve | `RegularisationController.php:1987` |
| bulkadminapprove | GET/POST (unrestricted) | JSON | Bulk admin approve | `RegularisationController.php:2012` |
| bulkhierarchyapprove | GET/POST (unrestricted) | JSON | Bulk hierarchy approve | `RegularisationController.php:2038` |
| bulkupdate_self | GET/POST (unrestricted) | JSON | Bulk self-update | `RegularisationController.php:2064` |
| bulkupdate | GET/POST (unrestricted) | JSON (~285 lines) | Bulk update, `$adminUpdate` flag | `RegularisationController.php:2197` |
| bulksavenew | GET/POST (unrestricted) | JSON (~130 lines) | Bulk save new regularization requests | `RegularisationController.php:2483` |
| remove | GET/POST (unrestricted) | JSON | Delete | `RegularisationController.php:2614` |
| insert_func | n/a (helper) | Void/helper | Generic insert | `RegularisationController.php:2632` |
| savenew | GET/POST (unrestricted) | JSON | Save new | `RegularisationController.php:2653` |
| savepunch | GET/POST (unrestricted) | JSON | Save punch edit | `RegularisationController.php:2751` |
| getmonths | GET/POST (unrestricted) | JSON (return) | Month combo | `RegularisationController.php:2861` |
| sendmemo | GET/POST (unrestricted) | JSON | Send memo email | `RegularisationController.php:2876` |
| updateStatus | GET/POST (unrestricted) | JSON | Update status | `RegularisationController.php:2890` |
| getEmployeesByBranch | GET/POST (unrestricted) | JSON | Employee combo by branch | `RegularisationController.php:2906` |

This is the one controller where the prior-pass "New is canonical" claim is **code-verified**: `AttendanceSetupController.php:80-84` explicitly swaps `Regularisation/adminindexnew` → `Regularisation/adminindex` for companies in the `not_allowed_companies` blocklist, confirming `adminindexnew` (line 266) is the default/canonical action and `adminindex` (line 217) is the fallback for a specific blocklist of companies — not simply "legacy," it's an active company-specific code path.

### ShiftPlannerController.php (258 lines)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Shift planner landing | `ShiftPlannerController.php:33` |
| listemployees | GET/POST (unrestricted) | JSON | Employee list for planner grid | `ShiftPlannerController.php:92` |
| saveRoster | **POST only** (`if ($this->request->is('post'))` line 186; else returns JSON error at 192) | JSON | Save shift roster assignments | `ShiftPlannerController.php:181` |

Small, cleanly-structured controller (only 3 actions) — good migration candidate to port first as a template.

### ScheduledBreakOffController.php (600 lines)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Landing | `ScheduledBreakOffController.php:34` |
| listbreakoffdatesforsbospecial | GET/POST (unrestricted) | JSON | Special SBO dates | `ScheduledBreakOffController.php:38` |
| listbreakoffdatesforsbospecialall | GET/POST (unrestricted) | JSON | All special SBO dates | `ScheduledBreakOffController.php:53` |
| listbreakoffdatesforsbo | GET/POST (unrestricted) | JSON | SBO dates | `ScheduledBreakOffController.php:68` |
| addEmpToSBO | GET/POST (unrestricted) | JSON (~135 lines) | Add employee to scheduled break-off | `ScheduledBreakOffController.php:121` |
| removeEmpFromSBO | GET/POST (unrestricted) | JSON | Remove employee from SBO | `ScheduledBreakOffController.php:257` |
| listemployeesinsbodate | GET/POST (unrestricted) | JSON | Employees on SBO for a date/type | `ScheduledBreakOffController.php:344` |
| listemployeesforsbodate | GET/POST (unrestricted) | JSON | Eligible employees for SBO date | `ScheduledBreakOffController.php:416` |
| listemployeesforsbodateone | GET/POST (unrestricted) | JSON | Single-date variant | `ScheduledBreakOffController.php:508` |

### CompoffController.php (84 lines)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Comp-off summary/eligibility for logged-in employee (`Session->read('emp_fkey')`), raw SQL against `attendance_register`/`fin_year`/`emp_detail_timeattandance` | `CompoffController.php:52` |

Single-action controller; entirely raw SQL (`LeaveRequests->query(...)`) with no model validation layer — direct migration risk since the query embeds a 32-column `FIELDx='COFF'` OR-chain against `attendance_register` (this schema detail — 32 day-columns per month row — matters for the Next.js DB layer design).

### HolidayCalendarController.php (463 lines)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Landing | `HolidayCalendarController.php:54` |
| form | GET/POST (unrestricted) | View | Holiday form | `HolidayCalendarController.php:71` |
| filterjson | GET/POST (unrestricted) | JSON | Filter holidays | `HolidayCalendarController.php:107` |
| groupform | GET/POST (unrestricted) | View | Holiday-group form | `HolidayCalendarController.php:126` |
| listholidaygroup | GET/POST (unrestricted) | JSON | List groups | `HolidayCalendarController.php:139` |
| listholidaygroupforconfig | GET/POST (unrestricted) | JSON | Groups for config combo | `HolidayCalendarController.php:160` |
| saveholiday | GET/POST (unrestricted) | JSON | Save holiday | `HolidayCalendarController.php:184` |
| delete | GET/POST (unrestricted) | JSON | Delete holiday | `HolidayCalendarController.php:243` |
| deletegroup | GET/POST (unrestricted) | JSON | Delete group | `HolidayCalendarController.php:273` |
| saveholidaygroup | GET/POST (unrestricted) | JSON | Save group | `HolidayCalendarController.php:324` |
| listholidays | GET/POST (unrestricted) | JSON | List holidays | `HolidayCalendarController.php:362` |
| listholidaygroupforgrid | GET/POST (unrestricted) | JSON | Groups for grid | `HolidayCalendarController.php:407` |
| checkholidaydateexists | GET/POST (unrestricted) | JSON | Duplicate-date check | `HolidayCalendarController.php:440` |

Clean CRUD controller, `Model/Holiday.php` and `Model/HolidayGroup.php` both bare (no validation) — all uniqueness/date checks (`checkholidaydateexists`) are done in the controller, not the model.

### DayTimeProcedureController.php (979 lines) — shift/working-time policy config
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Landing | `DayTimeProcedureController.php:51` |
| lists | GET/POST (unrestricted) | JSON | List shift policies | `DayTimeProcedureController.php:82` |
| listpolicies | GET/POST (unrestricted) | JSON | Policies list | `DayTimeProcedureController.php:131` |
| listpoliciesforconfig | GET/POST (unrestricted) | JSON | Policies for config combo | `DayTimeProcedureController.php:164` |
| saveDayTimeProcedure | GET/POST (unrestricted) | JSON (~370 lines, largest action) | Save/update shift policy definition | `DayTimeProcedureController.php:189` |
| delete | GET/POST (unrestricted) | JSON | Delete policy | `DayTimeProcedureController.php:562` |
| form | GET/POST (unrestricted) | View | Policy form | `DayTimeProcedureController.php:614` |
| checkshiftpolicyexists | GET/POST (unrestricted) | JSON (return) | Duplicate-code check | `DayTimeProcedureController.php:662` |
| roaster | GET/POST (unrestricted) | View [sic typo, "roster"] | Roster view | `DayTimeProcedureController.php:683` |
| deleteTempExceptions | GET/POST (unrestricted) | JSON | Delete temp shift exceptions | `DayTimeProcedureController.php:692` |
| saveException | **POST only** (`if ($this->request->is('post'))` line 720, else JSON error at 834) | JSON | Save a shift exception rule | `DayTimeProcedureController.php:713` |
| getExceptions | **AJAX only** (`if ($this->request->is('ajax'))` line 844) | JSON | Get exceptions list | `DayTimeProcedureController.php:839` |
| deleteException | GET/POST (unrestricted) | JSON | Delete exception | `DayTimeProcedureController.php:865` |
| getDayStatus | GET/POST (unrestricted) | JSON | Day status (holiday/weekoff/exception) lookup | `DayTimeProcedureController.php:914` |
| toggleExceptionStatus | GET/POST (unrestricted) | JSON | Toggle exception active/inactive | `DayTimeProcedureController.php:945` |

Backing model `Model/ShiftExceptions.php` declares `class ExceptionRule extends AppModel` with `$name = 'ShiftException'` — **class name (`ExceptionRule`) does not match filename/CakePHP model name (`ShiftException`)**, a naming inconsistency worth flagging for the migration (CakePHP's autoloader tolerates this via `$name`, but a Next.js/TS model layer should not carry the mismatch forward). Uses `$uses` entry `'ShiftException'` (singular) at `DayTimeProcedureController.php:50` — confirms CakePHP resolves by `$name`, not filename/classname.

### MobileLocationUpdateController.php (355 lines)
| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| index | GET/POST (unrestricted) | View | Map/landing | `MobileLocationUpdateController.php:33` |
| loadmap | GET/POST (unrestricted) | View (sets `datas` as json_encode string for map JS) | Load map data | `MobileLocationUpdateController.php:78` |
| employeelist | GET/POST (unrestricted) | JSON | Employee list for filter | `MobileLocationUpdateController.php:123` |
| downloadexcel | GET/POST (unrestricted) | Mixed (Excel file, ~160 lines) | Export mobile location log | `MobileLocationUpdateController.php:154` |
| downloadpdf | GET/POST (unrestricted) | Mixed (PDF file, ~40 lines to EOF) | Export mobile location log as PDF | `MobileLocationUpdateController.php:315` |

---

## 2. Model Validation & Business Rules

**Central finding: every model backing this cluster is a bare `AppModel` subclass — none declare `$validate`, `beforeSave`, `beforeValidate`, `afterSave`, or `afterFind`.** Verified via grep across all 26 candidate model files (§0). Only two have any custom logic at all, and it's SQL-procedure-call wrapping, not validation:

| Model | File | Table | Custom methods | Notes |
|---|---|---|---|---|
| `AttendanceRegister` | `Model/AttendanceRegister.php:7-72` | `attendance_register` | `insertUpdateAttendanceRegisterProc()` (line 18) → `CALL insert_update_att_reg(...)`; `salaryProcessPrc()` (line 47) → `CALL salary_process_prc(...)`; `calculateSalaryMainPrc()` (line 60, annotated "Edited by Akshay on 19-12-2025") → `CALL calculate_salary_main_prc(...)` | **All register write/verify/salary-process business logic is delegated to MySQL stored procedures**, not visible in PHP. Migration must locate and port these 3 procedures from the DB schema. |
| `AttendanceRegisterReport` | `Model/AttendanceRegisterReport.php:5-27` | `attendance_register_rep` | `insertUpdateAttendanceRegisterForReportProc()` (line 16) → `CALL insert_update_att_reg_rep(...)` | Same pattern, reporting-table variant. |
| `EditPunches` | `Model/EditPunches.php:7-17` | `device_attandance` (note: table name misspelled "attandance" in schema — carries through many controller vars like `$device_attandance_seq`) | none | Bare mapping only. |
| `EditPunchesHist` | `Model/EditPunchesHist.php:7-17` | `device_attandance_hist` | none | Bare mapping; history/audit table for punch edits. |
| `RegisterHistory` | `Model/RegisterHistory.php:9-19` | `register_history` | none | Bare mapping; audit table for register changes. |
| `EmployeeAttendanceUpload` | `Model/EmployeeAttendanceUpload.php:7-14` | `emp_attendance_upload` | none | Bare mapping. |
| `EmpDetailedAttendanceUpload` | `Model/EmpDetailedAttendanceUpload.php:9-16` | `emp_detailed_attendance_uploads` | none | Bare mapping; **does not even declare `App::uses('AppModel','Model')`** (line 1-2 jump straight to class decl) — likely relies on autoloading working elsewhere in bootstrap; verify no fatal in isolation. |
| `DayTimeProcedures` | `Model/DayTimeProcedures.php:7-17` | `working_day_time_procedures` | none | Bare mapping; shift policy master. |
| `ExceptionRule` (aliased `ShiftException`) | `Model/ShiftExceptions.php:9-19` | `shift_exceptions` | none | Class/file/CakePHP-name mismatch noted above. |
| `ScheduledBreakOff` | `Model/ScheduledBreakOff.php:5-11` | `scheduled_break_off` | none | Bare mapping. |
| `Holiday` | `Model/Holiday.php:7-17` | `holidays` | none | Bare mapping; PK `HOLIDAYID` (uppercase, inconsistent with rest of schema's lowercase convention). |
| `HolidayGroup` | `Model/HolidayGroup.php:7-17` | `holiday_group` | none | Bare mapping; PK `HOLIDAY_GROUP_ID` (uppercase). |
| `ReportAudit` | `Model/ReportAudit.php:7-17` | `report_audit` | none | Bare mapping; used by `AttendanceCheckInOutController::reportAudit()` and `OvertimeController::reportAudit()`/`downloadHistory()` to log/replay report runs. |
| `MobileUserTracking` | `Model/MobileUserTracking.php:7-16` | `mob_user_tracking` | none | Bare mapping. |
| `MobileUserauditor` | `Model/MobileUserauditor.php:9-14` | `mob_user_login_auditor` | none | Bare mapping. |
| `ProjectActivity` | `Model/ProjectActivity.php:7-16` | `project_activity` | none | Bare mapping; **primary key declared as `'activity_pkey\t'` (line 14) — literal trailing tab character in the string**, a latent bug that likely still works because CakePHP/MySQL column matching may tolerate it inconsistently, or may silently break PK-based finds/deletes. Flag for verification — could explain any "delete doesn't work" reports on `DailyActivityController::delete()`. |
| `SiteAttendance` | `Model/SiteAttendance.php:7-16` | `site_attendance` | none | Bare mapping; also has an unused `SiteAttendance_bkup_megha.php` backup sibling. |
| `Useraccess` | `Model/Useraccess.php:23-27` | `user_access` | none | Bare mapping; this is the site-level access-grant table used by `DailyActivityController`, unrelated to the app-wide `user_group`/menu ACL discussed in §0. |
| `Access_site` | `Model/Access_site.php:7-16` | `access_site` | none | Bare mapping. |
| `SiteHistory` | `Model/SiteHistory.php:7-16` | `site_history` | none | Bare mapping. |
| `SiteTransactions` | `Model/SiteTransactions.php:7-16` | `site_transactions` | none | Bare mapping; also has unused `SiteTransactions_bkup_megha.php` sibling. |
| `DeviceAttendance` | `Model/DeviceAttendance.php:7-17` | `device_attandance` (same table as `EditPunches` — **two model classes map the same underlying table**) | none | Confirms `EditPunches` and `DeviceAttendance` are two ORM facades over one physical table (`device_attandance`), used by different controllers (`EditPunchesController`/`EditAttendanceController` use `EditPunches`; `AttendanceCheckInOutController` uses `DeviceAttendance`) — must reconcile into one Next.js data-access module to avoid drift. |
| `Earlyin` / `Earlyout` / `Latein` / `Lateout` | `Model/Earlyin.php`, etc. | `emp_early_in` (and presumably parallel `emp_early_out`/`emp_late_in`/`emp_late_out`, not individually read but same pattern) | none | Bare mappings backing the early/late report generators in `AttendanceCheckInOutController`. |

### Association / business-rule implications for migration

- Because **no model enforces validation**, all field-level rules (required fields, date-range sanity, duplicate checks, status-transition legality) live inline in controller action bodies as ad hoc `if`/`switch` blocks — e.g. `HolidayCalendarController::checkholidaydateexists` (`HolidayCalendarController.php:440`), `DayTimeProcedureController::checkshiftpolicyexists` (`DayTimeProcedureController.php:662`), `AttendanceRegisterNewController::checkLeaveExists`/`isLeaveAlreadyApplied` (`AttendanceRegisterNewController.php:3959`, `:4234`). These must each be individually re-implemented as explicit validation logic in the Next.js API layer — there is no single validation schema to port wholesale.
- **No CakePHP model associations (`$belongsTo`/`$hasMany`/`$hasOne`)** were declared in any model in this cluster — all "joins" happen via raw SQL in controllers (e.g. `CompoffController.php:55-66`) or via CakePHP's automatic `$uses` array + manual `find()` calls with hand-written `conditions`/`joins`. This means the relational structure (e.g., which table owns which FK) must be reverse-engineered from controller SQL and stored-procedure definitions, not from model class declarations.
- The `attendance_register` table's business logic is almost entirely opaque to static PHP analysis because the primary write path (`insertUpdateAttendanceRegisterProc`) is one `CALL` statement — **strongly recommend pulling the stored procedure source (`insert_update_att_reg`, `salary_process_prc`, `calculate_salary_main_prc`, `insert_update_att_reg_rep`) directly from the MySQL schema/dump before scoping the Next.js migration of any register-verify or salary-process flow.**

---

## 3. Summary of legacy-cruft flags requiring verification before migration

1. `Controller/AttendanceregisterController.php` — entire controller, duplicate of `AttendanceController.php`/`AttendanceRegisterNewController.php` register actions under a different route casing; no inbound feature-gate reference found.
2. `Controller/EditAttendanceController.php::chnagestatus_old` (`EditAttendanceController.php:1046`) — superseded by `chnagestatus` (line 1117).
3. `Controller/EditAttendanceController.php::getstatus` (`EditAttendanceController.php:1334`) — empty/no-op body, dead code.
4. `Controller/EditPunchesController.php::listpunches` (line 1442) vs `listpunchesnew` (line 2394) — unclear which is live; verify wiring.
5. `Controller/EmpattendanceregisterController.php` — thin 3-action controller, likely superseded by `EmployeeAttendanceregisterController.php` (14 actions).
6. `Controller/EmpeditpunchesController.php` — thin 8-action controller, likely superseded by `EditPunchesController.php`.
7. `Controller/EmpattendanceuploadController.php` — likely superseded by `EmployeeAttendanceUploadController.php` (broader feature set, includes `checkleave`, `employeefilter`).
8. `Controller/OtAttendanceController.php::remove` (line 535) vs `removenew` (line 583) — verify which is wired.
9. `Controller/DailyOvertimeVerifyController.php` (whole) vs `DailyOvertimeVerifyNewController.php` — New has 3 extra actions (`updateOvertime`, `overtimeDataUpdate`, `getEmployeesByBranch`); no code-level company-blocklist swap found for this pair (unlike Regularisation/AttendanceRegisterNew) — verify via `features`/`plan_feature` DB tables, not assumable from code.
10. `Controller/OtAttendanceController.php` (whole) vs `OtAttendanceNewController.php` — same caveat as #9; New has 5 extra data-fetch actions.
11. Backup/dated-duplicate files (excluded from inventory, see §0 bullet 4) — confirm none are still routed before deleting during migration cutover.
12. `Model/ProjectActivity.php:14` — primary key string contains a literal trailing tab (`'activity_pkey\t'`) — verify this doesn't silently break delete/update-by-PK in `DailyActivityController`.
13. `Model/ShiftExceptions.php` — class `ExceptionRule` vs. filename/CakePHP `$name` `ShiftException` mismatch — cosmetic in CakePHP but should be normalized in the Next.js port.
14. `AttendanceCheckInOutController`'s `generate*` report methods (10 of them) are `private` and unreachable directly; `OvertimeController`'s three `generate*Report` methods are `public` and technically URL-reachable outside the `generatereport` dispatcher — minor hardening note, not a functional migration blocker.

---

### 2.3 Leave Management

# Leave Management Cluster — Backend Technical Report

Scope: `Controller/LeaveRequestController.php`, `Controller/EmployeeLeaveRequestController.php`,
`Controller/EmployeeLeavesController.php`, `Controller/EmployeeLeaveUploadController.php`,
`Controller/EmpleaveuploadController.php`, `Controller/LeaveEncashmentRequestController.php`,
`Controller/LeavePolicyController.php`, `Controller/LeaveapiController.php`, and their backing models
in `Model/`.

All line numbers verified directly against the files in this repo (D:\Projects\RIZOMigration\legacy).

---

## 0. Cross-cutting findings (read this first)

1. **Auth gate is inconsistent across the cluster.** Seven of the eight controllers extend
   `AppController` (session-gated, `user_group` 1=admin/2=employee per
   `Controller/AppController.php:39-46`):
   - `Controller/LeaveRequestController.php:36`
   - `Controller/EmployeeLeaveRequestController.php:36`
   - `Controller/EmployeeLeavesController.php:9`
   - `Controller/EmployeeLeaveUploadController.php:9`
   - `Controller/EmpleaveuploadController.php:9`
   - `Controller/LeaveEncashmentRequestController.php:34`
   - `Controller/LeavePolicyController.php:33`

   **`Controller/LeaveapiController.php:36` extends the bare CakePHP `Controller` class**, not
   `AppController` — confirmed. It skips the session auth gate entirely; every action in the file is
   reachable unauthenticated. `checkLogin()` redirects with a plaintext password appended to the
   query string (`Controller/LeaveapiController.php:126`) — confirmed, see §3.

2. **No HTTP verb enforcement anywhere in the cluster.** Across all ~20,500 lines of these 8
   controllers, there is exactly **one** `$this->request->is('post')` check in the entire cluster:
   `Controller/EmpleaveuploadController.php:1405`. Every other action — including ones that mutate
   state (save/delete/approve/reject) — will execute identically whether invoked via GET or POST.
   Practically all "mutating" actions are still routed through AJAX POST by the front-end, but the
   backend does not enforce it. This matters for the Next.js migration: verb semantics (GET vs
   POST/PUT/DELETE) must be designed fresh: they are not recoverable from the legacy code.

3. **Widespread raw SQL string interpolation (SQL injection surface).** Nearly every controller
   builds raw SQL via `$this->Model->query("... '$var' ...")` instead of parameterized
   `find()`/conditions arrays, with `$var` sourced from `$_REQUEST`, `$this->request->data`, or
   session values. Representative examples:
   - `Controller/LeavePolicyController.php:137` — `... WHERE LEAVEPOLICY_GROUP_ID = '".$_REQUEST["LEAVEPOLICY_GROUP_ID"]."'"` (raw `$_REQUEST` straight into SQL)
   - `Controller/LeavePolicyController.php:227` — same pattern with `$leavepolicygroupid`
   - `Controller/LeaveRequestController.php:2320` — `SELECT emp_pkey FROM employee_info WHERE employee_id = '$empid'` where `$empid` comes directly from posted form data
   - `Controller/LeaveRequestController.php:2482-2485` (and identically `EmployeeLeaveRequestController.php:2588-2590`) — chained raw queries keyed off values pulled from a prior raw query
   - `Controller/EmployeeLeaveUploadController.php:2167-2181` (`checkLeaveDays`) — leave-overlap detection query built from posted dates/sessions
   Flag as a security item independent of the migration — worth a dedicated remediation pass.

4. **Anemic models — all business logic lives in controllers.** See §4. None of the 12 leave-related
   models define `$validate`, `beforeSave`, `beforeValidate`, `afterSave`, or associations
   (`$belongsTo`/`$hasMany`). Validation, state transitions, and notification triggering are 100%
   controller-resident, mostly inside the duplicated `grandLeave()` functions.

5. **Two distinct models map to the identical `leaveentries` table:** `Model/LeaveRequests.php`
   (`useTable = 'leaveentries'`, `Model/LeaveRequests.php:15`) and `Model/LeaveEntries.php`
   (`useTable = 'leaveentries'`, `Model/LeaveEntries.php:15`), both keyed on `LEAVEENTRYID`. Only
   `LeaveRequests` is actually wired into the controllers via `$uses`; `LeaveEntries` appears
   unused by this cluster — confirm before treating it as authoritative anywhere else in the app.

6. **`grandLeave()` is duplicated three times, not two, with materially different implementations
   under the same name:**
   - `Controller/LeaveRequestController.php:2301-2860` — the full leave-request state machine
     (Applied → Authorized → Approved, plus Cancellation sub-chain). ~560 lines.
   - `Controller/EmployeeLeaveRequestController.php:2402-2949` — near-identical duplicate of the
     above (self-service employee-portal variant). ~550 lines.
   - `Controller/LeaveEncashmentRequestController.php:1002-1025` — a *different, much simpler*
     function that reuses the same name for leave-**encashment** approval (just flips
     `is_approved`/`approved_date` on `LeaveEncashmentMaster`). Do not conflate this with the other
     two when migrating — it is not part of the Applied/Authorized/Approved state machine.

7. **Hardcoded `leval_of_approval = '3'` in every final-notification lookup.** Both
   `LeaveRequestController.php` (`:2393`, `:2485`, `:2190`... see grep results below) and
   `EmployeeLeaveRequestController.php:366,559,706,2276,2590` hardcode `leval_of_approval = '3'`
   when looking up the "final" sanctioning employee for the completion email — never `'2'`. The
   `leavepolicy` table clearly supports a per-(policy-group, leave-type) chain of sanctioners
   (columns imply levels 1/2/3), but the notification code always assumes a 3-level chain exists.
   If a company's policy is only configured with 1 or 2 levels, this lookup returns empty and the
   completion email silently never sends (no error surfaced) — confirmed pattern at
   `Controller/LeaveRequestController.php:2485-2517` where `$issendfinalmail` simply stays `false`.
   Migration must decide explicitly whether the new schema keeps a fixed 3-level chain or a
   variable-length approval chain, since the legacy code effectively hardcodes the former for
   notifications regardless of what's configured.

8. **Hardcoded per-tenant branching baked directly into shared controller code.**
   `Controller/LeavePolicyController.php` contains a repeated `$restricted_companies` array
   (`'KWMT','ABSG','MBCT','MRBS','STCL','AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'`) checked with
   `in_array($company_code, $restricted_companies, true)` at **6 separate locations** —
   `:145-149`, `:235-239`, `:281-285`, `:302-306`, `:326-330`, `:356-360` — gating whether
   leave-cycle-date fields exist, whether a company-specific `att_start_end_fn` SQL call runs, and
   which view template renders (`policyform` vs `form`, `:307-309`). This is tenant-specific logic
   living in generic application code rather than in per-tenant configuration — a strong candidate
   to externalize into config/feature-flags during migration rather than reproduce as code branches.
   Comments attribute these edits to "athira" between 2025-08-01 and 2025-09-21, i.e. recent and
   still evolving — check with the business owner before assuming they're finalized.

9. **Overlap/double-booking validation lives in the upload controller, not the request
   controller.** `Controller/EmployeeLeaveUploadController.php::checkLeaveDays()` (`:2123-2199`)
   is the only place that explicitly checks for overlapping leave date/session ranges against
   `emp_leave_transactions` joined to `leaveentries`, gated on
   `LEAVESTATUS IN ('Approved','Authorized','Applied','CancellationOfApproved','CancellationOfAuthorized')`
   (`:2181`). Neither `LeaveRequestController::saveLeaveEntry()` nor
   `EmployeeLeaveRequestController::saveLeaveEntry()` appear to call this same check inline (verify
   during migration — do not assume overlap protection exists on the normal apply-leave path just
   because it exists on the bulk-upload path).

10. **Hand-rolled inline PHPMailer with hardcoded SMTP credentials — confirmed, and worse than a
    single occurrence.** `Controller/LeaveapiController.php::sendauthorizationmail()` (`:192-706`)
    hardcodes an OCI SMTP username/password in plaintext (`:260-261`). The `send*mail()` family in
    `LeaveRequestController.php` (`sendauthorizationmail`, `sendfinalapprovalmail`,
    `sendapprovemail`, `sendapprovedmail`, `sendrejectedsmail`, `sendcancellationappliedmail`,
    `sendcancellationapprovedmail`, `sendcancelledmail` — see action tables below) and their
    `EmployeeLeaveRequestController.php` twins are the actual mail senders invoked from
    `grandLeave()`; confirm whether they also inline PHPMailer with hardcoded creds or delegate to
    the `Email` component (`$components = array(..., 'Email')` is declared on `LeaveapiController`
    at `:54` but that component does not appear to be used inside `sendauthorizationmail()` itself —
    it manually instantiates `PHPMailer` instead, `:255`).

---

## 1. Controller Action Inventory

Response-type shorthand: **JSON** = `autoRender=false` + `json_encode`/`echo json_encode`;
**HTML** = renders a `.ctp` view; **redirect** = `header(Location:...)`/CakePHP `redirect()`;
**mixed** = branches between JSON and HTML/redirect depending on path; **none/void** = fire-and-forget
(mailer or notification action with no structured response).

All actions below accept **any HTTP verb** (GET/POST/etc.) unless noted otherwise — see cross-cutting
finding #2.

### 1.1 `Controller/LeaveRequestController.php` (extends AppController — session-gated)

Uses: `AppModel, LeaveRequests, EmpLeaveApproval, EmployeeConfig, SalaryHeadItems, EmployeeDetails, EmployeeLeaveTransaction, LeavePolicy, EmployeeInfo` (`:52`).

| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML | Landing page for leave-request admin list | `:71` |
| `employeeleaves` | GET/POST | JSON | DataTables server-side feed of leave requests | `:85` |
| `getusers` | GET/POST | JSON | Filtered employee lookup for leave-request filters | `:281` |
| `getusers_notify` | GET/POST | JSON | Employee lookup for notification recipients | `:442` |
| `listempleaves` | GET/POST | JSON | List leave entries for an employee | `:469` |
| `listempleavesverified` | GET/POST | JSON | List verified leave entries | `:555` |
| `manageempleave($leaveentryId=0)` | GET/POST | mixed (JSON error paths, else renders `manageempleave` at `:898`) | Create/edit leave-entry management screen | `:621` |
| `getfinyear($date=null)` | GET/POST | JSON (implied by return use) | Resolve financial year from a date | `:925` |
| `getEmployeeDates` | GET/POST | JSON | Fetch employee-specific date constraints | `:934` |
| `listleaves` | GET/POST | JSON | List leave entries (self/admin) | `:969` |
| `addeditleave($leaveentryId=0)` | GET/POST | JSON | Create/update a leave request | `:1032` |
| `getLeaveType` | GET/POST | JSON | Leave-type dropdown source | `:1197` |
| `getReportingEmployeeList($showall=0,$leaveentryId=0)` | GET/POST | JSON (commented out) / array return | Reporting-manager list for approval routing | `:1245` |
| `loadLeaveDetails($leaveentryId=0)` | GET/POST | JSON (commented) | Load leave entry detail | `:1280` |
| `loadEmpLeaveDetails($leaveentryId=0,$sanction=0)` | GET/POST | JSON (commented) | Load employee leave detail incl. sanctioning info | `:1353` |
| `checkIfLeaveRequestEditable($leaveentryId=0)` | GET/POST | JSON | Guard: is this leave request still editable | `:1464` |
| `saveDocument` | POST (by convention; no verb check) | JSON | Upload supporting document for a leave request | `:1543` |
| `saveLeaveEntry($leaveentryId=0)` | POST (by convention) | JSON | Core save/apply-leave handler | `:1648` |
| `criterias($pkey=0,$fromdate=0,$todate=0,$leavestatus='')` | internal/GET | array (helper, called by `grandLeave` and others) | Leave-balance/eligibility criteria evaluation | `:2222` |
| `criterias1($pkey=0,$fromdate=0,$todate=0)` | internal/GET | array (helper) | Secondary criteria evaluation variant | `:2281` |
| `grandLeave` | POST (by convention) | JSON | **Core state-machine action**: Authorize/Approve/Reject/Cancel-chain transitions + triggers mail senders below | `:2301-2857` |
| `deleteLeaveRequests` | POST/GET (no verb check — destructive action reachable via GET) | JSON | Delete a leave request | `:2860` |
| `GetLeaveBalance($salary_head_item_fkey="",$end_date="")` | GET/POST | JSON | Compute leave balance for a leave type | `:2884` |
| `showleavedays($leaveentryid=0)` | GET/POST | HTML/JSON (mixed) | Display day-by-day breakdown of a leave entry | `:3138` |
| `getLeaveBalanceForAuthOrApproval($leaveentryId="")` | internal | array (helper) | Monthly balance check used inside `grandLeave` | `:3248` |
| `getYearlyLeaveBalanceForAuthOrApproval($leaveentryId="")` | internal | array (helper) | Yearly balance check used inside `grandLeave` | `:3293` |
| `sendauthorizationmail($output,$auth)` | internal | none/void | Email: leave awaiting authorization | `:3332` |
| `sendfinalapprovalmail($output,$auth)` | internal | none/void | Email: final-level sanctioner approval needed | `:3634` |
| `sendapprovemail($output,$auth)` | internal | none/void | Email: approval-stage notification | `:3940` |
| `sendapprovedmail($output,$auth)` | internal | none/void | Email: leave approved confirmation | `:4219` |
| `sendrejectedsmail($output,$auth)` | internal | none/void | Email: leave rejected notification | `:4496` |
| `leaves` | GET/POST | (thin wrapper) | Wrapper/alias — see `loadleaves` | `:4762` |
| `loadleaves` | GET/POST | JSON (implied) | Loads leave list, called by `leaves()` | `:4771` |
| `sendcancellationappliedmail($output,$auth)` | internal | none/void | Email: cancellation request submitted | `:4785` |
| `sendcancellationapprovedmail($output,$auth)` | internal | none/void | Email: cancellation approved | `:5067` |
| `sendcancelledmail($output,$auth)` | internal | none/void | Email: leave fully cancelled | `:5339` |
| `callLeaveTransactionProcedure($leaveentryId='')` | internal | bool | Invokes `leave_transaction_prc` stored procedure via `LeaveRequests::leaveTransactionPrc()` | `:5611` |
| `deletedoc($id=0,$name=0,$document=0)` | GET/POST | JSON | Delete an uploaded supporting document | `:5627` |
| `GetLeaveBalanceNew($salary_head_item_fkey="",$end_date="")` | GET/POST | JSON | Newer variant of leave-balance calc (parallel to `GetLeaveBalance`) — **possible legacy cruft: verify which of the two is still called by current views before migrating; both exist side by side** | `:5644` |
| `addeditleave_new($leaveentryId=0)` | GET/POST | JSON | Newer variant of `addeditleave` — **possible legacy cruft: two apply-leave handlers coexist (`addeditleave` at `:1032` and `addeditleave_new` at `:5926`); confirm which the current front-end actually calls** | `:5926` |
| `checkAttendancePunches($emp_pkey,$fromdate,$todate,$fromhalf=1,$tohalf=2)` | internal (private) | array (helper) | Cross-checks attendance punches against leave dates | `:6113` |

### 1.2 `Controller/EmployeeLeaveRequestController.php` (extends AppController — session-gated)

Near-duplicate of §1.1, exposed under the employee self-service portal. Same `$uses` pattern (not re-read in full; verify against `:1-60` if divergence matters).

| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML | Landing page (employee self-service) | `:60` |
| `employeeleaves` | GET/POST | JSON | DataTables feed, employee-scoped | `:74` |
| `getusers` | GET/POST | JSON | Employee lookup | `:150` |
| `getusers_notify` | GET/POST | JSON | Notification-recipient lookup | `:276` |
| `listempleaves` | GET/POST | JSON | List own leave entries | `:302` |
| `listempleavesverified` | GET/POST | JSON | List verified leave entries | `:383` |
| `manageempleave($leaveentryId=0)` | GET/POST | mixed | Manage-leave screen | `:447` |
| `getfinyear($date=null)` | GET/POST | JSON | Financial-year resolver | `:797` |
| `listleaves` | GET/POST | JSON | List leave entries | `:806` |
| `printleave($pkey=0)` | GET | HTML (print view) | Printable leave-request slip — **not present in `LeaveRequestController`; employee-portal-only action** | `:927` |
| `addeditleave($leaveentryId=0)` | GET/POST | JSON | Apply/edit leave | `:1040` |
| `Getauther_approv` | GET/POST | JSON | Fetch authorizer/approver chain for display — **not present in `LeaveRequestController`; employee-portal-only action** | `:1204` |
| `getLeaveType` | GET/POST | JSON | Leave-type dropdown | `:1338` |
| `getReportingEmployeeList($showall=0,$leaveentryId=0)` | GET/POST | array | Reporting-manager list | `:1400` |
| `loadLeaveDetails($leaveentryId=0)` | GET/POST | JSON (commented) | Load leave detail | `:1435` |
| `loadEmpLeaveDetails($leaveentryId=0,$sanction=0)` | GET/POST | JSON (commented) | Load leave + sanctioning detail | `:1482` |
| `checkIfLeaveRequestEditable($leaveentryId=0)` | GET/POST | JSON | Editability guard | `:1596` |
| `saveDocument` | POST (convention) | JSON | Upload supporting document | `:1677` |
| `saveLeaveEntry($leaveentryId=0)` | POST (convention) | JSON | Apply-leave core handler | `:1774` |
| `criterias($pkey=0,$fromdate=0,$todate=0)` | internal | array | Eligibility criteria (note: 3-arg signature here vs 4-arg in `LeaveRequestController::criterias`, no `$leavestatus` param — **behavioral divergence between the two duplicates, verify**) | `:2334` |
| `criterias1($pkey=0,$fromdate=0,$todate=0)` | internal | array | Secondary criteria variant | `:2382` |
| `grandLeave` | POST (convention) | JSON | **Core state-machine action** — near-identical duplicate of `LeaveRequestController::grandLeave` | `:2402-2949` |
| `deleteLeaveRequests` | POST/GET (no verb check) | JSON | Delete leave request | `:2950` |
| `GetLeaveBalance($salary_head_item_fkey="",$emp="",$end_date="")` | GET/POST | JSON | Leave balance calc (note extra `$emp` param vs `LeaveRequestController` version) | `:2975` |
| `showleavedays($leaveentryid=0)` | GET/POST | mixed | Day-by-day leave breakdown | `:3166` |
| `getLeaveBalanceForAuthOrApproval($leaveentryId="")` | internal | array | Monthly balance helper | `:3268` |
| `getYearlyLeaveBalanceForAuthOrApproval($leaveentryId="")` | internal | array | Yearly balance helper | `:3305` |
| `sendauthorizationmail($output,$auth)` | internal | none/void | Email: authorization needed | `:3332` |
| `sendsanctiontionmail($output,$auth)` | internal | none/void | Email: sanction-stage notice — **not present under this name in `LeaveRequestController`; verify equivalence to `sendfinalapprovalmail`** | `:3655` |
| `sendfinalapprovalmail($output,$auth)` | internal | none/void | Email: final approval | `:3953` |
| `sendapprovemail($output,$auth)` | internal | none/void | Email: approval stage | `:4267` |
| `sendapprovedmail($output,$auth)` | internal | none/void | Email: approved confirmation | `:4547` |
| `sendrejectedsmail($output,$auth)` | internal | none/void | Email: rejected notice | `:4823` |
| `leaves` | GET/POST | thin wrapper | Alias for `loadleaves` | `:5090` |
| `loadleaves` | GET/POST | JSON | Loads leave list | `:5100` |
| `sendcancellationappliedmail($output,$auth)` | internal | none/void | Email: cancellation applied | `:5115` |
| `sendcancellationapprovedmail($output,$auth)` | internal | none/void | Email: cancellation approved | `:5398` |
| `sendcancelledmail($output,$auth)` | internal | none/void | Email: cancelled | `:5671` |
| `callLeaveTransactionProcedure($leaveentryId='')` | internal | bool | Stored-proc trigger | `:5944` |
| `deletedoc($id=0,$name=0,$document=0)` | GET/POST | JSON | Delete uploaded document | `:5964` |

Note: unlike `LeaveRequestController.php`, this file has **no** `addeditleave_new` / `GetLeaveBalanceNew` counterparts — the "_new" variants appear to be admin-side-only additions. Confirm during migration whether the employee-portal flow is missing a fix that was applied only to the admin side, or whether it was deliberately not needed.

### 1.3 `Controller/EmployeeLeavesController.php` (extends AppController — session-gated)

| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML | Landing page | `:28` |
| `branchwiss($branch='')` | GET/POST | JSON | Branch-filtered employee/leave list | `:64` |
| `form($emp_leave_upload_pkey="")` | GET/POST | HTML | Leave-balance/entry form | `:86` |
| `leavesave` | POST (convention) | JSON | Save a leave entry | `:172` |
| `load` | GET/POST | JSON | Load supporting data for the form | `:433` |
| `listleave` | GET/POST | JSON | List leave entries | `:452` |
| `deleteleave` | POST/GET (no verb check) | JSON | Delete a leave entry | `:780` |
| `approveleave` | POST (convention) | JSON | Approve a leave entry | `:801` |
| `getfinyear($date=null)` | GET/POST | JSON | Financial-year resolver | `:833` |
| `getleave` | GET/POST | JSON | Fetch a single leave entry | `:841` |
| `downloadempctcformat($ctcuploadtype=0,$branch='',$employee='')` | GET | file download | Download a CTC/leave upload template | `:869` |
| `uploadandsaveempctc($ctcuploadtype=0)` | POST (convention) | JSON | Bulk-upload leave entries from file | `:1143` |
| `GetLeaveBalance($salary_head_item_fkey="",$emp_fkey="",$end_date="")` | GET/POST | JSON | Leave-balance calc | `:1509` |
| `getLeaveTypeByOccurance($type)` | GET/POST | JSON | Leave types filtered by occurrence type | `:1575` |
| `Loadingform` | GET/POST | JSON | Loading/init data for form | `:1586` |
| `getLeaveType($emp_fkey="")` | GET/POST | JSON | Leave-type dropdown source | `:1590` |
| `leavecheck` | GET/POST | JSON | Pre-save leave validity check | `:1627` |
| `getLeaveBalanceForAuthOrApproval($emp_fkey='',$salary_head_item_fkey='',$to_date='')` | internal | array | Balance helper for approval flow | `:1665` |
| `showfailedleaverequests($importedCount='',$startedTime='',$employee=array())` | GET/POST | HTML/JSON | Report of failed bulk-import rows | `:1686` |
| `showleavedays($leaveentryid=0)` | GET/POST | mixed | Day-by-day breakdown | `:1713` |
| `filterHeads` | GET/POST | JSON | Filtered salary-head/leave-type list | `:1798` |

### 1.4 `Controller/EmployeeLeaveUploadController.php` (extends AppController — session-gated)

Largest of the upload controllers (2573 lines). Superset of §1.3-style bulk upload plus newer variants.

| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML | Landing page | `:29` |
| `branchwiss($branch='')` | GET/POST | JSON | Branch-filtered list | `:64` |
| `form($emp_leave_upload_pkey="")` | GET/POST | HTML | Upload/edit form | `:86` |
| `getEmployeeDates` | GET/POST | JSON | Employee date constraints | `:197` |
| `leavesave` | POST (convention) | JSON | Save leave entry | `:231` |
| `load` | GET/POST | JSON | Supporting data for form | `:545` |
| `listleave` | GET/POST | JSON | List entries | `:564` |
| `deleteleave` | POST/GET (no verb check) | JSON | Delete entry | `:726` |
| `getfinyear($date=null)` | GET/POST | JSON | Financial-year resolver | `:752` |
| `getleave` | GET/POST | JSON | Fetch single entry | `:760` |
| `downloadempctcformat($ctcuploadtype=0,$branch='',$employee='')` | GET | file download | Download upload template | `:797` |
| `uploadandsaveempctc($ctcuploadtype=0)` | POST (convention) | JSON | Bulk-upload leave entries | `:1444` |
| `GetLeaveBalance($salary_head_item_fkey="",$emp_fkey="",$end_date="")` | GET/POST | JSON | Leave-balance calc | `:1837` |
| `getLeaveTypeByOccurance($type)` | GET/POST | JSON | Leave types by occurrence | `:1905` |
| `Loadingform` | GET/POST | JSON | Form init data | `:1923` |
| `getLeaveType($emp_fkey="")` | GET/POST | JSON | Leave-type dropdown | `:1927` |
| `leavecheck` | GET/POST | JSON | Pre-save validity check | `:1994` |
| `getLeaveBalanceForAuthOrApproval($emp_fkey='',$salary_head_item_fkey='',$to_date='')` | internal | array | Approval-flow balance helper | `:2062` |
| `showfailedleaverequests($importedCount='',$startedTime='',$employee=array())` | GET/POST | HTML/JSON | Failed-import report | `:2095` |
| `checkLeaveDays` | POST (convention) | JSON | **Overlap/double-booking validation** — the only explicit date/session overlap check in the whole cluster (see finding #9) | `:2123-2199` |
| `GetLeaveBalanceNew($salary_head_item_fkey="",$emp_fkey="",$start_date="",$end_date="")` | GET/POST | JSON | Newer leave-balance variant — **possible legacy cruft alongside `GetLeaveBalance`; confirm which is live** | `:2203` |
| `form_new($emp_leave_upload_pkey="")` | GET/POST | HTML | Newer form variant alongside `form()` — **possible legacy cruft; confirm which view/route is actually linked** | `:2373` |
| `checkAttendancePunches($emp_pkey,$fromdate,$todate,$fromhalf=1,$tohalf=2)` | internal (private) | array | Attendance-punch cross-check, duplicated from `LeaveRequestController` | `:2515` |

### 1.5 `Controller/EmpleaveuploadController.php` (extends AppController — session-gated)

Distinct, older/parallel controller focused on **leave-balance** upload rather than leave-entry upload (note filename similarity to §1.4 but different responsibility — likely a naming collision worth flagging to the team).

| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML | Landing page | `:29` |
| `form` | GET/POST | HTML | Leave-balance upload form | `:45` |
| `leavebalancesave` | POST (convention) | JSON | Save a leave-balance record | `:133` |
| `load` | GET/POST | JSON | Supporting data | `:204` |
| `listleave` | GET/POST | JSON | List entries | `:223` |
| `deleteleave` | POST/GET (no verb check) | JSON | Delete entry | `:300` |
| `getleave` | GET/POST | JSON | Fetch single entry | `:328` |
| `downloadempctcformat($ctcuploadtype=0)` | GET | file download | Download template | `:395` |
| `uploadandsaveempctc($ctcuploadtype=0)` | POST — **the one verified `$this->request->is('post')` check in the entire cluster, at `:1405`, inside this action** | JSON | Bulk-upload leave-balance records | `:551` |
| `getleavebalance($cur_emp_key,$leavebalance)` | internal | array | Balance-calc helper | `:812` |
| `getLeaveType($type)` | GET/POST | JSON | Leave-type dropdown | `:882` |
| `leavebalance` | GET/POST | JSON | Get current leave balance | `:892` |
| `listleavebalance` | GET/POST | JSON | List leave balances | `:931` |
| `downloadLeaveBalanceUploadctc($ctcuploadtype='',$branch=0,$emp_fkey=0)` | GET | file download | Download balance-upload template variant | `:1013` |
| `uploadandsaveempctcleavebalance($ctcuploadtype=0)` | POST (convention) | JSON | Bulk-upload leave balances (variant of `uploadandsaveempctc`) — **possible legacy cruft: two near-identical bulk-upload actions in the same controller, confirm which is live** | `:1222` |
| `getLeaves` | GET/POST | JSON | Fetch leave list | `:1402` |

### 1.6 `Controller/LeaveEncashmentRequestController.php` (extends AppController — session-gated)

Uses `LeaveEncashmentMaster` model primarily (per §4). Note: `grandLeave()` here is a **different, unrelated function** from the state-machine one in §1.1/§1.2 — see finding #6.

| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML | Landing page | `:56` |
| `employeeleaves` | GET/POST | JSON | DataTables feed | `:68` |
| `addleave` | GET/POST | HTML/JSON | New encashment-request form/submit | `:114` |
| `listappliedleaveencashs` | GET/POST | JSON | List applied encashment requests | `:156` |
| `editvalue` | POST (convention) | JSON | Inline edit of an encashment value | `:173` |
| `listallempsforenc($id=0)` | GET/POST | JSON | List employees eligible for encashment | `:185` |
| `listallempsforencash` | GET/POST | JSON | List employees for encashment (bulk) | `:248` |
| `listallempsforverified` | GET/POST | JSON | List verified employees | `:474` |
| `cancelentries($registerid=0)` | POST (convention) | JSON | Cancel an encashment register entry | `:571` |
| `verifyregisterentries` | POST (convention) | JSON | Verify register entries | `:589` |
| `encashemp` | POST (convention) | JSON | Perform encashment for an employee | `:622` |
| `encashlisting` | GET/POST | JSON | List encashment records | `:689` |
| `getusers` | GET/POST | JSON | Employee lookup | `:711` |
| `listempleavesverified` | GET/POST | JSON | List verified leave entries | `:742` |
| `manageleave($leaveentryId=0,$emp=0)` | GET/POST | HTML/JSON | Manage-leave screen (encashment context) | `:764` |
| `getfinyear($date=null)` | GET/POST | JSON | Financial-year resolver | `:806` |
| `listleaves` | GET/POST | JSON | List leave entries | `:813` |
| `save($leaveentryId=0)` | POST (convention) | JSON | Save encashment request | `:869` |
| `form` | GET/POST | HTML | Encashment form | `:904` |
| `loadleave($leaveentryId=0)` | GET/POST | JSON | Load a leave entry | `:963` |
| `approvetab` | GET/POST | HTML/JSON | Approval-tab view | `:982` |
| `listleavesrequests` | GET/POST | JSON | List leave requests pending encashment action | `:987` |
| `grandLeave` | POST (convention) | JSON | **Encashment approval action** (approve encashed days) — NOT the Applied/Authorized/Approved state machine; see finding #6 | `:1002-1025` |
| `cancel_leave` | POST (convention) | JSON | Cancel an encashment master record | `:1028` |
| `listcriteriaitems($str_criteria='',$branch='')` | GET/POST | JSON | Criteria-filtered item list | `:1036` |
| `employeelists($branch)` | GET/POST | JSON | Employee list by branch | `:1099` |

### 1.7 `Controller/LeavePolicyController.php` (extends AppController — session-gated)

Configuration controller for leave policy chains (`leavepolicy` / `leavepolicy_group` tables).

| Action | Method | Response | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML | Landing page | `:54` |
| `groupform` | GET/POST | HTML | Leave-policy-group form | `:63` |
| `jsons($branch='')` | GET/POST | JSON | Branch-filtered JSON data feed | `:98` |
| `form` | GET/POST | HTML — reads raw `$_REQUEST` directly for SQL (`:137,:157,:200,:210,:223,:227`); tenant-specific branching via hardcoded `$restricted_companies` (`:145-149` etc., see finding #8) | Leave-policy create/edit form, incl. approval-chain (`sanction_by`, `leval_of_approval`) config | `:125-313` |
| `savepolicy` | POST (convention) | JSON | Persist a leave policy (calls `LeavePolicy->save()` with no model-level validation — see §4); more `$restricted_companies` tenant branching | `:315-396` |
| `saveleavepolicygroup` | POST (convention) | JSON | Persist a leave-policy group | `:398` |
| `listpolicies` | GET/POST | JSON | List policies | `:419` |
| `listpolicygroupforconfig` | GET/POST | JSON | List policy groups for config screens | `:509` |
| `listpolicygroup` | GET/POST | JSON | List policy groups | `:530` |
| `listleavetype` | GET/POST | JSON | List leave types | `:572` |
| `delete` | POST/GET (no verb check — destructive action reachable via GET) | JSON | Delete a leave policy | `:589` |
| `deletegroup` | POST/GET (no verb check) | JSON | Delete a leave-policy group | `:609` |
| `checkleavepolicyexists($LEAVEPOLICY_GROUP_ID=0)` | GET/POST | JSON | Existence check before create | `:649` |

### 1.8 `Controller/LeaveapiController.php` (extends bare `Controller` — UNAUTHENTICATED, exhaustive)

`App::uses('AppController', 'Controller')` is present at `:23` but **not used** — the class declaration
at `:36` explicitly extends `Controller`, bypassing `AppController::beforeFilter()` session checks
entirely. `$uses = array('AppModel','LeaveRequests','EmpLeaveApproval','EmployeeConfig','SalaryHeadItems','EmployeeDetails','EmployeeLeaveTransaction','LeavePolicy','EmployeeInfo')` (`:53`); `$components = array('LoginManagement','Session','Email')` (`:54`).

| Action | Full signature | Method | Response | Purpose | path:line |
|---|---|---|---|---|---|
| `postCurlRequest($url, $post_array, $check_ssl=true)` | internal helper | n/a | bool | Fires a backgrounded shell `curl` POST via `exec()` (`:60-74`) — **shells out to the OS from PHP with data interpolated into a command string; command-injection-adjacent pattern even though inputs are currently controller-internal** | `:60-74` |
| `checkmails()` | `public function checkmails()` | GET/POST | echo "Hello" (plain text) | **Test/debug action**: posts hardcoded demo leave data (`emp_id=12611`, `"sanjun"`, etc.) to the *production* URL `https://v1.mypayrollmaster.online/Leaveapi/sendauthorizationmail` via `postCurlRequest`, then `var_dump`s the curl exit status. No auth, no input parsing — pure hardcoded test harness left live in production code. **Possible legacy cruft — verify before migrating; if reachable it also spams the real authorization-mail endpoint.** | `:76-101` |
| `checkLogin()` | `public function checkLogin()` | GET | `redirect` (302 `Location` header) or `die("Invalid credentials")` | Reads `$_GET` into `$data` (`:110`) but then **ignores it** — runs a hardcoded single-sign-on lookup for a fixed demo identity (`'httaiosdiosd','DEMO','12611','projects@greatleap.tech','DEMO16'`) against `controldb` via **raw `mysql_connect()`** (deprecated ext/mysql, not PDO/mysqli) with a **hardcoded root password `'Localhost&*()'`** (`:113,:121`), then issues a redirect embedding the resolved account's **plaintext password in the URL query string**: `Location: https://login.mypayrollmaster.online/Site/login?user_id=...&password=...` — **confirmed exactly as flagged**, `:126`. Also vulnerable to SQL injection via the `mysql_query` calls at `:115,:123` if the hardcoded literals were ever replaced with the unused `$data`. | `:103-130` |
| `sendOTP()` | `function sendOTP()` (no visibility modifier — implicitly public) | POST (reads `php://input` JSON) | none/void | Reads JSON body (`message`, `messageTitle`, `attr2`, `email`), resolves a push-notification key via `getUID()`, then sends an FCM push via `Vendor/FirebaseNotification.php`. No auth check, no input validation. | `:132-156` |
| `getUID($profileID, $email)` | `public function getUID($profileID, $email)` | internal helper (also directly callable as an action since public) | JSON string (raw curl response, not re-encoded) | Calls external `myportalapi.mypayrollmaster.online` third-party API with **hardcoded HTTP Basic-style credentials in custom headers** (`username: profileadmin`, `password: admin&*()`, `:179-181`) to verify a push-notification key. | `:158-190` |
| `sendauthorizationmail()` | `public function sendauthorizationmail()` | POST (reads `php://input` JSON) | echo plain text (`'Message has been sent'` / `'Message could not be sent.'`) | Sends the actual leave-workflow notification emails (authorize/approve/reject/cancel variants selected via `actionType` in the JSON body) using **inline PHPMailer** with **hardcoded OCI SMTP credentials** (`Username`/`Password` literal in source, `:260-261`) — confirmed. Large inline HTML email templates built via string concatenation (`:288-692`), with `$reason`, `$duties`, etc. interpolated directly into HTML with no escaping (stored-XSS-in-email risk if any of those originate from user-controlled leave-request fields). No auth check — this endpoint is reachable by anyone and will actually attempt to send email through the hardcoded SMTP account. | `:192-706` |
| `getEmployeesCount()` | `public function getEmployeesCount()` | GET | `var_dump("hello")` (plain text) | **Test/debug action**: connects to `controldb` via raw `mysql_connect()` with the same hardcoded root password (`:716`), dynamically registers a new `companydb` `ConnectionManager` config pointed at whatever `central_control.control_pkey=16` resolves to, then runs a hardcoded `emp_pkey=21` name lookup and dumps `"hello"`. Doesn't actually return an employee count despite the name. **Possible legacy cruft — verify before migrating; also mutates the live `ConnectionManager` registry as a side effect of a GET request.** | `:708-749` |

---

## 2. Summary counts

| Controller | Extends | Total actions found | Notable duplicate-name/variant pairs |
|---|---|---|---|
| LeaveRequestController.php | AppController | 39 | `addeditleave`/`addeditleave_new`; `GetLeaveBalance`/`GetLeaveBalanceNew` |
| EmployeeLeaveRequestController.php | AppController | 37 | none (no `_new` variants — divergent from admin side) |
| EmployeeLeavesController.php | AppController | 21 | — |
| EmployeeLeaveUploadController.php | AppController | 23 | `GetLeaveBalance`/`GetLeaveBalanceNew`; `form`/`form_new` |
| EmpleaveuploadController.php | AppController | 16 | `uploadandsaveempctc`/`uploadandsaveempctcleavebalance` |
| LeaveEncashmentRequestController.php | AppController | 25 | `grandLeave` name-collision with the other two controllers (different logic — finding #6) |
| LeavePolicyController.php | AppController | 12 | — |
| LeaveapiController.php | **Controller (bare, unauthenticated)** | 7 (incl. 2 internal helpers reachable as public methods) | `checkmails`/`getEmployeesCount` are debug/test cruft |

---

## 3. LeaveapiController deep-dive (see §1.8 table above for full detail)

Re-confirms the prior-pass finding verbatim and adds detail:
- `Controller/LeaveapiController.php:36` — `class LeaveapiController extends Controller` (not `AppController`).
- `Controller/LeaveapiController.php:126` — plaintext password appended to a redirect `Location` URL.
- New findings this pass: two raw `mysql_connect()` calls with a **hardcoded MySQL root password**
  (`'Localhost&*()'`) at `:113` and `:716`; a hardcoded third-party API credential pair at
  `:179-181`; hardcoded OCI SMTP credentials at `:260-261`; two apparent debug/test actions
  (`checkmails`, `getEmployeesCount`) that are live in production code and interact with
  real external endpoints/databases with no auth and no meaningful input validation.

---

## 4. Model Validation & Business Rules

### 4.1 Model-to-table mapping (confirmed via `$useTable`/`$primaryKey`)

| Model file | Class | `$useTable` | `$primaryKey` | Used by this cluster? |
|---|---|---|---|---|
| `Model/LeaveRequests.php` | `LeaveRequests` | `leaveentries` | `LEAVEENTRYID` | Yes — the actual model wired via `$uses` in all request/approval controllers |
| `Model/LeaveEntries.php` | `LeaveEntries` | `leaveentries` | `LEAVEENTRYID` | Not found in any `$uses` array in this cluster — **same table as `LeaveRequests`, appears to be a dead/parallel model; verify no other controller outside this cluster depends on it before treating `LeaveRequests` as sole authority** |
| `Model/LeavePolicy.php` | `LeavePolicy` | `leavepolicy` | `LEAVEPOLICYID` | Yes |
| `Model/LeavePolicyGroup.php` | `LeavePolicyGroup` | `leavepolicy_group` | `LEAVEPOLICY_GROUP_ID` | Yes (LeavePolicyController) |
| `Model/Leavestatus.php` | `Leavestatus` | `leavestatus` | (none declared) | Referenced by name (`LEAVESTATUS` values are hardcoded strings in controllers, e.g. `'Applied'`, `'Authorized'`, `'Approved'`) — the model itself is not visibly `$uses`'d in this cluster's controllers; status values are managed as string literals in controller code, not looked up from this model |
| `Model/EmpLeaveApproval.php` | `EmpLeaveApproval` | `emp_leave_approval` | `emp_leave_approval_pkey` | Yes — the "approval token" record inserted in `grandLeave()` (e.g. `Controller/LeaveRequestController.php:2556`) |
| `Model/EmployeeLeaveTransaction.php` | `EmployeeLeaveTransaction` | `emp_leave_transactions` | `emp_leave_transactions_pkey` | Yes — the ledger table joined in overlap checks (`EmployeeLeaveUploadController.php:2167`) |
| `Model/LeaveType.php` | `LeaveType` | `salary_head_items` | `salary_head_item_pkey` | Leave types are modeled as rows in the generic `salary_head_items` table (shared with payroll salary-head config), not a dedicated leave-type table — commented-out `$belongsTo` to `SalaryHeads` (`:18-23`) suggests an association was planned but never activated |
| `Model/EmployeeLeaveInfo.php` | `EmployeeLeaveInfo` | `emp_leave_info` | `emp_leave_info` (primary key name equals table name — likely a copy-paste error, verify actual PK column) | Not confirmed used by name in this cluster's controllers — grep didn't surface `EmployeeLeaveInfo` in `$uses` arrays checked |
| `Model/EmployeeLeaveUpload.php` | `EmployeeLeaveUpload` | `emp_leave_upload` | `emp_leave_upload_pkey` | Yes — `EmployeeLeaveUploadController` |
| `Model/EmployeeLeaveBalanceUpload.php` | `EmployeeLeaveBalanceUpload` | `leave_balance_upload` | (none declared) | Likely used by `EmpleaveuploadController` (balance-upload flow) though not confirmed via direct grep of its `$uses` |
| `Model/LeaveEncashmentMaster.php` | `LeaveEncashmentMaster` | `leave_encashment_master` | `leave_encashment_master_pkey` | Yes — `LeaveEncashmentRequestController` |

### 4.2 Validation / lifecycle hooks — **none exist**

Every model file listed above was read in full. **Not one of the 12 models in this cluster defines**:
- a `$validate` array,
- `beforeSave()`, `afterSave()`, `beforeValidate()`, or `beforeDelete()`,
- `$belongsTo` / `$hasMany` / `$hasAndBelongsToMany` associations (one commented-out `$belongsTo` exists in `Model/LeaveType.php:18-23`, never activated).

Every model body is limited to a class declaration, `$name`, `$primaryKey`, and `$useTable` — e.g.
`Model/LeaveEntries.php:7-17` (11 lines total), `Model/EmpLeaveApproval.php:24-28` (5 lines total).
**All validation, state-transition rules, and associations that exist anywhere in this domain are
implemented ad hoc inside controller methods** (`grandLeave()`, `saveLeaveEntry()`, `checkLeaveDays()`,
`criterias()`/`criterias1()`, `getLeaveBalanceForAuthOrApproval()`, `getYearlyLeaveBalanceForAuthOrApproval()`)
using raw SQL joins built in controller code rather than CakePHP model associations. This is the
single most important structural fact for the Next.js migration: **there is no model layer to port —
the "model" for this migration effort is the union of controller logic**, primarily the two
`grandLeave()` state machines (§1.1/§1.2) plus the balance/eligibility helper methods listed above.

### 4.3 The `LEAVESTATUS` state machine (as implemented in `grandLeave()`)

Confirmed states referenced across `grandLeave()` and the overlap-check query
(`Controller/EmployeeLeaveUploadController.php:2181`): `Applied`, `Authorized`, `Approved`,
`CancellationOfAuthorized`, `CancellationOfApproved`, plus an implicit `Rejected`/`Cancelled` end
state driven by the `actionType` values handled in `grandLeave()`: `'Authorize'`, `'Approve'`,
`'Reject'`, `'Approve Cancellation'`, `'Authorize Cancellation'` (see the branch conditions at
`Controller/LeaveRequestController.php:2374,2404,2527` and the mail-type switch in
`LeaveapiController::sendauthorizationmail()` at `:273` which separately enumerates
`'Approve'|'Authorize'|'cancellationOfApproval'|'cancellationOfAuthorization'|'rej'`).

Balance-checking rule: whether a request must respect a hard balance ceiling depends on
`LeavePolicy.ALLOW_NEGETIVE` (`Y`/other) looked up per `(salary_head_item_fkey, LEAVEPOLICY_GROUP_ID)`
(`Controller/LeaveRequestController.php:2389-2402`) — if `ALLOW_NEGETIVE = 'Y'`, the yearly-balance
check path is used (`getYearlyLeaveBalanceForAuthOrApproval`), otherwise the monthly-balance path
(`getLeaveBalanceForAuthOrApproval`). Note the actual balance-insufficiency rejection logic is
**commented out** in the excerpt reviewed (`Controller/LeaveRequestController.php:2411-2416,
2428-2433`) — as it stands, the code computes the balance but does not appear to block over-drawing
leave on this path; only sets a generic "Leave Approved" success message. **This looks like disabled
business logic — flag for product-owner confirmation before deciding whether the Next.js version
should (re)enforce a balance ceiling.**

### 4.4 Approval-chain rule (`leavepolicy.leval_of_approval`)

The `leavepolicy` table stores one row per `(LEAVEPOLICY_GROUP_ID, salary_head_item_fkey, leval_of_approval)`
combination, each with its own `sanction_by` (employee who approves at that level) — confirmed by the
query shape at `Controller/LeaveRequestController.php:2485` and identically in
`Controller/EmployeeLeaveRequestController.php:2590`. However, **every lookup of the "final"
sanctioning employee across both controllers hardcodes `leval_of_approval = '3'`** (5 call sites
enumerated in finding #7). There is no code path in this cluster that reads `leval_of_approval = '2'`
or `'1'` to resolve a *shorter* approval chain — the actual `Authorize`/`Approve` two-step transition
in `LEAVESTATUS` is independent of how many `leavepolicy` rows exist; the level-3 lookup is used
**only to pick the recipient of the completion/final-approval email**, not to gate the state
transition itself. Net effect: a 2-level-configured policy will still transition
`Applied → Authorized → Approved` correctly, but the "final approval" notification email
(`sendfinalapprovalmail`) will silently have no recipient. This is the concrete mechanism behind the
"2-step vs 3-step chain" note carried over from the prior research pass — confirmed and now precisely
localized to the email-recipient-resolution step, not the status-transition step.

---

## 5. Flagged as possible legacy cruft (verify before migrating)

- `Controller/LeaveapiController.php::checkmails()` (`:76-101`) — hardcoded test payload posted to a production URL.
- `Controller/LeaveapiController.php::getEmployeesCount()` (`:708-749`) — hardcoded `emp_pkey=21` debug lookup; return value doesn't match its name.
- `Controller/LeaveRequestController.php::addeditleave_new()` (`:5926`) vs `addeditleave()` (`:1032`) — duplicate apply-leave handlers; only one is presumably wired to the live front-end.
- `Controller/LeaveRequestController.php::GetLeaveBalanceNew()` (`:5644`) vs `GetLeaveBalance()` (`:2884`) — duplicate balance calculators.
- `Controller/EmployeeLeaveUploadController.php::GetLeaveBalanceNew()` (`:2203`) vs `GetLeaveBalance()` (`:1837`); `form_new()` (`:2373`) vs `form()` (`:86`).
- `Controller/EmpleaveuploadController.php::uploadandsaveempctcleavebalance()` (`:1222`) vs `uploadandsaveempctc()` (`:551`) — two near-identical bulk upload handlers in the same controller.
- `Model/LeaveEntries.php` — appears to be a dead/unused duplicate of `Model/LeaveRequests.php` (identical `useTable`/`primaryKey`, not found in this cluster's `$uses` arrays).
- Commented-out balance-rejection logic in `grandLeave()` (`Controller/LeaveRequestController.php:2411-2416,2428-2433`) — dead code that suggests a disabled/removed enforcement rule; confirm intent with product owner.

---

## 6. Suggested next steps for the migration write-up

1. Treat the two `grandLeave()` implementations (admin vs employee-portal) as the canonical
   specification of the leave state machine; diff them line-by-line before writing the Next.js
   API route to catch any behavioral divergence beyond the `criterias()` signature difference noted in §1.2.
2. Decide explicitly on verb semantics (GET vs POST/PUT/DELETE) for every mutating action, since the
   legacy code provides no signal here (finding #2).
3. Design the new schema/notification logic to either (a) always require and enforce a 3-level
   approval chain, or (b) make the final-notification recipient resolution level-count-aware — do
   not silently reproduce the current "hardcoded level 3" gap (finding #7/§4.4).
4. Externalize the `$restricted_companies` tenant list (finding #8) into configuration rather than
   porting it as an `in_array` check in code.
5. Re-implement all raw-SQL business rules (`criterias`, `criterias1`, balance helpers, overlap
   check) as parameterized queries — do not carry forward the string-interpolation pattern (finding #3).
6. Confirm whether `checkLeaveDays`'s overlap protection (`EmployeeLeaveUploadController.php:2123`)
   needs to be added to the normal `saveLeaveEntry` path, since it currently only guards bulk uploads
   (finding #9).

---

### 2.4 Payroll, Salary & Tax

# Backend Technical Report — Payroll, Salary & Tax Cluster

Scope: `legacy/Controller/{Payroll,PayrollProcess,SalaryProcessing,SalaryHeads,SalaryStructure,SalaryComponentUpload,SalaryIncrement,Tax,TaxHeads,Taxation,Taxsalarycomponents,EmpTax,EmployeeTax,FinancialYear,Arrear,Variable,FixedPaymentUpload,EmployeeIncrementReports,YearEnd}Controller.php` and their backing models in `legacy/Model/`.

General framework notes that apply to every controller below (not repeated per-row):
- CakePHP 2.x. No `SecurityComponent` and no CSRF token handling anywhere in this cluster or in `AppController.php` — all "form protection" is whatever the browser/jQuery does.
- No `$this->request->is('post')` verb-guards found anywhere in this cluster (`grep -n "request->is(" ...` across all 18 controllers returns nothing but a couple of unrelated Ajax detections). Actions distinguish "read" vs "write" purely by whether they read `$this->request->data` / `$_REQUEST` — **any of these endpoints can be hit with GET and will execute the same logic**, which matters for the Next.js route design (idempotency / method semantics need to be added, not carried over).
- Auth/session model per prior pass: `Controller/AppController.php:39-46`, `user_group` 1=admin, 2=employee. Confirmed still current.
- "Response type" column: `JSON (raw echo)` = `$this->autoRender = FALSE; ... echo json_encode(...)`, the dominant pattern; `HTML view` = renders a `.ctp` view; `redirect` = `$this->redirect(...)`; `mixed` = branches between JSON and view depending on input.

---

## 1. Controller Action Inventory

### 1.1 `Controller/PayrollController.php` — THE LIVE PAYROLL RUN (extra detail per instructions)

`$uses` (`PayrollController.php:53`): `EmployeeDetails, CompanyContactInfo, Units, AttendanceRegister, MonthlyCTC, GrossSalary, TotalDeductions, MonthlyAmount, Payrollmaster, EmpSalarySlip, EmployeeProfessionalDetails`. Component: `MasterdataManagement` (`:55`).

Entry point confirmed from `View/Dashboard/index.ctp:441-584` — "💰 Process Payroll" link calls `viewPayroll()` → `/Payroll/showprocesspayroll`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `showprocesspayroll` | GET/POST | HTML view | Landing page for the "process payroll" tab; loads branch combo, plan/entitlement info, branch-restricted employee list for `payro_priv` users | `PayrollController.php:57-112` |
| `showapprovepayroll` | GET/POST | HTML view | Landing page for "approve payroll" tab; near-duplicate of above (plan/planId fetched **twice**, `:145-194`, dead code) | `PayrollController.php:114-196` |
| `FilterList` | POST (reads `request->data`) | JSON (raw echo) | Deletes stray `attendance_register` rows for `emp_fkey=0`, then calls stored proc **`payroll_master_insert(branch, month, user_id, @error)`** to seed `payroll_master` for the branch/month. This is the "generate payroll rows" trigger. | `PayrollController.php:198-217`, proc call `:213` |
| `showprocesspayrolltab($processed=0)` | GET/POST | HTML view (partial/tab) | Loads employee list for the process-payroll grid, with branch-scoping via `get_branch_code_abs_fn()` SQL function for restricted `user_group==2` users of certain company codes | `PayrollController.php:219-296` |
| `listpayroll` | GET/POST (`$_REQUEST`) | JSON (raw echo) | Datagrid feed: unprocessed payroll rows (`action` empty/NULL) with pagination, branch/month/employee filters, joined to `emp_details`/`termination` for resigned flag | `PayrollController.php:298-434` |
| `listpayrollold` | GET/POST | JSON (raw echo) | Legacy datagrid feed sourced from `attendance_register` + `MonthlyCTC`/`GrossSalary`/`TotalDeductions`/`MonthlyAmount` models instead of `payroll_master` — **possible legacy cruft, superseded by `listpayroll`, verify before migrating** | `PayrollController.php:436-554` |
| `listprocessedpayrollold` | GET/POST | JSON (raw echo) | Legacy variant of processed-list, same `attendance_register`-based approach — **possible legacy cruft** | `PayrollController.php:556-632` |
| `listprocessedpayroll` | GET/POST | JSON (raw echo) | Datagrid: processed rows (`action` not empty/NULL), also computes prior-month net salary diff per employee | `PayrollController.php:634-789` |
| **`processpayroll`** | POST (`request->data`) | JSON (raw echo `{success}`) | **The core "Process" stage.** For each selected `(emp, payroll_master_pkey)`: (1) optionally flips `payroll_master.tax_include` Y/N via raw UPDATE (`:838,848`); (2) **branches by company code allow-list** (`$specialCompanies`, `:818`) — non-special companies call stored proc **`calculate_salary_main_prc`** via `AttendanceRegister::calculateSalaryMainPrc()` (`:872`), special companies call **`salary_process_prc`** via `AttendanceRegister::salaryProcessPrc()` (`:874`); (3) backfills `payroll_master.desig` from a raw SQL lookup; (4) for special companies, re-evaluates each `emp_salary_slip.remarks` formula using **`eval()`** (`:943` — guarded by a regex `^[0-9+\-*/().\s]+$` allow-list, but the regex is applied only to decide whether to divert to a fallback path, not to block the `eval()` call at `:943` which runs unconditionally on `!is_numeric($formula_from_remarks)`), applies ESI ceil/round rounding rules, then calls stored proc **`tax_salary_process_prc`** via `Payrollmaster::taxSalaryProcessPrc()` (`:1013`); (5) unconditionally (all companies) re-derives a "combined base value" per salary-slip row by tokenizing a structure formula (regex `\d+_[A-Za-z_]+|monthsal`), substituting live DB values, and running it through a **second `eval()`** (`:1155`, plus a third at `:1166` for a multiplier sub-expression) before writing `emp_salary_slip.combined_base_value`. This is the eval-based formula engine referenced in the prior pass, now traced to 3 separate `eval()` call sites inside one action. | `PayrollController.php:790-1204` |
| `holdProcessPayroll` | POST | JSON (raw echo) | Sets `payroll_master.action = 'Hold'` for selected pkeys via `Payrollmaster->save()` | `PayrollController.php:1207-1227` |
| `removePayrollEntry` | POST | JSON (raw echo) | Reverses a processed entry: clears `action`, stamps `end_date_effective` on `emp_salary_slip` rows, and (for non-special companies) resets `emp_variables_upload.status=0` for fixed-type variable uploads of that month — raw SQL with pkey list interpolated directly into the IN clause (`:1258`, `:1280`, `:1285`) | `PayrollController.php:1229-1299` |
| `showsalaryslip($payroll_master_pkey=0)` | GET | HTML view | Renders the payslip breakdown grouped by `salary_heads`, direct vs indirect components, with a company-specific (`KWMT`) sort order | `PayrollController.php:1301-1400` |
| `showapprovepayrolltab($approved=0)` | GET/POST | HTML view (partial/tab) | Employee list for the approve-payroll grid, same branch-restriction logic as `showprocesspayrolltab` | `PayrollController.php:1403-1483` |
| `listapprovepayroll` | GET/POST | JSON (raw echo) | Datagrid: rows with `action IN ('Processed','Verified')`, i.e. awaiting approval | `PayrollController.php:1485-1626` |
| `listapprovedpayroll` | GET/POST | JSON (raw echo) | Datagrid: rows with `action='Approved'`; conditionally exposes employee email for `GLET`/`SHYD` company codes | `PayrollController.php:1628-1756` |
| **`approvepayroll`** | POST | JSON (raw echo `{success:1}`) | **The "Approve" stage.** For each selected pkey, looks up branch/month/emp, then calls stored proc **`payroll_master_approve(branch, month, emp, uid, @perror_message)`** (`:1785`) — note the proc's error output param `@perror_message` is never read back/checked in PHP; finally bulk-sets `payroll_master.action='Approved'` regardless of what the proc reported | `PayrollController.php:1757-1794` |
| `generatePasswords` | GET/POST | JSON (raw echo) | Generates a random password per active employee with an email, writes to `passwords` table via raw INSERT (SQL-interpolated, no parameter binding), and **emails the plaintext password** via PHPMailer using hardcoded SMTP creds (see Security section below) | `PayrollController.php:1796-1891` |

**Stored procedures invoked from `PayrollController.php`:** `payroll_master_insert` (`:213`), `calculate_salary_main_prc` (`:872`), `salary_process_prc` (`:874`), `tax_salary_process_prc` (`:1013`), `payroll_master_approve` (`:1785`). All via `$model->query("CALL proc(...)")` with values string-interpolated directly (no bound params) — classic SQL-injection-shaped code even though inputs mostly originate from session/internal IDs rather than free-text user input.

**Confirmed security leak:** hardcoded Oracle Cloud SMTP username/password at `PayrollController.php:1857-1860` (`mail->Host`, `mail->Username`, `mail->Password`), sent in cleartext PHP source — matches prior-pass finding, exact lines shifted slightly (was 1857-1861, now 1857-1860 after recent edits) but still present and unresolved.

---

### 1.2 `Controller/PayrollProcessController.php` — ORPHANED 6-stage pipeline

`$uses` (`:51`): `EmployeeDetails, Units, AttendanceRegister, MonthlyCTC, GrossSalary, TotalDeductions, MonthlyAmount, Payrollmaster, EmpSalarySlip, Payrolltransactions`.

**Reachability re-verified this pass:** `grep -rl "PayrollProcess" View/` returns only files inside `View/PayrollProcess/` itself; `grep -rn "PayrollProcess" View/Dashboard/ View/Elements/ View/Layouts/` and `Config/` return **zero** hits. No menu, no dashboard link, no route override references this controller. **Confirmed orphaned — possible legacy cruft, verify before migrating** (do not port unless product explicitly wants the richer 6-stage flow revived).

Stages implemented (process → provisional → pre-audit → finalization → payment-approval → salary-approval), each with its own `show*`, `show*tab`, `list*`, and action endpoint, all echoing JSON like `PayrollController`:

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `showprocesspayroll` / `showapprovepayroll` | GET/POST | HTML view | Stage landing pages | `:54-73` |
| `FilterList` | POST | JSON | Same `payroll_master_insert` proc trigger as live controller | `:74-88`, proc `:83` |
| `showprocesspayrolltab($processed=0)` | GET/POST | HTML view | Employee list for process tab | `:89-114` |
| `listpayroll` | GET/POST | JSON | Unprocessed datagrid feed | `:115-202` |
| `listprocessedpayroll` | GET/POST | JSON | Processed datagrid feed, reads `payroll_transactions.remarks` for provisional status | `:203-320` |
| `processpayroll` | POST | JSON | Calls `salary_process_prc` (`:361`) + same `eval()`-based formula re-derivation as live controller | `:321-448` |
| `holdProcessPayroll` | POST | JSON | Hold action | `:449-470` |
| `removePayrollEntry` | POST | JSON | Reverses processing incl. `payroll_transactions` cleanup | `:471-537` |
| `showsalaryslip($pkey=0)` | GET | HTML view | Payslip view | `:538-624` |
| `showapprovepayrolltab` | GET/POST | HTML view | Approve tab | `:625-648` |
| `listapprovepayroll` / `listapprovedpayroll` | GET/POST | JSON | Approve-stage datagrids | `:649-810` |
| `approvepayroll` | POST | JSON | Calls `payroll_master_approve` (`:839`) | `:811-850` |
| `listprovisionalpayroll`, `showprovisionalpayroll`, `showprovisionalpayrolltab` | GET/POST | JSON/HTML | **Provisional stage** — stage 2/6, not present in live controller | `:851-1020` |
| `addremarks`…`addremarks5`, `addreversalremarks`…`addreversalremarks3`, `addrejectremarks` | GET/POST | HTML view (each just `$this->render(...)` a small remarks-entry partial) | Remarks-entry stub actions, mostly near-duplicates of each other (`addremarks2..5` are copy-pasted variants) — **possible legacy cruft, several of these look unused even within this orphaned controller** | `:1021-1066` |
| `showpreauditpayroll`, `showpreauditpayrolltab`, `addremarkspreaudit`, `preauditpayroll`, `listpreauditpayroll`, `listpreauditedpayroll` | GET/POST | JSON/HTML | **Pre-audit stage** — stage 3/6 | `:1067-1434` |
| `showfinalizationpayroll`, `showfinalizationpayrolltab`, `listfinalizationpayroll`, `listfinalizedpayroll`, `finalizationpayroll` | GET/POST | JSON/HTML | **Finalization stage** — stage 4/6, writes `payroll_transactions.final_status='finalization'` | `:1435-1791` |
| `paymentprocessedpayrolllist`, `paymentpayroll`, `paymentpayrolltab`, `listpaymentapprovalpayroll`, `PaymentapprovePayroll`, `listpaymentapprovedpayroll`, `processpayment` | GET/POST | JSON/HTML | **Payment-approval stage** — stage 5/6 | `:1792-2297` |
| `approvalpayroll`, `approvalpayrolltab`, `processapprovalpayroll` | GET/POST | JSON/HTML | Generic approval-stage variant | `:2298-2430` |
| `listsalaryapprovalpayroll`, `SalaryapprovePayroll`, `listsalaryapprovedpayroll` | GET/POST | JSON/HTML | **Salary-approval stage** — stage 6/6 | `:2431-2723` |
| `rejectPayrollEntry($pkeys='', $status='')` | GET/POST | JSON | Rejects a payroll entry, resets `payroll_master.action='Processed'` and `payroll_transactions` flags | `:2724-2765` |

Stored procs used here: `payroll_master_insert` (`:83`), `salary_process_prc` (`:361`), `payroll_master_approve` (`:839`, `:1760`, `:2254`, `:2390`) — i.e. this pipeline reuses the same procs as the live `PayrollController` but layers a 6-stage `payroll_transactions.final_status` state machine (`provisional → audited → finalization → paymentapproval → salaryapproval`) on top that the live controller does not have.

---

### 1.3 `Controller/SalaryProcessingController.php` — thin dashboard-shell controller

Only 78 lines. `$uses` is a large grab-bag (`:6`) but only two actions:

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML view | Renders the Salary Processing landing/dashboard shell, loads branch combo | `SalaryProcessingController.php:10-16` |
| `getPayrollFeatures` | GET | JSON (raw echo) | Plan/entitlement-gated feature list for the payroll module — looks up `CentralUserCredentials.plan_id` on `controldb`, cross-references `Features`/`PlanFeature` to build an enabled/disabled feature flag list | `SalaryProcessingController.php:17-75` |

---

### 1.4 `Controller/SalaryHeadsController.php` (master data: salary heads/items)

`$uses` (`:50`): `SalaryHeadItems, SalaryHeads`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET/POST | HTML view | List/manage salary heads | `:57-255` |
| `form` | GET | HTML view | Add/edit head form | `:256-277` |
| `addsalaryheads` | POST | redirect-only | Save a salary head | `:278-314` |
| `form_items($id,$kid)` | GET | HTML view | Add/edit head-item form | `:315-343` |
| `addsalaryheaditems` | POST | redirect-only | Save a salary head item | `:344-387` |
| `DeleteHead` | GET/POST | redirect-only | Soft-deletes a head | `:388-402` |
| `checksalaryheadexists($codecount='', $SALARYHEAD_ID=0)` | GET/POST | JSON | Uniqueness check for AJAX validation | `:403-421` |
| `checksalaryheaditemexists($SALARYHEADITEM_ID=0)` | GET/POST | JSON | Uniqueness check | `:422-438` |
| `checkshortnameexists($short_name=0)` | GET/POST | JSON | Uniqueness check | `:439-460` |

---

### 1.5 `Controller/SalaryStructureController.php` (salary structure definitions)

`$uses` (`:51`): `SalaryHeadItems, Menu, SalaryHeads, SalaryStructures, SalaryStructureDetails, EmployeeProfessionalDetails, EmployeeConfig, EmployeeDetails`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML view | Structures list page | `:58-75` |
| `liststructures` | GET/POST | JSON | Datagrid feed of structures | `:76-96` |
| `addEmpToSalConfig` | POST | JSON | Assigns employee to a salary structure/config | `:97-117` |
| `removeEmpFromSalConfig` | POST | JSON | Removes assignment | `:118-140` |
| `listemployeesinsalary` | GET/POST | JSON | Employees already on a structure | `:141-184` |
| `listemployeesforsalary` | GET/POST | JSON | Employees eligible to add | `:185-220` |
| `savesalarystructuresetup` | POST | JSON | Saves structure-to-employee setup | `:221-334` |
| `loadSalaryStructureDetails($structure_id)` | GET | JSON | Loads component rows for a structure | `:335-392` |
| `getSalaryHeadItems` | GET | JSON | Lookup list of head items | `:393-402` |
| `view($structure_id=0)` | GET | HTML view | Read-only structure view | `:403-503` |
| `form($structure_id=0)` | GET/POST | HTML view (renders `"form"` explicitly, `:390` in prior block is unrelated — actual render at `:504+`) | Add/edit structure form incl. formula/limit fields | `:504-639` |
| `calculateFixedLimit($occu,$amount,$structure_define)` | internal helper (not a route target in practice, but public) | n/a | Computes a fixed-limit amount for structure definitions | `:640-662` |
| `search($array,$key,$value)` | internal helper | n/a | Generic array search utility | `:663-679` |
| `delete` | POST | JSON | Deletes a structure | `:680-720` |
| `checkstructureexists($structure_id=0)` | GET/POST | JSON | Uniqueness check | `:721-740` |

---

### 1.6 `Controller/SalaryComponentUploadController.php` (bulk CTC/component upload)

`$uses` (`:52`) is a very large grab-bag including `EmpSalaryCompUpload, SalaryHeadItems, ComponentIncrement, SalaryStructures, EmployeeSalaryStructure`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML view | Upload landing page | `:59-117` |
| `showsalaryupload` | GET | empty (no body) | Stub, likely dead | `:118` |
| `branchwiss($branch='')` | GET/POST | JSON | Branch-scoped lookups | `:119-141` |
| `saveuploads` | POST | JSON | Bulk-saves uploaded CTC components; drives `ctc_component_upload_prc` (`:193`) and `salary_structure_limit_prc` (`:276`) | `:142-292` |
| `saveuploads_od` | POST | JSON | On-duty/variant of above | `:293-341` |
| `component_upload` | GET | HTML view | Upload form page | `:342-411` |
| `getComp` | GET/POST | JSON | Component lookup | `:412-438` |
| `load` | GET/POST | JSON | Generic loader | `:439-457` |
| `listemployee` | GET/POST | JSON | Employee list for upload targeting | `:458-593` |
| `filterjson($branch='', $structure='')` | GET/POST | JSON | Filter combo data | `:594-636` |
| `jsonbranch($branch=0)` | GET/POST | JSON | Branch lookup | `:637-673` |
| `downloadempctcformat($employee='', $branch='', $structure='')` | GET | file download (CSV/Excel via PHPExcel-style stream, not HTML) | Generates a downloadable CTC upload template | `:674-842` |
| `uploadandsaveempctc($ctcuploadtype=0)` | POST (multipart) | JSON | Parses an uploaded CTC file and persists via `ctc_component_upload_prc` (`:1018`) / `salary_structure_limit_prc` (`:1100`) | `:843-1158` |
| `loadcomponents($emp_fkey='')` | GET/POST | JSON | Loads a given employee's current components | `:1159-1170` |
| `jsonbranchemp($branch=0, $structure=0)` | GET/POST | JSON | Branch+structure filtered employee lookup | `:1171-1226` |
| `component` | GET | HTML view | Component management page | `:1227-1302` |
| `listincrements` | GET/POST | JSON | Increment history list | `:1303-1348` |
| `itemIncrementForm` | GET | HTML view | Per-item increment form | `:1349-1384` |
| `saveItemIncrement` | POST | JSON | Saves a per-item increment | `:1385-1420` |
| `componentAllocate($sal_fkey=0)` | GET | HTML view | Component allocation form | `:1421-1465` |
| `saveAllocate` | POST | JSON | Saves allocation | `:1466-1535` |
| `removeAllocate` | POST | JSON | Removes allocation | `:1536-1560` |
| `getEmployeesByTypeValue` | GET/POST | JSON | Filter helper | `:1561-1610` |
| `getTypeValues` | GET/POST | JSON | Filter helper | `:1611-1660` |
| `saveComponentAllocate` | POST | JSON | Contains the first of 5 `eval()` sites in this file for formula-driven salary_amount recompute (`:1761`) | `:1661-1819` |
| `saveComponentUploads` | POST | JSON | Bulk save with `eval()` at `:1987` | `:1820-2007` |
| `saveComponentAllocateKWMT` | POST | JSON | Company-specific (`KWMT`) variant with `eval()` at `:2199` | `:2008-2258` |

`eval()` sites in this file: `:253, :1077, :1761, :1987, :2199` — same "reconstruct salary_amount from a stored `remarks` formula string" pattern as `PayrollController::processpayroll`.

---

### 1.7 `Controller/SalaryIncrementController.php` (largest file in scope, 5,814 lines — increments, hikes, item-level recalculation)

`$uses` (`:47`) again a broad grab-bag including `SalaryHike, SalaryHikeDetail, EmpSalaryCompUpload`.

Given the size, actions are grouped by purpose rather than each individually narrated in full; all follow the `autoRender=FALSE; echo json_encode(...)` pattern unless noted.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML (empty body) | Stub | `:52` |
| `jsons($branch='')` | GET/POST | JSON | Branch lookups | `:54-95` |
| `listemployees` | GET/POST | JSON | Employee list for increment targeting | `:96-253` |
| `downloadempctcformat(...)`, `downloadempctcformatItem(...)` | GET | file download | Increment upload templates (whole-structure and item-level variants) | `:254-820`, `:521-821` |
| `uploadandsaveempctc(...)`, `uploadandsaveempctcitem(...)` | POST (multipart) | JSON | Bulk increment upload+save. Calls stored procs `ctc_component_upload_prc` (`:3274`), `salary_structure_limit_prc` (`:2447,:3328`), `calculate_emp_salary_breakup` (`:2102`) | `:822-1592`, `:1215-1592` |
| `salaryIncrementForm`, `saveIncrement`, `saveItemIncrement` | GET/POST | HTML/JSON | Single-employee increment entry + save; `eval()` at `:2389, :3084` | `:1592-2066` |
| `calcSalaryStructure`, `alterSalaryStructure` | POST | JSON | Recalculates/alters a structure; calls `copy_salary_structure_to_new` proc (`:3863`) and `calculate_emp_component_breakup` proc (`:3950`); `eval()` at `:3315, :3528` (via nested helpers) | `:2067-2495` |
| `onEffectiveDateChange`, `getSalaryStructure`, `componentAllocate`, `saveAllocate`, `removeAllocate` | GET/POST | JSON | Structure component management, mirrors `SalaryComponentUploadController` patterns | `:2496-2847` |
| `getEmployeesByTypeValue`, `getTypeValues`, `saveComponentAllocate`, `saveComponentUploads`, `saveComponentAllocateKWMT` | POST | JSON | Duplicated (near-verbatim) copies of the same-named actions in `SalaryComponentUploadController.php` — **strong candidate for shared-service consolidation in the Next.js rewrite rather than porting as two parallel copies** | `:2848-3585` |
| `employeelist`, `employeelistPending` | GET/POST | JSON | Increment-eligible employee lists | `:3586-3711`, `:5591-5767` |
| `process`, `processItem` | POST | JSON | Batch-applies increments; `eval()` at `:4034, :4175` (`@eval("\$calculated_value = ...")`) and `:4603` | `:3712-3936`, `:4225-4683` |
| `onIncrementChange`, `onIncrementChangeNew` | GET/POST | JSON | Recalculation on UI field change | `:3937-4224` |
| `itemIncerementReport`, `incerementReport` | GET/POST | HTML/JSON | Increment reports | `:4684-4990` |
| `viewSalaryIncrementForm($salary_hike_pkey=0, $empName)` | GET | HTML view | Read-only increment view; **note: `$empName` has no default value, so calling this action with only one URL segment throws a CakePHP "Missing argument" error** — worth flagging for the Next.js route signature | `:4991-5075` |
| `getTempSalaryStructure(...)` | GET/POST | JSON | Preview calc; calls `structure_preview_prc` (`:5099`) | `:5076-5181` |
| `deleteIncrement`, `fileUpload`, `download($salary_hike_pkey)` | POST/GET | JSON / file download | Delete, attachment upload, and download of increment records | `:5182-5590` |
| `getGrossByEmp($emp_pkey=null)`, `getSummaryValue` | GET/POST | JSON | Supporting lookups | `:5259-5419`, `:5768-5814` |

`eval()` sites in this file (7 total): `:2389, :3084, :3315, :3528, :4034, :4175, :4603`.

---

### 1.8 `Controller/TaxController.php` (employee tax setup/regime, Form-16)

`$uses` (`:55`): `EmployeeTaxsalsumNew, EmpTaxSalTransNew, EmpTaxSalTrans, EmployeeTaxsalsum, EmpTaxRegime, TaxSave, SalaryHeadItems, SalaryHeads, FinancialYear, EmployeeTaxTransactions, EmployeeDetails`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML view | Tax module landing | `:62-67` |
| `form($tax_detail=0,$tax_head=0,$emp=0)` | GET | HTML view | Tax entry form | `:68-77` |
| `Tabs` | GET/POST | JSON/HTML | Tab content loader | `:89-116` |
| `Calculate($ctc=0)`, `Calculate_new($ctc=0)` | GET/POST | JSON | Old-regime / new-regime tax calculators (pure PHP arithmetic against slab tables, not stored procs) | `:117-172` |
| `setup($emp_pkey=0)` | GET/POST | HTML view (large, 400 lines) | Main employee tax-declaration setup screen | `:173-570` |
| `setupshow($emp_pkey=0)`, `setupshow_new($emp_pkey=0)` | GET | HTML view | Read-only tax setup display (old/new regime) | `:571-710` |
| `Proccess($emp_pkey=0)` | POST | JSON | Processes/saves tax setup [sic: misspelled action name] | `:711-730` |
| `Choosetax($emp_pkey=0,$fin_year=0,$tax='')` | GET/POST | JSON | Regime selection | `:731-778` |
| `setupupload_new($emp_pkey=0)`, `addnewtaxdocument_modal(...)`, `setupdownload_new($emp_pkey=0)`, `downloadtaxdocument_modal(...)` | POST/GET | JSON / file download | Tax-proof document upload/download | `:779-877` |
| `formSixteen`, `formSixteenDownload(...)` | GET | file download (PDF) | Form-16 generation/download | `:878-1143` |
| `getEmployeesByBranch` | GET/POST | JSON | Employee filter | `:1144-1175` |

---

### 1.9 `Controller/TaxHeadsController.php` (master data: tax types/heads/detail fields)

`$uses` (`:51`): `TaxType, TaxHead, TaxHeadDetail`. CRUD-shaped, mirrors `SalaryHeadsController.php` structurally.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET/POST | HTML view | List tax types/heads | `:58-241` |
| `form` | GET | HTML view | Add/edit form | `:242-261` |
| `addtaxtypes` | POST | JSON | Save tax type | `:262-294` |
| `Deletetaxtype` | POST | JSON | Soft-delete tax type | `:295-310` |
| `form_items($id,$kid)` | GET | HTML view | Add/edit tax head form | `:311-333` |
| `addtaxheads` | POST | JSON | Save tax head | `:334-371` |
| `Deletetaxhead` | POST | JSON | Soft-delete tax head | `:372-388` |
| `gettaxtypename($id)` | GET | JSON | Lookup | `:389-402` |
| `Gettaxnamelist` | GET | JSON | Lookup list | `:403-412` |
| `add_details($id,$kid)` | GET/POST | HTML view | Manage tax-head detail fields (dynamic form-field definitions per head) | `:413-465` |

---

### 1.10 `Controller/TaxationController.php` (AJAX backend for employee-facing tax-head data entry)

`$uses` (`:48`): `CentralControl, UserCredentials, TaxType, TaxHead, TaxHeadDetail, EmployeeTaxTransactions`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `getTaxTypes` | GET/POST | JSON | Lookup | `:52-64` |
| `getTaxHeads` | GET/POST | JSON | Lookup | `:65-80` |
| `getTaxHeadFields` | GET/POST | JSON | Dynamic field metadata for a tax head | `:81-138` |
| `getTaxHeadDetails($tax_heads_fkey=0)` | GET/POST | JSON | Detail rows for a head | `:139-144` |
| `deletedoc($emp_pkey=0,$head=0,$detail=0,$document=0)` | POST | JSON | Deletes an uploaded proof document | `:145-157` |
| `saveemployeetaxheads($empPkey=0)` | POST | JSON | Saves selected tax heads for an employee | `:158-241` |
| `uploadFile` | POST (multipart) | JSON | Uploads a tax-proof document | `:242-411` |
| `savetaxdetail(...)`, `saveemployeetaxheaddetails(...)` | POST | JSON | Saves individual detail values | `:412-541` |
| `loadEmpTaxationDetails($empPkey=0)`, `loadEmpTaxHeadDetails(...)`, `loadEmpTaxHeadDocuments(...)` | GET/POST | JSON | Read-back of saved tax data/documents | `:542-619` |
| `getPFTaxValue($emp_fkey=0)` | GET/POST | JSON | PF-specific tax value lookup | `:620-626` |

---

### 1.11 `Controller/TaxsalarycomponentsController.php` (tiny — maps tax heads to salary components)

`$uses` (`:57`): `Taxsalarycomponents, SalaryHeadItems`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML view | List/manage mappings | `:63-72` |
| `save` | POST | JSON/redirect | Save mapping | `:73-92` |

---

### 1.12 `Controller/EmpTaxController.php` (near-empty — likely legacy/stub)

`$uses` (`:50`): `TaxHeads, Units, AttendanceRegister, DbConfig`. Only 59 lines, single action `index` (`:52`), no `request->is`/JSON/render patterns detected at all. **Possible legacy cruft — verify whether this controller is still routed to anywhere before migrating; functionally superseded by `EmployeeTaxController.php` / `TaxationController.php`.**

---

### 1.13 `Controller/EmployeeTaxController.php` (older, parallel tax-setup controller)

`$uses` (`:50`): `EmpTaxSalTrans, EmployeeTaxsalsum, TaxSave, SalaryHeadItems, SalaryHeads, FinancialYear, EmployeeTaxTransactions, EmployeeDetails`. Structurally near-identical to `TaxController.php`'s `setup`/`Calculate`/`Proccess` trio — **likely an earlier iteration kept alongside the newer `TaxController.php`; verify which one the current UI actually links to before deciding what to port.**

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML view | Landing | `:57-64` |
| `Tabs` | GET/POST | HTML/JSON | Tab loader | `:65-69` |
| `Calculate($ctc=0)` | GET/POST | JSON | Tax calculator | `:70-101` |
| `setup` | GET/POST | HTML view | Setup screen (no `$emp_pkey` param, unlike `TaxController::setup`) | `:102-257` |
| `Employeesetup($emp_pkey=0)` | GET/POST | HTML view | Per-employee setup variant | `:258-413` |
| `setupshow($emp_pkey=0)` | GET | HTML view | Read-only display | `:414-488` |
| `Proccess($emp_pkey=0)` | POST | JSON | Save [sic: misspelled] | `:489-500` |

---

### 1.14 `Controller/FinancialYearController.php`

`$uses` (`:48`): `UserCredentials, CompanyContactInfo, Units, FinancialYear`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML view | List financial years | `:54-62` |
| `form` | GET | HTML view | Add/edit form | `:63-92` |
| `save` | POST | redirect-only | Save a financial year record | `:93-133` |
| `listfinyears` | GET/POST | JSON | Datagrid feed | `:134-199` |
| `deleteFinYear` | POST | JSON | Soft-delete | `:200-220` |

---

### 1.15 `Controller/ArrearController.php` (arrear/back-pay processing — mirrors the live PayrollController 2-stage flow)

`$uses` (`:51`): `EmployeeDetails, Units, AttendanceRegister, MonthlyCTC, GrossSalary, TotalDeductions, MonthlyAmount, Payrollmaster, EmpSalarySlip, EmployeeCTC, Payrollarrearmaster, SalaryHeadItems, EmpArrearSalarySlip, PayrollArrearComponents`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `showprocesspayroll` | GET | HTML view | Landing page | `:54-72` |
| `listcomponents` | GET/POST | JSON | Available arrear salary components for a branch | `:73-101` |
| `filter_month($branch='')` | GET/POST | JSON | Eligible payout months; calls SQL function `arrear_month_fn(branch, month, user_id)` (`:183`) | `:126-161` |
| `showapprovepayroll` | GET | HTML view | Landing page | `:162-171` |
| `FilterList` | POST | JSON | Seeds arrear rows, similar role to `PayrollController::FilterList` | `:172-207` |
| `showprocesspayrolltab($processed='')` | GET/POST | HTML view | Employee list for the process tab | `:208-258` |
| `listpayroll`, `listpayrollold`, `listprocessedpayrollold`, `listprocessedpayroll` | GET/POST | JSON | Datagrid feeds (current + legacy variants, mirrors `PayrollController` pattern — the `*old` ones are **possible legacy cruft**) | `:259-701` |
| `processpayroll` | POST | JSON | Arrear-equivalent of `PayrollController::processpayroll`; contains its own `eval()` at `:866` for formula-based recompute | `:702-896` |
| `holdProcessPayroll` | POST | JSON | Hold action | `:897-918` |
| `removePayrollEntry` | POST | JSON | Reverse processing | `:919-954` |
| `showarrearsalaryslip($payroll_arrear_master_pkey=0)` | GET | HTML view | Arrear payslip breakdown | `:955-1052` |
| `showapprovepayrolltab($approved=0)` | GET/POST | HTML view | Approve tab | `:1053-1069` |
| `listapprovepayroll`, `listapprovedpayroll` | GET/POST | JSON | Approve-stage datagrids | `:1070-1235` |
| `approvePayroll` | POST | JSON | Calls stored proc **`payroll_arrear_approve(branch, month, payout_month, emp, uid, @perror_message)`** (`:1280`) — the arrear-specific counterpart to `payroll_master_approve` | `:1236-1315` |

---

### 1.16 `Controller/VariableController.php` (variable-pay bulk upload)

`$uses` (`:21`): `UserCredentials, SalaryHeadItems, EmployeeDetails, Units, FinancialYear, SalaryHeads, EmployeeVariableUpload, EmployeeProfessionalDetails`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML view | Landing page | `:24-82` |
| `variableupload` | GET | empty (stub) | `:83-86` |
| `downloadvariableuploadform($salaryhead, $branch='', $emp_pkey=0)` | GET | file download | Upload template generator | `:87-240` |
| `uploadandsaveempvar($salary_head_item=0, $month=0)` | POST (multipart) | JSON | Parses+saves uploaded variable-pay file; blocks re-upload for months already payroll-processed (`:598`) | `:241-419` |
| `employeelistvariable` | GET/POST | JSON | Employee list for variable entry | `:420-522` |
| `form($id=0)` | GET | HTML view | Manual entry form | `:523-563` |
| `deleteEmployees` | POST | JSON | Removes variable-pay rows | `:564-578` |
| `VariableSave` | POST | JSON | Manual save path; also payroll-processed guard | `:579-661` |

---

### 1.17 `Controller/FixedPaymentUploadController.php` (fixed-payment/EMI-style recurring payment bulk upload)

`$uses` (`:22`): `UserCredentials, SalaryHeadItems, EmployeeDetails, Units, FinancialYear, SalaryHeads, EmployeeVariableUpload, EmployeeProfessionalDetails, EmployeeFixedPaymentUpload`. Structurally a near-duplicate of `VariableController.php` with date-range/occurrence fields (EMI-style recurring payments).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML view | Landing | `:26-59` |
| `branchemployee($branch)` | GET/POST | JSON | Employee lookup | `:60-75` |
| `downloadfixedpaymentuploadform(...)` | GET | file download | Upload template | `:76-350` |
| `uploadandsavefixedpayment($salary_head_item=0,$month=0,$occurance=0)` | POST (multipart) | JSON | Parses+saves fixed-payment schedule, validates start-date vs joining date and EMI month math | `:351-617` |
| `employeelistfixedpayment` | GET/POST | JSON | Employee list | `:618-747` |
| `form($id=0)` | GET | HTML view | Manual entry form | `:748-787` |
| `checkdoj($start_month='',$emp_fkey='')` | GET/POST | JSON | Date-of-joining validation | `:788-811` |
| `deleteEmployees` | POST | JSON | Delete rows | `:812-828` |
| `FixedPaymentSave` | POST | JSON | Manual save; payroll-processed guard | `:829-947` |

---

### 1.18 `Controller/EmployeeIncrementReportsController.php` (reporting)

`$uses` (`:52`): `LeavePolicyGroup, HolidayGroup, CentralControl, Designation, CompanyContactInfo, UserCredentials, EmployeeDetails, EmployeeProfessionalDetails, Departments, Verticals, Units, ReportCriterias, DayTimeProcedures, ReportAudit`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports`, `attendance`, `miscellanious`, `sallary`, `statutory` | GET | HTML view | Report-category landing pages | `:56-125` |
| `changereporttype($type='')` | GET/POST | JSON | Switches active report type | `:126-149` |
| `addreportcriteria(...)` | GET/POST | HTML view | Adds a dynamic filter-criteria row | `:150-171` |
| `loadcriteriaitems($index, $str_criteria='')` | GET/POST | HTML view | Loads criteria item options | `:172-189` |
| `listcriteriaitems($str_criteria='')` | GET/POST | JSON | Datagrid of filtered increment/report rows (large — 240 lines) | `:190-429` |
| `reportAudit($type,$mode)` | GET/POST | HTML view | Report-run audit trail | `:430-521` |
| `generatereport($type='',$mode='')` | GET/POST | file download / HTML | Generates the report output | `:522-538` |
| `listemployeefields` | GET/POST | JSON | Field-picker metadata | `:539-622` |
| `generateemployeincrement($mode)` | GET/POST | HTML view | Increment-specific report generator | `:623-860` |

---

### 1.19 `Controller/YearEndController.php` (fiscal year-end rollover, incl. leave auto-approval)

`$uses` (`:48`): `UserCredentials, CentralControl, CentralUserCredentials, Registrations, Currency, Country, MasterDb, EmployeeMenu, Units, EmployeeDetails`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index` | GET | HTML view | Year-end landing page; reads current open financial year via raw SQL against `fin_year` | `:53-132` |
| `loaders` | GET/POST | JSON | Supporting lookups | `:133-136` |
| `approve` | POST | JSON | **Auto-approves all pending leave entries** (`LEAVESTATUS IN ('Applied','Authorized')`) within the financial-year window via bulk raw UPDATE, then sends notification emails using PHPMailer with **hardcoded Zoho SMTP credentials** (`Host: smtp.zoho.com`, `Username: info@mypayrollmaster.in`, `Password: welcome123`) — confirmed leak at `YearEndController.php:201-204`, matches prior-pass finding | `:137-152` |
| `processleave` | POST | JSON | Leave-status processing step of the rollover | `:153-175` |
| `notice` | GET/POST | JSON | Sends year-end notice emails (reuses the same SMTP block) | `:176-220` |

---

## 2. Model Validation & Business Rules

**Headline finding: every model backing this cluster is anemic.** None of the ~30 models read below define a `$validate` array, a `beforeSave`/`beforeValidate`/`afterSave`/`afterFind` hook, or (in the working code — associations are present but commented out) `belongsTo`/`hasMany` relations. Business rules live in one of two places: (a) raw SQL/`eval()` directly in the controllers (Section 1), or (b) MySQL stored procedures called via thin one-line wrapper methods on the model. This is the single most important migration fact for this cluster: **there is effectively no server-side validation layer to port from the models** — validation, if any exists at all, is client-side JS in the `.ctp` views, and the authoritative business rules are inside MySQL stored procedures that Next.js API routes cannot call the same way (`CALL proc(...)` with output/session variables like `@perror_message` is MySQL-CLI-session-scoped and not something a stateless connection-pooled Prisma/mysql2 client trivially reproduces without extra plumbing).

### 2.1 Models with stored-procedure wrapper methods (business logic lives in MySQL, not PHP)

| Model | File | Wrapper method(s) | Stored procedure called | Notes |
|---|---|---|---|---|
| `AttendanceRegister` | `Model/AttendanceRegister.php` | `insertUpdateAttendanceRegisterProc()` (`:18`), `salaryProcessPrc()` (`:47`), `calculateSalaryMainPrc()` (`:60`) | `insert_update_att_reg`, `salary_process_prc`, `calculate_salary_main_prc` | `calculateSalaryMainPrc`/`salaryProcessPrc` are the two mutually-exclusive salary calculation engines dispatched by company-code allow-list in `PayrollController::processpayroll` — **this is the actual "payroll calculation" business logic, and it is entirely opaque to the PHP layer** (all params passed positionally as strings, no named args, no return-value inspection of `@Perr_msg`) |
| `Payrollmaster` | `Model/Payrollmaster.php` | `taxSalaryProcessPrc()` (`:17`) | `tax_salary_process_prc` | Tax-inclusive salary recompute for the "special companies" branch |
| `Payrollarrearmaster` | `Model/Payrollarrearmaster.php` | `taxArrearProcessPrc()` (`:18`), `taxArrearProcessOldPrc()` (`:31`) | `tax_arrear_process_prc`, `tax_salary_process_old_prc` | Arrear equivalents |
| `PayrollArrearComponents` | `Model/PayrollArrearComponents.php` | wrapper at `:23` | `tax_salary_process_prc` | Shared with `Payrollmaster` |

All four of these wrapper methods share the identical anti-pattern: build a comma-joined, single-quote-wrapped string of raw values from an array (`foreach ($outputParameter as $prm) { $parameter .= "'$prm'"; }`) and interpolate it directly into `"CALL proc($parameter,@Perr_msg);"` — no `PDO`-style bound parameters, no escaping beyond the surrounding quotes. Every stored-proc call site in the controllers (Section 1) follows the same pattern directly (e.g. `payroll_master_approve`, `payroll_master_insert`, `ctc_component_upload_prc`, `salary_structure_limit_prc`, `structure_preview_prc`, `calculate_emp_salary_breakup`, `calculate_emp_component_breakup`, `copy_salary_structure_to_new`, `arrear_month_fn`, `payroll_arrear_approve`).

**Full list of stored procedures referenced from this controller cluster** (name, first call site):
- `payroll_master_insert` — `PayrollController.php:213`
- `calculate_salary_main_prc` — via `AttendanceRegister::calculateSalaryMainPrc()`, called `PayrollController.php:872`
- `salary_process_prc` — via `AttendanceRegister::salaryProcessPrc()`, called `PayrollController.php:874`, `PayrollProcessController.php:361`
- `tax_salary_process_prc` — via `Payrollmaster::taxSalaryProcessPrc()`, called `PayrollController.php:1013`
- `payroll_master_approve` — `PayrollController.php:1785`, `PayrollProcessController.php:839,1760,2254,2390`
- `tax_arrear_process_prc`, `tax_salary_process_old_prc` — `Model/Payrollarrearmaster.php:24,37`
- `payroll_arrear_approve` — `ArrearController.php:1280`
- `arrear_month_fn` — `ArrearController.php:183`
- `ctc_component_upload_prc` / `ctc_component_update_and_upload_prc` — `SalaryComponentUploadController.php:193,325,1018,1951,2161`, `SalaryIncrementController.php:3274,3485,4476`
- `salary_structure_limit_prc` — `SalaryComponentUploadController.php:276,1100,2000,2243`, `SalaryIncrementController.php:2447,3328,3570,4622`
- `calculate_emp_salary_breakup` — `SalaryIncrementController.php:2102`
- `calculate_emp_component_breakup` — `SalaryIncrementController.php:3950,4097`
- `copy_salary_structure_to_new` — `SalaryIncrementController.php:3863,4297`
- `structure_preview_prc` — `SalaryIncrementController.php:5099`
- `get_branch_code_abs_fn` (SQL function, not a proc) — used pervasively for branch-scoping restricted `payro_priv` users, e.g. `PayrollController.php:244,282,1424,1467`
- `year_ending_fn` — `YearEndController.php:168`

### 2.2 Anemic models (skeleton only — name/primaryKey/useTable, no validation, associations commented out)

Confirmed by direct read of each file (all under `Model/`): `Payrollmaster.php` (27 lines), `EmpSalarySlip.php` (15), `SalaryHeads.php` (23, `hasMany` commented `:18-22`), `SalaryHeadItems.php` (24, `belongsTo` commented `:17-23`), `SalaryStructures.php` (16), `SalaryStructureDetails.php` (17), `TaxHead.php` (16), `TaxHeads.php` (16), `TaxHeadDetail.php` (16), `FinancialYear.php` (16), `EmployeeSalaryStructure.php` (16), `SalaryIncrement.php` (16), `SalaryIncrementDetails.php` (16), `ComponentIncrement.php` (16), `EmpArrearSalarySlip.php` (15), `EmployeeTaxTransactions.php` (15), `EmpTaxSalTrans.php` (15), `EmpTaxSalTransNew.php` (15), `EmployeeTaxsalsum.php` (15), `EmployeeTaxsalsumNew.php` (15), `EmpTaxRegime.php` (16), `TaxSave.php` (23, `hasMany` commented `:18-22`), `TaxType.php` (16), `Taxsalarycomponents.php` (14), `EmployeeFixedPaymentUpload.php` (14), `EmployeeVariableUpload.php` (14), `EmpSalaryCompUpload.php` (16), `SalaryHike.php` (18), `SalaryHikeDetail.php` (17), `GrossSalary.php` (14), `salarySlip.php` (16).

Implication for migration: **all referential integrity, required-field checks, uniqueness constraints, and cross-field business rules for salary/tax data must be reverse-engineered from (a) the controller-level ad-hoc checks (e.g. `checksalaryheadexists`, `checkstructureexists`, `checkdoj`), (b) the MySQL stored procedures listed in 2.1 (not inspected in this pass — they live in the DB schema/dump, not in `legacy/`), and (c) client-side JS validation in the `.ctp` views** — nothing meaningful can be lifted directly from CakePHP `$validate` arrays because none exist in this cluster.

### 2.3 The `eval()`-based formula engine (cross-cutting business rule, not model-resident)

Not a model at all, but the single largest "hidden business logic" surface in this cluster: salary component amounts are frequently stored as formula strings in `emp_salary_slip.remarks` / `salary_structure_details.structure_det_calequation` and re-evaluated at payroll-process time via PHP `eval()`. Full site list (17 occurrences across 5 controllers):
- `PayrollController.php:943,1155,1166`
- `PayrollProcessController.php:406`
- `ArrearController.php:866`
- `SalaryIncrementController.php:2389,3084,3315,3528,4034,4175,4603`
- `SalaryComponentUploadController.php:253,1077,1761,1987,2199`

Most sites guard the input with a regex allow-list (typically `^[0-9+\-*/().\s]+$`) before or around the `eval()` call, but the guard is inconsistently applied — e.g. `PayrollController.php:943` calls `eval()` first and only uses the regex to decide the *fallback* path, not to gate the call itself. **For the Next.js migration this entire mechanism should be replaced with a proper expression evaluator (e.g. a math-expression-only parser/library) rather than ported as-is** — `eval()` has no place in a Node.js API route regardless of how tightly the pre-validated input is scoped.

---

## 3. Summary of flagged items (legacy cruft / verify before migrating)

1. **`Controller/PayrollProcessController.php`** — entire 2,765-line 6-stage pipeline is unreachable from any view/menu/route in the repo (confirmed via `grep -rl "PayrollProcess" View/` outside its own view folder = zero hits, and zero hits in `Config/`). Do not port unless the business explicitly wants the richer stage model back.
2. **`PayrollController::listpayrollold`, `listprocessedpayrollold`** and **`ArrearController::listpayrollold`, `listprocessedpayrollold`** — superseded `attendance_register`-based datagrid feeds sitting alongside the current `payroll_master`-based ones.
3. **`PayrollProcessController::addremarks2`..`addremarks5`, `addreversalremarks2`..`addreversalremarks3`** — near-duplicate copy-pasted stub actions inside the already-orphaned controller.
4. **`EmpTaxController.php`** — 59-line stub with a single empty-looking `index`, no JSON/render patterns detected; likely superseded by `EmployeeTaxController.php`/`TaxController.php`.
5. **`EmployeeTaxController.php` vs `TaxController.php`** — two parallel, structurally near-identical employee-tax-setup controllers (`setup`, `Calculate`, `Proccess` all present in both). Confirm which one the current UI links to before deciding what to port; do not port both.
6. **`SalaryIncrementController.php`'s `getEmployeesByTypeValue`, `getTypeValues`, `saveComponentAllocate`, `saveComponentUploads`, `saveComponentAllocateKWMT`** duplicate the same-named actions in `SalaryComponentUploadController.php` almost verbatim — consolidate into one shared service in the rewrite.
7. **Backup/scratch controller files present in the directory but out of scope for routing** (not analyzed as live code, listed for awareness): `SalaryHeadsControllernimishabackup.php`, `SalaryReportsController_nimishabackup.php`, `SalaryReportsControllerbkup_nimisha_11_5_19.php`, `SalarySlipReportsController_nimisha.php` — none of these are named in this task's scope, flagged only because `ls Controller/` surfaced them alongside the in-scope files.
8. **Hardcoded SMTP credentials (confirmed, both re-verified this pass):**
   - `PayrollController.php:1857-1860` — Oracle Cloud Infrastructure email SMTP username (OCID-based) and password in plaintext, used to email employees their newly-generated plaintext passwords.
   - `YearEndController.php:201-204` — Zoho SMTP (`info@mypayrollmaster.in` / `welcome123`) used for year-end leave-approval and notice emails.
9. **No CSRF/verb enforcement anywhere in this cluster** — every "write" action is reachable via GET as well as POST since nothing checks `$this->request->is('post')`.
10. **`SalaryIncrementController::viewSalaryIncrementForm($salary_hike_pkey=0, $empName)`** — second parameter has no default, so CakePHP throws a "Missing argument" error if the URL doesn't supply both segments; flag when designing the Next.js route's required params.

---

### 2.5 Advances, Loans & Expenses

# Advances, Loans & Expenses — Backend Technical Report

Scope: `D:\Projects\RIZOMigration\legacy\Controller\{Advance,Employeeadvance,EmployeeAdvanceReports,EmployeeLoan,EmployeeLoanReports,EmployeeExpenses,EmployeeExpenseReports,ExpenseItem,ExpenseType(s),ExpenseReport,ProjectExpenses,ProjectExpenseReport,VehicleExpenses,PaymentApprovals}Controller.php` and their backing models in `legacy\Model\`.

All controllers extend `AppController` (session-based auth, `user_group` 1=admin / 2=employee, `Controller/AppController.php:39-46`). None of the files in scope define their own `beforeFilter`/auth override, so they inherit AppController's session gate as-is.

---

## Bug re-verification: `$this->setup()` fatal-error path

**Re-confirmed.** Three controllers call an undefined instance method `setup()` when `user_group == 2` (employee login) hits `index()`:

| Controller | Call site |
|---|---|
| `EmployeeadvanceController::index()` | `legacy\Controller\EmployeeadvanceController.php:76` |
| `EmployeeExpensesController::index()` | `legacy\Controller\EmployeeExpensesController.php:76` |
| `ProjectExpensesController::index()` | `legacy\Controller\ProjectExpensesController.php:74` |

Pattern (identical in all three):
```php
} else if ($user_group == '2') {
    //Employee View
    $emp_fkey = $this->Session->read("emp_fkey");
    $this->setup($emp_fkey);      // <-- undefined method
    $this->render('setup');
}
```
Verified via `Grep` for `function setup(` across the entire `legacy` tree: 13 files define a `setup()` method (`TaxController.php`, `SiteController.php`, `EmployeeUnderController.php`, `EmployeeTaxController.php`, `EmployeeResignationController(Bkup).php`, `EmployeeJoinController.php`, `EmployeeController(_old...).php`, `EmployeeConfigController.php`, `DataUploaderController.php`, `CompanyNewController.php`, `CompanyController.php`) — **none of them is `AppController`, `EmployeeadvanceController`, `EmployeeExpensesController`, or `ProjectExpensesController`**, and `AppController.php` itself has no `setup()`. Confirmed: any employee-role user (`user_group == 2`) hitting `index()` on these three controllers triggers a PHP fatal error ("Call to undefined method"). `EmployeeLoanController::index()` (`legacy\Controller\EmployeeLoanController.php:59-66`) does **not** have this bug — it directly queries `EmployeeLoan` and does not branch on `user_group` or call `setup()`, so loans are unaffected.

**Migration implication:** the employee-facing landing view for Advances, Employee Expenses, and Project Expenses has no working legacy implementation to port faithfully — this code path was presumably dead/broken in production, or employees never navigate to `index()` directly (JS routing likely bypasses it, going straight to `listemployees`/`empexpenselist`/list endpoints). Treat these three `index()` employee branches as needing new design rather than lift-and-shift.

---

## 1. Controller Action Inventory

### AdvanceController.php (`legacy\Controller\AdvanceController.php`)
Backs a simple "Advance" (cash/petty-cash) ledger against `advance_expense` table (distinct from `EmployeeAdvance`/`emp_advance`, which is salary-advance). `$uses` at line 52; no `setup()` call, no bug.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `form()` | GET (renders form partial) | HTML (layout=null) | Add/edit form for an advance_expense record; loads employees, banks, and existing record if `advance_pkey` present | AdvanceController.php:55 |
| `advancesave()` | POST | JSON | Create or update `advance_expense` row via raw `updateAll`/`save` | AdvanceController.php:75 |
| `advancelist()` | GET/POST (datagrid) | JSON | Paginated list of advances joined to employee/bank names; hardcoded `account_fkey`→bank-name lookup table (13 cases) | AdvanceController.php:107 |
| `Advance()` | GET | HTML | Dropdown/landing data (all advances + employees) | AdvanceController.php:184 |
| `deleteEmployee()` | POST/GET (`$_REQUEST['ids']`) | JSON | Soft-delete (status=0) one or more advance rows | AdvanceController.php:197 |

### EmployeeadvanceController.php (`legacy\Controller\EmployeeadvanceController.php`, 1346 lines)
Backs salary-advance (`emp_advance` table via `EmployeeAdvance` model). Contains substantial embedded business logic for advance-limit calculation (see §2).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index()` | GET | mixed (render `index`/`setup`, admin branch) | Landing; **admin branch OK, employee branch fatal (`setup()` bug, line 76)** | EmployeeadvanceController.php:59 |
| `listemployees()` | GET (datagrid) | JSON | Paginated employee list for combo/grid | EmployeeadvanceController.php:86 |
| `showtaxheaddetail($emp_pkey,$tax_heads_fkey)` | GET | HTML | Tax head detail popup (uses `requestAction` cross-controller calls) | EmployeeadvanceController.php:123 |
| `loadEmpDetails($emp_pkey)` | GET | HTML (view var) | Loads one employee's personal profile into view | EmployeeadvanceController.php:140 |
| `loadEmpProfDetails($emp_pkey)` | GET | HTML (view var) | Loads/derives employee professional profile, auto-generates next `emp_id` if none exists | EmployeeadvanceController.php:153 |
| `empprofdetails($emp_pkey)` | GET | HTML | Employee professional-details form (note: hardcodes `$emp_pkey = 1` at line 203, ignoring the parameter — **possible legacy cruft/bug**) | EmployeeadvanceController.php:200 |
| `emptaxationdetails($emp_pkey)` | GET | empty (no-op) | Stub, empty body — **dead code, verify before migrating** | EmployeeadvanceController.php:253 |
| `getcurrentemployeekey()` | GET | JSON | Returns current session employee's pkey if `user_group==2` | EmployeeadvanceController.php:255 |
| `uploadandsaveempctc($ctcuploadtype)` | POST (multipart file `empctc`) | JSON | Bulk-imports salary advances from an uploaded Excel sheet; contains the eligibility/limit computation described in §2 | EmployeeadvanceController.php:271 |
| `form()` | GET/POST (`$_REQUEST['emp_advance_pkey']`) | HTML (layout=null, renders `absForm` or `form`) | Add/edit popup for a single salary advance; branch-restricts employee list for GLET/ABSG company codes | EmployeeadvanceController.php:657 |
| `salarycheck()` | POST | JSON | Checks whether salary already processed for month/employee | EmployeeadvanceController.php:716 |
| `salary()` | POST | JSON | Computes eligible-advance amount for an employee/month (see §2 — prorated-salary calc) | EmployeeadvanceController.php:746 |
| `employeeloansave()` | POST | JSON | Saves a single salary-advance record (despite the name, saves to `EmployeeAdvance`, not loan) | EmployeeadvanceController.php:882 |
| `employeelist()` | POST (datagrid) | JSON | Paginated list of un-credited advances (`is_credited='N'`), filterable by employee/branch/month, with branch-scoping for GLET/ABSG employee-role users | EmployeeadvanceController.php:917 |
| `advance()` | GET | HTML | Dropdown data (branches, GLET/ABSG-aware) for advance module landing | EmployeeadvanceController.php:1029 |
| `deleteEmployee()` | POST/GET (`$_REQUEST['emp_advance_pkey']`) | JSON | Soft-delete advance record(s) | EmployeeadvanceController.php:1065 |
| `downloadempctcformat($ctcuploadtype,$branch,$employee)` | GET | binary (xlsx download) | Generates the Excel import template | EmployeeadvanceController.php:1083 |
| `Updateame()` | POST | (internal helper, called by other actions, not typically hit directly) | Attendance-sync helper invoked mid bulk-import when present-day data missing | EmployeeadvanceController.php:1221 |
| `listpunches($emp_pkey,$month)` | GET | (helper) | Attendance punch listing, used internally by advance-limit calc | EmployeeadvanceController.php:1251 |

### EmployeeAdvanceReportsController.php (`legacy\Controller\EmployeeAdvanceReportsController.php`, 1225 lines)
Report-generation controller (PDF/Excel), typical "criteria builder" pattern shared across most `*Reports` controllers in this app.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML (view var) | Sets report-type dropdown (`Advance` only) | EmployeeAdvanceReportsController.php:59 |
| `changereporttype($type)` | GET | HTML (renders `showreport`) | Loads report-criteria selector for chosen type | EmployeeAdvanceReportsController.php:73 |
| `addreportcriteria($type,$newindex,$str_currentcriterias)` | GET | HTML (renders `showcriteria`) | Adds another criteria row to the report builder | EmployeeAdvanceReportsController.php:102 |
| `loadcriteriaitems($index,$str_criteria)` | GET | HTML (renders `loadcriteriaitems`) | Loads the item-picker UI for a criteria type; validates model exists via `_modelExists()` | EmployeeAdvanceReportsController.php:124 |
| `listcriteriaitems($str_criteria)` | POST | JSON | Returns list of selectable items (employees/branches) for the chosen criteria, with GLET/ABSG/GAAR/HRBL branch-restriction logic for `user_group==2` | EmployeeAdvanceReportsController.php:143 |
| `reportAudit($type,$mode)` | internal (called from `generatereport`) | (side-effect, saves audit row) | Logs report-download history to `ReportAudit` model | EmployeeAdvanceReportsController.php:279 |
| `generatereport($type,$mode)` | GET/POST | delegates | Dispatcher: routes to `generateemployeeadvance()` for `Advance` type, then calls `reportAudit()` | EmployeeAdvanceReportsController.php:345 |
| `listemployeefields()` | GET | JSON | Returns field-heading metadata for report builder | EmployeeAdvanceReportsController.php:364 |
| `_modelExists($modelName)` (private) | — | — | Guards against arbitrary model instantiation in `loadcriteriaitems`/`listcriteriaitems` (uses `App::objects('model')`) | EmployeeAdvanceReportsController.php:398 |
| `generateemployeeadvance($mode)` (private) | — | HTML / PDF (html2pdf) / xlsx (PHPExcel) download | Builds the actual Salary Advance report, branch-wise or flat, in view/pdf/excel mode | EmployeeAdvanceReportsController.php:404 |
| `generatesummaryreport($mode)` (private, referenced by grep at line 972, not fully read) | — | HTML/PDF/xlsx | Secondary summary-report generator | EmployeeAdvanceReportsController.php:972 |

### EmployeeLoanController.php (`legacy\Controller\EmployeeLoanController.php`, ~2200+ lines)
Backs `emp_loan` (loan master, `EmployeeLoan` model) and `emp_loan_info` (per-month EMI schedule, `EmployeeLoanInfo` model). No `setup()` bug — `index()` is self-contained.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index()` | GET | HTML (view var, no explicit render) | Employee's own loan list (uses session `emp_fkey` directly, no admin/employee branch) | EmployeeLoanController.php:59 |
| `getcontactsbysite($skey)` | POST | HTML fragment | Site-scoped employee/contact dropdown | EmployeeLoanController.php:77 |
| `emptaxationdetails($emp_pkey)` | GET | empty (no-op) | Stub — **dead code, verify before migrating** | EmployeeLoanController.php:102 |
| `uploadandsaveempctc($ctcuploadtype)` | POST (multipart) | JSON | Bulk Excel import of loan/EMI data (parallels EmployeeadvanceController's CTC upload) | EmployeeLoanController.php:111 |
| `form()` | GET | HTML | Add/edit loan popup, excludes terminated employees from dropdown | EmployeeLoanController.php:427 |
| `emi_upload()` | GET | HTML | EMI-upload form (employee dropdown) | EmployeeLoanController.php:489 |
| `getEmi()` | POST | JSON | Fetches EMI for employee/month | EmployeeLoanController.php:554 |
| `update_transfer($loan_pkey)` | POST | JSON | Shifts/transfers an EMI installment to another month (`emi_transfer` flag) | EmployeeLoanController.php:585 |
| `amount_pay($loan_pkey,$amount)` | POST | JSON | Records a manual loan repayment | EmployeeLoanController.php:669 |
| `update($loan_pkey)` | GET | HTML (view vars) | Loads loan + EMI schedule + paid/balance totals for edit view | EmployeeLoanController.php:787 |
| `checkmonth($from_month,$loan_pkey)` | GET | HTML fragment (`<option>` list) | Lists eligible EMI months for transfer target | EmployeeLoanController.php:823 |
| `completed($loan_pkey)` | GET | plain `echo 1` | Marks a loan fully completed (raw SQL updates) | EmployeeLoanController.php:842 |
| `emploan()` | GET | empty (no-op) | Stub — **dead code, verify before migrating** | EmployeeLoanController.php:850 |
| `upload()` | GET | HTML | Bulk upload landing (branches dropdown) | EmployeeLoanController.php:854 |
| `viewloan($loan_pkey)` | GET | HTML (view vars) | Loan detail/statement view with paid counts, shifted-EMI counts | EmployeeLoanController.php:862 |
| `downloads($loan_pkey)` | GET | binary (pdf/xlsx, not fully inspected) | Loan statement download | EmployeeLoanController.php:1069 |
| `employeeemiloansave()` | POST | JSON | Saves an EMI upload row (`LoanEmi` model) | EmployeeLoanController.php:1141 |
| `employeeloansave()` | POST | JSON | Creates a new loan (computes tenure/EMI schedule from `tenure`,`loan_amount`,`intrest_rate`) | EmployeeLoanController.php:1158 |
| `employeeloanlist()` | POST (datagrid) | JSON | Paginated loan list, filterable by employee/branch/month | EmployeeLoanController.php:1226 |
| `jsons($branch)` | GET | JSON | Employee autocomplete/select2 source, branch-scoped | EmployeeLoanController.php:1381 |
| `jsons_form($branch)` | GET | JSON | Same, for the loan form specifically | EmployeeLoanController.php:1425 |
| `ctcupload()` | GET | HTML | CTC-upload landing page | EmployeeLoanController.php:1455 |
| `deleteEmployeeloan()` | POST/GET | JSON | Soft-delete a loan | EmployeeLoanController.php:1464 |
| `salarycheck()` | POST | JSON | Salary-processed check (same pattern as advances) | EmployeeLoanController.php:1480 |
| `payroll_check_amount_pay($emp_fkey)` | GET | JSON (not fully inspected) | Checks payroll status before allowing a manual repayment | EmployeeLoanController.php:1503 |
| `downloadexcels($loan_pkey)` | GET | binary (xlsx) | Loan statement Excel export | EmployeeLoanController.php:1523 |
| `downloadempctcformat($ctcuploadtype,$branch,$employee)` | GET | binary (xlsx) | Excel import template for loan CTC upload | EmployeeLoanController.php:1723 |
| `employeelist()` | POST (datagrid) | JSON | Employee list for loan module | EmployeeLoanController.php:1854 |
| `downloademploanformat($ctcuploadtype,$branch,$employee)` | GET | binary (xlsx) | Import template for loan-only bulk upload | EmployeeLoanController.php:1918 |
| `uploadandsaveempemi($ctcuploadtype)` | POST (multipart) | JSON | Bulk EMI-only import | EmployeeLoanController.php:2012 |
| `deleteEmployeesemi($id)` | POST/GET | JSON (not fully inspected) | Deletes an EMI upload row | EmployeeLoanController.php:2199 |
| `getLoanBalance($emp_pkey)` | GET | JSON (not fully inspected) | Returns outstanding loan balance for an employee | EmployeeLoanController.php:2210 |

### EmployeeLoanReportsController.php (`legacy\Controller\EmployeeLoanReportsController.php`)
Same criteria-builder/report-generator pattern as EmployeeAdvanceReportsController.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML | Report-type dropdown | EmployeeLoanReportsController.php:55 |
| `changereporttype($type)` | GET | HTML | Criteria selector | EmployeeLoanReportsController.php:66 |
| `addreportcriteria($type,$newindex,$str_currentcriterias)` | GET | HTML | Add criteria row | EmployeeLoanReportsController.php:90 |
| `loadcriteriaitems($index,$str_criteria,$type)` | GET | HTML | Item-picker UI (note extra `$type` param vs Advance version) | EmployeeLoanReportsController.php:111 |
| `listcriteriaitems($str_criteria)` | POST | JSON | Item list for criteria | EmployeeLoanReportsController.php:131 |
| `reportAudit($type,$mode)` | internal | side-effect | Download-history logging | EmployeeLoanReportsController.php:293 |
| `generatereport($type,$mode)` | GET/POST | delegates | Dispatcher → `generateemployeeeloan()` | EmployeeLoanReportsController.php:362 |
| `listemployeefields()` | GET | JSON | Field metadata | EmployeeLoanReportsController.php:380 |
| `_modelExists($modelName)` (private) | — | — | Guard | EmployeeLoanReportsController.php:408 |
| `generateemployeeeloan($mode)` (private) | — | HTML/PDF/xlsx | Loan report generator | EmployeeLoanReportsController.php:413 |
| `generatesummaryreport($mode)` (private) | — | HTML/PDF/xlsx | Summary variant | EmployeeLoanReportsController.php:965 |

### EmployeeExpensesController.php (`legacy\Controller\EmployeeExpensesController.php`, 2086 lines)
Employee-expense claims against `emp_expense` (`EmployeeExpenses` model). Implements the **two-level Authorize→Approve** workflow (re-confirmed, see §2/grandexpense below).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index()` | GET | mixed | Landing; **employee branch fatal (`setup()` bug, line 76)** | EmployeeExpensesController.php:59 |
| `listemployees()` | GET (datagrid) | JSON | Employee list, branch-scoped for `user_group==2` | EmployeeExpensesController.php:86 |
| `form()` | GET | HTML | Add/edit expense-claim popup | EmployeeExpensesController.php:137 |
| `salarycheck()` | (same pattern as advance/loan) | JSON | — | (grep, not individually re-read; consistent naming) |
| `employeeloansave()` | POST | JSON | Misnamed — saves an expense claim, not a loan | (per earlier grep listing) |
| `employeelist()` (×3 defined/commented variants at 374/626/981) | POST | JSON | Only the **uncommented** definition at line 981 is live; the two earlier ones (374, 626) are commented out — **dead code, confirm removal is safe before migrating** | EmployeeExpensesController.php:374 (commented), 626 (commented), 981 (live) |
| `employeeverifiedlist()` | POST (datagrid) | JSON | List of authorized/approved/rejected expenses for the approver | EmployeeExpensesController.php:1143 |
| `Expenses()` (×2 defined/commented, live at 1461) | GET | HTML | Landing dropdown data; earlier definitions at 1227/1292 are commented out — **dead code** | EmployeeExpensesController.php:1227 (commented), 1292 (commented), 1461 (live) |
| `deleteEmployee()` | POST/GET | JSON | Soft-delete an expense claim | EmployeeExpensesController.php:1638 |
| `jsons($branch,$resigned)` | GET | JSON | Employee autocomplete, with special GLET logic | EmployeeExpensesController.php:1655 |
| `cancalEmployee()` [sic] | POST/GET | JSON | Cancels (soft-deletes with different status) an expense claim | EmployeeExpensesController.php:1781 |
| `empexpenselist()` | GET | HTML (view var) | Count of claims pending the current user's authorization | EmployeeExpensesController.php:1799 |
| `manageexpense($expenseId)` | GET | HTML (view vars, `mode`=view/edit) | Detail/edit screen; determines edit-vs-view mode from claim status and whether current user is the authorizer | EmployeeExpensesController.php:1818 |
| `grandexpense()` | POST | JSON | **The two-level Authorize→Approve/Reject action.** Compares `cur_emp_key` against `authorized_by`/`approved_by` to decide whether this call authorizes or fully approves; `cur_emp_key==null` (admin) can approve directly. See §2 for full state logic. | EmployeeExpensesController.php:1867 |
| `listempexpense()` | POST (datagrid) | JSON | Claims pending current user's authorization or approval | EmployeeExpensesController.php:1944 |
| `listempexpenseverified()` | POST (datagrid) | JSON | Claims already authorized/approved/rejected by current user | EmployeeExpensesController.php:1978 |
| `employeerequests()` | GET | HTML (view var, minimal) | Landing for "my requests" | EmployeeExpensesController.php:2011 |
| `viewrequest()` | GET (datagrid) | JSON | Paginated list of the current employee's own submitted claims with approver names | EmployeeExpensesController.php:2018 |
| `viewexpense($expenseId)` | GET | HTML (view vars) | Read-only detail view of one's own claim | EmployeeExpensesController.php:2052 |
| `showimage($pkey)` | GET | HTML (view var, image blob) | Serves the receipt image attached to a claim | EmployeeExpensesController.php:2078 |

### EmployeeExpenseReportsController.php (`legacy\Controller\EmployeeExpenseReportsController.php`)
Same criteria-builder pattern.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML | Report-type dropdown | EmployeeExpenseReportsController.php:55 |
| `changereporttype($type)` | GET | HTML | Criteria selector | EmployeeExpenseReportsController.php:92 |
| `addreportcriteria($type,$newindex,$str_currentcriterias)` | GET | HTML | Add criteria row | EmployeeExpenseReportsController.php:116 |
| `loadcriteriaitems($index,$str_criteria)` | GET | HTML | Item-picker UI | EmployeeExpenseReportsController.php:137 |
| `listcriteriaitems($str_criteria)` | POST | JSON | Item list | EmployeeExpenseReportsController.php:155 |
| `reportAudit($type,$mode)` | internal | side-effect | Download-history logging | EmployeeExpenseReportsController.php:324 |
| `generatereport($type,$mode)` | GET/POST | delegates | Dispatcher → `generateemployeeexpence()` | EmployeeExpenseReportsController.php:387 |
| `listemployeefields()` | GET | JSON | Field metadata | EmployeeExpenseReportsController.php:405 |
| `_modelExists($modelName)` (private) | — | — | Guard | EmployeeExpenseReportsController.php:433 |
| `generateemployeeexpence($mode)` (private) | — | HTML/PDF/xlsx | Expense report generator (only counts `expense_status='Approved'` rows, per §2) | EmployeeExpenseReportsController.php:438 |
| `generatesummaryreport($mode)` (private) | — | HTML/PDF/xlsx | Summary variant | EmployeeExpenseReportsController.php:1009 |

### ExpenseItemController.php (`legacy\Controller\ExpenseItemController.php`)
Manages an item/inventory master used by the Project-expense purchase-order flow (`item_master`/`ItemDetails` etc.) — closer to a procurement-catalog controller than an expense-approval controller.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index()` | GET | empty (no-op) | Stub — **dead code, verify before migrating** | ExpenseItemController.php:50 |
| `itemfilter()` | GET | JSON (implied) | Autocomplete filter for expense items | ExpenseItemController.php:57 |
| `expensefilter()` | — | — | Entirely commented out — **dead code**, and note line 90 (`\` before `$q = ...`) is a leftover stray backslash inside the comment block that would be a parse error if uncommented as-is | ExpenseItemController.php:84 |
| `form($acct_payable_pkey)` | GET | HTML | Item add/edit form | ExpenseItemController.php:113 |
| `chkcategory()` | POST | JSON (layout=null) | Validates item code against `item_master` | ExpenseItemController.php:153 |
| `itemlist($param)` | GET/POST (datagrid) | JSON | Paginated item list | ExpenseItemController.php:177 |
| `itemdelete()` | POST/GET | JSON | Soft-delete an item | ExpenseItemController.php:240 |
| `save()` | POST | JSON (implied) | Create/update item master record across multiple related tables (Item, QuantityDetails, ItemDetails, WarrantyDetails, ItemAdditionalDetails, ItemPricing, ExpenseItem) | ExpenseItemController.php:262 |

### ExpenseTypeController.php vs ExpenseTypesController.php (`legacy\Controller\ExpenseType(s)Controller.php`)
**Two near-duplicate controllers exist** (`ExpenseType` singular and `ExpenseTypes` plural) managing the same `expense_type` table via the same `ExpenseType` model. No custom routes reference either explicitly (`Grep` for `ExpenseType` in `Config/routes.php` returned no matches, meaning both are reachable only via CakePHP's default `/controller/action` convention). **Flag both as possible duplicate/legacy cruft — determine from the frontend/views which one is actually linked before migrating; do not port both.**

| Action | HTTP method(s) | Response type | Purpose | path:line (Singular / Plural) |
|---|---|---|---|---|
| `index()` | GET | empty (no-op) | Stub | ExpenseTypeController.php:36 / ExpenseTypesController.php:36 |
| `expense($expense_type_pkey)` | GET | HTML | Add/edit form; singular version also loads `expense_heads` dropdown (plural doesn't) | ExpenseTypeController.php:41 / ExpenseTypesController.php:41 |
| `delete($user_pkey)` | GET/POST | JSON | Soft-delete | ExpenseTypeController.php:63 / ExpenseTypesController.php:54 |
| `save()` | POST | JSON | Create/update; singular checks name-uniqueness only (type-code check is commented out); plural checks both name and code uniqueness but has an **undefined-variable bug**: in the `else` branch (no `$pkey`, i.e. new record) it references `$time['0']['0']['time']` before `$time` is ever queried (ExpenseTypesController.php:95) — will emit a PHP notice/produce `NULL` `creation_date` | ExpenseTypeController.php:81 / ExpenseTypesController.php:68 |
| `listexpense()` | GET/POST (datagrid) | JSON | Paginated list; singular joins `ExpenseHead` for head name, plural does not | ExpenseTypeController.php:162 / ExpenseTypesController.php:130 |
| `checkexpensetypenameexists($expense_type_name)` | POST | plain int (echo count) | Uniqueness check | ExpenseTypeController.php:219 / ExpenseTypesController.php:169 |
| `checkexpensetypecodeexists($expense_type_code)` | POST | plain int (echo count) | Uniqueness check | ExpenseTypeController.php:236 / ExpenseTypesController.php:184 |
| `search()` | GET (`$this->request->query`) | JSON | Search-by-name | ExpenseTypeController.php:253 / ExpenseTypesController.php:199 |
| `allocate_expense($expense_type_pkey)` | GET | HTML | (Singular only) Designation-allocation screen for an expense type — restricts which designations may claim this expense type | ExpenseTypeController.php:268 |
| `save_allocate()` | POST | JSON | (Singular only) Saves designation↔expense-type allocation (`allocate_expense` table); supports "ALL" designations bulk-insert | ExpenseTypeController.php:282 |
| `remove_allocate()` | POST | JSON | (Singular only) Removes a designation allocation | ExpenseTypeController.php:321 |

Note: only `ExpenseTypeController` (singular) has the designation-allocation feature (`allocate_expense`/`save_allocate`/`remove_allocate`) — this is a business rule that restricts which job designations may submit which expense type, and it lives only in the singular controller, reinforcing that the plural one is likely the abandoned/superseded copy.

### ExpenseReportController.php (`legacy\Controller\ExpenseReportController.php`)
Generic "Expense Report" (labelled `AdvanceExpense` internally) — reports on `emp_expense`/`emp_expense_details` joined to `site`, `expense_type`, `expense_heads`. Criteria-builder pattern, criteria types: EmployeeDetails, Units, ExpenseType, Site, and a default (ExpenseHead-driven) branch.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML | Report-type dropdown | ExpenseReportController.php:55 |
| `changereporttype($type)` | GET | HTML | Criteria selector | ExpenseReportController.php:66 |
| `addreportcriteria($type,$newindex,$str_currentcriterias)` | GET | HTML | Add criteria row | ExpenseReportController.php:90 |
| `loadcriteriaitems($index,$str_criteria)` | GET | HTML | Item-picker UI | ExpenseReportController.php:110 |
| `listcriteriaitems($str_criteria)` | POST | JSON | Item list (Site/ExpenseType/Units/ExpenseHead/EmployeeDetails) | ExpenseReportController.php:129 |
| `downloadHistory($type,$mode)` | internal | side-effect | Download-history logging | ExpenseReportController.php:227 |
| `generatereport($type,$mode)` | GET/POST | delegates | Dispatcher → `generateExpenseReport()` | ExpenseReportController.php:290 |
| `listemployeefields()` | GET | JSON | Field metadata | ExpenseReportController.php:305 |
| `_modelExists($modelName)` (private) | — | — | Guard | ExpenseReportController.php:333 |
| `generateExpenseReport($mode)` (private) | — | HTML/PDF/xlsx | Core report builder; **hardcodes `emp_expense.expense_status = 'Approved'`** in every criteria branch (business rule: only fully-approved claims appear in the report) | ExpenseReportController.php:339 |

### ProjectExpensesController.php (`legacy\Controller\ProjectExpensesController.php`, 2500+ lines)
The largest controller in scope — a combined project-expense claim + purchase-order + payment/advance-payment module against `emp_expense` (shared table with `EmployeeExpensesController`) plus `ExpenseTypePaymentDetails`/`ExpenseTypeDetails`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index()` | GET | mixed | Landing; **employee branch fatal (`setup()` bug, line 74)** | ProjectExpensesController.php:58 |
| `listemployees()` | GET (datagrid) | JSON | Employee list, branch-scoped | ProjectExpensesController.php:84 |
| `form()` | GET | HTML | Add/edit claim popup | ProjectExpensesController.php:134 |
| `salarycheck()` | POST | JSON | Salary-processed check | ProjectExpensesController.php:198 |
| `employeeloansave()` | POST | JSON | Misnamed — saves a project-expense claim | ProjectExpensesController.php:243 |
| `employeelist()` | POST (datagrid) | JSON | Employee list for grid | ProjectExpensesController.php:337 |
| `employeeverifiedlist()` (×2, live at 404) | POST | JSON | Verified-claims list; commented duplicate at line 553 — **dead code** | ProjectExpensesController.php:404 (live), 553 (commented) |
| `projectlist()` | GET/POST | JSON | Project/site dropdown source | ProjectExpensesController.php:476 |
| `Expenses()` | GET | HTML | Landing dropdown data | ProjectExpensesController.php:630 |
| `Purchase()` | GET | HTML | Purchase-order landing | ProjectExpensesController.php:636 |
| `deleteEmployee()` | POST/GET | JSON | Soft-delete a claim | ProjectExpensesController.php:644 |
| `delete_expense($id)` | GET/POST | JSON (implied) | Deletes an expense line item | ProjectExpensesController.php:659 |
| `empexpenselist()` | GET | HTML (view var) | Pending-approval count | ProjectExpensesController.php:671 |
| `manageexpense($expenseId)` | GET | HTML | Detail/edit screen | ProjectExpensesController.php:689 |
| `grandexpense()` | POST | JSON | **Single-level Approve/Reject action** (re-confirmed — no separate authorize step; moves straight `Applied`→`Approved`/`Rejected`; has a ZWLK-company-specific side effect that voids `advance_payment` rows on rejection when `headkey==1`) | ProjectExpensesController.php:758 |
| `listempexpense()` | POST (datagrid) | JSON | Pending-approval list | ProjectExpensesController.php:807 |
| `listempexpenseverified()` | POST (datagrid) | JSON | Verified list | ProjectExpensesController.php:863 |
| `employeerequests()` | GET | HTML | "My requests" landing | ProjectExpensesController.php:926 |
| `viewrequest()` | GET (datagrid) | JSON | Own-submitted-claims list | ProjectExpensesController.php:932 |
| `viewexpense($expenseId)` | GET | HTML | Read-only claim detail | ProjectExpensesController.php:988 |
| `showimage($pkey)` | GET | HTML | Receipt image | ProjectExpensesController.php:1025 |
| `advanceamount($pkey)` | GET | JSON (implied) | Fetches advance-payment amount tied to a claim | ProjectExpensesController.php:1033 |
| `loadnew()` | GET | HTML | New purchase-order line-item form | ProjectExpensesController.php:1056 |
| `aprlist()` | GET | JSON (implied) | Approver dropdown list | ProjectExpensesController.php:1128 |
| `emplist()` | GET | JSON (implied) | Employee dropdown | ProjectExpensesController.php:1165 |
| `explist()` | GET | JSON (implied) | Expense-type dropdown | ProjectExpensesController.php:1197 |
| `benlist()` | GET | JSON (implied) | Beneficiary dropdown | ProjectExpensesController.php:1229 |
| `prolist()` | GET | JSON (implied) | Project/site dropdown | ProjectExpensesController.php:1253 |
| `editexpense($id)` | GET | HTML | Edit an existing purchase-order expense | ProjectExpensesController.php:1295 |
| `returnexpense($id,$key)` | GET/POST | JSON (implied) | Handles a purchase-order "return" | ProjectExpensesController.php:1316 |
| `editexpensepayment($id)` | GET | HTML | Edit a payment record | ProjectExpensesController.php:1341 |
| `save()` | POST | JSON (implied) | Saves purchase-order / expense data (large method) | ProjectExpensesController.php:1372 |
| `edit_save()` | POST | JSON (implied) | Saves edits to an existing purchase order | ProjectExpensesController.php:1568 |
| `return_save()` | POST | JSON (implied) | Saves a purchase-order return | ProjectExpensesController.php:1721 |
| `editpayment_save()` | POST | JSON (implied) | Saves a payment edit | ProjectExpensesController.php:1792 |
| `loadtable($id,$rowindex)` | GET | HTML fragment | Renders an editable line-item table row | ProjectExpensesController.php:1816 |
| `deleteorder($id,$total)` | POST/GET | JSON (implied) | Deletes a PO line item | ProjectExpensesController.php:1915 |
| `deleteordermaster($id)` | POST/GET | JSON (implied) | Deletes an entire PO | ProjectExpensesController.php:1930 |
| `category($type)` | GET | JSON (implied) | Category dropdown source | ProjectExpensesController.php:1941 |
| `submit($total,$balance,$pid,$payment_balance,$payment)` | POST | JSON (implied) | Finalizes a PO payment | ProjectExpensesController.php:1964 |
| `view_expense($expenseId)` | GET | HTML | View expense (variant of `viewexpense`) | ProjectExpensesController.php:1983 |
| `show_expense($expenseId)` | GET | HTML | Show expense (variant) | ProjectExpensesController.php:1999 |
| `downloads($pkey)` | GET | binary | Download PO/expense document | ProjectExpensesController.php:2018 |
| `downloadexcels($pkey)` | GET | binary (xlsx) | Excel export | ProjectExpensesController.php:2055 |
| `loadnewpurchase()` | GET | HTML | New purchase form | ProjectExpensesController.php:2387 |
| `project($type)` | — | — | Commented out — **dead code** | ProjectExpensesController.php:2396 |
| `beneficiary($type)` | GET | JSON (implied) | Beneficiary lookup | ProjectExpensesController.php:2415 |
| `expense_type($type)` | GET | JSON (implied) | Expense-type lookup | ProjectExpensesController.php:2433 |
| `expense_typelist($type,$ben)` | GET | JSON (implied) | Expense-type list scoped to beneficiary | ProjectExpensesController.php:2449 |
| `request_id($vendor,$type,$ben)` | GET | JSON (implied) | Request-ID lookup | ProjectExpensesController.php:2465 |
| `get_details($id,$type)` | GET | JSON (implied) | Generic detail lookup by type | ProjectExpensesController.php:2486 |

*(Note: many of the PO-specific actions past line 1000 were located via signature grep only, not individually read in full — response-type "implied" flags where `echo json_encode`/`autoRender=FALSE` pattern is consistent with the rest of the file but wasn't line-by-line verified.)*

### ProjectExpenseReportController.php (`legacy\Controller\ProjectExpenseReportController.php`)
Reports specifically for the project-expense/beneficiary/PO data. Broadest criteria set of all the report controllers in scope (adds project- and beneficiary-level criteria not present in the employee-expense report).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML | Report-type dropdown | ProjectExpenseReportController.php:56 |
| `changereporttype($type)` | GET | HTML | Criteria selector | ProjectExpenseReportController.php:69 |
| `addreportcriteria($type,$newindex,$str_currentcriterias)` | GET | HTML | Add criteria row | ProjectExpenseReportController.php:99 |
| `loadcriteriaitems($index,$str_criteria)` | GET | HTML | Item-picker UI | ProjectExpenseReportController.php:119 |
| `listcriteriaitems($str_criteria)` | POST | JSON | Item list | ProjectExpenseReportController.php:136 |
| `generatereport($type,$mode)` | GET/POST | delegates | Dispatcher (routes to one of the three generators below based on `$type`) | ProjectExpenseReportController.php:188 |
| `listemployeefields()` | GET | JSON | Field metadata | ProjectExpenseReportController.php:208 |
| `_modelExists($modelName)` (private) | — | — | Guard | ProjectExpenseReportController.php:236 |
| `GenerateProjectExpensereport($mode)` (private) | — | HTML/PDF/xlsx | Project-level expense report | ProjectExpenseReportController.php:243 |
| `projectcriteria($site)` | GET | JSON (implied) | Project-criteria lookup | ProjectExpenseReportController.php:1869 |
| `bencriteria($ben,$crit)` | GET | JSON (implied) | Beneficiary-criteria lookup | ProjectExpenseReportController.php:1874 |
| `itemcriterialist($ben,$crit)` | GET | JSON (implied) | Item-criteria list | ProjectExpenseReportController.php:1886 |
| `GenerateBeneficiaryExpensereport($mode)` (private) | — | HTML/PDF/xlsx | Beneficiary-level expense report | ProjectExpenseReportController.php:1902 |
| `GenerateExpenseTypeReport($mode)` (private) | — | HTML/PDF/xlsx | Expense-type-level report | ProjectExpenseReportController.php:2267 |

### VehicleExpensesController.php (`legacy\Controller\VehicleExpensesController.php`)
Backs `transportation_expense` (`VehicleExpenses` model) against `vehicle_master` (`Vehicle` model). **No approval workflow at all** — direct data entry, confirming the prior finding for this controller.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `form()` | GET | HTML | Add/edit vehicle-expense (mileage/trip) form; loads vehicle and site lists | VehicleExpensesController.php:56 |
| `get_rate_per_km()` | GET (`$_REQUEST['veh_id']`) | JSON | Looks up `rate_per_km` for selected vehicle from `vehicle_master` — the one business-rule lookup in this controller (used client-side to compute trip cost) | VehicleExpensesController.php:100 |
| `employeeloansave()` | POST | JSON | Misnamed — creates/updates a vehicle-expense record (raw SQL `updateAll` for edit, `save()` for new) | VehicleExpensesController.php:115 |
| `vehicleexpenselist()` | POST (datagrid) | JSON | Paginated list, filterable by site/vehicle | VehicleExpensesController.php:177 |
| `Expenses()` | GET | HTML | Landing dropdown data (sites, vehicles) | VehicleExpensesController.php:238 |
| `deleteEmployee()` | POST/GET | JSON | Soft-delete a vehicle-expense record | VehicleExpensesController.php:249 |

### PaymentApprovalsController.php (`legacy\Controller\PaymentApprovalsController.php`, 122 lines)
**Re-confirmed: this is not an approval hub.** It is a small SaaS plan/feature-flag lookup controller — checks which "payment approvals" feature-flags are enabled for the tenant's subscription plan, entirely unrelated to expense/advance/loan approval logic.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index()` | GET | empty (no-op) | Stub — **dead code** | PaymentApprovalsController.php:54 |
| `getPaymentFeatures()` | GET | JSON | Looks up the tenant's `plan_id` from `CentralUserCredentials` (control DB), then returns all `Features` rows where `feature_key='payment approvals'` with an `is_enabled` flag computed against `PlanFeature` for that plan | PaymentApprovalsController.php:57 |

---

## 2. Model Validation & Business Rules

### Model files (all in `legacy\Model\`) — barebones, no CakePHP-native validation

Every model backing this domain is a minimal `AppModel` subclass declaring only `$name`, `$primaryKey`, `$useTable`. **None of them define `$validate`, `$belongsTo`/`$hasMany`/`$hasOne` associations, or `beforeSave`/`beforeValidate`/`afterSave`/`beforeFind` hooks.** `AppModel.php` itself (`legacy\Model\AppModel.php:34-38`) is also empty beyond a passthrough constructor — there is no shared validation layer to inherit either.

| Model | Table | Primary key | path |
|---|---|---|---|
| `Advance` | `advance_expense` | `advance_pkey` | Model/Advance.php |
| `EmployeeAdvance` | `emp_advance` | `emp_advance_pkey` | Model/EmployeeAdvance.php |
| `EmployeeAdvanceInfo` | `emp_advance_info` | `emp_advance_info_pkey` | Model/EmployeeAdvanceInfo.php |
| `EmployeeLoan` | `emp_loan` | `emp_loan_pkey` | Model/EmployeeLoan.php |
| `EmployeeLoanInfo` | `emp_loan_info` | `emp_loan_info_pkey` | Model/EmployeeLoanInfo.php |
| `LoanEmi` | `emi_upload` | `emi_upload_pkey` | Model/LoanEmi.php |
| `EmployeeExpenses` | `emp_expense` | `emp_expenses_pkey` | Model/EmployeeExpenses.php |
| `ExpenseHead` | `expense_heads` | `expense_heads_pkey` | Model/ExpenseHead.php |
| `ExpenseItem` | `expense_item` | `expense_item_pkey` | Model/ExpenseItem.php |
| `ExpenseType` | `expense_type` | `expense_type_pkey` | Model/ExpenseType.php |
| `ExpenseTypeDetails` | `emp_expense_details` | `expense_details_pkey` | Model/ExpenseTypeDetails.php |
| `ExpenseTypePaymentDetails` | `emp_expense_payment` | `payment_pkey` | Model/ExpenseTypePaymentDetails.php |
| `VehicleExpenses` | `transportation_expense` | `transportation_expense_pkey` | Model/VehicleExpenses.php |
| `Vehicle` | `vehicle_master` | `vehicle_master_pkey` | Model/Vehicle.php |

**Migration implication:** all data integrity/business rules for this domain live entirely in **controller code**, not the model layer. There is nothing to "port" from CakePHP validation config — every rule below must be re-derived from controller logic and re-implemented explicitly in the Next.js API layer (e.g., zod schemas / service-layer checks), since the legacy models give no scaffolding at all.

### Business rules found in controller code (not models)

**a) Salary-advance eligibility/limit calculation** — `EmployeeadvanceController::uploadandsaveempctc()` (EmployeeadvanceController.php:271-654) and `EmployeeadvanceController::salary()` (EmployeeadvanceController.php:746-879):
- For non-`DAILY WAGES`/`HOURLY WAGES` employees, eligible advance = 80% of monthly CTC (`emp_anual_ctc / 12`), i.e. `salary = round(0.80 * (annual_ctc/12))` (line 407-409, repeated 763-764).
- For company codes `GLET`/`ABSG` specifically, this is further **prorated by attendance**: `advance_limit = (monthly_ctc / daysInMonth) * working_day_count`, where `daysInMonth` and `working_day_count` are derived from attendance-register/punch queries (lines 484-524, 840-861), and any advance already taken for the month is subtracted (`amount = existing_advance + new_amount`, condition `amount <= advance_limit`, lines 539-554).
- For all other companies, the check is simply `amount < salary_amount` (80%-of-monthly-CTC cap), no attendance proration (line 557).
- Daily/hourly-wage employees bypass the salary-cap check entirely and are saved unconditionally (lines 590-601) — **no eligibility limit applies to wage employees**, only to fixed-salary employees.
- Rows failing the check are collected into `rejeted_exceeding_ctc` / `rejeted_exceeding_advance` arrays and reported back in the JSON response rather than saved (lines 549-589) — i.e. **rejection is silent per-row within a bulk import, not a thrown error**.

**b) Two-level Authorize→Approve workflow (Employee Expenses)** — `EmployeeExpensesController::grandexpense()` (EmployeeExpensesController.php:1867-1942), re-confirmed:
- `emp_expense.expense_status` transitions: `Applied` → (`Authorized` by `authorized_by` user) → `Approved` (by `approved_by` user) or `Rejected` at either stage.
- Who can act is determined purely by matching `Session.emp_fkey` against the `authorized_by`/`approved_by` columns already stored on the row (set at claim-creation time, not visible in this controller — presumably assigned in `form()`/save logic not shown here). Admin (`cur_emp_key == null`, i.e. `user_group==1`) can approve directly, skipping the authorize step, and the remark is auto-prefixed `"Approved/Rejected by Admin: ..."`.
- Rejecting at either stage sets `status=2` (vs `status=1` for authorize/approve) — this is a distinct "soft state" from the `expense_status` string field.

**c) Single-level Approve/Reject (Project Expenses)** — `ProjectExpensesController::grandexpense()` (ProjectExpensesController.php:758-805), re-confirmed:
- Only one status transition: `Applied` → `Approved`/`Rejected`, matched against `authorized_by` only (no separate `approved_by`/second stage).
- Company-specific side effect: for `company_code == 'ZWLK'` and `headkey == 1`, rejecting an expense also voids (`status='0'`) any linked `advance_payment` row (line 782) — a cross-table cascade found nowhere else in this domain.

**d) Report-level business rule** — both `ExpenseReportController::generateExpenseReport()` (ExpenseReportController.php:384 etc.) and `EmployeeExpenseReportsController::generateemployeeexpence()` hardcode `emp_expense.expense_status = 'Approved'` in every query branch — i.e. **only fully-approved claims are ever reportable**; authorized-but-not-approved or rejected claims never appear in generated reports regardless of date range/criteria selected.

**e) Designation-based expense-type allocation** — `ExpenseTypeController::allocate_expense()`/`save_allocate()`/`remove_allocate()` (ExpenseTypeController.php:268-350, singular controller only): maintains an `allocate_expense` join table (`expense_type_fkey` × `designation_fkey`) that restricts which job designations are eligible to claim a given expense type. This is a real authorization/business rule enforced only via this admin-side allocation screen — **no evidence in the code reviewed that `grandexpense()` or the claim-creation forms actually check this allocation table before allowing a claim**, i.e. it may be an unenforced/UI-only restriction. Worth flagging for the migration team to verify against the frontend.

**f) No business rules found for Advance (AdvanceController, generic ledger), EmployeeLoan (interest/EMI schedule is computed inline in `employeeloansave()`, EmployeeLoanController.php:1158-1225, not in the model), or VehicleExpenses** beyond the `rate_per_km` lookup (VehicleExpensesController.php:100-112) and simple soft-delete/status flags. These three areas match the prior finding: **direct data entry, no approval workflow, no model-level validation**.

---

## Summary of dead/unreachable code flagged for verification

| Location | Issue |
|---|---|
| EmployeeadvanceController.php:76, EmployeeExpensesController.php:76, ProjectExpensesController.php:74 | `$this->setup($emp_fkey)` calls an undefined method — fatal error for `user_group==2` on `index()` (re-confirmed) |
| EmployeeadvanceController.php:253, EmployeeLoanController.php:102, EmployeeLoanController.php:850, ExpenseItemController.php:50, PaymentApprovalsController.php:54 | Empty no-op action stubs |
| EmployeeExpensesController.php:374, 626 (commented `employeelist`); 1227, 1292 (commented `Expenses`) | Superseded/commented-out duplicate method definitions still in file |
| ProjectExpensesController.php:553 (commented `employeeverifiedlist`); 2396 (commented `project`) | Same pattern |
| ExpenseItemController.php:84-92 | Fully commented `expensefilter()` block, contains a stray `\` that would break if uncommented |
| ExpenseTypeController.php vs ExpenseTypesController.php (entire files) | Near-duplicate controllers over the same `ExpenseType` model/table; no routes.php entry distinguishes them — confirm with frontend which is live before porting either |
| EmployeeadvanceController.php:200-203 (`empprofdetails`) | Hardcodes `$emp_pkey = 1`, ignoring its own parameter — likely broken/abandoned action |
| ExpenseTypesController.php:95 | References `$time['0']['0']['time']` before `$time` is assigned in the new-record branch of `save()` — undefined-variable bug |

---

*Compiled from direct file reads and `Grep`-based signature scans of `D:\Projects\RIZOMigration\legacy\Controller\*.php` and `D:\Projects\RIZOMigration\legacy\Model\*.php`. Several very long controllers (ProjectExpensesController.php ~2500 lines, EmployeeLoanController.php ~2200 lines, EmployeeadvanceController.php ~1346 lines) were inventoried by signature + partial-body reads rather than full line-by-line reads past the ~1000-line mark; response-type classifications for those tail sections are marked "(implied)" where based on the file's consistent JSON/autoRender pattern rather than individually verified.*

---

### 2.6 Reports

# Backend Technical Report — Reports Controllers Cluster

Scope: `legacy/Controller/*Report*.php` and backing models. All controllers extend `AppController`
(session auth: `Session.user_group` 1=admin/2=employee — `Controller/AppController.php:39-46`).
Skipped per instructions: `#backup`/dated-duplicate files (e.g. `AttendanceReportsControllerBkup*.php`,
`SalaryReportsControllerbkup_nimisha_11_5_19.php`, `MiscellaniousReportsControllerBKUP.php`,
`StockReportControllerBkup-01.php`, `EditedReportsController_2018-10.php`, etc. — noted, not analyzed)
and `SynthiteSalaryReportsController.php` (customer-specific, out of scope per instructions).

**Common pattern confirmed** across almost all controllers in this cluster:
`hrreports()` (landing, sets `$arr_reporttypes`) → `changereporttype($type)` (renders `showreport`
partial, loads `ReportCriterias` for the type) → `addreportcriteria(...)` (renders `showcriteria`
partial) → `loadcriteriaitems($index, $criteria)` (renders `loadcriteriaitems` partial) →
`listcriteriaitems($criteria)` (AJAX, `autoRender=false`, `echo json_encode(...)`, returns criteria
picklist e.g. branches/departments/employees) → `generatereport($type, $mode)` (dispatches by
`$type` to a private `generate*report($mode)` worker; `$mode` is `'pdf' | 'excel' | ''`(HTML view)) →
worker method builds the dataset then, on a `switch($mode)`, either `$this->render(...)` an HTML
view, streams a PDF via **HTML2PDF** (`App::import('Vendor','HTML2PDF', ...); new HTML2PDF('L','A2','fr')`),
or streams an Excel file via **PHPExcel** (`new PHPExcel(); ... PHPExcel_Writer_Excel2007; header('Content-Disposition: attachment...'); readfile(...); unlink(...)`)
— confirmed at `Controller/ReportsController.php:1201-1514`. Most controllers also log every
generate/download call to the `ReportAudit` model (`reportAudit($type,$mode)` or `downloadHistory($type,$mode)`,
called at the tail of `generatereport()`, e.g. `ReportsController.php:678`).

All input is via classic `$_REQUEST`/`$this->request->data` (no CakePHP form validation, no
Validate rules on the report models — these are read-heavy reporting endpoints, not data-entry
controllers). Almost every action sets `$this->autoRender = FALSE` and either `echo`s JSON, calls
`$this->render('viewname')`, or streams a binary. **No REST routing / HTTP verb restriction is
enforced anywhere in this cluster** — every action responds to GET or POST identically (CakePHP 2.x
default routing, no `$this->request->allowMethod()` or `RequestHandler` verb checks found in any
file in this cluster).

---

## 1. Controller Action Inventory — Major Controllers

### 1.1 `Controller/ReportsController.php` (2,818 lines)

Employee/shift-policy/leave-policy/holiday/salary-structure reports. Uses:
`Menu, LeavePolicyGroup, HolidayGroup, CentralControl, Designation, CompanyContactInfo,
UserCredentials, EmployeeDetails, EmployeeProfessionalDetails, Departments, Verticals, Units,
ReportCriterias, DayTimeProcedures, ReportAudit, SalaryStructures` (`ReportsController.php:52`).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML view | Landing page; builds `$arr_reporttypes` menu (employee/shiftpolicy/leavepolicy/holiday/salarystructures); also reads `plan` from `comp_contact_info` via raw query | `ReportsController.php:75-97` |
| `attendance()` | GET | HTML view | Alternate landing list incl. `tax` type — appears unused/orphaned menu (no `tax` case in `changereporttype`/`generatereport` switches in this file) — **possible legacy cruft** | `ReportsController.php:99-111` |
| `miscellanious()` | GET | HTML view | Same pattern as `attendance()`, near-duplicate — **possible legacy cruft / dead route** | `ReportsController.php:113-125` |
| `sallary()` | GET | HTML view | Same near-duplicate menu, typo'd name — **possible legacy cruft / dead route** | `ReportsController.php:127-139` |
| `statutory()` | GET | HTML view | Same near-duplicate menu — **possible legacy cruft / dead route** | `ReportsController.php:141-153` |
| `changereporttype($type)` | GET/AJAX | HTML partial (`showreport`) | Loads `ReportCriterias` rows for `$type` (employee/shiftpolicy/leavepolicy/holiday/salarystructures) | `ReportsController.php:159-196` |
| `addreportcriteria($type,$newindex,$str_currentcriterias)` | GET/AJAX | HTML partial (`showcriteria`) | Loads remaining (not-yet-picked) criteria list | `ReportsController.php:202-218` |
| `loadcriteriaitems($index,$str_criteria)` | GET/AJAX | HTML partial (`loadcriteriaitems`) | Validates criteria is a real model (`_modelExists`) then renders picker | `ReportsController.php:224-240` |
| `listcriteriaitems($str_criteria,$type)` | POST/AJAX | JSON | Returns key/text pairs for a criteria model (Departments/Grades/Verticals/Units/HolidayGroup/LeavePolicyGroup/EmployeeDetails/DayTimeProcedures/SalaryStructures); branch-scoping logic hardcoded per-company-code (VGFS/VSFS/GLET/ABSG) | `ReportsController.php:242-552` |
| `reportAudit($type,$mode)` | internal (called from `generatereport`) | none (writes DB) | Builds `dataForHistory` array and `ReportAudit->save()` | `ReportsController.php:553-648` |
| `generatereport($type,$mode)` | GET/POST | delegates | Dispatches to `generateemployeereport/generateshiftpolicyreport/generateleavepolicyreport/generateholidaypolicyreport/generateSalaryStructureReport`, then calls `reportAudit` | `ReportsController.php:650-679` |
| `listemployeefields()` | GET/AJAX | JSON | Returns field-heading catalog for "Employee Information" report builder; company-code branch for `KWMT` uses different field vendor class | `ReportsController.php:681-757` |
| `generateemployeereport($mode)` (private) | — | HTML view / PDF stream / Excel stream | Employee info report; dynamic field selection + KWMT-specific join set (grade/category/nationality/countries/contract) | `ReportsController.php:765-1524` |
| `generateshiftpolicyreport($mode)` (private) | — | HTML/PDF/Excel | Shift policy report | `ReportsController.php:1526-1980` |
| `generateleavepolicyreport($mode)` (private) | — | HTML/PDF/Excel | Leave policy report | `ReportsController.php:1981-2281` |
| `generateholidaypolicyreport($mode)` (private) | — | HTML/PDF/Excel | Holiday group report | `ReportsController.php:2282-2510` |
| `generateSalaryStructureReport($mode)` (private) | — | HTML/PDF/Excel | Salary structure report (newest addition, "Edited by Akshay 11-3-2026") | `ReportsController.php:2511-2818` |

### 1.2 `Controller/ReportController.php` (300 lines — SINGULAR, distinct from `ReportsController.php`)

Not part of the hrreports pattern at all — a **plan/feature-gating catalog API** for the reports
menu UI, confirming the prior-pass finding. Uses: `Menu, LeavePolicyGroup, CentralControl, Banks,
UserCredentials, EmployeeDetails, EmployeeProfessionalDetails, Departments, EmployeeGrossDetails,
Verticals, Units, ReportCriterias, DayTimeProcedures, EmpCtcTransaction, LeaveRequests, Designation,
DbConfig, ReportAudit, FinancialYear, EmployeeTaxsalsumNew, EmployeeTaxsalsum, Gender, Plan,
Features, PlanFeature, CentralUserCredentials` (`ReportController.php:51`) — most of these are
declared but unused (only `Features`, `Plan`, `PlanFeature`, `CentralUserCredentials` are actually
referenced) — **dead `$uses` entries, verify before migrating**.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index()` | GET | empty (no body) | **Dead action / stub — possible legacy cruft**, no view set, no logic | `ReportController.php:54-56` |
| `getReportCategories()` | GET | JSON | Distinct `Features.report_list` values where `feature_key='report'`, queried against `controldb` datasource | `ReportController.php:57-79` |
| `getReportByCategory()` | GET (reads `$this->request->query('category')`) | JSON | Returns feature list for a category with `is_enabled` flag computed from the company's `plan_id` (via `CentralUserCredentials`→`PlanFeature`); applies a **company-code path-remap** (`$pathMap`) redirecting `attendanceReportsNew/hrreportsnew`→`attendanceReports/hrreports` and `TrackingReportsNew/hrreports`→`TrackingReports/hrreports` for a hardcoded blocklist of 16 company codes (`KWMT,ABSG,MBCT,DRRC,SRTS,MRBS,DJIC,STCL,SHYD,AGNG,ESNP,GTRA,VGNN,AYRK,VGFS,VSFS`) — business rule to migrate carefully | `ReportController.php:80-168` |
| (commented-out duplicate `getReportByCategory` x2) | — | — | Two full commented-out prior versions left in file — dead code, safe to drop | `ReportController.php:169-267` |
| `getArticleByFeature()` | GET (query `feature_id`) | JSON | Returns feature name/description/article (help text) from `controldb.Features` | `ReportController.php:268-297` |

### 1.3 `Controller/AttendanceReportsController.php` (~5,800 lines)

Uses: `Menu, Attendance, CentralControl, UserCredentials, EmployeeDetails,
EmployeeProfessionalDetails, DeviceAttendance, Departments, Grades, Verticals, Units,
ReportCriterias, AttendanceRegister, AttendanceRegisterReport, DbConfig, ReportAudit`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML | Landing/menu | `AttendanceReportsController.php:75` |
| `changereporttype($type)` | GET/AJAX | HTML partial | Standard pattern | `AttendanceReportsController.php:125` |
| `addreportcriteria(...)` | GET/AJAX | HTML partial | Standard pattern | `AttendanceReportsController.php:203` |
| `loadcriteriaitems(...)` | GET/AJAX | HTML partial | Standard pattern | `AttendanceReportsController.php:224` |
| `listcriteriaitems(...)` | POST/AJAX | JSON | Standard pattern | `AttendanceReportsController.php:241` |
| `downloadHistory($type,$mode)` | internal | writes `ReportAudit` | Audit log (this controller's flavor of `reportAudit`) | `AttendanceReportsController.php:449` |
| `generatereport($type,$mode)` | GET/POST | delegates | Dispatches to ~13 private generators below | `AttendanceReportsController.php:543` |
| `listemployeefields()` | GET/AJAX | JSON | Field catalog | `AttendanceReportsController.php:597` |
| `generateemployeereport` (priv) | — | HTML/PDF/Excel | Employee info (dup of ReportsController's) | `:630` |
| `generateattendancereport` (priv) | — | HTML/PDF/Excel | Attendance summary | `:823` |
| `generatesummaryreport` (priv) | — | HTML/PDF/Excel | Summary attendance | `:1106` |
| `generateVerifiedAttendancereport` (priv) | — | HTML/PDF/Excel | Verified/approved attendance | `:1705` |
| `generateDetailedreport` (priv) | — | HTML/PDF/Excel | Detailed attendance | `:2327` |
| `generatetimeattendancereport($type,$mode)` (priv) | — | HTML/PDF/Excel | Time/in-out attendance | `:2611` |
| `generatemobilelocationreport($type,$mode)` (priv) | — | HTML/PDF/Excel | Mobile geo-tagged attendance | `:2944` |
| `Overtimereport($type,$mode)` | GET/POST | delegates | OT report entry point | `:3137` |
| `generateOvertimereport($type,$mode)` | GET/POST | HTML/PDF/Excel | OT computation report | `:3537` |
| `generateCheckinlogsReport($type,$mode)` (priv) | — | HTML/PDF/Excel | Raw check-in/out logs | `:4415` |
| `generateregularisationreport($type,$mode)` (priv) | — | HTML/PDF/Excel | Attendance-regularization requests | `:4656` |
| `generatenonpunchedreport($type,$mode)` | GET/POST | HTML/PDF/Excel | Employees who didn't punch | `:4984` |
| `generatenonattendancereport($type,$mode)` | GET/POST | HTML/PDF/Excel | Absentee report | `:5295` |
| `Updateame()` | GET/POST | ? (not fully inspected) | Legacy-named action, unclear purpose from name alone — **verify before migrating** | `:5613` |
| `listpunches($emp_pkey,$month)` | GET | JSON/HTML | Per-employee punch list | `:5658` |
| `processregisterentries($branchcode,$month)` | GET/POST | ? | Batch register processing | `:5759` |

`Controller/AttendanceReportsNewController.php` (~7,400 lines) mirrors this almost 1:1 (same
action set, `hrreportsNew()` instead of `hrreports()`, plus one extra `getEmployeesByBranch()` at
`:7343` and a `generatemovementsreport`/`generateLOPreport` pair not present in the old controller)
— this is the newer parallel implementation referenced by the `attendanceReportsNew/hrreportsnew`
path in `ReportController.php`'s `$pathMap`. Both controllers write `ReportAudit` via
`downloadHistory()` (`AttendanceReportsNewController.php:541-542`).

### 1.4 `Controller/SalaryReportController.php` (singular — small, ~470 lines, distinct helper controller)

Not the standard hrreports pattern. Uses: `Units, EmployeeDetails, Designation, EditPunches,
SalarySlip, Payrollmaster, EmpCtcTransaction, EmployeeSalaryStructure`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index()` | GET | HTML | Landing, sets `arr_reporttypes` (Payroll Summary / CTC Summary / CTC Detail — several other types commented out, e.g. `Salaryslip`, `Grosssalary`, `DetailedAttendance` — **possible legacy cruft, verify which report types are still live**) | `SalaryReportController.php:55-77` |
| `Branches($hierarchy)` | GET/AJAX | JSON (via `return`, not `echo`) | Branch picklist | `:79-95` |
| `Designation($hierarchy)` | GET/AJAX | JSON | Designation picklist | `:97-...` |
| `Departments($hierarchy)` | GET/AJAX | JSON | Department picklist | `~:116` |
| `Employees($hierarchy)` | GET/AJAX | JSON | Employee picklist | `~:134` |
| `Generate()`-equivalent not present here — this controller looks like a **hierarchy/picklist helper only**, no actual report generation logic found in its short action list; distinct role from the plural `SalaryReportsController.php` | | | | |

### 1.5 `Controller/SalaryReportsController.php` (plural — MASSIVE, ~45,000 lines, the real salary report engine)

Uses: `Units, EmployeeDetails, Designation, EditPunches, ReportCriterias, ReportAudit, ...` (large
`$uses` array; not fully enumerated here for space). This single file contains the standard
`hrreports→changereporttype→addreportcriteria→loadcriteriaitems→listcriteriaitems→downloadHistory→
generatereport→listemployeefields` chain (`:75-1352`) plus **~55 private report-generator methods**,
including numerous **customer-specific duplicated variants** (suffixes `PSQUARE`, `SBL`, `KWMT`,
`Synthite`, `HEDGE`) of the same report — e.g. `GenerateSalaryGrossPeriod` /
`GenerateSalaryGrossPeriodPSQUARE`, `GenerateSalarySlipreport` / `GenerateSalarySlipreportPSQUARE` /
`GenerateSalarySlipreportSBL`, `generateLOPReport` / `generateLOPReportHEDGE`. This is the single
largest maintenance/migration risk file in the whole cluster.

| Action / representative generator | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML | Landing/menu | `:75` |
| `changereporttype/addreportcriteria/loadcriteriaitems/listcriteriaitems` | GET/POST/AJAX | HTML partial / JSON | Standard pattern | `:319-919` |
| `downloadHistory($type,$mode)` | internal | writes `ReportAudit` | Audit log | `:919` |
| `generatereport($type,$mode)` | GET/POST | delegates | Dispatches to ~55 generators by `$type` | `:1121` |
| `listemployeefields()` | GET/AJAX | JSON | Field catalog | `:1352` |
| `getAccountPeriod()` | GET/AJAX | JSON | Payroll account-period lookup | `:1386` |
| `GenerateSalarySlipreportExempted($mode)` (priv) | — | HTML/PDF/Excel | Salary slip, tax-exempt employees | `:1426` |
| `sendSliptoMail()` | POST | JSON (email dispatch) | Emails salary slip PDFs to employees — **side-effecting action embedded in a "report" controller** | `:2359` |
| `sendSliptoMailSynthite()` | POST | JSON | Customer-specific slip-email variant | `:2638` |
| `GenerateSalarySlipnewreport($mode)` (priv) | — | HTML/PDF/Excel | Salary slip (newer template) | `:2902` |
| `generateBanktransferreport($type,$mode)` (priv) | — | HTML/PDF/Excel | Bank transfer/salary disbursement file | `:3996` |
| `generateBanktransfernewreport($type,$mode)` (priv) | — | HTML/PDF/Excel | Bank transfer (new format) | `:4741` |
| `generateEmpSalaryreport($mode)` (priv) | — | HTML/PDF/Excel | Employee salary report | `:5462` |
| `GenerateSalaryGross($mode)` / `...GrossPeriod` / `...GrossNew` / `...GrossSummary` (priv) | — | HTML/PDF/Excel | Gross salary reports (several date-range/format variants) | `:5838, 7568, 10013, 11153` |
| `GenerateSummaryPayrolreport($mode)` (priv) | — | HTML/PDF/Excel | Payroll summary | `:11808` |
| `generatesalaryreport($mode)` (priv) | — | HTML/PDF/Excel | General salary report | `:12455` |
| `generateEmpMonthlyCTCReport($mode)` (priv) | — | HTML/PDF/Excel | Monthly CTC report | `:12948` |
| `generateLOPReport($mode)` (priv) | — | HTML/PDF/Excel | Loss-of-pay report | `:13380` |
| `GenerateSalaryslipFirstVersion/SecondVersion/ThirdVersion($mode)` (priv) | — | HTML/PDF/Excel | Three parallel salary-slip template versions kept live simultaneously — **verify which is current before migrating** | `:14125, 14997, 15868` |
| `GenerateDepositSlipreportSynthite($mode)` (priv) | — | HTML/PDF/Excel | Customer-specific deposit slip | `:16741` |
| `GenerateSalaryComparison($mode)` (priv) | — | HTML/PDF/Excel | Period-over-period salary comparison | `:18068` |
| `itemcriteriaSite/listBankBranches` | GET/AJAX | JSON | Bank-branch picklists for bank-transfer report | `:21053, 21062` |
| `generateCustomreport($mode)` (priv) | — | HTML/PDF/Excel | Ad-hoc/custom report | `:21116` |
| `generateSalaryBulkUploadreport($type,$mode)` (priv) | — | HTML/PDF/Excel | Bulk-upload template/report | `:21792` |
| `generatePayrollCTC($type,$mode)` (priv) | — | HTML/PDF/Excel | Payroll CTC report | `:22569` |
| `...PSQUARE` variants (14 methods) | — | HTML/PDF/Excel | Customer-specific (P-Square) duplicate of most above reports | `:23328-34246` |
| `GenerateSalarySlipreportSBL($mode)` / `sendSliptoMailSBL()` | — / POST | HTML/PDF/Excel / email | Customer-specific (SBL) variants | `:36719, 38324` |
| `GenerateSalaryGrossKWMT($mode)` | — | HTML/PDF/Excel | Customer-specific (KWMT) | `:38757` |
| `GenerateSalaryAccountReport($mode)` | — | HTML/PDF/Excel | Salary/account reconciliation | `:40503` |
| `GenerateSalarySlipreport($mode)` | — | HTML/PDF/Excel | "Default" salary slip generator (post-variants) | `:41101` |
| `GenerateSalaryGrossNonExemted/GrossNewNotExempted($mode)` | — | HTML/PDF/Excel | Non-tax-exempt variants | `:42057, 43850` |
| `generateLOPReportHEDGE($mode)` | — | HTML/PDF/Excel | Customer-specific (HEDGE) LOP report | `:44984` |
| `getprodataDesc/getprodataDesc_branch` | GET/AJAX | JSON | Small lookup helpers | `:12826, 12870` |

### 1.6 `Controller/StatutoryReportController.php` (~13,000 lines — the real statutory/ESI/EPF/PT/TDS engine)

Uses (via `TaxReportController.php`-style pattern, confirmed at file top):
`LeaveRequests, Leavestatus, SalaryHeadItems, CentralControl, UserCredentials, EmployeeDetails,
EmployeeProfessionalDetails, DeviceAttendance, Departments, Grades, Verticals, Units,
ReportCriterias, AttendanceRegister, AttendanceRegisterReport, CompanyContactInfo,
EmployeeTaxsalsum, ReportAudit`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML | Landing/menu | `StatutoryReportController.php:85` |
| `changereporttype/addreportcriteria/loadcriteriaitems/listcriteriaitems` | GET/POST/AJAX | HTML partial / JSON | Standard pattern | `:158-504` |
| `reportAudit($type,$mode)` | internal | writes `ReportAudit` | Audit log | `:504` |
| `generatereport($type,$mode)` | GET/POST | delegates | Dispatches to statutory generators | `:627` |
| `listemployeefields()` | GET/AJAX | JSON | Field catalog | `:687` |
| `generateemployeelabour($mode)` (priv) | — | HTML/PDF/Excel | Employee "labour" register (ESI/EPF eligible roster) | `:727` |
| `generateESIlabourreport($mode)` (priv) | — | HTML/PDF/Excel | **ESI contribution report** — employee ESI = `ceil($esi_salary * .0075)` (0.75%) computed inline in the controller (`:2451`) | `:1725` |
| `generateESINEWlabourreport($mode)` (priv) | — | HTML/PDF/Excel | ESI report (new format) | `:2888` |
| `generateEPFlabourreport($mode)` (priv) | — | HTML/PDF/Excel | **EPF contribution report** — EPF = `round($salary*.12)`, EPS = `round($salary*.0833)`, EDLI = EPF−EPS, all computed inline (`:4408` and mirrored logic in `EsiEpfReportController.php:1987-1989`) | `:3873` |
| `generatesummaryreport($mode)` (priv) | — | HTML/PDF/Excel | Statutory summary | `:4632` |
| `generateprofessionaltaxreport($mode)` (priv) | — | HTML/PDF/Excel | **Professional Tax report** — PT amount pulled from a `professional_tax_view` SQL view / `tax_salary_components` config rather than hardcoded slabs (`:822-823, 5103-5104`) — business rule lives in the DB view, not the controller | `:4885` |
| `generateEPFNEWlabourreport($mode)` (priv) | — | HTML/PDF/Excel | EPF report (new format) | `:5434` |
| `generateTAXreport($mode)` (priv) | — | HTML/PDF/Excel | Income-tax/TDS report | `:6870` |
| `generate12BBFormreport($mode)` (priv) | — | PDF (Form 12BB is a fixed statutory format) | Form 12BB (investment declaration) generator | `:9226` |
| `generateEPFSYNTHIETlabourreport/generateESISYNTHIETlabourreport/generateEPFUPLOADSYNTHIETlabourreport` (priv) | — | HTML/PDF/Excel | Customer-specific (Synthite) EPF/ESI variants | `:9461, 10291, 11350` |
| `generateprofessionaltaxsalaryreport()` / `...KWMT($mode)` (priv) | — | HTML/PDF/Excel | PT-vs-salary reconciliation, incl. KWMT customer variant | `:12121, 12585` |

**Naming warning**: `Controller/statutoryReportsController.php` (lowercase `s`, plural, 956 lines)
is a **separate, much smaller controller** with only `hrreports/changereporttype/.../
generateemployeereport/generatesummaryreport/generateleavebalancereport` — despite the similar
name it does **not** contain statutory/tax logic; it's a generic employee+summary+leave-balance
report clone. On case-insensitive filesystems (Windows, as here) these two files could collide —
**flag for migration naming cleanup**.

### 1.7 `Controller/TaxReportController.php` (small, ~1,114 lines — misleadingly named)

Despite the name, this controller's only real report type is `TaxTDS` ("Tax TDS Report") which
dispatches to `generatesummaryreport()`; the rest of its report types are **leave/attendance**
reports, not tax reports:

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML | Landing; menu = `TaxTDS` only (`Tax TDS Report`) | `TaxReportController.php:72-79` |
| `changereporttype($type)` | GET/AJAX | HTML partial | Switch handles `employee, TaxTDS, LeaveDetaillsReport, LeaveBalance, DetailedAttendance, TimeAttendance` — **more cases than are reachable from the menu or from `generatereport`'s dispatch switch** | `:84-123` |
| `addreportcriteria/loadcriteriaitems/listcriteriaitems` | GET/POST/AJAX | HTML partial / JSON | Standard pattern | `:128-363` |
| `generatereport($type,$mode)` | GET/POST | delegates | Only wires up `employee→generateemployeereport`, `TaxTDS→generatesummaryreport`, `LeaveBalance→generateleavebalancereport`, `DetailedAttendance→generateDetailedreport`, `TimeAttendance→generatetimeattendancereport` — **but `generateDetailedreport` and `generatetimeattendancereport` are referenced here yet have no corresponding `private function` defined in this file** (only `generateemployeereport`, `generatesummaryreport`, `generateleavebalancereport` exist per the earlier grep) — **dead/broken routes, verify before migrating**; `LeaveDetaillsReport` case exists in `changereporttype` but has no case at all in `generatereport` — **possible legacy cruft** | `:364-388` |
| `listemployeefields()` | GET/AJAX | JSON | Field catalog | `:390` |
| `generateemployeereport($mode)` (priv) | — | HTML/PDF/Excel | Employee info | `:423` |
| `generatesummaryreport($mode)` (priv) | — | HTML/PDF/Excel | "TaxTDS" summary — actually a generic summary report, no visible TDS-specific tax calculation in this file | `:623` |
| `generateleavebalancereport($mode)` (priv) | — | HTML/PDF/Excel | Leave balance report | `:790` |

No `ReportAudit` write found in this controller (`ReportAudit` not in `$uses`) — **inconsistent
with the rest of the cluster; verify whether audit logging is intentionally skipped here.**

### 1.8 `Controller/HierarchyReportController.php` (~1,000 lines)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML | Landing/menu | `HierarchyReportController.php:60` |
| `changereporttype/addreportcriteria/loadcriteriaitems/listcriteriaitems` | GET/POST/AJAX | HTML partial / JSON | Standard pattern | `:72-252` |
| `downloadHistory($type,$mode)` | internal | writes `ReportAudit` | Audit log | `:252` |
| `generatereport($type,$mode)` | GET/POST | delegates | Dispatches to the two generators below | `:322` |
| `listemployeefields()` | GET/AJAX | JSON | Field catalog | `:340` |
| `generateemployeehierarchyreport($type,$mode)` (priv) | — | HTML/PDF/Excel | Org-chart / reporting-line hierarchy for employees | `:376` |
| `generateleavehierarchyreport($type,$mode)` (priv) | — | HTML/PDF/Excel | Leave-approval hierarchy report | `:715` |

---

## 2. Condensed Inventory — Smaller/Niche Report Controllers

| Controller | Main action(s) | Response type | Notes / path |
|---|---|---|---|
| `AccessDetailReportController.php` | `hrreports→...→generatereport`; workers: `generateaduitreport`, `generateformauditreport`, `generateloginauditreport` | HTML/PDF/Excel | Audit-trail reports (data-change audit, form audit, **login audit** — reads `login_auditor` table via the `CentralControl` model, whose PHP class is literally named `CentralControl` mapped to table `login_auditor`, `useDbConfig='controldb'` — **filename/model-name mismatch, verify at migration**, `Model/login_auditor.php:9-22`). No `ReportAudit` write in this controller (writes audit data as its subject, doesn't self-log) — `AccessDetailReportController.php:54-529` |
| `ArrearsReportsController.php` | Standard chain; `generatearrearreport($type,$mode)` | HTML/PDF/Excel | Salary-arrears report; writes `ReportAudit` via `downloadHistory` — `:76-437` |
| `AssetsReportsController.php` | Standard chain; `generateHistoryreport($mode)` | HTML/PDF/Excel | Asset allocation/history report; writes `ReportAudit` via `reportAudit` — `:71-439` |
| `CompanyProfileReportController.php` | `index/addcriterias/reportAudit($company,$branch,$department,$designation,$bank)/viewreport(...)` | HTML view | **Deviates from standard pattern** — explicit positional params instead of criteria-picker flow; `reportAudit` here is overloaded to mean "build filter criteria," not "write ReportAudit log" (misleading name reuse vs. rest of cluster) — `:56-98+` |
| `ConfigReportController.php` | `index/Branches/Designation/Departments/Employees/Generate()` | JSON (picklists) + report | Small hierarchy-picklist helper + a `Generate()` action using `EditPunches` model — unclear if still wired to a live UI, **verify before migrating** — `:55-140+` |
| `EditedReportsController.php` | Standard chain; `generateeditedreport($mode)`, `generateattendancereport`, `generatesummaryreport` | HTML/PDF/Excel | Reports on manually-edited attendance/salary records; writes `ReportAudit` — `:80-1473` |
| `EmpOtRegisterController.php` | `Register/Approved/form/save/Toapproved/index/jsons/subtable/approves/Setvalue/remarks` | HTML/JSON, POST for `save/Toapproved/approves/Setvalue` (uses `$this->request->data`) | **Not a "report" in the read-only sense** — this is an OT approval workflow controller (data-writing: `save()`, `Toapproved()`, `approves()`) accidentally in the reports cluster; needs to be modeled as a mutation API, not a report endpoint, when migrating — `:53-279` |
| `EmpreportController.php` | Large ad-hoc set: `salarystructure/attendancedetails/refresh/downloads/downloadexcels/attendanceReports/employeelist/getattendancedays/showattendancedetails/employeeleavelist/attendance/attendanceregister/leavepolicyreport/holidayreport/leavedaysreport/leavedetailsreport/shiftpolicyreport/attendencereportpdf/leavereportpdf/CustomerVisits/AttendanceLocation/KmTravelled/KmtravelledTracking` (30 actions) | HTML/PDF/Excel/JSON mixed | Appears to be an **employee self-service** report portal (single-employee-scoped, not admin criteria-based) — distinct architecture from the rest of the cluster; large surface area, budget extra migration time — `:39-2259` |
| `EsiEpfReportController.php` | `hrreports/reportAudit($type,$mode,$month)/generatereport()/generatereportPdf()/generate_report(...)/generate_wps_template(...)/generate_esi_contr(...)/epfUploadReport(...)` | HTML/PDF/Excel | **No `changereporttype/addreportcriteria/loadcriteriaitems`** — deviates from standard chain, uses direct params (`$from,$type,$subcat,$branch`) instead; embeds EPF formula `epf_contr = round(salary*.12)`, `eps_contr = round(salary*.0833)`, `edli_contr = epf_contr - eps_contr` (`:1987-1989`) and ESI `salary/.0075` inverse-calc (`:1376`) — WPS (Wage Protection System) bank-upload template generator (`generate_wps_template`) is a statutory/regulatory export, high migration priority — `:80-1817` |
| `EventReportsController.php` | Standard chain; `generateemployeeevents($mode)` | HTML/PDF/Excel | Employee event-log report; writes `ReportAudit` — `:55-345` |
| `InteligenceReportsController.php` | Standard chain; `IntelligenceandNonCompliancereport($mode)` | HTML/PDF/Excel | Compliance/anomaly-detection report; writes `ReportAudit` via `downloadHistory` — `:76-524` |
| `LopReportsController.php` | Standard chain; `generatelopreport($mode)` | HTML/PDF/Excel | Loss-of-pay report (separate from the LOP logic embedded in `SalaryReportsController.php`/`AttendanceReportsNewController.php` — **possible duplicated business logic across 3+ files, verify single source of truth**) — `:75-442` |
| `MiscellaniousReportsController.php` | Standard chain; large set: `generatesummaryreport, generateleavebalancereport, generateleavebalancesummaryreport, generatecompoffreport, generateleavebalancemonthlyreport, generatecompoffreportnew, generatelopReport` + `PSQUARE`-suffixed duplicates + `_new`-suffixed duplicates | HTML/PDF/Excel | Comp-off & leave-balance reports; heavy duplication pattern (old/new/PSQUARE variants) mirrors `SalaryReportsController.php` — `:75-9075` (includes a private helper `_buildLopRow(array $row)` at `:9075`, unusual typed-array signature for this legacy codebase) |
| `ReceiptReportController.php` | `index/printgenerateinvoicereport/generatereceiptreport/getcontactsbysite` | HTML/PDF | **Deviates from standard pattern** — invoice/receipt generation, no criteria chain, no `ReportAudit` write — `:45-336` |
| `ReconciliationReportController.php` | Standard chain; `generateReconciliationReport($mode)`, `generateReconciliationSectionReport($mode)` | HTML/PDF/Excel | Payroll reconciliation report; writes `ReportAudit` via `downloadHistory` — `:53-1058` |
| `ResighnedReportsController.php` (sic — "Resighned" typo for "Resigned") | Standard chain; `generateemployeereport, generateattendancereport, generatesummaryreport, generateDetailedreport, generatetimeattendancereport, generatemobilelocationreport` | HTML/PDF/Excel | Reports scoped to resigned/exited employees; writes `ReportAudit` via `reportAudit` — `:79-2167` |
| `SalarySlipReportsController.php` | `index/Reports($id)/SalarySlipdownload($id)/SalarySlipdownloadpdf($id)/slipsecondversion($id)/SalarySlipdownloadSecondVersion($id)/getempdetails($id)/slipthirdversion($id)` | HTML/PDF | **Deviates from standard pattern** — single-employee ID-driven slip download (not criteria-based); three parallel slip-template versions (first/second/third) mirror the ones inside `SalaryReportsController.php` — likely redundant with `GenerateSalaryslipFirstVersion/SecondVersion/ThirdVersion` there, **verify which is canonical** — `:60-694` |
| `SiteReportsController.php` | Standard chain + extras: `itemcriteria/itemcriteriaSite/itemcriterialist/itemcriterialistSite/sitelists`; workers: `generateClientReportStandard, generateemployeereport, generateDetailedreport, generateEmployeePayHoursreport, generateCheckinlogsReport, generateClientReport, generateRottaMaster/new, generateSiteRate, generateCustomerReport` | HTML/PDF/Excel | Site/client-facing (security-guard-industry-style "rota"/site-rate) reports; writes `ReportAudit` — `:76-5390` |
| `StockReportController.php` | Standard chain + `get_all_items/itemlist/itemcriteria`; workers include stock reports AND payroll reports (`GenerateSalaryGrossSummary`, `GenerateSummaryPayrolreport`, `GenerateSalarySlipreport`) alongside `GenerateStockAllDetailsreport, GenerateStockTransferreport, GenerateAllocationreport, GenerateUniformAllocationEMIReport, GenerateStockreport, GeneratePoReturnreport, GeneratePoreport, GenerateGRNreport, GenerateItemRatereport` | HTML/PDF/Excel | Dual-purpose controller mixing inventory/stock reports (PO, GRN, uniform allocation) with payroll reports — likely a customer-specific (security/facilities industry) bolt-on; writes `ReportAudit` via `reportAudit` — `:76-4594` |
| `TrackingReportsController.php` | Standard chain; workers: `generateClient, generateCustomer_google, generateCustomer, generateAttendanceLocation, generateMobileTrack, generateKmTravelled, generateKmTravelled_google, generateKmTravelledTracking` | HTML/PDF/Excel | GPS/location tracking reports (Google Maps-integrated distance/km reports); writes `ReportAudit` — `:38-3297` |
| `TrackingReportsNewController.php` | Same as above + `getEmployeesByBranch()`, `callDistanceAPI($url)` (external HTTP call to a distance/geocoding API — **outbound third-party API dependency to note for migration**) | HTML/PDF/Excel | Newer parallel version, referenced by `ReportController.php`'s `$pathMap` remap — `:38-3464` |
| `VariableReportController.php` | Standard chain; `generatevariableuploadreport($mode)`, `generatefixeduploadreport($mode)` | HTML/PDF/Excel | Variable-pay / fixed-pay bulk-upload templates & reports; writes `ReportAudit` — `:135-1169` |

---

## 3. Model Validation & Business Rules

### 3.1 `ReportAudit` — the shared download/view audit-log model

`Model/ReportAudit.php` (`:1-18`) is a **bare `AppModel` subclass** — `$name='ReportAudit'`,
`$primaryKey='id'`, `$useTable='report_audit'`, **no `$validate` rules, no callbacks**. All
validation/shaping happens in the calling controllers before `->save()`.

Fields written (compiled from `ReportsController.php:558-647`, mirrored with minor variation across
every controller that logs; representative field set):

| Field | Populated from | Notes |
|---|---|---|
| `report_type` | Hardcoded per-`$type` string, e.g. "Employee Information Report" | `ReportsController.php:562-578` |
| `report_from` / `report_to` | `$_REQUEST['reportfrom']/['reportto']` | Date-range filter used, if present |
| `include_resigned` | `$_REQUEST['resigned']` | |
| `Include_negative_salary` | `$_REQUEST['ngtvsal']` | Salary-report-specific flag (inconsistent camel-case field name — capital "I") |
| `criteria` | Comma-joined list of criteria model names used (e.g. `Units,Departments`) | Built from `hidden-criteriaN` fields |
| `criteria_name` | Comma-joined human-readable labels for the above | Hardcoded lookup switch, e.g. `'Units' → 'belonging to a Branch'` |
| `items` | Comma-joined selected item values per criteria | |
| `items_count` | Comma-joined counts per criteria | |
| `mode` | `'PDF Download' \| 'Excel Download' \| 'View Report'` derived from `$mode` param | |
| `user_id` / `user_name` | `Session::read('login_user_id')` / `Session::read('user_name')` | Ties audit row to the session user — this is the closest thing to an authorization/attribution record in this cluster |

This model+pattern is the **only** cross-cutting "business rule" enforcement in the whole
reports cluster: every report download/view is meant to be attributable to a user, and every
controller in section 2 that writes it should be migrated with equivalent audit-log behavior
preserved (needed for compliance, given statutory reports like ESI/EPF/PT/Form 12BB are involved).
Not every controller in scope writes it — noted per-controller above; `ReportController.php`
(singular), `TaxReportController.php`, `ReceiptReportController.php`, `ConfigReportController.php`,
`CompanyProfileReportController.php`, `EmpOtRegisterController.php`, `EmpreportController.php`,
`SalarySlipReportsController.php`, `AccessDetailReportController.php` do **not** call
`ReportAudit->save()` — **verify intentional vs. missed instrumentation before migration.**

### 3.2 `ReportCriterias` model

`Model/ReportCriterias.php` (`:1-16`) — also a bare `AppModel`, `$useTable='reportcriterias'`, no
validation. Purely a lookup/config table (`reportcriteria`, `reportcriteria_desc`, `reporttype`,
`status` columns inferred from controller queries) driving which criteria pickers appear per report
type. No business logic here; it's config data, safe to migrate as a simple reference table.

### 3.3 Embedded statutory calculation formulas (the real "business rules" in this cluster)

These are **not** in any model — they are computed inline inside controller report-generator
methods, which is itself a migration risk (formulas duplicated across files):

- **ESI (Employee State Insurance), employee contribution**: `0.75%` of ESI-wage, computed as
  `ceil($esi_salary * .0075)` — `StatutoryReportController.php:2451` (also inverse form
  `$salary / .0075` in `EsiEpfReportController.php:1376`).
- **EPF (Employees' Provident Fund)**: `12%` of PF-wage — `round($employer_epf_salary * .12)` —
  `StatutoryReportController.php:4408`, and again independently in `EsiEpfReportController.php:1987`
  (`round($epf_salary * .12)`).
- **EPS (Employees' Pension Scheme)**: `8.33%` of PF-wage — `round($eps * .0833)` —
  `EsiEpfReportController.php:1988`.
- **EDLI (Employees' Deposit Linked Insurance)**: derived as EPF-contribution minus EPS-contribution
  — `StatutoryReportController.php:4408`, `EsiEpfReportController.php:1989`.
- **Professional Tax**: *not* hardcoded — pulled from a SQL view `professional_tax_view` joined
  against `emp_pkey`, and from a `tax_salary_components` config table filtered on
  `lcase(tax_salary_components_name)='professional tax'` — `StatutoryReportController.php:822-823,
  5103-5104`. This means PT slabs are DB-configured, not code-configured — the actual slab logic
  lives in a database view not reviewed in this pass (out of scope: DB objects) — **flag for
  DB-view review during migration**, since the amount ultimately consumed by the report is only as
  correct as that view.
- Because the 0.75% / 12% / 8.33% constants above are copy-pasted (not shared via a constants file
  or config table) across at least two controllers, **recommend centralizing these statutory rates
  into a single config/constants module during the Next.js migration** rather than porting the
  duplication as-is.

### 3.4 Other model note

`Model/login_auditor.php` — despite the filename, the PHP class inside is `class CentralControl
extends AppModel` (`:9`), `$useTable='login_auditor'`, `$useDbConfig='controldb'`. This is consumed
by `AccessDetailReportController.php`'s `generateloginauditreport()`. **Filename/class name
mismatch — verify the intended model identity before porting** (this may be an accidental copy from
elsewhere in the codebase, or CakePHP's flexible class-to-file convention masking a rename).

---

### 2.7 Company / Organization Setup

# Company / Organization Setup — Controller & Model Inventory

Scope: `Controller/CompanyController.php`, `CompanyNewController.php`, `CompanySetupController.php`,
`BranchController.php`, `DepartmentController.php`, `DesignationController.php`, `DivisionController.php`,
`SectionController.php`, `GradeController.php`, `GradesController.php`, `GradesNewController.php`,
`CategoryController.php`, `CategoryMasterController.php`, `VerticalController.php`, `DbConfigController.php`,
`TemplateController.php`, `UniformController.php`, `BankController.php`, and their backing models.

All controllers extend `AppController` (session auth, dynamic per-company `useDbConfig`/`ds` datasource
switching set up in `Controller/AppController.php:48-95`). Every "list" action reads pagination params
straight off `$_REQUEST`/`$_POST` (no CakePHP Security component / CSRF token checks observed), and almost
all mutating actions set `$this->autoRender = FALSE` and hand-roll `json_encode()` output — these are
JSON endpoints in view/routing only, not helpers.

---

## 1. Controller Action Inventory

### CompanyController.php (real "Company Info / Compliance / Policy" panel — separate from CompanySetupController)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view (sets `plan`) | Dashboard landing; reads subscription `plan` via raw query on `comp_contact_info` | Controller/CompanyController.php:56-69 |
| initialsetup | GET | HTML view (empty) | Stub — no logic | Controller/CompanyController.php:71-73 |
| setup | GET | HTML view (empty) | Stub — no logic | Controller/CompanyController.php:75-77 |
| viewinfo | GET | HTML view | Loads contact info + compliance info + `db_config` attendance/payroll settings | Controller/CompanyController.php:79-126 |
| infoedit | GET | HTML view | Same as viewinfo, for edit form | Controller/CompanyController.php:127-168 |
| contactinfo | GET/AJAX | JSON | Returns `CompanyContactInfo` first row | Controller/CompanyController.php:170-181 |
| loadContactInfo | GET/AJAX | JSON | Same, wrapped in `success/data` envelope | Controller/CompanyController.php:183-199 |
| savecompanysetup | POST | JSON | Saves contact info + logo upload + compliance info + updates `db_config` attendance_format/payroll_type via raw interpolated SQL (values are server-derived, not directly SQLi but still string-built) | Controller/CompanyController.php:320-456 (SQL at 446-450) |
| getCompanyComplianceInfo | GET/AJAX | JSON | Returns compliance info fields | Controller/CompanyController.php:458-491 |
| saveCompanyComplianceInfo | POST | JSON | Saves compliance info (id hardcoded to 1) | Controller/CompanyController.php:493-516 |
| savePolicy | POST | none (no `echo`/render — silent) | Saves up to 5 policy key/value rows from `$_POST` | Controller/CompanyController.php:518-570 |
| getPolicyInfo | GET/AJAX | JSON | Returns policy info with hardcoded defaults | Controller/CompanyController.php:572-604 |

There's a large commented-out duplicate of `savecompanysetup()` left in the file (lines 201-318) — dead code, safe to delete during migration.

### CompanyNewController.php — duplicate of CompanyController

Nearly byte-identical to `CompanyController.php` minus the `plan`/`Menu` additions and the `db_config` policy-cycle update block. Same action list: `index, initialsetup, setup, viewinfo, infoedit, contactinfo, loadContactInfo, savecompanysetup, getCompanyComplianceInfo, saveCompanyComplianceInfo, savePolicy, getPolicyInfo` (Controller/CompanyNewController.php:56-449). **Possible legacy cruft — verify before migrating**; nothing distinguishes it functionally from `CompanyController.php` and it is not referenced by the other controllers in this cluster.

### CompanySetupController.php — subscription/feature-gate + Razorpay payment (NOT the org-structure setup hub)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Sets `arr_branches` combo list via `MasterdataManagement` component | Controller/CompanySetupController.php:10-16 |
| getCompanyFeatures | GET/AJAX | JSON | Looks up company's `plan_id` from `controldb.user_credentials`, joins `Features`×`PlanFeature` to build an enabled-feature list, scoped to `feature_key = 'company'` | Controller/CompanySetupController.php:17-77 |
| createRazorpayOrder | POST | JSON | Creates a Razorpay order via cURL; **hardcoded test API key/secret** `rzp_test_RUncVaytiPXcE3` / `5mvotOLNsHZXDeb5g5YaiXRE` | Controller/CompanySetupController.php:79-108 (keys at 81-82) |
| verifyPayment | POST | JSON | Verifies Razorpay HMAC signature, records `PlanPaymentHistory`, and on `captured` status **upgrades the plan via raw interpolated SQL** | Controller/CompanySetupController.php:109-184 |
| getRazorpayPaymentDetails | internal (called from verifyPayment) | array (not directly routable as JSON) | Fetches payment details from Razorpay API using same hardcoded key/secret | Controller/CompanySetupController.php:187-199 |

**SQL injection surface** — `CompanySetupController::verifyPayment()`, Controller/CompanySetupController.php:154-158:
```php
$this->CentralUserCredentials->query("
        UPDATE user_credentials 
        SET plan_id = {$plan_id_value}
        WHERE LOWER(user_id) = LOWER('{$lower_user_id}')
    ");
```
`$lower_user_id` is `strtolower($this->Session->read('login_user_id'))` (Controller/CompanySetupController.php:148-151) — session-derived rather than raw `$_POST`, so exploitation requires either a prior session-fixation/impersonation bug or a code path that lets a user influence `login_user_id`, but it is still raw string interpolation into SQL with no `Sanitize`/prepared statement, and `$plan_id_value` is cast to `(int)` (safer) while `$lower_user_id` is not escaped at all. Flag as a SQLi-shaped defect regardless of current exploitability — must be parameterized in the Next.js port.

### BranchController.php (backs `Units` model, table `branches`; company-branch registration also writes to control DB `mypayrol_control_db.company_branches`)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing list page | Controller/BranchController.php:55-59 |
| form | GET | HTML view (`layout=null`) | Edit/create form; also queries `fin_year` table for current fin/leave year (raw interpolated SQL using `$branch_code` from DB, not user input) | Controller/BranchController.php:60-86 |
| branches | GET | HTML view (`layout=false`) | Static branches listing shell | Controller/BranchController.php:87-90 |
| load | GET/AJAX | JSON | Loads a single branch's fields by `$_REQUEST['id']` | Controller/BranchController.php:92-112 |
| savebranch | POST | JSON | Upserts a branch (`Units`), generates `branch_code`, **also inserts into `mypayrol_control_db.company_branches` (cross-DB write, control DB)** and inserts/updates `fin_year` rows for financial + leave year, all via interpolated SQL built from request data | Controller/BranchController.php:114-197 (control-DB insert at 155-160) |
| listunits | GET/AJAX | JSON | Paginated/sorted branch listing joined with `fin_year` (LeaveYear/FinYear) | Controller/BranchController.php:199-265 |
| deleteBranches | POST/GET | JSON | Soft-deletes (`status=0`) via `updateAll` on comma-separated ids | Controller/BranchController.php:267-283 |
| checkbranchexists | POST/AJAX | plain int (echoed, not JSON-wrapped) | Uniqueness check for `branch_name` excluding current id | Controller/BranchController.php:284-303 |

Note: `savebranch()` builds several raw SQL strings from `$company_code`/`$branch_code`/dates derived from request data (Controller/BranchController.php:149,155-160,175-179) — another SQLi-shaped surface worth flagging even though most of the identifiers pass through prior lookups.

### DepartmentController.php (model `Departments`, table `department`)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing (`layout=false`) | Controller/DepartmentController.php:55-63 |
| form | GET | HTML view (`layout=null`) | Create/edit form | Controller/DepartmentController.php:66-82 |
| savedepartment | POST | JSON | Upsert with duplicate-code/name guard (calls `checkdepartmentcodeexists`/`checkdepartmentexists` inline) | Controller/DepartmentController.php:83-120 |
| listdepartments | GET/AJAX | JSON | Paginated/sorted list, `status=1` filter | Controller/DepartmentController.php:121-152 |
| deleteDepartment | POST/GET | JSON | Soft-delete via `updateAll`, **no guarded-delete check for assigned employees** (unlike Division/Section) | Controller/DepartmentController.php:154-171 |
| checkdepartmentexists | POST/AJAX (also called internally) | int (returned, not echoed directly outside `save`) | Uniqueness check on `dept_name` | Controller/DepartmentController.php:172-188 |
| checkdepartmentcodeexists | POST/AJAX (also called internally) | int | Uniqueness check on `dept_code` | Controller/DepartmentController.php:190-208 |

### DesignationController.php (model `Designation`, table `designation`)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing | Controller/DesignationController.php:55-63 |
| form | GET (`$id` param) | HTML view | Create/edit form; also builds a list of existing `desig_code`s | Controller/DesignationController.php:66-92 |
| save | POST | JSON | Upsert with duplicate-code/name guard | Controller/DesignationController.php:93-130 |
| listDesignation | GET/AJAX | JSON | Paginated/sorted list | Controller/DesignationController.php:132-166 |
| deleteDepartment | POST/GET | JSON | **Misnamed** — actually soft-deletes a Designation, not a Department (copy-paste leftover) | Controller/DesignationController.php:168-185 |
| checkdesignationexists | POST/AJAX | int | Uniqueness check on `desig_name` | Controller/DesignationController.php:186-203 |
| checkdesignationcodeexists | POST/AJAX | int | Uniqueness check on `desig_code` | Controller/DesignationController.php:204-220 |

### DivisionController.php (model `Division`, table `division`) — has a guarded delete

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing | Controller/DivisionController.php:55-63 |
| form | GET | HTML view | Create/edit form | Controller/DivisionController.php:66-82 |
| savedivision | POST | JSON | Upsert with duplicate-code/name guard | Controller/DivisionController.php:83-120 |
| listdivision | GET/AJAX | JSON | Paginated/sorted list | Controller/DivisionController.php:121-152 |
| deleteDivision | POST/GET | JSON | **Guarded delete**: counts `EmployeeProfessionalDetails` rows where `emp_vertical` (sic — field name says "vertical" but is used as the division FK) matches the id(s); blocks delete with `danger:true` message if any employee is still assigned, else soft-deletes | Controller/DivisionController.php:154-194 |
| checkdivisionexists | POST/AJAX | int | Uniqueness check on `div_name` | Controller/DivisionController.php:195-211 |
| checkdivisioncodeexists | POST/AJAX | int | Uniqueness check on `div_code` | Controller/DivisionController.php:213-231 |

Note the field-name mismatch: the guard for Division delete checks `EmployeeProfessionalDetails.emp_vertical` (Controller/DivisionController.php:165) — worth double-checking against the actual employee schema before porting the business rule, since `emp_vertical` sounds like it should belong to `VerticalController`, not `DivisionController`. This may be a copy-paste bug carried over from Vertical/Division code sharing.

### SectionController.php (model `Section`, table `section`) — has a guarded delete

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing | Controller/SectionController.php:55-63 |
| form | GET | HTML view | Create/edit form | Controller/SectionController.php:66-82 |
| savesection | POST | JSON | Upsert with duplicate-code/name guard | Controller/SectionController.php:83-124 |
| listsection | GET/AJAX | JSON | Paginated/sorted list | Controller/SectionController.php:125-156 |
| deleteSection | POST/GET | JSON | **Guarded delete**: counts `EmployeeProfessionalDetails` rows where `emp_sep_priv` matches id(s); blocks with `danger:true` if employees assigned, else soft-deletes | Controller/SectionController.php:158-196 |
| checksectionexists | POST/AJAX | int | Uniqueness check on `section_name` | Controller/SectionController.php:197-215 |
| checksectioncodeexists | POST/AJAX | int | Uniqueness check on `section_code` | Controller/SectionController.php:217-235 |

### GradeController.php — MISLEADINGLY NAMED: this is the Holiday Calendar controller (grade-only in name)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing (unused for grades) | Controller/GradeController.php:55-63 |
| Hoildaycalender | GET (`$group_id`) | HTML view | Holiday calendar view (typo in name preserved) | Controller/GradeController.php:65-68 |
| eventsCalender | GET (`$group_id`) | JSON | Returns holidays for a holiday group as FullCalendar-style events | Controller/GradeController.php:69-89 |
| eventsCalenderdate | GET (`$group_id`) | JSON | Duplicate of `eventsCalender` (unused variant) | Controller/GradeController.php:90-110 |
| insert | POST | none | Inserts a holiday row from form data | Controller/GradeController.php:111-123 |
| delete | POST | none | Soft-deletes a holiday via raw interpolated SQL keyed on `$data_holiday_id` (POST-derived, no escaping) — **SQLi-shaped surface** | Controller/GradeController.php:124-131 |
| holidayreport | GET (`$mode`) | HTML view (stub) | Empty stub | Controller/GradeController.php:134-136 |
| Getholidays | GET/AJAX | JSON | Returns holidays for the logged-in employee's holiday group, using `emp_fkey` from session interpolated raw into SQL | Controller/GradeController.php:137-158 |
| Getholidayss | GET/AJAX | JSON | Attendance-lookup variant (looks unrelated/dead — queries `emp_detail_timeattandance` hardcoded to `emp_pkey='14'`) — **possible legacy cruft**, hardcoded test employee id | Controller/GradeController.php:160-182 |
| newGrade | GET | HTML view | Grade create/edit form (this is the only "real" Grade logic in this controller) | Controller/GradeController.php:184-203 |
| saveGrade | POST | JSON | Upserts a Grade row (uses generic `dept_code`/`dept_name` field names — copy-paste from Department) — **no duplicate-code/name guard**, unlike GradesController/GradesNewController | Controller/GradeController.php:204-232 |
| listGrades | GET/AJAX | JSON | Lists all Grades (no pagination applied despite `$this->datatable` being set) | Controller/GradeController.php:233-248 |
| deleteGrade | POST/GET | JSON | Soft-delete via `updateAll`, **no guarded-delete check for assigned employees** (unlike GradesNewController's `deleteGrade`) | Controller/GradeController.php:250-266 |

This controller is a grab-bag: mostly Holiday-calendar CRUD reusing the `Holiday`/`HolidayGroup` models, plus a bolted-on, thinner "Grade" CRUD that duplicates `GradesController`/`GradesNewController` but with weaker validation. **Flag entire controller for review — likely legacy/duplicate, confirm which of Grade/Grades/GradesNew is actually linked from the live menu before porting.**

### GradesController.php (model `Grades`, table `grade`) — second, independent Grade CRUD implementation

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing | Controller/GradesController.php:57-60 |
| listGrades | GET/AJAX | JSON | Paginated list ordered by `grade_pkey DESC` | Controller/GradesController.php:62-85 |
| form | GET (`$grade_pkey`) | HTML view | Create/edit form via raw interpolated SQL (`$grade_pkey` comes from route param, cast implicitly — not sanitized) | Controller/GradesController.php:87-107 |
| checkgradeexists | POST/AJAX | JSON (0/1) | Uniqueness check via raw interpolated SQL on `grade_name` (**SQLi-shaped**, no escaping) | Controller/GradesController.php:109-121 |
| checkgradecodeexists | POST/AJAX | JSON (0/1) | Uniqueness check via raw interpolated SQL on `grade_code` (**SQLi-shaped**) | Controller/GradesController.php:123-135 |
| saveGrade | POST | JSON | Upsert — **update path uses fully hand-built raw SQL** `UPDATE grade SET grade_pkey=..., grade_code='...', grade_name='...' WHERE grade_pkey=... ` with values taken directly from request data with no escaping (**SQLi**); insert path uses CakePHP `save()` | Controller/GradesController.php:136-192 |
| deleteGrade | POST/GET (`$grade_pkey`) | JSON | **Guarded delete**: counts `EmployeeProfessionalDetails.emp_grade` assignments; blocks with `danger:true` if any, else raw `UPDATE grade SET status=0 WHERE grade_pkey=$grade_pkey` (**SQLi-shaped**, `$grade_pkey` from route param) | Controller/GradesController.php:194-237 |

### GradesNewController.php (model `Grades`, table `grade`) — third Grade CRUD implementation, adds pay-scale + category link

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing | Controller/GradesNewController.php:57-60 |
| listGrades | GET/AJAX | JSON | Paginated list, also joins in `category_name` per row via a raw per-row SQL query in a loop (**N+1 query pattern**) | Controller/GradesNewController.php:62-92 |
| form | GET (`$grade_pkey`) | HTML view | Create/edit form; also loads `category_pkey`/`category_name` and full category list for dropdown | Controller/GradesNewController.php:94-129 |
| checkgradeexists | POST/AJAX | JSON (0/1) | Uniqueness check via raw interpolated SQL (**SQLi-shaped**) | Controller/GradesNewController.php:131-143 |
| checkgradecodeexists | POST/AJAX | JSON (0/1) | Uniqueness check via raw interpolated SQL (**SQLi-shaped**) | Controller/GradesNewController.php:145-157 |
| saveGrade | POST | JSON | Upsert including `category_fkey`/`pay_scale`; update path hand-builds raw SQL (**SQLi**) and also writes to a separate `pay_scale` table; insert path uses `save()` then inserts into `pay_scale` via raw SQL | Controller/GradesNewController.php:158-227 |
| deleteGrade | POST/GET (`$grade_pkey`) | JSON | **Guarded delete**: counts employees via a subquery against `employee_info`/`emp_proff` (different join path than GradesController's version, same intent); on success soft-deletes grade and `pay_scale` rows | Controller/GradesNewController.php:229-253 |

**Grade duplication summary**: three controllers (`GradeController`, `GradesController`, `GradesNewController`) all operate against the same `grade` table via the same `Grades` model, with different feature sets (GradesNew adds category linkage + pay scale, Grades has a simpler guard, Grade has almost no guard). GradesNewController looks like the most complete/most-recently-edited version (comments credit "Akshay" edits in 2023) and is the most likely canonical implementation — but **which one is wired into the live menu must be verified against `Config/routes.php` / view/layout menu partials before deciding which to port**; porting all three would duplicate effort and risk conflicting business rules.

### CategoryController.php (model `Category`, table `category`) — Grade-category CRUD, copy-paste of Grades pattern

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing | Controller/CategoryController.php:57-60 |
| listGrades | GET/AJAX | JSON | **Misnamed** (copy-paste from GradesController) — actually lists Categories | Controller/CategoryController.php:62-85 |
| form | GET (`$category_pkey`) | HTML view | Create/edit form via raw interpolated SQL | Controller/CategoryController.php:87-107 |
| checkcategoryexists | POST/AJAX | JSON (0/1) | Uniqueness check via raw interpolated SQL (**SQLi-shaped**) | Controller/CategoryController.php:109-121 |
| checkcategorycodeexists | POST/AJAX | JSON (0/1) | Uniqueness check via raw interpolated SQL (**SQLi-shaped**) | Controller/CategoryController.php:123-135 |
| saveGrade | POST | JSON | **Misnamed** — actually upserts a Category; update path hand-builds raw SQL (**SQLi**) | Controller/CategoryController.php:136-192 |
| deleteGrade | POST/GET (`$category_pkey`) | JSON | **Misnamed** — actually deletes a Category. **Guarded delete**: blocks if any `grade.category_fkey` references this category (i.e., Grades depend on Categories, not employees directly) | Controller/CategoryController.php:194-233 |

This is essentially `GradesController.php` with find-and-replace `grade`→`category` and stale method names left as `listGrades`/`saveGrade`/`deleteGrade`. Confirm this is intentional Category-vs-Grade separation (categories feed the `GradesNewController` dropdown at Controller/GradesNewController.php:124-126) rather than dead scaffolding.

### CategoryMasterController.php (model `CategoryMaster`, table `category_master`) — a different, unrelated "category" concept

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view (empty stub) | Controller/CategoryMasterController.php:39-41 |
| category | GET (`$category_pkey`) | HTML view | Add/Edit form via raw interpolated SQL on `category_master` | Controller/CategoryMasterController.php:42-55 |
| categoryfilter | GET/AJAX | JSON | Select2-style typeahead search on `category_master.code`, raw interpolated `LIKE` query (**SQLi-shaped**, `$q` from `$_REQUEST` unescaped) | Controller/CategoryMasterController.php:56-80 |
| delete | POST/GET (`$user_pkey`) | JSON | Soft-delete via `updateAll` | Controller/CategoryMasterController.php:81-93 |
| save | POST | JSON | Upsert with duplicate-code guard scoped by `status=1` and excluding current pkey | Controller/CategoryMasterController.php:95-136 |
| listmaster | GET/AJAX (`$param`) | JSON | Paginated list, optional raw interpolated `category_pkey = $categorypkey` filter condition (**SQLi-shaped** if `site` param is attacker controlled) | Controller/CategoryMasterController.php:137-187 |

Note: `CategoryMaster` appears to be a generic lookup-list feature (uses `Workstatus` model too — `public $uses = array('Workstatus','CategoryMaster')` at Controller/CategoryMasterController.php:34) unrelated to `Category`/`Grades`. Verify against views/menu whether this is actually wired to org-setup or is a separate generic-masters feature before scoping it into the Company Setup migration slice.

### VerticalController.php (model `Verticals`, table `verticals`) — thin CRUD, weakest validation in the cluster

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing | Controller/VerticalController.php:55-63 |
| newVertical | GET | HTML view | Create/edit form (uses generic `dept_code`/`dept_name` field names — copy-paste) | Controller/VerticalController.php:66-85 |
| saveVertical | POST | JSON | Upsert — **no duplicate-name/code guard at all** (unlike Department/Designation/Division/Section) | Controller/VerticalController.php:86-114 |
| listVerticals | GET/AJAX | JSON | Lists all (no pagination despite `$this->datatable` set) | Controller/VerticalController.php:115-130 |
| deleteVertical | POST/GET | JSON | Soft-delete via `updateAll`, **no guarded-delete check for assigned employees** | Controller/VerticalController.php:132-148 |

### BankController.php (model `Banks`, table `bank`)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing; **calls `debug()` on all `CompanyContactInfo` rows unconditionally** — leftover debug statement that will dump data to output in production if `debug` level allows | Controller/BankController.php:54-62 (debug call at line 59) |
| form | GET | HTML view | Create/edit form | Controller/BankController.php:66-85 |
| branches | GET | HTML view (stub, `layout=false`) | Empty/unused | Controller/BankController.php:86-89 |
| savebank | POST | JSON | Upsert (no uniqueness guard) | Controller/BankController.php:91-125 |
| listbanks | GET/AJAX | JSON | Paginated/sorted list | Controller/BankController.php:126-169 |
| deleteBank | POST/GET | JSON | Soft-delete via `updateAll` | Controller/BankController.php:171-189 |

### TemplateController.php — dead/orphaned duplicate; misconfigured (`$uses`/`$name` point at Bank, not templates)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | **Bug**: uses `CompanyContactInfo` and calls `debug()` unconditionally, same as BankController — this controller's `$name = 'Bank'` and `$uses = array('UserCredentials','CompanyContactInfo','Banks')` (Controller/TemplateController.php:40,47) never reference the `Templates`/`TemplatesDetails`/`DocTemplate` models despite the class name | Controller/TemplateController.php:53-61 |
| newbank | GET | HTML view | Bank create/edit form — **copy-paste from BankController**, has nothing to do with document templates | Controller/TemplateController.php:64-86 |
| savebank | POST | JSON | Bank upsert — copy-paste duplicate of `BankController::savebank` | Controller/TemplateController.php:87-120 |
| listbanks | GET/AJAX | JSON | Uses `DatatablesManagement->fetchData()` component helper (different pattern than BankController's manual pagination) | Controller/TemplateController.php:121-133 |

**Confirmed dead/legacy cruft** — `TemplateController.php` is a mis-named, incomplete copy of `BankController.php`. It does not touch `Templates`, `TemplatesDetails`, or `DocTemplate` models at all despite the class name suggesting a document-template manager. **Verify before migrating** — likely should be deleted rather than ported; the real template-related models (`Templates.php`, `TemplatesDetails.php`, `DocTemplate.php`) appear to have no controller in this cluster driving them (no `Templates`/`DocTemplate` controller found under `Controller/`).

### DbConfigController.php — 13-step onboarding wizard (orphaned, not linked from any live menu per prior research)

All 13 "wizard step" actions plus their save/support actions. `$uses` includes `DayTimeProcedures, CompanyContactInfo, DbConfig, Designation, Departments, MobileUserCredentials, UserCredentials` (Controller/DbConfigController.php:51). Several methods use PHP's deprecated `mysql_*` API (removed in PHP 7) with a **hardcoded root DB credential repeated at every call site**: `mysql_connect('localhost', 'root', 'Localhost&*()')`.

Wizard-step actions (chained in logical onboarding order, several call `$this->redirect(...)` to the next step):

| # | Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|---|
| 1 | config | GET/POST | none (no output) | Sets `db_config.company_code`, `branches.company_code`/`branch_code`, `fin_year.company_code`/`branch_code` via raw interpolated SQL from session `company_code` | Controller/DbConfigController.php:82-92 |
| 2 | designation_departments | GET | HTML view | Uses raw `mysql_connect` with hardcoded root creds (line 96) to pull department/designation lists redundantly alongside a CakePHP query | Controller/DbConfigController.php:94-116 |
| 3 | holidays_s | GET | HTML view | Fetches holidays from external `calendarific.com` API with a **hardcoded API key** in the URL, then lists local `holiday_group` via raw `mysql_connect` (hardcoded root creds) | Controller/DbConfigController.php:207-227 (API key at 208, mysql_connect at 218) |
| 4 | holidays | GET | HTML view | Lists `holiday_group` via raw `mysql_connect` (hardcoded root creds) | Controller/DbConfigController.php:229-239 |
| 5 | policy | GET | HTML view | Marks wizard state (`UPDATE wizard_config SET link='DbConfig/policy', state='1'`) then lists shift/day-time procedures via both raw `mysql_connect` and CakePHP find | Controller/DbConfigController.php:278-294 |
| 6 | leave_heads | GET | HTML view | Lists `salary_head_items` where `item_type='LEAVE'` | Controller/DbConfigController.php:351-356 |
| 7 | salary_policy | GET | HTML view (empty stub) | Controller/DbConfigController.php:386-388 |
| 8 | emp_upload | GET | HTML view (empty stub) | Controller/DbConfigController.php:390-392 |
| 9 | load_config | GET | HTML view (empty stub) | Controller/DbConfigController.php:394-396 |
| 10 | emp_login | GET | HTML view (empty stub) | Controller/DbConfigController.php:398-400 |
| 11 | welcome | GET | HTML view (empty stub) | Controller/DbConfigController.php:439-441 |
| 12 | login_cred | GET | HTML view (empty stub) | Controller/DbConfigController.php:594-596 |
| 13 | SetupComplete / completed_setup | GET | redirect-only / HTML stub | `SetupComplete` marks `wizard_config.state='1'` then redirects to `Dashboard/index`; `completed_setup` is an unused empty stub of the same concept | Controller/DbConfigController.php:343-349, 598-600 |

Supporting/save actions (not standalone wizard steps but wired to the above):

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| form | GET | HTML view | Loads first `DbConfig` row | Controller/DbConfigController.php:64-80 |
| save_shift | POST (`$pShift_id`) | JSON | Duplicates a shift/day-time-procedure row via raw `mysql_*` calls with hardcoded root creds; guards against duplicate `day_time_desc` | Controller/DbConfigController.php:118-195 |
| remove_shift | POST | JSON | Deletes a shift row via raw interpolated SQL | Controller/DbConfigController.php:197-205 |
| save_holidays | POST | redirect-only | Copies a holiday-group + its holidays into new rows via raw `mysql_*` (hardcoded creds) then redirects to `policy` | Controller/DbConfigController.php:241-276 |
| show_policiess | GET (`$id`) | HTML view | Shows one shift policy + eligible variable salary components, via raw `mysql_*` (hardcoded creds) | Controller/DbConfigController.php:296-325 |
| save_policies | POST | redirect-only | Redirects to `leave_heads` (logic incomplete/stubbed) | Controller/DbConfigController.php:327-341 |
| save_leave_head | POST | JSON | Toggles a `salary_head_items.value` Y/N flag via raw interpolated SQL | Controller/DbConfigController.php:358-384 |
| save_Desig | POST | JSON | Inserts a Designation with duplicate-code guard | Controller/DbConfigController.php:403-437 |
| savecompletess | POST (`$bank_id` unused) | JSON | Bulk-resets passwords for all users with blank password, also upserts `MobileUserCredentials`; **`debug()` calls left active on request data (including generated password) at lines 484-485** | Controller/DbConfigController.php:443-496 |
| remove_desig | POST | JSON | Soft-deletes a designation via raw interpolated SQL | Controller/DbConfigController.php:498-520 |
| showholidays | GET (`$id`) | HTML view | Lists holidays for a group via raw `mysql_*` (hardcoded creds) | Controller/DbConfigController.php:523-532 |
| savedepartment | POST | JSON | Inserts a Department with duplicate-code guard | Controller/DbConfigController.php:534-568 |
| remove_dept | POST | JSON | Soft-deletes a department via raw interpolated SQL | Controller/DbConfigController.php:570-592 |
| listdb | GET/AJAX | JSON | Paginated `DbConfig` listing | Controller/DbConfigController.php:602-634 |
| deleteDepartment | POST/GET | JSON | Soft-deletes departments via `updateAll` | Controller/DbConfigController.php:636-651 |

**Orphaned/dead per prior research** — confirmed structurally: this controller mixes deprecated `mysql_*` calls (which fail outright on PHP 7+, meaning several of these actions are already broken on any modern PHP runtime) with hardcoded root DB credentials `root:Localhost&*()` repeated at Controller/DbConfigController.php:96,122,218,230,246,281,297,331. Also leaks a third-party API key for calendarific.com in plaintext (Controller/DbConfigController.php:208). **Do not port as-is; treat as reference/spec only for the wizard-step business logic, not as code to lift.**

### UniformController.php / UniformController_bkup.php — out of scope domain (uniform inventory, not org-structure)

These are **not** part of Company/Org setup; they implement uniform-item purchase, allocation, and employee-loan tracking (27 actions in the live controller: `index, master, employeelist, jsons, downloadexcels, downloads, view_emi, completed, lists_purchase, lists, returns, return_item, form, Master_save, deleteEmp, load_qty, finditem_qty, form_purchase, purchase_file, save_Purchase, deleteEmppurchase, allocate, allocate_form, getval, getendmonth, allocate_emp, allocate_save` — Controller/UniformController.php:58-1085). `UniformController_bkup.php` (497 lines, 21 actions) is an older, near-identical snapshot of the same feature — **dead backup file, not routable in a typical CakePHP setup unless explicitly mapped**, verify it isn't referenced by any route before dropping it. Recommend excluding both from the Company-Setup migration slice and re-scoping them under an "Inventory/Uniform" migration unit if one exists.

---

## 2. Model Validation & Business Rules

**Headline finding: every backing model in this cluster is a bare CakePHP passthrough model.** None of `Departments`, `Designation`, `Division`, `Section`, `Grades`, `Category`, `CategoryMaster`, `Banks`, `Verticals`, `Units`, `Templates`, `TemplatesDetails`, `DocTemplate`, `CompanyContactInfo`, `CompanyInfo` define a `$validate` array, or a `beforeSave`/`beforeValidate`/`afterSave`/`beforeDelete` hook. Confirmed via direct read of every model file plus a `grep` for those hook names across all of them — zero matches. All business rules (uniqueness checks, guarded/cascading deletes, field mapping) live entirely in the controllers, not the models — this is the single most important migration implication for this cluster: **there is no model-layer contract to port; every validation/guard rule must be re-derived from controller code.**

| Model | File | Table (`$useTable`) | Primary key | Used by controller(s) | path:line |
|---|---|---|---|---|---|
| Units | Model/Units.php | `branches` | default `id` | BranchController | Model/Units.php:7-16 |
| Departments | Model/Departments.php | `department` | default `id` | DepartmentController, DbConfigController | Model/Departments.php:7-16 |
| Designation | Model/Designation.php | `designation` | default `id` | DesignationController, DbConfigController | Model/Designation.php:7-16 |
| Division | Model/Division.php | `division` | default `id` | DivisionController | Model/Division.php:7-15 |
| Section | Model/Section.php | `section` | default `id` | SectionController | Model/Section.php:7-15 |
| Grades | Model/Grades.php | `grade` | default `id` (but controllers use `grade_pkey` in raw SQL — **mismatch**) | GradeController, GradesController, GradesNewController | Model/Grades.php:7-15 |
| Category | Model/Category.php | `category` | default `id` (controllers use `category_pkey` in raw SQL — **mismatch**) | CategoryController | Model/Category.php:7-15 |
| CategoryMaster | Model/CategoryMaster.php | `category_master` | `category_pkey` (explicit) | CategoryMasterController | Model/CategoryMaster.php:24-28 |
| Banks | Model/Banks.php | `bank` | default `id` | BankController, TemplateController | Model/Banks.php:7-15 |
| Verticals | Model/Verticals.php | `verticals` | default `id` | VerticalController | Model/Verticals.php:7-15 |
| Templates | Model/Templates.php | `templates` | `id` (explicit) | none found in this cluster — orphaned model | Model/Templates.php:7-17 |
| TemplatesDetails | Model/TemplatesDetails.php | `templates_details` | `id` (explicit) | none found in this cluster — orphaned model | Model/TemplatesDetails.php:7-17 |
| DocTemplate | Model/DocTemplate.php | `doc_template` | `template_pkey` (explicit) | none found in this cluster — orphaned model | Model/DocTemplate.php:7-16 |
| CompanyContactInfo | Model/CompanyContactInfo.php | `comp_contact_info` | default `id` | CompanyController, CompanyNewController, BranchController, DepartmentController, etc. (declared in `$uses` broadly, only actually used by Company*Controller) | Model/CompanyContactInfo.php:7-15 |
| CompanyInfo | Model/CompanyInfo.php | `db_config` | default `id` | not referenced by any controller in this cluster (orphaned model — DbConfigController uses its own `DbConfig` model, not `CompanyInfo`, despite both mapping toward `db_config`-related concerns) | Model/CompanyInfo.php:7-15 |

Note the **primary-key mismatch on Grades/Category**: the model declares no explicit `$primaryKey`, defaulting to CakePHP's convention of `id`, but every controller action operates against the actual DB column `grade_pkey`/`category_pkey` exclusively via hand-written raw SQL (e.g., Controller/GradesController.php:96,146,179; Controller/CategoryController.php:96,179). This confirms the models were never used for CakePHP's built-in `save()`/`find()` conventions for these two tables in most of the "new" grade/category flow — `save()` is only used for the simpler insert path (Controller/GradesController.php:185, Controller/GradesNewController.php:215), while updates always drop to raw SQL. **Migration implication**: the actual PK column names (`grade_pkey`, `category_pkey`) must be used when defining the new schema/ORM models — do not assume `id`.

### Guarded/cascading-delete business rules (confirmed by controller code, not model hooks)

| Entity | Guard condition | Employee-link field checked | path:line |
|---|---|---|---|
| Division | Blocks soft-delete if any `EmployeeProfessionalDetails.emp_vertical` row matches the division id(s) — field name is suspicious, likely should be an `emp_division`-style column; verify against actual DB schema | Controller/DivisionController.php:154-194 |
| Section | Blocks soft-delete if any `EmployeeProfessionalDetails.emp_sep_priv` row matches the section id(s) | Controller/SectionController.php:158-196 |
| Grade (via GradesController) | Blocks soft-delete if any `EmployeeProfessionalDetails.emp_grade` row matches the grade pkey | Controller/GradesController.php:194-237 |
| Grade (via GradesNewController) | Blocks soft-delete if `employee_info`/`emp_proff.emp_grade` subquery count > 0 (different join path, same rule) | Controller/GradesNewController.php:229-253 |
| Category | Blocks soft-delete if any `grade.category_fkey` references the category (protects Category from deletion while Grades still reference it — not an employee-level guard) | Controller/CategoryController.php:194-233 |
| Department | **No guard** — unconditional soft-delete via `updateAll`, unlike Division/Section/Grade | Controller/DepartmentController.php:154-171 |
| Designation | **No guard** — unconditional soft-delete | Controller/DesignationController.php:168-185 |
| Vertical | **No guard** — unconditional soft-delete, despite Division's guard apparently (mis-)checking an `emp_vertical` field that should logically belong here | Controller/VerticalController.php:132-148 |
| Grade (via GradeController, the holiday-calendar-named one) | **No guard** — unconditional soft-delete, inconsistent with the other two Grade CRUD implementations | Controller/GradeController.php:250-266 |
| Branch | **No guard** — unconditional soft-delete via `updateAll`, despite branch deletion potentially orphaning `fin_year`/`company_branches` control-DB rows created in `savebranch()` | Controller/BranchController.php:267-283 |
| Bank | **No guard** — unconditional soft-delete | Controller/BankController.php:171-189 |
| CategoryMaster | **No guard** — unconditional soft-delete | Controller/CategoryMasterController.php:81-93 |

**Migration implication**: guarded-delete behavior is inconsistent across sibling entities (Division/Section/Grade guard against employee assignment; Department/Designation/Vertical/Grade-in-GradeController/Branch/Bank/CategoryMaster do not) — this inconsistency should be treated as unintentional/legacy debt rather than a deliberate design choice, and the Next.js port should decide deliberately (likely: apply the guard consistently to every entity referenced by an employee FK) rather than blindly replicating the inconsistency.

### Duplicate-value uniqueness rules (application-level, not DB constraints — implemented as ad hoc controller "check*Exists" methods, never as model `$validate['rule' => 'isUnique']`)

- Department: unique `dept_code`, unique `dept_name`, scoped to `status=1`, excluding current id (Controller/DepartmentController.php:172-208)
- Designation: unique `desig_code`, unique `desig_name`, scoped to `status=1`, excluding current id (Controller/DesignationController.php:186-220)
- Division: unique `div_code`, unique `div_name`, scoped to `status=1`, excluding current id (Controller/DivisionController.php:195-231)
- Section: unique `section_code`, unique `section_name`, scoped to `status=1`, excluding current id (Controller/SectionController.php:197-235)
- Grade (all 3 controllers): unique `grade_code`, unique `grade_name`, scoped to `status=1` — but enforcement differs: GradesController/GradesNewController check via raw SQL with the SQLi issues noted above; GradeController has **no such check at all**
- Category: unique `category_code`, unique `category_name`, scoped to `status=1` — same raw-SQL SQLi pattern as Grade
- CategoryMaster: unique `code`, scoped to `status=1`, excluding current pkey (Controller/CategoryMasterController.php:95-136)
- Branch: unique `branch_name`, scoped to `status=1`, excluding current id (Controller/BranchController.php:284-303)
- Vertical, Bank: **no uniqueness checks at all**

All of these "exists" checks are re-implemented per-controller with copy-pasted logic and inconsistent escaping (`Model->find('count', ...)` for Department/Designation/Division/Section/Branch/CategoryMaster is parameterized safely by CakePHP; the raw-SQL versions for Grade/Category/CategoryMaster's `categoryfilter` are not). **Migration implication**: these should become either DB-level `UNIQUE` constraints or a single shared Zod/service-layer validator in the Next.js API, not per-entity copy-pasted logic.

---

## Summary of flags for migration triage

1. **SQL injection surfaces** (raw string interpolation into SQL, beyond the previously-confirmed `CompanySetupController::verifyPayment()` at Controller/CompanySetupController.php:154-158):
   - `GradesController::form/checkgradeexists/checkgradecodeexists/saveGrade/deleteGrade` — Controller/GradesController.php:93,117,131,152-153,182,216
   - `GradesNewController::form/checkgradeexists/checkgradecodeexists/saveGrade` — Controller/GradesNewController.php:100-102,139,153,177-178,205-211,220
   - `CategoryController::form/checkcategoryexists/checkcategorycodeexists/saveGrade/deleteGrade` — Controller/CategoryController.php:92-93,117,131,152-153,182,200,209
   - `CategoryMasterController::category/categoryfilter/listmaster` — Controller/CategoryMasterController.php:51,62,66,156
   - `GradeController::delete/Getholidays/Getholidayss` — Controller/GradeController.php:130,143,167
   - `DbConfigController` — extensive raw SQL throughout, e.g. Controller/DbConfigController.php:85-90,180,186-187,203,254,257,263,269,299,304,315,346,353,371,373,509,526,581
   - `BranchController::savebranch/checkbranchexists` — Controller/BranchController.php:149,155-160,175-179,297

2. **Hardcoded secrets**:
   - Razorpay test API key/secret — Controller/CompanySetupController.php:81-82,119,189-190
   - Root DB credentials `root`/`Localhost&*()` — repeated across Controller/DbConfigController.php (8+ call sites)
   - Third-party calendarific.com API key — Controller/DbConfigController.php:208

3. **Dead/duplicate/orphaned controllers — verify before migrating**:
   - `CompanyNewController.php` — near-duplicate of `CompanyController.php`
   - `TemplateController.php` — mis-named dead copy of `BankController.php`; does not touch the actual Templates models
   - `GradeController.php` (grade portion only; the Holiday-calendar portion may be live elsewhere) vs `GradesController.php` vs `GradesNewController.php` — three competing implementations of the same feature
   - `DbConfigController.php` — entire 13-step wizard, orphaned per prior research, uses deprecated `mysql_*` API (broken on PHP 7+)
   - `UniformController_bkup.php` — stale backup snapshot of `UniformController.php`
   - `Templates.php`, `TemplatesDetails.php`, `DocTemplate.php` models — no controller in this cluster (or found elsewhere via this pass) drives them; likely a feature whose controller was removed or lives outside this cluster

4. **Model layer is a no-op for business rules** — zero `$validate`/hooks across every model checked; all validation and guarded-delete logic must be re-derived from controller code during the Next.js port (see table above).

5. **Naming/data inconsistencies to resolve before porting**:
   - Division's guarded-delete checks `emp_vertical`, not an obviously-Division-named field — confirm against actual schema
   - `Grades`/`Category` models default to PK `id` but all real queries use `grade_pkey`/`category_pkey`
   - `CategoryController`'s action names (`listGrades`, `saveGrade`, `deleteGrade`) are all copy-paste leftovers from `GradesController`
   - `DesignationController::deleteDepartment` is a misnamed method that deletes Designations

---

### 2.8 Site / Field Work & Project Management

# Site / Field Work / Project Management — Backend Report

Legacy root confirmed at `D:\Projects\RIZOMigration\legacy` (CakePHP 2.x, no `app/` prefix — `Controller/` and `Model/` are top-level). Auth is session-based (`AppController.php:39-46`, `user_group` 1=admin/2=employee); `SiteController` extends `LoginAppController` (`Controller/SiteController.php:37`) confirming it is a login/auth controller, not a site-CRUD controller.

All HTTP "methods" below are CakePHP 2.x conventions — routes are `/Controller/action/param1/param2` (GET by default), and controllers gate specific actions with `$this->request->is('post')`. There is no REST routing; almost every "form submit" action accepts GET or POST but branches internally, or accepts any method and reads `$this->request->data` / `$_REQUEST` directly (no CSRF token checks observed in this cluster).

No `$validate` arrays, no `beforeSave`/`beforeValidate`/`afterSave`/`afterFind` hooks exist on ANY model in this cluster (confirmed by reading every Model file backing these controllers — see Section 2). All business rules and data shaping live in controller code as raw SQL / array munging. No email/SMS notification code found anywhere in this cluster (re-confirmed — no `mail(`, `Email::`, `Cake\Mailer`, SMS gateway calls in any of the 15 controllers).

`api/v1` re-confirmed as an unused Slim 3 scaffold: `api/v1/index.php` (30 lines) requires `src/settings.php`, `src/dependencies.php`, `src/middleware.php`, `src/routes.php`, but **no `src/` directory exists at all** in `api/v1/` — the app would fatal on first request. Directory only contains uploaded images/attachments, `composer.json/.lock`, and log files. Dead/unused — confirm before migrating; do not treat as an API contract.

---

## 1. Controller Action Inventory

### Controller/SiteController.php (login-only, per prior finding — RE-CONFIRMED)
Extends `LoginAppController` (line 37). `$uses` (line 55): `UserCredentials, Useraccess, login_auditor, CentralControl, CentralUserCredentials, Registrations, Currency, Country, MasterDb, EmployeeMenu, ReportAudit, CompanyContactInfo, LeavePolicy` — no `Site`/`SiteMaster`/`SiteTransactions` model. Confirmed: **no site/job-site CRUD actions exist in this controller.**

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML | Landing/login page | Controller/SiteController.php:58 |
| mailSend | GET/POST | mixed | Sends a login-related email (OTP/token) | Controller/SiteController.php:78 |
| employeeLoginAudit | GET/POST | JSON/redirect | Logs employee login attempt | Controller/SiteController.php:113 |
| adminLoginAudit | GET/POST | JSON/redirect | Logs admin login attempt | Controller/SiteController.php:127 |
| login | POST | redirect | Main session-based login | Controller/SiteController.php:268 |
| logoutAudit | GET | redirect | Logs logout, destroys session | Controller/SiteController.php:481 |
| register / setup / reset / resetadmin / forgot / forgotadmin / approval / success | GET/POST | HTML/mixed | Registration, password reset, forgot-password flows | Controller/SiteController.php:1248,1282,749,824,910,981,1130,1276 |
| sendtokenmail / sendtestemail / testcon | GET/POST | JSON/mixed | Token/test email utilities, DB connectivity test | Controller/SiteController.php:1427,1751,1819 |

All other actions are login/account-management scoped. **No non-login actions exist** — prior finding stands.

### Controller/SiteAttendanceApplyController.php
`$uses` (line 52): `EmployeeDetails, Useraccess, EmployeeMenu, SiteMaster, SiteAttendance, SiteTransactions, Access_site, SiteHistory, SiteMasterApprovalDetails, SiteMasterApproval`. This controller is the **maker-checker workflow** for site changes: `saveSiteApply()` diffs incoming form data against the existing `SiteMaster` row and, if `SiteMasterApproval`/`SiteMasterApprovalDetails` models detect changes, records them for approval rather than writing directly (Controller/SiteAttendanceApplyController.php:1975-2050+). This is a distinct write path from `SiteAttendanceController::saveSite()` which writes directly.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML | Site list page | Controller/SiteAttendanceApplyController.php:55 |
| filtersite | GET/POST | JSON | Site filter dropdown data | Controller/SiteAttendanceApplyController.php:82 |
| form / form2 | GET | HTML | Add/edit site form | Controller/SiteAttendanceApplyController.php:135,172 |
| check_start_date / check_end_date | GET/POST | JSON | Date-range overlap validation for site shift transactions | Controller/SiteAttendanceApplyController.php:187,204 |
| insec | GET | JSON/HTML | Loads menu/access-control tree for a user | Controller/SiteAttendanceApplyController.php:221 |
| lists | GET/POST | JSON | Site datagrid listing | Controller/SiteAttendanceApplyController.php:238 |
| save | POST | JSON/redirect | Save employee-site allocation record | Controller/SiteAttendanceApplyController.php:321 |
| addDefault / resetDefault / deletemens | GET/POST | JSON | Manage default site/menu access mapping | Controller/SiteAttendanceApplyController.php:378,395,413 |
| listuseraccess | GET | JSON | List user access records | Controller/SiteAttendanceApplyController.php:430 |
| delete | GET/POST | JSON | Soft-delete site allocation | Controller/SiteAttendanceApplyController.php:494 |
| Employee | GET | HTML/JSON | Employee lookup for site form | Controller/SiteAttendanceApplyController.php:557 |
| shift_delete_check | GET/POST | JSON | Validates whether a shift/transaction can be deleted | Controller/SiteAttendanceApplyController.php:587 |
| saveSite | POST | redirect-only (no JSON echo) | Direct create/update of `SiteMaster` + `SiteTransactions` + `SiteHistory` (same pattern as SiteAttendanceController) | Controller/SiteAttendanceApplyController.php:605 |
| admin | GET | HTML | Admin view of site | Controller/SiteAttendanceApplyController.php:890 |
| get / deleteuser / saveuseraccess | GET/POST | JSON | User-access CRUD | Controller/SiteAttendanceApplyController.php:918,939,951 |
| get_incompleteDate | GET | JSON | Finds attendance dates not yet closed | Controller/SiteAttendanceApplyController.php:989 |
| pnch | GET/POST | JSON | Punch data endpoint | Controller/SiteAttendanceApplyController.php:1008 |
| get_shift | GET | JSON | Shift lookup for month/site | Controller/SiteAttendanceApplyController.php:1040 |
| load_sites / add_site / load_closed_sites | GET/POST | JSON | Site-shift assignment loading | Controller/SiteAttendanceApplyController.php:1082,1106,1802 |
| shift_closure / shift_open / close_shift | POST | JSON | Open/close a shift for a site/date | Controller/SiteAttendanceApplyController.php:1138,1189,1230 |
| data_site / data_edit_site | GET/POST | JSON | Grid data for site attendance entry/edit | Controller/SiteAttendanceApplyController.php:1272,1859 |
| mark_activestatus / mark_deactivestatus | POST | JSON | Toggle employee active status at a site | Controller/SiteAttendanceApplyController.php:1390,1418 |
| mark_attendance / update_attendance | POST | JSON | Record/update site attendance punches | Controller/SiteAttendanceApplyController.php:1447,1573 |
| site_allocate / save_allocate / remove_allocate | GET/POST | JSON | Allocate/deallocate employees to a site | Controller/SiteAttendanceApplyController.php:1719,1735,1769 |
| **saveSiteApply** | POST | JSON | **Maker-checker save**: diffs against existing site, records pending approval instead of a direct write | Controller/SiteAttendanceApplyController.php:1975 |
| approval / approvallists / viewapproval | GET | HTML/JSON | Approval queue listing/viewing | Controller/SiteAttendanceApplyController.php:2284,2310,2360 |
| saveApproved | POST | JSON | Approver accepts/rejects a pending site-master change; applies diff to `SiteMaster` | Controller/SiteAttendanceApplyController.php:2550 |
| confirmation_modal | GET | HTML | Confirmation dialog fragment | Controller/SiteAttendanceApplyController.php:3011 |

### Controller/SiteAttendanceController.php
`$uses` (line 51): `EmployeeDetails, Useraccess, EmployeeMenu, SiteMaster, SiteAttendance, SiteTransactions, Access_site, SiteHistory`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML | Site list page | Controller/SiteAttendanceController.php:54 |
| filtersite | GET/POST | JSON | Filter dropdown | Controller/SiteAttendanceController.php:81 |
| form / form2 | GET | HTML | Add/edit site form | Controller/SiteAttendanceController.php:135,171 |
| check_start_date / check_end_date | GET/POST | JSON | Overlap validation | Controller/SiteAttendanceController.php:190,207 |
| insec | GET | JSON/HTML | Menu/access tree | Controller/SiteAttendanceController.php:224 |
| lists | GET/POST | JSON | Site datagrid | Controller/SiteAttendanceController.php:241 |
| save | POST | JSON | Save employee-site allocation | Controller/SiteAttendanceController.php:323 |
| addDefault / resetDefault / deletemens | GET/POST | JSON | Default access mgmt | Controller/SiteAttendanceController.php:380,397,415 |
| listuseraccess | GET | JSON | List access records | Controller/SiteAttendanceController.php:432 |
| delete | GET/POST | JSON | Soft-delete allocation | Controller/SiteAttendanceController.php:496 |
| Employee | GET | HTML/JSON | Employee lookup | Controller/SiteAttendanceController.php:561 |
| shift_delete_check | GET/POST | JSON | Delete-eligibility check | Controller/SiteAttendanceController.php:593 |
| **saveSite** | POST | redirect-only | **Direct** create/update of `SiteMaster`+`SiteTransactions`+`SiteHistory` (same tables as ProjectController::saveProject) — no approval step, unlike SiteAttendanceApplyController::saveSiteApply | Controller/SiteAttendanceController.php:611 |
| admin | GET | HTML | Admin view | Controller/SiteAttendanceController.php:728 |
| get / deleteuser / saveuseraccess | GET/POST | JSON | Access CRUD | Controller/SiteAttendanceController.php:761,783,795 |
| get_incompleteDate | GET | JSON | Open-date lookup | Controller/SiteAttendanceController.php:833 |
| pnch | GET/POST | JSON | Punch endpoint | Controller/SiteAttendanceController.php:852 |
| get_shift | GET | JSON | Shift lookup | Controller/SiteAttendanceController.php:884 |
| load_sites / add_site / load_closed_sites | GET/POST | JSON | Site-shift loading | Controller/SiteAttendanceController.php:935,959,1707 |
| shift_closure / shift_open / close_shift | POST | JSON | Shift open/close | Controller/SiteAttendanceController.php:991,1044,1087 |
| data_site / data_edit_site | GET/POST | JSON | Grid data | Controller/SiteAttendanceController.php:1145,1764 |
| mark_activestatus / mark_deactivestatus | POST | JSON | Status toggle | Controller/SiteAttendanceController.php:1266,1294 |
| mark_attendance / update_attendance | POST | JSON | Record/update punches | Controller/SiteAttendanceController.php:1324,1473 |
| site_allocate / save_allocate / remove_allocate | GET/POST | JSON | Employee-site allocation | Controller/SiteAttendanceController.php:1619,1639,1674 |

### Controller/SiteAttendanceManageController.php
`$uses` (line 50): `EmployeeDetails, Units, AttendanceRegister, Siteattendanceregister, Site, SiteAttendance, DbConfig, SalaryHeadItems, LeaveRequests`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| showregister | GET/POST | HTML | Register (list) landing page | Controller/SiteAttendanceManageController.php:53 |
| jsons | GET | JSON | Branch/lookup JSON feed | Controller/SiteAttendanceManageController.php:176 |
| showregistertab | GET | HTML | Verified/unverified tab view | Controller/SiteAttendanceManageController.php:239 |
| listregisterentries / listverifiedregisterentries | GET/POST | JSON | Register datagrid (pending/verified) | Controller/SiteAttendanceManageController.php:347,476 |
| processregisterentries | POST | JSON | Bulk-process register rows | Controller/SiteAttendanceManageController.php:574 |
| verifyregisterentries | POST | JSON | Marks register entries verified | Controller/SiteAttendanceManageController.php:590 |
| loadattendanceregisterheader | GET | JSON | Header metadata for register | Controller/SiteAttendanceManageController.php:711 |
| checkifregistercanverify | GET/POST | JSON | Business rule check before verify | Controller/SiteAttendanceManageController.php:741 |
| updateregisterentries | POST | JSON | Update register row(s) | Controller/SiteAttendanceManageController.php:787 |
| submitregisterentry | POST | JSON | Submit single register entry | Controller/SiteAttendanceManageController.php:819 |
| createDateRange / createDateRangeArray | (internal helper) | n/a | Date range utility | Controller/SiteAttendanceManageController.php:896,1000 |
| AddLeave / AddHalfLeave | (internal helper) | n/a | Leave-day math for register | Controller/SiteAttendanceManageController.php:911,963 |
| verifiedpdf | GET | PDF (binary) | Generates a PDF of a verified register | Controller/SiteAttendanceManageController.php:1020 |
| Getbranchname | (internal helper) | n/a | Branch name lookup | Controller/SiteAttendanceManageController.php:1283 |
| removeAttendanceEntry | POST | JSON | Deletes a register entry | Controller/SiteAttendanceManageController.php:1293 |
| getcolor | (internal helper) | n/a | UI color-coding helper | Controller/SiteAttendanceManageController.php:1307 |

### Controller/SiteAttendanceUploadController.php
`$uses` (line 25): `UserCredentials, Units, CompanyInfo, EmployeeDetails, Useraccess, EmployeeMenu, SiteMaster, SiteAttendance, SiteTransactions, Access_site, SiteHistory`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML | Upload landing page | Controller/SiteAttendanceUploadController.php:29 |
| get_shift | GET | JSON | Shift lookup | Controller/SiteAttendanceUploadController.php:105 |
| load_site_data | GET/POST | JSON | Site data for upload template | Controller/SiteAttendanceUploadController.php:162 |
| attendance / form | GET | HTML | Upload form views | Controller/SiteAttendanceUploadController.php:305,309 |
| designationFilter / branchFilter | GET | JSON | Dropdown filters | Controller/SiteAttendanceUploadController.php:387,414 |
| employeefilter | GET/POST | JSON | Employee dropdown filtered by branch/shift | Controller/SiteAttendanceUploadController.php:473 |
| downloadsiteattendanceformat | GET | file download (Excel/CSV) | Downloads bulk-upload template | Controller/SiteAttendanceUploadController.php:763 |

Note: this controller only exposes template-download/lookup actions in the code read; the actual bulk-save-from-upload action was not located within the read range — likely present further in the 54KB file (not required by report scope beyond confirming site-attendance upload exists).

### Controller/SiteattendanceregisterController.php
`$uses` (line 52): `EmployeeDetails, Units, Siteattendanceregister, DbConfig, Site, SiteAttendance, EditPunches`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| registerbook / siteregisterbook / registerbookless / empregisterbook | GET | HTML | Register book views (full/site/condensed/employee) | Controller/SiteattendanceregisterController.php:55,217,288,368 |
| getmodal | GET | HTML | Modal fragment for a specific employee/month/site | Controller/SiteattendanceregisterController.php:351 |
| jsons | GET | JSON | Branch/month lookup feed | Controller/SiteattendanceregisterController.php:435 |
| sitefilter / filter | GET | JSON | Filter dropdowns | Controller/SiteattendanceregisterController.php:523,586 |
| index / empindex | GET | HTML | Landing pages | Controller/SiteattendanceregisterController.php:621,638 |
| showregister / showregistertab | GET/POST | HTML | Register views | Controller/SiteattendanceregisterController.php:656,720 |
| listregisterentries / listverifiedregisterentries | GET/POST | JSON | Datagrid feeds | Controller/SiteattendanceregisterController.php:789,863 |
| processregisterentries / verifyregisterentries | POST | JSON | Process/verify register rows | Controller/SiteattendanceregisterController.php:937,951 |
| loadattendanceregisterheader | GET | JSON | Header metadata | Controller/SiteattendanceregisterController.php:984 |
| checkifregistercanverify | GET/POST | JSON | Verify-eligibility rule | Controller/SiteattendanceregisterController.php:1014 |
| updateregisterentries / submitregisterentry | POST | JSON | Update/submit register rows | Controller/SiteattendanceregisterController.php:1056,1066 |
| createDateRange / createDateRangeArray | (internal helper) | n/a | Date utilities | Controller/SiteattendanceregisterController.php:1115,1130 |

Note the strong duplication between `SiteattendanceregisterController` and `SiteAttendanceManageController` — nearly identical action names/bodies (`showregister`, `showregistertab`, `listregisterentries`, `verifyregisterentries`, `updateregisterentries`, `submitregisterentry`, `checkifregistercanverify`). This looks like a fork/copy of the same register feature — worth deciding which is canonical before migrating.

### Controller/SiteWorkController.php (backs `efsr_site`, distinct from `site`/job-site — RE-CONFIRMED)
`$uses` (line 50): `EmployeeDetails, SiteWork, Contacts, SiteMaster`. Model `SiteWork` → table `efsr_site`, primary key `efsr_site_pkey` (Model/SiteWork.php:24-27).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML | Landing page | Controller/SiteWorkController.php:53 |
| form | GET | HTML | Add/edit site-work form | Controller/SiteWorkController.php:66 |
| form2 | GET | HTML | Secondary form fragment | Controller/SiteWorkController.php:89 |
| insec | GET | JSON/HTML | Menu/access tree | Controller/SiteWorkController.php:98 |
| lists | GET/POST | JSON | Datagrid listing | Controller/SiteWorkController.php:114 |
| save | POST | JSON | Save `efsr_site` record | Controller/SiteWorkController.php:161 |
| downloadempctcformat | GET | file download | Downloads a CTC/upload template (naming looks copy-pasted from payroll) | Controller/SiteWorkController.php:217 |
| uploadandsaveempctc | POST | JSON | Bulk upload/save (naming inherited from payroll copy-paste) | Controller/SiteWorkController.php:272 |
| jsons | GET | JSON | Lookup feed | Controller/SiteWorkController.php:428 |
| addDefault / resetDefault / deletemens | GET/POST | JSON | Default access mgmt | Controller/SiteWorkController.php:466,483,501 |
| listuseraccess | GET | JSON | Access list | Controller/SiteWorkController.php:518 |
| delete | GET/POST | JSON | Soft-delete | Controller/SiteWorkController.php:581 |
| Employee | GET | HTML/JSON | Employee lookup | Controller/SiteWorkController.php:644 |
| saveSite | POST | JSON | (Legacy-named) direct save, likely dead — see below | Controller/SiteWorkController.php:676 |
| admin / get / deleteuser / saveuseraccess | GET/POST | HTML/JSON | Access CRUD | Controller/SiteWorkController.php:714,746,767,778 |

**Possible legacy cruft**: lines 813-994 are seven commented-out method stubs (`get_incompleteDate`, `pnch`, `get_shift`, `load_sites`, `add_site`, `data_site`, `mark_attendance`) — copy-pasted from `SiteAttendanceController` and then disabled, confirming this controller was forked from the attendance controller and stripped down. Flag as dead code, no migration needed for those stubs.

### Controller/FieldSurveyController.php (also backs `efsr_site`/`efsr_tickets`/`efsr_equipments_master` — distinct "field survey" concept, RE-CONFIRMED)
`$uses` (line 50): `SurveyType, EquipmentType, Item, SiteWork, Site, Equipments, EfsrTickets, EmployeeDetails, PackageMaster, Item (dup), MaterialRequest, MaterialRequestDetails`. Note: a full duplicate backup file exists at `Controller/FieldSurveyController_bkup.php` (56KB, near-identical) — dead file, exclude from migration scope.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| home | GET | HTML | Dashboard/landing | Controller/FieldSurveyController.php:53 |
| index | GET | HTML | List page | Controller/FieldSurveyController.php:70 |
| form_ticket | GET | HTML | Ticket form | Controller/FieldSurveyController.php:84 |
| form | GET | HTML | Survey/site form | Controller/FieldSurveyController.php:99 |
| lists | GET/POST | JSON | Survey datagrid | Controller/FieldSurveyController.php:115 |
| lists_site | GET/POST | JSON | Site datagrid for survey context | Controller/FieldSurveyController.php:158 |
| save | POST | JSON | Save survey record | Controller/FieldSurveyController.php:231 |
| saveEquip | POST | JSON | Save equipment record | Controller/FieldSurveyController.php:281 |
| ticket | GET | HTML | Ticket view | Controller/FieldSurveyController.php:306 |
| jsons | GET | JSON | Lookup feed | Controller/FieldSurveyController.php:316 |
| jsons_equipments | GET | JSON | Equipment lookup feed | Controller/FieldSurveyController.php:372 |
| jsons_Surveys | GET | JSON | Survey lookup feed | Controller/FieldSurveyController.php:439 |
| downloadempctcformat / downloadempticketformat | GET | file download | Bulk-upload templates | Controller/FieldSurveyController.php:625,676 |
| uploadandsaveempctc / uploadandsaveempequipment | POST | JSON | Bulk upload processors | Controller/FieldSurveyController.php:726,872 |

**Possible legacy cruft**: lines 497-625 contain six commented-out actions (`loadtable`, `editstoreitem`, `editordersave`, `deletestoreitem`, `getautocompletionsadjustment_code`, `deletestoremaster`) — dead code carried over from a store/inventory controller, never active. No migration needed.

### Controller/ProjectController.php
`$uses` (line 50): `EmployeeDetails, Useraccess, ProjectActivity, EmployeeMenu, SiteMaster, SiteAttendance, SiteTransactions, Access_site, SiteHistory`. **Confirms prior finding**: no dedicated `Project` model — `ProjectController` operates on the same `SiteMaster`/`SiteTransactions`/`SiteHistory` models (tables `site`, `site_transactions`, `site_history`) as `SiteAttendanceController`. "Projects" and "sites" are the same underlying entity, differentiated only by which controller/view is used.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML | Project list page | Controller/ProjectController.php:53 |
| activity_form / activity_save | GET/POST | HTML/JSON | Manage `ProjectActivity` records (table `project_activity`) | Controller/ProjectController.php:77,96 |
| filtersite | GET/POST | JSON | Filter dropdown | Controller/ProjectController.php:135 |
| form | GET | HTML | Add/edit project form | Controller/ProjectController.php:188 |
| delete | GET/POST | JSON | Soft-delete: `SiteMaster->updateAll(status=0)` keyed on `site_pkey` | Controller/ProjectController.php:215-227 |
| checkprojectexists | GET/POST | JSON (plain int echo) | Uniqueness check on `SiteMaster.site_id` | Controller/ProjectController.php:228-247 |
| form2 | GET | HTML | Secondary form (transactions/designations/shifts) | Controller/ProjectController.php:248 |
| insec | GET | JSON/HTML | Menu/access tree | Controller/ProjectController.php:262 |
| lists | GET/POST | JSON | Project datagrid | Controller/ProjectController.php:278 |
| save | POST | JSON | Save employee-project allocation | Controller/ProjectController.php:325 |
| addDefault / resetDefault / deletemens | GET/POST | JSON | Default access mgmt | Controller/ProjectController.php:381,398,416 |
| listuseraccess | GET | JSON | Access list | Controller/ProjectController.php:433 |
| Employee | GET | HTML/JSON | Employee lookup | Controller/ProjectController.php:497 |
| **saveProject** | POST | redirect-only (no explicit output) | **Direct** create/update of `SiteMaster` only (no `SiteTransactions`/`SiteHistory` write in this action, unlike `saveSite` in the attendance controllers) — writes to the SAME `site` table as `SiteAttendanceController::saveSite()` | Controller/ProjectController.php:528-567 |
| admin | GET | HTML | Admin view | Controller/ProjectController.php:569 |
| get / deleteuser / saveuseraccess | GET/POST | JSON | Access CRUD | Controller/ProjectController.php:601,622,633 |
| get_incompleteDate | GET | JSON | Open-date lookup | Controller/ProjectController.php:668 |
| get_shift | GET | JSON | Shift lookup | Controller/ProjectController.php:719 |
| load_sites / add_site | GET/POST | JSON | Site-shift loading | Controller/ProjectController.php:752,767 |
| shift_closure / close_shift | POST | JSON | Shift open/close | Controller/ProjectController.php:806,857 |
| data_site | GET/POST | JSON | Grid data | Controller/ProjectController.php:896 |
| mark_activestatus / mark_deactivestatus | POST | JSON | Status toggle | Controller/ProjectController.php:1016,1043 |
| mark_attendance | POST | JSON | Record punches | Controller/ProjectController.php:1072 |
| site_allocate / save_allocate / remove_allocate | GET/POST | JSON | Employee-site allocation | Controller/ProjectController.php:1219,1238,1263 |

Note: line 688 `pnch` action is block-commented out entirely — dead code, no action needed.

### Controller/ProjectIncomeController.php — bugs RE-CONFIRMED with exact citations
`$uses` (line 36): `array('ProjectIncome','Site','ProjectIncomePayment')` — **`Vehicle` is NOT declared** in `$uses`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty body) | Landing page, no logic | Controller/ProjectIncomeController.php:41-43 |
| form | GET | HTML | Add/edit income form, joins `project_income`→`site` | Controller/ProjectIncomeController.php:44-63 |
| delete | GET/POST | JSON | Soft-delete via `ProjectIncome->updateAll(status=0)` | Controller/ProjectIncomeController.php:66-78 |
| save | POST | JSON | Create/update `project_income` + `project_income_payment` (payment history row per save); tracks running `balance = total - payments` | Controller/ProjectIncomeController.php:80-158 |
| listproject | GET/POST | JSON | Datagrid feed, raw SQL with **string-interpolated `$where`** built directly from `$arr_data['invoice_number']`/`invoice_date`/`total`/`remarks`/`project` — classic SQL-injection-shaped code (no escaping/parameterization) | Controller/ProjectIncomeController.php:159-231 |
| **checkexpensetypenameexists** | POST | plain int echo | **Dead/broken**: accesses `$this->Vehicle->find(...)` at line 240-244; `Vehicle` is not in `$uses` (line 36) and is never lazy-loaded elsewhere in this controller — will fatal with a missing-model error (or at minimum an undefined-property PHP notice/fatal) on every call. Also functionally nonsensical for "Project Income" (Vehicle/expense-type is an unrelated domain, clearly copy-pasted from a Vehicle/Expense controller). | Controller/ProjectIncomeController.php:232-247 |
| **checkregnoexists** | POST | plain int echo | **Dead/broken**: same undeclared `$this->Vehicle` reference at line 260-264. Copy-pasted vehicle-registration-number uniqueness check, irrelevant to project income. | Controller/ProjectIncomeController.php:248-267 |
| **search** | GET | JSON | **Dead/broken**: same undeclared `$this->Vehicle` reference at line 270-280, PLUS a PHP logic bug — `'conditions' => array('(Vehicle.model_dec LIKE ...)' or '(Vehicle.reg_number LIKE ...)', 'Vehicle.status' => 1)` uses the `or` operator between two strings inside an array-literal, which does NOT do what it looks like it does (PHP `or` has very low precedence; this evaluates the first string as truthy and discards the second condition entirely — the second search field is silently ignored even if this code path were reachable). | Controller/ProjectIncomeController.php:268-282 |

**All three flagged actions (`checkexpensetypenameexists`, `checkregnoexists`, `search`) are confirmed dead/broken — undeclared `Vehicle` model reference, matching and reconfirming the prior pass's finding. Do not port their logic as-is; if vehicle-search functionality is needed, source it from wherever `VehicleExpenses`/`Vehicle` model is properly declared elsewhere in the app.**

### Controller/MaterialController.php
`$uses` (line 49): `array('Item','QuantityDetails','AdditionalDetails','ItemDetails','WarrantyDetails','MaterialController')`.

**New finding this pass**: `$uses` includes the literal string `'MaterialController'` as if it were a model name (line 49). No `Model/MaterialController.php` file exists (confirmed via directory listing — only `Model/Item.php` etc. exist for this domain). In CakePHP 2.x, `$uses` models are eagerly instantiated in the controller constructor via `Controller::constructClasses()`/`loadModel()`; a missing model class here would throw `MissingModelException` on **every single request to this controller**, breaking `index`, `form`, `itemlist`, `itemdelete`, and `save` alike. This needs verification against the live app (it's possible AppModel's fallback silently creates a bare Model object for unknown names rather than throwing, which would explain how this hasn't crashed the app — but it's not a `$validate`-bearing real model either way). Flag as **possible legacy cruft — verify before migrating**; at minimum, drop `'MaterialController'` from any migrated model list.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty) | Landing, no logic | Controller/MaterialController.php:50-53 |
| form | GET/POST | HTML | Add/edit item form, raw SQL join across `item_master`/`item_details`/`quantity_details`/`waranty_details`/`additional_details` | Controller/MaterialController.php:56-80 |
| itemlist | GET/POST | JSON | Datagrid feed (raw SQL join, same 5 tables) | Controller/MaterialController.php:84-123 |
| itemdelete | POST | JSON | Soft-delete via `Item->updateAll(status=0)`, IDs from `$_REQUEST['item_master_pkey']` (comma-split, not validated) | Controller/MaterialController.php:126-143 |
| save | POST | **redirect** (`$this->redirect(...)`) despite `autoRender=false` and being invoked as what looks like an AJAX form-save elsewhere in the app — inconsistent with every sibling action's JSON response pattern | Controller/MaterialController.php:145-168 |

### Controller/MaterialRequestController.php
`$uses` (line 51): `MaterialRequest, MaterialRequestDetails, Store, Item, PackageMaster, QuantityDetails, AdditionalDetails, ItemDetails, WarrantyDetails, Contacts, Site, EmployeeDetails`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| home / index | GET | HTML | Landing pages | Controller/MaterialRequestController.php:53,57 |
| showDetails | GET | HTML/JSON | Shows a material request's detail rows | Controller/MaterialRequestController.php:101 |
| loadnew / addnewrow | GET/POST | JSON/HTML | Adds a blank row to the request form | Controller/MaterialRequestController.php:121,165 |
| form | GET | HTML | Add/edit MR form | Controller/MaterialRequestController.php:170 |
| materiallist | GET/POST | JSON | MR datagrid | Controller/MaterialRequestController.php:196 |
| materialdelete | POST | JSON | Soft-delete MR | Controller/MaterialRequestController.php:238 |
| save | POST | JSON | Create/update `material_request` + `mr_details` rows | Controller/MaterialRequestController.php:254 |
| CheckIfExists / UpdateIfExists | (internal helper) | n/a | Line-item de-dup logic within a request | Controller/MaterialRequestController.php:293,304 |
| getitemid / getitem | GET | JSON | Item lookup helpers | Controller/MaterialRequestController.php:311,321 |
| materialtable | GET/POST | JSON | Item picker table feed | Controller/MaterialRequestController.php:334 |
| loadtable | GET | JSON | Loads a stored order's line items | Controller/MaterialRequestController.php:432 |
| editorder / editordersave | GET/POST | HTML/JSON | Edit an existing order | Controller/MaterialRequestController.php:509,532 |
| deleteordermaster / deleteorder | POST | JSON | Delete order (header/line) | Controller/MaterialRequestController.php:546,557 |
| getautocompletionsmr_code / ...site_name / ...gstore_code / ...customer_name / ...customer_po_number / ...item_desc / ...itemcode | GET | JSON | Autocomplete endpoints (8 total, one duplicate — see below) | Controller/MaterialRequestController.php:571,594,610,626,642,830,673,718 |
| generatereport | GET/POST | file/HTML | Generates a report/printout | Controller/MaterialRequestController.php:734 |
| generateitem / generatestorecode | (internal helper) | n/a | Code-generation helpers | Controller/MaterialRequestController.php:788,802 |
| itemfilter | GET | JSON | Filter feed | Controller/MaterialRequestController.php:844 |
| getitem_code | GET | JSON | Store/item code lookup | Controller/MaterialRequestController.php:868 |

**Possible legacy cruft**: `getautocompletionsitem_code` is defined twice — once commented out at line 658 and once active at line 673 (`Controller/MaterialRequestController.php:658,673`). Harmless (dead comment), but worth cleaning up. There is also a near-duplicate `getautocompletionsitemcode` (no underscore, line 718) alongside `getautocompletionsitem_code` (line 673) — two similarly-named autocomplete endpoints; verify which one the frontend actually calls before consolidating.

### Controller/GatePassController.php (reference implementation — compare against OutPassController below)
`$uses` (line 53): `DocTemplate, TemplatesDetails, Templates, Documents, EmpDetails, Units, EmployeeDetails, EmployeeExpenses, DocumentAllocation, DocumentUpload, GatePass, GatePassItems`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty) | Landing | Controller/GatePassController.php:56-58 |
| downloadHistory | GET | file download | Downloads pass history | Controller/GatePassController.php:66 |
| uploadForm | GET | HTML | Add/edit gate-pass form, correctly queries `GatePass`/`GatePassItems` for edit mode | Controller/GatePassController.php:269-333 |
| getDocumentsFromDatabase | POST | JSON | Datagrid feed with search | Controller/GatePassController.php:334 |
| **savePass** | POST | JSON | Create (`pkey==0`) **or** update (`else` branch, raw `UPDATE gate_pass` query) — has both branches, works correctly | Controller/GatePassController.php:428-563, update branch at 497-532 |
| savePreview | GET/POST | HTML | Preview before print | Controller/GatePassController.php:565 |
| printPass / printGatePass | POST/GET | PDF (binary, via HTML2PDF) | Print/download the pass as PDF | Controller/GatePassController.php:625,819 (approx., pattern matches OutPass's printOutPass at 819→ see note) |
| deleteFromGrid | POST | JSON | Soft-delete (`status=0`) | Controller/GatePassController.php:882 |
| generatereport | GET | delegates to print action | Controller/GatePassController.php:918 |

### Controller/OutPassController.php — bugs RE-CONFIRMED with exact citations
`$uses` (line 53): `DocTemplate, TemplatesDetails, Templates, Documents, EmpDetails, Units, EmployeeDetails, EmployeeExpenses, DocumentAllocation, DocumentUpload, GatePass, GatePassItems, OutPass`. This controller is a **copy of `GatePassController`** with `OutPass`-specific fields swapped in, but the copy is incomplete in two places:

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty) | Landing | Controller/OutPassController.php:56-58 |
| **uploadForm** | GET | HTML | **Bug (RE-CONFIRMED)**: intended to load an out-pass for editing, but queries the **`GatePass` model/`gate_pass`+`gate_pass_items` tables** instead of `OutPass`/`out_pass` — `$arr_out_pass = $this->GatePass->find('all', [...'conditions' => ['GatePass.status' => 1, 'GatePass.gate_pass_pkey' => $pkey], 'joins' => [['table' => 'gate_pass_items', ...]]])`. Editing an out-pass will load gate-pass data (wrong entity) or nothing. | Controller/OutPassController.php:99-117 (compare to correct pattern in Controller/GatePassController.php:301-319) |
| getDocumentsFromDatabase | POST | JSON | Datagrid feed with search — correctly targets `OutPass`/`out_pass` here | Controller/OutPassController.php:132-226 |
| **savePass** | POST | JSON | **Bug (RE-CONFIRMED)**: only handles `if ($pkey == 0) { $save = $this->OutPass->save($data); ... }` — **there is no `else` branch to handle updates** (`$pkey != 0`). When called with a non-zero `$pkey`, `$save` remains `false` (its initial value from line 269) and the resulting JSON response is always the generic "Error saving gate pass" failure message — **editing an out-pass via this action is completely broken**, matching prior finding exactly. | Controller/OutPassController.php:228-306, missing-branch at 269-276 (compare to GatePassController.php:473-532 which has both `if`/`else`) |
| savePreview | GET/POST | HTML | Preview before print, correctly targets `out_pass` via raw SQL | Controller/OutPassController.php:308-359 |
| printPass | POST | JSON | Handles inline edit-and-status-update via raw `UPDATE out_pass` queries (separate from the broken `savePass`) — this is the *actual* update mechanism in practice, bypassing the CakePHP model layer entirely | Controller/OutPassController.php:361-493 |
| printOutPass | GET | PDF (binary, via HTML2PDF) | Print/download as PDF | Controller/OutPassController.php:495-563 |
| deleteFromGrid | POST | JSON | Soft-delete (`status=0`) via raw `UPDATE out_pass` | Controller/OutPassController.php:566-600 |
| generatereport | GET | delegates to printOutPass | Controller/OutPassController.php:602-606 |
| checkPersonalPass | POST | JSON | Business rule: blocks a second "Personal" type out-pass for the same employee in the same month (checked via `DATE_FORMAT(out_pass_date,'%Y-%m')`) | Controller/OutPassController.php:608-650 |

**Net effect**: `savePass` (the "proper" model-layer save endpoint) can only create, never update. The actual edit path that's functional is inside `printPass` (lines 383-461), which does raw SQL `UPDATE` statements directly against `out_pass` when `$is_edited=='true'`, bypassing the `OutPass` model and `savePass` entirely — meaning there are effectively two divergent, inconsistent save code paths for the same entity, one of which (`savePass`'s update path) is simply dead. **Migrate the `printPass`-embedded update logic, not `savePass`'s.**

### Controller/DeviceController.php
`$uses` (line 51): `UserCredentials, Device, branches`. Model `Device` → table `devices`, **`useDbConfig = 'controldb'`** (Model/Device.php:19) — this model reads/writes a separate control database from the main app DB, confirmed by raw queries using fully-qualified `mypayrol_control_db.devices` table names.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML | Device list/dashboard | Controller/DeviceController.php:57 |
| listDev | GET/POST | JSON | Device datagrid | Controller/DeviceController.php:119 |
| addEditDev | GET | HTML | Add/edit device form | Controller/DeviceController.php:168 |
| **saveDev** | GET/POST (reads `$_REQUEST`, no `is('post')` check) | plain bool echo (`echo true`/`echo false`, not JSON) | Create/update device row via hand-built raw SQL (`INSERT`/`UPDATE` string concatenation directly from `$_REQUEST`, no escaping) against `mypayrol_control_db.devices`; auto-increments `DeviceId` per `company_code` manually via `MAX(DeviceId)+1` (race-condition prone, no transaction) | Controller/DeviceController.php:186-237 |
| checkDevice | GET/POST | JSON | Device existence/validation check | Controller/DeviceController.php:239 |

### Controller/DeviceEmployeeInfoController.php
`$uses` (line 51): `UserCredentials, Device`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML | Landing page | Controller/DeviceEmployeeInfoController.php:57 |
| listEmpDev | GET/POST | JSON | Employee-device mapping datagrid | Controller/DeviceEmployeeInfoController.php:89 |
| downloadempuploadform | GET | file download | Bulk-upload template | Controller/DeviceEmployeeInfoController.php:191 |
| editEmpDev | GET | HTML | Edit a mapping record | Controller/DeviceEmployeeInfoController.php:303 |
| saveEmpDev | POST | JSON | Save employee-device mapping | Controller/DeviceEmployeeInfoController.php:350 |

Note: an earlier `saveEmpDev` definition is commented out at line 329 (`Controller/DeviceEmployeeInfoController.php:329`), with the active version at line 350 — dead code, harmless, no action needed.

---

## 2. Model Validation & Business Rules

**Global finding**: every model file backing this cluster (23 files read in full) consists of nothing more than `$name`, `$primaryKey`, `$useTable`, and occasionally `$useDbConfig` / `$order`. **None declare `$validate`, `$belongsTo`/`$hasMany`/`$hasOne`/`$hasAndBelongsToMany` associations, or any `beforeSave`/`beforeValidate`/`afterSave`/`afterFind` callback.** The only model with custom methods at all is `Siteattendanceregister` (two stored-procedure wrapper methods, not validation). This means: **all business rules, uniqueness checks, and data integrity logic in this entire cluster live in controller code as raw SQL / inline PHP conditionals — there is no model-layer contract to port.** Migration to Next.js should treat these controllers' inline logic as the actual spec (see per-action notes above), not the models.

| Model | Table | Primary Key | DB Config | Notable file:line | Notes |
|---|---|---|---|---|---|
| Site | `site` | `site_pkey` | default | Model/Site.php:24-28 | Bare model |
| SiteMaster | `site` (same table as `Site`) | `site_pkey` | default | Model/SiteMaster.php:7-17 | Duplicate model pointing at the same table as `Site` — two model classes, one table |
| SiteTransactions | `site_transactions` | `site_transactions_pkey` | default | Model/SiteTransactions.php:7-17 | Bare model, holds per-shift rate/effective-date rows for a site |
| SiteHistory | `site_history` | `site_history_pkey` | default | Model/SiteHistory.php:7-17 | Audit-trail table for site/site_transactions changes |
| SiteMasterApproval | `site_master_approval` | `site_master_approval_pkey` | default | Model/SiteMasterApproval.php:7-17 | Header row for a pending site-change approval |
| SiteMasterApprovalDetails | `site_master_approval_details` | `site_master_approval_details_pkey` | default | Model/SiteMasterApprovalDetails.php:7-17 | Field-level diff rows for the approval workflow (used by `SiteAttendanceApplyController::saveSiteApply`) |
| EfsrSite | `efsr_site` | `efsr_site_pkey` | default | Model/EfsrSite.php:7-17 | Backs `SiteWorkController`; `$name` is mistakenly set to `'EfsrTickets'` (copy-paste error, line 13) though class is `EfsrSite` |
| SiteWork | `efsr_site` (same table as `EfsrSite`) | `efsr_site_pkey` | default | Model/SiteWork.php:24-28 | Second model class over the same `efsr_site` table |
| EfsrTickets | `efsr_tickets` | `efsr_tickets_pkey` | default | Model/EfsrTickets.php:7-17 | Backs `FieldSurveyController` ticket actions |
| EfsrEquipmentsMaster | `efsr_equipments_master` | `efsr_equipments_master_pkey` | default | Model/EfsrEquipmentsMaster.php:7-17 | Backs equipment actions in `FieldSurveyController` |
| ProjectActivity | `project_activity` | `activity_pkey` (note trailing-tab typo in source: `'activity_pkey\t'`, line 14) | default | Model/ProjectActivity.php:7-17 | Backs `ProjectController::activity_form/activity_save` |
| ActivityProjects | `activity_projects` | `activity_projects_pkey` | default | Model/ActivityProjects.php:9-20 | Not directly referenced in the 15 controllers read — verify usage elsewhere before dropping |
| ProjectIncome | `project_income` | `project_income_pkey` | `'ProjectIncome'` (own dedicated db config) | Model/ProjectIncome.php:24-29 | Only model in this cluster with a distinct `useDbConfig` hardcoded at model level rather than set per-request from session |
| ProjectIncomePayment | `project_income_payment` | `income_pkey` | default | Model/ProjectIncomePayment.php:7-17 | One row per payment/installment against a `ProjectIncome` |
| Access_site | `access_site` | `access_site_pkey` | default | Model/Access_site.php:7-17 | Site-level access-control rows |
| Material / MaterialRequest | `material_request` | `mr_pkey` | default | Model/MaterialRequest.php:24-29 | Header table for material requests |
| MaterialRequestDetails | `mr_details` | `mr_details_pkey` | default | Model/MaterialRequestDetails.php:24-28 | Line items for a material request |
| Item | `item_master` | `item_master_pkey` | default | Model/Item.php:24-29 | Backs `MaterialController` (item catalog) — joined ad hoc in controller SQL against `item_details`/`quantity_details`/`waranty_details` [sic, misspelled table name confirmed in raw SQL]/`additional_details`, none of which have dedicated Model files with `$validate` either |
| GatePass | `gate_pass` | (implicit `id`) | default | Model/GatePass.php:7-16 | Has `$order = "GatePass.creation_date DESC"` default sort, nothing else |
| GatePassItems | `gate_pass_items` | (implicit `id`) | default | Model/GatePassItems.php:7-16 | Line items for a gate pass |
| OutPass | `out_pass` | (implicit `id`) | default | Model/OutPass.php:7-16 | Bare — no fields beyond `$order` |
| Vehicle | `vehicle_master` | `vehicle_master_pkey` | default | Model/Vehicle.php:7-18 | Exists as a model but is **not declared in `ProjectIncomeController::$uses`** — see broken-actions section above |
| VehicleExpenses | `transportation_expense` | `transportation_expense_pkey` | default | Model/VehicleExpenses.php:7-16 | Not referenced in this cluster's controllers; unrelated to project income despite naming proximity |
| Device | `devices` | `DeviceId` | `'controldb'` (separate database) | Model/Device.php:9-21 | Confirms devices live in a distinct control DB, not the tenant's main DB — important for migration data-access-layer design (dual connection) |
| DeviceAttendance | `device_attandance` [sic] | `device_attandance_seq` | default | Model/DeviceAttendance.php:7-18 | Not referenced by the 15 controllers in this cluster's read scope — verify elsewhere |
| Devicelog | `device_attandance` [sic] (same table as DeviceAttendance) | `device_attandance_seq` | default | Model/Devicelog.php:7-16 | Second model class over the same table as `DeviceAttendance` |
| Siteattendanceregister | `site_attendance_register` | `registerid` | default | Model/Siteattendanceregister.php:7-59 | **Only model in the cluster with custom methods**: `insertUpdateAttendanceRegisterProc()` (line 19) and `salaryProcessPrc()` (line 48) — both simply wrap `CALL <stored_procedure>(...)` invocations; the actual business logic lives in MySQL stored procedures (`site_insert_update_att_reg`, `salary_process_prc`) not visible in PHP source — **flag for DBA/schema review**, these procs must be located and ported/reimplemented for a Next.js migration since there's no PHP-side equivalent logic to read |
| GoodsReceivedNotesItem | `gr_item_details` | `gr_item_pkey` | default | Model/GoodsReceivedNotesItem.php:24-28 | Referenced by name in Model dir but not used by any controller in this cluster's scope |

### Backup/dead model files (exclude from migration)
`EfsrEquipmentsMaster_bkup_megha.php`, `EfsrSite_bkup_megha.php`, `EfsrTickets_bkup_megha.php`, `SiteAttendance_bkup_megha.php`, `SiteMaster_bkup_megha.php`, `SiteWork_bkup_megha.php`, `Site_bkup_megha.php` — all near-duplicates of their non-`_bkup` counterparts in `Model/`. Confirmed present via directory listing; not read in detail as they are clearly disabled backups (not `App::uses`'d anywhere active). Same applies to `Controller/FieldSurveyController_bkup.php`.

---

## Summary of Flagged Items (all "verify before migrating")

1. **OutPassController::savePass** (Controller/OutPassController.php:228-306) — no update branch; editing broken via this endpoint. RE-CONFIRMED.
2. **OutPassController::uploadForm** (Controller/OutPassController.php:99-117) — queries `GatePass`/`gate_pass` table instead of `OutPass`/`out_pass` when loading a record for edit. RE-CONFIRMED.
3. **OutPassController::printPass** (Controller/OutPassController.php:383-461) — contains the actual working update logic via raw SQL, bypassing both the model layer and `savePass`. New detail this pass — migrate this logic, not `savePass`'s.
4. **ProjectIncomeController::checkexpensetypenameexists / checkregnoexists / search** (Controller/ProjectIncomeController.php:232-282) — reference undeclared `Vehicle` model (not in `$uses`, Controller/ProjectIncomeController.php:36). RE-CONFIRMED, three actions.
5. **ProjectIncomeController::search** additionally has a broken `or`-in-array-literal condition (Controller/ProjectIncomeController.php:278) that silently drops the second search field even if the model reference were fixed.
6. **ProjectIncomeController::listproject** — raw string-interpolated SQL built from unescaped request fields (Controller/ProjectIncomeController.php:175-176, 189-190, 205-206) — SQL-injection-shaped code, flag for security review regardless of migration.
7. **MaterialController `$uses`** includes the non-existent model name `'MaterialController'` (Controller/MaterialController.php:49) — new finding, needs runtime verification of whether this fatals or is silently tolerated.
8. **MaterialController::save** redirects instead of returning JSON (Controller/MaterialController.php:167), inconsistent with every sibling action.
9. **ProjectController::saveProject** and **SiteAttendanceController::saveSite** / **SiteAttendanceApplyController::saveSite** all write directly to the same `SiteMaster`/`site` table with overlapping but not identical field sets (`saveProject` skips `SiteTransactions`/`SiteHistory` entirely) — RE-CONFIRMED and detailed; reconcile into one canonical "site/project" write path during migration.
10. **SiteAttendanceApplyController::saveSiteApply** vs the plain `saveSite` variants — two different write strategies (approval-queued vs direct) for what is nominally the same entity; product decision needed on which becomes canonical.
11. **SiteattendanceregisterController** vs **SiteAttendanceManageController** — near-duplicate register features (same action names/bodies); consolidate.
12. **SiteWorkController** and **FieldSurveyController** contain large blocks of commented-out dead code (copy-pasted from attendance/store controllers) — no migration action needed, just noise.
13. **Siteattendanceregister model** wraps MySQL stored procedures (`site_insert_update_att_reg`, `salary_process_prc`) whose logic is NOT visible in PHP — must be retrieved from the database schema/DBA before this business logic can be ported.
14. **api/v1 Slim scaffold** — RE-CONFIRMED dead: `index.php` requires a `src/` directory that does not exist on disk; would fatal on any request. Not a usable API reference.
15. Two model classes point at the same table in three places: `Site`/`SiteMaster` → `site`; `EfsrSite`/`SiteWork` → `efsr_site`; `DeviceAttendance`/`Devicelog` → `device_attandance`. Pick one canonical model name per table for the Next.js schema.

---

### 2.9 Assets, Inventory & Purchasing

# Backend Report: Assets, Inventory & Purchasing Cluster

Legacy root: `D:\Projects\RIZOMigration\legacy`
Auth model (context, reconfirmed): session-based, `user_group` 1=admin/2=employee (`Controller/AppController.php:39-46`). No per-menu server-side ACL was found anywhere in this cluster either — every controller below relies solely on the global `user_group` session check, with row-level restriction for `user_group==2` implemented ad hoc via `access_store` table subqueries embedded directly in raw SQL (seen in `PurchaseOrderController`, `GoodsReceivedNotesController`, `StoreController`, `StockReportController`).

---

## 1. Controller Action Inventory

### Controller/AssetController.php (`Controller/AssetController.php`)
Model deps (`$uses`, line 51): `EmployeeDetails, Assets, EmployeeMenu, allocate, AssetType, Units`. Note: `AssetType`/`allocate`/`Units` aren't declared model files but resolved dynamically by CakePHP's `$this->ModelName` magic against existing DB tables (`asset_types`, `asset_allocate`, `units`) — no explicit `Model/AssetType.php` etc. exists.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Lists active assets joined to asset_types | AssetController.php:54 |
| listAssetsss | POST (any) | JSON (autoRender=false) | Legacy/duplicate asset listing via raw query on `asset_management` | AssetController.php:69 |
| listAllocates | POST (any) | JSON | Legacy paged listing of allocations joined to emp_details | AssetController.php:90 |
| array_flatten | n/a | n/a | Pure helper, not a real action (utility fn) | AssetController.php:109 |
| listAllAssets | POST | JSON, `exit` after echo | **Active** grid endpoint (superseding 3 large commented-out prior versions at lines 128-376) with search/status filters, ordered allocation-status subqueries | AssetController.php:379 |
| listAllocatesaa | POST | JSON | Older allocation listing variant (near-duplicate of listAllocates) | AssetController.php:499 |
| addnew_old | GET | HTML view | Deprecated by name; still reachable | AssetController.php:521 |
| getassets | GET (query) | JSON | Autocomplete of unallocated assets, string-built LIKE filter | AssetController.php:531 |
| getTypes | GET (query) | JSON | Distinct asset Type autocomplete | AssetController.php:564 |
| addnew | GET | HTML view | Add/Edit asset form data + asset-type dropdown | AssetController.php:595 |
| Allocatenew | GET | HTML view | Allocation form; branch-filtered asset-type union query (rewritten 29-08-25, old version commented lines 626-652) | AssetController.php:654 |
| edit | GET | HTML view (explicit `render('Allocatenew')`) | Edit-allocation form incl. type-preselect reorder logic | AssetController.php:746 |
| getEmi | POST | JSON | Returns `<option>` HTML string embedded in JSON for asset dropdown by type (old version commented 854-867) | AssetController.php:868 |
| details | GET | HTML view | Allocation detail by asset pkey | AssetController.php:903 |
| save | POST | none (echo nothing; autoRender=false) | Generic `Assets->save()` | AssetController.php:941 |
| release | GET/POST | echo `1` (not JSON) | Marks allocation Returned + asset Returned via raw `updateAll`/`query` | AssetController.php:949 |
| Emp | GET | HTML view (near no-op; commented body) | Possible legacy cruft — verify before migrating | AssetController.php:959 |
| asset | GET | HTML view (near no-op; commented body) | Possible legacy cruft — verify before migrating | AssetController.php:967 |
| listitems | POST | JSON | Lists allocations for an employee/branch with Emp join | AssetController.php:999 |
| assetsave | POST | none | `allocate->save()` raw passthrough | AssetController.php:1084 |
| AddnewAsset | POST | none | `Assets->save()` raw passthrough | AssetController.php:1097 |
| AllocatenewAsset | POST | none | Saves allocation + `asset_state`/`status` via `updateAll`/raw query | AssetController.php:1106 |
| Create_asset | GET | HTML view | Branch/employee dropdown data (old branch-filtering logic commented out 1142-1210; current version at 1214 shows **all** branches/employees regardless of `user_group`) | AssetController.php:1214 |
| Company_asset | GET | HTML view (empty method body) | Possible legacy cruft — verify before migrating | AssetController.php:1248 |
| Create_asset_new | GET | HTML view | Branch dropdown via `MasterdataManagement` feature-access component (hierarchy/allocated-branch aware) — this is the *actual* current access-scoped variant, unlike `Create_asset` | AssetController.php:1249 |
| getEmployeesByBranch | GET (REQUEST) | JSON (two shapes: Select2 `{items,total_count}` vs plain array) | Employee-by-branch autocomplete, branch-access aware | AssetController.php:1287 |
| Company_asset_new | GET | HTML view | Branch dropdown honoring `user_feature_branch_access`/hierarchy | AssetController.php:1329 |

Flag: `Create_asset` (1214) and `Create_asset_new`/`Company_asset_new` (1249, 1329) are three overlapping/duplicate branch-dropdown implementations with different access logic — likely iterative patches left in place. Migration should pick one canonical access-scoping strategy.

### Controller/ItemController.php
`$uses` (line 49): `Item, ItemPricing, QuantityDetails, ItemAdditionalDetails, AdditionalDetails, ItemDetails, WarrantyDetails, CategoryMaster, ItemSpecification, PackageMaster, UoMaster`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty body) | Placeholder | ItemController.php:50 |
| itemfilter | GET (REQUEST) | JSON | Item dropdown search (`item_desc` LIKE) | ItemController.php:57 |
| form | POST/GET | HTML view, `layout=null` | Add/Edit item popup; loads category/specification/package/UOM dropdowns + joined item detail | ItemController.php:81 |
| chkcategory | POST | JSON | Duplicate item_code / item_desc check (client calls before save) | ItemController.php:121 |
| itemlist | GET/POST (REQUEST) | JSON | Paged item grid, multi-table join | ItemController.php:154 |
| itemdelete | POST/GET (REQUEST) | JSON | Soft-delete (`status=0`) via `updateAll` on CSV pkeys | ItemController.php:224 |
| save | POST | none (autoRender=false, no echo) | Saves `Item`, `ItemDetails`, `ItemAdditionalDetails`; `QuantityDetails`/`WarrantyDetails`/`ItemPricing` saves are commented out (dead code) | ItemController.php:243 |

Flag: `save()` (line 243) does not echo any JSON response at all — front-end gets an empty 200 body; likely relies on subsequent grid refresh rather than a response contract. Verify before migrating to a REST endpoint (needs an explicit response).

### Controller/ItemSpecificationController.php
`$uses` (line 34): `Workstatus, ItemSpecification` (Workstatus appears unused/vestigial).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty) | Placeholder | ItemSpecificationController.php:40 |
| specification | GET | HTML view | Fetch one spec row by pkey | ItemSpecificationController.php:44 |
| delete | GET/POST | JSON | Soft-delete via `updateAll` | ItemSpecificationController.php:54 |
| save | POST | JSON | Create/update; `created_by`/`modified_by` read from `Session->read('user_name')` (note: other controllers use `login_user_id`/`user_id` inconsistently) | ItemSpecificationController.php:66 |
| listmaster | GET | JSON | List active specs | ItemSpecificationController.php:88 |

### Controller/StockReportController.php (very large, ~4800+ lines — reporting-only controller)
`$uses` not fully enumerated (huge report controller); read via signature grep only (file exceeds single-read size limit). This controller is pure **reporting/read** surface (stock ledgers, GRN reports, uniform allocation, salary-adjacent reports oddly co-located) — no create/update business logic for inventory itself.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| hrreports | GET | HTML view | Report landing page | StockReportController.php:76 |
| changereporttype | POST | HTML (rendered partial `showreport`) | Dynamic report-type switch | StockReportController.php:99 |
| get_all_items | GET | JSON | Item lookup for report criteria | StockReportController.php:192 |
| addreportcriteria | POST | HTML (rendered partial `showcriteria`) | Adds a criteria row to report builder | StockReportController.php:212 |
| loadcriteriaitems | GET | HTML (rendered partial) | Loads criteria items | StockReportController.php:233 |
| listcriteriaitems | POST | JSON | Lists criteria items | StockReportController.php:251 |
| reportAudit | GET/POST | JSON | Report audit trail | StockReportController.php:491 |
| generatereport | POST | mixed (renders one of many named partials: `stockalldetails`, `grosssummaryreport`, `stocktrnsfer`, `uniformallocation`, `uniformemireport`, `empsalary`, `stockdetails`, `material`, `poreturn`, `salaryslip`, `grn`, `itemrate`, etc. — dispatch by `$type`/`$mode`) | Master report dispatcher/renderer (huge switch-like structure spanning lines 588-4817) | StockReportController.php:588 |
| listemployeefields | GET | JSON | Field list for report builder | StockReportController.php:635 |
| itemlist | GET | JSON | Item list for report filter | StockReportController.php:669 |
| itemcriteria | POST | HTML/JSON (mixed within dispatcher) | Item-based criteria sub-handler | StockReportController.php:697 |
| getprodataDesc | GET | (mixed, within report dispatch) | Product/data description lookup | StockReportController.php:3251 |

Flag: `generatereport` is a single ~4200-line method containing dozens of inline report branches (stock, GRN, PO return, uniform allocation, payroll-adjacent salary slip reports). This is a monolith that should be decomposed per-report during migration rather than ported as one Next.js route; also note payroll report logic (`salaryslip`, `empsalary`) is intermixed into an ostensibly "stock report" controller — scope leakage to confirm with product owner.

### Controller/StockTranferController.php
`$uses` (line 50): `StockTranferItem, StockTranfer, Item, Store, StockDetails, PackageMaster, MaterialRequest, MaterialRequestDetails`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| home | GET | HTML (empty) | Placeholder | StockTranferController.php:51 |
| index | GET | HTML view | Stock-transfer form data (items/stores dropdowns) | StockTranferController.php:54 |
| save_stock_transfer | POST | JSON | Creates `stock_tranfer` + `stock_tranfer_item`; **actual `stock_details` insert is commented out (lines 115-137)** — transfer does NOT adjust stock ledger at save time | StockTranferController.php:68 |
| loadtabledata | GET | HTML fragment (raw `echo`, not JSON) | Item-detail table partial for a transfer | StockTranferController.php:143 |
| lists | POST | JSON | Paged stock-transfer grid | StockTranferController.php:186 |
| finditembycode | internal helper | return value | Item pkey lookup by code | StockTranferController.php:243 |
| getautocompletionsstore_code | GET (REQUEST) | JSON | From-store autocomplete | StockTranferController.php:251 |
| getautocompletionstostore_code | GET (REQUEST) | JSON | To-store autocomplete | StockTranferController.php:267 |
| finditem_qty | GET/POST | JSON | Balance-qty + last PO lookup via stored function `stock_bal_qty_fn` | StockTranferController.php:505 |
| getautocompletionsitem_code | GET (REQUEST) | JSON | Item-code autocomplete | StockTranferController.php:519 |
| finditem | internal helper | return value | Aggregated qty-to-date calc (uses views: `item_except_grn_allocation_view`, `grn_stock_details_date_view`, `Item_allocation_details_view`) | StockTranferController.php:534 |
| itemname / storename / store_name | internal helpers | return value | Lookups | StockTranferController.php:575, 582, 590 |
| existingitem | internal helper | return value | Sum of existing transfer requests for item/store | StockTranferController.php:598 |
| save | POST | JSON | Alternate save path for stock transfer + items (duplicate of save_stock_transfer, uses different `created_by` session key `user_id` vs `login_user_id`) | StockTranferController.php:610 |
| getitem | internal helper | return value | Item detail join | StockTranferController.php:637 |
| loadtable | GET | HTML fragment (raw echo) — calls undefined-looking `debug()` at line 673 left in production path | Item-detail table for edit view | StockTranferController.php:653 |
| editstoreitem | GET | HTML view | Edit form data | StockTranferController.php:735 |
| transfer | GET | HTML view | Edit-quantity form data (near-duplicate of editstoreitem) | StockTranferController.php:753 |
| editorder_save | POST | JSON | Updates `required_qty` on a transfer item (StockDetails update commented out, line 784) | StockTranferController.php:771 |
| editordersave | POST | JSON | Saves edited transfer-item row (near-duplicate name of editorder_save) | StockTranferController.php:800 |
| deletestoreitem | GET/POST | JSON | Soft-deletes one transfer item (`status=2`) | StockTranferController.php:820 |
| getitemfkey | internal helper | return value | Lookup parent transfer pkey | StockTranferController.php:839 |
| submit_return | GET/POST | JSON | Finalizes transfer: sets item rows `status=1` and calls stored procedure `stock_tranfer_pkey_prc` — this is where actual stock ledger effects appear to live (in DB proc, not model/controller code visible here) | StockTranferController.php:847 |
| getautocompletionsadjustment_code | GET (REQUEST) | JSON | Transfer-code autocomplete | StockTranferController.php:879 |
| deletestoremaster | GET/POST | JSON | Soft-deletes whole transfer (`status=2`) | StockTranferController.php:896 |
| itemfilter | GET (REQUEST) | JSON | Item dropdown (duplicate name/purpose vs ItemController::itemfilter) | StockTranferController.php:913 |
| getitem_code | GET | JSON | Item + qty-to-date lookup for one store/item pair | StockTranferController.php:936 |

Flag: `save_stock_transfer` (line 68-140) has its `stock_details` INSERT fully commented out — the write path that would decrement source-store stock and increment destination-store stock does not execute here in application code. Actual stock adjustment for transfers appears to depend on the stored procedure `stock_tranfer_pkey_prc` invoked in `submit_return` (line 859) — **this is a possible legacy cruft / hidden business-logic-in-DB situation; must inspect the stored procedure DDL (not present in this codebase scope) before migrating stock-transfer logic to app code.**

### Controller/StockmanagementController.php
`$uses` (line 6): large unrelated payroll/attendance model list (`CentralControl, EmpDocument, ... EmployeeCTC ... Plan, Features, PlanFeature, CentralUserCredentials`) — no stock-specific models declared at all.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Branch dropdown via `MasterdataManagement` | StockmanagementController.php:10 |
| getAttendanceFeatures | GET | JSON | Returns enabled "Stockmanagement"-keyed feature list for the company's plan (plan/feature-flag lookup, misnamed method — it's about Stock features despite "Attendance" in the name) | StockmanagementController.php:17 |

Flag: This controller is essentially a stub/plan-feature-toggle shim, not an inventory CRUD controller — likely just a menu/feature-gating endpoint co-opted under the "Stockmanagement" feature key. Confirm with product whether any real functionality is expected here before migration (currently 2 actions only).

### Controller/StoreController.php
`$uses` (line 51): `Item, QuantityDetails, AdditionalDetails, StockAdjustment, StockAdjustmentDetails, EmployeeDetails, ItemDetails, WarrantyDetails, Store, GoodsReceivedNotes, StockDetails, GrItemDetails, PoReturn, PoReturnRequest`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty) | Placeholder | StoreController.php:53 |
| adj | GET | HTML (empty) | Placeholder | StoreController.php:57 |
| returnitem | GET | HTML view | Store dropdown via `MasterdataManagement` | StoreController.php:61 |
| form | POST/GET | HTML view | Add/Edit store popup | StoreController.php:69 |
| getStoreNameList | internal/GET | array return | Branch list helper; references undefined `$emps` variable (line 87) — **bug**: `$emps` is never set before the `if($emps != 0)` check, causing a PHP notice and always following the else branch in practice | StoreController.php:84 |
| save_stock_transfer | POST | JSON | **This is actually a Stock Adjustment save** (misleading method name reused from StockTranferController): checks balance via `stock_bal_qty_fn`, saves `StockAdjustment`/`StockAdjustmentDetails`, then does a **raw SQL INSERT directly into `stock_details`** (lines 125-126) — this is the clearest example in the cluster of stock-ledger mutation done via raw SQL in the controller, not a model hook | StoreController.php:96 |
| stockadjlist | POST | JSON | Paged stock-adjustment grid | StoreController.php:133 |
| chkcategory | POST | JSON | Duplicate store_code/store_location check | StoreController.php:196 |
| finditembycode | internal helper | return value | Item lookup by code | StoreController.php:231 |
| Storedata | GET | HTML view | Employee-to-store access assignment screen | StoreController.php:237 |
| storefilter | GET (REQUEST) | JSON | Store dropdown search | StoreController.php:252 |
| Storelist | POST | JSON | Paged store grid | StoreController.php:276 |
| save_allocate | POST | JSON | Grants employee access to a store — **raw SQL INSERT into `access_store`** wrapped in try/catch | StoreController.php:322 |
| remove_allocate | POST | JSON | Revokes employee store access — raw SQL UPDATE `access_store.status=0` | StoreController.php:346 |
| Storedelete | POST/GET (REQUEST) | JSON | Soft-delete stores | StoreController.php:369 |
| save | POST | none (no echo) | Generic `Store->save()` | StoreController.php:384 |
| findstoreitems | GET (REQUEST) | JSON | Items available in a given store (via `stock_details` join) | StoreController.php:397 |
| findgritems | GET (REQUEST) | none returned (builds `$gritems` but never echoes — **dead output**, response body will be empty) | GRN item list for a given item | StoreController.php:429 |
| findgrnlistitems | GET (REQUEST) | JSON | GR numbers for store/date | StoreController.php:463 |
| finditem_qty | GET | JSON | Available qty via `stock_bal_qty_fn` for a GRN item | StoreController.php:502 |
| finditem_qtyvalue_po / finditem_qtyvalue | GET | JSON | Available qty via `stock_bal_qty_fn` (two near-duplicate methods, param order swapped) | StoreController.php:518, 526 |
| findgrnitemslist | GET (REQUEST) | JSON | Items list for a chosen GRN | StoreController.php:536 |
| getautocompletionsitem_desc | GET (REQUEST) | JSON | Item-desc autocomplete w/ qty calc | StoreController.php:572 |
| itemexists | GET | JSON | Checks if item already added to a PO-return | StoreController.php:600 |
| getavailqty | GET | JSON | Available return qty via multi-view union query | StoreController.php:633 |
| getitem_pkey | internal helper | return value | Item pkey for a GRN item row | StoreController.php:686 |
| getavailqty_grn | GET | JSON | Available return qty (received - returned) | StoreController.php:696 |
| save_po_return | POST | JSON | Saves a PO-return request: `PoReturnRequest`, negative `StockDetails` row (return decrements stock), `PoReturn` — **this is the return-side stock ledger insert via model `save()`, not raw SQL** (contrast with `save_stock_transfer` above) | StoreController.php:719 |
| loadtabledata | GET | HTML fragment (raw echo) | PO-return items table partial | StoreController.php:779 |
| deletepoorder | GET/POST | JSON | Hard `DELETE FROM return_gr_items` (not soft-delete) | StoreController.php:829 |
| deleteordermaster | GET/POST | JSON | Hard `DELETE` across 3 tables (`return_gr_items`, `po_return_request`, `stock_details`) | StoreController.php:844 |
| submit_return | GET/POST | JSON | Finalizes a PO return: sets `PoReturn.status=1` and `StockDetails.status=1` | StoreController.php:860 |
| editpo_order | GET | HTML view | Edit-return form data | StoreController.php:874 |
| editorder_save | POST | JSON | Saves edited return-qty; updates `StockDetails.item_qty` directly, then `PoReturn.saveAll` | StoreController.php:907 |
| poreturn | POST | JSON | Paged PO-return grid | StoreController.php:932 |

Flag: `findgritems` (line 429) builds a response array but never `echo json_encode(...)`s it — calling clients get an empty body. Possible legacy cruft — verify before migrating.
Flag: `save_stock_transfer` in this controller is **not** a stock-transfer at all — it's a stock adjustment; the shared method name across `StoreController` and `StockTranferController` for two different features is confusing and both perform different (and inconsistent) stock_details write strategies (raw INSERT here vs. commented-out INSERT there).

### Controller/SupplierMasterController.php (misleadingly named — implements Material Request, per prior-pass note, reconfirmed)
`$uses` (line 49): only `SupplierMaster` is declared, yet the controller body references `$this->MaterialRequest`, `$this->MaterialRequestDetails`, `$this->Store` (lines 60-62, 76-77, 87) which are never declared in `$uses` — these resolve only via CakePHP's dynamic model-loading magic (works because tables/model files exist elsewhere), a fragile pattern worth flagging for the port (Next.js route handlers will need explicit imports for `MaterialRequest`/`MaterialRequestDetails`/`Store`, not just `SupplierMaster`).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty) | Placeholder | SupplierMasterController.php:50 |
| addnewrow | GET | HTML (empty) | Placeholder | SupplierMasterController.php:54 |
| form | POST/GET | HTML view | Material Request add/edit form (joined `material_request` + `mr_details`) | SupplierMasterController.php:57 |
| materiallist | POST | JSON | Paged Material Request grid | SupplierMasterController.php:71 |
| materialdelete | POST/GET (REQUEST) | JSON | Soft-delete Material Requests | SupplierMasterController.php:111 |
| save | POST | none (no echo) | Saves `MaterialRequest` + loops `item_no[]` array to save `MaterialRequestDetails` rows; **bug-prone**: `$arr_form_data['mr_pkey'] == 'mr_pkey'` string-literal comparison at line 137 looks like a copy-paste bug (should probably check for non-empty pkey, not compare to the literal string `'mr_pkey'`) — this condition is effectively always false, so `modified_by` is never set on edit | SupplierMasterController.php:129 |

Flag: `save()` line 137 `if ($arr_form_data['mr_pkey'] == 'mr_pkey')` — almost certainly a bug (comparing a form value to the literal string `'mr_pkey'` instead of checking whether it's set/non-empty). Possible legacy cruft — verify before migrating; likely intended `!= ''`.

### Controller/VendorController.php
`$uses` (line 51): `CentralControl, item_master, Event_receiver, Units, item_purchase, item_allocate, allocate_details, FinancialYear, UserCredentials, EmployeeDetails, Designation, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, TaxHead, EmployeeCTC, Contacts`. Note `item_master`/`item_purchase`/`item_allocate`/`allocate_details`/`Event_receiver` have no corresponding `Model/*.php` files in this codebase — dynamically resolved.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty) | Placeholder | VendorController.php:58 |
| master | GET | HTML (empty) | Placeholder | VendorController.php:61 |
| employeelist | POST | JSON | Paged listing querying table `item` (not `item_master`) via `$this->item_master->find()` — inconsistent table/model naming | VendorController.php:64 |
| downloadempctcformat | GET | binary (xlsx download) | Exports a "Vendor List" Excel template (misnamed "empctc") via PHPExcel | VendorController.php:107 |
| uploadandsaveempctc | POST (file upload) | JSON | Imports vendor/contact data from uploaded Excel into `Contacts` | VendorController.php:187 |
| lists_purchase | POST | JSON | Paged `item_purchase` grid joined to `item` | VendorController.php:344 |
| lists | POST | JSON | Paged `item_allocate` grid joined to `emp_details` | VendorController.php:390 |
| form | GET | HTML view | Item-purchase form data lookup from table `item` (not `item_master`) | VendorController.php:435 |
| Master_save | POST | JSON | Saves a `Contacts` row (used as generic vendor master save) | VendorController.php:446 |
| vendorlist | POST | JSON | Paged vendor grid filtered `Contacts.relationship='vendor'` | VendorController.php:460 |
| deleteEmp | POST/GET (REQUEST) | JSON | Soft-delete `Contacts` (vendor) rows | VendorController.php:506 |
| load_qty | GET | HTML view | Loads `item_view` (a DB view) | VendorController.php:520 |
| form_purchase | GET | HTML view | Item-purchase form dropdowns | VendorController.php:526 |
| purchase_file | GET | HTML view | Branch dropdown | VendorController.php:542 |
| save_Purchase | POST | JSON | Saves an `item_purchase` row | VendorController.php:548 |
| deleteEmppurchase | POST/GET (REQUEST) | JSON | Soft-delete `item_purchase` rows | VendorController.php:560 |
| allocate | GET | HTML view | Branch dropdown for allocation screen | VendorController.php:574 |
| allocate_form | GET | HTML view | Employee dropdown for allocation | VendorController.php:580 |
| allocate_save | POST | JSON | Saves `item_allocate` + loops `item_name[]`/`item_qty[]` into `allocate_details` | VendorController.php:586 |

Flag: This controller mixes **two unrelated "vendor" concepts**: (a) `Contacts`-table-backed vendor/supplier master (Master_save, vendorlist, deleteEmp) and (b) a completely separate, apparently unused/parallel "item_purchase / item_allocate" asset-purchase subsystem operating on tables `item`, `item_purchase`, `itm_allocation`, `allocate_details` that do not otherwise appear referenced by `PurchaseOrderController`/`GoodsReceivedNotesController`. This looks like an **earlier, possibly abandoned parallel implementation of the asset/purchasing flow** — needs product confirmation on whether `lists_purchase`/`lists`/`allocate_save` (item_purchase/item_allocate path) is live functionality or dead cruft before migrating. Possible legacy cruft — verify before migrating.

### Controller/PurchaseOrderController.php
`$uses` (line 51): `PurchaseOrder, MaterialRequest, PoMaterial, PurchaseList, MaterialRequestDetails, Store, PurchaseOrderDetails, Item, QuantityDetails, AdditionalDetails, ItemDetails, WarrantyDetails, Contacts, Site, UoMaster, Location, Contacts (dup), EmployeeDetails`.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | PO create form (items + vendor dropdowns) | PurchaseOrderController.php:53 |
| home | GET | HTML (empty) | Placeholder | PurchaseOrderController.php:66 |
| purchaseorders | POST | JSON | Paged PO grid; row-level `user_group==2` restriction via nested `access_store` subquery (lines 93-100) — reconfirms prior-pass finding | PurchaseOrderController.php:72 |
| materialtable | POST | JSON | Paged Material-Request grid (pending-qty aware), same `access_store` branch-scoping pattern (lines 154-164) | PurchaseOrderController.php:139 |
| searchm | GET (REQUEST) | JSON | Item-desc search | PurchaseOrderController.php:212 |
| details | GET | HTML view | PO print-preview data (no side effects) | PurchaseOrderController.php:228 |
| testdownloads | GET | HTML (`render('download')`) | **Confirmed bug (reconfirmed from prior pass):** print-preview action that silently mutates `purchase_order.grn_status = '2'` as a side effect via raw UPDATE — `PurchaseOrderController.php:291` (`$this->PurchaseList->query("UPDATE purchase_order set grn_status = '2' where po_pkey = $po_pkey");`) inside a GET-triggered "test download" view render at `testdownloads()` (`PurchaseOrderController.php:281`). This silently advances GRN workflow state merely by viewing/printing a PO. Flag as a correctness bug to fix, not replicate, in the Next.js port. | PurchaseOrderController.php:281 (mutation at :291) |
| downloads | GET | binary (PDF via HTML2PDF) | PO PDF export — no `grn_status` mutation here (unlike `testdownloads`), so behavior differs between the two "download" actions despite similar names | PurchaseOrderController.php:335 |
| save | POST | JSON | Creates PO (if `po_fkey` empty) OR appends items to an existing PO's `po_item_details`, also bumps `mr_details.ordering_qty` and sets `material_request.po_status='2'` via raw SQL — this is the MR→PO status-field transition referenced in prior-pass notes, reconfirmed | PurchaseOrderController.php:408 |
| loadtable | GET | HTML fragment (raw echo) | PO line-items table partial, same branch-scoping pattern | PurchaseOrderController.php:465 |
| edit | GET | HTML view | PO edit form | PurchaseOrderController.php:548 |
| form | GET | HTML view | Add-to-PO form from a Material Request | PurchaseOrderController.php:584 |
| purchasedelete | POST/GET (REQUEST) | JSON | Soft-delete POs | PurchaseOrderController.php:632 |
| getautocompletionspo_number | GET (REQUEST) | JSON | PO-number autocomplete | PurchaseOrderController.php:647 |
| getitem | internal helper | return value | Item detail join | PurchaseOrderController.php:662 |
| getautocompletionslocation | GET (REQUEST) | JSON | Location autocomplete | PurchaseOrderController.php:676 |
| getautocompletionssupplier_name | GET (REQUEST) | JSON | Supplier-name autocomplete (`Contacts` where `relationship='Vendor'`) | PurchaseOrderController.php:691 |
| getautocompletionssupplier_code | GET (REQUEST) | JSON | Supplier-code autocomplete | PurchaseOrderController.php:706 |
| getautocompletionsstore_code | GET (REQUEST) | JSON | Store-code autocomplete | PurchaseOrderController.php:721 |
| meteriallist | POST | JSON | Aggregated item-desc/unit-sum listing (typo'd method name, kept as-is) | PurchaseOrderController.php:736 |
| addnewrow | GET | HTML view | Add-row form fragment | PurchaseOrderController.php:766 |
| save1 | POST | none (no final echo) | Alternate/older PO-save path saving `PurchaseOrderDetails`, updating `MaterialRequestDetails.unit`, and `PoMaterial` — appears to be a **parallel/duplicate save path to `save()`** using a different schema shape (`item_code[]`, `uom[]`, etc. vs `save()`'s `required_qty[]`) | PurchaseOrderController.php:776 |
| getitem1 | GET | HTML fragment (raw echo) | MR-details table lookup by item_code, with inline HTML generation | PurchaseOrderController.php:853 |
| itemtotel | GET | plain-text number (echo, not JSON) | Sums `mr_details.unit` for an item | PurchaseOrderController.php:926 |

Flags reconfirmed from prior pass:
- **`testdownloads()` bug**: `PurchaseOrderController.php:281` (method) / `:291` (the mutating UPDATE). A GET-style "preview/print" action silently mutates workflow state (`grn_status`). This is a correctness bug — the Next.js port should separate "mark as printed/issued" from "render preview," with an explicit, idempotent, intentional state-transition endpoint.
- **Linear/status-driven procurement chain reconfirmed**: `save()` (line 408-462) shows MR→PO transition purely via `mr_details.ordering_qty` increments and `material_request.po_status='2'` string-literal update — no second-approver gate, no distinct approval table/workflow engine.
- **`save()` vs `save1()` duplication**: two independently-maintained PO-save code paths exist (line 408 and line 776) with different expected form-field shapes; migrating both risks perpetuating divergent business rules — needs product/dev confirmation on which is actually wired to the live UI before choosing one as the migration source of truth.

### Controller/DirectPurchaseOrderController.php (reconfirmed disconnected from stock, per prior-pass note)
`$uses` (line 51): `DirectPurchaseOrderDetails, DirectPurchaseOrder, Item, Store, QuantityDetails, AdditionalDetails, ItemDetails, WarrantyDetails, Contacts, Site, UoMaster`. **No `StockDetails` model in `$uses` at all**, and no reference to `StockDetails`/`stock_details` anywhere in this file's actions — reconfirms prior-pass finding that Direct PO items never post to the stock ledger.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| home | GET | HTML (empty) | Placeholder | DirectPurchaseOrderController.php:54 |
| index | GET | HTML view | Direct-PO create form (items + sites) | DirectPurchaseOrderController.php:58 |
| addnewrow | GET | HTML view | Add-row form fragment | DirectPurchaseOrderController.php:76 |
| save | POST | JSON | Creates `DirectPurchaseOrder` + `DirectPurchaseOrderDetails` (no stock effect) | DirectPurchaseOrderController.php:86 |
| getitem | internal helper | return value | Item detail join | DirectPurchaseOrderController.php:114 |
| loadtable | GET | HTML fragment (raw echo) | Direct-PO line items partial | DirectPurchaseOrderController.php:130 |
| editorder | GET | HTML view | Edit form data | DirectPurchaseOrderController.php:192 |
| editordersave | POST | JSON | Saves edited line item | DirectPurchaseOrderController.php:218 |
| getautocompletionsdirect_po_number | GET (REQUEST) | JSON | Direct-PO-number autocomplete | DirectPurchaseOrderController.php:229 |
| delete | GET/POST | JSON | Soft-delete Direct PO (message text says "Material Request" — copy-paste artifact) | DirectPurchaseOrderController.php:251 |
| deleteorder | GET/POST | JSON | Soft-delete Direct PO line item | DirectPurchaseOrderController.php:264 |

Flag reconfirmed: `DirectPurchaseOrderController` never touches `stock_details`/`StockDetails` in any action — items purchased through this flow are recorded in `direct_purchase_order`/`direct_po_details` only and never increment on-hand stock. If the business intends Direct POs to affect inventory, this is a functional gap to close during migration, not silently replicate. `delete()` message text ("Material Request deletion successfull!" at line 258) is copy-pasted from `SupplierMasterController`/other controllers — cosmetic but indicates copy-paste-derived code.

### Controller/GoodsReceivedNotesController.php
`$uses` (line 51-52): `GrItemDetails, StockDetails, GoodsReceivedNotes, Contacts, Store, PurchaseOrderDetails, Item, item_purchase, GoodsReceivedNotesitem, PurchaseOrder, MaterialRequest, PoMaterial, PurchaseList, MaterialRequestDetails, Store (dup), PurchaseOrderDetails (dup), Item (dup), StockStoreTranferItem, StockStoreTranfer, EmployeeDetails`. Note: both `GrItemDetails` (Model/GrItemDetails.php) and `GoodsReceivedNotesitem` (Model/GoodsReceivedNotesItem.php) are declared — **two separate model classes mapped to the same table `gr_item_details`** (confirmed: `Model/GrItemDetails.php:14` and `Model/GoodsReceivedNotesItem.php:27` both set `$useTable = 'gr_item_details'`). Redundant duplicate models — flag for consolidation.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | GRN create form (store list, branch-scoped for `user_group==2` via `access_store`) | GoodsReceivedNotesController.php:54 |
| home | GET | HTML (empty) | Placeholder | GoodsReceivedNotesController.php:72 |
| purchaselist | POST | JSON | Paged PO-to-receive grid, branch-scoped like PurchaseOrderController | GoodsReceivedNotesController.php:76 |
| pgrnlist | POST | JSON | Paged GRN grid, branch-scoped via `access_store` | GoodsReceivedNotesController.php:157 |
| showgrndetails | GET | HTML view | GRN detail view | GoodsReceivedNotesController.php:208 |
| form | GET | HTML view | GRN-creation item picker for a PO/store, with per-item running-stock calc (current version at 287 replaced an older commented-out per-item-query version at 216-285 with a batched single-query stock lookup — performance fix, not behavior change) | GoodsReceivedNotesController.php:287 |
| edit | GET | HTML view | GRN edit form | GoodsReceivedNotesController.php:393 |
| grndelete | POST/GET (REQUEST) | JSON | Soft-delete GRN (note: `updateAll` condition string `'GoodsReceivedNotes   .grn_pkey'` at line 421 has stray whitespace inside the field name — likely harmless to CakePHP's condition parser but worth cleaning) | GoodsReceivedNotesController.php:413 |
| **save** | POST | JSON | **Core GRN receipt action.** On first save creates the `GoodsReceivedNotes` header. On line-item save (`grn_fkey` present): sets `purchase_order.grn_status = 0` (line 458, reopens/marks-in-progress rather than "received"), then loops incoming rows, calls `GrItemDetails->save()` per line, and for each saved line calls `$this->UpdateStock(...)` (line 475) | GoodsReceivedNotesController.php:429 |
| **UpdateStock** | internal helper (called from save()) | n/a | **Answers the "hook vs raw SQL" question**: stock increment is implemented as a **controller-level PHP method**, not a CakePHP model `afterSave`/`beforeSave` hook, and not raw SQL either — it does a `GrItemDetails->find()` join to pull store/supplier/po/item context, builds `$arr_save_data`, and calls `$this->StockDetails->saveAll($arr_save_data)` (CakePHP ORM save, line 523). So: **GRN receipt increments `stock_details` via an explicit controller method invoking model `saveAll()`, not a model hook and not raw SQL.** This is architecturally important for the Next.js port: the stock-increment logic must be reimplemented explicitly (e.g., in a service function called from the GRN save handler), since there is no model-level hook to lean on. | GoodsReceivedNotesController.php:486 |
| loadtable | GET | HTML fragment (raw echo) | GRN line items table partial | GoodsReceivedNotesController.php:528 |
| getautocompletionsgr_number | GET (REQUEST) | JSON | GR-number autocomplete | GoodsReceivedNotesController.php:583 |
| getautocompletionsstore_code | GET (REQUEST) | JSON | Store-code autocomplete, `access_store`-scoped for employees | GoodsReceivedNotesController.php:599 |
| searchm | GET (REQUEST) | JSON | PO-number search | GoodsReceivedNotesController.php:624 |
| save1 | POST | none (no final echo) | **Alternate/parallel GRN save path** — saves into `GoodsReceivedNotesitem` (the *other* duplicate model for `gr_item_details`) rather than `GrItemDetails`/`UpdateStock()`, and does **not** call `UpdateStock()` at all, so this path does **not** touch `stock_details`. Sets `purchase_order.grstatus = 0` (note: field name `grstatus`, not `grn_status` as used elsewhere — likely a dead/incorrect column reference) | GoodsReceivedNotesController.php:643 |
| goodlist | POST | JSON | GRN grid (alternate query shape); references undefined `$arr_goods` when the `po_number` REQUEST branch is taken (only `$arr_meterial` is set in that branch, `$arr_goods` used in the shared foreach at line 732) — **bug**: PHP notice / empty listing when filtering by `po_number` | GoodsReceivedNotesController.php:691 |

Flags:
- **Stock-increment mechanism confirmed**: GRN receipt updates `stock_details` via the controller method `UpdateStock()` (`GoodsReceivedNotesController.php:486-525`) calling `StockDetails->saveAll()` — an explicit ORM call triggered from `save()`, not a model hook, not raw SQL insert (contrast with `StoreController::save_stock_transfer` which *does* use raw SQL INSERT into `stock_details`). Both patterns exist in this codebase for different stock-mutating flows; the Next.js port should standardize on one pattern (a single stock-mutation service).
- **`save1()` bypasses stock update entirely** (line 643) — a parallel GRN-save code path exists that writes `GoodsReceivedNotesitem` records but never calls `UpdateStock()`, meaning if this path is reachable from any UI it would create GRN item records with no corresponding stock increment. Possible legacy cruft — verify whether `save1` is still wired to any view before deciding to port it.
- `goodlist()` (line 691) has an undefined-variable bug when `$_REQUEST['po_number']` is set (queries into `$arr_meterial` but iterates `$arr_goods`). Possible legacy cruft/bug — verify before migrating.
- Reconfirms prior-pass finding: no second-approver step is visible in the GRN save flow — receipt is a single-step `save()` action performed by whoever has store access.

### Controller/VehicleController.php
`$uses` (line 36): `Vehicle` only.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty) | Placeholder | VehicleController.php:41 |
| vehicle | GET | HTML view | Add/Edit vehicle form data | VehicleController.php:46 |
| delete | GET/POST | JSON | Soft-delete vehicle | VehicleController.php:60 |
| save | POST | JSON | Create/update vehicle with duplicate reg-number check (name-duplicate check is dead-commented, lines 93-99, 110-116) | VehicleController.php:74 |
| listvehicle | POST | JSON | Paged vehicle grid with OR-style multi-field search | VehicleController.php:140 |
| checkexpensetypenameexists | POST | plain-text count (echo int, not JSON) | Duplicate-name check for an "expense type" field that doesn't otherwise appear used on Vehicle | VehicleController.php:179 |
| checkregnoexists | GET/POST | plain-text count (echo int, not JSON) | Duplicate reg-number check (this is the one actually wired into `save()`'s guard logic via a prior AJAX call, presumably) | VehicleController.php:195 |
| **search** | GET (query) | JSON | **Confirmed broken OR-search bug (reconfirmed from prior pass), exact citation: `VehicleController.php:222-227`.** The `conditions` array is built as: `array('(Vehicle.model_dec LIKE "%'.$searchkey.'%")' or '(Vehicle.reg_number LIKE "%'.$searchkey1.'%")', 'Vehicle.status' => 1)`. Because PHP's `or` operator has lower precedence than `.` (string concatenation) but is still evaluated *before* the array element is added to the list, this expression collapses to a **boolean** short-circuit result (`or` returns `true` as soon as the left operand — a non-empty string — is truthy), NOT a SQL fragment. The array element actually stored is the literal boolean `true` at key `0`, not either LIKE clause. CakePHP then tries to build SQL condition from a boolean value at a numeric key, which does not filter by search term at all — the search silently ignores `model_dec`/`reg_number` input entirely and only the `status=1` condition binds correctly. Additionally, `'fields' => 'vehicle_master_pkey,model_dec,reg_number','Vehicle.'` (line 223) has a stray trailing `,'Vehicle.'` string fragment left over from a find-and-replace, which is also dead/harmless but confirms the surrounding code was edited carelessly. | VehicleController.php:215 (method) / :222-227 (bug) |

Flag reconfirmed: `VehicleController::search()` broken OR-search — do not port the PHP `or`-in-array-literal pattern; must be reimplemented as a proper `WHERE (model_dec ILIKE $1 OR reg_number ILIKE $2) AND status = 1` in the new backend.

---

## 2. Model Validation & Business Rules

**Headline finding: every model backing this cluster is a bare CakePHP model shell.** None of the following models define a `$validate` array, `beforeSave`, `afterSave`, `beforeValidate`, or any other lifecycle hook. `Model/AppModel.php` (the shared base class) also defines no hooks — it only forwards the constructor (`Model/AppModel.php:35-37`). This means **all validation and business rules for Assets/Inventory/Purchasing live in controller code** (ad hoc PHP `isset()`/duplicate-check AJAX calls invoked by the frontend before submit, e.g. `chkcategory()` in `ItemController.php:121` and `StoreController.php:196`, `checkregnoexists()` in `VehicleController.php:195`), not in the model layer. This is a significant finding for the Next.js migration: there is no server-side model validation safety net today — any required-field/uniqueness/type checks in the legacy app are either client-side or in these standalone controller "check" endpoints that must be explicitly called by the frontend before save. The Next.js API routes must add real server-side validation since none exists to carry over from the model layer.

Models inspected (all confirmed to contain only `$name`/`$primaryKey`/`$useTable`/occasionally `$useDbConfig`, no `$validate`, no hooks):

| Model | Table | primaryKey | path | Notes |
|---|---|---|---|---|
| Item | item_master | item_master_pkey | Model/Item.php:24-29 | No validation |
| PurchaseOrder | purchase_order | po_pkey | Model/PurchaseOrder.php:24-29 | `useDbConfig='sitedb'`; no validation |
| PurchaseOrderDetails | po_item_details | po_item_pkey | Model/PurchaseOrderDetails.php:24-29 | `useDbConfig='sitedb'`; no validation |
| GoodsReceivedNotes | goods_receved_notes | grn_pkey | Model/GoodsReceivedNotes.php:24-29 | `useDbConfig='sitedb'`; no validation |
| GoodsReceivedNotesitem (class `GoodsReceivedNotesitem`) | gr_item_details | gr_item_pkey | Model/GoodsReceivedNotesItem.php:24-28 | Duplicate of GrItemDetails (see below) |
| GrItemDetails | gr_item_details | gr_item_pkey | Model/GrItemDetails.php:7-16 | Same table as GoodsReceivedNotesitem — redundant model pair |
| StockDetails | stock_details | stock_details_pkey | Model/StockDetails.php:7-16 | The actual stock ledger table; no hooks — confirms increments are app-code-driven, not model-driven |
| Store | store_master | store_master_pkey | Model/Store.php:24-29 | No validation |
| Vehicle | vehicle_master | vehicle_master_pkey | Model/Vehicle.php:7-17 | No validation (duplicate-checks are controller-side only) |
| ItemSpecification | item_specification | specification_pkey | Model/ItemSpecification.php:24-28 | No validation |
| DirectPurchaseOrder | direct_purchase_order | direct_po_pkey | Model/DirectPurchaseOrder.php:24-29 | `useDbConfig='sitedb'`; no validation |
| DirectPurchaseOrderDetails | direct_po_details | direct_po_details_pkey | Model/DirectPurchaseOrderDetails.php:24-29 | `useDbConfig='sitedb'`; no validation |
| StockTranfer | stock_tranfer | stock_tranfer_pkey | Model/StockTranfer.php:24-28 | No validation |
| StockTranferItem | stock_tranfer_item | stock_item_pkey | Model/StockTranferItem.php:24-28 | No validation |
| Assets | asset_management | asset_pkey | Model/Assets.php:7-17 | No validation |
| AssetsModel | asset_management | asset_pkey | Model/AssetsModel.php:7-17 | Duplicate model pointing at same table as `Assets` — redundant, appears unused by AssetController (which uses `Assets`) |
| AppModel (base) | n/a | n/a | Model/AppModel.php:34-38 | No hooks defined at the shared base level either |

### Stock-quantity-adjustment business logic — answered per flow

1. **GRN receipt (increment)**: Controller-level orchestration. `GoodsReceivedNotesController::save()` (`GoodsReceivedNotesController.php:429`) loops submitted line items, saves each to `GrItemDetails`, then explicitly calls `UpdateStock($grdetailsid, $received_qty, $mr_fkey)` (`GoodsReceivedNotesController.php:475`). `UpdateStock()` (`GoodsReceivedNotesController.php:486-525`) resolves store/supplier/po/item context via a `find()` with joins, then calls `$this->StockDetails->saveAll($arr_save_data)` — a normal CakePHP ORM insert, **not** a model hook, **not** raw SQL. This is the "correct"/canonical stock-increment path for received goods.
2. **PO Return (decrement)**: `StoreController::save_po_return()` (`StoreController.php:719`) builds a negative-quantity row (`$return_qty = $arr_request_data['return_qty']*-1`, line 742) and calls `$this->StockDetails->save($arr_request_detail)` (line 764) — again ORM `save()`, not a hook, not raw SQL.
3. **Stock Adjustment**: `StoreController::save_stock_transfer()` (`StoreController.php:96`, despite the method name this is the *adjustment* feature, not transfer) does a **raw SQL `INSERT INTO stock_details (...)`** (`StoreController.php:125-126`) after saving `StockAdjustment`/`StockAdjustmentDetails` via ORM. This is the one clear instance in the cluster of a direct hand-written SQL insert into the stock ledger from a controller, bypassing the model layer entirely.
4. **Stock Transfer**: `StockTranferController::save_stock_transfer()` (`StockTranferController.php:68`) has its `StockDetails->save()` calls **commented out** (`StockTranferController.php:115-137`) — no stock_details write happens at line-item-save time. The only live stock effect for transfers appears to be inside the database stored procedure `stock_tranfer_pkey_prc`, invoked from `StockTranferController::submit_return()` (`StockTranferController.php:859`: `CALL stock_tranfer_pkey_prc('$id', @perr_msg)`). **The procedure body is not present in this codebase (DB-side logic, out of scope of these PHP files)** — migrating this flow to Next.js requires either extracting the stored-procedure SQL from the database directly or reverse-engineering its effect by testing, since the PHP layer alone does not show what it does to `stock_details`.
5. **PO/GRN status fields** (workflow, not quantity): `mr_details.po_status`, `purchase_order.grn_status` are updated via raw SQL string-literal writes scattered through `PurchaseOrderController::save()` (line 447) and `GoodsReceivedNotesController::save()` (line 458) — reconfirms the prior-pass finding that the procurement chain is linear/status-field-driven with no dedicated workflow/approval table.

### Duplicate/redundant models (relevant to migration data-model design)
- `GrItemDetails` (Model/GrItemDetails.php) and `GoodsReceivedNotesitem`/class name vs `GoodsReceivedNotesItem` filename (Model/GoodsReceivedNotesItem.php) both map to table `gr_item_details` and are used inconsistently across controllers (`GoodsReceivedNotesController::save()` uses `GrItemDetails`+`UpdateStock`; `GoodsReceivedNotesController::save1()` uses `GoodsReceivedNotesitem` and skips `UpdateStock`). Consolidate to one entity in the new schema/ORM.
- `Assets` (Model/Assets.php) and `AssetsModel` (Model/AssetsModel.php) both map to `asset_management`; only `Assets` appears actually used by `AssetController`. `AssetsModel` looks unused — verify with a repo-wide grep before dropping in migration (out of this cluster's direct scope, flagging for awareness).

---

## Summary of Flagged Bugs / Legacy Cruft (all re-verified with exact citations)

1. **`VehicleController::search()`** — broken OR-search due to PHP `or` operator misuse inside an array literal. `VehicleController.php:222-227` (method starts `VehicleController.php:215`). Confirmed exact lines.
2. **`PurchaseOrderController::testdownloads()`** — silently mutates `purchase_order.grn_status='2'` as a side effect of a print-preview GET action. Mutation at `PurchaseOrderController.php:291`, inside method starting `PurchaseOrderController.php:281`. Confirmed exact line.
3. **`DirectPurchaseOrderController`** — entirely disconnected from `stock_details`; no model/action in the file references `StockDetails`. Confirmed via full-file read (`DirectPurchaseOrderController.php:1-286`) and absence of `StockDetails` in `$uses` (line 51).
4. **`SupplierMasterController::save()`** — likely-bugged conditional `if ($arr_form_data['mr_pkey'] == 'mr_pkey')` (`SupplierMasterController.php:137`) compares to a literal string instead of checking emptiness; `modified_by` effectively never gets set on edit.
5. **`StoreController::getStoreNameList()`** — references undefined `$emps` variable (`StoreController.php:87`).
6. **`StoreController::findgritems()`** — builds a result array but never echoes it; callers receive an empty body (`StoreController.php:429-459`).
7. **`GoodsReceivedNotesController::goodlist()`** — undefined-variable bug: queries into `$arr_meterial` but iterates `$arr_goods` when filtering by `po_number` (`GoodsReceivedNotesController.php:705-715` vs the shared `foreach` at `:732`).
8. **`GoodsReceivedNotesController::save1()`** — parallel GRN-save path that never calls `UpdateStock()`, so it can create GRN item rows with no corresponding stock increment if reachable (`GoodsReceivedNotesController.php:643-689`); also writes to `purchase_order.grstatus` (line 652), a differently-named field than the `grn_status` used elsewhere — possible dead/incorrect column reference.
9. **`StockTranferController::save_stock_transfer()`** — the `stock_details` INSERT is commented out (`StockTranferController.php:115-137`); real stock effect for transfers (if any) lives in the untraceable-from-PHP stored procedure `stock_tranfer_pkey_prc` called from `submit_return()` (`StockTranferController.php:859`).
10. **Duplicate models**: `GrItemDetails`/`GoodsReceivedNotesitem` (both → `gr_item_details`) and `Assets`/`AssetsModel` (both → `asset_management`).
11. **`VendorController`** — contains what looks like an abandoned parallel asset-purchase subsystem (`item_purchase`, `item_allocate`, `allocate_details`, table `item`) alongside the real `Contacts`-based vendor master; needs product confirmation before deciding what to migrate (`VendorController.php:344-434`, `:520-613`).
12. **`AssetController`** — three overlapping branch/employee-dropdown implementations (`Create_asset` at 1214, `Create_asset_new`/`Company_asset_new` at 1249/1329) with inconsistent access-scoping logic; pick one canonical pattern for migration.
13. **No server-side model validation anywhere in this cluster** — every model is a bare shell (see Section 2 table); all "validation" observed is either client-side or standalone controller AJAX "check" endpoints the frontend must remember to call before submit (e.g., duplicate checks). The Next.js port must add real validation since there is none to port from the model layer.

---

### 2.10 Performance Management & HR Admin

# Backend Report 11 — Performance Management & HR Admin

Scope: `PerformanceController`, `SelfReviewController`, `TeamReviewController`, `HierarchyReviewController`,
`SurveyController`, `PromoController`, `FullandFinalsettlementController`, `ResignationRequestController`,
`ExceptionRuleController`, and their backing Models.

All controllers extend `AppController` (session-based auth, `user_group` 1=admin/2=employee — see
`Controller/AppController.php:39-46`). None of the controllers in this cluster override `beforeFilter`/auth
logic — access control is whatever `AppController` enforces plus ad-hoc `$this->Session->read('emp_fkey')` /
`user_group` checks sprinkled inside individual actions (e.g. `Controller/PerformanceController.php:147-154`,
`Controller/PromoController.php:84-87`). No CSRF/API-token layer, no email/push notifications anywhere in this
scope (confirms prior finding).

---

## 1. Controller Action Inventory

### PerformanceController (`Controller/PerformanceController.php`)
`$uses`: AppModel, CompanyContactInfo, ReportAudit, ReportCriterias, LeaveRequests, EmpLeaveApproval, EmployeeConfig, SalaryHeadItems, EmployeeDetails, EmployeeLeaveTransaction, LeavePolicy, EmployeeInfo, Category, Status (line 8). This is a **reporting** controller — no CRUD on assessments, only builds/exports HR performance reports.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| hrreports | GET | HTML view | Landing page listing report types (Employee Marks Exec/Staff, Self-Appraisal workflow, Staff Assessment workflow, Annual Performance) | PerformanceController.php:12 |
| changereporttype | GET/POST | HTML (`showreport` view) | Loads criteria list (branch/employee/status) for chosen report type + available fin years | PerformanceController.php:26 |
| addreportcriteria | GET/POST | HTML (`showcriteria` view) | Adds another filter criterion row to the report builder UI | PerformanceController.php:71 |
| loadcriteriaitems | GET/POST | HTML (`loadcriteriaitems` view) | Loads item list for a given criteria model (validates model exists via `App::objects('model')`) | PerformanceController.php:86 |
| listcriteriaitems | GET/POST | JSON | Returns employees/categories/statuses matching criteria, branch-scoped for `user_group==2` | PerformanceController.php:104 |
| reportAudit | internal (called by generatereport) | none (writes to `ReportAudit`) | Logs an audit trail row for each report generation/download | PerformanceController.php:191 |
| generatereport | GET/POST | redirect-only (delegates) | Dispatches to one of the 5 `generate*Report` methods by `$type`, then calls `reportAudit` | PerformanceController.php:270 |
| listemployeefields | GET | JSON | Returns field name/heading metadata from `Vendor/ReportFields/EmployeeInformationFields.php` for report column builder | PerformanceController.php:299 |
| generateEmployeeMarksReport | internal | HTML / PDF (html2pdf) / XLSX (PHPExcel) download | Executive marks report: joins `assessment_summary_executive`, `self_review_details`, `termination`, `user_credentials`; groups reporting/reviewing officer marks per employee | PerformanceController.php:340 |
| generateEmployeeMarksStaffReport | internal | HTML / PDF / XLSX download | Same as above but for staff/workmen, sourced from `assessment_attributes_staff_details` (`status=3` filter = fully reviewed) | PerformanceController.php:678 |
| generateSelfAppraisalReport | internal | HTML / PDF / XLSX download | Workflow status report over `self_review_details` (executive track), status-label mapping | PerformanceController.php:997 |
| generateStaffAssessmentReport | internal | HTML / PDF / XLSX download | Workflow status report over `assessment_attributes_staff_details` (staff/workmen track) | PerformanceController.php:1446 |
| generateAnnualPerformanceAssessment | internal | HTML / PDF / XLSX download | Combined executive + staff annual report; joins self_review, assessment_attributes_executive_details/executive, assessment_summary_executive, assessment_attributes_staff_details/items | PerformanceController.php:1823 |

Note: all `generate*Report` methods are declared `public function` but are effectively internal — they are only invoked via `generatereport()`'s switch and read from `$_REQUEST` directly rather than method args, so calling them as a standalone route would behave identically to a GET on `generatereport`. Flag as **routing quirk to normalize during migration**, not dead code.

### SelfReviewController (`Controller/SelfReviewController.php`)
`$uses`: AppModel, EmployeeDetails, LeaveRequests, SelfReviewDetails, AssessmentAttributesStaffDetails (line 53). This is the **employee self-appraisal initiation + first-stage workflow** entry point (executive track feeds `self_review_details`; the same controller also touches `assessment_attributes_staff_details` for combined listings/deletes).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing page, shows count of self reviews for current employee | SelfReviewController.php:60 |
| initaiteSelfReview | GET | HTML (`initaite_self_review` view) | Alternate landing/initiation view | SelfReviewController.php:70 |
| newReview | GET | HTML (`new_review` view) | Form to start a new self review; lists all active employees | SelfReviewController.php:82 |
| listreviews | POST | JSON | Combined/unpaginated-ish list across `self_review_details` AND `assessment_attributes_staff_details`, computes a derived `status_label` (state-machine display logic client-side) | SelfReviewController.php:123 |
| listreviews_self_review | GET (query params) | JSON | Paginated list of the current employee's own `self_review_details` rows only, with same status_label derivation | SelfReviewController.php:365 |
| getEmployeeDetails | POST | JSON | Returns employee profile + eligible reporting/reviewing officers (from `emp_config` type HIERARCHY/LAPPR) + fin years + category (`WORK` category code → `hierarchy` track, else `employee`/executive track) — **this is the category→track branch point** referenced in prior findings | SelfReviewController.php:501 |
| getAbsencePeriod | POST | JSON | Sums `attendance_register.lop_total` for employee/fin_year window | SelfReviewController.php:650 |
| createSelfReview | POST | JSON | Inserts new `self_review_details` row with `status='New'`; **guards against duplicate** via count query on `emp_fkey+fin_year` where status != 'Deleted' | SelfReviewController.php:689 |
| form | GET | HTML view (default `form.ctp`) | Legacy/alternate self-review form (uses `emp_config` LAPPR/HIERARCHY lookups) | SelfReviewController.php:739 |
| view | GET | HTML (`form` view) | View/edit an existing self review by pkey; loads duty/work-done desc, reporting/reviewing officer | SelfReviewController.php:768 |
| saveSelfReview | POST | JSON | Updates `self_review_details`; sets `is_applied`/`applied_by`/`applied_date` when `status=='Applied'`, or `is_drafted` when `status=='Draft'` | SelfReviewController.php:818 |
| deleteSelfReview | POST | JSON | Soft-deletes either `self_review_details` (status→'Deleted') or `assessment_attributes_staff_details` (status→0), selected by `$table` param | SelfReviewController.php:886 |
| previewPdfReview | GET | PDF download (html2pdf) | Renders self-review as PDF via `SelfReview/performance_report` view | SelfReviewController.php:922 |
| newBulkReview | GET | HTML (`new_bulk_review` view) | Bulk self-review initiation UI, category-filtered employee list | SelfReviewController.php:975 |
| getEmployeesByCategory | POST only (`onlyAllow('post')`) | JSON | Employees filtered by `grade.category_fkey` | SelfReviewController.php:1016 |
| getFinYears | POST | JSON | Distinct fin years available for a set of employee branch codes | SelfReviewController.php:1051 |
| createBulkSelfReview | POST | JSON | Bulk-creates `self_review_details` rows for multiple employees; **skips** employees that already have a non-deleted record in either `self_review_details` or `assessment_attributes_staff_details` for that fin_year, and skips employees whose branch has no matching fin_year row; returns list of unsaved employees | SelfReviewController.php:1120 |

### TeamReviewController (`Controller/TeamReviewController.php`) — Executive track (reporting/reviewing officer scoring)
`$uses`: AppModel, LeaveRequests, EmpLeaveApproval, EmployeeConfig, SalaryHeadItems, EmployeeDetails, EmployeeLeaveTransaction, LeavePolicy, EmployeeInfo, AssessmentAttributesExecutiveDetails (line 8).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Determines whether current user is reporting_officer or reviewing_officer for any `self_review_details` row and sets `role` | TeamReviewController.php:13 |
| listrequest | GET (uses `$_GET`) | JSON | Paginated queue of self-review requests awaiting the officer's action, split into "left"/current tabs by status+role combination (raw SQL against `self_review_details`) | TeamReviewController.php:257 (dead commented-out earlier version at 48-256 — **legacy cruft, superseded, safe to ignore**) |
| saveRequest | POST | JSON | **Core scoring endpoint.** Handles 3 action types via `action_type` param: `draft` (partial save, status `Reporting/Reviewing Person Drafted the Appraisal`), `send_back` (rejection loop — sets `is_rejected=1`, `was_rejected_by_reviewer` flag, resets attribute statuses), default = submit (upserts `assessment_attributes_executive_details` per-attribute marks + `assessment_summary_executive` totals, flips `self_review_details.status` to `Reporting/Reviewing Person submitted the Appraisal`). Enforces reporting-then-reviewing sequencing: reviewing officer submit is blocked if `reporting_officer_status != 1` (line 654) | TeamReviewController.php:397 |
| approverequest | GET (query params) | HTML view | Officer's scoring screen: loads attribute list, prior self-review answers, existing marks/summary for both officer roles, determines role from `emp_pkey` vs reporting/reviewing officer query params | TeamReviewController.php:790 |
| viewForm | GET (query params) | HTML view | Read-only view of a submitted assessment summary + attribute marks for one officer role | TeamReviewController.php:955 |
| previewPdfReview | GET | PDF download | Employee-facing PDF of executive review (`TeamReview/performance_report`) | TeamReviewController.php:1035 |
| previewDocumentReview | GET | HTML (`performance_view`, autoRender off but explicit render) | Non-PDF preview of the same document | TeamReviewController.php:1191 |
| previewPdfReviewHR | GET | PDF download | HR-facing variant of the PDF preview (role forced to `reviewing_officer`, includes `status` in output) | TeamReviewController.php:1330 |

### HierarchyReviewController (`Controller/HierarchyReviewController.php`) — Staff/Workmen track
`$uses`: AppModel, EmployeeDetails, AssessmentAttributesStaffDetails, SelfReviewDetails, AssessmentAttributesStaffItem (line 53). Uses a **numeric status code** model (1=drafted, 2=reported, 3=reviewed, 5=new/initial, 0=deleted) unlike TeamReview's string statuses.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing, count of staff assessments for current employee | HierarchyReviewController.php:60 |
| listreviews | POST (`$_POST` page/rows) | JSON | Paginated queue for current officer: `reporting_officer = me` OR (`reviewing_officer = me` AND `status>=2` AND `status!=5`) — reviewing officer only sees items once reporting officer has acted | HierarchyReviewController.php:70 |
| form | GET | HTML view | New staff-review form; lists leave/hierarchy config lookups and active `assessment_attributes_staff` (attribute catalog) | HierarchyReviewController.php:189 |
| getDesignation | POST | JSON | Looks up an employee's designation | HierarchyReviewController.php:226 |
| view | GET | HTML (`form` view) | View/edit one staff assessment by pkey; loads reporting/reviewing officer names, per-attribute marks (`assessment_attributes_staff_items`), determines `is_reviewing_officer` flag | HierarchyReviewController.php:245 |
| createHierarchyReview | POST | JSON | Creates new `assessment_attributes_staff_details` row, `status=5`, **duplicate guard** on emp_fkey+fin_year+status!=0 | HierarchyReviewController.php:370 |
| saveHierarchyReview | POST | JSON | **Core scoring endpoint (staff track).** Status-driven: `status==1`→draft, `status==2`→reporting officer marks "reported" (or reviewing officer drafts if they hit this branch), `status==3`→reviewing officer "reviewed". Saves header row then loops `reporting_officer_marks_{attrId}` fields into `assessment_attributes_staff_items` (per-attribute upsert) | HierarchyReviewController.php:454 |
| deleteHierarchyReview | POST | JSON | Soft-delete (`status=0`) | HierarchyReviewController.php:592 |
| sendBackReview | POST | JSON | **Rejection loop** — sets `status=1`, `is_rejected=1`, `rejected_by/date`, resets is_reported/is_reviewed/is_drafted | HierarchyReviewController.php:620 |
| previewPdfReview | GET | PDF download | Staff assessment PDF (`HierarchyReview/performance_report`), computes hide/show table logic based on who drafted vs reporting/reviewing officer | HierarchyReviewController.php:693 |
| createBulkHierarchyReview | POST | JSON | Bulk creation across multiple employees, same duplicate-guard pattern as SelfReview's bulk create, shares `getLOP` helper logic | HierarchyReviewController.php:837 |
| previewDocumentReview | GET | HTML (`performance_view`) | Non-PDF preview variant | HierarchyReviewController.php:1012 |
| selfAppraisalWorkmen | GET | HTML (`self_review_workmen` view) | Workmen-specific landing page | HierarchyReviewController.php:1125 |
| listreviewsWorkmen | POST | JSON | Paginated list filtered to `emp_fkey = current employee` (i.e. "my own submitted assessments" as workman) | HierarchyReviewController.php:1138 |

### SurveyController (`Controller/SurveyController.php`)
**Confirms prior finding: this is the Equipment/Facility Service Report (EFSR) ticket module, not an employee satisfaction survey.** `$uses`: SurveyType, SurveyCategory, OptionItems, OptionItemsValues, EfsrTickets, EfsrSite, EfsrEquipmentsMaster, EmpDetails, EquipmentType (line 57). "Survey" here = configurable equipment-inspection checklist (type→category→option items) used to build `efsr_tickets` service reports.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty view) | Placeholder | SurveyController.php:61 |
| form | GET | HTML view | Survey-type add/edit form, loads equipment types | SurveyController.php:65 |
| surveyTypesList | POST | JSON (paginated) | Lists `survey_type` rows joined to `equipment_type` | SurveyController.php:82 |
| getSurveyTypes | internal/GET | JSON or array (dual-mode via `$asArray`) | Simple active survey-type list for dropdowns | SurveyController.php:104 |
| saveSurveyType | POST | JSON | Create/update `survey_type`, duplicate-name/code guard via raw SQL LIKE check | SurveyController.php:118 |
| deleteSurveyType | GET (`$_GET['ids']`) | JSON | Soft delete (`status=0`) | SurveyController.php:155 |
| surveyCategory | GET | HTML view | Category management page | SurveyController.php:169 |
| getSurveyCategory | internal/POST | JSON or array | Category list, optionally filtered by survey_type | SurveyController.php:174 |
| surveyCategoryForm | GET | HTML view | Category add/edit form | SurveyController.php:194 |
| getCategoryOrder | POST | JSON | Ordered list of `category_order` values for a survey type (drag-reorder support) | SurveyController.php:214 |
| swapSurveyCategoryOrder | internal (called from saveSurveyCategory) | none | Swaps `category_order` values between two rows on reorder | SurveyController.php:230 |
| saveSurveyCategory | POST | JSON | Create/update category w/ duplicate code/name guard, calls swap-order helper on edit | SurveyController.php:242 |
| deleteSurveyCategory | GET (`$_GET['ids']`) | JSON | Soft delete | SurveyController.php:285 |
| options | GET | HTML view | Option-items management page | SurveyController.php:299 |
| getOptionsList | internal/POST | JSON or array | Option items + `GROUP_CONCAT`'d values, filtered by type/category | SurveyController.php:309 |
| optionsForm | GET | HTML view | Option item add/edit form, builds inline HTML template for value rows server-side | SurveyController.php:341 |
| getSurveyCategoryBySurveyType | POST | JSON | Cascading category dropdown | SurveyController.php:384 |
| saveOption | POST | JSON | Create/update `option_items` + child `option_items_values`, duplicate-name guard | SurveyController.php:395 |
| deleteOptionItemValues | internal (called from saveOption) | none | Soft-deletes removed value rows by pkey list | SurveyController.php:466 |
| getOptionsOrder | POST | JSON | Ordered list for reorder UI | SurveyController.php:475 |
| swapOptionItemOrder | internal | none | Swap order helper | SurveyController.php:492 |
| deleteOptionItem | GET (`$_GET['ids']`) | JSON | Soft delete | SurveyController.php:505 |
| getSites | internal (called by tickets/tickets_filter) | none (sets view var) | Site dropdown data | SurveyController.php:519 |
| tickets | GET | HTML view | Ticket listing page | SurveyController.php:524 |
| tickets_filter | GET | HTML view | Filter panel partial | SurveyController.php:533 |
| getEquipments | POST | JSON | Equipment + technician dropdowns filtered by site | SurveyController.php:536 |
| getTickets | POST | JSON (paginated) | Ticket list with status-based conditions (0=all, 2/3=pending on current user as approver) | SurveyController.php:564 |
| preview | GET | HTML view | Sets preview/download URLs for a ticket report iframe | SurveyController.php:612 |
| getCategories | internal (helper for reports) | array | Groups ticket category-transaction data by category, splits image vs text fields | SurveyController.php:622 |
| getLastServiceHtml | internal (helper for reports) | HTML string | Builds "last 4 services" table snippet for report | SurveyController.php:639 |
| reports | GET | HTML/PDF (dompdf, via `Vendor/dompdf`) | Full EFSR ticket service report render | SurveyController.php:688 |

`Controller/SurveyController_bkup_megha.php` and `Controller/FieldSurveyController_bkup.php` exist alongside — **backup files, confirmed dead/unreferenced by routes, legacy cruft, exclude from migration.**

### PromoController (`Controller/PromoController.php`)
`$uses`: CentralControl, EmployeeConfig, Family, NoticePeriod, qualifcations, Promotion, history, EmployeeTaxTransactions, EmpTaxSalTrans, FinancialYear, UserCredentials, EmployeeDetails, Designation, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, TaxHead, EmployeeCTC (line 51).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| getautocompletions_superior | GET (query) | JSON | Employee autocomplete for picking a superior/promotee, branch-scoped for `user_group==2` via `emp_proff.attr1` | PromoController.php:58 |
| promotion | GET | HTML view (`promotionjoin` or `promotion`, company-code-dependent allowlist) | Promotion request form; loads dept/designation/grade/vertical/branch combos, `emp_config_history`, salary structures, current CTC | PromoController.php:106 |
| listemployees | POST | JSON (paginated) | Lists `Promotion` rows pending current user's approval (`approved_by = me`, `promotion_status = 'APPLIED'`) | PromoController.php:253 |
| promotion_home | GET | HTML (empty view) | Placeholder landing | PromoController.php:311 |
| savepromotions | POST | JSON (malformed — references undefined `$result`, **likely bug**) | Creates a `Promotion` request, status `APPLIED` | PromoController.php:315 |
| approvepromotion | GET | HTML view | Approval screen; joins Promotion→employee/designation/department/shift/leave/salary/branch for review | PromoController.php:343 |
| approvesave | POST | JSON | **Promotion approval fan-out.** Sets `approved_status='Y'`, `promotion_status='APPROVED'`, then conditionally invokes internal helpers (chnageType, changeDesignation, changeDepartment, changeBranch, addToShift, addToLeave, promotionWage, addToSuperior, addToSalary) to actually apply each changed field to the employee's live profile/config rows, then saves the Promotion record | PromoController.php:488 |
| addToShift / addToSalary / addToSuperior / addToLeave | internal (also directly callable, no auth checks of their own) | boolean / JSON on error | Each supersedes the employee's active `emp_config` row of a given `type` (soft-close old row, insert new) | PromoController.php:543 / 567 / 607 / 632 |
| changeDesignation / changeDepartment / chnageType / changeBranch | internal (also directly callable) | boolean | Directly `UPDATE emp_proff` for designation/dept/type/branch, plus mirrors into `emp_config` (except `chnageType`, which does NOT touch `emp_config` — commented out at line 719-723) | PromoController.php:654 / 677 / 702 / 728 |
| UpdateSalary | internal | none / JSON on error | Calls DB stored function `sal_structure_distribution_fn` to redistribute salary structure | PromoController.php:753 |
| promotionWage | internal | boolean / JSON on error | Inserts new `EmployeeCTC` row effective start-of-month | PromoController.php:768 |
| rejsave | POST | JSON | Rejects a promotion (`approved_status='R'`, `promotion_status='REJECTED'`) — **does not revert/undo anything**, just marks status | PromoController.php:791 |

Note: `getautocompletions_superior` (line 68-73) builds a raw OR-condition string with unescaped `$searchkey`/`$emp_id` interpolated into a Cake condition array as a literal SQL fragment — **SQL injection risk**, flag for the security-focused pass.

### FullandFinalsettlementController (`Controller/FullandFinalsettlementController.php`)
`$uses`: AppModel, LeaveRequests, Termination, EmployeeDetails, EmployeeLeaveTransaction, LeavePolicy, Units, LeaveEncashmentMaster, allocate (line 50).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Landing page, lists active (`status=1`) and resigned (`status=2`) employees | FullandFinalsettlementController.php:56 |
| leavebalance | POST | JSON (paginated) | Employee's pending/authorized leave requests for F&F processing | FullandFinalsettlementController.php:68 |
| Terminate | POST | JSON | **Core termination write.** Upserts `termination` row (applied/submitted/last-working dates, notice period, reason, remarks) then `UPDATE emp_details SET status='2'` for the employee — **confirms prior finding: resignation→F&F chain via shared `termination` table + `emp_details.status` flip** | FullandFinalsettlementController.php:124 |
| details_res | GET | JSON | Fetches active termination record details for display | FullandFinalsettlementController.php:149 |
| Assets | GET | HTML view | Lists allocated assets (`allocate` joined to `asset_management`) pending return for the employee | FullandFinalsettlementController.php:172 |
| approve_selectd | POST | boolean (no echo — **response body is empty on success**, likely a bug/oversight) | Approves selected leave requests for settlement; calls stored proc `leave_transaction_prc` per leave, then bulk `updateAll` to `Approved` | FullandFinalsettlementController.php:186 |
| savedetails | POST | boolean (same empty-response issue) | Updates settled working days/leave balance/attendance on the `termination` row | FullandFinalsettlementController.php:221 |
| workingattendnacedays | POST | JSON | Computes attendance/leave/weekoff stats via DB functions (`leave_balance_inthe_year_fn`, `weekoff_days_count_fn`) for the settlement period | FullandFinalsettlementController.php:238 |
| reject_selected | POST | boolean (same empty-response issue) | Rejects selected leave requests for settlement, mirrors approve_selectd logic with `Rejected` status | FullandFinalsettlementController.php:258 |
| get_complete | GET | HTML view (sets `details`/`arr_salary_for_template`) | Runs stored proc `final_settle_pay_prc` to compute final settlement pay, then loads salary slip summary | FullandFinalsettlementController.php:292 |
| approveencash | POST | boolean (no JSON echoed on success path — inconsistent) | Approves leave encashment as part of F&F: computes eligible encashable days via `leave_balance_inthe_year_fn`, saves `LeaveEncashmentMaster`, calls proc `leave_encash_prc` | FullandFinalsettlementController.php:352 |
| getperiod | GET | JSON | Returns employee's configured notice period days | FullandFinalsettlementController.php:391 |
| leaveadjustment | POST | JSON | **Final leave/attendance adjustment writeback.** Updates `termination.leave_balance`/`approved_balance` (plus `working_days_settled`/`payroll_days` for company codes DEMO/KWMT specifically — hardcoded tenant branching), computes pro-rated encashment salary from annual CTC, writes `emp_settle_slip` rows (type ENCASHMENT + BALANCE) | FullandFinalsettlementController.php:469 |

A commented-out duplicate of `leaveadjustment` (lines 405-467) predates the active version — **legacy cruft, superseded.**

### ResignationRequestController (`Controller/ResignationRequestController.php`)
`$uses`: AppModel, ResignationRequests, LeaveRequests, EmployeeDetails, ResignationAccept, LeavePolicy, Termination (line 50). This is the **employee-initiated resignation request** flow that feeds into `termination`/F&F.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Employee's own resignation requests + linked `termination` row | ResignationRequestController.php:56 |
| Emprequests | GET | HTML view | Sets current employee key for the request-listing UI | ResignationRequestController.php:99 |
| getusers | GET (query) | JSON | Employee autocomplete for choosing the "authorised to" approver | ResignationRequestController.php:106 |
| addeditleave | GET | HTML view | Resignation detail/edit view with employee, approver, handover-to, and forwarded-to (HR) joins | ResignationRequestController.php:136 |
| listleaves | POST (`$_REQUEST` page/rows) | JSON | Manager's queue: requests authorised-to or forwarded-to the current employee, not yet authorized/approved | ResignationRequestController.php:270 |
| Manageleave | GET | HTML (empty view) | Placeholder | ResignationRequestController.php:317 |
| Request | GET | HTML view | Current employee's resignation request + approver details | ResignationRequestController.php:321 |
| letter | GET | HTML view | Resignation acceptance-letter render, company/employee/approver joins | ResignationRequestController.php:345 |
| letteredit | GET | HTML view | Editable variant of the letter view (near-duplicate of `letter`, joins on branch_code/desig_code/dept_code instead of `.id` — **inconsistent join keys between `letter` and `letteredit`, verify which is correct before migrating**) | ResignationRequestController.php:429 |
| withd | POST | JSON (`1`/`0`) | **Withdraw resignation.** Only allowed if a `termination` row exists (`$arr_count > 0`); soft-cancels `ResignationRequests` (status=0, `Resignation_status='Cancelled'`) and the linked `termination` row (status=0, remarks='Employee cancelled') and `ResignationAccept` row | ResignationRequestController.php:513 |
| grandrequest | POST | none (no echo) | Manager/HR action: saves checkbox confirmations (assets/formalities/leave cleared) onto `ResignationAccept` | ResignationRequestController.php:552 |
| Empagreed | POST | none (no echo) | Employee agrees to handover terms; `$arr_form_data['agree'] == 1;` at line 588 is a **comparison, not assignment — dead statement / no-op bug**, `agree` flag is never actually set to 1 in the save | ResignationRequestController.php:577 |
| deleteresignation | GET (`$rs` param, despite name should be POST-guarded — **no request-method check**) | none | Soft-deletes a `ResignationRequests` + `ResignationAccept` pair by pkey | ResignationRequestController.php:593 |
| Saverequests | POST | none (no echo) | **Core resignation submission.** Guards against duplicate active request (`ResignationRequests` count + `Termination` count both must be 0 to insert fresh); inserts a `termination` row directly via raw SQL insert (mirrors what `FullandFinalsettlementController::Terminate` does, but does NOT flip `emp_details.status` here — that only happens later when F&F processes it) — **note the workflow duplication**: resignation triggers a `termination` insert here, but the `emp_details.status='2'` flip is only performed by `FullandFinalsettlementController::Terminate`, meaning a plain resignation submission alone does not mark the employee resigned | ResignationRequestController.php:610 |

### ExceptionRuleController (`Controller/ExceptionRuleController.php`)
New module ("create by bindu 17-10-2025" per header comment) — attendance exception rules (late-in/early-out detection → LOP or leave deduction), **not previously covered**; included here since it lives in the HR admin area and touches `emp_details`/attendance.
`$uses`: large shared list including `ExceptionRule` (line 7).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML view | Lists active exception rules + branch combo | ExceptionRuleController.php:11 |
| getActiveRulesList | GET | JSON | Dropdown data of active rules | ExceptionRuleController.php:30 |
| newForm | GET | HTML (`newform` view) | New rule form | ExceptionRuleController.php:63 |
| saveRule | POST | JSON | Creates an `ExceptionRule` row; `leave_detect_type` forced to `105` (LOP) when `action_after_exception==1`, else uses `leaveType` or `0`; `detect_count` custom-rounded to nearest 0.5 via local `customRound()` closure-like function (line 91-101, **defines a global function inside the method — redeclaration risk if called twice in one request**) | ExceptionRuleController.php:71 |
| getRuleById | GET | HTML (`newform` view, pre-populated) | Loads one rule for edit | ExceptionRuleController.php:148 |
| updateRule | POST (reads raw `php://input` JSON, not `$this->request->data`) | JSON | Updates a rule; same leave-type/LOP branching logic duplicated inline (not reusing `saveRule`'s helper) | ExceptionRuleController.php:180 |
| getAllRules | GET | JSON (paginated) | Lists rules with `status=0` (soft-delete flag inverted vs `activate_status` — **two independent status fields, confusing semantics**), decorates with human labels via `getDataTypeLabel`/`getLeaveTypeLabel` | ExceptionRuleController.php:236 |
| getAppliedList | GET | JSON (paginated) | Lists `exception_applied` history rows w/ N+1 lookups (per-row queries for branch name and rule name — **N+1 query pattern**, migrate to a JOIN) | ExceptionRuleController.php:384 |
| getDataTypeLabel / getLeaveTypeLabel | internal (private) | string | Hardcoded label maps for `data_type` and `leave_detect_type` codes | ExceptionRuleController.php:469 / 483 |
| deleteRule | POST | JSON | Soft delete: `status=1, activate_status=0` (note inverted meaning vs `getAllRules`' `status=0` filter for "active") | ExceptionRuleController.php:547 |
| checkRuleName | POST | JSON | Case-insensitive duplicate name check via `hasAny` | ExceptionRuleController.php:609 |
| applyRule | GET/POST | JSON | **Applies an exception rule to a branch/month.** Multi-stage guard: (1) blocks if `attendance_register` already verified (`isdelete='N'`) for that branch/month, (2) blocks duplicate rule application for same branch+rule+month, (3) blocks if ANY rule already applied for that branch+month (contradicts guard #2 — #2 is unreachable given #3 is stricter, **redundant/dead guard**, lines 683-701 vs 702-723), then calls stored proc `exception_rule_apply_prce`, inserts an `exception_applied` audit row | ExceptionRuleController.php:630 |
| processAndGetLogs | GET | JSON (paginated) | Fetches (optionally triggers via `CALL ProcessExceptionRules`) attendance-change log rows for a rule/branch/month from `exception_attendance_change_log` | ExceptionRuleController.php:785 |
| downloadExceptionExcel | GET | XLSX download (PHPExcel) | Exports exception change log to Excel, per-row N+1 lookup against `EmployeeInfo` for name/branch (**N+1 pattern**, and note query condition `EmployeeInfo.emp_id` at line 1269 — likely should be `employee_id`, two earlier commented-out versions used `employee_id`, current one silently changed to `emp_id`, **possible regression, verify column name**) | ExceptionRuleController.php:1155 |
| reverseAppliedRule | POST | JSON | Calls stored proc `exception_rule_reversal_proc` then hard-deletes the `exception_applied` row | ExceptionRuleController.php:1312 |

Two earlier full implementations of `getAppliedList` and `downloadExceptionExcel` are left commented out in the file (lines 306-382, 866-1154) — **legacy cruft, superseded by the active versions below them, safe to remove.**

---

## 2. Model Validation & Business Rules

**Key finding: every model backing this cluster is a bare CakePHP shell.** None declare `$validate`, none override `beforeSave`/`beforeValidate`/`afterSave`/`beforeFind`, and `Model/AppModel.php` (the common parent, `Model/AppModel.php:34-38`) only forwards the constructor — it defines no global hooks either. All validation, duplicate-guarding, status-flag sequencing, and score computation happens **in the controllers**, via raw SQL and manual `find('count', ...)` duplicate checks, not in the model layer. This is a critical migration note: **there is no server-side field validation to port from Cake models — all of it must be reconstructed from controller logic** (required fields, numeric ranges, status enums are implicit in how controllers build arrays before calling `->save()`).

| Model | File | Table | PK | Notes |
|---|---|---|---|---|
| SelfReviewDetails | Model/SelfReviewDetails.php:7-17 | `self_review_details` | `self_review_details_pkey` | No validation/hooks. Executive self-appraisal record; string `status` field drives workflow (see below). |
| AssessmentAttributesExecutiveDetails | Model/AssessmentAttributesExecutiveDetails.php:7-17 | `assessment_attributes_executive_details` | `attr_exec_details_pkey` | No validation/hooks. Per-attribute mark rows for executive track, one row per (emp, attribute, fin_year). |
| AssessmentAttributesStaffDetails | Model/AssessmentAttributesStaffDetails.php:7-17 | `assessment_attributes_staff_details` | `attr_staff_details_pkey` | No validation/hooks. Staff/workmen assessment header; numeric `status` field (0=deleted,1=drafted,2=reported,3=reviewed,5=new). |
| AssessmentAttributesStaffItem | Model/AssessmentAttributesStaffItem.php:7-17 | `assessment_attributes_staff_items` | `aast_pkey` | No validation/hooks. Per-attribute mark line items for staff track, linked via `attr_staff_details_fkey`. |
| Termination | Model/Termination.php:7-12 | `termination` | `terminate_pkey` | No validation/hooks. Confirms prior finding — this is the single shared table for both HR-initiated termination and employee resignation, gated only by `status` (1=active,0=cancelled/superseded) and joined against `emp_details.status`. |
| ResignationRequests | Model/ResignationRequests.php:7-16 | `resignation_requests` | `Resignation_pkey` | No validation/hooks. |
| ResignationAccept | Model/ResignationAccept.php:7-17 | `resignation_accept` | `resignation_accept_pkey` | No validation/hooks. 1:1(ish) companion to a ResignationRequests row via `Resignation_pkey` FK. |
| Promotion | Model/Promotion.php:24-29 | `promotions` | `promotion_pkey` | No validation/hooks. |
| ExceptionRule | Model/ExceptionRule.php:9-19 | `exception_rule` | `exception_id` | No validation/hooks. |
| SurveyType | Model/SurveyType.php:7-16 | `survey_type` | `type_pkey` | No validation/hooks. |
| SurveyCategory | Model/SurveyCategory.php:7-16 | `survey_category` | `category_pkey` | No validation/hooks. |

### Business logic that lives in controllers instead of models

**Score / grade computation is entirely client-supplied.** `total_marks`, `grade`, `comments_recommendation`, `training_need` on `assessment_summary_executive` (executive track) and `reporting_officer_marks`/`reporting_officer_grade`/`reviewing_officer_marks`/`reviewing_officer_grade` on `assessment_attributes_staff_details` (staff track) are taken directly from POST body (`TeamReviewController.php:504-508`, `HierarchyReviewController.php:461-464`) with no server-side recomputation from the underlying per-attribute marks — the server trusts the client's aggregate. Only defaulting logic exists: if `reporting_officer_marks` is empty, staff track defaults to `0` / `'Below Average'` (`HierarchyReviewController.php:461-464`).

**Duplicate-submission guards (replacing model validation):**
- `SelfReviewController::createSelfReview` — blocks a second `self_review_details` row for the same `emp_fkey`+`fin_year` unless prior is `status='Deleted'` (SelfReviewController.php:701-711).
- `HierarchyReviewController::createHierarchyReview` — same pattern against `assessment_attributes_staff_details` where `status != 0` (HierarchyReviewController.php:391-401).
- Bulk variants (`createBulkSelfReview`, `createBulkHierarchyReview`) check **both** tables before allowing a bulk-create for any given employee, and independently require a matching `fin_year` row to exist for the employee's `branch_code` in the `fin_year` table (SelfReviewController.php:1196-1216, HierarchyReviewController.php:908-928).
- `ResignationRequestController::Saverequests` — blocks a new resignation if the employee already has an active `ResignationRequests` row OR an active `Termination` row (ResignationRequestController.php:617-620).

**Workflow/status state machine (executive track, string-valued `self_review_details.status`):**
`New` → `Applied` (employee submits self-review, `is_applied=1`) → `Reporting Person submitted the Appraisal` (`is_reported=1`, requires reporting officer's own submit) → `Reviewing Person submitted the Appraisal` (`is_reviewed=1`, **blocked server-side unless `reporting_officer_status==1`** on the corresponding `assessment_attributes_executive_details` rows — TeamReviewController.php:647-657) → terminal. A "send back" at either reporting or reviewing stage sets `is_rejected=1`, `status` to `Reporting/Reviewing Person Rejected the Appraisal`, resets the corresponding officer's `*_status` flag to 0 on all attribute rows, without deleting the marks already entered (TeamReviewController.php:576-621). `was_rejected_by_reviewer` flag distinguishes rejection-by-reviewer from rejection-by-reporting-officer for downstream UI (TeamReviewController.php:583-587).

**Workflow/status state machine (staff/workmen track, numeric `assessment_attributes_staff_details.status`):** 1=drafted, 2=reported (reporting officer done), 3=reviewed (reviewing officer done, terminal for scoring), 5=newly created/not yet started, 0=soft-deleted. `saveHierarchyReview` branches purely on the numeric `status` value passed from the client combined with whether `current_emp_fkey` matches `reporting_officer` or `reviewing_officer` on the record (HierarchyReviewController.php:483-504) — there is no server-side enforcement here that reviewing can't happen before reporting (unlike the executive track's explicit block at TeamReviewController.php:654), which is an **inconsistency between the two tracks worth flagging for the migration** (staff track trusts client-submitted status more than executive track does).

**Category → track branch point** (confirms prior finding): `SelfReviewController::getEmployeeDetails` resolves `emp_proff.emp_grade → grade.category_fkey → category.category_code`; if `category_code === 'WORK'` the employee is routed to the `hierarchy` (staff/workmen) track, otherwise `employee` (executive) track (SelfReviewController.php:603-615, duplicated in `createBulkSelfReview` at 1233-1241).

**Termination / resignation / F&F chain** (confirms prior finding, with one addition): `ResignationRequestController::Saverequests` inserts a `termination` row directly on employee self-submission (ResignationRequestController.php:630-654) but does **not** flip `emp_details.status`. The actual `status='2'` (resigned) flip only happens in `FullandFinalsettlementController::Terminate` (FullandFinalsettlementController.php:146), which is also reachable independently (HR can terminate an employee without a prior `ResignationRequests` row at all). So there are two entry points into the same `termination` table — self-service resignation and HR-initiated termination — that converge on the same F&F processing (`get_complete`, `leaveadjustment`, `approveencash`), but only the HR-side `Terminate` action actually marks the employee inactive. `ResignationRequestController::withd` (withdrawal) requires **both** an active `ResignationRequests` row and an active `Termination` row to exist before allowing cancellation (ResignationRequestController.php:520-537) — meaning an employee cannot withdraw a resignation until HR has run `Terminate` to create the `termination` record, which is a **process-ordering dependency worth confirming with product** before reimplementing.

**Tenant/company-code hardcoding:** `FullandFinalsettlementController::leaveadjustment` branches column updates specifically for company codes `DEMO`/`KWMT` (FullandFinalsettlementController.php:496-504); `PromoController::promotion` has an allowlist/denylist of ~17 company codes controlling which view template renders (PromoController.php:242-247). These are **tenant-specific behavior baked into code**, not config — flag for the migration's multi-tenancy design.

---

## Dead / legacy cruft summary (flagged, verify before migrating)

- `Controller/SurveyController_bkup_megha.php`, `Controller/FieldSurveyController_bkup.php` — backup copies, not routed.
- `TeamReviewController.php:48-256` — fully commented-out earlier `listrequest()` implementation, superseded by the active one at line 257.
- `FullandFinalsettlementController.php:405-467` — commented-out earlier `leaveadjustment()`, superseded by active version at line 469.
- `ExceptionRuleController.php:306-382` and `:866-1154` — two commented-out earlier versions each of `getAppliedList` and `downloadExceptionExcel`.
- `ExceptionRuleController::applyRule` — guard block at lines 683-701 (duplicate rule+branch+month check) is logically subsumed by the stricter guard immediately following at 702-723 (any rule for that branch+month) — the first guard can never fire; not exactly dead code but functionally redundant.
- `PromoController::chnageType` (typo'd name, PromoController.php:702) — does not mirror its change into `emp_config` the way its sibling `changeDesignation`/`changeDepartment`/`changeBranch` do (commented out at lines 719-723) — inconsistent, verify intended.
- `ResignationRequestController::Empagreed` (line 577-591) — `$arr_form_data['agree'] == 1;` is a no-op comparison, not an assignment; the `agree` field is likely never actually persisted as intended.
- `PerformanceController::savepromotions`-style pattern isn't here, but `PromoController::savepromotions` (line 315-341) references an undefined `$result` variable in its return — will emit a PHP notice/warning and return `null` for `result`.
- Several actions return no output at all on their success path (`approve_selectd`, `savedetails`, `reject_selected`, `grandrequest`, `Empagreed`, `Saverequests` in FullandFinalsettlement/ResignationRequest controllers) — client-side presumably infers success from HTTP 200, but there's no JSON contract to port; migration will need to define one.

---

### 2.11 Dashboards, Notifications & Mail

# Dashboards, Notifications, Mail & Misc Admin — Controller/Model Inventory

Scope: `DashboardController`, `DashboardNewController`, `BusinessDashboardController`, `MailBoxController`, `EventHandlerController`, `InfoController`, `PagesController`, `ActivityController`, `AnalysisController`, `DataUploaderController`, `ApiRequestController`, and their backing models.

All controllers below (except `InfoController`) extend `AppController` (`Controller/AppController.php:34` → `class AppController extends Controller`), which gates access via session (`user_group` 1=admin, 2=employee — `Controller/AppController.php:39-46`). Session-gating inside actions is still inconsistent: most dashboard `index()` actions manually re-check `Session->read('ds') == null` and redirect to login rather than relying on a single AppController hook — legacy pattern, verify migration equivalent (middleware/session check) covers all these entry points.

---

## 1. Controller Action Inventory

### DashboardController.php (`Controller/DashboardController.php`)
Legacy dashboard controller — session-gated in-action (not via component), heavy inline SQL, admin/employee/hierarchy branching duplicated with `DashboardNewController`. **Not the routed entry point** (no explicit route found in `Config/routes.php` — CakePHP default `/Dashboard/index` convention only); superseded by `DashboardNewController` for `/Analytics`. Possible legacy cruft — verify still linked anywhere (menus, other redirects) before retiring.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (default layout) / redirect | Landing dashboard; branches to employee/hierarchy/admin dashboard based on `user_group`/`company_code`; sets employee counts, present-today counts, leave requests, announcements (GLET only), Zoom session info | Controller/DashboardController.php:12 |
| menuAudit($menu) | GET/POST | JSON | Logs a menu-click audit row to `report_audit` | Controller/DashboardController.php:283 |
| hierarchydashboard | internal/GET | HTML (`hierarchydashboard` view) | Hierarchy-scoped dashboard for managers (recursive `emp_proff.attr1` self-joins to build subordinate tree) | Controller/DashboardController.php:298 |
| load_birthdays | GET | sets view vars only (no explicit render call visible in first 735 lines) | Loads upcoming birthdays/anniversaries/reminders/missing-salary-upload list | Controller/DashboardController.php:617 |
| getEvents($emp_fkey) | internal | returns array | Helper: holidays/birthdays/joining-anniversaries in next 30 days | Controller/DashboardController.php:720 |
| empcalendar | GET | (truncated — not fully read; likely JSON via autoRender=false, based on convention seen elsewhere) | Employee calendar data | Controller/DashboardController.php:732 |
| empdashboard | GET | HTML | Employee-view dashboard | Controller/DashboardController.php:785 |
| locationtracking | GET | HTML/JSON | Employee GPS/location tracking view | Controller/DashboardController.php:1144 |
| issuereport($issuedate) | GET | HTML/JSON | Issue report listing | Controller/DashboardController.php:1157 |
| loadmap | GET | HTML/JSON | Map data loader (device/location) | Controller/DashboardController.php:1231 |
| timers | GET | HTML/JSON | Timer widget data | Controller/DashboardController.php:1280 |
| form($day, $day2) | GET | HTML | Form widget | Controller/DashboardController.php:1375 |
| empform($day, $day2) | GET | HTML | Employee form widget | Controller/DashboardController.php:1431 |
| form2($day, $day2) | GET | HTML | Second form variant | Controller/DashboardController.php:1487 |
| general_setting | GET | HTML/JSON | General settings widget | Controller/DashboardController.php:1544 |
| save_emergency_contact($family_key, $settings_fkey) | POST | JSON (likely) | Save emergency contact reminder settings | Controller/DashboardController.php:1564 |
| never_remind_emergency($settings_fkey) | GET/POST | JSON | Dismiss emergency-contact reminder permanently | Controller/DashboardController.php:1593 |
| later_remind_emergency($settings_fkey) | GET/POST | JSON | Snooze emergency-contact reminder | Controller/DashboardController.php:1615 |
| share($customerId, $recipeId) | GET | unclear/likely dead — generic recipe-sharing signature unrelated to HR domain | Possible legacy cruft — verify before migrating; params suggest copy-pasted boilerplate | Controller/DashboardController.php:1635 |
| presenttodayall | GET | JSON/HTML | Present-today (all) listing | Controller/DashboardController.php:1641 |
| presenttoday | GET | JSON/HTML | Present-today listing | Controller/DashboardController.php:1680 |
| absenttoday($mode) | GET | JSON/HTML | Absent-today listing | Controller/DashboardController.php:1756 |
| download | GET | file download | Generic download handler | Controller/DashboardController.php:1887 |
| search($query) | GET | JSON | Employee search | Controller/DashboardController.php:1908 |
| gettodayattendace | GET | JSON | Today's attendance (note: typo in method name — "attendace") | Controller/DashboardController.php:1913 |
| getMenus | internal | returns array | Builds admin menu tree from `hrm_menu`/access rules | Controller/DashboardController.php:1918 |
| listemployeeleaverequestscounts($emp_fkey) | internal | returns array | Leave-request counts | Controller/DashboardController.php:2082 |
| misspunch($emp_fkey) | GET | JSON/HTML | Missed-punch listing | Controller/DashboardController.php:2121 |
| listemployeeleaverequests($emp_fkey) | internal | returns array | Leave-request list | Controller/DashboardController.php:2129 |
| todayattendance | GET | JSON | Today attendance summary | Controller/DashboardController.php:2187 |
| lastmonthattendanceinfo | GET | JSON | Last-month attendance info | Controller/DashboardController.php:2192 |
| thismonthattendanceinfo | GET | JSON | This-month attendance info | Controller/DashboardController.php:2197 |
| listtodayattendance($hierarchy) | GET | JSON (datatable) | Today attendance datatable feed | Controller/DashboardController.php:2202 |
| LocationUpdates($hierarchy) | GET | JSON | Location update feed | Controller/DashboardController.php:2353 |
| EmployeeEvent | GET | JSON/HTML | Employee event feed | Controller/DashboardController.php:2453 |
| listthismonthattendance($hierarchy) | GET | JSON (datatable) | This-month attendance datatable feed | Controller/DashboardController.php:2747 |
| listleaverequests | GET | JSON | Leave request datatable feed | Controller/DashboardController.php:2905 |
| checkpunch($x,$y,$z) | GET/POST | JSON | Punch validation (geofence-style params) | Controller/DashboardController.php:2911 |
| lastpunch | GET | JSON | Last punch info | Controller/DashboardController.php:3076 |
| listlastmonthattendance($hierarchy) | GET | JSON (datatable) | Last-month attendance datatable feed | Controller/DashboardController.php:3099 |
| listemployeemisspunches | internal | returns array | Miss-punch list helper (referenced but commented out in index()) | Controller/DashboardController.php:3253 |
| wish_modal($emp_pkey, $event) | GET | HTML (modal) | Birthday/anniversary wish modal | Controller/DashboardController.php:3287 |
| convertimage | POST | JSON | Image conversion utility (used with wish images) | Controller/DashboardController.php:3300 |
| sendemailtemplatedatabase($emp_pkey, $event, $remark) | POST | JSON | Sends wish/notification email using DB-stored template — **inline PHPMailer instantiation expected** (pattern matches other actions) | Controller/DashboardController.php:3357 |
| sendemailtemplate($emp_pkey, $event, $remark) | POST | JSON | Sends wish/notification email using hardcoded template | Controller/DashboardController.php:3545 |
| mergeImages(...) | internal (private) | image file | Merges background + profile photo for wish cards | Controller/DashboardController.php:4211 |
| createImageFromFile($filePath) | internal (private) | GD resource | Image helper | Controller/DashboardController.php:4347 |
| automation_load_birthdays | GET (likely cron/automation endpoint, unauthenticated risk — verify) | JSON | Automated birthday batch job | Controller/DashboardController.php:4363 |
| getMenusForAbs | internal | returns array | ABSG/company-specific menu variant | Controller/DashboardController.php:4556 |
| sendFormEmail | POST | JSON | Generic form-triggered email — likely another inline PHPMailer instance | Controller/DashboardController.php:4655 |
| dashboard_old | GET | HTML | Explicitly named legacy dashboard — **possible legacy cruft — verify before migrating** | Controller/DashboardController.php:4731 |
| (commented out) sendemailtemplate | — | — | Dead code, commented block | Controller/DashboardController.php:3687 |
| (commented out) UserCredentials | — | — | Dead code, commented block | Controller/DashboardController.php:4950 |

Note: file is 4957 lines; only the first 735 + grep-derived action list were reviewed in full detail. `automation_convertimage($emp_pkey, $event, $remark)` also exists near line 4503 (from grep) — automation counterpart to `convertimage`.

### DashboardNewController.php (`Controller/DashboardNewController.php`)
**This is the actively-maintained dashboard**, routed at `/Analytics` (`Config/routes.php:33`: `Router::connect('/Analytics', array('controller' => 'DashboardNew', 'action' => 'index'));`). `index()` is a single ~1000-line action (Controller/DashboardNewController.php:52-1048) accreting many independent chart/KPI queries (salary trend, ESI/PF coverage, department/designation/branch charts, leave/promotion/expense pending-approval counts, age-group donut, attendance regularization, etc.) attributed to many different contributors by "edited by X on DATE" comments — a strong signal of organic, uncoordinated growth. Recommend decomposing into discrete API endpoints per widget for the Next.js migration rather than porting as one mega-action.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| getScopeFilter (protected) | internal | returns array | Builds hierarchy-scoped employee-id filter for non-admin/non-LNTT users (recently simplified — comment "Simplified by Antigravity on 28-04-2026: Only direct subordinates") | Controller/DashboardNewController.php:16 |
| index | GET | HTML (default layout) + many `$this->set()` JSON-encoded chart payloads embedded in the view | Main dashboard: employee counts, present/absent counts, salary trend chart, ESI/PF/PAN coverage stats, age-group donut, department/designation/branch salary breakdowns, pending leave/promotion/expense/attendance-reg approvals, upcoming birthdays/anniversaries w/ "wished" flag via `Wish` model | Controller/DashboardNewController.php:52 |
| menuAudit($menu) | GET/POST | JSON | Same as DashboardController — logs menu click to report_audit | Controller/DashboardNewController.php:1058 |
| hierarchydashboard | internal/GET | HTML | Hierarchy-scoped dashboard variant | Controller/DashboardNewController.php:1073 |
| load_birthdays | GET | sets view vars | Birthday/anniversary reminders | Controller/DashboardNewController.php:1274 |
| getEvents($emp_fkey) | internal | returns array | Same helper pattern as DashboardController | Controller/DashboardNewController.php:1377 |
| empcalendar | GET | JSON/HTML | Employee calendar | Controller/DashboardNewController.php:1402 |
| empdashboard | GET | HTML | Employee-view dashboard | Controller/DashboardNewController.php:1455 |
| (commented out) locationtracking | — | — | Dead code | Controller/DashboardNewController.php:1814 |
| issuereport($issuedate) | GET | HTML/JSON | Issue report | Controller/DashboardNewController.php:1827 |
| loadmap | GET | HTML/JSON | Map loader | Controller/DashboardNewController.php:1935 |
| timers | GET | HTML/JSON | Timer widget | Controller/DashboardNewController.php:1984 |
| form($day, $day2) | GET | HTML | Form widget | Controller/DashboardNewController.php:2079 |
| empform($day, $day2) | GET | HTML | Employee form widget | Controller/DashboardNewController.php:2147 |
| form2($day, $day2) | GET | HTML | Form variant | Controller/DashboardNewController.php:2203 |
| general_setting | GET | HTML/JSON | Settings widget | Controller/DashboardNewController.php:2260 |
| save_emergency_contact / never_remind_emergency / later_remind_emergency | POST/GET | JSON | Emergency contact reminder flow, same as Dashboard | Controller/DashboardNewController.php:2280, 2309, 2331 |
| share($customerId, $recipeId) | GET | unclear — same suspicious unrelated-domain signature | Possible legacy cruft (copy-paste from DashboardController) — verify before migrating | Controller/DashboardNewController.php:2351 |
| presenttoday | GET | JSON/HTML | Present-today | Controller/DashboardNewController.php:2357 |
| activetoday | GET | JSON/HTML | Active-today (new vs. DashboardController's presenttodayall) | Controller/DashboardNewController.php:2411 |
| absenttoday($mode) | GET | JSON/HTML | Absent-today | Controller/DashboardNewController.php:2482 |
| download | GET | file | Download handler | Controller/DashboardNewController.php:2609 |
| search($query) | GET | JSON | Employee search | Controller/DashboardNewController.php:2630 |
| gettodayattendace | GET | JSON | Today attendance (same typo carried over) | Controller/DashboardNewController.php:2635 |
| getMenus | internal | returns array | Admin menu builder | Controller/DashboardNewController.php:2640 |
| listemployeeleaverequestscounts / misspunch / listemployeeleaverequests | internal/GET | array/JSON | Same helper set as DashboardController | Controller/DashboardNewController.php:2813, 2852, 2860 |
| todayattendance / lastmonthattendanceinfo / thismonthattendanceinfo | GET | JSON | Attendance summaries | Controller/DashboardNewController.php:3012, 3017, 3022 |
| listlastmonthattendance($hierarchy) | GET | JSON (datatable) | Last-month attendance feed | Controller/DashboardNewController.php:3026 |
| listthismonthattendance($hierarchy) | GET | JSON (datatable) | This-month attendance feed | Controller/DashboardNewController.php:3179 |
| LocationUpdates($hierarchy) | GET | JSON | Location feed | Controller/DashboardNewController.php:3338 |
| ajax_locationtracking | GET | JSON | AJAX location tracking (new vs. Dashboard) | Controller/DashboardNewController.php:3439 |
| listtodayattendance($hierarchy) | GET | JSON (datatable) | Today attendance feed | Controller/DashboardNewController.php:3448 |
| listemployeemisspunches | internal | array | Miss-punch helper | Controller/DashboardNewController.php:3605 |
| getMissedAttendance | GET | JSON | New endpoint not present in DashboardController | Controller/DashboardNewController.php:3680 |
| listleaverequests | GET | JSON | Leave request feed | Controller/DashboardNewController.php:3691 |
| checkpunch($x,$y,$z) | GET/POST | JSON | Punch validation | Controller/DashboardNewController.php:3697 |
| lastpunch | GET | JSON | Last punch | Controller/DashboardNewController.php:3862 |
| ajax_today_attendance / ajax_thismonth_attendance / ajax_lastmonth_attendance | GET | JSON | AJAX wrappers (new vs. Dashboard) | Controller/DashboardNewController.php:3884, 3891, 3897 |
| wish_modal($emp_pkey, $event) | GET | HTML (modal) | Wish modal | Controller/DashboardNewController.php:3908 |
| convertimage | POST | JSON | Image conversion | Controller/DashboardNewController.php:3922 |
| sendemailtemplate($emp_pkey, $event, $remark) | POST | JSON | Wish/notification email — **inline PHPMailer expected** | Controller/DashboardNewController.php:3981 |
| sendemailtemplatedatabase($emp_pkey, $event, $remark) | POST | JSON | DB-template email variant | Controller/DashboardNewController.php:4283 |
| automation_load_birthdays | GET/cron | JSON | Automated birthday batch | Controller/DashboardNewController.php:4547 |
| automation_convertimage($emp_pkey, $event, $remark) | GET/cron | JSON | Automated image conversion | Controller/DashboardNewController.php:4687 |
| getHrmMenusForAbs | internal | array | ABS/GLET-specific HRM menu variant | Controller/DashboardNewController.php:4741 |
| sendFormEmail | POST | JSON | Form-triggered email | Controller/DashboardNewController.php:4857 |
| createOverlayImage | POST | JSON/image | Overlay image generator (new vs. Dashboard's mergeImages) | Controller/DashboardNewController.php:4936 |
| promotion | GET | HTML/JSON | Promotion widget entry (new) | Controller/DashboardNewController.php:5092 |
| expense | GET | HTML/JSON | Expense widget entry (new) | Controller/DashboardNewController.php:5139 |
| AttendanceRegularisation | GET | HTML/JSON | Attendance regularization widget (new) | Controller/DashboardNewController.php:5198 |
| Attendanceverification | GET | HTML/JSON | Attendance verification widget (new) | Controller/DashboardNewController.php:5223 |

Note: file is 5251 lines; index() body (lines 52-1048) fully reviewed, remainder of action list derived from grep + spot checks. `index()` contains 3 separate redundant `if ($this->Session->read('ds') == null) { redirect... }` checks (lines 62, 968, 1002) — dead-code duplication within a single action, worth flagging for cleanup during migration.

### BusinessDashboardController.php (`Controller/BusinessDashboardController.php`)
Third, newest dashboard variant — analytics-heavy (Morris.js chart data), fully read (1096 lines). Overlaps heavily with `DashboardNewController::index()` (same age-donut, department/designation/branch salary breakdown queries, copy-pasted almost verbatim) but also exposes a family of dedicated AJAX/JSON drill-down endpoints not present in the other two — this looks like the newest iteration heading toward endpoint-per-widget, useful as the migration blueprint for the Next.js API route shape.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML + JSON-encoded chart vars | Company info, active employee count, admin/employee/hierarchy branching, age-group donut, department/designation/branch stats, salary trend, CTC breakup, coverage stats (PF/ESI/PAN), pending leaves | Controller/BusinessDashboardController.php:8 |
| getCTCBreakup | GET (query: month_year) | JSON | CTC breakup by fixed/variable/employer-contribution for a month; **month_year interpolated into SQL only after regex validation** `preg_match('/^\d{4}-\d{2}$/', ...)` — reasonably guarded | Controller/BusinessDashboardController.php:565 |
| getCTCBreakupByDept | GET (query: month_year) | JSON | CTC breakup by department; same regex-guarded month param | Controller/BusinessDashboardController.php:603 |
| getCTCBreakupByDesignation | GET (query: month_year) | JSON | CTC breakup by designation | Controller/BusinessDashboardController.php:652 |
| getCTCBreakupByBranch | GET (query: month_year) | JSON | CTC breakup by branch | Controller/BusinessDashboardController.php:702 |
| getEmployeeCount | GET (query: month) | JSON | 6-month employee headcount trend; `$month` interpolated into SQL **without regex validation** — potential SQL injection surface, flag for migration (parameterize) | Controller/BusinessDashboardController.php:752 |
| getPresentCount | GET (query: month) | JSON | 6-month present-days trend; `$selectedMonth` interpolated into SQL without validation — same injection concern | Controller/BusinessDashboardController.php:790 |
| getVariableAddition | GET (query: month_year) | JSON | Variable-pay addition trend; `$monthYear` interpolated without validation | Controller/BusinessDashboardController.php:819 |
| getVariableDeduction | GET (query: month_year) | JSON | Variable-pay deduction trend; same pattern | Controller/BusinessDashboardController.php:860 |
| getAbsenceData | GET (query: month) | JSON | Leave/LOP days trend; `$selectedDate` interpolated without validation | Controller/BusinessDashboardController.php:901 |
| highestSalaries | GET (query: month) | HTML fragment (raw `<tr>` markup echoed) | Top-5 salary table fragment; uses parameterized query (`:month`) — safer than siblings | Controller/BusinessDashboardController.php:943 |
| lowestSalaries | GET (query: month) | HTML fragment | Bottom-5 salary table fragment; parameterized query | Controller/BusinessDashboardController.php:987 |
| monthlyCTCChartData | GET (query: month) | JSON | 6-month CTC line chart; uses parameterized query (`:selectedDate`) | Controller/BusinessDashboardController.php:1036 |

**Security note**: `getEmployeeCount`, `getPresentCount`, `getVariableAddition`, `getVariableDeduction`, `getAbsenceData` all interpolate a `month`/`month_year` request-query parameter directly into raw SQL strings without the `preg_match` guard used in the `getCTCBreakup*` siblings (Controller/BusinessDashboardController.php:763,803,831,865,914 use `"...".$month/$selectedMonth/$monthYear."..."` unguarded, vs. line 572's `preg_match('/^\d{4}-\d{2}$/', $monthYear)` guard). Flag as SQL-injection risk to fix during the Next.js port (use parameterized queries throughout, as already done in `highestSalaries`/`lowestSalaries`/`monthlyCTCChartData`).

### MailBoxController.php (`Controller/MailBoxController.php`) — fully read
Nearly empty shell despite the "MailBox" name — no actual mailbox/inbox logic implemented.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (default view, empty body) | Landing view, no logic | Controller/MailBoxController.php:54 |
| Template | GET | HTML (empty body) | Stub, no logic | Controller/MailBoxController.php:59 |

Possible legacy cruft — verify whether this controller is used anywhere (routes/menus) before migrating; as written it does nothing beyond rendering blank views. `$uses = array('UserCredentials','CompanyContactInfo','Banks')` declared but never referenced in either action.

### EventHandlerController.php (`Controller/EventHandlerController.php`) — fully read
Also a near-empty shell.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (empty body) | Landing view, no logic implemented | Controller/EventHandlerController.php:58 |

Possible legacy cruft — verify usage; `$uses` declares 13 models (CentralControl, Family, passport, qualifcations, history, EmployeeTaxTransactions, EmpTaxSalTrans, FinancialYear, UserCredentials, EmployeeDetails, Designation, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, TaxHead, EmployeeCTC) none of which are used in the single empty action — strongly suggests this was scaffolded and abandoned, or logic was moved elsewhere and this file was never cleaned up.

### InfoController.php (`Controller/InfoController.php`) — fully read, confirmed
**Confirmed security issue.** `class infoController extends Controller` (Controller/InfoController.php:36) — extends the **bare CakePHP `Controller`**, not `AppController`, so it skips the `AppController::beforeFilter` session/auth gate entirely (Controller/AppController.php:39-46 never runs for this controller).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | raw `phpinfo()` HTML dump | Dumps full PHP configuration (versions, loaded extensions, paths, `disable_functions`, sometimes env vars) with **no authentication** | Controller/InfoController.php:59-67, `phpinfo()` call exactly at **Controller/InfoController.php:65** |

**SECURITY ISSUE — confirmed exact line**: `echo phpinfo();` at Controller/InfoController.php:65, reachable at `/info/index` (or `/info`) by any unauthenticated visitor since the controller extends bare `Controller` (line 36) instead of `AppController`. This must be removed entirely before/during migration — do not port this endpoint. Also note despite extending bare `Controller`, the file still declares `$components = array('LoginManagement', 'Session', 'Email')` and `$uses` with 7 models (Controller/InfoController.php:52-53) that are never referenced in the single `index()` action — dead declarations from an apparent copy-paste of another controller's header.

### PagesController.php (`Controller/PagesController.php`) — fully read
Extends `LoginAppController` (not `AppController`) — a separate base class used for pre-login/cookie-based "remember me" flows.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| display(...$path) | GET | HTML (renders arbitrary view path) or redirect | CakePHP static-page renderer, repurposed to also handle cookie-based auto-login: reads `$_COOKIE['user_id']`/`password`/`userGroup`, calls `LoginManagement->verifyAdminLogin()` or `verifyEmployeeLogin()`, and redirects into Dashboard/hierarchy/employee dashboard on success; falls through to rendering `views/pages/<path>` (company-branding layout switch based on subdomain) if no valid cookie | Controller/PagesController.php:48 |

Note: this action mixes unrelated concerns (generic CakePHP page renderer + cookie auto-login flow) — worth splitting into a dedicated auth-cookie endpoint during migration rather than porting as a "static pages" controller. Cookie-based credential storage (`$_COOKIE['password']`) is itself a security smell to flag separately (plaintext/reversible password in a cookie, per line 50/53/60).

### ActivityController.php (`Controller/ActivityController.php`) — fully read
Task/activity-tracking module (not literally a "dashboard" but an admin misc feature per scope).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML | Activity board landing view; loads status list, project list, employee list, top-level activity ids scoped to session emp | Controller/ActivityController.php:59 |
| report | GET | HTML | Same data load as index, separate report view | Controller/ActivityController.php:84 |
| laodReoport() [sic] | POST | sets view var (partial render) | Filtered activity report by type/employee/project/date range (note misspelled method name) | Controller/ActivityController.php:109 |
| projects | GET | HTML | Projects listing view | Controller/ActivityController.php:137 |
| addproject | GET/POST | HTML | Add/edit project form (loads existing record if `$_REQUEST['id']`) | Controller/ActivityController.php:157 |
| add | GET/POST | HTML | Add/edit activity form incl. subtasks and creator lookup | Controller/ActivityController.php:170 |
| save2 | POST | JSON | Saves an `ActivityProjects` record | Controller/ActivityController.php:204 |
| save | POST | JSON | Saves `Activity` record; handles task transfer-to reassignment and subtask creation; **sends notification email via `sendMail()`** | Controller/ActivityController.php:217 |
| sendMail($toEmail, $toEmail2, $htmlcontent, $subject) | internal | none (echo suppressed, no return) | **Inline PHPMailer instance with hardcoded SMTP creds**: Host `smtp.zoho.in`, Username `noreply@mypayrollmaster.online`, Password `@Password90#` (Controller/ActivityController.php:367-370). Note the actual `$mail->Send()`/return call is commented out (lines 383-389) — **this action currently builds and populates the mail object but never sends it — dead code / broken notification, verify before migrating** | Controller/ActivityController.php:350 |
| getTemplate($body) | internal | returns HTML string | Large inline HTML email template (Unbounce-style, ~290 lines of markup) | Controller/ActivityController.php:392 |
| listData | POST | JSON (datatable) | Activity datatable feed w/ status-badge HTML embedded in JSON payload | Controller/ActivityController.php:685 |
| listProjectsData | POST | JSON (datatable) | Project datatable feed | Controller/ActivityController.php:803 |
| deleteRow | POST | JSON | Soft-delete (status=0) activities by id list | Controller/ActivityController.php:846 |
| deleteProject | POST | JSON | Soft-delete projects by id list | Controller/ActivityController.php:861 |
| deleteLogTime | POST | JSON | Soft-delete a single log-time entry | Controller/ActivityController.php:876 |

**Security/reuse note**: confirms prior finding — another inline PHPMailer instantiation with hardcoded credentials, distinct from the ones found in `DataUploaderController` (below) and elsewhere. Credentials differ across files (`smtp.zoho.in`/`@Password90#` here vs. `smtp.zoho.com`/`welcome123` in DataUploaderController), meaning there are at least two different Zoho mail accounts hardcoded in plaintext across the codebase.

### AnalysisController.php (`Controller/AnalysisController.php`) — fully read
Data-quality/duplication-check utility controller.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML | Employee list landing view | Controller/AnalysisController.php:53 |
| duplication_founds | GET | HTML fragment (raw echoed `<div>/<table>`) | Detects duplicate department/designation/branch codes and echoes Bootstrap alert HTML directly | Controller/AnalysisController.php:60 |
| salary_structure_allocate_issues | GET | HTML fragment | Checks for salary-structure setup issues; **contains malformed/placeholder SQL** — queries reference undefined PHP variables `Pemp_fkey`, `vsalary_head_item_fkey`, `Pemp_structure_id` directly inside SQL strings (not interpolated, literal identifiers) at Controller/AnalysisController.php:107-118 — this code cannot function as written; likely dead/never-finished code. **Possible legacy cruft — verify before migrating**, do not port as-is | Controller/AnalysisController.php:103 |
| payroll($emp_pkey) | GET | HTML fragment | Checks a specific employee's salary structure assignment + duplicate designation/branch checks | Controller/AnalysisController.php:175 |

### DataUploaderController.php (`Controller/DataUploaderController.php`) — largest file (2997 lines), action list via grep + spot-read of key sections
Primarily an employee master-data CRUD/bulk-upload controller (setup wizard, Excel import/export, promotions, tax); included in scope per task list. Given size, full action-by-action code review was not exhaustive — action list and two notable email methods were verified directly.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| index | GET | HTML (`index` or `setup` view, autoRender=false with explicit render) | Admin vs employee landing; admin sees active count + missing-professional-details list + branch/designation combos; employee routed to own `setup()` | Controller/DataUploaderController.php:58 |
| employeesunder | GET | HTML | Subordinate-scoped employee listing setup | Controller/DataUploaderController.php:87 |
| checkProff | internal | array | Finds employees missing `emp_proff` record | Controller/DataUploaderController.php:129 |
| getstages($site_pkey) | GET | HTML `<option>` fragment | Branch-filtered employee dropdown options | Controller/DataUploaderController.php:136 |
| updateesi($site_pkey) | GET | none/no output | Bulk-updates ESI/PF/UAN from a staging table `update_esi` | Controller/DataUploaderController.php:161 |
| getautohierarchycompletions | GET | JSON | Autocomplete for hierarchy assignment | Controller/DataUploaderController.php:181 |
| getautocompletions | GET | JSON | Employee autocomplete | Controller/DataUploaderController.php:222 |
| getautocompletions_superior | GET | JSON | Superior/manager autocomplete | Controller/DataUploaderController.php:271 |
| listemployees | POST | JSON (datatable) | Paginated employee list (bootstrap-table style) | Controller/DataUploaderController.php:328 |
| jsons($branch) | GET | JSON | select2-style branch-filtered employee list | Controller/DataUploaderController.php:429 |
| jsonss($emp) | GET | JSON | select2-style employee list excluding one emp | Controller/DataUploaderController.php:461 |
| setup($emp_pkey) | GET | HTML | Employee add/edit master form (loads personal, professional, tax, dropdowns) | Controller/DataUploaderController.php:495 |
| showtaxheaddetail($emp_pkey, $tax_heads_fkey) | GET | HTML | Tax head detail sub-form (via `requestAction` to Taxation controller) | Controller/DataUploaderController.php:618 |
| loadEmpDetails / loadEmpProfDetails | internal | sets view vars | Personal/professional detail loaders | Controller/DataUploaderController.php:634, 646 |
| Finyear | GET | none (autoRender false, empty) | Stub | Controller/DataUploaderController.php:692 |
| promotion($emp_fkey) | GET | HTML | Promotion form | Controller/DataUploaderController.php:696 |
| promotion_home | GET | HTML (empty body) | Stub landing | Controller/DataUploaderController.php:779 |
| empprofdetails($emp_pkey) | GET | HTML | Professional-details-only sub-form; **note: references undefined `$emp_fkey` instead of the `$emp_pkey` parameter** at line 787 (`$this->set("emp_pkey",$emp_fkey);`) — likely bug, verify before migrating | Controller/DataUploaderController.php:784 |
| emptaxationdetails($emp_pkey) | GET | HTML (empty body) | Stub | Controller/DataUploaderController.php:837 |
| savepromotions | POST | JSON | Saves a promotion record; **references undefined `$result` variable** in the return statement (line 866: `'result' => $result` never assigned) — bug, verify | Controller/DataUploaderController.php:841 |
| approvepromotion($emp_fkey) | GET | HTML | Promotion approval form | Controller/DataUploaderController.php:869 |
| saveemployeesetupnew | POST | JSON | Main employee save (personal + professional + user credentials), branches on device vs. manual emp-id generation | Controller/DataUploaderController.php:956 |
| restsave($emp_pkey) | internal | — | Rollback/cleanup helper referenced on save failure | Controller/DataUploaderController.php:1155 |
| saveemployeesetup | POST | JSON | Older/alternate save variant (naming suggests superseded by `saveemployeesetupnew`) — possible legacy cruft, verify which is actually wired to the current form | Controller/DataUploaderController.php:1169 |
| jsons_getemps($branch) | GET | JSON | Another employee-list-for-combo variant | Controller/DataUploaderController.php:1294 |
| saveconfigs | POST | JSON | Saves employee config (hierarchy policy etc.) | Controller/DataUploaderController.php:1341 |
| downloadempdataformat | GET | file (Excel) | Downloads bulk-upload template | Controller/DataUploaderController.php:1515 |
| mailsend($empname) | internal | none | **Inline PHPMailer, hardcoded creds**: Host `smtp.zoho.com`, Username `info@mypayrollmaster.in`, Password `welcome123` (Controller/DataUploaderController.php:1731-1734); also references an **undefined `$database` variable** in the Subject line (line 1742) — bug. `SMTPDebug = 2` left enabled (verbose debug output) — should not ship to production | Controller/DataUploaderController.php:1725 |
| uploadandsaveempdetails($emp_branch) | POST | JSON | Bulk Excel employee import | Controller/DataUploaderController.php:1755 |
| getcurrentemployeekey | GET | JSON | Returns session emp key | Controller/DataUploaderController.php:2016 |
| downloadempctcformat(...) | GET | file (Excel) | CTC upload template download | Controller/DataUploaderController.php:2031 |
| uploadandsaveempctc($ctcuploadtype) | POST | JSON | Bulk CTC import | Controller/DataUploaderController.php:2155 |
| form | GET | HTML (empty body) | Stub | Controller/DataUploaderController.php:2347 |
| employeesave | POST | JSON (likely, not fully verified) | Employee save variant #3 | Controller/DataUploaderController.php:2374 |
| employeelist | GET | HTML/JSON | Employee list view | Controller/DataUploaderController.php:2403 |
| ctcupload | GET | HTML (empty body) | Stub | Controller/DataUploaderController.php:2485 |
| deleteEmployees | POST | JSON | Bulk soft-delete employees | Controller/DataUploaderController.php:2494 |
| deleteEmp | POST | JSON | Single soft-delete employee | Controller/DataUploaderController.php:2509 |
| addqualification / addfamily / passport | GET | HTML (empty bodies) | Stub sub-form entry points | Controller/DataUploaderController.php:2523, 2526, 2529 |
| savefamily / savepassport / savequalifications | POST | JSON (likely) | Save handlers for family/passport/qualification sub-records | Controller/DataUploaderController.php:2532, 2540, 2548 |
| history($emp_pkey) | GET | HTML (empty body) | Stub | Controller/DataUploaderController.php:2559 |
| savehistory | POST | JSON (likely) | Save employment history | Controller/DataUploaderController.php:2563 |
| lstfamilies($emp_pkey) | GET | JSON (likely) | List family/nominee records | Controller/DataUploaderController.php:2573 |
| listhistory($emp_pkey) | GET | JSON (likely) | List history records | Controller/DataUploaderController.php:2592 |
| getusers($emp_fkey) | GET | JSON (likely) | User credential lookup | Controller/DataUploaderController.php:2614 |
| listqualifications($emp_pkey) | GET | JSON (likely) | List qualifications | Controller/DataUploaderController.php:2643 |
| listpassports($emp_pkey) | GET | JSON (likely) | List passports | Controller/DataUploaderController.php:2660 |
| deletequal / deletepassports / deletehist / deletefdetails | POST | JSON (likely) | Soft-delete handlers for sub-records | Controller/DataUploaderController.php:2677, 2686, 2695, 2705 |
| mark_nominee($pkey, $emp) | POST | JSON (likely) | Marks a family member as nominee | Controller/DataUploaderController.php:2716 |
| showimportresponse(...) | GET | HTML (likely) | Import result summary view | Controller/DataUploaderController.php:2730 |
| sendpasswordemail($userid) | GET/POST | none directly (mail send) | **Inline PHPMailer, hardcoded creds**: same `smtp.zoho.com` / `info@mypayrollmaster.in` / `welcome123` as `mailsend()` above (Controller/DataUploaderController.php:2769-2772); also contains a **broken CakePHP-in-string bug** — `$mail->AddAttachment('<?php echo $this->webroot; ?>')` at line 2777 embeds a literal PHP tag as a string value rather than actual `$this->webroot`, meaning the attachment path is broken/non-functional | Controller/DataUploaderController.php:2754 |
| (commented out) updateSalStructureDistributionFn | — | — | Dead code | Controller/DataUploaderController.php:2968 |

**Summary for DataUploaderController**: at least 2 more inline-PHPMailer-with-hardcoded-credentials instances (`mailsend`, `sendpasswordemail`), both using the same `smtp.zoho.com` / `info@mypayrollmaster.in` / `welcome123` account — a third distinct hardcoded credential set (alongside ActivityController's `smtp.zoho.in`/`@Password90#` and the pattern noted in prior passes). Multiple apparent bugs found (`$database` undefined, `$result` undefined, `$emp_fkey` vs `$emp_pkey` mismatch, broken PHP-tag-as-string attachment path) suggest this controller has accumulated unreviewed changes; recommend a full line-by-line pass before porting any of its save/upload logic into the Next.js API.

### ApiRequestController.php (`Controller/ApiRequestController.php`) — fully read
Menu/autocomplete API surface for the frontend.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| getMenus | GET | JSON (`echo json_encode`) or empty (when `$context != 'main'` and no `$root`, falls through with no output — dead branch) | Builds nested menu tree (admin `Menu`/`hrm_menu` vs employee `EmployeeMenu`) for ExtJS-style tree UI | Controller/ApiRequestController.php:51 |
| listemployeesforhierarchy | GET | **`return json_encode(...)` instead of `echo`** — CakePHP actions that `return` a string do auto-render it as the view/response in some configs, but combined with the trailing `$this->autoRender=FALSE;` placed *after* the `return` (unreachable, Controller/ApiRequestController.php:186) this is inconsistent with the `echo`+`autoRender=FALSE` pattern used elsewhere in the same file — verify actual runtime output shape before porting | Employee list scoped to caller's subordinates (`EmployeeProfessionalDetails.attr1`) | Controller/ApiRequestController.php:151 |
| listemployees | GET | same `return json_encode(...)` pattern, unreachable `autoRender` note doesn't even appear here (dead `//`-commented lines instead) | Full employee list with branch-code scoping special-cased for `VGFS`/`VSFS` company codes | Controller/ApiRequestController.php:188 |
| listdepartmentsforcombo | GET | JSON (echo) | Department combo list | Controller/ApiRequestController.php:242 |
| listbranchesforcombo | GET | JSON (echo) | Branch combo list | Controller/ApiRequestController.php:258 |
| listdepartments1forcombo | GET | JSON (echo) | **Byte-for-byte duplicate** of `listdepartmentsforcombo` (identical query/logic) — possible legacy cruft, dead duplicate, verify before migrating | Controller/ApiRequestController.php:274 |
| listdepartments1sforcombo | GET | JSON (echo) | **Third byte-for-byte duplicate** of the same department-combo logic | Controller/ApiRequestController.php:290 |

**Reuse note**: `listdepartmentsforcombo`, `listdepartments1forcombo`, and `listdepartments1sforcombo` are identical copy-pasted code (3x duplication of the same department list query) — collapse to one endpoint in the Next.js port.

---

## 2. Model Validation & Business Rules

Models backing this cluster (declared via `$uses` in the controllers above) were checked for `$validate` arrays, `beforeSave`/`afterSave` hooks, and other business-rule logic.

| Model | File | `$validate`? | Hooks? | Notes |
|---|---|---|---|---|
| EmployeeDetails | Model/EmployeeDetails.php | **None** | None | Primary key `emp_pkey`, table `emp_details`; declares a `virtualFields` computed `emp_name` concat and a commented-out `hasOne` association (dead code, Model/EmployeeDetails.php:22-26). No validation despite being the most heavily queried model across all dashboards. |
| Menu | Model/Menu.php | **None** | None | Table `hrm_menu`, primary key `menu_id`. Pure data-access shell. |
| ReportAudit | Model/ReportAudit.php | **None** | None | Table `report_audit`, backs `menuAudit()` action's audit-log inserts. No validation on what gets logged. |
| Activity | Model/Activity.php | **None** | None | Table `activity_track`, primary key `activity_track_pkey`. Backs ActivityController save/listData. |
| ActivityProjects | Model/ActivityProjects.php | **None** | None | Table `activity_projects`. |
| CentralControl | Model/CentralControl.php | **None** | None | Table `central_control`, explicitly pinned to `useDbConfig = 'controldb'` (the shared control/central DB, distinct from per-tenant company DBs) — confirms dual-DB architecture noted in project memory. |
| Banks | Model/Banks.php | **None** | None | Table `bank`. |
| CompanyContactInfo | Model/CompanyContactInfo.php | **None** | None | Table `comp_contact_info` — backs company name/logo/address lookups used throughout the dashboards. |
| UserCredentials | Model/UserCredentials.php | **None** | One custom method: `linkempDeviceanddatabase($outputParameter)` — builds and executes a raw stored-procedure call `CALL Linkemp_deviceanddatabase(...)` (Model/UserCredentials.php:20-30) by concatenating parameters directly into the query string with no escaping. **Flag**: if any `$outputParameter` element originates from user input, this is a SQL injection vector; verify callers before migrating. | Table `user_credentials`, primary key `user_pkey`. |
| Wish, EmailContent | *(no Model file exists)* | N/A | N/A | Declared in `$uses` (e.g. `Controller/DashboardController.php:9`, `Controller/DashboardNewController.php:10`) but **no `Model/Wish.php` or `Model/EmailContent.php` file exists** in the codebase (confirmed via directory listing). CakePHP 2.x will silently instantiate these as bare `AppModel` instances bound to an Inflector-guessed table name (`wishes`, `email_contents`) — meaning there is no dedicated model class, no validation, and no documented schema for these; the table names are inferred purely by convention. Verify actual table names in the DB schema before writing the Next.js equivalent. |
| AppModel (base) | Model/AppModel.php | **None** | Empty constructor passthrough only (Model/AppModel.php:35-37) | Confirms there is no global validation layer applied to any model in this app — every model is validation-free unless it declares its own `$validate`, and none of the models in this cluster do. |

**Conclusion**: As anticipated for a dashboard/reporting cluster, every backing model reviewed is read/write-heavy with **zero declared validation rules** (no `$validate` arrays, no `beforeValidate`/`beforeSave` hooks found in any of the 9 model files read). All data integrity checks happen ad-hoc in controller code (e.g., `preg_match` on `month_year` in `BusinessDashboardController::getCTCBreakup`, inconsistently applied to sibling actions). This is a significant migration consideration: the Next.js API layer will need to introduce its own validation (e.g., zod schemas) since none exists to carry over from the model layer.

---

## 3. DashboardManagementComponent.php — file/class mismatch confirmed

**Confirmed and precisely verified**: `Controller/Component/DashboardManagementComponent.php` declares `class LoginManagementComponent extends Component` (Controller/Component/DashboardManagementComponent.php:2) — it contains **none** of the code its filename implies. Full content is a login-verification component with two public methods:

- `verifyAdminLogin($username, $password)` (line 27) — queries `CentralUserCredentials` for `user_id`/`password` match with `access_allowed = 'y'`, looks up the company's DB via `CentralControl`, then re-queries `UserCredentials` against the tenant DB, and on success writes `login_user_id`, `user_group=1`, `user_name`, `company_key` to session. **Note**: password comparison is done via raw SQL string concatenation (`"password ='".$password."'"`, line 29/40) — plaintext password comparison in SQL, not a hash comparison; also a classic SQL-injection-shaped pattern (mitigated only if framework-level escaping is applied elsewhere, unverified here).
- `verifyEmployeeLogin($username, $password)` (line 60) — same pattern for employee login (`user_group=2`), company code derived from first 4 chars of username (`substr($username,0,4)`, line 62).

This is the exact component `PagesController::display()` calls as `$this->LoginManagement->verifyAdminLogin(...)` / `verifyEmployeeLogin(...)` (Controller/PagesController.php:53, 60) — meaning the component `$this->LoginManagement` in `PagesController`'s `$components = array('LoginManagement','Session')` (Controller/PagesController.php:40) actually resolves to this same file (CakePHP resolves component names to `{Name}Component.php`, so `'LoginManagement'` → `LoginManagementComponent.php`, which is presumably a **separate, correctly-named file elsewhere** — this file at the `DashboardManagement` path is a duplicate/orphaned copy of that same class, mis-filed under the wrong name). Practical implication: any controller that declares `$components = array('DashboardManagement')` expecting dashboard-building helper methods will get login-verification methods instead and will fatal-error (`Call to undefined method`) on any dashboard-specific call — worth grepping the full codebase for `$this->DashboardManagement->` usages before the port to see if this bug is latent/unused or actively broken in production. Not grepped in this pass (out of assigned scope — flagging for the controllers/components report if such usages exist elsewhere).

---

## 4. Cross-cutting findings (confirms/extends prior-pass notes)

1. **Three parallel dashboard controllers confirmed**: `DashboardController` (legacy, not routed), `DashboardNewController` (routed at `/Analytics`, actively maintained per the density of dated "edited by" comments through late 2025/2026), `BusinessDashboardController` (newest, most granular AJAX-endpoint-per-widget design — closest to the target Next.js API shape). All three duplicate the admin/employee/hierarchy branching logic, the age-group donut query, and the department/designation/branch salary breakdown queries nearly verbatim. Recommend building the Next.js dashboard API against `BusinessDashboardController`'s endpoint decomposition, backfilling any KPIs unique to `DashboardNewController::index()` (e.g., ESI/PF/PAN coverage stats, pending-approval counts) as additional discrete endpoints.
2. **InfoController security issue reconfirmed with exact citation**: `Controller/InfoController.php:36` (`class infoController extends Controller`) + `Controller/InfoController.php:65` (`echo phpinfo();`) — unauthenticated PHP info disclosure. Must not be ported; flag for immediate remediation independent of migration timeline.
3. **DashboardManagementComponent mismatch reconfirmed with exact citation**: `Controller/Component/DashboardManagementComponent.php:2` — file is 100% `LoginManagementComponent` code, zero dashboard logic.
4. **No centralized email service reconfirmed and extended**: found 3 additional inline PHPMailer instantiations with hardcoded credentials in this cluster alone (`ActivityController::sendMail` — Controller/ActivityController.php:350, `DataUploaderController::mailsend` — Controller/DataUploaderController.php:1725, `DataUploaderController::sendpasswordemail` — Controller/DataUploaderController.php:2754), using at least 2 distinct hardcoded credential sets, plus several more expected-but-not-fully-verified instances in `DashboardController`/`DashboardNewController`'s `sendemailtemplate*`/`sendFormEmail` actions (pattern strongly implied by naming and by every other mail-sending action found in this pass, though not individually opened given file size). Confirms the "reuse a shared email service" recommendation for the migration.
5. **New finding — inconsistent SQL parameterization within a single controller**: `BusinessDashboardController` shows both safe (parameterized `:month`/`:selectedDate`) and unsafe (raw string interpolation of `$month`/`$monthYear` request-query values) query patterns side-by-side, with no consistent guard. Flag as a SQL-injection audit item for the migration, specifically `getEmployeeCount`, `getPresentCount`, `getVariableAddition`, `getVariableDeduction`, `getAbsenceData`.
6. **New finding — apparent live bugs in DataUploaderController**: undefined-variable references (`$database` in `mailsend()`, `$result` in `savepromotions()`, `$emp_fkey`/`$emp_pkey` mix-up in `empprofdetails()`) and a broken PHP-tag-as-literal-string attachment path in `sendpasswordemail()`. These are not simply "legacy but working" — they appear to be non-functional as written and should not be ported literally; the intended behavior needs to be inferred from context or confirmed with the client before the equivalent Next.js logic is written.
7. **New finding — triplicated dead code**: `ApiRequestController::listdepartmentsforcombo` / `listdepartments1forcombo` / `listdepartments1sforcombo` are three identical copies of the same department-combo endpoint (Controller/ApiRequestController.php:242, 274, 290) — collapse to one in the port.
8. **Model layer confirmed validation-free**: all 9 models backing this cluster (EmployeeDetails, Menu, ReportAudit, Activity, ActivityProjects, CentralControl, Banks, CompanyContactInfo, UserCredentials) plus the base AppModel declare zero `$validate` rules and zero lifecycle hooks beyond a passthrough constructor. `Wish` and `EmailContent`, used throughout the dashboards, have no Model file at all (relies on CakePHP's implicit AppModel-by-convention fallback) — their actual table schema should be confirmed directly against the database before the Next.js data layer is designed.

---

### 2.12 Statutory & Access Admin

# Statutory & Access Admin Controllers — Backend Report

Scope: `Controller/StatutoryRegistersController.php`, `Controller/StatutoryUploadsController.php`,
`Controller/AccessController.php`, `Controller/UserAccessController.php` (class name `UseraccessController`),
`Controller/UserController.php`, `Controller/UserCredentialsController.php`, and their backing models
(`Model/UserCredentials.php`, `Model/CentralUserCredentials.php`, `Model/Useraccess.php`, `Model/MobileUserCredentials.php`).

All paths below are relative to `D:\Projects\RIZOMigration\legacy`.

---

## Auth baseline (context for every table below)

`Controller/AppController.php:39-46` — `beforeFilter()` only checks that session `user_group` is `1` or `2`
(any authenticated user, admin or employee); it does **not** check role/group for any specific controller or
action. Every controller in this report extends `AppController` (except `AccessController`, which extends
`Controller` directly and bypasses this check entirely — see below). This means **role-based authorization
(admin vs employee) is not enforced by any base-controller hook** — each controller/action must do its own
`$user_group` check, and most of the ones below don't.

---

## 1. Controller Action Inventory

### AccessController.php (SSO bridge — does NOT extend AppController, extends `Controller`; class named `AccessController`, `$name = 'Leaveapi'`)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `checkLogin()` | GET (reads `$_GET`) | redirect-only | SSO bridge: takes `fullstring`, `company_code`, `emp_company_id`, `emp_email`, `code` from querystring, calls stored function `single_signon_fn` via raw `mysql_query` (deprecated ext/mysql, not mysqli/PDO), looks up `mob_user_credentials.password` in the resolved company DB, then **redirects to `https://v1.mypayrollmaster.online/Site/login?user_id=...&password=...` with the plaintext password in the URL querystring** | Controller/AccessController.php:59-87, redirect at line 80 |

**Security issues (confirmed):**
- Password shipped in URL querystring on redirect — `Controller/AccessController.php:80`. URLs land in browser history, server access logs, referrer headers, and any proxy/CDN logs.
- `checkLogin()` builds the `single_signon_fn(...)` call by directly concatenating unescaped `$_GET` values into a raw SQL string — `Controller/AccessController.php:69`. Classic SQL injection surface.
- Hardcoded DB credentials in source — `Controller/AccessController.php:67` (`mpm_cntrl_usr` / `MyPyR01@Cntr1#LB`).
- Uses the deprecated/removed `mysql_*` extension (`mysql_connect`, `mysql_select_db`, `mysql_query`, `mysql_fetch_assoc`) — `Controller/AccessController.php:67-79` — will not run at all on PHP 7+; this whole controller is effectively unreachable/dead on any modern PHP runtime and is either already broken in production or is running on an old PHP 5.x box. Flag as **possible legacy cruft — verify before migrating** (confirm whether this endpoint is still live/used by any partner integration before deciding to port it).
- `AccessController` does not extend `AppController`, so it does not get the `beforeFilter()` session check either — it is intentionally a pre-auth/cross-app bridge, consistent with its SSO purpose, but that also means there is zero session-based protection on this action; its only "auth" is the DB-side `single_signon_fn` call.

---

### UserAccessController.php (class `UseraccessController`, extends `AppController`) — per-user/per-menu permission admin screen

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index()` | GET | HTML view | Landing page: employee picker + menu tree (parent/child `emp_menu`), fetches plan_id from control DB | UserAccessController.php:53-129 |
| `insec($emp_pkey=0)` | GET | HTML view (element, no autoRender=false) | Loads per-employee menu tree + current `user_access` rows + `payro_priv` for the access-grant screen | UserAccessController.php:131-147 |
| `indexnew()` | GET | HTML view | Newer landing page variant with avatar, add-on "features" list, branch list | UserAccessController.php:149-237 |
| `saveFeatureAccess()` | POST (`$this->request->data`) | JSON | Grants/revokes an add-on "feature" for a user; branch-wise or hierarchy-wise; also runs an ad-hoc `ALTER TABLE ... ADD COLUMN` inside a try/catch as a migration "safeguard" | UserAccessController.php:239-299 |
| `saveBranchAccess()` | POST (`$this->request->data`) | JSON | Sets branch-level access rows for a user/feature | UserAccessController.php:301-341 |
| `getFeatureBranches($user_fkey=0,$feature_id=0)` | GET (route params) | JSON | Returns branches + hierarchy flag for a user/feature | UserAccessController.php:343-360 |
| `insecs($emp_pkey=0)` | GET | HTML view | Newer variant of `insec()`: default-menu tree + add-on features scoped by plan/company_addons + branch access | UserAccessController.php:362-458 |
| `autoAllocateDefault($emp_fkey=0)` | GET/POST (route param) | JSON | If user has zero `user_access` rows, bulk-inserts default menus | UserAccessController.php:494-538 (dead/commented-out earlier version at 459-492) |
| `save($emp_pkey='', $s='')` | GET/POST (route params) | JSON (echoed, no explicit content-type) | Grants menu access: `$s=='All'` bulk-grants every active menu; otherwise grants a single menu and auto-enables its parent menu | UserAccessController.php:539-623 |
| `addDefault($emp_fkey=0)` | GET/POST (route param) | JSON | Calls stored proc `insert_default_menu` | UserAccessController.php:625-640 |
| `resetDefault($emp_fkey=0)` | GET/POST (route param) | JSON | Deactivates all current access then calls `insert_default_menu` | UserAccessController.php:642-658 |
| `deletemens($emp_fkey=0)` | GET/POST (route param) | JSON | Bulk-deactivates all `user_access` rows for a user | UserAccessController.php:660-675 |
| `listuseraccess()` | POST (`$this->request->data`) | JSON | Paginated/sortable listing of menus with per-user `accessallow` flag; **uses `$page`/`$limit` variables that are never assigned in this method** (`$offset = ($page - 1) * $limit;` at line 686) — will throw an undefined-variable notice / behave incorrectly (limit will be treated as `0`/null) | UserAccessController.php:677-738, bug at line 686 |
| `delete($emp_pkey='', $s='')` | GET/POST (route params) | JSON | Revokes menu access (single or bulk "All"); for single-menu revoke, also auto-disables the parent if no sibling submenus remain active | UserAccessController.php:835-923 (dead/commented-out earlier version at 740-801) |
| `Employee($user_pkey=0, $DD='')` | GET/POST (route params) | JSON-ish (no explicit echo; only `save()` is called, no response body) | Toggles a special `menu_id='#'` "Employee dashboard" flag on/off based on `$DD=='EMPLOYEE'` | UserAccessController.php:925-956 (near-duplicate of dead code at 803-833) |
| `admin($user_pkey=0, $DD='')` | GET/POST (route params) | none (no echo) | Toggles a special `menu_id='0'` "admin" flag on/off based on `$DD=='ADMINS'` | UserAccessController.php:957-987 |
| `admin2($user_pkey=0, $DD='')` | GET/POST (route params) | JSON | Sets `emp_proff.payro_priv` 0/1; **compares `$DD` against bareword constants `id`/`ADMINSS`** (`if ($DD == id)` / `elseif ($DD == ADMINSS)`) rather than string literals — these are undefined PHP constants; on PHP<8 this degenerates to comparing against the constant's own name string (with a deprecation warning), on PHP 8+ it's a fatal `Error: Undefined constant`. Likely broken/dead code — **possible legacy cruft, verify before migrating** | UserAccessController.php:988-1004, bug at lines 993/995 |
| `get()` | POST (`$_POST['emp_id']`) | JSON | Returns Y/N whether a user has the "Dashboard/Hierarchy" menu active | UserAccessController.php:1006-1025 |
| `deleteuser($user_access_pkey=0)` | GET/POST (route param) | JSON | Soft-deletes (`status=0`) a `user_access` row via `updateAll` | UserAccessController.php:1027-1036 |
| `saveuseraccess()` | POST (`$this->request->data`) | JSON | Upserts a single `user_access` row (menu_id/user_pkey/active) | UserAccessController.php:1038-1071 |

**Security issue (confirmed, per prior-pass finding):** None of the above actions check `$this->Session->read('user_group') == 1` (admin) before granting/revoking permissions or elevating `payro_priv`/admin flags. `AppController::beforeFilter()` (Controller/AppController.php:43-46) only requires the session to have *some* valid `user_group` (1 or 2); it does not gate by role. Confirmed: **no server-side enforcement that only admins can call `save()`, `delete()`, `admin()`, `saveuseraccess()`, `deleteuser()`, `resetDefault()`, etc.** Any authenticated employee (`user_group=2`) session that can reach these URLs directly (bypassing the view-layer menu that hides the admin screen) can grant themselves or any other `emp_pkey`/`user_pkey` arbitrary menu access, including the special admin flag (`admin($user_pkey, 'ADMINS')` at UserAccessController.php:957-987) and `payro_priv` (payroll privilege) elevation via `admin2()` (UserAccessController.php:988-1004, though gated by the broken bareword-constant bug noted above). This is a real vertical-privilege-escalation risk to flag explicitly for the Next.js migration — the new API routes must add explicit admin-role middleware on every one of these endpoints.

Also note: heavy raw SQL string interpolation of route/POST params throughout this controller (e.g. `$emp_pkey`, `$s`, `$user_fkey`, `$feature_id`, `$branch_code` are concatenated directly into `query()` calls at, e.g., lines 136,140,143,280-328,371-380,384,435-439,500-504,510-523,553-620,645,653,663,858-920) — widespread SQL-injection surface throughout, not sanitized/parameterized (contrast with the one parameterized query at lines 68-71 which uses `['emp_pkey' => ...]` binding — showing the codebase knows how to do it safely but does so inconsistently).

---

### UserController.php (extends `AppController`) — self-service "my own profile" (not admin user management)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `saveavatar()` | POST (multipart, `$_FILES['avatarfile']`) | none (autoRender false, no echo) | Uploads/validates avatar image (MIME sniffed via fileinfo), saves path to `CentralUserCredentials` (admin) or `user_credentials` (employee) depending on `user_group` | UserController.php:59-146 |
| `checkpassvalidation($param='')` | POST/GET (`$_REQUEST['oldpass']`) | **broken** — uses `return 1`/`return 0` instead of `echo`/JSON, with `autoRender=false`; the caller gets an empty response body, not the 1/0 value | UserController.php:148-168 |
| `change_image($param='')` | — | empty stub, does nothing | UserController.php:170-173 |
| `load_basic_details($param='')` | GET | HTML view (sets `arr_data`) | Loads current employee's profile fields for display | UserController.php:175-226 |
| `savebasics_form()` | GET | HTML view | Loads profile fields for the edit form | UserController.php:229-278 |
| `loadImage($param='')` | GET | HTML view | Loads profile + avatar for display | UserController.php:280-332 |
| `profile()` | GET | HTML view (`render('profile')` for admin, `render('user')` for employee) | Main "my profile" page, branches on `user_group` | UserController.php:334-414 |
| `savePassword()` | POST (`$_REQUEST['password']`/`password1`/`password2`) | JSON | Self-service password change: verifies current password (SHA-hash comparison), hashes new password via `Security::hash(...)`, saves to `CentralUserCredentials` (admin) or `UserCredentials` (employee), and **also writes the new password in plaintext to `mob_user_credentials`** via raw query | UserController.php:416-494, plaintext mirror at line 483 |
| `savebasics($param='')` | POST (`$this->request->data`) | JSON | Saves arbitrary `EmployeeDetails` fields (no field allow-list) | UserController.php:496-515 |
| `saveNames()` | POST (`$this->request->data`) | JSON | Updates first/last name. **Confirmed bug**: for `user_group == 2` (employee) branch, references undefined variables `$password_new` (UserController.php:549) and `$password1` (UserController.php:552) — neither is set anywhere in this method (they only exist in the unrelated `savePassword()` method above). `$arr_data['password'] = $password_new;` then gets passed to `$this->UserCredentials->save($arr_data)` at line 550. Because `$password_new` is undefined, PHP evaluates it as `null`/empty string, so **every employee name edit blanks out that employee's `user_credentials.password` field**, effectively locking them out until an admin resets the password. Also `$this->MobileUserCredentials->query("UPDATE mob_user_credentials set password = '$password1' ...")` at line 552 runs with `$password1` undefined, blanking the mobile password mirror too. This method never even echoes a real `$resp` for that branch (the `$resp` array from the `user_group==1` branch is unset/empty for employees) — confirmed exact bug, matches prior-pass finding. | UserController.php:517-556, bug at lines 549/552 |

Notable: `checkpassvalidation()` looks unreachable/broken as written (uses `return` instead of `echo` under `autoRender=false`) — **possible legacy cruft, verify before migrating**.

---

### UserCredentialsController.php (extends `AppController`) — admin user-credential management (the "real" admin screen for provisioning logins)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index()` | GET | HTML view (`layout=false`) | Landing page for the credentials grid | UserCredentialsController.php:59-66 |
| `getusers()` | GET (`$this->request->query`) | JSON | Employee-name/company-id typeahead search | UserCredentialsController.php:68-116 |
| `listCredentials()` | POST (`$this->request->data`) | JSON | Paginated/sortable grid of `user_credentials` joined to employee/professional details, with branch-restriction logic for `GLET`/`ABSG` company codes | UserCredentialsController.php:118-237 |
| `form($id=0)` | GET (`$_REQUEST['id']`) | HTML view (`layout=null`) | Loads a single user's credential + mobile-credential record for the edit form | UserCredentialsController.php:239-267 |
| `bulkUpload($id=0)` | GET | HTML view (`layout=null`) | Renders bulk-upload form with branch combo list | UserCredentialsController.php:269-277 |
| `deleteuser($bank_id=0)` | GET/POST (route param) | JSON | Soft-deletes an employee (`emp_details.status=0`) via raw query | UserCredentialsController.php:279-288 |
| `resetmobiles()` | POST (`$this->request->data`) | JSON | Clears mobile device binding (`securitycode`, `macid`, `imei`) for a user in `mob_user_credentials` | UserCredentialsController.php:290-297 |
| `companyHistory_add($payloadData)` | internal (called from `save()`), also technically reachable as a controller action | mixed (returns raw HTTP response string) | Fires a `curl` POST to an external third-party API (`myportalapi.mypayrollmaster.online`) with **hardcoded credentials in HTTP headers** (`username: profileadmin` / `password: admin&*()`) | UserCredentialsController.php:299-325, hardcoded creds at 314-316 |
| `empDataThirdparty_update($data)` | internal (called from `save()`) | mixed (returns raw HTTP response string) | Pushes full employee PII (name, DOB, bank details, PAN, Aadhaar name, disability info, etc.) to the same third-party API, again with hardcoded creds | UserCredentialsController.php:327-470, hardcoded creds at 459-461 |
| `saveBulkAccess()` | POST (`$this->request->data`) | JSON | Bulk-provisions first-time login access for a whole branch via stored proc `user_access_firstime_only`, then bulk-emails everyone in that branch the **same shared plaintext password** | UserCredentialsController.php:472-519 |
| `sendAccessMail($password1='', $addressess)` | internal (called from `saveBulkAccess()`) | boolean | Sends the branch-wide password-reset email (plaintext password embedded in HTML body) via PHPMailer over SMTP with **hardcoded SMTP credentials**, and **silently BCCs `projects@greatleap.tech` (a vendor address) on every send** | UserCredentialsController.php:521-745; hardcoded SMTP creds at 546-547; silent BCC at line 551; password embedded in email body at line 643 |
| `save($bank_id=0)` | POST (`$this->request->data`) | JSON | Core "create/update user credential" action: hashes new password for `UserCredentials` (control table) via `Security::hash()`, but **also stores the plaintext password verbatim into `mob_user_credentials.password`** (`$arr_mobile_data['password'] = $arr_form_data['pasword'];` — note the typo `pasword`, matches the form field name), then emails the plaintext password to the user via `sendpasswordemail()` | UserCredentialsController.php:747-927; plaintext mirror at line 790; hashing at line 767 |
| `sendpasswordemail($email, $userid, $password1, $name)` | internal (called from `save()`) | boolean | Sends the single-user password-reset email (plaintext password embedded in HTML body) via PHPMailer, **hardcoded SMTP credentials**, and **silently BCCs `projects@greatleap.tech` on every reset** in addition to BCC'ing the user's own address | UserCredentialsController.php:938-1160; hardcoded SMTP creds at 964-965; silent vendor BCC at line 973; password embedded at line 1068 |
| `chekPassword($oldpassword='')` | GET/POST (`$_REQUEST`) | JSON | **Dead/no-op**: reads `$_REQUEST['oldpassword']` and `Session->read('emp_fkey')` into local vars but never uses them for validation — just echoes back the raw `$oldpassword` route-param argument it was called with. Does not actually verify anything. **Possible legacy cruft — verify before migrating.** | UserCredentialsController.php:1161-1169 |
| `savedata()` | GET | JSON | Hardcoded, single-employee (`user_id = 'GLET100132'`) debug/test action that re-runs the third-party sync for one specific employee. Clearly a leftover dev/test endpoint pointing at a specific tenant's employee ID — **possible legacy cruft / should not ship to production, verify before migrating.** | UserCredentialsController.php:1170-1196, hardcoded ID at line 1175 |

**Confirmed security issues in `UserCredentialsController.php`:**
1. Plaintext password mirrored into `mob_user_credentials.password` on every credential save — `UserCredentialsController.php:790` (single-user `save()`) — confirms prior-pass finding.
2. Every password reset/provisioning email — both the bulk branch-wide flow (`sendAccessMail`) and the single-user flow (`sendpasswordemail`) — silently BCCs a vendor address `projects@greatleap.tech`:
   - `UserCredentialsController.php:551` (`sendAccessMail`)
   - `UserCredentialsController.php:973` (`sendpasswordemail`)
   Both confirm the prior-pass finding, with **two** distinct locations (bulk + single-user flows), not just one.
3. Hardcoded SMTP credentials in source (`smtp.zoho.in`, `noreply@mypayrollmaster.online` / `@Password90#`) — `UserCredentialsController.php:546-547` and `964-965`.
4. Hardcoded third-party API credentials — `UserCredentialsController.php:314-316` and `459-461`.
5. Passwords transmitted in plaintext via email body — `UserCredentialsController.php:643` and `1068`.

---

### StatutoryRegistersController.php (extends `AppController`) — HR statutory report generator (Wage Sheet / Muster Roll / Service Record)

Not an access/credential controller — included per scope for completeness of the inventory. It is a large report-building controller (2687 lines) that reads `UserCredentials`/`EmployeeDetails`/payroll tables to render/export statutory registers as HTML, PDF (via HTML2PDF) or Excel (via PHPExcel). No password/credential logic; `UserCredentials`/`CentralUserCredentials` are used purely as read-only joins for employee name/user_id display, not for authentication.

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `hrreports()` | GET | HTML view | Landing page listing available report types, filtered by company code (`HRBL` sees only Muster Roll) | StatutoryRegistersController.php:62-80 |
| `changereporttype($type='')` | GET/POST (route param) | HTML view (`render('showreport')`) or plain text | Loads report-criteria list for the chosen report type | StatutoryRegistersController.php:86-117 |
| `addreportcriteria($type='', $newindex='', $str_currentcriterias='')` | GET/POST (route params) | HTML view (`render('showcriteria')`) or empty string | AJAX-loads remaining criteria options for a report builder row | StatutoryRegistersController.php:123-136 |
| `loadcriteriaitems($index, $str_criteria='')` | GET/POST (route params) | HTML view (`render('loadcriteriaitems')`) or empty string | AJAX-loads a criteria-item picker widget, validated against `App::objects('model')` via `_modelExists()` | StatutoryRegistersController.php:142-158 |
| `listcriteriaitems($str_criteria='')` | POST (`$this->request->data`) | JSON | Returns typeahead/list items for a given criteria model (Employees or Units), with branch restriction for `GLET`/`ABSG` | StatutoryRegistersController.php:160-252 |
| `downloadHistory($type, $mode)` | GET/POST (route params, `$_REQUEST`) | none (autoRender false, no echo) | Audit-logs report downloads to `ReportAudit` | StatutoryRegistersController.php:253-360 |
| `generatereport($type='', $mode='')` | GET/POST (route params) | delegates (PDF/Excel/HTML per sub-method) | Dispatches to `Generatewage`, `generatemusterrollreport`, or `generateServiceRecordReport`, then logs via `downloadHistory` | StatutoryRegistersController.php:362-384 |
| `listemployeefields()` | GET | JSON | Returns field-name/heading metadata for report-builder UI (from `Vendor/ReportFields/EmployeeInformationFields.php`) | StatutoryRegistersController.php:386-418 |
| `_modelExists($modelName)` (private) | n/a | n/a | Helper validating a model name against `App::objects('model')` | StatutoryRegistersController.php:420-424 |
| `Generatewage($mode)` (private) | n/a | HTML/PDF/Excel (mixed, built inline) | Builds the Wage Sheet register (Form XI) from `$_REQUEST` criteria; heavy raw SQL with `$_REQUEST` values concatenated directly into query strings | StatutoryRegistersController.php:430-1668 (~1200 lines) |
| `generatemusterrollreport($mode)` (private) | n/a | HTML/PDF/Excel | Builds the Muster Roll register | StatutoryRegistersController.php:1669-2293 |
| `generateServiceRecordReport($mode)` (private) | n/a | HTML/PDF/Excel | Builds the Service Record register (added per code comment "Edited by Akshay on 5-2-2026") | StatutoryRegistersController.php:2294-2687 |

Note: `Generatewage()`, `generatemusterrollreport()`, `generateServiceRecordReport()` all build SQL by directly interpolating `$_REQUEST`/`$arr_form_data` values (e.g. `$leavepolicygroupid`, `$from`) into query strings without parameterization — SQL-injection surface, consistent with the pattern seen elsewhere in this codebase; flagged for completeness even though out of the "access admin" theme.

---

### StatutoryUploadsController.php (extends `AppController`) — statutory export utility (PF/ESI-style flat-file exports)

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `index()` | GET | empty stub (renders default view if one exists, no logic) | StatutoryUploadsController.php:55-57 |
| `statutory()` | GET/POST (`$_REQUEST`) | binary file download (`.xls`, `Content-Disposition: attachment`) | Builds an ESI/PF-style statutory export for employees with a non-blank `esi` code, writes to a temp file on disk, streams it via `readfile()`, then `unlink()`s it | StatutoryUploadsController.php:59-277 |
| `pf()` | GET | empty stub | StatutoryUploadsController.php:278-280 |
| `pfdownload($from='')` | GET (route param) | HTML view (sets `arr_salary_for_template`, no explicit render/download despite the name) | Builds the same PF/ESI dataset for all active (`status='1'`) employees but only `set()`s it for a view — unlike `statutory()`, it never actually streams a file; **name suggests a download that never happens — possible legacy cruft/incomplete refactor, verify before migrating** | StatutoryUploadsController.php:282-354 |

Uses raw SQL string interpolation from `$_REQUEST`/route params throughout (`statutory()`/`pfdownload()`), same SQL-injection-surface pattern as above.

---

## 2. Model Validation & Business Rules

### `Model/UserCredentials.php` — company/tenant DB, `user_credentials` table

- `App::uses('AppModel', 'Model')`; `$primaryKey = 'user_pkey'`; `$useTable = 'user_credentials'` — Model/UserCredentials.php:9-18.
- **No `$validate` array** — no field-level validation rules (no password complexity/length/format rules) are defined on this model at all.
- **No `beforeSave`/`beforeValidate`/`afterSave` hooks** — password hashing is not centralized in the model; it is done ad hoc in controller code via `Security::hash($password, null, true)` (e.g. `UserController.php:454-455`, `UserCredentialsController.php:767`). This means any code path that saves a password without remembering to call `Security::hash()` first will store it in plaintext — and indeed `UserCredentialsController.php:790` does exactly that into the sibling `mob_user_credentials` table.
- One custom method: `linkempDeviceanddatabase($outputParameter)` — builds and runs a raw `CALL Linkemp_deviceanddatabase(...)` stored-procedure invocation by string-concatenating the parameter array (no placeholders) — Model/UserCredentials.php:20-30. SQL-injection surface if `$outputParameter` ever contains untrusted input (no callers found in the six controllers reviewed here, so likely used elsewhere in the app — flag for a broader audit).
- No explicit `$belongsTo`/`$hasMany` associations declared — all employee/professional-detail joins are done ad hoc via raw `'joins' => array(...)` clauses in controller `find()` calls (e.g. `UserCredentialsController.php:90-98, 176-190, 196-210`), not through CakePHP associations.

### `Model/CentralUserCredentials.php` — **control DB** (`mypayrol_control_db`), also maps to `user_credentials` table but in the control DB

- `$useDbConfig = 'controldb'` (Model/CentralUserCredentials.php:17) — explicitly pinned to the control datasource (confirms this model is the **admin/control-plane user table**, separate from the per-company `UserCredentials` model above even though both use table name `user_credentials`).
- `$primaryKey = 'user_pkey'`, `$tablePrefix = ''` — Model/CentralUserCredentials.php:14-16.
- **No `$validate` array, no `beforeSave`/hooks** — same as `UserCredentials`; password hashing happens in controller code (`UserController.php:454-467` for admin password change; `UserController.php:530-537` for admin name change) using `Security::hash($password, null, true)`.
- Note controllers frequently call `$this->CentralUserCredentials->setDataSource('controldb')` explicitly before querying (e.g. `UserAccessController.php:110, 208, 229, 389`, `UserController.php:117, 343, 460, 528`) even though the model already declares `$useDbConfig = 'controldb'` in its class definition — redundant but harmless; suggests some historical uncertainty/defensiveness in the codebase about whether `useDbConfig` sticks across requests.

### `Model/Useraccess.php` — company/tenant DB, `user_access` table (menu/feature grant table)

- `$primaryKey = 'user_access_pkey'`; `$useTable = 'user_access'` — Model/Useraccess.php:23-26.
- **No `$validate` array, no hooks, no associations** — a bare CRUD model. All grant/revoke business logic (parent-menu auto-enable, sibling-menu auto-disable, "All" bulk grant, default-menu allocation) lives entirely in `UserAccessController.php`, not in the model.

### `Model/MobileUserCredentials.php` — company/tenant DB, `mob_user_credentials` table (mobile app login credentials, plaintext password column)

- `$primaryKey = 'user_pkey'`; `$useTable = 'mob_user_credentials'` — Model/MobileUserCredentials.php:14-16.
- **No `$validate` array, no hooks.** This table's `password` column is written in plaintext by multiple controller flows: `UserCredentialsController.php:790` (`save()`), `UserController.php:483` (`savePassword()`, via raw `UPDATE mob_user_credentials set password = '$password1'`), and (buggily, with an undefined variable) `UserController.php:552` (`saveNames()`).

### Password hashing algorithm (confirmed)

- Every password-hashing call site found in these controllers uses CakePHP's `Security::hash($value, null, true)` — e.g. `UserController.php:158, 454-455`, `UserCredentialsController.php:481, 767`. The second parameter (`$type`) is passed as `null`, which in CakePHP 2.x `Lib/Cake/Utility/Security.php` falls back to `Security::$hashType`. No `Security.hash` type override was found anywhere under `Config/` (grep for `Security::hash|hashType|Security\.hash` in `Config/` returned no matches), and CakePHP 2.x's default `Security::$hashType` is `'sha1'`. This **confirms the prior broader-research finding that passwords are hashed with plain SHA-1** (unsalted beyond CakePHP's default behavior, no bcrypt/Argon2, no per-user salt configured in `Config/`). SHA-1 is a fast, unsalted-by-default, cryptographically broken-for-collision-resistance hash — inappropriate for password storage by 2020s standards. This is a hard requirement to fix during the Next.js/API migration (rehash to bcrypt/argon2 on next login, or forced reset).

---

## Summary of confirmed findings (cross-referencing the prior-pass claims in the task brief)

1. **`AccessController.php:80`** — SSO bridge redirects with `user_id` and plaintext `password` in the URL querystring. Confirmed. Also confirmed unescaped `$_GET` values built into a raw SQL string at `AccessController.php:69`, hardcoded DB credentials at `AccessController.php:67`, and use of the deprecated `mysql_*` API (won't run on PHP 7+) — flagged as **possible legacy cruft — verify whether this endpoint is even reachable in the current production PHP version** before deciding how/whether to port it.
2. **`UserAccessController.php`** grant/revoke flow (`save`, `delete`, `saveuseraccess`, `deleteuser`, `admin`, `resetDefault`, etc.) has **no server-side admin-only enforcement** — confirmed via `AppController::beforeFilter()` (Controller/AppController.php:39-46), which only checks that *a* valid session (`user_group` 1 or 2) exists, never that it's specifically `1` (admin). Every write action in `UserAccessController.php` is reachable by an authenticated employee session that knows/guesses the URL.
3. **`UserCredentialsController.php`** mirrors plaintext passwords into `mob_user_credentials` (`UserCredentialsController.php:790`) and emails them with a silent BCC to `projects@greatleap.tech` on every reset — confirmed at **two** locations: `UserCredentialsController.php:551` (bulk `sendAccessMail`) and `UserCredentialsController.php:973` (single-user `sendpasswordemail`).
4. **`UserController.php::saveNames()`** — confirmed exact bug: references undefined `$password_new` (line 549) and `$password1` (line 552) in the employee (`user_group==2`) branch, nulling out the employee's `user_credentials.password` (and blanking the `mob_user_credentials` mirror) on every name edit. Matches the prior-pass finding exactly.

## Additional issues found during this pass (not in the prior brief)

- `UserAccessController.php::admin2()` compares against undefined bareword constants `id`/`ADMINSS` instead of string literals (lines 993, 995) — likely broken/dead on modern PHP.
- `UserAccessController.php::listuseraccess()` uses `$page`/`$limit` without ever assigning them (line 686) — broken pagination.
- `UserCredentialsController.php::chekPassword()` (line 1161) and `::savedata()` (line 1170, hardcoded to employee `GLET100132`) look like dead/test code left in production.
- `UserController.php::checkpassvalidation()` uses `return` instead of `echo` under `autoRender=false` (lines 148-168) — caller gets no usable response body.
- `StatutoryUploadsController.php::pfdownload()` (line 282) never actually streams a file despite its name — looks like an incomplete refactor of `statutory()`.
- Hardcoded third-party API credentials (`UserCredentialsController.php:314-316, 459-461`) and hardcoded SMTP credentials (`UserCredentialsController.php:546-547, 964-965`) — should move to environment/secret config in the Next.js migration regardless of the above bugs.
- Widespread SQL-injection surface via raw string interpolation of request/session values into `->query()` calls across `UserAccessController.php`, `UserCredentialsController.php`, `StatutoryRegistersController.php`, and `StatutoryUploadsController.php` — the codebase does know how to use parameterized queries (one example at `UserAccessController.php:68-71`) but does so inconsistently; every raw `query()` call site should be treated as a migration-blocking item requiring parameterization in the new API layer.

---

### 2.13 Timesheet & Full Controller Coverage Check

# Timesheet Controller & Uncovered Controllers — Backend Report

Source: `D:\Projects\RIZOMigration\legacy\Controller\TimesheetController.php`
(base class: `AppController` — normal session-authenticated controller, `user_group` 1=admin / 2=employee)

## 1. Controller Action Inventory

`TimesheetController` has no `$uses` model of its own name; it wires in `EmployeeDetails, Units, AttendanceRegister, DbConfig, EditPunches, LeaveRequests` plus the `MasterdataManagement` component, and does almost all data access via raw `->query()` SQL (including calls to stored procedures/functions: `emp_detail_att_reg`, `att_start_end_fn`, `time_duration_check`, `time_duration_check_multishift`, `insert_update_att_reg`, `leave_transaction_prc`, `leave_balance_inthe_month_fn`). Every action swaps the DB connection per-request via `useDbConfig = $this->Session->read('ds')` (multi-tenant dual-DB pattern).

| Action | HTTP method(s) | Response type | Purpose | path:line |
|---|---|---|---|---|
| `registerbook($monthdd, $emp_pkey, $branch)` | GET | HTML (view) | Main monthly attendance "register book" grid for a branch/employee: computes month attendance-window dates via `att_start_end_fn`, calls `emp_detail_att_reg` proc to (re)generate attendance rows, then per-employee aggregates present/LOP/week-off/holiday/leave-taken counts and joining date; sets `editable` flag based on whether an `attendance_register` (verified) row already exists for that month. | `Controller/TimesheetController.php:55-247` |
| `empregisterbook($monthdd, $emp_pkey, $branch)` | GET | HTML (view) / early `return false` | Employee-scoped register view. Looks up the employee's shift (`working_day_time_procedures`), branches to `time_duration_check_multishift` or `time_duration_check` SQL function depending on `is_multiple_days`, then lists `emp_detail_timeattandance` rows for the month. Returns `FALSE`/`die()` (no proper HTTP response) if shift lookup fails — dead-end control flow. | `Controller/TimesheetController.php:251-317` |
| `getbranches()` | GET/AJAX | JSON (autoRender off) | Combo/autocomplete data source: branch list scoped to current employee via `MasterdataManagement::getBranchesListForCombo`. | `Controller/TimesheetController.php:320-343` |
| `jsons($branch, $resigned)` | GET/AJAX (reads `$_REQUEST['q']`) | JSON (autoRender off) | Employee search/autocomplete combo, filterable by branch, resigned status, and free-text name/company-id search (`$q` from raw `$_REQUEST`, string-concatenated into SQL — SQL-injection risk to flag). | `Controller/TimesheetController.php:345-421` |
| `filter()` | GET | HTML (view), but **references undefined `$monthdd`** | Same pattern as `registerbook`/`empregisterbook` but the local `$month = $monthdd;` at line 429 uses an undefined variable (no method parameter) — this will throw a PHP notice and effectively always compute against a null month. Appears to be an abandoned/superseded copy of the other two register actions. **Possible legacy cruft — verify before migrating** (likely dead/unused code path; no other action calls it and the `$monthdd` bug suggests it was never exercised post-edit). | `Controller/TimesheetController.php:423-457` |
| `index()` | GET | HTML (view) | Landing page for the module: builds branch dropdown (with special-cased logic for company codes `DEMO`/`BKHS`/`GLET` restricting to branches reachable from the current employee's hierarchy) and active employee list. | `Controller/TimesheetController.php:459-497` |
| `empindex()` | GET | HTML (view) | Employee-facing variant of `index()` — same branch/employee list build but without the payroll-privilege / company-code branching. | `Controller/TimesheetController.php:499-515` |
| `showregister()` | GET | HTML (view) | Loads the "AttendanceRegister" grid (denormalized day-per-column table) for the logged-in employee's reportees, plus a legend/status-color map (`arr_registerentries`) used by the view. | `Controller/TimesheetController.php:517-583` |
| `showregistertab($verified = 0)` | GET | HTML (view) | Tab variant of the register grid, computing the attendance-period start/end dates from company `DbConfig.attendance_date` cutoff; `$verified` selects verified vs unverified tab. | `Controller/TimesheetController.php:585-651` |
| `listregisterentries()` | GET/AJAX (reads `$_REQUEST[rows/page/branch/employee/month]`) | JSON (autoRender off) | Server-side-paginated datatable feed of **unverified** (`isdelete="Y"`) `attendance_register` rows, with days-present/leave/holiday computed by counting cell values. | `Controller/TimesheetController.php:658-731` |
| `listverifiedregisterentries()` | GET/AJAX | JSON (autoRender off) | Same as above but for **verified** (`isdelete="N"`) rows. Near-duplicate of `listregisterentries()` — candidate for consolidation. | `Controller/TimesheetController.php:733-806` |
| `processregisterentries()` | POST (reads `$_POST[branch/month]`) | JSON (autoRender off) | Triggers `insert_update_att_reg` stored proc (via `AttendanceRegister::insertUpdateAttendanceRegisterProc`) to (re)build register rows for a branch/month; always returns `success=1` regardless of proc outcome (proc errors are swallowed). | `Controller/TimesheetController.php:808-821` |
| `verifyregisterentries($registerid = 0)` | POST (reads `$this->request->data['ids']`) | JSON (autoRender off) | Marks one or many `attendance_register` rows as verified (`isdelete='N'`) after checking each is fully punched via `checkifregistercanverify()`. | `Controller/TimesheetController.php:823-857` |
| `loadattendanceregisterheader()` | POST | JSON (autoRender off) | Builds the dynamic day-column header definition (`FIELD1..FIELDn`) for the register datatable based on the company's attendance-period start/end days. | `Controller/TimesheetController.php:859-887` |
| `checkifregistercanverify($registerid = 0, $arr_requestdata = [])` | Internal (POST when called directly) | JSON string (autoRender off) — also called internally as a plain function from `verifyregisterentries()` and `submitregisterentry()` | Validates that every day-field in the register row for the given date range is punched; returns JSON array of missing (mis-punched) dates. | `Controller/TimesheetController.php:890-931` |
| `updateregisterentries($registerid = 0)` | GET | HTML (view) | Renders the edit form for missing punch dates on a register row (decodes `dates` JSON from request data). | `Controller/TimesheetController.php:933-942` |
| `submitregisterentry()` | POST (reads dynamic `hid-*`/`reg-date-*` fields) | JSON (autoRender off) / no response on some branches | Submits corrected register field values, updates `attendance_register`, then re-checks completeness and auto-verifies via `verifyregisterentries()` if complete; if `registerid=0` or `count=0`, some branches produce **no output at all** (missing `else` when `$count == 0`). **Possible legacy cruft/bug — verify before migrating.** | `Controller/TimesheetController.php:944-986` |
| `createDateRange($startDate, $endDate, $format = "Y-m-d")` | Internal helper (public, but never called elsewhere in this file) | N/A (returns array) | Generates inclusive date range using `DateTime`/`DatePeriod`. **Dead code — not called anywhere in this controller** (the file uses `createDateRangeArray` instead); possible legacy cruft. | `Controller/TimesheetController.php:995-1009` |
| `createDateRangeArray($strDateFrom, $strDateTo)` | Internal helper | N/A (returns array) | Actual date-range generator used by `checkifregistercanverify()`, via `mktime`. | `Controller/TimesheetController.php:1011-1031` |
| `editPunch($month, $emp_pkey, $edtPkey)` | GET | HTML (view) | Edit-punch form for a single `emp_detail_timeattandance` row: resolves current financial year, computes each leave-head's remaining balance via `leave_balance_inthe_month_fn`, and current `present` status. | `Controller/TimesheetController.php:1034-1074` |
| `bulkipdatestatus()` | POST (reads `$_POST[device_attandance_seq/status/adstatus/monthYear]`) | JSON (autoRender off) | Bulk attendance-status override across employees/dates: blocks edits once the month's register is already verified (`iseditable` check), restricts employees (`user_group==2`) to only editing from today forward, and for `status == 'LOP'` invokes `AddLeave()` to auto-create/approve a leave entry, otherwise writes a status-override row into `emp_detail_status_update`. Note: actual `present`/`leaves` fields on `emp_detail_timeattandance` are **not** updated here — only an audit/override table (`emp_detail_status_update`) is inserted; the commented-out `UPDATE emp_detail_timeattandance` lines suggest this is a deliberate but confusing partial implementation. | `Controller/TimesheetController.php:1076-1165` |
| `checkLeaveExists($att_date, $emp_pkey, $statusType)` | Internal helper (`function`, not `public function` — implicitly public in PHP but not intended as a route) | N/A (returns bool) | Cancels/re-derives an existing approved leave entry for a date when its status is being overridden; calls `leave_transaction_prc` to cancel then re-applies via `AddLeave()` depending on half-day/full-day logic. | `Controller/TimesheetController.php:1166-1238` |
| `AddLeave($head, $day, $emp_fkey, $session)` | Internal helper (public) | N/A (returns bool) | Creates and auto-approves a new `leaveentries` record (via `LeaveRequests->saveAll()` + two calls to `leave_transaction_prc` — one to apply, one to approve) for full/first-half/second-half day sessions; used by `bulkipdatestatus()`/`checkLeaveExists()` to reconcile leave when attendance status changes. | `Controller/TimesheetController.php:1239-1309` |

**Cross-cutting risks worth flagging for migration:**
- Heavy raw SQL string concatenation of request/session values (e.g., `$q`, `$branch`, `$emp_pkey`, `$_POST` fields directly interpolated) throughout — SQL-injection surface that a Next.js/ORM rewrite should close with parameterized queries.
- No CakePHP model-level `$validate` rules anywhere in this controller's chain (see §2) — all business rules live in controller code and MySQL stored procedures/functions (`emp_detail_att_reg`, `att_start_end_fn`, `time_duration_check[_multishift]`, `insert_update_att_reg`, `leave_transaction_prc`, `leave_balance_inthe_month_fn`), which are **not visible in the PHP codebase** and must be pulled from the DB schema/proc definitions separately for a full migration spec.
- `filter()` (undefined `$monthdd`) and `createDateRange()` (unused) are flagged as legacy cruft above.

## 2. Model Validation & Business Rules

None of the models wired into `TimesheetController` (`EmployeeDetails`, `Units`, `AttendanceRegister`, `DbConfig`, `EditPunches`, `LeaveRequests`) define a `$validate` array — this app's CakePHP layer performs no field-level validation; per-tenant multi-DB routing is instead handled ad hoc per-request via `useDbConfig = Session->read('ds')`.

- **`Model/AttendanceRegister.php`** (`Controller/TimesheetController.php` uses this as `$this->AttendanceRegister`) — `useTable = 'attendance_register'`, `primaryKey = 'registerid'`. No validation rules. Custom methods used elsewhere in the app (`salaryProcessPrc`, `calculateSalaryMainPrc`) are unrelated to Timesheet; Timesheet only uses `insertUpdateAttendanceRegisterProc($outputParameter)`, which builds and calls `CALL insert_update_att_reg(...)` — a thin proc wrapper that always `return true` regardless of proc success/failure (error swallowing, no result surfaced to caller). `Model/AttendanceRegister.php:18-45`
- **`Model/EditPunches.php`** — `useTable = 'device_attandance'`, `primaryKey = 'device_attandance_seq'`. No validation, no custom methods; used purely as a `->query()` proxy for raw SQL against `attendance_register`, `emp_detail_timeattandance`, `leaveentries`, `emp_proff`, `working_day_time_procedures`, and `emp_detail_status_update` tables (not actually querying its own `device_attandance` table in most calls — the model is effectively just a DB-connection handle). `Model/EditPunches.php:1-17`
- **`Model/LeaveRequests.php`** — `useTable = 'leaveentries'`, `primaryKey = 'LEAVEENTRYID'`. No validation rules. Custom method `leaveTransactionPrc($outputParameter)` wraps `CALL leave_transaction_prc(...)`; Timesheet calls this stored procedure both directly via `->query()` (bypassing the wrapper, e.g. `checkLeaveExists`, `AddLeave`) and would presumably use the wrapper elsewhere in the app — inconsistent usage pattern to normalize during migration. `Model/LeaveRequests.php:1-45`
- **`EmployeeDetails`, `Units`, `DbConfig`** models are shared across many other clusters (Employee, Company Setup) and were not re-analyzed here since they carry no Timesheet-specific rules; Timesheet uses them only for raw lookups (`emp_details`, `emp_proff`, `branches`, `fin_year`, `salary_head_items`, `db_configs`/`comp_contact_info`) and the `MasterdataManagement` component for branch-combo scoping.
- Effective "business rules" for timesheets live almost entirely in:
  1. Session-derived scoping — `user_group` (1=admin/2=employee) gates which branches/employees are visible (`Controller/TimesheetController.php:107-113, 471-488`), and further restricts employees (`user_group==2`) to only edit attendance from today onward (`Controller/TimesheetController.php:1110-1112`).
  2. The `attendance_register` "verified" lock — once a month's register exists for an employee (`isdelete='N'`), that month's attendance becomes non-editable (`editable` flag, `Controller/TimesheetController.php:155-156, 222`; `iseditable` gate in `bulkipdatestatus`, `Controller/TimesheetController.php:1098-1101`).
  3. Leave/attendance reconciliation logic in `AddLeave`/`checkLeaveExists` — half-day vs full-day leave session codes (1=first half, 2=second half, 3=full day) drive `leaveentries.leave_days` and status transitions through the `leave_transaction_prc` stored procedure.

## 3. Uncovered Controllers

**None.** A fresh `find`/`ls` over `Controller/*.php` (excluding the `Component` subfolder) returned 223 files. Cross-checking every filename against the 13-cluster "already covered" list plus this Timesheet analysis accounts for all of them. The files not literally named in the covered list are all dead/duplicate backup copies of controllers that ARE in the list (same pattern as the explicitly-excluded `UserControllerssss.php`) — confirmed by matching class names / much smaller line counts / date- or editor-suffixed filenames. These are flagged as **possible legacy cruft — verify before migrating** rather than treated as distinct controllers:

- `AttendanceReportsController2018-010.php`, `AttendanceReportsControllerBkup-6-12.php`, `AttendanceReportsControllerBkup.php`, `AttendanceReportsController_Bkup-2018-01-07.php` — dated/"Bkup" backups of `AttendanceReportsController.php`.
- `DashboardController_bkup_jan.php` — backup of `DashboardController.php`.
- `DayTimeProcedureController_nimisha_edited_backup.php`, `DayTimeProcedureControllerbackupnimishas.php` — backups of `DayTimeProcedureController.php`.
- `EditPunchesController_bkup-24-10-2017.php` — backup of `EditPunchesController.php`.
- `EditedReportsController_2018-10.php` — backup of `EditedReportsController.php`.
- `EmployeeAdvanceReportsController_2018-010.php` — dup of `EmployeeAdvanceReportsController.php`.
- `EmployeeController_old_before addnominee.php` — backup of `EmployeeController.php`.
- `EmployeeExpenseReportsController_2018-10.php` — dup of `EmployeeExpenseReportsController.php`.
- `EmployeeLoanReportsController_2018-10.php` — dup of `EmployeeLoanReportsController.php`.
- `FieldSurveyController_bkup.php` — backup of `FieldSurveyController.php`.
- `LeaveRequestControllerNimisha_edited.php` — editor-named backup of `LeaveRequestController.php` (class name is identically `LeaveRequestController`; 3,333 lines vs. the live file's 6,173 and the already-catalogued `LeaveRequestControllerBkups.php`'s 3,377 — confirmed same-class duplicate, not a distinct controller). `Controller/LeaveRequestControllerNimisha_edited.php:1-34`
- `MiscellaniousReportsControllerBKUP.php`, `MiscellaniousReportsController_editedNimisha.php` — backups of `MiscellaniousReportsController.php`.
- `SalaryHeadsControllernimishabackup.php` — backup of `SalaryHeadsController.php`.
- `SalaryReportsController_nimishabackup.php`, `SalaryReportsControllerbkup_nimisha_11_5_19.php` — backups of `SalaryReportsController.php`.
- `SalarySlipReportsController_nimisha.php` — backup of `SalarySlipReportsController.php`.
- `StockReportControllerBkup-01.php`, `StockReportController_bkup_vanguards.php` — backups of `StockReportController.php`.
- `StoreController_bkup_VGFS.php` — backup of `StoreController.php`.
- `SurveyController_bkup_megha.php` — backup of `SurveyController.php`.
- `UniformController_bkup.php` — backup of `UniformController.php`.
- `UserControllerssss.php` — already noted as excluded dead dup in the task prompt.

No controller file exists outside the 13 clusters + base classes + `TimesheetController.php` itself.

---

## 3. The api/ Subsystem

# `api/` Subsystem Report — Legacy My Payroll Master (MPM)

Scope: `D:\Projects\RIZOMigration\legacy\api\` only, treated as an independent subsystem from the CakePHP 2.x main app.

## 0. Critical caveat — source code is missing from this checkout

`api/v1/composer.json` (`api/v1/composer.json:23-31`) declares PSR-4 autoloading rooted at `src/App/` and explicitly force-loads three files:

```
"src/App/Controller/ClientdbController.php",
"src/App/Controller/ApiController.php",
"src/App/Controller/SecurityController.php"
```

`api/v1/index.php:12,17,21,24,27` also `require`s `vendor/autoload.php`, `src/settings.php`, `src/dependencies.php`, `src/middleware.php`, and `src/routes.php`.

**None of `api/v1/src/` or `api/v1/vendor/` exist in this checkout** — verified fresh with a full recursive listing (below). So there are **no route-definition files, no controller source, and no DB-connection code available to read directly**. Everything below about routes/actions is either (a) directly cited from `index.php`/`composer.json`/`.htaccess`/logs, which do exist, or (b) INFERRED from `api/v1/error_log`, `api/v1/error_log_old`, and `api/v1/logs/request*.txt`, which do exist and are large enough to reconstruct behavior with reasonable confidence.

## 1. Structure

Full file listing of `api/` (recursive, confirmed via `find api -type f`):

```
api/v1/.gitignore
api/v1/.htaccess
api/v1/composer.json
api/v1/composer.lock
api/v1/error_log
api/v1/error_log_old
api/v1/index.php
api/v1/logs/request.txt
api/v1/logs/request.txt#06_01_2026
api/v1/logs/request.txt#13_04_2026
api/v1/logs/request.txt#13_04_20269
api/v1/logs/request.txt#bkup13_04_20261
api/v1/logs/request_04-Jun-2026-0130PM.txt
api/v1/logs/request_08-Jun-2026-0130AM.txt
api/v1/phpunit.xml
api/v1/README.md
api/v1/CONTRIBUTING.md
api/v1/1formdesign.png                                       (loose image, not app code)
api/v1/113_7_IMG_20210824_135110.jpg                          (uploaded employee/expense photo)
api/v1/11_1_ei_16600405830577475161186081734399.jpg           (uploaded employee/expense photo)
api/v1/121_1_ei_1668863717398.jpg                             (uploaded employee/expense photo)
api/v1/12_16_IMG_20220804_105626.jpg                          (uploaded employee/expense photo)
api/v1/12_18_IMG_20220412_102609.jpg                          (uploaded employee/expense photo)
api/v1/131_1_IMG_20211108_141904.jpg                          (uploaded employee/expense photo)
api/v1/141_1_IMG_20210601_230230.jpg                          (uploaded employee/expense photo)
api/v1/148_1_IMG_20220703_000914.jpg                          (uploaded employee/expense photo)
api/v1/15_1_ei_16724757179175053219087684725744.jpg           (uploaded employee/expense photo)
api/v1/1618_12_IMG_20201231_120909.jpg                        (uploaded employee/expense photo)
api/v1/161_2_IMG_20220730_093903.jpg                          (uploaded employee/expense photo)
api/v1/162_1_IMG_20220615_091130.jpg                          (uploaded employee/expense photo)
api/v1/176_17_ei_1660138495437973002179407226414.jpg          (uploaded employee/expense photo)
api/v1/193_1_ei_16632082370875064399300163665814.jpg          (uploaded employee/expense photo)
```

There is only **one version folder, `v1`** — no `v2`, `v3`, etc. directories exist on disk. (See §2 for evidence of a `_v2` **action-suffix** convention inside the single `v1` endpoint — that is a different thing from a URL-level API version.)

**Framework**: `slim/slim` `^3.1`, resolved to `slim/slim` **3.8.1** in `api/v1/composer.lock:403-404`, plus `slim/php-view` 2.2.0 and `monolog/monolog` 1.22.1. `api/v1/composer.json:2-18` names the project `mpm/api` ("My Payroll Master API Services"), homepage literally points at `slimphp/Slim-Skeleton`, and `api/v1/README.md:1-21` / `api/v1/CONTRIBUTING.md:1-15` are the **unmodified Slim-Skeleton boilerplate text** — nobody edited them for this project. This is a completely separate PHP micro-framework stack from CakePHP 2.x used by the main app (Controller/Model/View MVC under `Controller/`, `Model/`, `View/` at the repo root, per prior research) — `api/` shares no framework code with the main app.

Composer platform requirement: PHP `>=5.5.0` (`api/v1/composer.json:15`, `composer.lock:1598`), consistent with the same legacy-PHP era as the main CakePHP 2.x app.

`api/v1/.htaccess:9-10` rewrites all non-file requests to `index.php` — classic front-controller pattern, i.e. **every** api/v1 request physically hits `index.php`, which then hands off to Slim's router (`src/routes.php`, missing here).

`api/v1/.gitignore:1-2` ignores `/vendor/` and `/logs/*` (except a `logs/README.md` that doesn't exist in this checkout either) — explains why `vendor/` is absent, but **does not** explain why `src/` is absent, since `src/` is not gitignored. Likely explanation: this checkout/export simply never captured `src/`, or `src/` lived outside version control on the production host. Either way, **the actual route table and controller logic must be sourced from the live server or a different backup**, not from this checkout.

## 2. Endpoints exposed

### Source-level (verified)
Only one HTTP entry point exists in source: `api/v1/index.php` → Slim front controller → `src/routes.php` (missing, so exact registered Slim routes/HTTP verbs are unknown). Given `.htaccess` rewrites everything to `index.php`, and the request logs show no distinguishable path/URI per call (see below), it is very likely the whole app is served from a small number of Slim routes (e.g. a single `POST /` or `POST /api`) that dispatch internally based on a request-body `action` field, rather than classic REST resource routing (`GET /users/1`, etc.). This is an **INFERRED** architectural conclusion, not read from `routes.php`.

### INFERRED from access logs (`api/v1/logs/request*.txt`, `error_log*`)

The request logs (`api/v1/logs/request.txt` and 6 rotated siblings, covering roughly Jan 5 2026, Apr 13 2026, Jun 3–8 2026) do **not** record HTTP method or URL path — each entry is just a PHP `print_r` dump of the POST body (`Request came @ <timestamp>` followed by an `Array(...)` of POST fields, then `Response given @ <timestamp>` with the JSON-like response array). This confirms the RPC-over-POST pattern: **all traffic is routed through action dispatch keyed by a `[action]` field in the POST body**, almost certainly all against a single (or very small number of) Slim route(s).

Distinct `action` values observed, aggregated across all 7 log files, with approximate frequency from the largest/most recent log (`api/v1/logs/request.txt`, 17,794 requests, Jun 8 2026 01:54–10:19):

| action | approx. count (request.txt) | purpose (inferred from response fields) |
|---|---|---|
| `markattendance` | 1647 | mobile punch-in/out (GPS check-in), ties to `login_auditor` JSON blob with lat/long |
| `getLastPunch` | 1495 | fetch employee's last attendance punch |
| `getHomePageDatas` | 1249 | mobile-app home/menu config (expense/leave/customer-visit flags) |
| `branches` | 1078 | list company branches |
| `MissAction` | 910 | unclear — possibly a fallback/miss-route logger |
| `detailattendancecalendar` | 749 | monthly attendance calendar detail |
| `leave_list` | 538 | list leave requests |
| `attendance_details_by_date` | 373 | attendance detail for a specific date |
| `leave_items` | 216 | leave type/balance items |
| `list_month` | 143 | monthly listing (attendance/regularization) |
| `payslip` | 130 | fetch payslip |
| `Leave` | 125 | leave-related action (capitalized variant, likely legacy dup of `leave`) |
| `Sync` | 113 | data sync |
| `reg_attendance_list` | 90 | regularization attendance list |
| `leave` | 65 | apply/act on leave |
| `leave_request` | 56 | submit leave request |
| `listbranches` | 52 | list branches (dup of `branches`?) |
| `expence_lists` | 42 | expense claim listing |
| `update` | 31 | generic update action |
| `add_expense` | 21 | submit expense claim (with `image` field — multipart upload, see §3) |
| `reg_attendance_save` | 19 | save regularized attendance |
| `Approved` | 16 | approval action |
| `access` | 14 | **login** (see §4) |
| `P` | 4 | short-code action, unclear (possibly punch-type shorthand) |
| `regularized_attendance_list` | 3 | regularization list |
| `getlocationlog` | 3 | location log fetch |
| `resetPassword` | 1 | password reset |

Additional actions seen only in **other** rotated logs (not in the main `request.txt` sample above) — INFERRED, same caveat:
- `getSiteList`, `NeedSupport`, `rep_problm`, `ShiftClose`, `A`, `S` (from `logs/request.txt#06_01_2026`, `logs/request_04-Jun-2026-0130PM.txt`, `logs/request_08-Jun-2026-0130AM.txt`)
- A parallel **`*_v2` suffixed family** appears starting around Apr 13 2026 (`logs/request.txt#13_04_20269`): `getHomePageDatas_v2`, `events_v2`, `leave_items_v2`, `leave_list_v2`, `leave_request_v2`, `listbranches_v2`, `reg_attendance_list_v2`, `reg_attendance_save_v2`, `regularized_attendance_list_v2`, `update_v2`, `detailattendancecalendar_v2`, `getLastPunch_v2`, `getlocationlog_v2`, `markattendance_v2`, `payslip_v2`. This strongly suggests an **in-place "v2" migration of individual actions within the single `v1` URL namespace**, not a separate `/v2` route tree — i.e., versioning here is done via action-name suffix, not URL path.

Controller files referenced in `error_log`/`error_log_old` stack traces (these are the **actual controller class names** that exist on the production server, even though the files themselves are missing from this checkout):
- `ApiController.php` — `api/v1/error_log:2` (`mysql_fetch_assoc() ... in .../src/App/Controller/ApiController.php on line 163`), referenced dozens of times — appears to be the main action-dispatch/auth controller.
- `SignupController.php` — `api/v1/error_log:8` (`mysql_fetch_assoc() ... SignupController.php on line 136`) and `api/v1/error_log:14` (`PHP Fatal error: Cannot use try without catch or finally ... SignupController.php on line 112`) — this fatal error indicates **broken/uncompilable code path in production** (or at least was broken as of 26-May-2021).
- `UpdatelocationController.php` — `api/v1/error_log:1`, `api/v1/error_log_old:1` — handles GPS location updates and calls Google Maps Geocoding API via `file_get_contents()` with a hardcoded API key (`AIzaSyAtYvL7xbQpcBIRGzO0X_F6tC8lH_skfzY`, visible repeatedly in `api/v1/error_log`, e.g. line 12) — **exposed/hardcoded third-party API key**, flag for migration (rotate the key).
- `ProjectController.php` — appears in both `error_log` and `error_log_old` (grep hit, exact line numbers not captured in this pass).
- `ClientdbController.php`, `SecurityController.php` — named explicitly in `composer.json:28,30` as force-autoloaded files, and `ClientdbController.php`/`SecurityController.php` also appear in `error_log_old`. `ClientdbController` strongly suggests this is the code responsible for resolving/connecting to the per-company client DB (parallel to the main app's `AppController.php:48-95` dynamic `mysql_connect()`), but its body is not available to confirm.

## 3. Request/response formats

- All observed traffic is **`application/x-www-form-urlencoded` (or multipart) POST**, not JSON — the logs dump `$_POST`-shaped PHP arrays (`Array ( [user_id] => ..., [action] => ... )`), not raw JSON bodies. Example: `api/v1/logs/request.txt:1-6`.
- Responses are **PHP arrays serialized via `print_r`** in the log (e.g. `api/v1/logs/request.txt:9-19`), and given Slim conventions almost certainly serialized to **JSON** for the actual HTTP response body (Slim 3 + the typical `$response->withJson()` pattern) — but this is INFERRED since the wire format itself isn't captured, only the in-memory array dump.
- **File upload / multipart**: confirmed indirectly. The `add_expense` action's response payloads include an `image` field holding a filename like `148_8580_ei_17223175106157302761430630010404.jpg` (`api/v1/logs/request.txt`, tail of file) that **matches the naming pattern of the loose `.jpg` files sitting directly in `api/v1/`** (e.g. `api/v1/113_7_IMG_20210824_135110.jpg`, `api/v1/12_16_IMG_20220804_105626.jpg`). This confirms the API receives multipart file uploads (expense receipt photos) and stores them **directly in the `api/v1/` webroot** (not a dedicated uploads directory) — a deployment/security smell worth flagging for migration (public web-root file storage, predictable filenames, no CDN/object storage separation).
- No XML evidence found anywhere in logs, composer deps, or index.php.
- `login_auditor` field in `markattendance` requests is itself a **JSON string embedded inside the form-POST field** (e.g. `api/v1/logs/request.txt:207153`: `[login_auditor] => [{"auditor_pkey":10,"latitude":9.9834461,...}]`) — i.e., JSON-in-form-field, a common mobile-app integration pattern, not a JSON request body.

## 4. Authentication

This is architecturally **very different** from the main app and appears materially weaker:

- **Main app** (per prior research): session-based, `user_group` (1=admin, 2=employee) checked per-request in `Controller/AppController.php:39-46`; no CakePHP AuthComponent/ACL in use.
- **`api/v1`**: INFERRED from logs — there is **no visible session or bearer-token mechanism in the request logs**. A login-like `access` action exists (`api/v1/logs/request.txt:207125-207133`) that takes `password` (observed value is a 10-digit mobile number, e.g. `9947109096`), a `securitycode` (a short alphanumeric string, e.g. `kjUL` — possibly a company/app identifier rather than a per-request secret), `history_type => LOGIN`, plus GPS/IMEI device metadata. The response to a failed `access` call is `[success] => 0, [message] => Unauthorized UserID!, [data] => [invalid_login] => 1, [locked] => 1]` (`api/v1/logs/request.txt:207138-207144`).
- Critically, **the vast majority of subsequent action calls (markattendance, getHomePageDatas, leave_list, payslip, etc.) carry only `[user_id] => <EMPCODE>` and `[action] => ...` with no password, token, or session identifier at all** (e.g. `api/v1/logs/request.txt:207148-207154`, immediately after the failed login above, a `markattendance` call for a *different* `user_id` (`IMSC1000439`) goes through with no auth field). This means, **as far as can be told from the logs alone, per-request authorization for the bulk of the API surface appears to rely on trusting the client-supplied `user_id` field**, with no re-validated token/session — this should be treated as a high-priority security question to verify against the missing `ApiController.php` source (or the live server) before migration; do not assume it's actually this permissive without confirming in source, but the log evidence is consistent with it.
- A field literally named `[token] => Y` appears in some `getHomePageDatas`-style responses (e.g. `api/v1/logs/request.txt:3782`), but context (`[access_allowed] => Y`, `[locked] => 0`, `[mobile_locked] => N`, `[token] => Y`) shows this is a **boolean employee-record flag** (something like "has a device token registered"), not a session/bearer token value returned to the client for subsequent auth — no long opaque token string is ever observed in the logs.
- No `Authorization` header, API key header, or JWT-looking value appears anywhere in `logs/request*.txt` or `error_log*`.
- **DB credentials**: not directly visible (no connection code in this checkout), but `error_log_old` shows `mysql_insert_id(): Access denied for user ''@'localhost' (using password: NO)` repeatedly (`api/v1/error_log_old:2-3` etc.) from `UpdatelocationController.php:2701` — this indicates that specific call path failed to pass credentials to a `mysql_connect()`/`mysql_insert_id()` call at some point (23-Jul-2020), consistent with the same raw deprecated `mysql_*` API style used by the main app's `AppController.php:48-95`, but the actual working credentials used elsewhere by `ApiController.php`/`ClientdbController.php` are not present in this checkout. **Cannot confirm from this checkout whether `api/v1` reuses the main app's hardcoded control-DB credentials (`mpm_cntrl_usr` / `MyPyR01@Cntr1#LB`, `Config/database.php:75-84`) or has its own** — the presence of a `ClientdbController.php` (named for exactly the "resolve company DB" responsibility) is suggestive that it replicates the pattern, but this must be verified against the live `src/App/Controller/ClientdbController.php` before migration.

## 5. Database access

- Same **deprecated `mysql_*` extension** style as the main app is confirmed in error logs from **three different api/v1 controllers**: `ApiController.php:163` (`mysql_fetch_assoc()`), `UpdatelocationController.php:2701,2781` (`mysql_insert_id()`, `file_get_contents()`-based reverse geocoding), and `SignupController.php:136` (`mysql_fetch_assoc()`). This is strong evidence `api/v1` does **not** use PDO/mysqli or an ORM, and instead hand-rolls raw MySQL connections exactly like the main CakePHP app's non-standard `mysql_connect()` bypass of `ConnectionManager` (per prior research, `Controller/AppController.php:48-95`).
- No `.env` file, no `PDO`/`mysqli` connection strings, no visible DB host/credential literals anywhere in this checkout of `api/v1` (searched `error_log`, `error_log_old`, `README.md`, `CONTRIBUTING.md`, `composer.json/.lock`, `phpunit.xml`, `.gitignore`).
- `ClientdbController.php` (named in `composer.json:28`) is the only structural evidence of a per-client/company DB resolution step analogous to the main app's `central_control` → per-company DB pattern — but its actual logic is unavailable in this checkout.
- **Cannot confirm** table-level overlap with the main app (e.g. shared `attendance`/`leave_requests` tables) directly, since no query strings are visible in the logs (only PHP array dumps of pre-formed response data, no SQL). However, the **domain semantics strongly imply shared tables**: `markattendance`/`getLastPunch`/`reg_attendance_*` clearly write/read attendance punches, `leave_list`/`leave_request`/`leave_items` clearly touch leave-request tables, `payslip` reads payroll output, and `add_expense`/`expence_lists` manipulate an `emp_expenses` table — response field names like `emp_expenses_pkey`, `emp_fkey`, `authorized_by`, `approved_by` (`api/v1/logs/request.txt`, tail of file) look like direct column dumps from a table also plausibly used by the CakePHP admin UI for expense authorization workflows. This is an important migration risk: **api/v1 and the main CakePHP app most likely write to the same company-database tables concurrently** — needs source-level confirmation (which controller in `Controller/` handles expense authorization in the main app, and whether its table/column names match `emp_expenses_pkey`, `emp_fkey`, etc.) before assuming they're independent.

## 6. Relationship to main app

- **No shared PHP code**: `api/v1` is a self-contained Slim 3 micro-framework app (`composer.json` autoloads only `App\` from `api/v1/src/App/`) with its own `Controller` classes (`ApiController`, `SignupController`, `UpdatelocationController`, `ProjectController`, `ClientdbController`, `SecurityController`) that are structurally and namespace-wise unrelated to the main app's CakePHP `Controller/`, `Model/`, `View/` MVC classes. Nothing in `api/v1/composer.json` references the CakePHP `Controller/`/`Model/` directories or autoloads any class from outside `api/v1/src/`.
- **Different URL/deploy path**: production error-log paths show the live location is `/home/mypayrollmaster/public_html/forsight/api/v1/...` (`api/v1/error_log:1` etc.) — i.e. nested under a `forsight/` app root, with `api/v1` as a sibling/subpath, reached via its own `.htaccess` rewrite-to-`index.php`. This is consistent with it being a **separately deployed front controller**, reachable at a distinct URL prefix from the CakePHP app's own dispatcher — but exact relative URL structure (e.g. whether `forsight/` itself is the CakePHP webroot) cannot be confirmed from `api/v1` alone; would need to cross-check the CakePHP `webroot/.htaccess`/`app/webroot` layout.
- **Very likely shares data** (same company MySQL databases) even though it shares no code — see §5. This is the more consequential fact for migration: the Next.js API routes replacing `api/v1` must account for the same tables the CakePHP admin UI touches (attendance, leave, expenses, payslips), i.e. this is not an isolated subsystem that can be migrated independently of the main app's data model.
- Mobile-app-facing surface: field names (`imei`, `latitude`/`longitude`, `login_auditor`, GPS-tagged punches) indicate `api/v1` is the backend for a **mobile attendance/leave/expense app**, distinct in purpose from the CakePHP web admin UI, even though they likely share the underlying company database.

## 7. Dead / deprecated / unreachable code — flag before migrating

- **`README.md` and `CONTRIBUTING.md`** (`api/v1/README.md`, `api/v1/CONTRIBUTING.md`) are unmodified Slim-Skeleton boilerplate — no project-specific documentation value. Possible legacy cruft — safe to ignore/discard, not safe to rely on for behavior.
- **`1formdesign.png`** (`api/v1/1formdesign.png`) — a loose design-mockup image sitting in the API webroot with no evident code reference. Possible legacy cruft — verify before migrating (almost certainly leftover, not served intentionally).
- **`SignupController.php:112`** — logged fatal error `Cannot use try without catch or finally` (`api/v1/error_log:14,15,16`, dated 26-May-2021) indicates this controller had a **syntax-breaking bug in production** at that time. Whether it was ever fixed cannot be determined from this checkout (source missing) — flag as "possible dead/broken code path — verify current state on live server before assuming `SignupController` works."
- **`ApiController.php:163`** — the `mysql_fetch_assoc() expects parameter 1 to be resource, object given` warning recurs **very heavily** across the entire `error_log` (dozens of hits from 2021 through Jan 2023) — this is the classic PHP 5.5→7.x mysql-extension-removed symptom (the mysql extension was dropped in PHP 7; `mysql_query()` starts returning `mysqli_result`/other objects or failing silently depending on shim/compat layer used). This suggests **`api/v1` has been running in a broken/degraded state against a newer PHP version for a long time**, silently swallowing this warning while presumably still functioning via error suppression or a compatibility shim. High-priority item to understand before migration — this code path is clearly fragile and possibly semi-functional, not a clean reference implementation.
- **Hardcoded Google Maps Geocoding API key** in `UpdatelocationController.php` (visible repeatedly in `api/v1/error_log`, e.g. line 12: `...key=AIzaSyAtYvL7xbQpcBIRGzO0X_F6tC8lH_skfzY`) — also repeatedly timing out/failing (`Connection timed out`, multiple entries dated Jul 2021) — flag as a credential to rotate and a call that appears unreliable/possibly abandoned in practice (worth checking if reverse-geocoding is still actually used by the mobile app or if failures are silently ignored).
- **`MissAction`** (910 hits in the sampled log) — action name suggests a catch-all/miss-route handler (i.e., requests that didn't match a known `action` value get logged under this label) rather than a real business action. Possible instrumentation/fallback logic — verify semantics before treating it as a real endpoint to migrate.
- **`_v2`-suffixed action family** (`*_v2`, first appearing in `logs/request.txt#13_04_20269`) coexisting with the original action names — indicates an **in-flight, incomplete migration of individual actions** within the same `v1` URL surface. For the Next.js rewrite, prefer the `_v2` semantics where both exist, but this needs confirming against actual controller code (unavailable here) since we only have action *names*, not implementations, from the logs.
- **Uploaded images stored directly in the `api/v1/` webroot** (`api/v1/*.jpg`, see §3) rather than a dedicated non-executable uploads directory — a deployment anti-pattern to fix during migration (should not persist in the Next.js replacement), not necessarily "dead" but worth flagging as legacy cruft/tech debt.

## Summary for migration planning

- `api/v1` is a **Slim 3 micro-framework RPC-over-POST API** (single/few Slim routes, all real dispatch happens on a POST-body `action` field) serving a **mobile attendance/leave/expense/payslip app**, entirely separate in code from the CakePHP main app, but almost certainly sharing the same per-company MySQL database and core HR tables (attendance, leave, expenses, payslip).
- **The actual controller source (`api/v1/src/`) and vendor dir are missing from this checkout** — the endpoint catalog above is reconstructed from access/error logs and is explicitly marked INFERRED. Before building the Next.js route replacements, the real `src/App/Controller/*.php` files (or a working copy of the live server) must be retrieved to confirm exact input validation, response shapes, and DB queries per action.
- Authentication for the bulk of actions appears (from logs alone) to trust a client-supplied `user_id` with no re-validated session/token — this is a security question to resolve with source access before assuming the same trust model is safe to carry into the new API.

## 8. Addendum — additional corroborating findings (second pass)

These findings were gathered on a follow-up pass over the same checkout and either add new evidence or corroborate/refine points above. All citations verified directly against files on disk.

- **Full-corpus action frequency** (all 7 log files combined, ~97,610 total `[action] => ...` lines, `grep -o '\[action\] => .*'` across every `logs/request*` file including rotated ones): confirms the same top actions as §2's single-file sample, with `markattendance` (13,265), `getLastPunch` (12,567), `getHomePageDatas` (11,224), `branches` (10,077), `detailattendancecalendar` (8,568), `MissAction` (7,492), `attendance_details_by_date` (6,114), `leave_list` (4,739) as the heaviest-traffic actions across the entire available log history (spanning 2026-01-05 through 2026-06-08 in this file set; `error_log`/`error_log_old` push the evidence window back to 2020–2023). This corroborates §2's ranking and adds confidence the sampled file wasn't unrepresentative.
- **`access` (login) action — concrete example payloads** confirming the shape described in §4: a failed attempt (`logs/request.txt`, user `ALI K M`) sends `user_id`, `action=access`, `password` (plaintext, e.g. `9947109096` — looks like a phone number reused as password), `securitycode` (e.g. `kjUL`), `history_type=LOGIN`, `access_time`, `latitude`, `longitude`, `imei` (device-fingerprint string, e.g. `ALI K MsamsungSM-A356E`), and gets back `[success] => 0, [message] => Unauthorized UserID !`. A successful attempt (`user_id=ACCS100277`) gets `[success] => 1, [message] => User logged in successfully, [data] => [user_id, firstname, lastname, uploaded_time, history_pkey, punchtype, reset_login_flag, is_track, logo, location]` — **no token/session id is returned in this payload**, reinforcing §4's conclusion that subsequent calls are authorized purely by the client re-sending `user_id`.
- **The `[token] => Y` field is confirmed to be an employee-record flag, not an auth token**, appearing alongside `[locked] => 0`, `[mobile_locked] => N`, `[reset_login_flag] => N`, `[punchtype] => ...`, `[on_dutty1]/[off_dutty1]/[working_time1]` — i.e., shift/lock configuration fields from the employee master record, not a session credential.
- **Second confirmed live bug, distinct from the `mysql_fetch_assoc()` one**: `error_log` / `error_log_old` both show `PHP Fatal error: Call to undefined method PDO::close() in .../UpdatelocationController.php on line 2768`, firing repeatedly in production (e.g. a dozen+ times within one hour on 04-Aug-2020, per `error_log_old`). This proves `UpdatelocationController.php` uses **PDO** (not `mysql_*`) for at least this call path — i.e. `api/v1` mixes **two different raw-DB-access styles** internally (`mysql_*` in `ApiController.php`/`SignupController.php`, `PDO` in `UpdatelocationController.php`), with no shared DB abstraction layer, and this specific PDO call site has been broken (calling a nonexistent method) for years without being fixed.
- **Intermittent `Class '...' not found` fatals** for `UpdatelocationController`, `ProjectController`, and `SignupController` recur across many dates in both `error_log` and `error_log_old` (e.g. `error_log`: `04-Jan-2023 16:49:55 ... Class 'App\Controller\UpdatelocationController' not found in .../src/dependencies.php on line 53`), while `ApiController` (force-loaded via `composer.json`'s `autoload.files`) never appears in a "class not found" fatal. This is consistent with `ApiController.php`/`ClientdbController.php`/`SecurityController.php` being reliably available (explicit `files` autoload) while `ProjectController`, `SignupController`, and `UpdatelocationController` rely on PSR-4 autoloading that **intermittently fails in production** (likely a stale/uncommitted Composer classmap or filename-casing mismatch on the Linux host) — meaning those three controllers' actions have historically been sporadically completely unreachable, independent of any logic bugs within them.
- **Per-tenant, per-version API URL configuration confirmed in the control DB schema**: `schema/mypayrol_control_db.sql:1433-1436` — the `central_control` table (one row per company/tenant) has separate `web_url` (default `https://login.mypayrollmaster.online/`), `api_url` (default `https://apps.office24.online/forsight/api/v1/`), `app_url` (default `https://v1.mypayrollmaster.online/api/v3/`), and `biometric_url` columns. This is significant: **there is a `v3` API referenced by tenant configuration that does not exist anywhere in this repository checkout** — either a separate codebase/deployment not exported here, or a planned-but-unbuilt endpoint. The Next.js migration scope should explicitly confirm with the client whether a `v3` API exists in production and needs to be accounted for, since this checkout only contains `v1`. Also note `api_url` and `app_url` point at **two different hostnames** (`apps.office24.online` vs `v1.mypayrollmaster.online`) — i.e. this is not just a path-versioning scheme, different API versions may be on entirely different domains.
- **Confirmed shared-table relationship with the main CakePHP app via model presence, not just field-name similarity**: `legacy/Model/EmployeeExpenses.php` exists in the main app and is the model backing `emp_expenses`-family data — the exact field names logged by the API's `expence_lists`/`add_expense` actions (`emp_expenses_pkey`, `emp_fkey`, `expenses_amount`, `authorized_by`, `approved_by`, `expense_status`, `image`) match this model's domain. The main app additionally has dedicated CakePHP controllers for the same workflow — `legacy/Controller/EmployeeExpensesController.php`, `legacy/Controller/ExpenseReportController.php`, `legacy/Controller/ProjectExpensesController.php`, `legacy/Controller/ProjectExpenseReportController.php` — meaning an expense submitted via the mobile API (`add_expense`) is very likely authorized/approved through the CakePHP web dashboard using these controllers against the same table. This should be treated as a hard dependency for the migration: the expense (and by extension attendance/leave/payslip) domain cannot be migrated as an isolated "API subsystem" without also addressing the main app's read/write paths to the same tables.
- **A second, independent "mobile" integration surface exists inside the main CakePHP app itself**, separate from `api/v1`: `legacy/Controller/MobileLocationUpdateController.php` and `legacy/Controller/LeaveapiController.php`. `MobileLocationUpdateController.php:24-33` is a normal CakePHP controller (extends `AppController`, uses `$this->Session->read('ds')` for per-company DB selection — the same session-based multi-tenant pattern as `AppController.php`), with `$uses = array('UserCredentials', 'EmployeeDetails', 'MobileUserTracking', 'Units', 'MobileUserauditor', 'CompanyContactInfo', 'DbConfig', 'ReportAudit')`. This is **not part of `api/v1`** and is reachable through the main app's normal CakePHP URL/session-auth path, not the Slim API's. Its existence alongside `api/v1`'s own location/attendance actions (`getlocationlog`, `markattendance`) suggests either (a) `MobileLocationUpdateController`/`LeaveapiController` are web-dashboard views over data the mobile app writes via `api/v1`, or (b) a redundant/legacy second mobile-data-entry path. This needs verification before migration — flagged as "possible legacy cruft — verify before migrating" since two independent mobile-data surfaces (one Slim, one CakePHP) covering overlapping domains (location tracking, leave) is a common symptom of an incomplete internal migration.
- **`central_control.api_hit_count`-style columns** appear in tables adjacent to `central_control` in the schema (`schema/mypayrol_control_db.sql:1614,1651`), suggesting some API usage metering/limiting exists conceptually, but no corresponding enforcement code was found anywhere in this `api/v1` checkout — likely implemented in the missing `src/`, if at all.

---

## 4. Authentication & Authorization

### 3.1 Session-based, not CakePHP `AuthComponent`

`Config/acl.php` and the ACL database schema (`Config/Schema/db_acl.php`, `Config/Schema/db_acl.sql`) exist on disk but are **unused scaffolding** — no controller registers `AuthComponent` in `$components`, and no `isAuthorized()`/ACL check was found anywhere across all 15 research passes covering ~223 controllers.

Real authorization is a hand-rolled session check in `Controller/AppController.php:39-46`, run on every request via `beforeFilter()`:

```php
$user_group = $this->Session->read('user_group');
if ($user_group != 1 && $user_group != 2) {
    $this->redirect(array('controller' => 'Site', 'action' => 'login'));
}
```

- `user_group = 1` → Admin/HR staff, full back-office access.
- `user_group = 2` → Employee/ESS, restricted to own records (further scoped ad hoc, per-controller, by comparing `emp_fkey` in the session to row ownership — not by any framework mechanism).
- Session config: `Config/core.php:218-221` — `Session.defaults => 'php'` (native PHP sessions, no CakePHP database-session or cache-session handler in use), default cookie/timeout settings (commented out, so framework defaults apply).

### 3.2 Login flow

`Controller/SiteController.php::login()` (`SiteController.php:268-479`) — see the prior user-facing report for the full step-by-step flow. Backend-relevant details:
- Password verification is delegated to `Controller/Component/LoginManagementComponent.php` (`verifyAdminLogin()`/`verifyEmployeeLogin()`).
- Password hashing: no custom hasher is configured anywhere in `Config/`; controllers call `Security::hash($password, null, true)` directly (confirmed in the Statutory/Access cluster, `UserCredentialsController.php`/`UserController.php`), which resolves to CakePHP 2.x's **default SHA-1**, unsalted beyond the app-wide `Security.salt` constant in `Config/core.php:226`. No bcrypt/PBKDF2 upgrade path exists.
- No CSRF protection: `SecurityComponent` is not registered in `AppController::$components` (`AppController.php:38` — only `Email, DataTable, Session, RequestHandler`), so none of the ~223 controllers get CakePHP's CSRF/black-hole-callback protection. Combined with the earlier finding that most POST actions accept GET too, this is a real gap for the Next.js rewrite to close (CSRF tokens / same-site cookies / verb enforcement should be non-negotiable in the new app even though the legacy app has none).

### 3.3 The fine-grained permission layer is UI-only

Beyond the coarse `user_group` gate, `emp_menu` (menu catalog) + `user_access` (`user_fkey, menu_id → active`) implement per-menu-item permissions, administered via `Controller/UserAccessController.php`. **Confirmed by the Statutory/Access cluster pass: no controller action anywhere re-checks `user_access` server-side before executing** — `UserAccessController`'s own grant/revoke actions (`save`, `delete`, `admin()` which flips a user's admin flag, `saveuseraccess()`) have no admin-only enforcement beyond the coarse session gate, meaning any logged-in user (group 1 or 2) can in principle call them directly. This is UI-only permissioning, not server-enforced authorization — a significant design decision the Next.js app should not blindly replicate.

### 3.4 Controllers that bypass the auth gate entirely

A handful of controllers extend the bare CakePHP `Controller` class instead of `AppController`, which means they **skip the `user_group` session check entirely** and are reachable unauthenticated:

- `Controller/LoginAppController.php` — the legitimate base for pre-login pages (`SiteController` for login/logout, `PagesController` for the public homepage). Intentional.
- `Controller/InfoController.php` — dumps a raw, unauthenticated `phpinfo()`. Not intentional; a real exposure.
- `Controller/LeaveapiController.php` — an entire leave-authorization-email mini-API reachable with no login; its `checkLogin()` redirects with the user's plaintext password in the URL querystring.
- `Controller/AccessController.php` — an SSO-bridge endpoint that also passes a password in a URL querystring (`AccessController.php:80`), plus unescaped `$_GET` concatenated directly into SQL (`AccessController.php:69`) using the removed `mysql_*` extension (won't run on PHP 7+ at all).

### 3.5 Multi-tenant / dual-database auth implications

Every authenticated request re-resolves the tenant's database connection dynamically: `AppController.php:48-95` reads `company_key` from session, queries `central_control` in the control DB via a **raw, deprecated `mysql_connect()` call with hardcoded control-DB credentials** (`mpm_cntrl_usr` / `MyPyR01@Cntr1#LB` — `Config/database.php:75-84`), then calls `ConnectionManager::create('companydb', ...)` with the resolved per-company DB credentials (also plaintext, sourced from the `central_control` row) for the rest of the request. This means **database credentials for every tenant are stored in plaintext in the control DB** and re-fetched on every request — a data-model decision the Next.js migration needs an explicit secrets-management strategy to replace (e.g. resolving tenant → connection string via a secrets manager rather than a plaintext DB column).

A separate multi-admin flow (`SiteController::loginWithCentral()`, `SiteController.php:220-266`) lets one login govern multiple tenant companies, switching `company_key`/`company_code` in session after a company-picker step.

---


## 5. Components & Behaviors

## 6. Background / Scheduled Work

## 7. External Integrations

_Sections 5-7 below are combined into a single infrastructure research pass covering Components (A), Behaviors (B), Background/Scheduled Work (C), and External Integrations (D) — read the lettered subsections for each requirement._

# Legacy Backend Infrastructure Report — Components, Behaviors, Background Jobs, External Integrations

Scope: `D:\Projects\RIZOMigration\legacy` (CakePHP 2.x). Read-only research feeding a Next.js migration report.

---

## A. Components (`Controller/Component/*.php`)

Directory listing confirmed complete — exactly 6 files exist in `legacy/Controller/Component/`:

1. `DataTableComponent.php`
2. `DashboardManagementComponent.php`
3. `DatatablesManagementComponent.php`
4. `EmailComponent.php`
5. `LoginManagementComponent.php`
6. `MasterdataManagementComponent.php`

No other component files exist (glob `legacy/Controller/Component/*.php` returned exactly these 6).

### 1. `DataTableComponent.php` (`legacy/Controller/Component/DataTableComponent.php:1-425`)
Third-party (MIT-licensed, by Chris Nizzardini, `DataTableComponent.php:3-28`) adapter that bridges the jQuery DataTables plugin's server-side-processing GET params (`iDisplayStart`, `sSearch`, `order`, etc., via CakePHP's `$controller->request->query`) to CakePHP `find()` calls, returning a DataTables-compatible JSON envelope (`draw`/`recordsTotal`/`recordsFiltered`/`data`) — `getData()` at `DataTableComponent.php:55-176`. Builds `LIKE` search conditions per-column (`getWhereConditions`, `DataTableComponent.php:222-286`).
- **Usage**: Registered app-wide via `AppController.php:38` (`$components = array('Email', 'DataTable', 'Session', 'RequestHandler')`), so every controller inherits it, though only some controllers actually call `$this->DataTable->getData()`.

### 2. `DashboardManagementComponent.php` — **filename/class mismatch, dead code**
`legacy/Controller/Component/DashboardManagementComponent.php:1-91` contains a class literally named `LoginManagementComponent` (identical, older, unhashed-password version of the real `LoginManagementComponent.php` — see below), **not** a `DashboardManagementComponent` class. This mirrors the same content/filename-mismatch pattern already suspected for `EmailComponent.php`, confirmed here too.
- **Usage**: `grep` for `DashboardManagement` across the whole `legacy/` tree returns **zero** matches in any `$components = array(...)` declaration. No controller ever loads a `DashboardManagement` component. Because CakePHP's component loader requires the class name inside the file to match the requested component name, if any controller ever did declare `$components = array('DashboardManagement')` it would fatal (class not found). **Confirmed dead/orphaned file — possible legacy cruft, verify before migrating** (do not port; the real login logic lives in `LoginManagementComponent.php`).

### 3. `DatatablesManagementComponent.php` (`legacy/Controller/Component/DatatablesManagementComponent.php:1-107`)
Hand-rolled SQL-fragment builder for the **old-style** jQuery DataTables server-side protocol (`iDisplayStart`/`iDisplayLength`/`iSortCol_N`/`sSearch` — the pre-1.10 DataTables API, distinct from `DataTableComponent` above which targets the 1.10+ `columns[]`/`order[]` API). `generateSQLConditionsForListing()` (`:27-106`) manually builds `LIMIT`/`ORDER BY`/`WHERE ... LIKE` strings via string concatenation (SQL built as raw strings, some `addslashes()` escaping used but not parameterized).
- **Usage**: Very widely used — declared in ~60+ controllers via `$components = array('DatatablesManagement', ...)`, e.g. `ActivityController.php:53`, `ApiRequestController.php:49`, `CompanyController.php:50`, `DeviceController.php:52`, `EmployeeTaxController.php:51`, `UserCredentialsController.php:53`, `FinancialYearController.php:49`, `RegularisationController.php:54`, `TrackingReportsNewController.php:35`, etc. This is the dominant listing/grid backend across the admin UI.

### 4. `EmailComponent.php` — **name/content matches its purpose, but is itself the "hand-rolled inline PHPMailer with hardcoded creds" pattern**
`legacy/Controller/Component/EmailComponent.php:1-139`. Prior research flagged this file's content as possibly not matching its name — verified: the class name **does** match (`class EmailComponent extends Component`, `:9`) and it does send email, so the filename/class itself is not mismatched. However its actual behavior is effectively a fixed demo/test mailer, not a general-purpose reusable component:
- Hardcoded Gmail SMTP credentials baked directly into class properties: `smtpUserName = 'developer.binesh@gmail.com'`, `smtpPassword = 'developerbinesh'`, `smtpHostNames = "smtp.gmail.com"` (`EmailComponent.php:39-41`).
- Hardcoded recipient defaults: `$to = "bineshbabu.t@gmail.com"`, `$toName = "Binesh Babu"`, `$subject = "Test Mail From Forsight"` (`EmailComponent.php:44-46`).
- `send()` (`:90-138`) instantiates `PHPMailer` directly, always sends the literal body `"Hi How are you"` / `"Alt Text"` (`:128-129`) rather than using `bodyHTML()`/`bodyText()` — looks like an unfinished/test implementation left in place.
- **Usage**: Registered app-wide via `AppController.php:38` and also explicitly in a handful of controllers (`AccessController.php:53`, `SiteController.php:56`, `InfoController.php:53`, `LeaveapiController.php:54`, `YearEndController.php:50`, `UserControllerssss.php:50`, `LoginAppController.php:34`, `PagesController.php:40` indirectly via LoginManagement). Given the developer-test defaults (`developer.binesh@gmail.com`), **it is unclear whether any controller actually calls `$this->Email->send()` in production flows** vs. relying on the inline-PHPMailer pattern described in section D below — worth verifying with a call-site grep before migration (`possible legacy cruft — verify before migrating` if unused).

### 5. `LoginManagementComponent.php` (`legacy/Controller/Component/LoginManagementComponent.php:1-253`)
The real, current authentication logic component. `verifyAdminLogin()` (`:50-105`) and `verifyEmployeeLogin()` (`:107-251`) implement the dual-DB login flow already documented in memory: looks up `CentralUserCredentials`/`CentralControl` on the control DB, then dynamically creates/switches to a per-company `companydb` connection (`ConnectionManager::create('companydb', ...)`, `:159`) and re-checks credentials against `UserCredentials` on that per-tenant DB. Notable details not previously captured:
  - Passwords are hashed with CakePHP's `Security::hash($password, null, true)` (`:53`, `:112`) — i.e. legacy Cake hashing (salted SHA1/256 depending on config), not bcrypt/argon2 — relevant for the Next.js auth migration (cannot directly verify against a modern hash without a compare shim or forced reset).
  - Implements account lockout after 3 failed attempts (`incorrect_login_attempt`, locks via `data['locked'] = 1`, `:167-245`).
  - Implements forced password reset flow: if `reset_login_flag == 'Y'`, generates a reset token/URL instead of logging in (`:72-89`, `:179-207`).
  - Login by employee email is also supported by looking up `emp_device_comp_branch` (`:121-128`).
- **Usage**: `AccessController.php:53`, `SiteController.php:56`, `InfoController.php:53`, `LeaveapiController.php:54`, `YearEndController.php:50`, `UserControllerssss.php:50`, `LoginAppController.php:34`, `PagesController.php:40`.
- Note: `DashboardManagementComponent.php` (item 2 above) is an **older, unhashed-password duplicate** of this exact class — evidence that login logic was revised in place at some point and the stale copy was never deleted, just renamed/orphaned.

### 6. `MasterdataManagementComponent.php` (`legacy/Controller/Component/MasterdataManagementComponent.php:1-393`)
Grab-bag of combo/dropdown data-fetchers and branch/hierarchy access-control helpers, all reading `$this->Session->read('ds')` to pick the correct per-tenant datasource:
  - Simple lookup lists: `getDepartmentsListForCombo()`, `getGradesListForCombo()`, `getVerticalsListForCombo()`, `getBranchesListForCombo()`, `getDesignationsListForCombo()`, `getStoreListForCombo()` (`:27-71`).
  - Feature/branch access-control logic reading a `user_feature_branch_access` table and an `is_hierarchy` flag to decide whether a user sees their whole reporting hierarchy or just their assigned branch(es): `getEmployeesForFeature()`, `getFeatureAccessContext()`, `getBranchesForFeature()`, `getHierarchyEmployeesByBranch()`, `getHierarchyBranches()`, `getAllocatedBranches()` (`:72-343`) — this is effectively a hand-rolled row-level-authorization layer keyed on `current_feature_id` session value, important for the Next.js migration's authorization model.
  - Raw SQL string interpolation is used throughout (e.g. `:83-90`, `:95-106`) — some newer methods use parameterized queries (`getAllEmployeesByBranch()`, `:365-392`, uses `?` placeholders) while older ones interpolate `$emp_pkey`/`$branch_id` directly — inconsistent SQL-injection hygiene, worth flagging for the migration's security review.
- **Usage**: The single most widely-used non-Session component — declared in 90+ controllers, e.g. `ArrearsReportsController.php:54`, `AttendanceController.php:52`, `EmployeeController.php:53`, `PayrollController.php:55`, `SalaryReportsController.php:53`, `TaxController.php:56`, `StoreController.php:52`, etc.

### Base-controller components
`AppController.php:38-46` (already documented in memory) registers `array('Email', 'DataTable', 'Session', 'RequestHandler')` for every controller. No `AuthComponent` or `AclComponent` anywhere in the codebase — confirmed no additional matches beyond what memory already states.

---

## B. Behaviors

`legacy/Model/Behavior/` **does not exist**. `find -type d -iname "Behavior"` under `legacy/` returns nothing, and `Glob legacy/Model/Behavior/*.php` returns no files. `legacy/Model` itself has **no subdirectories at all** (confirmed via directory listing) — it's a flat folder of model `.php` files only.

`grep -r '\$actsAs'` across `legacy/Model/*.php` returns **zero matches**. No model in the app uses `$actsAs`, and since no custom Behavior classes exist, none of CakePHP's built-in behaviors (Tree, Translate, etc.) appear to be wired in either (a targeted grep would be needed to fully rule out core behaviors, but no `$actsAs` declaration exists anywhere to trigger any).

**Conclusion: this app has no Behavior layer at all.** All "shared model logic" is instead implemented ad hoc inside Components (see section A) or duplicated per-controller.

---

## C. Background / scheduled work

### Console/Shells
Searched the entire project tree (not just `legacy/`) for any `Console` or `Shell` directory: **none exist**. No CakePHP Shell classes anywhere. This is a plain web app with no CLI task-runner layer.

### "cron" grep — false positive, corrected
The prior research's "cron" hit in `EmployeeResignationController.php` was re-verified and is a **false positive**: it matches the substring `cron` inside the variable/function name `acronyms` (`EmployeeResignationController.php:1955`, `array_map(function ($value) { ... acronyms ...})` — building leave-type acronyms for a report, unrelated to scheduling). A corrected whole-tree, word-boundary search (`\bcron\b`, excluding "acronym" matches) across every `.php` file in `legacy/` returns **zero genuine hits**. There is no code, comment, or dead branch anywhere in the app that references "cron" as a scheduling mechanism.

### Queue-like patterns
Searched for `queue`, `job_status`, `pending_job`, `scheduled_task`, `cronjob`/`cron_job` across all `.php` files: **zero matches**. No job-queue table names, no queue library usage, no background-worker pattern of any kind in code.

### External-scheduler-style actions
No `Console`/Shell exists to run scheduled jobs from cron, and no queue mechanism exists in-app. I did not find a distinct "batch action with no session check" pattern beyond what's already documented (many `autoRender = false` AJAX endpoints exist, but these are all invoked from the browser as part of the interactive admin UI — e.g. `UserCredentialsController.php:280-297` — not designed as unauthenticated cron-hit endpoints; they still sit behind `AppController`'s session-based `beforeFilter` gate per the already-confirmed auth model at `AppController.php:39-46`).

### Conclusion
**This app has no in-repo background job mechanism whatsoever** — no Shells/Console, no queue tables, no cron-triggered code paths, no job-status tracking. Evidence: (1) `Console/` directory absent from the entire repo tree, (2) zero genuine "cron" references anywhere, (3) zero queue/job-related identifiers anywhere, (4) `Model/Behavior/` also absent, ruling out behavior-driven scheduled logic too. **INFERRED**: if any periodic task exists in production (e.g. daily attendance rollups, leave-balance accrual, resignation-date processing), it must be driven by an **external mechanism** not present in this repo — most likely a system crontab hitting a URL/controller action via HTTP (a common CakePHP 2.x pattern), or a manually-triggered admin action. No evidence in-repo identifies which endpoint(s), if any, serve that role — this should be confirmed with whoever manages the production server/infra, as it's invisible from source alone.

---

## D. External integrations

### Payment gateways
- **Razorpay** — confirmed, the only payment gateway integrated. `legacy/Controller/CompanySetupController.php`:
  - `createRazorpayOrder()` (`:79-108`) — creates an order via `curl_init('https://api.razorpay.com/v1/orders')` (`:93`), with **hardcoded test-mode API keys** `key_id = "rzp_test_RUncVaytiPXcE3"`, `key_secret = "5mvotOLNsHZXDeb5g5YaiXRE"` (`:81-82`, repeated at `:119`, `:189-190`).
  - `verifyPayment()` (`:109-184`) — verifies the HMAC-SHA256 signature (`:121-123`) using the same hardcoded secret, then updates `PlanPaymentHistory` and the user's `plan_id` via raw SQL string interpolation (`:154-158`, `$lower_user_id`/`$plan_id_value` — the latter is cast to `(int)` so is safe, the former is not parameterized).
  - `getRazorpayPaymentDetails()` (`:187-199`) — fetches payment details via `curl_init("https://api.razorpay.com/v1/payments/" . $payment_id)` (`:192`), again hardcoded key/secret.
  - Keys are **test-mode** (`rzp_test_...` prefix) — confirm whether production uses different (presumably also hardcoded) keys elsewhere, or whether this whole plan/billing flow is itself unused/in-development functionality.
- **No other payment gateway found.** Searches for Stripe, PayPal, PayU, Instamojo, Paytm returned no genuine matches — the only near-hits were false positives from the CSS class `table-striped` (contains substring "stripe") in several controllers (`DirectPurchaseOrderController.php:151`, `MaterialRequestController.php:452`, `PurchaseOrderController.php:494`, etc.) — **not real Stripe references**.

### Third-party / outbound APIs
Systematic `curl_init` grep across the whole tree found real outbound HTTP integrations beyond Razorpay:
- **Nextcloud** — referenced only in comments, never actually called. `SiteController.php:357` has a commented-out example `curl` shell command hitting `https://nextcloud.mypayrollmaster.com/ocs/v1.php/cloud/users` to provision a Nextcloud user. `UserCredentialsController.php:882` has a similarly commented-out `CURLOPT_URL` line for the same host. **Confirmed dead/aspirational — never wired up, possible legacy cruft.**
- **Google Maps Geocoding API** — `DashboardController.php:2925` and `DashboardNewController.php:3711` call `https://maps.googleapis.com/maps/api/geocode/json` with a hardcoded API key (`AIzaSyBmp4GQqI30Qis3uVCEbDncRA667nvO61A`) to reverse-geocode attendance check-in coordinates.
- **Google Maps Distance Matrix API** — `TrackingReportsController.php:1186,1225` and `TrackingReportsNewController.php:1299,1350` call `https://maps.googleapis.com/maps/api/distancematrix/json` with a second hardcoded key (`AIzaSyDCRnB84OWMvOU1Yv6vsXSLo9U0mxyx3Lc`) for field-tracking distance calculations.
- **Firebase Cloud Messaging (FCM)** — `EmployeeController.php:6499` and `EmployeeJoinController.php:5921` post to `https://fcm.googleapis.com/fcm/send` for mobile push notifications (server key not visible in the snippet grepped; would need a follow-up read of surrounding lines if push-notification migration is in scope).
- **Internal self-referential APIs** (same product, different subdomain/service — still worth flagging as "external" from this app's perspective since they're separate deployed services):
  - `DocumentManagerController.php:1861,1897` / `DocumentManagersController.php` (duplicate) / `View/DocumentManagers/DocumentManagersController.php` (duplicate) — call `https://v1.mypayrollmaster.online/api/v2qa/templateRender` and `https://v1.mypayrollmaster.online/DocumentManager/renderImageTempalte` to render document/certificate image templates, including a hardcoded session cookie in the request headers (`'Cookie: PHPSESSID=kmep5t5oca2oo1fa6dfe0l95t3'`, `DocumentManagerController.php:1907`) — a **hardcoded stale session ID baked into source**, almost certainly broken/expired and worth flagging as a bug, not just cruft.
  - `UserCredentialsController.php:299-325` (`companyHistory_add()`) — posts to `https://myportalapi.mypayrollmaster.online/thirdpartyapi/companyHistory/add` with hardcoded Basic-Auth-style headers `'username: profileadmin'`, `'password: admin&*()'` (`:314-315`).
- **htmlcsstoimage.com** — `DocumentManagerController.php:1864-1888` (and duplicates) — entirely commented out, with hardcoded `user_id`/`api_key` UUIDs still visible in the dead code (`:1873`). **Dead code — verify before migrating, do not port.**

### Email providers — full SMTP credential inventory
Confirms and substantially expands the prior partial finding. The dominant pattern across the codebase is **hand-rolled inline `PHPMailer` instantiation per-controller/per-action** (not the registered `EmailComponent`), each block re-setting `IsSMTP()`/`Host`/`Username`/`Password` locally. This pattern is repeated **dozens of times** across live controllers and backup (`*Bkup*`, `_old_*`, duplicate `View/...Controller.php`) files. Distinct host/credential pairs found:

| Host | Username | Password | Representative files |
|---|---|---|---|
| `smtp.gmail.com` | `developer.binesh@gmail.com` | `developerbinesh` | `Config/email.php:62-68` (the `gmail` transport config, already known), `Controller/Component/EmailComponent.php:39-40` |
| `smtp.zoho.in` | (none set inline — uses default/from-only) | — | `ActivityController.php:367` |
| `smtp.zoho.com` | `info@mypayrollmaster.in` | `welcome123` | `DataUploaderController.php:1731-1734`, `EmployeeController.php:4341-4344`, `EmployeeJoinController.php:3853-3856`, `LeaveRequestControllerBkups.php`, `LeaveRequestControllerNimisha_edited.php`, `EmployeeController_old_before addnominee.php` |
| `smtp.zoho.com` | `info@myprojectsmaster.com` | (see below) | referenced among the username list — needs a follow-up read if in scope |
| `smtp.zoho.com` | `noreply@mypayrollmaster.com` | (password commented out / blank in several spots) | `EmployeeLeaveRequestController.php:4295,4582,4849,5141,5425,5697` |
| `smtp.email.ap-hyderabad-1.oci.oraclecloud.com` (**Oracle Cloud Infrastructure email delivery**) | `ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com` (an OCI SMTP credential username, which embeds the OCI user OCID and tenancy OCID) | one of `@Password90#`, `Mypayroll125#`, `Welcome123`, `m$Xt&:CFT7KCFkB]S$K)`, `mypayrollmaster123` (exact password not co-located with username in the grepped SMTP-set blocks — passwords for this host are set a few lines below/above `Username` in each file and should be paired per-file if exact mapping is needed) | `DashboardController.php:3412-3418,3591-3597`, `DashboardNewController.php:4032-4038,4338-4344,4879-4885`, `DocumentManagerController.php:1812-1818`, `DocumentManagersController.php:2260-2266`, `View/DocumentManagers/DocumentManagersController.php:2262-2268`, `EmployeeJoinController.php:9946-9955`, `EmployeeLeaveRequestController.php:3393-3403,3710-3720,3994-4007`, `EmployeeResignationController.php:2619-2625`, `LeaveapiController.php:256-262`, plus `LeaveRequestController.php`, `PayrollController.php`, `SalaryReportsController.php`, `SynthiteSalaryReportsController.php`, `SiteController.php` |
| `localhost` (dev/default, unused in practice) | `user` | `secret` | `Config/email.php:49-57,72-97` (the `smtp`/`fast` default transport stubs) |

Additional distinct usernames seen in the codebase not fully cross-referenced above (worth a follow-up targeted grep if a complete per-file password mapping is needed for a credential-rotation task): `noreply@mypayrollmaster.online`, `noreply@myparollmaster.online` (typo variant — note the misspelling, likely a copy-paste bug), `info@myprojectsmaster.com`.

**Key finding confirmed and generalized**: the app has **at least 4 distinct SMTP providers/accounts hardcoded across 15+ controller files** (Gmail, two Zoho accounts/variants, Oracle Cloud OCI email), all via inline `new PHPMailer()` blocks rather than the single registered `EmailComponent`/`Config/email.php` transports. `Config/email.php` itself only actually configures the Gmail account (`:62-71`) — the `smtp`/`fast` configs are unmodified CakePHP boilerplate defaults (`host: localhost`, `:49-57`, `:72-101`) that are almost certainly unused. **This is a significant migration concern**: credential rotation, centralizing email sending, and secrets management all need to account for this sprawl rather than a single config file.

### File storage
**Local filesystem only** — no S3 or cloud storage SDK found anywhere. An initial broad grep for `s3`/`amazonaws`/etc. produced one hit that is a **false positive** (`DashboardController.php:2925` — the substring "s3" appears inside the Google Maps API key `AIzaSyBmp4GQqI30Qis3uVCEbDncRA667nvO61A`, not a reference to AWS S3). Actual upload handling uses PHP's native `move_uploaded_file()` writing into webroot-relative paths, e.g. `EmployeeController.php:785,3084,4376,4915,6263` (avatar uploads, employee data imports, CTC documents). No AWS SDK, Guzzle, or any cloud storage client library reference exists anywhere in `legacy/`.

---

## Summary of corrections to prior research
1. The "cron" hit in `EmployeeResignationController.php` is a **false positive** (matches inside "acronyms") — there is no cron reference anywhere in the app, confirmed via whole-tree word-boundary search.
2. `EmailComponent.php`'s class name **does** match its filename (unlike the suspicion raised) — the real filename/class mismatch is in **`DashboardManagementComponent.php`**, which actually contains a stale duplicate of `LoginManagementComponent`'s class and is **never referenced by any controller** (confirmed dead file).
3. Payment gateway search confirms **Razorpay only** — Stripe/PayU/Instamojo/PayPal/Paytm searches all returned false positives (`table-striped` CSS class).
4. Beyond the already-known Nextcloud comment (still just a comment, never called), found **four additional real outbound integrations**: Google Maps Geocoding, Google Maps Distance Matrix, Firebase Cloud Messaging, and two internal-but-separate `mypayrollmaster.online`/`myportalapi.mypayrollmaster.online` service calls (one with a hardcoded, likely-stale session cookie baked into source).

---

## 8. Model-Layer Validation & Business Rules — Cross-Cutting Summary

This requirement is answered in full detail per-cluster in §10 below (each cluster's research pass checked its own models for `$validate`, lifecycle hooks, and associations). The headline finding, confirmed independently and without exception by all 13 cluster passes:

**No model in this application defines meaningful validation or business logic.** `Model/AppModel.php` (the shared base class every model extends) is a bare passthrough with no global hooks. Individual models are thin table-mapping shells — `$name`, `$primaryKey`/`$useTable`, occasionally a commented-out (i.e. disabled) `belongsTo`/`hasMany` association — and nothing else. Zero `$validate` arrays and zero `beforeSave`/`beforeValidate`/`afterSave`/`beforeDelete` implementations were found across ~190 model files surveyed.

Where "business rules" do exist, they live in one of two places, neither of which is the Model layer:

1. **Controller PHP** — validation, eligibility calculations, approval-chain state transitions, and cascading-delete guards are all hand-written inline in controller actions (frequently mixed with raw SQL string interpolation). Examples: leave-overlap detection in `LeaveRequestController`/`EmployeeLeaveRequestController`; advance eligibility (80% of monthly CTC, attendance-prorated for specific tenants) in `EmployeeadvanceController`; Division/Section guarded-delete-when-employees-assigned checks in the Company Setup cluster.
2. **MySQL stored procedures** — payroll calculation (`calculateSalaryMainPrc`, `salaryProcessPrc`, `payroll_master_approve`), attendance register writes (`insert_update_att_reg`), and site-attendance transactions are invoked via one-line model wrappers (`$this->Model->query('CALL some_proc(...)')`) that call into stored procedures **not present anywhere in the PHP tree** — their logic is opaque to static code analysis and can only be recovered from the live database (`schema/*.sql` dumps, if they include routine definitions, or a direct DB introspection pass) before the Next.js migration can port that logic.

**Practical implication for the migration:** there is no "port the models" phase for this app in the conventional sense. The Next.js/Prisma (or equivalent) schema can be derived from the table structure, but the actual business rules must be re-derived control-by-control from the controller PHP and, for payroll/attendance specifically, from the stored procedure definitions in MySQL — not from anything in `Model/`.

---

## 9. Caching & Error Handling / Logging

### 9.1 Caching

There is effectively **no application-level caching** in this app.

- `Config/core.php:370-388` configures only CakePHP's two internal framework caches — `_cake_core_` (class maps/path info) and `_cake_model_` (schema descriptions) — both using the `File` engine, both scoped to framework internals, not application data.
- No `Cache::config('default', ...)` is defined anywhere (the commented-out example in `Config/core.php:87-96` was never uncommented), so CakePHP's `Cache::write()`/`Cache::read()` API has no working default configuration if any code tried to use it.
- A repo-wide search found **zero calls to `Cache::write()` or `Cache::read()`** in `Controller/` or `Model/`. No APC/Memcached/Redis configuration exists anywhere in `Config/`.
- The only "caching" concept present is CakePHP's browser static-asset timestamping (`Config/core.php:239-242`), which is commented out (disabled).

**Conclusion:** every request recomputes everything from the database on every hit — there is no cache layer to migrate. The Next.js rewrite starts with a clean slate here and can introduce caching (React Server Component caching, Next.js data cache, Redis, etc.) without needing to replicate any legacy caching behavior.

### 9.2 Error handling

- `Config/core.php:34` sets `Configure::write('debug', 2)` — CakePHP's **highest** debug level, which enables full stack traces and SQL query dumps on every error page. This value is not overridden anywhere else in `Config/` for a production environment; if this configuration file is what's actually deployed, **the application would leak full stack traces, file paths, and SQL to any user who triggers an error** — flagged as a real (not just legacy-cruft) security concern to raise with the client, not just a migration note.
- `Config/core.php:51-55` and `:77-81` configure the framework's default `ErrorHandler`/`ExceptionRenderer` — no custom error handler class was found in the app (`Lib/Error/` does not exist in this checkout).
- Application-level error handling is inconsistent and ad hoc: `try`/`catch` blocks appear in 79 of ~223 controller files (roughly a third), with no consistent pattern for what's caught or how failures are surfaced to the client — some swallow exceptions silently, others `echo json_encode(array('error' => ...))`, others let CakePHP's default exception rendering take over. `die()`/`exit()` is used in 111 controller files, generally to short-circuit AJAX/JSON-response actions after `echo json_encode(...)` — this is the dominant "early return" idiom in the codebase rather than clean `return`/`autoRender=false` flow.

### 9.3 Logging

- `Config/bootstrap.php:98-108` configures two file-based CakeLog streams: a `debug` log (types: notice/info/debug) and an `error` log (types: warning/error/critical/alert/emergency), both writing to the default `App/tmp/logs/` location via CakePHP's `FileLog` engine.
- **Almost nothing in the application actually writes to these logs.** A repo-wide search found `CakeLog::write()` used in exactly one controller (`Controller/EmployeeJoinController.php`) outside of the framework's own automatic error/exception logging (which fires on uncaught exceptions per `Config/core.php:77-81`'s `'log' => true`). A single `error_log()` PHP-native call was found (one hit, one file).
- The `api/v1` Slim subsystem has its own, separate, more consistently-used logging convention: `api/v1/logs/request.txt` (plus rotated/dated copies like `request.txt#06_01_2026`, `request_04-Jun-2026-0130PM.txt`) logs every request, and `api/v1/error_log`/`error_log_old` capture PHP errors — this is the log data the api/ subsystem research pass (§2) used to reconstruct its endpoint catalog, since source-level route definitions are missing from this checkout.
- **Conclusion:** the main CakePHP app has almost no operational logging in practice, despite having a configured logging framework available — errors are surfaced to the browser (via `debug=2`) rather than captured centrally. The Next.js migration should treat structured, centralized logging (e.g. to a log aggregator) as a net-new capability rather than something to "port" from this app.

---

