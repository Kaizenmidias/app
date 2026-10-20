# Testing

Run `php artisan test` after Composer autoload is available. Authentication, verification, password recovery, active-user blocking, admin authorization, tenant isolation, MySQL migrations, scraper import, and protected exports require feature tests before the Phase 1 implementation is considered complete.

No production database or VPS service is part of local testing. Integration tests for the scraper must use a fake HTTP client or an explicitly isolated disposable service; they must not call Google Maps.
