# NiftyQuoter API PHP SDK - AI Assistant Guide

## What This Is

A public PHP SDK for the [NiftyQuoter](https://niftyquoter.com) sales proposal API. Modeled after the `jcolombo/paymo-api-php` package — same structural patterns, conventions, and architecture.

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

## Documentation Index

Reference these docs for deeper context before asking the user or making assumptions:

| Document | When to Read |
|----------|-------------|
| [docs/TECH-STACK.md](docs/TECH-STACK.md) | PHP version rationale, dependencies, architecture patterns, API reference links |
