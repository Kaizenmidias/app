# Current-state audit

Inspected 2026-10-20, local working tree at base `f5ffc7bd8693a4b6d80694b164d27041d7b258e7`.
No SSH, provider requests, database queries, test execution or configuration-secret inspection was needed for this audit. Runtime claims below are historical, not newly verified.

## Inventory and implementation status

| Area | Evidence | Actual behavior / boundary |
| --- | --- | --- |
| Runtime | [composer.json](../../composer.json), [composer.lock](../../composer.lock) | PHP requirement ^8.2; target PHP 8.4; locked Laravel v12.70.0, Guzzle 7.16.0, Symfony HttpFoundation v7.4.21. No Cashier, Stripe SDK, Sanctum, team/tenancy or permission package |
| Frontend toolchain | [package.json](../../package.json), [views](../../resources/views/layouts/scraper-shell.blade.php) | Vite/Tailwind/Axios dev tools exist; inspected product pages load direct public CSS/JS, not a SPA/build entry |
| Auth | [AuthController](../../app/Http/Controllers/AuthController.php), [User](../../app/Models/User.php) | Session guard, hash checking, regeneration, active/verified login gate, partner-only registration, remember/logout |
| Email lifecycle | [VerificationController](../../app/Http/Controllers/VerificationController.php), [PasswordController](../../app/Http/Controllers/PasswordController.php) | Signed verification and password-broker flows exist; mail delivery/account configuration not verified |
| Administrator | [CreateAdmin](../../app/Console/Commands/CreateAdmin.php), [AdminController](../../app/Http/Controllers/AdminController.php) | Official interactive creation, user listing/search/create/update/status/verification/reset actions and basic aggregate admin page |
| Partner dashboard | [web routes](../../routes/web.php), [panel HTML](../../public/app/painel.html) | /dashboard and /painel return the same static original panel via BinaryFileResponse; current panel metrics summarize scraping data, not a full CRM |
| Map | [prospecting HTML](../../resources/views/prospecting.html), [app.js](../../public/app/app.js) | Original Leaflet map/search/history/results frontend using legacy /api/v1/jobs adapter |
| Leads | [LeadPageController](../../app/Http/Controllers/LeadPageController.php), [LeadController](../../app/Http/Controllers/LeadController.php) | Owner-scoped paginated web list (20), search name/phone/address; JSON list (25), show/update/delete. No separate contacts model or detail/edit web page |
| Notes/export | [NoteController](../../app/Http/Controllers/NoteController.php), [ExportController](../../app/Http/Controllers/ExportController.php) | Owner-scoped note mutations and CSV endpoints; export audit row created by GET. No complete notes management UI |
| Pipeline | [PipelineController](../../app/Http/Controllers/PipelineController.php), [models](../../app/Models/PipelineOpportunity.php), [UI](../../resources/views/pipeline/index.blade.php), [JS](../../public/pipeline.js) | Personal stages, name/edit/order/delete/transfer, one opportunity per user+lead, drag-and-drop and select fallback. No won/lost/value/forecast/history |
| Scraping | [Run](../../app/Jobs/RunScrapingJob.php), [Poll](../../app/Jobs/PollScrapingJob.php), [client](../../app/Services/ScraperClient.php) | Async submission/status/download/import, capacity singleton, persistent polling claim+token, cache lock, terminal finish |
| Scheduler | [console routes](../../routes/console.php), [reconciler](../../app/Console/Commands/ReconcileScraperJobs.php) | scraper:reconcile every minute with overlapping prevention; dispatches eligible pending/working/unknown external jobs |
| Test safety | [PHPUnit SQLite](../../phpunit.xml), [MySQL](../../phpunit.mysql.xml), [TestingMigrate](../../app/Console/Commands/TestingMigrate.php) | Separate configs and test-only scripts exist, but effective-target/ordering gaps remain (chapter 11) |
| SaaS billing | File/dependency/migration inventory | Plans, subscriptions, usage accounting, entitlements, Stripe webhooks, Checkout and Portal absent |
| Future CRM | File/model/route inventory | Chats, connected accounts, campaigns, email marketing, automations, agenda, briefings, profile management absent; wa.me/mailto links are not messaging integrations |
| Events/listeners | app directory inventory | No application Events or Listeners directories; framework notifications exist, but no durable domain-event or automation bus |

## Route and middleware map

[web.php](../../routes/web.php) registers /login, /register, forgot/reset-password, verification routes and POST /logout.
Partner routes /dashboard, /painel, /prospecting, /leads and /pipeline use auth + active + verified.
Pipeline mutation endpoints are POST /pipeline/stages, PATCH /pipeline/stages/{stage}, POST /pipeline/stages/reorder, DELETE /pipeline/stages/{stage}, POST /pipeline/leads/{lead}, PATCH /pipeline/opportunities/{opportunity}.

[api.php](../../routes/api.php) is mounted under /api by [bootstrap/app.php](../../bootstrap/app.php).
Session-based API routes explicitly apply web/auth/active/verified; legacy /api/v1/jobs and /api/prospecting paths also use custom frontend.csrf.
Lead JSON, notes and exports are not public APIs merely because they live in api.php.
Admin uses /admin with auth/active/verified/admin middleware. Backend ownership checks currently dominate; existing LeadPolicy and ProspectingJobPolicy must not be mistaken for universally invoked policies.

## Existing schema (six migration files; schema-derived, not a live inventory)

| Migration | Tables / alteration |
| --- | --- |
| 0001_01_01_000000_create_users_table | users, password_reset_tokens, sessions |
| 0001_01_01_000001_create_cache_table | cache, cache_locks |
| 0001_01_01_000002_create_jobs_table | jobs, job_batches, failed_jobs |
| 2026_10_19_000001_create_prospecting_tables | scraper_capacity, prospecting_jobs, leads, lead_notes, prospecting_exports, admin_audit_logs |
| 2026_10_20_000002_add_polling_claim_token | nullable indexed polling_claim_token on prospecting_jobs |
| 2026_10_20_000003_create_pipeline_tables | pipeline_stages, pipeline_opportunities |

Seventeen tables including Laravel migration metadata are expected after all files apply. This does not establish the current production schema. See [model](03-database-model.md).

## Concrete discrepancies and technical debt

1. **Verification onboarding dead end.** register sends verification without logging in; login rejects unverified users; verification URL requires auth. New partners cannot reach the signed verification endpoint through the normal current flow. Keep registration closed until B01.
2. **Administrative email change retains verification.** AdminController::update does not invalidate email_verified_at on address change. Last-admin count checks are not a shared atomic protocol across update/toggle. E01/B01.
3. **Unsafe occupied-stage deletion race.** destroyStage checks opportunity count and destination before its transaction, without locking the source stage. New opportunities can appear between check and cascade deletion. F01 must test actual application path.
4. **Panel mutation CSRF contract mismatch.** painel.js POST/DELETE fetches do not send X-XSRF-TOKEN/_token while custom frontend.csrf demands one. Read paths can work while writes fail. A01 must reproduce using actual browser HTTP.
5. **Legacy navigation and payload mismatch.** Original Mapa/open-in-map links point at /, now redirected to /dashboard; /classic has no route. Numeric API IDs conflict with string dataset/hash IDs in strict-equality frontend paths. legacyStore drops submitted lang/zoom/fast_mode. Job-list HTTP success is labeled scraper online without worker/provider health proof. CSV completo is reconstructed with eleven fields. Static public/app/painel.html can bypass Laravel shell auth under normal static serving; it has no server-rendered private data. See chapter 06 for exact contracts.
6. **Unknown recovery deadline.** Poll checks elapsed max_duration before contacting Gosom. Reconciler includes unknown+external ID but hits the same elapsed check forever; scraper:unknown only lists. F02 needs safe status-only recovery without another POST.
7. **Ambiguous submission and stale duplicate delivery.** Run::failed releases a slot on missing external ID, although hard death after accepted POST but before saving its ID is ambiguous. Missing ID is not evidence that no external work exists. Run::handle does not revalidate terminal status/external ID on the locked current row after a prior model read; a paused duplicate can reserve after original completion and POST again. The code window requires deterministic actual-job reproduction.
8. **Ownership windows.** Poll timeout writes through an old model; failed() selects by token then independently updates. These paths lack the atomic token condition used in progress/finalization.
9. **Heartbeat no-op and false completion risk.** Poll renewal writes now()+1 second and treats affected rows==1 as ownership proof. Multiple same-second renewals can be no-ops in MySQL. Import breaks on false, yet Poll marks ok without a completion result. F03 needs multirow real-MySQL service tests; not a verified live incident.
10. **Incomplete cancellation.** cancel_requested is not a remote cancellation command, reconciler excludes it, repeated delete can soft-delete work still active. No cancellation acknowledgement protocol exists.
11. **CSV/import limits.** Whole HTTP body loaded; CSV split by line cannot handle embedded newlines; no result cap/import report/resume cursor. Null external IDs are skipped. updateOrCreate uniqueness is helpful but not complete import correctness proof. Latest import rewrites the lead's single source-job relation.
12. **Request/resource validation.** search_parameters accepts arbitrary arrays; legacy lat/lon and keyword values lack complete semantic bounds; no dedicated submission rate limiter. Runtime concurrency env value does not rewrite seeded scraper_capacity.
13. **Frontend data links.** Escaping HTML attributes does not validate URL schemes. Provider-derived website/link values need safe http(s) allowlists; CSV export needs formula-injection protection.
14. **Test fidelity and target guard gaps.** External concurrency workers execute handwritten PDO SQL, not the controllers/jobs. MySqlConcurrencyTest calls framework setup before its own guards; DB_URL/cached effective config is not fully guarded. Green SQL examples do not validate production-path safety.

These are code findings and test limitations, not claims of exploitation or current production incidents. No corrective implementation was made in this architecture task.

## Coverage evidence and limits

Historical prior task: SQLite 43 passed / 109 assertions / 9 MySQL-only skipped; isolated MySQL 51 passed / 144 assertions. These counts were not rerun here. A skipped MySQL scenario is not a pass. No new browser test, load test, real queue test or production validation is implied.

Existing classes: AuthenticationTest, CreateAdminCommandTest, TenantIsolationTest, ProspectingRouteTest, LeadsPageTest, PipelineTest, ScraperProcessingTest, ScraperRegressionTest, MySqlConcurrencyTest, MySqlProcessConcurrencyTest, MySqlPipelineConcurrencyTest. The two ExampleTest classes include smoke/trivial checks, not business guarantees.

## Reuse and simplification

Keep controllers and Eloquent. Extract small action/service classes only for transactions, shared authorization and external effects that need direct concurrent testing. Reuse Lead, notes, Pipeline and the official login/DS assets. Introduce one shared shell, a small navigation registry, a billing adapter and event outbox incrementally. Do not rewrite the application or replace Gosom.
