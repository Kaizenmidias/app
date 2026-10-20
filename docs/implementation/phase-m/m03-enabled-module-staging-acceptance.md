# M03 — Enabled-module staging acceptance

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **M03** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [11-testing-strategy](../../architecture/11-testing-strategy.md), [12-release-strategy](../../architecture/12-release-strategy.md), [13-decisions-and-risks](../../architecture/13-decisions-and-risks.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Verify the dependency closure of only modules selected for release in an isolated staging environment.

### Required context and dependencies

Complete and independently verify **A03, A04, B02, C02, D04, E03, F01, F02, F03, F05, M01** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

test suites/contracts/browser checks; release manifest; docs/implementation evidence.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

Migrations only on explicitly authorized disposable test/staging target, never production by default.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Run fast SQLite, real MySQL action/process cases, DB queue/cache/session lifecycle, webhook sandbox and current schema checks. Add optional G–L prerequisites only when those modules are enabled.

### Frontend scope

Desktop/mobile/a11y/font/asset/navigation checks with actual browser or report limitation; no made-up screenshots.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Negative permissions/CSRF/guard matrix, restore compatibility, no unexpected skip, protected scraper approved hashes, demo DB before/after.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

Every enabled module has tested dependencies and business decisions; undeveloped modules inert; measured resource envelope and rollback compatibility recorded.

### Risks and exclusions

Do not demand all twelve modules for first core launch; no production writes, scraper/live sends or automatic infrastructure startup.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
