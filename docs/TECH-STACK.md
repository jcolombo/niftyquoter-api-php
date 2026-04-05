# Tech Stack

## PHP Version

**Minimum: PHP 8.1**

Chosen to leverage constructor promotion, enums, readonly properties, union types, and named arguments — all of which produce cleaner SDK code. PHP 8.0 is already EOL. Anyone running 7.x is on unsupported PHP.

## Dependencies

| Package | Purpose | Notes |
|---------|---------|-------|
| `guzzlehttp/guzzle` ^7.8 | HTTP client | Industry standard |
| `adbario/php-dot-notation` ^3.3 | Dot-notation array access | Clean nested config/data access |

Minimal dependency footprint — native `json_decode()` for config loading instead of a multi-format library (see Configuration below).

## Configuration Approach

This SDK uses native PHP for configuration loading:

- **Native `json_decode()`** for loading JSON config files (PHP 8.1's `JSON_THROW_ON_ERROR` flag gives clean error handling)
- **`adbario/php-dot-notation`** for dot-notation access into the parsed config array

Single-format loading (JSON only) keeps the dependency count low.

## Architecture Reference

- **PSR-4 autoloading** under `Jcolombo\NiftyquoterApiPhp\`
- **Resource entity classes** representing API endpoints
- **Collection classes** for list responses
- **Configuration** via JSON config file (native `json_decode` + dot-notation)
- **Guzzle-based HTTP** with auth handling

## API Reference

- **NiftyQuoter API Docs**: https://niftyquoter.docs.apiary.io
- **Base URL**: `https://api.niftyquoter.com/api/v1/` (configured in `default.niftyquoterapi.config.json`)
- **Auth**: HTTP Basic Authentication — `base64_encode("{email}:{apiKey}")` sent as `Authorization: Basic {encoded}` header on every request
