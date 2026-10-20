# Architecture decision records

All records below are **proposed**, except constraints directly requested by the operator. They authorize documentation only. Approval date/actor and superseding ADR must be recorded during implementation.

## ADR-001 — Personal ownership within a modular monolith

- Decision: retain Laravel 12/PHP 8.4/MySQL 8.4 and user_id personal data boundary.
- Alternatives: tenant-per-database, organization/workspace migration, microservices.
- Rationale: no approved team collaboration; current Leads/Pipeline depend on User ownership.
- Consequences: same-owner relationships must be verified in every query/write; platform admin scopes explicit.
- Risks: later teams need a deliberate migration, not a global query bypass.
- Status: proposed; no tenancy package required.

## ADR-002 — Reuse UI through one shared shell

- Decision: converge module presentation on Blade shared navigation/components and existing tokens; adapt legacy DOM only in authorized A04.
- Alternatives: iframe, SPA rewrite, duplicated sidebars, injecting uncontrolled global HTML.
- Rationale: preserve map/result/event behavior with minimal dependencies.
- Consequences: /painel becomes compatibility alias only after approved dashboard transition; legacy DOM IDs/contract tests remain stable.
- Risks: mapa links point to root, panel CSRF mismatch, public static shell, design blue conflict.
- Status: proposed; P01/P02 gate visual changes.

## ADR-003 — Separate commercial concepts

- Decision: role != plan != financial subscription != entitlement. Four stable codes free/essencial/plus/pro, no price or benefits set.
- Alternatives: role-per-plan; plan string checked throughout controllers.
- Rationale: prevents privilege elevation and supports configuration without code edits.
- Consequences: resolver with explicit unset/unlimited/boolean states, audited exceptions, commercial policy decisions before enforcement.
- Risks: hidden defaults or conflicting subscription assignments.
- Status: product separation required; schema/resolver proposed.

## ADR-004 — Official Stripe integration with Cashier preference

- Decision: prefer compatible Laravel Cashier Stripe as one subscription adapter/projection after package/API review, plus application-owned signed webhook inbox and entitlements.
- Alternatives: direct stripe-php integration; bespoke card handling.
- Rationale: hosted Checkout/Portal and conventional subscriptions suit one paying User.
- Consequences: reconcile Cashier schema/event handlers with one authoritative consumer; no duplicate subscription table/truth.
- Risks: API version/package compatibility, event order, test/live mixing, hidden auto-registration routes.
- Status: proposed; actual dependency and commercial activation require authorization.

## ADR-005 — At-least-once effects with durable intent

- Decision: local data+outbox in one transaction, provider inbox deduplication, idempotent consumers and explicit unknown external outcomes.
- Alternatives: only afterCommit dispatch; long transaction over HTTP; exactly-once promises.
- Rationale: local DB and external providers cannot commit together.
- Consequences: stable event keys, current-state reconciliation, no blind retries of ambiguous effects.
- Risks: outbox backlog, receipt retention, provider lacks idempotent send.
- Status: proposed incrementally starting billing.

## ADR-006 — Provider-neutral communication, official first

- Decision: compare direct Meta Cloud API and approved BSP; unsupported WhatsApp Web/Baileys path is not chosen by default. Never reuse CRM Evolution credentials/network.
- Alternatives: shared Evolution instance, new isolated unofficial connection.
- Rationale: business reliability, consent and account boundaries.
- Consequences: provider decision precedes implementation; encryption, webhook validation and account binding per owner.
- Risks: eligibility, cost, provider policy changes and unofficial session/blocking risk.
- Status: proposed; P07 approval required.

## ADR-007 — Conservative production evolution

- Decision: additive schema, immutable releases, shared private state, code rollback separate from DB recovery, module-by-module activation.
- Alternatives: editing current release, destructive rollback, automatic service restart.
- Rationale: shared VPS and preserved data.
- Consequences: fresh resource preflight and tested restore before rollout; no worker activation by incidental deploy.
- Risks: DDL implicit commits, queue payload/backward compatibility.
- Status: operational requirement; detailed runbook proposed.

## ADR-008 — Test application behavior, not copied SQL

- Decision: fail closed on effective DB target before test hooks; independent-process tests execute real actions/controllers/jobs under barriers.
- Alternatives: SQL simulations only, production DB test reuse, SQLite as lock proof.
- Rationale: audit found disagreement between harness SQL and controller locking.
- Consequences: preserve primitive SQL probes as lower-level evidence; add correct-layer integration coverage.
- Risks: test guard ordering, process environment, nondeterministic waits.
- Status: required safety gate; no code changed here.

## ADR-009 — No destructive data loss on commercial lifecycle

- Decision: cancellation/inadimplence changes eligible operations, not ownership or deletion of CRM data.
- Alternatives: cascaded user delete or stripping records on downgrade.
- Rationale: commercial changes must not silently destroy partner work.
- Consequences: retention and account closure separate from billing; lower plan overage requires approved policy.
- Risks: indefinite storage until legal/retention policy set.
- Status: product requirement; P03/P06/P09 pending.

## ADR-010 — Business and legal choices remain explicit

- Decision: undefined quota is not zero or unlimited; scraped business contact is not marketing consent; undefined provider capability is unavailable.
- Alternatives: guessing defaults, treating UI placeholders as live modules.
- Rationale: truthful implementation with no invented commercial or integration behavior.
- Consequences: read-only catalogue before policies; tests for missing config and safe unavailable states.
- Risks: incomplete setup can block new commercial operations intentionally.
- Status: required planning constraint.
