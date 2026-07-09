# Architecture & Product Decisions

Decisions that shape ongoing work on this WordPress theme, kept here so future sessions don't re-litigate them.

## 2026-07-08 — `player_meta` stays player-scoped, not org-scoped, in WordPress

**Decision:** `wp_br_player_meta` (`player_gender`, `work_level`, `work_function`, `work_sub_function`, `job_profile`, `business_pillar`, `work_cluster`, `work_country`, `work_location`) stays keyed by `player_id` only, as it is today. We will **not** refactor it to be organization-scoped or per-adventure-scoped in this WordPress codebase.

**Why:** This WP theme is not BlueRabbit's long-term platform — the CodeIgniter 4 rewrite (`c:\xampp\htdocs\blue\blue`) is. Per-adventure scoping was considered (it would let template adventures share field definitions with their child adventures) but rejected: a player enrolls in multiple adventures, so per-adventure meta would duplicate and drift the same person's data across adventures. Org-scoping is the architecturally correct answer — this schema already has `wp_br_orgs`, `wp_br_player_org` (many-to-many), and `org_id` on adventures to support it — but investing in that refactor here isn't worth it given the planned migration.

**What we're doing instead:** Building a Meta Data manager scoped to the existing player-keyed model — a per-player editor plus a CSV bulk-updater — to fix the data-entry mistakes from the original bulk import.

**Where the real fix belongs:** The CodeIgniter rewrite. See the matching note in `BLUERABBIT_PROJECT_BRIEF.md` there, under "CodeIgniter Migration — Key Considerations" → "Player Meta / Custom Fields". That version should key custom fields by `(org_id, player_id)` and likely needs configurable-per-org field definitions (EAV or JSON-column) to support the planned Enterprise tier / SSO attribute mapping, rather than hardcoded columns like this WP version.

---

## Dev process: bump the theme version at every good commit point

`style.css` and `style.scss` both carry a WordPress theme header block with a `Version:` line (currently `13.0.1`) — this is what WordPress reads to report the active theme version. Bump it (both files, kept in sync — no build step compiles one from the other) whenever we land at a good commit point, not just for major releases.
