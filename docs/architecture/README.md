# Parceiros Kaizen architecture index

Planning dossier for the local Laravel application, audited 2026-10-20. **Documentation only. No application implementation, production access or release is authorized here.**

Start with [overview](00-overview.md), [current-state evidence](01-current-state.md) and [risk gates](13-decisions-and-risks.md).
The first executable prompt is [A00 — safety baseline](../implementation/phase-a/a00-safety-baseline-and-faithful-test-entry-points.md).

## Architecture chapters

- [Executive overview and evidence labels](00-overview.md)
- [Code inventory, real functionality and defects](01-current-state.md)
- [System boundaries, flows and module dependencies](02-system-architecture.md)
- [Existing and proposed logical data model](03-database-model.md)
- [Identity, ownership, permissions and security](04-auth-and-security.md)
- [Shared shell, original frontend and design decisions](05-navigation-and-design.md)
- [All twelve partner module contracts](06-crm-modules.md)
- [Four plans, typed entitlements and future quota rules](07-plans-and-entitlements.md)
- [Official Stripe/Cashier sandbox architecture](08-stripe-billing.md)
- [Central administrative capabilities and audit](09-admin-panel.md)
- [Dedicated operation on shared infrastructure](10-infrastructure.md)
- [Test layers, guard gaps and acceptance evidence](11-testing-strategy.md)
- [Additive releases, backups and separate DB recovery](12-release-strategy.md)
- [Prioritized risks and pending business decisions](13-decisions-and-risks.md)
- [41-step dependency roadmap and prompt catalog](14-implementation-roadmap.md)
- [Full user lifecycle, empty/error/loading states](15-user-journeys.md)
- [Official/unofficial communication and email comparison](16-provider-decisions.md)
- [Architecture decision records](DECISIONS.md).
- [Implementation contract and checklist](../implementation/CONTRACT.md).
- [Progress and evidence ledger](../implementation/PROGRESS.md).

## All implementation prompts

### Phase A

- [A00 — Safety baseline and faithful test entry points](../implementation/phase-a/a00-safety-baseline-and-faithful-test-entry-points.md).
- [A01 — Shared shell and browser CSRF contract](../implementation/phase-a/a01-shared-shell-and-browser-csrf-contract.md).
- [A02 — User identity and module navigation](../implementation/phase-a/a02-user-identity-and-module-navigation.md).
- [A03 — Useful CRM overview with real aggregates](../implementation/phase-a/a03-useful-crm-overview-with-real-aggregates.md).
- [A04 — Integrate the preserved map into the shared shell](../implementation/phase-a/a04-integrate-the-preserved-map-into-the-shared-shell.md).

### Phase B

- [B01 — Repair verification and identity lifecycle](../implementation/phase-b/b01-repair-verification-and-identity-lifecycle.md).
- [B02 — Personal profile and session management](../implementation/phase-b/b02-personal-profile-and-session-management.md).
- [B03 — Consistent ownership authorization](../implementation/phase-b/b03-consistent-ownership-authorization.md).

### Phase C

- [C01 — Four-plan catalog and environment price mapping](../implementation/phase-c/c01-four-plan-catalog-and-environment-price-mapping.md).
- [C02 — Typed entitlement resolution](../implementation/phase-c/c02-typed-entitlement-resolution.md).
- [C03 — Atomic quota reservation primitives](../implementation/phase-c/c03-atomic-quota-reservation-primitives.md).

### Phase D

- [D01 — Sandbox billing dependency and single projection](../implementation/phase-d/d01-sandbox-billing-dependency-and-single-projection.md).
- [D02 — Hosted Checkout and billing Portal](../implementation/phase-d/d02-hosted-checkout-and-billing-portal.md).
- [D03 — Signed durable billing event intake](../implementation/phase-d/d03-signed-durable-billing-event-intake.md).
- [D04 — Subscription lifecycle and reconciliation policy](../implementation/phase-d/d04-subscription-lifecycle-and-reconciliation-policy.md).

### Phase E

- [E01 — Administrative authorization and atomic audit](../implementation/phase-e/e01-administrative-authorization-and-atomic-audit.md).
- [E02 — User lifecycle and retention-safe administration](../implementation/phase-e/e02-user-lifecycle-and-retention-safe-administration.md).
- [E03 — Administrative plans and subscription management](../implementation/phase-e/e03-administrative-plans-and-subscription-management.md).

### Phase F

- [F01 — Pipeline transaction and race safety](../implementation/phase-f/f01-pipeline-transaction-and-race-safety.md).
- [F02 — Scraper ownership uncertainty and safe recovery](../implementation/phase-f/f02-scraper-ownership-uncertainty-and-safe-recovery.md).
- [F03 — Complete resumable CSV import](../implementation/phase-f/f03-complete-resumable-csv-import.md).
- [F04 — Lead detail notes tags and capture history](../implementation/phase-f/f04-lead-detail-notes-tags-and-capture-history.md).
- [F05 — Opportunity lifecycle and history](../implementation/phase-f/f05-opportunity-lifecycle-and-history.md).

### Phase G

- [G01 — Owned activities and tasks](../implementation/phase-g/g01-owned-activities-and-tasks.md).
- [G02 — Calendar appointments and reminders](../implementation/phase-g/g02-calendar-appointments-and-reminders.md).

### Phase H

- [H01 — Versioned briefing templates](../implementation/phase-h/h01-versioned-briefing-templates.md).
- [H02 — Briefing responses secure sharing and attachments](../implementation/phase-h/h02-briefing-responses-secure-sharing-and-attachments.md).

### Phase I

- [I01 — Provider and consent decision record](../implementation/phase-i/i01-provider-and-consent-decision-record.md).
- [I02 — Owned communication accounts and signed webhooks](../implementation/phase-i/i02-owned-communication-accounts-and-signed-webhooks.md).
- [I03 — Contact identity and channel permission](../implementation/phase-i/i03-contact-identity-and-channel-permission.md).

### Phase J

- [J01 — Inbox inbound conversations](../implementation/phase-j/j01-inbox-inbound-conversations.md).
- [J02 — Safe outbound messages attachments and delivery](../implementation/phase-j/j02-safe-outbound-messages-attachments-and-delivery.md).

### Phase K

- [K01 — Email provider sender verification and audiences](../implementation/phase-k/k01-email-provider-sender-verification-and-audiences.md).
- [K02 — Consented campaigns scheduling and dispatch](../implementation/phase-k/k02-consented-campaigns-scheduling-and-dispatch.md).
- [K03 — Delivery feedback metrics and cost controls](../implementation/phase-k/k03-delivery-feedback-metrics-and-cost-controls.md).

### Phase L

- [L01 — Domain events and bounded automation rules](../implementation/phase-l/l01-domain-events-and-bounded-automation-rules.md).
- [L02 — Idempotent automation execution and recovery](../implementation/phase-l/l02-idempotent-automation-execution-and-recovery.md).

### Phase M

- [M01 — Safe observability and runtime budgets](../implementation/phase-m/m01-safe-observability-and-runtime-budgets.md).
- [M02 — Retention erasure export and restore rehearsal](../implementation/phase-m/m02-retention-erasure-export-and-restore-rehearsal.md).
- [M03 — Enabled-module staging acceptance](../implementation/phase-m/m03-enabled-module-staging-acceptance.md).
- [M04 — Release candidate and activation handoff](../implementation/phase-m/m04-release-candidate-and-activation-handoff.md).

## Existing documentation preserved

- [Design System](../DESIGN_SYSTEM.md) and [tokens](../../public/design-system/tokens.css): current source, pending requested color reconciliation.
- [Original architecture overview](../architecture.md): introductory historical baseline.
- [Deployment checklist](../deployment-aapanel.md), [rollback](../rollback.md), [setup](../setup.md), [security](../security.md), [testing](../testing.md), [MySQL testing](../mysql-testing.md), [scraper processing](../scraper-processing.md): retained for context. Read the discrepancies in chapter 01/10 before execution; old commands/timeouts are not authoritative future instructions.

## Governance and sources

Current state means inspected code, not deployed runtime. Future entities/classes/routes and ADRs are proposals. All 41 prompts remain planned in the ledger. Official Laravel/Stripe/provider/MySQL/ANPD references are linked alongside their relevant claims in chapters 04/08/12/16; account-specific capabilities remain unverified. No production secrets or real client data are included.

## Documentation validation

2026-10-20: 62 new Markdown documents (19 architecture, 41 task prompts, contract and progress ledger); 566 local links resolved; all 41 dependency nodes checked without cycles; Markdown fences/whitespace passed. git diff --check passed with only pre-existing CRLF-normalization warnings in functional files. SHA256 comparison of all 147 pre-existing versioned/untracked non-architecture files, excluding the authorized AGENTS.md references, was identical before/after. No application test/database/provider/VPS operation was executed in this planning phase; historical test counts are labelled accordingly.
