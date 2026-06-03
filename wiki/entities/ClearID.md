---
type: entity
title: "ClearID"
status: current
tags:
  - entity
  - clearid
  - genetec
  - access-control
  - physical-security
  - hermes
related:
  - "[[Hermes International]]"
  - "[[ClearID - MCH Interface]]"
  - "[[ClearID - SMI Interface]]"
  - "[[ClearID - IAM Interface]]"
  - "[[ClearID - Symfony API]]"
  - "[[ClearID Integration Architecture]]"
  - "[[clearid-directory]]"
---

# ClearID

**Vendor:** Genetec | **Product:** Physical Identity & Access Management | **Region:** EU (`*.eu.clearid.io`)

ClearID is the physical access control platform used by Hermes International to manage employee and contractor identities across their physical security infrastructure.

---

## Hermes Account

- **Account ID:** `j3gg5ror3f`
- **Portal:** `https://portal.eu.clearid.io/j3gg5ror3f`
- **Environment:** Production (with demo environment available)

---

## API Services

| Service | Production Endpoint | Purpose |
|---------|---------------------|---------|
| STS | `sts.eu.clearid.io` | OAuth2 token issuance |
| Identity | `identityservice.eu.clearid.io` | CRUD on identities |
| Search | `searchservice.eu.clearid.io` | Identity search |
| Location | `locationservice.eu.clearid.io` | Locations & schedules |
| Site | `siteservice.eu.clearid.io` | Site management |
| Role | `roleservice.eu.clearid.io` | Role management |
| VR | `vrservice.eu.clearid.io` | Visitor registration |
| RPS | `rps.eu.clearid.io` | Registration portal |
| Principal | `principalservice.eu.clearid.io` | Principal management |
| System | `systemservice.eu.clearid.io` | System configuration |

---

## Hermes Integrations

| Integration | Direction | System | Data |
|-------------|-----------|--------|------|
| [[ClearID - MCH Interface\|MCH Contract Dates]] | MCH → ClearID | Oracle HCM | Contract start/end dates, assignment dates |
| [[ClearID - MCH Interface\|MCH Photos]] | MCH → ClearID | Oracle HCM | Employee photos |
| [[ClearID - SMI Interface\|SMI Access Rights]] | SMI ↔ ClearID | Hermes SMI | Identities, locations, schedules, principals |
| [[ClearID - SMI Interface\|SMI User Export]] | ClearID → SMI | Hermes SMI | SST (temporary worker) imports |
| [[ClearID - IAM Interface\|IAM Identity]] | IAM ↔ ClearID | Hermes IAM | Identity mapping, location mapping |
| [[ClearID - Symfony API\|Symfony API]] | — | Custom | Development API layer (Symfony 5.4) |

---

## Authentication

All Hermes integrations use **OAuth2 client_credentials** grant:
- Token endpoint: `POST {sts_url}/connect/token`
- Scope: Full API access via client credentials
- Token refresh: Every 1,000–2,000 identities during bulk operations

**Note:** All PHP scripts disable SSL certificate verification (`CURLOPT_SSL_VERIFYHOST/VERIFYPEER => false`). This is a known configuration in the integration scripts.

---

## Source

- [[clearid-directory]] — full source inventory and code analysis
- `.raw/clearid-directory.md` — immutable raw source
