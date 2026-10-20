# Rollback plan

Before production migration, snapshot the application files and take a verified backup of the dedicated Kaizen MySQL database. Record the current release identifier and queue state.

For an application rollback, stop new deployments, set the site to maintenance mode, stop the new queue workers, restore the previous release files, restore only the matching database backup when a migration cannot be reversed safely, clear cached configuration/routes/views, and restart the previous worker command. Verify login, tenant isolation, and read-only health before reopening traffic.

Do not use `migrate:fresh`, `db:wipe`, `docker compose down -v`, or a broad database restore on shared systems. The scraper volumes are separate and must be preserved unless their own backup and rollback are explicitly approved.
