# Commerce Health Check — Magento 2 Health Suite

**One-line pitch:** Stop manually checking cron, Redis, OpenSearch, queues, and indexers — run `bin/magento commerce:health-check` or open **System → Commerce Health** for instant alerts and a health score.

## What is this?

**Commerce Health Check** (`Amaresh_CommerceHealthCheck`) is a Magento 2 module that runs seven infrastructure checks from the CLI and admin panel. Built for agencies and platform teams who maintain Magento projects and need fast, repeatable health checks with optional email and Slack alerts.

## Features

### Phase 1 — CLI Health Check

- **Database health** — `SELECT 1` connectivity probe
- **Redis health** — cache save/load via `CacheInterface`
- **OpenSearch health** — `GET /_cluster/health` (green/yellow = healthy, red = fail)
- **Cron health** — recent `cron_schedule` activity (15-minute window)
- **Queue consumer health** — `consumers_runner` cron, process list, stuck message detection
- **Indexer health** — all indexers must be `valid`
- **Disk monitoring** — free space on Magento root (`<10 GB` warning, `<5 GB` critical)
- **Integration health** — HTTP health checks for payment/shipping/ERP endpoints (admin-configured)
- **Health score** — 100 base points, −10 per failed check

### Phase 2 — Admin UI

- **System → Commerce Health** native UI Component admin grid
- Component status table: Database, Redis, OpenSearch, Cron, Queue Consumers, Indexers, Disk, Integrations
- Live health score with **Run Health Check** refresh button

### Phase 3 — Enterprise Alerts

- **Email alerts** — subjects: **Redis Down**, **Cron Stopped**, **Disk Full**, **Integration Failed**
- **Slack alerts** — post to `#commerce-alerts` (configurable channel) via incoming webhook
- **Scheduled monitoring** — Magento cron every 15 minutes
- **Alert deduplication** — alerts only when failure state changes (no spam)

## Why use this vs alternatives?

| Approach | Pros | Cons |
|----------|------|------|
| **Commerce Health Check (this)** | CLI + admin + alerts, Magento-native | Not a full APM platform |
| Manual SSH checks | Full control | Slow, inconsistent, not repeatable |
| Generic monitoring (Nagios, Datadog) | History, dashboards | Extra setup; not Magento-specific |
| `bin/magento indexer:status` only | Built-in | Single concern only |

## Installation

**Option A — Copy to Magento (quickest)**

```bash
git clone git@github.com:amareshtiwari/CommerceHealthCheck.git
cp -r CommerceHealthCheck /path/to/magento/app/code/Amaresh/CommerceHealthCheck
bin/magento module:enable Amaresh_CommerceHealthCheck
bin/magento setup:upgrade
bin/magento cache:flush
```

**Option B — Composer VCS**

Add to your Magento root `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/amareshtiwari/CommerceHealthCheck.git"
    }
  ]
}
```

Then:

```bash
composer require amareshtiwari/commerce-health-check
bin/magento module:enable Amaresh_CommerceHealthCheck
bin/magento setup:upgrade
bin/magento cache:flush
```

## Usage

### CLI

```bash
bin/magento commerce:health-check
```

Example output:

```text
------------------------------------------------
Commerce Health Check
------------------------------------------------

✓ Database Connected

✓ Redis Connected

✓ OpenSearch Healthy

✓ Cron Running

✓ Queue Consumers Running

✓ All Indexers Valid

✓ Disk Space Healthy

------------------------------------------------
Health Score : 100%
------------------------------------------------
```

Exit code `0` when score is 100%; `1` when any check fails.

### Admin UI

1. Log in to Magento Admin
2. Go to **System → Commerce Health**
3. Review component status and health score
4. Click **Run Health Check** to refresh

### Configuration

**Stores → Configuration → Commerce Health → Commerce Health Check**

| Group | Setting | Required? | Default |
|-------|---------|-----------|---------|
| **General** | **Enable Commerce Health Check** | No — master on/off switch | **Enabled** |
| **General** | Enable Scheduled Alerts | No — only if you want cron alerts | Disabled |
| **General** | Alert When These Checks Fail | Only if alerts enabled | Redis, Cron, Disk |
| **Check Thresholds** | Cron Lookback (minutes) | No | 15 |
| **Check Thresholds** | Disk Warning Threshold (GB) | No | 10 |
| **Check Thresholds** | Disk Critical Threshold (GB) | No | 5 |
| **Email Alerts** | Enable + recipient | Only if you want email | Disabled |
| **Slack Alerts** | Enable + webhook + channel | Only if you want Slack | Disabled |

CLI (`commerce:health-check`) and **System → Commerce Health** work out of the box with defaults — no configuration required.

Ensure Magento cron is running:

```bash
bin/magento cron:run
```

## Module structure

```text
Amaresh/CommerceHealthCheck/
├── Api/CheckerInterface.php
├── Console/Command/HealthCheck.php
├── Controller/Adminhtml/Health/Index.php
├── Block/Adminhtml/Health/Report.php
├── Cron/HealthAlertCron.php
├── Model/
│   ├── CheckerPool.php
│   ├── DatabaseCheck.php … DiskCheck.php
│   └── Alert/EmailNotifier.php, SlackNotifier.php, AlertManager.php
├── view/adminhtml/templates/health/report.phtml
└── etc/adminhtml/menu.xml, system.xml, crontab.xml
```

## Requirements

- Magento 2.4+ (OpenSearch / Elasticsearch search engine support)
- PHP 8.1+
- Redis configured as Magento cache/session backend (for Redis check)
- Magento cron enabled (for scheduled alerts)

## FAQ

**Where is the admin grid?**  
**System → Commerce Health** in the Magento admin sidebar.

**How do Slack alerts work?**  
Create an incoming webhook in Slack, paste the URL in config, set channel to `#commerce-alerts`.

**Will I get alert spam?**  
No. Alerts fire when the failure set changes (e.g. Redis goes down), not every 15 minutes.

**Can I add custom checks?**  
Implement `CheckerInterface`, register in `CheckerPool`, and add to `AlertChecks` source if needed.

## Keywords & topics

Magento health check, Magento cron monitor, Magento Redis check, OpenSearch health, Magento queue consumer, Magento indexer status, Magento disk space, `commerce:health-check`, Magento admin health grid, Slack commerce alerts

Suggested GitHub topics: `magento2`, `magento2-module`, `health-check`, `redis`, `opensearch`, `cron`, `slack`, `admin-grid`

## What this is NOT

- Not a SaaS monitoring platform or APM
- No Docker or Kubernetes deployment
- No multi-tenant SaaS model
- No automatic remediation (restart consumers, reindex)
- No historical trending dashboard (snapshot only)

## Author

**Amaresh Tiwari** — https://github.com/amareshtiwari/CommerceHealthCheck
