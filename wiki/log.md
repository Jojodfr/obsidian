---
type: meta
title: "Operation Log"
updated: 2026-06-09
tags:
  - meta
  - log
status: evergreen
related:
  - "[[index]]"
  - "[[hot]]"
  - "[[overview]]"
  - "[[sources/_index]]"
---

# Operation Log

Navigation: [[index]] | [[hot]] | [[overview]]

Append-only. New entries go at the TOP. Never edit past entries.

Entry format: `## [YYYY-MM-DD] operation | Title`

Parse recent entries: `grep "^## \[" wiki/log.md | head -10`

## [2026-06-09] ingest | ClearID SMI Droits d'Acces PHP scripts

- Source: `\\cifs-frsel\etudes\Applications Départementales\PEPS France\07 - Doc DSI\ClearID (interface, plugins)\Interface SMI-ClearID (droits d'acces)\PHP`
- Type: codebase (14 PHP 8.2 scripts + operational doc)
- Created: [[clearid-smi-droits-acces-scripts]] source page
- Updated: [[ClearID - SMI Interface]] entity (full rewrite with 14-script inventory, API endpoints, procedures), [[ClearID Integration Architecture]] concept (enriched SMI flow table), [[index]] (108 pages, 28 sources), [[hot]], [[log]], [[entities/_index]], [[sources/_index]], [[sources/clearid-smi-droits-acces-scripts]]
- Key findings:
  - 14 scripts: 4 list+audit scripts (identities, principals, deleted, inactive, no-site, no-principal), 3 reference builders (sites, locations, schedules), 3 import scripts (access rights, location/schedules, contractors), 1 sync, 1 repair
  - `ImportIdentitiesAccessClearId.php`: PATCH `/api/v3/accounts/{id}/locations/{id}/accesses`, CSV `identityIds;locationId;scheduleId;startDateTimeUtc;endDateTimeUtc;description`, UTF-8 BOM + semicolons, date conversion `d/m/Y H:i`→ISO 8601 UTC
  - `ImportPrestatairesClearId.php`: POST `/api/v3/accounts/{id}/identities` with custom fields for SST (external contractors), deterministic `externalId = SST-{md5(...)}`, hardcoded `siteId: 72b455d1-5e21-46a1-afef-390b2f95b67d`
  - `SynchronizeIdentitiesClearId.php`: batch synchronize all identities via `POST /identities/synchronize`, excludes 2 hardcoded GUIDs
  - Token refresh every 2,000 records; all scripts use OAuth2 client_credentials; SSL verification disabled (`CURLOPT_SSL_VERIFYHOST/VERIFYPEER = false`)
  - Generated cache arrays: `array_identities_prod.inc.php` (~14MB), `array_locations_prod.inc.php` (~64KB), `array_schedules_prod.inc.php` (~8KB), etc.
  - Operational doc v1.0 (2026-06-08): import procedures, prerequisites (disable end-date limit in ClearID UI during import), CSV formatting rules
  - Environments: demo (`x9qxn4iaq2`, `*.demo.clearid.io`) and prod (`j3gg5ror3f`, `*.eu.clearid.io`)
- Total vault state: 108 pages, 28 sources

---

## [2026-06-09] ingest | Captation Croco Symfony repository

- Source: `applications-departementales/symfony/captation-croco/sources-armonie` (local clone at `~/work/captation-croco`)
- Type: codebase
- Created: [[captation-croco-repo]] source page, [[Captation Croco]] entity, [[Captation Croco App]] concept page
- Updated: index, hot, log, entities/_index, sources/_index, concepts/_index, Hermes International
- Key findings:
  - Symfony 6.4 LTS, PHP 8.2, MariaDB, Doctrine ORM 2.16 with DBAL 2.13.9 (legacy)
  - Domain: crocodile leather traceability & quality control at Hermès
  - 55+ AS/400 legacy entities (Cap001t-Cap055t) mapped with Doctrine PHP 8 attributes, fixed-width strings
  - Core domain: tannery orders (Cap001t: species, finish, color, nbPeauxCmd), color tinting (Capteinte 4-part composite key), defect categories (CategorieDefautsFdr), delivery view (Vuem3t)
  - Quality control cards: FCCController (48KB), FDRController (30KB), FCPController (13KB), BackOfficeController (46KB), RACController
  - PDF generation via DOMPDF, Excel via PHPSpreadsheet, S3 export via AWS SDK PHP
  - Frontend: Webpack Encore + Bootstrap 5.3 + DataTables + jQuery
  - Auth: Microsoft Entra ID SAML via mod_auth_mellon + temporary Symfony User entity
  - **Custom CI/CD**: NOT using standard Hermes cicd-aws-ecs component; instead: OIDC token → ECR push → custom deploy stage reads env.properties → jq task-definition mutation → ECS service update → SSM parameter writeback
  - Environments: CHK (auto), PRD (manual on main); dev/tst/acc disabled
  - AWS accounts: CHK `225989339017`, PRD `588738579322`, region `eu-west-3`
  - Docker Compose local dev: app + nginx LB + MariaDB + Adminer
  - Version 1.0.0 (2025-09-17) — relatively new application
- Total vault state: 107 pages, 27 sources

---

## [2026-06-09] ingest | InfoCentre PHP400 repository

- Source: `applications-departementales/infocentre/infocentre-sources` (local clone at `~/work/infocentre-sources`)
- Type: codebase
- Created: [[infocentre-repo]] source page, [[Infocentre]] entity, [[Infocentre App]] concept page
- Updated: index, hot, log, entities/_index, sources/_index, concepts/_index, Hermes International, ClearID - IAM Interface
- Key findings:
  - PHP400 legacy framework (IBM iSeries/AS400 origin), not Symfony
  - Dual app architecture: `php400/` (core framework + BI) + `phpallstat/` (statistics sub-app that mounts php400 framework)
  - PHP 8.1 + Apache 2.4 + mod_auth_mellon (SAML SSO) on Debian 12
  - Dual databases: MySQL RDS Aurora (`maillage`, `phphimlg`) + Snowflake (`pdo_snowflake` extension + `snowcd` diagnostic)
  - 70+ report engine files: creation (crt), SQL builder, display (dsp/dspgend), tables (tbl), tabs, sharing (partage), security (secu)
  - CI/CD: Hermes template components (gitleaks 1.1.0, SonarQube 2.4.0, containers 2.1.0, aws-ecs 4.2.1, release 2.1.0)
  - Only dev and prd environments enabled (tst/chk/acc/ppd all false)
  - Extensive debug tooling: Adminer, query_db, query_snowflake, tinyfilemanager, servercheck, test_pdo_snowflake_tst/prd
  - Heavy AS400 legacy: `os400/` directory, `cyyjul`/`cyymmdd` date formats, PHP400 framework itself
  - **IAM → ClearID mapping spec** (`iam gnt.txt`): 20-field mapping between Hermes IAM and Genetec ClearID identities, including custom fields (login_atlas, collaborator, start_date, end_date, division, etablissement)
- Total vault state: 104 pages, 26 sources

---

## [2026-06-08] ingest | Suivi des Jours Travailles Symfony repository

- Source: `applications-departementales/symfony/suivi-des-jours-travailles` (local clone)
- Type: codebase
- Created: [[suivi-des-jours-travailles-repo]] source page, [[Suivi des Jours Travailles]] entity, [[Suivi des Jours Travailles App]] concept page
- Updated: index, hot, log, entities/_index, sources/_index, concepts/_index, Hermes International
- Key findings: Symfony 6.4 on PHP 8.1/8.2, MariaDB 10.5, 20+ entities, 7 batch commands, AS400 legacy data import pipeline (CSV windows-1252, semicolon separator, staging tables SDJ001T..SDJ006T), custom Authenticator with role hierarchy (SUPER_ADMIN > ADMIN > ADMIN_RH > RH > MANAGER > USER), ECS Fargate deploy via standard Hermes CI/CD template, wkhtmltopdf for PDF generation, N+1 email notification workflow (EnvoiMailN1 entity)

---

## [2026-06-08] ingest | Tilbury Symfony repository

- Source: `applications-departementales/symfony/tilbury` (24 entity PHPs + README + composer.json + .gitlab-ci.yml)
- Type: codebase
- Created: [[tilbury-repo]] source page, [[Tilbury]] entity, [[Tilbury App]] concept page, [[tilbury-app-architecture.canvas]] canvas
- Updated: index, hot, entities/_index, sources/_index, concepts/_index, Formation Hermès, manifest
- Key findings: 19 entities (Magasin boutique 24 fields, Site location 23 fields, Tandem pairing with date ranges), dual-entity discriminator pattern (usertype=M/S), bilingual Donneeref reference system, standard Hermes CI/CD pipeline to ECS Fargate

---

## [2026-06-08] analyze | Monodyssee training evaluation documents (PDF sample)

- Type: document analysis
- Trigger: user approved strategic sample download from `monodyssee.hermes.com/documents/pdf`
- Source: 7 ZIP/PDF files → extracted text from ~100 PDFs via pdftotext
- Summary: [[monodyssee-training-docs]]
- Pages created:
  - [[monodyssee-training-docs]] — Source page: 7 downloaded files, text-extracted samples; Jalon/Bilan/CAP document types; formateurs: PALUMBO, VRIGNAUD, COULAUD, SAINSON
  - [[CFA Hermès Evaluation Framework]] — Concept page: full evaluation framework (Jalons, Bilans, CAP), 5-level competency scale, savoir-faire taxonomy, savoir-être competencies, Monodyssee entity mapping
- Pages updated: [[Monodyssee]] (added training docs section), [[Formation Hermès]] (added Module 7), [[index]], [[hot]], [[log]], [[concepts/_index]], [[sources/_index]]
- Key findings:
  - Three document types: Jalon (technical per-product), Bilan (global periodic), CAP Maroquinerie (professional certification)
  - 5-level scale: Non évalué → Non acquis → EC d'acquisition (-) → EC d'acquisition (+) → Acquis
  - Jalons evaluate: griffage, couture sellier/retournée, astiquage (ponçage/filetage/tranche), parage, collage, montage
  - Bilans evaluate: Savoir-être (9 items), Savoir-faire (11+ items), Santé-sécurité, Règlement
  - CAP evaluates: C1 (documents), C2 (product elements), C3 (preparation)
  - PDF footer on all docs: "Document interne et confidentiel — Hermès Propriété de l'École Hermès des savoir-faire"
  - Products evaluated: Constance slim, Sac Rachel, Couvre cahier & marque-page
  - All documents signed by formateur with date
- Total vault state: 95 pages, 23 sources

---

## [2026-06-08] ingest | Suivi des Incidents (SDI) - Hermes incident tracking

- Type: ingest
- Trigger: user cloned `https://gitlab.com/hermesintl/applications-departementales/symfony/suivi-des-incidents` to `~/work/suivi-des-incidents`
- Source: `.raw/suivi-des-incidents/` (31 files: README, composer.json, .gitlab-ci.yml, doctrine.yaml, bundles.php, 25 Entity PHP files)
- Summary: [[suivi-des-incidents-repo]]
- Pages created:
  - [[suivi-des-incidents-repo]] - Source page: 31 files; Symfony 6.2 on PHP 8.1; MariaDB 10.5; S3 dual storage; incident tracking
  - [[Suivi des Incidents]] - Entity page: Hermes incident tracking system; SF 6.2, MariaDB, S3, ECS Fargate; 25 entities, 17 controllers
  - [[Suivi des Incidents App]] - Concept page: application architecture; glossaire translation, social media feature, AS400 legacy import, alert system, dual file storage, 7 batch commands
- Pages updated: [[index]] (98 pages, 24 sources), [[hot]], [[log]], [[entities/_index]], [[concepts/_index]], [[sources/_index]], [[Hermes International]], [[Formation Hermes]]
- Key findings:
  - Based on Hermes Symfony project type v6.0.1
  - 25 entities: 4 core (Incident, Pj, Mails, HistoModifNotif) + 10 RefData + 8 Admin + Legacy SDI001T + User
  - 17 controllers: 3 SDI domain, 7 system, 4 admin, 3 BO
  - 7 commands: batch mails, import CSV, reclassement, refdatas glossaire, division PJ, initial users, batch errors
  - Dual file storage: local (dev) + S3 (prod) with presigned URLs
  - Social media feature: auto-detects type numglossaire=1001, auto-adds retail.emergency@hermes.com to alerts
  - Translation: numeric glossaire keys (tr->trans(267)) not standard Symfony i18n
  - Auth: Symfony Security + VerifyEmailBundle, custom voters, 4 roles (USER, AUDIT, ADMIN, SUPER_ADMIN)
  - Confidentiality: N/C/R with mailing list filtering
  - Full CI/CD: same Hermes component pipeline as other projects
  - ECS Fargate on AWS, dev auto-deploy from master, prd manual from tag
  - AS400 legacy migration: complex CSV import from AGRlib.SDI001T with character encoding fixes
- Total vault state: 98 pages, 24 sources

---

## [2026-06-08] ingest | Monodyssee — Symfony 6.2 successor to Savoir-faire

- Type: ingest
- Trigger: user cloned `https://gitlab.com/hermesintl/applications-departementales/savoir-faire/monodyssee` to `~/work/monodyssee`
- Source: `.raw/monodyssee-repo/` (478 files: README, composer.json, CHANGELOG, .gitlab-ci.yml, src/, config/, templates/, docker/, etc.)
- Summary: [[monodyssee-repo]]
- Pages created:
  - [[monodyssee-repo]] — Source page: 478 files; Symfony 6.2 on PHP 8.2.1; AWS ECS Fargate + S3; production active
  - [[Monodyssee]] — Entity page: CFA Hermès MyCampH EDSF v2; Symfony 6.2; bilan/attestation/module system; v1.13.0 active
  - [[Monodyssee App]] — Concept page: architecture (35 controllers, 12+ new entities), AWS S3 file upload, CKEditor, PHPSpreadsheet, CRON, full CI/CD pipeline
- Pages updated: [[index]] (92 pages, 22 sources), [[hot]], [[log]], [[entities/_index]], [[concepts/_index]], [[sources/_index]], [[Hermes International]], [[Savoir-faire]], [[Formation Hermès]]
- Key findings:
  - Major upgrade: Symfony 5.4/PHP 7.2 → Symfony 6.2/PHP 8.2.1
  - Doctrine mapping: annotations → PHP 8 Attributes (#[ORM\])
  - Full CI/CD pipeline vs savoir-faire's minimal gitleaks
  - AWS S3 dual file storage (local dev, S3 prod)
  - New entities: Bilan, Attestation, Module, AutoEvaluation, LivretDeSuivi, ExportActions, Metiers, Questionnaire, Entretien
  - CKEditor rich text, PHPSpreadsheet Excel, League CSV
  - Database sessions (v1.13.0), structured error logging
  - CRON service for automated DB exports with audit trail
  - ECS Fargate deployment: `EcsCluster-Prd-Monodyssee-external-01`
  - SonarQube, GitGuardian, Harbor registry, S3 variable copy
  - v1.9.0: Module categories, module suivi, competency rules, school/job display
  - v1.10.0: School and job user management
  - v1.13.0: DB sessions, error_log, migration migrate
- Total vault state: 92 pages, 22 sources

---

## [2026-06-08] ingest | Savoir-faire (Mon Odyssée) — Symfony 5.4 CFA evaluation portal

- Type: ingest
- Trigger: user cloned `https://gitlab.com/hermesintl/applications-departementales/savoir-faire/savoir-faire` to `~/work/savoir-faire`
- Source: `.raw/savoir-faire/` (201 files: README, composer.json, src/Controller/*.php, src/Entity/*.php, config/, templates/, tests/, etc.)
- Summary: [[savoir-faire-repo]]
- Pages created:
  - [[savoir-faire-repo]] — Source page: 201 files ingested; Symfony 5.4 on PHP 7.2.5+; PostgreSQL 13; Dompdf + wkhtmltopdf; retired project
  - [[Savoir-faire]] — Entity page: CFA Hermès MyCampH apprentice evaluation portal; Symfony 5.4; campaign→evaluation→signature workflow
  - [[Savoir-faire App]] — Concept page: architecture (18 controllers, 12 entities, Guard auth, PDF generation), data model, evaluation workflow
- Pages updated: [[index]] (88 pages, 21 sources), [[hot]], [[log]], [[entities/_index]], [[concepts/_index]], [[sources/_index]], [[Hermes International]]
- Canvas created: `wiki/canvases/savoir-faire-app-architecture.canvas`
- Key findings:
  - Domain: CFA Hermès MyCampH — evaluation campaigns for apprentices
  - Mix of Doctrine annotations (legacy) and attribute syntax (newer)
  - No API Platform, no JWT — classic Symfony form-login with session-based auth
  - Reponses table is a **denormalized clone** of Questions per evaluation (bulk INSERT on campaign creation)
  - Status workflow: 1=active, 4=closed, 5=abandoned
  - Email notification to formateurs on campaign creation via CampagnesMailer
  - Dual auth: `User` (Symfony security) + `Utilisateur` (business profile with ECOLE_ID and ROLE_ID)
  - Role-based filtering: role 3 = school-only, role 4 = admin
  - **Retired**: README explicitly redirects to `monodyssee`
  - Minimal CI/CD: only gitleaks template; SonarQube commented out
- Total vault state: 88 pages, 21 sources

---

## [2026-06-05] ingest | EHS Sources — Docker image source for EHS web server

- Type: ingest
- Trigger: user cloned `hermesintl/applications-departementales/ehs/ehs-sources` and requested wiki ingest
- Source: `.raw/ehs/ehs-sources.md` + `.raw/ehs/CHANGELOG.md`
- Summary: [[EHS-sources]]
- Pages created:
  - [[EHS-sources]] — Source page: EHS web server Docker image; PHP + Apache; dual MySQL schemas; OAuth2 Cognito; admin attachment rights in v1.11.0
  - [[EHS]] — Entity page: Hermes Environmental Health & Safety departmental app; audit campaigns, risk/culture eval, site assessments
- Pages updated: [[index]] (86 pages, 20 sources), [[hot]], [[log]], [[sources/_index]], [[entities/_index]]
- Key findings:
  - EHS is a PHP/Apache Docker app (not Symfony — plain PHP image source)
  - Dual-database architecture: generic `DB_*` + EHS-specific `EHS_DB_*` (schemas `ehs` and `phphiehs`)
  - Auth: self-enrollment Cognito + OAuth2; FQDN `ehs.internal` required for dev
  - Version v1.11.0 (2026-04-27): admin right for audit attachments; import eval ref fix
  - v1.9.0 (2025-09-24): **Instant Carbon Footprint feature removed**
  - CI/CD tracked on `master` branch with SonarQube quality gate
  - Uses Hermes baseline pipeline (similar to DOC4 and Shop Maintenance)
- Total vault state: 86 pages, 20 sources

---

## [2026-06-05] ingest | ClearID Demo Identities API response

- Type: ingest
- Trigger: user sent live API response from `GET /api/v3/accounts/{accountId}/identities`
- Source: `.raw/clearid-demo-identities.json`
- Summary: [[clearid-demo-identities]]
- Pages created:
  - [[clearid-demo-identities]] — Source page: 23 demo identities; response model with privateData/companyData/systemData breakdown; custom fields catalog
- Pages updated: [[ClearID]] (added Identity API v3 section), [[index]] (84 pages, 19 sources), [[hot]], [[log]], [[sources/_index]]
- Key findings:
  - Identity response is a rich nested object with 3 data sections: `privateData` (PII), `companyData` (HR), `systemData` (integration)
  - 14 Hermes custom fields: `login_atlas`, `division`, `collaborator`, `start_date`, `end_date`, `mch_etablissement`, `manager_id`, `abu_building_name`, `mch_contract_type`, `work_location`, `cost_center`, `external_company`, `manager_email`, `iam_displayname`
  - `lastModifiedByPrincipalType`: `User`, `Service`, or `SystemService` — indicates automation vs human touch
  - `workerTypeDescription`: `CDI`, `CDD`, `EXT` (external), or empty
  - Approver chain in `companyData.approvers[]` as `{approverId}` objects (distinct from Location API's top-level `approvers[]`)
  - Notable identity: `394b901c-e635-49e7-ad15-53cb94da7ab8` (Christophe GROTTE, `ordinal: 53`, approver for ENTREE CARROUSEL)
  - Updated Insomnia environment: `identityId` set to Christophe GROTTE's GUID
- Total vault state: 84 pages, 19 sources

---

## [2026-06-05] ingest | ClearID Sites, Locations & Schedules CIFS export

- Type: ingest
- Trigger: user asked for "location id from kanal" and discovered CIFS data
- Source: `.raw/clearid-sites-locations-schedules.csv` + `.raw/clearid-array-locations-sites-prod.php` (from `\\cifs-frsel\\...\\ClearID (interface, plugins)\\linux\\clearid\\accctrl\\csv\\`)
- Summary: [[clearid-sites-locations-schedules]]
- Pages created:
  - [[clearid-sites-locations-schedules]] — Source page: CSV export from production ClearID (2025-11-23); 2,666 rows; 106 sites, 305 locations, 93 schedules
  - [[ClearID - Site and Location Reference]] — Entity page: notable sites (Pantin-Kanal, Loupes), naming conventions, schedule types, Insomnia query patterns
- Pages updated: [[ClearID]] (added Site and Location Reference to related + data sources table), [[index]], [[hot]], [[log]], [[entities/_index]], [[sources/_index]]
- Key findings:
  - **Kanal is a site, not a location**: `siteId: 6e6159b4-220f-458f-b5e6-82313d334327`, zero locations in prod export (2025-11-23)
  - Loupes has 1 location (`0 EXT EXTERIEUR [SECTEUR 1]`) and 9 schedules
  - Location naming pattern: `{NUMÉRO} {TYPE} {DESCRIPTION} [SECTEUR {N}]`
  - PHP array `$array_location_site[locationId] = siteId` exists for SMI script mapping
- Total vault state: 82 pages, 18 sources

---

## [2026-06-05] ingest | ClearID Access API + Developer Portal

- Type: ingest
- Trigger: user provided two URLs: https://swaggest.demo.clearid.io/ (swagger) and https://developer.genetec.com/.../overview-of-the-clearid-api (dev guide)
- Source: `.raw/clearid-access-api-swagger.json` + `.raw/clearid-access-api-swagger.md` + `.raw/clearid-developer-guide.md` (fetched)
- Summary: [[clearid-access-api-swagger]], [[clearid-developer-guide]]
- Pages created:
  - [[clearid-access-api-swagger]] — Source page: OpenAPI 3.0.4 Access API spec with 12 endpoints
  - [[clearid-developer-guide]] — Source page: developer portal overview (auth, endpoints, code samples)
  - [[ClearID Access API]] — Concept page: full endpoint reference + data model tables + error handling
  - [[ClearID Developer Portal]] — Concept page: auth flows, integration patterns, data models
- Pages updated: [[ClearID]] (added Access API section), [[index]], [[hot]], [[log]], [[sources/_index]], [[concepts/_index]]
- Key insights:
  - Access API v1 is separate from the identity APIs used by existing Hermes PHP scripts (v3/v4)
  - 5 tag groups: Accesses, IdentityAccesses, RoleAccesses, ScheduleMaps, VisitorAccesses
  - `scheduleId` deprecated in favor of `scheduleMapId` across all models
  - Optimistic concurrency on ScheduleMap updates via `version` field
  - Developer portal uses SPA routing — sub-pages returned 404 to direct fetch
- Total vault state: 80 pages, 17 sources

---

## [2026-06-04] ingest | Hermes CI/CD Pipeline (6 repos)

- Type: ingest
- Trigger: user requested ingestion of all CI/CD repos from `~/work/cicd/`
- Source: `.raw/hermes-cicd-pipeline.md` — synthesized from 6 GitLab repos
- Summary: [[hermes-cicd-pipeline]]
- Pages created:
  - [[hermes-cicd-pipeline]] — Source summary of 6 CI/CD repos (baseline, release, containers, aws-components, aws-ecs)
  - [[Hermes CI-CD Pipeline]] — Entity page for the Hermes CI/CD platform
  - [[GitLab CI-CD Component Architecture]] — Concept: modular GitLab component pattern
  - [[Hermes CI-CD Baseline Template]] — Concept: mandatory baseline jobs (SonarQube, GitGuardian, Vault, release)
  - [[AWS ECS Deployment Pattern]] — Concept: standard ECS Fargate deployment flow
  - [[Container Build Pipeline]] — Concept: Docker/Kaniko build + Hadolint + Container Factory validation
- Pages updated: [[index]], [[hot]], [[entities/_index]], [[concepts/_index]], [[sources/_index]]
- Key insight: Hermes uses a component-based CI/CD architecture with 6 reusable GitLab components; all auth via Vault+OIDC; dual registry (JFrog/Harbor); AWS deployments via LZv2 OIDC roles.

---

## [2026-06-04] ingest | Shop Maintenance Reversibility Documents (3 PDFs)

- Type: ingest
- Trigger: user provided 3 PDFs from Downloads folder
- Sources:
  - `.raw/cr-atelier6-shop-maintenance.pdf` → text extracted via pdftotext
  - `.raw/mep-shop-maintenance-20250821.pdf` → text extracted via pdftotext
  - `.raw/niji-hermes-reversibilite-ateliers-6.pdf` → text extracted via pdftotext
- Summary: [[cr-atelier6-shop-maintenance]], [[mep-shop-maintenance-20250821]], [[niji-hermes-reversibilite-ateliers-6]]
- Pages created:
  - [[cr-atelier6-shop-maintenance]] — Compte rendu atelier 6 CI/CD (03/06/2026); participants Hermès/Niji/Talan; MEP de 3 correctifs en atelier; ROTI 4/5; plan d'actions
  - [[mep-shop-maintenance-20250821]] — Fiche de livraison MEP 2.0.8 (21/08/2025); bugs HSM-18/19/20 (front); déploiement manuel par Hermès
  - [[niji-hermes-reversibilite-ateliers-6]] — Macro-planning réversibilité + détail atelier 6; mirroring GitLab; branches develop/integ/stage/master; checklist MEP; 7 ateliers total
  - [[Talan]] — Prestataire TMA prenant la maintenance de Shop Maintenance à partir du 18/06/2026
- Pages updated: [[Shop Maintenance App]], [[Hermes International]], [[index]], [[hot]], [[log]], [[entities/_index]], [[sources/_index]]
- Key insights:
  - Projet de réversibilité Niji → Talan via 7 ateliers (27/04 au 04/06)
  - Mirroring GitLab Niji → Hermès; `stage` (préprod auto) et `master` (prod manuelle)
  - MEP 2.0.8 : release front minimaliste (3 bugs), pas de changement backend/DBD
  - Tickets production : préfixe HSM-NN (Hermès Shop Maintenance); tickets SCRUM atelier = SCRUM-N
  - Équipe Hermès : Kaotar ECH CHOUKARI (chef projet tech), Christophe GROTTE (expert technique)
  - Équipe Niji : Christophe VILBERT (directeur projet), Sébastien OILLIC (dev back)
  - Équipe Talan : Seifallah DAGHAR (chef projet), Fares AMDOUNI (lead), Mohamed GASMI (architecte), Habib HAJJEM, Jordan DIAZ
  - Atelier pratique : MEP des 3 correctifs HSM-48/49/51 réalisée en temps réel
  - Maintenance sous contrôle Niji jusqu'au 18/06/2026, puis Talan responsable TMA

---

## [2026-06-03] ingest | DOC4 Sources Repository
- Type: ingest
- Trigger: user said "now ingest" (`~/work/doc4`)
- Source: `.raw/doc4-sources.md`
- Summary: [[doc4-sources]]
- Pages created: [[DOC4]], [[doc4-sources]]
- Pages updated: [[Hermes International]], [[index]], [[hot]], [[log]], [[entities/_index]], [[sources/_index]]
- Key insight: Mature PHP 7.4 + Apache app on ECS Fargate for sustainable real estate. Two MySQL DBs, Cognito OIDC auth, wkhtmltopdf PDF generation, EFS + S3 file storage.

---

## [2026-06-03] ingest | Epark sources-aws Repository
- Type: ingest
- Trigger: user said "ingest this one" (`~/work/epark/sources-aws`)
- Source: `.raw/epark-sources-aws.md`
- Summary: [[epark-sources-aws]]
- Pages created: [[Epark]], [[epark-sources-aws]]
- Pages updated: [[Hermes International]], [[index]], [[hot]], [[log]], [[entities/_index]], [[sources/_index]]
- Key insight: Simplest Hermes app yet — static jQuery SPA on S3 with an on-prem VM cron job feeding data every 5 minutes.

---

## [2026-06-03] ingest | Shop Maintenance App Repository
- Type: ingest
- Trigger: user said "ingest this repo" (`~/work/shop-mnt`)
- Actions: explored repo structure (3 sub-repos: back/front/intl), read READMEs, composer.json, package.json, .gitlab-ci.yml files, Ticket.php, Store.php, User.php entity files
- New findings:
  - Hermes Shop Maintenance App — internal ticketing system for boutique maintenance worldwide
  - **Backend**: Symfony 4.4 + API Platform 2.5.8 + PHP 7.4 + MySQL 5.7 + JWT auth (Lexik)
  - **Frontend**: Angular 17.3.12 + TypeScript 5.2 + NGXS 18.1.1 + Angular Material + Jest 29.7.0
  - **Intl**: Separate repo with 8 translation files (AR, EN, FR, IT, JP, KR, ZH, ZZ); only FR, EN, JP enabled in `list_languages.json`
  - **Intl CI/CD**: Separate pipeline deploying to S3 via `aws s3 sync`; HashiCorp Vault for AWS secrets; 8 stages; only TST and PRD enabled
  - **Domain model**: Ticket (core, unique ID `DDMMYY-NNN`), Store, User, Vendor, Area/SubArea, Category/SubCategory, TicketStatus, TicketComment, TicketChangeLog, MediaObject, Role
  - **Security**: RBAC with 4 roles (ADMINISTRATOR, HCT, VENDOR, STAFF), custom voters per entity, closed tickets immutable
  - **Auth**: Hermes login portal (`fed.hermes.com`) via Amazon Cognito + JWT backend
  - **Three-stage completion**: vendor → store → HCT finish dates with chronological validation
  - **Vendor contacts**: Up to 4 contact levels per vendor (main/secondary/third/fourth)
  - **CI/CD**: GitLab CI with Trivy security scans, SonarQube, Docker builds to Artifact Registry
  - **Environments**: Integration (Niji GCP VM), Preprod (client mirror), Production (client mirror)
  - **Dev workflow**: Conventional commits with Husky + Commitlint, Prettier, PHP-CS-Fixer, PHPStan, Rector
  - **Docker**: docker-compose with nginx, php-fpm, mysql, mailhog, phpMyAdmin for local dev
  - **Components library**: Angular library project for reusable UI components
- Pages created: [[shop-maintenance-app-repo]], [[Shop Maintenance App]], [[Ticketing System Architecture]]
- Pages updated: [[index]], [[hot]], [[log]], [[entities/_index]], [[sources/_index]], [[concepts/_index]], [[Hermes International]]
- Raw source filed: `.raw/shop-maintenance-app.md`

---

## [2026-06-03] ingest | coach-app Repository
- Type: ingest
- Trigger: user said "ingest coach-app" (`~/work/coach-app`)
- Actions: explored repo structure, read `README.md`, `App.jsx`, `api.js`, `package.json`, `lambda.tf`, `dynamodb.tf`, `deploy.sh`, `lambda_function.py`
- New findings:
  - Uplift Coach — fitness coaching SaaS; production at app.upliftcoach.fr
  - Full-stack: React 19 PWA + Python 3.13 Lambda monolith + DynamoDB single-table
  - Three role-based views: coach, alumni (students), admin
  - 3950-exercise library; program & session builder; workout/nutrition/body trackers
  - **Deep read findings:**
    - README design tokens are stale: actual accent is `#C8FF00` (Electric Lime), heading font is Teko (not Bebas Neue)
    - Tailwind 4 uses `@theme` CSS variables in `App.css` — no Tailwind config file
    - API client is custom fetch wrapper with status-code-to-toast mapping and badge unlock notifications
    - `localStorage` chosen over `sessionStorage` for token persistence across mobile app switches
    - Login uses `EMAIL#` reverse-lookup first; falls back to paginated scan for alumni
    - `_DUMMY_HASH` timing attack mitigation on failed login
    - CloudFront has strict CSP: HSTS 2yr, X-Frame-Options DENY, script-src with GA/GTM
    - Stripe: €39/€79/€149 with ~15% annual discount; 14-day trial on upgrade; webhook links subscription post-checkout
    - Separate images S3 bucket with CORS for direct browser uploads via presigned URLs
  - Stripe billing with 4 tiers: free (3 students), starter (15), pro (50), business (unlimited)
  - Multi-tenant by organization — coaches can invite other coaches
  - PWA features: service worker, install prompt, push notifications, offline banner
  - JWT auth with separate Lambda authorizer; token blocklist for revocation
  - DynamoDB: main table (PK/SK + 2 GSIs) + 5 aux tables (invites, billing, blocklist, audit, push tokens)
  - `deploy.sh` orchestrates full lifecycle: test → terraform → build → S3 → CloudFront invalidate
  - AWS naming convention `app-uplift-prd-*` prevents collision with marketing site
  - Security: OWASP A09 log retention, security audit table, rate limiting, CORS strict
- Pages created: [[coach-app-repo]], [[Uplift Coach]], [[Serverless SaaS Architecture]], [[coach-app-architecture]], [[coach-app-infra]], [[coach-app-auth-flow]], [[coach-app-data-model]]
- Pages updated: [[index]], [[hot]], [[log]], [[entities/_index]], [[sources/_index]], [[concepts/_index]]
- Raw source filed: `.raw/coach-app.md`

---

## [2026-06-03] ingest | Livret Penthievre Repository
- Type: ingest
- Trigger: user cloned `gitlab.com/hermesintl/hpt/projects/app/source` to `~/work/hpt/source`
- Actions: explored repo structure, read `.gitlab-ci.yml`, `gitlab-ci-custom.yml`, `README.md`, `CHANGELOG.md`, both `index.html` files, and JS assets
- New findings:
  - Static website for Hermes 8 Penthièvre building (8 rue de Penthièvre, 75008 Paris)
  - Dual site: Public (external landing) + Private (internal employee portal with floor plans)
  - Vanilla HTML/CSS/JS — no frontend framework
  - 11 interactive SVG floor plans (RDC, R1-R9, SS1-SS2)
  - Custom JS for floor plan navigation with cursor tracking and zone info popups
  - JWT-based OAuth2 auth via Hermes SSO (`login.js`, `jwt-decode.js`)
  - NeutraText typography (Hermes brand font)
  - Deploys to AWS S3 via `aws s3 sync src/` (custom CI overrides)
  - Environments: CHK (auto on MR) and PRD (manual on tag)
  - S3 buckets: `hsi-penthievre-chk-frontend` / `hsi-penthievre-prd-frontend`
  - Same Hermes CI/CD template library as other projects
- Pages created: [[livret-penthievre-sources]], [[8 Penthièvre]]
- Pages updated: [[index]], [[hot]], [[log]], [[entities/_index]], [[sources/_index]], [[Hermes International]]
- Raw source filed: `.raw/livret-penthievre-sources.md`
- Correction: initially reported wrong repo path (`applications-departementales/livret-penthievre/livret-penthievre-sources`); corrected to `hpt/projects/app/source`

---

## [2026-06-03] ingest | ClearID Integration Directory
- Type: ingest
- Trigger: user requested same treatment as gitlab repos for `\\cifs-frsel\...\ClearID (interface, plugins)`
- Actions: explored ~2,500 non-vendor files across 9 directories; read key PHP scripts, config files, and documentation
- New findings:
  - ClearID is Genetec's physical access control platform; Hermes account `j3gg5ror3f`
  - 5 integration interfaces: MCH (contract dates + photos), SMI (access rights + user export), IAM (identity mapping), Symfony API (dev), Outlook plugin
  - MCH scripts sync contract dates (daily 04:00 cron) and photos from Oracle HCM to ClearID
  - SMI scripts synchronize identities, locations, schedules, principals bidirectionally
  - All PHP scripts use OAuth2 client_credentials against `*.eu.clearid.io`; SSL verification disabled
  - MCH API: Oracle HCM REST with Basic Auth (`svc_MCH_API_ClearID`)
  - Deployment: Windows (Task Scheduler + PHP 8.2) + Linux (cron + Apache + ODBC)
  - Symfony 5.4 app (`clearidapi`) in development; PostgreSQL, Docker Compose
- Pages created: [[clearid-directory]], [[ClearID]], [[ClearID - MCH Interface]], [[ClearID - SMI Interface]], [[ClearID - IAM Interface]], [[ClearID - Symfony API]], [[ClearID Integration Architecture]]
- Pages updated: [[index]], [[hot]], [[log]], [[entities/_index]], [[concepts/_index]], [[sources/_index]]
- Diagrams created:
  - Interactive canvas: `wiki/canvases/clearid-integration-architecture.canvas`
- Raw source filed: `.raw/clearid-directory.md`

---

## [2026-06-02] ingest | CNF (Confie) repo
- Type: ingest
- Trigger: user cloned `gitlab.com/hermesintl/cnf` to `~/work/cnf/app`
- Actions: analyzed `.gitlab-ci.yml`, `gitlab-ci-custom.yml`, `composer.json`, `Dockerfile`, `README.md`, full `src/` structure
- New findings:
  - Confie is a Symfony 7.2 PHP application (full source in repo, unlike KLM)
  - Deploys to AWS ECS Fargate (not S3 static like HB8/KLM)
  - Uses Kaniko for container builds (language: container, tool: kaniko)
  - Container stack: Debian 12 + Apache 2.4 + PHP 8.4 + PostgreSQL 17 + wkhtmltopdf
  - Auth via Apache mod_auth_openidc (SSO)
  - Same Hermes CI/CD template library as HB8/KLM
  - 17 stages (same as KLM), baseline-component with kaniko
  - Custom jobs: get-secrets-rct/prd, custom_kaniko_build, confie-chk:ecs-*
- Pages created: [[cnf-repo]], [[Confie]], [[Symfony ECS Deployment]]
- Pages updated: [[Hermes International]], [[index]], [[entities/_index]], [[concepts/_index]], [[sources/_index]], [[hot]]
- Diagrams created:
  - SVG: `_attachments/images/cnf-cicd-pipeline.svg`
  - Interactive canvas: `wiki/canvases/cnf-cicd-pipeline.canvas` (Obsidian Canvas with clickable wiki links)
- Raw source filed: `.raw/cnf-repo.md`

---

## [2026-06-02] ingest | KLM (Kellymorphose) repo
- Type: ingest
- Trigger: user cloned `gitlab.com/hermesintl/klm` to `~/work/klm`
- Actions: analyzed `.gitlab-ci.yml` and `gitlab-ci-custom.yml`; explored compiled Vue.js SPA structure
- New findings:
  - Kellymorphose is a Vue.js SPA (compiled bundles only, no source in repo)
  - Uses same Hermes CI/CD template library as HB8
  - Dual deployment: standard S3 component + CIFS mount for media assets
  - Auth: Hermes SSO / OAuth2 (not JWT like HB8)
  - Only CHK and PRD environments enabled
- Pages created: [[klm-repo]], [[Kellymorphose]], [[Vue.js SPA Static Deployment]]
- Pages updated: [[Hermes International]], [[index]], [[entities/_index]], [[concepts/_index]], [[sources/_index]]
- Diagrams created:
  - SVG: `_attachments/images/klm-cicd-pipeline.svg`
  - Interactive canvas: `wiki/canvases/klm-cicd-pipeline.canvas` (Obsidian Canvas with clickable wiki links)
- Raw source filed: `.raw/klm-repo.md`

---

## [2026-06-02] save | HB8 CI/CD pipeline deep dive
- Type: analysis
- Trigger: user requested CI/CD diagram
- Actions: read 3x `.gitlab-ci.yml` files; created SVG architecture diagram; discovered dual deployment paths
- New findings:
  - Project is "Haute Bijouterie 8" (high jewelry), not just a color tool
  - Two deployment paths: standard (JFrog → S3 component) and custom (CIFS mount → aws s3 sync)
  - Security stack: GitGuardian, SonarQube, Vault, AWS OIDC, JFrog Artifactory
- Diagram: `_attachments/images/hb8-cicd-pipeline.svg`
- Session note updated: [[2026-06-02-hb8-ingestion-session]]

## [2026-06-02] save | HB8 repo ingestion session
- Type: session
- Scope: vault health check + repo ingestion + save pipeline demonstration
- Pages created: 6 (5 from ingest + 1 session note)
- New session note: [[2026-06-02-hb8-ingestion-session]]
- Key topics: Nuxt.js static deployment, two-repo architecture, JWT client-side auth, built-output vs source-code ingestion
- All indexes updated: index, log, hot, sources/_index, entities/_index, concepts/_index

## [2026-06-02] ingest | HB8 App Repository
- Type: codebase ingestion
- Source: `~/work/hb8/app`
- Pages created: 5 (1 source, 1 entity, 3 concepts)
- Key findings: Nuxt.js static app for Hermes International; two-repo architecture; JWT client-side auth; Excel processing; custom Garamond/Memphis web fonts
- New pages: [[hb8-app-repo]], [[Hermes International]], [[Two-Repo Architecture]], [[Nuxt.js Static Deployment]], [[JWT Auth in Static Sites]]
- Stack: Nuxt.js (Vue), GitLab CI/CD, AWS static hosting

## [2026-04-24] save | v1.6.0 public release notes (Teams, Karpathy-style)
- Type: release doc + visual assets
- Locations (new): `docs/releases/v1.6.0.md` (346 lines, 6 sections, Karpathy-style prose), `wiki/meta/dragonscale-mechanism-overview.svg` (4-mechanism diagram with shared .vault-meta/ gate), `wiki/meta/dragonscale-6-test-flow.svg` (validation timeline), `wiki/meta/dragonscale-frontier-graph.svg` (M4 candidate + 3 filed pages)
- Locations (modified): `wiki/meta/2026-04-24-v1.6.0-release-session.md` (cross-reference added pointing to public release notes)
- Scope: Teams approach. R1 (chair) wrote 3 original SVGs per SVG Diagram Style Guide. R2 (codex worker) drafted Karpathy-style release prose. R3 (chair) stitched SVGs, pivoted Wikipedia imagery to text links only (no binary vendoring per permission). R4 (codex verifier) returned ACCEPT WITH FIXES, 3 wording fixes on version narrative. R5 (chair) applied fixes, committed.
- Style: direct, short, signal-dense, lists over prose, no em dashes, no marketing terms. Verifier confirmed zero em-dashes and zero banned marketing language ('revolutionary', 'seamless', 'world-class', 'game-changing', 'unlock', 'transform').
- Distribution (all three destinations covered): (1) `docs/releases/v1.6.0.md` public-facing file (commit `85515bb`), (2) `wiki/meta/2026-04-24-v1.6.0-release-session.md` internal engineering record (cross-linked), (3) GitHub Release body (user to paste from docs/releases/v1.6.0.md when ready to `gh release create v1.6.0`).
- Wikipedia imagery: referenced as text link to `https://en.wikipedia.org/wiki/Dragon_curve` rather than hotlinked or vendored. Cleaner license-wise (no CC-BY-SA attribution needed) and no external dependency. The 3 original SVGs carry the visual load instead.
- PII scan post-write: `docs/releases/v1.6.0.md` + all three SVGs are clean. No `/home/` paths, no real emails, no tokens.
- Next recommended: user runs `gh release create v1.6.0 --notes-file docs/releases/v1.6.0.md` when ready to cut the public release. This also creates the annotated tag.

## [2026-04-24] save | DragonScale end-to-end validation pass (Teams, 6 tests)
- Type: validation + first real fold + first real autoresearch
- Tests executed (all green):
  - T0 ollama pull `nomic-embed-text`: done (274MB, 15s wall)
  - T1 M1 dry-run k=3 via codex: DRY-RUN OK, 8 children, no em-dashes
  - T2 M2 real allocate: counter advanced 2 to 3, got `c-000002` (unassigned reservation; gap acceptable per spec)
  - T3 M3 full tiling with model present: 41 pages scanned, 21 embedded, 20 correctly skipped (meta/excluded/embed-error), 0 errors at >=0.9, 15 pairs in 0.8-0.9 review band (top 0.8822 Compounding Knowledge vs LLM Wiki Pattern, a legitimate semantic neighbor), report at `wiki/meta/tiling-report-2026-04-24.md`
  - T4 M1 commit via codex: first real fold committed, `wiki/folds/fold-k3-from-2026-04-23-to-2026-04-24-n8.md` (115 lines, 8 children, flat extractive). Flips the long-standing "no fold committed yet" status
  - T6 M4 autoresearch no-topic via codex: selected "How does the LLM Wiki pattern work?" as candidate (score 1.7022, #3 after skipping top-1 source + top-2 self-reference); 6 web fetches (Karpathy gist, RAG paper arXiv 2005.11401, MemGPT arXiv 2310.08560, Obsidian docs); 3 new concept pages filed, each with Primary Sources
- Locations (new): `wiki/folds/fold-k3-from-2026-04-23-to-2026-04-24-n8.md`, `wiki/meta/tiling-report-2026-04-24.md`, `wiki/concepts/Persistent Wiki Artifact.md`, `wiki/concepts/Source-First Synthesis.md`, `wiki/concepts/Query-Time Retrieval.md`
- Locations (modified): `.vault-meta/address-counter.txt` (2 to 3), `wiki/index.md` (3 concept links), `wiki/concepts/_index.md` (3 concept links)
- Scope: six-test menu the user approved. Codex gpt-5.4 for T1/T4/T6 (sub-agent delegation); chair for T0/T2/T3 (one-shot shell) and all integration (index, log, hot, commit).
- Style: all new content uses colons or parens instead of em-dashes. Pre-existing em-dashes in index entries and wiki/concepts/_index.md left as-is (clean-room boundary; deferred to F-slice style pass).
- Tests still green: `make test` passes (74+ assertions).
- Integration: chair added the 3 new concepts to `wiki/index.md` and `wiki/concepts/_index.md` with colon-style descriptions so the fresh pages are discoverable. The cluster extends `[[How does the LLM Wiki pattern work?]]` and cross-references `[[LLM Wiki Pattern]]`.
- Next recommended slice: either (G) commit this test batch and declare v1.6.0 validated, or (H) run a second fold k=3 now that 8 newer entries exist above this one and close the hierarchical-fold-not-yet-supported loop in a future phase.

## [2026-04-24] save | v1.6.0 closeout (Teams, chair-led)
- Type: docs + release hygiene
- Locations (new): wiki/meta/2026-04-24-v1.6.0-release-session.md (release session summary, 346 lines), wiki/meta/boundary-frontier-2026-04-24.md (first M4 run artifact against this vault), docs/dragonscale-guide.md (user-facing DragonScale guide, 563 lines)
- Locations (modified): wiki/hot.md (tag-claim fix, Scripts line adds boundary-score, tests line adds test_boundary_score, push-line drift, tiling line-count, one em-dash), docs/install-guide.md (version 1.5.0 to 1.6.0, DragonScale callout expanded to all four mechanisms, "hierarchical log folds" corrected to "flat extractive log folds", points to docs/dragonscale-guide.md), README.md (DragonScale parenthetical expanded to all four mechanisms plus guide link)
- Scope: Teams approach, chair-led. Slice A (2 codex read-only explorers: closeout punch list + doc-surface map). Slice B (6 bounded writes: 4 chair, 2 codex workers, non-overlapping write scopes). Slice C (codex adversarial verifier, ACCEPT WITH FIXES). Slice D (fix pass + log entry + manual commit of docs + README).
- Verifier: C1 found 11 items across 6 files. All 11 applied. Flag typos `--allow-remote-ollama` and `--report PATH` corrected in release-session; boundary-frontier provenance corrected to `--top 7` to match default vs explicit top; hot.md tiling line-count claim stripped to avoid drift; hot.md "local tag only" corrected to "local commits only, no git tag"; install-guide log-fold wording corrected from "hierarchical" to "flat extractive"; dragonscale-guide rollback wording corrected (`.vault-meta/` is a shared gate across M2+M3+M4, not per-mechanism).
- Model: codex gpt-5.4 used throughout. User requested gpt-5.5; not reachable via codex CLI 0.123.0 / this account at the time. models_cache lists max gpt-5.4, and the API rejects gpt-5.5 with "does not exist or you do not have access". Existing config already has `service_tier = "fast"` and `sandbox_mode = "workspace-write"`, matching the "fast for chatgpt with permission of full access" intent.
- Tests: `make test` passes. test_allocate_address.sh (shell, 12 assertions), test_tiling_check.py (python, 18 assertions), test_boundary_score.py (python, 44 assertions). Zero ollama dependency.
- Tags: still no local v1.5.0 / v1.5.1 / v1.6.0 tags. User controls tag creation and push. Pre-existing tags unchanged (v1.1, v1.4.0 through v1.4.3).
- Deliberately NOT done: no real M1 fold committed; no M3 end-to-end run (needs `ollama pull nomic-embed-text`); pre-existing em-dashes in install-guide.md and README.md left untouched (clean-room boundary, not in write scope this slice); CLAUDE.md pre-existing uncommitted change left untouched.
- Next recommended slice: either (E) push to origin/main and create annotated tags v1.5.0, v1.5.1, v1.6.0 in landing order, or (F) dedicated style pass to scrub pre-existing em-dashes across install-guide.md, README.md, and any other wiki files flagged by a grep scan.

## [2026-04-24] save | DragonScale Phase 4 — boundary-first autoresearch shipped (v1.6.0)
- Type: feature release
- Locations (new): scripts/boundary-score.py (with --top, --page, --json, stdout-only CLI), tests/test_boundary_score.py (40+ assertions)
- Locations (modified): skills/autoresearch/SKILL.md (new Topic Selection section A/B/C with helper-failure fallback), commands/autoresearch.md (no-topic candidate flow with agenda-control label), wiki/concepts/DragonScale Memory.md (v0.4: M4 flipped from NOT IMPLEMENTED to shipped; exact formula without recency floor; filename-stem disclosure; fence-handling qualifiers), CHANGELOG.md, .claude-plugin/{plugin,marketplace}.json (1.5.0 -> 1.6.0), Makefile (test-boundary target), wiki/hot.md, wiki/index.md, wiki/concepts/_index.md (status drift resolved).
- Scope: boundary-first autoresearch as opt-in Topic Selection mode. `/autoresearch` without a topic surfaces top-5 frontier pages; user picks/overrides/declines. Explicit helper-failure fallback to user-ask. Labeled "agenda control" throughout to match the spec's scope disclosure.
- Correctness: filename-stem resolution including folder-qualified `[[notes/Foo]]` -> Foo.md. Self-loops, unresolved targets, meta-targets, symlinks, and vault escapes all excluded. Code-fence parser handles backticks AND tildes with CommonMark length tracking (longer opening fence is not closed by shorter inner fence). Indented blocks intentionally not filtered (Obsidian bullet convention).
- Recency: exp(-days/30), no floor. Stale pages approach zero weight so they do not dominate frontier ranking.
- Review rounds: codex adversarial Phase 4 round 1 (10 items: 7 reject + 3 refine). Round 2 (7 accept + 3 still-reject: folder-qualified stem, docstring floor mention, hot.md historical drift). Round 3 (3 accept, PASS).
- Phase 3.6 (pre-Phase-4 hardening) already landed as v1.5.1: tiling --report VAULT_ROOT confinement, rollout baseline, AGENTS.md consistency, wiki-ingest .raw/ contradiction, install-guide version.
- All four DragonScale mechanisms now shipped and opt-in. 44 commits ahead of origin/main, no push.

## [2026-04-24] save | DragonScale Phase 3.5 — cross-phase hardening to v1.5.0
- Type: release hardening
- Locations (new): bin/setup-dragonscale.sh (opt-in installer), tests/test_allocate_address.sh, tests/test_tiling_check.py, Makefile, CHANGELOG.md
- Locations (modified): hooks/hooks.json (+.vault-meta/ staging), agents/wiki-ingest.md (single-writer rule for addresses), agents/wiki-lint.md (Mechanism 2+3 checks), skills/wiki-ingest/SKILL.md (aligned non-DragonScale wording), wiki/concepts/DragonScale Memory.md (M2 severity matches lint, M4 marked NOT IMPLEMENTED, seed page gets address c-000001), .claude-plugin/{plugin.json,marketplace.json} (1.4.2/1.4.3 → 1.5.0), README.md (11 skills + DragonScale callout), wiki/hot.md (refreshed for v1.5.0), .raw/.manifest.json (address_map now has DragonScale Memory.md → c-000001), .gitignore (.vault-meta/.tiling.lock + cache), .vault-meta/address-counter.txt (advanced to 2).
- Scope: resolve the 10 hold-ship items from the cross-phase audit. Add reproducible test harness (make test passes). Version-bump plugin.json and marketplace.json to 1.5.0. Create CHANGELOG.md. Refresh hot cache.
- Review rounds: codex 3.5a (5/5 accept on doc/agent fixes), codex final holistic (10/10 accept on audit items + 2 surgical regression fixes: wiki-ingest/wiki-lint non-DragonScale wording alignment, README skill count).
- Tests: `make test` runs 12 shell assertions (allocator) + 18 python assertions (tiling-check). All pass; no ollama dependency.
- Phase 3.5 complete. Repo state: 6 developer commits added this pass (f2e73c1, 2b49a0c, 8b28e48, 19ad7e4, 365f557, 2e7dd16). Total 39 commits ahead of origin/main. No push.

## [2026-04-24] save | DragonScale Phase 3 — semantic tiling MVP
- Type: skill update + new script + threshold state
- Locations: scripts/tiling-check.py (485 lines), .vault-meta/tiling-thresholds.json (seed defaults), skills/wiki-lint/SKILL.md (109-line Semantic Tiling section + item #10 in checks), wiki/concepts/DragonScale Memory.md (Mechanism 3 cost framing clarified)
- Scope: opt-in embedding-based duplicate detection via ollama nomic-embed-text. Default bands error>=0.90, review>=0.80, explicitly documented as conservative seeds (not literature-backed interpolation). Calibration procedure documented, not automated.
- Security: default OLLAMA_URL locked to 127.0.0.1; non-localhost requires --allow-remote-ollama flag. Symlinks and vault-root escapes rejected before file reads (prevents data exfil).
- Correctness: cache keyed on sha256(model+body); orphan GC on save; model-drift auto-invalidation on load.
- Concurrency: flock(LOCK_EX) on .vault-meta/.tiling.lock; per-PID temp file for atomic writes.
- Scale: warn >500 pages; hard-fail exit 4 at >5000 pages.
- Exit codes: 0/2/3/4/10/11 distinctly surfaced in wiki-lint wiring (not collapsed into "unknown").
- Review rounds: 4 codex exec adversarial passes covering security, cache correctness, feature gate, inclusion logic, scale, threshold honesty, concurrency, exit codes, model drift, terminology coupling.
  Round 1: 10 items -> 7 reject + 3 refine.
  Round 2: 6 accept + 4 still-reject (symlink ordering, prose sync, exit-code wiring, terminology in checklist + "no API cost" claim).
  Round 3: 3 accept + 1 still-reject (cost-framing phrasing).
  Round 4: accept.
- Final verdict: 10/10 accept.
- Phase 3 complete. All three DragonScale mechanisms that were in-scope for the initial spec are now shipped as opt-in features. Mechanism 4 (boundary-first autoresearch) was flagged as agenda-control out-of-scope per the v0.2 scope boundary; may or may not ship as a future phase.

## [2026-04-23] save | DragonScale Phase 2 — deterministic page addresses MVP
- Type: skill update + new script
- Locations: scripts/allocate-address.sh, skills/wiki-ingest/SKILL.md (Address Assignment section), skills/wiki-lint/SKILL.md (Address Validation section), wiki/concepts/DragonScale Memory.md (Mechanism 2 rewritten v0.2→v0.3), .vault-meta/address-counter.txt, .raw/.manifest.json (new)
- Scope: MVP address format `c-NNNNNN` (creation-order counter, zero-padded 6 digits). Rollout baseline 2026-04-23. Legacy pages exempt until deliberate backfill (future `l-` prefix). No content hash, no fold-ancestry encoding in the MVP (both deferred).
- Concurrency: atomic allocation via flock-guarded Bash helper. Counter recovery from max observed `c-` address, never silent reset to 1.
- Lint: post-rollout pages without address are errors; legacy pages without address are informational. Optional `.vault-meta/legacy-pages.txt` manifest grandfathers pages with missing/wrong `created:` metadata.
- Re-ingest idempotency: `.raw/.manifest.json` `address_map` preserves path→address mapping across re-ingests and renames.
- Naming: mechanism renamed from "content-addressable paths" to "deterministic page addresses" (the MVP is a counter, not a content hash; the old name was overclaim).
- Review rounds: 2 codex exec adversarial passes. Round 1: 8 rejects covering counter mutation, race conditions, uniqueness atomicity, missing-file recovery, terminology drift, silent regression path, legacy classification, re-ingest idempotency. Round 2: 7 accept + 1 reject (manifest.json absent). Round 3 (item 8 only): accept after creating `.raw/.manifest.json`.
- Final verdict: 8/8 accept.
- Phase 2 complete. Phase 3 (semantic tiling lint) gated on human approval.

## [2026-04-23] save | DragonScale Phase 1 — wiki-fold skill shipped
- Type: skill
- Location: skills/wiki-fold/SKILL.md, skills/wiki-fold/references/fold-template.md
- Scope: flat extractive fold over raw wiki/log.md entries. Dry-run default via Bash stdout (no Write tool, avoids PostToolUse hook residue). Structural idempotency via deterministic fold_id. Duplicate-range detection. Fold-of-folds explicitly out of scope.
- Review rounds: 3 codex exec adversarial passes. Round 1: 1 refine + 6 reject across 7 items (allowed-tools, hook-mutation risk, idempotency claim, dry-run faithfulness, children structure, Mechanism 1 coverage, auto-commit conflict). Round 2: 6 accept + 1 reject (25/26 count inversion). Round 3 (item 4 only): accept.
- Final verdict: 7/7 accept.
- Dry-run artifact: /tmp/wiki-fold-dry-run-v2.md (not committed). fold_id: fold-k3-from-2026-04-10-to-2026-04-23-n8.
- Phase 1 complete. Phase 2 (content-addressable paths) gated on human approval.

## [2026-04-23] save | DragonScale Memory v0.2 — post-adversarial-review
- Type: concept revision
- Location: wiki/concepts/DragonScale Memory.md
- Review: codex exec adversarial review rejected all 7 load-bearing claims in v0.1
- Changes: weakened LSM analogy, removed strong prompt-cache claim, replaced 0.85 threshold with calibration procedure, justified 2^k as MVP convenience, acknowledged scope-boundary leak for boundary-first autoresearch, added Operational Policies section (retention/tombstones/versioning/conflict/concurrency/provenance/ACL), tagged claims as [sourced]/[derived]/[conjecture], narrowed tagging scope per re-review
- Re-review result: 7/7 accepted (after one surgical fix on tagging-scope language)
- Phase 0 complete. Phase 1 (wiki-fold skill) gated on human approval.

## [2026-04-23] save | DragonScale Memory — Phase 0 design doc (proposed)
- Type: concept
- Location: wiki/concepts/DragonScale Memory.md
- From: brainstorming session on applying Heighway dragon curve properties to LLM wiki memory architecture
- Scope: memory-layer only, NOT agent reasoning. Four mechanisms: (1) fold operator (LSM-style exponential compaction at 2^k log entries), (2) content-addressable page paths for prompt-cache stability, (3) semantic tiling lint (embedding-based dedup, 0.85 cosine threshold), (4) boundary-first autoresearch scoring
- Status: proposed. Phase 0 pending codex adversarial review. Phase 1+ (fold skill, address anchors, tiling lint, boundary score) gated on review pass.
- Primary sources verified: Dragon curve (Wikipedia, boundary dim 1.523627086), Regular paperfolding sequence (OEIS A014577), LSM trees (arXiv 2504.17178, LevelDB 10x level ratio), MemGPT (arXiv 2310.08560), Anthropic prompt caching docs (5min/1hr TTL, 20-block lookback)
- Links updated: wiki/concepts/_index.md, wiki/index.md

## [2026-04-15] save | Claude SEO v1.9.0 Slides and GitHub Release
- Type: session
- Location: wiki/meta/2026-04-15-slides-and-release-session.md
- From: built 15-slide HTML presentation deck (v190.html), fixed hardcoded path in release_report.py, pushed 68 files to GitHub, tagged v1.9.0, created GitHub release with PDF asset
- Key lessons: Path.home() not hardcoded paths, git pull --rebase before big pushes, Chrome blocks file:// cross-origin images, .claude/ always in .gitignore
- Release: https://github.com/AgriciDaniel/claude-seo/releases/tag/v1.9.0

## [2026-04-15] save | Claude SEO v1.9.0 Release Report — PDF Complete
- Type: session
- Location: wiki/meta/2026-04-15-release-report-session.md
- From: full session completing the v1.9.0 PDF release report. Dark theme, 13 pages, 1.53 MB. Fixed logo (double-space filename), empty spaces, page-break orphans, file:// URL encoding.
- Key fixes: `urllib.parse.quote()` for file:// URIs; `display:table-cell` is atomic in WeasyPrint (no page-break); fixed `height:297mm` causes empty space; replaced orphan tables with paragraphs
- Challenge v2 added: keyword LEADS, $600 prize pool, deadline April 28
- Output: `~/Desktop/Claude-SEO-v1.9.0-Release-Report.pdf`

## [2026-04-14] save | Claude SEO v1.9.0 — Pro Hub Challenge Integration Session
- Type: session + 4 concept pages + 1 entity page
- Location: wiki/meta/2026-04-14-claude-seo-v190-session.md
- From: full v1.9.0 implementation session — reviewed 5 community submissions, integrated 4 new skills (seo-cluster, seo-sxo, seo-drift, seo-ecommerce), enhanced seo-hreflang, added DataForSEO cost guardrails
- Pages created: [[2026-04-14-claude-seo-v190-session]], [[Claude SEO]], [[Pro Hub Challenge]], [[Semantic Topic Clustering]], [[Search Experience Optimization]], [[SEO Drift Monitoring]]
- Review rounds: 4 (code review x3 + cybersecurity audit). Score: 87 → 93 → 97 → 85 security
- Key learnings: always verify subagent output (40-line count error caught), insertion-point bugs caught by max-effort plan review, pre-existing security debt identified (10 of 15 findings)

## [2026-04-14] save | SVG Diagram Style Guide
- Type: concept
- Location: wiki/concepts/SVG Diagram Style Guide.md
- From: extracted design tokens from 17 production SVGs in claude-ads/assets/diagrams/
- Covers: colors, typography, layout primitives, card patterns, arrow connectors, numbered circles, file naming

## [2026-04-14] save | Community CTA Footer Rollout
- Type: decision
- Location: wiki/meta/2026-04-14-community-cta-rollout.md
- From: session adding Skool community footer to 6 skill repos (claude-ads, claude-seo, claude-obsidian, claude-blog, banana-claude, claude-cybersecurity)
- Key insight: frequency calibration per tool type; single-point orchestrator instruction pattern

## [2026-04-10] save | Backlink Empire - Blog Posts, Karpathy Gist, GitHub Cross-Linking
- Type: session
- Location: wiki/meta/2026-04-10-backlink-empire-session.md
- From: full session covering blog creation (claude-obsidian + claude-canvas), Karpathy gist comment, 26 GitHub README updates with Author/community/backlink sections, homepage URLs on 10 repos, topics on 25 repos, rankenstein.pro backlinks on 5 SEO repos
- Blog posts: agricidaniel.com/blog/claude-obsidian-ai-second-brain, agricidaniel.com/blog/claude-canvas-ai-visual-production
- Impact: ~87 new backlinks from DA 96 github.com, 6 rankenstein.pro backlinks, 25 Skool community links

## [2026-04-08] save | claude-obsidian v1.4 Release Session
- Type: session
- Location: wiki/meta/claude-obsidian-v1.4-release-session.md
- From: full release cycle covering v1.1 (URL/vision/delta tracking, 3 new skills), v1.4.0 (audit response, multi-agent compat, Bases dashboard, em dash scrub, security history rewrite), and v1.4.1 (plugin install command hotfix)
- Key lessons: plugin install is 2-step (marketplace add then install), allowed-tools is not valid frontmatter, Bases uses filters/views/formulas not Dataview syntax, hook context does not survive compaction, git filter-repo needs 2 passes for full scrub

## [2026-04-08] ingest | Claude + Obsidian Ecosystem Research
- Type: research ingest
- Source: `.raw/claude-obsidian-ecosystem-research.md`
- Queries: 6 parallel web searches + 12 repo deep-reads
- Pages created: [[claude-obsidian-ecosystem]], [[cherry-picks]], [[claude-obsidian-ecosystem-research]], [[Ar9av-obsidian-wiki]], [[Nexus-claudesidian-mcp]], [[ballred-obsidian-claude-pkm]], [[rvk7895-llm-knowledge-bases]], [[kepano-obsidian-skills]], [[Claudian-YishenTu]]
- Key finding: 16+ active Claude+Obsidian projects; 13 cherry-pick features identified for v1.3.0+
- Top gap confirmed: no delta tracking, no URL ingestion, no auto-commit

## [2026-04-07] session | Full Audit, System Setup & Plugin Installation
- Type: session
- Location: wiki/meta/full-audit-and-system-setup-session.md
- From: 12-area repo audit, 3 fixes, plugin installed to local system, folder renamed

## [2026-04-07] session | claude-obsidian v1.2.0 Release Session
- Type: session
- Location: wiki/meta/claude-obsidian-v1.2.0-release-session.md
- From: full build session — v1.2.0 plan execution, cosmic-brain→claude-obsidian rename, legal/security audit, branded GIFs, PDF install guide, dual GitHub repos


- Source: `.raw/` (first ingest)
- Pages updated: [[index]], [[log]], [[hot]], [[overview]]
- Key insight: The wiki pattern turns ephemeral AI chat into compounding knowledge — one user dropped token usage by 95%.

## [2026-04-07] setup | Vault initialized

- Plugin: claude-obsidian v1.1.0
- Structure: seed files + first ingest complete
- Skills: wiki, wiki-ingest, wiki-query, wiki-lint, save, autoresearch
