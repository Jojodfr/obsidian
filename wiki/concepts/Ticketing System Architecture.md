---
type: concept
title: "Ticketing System Architecture"
created: 2026-06-03
updated: 2026-06-03
tags:
  - concept
  - architecture
  - symfony
  - angular
  - api-platform
  - ticketing
status: developing
complexity: intermediate
domain: software-architecture
aliases:
  - "Maintenance Ticketing Pattern"
related:
  - "[[Shop Maintenance App]]"
  - "[[shop-maintenance-app-repo]]"
  - "[[Symfony ECS Deployment]]"
  - "[[Serverless SaaS Architecture]]"
sources:
  - "[[shop-maintenance-app-repo]]"
---

# Ticketing System Architecture

A pattern for internal maintenance/work-order ticketing systems, exemplified by the Hermes Shop Maintenance App. Separates concerns into API backend, SPA frontend, and standalone internationalization.

**Status**: Developing | **Complexity**: Intermediate

---

## Pattern Structure

```
Ticketing System
├── Backend API          # REST API with domain-driven entities
│   ├── Core entity: Ticket (with workflow state machine)
│   ├── Supporting: User, Store, Vendor, Category, Area
│   ├── Media: File attachments (quotations, photos)
│   ├── Audit: Change logs, comments
│   └── Auth: JWT + RBAC with custom voters
├── Frontend SPA         # Angular/Vue/React consuming API
│   ├── State management (NGXS/NgRx/Redux)
│   ├── Role-based UI (show/hide based on permissions)
│   └── Multi-language (i18n via ngx-translate or similar)
└── Internationalization # Separate translation delivery
    └── JSON files per locale, deployed independently
```

---

## Backend Patterns

### API-First with API Platform (Symfony)

- Annotation-driven resource configuration (`@ApiResource`, `@ApiFilter`)
- Auto-generated OpenAPI/Swagger documentation
- Doctrine ORM with repository pattern
- Custom filters for complex search (sort, boolean, date range, exists)
- Validation groups per operation (`postValidation`, `patchValidation`, `putValidation`)

### Security Model

- **JWT authentication** — Stateless token-based auth
- **Role hierarchy** — `ROLE_ADMINISTRATOR` > `ROLE_HCT` > `ROLE_VENDOR` > `ROLE_STAFF`
- **Custom voters** — Fine-grained object-level permissions (e.g., `TICKET_EDIT` checks ownership + status)
- **Workflow enforcement** — Security expressions prevent edits to closed tickets
- **Soft delete** — `isHide` flag instead of hard deletion, with unique active constraints

### Domain-Driven Entities

- **Ticket** as aggregate root with rich validation
- **Unique ID generation** — Format `DDMMYY-NNN` with regex validation
- **Date constraints** — Expression-based validation preventing future dates and enforcing chronological ordering
- **Media attachments** — Many-to-one to MediaObject with cascade persist
- **Audit trail** — Separate TicketChangeLog entity capturing who changed what and when

### Three-Stage Completion Workflow

```
Ticket Created
    ↓
Vendor Assigned → Vendor sets working dates → Vendor marks finished
    ↓
Store confirms finished
    ↓
HCT confirms finished
    ↓
Ticket CLOSED (immutable)
```

Each stage has date validation rules:
- Finished dates cannot be in the future
- Store finish ≥ Vendor finish
- HCT finish ≥ Store finish

---

## Frontend Patterns

### State Management with NGXS

- Centralized stores for auth, user habilitations, application state
- Storage plugin for session persistence
- Devtools integration for debugging

### Role-Based UI

- API returns user's role; frontend gates UI elements
- Same API endpoints filtered by backend security — frontend is defense in depth, not sole security layer

### Component Library

- Shared component library (Angular library project)
- Reusable UI components consumed by feature modules
- Independent versioning and build

---

## Internationalization Strategy

- **Separate repo** for translations — decouples content updates from app releases
- **JSON format** — Key-value pairs per locale
- **Pseudo-localization** (`zz` locale) — For testing UI layout with longer strings
- **Runtime loading** — Frontend loads translation files at runtime via HTTP loader

---

## Deployment Patterns

### Dual-Deployment Model

| Environment | Managed By | Infrastructure |
|---|---|---|
| Integration | Development team (Niji) | GCP VM, direct CI/CD |
| Staging/Production | Client (Hermes) | Mirrored from integration, internal infrastructure |

This pattern is common in client-vendor relationships where the vendor manages dev/integration, and the client controls production.

### Container-Based CI/CD

- Docker images built per commit
- Security scanning (Trivy) before deployment
- SonarQube quality gates
- Artifact Registry for image storage

---

## Comparison with Related Patterns

| Pattern | Backend | Frontend | Auth | Best For |
|---|---|---|---|---|
| **Ticketing System** | Symfony + API Platform | Angular + NGXS | JWT + Cognito | Internal maintenance, work orders |
| [[Symfony ECS Deployment]] | Symfony 7.2 | — | Apache mod_auth_openidc | Server-rendered internal apps |
| [[Serverless SaaS Architecture]] | Python Lambda | React PWA | JWT custom authorizer | Multi-tenant SaaS, rapid scaling |
| [[Nuxt.js Static Deployment]] | — (static) | Nuxt.js | JWT client-side | Content-heavy public sites |
| [[Vue.js SPA Static Deployment]] | — (static) | Vue.js | OAuth2/SSO | Simple SPAs on S3 |

---

## When to Use

This architecture fits when:
- Internal users need structured workflow management
- Multi-role access with complex permissions
- File/media attachments are required
- Audit trail and change tracking are mandatory
- Multi-language support needed
- Client-managed production environment

---

## Anti-Patterns to Avoid

- **Frontend-only security** — Always validate permissions server-side; frontend gating is UX, not security
- **Hard deletes** — Use soft delete (`isHide`) for data integrity and audit compliance
- **Monolithic i18n** — Separating translations enables content team autonomy
- **Skipping integration tests** — Workflow state machines have many edge cases
