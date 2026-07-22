# CLAUDE.md

# Project: PageSpeed Monitor

## Purpose

Build a lightweight PageSpeed monitoring platform.

The application allows users to register multiple websites and automatically monitor their Lighthouse performance over time. It is intended to monitor production and staging environments, visualize trends, and detect regressions after deployments.

The system should be designed to use minimal server resources while remaining extensible.

---

# Technology Stack

**Backend**
- Laravel 12
- PHP 8.3+
- MariaDB (or PostgreSQL)
- Laravel Scheduler
- Laravel Queue (Database driver initially)
- Symfony Process
- Pest (testing)

**Frontend**
- React
- Vite
- Tailwind CSS
- Recharts

**Scanner**
- Lighthouse CLI
- Chromium (Headless)

No Google API should be required.

---

# High Level Architecture

```
React Dashboard
        │
        ▼
Laravel API
        │
        ▼
Database
        │
        ▼
Scheduler
        │
        ▼
Queue Jobs
        │
        ▼
Lighthouse CLI
        │
        ▼
Results Database
```

---

# Development Workflow

- Before starting any new feature or phase, create a dedicated branch (e.g. `phase-1-database`, `feature-webhook-auth`).
- Never commit or merge changes automatically. Work stays uncommitted/unstaged on the branch.
- After making progress, stop and leave the changes for manual review, commit, and merge by the user.
- Track overall plan in `DEVELOPMENT_PLAN.md` and current status in `PROGRESS.md`. Update `PROGRESS.md` at the end of a work session so the next session knows where to resume.
- One phase/feature per branch. Don't mix unrelated work in the same branch.

---

# Primary Features

## Website Management

Users can:
- Add website
- Edit website
- Delete website
- Enable/Disable monitoring

Website fields:
- Name
- Base URL
- Environment
- Schedule
- Active

## Page Management

Each website contains pages.

Supported page types:
- Homepage
- CMS
- Category
- Product
- Custom

`pages.url` is the full absolute URL of the page (not a path relative to `base_url`) — keeps scanning logic simple and avoids ambiguity when a page lives on a different subdomain than the website's base URL.

Initially pages are added manually. Future: automatic Magento discovery.

## Scheduling

Every website has its own schedule.

Examples:
- Hourly
- Every 6 hours
- Daily
- Weekly

Laravel Scheduler dispatches scan jobs.

## Performance Scanner

Use Lighthouse CLI. The application executes Lighthouse through Symfony Process.

Expected output:
- Performance
- Accessibility
- SEO
- Best Practices
- FCP
- LCP
- CLS
- TBT
- Speed Index
- TTI

Store the complete Lighthouse JSON.

MVP scans **mobile only**. `scan_results.device` column exists so desktop can be added later without a schema change.

Concurrency: default is one Lighthouse scan at a time (see `config/pagespeed.php` → `concurrent_scans`, default `1`). Raising it is a config change, not a code change — keep this default unless a real resource-capacity need shows up.

## Deployment Trigger

Provide webhook endpoint:

```
POST /api/webhooks/bitbucket/deployment
```

Workflow:

```
Bitbucket
    ↓
Webhook (signature verified)
    ↓
Laravel
    ↓
Delayed Queue Job
    ↓
Scan Website
```

The endpoint must verify a Bitbucket webhook signature/shared secret (`config/pagespeed.php` → `webhook_secret`) before dispatching. Unsigned or invalid requests are rejected — this endpoint is otherwise a public, unauthenticated way to trigger scans.

Delay should be configurable. Default: 10 minutes.

## Dashboard

Landing page should contain:
- Total websites
- Last scan
- Failed scans
- Average performance
- Recent activity

Charts:
- Performance trend
- LCP trend
- CLS trend
- TBT trend

## Website Details

Display:
- Current score
- Previous score
- Performance history
- Pages
- Latest scan
- Next scheduled scan

## Page Details

Display:
- Latest metrics
- Trend chart
- Historical scans
- Raw Lighthouse report

---

# Database Design

## websites
- id
- name
- base_url
- environment
- schedule
- enabled
- created_at
- updated_at

## pages
- id
- website_id
- url
- page_type
- enabled

## scans
- id
- page_id
- status
- started_at
- finished_at
- trigger

`status` values: `pending`, `running`, `completed`, `failed`.

`trigger` values: `schedule`, `webhook`, `manual`.

## scan_results
- id
- scan_id
- device (`mobile`, `desktop` — MVP only ever writes `mobile`)
- performance
- accessibility
- seo
- best_practices
- fcp
- lcp
- cls
- tbt
- speed_index
- tti
- raw_json

No retention policy yet for `raw_json` — revisit if storage becomes a concern; don't add pruning logic speculatively.

---

# Queue Jobs

**ScanWebsiteJob**
- Get enabled pages
- Dispatch ScanPageJob

**ScanPageJob**
- Execute Lighthouse
- Parse JSON
- Store metrics

**DiscoverMagentoPagesJob** (future)
- Discovers CMS, Categories, Products

---

# Services

- WebsiteService
- PageService
- ScanService
- LighthouseService
- WebhookService
- MetricsService

---

# Laravel Principles

- Business logic belongs inside Services.
- Jobs should be thin.
- Controllers should only validate and delegate.
- Never place Lighthouse execution directly inside controllers.

---

# React Principles

Create reusable components, e.g.:
- WebsiteTable
- WebsiteCard
- PerformanceChart
- MetricCard
- TrendChart
- ScanHistoryTable
- Sidebar
- TopNavigation
- StatusBadge

---

# Charts

Use Recharts. Every chart should support:
- Last 24 hours
- Last 7 days
- Last 30 days
- Custom range

---

# Error Handling

If Lighthouse fails, store:
- Exit code
- Error message
- Timestamp

Website should still be considered scanned.

---

# Configuration

`config/pagespeed.php` contains:
- Chrome executable
- Lighthouse executable
- Default schedule
- Scan timeout
- Webhook delay
- Webhook secret
- Concurrent scans

No hardcoded paths.

---

# Performance

Goals:
- Run only one Lighthouse scan at a time initially.
- Avoid unnecessary concurrent Chrome instances.
- Reuse Scheduler + Queue.
- Keep memory usage low.

---

# Future Features

- Magento page discovery
- WordPress discovery
- Shopify discovery
- Sitemap discovery
- Slack notifications
- Email alerts
- Performance regression detection (needs its own design: baseline window, threshold config, comparison service — not yet specified)
- Desktop device scans (schema already supports it via `scan_results.device`)
- Screenshot history
- PDF reports
- User authentication
- API authentication (dashboard↔API currently assumes trusted/local network — revisit before any public deployment)
- Teams
- Public dashboards
- REST API

---

# Development Guidelines

- Prefer simple implementations.
- Avoid premature optimization.
- Write readable code.
- Follow Laravel conventions.
- Prefer composition over inheritance.
- Use dependency injection.
- Avoid static helper classes.
- Write tests for business logic using Pest.

---

# Coding Style

- Keep functions small.
- One responsibility per class.
- Avoid duplicated logic.
- Meaningful variable names.
- No magic numbers.
- Configuration belongs in config files.

---

# Success Criteria

A user should be able to:
1. Register a website.
2. Add pages.
3. Schedule scans.
4. Trigger scans manually.
5. Trigger scans from Bitbucket deployment.
6. View Lighthouse metrics.
7. View historical trends.
8. Detect performance regressions over time. *(Currently a Future Feature — no design committed yet. Success criteria and feature list are out of sync on this point; close the gap before calling MVP done.)*

The application should remain lightweight, maintainable, and easy to extend.
