# CRM module contracts

Status: planning only. The existence classifications below come from source inspection, not a new production validation. All future paths, fields, events and provider integrations are proposals until their phase is authorized. No prices, feature tiers, usage limits, communication provider, retention period or legal basis is decided here.

## Common contract

The twelve modules belong to one Laravel monolith. Keep controllers thin, introduce focused domain services where HTTP/worker/command paths must share transactions, use the existing database queue initially, and add events only when an actual consumer exists. Do not introduce microservices or mandatory Redis.

- Every partner route requires `auth`, `active`, `verified`; each write also requires applicable CSRF protection, validated input and owner authorization. A subscription/entitlement layer is separate and future.
- A partner account sees its own `user_id` records, including when that actor also has admin role. Global management lives behind `/admin` authorization.
- Create and mutation operation IDs must survive retries for external effects. Jobs receive immutable identifiers and load/authorize current state; never trust a user ID submitted by the browser.
- Server owns state, filters and pagination. A hidden menu item is not authorization, a successful GET is not proof that POST works, and frontend limits are not provider or resource limits.
- Proposed events include `event_id`, `user_id`, aggregate ID/version, `occurred_at`, correlation/causation identifiers and a versioned minimal payload. Publish after successful transaction; add an outbox only when required to make downstream processing durable. Never include provider secrets, session tokens or full message bodies by default.
- No module may share production database, credentials, storage, sessions, communication connections or queues with the internal Kaizen CRM, Evolution API or n8n without a separate reviewed authorization.

## Actual public/legacy API contracts

These are useful compatibility seams to retain while consolidating the product UI:

| Contract | Current behavior | Gap or limit |
| --- | --- | --- |
| `GET /api/prospecting/jobs` | Owner-scoped jobs, newest first, paginate 20 | Different shape from legacy list; frontend must not swap without adapter. |
| `POST /api/prospecting/jobs` | keywords array required; optional location/coordinates/search_parameters; creates pending job, dispatches RunScrapingJob; 202 `{id,status}` | Nested search parameters are not a future approved entitlement/quota schema. |
| `GET /api/prospecting/jobs/{job}` | Owner check then loads leads | Entire associated lead list, not paginated in this response. |
| `GET /api/v1/jobs` | Owner-scoped all jobs mapped to Go-style `ID/Name/Date/Status/Data` | Unpaginated legacy compatibility; numeric ID/type mismatch needs adapter review. |
| `POST /api/v1/jobs` | keywords array, lat/lon and integer max_time required; optional depth/radius/email/name; creates pending job; 201 `{id}` | Current validation drops fields not in rules, including frontend `lang`, `zoom`, `fast_mode`; do not claim they are honored end-to-end. Lat/lon are only required, not numeric-validated here. |
| `GET /api/v1/jobs/{job}` | Owner-only legacy status JSON | Unknown/cancel states are not fully represented in legacy UI language. |
| `GET /api/v1/jobs/{job}/download` | Owner-only CSV rebuilt from persisted Leads, 11 columns including ratings from raw_data | Not original Gosom CSV with all columns despite “CSV completo” UI label. Association can change on re-import. |
| `DELETE /api/v1/jobs/{job}` | pending/working/unknown -> `cancel_requested`, HTTP 202; other states soft-delete | Cancellation request is not proof external job stopped; reconciliation currently ignores cancel_requested. |
| `GET /api/leads` | Owner-scoped paginate 25 | No search/filter parameters implemented here. |
| `GET/PATCH/DELETE /api/leads/{lead}` | Owner check; show loads notes; PATCH permits name,phone,email,website; DELETE hard-deletes | Delete cascades opportunity/notes; no detailed web editor or delete UI exists yet. |
| `POST /api/leads/{lead}/notes`; `PUT/DELETE /api/notes/{note}` | Owner-checked CRUD, content max 10,000 chars | Notes API exists; notes UI/history not integrated into Leads screen. |
| `GET /api/exports` | Owner export records paginate 25 | No dedicated export-history UI. |
| `GET /api/prospecting/jobs/{job}/export` | Owner CSV download, records ProspectingExport | Download has database side effect on GET; future audit/export design should make this explicit. |
| `/pipeline/stages`, `/pipeline/leads/{lead}`, `/pipeline/opportunities/{opportunity}` | Web session/CSRF forms; stage create/update/reorder/delete; lead send; opportunity move | Not a comprehensive versioned JSON CRM API; retain web contracts unless a real consumer needs one. |

Sources: [`routes/api.php`](../../routes/api.php), [`ProspectingController`](../../app/Http/Controllers/ProspectingController.php), [`LeadController`](../../app/Http/Controllers/LeadController.php), [`NoteController`](../../app/Http/Controllers/NoteController.php), [`ExportController`](../../app/Http/Controllers/ExportController.php), [`PipelineController`](../../app/Http/Controllers/PipelineController.php).

## 1. Overview — `/dashboard`

**Existing:** `/dashboard` and `/painel` serve the same `public/app/painel.html`. `painel.js` reads the owner legacy job list, downloads each successful job's CSV, computes counts/phone/email/category statistics and renders results/maps/export controls. These are actual client-calculated search statistics, not CRM-wide persisted metrics or financial indicators. The “Scraper online” indicator only proves the jobs API responded, not that a worker/Gosom is available.

**Proposed useful MVP:** preserve approved panel patterns while transitioning the overview to owner-scoped aggregates from `leads`, `pipeline_opportunities`, `pipeline_stages`, `prospecting_jobs`. Show total leads; opportunities grouped by current stage; searches by real status; latest owned searches. New activity/appointment indicators remain absent until those entities exist. A label such as “open opportunities” is not justified until lifecycle status exists; currently all opportunities have only a stage.

**Contract:** server aggregation constrained by authenticated user, scoped time window only when timestamps support it, explicit count definitions, no summed potential amount presented as earned revenue. Do not compute global counts or silently count duplicate per-search occurrences as unique leads. Handle zero data with real next actions and no sample values.

**Dependencies/tests:** shared shell, owner query service, Leads/Pipeline. Tests cover user A/B counts, empty account, stage grouping, no unimplemented indicators and exact compatibility of `/painel`. Overview is read-only except explicitly labeled actions. No new table required initially.

## 2. Prospecting — `/prospecting`

**Existing:** original map HTML, vanilla JS, Leaflet/OpenStreetMap tiles, Nominatim geocoding, job history, result filtering/maps/exports; Laravel legacy API maps jobs to queue-backed scraping; `RunScrapingJob`, `PollScrapingJob`, `ScraperClient` persist deduplicated owner Leads. Import happens automatically on successful external completion, not via a separate manual “import selected” action.

**Partial/unproven:** creation/deletion from panel lacks required CSRF headers; map navigation links assume old root; frontend fields `lang/zoom/fast_mode` do not survive `legacyStore` validation. CSV rebuilt by Laravel is narrower than the original scraper CSV. Unknown/cancellation UX and safe recovery require the lifecycle audit. Legacy UI declares approximate quantity/depth, not a hard maximum. No plan quotas, resource reservations per user or proved provider health endpoint exists.

**Proposed integration:** one shared sidebar, no Mapa/Painel switch, map fills the remaining content area; retain existing search/filter/results behavior through isolated legacy adapter. Do not embed an iframe or rewrite Gosom. UI must show pending, working, unknown, cancel_requested and terminal outcomes honestly. Plan-resource policy may be introduced later after business limits are approved; global scraper concurrency remains an infrastructure safety control, separate from entitlements.

**Data and errors:** owner job -> external ID -> polling/claim -> lead import. Ambiguous external failure retains capacity and never repeats an unknown POST automatically. A “cancelled” result requires external confirmation or safe documented administrative recovery; no guessed slot release. Partial CSV/import retries must remain idempotent and preserve provenance. Raw provider errors are sanitized.

**Acceptance:** fake HTTP tests for payload, ID normalization, CSRF-bearing create/delete, status transitions, cross-owner list/status/download/delete; import retry and claim ownership tests; browser tests for map sizing/deep links/mobile with no real scraping. See [navigation strategy](05-navigation-and-design.md).

## 3. Leads — `/leads`

**Existing:** `LeadPageController` uses `$request->user()->leads()->with('job')->latest()`, search `q` across name/phone/address, server pagination 20. Blade shows lead name, source/latest search location, phone, website as text, address, captured timestamp and send-to-Pipeline POST. A no-results state exists. API supports read/edit/delete and notes, with owner checks. Export is per search, not a bulk filtered lead export.

**Proposed minimum extension:** add authorized detail/edit/notes UI reusing those endpoints, optional explicit filters only on real fields, clear send status/error. Reuse `Lead` as the business prospect; do not create a duplicate Company table simply to rename it. A future `Contact` represents a distinct person only when communication requires it; `lead_contact` links companies/people, and `contact_channels` owns editable communication addresses without scalar phone/email mirrors on Contact. Opportunity references Lead rather than copying editable company data.

**Duplication/provenance:** existing UNIQUE(user_id,external_place_id) handles imported known IDs. NULL IDs allow multiple rows; UI/manual merge must not blindly dedupe by name/phone. Import `updateOrCreate` updates last search association; historical multi-search membership is absent. Introduce `lead_search_occurrences` before promising immutable original-job exports/history; `lead_tags`/`lead_tag_assignments` are the canonical future tag tables. Lead tags/merge/import are future capabilities, not present.

**Acceptance:** owner list/detail/update/delete/notes, grouped search conditions cannot escape owner scope, pagination, no other-user IDs, HTML escaping of imported strings, validation of website schemes before making external links clickable, safe delete disclosure for linked opportunities, idempotent Pipeline send, success/error state. No new migration needed for connecting existing editor/notes UI; tags/history need additive schema.

## 4. Pipeline — `/pipeline`

**Existing:** private stages with validated nonblank names <=80 characters, duplicate-name check/UNIQUE, create/rename, whole-set reordered positions in transaction with stable ID row locking, occupied-stage transfer/delete, one opportunity per user/lead, move by AJAX or select, vanilla drag/drop, HTML dialogs and confirmation UI. No fixed stages are imposed. First stage by position is default destination; no stages produces actionable empty state.

**Concrete gaps:** stage delete checks before transaction and can race; simultaneous stage creates can duplicate positions; duplicate-name validation can race into SQL error; default stage can disappear before send; SQL external concurrency harness does not exercise the same controller paths. These need targeted repair/tests before another production safety claim. Drag/drop moves DOM only after successful response and has a request-busy guard, but does not update column count text or dialog opportunity counts until reload; server remains correct while those visual counts can be stale.

**Proposed extension:** first consolidate stage mutations in one transactional service/lock order; add movement history in same transaction; lifecycle `open/won/lost`, optional potential value/currency/expected close date/loss reason and linked activities in a later approved additive migration. Stage name is not lifecycle state. No responsible-party feature until collaboration exists; owner is current user. Existing one opportunity per lead is intentional MVP cardinality; multiple deals per lead needs explicit product decision and index migration.

**Acceptance:** service-level concurrent stage create/rename/reorder/delete/send/move, rollback preservation, no silent opportunity loss; same-user and foreign-user IDs; no-stage send blocked; duplicate send idempotent; keyboard/select and mobile fallback; reload counts/persistence; no deadlock errors presented as success. External tests must call shared production service rather than duplicate SQL algorithm.

## 5. Chats — proposed `/chats`

**Not implemented:** no models, routes, chat views, messaging events/provider client or real-time transport found.

**MVP contract after provider approval:** authenticated owner inbox; conversations linked to a connected communication account and optional same-owner contact/lead; paginated thread; inbound messages from validated webhook inbox; outbound message command with stable operation ID; delivery state with provider-authoritative transitions; explicit pending/error UI. Store sent/received time and provider ID; never infer delivery/read from successful HTTP submission.

**Dependencies/data:** communication_accounts, conversations, messages, provider event inbox, consent/preferences and private attachment service if needed. Reuse native queue/polling first; evaluate real-time delivery from measured latency/load, not an assumed websocket dependency. No team assignment in first version. Conversation access requires owner and connected account; webhook maps provider IDs using verified account mapping, never user_id supplied by payload.

**Failure/acceptance:** duplicate/out-of-order webhook, unknown send result, expired connection, blocked attachment, cross-owner thread/download, reply dedup and opt-out. Disable automatic retry where it might duplicate a provider send without idempotency/reconciliation. No provider or credential is chosen here; see integration decisions before implementation.

## 6. Connected numbers — proposed `/connections`

**Not implemented:** no connection-management layer exists. Evolution API on the same VPS belongs to another system and is not an approved reusable backend.

**MVP contract:** user-owned provider/account connection with provider identity/status, verified connection handshake, capability flags, encrypted secret/reference, last synchronization/error, disconnect confirmation and safe token revocation. The connection wizard follows the provider's supported authentication flow; do not invent QR/session support for all providers. Signature-validated webhooks have unique account mapping and idempotent event receipt.

**Dependencies/data:** communication_accounts/integration_events; provider adapter interface separating connect/status/send/revoke/webhook normalization. Official Meta/WhatsApp routes and unofficial session-based providers require explicit comparison of support, permitted usage, account security, policy/blocking risk, cost and operator burden; selection is decision-gated. Same-host availability is not consent to share credentials/instances.

**Acceptance:** account owner checks, safe reconnect/disconnect, revoke failure pending state, secret redaction/rotation, webhook replay and wrong-owner mapping, protected provider events, no real sends in automated tests.

## 7. Disparar — proposed `/campaigns`

**Not implemented:** no audience/campaign/template/sending pipeline exists. A lead with phone/email is not an opted-in recipient.

**MVP contract after channel/provider approval:** draft -> validate audience/template/account/permission -> immutable recipient snapshot -> schedule or approved start -> queued per-recipient processing -> completed/partial/failed/cancelled. Record explicit statuses without promising delivery. Revalidate opt-out and current account permission at dispatch; cancellation stops pending deliveries and does not claim already-submitted messages were recalled.

**Data/dependencies:** campaigns/campaign_recipients, approved template versions, contact permissions, connected number, per-provider rate/cost policy, idempotent external-operation IDs, provider delivery events. No plan-based quantities or delivery prices are approved; infrastructure safety caps must be configurable and reviewed separately from commercial limits.

**Acceptance:** no duplicate sends on retry, consent suppression across re-import, audience isolation, invalid destination, cancellation race, provider rejection, unknown submission, delivery webhook replay, sanitized audit and cost reporting only when provider supplies verifiable values. No anti-spam bypass, rotating identities or evasion mechanism.

## 8. E-mail Marketing — proposed `/email-marketing`

**Not implemented:** Laravel transactional password/verification mail is not bulk marketing infrastructure, verified marketing sender, list management or analytics.

**MVP contract after provider approval:** private lists/segments over owned contacts; verified sender/domain capability; draft/versioned content; recipient snapshot and permission checks; schedule and queued submission; unsubscribe/suppression; provider bounce/complaint/delivery events; honest available metrics. Opens/clicks are provider signals with uncertainty, not verified human engagement. Do not add a new SMTP mass server by default.

**Data/dependencies:** reuse campaign lifecycle with `channel=email`; sender identity, consent/suppression, provider event dedup, template versions and protected public unsubscribe token. Never expose a raw contact ID as unsubscribe authorization. Signatures, replay controls and sender DNS work require separate authorization.

**Provider decision:** compare official managed-provider capabilities (sender verification, region/account availability, event/webhook support, unsubscribe/bounce tooling, cost and data handling) in the dedicated integration decision before selection; no hardcoded provider SDK today.

**Acceptance:** cross-owner lists, unsubscribe idempotence, bounce/complaint suppression, invalid sender block, retry dedup, event order, accessible template/preview, no accidental real mail during tests. Keep marketing consent and platform transactional notices separate.

## 9. Automations — proposed `/automations`

**Not implemented:** existing scraper jobs/scheduler are fixed operational workflows, not a user-configurable automation engine. No Events/Listeners domain catalog or user rule builder is present.

**MVP contract:** small allowlisted trigger/condition/action rule set over actually implemented modules; no arbitrary PHP/SQL/JavaScript or user-supplied server URL execution. Candidate events: `lead.created`, `opportunity.moved`, `activity.overdue`, `calendar_event.created`, `message.received`, `campaign.completed`; each becomes available only when its producer exists.

**Execution:** versioned rules, durable event ID, owner context and one execution per stable action: UNIQUE(automation_rule_id,rule_version,source_event_id,action_key). A rule may have multiple actions; each retains its pinned snapshot and logical operation identity across retries. Queue actions with bounded retries/backoff, timeout, safe error log, root correlation/causation IDs, bounded chain depth and no same-event recursive replay. Trigger producer writes committed business state first; use `outbox_events` if durable delivery cannot otherwise be guaranteed. Unknown external actions reconcile instead of blind resend.

**Dependencies/data:** rules/executions, events from core modules, operation keys reused by sending/action services. Native Laravel is sufficient for initial finite workflows; evaluate an independent authorized n8n integration only for requirements unavailable internally. Existing n8n of other projects is not a dependency.

**Acceptance:** duplicate event, rule changed while queued, disabled rule semantics, foreign-owner payload, loop/chain limit, partially completed action retry, external unknown, authorized action whitelist and secret-free logs.

## 10. Agenda — proposed `/calendar`

**Not implemented:** no activities/calendar models or screens exist.

**MVP contract:** private tasks and appointments linked optionally to an owned lead/opportunity; subject, due/start/end, priority, status, IANA timezone; list/agenda view; complete/reschedule/cancel. Activities model tasks/reminders; calendar_events model bounded start/end appointments, avoiding duplicate `tasks`/`activities` concepts. Store UTC instants, display in user preference timezone; define all-day date behavior before implementation.

**Reminders:** queued only after transactional commit; recheck current status/time/version before delivery; stable reminder identity prevents duplicate notifications; no implicit external e-mail/WhatsApp delivery until approved capability exists. Initially in-app state can be enough. Recurrence, attendee collaboration and calendar provider synchronization are expansion decisions.

**Acceptance:** cross-owner associations, invalid ranges, timezone/daylight saving boundaries, overdue logic, reschedule invalidates old reminder, duplicate job, cancellation and accessible mobile view.

## 11. Briefings — proposed `/briefings`

**Not implemented:** no templates/answers/uploads/sharing workflow exists.

**MVP contract:** admin-provided versioned template for approved project type; partner drafts own briefing attached optionally to owned lead/opportunity; server validates allowlisted field types and required/length constraints; submit locks definition/answer interpretation; archive or version a correction. Candidate types are website, landing page, e-commerce, systems and automations, not automatic financial products.

**Data:** `briefing_templates` is the template identity; `briefing_template_versions` contains immutable published definitions; each `briefings.briefing_template_version_id` pins one definition; `briefing_answers` is the sole answer store. Use no dynamic SQL columns or duplicate whole-answer document. Private `attachments` use allowlisted size/type, randomized storage key and authenticated download/scan workflow; defer until required. Optional `briefing_share_tokens` grants one scoped expiring revocable action, stores only the digest, exposes no indexable public customer data and gives no full CRM access.

**Acceptance:** template-version immutability, required/type validation, draft/submit ownership, cross-owner attachment/link, revocation/expiration, stored-XSS prevention, download authorization and no billing/commission effects.

## 12. My account — proposed `/account`

**Existing building blocks:** User authentication, email verification, password-reset flow, remember/session handling and admin-managed name/email/status/role. There is no partner self-service account settings screen, avatar upload, preferences UI, active-session management or billing portal.

**MVP contract:** edit allowed profile fields only; photo or initials; display timezone/language preferences when supported; password change with current-password confirmation; email change requires re-verification and safe session handling; list/revoke own sessions only if safely supported by current session driver. No role/status/plan changes accepted from this form.

**Billing expansion:** show provider-synchronized subscription data and safe server-created Stripe Customer Portal session for the authenticated owner's customer only after billing integration/test approval. Do not let browser submit arbitrary Stripe customer ID, portal return URL or local paid status. Cancellation/delinquency must not destroy CRM records.

**Acceptance:** mass-assignment elevation attempts rejected; cross-owner session revoke blocked; avatar safety; old/new email verification flow; password update keeps security; portal customer ownership; no real charges; inactive/verified rules remain enforced.

## Existing UI versus backend distinctions

| Capability | Present code | Missing before claiming complete product capability |
| --- | --- | --- |
| Account logout | POST `/logout`, common shell form | Original static panel/map do not share the authenticated shell/footer |
| Global overview | Search-oriented JS KPIs | Server CRM aggregates and future activity metrics |
| Lead edit/notes/delete | Owner-protected API | Web detail/editor/history/notes surface and safe delete disclosure |
| Lead filters | Text q across name/phone/address | Approved advanced filters/tags/segments; no invented query parameters |
| Pipeline business status | Stage position only | Won/lost/closing value/history/forecast fields |
| Pipeline concurrency | UNIQUE, some locks, SQL-worker tests | Shared service end-to-end races, delete atomicity, stage-create ordering |
| Scraper availability | Job API fetch health | Verified worker/external availability, safe user-facing error state |
| Communication | None | Provider choice, credentials, webhooks, consent, delivery lifecycle |
| Plans/billing | None | Catalog/entitlement/subscription integration phases |

## Current tests and the limits of their evidence

- `LeadsPageTest`: owned list rendering, guest redirect, another owner's lead hidden. It does not test multiple pages/search combinations or the future details UI.
- `PipelineTest`: create/rename/delete stage lifecycle, cross-owner rename/move rejection, sequential duplicate send, occupied transfer, occupied-without-destination rejection, whole owned-stage reorder set, blank-name rejection and shared shell markup.
- `MySqlPipelineConcurrencyTest`: four two-process SQL scenarios through external worker. These do not invoke `PipelineController::destroyStage()` and therefore cannot close its pre-transaction race.
- `AuthenticationTest` and `ProspectingRouteTest`: authentication/active/verified and binary-file route assertions, menu/source integrity checks. Source string assertions do not prove browser event handlers or CSRF-bearing panel writes.
- Scraper tests cover fake HTTP and claims/capacity in existing test files; examine their exact scope before expanding lifecycle claims. This documentation run did not execute them again or access any database.

The next executable phase should address the bounded navigation/contract defects and establish service-level transaction tests before advanced CRM or communication features. Shared design implementation follows the specific visual approval; commercial activation remains separate.
