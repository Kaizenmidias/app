# F01 — Pipeline transaction and race safety

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **F01** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [01-current-state](../../architecture/01-current-state.md), [03-database-model](../../architecture/03-database-model.md), [11-testing-strategy](../../architecture/11-testing-strategy.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Repair current Pipeline races without replacing functional Kanban.

### Required context and dependencies

Complete and independently verify **A00, B03** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

PipelineController; small Pipeline action classes; stage/opportunity models; real-application MySQL process tests.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

No schema by default; preserve UNIQUE(user_id,name), UNIQUE(user_id,lead_id). Evaluate composite owner refs/indexes only with evidence.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Serialize stage topology using owner coordination row; lock stages by stable id then opportunities consistently across create/rename/reorder/send/move/delete. Recheck occupancy/destination inside transaction. Never cascade-delete late opportunities. Catch genuine unique/deadlock conflicts with bounded transaction-only retry and business response.

### Frontend scope

Existing confirmation/select/DnD remain; surface conflicts clearly, no redesign.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Two real application processes: delete-vs-send into empty stage, delete-vs-move, concurrent destinations deleted, create/reorder, duplicate stage/lead, transfer failure rollback. Execute actual actions/controller not copied SQL.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

No opportunity lost, exact one current lead opportunity, valid complete stage order, foreign IDs denied, deadlock recoverable/error bounded.

### Risks and exclusions

Do not remove uniqueness/FKs to pass tests. Do not hold HTTP calls under locks or modify protected scraper/login.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
