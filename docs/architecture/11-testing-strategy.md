# Testing strategy and evidence boundaries

Status: test architecture based on inspected local files on 2026-10-20. No test, migration, Docker service, SSH command, provider request, or production operation was executed as part of this architecture task. Historical pass counts reported in the conversation are historical evidence, not a fresh run of this working tree.

Related: [current state](01-current-state.md), [infrastructure](10-infrastructure.md), [release strategy](12-release-strategy.md), [decisions and risks](13-decisions-and-risks.md), [roadmap](14-implementation-roadmap.md).

## Existing configurations

| File | Observed configuration | What it can establish |
| --- | --- | --- |
| `phpunit.xml` | Forces `sqlite` and `:memory:`; array cache/session; synchronous queue | Isolated application feature behavior and SQLite schema; not InnoDB concurrency or worker behavior |
| `phpunit.mysql.xml` | Dedicated host `127.0.0.1`, port `13306`, database `kaizen_partners_test`, user `kaizen_test`; array cache/session; sync queue | MySQL feature behavior when independently validated; not the real database queue/cache/session lifecycle |
| `tests/bootstrap/mysql.php` | Copies fixed test variables into process environment, `$_ENV`, and `$_SERVER` before autoload | Resolves environment source consistency for that configuration; does not validate a remote server identity by itself |
| `tests/TestCase.php` | Unextended Laravel base test case | No centralized pre-write connection guard currently exists here |
| `docker-compose.mysql-test.yml` | Optional MySQL 8.4 service; dedicated loopback port/network/volume; external secrets | Declared test infrastructure; does not prove the live container configuration |
| `tests/Support/run-mysql-concurrency.php` | External Linux/PHP/PDO process orchestrator with ready/go files | Independent SQL transaction concurrency for its own scenarios |

The SQLite configuration protects `database/database.sqlite` by forcing `:memory:`. Future tests must preserve this invariant, and verify an existing demo row/database fingerprint before and after any local regression that also runs a development server. Never clear the local development database to make a test pass.

The MySQL configuration currently includes all feature tests. Tests with `RefreshDatabase` can recreate or migrate the isolated schema. Calling a test suite is therefore a write operation even when an individual test method appears read-only. No MySQL command in this document is permission to operate on a live server.

## Current tests: what is and is not covered

| Area | Existing evidence in files | Coverage boundary / required next evidence |
| --- | --- | --- |
| Public registration role | `AuthenticationTest::test_public_registration_creates_only_partner` | Verify disabled registration separately; never grant admin through registration input |
| Verification and admin authorization | `AuthenticationTest::test_unverified_user_cannot_access_dashboard`, `test_partner_cannot_access_admin` | Broaden negative matrix across all new modules |
| First administrator | `CreateAdminCommandTest::test_command_creates_verified_admin_with_hashed_password_once` | No real credentials; keep official command behavior explicit |
| Frontend preservation | `ProspectingRouteTest` | File response identity and selected static markup; not browser rendering, asset behavior, or an authenticated live FPM request |
| Leads listing ownership | `LeadsPageTest` | One-row feature fixtures; pagination boundaries/search combinations need explicit assertions |
| Job/Lead/note/export isolation | `TenantIsolationTest`, selected `ScraperRegressionTest` methods | Direct-ID denials exist; add all verbs and new resources as they are introduced |
| Pipeline stage lifecycle | `PipelineTest::test_stage_lifecycle_and_isolation` | Sequential requests; not concurrent stage creation/rename/delete |
| Pipeline idempotency and transfer | `PipelineTest::test_duplicate_send_and_safe_transfer` | Sequential duplicate submission and one transfer; not concurrent controller execution |
| Pipeline destination authorization | `test_other_user_stage_is_rejected_on_move` | Covers foreign destination; broaden source/opportunity/HTTP method matrix |
| Pipeline complete reorder set | `test_reorder_requires_the_complete_owned_stage_set` | Covers normal set and foreign ID rejection; new concurrent stage insertion still needs a defined policy |
| Completed import | `ScraperProcessingTest::test_polling_imports_completed_csv_idempotently`, `ScraperRegressionTest::test_completed_poll_clears_persistent_claim_and_repeated_poll_is_noop` | One valid CSV row, repeat poll; not multirow renewal, interrupted imports, multiline CSV, or a worker process |
| Remote failure | `ScraperProcessingTest::test_polling_marks_remote_failure` | Fake terminal failure; no ambiguous submission timeout coverage |
| Unknown preserves capacity | `test_failed_poll_preserves_capacity_when_external_state_is_unknown` | Direct callback with no competing ownership; not stale callback race or run-job hard timeout |
| Token protection | `test_stale_claim_token_cannot_update_new_owner`, `test_claim_renewal_requires_current_token` | Hand-written conditional query assertions, not all actual job paths or real lost-claim import |
| Claim recovery | `ScraperRegressionTest::test_active_claim_prevents_second_poll_and_expired_claim_is_recoverable` | Sequential fake clock/state alteration; does not leave worker A active while B takes over |
| Reconciler | `test_reconcile_requeues_unknown_with_external_id_without_posting`, `test_reconcile_does_not_requeue_unknown_without_external_id` | Dispatch assertions only; not end-to-end terminal recovery beyond logical deadline |
| Capacity release | `test_capacity_release_is_idempotent_after_completion` | Sequential repeats on terminal failure; not simultaneous completion/failure callback |
| Missing external ID | `test_lines_without_external_identifier_are_ignored` | One rejected row; no rejected-row accounting or complete import outcome |
| MySQL structure | `MySqlConcurrencyTest::test_mysql_schema_has_unique_lead_key` | Asserts index name exists, not exact index columns/FKs/collation/engine |
| Row lock | `test_mysql_for_update_serializes_capacity_row` | One connection locks a row; the name overstates actual multi-connection proof |

Reported historical results were SQLite 43 passed / 109 assertions / 9 MySQL-only skipped, and isolated MySQL 51 passed / 144 assertions. Preserve the original output/commit identity in a future verification record. Do not label skipped tests as passed or use these counts as proof of untested scenarios.

## External process concurrency harness

`MySqlProcessConcurrencyTest` launches the external runner for `capacity`, `claim`, and `unique`. `MySqlPipelineConcurrencyTest` launches it for `pipeline_send`, `pipeline_move`, `pipeline_delete_move`, and `pipeline_reorder`.

The runner creates controlled fixtures, starts two independent PHP processes using `setsid`, waits for both ready files, opens a shared go barrier, observes results, and deletes its fixture records. Each child creates its own PDO connection. The runner currently uses Linux shell utilities and is not a portable Windows concurrency harness. Child-side additional sleeps are not a substitute for the ready/go barrier.

Crucial limitation: these children execute **hand-written SQL**, not authenticated Laravel requests or production service methods. Specifically:

- The claim SQL changes `polling_claimed_at` without testing UUID-token ownership, renewal, token handoff, `failed()`, or stale-worker import fencing.
- The Pipeline send SQL proves its unique index conflict, not controller conflict handling and user authorization under concurrent requests.
- The delete/move worker takes locks that the production `destroyStage()` path does not take. Passing this scenario does not prove that controller deletion is safe against a late insert or move.
- The reorder worker updates in different orders and does not execute the controller's stable-ID lock acquisition. A controller lock fix and a SQL-worker pass are distinct evidence.
- The runner resets the global test capacity row. It must not run beside another suite using the same test database.
- Fixture creation, timeout exits, and cleanup are not wrapped in a complete parent-level `finally` cleanup path. Crash handling, child reaping, secret-file cleanup, and residual fixture detection need hardening.

Keep useful SQL-level tests, but add process workers that bootstrap the actual application and invoke the real action/service or HTTP route. Observe separate process IDs and MySQL `CONNECTION_ID()` values, barrier readiness, outcomes, final invariants, and rollback. Do not copy production SQL into tests and then infer that the implementation was exercised.

## Mandatory destination guard before any destructive test setup

The existing guard is a starting point, not a complete safety boundary. `MySqlConcurrencyTest::setUp()` calls `parent::setUp()` before its guard, while `RefreshDatabase` setup can already run migrations. The standalone worker checks fewer values than the runner. `testing:migrate` checks declared variables and `DATABASE()`, `CURRENT_USER()`, internal port, but does not independently validate every effective Laravel connection value, grants, or a unique server identity. `DB_URL`/socket/config-cache overrides also need consideration.

In a future hardening task, centralize a reusable pre-write guard and invoke it **before** `RefreshDatabase`, schema creation, fixtures, cleanup, or migration calls. It must:

1. Require `APP_ENV=testing` and explicit `MYSQL_TEST_ENABLED=true` for MySQL.
2. Require the exact approved database/user/host/port: `kaizen_partners_test`, `kaizen_test`, `127.0.0.1:13306` for this environment, with `MYSQL_TEST_DATABASE` equal to that exact database.
3. Reject production names, unexpected `DB_URL`, socket, secondary connections, cached production configuration, and any inferred fallback connection.
4. Inspect the effective Laravel connection driver and resolved host/port/database/user, not environment strings alone.
5. Validate an authenticated read-only identity probe: effective database, account, MySQL version/UUID, internal container port, and an out-of-band verified association to the dedicated container. Port 3306 inside the container is expected; it is not proof of the host mapping.
6. Validate privileges restricted to the dedicated test schema and required test operations; never reuse an application/CRM account or a global root account for tests.
7. Require a separate explicit destructive-test opt-in for schema recreation, accepted only after the guard succeeds. Prefer a newly provisioned disposable schema when the resource is not proven empty/disposable.
8. Reuse the validated connection configuration in child processes, with secrets passed via protected environment/file mechanisms and never command arguments or reports.
9. Fail closed before connecting to any invalid destination. Invalid-config tests should inject controlled configs/fakes, not attempt network access to real production endpoints.
10. Record only a sanitized identity/result and verify post-run cleanup. Never print passwords, hashes, tokens, complete session cookies, or raw provider payloads.

For SQLite, force `:memory:` before Laravel application bootstrap and before any trait can run setup. Multiprocess SQLite tests require a newly allocated exclusive temporary file, explicit ownership, and narrow cleanup; they must never use `database/database.sqlite`. The MySQL guard does not replace the SQLite invariant.

## Processing regression gate

Before expanding SaaS functionality or enabling scraping workers, add the following targeted tests in a separately approved task:

- Multirow CSV imported within one second on MySQL: valid renewal must not truncate imports or mark incomplete work `ok` when an update is unchanged.
- Quoted commas/newlines, extra/missing cells, blank `place_id` with valid `cid`, missing identifiers, duplicate identifiers, and malformed CSV. Count accepted/updated/rejected records explicitly.
- Worker hard timeout/crash after accepted external POST before local external-ID persistence: capacity stays held and no retry creates another POST.
- Completed external job beyond the local deadline: read-only reconciliation can reach a safe terminal state without resubmission.
- Worker A's claim expires while A remains active; B obtains token B; A cannot update progress, import another row, finalize, free capacity, clear B's claim, or mutate state via `failed()`/deadline handling.
- Import interrupted midway by a worker timeout: retry preserves the ID, deduplicates already-written rows, completes remaining rows, and releases capacity once only after terminal import completion.
- Reconciler versus scheduled poll; duplicate queued polls; stale `failed()` callback after terminal completion/new owner; actual database cache locks across independent processes.
- Cancellation while queued, submitting, running, importing, or unknown: no premature free/soft-delete and no false claim that remote work stopped.
- Actual queued-worker lifecycle with database reservation/retry timing (`75 < 120`, cache lock 120, claim 600), supervised process exit, retry backoff, and restart. Fake HTTP must remain in use; no Google Maps job is required.
- Request-level MySQL Pipeline concurrent creation/reorder/send/move/delete, including insert into a stage between pre-count and deletion. Assert no opportunity loss under `cascadeOnDelete` and no unhandled deadlock/unique error.

Tests should assert durable business invariants and execute production paths. They must not merely repeat a SQL condition that the test itself wrote. A deterministic barrier or transaction synchronization should expose the critical interleaving; arbitrary sleeps alone are insufficient.

## Layered strategy for the planned SaaS

1. **Fast isolated unit tests:** entitlement precedence, plan/role distinction, state mapping, webhook normalization, consent decisions, recurrence/timezone calculations, and automation loop budgets.
2. **SQLite feature tests:** authentication, IDOR and all-verb ownership, CSRF, request validation, navigation, state/error responses, and HTML escaping. SQLite proves application behavior, not MySQL locking.
3. **MySQL 8.4 feature/integrity tests:** migrations, exact indexes/FKs, collation-sensitive uniqueness, transactions/rollback, lock behavior, and application actions with the real engine.
4. **Separate-process concurrency tests:** actual application services/requests for quotas, capacity, claims, Pipeline, webhook deduplication, billing state ordering, and automation idempotency.
5. **Provider contract tests:** fake responses and official sandbox events for Stripe, WhatsApp, and email. Record event shape/version and signature behavior without secrets. No paid charge, campaign, or live message belongs in a default suite.
6. **Worker/scheduler integration tests:** dedicated database queue/cache, deterministic clock/fixtures, isolated processes, supervised crash/restart behavior, and exactly-once effects built on idempotent handling. Queue deliveries themselves may repeat.
7. **Browser accessibility/visual checks:** approved Design System, real font loading, keyboard/focus, desktop/mobile layouts, Kanban DnD plus accessible fallback, form errors, session/CSRF, and assets. If browser tooling is unavailable, record the limitation; do not invent screenshots or computed-font evidence.
8. **Authorized release smoke tests:** HTTPS/cookies, guest/partner/admin boundaries, known routes, sensitive-file protection, registration state, data isolation, and logs. Do not use real credentials in automation or initiate scraping/campaigns as a smoke test.

For Stripe specifically, test signed raw-body verification, bad signature, duplicate event ID, different events for one object, out-of-order delivery, retries, provider retrieval failure, reconciliation, free-plan flow without Checkout, cancellation without data deletion, and no access based only on a browser success redirect. Provider-specific details remain subject to the selected integration's official documentation.

## Evidence record and completion checklist

Each future implementation step must record:

- Exact commit/base and changed-file set, environment/configuration chosen, sanitized connection/server identity, and executed commands.
- Passed tests, assertions, skipped tests with reasons, failures, and expected versus unexpected results separately.
- Which production methods ran, which HTTP calls were faked, and whether real processes/connections or only sequential calls were used.
- Pre/post fixture counts, local demo preservation, protected scraper hashes where relevant, secret/temp-file cleanup, and no uncontrolled external side effects.
- Resource baseline/peak and other-service status for any explicitly authorized VPS test; stop criteria and interruption outcome.
- Remaining uncovered cases and the next gate. A green result cannot waive an untested security or concurrency boundary.

For this documentation-only phase, the completion evidence is static review and documentation validation. It does not supersede the future implementation/release test gate.
