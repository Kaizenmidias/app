# Navigation, shared interface, and legacy migration

Status: proposed implementation architecture. This phase creates documentation only. No original scraper HTML/CSS/JavaScript, login, route, or design token is changed.

## Audited surfaces

| Surface | Existing implementation | Finding |
| --- | --- | --- |
| Dashboard and panel | `routes/web.php` returns `public/app/painel.html` through `BinaryFileResponse` for `/dashboard` and `/painel` | Same physical file, protected by auth/active/verified. Static markup cannot render authenticated name, photo, shared Blade navigation, or logout CSRF itself. |
| Map | `/prospecting` returns `resources/views/prospecting.html`; loads `public/app/style.css` and `app.js` | Original map/search interface with its own floating controls and Mapa/Painel toggle. |
| CRM pages | Leads/Pipeline extend `resources/views/layouts/scraper-shell.blade.php` | Reusable shell already exists, but sidebar markup is separate from original panel/map and still branded Google Maps Scraper. |
| Branding | `public/auth/logo-kaizen.png` is the documented official mark | Shell currently uses `app/img/logo.png`, not the official Kaizen mark. No profile photo field/upload path exists. |
| Fonts | All three surfaces load Google Fonts Inter weights 400/500/600/700 | External fetch required in current design; CSS declaration alone does not prove successful browser render. No bundled font was found in audited sources. |
| Styles | `public/design-system/tokens.css`, `scraper-shell.css`, `leads.css`, `pipeline.css`, original `app/painel.css`/`style.css` | Two blue/token families currently coexist; see decision below. |

### Exact design discrepancy requiring a recorded decision

The new product request specifies **Inter and blue `#1a73e8`**. Original panel CSS defines `--blue:#1a73e8`; `public/scraper-shell.css` defines `--shell-blue:#1a73e8`. However the approved [`DESIGN_SYSTEM.md`](../DESIGN_SYSTEM.md) and [`tokens.css`](../../public/design-system/tokens.css) still define `--kaizen-blue:#0075ff` and hover `#0065df`. Leads buttons use the Kaizen token; Pipeline buttons use a `#4285f4 -> #1a73e8` gradient. This is observable source inconsistency, not a request to silently recolor the login.

Record the requested future platform blue in the decision log and obtain approval for its scope before an implementation phase changes the canonical tokens or existing approved screens. Until that decision is accepted, retain existing token values and explicitly preserve the login. A future accepted change must update the documentation and token aliases together, with screenshot comparison and accessibility checks. Do not introduce a third palette or duplicate new `:root` values per module.

## Intended application navigation

The user-facing labels remain Portuguese; paths and component filenames remain English. Only the first four module routes exist now. Proposed paths below are contracts for future phases, not links to publish today.

| Order | Sidebar label | Route | State |
| --- | --- | --- | --- |
| 1 | Visão geral | `/dashboard` | Existing; currently original search-oriented panel, not yet consolidated CRM overview |
| 2 | Prospecção | `/prospecting` | Existing map/search interface |
| 3 | Leads | `/leads` | Existing private paginated list |
| 4 | Pipeline | `/pipeline` | Existing private Kanban |
| 5 | Chats | `/chats` | Proposed; no live link until module is authorized and implemented |
| 6 | Números conectados | `/connections` | Proposed; provider decision required |
| 7 | Disparar | `/campaigns` | Proposed; consent/provider dependencies |
| 8 | E-mail Marketing | `/email-marketing` | Proposed; sender/provider dependencies |
| 9 | Automações | `/automations` | Proposed |
| 10 | Agenda | `/calendar` | Proposed |
| 11 | Briefings | `/briefings` | Proposed |
| 12 | Minha conta | `/account` | Proposed |

Do not add fictitious functional pages or broken `href` values for these future modules. Render a clear unavailable state such as “Em desenvolvimento” with no click action, or hide a not-yet-approved commercial feature. Which modules are plan-gated remains undecided. An active subscription is not an admin permission.

Partner shell requirements:

- A real authorized photo or deterministic initials, then “Bem-vindo, [first name]”. Derive a display first name from the user's own saved name; do not introduce a legal-name requirement.
- One shared menu definition and one shared sidebar template, server-rendered with current route and authenticated account.
- Official Kaizen logo at the sidebar footer, preserved proportion; logout is POST with CSRF.
- No Mapa/Painel toggle after legacy migration. `/painel` remains a compatibility alias to the approved overview or the same shared view; do not silently reuse it for a different product.
- Separate `/admin` shell/entry for authorized administrators. Partner pages remain owner-scoped even when the signed-in actor is an admin; broad audit/admin access must be explicit and separately authorized.
- Desktop fixed sidebar; mobile collapsible navigation controlled by a labeled button, with keyboard focus handling, `aria-expanded`, escape/overlay close and reduced-motion support. Avoid duplicating the menu DOM just to serve mobile.

## Current integration defects to resolve before shell consolidation

These findings come from code and have not been newly exercised through production in this documentation phase.

1. `Route::redirect('/', '/dashboard')` conflicts with original `painel.html` links `#to-map` and `#open-in-map` pointing to `/`. `painel.js:449-450` rewrites them to `/#job=...`; that returns to the dashboard instead of the map. Target `/prospecting#job=...` in an explicitly authorized legacy integration patch.
2. Original `painel.js:295` DELETE and `:425` POST use `fetch()` without `_token` or `X-XSRF-TOKEN`, while `/api/v1/jobs` is protected by `EnsureFrontendCsrf`. `app.js:7-8` has a CSRF-aware wrapper; panel code does not. A response HTTP 200 and successful KPI GET are insufficient proof that create/delete works.
3. `painel.js`/`app.js` use strict equality against a string `dataset.id`/hash. Legacy API maps numeric Eloquent IDs to `ID`, unlike Gosom's string IDs. Normalize identifiers once at the API boundary/client adapter before DOM lookup, and add click/hash regression tests.
4. Both original pages link `/classic`, with no matching route in `routes/web.php`. Decide whether to retain a supported legacy route or remove the obsolete link in a specifically approved integration patch; do not present it as working today.
5. `setHealth()` treats a successful Laravel job-list GET as “Scraper online”. That proves session/API access, not Docker/worker/Gosom health. Provide a future restricted health capability/status response or change wording to the actual state; do not proxy unrestricted Docker health details to partners.
6. Legacy statuses recognize `pending/working/ok/failed` while backend can produce `unknown` and `cancel_requested`. Provide explicit honest UI states/actions rather than displaying internal raw state or suggesting a cancellation is confirmed.
7. `BinaryFileResponse` cannot compose Blade slots, profile text, named-route links, or a menu definition. Do not solve this by injecting a second sidebar or wrapping the whole map in an iframe.

## Proposed shared component contract

Introduce these components only in the authorized visual/navigation phase:

| Proposed component | Responsibility | Inputs / restrictions |
| --- | --- | --- |
| `layouts/partner.blade.php` | Document head, canonical tokens/font, single sidebar, main content slot, page script slots | Authenticated user; no private data emitted into global scripts without need |
| `components/partner/sidebar.blade.php` | One shared brand/welcome/nav/footer/logout layout | Server-owned module definitions; route existence/capability; named routes |
| `components/partner/nav-item.blade.php` | Active/unavailable state, icon, label, accessible status | Allowlisted icon/label; route-generated URL; no arbitrary HTML |
| `components/ui/button`, `input`, `alert`, `empty-state` | Compact controls with design tokens and standard states | Preserve actual semantics and visible focus; avoid broad global tag selectors |
| `components/partner/account-menu` | Name/avatar, account link when available, logout | Owner account data, no admin powers inferred from subscription |
| `resources/navigation/partner.php` or equivalent config | Ordered 12-module registry | Label, named route if implemented, availability/capability, icon key; backend middleware remains authoritative |
| Namespaced map/panel page assets | Original behavior inside page content | Existing DOM IDs/event dependencies preserved through deliberate mapping; no global DOM rewrite |

Choose Blade and existing vanilla JavaScript. A SPA rewrite, iframe, new framework or heavy drag library is not required by this task. Reuse the shell's structure and accessible Pipeline controls rather than replacing working business logic.

## Incremental legacy migration with no duplicated sidebar

1. **Freeze behavior and identify selectors.** Record hashes/screenshots of map/panel pages and inventory all `#id`, `.nav-item`, map sizing, overlay, hash/deep-link and keyboard dependencies in `app.js`/`painel.js`. Add HTTP plus browser smoke coverage for token-bearing writes using fake jobs in testing.
2. **Approve the visual transition.** The current protection rule forbids editing original assets. The master plan authorizes planning their integration, not editing them now. A later prompt must explicitly permit the minimum extraction from original HTML and selector adaptation while preserving map/search/results behavior. Record the token decision before restyling.
3. **Build the common shell independently.** Refactor only Leads/Pipeline into a shared sidebar/layout first. Render all implemented modules from one registry; leave future modules unavailable. Preserve current login as a separate auth layout.
4. **Extract body content, not copies of full pages.** Create a page-specific panel content view from the original main region and a map content view from map/controls/overlays. Keep one authoritative copy of each content region. Original HTML remains a reference until all routes migrate; no runtime duplication of panel markup in dashboard and `/painel`.
5. **Migrate dashboard and `/painel` together.** Both render one view. Preserve original panel data contracts while moving Mapa/Painel navigation into module navigation. Guard the intersection observer so it only manages within-page links, not shared module items.
6. **Migrate Prospecção in a separate patch.** Shared sidebar remains visible. Place the map in the main content region with explicit width/height; call `invalidateSize()` when navigation/layout changes. Preserve search/filters/result overlays and move the existing controls without creating a second product sidebar. Test desktop/mobile bounds and Leaflet z-index containment.
7. **Centralize the API adapter.** Normalize IDs/statuses and CSRF behavior for both pages, preserving Laravel owner checks. Backend adapter remains between legacy JSON/CSV and Laravel/Gosom. Do not expose Gosom directly or rewrite the scraper engine.
8. **Remove obsolete markup only after validation.** No original source deletion until references, licenses, fallback routes and tests are reviewed. Preserve attribution in distributed license/notice files; remove promotional interface links only when explicitly authorized.

This sequence reduces duplicated UI without falsely promising zero code changes to legacy pages. Preserving the existing visual/behavioral contract and replacing the old navigation are distinct acceptance criteria and need a side-by-side approval.

## Component states and validation

- Loading: scoped busy state, retain sidebar, announce updates without stealing focus. Avoid one stalled module blocking the entire page.
- Empty: explain the real prerequisite and link only to an implemented next action; no demo metrics.
- Errors: sanitize external failure messages, preserve entered data and prior card position; show retry only when operation is idempotent.
- Permissions/subscription: explain why an action is unavailable only from server-authoritative reason, with no leak of other users' identifiers or records.
- Mobile: test 390x844 and desktop 1440x900; Kanban horizontal scroll is contained in content; select alternative remains usable; forms/dialogs fit viewport and keyboard.
- Visual evidence: inspect rendered Inter in DevTools where available, network font load, fallback behavior, contrast and focus. If browser unavailable, state limitation; never claim CSS declarations prove pixels.
- Regression: login/session/CSRF, `/dashboard`, `/painel`, `/prospecting`, Leads/Pipeline, cross-owner IDs, admin middleware, asset 200, absent public signup, deep links, protected file routes and no duplicate sidebar. No production scraping or real credentials.

See [module contracts](06-crm-modules.md) and [data model](03-database-model.md) for business ownership and state boundaries.
