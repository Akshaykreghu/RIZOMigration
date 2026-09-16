# Generate Employee Documents — Legacy vs. Next.js Comparison

**Legacy:** `Controller/DocumentManagerController.php` (2,237 lines) — HTML-merge templates
(`doc_template` / `documents`), image-certificate designer (`templates` / `templates_details`),
birthday-wish email (`sendemailtemplate()`).
**Next.js:** `/employees/generate-documents`, `src/app/api/document-templates/**`,
`src/app/api/employees/[id]/generate-document/route.ts`, `.../generate-image-document/route.ts`,
`.../birthday-wish/route.ts`, `src/lib/documentMerge.ts`, `src/lib/imageTemplate.ts`, `src/lib/mailer.ts`
**Date:** 2026-09-02

---

## Headline

The HTML template/merge engine was already ported. This pass added the two previously-deferred
legacy features — the **image-certificate designer** and the **birthday-wish email** — re-implemented
without legacy's external rendering microservice.

## Scope comparison

| Feature | Legacy | Next.js | Status |
|---|---|---|---|
| HTML templates with `{:token}` merge fields → per-employee document, saved to `documents` | `getPlaceholder()` / `saveDocument()` / `doc_template` | `document-templates` routes + `generate-document` + `documentMerge.ts` | **Ported (earlier)** |
| PDF output | Dompdf | browser `window.print()` on a preview window | **Ported (earlier)**, different mechanism |
| **Image-certificate template** — position a text block + an avatar photo over a background image; `is_default` per `type` | `template()` / `save_template()` / `savedefault_template()`; tables `templates` + `templates_details` | `document-templates/image` CRUD routes + "Image Templates" tab (drag editor) | **Ported this pass** |
| Per-employee certificate render | `renderImageTempalte()` → **external microservice** `v1.mypayrollmaster.online/api/v2qa/templateRender` (earlier: hcti.io) | `generate-image-document` route resolves text/photo; client draws background + photo + text to a `<canvas>` → PNG, and "Add to PDF" via `jsPDF` | **Ported this pass**, rendered locally — no external service |
| Merge tokens in the image text | `str_replace('{{first_name}}' / '{{last_name}}')` | `resolveTemplateText()` — keeps `{{first_name}}` / `{{last_name}}` **and** exposes the full `buildMergeTokens` set (`{{empolyee_name}}`, `{{employee_designation}}`, `{{current_date}}`, …) | **Superset** |
| **Birthday-wish email** — default `type='birthday'` template, rendered HTML mailed to the employee | `sendemailtemplate($emp_fkey, 'birthday')` via PHPMailer/SMTP | `birthday-wish` route → `src/lib/mailer.ts` (nodemailer, SMTP_* env vars; logged no-op when unset) | **Ported this pass** |
| Scheduler that triggers birthday emails automatically | not in this codebase (`convertimage()`'s cron path is external) | not built — manual "Send birthday wish" action only | **Gap (by design)** — no scheduler infra in this port |
| Image-composite certificate microservice (`renderImageTempalte` server render) | external | replaced by local canvas render | **Replaced** |

## Deployment notes

- `templates` / `templates_details` are **not in this repo's schema dumps**. Tenants migrated from
  legacy may already have them (with data); fresh tenants don't. Run
  `node scripts/create-image-templates.mjs <companyCode>` once per tenant DB — it's
  `CREATE TABLE IF NOT EXISTS`, so it never touches an existing table.
- Birthday email sends only when `SMTP_HOST` + `SMTP_FROM` (and optionally `SMTP_USER` / `SMTP_PASS`
  / `SMTP_PORT` / `SMTP_SECURE`) are set — otherwise every send is a logged no-op returning
  `skipped-no-smtp`, same optional-infra pattern as `src/lib/storage.ts` (DigitalOcean Spaces).

## Verification (2026-09-02, real GRTL data)

Ran the schema script twice (idempotent — second run reports "already exists"). Created an image
template, edited it (geometry + a new `templates_details` row appended, latest read back), toggled
`is_default` for `type='birthday'` (only one default per type). `generate-image-document` for real
employee 104 merged "Well done Aarav Sharma" and returned the positioned HTML. `birthday-wish`
returned `no-email` for an employee with no address and `skipped-no-smtp` (nothing sent) for one
with an address while SMTP was unset. All test rows deleted afterward; admin password override
restored and confirmed.
