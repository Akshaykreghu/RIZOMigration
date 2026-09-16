# Process Payroll — Legacy vs Next.js Migration Status

**Menu:** Payroll → Process Payroll
**Legacy:** `https://in.mypayrollmaster.online/Dashboard` → Payroll → Salary Processing (labelled "Process Payroll" on the page itself)
**Next.js:** `https://dev.rizo.one/payroll/process`
**Date:** 2026-08-11
**Test data used:** April 2026, GREATLEAP branch (plus Head Office and Kochi branches for cross-checking), tenant GRTL / Great Leap Tech

**Method:** three-step comparison — (1) operate legacy's own Process Payroll screen directly and document its working and design, (2) operate Next.js's own Process Payroll screen directly the same way, (3) compare the two and write up migration status and next steps. All testing was read-only / non-destructive: no real payroll run was triggered (no click on legacy's "Process" checkmark or Next.js's "Process"/"Hold" buttons), since both systems hold live company data for Great Leap Tech.

---

## Part 1 — Legacy: working and design

### Layout

The page has two filters at the top — **Branch** (searchable dropdown, e.g. "ABS GROUP HEAD OFFICE", "Alappuzha", "GREATLEAP" — no "all branches" option) and **Month** (searchable dropdown, e.g. "Apr-2026") — plus a **List** button that loads data for the selected combination. Below that are two tabs: **Not Processed** and **Processed**.

### Not Processed tab

Shows every employee in the selected branch who hasn't had that month's payroll run yet, with columns: checkbox, Employee name, Calendar Days, Working Days, Days present, Days on leave, Loss of pay, Net Salary (always 0 here — Net Salary is not computed until processing). A red-text legend note states *"Employees shown in red letters are separated"* (i.e. exited/terminated employees are still listed but visually flagged). Controls above the table: a green **Process** action (checkmark icon), an **Include Tax** Yes/No dropdown, and a **Select Employee** filter (searchable, lists the full employee roster by name + employee code, not just the visible rows — lets an admin isolate one person before processing). Standard pagination (page size selector, first/prev/next/last, page-of-total counter) is present.

### Processed tab

Shows employees whose payroll for that branch/month has already been run, with a materially different column set: **View Slip** (link), Employee name, Working Days, Loss of pay, Monthly CTC, Gross Salary, Net Salary, Previous Salary, Difference. A red **Remove** action (reverses/un-processes) replaces the green Process action. Confirmed live on **GREATLEAP / April-2026**: 38 processed employees, e.g. Akshay (CTC 31,351 / Gross 30,000 / Net 28,666), Arun (CTC 308,683 / Gross 300,000 / Net 221,499), with **Previous Salary** and **Difference** columns populated for employees who've had a prior processed month to compare against (e.g. June12: previous 165,556 → net 499,679, diff +334,123).

### View Slip (payslip design)

Clicking **View Slip** opens a modal with a fully itemized breakdown, grouped into named sections in this order: **Monthly Salary Components** (Item / Salary rate / Salary amount — Basic, Conveyance Allowance, Medical Allowance, LTA, HRA, Special Allowance ×2, Service Weightage, Outstation Allowance, Travelling/Conveyance Allowance, Consolidated Pay, Personal Pay, VDA, then a **Gross Salary** subtotal), **Statutory Deductions** (LWF/EPF/WWF – Employee Contribution), **Variable Deductions from Salary** (e.g. Professional Tax), a **Net Pay** total, and finally an **Indirect** section (JSNL, BSNL, EPF/WWF/LWF – Employer Contributions) — i.e. legacy explicitly separates employer-cost items into their own "Indirect" section, never folding them into Net Pay.

### Confirmed working

Legacy's Process Payroll is fully functional and holds real, live data: GREATLEAP/April-2026 alone has 38 already-processed employees (visible in the Processed tab with correct-looking CTC/Gross/Net/Previous/Difference figures) plus 11 not-yet-processed. ABS GROUP HEAD OFFICE, Alappuzha, and CYFB branches had no April-2026 records in either tab (payroll for those branches/months hadn't been run), which is expected/normal rather than a defect.

---

## Part 2 — Next.js: working and design

### Layout

Filters: **Branch** (native `<select>`, options: ALUVA (Demo Branch), Banglore, Branch XYZ, EKM, GREATLEAP, Head Office, Kakkanad, Kochi, NADAKKAVU) and **Month** (native month-picker). A **Seed from Attendance** button sits beside them — this is Next.js's equivalent of legacy's automatic "Not Processed" population, except here it's an explicit, user-triggered action rather than automatic. Below that: two tabs, **Pending** and **On Hold** (legacy's equivalent naming is "Not Processed" / a held-back state that doesn't have a direct legacy counterpart — legacy has no "Hold" concept, only Not Processed → Processed).

### Pending tab

Controls: an **Include TDS in this run** checkbox (maps to legacy's Include Tax dropdown, but as a checkbox rather than Yes/No selector), a green **Process** button, and a yellow **Hold** button (no legacy equivalent — lets an admin set a payroll run aside without processing or discarding it). Table columns: checkbox, Employee, Present, LOP, Gross, Deductions, Net, Status, Slip — narrower and more consolidated than legacy's Not-Processed columns (no separate Calendar Days / Days on leave columns; LOP and Gross/Deductions/Net are shown even pre-processing, which legacy doesn't do).

### On Hold tab

Same column layout, with **Send back to Pending** replacing the Hold button (a clear, reversible action — a genuine improvement over legacy, which has no equivalent staging state at all).

### Confirmed NOT working

This is the core finding: **the Pending/On Hold lists never populate, for any branch or month combination tested.** Specifically:

- GREATLEAP / April-2026 — clicked "Seed from Attendance" twice; both times the page displayed the success message *"Payroll drafts seeded from attendance"*, and both times the table still read **"No records. Try seeding from attendance."**
- Head Office / April-2026 — same result (no records, before even attempting to seed).
- Kochi / April-2026 — same seed-reports-success-but-zero-rows pattern, reproduced again.
- Kochi / **August-2026** (the current month in this environment) — same pattern once more, ruling out "this is just an old month with no data" as the explanation.

Network inspection confirms this isn't a silent client-side failure: both the seed call (`POST /api/payroll`) and the subsequent list fetch (`GET /api/payroll?branch=...&month=...&status=pending`) returned **HTTP 200** every time — the backend is accepting the request and responding successfully, it's just returning zero records. No JavaScript errors appeared in the browser console during any of these attempts.

Following the dependency chain one level further: Attendance → Register (`/attendance/register`) for GREATLEAP/April-2026 shows **"No records for this month/branch. Try Process first"** on both its "Not Verified" and "Verified" tabs — meaning the underlying monthly attendance register itself hasn't been generated for that branch/month in this environment, which would fully explain why Payroll's seed step finds nothing to seed *for that specific case*. However, this doesn't explain the Kochi/August-2026 (current month) result, where attendance activity would normally be expected to exist — that case points more toward either a genuine data gap in this dev environment or a bug in how the seed endpoint matches attendance records to a branch/month (e.g. a branch-ID or date-format mismatch between the Attendance and Payroll modules).

---

## Part 3 — Migration status and next steps

### Status: not yet functional, structurally close

Next.js's Process Payroll page is well-designed and structurally close to (in some respects, ahead of) legacy — it adds a genuinely useful "Hold" staging state legacy lacks, and its Slip/Status columns show more information up front. But it cannot currently do the one thing this menu exists to do: **produce a working list of employees whose payroll is ready to run.** Every test — 3 branches, 2 months including the current one — reproduced the same failure. This is a P0 blocker: no payroll can be processed through this screen in its current state, for any branch or month.

### What legacy can do that Next.js cannot (yet)

1. **Populate a Not Processed / Pending list reliably.** Legacy's list appears automatically per branch/month with no extra action needed; Next.js requires an explicit "Seed from Attendance" step that currently produces zero rows regardless of input.
2. **Show a real, itemized payslip via View Slip**, with Gross Salary, category-grouped components, Net Pay, and a separate "Indirect" (employer-cost) section — not yet testable in Next.js since no processed records exist to view a slip for.
3. **Show Previous Salary / Difference columns** for month-over-month comparison on already-processed payroll — also not yet verifiable in Next.js for the same reason.
4. **Filter down to a single employee** before processing (legacy's Select Employee dropdown) — no equivalent control was found on the Next.js Pending/On Hold tabs.

### What Next.js already does better (worth preserving)

1. **Explicit Hold / Send-back-to-Pending staging state** — lets an admin park a payroll run mid-review without processing or losing it. Legacy has no equivalent; an admin there can only process or leave unprocessed.
2. **More informative Pending table** — shows Gross/Deductions/Net/Status columns before processing, where legacy's Not-Processed tab only shows attendance-derived figures (Calendar Days, Working Days, Days present/leave, Loss of pay) and leaves Net Salary at 0 until after processing.
3. **Cleaner action set** — Process, Hold, and (from On Hold) Send back to Pending are three clear, named actions versus legacy's single checkmark-icon Process action plus a separate Remove action once already processed.

### Recommended steps to complete the migration for this menu

1. **Root-cause the seed failure first — this blocks everything else.** Instrument or log what `POST /api/payroll` (seed) actually does when it returns 200 with zero effect: confirm whether it's (a) correctly finding no attendance data to seed from because Attendance Register genuinely hasn't been generated/processed for that branch/month in this environment, or (b) a matching bug between Attendance's stored branch/month keys and what Payroll's seed query looks up. The Kochi/current-month (August 2026) test result — same empty outcome — argues for ruling out "just old test data" and treating this as reproducible across the board.
2. **Once seeding works, verify against GREATLEAP/April-2026 specifically**, since that's the one branch/month combination confirmed to have 38 real processed + 11 not-processed employees in legacy — it's the best available ground truth to check the seeded Pending list's figures (Present, LOP, Gross, Deductions, Net) against legacy's Not Processed tab for the same employees before trusting the numbers.
3. **Build out the View Slip modal** to match legacy's structure: category-grouped Monthly Salary Components → Gross Salary → Statutory Deductions → Variable Deductions → Net Pay → a separate Indirect/employer-cost section. Given this project's earlier Salary Structure testing confirmed Next.js's underlying formula engine and Net-exclusion-of-employer-contributions logic are now correct (see `Salary_Structure_Formula_Deep_Dive.md`), the computation groundwork already exists — this is primarily a matter of wiring the Slip UI to reuse it in the same category structure as legacy.
4. **Add Previous Salary / Difference columns to the Pending or a Processed-equivalent view**, once there's processed data to compare against — this is a valuable month-over-month sanity check legacy admins currently rely on and shouldn't be dropped in migration.
5. **Add a single-employee filter** to the Pending/On Hold tabs, matching legacy's Select Employee dropdown, so an admin can process or review one person without acting on the whole branch.
6. **Re-run this entire three-step comparison once the seed bug is fixed**, using GREATLEAP/April-2026 as the primary test case, and specifically verify: the Pending list's employee count and figures match legacy's Not Processed count for the same period; a full Process run against a small subset (not the whole real branch) produces a Slip and Processed-equivalent record whose Gross/Net match hand-calculated expectations; and the Hold / Send-back-to-Pending states behave correctly as a reversible staging step.

### Notes on testing method

This pass was read-only with respect to real payroll state: no "Process" or "Hold" action was clicked in either system, since both hold live Great Leap Tech company data and processing is not easily reversible (legacy's Remove action exists but wasn't tested for the same reason). Legacy's data was inspected across ABS GROUP HEAD OFFICE, Alappuzha, CYFB, and GREATLEAP branches before finding GREATLEAP/April-2026 as the branch/month with real processed data, per the user's steer that April 2026 has data in different branches. Next.js's seed/list behavior was tested across GREATLEAP, Head Office, and Kochi branches, and April-2026 plus August-2026 (current month) to distinguish a stale-data explanation from a systemic one. Network requests (`read_network_requests`) and console output (`read_console_messages`) were inspected during the Next.js seed attempts to confirm the failure is happening server-side/silently rather than as a visible client error.
