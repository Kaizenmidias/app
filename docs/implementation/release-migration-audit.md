# Release migration audit

Audit date: 2026-10-20. Read-only only; no migration, backup, restore, deploy or service restart was performed.

## Verified production baseline

- Host: `kaizenmidias.com` via the authorized `kaizen-vps` alias.
- Active symlink: `/www/wwwroot/app.kaizenmidias.com/current` -> `/www/wwwroot/app.kaizenmidias.com/releases/f5ffc7bd8693a4b6d80694b164d27041d7b258e7`.
- Production MySQL: MySQL 8.4.8, database `kaizen_partners`, host `127.0.0.1:3306`, 15 tables, 5 recorded migrations.
- The release currently reports migrations through `2026_10_20_000002_add_polling_claim_token`.
- `pipeline_stages` and `pipeline_opportunities` do not currently exist.
- The active release directory contains only migrations through `000002`; candidate migrations `000003` and `000004` are not yet present there.
- Disk: 63 GB available on the filesystem containing `/www` and `/www/backups`. Memory was approximately 2.8 GiB available and swap usage was low at inspection time; this is a point-in-time observation, not a deployment guarantee.
- Docker and system MySQL are active. The test MySQL container is loopback-bound to `127.0.0.1:13306`; it is not the production database.

No personal or commercial records were queried. `db:show --counts` was used only for metadata and table row counts.

## Migration compatibility

### `2026_10_20_000002_add_polling_claim_token`

This migration adds nullable UUID `polling_claim_token` and an index to `prospecting_jobs`. It depends on the existing prospecting table and is already recorded as applied in production. No action is required for this candidate migration.

### `2026_10_20_000003_create_pipeline_tables`

Creates `pipeline_stages` and `pipeline_opportunities`, with user, lead and stage foreign keys, cascade rules, unique `(user_id,name)` and `(user_id,lead_id)` constraints, and stage positions. It requires existing `users` and `leads` tables. It is additive for the current schema, but creates two new tables and may acquire metadata/table locks. It must be applied forward only after a backup and a schema preflight. Its `down()` drops both tables and is not an operational rollback.

### `2026_10_20_000004_add_submission_intent_to_prospecting_jobs`

Adds nullable indexed `submission_intent_at` and nullable unique `submission_attempt_token` to `prospecting_jobs`. Existing rows remain compatible because both fields are nullable. Before execution, check for naming/index conflicts and confirm the deployed application code understands the new fields. Its `down()` removes both columns and is not an operational rollback.

## Backup assessment

The server has `/usr/bin/mysqldump` and `sha256sum`; `/www/backups/kaizen-partners` exists with mode `755`, but no backup artifacts were enumerated by the read-only directory inspection. Available disk space is sufficient for planning, but backup size and retention cannot be inferred from free space.

Required backup scope before publication:

1. Consistent dump of only `kaizen_partners`.
2. Private `shared/.env` and `APP_KEY` backup with restricted permissions.
3. Active symlink target and release manifest.
4. Relevant vhost/service configuration if it will be changed.
5. SHA-256 and independent integrity verification.
6. Restore rehearsal into an isolated database and storage destination.

Do not copy production data into the local workspace. Do not expose dump paths or credentials in logs.

## Migration risks

- MySQL DDL can implicitly commit; a partially applied migration must be inventoried and repaired forward, not blindly retried.
- The new unique constraints can fail if pre-existing duplicate data is introduced before migration; current production has no pipeline tables, so the direct conflict is unlikely for `000003`, but still requires preflight.
- `000004` unique nullable token behavior must be confirmed against the exact MySQL collation and existing indexes.
- Code rollback does not reverse schema changes or data created after activation.
- Long-lived workers must be checked for compatibility with the expanded schema before restart.

## GO/NO-GO

- Schema preflight: **GO with conditions**; metadata is available, but candidate code/migrations are not deployed.
- Migration execution now: **NO-GO**; explicitly outside this audit.
- Backup readiness: **NO-GO** until a verified backup and restore rehearsal exist.
- Production release: **NO-GO** until code scope, public-panel correction, HTTP validation and rollback are approved.
