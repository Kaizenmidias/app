# L02 — Idempotent automation execution and recovery

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **L02** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [02-system-architecture](../../architecture/02-system-architecture.md), [06-crm-modules](../../architecture/06-crm-modules.md), [16-provider-decisions](../../architecture/16-provider-decisions.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Execute approved actions asynchronously with dedup, retries, ownership and loop protection.

### Required context and dependencies

Complete and independently verify **L01, C03, J02** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

AutomationExecution job/service; rule/event consumer; retry/dead-letter admin views.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

Use `automation_executions` with UNIQUE(automation_rule_id,rule_version,source_event_id,action_key), claim/retry/outcome and a pinned rule/action snapshot. One rule/event may have multiple stable action keys; each retry reuses its original execution/operation identity. No second pair-level execution store is required; derive the summary from action rows. Follow chapter 03 for exact ownership/FKs and minimal safe logs.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Revalidate rule/account/entitlements/consent per execution; reserve quota, bounded backoff, unknown external outcome not blind retry, durable completion/outbox, rule rate and chain budget.

### Frontend scope

Safe execution history/failure reasons and explicit retry only idempotent case.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Crash after effect before ack, duplicate event, concurrent claim, rule disabled after queue, loop budget, failure isolation, provider fake.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

At-least-once transport causes one logical action, no cross-user or uncontrolled loop.

### Risks and exclusions

No shared production n8n instance; external n8n adapter only after separate isolated deployment approval.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
