# MyPayrollMaster → Next.js Migration Specification
**For: Code Agent with access to CakePHP source + MySQL schema**  
**Target: Next.js 14 App Router + MySQL (same DB) + NextAuth.js + TanStack Query**

---

## HOW TO USE THIS DOCUMENT

You have access to:
- CakePHP source: `app/Controller/`, `app/Model/`, `app/View/`
- Database schema: MySQL dump or live DB
- This spec: describes what to build, not how CakePHP did it

For each module section:
1. Read the listed CakePHP files to understand current logic
2. Implement the Next.js component + API route described here
3. Business logic pseudo-code = what your API route must implement
4. Use the field lists as the source of truth for DB columns and form inputs

---

## 1. ARCHITECTURE OVERVIEW

### 1.1 CakePHP (Legacy)

```
app/
  Controller/          # All business logic here
  Model/               # DB queries + validation
  View/                # PHP templates + JS inline
  webroot/js/          # jQuery, AJAX calls
  webroot/css/
Config/
  database.php         # DB credentials
  core.php             # App config
```

**Key pattern:** All routes are server-rendered. Sidebar links all have `href="#"` and use `data-url` + `navigateToUrl()` JS to AJAX-load content into `#main-content` div. The URL in browser bar NEVER changes — always stays at `/Dashboard`.

**Auth:** CakePHP sessions via `$this->Session->write('Auth.User', ...)`. Session stored server-side.

**Multi-tenancy:** Every table has `company_id`. Retrieved from session: `$this->Session->read('Auth.User.company_id')`. Every query must be scoped: `WHERE company_id = ?`.

### 1.2 Next.js (Target)

```
src/
  app/
    (auth)/
      login/page.tsx
    (dashboard)/
      layout.tsx          # Shell with sidebar + top nav
      dashboard/page.tsx
      analytics/page.tsx
      company/
        page.tsx          # Company module index
        info/page.tsx
        salary-heads/page.tsx
        statutory-heads/page.tsx
        attendance-policy/page.tsx
        holiday-calendar/page.tsx
        leave-policy/page.tsx
        salary-structure/page.tsx
        it-declaration-items/page.tsx
        biometric-devices/page.tsx
        employee-devices/page.tsx
        leave-year-end/page.tsx
        document-master/page.tsx
        employee-assets/page.tsx
        expense-types/page.tsx
        profile-management/page.tsx
      employee/
        page.tsx
        add/page.tsx
        join/page.tsx
        access/page.tsx
        menu-allocation/page.tsx
        bulk-policies/page.tsx
        import/page.tsx
        remove/page.tsx
        generate-documents/page.tsx
        document-upload/page.tsx
        allocate-assets/page.tsx
        it-declarations/page.tsx
        approvals/page.tsx
      attendance/
        page.tsx
        upload/page.tsx
        leave-upload/page.tsx
        special-events/page.tsx
        leave-balance-upload/page.tsx
        approve-cancel-leaves/page.tsx
        regularisation-approval/page.tsx
        edit-attendance/page.tsx
        verify-ot/page.tsx
        verify-monthly-ot/page.tsx
        verify-new-ot/page.tsx
        verify-monthly/page.tsx
        register/page.tsx
        daily-time/page.tsx
        shift-planner/page.tsx
        timesheet/page.tsx
        exception/page.tsx
      payroll/
        page.tsx
        salary-revision/page.tsx
        salary-management/page.tsx
        variable-upload/page.tsx
        leave-encashments/page.tsx
        salary-advance/page.tsx
        loans/page.tsx
        fixed-components/page.tsx
        processing/page.tsx
        arrear-calculation/page.tsx
      payment-approvals/page.tsx
      reports/
        page.tsx
        attendance/page.tsx
        company/page.tsx
        employee/page.tsx
        leave/page.tsx
        others/page.tsx
        payroll/page.tsx
  api/
    auth/[...nextauth]/route.ts
    company/
      info/route.ts
      salary-heads/route.ts
      salary-heads/[id]/route.ts
      statutory-heads/route.ts
      attendance-policy/route.ts
      holiday-calendar/route.ts
      leave-policy/route.ts
      salary-structure/route.ts
      it-declaration-items/route.ts
      document-master/route.ts
      expense-types/route.ts
    employee/
      route.ts
      [id]/route.ts
      [id]/join/route.ts
      access/route.ts
      menu-allocation/route.ts
      import/route.ts
      assets/route.ts
      it-declarations/route.ts
    attendance/
      upload/route.ts
      leaves/route.ts
      leaves/approve/route.ts
      edit/route.ts
      overtime/route.ts
      register/route.ts
      shift-planner/route.ts
      timesheet/route.ts
    payroll/
      salary-revision/route.ts
      salary-management/route.ts
      variable-upload/route.ts
      leave-encashments/route.ts
      advances/route.ts
      loans/route.ts
      fixed-components/route.ts
      processing/route.ts
      arrears/route.ts
    reports/
      attendance/route.ts
      employee/route.ts
      leave/route.ts
      payroll/route.ts
  lib/
    db.ts               # MySQL connection pool (mysql2/promise)
    auth.ts             # NextAuth config
    payroll-engine.ts   # Salary calculation engine
    utils.ts
  components/
    layout/
      Sidebar.tsx
      TopNav.tsx
      ModuleCard.tsx
    ui/
      DataTable.tsx     # Reusable table with sort/filter/pagination
      FormModal.tsx     # CRUD modal wrapper
      FileUpload.tsx    # CSV/Excel upload with preview
      StatusBadge.tsx
      DateRangePicker.tsx
      MonthYearPicker.tsx
      ExportButton.tsx  # CSV/Excel/PDF export
    company/
      SalaryHeadForm.tsx
      SalaryStructureBuilder.tsx
      LeavePolicyForm.tsx
    employee/
      EmployeeForm.tsx  # Multi-step add/edit
      EmployeeTable.tsx
    attendance/
      AttendanceGrid.tsx  # Month-view grid of status codes
      ShiftPlanner.tsx
    payroll/
      SalarySlip.tsx
      ProcessingTable.tsx
```

### 1.3 URL Migration Map

| CakePHP (AJAX target)      | Next.js Route                          |
|----------------------------|----------------------------------------|
| `/Dashboard`               | `/dashboard`                           |
| `/Analytics`               | `/analytics`                           |
| `/BusinessDashboard`       | `/business-dashboard`                  |
| `/CompanySetup/index`      | `/company`                             |
| `/CompanySetup/companyInfo`| `/company/info`                        |
| `/CompanySetup/salaryHeads`| `/company/salary-heads`                |
| `/CompanySetup/statutoryHeads` | `/company/statutory-heads`         |
| `/CompanySetup/attendancePolicy` | `/company/attendance-policy`     |
| `/CompanySetup/holidayCalendar` | `/company/holiday-calendar`       |
| `/CompanySetup/leavePolicy` | `/company/leave-policy`               |
| `/CompanySetup/salaryStructure` | `/company/salary-structure`       |
| `/Employee/index`          | `/employee`                            |
| `/Employee/addEmployee`    | `/employee/add`                        |
| `/Employee/employeeJoin`   | `/employee/join`                       |
| `/Employee/employeeAccess` | `/employee/access`                     |
| `/Attendance/index`        | `/attendance`                          |
| `/Attendance/uploadAttendance` | `/attendance/upload`               |
| `/Attendance/leaveApproval` | `/attendance/approve-cancel-leaves`   |
| `/Payroll/salaryProcessing` | `/payroll/processing`                 |
| `/Report/index`            | `/reports`                             |

---

## 2. DATABASE SCHEMA

**Key tables** (read from your MySQL dump to confirm exact column names):

### `users` / `user_access`
```sql
-- CakePHP model: app/Model/UserAccess.php
user_id, company_id, company_code, employee_id, username, password (hashed),
role, menu_access (JSON or comma-separated), is_active, created, modified
```

### `companies`
```sql
-- CakePHP model: app/Model/Company.php
id, company_name, company_code, address, city, state, country, pincode,
pan_number, tan_number, esic_code, pf_code, pt_code, logo (filename),
financial_year_start (month: 1-12), created, modified
```

### `salary_heads`
```sql
-- CakePHP model: app/Model/SalaryHead.php
id, company_id, head_name, head_code, head_type (earning|deduction|employer),
formula_type (formula|fixed|limit|manually),
formula_value (VARCHAR: "50% of Basic" or "1800" or "12% of Basic"),
limit_min, limit_max, is_active, display_order, taxable (0|1), created, modified
```

### `statutory_heads`
```sql
-- CakePHP model: app/Model/StatutoryHead.php
id, company_id, head_type (EPF|ESI|PT|TDS|LWF),
employee_rate, employer_rate, threshold_amount, cap_amount,
whichever_lesser (0|1), is_active
```

### `attendance_policies`
```sql
-- CakePHP model: app/Model/AttendancePolicy.php
id, company_id, policy_name, calculation_type (Calendar|WorkingDays|FixedDays),
fixed_days (INT, used when calculation_type=FixedDays),
ot_applicable (0|1), weekly_off_day (0=Sun..6=Sat), created, modified
```

### `holiday_calendar`
```sql
id, company_id, year, holiday_name, holiday_date, holiday_type (National|Optional)
```

### `leave_policies`
```sql
id, company_id, leave_type_code (CL|SL|EL|ML|etc),
leave_name, annual_quota, carry_forward (0|1),
max_carry_forward, encashable (0|1), is_active
```

### `salary_structures`
```sql
id, company_id, structure_name, applicable_for (all|specific),
head_ids (JSON array of salary_head ids with amounts/formulas)
```

### `employees`
```sql
-- CakePHP model: app/Model/Employee.php
id, company_id, employee_code, first_name, last_name, dob, gender,
mobile, email, department, designation, date_of_joining, date_of_leaving,
employment_type (Permanent|Contract|Trainee), 
reporting_manager_id, location, branch, cost_center,
pan_number, aadhar_number, bank_account_number, bank_ifsc, bank_name,
pf_number, uan_number, esic_number,
is_active, probation_end_date, confirmation_date, created, modified
```

### `attendance`
```sql
-- CakePHP model: app/Model/Attendance.php
id, company_id, employee_id, att_date, status (P|WO|HO|A|LOP|SL|CL|EL|ML|PTL|
  ALOP|COFF|RH|AL|TC|WOF|CPL|PL|WAH|WFH|SP|ESI|WOFF|PRL|TL|LOP1),
in_time, out_time, ot_hours (DECIMAL), edited (0|1), created, modified
```

### `leaves`
```sql
id, company_id, employee_id, leave_type_code, from_date, to_date,
no_of_days (DECIMAL), reason, status (Applied|AuthorizePending|Authorized|
  ApprovePending|Approved|Rejected|CancelPending|CancelAuthorize|CancelApprove|
  Cancelled|AdminRejected), 
applied_on, actioned_by, actioned_on
```

### `payroll_processed`
```sql
id, company_id, employee_id, month, year, 
days_in_month, working_days, present_days, lop_days,
[head_code]_earning (one column per salary head, dynamic),
[head_code]_deduction,
gross_salary, total_deductions, net_salary,
tds_amount, is_finalized, processed_by, processed_on
```

### `salary_revision`
```sql
id, company_id, employee_id, effective_date, revision_type (Increment|Revision),
old_ctc, new_ctc, remarks, approved_by, created
```

### `salary_advances`
```sql
id, company_id, employee_id, advance_date, amount, reason,
repayment_months, monthly_deduction, balance_amount, is_closed
```

### `employee_loans`
```sql
id, company_id, employee_id, loan_type, loan_date, principal_amount,
interest_rate, tenure_months, emi_amount, balance_amount, is_closed
```

---

## 3. AUTHENTICATION

### CakePHP (legacy)
```
app/Controller/AppController.php        # beforeFilter() — checks session
app/Controller/SiteController.php       # login(), logout() actions
app/Model/UserAccess.php               # validateUser() — MD5 or bcrypt password
```

**Session vars set on login:**
```php
$this->Session->write('Auth.User.id', $user['id']);
$this->Session->write('Auth.User.company_id', $user['company_id']);
$this->Session->write('Auth.User.company_code', $user['company_code']);
$this->Session->write('Auth.User.role', $user['role']);
$this->Session->write('Auth.User.menu_access', $user['menu_access']);
```

### Next.js (target)
**File:** `src/lib/auth.ts`

```typescript
// NextAuth config
export const authOptions: NextAuthOptions = {
  providers: [
    CredentialsProvider({
      name: 'credentials',
      credentials: { username: {}, password: {} },
      async authorize(credentials) {
        // PSEUDO-CODE:
        // 1. SELECT * FROM user_access WHERE username = credentials.username AND is_active = 1
        // 2. Compare password: check MD5(credentials.password) OR bcrypt.compare()
        //    (read app/Model/UserAccess.php to see which hash CakePHP used)
        // 3. If match: return { id, company_id, company_code, role, menu_access }
        // 4. Else: return null
      }
    })
  ],
  callbacks: {
    async jwt({ token, user }) {
      if (user) {
        token.company_id = user.company_id;
        token.company_code = user.company_code;
        token.role = user.role;
        token.menu_access = user.menu_access;
      }
      return token;
    },
    async session({ session, token }) {
      session.user.company_id = token.company_id;
      session.user.company_code = token.company_code;
      session.user.role = token.role;
      session.user.menu_access = token.menu_access;
      return session;
    }
  }
};
```

**Login page:** `src/app/(auth)/login/page.tsx`

UI fields:
- `user_id` → username text input (label: "User Name")
- `password` → password input
- `rememberme` → checkbox
- Submit button: "Login"
- Link: "Forget password?"
- Left panel: Logo ("Rizo" / company logo), tagline "MPM - HR IS NOW RIZO", description text

**Security middleware:** `src/middleware.ts`
```typescript
// Protect all routes under /(dashboard) — redirect to /login if no session
export { default } from 'next-auth/middleware';
export const config = { matcher: ['/((?!login|api/auth).*)'] };
```

---

## 4. MODULE: COMPANY SETUP

**CakePHP files to read:**
- `app/Controller/CompanySetupController.php`
- `app/Model/CompanySetup.php` (or `Company.php`)
- `app/View/CompanySetup/` (all .ctp files)

**Sub-modules (sidebar order):**
1. Company Info
2. Salary & Leave Heads
3. Statutory Heads
4. Attendance Policy
5. Holiday Calendar
6. Leave Policy
7. Salary Structure
8. Income Tax Declaration Items
9. Biometric Device Management
10. Employee Devices
11. Leave Year End Process
12. Document Master
13. Employee Assets Creation
14. Expense Type
15. Profile Management
16. Your Current Plan

---

### 4.1 Company Info

**CakePHP:** `CompanySetupController::companyInfo()`  
**Next.js page:** `src/app/(dashboard)/company/info/page.tsx`  
**Next.js API:** `src/app/api/company/info/route.ts`

**Form fields** (single company record, edit-only — no add):
```
Company Name*         VARCHAR
Company Code*         VARCHAR (read-only, generated)
Address               TEXT
City                  VARCHAR
State                 VARCHAR
Country               VARCHAR
Pincode               VARCHAR(6)
PAN Number            VARCHAR(10)
TAN Number            VARCHAR(10)
ESIC Code             VARCHAR
PF Code               VARCHAR
PT Code               VARCHAR
Logo                  FILE UPLOAD (image)
Financial Year Start  SELECT (month)
```

**API routes:**
```
GET  /api/company/info            → SELECT * FROM companies WHERE company_id = session.company_id
PUT  /api/company/info            → UPDATE companies SET ... WHERE id = session.company_id
POST /api/company/info/logo       → Upload logo file, store filename
```

**Component:** `<CompanyInfoForm>` — single-record edit form, no table.

---

### 4.2 Salary & Leave Heads

**CakePHP:** `CompanySetupController::salaryHeads()`  
**Next.js page:** `src/app/(dashboard)/company/salary-heads/page.tsx`  
**Next.js API:** `src/app/api/company/salary-heads/route.ts`

**Table columns displayed:**
```
Head Name | Head Code | Type (Earning/Deduction/Employer) | Formula Type | Formula Value | Active
```

**Form fields (Add/Edit modal):**
```
Head Name*            VARCHAR
Head Code*            VARCHAR (auto-generated from name, editable)
Head Type*            SELECT: Earning | Deduction | Employer Contribution
Formula Type*         SELECT: Formula | Fixed | Limit | Manually
Formula Value         INPUT (shown when formula_type = 'formula' or 'fixed')
  - For 'formula': text like "50% of Basic" (references another head_code)
  - For 'fixed': numeric amount
Limit Min             DECIMAL (shown when formula_type = 'limit')
Limit Max             DECIMAL (shown when formula_type = 'limit')
Whichever Toggle      RADIO: Lesser | Greater (shown when formula_type = 'limit')
Taxable               CHECKBOX
Display Order         NUMBER
Is Active             TOGGLE
```

**Business logic (formula_type rules):**
```
PSEUDO-CODE for salary calculation using this head:

if formula_type == 'formula':
  # Parse formula_value: e.g. "50% of Basic"
  percentage = extract_percentage(formula_value)   # → 50
  base_head = extract_head_code(formula_value)     # → 'Basic'
  computed = (salary_heads['Basic'] * percentage) / 100

elif formula_type == 'fixed':
  computed = parseFloat(formula_value)

elif formula_type == 'limit':
  base_computed = compute_from_formula(...)
  if whichever == 'lesser':
    computed = min(base_computed, limit_max)
  else:  # 'greater'
    computed = max(base_computed, limit_min)
  computed = max(computed, limit_min)  # enforce minimum

elif formula_type == 'manually':
  computed = user_entered_value  # entered during payroll processing
```

**API routes:**
```
GET    /api/company/salary-heads              → list all (company_id scoped)
POST   /api/company/salary-heads              → create
PUT    /api/company/salary-heads/[id]         → update
DELETE /api/company/salary-heads/[id]         → soft delete (is_active=0)
```

---

### 4.3 Statutory Heads

**CakePHP:** `CompanySetupController::statutoryHeads()`  
**Next.js page:** `src/app/(dashboard)/company/statutory-heads/page.tsx`

**Form fields (one record per statutory type):**
```
Type*                 SELECT: EPF | ESI | Professional Tax | TDS | LWF
Employee Rate %       DECIMAL
Employer Rate %       DECIMAL
Threshold Amount      DECIMAL (salary ceiling for applicability)
Cap Amount            DECIMAL (max deduction amount)
Whichever is         RADIO: Lesser | Greater
Is Active            TOGGLE
```

**EPF business logic:**
```
PSEUDO-CODE:

EPF_employee_rate = statutory_heads['EPF'].employee_rate   # typically 12%
EPF_employer_rate = statutory_heads['EPF'].employer_rate   # typically 12%

# Cap rule (read exact logic from CompanySetupController or PayrollController)
basic_salary = computed_earnings['Basic']
epf_base = min(basic_salary, 15000)   # EPF computed on max ₹15,000

employee_epf = epf_base * EPF_employee_rate / 100
employer_epf = epf_base * EPF_employer_rate / 100

# Cap at ₹1800 if basic <= ₹15000
if statutory_heads['EPF'].cap_amount:
  if statutory_heads['EPF'].whichever_lesser:
    employee_epf = min(employee_epf, statutory_heads['EPF'].cap_amount)
```

**ESI business logic:**
```
PSEUDO-CODE:

gross_salary = sum(all earnings for the month)
ESI_threshold = 21000  # ₹21,000/month gross eligibility ceiling

if gross_salary <= ESI_threshold:
  employee_esi = gross_salary * statutory_heads['ESI'].employee_rate / 100
  employer_esi = gross_salary * statutory_heads['ESI'].employer_rate / 100
else:
  employee_esi = 0
  employer_esi = 0
```

---

### 4.4 Attendance Policy

**CakePHP:** `CompanySetupController::attendancePolicy()`  
**Next.js page:** `src/app/(dashboard)/company/attendance-policy/page.tsx`

**Form fields:**
```
Policy Name*             VARCHAR
Calculation Type*        SELECT: Calendar | Working Days | Fixed Days
Fixed Days               NUMBER (required when Calculation Type = Fixed Days)
OT Applicable            CHECKBOX
Weekly Off Day           SELECT: Sunday | Monday | ... | Saturday
Late Mark After (mins)   NUMBER
Half Day After (mins)    NUMBER
Is Active                TOGGLE
```

**Salary proration business logic (affects payroll calculation):**
```
PSEUDO-CODE:

# Read from app/Controller/PayrollController.php — processPayroll() or similar

days_in_month = days_in(month, year)  # calendar days

if policy.calculation_type == 'Calendar':
  divisor = days_in_month
  
elif policy.calculation_type == 'WorkingDays':
  divisor = count(working_days_in_month)  # exclude Sundays/holidays
  
elif policy.calculation_type == 'FixedDays':
  divisor = policy.fixed_days  # e.g., 26

# LOP deduction:
lop_days = count(attendance where status = 'LOP' for employee for month)
lop_deduction_per_day = (gross_salary / divisor)
lop_deduction = lop_days * lop_deduction_per_day

# Present days for attendance percentage:
present_days = divisor - lop_days
```

---

### 4.5 Holiday Calendar

**CakePHP:** `CompanySetupController::holidayCalendar()`  
**Next.js page:** `src/app/(dashboard)/company/holiday-calendar/page.tsx`

**Table columns:** Holiday Name | Date | Type (National/Optional) | Day | Actions  
**Filters:** Year (SELECT)

**Form fields:**
```
Year*           NUMBER (4-digit)
Holiday Name*   VARCHAR
Holiday Date*   DATE PICKER
Holiday Type*   SELECT: National Holiday | Optional Holiday
```

**API routes:**
```
GET    /api/company/holiday-calendar?year=YYYY
POST   /api/company/holiday-calendar
PUT    /api/company/holiday-calendar/[id]
DELETE /api/company/holiday-calendar/[id]
```

---

### 4.6 Leave Policy

**CakePHP:** `CompanySetupController::leavePolicy()`  
**Next.js page:** `src/app/(dashboard)/company/leave-policy/page.tsx`

**Table columns:** Leave Type | Leave Name | Annual Quota | Carry Forward | Max CF | Encashable | Active

**Form fields:**
```
Leave Type Code*   SELECT: CL | SL | EL | ML | PTL | PL | CPL | COFF | AL | RH | WFH | WAH
Leave Name*        VARCHAR
Annual Quota*      DECIMAL (days per year)
Carry Forward      CHECKBOX
Max Carry Forward  NUMBER (enabled if Carry Forward = true)
Encashable         CHECKBOX
Gender Specific    SELECT: All | Male | Female
Is Active          TOGGLE
```

---

### 4.7 Salary Structure

**CakePHP:** `CompanySetupController::salaryStructure()`  
**Next.js page:** `src/app/(dashboard)/company/salary-structure/page.tsx`  
**Next.js component:** `src/components/company/SalaryStructureBuilder.tsx`

**Table columns:** Structure Name | Applicable For | Heads Count | Active

**Form (Add/Edit):**
```
Structure Name*     VARCHAR
Applicable For*     SELECT: All Employees | Specific Department | Specific Designation

# Dynamic head mapping table:
# Rows = one per active salary head
# Columns: Head Name | Head Code | Formula Type | Value/Amount (editable)
```

**Business logic:** When assigning to employee, this structure defines all their earning/deduction formulas. Override possible at individual employee level.

---

### 4.8 Income Tax Declaration Items

**CakePHP:** `CompanySetupController::itDeclarationItems()`  
**Next.js page:** `src/app/(dashboard)/company/it-declaration-items/page.tsx`

**Table columns:** Section | Declaration Item Name | Max Limit | Active

**Form fields:**
```
Section*            SELECT: 80C | 80D | 80E | 80G | HRA | LTA | Other
Item Name*          VARCHAR
Max Limit           DECIMAL (annual ₹ limit)
Is Active           TOGGLE
```

---

### 4.9 Biometric Device Management

**CakePHP:** `CompanySetupController::biometricDevices()`  
**Next.js page:** `src/app/(dashboard)/company/biometric-devices/page.tsx`

**Table columns:** Device Name | Device ID | IP Address | Port | Location | Status

**Form fields:**
```
Device Name*    VARCHAR
Device ID*      VARCHAR (hardware serial)
IP Address*     VARCHAR
Port            NUMBER (default: 4370)
Location        VARCHAR
Is Active       TOGGLE
```

---

### 4.10 Document Master

**CakePHP:** `CompanySetupController::documentMaster()`  
**Next.js page:** `src/app/(dashboard)/company/document-master/page.tsx`

**Form fields:**
```
Document Name*     VARCHAR (e.g., "Aadhar Card", "PAN Card", "Offer Letter")
Document Type*     SELECT: Identity | Address | Educational | Employment | Other
Is Mandatory       CHECKBOX
Is Active          TOGGLE
```

---

### 4.11 Employee Assets Creation

**CakePHP:** `CompanySetupController::employeeAssets()`  
**Next.js page:** `src/app/(dashboard)/company/employee-assets/page.tsx`

**Form fields:**
```
Asset Type*      VARCHAR (e.g., "Laptop", "Mobile", "ID Card")
Asset Category   SELECT
Description      TEXT
Is Active        TOGGLE
```

---

### 4.12 Expense Type

**CakePHP:** `CompanySetupController::expenseType()`  
**Next.js page:** `src/app/(dashboard)/company/expense-types/page.tsx`

**Form fields:**
```
Expense Type Name*   VARCHAR
Max Limit/Month      DECIMAL
Requires Receipt     CHECKBOX
Is Active            TOGGLE
```

---

## 5. MODULE: EMPLOYEE

**CakePHP files to read:**
- `app/Controller/EmployeeController.php`
- `app/Model/Employee.php`
- `app/View/Employee/` (all .ctp files)

**Sub-modules:** Add Employee | Employee Join | Employee Access | Menu Allocation | Allocate Policies in Bulk | Import Employee | Remove Employee | Generate Employee Documents | Document Upload | Allocate Assets to Employees | Employee Income Tax Declarations | Approvals (Probation/Promotion)

---

### 5.1 Add Employee

**CakePHP:** `EmployeeController::addEmployee()`  
**Next.js page:** `src/app/(dashboard)/employee/add/page.tsx`  
**Next.js component:** `src/components/employee/EmployeeForm.tsx` (multi-step)

**Form is multi-tab/step:**

**Tab 1: Personal Details**
```
First Name*           VARCHAR
Last Name             VARCHAR
Date of Birth*        DATE
Gender*               SELECT: Male | Female | Other
Mobile*               VARCHAR(10)
Email                 VARCHAR
Blood Group           SELECT: A+|A-|B+|B-|O+|O-|AB+|AB-
Marital Status        SELECT: Single | Married | Divorced | Widowed
Nationality           VARCHAR
Religion              VARCHAR
Physically Challenged CHECKBOX
Photo                 FILE (image)
```

**Tab 2: Employment Details**
```
Employee Code*        VARCHAR (auto-generated, editable)
Department*           SELECT (from departments table/config)
Designation*          SELECT (from designations table/config)
Date of Joining*      DATE
Employment Type*      SELECT: Permanent | Contract | Trainee | Intern
Reporting Manager     SELECT (from active employees)
Branch/Location*      SELECT
Cost Center           SELECT
Probation Period      NUMBER (months)
```

**Tab 3: Salary & Statutory**
```
Salary Structure*     SELECT (from salary_structures)
CTC / Basic Salary*   DECIMAL
PAN Number            VARCHAR(10)
UAN Number            VARCHAR(12)
PF Number             VARCHAR
ESIC Number           VARCHAR
PT Applicable         CHECKBOX
```

**Tab 4: Bank Details**
```
Account Number*       VARCHAR
Bank Name*            SELECT/VARCHAR
IFSC Code*            VARCHAR(11)
Account Type          SELECT: Savings | Current
```

**Tab 5: Documents**
```
Dynamic document uploads based on document_master:
  - For each active document type: LABEL + FILE UPLOAD field
```

**API routes:**
```
GET    /api/employee?company_id=&search=&dept=&status=
POST   /api/employee                     → INSERT employee record
GET    /api/employee/[id]                → full employee details
PUT    /api/employee/[id]                → update
DELETE /api/employee/[id]               → soft delete (is_active = 0)
GET    /api/employee/[id]/salary         → salary details
```

**Employee list table columns:**
```
Emp Code | Name | Department | Designation | Date of Joining | Status | Actions
```

**Filters:** Department, Designation, Employment Type, Status (Active/Inactive)

---

### 5.2 Employee Join

**CakePHP:** `EmployeeController::employeeJoin()`  
**Next.js page:** `src/app/(dashboard)/employee/join/page.tsx`

**Purpose:** Formal joining formalities after initial add — collect additional docs, signed agreements, issue ID card, etc.

**Table columns:** Emp Code | Name | DOJ | Joining Status | Documents Collected | Actions

**Join form fields:**
```
Employee*             SELECT (employees pending join completion)
Joining Date*         DATE
Documents Verified    CHECKBOX list (per document_master)
ID Card Issued        CHECKBOX
System Access Given   CHECKBOX
Remarks               TEXT
```

---

### 5.3 Employee Access (User Login Creation)

**CakePHP:** `EmployeeController::employeeAccess()`  
**Next.js page:** `src/app/(dashboard)/employee/access/page.tsx`  
**Next.js API:** `src/app/api/employee/access/route.ts`

**Table columns:** Emp Code | Name | Username | Role | Access Status | Actions

**Create/Edit Access form:**
```
Employee*       SELECT (from employees)
Username*       VARCHAR (usually email or emp_code)
Password*       INPUT (hashed before save)
Role*           SELECT: Admin | HR | Manager | Employee | Payroll
Is Active       TOGGLE
```

**API routes:**
```
GET    /api/employee/access
POST   /api/employee/access              → create user_access record
PUT    /api/employee/access/[id]         → update role/status
DELETE /api/employee/access/[id]         → deactivate
POST   /api/employee/access/[id]/reset-password
```

---

### 5.4 Menu Allocation

**CakePHP:** `EmployeeController::menuAllocation()`  
**Next.js page:** `src/app/(dashboard)/employee/menu-allocation/page.tsx`

**Purpose:** Control which sidebar menu items each user/role can see.

**UI:** Two-panel: left = employee/role selector, right = checkbox tree of all menu items

**Data structure:**
```json
{
  "user_id": 123,
  "menu_access": ["company", "employee", "attendance", "payroll", "reports"]
}
```

**API routes:**
```
GET  /api/employee/menu-allocation/[userId]
PUT  /api/employee/menu-allocation/[userId]   → { menu_access: string[] }
```

---

### 5.5 Import Employee

**CakePHP:** `EmployeeController::importEmployee()`  
**Next.js page:** `src/app/(dashboard)/employee/import/page.tsx`  
**Next.js component:** `src/components/ui/FileUpload.tsx`

**UI flow:**
1. Download template button → GET `/api/employee/import/template` → returns XLSX
2. Upload filled XLSX/CSV → POST `/api/employee/import`
3. Preview table shows parsed rows with validation errors highlighted
4. "Confirm Import" button → POST `/api/employee/import/confirm`

**Validation rules (read from `EmployeeController::importEmployee()`):**
```
PSEUDO-CODE:
for each row in uploaded_file:
  errors = []
  if not row.first_name: errors.push('First name required')
  if not row.date_of_joining: errors.push('DOJ required')
  if row.email and not valid_email(row.email): errors.push('Invalid email')
  if row.pan and not /[A-Z]{5}[0-9]{4}[A-Z]/.test(row.pan): errors.push('Invalid PAN')
  if Employee.find(company_id=company_id, employee_code=row.employee_code):
    errors.push('Duplicate employee code')
  row.validation_errors = errors
```

---

### 5.6 Employee IT Declarations

**CakePHP:** `EmployeeController::itDeclarations()`  
**Next.js page:** `src/app/(dashboard)/employee/it-declarations/page.tsx`  
**Next.js API:** `src/app/api/employee/it-declarations/route.ts`

**Table columns:** Emp Name | Financial Year | Section | Item | Declared Amount | Proof Submitted | Approved Amount | Status

**Form:**
```
Employee*           SELECT
Financial Year*     SELECT (e.g., "2025-26")
# Dynamic rows per IT declaration item from it_declaration_items:
Section | Item Name | Declared Amount | Proof Upload
```

---

## 6. MODULE: ATTENDANCE

**CakePHP files to read:**
- `app/Controller/AttendanceController.php`
- `app/Model/Attendance.php`
- `app/Model/Leave.php`
- `app/View/Attendance/`

**Sub-modules:** Attendance Upload | Leave Upload | Special Events Attendance | Leave Balance Upload | Approve or Cancel Leaves | Attendance Regularisation Approval | Edit Attendance | Verify Daily OT/Attendance | Verify Monthly Overtime | New Verify Overtime | Verify Monthly Attendance | Attendance Register | Daily Time Verification | Shift Planner | Attendance Timesheet | Exception

---

### 6.1 Attendance Upload

**CakePHP:** `AttendanceController::uploadAttendance()`  
**Next.js page:** `src/app/(dashboard)/attendance/upload/page.tsx`  
**Next.js component:** `src/components/ui/FileUpload.tsx`  
**Next.js API:** `src/app/api/attendance/upload/route.ts`

**Upload flow:**
1. Select Month + Year (MonthYearPicker component)
2. Download template → GET `/api/attendance/upload/template?month=&year=`
3. Upload XLSX/CSV with columns: `employee_code, date, status, in_time, out_time`
4. Preview + validation
5. Confirm upload → upsert attendance records

**Status codes (26 valid values):**
```
P     = Present
WO    = Weekly Off
HO    = Holiday
A     = Absent
LOP   = Loss of Pay
SL    = Sick Leave
CL    = Casual Leave
EL    = Earned Leave
ML    = Maternity Leave
PTL   = Paternity Leave
ALOP  = Absent LOP
COFF  = Compensatory Off
RH    = Restricted Holiday
AL    = Annual Leave
TC    = Tour/Client
WOF   = Weekly Off (Female)
CPL   = Compensatory Planned Leave
PL    = Privilege Leave
WAH   = Work from Anywhere Home
WFH   = Work from Home
SP    = Special
ESI   = ESI Leave
WOFF  = Weekly Off (Fixed)
PRL   = Personal Leave
TL    = Training Leave
LOP1  = LOP Type 1
```

**Validation:**
```
PSEUDO-CODE:
for each row:
  if row.status not in VALID_STATUS_CODES: error('Invalid status')
  if row.date > today: error('Future date not allowed')
  if not Employee.find(employee_code=row.employee_code, company_id=session.company_id):
    error('Employee not found')
```

---

### 6.2 Leave Upload

**CakePHP:** `AttendanceController::leaveUpload()`  
**Next.js page:** `src/app/(dashboard)/attendance/leave-upload/page.tsx`

**Upload columns:** employee_code, leave_type, from_date, to_date, reason  
**Same flow as attendance upload (template → upload → preview → confirm)**

---

### 6.3 Approve or Cancel Leaves

**CakePHP:** `AttendanceController::leaveApproval()`  
**Next.js page:** `src/app/(dashboard)/attendance/approve-cancel-leaves/page.tsx`  
**Next.js API:** `src/app/api/attendance/leaves/approve/route.ts`

**Leave status state machine:**
```
Applied
  → [Manager action] AuthorizePending
    → [Senior action] Authorized
      → [HR/Admin action] ApprovePending
        → [Admin action] Approved
        → [Admin action] AdminRejected
      → Rejected
    → CancelPending
      → [Manager] CancelAuthorize
        → [HR] CancelApprove
          → Cancelled
  → [Self action] Cancelled (before authorization)
```

**Table columns:** Emp Name | Leave Type | From | To | Days | Applied On | Status | Actions (Authorize/Approve/Reject/Cancel)

**Filters:** Month, Department, Status, Leave Type

**API routes:**
```
GET  /api/attendance/leaves?month=&year=&status=&department=
PUT  /api/attendance/leaves/[id]/status  → { action: 'authorize'|'approve'|'reject'|'cancel', remarks: string }
```

---

### 6.4 Edit Attendance

**CakePHP:** `AttendanceController::editAttendance()`  
**Next.js page:** `src/app/(dashboard)/attendance/edit-attendance/page.tsx`

**UI:** Grid view — rows = employees, columns = days 1-31. Each cell = status dropdown.

**Filters:** Month, Year, Department, Employee

**API routes:**
```
GET  /api/attendance/edit?emp_id=&month=&year=
PUT  /api/attendance/edit        → { emp_id, date, status, in_time, out_time }
```

**Component:** `src/components/attendance/AttendanceGrid.tsx`
```typescript
// Props
interface AttendanceGridProps {
  employees: Employee[];
  month: number;
  year: number;
  attendance: Record<string, Record<string, AttendanceRecord>>;
  // attendance[emp_id][date] = AttendanceRecord
  onStatusChange: (empId: string, date: string, status: string) => void;
}
```

---

### 6.5 Attendance Register

**CakePHP:** `AttendanceController::attendanceRegister()`  
**Next.js page:** `src/app/(dashboard)/attendance/register/page.tsx`

**Table:** Month summary per employee
```
Emp Code | Name | P | WO | HO | A | LOP | SL | CL | EL | ... (all 26 codes as columns) | Total Days
```

**Filters:** Month, Year, Department, Branch

**Export:** Excel/PDF button

---

### 6.6 Shift Planner

**CakePHP:** `AttendanceController::shiftPlanner()`  
**Next.js page:** `src/app/(dashboard)/attendance/shift-planner/page.tsx`

**UI:** Calendar grid — assign shifts to employees for specific dates

**Data:**
```
shift_plans table: id, company_id, employee_id, date, shift_id, created
shifts table: id, company_id, shift_name, start_time, end_time, break_duration
```

---

### 6.7 Verify Monthly Attendance

**CakePHP:** `AttendanceController::verifyMonthlyAttendance()`  
**Next.js page:** `src/app/(dashboard)/attendance/verify-monthly/page.tsx`

**Purpose:** HR reviews and locks attendance before payroll processing.

**Table columns:** Emp Code | Name | Present | WO | HO | LOP | Leave Days | Total | OT Hours | Verified | Actions

**API route:**
```
POST /api/attendance/verify   → { month, year, employee_ids: [], verified: true }
```

---

## 7. MODULE: PAYROLL

**CakePHP files to read:**
- `app/Controller/PayrollController.php`  ← **MOST IMPORTANT FILE**
- `app/Model/Payroll.php`
- `app/Model/SalaryHead.php`
- `app/View/Payroll/`

---

### 7.1 Salary Revision

**CakePHP:** `PayrollController::salaryRevision()`  
**Next.js page:** `src/app/(dashboard)/payroll/salary-revision/page.tsx`

**Table columns:** Emp Code | Name | Current CTC | New CTC | Effective Date | Revision Type | Status | Actions

**Form fields:**
```
Employee*         SELECT (autocomplete)
Effective Date*   DATE
Revision Type*    SELECT: Increment | Revision | Promotion
Old CTC           DECIMAL (auto-filled, read-only)
New CTC*          DECIMAL
New Basic*        DECIMAL
New HRA           DECIMAL
[Other head fields dynamically based on salary structure]
Remarks           TEXT
```

---

### 7.2 Salary Management

**CakePHP:** `PayrollController::salaryManagement()`  
**Next.js page:** `src/app/(dashboard)/payroll/salary-management/page.tsx`

**Table columns:** Emp Code | Name | Department | CTC | Basic | HRA | [all earning heads] | [all deduction heads] | Net Salary | Structure | Actions

**Edit modal:** Shows all salary head values for employee, editable

---

### 7.3 Variable Upload

**CakePHP:** `PayrollController::variableUpload()`  
**Next.js page:** `src/app/(dashboard)/payroll/variable-upload/page.tsx`

**Purpose:** Upload month-specific variable components (incentives, bonuses, special allowances)

**Upload columns:** employee_code, head_code, amount, month, year

---

### 7.4 Salary Processing ← CORE FEATURE

**CakePHP:** `PayrollController::salaryProcessing()`  
**Next.js page:** `src/app/(dashboard)/payroll/processing/page.tsx`  
**Next.js API:** `src/app/api/payroll/processing/route.ts`  
**Business logic:** `src/lib/payroll-engine.ts`

**UI Flow:**
1. Select Month + Year
2. Click "Process" → system calculates for all active employees
3. Review table of computed salaries
4. Individual edit allowed (for manual overrides of 'manually' type heads)
5. "Finalize" → locks the payroll for the month
6. Generate payslips → PDF per employee

**Table columns (processing view):**
```
Emp Code | Name | Days | LOP | Present | [all earning heads] | Gross | [all deduction heads] | Total Deductions | Net Salary | TDS | Net Payable | Actions
```

**Salary calculation engine (`src/lib/payroll-engine.ts`):**
```typescript
// PSEUDO-CODE — read app/Controller/PayrollController.php for exact implementation

interface PayrollInput {
  employee: Employee;
  attendance: AttendanceRecord[];
  month: number;
  year: number;
  salaryHeads: SalaryHead[];
  attendancePolicy: AttendancePolicy;
  statutoryHeads: StatutoryHead[];
  salaryAdvances: SalaryAdvance[];
  loans: Loan[];
  variableComponents: VariableComponent[];
}

function calculateSalary(input: PayrollInput): PayrollResult {
  const { employee, attendance, month, year, salaryHeads, attendancePolicy } = input;
  
  // STEP 1: Count attendance
  const daysInMonth = getDaysInMonth(month, year);
  const lopDays = attendance.filter(a => a.status === 'LOP' || a.status === 'A').length;
  const presentDays = daysInMonth - lopDays;  // or working days per policy
  
  // STEP 2: Determine divisor based on policy
  let divisor: number;
  if (attendancePolicy.calculation_type === 'Calendar') {
    divisor = daysInMonth;
  } else if (attendancePolicy.calculation_type === 'WorkingDays') {
    divisor = countWorkingDays(month, year, attendancePolicy.weekly_off_day);
  } else if (attendancePolicy.calculation_type === 'FixedDays') {
    divisor = attendancePolicy.fixed_days;
  }
  
  // STEP 3: Proration factor
  const prorationFactor = presentDays / divisor;
  
  // STEP 4: Compute earnings in dependency order
  const computed: Record<string, number> = {};
  
  // Sort heads by dependency (Basic first, then % of Basic, etc.)
  const sortedHeads = topologicalSort(salaryHeads);
  
  for (const head of sortedHeads) {
    if (head.head_type !== 'earning') continue;
    
    let amount = 0;
    
    if (head.formula_type === 'fixed') {
      amount = parseFloat(head.formula_value);
      
    } else if (head.formula_type === 'formula') {
      // e.g., "40% of Basic"
      const { percentage, baseHead } = parseFormula(head.formula_value);
      amount = (computed[baseHead] || 0) * percentage / 100;
      
    } else if (head.formula_type === 'limit') {
      const base = computeFormulaBase(head, computed);
      if (head.whichever_lesser) {
        amount = Math.min(base, head.limit_max);
      } else {
        amount = Math.max(base, head.limit_min);
      }
      amount = Math.max(amount, head.limit_min);
      
    } else if (head.formula_type === 'manually') {
      // Get from variable_components or manual override
      amount = input.variableComponents.find(v => v.head_code === head.head_code)?.amount || 0;
    }
    
    // Apply proration (only for time-based earnings, not fixed allowances)
    // CHECK PayrollController for which heads are prorated
    amount = amount * prorationFactor;
    
    computed[head.head_code] = Math.round(amount);
  }
  
  // STEP 5: Compute gross
  const grossEarnings = Object.values(computed).reduce((a, b) => a + b, 0);
  
  // STEP 6: ESI check
  const esiApplicable = grossEarnings <= 21000;
  
  // STEP 7: Statutory deductions
  let epfEmployee = 0, epfEmployer = 0, esiEmployee = 0, esiEmployer = 0;
  const epfStat = input.statutoryHeads.find(s => s.head_type === 'EPF');
  const esiStat = input.statutoryHeads.find(s => s.head_type === 'ESI');
  
  if (epfStat?.is_active) {
    const epfBase = Math.min(computed['Basic'] || 0, 15000);
    epfEmployee = Math.round(epfBase * epfStat.employee_rate / 100);
    epfEmployer = Math.round(epfBase * epfStat.employer_rate / 100);
    if (epfStat.cap_amount) {
      epfEmployee = epfStat.whichever_lesser 
        ? Math.min(epfEmployee, epfStat.cap_amount)
        : Math.max(epfEmployee, 0);
    }
  }
  
  if (esiStat?.is_active && esiApplicable) {
    esiEmployee = Math.round(grossEarnings * esiStat.employee_rate / 100);
    esiEmployer = Math.round(grossEarnings * esiStat.employer_rate / 100);
  }
  
  // STEP 8: Other deductions (head_type = 'deduction')
  const deductions: Record<string, number> = {
    EPF: epfEmployee,
    ESI: esiEmployee,
  };
  
  for (const head of salaryHeads.filter(h => h.head_type === 'deduction')) {
    // compute same as earnings above
    deductions[head.head_code] = computeDeduction(head, computed, input);
  }
  
  // STEP 9: Loan/advance deductions
  const loanDeduction = input.loans.reduce((sum, l) => sum + l.emi_amount, 0);
  const advanceDeduction = input.salaryAdvances.reduce((sum, a) => sum + a.monthly_deduction, 0);
  
  // STEP 10: TDS (read exact formula from PayrollController — typically annual TDS / 12)
  const tdsMonthly = computeTDS(employee, computed, input);
  
  const totalDeductions = Object.values(deductions).reduce((a, b) => a + b, 0) 
    + loanDeduction + advanceDeduction + tdsMonthly;
  
  const netSalary = grossEarnings - totalDeductions;
  
  return {
    employee_id: employee.id,
    month, year,
    days_in_month: daysInMonth,
    present_days: presentDays,
    lop_days: lopDays,
    earnings: computed,
    gross_earnings: grossEarnings,
    deductions,
    epf_employer: epfEmployer,
    esi_employer: esiEmployer,
    loan_deduction: loanDeduction,
    advance_deduction: advanceDeduction,
    tds: tdsMonthly,
    total_deductions: totalDeductions,
    net_salary: netSalary,
  };
}
```

**API routes:**
```
GET  /api/payroll/processing?month=&year=    → list processed/unprocessed employees
POST /api/payroll/processing                 → { month, year } → run calculateSalary for all
PUT  /api/payroll/processing/[id]            → override individual values
POST /api/payroll/processing/finalize        → { month, year } → lock payroll
GET  /api/payroll/processing/payslip/[empId]?month=&year=  → generate PDF payslip
```

---

### 7.5 Salary Advance

**CakePHP:** `PayrollController::salaryAdvance()`  
**Next.js page:** `src/app/(dashboard)/payroll/salary-advance/page.tsx`

**Table columns:** Emp Code | Name | Advance Date | Amount | Reason | Repayment Months | Monthly Deduction | Balance | Status

**Form:**
```
Employee*              SELECT
Advance Date*          DATE
Amount*                DECIMAL
Reason                 TEXT
Repayment Months*      NUMBER
Monthly Deduction      AUTO-COMPUTED: amount / repayment_months
Approved By            VARCHAR
```

---

### 7.6 Employee Loans

**CakePHP:** `PayrollController::employeeLoans()`  
**Next.js page:** `src/app/(dashboard)/payroll/loans/page.tsx`

**Form:**
```
Employee*              SELECT
Loan Type*             SELECT: Personal | Housing | Vehicle | Emergency | Other
Loan Date*             DATE
Principal Amount*      DECIMAL
Interest Rate %        DECIMAL
Tenure (Months)*       NUMBER
EMI Amount             AUTO-COMPUTED
First EMI Date*        DATE
```

---

### 7.7 Leave Encashment

**CakePHP:** `PayrollController::leaveEncashments()`  
**Next.js page:** `src/app/(dashboard)/payroll/leave-encashments/page.tsx`

**Table columns:** Emp Code | Name | Leave Type | Encashable Balance | Encash Days | Amount | Month | Actions

**Calculation:**
```
PSEUDO-CODE:
per_day_salary = employee.basic_salary / attendance_policy.divisor
encash_amount = encash_days * per_day_salary
```

---

### 7.8 Fixed Salary Components

**CakePHP:** `PayrollController::fixedSalaryComponents()`  
**Next.js page:** `src/app/(dashboard)/payroll/fixed-components/page.tsx`

**Purpose:** Override specific salary head amounts for individual employees (overrides the formula-computed value)

**Table:** Emp Code | Name | Head Name | Fixed Amount | Effective From | Effective To

---

### 7.9 Arrear Salary Calculation

**CakePHP:** `PayrollController::arrearCalculation()`  
**Next.js page:** `src/app/(dashboard)/payroll/arrear-calculation/page.tsx`

**Purpose:** Calculate arrears when salary revision is backdated

**Inputs:**
```
Employee*           SELECT (or All)
Arrear From Month*  MonthYear
Arrear To Month*    MonthYear
```

**Logic:**
```
PSEUDO-CODE:
for each month in [arrear_from .. arrear_to]:
  old_salary = get_processed_salary(employee, month)
  new_salary = calculate_with_new_structure(employee, month)
  arrear = new_salary.net - old_salary.net
  total_arrear += arrear
```

---

## 8. MODULE: REPORTS

**CakePHP:** `app/Controller/ReportController.php`  
**Next.js page:** `src/app/(dashboard)/reports/`

All reports follow the same pattern:
1. Filter panel (date range / month-year / department / employee)
2. DataTable with results
3. Export buttons: Excel | PDF

### 8.1 Attendance Reports

**Sub-reports:**
- **Attendance Report** → monthly summary per employee with all status codes as columns
- **Attendance In/Out Report** → daily in-time/out-time per employee
- **Edited Attendance** → list of attendance records that were manually edited (has `edited=1`)
- **Edited Attendance Report** → same with change history

**API routes:**
```
GET /api/reports/attendance/summary?month=&year=&dept=&emp_id=
GET /api/reports/attendance/inout?from=&to=&emp_id=
GET /api/reports/attendance/edited?month=&year=
```

### 8.2 Employee Reports

Sub-reports: Employee Master | Headcount | Joining/Exit Report | Birthday/Anniversary | Employee Document Status

### 8.3 Leave Reports

Sub-reports: Leave Balance | Leave History | Leave Encashment | Leave Lapse

### 8.4 Payroll Reports

Sub-reports:
- **Salary Register** → all employees, all heads, for a month (the main payroll sheet)
- **Bank Advice** → bank account numbers + net pay (for bulk salary transfer)
- **Payslip** → individual salary slip (PDF)
- **EPF Report** → ECR format for PF portal upload
- **ESI Report** → for ESIC portal
- **PT Report** → Professional Tax
- **TDS Report** → Form 16 / monthly TDS

### 8.5 Company Reports

Sub-reports: Company Master | Department-wise Headcount

### 8.6 Others

Sub-reports: Expense Report | Asset Report | Loan Report | Advance Report

---

## 9. MODULE: PAYMENT APPROVALS

**CakePHP:** `app/Controller/PaymentApprovalsController.php`  
**Next.js page:** `src/app/(dashboard)/payment-approvals/page.tsx`

**Purpose:** Multi-level approval for finalized payroll before actual payment/disbursement

**States:** Pending → L1 Approved → L2 Approved → Final Approved → Paid

**Table columns:** Month | Year | Total Employees | Total Net Payable | Status | Submitted By | Actions

---

## 10. SHARED COMPONENTS

### 10.1 DataTable Component

**File:** `src/components/ui/DataTable.tsx`

```typescript
interface DataTableProps<T> {
  columns: ColumnDef<T>[];
  data: T[];
  loading?: boolean;
  pagination?: boolean;
  pageSize?: number;
  searchable?: boolean;
  exportable?: boolean;       // shows Excel + PDF buttons
  onAdd?: () => void;        // shows "Add" button if provided
  onEdit?: (row: T) => void;
  onDelete?: (row: T) => void;
}
```

Use TanStack Table v8.

### 10.2 FormModal Component

**File:** `src/components/ui/FormModal.tsx`

```typescript
interface FormModalProps {
  title: string;
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (data: Record<string, unknown>) => Promise<void>;
  children: React.ReactNode;  // form fields
  submitLabel?: string;
}
```

### 10.3 MonthYearPicker

**File:** `src/components/ui/MonthYearPicker.tsx`

Used across Attendance Upload, Payroll Processing, Reports. Renders month SELECT + year SELECT.

### 10.4 FileUpload with Preview

**File:** `src/components/ui/FileUpload.tsx`

```typescript
interface FileUploadProps {
  accept: string;             // ".xlsx,.csv"
  templateUrl: string;        // URL to download template
  onParsed: (rows: Record<string, unknown>[]) => void;
  validationFn?: (rows: Record<string, unknown>[]) => ValidationError[];
}
```

Parse XLSX in browser using `xlsx` npm package before uploading.

### 10.5 ExportButton

**File:** `src/components/ui/ExportButton.tsx`

```typescript
// Excel export using 'xlsx' package
// PDF export using 'jspdf' + 'jspdf-autotable'
interface ExportButtonProps {
  data: Record<string, unknown>[];
  columns: string[];
  filename: string;
  formats: ('excel' | 'pdf')[];
}
```

### 10.6 Sidebar

**File:** `src/components/layout/Sidebar.tsx`

```typescript
// Navigation items — filter by session.user.menu_access
const NAV_ITEMS = [
  { key: 'dashboard', label: 'Home', href: '/dashboard', icon: HomeIcon },
  { key: 'analytics', label: 'Dashboard', href: '/analytics', icon: ChartIcon },
  { key: 'company', label: 'Company', href: '/company', icon: BuildingIcon },
  { key: 'employee', label: 'Employee', href: '/employee', icon: UsersIcon },
  { key: 'attendance', label: 'Attendance', href: '/attendance', icon: CalendarIcon },
  { key: 'payroll', label: 'Payroll', href: '/payroll', icon: CurrencyIcon },
  { key: 'payment-approvals', label: 'Payment Approvals', href: '/payment-approvals', icon: CheckIcon },
  { key: 'reports', label: 'Reports', href: '/reports', icon: FileTextIcon },
];

// Filter: only show items where session.user.menu_access.includes(item.key)
```

---

## 11. DATABASE CONNECTION

**File:** `src/lib/db.ts`

```typescript
import mysql from 'mysql2/promise';

const pool = mysql.createPool({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
  waitForConnections: true,
  connectionLimit: 10,
});

export async function query<T>(sql: string, params: unknown[] = []): Promise<T[]> {
  const [rows] = await pool.execute(sql, params);
  return rows as T[];
}

export async function queryOne<T>(sql: string, params: unknown[] = []): Promise<T | null> {
  const rows = await query<T>(sql, params);
  return rows[0] ?? null;
}

// ALWAYS scope to company_id — helper:
export function withCompany(sql: string, companyId: number): string {
  return sql.includes('WHERE') 
    ? sql + ` AND company_id = ${companyId}`
    : sql + ` WHERE company_id = ${companyId}`;
}
```

**`.env.local`:**
```
DB_HOST=
DB_USER=
DB_PASSWORD=
DB_NAME=
NEXTAUTH_SECRET=
NEXTAUTH_URL=http://localhost:3000
```

---

## 12. API ROUTE PATTERN

Every API route must:
1. Get session — `const session = await getServerSession(authOptions)`
2. Return 401 if no session
3. Scope all DB queries to `session.user.company_id`

**Template for any GET route:**
```typescript
// src/app/api/[module]/[resource]/route.ts
import { NextRequest, NextResponse } from 'next/server';
import { getServerSession } from 'next-auth';
import { authOptions } from '@/lib/auth';
import { query } from '@/lib/db';

export async function GET(req: NextRequest) {
  const session = await getServerSession(authOptions);
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  
  const companyId = session.user.company_id;
  const { searchParams } = new URL(req.url);
  
  const rows = await query(
    'SELECT * FROM [table] WHERE company_id = ? AND is_active = 1',
    [companyId]
  );
  
  return NextResponse.json({ data: rows });
}

export async function POST(req: NextRequest) {
  const session = await getServerSession(authOptions);
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  
  const body = await req.json();
  const companyId = session.user.company_id;
  
  // Validate body...
  
  await query(
    'INSERT INTO [table] (company_id, ...) VALUES (?, ...)',
    [companyId, ...]
  );
  
  return NextResponse.json({ success: true }, { status: 201 });
}
```

---

## 13. MIGRATION EXECUTION ORDER

Execute in this order to avoid FK constraint issues:

```
Phase 1 — Infrastructure:
  [ ] Set up Next.js 14 project with App Router
  [ ] Configure MySQL connection (src/lib/db.ts)
  [ ] Implement NextAuth (src/lib/auth.ts) — test login with existing users table
  [ ] Build Sidebar + Layout shell (src/components/layout/)
  [ ] Build shared UI components: DataTable, FormModal, FileUpload, ExportButton

Phase 2 — Company Module (no employee dependencies):
  [ ] Company Info (read-only edit form)
  [ ] Salary & Leave Heads (CRUD + formula types)
  [ ] Statutory Heads
  [ ] Attendance Policy
  [ ] Holiday Calendar
  [ ] Leave Policy
  [ ] Salary Structure
  [ ] IT Declaration Items
  [ ] Document Master
  [ ] Expense Types
  [ ] Employee Assets Creation

Phase 3 — Employee Module:
  [ ] Add Employee (multi-step form)
  [ ] Employee Access (create login)
  [ ] Menu Allocation
  [ ] Import Employee
  [ ] Employee Join
  [ ] Document Upload
  [ ] Employee IT Declarations

Phase 4 — Attendance Module:
  [ ] Attendance Upload (with 26 status codes)
  [ ] Edit Attendance (grid view)
  [ ] Leave Upload + Approve/Cancel Leaves (state machine)
  [ ] Attendance Register (summary view)
  [ ] Shift Planner
  [ ] Verify Monthly Attendance
  [ ] Overtime verification

Phase 5 — Payroll Engine:
  [ ] Implement src/lib/payroll-engine.ts (calculateSalary function)
  [ ] Salary Management (view/edit per-employee salaries)
  [ ] Variable Upload
  [ ] Salary Advance + Loans
  [ ] Leave Encashment
  [ ] Salary Processing (run engine, preview, finalize)
  [ ] Arrear Calculation
  [ ] Salary Revision

Phase 6 — Reports:
  [ ] Attendance Reports
  [ ] Employee Reports
  [ ] Leave Reports
  [ ] Payroll Reports (Salary Register, Bank Advice, Payslip PDF)
  [ ] EPF/ESI/PT statutory reports

Phase 7 — Polish & Testing:
  [ ] Payment Approvals workflow
  [ ] Biometric Device Management
  [ ] Analytics/Business Dashboard
  [ ] Test all payroll calculations against existing processed data
  [ ] Test multi-tenancy (company_id isolation)
  [ ] PDF payslip generation
```

---

## 14. CRITICAL GOTCHAS

**Read these before implementing any module:**

1. **All data is multi-tenant** — every SQL query must include `WHERE company_id = ?`. No exceptions.

2. **Password hashing** — Read `app/Model/UserAccess.php` to see if CakePHP used MD5, SHA1, or bcrypt. The users table already has passwords. Your auth must match the existing hash algorithm, OR run a one-time migration to bcrypt.

3. **Salary head dependency order** — When computing salaries, Basic must compute before HRA (which is % of Basic). Read `app/Controller/PayrollController.php` for the exact computation order. Implement topological sort based on `formula_value` references.

4. **Calendar vs. Working Days vs. Fixed Days** — The divisor for LOP deduction is NOT always the calendar days of the month. It depends on the assigned attendance policy. One employee may use 26, another 30, another 22.

5. **ESI eligibility re-check** — If an employee's gross drops below ₹21,000 mid-year, ESI kicks in. If it rises above ₹21,000, ESI continues until year-end (check CakePHP logic — may differ).

6. **Leave status machine is 11 states** — Do NOT simplify to just Approved/Rejected. The multi-level flow is a core feature.

7. **The app is a true SPA with one URL** — Users copy-paste `/Dashboard` to share access. In Next.js each module IS a separate URL — add a redirect so old `/Dashboard` links still work.

8. **AJAX navigation pattern** — CakePHP views load via AJAX into `#main-content`. In Next.js this becomes normal page navigation — no special handling needed, but transitions should be fast (use `<Suspense>` + skeletons).

9. **No real-time/WebSocket features** — The app is purely request-response. No need for WebSockets.

10. **File uploads** — CakePHP stores files in `app/webroot/uploads/` or similar. In Next.js, store in `/public/uploads/` or use cloud storage (S3). Read `app/Controller/` upload actions for path conventions.

11. **Reports are server-rendered** — Do not generate large payroll reports client-side. Keep heavy data processing in API routes.

12. **The `formula_value` field is a human-readable string** — e.g., `"40% of Basic"`. You must parse it. Write a parser: extract percentage + head_code.

---

*Generated from live app exploration — v1.mypayrollmaster.online — June 2026*  
*Verify all table names and column names against your actual MySQL schema before implementing.*