---
type: meta
title: "Hot Cache"
updated: 2026-06-09T17:30:00
tags:
  - meta
  - hot-cache
status: evergreen
related:
  - "[[index]]"
  - "[[log]]"
  - "[[overview]]"
  - "[[hermes-cicd-pipeline]]"
  - "[[Hermes CI-CD Pipeline]]"
  - "[[GitLab CI-CD Component Architecture]]"
  - "[[AWS ECS Deployment Pattern]]"
  - "[[Container Build Pipeline]]"
  - "[[Hermes International]]"
---

# Recent Context

Navigation: [[index]] | [[log]] | [[overview]]

## Last Updated

2026-06-09: **Ingested ClearID SMI Droits d'Acces PHP scripts** from `\\cifs-frsel\...\Interface SMI-ClearID (droits d'acces)\PHP`. 14 PHP 8.2 scripts managing access rights, locations, schedules, and contractor (SST) imports between Hermes SMI and Genetec ClearID. Key scripts: `ImportIdentitiesAccessClearId.php` (PATCH `/locations/{id}/accesses` with CSV of schedule assignments), `ImportLocationsSchedulesClearId.php` (PATCH `/locations/{id}/schedules`), `ImportPrestatairesClearId.php` (POST identities with custom fields for external contractors, hardcoded siteId `72b455d1...`), `SynchronizeIdentitiesClearId.php` (batch synchronize all identities). All scripts use OAuth2 client_credentials, token refresh every 2,000 records, and generate cache arrays (`array_identities_prod.inc.php` ~14MB, etc.). Operational doc v1.0 dated 08/06/2026. Updated [[ClearID - SMI Interface]] entity + [[clearid-smi-droits-acces-scripts]] source page.

Previous: 2026-06-09: **Ingested Captation Croco (Armonie)** from `applications-departementales/symfony/captation-croco/sources-armonie`. Symfony 6.4 on PHP 8.2, Bootstrap 5.3 + DataTables + jQuery frontend, DOMPDF, PHPSpreadsheet, AWS SDK PHP, MariaDB. Domain: crocodile leather traceability and quality control — tannery purchase orders (Cap001t: species, finish, color, nbPeauxCmd), color tinting (Capteinte composite key), FCC/FDR/FCP quality control cards, defect library (défauthèque), S3 export. Unique custom ECS deploy via jq task-definition mutation + SSM parameter store + OIDC token auth (not standard Hermes cicd-aws-ecs component). Microsoft Entra ID SAML. 55+ AS/400 legacy entities (Cap001t-Cap055t). Created [[Captation Croco]] entity + [[Captation Croco App]] concept + [[captation-croco-repo]] source page.

Previous: 2026-06-09: **Ingested InfoCentre (INFOCENTRE)** from `applications-departementales/infocentre/infocentre-sources`. PHP400 legacy framework on PHP 8.1, Debian 12 + Apache 2.4 + mod_auth_mellon, MySQL (RDS Aurora: maillage, phphimlg) + Snowflake (pdo_snowflake), AWS ECS Fargate. Domain: business intelligence, reporting, dashboards, batch exports, statistics (phpallstat sub-app). Dual php400/phpallstat architecture with 70+ report engine files, desk dashboard module, AS400 legacy date formats (cyyjul). Discovered `iam gnt.txt` — authoritative Hermes IAM → ClearID identity mapping spec with 20 fields. Created [[Infocentre]] entity + [[Infocentre App]] concept + [[infocentre-repo]] source page. Updated [[ClearID - IAM Interface]] with mapping data.

Previous: 2026-06-08: **Ingested Suivi des Jours Travailles (SDJ)** from `applications-departementales/symfony/suivi-des-jours-travailles`. Symfony 6.4 on PHP 8.1/8.2, MariaDB 10.5, standard Hermes CI/CD to ECS Fargate. Domain: workforce tracking (absences, exceptions, monthly worked days, manager/RH validation). AS400 legacy data import pipeline (CSV windows-1252). 20+ entities, 7 batch commands (import, batch emails, quarterly export, reminders). Created [[Suivi des Jours Travailles]] entity + [[Suivi des Jours Travailles App]] concept + source page.

Previous: 2026-06-08: **Ingested Tilbury (TIL)** from `applications-departementales/symfony/tilbury`. Symfony 6.2 on PHP 8.1, 19 entities, standard Hermes CI/CD to ECS Fargate. Domain: boutique/store management with Magasin/Site/Tandem pairing, thematic questionnaires, preferences, language skills, bilingual Donneeref reference system. Created [[Tilbury]] entity + [[Tilbury App]] concept + canvas.

Previous: 2026-06-08: **Ingested Suivi des Incidents (SDI)** from `applications-departementales/symfony/suivi-des-incidents`. Symfony 6.2 on PHP 8.1, MariaDB 10.5, S3 dual storage, ECS Fargate. 25 entities, 17 controllers, 7 batch commands. Unique features: numeric glossaire translation system, social media incident tracking (auto-adds retail.emergency@hermes.com), AS400 legacy migration, N/C/R confidentiality with role-based mailing lists. Created [[Suivi des Incidents]] entity + [[Suivi des Incidents App]] concept + canvas.

Previous: 2026-06-08 — Analyzed Monodyssee training evaluation documents (strategic sample of 7 PDFs/ZIPs from monodyssee.hermes.com/documents/pdf). Extracted text from ~100 PDFs via pdftotext. Created [[CFA Hermès Evaluation Framework]] concept page: Jalon/Bilan/CAP document types, 5-level competency scale, savoir-faire taxonomy, savoir-être framework, mapping to Monodyssee entities. Updated [[Formation Hermès]] with Module 7.

Previous: 2026-06-08 — Ingested Monodyssee (v2 successor to Savoir-faire). Symfony 6.2, PHP 8.2.1, AWS ECS Fargate + S3. New features: CKEditor, PHPSpreadsheet, Bilan/Attestation/Module system, DB sessions, full CI/CD pipeline.

Previous: 2026-06-08 — Created [[Formation Hermès]] — 6-module training curriculum from wiki knowledge.

Previous: 2026-06-08 — Ingested Savoir-faire (Mon Odyssée) Symfony 5.4 repository. CFA Hermès MyCampH apprentice evaluation portal. Retired project; superseded by monodyssee.

Previous: 2026-06-05 — Ingested EHS web server source image (`ehs-sources`). Hermes Environmental Health & Safety departmental application.

Previous: 2026-06-05 — Ingested ClearID demo identities API response (live call). 23 identities on demo account `x9qxn4iaq2`.

Previous: 2026-06-05 — Ingested ClearID Access API + Developer Portal (2 URLs).

---

## Key Recent Facts

### Suivi des Jours Travailles (New)
- Symfony 6.4 departmental app tracking employee absences and worked days
- Shared Hermes template v6.0.1 with SDI and Tilbury (same Docker/CI/CD stack)
- Key entities: Absences, JoursTravail, Validations, Exceptions, EnvoiMailN1
- Role hierarchy: SUPER_ADMIN > ADMIN > ADMIN_RH > RH > MANAGER > USER
- AS400 legacy import via CSV (windows-1252, semicolon) into staging tables SDJ001T..SDJ006T

### ClearID Site/Location Mapping (New)
- **106 sites, 305 locations, 93 schedules** across production account `j3gg5ror3f`
- **Kanal = site, not location**: `siteId: 6e6159b4-220f-458f-b5e6-82313d334327`, no associated locations or schedules in prod export
- **Loupes**: 1 location (`0 EXT EXTERIEUR [SECTEUR 1]`, `aa00ffde-3b50-4527-8f72-bf63fdc99303`) with 9 schedules
- Location naming pattern: `{NUMÉRO} {TYPE} {DESCRIPTION} [SECTEUR {N}]`
- Query in Insomnia: `GET {{locationservice_url}}/api/v3/accounts/{{accountId}}/locations?SiteId={{siteId}}`

### ClearID Access API / Location Service v3 (Updated)
- **Access API v1 spec** (12 endpoints, 5 tag groups) documented in developer portal but **NOT deployed on Hermes tenant**
- **Location Service v3 endpoint discovered:** `GET /api/v3/accounts/{id}/locations/{id}/accesses` on `locationservice.eu.clearid.io`; returns `AccessesModel` with `accessModels` (individual) + `teamAccessModels` (team/role)
- **New script:** `ListAccessesByLocationClearId.php` queries Location Service v3 directly, exports CSV with `accessType` column (`identity` or `team`)
- **Deprecated:** `scheduleId` replaced by `scheduleMapId` throughout
- **Hermes integration:** No separate `accessapi` host; existing SMI scripts use identity/location v3 endpoints

## Key Recent Facts

### Hermes CI/CD Pipeline
- **6 repos** under `hermesintl/template-cicd/`: baseline-component, cicd-baseline (legacy), release-component, cicd-containers-build-component, cicd-aws-components, cicd-aws-ecs (legacy)
- **Architecture**: GitLab CI/CD components (`include: component:`) replacing monolithic templates
- **Baseline** (mandatory): SonarQube + GitGuardian + Vault + semantic release
- **Release component**: Multi-language (Python poetry/pipenv, Node.js npm/yarn, Java maven/gradle, .NET dotnetcli, Container kaniko)
- **Container build**: Docker or Kaniko (rootless) → push to JFrog Artifactory or Harbor; Hadolint linting enforces Container Factory sourcing
- **AWS deployment**: ECS Fargate, Lambda (image/zip), API Gateway; all via OIDC `lzm-glb-gitlab-oidc-app` role
- **Security**: Vault-first secrets, GitGuardian commit scanning, SonarQube quality gates, Hadolint container validation, Trivy vulnerability scanning
- **Environments**: dev/tst/chk auto-deploy from main; acc/prd only via git tags
- **Registries**: JFrog Artifactory (primary), Harbor (alternative), AWS ECR (deployment-only)

### Hermes Codebases (consuming the pipeline)
- All 7 Hermes projects use these components: HB8, KLM, CNF, DOC4, Epark, 8 Penthièvre, Shop Maintenance
- CNF and DOC4 deploy to ECS Fargate; HB8/KLM/Epark/8 Penthièvre to S3; Shop Maintenance to GCP

## Recent Changes

- Created: [[captation-croco-repo]], [[Captation Croco]], [[Captation Croco App]]
- Created: [[infocentre-repo]], [[Infocentre]], [[Infocentre App]]
- Updated: [[index]] (107 pages, 27 sources), [[hot]], [[log]], [[entities/_index]], [[concepts/_index]], [[sources/_index]], [[Hermes International]], [[ClearID - IAM Interface]]
- Raw source filed: `.raw/infocentre/`, `.raw/captation-croco/`
- Key discovery: `iam gnt.txt` — authoritative Hermes IAM → ClearID identity field mapping (20 fields)

## Active Threads

- Vault now covers 10+ Hermes departmental applications: HB8, KLM, CNF, DOC4, Epark, 8 Penthièvre, Shop Maintenance, EHS, InfoCentre, Captation Croco, Monodyssee, Savoir-faire, SDI, SDJ, Tilbury + CI/CD pipeline + ClearID integrations + coach-app SaaS
- Possible follow-up: deep dive into individual CI/CD component YAML templates, or comparison with GitHub Actions
- Plugin version: 1.9.2
