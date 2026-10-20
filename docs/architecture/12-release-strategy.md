# Proposed release and recovery strategy

This is a future execution plan. No commit, push, deploy, migration, worker or provider activation is authorized by this document.

## Release inputs

An approved task implementation, reviewed diff, clean scoped candidate, exact commit SHA, reviewed migration list, test artifact IDs, environment manifest without secrets, protected frontend hashes and an approved operational window. Existing uncommitted work must be reconciled and reviewed; this architecture task neither freezes it nor declares it safe to deploy.

Update legacy runbooks only through an authorized task. Their historical worker timeout=900 and app:create-admin examples do not match current code. See [infrastructure](10-infrastructure.md).

## Expand, validate, activate

1. Audit current origin/release, PHP extensions, MySQL version/target/grants, Nginx paths, service state, memory/swap/load and dedicated Gosom listener. Never rely on historical VPS observations.
2. Create private timestamped backups outside document root: dedicated database, shared configuration, release manifest, active symlink target and affected vhost/service files if changes are authorized. Preserve APP_KEY. Encrypt off-host copies and verify SHA256/archive integrity. Rehearse restoration in a separate database; define acceptable RPO/RTO with the operator.
3. Prepare a new immutable releases/<full-sha> as the dedicated deployment user. Obtain only versioned code from the verified remote commit, install from composer.lock without dev dependencies and reviewed scripts. No credentials in Git or command arguments.
4. Link shared/.env and shared/storage; keep bootstrap/cache per release. Protect private uploads and use stable shared storage/app/private with authorized download routes. Do not share compiled views across incompatible releases without an explicit cache policy.
5. Verify effective production configuration, dedicated database identity, auth/registration flags, queue/cache/session prefixes and PHP-FPM user permissions. Freeze new writes to affected module only where migration semantics require it.
6. Apply only approved forward, additive migrations after schema/status checks. Avoid changing historical migrations already used in production. MySQL DDL can implicitly commit: a failed multi-statement migration can leave partial schema; inspect and resume safely, never retry blindly. [MySQL documentation](https://dev.mysql.com/doc/refman/8.4/en/implicit-commit.html).
7. Run CLI/framework/read-only DB validations, compile views, test via isolated loopback Nginx/PHP-FPM if authorized, compare protected files and test cache behavior.
8. Atomically replace current symlink. Test PHP-FPM realpath/opcache behavior; do not assume CLI OPcache invalidation affects FPM. No shared-service restart without need and scope approval.
9. Anonymous smoke: login 200, valid CSRF fake-account POST passes CSRF, missing CSRF fails, protected routes redirect, registration remains disabled, sensitive paths blocked, assets 200, Secure/HttpOnly/SameSite cookies and no stack traces.
10. Operator performs real authenticated smoke. Verify owner-scoped dashboard/Leads/Pipeline and administrator routes, correct database schema and no new errors. Do not automate a real password.
11. Enable each implemented module gradually behind operational flags; paid checkout, campaign sends, scraper workers and scheduling require their own explicit authorization. Registration is not enabled merely because billing exists.
12. Monitor correlation IDs, oldest queued age, unknown scraping jobs, capacity ledger consistency, webhook age/failures, DB locks, memory, swap and existing services; retain previous releases and backup manifest.

## Initial Pipeline migration checkpoint

The existing 2026_10_20_000003_create_pipeline_tables creates stages and opportunities only; its up() is additive. F01 may require additional safety but must not rewrite deployed history. Confirm users/leads prerequisite tables; inspect actual unique/FK indexes, collation and zero orphan rows in test and production target. There is no automatic instruction here to execute it.

## Code rollback

Quiesce only affected new consumers/features; record in-flight work and unknown external effects. Atomically restore the preceding symlink and the matching per-release caches. Check old code compatibility with expanded schema, sessions and queue payloads. Restore vhost only if it actually changed; validate nginx -t before a separately authorized reload. Do not touch CRM, n8n, Evolution or unrelated Docker services.

## Database recovery is separate

A code rollback does not revert schema, data, billing or outbound messages. Prefer forward-compatible additive migrations and a forward repair when safe. Restore a verified database backup only after operator approval of write freeze, RPO/data loss, affected tables/DB and external state reconciliation. Test restore before replacing the dedicated database; never restore all shared MySQL databases. Do not run destructive down(), migrate:fresh, db:wipe, reset or broad volume removal in production.

For a half-applied migration, inventory tables/indexes/migration metadata, preserve evidence, create a corrective forward migration or approved repair. Do not mark it applied without structural proof.

Stripe/provider effects must be reconciled from provider truth after restore; an old database snapshot must not re-send payments/messages or reactivate old event claims. Preserve provider idempotency keys and event receipts where recovery requires them.

## Release rejection criteria

Unresolved F01–F03 safety issues, test guards running after mutation hooks, cross-user access, unverified signer/provider mode, unavailable backup restore proof, unexplained skipped critical tests, unapproved commercial values, DS conflict not resolved for a visual release or unknown rollback compatibility. A visually approved screen and a green SQL harness alone do not satisfy release acceptance.
