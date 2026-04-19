# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Non-negotiable requirements

These three requirements apply to **every line of code, every config decision, every template**. They are not optional and must be verified before considering any task done.

### Drupal modules first
**Always prefer existing Drupal modules over custom code.** Only write a custom module or custom PHP when no contributed module solves the problem. When in doubt, check Drupal.org first.

Order of preference:
1. Drupal core functionality or configuration
2. Contributed module (`composer require drupal/...`)
3. Custom configuration or minor theme-level code
4. Custom module — only as a last resort

### WCAG 2.2 compliance
**All code and design decisions must meet WCAG 2.2 Level AA.** This is a hard requirement, not a suggestion.

Mandatory checks for every component:

| Criterion | Requirement |
|---|---|
| 1.1.1 Non-text content | Every `<img>` needs a meaningful `alt`. Decorative images get `alt=""` + `aria-hidden="true"`. |
| 1.3.1 Info & relationships | Use semantic HTML: `<nav>`, `<main>`, `<header>`, `<footer>`, `<section aria-labelledby>`. |
| 1.4.3 Contrast (min) | Text contrast ≥ 4.5:1 (normal), ≥ 3:1 (large/bold). Accent `#D4A017` on white **fails** — only use on dark (`#2D3436`) backgrounds or for large text. |
| 1.4.4 Resize text | No fixed `px` font sizes that break at 200% zoom. |
| 1.4.10 Reflow | No horizontal scrolling at 320px viewport width. |
| 1.4.11 Non-text contrast | UI components (buttons, inputs, focus rings) ≥ 3:1 contrast. |
| 2.1.1 Keyboard | All interactive elements reachable and operable by keyboard alone. |
| 2.4.3 Focus order | Logical tab order; no focus traps except intentional modals. |
| 2.4.7 Focus visible | Always show a visible focus indicator — never `outline: none` without a custom replacement. |
| 2.4.11 Focus not obscured | Sticky header must not fully hide a focused element. |
| 4.1.2 Name, role, value | All interactive elements have accessible name + role. Use `aria-label` / `aria-labelledby` where the visual label is absent. |
| 4.1.3 Status messages | Dynamic messages (form errors, confirmations) exposed via `role="status"` or `aria-live`. |

**Colour contrast note:** `#D4A017` (accent) on `#F9FAFB` (bg) has a contrast ratio of ~2.5:1 — too low for body text. Use it only for:
- Large/bold headings (≥18pt normal or ≥14pt bold)
- Decorative UI accents
- Text on `#2D3436` dark backgrounds (ratio ~6.5:1 ✓)

For interactive elements (buttons, links) on a light background, pair accent colour with a dark enough surrounding context or use `#2D3436` text instead.

### Drupal coding standards & best practices

All PHP, Twig, JS, and CSS must follow the [Drupal coding standards](https://www.drupal.org/docs/develop/standards).

**PHP**
- PSR-4 autoloading; classes in `src/` with correct namespace (`Drupal\<module>`).
- Type hints on all function parameters and return types.
- No direct use of `$_GET`, `$_POST`, `$_SERVER` — use Drupal's `Request` object or dependency injection.
- Services via dependency injection, never `\Drupal::service()` inside a class — only in procedural `.module`/`.theme` code.
- All user-facing strings wrapped in `$this->t()` or `t()` for translatability.
- Use `\Drupal::logger('channel')->error()` for error logging, never `error_log()` or `var_dump()`.
- Strict separation: logic in services/plugins, output in templates.

**Twig templates**
- Never call PHP functions directly in Twig — use preprocess hooks or Twig extensions.
- Always use `{{ variable }}` (auto-escaped) not `{{ variable|raw }}` unless the value is a trusted render array from Drupal core.
- Access control: never bypass Drupal's render pipeline to output raw user content.

**Configuration**
- All configuration managed via `ddev drush cex` / `cim` — never edit the database directly.
- Use `config_ignore` for environment-specific config (credentials, domain settings).
- Modules enabled via Composer + Drush only, never via FTP or manual file copy.

**JavaScript**
- All JS wrapped in `Drupal.behaviors` with `once()` to prevent double-execution.
- No inline `<script>` tags in templates — use `#attached` or `dorpshuis.libraries.yml`.
- No `eval()`, no `innerHTML` with untrusted content.

### OWASP Top 10 — mandatory mitigations

| Risk | Mitigation in this project |
|---|---|
| **A01 Broken Access Control** | Use Drupal's permission system (`hook_permission`, node access). Never hand-roll access checks. Always use `$node->access('view')` etc. |
| **A02 Cryptographic Failures** | No custom crypto. Use Drupal's `password_hash` service. HTTPS enforced via DDEV and `trusted_host_patterns`. |
| **A03 Injection** | Use Drupal's Database API (`\Drupal::database()->select()` with placeholders) — never raw SQL. In Twig, `{{ var }}` auto-escapes; `|raw` is forbidden unless the source is a Drupal render array. |
| **A04 Insecure Design** | Enforce least-privilege roles. No anonymous access to admin paths. Use Drupal's role/permission UI. |
| **A05 Security Misconfiguration** | `settings.ddev.php` is gitignored. Production `settings.php` must set `$config['system.logging']['error_level'] = 'hide'`. No `update.php` accessible without key. |
| **A06 Vulnerable Components** | Run `ddev drush pm:security` regularly. Keep Composer dependencies updated. `composer audit` in CI. |
| **A07 Auth Failures** | Never weaken Drupal's authentication. Use `drupal/tfa` or similar for admin accounts on production. |
| **A08 Software & Data Integrity** | Only install modules from Drupal.org or trusted Composer sources. Verify `composer.lock` is committed. |
| **A09 Logging & Monitoring** | Use Drupal's watchdog (`\Drupal::logger()`). Log failed access attempts. Never log passwords or PII. |
| **A10 SSRF** | Never pass raw user input as a URL to `file_get_contents()`, curl, or HTTP client requests. |

**Additional hard rules:**
- `$_GET`/`$_POST` input: always sanitize via `\Drupal\Component\Utility\Html::escape()` or the Form API — never echo raw request data.
- File uploads: always use Drupal's managed file system with validated extensions and MIME checks.
- CSRF: always use Drupal's Form API token — never build forms outside of it.
- The `php` text format must be disabled. The `full_html` format must be restricted to trusted roles only.

## Environment

This is a **Drupal 11** project running in **DDEV** (Docker-based local dev).

- Site URL: https://dorpshuis.ddev.site
- PHP 8.4, MariaDB 11.8, nginx-fpm
- Node.js version is managed by **Volta** (pinned in `web/themes/custom/dorpshuis/package.json`)

**Always prefix Drush commands with `ddev`** — the database host `db` only resolves inside the Docker network:

```bash
ddev drush <command>    # correct
drush <command>         # will fail — can't reach the DB
```

## Key Commands

### DDEV

```bash
ddev start / stop / restart
ddev ssh                        # shell into the web container
ddev describe                   # show URLs and service status
```

### Drush (always via ddev)

```bash
ddev drush cr                   # clear all caches
ddev drush updb                 # run pending DB updates
ddev drush cex --yes            # export config → config/sync/
ddev drush cim --yes            # import config ← config/sync/
ddev drush config:split:export dev   # export dev-only config → config/split/dev/
ddev drush pm:security          # check for security updates
```

### Theme build (run from `web/themes/custom/dorpshuis/`)

```bash
npm run build           # full build: Tailwind + Sass
npm run watch           # Tailwind + Sass in parallel (development)
npm run tailwind:build  # Tailwind only → dist/css/tailwind.css
npm run sass:build      # Sass only     → dist/css/main.css
npm run lint:js         # ESLint
npm run lint:scss       # Stylelint
npm run lint:fix        # auto-fix both
npm run format          # Prettier (js, scss, twig, json, yml)
```

### Setup scripts

```bash
ddev drush php:script scripts/setup-paragraphs.php   # paragraph types + block content types
```

## Architecture

### Project layout

```
/                          ← Composer root
  config/
    sync/                  ← config export (git-tracked, 410+ YAML files)
    split/dev/             ← dev-only config (devel, debuggers — niet op productie)
  scripts/                 ← one-time setup scripts (drush php:script)
  web/                     ← Drupal docroot
    sites/default/
      settings.php         ← bevat: $settings['config_sync_directory'] = '../config/sync'
      settings.ddev.php    ← DDEV-generated DB credentials (do not edit)
    themes/custom/dorpshuis/
    modules/custom/        ← custom modules (last resort only)
```

**Config workflow:**
- After every admin-UI change: `ddev drush cex --yes` and commit the result
- On deployment: `ddev drush cim --yes` then `ddev drush cr`
- Dev-only modules (devel etc.) go in Config Split `dev` — never in `sync/`

### Theme: Atomic Design + Tailwind

All theme work lives in `web/themes/custom/dorpshuis/`.

**CSS strategy:**
- `css/input.css` → Tailwind directives; compiled to `dist/css/tailwind.css`
- `css/main.scss` → SCSS entry point; imports abstracts + all component SCSS files; compiled to `dist/css/main.css`
- Use **Tailwind utilities** in Twig for layout/spacing; use **SCSS** for component variants, BEM modifiers, hover/focus states, and Drupal-specific overrides

**Design tokens** are defined in two mirrored places — keep them in sync:
- `tailwind.config.js`
- `css/abstracts/_variables.scss`

**Atomic Design layers** under `patterns/`:

| Layer | Prefix | Example |
|---|---|---|
| Atoms | `a-` | `button`, `icon`, `form-element` |
| Molecules | `m-` | `card`, `nav-item` |
| Organisms | `o-` | `header`, `hero`, `footer`, `card-grid` |

Each component can have a `.twig`, `.scss`, and `.js` file alongside each other. Component SCSS files are `@use`-imported into `css/main.scss`.

**Twig namespaces** (`@dorpshuis/organisms/header/header.twig`) are registered via the `components` module in `dorpshuis.info.yml`.

**Drupal template overrides** live in `templates/` (not `patterns/`):
- `templates/layout/` — `html.html.twig`, `page.html.twig`, `page--front.html.twig`
- `templates/content/` — node templates
- `templates/block/` — block templates

The `dorpshuis.theme` PHP file handles preprocess hooks and theme suggestions.

### Homepage regions

`page--front.html.twig` uses six dedicated regions filled via **Admin → Structuur → Blokopmaak**:

| Region key | Purpose |
|---|---|
| `hp_hero` | Hero organism (split layout) |
| `hp_usps` | Block content type `usps` (paragraph velden) |
| `hp_rooms` | View: Zalen |
| `hp_testimonials` | Block content type `testimonials` (paragraph velden) |
| `hp_agenda` | View: Agenda |
| `hp_news` | View: Nieuws |

### Paragraph types

Created via `scripts/setup-paragraphs.php`:

- **usp** — `field_usp_icon`, `field_usp_title`, `field_usp_text`
- **testimonial** — `field_quote`, `field_author`, `field_company`

Used inside block content types `usps` and `testimonials`.

### Libraries

`dorpshuis.libraries.yml` defines:
- `fonts` — Google Fonts (Montserrat)
- `global` — compiled CSS + `js/main.js`
- `header` — mobile menu JS
- `navigation` — keyboard dropdown JS

### Linting rules

- **CSS class names** follow BEM with Atomic Design prefixes: `o-`, `m-`, `a-`, `t-`, `l-`. Stylelint enforces this.
- **SCSS** uses `@use`/`@forward`, not `@import`.
