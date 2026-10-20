# Backup readiness and isolated restore plan

Audit date: 2026-10-20. Read-only inspection only. No backup, restore, migration, container or permission operation was performed.

## Inventory

The directory `/www/backups/kaizen-partners` exists. The inventory found configuration and document-root snapshots, including:

- `20261020-062440/`: Nginx backup, document-root snapshot, `manifest.txt`, `manifest.sha256`, `docroot.sha256`, and `nginx.sha256`.
- `20261020-085017-pre-migrate/`: vhost configuration, state file, project-root list and a protected environment snapshot.
- `20261020-090011-basic-auth/`, `20261020-090403-basic-auth-enabled/`, `20261020-090344-basic-auth-enabled/`, `20261020-090633-basic-auth-removed/`, and `20261020-091048-laravel-front-controller/`: vhost configuration snapshots.

No MySQL dump (`.sql`, `.sql.gz`, `.zst`, or equivalent) was found in the inspected backup tree. The most recent listed artifact was the `20261020-091048-laravel-front-controller` configuration snapshot; it is not a database backup.

The backup root is mode `755`. Individual configuration snapshots were mostly root-owned and mode `600`; the historical document-root snapshot contains files owned by `www`. The directory is outside the public document root, but the directory mode and environment snapshot require an explicit secrets review before use.

## Integrity evidence

- A SHA-256 manifest exists for the `20261020-062440` configuration/document-root snapshot.
- The manifest records commit identifier `bd1b8f3a63b11afbe02ee27c4bdc1acc349c2dc9`, document root and vhost path, with creation time `2026-10-20T06:24:40+00:00`.
- The hash file and manifest are readable as metadata, but no database dump is present to validate or restore.
- File existence and recorded hashes do not prove transactional consistency or recoverability of the production database.

Conclusion: **zero verified database backups found**. Configuration snapshot integrity is partially evidenced; database recovery readiness is not established.

## Isolated restore plan (not executed)

1. Obtain explicit authorization and a fresh, consistent `kaizen_partners` dump created with production-safe options.
2. Verify the dump format and size without exposing contents; verify its SHA-256 against a separately protected manifest.
3. Provision a new MySQL 8.4 container with a unique name, network, volume and database, never reusing `kaizen-mysql-test`, its volume or port `13306`.
4. Bind any temporary port to loopback only, enforce memory/CPU limits, and verify the container identity before import.
5. Import the dump into the isolated database using credentials stored outside logs and the repository.
6. Check table count, migration metadata, columns, indexes, foreign keys, constraints and representative row counts without exporting records.
7. Verify application read-only connectivity against the isolated target, never production.
8. Record restore duration, errors, checksums and structural results.
9. Stop and remove only the authorized temporary container, volume and network after preserving non-sensitive evidence.

The existing MySQL test database at `127.0.0.1:13306` is explicitly excluded because it contains its own fixtures and is not a restore target.

## Risks and prerequisites

- No recoverable production database backup is currently evidenced.
- The existing configuration snapshot includes an environment snapshot whose contents must not be copied or exposed.
- A configuration backup cannot restore database state.
- A code/symlink rollback cannot undo schema or data changes.
- Restore testing requires capacity headroom, an isolated target and a defined retention/RPO/RTO.

## GO/NO-GO

- Backup existence: **NO-GO** for database recovery; no dump found.
- Backup integrity: **NOT VERIFIED** for database; partial manifest evidence exists only for configuration/document-root artifacts.
- Isolated restore: **FEASIBLE WITH APPROVAL**, but not authorized or executed.
- Release publication: **NO-GO** until a verified database backup and isolated restore rehearsal exist.

Next required operation: obtain explicit authorization to create a production database backup, then verify and rehearse its restoration in a newly isolated environment.
