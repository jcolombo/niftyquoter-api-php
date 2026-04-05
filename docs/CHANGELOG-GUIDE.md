# Changelog Maintenance Guide

This project follows [Keep a Changelog](https://keepachangelog.com/) and [Semantic Versioning](https://semver.org/).

## When to Update the Changelog

Update `CHANGELOG.md` at the end of **every session** that changes functional code. This includes:

- New methods, classes, or features
- Changed behavior of existing methods
- Bug fixes
- Removed or deprecated functionality
- Breaking changes to the public API

**Do NOT log**: formatting-only changes, comment edits, documentation-only updates, or internal refactors that do not affect the public API surface.

## The `[Unreleased]` Section

All changes go into `[Unreleased]` until a version is tagged via `bin/release`. This section is the **single source of truth** for what has changed since the last release.

### Categories (use only what applies)

```markdown
## [Unreleased]

### Added       — new features, methods, classes, constants
### Changed     — behavior changes to existing functionality
### Fixed       — bug fixes
### Removed     — removed features or deprecated code
### Breaking    — changes that break backward compatibility
```

### Writing Good Entries

Each entry should answer: **what changed, and why would a developer care?**

**Good:**
```markdown
- `fetch()` now returns a single page of results; use `fetchAll()` for auto-pagination
- Fixed `where()` passing operator arrays as data types — now looks up types from `FIELDS` constant
```

**Bad:**
```markdown
- Updated AbstractCollection.php
- Changed some code in the where method
```

Rules:
- Reference method/class names in backticks
- Lead with the thing that changed, then the impact
- One bullet per logical change (not per file)
- If a fix corrects a bug introduced in the same `[Unreleased]` cycle, **update the original entry** instead of adding a separate fix entry

### Consolidating Unreleased Entries

When a new change impacts an entry already in `[Unreleased]`, revise the existing entry rather than adding a contradictory or redundant one.

**Example:** If you add a method in one session, then rename it in the next session before any release, the changelog should only show the final name — not the add-then-rename history.

Before:
```markdown
### Added
- Added `getItems()` method to Collection

### Changed
- Renamed `getItems()` to `items()` on Collection
```

After (consolidated):
```markdown
### Added
- Added `items()` method to Collection
```

## Commit Messages

Use [Conventional Commits](https://www.conventionalcommits.org/) prefixes:

| Prefix | When |
|--------|------|
| `feat:` | New feature |
| `fix:` | Bug fix |
| `refactor:` | Code restructure, no behavior change |
| `docs:` | Documentation only |
| `chore:` | Build, CI, tooling |
| `test:` | Test additions or fixes |
| `breaking:` | Breaking API change |

Example: `fix: where() now looks up data types from FIELDS instead of WHERE_OPERATIONS`

## Release Process

Releases are cut via the `bin/release` script:

```bash
bin/release 0.5.1-alpha --push
```

This:
1. Renames `[Unreleased]` → `[version] - date` in CHANGELOG.md
2. Adds a fresh empty `[Unreleased]` section
3. Commits the changelog
4. Creates annotated tag `v<version>`
5. Pushes to origin (with `--push`)

After pushing, Packagist auto-detects the new tag. Consumers run `composer update jcolombo/niftyquoter-api-php` to pull it.
