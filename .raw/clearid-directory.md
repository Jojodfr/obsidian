# ClearID Directory Source

**Source:** `\\cifs-frsel\etudes\Applications Départementales\PEPS France\07 - Doc DSI\ClearID (interface, plugins)`
**Ingested:** 2026-06-03
**Total files (non-vendor):** ~2,500

---

## Overview

ClearID is a Genetec physical access control and identity management platform. This directory contains all Hermes integration scripts, APIs, and plugins connecting internal Hermes systems (MCH, SMI, IAM) to the ClearID platform.

**ClearID EU Production Endpoints:**
- STS: `https://sts.eu.clearid.io`
- Identity Service: `https://identityservice.eu.clearid.io`
- Search Service: `https://searchservice.eu.clearid.io`
- Location Service: `https://locationservice.eu.clearid.io`
- Site Service: `https://siteservice.eu.clearid.io`
- Role Service: `https://roleservice.eu.clearid.io`
- VR Service: `https://vrservice.eu.clearid.io`
- RPS: `https://rps.eu.clearid.io`
- Principal Service: `https://principalservice.eu.clearid.io`
- System Service: `https://systemservice.eu.clearid.io`

**Account ID:** `j3gg5ror3f`

---

## Directory Structure

```
ClearID (interface, plugins)
├── php-8.2.13-Win32-vs16-x64/          # PHP Windows runtime binaries (81 files)
├── linux/                               # Linux deployment scripts + PHP scripts (672 files)
│   ├── odbc.sh                          # MS ODBC driver installer for RHEL
│   └── clearid/
│       ├── contractdates/               # Linux version of contract date sync
│       └── ...
├── Interface MCH-CleadID (dates contrat)/   # Contract dates sync MCH → ClearID (49 files)
│   └── PHP/
│       ├── UpdateContractDatesClearId.php   # Main sync script
│       ├── api_url.inc.php                  # ClearID API endpoints & credentials
│       ├── api_functions.inc.php            # Shared ClearID API functions
│       ├── mch_api_functions.inc.php        # MCH (Oracle HCM) API functions
│       ├── functions.inc.php                # Utility functions
│       └── cache_*.txt                      # Cache files per contract type
├── Interface MCH-CleadID (photo)/       # Photo sync MCH → ClearID (100 files)
│   └── PHP/
│       ├── AddMissingPhotoClearId.php   # Main photo sync script
│       ├── CatchUpMissingPhotoClearId.php
│       ├── api_functions.inc.php
│       ├── functions.inc.php
│       └── logs/                        # Execution logs
├── Interface SMI-ClearID (droits d'acces)/  # Access rights sync SMI ↔ ClearID (329 files)
│   └── PHP/
│       ├── SynchronizeIdentitiesClearId.php # Bulk identity synchronization
│       ├── ImportIdentitiesSchedulesClearId.php
│       ├── ListIdentiesClearId.php
│       ├── ListIdentityPrincipalsClearId.php
│       ├── ListIdentityDeletedClearId.php
│       ├── ImportPrestatairesClearId.php
│       ├── ImportLocationsSchedulesClearId.php
│       ├── ListSitesLocationsSchedulesClearId.php
│       ├── common.inc.php
│       ├── api_url.inc.php
│       ├── api_functions.inc.php
│       └── array_*.inc.php              # Static config arrays (identities, schedules, locations)
├── Interface IAM-ClearID (identity)/    # IAM identity mapping (48 files)
│   ├── Champs IAM_ClearId.docx
│   ├── Genetec_IAM Contract Agreement v2.0_20240227.xlsx
│   ├── locations_iam_mapping.xlsx
│   ├── locations_iam_data_model.xlsx
│   └── PHP/
│       ├── ScriptClearId.php
│       ├── ScriptClearId2.php
│       ├── ScriptClearId3.php
│       ├── ListIdentiesClearId.php
│       ├── update_manager_email.php
│       ├── api_url.inc.php
│       └── array_identities_prod.inc.php
├── Interface ClearID-SMI (utilisateurs)/  # User export ClearID → SMI (191 files)
│   ├── Interface_IAM_SMI_20260327.docx
│   ├── Interface Iporta vers Gunnebo.msg
│   ├── planificateur de tâches.txt
│   ├── Import_Iporta_SST_*.txt          # SST import data files
│   └── PHP/
│       ├── ImportSSTintoSMI.php         # Imports SST (temporary workers) into SMI
│       ├── ListIdentiesClearId.php
│       ├── runp_ImportSSTintoSMI_AKA.ps1  # PowerShell wrapper for Windows Task Scheduler
│       └── api_url.inc.php
├── Symfony/                             # Symfony 5.4 API application (1013 files non-vendor)
│   └── clearidapi/
│       ├── composer.json                # PHP 7.2.5+, Symfony 5.4.*, Doctrine ORM
│       ├── .env                         # PostgreSQL default, dev env
│       ├── docker-compose.yml
│       ├── src/                         # Application source
│       └── tests/
└── Plugin ClearID pour Outlook par AddonXpert/  # Outlook plugin (2 files)
    ├── outlook-clearid-schema-fr.png
    └── Outlook ClearID - Raccourci.lnk
```

---

## Integration Interfaces

### 1. MCH → ClearID (Contract Dates)

**Script:** `UpdateContractDatesClearId.php`
**Schedule:** Daily at 04:00 (cron: `0 4 * * 1-6` weekdays, `0 4 * * 0` full refresh Sundays)
**Platform:** Windows (PHP CLI) + Linux

**Flow:**
1. Authenticate to ClearID via OAuth2 client_credentials
2. Paginate through all identities via `/api/v4/accounts/{account_id}/identities?take=50`
3. For each identity with `externalId`:
   - Query MCH (Oracle HCM REST API) by `PersonNumber`
   - Navigate: worker → workRelationships → contracts + assignments
   - Extract contract dates (`EffectiveStartDate`, `ContractEndDate`)
   - Extract assignment dates (`ProjectedStartDate`)
4. Calculate `calculated_start_date`:
   - Internal: `assignment.ProjectedStartDate` → `contract.EffectiveStartDate` → `start_date`
   - External/Temporary: `start_date`
5. PATCH identity in ClearID with custom fields:
   - `mch_contract_start_date`
   - `mch_contract_end_date`
   - `mch_assigments_start_date`
   - `calculated_start_date`

**Contract Types Handled:** CDI, CDD, APP (apprentice), STG (intern), VAC, EXT (external), SST (temporary)

**Caching:** Per-contract-type cache files (`cache_CDI.txt`, `cache_CDD.txt`, etc.) to avoid reprocessing identities on subsequent runs. `--nocache` flag clears all caches.

**MCH API:** `https://fa-eoic-saasfaprod1.fa.ocs.oraclecloud.com/hcmRestApi/resources/11.13.18.05/workers`

---

### 2. MCH → ClearID (Photos)

**Script:** `AddMissingPhotoClearId.php`
**Platform:** Windows + Linux

**Flow:**
1. Paginate all ClearID identities
2. Skip if already has photo (`pictureBlobName` exists)
3. Skip external workers (`EXT*` prefix)
4. Query MCH by `PersonNumber` for photo URL
5. Download photo from MCH (JPEG/PNG/GIF)
6. POST photo to ClearID: `/identity/api/v3/accounts/{account_id}/identities/{identityId}/picture`
7. Cache processed identities to avoid reprocessing

**Cache Files:** `cache_noextid.txt`, `cache_external.txt`, `cache_nomch.txt`, `cache_nophoto.txt`, `cache_photoexists.txt`, `cache_photonotadded.txt`

---

### 3. SMI ↔ ClearID (Access Rights)

**Script:** `SynchronizeIdentitiesClearId.php`
**Platform:** Web (Apache) at `frsellpappepa02.atlas.hermes/clearid/accctrl/`

**Flow:**
1. Get OAuth2 token from ClearID STS
2. Paginate all identities via `/api/v3/accounts/{account_id}/identities`
3. Refresh token every 2,000 identities
4. Batch synchronize identities via POST `/api/v3/accounts/{account_id}/identities/synchronize`
5. Excludes two specific identity IDs from sync

**Other SMI Scripts:**
- `ImportIdentitiesSchedulesClearId.php` - Import identities with schedules
- `ListIdentityPrincipalsClearId.php` - List principals per identity
- `ImportLocationsSchedulesClearId.php` - Import locations and schedules
- `ListSitesLocationsSchedulesClearId.php` - List all sites/locations/schedules
- `ImportPrestatairesClearId.php` - Import external contractors

---

### 4. IAM ↔ ClearID (Identity)

**Scripts:** `ScriptClearId.php`, `ScriptClearId2.php`, `ScriptClearId3.php`
**Purpose:** Identity mapping between Hermes IAM and ClearID

**Key Files:**
- `locations_iam_mapping.xlsx` - Location mapping between IAM and ClearID
- `locations_iam_data_model.xlsx` - Data model documentation
- `Champs IAM_ClearId.docx` - Field mapping documentation

---

### 5. ClearID → SMI (Users / SST Import)

**Script:** `ImportSSTintoSMI.php` (called by `runp_ImportSSTintoSMI_AKA.ps1`)
**Platform:** Windows Task Scheduler

**Flow:**
1. PowerShell wrapper changes to directory and runs PHP with `--startdate` parameter
2. Imports SST (temporary workers) from Iporta into SMI/Gunnebo system

**Related Documents:**
- `Interface_IAM_SMI_20260327.docx` - Interface specification
- `Interface Iporta vers Gunnebo.msg` - Email thread
- `Import_Iporta_SST_*.txt` - Data import files

---

### 6. Symfony API (clearidapi)

**Framework:** Symfony 5.4 (PHP >=7.2.5)
**Database:** PostgreSQL (default config)
**Features:**
- Doctrine ORM + Migrations
- Symfony Messenger (queue)
- Webpack Encore (frontend assets)
- PHPUnit tests
- Docker Compose setup

**Dependencies:**
- `amphp/http-client` for async HTTP
- `sensio/framework-extra-bundle`
- Full Symfony 5.4 webapp pack

**Status:** Dev environment (`APP_ENV=dev`), appears to be in development/pilot phase.

---

## Authentication Patterns

### ClearID API
- OAuth2 `client_credentials` grant
- Token endpoint: `{sts_url}/connect/token`
- API calls use `Authorization: Bearer {token}` header
- SSL verification disabled in all scripts (`CURLOPT_SSL_VERIFYHOST/VERIFYPEER => false`)

### MCH (Oracle HCM) API
- Basic Authentication with service account
- Endpoint: `fa-eoic-saasfaprod1.fa.ocs.oraclecloud.com`
- API version: `11.13.18.05`
- Resources: `workers`, `publicWorkers`

---

## Deployment Architecture

**Windows:**
- PHP 8.2.13 NTS (non-thread-safe) binaries
- Scripts executed via Windows Task Scheduler or manual CLI
- CIFS share hosting (`\\cifs-frsel\...`)

**Linux:**
- RHEL 7/8/9/10 compatible
- Cron jobs for scheduled execution
- ODBC driver for SQL Server connectivity (`msodbcsql18`)
- Apache web server for SMI interface (`/var/www/clearid/`)

---

## Data Flow Summary

```
MCH (Oracle HCM) ──► ClearID  (contract dates, photos)
SMI ──────────────► ClearID  (access rights, identities, locations, schedules)
IAM ──────────────► ClearID  (identity mapping)
ClearID ──────────► SMI      (user export, SST imports)
```

Hermes is the **source of truth** for HR data (MCH) and access control policies (SMI), while ClearID is the **target system** for physical access identity management.
