# BlueRabbit — Architectural Decisions Log

Decisions that shape ongoing work on this WordPress theme.
Kept here so future Claude Code sessions and the claude.ai Project don't re-litigate them.

- Decisions made in claude.ai → log here → Claude Code reads them on next session
- Discoveries made in code → log here → paste into claude.ai when relevant

Format: `## YYYY-MM-DD — Decision Title` then rationale + outcome.

---

## 2026-06-30 — Starting CodeIgniter Migration

**Context:** BlueRabbit is a WordPress theme. Migrating to CodeIgniter 4 to remove the WordPress dependency and gain a proper MVC architecture.

**Current state:**
- All business logic lives in `classes/BR-*.php` singletons
- All endpoints are WordPress AJAX actions in `functions.php`
- All pages are WordPress page templates (`page-*.php`)
- Database is MySQL, `br1` database, prefix `br1_br_`

**Migration approach:** TBD — log decisions here as they're made in the claude.ai Project.

---

## 2026-07-08 — `player_meta` stays player-scoped, not org-scoped, in WordPress

**Decision:** `wp_br_player_meta` (`player_gender`, `work_level`, `work_function`, `work_sub_function`, `job_profile`, `business_pillar`, `work_cluster`, `work_country`, `work_location`) stays keyed by `player_id` only. We will **not** refactor it to be organization-scoped or per-adventure-scoped in this WordPress codebase.

**Why:** This WP theme is not BlueRabbit's long-term platform — the CodeIgniter 4 rewrite (`c:\xampp\htdocs\blue\`) is. Per-adventure scoping was considered (it would let template adventures share field definitions with child adventures) but rejected: a player enrolls in multiple adventures, so per-adventure meta would duplicate and drift the same person's data across adventures. Org-scoping is architecturally correct — this schema already has `wp_br_orgs`, `wp_br_player_org` (many-to-many), and `org_id` on adventures to support it — but the refactor isn't worth it given the planned migration.

**What we're doing instead:** A Meta Data manager scoped to the existing player-keyed model — a per-player editor plus a CSV bulk-updater — to fix data-entry mistakes from the original bulk import.

**Where the real fix belongs:** The CodeIgniter rewrite. Key custom fields by `(org_id, player_id)` and use configurable-per-org field definitions (EAV or JSON-column) to support the planned Enterprise tier / SSO attribute mapping, rather than hardcoded columns like this WP version.

---

## Dev process: bump the theme version at every good commit point

`style.css` and `style.scss` both carry a WordPress theme header block with a `Version:` line — this is what WordPress reads to report the active theme version. Bump it (both files, kept in sync — no build step compiles one from the other) whenever we land at a good commit point, not just for major releases.

---
<!-- Add new decisions below this line -->
