# Parceiros Kaizen — Design System

This document defines the visual baseline for new product interfaces. The original scraper interface remains an independent, protected design surface.

## Identity

The product name is **Parceiros Kaizen**. The official logo is `public/auth/logo-kaizen.png`. Preserve its proportions, use it without artificial filters or shadows, and keep it centered where the layout calls for a brand mark. Do not modify the original scraper assets.

## Typography

The family is Inter, loaded from Google Fonts with weights 400, 500, 600, and 700. The login uses 20px/1.3 weight 600 for its title, 15px/1.4 for controls and 16px/1.4 weight 600 for the primary button. Use the existing font stack `Inter, sans-serif` and retain visible focus states.

## Colors and states

The approved login tokens are:

| Token | Value | Use |
| --- | --- | --- |
| Primary blue | `#0075ff` | Primary actions and links |
| Blue hover | `#0065df` | Hover state |
| Text | `#141a20` | Main text |
| Muted icon | `#7e8793` | Field icons |
| Placeholder | `#969ca8` | Placeholder text |
| Border | `#dce0e6` | Controls |
| Background | `#f4f6f8` | Page background |
| Surface | `rgba(255,255,255,.96)` | Cards |
| Error | `#b42318` | Validation errors |
| Success | `#188038` | Success messages |

Disabled controls use reduced opacity and retain their normal shape. Do not introduce new colors without a documented reason and approval.

## Components

Use the existing login patterns for primary buttons, inputs, checkboxes, cards, links, alerts, and forms. New secondary buttons, tables, modals, navigation, empty states, and loading states are proposed patterns only until implemented; they should reuse the tokens in `public/design-system/tokens.css` and preserve keyboard focus, contrast, and clear disabled states.

## Layout

The compact login card is approximately 400px wide with 28px horizontal padding, 22px radius, and the documented card shadow. Controls are 44px high with 10px radius; the primary button is 46px high with 12px radius. Use 16px page gutters on small screens and allow content to wrap below 520px.

## Accessibility

Every control has an associated label, including visually hidden labels where icons provide the visual cue. Preserve keyboard navigation, visible focus outlines, sufficient contrast, adequate checkbox hit areas, and explicit error/status messaging. Disabled registration is presented without a broken link and exposes its unavailable state accessibly.

## Governance

New interfaces must consult this document and reuse the tokens. Do not change the design system or modify existing screens without explicit approval. The scraper design and assets are protected and are not replaced by this system.
