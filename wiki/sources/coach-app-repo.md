---
type: source
title: "coach-app Repository"
created: 2026-06-03
updated: 2026-06-03T21:00:00
tags:
  - source
  - codebase
  - aws
  - react
  - python
  - fitness
status: developing
related:
  - "[[Uplift Coach]]"
  - "[[Serverless SaaS Architecture]]"
  - "[[DynamoDB Single-Table Pattern]]"
sources:
  - "[[.raw/coach-app.md]]"
source_type: codebase
author: ""
date_published: 2026-04-01
url: ""
confidence: high
key_claims:
  - "Fitness coaching SaaS built as React 19 PWA + Python 3.13 Lambda monolith on AWS"
  - "Single-table DynamoDB with GSIs enables efficient multi-tenant queries"
  - "Stripe-integrated billing with 4 tiers: free (3 students), starter (15), pro (50), business (unlimited)"
  - "Deploy script orchestrates test → terraform → build → S3 → CloudFront invalidate"
  - "Named resource convention owner-project-env-resource prevents AWS resource collisions"
---

# coach-app Repository

Fitness coaching SaaS — full-stack React PWA with Python Lambda backend, deployed on AWS via Terraform.

---

## What It Is

**Uplift Coach** is a multi-tenant SaaS for fitness coaches to manage clients, programs, workouts, nutrition, and billing. Three role-based views:

| Role | Users | Key Features |
|------|-------|-------------|
| Coach | Personal trainers | Dashboard, program builder, client management, billing |
| Alumni | Clients/students | Workout tracker, nutrition log, body measurements, progress charts |
| Admin | Platform owners | User management, org management, global dashboard |

**Production**: [app.upliftcoach.fr](https://app.upliftcoach.fr)

---

## Architecture

```
Frontend (React 19 + Vite + Tailwind 4 + Zustand)
    │
    ▼
API Gateway HTTP  ←── JWT Lambda Authorizer
    │
    ▼
AWS Lambda (Python 3.13) — monolithic handler
    │
    ├── DynamoDB (single-table, 5 aux tables)
    ├── S3 (images)
    ├── CloudFront (CDN)
    ├── Stripe (billing)
    └── SES (emails)
```

Infrastructure managed entirely in `terraform/`.

---

## Codebase Structure

| Path | Purpose | Lines |
|------|---------|-------|
| `frontend/src/App.jsx` | Router + role guards + lazy loading + ScrollToTop | ~636 |
| `frontend/src/lib/api-client.js` | Fetch wrapper with interceptors, toast integration, JWT decode | ~237 |
| `frontend/src/lib/api.js` | API method signatures (thin layer over api-client) | ~100+ |
| `frontend/src/store/auth.js` | Zustand + persist; login/verify/refresh/logout | ~200+ |
| `frontend/src/ui/` | 8 atomic components (Card, Button, Badge, Input, Label, Divider, Modal, Toast) | — |
| `frontend/src/pages/coach/` | Coach-facing pages | ~15 files |
| `frontend/src/pages/alumni/` | Student-facing pages | ~12 files |
| `frontend/src/pages/admin/` | Admin pages | ~5 files |
| `terraform/lambda/lambda_function.py` | Main Lambda handler | ~4800 |
| `terraform/lambda/shared/base.py` | SSM secrets, Sentry, Stripe, WebPush, DynamoDB utils, rate limiting | ~200+ |
| `terraform/lambda/handlers/auth_users.py` | Auth (login/logout/register), email verify, password reset, user/org CRUD | ~400+ |
| `terraform/lambda/handlers/coaching_content.py` | Programs, workouts, body stats, bilans, notes, recipes, nutrition, exercises, meal plans, books | ~800+ |
| `terraform/lambda/handlers/billing_gamification.py` | Stripe checkout/portal/webhooks, XP/leaderboard, challenges, push notifications, upload URLs | ~600+ |
| `terraform/lambda/handlers/notifications_cron.py` | Notifications, weekly bilan creation, bilan reminders | ~200+ |
| `terraform/lambda/authorizer_function.py` | JWT verification + blocklist check (separate Lambda) | ~80 |
| `terraform/frontend/main.tf` | S3 buckets (site + images), CORS for direct uploads | ~76 |
| `terraform/frontend/cloudfront.tf` | Distribution with 5 cache behaviors + CSP security headers | ~244 |
| `terraform/http_api.tf` | 150+ routes, JWT authorizer, CORS, access logging | ~359 |
| `terraform/dynamodb.tf` | 6 DynamoDB tables | ~179 |
| `terraform/lambda.tf` | Lambda config + authorizer + CloudWatch | ~133 |
| `terraform/http_api.tf` | API Gateway HTTP | — |
| `terraform/frontend.tf` | S3 + CloudFront | — |

---

## DynamoDB Tables

| Table | Keys | Purpose |
|-------|------|---------|
| Main table | PK (hash) + SK (range), CoachIdIndex GSI, OrgIdIndex GSI | Core app data |
| invite_tokens | token (hash) + TTL | Student/coach invitations |
| billing | PK + SK | Stripe subscription data |
| token_blocklist | jti (hash) + TTL | JWT revocation |
| security_audit | PK + SK + TTL | Sentinel event logging |
| push_tokens | userId + endpoint | WebPush notification tokens |

**Single-table key patterns in main table:**
```
PK              SK                Entity
USER#123        #PROFILE          User profile
USER#123        SESSION#abc       Workout log
CLIENT#name     PROGRAM#456       Program metadata
CLIENT#name     SESSION#789       Session within program
EMAIL#user@...  USER              Email → userId reverse lookup (coaches)
```

**Visibility model for programs:**
- `private` — coach only
- `org` — all coaches in same org
- `assigned` — specific assigned clients
- `isExample` — global examples visible to all

---

## Deployment

Single `deploy.sh` script handles full lifecycle:

```bash
./deploy.sh all        # Test → Terraform → Build → S3 → invalidate
./deploy.sh frontend   # Build + S3 deploy + CloudFront invalidate
./deploy.sh infra      # Terraform apply only
./deploy.sh test       # pytest + npm test
./deploy.sh validate   # terraform fmt + validate
```

Smart cache strategy:
- `index.html`, `sw.js`, `*.webmanifest` → no-cache
- Hashed assets → `max-age=31536000, immutable`

---

## Frontend Architecture

### Design System
Tailwind 4 uses `@theme` CSS variables declared directly in `App.css` — no `tailwind.config.js`:

```css
@theme {
  --color-bg: #1a1a1a;
  --color-bg-card: #262626;
  --color-primary: #C8FF00;        /* Electric Lime */
  --color-primary-text: #1a1a1a;
  --color-gold: #D4A017;           /* Brutal Gold */
  --font-heading: 'Teko', sans-serif;
  --font-body: 'Montserrat', system-ui, sans-serif;
}
```

Custom utility classes (`up-btn`, `up-input`) with explicit focus-visible states for accessibility.

### State Management
- **Auth store**: Zustand + `persist` middleware; stores user object + token; client-side JWT expiry check before API calls
- **UI store**: Toast notifications, modals, offline state

### API Client
Custom `fetch` wrapper (`api-client.js`) with:
- Token injection via `Authorization: Bearer` header
- Status code normalization: 401 → logout redirect, 402 → payment toast, 429 → rate-limit warning
- Toast integration for unlocked badges (gamification)
- Network error detection (`TypeError` + "fetch")

### PWA Stack
- `vite-plugin-pwa` generates service worker
- `localStorage` for JWT (explicitly chosen over `sessionStorage` for mobile app-switching survival)
- Install prompt, offline banner, push notification subscription

---

## Notable Patterns

- **Single-table design** — All entities share PK/SK namespace; access patterns served via GSIs
- **Lambda monolith** — One Python function handles all routes via internal dispatch (~4800 lines)
- **JWT authorizer** — Separate Lambda validates tokens at API Gateway level
- **Lazy-loaded routes** — All authenticated pages use React.lazy() + Suspense
- **PWA features** — Service worker, install prompt, push notifications, offline detection
- **Named resource convention** — `app-uplift-prd-*` prevents collision with marketing site (`cto-uplift-prd-*`)
- **SSM-first secrets** — `shared/base.py` reads from AWS SSM Parameter Store with env fallback; never hardcodes secrets

---

## Billing Tiers

| Plan | Students | Monthly | Annual |
|------|----------|---------|--------|
| Free | 3 | €0 | — |
| Starter | 15 | €39/mo | €33/mo (€396/yr) |
| Pro | 50 | €79/mo | €66/mo (€792/yr) |
| Business | unlimited | €149/mo | €125/mo (€1500/yr) |

Stripe integration:
- **Checkout sessions** — subscription mode, with 14-day trial for new coaches
- **Customer portal** — coaches manage payment methods and invoices
- **Webhook handler** — `checkout.session.completed` links subscription to user profile
- **Signup flow** — creates Stripe customer before account exists; links on checkout completion

---

## Testing

- **Backend**: pytest on Lambda handlers (unit + integration)
- **Frontend**: Playwright e2e tests via `@playwright/test`
- **Infra**: `terraform validate` + `terraform fmt -check`

---

## Security Posture

- **OWASP A09**: CloudWatch 90-day log retention on all log groups
- **JWT authorizer**: Separate Lambda (`app-uplift-prd-authorizer`) verifies HS256 signatures and checks DynamoDB token blocklist
- **Token revocation**: Logout writes `jti` to `token_blocklist` table with TTL matching JWT expiry
- **Security audit table**: Sentinel events (`LOGIN_SUCCESS`, `LOGIN_FAILED`, `LOGIN_RATE_LIMITED`, etc.) persisted for 90 days
- **Rate limiting**: Auth endpoints use client-IP-based rate limiting with 15-minute lockout
- **Timing attack mitigation**: `_DUMMY_HASH` check on missing users ensures constant-time comparison
- **CORS**: Explicit allowed origins in API Gateway; no wildcard
- **CloudFront CSP** (via `aws_cloudfront_response_headers_policy`):
  - Strict HSTS (2-year preload)
  - X-Frame-Options DENY
  - XSS protection block mode
  - Referrer policy strict-origin-when-cross-origin
  - Content-Security-Policy: default-src 'self', script-src with Google Analytics, img-src with CloudFront + S3
- **Separate S3 buckets**: Site bucket (private, CloudFront OAI) and images bucket (CORS-enabled for direct browser PUT uploads)

---

## Cross-References

- [[Uplift Coach]] — product/entity page
- [[Serverless SaaS Architecture]] — architectural concept extracted from this codebase
- [[DynamoDB Single-Table Pattern]] — data modeling pattern demonstrated here
- [[hb8-app-repo]] — Hermes Nuxt.js static app (contrast: serverless CMS vs SaaS backend)
- [[cnf-repo]] — Hermes Symfony ECS app (contrast: containerized PHP vs Lambda Python)
