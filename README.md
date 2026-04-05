# NiftyQuoter API for PHP

A PHP SDK for the [NiftyQuoter](https://niftyquoter.com) sales proposal API.

[![Latest Version](https://img.shields.io/packagist/v/jcolombo/niftyquoter-api-php.svg)](https://packagist.org/packages/jcolombo/niftyquoter-api-php)
[![PHP Version](https://img.shields.io/packagist/php-v/jcolombo/niftyquoter-api-php.svg)](https://packagist.org/packages/jcolombo/niftyquoter-api-php)
[![License](https://img.shields.io/github/license/jcolombo/niftyquoter-api-php)](LICENSE)
[![GitHub Issues](https://img.shields.io/github/issues/jcolombo/niftyquoter-api-php)](https://github.com/jcolombo/niftyquoter-api-php/issues)

---

## Overview

This independently developed package provides a developer-friendly PHP toolkit for interacting with the NiftyQuoter REST API. It is not affiliated with or endorsed by NiftyQuoter.

**API Documentation:** https://niftyquoter.docs.apiary.io

> **Stability Notice:** This package is in active development (v0.5.x-alpha). The API surface may change before v1.0. Pin to `^0.5` in production.

---

## Features

- **Full CRUD Operations** — Create, Read, Update, and Delete for all 10 NiftyQuoter resource types
- **Fluent Interface** — Chainable methods for clean, readable code
- **Smart Query Building** — Server-side WHERE filters and client-side HAS post-filters
- **Nested Resources** — Parent context for proposal-scoped entities (items, comments, notes, contacts, pricing tables)
- **Auto-Pagination** — Automatically fetches all pages in a collection
- **Response Caching** — Built-in file-based caching with custom backend support
- **Rate Limiting** — Dual sliding-window limiter (30/min + 1000/hr) with automatic 429 retry
- **Request Logging** — Conditional file-based logging for debugging
- **Type Coercion** — Automatic property type conversion based on resource field definitions
- **Dirty Tracking** — Only changed fields are sent on update
- **Zero Dev Dependencies** — Custom test framework requires no PHPUnit or dev packages

---

## Requirements

- PHP 8.1 or higher
- A [NiftyQuoter](https://niftyquoter.com) account with API access
- Your NiftyQuoter account email and API key
- Composer

---

## Installation

```bash
composer require jcolombo/niftyquoter-api-php
```

---

## Quick Start

### Establishing a Connection

```php
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;

// Connect with your email and API key (HTTP Basic Auth)
$nq = NiftyQuoter::connect('you@example.com', 'your-api-key');
```

The SDK uses a singleton pattern — calling `connect()` with the same credentials returns the existing connection instance.

### Fetching a Single Resource

```php
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Client;

// Fetch a client by ID
$client = Client::new($nq)->fetch(123);

// Access properties directly
echo $client->first_name;
echo $client->email;
```

### Fetching Collections (Lists)

```php
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;

// Get one page of proposals (default: page 1, up to 100 results)
$proposals = Proposal::list($nq)->fetch();

foreach ($proposals as $proposal) {
    echo $proposal->name . "\n";
}

// Get count of returned results
echo "Proposals on this page: " . count($proposals);

// Explicitly fetch ALL proposals (auto-paginates through every page)
$allProposals = Proposal::list($nq)->fetchAll();

// JSON encode directly (collections implement JsonSerializable)
$json = json_encode($proposals);
```

### Creating Resources

```php
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Client;

// Create a new client
$client = Client::new($nq);
$client->is_company = false;
$client->first_name = 'Jane';
$client->last_name = 'Doe';
$client->email = 'jane@example.com';
$client->create();

// The client now has an ID from the API
echo "Created client #{$client->id}";
```

### Updating Resources

```php
// Fetch, modify, and update
$client = Client::new($nq)->fetch(123);
$client->email = 'new-email@example.com';
$client->phone = '555-0100';
$client->update();

// Only dirty (changed) fields are sent to the API
```

### Deleting Resources

```php
$client = Client::new($nq)->fetch(123);
$deleted = $client->delete(); // Returns true on success
```

### Disconnecting

```php
// Disconnect a specific connection
NiftyQuoter::disconnect('you@example.com', 'your-api-key');

// Disconnect all connections
NiftyQuoter::disconnect();
```

---

## Supported Resources

The SDK covers all 10 NiftyQuoter API resource types:

| Resource | Class | Scope | Notes |
|----------|-------|-------|-------|
| **Client** | `Entity\Resource\Client` | Top-level | Companies and individual contacts |
| **Proposal** | `Entity\Resource\Proposal` | Top-level | Sales proposals (hub entity) |
| **Comment** | `Entity\Resource\Comment` | Nested (Proposal) | Internal comments on proposals |
| **Note** | `Entity\Resource\Note` | Nested (Proposal) | Notes attached to proposals |
| **Contact** | `Entity\Resource\Contact` | Nested (Proposal) | Client-proposal junction records |
| **Item** | `Entity\Resource\Item` | Nested (Proposal or ServiceTemplate) | Line items with pricing |
| **PricingTable** | `Entity\Resource\PricingTable` | Nested (Proposal) | Pricing table groupings |
| **ServiceTemplate** | `Entity\Resource\ServiceTemplate` | Top-level | Reusable service templates |
| **EmailTemplate** | `Entity\Resource\EmailTemplate` | Top-level | Email templates for proposals |
| **TextBlock** | `Entity\Resource\TextBlock` | Top-level | Reusable text content blocks |

---

## Query Building

### WHERE Filters (Server-Side)

Filter collections using the fluent `where()` method. Available filter keys are defined per resource in their `WHERE_OPERATIONS` constant.

```php
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Client;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;

// Search clients by email
$clients = Client::list($nq)
    ->where('search_email', 'jane@example.com')
    ->fetch();

// Filter proposals by state
$sent = Proposal::list($nq)
    ->where('state', 'sent')
    ->fetch();

// Combine multiple filters
$proposals = Proposal::list($nq)
    ->where('state', 'sent')
    ->where('user_id', 42)
    ->fetch();
```

**Available WHERE filters by resource:**

| Resource | Filters |
|----------|---------|
| Client | `only_companies`, `search_email`, `search_company`, `search_first_name`, `search_last_name`, `search_phone`, `search_name` |
| Proposal | `state`, `user_id`, `currency_id`, `template_id`, `code`, `archived`, `from_date`, `to_date` |

### HAS Filters (Client-Side Post-Filters)

For properties not supported by the API's server-side filtering, use `has()` to filter results after they are fetched:

```php
// Only proposals with a total value above 10000
$highValue = Proposal::list($nq)
    ->has('total_value', 10000, '>')
    ->fetch();

// Clients with a specific business name (case-insensitive contains)
$clients = Client::list($nq)
    ->has('business_name', 'acme', 'like')
    ->fetch();
```

Supported `has()` operators: `=`, `!=`, `>`, `>=`, `<`, `<=`, `like`

---

## Nested Resources

Some resources exist only within a parent context (e.g., Items belong to a Proposal). Use `forProposal()` (or `forServiceTemplate()` for Items) to set the parent context before CRUD operations.

### Single Resource with Parent

```php
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Item;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Comment;

// Create an item under a proposal
$item = Item::new($nq)->forProposal(456);
$item->name = 'Web Design Package';
$item->price = '2500.00';
$item->quantity = '1';
$item->create();

// Add a comment to a proposal
$comment = Comment::new($nq)->forProposal(456);
$comment->body = 'Client approved the design phase.';
$comment->user_id = 42;
$comment->create();
```

### Collections with Parent

```php
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Item;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Comment;

// List all items for a proposal
$items = Item::list($nq)->forProposal(456)->fetch();

foreach ($items as $item) {
    echo "{$item->name}: \${$item->price}\n";
}

// List all comments for a proposal
$comments = Comment::list($nq)->forProposal(456)->fetch();
```

### Items Under Service Templates

Items have a polymorphic parent — they can belong to either a Proposal or a ServiceTemplate:

```php
// Items under a service template
$items = Item::list($nq)->forServiceTemplate(789)->fetch();

$item = Item::new($nq)->forServiceTemplate(789);
$item->name = 'Consulting Hour';
$item->price = '150.00';
$item->quantity = '1';
$item->create();
```

### Nested Resources Reference

| Resource | Required Parent | Methods |
|----------|----------------|---------|
| Comment | Proposal | `forProposal($id)` |
| Note | Proposal | `forProposal($id)` |
| Contact | Proposal | `forProposal($id)` |
| PricingTable | Proposal | `forProposal($id)` |
| Item | Proposal OR ServiceTemplate | `forProposal($id)` or `forServiceTemplate($id)` |

---

## Pagination

By default, `fetch()` returns a **single page** of results. Use `fetchAll()` when you explicitly need every record.

```php
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Client;

// Fetch one page (default: page 1, 100 results) — single API call
$clients = Client::list($nq)->fetch();

// Control page and page size
$page2 = Client::list($nq)->limit(2, 25)->fetch();

// Fetch ALL clients (auto-paginates through every page)
// Use with caution on large collections — may generate many API calls
$allClients = Client::list($nq)->fetchAll();

// Control page size for fetchAll batches
$allClients = Client::list($nq)->limit(null, 50)->fetchAll();
```

**Key points:**
- Pages are **1-indexed** (first page is 1)
- Default page size is 100 (20 for Proposals)
- `limit($page, $pageSize)` — both parameters are optional
- `fetch()` returns one page; `fetchAll()` auto-paginates until `count(results) < pageSize`

---

## Configuration

### Default Configuration

The SDK ships with a `default.niftyquoterapi.config.json` that is loaded automatically. You do not need to create a config file to get started.

### Custom Configuration File

Create a `niftyquoterapi.config.json` file in your project root. The SDK merges your overrides on top of the defaults using `array_replace_recursive()` — you only need to include the keys you want to change.

```json
{
  "connection": {
    "timeout": 15,
    "verify": false
  },
  "path": {
    "cache": "/tmp/niftyquoter-cache",
    "logs": "/var/log/niftyquoter"
  },
  "enabled": {
    "cache": true,
    "logging": true
  },
  "devMode": true
}
```

### Loading Configuration

```php
use Jcolombo\NiftyquoterApiPhp\Configuration;

// Auto-detect: pass a directory — SDK looks for niftyquoterapi.config.json in it
Configuration::overload(__DIR__);

// Or load a specific file path
Configuration::load('/path/to/my-config.json');

// Read/write config values at runtime
$timeout = Configuration::get('connection.timeout');
Configuration::set('devMode', true);
```

### Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `connection.url` | string | `https://api.niftyquoter.com/api/v1/` | API base URL |
| `connection.timeout` | int | `30` | Request timeout in seconds |
| `connection.verify` | bool | `true` | SSL certificate verification |
| `enabled.cache` | bool | `false` | Enable response caching |
| `enabled.logging` | bool | `false` | Enable request/response logging |
| `path.cache` | string\|null | `null` | Directory for cache files |
| `path.logs` | string\|null | `null` | Directory for log files |
| `log.connections` | bool | `false` | Log connection create/destroy events |
| `log.requests` | bool | `true` | Log API request/response details |
| `rateLimit.enabled` | bool | `true` | Enable built-in rate limiter |
| `rateLimit.minDelayMs` | int | `200` | Minimum delay between requests (ms) |
| `rateLimit.perMinute` | int | `30` | Rate limit: requests per minute |
| `rateLimit.perHour` | int | `1000` | Rate limit: requests per hour |
| `devMode` | bool | `false` | Enable development-mode validations and warnings |

---

## Caching

The SDK includes opt-in file-based caching to reduce API calls and avoid rate limits.

### Enable Caching

**Option A** — via config file:

```json
{
  "enabled": { "cache": true },
  "path": { "cache": "/tmp/niftyquoter-cache" }
}
```

**Option B** — via PHP constant (must be defined before any SDK calls):

```php
define('NQAPI_REQUEST_CACHE_PATH', '/tmp/niftyquoter-cache');
```

Both the constant AND `enabled.cache = true` are required for caching to activate.

### Cache Behavior

- Only **GET** requests are cached
- Any **POST/PUT/DELETE** request clears the entire cache (safe default)
- Cache files are stored in `{cache_path}/nqapi-cache/` as serialized PHP
- Expiry is time-based using file modification timestamps

### Custom Cache Backend

Replace the file-based cache with your own backend (Redis, Memcached, etc.):

```php
use Jcolombo\NiftyquoterApiPhp\Cache\Cache;

Cache::registerCacheMethods(
    function (string $key) {
        // Read: return RequestResponse or null
        return Redis::get("nqapi:{$key}");
    },
    function (string $key, $data) {
        // Write: store the RequestResponse
        Redis::setex("nqapi:{$key}", 300, $data);
    },
    function () {
        // Clear: flush all SDK cache entries
        Redis::del(Redis::keys("nqapi:*"));
    }
);
```

---

## Rate Limiting

The SDK includes a built-in dual sliding-window rate limiter that operates transparently:

- **30 requests/minute** and **1000 requests/hour** (configurable)
- `waitIfNeeded()` pauses execution before each request if limits are near
- **429 responses** trigger automatic retry with exponential backoff (up to 3 retries)
- Rate limits are tracked per connection (by credential pair)

No action is needed to enable rate limiting — it is on by default. Disable it via config:

```json
{
  "rateLimit": { "enabled": false }
}
```

---

## Error Handling

The SDK uses a configurable error handler rather than throwing exceptions for API errors:

```php
use Jcolombo\NiftyquoterApiPhp\Utility\Error;
use Jcolombo\NiftyquoterApiPhp\Utility\ErrorSeverity;
```

### Error Severity Levels

| Level | Default Handlers | Description |
|-------|-----------------|-------------|
| `NOTICE` | `log` | Informational — non-critical issues |
| `WARN` | `log` | Warnings — missing fields, unexpected responses |
| `FATAL` | `log`, `echo` | Critical failures — connection errors, authentication failures |

### Customizing Error Handlers

Configure per-severity handlers in your config file:

```json
{
  "error": {
    "handlers": {
      "notice": ["log"],
      "warn": ["log", "echo"],
      "fatal": ["log", "echo"]
    },
    "triggerPhpErrors": true
  }
}
```

When `triggerPhpErrors` is `true`, errors also trigger PHP's native error system (`trigger_error()`), allowing integration with existing error handlers.

### Checking API Response Success

```php
// CRUD methods return the entity — check if it was populated
$client = Client::new($nq)->fetch(99999);
if ($client->id === null) {
    echo "Client not found or request failed";
}

// Delete returns a boolean
$deleted = $client->delete();
if (!$deleted) {
    echo "Delete failed";
}
```

---

## Working with Properties

### Getting and Setting

```php
$client = Client::new($nq)->fetch(123);

// Magic property access
echo $client->first_name;
$client->email = 'new@example.com';

// Explicit method access
$name = $client->get('first_name');
$client->set('email', 'new@example.com');
```

### Dirty Tracking

The SDK tracks which properties have changed since the last fetch/create:

```php
$client = Client::new($nq)->fetch(123);
$client->email = 'changed@example.com';

// Check if anything changed
if ($client->isDirty()) {
    $client->update(); // Only sends 'email' to the API
}

// Check a specific property
$client->isDirty('email');    // true
$client->isDirty('phone');    // false

// Get list of changed property names
$dirtyKeys = $client->getDirty(); // ['email']
```

### Serialization

```php
// To associative array
$data = $client->toArray();

// To JSON string
$json = $client->toJson();

// Collections are JSON-serializable
$clients = Client::list($nq)->fetch();
echo json_encode($clients); // Array of client objects from one page

// Extract a single field from all items
$names = $clients->flatten('first_name'); // ['Jane', 'John', ...]

// Get raw keyed-by-ID array
$indexed = $clients->raw(); // [123 => Client, 456 => Client, ...]
```

---

## Advanced Usage

### Multiple Connections

```php
// Connect to different NiftyQuoter accounts
$account1 = NiftyQuoter::connect('user1@company.com', 'key1');
$account2 = NiftyQuoter::connect('user2@company.com', 'key2');

// Pass connection to entities
$clientsFromAccount1 = Client::list($account1)->fetch();
$clientsFromAccount2 = Client::list($account2)->fetch();
```

### Custom API Requests

For endpoints not covered by resource classes (e.g., proposal actions):

```php
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;

// Send email for a proposal
$proposal = Proposal::new($nq)->fetch(456);
$result = $proposal->sendEmail(
    subject: 'Your Proposal is Ready',
    body: '<h1>Please review</h1><p>Click the link below.</p>',
    attachPdf: true,
    bcc: 'records@company.com'
);

// Clone a proposal
$clone = $proposal->clone(
    cloneClient: true,
    cloneComments: false,
    archiveSource: true
);
echo "Cloned as proposal #{$clone->id}";
```

### Property Types

Each resource defines typed properties that are automatically coerced:

| Type | PHP Type | Example Fields |
|------|----------|----------------|
| `text` | string | `name`, `email`, `body` |
| `integer` | int | `id`, `user_id` |
| `decimal` | float | `total_value`, `discount` |
| `boolean` | bool | `is_company`, `archived` |
| `datetime` | string | `created_at`, `updated_at` |
| `numeric_string` | string | `price`, `quantity` (Item fields) |
| `html` | string | `body` (EmailTemplate) |
| `enum:...` | string | `state` (Proposal) |

### Read-Only and Write-Only Properties

```php
// READONLY: Set by the API, cannot be changed (id, created_at, computed fields)
// CREATEONLY: Can only be set during create(), ignored on update()
// WRITEONLY: Sent to API but never returned in responses (action triggers)

$client = Client::new($nq);
$client->is_company = true;
$client->business_name = 'Acme Corp';
$client->company_name = 'Acme Corp';  // WRITEONLY — triggers company creation
$client->create();
// company_name will not appear when you fetch this client later
```

---

## Running Tests

The SDK includes a custom test framework that runs live API tests against your NiftyQuoter account. **No dev dependencies required.**

### Setup

Create a `niftyquoterapi.config.test.json` in the project root:

```json
{
  "testing": {
    "email": "you@example.com",
    "api_key": "your-test-api-key"
  }
}
```

> **Warning:** Tests create and delete real data in your NiftyQuoter account. Use a test/sandbox account.

### Running

```bash
# Run all tests
composer test

# Dry run (no API calls)
composer test:dry-run

# Verbose output
composer test:verbose

# Run tests for a specific resource
./tests/validate --resource=client

# Read-only mode (only GET operations)
./tests/validate --read-only

# Stop on first failure
./tests/validate --stop-on-failure

# Skip cleanup (leave test entities for inspection)
./tests/validate --no-cleanup
```

### CLI Options

| Flag | Description |
|------|-------------|
| `--help` | Show help message |
| `--dry-run` | Simulate without API calls |
| `--read-only` | Only run GET operations |
| `--verbose` | Enable verbose output |
| `--resource=<name>` | Test a specific resource only |
| `--stop-on-failure` | Stop after first failure |
| `--no-cleanup` | Skip cleanup of test entities |
| `--email=<email>` | Override test email |
| `--api-key=<key>` | Override test API key |

---

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## License

MIT — see [LICENSE](LICENSE) for details.

---

## Credits

Developed and maintained by [Joel Colombo](mailto:jc-dev@360psg.com) at [360 PSG, Inc.](https://360psg.com)

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed history of changes.
