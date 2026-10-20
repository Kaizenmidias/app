# Implementation contract and completion checklist

This contract applies to every prompt in this directory. All tasks start **planned**. A document, prompt or historical test result is not an instruction to run the next task.

## Start contract

1. Read [AGENTS.md](../../AGENTS.md), [architecture index](../architecture/README.md), the task file and its referenced chapters.
2. Verify completed dependencies in [PROGRESS](PROGRESS.md) against real files and test evidence; task IDs are not proof.
3. Inspect actual code and working tree; preserve unrelated changes and record scope. Do not reset/stash/delete the operator's work automatically.
4. Resolve only decisions necessary for the current step. Keep prices, limits, feature allocations, grace, providers and deployment pending rather than inventing them.
5. Explain implementation scope, target environment, required changes and meaningful validation before changing files.

## Scope and safety

- Implement only the explicitly authorized task. Do not rewrite other modules or original scraper assets unless its exact approved file/region scope is documented.
- Keep auth/active/verified, CSRF, session safety, per-user ownership and permissions. A paid plan grants no admin privilege.
- Test only forced SQLite :memory: or a separately authorized disposable database with guards **before any** bootstrap/test-trait write. Never use development SQLite, production/CRM/MySQL accounts or production .env.
- No VPS access, service actions, external effects, migrations in production, commit/push/deploy by default. Implementation migrations in an isolated target still require proven destination and the task's authorization.
- No actual charge, campaign, WhatsApp, scraping, production credential login, real email or persistent/production worker in ordinary tests. Bounded isolated test-worker subprocesses require explicit task scope and cleanup. Fakes are default; sandbox secrets/calls require explicit secure provisioning/authorization.
- Browser auth/active/verified/CSRF stays enforced. Exact provider webhook ingress instead uses provider signature/account/mode checks without user-session middleware and only a narrow CSRF exemption.
- Do not print/store secrets in command arguments, logs, Git or reports. Never weaken guards to make tests green.
- No automatic dependency installation or infrastructure startup outside an explicitly approved task. No automatic model/client/authentication changes.

## Evidence categories

Report static findings, fake/unit/feature tests, real-engine/process tests, browser checks and authorized operational observations separately. A SQL helper that copies an algorithm is not an application-path test. A skipped test is not passed. An unavailable browser is not a visual check. An old VPS report is not current headroom.

## Standard completion checklist

- [ ] Task/dependencies/decisions and approved scope recorded.
- [ ] Existing files inspected; only necessary source/schema/docs changed.
- [ ] Input, role and same-owner reference authorization validated server-side.
- [ ] Local transaction/lock order, idempotency, external uncertainty and recovery reviewed.
- [ ] Additive migration tested for target MySQL version where applicable; no destructive production operation.
- [ ] Relevant tests run with sanitized target identity; pass/assertion/skip/failure counts recorded separately.
- [ ] Syntax/Blade/JS checks and git diff --check performed for affected files.
- [ ] Browser/keyboard/mobile/actual font checks recorded, or explicit limitation.
- [ ] Original scraper hashes reviewed against allowed diffs; development demo DB preserved.
- [ ] No secrets, real client fixtures, logs, test temp files or unrelated changes introduced.
- [ ] Documentation/ADRs updated when behavior or decision changed.
- [ ] Rollback compatibility and separate data recovery effects explained.
- [ ] Remaining risks and unsupported claims listed; operator approval requested before another phase.

Do not tick a box based only on an assertion in a previous chat. If a criterion is unverified, leave it unchecked and explain.

## Progress and deviations

Add task evidence to [PROGRESS.md](PROGRESS.md): date, task ID, approved scope, base/full candidate commit when available, exact changed files, target identity without secrets, executed commands, named tests/counts/skips/failures, reproduction outcomes, limitations and next decision. No auto-commit is implied.

New architecture decisions go into [DECISIONS.md](../architecture/DECISIONS.md) with alternatives, rationale, consequences, risks, status and approver. A deviation must describe evidence and smallest correction; stop dependent work if it changes a business decision/security boundary. Do not silently revise acceptance criteria to match broken code.

## Completion report template

Task ID and outcome; files changed; actual behavior; database/environment changes; security and ownership evidence; tests and counts; frontend integrity/demo preservation; unresolved decisions/risks; recovery/rollback; next authorized action. Keep the report concise but reproducible. Do not begin the next task automatically.
