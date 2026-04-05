# OVERRIDES — Intentional Deviations from paymo-api-php

This document tracks every intentional deviation from `jcolombo/paymo-api-php` v0.6.1 patterns. Each override has a unique `OVERRIDE-NNN` identifier referenced in source code via `@override OVERRIDE-NNN` annotations.

---

## OVERRIDE-001: WHERE Compilation to Individual Query Parameters

**Files**: `src/Request.php` (`compileWhereParameters()`)

**Paymo**: Joins WHERE conditions into a single `?where=field=value and field=value` query string.

**NiftyQuoter**: Maps each condition to an individual query parameter: `?search_email=foo&only_companies=true`.

**Justification**: NiftyQuoter uses named per-resource query parameters, not a generic WHERE string.

---

## OVERRIDE-002: Single Auth Mode

**Files**: `src/NiftyQuoter.php` (`connect()`)

**Paymo**: Polymorphic `connect(string|array)` supporting API key string or `['email' => ..., 'password' => ...]` array for dual auth modes.

**NiftyQuoter**: `connect(string $email, string $apiKey)` with two explicit parameters.

**Justification**: NiftyQuoter always requires email + API key. Two typed parameters are clearer and leverage PHP 8.1 named arguments.

---

## OVERRIDE-003: 1-Indexed Pagination

**Files**: `src/Entity/AbstractCollection.php` (`$paginationPage`, `fetch()`)

**Paymo**: 0-indexed pagination (`?page=0` is first page).

**NiftyQuoter**: 1-indexed pagination (`?page=1` is first page).

**Justification**: NiftyQuoter documents `?page=1` as the first page.

---

## OVERRIDE-004: Native JSON Configuration (hassankhan/config Removed)

**Files**: `src/Configuration.php`

**Paymo**: Uses `hassankhan/config` (Noodlehaus) for multi-format config file parsing (JSON, YAML, XML, INI).

**NiftyQuoter**: Uses native `json_decode()` + `JSON_THROW_ON_ERROR` + `array_replace_recursive()` for deep merge. Dot-notation access via `adbario/php-dot-notation` (`Adbar\Dot`).

**Justification**: The SDK only uses JSON config. Noodlehaus's YAML/XML/INI support is unused. One fewer dependency.

---

## OVERRIDE-005: devMode as Configuration Value

**Files**: `src/Configuration.php`, all files that check dev mode

**Paymo**: Uses `PAYMO_DEVELOPMENT_MODE` global PHP constant.

**NiftyQuoter**: Uses `Configuration::get('devMode')`.

**Justification**: Aligns with configuration cascade pattern. Enables runtime toggling without redefining a PHP constant.

---

## OVERRIDE-006: WRITEONLY Constant Added

**Files**: `src/Entity/AbstractResource.php`, `src/Entity/Resource/Client.php`, `src/Entity/Resource/Proposal.php`, `src/Entity/Resource/Item.php`

**Paymo**: No WRITEONLY concept. 9 required constants on AbstractResource.

**NiftyQuoter**: Added `public const WRITEONLY = []` as a 10th required constant. Fields listed are sent in create/update but never expected in responses.

**Justification**: 6 write-only fields exist across 3 resources. Without this constant, dirty tracking cannot correctly handle fields that are sent but never returned.

---

## OVERRIDE-007: numeric_string PROP_TYPE Added

**Files**: `src/Utility/Converter.php`, `src/Entity/Resource/Item.php`

**Paymo**: No `numeric_string` type in PROP_TYPES vocabulary.

**NiftyQuoter**: Added `'numeric_string'` type. Converter preserves string type — never casts to float or int.

**Justification**: All Item monetary fields are JSON strings (`"400"` not `400`). Casting to float risks precision loss.

---

## OVERRIDE-008: Parent Context Setter for Nested Resource URLs

**Files**: `src/Entity/AbstractEntity.php`, `src/Entity/AbstractCollection.php`, `src/Request.php`, all nested resource classes

**Paymo**: Uses top-level endpoints with WHERE filtering (`/tasks?where=project_id=123`).

**NiftyQuoter**: Uses nested URLs (`/proposals/{id}/comments`). Resources store parent context via `setParentContext()`, with convenience methods `forProposal()` and `forServiceTemplate()`.

**Justification**: NiftyQuoter's API uses nested resource URLs instead of filter parameters for parent-child relationships.

---

## OVERRIDE-009: Dual Sliding-Window Rate Limiter with Blind Tracking

**Files**: `src/Utility/RateLimiter.php`

**Paymo**: Single-window rate limiter using response headers to track remaining quota.

**NiftyQuoter**: Dual sliding window (30/min + 1000/hr) using local timestamp-based tracking. No response headers parsed (undocumented).

**Justification**: NiftyQuoter does not document rate-limit response headers. Blind timestamp tracking is the only option.

---

## OVERRIDE-010: intEnum Prefix Bug Fix

**Files**: `src/Utility/Converter.php` (`getPrimitiveType()`, `convertToPhpValue()`)

**Paymo Bug**: `getPrimitiveType()` checks for `'enumInt:'` prefix, but resource classes define `'intEnum:'`. Values silently fall through to string.

**Fix**: Check for `'intEnum:'` prefix in Converter.

---

## OVERRIDE-011: EntityMap::overload() Typo Fix

**Files**: `src/Entity/EntityMap.php` (`overload()`)

**Paymo Bug**: Dev-mode validation references `AbstractResourcce` (double c).

**Fix**: Correct to `AbstractResource`.

---

## OVERRIDE-012: Configuration::overload() dirname Bug Fix

**Files**: `src/Configuration.php` (`overload()`)

**Paymo Bug**: Passing a directory path to `overload()` calls `dirname()` which navigates one level up, looking for the config file in the wrong directory.

**Fix**: Add `is_dir($path)` check before calling `dirname()`. If path is a directory, look for `niftyquoterapi.config.json` inside it directly.
