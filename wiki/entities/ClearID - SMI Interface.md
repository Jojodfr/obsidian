---
type: entity
title: "ClearID - SMI Interface"
status: current
tags:
  - entity
  - clearid
  - smi
  - access-rights
  - integration
  - php
  - gunnebo
related:
  - "[[ClearID]]"
  - "[[Hermes International]]"
  - "[[ClearID Integration Architecture]]"
  - "[[clearid-directory]]"
---

# ClearID - SMI Interface

Two bidirectional integration flows between Hermes SMI (access rights management) and Genetec ClearID.

---

## 1. SMI → ClearID (Access Rights)

**Script:** `SynchronizeIdentitiesClearId.php`
**Hosted:** `https://frsellpappepa02.atlas.hermes/clearid/accctrl/SynchronizeIdentitiesClearId.php`
**Platform:** Apache on Linux (`/var/www/clearid/accctrl/`)

### Flow

1. Authenticate to ClearID via OAuth2 client_credentials
2. Paginate all identities: `GET /api/v3/accounts/{account_id}/identities?continuation={token}`
3. Refresh token every 2,000 identities
4. Build batch of `identityIds` (excluding two hardcoded IDs)
5. POST to `/api/v3/accounts/{account_id}/identities/synchronize`
6. Log responses per batch

### Other SMI → ClearID Scripts

| Script | Purpose |
|--------|---------|
| `ImportIdentitiesSchedulesClearId.php` | Import identities with schedules |
| `ListIdentityPrincipalsClearId.php` | List principals per identity |
| `ImportLocationsSchedulesClearId.php` | Import locations and schedules |
| `ListSitesLocationsSchedulesClearId.php` | List all sites/locations/schedules |
| `ImportPrestatairesClearId.php` | Import external contractors |
| `ListIdentiesClearId.php` | List all identities |
| `ListIdentityDeletedClearId.php` | List deleted identities |

### Static Config Arrays

- `array_identities_prod.inc.php` — Identity mappings
- `array_schedules_prod.inc.php` — Schedule mappings
- `array_locations_sites_prod.inc.php` — Location/site mappings

---

## 2. ClearID → SMI (User Export / SST Import)

**Script:** `ImportSSTintoSMI.php`
**Wrapper:** `runp_ImportSSTintoSMI_AKA.ps1` (Windows Task Scheduler)

### Flow

1. PowerShell wrapper changes to CIFS directory
2. Runs PHP with `--startdate {YYYYMMDDhhmmss}` parameter
3. Imports SST (temporary workers/subcontractors) from Iporta into SMI/Gunnebo system

### Related Documents

- `Interface_IAM_SMI_20260327.docx` — Interface specification
- `Interface Iporta vers Gunnebo.msg` — Email thread
- `Import_Iporta_SST_*.txt` — Data import files (dated snapshots)
- `planificateur de tâches.txt` — Task scheduler configuration notes

---

## Shared Libraries

- `api_url.inc.php` — ClearID endpoints & credentials
- `api_functions.inc.php` — Token, sites, locations, schedules, identity helpers
- `common.inc.php` — Shared utilities

---

## Source

- [[clearid-directory]] — full directory inventory
- `.raw/clearid-directory.md` — raw source with code excerpts
