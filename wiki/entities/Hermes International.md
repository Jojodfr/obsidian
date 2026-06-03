---
type: entity
title: "Hermes International"
category: organization
status: evergreen
created: 2026-06-02
tags:
  - entity
  - organization
  - luxury
  - fashion
related:
  - "[[hb8-app-repo]]"
  - "[[klm-repo]]"
  - "[[cnf-repo]]"
  - "[[index]]"
  - "[[entities/_index|Entities]]"
---

# Hermes International

Navigation: [[index]] | [[entities/_index|Entities]]

French luxury design house founded in 1837. Known for leather goods, silk scarves, ready-to-wear, and accessories.

## Relation to This Wiki

Owner of multiple internal codebases under the `hermesintl/` GitLab organization. Projects span from high jewelry tooling to loan management and e-commerce initiatives.

## Technical Footprint

- GitLab organization: `hermesintl`
- Internal tools use modern JS stacks (Nuxt.js, Vue.js) and PHP stacks (Symfony 7.2)
- CI/CD via GitLab with AWS static hosting (S3) and container hosting (ECS Fargate)
- Shared security stack: GitGuardian, SonarQube, Vault, JFrog, AWS OIDC

## Projects

- [[hb8-app-repo|HB8]] — Haute Bijouterie 8 (Nuxt.js 3, high jewelry tooling, AWS S3)
- [[klm-repo|Kellymorphose]] — Vue.js SPA for Kellymorphose initiative (AWS S3)
- [[cnf-repo|Confie]] — Symfony 7.2 PHP application for loan/catalog management (AWS ECS Fargate)
- [[ClearID]] — Genetec physical access control platform integrations (PHP scripts, Symfony API)
- [[8 Penthièvre]] — Building website with interactive floor plans (vanilla HTML/JS, AWS S3)
- [[Shop Maintenance App]] — Internal maintenance ticketing system (Symfony 4.4 + Angular 17, API Platform, GCP)
