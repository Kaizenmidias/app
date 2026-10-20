# I03 — Contact identity and channel permission

Status: planned; not executed. This is an individually scoped future implementation prompt. Reading it does not authorize execution or a production operation.

## Executable prompt

Work only on task **I03** after the operator explicitly authorizes it. Use the existing codebase; do not switch model, client or authentication as a side effect.

1. Read [AGENTS.md](../../../AGENTS.md), the [execution contract](../CONTRACT.md), [overview](../../architecture/00-overview.md), and [03-database-model](../../architecture/03-database-model.md), [06-crm-modules](../../architecture/06-crm-modules.md), [16-provider-decisions](../../architecture/16-provider-decisions.md).
2. Check [progress](../PROGRESS.md), dependencies and decisions below, then inspect the actual files. If reality differs, report the specific gap before dependent work; do not rediscover or rewrite unrelated architecture.
3. Implement only the objective and boundaries below. Existing uncommitted changes belong to the operator; preserve them.

### Objective

Separate company Lead from human/contact address only when communication needs it.

### Required context and dependencies

Complete and independently verify **I01, F04** first. A task marked planned is not completed. Include the relevant pending P-decisions from chapter 13; never invent a commercial or provider choice.

### Probable files and reuse

Contact/ContactChannel/LeadContact; consent/suppression services; lead/contact UI.

These are probable touch points, not permission to modify every file. Reuse existing actions, models, UI and tests after inspection. Record exact scope before writing.

### Database changes

Use `contacts` without scalar phone/email mirrors, `contact_channels` as the authoritative editable address store, and `lead_contact` for same-owner company/person links. Add `contact_permissions` evidence and `contact_suppressions` keyed by owner/channel/normalized destination/purpose as chapter 03 defines. Channel UNIQUE(contact_id,channel,normalized_address) does not forbid legitimate shared destinations across people; dispatch deduplicates destinations and suppression applies across duplicate contacts. Scraped Lead phone/email remain source snapshots, not a second synchronized contact truth.

Any new migration must be forward/additive and separately tested on MySQL 8.4. This prompt never authorizes production migrations or cleanup of development data.

### Backend scope

Normalize phone/email cautiously, same-owner associations, non-destructive dedup/merge review; consent evidence purpose/channel/source/time and opt-out precedence. Scraped contact not opted in.

### Frontend scope

Owned contact detail/link and clear do-not-contact state; no speculative enrichment.

### Security and operation boundaries

Keep auth, active, verified, ownership and CSRF checks. Validate every related ID server-side; role and plan remain distinct. No production/VPS access, real credentials, external charge/message/scraping, worker/scheduler activation, migrations outside guarded isolated test targets, commit, push or deployment. Do not modify original scraper/login or other modules except specifically authorized files/regions stated in this task. Where this task proposes a live operational step, prepare its reviewable procedure and stop before execution.

### Mandatory tests and validation

Cross-owner merge/reference, normalized duplicates, opt-out immediate effect, evidence immutability and consent withdrawal.

Run relevant tests only with forced SQLite :memory: or an explicitly authorized disposable MySQL8.4 target protected before setup/fixtures. Do not install/start infrastructure automatically. Record pass/assertion/skip/failure counts separately; unavailable MySQL/browser/provider sandbox is a limitation, not a pass. For touched source run PHP/JS syntax, Blade compilation when applicable and git diff --check. Compare protected frontend hashes and verify development demo preservation when DB tests run.

### Acceptance criteria

One authoritative editable channel store, explicit normalization/dedup scope, destination-wide suppression across contacts, no copied Lead company in every message and no destructive automatic merge of people sharing an address.

### Risks and exclusions

No global shared person directory or automatic campaign subscription from scraping.

### Completion, reporting and next gate

Use the [standard checklist](../CONTRACT.md), update only this task's progress/evidence entry and necessary ADRs. Report exact changed files, schema effects, tests/commands/target identity without secrets, remaining risks and decision needs. Explain whether each acceptance criterion is demonstrated or pending. Do not claim deployment or runtime validation from static tests. Wait for operator approval before the next task.
