# Authentication, authorization, and security

Status: architecture proposal, based on a read-only local code audit on 2026-10-20. Nothing in this document changes runtime behavior. Production configuration and database contents were not inspected in this planning task.

## Existing implementation

| Concern | Evidence | Current behavior |
| --- | --- | --- |
| Identity | `app/Models/User.php`, users migration | One user owns an individual workspace. No organization, membership, team, or separate tenant model exists. |
| Login | `AuthController::login` | Validates email/password, checks password hash, active status, and verified email; regenerates the session; records `last_login_at`; redirects to the intended URL or `/dashboard`. |
| Logout | `AuthController::logout` | Logs out, invalidates the session, regenerates CSRF token. |
| Sessions | `config/auth.php`, `config/session.php` | Session guard `web`; driver defaults to database. Secure, SameSite, domain and path are environment-driven. Declaration in source is not evidence of live values. |
| Verification | `User` implements `MustVerifyEmail`; `VerificationController`; web routes | Signed verification URL and throttled resend exist. All verification routes require `auth`. |
| Registration | `AuthController::register`, `config/kaizen.php` | Creates only `partner` / `active`, sends verification notification. Flag defaults to true in source; deployed configuration must explicitly remain false until approved. |
| Password reset | `PasswordController`, password broker configuration | Laravel reset token/broker, hashed replacement password, rotated remember token. No account password is exposed to administrators. |
| Roles/status | `EnsureAdmin`, `EnsureActiveUser`, `User` | String roles `partner` / `admin`; statuses `active` / `blocked`. No permission package or role tables. |
| Route boundaries | `routes/web.php`, `routes/api.php` | Partner CRM surfaces use `auth`, `active`, `verified`; admin group adds `admin`. APIs use session-based web middleware. |
| Ownership | `LeadPolicy`, `ProspectingJobPolicy`, scoped controllers | Lead/job policies permit the owner only. Admin role does not automatically bypass these policies. |
| CSRF | web middleware; `EnsureFrontendCsrf` | Standard session CSRF and a custom XSRF comparison for legacy scraper writes. The custom middleware must be considered when integrating future endpoints. |
| Throttling | `AppServiceProvider` | Login: 5/minute per lowercased email and IP; registration: 3/minute per IP; reset request: 3/minute per email and IP. |
| Admin provisioning | `CreateAdmin`, `CreateAdminCommandTest` | Interactive password of at least 12 characters, admin/active, explicit verification assignment outside mass assignment. |

Existing schema contains unique `users.email`; nullable `email_verified_at`; password hash; role/status indexes; remember token; session table with indexed nullable user ID. Email normalization is not centralized: the CLI lowercases email, while registration/login/admin editing do not explicitly normalize it. Database collation behavior is not a substitute for a documented canonicalization policy.

## Concrete gaps found in code

1. **Verification onboarding loop.** Registration does not authenticate the new account. Verification links require authentication. Login refuses an unverified account before `Auth::login`. Therefore the implemented registration -> verification -> login path cannot complete normally. Existing tests verify row creation and denied dashboard access, not the complete signed-link lifecycle. Keep public registration disabled. A later security task should permit a restricted authenticated session for unverified active users, with only verification, resend, logout and explicitly approved account remediation accessible; protected modules retain `verified`. Do not remove verification middleware or silently verify ordinary users.
2. **Email editing preserves obsolete verification.** `AdminController::update` changes `email` without clearing `email_verified_at`. A later fix must normalize email, detect an actual address change, clear verification, send the new signed verification flow after commit, and audit the change. Ordinary partner profile editing must apply the same invariant.
3. **Last administrator protection is not one atomic invariant.** `update` counts administrators before updating without a transaction. `toggle` locks the target, but competing actions against different administrators have no single shared serialization rule. See [admin invariants](09-admin-panel.md#last-active-administrator-invariant).
4. **Reset response is not proven generic.** The test named `test_password_reset_request_is_generic_for_unknown_email` asserts only presence of status; `PasswordController::email` returns the broker's status translation. A future test must compare public response/message/shape for existing and unknown accounts before claiming enumeration resistance.
5. **Mass-assignment boundary needs discipline.** `User::$fillable` includes role/status for existing administrative workflows. Never expose generic partner `update($request->all())`; personal profile services must allowlist their own fields. Verification remains explicit and controlled.
6. **Ownership is not enforced by foreign keys alone.** For example an opportunity can technically reference a different user's lead/stage if inserted outside the checked controller. Every writer, job, importer and future automation must verify the same-owner relationship. No global admin bypass is implied.
7. **No MFA, recent-password gate, device-session management or recovery-code flow exists.** They are proposed hardening work, not available features.

## Proposed boundary: individual user ownership

Keep `user_id` as the workspace boundary for the MVP. The request explicitly excludes collaboration between employees of the same company. Adding tenant databases or a membership framework now would increase migration and authorization risk without an approved use case.

For each owned resource: select within the authenticated user's relation, then authorize the action. Do not retrieve globally and rely on frontend visibility. For a compound operation verify every related lead, stage, opportunity, attachment and communication account belongs to the same owner. Return 404 for another user's private resource where the existing controllers follow that convention. The user ID in a queued job is context to revalidate, not authorization supplied by the browser.

Private user operations remain owner-scoped even for an administrator. Central administration uses explicit, separately audited permissions; it does not imply access to every conversation or downloaded attachment. Future support access/impersonation requires a separately approved policy and is absent from this plan.

## Proposed permission matrix

| Capability | Visitor | Partner | Administrator |
| --- | --- | --- | --- |
| Login/reset request | Yes, throttled | Guest-only forms where appropriate | Same identity mechanism |
| Public signup | Only if explicitly enabled after verification tests | Not a privilege escalation path | Does not bypass flag via public signup |
| Verification remediation | Only through signed/authorized flow | Restricted unverified-session workflow proposed | Own account follows verification unless explicitly provisioned |
| CRM data | None | Own data; active and verified | Own workspace unless a separate administrative action is authorized |
| Profile/security | None | Own allowed fields | Own allowed fields |
| Account billing portal | None | Own customer only | Own customer; platform subscription administration is separate |
| User/plan/subscription administration | None | None regardless of plan | Explicit administrative middleware/policies, recent confirmation for sensitive actions |
| Role elevation/suspension | None | None | Validated, audited, final-admin invariant enforced |
| External webhook intake | Not session-authenticated | Not based on user login | Provider signature verification, durable deduplication, strict account/mode mapping |

Role, plan, payment state and entitlements are four separate concepts. A Pro partner never becomes an administrator. A blocked user remains blocked even when paid. Billing events must never set role or clear an administrative suspension.

## Proposed security controls

- Preserve CSRF for every browser mutation, including logout, profile, campaign and billing-session creation. Exempt only the exact signed provider webhook endpoint, never all APIs.
- Preserve APP_KEY across releases. Encrypt external credentials with Laravel encryption; do not log decrypted values. Plan key rotation and recovery before production credential storage.
- Keep secure/HttpOnly session cookies, SameSite policy and narrow cookie domain. Validate effective configuration and real HTTP behavior separately; do not infer it from `.env` examples.
- Apply output escaping to editable fields. Audit legacy HTML/JS sinks before injecting account names, stage names or lead content. Do not convert user content into trusted `innerHTML`.
- Validate website/download URLs and protocol allowlists. No arbitrary authenticated server-side fetch of user URLs without SSRF controls.
- Store uploads outside public webroot with random names, size/type validation and owner authorization on download. Never trust extension or browser-supplied MIME alone; no executable uploads.
- Separate queues for scraping, transactional notifications, billing and future communication. Use bounded timeouts and retry policy per integration, with no automatic replay of ambiguous external effects.
- Record security actions using safe identifiers and allowlisted metadata. Never include passwords, password hashes, session contents, cookies, CSRF tokens, webhook secrets or provider credential values.
- Use operational alerts for sustained queue failures, ambiguous scraping jobs, webhook lag, signature failures and anomalous authentication attempts. Metrics must not leak personal data.
- Retention/export/deletion rules and controller/processor responsibilities require an operator/legal decision. Do not present this technical plan as legal certification.

## Test destination protections: observed limitations

`phpunit.xml` forces SQLite `:memory:` and `phpunit.sqlite.xml` also forces testing/memory. The MySQL configuration and `tests/bootstrap/mysql.php` set isolated values. `TestingMigrate` checks declared variables and then database/current-user/internal-port identity. The raw concurrency worker has a different, weaker set of guards: it does not currently require the exact database name, exact username, effective server identity, or privilege verification.

Proposed hardening: one reusable test-destination validator, invoked before any `RefreshDatabase` setup, fixture creation or raw PDO worker write. Validate effective driver, resolved host/port/database/user, absence of unexpected `DB_URL`/socket/cached-configuration overrides, a dedicated server identity marker, and database-scoped grants. Fail closed instead of silently skipping a requested MySQL run. Negative guard tests must run in separate processes before migrations. A historical MySQL pass is not proof these guards cover every alternate entry point.

## Required acceptance evidence

- Full public signup/verification flow with HTTP/session semantics; blocked/unverified account isolation.
- Changed email invalidates prior verification and old signed link; role/status cannot be injected through profile/signup.
- Real CSRF rejection and secure cookies; password reset messages do not reveal account existence.
- IDOR tests for reads, writes, downloads, nested IDs and queued actions across every owned module.
- Concurrent final-admin demotions cannot leave zero active admins.
- Destructive test setup rejects wrong destination before touching a connection or schema.
- Fake provider APIs only; isolated SQLite and MySQL 8.4; no production data, credentials or scraping.

## Official references

Checked 2026-10-20: [Laravel 12 authentication](https://laravel.com/docs/12.x/authentication), [email verification](https://laravel.com/docs/12.x/verification), and [authorization](https://laravel.com/docs/12.x/authorization). Their session, signed-verification and policy mechanisms support the proposed reuse; project-specific invariants above remain implementation work.
