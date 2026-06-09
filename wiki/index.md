---
type: meta
title: "Wiki Index"
updated: 2026-06-08
tags:
  - meta
  - index
status: evergreen
related:
  - "[[overview]]"
  - "[[log]]"
  - "[[hot]]"
  - "[[dashboard]]"
  - "[[Wiki Map]]"
  - "[[concepts/_index]]"
  - "[[entities/_index]]"
  - "[[sources/_index]]"
  - "[[LLM Wiki Pattern]]"
  - "[[Hot Cache]]"
  - "[[Compounding Knowledge]]"
  - "[[Andrej Karpathy]]"
---

# Wiki Index

Last updated: 2026-06-09 | Total pages: 108 | Sources ingested: 28

Navigation: [[overview]] | [[log]] | [[hot]] | [[dashboard]] | [[Wiki Map]] | [[getting-started]]

---

## Concepts

- [[LLM Wiki Pattern]] — the pattern for building persistent, compounding knowledge bases using LLMs (status: mature)
- [[Hot Cache]] — ~500-word session context file, updated after every ingest and session (status: mature)
- [[Compounding Knowledge]] — why wiki knowledge grows more valuable over time, unlike RAG (status: mature)
- [[cherry-picks]] — prioritized feature backlog from ecosystem research; 13 features to add to claude-obsidian (status: current)
- [[SVG Diagram Style Guide]] — canonical visual style for all diagrams: Space Grotesk, #0A0A0A dark theme, #E07850 accent, full design tokens (status: evergreen)
- [[Pro Hub Challenge]] — community challenge pattern for building claude-seo/claude-blog extensions; first challenge produced 6 submissions, 5 integrated in v1.9.0 (status: evergreen)
- [[Semantic Topic Clustering]] — SERP-based keyword grouping replacing paid tools; hub-spoke architecture with interactive visualization (status: evergreen)
- [[Search Experience Optimization]] — "read SERPs backwards" methodology for page-type mismatch detection and persona scoring (status: evergreen)
- [[SEO Drift Monitoring]] — "git for SEO" baseline/diff/track with 17 comparison rules and SQLite persistence (status: evergreen)
- [[DragonScale Memory]] — memory-layer spec inspired by the Heighway dragon curve; fold operator, deterministic page addresses, semantic tiling, boundary-first autoresearch (status: shipped v0.4, all four mechanisms opt-in)
- [[Persistent Wiki Artifact]]: durable Markdown page as the LLM's memory object, distinct from ephemeral chat turns (status: developing)
- [[Source-First Synthesis]]: provenance discipline; raw sources stay immutable while the wiki layer is synthesized and cited (status: developing)
- [[Query-Time Retrieval]]: wiki query path synthesizes with citations; complementary to Obsidian's in-vault search (status: developing)
- [[Two-Repo Architecture]] — separating built app from infrastructure-as-code; used by [[hb8-app-repo]] (status: mature)
- [[Nuxt.js Static Deployment]] — static generation with SPA fallback; preloaded fonts and chunked JS (status: mature)
- [[JWT Auth in Static Sites]] — client-side JWT pattern without backend; limitations and alternatives (status: developing)
- [[Vue.js SPA Static Deployment]] — compiled Vue.js bundles to S3, client-side routing (status: mature)
- [[Symfony ECS Deployment]] — containerized Symfony to AWS ECS Fargate via Kaniko (status: mature)
- [[ClearID Integration Architecture]] — Hermes systems integration with Genetec ClearID; PHP scripts, OAuth2, Oracle HCM REST (status: current)
- [[Serverless SaaS Architecture]] — React PWA + Lambda monolith + DynamoDB single-table on AWS; distilled from [[Uplift Coach]] (status: developing)
- [[GitLab CI-CD Component Architecture]] — modular GitLab CI/CD component pattern used across Hermes; replaces monolithic templates with versioned, input-driven components (status: mature)
- [[Hermes CI-CD Baseline Template]] — mandatory baseline jobs: SonarQube, GitGuardian, Vault, semantic release (status: mature)
- [[AWS ECS Deployment Pattern]] — standard ECS Fargate deployment: ECR push → task definition → service update with rollback (status: mature)
- [[Container Build Pipeline]] — Docker/Kaniko build + Hadolint lint + Container Factory sourcing validation (status: mature)
- [[ClearID Access API]] — ClearID REST API for physical access management; OpenAPI 3.0.4 spec with endpoints for granting, revoking, checking accesses (status: current)
- [[ClearID Developer Portal]] — Public developer portal for Genetec ClearID; authentication guides, endpoint catalog, code samples (status: current)

---

## Entities

- [[Hermes CI-CD Pipeline]] — Hermes modular CI/CD platform; 6 reusable GitLab components; Vault+OIDC auth; JFrog/Harbor registries; AWS LZv2 deployment (status: current)
- [[Talan]] — Prestataire TMA (Tierce Maintenance Applicative) pour Hermès Shop Maintenance; transition depuis Niji en juin 2026 (status: current)
- [[Andrej Karpathy]] — AI researcher, creator of the LLM Wiki pattern, former Tesla AI director (status: developing)
- [[Ar9av-obsidian-wiki]] — multi-agent compatible LLM Wiki plugin; delta tracking manifest (status: current)
- [[Nexus-claudesidian-mcp]] — native Obsidian plugin + MCP bridge; workspace memory, task management (status: current)
- [[ballred-obsidian-claude-pkm]] — goal cascade PKM; auto-commit hooks, /adopt command (status: current)
- [[rvk7895-llm-knowledge-bases]] — 3-depth query system, Marp slides, parallel deep research (status: current)
- [[kepano-obsidian-skills]] — official skills from Obsidian creator; defuddle, obsidian-bases (status: current)
- [[Claudian-YishenTu]] — native Obsidian plugin embedding Claude Code; plan mode, @mention (status: current)
- [[Claude SEO]] — Tier 4 Claude Code skill for SEO analysis; 23 skills, 17 agents, 30 scripts at v1.9.0 (status: evergreen)
- [[Hermes International]] — French luxury design house; owner of HB8 and Kellymorphose codebases (status: evergreen)
- [[Kellymorphose]] — Hermes internal web app (Vue.js SPA, AWS S3) (status: developing)
- [[Confie]] — Hermes internal web app (Symfony 7.2, AWS ECS Fargate) (status: developing)
- [[ClearID]] — Genetec physical access control platform; Hermes identity management hub; now includes Access API v1 (OpenAPI 3.0.4) and developer portal docs (status: current)
- [[ClearID - MCH Interface]] — contract dates + photo sync Oracle HCM → ClearID (status: current)
- [[ClearID - SMI Interface]] — access rights sync SMI ↔ ClearID + user export (status: current)
- [[ClearID - IAM Interface]] — IAM identity mapping to ClearID (status: current)
- [[ClearID - Symfony API]] — Symfony 5.4 API layer for ClearID integrations (status: developing)
- [[ClearID - Site and Location Reference]] — Genetec ClearID site/location inventory; 106 sites, 305 locations, 93 schedules; Kanal is a site with no locations in prod (status: current)
- [[8 Penthièvre]] — Hermes building website with interactive SVG floor plans (status: current)
- [[Uplift Coach]] — fitness coaching SaaS; React 19 PWA + Python Lambda + DynamoDB; production at app.upliftcoach.fr (status: current)
- [[Shop Maintenance App]] — Hermes internal maintenance ticketing system; Symfony 4.4 + Angular 17 + API Platform (status: current)
- [[Epark]] — Hermes parking availability dashboard; jQuery SPA on AWS S3 (status: current)
- [[DOC4]] — Hermes sustainable real estate management; PHP 7.4 + Apache on AWS ECS Fargate (status: current)
- [[Savoir-faire]] — Hermes CFA apprentice evaluation portal; Symfony 5.4, PostgreSQL, session-based auth; retired (replaced by monodyssee) (status: retired)
- [[Monodyssee]] — Hermes CFA apprentice evaluation v2; Symfony 6.2, PHP 8.2, AWS ECS Fargate, S3; production active (status: current)
- [[Suivi des Incidents]] - Hermes incident tracking system; Symfony 6.2, MariaDB 10.5, S3, ECS Fargate; production active (status: current)
- [[Tilbury]] - Hermes boutique and store management system; Symfony 6.2, PHP 8.1, AWS ECS Fargate; manages Magasin/Site/Tandem, questionnaires, preferences (status: current)
- [[Suivi des Jours Travailles]] - Hermes working days tracking system; Symfony 6.4, PHP 8.1/8.2, MariaDB 10.5, ECS Fargate; tracks absences, exceptions, monthly worked days with manager/RH validation (status: current)
- [[Infocentre]] - Hermes business intelligence and reporting platform; PHP400 legacy framework, PHP 8.1, MySQL + Snowflake, AWS ECS Fargate; report generation, dashboards, batch exports, dual php400/phpallstat architecture; contains IAM → ClearID identity mapping spec (status: current)
- [[Captation Croco]] - Hermes crocodile leather traceability and quality control system; Symfony 6.4, PHP 8.2, MariaDB, AWS ECS Fargate; AS/400 legacy tables (55+ entities), FCC/FDR/FCP quality control cards, color tinting (Capteinte), defect library (défauthèque), S3 export, custom ECS deploy via jq+SSM+OIDC (status: current)

---

## Formation

- [[Formation Hermès]] — 7-module curriculum: overview → ClearID → CI/CD → architecture patterns → app deep dives → glossary → CFA evaluation
- [[CFA Hermès Evaluation Framework]] — CFA MyCampH EDSF evaluation framework: Jalons, Bilans, CAP Maroquinerie; 5-level competency scale; Monodyssee entity mapping

---

## Sources

- [[hermes-cicd-pipeline]] — 2026-06-04 | Hermes CI/CD Pipeline Repositories | 6 GitLab repos: baseline, release, containers, aws-components, aws-ecs | 7 wiki pages created
- [[cr-atelier6-shop-maintenance]] — 2026-06-04 | Atelier 6 CI/CD Shop Maintenance | Compte rendu réversibilité Niji→Talan, MEP en atelier, action plan
- [[mep-shop-maintenance-20250821]] — 2026-06-04 | MEP 2.0.8 Shop Maintenance | Fiche de livraison, bugs HSM-18/19/20, déploiement manuel
- [[niji-hermes-reversibilite-ateliers-6]] — 2026-06-04 | Macro-planning réversibilité ateliers | Mirroring GitLab, checklist MEP, planning 7 ateliers
- [[claude-obsidian-ecosystem-research]] — 2026-04-08 | web research across 16+ repos | 8 wiki pages created
- [[hb8-app-repo]] — 2026-06-02 | Nuxt.js static app for Hermes International | 5 wiki pages created
- [[klm-repo]] — 2026-06-02 | Vue.js SPA for Kellymorphose (Hermes) | 3 wiki pages created
- [[cnf-repo]] — 2026-06-02 | Symfony 7.2 app for Confie (Hermes) | 5 wiki pages created
- [[clearid-directory]] — 2026-06-03 | ClearID integration directory for Hermes | PHP scripts, Symfony API, MCH/SMI/IAM interfaces | 7 wiki pages created
- [[livret-penthievre-sources]] — 2026-06-03 | 8 Penthièvre building website | Vanilla HTML/CSS/JS, AWS S3, interactive SVG floor plans | 2 wiki pages created
- [[coach-app-repo]] — 2026-06-03 | Uplift Coach fitness SaaS | React 19 PWA, Python 3.13 Lambda, DynamoDB single-table, Stripe, Terraform, AWS | 3 wiki pages created
- [[shop-maintenance-app-repo]] — 2026-06-03 | Hermes Shop Maintenance App | Symfony 4.4 API, Angular 17 SPA, JWT auth, GitLab CI/CD, GCP
- [[epark-sources-aws]] — 2026-06-03 | Epark parking availability app | jQuery SPA, AWS S3, on-prem cron data pipeline | 4 wiki pages created
- [[doc4-sources]] — 2026-06-03 | DOC4 sustainable real estate app | PHP 7.4 + Apache, AWS ECS Fargate, Cognito OIDC, wkhtmltopdf | 2 wiki pages created
- [[clearid-access-api-swagger]] — 2026-06-05 | ClearID Access API OpenAPI spec | 12 endpoints, 20+ data models, OAuth2 auth; from swagger demo | 2 wiki pages created
- [[clearid-developer-guide]] — 2026-06-05 | ClearID Developer Portal overview | Authentication flows, endpoint catalog, code samples, webhooks | 1 wiki page created
- [[clearid-sites-locations-schedules]] — 2026-06-05 | ClearID Sites/Locations/Schedules production CSV | 106 sites, 305 locations, 93 schedules; from CIFS SMI scripts
- [[clearid-demo-locations]] — 2026-06-05 | ClearID demo locations API response | 11 locations across 5 sites; Kanal confirmed 0 locations; from live API call
- [[EHS-sources]] — 2026-06-05 | EHS web server source image | PHP + Apache Docker; dual MySQL schemas; OAuth2 Cognito; environmental health & safety audits
- [[savoir-faire-repo]] — 2026-06-08 | Savoir-faire (Mon Odyssée) source | Symfony 5.4, PostgreSQL, Dompdf; apprentice evaluation portal; retired
- [[monodyssee-repo]] — 2026-06-08 | Monodyssee v2 source | Symfony 6.2, PHP 8.2, AWS ECS Fargate, S3; production successor to Savoir-faire
- [[monodyssee-training-docs]] — 2026-06-08 | Monodyssee training evaluation PDFs | Jalon/Bilan/CAP documents, 5-level competency scale, maroquinerie skills taxonomy
- [[suivi-des-incidents-repo]] - 2026-06-08 | Suivi des Incidents (SDI) source | Symfony 6.2, PHP 8.1, MariaDB, S3, ECS Fargate; incident tracking
- [[tilbury-repo]] - 2026-06-08 | Tilbury boutique/store management source | Symfony 6.2, PHP 8.1, 19 entities, dual Magasin/Site model, bilingual Donneeref
- [[suivi-des-jours-travailles-repo]] - 2026-06-08 | Suivi des Jours Travailles (SDJ) source | Symfony 6.4, PHP 8.1/8.2, MariaDB 10.5, workforce tracking, AS400 legacy import
- [[infocentre-repo]] - 2026-06-09 | Infocentre BI reporting source | PHP400 legacy framework, PHP 8.1, MySQL + Snowflake, AWS ECS Fargate, mod_auth_mellon SAML; contains IAM → ClearID identity mapping spec
- [[captation-croco-repo]] - 2026-06-09 | Captation Croco (Armonie) source | Symfony 6.4, leather traceability, AS/400 tables (Cap001t-Cap055t), DOMPDF, custom ECS deploy via jq+SSM, Entra ID SAML; FCC/FDR/FCP quality control cards

---

## Questions

- [[How does the LLM Wiki pattern work]] — how the pattern works and why it outperforms RAG at human scale (status: developing)

---

## Comparisons

- [[Wiki vs RAG]] — when to use a wiki knowledge base versus RAG; verdict: wiki wins at <1000 pages
- [[claude-obsidian-ecosystem]] — feature matrix of 16+ Claude+Obsidian projects; where claude-obsidian wins and gaps

---

## Decisions

- [[2026-04-14-community-cta-rollout]] - Skool community CTA footer added to 6 skill repos with per-tool frequency rules (status: active)
- [[2026-04-15-slides-and-release-session]] - Claude SEO v1.9.0 slides (15-slide HTML deck) + GitHub release v1.9.0 with PDF asset (status: complete)
- [[2026-04-15-release-report-session]] - Claude SEO v1.9.0 Release Report PDF: dark theme, 13 pages, WeasyPrint layout fixes, Challenge v2 added (status: complete)
- [[2026-04-14-claude-seo-v190-session]] - Claude SEO v1.9.0 Pro Hub Challenge integration: 5 submissions, 4 new skills, 4 review rounds, cybersecurity audit (status: complete)

---

## Sessions

- [[2026-06-02-hb8-ingestion-session]] — vault health check + HB8 repo ingestion + save pipeline demo (status: complete)

---

## Domains

<!-- Add domain entries here after scaffold -->
