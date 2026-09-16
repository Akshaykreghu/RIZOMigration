# Legacy DB vs. Senior's Database — Reader-Friendly Summary

*A plain-language walkthrough of the major differences. Full object-by-object detail (every one
of the 309 tables and 135 procedures/functions/triggers, individually) is in
[`Legacy_vs_Senior_DB_Object_By_Object_Diff.md`](Legacy_vs_Senior_DB_Object_By_Object_Diff.md) —
this document highlights what actually matters from that reference.*

---

## The headline numbers

Out of everything in the legacy database:

- **Tables:** about 1 in 5 (62 of 309) were simply renamed with the same structure. Another 48
  were restructured (same idea, different shape). 16 kept their exact name. But **108 tables — over
  a third — have no trace at all** in the senior's system, and 75 more were deliberately left out
  (inventory, staging tables, backups — things nobody would want to carry forward anyway).
- **Procedures, functions, and triggers:** only 15 were faithfully rebuilt in JavaScript with
  working code to point to. 13 more were rebuilt but with pieces missing or simplified. 3 exist
  only as read-only reports, not as logic that actually feeds payroll. **67 — about half — have no
  trace in the code at all**, beyond being mentioned in a planning document. 39 were correctly and
  deliberately dropped because they made no sense in the new architecture (e.g. procedures for
  provisioning a separate database per company, which the new single-database design doesn't need).

So roughly a third of the data model and about half of the business logic simply isn't there yet —
either genuinely missing, or present only as a plan on paper.

---

## Module-by-module: what changed, what's missing

### Employee records — mostly intact, some gaps

The core employee record survived well: `emp_details` and the job-info table `emp_proff` were
merged into a single `employees` table (typos like `middile_name` and `maritual_status` got fixed
along the way). Family, education, documents, promotions, loans, and advances all made it over
with sensible new names.

What didn't make it: the generic "assign a policy to an employee" mechanism (`emp_config`), any
kind of reporting-hierarchy/org-chart table, dashboards-as-persisted-config, and the old audit-log
tables. These read like reasonable things to drop or rebuild differently, not accidents.

### Attendance — the biggest structural rewrite, and it's a genuine improvement

Legacy stored one row per employee *per month*, with 31 separate columns (`FIELD1` through
`FIELD31`) holding each day's status — an awkward, hard-to-query design. The new system correctly
replaces this with one row per employee *per day*. This is the single most consequential schema
change in the whole comparison, and it's a real upgrade, not a loss.

That said, the **entire site/contract-labour attendance module — 10+ tables, 6+ procedures, 2
triggers — has no trace anywhere** in the new system. It isn't on their own "things we're
deliberately skipping" list either; it just isn't there. If your business has contract/site labour
tracked separately from regular employees, that whole workflow would need to be rebuilt from
scratch.

Also missing: the underlying logic that actually turns raw biometric punches into attendance
records (shift-window clamping, night-shift date attribution, half-day thresholds). The
*tables* that would hold the result exist, but the calculation logic that fills them in wasn't
found.

### Payroll — this is where the real risk is

The core payroll tables carried over: `payroll_master` kept its exact name, salary structures and
their formula-driven components are intact, and the main salary-breakup calculation
(`calculate_emp_salary_breakup`) was faithfully reimplemented in JavaScript.

But the **payroll processing orchestrator itself is incomplete**. Direct code inspection (not
guesswork) confirms these are simply not part of the payroll run:
- Holiday allowance calculation
- Shift allowance calculation
- Overtime allowance calculation
- Leave encashment payout
- Loan/advance EMI auto-deduction

The tables for loans and advances exist as standalone features — you can create and track them —
but nothing automatically deducts an EMI from an employee's monthly salary. That connection is
missing.

### Tax & statutory (PF/ESI/PT/TDS) — reports, not real calculations

Income tax (TDS) came over well — the slab math, old-vs-new regime comparison, rebates, and HRA
exemption logic are genuinely reimplemented and reasonably complete.

PF, ESI, and Professional Tax are a different story. Legacy computed these independently, with
proper rules (contribution ceilings, formula vs. fixed modes) and *wrote the results into the
payslip* as part of processing payroll. The new system instead **scans the already-generated
payslip for lines that look like PF or ESI deductions and reports on those** — there's no
independent calculation engine enforcing the actual PF/ESI rules. In other words, these numbers
are only as correct as whatever the (already-incomplete) payroll run happened to produce.

### Leave management — better than initially thought, still simplified

One correction worth flagging: earlier research assumed year-end leave carry-forward was entirely
missing. It isn't — it exists as a manual, one-company-at-a-time button (`runYearEnd()`), not
legacy's automatic nightly process across every company. It also uses one simple carry-forward
limit instead of legacy's six different cycle types (yearly/monthly/quarterly/half-yearly/
project/daily), each with its own rollover math.

Leave requests themselves got a genuine improvement — a day-by-day breakdown table was added,
where legacy only tracked a date range with half-day flags at the endpoints. But the detailed
rules engine (minimum/maximum days, notice period requirements, blackout-day restrictions) wasn't
found — the new system checks balance sufficiency but not the fuller rulebook.

### Assets — kept; general inventory — dropped

Interesting scope split: asset tracking (laptops, equipment assigned to employees) was kept with
its table names completely unchanged. General inventory/procurement (stock, purchase orders,
goods-received notes, material requests — about 25 tables) was dropped entirely. Both are
"physical things a company tracks," but only one was judged in scope for an HR system.

### Platform/control-DB concepts — architecturally can't carry over, and that's expected

Legacy ran on physical database-per-company isolation, with a central "control" database that knew
how to find and log into each tenant's database. The new system uses one shared database with a
`company_id` column instead — a completely different tenancy model. Everything tied to the old
model (auto-provisioning a new database for each signup, cross-database SSO, per-tenant billing
plan tables) is gone by design, not by omission. This is the one area where "missing" isn't really
a gap — it's a deliberate, sensible consequence of the architecture choice.

---

## A pattern worth knowing about: the plan and the code don't always agree

In several places, the senior's own planning document describes one design, but the actual running
code does something else:

- The plan called the new payslip-line table `payroll_entries`; the code calls it
  `payroll_slip_lines`.
- The plan proposed splitting attendance into two tables (`attendance_monthly_summary` +
  `attendance_daily_records`); the code uses `attendance` + `attendance_audit` instead.
- The plan kept employee personal data and job data in two linked tables; the code merged them
  into one `employees` table.

None of these are wrong, exactly — the underlying design intent usually still comes through — but
it means the planning documents can't be trusted as an accurate description of what's actually
built. Every finding in the full report was checked against real code for this reason, not just
against the docs.

---

## Bottom line

The parts of the system that are simpler and more self-contained — the core employee record, the
attendance date model, income tax calculation, salary structure formulas — came over faithfully or
were genuinely improved. The parts that involve multiple moving pieces working together — full
payroll processing (which needs allowances, encashment, and loan deductions all to fire correctly),
real statutory calculation (which needs to feed *into* payroll, not just read from it after the
fact), and the site/contract-labour module — have real, verifiable gaps.
