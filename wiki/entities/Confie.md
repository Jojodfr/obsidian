---
type: entity
title: "Confie"
created: 2026-06-02
updated: 2026-06-02
tags:
  - entity
  - hermes
  - application
  - symfony
  - php
  - internal-tool
related:
  - "[[cnf-repo]]"
  - "[[Hermes International]]"
  - "[[hb8-app-repo]]"
  - "[[klm-repo]]"
---

# Confie

Navigation: [[entities/_index|Entities]] | [[index]]

**Confie** is an internal web application for [[Hermes International]], built with **Symfony 7.2** (PHP 8.4). It is a full-stack server-rendered application deployed to **AWS ECS Fargate**.

---

## What It Is

Confie (project code **CNF**) appears to be an internal business application for managing loans/prets, catalogs, and back-office administration. The name "Confie" likely relates to "confiance" (trust) or "confier" (to entrust), consistent with loan/collateral management use cases.

The repository is a **project type v6.0.1** (projet type) -- a template/base for rapid Hermes internal application deployment.

---

## Technology

| Layer | Technology |
|-------|-----------|
| Backend | Symfony 7.2 (PHP 8.4) |
| ORM | Doctrine ORM 2.17 |
| Templates | Twig 3.x |
| Database | PostgreSQL 17 |
| Frontend | Bootstrap, jQuery 3.5, dataTables.js |
| PDF | wkhtmltopdf + dompdf |
| Excel/CSV | PhpSpreadsheet, League CSV |
| Barcode | picqer/php-barcode-generator |
| Auth | Apache mod_auth_openidc (SSO) |
| AWS | ECS Fargate, S3, ECR |
| Container | Debian 12 + Apache + PHP + PostgreSQL |

---

## Modules

- **Administration**: User management, groups, menus, reference data
- **Back-Office (BO)**: FAQ, glossary, groups, logs, parameters
- **Catalogue**: Product catalog with barcode generation
- **Loans (Prets)**: Loan management, reservations, validations
- **Reports (Rapports)**: Multi-format reports with PDF/Excel export
- **Import/Export**: Excel/CSV data exchange
- **M3 Integration**: Stock verification via Hermes M3 API

---

## Deployment

Confie deploys to **AWS ECS Fargate** (not S3 static like HB8/KLM):

| Environment | AWS Account | ECS Service | Trigger |
|-------------|-------------|-------------|---------|
| CHK | 543025552220 | confie-chk-fargate | MR / Branch |
| PRD | 027354322115 | confie-prd-fargate | Git tag |

The CI/CD pipeline uses **Kaniko** for container image builds and the `complete-ecs-service-deployment` Hermes template component for ECS deployment.

---

## See Also

- [[cnf-repo]] -- Full repository analysis and CI/CD pipeline
- [[hb8-app-repo]] -- Nuxt.js static SPA (S3-hosted)
- [[klm-repo]] -- Vue.js SPA (S3-hosted)
- [[Hermes International]] -- Parent company context
- [[Symfony ECS Deployment]] -- Deployment pattern
