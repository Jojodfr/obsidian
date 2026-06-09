---
type: concept
title: "Tilbury App"
created: 2026-06-08
tags:
  - concept
  - hermes
  - symfony
  - tilbury
  - architecture
  - boutique
status: current
related:
  - "[[Tilbury]]"
  - "[[Hermes International]]"
  - "[[Hermes CI-CD Pipeline]]"
  - "[[Shop Maintenance App]]"
  - "[[Symfony ECS Deployment]]"
---

# Tilbury App

**Hermes International** | TIL | Boutique & Store Management
Symfony 6.2 | PHP 8.1 | AWS ECS Fargate

Navigation: [[concepts/_index|Concepts]] | [[Tilbury]] | [[Hermes International]]

---

## Overview

Tilbury is a Hermes departmental application for managing retail boutiques (`Magasin`) and manufacturing/operational sites (`Site`). It tracks personnel assignments, store characteristics, and the pairing relationships (`Tandem`) between boutiques and their associated locations. The app also manages thematic questionnaires, preferences, language skills, and file attachments for each entity.

---

## Domain Model

### Core Layer

**Magasin (Boutique)** — 24 fields
- Identity: username, nom, prenom, fonction
- Assignment: datedebgrp, datedebfct
- Store: magasin (name), filiale
- Metrics: effectif, nbsubhie, nbequipes, taille (decimal), anneeouverture
- Hours: horairedeb, horairefin
- Management: manager, rh, assistant
- Preferences: dtprefmag, dtprefsite (free text)
- Status: actif, isnotif, statut
- Audit: tmpcre, tmpmaj, codoper, codopermaj
- Relation: region (Donneeref)

**Site (Location/Manufacture)** — 23 fields
- Same core structure as Magasin but replaces `magasin`/`filiale` with `manufacture`
- Adds `prenom` and `pole` (Donneeref) instead of `region`
- `__toString()` returns `manufacture`

**Tandem (Pairing)** — Key relationship entity
- `codetandem` (string, 10)
- `idrmag` (Magasin ManyToOne)
- `idrsite` (Site ManyToOne)
- Four date ranges: datedebsite/datefinsite + datedebmag/datefinmag
- Full audit trail

**Questionnaire System**
- `Theme` — question category (libtheme, numorder, usertype)
- `Question` — response (idquestion, libreponse, usertype)
- Both linked to Magasin or Site via rmag/rsite + usertype

**Preferences & Skills**
- `Preference` — datespref + levelpref + idpreference (Donneeref)
- `Langue` — codlangue + levellg per entity

**File Attachments**
- `PieceJointe` — idpj, typepj, nomfichier, cheminfichier, ext

### Reference Data

| Entity | Purpose |
|--------|---------|
| **Donneeref** | Core bilingual lookup: iddonneeref + typedata + libdatafr/libdataen + numorder |
| **RefPartenaires** | External partner contacts (nom, mail) |
| **RefSociete** | Company/society names |

### Auth/Admin (Standard Hermes Pattern)

| Entity | Role |
|--------|------|
| **User** | Symfony Security (username, roles, password, locale) — implements UserInterface + PasswordAuthenticatedUserInterface |
| **AdmUser** | Admin user management |
| **AdmGroupe** | Permission groups |
| **AdmGrpmen** | Group-menu links |
| **AdmMenu** | Navigation menu |
| **AdmParam** | Application parameters |
| **AdmConn** | Login audit log |
| **AdmUsgrp** | User-group assignments |

---

## Technical Architecture

### Stack

| Layer | Technology |
|-------|------------|
| **Framework** | Symfony 6.2 (projet-type-v6.0.1) |
| **PHP** | 8.1+ |
| **ORM** | Doctrine 2.14 |
| **DB** | MariaDB/MySQL (inferred) |
| **PDF** | KnpSnappy + wkhtmltopdf |
| **Spreadsheets** | PHPSpreadsheet |
| **Auth** | Symfony Security (username/password) |
| **Email** | Symfony Mailer + VerifyEmailBundle |
| **Serializer** | Symfony Serializer (groups: groupe1-4, pj, langue) |

### CI/CD Pipeline

Standard Hermes component pipeline:

1. analysis (SonarQube + gitleaks)
2. build_app
3. build (Docker → Harbor)
4. test
5. push
6. pre_deploy
7. deploy_dev (master branch)
8. deploy_tst/chk/rct
9. release
10. deploy_acc/ppd
11. deploy_prd (git tag)
12. S3 config copy

### Deployment

- **Dev**: internal-alb-tilbury.dev.cloudh.hermes
- **Prod**: internal-alb-tilbury.prd.cloudh.hermes
- **Platform**: AWS ECS Fargate
- **Health**: `/health_check.php`
- **Debug**: `/debug/adminer.php`

---

## Design Patterns

### Dual-Entity Discriminator
Business data (`Theme`, `Question`, `Preference`, `PieceJointe`, `Langue`) attaches to either a `Magasin` or a `Site` through parallel `rmag`/`rsite` ManyToOne relations, with a `usertype` column (`M`/`S`) discriminating the owner type. This avoids separate tables per entity type.

### Bilingual Reference (Donneeref)
All reference values are centralized in `Donneeref` with:
- `typedata` as category code (e.g., region, pole, preference type)
- `libdatafr` / `libdataen` parallel labels
- `numorder` for display ordering
This replaces Symfony's standard i18n YAML/XML files with database-driven l10n.

### Audit Trail
Every entity includes:
- `tmpcre` / `tmpmaj` — creation and last-modification timestamps
- `codoper` / `codopermaj` — AdmUser who created/last modified
Implemented consistently across all 19 entities.

---

## Wiki Links

- [[Tilbury]]
- [[Hermes International]]
- [[Shop Maintenance App]]
- [[Hermes CI-CD Pipeline]]
- [[Symfony ECS Deployment]]
