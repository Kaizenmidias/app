# Release deployment runbook (proposal)

This is a proposed procedure only. Do not execute it without a separate authorization and a named maintenance window.

## Preconditions

- Approved commit and production-only allowlist.
- Verified release/build provenance.
- Reviewed scraper and public-panel changes.
- Fresh memory, swap, load, disk, MySQL, PHP-FPM, Nginx, worker and Gosom baseline.
- Confirmed database identity, grants and pending migration list.
- Verified backup and restore rehearsal.
- Operator-approved rollback owner and RPO/RTO.

## Preparation

1. Record current `current` target, release hash, migration status and service state.
2. Create private database/configuration/release-manifest backups outside the document root.
3. Verify backup hashes and preserve the previous release.
4. Create an immutable `releases/<full-sha>` directory from the verified source.
5. Install Composer dependencies from `composer.lock` without development dependencies.
6. Link only the approved shared `.env` and storage; preserve `APP_KEY` and permissions.
7. Confirm the new release has no `public/app/painel.html`; the protected panel must exist only outside `public/`.
8. Run non-destructive syntax, view compilation and configuration checks.

## Migration window

1. Confirm the target is exclusively `kaizen_partners` on `127.0.0.1:3306`.
2. Confirm the backup is complete and verified.
3. Confirm `000003` and `000004` are the only approved pending migrations.
4. Apply forward migrations with the release's Laravel command, capturing output without secrets.
5. Re-run migration status and inspect tables, columns, foreign keys and unique indexes.
6. If a migration partially applies, stop. Preserve evidence and perform an approved forward repair; never run `down()` automatically.

## Activation

1. Validate `APP_ENV`, debug, URL, database, session, queue and cache settings.
2. Atomically switch `current` to the new release.
3. Restart only this application's workers if required; do not restart unrelated services.
4. Use `nginx -t` only if Nginx configuration changed; do not alter Nginx for this release unless separately approved.

## Smoke tests

- Anonymous `/dashboard`, `/prospecting`, `/leads` and `/pipeline` redirect to login.
- `/app/painel.html` is not publicly available.
- Login page and CSRF behavior are valid.
- Authenticated dashboard, Leads, Pipeline and scraper assets load.
- Owner isolation, migration status and queue configuration are correct.
- No 500 responses, stack traces, unexpected public registration or new critical logs.
- No scraping or outbound provider action is triggered by smoke tests.

## Rollback and recovery

1. Stop only affected new consumers and record in-flight/unknown scraping work.
2. Restore the previous `current` symlink atomically.
3. Verify old-code compatibility with the additive schema; do not run destructive `down()` methods.
4. Re-run smoke tests and inspect workers, queues and logs.
5. Restore the database only as a separately approved last resort after a write freeze and RPO/data-loss decision.
6. If restoration is required, use an isolated rehearsal-tested backup and reconcile external scraper state before resuming work.

Code rollback does not undo migrations or records created after activation.

## Stop conditions

Stop before activation for missing/invalid backup, uncertain database identity, migration drift, public panel exposure, failed configuration checks, degraded host services, unexplained 500s, or any need to touch unrelated projects.
