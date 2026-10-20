# Release candidate scope inventory

Audit date: 2026-10-20. This document is an inventory and proposal only. No commit, push, deployment, migration, VPS access, or production change was performed.

## Reference and repository state

- Branch: `main`
- HEAD: `f5ffc7bd8693a4b6d80694b164d27041d7b258e7`
- Working tree: dirty; 61 status entries (32 tracked modifications/deletion and 29 untracked paths).
- A release reference commit was not supplied in the current workspace, so comparison is against `HEAD` only. The active production release cannot be verified from this local-only audit.
- Staged changes: none observed. Changes are unstaged or untracked.

## Classification inventory

### A — Functional production code

`app/Console/Commands/ReconcileScraperJobs.php`, `app/Http/Controllers/Controller.php`, `app/Http/Controllers/ExportController.php`, `app/Http/Controllers/LeadController.php`, `app/Http/Controllers/NoteController.php`, `app/Http/Controllers/ProspectingController.php`, `app/Http/Controllers/LeadPageController.php`, `app/Http/Controllers/PipelineController.php`, `app/Jobs/PollScrapingJob.php`, `app/Jobs/RunScrapingJob.php`, `app/Models/ProspectingJob.php`, `app/Models/User.php`, `app/Models/PipelineOpportunity.php`, `app/Models/PipelineStage.php`, `app/Policies/LeadNotePolicy.php`, `app/Policies/PipelineOpportunityPolicy.php`, `app/Policies/PipelineStagePolicy.php`, `app/Services/ScraperClient.php`, `app/Services/ScraperImportOutcome.php`, `app/Support/TestingTargetGuard.php`, `config/kaizen.php`, `routes/web.php`, `resources/views/layouts/scraper-shell.blade.php`, `resources/views/leads/index.blade.php`, `resources/views/pipeline/index.blade.php`, `public/leads.css`, `public/pipeline.css`, `public/pipeline.js`, `public/scraper-shell.css`.

### B — Migrations

`database/migrations/2026_10_20_000002_add_polling_claim_token.php`, `database/migrations/2026_10_20_000003_create_pipeline_tables.php`, `database/migrations/2026_10_20_000004_add_submission_intent_to_prospecting_jobs.php`.

### C — Operational/test configuration

`app/Console/Commands/TestingMigrate.php`, `phpunit.xml`, `phpunit.mysql.xml`, `tests/TestCase.php`, `tests/bootstrap/mysql.php`.

### D — Automated tests

`tests/Feature/AuthenticationTest.php`, `tests/Feature/LeadsPageTest.php`, `tests/Feature/MySqlPipelineConcurrencyTest.php`, `tests/Feature/MySqlProcessConcurrencyTest.php`, `tests/Feature/PipelineTest.php`, `tests/Feature/ProspectingRouteTest.php`, `tests/Feature/ScraperProcessingTest.php`, `tests/Feature/ScraperRegressionTest.php`, `tests/Feature/TenantIsolationTest.php`, `tests/Unit/TestingTargetGuardTest.php`.

### E — Harnesses and diagnostics

`tests/Support/mysql-concurrency-worker.php`, `tests/Support/mysql-worker.php`, `tests/Support/run-mysql-concurrency.php`, `tests/Support/http-controller-worker.php`, `tests/Support/run-http-controller-concurrency.php`, `tests/Support/real-http-f01.php`, `tests/Support/real-http-f01-worker.php`, `tests/Support/RealHttp/FixtureManager.php`, `tests/Support/RealHttp/HttpClient.php`, `tests/Support/RealHttp/HttpServer.php`.

### F — Documentation

`AGENTS.md`, `docs/architecture/` (all files), `docs/implementation/` (all files, including this inventory).

### G — Clearly unrelated

No file was safely classified as unrelated from names alone. The modified `AGENTS.md` and broad architecture/implementation documentation require an explicit scope decision before packaging.

### H — Requires decision before inclusion

`public/app/painel.html`, `public/app/painel.css`, `resources/views/prospecting.html`, and deleted `resources/views/dashboard.blade.php` require review. They are part of the original scraper or its entry points and cannot be included solely because they changed alongside F02/F03.

## Original scraper review

The following local/reference hashes were obtained with `git hash-object` and `git rev-parse HEAD:<path>`:

| File | HEAD reference | Local | Recommendation |
|---|---|---|---|
| `public/app/painel.html` | `f377e9f3b06e7af50e05802e1e9eec9009f01e45` | `176e8a361bb9ab2b26701f679fe5334dc922ecf6` | Review diff; exclude until approved |
| `public/app/painel.css` | `56a3ffcb6075413442690a01afe1276d94ee0fa9` | `db467d5cb9f45b256cf8ecac83846e9aad0d9fe9` | Review diff; exclude until approved |
| `resources/views/prospecting.html` | `1d9e95e0a7e91adbe1c68df370447cc909fbdf33` | `f2a243f97507ce9d7026b0eb0abe6bab74566057` | Review diff; exclude until approved |
| `public/app/painel.js` | `65d69a43048ad3aee6cbf419f7ff1b94af974374` | same | Unchanged |
| `public/app/style.css` | `0948ff97a99d235d17eb51bbda3784e49f78f541` | same | Unchanged |
| `public/app/app.js` | `2a4993a1fd0a6dd9b6e2d9d52e93399c3001824f` | same | Unchanged |
| `public/app/img/logo.png` | `3213cd4620fc775562139815ea628a2237129f74` | same | Unchanged |
| `public/app/img/favicon.png` | `01ef07fa21e78ac9498fa16bf68e25223dcdbe58` | same | Unchanged |

The changed scraper files have no approved reason recorded in this inventory. Their risk is visual or functional regression; inclusion requires an explicit diff review and preservation of legal notices.

## Migration inventory

`000002_add_polling_claim_token` adds nullable indexed `prospecting_jobs.polling_claim_token`; dependency: the existing `prospecting_jobs` table. It is additive and expected to be compatible with MySQL 8.4. Operational rollback is a forward corrective migration or restore procedure, never `down()` during production rollback.

`000003_create_pipeline_tables` creates `pipeline_stages` and `pipeline_opportunities`, user/lead/stage foreign keys, unique `(user_id,name)` and `(user_id,lead_id)` constraints, and position data. It depends on users and leads. Existing data is not altered by `up()`, but `down()` drops both tables and is destructive. Production requires backup and forward-only migration policy.

`000004_add_submission_intent_to_prospecting_jobs` adds indexed nullable `submission_intent_at` and unique nullable `submission_attempt_token`. It depends on `prospecting_jobs`; existing rows remain nullable. `down()` removes both columns and is destructive. Validate existing duplicate/non-null values before production execution.

## Proposed allowlist

### Required runtime candidates

Only after review: the functional files in category A that are proven part of the approved scope, the three migrations in B, required production assets/views, and the minimum configuration changes in C. The current inventory is not sufficient to freeze this list because scraper files and the deleted dashboard view remain unresolved.

### Optional/repository-only

Category D tests and category F documentation may remain versioned, but are not required in the production runtime package. They must not introduce secrets or test-only environment files.

### Review before inclusion

All category H scraper/entry-point changes, `AGENTS.md`, and any broad documentation changes outside the approved release record.

### Exclude from runtime package

All category E harnesses, workers, cookie jars, fixtures, logs, dumps, local `.env*` files, PHPUnit-only configuration, test databases, temporary artifacts, and development storage. No such secret values were printed or copied by this audit.

## Freeze blockers and decisions

1. Identify the exact production reference commit and compare against it.
2. Approve or reject each changed original scraper file.
3. Decide whether the deleted dashboard view is intentional and included in the navigation change.
4. Produce a production-only file allowlist separate from test tooling.
5. Verify migration compatibility and backup/rollback procedures in an authorized pre-production target.
6. Resolve the outstanding real HTTP/controller validation gap; its current 419 result is not evidence of an application defect, but it prevents release confidence.

The release is **not frozen** and is not ready for publication based on this inventory alone.

## Final scraper and dashboard review

### `public/app/painel.html`

The diff adds `/leads` and `/pipeline` navigation items and removes the visible GitHub “Estrela” and “Apoiar” actions plus the compact credit wrapper. The motor attribution text is removed from this panel, while the change does not alter scraper form fields, job controls, map IDs, result IDs, or API names in the shown diff. The navigation is functionally related to the CRM integration, but it is a direct modification of the legacy entry point. Recommendation: **REVISAR ANTES DE DECIDIR**; include only with explicit approval and navigation regression tests.

### `public/app/painel.css`

The diff removes only the `.credits.compact` and `.liquid .credits.compact` visual rules. This is coupled to the removed credit block and appears visual-only. Recommendation: **REVISAR ANTES DE DECIDIR**, together with the HTML change; it can be included if the credit removal is approved and visual regression is checked.

### `resources/views/prospecting.html`

The diff removes the two visual GitHub action links from the credit footer, while retaining the motor, author, MIT and installation-kit attribution text. No form, field name, ID, data attribute, scraper endpoint, map markup or JavaScript contract changes were found in the diff. This is visual-only and related to the previously authorized credit cleanup. Recommendation: **INCLUIR**, conditional on legal attribution review and a final hash/diff approval.

### Removed `resources/views/dashboard.blade.php`

No current route or controller references this view. `/dashboard` is a protected route returning `public/app/painel.html`; authentication redirects and tests target `/dashboard`, not the deleted Blade view. The file removal is therefore **INTENTIONAL AND CURRENTLY SAFE**, provided the release continues to use the static panel route and no historical deployment depends on the old view. It should remain deleted; do not package it as a runtime file.

### Release-reference limitation

No trustworthy local document identifies the production commit. The current HEAD is only the local reference. The active release must be confirmed separately before freeze; this audit did not access SSH or production.

### Decision

The scope still cannot advance to freeze. The HTML/CSS changes require operator approval as a legacy scraper modification, and the production reference commit plus final protected-file review are outstanding. No files were restored or otherwise modified by this review.

## Production baseline audit (read-only, 2026-10-20)

- SSH alias `kaizen-vps` resolved to `kaizenmidias.com`; the identity check reported user `root`.
- `current` resolves to `/www/wwwroot/app.kaizenmidias.com/releases/f5ffc7bd8693a4b6d80694b164d27041d7b258e7`.
- The directory name is commit-like, but the release has no usable `.git` metadata; the Git commit could not be independently verified.
- The active release contains `resources/views/dashboard.blade.php`, unlike the local workspace. Its `routes/web.php` redirects `/dashboard` to `/prospecting`, while the local route returns `public/app/painel.html`. This is a material production/workspace divergence.
- Published SHA-256: `public/app/painel.html` `f055b440210ecd8ac7d74f238868a93e752ecb37e23e1f08f7d1e2b98a302e31`; `public/app/painel.css` `8e96216daeb1c7c4ba279cf4c06d37bbef3e5db7d6ecc61db51700077a6070e3`; `resources/views/prospecting.html` `75b89adf838f6bb060c48be02aa5dba12d8fdfc2ea262f0515353f95df42c9f9`; `resources/views/dashboard.blade.php` `35b13b22c6313c9056dce43c619767c95593fe237fb96f51f64b6f147da8d9b7`.
- The published scraper still contains the `Estrela` and `Apoiar` links. Its prospecting footer retains Google Maps Scraper, author, MIT and installation-kit attribution text.
- Only a vendor Composer `LICENSE` file was found in the inspected release path. A complete project-level license/notice inventory was not established.

### Baseline conclusion

The local workspace does not match the active release, despite the release directory name resembling local HEAD. The scraper cleanup and dashboard route changes are not published. The candidate remains **NO-GO** until production build provenance and the intended dashboard transition are reconciled.

## Dashboard transition audit (local, 2026-10-20)

### Route matrix

| Route | Middleware | Authenticated result | Visitor result | Main dependency |
|---|---|---|---|---|
| `GET /` | redirect | redirects to `/dashboard` | redirects to `/dashboard` | route contract |
| `GET /login` | `guest` | login view for guests | HTTP 200 login view | auth views/assets |
| `POST /login` | `guest`, throttle | validates, regenerates session, redirects to intended or `/dashboard` | guest-only | CSRF/session/auth |
| `GET /dashboard` | `auth`, `active`, `verified` | serves `public/app/painel.html` | redirects to login | panel HTML and `/app/*` assets |
| `GET /prospecting` | `auth`, `active`, `verified` | serves original HTML and XSRF cookie | redirects to login | prospecting HTML/assets and protected API |
| `GET /leads` | `auth`, `active`, `verified` | renders Leads page | redirects to login | Leads view/CSS/API |
| `GET /pipeline` | `auth`, `active`, `verified` | renders Pipeline page | redirects to login | Pipeline view/CSS/JS/controllers |
| `POST /logout` | `auth` | invalidates session and redirects to `/login` | unavailable | CSRF/session invalidation |

Protected API routes use `web`, `auth`, `active`, `verified` and, where applicable, frontend CSRF middleware. Pipeline mutations also use controller policies.

### Findings

- `/dashboard` does not depend on `dashboard.blade.php` locally; login and verification redirects target it without a cycle.
- `/prospecting`, `/leads` and `/pipeline` remain separate protected modules.
- `public/app/painel.js` calls `/api/v1/jobs`; those routes are authenticated and CSRF-protected.
- The panel HTML is physically under `public/`. A web server serving `/app/painel.html` directly could bypass Laravel middleware. Production Nginx must therefore block or otherwise control direct static access; this was not verified locally.
- Module links do not bypass server-side protection.

### Local validation

`php artisan test tests/Feature/AuthenticationTest.php tests/Feature/ProspectingRouteTest.php` passed: 10 tests, 26 assertions. This covers guest protection, verified/active dashboard access, original file responses, module links, attribution retention and guest denial. It does not prove production Nginx isolation or real browser session continuity.

### Recommendation

The local transition is structurally coherent, but remains **NO-GO** until direct `/app/painel.html` exposure is checked in the production web-server configuration, the production/build divergence is reconciled, and the HTTP harness limitation is resolved or explicitly accepted.

## Public panel security verification (read-only, 2026-10-20)

### Nginx evidence

- The active vhost is `/www/server/panel/vhost/nginx/app.kaizenmidias.com.conf`.
- Its root is `/www/wwwroot/app.kaizenmidias.com/current/public`.
- `location /` uses `try_files $uri $uri/ /index.php?$query_string`.
- Static JavaScript/CSS/image locations are present, but there is no rule excluding `/app/painel.html` from direct static serving.
- The deployed file exists with mode `644`. No permissions were changed.

### Unauthenticated requests

Requests were made without cookies or authentication:

| Request | Result | Interpretation |
|---|---:|---|
| `GET /dashboard` | `302` to `/login` | Laravel middleware participates |
| `GET /app/painel.html` | `200`, `Content-Type: text/html` | Direct static exposure confirmed |
| `GET /prospecting` | `302` to `/login` | Laravel middleware participates |

The body of `/app/painel.html` contained the panel markup and no observed user-specific records, credentials, CSRF token, or secret value. It does contain client-side UI, labels and calls to protected API endpoints. The API routes remain guarded, so this is an interface exposure and middleware-bypass risk rather than proven private-data exposure.

### Recommendation

The direct static exposure is **confirmed**. The smallest safe correction should make the panel HTML unavailable as a public file while preserving its CSS/JavaScript/assets, then serve it through the authenticated Laravel route without duplicating markup. Candidate approaches include moving the HTML outside `public/` or adding a narrowly scoped web-server deny rule, with route and asset regression tests. Do not implement this correction in the current audit.

This risk is **NO-GO** for the candidate until corrected and verified with unauthenticated requests to `/dashboard`, `/app/painel.html`, and authenticated module flows.

## Local correction of direct panel access (2026-10-20)

- Cause: `public/app/painel.html` was directly reachable under the Nginx document root, bypassing Laravel middleware.
- The single panel document was moved to `resources/views/panel.blade.php`; no panel markup was reconstructed.
- `/dashboard` now renders the `panel` view through `auth`, `active`, and `verified`. `/painel` renders the same view and retains the same middleware.
- CSS, JavaScript, images, form IDs, navigation links, API paths and attribution text remain in their existing public assets or view markup.
- The public source path `public/app/painel.html` is absent from the local runtime tree. Future release packaging must also ensure that stale copies are absent from the new release directory; this local move cannot remove an old production copy.
- Updated tests cover guest denial, authenticated rendering, dashboard/panel content equivalence, module links, attribution retention, and source absence from `public/`.
- Local result: 11 tests passed, 28 assertions; `git diff --check` reported no errors. The real HTTP harness was not used.
- Production remains unchanged and the release remains NO-GO until the correction is packaged, deployed under an approved process, and verified with unauthenticated HTTP requests.
