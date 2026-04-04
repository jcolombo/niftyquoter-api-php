# Worktree Shared Directory Rule

When running in a git worktree (i.e., this checkout is NOT the main worktree), `.ai/`, `.development/`, and `.claude/` must be **read from and written to the main worktree**, not the local worktree copy. This ensures all worktrees share the same knowledge base without merge conflicts.

**How to detect**: Run `git worktree list --porcelain` — the first `worktree` line is the main repo path. If your current working directory differs from that path, you are in a worktree.

**How to find main repo path**:
```bash
git worktree list --porcelain | head -1 | sed 's/worktree //'
```

**Rules**:
- **Reads**: Always read `.ai/`, `.development/`, and `.claude/` from the main worktree path (not the local copy).
- **Writes**: Always write to the main worktree's `.ai/`, `.development/`, and `.claude/` directories.
- **Never** write to the local worktree copies of these directories — they exist only as an initial snapshot.
- This applies to all files in these directories, including subdirectories.
