# Extension Spec — Auth, Uptime Monitoring, JS Error Tracking, Email Alerts

Extends `CLAUDE.md` (PageSpeed Monitor). Same repo, same stack (Laravel 12 API + React/Vite frontend). This doc adds new capability on top of existing Lighthouse scanning — does not replace it.

---

## 1. User Authentication

- Signup, login.
- Laravel Sanctum (SPA token auth — fits existing React SPA + Laravel API split; drops the current CLAUDE.md "trusted network" assumption).
- Password reset via email.
- Every existing website/page/dashboard/webhook route gets `auth:sanctum` middleware except the Bitbucket webhook (stays secret-signed, not user-authenticated).
- `websites.user_id` FK added — websites become owned by a user, not global.

## 2. Website Management (extends existing)

Already exists (Phase 1–2). Add: scope all website/page CRUD to `auth()->user()`.

## 3. Website Monitoring (NEW — uptime, separate from Lighthouse scoring)

- Periodic HTTP check per website (not per page) — HEAD/GET request, record: status (`online`/`offline`/`unavailable`), HTTP code, response time (ms), checked_at.
- Interval: every 1–5 min (config, not tied to existing Lighthouse `schedule` field).
- New table `uptime_checks`: id, website_id, status, http_code, response_time_ms, checked_at.
- Dashboard shows current status + last checked time + response time per website.

## 4. Page-Level Error Monitoring (NEW — JS console errors)

- Needs a client-side collector: a small JS snippet injected into monitored pages (or a headless-browser check, e.g. Puppeteer/Playwright navigating the page and capturing `console.error`/`pageerror` events) — Lighthouse CLI does not capture this, so this is a new scanner path alongside `LighthouseService`.
- New table `page_errors`: id, page_id, message, source (file/line if available), stack, first_seen_at, last_seen_at, occurrence_count.
- Filter/search UI by page, date range, message text.

## 5. Email Notifications (NEW)

- Laravel `Mail` + queued notifications. SMTP/SES/Mailgun/SendGrid via Laravel mail config (no new package needed, just `.env` driver swap).
- Triggers: website online→offline, offline→online, new JS console error detected, (optional) critical failure.
- Dedup rule: only send when status actually *changes* (compare against previous check) or error is genuinely new (not seen in `page_errors` before) — never re-alert on unchanged state.

## 6. Dashboard (extends existing)

Add to existing dashboard: uptime status per website, recent JS errors feed, per-page error counts. Existing Lighthouse metrics/trends stay as-is.

## 7. Background Monitoring (extends existing queue/scheduler)

- New scheduled command `pagespeed:check-uptime` (every 1–5 min per `config/pagespeed.php` → `uptime_check_interval`), queues `CheckWebsiteUptimeJob` per enabled website.
- New scheduled command or job `CheckPageErrorsJob` per enabled page, on its own interval (config, e.g. hourly — checking JS errors on every page every minute would spin up a headless browser too often).
- Both go through existing Database queue driver, existing concurrency-limiting pattern (`WithoutOverlapping`) from Phase 4.

## 8. Config additions (`config/pagespeed.php`)

- `uptime_check_interval` (minutes, default 2)
- `page_error_check_interval` (minutes, default 60)
- `mail` settings via standard Laravel `config/mail.php` (not duplicated here)

## Non-goals / open questions for later

- Real-time (websocket) dashboard push — not in this pass, polling is enough for MVP.
- Headless-browser tool choice for JS error capture (Puppeteer via Node sidecar vs Panther/Playwright-PHP) — decide in Phase 17, needs a spike.
- Rate-limiting repeated identical errors beyond simple dedup (e.g. same error every check) — `occurrence_count` + `last_seen_at` covers basic case, revisit if noisy.
