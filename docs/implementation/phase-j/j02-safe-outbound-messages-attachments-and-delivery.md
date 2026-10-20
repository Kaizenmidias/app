# J02 — Safe outbound messages attachments and delivery

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **J02** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [06-crm-modules](../../architecture/06-crm-modules.md), [16-provider-decisions](../../architecture/16-provider-decisions.md), [04-auth-and-security](../../architecture/04-auth-and-security.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Add selected-provider sandbox sends with eligibility and uncertain-outcome handling.

### Required context and dependencies

Complete and independently verify **J01, C03** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

SendMessage action/job/provider adapter; Message/attachment states; chat controls.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

durable outbound operation/idempotency fields and private attachments; use existing message records.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Recheck owner/account/channel consent, provider templates/window, suppression, quota and attachment types immediately before send. Mark unknown after ambiguous timeout, reconcile don't blind retry. Status monotonic by provider rules.

### Frontend scope

Pending/sending/sent/delivered/failed/unknown distinctions; accessible retry only safe cases.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Fake provider window/template denial, opt-out after queueing, concurrent click, timeout after accept, status order, attachment authorization.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

No duplicate outbound effect on retry, no messages outside allowed consent/provider policy.

### Risks and exclusions

No live send, bulk bypass, unofficial fallback or unlimited costs.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
