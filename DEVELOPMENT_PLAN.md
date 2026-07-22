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

## Backlog (not phased yet — see CLAUDE.md → Future Features)

- Performance regression detection (needs design: baseline window, threshold config, comparison service)
- Desktop device scans
- Magento/WordPress/Shopify/Sitemap discovery
- Slack/Email notifications
- Screenshot history, PDF reports
- User authentication, API authentication, Teams, Public dashboards
