# D01 — Sandbox billing dependency and single projection

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **D01** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [08-stripe-billing](../../architecture/08-stripe-billing.md), [03-database-model](../../architecture/03-database-model.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Adopt the approved official billing adapter and compatible Cashier/SDK in test mode only.

### Required context and dependencies

Complete and independently verify **C01, B01, B03** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

composer.json/lock; User Billable; proposed BillingService/config; reviewed Cashier migrations.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

Only reviewed Cashier user fields/subscriptions/subscription_items plus necessary pending-operation fields; no second subscription model.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Confirm P04; lock Cashier/SDK/API/webhook versions against Laravel12/PHP8.4. Keys outside Git. Inspect/disable or fail-close package webhook routes until D03 supplies one signed durable intake. Add deferred assignment subscription reference/FK after Cashier tables exist.

### Frontend scope

None beyond sandbox unavailable state.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Dependency compatibility, schema MySQL8.4/SQLite, user customer ownership, no live key fallback, one financial projection, no secrets in output.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

Sandbox adapter contract documented/versioned; provider calls fake by default; no real charge.

### Risks and exclusions

Package installation is authorized only as part of this later explicitly approved task; never by reading this plan. No production key or paid Price invention.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
