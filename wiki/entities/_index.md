---
type: meta
title: "Entities Index"
updated: 2026-06-09
tags:
  - meta
  - index
  - entity
status: evergreen
related:
  - "[[index]]"
  - "[[Andrej Karpathy]]"
  - "[[hot]]"
  - "[[LLM Wiki Pattern]]"
---

# Entities Index

Navigation: [[index]] | [[concepts/_index|Concepts]] | [[sources/_index|Sources]]

All entity pages — people, organizations, products, and tools.

---

## People

- [[Andrej Karpathy]] — AI researcher, educator; originated the LLM Wiki pattern

---

## Organizations

<!-- Add organization pages here -->

---

## Products & Tools

<!-- Add tool and product pages here -->

---

## Organizations

- [[Hermes International]] — French luxury design house; owner of HB8, Kellymorphose, and Confie codebases
- [[Kellymorphose]] — Hermes internal web app (Vue.js SPA, AWS S3)
- [[Confie]] — Hermes internal web app (Symfony 7.2, AWS ECS Fargate)
- [[ClearID]] — Genetec physical access control platform; Hermes identity management
- [[ClearID - MCH Interface]] — contract dates + photo sync from Oracle HCM to ClearID
- [[ClearID - SMI Interface]] — access rights sync SMI ↔ ClearID; 14 PHP 8.2 scripts on `frsellpappepa02`; imports access rights via PATCH `/locations/{id}/accesses`, schedules via PATCH `/locations/{id}/schedules`, contractors (SST) via POST identities with custom fields; generates identity/location/schedule cache arrays (~14MB prod); demo and prod environments
- [[ClearID - IAM Interface]] — IAM identity mapping to ClearID
- [[ClearID - Symfony API]] — Symfony 5.4 API layer for ClearID (development)
- [[ClearID - Site and Location Reference]] — Genetec ClearID site/location inventory; 106 sites, 305 locations, 93 schedules in production; Kanal is a site with no associated locations (status: current)
- [[8 Penthièvre]] — Hermes building website with interactive floor plans (vanilla HTML/JS, AWS S3)
- [[Uplift Coach]] — fitness coaching SaaS; React 19 PWA + Python Lambda + DynamoDB; production at app.upliftcoach.fr
- [[Shop Maintenance App]] — Hermes internal maintenance ticketing system; Symfony 4.4 + Angular 17 + API Platform; transition TMA Niji→Talan en cours
- [[Hermes CI-CD Pipeline]] — Hermes modular CI/CD platform; 6 reusable GitLab components; Vault+OIDC auth; JFrog/Harbor registries; AWS LZv2 deployment
- [[Talan]] — Prestataire TMA prenant la maintenance de Shop Maintenance à partir de juin 2026
- [[Epark]] — Hermes parking availability dashboard; jQuery SPA, AWS S3, on-prem cron data pipeline
- [[DOC4]] — Hermes sustainable real estate management; PHP 7.4 + Apache, AWS ECS Fargate, Cognito OIDC
- [[EHS]] — Hermes Environmental Health & Safety audit app; PHP + Apache Docker, dual MySQL schemas, OAuth2 Cognito; departmental application
- [[Savoir-faire]] — Hermes CFA apprentice evaluation portal; Symfony 5.4, PostgreSQL, session-based auth; retired (replaced by Monodyssee)
- [[Monodyssee]] — Hermes CFA apprentice evaluation v2; Symfony 6.2, PHP 8.2, AWS ECS Fargate, S3 file storage; production active

## Add new entities here as they are identified during ingests.

- [[Suivi des Incidents]] - Hermes incident tracking system; Symfony 6.2, MariaDB 10.5, S3, ECS Fargate; production active
- [[Tilbury]] - Hermes boutique and store management system; Symfony 6.2, PHP 8.1, AWS ECS Fargate; manages Magasin/Site/Tandem, questionnaires, preferences, bilingual Donneeref (status: current)
- [[Suivi des Jours Travailles]] - Hermes working days tracking system; Symfony 6.4, PHP 8.1/8.2, MariaDB 10.5, ECS Fargate; tracks absences, exceptions, monthly worked days with manager/RH validation (status: current)
- [[Infocentre]] - Hermes business intelligence and reporting platform; PHP400 legacy framework, PHP 8.1, MySQL + Snowflake, AWS ECS Fargate; report generation, dashboards, batch exports, dual php400/phpallstat architecture; contains IAM → ClearID identity mapping spec (status: current)
- [[Captation Croco]] - Hermes crocodile leather traceability and quality control system; Symfony 6.4, PHP 8.2, MariaDB, AWS ECS Fargate; AS/400 legacy tables (55+ entities), FCC/FDR/FCP quality control cards, color tinting (Capteinte), defect library (défauthèque), S3 export, custom ECS deploy via jq+SSM+OIDC (status: current)
