---
type: entity
title: "Tilbury"
created: 2026-06-08
tags:
  - entity
  - hermes
  - symfony
  - tilbury
  - boutique
status: current
related:
  - "[[Tilbury App]]"
  - "[[Hermes International]]"
  - "[[Hermes CI-CD Pipeline]]"
  - "[[Shop Maintenance App]]"
---

# Tilbury

**Hermes International** | Symfony 6.2 | PHP 8.1 | AWS ECS Fargate

Hermes boutique and store management system. Manages retail locations (Magasin), manufacturing sites (Site), their pairing relationships (Tandem), and associated questionnaires/preferences.

Navigation: [[entities/_index|Entities]] | [[Tilbury App]] | [[Hermes International]]

---

## Domain Model

### Core Business Entities (11)

| Entity | Purpose | Key Fields |
|--------|---------|------------|
| **Magasin** | Boutique/store | username, nom, prenom, fonction, datedebfct, magasin (name), filiale, effectif, nbsubhie, nbequipes, taille, anneeouverture, horairedeb/fin, manager, rh, assistant, actif, isnotif, statut, region (Donneeref) |
| **Site** | Location/manufacture | username, nom, prenom, fonction, datedebfct, effectif, nbsubhie, nbequipes, taille, anneeouverture, horairedeb/fin, manager, rh, assistant, manufacture, pole (Donneeref), actif, isnotif, statut |
| **Tandem** | Store-site pairing | codetandem, idrmag (Magasin), idrsite (Site), datedebsite, datefinsite, datedebmag, datefinmag |
| **Theme** | Thematic questions | libtheme, numorder, usertype, rmag/rsite |
| **Question** | Questionnaire responses | idquestion, libreponse, usertype, rmag/rsite |
| **Preference** | Preference levels | datespref, levelpref, idpreference (Donneeref), usertype, rmag/rsite |
| **Donneeref** | Bilingual reference | iddonneeref, typedata, libdatafr, libdataen, numorder |
| **PieceJointe** | File attachments | idpj, typepj, nomfichier, cheminfichier, ext, usertype, rmag/rsite |
| **Langue** | Language skills | codlangue, levellg, usertype, rmag/rsite |
| **RefPartenaires** | Partner contacts | nom, mail |
| **RefSociete** | Societies/companies | nom |

### Auth & Admin (8)

| Entity | Purpose |
|--------|---------|
| **User** | Symfony security user (username, password, roles, locale) |
| **AdmUser** | Admin user account |
| **AdmGroupe** | Permission groups |
| **AdmGrpmen** | Group-menu mapping |
| **AdmMenu** | Application menu entries |
| **AdmParam** | Runtime parameters |
| **AdmConn** | Connection/session log |
| **AdmUsgrp** | User-group memberships |

---

## Architecture

### Stack

- **Framework**: Symfony 6.2 (PHP 8.1+)
- **Project template**: projet-type-v6.0.1
- **DB**: Doctrine ORM (MariaDB/MySQL inferred)
- **PDF**: KnpSnappy + wkhtmltopdf
- **Spreadsheets**: PHPSpreadsheet
- **Auth**: Symfony Security (username/password, ROLE_USER default)
- **Email**: Symfony Mailer + VerifyEmailBundle
- **Serializer**: Symfony Serializer with custom groups

### Deployment

- **Dev**: internal-alb-tilbury.dev.cloudh.hermes
- **Prod**: internal-alb-tilbury.prd.cloudh.hermes
- **Pipeline**: Standard Hermes CI/CD (gitleaks, SonarQube, Harbor, ECS Fargate, Vault)
- **S3**: Config file copy on deploy
- **Health check**: `/health_check.php`
- **Adminer**: `/debug/adminer.php`

---

## Key Patterns

### Dual-Entity Discriminator

Most business entities (`Theme`, `Question`, `Preference`, `PieceJointe`, `Langue`) link to BOTH `Magasin` and `Site` via `rmag`/`rsite`, with a `usertype` column (`M` for magasin, `S` for site) to indicate which side the data belongs to.

### Bilingual Reference Data

All dropdown/reference values live in `Donneeref` with `typedata` as the category code, `numorder` for sort order, and parallel `libdatafr` / `libdataen` fields. This avoids Symfony's standard translation system.

### Tandem Date Ranges

Each Tandem records four dates: `datedebsite`/`datefinsite` (site assignment window) and `datedebmag`/`datefinmag` (store assignment window), allowing historical tracking of boutique-manufacture relationships.

### Audit Trail

Every entity has `tmpcre`/`tmpmaj` timestamps and `codoper`/`codopermaj` user references (AdmUser), providing full create/update provenance.

---

## Related

- [[Tilbury App]] — Architecture concept page
- [[Shop Maintenance App]] — Similar Symfony 4.4 boutique maintenance app
- [[Hermes CI-CD Pipeline]] — Deployment pipeline details
- [[Hermes International]] — Organization overview
