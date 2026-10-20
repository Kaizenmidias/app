# User journeys and state contracts

Status: proposed UX and acceptance plan. Existing building blocks are distinguished below; no screen is changed by this document. Real Portuguese product labels follow the approved Design System.

## Journey map

| Journey | Existing building block | Proposed complete flow and errors | Release gate |
| --- | --- | --- | --- |
| Signup | Partner-only gated registration and notification | Explicitly open only after approval -> validate -> limited unverified session -> signed email verification -> active account -> plan selection. Duplicate/expired mail/retry state without exposing another account | B01; commercial policy C/D |
| Login | Password hash check, active + verified, regenerated session, intended /dashboard | Valid credentials -> intended authorized page or /dashboard; generic invalid account response; no scraper auto-start. Unverified recovery route reachable after B01; blocked remains denied | B01/A03 |
| Password recovery | Laravel broker request/token/reset views | Generic request acknowledgment for known/unknown; expiring single-use token; validated confirmation; invalidate session/remember strategy explicitly | B01/B02 |
| Choose plan | Absent | Four catalog names, undefined benefits/prices visibly unavailable; Free selects local assignment; paid requires configured Stripe mapping, not arbitrary frontend price | C01/C02 |
| Subscribe | Absent | Owned pending billing operation -> hosted Checkout sandbox -> return pending -> signed event -> reconciled state; safely reuse operation on retry | D01–D04 |
| First access | Existing original panel search summary | Shared shell, initials/photo + first-name greeting, real empty dashboard, available modules only; no fake onboarding metrics | A02/A03 |
| Prospect | Existing map/history/legacy API plus async backend | Validate request -> explain pending/working/unknown -> bounded polling -> terminal import report; cancellation does not claim external stop without confirmation | A04/F02/F03 |
| Import Leads | Existing automatic import at external completion | Show inserted/updated/rejected/partial stats only once implemented. No redundant manual import command implying all original CSV already persisted | F03/F04 |
| Create opportunity | Existing one per lead + first stage | Owned Lead -> create stage if none -> idempotent send to first ordered stage -> show existing/new success | F01 |
| Manage Pipeline | Existing Kanban/select + stage management | Move/rename/reorder -> server confirmation -> truthful counts; occupied delete chooses destination, previews count and confirms -> atomic transfer, never silent deletion | F01/F05 |
| Account | Reset and admin editing exist; no self-service | Profile/timezone/private avatar; current-password-confirmed sensitive changes; new email unverified; own session controls; separate billing recovery | B02 |
| Change plan | Absent | Choose configured offer -> disclose approved timing/proration -> provider operation pending -> sync -> update entitlements with preserved data | D04/E03 |
| Cancel | Absent | Confirm approved immediate/period-end rule -> provider accepts -> sync state -> retain history/CRM data -> account/billing access per approved policy | D04/P06 |
| Inadimplence | Absent | invoice failure -> reconcile current state -> approved notice/grace/access policy -> Portal recovery -> restore eligible operations on authoritative update | D04/P03 |
| Administrator | Existing /admin role middleware and basic users | Verified active admin -> explicit admin navigation -> audited controlled actions -> final-admin protection -> no partner-data browsing implied | E01–E03 |

## Common interaction contract

- Empty: explain what exists and the next authorized action; do not seed fictitious Leads/stages/opportunities.
- Loading: bounded pending state; prevent duplicate command submissions; allow reading authoritative status after refresh.
- Validation: field-level errors, preserve safe input; do not preserve passwords or echo provider secrets.
- Network/queue failure: stable operation ID, safe retry classification, honest unknown state; never label unknown as canceled/sent/paid/completed.
- Permission/account block: generic resource denial, clear account-state recovery where appropriate; no cross-owner names in error details.
- Conflict: explain record/stage changed elsewhere and refresh/retry only safe local transaction; do not silently drop opportunities.
- Accessibility: semantic labels, focus restoration after dialogs/DnD, keyboard alternatives, responsive nav, readable status. Visual tests require real browser evidence.

## Metric definitions

Available with current schema: owner Lead count, current opportunity count/by stage, total jobs/by explicit status/date. Existing panel's search-result metrics are not CRM revenue.

After F05: open/won/lost opportunity counts and potential value grouped by currency when explicitly provided; not revenue. After G01/G02: pending/overdue activities and upcoming events in user timezone. Historical funnel conversion/trend requires timestamped transitions/cohort definition, not today's stage counts. Never display a monetary zero for missing value as though measured.

## Commercial access and retained data

Design separate screens/actions for account recovery, payment recovery and security suspension. Neither plan downgrade nor cancellation destroys records. Exact read-only/export/grace periods remain P03/P06 decisions. A website success redirect does not establish paid subscription. Status placeholders must not imply a configured provider or shipped module.
