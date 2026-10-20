# Kaizen Partners

Kaizen Partners is a multi-user prospecting application built around the original Google Maps Scraper Kit interface. Phase 1 adds secure Laravel authentication, partner isolation, MySQL persistence, database queues, and an administrative foundation while preserving the original map, filters, results, panel, and exports as the visual reference.

## Phase 1 scope

- Laravel 12 in this repository root.
- MySQL database dedicated to this application.
- Session authentication, email verification, password recovery, and partner registration controlled by `REGISTRATION_ENABLED`.
- `admin` and `partner` roles with backend authorization.
- Prospecting jobs, leads, notes, exports, and admin audit records owned by users.
- Shared internal scraper container; browser clients never call its API directly.
- Stripe, subscriptions, credits, commissions, hosting, and commercial automation are deferred.

## Acceptance criteria

No partner can read or mutate another partner's jobs, leads, notes, or exports. Public registration always creates `partner` accounts. Administrators are created only by the local `kaizen:create-admin` command. Production services and databases are not changed by local development.
