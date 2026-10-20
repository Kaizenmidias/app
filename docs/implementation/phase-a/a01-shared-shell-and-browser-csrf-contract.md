# A01 — Shared shell and browser CSRF contract

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **A01** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [05-navigation-and-design](../../architecture/05-navigation-and-design.md), [04-auth-and-security](../../architecture/04-auth-and-security.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Create one reusable shell/navigation source using approved tokens and fix the existing browser session/CSRF contract with minimal scope.

### Required context and dependencies

Complete and independently verify **A00** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

resources/views/layouts/scraper-shell.blade.php; proposed shared Blade components; routes/web.php; app/Http/Middleware/EnsureFrontendCsrf.php; future browser request helper.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

None.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

First trace Laravel web CSRF cookie encryption and custom frontend.csrf expectations. Ensure /dashboard and /painel bootstrap the same secure session/token contract. Centralize request headers without disabling either verification blindly. Expose only safe authenticated user identity to the shell.

### Frontend scope

Extract sidebar definition and active route behavior. Document #1a73e8 versus current #0075ff and wait for P01 color selection before changing tokens. No global legacy CSS rewrite; unavailable modules are inert accessible labels, not fake links.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

HTTP GET-cookie-token-POST and missing/invalid CSRF tests; verify correct token representation over actual HTTP, not only middleware-disabled PHPUnit. Guest/active/verified matrix; keyboard/mobile shell and no sidebar duplication.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

Single navigation source, valid legacy mutation passes CSRF, invalid fails, tokens/cookies stay secure and no new broken links.

### Risks and exclusions

Do not change scraper files until A04 authorization. Do not expose static private data in public/app/painel.html. Browser tooling absence must be reported.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
