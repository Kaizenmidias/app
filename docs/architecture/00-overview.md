# Parceiros Kaizen master architecture

Status: **proposed architecture; no implementation authorization**. Code audit: 2026-10-20.
Repository boundary: this Laravel repository, not the enclosing Vibe Coding Toolkit or the internal Kaizen CRM.

## Executive decision

Evolve the existing Laravel 12 application as a modular monolith with one personal workspace per user. Reuse session authentication, Leads, Pipeline, database queues and the internal Gosom adapter. Keep administrative permissions, commercial plans, financial subscriptions and entitlements as four separate concepts.

The next implementation is **A00, safety baseline**, not Stripe installation or a production release. Current code contains concrete recovery, import and authorization gaps described in [current state](01-current-state.md) and [risks](13-decisions-and-risks.md). Previous green suites do not demonstrate all HTTP, queue and controller concurrency paths.

## Evidence labels

- **Verified in code**: inspected local files in this audit; not proof of a working deployed service.
- **Historical result**: reported earlier in this conversation, not rerun or remotely audited here.
- **Proposed**: future design requiring an individually authorized implementation task.
- **Decision-gated**: commercial, provider, retention or visual choice that must be resolved before dependent implementation.
- **Not verified**: unavailable runtime, credential, region/account capability or live behavior.

Snapshot: branch `main`, HEAD `f5ffc7bd8693a4b6d80694b164d27041d7b258e7`, with existing uncommitted Leads/Pipeline/testing/navigation changes. This document describes that working tree, not HEAD alone. Record a new approved commit and rerun target-specific checks before any release.

## Scope and non-goals

This dossier plans twelve partner modules, central administration, four plan records and official Stripe subscription billing. It does not set prices, quotas, feature bundles, grace periods, a trial policy, financial promises or provider accounts. It does not enable registration, call providers, run tests against databases, change the VPS or install packages.

No teams, tenant packages, microservices, Kubernetes, Redis, real-time server or separate frontend framework are required by the current scope. Add infrastructure only for a measured need. The original scraper assets remain protected; a separately approved A04 task will define the smallest adaptation to the future shared shell.

## Reading order

1. [Current state and evidence](01-current-state.md).
2. [System boundaries and flows](02-system-architecture.md).
3. [Data model](03-database-model.md), [security](04-auth-and-security.md), [navigation](05-navigation-and-design.md).
4. [Module contracts](06-crm-modules.md), [plans](07-plans-and-entitlements.md), [billing](08-stripe-billing.md), [administration](09-admin-panel.md).
5. [Infrastructure](10-infrastructure.md), [testing](11-testing-strategy.md), [release](12-release-strategy.md).
6. [Risks and pending decisions](13-decisions-and-risks.md), [roadmap](14-implementation-roadmap.md), [ADRs](DECISIONS.md).
7. [Execution contract](../implementation/CONTRACT.md), [progress ledger](../implementation/PROGRESS.md), then the authorized task prompt.

## Priorities and completion gates

| Gate | Required evidence | Blocked until then |
| --- | --- | --- |
| Safe test target | Effective connection/driver/DSN/account/grants checked before database test hooks | Any MySQL test migration or cleanup |
| Reliable existing core | F01–F03 regressions exercise real controller/service/job paths | Scraper workers, new ingestion, unsafe stage deletion |
| Identity lifecycle | B01 and E01 tests for verification, role boundaries and final-admin mutation | Public signup and administrative lifecycle expansion |
| SaaS nucleus | Four configured plan records, deterministic resolver, sandbox lifecycle and administrative audit | Paid launch |
| Provider readiness | Approved provider/account/consent, verified domains/numbers, test-only acceptance | Messaging, campaigns and automations with external effects |
| Production readiness | Authorized release, restore rehearsal, current resource audit and smoke checks | Activation of each new module |

The CRM can remain usable without commercial billing activation; configuring plan records does not grant invented benefits or impose unapproved commercial blocks.

## Documentation index

[Full index](README.md) lists every architecture chapter, decision record, task prompt and implementation gate. Architecture documents are canonical for this future design; legacy deployment notes remain historical references and contain conflicting instructions called out in chapter 10.
