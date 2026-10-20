# C02 — Typed entitlement resolution

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **C02** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [07-plans-and-entitlements](../../architecture/07-plans-and-entitlements.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Implement deterministic plan assignments and typed feature/limit overrides without enabling commercial restrictions.

### Required context and dependencies

Complete and independently verify **C01, B03** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

proposed EntitlementResolver; definitions/models; account assignment actions; unit/feature tests.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

feature_definitions, plan_features, limit_definitions, plan_limits, user_plan_assignments, user_entitlement_overrides; justify each before migration. Use append-only assignment rows as single access history. C02 precedes Cashier: defer subscription_id column/FK to D01 after subscriptions exists.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Use one effective base assignment under user-row serialization. Separate undefined/finite-zero/unlimited/boolean; expiry and audited reason for override. Security and physical operational limits always dominate. Financial access adapter is injectable until D04.

### Frontend scope

Administrative incomplete-configuration state only; no undocumented lockout of current workflows.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Truth table: roles vs plans, unresolved policy, explicit zero, unlimited, override expiry/priority, concurrent assignment, financial hold versus grant semantics, no data loss on downgrade.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

Resolver outputs value+reason+source; unconfigured commercial gates remain disabled; no arbitrary benefit allocations.

### Risks and exclusions

No duplicate financial subscription table or magic unlimited sentinel. Decide assignment history contract before schema.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
