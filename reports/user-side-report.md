# RIZO (MyPayrollMaster) — User-Facing Behavior Report

**Scope:** `D:\Projects\RIZOMigration\legacy` — a CakePHP 2.x HR/payroll SaaS application. This report documents actual, code-confirmed end-user behavior (not just file structure) to inform the Next.js migration.

**Method:** This report was assembled from 13 parallel deep-read passes over `Controller/`, `Model/`, `View/`, and `Config/`, each covering one feature-area cluster of controllers, plus a dedicated pass over `Config/core.php`, `Config/routes.php`, `Controller/AppController.php`, and `Controller/SiteController.php` for the authentication/role model. Every factual claim in the per-feature sections below is cited as `path:line`. Claims the researching pass could not directly confirm in code are explicitly marked `INFERRED:`. Files with backup/duplicate naming patterns (`Bkup`, `backup`, `_old`, dated suffixes, per-customer one-off copies, and anything containing `#backup`) were identified and noted once rather than fully re-traced, per instructions.

**Codebase scale:** ~223 controllers, 2,502 view template files, a separate `api/v1/` mini-app. Given this scale, many controllers turned out to be dead/orphaned duplicates of a canonical controller — each section below calls this out explicitly where confirmed.

---

## 1. Roles & Authentication Model

**There is no CakePHP `AuthComponent`/ACL enforcement in this app**, despite `Config/acl.php` and the ACL database schema (`Config/Schema/db_acl.php`) existing on disk — they are unused scaffolding. Authorization instead works entirely through a hand-rolled session-based model:

### 1.1 The two coarse roles

Every request through `AppController::beforeFilter()` (`Controller/AppController.php:39-46`) checks the session variable `user_group`:

```php
$user_group = $this->Session->read('user_group');
if ($user_group != 1 && $user_group != 2) {
    $this->redirect(array('controller' => 'Site', 'action' => 'login'));
}
```

- **`user_group = 1` — Admin/HR staff.** Full back-office access: employee master data, payroll processing, company setup, reports, approvals.
- **`user_group = 2` — Employee (ESS / self-service).** Restricted to their own records: apply for leave, view payslips, submit expense/advance requests, check attendance, participate in performance reviews. `AppController.php:100-127` shows this group additionally gets a leave-approval notification query and event feed injected into every page load if they have subordinates.

Any other/missing value redirects to `Site/login`.

Two base controllers exist below `AppController`: **`LoginAppController`** (extends the bare CakePHP `Controller`, *not* `AppController` — so it skips the `user_group` gate entirely) is the parent of `Controller/SiteController.php` (login/logout) and `Controller/PagesController.php` (the public "/" homepage) — this is intentional, since a user isn't logged in yet when hitting these. A few *other* controllers were found to also extend the bare `Controller` class directly (bypassing the gate) where that was clearly unintentional — see the security notes in §1.4 and the Dashboards/Notifications and Leave sections below.

### 1.2 Login flow

`Controller/SiteController.php::login()` (`SiteController.php:268-479`) handles both roles through one form:
1. POST is checked against `LoginManagement->verifyAdminLogin()` first (admin path, sets `user_group=1`).
2. If that fails, it's checked against `LoginManagement->verifyEmployeeLogin()` (employee path, sets `user_group=2`), which can return **locked**, **reset-required**, or **unauthorized** states, each with a distinct message (`SiteController.php:335-346`).
3. On success, admins land on `Dashboard/index`; employees land on `Dashboard/hierarchydashboard` (if they have subordinate-approval permissions) or `Dashboard/empdashboard` (individual contributor) — except two hardcoded company codes (`GLET`, `ABSG`) which always land on `Dashboard/index` (`SiteController.php:380-395`).
4. A separate **multi-admin / multi-company** flow exists (`loginWithCentral()`, `SiteController.php:220-266`) for users who administer more than one tenant company — they see a company picker (`companies_list`) before being dropped into a specific company's session.

### 1.3 Fine-grained permissions layer

Beyond the coarse `user_group`, a second permission layer exists: the `emp_menu` table defines every menu item (keyed by `menu_id`), and the `user_access` table maps `(user_fkey, menu_id) → active ('Y'/'N')`, checked e.g. at `SiteController.php:371-396` to decide which post-login dashboard an employee lands on, and used throughout the app to decide what's visible in the nav menu.

**Important finding, confirmed independently by nearly every one of the 13 research passes below: this `user_access`/`menu_id` system is UI-only.** No controller action in the ~200 controllers surveyed re-checks `user_access` server-side before executing — a user who guesses/bookmarks a URL for a menu item they don't have `user_access` for can generally still execute it, gated only by the coarse `user_group` check. The real admin screen for managing this permission layer is `Controller/UserAccessController.php` (see §12/Statutory-Access section) — an admin (or anyone who reaches it) can check/uncheck a tree of menu permissions per user.

### 1.4 Security notes surfaced during this research

These aren't in scope to fix, but are worth flagging before migration since the new app should not reproduce them:

- `Controller/InfoController.php` extends the bare `Controller`, skipping the auth gate, and outputs a raw `phpinfo()` dump — unauthenticated.
- `Controller/LeaveapiController.php` also extends the bare `Controller`, so its entire leave-authorization-email API is reachable without login; its `checkLogin()` action redirects with the user's plaintext password in the URL query string.
- `Controller/AccessController.php` is an SSO-bridge endpoint that also passes a password in a URL querystring.
- Plaintext passwords are mirrored into `mob_user_credentials` and emailed out on reset (`UserCredentialsController`), with a silent BCC to a vendor address on every reset.
- Hardcoded SMTP/API credentials and a hardcoded root DB credential appear repeated across many controllers (Payroll, YearEnd, Employee onboarding, `DbConfigController`'s orphaned setup wizard) rather than centralized config.
- `CompanySetupController::verifyPayment()` builds SQL via raw string interpolation (SQL-injection surface) and ships hardcoded Razorpay test API keys.
- The dual-database architecture (control DB `mypayrol_control_db` mapping `company_code → company DB` via `central_control`, then a per-company DB with all payroll data) is set up dynamically per-request in `AppController.php:48-95` using a **raw `mysql_connect()` call with hardcoded control-DB credentials** (the deprecated `mysql_*` extension, not CakePHP's `ConnectionManager`).

### 1.5 How to read this report

Each section below covers one feature-area cluster of controllers (mirroring `Controller/*.php`), in this order:

1. [Employee Management](#2-employee-management)
2. [Attendance & Time](#3-attendance--time)
3. [Leave Management](#4-leave-management)
4. [Payroll, Salary & Tax](#5-payroll-salary--tax)
5. [Advances, Loans & Expenses](#6-advances-loans--expenses)
6. [Reports](#7-reports)
7. [Company / Organization Setup](#8-company--organization-setup)
8. [Site / Field Work & Project Management](#9-site--field-work--project-management)
9. [Assets, Inventory & Purchasing](#10-assets-inventory--purchasing)
10. [Performance Management & HR Admin](#11-performance-management--hr-admin)
11. [Dashboards, Notifications & Mail](#12-dashboards-notifications--mail)
12. [Statutory, Access Admin & Mobile API](#13-statutory-access-admin--mobile-api)
13. [Timesheet & Full Controller Coverage Check](#14-timesheet--full-controller-coverage-check)

Within each section, every controller gets: **Purpose**, **Who can access it**, **step-by-step user flow**, **Forms** (fields/validation/redirects/flash messages), **Notifications**, and **AJAX/JS endpoints**. Multi-step workflows (leave approval, payroll run, procurement, performance review cycle, onboarding wizard) get a dedicated end-to-end summary within their section.

---


## 2. Employee Management

# Employee Management — Behavior Report

Scope: CakePHP 2.x legacy app at `D:\Projects\RIZOMigration\legacy`. Covers EmployeeController, EmployeeDetailController, EmployeeJoinController, EmployeeRegisterController, EmployeeManageController, EmployeeConfigController, EmployeeMenuController, EmployeeHierarchyController, EmployeeUnderController, EmployeeResignationController, NoticePeriodController, EmployeeEmiController, DocumentManagerController/DocumentManagersController, ContactsController, BeneficiaryController.

## Global findings applicable to this whole area

- **No custom routes.** `Config/routes.php` has zero entries referencing any of the controllers in scope (`grep -i "employee\|noticeperiod\|documentmanager\|contacts\|beneficiary" Config/routes.php` → no output). All of them rely on CakePHP's default routing, `/ControllerName/action/param1/param2`.
- **No server-side `menu_id`/`user_access` enforcement in any controller in this scope.** Confirmed by grep for `menu_id|user_access` across all 16 controllers: only `EmployeeRegisterController.php` (3 hits) and `EmployeeMenuController.php` (6 hits) reference these terms at all, and in both cases the usage is **data filtering**, not access control:
  - `EmployeeRegisterController.php:898,1541` — reads `user_access` where `menu_id = '0'` to decide whether the current employee sees *all* punch records vs. only their own branch's (a query-condition toggle, not a page/action block).
  - `EmployeeMenuController.php:36-81,197-287` — `getDefaultMenus()`/`getAddonMenus()` return the *list of menu items to render* in the employee's side nav (UI composition), not a gate on any controller action.
  - No controller in scope calls anything resembling `checkAccess`, aborts with a 403, or redirects based on `user_access`/`menu_id` before executing an action.
- **The only real gate is the session-level `user_group` check inherited from `AppController::beforeFilter` (1 = Admin/HR, 2 = Employee/ESS)**, confirmed already in prior research (`Controller/AppController.php:39-46`). Within that gate, individual actions branch behavior by `user_group` (e.g. showing "My Profile" instead of the admin employee list, restricting query results to the logged-in employee's own branch), but they do not block the action outright — an Employee-group user who guesses/bookmarks an admin URL in this area will generally still execute the action; only the *data returned* differs, and in most actions not even that (see per-controller notes for the exceptions found, e.g. `EmployeeController.php:598,872`).
- **Backup/duplicate files present but excluded from analysis** (per instructions), noted per controller below.

---

## 1. EmployeeController.php (`Controller/EmployeeController.php`, 9234 lines)

### Purpose
The main "Employee Master" hub: create/edit an employee's full profile (personal, professional, tax, family, documents), list/search employees, bulk-import employees and CTC/gross-salary data from Excel, run salary increments and promotions.

### Who can access it
No explicit route-level gate in this controller. Access is enforced only at the session-check layer (`AppController::beforeFilter`, `user_group` 1 or 2). Within actions, `user_group` is read repeatedly (`EmployeeController.php:68,85,126,142,159,196,276,352,600,874,1300,...`) to alter which data is shown (e.g. `setup()` shows "My Profile" heading for group 2 vs. `"<name>'s Profile"` for group 1, `EmployeeController.php:2341-2347`) and, in two places, to restrict edits: `EmployeeController.php:598-600` and `:872-874` contain comments "Authorization: only allow editing own record unless session user_group is 1 or 2" guarding a specific sub-flow (izsave/importEmployeetoEmpjoin area) — this is the only place in the whole Employee area with anything resembling a per-record authorization check, and it is inline in application code, not a framework-level ACL.

### Step-by-step user flow (core "Employee Setup" flow)
1. Admin/HR opens the Employee list — `View/Employee/index.ctp` renders a datagrid bound to `EmployeeController::listemployees()` (`EmployeeController.php:1946`) via AJAX (`easyui datagrid`, `url: livesite+'Employee/listemployees'`).
2. Clicking "New" or "Edit" opens a large modal loaded from `Employee/setup/<emp_pkey>` (`EmployeeController.php:2309`, view `View/Employee/setup.ctp`). In edit mode (`emp_pkey` truthy) it loads `EmployeeDetails`, professional details, tax transactions, and ~10 lookup lists (departments, designations, grades, verticals, branches, holidays, shifts, leave policies, salary structures, financial years, qualifications, notice periods) via `MasterdataManagement` component calls and raw SQL (`EmployeeController.php:2329-2571`). In add mode it builds an empty field array from the model schema.
3. The `setup.ctp` form is a large multi-section (tabbed) form: Personal Info, ID/Bank details, Nominee/Family, Professional Info, Policy allocation (shift/holiday/leave/salary). Representative required fields (`View/Employee/setup.ctp`): `first_name:1424`, `last_name:1498`, `classification:1506`, `date_of_birth:1532`, `nationality_id:1559`, nominee `name:1688`/`relation:1695`/`date_of_birth_nominee:1718`, `id_card:1788`, `country` (required only if `international_worker = Y`, `:1900`), `joining_date:1978`, `emp_type:1995`, `notice_days:2023`, `emp_dept:2044`, `designation:2059`, `emp_branch:2094`, `shift:2476`, `holidays:2493`, `leave:2515`, `salary:2557`.
4. Client-side validation is plain HTML5 `required` attributes; no jQuery-Validate plugin instances found for this form specifically (grep found `.validate(` only in other controllers' views, e.g. Contacts). Several `onchange` JS handlers exist (`set_names()`, `updateStartDate()`, `handleEmployeeTypeChange()`) that recompute dependent fields live.
5. On submit, the form posts (via `$.ajax`/`ajaxSubmit` pattern used throughout the app) to `Employee/saveemployeesetupnew` (`EmployeeController.php:3097`). Server-side logic:
   - Reads `$arr_form_data['model']` to decide which entity is being saved.
   - **Duplicate-check validation**: queries for an existing employee with the same `id_card` (`EmployeeController.php:3195-3199`) and same `lwf_code` (`:3202-3205`). If a duplicate `id_card` is found, it aborts and returns `json_encode(['success'=>FALSE,'error'=>'Duplicate id card or lwf code found','pkey'=>$pkey,'message'=>$message])` (`:3220-3221`). If only `lwf_code` duplicates, current code still saves but returns an error message (`:3211-3218` — note this branch saves AND reports an error, a latent bug/inconsistency worth flagging for the rewrite).
   - On success it saves to `EmployeeDetails`, and if this is a new employee (`pkey == 0`), auto-creates a `UserCredentials` row (device-linked or manual ID generation depending on company `punch_type` config) and, for branch changes on existing employees, runs several supporting checks (unprocessed-attendance recount, "was this employee's attendance already approved/processed in the old branch" warning) before allowing the branch to change (`EmployeeController.php:3225-3291`).
   - Response is a JSON envelope `{success, pkey, message}` (no CakePHP `Session->setFlash` in this action — messages are surfaced client-side via `$.notify(...)`, consistent with the rest of the app's AJAX-modal pattern).

### Other flows in this controller (breadth, not fully traced)
- Bulk employee import (`downloadempdataformat`, `uploadandsaveempdetails` — `:4075,4365`) and bulk CTC/salary upload (`downloadempctcformat`, `uploadandsaveempctc` — `:4717,4904`) both follow the same Excel-template pattern seen in `BeneficiaryController::uploadandsaveempctc` (see below): download an `.xlsx` template via PHPExcel, re-upload it, server validates "mandatory column" presence per row and returns `{success, msg}` JSON.
- Promotion workflow: `promotion($emp_fkey)` (`:2717`) shows a form (`View/Employee/promotion.ctp`), `savepromotions()` (`:2849`) saves it, `approvepromotion($emp_fkey)` (`:2879`) approves it — a simple submit → pending → approve chain, no further gating found beyond the generic `user_group` checks.
- Salary increment workflow: `employeeincrement()` (`:8164`), `salaryIncrementForm()` (`:8594`), `calcSalaryStructure()`/`alterSalaryStructure()`/`saveIncrement()` (`:8701,8763,8931`) — a calculation-heavy form (`View/Employee/salary_increment.ctp`) that recomputes salary-structure line items live via AJAX (`getSalaryStructure`, `onEffectiveDateChange`) before a final save.
- OTP/device-linked import: `sendRequestOTP()` (`:6402`), `importEmployeetoDatabase()` (`:6575`) — pulls candidate profiles from a biometric device/API and imports them; `sendpasswordemail($userid)` (`:6937`) — **email notification**: sends a welcome/password email (uses `PHPMailer`, same hardcoded SMTP account pattern seen in EmployeeResignationController, see below).
- Resume download: `downloadResume($emp_fkey)` / `downloadResume2($emp_fkey)` (`:7184,7476`) generate a PDF resume via Dompdf.

### Notifications
- Email: `sendpasswordemail()` (`:6937`) sends the new employee's login credentials by email via PHPMailer/SMTP.
- Email: FCM push notification helpers `sendFCM()`/`testFCM()` (`:6462,6447`) — in-app/mobile push, used elsewhere for OTP/notice flows.
- All list/save actions surface success/failure purely via JSON `{success, msg}` consumed by `$.notify(...)` toast notifications client-side (no server-rendered CakePHP flash messages found in this controller).

### AJAX/JS endpoints hit from Employee views (non-exhaustive, representative)
`Employee/listemployees`, `Employee/setup/:id`, `Employee/saveemployeesetupnew`, `Employee/jsons`, `Employee/jsonss`, `Employee/getautocompletions`, `Employee/downloadempdataformat`, `Employee/uploadandsaveempdetails/:branch`, `Employee/downloadempctcformat/...`, `Employee/uploadandsaveempctc/:type`, `Employee/getGrade/:category`, `Employee/showtaxheaddetail/:emp/:tax`, `Employee/savepromotions`, `Employee/approvepromotion/:emp`, `Employee/getSalaryStructure/:emp`, `Employee/saveIncrement`.

### Duplicates in `Controller/`
- `EmployeeController_old_before addnominee.php` — older snapshot, not traced (per instructions).

---

## 2. EmployeeDetailController.php (`Controller/EmployeeDetailController.php`, 61 lines)

### Purpose
Effectively a stub. `index()` is empty (`EmployeeDetailController.php:58-61`), no other actions exist, and `View/EmployeeDetail/` has **no `.ctp` files at all**. This controller does not currently drive any user-facing screen — it declares a full set of `$uses` models (suggesting it was scaffolded for a future feature) but implements nothing.

### Who can access it
Only the base session gate; irrelevant since there's no functionality.

### Step-by-step flow / Forms / Notifications / AJAX
None — no implemented behavior to migrate.

---

## 3. EmployeeJoinController.php (`Controller/EmployeeJoinController.php`, 13261 lines)

### Purpose
The "new hire onboarding" counterpart to `EmployeeController`. Handles the join/onboarding wizard used when bringing a new employee's records to completion after initial creation (personal, professional, education, experience, family, documents, statutory numbers), tracks and reports **onboarding completion percentage**, and notifies HR by email when onboarding finishes. Also duplicates most of `EmployeeController`'s CRUD surface (list/setup/promotion/increment/CTC-upload) — this looks like a forked/expanded copy of `EmployeeController` that grew a dedicated onboarding wizard on top.

### Who can access it
Same pattern as `EmployeeController`: no controller-level gate beyond the session `user_group` check; individual actions branch on `user_group` (e.g. `onboarding()` sets different `head` text and restricts branch lists for group 2 users of specific companies, `EmployeeJoinController.php:8394-8419`).

### Step-by-step user flow (onboarding wizard — the feature unique to this controller)
1. Admin opens the onboarding list page — `View/EmployeeJoin/index.ctp` renders a card/tab UI ("All Employees" panel etc.) with AJAX-bound sections; live "% complete" values are pulled per employee via `EmployeeJoin/getOnboardingCompletion` (`View/EmployeeJoin/index.ctp:1294-1295`; controller method `getOnboardingCompletion()` at `EmployeeJoinController.php:10391`).
2. Clicking into a new-hire record opens `EmployeeJoin/onboarding/:emp_join_pkey` (`EmployeeJoinController.php:8231`). Note the parameter is actually an `emp_join_pkey`, which the action resolves to the real `emp_fkey` via `EmployeeJoin->find('first', ['conditions'=>['emp_join_pkey'=>...]])` (`:8267-8275`) before loading any employee-specific data — i.e., a join-in-progress row and the final `emp_details` row are separate records linked by this lookup.
3. The onboarding form (`View/EmployeeJoin/onboarding.ctp`) is broken into the same sections as `Employee/setup.ctp` (personal, professional, statutory, policy) and is saved progressively — supporting endpoints `saveonboarding()` (`:10622`), `saveAllOnboard()` (`:9075`), `updateOnboardData()` (`:8884`), each persisting a subset of the form.
4. **Completion percentage** is computed server-side, not just client-side, by `calculateOnboardingPercentage($emp_pkey)` (`EmployeeJoinController.php:10235`): it checks a fixed list of "personal" fields (`first_name, date_of_birth, classification, email, mobile_no, address, pincode, nationality_id, city, state, maritual_status, guradian, relation_guardian, blood, id_card, pan_no, bank_name, branch_name, ifsc_code, account_no, esi_dispensary, esi, pf, company_pf, previous_member_id, wps_code, lwf_code, profile_pic, international_worker, country` — `:10244-10275`) for non-blank values (treating the literal string `'0'` as *not filled*, `:10239-10240`), plus a "company data" section (`joining_date, emp_branch, emp_dept, designation, emp_type, notice_days` plus conditional fields depending on `emp_type` being Permanent/Contract/Probation, `:10308-10334`). `profile_pic` only counts as filled if it isn't one of the two placeholder image paths (`:10295-10296`). The weighted percentage across sections drives the progress ring shown in the UI and in the completion email.
5. When an admin explicitly triggers the completion email (`View/EmployeeJoin/onboarding.ctp:1396-1397`, `$.ajax` to `EmployeeJoin/sendOnboardingMail`), `sendOnboardingMail()` (`EmployeeJoinController.php:9882`) looks up the admin's own email from `CentralUserCredentials` (central/control DB) as the *recipient* (`:9890-9898,9925` — i.e., the email goes to the admin/HR user who is logged in, not to the new employee), builds an HTML email with a circular percentage ring (`:9933-9935`) and sends it via PHPMailer/SMTP with the subject `"Onboarding Completed <name>"` (`:9965`).

### Forms
- Onboarding form fields largely mirror `Employee/setup.ctp` (see EmployeeController section above) since both write to the same `emp_details`/`emp_proff` tables; the percentage-calculation field list above is the authoritative "what counts as complete" definition to preserve in a rewrite.
- No dedicated client-side validation library found beyond standard `required` attributes and inline `onchange` handlers (same conventions as `Employee/setup.ctp`).

### Notifications
- Email: `sendOnboardingMail()` — HTML email with progress ring, hardcoded SMTP creds (`smtp.email.ap-hyderabad-1.oci.oraclecloud.com`, `EmployeeJoinController.php:9948-9961` — same literal credentials appear in `EmployeeResignationController::sendEmail()`, flag for secrets rotation during migration, not just behavior parity).

### AJAX/JS endpoints (representative, from `onboarding.ctp`/`index.ctp`)
`EmployeeJoin/getOnboardingCompletion`, `EmployeeJoin/sendOnboardingMail`, `EmployeeJoin/onboarding/:id`, `EmployeeJoin/allonboard/:id` (`:8484`), `EmployeeJoin/saveonboarding`, `EmployeeJoin/saveAllOnboard`, `EmployeeJoin/updateOnboardData`, plus the full duplicate set of list/lookup endpoints shared with `EmployeeController` (`listemployees`, `jsons`, `getautocompletions`, `downloadempctcformat`, `uploadandsaveempctc`, etc. — same behavior as documented under EmployeeController, not re-described here).

### Duplicates
None flagged with backup-naming conventions in `Controller/`, but this whole controller is itself largely a parallel/duplicate implementation of `EmployeeController`'s CRUD surface — worth a design decision in the rewrite about whether "Join" and "Employee" become one unified employee-record flow with a status field, rather than two parallel controllers.

---

## 4. EmployeeRegisterController.php (`Controller/EmployeeRegisterController.php`, 2278 lines)

### Purpose
Despite the name, this is **not** an employee-registration/signup flow — it is the **attendance punch register / punch-correction tool**: viewing and manually adding/editing an employee's device attendance punches (in/out times), syncing with the biometric device (AME), and reviewing punch history. `View/EmployeeRegister/index_new.ctp` is the current view (`index.ctp` still exists as an older/parallel copy).

### Who can access it
Session gate only (`user_group` 1/2). `index()` (`EmployeeRegisterController.php:60-125`) now hard-scopes to `$this->Session->read('emp_fkey')` — i.e., in the current implementation, the index only ever loads **the logged-in user's own** record/branch (`:71-78,95-97`), regardless of `user_group`. This differs from most other controllers in this scope where non-employee-group restriction is conditional; here it looks unconditional in the newest `index()`.

### Step-by-step user flow
1. `EmployeeRegister/index/:emp_id?` (`:60`) loads the logged-in employee's punch data and renders `index_new.ctp` (`:124` — explicit `$this->render('index_new')` override).
2. The punch grid/list is populated via `listpunchesnew()` (`:1094`) or `listpunches()` (`:346`), filtered by employee/date/branch.
3. "Add new punch" opens `form($empid, $att_date, $site_t_fkey)` (`:1793`) — before showing the form, the server checks whether a **leave already exists for that date** and blocks/annotates accordingly (queries against `emp_leave_transactions`/`leaveentries`, `:1806-1858`) so the UI can warn "Cannot add attendance, <leave-type> <status> on <date>".
4. Submitting a brand-new punch posts to `savenew()` (`:1899`). Server-side validation, in order:
   - Re-checks full-day leave exists for that date → if so, aborts with `{"success":false,"msg":"Cannot add attendance, Leave Exists in this Date."}` (`:1953-1957`).
   - Checks the punch date/time is not in the future → `{"success":false,"msg":"Cannot add attendance to upcoming dates."}` (`:1984-1987`).
   - Catches `RuntimeException` from the underlying save (duplicate-key constraint) → `{"success":false,"msg":"Cannot add duplicate attendance for the same date."}` (`:1967-1971,1989-1992`).
   - On success, writes to `device_attandance` (via `EditPunches->save`) and mirrors the row into `device_attandance_hist` via a hand-built `INSERT` statement (`insert_func()`, `:1879-1897` — string-concatenated SQL, worth flagging as a SQL-injection-shaped pattern to not carry into the rewrite) and returns `{"success":true,"msg":"New Attendance saved successfully"}` (`:1980-1982`).
5. `savepunch()` (`:1997`) is the edit-existing-punch counterpart (not fully traced beyond signature — same validation family expected based on shared helper usage).
6. `remove()` (`:1862`) soft-deletes a punch by setting `status = 'D'` on `EditPunches` (no confirmation server-side; UI is expected to confirm before calling).
7. `Syncame()`/`SyncAttendance()` (`:2172,2231`) synchronize punches from the external "AME" biometric/attendance device/API.

### Forms
- Add/edit punch form (`View/EmployeeRegister/form.ctp` — not fully read line-by-line, but referenced fields include employee, attendance date, in/out time, direction).
- No jQuery-Validate plugin detected; validation is server-side (see above) plus basic required-field HTML.

### Notifications
- `sendmemo()` (`:2123`) — appears to send a memo/notification (not traced in depth given breadth constraints); `App::uses('CakeEmail', 'Network/Email')` is imported at the top of the file (`:26`), suggesting CakePHP's built-in `CakeEmail` component is used here (unlike the PHPMailer-with-hardcoded-creds pattern used elsewhere in this scope) — worth confirming exact usage if this flow is in scope for migration.

### AJAX/JS endpoints
`EmployeeRegister/listpunchesnew`, `EmployeeRegister/listpunches`, `EmployeeRegister/form/:empid/:date/:site`, `EmployeeRegister/savenew`, `EmployeeRegister/savepunch`, `EmployeeRegister/remove`, `EmployeeRegister/getmonths`, `EmployeeRegister/Syncame`, `EmployeeRegister/jsons/:branch/:resigned`.

### Duplicates
`View/EmployeeRegister/editpunch_new.ctp` vs `editpunch.ctp`, `index_new.ctp` vs `index.ctp` — the controller explicitly renders the `_new` variants; the non-`_new` files appear to be superseded but not renamed to a backup convention. Flagged, not traced.

---

## 5. EmployeeManageController.php (`Controller/EmployeeManageController.php`, 150 lines)

### Purpose
A thin "landing/menu" controller for the Employee module plus a feature-flag JSON endpoint used to show/hide employee sub-features based on the tenant's subscription plan.

### Who can access it
Session gate only; `index()` is empty (`:54-56`).

### Step-by-step user flow
- `index()` — empty, presumably just a layout/menu shell for client-side navigation into the other Employee-area controllers (no server logic).
- `getEmployeeFeatures()` (`:58-149`) — AJAX JSON endpoint: looks up the tenant's `plan_id` from the central `CentralUserCredentials` table (`:66-71`), fetches all `Features` rows where `feature_key = 'employee'` and `is_common = 0` (`:79-94`), cross-references which are enabled for the plan via `PlanFeature` (`:97-103`), and — for a hardcoded list of ~16 company codes (`:113-116`) — rewrites two specific `feature_path` values (`UserAccess/indexnew` → `UserAccess`, `Asset/Create_asset_new` → `Asset/Create_asset`) before returning the feature list as JSON (`:118-148`). This is a per-tenant UI-composition endpoint (which employee sub-menu tiles to show), not an authorization boundary — it does not prevent an unauthorized user from directly navigating to a hidden feature's URL.

### Forms / Notifications / AJAX
No forms. AJAX: `EmployeeManage/getEmployeeFeatures`.

---

## 6. EmployeeConfigController.php (`Controller/EmployeeConfigController.php`, 5039 lines)

### Purpose
"Bulk Policy Allocation" console (per the view's own `<h1>`, `View/EmployeeConfig/index.ctp:81`): a large tabbed admin screen for assigning/removing employees to/from Shift, Holiday, Leave Policy, Salary Structure, Notice Period, Division, Section, Grade, Leave Hierarchy, Multishift, and reporting Hierarchy — i.e., the bulk configuration layer that sits on top of individual employee records.

### Who can access it
Session gate only; `index()` sets `user_group` into the view (`EmployeeConfigController.php:39-41`) purely for conditional UI (e.g. `payroUser` privilege flag shown only to group-2 users, `:79-82`) and reads a `plan` value from `comp_contact_info` to conditionally hide the "Back" button on a `basic` plan (`View/EmployeeConfig/index.ctp:82`). No action-level authorization gate found.

### Step-by-step user flow
1. `EmployeeConfig/index` loads the tabbed shell (`View/EmployeeConfig/index.ctp`), with each tab (Shift/Holiday/Leave/etc.) rendering two side-by-side lists: employees *not yet* assigned to a policy value, and employees *currently* assigned. A hard-coded list of ~20 "restricted" company codes (`EmployeeConfigController.php:223-227` and mirrored in the view `:97-100`) toggles which tabs are shown (e.g. Shift tab only shown for those companies or for `HDFN/HDEQ/HDSC/HDCM`).
2. Each tab follows the same **list → drag/select → add/remove** pattern, illustrated by the Shift tab:
   - `listemployeesforshift()` (`:409`) / `listemployeesinshift()` (`:321`) populate the two lists.
   - `addEmpToShift()` (`:167-297`) — accepts a comma-separated list of employee IDs plus a target `shift` policy id; for each employee it writes a row to `EmployeeConfig` (generic polymorphic table, `type = 'SHIFT'`, `:184-191`), then (for a hardcoded set of ~11 "restricted" companies, `:223-227`) runs extra data-integrity steps: deletes stale `emp_detail_timeattandance` rows for the current month not already locked into `attendance_register` (`:230-236`), and calls DB functions `time_duration_check_multishift`/`time_duration_check` to re-validate attendance duration under the new shift (`:238-257`); for other companies it instead walks each device-punch date and re-validates individually (`:258-287`). Returns `{status, emp_fkey, message}` per employee (only the last employee's response is actually echoed since `$response` is overwritten each loop iteration — likely a real bug for bulk assignment, worth flagging: with multiple employee IDs the client only ever sees the message for the final one).
   - `removeEmpFromShift()` (`:302+`) is the inverse (soft-delete pattern, not fully traced given breadth constraints).
   - The other 9 tabs (Holiday, Leave, Hierarchy, Salary structure, Notice period, Division, Section, Grade, Leave hierarchy, Multishift) each have their own `listemployeesforX`/`listemployeesinX`/`addEmpToX`/`removeEmpFromX` action quartet (full list of ~90 actions enumerated by function-name grep above); given time constraints these were not traced individually, but they follow the same shape as Shift based on naming and the shared `EmployeeConfig` polymorphic-assignment table.
3. `getEmployeeTree()` (`:112`) / `createTree()`/`createBranch()` (`:143-164`) build a parent/child org-chart tree from `EmployeeDetails.parent`, used by a separate "Hierarchy" visualization (drag-and-drop reporting-line editor) reachable from this same screen.

### Forms
No traditional forms — this is a "select from list, click Assign/Remove" UI (drag-select + toolbar buttons), consistent with the JS pattern seen in other datagrid screens throughout the app.

### Notifications
No email notifications found in this controller; feedback is JSON `{status/success, message}` surfaced via `$.notify(...)`.

### AJAX/JS endpoints (representative — Shift tab; other 9 tabs mirror this shape with different nouns)
`EmployeeConfig/listemployeesforshift`, `EmployeeConfig/listemployeesinshift`, `EmployeeConfig/addEmpToShift`, `EmployeeConfig/removeEmpFromShift`, `EmployeeConfig/getEmployeeTree`.

---

## 7. EmployeeMenuController.php (`Controller/EmployeeMenuController.php`, 305 lines)

### Purpose
Serves the navigation menu and "addon" feature tiles for the **employee (self-service) side** of the app — the ESS equivalent of `EmployeeManageController::getEmployeeFeatures()`.

### Who can access it
Session gate only. `index()`/`addon()` just set `emp_pkey` from session (`:23-30`) for the view shell.

### Step-by-step user flow
1. `EmployeeMenu/index` and `EmployeeMenu/addon` render the ESS landing shells (`View/EmployeeMenu/index.ctp`, `addon.ctp`).
2. `getDefaultMenus()` (`:36-81`) — AJAX JSON: joins `emp_menu` (aliased `EmployeeMenu`) to `user_access` on `menu_id`, filtered to the current employee (`user_fkey = emp_fkey`), `active = 'Y'` on both, `parent_id != 0`, `is_default = 'Y'` — returns the menu tree the employee is *entitled* to see, ordered by name. **This is the per-menu `user_access` data referenced in the CONTEXT** — confirmed here it drives what renders in the nav, not what a controller will execute; the earlier "no server-side enforcement" finding for the other 14 controllers stands.
3. `getAddonMenus()` (`:197-287`) — similar but pulls from `user_feature_branch_access` (tenant DB) joined against the central `Features` table, grouped by `feature_key`; contains a company-specific override (feature_key `81` remapped to `AttendanceRegisterNew/indexneww` unless the company is in a ~16-item restricted list, `:260-271`).
4. `setFeatureSession()` (`:288-305`) — writes/clears `current_feature_id` into session so subsequent screens know which "addon" context the user is in; supports both GET and POST.

### Notifications / Forms
None.

### AJAX/JS endpoints
`EmployeeMenu/getDefaultMenus`, `EmployeeMenu/getAddonMenus`, `EmployeeMenu/setFeatureSession`.

### Duplicates
`View/EmployeeMenu/addon.ctp#backup_bindhu_01_04_2026`, `index.ctp#backup_bindhu_01_04_2026` — noted, not traced.

---

## 8. EmployeeHierarchyController.php (`Controller/EmployeeHierarchyController.php`, 199 lines)

### Purpose
Standalone reporting-hierarchy (org chart) editor: list employees, list an employee's direct reports, and save a drag-and-drop-edited hierarchy tree back to the DB. Functionally overlaps with the "Hierarchy" tab inside `EmployeeConfigController` (both write parent/child relationships) — worth reconciling into one flow in the rewrite.

### Who can access it
Session gate only; no per-action checks at all in this controller (confirmed by full read — no `Session->read('user_group')` calls anywhere in the file).

### Step-by-step user flow
1. `listemployees()` (`:32-55`) returns all active employees `{id, data:[name]}` for the tree widget's root list.
2. `listchildEmployee()` (`:57-87`) / `listemployeesforhierarchy()` (`:89-124`) return, respectively, the children of a given `parentEmpKey` and the employees *not yet* under that parent (candidates to add).
3. `add()` (`:126-141`) / `remove()` (`:143-157`) — given a comma-separated `id` list and a `parent`, insert/delete rows in `EmployeeStructure` (a dedicated parent/child mapping table, separate from `EmployeeDetails.parent` used by `EmployeeConfigController::getEmployeeTree`) — **note this is a second, structurally different hierarchy data source from the one `EmployeeConfigController` uses**, a discrepancy to resolve before migrating.
4. `saveData()`/`saveHeirarchy()` (`:158-183`, recursive) — takes a full JSON tree posted from the client (presumably after a drag-and-drop reorg) and recursively writes `EmployeeDetails.parent` for every node — this path writes to the *other* hierarchy field (`emp_details.parent`), meaning this single controller maintains **both** of the app's two parallel hierarchy representations depending on which action is called. Returns `{"success":true,"msg":"Employee Hierarchy Successfully Saved!"}` (`:169-171`).

### Forms / Notifications
No traditional form; no notifications.

### AJAX/JS endpoints
`EmployeeHierarchy/listemployees`, `listchildEmployee`, `listemployeesforhierarchy`, `add`, `remove`, `saveData`.

### Views
`View/EmployeeHierarchy/` directory is **empty** — no `.ctp` files exist, meaning this controller's JSON endpoints are consumed by a view rendered from elsewhere (likely embedded inside `EmployeeConfig/index.ctp`'s tree UI or a shared JS widget) rather than having its own page.

---

## 9. EmployeeUnderController.php (`Controller/EmployeeUnderController.php`, 590 lines)

### Purpose
A branch-scoped variant of the Employee setup screen — "Employees Setup" page restricted to the logged-in user's own branch (`View/EmployeeUnder/index.ctp:28`), used to add/edit employees within one's own branch/site.

### Who can access it
Session gate only. `index()` (`:55-83`) always resolves the current session employee's branch via a SQL join (`emp_proff`→`branches`, `:64`) and only ever offers that one branch in the branch dropdown (`:67-73`) — this is a **structural** restriction (only one branch is ever loaded into the UI), not a permission check, so it applies uniformly regardless of `user_group`.

### Step-by-step user flow
1. `EmployeeUnder/index` shows a branch-scoped employee datagrid bound to `listemployees()` (`:144-221`), with a branch dropdown (pre-populated to the user's own branch only), an employee-name autocomplete (`getautocompletions()`, `:114-139` — via `easyAutocomplete` widget, `View/EmployeeUnder/index.ctp:230-249`), and a designation filter.
2. "New"/"Edit" open `EmployeeUnder/setup/:emp_pkey` (`:223-315`) in a large modal (`showLargeModalForm`) — same personal/professional/tax-heads/policy-lookup loading pattern as `EmployeeController::setup()`, but always re-derives and force-sets the branch dropdown to the user's own branch (`:302-314`), so branch cannot be changed away from the current user's branch via this screen.
3. Save goes to `saveemployeesetup()` (`:449-588`) — simpler than `EmployeeController::saveemployeesetupnew()` (no duplicate id-card/lwf checks here); switches on `model` (`EmployeeDetails` vs `EmployeeProfessionalDetails`), force-sets `branch_code` to the session user's branch (`:463-464,469`), and on first insert auto-creates a `UserCredentials` row exactly as `EmployeeController` does (device-linked vs manual ID generation depending on `punch_type`, `:479-557`). Returns `{success, pkey, message}`.
4. `getstages($site_pkey)` (`:85-112`) returns an HTML `<option>` list of employees for a given branch (used to populate a dependent dropdown server-side rather than client-side).

### Forms
Same shape as `Employee/setup.ctp`, minus the id-card/lwf duplicate checks found in the main controller — a gap to note (this screen currently allows duplicate ID cards where the main Employee screen blocks them).

### Notifications
None found.

### AJAX/JS endpoints
`EmployeeUnder/listemployees`, `getstages/:branch`, `getautocompletions`, `setup/:emp_pkey`, `saveemployeesetup`, `showtaxheaddetail/:emp/:tax`, plus (from the view) `Employee/uploadandsaveempdetails/:branch` and `Employee/downloadempdataformat` — note the "Download Employee Data Format" toolbar button in `View/EmployeeUnder/index.ctp:318` actually calls the **`Employee` controller**, not `EmployeeUnder`, i.e. cross-controller endpoint reuse to preserve when splitting these into separate services/routes.

---

## 10. EmployeeResignationController.php (`Controller/EmployeeResignationController.php`, 2884 lines) — labeled "Remove Employee" in the UI

### Purpose
End-to-end offboarding: initiate a resignation/termination record, validate that attendance/payroll prerequisites are met, process the "Full & Final" settlement (leave encashment, loan balance, asset damage recovery), generate/download/email the settlement slip.

### Who can access it
Session gate only; no per-action authorization beyond `user_group`-driven branch scoping (e.g. `form()` restricts the employee dropdown to the caller's branch for `user_group == 2` at companies `GLET`/`ABSG`, `EmployeeResignationController.php:142-157`).

### Step-by-step user flow
1. `EmployeeResignation/index` (`:59-65`) shows the "Remove Employee" datagrid (`View/EmployeeResignation/index.ctp`), bound to `listemployees()` (`:1814`). Toolbar actions: "Separate An Employee" (opens the termination form), "Process Full & Final" (only enabled once separated), "Edit", "Delete", "View Slip" (enabled only once `statuses == '2'`, i.e. fully processed), and conditionally "F&F Email Slip" (only for company codes `KWMT`/`DEMO`/`GLET`, `View/EmployeeResignation/index.ctp:241`).
2. **Step 1 — Separate an employee**: modal loads `EmployeeResignation/form/0` (`:107-168`) listing eligible employees (excludes anyone already in an active `termination` row, `:165`) with a `Reason` dropdown (17 fixed values: Resignation, Absconding, Dismissed, Retirement, Retrenchment, Permanent Disabilities, End of Contract, Death, Death In Service, Personal, Relocation, Cessation×4, Superannuation, Left Service, Termination, Other — `View/EmployeeResignation/form.ctp:42-104`), submitted-date, applied-date, and (read-only, auto-computed from `notice_days`) notice period / last-working-date fields.
3. Submits to `Terminate()` (`:2381-2431`): wraps the save in try/catch for `ErrorException`/`Exception`/`mysqli_sql_exception` (all three silently swallow the message — a rewrite gap, since `$e` is echoed in the response but never actually holds a caught message on the happy path), saves to `Termination` model, and always returns `{"success":1,"msg":"Successfully Terminated Employee","error":$e}` regardless of whether the save actually succeeded (`!$this->Termination->save(...)` only throws internally and is caught — the outer response line runs unconditionally) — **flag: the client cannot currently detect a save failure from this endpoint's response shape.**
4. **Step 2 — Process Full & Final**: `setup($terminate_pkey)` (`:1985-2380`, the largest single action found in this scope) is loaded inline into the index page (not a modal) and runs a long chain of **pre-condition checks**, each of which `die()`s with an inline HTML error `<div class="callout callout-danger">` if it fails (i.e., these are server-rendered blocking errors, not JSON):
   - Cannot process before the `last_approved_working_date` (`:1996-1999`).
   - Cannot re-process if already processed (checks `emp_details.status = 2` combined with existing settle-slip rows, `:2006-2011`).
   - Employee must have a Shift policy and Salary structure allocated (`:2012-2020`).
   - All attendance months between resignation and last working date must be "processed" in `attendance_register` (`:2033-2046`) and fully approved (no `isdelete='Y'` rows) — with a special-cased alternate check for company `ABSG` using a `site_attendance_register` table (`:2070-2094`).
   - If all checks pass, computes: active loans and next-EMI-due summary (`:2100-2104`), damaged/unreturned assets (`:2097`), resets any stale `emp_settle_slip` rows for this employee (`:2111-2119`), and a large block of date-math to determine attendance windows, notice-period working-days, and week-off/holiday day counts feeding the settlement calculation (`:2121-2234`+).
5. `Viewslip($emp_pkey)` (`:1216`) / `downloads(...)` (`:1440`) — view/download the generated Full & Final settlement slip.
6. `sendEmail()` (`:2600-2664`) — **email notification**: generates the final-slip PDF (`generateFinalSlipPDF()`, `:2666`), emails it as an attachment via PHPMailer to the employee's own email on file, BCC to `projects@greatleap.tech` (`:2631`), using the same hardcoded SMTP account seen in `EmployeeJoinController::sendOnboardingMail()` (`:2621-2626`). Returns `{status:'success'|'danger', message}` consumed by `$.notify`.
7. `DeleteEmployeeResignation()` (`:1880`) — deletes/reverts a resignation record (e.g. if initiated by mistake or by the employee via self-service).

### Forms
- Separation form (`form.ctp`, described above) — required: employee, reason, submitted date, applied date; notice period is read-only/derived; last-working-date is computed "as per notice days" but appears editable/approvable separately (`applied_date` vs `apprved`/last-working fields both present in `Terminate()`'s payload, `:2397-2404`).
- No jQuery-Validate; relies on HTML5 `required` plus the extensive server-side precondition checks in `setup()`.

### Notifications
- Email: settlement-slip PDF to the resigning employee (`sendEmail()`), triggered manually by an HR user clicking "F&F Email Slip" (only offered for 3 company codes).
- All other feedback is `$.notify` toasts driven by JSON responses, except the `setup()` precondition failures which are server-rendered HTML `die()` blocks injected directly into the page (a different UX pattern from the rest of the app worth normalizing in the rewrite).

### AJAX/JS endpoints
`EmployeeResignation/listemployees`, `form/:emp_pkey`, `Terminate`, `setup/:terminate_pkey`, `DeleteEmployeeResignation/:terminate_pkey/:emp_pkey`, `Viewslip/:emp_pkey`, `sendEmail`, `getperiod/:emp_fkey`.

### Duplicates
`Controller/EmployeeResignationControllerBkup.php` exists as a full backup copy — noted per instructions, not traced. Numerous `#bkup_*`/`#backup_*` view files under `View/EmployeeResignation/` (approves, download, form, index, metro_download, metro_slip, salaryslip, setup) — noted, not traced; canonical files used above are `index.ctp`, `form.ctp`, `setup.ctp` (no `#` suffix).

---

## 11. NoticePeriodController.php (`Controller/NoticePeriodController.php`, 250 lines)

### Purpose
Simple master-data CRUD: manage the list of notice-period options (days + description) used elsewhere (e.g. the resignation form's notice-period lookup, and `Employee/setup.ctp`'s `notice_days` dropdown).

### Who can access it
Session gate only.

### Step-by-step user flow
1. `NoticePeriod/index` (`:59-62`) renders a datagrid bound to `listNotice()` (`:64-87`, paginated, filtered to `status=1`).
2. "New"/"Edit" open `form/:notice_pkey` (`:89-109`) — a modal with `notice_days` and `description` fields; when editing an existing record with data present, the `notice_days` field is rendered **disabled** with a parallel hidden input carrying the real value (`View/NoticePeriod/form.ctp:26-38` — i.e., days cannot be changed once set, only description can, mirroring the code comment "the grade code cannot change when edit... employee table fields take grade code as key").
3. On blur, two live-uniqueness AJAX checks fire: `checknotice_periodexists()` (`:111-127`, checks `description` uniqueness) and `checknotice_periodcodeexists()` (`:129-145`, checks `notice_days` uniqueness) — both alert() the user inline and clear the field if a duplicate is found (`View/NoticePeriod/form.ctp:72-94,102-123`).
4. Submit posts to `savenotice_period()` (`:147-203`): re-validates both uniqueness constraints server-side (duplicate `notice_days` blocks only on **insert**, `:167-174`; duplicate `description` always blocks, `:176-183`), and if unique, either raw-SQL `UPDATE`s (string-concatenated, not parameterized — `:189-193`) or calls `->save()` for a new row. Returns `{success, msg}`.
5. `deleteNotice($notice_pkey)` (`:205-234`) — soft-delete (`status=0`), always returns `{"success":1,"msg":"Deleted Successfully"}` regardless of whether a row actually matched.

### Forms
`notice_days` (numeric, required, immutable after creation), `description` (text, required, unique).

### Notifications
None (in-app toasts only).

### AJAX/JS endpoints
`NoticePeriod/listNotice`, `form/:id`, `checknotice_periodexists/:id`, `checknotice_periodcodeexists`, `savenotice_period`, `deleteNotice/:id`.

---

## 12. EmployeeEmiController.php (`Controller/EmployeeEmiController.php`, 1130 lines)

### Purpose
Monthly EMI (loan installment) processing screen: for a chosen month, list employees with an active loan whose EMI is due, let HR bulk-upload actual paid EMI amounts via Excel (or add one manually), and track balance/paid/tenure per loan.

### Who can access it
Session gate only. `index()` (`:55-82`) and `getEmployee()` (`:228-324`) both contain a **branch-restriction rule specific to two companies**: for `user_group == 2` at company `GLET` or `ABSG`, a DB function `get_branch_code_abs_fn(emp_pkey)` resolves whether the employee is "Head Office" (`is_ho`); if not HO, the employee list/branch filter is forced to that employee's own branch (`:67-78,261-271`). This is the same pattern seen in several other controllers in this scope (GLET/ABSG special-cased branch scoping recurs across EmployeeResignation, EmployeeUnder-adjacent screens, etc.) — worth extracting into one shared rule in the rewrite instead of re-implementing per controller.
- Otherwise, no gate beyond session check.

### Step-by-step user flow
1. `EmployeeEmi/index` renders `View/EmployeeEmi/index.ctp`: a "Choose Month" dropdown (last 12 months, defaulting to next month, `:38-49`), branch dropdown, and an employee `select2` autocomplete backed by `EmployeeEmi/jsonsb/:branch` (`:126-159`).
2. Grid loads via `employeelist()` (`:489-640`) — a large raw-SQL join across `emp_details`, `user_credentials`, `emp_loan`, `emp_proff`, `emp_loan_info`, `employee_info`, computing `paid_amount`/`balance_due` per loan with correlated subqueries against `emi_upload`, filtered to `is_completed='N'` and the selected month. Includes non-trivial in-PHP EMI-adjustment logic: if the selected month equals the loan's `emi_end_month`, the displayed EMI is recalculated as `emi_amount - (emi_amount*tenure - loan_amount)` (i.e., the final installment is trued-up to not overshoot the loan amount), and separately capped so `emi_amount` never exceeds `balance_due` (`:593-619`).
3. "New" opens `emi_upload/:month` modal (`:84-181`) — lets HR record a single EMI payment for an employee against a specific loan (loan dropdown populated via `getEmi()` `:183-227`, which also computes and returns the loan's current `sum`/`balance`/`loan` amounts to prefill the form).
4. Submitting one EMI record posts to `employeeemiloansave()` (`:384-425`): computes `loan_balance = balance_due - emi_amount`; if negative, blocks with `{"success":0,"msg":"EMI amount should not be greater than balance amount"}` (`:416-424`); otherwise saves to `LoanEmi` and returns success.
5. Bulk path: "Download Format" → `downloademploanformat(...)` (`:642-854`) generates an `.xlsx` template (via PHPExcel) pre-populated with each due employee's loan/EMI/balance data plus a `Random` anti-replay token column and the current `EMI month`. "Upload EMI File" → `uploadEmployeeCTC()`/`uploadandsaveempemi(...)` (view JS `:217-327`; controller `:856-1088`) re-parses the uploaded workbook:
   - Validates mandatory columns (`Employee ID`, `Employee Name`) are non-blank per row, else aborts with `{"success":0,"msg":"Please check all mandatory fields entered"}` (`:908-913`).
   - **Replay-prevention check**: compares a client-generated random token (`icheck`) against the value embedded in the downloaded template; if they don't match on a repeat submit, blocks with `{"success":5,"msg":"This excel file already uploaded make sure upload new one"}` (`:961-964`) — this is a client-supplied anti-double-submit token, not a server-tracked idempotency key, so it's only a soft guard.
   - Per row, resolves `emp_fkey` from `Employee ID` via `UserCredentials`; skips rows with no matching loan key or blank EMI amount (`:980-985`).
   - **Overpayment guard**: if cumulative `paid > loan_amount` for any employee, that employee's name is appended to a message list and the row is skipped (not saved) rather than aborting the whole batch (`:1034-1041`); after the loop, if any rows were skipped this way, the response is `{"success":3,"msg":<names>,"data":<names>}` and the client `alert()`s the list (`view :287-298`) — meaning **the successfully-saved rows are NOT rolled back**, only the offending ones are skipped; the user only finds out which employees were skipped after the fact.
6. `jsons()`/`jsonsb()` (`:426-466,1089-1130`) — employee-search autocomplete endpoints feeding the `select2` widgets, with a numeric-vs-name search heuristic (`is_numeric($q)` searches `emp_company_id`, else searches `first_name`).

### Forms
- Single EMI entry: loan (dependent dropdown), EMI amount, month — validated against `balance_due` server-side only.
- Bulk upload: Excel with columns `Employee ID, Employee Name, Loan Pkey, EMI Amount, EMI month, Loan Amount, Paid Amount, Random` — validated as described above.

### Notifications
None found (JSON + `$.notify`/`alert()` only).

### AJAX/JS endpoints
`EmployeeEmi/index`, `emi_upload/:month`, `getEmi`, `getBalance`, `checkmonth/:from/:loan`, `employeeemiloansave`, `jsons/:branch`, `jsonsb/:branch`, `salarycheck`, `employeelist`, `downloademploanformat/...`, `uploadandsaveempemi/:type/:icheck/:clickcount`, `getEmployee/:month`.

### Duplicates
`View/EmployeeEmi/index.ctp#bkup_*` (3), `emi_upload.ctp#bkup_amal_06_03_2020` — noted, not traced.

---

## 13. DocumentManagerController.php (`Controller/DocumentManagerController.php`, 2237 lines) and DocumentManagersController.php (2713 lines, duplicate)

**`DocumentManagersController.php` is a parallel/forked copy of `DocumentManagerController.php`** (confirmed via `diff` on file headers — same license header, same base structure, diverges immediately; different line count, 2713 vs 2237). Per instructions this is treated as a duplicate and not separately traced — all behavior below is documented against the canonical `DocumentManagerController.php`. Note both are wired into routing (default routing means both `/DocumentManager/...` and `/DocumentManagers/...` URLs work independently) — worth confirming with the team which one is actually linked from the live nav menu before deciding which to port.

### Purpose
Two related sub-features under one controller: (a) a document-template engine (CKEditor-based templates with placeholders, e.g. offer letters/policy docs, that can be rendered per-employee and previewed/downloaded as PDF via Dompdf), and (b) a plain file-upload/document library (upload arbitrary PDFs, allocate them to specific employees, list/preview/delete).

### Who can access it
Session gate only; `documentMaster()` (`:1149-1154`) reads `user_group` purely to pass into the view for conditional UI, and reads `current_feature_id` from session (set by `EmployeeMenuController::setFeatureSession()`) to know which "addon" context launched this screen. `uploadForm()` (`:1156-1227`) branches significantly by `user_group`: admins (`!= 2`) get the full active-employee list; employees (`== 2`) instead get three role-scoped employee lists (`auth_employees`, `apr_employees`) computed via DB functions `leave_auth_apr_person_fn(company_code, emp_pkey, 'auth'|'api')` — i.e., who the current employee is allowed to route an expense/document approval to, based on the leave-approval hierarchy (reused here for document approval routing) — this is the closest thing to a real authorization rule found in this controller, and it's data-scoping, not an action block.

### Step-by-step user flow

**(a) Document templates**
1. `DocumentManager/documentMaster` — index/list of templates (`View/DocumentManager/index.ctp`, titled "Document Master"), grid bound to `getTemplates()` (`:89-122`, paginated, name-filterable).
2. "New"/"Edit" → `form/:template_pkey` (`:68-88`) — CKEditor rich-text template body plus `placeholders` (comma list), `policy` flag, `availability`, `editable` flags.
3. Save → `saveTemplate()` (`:123-187`): on **add**, also auto-creates a `Documents` row if `policy == 1` (i.e., "policy" templates immediately materialize as a company-wide document, `emp_fkey=0`, with a generated `doc_id` like `Doc_1000<pkey>`, `:137-155`); on **edit**, merges newly-added placeholder tokens into the existing placeholder list rather than overwriting it (`:166-181` — union of old+new placeholders is preserved even if some were removed from the template body, a potential UX mismatch to resolve in the rewrite — stale placeholders can accumulate).
4. `deleteTemplate()` (`:188-201`) — soft delete via `$_GET['ids']` (note: GET-based delete, no CSRF-style confirmation server-side).
5. `createDocument()` (`:202-205`) — the template-fill screen: pick a template, fill in placeholder values, generate a document for a specific employee/company/branch/supplier/customer context (`getData()`, `:310+`; `getPlaceholder()`, `:792`; `saveDocument()`, `:889`).
6. `docPreview($document_id, $preview)` (`:331-791`, the single largest action in the file) renders the filled template as an HTML/PDF preview.
7. `sendemailtemplate($emp_fkey, $type='birthday')` (`:1795-1847`) — **email notification**: renders a named template (e.g. birthday wishes) for an employee and sends it (this is the underlying implementation used by the "Send Wishes" feature referenced — but currently commented out — in `EmployeeResignation/index.ctp:300-329`).
8. Image support for templates: `addimage()` (`:206-212`) lists up to the last 32 uploaded images for the company; `deleteImage($pkey)` (`:213-226`) soft-deletes one.

**(b) File upload / allocation**
1. `documentAllocate($site_pkey)` (`:1230-1281`) — two-list UI: employees not yet allocated a given uploaded document vs. those who are; `save_allocate()` (`:1284+`) persists the allocation.
2. `documentUpload()` (`:1391-1454`) — raw file upload (expects `$_FILES['pdfFile']`): validates `UPLOAD_ERR_OK`, generates a sanitized unique filename (`preg_replace('/[^\w.-]/','_', ...)`, `:1420-1421`), stores it under `document/file/<COMPANY_CODE>/`, and inserts a `DocumentUpload` row with detected MIME type (via `finfo`). Returns `{status:1, message, filename}` on success or `{success:false, message}` on failure (note the **inconsistent success-key naming** between the success and failure branches — `status` vs `success` — a bug for any client code that only checks one key).
3. `getDocumentsFromDatabase()` (`:1458+`) — paginated list of uploaded documents for the grid.
4. `documentPreview($fileKey)` / `openFileInNewTab()` / `viewDocument($documentKey)` (`:1572,1610,1699`) — various document viewing endpoints.
5. `deleteDocumentFromGrid()` / `remove_allocate()` (`:1634,1670`) — delete/unallocate.

### Forms
- Template form: `template_name`, `template_content` (CKEditor), `placeholders`, `policy`, `availability`, `editable` — client-side validation function `validateTemplateForm()` exists in the view (`View/DocumentManager/index.ctp:72+`, checks template name and editor content are non-empty; not fully traced beyond the opening lines).
- Upload form: file input plus (for employee/group-2 users) expense-type and approver/authorizer dropdowns pulled from the leave-approval hierarchy functions described above.

### Notifications
- Email: `sendemailtemplate()` — templated email (e.g. birthday) sent to an employee, driven by the document-template engine rather than a fixed HTML string like the other controllers in this scope.

### AJAX/JS endpoints
`DocumentManager/getTemplates`, `saveTemplate`, `deleteTemplate`, `form/:id`, `addimage`, `deleteImage/:id`, `getDocuments`, `docPreview/:id/:preview`, `getPlaceholder`, `saveDocument`, `deleteDocument`, `getEmployees`, `getCompanies`, `getBraches`, `getContacts`, `getTemplatePlaceholers/:id`, `savefile`, `documentMaster`, `uploadForm`, `documentAllocate/:site`, `save_allocate`, `documentUpload`, `getDocumentsFromDatabase`, `documentPreview/:key`, `deleteDocumentFromGrid`, `remove_allocate`, `sendemailtemplate/:emp/:type`.

---

## 14. ContactsController.php (`Controller/ContactsController.php`, 145 lines)

### Purpose
Simple master-data CRUD for company Customers/Vendors ("Contacts") — name, address, banking details, relationship type.

### Who can access it
Session gate only; no per-action checks.

### Step-by-step user flow
1. `Contacts/index` — datagrid (`View/Contacts/index.ctp`) bound to `listcontacts()` (`:80-134`, paginated, filterable by free-text search across name/company/email/phone/city/relationship, and by `relationship`).
2. "New"/"Edit" → `addeditcontacts($contact_id=0)` (`:50-64`) — modal form (`View/Contacts/addeditcontacts.ctp`) with required fields: `company_name`, `address`, `city`, `state`, `pincode` (numeric), `email`, `relationship` (dropdown: Vendor/Customer/Others), `phone` (numeric), `pan_no`, `gst`, `bank_name`, `bank_branch`, `ifsc_code`, `account_no`, `first_name` (contact person). Optional: `reg` (registration no.), `tin` (TAN), `c_designation`.
3. Client-side: `$.validate({form: '#form-contacts-master'})` (jQuery-Validate-style plugin, `View/Contacts/addeditcontacts.ctp:2-4`) plus HTML5 `required`/`data-validation="number"|"email"` attributes.
4. Submit → `save()` (`:69-78`) — no server-side validation of required fields found (relies entirely on client-side); attaches the org's `organization_id` automatically (`:74-75`) and saves. Returns `{"msg":"Contacts saved successfully"}` unconditionally (no success/failure differentiation).
5. `deletecontacts($contact_id)` (`:135-144`) — soft delete (`status=0`).

### Forms
As listed above; client-only validation, no server-side re-validation of required fields or formats.

### Notifications
None.

### AJAX/JS endpoints
`Contacts/listcontacts`, `addeditcontacts/:id`, `save`, `deletecontacts/:id`.

---

## 15. BeneficiaryController.php (`Controller/BeneficiaryController.php`, 375 lines)

### Purpose
Near-identical twin of `ContactsController` for a separate "Beneficiary" master list (e.g. statutory/nominee/payment beneficiaries), plus its own Excel export/import pair (absent from `ContactsController`).

### Who can access it
Session gate only.

### Step-by-step user flow
1. `Beneficiary/index` → grid bound to `listcontacts()` (`:80-133` — same method name and shape as `ContactsController::listcontacts()`, operating on the `Beneficiary` model instead of `Contacts`).
2. `addeditcontacts($contact_id=0)` (`:50-64`) — same field set as Contacts (company_name, reg, address, city, state, pincode, email, relationship, phone, tin, pan_no, gst, bank_name, bank_branch, ifsc_code, account_no, first_name, c_designation) — view file not separately re-read (identical structure to Contacts' form confirmed by matching controller field list) but flagged as effectively the same form duplicated per-model rather than parameterized — a strong candidate for consolidation in the rewrite (one generic "Contacts" entity with a `type` discriminator instead of two parallel controllers/tables/views).
3. `save()` (`:69-78`) / `deletecontacts()` (`:134-143`) — identical pattern to Contacts.
4. **Beneficiary-specific**: `downloadempctcformat(...)` (`:144-225`) exports the beneficiary list to `.xlsx` via PHPExcel with a fixed 20-column header (Sl No, Beneficiary Name, Code, Reg No, Address, City, State, Pin Code, Email ID, Relationship, Phone, TAN, PAN No, GST No, Bank Name, Branch, IFSC Code, Account No, Contact Person Name, Designation). `uploadandsaveempctc($ctcuploadtype=0)` (`:227-374`) re-imports that file: validates only `Beneficiary Name` as mandatory per row (`:253`), skips exit-on-blank-mandatory only if the row's mandatory cell is empty (`:262-265`, then hard-exits the whole request rather than skipping just that row — `break 2` at `:264`, so **one bad row aborts the entire import with no partial save**, unlike the row-skipping approach seen in `EmployeeEmiController`'s bulk uploader), then `saveAll()`s each row unconditionally (no duplicate-detection).

### Forms
Same field set as Contacts (see above); bulk Excel import as described.

### Notifications
None.

### AJAX/JS endpoints
`Beneficiary/listcontacts`, `addeditcontacts/:id`, `save`, `deletecontacts/:id`, `downloadempctcformat/...`, `uploadandsaveempctc/:type`.

---

## Cross-cutting issues to carry into the Next.js rewrite

1. **Authorization model**: confirmed again for this whole feature area — the only real gate is session `user_group` (1/2) checked once at `AppController::beforeFilter`. `user_access`/`menu_id` (used in `EmployeeMenuController`) and `Features`/`PlanFeature` (used in `EmployeeManageController`, `EmployeeMenuController`) only control *what renders in navigation*, never what a controller action will execute. Any employee-group user who knows/guesses an admin URL in this scope can generally still call it; the few exceptions found are inline, ad hoc, and inconsistent (`EmployeeController.php:598,872`; branch-forcing in `EmployeeUnderController`/`EmployeeEmiController`/`EmployeeResignationController` for specific company codes). The rewrite should implement real per-route authorization rather than porting this pattern.
2. **Hardcoded SMTP credentials** appear verbatim in at least two controllers (`EmployeeResignationController.php:2621-2626`, `EmployeeJoinController.php:9950-9951`) — must not be committed to the new codebase; move to secrets/env config.
3. **Two independent, inconsistent employee-hierarchy data models** exist (`EmployeeStructure` table used by `EmployeeHierarchyController::add/remove`, vs. `emp_details.parent` used by `EmployeeHierarchyController::saveData` and `EmployeeConfigController::getEmployeeTree`) — needs a single source of truth.
4. **Company-code-specific branching** (`GLET`, `ABSG`, `VGFS`, `VSFS`, `KWMT`, `DEMO`, and various ~11–20-item "restricted company" lists) recurs across nearly every controller in this scope for both branch-scoping and feature-visibility. This is effectively unmodeled tenant configuration hardcoded into application logic — a strong candidate to externalize into real per-tenant config/feature flags in the rewrite rather than `if ($company_code == '...')` chains.
5. **Response-shape inconsistency**: some endpoints use `{success: bool}`, others `{status: 'success'|'danger'|1}`, others always return success regardless of outcome (`EmployeeResignationController::Terminate()`, `NoticePeriodController::deleteNotice()`, `ContactsController::save()`, `BeneficiaryController::save()`) — no uniform API contract to preserve; the rewrite should standardize rather than copy this.
6. **Bulk-import error handling is inconsistent between features**: `EmployeeEmiController`'s bulk EMI upload skips only the offending rows and reports names after the fact (partial success, no rollback); `BeneficiaryController`'s bulk import aborts the entire batch on the first bad row (`break 2`) with no partial save. Product should decide one behavior for the new implementation.

## Duplicate/backup files noted but not traced (per instructions)
`EmployeeController_old_before addnominee.php`, `EmployeeResignationControllerBkup.php`, `DocumentManagersController.php` (treated as the DocumentManager duplicate), plus dozens of `*.ctp#bkup_*` / `*.ctp#backup_*` / `*_old.ctp` view files under `View/Employee/`, `View/EmployeeConfig/`, `View/EmployeeMenu/`, `View/EmployeeResignation/`, `View/EmployeeEmi/`, `View/DocumentManager*/` (full list visible via directory listing, omitted here for brevity — none were opened or traced).

---

## 3. Attendance & Time

# Attendance & Time — Legacy Behavior Report

Scope: `legacy/Controller/*Attendance*`, `EditPunches*`, `Empeditpunches`, `DailyActivity`, `DailyOvertimeVerify*`, `OtAttendance*`, `Overtime`, `Regularisation`, `ShiftPlanner`, `ScheduledBreakOff`, `Compoff`, `HolidayCalendar`, `DayTimeProcedure`, `MobileLocationUpdate`.

## Cross-cutting findings (read this first)

1. **No custom routes.** `legacy/Config/routes.php` has no entries for any controller in this cluster — a targeted grep for every controller name returned nothing. All navigation uses default CakePHP `/Controller/action` routing, or (increasingly, in newer code) AJAX/JS `.load()` calls into a `#container` div — this app is transitioning toward an SPA-like shell (see finding 4).
2. **Auth confirmed as described.** `Controller/AppController.php:39-46` — every request reads `Session->read('user_group')`; anything other than `1` or `2` redirects to `Site/login`. No controller in this cluster overrides `beforeFilter()` to add its own gate. No `AuthComponent`/ACL usage found anywhere in this cluster (`Config/acl.php` is not referenced by any controller here).
3. **No per-controller `menu_id`/`user_access` gate found in this cluster.** Every controller in scope was grepped for `Useraccess`, `menu_id`, `user_access`. The only hits are in `AttendanceRegisterNewController.php:499,1480,1528` and `Controller/SiteController.php:371-396`, and in both cases the query is `... where menu_id = '0' ...` — this is the *hierarchy/admin-dashboard* flag checked once at login (decides whether a user_group=2 login lands on `Dashboard/hierarchydashboard` vs `Dashboard/empdashboard`), not a per-feature permission check. **In practice, gating for this whole cluster is coarse-grained**: `user_group == 1` (Admin/HR) vs `user_group == 2` (Employee/ESS), branching inside each action to restrict data scope (own records vs all), plus whether the menu item is shown at all (menu visibility is data-driven from `emp_menu`/`Features` tables, rendered client-side — see finding 4). Actions are **not** blocked server-side if a user_group=2 employee guesses the URL of an admin action; they simply get admin-shaped queries running under their own session context (usually filtered by their own `emp_fkey` due to `if ($user_group == 2) {...}` branches, but this is inconsistent action-by-action and should be treated as "INFERRED: weak/no server-side authorization" for migration purposes).
4. **New plan/feature-gating layer (2025/2026 additions).** `Controller/AttendanceSetupController.php:17-103` (`getAttendanceFeatures`) reads a `Features`/`PlanFeature` table pair from the `controldb` datasource, keyed by `feature_key = 'Attendance'`, and returns per-company enabled/disabled feature tiles with a `feature_path` (e.g. `AttendanceRegisterNew/indexneww`). `View/AttendanceSetup/index.ctp:196-327` renders these as clickable tiles; clicking an enabled tile does `$("#container").empty().load(url)` (line 305-312) to swap in the target controller/action's HTML, and clicking a disabled tile shows an "Upgrade to Next Plan" CTA that loads `User/profile` (line 315-325). This is the **primary discoverable admin entry point into the whole Attendance area** in the current build. Notably `AttendanceSetupController.php:79-91` hard-codes company-code exceptions: for company codes in `$not_allowed_companies` (KWMT, ABSG, MBCT, DRRC, SRTS, MRBS, DJIC, STCL, SHYD, AGNG, ESNP, GTRA, VGNN, AYRK, VGFS, VSFS), `Regularisation/adminindexnew` is swapped for `Regularisation/adminindex`, and `AttendanceRegisterNew/indexneww` is swapped for `AttendanceRegisterNew/index`. This is strong evidence that `adminindexnew`/`indexneww` are the **current default/canonical** actions and `adminindex`/`index` are **legacy fallbacks kept alive only for specific old customers**.
5. **Massive duplication across this cluster is real, not a false pattern.** Several controllers implement near-identical action sets (`showregister`, `showregistertab`, `listregisterentries`, `listverifiedregisterentries`, `processregisterentries`, `verifyregisterentries`, `loadattendanceregisterheader`, `checkifregistercanverify`, `updateregisterentries`, `submitregisterentry`, `createDateRange`, `createDateRangeArray`) appear verbatim (same names, same order) in: `AttendanceController.php`, `AttendanceregisterController.php`, `AttendanceRegisterNewController.php`, `EmpattendanceController.php`, `EmployeeAttendanceregisterController.php`. This is consistent with the file being copy-pasted forward each time a "new" version was needed rather than refactored. Deduping these into one canonical register-book flow (admin vs employee view via a scope flag, not a whole new controller) is probably the single highest-leverage migration simplification in this cluster.
6. **Backup/duplicate files excluded from deep tracing (per instructions), confirmed present:**
   - `Controller/AttendanceControllerBkup-22-01.php`, `Controller/AttendanceController_bkup-28-10.php` — backups of `AttendanceController.php`.
   - `Controller/EditPunchesController_bkup-24-10-2017.php` — backup of `EditPunchesController.php`.
   - `Controller/DayTimeProcedureController_nimisha_edited_backup.php`, `Controller/DayTimeProcedureControllerbackupnimishas.php` — backups of `DayTimeProcedureController.php`.
   - `Controller/AttendanceReportsController2018-010.php`, `Controller/AttendanceReportsControllerBkup-6-12.php`, `Controller/AttendanceReportsControllerBkup.php`, `Controller/AttendanceReportsController_Bkup-2018-01-07.php` — out of stated scope (not requested) but noted since they share the naming pattern; `AttendanceReportsController.php`/`AttendanceReportsNewController.php` were not in the requested list either and were left untraced.
   - Every `View/*` folder in this cluster contains large numbers of `#bkup_*`, `#backup_*`, `_old`, dated, and personal-name-suffixed `.ctp` copies (e.g. `View/AttendanceRegisterNew/registerbook.ctp#bkup_athira_18-05-2026`). Only the extensionless canonical `.ctp` (no `#`/`_bkup`/`_old` suffix) is ever `render()`-ed or requested via CakePHP's implicit view resolution, **except** where a controller explicitly calls `$this->render('other_name')` (see `DayTimeProcedureController` below) — in those cases the explicitly-rendered file is canonical and the default-named file is dead.

---

## AttendanceController.php (Controller/AttendanceController.php)

**Duplicates noted, not traced:** `AttendanceControllerBkup-22-01.php`, `AttendanceController_bkup-28-10.php`.

**Purpose:** Legacy attendance-register ("register book") controller — appears to be an early/superseded version of the same register-book feature now implemented in `AttendanceRegisterNewController.php` (identical action names/order, see cross-cutting finding 5). `AttendanceController.php:35-2750`.

**Who can access it:** No explicit gate beyond the global `user_group` check in `AppController.php:39-46`. `showregister()` (`AttendanceController.php:54-190`) branches on `user_group` at line 75 but the branch is commented out (lines 76-84) — currently dead code, meaning branch/employee filtering there is effectively a no-op leftover.

**Liveness:** No route in `Config/routes.php`; not linked from `View/AttendanceSetup/index.ctp`'s feature list (that hub points at `AttendanceRegisterNew/...`, per finding 4). Its own view folder `View/Attendance` was not found in this scope's directory listing (views live under `View/AttendanceRegisterNew`, `View/Attendanceregister`, etc. instead), suggesting this controller may be effectively orphaned code retained for compatibility/rollback. Treat as **legacy, likely dead** — do not treat as the migration source of truth; use `AttendanceRegisterNewController.php` instead.

**Step-by-step user flow / Forms / Notifications / AJAX:** Not traced in depth given likely-dead status (time budget directed at the canonical `AttendanceRegisterNewController.php` below, which has the same action surface).

---

## AttendanceRegisterNewController.php (Controller/AttendanceRegisterNewController.php) — CANONICAL register book

**Purpose:** The attendance "register book" — the month-by-month grid where HR/Admin reviews, edits, and verifies each employee's daily attendance codes (Present/Absent/Leave/Comp-off/etc.) before it feeds payroll. Also used by employees (via `empregisterbook`/`empindex`) to view their own register.

**Who can access it:**
- No explicit menu/permission gate in the controller; relies solely on the global `user_group` session check (`AppController.php:39-46`).
- `indexneww()` (`AttendanceRegisterNewController.php:55-212`) reads `user_group` at line 65 and applies employee-branch scoping when `user_group == 2` (line 81, 179): an ESS employee only sees their own branch's list; the branch dropdown is restricted via `MasterdataManagement->getBranchesListForCombo($emp_pkeys)`.
- `registerbook()` (line 212) similarly branches on `user_group == 2` at line 305 to scope data.
- Line 361 comment: "For user_group != 2 (admin/super-admin), no restriction — all employees shown" — confirms admin/HR (`user_group==1`) sees all branches/employees; this is enforced only inside the query-building logic, not via a separate authorization check.
- Reached from the admin side via the `AttendanceSetup` feature hub (`AttendanceSetup/index.ctp` → `feature_path = AttendanceRegisterNew/indexneww`, per cross-cutting finding 4); reached from ESS via `empindex`/`empregisterbook`.

**Step-by-step user flow:**
1. Admin opens Attendance hub (`AttendanceSetup/index.ctp`) → clicks the "Attendance Register" tile → AJAX-loads `AttendanceRegisterNew/indexneww` into `#container` (`View/AttendanceSetup/index.ctp:305-312`).
2. `indexneww()` (`AttendanceRegisterNewController.php:55-212`) renders `View/AttendanceRegisterNew/indexneww.ctp` — a month/branch/employee filter screen.
3. User selects month + branch (and optionally employee) → view calls `registerbook()` (`AttendanceRegisterNewController.php:212-794`), which renders `View/AttendanceRegisterNew/registerbook.ctp` — the actual grid of days × employees with per-day status codes.
4. Grid interactions (edit a day's status, mark leave, etc.) post back to `chnagestatus()` (`AttendanceRegisterNewController.php:1063-1410` — the earlier `chnagestatus()` at 806 is commented out/superseded), `AddLeave()` (line 4033), `editPunch()` (line 3172 — the version at 3064 is commented out), and `bulkipdatestatus()` (line 3552, bulk status update for multiple selected rows).
5. Once reviewed, HR verifies the month via `verifyAttendance()` (`AttendanceRegisterNewController.php:1560-1902`; an earlier commented-out version sits at 1902+). Verified rows then render through `View/AttendanceRegisterNew/verifiedregisterbook.ctp`.
6. `removeAttendance()` (line 1410) lets HR delete/reset an entry (the block at 1499+ is a commented-out earlier version).
7. `checkprocessingstatus()` (line 4160) / `markprocesscomplete()` (line 4212) gate whether payroll processing has already consumed this month's register (prevents edits after processing — inferred from name; not fully traced).

**Forms:** Grid-based inline editing rather than a classical single form; per-cell edits are submitted via AJAX to `chnagestatus`/`editPunch`/`AddLeave`. `verifyAttendance()` performs server-side checks before allowing verification (`checkifregistercanverify()`, line 2920, is called to validate whether a register can be verified — INFERRED: checks for incomplete/missing days before allowing verify).

**Notifications:** No email/SMS notification calls found in this controller (`Email->send` / `sendmemo` grep returned nothing for this file).

**AJAX/JS endpoints:** `registerbook.ctp` contains 2 `$.ajax(...)` calls (view file `View/AttendanceRegisterNew/registerbook.ctp`); the grid also uses `.load()`-style container swaps consistent with the rest of the app's AJAX shell pattern.

---

## AttendanceregisterController.php (Controller/AttendanceregisterController.php) — legacy duplicate

**Purpose/relationship:** Near-identical action set to `AttendanceRegisterNewController.php` minus the newer `indexneww`/`chnagestatus`/`bulkipdatestatus`/`getbranches`/`jsons`/`filter` additions (`AttendanceregisterController.php:54-997` has 19 actions vs. 36 in the New controller — see action lists captured during research). Per cross-cutting finding 4, the `AttendanceSetup` feature hub only special-cases fallback to `AttendanceRegisterNew/index` (not to this lowercase-`r` controller) for restricted company codes — so **this controller is superseded by `AttendanceRegisterNewController.php` and should be treated as legacy/likely-dead**, not traced further in depth. Its own views live under `View/Attendanceregister/*` (distinct folder from `AttendanceRegisterNew`), which still exist but are not reachable via the feature hub's `feature_path` values found in this codebase.

---

## AttendanceCheckInOutController.php (Controller/AttendanceCheckInOutController.php)

**Purpose:** Despite the name, this is **not** a punch/check-in-out capture screen — it is a large **HR reporting engine** for attendance-derived reports: Early-In, Early-Out, Late-In, Late-Out, and general attendance status reports, with report-criteria building, PDF/report generation, and report-audit logging. `AttendanceCheckInOutController.php:41-79` class header; `hrreports()` (line 79) is the reports landing action.

**Who can access it:** Global `user_group` gate only. Inside report generation, `listcriteriaitems()` (`AttendanceCheckInOutController.php:242-389`) branches specially for `user_group == 2 && ($user == 'GLET' || $user == 'ABSG')` (lines 256-258, 344-346) — company-specific ESS restriction on which report criteria an employee-level user can pick.

**Step-by-step user flow:**
1. `hrreports()` (line 79) renders the reports landing page (`View/AttendanceCheckInOut/hrreports.ctp`) listing report types (early/late in/out, daily/status attendance — see view files `earlyinreport.ctp`, `earlyoutreport.ctp`, `lateinreport.ctp`, `lateoutreport.ctp`, `dailyattendance.ctp`, `statusreport.ctp`).
2. `changereporttype()` (line 104) and `addreportcriteria()` (line 191) let the user pick a report type and add filter criteria (department/grade/vertical/branch/employee, per the commented `$arr_employee_reportcriterias` map at lines 60-74).
3. `listcriteriaitems()` (line 242) AJAX-populates the criteria picker.
4. `generatereport()` (line 482, ~6700 lines total, by far the largest action set in this cluster) builds and renders the selected report; each report-type branch reads `user_name` from session (many `$user_name = $this->Session->read('user_name')` lines, e.g. 796, 1156, 1504, 1939, 2324, 2740, 3127, 3426, 3735, 4043, 4348, 4783, 5605) purely for audit/footer attribution, not authorization.
5. `reportAudit()` (line 389) logs report generation into an audit table (uses `ReportAudit` model, consistent with the mobile-tracking audit pattern seen in `MobileLocationUpdateController`).

**Forms:** Report-criteria selection forms (department/grade/branch/employee/date-range) rather than data-entry forms; no create/update of attendance records here.

**Notifications:** None found (no `Email->send` in this file).

**AJAX/JS endpoints:** `View/AttendanceCheckInOut/loadcriteriaitems.ctp` and `showreport.ctp` drive dynamic filter loading; 2+ `.ctp` files under this view folder contain `$.ajax`/report-refresh calls (not individually enumerated given the report-engine's size — 6679 lines/many near-identical report-type branches).

---

## AttendanceSetupController.php (Controller/AttendanceSetupController.php)

**Purpose:** The Attendance module's landing/settings hub — surfaces the plan-gated feature tile UI described in cross-cutting finding 4, and (via `index()`) supplies the branch list for setup screens.

**Who can access it:** Global `user_group` gate only; no per-feature server-side enforcement — `getAttendanceFeatures()` (`AttendanceSetupController.php:17-103`) returns *all* feature metadata with an `is_enabled` flag computed client-side-consumed; a user could in principle still navigate directly to a disabled feature's controller/action since nothing server-side blocks it (the plan gating is UI-only in this file).

**Step-by-step user flow:**
1. `index()` (`AttendanceSetupController.php:10-16`) renders `View/AttendanceSetup/index.ctp`, an otherwise-empty three-column grid shell.
2. Page JS immediately calls `$.getJSON("AttendanceSetup/getAttendanceFeatures", ...)` (`View/AttendanceSetup/index.ctp:203-235`).
3. `getAttendanceFeatures()` (`AttendanceSetupController.php:17-103`) looks up the company's `plan_id` from `CentralUserCredentials` (controldb datasource, line 21-30), loads all `Features` rows where `feature_key = 'Attendance'` and `is_common = 0` (lines 37-52), cross-references `PlanFeature` to compute `is_enabled` per feature (lines 55-61), and applies the two company-code path substitutions described in finding 4 (lines 79-91).
4. Left column renders clickable feature tiles (line 212-226); last-clicked feature persisted to `localStorage` (lines 199, 229-240) so returning to the hub re-opens the same feature.
5. Clicking an enabled tile's "Open" button AJAX-loads the target `feature_path` into `#container` (lines 305-312); clicking a disabled tile shows an "Upgrade to Next Plan" CTA that loads `User/profile` and jumps to a pricing tab (lines 315-325).

**Forms:** None (read-only hub).

**Notifications:** None.

**AJAX/JS endpoints:** `AttendanceSetup/getAttendanceFeatures` (GET, JSON) — `View/AttendanceSetup/index.ctp:203`.

---

## EditAttendanceController.php (Controller/EditAttendanceController.php)

**Purpose:** Admin/HR screen for directly editing an individual employee's daily punches/attendance status outside the register-book grid flow (punch-level correction tool) — includes memo/notification stub, bulk status update, and "early-out/late-in" adjustment.

**Who can access it:** Global `user_group` gate only; no explicit per-action gate found (`Useraccess`/`menu_id` grep returned nothing for this file).

**Step-by-step user flow:**
1. `index($emp_id)` (`EditAttendanceController.php:66-81`) / `hierarchy()` (81-93) land on an employee picker.
2. `employeeeditpunch()` (93-109) and `listpunches()` (158-436, the version at 109 is commented out) list an employee's punches for a date range, rendering `View/EditAttendance/employeeeditpunch.ctp` / `editpunch.ctp`.
3. `form()` (705-775) renders the punch edit form (`View/EditAttendance/form.ctp`) for a specific `$empid`/`$att_date`/`$site_t_fkey`.
4. `savenew()` (814-921) / `savepunch()` (921-962, an earlier version at 962+ is commented out) persist the edited punch.
5. `chnagestatus()` (1117-1182, `chnagestatus_old()` at 1046 kept for reference) and `chnagestatusadditonal()` (1182-1282) update a day's attendance status code.
6. `bulkipdatestatus()` (1282-1334) applies a status change to multiple selected rows at once.
7. `remove()` (775-793) deletes a punch/entry.
8. `sendmemo()` (1349-1363) — **dead/test code**: hardcodes `Email->from('sruthi.pb@gmail.com')` / `Email->to('sruthiforsight@gmail.com')` / `Email->send('My message')` (`EditAttendanceController.php:1349-1363`). Not wired to any real business event (no dynamic recipient, no template) — appears to be a leftover developer test stub, not a functioning notification.
9. `ealry_out_late_in()` (1509-1562) / `lesshoursave()` (1562-1592) — short-hours/early-out adjustment handling.

**Forms:** Punch edit form (`form.ctp`) — fields inferred from action signature: employee, attendance date, `site_t_fkey` (site/terminal reference), in-time, out-time (`editpunch($att_date, $emp_id, $site_t_fkey, $att_in_time, $att_out_time)`, line 508). No client-side validation library confirmed in view (not deep-traced); server side builds raw SQL in several actions (INFERRED: limited server-side validation, consistent with the rest of the codebase's raw-query style seen elsewhere in this cluster, e.g. `CompoffController.php:55-76`).

**Notifications:** `sendmemo()` stub only (see above) — effectively non-functional.

**AJAX/JS endpoints:** Not individually enumerated (view folder `View/EditAttendance` has 5 files: `editemployeepunch.ctp`, `editpunch.ctp`, `employeeeditpunch.ctp`, `form.ctp`, `index.ctp`); functionally superseded in most flows by `EditPunchesController` below, which has near-identical actions plus more (26 actions vs this controller's 27 — nearly 1:1 overlap, see next entry).

---

## EditPunchesController.php (Controller/EditPunchesController.php) — likely canonical punch-editor

**Duplicate noted, not traced:** `EditPunchesController_bkup-24-10-2017.php`.

**Purpose:** Same punch-level edit tool as `EditAttendanceController.php` but with additional sync/shift-date features (`updateShiftDate`, `Syncame`, `SyncAttendance`) and a newer `listpunchesnew()` grid action — appears to be the actively maintained evolution of `EditAttendanceController.php`.

**Who can access it:** Global `user_group` gate only; no explicit per-action gate found.

**Step-by-step user flow:**
1. `index($emp_id)` (`EditPunchesController.php:113-341`) — employee/date-range picker, renders `View/EditPunches/index.ctp` (also has a distinct `index_new.ctp`, suggesting an in-progress newer variant — dated backups exist up to `#backup_Akshay_25-5-2026`).
2. `jsons()` (341-441) AJAX-supplies branch/employee dropdown data (`branch`, `resigned` params).
3. `indexload()` (441-479), `hierarchy()` (505-528), `employeeeditpunch()` (528-544) — supporting list/hierarchy views.
4. `listpunches()` (1442-2394, an earlier version is commented at 544-1442) and the newer `listpunchesnew()` (2394-2822) render the punch grid; `listpunchesnew` is the more recently touched variant per view-folder naming (`index_new.ctp`).
5. `editpunch()` (2822-2901), `listpunchesbydate()` (2901-2993), `Updateame()` (2993-3080), `Updateamendmens()` (3080-3144) — punch/day editing operations. "ame"/"amendmens" naming suggests an "amendment" workflow for correcting attendance after the fact.
6. `form()` (3144-3214) renders `View/EditPunches/form.ctp` (edit form) or `form.ctp_bkup-24-10-2017` (dead backup).
7. `savenew()` (3253-3350) / `savepunch()` (3350-3391) persist changes.
8. `remove()` (3214-3232) deletes an entry.
9. `updateShiftDate()` (3489-3526) — reassigns which shift/day a punch belongs to (relevant to `ShiftPlannerController` interplay — `ShiftPlannerController.php` also `uses` the `EditPunches` model, line 30).
10. `Syncame()` (3526-3585) / `SyncAttendance()` (3585-3632) — re-sync attendance from source device/biometric data (INFERRED from naming; not traced into implementation given time budget — these are the last two actions in the file, ~50-100 lines each).
11. `sendmemo()` (3475-3489) — same dead/test email stub pattern as `EditAttendanceController.php:1349` (`Email->send('My message')` to hardcoded gmail addresses).

**Forms:** Same punch-edit form pattern as `EditAttendanceController` (`form.ctp`, fields: employee, date, site/terminal, in-time, out-time — `editpunch($att_date, $emp_id, $site_t_fkey, $att_in_time, $att_out_time)`, line 2822).

**Notifications:** `sendmemo()` dead stub only (line 3475).

**AJAX/JS endpoints:** View folder `View/EditPunches` also contains `hrreports.ctp`, `showreport.ctp`, `loadcriteriaitems.ctp` — suggesting this controller also grew a reporting sub-feature parallel to `AttendanceCheckInOutController`'s report engine (not traced in depth; flagged for follow-up if Edit Punches reporting matters to migration scope).

---

## EmpattendanceController.php (Controller/EmpattendanceController.php)

**Purpose:** Employee-facing ("Emp" prefix = ESS) mirror of the register-book flow — same action set as `AttendanceController.php`/`AttendanceregisterController.php` (`showregister`, `showregistertab`, `listregisterentries`, `listverifiedregisterentries`, `processregisterentries`, `verifyregisterentries`, `loadattendanceregisterheader`, `checkifregistercanverify`, `updateregisterentries`, `submitregisterentry`, plus `verifiedpdf` for PDF export). `EmpattendanceController.php:53-1211`.

**Who can access it:** Global `user_group` gate only; no additional scoping check found in this file specifically (grep for `user_group` inside this controller returned no matches — meaning, unlike `AttendanceRegisterNewController`, it does **not** appear to branch behavior by group at all; it may rely entirely on the session's `emp_fkey` already being the acting employee, i.e., implicitly self-scoped). Views: `View/Empattendance/showregister.ctp`, `showregistertab.ctp`, `reportverified.ctp`, `updateregisterentries.ctp` — a **smaller view set** than the admin versions (no register-editing grid view, only show/report views), consistent with a read-mostly ESS "view my attendance register" screen rather than a full edit surface.

**Notifications:** None found.

---

## EmpattendanceregisterController.php (Controller/EmpattendanceregisterController.php)

**Purpose:** Minimal ESS register-book variant — only 3 actions: `registerbook()` (line 55), `filter()` (103), `index()` (143). Smallest controller of the register-book family; views are `form.ctp`, `index.ctp`, `registerbook.ctp` under `View/Empattendanceregister`.

**Who can access it:** Global gate only; no group-branch logic found in this small file.

**Relationship:** Overlaps heavily in purpose with `EmployeeAttendanceregisterController.php` (below) and `EmpattendanceController.php` above — three different ESS "view my register" implementations coexist. INFERRED: likely only one is currently linked from the live employee menu (menu is DB-driven, not resolvable from code alone); flag for the user to confirm which is live via the `emp_menu` table before committing to one in the Next.js migration.

---

## EmpattendanceuploadController.php (Controller/EmpattendanceuploadController.php)

**Purpose:** Bulk attendance upload tool (Excel/CSV import) — `index()` (26), `getemployeenames()` (48), `form()` (77), `attendancesave()` (122), `load()` (164), `listattendance()` (185), `deleteattendance()` (274), plus a CTC-format download/upload pair (`downloadempctcformat`, `uploadandsaveempctc`, lines 293-529) that looks like copy-pasted boilerplate from a payroll/CTC upload controller (naming mismatch — "empctc" inside an attendance-upload controller) rather than attendance-specific code.

**Who can access it:** Global gate only.

**Relationship:** Overlaps with `EmployeeAttendanceUploadController.php` (below), which is substantially larger (1610 lines vs 529) and has more complete leave-checking (`checkleave()`) and format-download logic — **`EmployeeAttendanceUploadController.php` looks like the more developed/canonical version**; this shorter one is likely an earlier iteration kept around. Not traced further given time budget.

---

## EmpeditpunchesController.php (Controller/EmpeditpunchesController.php)

**Purpose:** ESS-scoped punch editor — employee can view/edit their own punches. `index()` (58), `listpunches()` (73), `form()` (121), `remove()` (126), `savenew()` (142), `savepunch()` (166), `getmonths()` (176), `listempemployees()` (189). Views: `View/Empeditpunches/form.ctp`, `index.ctp`.

**Who can access it:** Global gate only; smallest of the punch-editor family (8 actions vs. 26-27 for the admin versions), consistent with a cut-down self-service surface.

---

## EmployeeAttendanceUploadController.php (Controller/EmployeeAttendanceUploadController.php) — canonical upload tool

**Purpose:** Bulk attendance import for a set of employees (branch/month scoped), with leave-conflict checking and both an "attendance format" and legacy "CTC format" download/upload path.

**Who can access it:** Global gate only.

**Step-by-step user flow:**
1. `index()` (`EmployeeAttendanceUploadController.php:27-76`) — landing page (`View/EmployeeAttendanceUpload/index.ctp`).
2. `employeefilter($branch)` (76-104) / `getemployeenames()` (104-123) — AJAX employee list scoped to a branch.
3. `form($emp_fkey)` (234-348, an earlier version at 123-234 is commented out) — renders `View/EmployeeAttendanceUpload/form.ctp`, the upload form (file input + employee/month context, inferred).
4. `checkleave($emp_fkey, $in_date, $out_date)` (348-528) — validates the uploaded attendance dates don't conflict with an approved leave before allowing save; likely returns a warning/blocking JSON response (not fully traced).
5. `attendancesave()` (528-582) persists parsed rows.
6. `load()` (582-603) / `listattendance()` (603-719) — list view of uploaded/pending attendance.
7. `deleteattendance()` (719-766) — remove an uploaded row.
8. `downloadempattendanceformat($ctcuploadtype, $branch, $month, $emp_pkey)` (766-1125) — generates the downloadable Excel template for bulk upload.
9. `downloadempctcformat()` (1125-1232) and `uploadandsaveempctc()` (1410-1610, an earlier commented version at 1232-1410) — legacy CTC-upload code path bundled into this controller (naming/purpose mismatch, likely copy-paste residue from a payroll controller).

**Forms:** File-upload form (`form.ctp`) for bulk attendance import; validation is server-side via `checkleave()` prior to save (leave-conflict check) — no client-side validation confirmed from this pass.

**Notifications:** None found.

---

## EmployeeAttendanceregisterController.php (Controller/EmployeeAttendanceregisterController.php)

**Purpose:** Third ESS "view my attendance register" implementation (see note under `EmpattendanceregisterController.php` above) — largest of the three ESS register variants (16 actions, `EmployeeAttendanceregisterController.php:54-763`), with the same show/verify/list action names as the admin `AttendanceRegisterNewController` but scoped for a single employee's own data.

**Who can access it:** Global gate only; no `user_group` branch found inside this file (grep returned nothing), implying it is expected to always run in an already-self-scoped context (e.g., relies on `emp_fkey` from session rather than checking `user_group` explicitly).

**Recommendation for migration:** Given three overlapping ESS register-book controllers (`EmpattendanceController`, `EmpattendanceregisterController`, `EmployeeAttendanceregisterController`) exist, confirm via the `emp_menu` DB table (or by asking the client) which single one is currently linked from the employee-facing menu before porting — porting all three is very unlikely to be correct.

---

## DailyActivityController.php (Controller/DailyActivityController.php)

**Purpose:** Site/project-based daily activity and shift-closure tracking tool — distinct from the payroll attendance-register flow. Tracks per-employee "activity" entries against sites/projects, per-user access lists for sites, shift open/close, and mass status flips (active/deactive/mark-attendance). `DailyActivityController.php:53-1256`, 33 actions — the largest action count of any controller in this cluster besides the register-book/report engines.

**Who can access it:** Global gate only; no `user_group`/`menu_id` gate found. Has its own internal access-list concept (`listuseraccess()` line 400, `saveuseraccess()` line 600, `deleteuser()` line 589) that manages which users can access which **sites** (`site_fkey`) — this is a feature-level access list distinct from the app's `user_access`/`menu_id` system, scoped to site/project assignment rather than menu permission.

**Step-by-step user flow (high level):**
1. `index()` (53-77) — landing/filter page.
2. `activity_form()` (77-96) / `activity_save()` (96-143) — create/edit a daily activity entry.
3. `filtersite()` (143-197) — AJAX site filter.
4. `delete()` (197-210) — remove an activity.
5. `lists()` (245-292) / `form2()` (230-245) — secondary listing/form.
6. `save()` (292-348), `addDefault()` (348-365), `resetDefault()` (365-383), `deletemens()` (383-400) — default-activity management per employee.
7. Site access management: `listuseraccess()` (400-464), `Employee()` (464-495), `saveProject()` (495-536), `admin()` (536-568), `get()` (568-589), `deleteuser()` (589-600), `saveuseraccess()` (600-635).
8. Shift/site operations: `get_incompleteDate()` (635-655), `get_shift()` (686-719), `load_sites()` (719-734), `add_site()` (734-773), `shift_closure()` (773-824), `close_shift()` (824-863), `data_site()` (863-983).
9. Bulk status: `mark_activestatus()` (983-1010), `mark_deactivestatus()` (1010-1039), `mark_attendance()` (1039-1186).
10. `site_allocate()` (1186-1205) / `save_allocate()` (1205-1230) / `remove_allocate()` (1230-1256) — allocate/deallocate employees to sites.

**Views:** Only `View/DailyActivity/activity_form.ctp` and `index.ctp` exist — meaning most of the 33 actions above are pure-JSON/AJAX endpoints with no dedicated template (consistent with `autoRender = false`-style APIs backing a JS-driven single page, though `autoRender` wasn't individually confirmed for every action in this pass).

**Notifications:** None found.

---

## DailyOvertimeVerifyController.php vs DailyOvertimeVerifyNewController.php

**Which is canonical:** `DailyOvertimeVerifyNewController.php` (`Controller/DailyOvertimeVerifyNewController.php`, 1924 lines, 21 actions) is the newer/more complete version — it has all the same actions as `DailyOvertimeVerifyController.php` (1423 lines, 18 actions) **plus** `updateOvertime()` (line 1824), `overtimeDataUpdate()` (1831), and `getEmployeesByBranch()` (1900), and its view folder (`View/DailyOvertimeVerifyNew`) has recent 2026 backup dates (`index.ctp#backup_Akshay_11-5-2026` etc.) plus a dedicated `update_overtime.ctp` template not present in the old controller's view folder. **`DailyOvertimeVerifyController.php` should be treated as legacy** and not migrated as a separate feature — its view folder's most recent edits are from 2022-2025 (`index.ctp#bkup_bindu_10_12_2025` being the newest), versus the New controller's 2026 edits.

### DailyOvertimeVerifyNewController.php (canonical)

**Purpose:** Daily (as opposed to monthly) overtime verification workbench — lists employees' daily punches with computed OT duration, lets HR verify/approve OT per day, edit punches directly, and manage Scheduled-Break-Off (SBO) dates inline (shares logic with `ScheduledBreakOffController`, see `listbreakoffdatesforsbo`/`listbreakoffdatesforsbo_verified`, lines 72-137, mirrored in `ScheduledBreakOffController.php:68-126`).

**Who can access it:** Global gate only; no explicit per-action gate found.

**Step-by-step user flow:**
1. `index()` (`DailyOvertimeVerifyNewController.php:34-72`) — branch/month picker, renders `View/DailyOvertimeVerifyNew/index.ctp`.
2. `listpunches()` (221-655) — main daily punch/OT grid for unverified days.
3. `listpunchesverify()` (655-974) — grid of already-verified days.
4. `listpunchesbydate()` (974-1043) — drill into a single date.
5. `editpunch()` (1043-1100) / `form()` (1100-1169) / `savenew()` (1169-1271) / `savepunch()` (1292-1336) — punch correction sub-flow, rendered via `View/DailyOvertimeVerifyNew/editpunch.ctp` / `form.ctp`.
6. `verify()` (1353-1377) / `verifyregisterentries()` (1377-1539) — mark day(s) as OT-verified.
7. `removeentries()` (1539-1630) — undo/delete entries.
8. `updateSetDuration()` (1630-1797) — manually override computed OT duration for a day.
9. `setRemarks()` (1797-1824) — attach a remark to an OT entry.
10. `updateOvertime()` (1824-1831) / `overtimeDataUpdate()` (1831-1900) — bulk OT data update, backed by `View/DailyOvertimeVerifyNew/update_overtime.ctp`.
11. `getEmployeesByBranch()` (1900-1924) — AJAX employee-list-by-branch helper.
12. SBO helpers: `listbreakoffdatesforsbo()` (72-137), `listbreakoffdatesforsbo_verified()` (137-214).

**Forms:** Punch-correction form (`form.ctp`) with same field pattern as other punch editors (date/employee/site/in-time/out-time). `updateSetDuration`/`overtimeDataUpdate` are AJAX-only.

**Notifications:** None found.

---

## OtAttendanceController.php vs OtAttendanceNewController.php

**Which is canonical:** `OtAttendanceNewController.php` (`Controller/OtAttendanceNewController.php`, 2969 lines, 24 actions) supersedes `OtAttendanceController.php` (804 lines, 17 actions) — the New version has all the same core actions (`Register`, `Approved`, `form`, `save`, `savedata`, `Toapproved`, `index`, `subtable`, `subtablegenpdf`, `approves`, `Setvalue`, `remarks`, `counting`, `remove`, `removenew`, `otprocess`, `process`) **plus** `getDurationRegister()` (1432), `getNotApprovedData()` (2267), `getApprovedData()` (2563), `getProcessedData()` (2740), `jsons()` (2881). View folder `View/OtAttendanceNew` has 2025-2026 dated backups (`backup_Akshay_22-5-2026` etc.) vs `View/OtAttendance`'s older ones (newest `bkup_bindu_10_12_2025`). **`OtAttendanceController.php` is legacy**, not traced further in depth.

### OtAttendanceNewController.php (canonical)

**Purpose:** Overtime register/approval workflow — separate from the daily-verify workbench above, this is the monthly OT register where computed OT per employee is registered, submitted for approval, approved, and processed into payroll.

**Who can access it:** Global gate only; no explicit gate found in this file.

**Step-by-step user flow:**
1. `Register($month, $emp_pkey, $branch)` (`OtAttendanceNewController.php:54-206`) — lists OT register entries for a month/branch, renders `View/OtAttendanceNew/register.ctp`.
2. `Approved($month, $emp_pkey, $tab, $branch)` (206-295) — lists already-approved OT.
3. `form($emp_pkey, $duration, $month)` (295-308) / `save()` (308-328) / `savedata()` (328-353) — manual OT entry form, `View/OtAttendanceNew/form.ctp`.
4. `Toapproved()` (353-377) — moves selected entries to approved state.
5. `index()` (377-404) — landing page.
6. `subtable($emppkey, $month)` (404-693) — per-employee OT detail sub-table (large action, ~290 lines), rendered via `View/OtAttendanceNew/subtable.ctp` / `table.ctp`.
7. `subtablegenpdf()` (693-1010) — PDF export of the sub-table.
8. `approves()` (1010-1094) — bulk-approve selected OT rows (note: parameterless in the New controller vs `approves($selectd, $month)` in the old one — signature changed to read from POST body instead of URL params, consistent with an AJAX-first refactor).
9. `Setvalue()` (1094-1107) / `remarks()` (1107-1119) — inline edit helpers.
10. `counting($month, $emp_pkey)` (1119-1130) — OT hour totals.
11. `remove()` (1130-1209) / `removenew()` (1209-1252) — delete OT entries (two variants, `removenew` likely the current one given naming convention elsewhere in this cluster).
12. `otprocess()` (1252-1355) / `process($month, $emp_pkey, $tab, $branch)` (1355-1432) — push OT into payroll processing.
13. `getDurationRegister()` (1432-1763, largest single action in this controller) — computes OT durations for the register view (heavy SQL, not traced line-by-line).
14. `getNotApprovedData()` (2267-2563) / `getApprovedData()` (2563-2740) / `getProcessedData()` (2740-2881) — status-filtered AJAX data feeds for the register tabs.
15. `jsons($branch, $resigned, $month)` (2881-2969) — branch/employee dropdown data.

**Forms:** Manual OT entry form (`form.ctp`) — fields inferred from signature: employee, OT duration, month.

**Notifications:** None found.

---

## OvertimeController.php (Controller/OvertimeController.php)

**Purpose:** A **separate OT reporting engine** (distinct from both `OtAttendanceNewController`'s register/approval workflow and `DailyOvertimeVerifyNewController`'s daily verify workbench) — mirrors `AttendanceCheckInOutController`'s report-builder pattern almost exactly (`hrreports`, `changereporttype`, `addreportcriteria`, `loadcriteriaitems`, `listcriteriaitems`, `reportAudit`, `generatereport`) plus OT-specific report generators.

**Who can access it:** Global gate only; no explicit gate found.

**Step-by-step user flow:**
1. `hrreports()` (`OvertimeController.php:60-74`) — report landing page, `View/Overtime/hrreports.ctp`.
2. `changereporttype()` (74-107) / `addreportcriteria()` (107-132) / `loadcriteriaitems()` (132-155) / `listcriteriaitems()` (155-468) — criteria-builder, same pattern as `AttendanceCheckInOutController`.
3. `reportAudit()` (468-534) — audit logging (same `ReportAudit` pattern).
4. `downloadHistory()` (534-643) — download previously generated reports.
5. `generatereport()` (643-664) dispatches to the specific generators: `generateOvertimeSynthietreport()` (704-1682, "synthetic"/summary report — largest action here at ~980 lines), `generateOtSynthietreport()` (1682-2173), `generateOtMonthwiseReport()` (2173-2657, `View/Overtime/ot_monthwise.ctp` / `overtime_dptmnt.ctp`).
6. `listemployeefields()` (664-704) — AJAX field list for report column selection.

**Notifications:** None found.

---

## RegularisationController.php (Controller/RegularisationController.php) — canonical attendance-regularisation/approval workflow

**Purpose:** Employee self-service "regularise my attendance" workflow (employee flags a missed/incorrect punch and requests correction) plus the HR/hierarchy-manager approval side (single and bulk approve), including an "adminindex" vs "adminindexnew" pair (see below) and a manager-hierarchy approval path.

**Who can access it:**
- Global `user_group` gate only (`AppController.php:39-46`); no `menu_id`/`user_access` per-action check found (`Useraccess`/`menu_id` grep on this file returned nothing).
- Internally splits by role via distinct actions rather than a single gated action: `index($emp_id)` (`RegularisationController.php:60-164`) is the **employee self-service** entry, `hierarchyindex($emp_id)` (164-217) is the **manager/hierarchy-approver** entry, `adminindex($emp_id)` (217-266) / `adminindexnew($emp_id)` (266-368) are the **HR/admin** entry points. Which one a given logged-in user actually reaches is presumably determined by menu visibility (DB-driven `emp_menu`), not by a runtime check inside the controller — meaning, per finding 3, an employee who directly hits `Regularisation/adminindexnew` in the URL bar is **not blocked server-side** based on this file alone.
- `adminindex` vs `adminindexnew`: confirmed by `AttendanceSetupController.php:80-84` that `adminindexnew` is default and `adminindex` is the fallback only for the `$not_allowed_companies` list (finding 4) — so **`adminindexnew` is canonical**, `adminindex` is a legacy path kept alive for specific customers only.

**Step-by-step user flow (employee self-service path):**
1. `index($emp_id)` (60-164) renders `View/Regularisation/index.ctp` — employee's own attendance list with regularisation status.
2. `listpunches()` (432-687, an alternate/hierarchy-aware version at 687+ is commented out) — lists punches eligible for regularisation.
3. `form($empid, $att_date, $site_t_fkey)` (1868-1950) — the regularisation request form (`View/Regularisation/form.ctp`), presumably capturing corrected in/out time and a reason (INFERRED from field naming pattern shared with other punch forms in this cluster; not confirmed against the .ctp directly in this pass).
4. `savenew()` (2653-2751) / `savepunch()` (2751-2792, `2792+` commented alt version) — submit the regularisation request.
5. `bulkform()` (1950-1987) / `bulkupdate_self()` (2064-2197) — employee can submit multiple regularisation requests at once.

**Step-by-step user flow (approval path):**
1. `hierarchyindex($emp_id)` (164-217) → `listhierarchyregularization()` (1336-1452) lists a manager's team's pending regularisation requests.
2. `adminindexnew($emp_id)` (266-368) → `listadminregularizationnew()` (1071-1236) — HR-wide pending list (canonical); `adminindex` → `listadminregularization()` (1236-1336) is the legacy-fallback equivalent.
3. `bulkapprove()` (1987-2012) / `bulkadminapprove()` (2012-2038) / `bulkhierarchyapprove()` (2038-2064) — bulk-approve actions per role.
4. `bulkupdate($adminUpdate)` (2197-2483) / `bulksavenew()` (2483-2614) — bulk save/update of approval decisions.
5. `updateStatus()` (2890-2906) — single-row status update; builds a raw SQL `UPDATE employee_regularaization SET status = $status ...` directly from `$_POST['id']`/`$_POST['status']`/`$_POST['C1']` (`RegularisationController.php:2890-2906`) — **no visible input sanitisation/escaping in this snippet**, flag as a security/data-integrity note for the migration (parameterize in the Next.js/API rewrite).
6. `viewattendancedetails()` (1498-1546) — read-only detail drill-down for an approver, `View/Regularisation/viewattendancedetails.ctp`.

**Forms:** Regularisation request form (`form.ctp`), bulk form (`bulkform.ctp`). No client-side validation library confirmed in this pass.

**Notifications:** `sendmemo()` (`RegularisationController.php:2876-2888`) — **same dead/test stub** as the other two controllers: hardcoded `Email->from('sruthi.pb@gmail.com')`/`to('sruthiforsight@gmail.com')`/`send('My message')`. Not wired into the actual approve/reject flow (no call to `sendmemo()` found from within `bulkapprove`/`bulkadminapprove`/`updateStatus` in the greps performed) — **regularisation approval/rejection does not appear to trigger any real employee notification in this codebase.** This is a notable gap to flag for the Next.js rewrite if email notifications are expected by users.

**AJAX/JS endpoints:** `getEmployeesByBranch()` (2906-2963) — branch-scoped employee list; `getmonths()` (2861-2876) — month dropdown data.

---

## ShiftPlannerController.php (Controller/ShiftPlannerController.php)

**Purpose:** Assign employees to daily shifts for a month (a roster/calendar tool), checking primary/secondary shift eligibility from `emp_config`/`working_day_time_procedures` and preventing changes once attendance for that month is verified.

**Who can access it:** Global gate only; no explicit gate found.

**Step-by-step user flow:**
1. `index()` (`ShiftPlannerController.php:33-45`) — lists active employees (`status = 1`), renders `View/ShiftPlanner/index.ctp`.
2. `listemployees()` (92-176) — AJAX: given `emp_pkey`+`month`, computes the month's date range via the `att_start_end_fn` SQL function (lines 101-104), fetches existing attendance (`emp_detail_timeattandance`, lines 116-120), primary/secondary eligible shifts (`emp_config`/`working_day_time_procedures`, lines 132-146), and any already-saved roster (`emp_shift_planner`, lines 149-157) — returns a merged per-day JSON payload (lines 160-175) that the view presumably renders as a calendar grid.
3. `saveRoster()` (181-252) — POST endpoint: validates `emp_pkey`/`month`/`shiftData` are present (191-194); **blocks the save if attendance for that month is already verified** (`attendance_register.isdelete === 'N'`, lines 197-212) with message *"Attendance already verified for this month. Please re-iterate attendance in Edit Attendance to reflect shift changes."*; otherwise for each date in the submitted roster it deactivates other shift rows for that date/employee (218-223), then either reactivates a matching existing `emp_shift_planner` row (225-239) or inserts a new one (240-247).

**Forms:** Roster grid (day × shift picker) submitted as a single JSON payload (`shiftData`) to `saveRoster`; validation is entirely server-side (missing-field check + verified-month lock, see above); success/failure returned as `{success, message}` JSON (lines 192, 207-211, 250) — front-end presumably renders this as a toast/alert (not confirmed in this pass, view not read in full).

**Notifications:** None.

**AJAX/JS endpoints:** `ShiftPlanner/listemployees` (GET-style with `emp_pkey`/`month` request params, line 92-96), `ShiftPlanner/saveRoster` (POST, line 181-252) — both `autoRender = false` JSON APIs (lines 93, 183).

---

## ScheduledBreakOffController.php (Controller/ScheduledBreakOffController.php)

**Purpose:** Manages "Scheduled Break Off" (SBO) — a special day-off/break category assignable to employees on specific dates, likely used in OT/attendance calculation (shares SQL logic with `DailyOvertimeVerifyNewController`'s `listbreakoffdatesforsbo*` actions, confirming SBO data feeds directly into daily OT verification).

**Who can access it:** Global gate only; no explicit gate found.

**Step-by-step user flow:**
1. `index()` (`ScheduledBreakOffController.php:34-38`) — landing page, `View/ScheduledBreakOff/index.ctp` (numerous dated/`_old` backups present, e.g. `index.ctp_old`, `index.ctp_old1`, `index.ctp_sanju29072022`, `index.ctp_sanjuold` — extra confirmation that only the extensionless `index.ctp` is live).
2. `listbreakoffdatesforsbospecial($monthChoosen)` (38-53) / `listbreakoffdatesforsbospecialall($monthChoosen)` (53-68) / `listbreakoffdatesforsbo($monthChoosen)` (68-121) — list SBO-eligible/assigned dates for a month, in three variants (special/special-all/regular — INFERRED difference: "special" likely denotes a company- or policy-specific SBO rule set vs the default; not confirmed further).
3. `addEmpToSBO()` (121-257) — assign an employee to an SBO date (large action, ~135 lines — likely includes eligibility validation).
4. `removeEmpFromSBO()` (257-344) — unassign.
5. `listemployeesinsbodate($attendanceType)` (344-416) / `listemployeesforsbodate()` (416-508) / `listemployeesforsbodateone()` (508-600) — three variants of listing employees for a given SBO date, differing by filter shape (INFERRED, names suggest date-scoped listings with slightly different grouping — not traced field-by-field).

**Notifications:** None found.

---

## CompoffController.php (Controller/CompoffController.php)

**Purpose:** Displays an employee's compensatory-off ("comp-off") summary — days earned by working on a week-off/holiday, and eligibility counts, for the current open financial year. Read-only summary screen; **only one action** (`index()`), 84 lines total, one view (`View/Compoff/index.ctp`).

**Who can access it:** Global gate only; no explicit gate found. Uses `$my_pkey = $this->Session->read('emp_fkey')` (`CompoffController.php:53`) — **inherently self-scoped to the logged-in user**, so this appears to be an ESS-only "my comp-off" view rather than an admin tool.

**Step-by-step user flow:**
1. `index()` (52-82) runs two raw SQL queries: one scanning `attendance_register`'s 32 day-fields (`FIELD1`...`FIELD32`) for the literal code `'COFF'` within the open financial year (`fin_year.Year_status = 'OPEN'`) to compute earned comp-off count per month (lines 55-67); another computing comp-off *eligibility* from `emp_detail_timeattandance` based on `weekoff`/`holiday` flags combined with `present` status (`P/P` full, `P/A`/`A/P` half-credit, lines 68-76).
2. Both result sets are packed into `arr_leavepolicydetails_for_template` (77-81) and set to the view.

**Forms:** None (read-only).

**Notifications:** None.

**Data-model note for migration:** The `FIELD1..FIELD32` wide-table pattern for daily attendance codes (`attendance_register`) recurs across this cluster (also implied in `AttendanceRegisterNewController`'s register-book grid) — worth normalizing into a proper day-rows table in the Next.js/new-schema design rather than porting the 32-column pattern.

---

## HolidayCalendarController.php (Controller/HolidayCalendarController.php)

**Purpose:** Admin tool to define company holidays and holiday groups (different branches/locations can have different holiday sets), used elsewhere in this cluster to compute leave/OT eligibility (e.g. `CompoffController`'s `holiday` field check).

**Who can access it:** Global gate only; no explicit gate found.

**Step-by-step user flow:**
1. `index()` (`HolidayCalendarController.php:54-71`) — landing page, `View/HolidayCalendar/index.ctp`.
2. `form()` (71-107) — add/edit a single holiday (`View/HolidayCalendar/form.ctp`); `groupform()` (126-139) — add/edit a holiday group (`groupform.ctp`).
3. `filterjson($id)` (107-126) — AJAX filter/lookup.
4. `saveholiday()` (184-243) — persists a holiday; `checkholidaydateexists($HOLIDAYID, $codecount, $HOLIDAY_GROUP_ID)` (440-463) is called client-side (per `View/HolidayCalendar/index.ctp:210` and `:333`, two `$.ajax` calls) to prevent duplicate holiday entries on the same date/group before save — a real client-enforced + server-checked validation.
5. `saveholidaygroup()` (324-362) — persists a holiday group.
6. `delete()` (243-273) / `deletegroup()` (273-324) — remove a holiday / group.
7. `listholidays()` (362-407) / `listholidaygroup()` (139-160) / `listholidaygroupforconfig()` (160-184) / `listholidaygroupforgrid()` (407-440) — various listing endpoints feeding grids/dropdowns.

**Forms:** Holiday form and holiday-group form; duplicate-date validation confirmed via AJAX call to `checkholidaydateexists` before submit (`View/HolidayCalendar/index.ctp:210,333`).

**Notifications:** None found.

**AJAX/JS endpoints:** `HolidayCalendar/checkholidaydateexists` (`View/HolidayCalendar/index.ctp:210,333`).

---

## DayTimeProcedureController.php (Controller/DayTimeProcedureController.php)

**Duplicates noted, not traced:** `DayTimeProcedureController_nimisha_edited_backup.php`, `DayTimeProcedureControllerbackupnimishas.php`.

**Purpose:** Defines "day/time procedures" (i.e., shift policies/templates: on-duty/off-duty times, working-time calculation rules) that feed `ShiftPlannerController` and attendance/OT computation elsewhere in this cluster (`working_day_time_procedures` table, referenced from `ShiftPlannerController.php:132-146`).

**Who can access it:** Global gate only; no explicit gate found.

**Important implementation detail — explicit view overrides:** Several actions in this controller override CakePHP's default view resolution with `$this->render(...)`, meaning the plainly-named `.ctp` files are **dead** and the `_new`/`_nw` variants are canonical:
- `index()` (`DayTimeProcedureController.php:51-82`) calls `$this->render('index_nw')` (line 77) → canonical view is `View/DayTimeProcedure/index_nw.ctp`; `index.ctp` (and its many `_bkup`/`indexbackupnimisha.ctp` siblings) are dead.
- `lists()` (82-131) calls `$this->render('lists_new')` (line 126) → canonical view is a `lists_new.ctp`-style file (not present in the `ls` output captured — flag for follow-up: only `lists.ctp` and `lists.ctp#bkup_athira_11_04_2025` were listed; if `lists_new.ctp` genuinely doesn't exist, this render call would error at runtime — worth double-checking directly in a follow-up pass).
- `form()` render call is at line 657 → `$this->render('form_new')` → canonical view is `View/DayTimeProcedure/form_new.ctp` (has 2026-dated backups, e.g. `form_new.ctp#backup_Akshay_25-5-2026`), while `form.ctp`, `form_nimish_modified.ctp`, `formbakupnimisha.ctp`, `form.ctp_bkup*` are all dead legacy variants.

**Step-by-step user flow:**
1. `index()` (51-82, renders `index_nw.ctp`) — lists existing day/time policies.
2. `listpolicies()` (131-164) / `listpoliciesforconfig()` (164-189) — AJAX policy lists for grids/dropdowns.
3. `saveDayTimeProcedure()` (189-562, the largest action here at ~370 lines) — creates/updates a shift policy; likely includes complex duration/rounding rule calculations given its size (not traced line-by-line).
4. `delete()` (562-614) — remove a policy.
5. `form()` (614-662, renders `form_new.ctp`) — the add/edit form; `checkshiftpolicyexists($codecount, $day_time_seq)` (662-683) is a duplicate-check helper (same pattern as `HolidayCalendarController::checkholidaydateexists`).
6. `roaster()` (683-692) — likely a typo'd link into the roster/`ShiftPlanner` feature (INFERRED from name; not traced).
7. Exception handling for a policy: `saveException()` (713-839), `getExceptions()` (839-865), `deleteException()` (865-914), `deleteTempExceptions()` (692-713), `toggleExceptionStatus()` (945-979), `getDayStatus()` (914-945) — lets admin define date-specific exceptions to a standard shift policy (e.g., a one-off different working time on a specific date).

**Forms:** Shift-policy form (`form_new.ctp`) with duplicate-code existence check (`checkshiftpolicyexists`) before save, mirroring the Holiday Calendar's validation pattern.

**Notifications:** None found.

---

## MobileLocationUpdateController.php (Controller/MobileLocationUpdateController.php)

**Purpose:** Admin-facing GPS/mobile-location tracking report for field employees — shows a map of location pings within a date range for a chosen employee, with Excel/PDF export, backed by `mob_user_tracking` table data presumably captured by a companion mobile app (not part of this codebase).

**Who can access it:** Global gate only (`user_group` read but only used for data-scoping, not blocking). `index()` (`MobileLocationUpdateController.php:33-76`) branches employee-list scope by `user_group`:
- Lines 50-62: special-case for `user_group == '2'` at companies `GLET`/`ABSG` — restricts to the employee's own branch via a `get_branch_code_abs_fn` SQL function unless they're flagged "HO" (`is_ho`).
- Lines 65-72: general case for any `user_group == 2` — restricts employee list to their own `branch_code`.
- Admin (`user_group == 1`) sees all employees (no `$conditions` applied), lines 43, 74.

**Step-by-step user flow:**
1. `index()` (33-76) — renders `View/MobileLocationUpdate/index.ctp` with branch list (`Units` model, line 39) and the (possibly branch-scoped) employee list (line 74).
2. `loadmap()` (78-121) — POST: given `emp_key`/`from_dates`/`to_dates`, looks up the employee's `user_id`, then queries `mob_user_tracking` joined to `employee_info` for pings in range (lines 88-101), sets `arr_location`/`datas` (JSON-encoded lat/long/location array, line 105) for the view's map render, and logs the view into `ReportAudit` (lines 106-120, `report_type = "Mobile Tracking Report"`, `mode = "View Report"`) — same audit pattern as `AttendanceCheckInOutController`.
3. `employeelist()` (123-152) — AJAX employee list filterable by `branch`/`depart`/`designation` query params (autoRender=false JSON, lines 124-152).
4. `downloadexcel($emp, $start_date, $end_date)` (154-313) — generates an `.xlsx` via `PHPExcel` (App::import, line 197) with columns Sl No / Employee ID / Name / Department / Branch / Date & Time / Location (lines 234-244), logs to `ReportAudit` with `mode = "Excel Download"` (163-176), and streams the file then deletes the temp copy (306-311).
5. `downloadpdf($mode)` (315-355) — generates a PDF via `HTML2PDF` (line 344) from the `downloadpdf.ctp` view, output as a forced download (`Output('Mobilelocation.pdf', 'D')`, line 349).

**Forms:** Filter form only (employee + date range) — no data entry, this is purely a viewing/reporting tool.

**Notifications:** None (audit logging only, not user-facing notification).

**AJAX/JS endpoints:** `MobileLocationUpdate/employeelist` (line 123, filtered by branch/depart/designation), `MobileLocationUpdate/loadmap` (line 78, POST).

---

## Summary table: canonical vs legacy in this cluster

| Feature | Canonical controller | Legacy/duplicate (not deep-traced) |
|---|---|---|
| Attendance register book | `AttendanceRegisterNewController.php` (esp. `indexneww`, confirmed via `AttendanceSetupController.php:87-91`) | `AttendanceController.php` (+2 bkup files), `AttendanceregisterController.php` |
| ESS "my register" | Unclear — 3 candidates coexist | `EmpattendanceController.php`, `EmpattendanceregisterController.php`, `EmployeeAttendanceregisterController.php` — **confirm live one via `emp_menu` table** |
| Punch editor (admin) | `EditPunchesController.php` (superset of actions) | `EditAttendanceController.php` (near-identical, fewer sync features), `EditPunchesController_bkup-24-10-2017.php` |
| Attendance bulk upload | `EmployeeAttendanceUploadController.php` (larger, has leave-check) | `EmpattendanceuploadController.php` |
| Daily OT verify | `DailyOvertimeVerifyNewController.php` (confirmed by superset of actions + 2026 view edits) | `DailyOvertimeVerifyController.php` |
| OT register/approval | `OtAttendanceNewController.php` (confirmed by superset of actions + 2026 view edits) | `OtAttendanceController.php` |
| Regularisation admin view | `Regularisation::adminindexnew` (confirmed via `AttendanceSetupController.php:80-84`) | `Regularisation::adminindex` (fallback for specific legacy company codes only) |
| Day/time (shift) policy | `DayTimeProcedureController.php` rendering `index_nw.ctp`/`form_new.ctp` (explicit `$this->render()` overrides) | Same controller's default-named views (`index.ctp`, `form.ctp`) are dead; 2 whole-file backups also excluded |

## Open questions to confirm with the client / DB before finalizing migration scope

1. Which of the three ESS "my attendance register" controllers (`EmpattendanceController`, `EmpattendanceregisterController`, `EmployeeAttendanceregisterController`) is actually linked from the live employee menu — requires querying the `emp_menu`/`Features` tables in the running DB, not resolvable from code alone.
2. Whether `sendmemo()` in `EditAttendanceController.php:1349`, `EditPunchesController.php:3475`, and `RegularisationController.php:2876` (all identical dead/test stubs sending to hardcoded `sruthiforsight@gmail.com`) are truly unused, or wired up from some other caller not covered in this pass — if genuinely dead, no notification behavior needs to be replicated for punch-edit/regularisation flows.
3. Whether `DayTimeProcedureController::lists()`'s `$this->render('lists_new')` (line 126) resolves to an existing file — `lists_new.ctp` was not observed in the `View/DayTimeProcedure` listing captured during this research; if missing, this action may currently error and should not be replicated as-is.

---

## 4. Leave Management

# Leave Management — User-Facing Behavior Report

Scope: `Controller/LeaveRequestController.php`, `Controller/EmployeeLeaveRequestController.php`,
`Controller/EmployeeLeavesController.php`, `Controller/EmployeeLeaveUploadController.php`,
`Controller/EmpleaveuploadController.php`, `Controller/LeaveEncashmentRequestController.php`,
`Controller/LeavePolicyController.php`, `Controller/LeaveapiController.php`, and their `View/` folders.

Duplicate/backup files noted but not analyzed as separate behavior: `Controller/LeaveRequestControllerBkups.php`,
`Controller/LeaveRequestControllerNimisha_edited.php`, and the many `*.ctp#bkup_*` / `*.ctp_bkp*` view files
alongside each active `.ctp` (these are dated backups left in place by developers, not live routes).

---

## Data model / state machine primer

`legacy/Model/LeaveRequests.php:7-16` — model `LeaveRequests`, primary key `LEAVEENTRYID`, backed by table
`leaveentries`. It exposes one stored-procedure helper, `leaveTransactionPrc()`
(`Model/LeaveRequests.php:17-44`), which calls MySQL procedure `leave_transaction_prc(...)` — this is invoked
after every status-changing update to propagate the change into `emp_leave_transactions` (the day-by-day leave
ledger used for attendance/payroll).

Key columns driving the workflow (all on `leaveentries`, confirmed via field lists throughout
`LeaveRequestController.php`/`EmployeeLeaveRequestController.php`):
- `LEAVESTATUS` (string enum, see state machine section)
- `ISAutherizedby` — emp_pkey of the person who must Authorize (step 1 approver)
- `ISAutherized` (0/1) — whether step-1 authorization is done
- `Autherized_date`
- `APPROVEDBY` — emp_pkey of the person who must Approve (step 2 / final approver)
- `ISAPPROVED` (0/1) — whether final approval is done
- `APPROVED_date`
- `ApproveRemarks`, `AuthoriseRemarks`, `Reason`, `message`
- `salary_head_item_fkey` (leave type), `FROMDATE`/`FROMHALF`, `TODATE`/`TOHALF`, `leave_days`, `contact_person`,
  `contact_No`, `cctome` (cc list)

`EmpLeaveApproval` model (used in `LeaveRequestController.php:2385` etc.) stores one row per
authorize/final-approval event with a one-time email token (`email_url`) used for the emailed magic-link
approval flow (see Leaveapi/Site "approval" route below).

---

## 1. `Controller/LeaveRequestController.php` (Admin/HR-side leave management, 6173 lines)

### Purpose
HR-staff/admin controller for the leave module: employee's own "My Leave Requests" list, the "Employee Leave
Requests" (team/company-wide) queue for Authorize/Approve/Reject, leave application form, leave balance
calculations, cancellation workflow, and the full state-machine engine (`grandLeave()`).

### Who can access it
Gated only by the blanket `AppController::beforeFilter()` check — any session with `user_group` 1 or 2 is let
through (`Controller/AppController.php:43-46`); this controller does not itself branch on `user_group`. Finer
per-menu visibility ("Leave Request", "Team Leave Requests", etc.) is controlled client-side/menu-side via
`user_access`/`emp_menu` (`Controller/AppController.php:119-123` reads `active` from `user_access` for menu id of
'Team Leave Requests' to decide whether to show the sidebar notification badge) — actual server-side action
methods in this controller do **not** re-check `user_access`/menu_id; access control is effectively enforced by
menu visibility in the UI plus the fact that queries filter by the logged-in `emp_fkey` as approver/authorizer.
INFERRED: any authenticated user_group 1/2 session could call these AJAX endpoints directly (e.g. via browser
devtools) for leave rows not addressed to them, since `grandLeave()` does not verify `cur_emp_key` equals
`ISAutherizedby`/`APPROVEDBY` before applying the state transition (it only branches presentation logic on that
comparison, e.g. `LeaveRequestController.php:2532`, `2626`, `2658`).

### Step-by-step user flow (full lifecycle)

**1. Apply (employee, any user_group, applies for self via "My Leave Requests")**
- View `View/LeaveRequest/index.ctp:1-230` — landing page "My Leave Request(s)" grid, sourced from
  `LeaveRequest/listleaves` (AJAX, `LeaveRequestController.php:969`), toolbar buttons: "New Leave" → opens modal
  `LeaveRequest/addeditleave_new/0` or `LeaveRequest/addeditleave/0` depending on company code
  (`View/LeaveRequest/index.ctp:66-81`, a per-tenant restricted-company list); "Leave Details" → edit modal;
  "Show Leave Days" → `LeaveRequest/showleavedays/{id}`; "Remove" → AJAX `LeaveRequest/deleteLeaveRequests`
  (blocks removal of `Approved`/`Authorized`/`CancellationOfApproved` leaves client-side,
  `View/LeaveRequest/index.ctp:148-165`).
- Form submit posts to `saveLeaveEntry()` (`LeaveRequestController.php:1648`). New applications:
  - Server checks for existing attendance punches in the requested half/day via `checkAttendancePunches()`
    and blocks the request with a flash-style JSON message if attendance already exists
    (`LeaveRequestController.php:1689-1704`, three distinct messages for first-half/second-half/full-day
    conflicts).
  - Server checks whether the month's attendance register has already been "verified" (locked) for the
    employee and blocks the save if so: `"Leave Can not be saved , Attendance Verified for this Month"`
    (`LeaveRequestController.php:1753-1757`).
  - On success, row is inserted with `LEAVESTATUS = 'Applied'`, `ISAutherizedby`/`APPROVEDBY` set from the
    submitted approver picks (`LeaveRequestController.php:1670-1684`).
- **Who gets notified**: the picked `ISAutherizedby` employee. `AppController.php:100-127` — every page load for
  `user_group == 2` sessions runs a query joining `leaveentries`/`emp_details` for rows where
  `ISAutherizedby = <session emp>` AND `ISAutherized = 0` AND `LEAVESTATUS = 'Applied'` (OR `APPROVEDBY = <session
  emp>` AND `LEAVESTATUS = 'Authorized'` pending final approval) and exposes it to every view as `noti`
  (in-app badge/notification list). A parallel "Team Leave Requests" menu-visibility flag (`showteamleavenoti`)
  is derived from `user_access` for that specific `menu_id` (`AppController.php:119-123`).
- No outbound email is sent at the moment of applying in `LeaveRequestController::saveLeaveEntry()` itself —
  email notification is only triggered later, at Authorize/Approve/Reject time (see below), i.e. the first
  authorizer only learns about a new "Applied" leave via the in-app `noti` badge, not email, unless/until it
  changes status. (INFERRED from absence of any `send*mail()` call inside `saveLeaveEntry()`.)

**2. Authorize (step 1 approver, `ISAutherizedby`)**
- View `View/LeaveRequest/employeeleaves.ctp:1-185` — "Employee Leave Request" queue, two tabs: "To be Verify"
  (AJAX `LeaveRequest/listempleaves`, `LeaveRequestController.php:281`) and "Verified" (AJAX
  `LeaveRequest/listempleavesverified`, `LeaveRequestController.php:555`). "Manage Leave" toolbar button opens
  modal `LeaveRequest/manageempleave/{LEAVEENTRYID}`.
- `manageempleave()` (`LeaveRequestController.php:621-905`) loads full leave detail plus computes `$ISAutherized`
  /`$ISAPPROVED` flags scoped to the *current* logged-in employee (i.e. whether *this* viewer has already acted)
  and the current `LEAVESTATUS`, and renders `View/LeaveRequest/manageempleave.ctp`.
- View `View/LeaveRequest/manageempleave.ctp` shows the leave detail, an "Authorize"/"Approve" button (label
  depends on whether a 3rd-level `sanction` step exists, `manageempleave.ctp:126,241-245`), a "Reject" button, and
  for cancellation states an "Authorize Cancellation"/"Approve Cancellation" button
  (`manageempleave.ctp:248-256`). Clicking any of these calls the JS `grandLeave(obj)`
  (`manageempleave.ctp:362-368`), which reads the button's own label into a hidden `#actionType` field and
  submits the form to `LeaveRequest/grandLeave` (`manageempleave.ctp:2`).
- Server action `grandLeave()` (`LeaveRequestController.php:2301-2857`) is the single state-machine engine for
  ALL transitions (Authorize/Approve/Reject/Approve Cancellation/Authorize Cancellation/Cancelled). It:
  1. Re-validates leave balance for `Applied` leaves via `criterias()` (`LeaveRequestController.php:2374-2384`)
     — blocks the action with a JSON error message if balance criteria fail.
  2. Looks up `ALLOW_NEGETIVE` policy flag and recomputes monthly/yearly balance
     (`getLeaveBalanceForAuthOrApproval`/`getYearlyLeaveBalanceForAuthOrApproval`,
     `LeaveRequestController.php:2404-2439`) — currently the actual balance-insufficient block is commented out
     (dead code at `LeaveRequestController.php:2411-2416`, `2428-2432`), i.e. **negative-balance requests are not
     actually blocked at authorize/approve time**, only surfaced informationally.
  3. Branches on `$actionType` to set `LEAVESTATUS`, `ISAutherized`, `ISAPPROVED`, `Autherized_date`,
     `APPROVED_date`, and to decide which email(s) to fire (see Notifications below). See "Leave Approval
     Workflow" section for the full transition table.
  4. Writes `ApproveRemarks`/`AuthoriseRemarks` from the form (`LeaveRequestController.php:2822-2826`).
  5. Persists via `LeaveRequests->updateAll()` (raw SQL fragment assembly — values are string-interpolated,
     not parameterized, e.g. `LeaveRequestController.php:2529,2653` etc. — INFERRED SQL-injection-adjacent risk
     worth flagging for the rewrite, though remarks/actionType come from a closed set of buttons in this
     particular flow).
  6. Calls `callLeaveTransactionProcedure($leaveentryId)` → wraps `LeaveRequests->leaveTransactionPrc()`
     (`Model/LeaveRequests.php:17`) to sync `emp_leave_transactions`.
  7. Returns JSON `{success, leaveentryId, message: "Leave <Actioned> successfully"}`.
- **What the employee sees when status changes**: no live push; employee sees the new `LEAVESTATUS` next time
  they load "My Leave Requests" (`LeaveRequest/listleaves`) or via the `noti` badge if they are also acting as
  someone else's approver. The *applicant* is notified primarily by email (see Notifications), not by an in-app
  badge — the `noti` badge in `AppController.php` only tracks items requiring the *viewer's own* action
  (as authorizer/approver), not "your own request was actioned" updates for the applicant.

**3. Approve (step 2 approver, `APPROVEDBY`)** — same `manageempleave.ctp` / `grandLeave()` flow; when the
current status is already `Authorized`, the button-driven `actionType` is `Approve`, moving `LEAVESTATUS` to
`Approved` (`LeaveRequestController.php:2652-2669`) unless a third "final sanction" party exists
(`leval_of_approval = 3` in `leavepolicy`, `LeaveRequestController.php:2485`), in which case an
`Authorized` intermediate state routes an approval-link email to that 3rd party instead of finalizing
(`LeaveRequestController.php:2527-2559`, `sendfinalapprovalmail()`).

**4. Reject** — either party (whoever currently owns the pending step) can Reject; sets `LEAVESTATUS = 'Rejected'`
and emails the applicant (`LeaveRequestController.php:2701-2720`).

**5. Cancellation sub-workflow** — once a leave is `Authorized`/`Approved`, the employee can request cancellation
from "My Leave Requests" edit form (`myLeaveAction = 'Cancellation Applied'`), which sets
`LEAVESTATUS` to `CancellationOfAuthorized` or `CancellationOfApproved` depending on the prior status
(`LeaveRequestController.php:1804-1817`). The same `manageempleave.ctp`/`grandLeave()` flow is then used by the
authorizer/approver with `actionType = 'Authorize Cancellation'` / `'Approve Cancellation'` to push it to
`Cancellation Authorized`/`Cancellation Approved`, or `Reject` to revert it back to `Authorized`/`Approved` with
message `"Leave cancellation Rejected"` (`LeaveRequestController.php:2671-2721`). A direct `Cancelled` action
(admin override) sets `LEAVESTATUS = 'Cancelled'` outright (`LeaveRequestController.php:2796-2809`).

### Forms
- **Apply/Edit leave** (`addeditleave.ctp` / `addeditleave_new.ctp`, posts to `saveLeaveEntry`): fields —
  leave type (`salary_head_item_fkey`), From/To date + half-day flags, Reason, Contact person, Contact number,
  leave days (computed), file upload (supporting document, handled by `saveDocument()`
  `LeaveRequestController.php:1543`), CC recipients (`cc[]`), "Notified by" (`Notifiedby`), Authorizer/Approver
  pick. Server-side validation: attendance-punch conflict check, attendance-register-locked check, "cannot apply
  0 days" check (`LeaveRequestController.php:1819-1825`), leave-balance criteria (`criterias()`,
  `LeaveRequestController.php:2222`). Errors returned as JSON `{success:false, message: "..."}` consumed by the
  view as inline/flash-style `$.notify` messages (client pattern seen throughout, e.g.
  `View/LeaveRequest/index.ctp:107-116`).
- **Manage/Authorize/Approve leave** (`manageempleave.ctp`, posts to `grandLeave`): read-only leave detail +
  Approver picker ("Pick"/"Cancel"/"Change" buttons, `manageempleave.ctp:154-155,371-419`), Authorize/Approve
  Remarks textarea, Reject button. No numeric/date validation here — purely a workflow-action form.

### Notifications
- **Email** — sent via a hand-rolled `PHPMailer` instance (not CakePHP's `EmailComponent`) inside each
  `send*mail()` helper (`LeaveRequestController.php:3332` `sendauthorizationmail`, `:3634`
  `sendfinalapprovalmail`, `:3940` `sendapprovemail`, `:4219` `sendapprovedmail`, `:4496` `sendrejectedsmail`,
  `:4785` `sendcancellationappliedmail`, `:5067` `sendcancellationapprovedmail`, `:5339` `sendcancelledmail`).
  SMTP credentials are hardcoded in the controller (Oracle Cloud email relay,
  `LeaveRequestController.php:3374-3383` — flagged as a secrets-hygiene issue for migration, not user behavior).
  Emails support CC (`cctome` field) and embed a company logo image. When a final third-level sanction is
  required, the email contains a one-time "magic link" (`Site/approval/{company_code}/{key}#{hash}`,
  `LeaveRequestController.php:2544-2553`) allowing the approver to act without logging in normally — token stored
  in `EmpLeaveApproval` model.
- **In-app badge** — `noti` variable set in every page's layout via `AppController.php:100-127` for
  `user_group == 2` sessions: lists leaves pending the current user's Authorize or Approve action. A separate
  `showteamleavenoti` flag (`AppController.php:119-123`) controls whether the "Team Leave Requests" menu item
  shows a notification dot, driven by `user_access.active` for that menu_id.

### AJAX/JS endpoints hit from these views
`LeaveRequest/listleaves`, `LeaveRequest/listempleaves`, `LeaveRequest/listempleavesverified`,
`LeaveRequest/addeditleave(_new)/{id}`, `LeaveRequest/manageempleave/{id}`, `LeaveRequest/grandLeave`,
`LeaveRequest/deleteLeaveRequests`, `LeaveRequest/showleavedays/{id}`, `LeaveRequest/saveDocument`,
`LeaveRequest/deletedoc`, `LeaveRequest/getReportingEmployeeList`, `LeaveRequest/GetLeaveBalance(New)`,
`LeaveRequest/getLeaveBalanceForAuthOrApproval`, `LeaveRequest/getYearlyLeaveBalanceForAuthOrApproval`,
`LeaveRequest/getusers`, `LeaveRequest/getusers_notify`.

---

## 2. `Controller/EmployeeLeaveRequestController.php` (ESS/self-service leave, 5980 lines)

### Purpose
Near-identical duplicate of `LeaveRequestController.php` scoped for the Employee Self-Service (ESS) surface —
same action names (`index`, `employeeleaves`, `manageempleave`, `addeditleave`, `saveLeaveEntry`, `grandLeave`
[named differently — see below], `getusers`, `getLeaveBalanceForAuthOrApproval`, etc.), same view filenames under
`View/EmployeeLeaveRequest/`. Confirmed structurally parallel via side-by-side line-number diffing of function
lists (both controllers define the same ~35 public actions at nearly the same offsets,
`EmployeeLeaveRequestController.php:60-3305` vs `LeaveRequestController.php:71-3305`).

### Who can access it
Same as above — gated only by the generic `user_group` 1/2 session check (`AppController.php:43-46`); no
menu_id re-check inside action methods. INFERRED: this is the ESS-branded route employees use from their own
dashboard, while `LeaveRequestController` is likely the HR/admin-branded equivalent — both appear to operate on
the same `leaveentries` table with no `user_group` differentiation inside the controller itself, i.e. the
separation is purely at the menu/routing layer (which controller a given nav link points to), not enforced
server-side.

### Step-by-step user flow / Forms / Notifications
Identical mechanics to `LeaveRequestController.php` above — apply via `saveLeaveEntry()`
(`EmployeeLeaveRequestController.php:1774`, contains the same attendance-punch and attendance-register checks,
`:1994` `checkIfLeaveRequestEditable`-guarded), authorize/approve/reject via the same state machine, here named
`grandLeave` as well but note it also exposes an extra endpoint `Getauther_approv()`
(`EmployeeLeaveRequestController.php:1204`) not present in `LeaveRequestController.php` — appears to fetch the
Authorizer/Approver display names for the manage-leave modal header. Email helpers are duplicated 1:1
(`sendauthorizationmail` etc. at effectively the same relative offsets) with the same hardcoded SMTP
credentials. The `noti` query at `EmployeeLeaveRequestController.php:5095/5104` is byte-identical to
`AppController.php:105`.

### AJAX/JS endpoints
Same endpoint surface under `EmployeeLeaveRequest/` prefix instead of `LeaveRequest/`, plus
`EmployeeLeaveRequest/Getauther_approv`, `EmployeeLeaveRequest/printleave/{id}` (an extra print/export action not
present in `LeaveRequestController.php`, `EmployeeLeaveRequestController.php:927`).

INFERRED for migration: `LeaveRequestController` and `EmployeeLeaveRequestController` should very likely collapse
into a single Next.js feature module with one state machine and one set of API routes — the duplication looks
like a historical copy-paste fork (possibly HR-view vs ESS-view of the identical feature) rather than two
distinct behaviors. Confirm with product/HR stakeholders which one is the "canonical" surface before merging, in
case subtle divergences (e.g. `Getauther_approv`, `printleave`) are load-bearing for one audience.

---

## 3. `Controller/EmployeeLeavesController.php` (Admin bulk leave entry/upload, 1832 lines)

### Purpose
Lets an admin/HR user directly create or bulk-manage leave entries **on behalf of** an employee (as opposed to
the employee self-applying) — a single-record "add leave for employee" form plus list/approve/cancel actions
that bypass the normal apply→authorize→approve chain.

### Who can access it
Gated by generic session check only. `index()` branches display (not access) on `user_group`
(`EmployeeLeavesController.php:42-58` — branch-scoping code is commented out/disabled "hided ... by sinsiya", so
currently both groups see all branches). `form()` still applies branch-scoping for `user_group == 2`
(`EmployeeLeavesController.php:105-115`), i.e. an ESS user landing on this admin-ish form only sees employees in
their own branch — suggesting this controller may in practice be reachable by both HR and certain
manager-tier ESS accounts, gated at the menu level.

### Step-by-step user flow
- `index()` (`EmployeeLeavesController.php:28-61`) — landing page listing branches, reads `comp_contact_info.plan`
  to conditionally show the branch-wise UI.
- `form()` (`EmployeeLeavesController.php:86-170`) — single-employee leave-entry form (employee picker, leave
  type, start/end date+session).
- `leavesave()` (`EmployeeLeavesController.php:172-...`) — saves directly with `LEAVESTATUS` effectively
  pre-approved: sets `Autherized_date`, `APPROVED_date`, `AuthoriseRemarks = 'Leave authorised from Leave
  Upload'`, `ApproveRemarks = 'Leave approved from Leave Upload'` immediately (`EmployeeLeavesController.php:196-
  200`) — i.e. leaves entered here skip the Authorize/Approve steps entirely (admin override). Computes
  `leave_days` from date range with half-day adjustments (`:208-220`) and blocks duplicate/overlapping leave in
  the same range with message `"Leave already existing in the range {from} - {to}. Please remove it, before
  applying."` (`:225-231`).
- `approveleave()` (`EmployeeLeavesController.php:801-821`) — bulk-approves selected `LEAVEENTRYIDS` by
  force-setting `LEAVESTATUS = 'Approved'` and remark `"Approved By ADMIN on {date}"`, bypassing the
  authorize/approve chain entirely.
- `deleteleave()` (`EmployeeLeavesController.php:780-799`) — bulk sets `LEAVESTATUS = 'CancelledByAdmin'` (a
  status value distinct from the employee-initiated `Cancelled`), with remark `"Cancelled By ADMIN on {date}"`.

### Forms
Employee, leave type, start date + session (1=first half/2=second half), end date + session. No file upload.
Server-side dedupe check against `emp_leave_transactions` for overlapping approved/applied/authorized leave in
the date range (`EmployeeLeavesController.php:224-231`).

### Notifications
No `send*mail()` calls found in this controller — INFERRED that admin-entered/bulk-approved/cancelled leaves via
this screen do **not** trigger employee email notification (a gap worth flagging explicitly for the rewrite if
email parity is desired).

### AJAX/JS endpoints
`EmployeeLeaves/branchwiss/{branch}`, `EmployeeLeaves/form/{id}`, `EmployeeLeaves/leavesave`,
`EmployeeLeaves/load`, `EmployeeLeaves/listleave`, `EmployeeLeaves/deleteleave`, `EmployeeLeaves/approveleave`,
`EmployeeLeaves/getfinyear`, `EmployeeLeaves/getleave`, `EmployeeLeaves/GetLeaveBalance`,
`EmployeeLeaves/getLeaveType(ByOccurance)`, `EmployeeLeaves/leavecheck`, `EmployeeLeaves/showleavedays/{id}`,
`EmployeeLeaves/showfailedleaverequests`, `EmployeeLeaves/filterHeads`. Also contains unrelated CTC-upload
actions (`downloadempctcformat`, `uploadandsaveempctc`, `EmployeeLeavesController.php:869,1143`) that appear to
be copy-paste leftovers from a CTC-upload controller and not part of the leave feature
(INFERRED — these read/write `emp_ctc`/CTC-format Excel, unrelated to `leaveentries`).

---

## 4. `Controller/EmployeeLeaveUploadController.php` (2573 lines) — near-duplicate of #3

### Purpose
Structurally parallel to `EmployeeLeavesController.php` (same action names/offsets: `index`, `branchwiss`,
`form`, `leavesave`, `load`, `listleave`, `deleteleave`, `getfinyear`, `getleave`, `downloadempctcformat`,
`uploadandsaveempctc`, `GetLeaveBalance`, `getLeaveType`, `leavecheck`, `getLeaveBalanceForAuthOrApproval`,
`showfailedleaverequests`) plus extras: `getEmployeeDates()` (`EmployeeLeaveUploadController.php:197`),
`checkLeaveDays()` (`:2123`), `GetLeaveBalanceNew()` (`:2203`), and a second form variant `form_new()` (`:2373`).
Views live under `View/EmployeeLeaveUpload/` including a newer `form_new.ctp`.

### Who can access it / flow / forms / notifications
Same admin bulk-entry pattern as `EmployeeLeavesController.php` — same immediate-authorize/approve bypass on
save, same no-email-on-save behavior (no `send*mail` calls found here either). INFERRED this is a newer
iteration of `EmployeeLeavesController.php` (extra `_new` form and extra balance-calc helpers suggest active
iteration), and the two should likely be reconciled into one feature for the rewrite — confirm with the team
which is currently live in navigation (`EmployeeLeaves` vs `EmployeeLeaveUpload`) since both have viewer files
maintained up through 2025 per the backup-file dates (e.g. `View/EmployeeLeaveUpload/index.ctp#bkup_bindu_10_12_2025`).

### AJAX/JS endpoints
Mirrors #3 under `EmployeeLeaveUpload/` prefix, plus `EmployeeLeaveUpload/getEmployeeDates`,
`EmployeeLeaveUpload/checkLeaveDays`, `EmployeeLeaveUpload/GetLeaveBalanceNew`, `EmployeeLeaveUpload/form_new`.

---

## 5. `Controller/EmpleaveuploadController.php` (1433 lines) — Leave *balance* upload (opening balances)

### Purpose
Distinct from #3/#4: this is for bulk-uploading/setting **leave balance** figures (opening balances, encashment
eligibility) per employee/leave-type, not individual leave requests — evidenced by `Model/EmployeeLeaveBalanceUpload.php`
usage and actions `leavebalance()`, `listleavebalance()`, `downloadLeaveBalanceUploadctc()`,
`uploadandsaveempctcleavebalance()` (`EmpleaveuploadController.php:892,931,1013,1222`) alongside a
leave-*request* form/leavesave pair that mirrors #3/#4 (`:45,133`).

### Who can access it
Generic session gate only. `form()` applies branch-scoping for `user_group == 2` at companies `GLET`/`ABSG`
specifically (`EmpleaveuploadController.php:64-75`), using a `get_branch_code_abs_fn` SQL function to detect
head-office vs branch employees and restrict the employee picker accordingly — a tenant-specific business rule,
not a general access-control gate.

### Step-by-step user flow
- `form()` (`EmpleaveuploadController.php:45-130`) — same single-employee leave-entry form pattern as #3/#4, with
  the added branch restriction above; excludes leave types where `item_part = 'Indirect'`
  (`:82`).
- `leavebalancesave()` (`EmpleaveuploadController.php:133-...`) — saves an opening/adjustment leave-balance
  record (separate table `employee_leave_balance_upload` via `EmployeeLeaveBalanceUpload` model), not a leave
  request row.
- `leavebalance()`/`listleavebalance()` — list/manage screen for previously uploaded balances.

### Forms
Leave-request half: employee, leave type (excluding "Indirect"), start/end date+session — identical shape to
#3/#4. Leave-*balance*-upload half: employee, leave type, balance figure, presumably an effective date (not
fully traced within the read window — INFERRED from action names `leavebalancesave`/`downloadLeaveBalanceUploadctc`).

### Notifications
No `send*mail` calls found — INFERRED no email is triggered by balance-upload or by the embedded leave-request
save path in this controller.

### AJAX/JS endpoints
`Empleaveupload/form`, `Empleaveupload/leavebalancesave`, `Empleaveupload/load`, `Empleaveupload/listleave`,
`Empleaveupload/deleteleave`, `Empleaveupload/getleave`, `Empleaveupload/downloadempctcformat`,
`Empleaveupload/uploadandsaveempctc`, `Empleaveupload/getleavebalance`, `Empleaveupload/getLeaveType`,
`Empleaveupload/leavebalance`, `Empleaveupload/listleavebalance`, `Empleaveupload/downloadLeaveBalanceUploadctc`,
`Empleaveupload/uploadandsaveempctcleavebalance`, `Empleaveupload/getLeaves`.

---

## 6. `Controller/LeaveEncashmentRequestController.php` (1127+ lines, read to line 799)

### Purpose
Separate sub-workflow: converting unused leave balance into a cash payout ("leave encashment"). Employee/admin
applies for encashment of a specific leave type up to a policy-defined `leave_encash_limit`; admin verifies and
approves; approved records feed into payroll (`payroll_master.action`).

### Who can access it
Generic session gate. `addleave()` (`LeaveEncashmentRequestController.php:114-155`) restricts the employee list
to the caller's own branch for `user_group == 2` at companies `GLET`/`ABSG` only (same `get_branch_code_abs_fn`
pattern as #5) — otherwise all employees are listed regardless of `user_group`, i.e. by default any user_group 2
(ESS) session could request/browse encashment for other employees through this screen unless the tenant is one
of the two hardcoded companies. INFERRED gap: menu-level hiding is likely the only real protection for most
tenants.

### Step-by-step user flow
1. **Apply**: `addleave()` renders the request form; `encashlisting()`
   (`LeaveEncashmentRequestController.php:689-709`) invokes stored procedure `leave_encash_insert_prc(...)` to
   create/insert an encashment row (per branch/employee/leave-type/month) — returns `{msg: true|false}`.
2. **List pending**: `listallempsforenc()` / `listallempsforencash()`
   (`LeaveEncashmentRequestController.php:185-356`) — grid of not-yet-approved (`is_approved != 'Y'`) requests,
   filterable by branch/employee/leave-type/month; for non-restricted-company tenants, computes `can_encash`
   by comparing `already_encashed` this policy cycle against `leave_encash_limit`
   (`LeaveEncashmentRequestController.php:301-353`).
3. **Edit before approval**: `editvalue()` (`LeaveEncashmentRequestController.php:173-183`) lets the requester
   adjust `requested_days`/`approved_days` on an un-approved row directly via raw SQL update.
4. **Cancel**: `cancelentries()` (`LeaveEncashmentRequestController.php:571-588`) hard-deletes the row(s) from
   `leave_encashment_master`.
5. **Approve** (bulk): `verifyregisterentries()` (`LeaveEncashmentRequestController.php:589-621`) sets
   `is_approved = 'Y'`, `approved_by`, `approved_date`, then calls stored procedure `leave_encash_prc(...)` per
   record — this is the point the leave balance is actually debited and made payroll-eligible.
6. **Approve (single)**: `encashemp()` (`LeaveEncashmentRequestController.php:622-645`) — same effect for one
   record from the "manage" modal, sets `approved_date = today` rather than the selected payroll month.
7. **Manage/detail modal**: `manageleave()` (`LeaveEncashmentRequestController.php:764-782`) shows the request
   plus computed `eligible` balance (`leave_encash_limit` minus already-encashed-this-year) and whether payroll
   has already been `Processed`/`Approved` for that employee/month (`action` field) — used client-side to
   disable approval if payroll is locked.
8. **Verified list**: `listempleavesverified()` (`LeaveEncashmentRequestController.php:742-758`) — records
   approved by the current user (`approved_by = cur_emp_key`).

### Forms
Add leave encashment: employee, branch, leave-type (only types with `is_leave_encash = 'Y'` and a non-null
`leave_encash_limit`, `LeaveEncashmentRequestController.php:148-150`), month. Server-side validation: eligibility
math (`can_encash` boolean) computed server-side and returned to the grid for client display/disable, but note
`encashemp()`/`verifyregisterentries()` do not appear to re-validate `can_encash` before calling the approval
stored procedure — INFERRED the eligibility check is advisory/UI-only unless the stored procedure itself
enforces it (procedure body not in this codebase, out of scope).

### Notifications
No `send*mail`/`EmailComponent` calls found in the portion read (through line 799) — INFERRED no email
notification exists for the encashment workflow; status changes are only visible by reloading the relevant grid.

### AJAX/JS endpoints
`LeaveEncashmentRequest/addleave`, `LeaveEncashmentRequest/listappliedleaveencashs`,
`LeaveEncashmentRequest/editvalue`, `LeaveEncashmentRequest/listallempsforenc(ash)`,
`LeaveEncashmentRequest/listallempsforverified`, `LeaveEncashmentRequest/cancelentries`,
`LeaveEncashmentRequest/verifyregisterentries`, `LeaveEncashmentRequest/encashemp`,
`LeaveEncashmentRequest/encashlisting`, `LeaveEncashmentRequest/getusers`,
`LeaveEncashmentRequest/listempleavesverified`, `LeaveEncashmentRequest/manageleave/{id}/{emp}`.

---

## 7. `Controller/LeavePolicyController.php` (667 lines) — Leave policy configuration (HR admin)

### Purpose
CRUD for leave policies (`leavepolicy` table) and leave-policy groups (`leavepolicy_group`) — defines, per group,
which leave types apply, annual/monthly allotment, carry-forward limit, negative-balance allowance, sandwich-rule
flag, encashment eligibility, and who authorizes/approves/sanctions leave for that group (`notified_by`,
`sanction_by` employee pickers). This is configuration data consumed by the request/approval flows above (e.g.
`ALLOW_NEGETIVE`, `leval_of_approval = 3` sanction routing).

### Who can access it
Generic session gate only; no explicit `user_group`/menu_id branching found in this controller — access is
purely menu-driven (this is an HR/admin-only screen by convention, not by server-side enforcement).
INFERRED: this endpoint set should be treated as effectively "trusted admin only" during the Next.js rewrite and
given real server-side role checks, since currently any authenticated session could call `savepolicy`/`delete`
directly.

### Step-by-step user flow
1. `index()` (`LeavePolicyController.php:54-61`) — landing page.
2. `groupform()` (`:63-97`) — add/edit a leave-policy *group* name.
3. `form()` (`:125-313`) — add/edit an individual policy within a group: leave type, allotment (yearly/monthly
   split, with a tenant-specific "leave_policy_type" cycle model for a subset of company codes — restricted list
   hardcoded at `:145-148`), carry-forward limit, applicable-to (All/Male/Female), sandwich rule, negative-balance
   allowance (auto-forced to `Y` for `COFF` type leave, `LeavePolicyController.php:336-338`), document-mandatory
   flag, encashment flag/limit, notify/sanction employee pickers.
4. `savepolicy()` (`:315-396`) — persists via `LeavePolicy->save()`; always returns
   `{success:true, msg:"Leave Policy Updated"}` regardless of underlying save result (no failure branch coded)
   — INFERRED the UI cannot currently distinguish a failed save from a successful one.
5. `saveleavepolicygroup()` (`:398-417`) — same pattern for the group entity.
6. `listpolicies()`/`listpolicygroup()`/`listpolicygroupforconfig()`/`listleavetype()` — grid data sources.
7. `delete()`/`deletegroup()` (`:589-648`) — soft-delete (`status = 0`) for policies; group delete additionally
   blocks if any active employees are still assigned to the group
   (`"Leave policy cannot be deleted, remove employees under this Policy"`, `:632-636`).
8. `checkleavepolicyexists()` (`:649-665`) — duplicate-name check used for client-side validation while typing a
   new group name.

### Forms
Policy group name (uniqueness enforced via `checkleavepolicyexists`). Policy form fields: leave type
(salary_head_item_fkey, excluding types already configured for the group), yearly/monthly allotment, carry
forward limit, applicable-to, sandwich flag, negative-balance flag, document-mandatory flag, encashment
flag+limit, remarks, notify-by/sanction-by employee multi-pick. Heavy tenant-specific conditional field sets
(restricted-companies list appears 5 times in this file alone, e.g. `:145-148,235-238,281-284,302-305,326-329` —
duplicated logic that should be centralized as a feature flag in the rewrite rather than a hardcoded company-code
array repeated per action).

### Notifications
None — this is pure configuration CRUD.

### AJAX/JS endpoints
`LeavePolicy/groupform`, `LeavePolicy/jsons`, `LeavePolicy/form`, `LeavePolicy/savepolicy`,
`LeavePolicy/saveleavepolicygroup`, `LeavePolicy/listpolicies`, `LeavePolicy/listpolicygroupforconfig`,
`LeavePolicy/listpolicygroup`, `LeavePolicy/listleavetype`, `LeavePolicy/delete`, `LeavePolicy/deletegroup`,
`LeavePolicy/checkleavepolicyexists/{id}`.

---

## 8. `Controller/LeaveapiController.php` (750 lines) — unauthenticated API/webhook-style controller

### Purpose
A small set of standalone HTTP endpoints used for (a) sending the "take action" leave emails from an external
context (curl-callable, no session), and (b) an experimental single-sign-on / OTP / Firebase push-notification
bridge. This is the controller referenced by the "confirm carefully" instruction — and it is indeed unusual.

### Who can access it — IMPORTANT FINDING
`class LeaveapiController extends Controller` (`LeaveapiController.php:36`) — it extends CakePHP's base
`Controller`, **not** `AppController**. This means it does **not** inherit `AppController::beforeFilter()`
(`AppController.php:39-46`), so the `user_group` session gate that protects every other controller in this
report is **not applied here**. Every action in this file (`checkmails`, `checkLogin`, `sendOTP`, `getUID`,
`sendauthorizationmail`, `getEmployeesCount`) is reachable **without any login/session** — confirmed no
`Session->read('user_group')` check exists anywhere in the file. This is a genuine unauthenticated-endpoint
finding, not an inference.

Additional concrete findings, since this file mixes several unrelated concerns:
- `checkLogin()` (`LeaveapiController.php:103-130`) connects directly to MySQL with hardcoded root credentials
  (`'localhost', 'root', 'Localhost&*()'`, `:113`) and a hardcoded demo-tenant single-sign-on call
  (`single_signon_fn('httaiosdiosd', 'DEMO', '12611', ...)`, `:115`) — reads like leftover test/demo code that
  performs a real cross-tenant login redirect. On success it 302-redirects to
  `login.mypayrollmaster.online/Site/login?user_id=...&password=...` **with the plaintext password in the URL**
  (`:126`) — a serious secrets-in-URL/logging exposure if this code path is ever live.
- `sendauthorizationmail()` (`LeaveapiController.php:192-706`) — a duplicate, JSON-input, session-less version of
  the same "leave action" email template family as `LeaveRequestController`'s `send*mail()` methods, built to be
  triggered by `postCurlRequest()` (`:60-74`, shells out to `curl ... &` — i.e. fires an OS-level background curl
  process from PHP) from `checkmails()` (`:76-101`, itself just a hardcoded test harness posting fake data to
  `https://v1.mypayrollmaster.online/Leaveapi/sendauthorizationmail`). Same hardcoded SMTP credentials as
  `LeaveRequestController.php:3382-3383`.
- `getEmployeesCount()` (`LeaveapiController.php:708-750`) — again hardcoded root DB credentials, looks like a
  debug/scratch endpoint (only ever prints `var_dump("hello")`, does nothing with the queried data).
- `sendOTP()` (`LeaveapiController.php:132-156`) and `getUID()` (`:158-190`) — bridges to an external Firebase
  push-notification microservice (`myportalapi.mypayrollmaster.online`) using hardcoded HTTP basic-auth-style
  headers (`username: profileadmin`, `password: admin&*()`, `:179-180`) — unrelated to leave per se (generic
  push-notification helper that happens to live in this controller) but exercised by the leave-approval-link
  flow if push notifications are wired up (not confirmed from this file alone whether leave-authorize actually
  calls `sendOTP`).

### Step-by-step user flow
Not a normal UI flow — no `.ctp` views were found under `View/Leaveapi/`
(INFERRED: this controller always calls `$this->render(false)` / `autoRender=false` and returns raw
output, confirmed at `LeaveapiController.php:78-80,106-108,195-197,711-713`). It is invoked either (a) as a
server-to-server webhook target from the approval-link flow in `LeaveRequestController.php`/
`EmployeeLeaveRequestController.php` (INFERRED — the `Site/approval/{company}/{key}#{hash}` link pattern built in
`LeaveRequestController.php:2544-2553` plausibly resolves through `SiteController::approval()`, which is outside
this report's scope, and could in turn call into this Leaveapi surface — not confirmed by direct code reference
in the files read), or (b) directly by URL as an unauthenticated API for external mobile/portal integrations.

### Forms
None (JSON/GET-body API, no HTML forms).

### Notifications
This controller *is* a notification-sending mechanism (email via PHPMailer, push via Firebase) rather than a
consumer of one.

### AJAX/JS endpoints
`Leaveapi/checkmails`, `Leaveapi/checkLogin`, `Leaveapi/sendOTP`, `Leaveapi/getUID`,
`Leaveapi/sendauthorizationmail`, `Leaveapi/getEmployeesCount`. **Migration note**: none of these should be
carried over as-is; they need real authentication (or removal if dead/debug code) and secrets must move out of
source before any Next.js equivalent is built.

---

## Model: `Model/LeaveRequests.php`
`legacy/Model/LeaveRequests.php:7-45` — thin CakePHP model over `leaveentries`; only custom method is
`leaveTransactionPrc()` which builds and runs `CALL leave_transaction_prc(...)` to sync the day-level
`emp_leave_transactions` ledger after any status change. No other "LeaveEntries"-named model class was found in
`Model/` beyond `LeaveRequests.php` itself (some controllers reference an `LeaveEntries` model/table alias — e.g.
`EmployeeLeavesController.php:783` `$this->LeaveEntries->useDbConfig` — but its class file was not located
within this scope; INFERRED it is either an alias config or a thin model defined elsewhere pointing at the same
`leaveentries` table).

---

## Leave Approval Workflow (multi-step) — full state machine

**States** (`LEAVESTATUS` values observed across `grandLeave()` in both `LeaveRequestController.php:2301-2857`
and `EmployeeLeaveRequestController.php:2631-2893`, plus `saveLeaveEntry()`):

| Status | Meaning | Set by | Trigger |
|---|---|---|---|
| `Applied` | Employee submitted a new leave request | Employee (self-service) | `saveLeaveEntry()` insert |
| `Authorized` | Step-1 authorizer (`ISAutherizedby`) has signed off; awaiting final approval (or already final if authorizer == approver) | Authorizer | `grandLeave(actionType='Authorize')` |
| `Approved` | Step-2 approver (`APPROVEDBY`) has signed off — terminal success state | Approver | `grandLeave(actionType='Approve')` |
| `Rejected` | Authorizer or approver rejected the request — terminal | Authorizer/Approver | `grandLeave(actionType='Reject')` |
| `CancellationOfAuthorized` | Employee requested cancellation of a leave that was only `Authorized` | Employee | `saveLeaveEntry(myLeaveAction='Cancellation Applied')` |
| `CancellationOfApproved` | Employee requested cancellation of a leave that was fully `Approved` | Employee | `saveLeaveEntry(myLeaveAction='Cancellation Applied')` |
| `Cancellation Authorized` | Authorizer approved the cancellation request (intermediate) | Authorizer | `grandLeave(actionType='Authorize Cancellation')` |
| `Cancellation Approved` | Approver approved the cancellation request — terminal (leave is cancelled) | Approver | `grandLeave(actionType='Approve Cancellation')` |
| `Cancelled` | Cancellation fully processed / or employee directly cancelled a not-yet-authorized leave | Employee or `grandLeave(actionType='Cancelled')` | `saveLeaveEntry(myLeaveAction='Cancelled')` or `grandLeave` |
| `CancelledByAdmin` | Admin force-cancelled via bulk tool | Admin | `EmployeeLeavesController::deleteleave()` |
| `Can not Apply` / `Cannot Apply 0 days` | Client/server-side rejection sentinel, not a persisted workflow state | — | Validation only |

**Two-step (optionally three-step) approval chain**:
1. **Apply** — employee (or admin via bulk-upload controllers, which can skip straight to `Approved`) creates
   the row with a chosen `ISAutherizedby` (authorizer) and `APPROVEDBY` (approver); these may be the same person.
2. **Authorize** — the authorizer acts via `manageempleave.ctp` → `grandLeave(actionType='Authorize')`
   (`LeaveRequestController.php:2527-2619`). Sub-branches:
   - If authorizer == approver (`cur_emp_key == APPROVEDBY && cur_emp_key == ISAutherizedby`): both
     `ISAutherized` and `ISAPPROVED` are set to 1 in one step and status may jump straight to a de-facto approved
     state while still labeled `Authorized` (`:2532-2539`) — OR, if a 3rd-level "final sanction" person is
     configured for that leave type/policy group (`leval_of_approval = 3` in `leavepolicy`, resolved at
     `:2485`), an approval-link email is sent to that person instead of auto-finalizing
     (`sendfinalapprovalmail()`, `:2534-2559`).
   - Otherwise: sets `ISAutherized=1`, `Autherized_date=today`, emails the `APPROVEDBY` approver
     (`sendauthorizationmail()`, `:2567-2570`).
3. **Approve** — the approver acts via the same modal → `grandLeave(actionType='Approve')`
   (`:2620-2670`). Sets `ISAPPROVED=1`, `APPROVED_date=today`, `LEAVESTATUS='Approved'`, emails the *applicant*
   that their leave was approved (`sendauthorizationmail()` with `action='Approved'` →
   `"Your Leave Request has been approved successfully."`, message text built at
   `LeaveRequestController.php:3357-3358`).
   - If a 3rd-level sanction person exists, this step instead routes to them via link-email exactly as in step 2
     (`:2621-2651`), meaning the *effective* number of approval hops for a given leave type/policy group is
     configurable (2 or 3) via `LeavePolicy.leval_of_approval` (managed in `LeavePolicyController.php`).
4. **Reject** — either party can reject at their respective pending step; sets `LEAVESTATUS='Rejected'` and
   emails the applicant with `"Sorry. Your Leave Request has been rejected."` (`:3359-3360`). If rejecting a
   pending *cancellation* request instead, status reverts to the prior `Authorized`/`Approved` state with message
   `"Leave cancellation Rejected"` (`:2672-2700`) rather than becoming `Rejected`.
5. **Cancellation branch** — once `Authorized`/`Approved`, employee can request cancellation
   (`saveLeaveEntry(myLeaveAction='Cancellation Applied')`), which re-enters the same two-step
   authorize/approve gate but for the *cancellation itself* (`CancellationOfAuthorized`/`CancellationOfApproved`
   → `Cancellation Authorized`/`Cancellation Approved`), using the identical `manageempleave.ctp` UI with
   different button labels (`"Authorize Cancellation"`/`"Approve Cancellation"`,
   `View/LeaveRequest/manageempleave.ctp:248-256`).
6. **Balance sync** — every terminal `updateAll()` in `grandLeave()` is followed by
   `callLeaveTransactionProcedure()` → `LeaveRequests->leaveTransactionPrc()` → MySQL `leave_transaction_prc(...)`
   (`LeaveRequestController.php:2833`, `Model/LeaveRequests.php:17-44`), which is what actually updates the
   day-by-day `emp_leave_transactions` ledger consumed by attendance/payroll — the PHP layer itself does not
   compute the ledger, it delegates to a stored procedure not present in this codebase (out of scope for this
   report, but material for the rewrite's data-layer design since business logic partially lives in MySQL).

**Bypass paths (do not go through the 2/3-step chain above)**:
- `EmployeeLeavesController::leavesave()` / `EmployeeLeaveUploadController` equivalent — admin "add leave for
  employee" forms pre-set `ISAutherized`/`ISAPPROVED`-equivalent remark fields at save time, effectively
  auto-approving (`EmployeeLeavesController.php:196-200`).
- `EmployeeLeavesController::approveleave()` — bulk force-approve, no per-record authorize step
  (`EmployeeLeavesController.php:801-821`).
- `EmployeeLeavesController::deleteleave()` — bulk force-cancel to `CancelledByAdmin`
  (`EmployeeLeavesController.php:780-799`).

**Notification summary across the whole workflow**:
- In-app: `noti` badge (`AppController.php:100-127`) — visible only to whoever currently owns the *next* pending
  action (authorizer or approver), refreshed on every page load; no notification is generated for the applicant
  when their own request changes status other than email.
- Email: fired at Authorize, Approve/final-sanction-request, Reject, Cancellation-applied-response points, via
  hand-rolled PHPMailer calls with hardcoded SMTP credentials (`LeaveRequestController.php:3374-3383` and
  duplicated in `EmployeeLeaveRequestController.php` and `LeaveapiController.php:258-263`). No emails are sent
  for admin-bulk-upload/approve/cancel paths (`EmployeeLeavesController.php`, `EmployeeLeaveUploadController.php`,
  `EmpleaveuploadController.php`) or for the Leave Encashment workflow
  (`LeaveEncashmentRequestController.php`, through line 799) — INFERRED gaps to confirm with the business before
  parity-porting to Next.js.

---

## 5. Payroll, Salary & Tax

# Payroll, Salary & Tax — User-Facing Behavior Report

Scope: `legacy/Controller/{Payroll,PayrollProcess,SalaryProcessing,SalaryHeads,SalaryStructure,SalaryComponentUpload,SalaryIncrement,Tax,TaxHeads,Taxation,Taxsalarycomponents,EmpTax,EmployeeTax,FinancialYear,Arrear,Variable,FixedPaymentUpload,EmployeeIncrementReports,YearEnd}Controller.php` and their `View/` directories.

## Global access model (applies to every controller below unless noted)

- `AppController::beforeFilter()` (`legacy/Controller/AppController.php:39-46`) is the only hard gate: it reads session `user_group`; if it is not `1` (Admin/HR) or `2` (Employee/ESS) it redirects to `Site/login`. **None of the 19 controllers in this scope override `beforeFilter()` or implement `isAuthorized()`** (confirmed via grep — no matches for `isAuthorized|beforeFilter` in any of these files). So there is no controller-level menu/permission gate comparable to `user_access`/`menu_id` — that gating (if it exists at all for this module) must happen client-side, via what menu items the front-end renders for the logged-in user, not in these controllers.
- Instead, every controller does its own **inline, per-action** `user_group` branching, almost always to answer "is this an Employee(2) hitting a payroll page that should really be admin-only, and if so scope/restrict the data" — e.g. `PayrollController.php:66-82` narrows the employee list to the caller's own branch if `emp_proff.payro_priv = 1`. This is a data-scoping check, not an access-denial check — an Employee(2) user who reaches these URLs directly (e.g. via `Payroll/showprocesspayroll`) is **not blocked**, only served branch-scoped data. **INFERRED**: real-world access restriction for ESS users is enforced entirely by hiding the menu link client-side, not server-side — worth flagging as a security gap to replicate deliberately (allow-list) rather than copy as-is in Next.js.
- Company-specific special-casing is pervasive (hardcoded `company_code` string comparisons like `'GLET'`, `'ABSG'`, `'DEMO'`, `'KWMT'`, `'GAAR'`, `'SHYD'` appear throughout — see e.g. `PayrollController.php:242`, `TaxationController.php:68`, `EmployeeIncrementReportsController.php:201`). These are tenant-specific behavior forks baked into shared code — a major migration risk since business rules differ by customer and are not configuration-driven.

---

## 1. PayrollController.php — the live Payroll Run screen

**Purpose**: Runs and approves monthly payroll for all employees at a branch: computes salary from attendance, holds/removes/approves entries, shows salary slips, and (as of a recent addition) can bulk-generate & email employee portal passwords.

**Confirmed live**: `View/Dashboard/index.ctp:441-442,584` wires the "💰 Process Payroll" dashboard tile to `/Payroll/showprocesspayroll` — this is the controller actually reachable from the main nav.

**Who can access it**: Gated only by the global `user_group` session check (`AppController.php:44`). Within actions, `user_group == 2` (Employee/ESS) triggers extra branch-scoping via `emp_proff.payro_priv` (`PayrollController.php:66-82,116-140,227-253,277-291,1417-1421,1461-1465`) — an ESS user with `payro_priv=1` on `emp_proff` sees only their own branch's payroll; otherwise they see the same list as Admin. There is no explicit `user_group==1` requirement anywhere in the file, so the payroll run screens are reachable (via direct URL) by an Employee(2) session — access to the menu link itself is presumably hidden client-side (not verified from this scope of files).

**Step-by-step user flow (this is the live 2-stage — not multi-stage — payroll run)**:

1. **Open "Process Payroll"** → `showprocesspayroll()` (`PayrollController.php:57-112`) loads branch dropdown, employee list, and the customer's `plan` (subscription tier) from `comp_contact_info`. View: `View/Payroll/showprocesspayroll.ctp`.
2. **Filter by Branch + Month** → user picks Branch/Month (`showprocesspayroll.ctp:56-100`) and clicks **List** → `filterPayroll()` JS (`showprocesspayroll.ctp:166-201`) calls `POST payroll/FilterList` → `FilterList()` (`PayrollController.php:198-217`) deletes stray zero-emp attendance rows then calls stored proc `payroll_master_insert('$branch','$month','$user_id',@error)` (`PayrollController.php:213`) — this populates `payroll_master` rows from `attendance_register` for the month/branch. Flash: `"Payroll listed successfully"` / `"Payroll listing failed!"` (`showprocesspayroll.ctp:184,193`).
3. Two tabs load via `pwstabs` jQuery plugin, each an AJAX-loaded partial:
   - **Tab 1 "Not Processed"** → `GET payroll/showprocesspayrolltab/0` → renders `payrolltable` (easyui `datagrid`) sourced from `payroll/listpayroll` (`PayrollController.php:298-434`), showing employees with `action IS NULL` (not yet processed), with columns Calendar/Working/Present/Leave/LOP days and Net Salary. Rows with `net_salary <= 0` are highlighted red/white; resigned employees (via `termination` join) shown in red text (`showprocesspayrolltab.ctp:142-149`).
   - **Tab 2 "Processed"** → `GET payroll/showprocesspayrolltab/1` → renders `processedpayrolltable` sourced from `payroll/listprocessedpayroll` — adds "View Slip", Last-Month-Salary, Difference columns; rows colored by `action` (`Hold` = red text, `Approved` = green) (`showprocesspayrolltab.ctp:174-186`).
4. **Optional: "Include Tax" toggle** on Tab 1 (only shown if `plan != 'basic'`, `showprocesspayrolltab.ctp:18-27`) — sets `tax_include='Y'/'N'` per selected row before Process.
5. **Select rows and click "Process"** → `processPayroll()` JS collects checked `emp_pkey`/`payroll_master_pkey` → `POST payroll/processpayroll` → `processpayroll()` (`PayrollController.php:790-1204`, the heart of the module):
   - For each selected employee, updates `tax_include` flag on `payroll_master` (`838-849`).
   - Calls a stored procedure to actually calculate salary: `calculateSalaryMainPrc()` for normal companies, or `salaryProcessPrc()` + `taxSalaryProcessPrc()` for a hardcoded list of ~17 "special companies" (`PayrollController.php:818,871-874,1013`) — this is a per-tenant fork of the calculation engine, not configuration.
   - Re-evaluates any salary-component "remarks" formulas (arithmetic strings referencing other salary-head amounts, `monthsal`, etc.) via `eval()` on a whitelisted numeric-only regex (`PayrollController.php:941-943,972,985,1151,1155-1161`) — **note**: this uses PHP `eval()` guarded only by a regex allow-list (`/^[0-9\+\-\*\/\(\)\. ]+$/`), a code-smell to not carry into Next.js (replace with a real expression evaluator).
   - Returns `{"success": 0|1}` JSON; on success moves the row from Tab 1 to Tab 2 and both grids reload; flash `"Payroll processed successfully"` / `"Payroll process failed!"` (`showprocesspayroll.ctp:228,242`).
6. **On the Processed tab**, admin can:
   - **Remove** a selected entry → `removePayrollEntry()` JS blocks client-side if any selected row has `action=='Approved'` (`showprocesspayroll.ctp:294-311`, flash `"You cannot remove an approved entry!"`) → else `POST payroll/removePayrollEntry` (`PayrollController.php:1229-1299`) sets `payroll_master.action = NULL`, soft-closes the employee's `emp_salary_slip` rows (`end_date_effective`), and for non-special companies resets any `emp_variables_upload` fixed-type rows back to `status=0` so they re-apply next run.
   - **View Slip** per row → modal `showSmallModalForm(payroll/showsalaryslip/{payroll_master_pkey})` → `showsalaryslip()` (`PayrollController.php:1301-1400`) — builds a head-wise breakdown of `emp_salary_slip` joined to `salary_heads`/`salary_head_items`, split into direct vs `item_part='Indirect'` components. View: `View/Payroll/showsalaryslip.ctp`.
   - (Commented out in current UI: "Hold" button — `holdProcessPayroll()` backend action still exists at `PayrollController.php:1207-1227` but its UI trigger is HTML-commented at `showprocesspayrolltab.ctp:33-40`, so it's currently dead from the UI though reachable via direct AJAX call.)
7. **Separate screen: "Approve Payroll"** → `showapprovepayroll()` (`PayrollController.php:114-196`) — same Branch/Month filter pattern, two tabs:
   - Tab "Not Approved" → `payroll/listapprovepayroll` → **Approve** button → `approvePayroll` JS → `POST payroll/approvepayroll()` (`PayrollController.php:1757-1794`) — for each selected `payroll_master_pkey`, calls stored proc `payroll_master_approve('$branch_code','$month','$emp_fkey','$uid', @perror_message)` then sets `Payrollmaster.action='Approved'`.
   - Tab "Approved" → `payroll/listapprovedpayroll` — from here, for specific `company_code`s (`DEMO`,`SHYD`,`KWMT`,`GLET`,`GAAR`), two extra buttons appear:
     - **"Email Slip"** → `sendSlipEmailtoPersons()` JS (`showapprovepayrolltab.ctp:252-327`) — client-side validates every checked row has an email (blocks / warns per company, `260-289`) then `GET SalaryReports/sendSliptoMail[Synthite|SBL]` (tenant-specific endpoint selection, `showapprovepayrolltab.ctp:298-304`) — **out of this scope's controller list** (lives in `SalaryReportsController`, not analyzed here) but is the actual payslip email trigger.
     - **"Password Generation"** (UI button HTML-commented out, `showapprovepayrolltab.ctp:36-43`, but `generatePasswords()` JS function and backend both exist) → `GET Payroll/generatePasswords` → `generatePasswords()` (`PayrollController.php:1796-1891`) — for every active employee with an email, generates a random 5-8 char password, expires prior `passwords` rows, inserts a new one, and **emails it via PHPMailer/SMTP directly** (`PayrollController.php:1853-1874`) with subject "Your Password" and the plaintext password in the email body. **Flags hardcoded SMTP credentials in source** (`PayrollController.php:1857-1861`) — must not be carried into the new codebase; also plaintext-password-by-email is a security anti-pattern worth calling out to product/security before reimplementing.

**Forms**: No traditional `<form>` POST/redirect cycle anywhere in this controller — every screen is AJAX/JSON (`autoRender = FALSE` on nearly all actions) with `$.notify()` toast messages, not CakePHP flash/`Session::setFlash`. There is no client-side validation library in use beyond native `<select>` constraints (Branch/Month dropdowns); server-side there is essentially no input validation — request fields are read with `isset()` fallbacks to `''`/`0` and passed straight into stored-procedure calls or raw SQL string interpolation (e.g. `PayrollController.php:838,848,1258,1280` — `$branches`, `$month`, `$arr_payroll_pkeys_list` concatenated directly into SQL — **SQL-injection-shaped code**, flag for the new stack to use parameterized queries).

**Notifications**: 
- `Payroll/generatePasswords` — password reset email via PHPMailer/SMTP (`PayrollController.php:1852-1874`).
- `SalaryReports/sendSliptoMail*` (payslip email) — triggered from this controller's view but implemented in `SalaryReportsController` (outside this scope).

**AJAX/JS endpoints hit from Payroll views**: `payroll/FilterList`, `payroll/showprocesspayrolltab/{0|1}`, `payroll/listpayroll`, `payroll/processpayroll`, `payroll/holdProcessPayroll` (dead UI), `payroll/removePayrollEntry`, `payroll/listprocessedpayroll`, `payroll/showsalaryslip/{pkey}`, `payroll/showapprovepayrolltab/{0|1}`, `payroll/listapprovepayroll`, `payroll/approvepayroll`, `payroll/listapprovedpayroll`, `Payroll/generatePasswords`, `SalaryProcessing/index` and `EmployeeMenu/addon` (the "Back" button branches by `user_group`, `showprocesspayroll.ctp:360-381`), `SalaryReports/sendSliptoMail*`.

---

## 2. PayrollProcessController.php — orphaned/legacy multi-stage workflow (NOT reachable from live nav)

**Purpose (as coded)**: A far more elaborate payroll pipeline than `PayrollController`: **Process → Provisional → Pre-Audit → Finalization → Approval (Salary) → Payment → Payment-Approval**, each with its own list/tab/action endpoints (`PayrollProcessController.php:54-2765`, 30 public actions).

**Live-reachability finding**: A repo-wide search of every `.ctp` view file for the string `PayrollProcess/` found **matches only inside `View/PayrollProcess/` itself** — no other view (dashboard, menu, layout) links into this controller. The dashboard's Process-Payroll tile points at `/Payroll/showprocesspayroll` (the controller above), not `PayrollProcess`. **INFERRED**: this controller is dead code from a superseded design — the product evolved from a 6-stage pipeline down to the simpler 2-stage (Process → Approve) flow in `PayrollController`, and the old stage-based views/controller were left in place. Do not port this workflow as the "real" payroll run; treat `PayrollController` as ground truth. However, the stage vocabulary here (Provisional/Pre-Audit/Finalization/Payment) may reflect a requirement the business still wants — worth confirming with stakeholders rather than assuming it's simply obsolete.

**Who can access it**: Same global gate only; no additional checks (`grep` for `isAuthorized|beforeFilter` empty).

**Stage inventory found in code** (each with its own show/list/process/approve quartet, mirroring the Process→Approve pattern of `PayrollController` but multiplied across stages):
- Process: `showprocesspayroll` (54), `processpayroll` (321), `holdProcessPayroll` (449), `removePayrollEntry` (471)
- Approval (of the *process* stage): `showapprovepayroll` (64), `listapprovepayroll` (649), `approvepayroll` (811)
- Provisional: `listprovisionalpayroll` (851), `showprovisionalpayroll` (986)
- Remarks/rejection sidebars: `addremarks*` (1021-1063), `addreversalremarks*` (1028-1046), `addrejectremarks` (1034)
- Pre-Audit: `showpreauditpayroll` (1067), `preauditpayroll` (1107), `listpreauditpayroll`/`listpreauditedpayroll` (1185/1301)
- Finalization: `showfinalizationpayroll` (1435), `finalizationpayroll` (1720), `listfinalizationpayroll`/`listfinalizedpayroll` (1471/1588)
- Payment: `paymentprocessedpayrolllist` (1792), `paymentpayroll`/`paymentpayrolltab` (1871/1880), `processpayment` (2222), `listpaymentapprovalpayroll` (1934), `PaymentapprovePayroll` (2063), `listpaymentapprovedpayroll` (2128)
- Salary Approval (separate from Payment Approval): `approvalpayroll`/`approvalpayrolltab` (2298/2308), `processapprovalpayroll` (2358), `listsalaryapprovalpayroll` (2431), `SalaryapprovePayroll` (2561), `listsalaryapprovedpayroll` (2633)
- `rejectPayrollEntry($payroll_master_pkeys, $status)` (2724) — generic reject-with-remarks endpoint used across stages.

**If migrating**: because this is unreachable dead code, **do not** build the Next.js payroll module around this 6-stage design unless the business explicitly confirms it wants that workflow revived. Flag to stakeholders as a design question, not a bug to fix.

---

## 3. SalaryProcessingController.php — Payroll feature hub / plan-gated launcher

**Purpose**: Not a payroll-processing screen itself — it's a **subscription-plan-gated menu/launcher page** ("Payroll" hub) that lists payroll-related features (from a `Features`/`PlanFeature` table pair) and shows each as enabled/disabled depending on the tenant's `plan_id`, with "Upgrade to Next Plan" CTA for disabled ones.

**Who can access it**: Global gate only. `index()` (`SalaryProcessingController.php:10-16`) sets branch list; no `user_group` branching.

**Flow**:
1. `index()` renders `View/SalaryProcessing/index.ctp` with three empty grid panels (`index.ctp:197-201`).
2. On load, JS calls `SalaryProcessing/getPayrollFeatures` (`index.ctp:212`) → `getPayrollFeatures()` (`SalaryProcessingController.php:17-75`) — looks up the tenant's `plan_id` from `controldb.CentralUserCredentials` by `company_code`, fetches all `Features` rows where `feature_key='payroll'` and `is_common=0`, cross-references `PlanFeature` for which are `is_enabled=1` for that plan, returns JSON list with `is_enabled` flag per feature.
3. Left panel renders one pill button per feature (`payroll-main-grid`), disabled ones grey/clickable-but-locked (`index.ctp:216-238`). Last-clicked feature is remembered via `localStorage` (`index.ctp:209-241`) and auto-reselected on next visit.
4. Clicking an **enabled** feature shows its description + "Open {title}" button in the center panel and any linked help articles in the right panel (`index.ctp:253-296`); clicking **"Open"** does `$("#container").load(data-route)` — i.e. **client-side SPA-style navigation** to that feature's real controller/action (e.g. presumably `Payroll/showprocesspayroll`), loaded via AJAX into `#container` (`index.ctp:321-333`).
5. Clicking a **disabled** feature shows "Not in Plan" badge + "Upgrade to Next Plan" button, which routes to `User/profile` and auto-scrolls/activates a billing tab (`index.ctp:298-313,336-351`).

**Forms/validation**: None — purely read/display + navigation.

**Notifications**: None.

**AJAX endpoints**: `SalaryProcessing/getPayrollFeatures`, then whatever `feature_path` values are stored in the `Features` table (data-driven, not enumerable from code).

**Migration note**: this is effectively a **feature-flag/entitlement gate UI** — in Next.js this maps naturally to a plan/entitlement check + conditional route rendering, not to any payroll business logic itself.

---

## 4. SalaryHeadsController.php (+ `SalaryHeadsControllernimishabackup.php` duplicate)

**Purpose**: Admin CRUD for **Salary Heads** (top-level pay components, e.g. "Earnings", "Deductions") and their child **Salary Head Items** (e.g. "Basic", "HRA" under "Earnings") — the taxonomy used to build salary structures.

**Duplicate file**: `SalaryHeadsControllernimishabackup.php` (376 lines) is a stale backup copy left in the `Controller/` directory (not a CakePHP-routable class name collision risk only because its filename doesn't match a `xxxController` convention CakePHP would auto-load as a route, but it does still declare a PHP class and will be loaded if referenced) — **INFERRED dead/backup file, exclude from migration**, just diff it against the live file if anyone suspects a feature was lost.

**Who can access it**: Global gate only; no `user_group` branching found in the main CRUD actions (`form`, `addsalaryheads`, `form_items`, `addsalaryheaditems`, `DeleteHead`) — `user_group` only appears in `index()` (`SalaryHeadsController.php:106-109`) purely to `$this->set()` it for the view, not to branch logic. So this looks like an **Admin-intended screen with no server-side Admin-only enforcement** — again relying on the menu being hidden from ESS users.

**Flow**:
1. `index()` (`SalaryHeadsController.php:57-...`) lists existing heads/items.
2. **Add/Edit Salary Head** → modal loads `form()` (`256-276`, `layout=null`) pre-filled if `?id=` present; **Save** posts to `addsalaryheads()` (`278-313`) — no CakePHP `$this->request->data` validation rules invoked (no `Model->validates()`), just direct field mapping (`SALARYHEAD_ID`→`head_pkey`, `SALARYHEAD_NAME`→`head_desc`, etc.) and `save()`. Duplicate-name guard: `checksalaryheadexists()` is called only *after* the early `return json_encode($resp)` on the happy path (`SalaryHeadsController.php:294,307,310`), meaning **the duplicate-check code is unreachable dead code** — it can never execute because both prior branches already `return` — flag as a real latent bug (duplicate salary heads can silently be created) worth deciding whether to fix or intentionally drop in the rewrite.
3. **Add/Edit Salary Head Item** → `form_items($id,$kid)` (315-342) / **Save** → `addsalaryheaditems()` (344-386) — same free-form field mapping.
4. **Delete Head** (soft delete) → `DeleteHead()` (388-401) sets `status=0`.
5. Helper AJAX validators: `checksalaryheadexists`, `checksalaryheaditemexists`, `checkshortnameexists` (403-461) — used for inline duplicate-name checks from the modal forms (not wired into the main save path per finding above).

**Forms**: Fields — Salary Head: Name, Operator (Addition/Deduction), Occurrence. Salary Head Item: Item name, Type, Value, Occurrence, Start-From, Comments, "Show on payslip" (Y/N), Item Part (Direct/Indirect). No visible client-side validation beyond required-field HTML attributes (not confirmed from controller; view not read in full).

**Notifications**: None.

---

## 5. SalaryStructureController.php

**Purpose**: Admin builds/edits **Salary Structures** (named templates — e.g. "Manager Grade A") composed of Salary Head Items with formulas/percentages, and assigns employees to a structure (salary configuration).

**Who can access it**: Global gate only; no `user_group` branching anywhere in this file's grep results — a fully un-gated admin screen server-side.

**Flow** (from action list, `SalaryStructureController.php:58-741`):
1. `index()` (58-74) lists active salary head items + active structures, plus tenant `plan`.
2. `liststructures()` (76-95) — datagrid JSON feed.
3. `addEmpToSalConfig` / `removeEmpFromSalConfig` (97-140) — attach/detach employees to a structure config.
4. `listemployeesinsalary` / `listemployeesforsalary` (141-220) — two employee-picker grids (already-configured vs available).
5. `savesalarystructuresetup()` (221-334) — the core "build a structure" save (component rows, formulas, limits).
6. `loadSalaryStructureDetails($structure_id)` (335-392), `getSalaryHeadItems()` (393-402) — supporting AJAX lookups for the structure editor.
7. `view($structure_id)` (403-503) / `form($structure_id)` (504-639) — read-only view vs edit form for a structure.
8. `calculateFixedLimit()` (640-662) — server-side helper for capped/limit-based components (e.g. PF wage ceiling).
9. `delete()` (680-720), `checkstructureexists()` (721-741).

**Forms**: Structure definition rows (head item, formula/percentage, occurrence, limit type/value) — no client validation confirmed from controller code; server-side again does direct field mapping with no `Model::validates()` calls seen.

**Notifications**: None. **AJAX-heavy**: nearly every action is `autoRender=FALSE` JSON.

---

## 6. SalaryComponentUploadController.php (2258 lines) & SalaryIncrementController.php (5814 lines)

These are the two largest files in scope and share the same problem domain: **bulk employee CTC/salary-component upload (Excel-based) and salary increment (revision) processing**. Given the size, this summary is grep-derived (function inventory) rather than a full line-by-line trace — flag for a follow-up deep-dive session if the migration team needs exact formula/validation semantics.

### SalaryComponentUploadController.php
**Purpose**: Excel upload workflow to bulk-set/change employees' CTC and per-component salary breakdown, plus ad-hoc "component allocation" (assign extra pay components to specific employees) and an increment listing.

**Who can access**: Global gate + heavy inline `user_group==2` scoping (branch-restriction pattern identical to Payroll, seen at lines 71,356,496,1184,1239,1262,1280) — same tenant-specific `GLET/ABSG/GAAR/HRBL` branch carve-outs as elsewhere.

**Key actions** (`SalaryComponentUploadController.php`):
- `component_upload()` (342) → landing page for the upload flow.
- `downloadempctcformat()` (674) → generates a downloadable Excel/CSV **template** pre-filled with current employee CTC data for the admin to edit offline.
- `uploadandsaveempctc($ctcuploadtype)` (843) → the actual bulk-import action, parses the re-uploaded file and writes new salary structures per employee — this is the highest-risk action to port faithfully (file parsing + per-row calculation + partial-failure handling need a dedicated trace).
- `saveuploads()` / `saveuploads_od()` (142/293) → related upload-save variants (likely different upload "modes").
- `component()` / `componentAllocate($sal_fkey)` / `saveAllocate()` / `removeAllocate()` (1227,1421,1466,1536) → ad-hoc per-employee component add/remove UI (outside the bulk-upload flow).
- `getEmployeesByTypeValue()` / `getTypeValues()` (1561/1611) → cascading dropdown AJAX helpers.
- `saveComponentAllocate()` / `saveComponentUploads()` / `saveComponentAllocateKWMT()` (1661/1820/2008) — **note the `KWMT`-suffixed variant**: another tenant-specific fork of the save logic, not a shared code path.
- `listincrements()` (1303) — read-only increment history grid.

### SalaryIncrementController.php
**Purpose**: The salary revision/increment engine — lets admin apply a hike (%, flat amount, or structure change) to one/many employees effective a future date, preview the new structure, and commit it; also handles its own parallel CTC-upload flow (largely duplicated from SalaryComponentUploadController — same `uploadandsaveempctc`/`saveComponentAllocate*` function names appear here too, e.g. lines 822,2984,3143,3337) plus increment-specific reporting (`itemIncerementReport`, `incerementReport`, lines 4684/4839) and PDF/Excel export (`download($salary_hike_pkey)`, 5420).

**Who can access**: Same global gate + `user_group==2` branch-scoping pattern (lines 101,109,174,266,345,5220,5225,5236).

**Core flow (inferred from action names/order)**:
1. `employeelist()` (3586) — pick employees to increment.
2. `salaryIncrementForm()` (1592) — enter new CTC/hike details, effective date.
3. `calcSalaryStructure()` / `getTempSalaryStructure()` (2067/5076) — server recalculates the *proposed* structure breakdown for preview before commit (temp/staging calculation).
4. `saveIncrement()` / `saveItemIncrement()` (1659/1864) — commits the increment.
5. `process()` / `processItem()` (3712/4225) — **INFERRED**: batch-apply step that actually writes the new `emp_salary_structure`/`emp_salary_slip` effective-dated rows (mirrors the `end_date_effective` versioning pattern seen in Payroll).
6. `alterSalaryStructure()` (2118), `onEffectiveDateChange()` (2496), `onIncrementChange()`/`onIncrementChangeNew()` (3937/4083) — live-recalculation AJAX handlers as the admin edits the form.
7. `deleteIncrement()` (5182) — reverse/cancel a pending increment.
8. `employeelistPending()` (5591) — queue of increments not yet processed.
9. `getSummaryValue()` (5768) — dashboard-style summary tile.

**Migration flag**: The near-duplicate function set between `SalaryComponentUploadController` and `SalaryIncrementController` (both have `uploadandsaveempctc`, `saveComponentAllocate`, `saveComponentAllocateKWMT`, `getEmployeesByTypeValue`, `getTypeValues`, `saveComponentUploads`) strongly suggests **copy-pasted logic that diverged over time** rather than shared code — worth a side-by-side diff before deciding whether Next.js gets one unified "salary structure editor" or must keep two parallel implementations to match subtly different legacy behavior.

**Notifications**: no `Email->send`/`PHPMailer` matches in either file — no emails triggered directly by these controllers.

---

## 7. Tax-related controllers

### TaxController.php (1175 lines) — Employee tax declaration & Form-16
**Purpose**: The employee-facing (and admin-facing) **income-tax declaration workflow** — old-vs-new tax regime choice, investment/deduction declarations, document upload for proofs, and Form-16 generation/download.

**Who can access**: Global gate; heavy `user_group` branching throughout (`Tabs()` at 89-117 renders different tab sets for `user_group==1` (91-111, presumably an employee-picker for HR) vs `user_group==2` (112-116, presumably "my tax" only) — i.e. **this screen is shared between ESS and Admin** with the UI adapting by role, unlike the Payroll-run screens which are Admin-oriented with ESS-scoping bolted on.
- `formSixteenDownload()` explicitly checks `user_group` at line 1027 (download permission likely scoped to self for group 2).
- `downloadtaxdocument_modal()` branches `if ($user_group==1) {...} elseif ($user_group==2) {...}` (846,861) — different document-set logic per role.

**Flow** (`setup($emp_pkey)`, `TaxController.php:173-...`):
1. Looks up the employee's **open financial year** for their branch (`FinancialYear` model, condition `Year_status='OPEN' AND is_current_finyear='Y' AND vattr1=1`, line 184). If none found and the caller is an Employee(2), it renders an alternate `no_fin_year` view instead of erroring (`188-192`) — a deliberate empty-state.
2. Loads the employee's chosen **tax regime** (`emp_tax_regime.option_type`, Old vs New, default `'O'`) (212-213).
3. Pulls current **tax-exempt salary components** already declared (`EmpTaxSalTrans` joined to `tax_salary_components`/`salary_head_items`) and sums them (`Total`, 215-239).
4. Pulls **tax deductions declared** (`EmployeeTaxTransactions` joined to `tax_heads`/`tax_heads_details`) and computes `tottax`, capping each declaration at its head's max limit (`TX.attr1`) (240-266) — i.e. server enforces per-section deduction caps (e.g. 80C ₹1.5L) at read time.
5. Pulls **other-income declarations** total (267-271).
6. Other actions in this controller (`Calculate`, `Calculate_new`, `setupshow`/`setupshow_new`, `Proccess`, `Choosetax`, `setupupload_new`, `addnewtaxdocument_modal`, `setupdownload_new`, `downloadtaxdocument_modal`, `formSixteen`, `formSixteenDownload`, `getEmployeesByBranch`) — the "_new" suffixed pairs (`Calculate_new`, `setupshow_new`, `setupupload_new`, `setupdownload_new`) strongly suggest an **in-place redesign that kept the old version alongside** — **INFERRED**: verify which of each `X`/`X_new` pair is actually linked from current views before porting; likely only the `_new` ones are live (same "orphaned old version" pattern as `PayrollProcessController` vs `PayrollController`).

**Forms**: Tax declaration line items (per tax head, a claimed value vs a system-known limit), document uploads per declaration (proof attachments), regime choice (Old/New radio). No `Model::validates()` calls found; deduction capping happens only at *read/display* time (step 4 above) — actual save action (`saveemployeetaxheaddetails` — see `Taxsalarycomponents` below) needs checking for whether the cap is enforced on write too.

**Notifications**: none directly in `TaxController.php` (no Email/PHPMailer matches).

### TaxHeadsController.php — Admin CRUD for Tax Heads (mirrors SalaryHeadsController)
Same shape as `SalaryHeadsController`: `index/form/addtaxtypes/Deletetaxtype/form_items/addtaxheads/Deletetaxhead` (`TaxHeadsController.php:58-372`) plus `gettaxtypename`/`Gettaxnamelist`/`add_details` lookups. No `user_group` branching found — un-gated admin CRUD, same pattern/risk as SalaryHeadsController's dead duplicate-check bug is worth checking here too (not verified in this pass).

### TaxationController.php — parallel/older tax module?
Has its own `Tabs()`, `Calculate()`, `setup()`, `Employeesetup($emp_pkey)`, `setupshow($emp_pkey)`, `Proccess($emp_pkey)` (`TaxationController.php:57-489`) — function names overlap heavily with `TaxController.php` (`Tabs`, `Calculate`, `setup`, `setupshow`, `Proccess`). **INFERRED**: `TaxationController` vs `TaxController` is very likely another live-vs-orphaned pair (same pattern as Payroll/PayrollProcess) — check view-file cross-references (`grep -r "Taxation/" View/`) before treating both as active; not done in this pass due to scope, flag for follow-up.

### Taxsalarycomponentscontroller.php (`TaxsalarycomponentsController.php`) — AJAX API backing TaxController's forms
Small (92 lines), pure JSON endpoints: `getTaxTypes`, `getTaxHeads`, `getTaxHeadFields`, `getTaxHeadDetails`, `deletedoc`, `saveemployeetaxheads($empPkey)`, `uploadFile`, `savetaxdetail`, `saveemployeetaxheaddetails`, `loadEmpTaxationDetails`, `loadEmpTaxHeadDetails`, `loadEmpTaxHeadDocuments`, `getPFTaxValue($emp_fkey)`.
- **Important write-time permission check found**: `saveemployeetaxheads()` (158-241) and `uploadFile()` (242-...) both check `if ($user_group == 1) { ... }` (193,218,230,343) — **this looks like a genuine server-side lock**: once a declaration is finalized, only Admin (`user_group==1`) can further edit/re-lock it — comment at line 193 area literally says "added by megha for locking action only for admin" (per earlier grep). This is one of the few real server-side role checks found in the entire scope — call this out explicitly to the migration team as a rule that must be preserved (not just UI-hidden).

### EmpTaxController.php — trivial/likely dead
Only `index()` (`EmpTaxController.php:52-58`), fetches `TaxHeads` and `debug()`s them to the page (`debug($tax_heads)` at line 56 — a raw CakePHP debug dump left in production code, meaning this screen literally prints a PHP var_dump-style block to the browser). **INFERRED dead/debug-only controller** — do not port as-is; if any UI still links here it needs a proper replacement view.

### EmployeeTaxController.php — real tax-heads/details API (separate from EmpTaxController)
Actions: `getTaxTypes`,`getTaxHeads`,`getTaxHeadFields`,`getTaxHeadDetails`,`deletedoc`,`saveemployeetaxheads`,`uploadFile`,`savetaxdetail`,`saveemployeetaxheaddetails`,`loadEmpTaxationDetails`,`loadEmpTaxHeadDetails`,`loadEmpTaxHeadDocuments`,`getPFTaxValue` — **this is functionally identical in name/shape to `TaxsalarycomponentsController.php`** (same 12 action names). **INFERRED**: one of these two is the live backend for the tax-declaration forms and the other is a duplicate/rename left behind; needs a view-reference grep to disambiguate (not completed in this pass — flag for follow-up before committing to port both).

---

## 8. FinancialYearController.php

**Purpose**: Admin CRUD for defining **Financial Year / Leave Year periods per branch** (`fin_year` table) — start/end month, status (OPEN/CLOSED), "is current financial year" flag, and a `vattr1` type flag distinguishing "Leave" year vs "Financial" year definitions (`FinancialYearController.php:174-179` shows the label mapping: `vattr1==0` → "Leave", else → "Financial"). This period record is what `TaxController::setup()` and `YearEndController` both key off of.

**Who can access**: Global gate only, no `user_group` branching — un-gated admin CRUD.

**Flow**:
1. `index()` (54-59) — landing/list shell.
2. **Add/Edit** → `form()` (63-90, `layout=null`) — modal form, pre-fills from `$_REQUEST['id']` if editing, formats dates `d/m/Y`.
3. **Save** → `save()` (93-133) — parses `d/m/Y` dates via `DateTime::createFromFormat`, derives `fin_year` from the start date's year, saves `branch_code`, `vattr1` (type), `Year_status`, `is_current_finyear`. Returns `{"success":true,"msg":"Financial saved successfully"}` — **note the message always says "Financial saved successfully" even for a "Leave" (`vattr1==0`) record** — a minor copy bug worth fixing rather than replicating.
4. **List** → `listfinyears()` (134-198) — datagrid feed joined to `branches` for branch name, formats `vattr1` to a human label and dates for display.
5. **Delete** (soft) → `deleteFinYear()` (200-218) — sets `status=0` for the given `Fin_year_seq` ids.

**Forms**: Branch (dropdown), Type (Leave/Financial radio via `vattr1`), Start Month, End Month (both `d/m/Y` text/date pickers), Year Status (Open/Closed), "Is current financial year" checkbox. No `Model::validates()` calls seen — pure server-side date parsing with `DateTime::createFromFormat`, which silently returns `false` on bad input rather than raising a validation error (**latent bug**: if the date field is malformed, `$start->format(...)` at line 115/118 will fatal-error calling a method on `false`, not show a friendly message) — flag as something the Next.js form should actually validate client- and server-side.

**Notifications**: None.

---

## 9. ArrearController.php — parallel mini payroll-run for salary arrears

**Purpose**: A structurally identical (smaller) clone of the `PayrollController` process→approve pipeline, but for **arrears** (retroactive pay adjustments) instead of regular monthly payroll — its own `payroll_arrear_master` table, own stored procedures.

**Who can access**: Global gate; same `user_group==2` branch-scoping via `GLET/ABSG` company-code special-case (`ArrearController.php:45`).

**Flow** (mirrors `PayrollController` almost 1:1 by action name):
1. `showprocesspayroll()` (54) → Branch/Month filter UI (`View/Arrear/showprocesspayroll.ctp`).
2. `FilterList()` (172) → populates arrear entries for processing (equivalent of `payroll_master_insert`, presumably an arrear-specific stored proc — not confirmed by line-read in this pass).
3. `showprocesspayrolltab($processed)` (208) → two-tab Not-Processed/Processed grids fed by `listpayroll`/`listprocessedpayroll` (259/579), with legacy `listpayrollold`/`listprocessedpayrollold` (381/501) also present — **INFERRED**: `*old` suffixed actions are superseded, not the live path (same dead-code pattern seen elsewhere).
4. `processpayroll()` (702) → calculates the arrear amount per selected employee (equivalent of `PayrollController::processpayroll`).
5. `holdProcessPayroll()` (897), `removePayrollEntry()` (919) → same hold/remove semantics as regular payroll.
6. `showarrearsalaryslip($payroll_arrear_master_pkey)` (955) → arrear-specific slip view (parallel to `showsalaryslip`).
7. **Approve stage**: `showapprovepayrolltab($approved)` (1053), `listapprovepayroll`/`listapprovedpayroll` (1070/1170), `approvePayroll()` (1236-1314) — calls stored proc `payroll_arrear_approve('$branch_code','$month','$payout_month','$emp_fkey','$uid', @perror_message)` (`ArrearController.php:1280`), but **only after a guard**: it first checks `attendance_register` has a non-deleted row for the employee/`payout_month` (`1272-1278`); if not, the entry is **skipped and reported separately** — response payload explicitly separates `processed` vs `skipped` pkeys (`1306-1310`) so the UI can tell the admin which arrears couldn't be approved and why (missing attendance for the payout month). This is a real business rule worth preserving exactly.

**Notifications**: none (no Email/PHPMailer matches in this file).

---

## 10. VariableController.php — variable-pay (bonus/incentive) upload

**Purpose**: Lets Admin bulk-upload **variable pay components** (e.g. monthly incentive/bonus amounts that change per employee per month, as opposed to fixed salary structure) via a downloadable-then-reupload Excel template, scoped per salary-head-item.

**Who can access**: Global gate + `user_group==2` branch scoping (`VariableController.php:34-45,425,460`); several actions additionally check `if ($user_group==1)` to gate admin-only branches within the download-format builder (`93,111,122,148,223`) — i.e. the template-download logic itself differs for Admin vs ESS callers (likely ESS never legitimately reaches this, but the code defensively branches).

**Flow**:
1. `index()` (24-...) — landing page, branch list.
2. `downloadvariableuploadform($salaryhead, $branch, $emp_pkey)` (87-...) — generates the Excel template pre-filled with current values for the chosen salary-head-item/branch/employee scope.
3. `uploadandsaveempvar($salary_head_item, $month)` (241-419) — parses the re-uploaded file and writes `emp_variables_upload` rows for that month (this is exactly the table `PayrollController::removePayrollEntry` resets `status=0` on when a processed entry is removed — confirms Variable uploads feed directly into the payroll calculation as "fixed"-type inputs for that month, `PayrollController.php:1285-1288`).
4. `employeelistvariable()` (420-...) — employee picker grid, also branch-scoped for `GLET/ABSG` ESS callers (460).
5. `form($id)` (523-...) / `VariableSave()` (579-...) — single-employee edit form as an alternative to bulk upload.
6. `deleteEmployees()` (564-...) — remove variable entries.

**Notifications**: None.

---

## 11. FixedPaymentUploadController.php — fixed one-time/recurring payment upload

**Purpose**: Sibling of `VariableController` but for **fixed payments** (e.g. a flat allowance starting from a given month, for a given number of occurrences) — same download-template → bulk-reupload pattern.

**Who can access**: Global gate only (no `user_group` matches found in this file — the one bulk-upload controller in scope with **no ESS branch-scoping at all**, consistent with it being a pure-Admin, back-office-only task).

**Flow**:
1. `index()` (26) — landing.
2. `branchemployee($branch)` (60) — cascading employee dropdown by branch.
3. `downloadfixedpaymentuploadform($salaryhead, $branch, $occurance, $emp_fkey, $start_month)` (76-350) — template generator, parameterized by **occurrence count and start month** (distinguishing this from Variable's simpler month-only scope — fixed payments are explicitly "starts on X, repeats N times").
4. `uploadandsavefixedpayment($salary_head_item, $month, $occurance)` (351-617) — bulk-import/save.
5. `employeelistfixedpayment()` (618-747) — picker grid.
6. `form($id)` (748-787) / `checkdoj($start_month, $emp_fkey)` (788-811) — single-entry form, with a **date-of-joining guard**: `checkdoj` presumably validates the chosen start month isn't before the employee's joining date (name strongly implies this; not read in full).
7. `deleteEmployees()` (812-828) / `FixedPaymentSave()` (829-...) — delete / single-save actions.

**Notifications**: None.

---

## 12. EmployeeIncrementReportsController.php — actually a general HR/statutory report hub, not just increments

**Purpose**: Despite the name, this is a broad **reporting module** (861 lines) covering HR reports, attendance reports, miscellaneous reports, salary reports, statutory reports, and increment reports — a shared report-criteria/report-type framework, not a single dedicated increment screen.

**Who can access**: Global gate + repeated `user_group==2` checks scoped to a specific tenant pair (`VGFS/VSFS`, lines 199,201,226,228,240,242,266,268,286,288,395,397) — i.e. these particular tenants get restricted report access as Employee(2); all other tenants presumably see the same reports regardless of role (not confirmed further).

**Flow (category landing pages, each presumably listing report types within that category)**:
1. `hrreports()` (56), `attendance()` (66), `miscellanious()` (80), `sallary()` (94) [sic — typo preserved from source], `statutory()` (108) — five report-category landing actions.
2. `changereporttype($type)` (126) — switches the active report type within a category (AJAX partial swap, typical of this app's SPA-via-`#container`-load pattern).
3. `addreportcriteria($type, $newindex, $str_currentcriterias)` (150) / `loadcriteriaitems($index, $str_criteria)` (172) / `listcriteriaitems($str_criteria)` (190-428) — a **dynamic report-criteria builder**: user adds filter rows (e.g. Branch, Department, Date Range) one at a time, each triggering an AJAX round-trip to fetch the next criteria's dropdown options and validate/list matching records — this is the most complex UI pattern here (multi-row dynamic filter builder), worth a dedicated deep-dive if the Next.js report screens need pixel-parity.
4. `reportAudit($type, $mode)` (430) — audit/preview step before generating.
5. `generatereport($type, $mode)` (522) — final report generation/export.
6. `listemployeefields()` (539) — field-picker for customizable report columns.
7. `generateemployeincrement($mode)` (623) — the actual increment-specific report generator (the one action that matches the controller's name).

**Notifications**: None (no Email/PHPMailer matches).

**Migration note**: given the name mismatch (controller is named for increments but implements a general report hub), **verify with the business which report types are still in active use** before committing UI/API surface — this smells like a controller that accreted unrelated features over time under a name nobody renamed.

---

## 13. YearEndController.php — annual leave/financial year-end closing

**Purpose**: Per-branch **year-end closing wizard**: shows pending leave requests for the closing year, lets Admin bulk-auto-approve them, then runs the actual year-close database routine, with an email-reminder feature to nudge approvers with pending requests first.

**Who can access**: Global gate + `user_group==2` branch-scoping via `GLET/ABSG` (`YearEndController.php:70`) — same recurring tenant pair as elsewhere.

**Step-by-step flow**:
1. `index()` (53-92) — Branch dropdown; looks up the branch's currently OPEN financial year (`fin_year` where `Year_status='OPEN' AND is_current_finyear='Y' AND vattr1=0 AND status=1` — note `vattr1=0` here means the **Leave**-type year record, consistent with `FinancialYearController`'s label mapping) and displays it.
2. **Select a branch** → `loadprocess()` (93-132) — loads that branch's fin-year window (start/end month), then for every active `leavepolicy_group`, joins `leavepolicy`→`salary_head_items` to build a **per-leave-type list with a live count of pending requests** (`LEAVESTATUS IN ('Applied','Authorized')` within the fin-year window) — this is the core "here's what's blocking year-end close" dashboard. **Hard stop**: if the branch has no active fin-year, the action calls `die("Selected Branch Have No Active Fin year")` (line 105) — a raw `die()` with no JSON envelope/flash message, which the frontend can't gracefully handle — flag as needing a proper error response in the rewrite.
3. **"Auto-Approve pending"** → `approve()` (137-152) — bulk-updates all `Applied`/`Authorized` leave entries within the fin-year window to `LEAVESTATUS='Approved'`, `ISAutherized=1`, `ISAPPROVED=1`, with `Reason='Auto Appproval'` [sic] and `REMARKS='Auto Approve'`, attributed to system user `0` — i.e. this is a **bulk administrative override of the normal leave-approval chain**, not a real approval by the assigned approver. Important business-rule nuance to flag to the product owner before reimplementing: it silently reassigns `ISAutherizedby`/`APPROVEDBY` to `'0'` regardless of who the real approver was.
4. **"Send reminder"** → `notice()` (176-358) — queries emails of anyone who *would* need to approve/authorize a pending leave entry in this window, then sends a marketing-styled HTML email ("Welcome to MyPayrollMaster... you have pending requests…") via **PHPMailer/SMTP with hardcoded credentials** (`YearEndController.php:197-353`, `Host='smtp.zoho.com'`, `Username='info@mypayrollmaster.in'`, `Password='welcome123'` in plaintext at line 204) — **flag immediately**: this is a second hardcoded-credentials instance (first was `PayrollController::generatePasswords`) and must never be copied into the new codebase; use environment-based secrets.
5. **"Process Year End"** → `processleave()` (153-174) — the actual close: derives the fin-year's start year, then calls DB function `year_ending_fn('$date','$branch')` (line 168) — all the real logic (presumably archiving/rolling leave balances, closing the year) lives inside this MySQL function, **not in application code** — this must be reverse-engineered from the DB schema/stored routines (out of scope of this controller file) before the Next.js migration can reimplement it; flag as a required follow-up (inspect `year_ending_fn` in the DB).
6. `loaders()` (133-136) — trivial stub, just sets DB config, no visible purpose beyond being an AJAX-loadable placeholder partial.

**Notifications**: Year-end reminder email (`notice()`, step 4 above) — HTML template embedded inline in PHP (lines 216-345), branded "MyPayrollMaster" (the vendor's own product name, not the tenant's) — another sign this was written once and never fully white-labeled per tenant, worth a product decision before porting branding as-is.

---

## Payroll Run Workflow (multi-step) — consolidated answer

**The workflow that is actually live** (`PayrollController.php`, wired from `View/Dashboard/index.ctp:441-442`) is a **2-stage process, not a deep multi-stage wizard**:

```
Branch + Month filter
        │
        ▼
  [FilterList] → stored proc payroll_master_insert()
  populates payroll_master rows from attendance_register
        │
        ▼
┌─────────────────────────┐
│ STAGE 1: PROCESS         │  screen: Payroll/showprocesspayroll
│ Tab "Not Processed"      │  (payroll_master.action IS NULL)
│  - toggle Include Tax    │
│  - select rows           │
│  - click "Process"       │──► processpayroll() runs stored proc
│                           │    calculateSalaryMainPrc / salaryProcessPrc
│                           │    (+ taxSalaryProcessPrc for special tenants)
│                           │    recomputes formula-based components (eval())
└─────────────┬─────────────┘
              │ row moves to Tab 2
              ▼
┌─────────────────────────┐
│ Tab "Processed"           │  action IS NULL still, but salary calculated
│  - View Slip (modal)      │
│  - Remove (undo, blocked  │──► removePayrollEntry(): action=NULL,
│    if action=='Approved') │    closes emp_salary_slip rows,
│                           │    resets emp_variables_upload 'fixed' rows
│  - (Hold — dead in UI)     │
└─────────────┬─────────────┘
              │ separate screen
              ▼
┌─────────────────────────┐
│ STAGE 2: APPROVE          │  screen: Payroll/showapprovepayroll
│ Tab "Not Approved"        │
│  - select rows            │
│  - click "Approve"        │──► approvepayroll(): stored proc
│                           │    payroll_master_approve(), then
│                           │    action = 'Approved'
└─────────────┬─────────────┘
              │
              ▼
┌─────────────────────────┐
│ Tab "Approved"             │  action = 'Approved' (terminal state
│  - View Slip                │  — cannot be Removed from Stage-1 UI,
│  - Email Slip (tenant-gated)│  guarded client-side)
│  - Password Gen (hidden btn,│──► emails payslip / portal passwords
│    backend still callable)  │    via SalaryReports/* or PHPMailer
└──────────────────────────┘
```

**What happens if something fails partway**:
- **Mid "Process"**: the loop in `processpayroll()` wraps the stored-proc call in `try/catch` **per employee** (`PayrollController.php:868-892`) and just `debug($e)`s the exception — it does **not** stop the loop or roll back; remaining employees in the same batch still get processed, and the overall response is still `{"success":1}` as long as at least one iteration set `$success=1`. So a partial failure is **silently swallowed** — the admin sees "Payroll processed successfully" even if some employees in the batch errored server-side. This is a real gap: no per-row error surfaced to the UI at this stage (contrast with `ArrearController::approvePayroll`, which does properly separate `processed`/`skipped` in its response, `ArrearController.php:1306-1310` — a better pattern that could be backported).
- **Undo before Approval**: "Remove" (`removePayrollEntry`) is the only rollback path, and it's explicitly blocked client-side once `action=='Approved'` (`showprocesspayroll.ctp:304-307`) — there is **no "un-approve"** action found in `PayrollController.php`. Once approved, the only further state changes are downstream (email slip, generate passwords) — there's no code path to revert `action='Approved'` back to null.
- **No locking/concurrency guard observed**: nothing in `processpayroll()`/`approvepayroll()` prevents two admins from double-processing/double-approving the same `payroll_master_pkey` concurrently (no optimistic lock/version column referenced) — **INFERRED risk**, not confirmed by a failing test, but worth a deliberate decision in the rewrite (e.g. add a status check before write, or use a DB transaction/lock).
- **Stored-procedure errors**: several stored-proc calls pass an output parameter conventionally named `@error`/`@perror_message` (e.g. `FilterList`: `PayrollController.php:213`; `approvepayroll`: `1785`) but **the PHP code never reads these output parameters back** — any error message the stored procedure sets is discarded; the controller always reports success to the frontend regardless of what the procedure actually did. This is the single biggest reliability gap in the whole payroll-run flow and should be explicitly designed around (proper error propagation) in the Next.js reimplementation.

---

## Summary of cross-cutting findings to flag to the migration/product team

1. **No true server-side RBAC for Admin-only screens** in this module — everything gates on the binary `user_group` session var from `AppController`; "Admin-only" is enforced by hiding menu links, not by controller checks, except for the one confirmed exception in `Taxsalarycomponents/EmployeeTaxController::saveemployeetaxheads/uploadFile` (`user_group==1` lock on tax-declaration edits).
2. **Two confirmed dead/orphaned controllers reachable only by direct URL**: `PayrollProcessController` (6-stage pipeline superseded by `PayrollController`'s 2-stage flow) and very likely `TaxationController` vs `TaxController` (same live/orphaned pattern, not fully confirmed — needs a view-reference grep). `SalaryHeadsControllernimishabackup.php` is a stray backup file.
3. **Hardcoded SMTP credentials in source** in two places: `PayrollController.php:1857-1861` (Oracle Cloud SMTP) and `YearEndController.php:201-204` (Zoho SMTP) — must become environment secrets.
4. **`eval()` used for formula recalculation** in `PayrollController::processpayroll()` (regex-guarded but still `eval`) — replace with a real expression parser.
5. **Widespread raw SQL string interpolation** (`"...= '$var'"` patterns) throughout every controller in scope — replace with parameterized queries; this is the default style, not an exception.
6. **Stored-procedure error/output-parameters are called but never read** — payroll/tax/year-end "success" toasts do not reflect actual backend success; this needs explicit redesign, not a straight port.
7. **Heavy per-tenant `company_code` string-branching** (`GLET`,`ABSG`,`DEMO`,`KWMT`,`GAAR`,`SHYD`,`VGFS`,`VSFS`, and the `specialCompanies` array of ~17 codes in `PayrollController.php:818`) embeds customer-specific business rules directly in shared code — the Next.js rewrite should externalize these into per-tenant configuration rather than hardcoded lists.
8. **Duplicated/forked logic between `SalaryComponentUploadController` and `SalaryIncrementController`** (identical function names, likely diverged copies) and between `TaxsalarycomponentsController`/`EmployeeTaxController` (identical function sets) — both pairs need a diff before deciding on a single unified implementation.
9. **A likely-dead-code bug**: `SalaryHeadsController::addsalaryheads()` calls its own duplicate-check helper only after an unconditional `return`, making duplicate-name protection unreachable (`SalaryHeadsController.php:294,307,310`).
10. **`YearEndController::loadprocess()` uses a raw `die()`** on the "no active financial year" case instead of a structured JSON/flash error — breaks the app's otherwise-consistent AJAX-JSON contract.

---

## 6. Advances, Loans & Expenses

# Advances, Loans & Expenses — Legacy Behavior Report

Scope: `legacy/Controller/{Advance,Employeeadvance,EmployeeAdvanceReports,EmployeeLoan,EmployeeLoanReports,EmployeeExpenses,EmployeeExpenseReports,ExpenseItem,ExpenseType,ExpenseTypes,ExpenseReport,ProjectExpenses,ProjectExpenseReport,VehicleExpenses,PaymentApprovals}Controller.php` and matching `legacy/View/*` directories. `legacy/Config/routes.php` has **no custom routes** for any controller in this scope (verified via `grep -inE "advance|expense|loan|paymentapproval" Config/routes.php` → no matches) — all URLs are default CakePHP `/ControllerName/action/param`.

## Global auth baseline (applies to every controller below)

All controllers extend `AppController`. `Controller/AppController.php:39-46` gates every request: session `user_group` must be `1` (Admin/HR) or `2` (Employee/ESS); anything else redirects to `Site/login`.

**Important finding: none of the 15 controllers in this scope perform any server-side `user_access`/`menu_id` permission check.** `grep -linE "user_access|menu_id" <all 15 controllers>` matched only one incidental, functionally-inert usage (`Controller/EmployeeadvanceController.php:1303`, inside `listpunches()`, an attendance helper unrelated to advances/loans/expenses — the query result is fetched but never used to filter the SQL, both branches set `$emp_condition = ""`). This means: **any authenticated user_group 1 or 2 session can hit any action in these controllers directly by URL**; the only real gating is the client-side sidebar menu (built from `emp_menu`/`user_access` elsewhere, out of scope here) and the in-code `user_group` branches described per-controller below (which control *what data/view* is returned, not *whether* the action runs).

---

## 1. AdvanceController

**Purpose**: Simple admin-only CRUD for ad-hoc "advance" payments against `advance_expense` table (petty-cash-style advances, e.g. Bank/Cash/Credit Card/named payment accounts), unrelated to payroll salary advances.

**Access**: No `user_group` branching at all — any authenticated user (group 1 or 2) can call `form`, `advancesave`, `advancelist`, `deleteEmployee` (`Controller/AdvanceController.php:55-212`). INFERRED: intended for Admin only (sidebar menu likely hides it from group 2), but not enforced server-side.

**Flow**: Single-step, no approval chain.
- `form()` (`AdvanceController.php:55-72`) renders a modal (`View/Advance/form.ctp`) listing all active employees and bank accounts.
- `advancesave()` (`AdvanceController.php:75-104`) does a raw `updateAll`/`save` on `advance_expense` — record is immediately persisted with no status/workflow field. No employee-facing "request"; this is direct HR data entry.
- `advancelist()` (`AdvanceController.php:107-182`) — datagrid JSON feed; hardcodes a 13-entry lookup table mapping `account_fkey` 1–13 to bank/cash/card names (`AdvanceController.php:143-171`) — brittle, will need to become a real lookup table/enum in the rewrite.
- `deleteEmployee()` (`AdvanceController.php:197-210`) — soft delete (`status=0`) by `advance_pkey` list.

**Forms**: `View/Advance/form.ctp` — employee select2, bank account select, date, amount, remarks. No visible client-side validation logic beyond `required` attributes (not fully verified, file not read in depth beyond controller).

**Notifications**: None (no Email component usage found).

**AJAX endpoints**: `Advance/advancesave`, `Advance/advancelist`, `Advance/deleteEmployee` (all same-origin form posts / datagrid feeds).

---

## 2. EmployeeadvanceController ("Salary Advance")

**Purpose**: HR-managed salary advances against payroll (deducted from a future salary run), distinct from `AdvanceController`'s petty-cash advances. Uses `emp_advance` table via `EmployeeAdvance` model.

**Access**:
- `index()` (`Controller/EmployeeadvanceController.php:59-79`): `user_group==1` → admin dashboard (`index` view with active employee count + branch list, `EmployeeadvanceController.php:63-72`). `user_group==2` → calls `$this->setup($emp_fkey)` then `$this->render('setup')` (`EmployeeadvanceController.php:73-77`).
  - **INFERRED BUG**: No `setup()` method is defined anywhere in this class (`grep -n "function setup" Controller/EmployeeadvanceController.php` → no match), and the base `AppController` doesn't define it either. Calling `$this->setup($emp_fkey)` on an employee (group 2) session should throw a PHP fatal error ("Call to undefined method"). This same pattern repeats identically in `EmployeeLoanController.php` and `EmployeeExpensesController.php` (see below) and `ProjectExpensesController.php:71-76`. Worth flagging to the team — possibly dead/broken code path, or `setup` is silently caught/suppressed by a global error handler not in scope.
- `form()` (`EmployeeadvanceController.php:657-713`) branches on `user_group=='2'` **and** `company_code` in `('GLET','ABSG')` to restrict the employee dropdown to the user's own branch via `get_branch_code_abs_fn()` (`EmployeeadvanceController.php:670-680`); otherwise shows all active employees — i.e. company-specific custom logic, not a generic employee/admin split.
- Renders `absForm` view for companies GLET/ABSG, else `form` (`EmployeeadvanceController.php:708-712`) — a company-specific form variant.

**Flow**: No approval workflow — this is direct HR data entry, immediately effective.
- `form()` populates employee list + prefill data if `emp_advance_pkey` passed.
- `salarycheck()` (`EmployeeadvanceController.php:716-743`) — AJAX guard: checks if salary already processed for the chosen month (`emp_salary_slip` where `end_date_effective is null`); returns rows to the UI so it can block advance entry against an already-processed month.
- `salary()` (`EmployeeadvanceController.php:746-879`) — AJAX: computes an **advance eligibility ceiling**. Default: 80% of one month's gross salary (`EmployeeadvanceController.php:763-764`). For company codes GLET/ABSG, replaced with a much more complex prorated/attendance-based formula involving `emp_salary_structure.prorate_code`, `attendance_register`, device punches, and existing advances already taken for the month (`EmployeeadvanceController.php:766-871`) — this is heavy, company-specific business logic that will need explicit product sign-off before being reimplemented.
- `employeeloansave()` (`EmployeeadvanceController.php:882-914`) — saves directly to `emp_advance` with `payment_date`/`affected_month` computed from form input; no status field set, no approval step; response `"Salary Advance Added successfully"`.

**Forms**: See `View/Employeeadvance/form.ctp` and `abs_form.ctp` (not fully read line-by-line; multiple stale `#bkup_*` copies exist in `View/Employeeadvance/`, confirming frequent ad-hoc edits — `form.ctp#backup_Akshay_28-2-2025`, `#bkup_arul_8_02_2021`, `#bkup_megha_17_02_2025`, `#bkup_sinsiya_22_07_2024`).

**Notifications**: None.

**AJAX/JS endpoints**: `Employeeadvance/salarycheck`, `Employeeadvance/salary`, `Employeeadvance/employeeloansave`, `Employeeadvance/employeelist` (listing, `EmployeeadvanceController.php:917+`, not fully traced), `Employeeadvance/listpunches` (attendance helper reused from mis-punch logic, `EmployeeadvanceController.php:1251-1345`).

---

## 3/4. EmployeeAdvanceReportsController (+ `_2018-010` duplicate)

**Purpose**: Report-builder for Salary Advance data — criteria-driven, generates on-screen/Excel report (`generateemployeeadvance()`), plus a `generatesummaryreport()`.

**Duplicate confirmed**: `EmployeeAdvanceReportsController_2018-010.php` is an older, near-identical copy (diff shows only whitespace/formatting differences and one label change: `'Advance' => 'Salary Advance'` in the live file vs `'Advance' => 'Employee Advance'` in the 2018 dup — `Controller/EmployeeAdvanceReportsController.php:64` vs `Controller/EmployeeAdvanceReportsController_2018-010.php:60`). Treat the live (non-suffixed) file as canonical; the `_2018-010` file is dead/unused legacy cruft (not referenced by routes or any `App::uses`/`requestAction` grep hit).

**Access**: `hrreports()` (`EmployeeAdvanceReportsController.php:59-86`) reads `user_group` and a plan/feature flag from `controldb.CentralUserCredentials`/`Plan` tables to decide what to render (`EmployeeAdvanceReportsController.php:60-82`) — this looks like the SaaS **plan-gating** mechanism (same shape as `PaymentApprovalsController::getPaymentFeatures()`, see Approval Workflow section) rather than RBAC; it drives which report options are shown based on the company's subscription plan, not the user's role. No hard `redirect`/`throw` found on missing plan — INFERRED it's advisory (UI hides options) not a hard block.

**Flow** (report-builder pattern, shared shape with EmployeeLoanReports and EmployeeExpenseReports below):
1. `hrreports()` → shows report type dropdown.
2. `changereporttype($type)` → AJAX-loads criteria fields for chosen report type from `ReportCriterias` table, renders `showreport.ctp`.
3. `addreportcriteria()` / `loadcriteriaitems()` / `listcriteriaitems()` (`EmployeeAdvanceReportsController.php:90-227` approx.) — dynamic multi-criteria builder (add/remove filter rows in the UI, each backed by an AJAX call to fetch valid values, e.g. employee list, branch list).
4. `generatereport($type,$mode)` → dispatches to `generateemployeeadvance($mode)` (private, `EmployeeAdvanceReportsController.php:404+`) which builds and streams the report (`$mode` presumably `'view'|'excel'`/similar, not fully confirmed).
5. `downloadHistory()` / `reportAudit()` — logs/serves prior generated reports via `ReportAudit` model.

**Forms**: Dynamic criteria rows — client JS (in `showcriteria.ctp`/`loadcriteriaitems.ctp`, not read in full) adds/removes filter blocks; no employee-facing form here (HR/reporting only).

**Notifications**: None.

**AJAX endpoints**: `EmployeeAdvanceReports/changereporttype`, `.../addreportcriteria`, `.../loadcriteriaitems`, `.../listcriteriaitems`, `.../generatereport`, `.../downloadHistory`.

---

## 5/6. EmployeeLoanController + EmployeeLoanReportsController (`_2018-10` duplicate)

### EmployeeLoanController

**Purpose**: Admin-managed employee loan issuance and EMI (equated monthly installment) schedule generation/tracking against `emp_loan`/`emp_loan_info` tables.

**Access**: `index()` (`Controller/EmployeeLoanController.php:59-66`) reads `emp_fkey` from session and lists loans `where emp_fkey = $emp_fkey` — **no `user_group` branch at all**, unlike the sibling controllers. This means: for an Admin (group 1) whose session has no `emp_fkey` set, `index()` would show an empty/broken loan list — INFERRED admins use a different list view (e.g. `employeeloanlist()`, `EmployeeLoanController.php:1226+`) reached via the sidebar rather than `index()`. No `setup()` call here (unlike the advance/expense controllers), so no equivalent fatal-error risk on this one.

**Flow**: No approval workflow — admin directly creates a loan and the system pre-computes the full amortization schedule up front.
- `form()` (`EmployeeLoanController.php:427-487`) — admin picks employee (excluding terminated employees, `EmployeeLoanController.php:434`), enters loan amount/tenure/interest/EMI/start-end months. Contains a "3-day edit window" check (`EmployeeLoanController.php:453-479`): if the loan was created more than 3 days ago, sets a flag (`data3`) presumably used by the view to restrict further editing — business rule that should be preserved.
- `employeeloansave()` (`EmployeeLoanController.php:1158-1223`) — saves the loan header, then **loops `tenure` times** computing `opening_balance`, `interest`, `principal`, `closing_balance` per month via simple monthly-interest amortization (`interest% * opening_balance / 12`), inserting one `emp_loan_info` row per month (`EmployeeLoanController.php:1179-1210`). Handles rounding remainder in the final installment so total EMIs reconcile to the loan amount (`EmployeeLoanController.php:1199-1218`) — non-trivial financial logic to port faithfully.
- `emi_upload()` / `uploadandsaveempemi()` — bulk Excel upload of EMI data (`EmployeeLoanController.php:489-552`, `2012+`).
- `update_transfer()`, `amount_pay()`, `update()`, `completed()` — mid-cycle loan adjustments (transfer balance, ad-hoc extra payment, mark completed) (`EmployeeLoanController.php:585-850`).
- `viewloan()`, `downloads()`, `downloadexcels()` — loan detail view and export.
- `deleteEmployeeloan()` (`EmployeeLoanController.php:1464+`) — soft delete.

**Forms**: `View/EmployeeLoan/form.ctp`, `emi_upload.ctp`, `ctcupload.ctp`, `update.ctp`. Multiple stale `#bkup_*` copies present (`ctcupload.ctp#bkup_akshay_04_11_2025`, `#bkup_bindu_10_12_2025`, `#bkup_megha_05_10_2023`; `update.ctp#bkup_sinsiya_17_03_2024`).

**Notifications**: None.

**AJAX/JS endpoints**: `EmployeeLoan/getEmi`, `.../checkmonth`, `.../payroll_check_amount_pay`, `.../employeeloansave`, `.../employeeemiloansave`, `.../employeeloanlist`, `.../jsons`, `.../jsons_form`, `.../getLoanBalance`.

### EmployeeLoanReportsController (+ `_2018-10` dup)

Same report-builder shape as EmployeeAdvanceReports (`hrreports→changereporttype→addreportcriteria→loadcriteriaitems→listcriteriaitems→generatereport→generateemployeeeloan(private)/generatesummaryreport(private)`), confirmed via `grep -n "function " Controller/EmployeeLoanReportsController.php`. The `_2018-10` file is the same older near-duplicate pattern (not diffed in full, but same structure/line-count proximity as the advance-reports pair; treat as dead legacy code).

---

## 7. EmployeeExpensesController — the core two-level approval workflow

**Purpose**: Employee-initiated expense reimbursement requests (`emp_expense` table) with receipt image upload, routed through a **two-level "Authorize then Approve" chain** before payment.

**Access**:
- `index()` (`Controller/EmployeeExpensesController.php:59-79`): group 1 → admin dashboard; group 2 → `$this->setup($emp_fkey)` + render `setup` — **same undefined-method risk noted above** (no `setup()` defined in this class either).
- `listemployees()` (`EmployeeExpensesController.php:86-130`) restricts the employee list to the current user's own branch when `user_group==2` (`EmployeeExpensesController.php:110-117`).
- `form()` (`EmployeeExpensesController.php:137-218`): admin (`user_group!=2`) sees a flat employee dropdown; employee (`user_group==2`) instead sees **no employee picker** (hidden field defaults to self) plus two dynamically-computed dropdowns — "Authorized By" and "Approved By" — populated by calling a MySQL stored function `leave_auth_apr_person_fn(company_code, emp_pkey, 'auth'|'api')` (`EmployeeExpensesController.php:179-196`). This is the same authorizer/approver resolution function used by the **leave request** module (reused, not expense-specific) — confirms this org chart/reporting-line lookup is centralized in the DB layer.

**Step-by-step flow**:
1. **Employee submits** (group 2) via `View/EmployeeExpenses/form.ctp` modal (`EmployeeExpensesController.php:137-218`, form posts to `EmployeeExpenses/employeeloansave`): expense type, date, amount, receipt image upload, purpose, vendor, remarks, plus **required** Authorized-By and Approved-By employee selects.
2. `employeeloansave()` (`EmployeeExpensesController.php:268-370`): if `authorized_by` present in payload (employee path) → status implicitly `Applied` (default DB value, not explicitly set in this branch) and dates computed from `affected_month`. If `authorized_by` absent (**admin direct-entry path**, `EmployeeExpensesController.php:280-292`) → the record is created **pre-approved**: `expense_status='Approved'`, both `remarks_auth`/`remarks_approved` auto-filled `"...By Admin"`, `approved_date`/`authorized_date` set to today, success message `"Expense Approved Successfully!!!"`. Receipt image is uploaded server-side with MIME-type sniffing (`finfo`) restricted to jpg/jpeg/png/gif/pdf, 5MB cap, saved to `/var/www/html/mpm/expense/{company_code}/` (`EmployeeExpensesController.php:300-358`) — hardcoded absolute filesystem path, will need to become object storage in the rewrite.
3. **Level 1 — Authorization**: the designated "Authorized By" employee (or any Admin) sees the request in their queue via `listempexpense()` (`EmployeeExpensesController.php:1944-1976`, filters `authorized_by = cur_emp_key AND status='Applied'` OR `approved_by = cur_emp_key AND status='Authorized'`) and opens `manageexpense($expenseId)` (`EmployeeExpensesController.php:1818-1865`) → renders `View/EmployeeExpenses/manageexpense.ctp` in "edit" mode when `status` is `Applied`/`Authorized` and the viewer is the assigned authorizer/approver, else "view" (read-only) mode (`EmployeeExpensesController.php:1856-1860`).
4. Approve/Reject/Authorize action posts to `grandexpense()` (`EmployeeExpensesController.php:1867-1942`, form in `manageexpense.ctp:2` action=`EmployeeExpenses/grandexpense`):
   - If the caller is the **approver** (`cur_emp_key == apr_person`) or an Admin (`user_group==1`) → status becomes `'Approved'`, `approved_date` set (`EmployeeExpensesController.php:1887-1890`).
   - Else if caller is the **authorizer** (`cur_emp_key == auth_person`) → status becomes `'Authorized'` (still pending final approval) (`EmployeeExpensesController.php:1891-1893`).
   - Reject (form field `reject=1`) → status `'Rejected'` regardless of level (`EmployeeExpensesController.php:1895-1898`).
   - When `cur_emp_key` is null (i.e. **Admin acting**, no `emp_fkey` in session) both authorize+approve remarks/dates are stamped simultaneously with an "Admin" prefix — Admin can short-circuit both levels in one click (`EmployeeExpensesController.php:1899-1913`).
   - Response message differs by role: `"Expense Approved Successfully!!!"` / `"Expense Authorized Successfully!!!"` / `"Expense Rejected Successfully!!!"` (`EmployeeExpensesController.php:1931-1939`).
5. **Level 2 — Approval**: same `manageexpense`/`grandexpense` cycle, now gated on `approved_by`. Final states: `Approved`, `Rejected`, plus terminal `Removed`/`Cancelled` (set via `cancalEmployee()`, `EmployeeExpensesController.php:1781-1797`, soft-cancel by the employee/admin on an already-decided record).
6. Employee can track their own submissions via `viewrequest()`/`employeerequests()` (`EmployeeExpensesController.php:2011-2050`) and view single-record detail via `viewexpense()` (`EmployeeExpensesController.php:2052-2076`); receipt image served via `showimage()` (`EmployeeExpensesController.php:2078-2085`, view `View/EmployeeExpenses/showimage.ctp`).

**Forms** (`View/EmployeeExpenses/form.ctp`, read in full):
- Fields: Employee (admin only, select2), Expense Type (select, populated from `expense_type` table), Expense Date (readonly text + Bootstrap datepicker, `required`), Expense Amount (`type=number`, `required`, blur-validated via `findamount()` regex `^[1-9][0-9\.]{0,15}$` — rejects 0/negative/non-numeric, `form.ctp:334-349`), Image (file upload, no client-side type/size check — server enforces it), Purpose (free text), Vendor (free text), Authorized By / Approved By (employee-only, required selects, select2-enabled), Remarks (free text).
- Client-side: uses `.validate()` jQuery plugin (`form.ctp:14-16`), a `confirm()` dialog before submit with a 2-second artificial delay (`form.ctp:38-49`), and an AJAX salary-slip guard `findextingsalary()` (`form.ctp:264-332`) that calls `EmployeeExpenses/salarycheck` on date/employee change — if the affected month's salary is already processed, the entire form is locked read-only and the Save button hidden (mirrors the advance-eligibility pattern in EmployeeadvanceController).
- Server-side validation is minimal — mostly implicit (relies on required HTML attributes); no CakePHP model-level validation rules were found in the controller for this action.

**Notifications**: None (no Email/SMS calls found in this controller). All "notification" is via `$.notify()` toast messages client-side and the queue-based `listempexpense`/`listempexpenseverified` badges — INFERRED there is no email/push alert to the authorizer/approver when a request lands in their queue; they must check the app.

**AJAX/JS endpoints hit from these views**: `EmployeeExpenses/salarycheck`, `.../employeeloansave`, `.../listempexpense`, `.../listempexpenseverified`, `.../manageexpense/{id}` (loads modal HTML), `.../grandexpense`, `.../cancalEmployee`, `.../showimage/{id}`, `.../viewrequest`, `.../viewexpense/{id}`, `.../empexpenselist`, `.../listemployees`.

---

## 8. EmployeeExpenseReportsController (+ `_2018-10` duplicate)

Same report-builder shape as the Advance/Loan report controllers (`hrreports→changereporttype→addreportcriteria→loadcriteriaitems→listcriteriaitems→generatereport→generateemployeeexpence(private, line 438)/generatesummaryreport(private, line 1009)`), confirmed via `Controller/EmployeeExpenseReportsController.php:55-1009` function list.

Notable addition vs. the other two report controllers: `hrreports()` also resolves the company's **subscription plan** via `controldb.CentralUserCredentials`/`plan_id` and sets `planId`/`plan`/`user_group` for the view (`EmployeeExpenseReportsController.php:60-85`) — same plan-gating pattern seen in `PaymentApprovalsController::getPaymentFeatures()` (see below), and `listcriteriaitems()` (`EmployeeExpenseReportsController.php:155-230+`) contains elaborate **company-specific branch-scoping logic** for the `Units` criteria: different rules for `GLET`/`ABSG` (branch-restricted via `get_branch_code_abs_fn`), `GAAR`/`HRBL` (director's-branch exclusion unless a `special_access` flag is set per user, `EmployeeExpenseReportsController.php:183-195`), and a generic fallback (self-branch only) for other companies when `user_group==2`. This per-tenant branching pattern recurs across this whole feature area and should be modeled as configurable rules, not hardcoded company-code `if`s, in the rewrite.

The `_2018-10` duplicate exists at `Controller/EmployeeExpenseReportsController_2018-10.php` (791 lines vs. 1261 in the live file) — confirmed stale/superseded, not routed anywhere.

---

## 9. ExpenseItemController

**Purpose**: Master-data CRUD for "expense items" (a catalog entity — `expense_item` table joined against `expense_type`), used to populate expense-related dropdowns elsewhere. Also references item-master infrastructure (`Item`, `ItemDetails`, `ItemPricing`, `CategoryMaster`, `PackageMaster`, `UoMaster`, `WarrantyDetails`) shared with a broader inventory/procurement module (out of scope) — `Controller/ExpenseItemController.php:49`.

**Access**: No `user_group` checks anywhere in this controller (confirmed by grep). Admin-only by convention/menu visibility only.

**Flow**: Pure CRUD, no workflow/approval.
- `itemfilter()` (`ExpenseItemController.php:57-82`) — AJAX typeahead/select2 source for expense items (`q` search param).
- `form($acct_payable_pkey)` (`ExpenseItemController.php:113-151`) — modal form with category/specification/package/measurement dropdowns.
- `chkcategory()` (`ExpenseItemController.php:153-174`) — AJAX duplicate-check against `item_master` by `item_code` or `item_desc`.
- `itemlist()` (`ExpenseItemController.php:177-237`) — datagrid feed.
- `itemdelete()` (`ExpenseItemController.php:240-260`) — soft delete.
- `save()` (`ExpenseItemController.php:262-288`) — hardcodes `created_by = "Admin"` literal string rather than session user (`ExpenseItemController.php:277`) — a bug/inconsistency vs. every other controller's `Session->read('login_user_id'|'user_name')` pattern; also leaves a `debug($arr_form_data)` call live in production code (`ExpenseItemController.php:276`).

**Forms**: `View/ExpenseItem/form.ctp` (not read line-by-line beyond controller context).

**Notifications**: None.

**AJAX endpoints**: `ExpenseItem/itemfilter`, `.../chkcategory`, `.../itemlist`, `.../itemdelete`, `.../save`.

---

## 10/11. ExpenseTypeController vs ExpenseTypesController — near-duplicate pair

**Purpose**: Master-data CRUD for expense type/category (`expense_type` table, joined to `expense_heads`), used as the dropdown source in the expense-request forms above (`EmployeeExpensesController.php:174`, `ProjectExpensesController` etc.).

**Confirmed near-duplicate**: `ExpenseTypesController` (`Controller/ExpenseTypesController.php`, 212 lines) is a trimmed copy of `ExpenseTypeController` (`Controller/ExpenseTypeController.php`, 351 lines) — same `index/expense/delete/save/listexpense/checkexpensetypenameexists/checkexpensetypecodeexists/search` actions with near-identical bodies (compare `ExpenseTypeController.php:36-267` to `ExpenseTypesController.php:36-218`), **minus** the budget-allocation actions `allocate_expense()`, `save_allocate()`, `remove_allocate()` (`ExpenseTypeController.php:268-350`, absent from `ExpenseTypesController`). INFERRED: `ExpenseTypesController` is either an abandoned refactor or a cut-down variant used by a different menu entry; both are live (no `_2018`-style suffix marking one as archived), so the migration needs to determine from the frontend menu config which one is actually linked before consolidating.

**Access**: No `user_group` checks in either controller.

**Flow** (ExpenseTypeController, the fuller of the two):
- `expense($expense_type_pkey)` (`ExpenseTypeController.php:41-61`) — add/edit modal, title toggles "Add"/"Edit" based on whether a pkey was passed.
- `save()` (`ExpenseTypeController.php:81-153`) — **duplicate-name guard**: blocks save and returns `success:false, msg:"Expense Name Already Exist"` if another active row has the same `expense_type_name` (`ExpenseTypeController.php:103-128`); a symmetric duplicate-code check exists but is fully commented out (`ExpenseTypeController.php:97-115` comments) — dead/disabled validation.
- `listexpense()` — datagrid feed with server-side search-by-name and sortable columns.
- `checkexpensetypenameexists()` / `checkexpensetypecodeexists()` — separate AJAX validators (presumably called on blur in the form, though the form.ctp wasn't read in full) duplicating the same check `save()` does inline.
- **Budget allocation sub-feature (ExpenseTypeController only)**: `allocate_expense($expense_type_pkey)` (`ExpenseTypeController.php:268-280`) shows which `Designation`s are/aren't yet allocated to an expense type (via `allocate_expense` join table); `save_allocate()` (`ExpenseTypeController.php:282-319`) lets HR assign either a specific designation or "ALL" remaining designations to an expense type; `remove_allocate()` (`ExpenseTypeController.php:321-350`) soft-removes one designation's allocation. This looks like a **designation-based expense-type eligibility control** (which job grades can claim which expense types) — not used/verified elsewhere in this scope, but likely consumed by expense-request validation logic not found in the read files (INFERRED — no controller in scope actually checks `allocate_expense` when a request is submitted, meaning it may be a partially-built/unused feature).

**Forms**: `View/ExpenseType/expense.ctp`, `form.ctp` (multiple stale bkups: `expense.ctp#bkup_16_5_20`, `#bkup_megha_22_05_2020`; `index.ctp#bkup_16_5_20`, `#bkup_amal_15_06_19`, `#bkup_megha_22_05_2020`); `View/ExpenseTypes/expense.ctp`, `index.ctp` (one bkup: `index.ctp#bkup_bindu_13_12_2025`, i.e. edited as recently as Dec 2025).

**Notifications**: None.

**AJAX endpoints**: `ExpenseType/save`, `.../delete`, `.../listexpense`, `.../checkexpensetypenameexists`, `.../checkexpensetypecodeexists`, `.../search`, `.../allocate_expense`, `.../save_allocate`, `.../remove_allocate` (and the `ExpenseTypes/*` equivalents minus the allocate trio).

---

## 12. ExpenseReportController

**Purpose**: Yet another report-builder, this one for a generic "Expense Report" (`AdvanceExpense` report type label, `Controller/ExpenseReportController.php:57`) — separate from `EmployeeExpenseReportsController`. Also contains payroll-adjacent report actions (`hrreports`, `bankreport.ctp`, `reportsalary.ctp` views exist) suggesting this controller is a shared/older report hub whose scope overlaps other reports controllers.

**Access**: No `user_group` gating found in the functions read (`hrreports`, `changereporttype`).

**Flow**: Same report-builder shape (`hrreports→changereporttype→addreportcriteria→loadcriteriaitems→listcriteriaitems→downloadHistory→generatereport→listemployeefields→generateExpenseReport(private)`), confirmed via function list at `Controller/ExpenseReportController.php:55-339`.

**Forms/Views**: `View/ExpenseReport/{bankreport,expensereports,hrreports,loadcriteriaitems,reportsalary,showcriteria,showreport}.ctp` — the presence of `bankreport.ctp` and `reportsalary.ctp` suggests this controller's scope bled into payroll/bank-file reporting territory beyond pure "expenses"; INFERRED this is an older, broader report controller that `EmployeeExpenseReportsController` may have since specialized away from (not confirmed by reading both fully).

**Notifications**: None.

**AJAX endpoints**: `ExpenseReport/changereporttype`, `.../addreportcriteria`, `.../loadcriteriaitems`, `.../listcriteriaitems`, `.../downloadHistory`, `.../generatereport`, `.../listemployeefields`.

---

## 13. ProjectExpensesController — parallel, single-level approval + purchase-order features

**Purpose**: Project/site-scoped expense requests against vendors/beneficiaries (`Controller/ProjectExpensesController.php:51` model list includes `ExpenseTypePaymentDetails`, `ExpenseTypeDetails`), reusing the same `emp_expense`/`EmployeeExpenses` model as the personal-expense feature (7 above) but with project/site/vendor/beneficiary/purchase-order fields layered on top. By far the largest controller in scope (2525 lines, 45 actions).

**Access**: `index()` (`ProjectExpensesController.php:58-77`) has the **identical group 1/2 branch + `$this->setup($emp_fkey)` call** as `EmployeeExpensesController`/`EmployeeadvanceController` — same undefined-method risk (`ProjectExpensesController.php:71-76`, no `setup()` defined in this class). `listemployees()` (`ProjectExpensesController.php:84-127`) has the identical own-branch restriction for `user_group==2` as `EmployeeExpensesController::listemployees()`.

**Step-by-step flow — key difference from EmployeeExpensesController: single-level approval, not two-level**:
1. `form()` builds the create/edit modal (site/vendor/beneficiary/expense-type pickers).
2. `save()` (`ProjectExpensesController.php:1372-1568+`): on first save (no `emp_expenses_pkey`), branches only on `user_group`:
   - Admin (`user_group==1`): `authorized_by='Admin'`, `expense_status='Approved'` immediately — no approval step at all for admin-entered project expenses (`ProjectExpensesController.php:1391-1395`).
   - Employee (`user_group==2`): `expense_status='Applied'`, `authorized_by` = whichever single person the employee selected in the form (`ProjectExpensesController.php:1396-1401`) — **there is only one authorizer field here, not separate Authorized-By/Approved-By** like `EmployeeExpensesController`.
   - On subsequent saves (edit, `emp_expenses_pkey` present), does a raw hand-built `updateAll` with string-interpolated SQL literals for vendor/beneficiary/expense_date/remarks/gst_bill_no/gst_bill_status/payment/balance/payment_status/authorized_by (`ProjectExpensesController.php:1409-1445`) — notably **not parameterized**, same as most of this codebase, but worth flagging for SQL-injection review during migration since these come straight from `$this->request->data`.
3. `manageexpense($expenseId)` (`ProjectExpensesController.php:689-756`): loads the request with joins to `site`, `beneficiary`, `expense_type`; "view" mode once status is `Approved`/`Rejected`, else "edit" mode for the assigned authorizer — same shape as the personal-expense flow but with only one status/level.
4. `grandexpense()` (`ProjectExpensesController.php:758-805`): approve → `expense_status='Approved'`; reject → `expense_status='Rejected'`, **plus a company-specific side effect**: for `company_code=='ZWLK'` and `headkey==1`, also cancels a linked `advance_payment` row (`ProjectExpensesController.php:781-783`) — tenant-specific business rule tightly coupled into shared code, another candidate for config-driven rules in the rewrite. No separate "Authorize" state exists here (compare to `EmployeeExpensesController::grandexpense()` which has three outcomes: Authorized/Approved/Rejected) — **confirms the two-level pattern is specific to personal expenses, not project expenses.**
5. Listing/tracking mirrors the personal-expense controller: `listempexpense()`, `listempexpenseverified()`, `employeerequests()`, `viewrequest()`, `viewexpense()`, `showimage()` (`ProjectExpensesController.php:807-1032`).
6. **Purchase-order-like sub-flow layered on top** (not present in `EmployeeExpensesController`): `loadnew()`/`loadnewpurchase()`, `aprlist()`/`emplist()`/`explist()`/`benlist()`/`prolist()` (dropdown/lookup feeds for approvers/employees/expense-types/beneficiaries/projects), `editexpense()`/`edit_save()`, `returnexpense()`/`return_save()` (return/refund flow), `editexpensepayment()`/`editpayment_save()` (partial-payment tracking against a request, presumably multiple payments against one approved expense), `loadtable()` (line-item table for a multi-item purchase), `deleteorder()`/`deleteordermaster()`, `advanceamount()` (link to an advance payment against this expense), `downloads()`/`downloadexcels()` (`ProjectExpensesController.php:1056-2486`). This is effectively a lightweight **purchase-order + payment-tracking module** riding on the expense-approval primitive — significantly more complex than the employee self-service expense flow and will need its own dedicated design pass rather than being folded into the "expense request" migration ticket.

**Forms**: `View/ProjectExpenses/{form,newexpense,purchase,editexpense,editexpensepayment,returnexpense,manageexpense,show_expense,view_expense,viewexpense,showimage,download,empexpenselist,employeerequests,expenses,loadnewpurchase}.ctp` (16 live view files, no stale bkups listed for this directory in the `ls` I ran, though I filtered `#bkup` — worth re-checking if a from-scratch inventory is needed).

**Notifications**: None found.

**AJAX/JS endpoints**: `ProjectExpenses/salarycheck`, `.../employeeloansave`, `.../manageexpense/{id}`, `.../grandexpense`, `.../listempexpense`, `.../listempexpenseverified`, `.../aprlist`, `.../emplist`, `.../explist`, `.../benlist`, `.../prolist`, `.../loadnew`, `.../loadnewpurchase`, `.../loadtable/{id}/{rowindex}`, `.../save`, `.../edit_save`, `.../return_save`, `.../editpayment_save`, `.../deleteorder`, `.../deleteordermaster`, `.../category`, `.../submit`, `.../expense_typelist`, `.../request_id`, `.../get_details/{id}/{type}`, `.../showimage/{id}`, `.../downloads/{pkey}`, `.../downloadexcels/{pkey}`.

---

## 14. ProjectExpenseReportController

**Purpose**: Report-builder for project expenses — three distinct report generators in one controller: `GenerateProjectExpensereport()` (private, `Controller/ProjectExpenseReportController.php:243`), `GenerateBeneficiaryExpensereport()` (private, line 1902), `GenerateExpenseTypeReport()` (private, line 2267) — the largest of the report controllers (2686 lines).

**Access**: Same `hrreports→changereporttype→addreportcriteria→loadcriteriaitems→listcriteriaitems→generatereport→listemployeefields` shape as the other three report controllers (`ProjectExpenseReportController.php:56-236`), confirmed via function-list grep. No `user_group` gating beyond whatever `listcriteriaitems()` branch logic exists (not fully read; INFERRED similar per-tenant branch-scoping as `EmployeeExpenseReportsController::listcriteriaitems()` given the shared model/pattern, but not verified line-by-line for this file given time constraints — flag for a follow-up pass if precise parity is required).

Additional criteria-lookup helpers unique to this controller: `projectcriteria($site)`, `bencriteria($ben,$crit)`, `itemcriterialist($ben,$crit)` (`ProjectExpenseReportController.php:1869-1901`) — cascading site→beneficiary→item-criteria dropdowns for the report builder.

**Notifications**: None.

**AJAX endpoints**: same family as other report controllers, plus `ProjectExpenseReport/projectcriteria`, `.../bencriteria`, `.../itemcriterialist`.

---

## 15. VehicleExpensesController

**Purpose**: Admin-only tracking of vehicle/transportation running costs against a `vehicle_master` fleet table and `transportation_expense` records, with an associated payment-tracking sub-table (`TransportationPayment`). Comment header attributes authorship/date: "Created by ARUL P DAS on 6/3/2020" (`Controller/VehicleExpensesController.php:38`).

**Access**: No `user_group` checks anywhere in this controller (confirmed by grep) — admin-only by menu convention, not enforced server-side.

**Flow**: Single-step CRUD with a running payments ledger, no approval workflow.
- `form()` (`VehicleExpensesController.php:56-98`) — vehicle + site pickers; on edit, looks up the selected vehicle's `rate_per_km` for prefill (`VehicleExpensesController.php:90-93`).
- `get_rate_per_km()` (`VehicleExpensesController.php:100-112`) — AJAX: returns a vehicle's per-km rate when the vehicle dropdown changes (used to auto-compute cost from distance in the form's client JS, not read in the view file itself).
- `employeeloansave()` (`VehicleExpensesController.php:115-174`, oddly-named — reused/copy-pasted action name from the loan controller pattern, not actually loan-related) — creates/updates a `transportation_expense` row (driver, purpose, start/end date+km, total km/cost, remarks); **on every save, if a `payments`/`payamount` value > 0 is present, also inserts/accumulates a row in `TransportationPayment`** (`VehicleExpensesController.php:139-145, 155-162`) with a running `balance = total_cost - payments` — i.e. partial-payment ledger tracked alongside the expense header, similar in shape to `ProjectExpensesController`'s payment sub-flow.
- `vehicleexpenselist()` (`VehicleExpensesController.php:177-236`) — datagrid feed, filterable by site/vehicle.
- `Expenses()` (`VehicleExpensesController.php:238-246`) — index/landing action populating site+vehicle dropdowns for the list page.
- `deleteEmployee()` (`VehicleExpensesController.php:249-263`) — soft delete (misnamed action, same copy-paste pattern as `employeeloansave`).

**Forms**: `View/VehicleExpenses/{form,expenses}.ctp` (each has one stale bkup: `#bkup_megha_26_09_20`).

**Notifications**: None.

**AJAX/JS endpoints**: `VehicleExpenses/get_rate_per_km`, `.../employeeloansave`, `.../vehicleexpenselist`, `.../deleteEmployee`.

---

## PaymentApprovalsController — NOT an approval hub (verified)

**Purpose confirmed**: Despite the name, `PaymentApprovalsController` (`Controller/PaymentApprovalsController.php`, 121 lines total) contains **no approval logic whatsoever**. Its only two actions are:
- `index()` (`PaymentApprovalsController.php:54-56`) — empty method body, renders whatever default view exists at `View/PaymentApprovals/index.ctp` (not read in depth; likely a static landing/menu page).
- `getPaymentFeatures()` (`PaymentApprovalsController.php:57-118`) — a pure **SaaS plan/feature-flag lookup**: resolves the company's `plan_id` from `controldb.CentralUserCredentials` (`PaymentApprovalsController.php:60-70`), then cross-references the `Features` table filtered to `feature_key='payment approvals'` against the `PlanFeature` table to return, per feature, whether it's `is_enabled` for that plan (`PaymentApprovalsController.php:78-116`). This powers a "what's included in your plan" UI (e.g. an upsell/feature-gate screen), not a workflow approval endpoint.

**Conclusion (confirms prior partial-pass finding)**: The actual approve/reject/authorize business logic for expenses lives **inside `EmployeeExpensesController::grandexpense()`** (two-level Authorize→Approve, section 7 above) and **inside `ProjectExpensesController::grandexpense()`** (single-level Approve/Reject, section 13 above) — two independent, structurally-similar-but-not-identical implementations against the same underlying `emp_expense` table, each with its own `manageexpense()`/`grandexpense()` action pair. `PaymentApprovalsController` is unrelated infrastructure (plan/feature gating) that happens to share the word "approval" in its name. No shared/central "PaymentApproval" service or table was found anywhere in this scope — `Advance`, `Employeeadvance`(salary advance), `EmployeeLoan`, and `VehicleExpenses` have **no approval workflow at all** (direct admin entry, immediately effective); only the two "expense" flows (personal and project) have any authorize/approve mechanic, and they diverge in level-count and in how Admin short-circuits the flow.

---

## Approval Workflow Pattern — consolidated summary

There is **no shared/reusable approval workflow module** in this codebase area. Each feature reimplements its own status machine inline in its own controller, against its own copy-pasted `manageexpense()`/`grandexpense()` action pair:

| Feature | Approval levels | Who is authorizer/approver | Admin short-circuit | Status field values |
|---|---|---|---|---|
| Personal Expenses (`EmployeeExpensesController`) | **Two** (Authorize → Approve) | Two separate employees chosen by the submitter at request time, resolved via DB function `leave_auth_apr_person_fn()` (reused from Leave module) | Yes — Admin (`user_group==1`, no `emp_fkey`) stamps both authorize+approve remarks/dates in one `grandexpense()` call | `Applied` → `Authorized` → `Approved` \| `Rejected` \| `Removed`/`Cancelled` (`EmployeeExpensesController.php:1867-1942`) |
| Project Expenses (`ProjectExpensesController`) | **One** (single Authorize/Approve step, field literally named `authorized_by` but functions as sole approver) | One employee chosen by the submitter at request time (no `leave_auth_apr_person_fn` reuse observed — direct dropdown) | Yes — Admin entries are auto-`Approved` at creation time in `save()`, bypassing the workflow entirely rather than short-circuiting an existing pending record | `Applied` → `Approved` \| `Rejected` (`ProjectExpensesController.php:758-805`), plus a ZWLK-specific side effect on rejection |
| Salary Advance (`EmployeeadvanceController`), Petty-cash Advance (`AdvanceController`), Employee Loan (`EmployeeLoanController`), Vehicle Expenses (`VehicleExpensesController`) | **None** | N/A — admin/HR enters directly and it's immediately effective | N/A | No status/workflow field; record is live on save |
| `PaymentApprovalsController` | N/A — not a workflow | N/A | N/A | Plan/feature-flag lookup only |

Key migration implications:
1. The "authorizer/approver" identity is **captured once, at submission time, as a fixed employee reference** (not a role or an org-chart lookup evaluated at approval time) for both expense flows — `authorized_by`/`approved_by` are FK columns on the request row itself, resolved via `leave_auth_apr_person_fn()` only to *populate the dropdown options* the submitter can choose from. If the org chart changes after submission, already-submitted requests are unaffected (INFERRED from the schema shape; not stress-tested against a live DB).
2. "Reject" is a single terminal action with **no re-submit path** found in this scope (no `status='Applied'` reset action) — once rejected, INFERRED the employee must create a brand-new request rather than revise the rejected one (not confirmed by a "resubmit" action in either controller's function list).
3. All three "list" queries (`listempexpense`, `listempexpenseverified`) are role-and-identity-scoped by raw SQL `WHERE authorized_by = cur_emp_key OR approved_by = cur_emp_key`, i.e. a person's approval queue is computed per-request rather than via a role/permission table — this is data-driven routing, not permission-driven routing, and needs to be preserved as such (e.g. as a computed "my approvals" view keyed on FK columns, not a generic RBAC check) in the Next.js rewrite.
4. No email/push notification exists anywhere in this scope when a request enters someone's queue or changes status — purely in-app polling/toast (`$.notify`) driven. Confirm with the product owner whether this is an intentional gap to close in the rewrite.

---

## 7. Reports

_This section reuses the shared DataTables UI pattern documented below before covering individual report controllers._

# Common DataTable AJAX Pattern (reference — cite this once per report, don't re-derive)

The legacy app uses jQuery DataTables (server-side processing) via two Cake components:

- `Controller/Component/DataTableComponent.php` — modern component. `initialize()` binds to `$controller->modelClass` model (Controller/Component/DataTableComponent.php:43-47). `getData($model, $columns)` (Controller/Component/DataTableComponent.php:55) reads DataTables' standard GET params (`request->query['order']`, `['search']`, `['start']`, `['length']`, `['draw']`) (Controller/Component/DataTableComponent.php:90-128), builds Cake `find('count')` / `find('all', $parameters)` calls, and returns a DataTables-1.10+ compatible JSON envelope: `{draw, recordsTotal, recordsFiltered, data}` (Controller/Component/DataTableComponent.php:166-171). Controllers set `$this->datatable = array('conditions'=>..., 'fields'=>..., ...)` before calling `$this->DataTable->getData(...)`, then `echo json_encode(...)` and exit (pattern seen across report controllers' `*_ajax`/`ajax_*` actions).
- `Controller/Component/DatatablesManagementComponent.php` — legacy/older component using DataTables <1.10 param names (`iDisplayStart`, `iDisplayLength`, `sSearch`, `bSortable_N`, `iSortCol_N`) (Controller/Component/DatatablesManagementComponent.php:35-99). `generateSQLConditionsForListing($arr_columns, $var_getRequest)` (Controller/Component/DatatablesManagementComponent.php:27) returns raw SQL fragments (`sLimit`, `sOffset`, `sOrder`, `sWhere`) that calling controllers splice into raw/custom SQL (`$this->ModelName->query(...)`) rather than Cake's ORM conditions array.

INFERRED: Which component a given report controller uses depends on when it was written; older controllers (Bkup/nimisha-era) tend to use `DatatablesManagementComponent` with raw SQL, newer ones use `DataTableComponent` with Cake finds.

**Common UI pattern across almost all report .ctp views**: a filter form (dropdowns for company/branch/department/site/month/year/date-range, populated server-side from `$this->set(...)` in `index()`/filter action) submits via GET or AJAX to an `*_ajax`/`ajax_list`/`fetch_data`-style action that returns JSON consumed by a jQuery DataTables instance rendered in the `.ctp` as an HTML `<table>` with AJAX `sAjaxSource`/`ajax.url` config. Most reports also expose a PDF and/or Excel export action (separate controller action, often named `export_pdf`, `export_excel`, `pdf`, `excel`, `csv`) invoked by a button that hits the export URL directly (full page navigation, not AJAX) with the same filter params appended as querystring.

# Reports Feature Area — UX/Behavior Report

Scope: `legacy/Controller/*Report*Controller.php` and `legacy/Controller/*Reports*Controller.php` (~40 controllers). See `_datatable_pattern.md` for the shared jQuery DataTables AJAX pattern (`DataTableComponent` vs `DatatablesManagementComponent`) — referenced below, not re-derived.

## 0. Common architecture shared by almost every "hrreports"-style controller

Nearly all report controllers (ReportsController, ReportController is the exception — see below), AttendanceReportsController, AttendanceReportsNewController, SalaryReportController, SalaryReportsController, StatutoryReportController, TaxReportController, HierarchyReportController, ArrearsReportsController, AssetsReportsController, EsiEpfReportController, EventReportsController, InteligenceReportsController, LopReportsController, MiscellaniousReportsController, ReconciliationReportController, ResighnedReportsController, SiteReportsController, StockReportController, TrackingReportsController/New, VariableReportController, statutoryReportsController, EditedReportsController) follow **the same 7-step controller/view pattern**:

1. **`hrreports()`** — landing action. Builds `$arr_reporttypes` (dictionary of report-type key => human label), often varying by `company_code` session value (multi-tenant white-labeling) and/or `user_group` (Admin=1 sees more types than Employee=2) and/or a `plan` value read from `comp_contact_info` table (basic vs full plan gates which report types show). Renders `hrreports.ctp`, a page listing report-type buttons/links.
2. **`changereporttype($type)`** — AJAX (autoRender=false), looks up `ReportCriterias` model rows (`status=1, reporttype=$type`) — the list of filter-criteria types available for that report (e.g. Department, Branch, Employee, Designation) — and renders `showreport.ctp` (the dynamic filter-builder UI/form).
3. **`addreportcriteria($type, $newindex, $str_currentcriterias)`** — AJAX, returns remaining criteria not yet added (renders `showcriteria.ctp`) so the user can add multiple filter rows.
4. **`loadcriteriaitems($index, $str_criteria)`** — AJAX, renders `loadcriteriaitems.ctp`, a criteria-value picker widget (e.g. multi-select of departments) for a given criteria model, validated via `_modelExists($modelName)` against `App::objects('model')`.
5. **`listcriteriaitems($str_criteria, $type)`** — AJAX, returns `json_encode($arr_criteriaItems)` — the actual list of selectable values (e.g. all active Departments, Branches, Employees) for a multi-select widget. Employee-branch scoping for `user_group==2` (ESS) restricts values to the logged-in employee's own branch (pattern seen repeatedly, e.g. `Controller/ReportsController.php:279-304`).
6. **`generatereport($type, $mode)`** (or `downloadHistory`/`reportAudit` variants) — dispatches to a private `generate<Type>report($mode)` method based on `$type`. `$mode` is one of `''` (view in browser as HTML `.ctp`), `'pdf'`, or `'excel'`. Also writes an audit row via `ReportAudit` model recording report type, date range, criteria, and download mode (e.g. `Controller/ReportsController.php:553-648`, `Controller/HierarchyReportController.php:252-320`).
7. **Export**: PDF via `HTML2PDF` library (`App::import('Vendor','HTML2PDF', ...html2pdf_v4.03...)`, `$html2pdf->writeHTML($view_output); $html2pdf->Output('<Name>.pdf','D')` — forces browser download) rendering the same view HTML that would show on-screen (e.g. `Controller/HierarchyReportController.php:499-506`). Excel via `PHPExcel` library (`App::import('Vendor','PHPExcel', ...)`) — manually builds cells/styles/headers/footers, writes with `PHPExcel_Writer_Excel2007`, streams via `header('Content-Type: application/vnd.ms-excel...')` + `readfile()` + `unlink()` temp file cleanup (e.g. `Controller/HierarchyReportController.php:513-699`). No CSV export seen; only PDF and XLSX.

**Access control**: no controller in this cluster does per-menu `isAuthorized`/`menu_id` checks itself — gating is purely the global `user_group` session check in `AppController::beforeFilter()` (any of 1/2 passes; anything else → Site/login, `Controller/AppController.php:39-46`) plus ad-hoc `user_group==2` branch-scoping logic embedded in each controller's criteria-loading methods (restricts ESS/Employee users to their own branch's data, never a hard block). INFERRED: menu-level `user_access`/`emp_menu` gating is likely enforced by the **menu/nav rendering** layer (not shown in these controllers) rather than in the report controllers themselves — i.e., a user_group=2 employee could reach a report URL directly if they know it even if the menu link is hidden, unless AppController has global menu-based ACL elsewhere not visible in this file set.

---

## 1. ReportsController.php / ReportController.php — canonical "Reports" hub

Two separate, non-overlapping controllers with confusingly similar names:

- **`Controller/ReportsController.php`** (plural) is the general HR-reports controller. `hrreports()` (`:75-97`) exposes report types: Employee Information, Shift Policy, Leave Policy, Holiday Group, and (newly added 2026) Salary Structure Reports. Uses steps 2-7 of the common pattern (`changereporttype :159`, `addreportcriteria :202`, `loadcriteriaitems :224`, `listcriteriaitems :242`, `reportAudit :553`, `generatereport :650`). `listcriteriaitems` (`:242-552`) has heavy per-criteria-model branching (DayTimeProcedures, Units, Departments, LeavePolicyGroup, HolidayGroup, EmployeeDetails, SalaryStructures) each with company-specific (VGFS/VSFS, GLET/ABSG) branch-scoping for ESS users. `listemployeefields()` (`:681-757`) returns a JSON list of selectable output columns for the Employee Information report, sourced from `Vendor/ReportFields/EmployeeInformationFields.php` (company `KWMT` gets an extended field set). Exports: PDF via HTML2PDF (`:1206,1790,2091,2385,2668` — one per report type: Employee/ShiftPolicy/LeavePolicy/HolidayPolicy/SalaryStructure) and Excel via PHPExcel (`:1213+`). Views: `View/Reports/hrreports.ctp`, `showreport.ctp`, `showcriteria.ctp`, `loadcriteriaitems.ctp`, `reportemployeeinformation.ctp`, `reportshiftpolicy.ctp`, `reportleavepolicy.ctp`, `reportholidaypolicy.ctp`, `reportsalarystructure.ctp` (numerous `#bkup_*` duplicate copies present — ignored per scope). File also defines dead/legacy `attendance()`, `miscellanious()`, `sallary()`, `statutory()` actions (`:99-153`) that appear to be superseded by the dedicated AttendanceReports/SalaryReport/StatutoryReport controllers — likely orphaned code paths.

- **`Controller/ReportController.php`** (singular) is a *different*, much smaller, modern controller unrelated to the criteria-builder pattern. It backs a **report catalog/menu API** for a newer UI: `getReportCategories()` (`:57-79`) returns distinct `report_list` categories from a `Features` table (datasource `controldb`, i.e. the central/control DB rather than tenant DB — confirms dual-DB architecture from project memory). `getReportByCategory()` (`:80-168`) looks up the tenant's `plan_id` from `CentralUserCredentials`, cross-references `PlanFeature.is_enabled` to mark each report feature as enabled/disabled for the company's subscription plan, and applies a hardcoded `$pathMap` (`:141-144`) that redirects certain "not_allowed_companies" (a hardcoded list of ~16 company codes at `:97-100`) from `attendanceReportsNew/hrreportsnew` to the legacy `attendanceReports/hrreports` path (and similarly for TrackingReportsNew → TrackingReports) — i.e. a feature-flag/plan-gating layer sitting in front of the report controllers, company-code-based rollback to legacy controllers for specific customers. `getArticleByFeature()` (`:268-297`) fetches help-article content per feature. All three actions are pure JSON APIs (`autoRender=false`), no PDF/Excel here — this controller is UI-chrome/catalog only, not report generation itself.

---

## 2. AttendanceReportsController.php / AttendanceReportsNewController.php

Purpose: attendance registers, overtime, check-in/out logs, regularisation, non-punched/non-attendance reports. `AttendanceReportsNewController.php` is a parallel/newer version of the same controller (near-identical function list: `hrreportsNew` vs `hrreports`, same `Overtimereport`, `generateOvertimereport`, `generatenonpunchedreport`, `generatenonattendancereport`, `Updateame`, `listpunches`, `processregisterentries`) plus an extra `getEmployeesByBranch()` (`AttendanceReportsNewController.php:7343`) — INFERRED this is the actively-developed replacement, with `Controller/ReportController.php`'s pathMap (`:141-144`) confirming certain legacy companies are explicitly routed back to the old `AttendanceReportsController` instead.

**Who can access**: `hrreports()` (`AttendanceReportsController.php:75-119`) varies report-type list by `company_code` (`GLET`/`GAAR`/`ABSG` get an extra "Non-Punched & Non-Attendance" type) and by `plan` (basic plan sees only 3 report types: Attendance Register, Detailed Attendance, Check-in/out logs; full plan sees 7 including Overtime and Regularisation).

**Report types**: Attendance Register (new/legacy variants), Detailed Attendance, Overtime Reports, Approved Overtime Reports, Employee Check-in/out logs (Dashboard), Attendance Regularisation, Non-Punched & Non-Attendance.

**Extra endpoints beyond the common pattern**: `Overtimereport()`/`generateOvertimereport()` (`:3137,3537`) — dedicated OT register generation; `generatenonpunchedreport`/`generatenonattendancereport` (`:4984,5295`) — flags employees missing punches; `Updateame()` (`:5613`) — INFERRED an attendance-record-update utility invoked from a report screen; `listpunches($emp_pkey, $month)` (`:5658`) and `processregisterentries($branchcode, $month)` (`:5759`) — AJAX drill-down/detail views. Views under `View/AttendanceReports/` (and mirrored `View/AttendanceReportsNew/`): `hrreports.ctp`/`hrreportsnew.ctp`, `dashboard.ctp`, `detailedattendance.ctp`, `overtime.ctp`, `overtimedetails.ctp`, `regularisation.ctp`, `nonpunch.ctp`, `nonattendance.ctp`, `reportsattendance.ctp`, `reportsummary.ctp`, `timeattendancereports.ctp`. Same PDF (HTML2PDF)/Excel (PHPExcel) export pattern as §0. Legacy/duplicate: `AttendanceReportsController2018-010.php`, `AttendanceReportsControllerBkup-6-12.php`, `AttendanceReportsControllerBkup.php`, `AttendanceReportsController_Bkup-2018-01-07.php` — dated backups, not analyzed further.

---

## 3. SalaryReportsController.php (plural) — main payroll/salary reporting controller

This is the largest and most heavily used salary-reporting controller (~38,000+ lines). `hrreports()` (`:75+`) exposes types including CTC Summary Report, Payroll Report with LOP, and (per grep) many more gated by company plan/code — report-type dictionary is redefined 5+ times in the file for different plan/company branches (`:95-247`). Beyond the common pattern (`changereporttype`, `addreportcriteria`, `loadcriteriaitems`, `listcriteriaitems`, `downloadHistory`, `generatereport`, `listemployeefields`), it has many bespoke generation methods for specific customer/report variants: `getAccountPeriod()` (`:1386`), `sendSliptoMail()` / `sendSliptoMailSynthite()` / `sendSliptoMailSBL()` (`:2359, 2638, 38324`) — emails salary slips as PDF attachments directly to employees, `getprodataDesc`/`getprodataDesc_branch` (`:12826,12870`), `itemcriteriaSite`/`listBankBranches` (`:21053,21062`) — bank-branch criteria for bank transfer reports, `GenerateSummaryPayrolreportPSQUARE($mode)` (`:29791`) and `generateBanktransferreportPSQUARE($type,$mode)` (`:33103`) — customer-specific ("PSQUARE") payroll summary and bank-transfer-file generation. Views in `View/SalaryReports/`: `bankreport.ctp`/`bankreport_synthite.ctp`/`bankreportnew.ctp`, `grossreport*.ctp` (several variants incl. `_kwmt`, `_non_exempted`), `payrolsummaryreports.ctp`, `payroll_ctc.ctp`, `salaryslip*.ctp` (multiple version variants — `slipfirstversion.ctp`, `slipsecondversion.ctp`, `slipthirdversion.ctp`, `sbl_salaryslip.ctp`, `synthite_salaryslip.ctp`), `lopreports.ctp`, `comparison_report.ctp`, `monthlyctc.ctp`. Same PDF/Excel export pattern. Duplicate/backup: `SalaryReportsController_nimishabackup.php`, `SalaryReportsControllerbkup_nimisha_11_5_19.php` — not analyzed.

## SalaryReportController.php (singular) — simpler CTC/payroll dashboard variant

Distinct, smaller controller (`class SalaryReportController`, `:33-100+`). `index()` (`:55-77`) offers only 2-3 report types (Payroll Summary Report, CTC Summary Report, CTC Detail Reports) — does *not* follow the hrreports/changereporttype criteria-builder pattern; instead has simple AJAX list endpoints `Branches()`, `Designation()` (`:79-100+`) returning JSON `{data:[...]}` for populating dropdowns directly (uses `DatatablesManagement` component per `_datatable_pattern.md`). Views in `View/SalaryReport/`: `index.ctp`, `salary.ctp`, `salarystructure.ctp`, `summary_payroll.ctp`, `Salaryslip.ctp`. INFERRED: this is an older or ESS-facing simplified salary dashboard, separate from the full-featured `SalaryReportsController.php`.

---

## 4. StatutoryReportController.php

Purpose: PF (EPF)/ESI/Professional Tax/TDS statutory compliance reports. `hrreports()` (`:85-151`) is heavily company-code-gated: `DEMO`/`GEDE` get 11 report types incl. `EPF_SYNTHIET`/`ESI_SYNTHIET`/`PF_UPLOAD_SYNTHIET` (Synthite-specific statement formats), `KWMT` gets a reduced 6-type set, `HRBL` its own set, `DRRC`/`DJIC`/`AGNG`/`AYRK` get a minimal 3-type set (Labour/EPF/ESI only), all others get a default 6-type set (Statutory Report, Professional Tax Summary, Professional Tax Salary, PF Summary, ESI Summary, Tax Detailed). Follows the common pattern (`changereporttype :319`, `addreportcriteria :472`, `loadcriteriaitems :493`, `listcriteriaitems :517`, `downloadHistory :919`, `generatereport :1121`, `listemployeefields :1352`, `getAccountPeriod :1386`). Views in `View/StatutoryReport/`: `12bbform.ctp`, `empepf.ctp`/`empepfnew.ctp`/`empepfsynthiet.ctp`/`empepfuploadsynthiet.ctp`, `empesi.ctp`/`empesinew.ctp`/`empesisynthiet.ctp`, `professionaltax.ctp`, `tax.ctp`, `empstatutony.ctp`. Same PDF/Excel export pattern.

Note: a separate, older/leaner **`statutoryReportsController.php`** (lowercase class `statutoryReportsController`, `public $name='Reports'`) also exists (`:33-90+`) offering only 2 report types (Edited Attendance / Detailed Edit Attendance) — this appears to be a legacy/unrelated controller despite the similar name, focused on edit-punch audit reports rather than statutory compliance; not the same feature as `StatutoryReportController.php`.

---

## 5. TaxReportController.php

Purpose: single-purpose controller for Tax TDS Report only. `hrreports()` (`:72-79`) exposes exactly one report type: `TaxTDS => 'Tax TDS Report '`. Follows the common pattern in full (`changereporttype :84`, `addreportcriteria`, `loadcriteriaitems`, `listcriteriaitems`, `reportAudit :504`, `generatereport :627`, `listemployeefields :687`). Views: `View/TaxReport/hrreports.ctp`, `showreport.ctp`, `showcriteria.ctp`, `loadcriteriaitems.ctp`, plus generic `reportemployeeinformation.ctp`/`reportleavebalance.ctp`/`reportleavesummary.ctp`/`reportshiftpolicy.ctp` (appear copy-pasted from ReportsController's view set but likely unused/dead given only one report type is exposed — INFERRED). Same PDF/Excel export via HTML2PDF/PHPExcel.

---

## 6. HierarchyReportController.php

Purpose: two reports — Employee Hierarchy (`:60-66` `arr_reporttypes`) and Leave Hierarchy — visualizing approval-hierarchy chains (superior/subordinate relationships) built from `emp_config` table rows of `type='HIERARCHY'`/`type='LAPPR'`.

**Who can access**: global `user_group` gate only. Employee/branch-scoping for `user_group==2`: for `EmployeeHierarchy`/`LeaveHierarchy` criteria, only employees present in `emp_config` with `policy_id > 0` (i.e. configured as an approver) are listed (`:201-205`); ESS users are further restricted to their own branch except for company codes `GLET`/`ABSG`/`DEMO` which use a `get_branch_code_abs_fn()` SQL function to detect head-office status (`:210-221`).

**User flow**: `hrreports` → `changereporttype($type)` (`:72-95`) loads `ReportCriterias` (typically Employee/Superior selection) → `addreportcriteria`/`loadcriteriaitems`/`listcriteriaitems` (`:101-250`) build the multi-select of employees who are configured as hierarchy approvers → `generatereport($type,$mode)` (`:322-338`) dispatches to `generateemployeehierarchyreport()` (`:376-706`) or `generateleavehierarchyreport()` (`:715-997`), both of which run a raw SQL query joining `emp_proff`, `emp_config`, `employee_info`, `emp_details`, `termination` to build a superior→subordinate table (including resignation/termination status flags), then render either `employeehierarchy.ctp`/`leavehierarchy.ctp` as HTML (default mode), a PDF (HTML2PDF, `:499-506` / `:821-826`) in landscape legal-size format, or an Excel workbook (PHPExcel, `:513-699` / `:834-990`) with a fixed 9/7-column layout (Sl No, Superior, Employee ID, Employee Name, Joining Date, Branch, Department, Designation, Termination Date, Order). `downloadHistory($type,$mode)` (`:252-320`) logs every view/PDF/Excel action to `ReportAudit`.

**No AJAX DataTables** used here (unlike most other reports) — the full result set is rendered server-side in one pass, no client-side paging/search grid.

---

## 7. Remaining controllers (lighter treatment)

All of the below follow the §0 common pattern (`hrreports`→`changereporttype`→`addreportcriteria`→`loadcriteriaitems`→`listcriteriaitems`→`generatereport`/`downloadHistory`/`reportAudit`, PDF via HTML2PDF + Excel via PHPExcel, `ReportAudit` logging) unless noted otherwise. Report-type dictionaries below are from each controller's `hrreports()`.

- **AccessDetailReportController.php** (`:54-67`) — 3 report types: Reports Audit, Forms Audit, Login Audit — i.e. system/user activity audit trail reports (who accessed/edited what). No `reportAudit`/`downloadHistory` method present (commented out at `:209` — audit-of-audits not itself logged).
- **ArrearsReportsController.php** (`:76-91`) — single report type "Arrears" (salary arrears report); has bespoke `generatearrearreport($type,$mode)` (`:437`) in addition to common pattern; gates report-type list by `plan` value.
- **AssetsReportsController.php** (`:71-78`) — single type "Asset History Report" (`History` key) tracking company asset assignment/history to employees.
- **CompanyProfileReportController.php** (`:35-100+`) — *not* the criteria-builder pattern. `index()` sets up dropdowns; `viewreport($company,$branch,$department,$designation,$bank)` (`:98+`) takes simple boolean flags (1/0) as URL params for which sections to include (Company/Branch/Department/Designation/Bank details) and renders a single consolidated company-profile report; `reportAudit(...)` (`:65-97`) logs which sections were viewed. Uses `DatatablesManagement` component.
- **ConfigReportController.php** (`:33-160+`) — utility/config-audit controller with `Branches()`, `Designation()`, `Departments()`, `Employees()` AJAX list endpoints (JSON `{data:[...]}`, not the criteria-builder JSON shape) and `Generate()` (`:136+`) which builds an "Edit Punches" audit report (who edited attendance punches) via raw joins on `emp_edit_punches`/`branches`/`emp_details`. Uses `DatatablesManagement` component — client-side DataTable grid rather than PDF/Excel-first flow (INFERRED from component choice per `_datatable_pattern.md`).
- **EditedReportsController.php** (`:76-90+`) — "Leave" report family: Leave Detailed Reports, Leave Balance Report, Leave Comp Off Report, Leave Balance Monthly Statement — company `HRBL` gets a fixed set, others vary by `plan`. Dup: `EditedReportsController_2018-10.php`.
- **EmpOtRegisterController.php** — does **not** follow the common pattern; ESS-facing overtime register: `Register($month,$emp_pkey)` (`:53`), `Approved($month,$emp_pkey)` (`:112`), `form()`/`save()` (`:136,149`) for OT request submission, `Toapproved()` (`:162`) approval action, `index/jsons/subtable/approves/Setvalue/remarks` (`:186-279`) AJAX/grid support — this is closer to a transactional OT-request workflow than a pure report.
- **EmpreportController.php** — ESS "My Reports" self-service dashboard, not the criteria-builder pattern: `salarystructure()` (`:43`), `attendancedetails()`/`refresh()` (`:79,82`), `downloads()`/`downloadexcels($loan_pkey)` (`:192,236`), `attendanceReports()` (`:365`), `employeelist()`/`employeeleavelist()` (`:452,1101`), `attendance()`/`attendanceregister()` (`:1155,1189`), `leavepolicyreport($mode)`/`holidayreport($mode)`/`leavedaysreport()`/`leavedetailsreport($mode)`/`shiftpolicyreport($mode)` (`:1197-1518`), plus GPS/site-visit tracking: `CustomerVisits()`/`loadcustomer()`/`downloadpdf()` (`:1659-1849`), `AttendanceLocation()`/`KmTravelled()`/`KmtravelledTracking()` with their own `download*` PDF actions (`:1849-2259`). This is a large, self-contained "My Info" report hub for logged-in employees (own data only, `emp_fkey` from session), separate from the Admin criteria-builder reports.
- **EsiEpfReportController.php** (`:80-97`) — 6 report types: EPF Member Registration, EPF Exit, EPF Contribution, ESI Monthly Contribution, WPS (Wages Protection System — likely GCC/Gulf statutory), EPF Upload. Has extra `epfUploadReport($from,$type,$subcat,$branch)` (`:1817`) and `generate_report($from,$type,$subcat,$branch)` (`:226`) instead of the standard `generatereport`.
- **EventReportsController.php** — single type "Events" (`:75-78` case in `changereporttype`); tracks HR event/notification reports.
- **HierarchyReportController.php** — see §6 above.
- **InteligenceReportsController.php** (`:76-90`) — single type "Intelligence and Non Compliance" (`InteligenceNonCompli`), company-code branching present but produces identical output either way — likely vestigial branching left from a removed differentiation.
- **LopReportsController.php** (`:75-84`) — single type "LOP Detailed" (Loss of Pay report).
- **MiscellaniousReportsController.php** (`:75-95+`) — grab-bag report type list (label cut off in read, company `HRBL`-specific variant present) — catch-all for reports not fitting other controllers' domains. Dups: `MiscellaniousReportsControllerBKUP.php`, `MiscellaniousReportsController_editedNimisha.php`.
- **ReceiptReportController.php** — does not follow common pattern; billing/receivables reports: `index()` lists sites/users/contacts, `printgenerateinvoicereport()` (`:54`), `generatereceiptreport()` (`:186`), `getcontactsbysite($skey)` (`:336`) — invoice/receipt generation tied to a `Site`/`Accountreceivable` model, uses `PhpExcel` helper.
- **ReconciliationReportController.php** (`:53-59`) — 2 types: Reconciliation Report, Reconciliation Report - Section — likely payroll/GL reconciliation.
- **ResighnedReportsController.php** (`:79-88`) — single type "Resignation Report" (`Resighned` key, typo preserved from source) — resigned/separated employee listing.
- **SalarySlipReportsController.php** — ESS self-service salary-slip viewer/downloader, not the criteria-builder pattern: `index()`/`Reports($id)` (`:60,68`) renders a single month's slip (date param `$id`) built from raw SQL joins across `emp_salary_slip`, `emp_details`, `emp_proff`, `attendance_register`, `branches`, `designation` (`:84-131`), with payroll-approval-state messaging ("Payroll Not Approved"/"Payroll Not Processed") if the month hasn't been finalized; company codes `DEMO`/`HRBL` get a different template (`synthite_reports.ctp`, `:134-137`). `SalarySlipdownload($id)`/`SalarySlipdownloadpdf($id)` (`:141,275`), `slipsecondversion`/`slipthirdversion` (`:382,694`) — multiple PDF template versions per company/format. Dup: `SalarySlipReportsController_nimisha.php`.
- **SiteReportsController.php** (`:76-121`) — facility-management/staffing-agency style reports gated by company (`VGFS`/`VSFS` get slightly different set incl. hiding "Billing"-type reports for `user_group==2`): Assignment Wise Reports, Employee Pay Hours Reports, Clock Wise Attendance Reports, Site Detailed Report (Actual/Standard Rate), Rota Master (legacy + "_New"), Site Rate Reports, Customer/Vendor Reports — this is a distinct site/shift-staffing report family (client billing by site/shift), separate from Attendance.
- **StatutoryReportController.php** — see §4 above.
- **StockReportController.php** (`:76-92`) — inventory/uniform-allocation report family: Stock Summary, Stock Movement, Material Request, Purchase Order, Goods Received Notes, Uniform Allocation (+ EMI variant), Stock Transfer, PO Return, Item Details, Item Rate — 11 report types, largest niche-controller type list. Dups: `StockReportControllerBkup-01.php`, `StockReportController_bkup_vanguards.php`.
- **SynthiteSalaryReportsController.php** — customer-specific (Synthite) one-off variant of salary reporting; one-line note only per scope instructions, not traced further.
- **TaxReportController.php** — see §5 above.
- **TrackingReportsController.php** (`:38-56`) — GPS/field-tracking reports: `Customervisit` (Customer Visit Detailed Report) for `user_group==2` without an add-on feature flag; Admins/users with the tracking add-on (`current_feature_id` session var) additionally see `AttendanceLocation` (Attendance-Location Report) and `KmTravelled` (distance-travelled report) — this is the first controller seen gating by a `current_feature_id`/add-on flag rather than plan/company-code alone.
- **TrackingReportsNewController.php** — parallel newer version of TrackingReportsController (same function signatures per grep: `hrreports`, `changereporttype`, `listcriteriaitems`, `downloadHistory`, `generatereport`, `listemployeefields`, plus extra `getEmployeesByBranch()` at `:507`) — mirrors the AttendanceReportsNew relationship; `Controller/ReportController.php`'s pathMap (`:143`) confirms specific legacy company codes get redirected back to the old TrackingReportsController.
- **VariableReportController.php** — class exists (`:71-88+`) but header read was truncated by unusual multi-line PHPDoc formatting; not further traced given time budget — INFERRED to follow the common pattern based on file size/shape parity with siblings (needs a follow-up pass if VariableReport becomes in-scope for migration).
- **statutoryReportsController.php** (lowercase) — see note under §4; distinct from `StatutoryReportController.php`, offers Edit-Punch audit reports only.

### Duplicate/backup files (per instructions, not traced)
`AttendanceReportsController2018-010.php`, `AttendanceReportsControllerBkup-6-12.php`, `AttendanceReportsControllerBkup.php`, `AttendanceReportsController_Bkup-2018-01-07.php`, `EditedReportsController_2018-10.php`, `MiscellaniousReportsControllerBKUP.php`, `MiscellaniousReportsController_editedNimisha.php`, `SalaryReportsController_nimishabackup.php`, `SalaryReportsControllerbkup_nimisha_11_5_19.php`, `SalarySlipReportsController_nimisha.php`, `StockReportControllerBkup-01.php`, `StockReportController_bkup_vanguards.php` — all dated/named backup or customer-edited copies of controllers already documented above; each `.ctp` directory also contains numerous `#bkup_*`/`#backup_*` view-file duplicates (e.g. `View/Reports/hrreports.ctp#bkup_bindu_09_12_2025`) which were excluded from analysis per file-naming convention.

---

## 8. Export/download mechanics summary

- **PDF**: `Vendor/html2pdf_v4.03/html2pdf.class.php` (HTML2PDF library) — controller renders the report's normal `.ctp` view to a string (`new View($this,false); $view->render(...)`), feeds that HTML into `HTML2PDF`, sets landscape/legal page size typically, and calls `->Output('<ReportName>.pdf','D')` which forces a browser download (`'D'` = download disposition). This means the PDF is visually identical to the on-screen HTML report, not a separately-designed template. Seen consistently at e.g. `Controller/HierarchyReportController.php:499-506,821-826`, `Controller/ReportsController.php:1206,1790,2091,2385,2668`.
- **Excel**: `Vendor/PHPExcel.php` (PHPExcel library, deprecated upstream but still in use here) — controller manually builds a workbook: title/subtitle merged header rows, bold column headers, per-cell writes in a loop over the result set, border styling, autosize columns, header/footer text (company name + "Downloaded By <user>"), A4 page setup, then `PHPExcel_Writer_Excel2007->save()` to a temp file on disk, streamed back via `readfile()` + `header('Content-Disposition: attachment')`, then `unlink()`s the temp file. No streaming-without-temp-file approach used.
- **No native CSV export** was found anywhere in this cluster.
- **Email delivery**: `SalaryReportsController.php` additionally supports emailing salary slips directly (`sendSliptoMail`, `sendSliptoMailSynthite`, `sendSliptoMailSBL`) rather than only browser download — INFERRED this generates the same PDF and attaches it via a mail component/library rather than exposing a download link.
- **Audit logging**: almost every controller's `generatereport`/`downloadHistory`/`reportAudit` records a row to the `ReportAudit` model capturing report type, criteria selected, date range, mode (View/PDF/Excel), user id/name — a consistent cross-cutting concern worth preserving in the Next.js migration (e.g. as a generic "report generated" event log).

---

## 8. Company / Organization Setup

# Company / Organization Setup — UX Behavior Report

Scope: `legacy/Controller/{Company,CompanyNew,CompanySetup,Branch,Department,Designation,Division,Section,Grade,Grades,GradesNew,Category,CategoryMaster,Vertical,DbConfig,Template,Uniform,Bank}Controller.php` and their `View/` folders.

## Global access gate (applies to every controller below)

All controllers in this cluster extend `AppController`. The only access check that fires before every action is `AppController::beforeFilter()`:

```
legacy/Controller/AppController.php:43-46
$user_group = $this->Session->read('user_group');
if ($user_group != 1 && $user_group != 2) {
    $this->redirect(array('controller' => 'Site', 'action' => 'login'));
}
```

- No controller in this cluster (`Company`, `CompanyNew`, `CompanySetup`, `Branch`, `Department`, `Designation`, `Division`, `Section`, `Grade`, `Grades`, `GradesNew`, `Category`, `CategoryMaster`, `Vertical`, `DbConfig`, `Template`, `Uniform`, `Bank`) references `user_access` or `menu_id` anywhere — confirmed by `grep -rn "user_access|menu_id" legacy/Controller` which returned 26 files, none from this list. **INFERRED**: fine-grained per-menu ACL for org-setup masters is enforced only at the menu/UI layer (which tab/link is rendered for the logged-in user, driven by `emp_menu`/`user_access` elsewhere), not by the controllers themselves — any authenticated session with `user_group` 1 or 2 can call these actions directly by URL (e.g. `Branch/savebranch`), there is no server-side re-check of the Admin role inside the controller methods.
- `View/Company/index.ctp:68,169` (the Company Setup hub) does gate several tabs (Grade, Notice Period, Leave/Financial Years, Banks, Division, Section) behind `<?php if($plan!='basic'){ ?>` — a **plan-based** UI gate, not a role gate. `$plan` is read from `comp_contact_info.plan` (`CompanyController::index()`, `legacy/Controller/CompanyController.php:59-62`).
- No custom routes exist for this cluster — `legacy/Config/routes.php` only defines `/`, `/pages/*`, and `/Analytics` (→ `DashboardNew::index`); everything else uses CakePHP's default `/Controller/action/param` routing.

---

## 1. CompanyController (`legacy/Controller/CompanyController.php`, 606 lines) — the real "Company Setup" hub

**Purpose**: The primary landing page for org-structure administration once a company is live. It is a tabbed hub (Settings / Branches / Departments / Designation / Grade / Notice Period / Leave-Financial Years / Banks / Division / Section) that embeds the CRUD grids of the smaller controllers described in §4 below, plus manages the company profile, statutory compliance numbers, and payroll/attendance cycle policy directly.

**Who can access it**: Reached from the main nav for `user_group == 1` (Admin/HR). `View/Company/index.ctp:1277-1290` shows the explicit branch: a "Back" link visible only when `plan != 'basic'` loads `CompanySetup/index` for `user_group == 1` and `EmployeeMenu/addon` for `user_group == 2` — i.e. Employees (group 2) are routed to their own ESS menu, not this hub, but this is a client-side JS branch, not a server-side block.

**Step-by-step flow / actions**:
- `index()` (`CompanyController.php:56-69`): sets `$plan` from `comp_contact_info.plan` via raw SQL, sets DB config on `UserCredentials`/`CompanyContactInfo`. Renders `View/Company/index.ctp`, the tab-set hub.
- `viewinfo()` (`:79-126`): read-only company profile panel loaded via `$("#infoid").load(livesite+"Company/viewinfo")` (`index.ctp:395`). Pulls `CompanyContactInfo` (logo, address, business type), `ComplianceInfo` (CIN/PAN/TAN/PF/ESI/PT numbers), and a raw SQL read of `db_config` for `attendance_format`/`attendance_date`/`payroll_type` (`:118-125`) to display the computed **Attendance Cycle** and **Payroll computation day** (rendered in `View/Company/viewinfo.ctp:141-192` with an ordinal-label helper function).
- `infoedit()` (`:127-168`): same data, but renders the editable form (`View/Company/infoedit.ctp`), reached by clicking the pencil-icon button (`viewinfo.ctp:41`, JS `editable()` at `viewinfo.ctp:197-199`).
- `contactinfo()` / `loadContactInfo()` (`:170-199`): JSON endpoints returning the current `CompanyContactInfo` row (unused by index.ctp directly in this trace — likely called by other modules).
- `savecompanysetup()` (`:320-456`): the main "Save" handler for `infoedit.ctp`'s form (`action="Company/savecompanysetup"`, `infoedit.ctp:7`). Handles:
  - Company logo file upload with manual validation: checks `$_FILES` error codes, 1,000,000-byte size cap (`:371-373`), MIME-sniffed via `finfo` restricted to jpg/png/gif (`:377-386`), saved to `files/companylogos/<company_code>/<sha1>.<ext>` (`:392-397`).
  - Upserts `CompanyContactInfo` (business name/type/nature, address, city, state, pincode, phone, email, fax, logo, logosize) — `:406-415`.
  - Upserts `ComplianceInfo` (CIN/PAN/TAN/PF/ESI/PT numbers) with **hardcoded `id = 1`** (`:429`, single-row table pattern — a form data race between two companies on the same code path could not occur since each company has its own DB, but it does mean the compliance record for a company always overwrites row id 1).
  - Updates `db_config.attendance_format` / `attendance_date` / `payroll_type` from the `attendance_cycle` (1-28 dropdown) and `salary_cycle` (T=Attendance days / M=Payroll days radio) fields (`:433-452`) — raw SQL `UPDATE ... WHERE active='Y'`.
  - Returns `{"success":true,"msg":"Information successfully saved!"}` JSON; client shows it via `$.notify(...)` (`infoedit.ctp:236-240`) and reloads `Company/viewinfo` back into `#infoid`.
- `getCompanyComplianceInfo()` / `saveCompanyComplianceInfo()` (`:458-516`): standalone JSON get/save pair for the compliance numbers only (appears to be an older/parallel path to the fields already covered by `savecompanysetup`).
- `savePolicy()` / `getPolicyInfo()` (`:518-604`): a generic key/value `PolicyInfo` store (policy_key 1-5: Finalised Date, Salary Pay, Proof Date, Yes/No, Payroll From) — **not wired into `index.ctp`'s visible tabs** in this trace; likely used by another (e.g. attendance/payroll setup) screen. INFERRED: dead/legacy from this cluster's perspective.
- `setup()` / `initialsetup()` (`:71-77`): **empty action bodies** that render `View/Company/setup.ctp` (993 lines) and `View/Company/initialsetup.ctp` (24 lines) respectively. `setup.ctp` is a modal-styled, read-only variant of the Contact Info tab (`readonly="readonly"` on every field, `View/Company/setup.ctp:47-70`) that references `$contactinfo` — **but the controller action sets no view variables**, so this would throw an undefined-variable notice/fail if rendered today. **INFERRED**: `setup()`/`initialsetup()` are dead/broken legacy actions superseded by `index()`/`viewinfo()`/`infoedit()`.

**Forms**: See `infoedit.ctp` (`legacy/View/Company/infoedit.ctp`) — required fields (`required` + `data-validation-error-msg`): Company Name, Type of Business, Address, City, State, Zip Code, Phone, Email; optional: Nature of Business, Fax, CIN/PAN/TAN/PF/ESI/PT numbers. Client validation via `jQuery.validate` (`modules: 'location, date, security, file'`, `infoedit.ctp:233`) plus native HTML5 `required`. No server-side re-validation of required fields in `savecompanysetup()` — it trusts whatever `$this->request->data` contains (classic CakePHP 2.x pattern, no explicit validation rules shown in the controller).

**Notifications**: `$.notify()` toast on save success (`infoedit.ctp:238`), styled `type:'success'`. No error-path notify wired for the logo-upload `RuntimeException`s — they are silently swallowed (`catch (RuntimeException $e) { }`, `CompanyController.php:402-405`), meaning a failed logo upload (bad MIME, >1MB, etc.) fails silently and the rest of the form still saves.

**AJAX/JS endpoints hit from `View/Company/index.ctp`**: `Company/viewinfo` (`:395`), `Branch/listunits` + `Branch/form` (`:441,450,458`), `Department/listdepartments` + `Department/form` + `Department/deleteDepartment` (`:521,532,540,564`), `Division/listdivision` + `Division/form` + `Division/deleteDivision` (`:597,606,614,637`), `Section/listsection` + `Section/form` + `Section/deleteSection` (`:675,684,692,715`), `Bank/listbanks` + `Bank/form` + `Bank/deleteBank` (`:752,761,769,783`), `FinancialYear/listfinyears` + `FinancialYear/form` + `FinancialYear/save` + `FinancialYear/deleteFinYear` (`:246,871,880,888,902`), `DbConfig/listdb` (commented-out tab, `:966` — dead code, tab markup itself is commented `:342-378`), `Designation/listDesignation` + `Designation/form` + `Designation/deleteDepartment` (`:1053,1063,1071,1096` — note the delete action is misnamed `deleteDepartment` inside `DesignationController`), `NoticePeriod/listNotice` + `/form` + `/deleteNotice` (`:1134,1142,1150,1163`), `Grades/listGrades` + `/form` + `/deleteGrade` (`:1195,1203,1211,1224`).

---

## 2. CompanyNewController (`legacy/Controller/CompanyNewController.php`, 449 lines)

**Purpose / verdict**: Byte-for-byte an **older duplicate** of `CompanyController` — same `index/initialsetup/setup/viewinfo/infoedit/contactinfo/loadContactInfo/savecompanysetup/getCompanyComplianceInfo/saveCompanyComplianceInfo/savePolicy/getPolicyInfo` methods, but **missing** the later additions found in `CompanyController` (no `Menu` model, no `plan` lookup, no attendance/payroll-cycle logic in `savecompanysetup`/`viewinfo`/`infoedit`). Its own `View/CompanyNew/*.ctp` files mirror `View/Company/*.ctp` but likewise lack the "Attendance Cycle"/"Payroll computation day" sections added later to the `Company` views.

**Who can access it**: Same `user_group` 1/2 gate (inherited `AppController::beforeFilter`). No inbound links found from `View/Company/index.ctp` or any other active view in this cluster to `CompanyNew/*` — **INFERRED dead/orphaned duplicate**, kept around from a prior refactor (possibly the "new" UI attempt that was later merged back into `Company`).

---

## 3. CompanySetupController (`legacy/Controller/CompanySetupController.php`, 200 lines) — NOT a wizard; it's a plan/feature/payment screen

Despite the name suggesting an onboarding wizard, this controller is a **subscription-plan feature gate + Razorpay payment integration**, reached via the "Back"/home button on `Company/index.ctp` (`View/Company/index.ctp:1279-1281`, `url = livesite + "CompanySetup/index"` when `user_group == 1`).

**Purpose**: Shows the company's enabled/disabled features (`feature_key = 'company'`) based on their subscription `plan_id`, and lets an admin buy/upgrade a plan through Razorpay.

**Who can access it**: `user_group == 1` per the JS branch noted above (group 2 gets `EmployeeMenu/addon` instead) — again, UI-level only, no controller-side re-check.

**Flow** (`legacy/View/CompanySetup/index.ctp`):
1. On load, `$.getJSON("CompanySetup/getCompanyFeatures", ...)` (`index.ctp:201`) → `getCompanyFeatures()` (`CompanySetupController.php:17-77`) looks up the logged-in user's `plan_id` from `CentralUserCredentials` on the **control DB** (`setDataSource('controldb')`, `:21-22`), then joins against `Features` (`feature_key='company', is_common=0`) and `PlanFeature` (`is_enabled=1`) to build a feature list with an `is_enabled` flag per feature.
2. Left column renders one pill per feature (`payroll-main-grid`), disabled-styled if not in plan (`index.ctp:210-222`). Last-clicked feature persisted in `localStorage` (`index.ctp:226-244`).
3. Clicking an **enabled** feature shows an "Active" badge, description, an "Open {title}" button (`data-route` from `Features.feature_path`) and an article list built from `Features.article` (comma-separated links) (`index.ctp:246-297`). Clicking "Open" AJAX-loads `data-route` into `#container` (`:322-329`) — i.e. this screen is the actual entry point into the Branch/Department/Grade/etc. masters when driven by plan feature routing (rather than the `Company/index` tab hub).
4. Clicking a **disabled** feature shows "Not in Plan" + an "Upgrade to Next Plan" button, which navigates to `User/profile` and scrolls to a billing tab (`#tab_3-3`) (`index.ctp:331-342`).
5. Upgrade/payment flow: `createRazorpayOrder()` (`:79-108`) creates a Razorpay order via cURL using **hardcoded test API keys** (`key_id = "rzp_test_..."`, `key_secret` in plaintext, `:81-82,119,189`) — a security smell (should be config/env, and these are test-mode keys but the pattern would carry over to live keys). `verifyPayment()` (`:109-184`) validates the HMAC-SHA256 signature, records a row in `PlanPaymentHistory` (on `controldb`), and on `status === 'captured'` runs a raw SQL `UPDATE user_credentials SET plan_id = {$plan_id_value} WHERE LOWER(user_id) = LOWER('{$lower_user_id}')` (`:154-157` — string-interpolated but `$plan_id_value` is cast `(int)` first, and `$lower_user_id` is not escaped/parameterized, a **SQL injection surface** if `login_user_id` session value were attacker-controlled, though normally it's server-set at login).

**Notifications**: none via `$.notify` in this view — purely JSON responses consumed by custom card rendering; no error toast shown to the user on payment failure paths beyond the JSON `message` field (not rendered anywhere visible in the traced JS).

**Forms**: none (fully AJAX/JS-driven card UI, no traditional `<form>`).

---

## Company Setup Wizard (multi-step) — found in `DbConfigController`, not `CompanySetupController`

The real multi-step onboarding wizard for a brand-new company lives in **`legacy/Controller/DbConfigController.php`** (653 lines) plus `legacy/View/DbConfig/*.ctp`. It is **not currently linked from any active menu or view in this cluster** — `grep -rn "DbConfig/welcome"` across `View/` and `Controller/` finds no caller, and the one tab that used to expose DB Config (`View/Company/index.ctp:342-378`) is commented out. **INFERRED**: this wizard is dormant/orphaned in the current build — either bypassed by a newer onboarding flow outside this cluster's scope (e.g. registration handled in `SiteController`) or left mid-migration. It is still fully functional code and worth tracing for the Next.js migration since it represents the intended "first-run" experience.

**Who can access it**: same blanket `user_group` 1/2 gate only; no extra role check inside `DbConfigController`.

### Step-by-step trace (each `<form>`'s `action` attribute names the controller method that both processes the current step and renders the next view, confirmed by grep of every `View/DbConfig/*.ctp` file):

1. **`DbConfig/welcome`** (`DbConfigController.php:439-441`, view `View/DbConfig/welcome.ctp`) — entry point. On load, JS does `$('#loader').load(livesite+'Company/infoedit/')` (`welcome.ctp:397-401`), embedding the same company-profile edit form described in §1 inline. The wizard's own outer form (`id="contactInfoFormSection"`, `welcome.ctp:341`) posts to `DbConfig/config`. Buttons: **"Skip Setup"** → `window.location.href = Dashboard` (abandons the wizard entirely, straight to the main app) and **"Next"** → submit (`welcome.ctp:351-352`).
2. **`DbConfig/config`** (`:82-92`) — on POST, runs raw SQL to propagate `company_code` onto `db_config`, and onto the first `branches` row (constructing `branch_code = company_code.'01'`) and the first two `fin_year` rows. No validation, no flash message. Renders `config.ctp`, whose form posts to `DbConfig/designation_departments`.
3. **`DbConfig/designation_departments`** (`:94-116`, view `designation_departments.ctp`) — lets the admin add Departments and Designations inline via AJAX (`savedepartment()` `:534-568`, `remove_dept()` `:570-592`, `save_Desig()` `:403-437`, `remove_desig()` `:498-520`), each doing a duplicate-code existence check before insert (department: `Departments.dept_code`; designation: `Designation.desig_code`) and returning `{"success":false,"msg":"...Already Exists"}` on collision. Also fetches raw MySQL lists of `department`/`designation` from a **hardcoded `mypayrol_control_db` connection with hardcoded root credentials** (`:96-97`, `'localhost','root','Localhost&*()'` — a hardcoded DB password checked into source, a serious secret-management smell to flag for migration). The page's final `<form>` (holiday-group checklist, presumably rendered alongside the dept/desig grid) posts to `DbConfig/save_holidays`.
4. **`DbConfig/save_holidays`** (`:241-276`) — reads a `checklists` array of chosen `HOLIDAY_GROUP_ID`s; for each, copies the template `holiday_group`/`holidays` rows (from the hardcoded `mypayrol_control_db`) into the company's own `holiday_group`/`holidays` tables. If `checklists` is empty it still redirects onward (no hard requirement). Always redirects to **`DbConfig/policy`**.
5. **`DbConfig/policy`** (`:278-294`, view `policy.ctp`) — lists active `working_day_time_procedures` (shift/attendance policies) from both the hardcoded control DB and the company DB (`Arr_holidays_local`). Also stamps `wizard_config SET link='DbConfig/policy', state='1'` (`:280`) — the only place `wizard_config` is written mid-flow, suggesting a resume-progress mechanism that is otherwise unused elsewhere in the traced code. Form posts to `DbConfig/save_policies`.
6. **`DbConfig/save_policies`** (`:327-341`) — no real processing shown (`checklists` check is a no-op since both branches redirect the same way); redirects to **`DbConfig/leave_heads`**.
7. **`DbConfig/leave_heads`** (`:351-355`, view `leave_heads.ctp`) — lists `salary_head_items` where `item_type='LEAVE'`; per-item toggle via AJAX `save_leave_head()` (`:358-384`, sets `value` to `'Y'`/`'N'`). Form posts to `DbConfig/salary_policy`.
8. **`DbConfig/salary_policy`** (`:386-388`) — **empty action body**, renders `salary_policy.ctp` with no data set. Form posts to `DbConfig/holidays`.
9. **`DbConfig/holidays`** (`:229-239`) — lists `holiday_group` rows (again from the hardcoded control DB) so the admin can review/expand the groups chosen in step 4 (`showholidays($id)` AJAX at `:523-532` lists individual holidays per group). Form posts to `DbConfig/emp_upload`.
10. **`DbConfig/emp_upload`** (`:390-392`) — **empty action body**, renders `emp_upload.ctp` (bulk employee upload placeholder; no upload-handling code found in this controller — **INFERRED** actual upload logic lives in a different controller such as `EmployeeAttendanceUploadController`/`DataUploaderController`, out of this cluster's scope). Form posts to `DbConfig/load_config`.
11. **`DbConfig/load_config`** (`:394-396`) — **empty action body**, renders `load_config.ctp`. Form posts to `DbConfig/emp_login`.
12. **`DbConfig/emp_login`** (`:398-400`) — **empty action body**, renders `emp_login.ctp`. This step also has a `showModalForm(livesite+'DbConfig/login_cred')` JS call (`emp_login.ctp:376`), opening the credentials-setup modal (**`DbConfig/login_cred`**, `:594-596`, view `login_cred.ctp`) which posts to **`DbConfig/savecompletess`** (`login_cred.ctp:1`). `savecompletess()` (`:443-496`) bulk-sets initial passwords for every `UserCredentials` row with an empty password (`Security::hash()`, `reset_login_flag='Y'`), and mirrors credentials into `MobileUserCredentials` with `punchtype='M'`. **Note**: contains stray `debug($arr_form_data); debug($arr_mobile_data);` calls left in production code (`:484-485`) — these would dump data into the response/log in a live CakePHP debug-enabled environment. Main step's own "Next" form posts to `DbConfig/completed_setup`.
13. **`DbConfig/completed_setup`** (`:598-600`) — **empty action body**, renders `completed_setup.ctp`, the wizard's final screen. Also offers the `login_cred` modal again (`completed_setup.ctp:393`). Its form posts simply to app root (`index`, `completed_setup.ctp:335`), returning the user to the main application (effectively ending the wizard by navigating away, not by explicitly calling a "complete" endpoint in this path).
14. **`DbConfig/SetupComplete`** (`:343-349`) — a separate action (not directly wired to any traced form submit — **INFERRED** triggered via a JS call not captured in the grep, possibly a "Finish" button variant) that stamps `wizard_config SET state='1'` and redirects to `Dashboard/index`.

**Every step** (except `leave_heads.ctp`) offers a **"Skip Setup"** button that immediately navigates to `Dashboard` (`window.location.href = livesite + 'Dashboard'`), abandoning the rest of the wizard with no explicit "you skipped setup" state recorded (only `SetupComplete()`/`policy()` write to `wizard_config`, and skip doesn't call either).

**Server-side validation**: minimal to none across all 13 steps — most POST handlers either do a single duplicate-key existence check (departments/designations) or perform unconditional raw SQL writes with no field validation, and several action bodies are entirely empty (steps 8, 10, 11, 12, 13 just render their view). No CSRF-token handling visible in any traced form. No file upload handling implemented despite `emp_upload.ctp`'s name (placeholder only, in this controller).

**Secrets/security flags worth carrying into the rewrite**:
- Hardcoded MySQL credentials `mysql_connect('localhost','root','Localhost&*()')` appear in `designation_departments()` (`:96`), `holidays_s()` (`:218`), `holidays()` (`:230`), `save_holidays()` (`:246`), `policy()` (`:282`), `show_policiess()` (`:297`), and elsewhere in this file — repeated hardcoded root DB password.
- `AppController.php:54` similarly hardcodes a control-DB credential (`'mpm_cntrl_usr','MyPyR01@Cntr1#LB'`) using the legacy `mysql_*` API (not the CakePHP `ConnectionManager`), and does a raw string-concatenated query `'SELECT * FROM central_control WHERE control_pkey = ' . $company_key` (`:56`) — SQL injection surface if `company_key` session value were ever attacker-influenced.

---

## 4. Small CRUD masters (Branch, Department, Designation, Division, Section, Grades, Category, Vertical, Bank) — one shared pattern

All eight controllers below follow an **identical CakePHP 2.x pattern**, embedded as datagrid tabs inside `View/Company/index.ctp` (see the AJAX endpoint list at the end of §1) using the jQuery EasyUI `datagrid` widget with a New/Edit/Remove toolbar:

1. `index()` — layout-less, sets the model's `useDbConfig` to the session's `ds` (per-company connection alias set in `AppController::beforeFilter`, `AppController.php:76`). Rarely rendered directly; the grid lives inside `Company/index.ctp` tabs instead.
2. `form($id=0)` — layout `null`, returns a small modal form partial. If `id` provided, loads the existing record; else returns blank defaults. Opened via `showModalForm(livesite+'X/form')` or `...?id=<row.id>` from the grid toolbar's "New"/"Edit" buttons.
3. `save<Entity>()` — `autoRender=false`; reads `$this->request->data`, runs a **duplicate-name and duplicate-code existence check** (`check<entity>exists`/`check<entity>codeexists`, excluding the current row's own id) before saving; returns `{"success":true/false,"msg":"..."}` JSON. No field-level validation beyond the uniqueness checks (no length/format checks in the controller — relies entirely on whatever HTML5 `required`/pattern attributes exist in the (mostly minimal) grid modal forms, not deeply traced here since these are simple 2-3 field forms).
4. `list<entities>()` — `autoRender=false`; paginated (`rows`/`page`/`sort`/`order` request params), filters `status=1`, returns `{"rows":[...],"total":N}` for the EasyUI datagrid.
5. `delete<Entity>()` — `autoRender=false`; soft-deletes (`status=0`, via `updateAll`) rows whose `id` is in a comma-separated `ids` request param; returns `{"success":true,"msg":"Record(s) deleted successfully."}`.

**Notifications**: consistently via `$.notify(response.msg, {type:'success', allow_dismiss:true})` on delete (`Company/index.ctp` toolbar handlers, e.g. `:571-579` for Department) — client-side `confirm("Are you sure want to delete ")` browser dialog gates every delete click (no in-app confirm modal).

### Per-entity specifics

- **Branch** (`legacy/Controller/BranchController.php`, 305 lines; views `View/Branch/{form,branches,newbranch}.ctp`): the richest of this group — model is **`Units`**, table `branches`. `savebranch()` (`:114-197`) additionally auto-generates a `branch_code` from the first 3 chars of the branch name + `strtotime("now")` on create (`:128-132`), inserts a mirror row into `mypayrol_control_db.company_branches` on the control DB (`:154-163`, only if not already present), and creates/updates **two `fin_year` rows per branch** — one Financial Year (`vattr1='1'`) and one Leave Year (`vattr1='0'`) — from `finstartdate/finenddate/leavestartdate/leaveenddate` form fields (`:164-180`). `listunits()` (`:199-265`) joins both `fin_year` rows via LEFT JOIN aliases `Fin_year`/`Leave_year` filtered to `is_current_finyear='Y'` and `Year_status='OPEN'` to show each branch's active FY/Leave-Year date ranges in the grid (columns `LeaveStart/LeaveEnd/FinStart/FinEnd`, `Company/index.ctp:514-517`). The grid toolbar's "Remove" button is **commented out** in `Company/index.ctp:464-504` (branches can only be soft-deleted via a still-live `Branch/deleteBranches` endpoint, but no UI wires to it — **INFERRED** branch deletion was intentionally disabled from the UI, perhaps because branches cascade into fin_year/payroll data).
- **Department** (`DepartmentController.php`, 211 lines; table `department`, model `Departments`): standard pattern; `checkdepartmentexists`/`checkdepartmentcodeexists` dedupe on name/code.
- **Designation** (`DesignationController.php`, 221 lines; table `designation`, model `Designation`): `form()` additionally exposes the full list of existing `desig_code`s (`:84-89`) to the view, presumably for client-side duplicate warning before submit. Delete action is named `deleteDepartment()` (`:168`) — a copy/paste artifact, not a bug in routing since it's a distinct method on a distinct controller, but worth flagging as a naming inconsistency for the rewrite's endpoint map.
- **Division** (`DivisionController.php`, 234 lines; table `division`) and **Section** (`SectionController.php`, 238 lines; table `section`): near-identical to Department, but their delete actions (`deleteDivision`, `deleteSection`) **block deletion** if any `EmployeeProfessionalDetails` row references the division/section (`emp_vertical` / `emp_sep_priv` columns respectively), returning `{"danger":true,"msg":"Employees allocated under the selected division/section"}` instead of deleting (`DivisionController.php:154-193`, `SectionController.php:158-196`). The client shows this via `$.notify(...,{type:'danger'})` (`Company/index.ctp:646-656` and `:724-734`) rather than the plain success toast used elsewhere — a distinct guarded-delete UX worth preserving in the rewrite.
- **Grades — three controllers, only one wired up**:
  - **`GradesController`** (`GradesController.php`, 259 lines; table `grade`, model `Grades`) is the **canonical, live** implementation — confirmed because `Company/index.ctp:1195-1266` calls `Grades/listGrades`, `Grades/form`, `Grades/deleteGrade` exactly matching this controller's methods. Its `deleteGrade()` similarly blocks deletion if `EmployeeProfessionalDetails.emp_grade` references the grade (`GradesController.php:194-237`), notifying `danger` vs `success` (view handles both branches, `Company/index.ctp:1233-1245`). `saveGrade()` uses raw SQL for update and `->save()` for insert (`:136-192`), with separate duplicate checks for grade code (blocks only on create) and grade name (blocks on both create and update).
  - **`GradeController`** (`GradeController.php`, 267 lines, `$name='Grade'`) — despite its name and `$uses` including `Grades`, its actual methods (`Hoildaycalender`, `eventsCalender`, `eventsCalenderdate`, `insert`, ...) are a **holiday-calendar module** (confirmed by `View/Grade/{Hoildaycalender,holidayreport}.ctp` — no grade-CRUD views exist under `View/Grade/`). **This is dead/mislabeled code with respect to Grade management** — do not port its grade-sounding name into the new app's grade feature; it belongs with the Holiday/Calendar feature area instead.
  - **`GradesNewController`** (`GradesNewController.php`, 275 lines) — a duplicate of `GradesController`'s pattern; **no inbound references found** anywhere in `View/` or `Controller/` (grep for `GradesNew/` returned nothing) — **INFERRED dead/orphaned duplicate**, same situation as `CompanyNewController`.
- **Category** (`CategoryController.php`, 255 lines; table `category`, model `Category`) — mirrors the `Grades` pattern almost exactly (method names `listGrades()`/`form($category_pkey)` are literally copy-pasted from `GradesController`, comment even reads `"GRADE IS CREATED BY..."` at `CategoryController.php:56`). **Not visibly wired into `Company/index.ctp`'s tab set** in this trace — no `Category/` AJAX calls found in `index.ctp`. **INFERRED**: reached from a different screen outside this cluster's scope (possibly an Asset/Uniform-adjacent categorization feature), or currently orphaned.
- **CategoryMaster** (`CategoryMasterController.php`, 187 lines; table `category_master`, model `CategoryMaster`) — a **separate, unrelated table** from `Category`/`category`. Methods: `category($category_pkey)` (add/edit form), `categoryfilter()` (autocomplete-style JSON list with an "ALL" pseudo-option, used for a Select2-style dropdown filter elsewhere), `delete()`, `save()`. **INFERRED**: this is a generic lookup/tagging master used by another module (e.g. Asset or Uniform item categorization) rather than the org-structure "Category" concept — keep the two `Category*` concepts separate in the new schema.
- **Vertical** (`VerticalController.php`, 149 lines; table `verticals`, model `Verticals`) — standard CRUD shape (`newVertical`/`saveVertical`/`listVerticals`/`deleteVertical`), but **`View/Vertical/` does not exist as a directory** and no view anywhere references `Vertical/newVertical` or `Vertical/listVerticals` (confirmed via grep). **This controller currently has no UI entry point — fully orphaned/dead in the traced codebase.**
- **Bank** (`BankController.php`, 191 lines; table `banks`(?)/model `Banks`) — standard pattern, wired to `Company/index.ctp`'s "Banks" tab (`:751-813`). `index()` contains a stray `debug($this->CompanyContactInfo->find("all"))` call (`BankController.php:59`) — leftover debug output that would leak into the page in a debug-enabled environment.

---

## 5. TemplateController (`legacy/Controller/TemplateController.php`, 134 lines) — dead duplicate of Bank

`TemplateController` sets `public $name = 'Bank'` (`:40`) and its four methods (`index`, `newbank`, `savebank`, `listbanks`) are a verbatim, slightly-earlier copy of `BankController`'s logic (down to the `debug(...)` call at `:58`, and a `listbanks()` that instead calls a generic `DatatablesManagement->fetchData('Banks', $_GET, $columns)` helper rather than the paginated raw-SQL style `BankController::listbanks()` uses). **No `View/Template/` directory exists**, and no view references `Template/`. **INFERRED**: entirely dead/orphaned — likely an early scaffold for a "Template" feature that was never built out, reusing Bank's code as a copy-paste starting point and never renamed.

---

## 6. UniformController (`legacy/Controller/UniformController.php`, 1113 lines) + `UniformController_bkup.php` (497 lines)

**Purpose**: Out of this cluster's core "org setup" scope but included per the task list — this is an **employee uniform/asset issuance and inventory module** (item master, purchase orders, stock allocation to employees, returns, EMI-style deduction tracking), not an org-structure master. Key actions: `master()` (item master CRUD), `lists_purchase()`/`form_purchase()`/`save_Purchase()` (purchase orders), `allocate()`/`allocate_form()`/`allocate_emp()`/`allocate_save()` (issuing uniform items to employees), `returns()`/`return_item()` (returns), `view_emi()`/`downloadexcels()` (EMI-style cost recovery tracking and Excel export). It reuses many of the same models as `CompanySetupController` (`Grades`, `Verticals`, `Departments`, `Designation`, `TaxHead`, `EmployeeCTC`) via `MasterdataManagement` component (`UniformController.php:51-52`), suggesting shared org-structure lookups (branch/department/designation dropdowns) feed into the allocation forms.

**`UniformController_bkup.php`** (497 lines, `App::uses` header intact) is a straightforward earlier-snapshot backup of this controller (smaller method set, e.g. no `deleteEmppurchase`/`getendmonth`), kept in the `Controller/` folder itself rather than a backup location — **dead file, do not port**, same treatment as the many `#bkup_*`/`_bkup*` `.ctp` files found throughout `View/` in this cluster (e.g. `View/Company/index.ctp#bkup_bindu_10_12_2025`, `View/Uniform/allocate.ctp#bkup_amal_12_03_2020`, etc. — none of these are ever included/rendered by CakePHP's naming convention, they are purely stray editor-saved backups sitting next to the live `.ctp` files).

---

## 7. DbConfigController — access-control summary (super-admin vs regular admin)

Per the task's specific question: **`DbConfigController` has no additional access restriction beyond the standard `user_group` 1/2 gate** — it is not "super-admin only" in code; it is gated only by the fact that its wizard is not currently linked from any active menu (see the Wizard section above). Its non-wizard leftover action `listdb()` (`:602-634`) — a paginated list of all `db_config` rows — was, per the commented-out tab in `Company/index.ctp:342-378`, once exposed to the same `user_group==1` Company Setup hub as every other tab; nothing in the code distinguishes a "platform super-admin" role from a regular company Admin. **INFERRED**: any super-admin-vs-company-admin distinction, if it exists in this product, is enforced elsewhere (e.g. in `SiteController::loginWithCentral`, out of this cluster's scope) rather than inside `DbConfigController` itself.

---

## Summary table: canonical vs dead/duplicate controllers in this cluster

| Feature | Canonical / live | Dead / duplicate / orphaned |
|---|---|---|
| Company profile & compliance | `CompanyController` | `CompanyNewController` (older duplicate, no inbound links) |
| Plan/feature gating + payments | `CompanySetupController` | — |
| First-run onboarding wizard | `DbConfigController` (functional but currently unlinked from any menu) | — |
| Grade master | `GradesController` (wired to `Company/index.ctp`) | `GradeController` (actually a Holiday-calendar controller, misnamed), `GradesNewController` (no inbound links) |
| Bank master | `BankController` (wired to `Company/index.ctp`) | `TemplateController` (verbatim duplicate, no views, no links) |
| Category | `CategoryController` (org-structure `category` table, not wired into any traced view) | `CategoryMasterController` (separate `category_master` table/purpose — not a duplicate, just a different concept sharing the "category" name) |
| Vertical | `VerticalController` exists but has **no view directory and no inbound links** — fully orphaned | — |
| Branch/Department/Designation/Division/Section | Each has exactly one live controller, all wired into `Company/index.ctp` tabs | — |
| Uniform | `UniformController` | `UniformController_bkup.php` (stale backup file) |

## Files referenced

- `D:\Projects\RIZOMigration\legacy\Controller\AppController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\CompanyController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\CompanyNewController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\CompanySetupController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\BranchController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\DepartmentController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\DesignationController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\DivisionController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\SectionController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\GradeController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\GradesController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\GradesNewController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\CategoryController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\CategoryMasterController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\VerticalController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\DbConfigController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\TemplateController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\UniformController.php`
- `D:\Projects\RIZOMigration\legacy\Controller\UniformController_bkup.php`
- `D:\Projects\RIZOMigration\legacy\Controller\BankController.php`
- `D:\Projects\RIZOMigration\legacy\View\Company\{index,viewinfo,infoedit,setup,initialsetup}.ctp`
- `D:\Projects\RIZOMigration\legacy\View\CompanySetup\index.ctp`
- `D:\Projects\RIZOMigration\legacy\View\DbConfig\{welcome,config,designation_departments,policy,leave_heads,salary_policy,holidays,emp_upload,load_config,emp_login,login_cred,completed_setup}.ctp`
- `D:\Projects\RIZOMigration\legacy\Config\routes.php`

---

## 9. Site / Field Work & Project Management

# Site / Field Work / Project Management — Legacy Behavior Report

Scope: `Controller/Site*`, `Controller/FieldSurvey*`, `Controller/Project*`, `Controller/Material*`, `Controller/GatePassController.php`, `Controller/OutPassController.php`, `Controller/Device*`.

All controllers `extends AppController` (except `SiteController`, which `extends LoginAppController`). Per `Controller/AppController.php:39-46`, every request in this area requires `Session::read('user_group')` to be `1` (Admin/HR) or `2` (Employee/ESS); anything else redirects to `Site/login`. Within that gate, most controllers additionally branch on `user_group == 2` to scope data to the logged-in employee's own site/branch/store (pattern repeated near-verbatim across nearly every controller in this area — see per-controller notes).

**No controller in this scope calls the `Email` component, an SMS/notification API, or anything resembling a queue/push mechanism** (`grep -c "sendEmail\|->Email\|smsSend\|sendSMS\|Notification"` returned `0` for all 15 controllers). "Notifications" in this feature area are exclusively client-side JS toasts (`$.notify(...)`, seen in `View/GatePass/upload_form.ctp:341-359`, `View/OutPass/upload_form.ctp`) or JSON `msg`/`message` fields returned from AJAX endpoints and displayed by the calling view's JS. This is a notable gap vs. other HR modules (e.g. leave approval) that do email — worth flagging for the Next.js rebuild if stakeholders expect approval notifications to reach anyone.

**api/v1 is not a real REST API and has no overlap with this feature area.** `api/v1/index.php:1-31` is a nearly-empty Slim Framework bootstrap (`require vendor/autoload.php`, then `settings.php`/`dependencies.php`/`middleware.php`/`routes.php`). There is exactly one PHP file under `api/v1/` (`find api -iname "*.php"` → 1 result); the directory is otherwise full of uploaded photo attachments (device/site images) and vendor scaffolding (`composer.json`, `README.md`). A `grep -i "site|field|project|material|gatepass|outpass|device" api/v1/index.php` returned nothing. **All AJAX in this feature area hits the same CakePHP controllers described below** — there is no separate mobile API layer for field-worker features despite the task's expectation that site-attendance/gate-pass/material-request might be API-driven.

`Config/routes.php` has no custom routes for any controller in this scope (only CakePHP's default `/:controller/:action/*` routing applies) — confirmed by `grep -n "Site|Field|Project|Material|GatePass|OutPass|Device" Config/routes.php` returning only an unrelated doc-comment line.

---

## Architectural note: shared `site` table across three controllers

`SiteAttendanceController`, `SiteAttendanceApplyController`, and `ProjectController` all read/write the **same underlying tables** — `site` (model `SiteMaster`), `site_transactions`, `site_history` — via near-identical `saveSite()`/`saveProject()` methods (`Controller/SiteAttendanceController.php:611-726`, `Controller/ProjectController.php:528-567`). `ProjectController::saveProject()` maps UI fields like `project_id`, `project_name`, `work_details`, `date_commencement_wo`, `amountutilised` onto the *same* `site.site_id`, `site.site_name`, `site.work_details`, `site.expected_starting_date`, `site.allocated_fund` columns (`Controller/ProjectController.php:540-560`). In other words, **"Project" is the same site-master entity as "Site Attendance," presented through a different UI/menu with an added `project_activity` work-log table** (`activity_form`/`activity_save`, `Controller/ProjectController.php:77-133`). `SiteWorkController`/`FieldSurveyController`, by contrast, operate on a genuinely separate table, `efsr_site` (EFSR = "Employee Field Survey Report"), via model `SiteWork` (`Controller/SiteWorkController.php:78`, `:133`, `:676-712`) — a distinct site master for field-survey/equipment-ticket work, not to be confused with the `site` table.

This is a critical finding for the Next.js data model: **do not model "Project" and "Site" as separate entities** — they are the same table with two front-ends layered over it, while "Field Survey Site" (`efsr_site`) is a third, independent site concept.

---

## Controller/SiteController.php — login-only, no job-site CRUD

`SiteController` (`Controller/SiteController.php:37`) `extends LoginAppController` and its 25 actions are exclusively authentication/session flows: `login` (`:268`), `logout` (`:521`), `passwordreset`/`sendtoken`/`reset`/`resetadmin`/`forgot`/`forgotadmin` (`:579-1041`), `register`/`setup` (`:1248-1409`), `companies_list`/`loginWithCentral` (`:196-268`), and audit logging (`employeeLoginAudit`, `adminLoginAudit`, `logoutAudit`, `:113-167`, `:481-521`). **There is no site/company-entity CRUD anywhere in this controller** — confirmed by `grep -n "function " Controller/SiteController.php` showing only auth-related method names. Its views (`View/Site/login.ctp`, `forgot.ctp`, `registration.ctp`, `passwordreset.ctp`, `success.ctp`, `approval.ctp`) are all login/onboarding screens, not job-site management. Job-site CRUD instead lives in `SiteAttendanceController::form/saveSite` (site master) and `SiteAttendanceApplyController` (the ESS "apply for site change" variant) — see below.

---

## Controller/SiteAttendanceController.php

### Purpose
Admin-facing master for physical job **sites** (`site` table) and their staffing shifts (`site_transactions`), plus daily **punch-in/out attendance** (`site_attendance`) recording, shift open/close, and per-user menu access provisioning (`user_access`/`emp_menu`) — this controller is overloaded with both site-master CRUD and a full duplicate of the app's menu-permission admin UI.

### Who can access it
Gated by `AppController::beforeFilter` (`user_group` 1 or 2). Within the controller, `user_group == 2` (Employee) branches scope results to sites the employee manages or has been granted via `access_site` (`Controller/SiteAttendanceController.php:94-114` in `filtersite()`, `:269-289` in `lists()`, `:857-877` in `pnch()`, `:968-973` in `add_site()`, `:1159-1167` in `data_site()`). No `menu_id`/`user_access` gate is enforced at the controller-action level (no `isAuthorized` override) — access to individual actions is enforced only client-side by which menu links are rendered, driven by the same `user_access`/`emp_menu` tables this controller itself maintains (`listuseraccess`, `saveuseraccess`, `:432-495`, `:795-831`).

### Step-by-step user flow
1. **Site list** — `index()` (`:54-78`) renders `View/SiteAttendance/index.ctp`; the grid is populated via AJAX to `lists()` (`:241-321`), a paginated/sortable query joining `site` + `site_transactions` with employee branch filtering baked in.
2. **Add/Edit Site** — clicking Add opens `View/SiteAttendance/form.ctp` via `form($site_pkey)` (`:135-169`), which also loads `form2($siteid)` (`:171-188`) for the shift sub-form (`View/SiteAttendance/form2.ctp`) and `View/SiteAttendance/add_site.ctp`. Submitting posts to `saveSite()` (`:611-726`), which loops multiple shift rows (`TdDayTime[]`, `TdDesg[]`, `TdCount[]`, `TdSRate[]`, `Rate[]`/`Rate28..31[]`, `StDE[]`/`EtDE[]`) and upserts both `site` and `site_transactions`, writing an audit copy to `site_history` (`:696-697`, `:722-723`). Company-code-specific branching (`GLET`/`ABSG`/`DEMO`/`SCRT`) switches between a single `eratess` rate field and four day-count-tiered rate fields `eratess_28..31` (`:677-686`).
3. **Daily punching** — `pnch()` (`:852-882`) renders `View/SiteAttendance/pnch.ctp`, a shift/date picker. Selecting a shift/date calls `get_shift()` (`:884-933`) then `load_sites()` (`:935-957`) and `add_site()` (`:959-988`) to list eligible employees for check-in, rendering `View/SiteAttendance/load_sites.ctp` / `add_site.ctp`. Marking a punch posts to `mark_attendance()` (`:1324-1473`), which calls a stored function `mark_site_attendance_out_fn(...)` (`:1377`) and updates `site_attendance.status` (1=checked-in, 2=checked-out, 3=shift-closed, 4=shift-reopened-pending).
4. **Shift close/open** — `View/SiteAttendance/close_shift.ctp` drives `shift_closure()` (`:991-1042`, requires no open/half-punched records, else returns *"Can't close shift. Please check all punchings."*) and `shift_open()` (`:1044-1085`, reverts closed rows and deletes any status-4 placeholder rows).
5. **Activate/deactivate a punch** — `mark_activestatus`/`mark_deactivestatus` (`:1266-1320`) toggle `site_attendance.active` via raw SQL, returning `"Punching disabled successfully."` / `"Punching enabled successfully."`.
6. **Site allocation to employees** — `site_allocate()`/`save_allocate()`/`remove_allocate()` (`:1619-1706`) manage `access_site` rows granting an employee visibility into a site beyond their own managed sites; view `View/SiteAttendance/site_allocate.ctp` posts via AJAX to `SiteAttendance/save_allocate` and `SiteAttendance/remove_allocate` (confirmed via `grep` of `View/SiteAttendance` for `url:`).
7. **Menu/user-access admin** (unrelated to sites, but embedded in this controller) — `insec()`, `save()`, `addDefault()`, `resetDefault()`, `deletemens()`, `listuseraccess()`, `saveuseraccess()`, `deleteuser()`, `Employee()`, `admin()`, `get()` (`:224-831`) duplicate the app's per-employee menu permission editor against `user_access`/`emp_menu`.

### Forms
- **Site form** (`View/SiteAttendance/form.ctp`): fields include site name/ID, branch, latitude/longitude, address, customer name/contact, payment mode, `min_days_before` (min days before which bulk site-attendance can be edited, comment `Controller/SiteAttendanceController.php:645`), plus one-or-more shift rows (day/time slot, designation, headcount, standard rate, per-day-count expense rates). Client-side: HTML5 `required` attributes only (`grep required View/SiteAttendance/form.ctp` → 3 hits, no jQuery-validate). Server-side: `check_start_date()`/`check_end_date()` (`:190-222`) block shift-date edits that would orphan existing `site_attendance`/`emp_site_detail_timeattandance` rows, returning `"Cannot set this date as Start Date. Data exist in the previous few days."` / the End-Date equivalent. `shift_delete_check()` (`:593-609`) blocks deleting a shift with existing punch data: *"This shift cannot be delete... Please close the shift by edit End Date as current date."*
- **Punch form**: in/out time fields; server computes next-day rollover for out-time (`:1365-1376`) and differentiates save-by user_group==2 (self-service employee punch, stamped with `login_user_id`) vs admin punch (stamped `"Admin"`, `:1387-1396`).

### Notifications
None (email/SMS). All feedback is a JSON `message`/`msg` string rendered client-side (e.g. `"Shift closed successfully"` `:1030`, `"Shift opened successfully."` `:1077`, `"Punch out successfully completed"` `:1386`).

### AJAX/JS endpoints hit from these views
`SiteAttendance/filtersite`, `/form2`, `/check_start_date`, `/check_end_date`, `/lists`, `/save` (menu), `/saveSite`, `/get_incompleteDate`, `/pnch`, `/get_shift`, `/load_sites`, `/add_site`, `/shift_closure`, `/shift_open`, `/close_shift`, `/data_site`, `/mark_activestatus`, `/mark_deactivestatus`, `/mark_attendance`, `/update_attendance`, `/site_allocate`, `/save_allocate` (`View/SiteAttendance` form action + JS, `Controller/SiteAttendanceController.php` action list), `/remove_allocate`, `/load_closed_sites`, `/data_edit_site`. No `api/v1` overlap (see top note).

---

## Controller/SiteAttendanceApplyController.php

### Purpose
Employee-self-service ("apply") variant of `SiteAttendanceController` — it duplicates essentially the entire controller (same function names/line-for-line logic for `filtersite`, `form`, `lists`, `save`, `mark_attendance`, `site_allocate`, etc. — confirmed by diffing `grep -n "function " ` output of both controllers side by side) but adds a **change-approval workflow** on top: any add/edit to a site or its shifts goes into a pending-approval queue instead of writing directly, and an Admin must approve or reject it before it takes effect.

### Who can access it
Same `AppController` gate. `user_group == 2` branch scoping repeats throughout (`Controller/SiteAttendanceApplyController.php:96`, `:267`, etc.). The approval-review screens (`approval()` `:2284`, `approvallists()` `:2310`, `viewapproval()` `:2360`, `saveApproved()` `:2550`) have no additional `user_group == 1`-only check in code — access is again only menu-link-driven, not enforced server-side.

### Step-by-step user flow
1. Employee edits a site/shift through the same form UI as `SiteAttendanceController` (`View/SiteAttendanceApply/form.ctp`, `form2.ctp`), but submission posts to `saveSiteApply()` (`:1975-2282`) instead of `saveSite`.
2. `saveSiteApply()` diffs the submitted values against the existing `site`/`site_transactions` row: for a brand-new site it inserts immediately with `site.status = 2` (pending) (`:2068-2069`) and logs every field into `site_master_approval` + `site_master_approval_details` (`:2084-2124`) with `type = 'new_site'`; for an edit to an existing site it does **not** write to `site` at all — it only logs the changed fields to `site_master_approval_details` with `old_value`/`new_value` pairs (`:2023-2063`), leaving the live record untouched until approval. New/changed/deleted shifts are logged the same way via `site_history` + `site_master_approval` with `type = 'shift'` (`:2161-2278`).
3. **Approval queue** — `approval()` (`:2284-2308`) renders `View/SiteAttendanceApply/approval.ctp`; grid populated by `approvallists()` (`:2310-2358`), filterable by status (0=all,1=pending,2=approved,3=rejected) and site.
4. **Review & decide** — `viewapproval($sma_pkey, $status)` (`:2360-...`) renders `View/SiteAttendanceApply/viewapproval.ctp`, showing the diff (old vs new value per field) sourced from `site_master_approval_details`. Approve/Reject posts to `saveApproved($sma_pkey, $site_pkey, $approve)` (`:2550-2860+`, form action `SiteAttendanceApply/saveApproved` per `grep` of the view). On approve, it replays each logged field change with a dynamic `UPDATE site SET $fieldname = '$new_value' WHERE site_pkey = ...` (`:2634-2639`) or `UPDATE site_transactions SET $fieldname = ...` (`:2589-2595`) — **field names are interpolated directly from `site_master_approval_details.fieldname`, i.e. built from data the applying employee's earlier request populated**, then marks the approval row `status='approved'` with `remarks` (from an optional textarea) and `modified_by`. New shifts pending in `site_history` get promoted into `site_transactions` (`:2664-2721`); deleted shifts are removed similarly (from `:2742` onward).
5. `confirmation_modal()` (`:3011-...`) renders `View/SiteAttendanceApply/confirmation_modal.ctp`, a lightweight confirm-before-approve/reject dialog; `notify.ctp` is a client-side toast partial.

### Forms
Same fields/validation as `SiteAttendanceController::form` (see above) plus a **remarks** textarea on the approval decision (`viewapproval.ctp`/`saveApproved($sma_pkey,...,$remarks)`). No explicit required-field markers found beyond the shared `form.ctp`.

### Notifications
None (email/SMS) — same finding as `SiteAttendanceController`. The approval outcome is only visible by the requester re-opening `approvallists`; there is no push/email telling the employee their change was approved or rejected. **This is a strong candidate for a Next.js improvement** (in-app notification badge or email) but is explicitly not present in the legacy behavior.

### AJAX/JS endpoints hit from these views
All the same endpoint family as `SiteAttendanceController` under the `SiteAttendanceApply/` controller prefix, plus the approval-specific ones: `SiteAttendanceApply/saveSiteApply`, `/approval`, `/approvallists`, `/viewapproval`, `/saveApproved` (form action confirmed in `View/SiteAttendanceApply` via `grep action=`), `/confirmation_modal`.

---

## Controller/SiteAttendanceManageController.php

### Purpose
Monthly **attendance register** ("site register book") — a spreadsheet-like grid (one row per employee, one column per day of the pay-cycle) built from `site_attendance` punches, with a bulk **verify** step that locks a month's entries. This is a general attendance-register feature that happens to live under the "Site" naming, and is functionally near-duplicated by `SiteattendanceregisterController` (see below — the codebase maintains two parallel implementations of the same register-book feature).

### Who can access it
`AppController` gate; `user_group == 2` restricts the branch dropdown to the employee's own branch (`Controller/SiteAttendanceManageController.php:60-85`) and, at `:271-278`, `:383-390`, `:495-502`, gates several actions with `if ($user_group != 2) { ... }` guards mixed with `if ($user_group == 2)` blocks (both patterns appear — worth double-checking during migration since the two conditions are not simply inverses of each other in this file).

### Step-by-step user flow
1. `showregister()` (`:53-175`) renders the register landing page (`View/SiteAttendanceManage/showregistertab.ctp` framework), populating branch and employee dropdowns (branch-restricted for `user_group==2`, `:61-85`).
2. `showregistertab($verified)` (`:239-...`) toggles between unverified/verified tabs.
3. Grid data loads via `listregisterentries()` (`:347-...`) / `listverifiedregisterentries()` (`:476-...`).
4. **Build/refresh the register** — `processregisterentries()` (`:574-588`) calls a stored procedure `site_insert_update_att_reg(company_code, branch, user, month, @Perr_msg)` (`:584`) that (re)materializes the register rows from raw punches.
5. **Verify** — `verifyregisterentries($registerid)` (`:590-709`) accepts a comma-separated list of `registerid`s, recomputes `presant_total`/`leave_total`/`lop_total` per row from the day-by-day `FIELD1..FIELDn` columns, then sets `isdelete = 'N'` (i.e., "keep/lock"). No confirmation text beyond `success`/`failure` JSON.
6. **Manual cell edit** — `updateregisterentries($registerid)` (`:787-...`) opens an edit dialog (`View/SiteAttendanceManage/updateregisterentries.ctp`) for correcting a single day's punch, posting to `submitregisterentry()` (`:819-...`, form action `SiteAttendanceManage/submitregisterentry` per `grep`).
7. `checkifregistercanverify()` (`:741-785`) pre-flight-checks a register for missing/"A" (absent) punches before allowing verify, returning the list of mis-punched date fields.
8. `verifiedpdf()` (`:1020-...`) generates a downloadable PDF of a verified register.

### Forms
Register-cell edit form (`submitregisterentry`) posts date + attendance-code values; no client-side `required` markers found in this controller's views search (not explicitly grepped, but pattern matches sibling controllers: HTML5 attributes only, no JS validation library).

### Notifications
None found (`grep -c "sendEmail\|->Email\|smsSend\|sendSMS\|Notification"` = 0).

### AJAX/JS endpoints
`SiteAttendanceManage/jsons`, `/listregisterentries`, `/listverifiedregisterentries`, `/processregisterentries`, `/verifyregisterentries`, `/loadattendanceregisterheader`, `/checkifregistercanverify`, `/updateregisterentries`, `/submitregisterentry`, `/removeAttendanceEntry`, `/verifiedpdf`.

---

## Controller/SiteAttendanceUploadController.php

### Purpose
Bulk Excel **upload/download** of site attendance punches — lets an admin download a pre-formatted `.xlsx` template (via PHPExcel) for a site/month/shift, fill it offline, and re-upload it to bulk-create punch records, rather than punching each employee individually.

### Who can access it
`AppController` gate; explicit `if ($user_group == 2) { ... }` branch restricts to the employee's own branch/site scope at `Controller/SiteAttendanceUploadController.php:37-63` (`index()`), `:190-196` (`load_site_data()`), `:327-...` (`form()`), and again inside `submitform()` at `:621-623`, `:676-679`, `:728-730`.

### Step-by-step user flow
1. `index()` (`:29-103`) renders `View/SiteAttendanceUpload/index.ctp` with branch/site/shift/designation/employee dropdowns.
2. `form($month)` (`:309-386`) renders the actual upload/edit grid (`View/SiteAttendanceUpload/form.ctp`); `attendance($month)` (`:305-308`) is a thin wrapper rendering `attendance.ctp`.
3. Filters cascade via AJAX: `get_shift()` (`:105-159`), `designationFilter()` (`:387-...`), `branchFilter()` (`:414-...`), `employeefilter()` (`:473-...`).
4. Grid rows load via `load_site_data()` (`:162-304`), a paginated join across `site_attendance`/`emp_details`/`emp_proff`/`designation`/`working_day_time_procedures`/`site`.
5. **Bulk submit** — `submitform()` (`:515-...`) iterates day-by-day form fields (`reg-date-{i}`) for up to 31 days in the selected month, and for each day either (a) updates an existing open punch by calling the same `mark_site_attendance_out_fn` stored function used by `SiteAttendanceController::mark_attendance` (`:617`), or (b) inserts a fresh check-in+check-out pair when no existing record is found (continues past `:637`). Errors per-day accumulate into `success_array['message'][]` as `{date, error}` pairs rather than a single flash message.
6. **Template download** — `downloadsiteattendanceformat($ctcuploadtype, $branch, $month, $site, $shift)` (`:763-...`) streams an `.xlsx` (`application/vnd.ms-excel`) built with `PHPExcel` (`:773-824`), named `{company_code}_site_attendance_uploads.xlsx`.

### Forms
Filter form (branch/site/shift/designation/employee/month) plus, in the bulk grid, one shift-dropdown per day-of-month (`reg-date-1..31`). No client-side `required` markers found; validation is entirely server-side per-day (existing-record lookup + stored-function return value check).

### Notifications
None.

### AJAX/JS endpoints
`SiteAttendanceUpload/get_shift`, `/load_site_data`, `/designationFilter`, `/branchFilter`, `/employeefilter`, `/submitform`, `/downloadsiteattendanceformat` (file download, not JSON AJAX).

---

## Controller/SiteattendanceregisterController.php

### Purpose
**A second, largely duplicate implementation of the monthly attendance-register-book feature** already covered by `SiteAttendanceManageController` above — same core methods exist under (nearly) the same names: `showregister`, `showregistertab`, `listregisterentries`, `listverifiedregisterentries`, `processregisterentries`, `verifyregisterentries`, `loadattendanceregisterheader`, `checkifregistercanverify`, `updateregisterentries`, `submitregisterentry` (compare `grep -n "function "` output of both files — 12+ identical method names). This controller additionally has `registerbook`/`siteregisterbook`/`registerbookless`/`empregisterbook` (`Controller/SiteattendanceregisterController.php:55-434`) — printable/viewable register variants — and a `setup.ctp`/`upload.ctp`/`passport.ctp` view set not present in the Manage controller, suggesting this is either the newer or the legacy-predecessor implementation (git history / commit dates would clarify which is currently linked from the live menu — **flag for the team to confirm which of the two is actually in production use** before committing to migrate both).

### Who can access it
Same `AppController` gate; `user_group == 2` branch-scoping at `:97-98`, `:451-452`, `:536-537`.

### Step-by-step user flow
Functionally identical to `SiteAttendanceManageController` above (`registerbook()` at `:55-216` builds the day-range grid honoring the company's configured `attendance_date` cutoff from `DbConfig`, `:62-74`; `getmodal()` at `:351-367` shows a single day's punch detail in a modal; `jsons()`/`sitefilter()`/`filter()` at `:435-620` drive cascading dropdowns).

### Forms / Notifications / AJAX
Same shapes as `SiteAttendanceManageController` — see that section. Endpoint prefix is `Siteattendanceregister/` instead of `SiteAttendanceManage/`.

---

## Controller/SiteWorkController.php

### Purpose
Site master + user-access admin for the **`efsr_site`** table (Employee Field Survey Report site) — a separate site concept from the `site` table used by `SiteAttendanceController`/`ProjectController`. Despite the name "SiteWork," this is essentially another copy of the site-master + menu-permission CRUD pattern (compare its action list to `SiteAttendanceController`'s), minus shift/punch management. It also contains a `downloadempctcformat`/`uploadandsaveempctc` pair (`Controller/SiteWorkController.php:217-427`) that appears to be leftover copy-paste from an Employee-CTC-upload controller (unrelated to sites) — **likely dead/unused code**, flagged for confirmation rather than migration.

### Who can access it
`AppController` gate only; no `user_group`-scoped branch filtering was found in this controller's core CRUD (`lists()` at `:114-159` has no branch condition, unlike its `SiteAttendanceController` counterpart) — meaning an Employee (`user_group=2`) using this screen sees **all** `efsr_site` records, not just their own, which is inconsistent with the rest of the module. **Flag as a possible existing access-control gap** rather than an intentional design.

### Step-by-step user flow
1. `index()` (`:53-64`) renders `View/SiteWork/index.ctp`.
2. `form($site_pkey)` (`:66-87`) / `form2()` (`:89-96`) render `View/SiteWork/form.ctp`, pulling designation/shift/contact reference data plus, if editing, the `efsr_site` row joined to `emp_details` (manager) and `contacts`.
3. Grid loads via `lists()` (`:114-159`), querying `efsr_site` joined to `contacts`.
4. Save posts to `saveSite()` (`:676-712`) — field set: `site_id`, `site_name`, `latitude`/`longitude`, `address`, `contact_id_fkey` (customer contact), `special_remarks`, `jurisdiction`, `sap_site_id`, `technician_pkey`, `gbt_rtt`, `eb_dg_status`, `tenancy`, `superviser_pkey`. No approval workflow (unlike `SiteAttendanceApplyController`).
5. `delete()` — not found as a distinct named action in this controller (unlike `ProjectController`); soft-delete presumably via a shared pattern not exercised in the grepped action list — **flag for verification** if a delete UI element exists in `View/SiteWork/index.ctp`.
6. Menu/user-access admin (`insec`, `save`, `addDefault`, `resetDefault`, `deletemens`, `listuseraccess`, `delete`, `Employee`, `admin`, `get`, `deleteuser`, `saveuseraccess`, `:98-813`) duplicates the same `user_access`/`emp_menu` editor seen in `SiteAttendanceController`.

### Forms
Site form fields as above. HTML5 `required` on several fields (`grep required View/SiteWork/form.ctp` → hits on `eb_dg_status`, `jurisdiction`, etc.), no JS validation library.

### Notifications
None.

### AJAX/JS endpoints
`SiteWork/saveSite` (form action, `grep` confirmed), `/lists`, `/insec`, `/save`, `/listuseraccess`, `/jsons`, `/downloadempctcformat`, `/uploadandsaveempctc` (likely dead — see above).

---

## Controller/FieldSurveyController.php (dup: `FieldSurveyController_bkup.php`)

### Purpose
Equipment master + "survey ticket" workflow tied to `efsr_site` (the same field-survey site master `SiteWorkController` manages). Field staff record equipment inventory per site (`efsr_equipments_master`) and raise survey "tickets" (`efsr_tickets`) against a site/equipment/survey-type combination, which get approved by a named approver.

Note: `Controller/FieldSurveyController_bkup.php` is a byte-for-byte-era backup (`.bkup.megha_6.4.19`-style views also exist alongside it: `form.ctp.bkup.megha_6.4.19`, etc.) — **treat as dead code, not a second live feature**; confirm it isn't separately routed before excluding it from migration.

### Who can access it
`AppController` gate only; `grep -n "user_group\|menu_id"` on this controller returned **zero matches** — no branch/employee scoping anywhere in this controller, unlike almost every other controller in this feature area.

### Step-by-step user flow
1. `home()` (`:53-69`) / `index()` (`:70-82`) render landing pages (`View/FieldSurvey/home.ctp`, `index.ctp`) with equipment-type and item reference data.
2. **Equipment master** — `form($id)` (`:99-112`) renders `View/FieldSurvey/form.ctp` (add/edit one `efsr_equipments_master` row: type, manufacturer, model). Grid via `lists()` (`:115-156`). Save posts to `saveEquip()` (`:281-296`, form action `FieldSurvey/saveEquip` per `grep`), which is a thin wrapper around `$this->Equipments->save($arr_form_data)` with no field-level validation beyond whatever CakePHP's default model validation (not inspected) provides.
3. **Survey ticket** — `form_ticket($efsr_tickets_pkey)` (`:84-96`) renders `View/FieldSurvey/form_ticket.ctp`, joining `efsr_tickets` to `efsr_site`, `survey_type`, `emp_details` (both requester and approver), and `efsr_equipments_master`. `ticket()` (`:306-...`) renders the ticket list screen (`View/FieldSurvey/ticket.ctp`), grid via `lists_site()` (`:158-228`). Save posts to `save()` (`:231-279`, form action `FieldSurvey/save`): auto-generates `ticket_no` as `concat(efsr_tickets_pkey, "", site_fkey)` and `efsr_id` as `concat(site_fkey, "", ticket_no)` immediately after insert (`:272-273`) — i.e., the human-readable ticket ID is only known *after* the row is created, which the UI must poll/refetch to display.
4. `jsons()`/`jsons_equipments()`/`jsons_Surveys()` (`:316-496`) drive cascading equipment/site/survey-type dropdowns.
5. Excel upload/download for equipment and tickets — `downloadempctcformat`, `downloadempticketformat`, `uploadandsaveempctc`, `uploadandsaveempequipment` (`:625-1026`), same PHPExcel pattern as `SiteAttendanceUploadController`.

### Forms
Ticket form: site, equipment, survey type, assigned employee, approver — all via dropdowns. Equipment master form: type, manufacturer, model, name. HTML5 `required` present on `site_fkey` in `View/FieldSurvey/form.ctp` (`grep` hit). No server-side duplicate-check functions found (unlike `Project`/`SiteAttendance`'s `checkprojectexists`/uniqueness checks) — tickets/equipment can apparently be duplicated freely.

### Notifications
None (no email to the assigned approver when a ticket is raised — worth flagging as a gap, mirroring the site-approval-workflow finding above).

### AJAX/JS endpoints
`FieldSurvey/save`, `/saveEquip`, `/lists`, `/lists_site`, `/jsons`, `/jsons_equipments`, `/jsons_Surveys`, `/downloadempctcformat`, `/downloadempticketformat`, `/uploadandsaveempctc`, `/uploadandsaveempequipment`.

---

## Controller/ProjectController.php

### Purpose
"Project" management screen — **operates on the same `site`/`site_transactions`/`site_history` tables as `SiteAttendanceController`** (see architecture note above), adding a lightweight work-diary feature (`project_activity`) on top. Functionally this controller is ~90% a copy of `SiteAttendanceController` (same `filtersite`, `lists`, `insec`, menu-admin methods, `site_allocate`, shift close/open, `mark_attendance`, etc. — see `grep -n "function "` comparison) with the UI relabeled Project ID/Name/Work Order dates/Funds instead of Site ID/Name/shift-rate table.

### Who can access it
Same `AppController` gate; `user_group == 2` scoping repeated at `Controller/ProjectController.php:148-167` (`filtersite`), and throughout the copied shift/attendance methods.

### Step-by-step user flow
1. `index()` (`:53-76`) renders `View/Project/index.ctp`.
2. Add/Edit — `form($site_pkey)` (`:188-214`) renders `View/Project/form.ctp` / `form2.ctp`; submission posts to `saveProject()` (`:528-567`), mapping `project_id → site.site_id`, `project_name → site.site_name`, `work_details`, `date_commencement_wo → expected_starting_date`, `date_commencement_actual → actual_starting_date`, `date_completion_wo → expected_compleation_date` [sic, DB typo preserved], `date_completion_actual → actual_completion_date`, `amountutilised → allocated_fund`, `amountreleased → released_fund`, `work_order_date → po_expirydate`.
3. **Uniqueness check** — `checkprojectexists($project_id, $pkey)` (`:228-247`) AJAX-validates the Project ID is unique among active `site` rows before submit (view calls `Project/checkprojectexists`, `grep` confirmed); client JS binds this via `onchange="checkIfProjectIdExists();"` on the Project ID field (`View/Project/form.ctp`, `grep required` hit).
4. **Soft delete** — `delete($site_pkey)` (`:215-227`) sets `site.status = 0`, returns `"Project details deleted successfully!"` / `"...deletion failed!"`.
5. **Work-log entries** — `activity_form($activity_pkey)` (`:77-95`) renders a small modal (`View/Project/activity_form.ctp`, `layout = NULL`); `activity_save()` (`:96-133`) prevents two work-detail entries on the same date for the same activity (`int_datecount` check, `:106-116`, message *"Work detail already added."*), otherwise saves to `project_activity` and returns *"Work Detail Added Successfully"*.
6. Shift/attendance/allocation flows (`form2`, `lists`, `save`, `saveProject`... through `remove_allocate`, `:248-1289`) mirror `SiteAttendanceController` exactly — same field names, same stored-function calls, same messages — see that section for behavioral detail rather than duplicating here.

### Forms
Project form: Project ID (unique, AJAX-checked), Project Name, branch, lat/long, address, work order dates ×2 pairs, allocated/released funds, site engineer/contact. Work-activity modal: project, work date (unique per activity), work detail text. HTML5 `required` present (`grep required View/Project/form.ctp` → 3 hits including the uniqueness-check-bound field).

### Notifications
None.

### AJAX/JS endpoints
`Project/filtersite`, `/checkprojectexists`, `/activity_save`, `/saveProject`, `/lists`, `/site_allocate`, `/save_allocate`, `/remove_allocate`, plus the full shift/attendance endpoint set mirrored from `SiteAttendanceController` (`/get_shift`, `/load_sites`, `/add_site`, `/shift_closure`, `/close_shift`, `/data_site`, `/mark_attendance`, etc., all under the `Project/` prefix). Also calls `ExpenseType/checkexpensetypecodeexists` and `ExpenseType/checkexpensetypenameexists` (outside this scope, `grep` hit in `View/Project`).

---

## Controller/ProjectIncomeController.php

### Purpose
Invoice/payment tracking against a Project (i.e., against a `site` row) — `project_income` and `project_income_payment` tables record invoices raised on a project and partial payments received against them.

### Who can access it
`AppController` gate only — **no `user_group`-scoped branch/site filtering found anywhere in this controller** (`grep -n "user_group\|menu_id"` → zero matches), unlike `ProjectController` itself. An Employee (`user_group=2`) using this screen can see/edit income records for every project, not just their own — **flag as an access-control inconsistency**, same pattern noted for `SiteWorkController` and `FieldSurveyController`.

### Step-by-step user flow
1. `form($project_income_pkey)` (`:44-63`) renders `View/ProjectIncome/form.ctp`, sourcing the project dropdown from active `site` rows.
2. **Save** — `save()` (`:80-158`) is dual-purpose: if `project_income_pkey` is set, it does an `updateAll` and recomputes `balance = total - payments` (adding any new `payamount` into a running total, `:106-107`), then also inserts a `project_income_payment` row if a payment amount was supplied (`:121-127`), returning *"Project Income updated successfully!"*. If new, it checks invoice-number uniqueness among active records first (`:110-114`) — duplicate invoice numbers return *"Invoice Number Already Exist"* and abort (`:151-156`); otherwise it saves and returns *"Project Income Added successfully"*.
3. **Delete** — `delete($project_income_pkey)` (`:66-78`) soft-deletes, returns *"Project Income details deleted successfully!"*.
4. **Grid** — `listproject()` (`:159-231`) is a paginated, free-text-searchable (invoice number/date/total/remarks/project name/ID) list joining `project_income` to `site`.
5. **Dead/copy-paste code**: `checkexpensetypenameexists()` (`:232-247`), `checkregnoexists()` (`:248-267`), and `search()` (`:268-282`) all reference `$this->Vehicle`, a model **not declared in `$uses`** (`Controller/ProjectIncomeController.php:36` lists only `ProjectIncome`, `Site`, `ProjectIncomePayment`) — these three actions are almost certainly leftover copy-paste from a Vehicle-management controller and will throw a missing-model error if ever invoked. **Do not port their behavior; verify they are unused before excluding.**

### Forms
Invoice number (unique among active), invoice date, amount, CGST, SGST, total, payment amount, remarks. HTML5 `required` present (`grep required View/ProjectIncome/form.ctp` → 1 hit).

### Notifications
None.

### AJAX/JS endpoints
`ProjectIncome/save` (form action), `/listproject`, `/checkexpensetypenameexists` (likely broken, see above). Also calls `ExpenseType/checkexpensetypecodeexists` (out of scope).

---

## Controller/MaterialController.php

### Purpose
Simple **item master** CRUD (`item_master` + related `item_details`/`quantity_details`/`waranty_details`/`additional_details` [sic, `waranty` DB typo preserved] child tables) — the catalog that `MaterialRequestController` draws from.

### Who can access it
`AppController` gate only — no `user_group` scoping in this controller at all (`Controller/MaterialController.php` has zero `user_group` references).

### Step-by-step user flow
1. `form($acct_payable_pkey)` (`:56-80`) — note the parameter is misleadingly named `acct_payable_pkey` but is actually the `item_master_pkey`; renders a popup form (`layout = null`) joining all four child tables for one item.
2. Grid via `itemlist()` (`:84-123`), same four-table join, paginated.
3. Delete — `itemdelete()` (`:126-143`) accepts a comma-separated list of `item_master_pkey`s and soft-deletes all of them at once.
4. Save — `save()` (`:145-168`) saves the parent `Item` row, then attaches the returned insert ID as `item_master_fkey` on the child rows and saves all four child models, then **hard-redirects** (`$this->redirect(...)`, `:167`) to `Item/index` — the only non-AJAX/non-JSON action in this entire feature scope; every sibling controller in this area returns JSON instead. Note also an un-gated `debug($arr_form_data)` left in the save path (`:151`), which will dump form data to output if CakePHP debug mode is on in this environment.

### Forms
Item code, category, specification, plus quantity/warranty/additional-detail child-table fields (not itemized from grep alone; view `View/Material/form.ctp` would need a full read to enumerate every field — noted as a gap here for anyone doing field-by-field migration of this one screen).

### Notifications
None. Note: `View/Material/form.ctp`'s AJAX actually targets `Store/save`, `Store/Storelist`, `Store/Storedelete` (`grep url:`/`action=` results) — **a controller/view mismatch**: the Material views appear to POST to a `Store` controller that is outside this task's scope, suggesting either dead code or that "Material" and "Store" are aliased/renamed inconsistently in the UI. Flag for confirmation before relying on `MaterialController`'s own `itemlist`/`itemdelete`/`save` actions as the true live behavior.

### AJAX/JS endpoints
Controller defines `/itemlist`, `/itemdelete`, `/save`, `/form`, but the actual view wiring found via grep points to `Store/save`, `Store/Storelist`, `Store/Storedelete` — see mismatch note above.

---

## Controller/MaterialRequestController.php

### Purpose
Material requisition workflow: employees/branches request items from a **store**, item-by-item, producing a `material_request` (header) + `mr_details` (line items) pair; requests can later be dispatched/edited/order-managed.

### Who can access it
`AppController` gate; explicit `user_group == 2` scoping restricts the **store** and **site** dropdowns to only those the employee has `access_store` grants for (`Controller/MaterialRequestController.php:74-96` in `index()`, `:133-152` in `loadnew()`), and again at `:359-360`.

### Step-by-step user flow
1. `index()` (`:57-99`) renders `View/MaterialRequest/index.ctp` (a full-page, `layout=NULL` SPA-style screen) with item/store/package/contact/site reference data, store list branch-scoped for `user_group==2` (`:74-80`).
2. `loadnew()` (`:121-163`) renders the "new request" sub-view (`loadnewrequest.ctp`) via `$this->render('loadnewrequest')` (`:162`), with a more thorough `access_store` INNER JOIN for the branch-scoped store list (`:139-149`) than `index()` uses.
3. `addnewrow()` (`:165-168`) is an AJAX partial (`.load('MaterialRequest/addnewrow')`, confirmed by grep) that appends one blank item row to the request-line grid.
4. `form($mr_pkey)` (`:170-193`) is the add/edit modal.
5. **Save** — `save()` (`:254-291`) saves the `material_request` header, then for the submitted `item_code` calls `getitem()` to pull default `package`/`re_order_level` from the item master, then checks `CheckIfExists($mr_fkey, $item_code)` (`:293-302`) — if that item is already a line on this request, it calls `UpdateIfExists()` (`:304-...`) to bump the quantity instead of inserting a duplicate line, returning the same *"Added new item sucessfully"* [sic] message either way.
6. **Grid/list** — `materiallist()` (`:196-235`), joined to `store_master`.
7. **Delete** — `materialdelete()` (`:238-251`) bulk-soft-deletes by comma-separated `mr_pkey` list, returns *"Record(s)  deleted successfully."* [sic, double space preserved].
8. **Order-level actions** beyond the base request: `materialtable()` (`:334-431`), `loadtable($id, $rowindex)` (`:432-508`), `editorder($id)`/`editordersave()` (`:509-545`), `deleteordermaster($id)`/`deleteorder($id)` (`:546-570`) — a secondary "sales order"-like detail view layered on top of the request, with matching AJAX also routed through a sibling `Salesorderdetail` controller (`Salesorderdetail/loadtable`, `/deleteorder`, `/deleteordermaster`, `/dispatchorder`, `/offercheck`, all confirmed via `grep` of `View/MaterialRequest`) — **outside this task's controller list but directly wired from these views**; flag for the team since dispatch/offer logic lives there, not in `MaterialRequestController`.
9. Autocomplete helpers: `getautocompletionsmr_code`, `...site_name`, `...gstore_code`, `...customer_name`, `...customer_po_number`, `...item_code` (two versions, one commented out — `:658-717` vs `:673-717`), `...itemcode`, `...item_desc` (`:571-843`).
10. `generatereport()` (`:734-787`) — report/print output for a request.

### Forms
Request header: customer, site, store, package, date. Line items: item (autocomplete), required quantity, unit, package (auto-filled from item master), re-order level (auto-filled). Client-side `required="reduired"` [sic typo preserved in source] on the quantity field (`grep required View/MaterialRequest/form.ctp`).

### Notifications
None.

### AJAX/JS endpoints
`MaterialRequest/loadtable`, `/deleteorder`, `/deleteordermaster`, `/dispatchorder`, `/materiallist`, `/materialdelete`, `/save`, `/editordersave`, plus `.load()` partial-refreshes (`MaterialRequest/addnewrow`, `/index`, `/loadnew`). Also directly calls sibling-controller endpoints `Salesorderdetail/loadtable`, `/deleteorder`, `/deleteordermaster`, `/dispatchorder`, `/offercheck`, and `GoodsReceivedNotes/save` — all outside this task's scope but load-bearing for the full user flow.

---

## Controller/GatePassController.php

### Purpose
Issue/print a **Gate Pass** — a document authorizing an item or person to leave/enter premises, optionally listing multiple items with quantity/rate/amount and an expected return date. Field-worker/security-facing but rendered through the same admin web UI as everything else (no separate mobile flow found).

### Who can access it
`AppController` gate; a single `userGroup` read exists at `Controller/GatePassController.php:340` inside `getDocumentsFromDatabase()` but the variable is captured and **never used to filter the query** (`grep -n "userGroup" Controller/GatePassController.php` shows it declared then not referenced again in the search window) — meaning, unlike almost every other controller here, **Gate Pass listing is not scoped by employee/branch at all**; any logged-in user (Admin or Employee) sees every gate pass in the company. Flag as an intentional-or-not access gap.

### Step-by-step user flow
1. `uploadForm($pkey)` (`:269-333`) renders `View/GatePass/upload_form.ctp` — the add/edit form. For a new pass it auto-generates the next `gate_pass_no` as `{year}/{sequence}` zero-padded to 3 digits (`:293-298`); for an edit it loads the existing `gate_pass` + `gate_pass_items` rows.
2. **List/search** — `getDocumentsFromDatabase()` (`:334-426`) is the AJAX-backed grid, searchable by pass number/date/type/creation-date/employee name, joined to `employee_info`.
3. **Save** — `savePass($pkey)` (`:428-563`) handles both create (`pkey==0`, inserts `gate_pass` then loops `item_name[]`/`qty[]`/`units[]`/`rate[]`/`amount[]`/`return[]` into `gate_pass_items` via raw `INSERT`, `:480-495`) and update (raw `UPDATE gate_pass SET ...`, `:514-527` — note this update branch does **not** touch `gate_pass_items` at all, only header fields; item-line edits are only saved via the separate `printPass` action's edit path, see below). Returns JSON `{status, message, pkey}`; success message *"Gate pass saved successfully."*, failure *"Error saving gate pass. Please try again."*.
4. **Preview/print/finalize** — `savePreview($pkey, $status, $edit)` (`:565-624`) and `printPass($pkey, $is_edited, $pass)` (`:625-817`) together drive the print-preview modal (`View/GatePass/save_preview.ctp`, `print_pass.ctp`). `printPass` is where full edits (header **and** item lines) actually get persisted when `$is_edited=='true'` (`:693-780`) — it replaces all existing `gate_pass_items` for the pass by first zeroing their `status` (`:731-737`) then re-inserting/updating each submitted line (`:739-779`); when `$is_edited=='false'` it just flips `gate_pass.status` between 1 (issued/printed) and 2 (draft/preview) depending on the `$pass` flag (`:641-645`, `:673-680`).
5. `printGatePass($pkey, $pass)` (`:819-...`) generates the actual printable/PDF output using `CompanyContactInfo` letterhead data.
6. `deleteFromGrid($pkey)` (`:882-...`) soft-deletes; `generatereport($pkey)` (`:918-...`) is a report/export entry point; `downloadHistory($type, $mode)` (`:66-268`) exports historical passes.

### Forms
Type, gate pass number (auto-generated, editable), requesting person, issued-to (employee), purpose, remarks, issued-by, sanctioned-by, issued date; repeatable item rows (name, qty, units, rate, amount, return date). Client-side: HTML5 `required` on several fields (`grep required View/GatePass/upload_form.ctp` → many hits including `autocomplete="off"` combos) plus a JS-level completeness check that fires a `$.notify("Please fill the required fields.", {type:'danger', ...})` toast before allowing submit (`View/GatePass/upload_form.ctp:355-360`).

### Notifications
None (email/SMS). All feedback is `$.notify()` toasts (success/danger styled) — this is the one controller in-scope where the toast pattern is explicit and reusable as a reference for the Next.js rebuild's equivalent (e.g. shadcn `toast`/`sonner`).

### AJAX/JS endpoints
`GatePass/printPass`, `/savePass` (both confirmed via `action=` attribute in `View/GatePass`), plus `getDocumentsFromDatabase`, `savePreview`, `printGatePass`, `deleteFromGrid`, `generatereport`, `downloadHistory` (invoked from JS, not grepped as `url:`/`action=` literals but present in the controller's action list and referenced by the view's JS per the `printGatePass('{pkey}')` inline-onclick pattern seen at `:411`).

---

## Controller/OutPassController.php

### Purpose
Near-identical sibling of `GatePassController` for a **personal/staff out-pass** (an employee temporarily leaving during work hours) instead of an item gate-pass — separate `out_pass` table but built by copy-pasting `GatePassController`, with one important **business rule** on top: only one "Personal" type out-pass per employee per calendar month is allowed.

### Who can access it
`AppController` gate; `userGroup` read at `Controller/OutPassController.php:139` — same pattern as `GatePassController`, declared but not used to scope the list query. No branch/employee restriction on visibility.

### Step-by-step user flow
1. `uploadForm($pkey)` (`:67-131`) renders `View/OutPass/upload_form.ctp`; auto-generates `out_pass_number` the same way as Gate Pass (`:91-96`). **Bug carried over from copy-paste**: for `$pkey != 0` (edit), it queries `$this->GatePass->find(... 'GatePass.gate_pass_pkey' => $pkey ...)` (`:99-111`) — i.e. it queries the **`gate_pass` table's primary key**, not `out_pass_pkey`, to load an existing Out Pass for editing. This almost certainly means editing an existing Out Pass via this screen does not load the correct record. **Flag explicitly for the Next.js team as a known legacy defect, not a behavior to replicate.**
2. `getDocumentsFromDatabase()` (`:132-...`) — grid, same shape as Gate Pass's.
3. **Save** — `savePass($pkey)` (`:228-306`): fields `out_pass_number`, `out_pass_date`, `type`, `staff_pkey`, `out_time`, `in_time`, `remarks`, `sanctioned_by`, `issued_by`. **Second bug**: the `if ($pkey == 0) { ...insert... }` branch is the *only* branch that sets `$save` (`:270-276`); there is no `else` handling `$pkey != 0` at all, so `$save` stays `false` for any edit attempt, and the response always falls through to `"Error saving gate pass. Please try again."` (`:285-290`, note the message text itself still says "gate pass," another copy-paste tell). **Editing an existing Out Pass is effectively broken in this codebase** — worth confirming with the client whether edit is expected to work in the new build, since the legacy one doesn't.
4. `savePreview($pkey, $status, $edit)` (`:308-359`) — unlike Gate Pass, this one correctly queries the `out_pass` table (`:322-339`), joining `employee_info` three times for staff/issued-by/sanctioned-by names.
5. `printPass`/`printOutPass`/`deleteFromGrid`/`generatereport` (`:361-607`) mirror Gate Pass's print/finalize flow.
6. **Personal-pass limit check** — `checkPersonalPass($date, $pkey)` (`:608-650`) is called before allowing a new "Personal"-type pass to be created; queries `out_pass` for any non-status-0 Personal pass for that employee in the same month, returning failure message *"Personal out pass has already been issued to this employee."* (if status==2/finalized) or *"Already, a personal out pass has been generated. For new creations, please delete the existing record."* (otherwise), else success *"Out pass not issued in this month."*.

### Forms
Same shape as Gate Pass minus item lines: type (dropdown incl. "Personal"), pass number (auto), staff, out/in time, remarks, issued-by/sanctioned-by, date. Client-side `required` present (`grep required View/OutPass/upload_form.ctp`), with the same `$.notify("Please fill the required fields.", ...)` pattern (`View/OutPass/upload_form.ctp`, mirrors GatePass).

### Notifications
None (email/SMS).

### AJAX/JS endpoints
`OutPass/checkPersonalPass`, `/printPass`, `/savePass` (all confirmed via `action=`/`url:` grep of `View/OutPass`), plus `getDocumentsFromDatabase`, `savePreview`, `printOutPass`, `deleteFromGrid`, `generatereport`.

---

## Controller/DeviceController.php

### Purpose
Registry of physical **biometric/attendance punch devices** (hardware, table `mypayrol_control_db.devices` — note: lives in a **separate control database**, not the per-company `companydb`) per company branch. Unrelated to job "sites"; this is IT-asset/hardware admin for the punch machines that feed attendance data generally (not specific to the Site Attendance feature).

### Who can access it
`AppController` gate; a **company-specific** branch restriction exists: if `user_group == 2` **and** `company_code` is `'GLET'` or `'ABSG'` (two specific tenant codes), the branch list is filtered down to the employee's own branch via a stored function `get_branch_code_abs_fn(emp_pkey)` (`Controller/DeviceController.php:70-90`, repeated at `:113-116`). For every other company, an Employee sees all branches/devices — **this is a tenant-specific carve-out, not a general rule**, important to flag since a naive migration might assume all `user_group==2` scoping is universal.

### Step-by-step user flow
1. `index()` (`:57-117`) renders `View/Device/index.ctp` with the branch list (company-scoped, `:62`) and device list (`:92`), plus a `planId` lookup against the central control DB (`CentralUserCredentials`, `:98-115`) — likely used to gate a plan-tier feature flag in the view (not otherwise explored here).
2. **List** — `listDev()` (`:119-166`) AJAX grid, searchable by employee/branch/serial-number free text, filterable by branch/serial.
3. **Add/Edit** — `addEditDev($dev_id)` (`:168-184`) renders `View/Device/add_edit_dev.ctp`.
4. **Save** — `saveDev()` (`:186-237`) builds a raw dynamic `INSERT`/`UPDATE` string against `mypayrol_control_db.devices` based on which `$_REQUEST` fields are present (`:209-231`) — no parameterization, string-concatenated SQL throughout this controller (worth flagging as a security note, though out of this report's UX scope).
5. **Uniqueness check** — `checkDevice()` (`:239-251`) AJAX-validates a Device ID isn't already registered for the company before save, returning literal string `'EXIST'` or `'OKAY'` (not JSON).

### Forms
Serial number (required — save silently no-ops/returns `false` without it, `:209`), device location (required, same), branch, friendly device name. No `required` HTML attribute found in the grepped set for this controller's views (not explicitly checked; flagged as a gap).

### Notifications
None.

### AJAX/JS endpoints
`Device/checkDevice` (confirmed via `url:` grep), plus `listDev`, `addEditDev`, `saveDev` (used from view JS, not grepped as literal strings but present in controller action list).

---

## Controller/DeviceEmployeeInfoController.php

### Purpose
Maps individual **employees to a biometric device** (assigns/records each employee's device-specific enrollment ID) — the join table between `DeviceController`'s hardware registry and the employee roster.

### Who can access it
Same `AppController` gate plus the identical `GLET`/`ABSG`-only branch restriction pattern as `DeviceController` (`Controller/DeviceEmployeeInfoController.php:117-132`, `:247-262`) — same tenant-specific carve-out caveat applies.

### Step-by-step user flow
1. `index()` (`:57-87`) renders `View/DeviceEmployeeInfo/index.ctp` with device list and plan-tier lookup (mirrors `DeviceController::index`).
2. **List** — `listEmpDev()` (`:89-189`) AJAX grid over `emp_device_comp_branch` joined to `devices`, searchable by employee/device text, grouped by `emp_fkey` (i.e., one row per employee even if they have multiple historical device rows, `:153`).
3. **Add/Edit** — `editEmpDev($emp_dev_seq)` (`:303-327`) renders the edit form; if `$emp_dev_seq` is falsy it `exit()`s immediately with no error message (`:317`) — a dead-end for a malformed URL, not a graceful redirect.
4. **Save** — `saveEmpDev()` (`:350-393`) — the live version uses parameterized query placeholders (`?`) unlike the commented-out earlier version above it (`:329-349`) which used raw string concatenation; only updates (`emp_device_comp_branch_seq > 0` required, `:368`) — **there is no create branch**, meaning new employee-device mappings must be created some other way not present in this controller (possibly seeded elsewhere, e.g. during employee onboarding — outside this scope).
5. **Bulk export** — `downloadempuploadform()` (`:191-302`) streams an `.xlsx` via PHPExcel listing every employee-device mapping (username, name, device ID/name/serial, employee-device ID), company- and (for GLET/ABSG employees) branch-scoped.

### Forms
Device dropdown, employee-device enrollment ID (numeric, cast via `(int)`, `:357-361`) — only two real editable fields per the `saveEmpDev` payload.

### Notifications
None.

### AJAX/JS endpoints
`DeviceEmployeeInfo/listEmpDev`, `/editEmpDev`, `/saveEmpDev`, `/downloadempuploadform` (file download).

---

## Summary of cross-cutting findings for the Next.js migration

1. **No email/SMS notifications anywhere in this feature area.** All 15 controllers — confirmed by direct grep — never call the mail/SMS layer. Any approval, gate-pass-issued, or ticket-raised "notification" in the legacy system is a same-session JSON message or `$.notify()` toast only. If the business expects real notifications in the rebuild, that is new scope, not parity work.
2. **`api/v1` is dead/unused for this feature area** — it's an empty Slim scaffold, not a mobile API. All AJAX described above hits the same CakePHP controllers as the desktop admin UI; there is no separate field-worker/mobile backend to reverse-engineer.
3. **"Project" and "Site Attendance" share one data model** (`site`/`site_transactions`/`site_history`) with two different UI skins layered over `SiteMaster`/`SiteHistory`/`SiteTransactions` — model this as one entity in the new system, not two.
4. **Two live implementations of the monthly attendance register** exist (`SiteAttendanceManageController` and `SiteattendanceregisterController`), overlapping almost completely — confirm with the client/product owner which one is actually in production before committing migration effort to both.
5. **Inconsistent branch/employee scoping.** Most controllers gate `user_group==2` users to their own branch/site/store, but `SiteWorkController`, `FieldSurveyController`, `ProjectIncomeController`, `GatePassController`, and `OutPassController` do **not** — either an intentional "these are cross-branch by design" decision or an access-control gap; needs a product-owner call before the new RBAC model is finalized.
6. **`OutPassController::savePass` cannot edit existing records** (only inserts), and `uploadForm()`'s edit path queries the wrong table (`gate_pass` instead of `out_pass`) — both are legacy defects, not intended behavior to replicate.
7. **`MaterialController`'s views actually POST to a `Store` controller**, not to `MaterialController`'s own `save`/`itemlist`/`itemdelete` — needs verification of which is truly live before relying on either.
8. **`ProjectIncomeController` has three dead actions** referencing an undeclared `Vehicle` model — will error if invoked; do not port their apparent behavior.
9. **Client-side validation throughout this feature area is HTML5 `required` attributes only** — no jQuery-validate/Parsley/etc. — so all real validation enforcement is server-side (uniqueness checks, existing-data conflict checks) and must be reproduced there in the Next.js/API layer, not assumed to already exist as reusable client logic.

---

## 10. Assets, Inventory & Purchasing

# Assets, Inventory & Purchasing — Behavior Report

Scope: `Controller/AssetController.php`, `ItemController.php`, `ItemSpecificationController.php`, `StockReportController.php`, `StockTranferController.php`, `StockmanagementController.php`, `StoreController.php`, `SupplierMasterController.php`, `VendorController.php`, `PurchaseOrderController.php`, `DirectPurchaseOrderController.php`, `GoodsReceivedNotesController.php`, `VehicleController.php`, and their `View/` folders. `Config/routes.php` has **no custom routes** for any of these controllers (`grep` for all 13 controller names against `Config/routes.php` returned no matches) — all navigation is CakePHP default `/Controller/action/params` routing plus AJAX/jQuery `.load()` calls into these actions.

**Backup/duplicate files (skipped, noted only):**
- `View/Asset/*` has 10 files with `#bkup_*`/`_BKUP`/`_bkk` suffixes (addnew, allocatenew, company_asset, create_asset, details, edit, emp) — old snapshots of the current files.
- `Controller/StockReportControllerBkup-01.php`, `Controller/StockReportController_bkup_vanguards.php` — full-controller backups of `StockReportController.php`.
- `View/StockReport/*` — nearly every view has one or more `#bkup_*` duplicates (empsalary, grn, hrreports, loadcriteriaitems, poreturn, salaryslip, showreport, stockalldetails, stockdetails, stocktrnsfer, uniformallocation, uniformemireport).
- `Controller/StoreController_bkup_VGFS.php` — backup of `StoreController.php`.
- `View/Store/*` — adj, editpo_order, form, index, returnitem, storedata each have `#bkup_*` duplicates.
- `View/DirectPurchaseOrder/bkp_28_05_2016`, `View/GoodsReceivedNotes/bkkp_07-06-2016`, `View/StockTranfer/bkp_10_06_2016` — old dated snapshot folders.
- `View/PurchaseOrder/*` — edit, form, index each have `#bkup_*` duplicates plus `index_kbp_28_05_2016.ctp`.
- `View/GoodsReceivedNotes/*`, `View/Vehicle/*` — index/form have `#bkup_*` duplicates.
- `View/Vendor/index.ctp#bkup_megha_20_02_2020`.

**Global auth gate** (applies to every controller below): `Controller/AppController.php:39-46` — `beforeFilter()` reads session `user_group`; if it is not `1` (Admin/HR) or `2` (Employee/ESS), it redirects to `Site/login`. None of the 13 controllers in this scope implement their own `isAuthorized()` or check `emp_menu`/`user_access` menu_id gating in PHP — access control beyond the global gate is done by (a) hiding/showing menu links in the app shell based on `emp_menu`/`user_access` (not in these controllers) and (b) branch/store-level row filtering inside individual actions (see below).

---

## 1. AssetController.php — Company Asset Register & Allocation

**Purpose:** Maintain a master list of company assets (laptops, phones, furniture, etc.), allocate/return them to employees, and track allocation history and condition state.

**Who can access it:** Gated only by the global `user_group` check (`AppController.php:39-46`). Inside the controller, `Create_asset_new()` (`Controller/AssetController.php:1249-1285`) and `Company_asset_new()` (`:1329-1410`) branch behavior by `user_group`:
- `user_group == 1` (Admin): sees all branches via `MasterdataManagement->getBranchesForAll()` (`:1261`).
- `user_group == 2` (Employee) with feature access context (`:1262-1270`): sees hierarchy branches or explicitly allocated branches (`user_feature_branch_access` table, `:1347-1393`) or only their own branch.

**Step-by-step user flow:**
1. `index()` (`:54-68`) lists active assets joined to `asset_types`.
2. `listAllAssets()` (`:379-496`, replacing older commented-out `#backup` versions at `:128-376`) is the current AJAX-paged grid endpoint — supports search `q` and `asset_status` filter (`allocated`/`returned`/`not_allocated`), computed via subqueries on `asset_allocate`.
3. **Add asset:** `addnew($pkey)` (`:595-624`) renders create/edit form; asset types loaded from `asset_types`. Save goes through generic `save()` (`:941-947`) which just calls `Assets->save()`.
4. **Allocate to employee:** `Allocatenew($pkey, $branch)` (`:654-744`, edited "by bindu 29-08-25", old version commented at `:626-652`) lists asset types available for allocation (branch-filterable). View renders `edit.ctp`'s template via `render('Allocatenew')` (`:852`) — same view used for both `Allocatenew` action and `edit()` action (`:746-853`).
5. `getEmi()` (`:868-902`) — AJAX endpoint returning an `<option>` HTML fragment of unallocated assets of a chosen type (called when the allocation form's asset-type dropdown changes).
6. `assetsave()` (`:1084-1096`) / `AllocatenewAsset()` (`:1106-1140`) — save the allocation row into `asset_allocate`, update `asset_state`, and update `asset_management.status`.
7. **Release/return:** `release($asset_pkey)` (`:949-957`) — raw SQL updates `asset_allocate.status='Returned'` and `asset_management.status='Returned'`.
8. **Details/history:** `details($edit_pkey)` (`:903-939`) shows allocation history for one asset (joined to `emp_details`).
9. **Employee's own assets list** (ESS use-case): `listitems($pkey, $branch)` (`:999-1082`, edited "by bindu 29-08-25") — AJAX list of assets allocated to an employee, joined to `employee_info` for branch filtering.
10. **New asset-creation wizard (branch/employee cascading dropdowns):** `Create_asset()` (`:1214-1245`, simplified "by bindu 18-12-2025" — the prior version at `:1142-1210` did payroll-privilege/branch-exclusion filtering that was removed) and `Create_asset_new()` (`:1249-1285`) feed `getEmployeesByBranch()` (`:1287-1327`, Select2-compatible paginated employee search) and `Company_asset_new()` (`:1329-1410`, branch dropdown driven by `user_feature_branch_access.is_hierarchy`).

**Forms:** No `.ctp` in `View/Asset/` was read line-by-line beyond structure, but based on controller `set()` calls the `addnew.ctp`/`allocatenew.ctp`/`create_asset_new.ctp`/`company_asset.ctp` forms collect: asset name, type (`asset_types`), serial no, brand, model, value, warranty, year, condition/specifications, and (allocation) employee, official email/contact, CRM ID, allocated office space, allocated date, description.

**Notifications:** None found in `AssetController.php` — no email/SMS dispatch calls.

**AJAX/JS endpoints hit:** `Asset/listAllAssets`, `Asset/getassets`, `Asset/getTypes`, `Asset/getEmi`, `Asset/listitems`, `Asset/save`, `Asset/assetsave`, `Asset/AllocatenewAsset`, `Asset/release/:asset_pkey`, `Asset/getEmployeesByBranch`.

---

## 2. ItemController.php — Item Master

**Purpose:** Master catalog of purchasable/stockable items (item code, description, category, specification, package/UOM, pricing, warranty) used throughout PO/GRN/Store/StockTransfer flows.

**Who can access it:** Global gate only; no branch-level filtering in this controller.

**Step-by-step user flow:**
1. `index()` (`Controller/ItemController.php:50-53`) is empty — real listing UI is loaded via `itemlist()`.
2. `itemlist($param)` (`:154-221`) — paginated grid joining `item_master`, `item_details`, `quantity_details`, `waranty_details`, `category_master`, `item_specification`, `additional_details`; supports single-item filter via `item` request param.
3. `itemfilter()` (`:57-79`) — Select2-style typeahead search (`{id, text}` pairs) over `item_desc`.
4. **Add/Edit modal:** `form($acct_payable_pkey)` (`:81-119`, view `View/Item/form.ctp`) — loads dropdowns for category, specification, package, UOM, and (if editing) full joined item row.
5. **Save:** `save()` (`:243-280`) — chains `Item->save()` then `ItemDetails->save()`, `ItemAdditionalDetails->save()` (uses `getInsertID()` after the first save to link child records); `QuantityDetails`/`WarrantyDetails`/`ItemPricing` saves are commented out (dead code, not executed).
6. **Delete:** `itemdelete()` (`:224-241`) — soft delete (`status=0`) by array of `item_master_pkey`.
7. **Duplicate check:** `chkcategory()` (`:121-151`) — AJAX validation used by the form's `onchange` handlers.

**Forms (`View/Item/form.ctp`):**
- Fields: Item Code* (`item_code`), Item Name* (`item_desc`), Item Category (dropdown), Item Specification (dropdown, hidden by CSS `display:none` at `:122`), then tabbed sections — "Cost & Price" (unit cost, sales price, vendor price — several fields hidden via inline `style="display:none"`) and "Quantity Details" (item balance, in stock, stock on hand, qty committed — mostly hidden — plus visible Minimum Order Qty, Re-order Level, Economic Order, Monthly Demand), and a Warranty tab (manufacture model/date, engineering no, warranty id/dates/period/notes — present in the DOM but no visible tab link in the `nav-tabs` list, i.e. effectively unreachable from the UI at `:139-153`).
- **Client-side validation:** `required="required"` HTML5 attribute on Item Code and Item Name only (`View/Item/form.ctp:94,101`). Item Code and Item Name each fire an `onchange` AJAX call (`checkIfitemcodeExists`/`checkIfitemExists`, `:19-45,46-74`) to `Item/chkcategory` for live duplicate-name/duplicate-code detection, displaying inline red error text and **clearing the field** if a duplicate is found.
- **Server-side validation:** `chkcategory()` (`Controller/ItemController.php:121-151`) queries `item_master` for existing rows with the same `item_code` or `item_desc` (excluding current pkey when editing) and returns `{msg: "1"}` (code dup) or `{msg: "2"}` (name dup); no hard blocking on submit — the JS wipes the field but the form can still be submitted with an empty value.
- On submit, `#itemform` is intercepted (`View/Item/form.ctp:12-18`), confirms via `confirm("Do You Want To Save The Form")`, then `ajaxSubmit()`; success closes the modal, reloads the `acctptable` datagrid, and shows a `$.notify` "Success" toast (`:1-11`).

**Notifications:** None (in-app `$.notify` toast only, no email/SMS).

**AJAX/JS endpoints hit:** `Item/itemfilter`, `Item/form/:pkey`, `Item/chkcategory`, `Item/itemlist`, `Item/itemdelete`, `Item/save`.

---

## 3. ItemSpecificationController.php — Item Specification Master

**Purpose:** Small lookup-table CRUD for "item specification" values referenced by the Item Master's (largely hidden) Specification field.

**Who can access it:** Global gate only.

**Step-by-step user flow:** `index()` (`Controller/ItemSpecificationController.php:40-42`) is empty; `listmaster()` (`:88-107`) is the AJAX grid data source; `specification($specification_pkey)` (`:44-52`, view `specification.ctp`) is the add/edit modal; `save()` (`:66-86`) creates/updates (`modified_by`/`modified_date` set only when editing); `delete($user_pkey)` (`:54-64`) soft-deletes (`status=0`).

**Forms:** `specification.ctp` — not read in detail, but the model only has a `item_specification` text field plus audit columns based on `save()`.

**Server-side validation:** None beyond required session `user_name` for `created_by`; no duplicate-name check.

**Notifications:** None.

**AJAX/JS endpoints hit:** `ItemSpecification/listmaster`, `ItemSpecification/specification/:pkey`, `ItemSpecification/save`, `ItemSpecification/delete/:pkey`.

---

## 4. StockmanagementController.php — Stock Module Landing/Feature-Gate

**Purpose:** Not a CRUD controller — it is the entry dashboard for the whole Stock/Inventory module and a feature-flag lookup used to show/hide sub-features per subscription plan.

**Who can access it:** Global gate only.

**Step-by-step user flow:**
1. `index()` (`Controller/StockmanagementController.php:10-16`) sets `arr_branches` from `MasterdataManagement->getBranchesListForCombo()` and renders the module landing page (branch selector + tiles, presumably linking into Asset/Store/PurchaseOrder/etc.).
2. `getAttendanceFeatures()` (`:17-77`) — despite its name (copy-pasted from an Attendance controller — note the `feature_key = 'Stockmanagement'` filter at `:39` shows it was actually repurposed for Stock), looks up the company's subscription `plan_id` from `CentralUserCredentials` (control DB), then cross-references `Features`/`PlanFeature` to return each Stock-module feature's `is_enabled` flag as JSON — used to grey out/hide inventory tiles the company hasn't licensed.

**Notifications:** None. **AJAX/JS endpoints hit:** `Stockmanagement/getAttendanceFeatures`.

---

## 5. StoreController.php — Store Master, Stock Adjustment & PO-Return

**Purpose:** Multi-purpose controller: (a) store/warehouse master CRUD, (b) employee↔store access allocation, (c) manual stock adjustments, and (d) **PO-return workflow** (returning goods-received items back to a supplier against a GRN).

**Who can access it:** Global gate only. Several list/lookup actions branch-filter by `user_group == 2` via a subquery on `access_store` (e.g. `getautocompletionsstore_code` pattern reused elsewhere; here `MasterdataManagement->getStoreListForCombo()` at `:63` presumably encapsulates the same).

**Step-by-step user flow — Store Master:**
1. `index()` empty (`:53-55`); grid data via `Storelist()` (`Controller/StoreController.php:276-320`).
2. Add/Edit modal `form($store_pkey)` (`:69-83`, view `View/Store/form.ctp`).
3. Duplicate check `chkcategory()` (`:196-229`) mirrors Item's pattern — checks `store_code`/`store_location` uniqueness.
4. Save `save()` (`:384-395`); soft-delete `Storedelete()` (`:369-382`).
5. **Employee↔Store access:** `Storedata($store_pkey)` (`:237-250`) lists unallocated vs. allocated employees for a store; `save_allocate()`/`remove_allocate()` (`:322-343, 346-366`) insert/soft-delete rows in `access_store` — this table is the row-level permission source used throughout PurchaseOrder/GRN/StockTransfer for `user_group==2` filtering.

**Step-by-step user flow — Manual Stock Adjustment:**
6. `adj()` (`:57-59`) renders the adjustment form. `save_stock_transfer()` (`:96-131`, misleadingly named — it's actually the adjustment save) checks current balance via SQL function `stock_bal_qty_fn(date, store, item)` (`:111-112`) and **blocks the save** (returns `{msg:'Adding new item failed', pk:0}`) if requested qty exceeds available qty (`:113,128-130`). On success it writes `stock_adjustments` + `stock_adjustments_details` and a raw `INSERT INTO stock_details ... item_state='ADJUSTMENT'` row (negative `item_qty`) (`:125-126`).
7. `stockadjlist()` (`:133-194`) — AJAX grid, filterable by employee/branch.

**Step-by-step user flow — PO Return (goods received → returned to supplier):**
8. `returnitem()` (`:61-66`) renders the return form with store list.
9. Lookups: `findstoreitems`, `findgritems`, `findgrnlistitems`, `findgrnitemslist`, `getautocompletionsitem_desc`, `getavailqty`/`getavailqty_grn`, `itemexists` (`:397-716`) progressively narrow store → GRN → item → available-to-return quantity, cross-checking against `return_gr_items` for already-returned quantities.
10. `save_po_return()` (`:719-777`) — writes `po_return_request` (header), a negative-quantity `stock_details` row (`item_state='PORETURN'`, `status=0` = pending), then `return_gr_items` line (also `status=0` = pending, `delete_status` used as a soft-delete flag independent of `status`).
11. Line items can be edited (`editpo_order`/`editorder_save`, `:874-930`) or deleted (`deletepoorder`, `:829-842`) while still pending (`status=0`).
12. **Final submit:** `submit_return($id)` (`:860-872`) flips `return_gr_items.status=1` and `stock_details.status=1` — this is the commit step that actually reduces on-hand stock.
13. `poreturn()` (`:932-964`) — AJAX grid of committed (`status=1`) returns.

**Forms (`View/Store/form.ctp`):**
- Fields: Store Code* (`store_code`), Store Location* (`store_location`), Address*, City*, State, Pin Code.
- **Client-side validation:** `$.validate({form:'#form-user-master'})` (jQuery Validation plugin) plus HTML5 `required` on Store Code/Location/Address/City. Store Code and Store Location each fire `onchange` AJAX duplicate checks (`checkIfStorecodeExists`/`checkIfStoreExists`, `:37-66,67-95`) against `Store/chkcategory`, showing inline red errors and clearing the field — same non-blocking pattern as Item form.
- Submit confirms via `confirm("Do You Want To Save The Form")` then `ajaxSubmit`.
- **Bug noted:** the success handler references an undefined `Response`/`type` variable (`View/Store/form.ctp:19-20` — should be `resp`/parsed JSON) — the `$.notify` call will throw a JS error in the browser console on every successful store save (cosmetic, doesn't block the save itself since the modal close/reload lines run first).

**Notifications:** None (email/SMS). In-app only: `$.notify` toasts.

**AJAX/JS endpoints hit:** `Store/Storelist`, `Store/chkcategory`, `Store/save`, `Store/Storedelete`, `Store/save_allocate`, `Store/remove_allocate`, `Store/save_stock_transfer` (adjustment save), `Store/stockadjlist`, `Store/findstoreitems`, `Store/findgritems`, `Store/findgrnlistitems`, `Store/getautocompletionsitem_desc`, `Store/itemexists`, `Store/getavailqty`, `Store/getavailqty_grn`, `Store/save_po_return`, `Store/loadtabledata`, `Store/deletepoorder`, `Store/deleteordermaster`, `Store/submit_return`, `Store/editpo_order`, `Store/editorder_save`, `Store/poreturn`.

---

## 6. SupplierMasterController.php — (Actually) Material Request CRUD

**Purpose:** Despite the controller name "SupplierMaster", every action operates on the `material_request`/`mr_details` tables (`$uses = array('SupplierMaster')` at `Controller/SupplierMasterController.php:49` is declared but never used in code — all queries go through `$this->MaterialRequest`, an undeclared/implicitly-available model). This is effectively the **Material Request (MR)** controller — the first step of the procurement chain, feeding into `PurchaseOrder`. INFERRED: this is a mis-named/legacy-renamed controller; the real "Supplier Master" (vendor) CRUD lives in `VendorController.php` instead.

**Who can access it:** Global gate only.

**Step-by-step user flow:**
1. `form($acct_payable_pkey)` (`:57-69`) — add/edit MR form, loads `all_store`.
2. `materiallist()` (`:71-108`) — AJAX grid of active material requests.
3. `save()` (`:129-160`) — saves `material_request` header, then loops `item_no[]` array to save each `mr_details` line (required_qty, incoming_qty/date, re_order_level, description, package, unit).
4. `materialdelete()` (`:111-127`) — soft delete by `mr_pkey` array.

**Forms:** Not read in detail (no `View/SupplierMaster/form.ctp` present — only `addnewrow.ctp`, `form.ctp`, `index.ctp` exist; `form.ctp` wasn't opened given scope/budget, INFERRED fields from `save()`: item, required qty, incoming qty, incoming date, re-order level, description, package, unit, per line).

**Notifications:** None. **AJAX/JS endpoints hit:** `SupplierMaster/materiallist`, `SupplierMaster/materialdelete`, `SupplierMaster/save`, `SupplierMaster/form/:pkey`.

---

## 7. VendorController.php — Vendor/Contact Master, Vendor Item Purchase & Allocation

**Purpose:** Manages vendor (supplier) contact records (via the shared `Contacts` model, `relationship='Vendor'`), a separate simple "item_purchase"/"item_allocate" mini-ledger (legacy, appears to be an older/parallel item-purchase tracking mechanism not connected to the PO/GRN tables), Excel export/import of vendor lists, and employee CTC-format bulk uploads (misplaced in this controller).

**Who can access it:** Global gate only.

**Step-by-step user flow:**
1. `vendorlist()` (`Controller/VendorController.php:460-504`) — AJAX grid of `contacts` where `relationship='vendor'`.
2. `Master_save()` (`:446-458`) — save/update a vendor via `Contacts->save()`.
3. `deleteEmp()` (`:506-518`) — soft-delete vendor(s) by `contact_id` array (method name is a copy-paste leftover from an employee controller).
4. `downloadempctcformat()` (`:107-185`) — generates an `.xlsx` template (PHPExcel) with vendor import columns (Company Name, Reg No, Address, City, State, Pin Code, Email, Relationship, Phone, TAN, PAN, GST, Bank details, Contact Person, Designation).
5. `uploadandsaveempctc()` (`:187-342`) — reads an uploaded `.xlsx`, validates all "mandatory" columns are non-blank per row (breaks out and returns `success:0` with "Please fill all fields." if any mandatory cell is empty), then bulk-saves each row into `Contacts`.
6. Separate legacy item-purchase/allocation flow: `lists_purchase()`/`form_purchase()`/`save_Purchase()`/`deleteEmppurchase()` (`:344-388, 526-540, 548-558, 560-572`) manage `item_purchase` records; `lists()`/`allocate()`/`allocate_form()`/`allocate_save()` (`:390-433, 574-613`) manage `item_allocate`/`allocate_details` (allocating purchased items to employees with quantities). These operate on different tables (`item`, `item_purchase`, `item_allocate`, `allocate_details`) than the main Item/PurchaseOrder/GRN flow (`item_master`, `po_item_details`, `gr_item_details`) — INFERRED this is a vestigial/parallel mini-module, possibly from an earlier version of the app before the full PO/GRN system was built, still reachable from the UI but disconnected from the main procurement chain.

**Forms:** `form.ctp`/`form_purchase.ctp`/`allocate_form.ctp`/`addeditvendor.ctp` exist but weren't read in full detail; `master.ctp` is presumably the vendor master modal (fields inferred from `save()`: standard `Contacts` fields — company name, address, city, state, pincode, email, relationship, phone, TAN, PAN, GST, bank details, contact person).

**Server-side validation:** Only the bulk-upload mandatory-column check (`:216-240`); no per-field validation in `Master_save()`.

**Notifications:** None (email/SMS). File download response headers only (`:111-112`, Excel MIME type).

**AJAX/JS endpoints hit:** `Vendor/vendorlist`, `Vendor/Master_save`, `Vendor/deleteEmp`, `Vendor/downloadempctcformat`, `Vendor/uploadandsaveempctc`, `Vendor/lists_purchase`, `Vendor/save_Purchase`, `Vendor/deleteEmppurchase`, `Vendor/lists`, `Vendor/allocate_save`.

---

## 8. VehicleController.php — Vehicle Master

**Purpose:** CRUD for a company vehicle register (registration number, model, make, fuel type, year) — used elsewhere by `VehicleExpensesController` (out of scope) for expense tracking against a vehicle.

**Who can access it:** Global gate only.

**Step-by-step user flow:**
1. `index()` empty (`Controller/VehicleController.php:41-43`); grid via `listvehicle()` (`:140-178`, supports free-text search across model/reg/make/type/year/fuel).
2. Add/Edit modal `vehicle($vehicle_master_pkey)` (`:46-58`, view `vehicle.ctp`) — title toggles "Add Vehicle"/"Edit Vehicle".
3. `save()` (`:74-139`) — **server-side duplicate-registration-number check**: counts existing active rows with the same `reg_number` excluding the current pkey (`:88-92`); if `int_vehiclecount > 0` the "Registration Number Already Exist" branch fires (note: the actual `if($int_vehiclecount>0){...echo...}` block at `:102-116` is **commented out dead code** — only the final `if($int_vehiclecount==0){...}else{...}` at `:118-138` is live, which does correctly return `success:false` with that message). A parallel commented-out duplicate-name check (`:93-99, 110-116`) was never activated.
4. `checkregnoexists($reg_number, $pkey)` (`:195-214`) and `checkexpensetypenameexists()` (`:179-194`) are separate AJAX live-validation endpoints (used by the form's onchange/onblur, presumably).
5. `delete($user_pkey)` (`:60-72`) — soft delete.
6. `search()` (`:215-229`) — **buggy**: `'(Vehicle.model_dec LIKE ...)' or '(Vehicle.reg_number LIKE ...)'` (`:225`) uses PHP's `or` between two string literals, which always evaluates to the first non-empty string — the intended OR-search condition never actually applies to `reg_number`; only `model_dec` matching effectively works.

**Forms (`View/Vehicle/form.ctp`, not read line-by-line but inferable from `save()`):** reg_number, model_dec, make, vehicle_type, make_year, fual_type (typo preserved from DB column name).

**Server-side validation:** Registration-number uniqueness (live + on submit, both against `Vehicle.status=1` rows).

**Notifications:** None.

**AJAX/JS endpoints hit:** `Vehicle/listvehicle`, `Vehicle/vehicle/:pkey`, `Vehicle/save`, `Vehicle/delete/:pkey`, `Vehicle/checkregnoexists`, `Vehicle/checkexpensetypenameexists`, `Vehicle/search`.

---

## 9. PurchaseOrderController.php — Purchase Order

**Purpose:** Convert one or more Material Requests (from the mis-named `SupplierMasterController`) into a Purchase Order sent to a supplier, tracking ordered vs. required quantities per item and a `grn_status` flag that drives whether the PO still needs goods receipt.

**Who can access it:** Global gate only, but **row-level branch scoping** for `user_group==2`: `purchaseorders()` (`Controller/PurchaseOrderController.php:92-103`) and `materialtable()` (`:153-164`) and `loadtable()` (`:470-481`) restrict visible POs/MRs to stores the logged-in employee has access to via `access_store` (joined by `emp_fkey`), further restricted to `store_master.status=1`.

**Step-by-step user flow (see also Procurement Workflow section below):**
1. `home()` (`:66-68`, view `home.ctp`) is a thin AJAX wrapper that `.load()`s `index()` into `#div-criteria1` (`View/PurchaseOrder/home.ctp:9-14`) — this is the pattern used by `GoodsReceivedNotes` too.
2. `index()` (`Controller/PurchaseOrderController.php:53-64`) loads all active items and all active `Contacts` where `relationship='Vendor'` for dropdowns, renders `index.ctp`.
3. **`index.ctp`** (`View/PurchaseOrder/index.ctp`) shows two datagrids: "Purchase Order lists" (`#purchase`, toggle-hidden by default, `:426`) fed by `PurchaseOrder/purchaseorders`, and "Material Request List" (`#materialtable`) fed by `PurchaseOrder/materialtable` (`:430-486`) showing Total Required / Total Ordered / Total Pending per MR.
4. User fills PO Number (auto `mt_rand()` placeholder, readonly, `:129`), PO Date (today, readonly), Expected Date* (required, datepicker `startDate:'+1d'` i.e. must be tomorrow or later, `:135,522-529`), Supplier Name*/Supplier Code* (autocomplete-linked, `:629-662`), Remark.
5. User selects an MR row from the Material Request grid and clicks "ADD TO PO" (`:440-475`) — **client-side gate**: alerts and blocks if Supplier Name or Expected Date is empty (`:449-456`; the Location check is present but commented out, `:457-460`).
6. This calls `test(mr_pkey)` (`:284-307`) which serializes the header form and posts to `PurchaseOrder/save` (`Controller/PurchaseOrderController.php:408-462`). On first save (no `po_fkey`) it creates the `purchase_order` header row; on subsequent adds it creates/updates a `purchase_list` link row and loops `mr_details`/`po_item_details`, incrementing `ordering_qty` on `mr_details` and setting `material_request.po_status='2'` (`:446-453`) — i.e. **MR status flips to "2" (ordered/in-progress)** as soon as any line is added to a PO.
7. Response opens a modal via `showLargeModalForm` to `PurchaseOrder/form/:mr_pkey/:po_pkey` (`:299`) — the modal (`form.ctp`) lets the user enter ordering quantity per item; not read line-by-line, but `PurchaseOrderController::form()` (`:584-629`) computes `sum(ordering_qty) - sum(required_qty)` as `$sum` (pending balance) for display.
8. `loadtable(pk, rowindex)` (`:327-340`, calling `PurchaseOrder/loadtable`, `:465-545`) refreshes the "items added" table under the header form after each add.
9. Existing POs can be reloaded into the header form by selecting a row → "SELECT TO PO" (`loaddata`, `:309-324`) or typing into the PO-number autocomplete (`easyAutocomplete`, `:566-580`).
10. "Details" toolbar button (`:396-416`) opens `PurchaseOrder/details/:po_pkey` — a read-only PO summary (`details()`, `:228-279`) joining supplier/location/company info, used for review/print.
11. **PDF/print:** `downloads($po_pkey)` (`:335-405`) renders the `download.ctp` view through HTML2PDF and forces a file download (`Output(..., 'D')`); `testdownloads($po_pkey)` (`:281-332`) is the same but additionally **side-effects `purchase_order.grn_status = '2'`** on every call (`:291`) — i.e. simply opening the print-preview marks the PO as having entered GRN stage, which is a questionable side effect for a "test/preview" action. INFERRED this is used as the "Generate/Print PO" trigger that also flags the PO ready for goods receipt.
12. **Delete:** `purchasedelete()` (`:632-645`) soft-deletes (`status=0`) by comma-separated `po_pkey` list.
13. A parallel/older single-shot multi-row save path exists — `save1()`/`addnewrow()`/`getitem1()`/`itemtotel()` (`:766-955`) — building a PO with all item rows submitted at once rather than the incremental MR→PO flow; INFERRED this is either an alternate entry point or legacy code not wired to the current `index.ctp` (the visible `index.ctp` script only calls `save`/`form`/`loadtable`/`edit`, not `save1`).

**Forms (`View/PurchaseOrder/index.ctp`):**
- Header fields: PO Number (readonly, system-generated placeholder), PO Date (readonly, today), Expected Date* (datepicker, min tomorrow), Supplier Name* / Supplier Code* (autocomplete pair, mutually clearing each other via `checksuppilername()`/`checksuppliercode()`, `:242-257`), Remark.
- **Client-side validation:** HTML5 `required` on PO Date and Expected Date inputs (`:129,135,141` — note Supplier Name/Code inputs do NOT have `required` in the DOM despite the JS alert-gate checking them); the "ADD TO PO" handler blocks with `alert()` if Supplier Name or Expected Date is blank.
- **Server-side validation:** None beyond implicit — `save()` (`Controller/PurchaseOrderController.php:408-462`) does not re-validate required fields; a PO can be created via direct POST with blank supplier if the client check is bypassed.
- Flash/toast: success shows `$.notify($.parseJSON(resp).msg, {type:'success'})` (`:557-560`) with a hard-coded success message from the server ("Added new item sucessfully" — note misspelling preserved).

**Notifications:** None (email/SMS) — in-app toast only.

**AJAX/JS endpoints hit:** `PurchaseOrder/purchaseorders`, `PurchaseOrder/materialtable`, `PurchaseOrder/save`, `PurchaseOrder/loadtable`, `PurchaseOrder/form`, `PurchaseOrder/edit`, `PurchaseOrder/details`, `PurchaseOrder/getautocompletionspo_number`, `PurchaseOrder/searchm`, `PurchaseOrder/getautocompletionslocation`, `PurchaseOrder/getautocompletionssupplier_name`, `PurchaseOrder/getautocompletionssupplier_code`, `PurchaseOrder/getautocompletionsstore_code`, `PurchaseOrder/purchasedelete`, `PurchaseOrder/deleteorder`.

---

## 10. DirectPurchaseOrderController.php — Direct Purchase Order (no MR required)

**Purpose:** A simplified, standalone PO flow that skips the Material Request step entirely — used for ad-hoc/direct purchases not tied to a store's material request.

**Who can access it:** Global gate only; no branch/store row-filtering found in this controller (unlike `PurchaseOrderController`).

**Step-by-step user flow:**
1. `index()` (`Controller/DirectPurchaseOrderController.php:58-72`) loads all active items and all active `Site` records (locations) for dropdowns.
2. `save()` (`:86-112`) creates the `direct_purchase_order` header, then looks up the item's UOM via `getitem()` (`:114-128`) and saves a `direct_po_details` line via `saveAll` — single-item-per-call pattern (repeated per add, unlike PurchaseOrder's MR-driven multi-line save).
3. `loadtable($id, $rowindex)` (`:130-190`) renders the running list of added lines with **inline edit/remove links** (`editdaata()`/`removedaata()` client JS, referencing `PurchaseOrder/deleteorder` pattern — actually calls `DirectPurchaseOrder/deleteorder`, `:264-275`).
4. `editorder($id)` (`:192-216`) / `editordersave()` (`:218-226`) — edit an existing line by `direct_po_details_pkey`.
5. `delete($id)` (`:251-262`) — soft-deletes the whole direct PO header; `deleteorder($id)` (`:264-275`) soft-deletes a single line.
6. `getautocompletionsdirect_po_number()` (`:229-249`) — PO-number typeahead, same pattern as `PurchaseOrder`.

**Key procurement-chain finding:** DirectPurchaseOrder has **no GRN linkage in code** — no controller in this scope references `direct_purchase_order`/`direct_po_details` from `GoodsReceivedNotesController.php` or vice versa, and no `grn_status`-equivalent field appears on `direct_purchase_order` in any query here. INFERRED: Direct POs are a closed-loop record (created → optionally edited/deleted) that does **not** feed into the GRN → stock-receipt pipeline the way regular POs do; goods bought via a Direct PO are not tracked into `stock_details`/`gr_item_details` by this codebase.

**Forms:** `View/DirectPurchaseOrder/index.ctp` not read line-by-line but structurally mirrors `PurchaseOrder/index.ctp` (item, qty, rate line entry against a header of PO number/date/site).

**Notifications:** None. **AJAX/JS endpoints hit:** `DirectPurchaseOrder/save`, `DirectPurchaseOrder/loadtable`, `DirectPurchaseOrder/getitem`, `DirectPurchaseOrder/editorder`, `DirectPurchaseOrder/editordersave`, `DirectPurchaseOrder/getautocompletionsdirect_po_number`, `DirectPurchaseOrder/delete`, `DirectPurchaseOrder/deleteorder`.

---

## 11. GoodsReceivedNotesController.php — Goods Received Notes (GRN)

**Purpose:** Record physical receipt of goods against an open Purchase Order into a specific store, updating `stock_details` (increasing on-hand stock) and closing out the PO's ordering/received quantities.

**Who can access it:** Global gate only, with `user_group==2` branch/store scoping via `access_store` in `index()` (`Controller/GoodsReceivedNotesController.php:59-64`), `pgrnlist()` (`:170-174`), `purchaselist()` (`:116-128`), and `getautocompletionsstore_code()` (`:607-612`) — same pattern as PurchaseOrder.

**Step-by-step user flow (continues the PO chain — see Procurement Workflow section):**
1. `home()` (`:72-74`) → AJAX-loads `index()` into `#div-criteria1` (`View/GoodsReceivedNotes/home.ctp:9-14`).
2. `index()` (`:54-70`) loads store list (branch-filtered for ESS users) and active suppliers.
3. **`index.ctp`** shows the "Goods Received Notes List" grid (`#materialtable`, fed by `GoodsReceivedNotes/pgrnlist`) and, once a GR-date and store are chosen, a "Purchase Order List" grid (`#purchaselist`, fed by `GoodsReceivedNotes/purchaselist`) listing **open POs eligible for receipt at that store**.
4. Header form fields: GR Number (readonly placeholder `mt_rand()`), GR date* (datepicker limited `startDate:'-15d'` to `endDate:'0d'` — i.e. GR date must be within the last 15 days up to today, `View/GoodsReceivedNotes/index.ctp:392-396`), Store Code* (autocomplete, store-access-scoped for ESS users, `Controller/GoodsReceivedNotesController.php:599-622`), Remark.
5. User selects a PO row, clicks "ADD TO GR" (`:330-352`) → `test(po_pkey)` (`View/GoodsReceivedNotes/index.ctp:199-234`) — **client-side gate**: alerts "Select a Store First" if store blank, "Select a GR date" if date blank — posts header to `GoodsReceivedNotes/save/:po_pkey`.
6. `save($po_pkey)` (`Controller/GoodsReceivedNotesController.php:429-484`): on first call (no `grn_fkey`) creates the `goods_receved_notes` header (note table name typo preserved in DB: "receved" not "received"). On the item-entry call it (a) sets `purchase_order.grn_status = 0` and updates `po_date` (`:456-461` — this **clears** the `grn_status=2` flag set by PO print-preview, marking the PO as GRN-in-progress/complete rather than pending), (b) loops item rows and `GrItemDetails->save()` each line, then (c) calls `UpdateStock()` per line (`:475`).
7. `UpdateStock($gritemsid, $Qty, $mr_number)` (`:486-525`) is the **stock-increment step**: joins the new GR item back to `goods_receved_notes`/`purchase_order`/`item_master` to pull store, supplier, PO number, and rate, then inserts a positive `stock_details` row (`item_qty = $Qty`) — this is the authoritative point where on-hand stock actually increases.
8. The modal form (`GoodsReceivedNotes/form/:po_key/:pk/:store`) computes, per PO line, quantity already received (`tos`, summed from `gr_item_details`) and current computed stock across three UNIONed views (`item_except_grn_allocation_view`, `grn_stock_details_date_view`, `Item_allocation_details_view`) as of today's date (`Controller/GoodsReceivedNotesController.php:296-388`) so the receiving clerk can see pending-vs-received and running stock while entering received quantities.
9. `loadtable`/`showgrndetails`/`edit`/`grndelete` follow the same list/detail/soft-delete pattern as PurchaseOrder.
10. A parallel `save1()` (`:643-689`) is an alternate single-shot multi-line save (sets `purchase_order.grstatus = 0` — a **different, likely-dead field name** than `grn_status` used elsewhere, `:651-653`) — INFERRED legacy/unused code path, not called from the read `index.ctp`.

**Forms:** header (GR Number, GR Date*, Store Code*, Remark) + dynamic item-rows table (item, ordering qty, received qty, current stock, re-order level, package) rendered by `form.ctp` (not read line-by-line; fields inferred from `save()`'s `$arr['item_code']`, `ordering_qty`, `required_qty`→`received_qty`, `current_stock`, `re_order_level`, `package`, `mr_fkeys`).

**Server-side validation:** None beyond the client-side date/store gates; `save()` does not re-check received qty against ordered qty or reject over-receipt.

**Notifications:** None found.

**AJAX/JS endpoints hit:** `GoodsReceivedNotes/pgrnlist`, `GoodsReceivedNotes/purchaselist`, `GoodsReceivedNotes/save`, `GoodsReceivedNotes/form`, `GoodsReceivedNotes/loadtable`, `GoodsReceivedNotes/showgrndetails`, `GoodsReceivedNotes/edit`, `GoodsReceivedNotes/grndelete`, `GoodsReceivedNotes/getautocompletionsgr_number`, `GoodsReceivedNotes/getautocompletionsstore_code`, `GoodsReceivedNotes/searchm`, `GoodsReceivedNotes/deleteorder`.

---

## 12. StockTranferController.php — Inter-Store Stock Transfer

**Purpose:** Move already-received stock from one store to another (distinct from GRN, which brings new stock in from a supplier).

**Who can access it:** Global gate only; no `access_store` branch scoping observed in this controller's list actions (unlike PurchaseOrder/GRN).

**Step-by-step user flow:**
1. `home()`/`index()` (`Controller/StockTranferController.php:51-66`) load all active items and stores for the transfer form.
2. `save_stock_transfer()` (`:68-140`) — **note:** despite the name, this is the primary save action used by `index.ctp` (there is a *separate* `save()` at `:610-634` used by the edit/secondary flow). Creates `stock_tranfer` header (or reuses `stock_tranfer_fkey` if already created for this session) then `stock_tranfer_item` line rows via `saveAll`. A large block writing negative/positive `stock_details` rows to actually move stock is **commented out** (`:115-137`) — meaning **stock quantities are not actually decremented/incremented by this save path**; only a `submit_return()`-style stored procedure call does the real transfer (see below).
3. `loadtabledata($id, $rowindex)` (`:143-184`) — renders the running list of transfer line items under the form.
4. `getitem_code($store_fkey, $item)` (`:936-983`) — live "available qty at source store" lookup (three-way UNION query across GRN/allocation/adjustment views, same pattern as GRN's stock computation) used while adding items to the transfer.
5. `editstoreitem($id)`/`transfer($id)` (`:735-751, 753-769`) — edit modals for a transfer line.
6. `editorder_save()` (`:771-798`) — updates a line's `required_qty` directly (the `StockDetails` sync update is commented out, `:784`).
7. `deletestoreitem($id)`/`deletestoremaster($id)` (`:820-837, 896-910`) — soft-delete (`status=2`, not `0` — a different soft-delete sentinel than most other controllers use) a line or the whole transfer; raw `DELETE` statements are present but commented out (`:828-831, 901-903`), so deletes are non-destructive.
8. **Final commit:** `submit_return($id)` (`:847-877`) — sets `stock_tranfer_item.status = 1` for all lines, then invokes a MySQL stored procedure `CALL stock_tranfer_pkey_prc('$id', @perr_msg)` (`:859`) — INFERRED this is where the actual store-to-store stock quantity movement happens (decrement source, increment destination), delegated to the DB layer rather than PHP; the equivalent PHP `StockDetails->save()` logic is commented out (`:862-872`) as a leftover before the stored-procedure approach was adopted.
9. `existingitem($storecode, $itemcode)` (`:598-608`) — sums already-requested transfer qty for a store/item pair (duplicate-request awareness).

**Forms:** header (From Store, To Store, PO reference, Adjustment/Transfer Date) + item rows (item, available qty, required/transfer qty) based on field names in `save_stock_transfer()`/`loadtabledata()`.

**Server-side validation:** None on quantity vs. availability visible in `save_stock_transfer()` itself (unlike Store's manual-adjustment `save_stock_transfer()`, which does check `stock_bal_qty_fn` before allowing the save — this is a *different* method of the *same name* in a different controller, easy to confuse during migration).

**Notifications:** None.

**AJAX/JS endpoints hit:** `StockTranfer/save_stock_transfer`, `StockTranfer/loadtabledata`, `StockTranfer/lists`, `StockTranfer/getitem_code`, `StockTranfer/editstoreitem`, `StockTranfer/transfer`, `StockTranfer/editorder_save`, `StockTranfer/editordersave`, `StockTranfer/deletestoreitem`, `StockTranfer/deletestoremaster`, `StockTranfer/submit_return`, `StockTranfer/getautocompletionsadjustment_code`, `StockTranfer/itemfilter`, `StockTranfer/loadtable`, `StockTranfer/getitem`.

---

## 13. StockReportController.php — Generic Report Engine (HR/Payroll + a few Inventory reports)

**Purpose:** This is overwhelmingly an **HR/payroll reporting controller** (salary slips, leave policy, shift policy, employee information, bank reports, payroll summary) built as a generic "criteria-driven report" engine — `changereporttype()`/`addreportcriteria()`/`loadcriteriaitems()`/`listcriteriaitems()`/`generatereport()`/`reportAudit()` (`Controller/StockReportController.php:99-233,251-490,588-634`) dynamically resolve a CakePHP model name from a `str_criteria` string and query it. A **handful of report types are inventory-related** and fall in scope here: `reportAudit()`'s `switch($type)` (`:498-...`) names `Stock` ("Stock Summary Report"), `StockDetail` ("Stock Movement Report"), `Material` ("Material Report"), `PO` ("Purchase Order Report"), `GRN` (goods received report) as recognized report types, corresponding to views `View/StockReport/stockdetails.ctp`, `stockalldetails.ctp`, `material.ctp`, `showreport.ctp` (generic PO/other), `grn.ctp`, plus `poreturn.ctp` (PO-return report), `stocktrnsfer.ctp` (stock-transfer report), `itemrate.ctp`, `itemcriteria.ctp`, and uniform-related reports (`uniformallocation.ctp`, `valueuniformallocation.ctp`, `uniformemireport.ctp` — asset/uniform-issue tracking, adjacent to Asset allocation).
- `listcriteriaitems('Store')` (`:295-313`) is store-scoped for `user_group==2` via the same `access_store` join pattern used elsewhere (INNER JOIN `access_store` on `Store.store_master_pkey`), so a non-admin user's report filter dropdown only shows stores they're allocated to.
- `get_all_items($id)` (`:192-206`) — AJAX lookup of distinct items in a store from `stock_details_view`, used to populate an item-filter dropdown for stock reports.
- `reportAudit($type, $mode)` (`:491-...`) — logs a **download-history record** (`$dataForHistory['report_type']`) before generating; this is the only "audit trail"/history-tracking behavior found across the whole scope of controllers reviewed.

**Who can access it:** Global gate only; branch/store scoping applied per-criteria-model as described above.

**Given the controller is 4,823 lines and >90% payroll/HR-report logic (leave, salary, shift, tax) out of this scope**, the inventory-adjacent report views were not traced line-by-line beyond confirming their existence and the criteria-loading mechanism above. INFERRED: end-user flow for any of the inventory reports is: pick report type from a dropdown (`changereporttype()`) → dynamically-rendered criteria pickers appear (`addreportcriteria`/`loadcriteriaitems`, AJAX-populated via `listcriteriaitems`) → "Generate" calls `generatereport()` which renders the matching `.ctp` (e.g. `grn.ctp`, `material.ctp`) → optional PDF/Excel export.

**Notifications:** None found.

**AJAX/JS endpoints hit (inventory-relevant subset):** `StockReport/changereporttype`, `StockReport/get_all_items`, `StockReport/addreportcriteria`, `StockReport/loadcriteriaitems`, `StockReport/listcriteriaitems`, `StockReport/generatereport`, `StockReport/reportAudit`.

---

## Procurement Workflow (multi-step): Material Request → Purchase Order → Goods Received Note → (optional) PO Return / Stock Transfer

This is a real, connected, multi-step approval-adjacent chain across `SupplierMasterController` (MR), `PurchaseOrderController` (PO), and `GoodsReceivedNotesController` (GRN), with `StoreController`'s PO-Return sub-flow as a downstream branch. `DirectPurchaseOrderController` is a **separate, disconnected** shortcut path (see §10) that does not participate in this chain.

**Step 1 — Material Request (`SupplierMasterController`):**
A requester creates a Material Request (`material_request` header + `mr_details` lines, each with `required_qty`) via `SupplierMaster/save` (`Controller/SupplierMasterController.php:129-160`). No status field is set at creation beyond the implicit default; `mr_details.ordering_qty` starts unset/0.

**Step 2 — Purchase Order creation from MR (`PurchaseOrderController`):**
- The PO screen (`PurchaseOrder/index`) lists open MRs (via `materialtable()`, filtered to rows where `sum(mr_details.required_qty) >= sum(mr_details.ordering_qty)` i.e. still has unordered quantity — `Controller/PurchaseOrderController.php:167-191`) alongside a supplier/date header form.
- Selecting an MR and clicking "ADD TO PO" (client-gated on Supplier Name + Expected Date being filled) calls `PurchaseOrder/save` (`:408-462`), which:
  - Creates the `purchase_order` header on first save.
  - On the item-linking call, creates/updates a `purchase_list` join row and, per item, **increments** `mr_details.ordering_qty` and sets `material_request.po_status = '2'` (`:446-453`) — this is the state transition marking the MR as (partially or fully) ordered.
  - Inserts/updates `po_item_details` rows carrying `ordering_qty`, `required_qty`, `po_rate` per item — this is the per-line record the GRN step later reconciles against.
- There is no explicit "PO Approval" step/status/role found anywhere in `PurchaseOrderController.php` — no `approved_by`, `approval_status`, or workflow-gate field is set or checked. **The closest thing to an approval-like gate is `purchase_order.grn_status`**: it starts unset, gets set to `'1'` implicitly wherever a PO is considered "open" for the `purchaseorders()` listing (`:112,114` — the grid only shows POs with `grn_status='1'`), gets forced to `'2'` merely by opening the print/download preview (`testdownloads()`, `:291`), and gets **reset to `0`** by GRN's `save()` once goods-receipt begins (`GoodsReceivedNotesController.php:456-461`). INFERRED: `grn_status` functions as a coarse three-state flag (1=open/awaiting print or receipt, 2=printed/dispatched-to-supplier, 0=receipt-in-progress) rather than a true multi-actor approval chain — there is no evidence of a second user (e.g. a manager) approving the PO before it can proceed to GRN.

**Step 3 — Goods Received Note (`GoodsReceivedNotesController`):**
- The GRN screen (`GoodsReceivedNotes/index`) lists POs open for a chosen store (`purchaselist()`, `:76-155`) — a PO only appears here if it has line items whose `mr_fkey`'s `store_code` matches the selected store.
- User picks a PO, provides a GR date (must be within last 15 days) and store, and adds received-quantity lines against each PO item (`form()`, `:287-390` — shows already-received `tos` vs. ordering qty, and current computed stock, per line, so the clerk can see partial-receipt state).
- `GoodsReceivedNotes/save` (`:429-484`):
  - Creates the `goods_receved_notes` header.
  - Sets `purchase_order.grn_status = 0` (marks PO as GRN-touched).
  - Per line: saves `gr_item_details` (received_qty, ordering_qty, po_rate, mr_fkeys) then calls `UpdateStock()` (`:486-525`), which **is the actual stock-increment**: inserts a new positive `stock_details` row keyed to store/supplier/item/rate, sourced by cross-referencing back through `gr_item_details → purchase_order/goods_receved_notes → item_master`.
  - No check exists preventing received_qty from exceeding ordering_qty (over-receipt is not blocked server-side).
- This is the "matched" point: the GRN line item directly carries the `po_fkey`/`item_code` it satisfies, and `UpdateStock()` re-derives the PO number for the stock ledger entry, so `stock_details` rows are always traceable back to their originating PO — but this traceability is implicit (via joins), not an explicit reconciliation/"3-way match" status field.

**Step 4a — Optional: PO Return (`StoreController`):**
Once goods are in stock (post-GRN), a portion can be returned to the supplier via `Store/returnitem` → `Store/save_po_return()` (`Controller/StoreController.php:719-777`), which creates a `po_return_request`/`return_gr_items` pair in a **pending state** (`status=0`) referencing the original `gr_item_details.gr_item_pkey`, alongside a negative pending `stock_details` row. `submit_return()` (`:860-872`) is the explicit commit step flipping both to `status=1`, at which point stock is actually reduced. This is a genuine two-phase (draft → submit) pattern, unlike PO/GRN which save immediately.

**Step 4b — Optional: Inter-store Stock Transfer (`StockTranferController`):**
Independently of PO Return, stock already received into one store can be moved to another store via `StockTranfer/save_stock_transfer()` → `submit_return()` (which, despite the shared name with Store's PO-return action, is a distinct method on `StockTranferController` that calls stored procedure `stock_tranfer_pkey_prc`). This is a sibling leaf off "stock exists," not part of the PO→GRN chain itself.

**Net assessment for migration:** the workflow is **linear and system-enforced by foreign-key/status-field state** (MR.po_status, PO.grn_status, GRN line ↔ PO line join) rather than by an explicit multi-actor approval workflow (no "submitted → approved → rejected" role-gated states were found for PO or GRN in this scope). The two genuinely two-phase (draft/commit) sub-flows are **PO Return** (`status=0` pending → `submit_return` → `status=1` committed) and **Stock Transfer** (`stock_tranfer_item.status=0` pending → `submit_return` → `status=1` committed, then a DB stored procedure does the actual quantity movement) — these are the closest analogues to an "approval" step, but they are self-submitted by the same user/session, not routed to a different approver.

---

## 11. Performance Management & HR Admin

# Performance Management & HR Admin — User-Facing Behavior Report

Scope: `PerformanceController`, `SelfReviewController`, `TeamReviewController`, `HierarchyReviewController`,
`SurveyController` (+ `SurveyController_bkup_megha.php` dup), `PromoController`, `FullandFinalsettlementController`,
`ResignationRequestController`, `ExceptionRuleController`.

All controllers extend `AppController`, whose `beforeFilter()` requires `Session::read('user_group')` to be `1`
(Admin/HR) or `2` (Employee/ESS); anything else redirects to `Site/login`
(`legacy/Controller/AppController.php:43-46`). None of the controllers in this scope declare a menu_id /
`user_access` check inside PHP — granular gating is done in the left-nav menu (built from `emp_menu`/`user_access`)
and by branching on `user_group` inside a handful of actions. No custom routes exist for any of these controllers —
`legacy/Config/routes.php` has no entries matching these names, so plain CakePHP default routing
(`/ControllerName/action/param1/param2`) applies throughout.

---

## 1. SelfReviewController — Self Appraisal (Executive workflow, Part I/II)

**File**: `legacy/Controller/SelfReviewController.php` (1294 lines)
**Views**: `legacy/View/SelfReview/{index,form,initaite_self_review,new_review,new_bulk_review,pdfreview,performance_report}.ctp`

### Purpose
Lets an employee (or HR on an employee's behalf) initiate and fill in a "Self Appraisal" (Part I/II of the annual
performance cycle) for executive-grade staff: duty description, work summary, and selection of Reporting/Reviewing
officers. Once submitted, it becomes the input record that `TeamReviewController` (reporting/reviewing officers)
marks and scores against.

### Who can access it
- Gated only by the blanket `user_group` 1/2 check in `AppController.php:43-46` — no menu_id/`user_access` check in
  the controller code itself. Visibility of the "Self Review"/"Initiate Self Review" menu item is presumably
  controlled by `emp_menu`/`user_access` (not verified in this controller).
- `index()` (`SelfReviewController.php:60-68`) reads `emp_fkey` from session — every logged-in user sees only their
  own review count, i.e., this is inherently self-scoped for group-2 (Employee) users; HR (group 1) can create
  reviews **for other employees** via `newReview`/`newBulkReview` (employee picker at `SelfReviewController.php:98-103`,
  `995-1000`).

### Step-by-step user flow
1. User opens `SelfReview/initaiteSelfReview` (`SelfReviewController.php:70-80`) → renders
   `View/SelfReview/initaite_self_review.ctp`, a data-grid of the user's/employee's review records
   (loaded via AJAX `SelfReview/listreviews` — `initaite_self_review.ctp:62`).
2. **New single review**: clicking "New" opens a modal loading `SelfReview/newReview`
   (`SelfReviewController.php:82-121`), which renders `View/SelfReview/new_review.ctp`. This pulls the active
   employee list (`emp_status = 1`) for HR to pick a subject employee.
3. **New bulk review** (HR only, in practice): `SelfReview/newBulkReview`
   (`SelfReviewController.php:975-1014`) renders `new_bulk_review.ctp`, letting HR select a *category*
   (`getEmployeesByCategory`, `SelfReviewController.php:1016-1047`) then multiple employees + financial year
   (`getFinYears`, `:1051-1089`) and reporting/reviewing officer, then POST to `createBulkSelfReview`
   (`:1120-1292`) which loops each employee, checks for an existing non-deleted record in either
   `self_review_details` or `assessment_attributes_staff_details` for that fin_year (`:1196-1216`), computes LOP
   absence days via `getLOP()` (`:1092-1117`), grade-entry date, and category (executive vs. workmen based on
   `category.category_code != 'WORK'` → `:1233-1241`), and inserts a `New` record per employee.
4. **Fill the appraisal form**: `SelfReview/form` (`SelfReviewController.php:739-766`, or `view()` at `:768-816`
   for edit/view of an existing record) renders `View/SelfReview/form.ctp` — a Bootstrap modal with:
   - **Duty description** (`duty_desc`, textarea, required on submit)
   - **Work-done summary** (`work_done_desc`, textarea, required, client-enforced **100-word limit**
     via `enforceWordLimit()` JS — `form.ctp:136-147`)
   - **Reporting Officer** and **Reviewing Officer** dropdowns (pre-populated, but rendered `disabled` in the
     form — so the employee cannot change officers from this screen; they're set at initiation)
   - Hidden `status` field toggled to `Draft` (Save) or `Applied` (Submit) by two buttons
     (`form.ctp:149-159`)
   - Client-side JS validation (`form.ctp:161-215`) blocks submit-with-empty duty/work fields or missing
     officers via `$.notify` toasts; Save allows partial content (at least one field non-empty).
   - Submits via AJAX POST to `SelfReview/saveSelfReview`.
5. **Save/submit handling**: `createSelfReview` (`SelfReviewController.php:689-737`) is used for the very first
   save (blocks duplicate creation per employee+fin_year — `:701-711`, flash JSON error "Record already created for
   this employee."). Subsequent saves/edits go through `saveSelfReview` (`:818-884`), which sets
   `is_applied`/`applied_by`/`applied_date` when `status == 'Applied'` (self-review "Completed") or
   `is_drafted`/`drafted_by`/`drafted_date` when `status == 'Draft'`.
6. **List/status view**: `listreviews`/`listreviews_self_review` (`:123-363`, `:365-498`) compute a human status
   label (`statusLabel`) from a priority chain of `is_reviewed → is_reported → is_applied/is_rejected → is_drafted →
   "Self Appraisal Initiated"` flags — this label is what the grid shows (e.g. "Self Review Completed",
   "Reporting person submitted the Appraisal", "Reviewing person rejected the Appraisal").
7. **Delete** (`deleteSelfReview`, `:886-919`): soft-deletes by setting `status='Deleted'` (self_review) or `0`
   (assessment_attributes_staff_details), keyed off a `table` param passed from the grid — this is a shared
   endpoint used by both executive and staff/workmen review grids.
8. **PDF export**: `previewPdfReview($emp_pkey, $pkey)` (`:922-971`) renders `performance_report.ctp` to HTML and
   converts via HTML2PDF (`html2pdf_v4.03`), downloading `SelfAppraisal.pdf`.

### Forms
- Free text (duty description, work summary — no rich text, plain textarea).
- Officer selection (single-select dropdowns, populated from `employee_info`/`emp_config` HIERARCHY/LAPPR policy
  types — `getEmployeeDetails`, `SelfReviewController.php:501-648`).
- No rating/competency matrix at this stage — marks/attributes are entered later by the officers in TeamReview /
  HierarchyReview.
- Server-side validation is minimal: `createSelfReview`/`saveSelfReview` mostly trust client payload; the only
  hard server check is the duplicate-per-fin-year guard and a missing-pkey guard
  (`SelfReviewController.php:825-831`, JSON error "Update failed: Missing review ID.").
- Flash messages are all JSON (`{status, message}`) consumed by `$.notify()` toasts client-side, not CakePHP
  `Session::setFlash` in most endpoints — except `createBulkSelfReview`/`createBulkHierarchyReview`'s
  "No employees selected." path, which does use `Session::setFlash` (`SelfReviewController.php:1134`,
  `HierarchyReviewController.php:850`).

### Notifications triggered
- No explicit `Email` component calls found inside `SelfReviewController.php` (searched for `Email->` — none).
  Notification of the next-step officer appears to rely on them polling `TeamReview/listrequest` rather than a
  push email. INFERRED: no email is sent on self-review submission.

### AJAX/JS endpoints hit from these views
- `SelfReview/listreviews`, `SelfReview/listreviews_self_review` (grid data)
- `SelfReview/view/{edit}/{pkey}`, `SelfReview/newReview`, `SelfReview/newBulkReview` (modal content loads)
- `SelfReview/saveSelfReview`, `SelfReview/createSelfReview`, `SelfReview/createBulkSelfReview`
- `SelfReview/deleteSelfReview`
- `SelfReview/getEmployeeDetails`, `SelfReview/getAbsencePeriod`, `SelfReview/getEmployeesByCategory`,
  `SelfReview/getFinYears`
- `SelfReview/previewPdfReview/{emp_pkey}/{pkey}` (PDF download, direct link not AJAX)
- Cross-controller: `TeamReview/previewPdfReviewHR/...`, `HierarchyReview/previewPdfReview/...` links from
  `initaite_self_review.ctp:146-148`

---

## 2. TeamReviewController — Reporting/Reviewing Officer Assessment (Executive)

**File**: `legacy/Controller/TeamReviewController.php` (1465 lines)
**Views**: `legacy/View/TeamReview/{index,approverequest,view_form,performance_report,performance_view}.ctp`

### Purpose
The second stage of the executive appraisal cycle: once an employee's self-review is `Applied`, their **Reporting
Officer** then their **Reviewing Officer** score the employee against a fixed set of attributes
(`assessment_attributes_executive`), write comments/training-needs, and the record is either approved onward or
sent back.

### Who can access it
- Same blanket `user_group` 1/2 gate only (`AppController.php:43-46`); no menu_id check in controller.
- Role (`reporting_officer` vs `reviewing_officer`) is derived dynamically per-request by comparing
  `Session::read('emp_fkey')` against `self_review_details.reporting_officer`/`reviewing_officer`
  (`TeamReviewController.php:36-42`, `803-810` "edited by athira on 04-07-2025" — role now taken directly from
  query params instead of DB join, `:797-809`). There is no authorization check preventing an arbitrary employee
  from hitting `approverequest?pkey=X` other than the UI not surfacing the link — INFERRED this is a
  security gap worth flagging for the Next.js rewrite (server should verify the requesting user is actually the
  reporting/reviewing officer on that record before allowing writes).

### Step-by-step user flow
1. Officer opens `TeamReview/index` (`TeamReviewController.php:13-46`) which determines their `role` for display
   purposes and renders `View/TeamReview/index.ctp`, a grid of pending requests loaded via
   `TeamReview/listrequest?status=...&role=...&position=...` (`index.ctp:219`).
2. `listrequest` (`TeamReviewController.php:257-395`) builds a big status filter: "left" tab shows items where
   the officer still owes action (`Applied`/`Reporting Person Drafted...`/`Reviewing Person Rejected...` for
   reporting officer; `Reporting Person submitted...`/`Reviewing Person Drafted...` for reviewing officer), the
   other tab shows submitted/awaiting-next-step items (`:298-306`).
3. Clicking a row opens `TeamReview/approverequest?emp_fkey=...&pkey=...&reporting_officer=...&reviewing_officer=...`
   (`index.ctp:127,153`) → `approverequest()` (`TeamReviewController.php:790-952`) loads:
   - The self-review free-text (`duty_desc`, `work_done_desc`) read-only (`:842-849`)
   - Employee identity block (name/designation/employee_id — `:853-862`)
   - Existing attribute marks (`assessment_attributes_executive_details` joined to
     `assessment_attributes_executive`, `:889-897`)
   - Reporting/Reviewing officer's prior summary rows if present (`:899-926`)
   Renders `View/TeamReview/approverequest.ctp`.
4. **Assessment form** (`approverequest.ctp`): for each attribute row, a `<input type="number" step="any"
   required>` marks field ("Each attribute carries 5 Marks" — `approverequest.ctp:88`); a required "Does the
   Reporting Officer agree with statement in Part II" textarea (`:92-100`); a Training Need Assessment textarea
   (`maxlength=600`); a Comments & Recommendation textarea (`maxlength=600`); auto-computed Total Marks and Grade
   fields (hidden inputs populated by client JS, grading table shown for reference:
   Above 90 = Outstanding, 80–90 = Very Good, 60–80 = Good, 40–60 = Average, ≤40 = Below Average —
   `approverequest.ctp:217-243`). Reviewing Officer additionally sees the Reporting Officer's marks read-only
   alongside their own editable column (`:118-166`).
5. Form POSTs to `TeamReview/saveRequest` (`TeamReviewController.php:397-787`), which branches on
   `action_type`:
   - `draft` (`:444-574`): saves attribute marks + summary with status
     `Reporting/Reviewing Person Drafted the Appraisal`, resets flags, does not advance workflow.
   - `send_back` (`:577-622`): sets `is_rejected=1`, status `Reporting/Reviewing Person Rejected the Appraisal`,
     resets `reporting_officer_status`/`reviewing_officer_status` to 0 — sends the record back to the employee/
     prior officer to redo.
   - default (submit): for the reviewing officer, **server-side gate** checks that
     `reporting_officer_status == 1` before allowing the reviewing officer's marks to save
     (`:647-657`, JSON error "Reporting Officer has not completed the assessment yet.") — this enforces the
     sequential reporting→reviewing order. Saves marks per-attribute (insert or update), then upserts
     `assessment_summary_executive` and updates `self_review_details.status` to
     `Reporting/Reviewing Person submitted the Appraisal` (`:746-776`).
6. **View-only mode**: `TeamReview/viewForm` (`:955-1033`) shows a read-only summary for an officer who already
   submitted; `previewDocumentReview` (`:1191-1326`) renders the same content in-browser
   (`performance_view.ctp`) rather than as a PDF.
7. **PDF export**: three variants — `previewPdfReview` (officer-facing, `:1035-1188`), `previewPdfReviewHR`
   (HR-facing, includes `status` — `:1330-1463`), both render `performance_report.ctp` via HTML2PDF and download
   `ExecutiveDetails.pdf`.

### Forms
- Numeric marks input per attribute (`type="number" step="any"`, no explicit max enforced client-side beyond
  the informational "5 marks" text — server does not clamp to 0–5 either; INFERRED validation gap).
- Free text: agreement-with-Part-II (required for reporting officer), training need (≤600 chars), comments
  (≤600 chars).
- Total marks / Grade are computed client-side (JS not fully inspected beyond the hidden-input pattern) and sent
  as hidden fields — server trusts the client-computed total/grade values (`saveRequest`, `:700-704`), another
  INFERRED trust boundary to reconsider in the rewrite.
- Server-side: the reporting→reviewing sequential gate described above is the only workflow-integrity check found.

### Notifications triggered
- No `Email->send` calls found in `TeamReviewController.php`. INFERRED: officers discover pending work only by
  polling the `TeamReview/index` grid, not via email/push notification.

### AJAX/JS endpoints hit from these views
- `TeamReview/listrequest`, `TeamReview/saveRequest`, `TeamReview/approverequest`, `TeamReview/viewForm`,
  `TeamReview/previewDocumentReview/{pkey}/{self_pkey}`, `TeamReview/previewPdfReview/...`,
  `TeamReview/previewPdfReviewHR/...`

---

## 3. HierarchyReviewController — Staff/Workmen Assessment (non-executive grade)

**File**: `legacy/Controller/HierarchyReviewController.php` (1257 lines)
**Views**: `legacy/View/HierarchyReview/{index,form,self_review_workmen,performance_report,performance_view}.ctp`

### Purpose
Parallel workflow to TeamReview but for **workmen/staff-category** employees (`category_code == 'WORK'`,
distinguished in `SelfReviewController.php:1241`/`HierarchyReviewController.php` context). Uses a separate table
set: `assessment_attributes_staff_details` / `assessment_attributes_staff_item` /
`assessment_attributes_staff` instead of the executive equivalents, and a numeric `status` code (not a string
label) to track workflow stage: `1=Drafted, 2=Reported/awaiting review, 3=Reviewed, 5=Initiated/new,
0=Deleted` (inferred from `createHierarchyReview:383`, `saveHierarchyReview:483-504`,
`deleteHierarchyReview:604`).

### Who can access it
- Blanket `user_group` gate only; no menu_id check found in controller.
- `view()` (`HierarchyReviewController.php:245-368`) determines `is_reviewing_officer` by comparing session
  `emp_fkey` to the record's `reviewing_officer` (`:308-317`) to decide which parts of the form render editable —
  same "no server enforcement beyond UI hiding" pattern as TeamReview.

### Step-by-step user flow
1. Employee/HR opens `HierarchyReview/index` (`:60-68`) → `initaite`-style list via `listreviews`
   (`:70-187`, AJAX from `HierarchyReview/listreviews`) — filtered so a user sees records where they are the
   `reporting_officer`, or the `reviewing_officer` **only once status >= 2** (i.e., reviewing officer can't see it
   until the reporting officer has acted) (`:92-99`, `:122`).
2. There's also a **workmen self-review view**: `selfAppraisalWorkmen` (`:1125-1136`) renders
   `self_review_workmen.ctp` and lists via `listreviewsWorkmen` (`:1138-1255`, filtered to `emp_fkey = current
   employee` — i.e. the employee's own record, distinct from the officer's queue).
3. **Create**: `createHierarchyReview` (`:370-452`) — duplicate-per-employee-per-finyear guard identical in
   pattern to SelfReview (`:391-401`, JSON error "Record already created for this employee."), sets
   `status = 5` (Initiated), looks up reporting/reviewing officer candidates from `emp_config` LAPPR/HIERARCHY
   policy types (unused in the actual save — commented out at `:426-427`, so officer assignment appears to happen
   elsewhere, e.g. bulk-create).
4. **Bulk create** (HR): `createBulkHierarchyReview` (`:837-986`) — same pattern as SelfReview's bulk create:
   selects employees, verifies fin_year validity per branch, skips employees with an existing non-deleted record,
   computes LOP/absence and grade-entry-date, inserts with explicit `reporting_officer`/`reviewing_officer` and
   `status = 1`.
5. **Fill the form**: `view($edit, $attr_staff_details_pkey, $emp_fkey_string)` (`:245-368`) loads existing marks
   per attribute (`assessment_attributes_staff_item`, keyed by `attributes_staff_fkey`) into a `default_data` array
   with keys like `reporting_officer_marks_{attr_fkey}`, and renders `form.ctp` (692 lines — attribute matrix with
   marks/grade/training-needs/comments fields per officer role, same shape as TeamReview's approverequest but
   for the staff attribute set).
6. **Save**: `saveHierarchyReview` (`:454-590`) — status-driven flag setting:
   - `status == 1`: `is_drafted=1` (draft save)
   - `status == 2`: if current user is the reporting officer → `is_reported=1`; if reviewing officer →
     (oddly) also sets `is_drafted=1` (`:493-497` — looks like a possible bug/quirk: reviewing officer submitting
     at status 2 is treated as a draft, not a review-submission; flag for product owner)
   - `status == 3`: if reviewing officer → `is_reviewed=1`
   Then saves the parent record and iterates `reporting_officer_marks_*` hidden fields into
   `assessment_attributes_staff_item` rows (insert-or-update per attribute), defaulting missing marks to
   `'Below Average'` grade (`:461-464`).
7. **Send back**: `sendBackReview` (`:620-691`) — sets `status = 1`, `is_rejected=1`, resets other `is_` flags.
8. **Delete**: `deleteHierarchyReview` (`:592-618`) — soft delete, `status = "0"`.
9. **PDF**: `previewPdfReview($pkey)` (`:693-834`) renders `performance_report.ctp` (HierarchyReview view path) to
   PDF `StaffDetails.pdf`; computes `hideReporting`/`hideReviewing`/`hideEntireTable` flags based on who drafted
   the record, to selectively hide sections that shouldn't show yet (`:709-719`).
   `previewDocumentReview($pkey)` (`:1012-1121`) is the equivalent in-browser HTML view
   (`performance_view.ctp`).

### Forms
- Same shape as TeamReview: per-attribute numeric marks + designation/training/comments free text — but the
  attribute set comes from `assessment_attributes_staff` (a different master list from the executive one).
- `getDesignation` AJAX (`:226-243`) auto-fills an employee's designation into the form when selected.
- Grade defaults to `'Below Average'` server-side if marks are empty (`:461-464`) — a fallback that silently
  scores an unmarked reporting officer submission as the worst grade; worth flagging as a UX gap (should probably
  block submit, not auto-fail).

### Notifications triggered
- No `Email->send` calls found in this controller. INFERRED: none.

### AJAX/JS endpoints hit from these views
- `HierarchyReview/listreviews`, `HierarchyReview/listreviewsWorkmen`, `HierarchyReview/view/{edit}/{pkey}/{empkey}`,
  `HierarchyReview/getDesignation`, `HierarchyReview/createHierarchyReview`, `HierarchyReview/createBulkHierarchyReview`,
  `HierarchyReview/saveHierarchyReview`, `HierarchyReview/sendBackReview`, `HierarchyReview/deleteHierarchyReview`,
  `HierarchyReview/previewPdfReview/{pkey}`, `HierarchyReview/previewDocumentReview/{pkey}`

---

## Performance Review Cycle (multi-step) — confirmed chain

The three controllers above form one continuous appraisal pipeline, branching by employee category:

```
                         SelfReviewController
                          (employee fills Part I/II:
                    duty_desc + work_done_desc, picks
                    Reporting Officer + Reviewing Officer)
                                  |
                     status: New -> Draft -> Applied
                     table: self_review_details
                                  |
             category_code == 'WORK'?  -----------------------------
                  NO (executive)             YES (workmen/staff)    |
                    |                                               |
          TeamReviewController                          HierarchyReviewController
   (reads self_review_details;                    (own record: assessment_attributes_
    writes assessment_attributes_                  staff_details / _item, status is
    executive_details + _summary)                   numeric 1/2/3/5 not string)
                    |                                               |
   Reporting Officer scores first                    Reporting Officer scores first
   (saveRequest, role=reporting_officer,              (saveHierarchyReview, status 1/2)
    sets self_review_details.status =
    'Reporting Person submitted the Appraisal')
                    |                                               |
   Server BLOCKS Reviewing Officer                   No equivalent explicit block found
   until reporting_officer_status==1                 in HierarchyReview (INFERRED gap)
   (TeamReviewController.php:647-657)
                    |                                               |
   Reviewing Officer scores second                    Reviewing Officer scores
   (status = 'Reviewing Person submitted                (status 3, is_reviewed=1)
    the Appraisal') -> workflow "complete"
                    |                                               |
        Either officer can "send_back" / "sendBackReview" at their stage,
        which flips is_rejected=1 and returns the record to the employee/
        prior officer (status becomes "...Rejected the Appraisal" / staff status=1)
                    |                                               |
     HR views/exports final record via PerformanceController's
     "Workflow - Executives" / "Workflow - Staff" report types
     (PerformanceController.php:997 generateSelfAppraisalReport,
      :1446 generateStaffAssessmentReport) and per-record PDF exports
      (SelfReview/previewPdfReview, TeamReview/previewPdfReview[HR],
       HierarchyReview/previewPdfReview)
```

Key evidence for this chain:
- `TeamReviewController.php:13-46` — role (`reporting_officer`/`reviewing_officer`) is derived from
  `self_review_details.reporting_officer`/`reviewing_officer`, proving TeamReview operates directly on
  SelfReview's records (no separate initiation step for executives).
- `TeamReviewController.php:746-776` — TeamReview writes back into `self_review_details.status`, closing the loop
  visible in SelfReview's own list/status labels (`SelfReviewController.php:207-225` reads exactly those same
  status strings back out for display).
- `HierarchyReviewController.php:908-928` (inside `createBulkHierarchyReview`) explicitly checks for an existing
  `SelfReviewDetails` record for the same employee+fin_year before creating a staff assessment — i.e. the two
  workflows are mutually exclusive per employee per year, driven by category.
- `SelfReviewController.php:1241` / `HierarchyReviewController` bulk-create logic both derive
  `category = ($category_code !== 'WORK') ? 'employee' : 'hierarchy'` from `grade → category` lookup — this is the
  literal branch point deciding whether an employee's review flows through TeamReview or HierarchyReview.
- HR's cross-links in `SelfReview/initaite_self_review.ctp:146-148` route to either
  `TeamReview/previewPdfReviewHR` or `HierarchyReview/previewPdfReview` depending on the row, confirming HR sees
  both branches from one combined list.

No sign-off / "final approval" step beyond the Reviewing Officer's submission was found — INFERRED the appraisal
is considered complete once the reviewing officer (or, for staff, `is_reviewed=1`) submits; there's no additional
HR countersign action in these controllers (HR only *views/exports* via PerformanceController's report screens).

---

## 4. PerformanceController — HR Reporting Layer (not a workflow controller)

**File**: `legacy/Controller/PerformanceController.php` (2111 lines)
**Views**: `legacy/View/Performance/{hrreports,showreport,showcriteria,loadcriteriaitems,reportsummary,pdfemployeemarks}.ctp`

### Purpose
A generic HR report-builder screen, **not** part of the appraisal data-entry flow. It lets HR pick a report type
(`hrreports()`, `PerformanceController.php:12-24`: `EmployeeMarks` (Executives), `EmployeeMarksStaff`,
`SelfAppraisal` ("Workflow - Executives"), `StaffAssessment` ("Workflow - Staff"), `AnnualPerformance`), add
filter criteria (employee/branch/etc.), and generate a report as an on-screen table, PDF, or Excel export. This is
the HR-facing read/export view onto the SelfReview → TeamReview / HierarchyReview data described above.

### Who can access it
- Blanket `user_group` 1/2 gate only. One branch-scoping check exists: in `listcriteriaitems()`
  (`:104-190`), when building the "Employees" filter-picker list, if `user_group == 2` (Employee), results are
  restricted to the current employee's own branch (`:147-153`) — i.e. a non-admin building a report criteria list
  only sees employees in their own branch, a soft data-scoping rule rather than a hard access block.

### Step-by-step user flow
1. `Performance/hrreports` (`:12-24`) — pick a report type from a dropdown.
2. `changereporttype($type)` (`:26-69`) — AJAX-loads available filter criteria for that type from
   `ReportCriterias` and available financial years, renders `showreport.ctp`.
3. `addreportcriteria`/`loadcriteriaitems`/`listcriteriaitems` (`:71-190`) — dynamic "add another criteria" UI
   pattern typical of CakePHP 2.x HR report builders (branch, employee, category, status filters).
4. `generatereport($type, $mode)` (`:270-297`) dispatches to one of five report generators based on `$type`
   (`generateEmployeeMarksReport`, `generateEmployeeMarksStaffReport`, `generateSelfAppraisalReport`,
   `generateStaffAssessmentReport`, `generateAnnualPerformanceAssessment`), each supporting `mode` of
   `pdf`/`excel`/on-screen view.
5. Every report generation call is preceded by `reportAudit($type, $mode)` (`:191-268`, called from
   `generatereport:296`) which writes an audit trail row to `ReportAudit` recording who ran the report, with what
   filters, in what mode, and when — a compliance/audit-log feature.

### Forms
- Dynamic criteria-builder UI (branch/employee/category pickers), no rating or free-text fields — this
  controller is read-only reporting over data entered elsewhere.

### Notifications
- None found; `ReportAudit` is a passive audit log, not a notification.

### AJAX/JS endpoints
- `Performance/changereporttype`, `Performance/addreportcriteria`, `Performance/loadcriteriaitems`,
  `Performance/listcriteriaitems`, `Performance/generatereport/{type}/{mode}`

---

## 5. SurveyController — Facility/Equipment Service Ticketing (not an employee-satisfaction survey)

**File**: `legacy/Controller/SurveyController.php` (871 lines); duplicate `SurveyController_bkup_megha.php`
(826 lines, appears to be a stale backup — same class shape, older logic; treat as dead code, not a live route).
**Views**: `legacy/View/Survey/*.ctp` (survey_category, options, options_item_values, tickets, tickets_filter,
reports, preview, EquipmentType.php)

### Purpose
Despite the name, this is **not** an employee pulse/engagement survey. It's a configurable "Equipment/Facility
Service Request" (EFSR) ticketing module: HR/admin defines `SurveyType`s tied to `equipment_type`
(`SurveyController.php:65-81`), `SurveyCategory` groupings and ordering (`:169-297`), and `OptionItems`/
`OptionItemsValues` (dropdown option catalogs used inside tickets, `:299-524`), then employees/technicians raise
and track `tickets` (`:524-621`) against equipment at a site (`EfsrSite`, `EfsrEquipmentsMaster` models in
`$uses`), with a `reports`/`preview` PDF output (`:612-871`, uses Dompdf per file header `:3-6,31`, distinct from
HTML2PDF used elsewhere).

### Who can access it
- Blanket `user_group` gate only; no menu_id check in controller code.

### Step-by-step user flow (config side, HR/admin)
1. `Survey/index` → manage Survey Types (`form`, `surveyTypesList`, `saveSurveyType`, `deleteSurveyType`).
2. Survey Categories nested under a type, with explicit ordering/swap (`getCategoryOrder`,
   `swapSurveyCategoryOrder` — drag-reorder UI pattern, `:214-241`).
3. Options and Option Item Values nested under categories, similarly orderable (`getOptionsOrder`,
   `swapOptionItemOrder`).

### Step-by-step user flow (ticket side, requester)
1. `Survey/tickets` / `tickets_filter` (`:524-535`) — list/filter tickets.
2. `getEquipments` (`:536-563`) — AJAX equipment picker (feeds the ticket form, not shown in this scope's read).
3. `getTickets($status)` (`:564-611`) — status-filtered ticket grid.
4. `preview($ticket_id, $status)` (`:612-621`) and `reports($ticket_id, $preview)` (`:688-871`) — Dompdf-rendered
   service report / ticket printout, including `getLastServiceHtml` (`:639-687`) which pulls the most recent
   service record for an equipment for historical context in the printout.

### Forms
- Survey Type form: `type_code`, `type_name`, `equipment_type_fkey` (dropdown of active equipment types).
- Category/Option forms: name, code, ordering fields.
- Ticket-side form fields not directly read in this pass (out of the nine target controllers' core scope) —
  INFERRED to include equipment selection, issue description, category/option selections based on the master-data
  structure above.

### Notifications
- Not established in this pass; no `Email->` calls seen in the portion of the file read. INFERRED: none found,
  but ticket workflows in other HR systems commonly notify assignees — would need the unread portion of
  `tickets`/ticket-save actions to confirm.

### AJAX/JS endpoints
- `Survey/surveyTypesList`, `Survey/saveSurveyType`, `Survey/deleteSurveyType`, `Survey/getSurveyCategory`,
  `Survey/saveSurveyCategory`, `Survey/deleteSurveyCategory`, `Survey/getOptionsList`, `Survey/saveOption`,
  `Survey/getTickets`, `Survey/getEquipments`, `Survey/preview/{id}/{status}`, `Survey/reports/{id}/{preview}`

**Note for migration**: `SurveyController_bkup_megha.php` should be excluded from the Next.js port — it is a
CakePHP-convention backup file left in the controller directory (same as the `.ctp#backup_*` files found
throughout the codebase), not a live alternate route.

---

## 6. PromoController — Employee Promotion Workflow

**File**: `legacy/Controller/PromoController.php` (810 lines)
**Views**: `legacy/View/Promo/{promotion,promotion_home,promotionjoin,approvepromotion}.ctp`

### Purpose
Lets HR (and, per one branch, employees for their direct reports) submit a promotion/change request for an
employee (new designation, department, grade, branch, shift, leave policy, salary, notice period, CTC/salary
structure, reporting hierarchy), route it for approval, and on approval, **fan the change out** to the
employee's live master-data records (`emp_proff`, `emp_config`, `emp_ctc_transaction`) via a set of `changeX`/
`addToX` helper methods.

### Who can access it
- Blanket `user_group` gate only.
- `getautocompletions_superior()` (`:58-104`) — if `user_group == '2'` (Employee), the "superior"/employee
  autocomplete is filtered to only employees where `EmployeeProfessionalDetails.attr1 = current emp_fkey`
  (`:84-87`) — i.e. an Employee-group user can only search among **their own direct reports**, restricting
  self-service promotion requests to a manager's own team.
- `listemployees()` (`:253-309`) — the "pending my approval" grid is hard-filtered to
  `approved_by = current emp_fkey AND promotion_status = 'APPLIED'` (`:287`), so each approver only ever sees
  requests routed to them.

### Step-by-step user flow
1. `Promo/promotion($emp_fkey)` (`:106-251`) — the main promotion request form. Loads *extensive* master-data
   dropdown lists (departments, designations, grades, verticals, branches, holiday groups, shift/working-day
   procedures, leave-policy groups, salary structures) plus the employee's current structure/CTC/professional
   record, and a `emp_config_history` audit trail (with a company-code-specific filter excluding
   `support`-prefixed system users, unless the company is `GLET` — `:132-149`, a per-tenant customization).
   Renders either `promotionjoin.ctp` or `promotion.ctp` depending on company code allowlist (`:242-250`) —
   two different UI variants per tenant.
2. `listemployees()` (`:253-309`) — grid of promotion requests awaiting the current user's approval
   (`Promotion.status=1 AND approved_by=me AND promotion_status='APPLIED'`).
3. `savepromotions()` (`:315-341`) — saves a new promotion request with `promotion_status = 'APPLIED'`.
   Note: this method references an undefined `$result` variable in its return (`:340`) — a latent PHP notice/bug.
4. `approvepromotion($promo_pkey)` (`:343-486`) — approver's detail view, loading the full proposed-change record
   joined against designation/department/shift/leave/salary/branch master tables, plus the employee's current
   qualifications and notice-period options, to compare old vs. new.
5. `approvesave()` (`:488-541`) — on approval (`approved_status='Y'`, `promotion_status='APPROVED'`), **cascades**
   the change into live records by calling, conditionally per populated field: `chnageType` (emp_type),
   `changeDesignation`, `changeDepartment`, `changeBranch`, `addToShift`, `addToLeave`, `promotionWage` (CTC),
   `addToSuperior` (reporting hierarchy), `addToSalary` (salary structure — also invokes a DB stored function
   `sal_structure_distribution_fn` via `UpdateSalary()`, `:753-765`). Each of these individually
   soft-closes (`status=0`) the prior `emp_config` row for that policy type before inserting the new one
   (audit-trail pattern, e.g. `:558,590,621,643,668,693`).
6. `rejsave()` (`:791-808`) — rejection path, sets `promotion_status = 'REJECTED'`, no cascade.

### Forms
- Large multi-section form: designation, department, grade, vertical, branch, holiday group, shift, leave policy,
  salary structure, gross/CTC amount, reporting hierarchy ("superior"), remarks.
- Server-side validation is essentially absent — most `save()` calls are wrapped in bare `try/catch` that swallow
  exceptions and return a generic failure JSON (e.g. `:602-604`, `:627-629`) without field-level validation
  messages.

### Notifications
- No `Email->` calls found in `PromoController.php`. INFERRED: none — approver must discover pending items via the
  `listemployees` grid.

### AJAX/JS endpoints
- `Promo/getautocompletions_superior`, `Promo/listemployees`, `Promo/savepromotions`, `Promo/approvesave`,
  `Promo/rejsave`, `Promo/approvepromotion/{promo_pkey}`

---

## 7. ExceptionRuleController — Attendance Exception Rule Engine

**File**: `legacy/Controller/ExceptionRuleController.php` (1390 lines, header comment: "create by bindu
17-10-2025" — a recently-added module)
**Views**: `legacy/View/ExceptionRule/{index,newform}.ctp`

### Purpose
Not a review/HR-lifecycle screen — it's an **attendance-policy rule builder**: HR defines rules such as "if an
employee is late/absent beyond N days or M minutes, X times, then apply Loss-of-Pay or deduct from a specific
leave type," then applies a chosen rule against a branch + month of attendance data by invoking a MySQL stored
procedure.

### Who can access it
- Blanket `user_group` gate only; no menu_id check found.

### Step-by-step user flow
1. `ExceptionRule/index` (`:11-29`) — lists active rules (`activate_status=1`) plus a branch dropdown.
2. `newForm()` (`:63-68`) / `getRuleById($id)` (`:148-178`) — both render `newform.ctp`, used for both create and
   edit (edit pre-populates `rule` from the DB).
3. `saveRule()` (`:71-147`) — validates only that `ruleName` and `ruleType` are non-empty
   (`:84-89`, JSON error "Invalid or empty data. Please fill all required fields."); computes `leave_detect_type`
   as a fixed LOP code `105` if `actionException === 1` (Loss of Pay), else the selected leave type or `0`
   (`:112-117`); `detect_count` is rounded to the nearest 0.5 via a locally-declared `customRound()` helper
   (`:91-101`, re-declared again inside `updateRule()` at `:192-197` — duplicate function definition, a code
   smell rather than a bug since it's function-scoped).
4. `updateRule()` (`:180-234`) — same shape, reads raw JSON body via `file_get_contents("php://input")` rather
   than `$this->request->data` (inconsistent with `saveRule`'s handling — worth normalizing in the rewrite).
5. `getAllRules()` (`:236-304`) — paginated grid with `status=0` filter (i.e. lists rules that are NOT yet
   "processed/closed" — the field semantics of `status` vs `activate_status` are distinct: `activate_status`
   toggles whether a rule is usable, `status` appears to track a separate lifecycle state).
6. `applyRule()` (`:630-783`) — the core action: given `branch_code`, `rule_id`, `month_start`, it:
   - Blocks if attendance for that branch+month is already verified/finalized
     (`attendance_register` has a row for that month, `:665-682`, error "Attendance already verified for this
     month. Rule cannot be applied.")
   - Blocks duplicate application of the same rule to the same branch+month (`:684-701`)
   - Blocks applying **any** rule a second time to the same branch+month (`:702-723`, different error message
     naming the already-applied rule)
   - Calls MySQL procedure `exception_rule_apply_prce(branch_code, month_start, rule_id, user_login, @p_output)`
     (`:727-737`) and surfaces its output message back to the UI
   - Inserts an audit row into `exception_applied` (`:750-757`)
7. `reverseAppliedRule` (`:1312+`), `processAndGetLogs` (`:785+`), `downloadExceptionExcel` (`:1155+`) — reversal,
   log viewing, and Excel export of applied-rule results (not read in full detail in this pass).

### Forms
- Rule form fields: `ruleName` (text), `ruleType` (select), `dataType` (select/int), `exceptionDays` (int, OR)
  `exceptionTimeLimit` (float minutes) — mutually exclusive trigger conditions, `actionException` (select:
  0=Leave Deduction, 1=Loss of Pay), `leaveType` (select, only relevant when action=Leave Deduction),
  `countDetection` (float, rounded to nearest 0.5), `resetCheckbox`/`activateCheckbox` (booleans).
- Client-side validation not inspected (view file not read in full); server-side validation is minimal
  (non-empty name/type only).

### Notifications
- None found.

### AJAX/JS endpoints
- `ExceptionRule/getActiveRulesList`, `ExceptionRule/saveRule`, `ExceptionRule/getRuleById/{id}`,
  `ExceptionRule/updateRule`, `ExceptionRule/getAllRules`, `ExceptionRule/checkRuleName`,
  `ExceptionRule/applyRule`, `ExceptionRule/processAndGetLogs`, `ExceptionRule/downloadExceptionExcel`,
  `ExceptionRule/reverseAppliedRule`, `ExceptionRule/deleteRule`

---

## Full & Final Settlement + Resignation — Offboarding Workflow (confirmed connection)

**Controllers**: `legacy/Controller/ResignationRequestController.php` (660 lines),
`legacy/Controller/FullandFinalsettlementController.php` (542 lines)
**Views**: `legacy/View/ResignationRequest/{index,addeditleave,emprequests,letter,letteredit}.ctp`,
`legacy/View/FullandFinalsettlement/{index,assets,get_complete}.ctp`

### Purpose
End-to-end employee exit process: employee submits a resignation → manager/HR review & approve with a notice
period → HR runs the leave/asset/attendance reconciliation → Full & Final settlement is computed and the
employee's `emp_details.status` is flipped to `2` (resigned/inactive).

### Confirmed connection (evidence)
- `ResignationRequestController::Saverequests()` (`ResignationRequestController.php:610-659`) — on submitting a
  resignation, it inserts a matching row directly into the **`termination`** table (`:633-634` /`:643`) — the
  exact same table `FullandFinalsettlementController::Terminate()` (`FullandFinalsettlementController.php:124-148`)
  and `details_res()` (`:149-170`) read from. This is the literal join point between the two controllers.
- `FullandFinalsettlementController::Terminate()` (`:124-148`) sets `emp_details.status = '2'`
  (`:146`) — the same status value `ResignationRequestController::index()` (`:61` via
  `EmployeeDetails->find(..., conditions: status=2)`) filters on to distinguish "resigned" employees, and the same
  value read in `PromoController` context elsewhere. This is the canonical "employee has exited" flag across the
  app.
- `ResignationRequestController::withd()` (withdraw request, `:513-551`) and `deleteresignation()` (`:593-608`)
  both null out (`status=0`) rows in **both** `resignation_requests` and `resignation_accept` **and**
  `termination`, confirming these three tables are treated as one linked lifecycle, not independent modules.

### Step-by-step user flow
1. **Employee submits resignation**: `ResignationRequest/index` (employee's own view, `:56-94`) shows their
   existing request + `ResignationAccept` approval status + any `termination` record. New submission goes through
   `Saverequests($leaveentryId)` (`:610-659`):
   - Fields: `Reason`, `Reason_Desc`, `Last_workingday`, implicit `applied_date = today`.
   - Guards against duplicate active resignation: only inserts if no existing non-cancelled
     `resignation_requests` row and no `termination` row exists for the employee (`:620`); otherwise, if a prior
     terminated/cancelled `termination` row exists (`status=0`), a **new** `termination` row is inserted rather
     than reusing the old one (comment: "inserting new request as new row", `:638`) — this looks like intentional
     re-application-after-withdrawal support.
   - `letter()`/`letteredit()` (`:345-511`) render an auto-populated resignation letter (employee details,
     company info, addressed to the `authorised_to` manager) — a printable/PDF-style letter view.
2. **Manager/HR reviews**: `ResignationRequest/addeditleave($emp)` (`:136-267`) — the **approver's** detail+action
   modal (rendered from `addeditleave.ctp`), joining `ResignationRequests` + `ResignationAccept` +
   employee/branch/designation/department master data. Two sub-forms live in this one view
   (`addeditleave.ctp:87-160` and `:181-265`):
   - **Manager approval block** (`isauthorized` field, `:149`): `comments_to_emp` (required textarea),
     `manager_reason`, `comments_to_hr`, `last_allowed_date` (required — the approved last working day),
     employee-search-driven `forwarded` (who to forward to, e.g. HR) and `handover_to` (successor) hidden fields
     populated via an employee-search autocomplete UI (`ResignationRequest/getusers` AJAX, `:106-133`).
   - **HR/final approval block** (`isApproved` field, `:251`): three checkboxes — "completed all formalities,"
     "retrieved all assets," "calculated leave encashment / F&F amount" (`addeditleave.ctp:231-237`) — plus
     `hr_comment` — this is the explicit hand-off gate from manager-approval to F&F settlement readiness.
   - Both blocks submit to `ResignationRequest/grandrequest` (`ResignationRequestController.php:552-576`), which
     upserts into `ResignationAccept` (creates if no existing row for that `Resignation_pkey`, else updates),
     coercing the three checkboxes to `1` if present (`:555-566`).
3. **Employee acknowledges** (if applicable): `Empagreed()` (`:577-591`) — sets an `agree` flag on the resignation
   request (note: `:588` has `$arr_form_data['agree'] == 1;` — a comparison, not assignment; this line is a no-op
   bug, the agree flag is likely never actually set through this path).
4. **Employee can withdraw**: `withd()` (`:513-551`) — cancels resignation (`Resignation_status = 'Cancelled'`)
   and the linked `termination` row, only if both a resignation and a termination record exist.
5. **HR runs Full & Final Settlement**: `FullandFinalsettlement/index` (`:56-66`) lists active employees and
   resigned employees (`status=2`) side by side. Per resigned employee, HR:
   - Views/edits termination details (`details_res`, `:149-170` — reason, submitted/approved/last-working dates,
     remarks).
   - Views/retrieves allocated assets (`Assets($emp_fkey)`, `:172-184`, joins `allocate` + `asset_management`).
   - Reconciles attendance/working days (`workingattendnacedays()`, `:238-256` — computes present days via SQL
     against `attendance_register`/`payroll_master`, leave balance via a DB function
     `leave_balance_inthe_year_fn`, and week-off days via `weekoff_days_count_fn`).
   - Approves/rejects pending leave requests as part of settlement (`approve_selectd`/`reject_selected`,
     `:186-219`, `:258-290` — both call a stored procedure `leave_transaction_prc` per leave entry then bulk
     `updateAll` the leave status).
   - Adjusts leave encashment (`leaveadjustment()`, `:469-542` — computes a per-day salary rate from
     `emp_anual_ctc / 365 * leave_adjusted`, writes `emp_settle_slip` rows of type `ENCASHMENT`/`BALANCE`; has a
     tenant-specific branch for `DEMO`/`KWMT` company codes that also stores `working_days_settled`/`payroll_days`
     on the `termination` row, `:496-504`).
   - Approves final leave encashment (`approveencash()`, `:352-389` — computes encashable days via
     `leave_balance_inthe_year_fn`, capped at policy's `leave_encash_limit`, and invokes stored procedure
     `leave_encash_prc`).
   - Saves computed settlement totals back onto `termination` (`savedetails()`, `:221-236` — `working_days_settled`,
     `leave_balance`, `days_attendance`, `payroll_days`).
   - Generates the final settlement payslip: `get_complete($emp_pkey, $dayss, $leaves)` (`:292-349`) — calls stored
     procedure `final_settle_pay_prc` and assembles salary summary/without-component/employee-detail data for a
     printable settlement statement.

### Forms
- Resignation request: Reason (likely select), Reason_Desc (free text), Last_workingday (date) — required per
  `Saverequests` usage, though client-side validation not directly inspected for `index.ctp`'s submission form
  itself (only the approver's `addeditleave.ctp` was read in detail).
- Manager approval: required `comments_to_emp` textarea, required `last_allowed_date`, employee-search pickers
  for forward-to/handover-to.
- HR approval: three completion checkboxes + comment + `isApproved` select (required).
- No rating/competency matrix in this workflow — it's status/date/checkbox-driven.

### Server-side validation / redirects / flash messages
- Very little formal validation: most actions are bare `save()`/`updateAll()` calls without field checks; errors
  surface as JSON `{success: 0/1}` or bare boolean returns consumed by client JS, not CakePHP flash messages.
- `Saverequests` and `withd` both gate on **existence checks** (does an active resignation/termination already
  exist) rather than input validation — the core server-side rule enforced is "one active resignation per
  employee at a time."

### Notifications
- No `Email->send` calls found in either controller. INFERRED: no email is sent at resignation submission,
  approval, or F&F completion — all hand-offs are visible only via the respective index/grid screens
  (`ResignationRequest/listleaves`, `FullandFinalsettlement/index`).

### AJAX/JS endpoints
- `ResignationRequest/Request`, `ResignationRequest/index`, `ResignationRequest/withd`,
  `ResignationRequest/letteredit`, `ResignationRequest/getusers`, `ResignationRequest/listleaves`,
  `ResignationRequest/grandrequest`, `ResignationRequest/Empagreed`, `ResignationRequest/deleteresignation`,
  `ResignationRequest/Saverequests`
- `FullandFinalsettlement/leavebalance`, `FullandFinalsettlement/Terminate`, `FullandFinalsettlement/details_res`,
  `FullandFinalsettlement/Assets/{emp_fkey}`, `FullandFinalsettlement/approve_selectd`,
  `FullandFinalsettlement/reject_selected`, `FullandFinalsettlement/get_complete/{emp}/{days}/{leaves}`,
  `FullandFinalsettlement/approveencash`, `FullandFinalsettlement/getperiod/{emp_fkey}`,
  `FullandFinalsettlement/workingattendnacedays`, `FullandFinalsettlement/savedetails`,
  `FullandFinalsettlement/leaveadjustment`
- Cross-controller calls seen in `FullandFinalsettlement/index.ctp`: `LeaveEncashmentRequest/encashemp`,
  `LeaveEncashmentRequest/listallempsforencash` — the F&F screen also reaches into the separate
  LeaveEncashmentRequest module (out of this report's scope, noted for cross-reference).

---

## Cross-cutting observations for the Next.js migration

1. **No server-side role/ownership enforcement** beyond the blanket `user_group` session check was found in
   TeamReview, HierarchyReview, Promo, ResignationRequest, or FullandFinalsettlement — actions like `saveRequest`,
   `saveHierarchyReview`, `approvesave`, `grandrequest` trust the `pkey`/`emp_fkey` passed in the request body
   rather than re-verifying the session user is the authorized reporting/reviewing officer, approver, or record
   owner. This should be re-derived server-side (not just UI-hidden) in the rewrite.
2. **All list/status logic relies on ad-hoc string status labels** in SelfReview/TeamReview
   (`'Reporting Person submitted the Appraisal'`, etc.) vs. **numeric status codes** in HierarchyReview
   (1/2/3/5) for what is conceptually the same workflow — the Next.js data model should likely normalize these
   into one enum shared by both branches.
2b. Duplicate/backup files exist throughout (`*_bkup_*.ctp`, `*#backup_*`, `SurveyController_bkup_megha.php`) —
   none of these are live routes; only the canonical (non-suffixed) files reflect current behavior.
3. **No email/push notifications** were found triggered from any of the nine controllers in this scope — all
   hand-offs (self-review→officer review, resignation→approval, promotion→approval) are surfaced only via
   polling grid screens the next actor must know to check.
4. **Client-computed totals sent as trusted input**: TeamReview's total marks/grade are computed in JS and POSTed
   as hidden fields, then persisted as-is server-side (`TeamReviewController.php:700-704`) — recompute
   server-side in the rewrite to avoid tampering.
5. Company-code-specific branches exist in `PromoController::promotion()` (view variant selection, `:242-250`),
   `PromoController::promotion()` history filter (`:134-149`), and
   `FullandFinalsettlementController::leaveadjustment()` (`:496-504`) — multi-tenant customization logic baked
   into controller code rather than configuration; these need explicit product decisions before porting (keep
   as config flags vs. drop legacy tenant behavior).

---

## 12. Dashboards, Notifications & Mail

# Dashboards, Notifications, Mail & Misc Admin — UX Behavior Report

Legacy path root: `D:\Projects\RIZOMigration\legacy`

All auth gating in this cluster inherits `AppController::beforeFilter()` (`Controller/AppController.php:39-197`):
- `AppController.php:43-46`: if session `user_group` is not `1` or `2`, redirect to `Site/login`.
- `AppController.php:95-136`: on every page load, if `company_key` session is set, the controller connects to the tenant DB (`companydb`) and — **only for `user_group == 2`** — runs the notification queries described in the Notifications Inventory below (pending leave approvals to authorize/approve, upcoming birthday/joining events in next 7 days, team-leave-notification visibility flag).
- `AppController.php:140-149`: for specific `company_code` values (`vgfs`,`vsfs`,`gede`,`absg`,`demo`,`glet`), also runs a **site-transaction end-date warning** query (site contracts expiring within 31 days).
- `AppController.php:158-175`: for `company_code` in (`demo`,`glet`), also runs a **CTC increment reminder** query (employees whose `next_increment_date` falls in current/next month).
- All of the above are `$this->set()` into every view via the layout, i.e., they feed the notification bell in `View/Layouts/default.ctp:550-635`, not just Dashboard-cluster views.

---

## 1. Controller/DashboardController.php

**Purpose**: Legacy/default admin+employee dashboard (index route `/Dashboard/index` — also the controller CakePHP falls back to for `Dashboard` links throughout the old UI). A near-duplicate `Controller/DashboardController_bkup_jan.php` exists (per task note) — confirmed present as a stray backup file, not routed anywhere; safe to ignore for migration (INFERRED: no route/menu references `DashboardController_bkup_jan`).

**Who can access it**: Requires only a valid session (`user_group` 1 or 2) per `AppController.php:43-46`. No menu_id gate on `index()` itself, but sub-widgets branch on `user_group`/`company_code`/`user_access` (below).

**Step-by-step flow — `index()` (`DashboardController.php:12-257`)**:
1. `DashboardController.php:17-19`: if session `ds` (datasource) is null, redirect to `Site/login`.
2. `DashboardController.php:28-42`: **branch point** — if `user_group == 2` AND `company_code != 'ABSG'`:
   - `DashboardController.php:31-34`: queries `user_access` for `menu_id = '0'` (the "Hierarchy Dashboard" toggle) for the logged-in employee (`emp_fkey`).
   - `DashboardController.php:35-42`: if that access flag is `'Y'` → renders **Hierarchy Dashboard** (calls `hierarchydashboard()`, a manager/team-lead view). Otherwise → renders **Employee (self-service) Dashboard** (`empdashboard()`).
3. `DashboardController.php:43-153`: else (i.e., `user_group == 1`, or `user_group==2` with `company_code=='ABSG'`) → **Admin Dashboard** rendered inline in `index()`:
   - `DashboardController.php:48-51`: menu list — `getMenusForAbs()` special-cased for ABSG company, else `getMenus()`.
   - `DashboardController.php:58-82`: total active employee count (`emp_details.status=1`), branch-scoped for `user_group==2 && company_code in (GLET,ABSG)` via `get_branch_code_abs_fn()` stored function (`:71-78`).
   - `DashboardController.php:84-109`: "present today" count from `present_today` table.
   - `DashboardController.php:110-137`: "present today (all)" count from `present_today_all`, date-filtered.
   - `DashboardController.php:139-140`: pending leave request counts via `listemployeeleaverequestscounts(0)`.
   - `DashboardController.php:154-158`: subscription `plan` from `comp_contact_info`.
   - `DashboardController.php:160-229`: "who's not clocked in yet today" widget (`results1` count) — recursive hierarchy CTE-style union query walking `emp_proff.attr1` (manager chain) up to 6 levels, for GLET/ABSG scoped users.
   - `DashboardController.php:230-244`: employee display name (`emp_name`) for greeting; company announcements list for `company_code == 'GLET'` only (`announcements` table).
   - `DashboardController.php:247-253`: hardcoded Zoom onboarding webinar link + `getNextWednesday()` helper (`DashboardController.php:261-281`) computing the next Wednesday session date/time (Asia/Kolkata-agnostic, uses server "today").
4. View: `View/Dashboard/index.ctp` (many dated backups alongside it, e.g. `index.ctp#bkup_athira_13_08_2025`, `#bkup_bindu_09_12_2025` — evidence of frequent, uncoordinated hot-patching).

**`hierarchydashboard()` (`DashboardController.php:298-733`)** — manager/team-lead dashboard:
- Recomputes menus, then builds a **recursive downline** of subordinates via repeated `emp_proff.attr1` self-joins (up to 6 levels deep, `:330-377`) to scope all counts (headcount, present-today, who's-in) to the manager's org subtree.
- `DashboardController.php:395-400`: shows a "settings runner" completion count if a `genaral_setings` flag `when_itis='Employee_Login'` is active — likely a first-login setup wizard nudge.
- `DashboardController.php:486-489`: pending leave requests for the team + upcoming events (`getEvents()`, `:720-730`) — holidays, birthdays, joining anniversaries in the next 30 days scoped to the manager's holiday group / subordinate list.
- `DashboardController.php:508-535`: **today's birthdays and work-anniversaries among direct reports** (`arr_employees_pics`, `arr_employees_work`) — this is the data source for the "Send Wish" widget (see Notifications Inventory).
- Renders `View/Dashboard/hierarchydashboard.ctp` (`:614`). Numerous stale backups present (`#bkup`, `#bkup_ashin_06_07_2024`, `#bkup_megha_30_03_2022`, `_bkup-22`).

**`empdashboard()` (`DashboardController.php:785-`, self-service view)**:
- `DashboardController.php:800`: last punch direction via `last_punch_fn()` stored function.
- `DashboardController.php:807-816`: this employee's own joining date and birthday (for a "your day is coming up" style widget).
- `DashboardController.php:820-824`: year-to-date approved leave count.
- `DashboardController.php:827-838`: current-month working days / leaves-taken / present-days computed from `emp_detail_timeattandance` with half-day logic (`P/P`, partial `/P` or `P/` patterns).
- `DashboardController.php:850-854`: current-month miss-punch count.
- `DashboardController.php:857-862`: last device punch timestamp/direction.
- `DashboardController.php:866-872`: current-month absent-days (full + half-day weighted).
- `DashboardController.php:884-902`: last 7 months' salary bar-chart data (net/gross) from `payroll_master`, with a `showbarchart` flag if no data.
- Renders `View/Dashboard/empdashboard.ctp` (many stale duplicates: `empdashboard.ctp_bkup`, `_bkup_nimisha_25_04_19`, `_nimisha_bkup_17_04_19`, `_bkup.ctp`).

**Other notable actions in DashboardController** (identified via function-name grep, not all fully read given file size — `DashboardController.php` is ~5000 lines):
- `menuAudit($menu)` (`:283-296`): AJAX, logs a menu-click audit row to `ReportAudit` model, no permission check beyond session.
- `load_birthdays()` (`:617-718`): AJAX partial — birthdays/anniversaries in a rolling window (today, +2, +7, +30, +31 days) plus two data-quality reminder lists: employees missing shift/holiday/leave-policy/salary-structure config (`arr_reminders`), and employees with no CTC uploaded (`salary_missed`). Renders `View/Dashboard/load_birthdays.ctp`.
- `getEvents($emp_fkey)` (`:720-730`): shared helper — holidays/birthdays/joining-anniversaries in next 30 days.
- `empcalendar()` (line 732+, truncated in this read — calendar view/AJAX for employee's own calendar).
- `EmployeeEvent()` (`:2453-`): AJAX endpoint (`autoRender=FALSE`) that resolves whether the birthday/anniversary "wish" has already been sent for an employee — feeds the wish_modal flow (see Notifications Inventory item "Birthday/Work-Anniversary wish email").
- `convertimage()` (`:3300+`): target of the `wish_modal.ctp` AJAX form POST — merges a background template image with the employee's avatar and **sends the actual wish email** (two near-identical PHPMailer blocks around `:3403` for Birthday and `:3589-3605` for Work Anniversary — see Notifications Inventory).
- `sendFormEmail()` (`:4655-4731+`): AJAX endpoint for an "Upgrade Plan" contact-sales form — see Notifications Inventory.
- `getMenus()` / `getMenusForAbs()`: menu-tree builders, ABSG-company variant filters differently (not fully diffed here).
- `dashboard_old()` (`:4731+`): legacy alternate dashboard render, presumably dead code retained for rollback (INFERRED — not called from `index()`'s branch logic that was read).

**Forms**: `View/Dashboard/wish_modal.ctp` — a "Birthday / Anniversary Wishes" confirmation modal with a required `remarks` textarea (pattern `^\d+(st|nd|rd|th)$`, i.e. expects an ordinal like "5th"), POSTs via AJAX to `Dashboard/convertimage` (`wish_modal.ctp:1,47`).

**AJAX/JS endpoints hit from these views** (from `wish_modal.ctp` and inferred sibling views):
- `Dashboard/convertimage` (POST, `wish_modal.ctp:47`) — send birthday/anniversary wish.
- `Dashboard/load_birthdays` (GET, reload after wish sent, `wish_modal.ctp:62`).
- `Dashboard/menuAudit/{menu}` — menu click audit (called from layout/menu JS, INFERRED from `menuAudit()` signature).
- `Dashboard/EmployeeEvent` — resolves wish-sent state (INFERRED caller: birthday widget JS).

---

## 2. Controller/DashboardNewController.php

**Purpose**: The **newer/current dashboard**, explicitly routed at `Config/routes.php:33`: `Router::connect('/Analytics', array('controller' => 'DashboardNew', 'action' => 'index'));`. This is the "Analytics" landing page and is under very active development — the `index()` action is ~1000 lines with dozens of `//edited by <name> on <date>` markers spanning Mar–Dec 2025, indicating this is the primary dashboard actively being iterated on (vs. the legacy `DashboardController` which looks frozen/backup-heavy by comparison).

**Who can access it**: Same session gate as above. Additionally implements a **scope filter** (`getScopeFilter()`, `DashboardNewController.php:16-50`): for `user_group==2` (non-admin) and not `company_code=='LNTT'` and session `scope != 'admin'`, restricts all dashboard counts to the logged-in employee's direct subordinates only (`emp_proff.attr1 = emp_fkey`, active status) — a simplified, single-level version of the old recursive hierarchy walk (comment at `:29` says "Simplified by Antigravity on 28-04-2026: Only direct subordinates ... and active status" — i.e. multi-level hierarchy traversal was intentionally reduced to direct reports only, a behavior change worth flagging for migration parity discussions).

**Step-by-step flow — `index()` (`DashboardNewController.php:52-1048`)**:
1. `:62-64`: redirect to login if no `ds` session.
2. `:68`: compute scope filter (direct-reports SQL `IN (...)` clause).
3. `:70-72`: subscription `plan`.
4. `:76-119`: **active employee count** and **absent count** (scoped).
5. `:123-136`: branch — `user_group==2 && company_code != 'DEMO'` → checks `user_access menu_id='0'` same as legacy controller → `hierarchydashboard()` or `empdashboard()` (both re-implemented in this controller, largely mirroring the legacy versions with the same recursive-hierarchy union queries at `:1096+`).
6. `:137-260`: **Admin dashboard** branch — menu list, HRM sub-menu list for ABS-family companies (`getHrmMenusForAbs()`), employee counts (HO vs branch-scoped), present-today counts, pending leave counts.
7. `:266-522`: a large **"gawtham" edit block** (per comments) adding a whole secondary KPI/analytics layer regardless of admin/employee branch outcome — this runs unconditionally after the branch logic:
   - `:276-300`: 6-month salary trend (`emp_salary_slip`, head_operator=ADDITION) → Morris.js line-chart data.
   - `:303-327`: employees missing bank account number.
   - `:312-322`: employees below ESI wage threshold (₹21,001/mo) with no ESI number.
   - `:330-342`: present-today count, active employee count (duplicated computation).
   - `:345-355`: absent-this-month count (unused — commented out at `:361`).
   - `:364-373`: employees with blank qualification records.
   - `:376-382`: employees with **no** qualification record at all.
   - `:385-404`: employees with **no nominee** on file (`emp_family`, `is_nominee='Y'` check).
   - `:415-446`: dynamic full/half-day leave count for a "21st-to-20th" payroll cycle window.
   - `:449-458`: late-comers in the last 8 days (`emp_detail_timeattandance`, `present='A/P'`).
   - `:460-466`: employees on notice period (`termination` table, active).
   - `:468-475`: retired employees count.
   - `:864-878`: age-group distribution (donut chart: Under 20 / 20-30 / 30-40 / 40-50 / 50-60 / 60+) with a hardcoded color map (`:881-889`).
   - `:914-930`: headcount-by-department bar chart.
   - `:973-994`: another salary trend chart (line, Morris.js) — appears **duplicated** with the one at `:276-300` (same query shape, different variable names `chartData` vs `salarychartData`) — likely leftover from copy-paste edits, worth flagging for cleanup during migration.
   - `:1007-1044`: hardcoded absent-count query with **literal dates `2022-12-01`/`2022-12-31`** (`:1011`) and hardcoded `yearmonth = '2024-05-01'` (`:1035`) — these are stale/dead test queries left in production code; the resulting `absent_count`/`leave_count` values are meaningless in 2026 (INFERRED bug — flag for migration: do not port this logic as-is).
8. `:567-861`: **pending-approvals KPI row** — pending leave approvals (cycle-aware date window via `att_start_end_fn()` stored function, `:772-802`), pending promotions (`promotions.approved_status='N'`), pending expense claims (`emp_expense.expense_status='Applied'`), attendance regularization requests pending this month, attendance-register verification pending this month.
9. `:570-730`: **upcoming events widget** (birthdays + work anniversaries in the next month, scoped to direct reports if applicable) with a `wished` flag computed per event via `Wish` model lookup (`:719-728`) — same "has this wish been sent" pattern as the legacy dashboard but reworked as a single UNION query with computed years-of-service description text.

**Forms**: None beyond the shared `wish_modal.ctp` (reused from `View/Dashboard/`, confirmed present in `View/DashboardNew/wish_modal.ctp` as well — a separate copy).

**Notifications**: See centralized Notifications Inventory (section 15) — this controller feeds the same wish-email flow, plus is the source of the KPI counts a real notification/alert system would likely be built from (pending approvals, missing-data flags), though currently these are dashboard tiles, not push notifications.

**AJAX/JS endpoints** (`View/DashboardNew/*.ctp` filenames strongly suggest these are drill-down partials loaded via AJAX from dashboard tiles — not individually read line-by-line, but enumerated from `View/DashboardNew/`):
- `DashboardNew/absenttoday.ctp`, `activetoday.ctp`, `latecomers.ctp`, `misspunchnew.ctp`, `noesinumber.ctp`, `nonominee.ctp`, `noqualification.ctp`, `noticeperiod.ctp`, `pendingleaves.ctp`, `pfnotcovered.ctp`, `presenttoday.ctp`, `retiredemployees.ctp`, `attendance_regularisation.ctp`, `attendanceverification.ctp`, `expense.ctp`, `promotion.ctp`, `leavethismonth.ctp`, `lastmonthattendancenew.ctp`, `thismonthattendancenew.ctp`, `todayattendancenew.ctp`, `LocationUpdates.ctp` — each is a drill-down list matching one of the KPI tiles computed in `index()` (INFERRED mapping by name).
- `Dashboard/convertimage`, `Dashboard/load_birthdays` — same wish-send flow.

---

## 3. Controller/BusinessDashboardController.php

**Purpose**: A third, separate **executive/BI-style dashboard** (`View/BusinessDashboard/index.ctp`) focused on charts rather than KPI tiles — company profile header, workforce composition, and payroll cost breakdowns. Not linked from `Config/routes.php` (no custom route found — reached via standard `/BusinessDashboard/index` URL and presumably a menu item, INFERRED).

**Who can access it**: Same session gate; `user_group==2 && company_code != 'DEMO'` branches to `hierarchydashboard()`/`empdashboard()` exactly as in the other two dashboard controllers (`BusinessDashboardController.php:55-67`), otherwise the large admin BI branch (`:68-529`) runs.

**Step-by-step flow — `index()` (`BusinessDashboardController.php:8-563`)**:
1. `:12-18`: loads `CentralControl` model (control DB) to fetch the tenant's `company_name`.
2. `:19-25`: active employee count.
3. `:26-48`: company profile block — business name, logo, address, city, pincode, state from `comp_contact_info`.
4. `:68-99`: employee count, HO-vs-branch scoped for `user_group==2`.
5. `:101-144`: age-group donut chart (same logic as DashboardNew).
6. `:147-172`: headcount-by-department bar chart (same as DashboardNew).
7. `:184-209`: **gender/classification** donut chart (`emp_details.classification` field, "Other" normalized to "Others").
8. `:211-235`: 6-month salary trend line chart.
9. `:237-261`: headcount-by-designation chart.
10. `:264-304`: same stale hardcoded-date absent-count bug as DashboardNew (`2022-12-01`..`2022-12-31`, `yearmonth='2024-05-01'`) — confirms this is copy-pasted dead code across at least 2 controllers.
11. `:307-337`: headcount-by-branch chart.
12. `:351-365`: last-12-months month picker options for the CTC drill-downs.
13. `:370-528`: **CTC/payroll breakdown suite**: monthly CTC trend (`:370-393`), CTC breakup (fixed/variable/employer-contribution, `:396-412`), salary-by-department/designation/branch for the prior month (`:414-525`).
14. `:530-561`: compliance-gap tile — count of employees missing PF (UAN), ESI, or PAN, plus total pending leaves (`leaveentries.LEAVESTATUS='Applied'`).

**AJAX endpoints (drill-down data, JSON)** — this controller is unusually AJAX-heavy, essentially a chart-data API:
- `getCTCBreakup` (`:565-601`) — `?month_year=YYYY-MM` query param, JSON.
- `getCTCBreakupByDept` (`:603-650`), `getCTCBreakupByDesignation` (`:652-701`), `getCTCBreakupByBranch` (`:702-751`) — same pattern, JSON.
- `getEmployeeCount` (`:752-789`) — 6-month rolling headcount trend, JSON.
- `getPresentCount` (`:790-818`) — 6-month present-days trend from `attendance_register`, JSON.
- `getVariableAddition` / `getVariableDeduction` (`:819-899`) — 6-month variable-pay upload trend, JSON.
- `getAbsenceData` (`:901-942`) — 6-month leave/LOP trend, JSON.
- `highestSalaries` / `lowestSalaries` (`:943-1034`) — top/bottom 5 earners for a given month, returns raw HTML `<tr>` fragments (not JSON) for direct table injection — note: employee first name only, no last name, and amounts are NOT masked/redacted for viewers (potential PII exposure concern to flag for migration — any `user_group==2` admin viewer with dashboard access sees named top/bottom earners).
- `monthlyCTCChartData` (`:1036-1092`) — 6-month CTC trend, JSON.

**Notifications**: None beyond the same pending-leaves compliance tile.

---

## 4. Controller/MailBoxController.php

**Purpose**: Stub controller — `index()` (`MailBoxController.php:54-58`) and `Template()` (`:59-61`) are both **empty function bodies**. `$uses = array('UserCredentials','CompanyContactInfo','Banks')` (`:48`) declared but unused in the two empty actions. This is dead/placeholder code (INFERRED — no logic to migrate).

**Who can access it**: Same session gate only; no menu_id-specific check found (none needed — nothing happens).

**Views**: `View/MailBox/index.ctp`, `View/MailBox/template.ctp` exist — not read in depth since the controller performs no data prep for them (INFERRED these are also placeholder/legacy markup, possibly leftover from an abandoned in-app mailbox feature). This is **not** related to the actual outbound-email system (see EmailComponent below) — "MailBox" here appears to be an unfinished internal-messaging feature that was never completed.

**Forms/Notifications/AJAX**: None found.

---

## 5. Controller/EventHandlerController.php

**Purpose**: Another near-stub — `index()` (`EventHandlerController.php:58-60`) is empty. Declares a large model list (`CentralControl, Family, passport, qualifcations, history, EmployeeTaxTransactions, EmpTaxSalTrans, FinancialYear, UserCredentials, EmployeeDetails, Designation, EmployeeProfessionalDetails, Departments, Grades, Verticals, Units, TaxHead, EmployeeCTC`, `:51`) and `MasterdataManagement` component (`:52`), suggesting it was scaffolded for an "Employees landing view" (per the code comment at `:54-56`) but never implemented. View: `View/EventHandler/index.ctp` exists.

**Who can access it**: Session gate only.

**Notifications/AJAX**: None (empty action).

---

## 6. Controller/InfoController.php

**Purpose**: A **debug/diagnostic endpoint that dumps `phpinfo()`** to the browser (`InfoController.php:59-67`). `index()` disables the layout/render and calls `echo phpinfo();` directly (`:61-66`).

**Who can access it**: Critically, `InfoController` extends the **bare `Controller`** class (`InfoController.php:36`), not `AppController` — meaning it **does NOT inherit `AppController::beforeFilter()`'s session/auth gate**. It also `App::uses('AccessController', 'Controller')` (`:23`) but doesn't extend it, and sets `ini_set("display_errors", 1)` at file scope (`:24`), globally enabling PHP error display for the whole request. **This route is unauthenticated and exposes full PHP configuration (paths, loaded extensions, potentially env vars) to anyone who requests `/info`** — a significant security finding to carry into the migration as "must not port this endpoint," or if a health-check equivalent is needed, it must be behind auth and should not leak `phpinfo()`.

**Notifications/AJAX**: None.

---

## 7. Controller/PagesController.php

**Purpose**: Stock CakePHP static-pages controller, overridden to double as the **login-page shell and "remember me" auto-login handler**. `Config/routes.php:27` routes `/` → `pages/display/home`, which renders `View/Pages/home.ctp`.

**Who can access it**: `display()` (`PagesController.php:48-141`) runs **before** any session check (this IS the entry point for unauthenticated users) — extends `LoginAppController` (`:31`), not `AppController`.

**Step-by-step flow — `display()`**:
1. `:50-97`: if `$_COOKIE['user_id']` and `$_COOKIE['password']` are set (a plaintext-credential "remember me" cookie pair — flag as a security concern: credentials appear to be stored in cookies, not a token) and `$_COOKIE['userGroup']`:
   - `userg=='1'` (`:52-57`): calls `LoginManagement->verifyAdminLogin()` (`Component/LoginManagementComponent.php:27-59`) — re-validates username/password against `CentralUserCredentials`/`CentralControl`/tenant `UserCredentials`, and on success writes `user_group=1` to session and redirects to `Dashboard/index` (`:55`).
   - `userg=='2'` (`:58-91`): calls `verifyEmployeeLogin()` (`LoginManagementComponent.php:60-90`) — same pattern for `user_group=2`. Handles three sub-cases: account locked (`:62-64`, message "Account locked for security reasons. Please contact Administrator."), forced password reset (`:65-67`, redirects to a reset URL), or success (`:68-86`) — re-checks the `user_access menu_id='0'` hierarchy flag and redirects to `Dashboard/hierarchydashboard` or `Dashboard/empdashboard` accordingly (mirrors the dashboard-selection logic duplicated in all 3 dashboard controllers — 4th copy of this exact branch).
   - Invalid credentials (`:87-90`): sets `$messages = "Invalid Username or Password"` (rendered in `home.ctp:38-40` as a Bootstrap alert).
   - Unknown `userGroup` cookie value (`:92-95`): clears both cookies.
2. `:100-113`: derives a `$company` slug from the subdomain (`$_SERVER["SERVER_NAME"]`) and switches the layout: `localhost`/`giridhar` → `giridhar` layout, everything else → `login` layout — evidence of a legacy white-label/multi-brand login page mechanism (INFERRED: likely dead for the current single-brand "MyPayrollMaster" product, but worth confirming before dropping in migration).
3. `:114-140`: standard CakePHP Pages passthrough — renders `View/Pages/{page}[/{subpage}]`.

**Forms**: `View/Pages/home.ctp` — the login form (`:41-58`): `user_id`, `password`, `rememberme` checkbox, submits via JS `submitForm()` to `Site/login` (not to PagesController itself — actual credential POST handling lives in `SiteController`, outside this scope). "Forget password?" link to `Site/passwordreset`.

**Notifications**: `home.ctp:38-40` — inline error alert `<div class="alert alert-danger">` showing `$messages` (e.g., "Invalid Username or Password", "Account locked for security reasons. Please contact Administrator.").

---

## 8. Controller/ActivityController.php

**Purpose**: An internal **task/activity tracker** (kanban-style: To Do / In Progress / Under Review / Completed statuses, `ActivityController.php:786-791`), scoped by project (`activity_projects`) and assignee — appears to be an internal team productivity tool bundled into the HR app, not employee-facing HR functionality per se.

**Who can access it**: Session gate only; results are implicitly scoped to `emp_fkey = session.emp_fkey OR created_by = session.emp_fkey` for the logged-in user in `index()`/`report()`/`listData()` (`:64-66`, `:734-736`) — i.e., every logged-in user (admin or employee) sees only tasks assigned to or created by them, with no explicit menu_id/user_group branching found.

**Step-by-step flow**:
- `index()` (`:59-82`) / `report()` (`:84-107`): load status list, project list, employee list, and root-level activity IDs for the create/filter dropdowns.
- `add()` (`:170-202`): edit-mode loader — if `id` param given, loads the activity + its subtasks + resolves "created by" display name.
- `save()` (`:217-348`): the core save action — handles task transfer (reassignment, `:230-246`), sends **email notifications** on task creation/modification (`:248-301` — see Notifications Inventory), and creates subtask rows when `start_time` is present (time-logging).
- `listData()` (`:685-801`): server-side-paginated datatable feed (JSON) with filters by status/project/employee/date/search text.
- `deleteRow()` / `deleteProject()` / `deleteLogTime()` (`:846-889`): soft-delete (status=0) endpoints.

**Forms**: `View/Activity/add.ctp` (task create/edit), `View/Activity/addproject.ctp` (project create/edit), `View/Activity/report.ctp` / `index.ctp` (list/filter shells).

**Notifications**: See Notifications Inventory — `ActivityController::sendMail()` (`:350-390`) is a **locally-defined, controller-specific mailer** using PHPMailer directly against **hardcoded SMTP credentials** (`smtp.zoho.in`, `noreply@mypayrollmaster.online` / password in source at `:369-370` — hardcoded credential in source control, flag for migration secrets handling). It builds a branded HTML wrapper (`getTemplate()`, `:392-683`, "MyPayrollMaster" header) around the caller-supplied body. **Send is currently disabled**: `:382-389` shows `$mail->Send()` is commented out — activity notification emails **do not actually get sent** in the current codebase, despite the surrounding logic (subject lines, recipient resolution) being fully built out. This is an important finding: any migration should confirm whether this dead code should be revived or intentionally left inert.

**AJAX/JS endpoints**: `Activity/listData` (POST, datatable), `Activity/listProjectsData` (POST, datatable), `Activity/save`, `Activity/save2`, `Activity/deleteRow`, `Activity/deleteProject`, `Activity/deleteLogTime` — all `autoRender=false` JSON-returning endpoints called from list/grid JS (INFERRED callers not individually traced).

---

## 9. Controller/AnalysisController.php

**Purpose**: Data-integrity checker for payroll masters — surfaces duplicate department/designation/branch codes and salary-structure allocation issues as alert banners, likely embedded as a widget/tab rather than a full page.

**Who can access it**: Session gate only.

**Step-by-step flow**:
- `index()` (`:53-58`): loads active employee list for a dropdown.
- `duplication_founds()` (`:60-101`): three `GROUP BY ... HAVING COUNT(...) > 1` queries against `department`, `designation`, `branches`; echoes raw HTML `<div class="alert alert-danger">Issue Found! Duplicate {Departments|Designation|Branches} Found</div>` + a table listing the offending codes, directly as the AJAX response body (not JSON).
- `salary_structure_allocate_issues()` (`:103-173`): checks for missing/duplicate financial-year config, missing gross salary, multiple salary heads, duplicate branches for a placeholder employee key (`Pemp_fkey` — this looks like a template/unbound parameter left in the SQL literal, e.g. `:107` `emp_Pkey=Pemp_fkey` — **this query is almost certainly broken/non-functional as written**, since `Pemp_fkey` is never interpolated; flag as dead/buggy code, INFERRED bug).
- `payroll($emp_pkey)` (`:175-207`): per-employee salary-structure-assignment check, alerts if no structure assigned.

**Forms**: None (read-only diagnostic alerts).

**Notifications**: In-page alert banners only (`alert alert-danger`), not the layout notification bell. No emails.

---

## 10. Controller/DataUploaderController.php

**Purpose**: The **employee master-data setup/edit form controller** — despite the name suggesting bulk upload, this is primarily the single-employee "Setup"/"Profile" form (personal + professional + tax + qualifications + promotions), reached as both the admin "add/edit employee" screen and the employee's "My Profile" self-service screen. Very large controller (~3000 lines read partially).

**Who can access it — admin vs employee difference (core of this task's "who sees what")**:
- `index()` (`DataUploaderController.php:58-86`):
  - `user_group=='1'` (`:61-79`): **Admin view** — active employee count, list of employees missing professional-details rows (`checkProff()`, `:129-134`), branch/designation combo lists; renders `View/DataUploader/index.ctp` — this is the admin's employee-directory/add-new-employee landing page.
  - `user_group=='2'` (`:80-85`): **Employee (self-service) view** — calls `setup($emp_fkey)` with the logged-in employee's own key, renders `View/DataUploader/setup.ctp` — i.e., employees land directly on their **own** profile-edit form ("My Profile", confirmed by the head-label logic below), with no directory/list access.
- `setup($emp_pkey)` (`:495-612`): the shared form-data loader for both add and edit modes:
  - `:508-517`: page heading differs by role — `user_group==2` → "My Profile" (`:513`); admin editing someone else → "`{first} {middle} {last}`'s Profile" (`:516`).
  - `:522-611`: loads gross CTC, professional details, tax-head details (via `requestAction` to `/Taxation/loadEmpTaxationDetails/$emp_pkey`), and every reference combo list needed by the form: departments, designations, grades, verticals, branches, holiday groups, shift/day-time procedures, leave-policy groups, salary structures, financial years, qualifications, notice-period options.
- `promotion($emp_fkey)` / `approvepromotion($emp_fkey)` (`:696-954`): promotion request/approval form data loaders — same combo-list pattern, plus `employee_structure_vview` current structure and pending-promotion lookups. `approvepromotion` has a bug: its "pending promotions" query filters `emp_fkey = ''` (`:879`, empty-string literal) rather than the passed `$emp_fkey` parameter — **likely always returns zero rows**, flag as dead/buggy code (INFERRED bug from reading the literal SQL).
- `saveemployeesetupnew()` (`:956-1062+`, truncated by file size): the actual save handler — creates `EmployeeDetails` + `EmployeeProfessionalDetails` + `UserCredentials` rows on first save; auto-generates employee ID by concatenating company code with the next sequence number if a device `punch_type` is configured (`:998-1046`); when the creator is `user_group==2` (a manager creating a subordinate), auto-inserts an `emp_config` HIERARCHY row linking the new employee to the creator (`:1008-1011`, `:1055-1058`) — this is the mechanism by which the manager/hierarchy dashboards' "downline" queries get populated.

**Forms**: The employee setup/profile form (`View/DataUploader/setup.ctp` — not present in the `View/DataUploader/` directory listing captured, only `ctcupload.ctp` was found there; the setup/index views likely live under a differently-cased or shared view path — INFERRED discrepancy worth a follow-up grep during implementation, not resolved in this pass) — a large multi-tab form (personal info, professional info, tax heads, qualifications, notice period).

**AJAX/JS endpoints**: `DataUploader/getstages`, `updateesi`, `getautohierarchycompletions`, `getautocompletions`, `getautocompletions_superior` (typeahead/autocomplete endpoints for employee pickers, branch-scoped and hierarchy-scoped variants), `DataUploader/listemployees` (datatable), `DataUploader/jsons`/`jsonss` (select2-style branch/employee pickers), `DataUploader/employeesunder` (subordinate-scoped employee list), `DataUploader/savepromotions`, `DataUploader/saveemployeesetupnew`.

**Notifications**: `saveemployeesetupnew()` triggers a **"New Employee Added" email** on the shared `sendMail()`-style PHPMailer pattern found at `DataUploaderController.php:1742` and `:2779` (subjects `"{database} Added a new Employee"` and `"New Employee Added To Payroll"`) — see Notifications Inventory.

---

## 11. Controller/ApiRequestController.php

**Purpose**: A small JSON API surface for **menu-tree rendering** and **employee autocomplete/lookup**, consumed by other views' JS (e.g., sidebar menu, hierarchy pickers) rather than being a page itself.

**Who can access it**: Session gate only. `getMenus()` (`ApiRequestController.php:51-150`) explicitly branches the menu SOURCE table by role:
- `sessionObj['user_group'] == 2` (`:64-98`): builds the tree from `EmployeeMenu` model (the employee-restricted menu set) plus a synthetic "Dashboard" root node (`:74-82`).
- else (`:99-126`): builds the tree from the full `Menu` model (admin menu set), with Font Awesome icon classes attached (`:112`).
- Both branches respect a `context` query param (`main` vs `sub`) and `root` param for lazy-loading submenu branches (`:57-63`) — this is the sidebar's AJAX lazy-load mechanism.

**Notable**: `getMenus()` here is a **different, JSON-API implementation** of essentially the same menu-tree concept that `DashboardController::getMenus()`/`getMenusForAbs()` implement server-side for view-rendering — i.e., there are at least two parallel menu-tree-building code paths in the app (one for AJAX/ExtJS-style tree widgets via this controller, one for server-rendered sidebar via the Dashboard controllers). Migration should reconcile these into a single menu API.

**Other actions**: `listemployeesforhierarchy()` (`:151-187`), `listemployees()` (`:188-241`, has a `VGFS`/`VSFS` company branch-code scoping special-case at `:203-208`), `listdepartmentsforcombo()`/`listbranchesforcombo()`/`listdepartments1forcombo()`/`listdepartments1sforcombo()` (`:242-306` — note `listdepartments1forcombo` and `listdepartments1sforcombo` are byte-for-byte identical duplicate functions, dead duplication to clean up in migration).

**AJAX/JS endpoints**: `ApiRequest/getMenus?context=main|sub&root={id}`, `ApiRequest/listemployeesforhierarchy`, `ApiRequest/listemployees`, `ApiRequest/listdepartmentsforcombo`, `ApiRequest/listbranchesforcombo`.

**Notifications**: None.

---

## 12. Controller/Component/EmailComponent.php

**Purpose**: A CakePHP `Component` wrapper around PHPMailer, registered app-wide via `AppController.php:38` (`$components = array('Email', 'DataTable', 'Session', 'RequestHandler')`), i.e. `$this->Email` is available in every controller. **However, in practice almost nothing in the codebase actually uses this shared component** — the grep for `Email->` usage found it referenced meaningfully only in `SiteController.php:1378-1396` (a hardcoded test-email action) and `EmployeeController.php` (not deeply inspected). The overwhelming majority of email-sending code across the app (28 controller files matched `PHPMailer`) **bypasses this component entirely** and instantiates `PHPMailer` directly inline, duplicating SMTP config each time — see Notifications Inventory. This is a major migration-relevant finding: there is no single email-sending abstraction to port; each feature has its own copy-pasted PHPMailer block, often with **different hardcoded credentials**.

**Configuration** (`EmailComponent.php:37-50`): hardcoded defaults — `from = info@forsight.com`, `fromName = "Forsight"` (a different/older product brand name than "MyPayrollMaster" seen elsewhere — evidence of rebranding without full cleanup), `smtpUserName = developer.binesh@gmail.com`, `smtpPassword` **in plaintext in source** (`:40`), `smtpHostNames = smtp.gmail.com`, hardcoded test recipient `bineshbabu.t@gmail.com` (`:44-45`) and hardcoded `subject = "Test Mail From Forsight"` (`:46`).

**`send()` method (`:90-138`)**: builds a PHPMailer instance with TLS/587/Gmail, but the actual `Body`/`AltBody` are **hardcoded placeholder text** ("Hi How are you" / "Alt Text", `:128-129`) rather than using `$this->text_body`/`$this->html_body` — i.e., even where this shared component IS wired up, its `send()` implementation looks incomplete/test-only, reinforcing that it's not the real production email path.

**Config/email.php**: Standard CakePHP `EmailConfig` class with 4 named configs: `default` (PHP `mail()`, generic `you@localhost`), `smtp` (generic placeholder `localhost:25`), `gmail` (`Config/email.php:62-71` — real-looking config: `smtp.gmail.com:465` SSL, username `developer.binesh@gmail.com`, from `info@mypayrollmaster.com`, **password in plaintext** `:68`), `fast` (another generic SMTP placeholder template). Only the `gmail` config looks like it was ever actually used in production (referenced from `SiteController.php` via `$Email->config('gmail')` in several places, e.g. `:673-681`, `:1777-1781`, mostly commented out).

---

## 13. Controller/Component/DashboardManagementComponent.php

Note: this file's actual contents, when read, are the **`LoginManagementComponent` class** (`verifyAdminLogin()`/`verifyEmployeeLogin()`, matching `Controller/Component/LoginManagementComponent.php` byte-for-byte). This is either a stray duplicate/misnamed file in the repo, or a filesystem/tooling artifact during this research pass — **flag for verification**: `Controller/Component/DashboardManagementComponent.php` did not contain any dashboard-specific logic when opened; it contained the login-verification component instead. No `DashboardManagementComponent` class body was found. Recommend the next engineer re-check this file directly (`D:\Projects\RIZOMigration\legacy\Controller\Component\DashboardManagementComponent.php`) before relying on this report for that component specifically.

For completeness, the **actual** `LoginManagementComponent.php` (referenced heavily by `PagesController`/`SiteController`) provides:
- `verifyAdminLogin($username, $password)` (`LoginManagementComponent.php:27-59`): validates against `CentralUserCredentials` (control DB) + tenant `CentralControl` (checks `end_date_effective >= today`, i.e. subscription not expired) + tenant `UserCredentials`; on success writes `login_user_id`, `user_group=1`, `user_name`, `company_key` to session.
- `verifyEmployeeLogin($username, $password)` (`:60-90`): same pattern but derives `company_code` from the **first 4 characters of the username** (`:62`) and writes `user_group=2`.

---

## 14. Config/routes.php

- `:27`: `/` → `Pages/display/home` (login page, see PagesController above).
- `:31`: `/pages/*` → `Pages/display` (generic static pages passthrough).
- `:33`: **`/Analytics` → `DashboardNew/index`** — confirmed the "Analytics" nav item/bookmark points at the newer dashboard controller, not the legacy one, matching the observation that `DashboardNewController` is the actively-maintained one.
- All other controller access in this cluster (`Dashboard`, `BusinessDashboard`, `MailBox`, `EventHandler`, `info`, `Activity`, `Analysis`, `DataUploader`, `ApiRequest`) relies on CakePHP's default `/{Controller}/{action}/{param}` convention routing (no explicit custom routes found for them).

---

## 15. Notifications Inventory (primary deliverable)

### A. In-app "bell" notifications (session-driven, computed on every page load)
All sourced from `AppController::beforeFilter()` and rendered in `View/Layouts/default.ctp`, visible only to `user_group == 2` (employee/manager) sessions:

| # | Trigger / Source | Who sees it | Message / UI | Code |
|---|---|---|---|---|
| 1 | Leave requests pending **this user's** authorization or approval (`leaveentries` joined to `emp_details`, `ISAutherizedby`/`APPROVEDBY` = logged-in emp, status Applied/Authorized) | `user_group==2` (managers/authorizers) | Bell badge count; dropdown list "You have a leave request from {first_name}", clickable to `LeaveRequest/employeeleaves` | `AppController.php:105`, rendered `View/Layouts/default.ctp:550-572` |
| 2 | "Team Leave Requests" menu visibility flag (`user_access` for a specific `menu_id` looked up by name) | `user_group==2` | Controls whether notification #1's bell is shown at all (`showteamleavenoti`) | `AppController.php:119-123`, `default.ctp:551` |
| 3 | Upcoming birthdays / joining anniversaries in next 7 days (`empevents`) | `user_group==2` | Computed but **the rendering block is commented out** in the layout (`default.ctp:652-670`) — i.e. this notification is currently invisible to users despite being computed on every request (wasted query / dead UI) | `AppController.php:112-116`; commented markup `default.ctp:652-670` |
| 4 | Site-transaction end-date warning (contracts expiring within 31 days) — only for `company_code` in `vgfs/vsfs/gede/absg/demo/glet` | `user_group==2`, and only rendered when `company_code == 'DEMO'` (`default.ctp:589`) — i.e. computed for 6 companies but only ever displayed for the DEMO tenant, another apparent gap/bug | Bell badge combining with #5; list item "Site Notification" linking to `SiteAttendance/` (admin only, `user_group==1` check inside the loop at `default.ctp:629`, which is contradictory since this whole block is inside the `user_group==2`-only notification section — worth flagging as a logic inconsistency) | `AppController.php:140-149`; `default.ctp:587-635` |
| 5 | CTC increment reminders — employees whose `next_increment_date` falls between 1st of this month and last day of next month — only for `company_code` in `demo/glet` | `user_group==2`, only rendered for `company_code=='DEMO'` | Same combined bell as #4; list shows employee name/ID and formatted next-increment date, plus a PDF export link (`Employee/incrimentdatapdf`) | `AppController.php:158-175`; `default.ctp:590-624` |

**Net finding**: the notification bell is effectively **broken for most tenants** — of the 5 computed notification types, only #1 (leave approvals) reliably renders for all `user_group==2` users; #4/#5 only render for the literal `DEMO` company code (likely a demo/testing artifact never generalized), and #3 (birthdays) is fully commented out. Migration should treat this as "intended notification center design, currently ~20% functional in production" rather than copying the conditional logic verbatim.

### B. Transactional / outbound emails (grep across entire `Controller/` directory)

All of these bypass `EmailComponent` and instantiate `PHPMailer` inline (pattern: `App::import('Vendor','PHPMailer',...); $mail = new PHPMailer; ...->isSMTP(); ...->Send()`), each with its own SMTP config block (mostly Zoho `smtp.zoho.in` or Oracle Cloud `smtp.email.ap-hyderabad-1.oci.oraclecloud.com`, some hardcoded credentials in source):

| Feature | Trigger | Recipient | Subject | Source |
|---|---|---|---|---|
| Activity/task notify | Task created or modified with a "notified_to" employee selected | The notified employee + the assigned employee (both, `AddAddress` twice) | "MyPayrollMaster - Activity #{id} - {summary} has been created" / "...Has bee modified [sic]" | `ActivityController.php:248-301` (send call at `:258`,`:274`) — **currently disabled**, `Send()` call commented out at `ActivityController.php:383-389` |
| New employee added | `saveemployeesetupnew()` on first save | (recipient not fully traced in this pass) | "{database} Added a new Employee" / "New Employee Added To Payroll" | `DataUploaderController.php:1742`, `:2779`; duplicated near-identically in `EmployeeController.php:4352`,`:6963`, `EmployeeJoinController.php:3864`,`:6358`, `SiteController.php:96` |
| Onboarding completed | Employee onboarding workflow finishes | (INFERRED: new employee or HR) | "Onboarding Completed {empName}" | `EmployeeJoinController.php:9965` |
| Full & Final settlement slip | F&F processing for a resigned employee | (INFERRED: the resigned employee) | "Full And Final Slip" | `EmployeeResignationController.php:2634` |
| Salary slip | Payslip generation/send action | Employee | "Salary_Slip of {name}" | `SalaryReportsController.php:2621`,`:2885`,`:38738`; `SynthiteSalaryReportsController.php:3743` |
| Leave request submitted → needs authorization | Employee applies for leave | Authorizer | "Action Needed : MPM : Leave Request Details" | `LeaveRequestController.php:3402`,`:3689`; `LeaveapiController.php:467`; `SiteController.php:1520` |
| Leave authorization request | Employee's leave moves to "Authorized" stage, needs approver | Approver | "MyPayrollMaster - Leave Authorization Request" | `LeaveRequestControllerBkups.php:1461` (and Nimisha-edited variant `LeaveRequestControllerNimisha_edited.php:1422`) |
| Leave approved/rejected action request | Leave status changes to Applied/Authorized needing next-stage action | Next approver | "MyPayrollMaster - Leave {action} Request" | `LeaveRequestController.php:3977`; `EmployeeLeaveRequestController.php:4305`; `LeaveRequestControllerBkups.php:1736`; Nimisha variant `:1698` |
| Leave approved successfully | Leave fully approved | Employee | "MyPayrollMaster - Leave {action} Successfully" | `LeaveRequestController.php:4263`; `EmployeeLeaveRequestController.php:4592`; Bkups `:2012`; Nimisha `:1975` |
| Leave rejected | Leave rejected by approver | Employee | "MyPayrollMaster - Your Leave has Rejected" | `LeaveRequestController.php:4531`; `EmployeeLeaveRequestController.php:4859`; Bkups `:2286`; Nimisha `:2239` |
| Leave cancellation requested | Employee requests to cancel an approved leave | Approver | "MyPayrollMaster - Leave Cancellation Request" | `LeaveRequestController.php:4820`; `EmployeeLeaveRequestController.php:5151`; Bkups `:2573`; Nimisha `:2525` |
| Leave cancellation approved | Cancellation approved | Employee | "MyPayrollMaster - Leave Cancellation {action} Successfully" | `LeaveRequestController.php:5103`; `EmployeeLeaveRequestController.php:5435`; Bkups `:2854`; Nimisha `:2807` |
| Leave cancelled | Cancellation finalized | Employee | "MyPayrollMaster - Your Leave has Cancelled" | `LeaveRequestController.php:5374`; `EmployeeLeaveRequestController.php:5707`; Bkups `:3123`; Nimisha `:3077` |
| Pending-requests reminder digest | Scheduled/manual year-end reminder job | Users with pending approvals | "MyPayrollMaster - Reminder Mail To Action Your Pending Requests" | `YearEndController.php:215` |
| Password reset / delivery | User requests password reset; admin resets a user's password | The user | "Your Password" / "Your New My Payroll Master Password" / (CakeEmail path) "Reset Your My Payroll Master Password" | `PayrollController.php:1869`; `UserCredentialsController.php:977`; `SiteController.php:678` (commented-out `CakeEmail` variant) |
| Access/credentials info | New user credential set created | The user | "My Payroll Master - Access Informations" | `UserCredentialsController.php:552` |
| **Birthday wish** | HR/manager clicks "Send" in the birthday `wish_modal.ctp` on the Dashboard/DashboardNew birthday widget → `Dashboard/convertimage` | The birthday employee (+ BCC `projects@greatleap.tech`) | "{event} of {name}" (e.g. "Birthday of Jane Doe") — HTML body with a merged banner image (background template + employee avatar composited server-side) | `DashboardController.php:3403` region (birthday branch) and `:3589-3630` (work-anniversary branch); duplicated in `DashboardNewController.php:4155`,`:4439` |
| **Work-anniversary wish** | Same modal, "Work Anniversary" event type | Employee (+ BCC) | "{event} of {name}" with computed years-of-service and ordinal suffix (ST/ND/RD/TH) | `DashboardController.php:3576-3630` |
| Upgrade-plan / contact-sales request | User submits the in-app "Upgrade Plan" form (admin-facing, likely a billing/plan-upgrade CTA) | `sales@greatleap.tech` (+ BCC `projects@greatleap.tech`), reply-to = submitter's email | "Upgrade Plan Request from {senderEmail}" | `DashboardController.php:4655-4731` (`sendFormEmail()`); duplicated `DashboardNewController.php:4895` |
| Registration test / generic test mail | Dev/test action, not user-facing | Hardcoded `bineshbabu.t@gmail.com` | "Test Mail From Forsight" / "Here is the subject" / "Test mail" | `EmailComponent.php:44-46`; `SiteController.php:1779`,`:1803` |

### C. Flash / inline status messages (`setFlash`, `$.notify`, alert banners) relevant to this cluster
- `View/Pages/home.ctp:38-40`: login error banner — "Invalid Username or Password", "Account locked for security reasons. Please contact Administrator." (from `PagesController.php:89`,`:64`).
- `View/Dashboard/wish_modal.ctp:57-70`: client-side `$.notify()` toast — success (server response text) or "Error:Message not sent" on AJAX failure.
- `View/Layouts/default.ctp:1392-1402`: generic `$.notify()` success/danger toast pattern used by an in-page "upgrade" form handler (ties to notification item "Upgrade-plan request" above).
- `AnalysisController.php` (`duplication_founds()`, `salary_structure_allocate_issues()`): raw HTML `alert alert-danger` banners injected via AJAX response body (not a flash-message framework call) — "Issue Found! Duplicate {X} Found", "Issue Found! Financial Year Not Added", "Issue Found! Salary Not Uploaded", "Issue Found! Multiple Heads Found", "Issue Found! Salary Structure Not Assigned".

---

## Cross-cutting findings for migration planning

1. **Three parallel dashboard implementations** exist (`Dashboard`, `DashboardNew`, `BusinessDashboard`), all re-implementing the same admin-vs-employee-vs-hierarchy branching logic (`user_access menu_id='0'` check) and much of the same SQL (recursive hierarchy walk, age/department/branch charts, salary trends). `DashboardNew` (routed as `/Analytics`) is the actively maintained one based on edit-comment density; `Dashboard` (legacy) still owns the wish-email and menu-audit actions that `DashboardNew` duplicates rather than reuses; `BusinessDashboard` is the most chart/BI-focused and has the richest AJAX drill-down API. Recommend consolidating into one dashboard in the migration, informed by which tiles are actually wired to real data vs. stale/hardcoded (see below).
2. **Dead/buggy widgets found**: hardcoded-date absent-count queries (`2022-12-01`, `2024-05-01`) in both `DashboardNewController.php:1007-1044` and `BusinessDashboardController.php:264-304`; unbound `Pemp_fkey` placeholder in `AnalysisController.php:107`; empty-string `emp_fkey` filter bug in `DataUploaderController.php:879`; duplicate identical functions in `ApiRequestController.php` (`listdepartments1forcombo`/`listdepartments1sforcombo`).
3. **No centralized email service**: despite `EmailComponent` being registered app-wide, virtually every feature sends mail via its own inline PHPMailer block with independently hardcoded SMTP credentials (Zoho, Gmail, Oracle Cloud all appear across different controllers) — a real risk item for secrets handling in the new stack, and an opportunity to build one proper notification/email service during migration.
4. **Notification bell is mostly non-functional**: 3 of 5 computed notification types don't reliably render for real tenants (birthday reminder markup is commented out; site-expiry and CTC-increment reminders only ever display for `company_code=='DEMO'`).
5. **`InfoController` is an unauthenticated `phpinfo()` leak** — do not port; flag to the user as a live security issue in the legacy app.
6. **`Controller/Component/DashboardManagementComponent.php` contains `LoginManagementComponent`'s code**, not dashboard logic — needs a maintainer follow-up before this component can be documented for migration.

---

## 13. Statutory, Access Admin & Mobile API

# Statutory & Access Admin Controllers + Mobile API Layer

Legacy root: `D:\Projects\RIZOMigration\legacy`

Duplicates noted once, not analyzed further:
- `Controller/UserControllerssss.php` — stray duplicate/backup of `UserController.php` (typo'd filename, not routed under normal CakePHP conventions since class name inside would collide; likely dead file).
- `Controller/statutoryReportsController.php` (lowercase) — separate from `StatutoryReportController.php`/`StatutoryRegistersController.php`; out of scope, not analyzed.
- Every `*.ctp#backup_*`, `*.ctp#bkup_*`, `*_bkup*.ctp` file in `View/Useraccess`, `View/User`, `View/UserCredentials`, `View/StatutoryRegisters` — legacy manual backups left in place by devs (bindu/arul/megha/akshay/sinsiya), not live views. Skipped per instructions.

---

## PART A — Controllers

### 1. AccessController.php — NOT a menu-access admin screen

`Controller/AccessController.php:36-89`. Despite the name, this is a **single-sign-on bridge for a legacy "Leaveapi"/portal handoff**, unrelated to `user_access`/menu permissions.

- `$name = 'Leaveapi'` (`AccessController.php:44`), extends bare `Controller` (not `AppController`), so it bypasses the normal `beforeFilter` user_group gate entirely (`AccessController.php:36`).
- Single action `checkLogin()` (`AccessController.php:59-87`): reads `$_GET['fullstring']`, `company_code`, `emp_company_id`, `emp_email`, `code`; calls a MySQL stored function `single_signon_fn(...)` directly via raw `mysql_connect`/`mysql_query` (old ext/mysql API, not CakePHP's DB layer) against the control DB (`AccessController.php:65-70`).
- If the function returns non-`0`, it parses `database||user_id` from the result, looks up `mob_user_credentials` in that tenant DB, and **redirects to `Site/login` with `user_id` and `password` in the querystring** (`AccessController.php:72-81`) — i.e. it 302s to the login page with the plaintext-hashed(?) password exposed in the URL. This is a real security smell worth flagging for the migration (credentials leaking via GET/redirect/browser history/referrer).
- No forms, no notifications, no AJAX beyond this bridge endpoint.

INFERRED: This looks like a bridge used by an external portal (`myportalapi.mypayrollmaster.online`, referenced elsewhere in `UserCredentialsController.php:304`) to hand off a logged-in user into this legacy app without re-entering credentials.

---

### 2. UserAccessController.php (class `UseraccessController`) — THE per-user/per-menu permission admin screen

`Controller/UserAccessController.php`. This is the real "menu allocation" admin screen the user asked to trace in detail.

**Purpose**: Lets an HR/admin user pick an employee and toggle which `emp_menu` items (and, in a newer add-on system, which licensed "features") that employee's login can see, writing rows into the `user_access` table keyed by `(user_fkey, menu_id)`.

**Who can access it**: Gated only by the global `AppController::beforeFilter` `user_group` check (must be 1 or 2, else redirect to `Site/login`) — `Controller/AppController.php:39-46`. There is **no additional `user_group == 1` enforcement inside `UserAccessController` itself** for most actions (e.g. `save`, `delete`, `saveuseraccess` have no group check). The *view* layer partially hides the UI for non-group-1 users:
- `index.ctp:85` — the "Back" button only renders `if ($user_group === 1 && $plan !== 'basic')`.
- `insecs()` (`UserAccessController.php:362-458`) fetches `user_group` but doesn't use it to block; it's used elsewhere in menu/notification logic only.
Effectively this is meant to be Admin-only (HR staff, group 1) by UX convention, but the controller does not hard-enforce it server-side beyond the base session check — an employee (group 2) who knows the URL could hit `UserAccess/save/{emp}/{menu}` directly. INFERRED: worth flagging as an authorization gap to fix in the Next.js rewrite (add explicit `user_group===1` guard server-side).

**Step-by-step flow (grant/revoke a menu)**:
1. Admin opens `UserAccess/index` (or the newer `indexnew`) — `UserAccessController.php:53-129` / `149-237`. Loads the active employee list (`emp_details` joined to `emp_proff`, filtered to `status=1`, with a branch-restriction carve-out for `user_group==2` at companies `GLET`/`ABSG` via `get_branch_code_abs_fn` — `UserAccessController.php:65-76`), and the full `emp_menu` tree split into `arr_parent` (top-level, `parent_id=0`) and `arr_child` (submenus), both filtered to `active='Y'` (`UserAccessController.php:90-105`).
2. View `View/Useraccess/index.ctp:29-57` renders an employee `<select id="emp_pkey">` (select2) and, on change, AJAX-loads `UserAccess/insec/{emp_pkey}` into `#load` (`index.ctp:32`).
3. `insec($emp_pkey)` (`UserAccessController.php:131-147`) runs a `LEFT JOIN` of `emp_menu` to `user_access` scoped to that employee to determine checked/unchecked state per menu (`UserAccessController.php:135-140`), excludes menus flagged `is_default = 'M'` (manual-only?), and also fetches the special "admin dashboard" toggle (`menu_id = '0'`, `UserAccessController.php:137-138`) and the `emp_proff.payro_priv` flag (`UserAccessController.php:145`).
4. `View/Useraccess/insec.ctp` renders three separate jQuery-EasyUI `tree` widgets:
   - `#admin` — single checkbox "My employee Dashboard" bound to `menu_id=0` / `user_access.active` (`insec.ctp:282`).
   - `#admin2` — single checkbox "Inactivate Salary Data" bound to `emp_proff.payro_priv` (`insec.ctp:290`).
   - `#tt` — the full parent/child menu tree, each `<li>` pre-checked from the `active` flag looked up in step 3 (`insec.ctp:306`, `315`).
5. Checking/unchecking a menu node fires `onCheck` (`insec.ctp:15-58`): checked → `GET UserAccess/save/{emp_pkey}/{menu_id}`; unchecked → `GET UserAccess/delete/{emp_pkey}/{menu_id}`. A Bootstrap `$.notify` toast confirms ("Access Allowed For X" / "Removed Access Of X").
6. `save($emp_pkey, $s)` (`UserAccessController.php:539-623`):
   - `$s == 'All'` → bulk-enables every active menu (parent+children) for the employee via `saveAll` per menu row.
   - Otherwise, single menu: looks up any existing `user_access` row for `(emp_pkey, menu_id)` to decide insert vs. update (reuses `user_access_pkey` if found), sets `active='Y'`, and — new cascading behavior — **if the menu has a parent, auto-enables the parent too** (`UserAccessController.php:583-603`) so a submenu can never be checked while its parent stays hidden.
   - Responds `{"msg":"Useraccess saved successfully"}`.
7. `delete($emp_pkey, $s)` (`UserAccessController.php:835-923`): mirror of `save`, sets `active='N'` instead of removing the row (soft toggle). New cascading logic: **if this was the last active child under a parent, the parent is auto-disabled too** (`UserAccessController.php:886-919`), counting remaining active siblings via a subquery.
   - `$s=='All'` bulk-disables everything.
   - Responds `{"msg":"Useraccess updated successfully"}`.
8. `admin($user_pkey, $DD)` (`UserAccessController.php:957-987`) and `admin2($user_pkey, $DD)` (`UserAccessController.php:988-1004`) handle the two standalone toggles from step 4 — `admin` writes to `user_access` with `menu_id='0'`; `admin2` directly flips `emp_proff.payro_priv` (0/1) via raw SQL, unrelated to `user_access` at all.
9. `Employee($user_pkey, $DD)` (`UserAccessController.php:925-956`) is a near-duplicate of `admin()` targeting `menu_id='#'` — dead/legacy code path (also present commented-out earlier in the file at lines 803-833, i.e. a duplicate-of-a-duplicate; the live version is at 925).
10. **Newer add-on/feature layer** (separate from the menu tree, added by "Akshay"/"bindu" per comments): `indexnew()` (`UserAccessController.php:149-237`) additionally loads `features` from the control DB (`controldb.features`, ordered by `display_order`) and `branches` from the tenant DB, feeding a UI (not read in this pass, presumably `View/Useraccess/indexnew.ctp` — not present in the file listing, so this newer screen's view may be missing/renamed) that lets an admin grant licensed **add-on features** per user, optionally scoped to specific branches or "hierarchy" mode:
    - `saveFeatureAccess()` (`UserAccessController.php:239-299`) — AJAX POST `{user_fkey, feature_id, active, mode}`. Writes a `user_access` row with `menu_id=0` (reusing the same table but keyed oddly by feature via a self-healing `ALTER TABLE ... ADD COLUMN is_hierarchy` safeguard at `UserAccessController.php:253`), then fans out into `user_feature_branch_access` — either one row per active branch (`mode='branch'`) or a single `is_hierarchy='Y'` row (`mode='hierarchy'`) (`UserAccessController.php:276-294`).
    - `saveBranchAccess()` (`UserAccessController.php:301-341`) — AJAX POST to reset and re-insert the specific branch list for a `(user_fkey, feature_id)` pair.
    - `getFeatureBranches($user_fkey, $feature_id)` (`UserAccessController.php:343-360`) — AJAX GET returning current branch/hierarchy selection, used to repopulate the UI.
    - `insecs($emp_pkey)` (`UserAccessController.php:362-458`) is the `indexnew`-flavored sibling of `insec()`: only shows `is_default='Y'` system menus, computes the employee's licensed `features` (plan features ∪ company add-ons, filtered by expiry, `UserAccessController.php:408-422`), and cross-references any `user_access` rows whose `menu_id` matches a feature id but isn't a real `emp_menu` row (`UserAccessController.php:434-441`) — i.e. add-on toggles reuse the `user_access` table with feature IDs standing in for menu IDs, which is a modeling smell to flatten out in the new schema.
11. **Auto-provisioning helpers**: `autoAllocateDefault($emp_fkey)` (`UserAccessController.php:494-538`) — one-time-only: if the employee has zero `user_access` rows at all, bulk-inserts every `emp_menu` row flagged `is_default='Y'`. `addDefault($emp_fkey)` / `resetDefault($emp_fkey)` (`UserAccessController.php:625-658`) call a stored procedure `insert_default_menu(emp_fkey)` (`resetDefault` first zeroes out all active rows for that user). `deletemens($emp_fkey)` (`UserAccessController.php:660-675`) mass-deactivates all menus for a user. None of these three are wired into `insec.ctp`'s live UI (the buttons that call them are commented out at `insec.ctp:347-358`) — INFERRED dead/parked feature.
12. `listuseraccess()` (`UserAccessController.php:677-738`) — POST endpoint for a datagrid (likely `easyui-datagrid`) paginating the full menu list with an `accessallow` Y/N column per employee; **has a bug**: `$offset = ($page - 1) * $limit;` at line 686 references `$page`/`$limit` that are never read from `$arr_data` (only defined below at 692+ / not at all) — this will throw an undefined-variable notice in PHP and likely always uses `offset=0`. Flag for behavior parity: don't blindly port this pagination logic.
13. `get()` (`UserAccessController.php:1006-1025`) and `deleteuser($user_access_pkey)` (`UserAccessController.php:1027-1036`) and `saveuseraccess()` (`UserAccessController.php:1038-1071`) are alternate/older single-purpose endpoints for checking/toggling one hard-coded menu ("Dashboard"/"HIerarchy") or soft-deleting a `user_access` row by its own pkey (sets `status=0`, distinct from the `active` flag used everywhere else) — likely superseded by `save`/`delete` but still reachable.

**Notifications**: None emailed; all feedback is client-side `$.notify` toasts driven by the AJAX responses (no server-triggered email/SMS in this controller).

**AJAX/JS endpoints** (all under `UserAccess/`): `insec/{emp_pkey}`, `insecs/{emp_pkey}`, `save/{emp_pkey}/{menu_id|All}`, `delete/{emp_pkey}/{menu_id|All}`, `admin/{user_pkey}/{DD}`, `admin2/{user_pkey}/{DD}`, `addDefault/{emp_fkey}`, `resetDefault/{emp_fkey}`, `deletemens/{emp_fkey}`, `saveFeatureAccess`, `saveBranchAccess`, `getFeatureBranches/{user_fkey}/{feature_id}`, `listuseraccess`, `autoAllocateDefault/{emp_fkey}` (not wired to any current view — call site not found).

---

### 3. UserCredentialsController.php — Employee login-account admin (create/reset/lock, bulk-provision)

`Controller/UserCredentialsController.php`. This is the actual "manage employee login accounts" admin screen (distinct from `UserAccessController`, which manages *menu permissions* for an already-existing account).

**Purpose**: CRUD for `user_credentials` (tenant DB) + mirrored `mob_user_credentials` (mobile app login), i.e. set/reset an employee's ESS + mobile-app password, toggle mobile access, lock/unlock, and bulk-provision an entire branch at once.

**Who can access it**: Same global `user_group ∈ {1,2}` gate only (`AppController.php:39-46`); no controller-level restriction to group 1 found in this file, though the feature is clearly HR/admin-facing (view uses `easyui-datagrid` admin chrome). INFERRED admin-only by convention/menu visibility, not server-enforced.

**Step-by-step flow**:
1. `index()` (`UserCredentialsController.php:59-66`) renders the landing datagrid shell (`View/UserCredentials/index.ctp`, not read in depth this pass), which calls `listCredentials()` (`UserCredentialsController.php:118-237`) for its data — paginated/sortable list of `user_credentials` joined to `emp_details`/`emp_proff`, with search-by-name/employee-id (`:151`) and the same GLET/ABSG branch restriction pattern seen in `UserAccessController` (`:159-170`).
2. Admin picks a row → `form($id)` (`UserCredentialsController.php:239-267`) loads that user's `user_credentials` row LEFT JOINed to `mob_user_credentials` (matched by `user_id`, `:253-263`) into `View/UserCredentials/form.ctp` (not read in depth — a modal form, given `$this->layout = null`).
3. `bulkUpload($id)` (`UserCredentialsController.php:269-277`) renders `View/UserCredentials/bulk_upload.ctp`, a branch picker (`getBranchesListForCombo`) feeding `saveBulkAccess()`.
4. **Single-user save** — `save($bank_id)` (`UserCredentialsController.php:747-927`), POST from `form.ctp`:
   - Reads `status` field: if `'Y'`, treats the submitted `pasword` as a **new password to set**, hashes it (`Security::hash(...)`), sets `reset_login_flag='Y'`, clears `locked`/`incorrect_login_attempt` (`:765-771`) — i.e. checking "Y" on the form both sets a password and force-clears any lockout.
   - `locked == 0` also resets `incorrect_login_attempt` to 0 (`:773-775`) — unlocking a user resets their bad-attempt counter.
   - Mirrors first/last name + plaintext(!) password into `mob_user_credentials` (`:786-793`) — the mobile-app table stores the **plaintext** password (`$arr_mobile_data['password'] = $arr_form_data['pasword']` at `:790`) alongside the tenant DB's hashed copy — a real security gap to flag for the rewrite (mobile API/app apparently needs cleartext compare, or does its own hashing client-side — needs verification against api/v1 login flow, which also shows plaintext `password` fields in the request logs, e.g. `logs/request.txt` "access" action).
   - `Mobile_Allow` Y/N toggles `mobileaccess` on `user_credentials` and inverts `locked` on `mob_user_credentials` (`:795-801`).
   - `punch_type` (radio, presumably W=web/M=mobile modes seen in API logs) is copied to both tables (`:829-833`).
   - If `attr2` (an external "profileId") is set, fires two outbound `curl` calls to a third-party API `myportalapi.mypayrollmaster.online` (`companyHistory_add`, `:299-325`; `empDataThirdparty_update`, `:327-470`) syncing employee master data to an external payroll-master portal — auth via hardcoded `username: profileadmin` / `password: admin&*()` headers (`:313-316`, `:459-463`) — **hardcoded credentials in source**, flag for migration (move to env/secrets).
   - Saves both `mob_user_credentials` and `user_credentials` (`:875,879`).
   - **Notification**: if a new password (`$password1`) was set, calls `sendpasswordemail($email, $userid, $password1, $name)` (`:918-921`, defined `:938-1160`) — sends an HTML email via PHPMailer/SMTP (`smtp.zoho.in`, hardcoded creds `:964-965`) titled "Your New My Payroll Master Password", **embedding the plaintext new password in the email body** (`:1068`), BCC'd to the user and always BCC'd to `projects@greatleap.tech` (`:973`) — i.e. every password-reset email is silently copied to the vendor's internal address, worth calling out explicitly for the migration/compliance conversation.
5. **Bulk provisioning** — `saveBulkAccess()` (`UserCredentialsController.php:472-519`), POST from `bulk_upload.ctp`: takes a branch code + one shared password, hashes it, and calls a stored procedure `user_access_firstime_only(company_code, branch_code, 'null', hashed_password, plain_password)` (`:489`) that presumably bulk-creates/resets credentials for every employee in that branch. On `'SUCCESS'`, queries for everyone in that branch whose account is newly access-allowed + flagged `reset_login_flag='Y'` with a start_date of today (`:496-501`), and BCC's all of them the same "Access Informations" welcome email via `sendAccessMail()` (`:521-745`) — same plaintext-password-in-email pattern, same hardcoded SMTP creds, same silent BCC to `projects@greatleap.tech` (`:551`).
6. `deleteuser($bank_id)` (`UserCredentialsController.php:279-288`) — soft-deletes by setting `emp_details.status = '0'` (NOTE: this disables the *employee record*, not just the credential — broader blast radius than the method name suggests).
7. `resetmobiles()` (`UserCredentialsController.php:290-297`) — AJAX POST `{user_pkey}`: clears `mob_user_credentials.securitycode`/`macid`/`imei`, forces `token='Y'` — i.e. "de-register this employee's phone" so their next mobile login re-binds a new device.
8. `getusers()` (`UserCredentialsController.php:68-116`) — AJAX autocomplete search by name/employee-id, used by an (now largely commented-out) easy-autocomplete widget referenced in `View/Useraccess/index.ctp:34-51`.
9. `chekPassword($oldpassword)` (`UserCredentialsController.php:1161-1169`) — dead/broken: reads `$_REQUEST['oldpassword']`, hashes and looks up a user by password but **never restricts by user**, so this would match *any* account with that password hash; also the function has no `return`/`echo` of the match result — it just echoes back the raw input unchanged (`:1168`). Effectively non-functional; do not port as-is.
10. `savedata()` (`UserCredentialsController.php:1170-1196`) — hardcoded to a single specific employee (`$userid = 'GLET100132'`) for re-triggering the third-party sync; clearly a one-off debug/ops utility left in the controller, not a real feature.

**Forms**: `form.ctp` (fields: user_id, password, email, first/last name, mobile access Y/N, punch type, lock/unlock, profile ID) and `bulk_upload.ctp` (branch selector + shared password) — not read line-by-line this pass (not required per the field-list already recoverable from `save()`'s consumption of `$arr_form_data`).

**Notifications**: Two email templates, both PHPMailer/SMTP (`smtp.zoho.in`), both embed the plaintext password, both BCC `projects@greatleap.tech`:
- `sendpasswordemail()` (`:938`) — single-user password (re)set.
- `sendAccessMail()` (`:521`) — bulk branch provisioning "welcome" email.

**AJAX/JS endpoints**: `UserCredentials/getusers`, `listCredentials`, `form/{id}`, `bulkUpload`, `deleteuser/{bank_id}`, `resetmobiles`, `save/{bank_id}`, `saveBulkAccess`.

---

### 4. UserController.php — NOT admin user management; it's the logged-in user's own profile/account screen

`Controller/UserController.php`. Despite the generic name, every action here operates on **`$this->Session->read('emp_fkey')` / `login_user_id`** — i.e. this is the "My Profile" self-service page for whoever is currently logged in (both group 1 admins and group 2 employees), not an admin tool for managing *other* users. Do not confuse with `UserCredentialsController` (which is the actual admin tool).

**Who can access it**: Same base session gate only. Internally branches on `user_group`:
- `user_group==1` (admin/HR staff): profile data comes from `CentralUserCredentials` in the **control DB** (`UserController.php:343-355`), rendered via `View/User/profile.ctp`.
- `user_group==2` (employee/ESS): profile data comes from `emp_details`/`emp_proff`/`employee_info` in the **tenant DB** (`UserController.php:357-413`), rendered via `View/User/user.ctp`, and additionally shows an `activity` log for that employee (`:403`).

**Step-by-step flow**:
1. `profile()` (`UserController.php:334-414`) — landing action, branches as above, also reads `comp_contact_info.plan` to gate UI (`:338-339`).
2. `saveavatar()` (`UserController.php:59-146`) — raw `$_FILES['avatarfile']` upload handler (no CakePHP FileUpload helper): validates via `finfo` MIME sniffing (jpg/png/gif only, `:93-102`) and a 1MB size cap (`:87-89`), names the file by `sha1_file(...)` of its own contents (content-addressed, avoids collisions/overwrites, `:109`), and moves it to `img/avatar/`. For admins (`user_group==1`) it updates `CentralUserCredentials.avatar` in the control DB keyed by `user_pkey` (`:117-127`); for employees it directly `UPDATE`s `user_credentials.avatar` by `emp_fkey` via raw SQL (`:139`) — inconsistent (one path uses the ORM `save()`, the other raw SQL), and the raw-SQL path is **not parameterized/escaped** (`$emp_fkey` interpolated directly into the query, `:139`) — a SQLi vector if `emp_fkey` were ever attacker-controlled (it's session-derived here, so low real risk, but worth flagging as a pattern to fix).
3. `savePassword()` (`UserController.php:416-494`) — change-own-password: validates all three fields present and `password1==password2` (`:432-452`), hashes and compares the *current* password against the correct source table depending on `user_group` (control DB for admins, tenant DB for employees), and on success also plaintext-mirrors the new password into `mob_user_credentials` (`:483`) — same plaintext-in-mobile-table pattern as `UserCredentialsController::save()`. Returns JSON `{success, msg}` consumed by the profile page's own-password-change form (not read this pass).
4. `saveNames()` (`UserController.php:517-556`) — change own first/last name, same admin-vs-employee table branching. Has a **latent bug**: in the `user_group != 1` (employee) branch it references `$password_new`/`$password1` (`:549-550`) which are never defined in this function — will throw undefined-variable warnings and blank out the employee's stored password on every name change (since `$arr_data['password'] = $password_new` writes `null`/empty into `UserCredentials.password`). This looks like a copy-paste bug from `savePassword()`. **Flag prominently** — this is a functional bug that could be locking employees out of their accounts silently whenever they edit their name, and must be fixed (not faithfully reproduced) in the Next.js rewrite.
5. `savebasics($param)` (`UserController.php:496-515`) — generic save of `EmployeeDetails` fields (whatever the posted form contains), tagged with `modified_by = login_user_id` (`:502`). Used by the employee-side "edit my basic details" form (`savebasics_form.ctp`).
6. `load_basic_details()`, `savebasics_form()` (render only), `loadImage()`, `change_image()` (`UserController.php:170-332`) — supporting AJAX/partial-view loaders that assemble the same joined employee/professional/branch/designation dataset for the profile edit UI; `change_image()` is an empty stub (`:170-173`).
7. `checkpassvalidation($param)` (`UserController.php:148-168`) — meant to verify a submitted "old password" against the tenant DB, but hashes with `null` conditions passed nowhere sensible and **the function `return`s 1/0 instead of echoing JSON**, while `autoRender=FALSE` — since CakePHP AJAX actions must `echo`, a bare `return` produces an empty response body; the caller would always see a blank/failed AJAX response. Likely dead/broken code, do not port literally.

**Forms**: profile edit (names, basic employee fields), avatar upload, change-password (current/new/confirm) — field names recovered from controller consumption (`$_REQUEST["password"|"password1"|"password2"]`, `$arr_form_data['first_name'|'last_name']`, `$_FILES['avatarfile']`).

**Notifications**: none server-triggered (no email sent from this controller — password/name changes are silent, unlike the admin-driven resets in `UserCredentialsController` which do email).

**AJAX/JS endpoints**: `User/saveavatar`, `checkpassvalidation/{param}`, `savePassword`, `savebasics/{param}`, `saveNames`, `load_basic_details/{param}`, `loadImage/{param}`, `profile`.

---

### 5. StatutoryUploadsController.php — Ad-hoc statutory Excel exports (PF/ESI-style wage data)

`Controller/StatutoryUploadsController.php`. Note the View folder mapping is crossed vs. the class name: this controller's templates live under `View/StatutoryRegisters/` (`index.ctp`, `pf.ctp`, `pfdownload.ctp`, `statutory.ctp`) — a legacy CakePHP folder-vs-controller-name mismatch worth knowing about before porting routes.

**Purpose**: Generates a downloadable `.xls` "Statutory" report (ESI number, name, working days, gross wages, zero-days reason code, last working day) for a selected payroll month, and a near-identical `pfdownload` variant (PF-oriented, all active employees rather than only ESI-registered ones).

**Who can access it**: base session gate only; no explicit `user_group` check in this controller (`Controller/StatutoryUploadsController.php` has none). INFERRED admin-only by menu placement.

**Step-by-step flow**:
1. `index()` (`:55-57`) — empty stub, presumably just renders `View/StatutoryRegisters/index.ctp` as a landing/menu page (not read in depth).
2. `statutory()` (`:59-277`) — reads `$_REQUEST['reportfrom']` (a month), restricts to `emp_details` rows with a non-empty `esi` number (`:68`), and for each employee computes: PF/ESI/WWF employer contribution amounts (via `tax_salary_components` lookups keyed by fixed pkeys `10`/`12`/`14+1`, `:78-83` — magic numbers, fragile if that lookup table is reseeded), gross salary sum, loss-of-pay days from `payroll_master`, resignation/termination reason-coding (numeric codes 1/2/3/10 mapped from `termination.Reason` text, `:112-125`), and last device-attendance punch date. Streams the result as an `.xls` via `PHPExcel_Writer_Excel5`, writes a temp file to the controller's own directory and `unlink()`s it after `readfile()` (`:266-272`) — i.e. synchronous file-based export, not a stream; concurrent requests could collide on the same filename (`Statutony.xls` — also note the typo, `:159`).
3. `pf()` (`:278-280`) — empty stub, renders `pf.ctp` (a form for `pfdownload`, presumably date input) — not read in depth.
4. `pfdownload($from)` (`:282-354`) — same computation as `statutory()` but without the ESI filter (all `status=1` employees) and without the Excel-writing tail; instead just `$this->set('arr_salary_for_template', ...)` for `pfdownload.ctp` to render inline (likely an HTML preview or a different export trigger not shown in this controller).

**Forms**: a month picker (`reportfrom`) feeding both `statutory()` and implicitly `pf()`/`pfdownload()`.

**Notifications**: none.

**AJAX/JS endpoints**: `StatutoryUploads/statutory`, `pf`, `pfdownload/{from}` — all effectively direct-download/report links, not JSON APIs.

---

### 6. StatutoryRegistersController.php — Statutory Registers report generator (Wage Sheet / Muster Roll / Service Record)

`Controller/StatutoryRegistersController.php` (2686 lines total; three private report-builder methods account for the bulk of the file: `Generatewage` lines 430-1668, `generatemusterrollreport` lines 1669-2293, `generateServiceRecordReport` lines 2294-2686 — these were not read line-by-line since they are large but structurally repetitive PHPExcel/PDF formatting code following the same pattern already fully traced in `StatutoryUploadsController::statutory()`; the public dispatch surface below was read in full).

**Purpose**: A generic "criteria-driven" statutory report builder — the admin picks a report type (Wage Sheet, Muster Roll, or Service Record — the set itself varies by company, see below), a date range, and one or more filter criteria (Employee / Branch / Department / Grade / Vertical / etc.), then views/downloads the result as Excel or PDF.

**Who can access it**: base session gate only; the report *type list itself* is customized per `company_code` inside `hrreports()` — company `HRBL` employees (`user_group==2`) only see "Muster Roll" (`StatutoryRegistersController.php:67-71`), all other users see Muster Roll + Wage Sheet + Service Record (`:73-77`). This is company-specific business logic baked into the controller — flag for the migration's feature-flag/tenant-config design (this shouldn't stay hardcoded to `'HRBL'` in Next.js; should come from a config/entitlement table, tying back to the `features`/`plan_features` system seen in `UserAccessController`).

**Step-by-step flow**:
1. `hrreports()` (`:62-80`) — landing page, builds the report-type dropdown per company rule above. View: `View/StatutoryRegisters/hrreports.ctp`.
2. On dropdown change, JS calls `StatutoryRegisters/changereporttype/{type}` (`View/StatutoryRegisters/hrreports.ctp:96-101`) → `changereporttype($type)` (`StatutoryRegistersController.php:86-117`) loads that type's available filter criteria from `ReportCriterias` (rows where `status=1 AND reporttype=$type`, `:96/100/105`) and renders `showreport.ctp` into the page.
3. `showreport.ctp` (`View/StatutoryRegisters/showreport.ctp`) renders: a month-range picker (shown only for `Musterroll`/`wage`/`FactoryMusterroll`/`ServiceRecord` — `showreport.ctp:8-16`), a "Criteria" dropdown populated from step 2's `arr_reportcriterias`, and (for `type=='employee'`, a report type not in the current `hrreports()` list but still supported by this generic template) a drag-and-drop field-picker fed by `listemployeefields()` (`StatutoryRegistersController.php:386-418`, pulls field labels from a `Vendor/ReportFields/EmployeeInformationFields.php` class).
4. Picking a criteria fires `loadCriteriaItems(index)` (`showreport.ctp:370-374`) → `StatutoryRegisters/loadcriteriaitems/{index}/{criteria}` → `loadcriteriaitems($index, $str_criteria)` (`StatutoryRegistersController.php:142-158`) — validates the model name exists (`_modelExists`, `:420-424`) and renders `loadcriteriaitems.ctp` (a checklist widget, not read in depth), which itself AJAX-loads the actual item list via `listcriteriaitems($str_criteria)` (`:160-252`) — e.g. for `EmployeeDetails` criteria, returns active employees (again with the GLET/ABSG branch restriction pattern, `:215-226`); for `Units`, returns branches.
5. Admin can add more criteria rows via `addOneReportCriteria()` (`showreport.ctp:376-392`) → `StatutoryRegisters/addreportcriteria/{type}/{newindex}/{currentCriteriaCsv}` → `addreportcriteria(...)` (`StatutoryRegistersController.php:123-136`) — returns the *remaining* unused criteria options (`reportcriteria NOT IN (...)`) so the same criteria type can't be picked twice, rendering `showcriteria.ctp`.
6. "View" (`viewReport()`, `showreport.ctp:333-367`) POSTs the whole form to `StatutoryRegisters/generatereport/{type}` and injects the HTML response into `#reportCon` in-page; "Download Excel"/"Download PDF" (`downloadReport(mode)`, `:160-188`) instead full-page-submits to `StatutoryRegisters/generatereport/{type}/{mode}`. Both funnel into `generatereport($type, $mode)` (`StatutoryRegistersController.php:362-384`), which dispatches by `$type` to the matching private builder (`Generatewage`/`generatemusterrollreport`/`generateServiceRecordReport`) and then unconditionally calls `downloadHistory($type, $mode)` (`:383`).
7. `downloadHistory($type, $mode)` (`StatutoryRegistersController.php:253-360`) — **audit trail**: records every report generation (view/PDF/Excel) into `ReportAudit`, capturing report type, date range, chosen criteria (with a small hardcoded label map at `:289-331` translating internal model names like `EmployeeGrossDetails` → "belonging to a Designation"), which items were selected and how many, the download mode, and the logged-in `login_user_id`/`user_name`. This is a genuine compliance/audit feature to preserve in the rewrite.
8. UI niceties layered on later by "Akshay": a print button (`#btn-submit3`, shown only after a successful "View", `:264-268` / `:293-330`) that opens a formatted print window of the just-rendered report HTML; and for `Musterroll` at company `HRBL` specifically, the "view" (`#btn-submit`) button is hidden and replaced with a note that printing is only available via the eye icon (`showreport.ctp:118-124`) — another hardcoded per-company UI branch.

**Forms**: report-type select, month/date-range picker, dynamic criteria rows (criteria-type select + item multi-select checklist), field-picker (employee-type reports only).

**Notifications**: none emailed; `ReportAudit` write is the only side effect besides the file download itself.

**AJAX/JS endpoints**: `StatutoryRegisters/changereporttype/{type}`, `loadcriteriaitems/{index}/{criteria}`, `listcriteriaitems/{criteria}`, `addreportcriteria/{type}/{newindex}/{csv}`, `generatereport/{type}[/{mode}]`, `listemployeefields`.

---

## PART B — `api/v1/` Mobile REST API Layer

**Framework**: Slim Framework 3 (`api/v1/composer.json:16` — `slim/slim: ^3.1`), using the official **Slim Skeleton** starter (`slim/php-view` for templates, `monolog/monolog` for logging) — `api/v1/composer.json`, `api/v1/README.md`. Entry point `api/v1/index.php:12-30` follows the stock skeleton bootstrap: `vendor/autoload.php` → `src/settings.php` → `src/dependencies.php` → `src/middleware.php` → `src/routes.php` → `$app->run()`.

**IMPORTANT caveat**: The actual application source is **not present in this checkout**. `api/v1/index.php` requires `src/settings.php`, `src/dependencies.php`, `src/middleware.php`, `src/routes.php`, and `vendor/autoload.php`, but **neither `src/` nor `vendor/` exist anywhere under `api/v1/`** (confirmed via directory listing — only `index.php`, `composer.json`/`composer.lock`, `README.md`, `CONTRIBUTING.md`, `.htaccess`, `logs/`, `error_log`/`error_log_old`, `phpunit.xml`, and 14 loose `.jpg`/1 `.png` files sit at `api/v1/` root). This means:
- The actual controller/route code (which the `composer.json` autoload section says lives at `src/App/Controller/ClientdbController.php`, `ApiController.php`, `SecurityController.php` — `api/v1/composer.json:23-31`) is missing from this snapshot. I cannot cite exact endpoint route definitions or auth-check code.
- Everything below about *what the endpoints do* is reconstructed from the **request/response logs** (`api/v1/logs/request.txt` and rotated copies `request_04-Jun-2026-0130PM.txt`, `request_08-Jun-2026-0130AM.txt`, plus several manual backups), not from source. Treat as INFERRED and verify against the real `src/` once located (likely deployed directly on the server outside this repo export, or in a `.gitignore`d path — `api/v1/.gitignore` exists and is only 34 bytes, consistent with ignoring `vendor/`/`src/` build artifacts... though ignoring `src/` itself would be unusual; more likely `src/` was simply never committed to whatever export produced this `legacy/` folder).
- All requests appear to hit a **single endpoint** that dispatches internally on an `action` request parameter (classic "action-router" pattern, not per-resource REST paths) — every logged request/response pair shows `[user_id] => ..., [action] => ..., ...` as the POST body (`logs/request.txt` throughout), not distinct URL paths. INFERRED: likely a single `POST /` or `POST /api` route in the missing `routes.php` that pattern-matches `action` to an internal handler, Slim's routing otherwise unused for this traffic.

**Endpoints (by `action` value)** — frequency counted across `logs/request.txt` + `logs/request_04-Jun-2026-0130PM.txt` (~30M lines of combined log; these two files sampled, not all rotated logs):

| action | count | purpose (INFERRED from request/response fields) |
|---|---|---|
| `markattendance` | 5534 | Mobile check-in/out punch. Request carries `login_auditor` JSON array with `latitude`/`longitude`/`accuracy`/`in_out`/`time_check`/reverse-geocoded `locationName`. Response echoes back an `auditor_pkey` + `uploaded_time` — see `logs/request.txt` sample around line 13138. |
| `getLastPunch` | 5365 | Returns the employee's most recent in/out punch (`lastAction`, `data[0].C1/LOGDATE/C3`) — used to render the mobile home screen's punch button state. |
| `getHomePageDatas` | 4579 | Mobile app home/menu bootstrap — returns feature flags (`expenseEnabled`, `customerVisits`, `leaveApproval`, `leaveItems`, `punchtype` (W/M), `customervisitMandatory`) — i.e. this is the per-employee **entitlement/menu response for the mobile app**, analogous to `UserAccessController`'s menu tree but mobile-specific. |
| `branches` / `listbranches` | 3970 / 220 | Branch list for punch-location or filter dropdowns. |
| `detailattendancecalendar` | 3529 | Full-month calendar view of attendance, returns a per-day array with `selected`/`selectedColor`/`dots` styling hints — directly feeds a calendar UI component (react-native-calendars style props). |
| `MissAction` | 2993 | Likely "missed punch" / regularization-request submission (paired frequently with `markattendance` in the logs). |
| `attendance_details_by_date` | 2495 | Drill-down into a specific day's punches. |
| `leave_list` / `leave_items` / `leave_request` / `leave` / `Leave` / `Approved` | 1872/791/158/180/448/176 | Full leave-request lifecycle: list existing requests, list leave-type items, submit a new request, and (for managers) approve. Case-inconsistent action names (`leave` vs `Leave`) suggest organically-grown, unversioned endpoint additions. |
| `Sync` | 1077 | Generic sync call — sample response is "Customer Visit Updated Successfully" (`logs/request.txt` line ~1124 region), so this batches up offline-queued customer-visit records. |
| `payslip` | 984 | Fetch/download payslip data for ESS. |
| `list_month` | 939 | Month-list dropdown helper (paired with most attendance/payslip calls). |
| `reg_attendance_list` / `reg_attendance_save` / `regularized_attendance_list` | 598/97/44 | Attendance regularization request list/submit. |
| `expence_lists` / `add_expense` | 418 / 138 | Employee expense claims list/submit — this is the source of the `{empfkey}_{expensepkey}_ei_{timestamp}.jpg` receipt-photo naming convention seen in the loose `.jpg` files at `api/v1/` root (confirmed via a matching record in `logs/request.txt`'s tail: `emp_expenses_pkey=8580, emp_fkey=148, image=148_8580_ei_...jpg`) — **these receipt images are stored directly in the API's document root**, a path-traversal/public-exposure concern worth flagging (any of these images is presumably fetchable by guessing/enumerating the filename, no auth). |
| `update` | 266 | Generic profile/record update (fields not distinguishable from `action` alone). |
| `access` | 40 | **Login/authentication** — payload is `{user_id, action:'access', password, securitycode, history_type:'LOGIN', access_time, latitude, longitude, imei}` (`logs/request.txt` ~line 207120). Failure response: `{success:0, message:'Unauthorized UserID !', data:{invalid_login:1, locked:1}}`. No token/session artifact is visible in the *logged* response body on success in the samples captured (all captured `access` calls in the sampled window happened to fail) — INFERRED the app likely relies on the device-bound `imei`/`securitycode` pair plus straight `user_id` on every subsequent call rather than a bearer token, since **every other action in the logs sends only `user_id`** (occasionally `token`) with no visible auth header captured by this logger (headers aren't logged, only `$_REQUEST` body, so a header-based bearer token can't be ruled out from these logs alone). |
| `resetPassword` | 4 | Password reset from within the app; response returns the **new password in plaintext** in the JSON body (`data => Venkitta@123`, `logs/request.txt`) — matches the plaintext-password pattern already flagged in `UserCredentialsController`/`UserController` on the web side. |
| `ShiftClose` | 50 | End-of-shift action (attendance-related). |
| `getlocationlog` | 14 | Fetch historical location pings (ties to the lat/long tracking seen in `markattendance`/`MissAction`). |
| `getSiteList` | 11 | Site/location list, likely for multi-site attendance or customer-visit check-ins. |
| `P` | 148 | Unidentified — too short/generic an action name to infer purpose from the log sample alone; flag for follow-up if this endpoint needs porting. |

**Auth model summary (INFERRED, not confirmed from source)**: username/password login via `action=access` (plus device `imei` + a client-generated `securitycode`), against `mob_user_credentials` (per the CakePHP-side `UserCredentialsController`/`UserController` code that maintains this table — `Controller/UserCredentialsController.php:778-793`, `1170-1196`). No confirmable bearer-token/session scheme visible in the request logs (only request *bodies* were logged, not headers), and the mobile-side password field is stored **in plaintext** in `mob_user_credentials` per the web-admin code paths that write to it. This whole auth/session mechanism needs to be re-verified against the actual `src/` once it's located, before any 1:1 port — do not assume the current scheme is secure enough to reproduce as-is.

**Is it actively used?**: Yes, clearly and heavily — the log files are enormous (`logs/request_04-Jun-2026-0130PM.txt` alone is ~17M lines / ~15MB, `logs/request.txt` is comparably large) and span real-looking employee IDs, GPS coordinates, and photo uploads across what looks like a live production window in mid-2026 (log timestamps read `2026-06-08`, consistent with "today" being 2026-07-15 per system context — i.e. this log is from about 5 weeks before the present research date, so the API was in active use very recently). Per the task's own caveat, this is a log *sample*, not a full traffic census — action-frequency counts above are from two rotated log files only, not the complete history.

**Loose files at `api/v1/` root worth noting**:
- 14 `.jpg` files matching `{emp_fkey}_{n}_{IMG_timestamp|ei_timestamp}.jpg` — a mix of camera-captured photos (`IMG_YYYYMMDD_HHMMSS` — likely attendance/site-visit selfies) and app-generated (`ei_<epoch>` — "expense image", confirmed via the `add_expense` log correlation above) receipt images, stored directly in the API's public webroot rather than a dedicated uploads/CDN path.
- `1formdesign.png` — looks like a stray design mockup, unrelated to runtime.
- `error_log` (4.96MB) / `error_log_old` (4.29MB) — PHP error logs; not reviewed in this pass but worth mining separately for recurring server-side exceptions if deeper API-behavior verification is needed later.

---

## 14. Timesheet & Full Controller Coverage Check

# TimesheetController + Uncovered Controllers Report

## Part 1: `Controller/TimesheetController.php`

### 1. Purpose

`Controller/TimesheetController.php:36-53` implements the "Attendance Management Sheet" — a monthly, per-employee attendance register / timesheet grid. It renders a spreadsheet-style view (one row per employee, one column per calendar day) showing daily attendance status (`present`, `holiday`, `weekoff`, `leaves`, `others`), lets Admin/HR (and, within limits, employees) drill into and correct individual day statuses, and supports a legacy "attendance register verification" workflow (sign-off on a monthly register before it becomes locked/uneditable). It uses no CakePHP `AppModel` binding of its own — it directly runs raw SQL (`$this->EmployeeDetails->query(...)`) against `emp_detail_timeattandance`, `emp_details`, `emp_proff`, `attendance_register`, `leaveentries`, `emp_leave_transactions`, `working_day_time_procedures`, and calls stored procedures/functions (`emp_detail_att_reg`, `att_start_end_fn`, `time_duration_check`, `time_duration_check_multishift`, `leave_transaction_prc`).

### 2. Who can access it

- Standard app auth applies via `AppController` (`user_group` session var must be 1 or 2, else redirect to `Site/login` — `Controller/AppController.php:39-46`). No additional role check is done inside `TimesheetController` itself (no explicit `isAuthorized`/permission gate found in this controller).
- Behavior differs by `user_group` read from session (`TimesheetController.php:106`, `:241`, `:470-471`, `:1083`):
  - `user_group == 2` (Employee/ESS): branch list is restricted to the employee's own scope. In `index()` (`:471-488`) for company codes `DEMO`, `BKHS`, `GLET` an employee sees only branches tied to their own `emp_proff.attr1`/`emp_fkey` hierarchy (`:479-481`); otherwise, if the employee has `payro_priv` set on `emp_proff` (`:483`), they see all branches, else only their own combo-list branches (`:486`). In `registerbook()`, `emp_condition` is scoped to `emp.attr1 = <emp_fkey>` when a session `emp_fkey` exists (`:100-105`), and in `bulkipdatestatus()` employees (`user_group==2`) can only bulk-edit attendance for **today or later** (`:1110-1112`, `AND att_date >= '$today_date'`), whereas Admin (`user_group==1`) has no such date floor.
  - `registerbook.ctp` also gates the edit affordance in the UI: a day cell is only clickable to edit if `$thisDate >= $joiningDate` AND (`$thisDate >= $currentDate` OR `$user_group == 1`) (`View/Timesheet/registerbook.ctp:270-277`); otherwise clicking shows an alert ("You are not authorized to edit this attendance record!" or "Attendance records cannot be edited for dates before the joining date!").
  - A record is also locked from editing once it has been "verified" into `attendance_register` (`iseditable` flag computed from a count query against `attendance_register` — `TimesheetController.php:155-156`, `:1098-1101`); the UI shows a locked/verified indicator instead of the edit control (`registerbook.ctp:284-286`, alert "Attendance Verified").

### 3. Step-by-step user flow

1. User navigates to `Timesheet/index` (menu-driven, likely reached from Attendance/HR menu — no hardcoded nav link found; menu is dynamic via `emp_menu`/`user_access`). `index()` (`TimesheetController.php:459-497`) loads branch dropdown options (`arr_branches`) and full active employee list (`arr_employees`, `:494`), and renders `View/Timesheet/index.ctp`.
2. `index.ctp` (`View/Timesheet/index.ctp:1-382`) shows three filter controls: **Month** dropdown (last 12 months, `:281-291`), **Branch** dropdown (`:298-309`), **Employee** select2 (populated via AJAX `Timesheet/jsons`, `:62-96`). A "Process" button (`:321`) triggers `filterRegister()` (`:155-176`), which does an AJAX `.load()` of `Timesheet/registerbook/<month>/<emp_fkey>/<branch>` into `#load` div.
3. `registerbook($monthdd, $emp_pkey, $branch)` (`TimesheetController.php:55-247`) is the core action:
   - Resolves the company's configured attendance-period boundaries via SQL function `att_start_end_fn` (`:72-73`, re-run at `:123-124`).
   - Refreshes attendance data first by calling stored proc `emp_detail_att_reg` (`:117`) — this presumably regenerates/recomputes the `emp_detail_timeattandance` rows for the month before display.
   - Pulls the joined attendance dataset (`:129-142`) — employee name, per-day status, latest manual override from `emp_detail_status_update` (via correlated subquery for `MAX(emp_detail_status_update_pkey)`, `:132-137`), and `working_day_time_procedures.minuts_calc_perday` for shift-duration comparison.
   - Per employee, computes summary stats: `editable` flag (whether a verified `attendance_register` entry blocks edits, `:155-156`), leave taken count (`:158-163`), present-day count (`:165-172`, `194-199`), week-off count (`:175`), holiday count (`:177`), LOP days (`:181-208`), calendar days in month (`:217`), and joining date (`:231-232`, used for the "before joining date" edit lock).
   - Sets all of this into `arr_dates`, `employee_attendance`, `month`, `user_group`, `currentDate` and renders `View/Timesheet/registerbook.ctp`.
4. `registerbook.ctp` (`View/Timesheet/registerbook.ctp:1-573`) renders a DataTable (`#LeaveDetailsReports`, `:422-449`, with Excel export button) with sticky first columns (employee name, checkbox, calendar/week-off/holiday/working-days/present/leaves/LOP summary) and one column per date in range. Each day cell shows the combined status code (e.g. `P/P`, `A/LOP`, `WO`, `HO`) color-coded (`:256-265`). Clicking the down-caret on a cell (when editable) calls `attendanceModal(month, empPkey, edtPkey)` (`:464-476`) which opens `Timesheet/editpunch/<month>/<empPkey>/<edtPkey>` in a small modal (`showSmallModalForm`).
5. `editPunch($month, $emp_pkey, $edtPkey)` (`TimesheetController.php:1034-1074`) computes the employee's current financial year, fetches applicable leave-type heads from `salary_head_items`/`leavepolicy` scoped to the employee's `LEAVEPOLICY_GROUP_ID` (`:1048-1051`), and for each leave head computes remaining balance via SQL function `leave_balance_inthe_month_fn` (`:1064`). Sets `emp_detail_timeattandance_pkey`, `status` (current present code, `:1057-1060`), `arr_leave` (available leave types + balances) and renders `View/Timesheet/editpunch.ctp`.
6. `editpunch.ctp` (`View/Timesheet/editpunch.ctp:1-173`) shows three columns of quick-action buttons: **First Half**, **Second Half**, **Full Day** — each with `P`, `LOP`, plus one button per available leave type (with balance check client-side using `#leave_<code>` value, `:73-90` — though note: this element ID pattern (`leave_<newstatuses>`) is not actually rendered anywhere in this view, so the leave-balance guard is effectively a dead/broken check that will always read `undefined`/`NaN` and pass through, i.e. INFERRED: the client-side "No Leave Balance Available" alert never fires because `$('#leave_'+newstatuses)` never matches an element). Clicking a button calls `updateStatuses()` (`:63-165`) which combines first/second half into a combined code and POSTs to **`EditAttendance/chnagestatus`** (`:130-138`, note this endpoint lives in `EditAttendanceController`, not in `TimesheetController` — cross-controller AJAX call). On success it updates the cell text/value and closes the modal (`:143-152`).
7. Employees/HR can bulk-set status for multiple rows at once: on `registerbook.ctp`, checkboxes (`:197`, `:232`) + a status dropdown (`#shift1combo`: `P/P`, `P/A`, `A/P`, `LOP`, `:170-178`) + an "all dates"/"blank dates" option selector (`:183-187`) + **Bulk Update** button (`:189`) call `updateselecteditem()` (`:489-554`), which POSTs the selected `edtaPkey`/`empPkey` pairs, chosen `status`, `adstatus` (option filter) and `monthYear` to `Timesheet/bulkipdatestatus`.
8. `bulkipdatestatus()` (`TimesheetController.php:1076-1165`) is the server-side bulk handler: for each selected employee it re-checks the `attendance_register` lock (`:1098-1101`); if unlocked, it applies a joining-date floor (`:1103-1109`) and, for employees (`user_group==2`), a "today or later" floor (`:1110-1112`); it then selects the target `emp_detail_timeattandance` rows (optionally only "blank" ones, `:1114-1118`) and for each row: if setting status `LOP`, calls `checkLeaveExists()` to cancel any conflicting approved leave (`:1125`, `:1166-1238`) then calls `AddLeave()` to insert a new LOP-mapped leave entry via `AddLeave()`/stored proc `leave_transaction_prc` (`:1139`, `:1239-1309`); otherwise it just inserts an override row into `emp_detail_status_update` (`:1145`). Responds with JSON `{success, array_success}`.
9. A separate "process/verify register" workflow exists in parallel (older UI, not obviously wired to `registerbook.ctp`'s current buttons, but present in the controller and possibly used elsewhere / by a different view such as `showregister`/`showregistertab`):
   - `showregister()` (`:517-583`) and `showregistertab($verified)` (`:585-651`) prep data/legend for an "AttendanceRegister" listing (statuses Present/On Leave/Week Off/Holiday/Absent/LOP/Others, `:545-581`) but **no `.ctp` views exist for `showregister`, `showregistertab`, `empregisterbook`, `empindex`, or `filter`** in `View/Timesheet/` (only `index.ctp`, `registerbook.ctp`, `editpunch.ctp` exist — confirmed via directory listing). INFERRED: these actions are dead/unreachable in the current UI (would throw a CakePHP "view not found" error if hit directly), or they were designed to render inside a shared/other-controller layout not present in this codebase snapshot.
   - `listregisterentries()` / `listverifiedregisterentries()` (`:658-806`) are AJAX JSON endpoints (paged) for unverified (`isdelete="Y"`) vs verified (`isdelete="N"`) `AttendanceRegister` rows — likely intended for a jqGrid/EasyUI-style grid.
   - `processregisterentries()` (`:808-821`) calls stored-proc wrapper `AttendanceRegister->insertUpdateAttendanceRegisterProc()` to generate/refresh register rows for a branch+month.
   - `verifyregisterentries($registerid)` (`:823-857`) marks one or more register rows verified (`isdelete='N'`) after checking via `checkifregistercanverify()` (`:890-931`) that no day field is missing/null in the fixed `FIELD1..FIELDn` register schema.
   - `updateregisterentries($registerid)` / `submitregisterentry()` (`:933-986`) present/submit a form to fill in missing `FIELDn` punch values for a register row, auto-verifying via `verifyregisterentries()` once all fields are filled (`:976-978`).
   - `loadattendanceregisterheader()` (`:859-887`) returns a JSON column-definition array (for a grid) with one `FIELDn` column per day in the period plus summary columns.
   - INFERRED: This whole "AttendanceRegister" verification sub-flow (steps in this list) appears to be legacy/superseded functionality — its comments date back to "santhosh, 02 Aug 2015" / "21 Feb 2016" (`:655-656`, `:830`) whereas the primary `registerbook`/`editpunch` flow has many 2024 edits by "Akshay", suggesting the newer flow is the actively used one and the register-verification grid may be orphaned or used by another (uncovered) controller's view.
10. `empregisterbook()` (`:251-317`) and `empindex()` (`:499-515`) are near-duplicates of `registerbook()`/`index()` (older/employee-facing variants — e.g. `empregisterbook` calls stored functions `time_duration_check`/`time_duration_check_multishift` instead of `emp_detail_att_reg`) but, as noted, have no corresponding `.ctp` views in this codebase, so they are likely dead code paths preserved from an earlier iteration. `filter()` (`:423-457`) similarly has no view and additionally references an undefined `$monthdd` variable (`:429` — PHP notice, would produce an empty/incorrect date), reinforcing that it's dead/broken.

### 4. Forms

- **Filter form** (`index.ctp`): Month select, Branch select, Employee select2 (AJAX-backed) — not a POST form, drives an AJAX `.load()` (`registerbook.ctp:172-176`).
- **Bulk status update** (`registerbook.ctp:167-189`): Status dropdown (`P/P`,`P/A`,`A/P`,`LOP`), Option dropdown (`all`/`blank` dates), row checkboxes, "Bulk Update" button → POST `Timesheet/bulkipdatestatus`.
- **Single-day edit** (`editpunch.ctp:5-53`): grid of quick-status buttons (First Half / Second Half / Full Day × P/LOP/HO/WO/leave-types) → POST `EditAttendance/chnagestatus` (external controller, not `TimesheetController`).
- **Register field-fill form** (`updateregisterentries`/`submitregisterentry`, `:933-986`): server-side handler exists (expects `hid-registerid`, `hid-count-missing`, `reg-date-N`, `hid-reg-field-N` POST fields) but no corresponding view file was found for `updateregisterentries.ctp`.

### 5. Notifications

- No server-side email/SMS notifications found in `TimesheetController.php`.
- Client-side toast notifications only (via `$.notify`, `registerbook.ctp:541-551`): "Status updated successfully. Process attendance again." (success) / "Attendance Verified. Cannot update status." (failure) after bulk update.
- `editpunch.ctp:156` uses `$.messager.alert('Failed', "Error occured while removing record", 'info')` on AJAX failure (note: this message text is copy-pasted from a delete-record flow and is misleading here — it's really a status-update failure, not a "removing record" failure).

### 6. AJAX/JS endpoints

All defined in `TimesheetController.php`, called from `View/Timesheet/*.ctp`:
- `Timesheet/registerbook/:month/:emp_pkey/:branch` — `:55`, loaded via `.load()` from `index.ctp:173,195,218`.
- `Timesheet/jsons/:branch/:resigned` — `:345`, select2 AJAX source for employee dropdown, `index.ctp:68`.
- `Timesheet/getbranches` — `:320`, select2 AJAX source for branch dropdown, `index.ctp:106` (note: function name says "for bank search box" per comment `:319` — copy-pasted comment, actually branches).
- `Timesheet/editpunch/:month/:emp_pkey/:edtPkey` — `:1034`, opened as modal from `registerbook.ctp:472`.
- `Timesheet/bulkipdatestatus` (POST) — `:1076`, called from `registerbook.ctp:528`.
- `EditAttendance/chnagestatus` (POST) — called from `editpunch.ctp:131`, but implemented in a **different** controller (`EditAttendanceController`, already covered under the Attendance cluster) — cross-referenced here because it's the actual mutation endpoint for the single-day edit modal that `TimesheetController` renders.
- `Timesheet/listregisterentries`, `Timesheet/listverifiedregisterentries`, `Timesheet/processregisterentries`, `Timesheet/verifyregisterentries/:registerid`, `Timesheet/loadattendanceregisterheader`, `Timesheet/checkifregistercanverify`, `Timesheet/updateregisterentries/:registerid`, `Timesheet/submitregisterentry` — all `autoRender=FALSE` JSON/AJAX actions (`:658-986`) that appear to belong to the legacy attendance-register verification grid; no calling `.ctp` markup for these was found under `View/Timesheet/`, so their client-side callers (if any) are outside this controller's own views — INFERRED likely orphaned/legacy given the lack of a matching view.

---

## Part 2: Uncovered Controllers

Cross-checked the full `Controller/*.php` directory listing (223 files, excluding the `Component` subfolder) against the "already covered" clusters supplied. Every file matched a covered cluster (including all `Bkup`/`bkup`/`_nimisha`/`_2018-xx`/duplicate variants of already-listed controller names) **except one**:

### `Controller/LoginAppController.php`

- **Not dead code.** It is a real, actively-used base controller. Confirmed via `Grep`: `App::uses('LoginAppController', 'Controller')` and `class SiteController extends LoginAppController` in `Controller/SiteController.php:23,37`, and `class PagesController extends LoginAppController` in `Controller/PagesController.php:21,31`.
- Purpose: `LoginAppController.php:33-38` defines a minimal alternative to the normal `AppController` base class — it extends CakePHP's core `Controller` directly (not `AppController`), and only wires up `Email`, `DataTable`, `Session` components (`:34`). Critically, it does **not** inherit `AppController`'s `beforeFilter` session/`user_group` authentication gate (`AppController.php:39-46`), so any controller extending `LoginAppController` instead of `AppController` bypasses the standard login check.
- INFERRED: This is the base class for the two controllers whose actions must be reachable **without** an authenticated session — `SiteController` (public-facing site/login-adjacent actions) and `PagesController` (CakePHP's static "Pages" controller, typically used for `/pages/*` static content and often the app's actual login page rendering). This matches its purpose: a lightweight, unauthenticated-friendly base class distinct from the main `AppController`.

No other uncovered controllers were found. `EmployeeController_old_before addnominee.php` was also seen in the raw directory listing without an explicit "(+bkup)" annotation in the supplied covered list, but it is unambiguously an obsolete backup copy of `EmployeeController` (same class family, "old_before addnominee" naming) and is treated as covered under the Employee cluster's `EmployeeController` entry — flagged here only for completeness, not as a separate uncovered controller.

`Controller/AppController.php` (base class) and `Controller/UserControllerssss.php` (known dup/skip) were excluded per instructions, as was `Controller/TimesheetController.php` (analyzed in Part 1 above).
