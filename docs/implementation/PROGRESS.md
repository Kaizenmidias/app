# Implementation progress

Architecture plan created 2026-10-20. **No implementation task below has started or been approved by this planning run.** Existing local features do not mean every new task criterion is satisfied.

Historical baseline: local working-tree base f5ffc7bd8693a4b6d80694b164d27041d7b258e7; prior reports SQLite43/109 (9 MySQL skipped), MySQL51/144. These are not new test runs or proof of uncovered races. See [current state](../architecture/01-current-state.md).

| Task | Status | Approval | Evidence / commit / limitations |
| --- | --- | --- | --- |
| [A00](phase-a/a00-safety-baseline-and-faithful-test-entry-points.md) | Implemented locally | Pending review | Guard centralization and pre-bootstrap fakes implemented; SQLite validation below |
| [A01](phase-a/a01-shared-shell-and-browser-csrf-contract.md) | Planned | Pending | Not executed |
| [A02](phase-a/a02-user-identity-and-module-navigation.md) | Planned | Pending | Not executed |
| [A03](phase-a/a03-useful-crm-overview-with-real-aggregates.md) | Planned | Pending | Not executed |
| [A04](phase-a/a04-integrate-the-preserved-map-into-the-shared-shell.md) | Planned | Pending | Not executed |
| [B01](phase-b/b01-repair-verification-and-identity-lifecycle.md) | Planned | Pending | Not executed |
| [B02](phase-b/b02-personal-profile-and-session-management.md) | Planned | Pending | Not executed |
| [B03](phase-b/b03-consistent-ownership-authorization.md) | Implemented locally | Pending review | Centralized policy invocation and two-user ownership matrix; SQLite green, MySQL unavailable |
| [C01](phase-c/c01-four-plan-catalog-and-environment-price-mapping.md) | Planned | Pending | Not executed |
| [C02](phase-c/c02-typed-entitlement-resolution.md) | Planned | Pending | Not executed |
| [C03](phase-c/c03-atomic-quota-reservation-primitives.md) | Planned | Pending | Not executed |
| [D01](phase-d/d01-sandbox-billing-dependency-and-single-projection.md) | Planned | Pending | Not executed |
| [D02](phase-d/d02-hosted-checkout-and-billing-portal.md) | Planned | Pending | Not executed |
| [D03](phase-d/d03-signed-durable-billing-event-intake.md) | Planned | Pending | Not executed |
| [D04](phase-d/d04-subscription-lifecycle-and-reconciliation-policy.md) | Planned | Pending | Not executed |
| [E01](phase-e/e01-administrative-authorization-and-atomic-audit.md) | Planned | Pending | Not executed |
| [E02](phase-e/e02-user-lifecycle-and-retention-safe-administration.md) | Planned | Pending | Not executed |
| [E03](phase-e/e03-administrative-plans-and-subscription-management.md) | Planned | Pending | Not executed |
| [F01](phase-f/f01-pipeline-transaction-and-race-safety.md) | Implemented locally | Pending review | Owner-row transaction coordination and atomic occupied-stage transfer; SQLite green, MySQL concurrency pending |
| [F02](phase-f/f02-scraper-ownership-uncertainty-and-safe-recovery.md) | Implemented locally | Pending review | Ambiguous submission, stale duplicate, deadline status recovery and cancellation reconciliation covered with fakes; MySQL/process validation pending |
| [F03](phase-f/f03-complete-resumable-csv-import.md) | Implemented locally | Pending review | Bounded resumable CSV import, explicit outcomes and ownership-aware finalization; MySQL heartbeat validation pending |
| [F04](phase-f/f04-lead-detail-notes-tags-and-capture-history.md) | Planned | Pending | Not executed |
| [F05](phase-f/f05-opportunity-lifecycle-and-history.md) | Planned | Pending | Not executed |
| [G01](phase-g/g01-owned-activities-and-tasks.md) | Planned | Pending | Not executed |
| [G02](phase-g/g02-calendar-appointments-and-reminders.md) | Planned | Pending | Not executed |
| [H01](phase-h/h01-versioned-briefing-templates.md) | Planned | Pending | Not executed |
| [H02](phase-h/h02-briefing-responses-secure-sharing-and-attachments.md) | Planned | Pending | Not executed |
| [I01](phase-i/i01-provider-and-consent-decision-record.md) | Planned | Pending | Not executed |
| [I02](phase-i/i02-owned-communication-accounts-and-signed-webhooks.md) | Planned | Pending | Not executed |
| [I03](phase-i/i03-contact-identity-and-channel-permission.md) | Planned | Pending | Not executed |
| [J01](phase-j/j01-inbox-inbound-conversations.md) | Planned | Pending | Not executed |
| [J02](phase-j/j02-safe-outbound-messages-attachments-and-delivery.md) | Planned | Pending | Not executed |
| [K01](phase-k/k01-email-provider-sender-verification-and-audiences.md) | Planned | Pending | Not executed |
| [K02](phase-k/k02-consented-campaigns-scheduling-and-dispatch.md) | Planned | Pending | Not executed |
| [K03](phase-k/k03-delivery-feedback-metrics-and-cost-controls.md) | Planned | Pending | Not executed |
| [L01](phase-l/l01-domain-events-and-bounded-automation-rules.md) | Planned | Pending | Not executed |
| [L02](phase-l/l02-idempotent-automation-execution-and-recovery.md) | Planned | Pending | Not executed |
| [M01](phase-m/m01-safe-observability-and-runtime-budgets.md) | Planned | Pending | Not executed |
| [M02](phase-m/m02-retention-erasure-export-and-restore-rehearsal.md) | Planned | Pending | Not executed |
| [M03](phase-m/m03-enabled-module-staging-acceptance.md) | Planned | Pending | Not executed |
| [M04](phase-m/m04-release-candidate-and-activation-handoff.md) | Planned | Pending | Not executed |

### A00 evidence (2026-10-20)

- Scope: centralized isolated-target guard, before-setup validation, child-process guard reuse, and fake rejection tests; no database or production operation.
- Changed files: `app/Support/TestingTargetGuard.php`, `app/Console/Commands/TestingMigrate.php`, `tests/TestCase.php`, `tests/bootstrap/mysql.php`, `tests/Support/mysql-worker.php`, `tests/Support/mysql-concurrency-worker.php`, `tests/Support/run-mysql-concurrency.php`, `phpunit.mysql.xml`, `tests/Unit/TestingTargetGuardTest.php`.
- Target: forced SQLite `:memory:` only for validation; MySQL target not executed because no independently authorized disposable target was established.
- Reproduction: F01/F02/F03 production-path reproducers remain pending; no skips were used to claim a fix.
- Pending: run the required SQLite suite and syntax/diff checks; review guard behavior before authorizing B03.

### B03 evidence (2026-10-20)

- Scope: invoke owner policies for leads, prospecting jobs, notes and exports; add policies for notes, pipeline stages and opportunities; preserve inline related-stage/lead ownership checks; expand deliberate 403/404 isolation assertions.
- Changed files: `app/Http/Controllers/Controller.php`, `app/Http/Controllers/LeadController.php`, `app/Http/Controllers/NoteController.php`, `app/Http/Controllers/ExportController.php`, `app/Http/Controllers/ProspectingController.php`, `app/Http/Controllers/PipelineController.php`, `app/Policies/LeadNotePolicy.php`, `app/Policies/PipelineStagePolicy.php`, `app/Policies/PipelineOpportunityPolicy.php`, `tests/Feature/TenantIsolationTest.php`, `tests/Feature/ScraperRegressionTest.php`, `tests/Feature/PipelineTest.php`.
- Database/schema: none; tests used forced SQLite `:memory:`. No persistent target or production database was accessed.
- Tests: PHPUnit SQLite `55 passed, 112 assertions, 9 skipped` (all MySQL-only tests; no disposable MySQL target available), no failures. PHP syntax checks passed for all touched PHP files; `git diff --check` passed with only pre-existing CRLF normalization warnings.
- Authorization evidence: partner cross-user reads/writes and related IDs are rejected; policy failures intentionally return 403, while missing/foreign related stage destinations remain 404. Admin/global routes were not broadened.
- Limitations: no MySQL 8.4 execution, browser test or production/runtime validation. F01 remains the next approved phase and must address pipeline transaction races.

### F01 evidence (2026-10-20)

- Scope: serialize Pipeline mutations through the owning `users` row; lock stages/opportunities in stable application order; recheck stage occupancy and destination inside the deletion transaction; preserve policies, unique keys and cascade FKs; bounded transaction retry for deadlocks.
- Changed files: `app/Http/Controllers/PipelineController.php`, `tests/Feature/PipelineTest.php`.
- Database/schema: no migration and no schema change. Existing unique constraints and foreign keys were preserved.
- Root cause corrected: `destroyStage()` previously counted opportunities before opening its transaction, allowing a concurrent send/move to be cascaded away. Stage creation/name updates/send/move/reorder also lacked one shared owner coordination lock.
- Tests: Pipeline feature tests `8 passed, 26 assertions`; full SQLite suite `56 passed, 115 assertions, 9 skipped`, with skips limited to MySQL-only tests because no disposable MySQL 8.4 target was available. PHP syntax and `git diff --check` passed.
- Not executed: real two-process application-path MySQL tests for delete-vs-send, delete-vs-move, concurrent reorder and deadlock recovery. Existing process harnesses use handwritten SQL and are not counted as F01 acceptance evidence.
- Acceptance: no-opportunity-loss behavior is covered sequentially and transactionally; exact concurrent guarantees remain pending real MySQL application-path tests. F02 remains blocked until ownership/uncertainty work is separately authorized.

### F02 evidence (2026-10-20)

- Scope: preserve capacity and external identifiers across ambiguous submission, stale duplicate delivery, polling timeout/deadline, stale claim failure and cancellation reconciliation. No Pipeline, visual, billing or provider changes.
- Changed files: `app/Jobs/RunScrapingJob.php`, `app/Jobs/PollScrapingJob.php`, `app/Console/Commands/ReconcileScraperJobs.php`, `app/Models/ProspectingJob.php`, `database/migrations/2026_10_20_000004_add_submission_intent_to_prospecting_jobs.php`, `tests/Feature/ScraperProcessingTest.php`, `tests/Feature/ScraperRegressionTest.php`.
- Database/schema: one additive migration adds nullable submission intent timestamp and unique attempt token. It was exercised only by the forced SQLite in-memory test migrations; no persistent migration ran.
- Corrections: submission intent is persisted before POST; locked state is rechecked before reservation; ambiguous failures become `unknown` while retaining capacity; delayed completed jobs do not POST; external status is checked before local deadline recovery; stale `failed()` updates are token/lock conditional; cancel-requested jobs remain visible to reconciliation and do not release capacity until terminal external evidence.
- Tests: scraper-directed tests `20 passed, 40 assertions`; full SQLite suite `60 passed, 121 assertions, 9 skipped`; skipped tests are MySQL-only. PHP syntax and `git diff --check` passed. HTTP calls were faked; no Gosom or worker ran.
- Blocked: real MySQL 8.4 locks, separate-process queue lifecycle, crash-after-POST, token expiry while active, multirow import heartbeat and cancellation races remain unvalidated without an authorized disposable target. F03 owns the import-specific cases.

### F03 evidence (2026-10-20)

- Scope: complete/resumable CSV ingestion only; no UI, provider, Pipeline or schema changes. The importer now uses stream CSV semantics, bounded bytes/rows, `place_id` with `cid` fallback, explicit accepted/updated/rejected counts and an incomplete/ownership-lost outcome.
- Root cause: line splitting with `preg_split` corrupted quoted commas/newlines and the poller ignored the import result, marking jobs `ok` after partial or lost-owner imports. Heartbeat renewal was also treated as failed when an unchanged timestamp produced a zero-row update.
- Changed files: `app/Services/ScraperClient.php`, `app/Services/ScraperImportOutcome.php`, `app/Jobs/PollScrapingJob.php`, `config/kaizen.php`, `tests/Feature/ScraperProcessingTest.php`.
- Database/schema: none; Lead uniqueness and existing F02 migration are preserved.
- Tests: F03-directed tests are included in the SQLite run (`62 passed, 128 assertions, 9 skipped`); skips are MySQL-only and no MySQL 8.4 target is authorized or available. PHP syntax and `git diff --check` passed. No persistent migration or production operation was performed.
- Pending acceptance: same-second MySQL heartbeat, multi-process stale-owner import, interrupted resume and real application-path MySQL validation require a disposable guarded MySQL 8.4 target. F01 and F02 real MySQL validation remain explicitly pending.

### F01–F03 integrated validation (2026-10-20)

- Scope: read-only preparation and environment audit; no container, volume, database, migration or persistent worker was created.
- Environment: Docker Engine/Compose is unavailable on the local Windows host (`docker` command not found). No VPS or production connection was attempted.
- Guard review: `TestingTargetGuard` requires the exact testing tuple (`127.0.0.1:13306`, `kaizen_partners_test`, `kaizen_test`), validates effective MySQL identity and database-scoped grants before setup. `TestingMigrate` refuses migration when that validation fails. The MySQL PHPUnit bootstrap invokes the guard before application setup.
- Existing coverage: SQLite covers F01 transactional sequencing and rollback behavior, F02 ambiguous submission/deadline/cancellation/ownership fakes, and F03 CSV parsing/outcome behavior. Existing MySQL/process harnesses are present but were not executed and do not by themselves prove all application-path acceptance criteria.
- Blocked scenarios: real two-process F01 delete/send, delete/move, reorder/deadlock; F02 crash-after-POST, token expiry and cancellation races; F03 same-second MySQL heartbeat, interrupted resume and multi-process stale-owner import. These remain pending a disposable, independently validated MySQL 8.4 target.

## Evidence entry template

Date:
Task:
Operator-approved scope and decisions:
Base/candidate commit or scoped worktree:
Files:
Database target guard evidence (sanitized):
Commands:
Named tests / passed / assertions / skipped(reason) / failed:
Real application paths and concurrent connections exercised:
Fake/sandbox/browser/runtime limitations:
Protected assets and demo DB preservation:
Residual risks / deviations / ADR:
Review outcome / next approval:

No credentials, cookies, tokens, password hashes, live payloads or personal client data belong in this ledger.

### F01-F03 MySQL validation on isolated VPS target (2026-10-20)

- Target: `kaizen-mysql-test`, MySQL 8.4.11, loopback port 13306, database `kaizen_partners_test`, user `kaizen_test`, exclusive network/volume. Credentials omitted.
- Local F01/F02/F03 source and tests were copied only to `/www/kaizen-partners/testing/`; production `current`, `.env`, storage and services were not modified. Autoload was regenerated in the test workspace.
- Effective target and database-scoped grants were verified; protected `testing:migrate` completed without schema errors. The database already contained 17 tables and was not reset or purged.
- Structural MySQL: `MySqlConcurrencyTest` passed, 2 tests / 2 assertions.
- F02/F03 application tests: `ScraperProcessingTest` plus `ScraperRegressionTest` passed, 22 tests / 47 assertions; external HTTP remained faked.
- F01 process tests: 6 of 7 scenarios passed. Capacity, claim, unique lead conflict, pipeline send, move and delete-vs-move passed. Concurrent reorder failed because one process returned an InnoDB deadlock and the harness expected both `reordered`; deadlock recovery is not demonstrated.
- The pre-existing test database was not empty and now contains fixture residue. No destructive cleanup was performed. A clean baseline requires operator identification/authorization or a fresh test volume.
- Post-run resources: about 2.8 GiB available memory, 86 MiB swap used, load about 1.16/0.98/2.17. No external service was restarted. F01-F03 are not fully validated; production deploy remains blocked.

### F01 reorder deadlock follow-up (2026-10-20)

- Root cause: the previous reorder worker updated stage rows in opposite orders without first locking the owner row, unlike `PipelineController::reorder()`. This created an artificial InnoDB lock inversion. The controller also omitted the bounded transaction retry argument.
- Corrections: `PipelineController::reorder()` now retries the complete transaction up to three times; the MySQL worker locks the owner and all stages in stable `id` order, rolls back before retry, and never retries indefinitely.
- Local validation: Pipeline feature tests passed (8 tests, 26 assertions); PHP syntax and diff checks passed.
- MySQL validation: `MySqlPipelineConcurrencyTest` passed three consecutive runs, each 4 tests / 23 assertions (12 tests / 69 assertions total). No production database or service was touched.
- Limitation: the process harness still exercises SQL-level concurrency rather than two independent HTTP/controller processes. Full controller-path concurrent validation remains pending and F01 is not yet considered completely accepted.

### F01 controller-path harness attempt (2026-10-20)

- Added local-only experimental workers `tests/Support/http-controller-worker.php` and `tests/Support/run-http-controller-concurrency.php` to boot the Laravel kernel in independent PHP processes and submit the real reorder route with fixture data.
- Local syntax and Pipeline tests passed. On the isolated VPS workspace, the kernel booted and both processes overlapped, but the synthetic authentication/session handoff was rejected by the real `auth` middleware (HTTP 302 to `/login`). No controller mutation occurred; fixture stages remained positions `0,1,2` and were cleaned by the runner.
- This attempt does not count as controller-path coverage. No authentication or middleware was weakened. A proper session-cookie or test HTTP server harness is still required before F01 can be accepted.

### F01 real web authentication harness correction (2026-10-20)

- Diagnosis: the first harness used `SESSION_DRIVER=array`, which cannot share an authenticated session between independent PHP processes. It also copied the encrypted session-cookie value into the raw session ID and injected a user with `Auth::guard()->setUser()`, so the real `auth` middleware correctly redirected both requests to `/login`.
- Correction: the local-only worker now performs the actual web flow (`GET /login`, extracts the CSRF field, `POST /login` with a bcrypt fixture password, retains the cookies emitted by Laravel, then `POST /pipeline/stages/reorder`). The runner supplies `SESSION_DRIVER=database`, an isolated cookie name, and a hashed fixture password through the authorized MySQL test target. No production authentication code or middleware was changed.
- Local validation: the Pipeline feature suite passed (8 tests, 26 assertions); both harness files pass PHP syntax validation and `git diff --check` reports no errors attributable to them.
- VPS execution: blocked in this session because the configured SSH alias could not resolve (`kaizenmidias`); no files were transferred and no remote test was run. The corrected harness therefore remains unvalidated against the isolated MySQL workspace. F01 remains pending controller-path validation.

### F01 SSH recovery and HTTP validation attempt (2026-10-20)

- SSH destination was recovered from the existing local alias `kaizen-vps` (`StrictHostKeyChecking` remained enabled). A single identity check confirmed the expected host, root account, `/www/kaizen-partners/testing/` workspace, and `kaizen-mysql-test` container. No SSH configuration was changed.
- Only `tests/Support/http-controller-worker.php` and `tests/Support/run-http-controller-concurrency.php` were synchronized. The A00 guard passed against the authorized target; no production directory or service was touched.
- The first single-process authentication check was blocked before authentication because the remote test workspace contains an old invalid `resources/views/auth/login.blade.php`; the real `GET /login` returned HTTP 500 with `unexpected token "endif", expecting end of file`. This is a stale workspace/application artifact, not an authentication approval. No middleware or application code was changed.
- The failed run created five scoped `http-fixture-*` users; they and their pipeline stages were removed by a targeted cleanup. No whole-database cleanup was used.
- Controller-path concurrency was not executed. F01 remains unvalidated until the authorized test workspace is synchronized with a syntactically valid application release (without changing production) and the single real login flow succeeds.

### F01 test-workspace consistency and session persistence follow-up (2026-10-20)

- Compared local, testing-workspace and active-release hashes. The testing workspace was stale only for the authentication view and its local dependencies: `resources/views/auth/login.blade.php`, `resources/views/layouts/auth.blade.php`, `public/auth/login.css`, and `public/auth/logo-kaizen.png`. Routes, controllers, middleware, policy, models and both HTTP harnesses already matched the local candidate.
- Synchronized only those four non-secret files into `/www/kaizen-partners/testing/`; the active release was not modified. `php artisan view:cache` passed in the testing workspace.
- The real `GET /login` now renders, and the login POST is accepted, but the subsequent protected reorder request still returns HTTP 302 to `/login`. The worker reports `SESSION_DRIVER=database` and an isolated cookie name; no `Auth::setUser()` or middleware bypass is used. Session continuity is therefore not yet proven and controller-path concurrency was not run.
- F01 remains blocked. The next investigation must inspect the database session row/cookie handoff in the isolated workspace or use a real local HTTP server, without changing production authentication code. No production services or databases were touched.

### F01 session persistence instrumentation (2026-10-20)

- Instrumentation proved the login path itself: `POST /login` returned the expected redirect to `/dashboard`, `auth.web` recognized the fixture user in the same process, and the `sessions` table contained a row. No secrets, cookie values or tokens were logged.
- The cookie value emitted by the kernel decrypted successfully, but its session identifier had no corresponding row; the row that existed belonged to a different identifier. The subsequent request therefore correctly redirected to `/login`.
- This mismatch is caused by the synthetic repeated `Kernel::handle()`/termination transport in the harness, which does not reproduce the web server's final cookie/session handoff. Production authentication and session configuration were not changed.
- The sequential authenticated controller check and all concurrent rounds remain blocked. A real isolated HTTP server/client transport is required before F01 can be accepted; no concurrency result is claimed.

### F01 real HTTP server preflight (2026-10-20)

- The authorized VPS workspace has PHP cURL and PDO MySQL available, and port `18183` was free during inspection. The target remained the isolated testing workspace and the A00 guard was not weakened.
- No HTTP server was started and no fixtures were created in this preflight. A real multi-worker HTTP harness still needs to be added and reviewed before execution; the previous synthetic kernel harness is not reused as evidence.

### F01 local real HTTP harness implementation (2026-10-20)

- Added local-only components `tests/Support/RealHttp/HttpServer.php`, `HttpClient.php`, `FixtureManager.php` and entrypoint `tests/Support/real-http-f01.php`.
- The harness validates A00, requires the testing environment, starts a loopback PHP server with four requested CLI workers, uses independent cURL cookie jars, performs real GET/POST login with CSRF, checks `/dashboard`, and cleans only its own fixtures in `finally`.
- Added `docs/implementation/f01-real-http-harness.md` with prerequisites and future execution instructions.
- Local validation: PHP syntax passed for all four files and `git diff --check` reported no harness-specific errors. The server was not started, no fixtures were created, no SSH/VPS access was performed, and F01 remains pending remote MySQL execution.
- The current local deliverable prepares sequential authentication and reorder entry points; deletion-vs-move, deletion-vs-create, isolation and retry-exhaustion scenarios remain explicitly unexecuted until their real endpoint orchestration and overlap instrumentation are implemented.

### F01 controlled execution review (2026-10-20)

- Pre-execution review found a critical harness defect: `tests/Support/real-http-f01.php` starts a temporary server and performs only sequential login/dashboard validation. It does not implement independent worker processes, reorder requests, or temporal-overlap evidence despite labeling reorder as `prepared`.
- No files were synchronized, no SSH/VPS access was performed, no server was started and no fixtures were created. Remote execution is blocked until the entrypoint is corrected so its reported scenarios match actual behavior.

### F01 local concurrency entrypoint completed (2026-10-20)

- Added `tests/Support/real-http-f01-worker.php` and completed `real-http-f01.php` with two independent PHP worker processes, separate cURL cookie jars, a ten-second barrier timeout, real CSRF login, real `POST /pipeline/stages/reorder`, PID/status/duration output and final position validation.
- The runner preserves A00, starts only loopback HTTP, cleans only its own fixture user/stages in `finally`, and does not run migrations or production services.
- Local validation: all new/changed PHP files pass `php -l`; Pipeline tests pass (8 tests, 26 assertions); `git diff --check` has no harness-specific errors. No server, fixtures, SSH or remote test was run.
- Temporal client overlap is observable through independent PIDs and barrier timing; transaction-level overlap remains explicitly inconclusive without server instrumentation. Delete-vs-move, delete-vs-create, cross-user isolation and retry-exhaustion scenarios remain pending.

### F01 controlled real HTTP execution (2026-10-20)

- Reviewed and synchronized only the five harness PHP files plus its documentation into `/www/kaizen-partners/testing/`. The active release and production services were untouched.
- A00 validation passed and port `18183` was free. The remote `.env.testing` contained `SESSION_DRIVER=array`; the runner now forces `SESSION_DRIVER=database`, an isolated cookie name and `CACHE_STORE=array` in its test process without modifying the remote environment file.
- The sequential real HTTP attempt started the loopback server but `POST /login` returned HTTP 419 and `/dashboard` returned 302. The runner's `finally` stopped the server and removed only its own fixtures. No concurrency rounds were started.
- F01 remains blocked on real HTTP CSRF/session continuity. No approval is claimed and no production authentication code was changed.

### F01 real HTTP 419 diagnosis (2026-10-20)

- The loopback PHP server started in the isolated workspace and `GET /login` returned 200 with a 40-character CSRF field and `Set-Cookie` headers for `XSRF-TOKEN` and `laravel_session`.
- cURL diagnostics showed the session cookie was present in the response headers but was not retained in the cookie jar; only `XSRF-TOKEN` appeared in the jar. The subsequent `POST /login` therefore returned HTTP 419 and `/dashboard` returned 302.
- The runner stopped in `finally`, removed only its own fixtures, and did not start concurrent workers. No production code or services were changed. F01 remains blocked on correcting the isolated cURL cookie transport.

### F01 cookie attribute follow-up (2026-10-20)

- Inspected effective `Set-Cookie` metadata without values. `XSRF-TOKEN` and `laravel_session` shared path `/`, host-only domain, `Secure=false`, `SameSite=Lax` and matching expiry. The session cookie differed only by `HttpOnly=true`.
- The cURL jar retained only `XSRF-TOKEN`; `laravel_session` was not stored. An isolated attempt to force `SESSION_HTTP_ONLY=false` in the temporary server command did not change the effective attribute, indicating another configuration source/cache in the test workspace. No manual cookie header workaround or application change was applied.
- The runner stopped and cleaned its own fixtures. Login remains HTTP 419, concurrency was not started, and F01 remains blocked until the isolated configuration source is identified and corrected safely.

### F01 libcurl cookie-engine diagnosis (2026-10-20)

- `CURLINFO_COOKIELIST` proved that libcurl's in-memory cookie engine receives both `XSRF-TOKEN` and `laravel_session`; the disk jar representation is incomplete and cannot by itself be used as the cause.
- The client was corrected locally to reuse one cURL handle per client and rely on `CURLOPT_COOKIEFILE`/`CURLOPT_COOKIEJAR`, without constructing a Cookie header. The isolated execution still returned HTTP 419 on login, so session recognition remains unproven.
- No CSRF, authentication or application code was changed. The server was stopped and fixtures cleaned. F01 remains blocked; concurrency was not started.

### F01 session-loss boundary narrowed (2026-10-20)

- cURL instrumentation now confirms the POST request selects both `laravel_session` and `XSRF-TOKEN`; the cookie engine is not dropping the session before transmission.
- The server still returns HTTP 419, so the remaining boundary is server-side receipt/decryption/session lookup/CSRF comparison. No cookie values or tokens were logged, and no manual Cookie header was introduced.
- The harness stopped and cleaned its fixtures. F01 remains blocked; further changes require server-side test-only observability of `$_COOKIE`, effective session configuration and the sessions row, without modifying production middleware.

### Release candidate readiness audit (2026-10-20)

- Read-only audit completed for the architecture, release strategy, roadmap, A00/B03/F01/F02/F03 documentation, PHPUnit configurations, migrations, routes and current Git state.
- The workspace is dirty and mixes application changes, migrations, documentation and test-only harnesses; no release candidate allowlist or commit was created.
- Existing evidence supports local Pipeline tests (8 tests, 26 assertions), prior isolated MySQL SQL-concurrency runs, and prior F02/F03 isolated tests. Real HTTP/controller authentication and concurrency remain unvalidated because the harness still returns HTTP 419 during session continuity.
- Migrations `2026_10_20_000003_create_pipeline_tables.php` and `2026_10_20_000004_add_submission_intent_to_prospecting_jobs.php` are additive in `up()`, but their `down()` methods are destructive and must not be used as a production rollback. No migration was executed in this audit.
- Candidate readiness is **NO-GO** pending scoped-file review, real HTTP/controller evidence, confirmation of F02/F03 production-path coverage, and verified backup/rollback procedures. No production or VPS changes were made.

### Release candidate scope inventory (2026-10-20)

- Inventoried the local repository at branch `main`, HEAD `f5ffc7bd8693a4b6d80694b164d27041d7b258e7`: 61 status entries, comprising 32 tracked modifications/deletion and 29 untracked paths; no staged changes were observed.
- Added `docs/implementation/release-candidate-scope.md` with classifications, scraper reference/local hashes, migration inventory, proposed runtime allowlist, exclusions, and freeze decisions.
- The release is not frozen. The original scraper entry-point files with differing hashes, the missing production reference commit, the mixed test/runtime workspace, and the unresolved real HTTP/controller validation remain explicit blockers.

### Final scraper/dashboard scope review (2026-10-20)

- Read-only diff review found that `public/app/painel.html` adds Leads/Pipeline navigation and removes the visible GitHub action block; `public/app/painel.css` removes only the corresponding compact-credit styles; `resources/views/prospecting.html` removes the two action links while retaining the motor and MIT attribution text.
- No form, field, map, scraper endpoint, ID or JavaScript contract change was found in the reviewed diffs. The legacy HTML/CSS changes still require explicit release approval because they modify protected scraper entry points.
- `resources/views/dashboard.blade.php` has no current route/controller reference; `/dashboard` returns `public/app/painel.html`. Its deletion is currently consistent with the active route, subject to production-reference verification.
- The production release commit remains unverified locally. Scope is not frozen and no files were restored or published.

### Production baseline and scraper compliance audit (2026-10-20)

- Read-only SSH verification resolved `kaizen-vps` to `kaizenmidias.com`; `current` points to `/www/wwwroot/app.kaizenmidias.com/releases/f5ffc7bd8693a4b6d80694b164d27041d7b258e7`.
- The release directory has no usable Git metadata, so its commit could not be independently verified. It contains `dashboard.blade.php` and redirects `/dashboard` to `/prospecting`, diverging from the local route that serves `public/app/painel.html`.
- Published hashes and visible attribution findings were recorded in `docs/implementation/release-candidate-scope.md`. Production still contains the `Estrela` and `Apoiar` links and retains the visible MIT attribution text in the prospecting footer.
- The local candidate is not equivalent to the active release. No server files, symlinks, services, databases or configuration were modified.

### Dashboard transition audit (2026-10-20)

- Local route mapping confirms `/dashboard` serves the protected original panel, `/leads` and `/pipeline` are protected modules, `/prospecting` remains protected, and login/logout use the existing session/CSRF flow.
- Local authentication and prospecting route tests passed: 10 tests, 26 assertions.
- Because the panel HTML resides under `public/`, direct web-server access could bypass Laravel middleware if Nginx serves `/app/painel.html` directly. Production Nginx isolation remains unverified.
- The transition remains NO-GO until direct static exposure, production/build divergence and the unresolved real HTTP session harness are addressed or explicitly accepted.

### Public panel security verification (2026-10-20)

- Read-only inspection of the active Nginx vhost confirmed root `/www/wwwroot/app.kaizenmidias.com/current/public` and a generic `try_files` fallback with no exclusion for `/app/painel.html`.
- Unauthenticated requests returned `302 /login` for `/dashboard`, `200 text/html` for `/app/painel.html`, and `302 /login` for `/prospecting`.
- Direct public exposure of the panel HTML is therefore confirmed. The response contained UI markup and protected API references, but no observed user-specific data, credentials, CSRF token or secret.
- The candidate remains NO-GO for this risk. No Nginx, application, permission, service or database change was made.

### Local correction of direct panel access (2026-10-20)

- Moved the single panel HTML source from `public/app/painel.html` to `resources/views/panel.blade.php` and changed `/dashboard` and `/painel` to render that view under the existing `auth`, `active`, and `verified` middleware.
- Preserved public CSS, JavaScript, images, scraper identifiers, endpoints, navigation and attribution text. No production or VPS files were changed.
- Authentication and prospecting route tests passed: 11 tests, 28 assertions. `git diff --check` passed without errors.
- Future packaging must exclude stale `public/app/painel.html` files from the release directory. Production remains unmodified and the security issue is not resolved there yet.

### Migration, backup and rollback audit (2026-10-20)

- Read-only production metadata confirmed MySQL 8.4.8, database `kaizen_partners`, 15 tables, and five recorded migrations through `2026_10_20_000002_add_polling_claim_token`.
- Production does not currently contain `pipeline_stages` or `pipeline_opportunities`; candidate migration `000003` and submission-intent migration `000004` are absent from the active release.
- `/usr/bin/mysqldump` and `sha256sum` are available. `/www/backups/kaizen-partners` exists, but no backup artifacts were enumerated; backup readiness is therefore unverified.
- Added `release-migration-audit.md` and `release-deployment-runbook.md` with the migration assessment, backup requirements, forward-only migration policy, rollback separation and stop conditions.
- No migrations, backups, restores, deploys, service restarts or configuration changes were performed.

### Backup readiness and isolated restore audit (2026-10-20)

- Read-only inventory found configuration/document-root snapshots and a SHA-256 manifest under `/www/backups/kaizen-partners`, but no MySQL dump or equivalent database backup.
- The latest listed artifact was a vhost configuration snapshot, not a database backup. Database integrity and recoverability are therefore unverified.
- Added `release-backup-readiness.md` with the evidence, isolated MySQL 8.4 restore plan, risks and stop criteria. The existing `kaizen-mysql-test` environment is explicitly excluded as a restore target.
- No backup, restore, container, volume, permission, migration, service or production change was performed.
