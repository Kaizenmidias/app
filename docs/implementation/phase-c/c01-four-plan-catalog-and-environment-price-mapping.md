# C01 — Four-plan catalog and environment price mapping

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **C01** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [07-plans-and-entitlements](../../architecture/07-plans-and-entitlements.md), [03-database-model](../../architecture/03-database-model.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Introduce four stable plan records without invented prices, limits or feature assignments.

### Required context and dependencies

Complete and independently verify **B03, A02** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

proposed Plan/PlanPrice models; additive migrations; safe idempotent catalog command/seed; domain tests.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

plans and plan_prices only. Four stable keys free/essencial/plus/pro; historical external IDs case-sensitive, account/mode scoped.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Validate key/slug/order/availability/kind and Stripe mapping integrity; no Checkout until real mapping verified. Plan archive does not delete subscribers. Plan metadata allowlist.

### Frontend scope

Catalogue may show unavailable-paid configuration states; no fake price/benefit marketing.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Idempotent catalog setup, four records, no numeric prices guessed, Free no checkout, same key duplicate rejection, test/live mapping isolation.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

Four plans persist independent of billing; price mapping can be absent safely; existing partner access unaffected until approved enforcement.

### Risks and exclusions

Do not assign resources to tiers, install Stripe, enable signup or mark Pro admin.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
