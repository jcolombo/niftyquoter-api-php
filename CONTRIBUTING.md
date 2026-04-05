# Contributing to NiftyQuoter API for PHP

Thank you for considering contributing! This guide covers the process for contributing to this project.

## Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally:
   ```bash
   git clone git@github.com:YOUR_USERNAME/niftyquoter-api-php.git
   cd niftyquoter-api-php
   ```
3. Install dependencies:
   ```bash
   composer install
   ```

## Development Setup

### Requirements

- PHP 8.1 or higher
- Composer
- A NiftyQuoter account with API access (for running tests)

### Project Structure

```
src/                    # SDK source code
├── NiftyQuoter.php     # Connection manager (entry point)
├── Configuration.php   # Singleton configuration
├── Request.php         # Static CRUD request builder
├── Entity/             # Resource and collection classes
│   ├── Resource/       # Individual entity classes (Client, Proposal, etc.)
│   └── Collection/     # Specialized collection classes
├── Cache/              # Caching layer
└── Utility/            # Converters, error handling, rate limiting, DTOs

tests/                  # Custom test framework (zero dev dependencies)
├── validate            # Bash entry point
├── bootstrap.php       # CLI test runner
├── ResourceTests/      # Per-resource test files
└── Fixtures/           # Test data factory and cleanup

examples/               # Working example scripts
docs/                   # Published documentation
```

### Coding Standards

- **PHP 8.1 minimum** — use constructor promotion, enums, readonly properties, union types, named arguments
- **`declare(strict_types=1)`** in every PHP file
- **PSR-4 autoloading** under `Jcolombo\NiftyquoterApiPhp\`
- **PSR-12 coding style** — 4 spaces indentation, LF line endings, UTF-8
- No external dev dependencies — the test framework is intentionally self-contained

### Running Tests

The SDK uses a custom test framework that makes live API calls. **No PHPUnit required.**

```bash
# Dry run (no API calls — validates test structure)
composer test:dry-run

# Full test run (requires credentials)
composer test

# Test a specific resource
./tests/validate --resource=client

# Verbose output
composer test:verbose
```

#### Test Credentials

Create `niftyquoterapi.config.test.json` in the project root:

```json
{
  "testing": {
    "email": "you@example.com",
    "api_key": "your-api-key"
  }
}
```

This file is gitignored. **Never commit API credentials.**

> **Warning:** Tests create and delete real data. Use a test or sandbox account.

## Making Changes

1. Create a feature branch from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```
2. Make your changes, following the coding standards above
3. Run the dry-run tests to check for structural issues:
   ```bash
   composer test:dry-run
   ```
4. If possible, run the full test suite against a test account
5. Commit with a clear message:
   ```bash
   git commit -m "feat: add support for new resource type"
   ```

### Commit Message Convention

Use [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` — new feature
- `fix:` — bug fix
- `docs:` — documentation only
- `chore:` — maintenance, dependencies, CI
- `refactor:` — code change that neither fixes a bug nor adds a feature
- `test:` — adding or updating tests

## Submitting a Pull Request

1. Push your branch to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```
2. Open a Pull Request against `main` on the upstream repository
3. Describe what your PR does and why
4. Reference any related issues

## Reporting Issues

- Use [GitHub Issues](https://github.com/jcolombo/niftyquoter-api-php/issues)
- Include PHP version, SDK version, and a minimal reproduction case
- For API behavior questions, note that this is an independent SDK — NiftyQuoter's API behavior is outside our control

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
