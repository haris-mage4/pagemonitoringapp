# Progress Log — PageSpeed Monitor

Read this first each session. See `DEVELOPMENT_PLAN.md` for phase breakdown, `CLAUDE.md` for spec.

## Current Status

**Phase: 13 — Tests** — done, uncommitted on branch.
**Branch: `phase-13-tests`.**
**Phases 0–12 merged to `master` (committed). This closes out the phased plan in `DEVELOPMENT_PLAN.md` — remaining work is the Backlog section + gaps flagged below.**

## Next Step

Review diff, commit/merge. All 14 phases (0–13) are now done — this closed the original PageSpeed spec.

**New extension scope added 2026-07-23:** `EXTENSION_SPEC.md` + `DEVELOPMENT_PLAN.md` Phases 14–19 (auth via Sanctum, uptime monitoring, JS console error capture, email alerts).

**Phase 14 — Auth (Sanctum) — done, uncommitted on branch `phase-14-auth`.** Next: Phase 15 (uptime monitoring).

Remaining from original spec before calling that part complete:
- Real Lighthouse CLI + Chromium still aren't installed in this sandbox (only `google-chrome` binary present) — every scan-related test/manual check so far used a fake JSON-emitting stand-in. Verify against the real `lighthouse` binary + headless Chromium before trusting any of it in prod.
- Custom date-range picker for trend charts is unbuilt (API supports `range=custom&from=&to=`, UI only exposes 24h/7d/30d quick buttons).
- Performance regression detection (success criterion #8 in CLAUDE.md) is still an unstarted Future Feature — CLAUDE.md itself flags success criteria and feature list as out of sync on this point.
- API authentication is still assumed-trusted-network per CLAUDE.md's Future Features list — revisit before any public deployment.

## Log

### 2026-07-23 (Phase 14 auth)
- Installed `laravel/sanctum`, published config/migration (`personal_access_tokens`). Used API token auth (`HasApiTokens`, Bearer token), not SPA cookie — simpler given the Vite dev server / Laravel API split, no shared domain/session assumed.
- `users` migration was already Laravel's default; no changes needed there.
- `websites.user_id` FK migration (`cascadeOnDelete`), `Website belongsTo User`, `WebsiteFactory`/`DatabaseSeeder` updated to attach `user_id`.
- `AuthService` (register/login/logout) + `AuthController` + `PasswordResetController` (wired to Laravel's built-in `Password` broker — `password_reset_tokens` table already existed from Phase 0 scaffold, unused until now). Routes: `POST auth/register`, `auth/login`, `auth/forgot-password`, `auth/reset-password` (public); `auth/logout`, `auth/me` (authenticated).
- All existing website/page/dashboard routes moved under `auth:sanctum` middleware group. Bitbucket webhook route untouched (stays secret-signed, not user-authenticated — deployments aren't a logged-in user action).
- Ownership enforcement: `WebsitePolicy`/`PagePolicy`, checked via explicit `$this->authorize()` calls per controller action (not Laravel's `authorizeResource()` — that relies on controller `->middleware()`, which plain controllers no longer support by default in Laravel 12; using it threw `Call to undefined method ...::middleware()` at runtime, caught via live curl testing, not just unit tests). `PagePolicy` checks ownership through `$page->website->user_id` since pages don't carry `user_id` directly.
- `WebsiteService::list()`/`create()` and `MetricsService::dashboardSummary()`/`trend()` now take a `$userId` and scope their queries — these were a real cross-tenant data leak if left global (dashboard/list would've shown every user's websites). `WebsiteService::details()`/`update()`/`delete()` rely on the controller-level policy check instead (already receive the specific `$website` model via route binding).
- Updated `WebsiteServiceTest`/`MetricsServiceTest` for the new signatures — all 26 Pest tests pass.
- Verified against real MariaDB + running `artisan serve`: registered/logged in a real user, confirmed empty website list for a new user (no cross-tenant bleed), created a website (persisted with correct `user_id`), confirmed `GET /websites/{id}` on another user's website returns 403, confirmed unauthenticated requests get 401.

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

### 2026-07-22 (Phase 1 database)
- Migrations: `websites`, `pages`, `scans`, `scan_results` per CLAUDE.md schema. Renamed generated migration files (Artisan gave them identical timestamps, alphabetical fallback would've ordered `pages` before `websites` and `scan_results` before `scans` — broke FK dependency order) to `..._153401_websites`, `..._153402_pages`, `..._153403_scans`, `..._153404_scan_results`.
- `pages.page_type` and `scans.status`/`trigger` as `enum` columns matching CLAUDE.md's fixed value lists.
- `scan_results` adds `exit_code`/`error_message` (nullable) beyond the base spec list — needed for the Error Handling section's "if Lighthouse fails, store exit code + error message + timestamp" requirement (timestamp covered by `created_at`).
- Models: `Website hasMany Page`, `Page belongsTo Website / hasMany Scan`, `Scan belongsTo Page / hasOne ScanResult`, `ScanResult belongsTo Scan`. `enabled` cast to boolean, `raw_json` cast to array, `started_at`/`finished_at` cast to datetime.
- Factories for all four models + wired `DatabaseSeeder` to create 3 websites, each with 3 pages, each with a scan + scan result (deleted the stray `DatabaseSeederPageSpeed` Artisan generated — folded into the existing `DatabaseSeeder` instead).
- Verified against real MariaDB: `php artisan migrate:fresh --seed` runs clean, migration order correct, all relationships (including `Scan::scanResult()`, renamed from `result()`) resolve correctly.
- `DatabaseSeeder`'s test-user insert switched to `firstOrCreate` — plain `migrate --seed` (no `--fresh`) was hitting a duplicate-email error on reruns.

### 2026-07-22 (Phase 2 core services)
- `WebsiteService`/`PageService`: list/find/create/update/delete/setEnabled. Controllers stay thin — validate via FormRequest, delegate to service, return JSON.
- `StoreWebsiteRequest`/`UpdateWebsiteRequest`/`StorePageRequest`/`UpdatePageRequest` — `environment` restricted to `production`/`staging` (not in CLAUDE.md's schema list verbatim, inferred from the "Environment" field description and CLAUDE.md's own "production and staging environments" wording), `schedule`/`page_type`/`status` values validated against the enums already fixed in Phase 1 migrations.
- Routes: `Route::apiResource('websites', ...)` + `PATCH websites/{website}/enabled`; `Route::apiResource('websites.pages', ...)->shallow()` (so `show`/`update`/`destroy` are just `/pages/{page}`, no need to carry the parent website in the URL) + `PATCH pages/{page}/enabled`.
- No auth on these routes — matches CLAUDE.md's current "dashboard/API assumes trusted local network" stance; `authorize()` on all FormRequests returns `true` accordingly.
- Verified against real MariaDB: `php artisan serve`, curl'd `POST /api/websites` and `GET /api/websites` — created row persisted, listing returns it with `pages_count`. Re-ran `migrate:fresh --seed` after to reset seed data.

### 2026-07-22 (Phase 3 scanner)
- `LighthouseService::scan()` wraps Laravel's `Process` facade (built on Symfony Process, per CLAUDE.md) — runs `lighthouse <url> --output=json --output-path=stdout --only-categories=performance,accessibility,best-practices,seo --chrome-flags=--headless=new --no-sandbox --chrome-path=...`, mobile only (no `--preset=desktop` passed — lighthouse defaults to mobile emulation). Timeout from `config('pagespeed.scan_timeout')`.
- On non-zero exit or unparseable JSON, returns a failure shape with `exit_code`/`error_message` instead of throwing — `ScanService` always writes a `scan_results` row (all metric columns null on failure) and marks the scan `failed`, never leaves it hanging in `running`. Matches CLAUDE.md's Error Handling section and "website should still be considered scanned."
- `ScanService::scanPage(Page, string $trigger)` — creates the `Scan` row (`running`), runs the scan, creates the `ScanResult` (`device: mobile` always for MVP), updates scan to `completed`/`failed` + `finished_at`.
- Metric extraction maps Lighthouse's `categories.*.score` (0–1 float to 0–100 int) and `audits.*.numericValue` (ms) to our columns — `cumulative-layout-shift`, `total-blocking-time`, `speed-index`, `interactive` (→ TTI), `first-contentful-paint`, `largest-contentful-paint`.
- **Sandbox has no `lighthouse` CLI installed** (only `google-chrome`). Smoke-tested by pointing `PAGESPEED_LIGHTHOUSE_PATH` at a throwaway shell script emitting canned Lighthouse-shaped JSON — confirmed parsing, DB writes, status transitions, and the failure path (ran once with a bad path first, got `exit_code: 127` + captured stderr, scan correctly marked `failed`). Not yet run against the real `lighthouse` binary + headless Chromium — do that before considering this phase production-ready.
- Had to `php artisan config:clear` mid-session — a stale `bootstrap/cache/config.php` from an earlier `config:cache` was shadowing the `PAGESPEED_LIGHTHOUSE_PATH` env override during testing.

### 2026-07-22 (Phase 4 queue jobs)
ScanWebsiteJob(Website $website, string $trigger) — pulls enabled pages, dispatches one ScanPageJob per page.
ScanPageJob(Page $page, string $trigger) — thin, delegates to ScanService::scanPage() (from Phase 3). $timeout set from pagespeed.scan_timeout + 30s buffer for process overhead.
Concurrency: middleware() returns WithoutOverlapping keyed to one of N slots (lighthouse-scan-slot-{0..N-1}), N = pagespeed.concurrent_scans (default 1). Slot picked by hashing page id + queue job id, so at most N Lighthouse processes run at once without needing a dedicated queue-per-slot setup. Kept intentionally simple — a cache-lock semaphore, not a custom queue driver — since default concurrency is 1 and CLAUDE.md says raising it should stay a config change.
Verified against real MariaDB + database queue: dispatched ScanWebsiteJob for a seeded website (3 enabled pages) → confirmed 3 ScanPageJob rows landed in the jobs table → ran queue:work --once --stop-when-empty three times (fake Lighthouse stand-in from Phase 3, same caveat about the real CLI not being installed here) → all 3 processed, scans table shows 3 new completed rows, 0 failed, jobs table back to empty.

### 2026-07-22 (Phase 6 webhook)
- `POST /api/webhooks/bitbucket/deployment`, guarded by `VerifyBitbucketWebhookSignature` middleware — HMAC-SHA256 over the raw request body against `pagespeed.webhook_secret`, header `X-Hub-Signature-256: sha256=<hex>` (GitHub/GitLab-style convention; Bitbucket Cloud itself has no built-in HMAC signing, so this assumes the shared-secret is asserted by whatever sits in front — e.g. a Bitbucket Pipe or proxy step that signs the payload — flagging this as an assumption since CLAUDE.md just says "signature/shared secret" without specifying the scheme). Missing/wrong signature → 401. Missing config → 500 (fails closed, not open).
- `BitbucketDeploymentRequest` requires `website_id` (int, must exist) in the JSON body — CLAUDE.md doesn't specify the payload shape and a real Bitbucket deployment payload has no direct link to our `websites` table, so the webhook caller (Pipe/proxy) is expected to supply which website to scan explicitly.
- `WebhookService::handleDeployment(Website)` — dispatches `ScanWebsiteJob` (trigger `webhook`) delayed by `pagespeed.webhook_delay` seconds (default 600 = 10 min).
- Set a local `PAGESPEED_WEBHOOK_SECRET` in `.env` (gitignored) for testing — was empty by default.
- Verified against real MariaDB + queue: unsigned request → 401; correctly HMAC-signed request → 202, and confirmed the queued job's `available_at` in the `jobs` table landed ~600s in the future, not immediate. Cleared the test job after (`queue:clear`).

### 2026-07-22 (Phase 7 dashboard API)
- `MetricsService::dashboardSummary()` — total websites, last scan (with page/website/scanResult eager-loaded), failed scan count, average performance (avg of `scan_results.performance` across all rows — no "latest per page" windowing yet, simplest thing that satisfies the current spec), 10 most recent scans as "recent activity."
- `MetricsService::trend(metric, range, from?, to?)` — `metric` restricted to `performance`/`lcp`/`cls`/`tbt`; `range` to `24h`/`7d`/`30d`/`custom` (custom requires explicit `from`/`to`). Joins `scan_results` to `scans` on `created_at`, returns `[{scanned_at, value}, ...]` ordered chronologically — shape chosen to drop straight into a Recharts `<LineChart>` in Phase 9 without transformation.
- `GET /api/dashboard/summary`, `GET /api/dashboard/trend/{metric}` (route-constrained via `whereIn` to the 4 valid metrics → unknown metric is a clean 404 rather than reaching the service). `TrendRequest` validates `range`/`from`/`to`.
- Verified against real MariaDB: `GET /summary` returns real seeded counts/last-scan; `GET /trend/performance?range=7d` returns the seeded scan_result values; `GET /trend/bogus?range=7d` → 404 (route constraint); `GET /trend/lcp?range=custom` with no dates + `Accept: application/json` → 422 with field-level errors (first attempt without the `Accept` header got a 302 redirect instead — reminder that FormRequest validation only returns JSON when the client asks for it).

### 2026-07-22 (Phase 5 scheduler)
- `pagespeed:dispatch-scheduled-scans` artisan command — for each enabled `Website`, works out if it's "due" by comparing `now()` against the most recent `scans.created_at` across all its pages, against a fixed interval map (`hourly`=60min, `every_6_hours`=360min, `daily`=1440min, `weekly`=10080min). Never-scanned websites are always due. Dispatches `ScanWebsiteJob` (trigger `schedule`) when due.
- Registered in `routes/console.php` via `Schedule::command(...)->everyMinute()->withoutOverlapping()` (Laravel 12's routes/console.php scheduling, no `Kernel.php` — command polls every minute and only actually dispatches for websites whose interval elapsed, rather than trying to register a distinct cron expression per website's `schedule` value at boot).
- **Bug caught during smoke testing:** `now()->diffInMinutes($lastScannedAt)` returned a *negative* number — this Carbon version stopped defaulting to absolute-value diffs. Wrapped in `abs()`. Confirms why "verify against real DB, don't trust syntax-only checks" matters — this would've silently made every website perpetually "not due."
- Verified against real MariaDB: ran the command with fresh seed data (nothing due, 0 jobs queued — correct, seeder scans are recent) → manually aged one website's scans 10 days back → reran → exactly that website dispatched, others untouched → reset via `migrate:fresh --seed`.

### 2026-07-22 (Phase 8 react shell)
- `src/api/client.js` — axios instance, `baseURL: '/api'`, `Accept: application/json` (Vite dev proxy already forwards `/api` to `artisan serve` per `vite.config.js`).
- `Sidebar` (nav links, `NavLink` active-state styling), `TopNavigation` (static header), `StatusBadge` (color-coded by scan status `pending`/`running`/`completed`/`failed` and enabled/disabled).
- `AppLayout` — `TopNavigation` + `Sidebar` + `<Outlet/>`, routing via `createBrowserRouter`: `/` → Dashboard, `/websites` → Websites, `/websites/:websiteId` → WebsiteDetails, `/pages/:pageId` → PageDetails. All four pages are placeholder stubs — real UI comes in Phases 9–11.
- Verified in a real browser (this sandbox has `google-chrome` but no display, so used `--headless=new --dump-dom`, not just `npm run build`/curl): loaded `/` and `/websites` on the Vite dev server, confirmed the Sidebar nav renders and each route's placeholder heading actually shows up, not just that the SPA shell returns 200.

### 2026-07-22 (Phase 9 dashboard UI)
- `MetricCard` (label + value), `TrendChart` (Recharts `LineChart`, quick-range buttons 24h/7d/30d wired to `onRangeChange`), `RecentActivity` (list using the existing `StatusBadge`).
- `Dashboard` page fetches `/dashboard/summary` once on mount, and independently fetches `/dashboard/trend/{metric}` for all four metrics (performance/lcp/cls/tbt) whenever that metric's own range selector changes — each chart has its own range state, not a single shared one, so a user can compare e.g. performance-7d against lcp-24h side by side.
- **Gap vs CLAUDE.md:** charts only expose the 24h/7d/30d quick buttons, no custom date-range picker UI yet, even though `MetricsService::trend()` (Phase 7) already supports `range=custom&from=&to=`. Flagged in Next Step — needed before this fully satisfies "every chart supports Last 24h/7d/30d/Custom range."
- Verified against the real backend in an actual rendered browser (not just build output): ran `artisan serve` + `vite` dev server together, pointed the frontend at the running API, and took a real headless-Chrome screenshot (`--virtual-time-budget` to let the async fetches resolve before capture — an immediate `--screenshot` without it caught the page mid-loading-state and would've been a false pass). Confirmed real seeded numbers in the metric cards (3 websites, real last-scan timestamp, 0 failed, 39.6 avg performance), populated axes on all 4 charts, and a real recent-activity list with website names/URLs/triggers/status badges.

### 2026-07-22 (Phase 10 website details)
- Extracted the `schedule` → interval-minutes map (`hourly`/`every_6_hours`/`daily`/`weekly`) out of `DispatchScheduledScansCommand` into `Website::SCHEDULE_INTERVAL_MINUTES` — both the scheduler command and the new details endpoint need it, avoided duplicating the map.
- `WebsiteService::details(Website)` replaces the old `find()` (deleted, had no other callers) as what `WebsiteController::show()` returns — now the single `GET /api/websites/{website}` response carries everything the Website Details page needs: `website`, `pages` (each with its own `latest_scan.scan_result` eager-loaded per page, N+1 query per page — acceptable at MVP page-counts, revisit if a website ever has dozens of pages), `latest_scan` across the whole website, `current_score`/`previous_score` (last two `scan_results.performance` values by `scans.created_at`), `performance_history` (full ordered series), `next_scheduled_scan` (`null` if disabled or unknown schedule, `now()` if never scanned, else last-scanned + interval — same logic as the scheduler command, now backed by the shared constant).
- Frontend: `WebsiteTable` (list page, links into details), `WebsiteCard` (name/environment/enabled + current vs previous score with a colored delta + next-scheduled-scan), reused `TrendChart` for the performance-history chart by making its range-button row conditional (`onRangeChange` optional) instead of writing a second chart component — history has no independent range control, so just don't render the buttons.
- `Websites.jsx` and `WebsiteDetails.jsx` wired to `GET /websites` and `GET /websites/{id}` respectively.
- Verified against real MariaDB, in a real headless-Chrome screenshot (not just build/curl): confirmed the current/previous score delta (`24` vs `79` → `-55`, colored red), `next_scheduled_scan` matching the `every_6_hours` math (last scan 22:43 + 6h = 04:43 next day), and the pages list showing each page's own latest score/status — all real seeded values, not placeholders. Caught and fixed one cosmetic bug: `schedule.replace('_', ' ')` only replaced the first underscore ("Every 6_hours") — switched to `replaceAll`.

### 2026-07-22 (Phase 11 page details)
- `PageService::details(Page)` replaces `find()` (deleted, no other callers) as what `PageController::show()` returns — bundles `page` (with `website`), `latest_scan`, `scan_history` (all scans for the page, newest first, each with its own `scan_result`), `performance_history` (chronological, `performance` non-null rows only — a scan with no `scan_result` yet, or a failed scan with a null `performance`, is skipped rather than plotted as a gap), `raw_report` (latest scan's `scan_results.raw_json`, `null` if none).
- Frontend: `ScanHistoryTable` (finished time, trigger, status, performance/LCP/CLS/TBT columns), `RawReportViewer` (collapsed by default — a full Lighthouse JSON dump is large, no reason to render it unless asked for), reused `MetricCard` for the four latest-metric tiles and `TrendChart` (no range buttons, same pattern as Phase 10's performance history) for the trend.
- `PageDetails.jsx` wired to `GET /pages/{id}`, includes a back-link to the parent website.
- Verified against real MariaDB in a real headless-Chrome screenshot: latest metrics (78/2672/0.054/112), scan history row, and raw-report toggle all showing real seeded data, not placeholders.

### 2026-07-22 (Phase 12 manual trigger + polish)
- `POST /api/websites/{website}/scan` → `WebsiteController::scan()` dispatches `ScanWebsiteJob` (trigger `manual`) immediately, no delay — this is the "Trigger scans manually" success criterion from CLAUDE.md. Verified against real MariaDB + queue: curl'd it directly, confirmed exactly one job landed in the `jobs` table, cleared it after.
- Frontend: `WebsiteCard` gets a "Scan Now" button (only rendered when an `onScan` prop is passed, so the component still works standalone elsewhere) — disables itself and shows "Scanning…" while the request is in flight, then a small inline message ("Scan queued."/"Failed to queue scan.") next to it. Wired in `WebsiteDetails.jsx`.
- Loading/error polish across all four data-fetching pages (`Dashboard`, `Websites`, `WebsiteDetails`, `PageDetails`): each now distinguishes "still loading" (`null` state) from "fetch failed" (a caught `.catch()` sets a message, rendered instead of a blank/broken page) from "loaded". Previously a failed fetch just left the page silently stuck showing `—`/nothing, with no indication anything had gone wrong.
- **Verification gap:** the "Scan Now" click itself wasn't exercised through a real browser click — this sandbox has no Puppeteer/CDP client available, only static `--headless=new --screenshot`/`--dump-dom`, neither of which can dispatch a click event. Confirmed instead by (a) curling the endpoint directly and checking the `jobs` table, and (b) a screenshot confirming the button renders in the right place with the right label. The `onClick` handler itself is a two-line `apiClient.post` + state update — low complexity, but flagging that the full user-click path is unverified.

### 2026-07-22 (Phase 13 tests)
- **Test DB switched from sqlite to real MariaDB.** `phpunit.xml` had `DB_CONNECTION=sqlite`/`DB_DATABASE=:memory:` since Phase 0, but this sandbox's PHP has no `pdo_sqlite` extension (only `pdo_mysql`) — every phase so far worked around this by testing manually against the dev DB instead of via `./vendor/bin/pest`. Created a dedicated `pagespeed_monitor_test` database and pointed `phpunit.xml` at `DB_CONNECTION=mysql`/`DB_DATABASE=pagespeed_monitor_test` (host/user/password still come from `.env`). `tests/Pest.php` now applies `RefreshDatabase` to the `Feature` suite. Confirmed test runs don't touch the dev DB: `pagespeed_monitor.websites` still has its 3 seeded rows, `pagespeed_monitor_test.websites` is empty (transactions roll back after each test).
- 26 Pest tests across `tests/Feature/Services/` (WebsiteService, PageService, ScanService, LighthouseService, WebhookService, MetricsService) and `tests/Feature/Jobs/` (ScanWebsiteJob, ScanPageJob) — all passing.
- `LighthouseServiceTest` uses `Process::fake()` (success/non-zero-exit/invalid-JSON cases) instead of touching a real binary. `ScanServiceTest` mocks `LighthouseService` to test the completed/failed status-transition logic in isolation. `WebhookServiceTest` uses `Bus::fake()` to assert `ScanWebsiteJob` is dispatched with a delay rather than actually waiting on one. `ScanWebsiteJobTest` confirms disabled pages are excluded from the fan-out. `ScanPageJobTest` covers both the `handle()` delegation and that `middleware()` returns exactly one `WithoutOverlapping` lock at the default `concurrent_scans=1`.
- Left the pre-existing `tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php` in place — both still pass and cost nothing to keep.
