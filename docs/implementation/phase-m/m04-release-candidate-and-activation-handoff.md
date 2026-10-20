# M04 — Release candidate and activation handoff

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **M04** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [12-release-strategy](../../architecture/12-release-strategy.md), [14-implementation-roadmap](../../architecture/14-implementation-roadmap.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Prepare a concrete reviewable release and separate code/database recovery plan; activation requires a later explicit instruction.

### Required context and dependencies

Complete and independently verify **M03, M02** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

release checklist/manifest; docs evidence/progress; no code unless diagnosed separately.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

List exact pending forward migrations, locks/data size/time plan and dedicated DB backup/restore checkpoint; do not execute.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Review Git scope/secrets/dependencies, immutable release/shared paths/cache/queue compatibility, provider test/live disabled manifest, feature flags and authorized smoke plan.

### Frontend scope

Operator signs visual acceptance and enabled-module list; registration/billing/workers remain separate activation choices.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Validate existing artifacts/commands/links and release candidate fingerprint, pre/post activation test list and rollback rehearsal result.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

One exact candidate scope, known migration/recovery effect and explicit operator approval request; no commit/push/deploy performed by this task.

### Risks and exclusions

No symlink change, production migration/service action or implicit paid/provider activation. M02 applies only to enabled-module retention scope if optional modules absent.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
