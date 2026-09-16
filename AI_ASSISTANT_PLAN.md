# AI Assistant (Natural-Language HR Query) — Build Plan

Decisions locked in with user (2026-08-06):
- **Data access:** curated tool-calling only (no text-to-SQL) — the LLM can only call a fixed, whitelisted set of business functions.
- **LLM provider:** Google Gemini (free tier — `gemini-2.0-flash` or `gemini-2.5-flash`), via `GEMINI_API_KEY`.
- **Phase 1 scope:** Employee & HR lookups (headcount, profile, leave balance, attendance status, new joiners, org breakdown). Payroll/statutory reports are Phase 2.

## 1. Architecture (mapped onto this codebase)

```
User (chat UI)
   |
   v
/assistant page (client)  --POST-->  /api/assistant/chat (route handler)
                                            |
                                            v
                              Gemini function-calling loop
                                 (lib/assistant/gemini.ts)
                                            |
                              picks a tool from the registry
                                            v
                              lib/assistant/tools.ts
                        (fixed, parameterized functions —
                         same style as reports.ts / payroll.ts)
                                            |
                                            v
                              getCompanyPool(session.user.companyCode)
                                            |
                                            v
                                  Company MySQL DB (tenant)
```

This is exactly the diagram you sketched — "GLOS Business APIs" = `lib/assistant/tools.ts`, each tool wrapping the *same* query/permission logic the rest of the app already uses (e.g. `emp_details`, `leaveentries`, `present_today` — the same tables `dashboard/stats/route.ts` already queries). The LLM never sees a raw SQL surface; it only sees named functions with typed arguments, same trust boundary as any other internal API.

## 2. New pieces to build

### 2.1 Sidebar menu entry
`src/components/layout/Sidebar.tsx` — add a top-level item (not nested), e.g.:
```ts
{ label: 'Ask RIZO', href: '/assistant', icon: Sparkles }
```
Not `adminOnly` — both admins and regular employees can use it, but **tool results are scoped by role** (see 2.4).

### 2.2 Page
`src/app/(dashboard)/assistant/page.tsx` — client component:
- Simple chat UI: scrollable message list, text input, send button, loading indicator.
- Each assistant message optionally shows a small "Queried: <tool name>" chip for transparency (builds trust that answers are grounded, not hallucinated).
- Client-side conversation state only (no need to persist across reloads for v1); POSTs full running message history each turn (Gemini is stateless per-request like other LLM APIs).

### 2.3 API route
`src/app/api/assistant/chat/route.ts`:
- `getServerSession(authOptions)` — 401 if missing, same as every other route.
- Load `session.user.companyCode`, `session.user.userGroup`, `session.user.loginUserId`.
- Simple per-session rate limit (protect the free Gemini quota) — in-memory counter keyed by session, generous but non-zero cap (e.g. 30 msgs/hour).
- Run the Gemini function-calling loop (2.5), execute whichever tool(s) it requests, feed results back, return final text.
- Write one row to an audit log table per turn (2.6) — question, tool(s) called + args, final answer, user, timestamp.

### 2.4 Tool registry — `src/lib/assistant/tools.ts`
Each tool = `{ name, description, parameters (JSON schema), execute(args, ctx) }`, where `ctx = { pool, userGroup, loginUserId, companyCode }`. Phase 1 set:

| Tool | Answers | Notes |
|---|---|---|
| `getEmployeeCount` | "How many employees do we have / in Branch X / Dept Y" | filters: branch, department, designation, status |
| `getEmployeeProfile` | "Show me John Doe's details" | name/emp-code lookup; **non-admin can only resolve their own record** |
| `getLeaveBalance` | "What's my leave balance" / "Priya's leave balance" | same self-only restriction for non-admins |
| `getPendingLeaveRequests` | "Any pending leave requests to approve" | admin/approver only (reuses the same `ISAutherizedby`/`APPROVEDBY` logic already in `dashboard/stats/route.ts`) |
| `getAttendanceStatus` | "Is X present today" / "Who's absent today" | reuses `present_today` view |
| `getNewJoiners` | "Who joined this month/quarter" | date-range filter |
| `getHeadcountBreakdown` | "Headcount by branch/department" | grouped count, admin only |
| `getUpcomingEvents` | "Any birthdays/anniversaries this week" | nice-to-have, low risk |

Every tool does its own row-level scoping — **the LLM cannot bypass this by phrasing the question differently**, because it can only ever call these fixed functions with these fixed arguments; there is no free-text SQL path.

### 2.5 Gemini integration — `src/lib/assistant/gemini.ts`
- New dependency: `@google/generative-ai` (official Node SDK, supports function calling / `functionDeclarations`).
- `GEMINI_API_KEY` env var (free key from Google AI Studio).
- System prompt constrains the assistant to: HR/payroll data questions about *this* company only; must call a tool for any factual claim (never invent numbers); politely decline anything outside scope (payroll numbers in Phase 1, or any other tenant's data).
- Standard function-calling loop: send messages + tool schemas → if response has a `functionCall`, execute the matching tool from the registry → send a `functionResponse` back → repeat (cap at e.g. 4 hops to avoid loops) → return final text part.

### 2.6 Audit log
New table (tenant DB, alongside the other per-company tables), e.g. `ai_assistant_log`:
`id, login_user_id, question, tools_called (JSON), answer, created_at`.
Verify no existing table already covers this before adding — check `mypayrol_trial.sql` for a naming collision first, per this project's standing discipline.

## 3. Guardrails (explicit, since this is new attack surface)

- **No SQL tool.** The model only ever gets typed function calls; arguments are schema-validated before hitting any query.
- **Role scoping lives in the tool implementation, not the prompt.** A non-admin asking "what's Priya's salary" gets refused by the tool layer (empty/forbidden result), not by hoping the LLM declines.
- **Payroll/salary data is out of scope for Phase 1 entirely** — no tool exists to fetch it, so the model structurally cannot answer those questions yet, regardless of prompt injection attempts.
- **Rate limit** per user session to stay inside Gemini's free tier and avoid one user exhausting the shared quota.
- **Audit every turn** (2.6) so any answer can be traced back to the exact tool calls and DB rows behind it.

## 4. Phased rollout

- **Phase A (this plan):** Employee/HR lookups above, chat UI, audit log, role scoping. Est. 2–3 days.
- **Phase B (future):** Payroll & statutory reports as tools wrapping the existing `reports.ts`/`statutoryReports.ts` engine (e.g. one `runReport(reportType, criteria)` tool) — admin-only, given sensitivity already established elsewhere in the app (payroll menu is `adminOnly`).
- **Phase C (future):** Deeper attendance/leave analytics, natural-language-triggered report export (reuse `reportExport.ts`).
- **Phase D (optional, only if Phase A/B prove tool coverage is too narrow):** a tightly constrained read-only SQL fallback (fixed allow-listed views, forced `LIMIT`, no joins beyond a pre-approved set) — deliberately deferred, not built by default, since it reopens the exact SQL-injection-shaped risk this project already fixed once in the Reports engine (see `reports.ts` gap-closure notes).

## 5. Open questions for you before I start building

1. Should "Ask RIZO" be visible to all logged-in users (self-service: "what's my leave balance") or admin-only for v1?
2. OK to add a new `ai_assistant_log` table to the tenant schema, or would you rather log to the control DB / a flat file for v1?
