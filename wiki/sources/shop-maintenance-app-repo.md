---
type: source
title: "Shop Maintenance App Repository"
created: 2026-06-03
updated: 2026-06-03
tags:
  - source
  - codebase
  - hermes
  - gitlab
status: mature
related:
  - "[[Shop Maintenance App]]"
  - "[[Hermes International]]"
  - "[[Ticketing System Architecture]]"
  - "[[Symfony ECS Deployment]]"
sources:
  - "[[.raw/shop-maintenance-app.md]]"
---

# Shop Maintenance App Repository

Internal ticketing web application for Hermes boutiques worldwide. Three-repo monorepo structure: Symfony 4.4 API backend, Angular 17 SPA frontend, and dedicated internationalization package.

**Source**: `.raw/shop-maintenance-app.md` | **GitLab**: `hermesintl/applications-departementales/shop-maintenance-app` | **Ingested**: 2026-06-03

---

## Overview

Shop Maintenance is Hermes' internal ticketing system for managing maintenance requests across all global boutiques. Store staff create tickets for maintenance issues, vendors are assigned to resolve them, and HCT (Hermes Corporate Team) oversees the workflow. The application supports multi-language (8 languages), role-based access control, and media attachments.

---

## Architecture

### Three-Repo Structure

```
shop-maintenance-app/
├── shop-maintenance-back/     # Symfony 4.4 API (PHP 7.4)
├── shop-maintenance-front/    # Angular 17 SPA (TypeScript)
└── shop-maintenance-intl/     # Translation files (8 languages)
```

### Backend (shop-maintenance-back)

- **Framework**: Symfony 4.4 with API Platform 2.5.8
- **PHP**: 7.4
- **Database**: MySQL 5.7 via Doctrine ORM
- **Auth**: JWT (LexikJWTAuthenticationBundle 2.9.0)
- **API**: REST with API Platform annotations, OpenAPI/Swagger docs
- **Files**: VichUploaderBundle for media storage
- **Email**: Symfony Mailer with Twig templates
- **Dev stack**: PHP-CS-Fixer, PHPStan, Rector, PHPUnit, GrumPHP
- **Docker**: docker-compose (nginx, php-fpm, mysql, mailhog, phpMyAdmin)

Key bundles: AWS SDK, Firebase JWT, Guzzle, KnpGaufrette, Eluceo iCal, Gedmo Doctrine Extensions, Nelmio CORS.

### Frontend (shop-maintenance-front)

- **Framework**: Angular 17.3.12 with TypeScript 5.2
- **State**: NGXS 18.1.1 with storage plugin
- **UI**: Angular Material 17.3.10, custom SCSS theme
- **Testing**: Jest 29.7.0 (migrated from Jasmine/Karma)
- **Build**: Angular CLI 17.3.9, ng-packagr for library builds
- **Package manager**: Yarn Classic 1.22.22
- **Node**: 18.20.4
- **Components library**: `projects/components-library/` — reusable UI components

### Internationalization (shop-maintenance-intl)

- **Standalone repo** for translation files, versioned independently (currently v0.2.3)
- **6 configured locales** in `list_languages.json`: FR (enabled), EN (enabled), IT (disabled), JP (enabled), ZZ (disabled), ZH (disabled)
- **8 translation files** present: `ar.json`, `en.json`, `fr.json`, `it.json`, `jp.json`, `kr.json`, `zh.json`, `zz.json` (AR and KR exist but are not registered in the language list)
- **ISO 639** language code standard
- **JSON structure**: each file has `FLAG` (emoji), `IS_AVAILABLE` (boolean), `NAME` (display name)
- **CI/CD**: GitLab CI with S3 deployment via `aws s3 sync intl $BUCKET/intl`
- **8 pipeline stages**: analysis → deploy_dev → deploy_tst → deploy_chk → release → deploy_ppd → deploy_acc → deploy_prd
- **Only TST and PRD enabled** in current config
- **Security**: HashiCorp Vault for AWS credentials, Gitleaks secret scanning, SonarQube analysis
- **Hermes template library**: `template-cicd/cicd-sonarqube`, `cicd-gitleaks`, `cicd-aws-buckets3`, `cicd-release`

---

## Domain Model

### Core Entities

- **Ticket** — central entity with unique ID (`DDMMYY-NNN`), workflow status, description, media attachments (quotation, zoom photos), vendor assignment, date tracking (working period, finished dates for vendor/store/HCT), urgency flag, security flag, MEO ID tracking
- **Store** — boutique with code, name, address, contact info, country, open/close status, store layouts
- **User** — staff member with AD login, role, stores, countries, notification preferences, vendor contact assignments
- **Vendor** — maintenance vendor with up to 4 contact levels, assigned stores, tickets
- **Area / SubArea** — physical locations within a store
- **Category / SubCategory** — ticket categorization hierarchy
- **TicketStatus** — workflow states with ordering
- **TicketComment** — threaded discussion on tickets
- **TicketChangeLog** — audit trail of all modifications
- **MediaObject** — uploaded files (quotations, photos)
- **Role** — RBAC: ADMINISTRATOR, HCT, VENDOR, STAFF

### Security Model

- API Platform security annotations per operation
- Custom Symfony voters for fine-grained access (STORE_*, TICKET_*, USER_* permissions)
- JWT authentication with role claims
- Soft-delete pattern via `isHide` flag + unique active entity validation
- Closed tickets are immutable (edit blocked by security expression)

---

## Authentication

- **Frontend**: Hermes login portal (`fed.hermes.com`) via Amazon Cognito
- **Backend**: JWT tokens (Lexik bundle), OAuth2 client credentials flow
- **Local dev**: `localhost:4200` whitelisted for Cognito redirect
- **Password management**: Handled by Hermes/Cognito (expiry, reset)

---

## Environments

| Environment | Branch | Backend URL | Frontend URL | Deployment |
|---|---|---|---|---|
| Recette Niji | `develop` (back: `integ`) | `symfony-integ.nf2500797.niji.cloud` | `localhost:4200` | GCP VM via GitLab CI |
| Preprod | `stage` | mirrored | `tst-shopmaintenance.hermes.com` | Client mirroring |
| Production | `master` | mirrored | `shopmaintenance.hermes.com` | Client mirroring |

Note: Backend uses `integ` branch for Niji integration environment, not `develop`.

---

## CI/CD

### Backend Pipeline

Stages: pre-gates → quality-security-gates → package → security-check-image → generate-certificates → deploy → audit

- PHP 7.4 container builds
- Docker image push to `europe-west9-docker.pkg.dev`
- Deployment to `/etc/project/shop-maintenance-back/<version>`

### Frontend Pipeline

Stages: dependencies → build-and-test → quality-security-gates → package → security-check-image → deploy → deploy-release → drop-release → expose-release → audit

- Node 20 Alpine container
- Yarn cache optimization
- Jest tests (full coverage on main, affected on feature branches)
- Trivy filesystem + image security scans
- SonarQube analysis
- Docker image build and deploy

### Shared Infrastructure

- GitLab CI component library for security checks (Trivy)
- SonarQube integration
- Hermes-specific CI templates (`.gitlab/ci/*.yml`)
- Docker Compose for local development with Hermes overrides

---

## Development Workflow

- **Commits**: Conventional commits with Husky + Commitlint
- **Branches**: `develop` → `stage` → `master`, feature branches `feat/*`
- **Formatting**: Prettier (front), PHP-CS-Fixer (back)
- **Quality**: PHPStan, Rector, TSLint (legacy), GrumPHP pre-commit hooks

---

## Key Insights

1. **Mature Symfony 4.4 codebase** — Uses API Platform extensively with annotation-driven config, not YAML/XML. Heavy use of custom filters, validators, and voters.

2. **Complex ticket workflow** — Three-stage finish validation (vendor → store → HCT) with date constraints preventing future dates and enforcing chronological order.

3. **Multi-contact vendor model** — Vendors support up to 4 contact levels (main/secondary/third/fourth), all linked to User entities. This enables escalation chains.

4. **NGXS over NgRx** — Frontend uses NGXS for state management (simpler API than NgRx), with devtools plugin and storage persistence.

5. **Jest migration** — Frontend replaced Jasmine/Karma with Jest 29, using `jest-preset-angular` and `@angular-builders/jest`.

6. **Separate intl repo** — Translations live in their own repo with independent CI/CD, allowing content updates without app redeployment.

7. **Niji + client mirroring** — Dual deployment model: Niji manages integration environment on GCP; client (Hermes) mirrors preprod and production internally.
