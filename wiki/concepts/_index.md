---
type: meta
title: "Concepts Index"
updated: 2026-06-09
tags:
  - meta
  - index
  - concept
domain: knowledge-management
status: evergreen
related:
  - "[[index]]"
  - "[[dashboard]]"
  - "[[Wiki Map]]"
  - "[[Hot Cache]]"
  - "[[LLM Wiki Pattern]]"
  - "[[Compounding Knowledge]]"
  - "[[LLM Wiki Pattern]]"
  - "[[Hot Cache]]"
  - "[[Compounding Knowledge]]"
---

# Concepts Index

Navigation: [[index]] | [[entities/_index|Entities]] | [[sources/_index|Sources]]

All concept pages — ideas, patterns, and frameworks extracted from sources.

---

## Knowledge Management

- [[LLM Wiki Pattern]] — the core architecture for persistent, compounding knowledge bases
- [[Hot Cache]] — ~500-word session context file, updated after every ingest
- [[Compounding Knowledge]] — why the wiki grows more valuable over time, unlike RAG
- [[DragonScale Memory]] — memory-layer spec: fold operator, deterministic page addresses, semantic tiling, boundary-first autoresearch (status: shipped v0.4, all four mechanisms opt-in)
- [[Persistent Wiki Artifact]]: durable Markdown page as the LLM's memory object (developing)
- [[Source-First Synthesis]]: provenance discipline for LLM wiki layers (developing)
- [[Query-Time Retrieval]]: query synthesis with citations, complementary to Obsidian search (developing)

---

- [[Two-Repo Architecture]] — separating built app from infrastructure-as-code (status: mature)
- [[Nuxt.js Static Deployment]] — static generation with SPA fallback and performance techniques (status: mature)
- [[Vue.js SPA Static Deployment]] — compiled Vue.js bundles to S3, client-side routing (status: mature)
- [[Symfony ECS Deployment]] — containerized Symfony to AWS ECS Fargate via Kaniko (status: mature)
- [[JWT Auth in Static Sites]] — client-side JWT pattern without backend (status: developing)
- [[ClearID Integration Architecture]] — Hermes systems integration with Genetec ClearID; 14 PHP scripts for SMI interface (access rights, locations, schedules, contractor import), OAuth2 client_credentials, demo/prod dual environment (status: current)
- [[Serverless SaaS Architecture]] — React PWA + Lambda monolith + DynamoDB single-table on AWS; distilled from [[Uplift Coach]] (status: developing)
- [[GitLab CI-CD Component Architecture]] — modular GitLab CI/CD component pattern used across Hermes; replaces monolithic templates with versioned, input-driven components (status: mature)
- [[Hermes CI-CD Baseline Template]] — mandatory baseline jobs: SonarQube, GitGuardian, Vault, semantic release (status: mature)
- [[AWS ECS Deployment Pattern]] — standard ECS Fargate deployment: ECR push → task definition → service update with rollback (status: mature)
- [[Container Build Pipeline]] — Docker/Kaniko build + Hadolint lint + Container Factory sourcing validation (status: mature)
- [[Ticketing System Architecture]] — Symfony API Platform + Angular SPA pattern for internal maintenance ticketing; from [[Shop Maintenance App]] (status: developing)
- [[ClearID Access API]] — ClearID REST API for physical access management; OpenAPI 3.0.4 spec with 12 endpoints, 20+ models (status: current)
- [[ClearID Developer Portal]] — Public developer portal for Genetec ClearID; authentication guides, code samples, webhooks (status: current)

- [[Savoir-faire App]] — Legacy Symfony 5.4 apprentice evaluation system for CFA Hermès MyCampH; campaign→evaluation→signature workflow (status: retired)
- [[Monodyssee App]] — Production Symfony 6.2 successor to Savoir-faire; AWS ECS Fargate, S3, CKEditor, PHPSpreadsheet, bilan/attestation/module system (status: current)
- [[Tilbury App]] — Hermes boutique/store management system; Symfony 6.2, dual Magasin/Site model, Tandem pairing, bilingual Donneeref, questionnaires and preferences (status: current)
- [[Formation Hermès]] — 7-module training curriculum to learn Hermès IT ecosystem from wiki knowledge; for newjoiners and contractors (status: current)
- [[CFA Hermès Evaluation Framework]] — CFA MyCampH EDSF apprentice evaluation framework; Jalon/Bilan/CAP document types, 5-level competency scale, savoir-faire taxonomy, Monodyssee entity mapping (status: current)

 - [[CFA Hermès Evaluation Framework]] - CFA MyCampH EDSF apprentice evaluation framework; Jalon/Bilan/CAP document types, 5-level competency scale, savoir-faire taxonomy, Monodyssee entity mapping
 - [[Suivi des Incidents App]] - Hermes incident tracking application architecture; SF 6.2, MariaDB, S3 dual storage, social media feature, AS400 legacy import, glossaire translation
- [[Suivi des Jours Travailles App]] - Hermes workforce tracking application; Symfony 6.4, PHP 8.1/8.2, MariaDB 10.5, ECS Fargate; absences, JoursTravail, Validations, N+1 email workflow, AS400 legacy import pipeline
- [[Infocentre App]] - Hermes BI reporting application; PHP400 legacy framework, PHP 8.1, MySQL + Snowflake, AWS ECS Fargate; 70+ report engine files, dashboard desk module, phpallstat statistics sub-app, dual-database health checks, Snowflake pdo_snowflake extension
- [[Captation Croco App]] - Hermes crocodile leather traceability and quality control; Symfony 6.4, PHP 8.2, MariaDB, AWS ECS Fargate; AS/400 legacy tables (Cap001t-Cap055t), FCC/FDR/FCP quality cards, DOMPDF, DataTables, custom ECS deploy via jq+SSM+OIDC, Entra ID SAML

## Add new concepts here as they are extracted from sources.
