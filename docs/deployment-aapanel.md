# aaPanel deployment checklist

This checklist is preparation only. It does not change a VPS.

## Order of publication

1. Create a dedicated Linux user, application directory, MySQL database, MySQL user, and database credentials for Kaizen Partners. Do not reuse another Kaizen system.
2. Configure PHP 8.4 (Laravel 12 compatible), required extensions (`pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `json`), Composer, and a process supervisor for the queue worker.
3. Deploy the repository outside the public web root. Point the aaPanel site root to Laravel's `public/` directory; never point it at the repository root.
4. Configure `.env` with a unique `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://app.kaizenmidias.com`, secure cookies, MySQL settings, SMTP settings, `REGISTRATION_ENABLED`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database` or shared Redis, and an internal scraper URL.
5. Before opening the domain, start the internal Docker service `gosom/google-maps-scraper:v1.18.1` on a private Docker network. Do not publish port 8080 publicly; set `SCRAPER_BASE_URL` to its internal address.
6. Run `composer install --no-dev --optimize-autoloader`, `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`, and `php artisan migrate --force` only after backup and explicit production approval.
7. Run a queue worker under supervision: `php artisan queue:work database --sleep=3 --tries=3 --timeout=900`. Configure a conservative worker count. Run `php artisan schedule:work` under supervision or configure aaPanel cron every minute with `php artisan schedule:run`.
8. Enable HTTPS, secure cookies, log rotation, backups, and monitoring. Verify the site, queue, scheduler, database, scraper health and protected `/prospecting` route before opening registration.

## Backup and rollback

Back up the dedicated database and `.env` securely before migrations. To roll back application code, restore the previous release directory and clear `config`, `route`, and `view` caches. Database rollback must use a tested migration rollback or database backup; do not run destructive commands blindly.

## First administrator and smoke test

Create the first administrator with `php artisan app:create-admin` using an interactive terminal. Then verify login, email verification, `/prospecting`, job creation, queue processing, CSV download and blocked-user behavior. Never store administrator credentials in the repository.

Never commit `.env`, SMTP credentials, scraper keys, database dumps, queue payloads, or production logs.
