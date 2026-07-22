# PageSpeed Monitor

Lightweight Lighthouse-based website performance monitor. Register websites, add pages, schedule scans, view trends, catch regressions.

See [CLAUDE.md](CLAUDE.md) for full architecture/spec, [DEVELOPMENT_PLAN.md](DEVELOPMENT_PLAN.md) for roadmap, [PROGRESS.md](PROGRESS.md) for current status.

## Stack

- Backend: Laravel 12, PHP 8.3+, MariaDB/PostgreSQL, Laravel Scheduler + Queue (database driver), Symfony Process, Pest
- Frontend: React 19, Vite, Tailwind CSS 4, Recharts
- Scanner: Lighthouse CLI + headless Chromium (no Google API needed)

## Requirements

- PHP 8.3+, Composer
- Node.js 18+, npm
- MariaDB or PostgreSQL
- Chromium/Chrome (headless) + Lighthouse CLI (`npm install -g lighthouse`)

## Setup (dev)

```bash
# backend
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_DATABASE=pagespeed_monitor
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

PAGESPEED_CHROME_PATH=/usr/bin/chromium-browser
PAGESPEED_LIGHTHOUSE_PATH=/usr/local/bin/lighthouse
PAGESPEED_WEBHOOK_SECRET=change-me
```

Create DB, then:

```bash
php artisan migrate
```

Frontend:

```bash
cd frontend
npm install
```

## Running in dev

Need four things running (separate terminals or a process manager):

```bash
php artisan serve              # API, http://localhost:8000
php artisan queue:work         # processes ScanWebsiteJob / ScanPageJob
php artisan schedule:work      # runs scheduled scans locally (dev substitute for cron)
cd frontend && npm run dev     # Vite dev server, proxies to API
```

Frontend dev server picks up `VITE_APP_NAME` from `.env` at root (Vite reads Laravel's `.env` via the standard `VITE_*` prefix convention).

Run tests: `php artisan test` (Pest).

## Running in prod

Backend:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

Set in `.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain
```

Frontend — build static assets, serve via web server (not `npm run dev`):

```bash
cd frontend
npm install
npm run build
```

`frontend/dist` (or configured build output) gets served by your web server / copied into `public/`.

Long-running processes (use Supervisor or systemd, not raw shell):

- `php artisan queue:work` (or `queue:work --daemon` with `--tries` set) — keep alive, restart on deploy (`php artisan queue:restart`)
- Cron entry for the Laravel Scheduler, runs every minute:
  ```
  * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
  ```

Chrome/Lighthouse paths, timeouts, concurrency, webhook secret — all in `config/pagespeed.php`, sourced from `.env`. No hardcoded paths. Confirm `PAGESPEED_CHROME_PATH` and `PAGESPEED_LIGHTHOUSE_PATH` are correct for prod server before first scan.

Webhook endpoint `POST /api/webhooks/bitbucket/deployment` needs `PAGESPEED_WEBHOOK_SECRET` set — unsigned requests get rejected.

## Notes

- MVP scans mobile only.
- Default concurrency: one Lighthouse scan at a time (`PAGESPEED_CONCURRENT_SCANS`).
- Dashboard/API currently assumes trusted local network — no auth yet (see Future Features in CLAUDE.md).
