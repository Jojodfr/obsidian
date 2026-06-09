---
type: source
title: "Tilbury Repository"
created: 2026-06-08
tags:
  - source
  - hermes
  - symfony
  - tilbury
related:
  - "[[Tilbury]]"
  - "[[Tilbury App]]"
  - "[[Hermes International]]"
---

# Tilbury Repository

**URL**: `gitlab.atlas.hermes:2222/applications-departementales/symfony/tilbury`
**Ingested**: 2026-06-08
**Type**: Symfony 6.2 application (projet-type-v6.0.1)

Navigation: [[sources/_index|Sources]] | [[Tilbury]] | [[Tilbury App]]

---

## Files Ingested

24 files from `applications-departementales/symfony/tilbury/src/Entity/` + README + composer.json + .gitlab-ci.yml + config_bundles.php

### Entities (19)

| Entity | Type | Description |
|--------|------|-------------|
| `Magasin` | Business | Boutique/store with personnel, hours, size, manager |
| `Site` | Business | Location/manufacturing site with pole, manufacture |
| `Tandem` | Business | Store-site pairing with date ranges |
| `Theme` | Business | Thematic question category (libtheme, numorder) |
| `Question` | Business | Questionnaire response (libreponse, usertype) |
| `Preference` | Business | Preference levels linked to Donneeref |
| `Donneeref` | Reference | Bilingual reference data (FR/EN) |
| `PieceJointe` | Business | File attachments (nomfichier, cheminfichier, ext) |
| `Langue` | Business | Language skills per entity |
| `RefPartenaires` | Reference | Partner contacts (nom, mail) |
| `RefSociete` | Reference | Companies/societies |
| `User` | Auth | Symfony security user (username, roles, password, locale) |
| `AdmUser` | Admin | Standard admin user |
| `AdmGroupe` | Admin | User groups |
| `AdmGrpmen` | Admin | Group-menu associations |
| `AdmMenu` | Admin | Menu entries |
| `AdmParam` | Admin | Parameters |
| `AdmConn` | Admin | Connection log |
| `AdmUsgrp` | Admin | User-group associations |

### Config

- `composer.json` — Symfony 6.2, PHP 8.1, KnpSnappy, PHPSpreadsheet, VerifyEmailBundle
- `.gitlab-ci.yml` — Standard Hermes pipeline (gitleaks, SonarQube, containers, ECS deploy, release)
- `config_bundles.php` — Symfony bundle registration

---

## Key Findings

1. **Bilingual by design**: `Donneeref.libdatafr` + `libdataen` powers all reference labels in French and English.
2. **Dual entity model**: Most business entities have both `rmag` (Magasin) and `rsite` (Site) with `usertype` discriminator (`M`/`S`).
3. **Audit trail standard**: `tmpcre`, `tmpmaj`, `codoper`, `codopermaj` on nearly every entity.
4. **No custom controllers found** in ingestion scope — only entities; likely standard CRUD or API Platform.
5. **Serializer groups**: `groupe1`, `groupe2`, `groupe3`, `groupe4`, `pj`, `langue` for API responses.
6. **Tandem is the key relationship**: links Magasin to Site with effective dates for both sides.

---

*Source summary generated from raw files in `.raw/tilbury/`*. 
