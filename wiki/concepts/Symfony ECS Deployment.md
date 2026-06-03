---
type: concept
title: "Symfony ECS Deployment"
created: 2026-06-02
updated: 2026-06-02
tags:
  - concept
  - symfony
  - aws
  - ecs
  - fargate
  - docker
  - kaniko
  - hermes
related:
  - "[[cnf-repo]]"
  - "[[Vue.js SPA Static Deployment]]"
  - "[[Nuxt.js Static Deployment]]"
  - "[[Hermes International]]"
---

# Symfony ECS Deployment

Navigation: [[concepts/_index|Concepts]] | [[index]]

Deployment pattern used by Hermes International for **Symfony PHP applications** to **AWS ECS Fargate**.

---

## Overview

Unlike the static SPA deployments (HB8/KLM → S3), Symfony applications require:
- A running PHP application server
- A database connection
- Server-side rendering via Twig
- Session/state management

Hermes solves this with **containerized deployment to AWS ECS Fargate** using the `complete-ecs-service-deployment` CI component.

---

## Architecture

```
GitLab CI
  |
  +-- Kaniko build
  |     Debian 12 + Apache + PHP 8.4 + PostgreSQL
  |     Composer install --no-scripts --no-dev
  |     +-- Container image
  |
  +-- JFrog Artifactory (push/publish)
  |
  +-- AWS ECS Fargate (deploy)
        +-- ECR (container registry)
        +-- Task Definition
        +-- ECS Service
        +-- ALB (Application Load Balancer)
```

## Container Stack

| Component | Technology | Purpose |
|-----------|-----------|---------|
| Base OS | Debian 12 (Bookworm) | Stable, secure foundation |
| Web Server | Apache 2.4 | HTTP request handling |
| PHP | PHP 8.4 | Application runtime |
| Database | PostgreSQL 17 | Data persistence (also client) |
| PDF | wkhtmltopdf 0.12.6 | PDF generation |
| Process Mgr | Supervisor | Manage Apache/PHP processes |
| Auth | mod_auth_openidc | SSO via Mellon/SAML |

## CI/CD Components

### Baseline Component
- `language: container`
- `tool: kaniko`
- Stages: init, build, test, analysis, manifest, sign, verify, push, publish, schedule

### ECS Deployment Component
`complete-ecs-service-deployment@feat/jfrog-migration`
- Inputs: `service_name`, `environment`, `aws_account`, `ssm_path`
- `enable_secret_manager: "true"`
- `env_variables_file: "environments/<env>/env.properties"`

### Custom Overrides
- **Vault**: Secret retrieval via `load_secret`
- **Custom Kaniko Build**: Write CI metadata before building
- **ECS Jobs**: `ecr-push-image`, `ecs-deploy-service`, `ecs-push-task-definition`

## Comparison with Static SPA Deployment

| Aspect | Static SPA (HB8/KLM) | Symfony (CNF) |
|--------|---------------------|---------------|
| Hosting | AWS S3 + CloudFront | AWS ECS Fargate |
| Build | Node.js / static | Kaniko Docker |
| Runtime | Client-side JS | Server-side PHP |
| Database | None | PostgreSQL |
| Auth | JWT / OAuth2 | mod_auth_openidc |
| Scaling | CDN-level | ECS task scaling |
| CI Component | `s3-push-files` | `complete-ecs-service-deployment` |

## See Also

- [[cnf-repo]] -- Full implementation example
- [[Vue.js SPA Static Deployment]] -- S3 static pattern
- [[Nuxt.js Static Deployment]] -- Nuxt S3 static pattern
