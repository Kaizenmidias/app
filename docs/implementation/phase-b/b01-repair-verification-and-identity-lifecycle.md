# B01 — Repair verification and identity lifecycle

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **B01** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [04-auth-and-security](../../architecture/04-auth-and-security.md), [09-admin-panel](../../architecture/09-admin-panel.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Repair new-user verification and address-change trust while keeping production registration closed.

### Required context and dependencies

Complete and independently verify **A00** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

AuthController; VerificationController; PasswordController; AdminController identity-update path or shared identity action; User; auth/verification routes; auth tests.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

No schema expected; use existing email_verified_at.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Choose limited authenticated unverified session for signed verification routes (or equally explicit verified-link flow) without granting dashboard/API access. Normalize email consistently and clear verification only when address changes. Validate signed links against current owner/address. Keep official CreateAdmin controlled verified behavior.

### Frontend scope

Only necessary verification flow/status messaging; preserve approved login styling.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Register -> verify -> dashboard using notification fake; unverified blocked across modules; signed expired/tampered/wrong-user link; email change reverify; disabled register GET/POST; generic reset response for known/unknown; invoke create-admin twice to prove duplicate refusal.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

Partner can complete verification; changed admin address loses prior verification; sole-admin email change has a secure limited-session verification/recovery path without automatic verification or lockout. No production registration enabling.

### Risks and exclusions

No new password policy guesses, real email sends, admin creation or stored real credentials. Do not disable MustVerifyEmail/CSRF.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
