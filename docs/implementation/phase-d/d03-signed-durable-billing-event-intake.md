# D03 — Signed durable billing event intake

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **D03** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [08-stripe-billing](../../architecture/08-stripe-billing.md), [02-system-architecture](../../architecture/02-system-architecture.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Build one durable signed Stripe webhook inbox with idempotent processing.

### Required context and dependencies

Complete and independently verify **D01** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

proposed BillingWebhookController/SyncBillingEvent; event handler adapter; routes/config; inbox/outbox models.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

billing_events and outbox_events as defined; unique provider/account/mode/event_id; case-sensitive external IDs.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Verify raw-body signature and expected mode/account; persist minimal receipt before ack. One Cashier/custom owner of projection, atomic claim/retry, provider retrieval for duplicate/out-of-order events. This exact provider endpoint uses no Laravel user session/auth/active/verified, and only it gets narrow CSRF exemption. All browser routes retain protections.

### Frontend scope

No public UI beyond safe event status to admin later.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Valid/invalid signature/replay timestamp, duplicate same/different event for object, out-of-order, durable ack, handler crash, concurrent delivery, no event PII/secrets in logs.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

No event has duplicate financial/access effect; retry recovers after crash; provider truth governs projection.

### Risks and exclusions

Do not trust request user IDs/metadata alone, ack before durable storage, disable global CSRF or log complete payloads.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
