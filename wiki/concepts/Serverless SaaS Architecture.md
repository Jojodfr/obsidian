---
type: concept
title: "Serverless SaaS Architecture"
created: 2026-06-03
updated: 2026-06-03
tags:
  - concept
  - architecture
  - aws
  - serverless
  - saas
status: developing
related:
  - "[[Uplift Coach]]"
  - "[[coach-app-repo]]"
  - "[[DynamoDB Single-Table Pattern]]"
  - "[[JWT Auth in Static Sites]]"
  - "[[Two-Repo Architecture]]"
sources:
  - "[[.raw/coach-app.md]]"
complexity: intermediate
domain: "cloud-architecture"
aliases:
  - "Lambda monolith"
  - "Serverless full-stack"
---

# Serverless SaaS Architecture

A full-stack SaaS build pattern using managed AWS services with minimal operational overhead. React/Vite frontend + Python Lambda monolith backend + DynamoDB single-table + Terraform IaC.

---

## Core Pattern

```
Static PWA (S3 + CloudFront)
    │
    ▼
API Gateway HTTP v2
    │
    ├── JWT Authorizer Lambda  ←── token validation
    │
    ▼
Main Lambda (Python/Node)
    │
    ├── DynamoDB (single-table, GSIs)
    ├── S3 (user uploads)
    └── Third-party APIs (Stripe, SES)
```

---

## Why This Pattern

| Concern | Serverless Approach | Alternative |
|---------|-------------------|-------------|
| **Scaling** | Automatic — Lambda scales to zero and up | ECS/EC2 needs capacity planning |
| **Cost** | Pay per request; free at low volume | Base cost for running servers |
| **Ops** | No servers to patch, no SSH | DevOps overhead for OS updates |
| **Speed to ship** | Single Lambda file, hot deploy | Container builds, registry pushes |
| **Cold start** | Mitigated by 512MB + provisioned concurrency | Always-on containers |

---

## When It Fits

- **Early-stage SaaS** — validate product without infra overhead
- **Bursty traffic** — fitness apps spike Monday mornings, quiet weekends
- **Small teams** — one developer handles frontend + backend + ops
- **Regulated billing** — Stripe webhooks map cleanly to Lambda events

## When It Doesn't

- Long-running workloads (>30 min — Lambda timeout)
- Heavy computation (ML inference, video encoding)
- Complex transactions requiring ACID across multiple entities
- WebSocket real-time (use API Gateway WebSockets or AppSync instead)

---

## Key Decisions in the Pattern

### Lambda Monolith vs Micro-Lambda

**Monolith** (one Lambda, internal routing):
- ✅ Shared imports, single deployment unit, warm caches between routes
- ❌ Larger cold start, tight coupling, all-or-nothing deploys

**Micro-Lambda** (one function per route):
- ✅ Smaller cold starts, independent deployments
- ❌ Code duplication, complex inter-service calls, more IAM roles

Uplift Coach uses **monolith** (~4800 lines Python) because the team is small and the API surface is cohesive.

### DynamoDB Single-Table

All entities share one table with composite PK/SK keys:

```
PK            SK                    Attributes
USER#123      PROFILE#123           name, email, role
PROGRAM#456   PROGRAM#456           title, description
PROGRAM#456   SESSION#789           name, exercises
SESSION#789   LOG#2026-04-01        reps, weight, rpe
```

Access patterns served by GSIs:
- `CoachIdIndex` — coachId + SK for "all data for this coach"
- `OrgIdIndex` — orgId + SK for "all data in this organization"

### JWT Authorizer Lambda

Separate Lambda function for token validation at API Gateway level:
- Decouples auth from business logic
- Enables token blocklist lookups (logout, suspend)
- Returns IAM policy allowing/denying access

---

## Stack Reference

| Layer | Technology | Role |
|-------|-----------|------|
| Frontend | React 19 + Vite + Tailwind | PWA, lazy routes, role guards |
| State | Zustand | Lightweight global store |
| Backend | Python 3.13 Lambda | Monolithic API handler |
| Database | DynamoDB | Single-table with TTL |
| Auth | JWT + Lambda Authorizer | Stateless token validation |
| Payments | Stripe | Checkout, portal, webhooks |
| Email | AWS SES | Transactional emails |
| Storage | S3 + CloudFront | Static assets, user images |
| IaC | Terraform | All resources declared |
| Testing | pytest + Playwright | Unit + e2e |
| Deploy | bash script | Test → Terraform → Build → S3 |

---

## Variations

| Variant | Change | When |
|---------|--------|------|
| API Gateway REST | Replace HTTP API v2 | Need request/response transformations |
| RDS instead of DynamoDB | Need ACID transactions, complex joins | Relational data model |
| Step Functions | Orchestrate multi-step flows | Long-running workflows |
| EventBridge + SQS | Async job queue | Heavy background processing |

---

## Related Patterns

- [[DynamoDB Single-Table Pattern]] — deep dive on data modeling
- [[JWT Auth in Static Sites]] — auth patterns for static frontends
- [[Two-Repo Architecture]] — separating built app from IaC (Uplift does this within one repo)
- [[Symfony ECS Deployment]] — contrasting containerized approach
- [[Nuxt.js Static Deployment]] — contrasting static site approach
