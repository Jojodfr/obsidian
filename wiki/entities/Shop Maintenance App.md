---
type: entity
title: "Shop Maintenance App"
created: 2026-06-03
updated: 2026-06-03
tags:
  - entity
  - product
  - hermes
  - ticketing
status: current
entity_type: product
role: "Internal maintenance ticketing system for Hermes boutiques"
first_mentioned: "[[shop-maintenance-app-repo]]"
related:
  - "[[Hermes International]]"
  - "[[shop-maintenance-app-repo]]"
  - "[[Ticketing System Architecture]]"
  - "[[Confie]]"
  - "[[Kellymorphose]]"
  - "[[8 Penthièvre]]"
sources:
  - "[[shop-maintenance-app-repo]]"
---

# Shop Maintenance App

Internal web application for managing maintenance tickets across Hermes boutiques worldwide. Store staff report maintenance issues, vendors are assigned for resolution, and HCT (Hermes Corporate Team) oversees operations.

**Type**: Product | **Owner**: Hermes International | **Status**: Production

---

## Technology Stack

### Backend

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Symfony | 4.4 |
| API | API Platform | 2.5.8 |
| PHP | PHP | 7.4 |
| Database | MySQL | 5.7 |
| ORM | Doctrine | 2.x |
| Auth | Lexik JWT | 2.9.0 |
| Testing | PHPUnit | — |
| Code quality | PHP-CS-Fixer, PHPStan, Rector | — |

### Frontend

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Angular | 17.3.12 |
| Language | TypeScript | 5.2 |
| State mgmt | NGXS | 18.1.1 |
| UI library | Angular Material | 17.3.10 |
| Testing | Jest | 29.7.0 |
| Build | Angular CLI | 17.3.9 |
| Package mgr | Yarn Classic | 1.22.22 |
| Node | Node.js | 18.20.4 |

### Infrastructure

| Component | Technology |
|-----------|-----------|
| Container registry | Google Artifact Registry (`europe-west9`) |
| Integration hosting | GCP VM (Niji) |
| Production hosting | Client-mirrored (Hermes) |
| CI/CD | GitLab CI |
| Security scanning | Trivy, SonarQube |
| Local dev | Docker Compose |

---

## Key Features

- **Ticket management** — Create, assign, track maintenance requests with unique IDs (`DDMMYY-NNN` format)
- **Multi-role access** — ADMINISTRATOR, HCT, VENDOR, STAFF with fine-grained permissions
- **Vendor management** — Vendors with 4 contact levels, assigned to stores and tickets
- **Store layouts** — Visual layout associations for ticket location context
- **Media uploads** — Attach quotations, zoom-in/zoom-out photos to tickets
- **Workflow tracking** — Three-stage completion (vendor → store → HCT) with date validation
- **Audit trail** — Full change log on every ticket modification
- **Multi-language** — 8 translation files (AR, EN, FR, IT, JP, KR, ZH, ZZ); only FR, EN, JP enabled in `list_languages.json`
- **Notifications** — Per-user notification preferences by type
- **MEO integration** — ID tracking for external system integration

---

## User Roles

| Role | Permissions |
|------|-------------|
| **ADMINISTRATOR** | Full access to all resources |
| **HCT** | Ticket oversight, vendor management, store management |
| **VENDOR** | Ticket resolution, working date updates, media uploads |
| **STAFF** | Ticket creation, store-level viewing |

---

## Data Model (Simplified)

```
User ──┬──> Role
       ├──> Store (many-to-many)
       ├──> Country (many-to-many)
       ├──> Ticket (as creator)
       ├──> Vendor (as contact 1-4)
       └──> NotificationChoice

Store ──┬──> Country
        ├──> StoreLayout
        ├──> Ticket
        └──> Vendor (many-to-many)

Ticket ──┬──> Store
         ├──> User (creator)
         ├──> SubCategory ──> Category
         ├──> SubArea ──> Area
         ├──> Vendor (assigned)
         ├──> TicketStatus
         ├──> TicketComment
         ├──> TicketChangeLog
         ├──> TicketLayout
         ├──> MediaObject (quotation, zoom-in, zoom-out)
         ├──> UserRequest
         └──> UserFeedback
```

---

## Environments

| Environment | URL | Managed By |
|---|---|---|
| Integration | `symfony-integ.nf2500797.niji.cloud` | Niji (GCP VM) |
| Preprod | `tst-shopmaintenance.hermes.com` | Hermes |
| Production | `shopmaintenance.hermes.com` | Hermes |

---

## Related Systems

- **Hermes Login Portal** (`fed.hermes.com`) — Cognito-based SSO
- **Passbolt Hades** — Credential management for dev accounts
- **Oracle HCM** — User data source (indirect, via Hermes IAM)

---

## Context Within Hermes Ecosystem

Shop Maintenance App is one of several internal web applications maintained by Hermes/Niji:

- [[HB8 App]] — Nuxt.js static app for Haute Bijouterie (high jewelry)
- [[Kellymorphose]] — Vue.js SPA for Kelly bag customization
- [[Confie]] — Symfony 7.2 app for internal services
- [[8 Penthièvre]] — Static site for building information
- **Shop Maintenance** — Symfony 4.4 + Angular 17 ticketing system (this app)

All share the same CI/CD template library (`hermesintl/template-cicd`) and security stack (GitGuardian, SonarQube, HashiCorp Vault).
