---
source_repo: ~/work/coach-app
ingested: 2026-06-03
type: source
category: codebase
---

# Uplift Coach — Fitness Coaching SaaS

SaaS de coaching fitness — PWA multi-tenant (coach / alumni / admin).

**Production**: [app.upliftcoach.fr](https://app.upliftcoach.fr)

---

## Architecture

```
frontend/ (React 19 + Vite + Tailwind 4)
    │
    └── API Gateway HTTP
            │
            └── Lambda (Python 3.13)
                    │
                    ├── DynamoDB (single-table)
                    ├── S3 (images) + CloudFront (CDN)
                    ├── Stripe (billing)
                    └── SES (emails)

terraform/         → IaC (tout)
```

---

## Structure

```
coach/
├── frontend/                  # Frontend actif (React 19 + Vite)
│   ├── src/
│   │   ├── App.jsx            # Router + auth guards
│   │   ├── App.css            # Design system (tokens + classes up-*)
│   │   ├── ui/                # Composants atomiques (Card, Button, Badge, Input)
│   │   ├── components/        # Composants métier
│   │   ├── pages/             # Pages par rôle (coach/, alumni/, admin/, auth/, onboarding/)
│   │   ├── store/             # Zustand stores (auth, ui)
│   │   └── lib/               # API client, utils
│   └── package.json
│
├── terraform/
│   ├── lambda/
│   │   └── lambda_function.py # Backend complet (~4800 lignes)
│   ├── http_api.tf            # API Gateway HTTP
│   ├── dynamodb.tf            # Table + GSIs (CoachIdIndex, OrgIdIndex)
│   ├── lambda.tf              # Lambda config
│   └── frontend.tf            # S3 + CloudFront
│
├── deploy.sh                  # Deploy frontend/lambda/all
└── README.md
```

---

## Stack

| Couche | Techno |
|--------|--------|
| Frontend | React 19, Vite, Tailwind 4, Zustand, Framer Motion |
| Backend | AWS Lambda (Python 3.13) |
| Database | DynamoDB (single-table, GSIs) |
| Auth | JWT (signé Lambda) |
| Billing | Stripe (plans: free/starter/pro/business) |
| Email | AWS SES |
| Infra | Terraform, S3, CloudFront, API Gateway HTTP |
| Testing | pytest (backend), Playwright (e2e frontend) |
| CI/CD | deploy.sh (bash) |

---

## Features

### Coach
- Dashboard avec stats & graphiques
- Création/gestion de programmes & séances
- Bibliothèque de 3950 exercices
- Gestion des clients (invitation par email)
- Recettes & plans alimentaires
- Bilans hebdo avec feedback
- Billing Stripe (free 3 élèves, starter 15, pro 50, business illimité)
- Organisation multi-coachs (invite, gestion membres)

### Alumni (élève)
- Suivi des séances (workout tracker)
- Nutrition tracker
- Progression corporelle (body tracker)
- Vue des coaches de l'organisation

### Admin
- Gestion de tous les comptes
- Dashboard global

---

## Design System

Dark mode OLED — accent `#FE7A00` — Bebas Neue / Montserrat.
Classes `up-*` dans `App.css`.

---

## Convention de nommage AWS

Format : `owner-project-env-resource`

| Owner | Prefix | Usage |
|-------|--------|-------|
| App (ce repo) | `app-uplift-prd-*` | Toutes les ressources Terraform de production |
| CTO (site vitrine) | `cto-uplift-prd-*` | Infra du site marketing / landing |

**Exemples :**
- `app-uplift-prd-api` — Lambda principale (monolithe Python)
- `app-uplift-prd-authorizer` — Lambda authorizer JWT
- `app-uplift-prd-backup` — Lambda backup DynamoDB → S3
- `app-uplift-prd-email-forwarder` — Lambda forward SES → Gmail

---

## Déploiement

Usage: `./deploy.sh [all|test|infra|frontend|validate|info] [--profile PROFILE] [--skip-tests]`

- `all`       — Test + Terraform apply + build + S3 deploy + CloudFront invalidate
- `test`      — pytest backend + npm test frontend
- `validate`  — terraform fmt + terraform validate
- `infra`     — Terraform apply uniquement
- `frontend`  — Build + S3 deploy + CloudFront invalidate
- `info`      — Affiche les URLs

---

## DynamoDB Schema

- **Main table** — PK/SK single-table pattern, `CoachIdIndex` GSI, `OrgIdIndex` GSI, TTL enabled
- **invite_tokens** — token-based student invitations with TTL
- **billing** — subscription data (Stripe integration)
- **token_blocklist** — JWT revocation (logout, suspend) with jti hash key
- **security_audit** — CRITICAL sentinel events, 90-day TTL
- **push_tokens** — WebPush notification tokens

---

## Backend Handlers (Python Lambda)

- `handlers.auth_users` — login/logout, registration, email verification, password reset, user CRUD, org management, coach invites
- `handlers.coaching_content` — programmes, workouts, body stats, bilans, notes, recipes, nutrition, exercises, templates, books, meal plans
- `handlers.billing_gamification` — Stripe billing, checkout, portal, webhooks, push notifications, XP/leaderboard, challenges, upload URLs
- `handlers.notifications_cron` — notifications, weekly bilan creation, bilan reminders

---

## Frontend Routing

Role-based route guards (`RequireAuth`, `RequireRole`, `RequireOnboarding`):
- `/` → redirects based on role
- `/login`, `/signup`, `/forgot-password`, `/reset-password` — auth
- `/dashboard`, `/programs`, `/clients`, `/students` — coach/admin
- `/home`, `/mes-programmes`, `/tracker`, `/bilans` — alumni
- `/admin`, `/admin/users`, `/admin/coaches`, `/admin/orgs` — admin
- `/onboarding/coach`, `/onboarding/alumni` — role-specific onboarding
- `/exercises`, `/recipes`, `/books`, `/challenges` — shared

Lazy-loading for all non-public pages. PWA features: service worker, install prompt, push notifications, offline banner.

---

## Security

- JWT authorizer Lambda séparé (`app-uplift-prd-authorizer`)
- Token blocklist for revocation
- Security audit table for sentinel events
- SES email forwarding
- Rate limiting on auth endpoints
- CloudWatch logs 90-day retention (OWASP A09)

---

*Avril 2026 / Ingested 2026-06-03*
