# RIZO (MyPayrollMaster) — Comprehensive Migration Plan
# CakePHP 2.x → Next.js 14

**Last Updated:** June 2026  
**Source Stack:** CakePHP 2.x + jQuery + MySQL  
**Target Stack:** Next.js 14 (App Router) + MySQL (same cluster) + NextAuth.js v4 + TanStack Query v5  
**DB Policy:** Keep MySQL as-is. Keep all stored procedures and functions. Call them from Next.js API routes.  
**PostgreSQL migration:** Separate future project — not in scope.

---

## Table of Contents

1. [Critical Architecture Findings](#1-critical-architecture-findings)
2. [Full Codebase Inventory](#2-full-codebase-inventory)
3. [Technology Stack](#3-technology-stack)
4. [Database Connection Strategy](#4-database-connection-strategy)
5. [Authentication & Session Design](#5-authentication--session-design)
6. [Next.js Project Structure](#6-nextjs-project-structure)
7. [Real Table Name Reference](#7-real-table-name-reference)
8. [Stored Procedures & Functions Reference](#8-stored-procedures--functions-reference)
9. [Module-by-Module Migration Plan](#9-module-by-module-migration-plan)
10. [Implementation Phases & Timeline](#10-implementation-phases--timeline)
11. [Key Technical Decisions & Gotchas](#11-key-technical-decisions--gotchas)
12. [Environment Variables](#12-environment-variables)
13. [Non-Goals](#13-non-goals)

---

## 1. Critical Architecture Findings

### 1.1 Dual-Database Per-Company Architecture

The system does NOT use a single database with a `company_id` column. It uses two separate MySQL tiers:

| Tier | Database | Purpose |
|------|----------|---------|
| **Control DB** | `mypayrol_control_db` | Company registry, login table, device mappings |
| **Company DB** | e.g. `mypayrol_trial`, `mypayrol_mpm1` | All HR/payroll data — one DB per company tenant |

**`central_control` table (control DB):**

| Column | Description |
|--------|-------------|
| `control_pkey` | Company PK (stored in session as `company_key`) |
| `company_code` | Short code e.g. `GLET`, `ABSG`, `STFR` |
| `company_name` | Display name |
| `user_db` | Per-company database name |
| `Admin_name` | DB username for that company |
| `user_pwd` | DB password for that company |
| `active` | `'active'` / `'inactive'` |
| `start_date_effective` | Subscription start |
| `end_date_effective` | Subscription end |
| `plan` | Legacy plan string |
| `subdomain` | Subdomain if any |

### 1.2 CakePHP Session Variables (Map to JWT)

Every CakePHP controller reads these from session. Next.js must replicate them via JWT + request context:

| CakePHP Session Key | Type | JWT Field | Description |
|--------------------|------|-----------|-------------|
| `company_key` | int | `controlPkey` | `central_control.control_pkey` |
| `company_code` | string | `companyCode` | e.g. `GLET` |
| `user_group` | int | `userGroup` | 1 = admin, 2 = employee |
| `emp_fkey` | int | `empFkey` | Employee PK (null for admin-only users) |
| `login_user_id` | string | `loginUserId` | Username |
| `ds` | string | *(derived)* | Always `'companydb'` — in Next.js this comes from pool lookup |
| `company_logo` | string | — | Fetched from `comp_contact_info.logo` |
| `company_name` | string | — | Fetched from `comp_contact_info.business_name` |

### 1.3 Payroll Engine is Almost Entirely in Stored Procedures

**Key finding:** The PHP controllers for payroll are thin wrappers. All calculation logic lives in MySQL stored procedures. The migration for payroll is mostly about calling these procs correctly, not rewriting calculations.

Core payroll flow in legacy:
1. Admin selects branch + month → calls `payroll_master_insert(branch, month, user_id, @error)`
2. System calls `salary_process_prc(...)` or newer `calculate_salary_main_prc(...)` per employee
3. Tax processing via `tax_salary_process_prc(...)`
4. Approval changes `payroll_master.action` status

### 1.4 Plan/Feature Flag System

`plan_id` stored in `central_control` (or `user_credentials`) controls which features are enabled. Tables in control DB:
- `features` — feature definitions (feature_id, feature_key, feature_name, feature_path, is_common, display_order)
- `plan_feature` — maps (plan_id, feature_id, is_enabled)

Every major module should check `plan_feature` before showing features. The `getPayrollFeatures()` pattern in `SalaryProcessingController.php` is the template.

### 1.5 Company-Specific Hard-Coded Overrides

Legacy code has many `if ($company_code == 'GLET' || ...)` branches. These need to be handled as configuration, not hard-coded in Next.js. Create a `getCompanyConfig(companyCode)` helper that returns feature flags per company.

### 1.6 Slim PHP API v1

There is a separate Slim Framework PHP API at `legacy/api/v1/` with its own routes, middleware, and dependencies. This serves mobile apps or third-party integrations. It connects to the same MySQL databases. Migration decision: **Keep the Slim API running in parallel during migration; migrate its endpoints to Next.js API routes in a final phase.**

---

## 2. Full Codebase Inventory

### 2.1 Scale

| Component | Count |
|-----------|-------|
| CakePHP Controllers | 223 files (including ~40 backup files) |
| CakePHP Models | 243 files |
| View templates (.ctp) | 1,700+ across 223 directories |
| Schema SQL files | 2 (control DB: 2916 lines, company DB: 47975 lines) |
| Slim API v1 | Separate PHP app with its own routes |

### 2.2 Active Controller Groups (Excluding Backups)

**Core / Auth**
- `SiteController` — login, logout, password reset
- `AppController` — base controller (DB connection, menu, notifications)

**Dashboard**
- `DashboardController` — admin dashboard (employee count, present today, leave requests)
- `DashboardNewController` — analytics/BI dashboard
- `BusinessDashboardController` — business metrics

**Employee Management**
- `EmployeeController` — employee CRUD, profile
- `EmployeeDetailController` — detailed view
- `EmployeeManageController` — management actions
- `EmployeeRegisterController` — new hire registration
- `EmployeeJoinController` — joining formalities
- `EmployeeResignationController` — resignation management
- `EmployeeMenuController` — employee self-service menu
- `UserController` / `UserCredentialsController` — user account management
- `UserAccessController` — access control (which menus per employee)

**Organizational Setup**
- `CompanyController` / `CompanySetupController` — company profile
- `BranchController` — branches
- `DepartmentController` — departments
- `DesignationController` — designations
- `GradeController` / `GradesController` — grades
- `DivisionController` / `SectionController` / `VerticalController` — org hierarchy
- `FinancialYearController` — financial year config
- `DbConfigController` — attendance config (format, dates)
- `HolidayCalendarController` — holidays

**Attendance**
- `AttendanceController` — attendance register (grid view by employee/month)
- `AttendanceregisterController` / `AttendanceRegisterNewController` — register list
- `EditAttendanceController` — edit attendance entries
- `EditPunchesController` — edit device punch data
- `AttendanceSetupController` — attendance setup/config
- `DeviceController` / `DeviceEmployeeInfoController` — biometric device management
- `DayTimeProcedureController` — shift/schedule management
- `OtAttendanceController` — overtime attendance
- `CompoffController` — compensatory off
- `RegularisationController` — attendance regularization
- `ShiftPlannerController` — shift planning
- `EmpattendanceController` — employee's own attendance view
- `AttendanceCheckInOutController` — manual check-in/out
- `SiteAttendanceController` — site-based attendance
- `ExceptionRuleController` — attendance exception rules
- `ScheduledBreakOffController` — planned break-off management

**Leave Management**
- `LeaveRequestController` — leave application and workflow
- `EmployeeLeaveRequestController` — employee's leave requests
- `EmployeeLeavesController` / `EmployeeLeaveUploadController` — leave balance management
- `LeavePolicyController` — leave policy config
- `LeaveEncashmentRequestController` — leave encashment
- `LopReportsController` — LOP (Loss of Pay) reports

**Payroll**
- `PayrollController` — payroll dashboard/view
- `PayrollProcessController` — payroll processing (calls stored procs)
- `SalaryProcessingController` — salary processing page + plan features
- `SalaryHeadsController` — salary head and item configuration
- `SalaryStructureController` — salary structure (per-employee structure)
- `SalaryIncrementController` — salary increments
- `SalaryComponentUploadController` — bulk component upload
- `FixedPaymentUploadController` — fixed payment bulk upload
- `VariableController` — variable pay components
- `TaxationController` / `TaxController` / `TaxHeadsController` / `EmpTaxController` — taxation
- `ArrearController` — salary arrears
- `AdvanceController` / `EmployeeadvanceController` — salary advances
- `EmployeeLoanController` — employee loans
- `FullandFinalsettlementController` — FnF settlement
- `YearEndController` — year-end processing

**Reports**
- `SalaryReportsController` / `SalarySlipReportsController` — salary and payslip reports
- `AttendanceReportsController` / `AttendanceReportsNewController` — attendance reports
- `EmployeeAdvanceReportsController` / `EmployeeLoanReportsController` — advance/loan reports
- `EsiEpfReportController` — statutory (ESI/EPF) reports
- `StatutoryRegistersController` / `StatutoryReportController` — statutory compliance
- `ReportController` / `ReportsController` / `MiscellaniousReportsController` — general reports
- `HierarchyReportController` — hierarchy/org chart reports
- `EditedReportsController` — edited data audit reports
- `ConfigReportController` — configuration reports
- `VariableReportController` — variable pay reports
- `ArrearsReportsController` — arrear reports

**Assets & Inventory**
- `AssetController` / `AssetsReportsController` — asset management
- `StockmanagementController` / `StockReportController` / `StockTranferController` — inventory

**Projects & Expenses**
- `ProjectController` — project management
- `ProjectExpensesController` / `ProjectExpenseReportController` — project expenses
- `ExpenseTypeController` / `ExpenseItemController` / `ExpenseReportController` — expenses
- `EmployeeExpensesController` / `EmployeeExpenseReportsController` — employee expenses

**Field & Site**
- `SiteController` (not login — site management) — construction/field site management
- `SiteAttendanceController` — site attendance
- `SiteReportsController` — site reports
- `SiteWorkController` — site work tracking
- `FieldSurveyController` — field survey management
- `GatePassController` / `OutPassController` — gate/out passes

**Procurement**
- `PurchaseOrderController` / `DirectPurchaseOrderController` — purchase orders
- `GoodsReceivedNotesController` — GRN management
- `SupplierMasterController` / `VendorController` — vendor management
- `MaterialController` / `MaterialRequestController` — material management
- `StoreController` — store/warehouse management

**Performance & HR**
- `PerformanceController` — performance review
- `TeamReviewController` / `SelfReviewController` / `HierarchyReviewController` — review types
- `TrainingController` — training management (if present)
- `DocumentManagerController` — document management
- `MailBoxController` — internal messaging

**Communication & Notification**
- `EventHandlerController` — event management and reminders
- `InfoController` — info/notice board
- `ApiRequestController` — API integration handler
- `MobileLocationUpdateController` — mobile tracking

---

## 3. Technology Stack

### 3.1 Target Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Framework | Next.js App Router | 14.x |
| Language | TypeScript | 5.x |
| Auth | NextAuth.js | v4 (stable) |
| DB Client | mysql2/promise | 3.x |
| Server State | TanStack Query | v5 |
| Forms | React Hook Form + Zod | latest |
| Tables | TanStack Table | v8 |
| UI Components | shadcn/ui | latest |
| Styling | Tailwind CSS | 3.x |
| PDF Export | jspdf + jspdf-autotable | latest |
| Excel Export | SheetJS (xlsx) | latest |
| Charts | Recharts or Chart.js | latest |
| Icons | Lucide React | latest |

### 3.2 Key Package Commands

```bash
npx create-next-app@latest rizo --typescript --tailwind --eslint --app --src-dir
cd rizo
npm install next-auth mysql2 @tanstack/react-query @tanstack/react-table
npm install react-hook-form zod @hookform/resolvers
npm install jspdf jspdf-autotable xlsx
npm install recharts lucide-react
npx shadcn-ui@latest init
```

---

## 4. Database Connection Strategy

### 4.1 Connection Manager (`src/lib/db.ts`)

```typescript
import mysql from 'mysql2/promise';

// Control DB pool — single, created at module load
const controlPool = mysql.createPool({
  host: process.env.CONTROL_DB_HOST,
  user: process.env.CONTROL_DB_USER,
  password: process.env.CONTROL_DB_PASSWORD,
  database: process.env.CONTROL_DB_NAME,
  connectionLimit: 5,
  waitForConnections: true,
});

// Per-company pools — keyed by company_code, created on first use
const companyPools = new Map<string, mysql.Pool>();

export async function getCompanyPool(companyCode: string): Promise<mysql.Pool> {
  if (companyPools.has(companyCode)) {
    return companyPools.get(companyCode)!;
  }

  const [rows] = await controlPool.execute<mysql.RowDataPacket[]>(
    `SELECT user_db, Admin_name, user_pwd 
     FROM central_control 
     WHERE company_code = ? AND active = 'active'`,
    [companyCode]
  );

  if (!rows.length) throw new Error(`Company not found or inactive: ${companyCode}`);

  const { user_db, Admin_name, user_pwd } = rows[0];

  const pool = mysql.createPool({
    host: process.env.COMPANY_DB_HOST || process.env.CONTROL_DB_HOST,
    // In local dev, fall back to root if per-company DB user doesn't exist
    user: process.env.NODE_ENV === 'development' ? 'root' : Admin_name,
    password: process.env.NODE_ENV === 'development' ? 'root' : user_pwd,
    database: user_db,
    connectionLimit: 10,
    waitForConnections: true,
  });

  companyPools.set(companyCode, pool);
  return pool;
}

export { controlPool };

// Helper: get company pool from a Next.js API route (reads company_code from JWT)
export async function getPoolFromSession(
  companyCode: string
): Promise<mysql.Pool> {
  return getCompanyPool(companyCode);
}
```

### 4.2 API Route Pattern

Every API route that touches company data must:
1. Get session via `getServerSession(authOptions)`
2. Extract `companyCode` from JWT
3. Call `getCompanyPool(companyCode)`
4. Run queries

```typescript
// src/app/api/employees/route.ts (example)
import { getServerSession } from 'next-auth';
import { authOptions } from '@/lib/auth';
import { getCompanyPool } from '@/lib/db';

export async function GET() {
  const session = await getServerSession(authOptions);
  if (!session) return Response.json({ error: 'Unauthorized' }, { status: 401 });

  const pool = await getCompanyPool(session.user.companyCode);
  const [rows] = await pool.execute(
    'SELECT emp_pkey, emp_name, emp_id, status FROM emp_details WHERE status = 1 ORDER BY emp_name'
  );
  return Response.json(rows);
}
```

### 4.3 Calling Stored Procedures

```typescript
// Calling a procedure (no result set)
await pool.execute('CALL insert_update_att_reg(?, ?, ?, ?, @err)', [p1, p2, p3, p4]);

// Calling a procedure with result set
const [results] = await pool.execute('CALL salary_process_prc(?, ?, ?, @err)', [branch, month, userId]);

// Calling a function
const [rows] = await pool.execute<mysql.RowDataPacket[]>(
  'SELECT att_start_end_fn(?, ?) AS att_date',
  [yearMonth, 1]
);
const startDate = rows[0].att_date;
```

---

## 5. Authentication & Session Design

### 5.1 Login Flow

**Legacy:** `SiteController::login()` queries `user_credentials` table in control DB, checks SHA1 password, creates PHP session with `company_key`, `company_code`, `user_group`, `emp_fkey`.

**Next.js (NextAuth CredentialsProvider):**

```typescript
// src/lib/auth.ts
import NextAuth, { AuthOptions } from 'next-auth';
import CredentialsProvider from 'next-auth/providers/credentials';
import { controlPool } from './db';
import crypto from 'crypto';
import bcrypt from 'bcryptjs';

export const authOptions: AuthOptions = {
  providers: [
    CredentialsProvider({
      name: 'Credentials',
      credentials: {
        username: { label: 'Username', type: 'text' },
        password: { label: 'Password', type: 'password' },
        companyCode: { label: 'Company Code', type: 'text' },
      },
      async authorize(credentials) {
        if (!credentials) return null;
        const { username, password, companyCode } = credentials;

        const [rows] = await controlPool.execute<mysql.RowDataPacket[]>(
          `SELECT uc.*, cc.control_pkey, cc.user_db, cc.plan
           FROM user_credentials uc
           JOIN central_control cc ON uc.control_fkey = cc.control_pkey
           WHERE uc.user_id = ? AND uc.company_code = ?
             AND cc.active = 'active'
             AND uc.access_allowed = 'Y'`,
          [username, companyCode.toUpperCase()]
        );

        if (!rows.length) return null;
        const user = rows[0];

        // Detect SHA1 (40 hex chars) vs bcrypt ($2b$...)
        let passwordValid = false;
        if (user.password.length === 40 && /^[a-f0-9]+$/.test(user.password)) {
          // SHA1 check
          const sha1 = crypto.createHash('sha1').update(password).digest('hex');
          passwordValid = sha1 === user.password;

          // Transparent upgrade to bcrypt on next login
          if (passwordValid) {
            const hash = await bcrypt.hash(password, 12);
            await controlPool.execute(
              'UPDATE user_credentials SET password = ? WHERE user_pkey = ?',
              [hash, user.user_pkey]
            );
          }
        } else {
          // bcrypt check
          passwordValid = await bcrypt.compare(password, user.password);
        }

        if (!passwordValid) return null;

        return {
          id: String(user.user_pkey),
          name: username,
          email: user.email || '',
          controlPkey: user.control_pkey,
          companyCode: user.company_code,
          userGroup: user.user_group,
          empFkey: user.emp_fkey || null,
          loginUserId: username,
        };
      },
    }),
  ],
  callbacks: {
    async jwt({ token, user }) {
      if (user) {
        token.controlPkey = user.controlPkey;
        token.companyCode = user.companyCode;
        token.userGroup = user.userGroup;
        token.empFkey = user.empFkey;
        token.loginUserId = user.loginUserId;
      }
      return token;
    },
    async session({ session, token }) {
      session.user.controlPkey = token.controlPkey as number;
      session.user.companyCode = token.companyCode as string;
      session.user.userGroup = token.userGroup as number;
      session.user.empFkey = token.empFkey as number | null;
      session.user.loginUserId = token.loginUserId as string;
      return session;
    },
  },
  pages: {
    signIn: '/login',
  },
  session: { strategy: 'jwt' },
};
```

### 5.2 Middleware (Route Protection)

```typescript
// src/middleware.ts
import { withAuth } from 'next-auth/middleware';

export default withAuth({
  pages: { signIn: '/login' },
});

export const config = {
  matcher: [
    '/((?!login|api/auth|_next/static|_next/image|favicon.ico|public).*)',
  ],
};
```

### 5.3 TypeScript Session Type Extension

```typescript
// src/types/next-auth.d.ts
import NextAuth from 'next-auth';

declare module 'next-auth' {
  interface Session {
    user: {
      id: string;
      name?: string | null;
      email?: string | null;
      controlPkey: number;
      companyCode: string;
      userGroup: number; // 1=admin, 2=employee
      empFkey: number | null;
      loginUserId: string;
    };
  }
}
```

---

## 6. Next.js Project Structure

```
rizo/
├── src/
│   ├── app/
│   │   ├── (auth)/
│   │   │   └── login/
│   │   │       └── page.tsx
│   │   ├── (dashboard)/
│   │   │   ├── layout.tsx          ← sidebar, header, session provider
│   │   │   ├── dashboard/
│   │   │   │   └── page.tsx
│   │   │   ├── employees/
│   │   │   │   ├── page.tsx        ← list
│   │   │   │   ├── [id]/
│   │   │   │   │   └── page.tsx    ← view/edit
│   │   │   │   └── new/
│   │   │   │       └── page.tsx    ← create
│   │   │   ├── attendance/
│   │   │   │   ├── register/       ← attendance register grid
│   │   │   │   ├── edit/           ← edit entries
│   │   │   │   └── upload/         ← bulk upload
│   │   │   ├── leave/
│   │   │   │   ├── requests/       ← apply/manage leaves
│   │   │   │   ├── my-leaves/      ← employee's own leaves
│   │   │   │   └── policy/         ← leave policy config
│   │   │   ├── payroll/
│   │   │   │   ├── process/        ← run payroll
│   │   │   │   ├── approve/        ← approve payroll
│   │   │   │   ├── salary-heads/   ← config: salary heads
│   │   │   │   └── salary-structure/ ← per-employee salary structure
│   │   │   ├── salary/
│   │   │   │   ├── reports/        ← salary reports
│   │   │   │   └── slips/          ← payslips
│   │   │   ├── reports/
│   │   │   │   ├── attendance/
│   │   │   │   ├── statutory/
│   │   │   │   └── misc/
│   │   │   ├── setup/
│   │   │   │   ├── company/
│   │   │   │   ├── branches/
│   │   │   │   ├── departments/
│   │   │   │   ├── designations/
│   │   │   │   ├── grades/
│   │   │   │   ├── financial-year/
│   │   │   │   ├── holidays/
│   │   │   │   └── db-config/      ← attendance period config
│   │   │   ├── assets/
│   │   │   ├── loans/
│   │   │   └── taxation/
│   │   └── api/
│   │       ├── auth/[...nextauth]/route.ts
│   │       ├── employees/
│   │       │   ├── route.ts         ← GET list, POST create
│   │       │   └── [id]/route.ts    ← GET, PUT, DELETE
│   │       ├── attendance/
│   │       │   ├── register/route.ts
│   │       │   ├── process/route.ts ← calls insert_update_att_reg
│   │       │   └── upload/route.ts
│   │       ├── leave/
│   │       │   ├── requests/route.ts
│   │       │   └── process/route.ts ← calls leave_transaction_prc
│   │       ├── payroll/
│   │       │   ├── process/route.ts ← calls payroll_master_insert + salary_process_prc
│   │       │   ├── approve/route.ts
│   │       │   └── slip/[id]/route.ts
│   │       └── reports/
│   ├── components/
│   │   ├── ui/                      ← shadcn/ui components
│   │   ├── layout/
│   │   │   ├── Sidebar.tsx
│   │   │   ├── Header.tsx
│   │   │   └── Breadcrumb.tsx
│   │   ├── data-table/
│   │   │   └── DataTable.tsx        ← TanStack Table wrapper
│   │   ├── forms/
│   │   │   └── FormField.tsx
│   │   └── attendance/
│   │       └── AttendanceGrid.tsx   ← calendar-grid component
│   ├── lib/
│   │   ├── db.ts                    ← dual-pool connection manager
│   │   ├── auth.ts                  ← NextAuth config
│   │   ├── utils.ts                 ← cn() and helpers
│   │   └── company-config.ts        ← per-company feature flags
│   ├── hooks/
│   │   ├── useSession.ts
│   │   └── useCompanyPool.ts
│   └── types/
│       ├── next-auth.d.ts
│       ├── employee.ts
│       ├── attendance.ts
│       ├── leave.ts
│       └── payroll.ts
├── .env.local
├── next.config.ts
└── package.json
```

---

## 7. Real Table Name Reference

Derived from reading Model files and SQL schema. **Use these names in queries — NOT the simplified names in the Migration Specification document.**

### 7.1 Control DB Tables (`mypayrol_control_db`)

| CakePHP Model | Real Table Name | Primary Key |
|---------------|-----------------|-------------|
| `CentralControl` | `central_control` | `control_pkey` |
| `CentralUserCredentials` | `user_credentials` | `user_pkey` |
| `Registrations` | `registrations` | — |
| `Features` | `features` | `feature_id` |
| `PlanFeature` | `plan_feature` | — |
| `Plan` | `plan` | `plan_id` |
| — | `company_branches` | — |
| — | `emp_device_comp_branch` | — |
| — | `attendancelogs` | — |

### 7.2 Company DB Tables (`mypayrol_trial` / per-company DB)

| CakePHP Model | Real Table Name | Primary Key | Notes |
|---------------|-----------------|-------------|-------|
| `EmpDetails` | `emp_details` | `emp_pkey` | Main employee table |
| `EmployeeProfessionalDetails` | `emp_proff` | — | FK: `emp_fkey` → `emp_pkey` |
| `EmployeeInfo` | `employee_info` | `emp_pkey` | Alternative employee view |
| `EmployeeDetails` | `emp_details` | `emp_pkey` | Same as EmpDetails |
| `AttendanceRegister` | `attendance_register` | `registerid` | Monthly attendance records |
| `Payrollmaster` | `payroll_master` | `payroll_master_pkey` | Payroll records per employee per month |
| `Payrolltransactions` | `payroll_transactions` | — | FK: `payroll_master_fkey` |
| `SalaryHeads` | `salary_heads` | `head_pkey` | Earnings / Deductions groups |
| `SalaryHeadItems` | `salary_head_items` | `salary_head_item_pkey` | Individual components (Basic, HRA…) |
| `SalaryStructures` | `salary_structures` | `structure_id` | Salary structure templates |
| `SalaryStructureDetails` | `salary_structure_details` | — | Components per structure |
| `EmployeeSalaryStructure` | `emp_salary_structure` | — | Employee-specific structure assignments |
| `LeaveRequests` | `leaveentries` | `LEAVEENTRYID` | Leave applications |
| `EmployeeLeaveTransaction` | `emp_leave_transactions` | — | Daily leave records |
| `LeavePolicy` | `leave_policy` | — | Leave policy config |
| `EmployeeCTC` | `emp_ctc_upload` | — | CTC upload history |
| `MonthlyCTC` | `monthly_ctc` | — | Monthly CTC per employee |
| `GrossSalary` | `gross_salary` | — | |
| `TotalDeductions` | `total_deductions` | — | |
| `MonthlyAmount` | `monthly_amount` | — | |
| `EmpSalarySlip` | `emp_salary_slip` | — | Generated payslips |
| `UserCredentials` | `user_credentials` | `user_pkey` | Login (also exists in company DB) |
| `CompanyContactInfo` | `comp_contact_info` | — | Company profile, logo |
| `DbConfig` | `db_config` | — | Attendance period config |
| `Menu` | `emp_menu` (admin menu) | `menu_id` | |
| `EmployeeMenu` | `emp_menu` | `menu_id` | Employee menu items |
| `Useraccess` | `user_access` | — | Which menus per employee |
| `Departments` | `departments` | `dept_pkey` | |
| `Designation` | `designation` | `desig_pkey` | |
| `Grades` | `grades` | `grade_pkey` | |
| `Verticals` | `verticals` | — | |
| `Units` | `branches` | `branch_pkey` | "Branch" is also called "Unit" |
| `FinancialYear` | `financial_year` | — | |
| `Holiday` | `holiday` | — | |
| `HolidayGroup` | `holiday_group` | — | |
| `DeviceAttendance` | `present_today` / `present_today_all` | — | Dashboard counters |
| `Devicelog` | `device_logs` | — | Raw biometric punch logs |
| `EditPunches` | `edit_punches` | — | Manual punch edits |
| `EmployeeLeaveInfo` | `emp_leave_balance` (approx) | — | Leave balances |
| `EmployeeLoan` | `employee_loan` | — | |
| `LoanEmi` | `loan_emi` | — | |
| `EmployeeAdvance` | `employee_advance` | — | |
| `TaxHead` | `tax_heads` | — | |
| `Taxsalarycomponents` | `tax_salary_components` | — | |
| `EmpTaxSalTrans` | `emp_tax_sal_transactions` | — | |
| `SalaryIncrement` | `salary_increment` | — | |
| `SalaryIncrementDetails` | `salary_increment_details` | — | |
| `ComponentIncrement` | `component_increment` | — | |
| `Assets` | `assets` | — | |
| `Site` | `site` | `site_pkey` | Field sites |
| `SiteAttendance` | `site_attendance` | — | |
| — | `working_day_time_procedures` | `day_time_seq` | Shift/schedule definitions |
| — | `emp_calc_variable_components` | — | Calculated variable pay |
| — | `leave_encashment_master` | — | Leave encashment records |

---

## 8. Stored Procedures & Functions Reference

**Policy: Call all existing stored procedures and functions as-is. Do not rewrite them.**

### 8.1 Attendance

| Procedure/Function | Call Signature | Purpose |
|--------------------|----------------|---------|
| `insert_update_att_reg` | `CALL insert_update_att_reg(p1, p2, p3, p4, @Perr_msg)` | Insert/update single attendance entry |
| `att_start_end_fn` | `SELECT att_start_end_fn(year_month DATE, 1\|2 INT) AS date` | Returns attendance period start (1) or end (2) date based on `db_config` |
| `att_start_end_date_fn` | `SELECT att_start_end_date_fn(start_date, year_month)` | Alternative date calc |
| `device_logs_iteration_fn` | `SELECT device_logs_iteration_fn(emp_id, year_month)` | Processes device logs for one employee into `attendance_register` |
| `bulk_device_logs_iteration_prc` | `CALL bulk_device_logs_iteration_prc(year_month DATE)` | Bulk version — all employees |
| `bulk_company_idupdate` | `CALL bulk_company_idupdate(emp_pkey INT)` | Syncs employee IDs across tables |

### 8.2 Payroll

| Procedure/Function | Call Signature | Purpose |
|--------------------|----------------|---------|
| `payroll_master_insert` | `CALL payroll_master_insert(branch, month_year, user_id, @error)` | Creates `payroll_master` records for branch+month |
| `salary_process_prc` | `CALL salary_process_prc(p1, p2, ..., @Perr_msg)` | Main salary calculation (legacy) |
| `calculate_salary_main_prc` | `CALL calculate_salary_main_prc(p1, p2, ..., @Perr_msg)` | Newer salary calculation (as of Dec 2025) |
| `tax_salary_process_prc` | `CALL tax_salary_process_prc(p1, ..., @Perr_msg)` | Tax processing |
| `calculate_emp_component_breakup` | `CALL calculate_emp_component_breakup(structure_id, salary_head_item_pkey, monthly_comp, monthly_gross)` | Returns salary breakup temp table |
| `calculate_emp_salary_breakup` | `CALL calculate_emp_salary_breakup(emp_fkey, structure_id, monthly_gross)` | Full salary breakdown per employee |
| `calculate_holiday_allowances_prc` | `CALL calculate_holiday_allowances_prc(emp_pkey, month_year, created_by)` | Sunday/holiday allowances |
| `calculate_leave_encashment_prc` | `CALL calculate_leave_encashment_prc(emp_pkey, month_year, created_by)` | Leave encashment into variable pay |
| `get_present_in_weekoff_holiday_count_prc` | `CALL get_present_in_weekoff_holiday_count_prc(emp_pkey, month_year, OUT weekoff, OUT holiday)` | Count weekend/holiday present days |

### 8.3 Leave

| Procedure/Function | Call Signature | Purpose |
|--------------------|----------------|---------|
| `leave_transaction_prc` | `CALL leave_transaction_prc(params..., @Perror_message)` | All leave state transitions (apply, authorize, approve, reject, cancel) |
| `leave_auth_apr_person_fn` | `SELECT leave_auth_apr_person_fn(company_code, emp_fkey, 'auth'\|'apr'\|'api')` | Returns comma-separated employee PKs who can authorize/approve for given employee |
| `leave_end_process_fn` | `SELECT leave_end_process_fn(...)` | End-of-period leave balance calculation |
| `leave_end_process_parent_prc` | `CALL leave_end_process_parent_prc(...)` | Parent proc for leave period close |

### 8.4 Company / HR

| Procedure/Function | Call Signature | Purpose |
|--------------------|----------------|---------|
| `company_statistics_prc` | `CALL company_statistics_prc(...)` | Company-level statistics |
| `get_branch_code_abs_fn` | `SELECT get_branch_code_abs_fn(emp_pkey)` | Returns branch code for employee (used for ABSG/GLET hierarchy) |

### 8.5 Salary Component Formula Operators

The `salary_structure_details.structure_det_operator` field controls how a component value is calculated. These are evaluated inside `calculate_emp_salary_breakup`:

| Operator | Calculation |
|----------|-------------|
| `formula` | `(derived_perc / 100) × monthly_gross` |
| `fixed` | `structure_det_value` (flat amount) |
| `limit` | `MIN(structure_det_value, formula_value)` |
| `limit_wl` | `MIN(structure_det_depends, formula_value)` |
| `limit_wg` | `MAX(structure_det_depends, formula_value)` |
| `rembalance` | `monthly_gross − sum_of_distributed_components` (remainder) |

---

## 9. Module-by-Module Migration Plan

### 9.1 Auth Module (Phase 1)

**Legacy:** `SiteController::login()` + PHP session  
**Next.js:** NextAuth CredentialsProvider + JWT

| Legacy URL | Next.js URL | Status |
|-----------|-------------|--------|
| `/Site/login` | `/login` | Migrate |
| `/Site/logout` | `POST /api/auth/signout` | NextAuth built-in |
| `/Site/forgotpassword` | `/login/forgot-password` | Migrate |
| `/Site/resetpassword` | `/login/reset-password` | Migrate |

**API Routes:**
- `POST /api/auth/[...nextauth]` — handled by NextAuth
- `POST /api/auth/forgot-password` — custom: sends reset email
- `POST /api/auth/reset-password` — custom: validates token, updates password

**DB Tables Used:** `user_credentials` (control DB), `central_control` (control DB)

---

### 9.2 Dashboard Module (Phase 2)

**Legacy:** `DashboardController::index()` — branches into admin/employee/hierarchy dashboard  
**Next.js:** Single page `/dashboard` that renders the appropriate view based on `userGroup`

| Legacy URL | Next.js URL |
|-----------|-------------|
| `/Dashboard/index` | `/dashboard` |
| `/DashboardNew/index` | `/dashboard/analytics` |

**Key Queries:**
```sql
-- Total active employees
SELECT COUNT(*) FROM emp_details WHERE status = 1;

-- Present today
SELECT COUNT(*) FROM present_today;

-- Today's attendance (with date filter)
SELECT COUNT(*) FROM present_today_all WHERE STR_TO_DATE(LOGDATE, '%Y-%m-%d') = CURDATE();

-- Pending leave requests (for authorizer)
SELECT leaveentries.EMP_fkey, emp_details.first_name, emp_details.last_name
FROM leaveentries
LEFT JOIN emp_details ON emp_details.emp_pkey = leaveentries.EMP_fkey
WHERE (ISAutherizedby = ? AND ISAutherized = '0' AND LEAVESTATUS IN ('Applied'))
   OR (APPROVEDBY = ? AND ISAPPROVED = '0' AND ISAutherized = '1' AND LEAVESTATUS IN ('Authorized'));

-- Upcoming birthdays / work anniversaries
SELECT 'BIR', first_name, DATE_FORMAT(date_of_birth,'%M-%d') date_month
FROM emp_details
WHERE DATE_FORMAT(date_of_birth,'%m-%d') BETWEEN DATE_FORMAT(CURDATE(),'%m-%d')
  AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY),'%m-%d')
  AND status = 1
UNION
SELECT 'JOIN', first_name, DATE_FORMAT(joining_date,'%M-%d') date_month
FROM emp_proff LEFT JOIN emp_details ON emp_details.emp_pkey = emp_proff.emp_fkey
WHERE DATE_FORMAT(joining_date,'%m-%d') BETWEEN DATE_FORMAT(CURDATE(),'%m-%d')
  AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY),'%m-%d')
  AND emp_details.status = 1;
```

**API Routes:**
- `GET /api/dashboard/stats` — employee count, present count, pending leaves
- `GET /api/dashboard/events` — birthdays and work anniversaries
- `GET /api/dashboard/notifications` — pending leave/salary notifications

---

### 9.3 Employee Module (Phase 2)

**Legacy:** `EmployeeController` — large controller with many actions  
**Key session branch:** `user_group == 1` → admin list view; `user_group == 2` → employee self-service view

| Legacy URL | Next.js URL |
|-----------|-------------|
| `/Employee/index` | `/employees` |
| `/Employee/setups/:id` | `/employees/:id` |
| `/Employee/add` | `/employees/new` |
| `/EmployeeDetail/index` | `/employees/:id/details` |
| `/EmployeeRegister/index` | `/employees/register` |
| `/UserCredentials/index` | `/employees/:id/account` |
| `/UserAccess/index` | `/employees/:id/access` |

**Key Tables:**
- `emp_details` — core employee data
- `emp_proff` — professional details (joining date, branch, dept, designation, shift)
- `employee_info` — additional info (different from emp_proff)
- `emp_ctc_upload` — CTC history
- `user_credentials` (company DB) — employee login credentials

**API Routes:**
```
GET    /api/employees              ← list (admin) or self (employee)
POST   /api/employees              ← create new employee
GET    /api/employees/[id]         ← profile
PUT    /api/employees/[id]         ← update
GET    /api/employees/[id]/ctc     ← CTC history
PUT    /api/employees/[id]/ctc     ← update CTC
GET    /api/employees/[id]/access  ← menu access
PUT    /api/employees/[id]/access  ← update menu access
```

---

### 9.4 Organizational Setup Module (Phase 2)

Simple CRUD modules with no complex business logic.

| Module | Legacy Controller | Next.js URL | Tables |
|--------|------------------|-------------|--------|
| Company Profile | `CompanyController` | `/setup/company` | `comp_contact_info` |
| Branches | `BranchController` | `/setup/branches` | `branches` |
| Departments | `DepartmentController` | `/setup/departments` | `departments` |
| Designations | `DesignationController` | `/setup/designations` | `designation` |
| Grades | `GradesController` | `/setup/grades` | `grades` |
| Divisions | `DivisionController` | `/setup/divisions` | `verticals` or `division` |
| Financial Year | `FinancialYearController` | `/setup/financial-year` | `financial_year` |
| Holiday Calendar | `HolidayCalendarController` | `/setup/holidays` | `holiday`, `holiday_group` |
| DB Config | `DbConfigController` | `/setup/attendance-config` | `db_config` |
| Shifts | `DayTimeProcedureController` | `/setup/shifts` | `working_day_time_procedures` |

---

### 9.5 Attendance Module (Phase 3)

**Most complex module after payroll.** The attendance register is a calendar-grid view showing all employees × all days with color-coded status codes.

| Legacy URL | Next.js URL |
|-----------|-------------|
| `/Attendance/showregister` | `/attendance/register` |
| `/AttendanceRegisterNew/index` | `/attendance/register` |
| `/EditAttendance/index` | `/attendance/edit` |
| `/EditPunches/index` | `/attendance/edit-punches` |
| `/OtAttendance/index` | `/attendance/overtime` |
| `/Regularisation/index` | `/attendance/regularisation` |
| `/AttendanceSetup/index` | `/setup/attendance-rules` |
| `/ShiftPlanner/index` | `/attendance/shift-planner` |
| `/AttendanceCheckInOut/index` | `/attendance/checkin` |
| `/Device/index` | `/setup/devices` |
| `/Compoff/index` | `/attendance/comp-off` |

**Attendance Status Codes (from `salary_head_items` where `item_type = 'LEAVE'`, plus hardcoded):**

| Code | Label | Color |
|------|-------|-------|
| `P` | Present | green |
| `WO` | Week Off | yellow |
| `HO` | Holiday | blue |
| `A` | Absent | red |
| `LOP` | Loss Of Pay | maroon |
| `TC` | Time Coupon | #ef00ff |
| *(Others from DB)* | Leave types | orange |
| `WFH` | Work From Home | deepskyblue |
| `COFF` | Comp Off | — |
| + many more | see summary | — |

**Key Business Logic:**
- Attendance period is NOT always calendar month — must call `att_start_end_fn(year_month, 1)` for start and `att_start_end_fn(year_month, 2)` for end
- Leave type abbreviations are dynamic — fetched from `salary_head_items` where `item_type = 'LEAVE'`
- Device logs are processed via `device_logs_iteration_fn(emp_id, year_month)`

**API Routes:**
```
GET  /api/attendance/register?month=YYYY-MM&branch=X&emp=Y
POST /api/attendance/register          ← calls insert_update_att_reg proc
GET  /api/attendance/period?month=YYYY-MM   ← calls att_start_end_fn
GET  /api/attendance/leave-types       ← salary_head_items where item_type=LEAVE
POST /api/attendance/process-device    ← calls device_logs_iteration_fn
GET  /api/attendance/punches?emp=X&month=YYYY-MM   ← device punch data
PUT  /api/attendance/punches/[id]      ← edit punch
GET  /api/attendance/regularisation    ← regularization requests
POST /api/attendance/regularisation    ← submit regularization
```

**`AttendanceGrid` Component:**
- Renders a grid: employees as rows, days 1–31 as columns
- Color-coded cells per status code
- Cell click → edit popup (calls `insert_update_att_reg`)
- Sticky first column (employee name)

---

### 9.6 Leave Module (Phase 3)

**State Machine** (from `leave_transaction_prc` and `LeaveRequests` model):

```
[Applied]
    ↓ (authorizer action)
[AuthorizePending] → [Authorized]
    ↓ (approver action)
[ApprovePending] → [Approved] or [AdminRejected]
    ↓
[Rejected] (any stage)
[CancelPending] → [CancelAuthorize] → [CancelApprove] → [Cancelled]
```

All state transitions go through `CALL leave_transaction_prc(params, @error)`. Do NOT replicate this logic in TypeScript — call the proc.

| Legacy URL | Next.js URL |
|-----------|-------------|
| `/LeaveRequest/index` | `/leave/my-requests` |
| `/LeaveRequest/employeeleaves` | `/leave/team-requests` |
| `/EmployeeLeaves/index` | `/leave/balances` |
| `/LeavePolicy/index` | `/setup/leave-policy` |
| `/LopReports/index` | `/reports/lop` |
| `/LeaveEncashmentRequest/index` | `/leave/encashment` |

**API Routes:**
```
GET  /api/leave/requests             ← my leave requests
POST /api/leave/requests             ← apply for leave (calls leave_transaction_prc)
GET  /api/leave/team-requests        ← pending approvals for this user
POST /api/leave/team-requests/[id]/authorize  ← calls leave_transaction_prc
POST /api/leave/team-requests/[id]/approve    ← calls leave_transaction_prc
POST /api/leave/team-requests/[id]/reject     ← calls leave_transaction_prc
GET  /api/leave/balances             ← leave balances per type
GET  /api/leave/types                ← available leave types
GET  /api/leave/authorizers?action=auth|apr   ← calls leave_auth_apr_person_fn
```

---

### 9.7 Payroll Module (Phase 4 — Highest Priority After Auth)

**Process Flow:**

```
1. Admin opens /payroll/process
2. Selects branch + month
3. Clicks "Process" → POST /api/payroll/process → CALL payroll_master_insert(branch, month, user_id, @error)
4. System creates payroll_master records for all employees in branch
5. For each employee: CALL calculate_salary_main_prc(params, @err) or salary_process_prc(params, @err)
6. Tax: CALL tax_salary_process_prc(params, @err)
7. Holiday/encashment: CALL calculate_holiday_allowances_prc + calculate_leave_encashment_prc
8. View results in payroll list
9. Admin approves → payroll_master.action changes to approved status
10. Payslips generated via emp_salary_slip table
```

| Legacy URL | Next.js URL |
|-----------|-------------|
| `/PayrollProcess/showprocesspayroll` | `/payroll/process` |
| `/PayrollProcess/showapprovepayroll` | `/payroll/approve` |
| `/Payroll/showprocesspayroll` | `/payroll` |
| `/SalaryHeads/index` | `/setup/salary-heads` |
| `/SalaryStructure/index` | `/setup/salary-structure` |
| `/SalaryIncrement/index` | `/payroll/increments` |
| `/Arrear/index` | `/payroll/arrears` |
| `/YearEnd/index` | `/payroll/year-end` |

**`payroll_master` columns (confirmed from controller):**
- `payroll_master_pkey`, `emp_fkey`, `emp_name`
- `days_presant`, `days_leave`, `loss_of_pay`
- `calander_days`, `working_days`
- `monthly_ctc`, `monthly_amount`
- `gross_salary`, `net_salary`, `total_deduction`
- `month_year` (format: `YYYY-MM`)
- `branch_code`
- `action` (null/empty = pending, non-null = processed/approved)

**API Routes:**
```
GET  /api/payroll?branch=X&month=YYYY-MM    ← list payroll_master (pending)
GET  /api/payroll/processed?branch=X&month=YYYY-MM  ← processed payroll
POST /api/payroll/process                   ← calls payroll_master_insert
POST /api/payroll/[id]/approve              ← approve individual payroll
POST /api/payroll/[id]/reverse              ← reverse approved payroll
GET  /api/payroll/features                  ← plan features check
GET  /api/payroll/slip/[id]                 ← get salary slip for emp+month
```

---

### 9.8 Salary Configuration Module (Phase 4)

**Salary Heads structure:**
- `salary_heads` — categories: Earnings (head_operator = 'earning'), Deductions (head_operator = 'deduction'), Employer Contributions
- `salary_head_items` — individual components: Basic, HRA, TA, PF, PT, ESI...
  - `item_type` — e.g. `SALARY`, `LEAVE`, `STATUTORY`
  - `occurance` — leave abbreviation (for leave types)
  - `is_show_salslip` — show on payslip Y/N
  - `item_part` — `Direct` / `Indirect` (Indirect shown as "Admin Only")

**Salary Structure:**
- `salary_structures` — named templates
- `salary_structure_details` — which components, at what rate/formula, per structure
- `emp_salary_structure` — which structure is assigned to which employee

**API Routes:**
```
GET  /api/salary/heads              ← all salary heads
GET  /api/salary/heads/[id]/items   ← items under a head
POST /api/salary/heads              ← create head
GET  /api/salary/structures         ← all structures
POST /api/salary/structures         ← create structure
GET  /api/salary/structures/[id]/breakup?gross=X  ← calls calculate_emp_salary_breakup
GET  /api/salary/employee/[empId]/structure        ← employee's assigned structure
```

---

### 9.9 Reports Module (Phase 5)

Reports are the largest surface area. Most legacy reports are DataTables with server-side pagination + AJAX loading. Next.js replaces these with TanStack Table + API routes.

**Common Report Pattern:**
```typescript
// All reports share this pattern:
// 1. Filter form (branch, month/date range, employee, dept)
// 2. TanStack Table with server-side pagination
// 3. Export buttons: Excel (SheetJS) + PDF (jspdf-autotable)
```

**Key Reports:**

| Report | Legacy URL | Next.js URL | Key Tables |
|--------|-----------|-------------|-----------|
| Salary Reports | `/SalaryReports/index` | `/reports/salary` | `payroll_master`, `emp_salary_slip` |
| Salary Slips | `/SalarySlipReports/index` | `/reports/salary-slips` | `emp_salary_slip` |
| Attendance Report | `/AttendanceReports/index` | `/reports/attendance` | `attendance_register` |
| ESI/EPF Report | `/EsiEpfReport/index` | `/reports/statutory/esi-epf` | `payroll_master` |
| Statutory Registers | `/StatutoryRegisters/index` | `/reports/statutory/registers` | Multiple |
| LOP Report | `/LopReports/index` | `/reports/lop` | `attendance_register` |
| Employee Report | `/Empreport/index` | `/reports/employees` | `emp_details`, `emp_proff` |
| Advance Reports | `/EmployeeAdvanceReports/index` | `/reports/advances` | `employee_advance` |
| Loan Reports | `/EmployeeLoanReports/index` | `/reports/loans` | `employee_loan` |
| Increment Reports | `/EmployeeIncrementReports/index` | `/reports/increments` | `salary_increment` |
| Hierarchy Reports | `/HierarchyReport/index` | `/reports/hierarchy` | `emp_proff` |
| Edited Reports | `/EditedReports/index` | `/reports/audit/edited` | `edit_punches_hist` |
| Variable Reports | `/VariableReport/index` | `/reports/variable-pay` | `emp_calc_variable_components` |
| Arrear Reports | `/ArrearsReports/index` | `/reports/arrears` | `payroll_arrear_master` |

---

### 9.10 Taxation Module (Phase 4)

| Legacy URL | Next.js URL | Key Tables |
|-----------|-------------|-----------|
| `/Taxation/index` | `/taxation` | `tax_heads`, `tax_salary_components` |
| `/TaxHeads/index` | `/setup/tax-heads` | `tax_heads` |
| `/EmployeeTax/index` | `/employees/[id]/tax` | `emp_tax_sal_transactions` |

**Procedure:** `CALL tax_salary_process_prc(...)` handles all tax calculation.

---

### 9.11 Assets, Loans, Advances (Phase 5)

Standard CRUD modules. Lower complexity.

| Module | Tables |
|--------|--------|
| Assets | `assets`, `assets_name`, `equipment_type` |
| Employee Loans | `employee_loan`, `loan_emi` |
| Advances | `employee_advance`, `advance_details` |
| Employee EMI | `emp_emi` |

---

### 9.12 Site & Field Operations (Phase 6)

Site management is used by specific companies (VGFS, VSFS, GEDE). Plan-gated.

| Module | Tables |
|--------|--------|
| Sites | `site`, `site_master` |
| Site Attendance | `site_attendance`, `siteattendanceregister` |
| Site Work | `site_work`, `site_transactions` |
| Field Survey | `efsr_tickets`, `efsr_site` |
| Gate/Out Pass | `gate_pass`, `out_pass` |

---

### 9.13 Procurement / Inventory (Phase 6)

Used by specific companies. Plan-gated.

| Module | Tables |
|--------|--------|
| Purchase Orders | `purchase_order`, `purchase_order_details` |
| GRN | `goods_received_notes`, `gr_item_details` |
| Stock | `stock_details`, `stock_adjustment` |
| Suppliers | `supplier_master` |
| Materials | `material_request`, `material_request_details` |

---

### 9.14 Performance Management (Phase 6)

| Module | Tables |
|--------|--------|
| Performance Reviews | `self_review_details` |
| Team Reviews | see `TeamReview` controller |
| 360 Reviews | `assessment_attributes_*` tables |

---

### 9.15 Mobile / Tracking (Phase 6)

| Module | Tables |
|--------|--------|
| Mobile Tracking | `mobile_user_tracking`, `mobile_user_auditor` |
| Location Updates | `mobile_user_tracking` |

---

## 10. Implementation Phases & Timeline

### Phase 0: Pre-Work (3–5 days)

- [ ] Read complete `mypayrol_trial.sql` to extract ALL table definitions (47,975 lines)
- [ ] Map every CakePHP Model's `$useTable` to its real table name
- [ ] Document all stored procedure parameters by reading their definitions in the SQL
- [ ] Map all active (non-backup) controllers to their corresponding feature
- [ ] Create a test MySQL instance locally with `mypayrol_control_db` and `mypayrol_trial`
- [ ] Verify stored procedures work by calling them from a test script

### Phase 1: Infrastructure (5–7 days)

- [ ] Initialize Next.js 14 project with TypeScript, Tailwind, App Router
- [ ] Install all dependencies
- [ ] Implement `src/lib/db.ts` — dual-pool connection manager
- [ ] Implement `src/lib/auth.ts` — NextAuth with CredentialsProvider + SHA1→bcrypt upgrade
- [ ] Implement `src/middleware.ts` — route protection
- [ ] Implement `src/types/next-auth.d.ts` — session type extensions
- [ ] Create `/login` page with company code + username + password form
- [ ] Create base layout with sidebar and header (stubbed menu items)
- [ ] Create `src/components/data-table/DataTable.tsx` — reusable TanStack Table
- [ ] Set up `.env.local` with local dev credentials
- [ ] Verify: login → session → logout works end-to-end

**Milestone: Can log in and see a dashboard page.**

### Phase 2: Company Setup + Employee Core (8–10 days)

- [ ] Dashboard page with employee count, present today, pending leaves
- [ ] Employee list page (admin: all employees; user_group 2: self only)
- [ ] Employee detail/edit page — all tabs (Personal, Professional, Documents)
- [ ] New employee creation form
- [ ] Organizational CRUD: Branches, Departments, Designations, Grades, Financial Year, Holidays
- [ ] Company profile page (`comp_contact_info`)
- [ ] User access management (which menus per employee)

**Milestone: Can view and manage employees and org structure.**

### Phase 3: Attendance (10–12 days)

- [ ] Attendance register grid (`AttendanceGrid` component — sticky header, color-coded cells)
- [ ] Attendance period calculation via `att_start_end_fn`
- [ ] Dynamic leave type codes from `salary_head_items`
- [ ] Edit attendance entry (calls `insert_update_att_reg`)
- [ ] Attendance upload (CSV/XLSX → bulk update)
- [ ] Device punch view and edit
- [ ] Device log processing (calls `device_logs_iteration_fn` / `bulk_device_logs_iteration_prc`)
- [ ] OT attendance
- [ ] Shift management (`working_day_time_procedures`)
- [ ] Regularization workflow

**Milestone: Can view, edit and process attendance for a month.**

### Phase 4: Leave Management (5–7 days)

- [ ] Leave application form (employee self-service)
- [ ] My leaves list with current status
- [ ] Team leave requests (pending authorizations/approvals)
- [ ] Authorize/approve/reject actions (all call `leave_transaction_prc`)
- [ ] Leave balance display
- [ ] Leave policy configuration
- [ ] Leave encashment request
- [ ] Leave types management (via salary_head_items)

**Milestone: Full leave workflow functional.**

### Phase 5: Payroll Engine (12–15 days)

- [ ] Salary heads configuration (CRUD for `salary_heads`, `salary_head_items`)
- [ ] Salary structure setup (`salary_structures`, `salary_structure_details`)
- [ ] Employee salary structure assignment (`emp_salary_structure`)
- [ ] Salary increment management
- [ ] Process payroll page — select branch + month → calls `payroll_master_insert`
- [ ] Payroll list with pagination (pending vs. processed)
- [ ] Approve/reverse payroll
- [ ] Taxation (calls `tax_salary_process_prc`)
- [ ] Arrears processing
- [ ] Year-end processing
- [ ] Salary slip generation (PDF via jspdf-autotable)
- [ ] Variable pay management

**Milestone: Can process payroll for a month and generate payslips.**

### Phase 6: Reports (8–10 days)

- [ ] Salary reports
- [ ] Salary slip bulk download (multiple employees)
- [ ] Attendance reports
- [ ] ESI/EPF statutory reports
- [ ] Statutory registers
- [ ] LOP report
- [ ] Employee reports
- [ ] Advance/loan/increment reports
- [ ] All reports: Excel export (SheetJS) + PDF export (jspdf-autotable)

**Milestone: All core reports functional with export.**

### Phase 7: Loans, Advances, Assets (5–7 days)

- [ ] Employee loans (create, schedule EMI, track)
- [ ] Salary advances
- [ ] Asset management (assign, track, return)

### Phase 8: Plan-Gated / Company-Specific Modules (10–15 days)

Only if client companies use these:
- [ ] Performance management
- [ ] Site & field operations
- [ ] Procurement / inventory
- [ ] Mobile tracking

### Phase 9: Polish & Cutover (7–10 days)

- [ ] Migrate Slim PHP API v1 endpoints to Next.js API routes
- [ ] End-to-end testing with real data (parallel run with legacy)
- [ ] Performance testing (connection pool under load)
- [ ] UI responsiveness and mobile audit
- [ ] Error boundary and loading state coverage
- [ ] Production environment setup

**Total Estimate: 73–97 working days (≈ 15–20 weeks)**

---

## 11. Key Technical Decisions & Gotchas

### 11.1 Two `user_credentials` Tables

There are TWO `user_credentials` tables:
- In **control DB**: `mypayrol_control_db.user_credentials` — the login table (used for auth)
- In **company DB**: `user_credentials` — employee credentials (used for employee self-service password changes)

Use `controlPool` for login auth. Use `getCompanyPool(companyCode)` for employee password changes.

### 11.2 Attendance Period Boundaries

Never assume attendance = calendar month. Always:
```typescript
const [startRow] = await pool.execute(
  'SELECT att_start_end_fn(?, 1) AS start_date', [yearMonth]
);
const [endRow] = await pool.execute(
  'SELECT att_start_end_fn(?, 2) AS end_date', [yearMonth]
);
```

### 11.3 Company-Specific Feature Overrides

The legacy code has many `if ($company_code == 'GLET' || $company_code == 'ABSG')` branches. Extract these into `src/lib/company-config.ts`:

```typescript
export function getCompanyConfig(companyCode: string) {
  return {
    showHierarchyDashboard: ['GLET', 'ABSG'].includes(companyCode),
    usePayrollPrivilege: true, // all companies
    siteManagement: ['VGFS', 'VSFS', 'GEDE', 'ABSG', 'DEMO', 'GLET'].includes(companyCode),
    incrementNotifications: ['DEMO', 'GLET'].includes(companyCode),
    absHierarchyBranch: ['ABSG', 'GLET', 'GAAR'].includes(companyCode),
    // add as discovered
  };
}
```

Over time, replace hard-coded checks with plan_feature records (the correct long-term solution).

### 11.4 Leave Authorization — Two-Level Hierarchy

Leave has two separate people: **authorizer** (`ISAutherizedby`) and **approver** (`APPROVEDBY`). These are fetched via `leave_auth_apr_person_fn(company_code, emp_fkey, 'auth'|'apr')`. Always call this function — do not try to replicate its logic.

### 11.5 `payro_priv` — Payroll Privilege

An employee with `emp_proff.payro_priv = '1'` (user_group 2) has restricted payroll access — they can only process/view payroll for their own branch. Handle this check in payroll API routes.

### 11.6 SHA1 Password Migration

- Old passwords: 40 hex chars = SHA1 hash
- New passwords: start with `$2b$` = bcrypt
- On login, transparently upgrade SHA1 → bcrypt after successful auth
- Default password: SHA1 of `company_code + 'admin'` or some convention — verify from legacy `SiteController`

### 11.7 `emp_details` vs `employee_info`

Some queries use `emp_details`, others use `employee_info`. Read the schema carefully — they may be different tables with different columns, or `employee_info` may be a view. Verify in `mypayrol_trial.sql`.

### 11.8 Connection Pool Cleanup

The `companyPools` Map persists for the lifetime of the Next.js server process. In development with hot reloading, pools can pile up. Wrap in a `global` object:

```typescript
const globalForPools = global as typeof globalThis & {
  controlPool?: mysql.Pool;
  companyPools?: Map<string, mysql.Pool>;
};
```

### 11.9 Slim API v1 — Keep Running in Parallel

The `legacy/api/v1/` Slim PHP app must remain available during the Next.js migration. It handles mobile app requests and possibly third-party integrations. Do not break it. Migrate its endpoints last (Phase 9).

### 11.10 Report Pagination

Legacy uses jqGrid / custom DataTable jQuery plugin for pagination. Replace with TanStack Table v8 + server-side pagination via API routes. Pattern:

```
GET /api/reports/salary?page=1&limit=50&branch=X&month=YYYY-MM&sort=emp_name&order=asc
```

### 11.11 Plan Feature Gates

Before showing any major feature, check `plan_feature` in control DB:

```typescript
async function isPlanFeatureEnabled(
  planId: number,
  featureKey: string
): Promise<boolean> {
  const [rows] = await controlPool.execute<mysql.RowDataPacket[]>(
    `SELECT pf.is_enabled 
     FROM plan_feature pf
     JOIN features f ON pf.feature_id = f.feature_id
     WHERE pf.plan_id = ? AND f.feature_key = ? AND pf.is_enabled = 1`,
    [planId, featureKey]
  );
  return rows.length > 0;
}
```

---

## 12. Environment Variables

```bash
# .env.local — Local Development

# Control Database
CONTROL_DB_HOST=localhost
CONTROL_DB_USER=root
CONTROL_DB_PASSWORD=root
CONTROL_DB_NAME=mypayrol_control_db

# Company Database host (credentials come from central_control at runtime)
COMPANY_DB_HOST=localhost
# In dev: override with root (per-company DB users may not exist locally)
# COMPANY_DB_USER and COMPANY_DB_PASSWORD are NOT set here for company DBs.
# getCompanyPool() falls back to root in NODE_ENV=development.

# NextAuth
NEXTAUTH_SECRET=dev-secret-change-in-production-minimum-32-chars
NEXTAUTH_URL=http://localhost:3000

# File uploads
UPLOAD_DIR=./public/uploads

# Node
NODE_ENV=development
```

```bash
# .env.production (example)

CONTROL_DB_HOST=127.0.0.1
CONTROL_DB_USER=mpm_cntrl_usr
CONTROL_DB_PASSWORD=MyPyR01@Cntr1#LB
CONTROL_DB_NAME=mypayrol_control_db

COMPANY_DB_HOST=127.0.0.1
# Company DB credentials come from central_control at runtime — no static env var

NEXTAUTH_SECRET=<strong-random-secret-min-32-chars>
NEXTAUTH_URL=https://app.rizo.in

UPLOAD_DIR=/var/www/rizo/uploads
NODE_ENV=production
```

---

## 13. Non-Goals

The following are explicitly OUT of scope for this migration:

1. **PostgreSQL migration** — MySQL stays as-is. Separate future project.
2. **Rewriting stored procedures** — All existing MySQL stored procedures and functions are called as-is from Next.js API routes. No reimplementation.
3. **Consolidating per-company databases** — Each company keeps its own database. No schema changes.
4. **Removing company-specific hardcoded logic** — Moved to `company-config.ts` helper, but the underlying data still comes from the database. Full config-driven approach is a future cleanup.
5. **Migrating backup controller files** — The ~40 backup files (`*_bkup.php`, `*_Nimisha*.php` etc.) are ignored. Only the current active logic is migrated.
6. **New features** — This is a like-for-like migration. No new HR features during migration.
7. **Real-time features** — No WebSockets for real-time attendance or notifications (future enhancement).
8. **Multi-language / i18n** — Not in scope unless specifically requested.

---

*This plan supersedes all previous versions. Last updated: June 2026.*
