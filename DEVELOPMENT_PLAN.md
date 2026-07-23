# Development Plan — PageSpeed Monitor

See `CLAUDE.md` for full spec. This file breaks the build into phases/branches. See `PROGRESS.md` for current status — start there first each session.

Branch per phase. No auto-commit/merge — leave changes staged for manual review per `CLAUDE.md` → Development Workflow.

---

## Phase 0 — Project Scaffold
Branch: `phase-0-scaffold`

- Laravel 12 install
- React + Vite + Tailwind install (separate frontend, or Laravel+Inertia — decide during phase)
- `config/pagespeed.php` skeleton (chrome path, lighthouse path, timeout, webhook delay, webhook secret, concurrent scans)
- Base repo structure, `.env.example`

## Phase 1 — Database & Models
Branch: `phase-1-database`

- Migrations: `websites`, `pages`, `scans`, `scan_results`
- Models + relationships (Website hasMany Page, Page hasMany Scan, Scan hasOne ScanResult)
- Factories + seeders for local dev

## Phase 2 — Core Services (CRUD)
Branch: `phase-2-core-services`

- WebsiteService, PageService (add/edit/delete/enable-disable)
- Form requests / validation
- Controllers (thin, delegate only)
- API routes for website/page CRUD

## Phase 3 — Lighthouse Scanner
Branch: `phase-3-scanner`

- LighthouseService wrapping Symfony Process
- Mobile-only Lighthouse run, parse JSON → scan_result fields
- Error handling: exit code, error message, timestamp; scan still marked completed/failed appropriately

## Phase 4 — Queue Jobs
Branch: `phase-4-queue-jobs`

- ScanWebsiteJob (enabled pages → dispatch ScanPageJob)
- ScanPageJob (execute scan via LighthouseService, store result)
- Concurrency respects `concurrent_scans` config (default 1)

## Phase 5 — Scheduler
Branch: `phase-5-scheduler`

- Per-website schedule (hourly/6h/daily/weekly) mapped to Laravel Scheduler
- Dispatches ScanWebsiteJob per due website

## Phase 6 — Webhook Trigger
Branch: `phase-6-webhook`

- `POST /api/webhooks/bitbucket/deployment`
- Signature/secret verification (reject unsigned requests)
- Configurable delay (default 10 min) before dispatching scan

## Phase 7 — Dashboard API
Branch: `phase-7-dashboard-api`

- MetricsService: totals, last scan, failed scans, average performance, recent activity
- Trend endpoints (performance/LCP/CLS/TBT) with range param (24h/7d/30d/custom)

## Phase 8 — React App Shell
Branch: `phase-8-react-shell`

- Routing, Sidebar, TopNavigation, StatusBadge
- API client setup

## Phase 9 — Dashboard UI
Branch: `phase-9-dashboard-ui`

- MetricCard, TrendChart (Recharts) wired to Phase 7 endpoints
- Recent activity feed

## Phase 10 — Website Details UI
Branch: `phase-10-website-details`

- Current/previous score, performance history, pages list, latest/next scan
- WebsiteTable, WebsiteCard components

## Phase 11 — Page Details UI
Branch: `phase-11-page-details`

- Latest metrics, TrendChart, ScanHistoryTable, raw Lighthouse report viewer

## Phase 12 — Manual Scan Trigger + Polish
Branch: `phase-12-manual-trigger-polish`

- Manual "scan now" action (UI + endpoint)
- Error states, loading states, empty states across UI

## Phase 13 — Tests
Branch: `phase-13-tests`

- Pest tests for Services (WebsiteService, PageService, ScanService, LighthouseService, WebhookService, MetricsService)
- Job tests with faked queue/process

---

---

# Extension — Auth, Uptime, JS Error Monitoring, Email Alerts

See `EXTENSION_SPEC.md` for full spec. Extends phases 0–13 above, same repo/stack.

## Phase 14 — Auth (Sanctum)
Branch: `phase-14-auth`

- Sanctum install, `users` migration already exists (Laravel default) — add signup/login/logout/password-reset endpoints
- `websites.user_id` FK migration + scope `WebsiteService` to authenticated user
- `auth:sanctum` middleware on all website/page/dashboard routes (webhook route stays secret-signed, untouched)
- Frontend: login/signup pages, auth token storage, axios interceptor, route guards

## Phase 15 — Uptime Monitoring
Branch: `phase-15-uptime`

- `uptime_checks` migration + model (belongsTo Website)
- `UptimeService` — HTTP check (status/http_code/response_time_ms)
- `CheckWebsiteUptimeJob` + `pagespeed:check-uptime` scheduled command (config-driven interval, default 2 min)
- Dashboard: current status, last checked, response time per website

## Phase 16 — Email Notifications (status change)
Branch: `phase-16-email-uptime`

- Laravel Mail/Notification classes for online→offline / offline→online
- Dedup: compare against previous `uptime_checks` row, only notify on actual change
- Queued mail, `.env` mail driver config (SMTP/SES/Mailgun/SendGrid)

## Phase 17 — JS Console Error Capture (spike + impl)
Branch: `phase-17-js-error-capture`

- Spike: headless-browser tool choice (Puppeteer/Node sidecar vs Playwright-PHP/Panther) — decide before building
- `page_errors` migration + model (belongsTo Page)
- `PageErrorService` — navigate page headless, capture console errors/pageerror, upsert by message (first_seen_at/last_seen_at/occurrence_count)
- `CheckPageErrorsJob` + scheduled command (config-driven interval, default 60 min)

## Phase 18 — JS Error Notifications + Dashboard UI
Branch: `phase-18-js-error-ui`

- Email on genuinely-new error (not seen in `page_errors` before)
- Page Details UI: error log list, filter/search by message/date
- Dashboard: recent errors feed, per-page error counts

## Phase 19 — Extension Tests
Branch: `phase-19-extension-tests`

- Pest tests: auth flows, UptimeService, CheckWebsiteUptimeJob, dedup logic, PageErrorService, notification dedup

---

## Backlog (not phased yet — see CLAUDE.md → Future Features)

- Performance regression detection (needs design: baseline window, threshold config, comparison service)
- Desktop device scans
- Magento/WordPress/Shopify/Sitemap discovery
- Slack/Email notifications
- Screenshot history, PDF reports
- User authentication, API authentication, Teams, Public dashboards
