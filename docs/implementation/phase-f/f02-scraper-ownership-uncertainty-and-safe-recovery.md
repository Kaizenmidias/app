# F02 — Scraper ownership uncertainty and safe recovery

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **F02** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [01-current-state](../../architecture/01-current-state.md), [10-infrastructure](../../architecture/10-infrastructure.md), [11-testing-strategy](../../architecture/11-testing-strategy.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Close current submission/timeout/failed/reconciliation/cancellation races while preserving physical capacity.

### Required context and dependencies

Complete and independently verify **A00, B03** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

RunScrapingJob; PollScrapingJob; ProspectingJob; ReconcileScraperJobs; InspectUnknownScraperJobs; regression/process tests.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

Only additive intent/attempt/recovery fields if justified by reproduced crash case; never rewrite deployed history.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Persist submission intent before POST; ambiguous hard crash retains capacity, unknown never auto-POSTs. Token-conditional simple updates or locked compound transitions. Status-only recovery can inspect terminal external jobs after local deadline. Explicit administrative evidence workflow for unknown no-ID. Cancellation must not hide/release active work without terminal evidence.

### Frontend scope

Safe unknown/recovery status messages only, no layout changes.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

A active past TTL then B token: A progress/import/finalize/release/clear/failed/deadline rejected; accepted POST then process death; delayed duplicate Run after completion; no-ID unknown no resubmit; deadline recovery; cancellation repeats. Use HTTP fake and real isolated DB locks/queue reservations. This specific test permits bounded test-worker subprocesses after guard validation, reaped on exit; never persistent/production workers.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

No premature release/stale-owner write; preserve external ID; status-only recovery. Revalidate status and absent external ID on locked current row. A delayed duplicate after original completion makes zero POSTs. Suspended owners cannot start work but platform cleanup continues safely.

### Risks and exclusions

Do not equate missing ID/expired claim/worker death with stopped remote scraper. No real Gosom job or production worker.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
