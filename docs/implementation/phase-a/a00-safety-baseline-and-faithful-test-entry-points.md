# A00 — Safety baseline and faithful test entry points

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **A00** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [01-current-state](../../architecture/01-current-state.md), [11-testing-strategy](../../architecture/11-testing-strategy.md), [13-decisions-and-risks](../../architecture/13-decisions-and-risks.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Make the isolated test guard run before any RefreshDatabase/setup/write, and capture real regressions for existing defects without enabling modules.

### Required context and dependencies

No implementation prerequisite. Establish the baseline from inspected code. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

tests/TestCase.php; tests/bootstrap/mysql.php; app/Console/Commands/TestingMigrate.php; tests/Feature/MySql*; tests/Support/*.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

None. No migration execution is authorized by this task until an independently disposable target and explicit destructive-test consent have been established.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Centralize declared and effective connection checks, reject DB_URL/socket/cache overrides, require exact test host/port/user/database plus authenticated server UUID/identity/grants and approved container association. Guard every child/cleanup before writes. SQLite must be :memory:. Prevent bootstrap from silently replacing an invalid operator choice and then calling it validated.

### Frontend scope

None; preserve every screen.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Use fakes to prove invalid values are rejected before connection/migration, including config cache and secondary overrides. Run only isolated SQLite until test-target authorization exists. Record F01/F02/F03 application-path reproducers in a separate command/evidence entry with defect IDs and honest expected failures. Do not hide defects with skips or inverted assertions; each F task must convert its reproducer to a passing regression.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

Invalid configuration cannot reach migration hooks; valid isolated target is distinguishable; development demo DB fingerprint is unchanged; report exactly which defects remain reproduced.

### Risks and exclusions

Guard ordering is part of safety. Do not call parent setup first or print connection secrets. Do not install/start Docker or access VPS. If MySQL unavailable, prepare coverage and report not executed.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
