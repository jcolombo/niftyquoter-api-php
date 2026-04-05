# NiftyQuoter PHP SDK — Examples

Working example scripts demonstrating SDK usage patterns. Each file is self-contained and can be run directly.

## Prerequisites

1. Install the SDK: `composer require jcolombo/niftyquoter-api-php`
2. Have your NiftyQuoter account email and API key ready

## Running Examples

```bash
php examples/01-basic-connection.php
```

Each example prompts for credentials or reads them from environment variables `NIFTYQUOTER_EMAIL` and `NIFTYQUOTER_API_KEY`.

## Example Index

| File | Description |
|------|-------------|
| `01-basic-connection.php` | Establish a connection, fetch a single client, disconnect |
| `02-crud-operations.php` | Create, read, update, and delete a client |
| `03-collections-and-filtering.php` | List resources with WHERE filters, HAS post-filters, pagination |
| `04-nested-resources.php` | Work with proposal-scoped resources (items, comments, notes) |
| `05-configuration.php` | Load custom config, enable caching and logging |

## Important

These examples create and modify real data in your NiftyQuoter account. Use a test or sandbox account when possible.
