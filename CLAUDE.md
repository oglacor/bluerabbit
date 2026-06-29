# BlueRabbit WordPress Theme — CLAUDE.md

## Project Overview
BlueRabbit is a **gamification platform** built as a WordPress theme. It lets organizations create adventures (learning journeys) with quests, steps, challenges, achievements, items, guilds, and more. Players progress through journeys, complete steps, earn rewards.

## Tech Stack
- **Backend:** WordPress theme (PHP), MySQL via `$wpdb`
- **Frontend:** jQuery 4.0.0, vanilla JS, custom SCSS → compiled CSS
- **SCSS Compiler:** `npx sass` (Dart Sass)
- **Server:** XAMPP on Windows 11
- **Database:** `br1` (MySQL root, no password, via `C:\xampp\mysql\bin\mysql.exe`)

## SCSS Architecture (refactored 2026-06-28/29)
The CSS was refactored from a monolithic 10,525-line style.scss into 22 partials:

| File | Purpose |
|------|---------|
| `style.scss` | Theme header + @font-face + 21 @import statements (40 lines) |
| `css/_variables.scss` | Shared palette, fonts, HUD tokens, mixins — single source of truth |
| `css/_reset.scss` | Global reset, base styles, scrollbar |
| `css/_chrome.scss` | Header/footer/taskbar, layout |
| `css/_forms.scss` | Form inputs, buttons, icon buttons, semantic classes |
| `css/_adventures.scss` | Adventure cards, selection grid |
| `css/_dashboard.scss` | Admin dashboard |
| `css/_steps.scss` | Step system, dialogue, characters |
| `css/_navigation.scss` | Login, badges, start menu |
| `css/_carousel.scss` | Card carousel, 3D effects |
| `css/_journey.scss` | Journey builder, board, map |
| `css/_tabis.scss` | Tabi system, modal, builder |
| `css/_achievements.scss` | Achievements, milestones, hex clips |
| `css/_content.scss` | Wall, tabs, accordion, lore, audio |
| `css/_challenges.scss` | Challenges, quizzes, surveys |
| `css/_social.scss` | Chat, profile, progress bars |
| `css/_players.scss` | Player lists, confirm dialogs |
| `css/_items.scss` | Items, item shop |
| `css/_tabi-editor.scss` | Tabi editor |
| `css/_guilds.scss` | Guilds, certificates |
| `css/_reports.scss` | Reports |
| `css/_responsive.scss` | Media queries, print |
| `css/_animations.scss` | Keyframe definitions |
| `css/style-framework.scss` | Utility framework (481 lines, SCSS loops) |
| `css/br-table.scss` | Admin component library |
| `css/br-stats.scss` | Stats dashboard (conditional) |

### Compile Commands
```bash
npx sass style.scss style.css --no-source-map --style=compressed
npx sass css/br-table.scss css/br-table.css --no-source-map --style=compressed
npx sass css/style-framework.scss css/style-framework.css --no-source-map --style=compressed
```

### Semantic CSS Classes (use these instead of utility combos)
- `br-icon-btn` + color variants → replaces `button-icon font _24 sq-40 {color}-bg-{shade}`
- `br-close-btn` → replaces close-confirm button patterns
- `br-icon-bg-on` / `br-icon-bg-off` → icon button state layers
- `br-icon-cancel` / `br-icon-cancel-red` → cancel/delete icon buttons
- `br-container` → centered max-width box
- `br-milestone-label` → centered overlay text
- `br-line-*` (error, subtitle, title, heading, caption, teal, number) → separator text
- `br-text-*` (18, 16, 16-light, 12-muted, center-bold, etc.) → standalone text sizes
- `br-form-btn-*` (red, green, green-18, blue, 30) → form button colors
- `br-action-icon-green` → green action icon
- `br-delete-mini` → small delete button
- `br-cell-inactive` / `br-cell-active-green` / `br-cell-content` → table cells
- `br-initially-hidden` → JS-toggled hidden elements
- `br-page-narrow` → narrow page layout
- `br-btn-submit` → submit button

## Key Files
- `functions.php` — AJAX handlers, DB migrations, enqueues
- `script.js` — Main JS (step collection, AJAX calls, UI logic)
- `classes/BR-*.php` — Data models (Adventure, Quest, Player, Config, etc.)
- `step-*.php` — Step type templates (open, puzzle, scorm, multiple-choice, etc.)
- `page-*.php` — Page templates (adventures, quests, config, etc.)

## Features Added Recently
- **AI Content Validation** — Claude API key per adventure, word count + AI validation on Open Text steps
- **SCORM Steps** — Upload/play SCORM packages with completion tracking
- **Puzzle Steps** — Vanilla JS drag-and-drop (no jQuery UI), cols×rows config
- **Plans Accordion** — Config page plans as accordion with auto-save

## Ongoing: Utility Class Cleanup (Phase 4 tail)
~142 PHP files still have old utility class combos like `font _24 w700 white-color`. These work fine (framework still generates them) but should be migrated to semantic `br-*` classes file by file.

### Files completed this session (2026-06-29):
- footer.php (44 combos)
- page-duplicator.php (41)
- page-post.php (29)
- page-challenges-report.php (29)
- page-schedule.php (27)
- card-quest.php (24)
- icon-select.php (34)
- item-reward.php (18)
- completed-challenge.php (13)
- completed-quest.php (12)
- completed-survey.php (13)
**Total: ~284 combos replaced across 11 files**

### Next priority files (by combo count):
- page-new-adventure.php (28)
- page-manage-adventures.php (27)
- page-my-account.php (26)
- page-items.php (22)
- page-mission.php (20)
- page-organization.php (19)
- page-challenge.php (18)
- page-bulk.php (17)
- survey-question-result.php (16)
- survey-question-form.php (16)

### When editing ANY PHP file:
1. Make the primary change first
2. Scan for utility patterns: `font _XX wNNN`, `white-color`, `opacity-NN`, `color-bg-NNN`, `sq-NN`
3. Replace with existing `br-*` classes or create new ones in the relevant SCSS partial
4. Compile both CSS outputs after changes

## Rules
- **NEVER use inline styles** in PHP templates — always SCSS classes
- **When editing any PHP file**, also clean up old utility classes in that same file
- Add new semantic classes to the relevant SCSS partial, not style-framework.scss
- Always compile after SCSS changes to catch errors
- Use `$wpdb->prepare()` for all database queries with user input
- Feature-gate with `$my_features['feature_name'][$f_role]`

## Git Workflow
- Branch: `new_steps_system`
- Remote: `origin` → GitHub `oglacor/bluerabbit`
- Commit frequently with descriptive messages
- Push after each logical unit of work
