# Requirements traceability matrix — Phase 04

Each row traces one requirement back to its module, rules and evidence, and forward to use cases, user stories and acceptance criteria.

- **Evidence** stems: `a11y/<stem>` = `evidence/a11y/<stem>.yaml`; `shot/<stem>` = `evidence/screenshots/<stem>.png`; `har #n` = entry *n* of `evidence/har/zalando` (see `evidence/har-sanitized/zalando.summary.json`).
- **BR** IDs are in [business-rules.md](business-rules.md); **Q** IDs in [open-questions.md](open-questions.md); modules in [`docs/modules/`](../modules/).
- Priority is **proposed** (basis in [PRD.md](PRD.md) §8).

| Req ID | Module | Evidence | Priority | Class | Conf. | Use cases | User stories | Acceptance criteria | Open Q |
|---|---|---|---|---|---|---|---|---|---|
| FR-NAV-01 | M01 | BR-01-02; `a11y/home_desktop`; `a11y/campaign_desktop`; `a11y/product_desktop` | Must | CONFIRMED | HIGH | UC-01 | — | AC-FR-NAV-01-1…3 | — |
| FR-NAV-02 | M01 | BR-01-01; `a11y/home_desktop` | Should | CONFIRMED | HIGH | — | US-01 | AC-FR-NAV-02-1 | — |
| FR-NAV-03 | M02 | BR-02-01; `a11y/menu_femme`; `a11y/campaign_desktop`; `evidence/nav-routes.json` | Must | CONFIRMED | HIGH | UC-01 | US-02 | AC-FR-NAV-03-1…2 | — |
| FR-NAV-04 | M02 | BR-02-02; BR-02-03; `a11y/menu_homme`; `a11y/menu_enfant`; `a11y/menu_sports` | Should | CONFIRMED | HIGH | UC-02 | US-03 | AC-FR-NAV-04-1…2 | — |
| FR-NAV-05 | M01, M14 | BR-14-02; `a11y/pdp_added`; `a11y/cart_with_item` | Must | CONFIRMED | HIGH | UC-06 | US-10 | AC-FR-NAV-05-1…2 | — |
| FR-EVT-01 | M05 | BR-05-01; BR-05-02; `evidence/nav-routes.json`; `a11y/page_all` | Must | CONFIRMED | HIGH | UC-01 | US-01, US-02 | AC-FR-EVT-01-1…2 | — |
| FR-EVT-02 | M05, M03 | BR-05-03; BR-05-08; BR-03-13; `a11y/page_femme_scrolled`; `a11y/home_scrolled` | Must | CONFIRMED | HIGH | UC-01 | US-01, US-23 | AC-FR-EVT-02-1…2 | — |
| FR-EVT-03 | M05 | BR-05-09; `a11y/home_scrolled` | Could | HYPOTHESIS | MEDIUM | — | US-23 | AC-FR-EVT-03-1 | Q-50 |
| FR-EVT-04 | M11 | BR-11-01; BR-11-02; `a11y/page_femme`; `a11y/home_desktop` | Should | CONFIRMED | HIGH | UC-01 | US-02 | AC-FR-EVT-04-1…2 | Q-13 |
| FR-EVT-05 | M05 | BR-05-05; `a11y/page_premium_scrolled` | Could | HYPOTHESIS | LOW | — | US-23 | AC-FR-EVT-05-1 | Q-16 |
| FR-CMP-01 | M03 | BR-03-02; BR-03-04; `a11y/home_desktop`; `shot/home_desktop` | Must | CONFIRMED | HIGH | UC-01 | US-01 | AC-FR-CMP-01-1…2 | — |
| FR-CMP-02 | M03 | BR-03-03; `a11y/home_desktop`; `a11y/product_desktop` | Must | CONFIRMED | HIGH | UC-01 | US-01 | AC-FR-CMP-02-1…2 | — |
| FR-CMP-03 | M03, M12 | BR-03-04; BR-12-01; BR-12-05; `a11y/home_desktop` | Must | CONFIRMED | HIGH | UC-01 | US-01 | AC-FR-CMP-03-1 | Q-23 |
| FR-CMP-04 | M03 | BR-03-05; `a11y/home_scrolled` | Should | CONFIRMED | MEDIUM | — | US-23 | AC-FR-CMP-04-1 | Q-24 |
| FR-CMP-05 | M03, M04, M05 | BR-03-09; BR-05-08; BR-03-16; `a11y/page_sports`; `a11y/home_scrolled` | Must | CONFIRMED | MEDIUM | UC-11 | US-23 | AC-FR-CMP-05-1…2 | Q-17 |
| FR-CMP-06 | M03, M07 | BR-03-01; `a11y/product_desktop`; `a11y/search_results` | Must | CONFIRMED | HIGH | UC-05 | — | AC-FR-CMP-06-1 | — |
| FR-CMP-07 | M03 | BR-03-14; `a11y/search_results` | Should | CONFIRMED | MEDIUM | — | US-24 | AC-FR-CMP-07-1 | Q-21 |
| FR-CMP-08 | M03 | BR-03-15 | Could | HYPOTHESIS | MEDIUM | UC-11 | US-23 | AC-FR-CMP-08-1 | Q-17; Q-21 |
| FR-CMP-09 | M03, M04 | BR-03-16; BR-03-18; BR-03-10; BR-04-05; `a11y/public_help_ventes` | Should | CONFIRMED | MEDIUM | UC-11 | US-23 | AC-FR-CMP-09-1…2 | Q-20; Q-33 |
| FR-CMP-10 | M11, M03 | BR-11-03; BR-03-05; `a11y/home_desktop` | Should | CONFIRMED | HIGH | — | US-23, US-24 | AC-FR-CMP-10-1 | — |
| FR-UPC-01 | M04 | BR-04-02; BR-03-11; `a11y/page_sports`; `a11y/home_scrolled` | Should | CONFIRMED | HIGH | UC-08, UC-11 | US-14 | AC-FR-UPC-01-1…2 | — |
| FR-UPC-02 | M04 | BR-03-12; BR-04-03; `a11y/page_sports`; `a11y/home_scrolled` | Should | CONFIRMED | MEDIUM | UC-08 | US-14 | AC-FR-UPC-02-1…2 | Q-10; Q-35 |
| FR-UPC-03 | M04, M05 | BR-05-04; BR-04-01; `a11y/page_all`; `a11y/page_habitat` | Should | CONFIRMED | HIGH | UC-08 | US-14 | AC-FR-UPC-03-1 | Q-27 |
| FR-UPC-04 | M04 | BR-04-06; `a11y/page_sports` | Could | HYPOTHESIS | LOW | UC-08 | US-15 | AC-FR-UPC-04-1 | Q-01; Q-47 |
| FR-CAT-01 | M06 | `a11y/campaign_desktop`; `a11y/category_80000687_outline`; `a11y/brand_H1X`; `a11y/search_results` | Must | CONFIRMED | HIGH | UC-02, UC-04, UC-09 | US-03 | AC-FR-CAT-01-1 | — |
| FR-CAT-02 | M06, M12 | BR-12-01; `a11y/campaign_desktop`; `shot/campaign_desktop` | Must | CONFIRMED | HIGH | UC-03 | US-08 | AC-FR-CAT-02-1 | — |
| FR-CAT-03 | M06 | BR-06-01; BR-06-02; `a11y/campaign_sort_open` | Must | CONFIRMED | HIGH | UC-03 | US-04 | AC-FR-CAT-03-1…2 | Q-43 |
| FR-CAT-04 | M06 | BR-06-08; `a11y/campaign_desktop`; `a11y/category_80000687_outline` | Must | CONFIRMED | HIGH | UC-02, UC-03 | US-03, US-05 | AC-FR-CAT-04-1…2 | Q-14 |
| FR-CAT-05 | M06 | BR-06-03; `a11y/campaign_size_open`; `a11y/campaign_price_open` | Should | CONFIRMED | HIGH | UC-03 | US-05 | AC-FR-CAT-05-1 | — |
| FR-CAT-06 | M06 | BR-06-04; `a11y/campaign_size_open` | Should | CONFIRMED | MEDIUM | UC-03 | US-05 | AC-FR-CAT-06-1 | — |
| FR-CAT-07 | M06 | BR-06-05; `a11y/campaign_price_open` | Should | CONFIRMED | MEDIUM | UC-03 | US-05 | AC-FR-CAT-07-1 | — |
| FR-CAT-08 | M06 | BR-06-06; `a11y/campaign_sort_open`; `a11y/brand_H1X`; `a11y/category_80000687_outline` | Should | CONFIRMED | HIGH | UC-03 | US-05 | AC-FR-CAT-08-1…2 | — |
| FR-CAT-09 | M06 | BR-06-07; `a11y/campaign_scrolled` | Must | CONFIRMED | HIGH | UC-03 | — | AC-FR-CAT-09-1 | Q-11; Q-60 |
| FR-PDP-01 | M07 | BR-07-01; `a11y/product_desktop`; `shot/product_desktop` | Must | CONFIRMED | HIGH | UC-05 | US-08 | AC-FR-PDP-01-1…2 | — |
| FR-PDP-02 | M07 | BR-07-06; `a11y/pdp_add_no_size` | Must | CONFIRMED | HIGH | UC-06 | US-09 | AC-FR-PDP-02-1 | — |
| FR-PDP-03 | M07, M14 | BR-07-07; BR-14-02; `a11y/product_desktop`; `a11y/pdp_added` | Must | CONFIRMED | HIGH | UC-06 | US-10 | AC-FR-PDP-03-1 | Q-51 |
| FR-PDP-04 | M07 | BR-07-08; `a11y/pdp_added` | Could | HYPOTHESIS | MEDIUM | UC-06 | — | AC-FR-PDP-04-1 | Q-46 |
| FR-PDP-05 | M07, M13 | BR-07-02; BR-13-01; `a11y/product_desktop`; `a11y/pdp_added` | Must | CONFIRMED | HIGH | UC-05 | US-08, US-22 | AC-FR-PDP-05-1…2 | Q-26 |
| FR-PDP-06 | M07, M17 | BR-07-04; BR-17-02; `a11y/product_desktop` | Should | CONFIRMED | MEDIUM | UC-05 | US-08 | AC-FR-PDP-06-1 | Q-49 |
| FR-PDP-07 | M07, M17 | BR-07-03; BR-17-01; BR-17-04; `a11y/product_desktop` | Must | CONFIRMED | HIGH | UC-05 | US-08 | AC-FR-PDP-07-1 | Q-31 |
| FR-PDP-08 | M07 | `a11y/product_desktop` | Should | CONFIRMED | HIGH | UC-05 | US-08 | AC-FR-PDP-08-1 | — |
| FR-SRC-01 | M08 | BR-08-01; `a11y/search_suggest`; `shot/search_suggest` | Must | CONFIRMED | HIGH | UC-04 | US-06 | AC-FR-SRC-01-1 | Q-44 |
| FR-SRC-02 | M08, M06 | BR-08-02; `a11y/search_results` | Must | CONFIRMED | HIGH | UC-04 | US-06 | AC-FR-SRC-02-1 | Q-44; Q-62 |
| FR-BRD-01 | M09 | BR-09-01; `a11y/brand_H1X`; `shot/brand_H1X` | Should | CONFIRMED | HIGH | UC-09 | US-07 | AC-FR-BRD-01-1 | — |
| FR-BRD-02 | M09 | BR-09-02; `a11y/brand_H1X` | Should | CONFIRMED | HIGH | UC-09 | US-07 | AC-FR-BRD-02-1 | — |
| FR-PRC-01 | M12 | BR-12-01; BR-12-02; `a11y/product_desktop`; `a11y/public_terms` | Must | CONFIRMED | HIGH | UC-05 | US-08 | AC-FR-PRC-01-1 | — |
| FR-PRC-02 | M12, M14 | BR-12-03; BR-12-06; BR-14-05; `a11y/cart_with_item`; `a11y/public_help_livraison` | Should | CONFIRMED | HIGH | UC-07, UC-16 | US-11 | AC-FR-PRC-02-1 | — |
| FR-STK-01 | M13, M14 | BR-13-02; BR-14-03; `a11y/brand_H1X`; `a11y/cart_remove_confirm` | Must | CONFIRMED | MEDIUM | UC-06, UC-12 | US-10, US-22 | AC-FR-STK-01-1…2 | Q-45 |
| FR-STK-02 | M13 | BR-13-04; BR-03-16; `a11y/public_help_ventes` | Must | CONFIRMED | HIGH | — | US-22 | AC-FR-STK-02-1 | — |
| FR-STK-03 | M13 | BR-13-03; `a11y/search_results` | Could | HYPOTHESIS | LOW | — | US-22 | AC-FR-STK-03-1 | Q-36 |
| FR-CRT-01 | M14 | `a11y/cart_empty`; `a11y/cart_with_item` | Must | CONFIRMED | HIGH | UC-07 | US-11 | AC-FR-CRT-01-1…2 | — |
| FR-CRT-02 | M14 | BR-14-01; BR-14-02; `a11y/cart_with_item`; `a11y/pdp_added` | Must | CONFIRMED | HIGH | UC-06 | US-10 | AC-FR-CRT-02-1 | Q-32 |
| FR-CRT-03 | M14 | BR-14-01; BR-14-04; `a11y/cart_with_item` | Must | CONFIRMED | HIGH | UC-07 | US-11 | AC-FR-CRT-03-1 | — |
| FR-CRT-04 | M14 | BR-14-03; `a11y/cart_remove_confirm`; `a11y/cart_after_remove` | Must | CONFIRMED | HIGH | UC-07 | US-12 | AC-FR-CRT-04-1…3 | — |
| FR-CRT-05 | M14, M12 | BR-14-04; BR-14-05; `a11y/cart_with_item` | Must | CONFIRMED | HIGH | UC-07 | US-11 | AC-FR-CRT-05-1 | — |
| FR-CRT-06 | M14 | `a11y/cart_empty`; `a11y/cart_after_remove` | Must | CONFIRMED | HIGH | UC-07 | US-11 | AC-FR-CRT-06-1 | — |
| FR-CRT-07 | M14, M21 | BR-21-01; `a11y/cart_with_item` | Must | CONFIRMED | HIGH | UC-07 | US-13 | AC-FR-CRT-07-1 | Q-04 |
| FR-CRT-08 | M14 | BR-14-06 | Could | HYPOTHESIS | MEDIUM | UC-12 | US-25 | AC-FR-CRT-08-1 | Q-32; Q-37 |
| FR-ACC-01 | M15 | `a11y/account_menu`; `a11y/account_overview` | Should | CONFIRMED | HIGH | — | US-19 | AC-FR-ACC-01-1 | Q-41 |
| FR-ACC-02 | M15 | `a11y/account_overview` | Should | CONFIRMED | HIGH | — | US-19 | AC-FR-ACC-02-1 | — |
| FR-ACC-03 | M15 | `a11y/account_orders` | Should | CONFIRMED | MEDIUM | — | US-19 | AC-FR-ACC-03-1 | — |
| FR-ACC-04 | M15, M17 | BR-15-06; `a11y/account_returns` | Should | CONFIRMED | MEDIUM | UC-15 | US-19 | AC-FR-ACC-04-1…2 | Q-31; Q-38 |
| FR-ACC-05 | M15 | BR-15-03; BR-15-07; BR-04-06; `a11y/account_newsletter` | Should | CONFIRMED | HIGH | UC-13 | US-17 | AC-FR-ACC-05-1…2 | — |
| FR-ACC-06 | M15 | BR-15-04; `a11y/account_newsletter` | Should | CONFIRMED | HIGH | UC-13 | US-17 | AC-FR-ACC-06-1 | — |
| FR-ACC-07 | M15 | BR-15-05; BR-15-07; `a11y/account_filter` | Should | CONFIRMED | HIGH | UC-14 | US-18 | AC-FR-ACC-07-1…2 | Q-39 |
| FR-ACC-08 | M15 | BR-15-01; BR-15-02; `a11y/public_terms`; `a11y/public_help_membre` | Must | CONFIRMED | HIGH | — | US-26 | AC-FR-ACC-08-1…2 | Q-03; Q-40; Q-41 |
| FR-REC-01 | M10 | BR-05-06; BR-10-01; BR-10-03; `a11y/home_scrolled`; `a11y/page_femme_scrolled` | Should | CONFIRMED | HIGH | UC-10 | US-16 | AC-FR-REC-01-1…2 | Q-29 |
| FR-REC-02 | M10 | BR-05-07; BR-10-02; `a11y/home_scrolled`; `a11y/page_homme_scrolled` | Should | CONFIRMED | MEDIUM | UC-10 | US-16 | AC-FR-REC-02-1 | Q-29 |
| FR-DLV-01 | M17, M18 | BR-17-03; BR-17-05; BR-17-06; BR-17-07; BR-17-08; `a11y/public_help_livraison` | Should | CONFIRMED | HIGH | UC-16 | US-20 | AC-FR-DLV-01-1 | Q-49 |
| FR-DLV-02 | M17 | BR-17-01; BR-17-04; `a11y/public_help_retour_delai`; `a11y/public_terms` | Must | CONFIRMED | HIGH | UC-15 | US-20 | AC-FR-DLV-02-1 | Q-31 |
| FR-CNT-01 | M18 | BR-18-01; `a11y/home_scrolled` | Should | CONFIRMED | HIGH | UC-16 | US-20 | AC-FR-CNT-01-1 | — |
| FR-CNT-02 | M18 | BR-18-02; `a11y/public_help_home` | Should | CONFIRMED | HIGH | UC-16 | US-20 | AC-FR-CNT-02-1 | — |
| FR-CNT-03 | M18 | BR-18-03; BR-18-04; `a11y/public_terms` | Must | CONFIRMED | HIGH | UC-16 | US-20 | AC-FR-CNT-03-1 | Q-48 |
| FR-CNT-04 | M19 | `a11y/home_scrolled`; `har #7`; `har #29` | Must | CONFIRMED | MEDIUM | — | US-21 | AC-FR-CNT-04-1 | — |
| FR-CNT-05 | M22 | BR-22-01; `a11y/home_scrolled` | Could | CONFIRMED | HIGH | — | — | AC-FR-CNT-05-1 | — |
| FR-ANL-01 | M20 | BR-20-01; `a11y/campaign_desktop` | Could | CONFIRMED | HIGH | — | — | AC-FR-ANL-01-1 | — |
| FR-OOS-01 | M21 | BR-21-01; BR-21-02; BR-21-03; BR-21-04; BR-21-05 | Won't (this spec) | OUT OF SCOPE | HIGH | UC-X1 | — | AC-FR-OOS-01-1 | Q-04 |
| FR-OOS-02 | M16 | BR-16-01; BR-16-02; `shot/home_plus_modal` | Won't (this spec) | OUT OF SCOPE | HIGH | UC-X2 | — | AC-FR-OOS-02-1 | Q-01 |
| NFR-TECH-01 | Project | CLAUDE.md.txt (Target Technology) | Must | CONFIRMED | HIGH | — | — | AC-NFR-TECH-01-1 | — |
| NFR-I18N-01 | M06, M08 | `a11y/search_suggest`; `a11y/campaign_price_open` | Must | CONFIRMED | HIGH | — | US-27 | AC-NFR-I18N-01-1 | — |
| NFR-I18N-02 | M07, M14 | BR-07-02; `a11y/product_desktop` | Must | CONFIRMED | HIGH | — | US-27 | AC-NFR-I18N-02-1 | — |
| NFR-I18N-03 | M12, M03 | `a11y/cart_with_item`; `a11y/home_scrolled` | Must | CONFIRMED | HIGH | — | US-27 | AC-NFR-I18N-03-1 | — |
| NFR-TIME-01 | M03, M04 | BR-03-17; BR-04-04 | Must | UNKNOWN | LOW | — | — | AC-NFR-TIME-01-1 | Q-10 |
| NFR-A11Y-01 | M01, M02, M07, M14 | `a11y/home_desktop`; `a11y/menu_homme`; `a11y/pdp_add_no_size`; `a11y/cart_remove_confirm` | Must | CONFIRMED | HIGH | — | US-27 | AC-NFR-A11Y-01-1…2 | — |
| NFR-SEC-01 | Phase 03 | docs/technical/authentication-analysis.md A-01; docs/technical/api-observations.md; `har #34`; `har #35` | Must | CONFIRMED | HIGH | — | — | AC-NFR-SEC-01-1 | — |
| NFR-SEC-02 | Phase 03 | docs/technical/authentication-analysis.md A-02; `har #36`; `har #44` | Must | CONFIRMED | MEDIUM | — | — | AC-NFR-SEC-02-1 | — |
| NFR-SEC-03 | Phase 03 | docs/technical/authentication-analysis.md A-04; `har #0`; `har #42` | Must | CONFIRMED | HIGH | — | — | AC-NFR-SEC-03-1 | — |
| NFR-SEC-04 | Phase 03 | docs/technical/authentication-analysis.md A-03; docs/technical/network-analysis.md N-07; `har #0`; `har #42` | Must | CONFIRMED | HIGH | — | — | AC-NFR-SEC-04-1 | — |
| NFR-SEC-05 | M20, Phase 03 | BR-20-02; docs/technical/authentication-analysis.md A-06 | Could | HYPOTHESIS | MEDIUM | — | — | AC-NFR-SEC-05-1 | Q-42 |
| NFR-PRV-01 | M19, M20, Phase 03 | docs/technical/authentication-analysis.md A-07; `har #34`; `har #36` | Must | CONFIRMED | HIGH | — | US-21 | AC-NFR-PRV-01-1 | — |
| NFR-CON-01 | M13, M14 | BR-13-02; BR-13-04; BR-14-03 | Must | CONFIRMED | HIGH | UC-06 | — | AC-NFR-CON-01-1 | — |
| NFR-CACHE-01 | Phase 03 | docs/technical/frontend-analysis.md F-09; `har #4`; `har #26` | Should | CONFIRMED | HIGH | — | — | AC-NFR-CACHE-01-1 | — |
| NFR-OBS-01 | Phase 03 | docs/technical/frontend-analysis.md F-10; docs/technical/network-analysis.md N-08 | Could | CONFIRMED | MEDIUM | — | — | AC-NFR-OBS-01-1 | — |
| NFR-PERF-01 | Phase 03 | docs/technical/network-analysis.md N-05 | Must | UNKNOWN | LOW | — | — | AC-NFR-PERF-01-1 | Q-42 |
| NFR-RESP-01 | All | `a11y/viewport390_connect_no_reflow` | Must | UNKNOWN | LOW | — | — | AC-NFR-RESP-01-1 | Q-15 |

## Coverage by module

| Module | Requirements |
|---|---|
| [M01](../modules/M01.md) | FR-NAV-01, FR-NAV-02, FR-NAV-05, NFR-A11Y-01 |
| [M02](../modules/M02.md) | FR-NAV-03, FR-NAV-04, NFR-A11Y-01 |
| [M03](../modules/M03.md) | FR-EVT-02, FR-CMP-01, FR-CMP-02, FR-CMP-03, FR-CMP-04, FR-CMP-05, FR-CMP-06, FR-CMP-07, FR-CMP-08, FR-CMP-09, FR-CMP-10, NFR-I18N-03, NFR-TIME-01 |
| [M04](../modules/M04.md) | FR-CMP-05, FR-CMP-09, FR-UPC-01, FR-UPC-02, FR-UPC-03, FR-UPC-04, NFR-TIME-01 |
| [M05](../modules/M05.md) | FR-EVT-01, FR-EVT-02, FR-EVT-03, FR-EVT-05, FR-CMP-05, FR-UPC-03 |
| [M06](../modules/M06.md) | FR-CAT-01, FR-CAT-02, FR-CAT-03, FR-CAT-04, FR-CAT-05, FR-CAT-06, FR-CAT-07, FR-CAT-08, FR-CAT-09, FR-SRC-02, NFR-I18N-01 |
| [M07](../modules/M07.md) | FR-CMP-06, FR-PDP-01, FR-PDP-02, FR-PDP-03, FR-PDP-04, FR-PDP-05, FR-PDP-06, FR-PDP-07, FR-PDP-08, NFR-I18N-02, NFR-A11Y-01 |
| [M08](../modules/M08.md) | FR-SRC-01, FR-SRC-02, NFR-I18N-01 |
| [M09](../modules/M09.md) | FR-BRD-01, FR-BRD-02 |
| [M10](../modules/M10.md) | FR-REC-01, FR-REC-02 |
| [M11](../modules/M11.md) | FR-EVT-04, FR-CMP-10 |
| [M12](../modules/M12.md) | FR-CMP-03, FR-CAT-02, FR-PRC-01, FR-PRC-02, FR-CRT-05, NFR-I18N-03 |
| [M13](../modules/M13.md) | FR-PDP-05, FR-STK-01, FR-STK-02, FR-STK-03, NFR-CON-01 |
| [M14](../modules/M14.md) | FR-NAV-05, FR-PDP-03, FR-PRC-02, FR-STK-01, FR-CRT-01, FR-CRT-02, FR-CRT-03, FR-CRT-04, FR-CRT-05, FR-CRT-06, FR-CRT-07, FR-CRT-08, NFR-I18N-02, NFR-A11Y-01, NFR-CON-01 |
| [M15](../modules/M15.md) | FR-ACC-01, FR-ACC-02, FR-ACC-03, FR-ACC-04, FR-ACC-05, FR-ACC-06, FR-ACC-07, FR-ACC-08 |
| [M16](../modules/M16.md) | FR-OOS-02 |
| [M17](../modules/M17.md) | FR-PDP-06, FR-PDP-07, FR-ACC-04, FR-DLV-01, FR-DLV-02 |
| [M18](../modules/M18.md) | FR-DLV-01, FR-CNT-01, FR-CNT-02, FR-CNT-03 |
| [M19](../modules/M19.md) | FR-CNT-04, NFR-PRV-01 |
| [M20](../modules/M20.md) | FR-ANL-01, NFR-SEC-05, NFR-PRV-01 |
| [M21](../modules/M21.md) | FR-CRT-07, FR-OOS-01 |
| [M22](../modules/M22.md) | FR-CNT-05 |

Modules without a requirement: none.

## Coverage checks (generated)

- 78 functional and 17 non-functional requirements; every one has at least one acceptance criterion.
- Every BR-ID cited exists in business-rules.md; every evidence stem and file cited exists under the project.
- Every use case and user story links only to existing requirement IDs.
- Functional requirements with no use case or story: FR-CNT-05, FR-ANL-01.
