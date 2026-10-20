# Decision register and concrete risks

Status date: 2026-10-20. Findings derive from the local code snapshot, not a new VPS or browser audit.
A prior green report is historical evidence, not proof that the following paths are safe.

## Prioritized risk matrix

| ID | Finding / evidence | Impact | Classification | Required gate |
| --- | --- | --- | --- | --- |
| R01 | MySqlConcurrencyTest performs framework setup before its guard; TestingMigrate validates declared variables and selected identity but not all effective target overrides | Wrong-target test writes possible if configuration diverges | Guard gap verified; accidental write not observed | A00 |
| R02 | PipelineController destroyStage checks count before transaction, no shared source/owner lock | New opportunity can be cascade-deleted during stage deletion | Race window verified; actual-controller concurrent reproduction needed | F01 |
| R03 | Poll renewal uses affected-row count with same-second timestamp; import breaks yet caller finishes ok | Partial import falsely marked complete | Code path verified; targeted multirow MySQL reproduction pending | F03 |
| R04 | Run::failed frees capacity if no external ID; reservation does not recheck locked current status/ID | Hard crash can oversubscribe; delayed duplicate can POST after completion | Code windows verified; actual-job process tests pending | F02 |
| R05 | Poll max_duration branch and failed() select/update lack atomic token condition | Stale owner can mutate newer state | Window verified; injected-interleaving regression pending | F02 |
| R06 | Poll elapsed guard runs before status even for unknown with external ID | Expired job never reaches external truth; capacity stuck | Recovery dead end verified | F02 |
| R07 | cancel_requested not reconciled/cancelled; repeat delete can hide ongoing work | Orphaned work/capacity | Contract incomplete in code | F02 |
| R08 | Panel POST/DELETE has no custom CSRF header; /dashboard lacks explicit token cookie path used in /painel | 419 mutation failures despite working GET | Contract mismatch verified; browser HTTP validation pending | A01 |
| R09 | register does not authenticate; login refuses unverified; verify route requires auth | Signup verification loop | Code path verified | B01 |
| R10 | Admin email edit keeps verified timestamp; final-admin check inconsistent/non-atomic | Wrong-address trust or loss of final administrator | Code gap verified | B01, E01 |
| R11 | Related-row owner equality not represented by composite FKs; controllers currently enforce most paths | Future worker/admin code could join across owners | Partial protection; tests required per new action | B03 |
| R12 | Whole CSV in memory, line-split parser, no import outcome report; arbitrary search_parameters | Truncation/errors/resource pressure; weak bounds | Limitation verified | F03, A04 |
| R13 | External SQL workers duplicate SQL rather than invoking application actions | False confidence in controller/job concurrency | Coverage gap verified | A00, F01–F03 |
| R14 | Unsafe URL schemes and CSV formula cells not consistently normalized | Stored-link XSS or spreadsheet formula execution | Defense gap; exploit not attempted | F04 |
| R15 | Docs deployment timeout 900 conflicts with job75/retry120; DS blue differs from requested target | Unsafe rollout or visual drift | Documentation conflict verified | M01/M04, A01 |
| R16 | Public /app/painel.html can bypass Laravel shell guard; no private server content embedded currently | Shell accessible; future inline data would leak | Exposure depends on Nginx; not audited live | A01 |
| R17 | Public map tiles/geocoder and Google Fonts/CDN dependencies | Availability, aggregate quotas, privacy/policy risk | Code references verified; production account/legal suitability unverified | A04 |
| R18 | No billing/webhook/outbox/account isolation implementation yet | Duplicate/external side effects if implemented naively | Future design risk | D01–D04, I01–L02 |

Fixes are planned, not applied in this documentation phase. A00 builds trustworthy reproduction and guard evidence; F01–F03 repair the existing mutable core before enabling scraping or exposing expanded operations.

## Decisions requiring administrator approval

| ID | Decision | Safe interim behavior / affected tasks |
| --- | --- | --- |
| P01 | Reconcile requested blue #1a73e8 with current #0075ff tokens | No CSS change; A01 visual implementation waits for explicit value |
| P02 | Exact scope for legacy scraper shell adaptation, toggle removal and user header | Preserve original files until A04 authorized |
| P03 | Prices, currency, cadence, trials, quotas, plan-feature matrix and grace/access policy | Four catalogue records only; no live billing or invented entitlements |
| P04 | Approve Cashier preference after dependency/API compatibility review | No package install; D01 uses sandbox after approval |
| P05 | Stripe legal entity, region/account methods, tax/invoicing/cancellation/refund policy | No live Checkout; availability must be verified on actual account |
| P06 | How canceled/past_due users may read/export their retained data | Billing/account/secure export remains conceptually separate; no data deletion |
| P07 | WhatsApp provider: direct official Cloud API or official BSP; account ownership/onboarding model | No reuse of CRM Evolution; provider adapter remains proposal |
| P08 | Marketing email provider, sender domain ownership and who pays provider costs | No sends, no local bulk SMTP server |
| P09 | Consent/purpose model, controller/processor roles, retention periods, deletion/export procedure | Block campaigns without positive channel eligibility; seek legal review |
| P10 | RPO, RTO, retention/off-host backup region and restore window | Backups not assumed sufficient until tested |
| P11 | Whether multiple deals per Lead, multiple pipelines or team collaboration are needed | Keep one opportunity/lead/user, one personal workspace |
| P12 | Feature rollout order, resource budget and worker activation window | Modules unavailable until implemented/approved |
| P13 | Contact dedup/merge authority and handling of historic job attribution | Avoid automatic destructive merges |
| P14 | Whether real-time transport/recurrence is required | Start with bounded polling and one-off activities/events |

## Simplification decisions

Keep user_id, one DB, Eloquent and native queues. Keep contacts deferred until communication needs a person/address identity distinct from a company Lead. Activities and calendar_events have distinct task-versus-time-slot semantics; avoid a generic polymorphic everything table. Reuse Laravel broker/notifications. Choose one financial subscription projection; no parallel homegrown and Cashier subscriptions. Reuse one webhook receipt/outbox pattern rather than one framework per integration.

## Evidence recording

Implementation outcomes belong in [progress](../implementation/PROGRESS.md). Record task ID, exact code commit/worktree scope, changed files, isolated target identity (no secrets), test names/counts/skips, actual runtime observations and remaining gaps. Mark proposed ADRs accepted only after approval; never turn an old successful report into a newly observed fact.
