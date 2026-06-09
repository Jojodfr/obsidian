---
type: entity
title: "ClearID - SMI Interface"
status: current
updated: 2026-06-09
tags:
  - entity
  - clearid
  - smi
  - access-rights
  - integration
  - php
  - genetec
related:
  - "[[ClearID]]"
  - "[[Hermes International]]"
  - "[[ClearID Integration Architecture]]"
  - "[[clearid-directory]]"
  - "[[clearid-smi-droits-acces-scripts]]"
  - "[[ClearID - Site and Location Reference]]"
---

# ClearID - SMI Interface

Bidirectional integration between Hermes SMI (Security & Maintenance Informatique — access rights management) and Genetec ClearID physical access control platform. 14 PHP 8.2 scripts on `frsellpappepa02.atlas.hermes/clearid/accctrl/` (Apache/Linux), also run via CLI from CIFS share.

---

## 1. SMI → ClearID (Access Rights)

### 1.1 Identity Synchronization

**Script:** `SynchronizeIdentitiesClearId.php`
**Endpoint:** `POST /api/v3/accounts/{account_id}/identities/synchronize`

Paginates all identities with `continuation` token, builds batches of `identityIds` (excluding two hardcoded IDs: `b1e145e9-45a0-478e-836c-3b1e04b3839e`, `e431b77d-9fa1-47cc-aca7-ac751752d814`), and triggers a synchronize operation for the batch. Token refresh every 2,000 identities.

### 1.2 Access Rights Import

**Script:** `ImportIdentitiesAccessClearId.php`
**Endpoint:** `PATCH /api/v3/accounts/{account_id}/locations/{locationId}/accesses`
**CSV Input:** `identityIds;locationId;scheduleId;startDateTimeUtc;endDateTimeUtc;description`
**Format:** UTF-8 with BOM, semicolon separator

Converts French dates (`d/m/Y H:i`) to ISO 8601 UTC (`Y-m-d\TH:i:s\Z`). Validates chronological order and identity existence in local cache (`array_identities_prod.inc.php`).

**Operational prerequisite**: disable ClearID end-date limit (`Organisation > Site > configuration de l'acces > pas de date de fin`) during import, re-enable after.

Error counters tracked: date errors, id errors, API errors.

### 1.3 Location/Schedule Import

**Script:** `ImportLocationsSchedulesClearId.php`
**Endpoint:** `PATCH /api/v3/accounts/{account_id}/locations/{locationId}/schedules`
**CSV Input:** location-to-schedule mappings

Associates schedules to locations in bulk.

### 1.4 Contractor (Prestataire/SST) Import

**Script:** `ImportPrestatairesClearId.php`
**Endpoint:** `POST /api/v3/accounts/{account_id}/identities`
**CSV Input:** `WorkerType;Company;Manager;Department;LocationCode;Worklocation;Division;FirstName;LastName;MissionTitle;Arrival;Departure;SitePrincipal;ExternalCompany`

Builds deterministic `externalId = SST-{md5(...)}` for deduplication. Looks up manager by name in `$array_identities` cache. Payload includes custom fields: `start_date`, `end_date`, `contract_type=SST`, `external_company`, `cost_center`, `manager_id`, `manager_email`. Hardcoded `siteId`: `72b455d1-5e21-46a1-afef-390b2f95b67d`.

### 1.5 Missing Principal Repair

**Script:** `AddMissingIdentityPrincipalsClearId.php`
Adds principal records for identities that lack access principals, complementing `ListIdentityNoPrincipalsClearId.php`.

---

## 2. ClearID → SMI / Reporting

### 2.1 Identity Listing & Cache Generation

**Script:** `ListIdentiesClearId.php`
**Endpoint:** `GET /api/v3/accounts/{account_id}/identities?continuation={token}`

CLI/web dual-mode. Options: `--noarray`, `--nocsv`, `--fastlog`. Generates:
- `array_identities_{ENV}.inc.php` (PHP cache dump, ~14 MB prod)
- CSV export: `identityId;firstName;lastName;companyName;departmentName;externalId;status`

Also extracts `costCenter` and `email` from custom fields into the array dump.

### 2.2 Principal State Audit

**Script:** `ListIdentityPrincipalsClearId.php`
**Endpoint:** `GET /api/v3/accounts/{account_id}/identityPrincipals/{identityId}`

Reports `roles` and `principalState` per identity. Flags inactive (`status != Active`) and deleted (`isDeleted == true`) identities.

### 2.3 Deleted Identity Audit

**Script:** `ListIdentityDeletedClearId.php`
Lists all identities with `isDeleted == true`.

### 2.4 Inactive Identity Audit

**Script:** `ListIdentityInactiveClearId.php`
Lists inactive or deleted identities.

### 2.5 No-Principal Audit

**Script:** `ListIdentityNoPrincipalsClearId.php`
Lists identities missing an access principal.

### 2.6 No-Site Audit

**Script:** `ListIdentiesNoSiteIdClearId.php`
Lists identities without a site assignment.

### 2.7 Site/Location/Schedule Reference Builder

**Script:** `ListSitesLocationsSchedulesClearId.php`
Cascading fetch:
1. `GET /api/v2/accounts/{account_id}/sites`
2. Per site: `GET /api/v3/accounts/{account_id}/locations?Take=200&SiteId={siteId}`
3. Per location: `GET /api/v3/accounts/{account_id}/locations/{locationId}/schedules`

Generates four array cache files and a CSV export:
- `array_sites_{ENV}.inc.php`
- `array_locations_{ENV}.inc.php`
- `array_locations_sites_{ENV}.inc.php`
- `array_schedules_{ENV}.inc.php`

### 2.8 Site Listing

**Script:** `ListSitesClearId.php`
**Endpoint:** `GET /api/v2/accounts/{account_id}/sites`

### 2.9 Location Listing

**Script:** `ListLocationsClearId.php`
**Endpoint:** `GET /api/v3/accounts/{account_id}/locations?Take=1000`

### 2.10 Accesses per Location (Location Service v3)

**Script:** `ListAccessesByLocationClearId.php` *(new)*
**Endpoint:** `GET /api/v3/accounts/{account_id}/locations/{locationId}/accesses[?includeExpired=true]`
**Host:** `locationservice.eu.clearid.io`

Lists identities and teams who have access rights to each location/secteur. Discovered that the ClearID **Access API v1 is not deployed** on the Hermes tenant; the endpoint exists on the existing **Location Service v3**.

Response model:
- `accessModels`: identityId → [AccessModel] (individual accesses)
- `teamAccessModels`: teamId → [AccessModel] (team/role accesses)

- **Prerequisites:** all array caches must be current (`array_identities`, `array_locations`, `array_locations_sites`, `array_sites`, `array_schedules`)
- **Options:** `--locationId` (single sector), `--identityId` (post-filter on individual accesses), `--includeExpired`, `--nocsv`, `--debug`
- **Output CSV:** `locationId;locationName;siteId;siteName;accessType;entityId;entityName;scheduleMapId;scheduleName;startDateTimeUtc;endDateTimeUtc;description;approvedById;approvedDateTimeUtc;approverPrincipalType`
- **Note:** uses `$url_locationservice` directly (no separate `accessapi` host)

---

## Shared Libraries

| File | Functions |
|------|-----------|
| `api_url.inc.php` | Environment endpoints, OAuth2 credentials (demo: `x9qxn4iaq2`; prod: `j3gg5ror3f`) |
| `api_functions.inc.php` | `get_token()`, `get_sites()`, `get_locations()`, `get_locations_from_site()`, `get_schedules_from_location()`, `get_identity()`, `get_identity_by_externalid()`, `import_identity()` |
| `functions.inc.php` / `common.inc.php` | `var_dump_ret()`, `array_recursive_search_key_map()`, `array_get_nested_value()`, `echo_flush()` |

---

## Environment Matrix

| Service | Demo | Production |
|---------|------|------------|
| STS | `sts-demo.clearid.io` | `sts.eu.clearid.io` |
| Identity | `identityservice-demo.clearid.io` | `identityservice.eu.clearid.io` |
| Location | `locationservice-demo.clearid.io` | `locationservice.eu.clearid.io` |
| Site | `siteservice-demo.clearid.io` | `siteservice.eu.clearid.io` |
| Principal | `principalservice-demo.clearid.io` | `principalservice.eu.clearid.io` |
| Access API v1 | `accessapi.demo.clearid.io` (unconfirmed) | `accessapi.eu.clearid.io` (to verify) |
| Account ID | `x9qxn4iaq2` | `j3gg5ror3f` |

---

## Data Cache Files (Generated)

| File | Size (prod) | Purpose |
|------|-------------|---------|
| `array_identities_prod.inc.php` | ~14 MB | All identity mappings with costCenter/email |
| `array_identities_demo.inc.php` | ~1.2 MB | Demo identity mappings |
| `array_sites_prod.inc.php` | ~7 KB | Site ID → name mapping |
| `array_locations_prod.inc.php` | ~64 KB | Location ID → name mapping |
| `array_locations_sites_prod.inc.php` | ~51 KB | Location ID → site ID mapping |
| `array_schedules_prod.inc.php` | ~8 KB | Schedule ID → name mapping |

---

## Operational Workflow

### Access Rights Import (typical run)

```
1. Update caches:
   php.exe ListSitesLocationsSchedulesClearId.php --nocsv
   php.exe ListIdentiesClearId.php --nocsv

2. Prepare CSV:
   identityIds;locationId;scheduleId;startDateTimeUtc;endDateTimeUtc;description
   UTF-8 with BOM, semicolons

3. Rename to ImportHoraires{SITE}{YYYYMMDD}_v{N}.csv → PHP\data\

4. In ClearID UI: disable end-date limit on target site

5. Run:
   php.exe ImportIdentitiesAccessClearId.php .\data\ImportHorairesXXX.csv

6. Re-enable end-date limit in ClearID UI
```

### Contractor Import (typical run)

```
1. Ensure array_identities_prod.inc.php is current
2. Prepare ImportPrestatairesYYYYMMDD.csv (UTF-8 BOM, semicolons)
3. Run:
   php.exe ImportPrestatairesClearId.php .\data\ImportPrestatairesYYYYMMDD.csv
```

---

## Error Handling

- **Curl errors**: fatal — script dies after logging
- **Token errors**: fatal — checked via `error` key in JSON response
- **Date validation**: non-fatal — skipped with counter increment
- **Missing identity**: non-fatal — skipped with counter increment
- **API HTTP non-2xx**: fatal — script dies after logging statusCode + response body

---

## Related Documents

- `Interface SMI-ClearID (droits d'acces) v1.0 20260608.docx` — operational procedure document
- `erreur clearid import id.txt` — known error notes
- [[clearid-smi-droits-acces-scripts]] — full script inventory and source page
- [[clearid-directory]] — parent directory with all interfaces
- [[ClearID - Site and Location Reference]] — site/location/schedule inventory (106 sites, 305 locations, 93 schedules)