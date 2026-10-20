# D04 — Subscription lifecycle and reconciliation policy

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **D04** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [08-stripe-billing](../../architecture/08-stripe-billing.md), [07-plans-and-entitlements](../../architecture/07-plans-and-entitlements.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Reconcile financial state and approved access policy independently of admin status.

### Required context and dependencies

Complete and independently verify **D02, D03, C02** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

BillingAccessPolicy; BillingService; scheduled reconciliation; assignments; notification fakes.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

Use authoritative subscriptions and assignments; additive only if missing reconciliation state.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Support chosen event set, cancellation/renewal/failed invoice/plan change/restore reconciliation. Lock local transition briefly; external lookup outside lock. Grace/read-only/trial/proration policies remain decisions. Refund != automatic cancel.

### Frontend scope

Account pending/delinquent/canceled states with provider Portal recovery; preserve CRM records.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Sandbox/fake invoice paid/failed, delayed events, subscription updated/deleted, periodic repair, unknown status fail-safe, downgrade retains Leads, user/status/role separation.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

SaaS nucleus acceptance requires D01–D04 with configured business policy; no billing effects from browser alone.

### Risks and exclusions

No live activation or arbitrary access grace. If P03/P05/P06 unresolved document blocked transition rather than guessing.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
