---
type: source
title: "ClearID SMI Droits d'Acces Scripts" 
date: 2026-06-09
updated: 2026-06-09
source_path: ".raw/clearid-smi-droits-acces/"
tags:
  - source
  - clearid
  - smi
  - php
  - integration
  - api
  - genetec
status: current
related:
  - "[[ClearID - SMI Interface]]"
  - "[[ClearID Integration Architecture]]"
  - "[[clearid-directory]]"
  - "[[clearid-access-api-swagger]]"
  - "[[ClearID - Site and Location Reference]]"
---

# ClearID SMI Droits d'Acces Scripts

PHP 8.2 integration suite for syncing access rights (schedules, locations, identities) between Hermes SMI and Genetec ClearID. Hosted on `frsellpappepa02.atlas.hermes/clearid/accctrl/` (Apache + Linux) and run locally via CLI (`C:\exe\php-8.2.13-Win32-vs16-x64\php.exe`).

Source: `\\cifs-frsel\etudes\Applications Départementales\PEPS France\07 - Doc DSI\ClearID (interface, plugins)\Interface SMI-ClearID (droits d'acces)`

---

## Inventory

### Core Config

| File | Purpose |
|------|---------|
| `api_url.inc.php` | Environment-specific endpoints (demo / prod) and OAuth2 `client_credentials` parameters. Account IDs: `x9qxn4iaq2` (demo), `j3gg5ror3f` (prod). STS for demo: `sts-demo.clearid.io`; prod: `sts.eu.clearid.io`. |
| `functions.inc.php` | Shared utilities: `var_dump_ret()`, `array_recursive_search_key_map()`, `array_get_nested_value()`, `echo_flush()`. |
| `api_functions.inc.php` | Reusable API wrappers: `get_token()`, `get_sites()`, `get_locations()`, `get_locations_from_site()`, `get_schedules_from_location()`, `get_identity()`, `get_identity_by_externalid()`, `import_identity()` (for external contractors). |
| `common.inc.php` | Same as `functions.inc.php` (redundant copy). |

### Scripts (listed by operation)

#### 1. ListIdentiesClearId.php
CLI/web dual-purpose. Paginates all identities via `GET /api/v3/accounts/{account_id}/identities?continuation={token}`.

- Supports `--noarray`, `--nocsv`, `--fastlog`
- Generates `array_identities_{ENV}.inc.php` (PHP cache dump of all identities)
- Generates CSV: `identityId;firstName;lastName;companyName;departmentName;externalId;status`
- Also saves `costCenter` from custom fields and `email` into the array dump
- Token refresh every 2,000 identities

#### 2. ListIdentityPrincipalsClearId.php
Lists principal state per identity (roles + principalState). Checks `status != Active` and `isDeleted == true`. Calls `GET /api/v3/accounts/{account_id}/identityPrincipals/{identityId}`.

#### 3. ListIdentityDeletedClearId.php
Lists only identities with `isDeleted == true` by paginating through all identities.

#### 4. ListIdentityInactiveClearId.php
Lists inactive or deleted identities.

#### 5. ListIdentityNoPrincipalsClearId.php
Lists identities that have no associated principal (access rights not configured).

#### 6. ListIdentiesNoSiteIdClearId.php
Lists identities missing a `siteId` assignment.

#### 7. ListSitesClearId.php
Lists all sites from `GET /api/v2/accounts/{account_id}/sites`.

#### 8. ListLocationsClearId.php
Lists all locations via `GET /api/v3/accounts/{account_id}/locations?Take=1000`.

#### 9. ListSitesLocationsSchedulesClearId.php
Master reference builder. Options: `--noarray`, `--nocsv`.

- Fetches all sites → per site, fetches locations → per location, fetches schedules
- Generates:
  - `array_sites_{ENV}.inc.php`
  - `array_locations_{ENV}.inc.php`
  - `array_locations_sites_{ENV}.inc.php`
  - `array_schedules_{ENV}.inc.php`
- Generates CSV: `siteId;siteName;siteIsDeleted;locationId;locationName;locationVisibility;scheduleId;scheduleName;scheduleState`

#### 10. ImportIdentitiesAccessClearId.php
Main access-rights import script. Takes a CSV path as CLI argument.

- **Prerequisite**: array files must be up-to-date (run `ListIdentiesClearId.php` + `ListSitesLocationsSchedulesClearId.php` first)
- **CSV format**: UTF-8 with BOM, semicolon separator, headers: `identityIds;locationId;scheduleId;startDateTimeUtc;endDateTimeUtc;description`
- **Date conversion**: `d/m/Y H:i` → ISO 8601 UTC (`Y-m-d\TH:i:s\Z`)
- Calls `PATCH /api/v3/accounts/{account_id}/locations/{locationId}/accesses`
- Validates: date parsing, chronological order, identity existence in local array
- Error counters: date errors, id errors, API errors
- Token refresh every 2,000 lines

#### 11. ImportLocationsSchedulesClearId.php
Associates schedules to locations in bulk via `PATCH /api/v3/accounts/{account_id}/locations/{locationId}/schedules`. Options: `--nocache`, `--genidarray`. CSV format: UTF-8 with BOM, semicolons.

#### 12. ImportPrestatairesClearId.php
Imports external contractors (prestataires/SST). Takes a CSV path as argument.

- **CSV header**: `WorkerType;Company;Manager;Department;LocationCode;Worklocation;Division;FirstName;LastName;MissionTitle;Arrival;Departure;SitePrincipal;ExternalCompany`
- Builds `externalId` as `SST-{md5(...)}` (deterministic dedup key)
- Looks up manager in local `$array_identities` by display name to get `identityId`, `externalId`, `costCenter`, `email`
- Calls `POST /api/v3/accounts/{account_id}/identities` with a full JSON payload including `customFields` (`start_date`, `end_date`, `contract_type=SST`, `external_company`, `cost_center`, etc.)
- Fixed `siteId`: `72b455d1-5e21-46a1-afef-390b2f95b67d` (hardcoded primary site)
- Skips rows where `WorkerType == 'Worker type'` (header)

#### 13. SynchronizeIdentitiesClearId.php
Batch-synchronizes all identities. Paginates through identities (same as ListIdenties), builds comma-separated `identityIds` arrays, then POST to `/api/v3/accounts/{account_id}/identities/synchronize`.

- Excludes two hardcoded identity IDs from batches
- Token refresh every 2,000 identities

#### 14. AddMissingIdentityPrincipalsClearId.php
Adds missing principal records for identities that lack one. Complements `ListIdentityNoPrincipalsClearId.php`.

#### 15. ListAccessesByLocationClearId.php *(new, Location Service v3)*
Lists all accesses ("propriétaires") per location/secteur using the **Location Service v3**.

- **Endpoint:** `GET /api/v3/accounts/{account_id}/locations/{locationId}/accesses[?includeExpired=true]`
- **Response:** `AccessesModel` with:
  - `accessModels`: dictionary keyed by `identityId` → array of `AccessModel` (individual accesses)
  - `teamAccessModels`: dictionary keyed by `teamId` → array of `AccessModel` (team/role accesses)
- **Host:** `locationservice.eu.clearid.io` (no dedicated `accessapi` host; Access API v1 is not deployed on Hermes tenant)
- **Prerequisites:** `array_identities_{ENV}.inc.php`, `array_locations_{ENV}.inc.php`, `array_locations_sites_{ENV}.inc.php`, `array_sites_{ENV}.inc.php`, `array_schedules_{ENV}.inc.php`
- **Options:** `--locationId={uuid}` (single sector), `--identityId={uuid}` (post-filter, identities only), `--includeExpired`, `--nocsv`, `--debug`
- **Output CSV:** `locationId;locationName;siteId;siteName;accessType;entityId;entityName;scheduleMapId;scheduleName;startDateTimeUtc;endDateTimeUtc;description;approvedById;approvedDateTimeUtc;approverPrincipalType`
- **Note:** uses `$url_locationservice` as base URL (the Access API v1 spec is NOT deployed on Hermes; the endpoint is on the existing Location Service)

---

## Data Files (generated caches)

| File | Content | Size (prod approx) |
|------|---------|-------------------|
| `array_identities_prod.inc.php` | All identities with custom fields | ~14 MB |
| `array_identities_demo.inc.php` | Demo identities | ~1.2 MB |
| `array_sites_prod.inc.php` | Site mapping | ~7 KB |
| `array_locations_prod.inc.php` | Location mapping | ~64 KB |
| `array_locations_sites_prod.inc.php` | Location → site mapping | ~51 KB |
| `array_schedules_prod.inc.php` | Schedule mapping | ~8 KB |

---

## Operational Procedures

### Importing access rights (ImportIdentitiesAccessClearId.php)

1. In ClearID admin UI: disable end-date limit (`Organisation > Site > configuration de l'acces > pas de date de fin`) — **re-enable after import**
2. Ensure CSV has headers: `identityIds;locationId;scheduleId;startDateTimeUtc;endDateTimeUtc;description`
3. Save CSV as UTF-8 with BOM, semicolon separator
4. Rename to `ImportHoraires{SITE}{YYYYMMDD}_v{N}.csv` and place in `PHP\data\`
5. Update caches: `php.exe ListSitesLocationsSchedulesClearId.php --nocsv` and `php.exe ListIdentiesClearId.php --nocsv`
6. Run: `php.exe ImportIdentitiesAccessClearId.php .\data\ImportHorairesXXX.csv`

### Importing locations/schedules (ImportLocationsSchedulesClearId.php)

1. Update caches: `php.exe ListSitesLocationsSchedulesClearId.php --nocsv`
2. Prepare `ImportSecteursHorairesXXX.csv` in UTF-8 BOM, semicolons
3. Run: `php.exe ImportLocationsSchedulesClearId.php .\data\ImportSecteursHorairesXXX.csv`

### Importing contractors (ImportPrestatairesClearId.php)

1. Prepare `ImportPrestatairesYYYYMMDD.csv` in UTF-8 BOM, semicolons
2. Ensure `array_identities_prod.inc.php` is current
3. Run: `php.exe ImportPrestatairesClearId.php .\data\ImportPrestatairesYYYYMMDD.csv`

---

## Error Handling Patterns

Common script behaviors:

- **Curl errors**: logged to debug file + echoed; script dies on fatal curl errors
- **Token errors**: checked by looking for `error` key in token JSON response; script dies
- **Date errors**: invalid format or start > end; logged and skipped with counter increment
- **Missing identity**: if `identityId` not found in local array; logged and skipped
- **API HTTP errors**: non-2xx response from ClearID; logged with statusCode + response body; script dies

Known issue noted in `erreur clearid import id.txt` (to be read separately if needed).

---

## Environment Matrix

| | Demo | Prod |
|--|------|------|
| STS | `sts-demo.clearid.io` | `sts.eu.clearid.io` |
| Identity | `identityservice-demo.clearid.io` | `identityservice.eu.clearid.io` |
| Location | `locationservice-demo.clearid.io` | `locationservice.eu.clearid.io` |
| Site | `siteservice-demo.clearid.io` | `siteservice.eu.clearid.io` |
| Principal | `principalservice-demo.clearid.io` | `principalservice.eu.clearid.io` |
| Schedule (implicit) | via Location Service | via Location Service |
| Account ID | `x9qxn4iaq2` | `j3gg5ror3f` |

---

## Log & Output Structure

All scripts write to:
- `PHP\logs\{ScriptName}_{ENV}_{YYYYMMDD}_{HHMMSS}.log` — full debug log
- `PHP\csv\{ScriptName}_{ENV}_{YYYYMMDD}_{HHMMSS}.csv` — CSV export (when applicable)
- Web access: scripts are served by Apache on `frsellpappepa02` under `/clearid/accctrl/`

---

## Source Files

Raw scripts stored in `.raw/clearid-smi-droits-acces/PHP/`:
- `api_url.inc.php`
- `api_functions.inc.php`
- `functions.inc.php`
- `ListIdentiesClearId.php`
- `ListIdentityPrincipalsClearId.php`
- `ListIdentityDeletedClearId.php`
- `ListIdentityInactiveClearId.php`
- `ListIdentityNoPrincipalsClearId.php`
- `ListIdentiesNoSiteIdClearId.php`
- `ListSitesClearId.php`
- `ListLocationsClearId.php`
- `ListSitesLocationsSchedulesClearId.php`
- `ImportIdentitiesAccessClearId.php`
- `ImportLocationsSchedulesClearId.php`
- `ImportPrestatairesClearId.php`
- `SynchronizeIdentitiesClearId.php`
- `AddMissingIdentityPrincipalsClearId.php`
- `erreur clearid import id.txt`