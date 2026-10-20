# Local setup

Requirements: PHP 8.2+, Composer, MySQL 8-compatible server, Node/npm for the preserved frontend assets, and Docker only when the scraper integration is enabled.

1. Copy `.env.example` to `.env`.
2. Set a unique `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` for Kaizen Partners.
3. Set `APP_URL`, `MAIL_*`, `REGISTRATION_ENABLED`, and internal `SCRAPER_BASE_URL`.
4. Run `php artisan key:generate`.
5. Run `php artisan migrate` against an isolated development database.
6. Create the first administrator with `php artisan kaizen:create-admin admin@example.test --name="Kaizen Admin"`.
7. Run `php artisan serve` for local HTTP development.

The queue configuration is `QUEUE_CONNECTION=database`. Run `php artisan queue:work` only after the isolated database is configured. Never expose the scraper API directly to a browser or public interface.
