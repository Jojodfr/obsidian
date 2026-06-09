---
type: concept
title: "ClearID Integration Architecture"
status: current
tags:
  - concept
  - clearid
  - architecture
  - integration
  - hermes
  - data-flow
related:
  - "[[ClearID]]"
  - "[[ClearID - MCH Interface]]"
  - "[[ClearID - SMI Interface]]"
  - "[[ClearID - IAM Interface]]"
  - "[[ClearID - Symfony API]]"
  - "[[ClearID - Site and Location Reference]]"
  - "[[Hermes International]]"
  - "[[clearid-directory]]"
  - "[[clearid-entities-relationships]]"
  - "[[clearid-entities-relationships-ascii]]"
---

# ClearID Integration Architecture

Hermes International uses Genetec ClearID as its physical access control platform. Multiple internal systems feed data into ClearID through PHP-based integration scripts.

---

## System Context

```
┌─────────────────────────────────────────────────────────────────┐
│                        Hermes International                      │
│                                                                  │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │    MCH      │    │    SMI      │    │    IAM      │         │
│  │ (Oracle HCM)│    │(Access Mgmt)│    │(Identity Mgmt│         │
│  └──────┬──────┘    └──────┬──────┘    └──────┬──────┘         │
│         │                  │                  │                │
│         │ contract dates   │ access rights    │ identity map   │
│         │ photos           │ locations        │                │
│         │                  │ schedules        │                │
│         │                  │                  │                │
│         └──────────────────┼──────────────────┘                │
│                            │                                     │
│                            ▼                                     │
│                   ┌─────────────────┐                           │
│                   │  PHP Scripts    │                           │
│                   │  (Windows/Linux)│                           │
│                   └────────┬────────┘                           │
│                            │                                     │
└────────────────────────────┼─────────────────────────────────────┘
                             │
                             ▼
                  ┌─────────────────────┐
                  │   Genetec ClearID   │
                  │  (Physical Access)  │
                  │ *.eu.clearid.io     │
                  └─────────────────────┘
                             │
                             ▼
                  ┌─────────────────────┐
                  │   Gunnebo / SMI     │
                  │  (User re-export)   │
                  └─────────────────────┘
```

---

## Data Flows

### 1. MCH → ClearID (HR Data)

**Systems:** Oracle HCM (MCH) → ClearID Identity Service
**Scripts:** `UpdateContractDatesClearId.php`, `AddMissingPhotoClearId.php`
**Schedule:** Daily 04:00 via cron/Task Scheduler

| Data                     | Direction     | API                                     |     |
| ------------------------ | ------------- | --------------------------------------- | --- |
| Contract start/end dates | MCH → ClearID | Oracle HCM REST → ClearID PATCH         |     |
| Assignment start dates   | MCH → ClearID | Oracle HCM REST → ClearID PATCH         |     |
| Employee photos          | MCH → ClearID | Oracle HCM REST → ClearID POST /picture |     |
|                          |               |                                         |     |

**MCH Endpoint:** `fa-eoic-saasfaprod1.fa.ocs.oraclecloud.com/hcmRestApi/resources/11.13.18.05/`

### 2. SMI ↔ ClearID (Access Control)

**Systems:** Hermes SMI ↔ ClearID
**Scripts:** 14 PHP 8.2 scripts under `frsellpappepa02.atlas.hermes/clearid/accctrl/`

| Data | Direction | Script | API Endpoint |
|------|-----------|--------|--------------|
| Identity sync | SMI → ClearID | `SynchronizeIdentitiesClearId.php` | `POST /api/v3/accounts/{id}/identities/synchronize` |
| Access rights | SMI → ClearID | `ImportIdentitiesAccessClearId.php` | `PATCH /api/v3/accounts/{id}/locations/{id}/accesses` |
| Location/schedule | SMI → ClearID | `ImportLocationsSchedulesClearId.php` | `PATCH /api/v3/accounts/{id}/locations/{id}/schedules` |
| Contractors (SST) | SMI → ClearID | `ImportPrestatairesClearId.php` | `POST /api/v3/accounts/{id}/identities` |
| Missing principals | SMI → ClearID | `AddMissingIdentityPrincipalsClearId.php` | depends |
| Identity audit | ClearID | `ListIdentiesClearId.php` | `GET /api/v3/accounts/{id}/identities` |
| Principal audit | ClearID | `ListIdentityPrincipalsClearId.php` | `GET /api/v3/accounts/{id}/identityPrincipals/{id}` |
| Site/location ref | ClearID | `ListSitesLocationsSchedulesClearId.php` | sites, locations, schedules |
| Access listing | ClearID | `ListAccessesByLocationClearId.php` | `GET /api/v3/accounts/{id}/locations/{id}/accesses` |

**Operational notes:**
- CSV imports require UTF-8 with BOM, semicolon separator
- Access rights import requires temporarily disabling end-date limit in ClearID UI
- Contractor import hardcodes siteId `72b455d1-5e21-46a1-afef-390b2f95b67d`
- Token refresh every 2,000 records during pagination

### 3. IAM ↔ ClearID (Identity Mapping)

**Systems:** Hermes IAM ↔ ClearID
**Scripts:** `ScriptClearId.php` variants
**Artifacts:** Excel mapping files (`locations_iam_mapping.xlsx`)

### 4. ClearID → SMI (SST Export)

**Systems:** ClearID → SMI/Gunnebo
**Script:** `ImportSSTintoSMI.php`
**Trigger:** Windows Task Scheduler (PowerShell wrapper)

---

## Authentication Patterns

### ClearID API
- **Protocol:** OAuth2 client_credentials
- **Token URL:** `{sts_url}/connect/token`
- **Usage:** `Authorization: Bearer {token}` on all API calls
- **Refresh:** Every 1,000–2,000 identities during pagination

### Oracle HCM (MCH)
- **Protocol:** Basic Authentication
- **Service Account:** `svc_MCH_API_ClearID`
- **Endpoint:** `fa-eoic-saasfaprod1.fa.ocs.oraclecloud.com`

---

## Deployment

| Platform | Environment | Scheduling |
|----------|-------------|------------|
| Windows | PHP 8.2.13 NTS | Task Scheduler |
| Linux (RHEL 7-10) | PHP + Apache | Cron (`crontab`) |
| CIFS Share | `\\cifs-frsel\...` | Shared code storage |

---

## Shared Components

All PHP scripts share:
- `api_url.inc.php` — Endpoint definitions & OAuth2 credentials
- `api_functions.inc.php` — `get_token()`, `get_sites()`, `get_locations()`, `get_identity()`, `import_identity()`
- Cache files (`cache_*.txt`) — Per-run state to avoid reprocessing
- Log directories — 60-day retention

---

## Symfony API (Future)

A Symfony 5.4 application (`clearidapi`) is in development to potentially replace or augment the script-based integrations with a structured API layer.

---

## Source

- [[clearid-directory]] — full source inventory
- `.raw/clearid-directory.md` — raw source with code excerpts
