# NiftyQuoter API PHP SDK - AI Assistant Guide

## What This Is

A public PHP SDK for the [NiftyQuoter](https://niftyquoter.com) sales proposal API.

- **API Documentation**: https://niftyquoter.docs.apiary.io
- **Namespace**: `Jcolombo\NiftyquoterApiPhp`
- **Package**: `jcolombo/niftyquoter-api-php`

## What This Is NOT

This is a **public open-source library**. It contains no internal business logic, no proprietary systems, and no references to private infrastructure. Everything in this repo must be suitable for public distribution on Packagist.

## Local-Only Directories

These directories are gitignored and must **never** be committed:

| Directory | Purpose |
|-----------|---------|
| `.ai/` | AI-generated non-code content (research, analysis, drafts). Default output path for any AI-generated artifact that isn't functional library code. |
| `.development/` | Developer planning notes, design docs, task tracking. Default output path for any planning or documentation work done during local development. |
| `.scratch/` | Throwaway experiments, temp files, junk drawer. |

**Rule**: Unless explicitly instructed to place content elsewhere, all non-library output goes into `.ai/` or `.development/`. Never place planning docs, AI drafts, or scratch work in tracked directories.

**Worktrees**: If working in a git worktree, read [docs/WORKTREES.md](docs/WORKTREES.md) first — `.ai/`, `.development/`, and `.claude/` must be read/written to the main repo, not the worktree copy.

## Documentation Freshness Rule (MANDATORY)

**After ANY code change**, you MUST check whether the change impacts, negates, updates, or otherwise makes stale any content in:

1. **All tracked `docs/*.md` files** — `docs/TECH-STACK.md`, `docs/WORKTREES.md`
2. **All tracked root `*.md` files** — `README.md`, `CHANGELOG.md`, `OVERRIDES.md`, `CONTRIBUTING.md`

**How to check:** Read each file and look for references to the code you changed — method names, class names, behavior descriptions, usage examples, architecture claims. If any documentation describes behavior that no longer matches the code, update it immediately.

**CHANGELOG.md** must always have an `[Unreleased]` entry for any functional change (new methods, changed behavior, removed features, breaking changes).

This is not optional. Documentation that contradicts the code is worse than no documentation.

## Changelog Rule (MANDATORY)

**At the end of every session that changes functional code**, you MUST read [docs/CHANGELOG-GUIDE.md](docs/CHANGELOG-GUIDE.md) and update the `[Unreleased]` section of `CHANGELOG.md` accordingly.

- Add entries for new features, behavior changes, bug fixes, removals, and breaking changes
- Consolidate entries: if a new change impacts an existing `[Unreleased]` entry, revise it — don't add contradictory bullets
- Use Conventional Commits prefixes (`feat:`, `fix:`, etc.) in commit messages
- This is not optional. An accurate `[Unreleased]` section is required before ending any code-change session.

## Documentation Index

Reference these docs for deeper context before asking the user or making assumptions:

| Document | When to Read |
|----------|-------------|
| [docs/CHANGELOG-GUIDE.md](docs/CHANGELOG-GUIDE.md) | After any code change session — changelog maintenance rules and conventions |
| [docs/TECH-STACK.md](docs/TECH-STACK.md) | PHP version rationale, dependencies, architecture patterns, API reference links |
