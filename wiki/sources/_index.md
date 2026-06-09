---
type: meta
title: "Sources Index"
updated: 2026-06-09
tags:
  - meta
  - index
  - source
status: evergreen
related:
  - "[[index]]"
  - "[[log]]"
  - "[[entities/_index]]"
  - "[[Andrej Karpathy]]"
---

# Sources Index

Navigation: [[index]] | [[concepts/_index|Concepts]] | [[entities/_index|Entities]]

All source pages — summaries of ingested documents, transcripts, articles, and data.

---

## Transcripts


---

## Articles

<!-- Add article source pages here -->

---

## Papers

<!-- Add paper source pages here -->

---

## Codebases

- [[hb8-app-repo]] — 2026-06-02 | Nuxt.js static web app for Hermes International | GitLab CI/CD, AWS
- [[klm-repo]] — 2026-06-02 | Vue.js SPA for Kellymorphose (Hermes) | GitLab CI/CD, AWS S3, CIFS
- [[cnf-repo]] — 2026-06-02 | Symfony 7.2 PHP app for Confie (Hermes) | GitLab CI/CD, AWS ECS Fargate, Kaniko
- [[clearid-directory]] — 2026-06-03 | ClearID integration directory for Hermes | PHP scripts, Symfony API, MCH/SMI/IAM interfaces
- [[livret-penthievre-sources]] — 2026-06-03 | 8 Penthièvre building website | Vanilla HTML/CSS/JS, AWS S3, interactive SVG floor plans
- [[coach-app-repo]] — 2026-06-03 | Uplift Coach fitness SaaS | React 19 PWA, Python 3.13 Lambda, DynamoDB single-table, Stripe, Terraform, AWS
- [[shop-maintenance-app-repo]] — 2026-06-03 | Hermes Shop Maintenance App | Symfony 4.4 API, Angular 17 SPA, JWT auth, GitLab CI/CD, GCP
- [[hermes-cicd-pipeline]] — 2026-06-04 | Hermes CI/CD Pipeline Repositories | 6 GitLab repos: baseline, release, containers, aws-components, aws-ecs | 7 wiki pages created
- [[cr-atelier6-shop-maintenance]] — 2026-06-04 | Compte rendu atelier 6 CI/CD Shop Maintenance | Atelier de reversibilité Niji→Talan, MEP en atelier, plan d'actions
- [[mep-shop-maintenance-20250821]] — 2026-06-04 | Fiche de livraison MEP 2.0.8 Shop Maintenance | Release notes, bugs HSM-18/19/20, procédure déploiement
- [[niji-hermes-reversibilite-ateliers-6]] — 2026-06-04 | Macro-planning + détail atelier 6 réversibilité | Mirroring GitLab, branches, checklist MEP, planning 7 ateliers
- [[epark-sources-aws]] — 2026-06-03 | Epark parking availability app | jQuery SPA, AWS S3, on-prem cron data pipeline
- [[doc4-sources]] — 2026-06-03 | DOC4 sustainable real estate app | PHP 7.4 + Apache, AWS ECS Fargate, Cognito OIDC, wkhtmltopdf
- [[clearid-access-api-swagger]] — 2026-06-05 | ClearID Access API OpenAPI 3.0.4 spec | 12 endpoints, 20+ models, OAuth2; from swaggest.demo.clearid.io
- [[clearid-developer-guide]] — 2026-06-05 | ClearID Developer Portal overview | Auth flows, endpoint catalog, code samples, webhooks; from developer.genetec.com
- [[clearid-sites-locations-schedules]] — 2026-06-05 | ClearID Sites/Locations/Schedules CSV | 106 sites, 305 locations, 93 schedules; production export from SMI scripts; includes Pantin - Kanal
- [[clearid-demo-locations]] — 2026-06-05 | ClearID demo locations API response | 11 locations across 5 sites; Kanal confirmed 0 locations; from live API call
- [[clearid-demo-identities]] — 2026-06-05 | ClearID demo identities API response | 23 identities; Active/Inactive; custom fields model; from live API call
- [[EHS-sources]] — 2026-06-05 | EHS web server source image | PHP + Apache Docker; dual MySQL schemas; OAuth2 Cognito; environmental health & safety audits
- [[savoir-faire-repo]] — 2026-06-08 | Savoir-faire (Mon Odyssée) source | Symfony 5.4, PostgreSQL, Dompdf; apprentice evaluation portal; retired
- [[monodyssee-repo]] — 2026-06-08 | Monodyssee v2 source | Symfony 6.2, PHP 8.2, AWS ECS Fargate, S3; production successor to Savoir-faire
- [[monodyssee-training-docs]] — 2026-06-08 | Monodyssee training evaluation PDFs | Jalon/Bilan/CAP documents, 5-level competency scale, maroquinerie skills taxonomy

 - [[suivi-des-incidents-repo]] - 2026-06-08 | Suivi des Incidents (SDI) source | Symfony 6.2, PHP 8.1, MariaDB, S3, ECS Fargate; incident tracking
- [[tilbury-repo]] - 2026-06-08 | Tilbury boutique/store management source | Symfony 6.2, PHP 8.1, 19 entities, dual Magasin/Site model, bilingual Donneeref
- [[suivi-des-jours-travailles-repo]] - 2026-06-08 | Suivi des Jours Travailles (SDJ) source | Symfony 6.4, PHP 8.1/8.2, MariaDB 10.5, workforce tracking, AS400 legacy import
- [[infocentre-repo]] - 2026-06-09 | Infocentre BI reporting source | PHP400 legacy framework, PHP 8.1, MySQL + Snowflake, AWS ECS Fargate, mod_auth_mellon SAML; contains IAM → ClearID identity mapping spec
- [[captation-croco-repo]] - 2026-06-09 | Captation Croco (Armonie) source | Symfony 6.4, leather traceability, AS/400 tables (Cap001t-Cap055t), DOMPDF, custom ECS deploy via jq+SSM, Entra ID SAML; FCC/FDR/FCP quality control cards
- [[clearid-smi-droits-acces-scripts]] - 2026-06-09 | ClearID SMI droits d'acces PHP scripts | 14 PHP 8.2 scripts: access rights import, location/schedule sync, contractor import, identity audits, cache generation; from `\\cifs-frsel\...\Interface SMI-ClearID (droits d'acces)\PHP`

## Add new sources here after each ingest.
