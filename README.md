<img src="assets/svg/ducky-logo-final.svg" height="250" />

# ducky-cms

A simple, flat, PHP-based CMS with SQLite.

## Get Started

1. Clone the repo.
2. Make sure `db/` exists and is writable.
3. Run the app on any PHP server (e.g. `php -S localhost:8000`).
4. Visit `/` in your browser and follow the setup steps:
   - Create database
   - Set site URL
   - Create admin user
5. Log in and start building.

## Tech

- PHP 8.2+
- SQLite
- Zero dependencies

## Database

One file: `db/ducky.sqlite`  
Schema is in `db/schema.php`

## Structure

- `setup/` – initial install
- `auth/` – login/logout
- `dashboard/` – admin UI
- `includes/` – shared functions

## TODO Overview

### Core
- [ ] Page Editor (title, slug, HTML)
- [ ] Save and render Pages
- [ ] Sections system (reusable HTML blocks)
- [ ] Section insert support in Page HTML
- [ ] Global Meta fields (site-wide settings)
- [ ] Per-Page Meta fields (custom page data)

### Content
- [ ] Content Types (formerly “custom types”)
- [ ] Data Lists (repeaters for Content Types)
- [ ] List tag renderer (e.g. <data-list type="events" />)

### Admin UI
- [ ] Admin UI for managing Pages, Types, and Lists
- [ ] Syntax highlighting (CodeMirror or similar)
- [ ] Optional page preview
- [ ] Admin Styles

### Security
- [ ] Better session/auth security (expiration, rotation)

### Fallbacks
- [ ] 404 page and routing fallback
- [ ] Static site export (maybe)
- [ ] Caching (static)

### Setup
- [ ] Setup Styles