---
type: entity
title: "ClearID - MCH Interface"
status: current
tags:
  - entity
  - clearid
  - mch
  - oracle-hcm
  - integration
  - php
  - cron
related:
  - "[[ClearID]]"
  - "[[Hermes International]]"
  - "[[ClearID Integration Architecture]]"
  - "[[clearid-directory]]"
---

# ClearID - MCH Interface

Two PHP-based integration scripts synchronizing employee data from Hermes MCH (Oracle HCM) to Genetec ClearID.

---

## 1. Contract Dates Sync

**Script:** `UpdateContractDatesClearId.php`
**Schedule:** Daily at 04:00 via cron
- Weekdays: `0 4 * * 1-6` — incremental sync
- Sundays: `0 4 * * 0` — full refresh with `--cdi --cdd --app --stg --vac`

**Platforms:** Windows (PHP CLI) + Linux (`/var/www/clearid/contractdates/`)

### Flow

1. Authenticate to ClearID via OAuth2 client_credentials
2. Paginate through all identities: `GET /api/v4/accounts/{account_id}/identities?take=50&continuation={token}`
3. For each identity with `externalId` (Hermes employee number):
   - Query MCH Oracle HCM REST API by `PersonNumber`
   - Navigate: worker → workRelationships → contracts + assignments
   - Extract contract dates (`EffectiveStartDate`, `ContractEndDate`)
   - Extract assignment `ProjectedStartDate`
4. Calculate `calculated_start_date`:
   - Internal: `assignment.ProjectedStartDate` → `contract.EffectiveStartDate` → `start_date`
   - External/Temporary: `start_date`
5. PATCH identity in ClearID with custom fields:
   - `mch_contract_start_date`
   - `mch_contract_end_date`
   - `mch_assigments_start_date`
   - `calculated_start_date`

### Contract Types

| Type | Description | Filter Flag |
|------|-------------|-------------|
| CDI | Permanent contract | `--cdi` |
| CDD | Fixed-term contract | `--cdd` |
| APP | Apprentice | `--app` |
| STG | Intern | `--stg` |
| VAC | Vacation/temp | `--vac` |
| EXT | External contractor | `--ext` |
| SST | Temporary worker | `--sst` |

### Caching

Per-contract-type cache files (`cache_CDI.txt`, `cache_CDD.txt`, etc.) to avoid reprocessing. `--nocache` clears all caches.

### MCH API

```
GET https://fa-eoic-saasfaprod1.fa.ocs.oraclecloud.com/hcmRestApi/resources/11.13.18.05/workers?q=PersonNumber='{id}'
```

Basic auth with service account `svc_MCH_API_ClearID`.

---

## 2. Photo Sync

**Script:** `AddMissingPhotoClearId.php`
**Platform:** Windows + Linux

### Flow

1. Paginate all ClearID identities
2. Skip if already has photo (`pictureBlobName` exists)
3. Skip external workers (`EXT*` prefix — no MCH photo)
4. Query MCH `publicWorkers` endpoint with `expand=photos`
5. Download photo from MCH (JPEG/PNG/GIF supported)
6. POST to ClearID: `/identity/api/v3/accounts/{account_id}/identities/{identityId}/picture`
7. Cache processed identities

### Cache Files

- `cache_noextid.txt` — no external ID
- `cache_external.txt` — external worker
- `cache_nomch.txt` — not found in MCH
- `cache_nophoto.txt` — no photo in MCH
- `cache_photoexists.txt` — already has photo
- `cache_photonotadded.txt` — upload failed

---

## Shared Libraries

Both scripts use:
- `api_url.inc.php` — ClearID endpoints & OAuth2 credentials
- `api_functions.inc.php` — `get_token()`, `get_identity()`, `get_identity_by_externalid()`
- `mch_api_functions.inc.php` — `get_worker_contracts()`, `get_worker_assigments()`

---

## Source

- [[clearid-directory]] — full directory inventory
- `.raw/clearid-directory.md` — raw source with code excerpts
