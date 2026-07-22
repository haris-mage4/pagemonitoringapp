# Progress Log — PageSpeed Monitor

Read this first each session. See `DEVELOPMENT_PLAN.md` for phase breakdown, `CLAUDE.md` for spec.

## Current Status

**Phase: 0 — Project Scaffold** — done, uncommitted on branch.
**Branch: `phase-0-scaffold`.**
**Repo: Laravel 12 backend + separate React frontend scaffolded, nothing committed yet.**

## Next Step

Review the diff, set up a local MariaDB database and credentials in `.env` (see below), run migrations, then start Phase 1 — Database & Models on branch `phase-1-database`.

## Log

### 2026-07-22
- Reviewed and reformatted `CLAUDE.md`. Added Development Workflow section (branch-per-phase, no auto-commit/merge).
- Closed spec gaps: webhook signature verification required, `scans.status` enum defined, `pages.url` clarified as absolute URL, `scan_results.device` column added (mobile-only for MVP), concurrency default clarified, Pest chosen as test framework.
- Left open (deliberate, not yet designed): performance regression detection, API authentication.
- Created `DEVELOPMENT_PLAN.md` with 14 phases (0–13) + backlog.
- Created this file.
- No code written yet — project is spec-only.

### 2026-07-22 (Phase 0 scaffold)
- Decided: separate React SPA (not Inertia) — matches the "React Dashboard → Laravel API" split in CLAUDE.md's architecture diagram and the future "REST API"/"API authentication" backlog items.
- Installed Laravel 12 (`composer create-project laravel/laravel`) at repo root. Removed Laravel's default Blade/Vite frontend (`resources/js`, `resources/css`, `vite.config.js`, `package.json`) since the frontend lives separately.
- Registered `routes/api.php` in `bootstrap/app.php` (not wired by default in Laravel 12); added a placeholder `/api/ping` route and a JSON placeholder at `/`.
- Added `config/pagespeed.php` skeleton: `chrome_path`, `lighthouse_path`, `default_schedule`, `scan_timeout`, `webhook_delay`, `webhook_secret`, `concurrent_scans` — all env-backed, no hardcoded paths.
- Switched `DB_CONNECTION` from Laravel's sqlite default to `mariadb` in `.env` and `.env.example` (per stack: MariaDB or PostgreSQL). **Not yet verified against a real DB** — local MariaDB access wasn't available in the sandbox (`Access denied for user 'root'@'localhost'`); tests were smoke-tested against an in-memory sqlite override instead. Whoever picks this up needs to create the `pagespeed_monitor` database and set real `DB_USERNAME`/`DB_PASSWORD` before running migrations.
- Installed Pest (`pestphp/pest`, `pestphp/pest-plugin-laravel`) and converted the default PHPUnit example tests to Pest syntax; added `tests/Pest.php`. `./vendor/bin/pest` passes (2 tests).
- Scaffolded `frontend/` with Vite + React (`npm create vite@latest frontend -- --template react`), Tailwind CSS v4 (`@tailwindcss/vite` plugin, not the old PostCSS config), Recharts, axios, react-router-dom. Stripped the Vite starter template's demo assets/markup down to a blank placeholder page. Dev server proxies `/api` to `http://localhost:8000` (Laravel's default `artisan serve` port).
- Node note for this machine: system `node` (via `/bin/node`) is very old and the default `npm` shim breaks with it — commands need `source ~/.nvm/nvm.sh && nvm use v22.23.1` first.
- Nothing committed — left staged/unstaged for manual review per CLAUDE.md workflow rules.
