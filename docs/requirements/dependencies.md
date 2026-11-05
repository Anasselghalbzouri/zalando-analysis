# Module dependencies — Phase 02

An edge **A → B** means A uses data or UI from B. Edges are CONFIRMED where they come from an observed link or shared component, and HYPOTHESIS where they're inferred. Back-office nodes are HYPOTHESIS: they aren't observable.

```mermaid
flowchart LR
  M01[M01 Header] --> M08[M08 Search]
  M01 --> M14[M14 Cart]
  M01 --> M15[M15 Account]
  M02[M02 Nav] --> M05[M05 Events]
  M02 --> M06[M06 Listing]
  M05 --> M03[M03 Campaigns]
  M05 --> M04[M04 Upcoming]
  M05 --> M10[M10 Recos]
  M05 --> M11[M11 Banners]
  M04 --> M03
  M04 -.-> M15
  M03 --> M06
  M06 --> M07[M07 PDP]
  M06 --> M12[M12 Pricing]
  M08 --> M06
  M09[M09 Brand] --> M06
  M07 --> M03
  M07 --> M09
  M07 --> M12
  M07 --> M13[M13 Scarcity]
  M07 --> M14
  M07 --> M16[M16 Plus]
  M07 --> M17[M17 Delivery]
  M10 --> M07
  M13 --> M14
  M14 -.-> M21[M21 Checkout - OUT OF SCOPE]
  M18[M18 Footer] --> M19[M19 Consent]
  M18 --> M22[M22 Apps]
  M18 --> M15
  BO1[(Campaign mgmt - HYPOTHESIS)] -.-> M03
  BO2[(Stock & reservation - HYPOTHESIS)] -.-> M13
  BO3[(CMS - HYPOTHESIS)] -.-> M11
  BO4[(Reco engine - HYPOTHESIS)] -.-> M10
  BO5[(Notification service - HYPOTHESIS)] -.-> M04
```
Solid lines are CONFIRMED (link or shared UI observed). Dotted lines are HYPOTHESIS. M20 (analytics) touches every module and is left out of the diagram for readability.

## Core domain entities implied by the dependencies
These are for the Laravel/MySQL design. Each one is a candidate, labelled by how much of it was observed.

| Entity | Evidence | Class |
|---|---|---|
| Event (landing) | numeric ID, title, hero, ordered campaign list | CONFIRMED (fields as displayed) |
| Campaign | ID `ZZO….`, name, image, start, end, headline % or "Special", product set | CONFIRMED (display) / HYPOTHESIS (stored fields) |
| Event ↔ Campaign | many-to-many (BR-03-13) | CONFIRMED |
| Product / model | model code (e.g. `NI112P025`), brand, attributes | CONFIRMED (display) |
| SKU / article | `H1X44A00D-A11`, campaign-scoped URL, RRP, sale price, sizes, stock hint | CONFIRMED (display) |
| Campaign ↔ article | campaign-scoped addressing (BR-03-01). One model can be in many campaigns (BR-03-14) | CONFIRMED |
| Brand | code `H1X`, name | CONFIRMED |
| Category | numeric ID tree (sitemap §3) | CONFIRMED |
| Upcoming subscription | the "Informez moi" button | HYPOTHESIS (entity not observable) |
| Cart reservation | 20-minute timer, "Réservé" group, release on remove (M14 BR-14-01..03) | CONFIRMED |
| Order fee | 3,90 € per order (BR-12-06) | CONFIRMED (help text) |
| Member preferences | newsletter frequency, saved size filters per gender/group (M15) | CONFIRMED |

## Build-order implication (dependency-driven)
1. Category, Brand
2. Product/SKU
3. Campaign (+ article assignment)
4. Event (+ campaign assignment)
5. Listing/filter API
6. PDP
7. Search
8. Upcoming/notify
9. Recommendations
10. Cart/checkout (separate spec, see Q-04)
