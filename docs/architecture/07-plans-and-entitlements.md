# Plans and entitlements

Status: proposed future domain; audited on 2026-10-20. There are no plan, subscription, commercial-limit or usage-meter models, tables, routes or packages in the current application. `scraper_capacity` is an operational global browser capacity counter, not a customer's plan quota.

## Four separate concepts

| Concept | Responsibility | Must not do |
| --- | --- | --- |
| Commercial plan | Catalog identity and offer | Grant administrative privileges or invent a price |
| Financial subscription | Stripe billing relationship and synchronized financial state | Override account suspension or imply successful settlement from a browser redirect |
| Administrative permission | Who may manage the platform | Depend on Free/Essencial/Plus/Pro tier |
| Entitlement/limit | Effective feature availability and approved consumption rule | Treat an undefined value as unlimited or silently delete data |

The approved catalog names are **Free, Essencial, Plus, Pro**. Physical identifier: `plans.code`, unique immutable values `free`, `essencial`, `plus`, `pro`. Display names may change. No prices, quantities, feature allocations, trials, grace periods or benefits are approved; administrators configure them later through audited workflows.

## Proposed minimal catalog

`plans`: bigint ID; immutable unique `code`; unique slug; display name; optional administrative description; availability; display order; explicit free/paid kind; allowlisted metadata; timestamps. Four records can exist before prices, without making them purchasable. Archival prevents selection, not history preservation or silent subscriber revocation.

`plan_prices`: bigint ID; plan FK with restricted deletion; provider (`stripe` initially); environment (`test` or `live`); provider account identifier; product ID; unique external price ID scoped by account/environment; currency and recurrence snapshot retrieved from Stripe; active flag; timestamps. External IDs are case-sensitive. Multiple historical prices can map to the same plan; one selected active price for a proposed checkout must be unambiguous. Do not edit historical financial terms by changing a local row.

Do not add a generic provider marketplace or a complex pricing engine. The mapping table is justified by test/live isolation and immutable historical Stripe Prices. A paid plan with no verified active Stripe mapping is shown as not available for checkout. Free never requires a paid Checkout session.

`user_plan_assignments` is a small entitlement assignment/history table, **not a second subscription table**: user FK; plan FK; source (`free`, `stripe_subscription`, `administrative_grant`); optional FK/reference to the authoritative Cashier subscription; starts/ends; actor/reason when administrative; timestamps. Only one effective base assignment may be selected for an instant. Serialize assignment changes on the user row in a transaction; MySQL does not provide a simple partial unique index for arbitrary overlapping effective periods. Keep prior rows for audit. Whether to use a separate history table or append-only assignment rows must be fixed in the schema task before migration, not improvised independently by each module.

Use append-only assignments as one access history, not duplicated current/history stores. C02 defers nullable subscription_id/FK to D01 after Cashier tables exist. Cashier subscriptions/subscription_items alone are the financial projection. See [billing](08-stripe-billing.md). Do not create a duplicate billing_subscriptions table.

## Proposed entitlements model

Use a small application service (`EntitlementResolver` proposed name) rather than copying plan checks into controllers or CSS. Definitions live in a controlled catalog; values may be configured by an administrator.

| Entity | Essential fields / constraint | Purpose |
| --- | --- | --- |
| `feature_definitions` | immutable unique key; name; value type; enforceable status | Defines a supported capability without giving it to a plan |
| `plan_features` | plan FK, feature FK, explicit boolean; UNIQUE(plan, feature) | Explicit feature availability once approved |
| `limit_definitions` | unique key, unit, aggregation strategy, window type, enforcement readiness | Defines what can actually be measured |
| `plan_limits` | plan FK, definition FK; mode `finite`/`unlimited`; nullable unsigned integer value; UNIQUE(plan, definition) | Finite requires value; unlimited requires NULL value |
| `user_entitlement_overrides` | user FK; definition/key; typed value/mode; starts/ends; reason; actor; audit reference | Time-bounded, explicit exceptions; never changes financial truth |

Missing configuration is **undefined**, not unlimited, zero, enabled, or disabled. Present undefined policy to the administrator as incomplete. Before commercial enforcement, require all advertised entitlements to resolve without ambiguity. Until an approved rollout enables a commercial gate, do not introduce new commercial blocks into existing users' workflow. Once enforcement is enabled, an undefined protected capability denies the new operation with an actionable configuration error; it never deletes existing data.

An absent limit does not automatically mean zero. A finite zero is an explicit prohibition. An unlimited mode is an explicit business decision. Store integers in the domain's documented unit; never use floating point or magic sentinel values such as `-1` for unlimited.

No numeric limit or feature-to-plan association is proposed here. Candidate future dimensions are lead records, scraping requests, opportunities, connections, campaign recipients, automation executions and storage bytes. Each remains inactive until measurement semantics and business policy are approved.

## Deterministic precedence

1. Authentication, active status, verified email, role/policy and ownership checks always apply. Financial or administrative grants cannot bypass these security controls.
2. Operational safety limits always apply, including global Gosom capacity and provider rate restrictions. Unlimited commercial usage does not mean unlimited simultaneous browser processes.
3. Resolve current financial access state through approved billing policy. Unknown/unreconciled payment state cannot manufacture a paid entitlement.
4. Resolve exactly one effective base plan assignment. Free is a local assignment, not a fake Stripe subscription. After a paid subscription ends, transition to a base plan only under the operator-approved policy; never silently rewrite financial state.
5. Apply explicit, valid administrative entitlement grants/denials for their defined period. An entitlement grant can authorize product usage despite payment restrictions only if its type explicitly permits that, with reason/expiry/audit; it never marks an invoice paid. A normal limit override alone does not bypass a financial suspension.
6. Apply plan feature/limit values and calculate remaining usage from the approved meter/reservations. Explain to the user whether a rejection is security, account, payment, operational or quota related.

No policy about `past_due`, cancellation grace or read-only access is commercially approved. Proposed safe UX is to preserve records and access to account/billing recovery, block only approved new consumption after the applicable policy, and never erase leads/opportunities on downgrade. Security blocks are independent.

## Future usage accounting: introduce only when needed

Do not conflate stock with consumption. Lead count and storage are current-stock limits; monthly completed scraping requests would be a period meter if approved. Before implementation decide whether an attempted, accepted, completed, canceled or partially successful operation consumes the unit.

For asynchronous metered work, use a durable reservation linked to the business operation before enqueueing; settle/release idempotently after its known outcome. Proposed `usage_reservations` has unique operation key, user, meter, quantity, state and expiry/reconciliation context. An append-only `usage_entries` table records settled debits/credits with unique business key; optional period aggregates are rebuildable. No counter should double-charge on queue retry. Do not release an ambiguous external reservation merely because a worker died. Reuse the scraper's ownership discipline, while keeping commercial quota separate from global capacity.

For stock limits, lock an owner/usage row and enforce atomically with insertion; do not check `count()` outside a transaction and assume simultaneous requests cannot exceed the limit. Reconcile cached counters against source records. Reordering stages, editing an existing lead or viewing records should not accidentally consume a creation unit.

## Changes, cancellations and data preservation

- A local admin edit of a plan name does not change the Stripe Price.
- Switching a paid financial plan is a validated Stripe operation, with local pending state until synchronized. Proration/effective timing requires approval.
- A temporary grant is a local access exception and must be identified as such to support staff.
- Downgrade below existing stock does not erase records. Proposed policy is to retain existing data and restrict net-new consumption only after explicit approval.
- Preserve catalog and assignment history referenced by subscriptions. Archive unavailable offers rather than cascading deletion.
- Resolver cache keys include user, policy version and assignment version; invalidate after committed catalog/assignment/override changes. Correctness must not depend on stale cache eviction.

## Acceptance tests and decisions

Required tests: four stable plan keys without prices; Free without checkout; paid plan without mapping rejects checkout; archived price cannot be selected; no role elevation; undefined/zero/unlimited distinction; expiry and priority of overrides; concurrent limit reservation; idempotent settlement; downgrade retains data; assignment overlap is rejected; test/live mapping cannot cross environments.

Operator decisions remain: prices/currency/interval; benefits and limits; public onboarding; payment delinquency policy; access after cancellation; grace/trial policy; upgrade/downgrade timing; exception authority and expiry; unit definitions and reset timezone. Implementation must not invent these values.

See [billing](08-stripe-billing.md), [administration](09-admin-panel.md), and [logical model](03-database-model.md). These entities are a staged design proposal, not authorization to create every table now.
