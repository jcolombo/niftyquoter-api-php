# OVERRIDES — Design Decisions & Intentional Patterns

This document tracks notable design decisions in the SDK. Each has a unique `OVERRIDE-NNN` identifier referenced in source code via `@override OVERRIDE-NNN` annotations.

---

## OVERRIDE-001: WHERE Compilation to Individual Query Parameters

**Files**: `src/Request.php` (`compileWhereParameters()`)

Each WHERE condition maps to an individual query parameter: `?search_email=foo&only_companies=true`.

**Justification**: NiftyQuoter uses named per-resource query parameters, not a generic WHERE string.

---

## OVERRIDE-002: Single Auth Mode

**Files**: `src/NiftyQuoter.php` (`connect()`)

`connect(string $email, string $apiKey)` with two explicit parameters.

**Justification**: NiftyQuoter always requires email + API key. Two typed parameters are clearer and leverage PHP 8.1 named arguments.

---

## OVERRIDE-003: 1-Indexed Pagination

**Files**: `src/Entity/AbstractCollection.php` (`$paginationPage`, `fetch()`, `fetchAll()`)

Pagination is 1-indexed (`?page=1` is first page). `fetch()` returns a single page; `fetchAll()` auto-paginates through all pages.

**Justification**: NiftyQuoter documents `?page=1` as the first page. Single-page default prevents runaway API calls on large collections.

---

## OVERRIDE-004: Native JSON Configuration

**Files**: `src/Configuration.php`

Uses native `json_decode()` + `JSON_THROW_ON_ERROR` + `array_replace_recursive()` for deep merge. Dot-notation access via `adbario/php-dot-notation` (`Adbar\Dot`).

**Justification**: The SDK only uses JSON config. Multi-format support is unnecessary — one fewer dependency.

---

## OVERRIDE-005: devMode as Configuration Value

**Files**: `src/Configuration.php`, all files that check dev mode

Uses `Configuration::get('devMode')` instead of a global PHP constant.

**Justification**: Aligns with configuration cascade pattern. Enables runtime toggling without redefining a PHP constant.

---

## OVERRIDE-006: WRITEONLY Constant Added

**Files**: `src/Entity/AbstractResource.php`, `src/Entity/Resource/Client.php`, `src/Entity/Resource/Proposal.php`, `src/Entity/Resource/Item.php`

Added `public const WRITEONLY = []` as a resource constant. Fields listed are sent in create/update but never expected in responses.

**Justification**: 6 write-only fields exist across 3 resources. Without this constant, dirty tracking cannot correctly handle fields that are sent but never returned.

---

## OVERRIDE-007: numeric_string PROP_TYPE Added

**Files**: `src/Utility/Converter.php`, `src/Entity/Resource/Item.php`

Added `'numeric_string'` type. Converter preserves string type — never casts to float or int.

**Justification**: All Item monetary fields are JSON strings (`"400"` not `400`). Casting to float risks precision loss.

---

## OVERRIDE-008: Parent Context Setter for Nested Resource URLs

**Files**: `src/Entity/AbstractEntity.php`, `src/Entity/AbstractCollection.php`, `src/Request.php`, all nested resource classes

Uses nested URLs (`/proposals/{id}/comments`). Resources store parent context via `setParentContext()`, with convenience methods `forProposal()` and `forServiceTemplate()`.

**Justification**: NiftyQuoter's API uses nested resource URLs instead of filter parameters for parent-child relationships.

---

## OVERRIDE-009: Dual Sliding-Window Rate Limiter with Blind Tracking

**Files**: `src/Utility/RateLimiter.php`

Dual sliding window (30/min + 1000/hr) using local timestamp-based tracking. No response headers parsed (undocumented).

**Justification**: NiftyQuoter does not document rate-limit response headers. Blind timestamp tracking is the only option.

---

## OVERRIDE-010: intEnum Prefix Handling

**Files**: `src/Utility/Converter.php` (`getPrimitiveType()`, `convertToPhpValue()`)

`getPrimitiveType()` checks for `'intEnum:'` prefix to correctly handle integer-based enum values.

---

## OVERRIDE-011: EntityMap::overload() Typo Fix

**Files**: `src/Entity/EntityMap.php` (`overload()`)

Dev-mode validation correctly references `AbstractResource` (not a misspelling).

---

## OVERRIDE-012: Configuration::overload() dirname Fix

**Files**: `src/Configuration.php` (`overload()`)

Add `is_dir($path)` check before calling `dirname()`. If path is a directory, look for `niftyquoterapi.config.json` inside it directly.
