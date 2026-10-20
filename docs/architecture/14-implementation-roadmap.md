# Implementation roadmap

**41 individually scoped prompts across phases A–M. All are planned, not implemented.** Prompts work with a smaller executor when explicitly selected by the operator; this plan does not switch the current client/model/authentication. English documentation is canonical; product UI labels remain Portuguese.

## Execution order and gates

Phase letters organize domains, not automatic authorization or completion. Existing safety defects change the practical order:

1. A00 establishes trustworthy pre-write guards and application-path reproduction.
2. B03 (ownership), F01 (Pipeline integrity), F02 (scraper recovery) and F03 (import completion) close existing-core defects; workers/scraping remain off.
3. B01 repairs identity lifecycle; A01/A02/A03/A04 consolidate shell, navigation, overview and map; B02 adds own account.
4. C01/C02 build catalog/resolution with no invented benefits. Sandbox order is D01 → D03 (durable intake) → D02 (Checkout/Portal) → D04. E01 can run earlier once B01/B03 complete; E02/E03 expand administration.
5. **SaaS nucleus gate:** identity, ownership, reviewed navigation/core, plans, sandbox lifecycle and admin audit accepted before expanding communication. No need to implement all twelve modules to validate this core.
6. C03 applies only to approved meter semantics; F04/F05 extend CRM. G activities/calendar and H briefings may follow independently when dependencies hold.
7. I provider/consent decision precedes communication account/contact integration; J chats follows. K campaigns/email follows approval and sandbox proof, then L automations.
8. M01 observability can start immediately after A00 (do not wait until all modules are built). M02 retention/restore and M03 enabled-module staging lead to M04 reviewable release handoff.
9. Production operations remain a separately authorized future task.

Recommended linear queue (dependencies are authoritative): A00 → B03 → F01 → F02 → F03 → B01 → A01 → A02 → A03 → A04 → B02 → C01 → C02 → D01 → D03 → D02 → D04 → E01 → E02 → E03 → C03 → F04 → F05 → G01 → G02 → H01 → H02 → I01 → I02 → I03 → J01 → J02 → K01 → K02 → K03 → L01 → L02 → M01 → M02 → M03 → M04.
M02's H/K dependencies apply only when those optional modules are in the enabled release; core-only retention must not wait for unimplemented campaign/briefing code. M03/M04 validate the enabled-module dependency closure, not a mandatory big-bang launch.

## Prompt catalog

| ID / scope | Prerequisites | Primary acceptance gate |
| --- | --- | --- |
| [A00 — Safety baseline and faithful test entry points](../implementation/phase-a/a00-safety-baseline-and-faithful-test-entry-points.md) | None | Invalid configuration cannot reach migration hooks; valid isolated target is distinguishable; development demo DB fingerprint is unchanged; report exactly which defects remain reproduced. |
| [A01 — Shared shell and browser CSRF contract](../implementation/phase-a/a01-shared-shell-and-browser-csrf-contract.md) | A00 | Single navigation source, valid legacy mutation passes CSRF, invalid fails, tokens/cookies stay secure and no new broken links. |
| [A02 — User identity and module navigation](../implementation/phase-a/a02-user-identity-and-module-navigation.md) | A01, B03 | Greeting derives from real account, one sidebar, no links to /classic or absent routes, no scraping from navigation. |
| [A03 — Useful CRM overview with real aggregates](../implementation/phase-a/a03-useful-crm-overview-with-real-aggregates.md) | A02, B03 | No fabricated metrics or money; /dashboard no longer auto-redirects to map; compatibility route decision documented. |
| [A04 — Integrate the preserved map into the shared shell](../implementation/phase-a/a04-integrate-the-preserved-map-into-the-shared-shell.md) | A02, F02, F03 | One sidebar remains visible at all breakpoints; map correctly resizes; APIs unchanged; no marketing buttons or removal of mandatory map attribution. |
| [B01 — Repair verification and identity lifecycle](../implementation/phase-b/b01-repair-verification-and-identity-lifecycle.md) | A00 | Partner can complete verification in isolated test; no automatic partner verification; no production registration enabling. |
| [B02 — Personal profile and session management](../implementation/phase-b/b02-personal-profile-and-session-management.md) | B01, B03, A02 | Only self-owned changes persist; no exposing password hashes or public private uploads; account accessible for billing recovery under approved policy. |
| [B03 — Consistent ownership authorization](../implementation/phase-b/b03-consistent-ownership-authorization.md) | A00 | No cross-user read/write/reference; UI hiding is not evidence; existing policy code is actually invoked or equivalently tested guards documented. |
| [C01 — Four-plan catalog and environment price mapping](../implementation/phase-c/c01-four-plan-catalog-and-environment-price-mapping.md) | B03, A02 | Four plans persist independent of billing; price mapping can be absent safely; existing partner access unaffected until approved enforcement. |
| [C02 — Typed entitlement resolution](../implementation/phase-c/c02-typed-entitlement-resolution.md) | C01, B03 | Resolver outputs value+reason+source; unconfigured commercial gates remain disabled; no arbitrary benefit allocations. |
| [C03 — Atomic quota reservation primitives](../implementation/phase-c/c03-atomic-quota-reservation-primitives.md) | C02, F03 | No retry double consumption and no over-reservation; operator's units and period policy recorded, no numbers invented. |
| [D01 — Sandbox billing dependency and single projection](../implementation/phase-d/d01-sandbox-billing-dependency-and-single-projection.md) | C01, B01, B03 | Sandbox adapter contract documented/versioned; provider calls fake by default; no real charge. |
| [D02 — Hosted Checkout and billing Portal](../implementation/phase-d/d02-hosted-checkout-and-billing-portal.md) | D01, C02, D03 | Hosted sandbox flow callable, unresolved outcomes visible; no local raw card capture or paid entitlement from browser. |
| [D03 — Signed durable billing event intake](../implementation/phase-d/d03-signed-durable-billing-event-intake.md) | D01 | No event has duplicate financial/access effect; retry recovers after crash; provider truth governs projection. |
| [D04 — Subscription lifecycle and reconciliation policy](../implementation/phase-d/d04-subscription-lifecycle-and-reconciliation-policy.md) | D02, D03, C02 | SaaS nucleus acceptance requires D01–D04 with configured business policy; no billing effects from browser alone. |
| [E01 — Administrative authorization and atomic audit](../implementation/phase-e/e01-administrative-authorization-and-atomic-audit.md) | B01, B03 | Every admin mutation authorized, atomic and traceable; no last-admin race. |
| [E02 — User lifecycle and retention-safe administration](../implementation/phase-e/e02-user-lifecycle-and-retention-safe-administration.md) | E01, B02 | No irreversible cascade or false invitation-sent claim; account state independent of billing. |
| [E03 — Administrative plans and subscription management](../implementation/phase-e/e03-administrative-plans-and-subscription-management.md) | E01, C02, D04 | Local catalog and provider financial truth remain coherent with explicit pending states/audit. |
| [F01 — Pipeline transaction and race safety](../implementation/phase-f/f01-pipeline-transaction-and-race-safety.md) | A00, B03 | No opportunity lost, exact one current lead opportunity, valid complete stage order, foreign IDs denied, deadlock recoverable/error bounded. |
| [F02 — Scraper ownership uncertainty and safe recovery](../implementation/phase-f/f02-scraper-ownership-uncertainty-and-safe-recovery.md) | A00, B03 | No premature capacity release and no stale-owner write; external ID preserved; recovery can finish known external work without POST. |
| [F03 — Complete resumable CSV import](../implementation/phase-f/f03-complete-resumable-csv-import.md) | F02, A00 | All valid rows accounted, lost owner writes no more rows, incomplete import never ok, capacity released only legitimate finalization. |
| [F04 — Lead detail notes tags and capture history](../implementation/phase-f/f04-lead-detail-notes-tags-and-capture-history.md) | A02, B03, F03 | No cross-user leak; unique identity persists and user correction/provenance semantics explicit. |
| [F05 — Opportunity lifecycle and history](../implementation/phase-f/f05-opportunity-lifecycle-and-history.md) | F01, F04 | Real lifecycle metrics possible; no financial forecast fabricated for records without amount/status/time. |
| [G01 — Owned activities and tasks](../implementation/phase-g/g01-owned-activities-and-tasks.md) | F05, B03, A02 | Tasks persist per user; real counts appear only after model available. |
| [G02 — Calendar appointments and reminders](../implementation/phase-g/g02-calendar-appointments-and-reminders.md) | G01, B02 | Correct reminder once logically despite repeated deliveries; no shared scheduler activation. |
| [H01 — Versioned briefing templates](../implementation/phase-h/h01-versioned-briefing-templates.md) | B03, A02 | No response breaks when template changes; type catalog does not add project charging. |
| [H02 — Briefing responses secure sharing and attachments](../implementation/phase-h/h02-briefing-responses-secure-sharing-and-attachments.md) | H01, F04 | Answers reproducible for pinned version and private by default; share intentionally scoped. |
| [I01 — Provider and consent decision record](../implementation/phase-i/i01-provider-and-consent-decision-record.md) | B03, C02 | P07/P08/P09 decisions captured with sources, provider/version/signature/retention requirements; no commercial/provider facts invented. |
| [I02 — Owned communication accounts and signed webhooks](../implementation/phase-i/i02-owned-communication-accounts-and-signed-webhooks.md) | I01, B03, D03 | Isolated sandbox account only, owned valid status from provider; no shared CRM network. |
| [I03 — Contact identity and channel permission](../implementation/phase-i/i03-contact-identity-and-channel-permission.md) | I01, F04 | One authoritative channel identity per owner/rule; Lead company not copied to every message. |
| [J01 — Inbox inbound conversations](../implementation/phase-j/j01-inbox-inbound-conversations.md) | I02, I03 | Only verified account events enter owned inbox; no assumed realtime service. |
| [J02 — Safe outbound messages attachments and delivery](../implementation/phase-j/j02-safe-outbound-messages-attachments-and-delivery.md) | J01, C03 | No duplicate outbound effect on retry, no messages outside allowed consent/provider policy. |
| [K01 — Email provider sender verification and audiences](../implementation/phase-k/k01-email-provider-sender-verification-and-audiences.md) | I01, I03 | Verified test sender model/audience snapshots possible, no local bulk mail server. |
| [K02 — Consented campaigns scheduling and dispatch](../implementation/phase-k/k02-consented-campaigns-scheduling-and-dispatch.md) | K01, I02, J02, C03 | No duplicate send or bypass of suppression, no unknown reported delivered; no arbitrary marketing limits. |
| [K03 — Delivery feedback metrics and cost controls](../implementation/phase-k/k03-delivery-feedback-metrics-and-cost-controls.md) | K02, D03 | Provider truth reconciles, consent withdrawal affects all unsent campaign paths; metrics definitions explicit. |
| [L01 — Domain events and bounded automation rules](../implementation/phase-l/l01-domain-events-and-bounded-automation-rules.md) | F05, G02, H02, J01, K03 | Only implemented catalog actions allowed; no automatic n8n dependency. |
| [L02 — Idempotent automation execution and recovery](../implementation/phase-l/l02-idempotent-automation-execution-and-recovery.md) | L01, C03, J02 | At-least-once transport causes one logical action, no cross-user or uncontrolled loop. |
| [M01 — Safe observability and runtime budgets](../implementation/phase-m/m01-safe-observability-and-runtime-budgets.md) | A00 | Document resource preflight, stop conditions, worker queue split and noninterference with other VPS services. |
| [M02 — Retention erasure export and restore rehearsal](../implementation/phase-m/m02-retention-erasure-export-and-restore-rehearsal.md) | E02, H02, K03 | Operator-approved retention/RPO/RTO exists; restored DB not allowed to resend old provider effects. |
| [M03 — Enabled-module staging acceptance](../implementation/phase-m/m03-enabled-module-staging-acceptance.md) | A03, A04, B02, C02, D04, E03, F01, F02, F03, F05, M01 | Every enabled module has tested dependencies and business decisions; undeveloped modules inert; measured resource envelope and rollback compatibility recorded. |
| [M04 — Release candidate and activation handoff](../implementation/phase-m/m04-release-candidate-and-activation-handoff.md) | M03, M02 | One exact candidate scope, known migration/recovery effect and explicit operator approval request; no commit/push/deploy performed by this task. |

## Phase totals

- Phase A: 5 prompts.
- Phase B: 3 prompts.
- Phase C: 3 prompts.
- Phase D: 4 prompts.
- Phase E: 3 prompts.
- Phase F: 5 prompts.
- Phase G: 2 prompts.
- Phase H: 2 prompts.
- Phase I: 3 prompts.
- Phase J: 2 prompts.
- Phase K: 3 prompts.
- Phase L: 2 prompts.
- Phase M: 4 prompts.

Every prompt includes objective, context/files, dependencies, database/backend/frontend changes, security rules, mandatory tests, acceptance, risks/exclusions, validation and stop condition. They are not instructions to create all future tables at once.

## Deferred commercial and provider activation

P03/P05/P06 govern prices, methods, access/grace and commercial quotas. P07/P08/P09 govern communication/consent/retention. Undefined decisions block only dependent implementation/activation, not documentation or unrelated core fixes. An unavailable module is not a linked fake page. Plan records may exist without prices; paid Checkout remains unavailable.

The [progress ledger](../implementation/PROGRESS.md) records evidence and approvals. No architecture prompt is automatically complete because a similarly named feature exists; compare its actual acceptance criteria.
