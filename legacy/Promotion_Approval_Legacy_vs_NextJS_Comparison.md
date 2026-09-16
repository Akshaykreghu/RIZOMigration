# Promotion Approval — Legacy vs. Next.js Comparison

**Legacy:** `Controller/PromoController.php` (810 lines) + `promotion.ctp` / `promotionjoin.ctp` / `approvepromotion.ctp`
**Next.js:** `/employees/promotions`, `src/app/api/promotions/route.ts`, `src/app/api/promotions/[id]/route.ts`
**Date:** 2026-09-02
**Method:** source-code comparison (no promotion was drafted or approved on the legacy side).

---

## Headline

**Close port of legacy's single-step approval model.** A promotion is a request row in `promotions`
(`approved_status = 'N'` initially); approving it cascades the requested changes onto the live
employee (`emp_proff` fields + `emp_config`-audited policy assignments + a `sal_structure_distribution_fn`
salary-structure change + a CTC row + a reporting-manager change). Next.js already covered the
cascade faithfully; this pass closed the smaller gaps below.

## Scope comparison

| Capability | Legacy | Next.js | Status |
|---|---|---|---|
| Request a promotion (designation / dept / branch / type / shift / leave / salary structure / annual CTC / reporting manager / remarks) | `savepromotions()` | `POST /api/promotions` | **Ported** |
| Approve → cascade onto employee | `approvesave()` + `changeDesignation()` / `changeDepartment()` / `changeBranch()` / `chnageType()` / `addToShift()` / `addToLeave()` / `addToSalary()` / `addToSuperior()` / `promotionWage()` | `PUT /api/promotions/[id]` (`action: 'approve'`) | **Ported** |
| Reject a request | `rejsave()` | `PUT /api/promotions/[id]` (`action: 'reject'`) | **Ported** |
| `emp_config` audit rows for `DESIG` / `DEPARTMENTS` / `BRANCH` (policy_id = numeric `id`, prior rows deactivated) | yes | **added this pass** | **Ported** |
| `TYPE` audit row | commented out in `chnageType()` | not written (matches) | **Match** |
| CTC `start_date_effective` = 1st of current month | `promotionWage()` `date("Y-m-1")` | **fixed this pass** (`YYYY-MM-01`) | **Ported** |
| Edit a still-pending request | `savepromotions()` re-saves by pkey | `PUT /api/promotions` (guarded to `approved_status = 'N'`) — **added this pass** | **Ported** |
| View a processed request's detail | `approvepromotion()` renders full detail | View modal on `/employees/promotions` — **added this pass** | **Ported** |
| Pending list scoped to the logged-in approving manager (`approved_by = emp_fkey`, `promotion_status = 'APPLIED'`) | `listemployees()` | **not replicated** — admin-central (`approved_status = 'N'`) | **Deliberate** — this app has no manager self-service login (same decision as Remove Employee / Regularisation) |
| Approver edits the requested values on the approval screen before approving | `approvepromotion.ctp` re-renders every dropdown | not replicated — approve is yes/no on the submitted values (Edit covers pre-approval changes) | **Reduced, by design** |
| Employee config history panel on the request form (`emp_config_history`) | `promotion()` | not shown | **Gap (minor)** — informational only |
| Delete a request | none in legacy | none | **Match** |

## What this means for the migration

1. The approval **cascade** — the part that actually mutates employee records — is a faithful port,
   now including the designation/department/branch `emp_config` audit rows legacy writes.
2. The **manager-routing** of the pending queue is intentionally flattened to a single admin
   approver, consistent with every other approval workflow in this port.
3. Remaining differences (no in-approval edit of values, no config-history panel) are cosmetic /
   informational and were left out deliberately.
