# Administration architecture

Status: existing behavior audited on 2026-10-20; improvements below are proposals for later implementation. `/admin` is already the correct route boundary; creating a second admin product or separate database is unnecessary.

## Implemented versus missing

| Function | Existing code | Limits / planned extension |
| --- | --- | --- |
| Route protection | `/admin` group: `auth`, `active`, `verified`, `admin`; `EnsureAdmin` checks role/status | No granular admin permissions, recent-password confirmation or MFA implemented |
| Admin dashboard | `AdminController::dashboard`: total/active/blocked users, all leads/jobs | These global counts are admin-only. No financial/plan metrics exist. |
| User list | Name/email search, latest order, pagination 25 | No plan/status filters or detailed account timeline |
| Create user | Validated role/status/name/email; random hashed password; password-reset invitation | Invitation outcome not checked; no reliable complete email-verification onboarding flow |
| Edit user | Name/email/role/status update | Email verification is not cleared on address change; last-admin count is not atomic |
| Toggle status | Transaction and target-row lock | No common serialization rule with other role/status update paths |
| Password assistance | Laravel password-reset link | Does not expose/reset to plaintext; sending success/failure needs honest UI |
| Verification resend | Sends standard notification | Must be combined with a viable unverified-session flow |
| Audit log | `AdminAuditLog`, `admin_audit_logs` | Actor/action/target/metadata/time exist; safe before/after/outcome/correlation are not standardized |
| Plans/subscriptions | None | Future catalog, Stripe projection and entitlement exceptions |
| Sessions/deletion | None | No session revocation UI, soft deletion, anonymization or retention process |

`User::isAdmin()` checks a role string. This is an adequate starting point for one administrative role; do not add a permission package merely to build these screens. Introduce abilities/policies as concrete sensitive actions grow. A public request cannot supply an administrative role through signup; maintain that invariant in every future profile/import endpoint.

## Proposed administrative capabilities

### Dashboard

Reuse actual user/status/job/lead metrics. Add users by effective plan, subscription statuses, failed/pending billing inbox work and recent administrative changes only after their data models exist. Label counts with timestamp and scope. MRR, revenue, outstanding balance or conversion value must not be fabricated from plan names; financial KPIs need explicit currency, source, calculation and reconciliation decisions.

### User management

Allowlist fields per action; separate identity changes, role/status changes, entitlement grants and financial subscription actions. Provide read-only account details, safe audit history and supported session revocation. Admin actions should require server-side authorization, validated request data, CSRF, rate limiting, recent confirmation for sensitive operations and an audit record. Hiding a button is not authorization.

Proposed email change transaction: normalize address; lock subject; validate uniqueness; update only approved identity fields; clear verification on actual address change; audit safe before/after; commit; enqueue notification through durable after-commit work. Do not log verification URLs/tokens. Define notification to the previous address as a separate approved security policy. Prevent changing a verified identity while retaining verification of the obsolete address.

Suspension is an administrative state. A billing webhook never clears it. Blocked accounts cannot use protected module/API routes even if an old session exists. Future session revocation should remove only that subject's database sessions and rotate relevant remember tokens through a controlled service; do not flush global sessions/cache. Re-activation does not imply email verification or paid status.

### Plans and entitlements

Show four approved catalog names without invented prices or benefits. Allow audited editing of metadata/availability, validated Stripe mapping and explicit future entitlement values. Retain immutable keys/history and referential integrity. Exceptions require user, capability/limit, typed value, start/end, reason and actor. A commercial catalog edit must not silently change invoices or the provider subscription.

### Subscription management

Read the synchronized Cashier projection and clear last-sync time. Link safe provider identifiers to the appropriate Stripe environment. Proposed privileged commands (change/cancel/reconcile) go through the billing service, not arbitrary local row edits. Handle requests as pending until provider synchronization. Define proration, timing, refunds and delinquency before exposing controls. Financial truth remains Stripe; a complimentary entitlement grant is a different action.

Do not create a support impersonation feature or grant blanket access to conversations/attachments by default. Need and access policy must be approved separately. Existing owner policies intentionally have no automatic admin bypass.

## Last active administrator invariant

**Invariant:** after any committed role/status/deactivation/deletion operation, at least one active administrator remains. An administrator must not remove their own last administrative account, and simultaneous changes must not bypass the rule.

Current `update` performs a count and later write without a transaction. Concurrent requests can both observe another administrator and demote separate targets. `toggle` locks its target but does not share a single serialization boundary with `update`. The code therefore does not prove the invariant under every mutation path.

Proposed minimal implementation: central `AdminUserService` for every admin-set mutation, wrapped in a transaction. For this small single-role system, select all current admin rows in a stable ID order with `lockForUpdate`, then lock/check the subject, recalculate the resulting active set and perform the mutation plus audit. Re-read in locking/current-read context; do not rely on an earlier consistent snapshot. Require every demotion/block/deletion path to use the same protocol. A dedicated stable singleton row acting as a database mutex is an alternative if locking the candidate set cannot be proven safe; choose one protocol and test it rather than mixing them. Do not use `COUNT ... FOR UPDATE` as evidence that all relevant records were safely serialized.

Test with independent MySQL connections: two active admins concurrently demote/block different targets; self-demotion; ordinary partner promotion; concurrent invite/activation; failed audit write rollback; two entry points (`update` and `toggle`) competing. At least one remains active after every committed result, and rejected operation changes nothing. Keep audit actor available for attribution.

## Retention and deletion

Current `User` does not use soft deletes. Most CRM user foreign keys cascade, while `admin_audit_logs.admin_user_id` restricts deletion. Hard-deleting a user today can remove owned jobs/leads/notes/opportunities and conflict with audit history. No deletion screen/route currently exists; do not add one as a generic CRUD convenience.

Proposed first step is reversible account deactivation and session revocation. A later data lifecycle specification must cover customer cancellation separately from privacy deletion, retention windows, invoice/legal records, export requests, attachment cleanup, backups, automation pause, external provider disconnect and audit pseudonymization. Any soft-delete proposal needs unique-email/re-registration rules, query scopes and restoration tests; adding `deleted_at` alone is not a policy.

Never delete leads or opportunities because of subscription cancellation/nonpayment. Never cascade delete financial or audit history automatically. Irreversible removal requires explicit subject/administrator authority, confirmation, scheduled execution and recovery limits. Legal retention/LGPD policy is pending a competent operator/legal decision.

## Audit design

Extend/reuse `admin_audit_logs`; do not create a redundant second audit stream for the same purpose. Proposed fields: actor (nullable or pseudonymized under approved retention), action key, subject type/ID, outcome, request/operation correlation ID, safe before/after allowlist, reason and timestamp. Existing `metadata` can hold structured safe deltas until indexing needs justify columns. Record mutation and success audit in one transaction. Record rejected/failed security actions through a safe separate channel without pretending a transaction committed.

Exclude password/hash/token values, cookies, secrets, entire request bodies, full webhook payloads and unnecessary personal data. Never trust the browser to provide the actor. Audit access and retention are administrative privileges themselves. Prevent application-level editing of historical audit rows; stronger immutable storage is deferred until a concrete compliance need exists.

## Test evidence and required extensions

Existing `AuthenticationTest::test_partner_cannot_access_admin` proves one partner-denial path. `CreateAdminCommandTest::test_command_creates_verified_admin_with_hashed_password_once` checks one successful interactive account creation, role, active status, verification and hash. It does not execute a second duplicate command despite the test name; a real duplicate invocation regression is still required. `TenantIsolationTest` checks several private Lead/job/note/export paths. None of those tests proves concurrent final-admin preservation, safe email editing, session revocation, billing/plan operations or irreversible deletion.

Future acceptance: guest/blocked/unverified admin denied; active verified admin allowed; partner denied regardless of commercial plan; role injection rejected; wrong-subject/ID checks; exact allowlist changes; email re-verification; reset invitation success/failure disclosure; concurrency invariant; CSRF and confirmation for sensitive actions; audit transaction rollback; no secret serialization; retained data on cancellation/suspension. Use SQLite in-memory for routine HTTP behavior and isolated MySQL 8.4 for locking/FK/transaction guarantees.

## References and dependencies

Checked 2026-10-20: [Laravel authorization](https://laravel.com/docs/12.x/authorization) provides middleware/gates/policies used as building blocks. Read [authentication/security](04-auth-and-security.md), [plans](07-plans-and-entitlements.md), [billing](08-stripe-billing.md) and [database model](03-database-model.md) before implementing this module. Proposed profile/plan/subscription permissions are not live features.
