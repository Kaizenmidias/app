# Infrastructure and asynchronous processing

Status: architecture proposal, grounded in the local repository on 2026-10-20. No SSH connection, VPS inspection, configuration change, infrastructure installation, or service activation was performed for this architecture task. Reported production history is context, not proof of current server state.

Related: [current state](01-current-state.md), [security](04-auth-and-security.md), [testing](11-testing-strategy.md), [release strategy](12-release-strategy.md), [decisions](DECISIONS.md).

## Operational boundary

Keep a modular Laravel monolith, one dedicated application database, database-backed queues/cache initially, one scheduler owner, and a separately constrained Gosom service. Redis, Kubernetes, microservices, a mail server, and external automation runtimes are not prerequisites. Introduce another component only after measured contention or a provider requirement establishes a need.

The partner SaaS must not reuse the internal Kaizen CRM database, accounts, sessions, communication instances, credentials, storage, queues, or backups. Existing n8n and Evolution API installations are other projects, not automatically available integrations.

Historical topology supplied in the task:

| Component | Reported location or address | Required boundary before future execution |
| --- | --- | --- |
| Public application | `https://app.kaizenmidias.com` | Nginx serves only the approved release's `public/` |
| Releases | `/www/wwwroot/app.kaizenmidias.com/releases/<full-commit>` | Immutable code; a new directory per release |
| Active release | `/www/wwwroot/app.kaizenmidias.com/current` | Atomic symlink switch; verify actual destination first |
| Persistent application data | `shared/.env`, `shared/storage` under the application base | Private configuration; storage access limited to application roles |
| Database | `kaizen_partners`, MySQL on `127.0.0.1:3306` | Application-specific credentials and grants |
| Scraper installation | `/www/kaizen-partners/gosom/` | Dedicated network/volumes; no host project mounts |
| Scraper API | `http://127.0.0.1:18080` | Host Laravel calls loopback proxy; no public API listener |
| Historical MySQL test service | `kaizen-mysql-test`, `127.0.0.1:13306` | Test-only credentials/data; fresh identity audit before any use |
| Test workspace | `/www/kaizen-partners/testing/` or an explicitly isolated validation directory | Outside public root; no production environment/storage links |
| Backups | `/www/backups/kaizen-partners/` | Outside public root, protected, recoverable, with off-host copy |

These paths are not authorization to access or modify them. Current existence, listeners, permissions, resource headroom, and service status must be verified during an explicitly authorized operational phase.

## Existing processing implementation

The actual runtime code is in `app/Jobs/RunScrapingJob.php`, `app/Jobs/PollScrapingJob.php`, `app/Services/ScraperClient.php`, `app/Console/Commands/ReconcileScraperJobs.php`, and `routes/console.php`.

| Setting | Current code/default | Operational consequence |
| --- | --- | --- |
| Queue | `database`; queue name `default` unless configured | All current jobs share a queue by default |
| Job attempts | `3` on both jobs | Not configurable through `SCRAPER_MAX_ATTEMPTS` today |
| Backoff | `[30, 120, 300]` seconds | Actual queue retries need worker integration coverage |
| Job timeout | `75` seconds on both jobs | Includes HTTP, CSV processing, and persistence |
| HTTP timeout | `60` seconds | Status plus download can exceed one job budget in combination |
| Database `retry_after` | `120` seconds | Must remain greater than effective worker timeout |
| Cache lock | `120` seconds per polling job | No cache lock renewal is implemented |
| Persistent claim | reclaimable after `10` minutes | New UUID token on acquisition; claim timestamp renewal during import |
| Poll delay | `10` seconds | Reconciler can also enqueue polls |
| Logical scraping deadline | `3600` seconds | Current deadline behavior prevents later automatic terminal confirmation |
| Reconciler | every minute, `withoutOverlapping()` | Command also has a `120` second cache lock |
| Capacity | singleton `scraper_capacity` row | Initialized from `SCRAPER_CONCURRENCY` by the initial migration |

The runtime capacity value comes from the database row, not continuously from `config('kaizen.scraper.concurrency')`. Changing the environment variable alone does not resize an existing pool. Any future administrative capacity adjustment must be locked, audited, and must not reduce the pool below confirmed active work without a defined policy.

`RunScrapingJob` reserves capacity transactionally, makes one external creation request, persists its external identifier, and dispatches polling. Ordinary submission exceptions conservatively set `unknown` and retain capacity. `PollScrapingJob` accepts an external identifier, acquires a cache lock and persistent claim, checks remote state, and imports only a reported terminal success. Confirmed terminal failure or successful import releases capacity inside a transaction. Unknown external states continue polling.

Gosom endpoints used by the client:

- `POST /api/v1/jobs`, with stored parameters plus `name`, `keywords`, `lat`, and `lon`; expects a JSON `id`.
- `GET /api/v1/jobs/{external-id}`; accepts `Status/status` and `Progress/progress`.
- `GET /api/v1/jobs/{external-id}/download`; expects CSV.

The client supports an optional `X-API-Key` header. That support does not prove authentication is enabled on the installed proxy. There is no remote cancellation method in this client. The deployment's internal Gosom port, proxy timeout, resource limits, volume layout, image digest, and healthcheck are not represented by a production Compose file in this workspace; validate them before operational changes.

## Processing gate before enabling workers

The static audit found the following concrete gaps. Repair and prove them in an isolated environment in a separately approved implementation task; this architecture task does not change the code.

1. **Submission ambiguity:** `RunScrapingJob::failed()` releases capacity if no external identifier is stored. A hard worker timeout after an accepted POST but before identifier persistence is still ambiguous. No external identifier is not proof that no external job exists. Preserve capacity and record submission uncertainty until a verified reconciliation procedure resolves it.
2. **Deadline recovery:** the poller's maximum-duration branch exits before querying Gosom. An old `unknown` with an external identifier is requeued by the reconciler but hits the same branch indefinitely. Separate stopping new work from read-only external-state reconciliation; allow terminal confirmation after the local deadline without issuing another POST.
3. **Ownership atomicity:** the deadline branch uses a model update without a token condition; `failed()` selects a token-filtered row and then updates it separately. Replace these windows with conditional updates or a transaction that locks and validates the row at mutation time. Test stale callbacks after ownership changes.
4. **Renewal result ambiguity:** renewal writes second-resolution timestamps and interprets zero affected rows as lost ownership. A same-value MySQL update can return zero even for the legitimate owner. The importer then stops, while the poller still attempts `ok`. Reproduce with a multirow CSV on MySQL; distinguish unchanged renewal, ownership loss, incomplete import, and completed import explicitly.
5. **Cancellation is incomplete:** the HTTP controller sets `cancel_requested`, but neither a remote cancellation implementation nor reconciler recovery for that state exists. A subsequent delete can soft-delete active work. Define safe tombstone/cancellation semantics before exposing cancellation as completed.
6. **Budget mismatch:** a status request plus CSV download can each take up to 60 seconds inside a 75-second job. Benchmark supported CSV sizes, introduce a bounded step/checkpoint strategy when necessary, and retain external identity and ownership guarantees across interruptions.
7. **Import bounds:** CSV is loaded entirely in memory and split on newlines. Quoted multiline fields are not handled correctly. There is no row cap, rejected-row accounting, or persisted completion checkpoint. Streaming parsing and explicit import outcome are future repairs, not current features.

The existing SQL uniqueness constraint remains valuable, but is not a replacement for ownership fencing, an explicit state transition policy, or proof that an import completed.

## Worker and scheduler topology

Start with one dedicated scraping worker only after the processing gate passes. Use an application-specific Linux account, release/current working directory, PHP 8.4 binary, environment, process unit name, and log destinations. A future command aligned with current code is:

```text
<php-8.4> artisan queue:work database --queue=default --sleep=3 --tries=3 --timeout=75 --backoff=30,120,300
```

This is a planning example, not a command executed here. Production execution also requires the PCNTL extension/worker timeout behavior to be verified and a supervisor stop timeout that permits controlled shutdown. Do not adopt the old `--timeout=900` example in `docs/deployment-aapanel.md`: it conflicts with the current 120-second reservation and 75-second jobs. The same old checklist mentions `app:create-admin`, while the actual command is `kaizen:create-admin`; inspect executable signatures rather than copying obsolete runbooks.

Future billing, communication, and automation jobs should use separate named queues with individual worker/resource budgets after their modules exist. Scraping must not starve payment reconciliation or notifications. Keep retries bounded and provider effects idempotent. Avoid one shared all-purpose worker being the only path for every future subsystem.

Choose exactly one scheduler mechanism: an application-specific systemd timer running `schedule:run`, or one supervised scheduler process. Do not activate both or rely on global aaPanel cron without checking existing entries. Cache locks coordinate overlap only within the configured shared cache; multiple servers will need a shared lock backend before horizontal scheduling.

Deployment restart signals must target only this application's workers after a verified release switch. Changing the `current` symlink does not replace PHP code already loaded by a long-lived worker; stop old workers gracefully and supervise new processes. Confirm OPcache settings before deciding whether any PHP-FPM reload is necessary; never restart unrelated PHP pools automatically.

## Resource gates and growth

The conversation reports a historical host with 2 vCPUs and roughly 7.8 GiB RAM, plus Evolution API, n8n, main MySQL, PHP-FPM, and Gosom. These are not current measurements. Container limits are ceilings, not evidence of free capacity or predictable consumption.

Before any test container or new worker is authorized, obtain a fresh baseline: available memory, swap activity, CPU usage, CPU steal, load, disk space, disk latency, container limits/current usage/restart counts, and main MySQL/PHP-FPM health. Capture the same measurements during and after the change.

Proposed pilot gates, to be approved and calibrated against the baseline:

- Maintain at least 2 GiB host `MemAvailable` before starting an additional 1 GiB MySQL test container; use a stronger margin if measured browser peaks require it.
- Do not begin a scraper run or concurrency test with sustained swap-in/out, OOM events, unstable container health, or existing service degradation.
- On the historical 2-vCPU machine, investigate sustained load above available CPU capacity together with CPU saturation/latency; load alone is not a utilization percentage.
- Keep disk headroom for database growth, browser temporary files, logs, and at least one recoverable backup; measure bytes rather than assume an unused Docker volume is small.
- Define an interruption owner and stop only the newly authorized test/workload when memory, latency, OOM, restart, or error thresholds are breached. Preserve external-state uncertainty; do not free scraper capacity just to silence an alert.

For the optional MySQL test service, `docker-compose.mysql-test.yml` declares `mysql:8.4`, loopback `13306`, one dedicated network/volume, 1 GiB memory, 1 CPU, and mandatory external password variables. Its logical volume name is project-prefixed unless explicitly named. Existing Docker resources must be inventoried before starting it; no `down -v` or broad prune operation belongs in this workflow.

If browser scraping measurably threatens shared services, move that workload to a dedicated host before adding complexity to the application tier. Scale database/queue technology only after contention is measured. Do not connect to existing CRM n8n/Evolution networks to avoid adding a new deployment.

## Secrets, logs, uploads, and backups

- Keep production and test secrets outside Git and public directories; use owner/group permissions appropriate for the runtime account. Preserve `APP_KEY` across releases and back it up securely.
- Keep integration credentials encrypted at rest in future communication modules, with provider/account ownership and key rotation procedures. An encrypted database column does not replace secure key management.
- Use structured internal correlation identifiers for jobs, external request attempts, imports, webhooks, and audit records. Do not log passwords, API tokens, cookies, raw webhook secrets, message bodies, or full customer CSVs by default.
- Current logs use `config/logging.php` and `storage/logs/laravel.log`; the default stack may use a single file. Configure bounded retention/rotation and protected supervisor logs in an operational phase.
- Record counts and safe error categories for import success, rejected rows, claim loss, uncertain submission, queue age, retry count, and capacity discrepancies. Current code does not yet provide complete metrics for these cases.
- Store future uploads privately with size/type validation, randomized names, tenant authorization, malware policy where applicable, and authorized download controllers. Do not place private attachments under a public storage symlink.
- Back up the dedicated database, private environment/key material, persistent private uploads, approved release identity, and relevant per-site configuration. Encrypt off-host copies and define retention, recovery point, and recovery time with the administrator.
- Test restoration to an isolated database and storage destination before declaring backups usable. Code rollback and data recovery are separate procedures; restoring a symlink does not undo migrations.

## Operational acceptance for future modules

Before a module becomes publicly available: verify authenticated/authorized routes, migration compatibility, queue isolation, provider sandbox behavior, signed/deduplicated webhooks if used, observable failures, backup coverage, rollback/data recovery procedure, and host headroom. No module may silently connect to an existing service of another Kaizen project.
