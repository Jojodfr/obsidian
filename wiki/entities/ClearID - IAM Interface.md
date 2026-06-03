---
type: entity
title: "ClearID - IAM Interface"
status: current
tags:
  - entity
  - clearid
  - iam
  - identity
  - integration
  - php
related:
  - "[[ClearID]]"
  - "[[Hermes International]]"
  - "[[ClearID Integration Architecture]]"
  - "[[clearid-directory]]"
---

# ClearID - IAM Interface

Identity mapping integration between Hermes IAM (Identity and Access Management) and Genetec ClearID.

---

## Scripts

| Script | Purpose |
|--------|---------|
| `ScriptClearId.php` | Main identity photo/test script (development/testing) |
| `ScriptClearId2.php` | Variant |
| `ScriptClearId3.php` | Variant |
| `ListIdentiesClearId.php` | List all ClearID identities |
| `update_manager_email.php` | Update manager email fields |

## Documentation

| File                                                | Format | Purpose                        |
| --------------------------------------------------- | ------ | ------------------------------ |
| `Champs IAM_ClearId.docx`                           | Word   | Field mapping documentation    |
| `Genetec_IAM Contract Agreement v2.0_20240227.xlsx` | Excel  | Contract agreement             |
| `locations_iam_mapping.xlsx`                        | Excel  | Location mapping IAM ↔ ClearID |
| `locations_iam_data_model.xlsx`                     | Excel  | Data model specification       |

## Key Data

- **Location Mapping:** `locations_iam_mapping.xlsx` and `locations_iam_data_model.xlsx` define how Hermes IAM locations map to ClearID sites/locations
- **Identity Mapping:** `array_identities_prod.inc.php` contains production identity mappings

## Source

- [[clearid-directory]] — full directory inventory
- `.raw/clearid-directory.md` — raw source
