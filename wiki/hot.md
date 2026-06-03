---
type: meta
title: "Hot Cache"
updated: 2026-06-03T20:00:00
tags:
  - meta
  - hot-cache
status: evergreen
related:
  - "[[index]]"
  - "[[log]]"
  - "[[overview]]"
  - "[[hb8-app-repo]]"
  - "[[coach-app-repo]]"
  - "[[Uplift Coach]]"
  - "[[Serverless SaaS Architecture]]"
  - "[[DragonScale Memory]]"
---

# Recent Context

Navigation: [[index]] | [[log]] | [[overview]]

## Last Updated

2026-06-03: **Ingested Shop Maintenance App Repository** (`~/work/shop-mnt`). Hermes internal ticketing system for boutique maintenance. Symfony 4.4 API + Angular 17 SPA + API Platform. 4 new wiki pages created.

Previous: 2026-06-03 — ingested coach-app Repository (`~/work/coach-app`). Uplift Coach — fitness coaching SaaS. 3 new wiki pages created.

## Key Recent Facts

### Uplift Coach (coach-app)
- [[coach-app-repo]] — fitness coaching SaaS; production at [app.upliftcoach.fr](https://app.upliftcoach.fr)
- React 19 PWA (Vite + Tailwind 4 + Zustand) + Python 3.13 Lambda monolith + DynamoDB single-table
- Three roles: **coach** (program builder, client management, billing), **alumni** (workout tracker, nutrition, body measurements), **admin** (global oversight)
- **Stripe billing** — 4 tiers: free (3 students), starter (15), pro (50), business (unlimited)
- **DynamoDB** — main table with PK/SK + `CoachIdIndex` + `OrgIdIndex`; 5 aux tables; `EMAIL#` reverse lookup for fast login; program visibility (private/org/assigned/example)
- **CloudFront CSP** — HSTS 2yr preload, X-Frame-Options DENY, strict CSP with GA + Fonts + Lyfta (video) + S3 image origins
- **PWA features** — service worker, install prompt, push notifications, offline detection, lazy-loaded routes
- **Design tokens** — Tailwind 4 `@theme` in CSS (no config file); accent `#C8FF00` Electric Lime; heading font Teko (not Bebas Neue as README claims)
- **JWT auth** — separate Lambda authorizer validates tokens at API Gateway level; token blocklist for logout/suspend
- **`deploy.sh`** — single bash script orchestrates test → terraform → build → S3 deploy → CloudFront invalidate
- **AWS naming** — `app-uplift-prd-*` for app resources; `cto-uplift-prd-*` for marketing site
- 3950-exercise library; weekly "bilan" feedback reports; meal plans & recipes
- **Security** — OWASP A09 90-day logs, security audit table, rate limiting, explicit CORS origins, `_DUMMY_HASH` timing attack mitigation on login, token blocklist with jti + TTL
- **Stripe billing** — €39 Starter / €79 Pro / €149 Business monthly; annual discounts ~15%; 14-day trial on upgrade; webhook links subscription on checkout completion

### Shop Maintenance App (shop-mnt)
- [[shop-maintenance-app-repo]] — Hermes internal maintenance ticketing system for boutiques worldwide
- **Backend**: Symfony 4.4 + API Platform 2.5.8 + PHP 7.4 + MySQL 5.7 + JWT (LexikJWTAuthenticationBundle)
- **Frontend**: Angular 17.3.12 + TypeScript 5.2 + NGXS 18.1.1 + Angular Material + Jest 29.7.0
- **Domain**: Ticket (unique ID `DDMMYY-NNN`), Store, User, Vendor, Area/SubArea, Category/SubCategory
- **Workflow**: Three-stage completion (vendor → store → HCT) with date validation; closed tickets are immutable
- **Auth**: Hermes SSO (`fed.hermes.com`) via Amazon Cognito + JWT backend tokens
- **Vendor model**: Up to 4 contact levels per vendor (main/secondary/third/fourth)
- **Multi-language**: 8 translation files in separate `shop-maintenance-intl` repo (AR, EN, FR, IT, JP, KR, ZH, ZZ); only FR, EN, JP enabled in `list_languages.json`
- **CI/CD**: GitLab CI with Trivy security scans, SonarQube, Docker builds to Google Artifact Registry
- **Environments**: Integration (Niji GCP VM), Preprod (client mirror `tst-shopmaintenance.hermes.com`), Production (client mirror `shopmaintenance.hermes.com`)
- **Dev tools**: PHP-CS-Fixer, PHPStan, Rector, Prettier, Husky + Commitlint
- **Docker**: docker-compose with nginx, php-fpm, mysql, mailhog, phpMyAdmin
- **Components library**: Angular library project (`projects/components-library/`) for reusable UI
- **Intl CI/CD**: Separate pipeline deploying to S3 via `aws s3 sync`; HashiCorp Vault for AWS secrets; 8 stages (analysis → deploy_dev → deploy_tst → deploy_chk → release → deploy_ppd → deploy_acc → deploy_prd); only TST and PRD enabled

### Hermes Codebases (GitLab repos)
- [[hb8-app-repo]] is a built Nuxt.js SPA for Hermes **Haute Bijouterie 8** (high jewelry)
- [[klm-repo]] is a Vue.js SPA for **Kellymorphose** (compiled bundles only, no source in repo)
- [[cnf-repo]] is a Symfony 7.2 PHP app for **Confie** (full source, AWS ECS Fargate)
- [[livret-penthievre-sources]] is a vanilla HTML/JS static site for **8 Penthièvre** building (AWS S3)
- All four use the same Hermes CI/CD template library (`hermesintl/template-cicd`)
- HB8/KLM have **dual deployment paths**: standard (JFrog → S3) and custom (CIFS → S3)
- CNF deploys to **AWS ECS Fargate** via Kaniko container builds
- 8 Penthièvre deploys via simple `aws s3 sync` (no build step)
- HB8 uses JWT client-side auth; KLM uses Hermes SSO/OAuth2; CNF uses Apache mod_auth_openidc
- HB8 has 5 environments; KLM and CNF have 2 (CHK/PRD); 8 Penthièvre has 2 (DEV, PRD)
- Security stack shared: GitGuardian, SonarQube, HashiCorp Vault, JFrog Artifactory, AWS OIDC

### ClearID Integrations (CIFS directory)
- [[ClearID]] is Genetec's physical access control platform; Hermes account `j3gg5ror3f`
- **MCH → ClearID**: Contract dates sync (daily 04:00 cron) + photo sync from Oracle HCM
- **SMI ↔ ClearID**: Access rights, identities, locations, schedules; bidirectional sync
- **IAM ↔ ClearID**: Identity mapping with Excel-based location mappings
- **ClearID → SMI**: SST (temporary worker) export via PowerShell/PHP
- **Symfony API**: `clearidapi` — Symfony 5.4 dev app, PostgreSQL, potential future API layer
- All PHP scripts use OAuth2 client_credentials against `*.eu.clearid.io` endpoints
- MCH API: Oracle HCM REST (`fa-eoic-saasfaprod1.fa.ocs.oraclecloud.com`) with Basic Auth
- Scripts deploy to both Windows (Task Scheduler) and Linux (cron + Apache)

## Recent Changes

- Created: [[shop-maintenance-app-repo]], [[Shop Maintenance App]], [[Ticketing System Architecture]]
- Updated: [[index]] (62 pages, 9 sources), [[hot]], [[log]], [[entities/_index]], [[sources/_index]], [[concepts/_index]], [[Hermes International]]
- Raw source filed: `.raw/shop-maintenance-app.md`
- Previous: [[coach-app-repo]], [[Uplift Coach]], [[Serverless SaaS Architecture]]

## Active Threads

- Vault was just opened after a hiatus; stale content from v1.7.1 and earlier still present
- Plugin version in hot cache corrected to 1.9.2
- Recent ingests: Hermes codebases (HB8, KLM, CNF, 8 Penthièvre, ClearID, Shop Maintenance) + coach-app fitness SaaS
- Shop Maintenance App is a mature internal ticketing system — possible follow-up: API Platform custom filters, Symfony voters, Angular NGXS state patterns, or ticket workflow state machine
- Coach-app is a live production SaaS — possible follow-up: deep dives into Lambda handlers, DynamoDB access patterns, or Stripe integration
- Plugin version in hot cache is now accurate (1.9.2)

## Plugin State (Reference)

- **Version**: 1.9.2 (repo state; hot cache previously claimed 1.7.1)
- **Skills**: 15 including wiki, wiki-ingest, wiki-query, wiki-lint, wiki-cli, wiki-retrieve, wiki-mode, save, autoresearch, canvas, defuddle, obsidian-bases, obsidian-markdown, think
- **Tests**: `make test` runs 7 suites green
- **Hooks**: SessionStart, PostCompact, PostToolUse, Stop
