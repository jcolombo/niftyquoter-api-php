# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [0.5.0-alpha] - 2026-04-05

### Added
- Full SDK implementation: connection manager, configuration, request builder, error handling, rate limiting, caching, logging
- 10 resource entity classes: Client, Proposal, Comment, Note, Contact, Item, PricingTable, ServiceTemplate, EmailTemplate, TextBlock
- 5 specialized collection classes with parent context validation (CommentCollection, ContactCollection, ItemCollection, NoteCollection, PricingTableCollection)
- Fluent collection builder: `where()`, `has()`, `include()`, `limit()`, `fields()`, `options()`
- Auto-pagination for collection fetches
- Dirty tracking — only changed properties sent on update
- Type coercion via Converter utility (text, integer, decimal, boolean, datetime, numeric_string, enum, intEnum, html)
- Dual sliding-window rate limiter (30/min + 1000/hr) with automatic 429 retry and exponential backoff
- File-based response caching with custom backend support (`Cache::registerCacheMethods()`)
- Mutation-triggered cache invalidation (ScrubCache)
- Configurable error handler with per-severity dispatch (log, echo, PHP errors)
- Configuration singleton with 3-level cascade: package defaults → user config file → runtime overrides
- Proposal-specific methods: `sendEmail()` and `clone()` with full parameter support
- Custom zero-dependency test framework with all 10 resource test files, test data factory, and automatic cleanup
- Comprehensive README with full usage documentation
- 5 working example scripts (connection, CRUD, collections, nested resources, configuration)
- CONTRIBUTING.md with setup guide, coding standards, and PR process
- GitHub Actions CI workflow (PHP 8.1-8.4: validate, syntax check, dry-run tests)
- `bin/release` script for standardized version tagging and changelog management
- `.editorconfig` for consistent editor formatting
- `composer.json` scripts: `test`, `test:dry-run`, `test:verbose`
- 12 documented design decisions in OVERRIDES.md

### Fixed
- docs/TECH-STACK.md: replaced stale "TBD" entries with implemented base URL and auth details
