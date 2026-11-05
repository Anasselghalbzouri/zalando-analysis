# ADR-008: Search with MySQL FULLTEXT (no external engine in v1)

- **Status:** Proposed
- **Date:** 2026-11-04 (Phase 05)
- **Related requirements:** FR-SRC-01, FR-SRC-02, FR-CAT-01

## Context
- **Observed search:** brand suggestions while typing (FR-SRC-01) and cross-campaign results (FR-SRC-02).
- **Unknown:** free-text behaviour, typo tolerance and suggestion types beyond brands (Q-44).
- **Out of reach:** no search-engine requirement exists. Load is UNKNOWN.

## Decision
- **Suggestions:** a brand-name **prefix** search on an indexed column, using the accent-insensitive collation.
- **Results:** **FULLTEXT** over a maintained `products.search_text` (brand + name + colour), restricted to live campaign articles.
- **Abstraction:** the `SearchSuggestions` / `ArticleListingQuery` interfaces hide the engine, so it can be swapped later.

## Alternatives considered
- **Laravel Scout + Meilisearch / OpenSearch:** typo tolerance and relevance tuning, but another service, index sync, and requirements that are still UNKNOWN.
- **`LIKE '%q%'` on names:** no index; slow.

## Consequences
- **Positive:** no extra infrastructure; consistent with MySQL transactions (live state is always current).
- **Negative:** limited relevance and no typo tolerance. The French stop-word list and `innodb_ft_min_token_size` need tuning. If Q-44 asks for typo tolerance, move to a search engine behind the same interface.
