# Architecture

Laravel 12 serves the application and the preserved frontend assets. MySQL stores application data and Laravel's queue tables. The scraper remains a shared Docker service on an internal network; Laravel is the only planned caller. The original scraper's Docker volumes remain internal implementation storage, while imported leads become tenant-owned MySQL records.

Authentication uses Laravel's web session guard, hashed passwords, CSRF protection, email verification, and active-user middleware. Authorization must be enforced with Policies and server-side user ownership checks before scraper integration is enabled.

`REGISTRATION_ENABLED` is read from `config/kaizen.php` and enforced in both route rendering and request handling. `DB_DATABASE` must identify a dedicated Kaizen database; do not reuse another system's database.
