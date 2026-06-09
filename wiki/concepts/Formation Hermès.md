---
type: concept
title: "Formation Hermès"
created: 2026-06-08
tags:
  - formation
  - hermes
  - curriculum
related:
  - "[[Hermes International]]"
  - "[[ClearID]]"
  - "[[Hermes CI-CD Pipeline]]"
---

# Formation Hermès — Parcours Ecosystème IT

**Objectif** : comprendre l'écosystème technique Hermès de zéro, à partir du wiki.
**Public** : nouveau collaborateur, prestataire, ou curieux.
**Durée estimée** : 6 modules, ~2-3h chacun (lecture + questionnements).

Navigation: [[index]] | [[concepts/_index|Concepts]]

---

## Module 1 — Vue d'ensemble Hermès International

### Ce qu'est Hermès

Maison française de luxe fondée en 1837. Connue pour ses maroquineries, foulards, ready-to-wear, bijoux. Côté IT, c'est une organisation `hermesintl` sur GitLab avec une dizaine d'applications internes.

### Les grands domaines IT

| Domaine | Applications | Stack |
|---------|-------------|-------|
| **Haute Joaillerie** | HB8 (Haute Bijouterie 8), Kellymorphose | Nuxt.js, Vue.js → AWS S3 |
| **Maintenance boutiques** | Shop Maintenance App | Symfony 4.4 + Angular 17 |
| **Immobilier / ESG** | DOC4 | PHP 7.4 + Apache → AWS ECS |
| **Parking** | Epark | jQuery SPA → AWS S3 |
| **EHS** | EHS | PHP + Apache → Docker |
| **Access Control** | ClearID intégrations | PHP scripts + Symfony API |
| **Gestion boutiques** | Tilbury | Symfony 6.2 AWS ECS |
| **Formation** | Savoir-faire (retiré) / Monodyssee (actif) | Symfony 5.4 legacy → Symfony 6.2 AWS ECS |

### Concept clé

Hermès héberge **tout sur AWS** (S3 statique, ECS Fargate conteneurisé) avec un **pipeline CI/CD commun** à tous les projets.

**Lecture** : [[Hermes International]]

---

## Module 2 — ClearID : L'identité physique chez Hermès

### Pourquoi ClearID ?

ClearID est le système de contrôle d'accès physique de Genetec. Chez Hermès, il gère :
- Qui entre où dans les bâtiments (Pantin, Loupes, etc.)
- Quand (plannings, horaires)
- Avec quels droits (rôles, secteurs)

### Les 4 interfaces Hermès ↔ ClearID

| Interface | Direction | Données |
|-----------|-----------|---------|
| **MCH** | Oracle HCM → ClearID | Dates de contrat + photos des collaborateurs |
| **SMI** | Bidirectionnel | Droits d'accès, identités, locations, schedules |
| **IAM** | IAM → ClearID | Mapping identité entreannuaire interne et ClearID |
| **Symfony API** | (développement) | API REST maison pour orchestrer ClearID |

### Chiffres clés

- **106 sites** (Pantin, Loupes, etc.)
- **305 locations** (portes, accès, secteurs)
- **93 schedules** (plannings horaires)
- **Kanal** est un site sans location associée (cas particulier)
- **23 champs personnalisés** Hermès dans les identités ClearID (login Atlas, division, manager, etc.)

### Concept clé

La synchronisation se fait via **scripts PHP en cron** (Windows Task Scheduler + Linux cron). Tous utilisent OAuth2 `client_credentials`. Les photos et dates de contrat viennent d'**Oracle HCM**.

**Lecture** : [[ClearID]], [[ClearID Integration Architecture]], [[ClearID - Site and Location Reference]]

---

## Module 3 — CI/CD : Le pipeline commun à tous les projets

### Philosophie

Hermès utilise un **système de composants** GitLab CI/CD (pas un gros fichier unique). Chaque projet inclut des briques réutilisables avec `include: component:`.

### Les 6 composants clés

| Composant | Rôle |
|-----------|------|
| **baseline-component** | Obligatoire pour tous. SonarQube + GitGuardian + Vault + release sémantique |
| **release-component** | Gestion des releases multi-langage (Node, Python, Java, .NET, Container) |
| **cicd-containers-build-component** | Build Docker ou Kaniko, lint Hadolint, push JFrog/Harbor |
| **cicd-aws-components** | Déploiement AWS (ECS Fargate, Lambda, API Gateway) |
| **cicd-aws-ecs** | Déploiement ECS spécifique (legacy) |

### Sécurité intégrée

- **GitGuardian** : scan de secrets à chaque commit
- **SonarQube** : qualité du code (quality gate)
- **Vault** : secrets managés (pas de clés en dur)
- **Trivy** : scan de vulnérabilités sur les images
- **Hadolint** : validation des Dockerfiles (source Container Factory obligatoire)
- **AWS OIDC** : auth sans clés AWS (`lzm-glb-gitlab-oidc-app`)

### Environnements

| Env | Déclencheur | Déploiement |
|-----|-------------|-------------|
| **dev/tst/chk** | MR / branche `main` | Automatique |
| **acc/prd** | Git tag | Manuel (release gate) |

### Concept clé

Tous les projets partagent la **même chaîne de sécurité**, mais les technologies de build varient selon le type d'application.

**Lecture** : [[Hermes CI-CD Pipeline]], [[GitLab CI-CD Component Architecture]], [[Hermes CI-CD Baseline Template]]

---

## Module 4 — Patterns d'architecture

### 4 patterns dominants chez Hermès

| Pattern | Projet d'exemple | Déploiement |
|---------|-----------------|-------------|
| **Nuxt.js statique** | HB8 | AWS S3 + CloudFront |
| **Vue.js SPA statique** | Kellymorphose | AWS S3 |
| **Symfony conteneurisé** | Confie, Shop Maintenance | AWS ECS Fargate / GCP VM |
| **PHP + Apache Docker** | DOC4, EHS | AWS ECS Fargate |
| **Serverless SaaS** | Uplift Coach | React + Lambda + DynamoDB |

### Auth

Deux modes dominants :
1. **Cognito OIDC / OAuth2** — pour les apps PHP (DOC4, Confie, EHS). Redirection vers `fed.hermes.com`.
2. **JWT client-side** — pour les apps statiques (HB8). Token Cognito stocké côté client.

### Concept clé

Hermès n'a pas **une** architecture unique mais **plusieurs patterns** selon le besoin : statique pour les sites vitrines, conteneurisé pour les applications métier avec base de données.

**Lecture** : [[Symfony ECS Deployment]], [[Nuxt.js Static Deployment]], [[Vue.js SPA Static Deployment]], [[Serverless SaaS Architecture]]

---

## Module 5 — Applications en détail

### 5.1 Shop Maintenance App — Maintenance des boutiques

Système de tickets pour les problèmes de maintenance dans les boutiques Hermès à travers le monde.

- **Stack** : Symfony 4.4 API + Angular 17 SPA
- **Auth** : JWT (LexikJWT) via portail Hermès (`fed.hermes.com`)
- **Rôles** : ADMINISTRATOR, HCT (corporate), VENDOR, STAFF
- **Ticket ID** : format `DDMMYY-NNN`
- **États** : Workflow en 3 étapes (vendor → store → HCT) avec validation chronologique
- **Transition TMA** : reversibilité Niji → Talan en juin 2026 (7 ateliers)

### 5.2 HB8 — Haute Bijouterie 8

Application de joaillerie (outil interne).

- **Stack** : Nuxt.js 3, Vue 3, TypeScript
- **Déploiement** : AWS S3 statique
- **Auth** : JWT client-side
- **Fonts** : Garamond + Memphis (custom web fonts)
- **Dual repo** : séparation code source (`app`) et infrastructure (`iac`)

### 5.3 Kellymorphose — Personnalisation sac Kelly

SPA Vue.js compilé uniquement (pas de source dans le repo, seulement les bundles).

- **Déploiement** : AWS S3 + CIFS (assets média)
- **Auth** : SSO OAuth2 Hermès

### 5.4 Confie — Services internes

Symfony 7.2, le plus récent des projets PHP chez Hermès.

- **Stack** : PHP 8.4 + Symfony 7.2 + PostgreSQL 17
- **Build** : Kaniko (rootless) → JFrog Artifactory
- **Déploiement** : AWS ECS Fargate
- **Auth** : Apache mod_auth_openidc (SSO)

### 5.5 DOC4 — Immobilier durable

Gestion immobilière et reporting ESG (Environmental, Social, Governance).

- **Stack** : PHP 7.4 + Apache (legacy)
- **DB** : MySQL dual (générique + EHS)
- **Auth** : Cognito OIDC
- **PDF** : wkhtmltopdf

### 5.6 Epark — Parking

Tableau de bord de disponibilité parking.

- **Stack** : jQuery SPA (vanilla JS)
- **Déploiement** : AWS S3
- **Data** : Cron on-premise → JSON → S3 toutes les 5 min

### 5.7 EHS — Environmental Health & Safety

Audits et inspections sécurité.

- **Stack** : PHP + Apache Docker
- **DB** : dual MySQL (schémas `ehs` et `phphiehs`)
- **Auth** : Cognito OAuth2
- **Feature removed** : Instant Carbon Footprint retiré en v1.9.0

### 5.8 Savoir-faire / Monodyssee — Évaluation apprentis

| Aspect | Savoir-faire (retiré) | Monodyssee (actif) |
|--------|----------------------|-------------------|
| **Stack** | Symfony 5.4 | Symfony 6.2, PHP 8.2 |
| **Déploiement** | Local/CIFS | AWS ECS Fargate + S3 |
| **Auth** | Form-login Guard | Symfony Security moderne |
| **Éditeur** | — | CKEditor (rich text) |
| **Import/Export** | — | PHPSpreadsheet (Excel) |
| **Fichiers** | Local | Local + AWS S3 |
| **Sessions** | Fichiers | Base de données (v1.13.0) |
| **Features** | Campagnes, Questions, Réponses, Évaluations | + Bilans, Attestations, Modules, Livret de suivi, Auto-évaluation, CRON |
| **CI/CD** | Gitleaks seul | Full pipeline Hermès (SonarQube, ECS, S3, Release) |
| **Statut** | Retiré, remplacé par monodyssee | Production active (v1.13.0)

Ancien portail CFA pour les campagnes d'évaluation.

- **Stack** : Symfony 5.4 + PostgreSQL 13
- **Auth** : Form-login session (Guard, pre-Symfony 6)
- **Workflow** : Campagne → Questions → Réponses → Signatures formateur/élève
- **Statut** : Retiré, remplacé par monodyssee

### 5.9 Suivi des Incidents — Incident tracking

Internal incident declaration and management system. Tracks incidents across Hermès societies and sites worldwide.

- **Stack** : Symfony 6.2 + MariaDB 10.5
- **Auth** : Symfony Security (USER, AUDIT, ADMIN, SUPER_ADMIN)
- **Features** : Incident declaration (draft/declare), category/type system, severity, confidentiality (N/C/R), alerts, file attachments, statistics, social media tracking
- **File storage** : Local (dev) + S3 (prod) with presigned URLs
- **Translation** : Numeric glossaire keys (not standard Symfony i18n)
- **Legacy** : AS400 data import from AGRlib.SDI001T
- **CI/CD** : Full pipeline Hermès (SonarQube, ECS, S3)

**Lecture** : pages entité individuelles ([[Shop Maintenance App]], [[HB8]], [[Kellymorphose]], [[Suivi des Incidents]], etc.)

### 5.10 Tilbury — Gestion des boutiques et sites

Systeme de gestion des boutiques (Magasin) et sites de production/manufacture (Site) Hermes.

- **Stack** : Symfony 6.2 + PHP 8.1 + MariaDB
- **Auth** : Symfony Security (username/password, ROLE_USER default)
- **Domain** : 19 entities including Magasin (boutique, 24 fields), Site (location, 23 fields), Tandem (pairing with date ranges)
- **Features** : Thematic questionnaires (Theme/Question), preferences (Preference/Donneeref), language skills (Langue), file attachments (PieceJointe)
- **Pattern** : Dual-entity discriminator (usertype=M/S) links data to either Magasin or Site
- **Translation** : Bilingual Donneeref (libdatafr/libdataen) instead of Symfony i18n
- **CI/CD** : Full pipeline Hermes (SonarQube, ECS, S3 config copy)

**Lecture** : [[Tilbury]], [[Tilbury App]]

---

## Module 6 — Glossaire et Références

### Acronymes

| Acronyme | Signification |
|----------|--------------|
| **HCT** | Hermès Corporate Team |
| **CFA** | Centre de Formation des Apprentis |
| **MEP** | Mise En Production |
| **TMA** | Tierce Maintenance Applicative |
| **MCH** | Maîtrise des Compétences Humaines (interface Oracle HCM) |
| **SMI** | Système de Management Interfacé |
| **IAM** | Identity and Access Management |
| **ESG** | Environmental, Social, Governance |
| **SSO** | Single Sign-On |
| **OIDC** | OpenID Connect |
| **ECS** | Elastic Container Service (AWS) |
| **ECR** | Elastic Container Registry (AWS) |

### Références rapides

| Sujet | Page wiki |
|-------|----------|
| Vue d'ensemble | [[Hermes International]] |
| CI/CD | [[Hermes CI-CD Pipeline]] |
| Access control | [[ClearID]] |
| Architecture CI/CD composants | [[GitLab CI-CD Component Architecture]] |
| Déploiement conteneurs | [[Symfony ECS Deployment]] |
| Applications | [[Shop Maintenance App]], [[Confie]], [[DOC4]], [[Epark]], [[EHS]], [[Savoir-faire]], [[Monodyssee]], [[Tilbury]], [[Suivi des Incidents]] |

---

## Module 7 — Savoir-faire / CFA : Le cadre d'évaluation des apprentis Hermès

### Contexte

Hermès forme ses artisans maroquiniers au **CFA MyCampH EDSF** (École des Savoir-Faire, Pantin). L'évaluation se fait via des documents PDF générés par Monodyssee. Trois types de documents : **Jalons**, **Bilans**, et **CAP Maroquinerie**.

### Le principe : échelle à 5 niveaux

| Niveau | Signification |
|--------|--------------|
| Non évalué | Pas encore abordé |
| Non acquis | Non maîtrisé |
| En cours d'acquisition (-) | En progression, encore fragile |
| En cours d'acquisition (+) | Presque maîtrisé |
| Acquis | Maîtrisé en autonomie |

### Les 3 document types

1. **Jalon** — Évaluation technique sur un produit (ex: Constance slim, Sac Rachel). Critères précis par savoir-faire (griffage, couture sellier, astiquage, parage, collage, montage). Commentaires libres du formateur.

2. **Bilan** — Évaluation globale périodique.
   - **Savoir-être** : 9 compétences (intégration valeurs, esprit collectif, communication, rigueur, patience, organisation, adaptation, force de proposition)
   - **Savoir-faire** : techniques de maroquinerie (lecture de peau, placement, coupe, parage, collage, couture, astiquage, montage, etc.)
   - **Santé-sécurité / Règlement** : ergonomie, douleurs, stress, ponctualité
   - Se termine par **Points forts**, **Axes d'amélioration**, **Commentaire général**

3. **CAP Maroquinerie** — Certification professionnelle (C1: documents, C2: éléments produit, C3: préparation). Référentiel métier.

### Mapping Monodyssee

| Document papier | Entité Monodyssee |
|-----------------|-------------------|
| Jalon | `Module`, `Evaluation`, `CommentaireEvaluation` |
| Bilan | `Bilan`, `BilanSe`, `BilanSf`, `Metiers` |
| Attestation | `Attestation` |
| Livret de suivi | `LivretDeSuivi`, `ModuleSuivi` |
| Export PDF | Traçé dans `ExportActions` (audit trail) |

### Savoirs techniques évalués

- **Griffage** : distance du bord, répartition régulière, pas de double griffage
- **Couture sellier/retournée** : inclinaison, taille point, arrêts fil (point à cheval, doublés lovés), serrage, martelage
- **Astiquage** : ponçage régulier, filetage (pas brûlé/croisé), tranche satinée et arrondie
- **Parage** : épaisseur homogène, respect consignes
- **Collage** : homogène, sans débord, marouflage lisse, pas de prisonnier
- **Montage** : bord à bord, mise en forme (rabat, gorge), affleurement, pas de bulle

**Lecture** : [[CFA Hermès Evaluation Framework]], [[Monodyssee]]

---

## Comment étudier

1. **Module 1** : Lire [[Hermes International]] pour le contexte.
2. **Module 2** : Explorer les pages ClearID. Identifier les 4 interfaces et leur direction.
3. **Module 3** : Lire le pipeline CI/CD. Comprendre la différence entre `baseline` et `release`.
4. **Module 4** : Comparer les 5 patterns. Se demander : pourquoi S3 pour HB8 et ECS pour Confie ?
5. **Module 5** : Choisir 2-3 apps qui t'intéressent le plus. Lire leurs pages en profondeur.
6. **Module 6** : Réviser le glossaire. Poser des questions au wiki via `query:`.
7. **Module 7** (optionnel) : Si tu touches à Monodyssee — lire [[CFA Hermès Evaluation Framework]] pour comprendre le métier.

---

*Formation générée automatiquement à partir du wiki. Mise à jour : 2026-06-08.*
