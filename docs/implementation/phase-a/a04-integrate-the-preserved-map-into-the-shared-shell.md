# A04 — Integrate the preserved map into the shared shell

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **A04** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [05-navigation-and-design](../../architecture/05-navigation-and-design.md), [06-crm-modules](../../architecture/06-crm-modules.md), [10-infrastructure](../../architecture/10-infrastructure.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Adapt the original prospecting map to the single approved shell without rewriting its collection/results behavior.

### Required context and dependencies

Complete and independently verify **A02, F02, F03** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

resources/views/prospecting.html; public/app/app.js; public/app/style.css; public/app/painel.html and painel.js only if explicitly authorized in this task.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

None.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Bound keywords, coordinates, radius/depth/runtime and allowed search parameters; throttle submissions. Preserve meaningful lang/zoom/fast_mode fields only after Gosom contract validation. Normalize numeric/string IDs consistently with regression evidence. Keep owner-scoped legacy endpoints until adapter tests permit migration; never direct browser traffic to Gosom. Correct misleading health/full-CSV claims: job-list availability is not Gosom worker health, and current re-export has eleven columns, not the original complete CSV.

### Frontend scope

Get explicit P02 approval for exact legacy files/regions first. Extract legacy body while preserving DOM IDs/listeners/results and map container dimensions; no iframe. Remove only approved mode toggle/header duplication. Keep attribution and implement return/dashboard via shared nav.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Map initialization/resize, forms/history/results/export contract, real CSRF fake-job request, no real scraper; before/after hashes with approved-diff list, keyboard/mobile and all navigation.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

One sidebar remains visible at all breakpoints; map correctly resizes; APIs unchanged; no marketing buttons or removal of mandatory map attribution.

### Risks and exclusions

No blind CSS injection/global fetch monkey patch. Review current public Nominatim/OSM policies and P07-style provider decision for scalable geocoding; do not assume public capacity or start scraping.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
