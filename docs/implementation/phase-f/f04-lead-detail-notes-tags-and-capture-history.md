# F04 — Lead detail notes tags and capture history

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **F04** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [06-crm-modules](../../architecture/06-crm-modules.md), [03-database-model](../../architecture/03-database-model.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Extend existing owner-scoped Leads with detail/edit and justified history without duplicating company/contact data.

### Required context and dependencies

Complete and independently verify **A02, B03, F03** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

LeadController/LeadPageController; Lead/LeadNote; lead views; proposed LeadSearchOccurrence/LeadTag and assignment relation; export actions.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

Add `lead_search_occurrences` only when immutable multi-search provenance is required, and `lead_tags`/`lead_tag_assignments` for the approved tag UI, using the exact model in chapter 03. Do not create alternate `lead_captures`, `tags` or `lead_tag` tables. Preserve existing notes and defer contacts/channel identity to I03.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Owner-scoped filters and pagination, safe fields, URL scheme http(s), export formula neutralization, provenance captures when reimport occurs. Define dedup merge review; no automatic destructive merge.

### Frontend scope

Detail/edit/notes/tags with same shell, server validation and real empty/error states.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Two-user all verbs, stored XSS/link schemes, CSV formula values, pagination/search, capture history not overwritten, reimport preserves user edits policy, pipeline send idempotent.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

No cross-user leak; unique identity persists and user correction/provenance semantics explicit.

### Risks and exclusions

Do not change external_place_id blindly or create multiple opportunity semantics. Existing captured phone is not contact opt-in.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
