---
type: source
title: "ClearID Directory"
ingested: 2026-06-03
source_path: "\\\\cifs-frsel\\etudes\\Applications Départementales\\PEPS France\\07 - Doc DSI\\ClearID (interface, plugins)"
raw_file: ".raw/clearid-directory.md"
tags:
  - source
  - clearid
  - hermes
  - genetec
  - access-control
status: current
related:
  - "[[ClearID]]"
  - "[[ClearID - MCH Interface]]"
  - "[[ClearID - SMI Interface]]"
  - "[[ClearID - IAM Interface]]"
  - "[[ClearID - Symfony API]]"
  - "[[ClearID Integration Architecture]]"
  - "[[Hermes International]]"
---

# ClearID Directory

**Ingested:** 2026-06-03 | **Files:** ~2,500 (non-vendor) | **Path:** `\\cifs-frsel\etudes\...\ClearID (interface, plugins)`

---

## What This Is

The complete Hermes integration codebase for [Genetec ClearID](https://www.genetec.com/solutions/physical-identity-access-management), a physical access control and identity management platform. Contains PHP integration scripts, a Symfony API, deployment artifacts, and documentation for connecting Hermes internal systems (MCH, SMI, IAM) to ClearID.

---

## Directory Contents

| Directory | Files | Purpose |
|-----------|-------|---------|
| `Symfony/clearidapi` | 1,013 | Symfony 5.4 API application (development) |
| `linux` | 672 | Linux deployment scripts + PHP cron jobs |
| `Interface SMI-ClearID (droits d'acces)` | 329 | Access rights sync SMI ↔ ClearID |
| `Interface ClearID-SMI (utilisateurs)` | 191 | User export ClearID → SMI |
| `Interface MCH-CleadID (photo)` | 100 | Photo sync MCH → ClearID |
| `Interface MCH-CleadID (dates contrat)` | 49 | Contract dates sync MCH → ClearID |
| `Interface IAM-ClearID (identity)` | 48 | IAM identity mapping |
| `php-8.2.13-Win32-vs16-x64` | 81 | Windows PHP runtime binaries |
| `Plugin ClearID pour Outlook par AddonXpert` | 2 | Outlook plugin by AddonXpert |

---

## Key Wiki Pages

- [[ClearID]] — main entity overview
- [[ClearID - MCH Interface]] — contract dates + photo synchronization
- [[ClearID - SMI Interface]] — access rights + user export
- [[ClearID - IAM Interface]] — identity mapping
- [[ClearID - Symfony API]] — Symfony 5.4 API application
- [[ClearID Integration Architecture]] — data flow and architecture

---

## Raw Source

Full inventory and code analysis: `.raw/clearid-directory.md`
