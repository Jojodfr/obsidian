---
type: entity
title: "Uplift Coach"
created: 2026-06-03
updated: 2026-06-03T21:00:00
tags:
  - entity
  - product
  - fitness
  - saas
status: developing
related:
  - "[[coach-app-repo]]"
  - "[[Serverless SaaS Architecture]]"
  - "[[DynamoDB Single-Table Pattern]]"
  - "[[Stripe Billing Integration]]"
sources:
  - "[[.raw/coach-app.md]]"
entity_type: product
role: "Fitness coaching SaaS platform"
first_mentioned: "[[coach-app-repo]]"
---

# Uplift Coach

SaaS de coaching fitness — PWA multi-tenant (coach / alumni / admin).

**Production**: [app.upliftcoach.fr](https://app.upliftcoach.fr)

---

## What It Is

Uplift Coach is a French fitness coaching platform. Coaches create workout programs and meal plans; students track workouts, nutrition, and body measurements. Multi-tenant by organization — a coach can invite other coaches to their organization.

**Status**: Production (April 2026)
**Stack**: React 19 PWA + Python 3.13 Lambda + DynamoDB + Terraform on AWS

---

## Core Features

### For Coaches
- Dashboard with stats & charts
- Program & session builder (3950-exercise library)
- Client management (email invitations)
- Meal plans & recipes
- Weekly feedback reports ("bilans")
- Billing management (Stripe portal)
- Multi-coach organization support

### For Students (Alumni)
- Workout tracker with reps/sets/weight logging
- Nutrition tracker with meal logging
- Body measurement tracking
- Progress charts
- Weekly self-assessment ("bilans")

### For Admins
- Global user management
- Organization oversight
- Global dashboard

---

## Architecture

```
React 19 (Vite + Tailwind 4 + Zustand)
    │
    ▼
API Gateway HTTP v2
    │
    ├── JWT Authorizer Lambda
    │
    ▼
Main Lambda (Python 3.13 — ~4800 lines)
    │
    ├── DynamoDB single-table (PK/SK + GSIs)
    ├── S3 (images)
    ├── Stripe (billing)
    └── SES (email)
```

Infrastructure as Code: **Terraform** manages all AWS resources.

---

## Billing Model

Stripe-integrated subscription tiers:

| Plan | Student Limit | Target |
|------|---------------|--------|
| Free | 3 | Individual coaches trying the platform |
| Starter | 15 | Small coaching practices |
| Pro | 50 | Medium gyms/studios |
| Business | unlimited | Large organizations |

---

## Design

- Dark mode OLED-first
- Accent: `#C8FF00` (Electric Lime) — rebrandable via `--color-primary` in `App.css`
- Secondary accent: `#D4A017` (Brutal Gold)
- Typography: Teko (headings), Montserrat (body)
- Custom utility classes: `up-*` prefix in `App.css` (up-btn, up-input)
- Tailwind 4 with `@theme` CSS variables (no Tailwind config file — theming in CSS)
- PWA features: installable, offline detection, push notifications, service worker, lazy-loaded routes

---

## Deployment

Single-command deployment via `./deploy.sh`:
- Backend: Terraform apply → Lambda zip upload
- Frontend: Vite build → S3 sync → CloudFront invalidation
- Tests: pytest (backend) + Playwright (e2e)

AWS resource naming convention: `app-uplift-prd-*` (app-owned resources) vs `cto-uplift-prd-*` (marketing site).

---

## Related

- [[coach-app-repo]] — full source-level documentation
- [[Serverless SaaS Architecture]] — architecture pattern distilled from this build
- [[DynamoDB Single-Table Pattern]] — database design pattern used throughout
- [[Stripe Billing Integration]] — billing implementation details
