# Livret Penthievre Source

**Source:** `gitlab.com/hermesintl/hpt/projects/app/source`
**Local:** `~/work/hpt/source`
**Ingested:** 2026-06-03
**Corrected:** 2026-06-03 (repo path fixed from `applications-departementales/livret-penthievre/livret-penthievre-sources`)

---

## Overview

Livret Penthievre is a static website for the Hermes **8 Penthièvre** building in Paris (8 rue de Penthièvre, 75008). It consists of two separate sites:
- **Public** — external visitor landing page
- **Private** — internal building guide with interactive floor plans for employees

**URLs:**
- CHK: `https://chk-penthievre.hermes.com`
- PRD: `https://8penthievre.hermes.com`

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | Vanilla HTML/CSS/JS (no framework) |
| Styling | SCSS (compiled to CSS) |
| Typography | NeutraText (Hermes brand font) |
| Auth | JWT-based OAuth2 (Hermes SSO) |
| Interactions | Custom vanilla JS for SVG floor plan maps |
| Hosting | AWS S3 static hosting |

---

## Directory Structure

```
hpt/source/
├── src/
│   ├── index.html              # Root redirect → Public/index.html
│   ├── favicon.ico
│   ├── robots.txt
│   ├── Public/                 # External visitor site
│   │   ├── index.html          # Landing page (large, ~76K tokens)
│   │   ├── assets/
│   │   │   ├── app.js          # Touch detection, custom cursor
│   │   │   ├── jwt-decode.js   # JWT token decoding
│   │   │   ├── login.js        # OAuth2 login flow
│   │   │   ├── PenthievreAnimLogo.gif
│   │   │   └── noise.png
│   │   └── styles/
│   │       ├── scss/
│   │       │   └── components/ # SCSS partials
│   │       ├── styles.css
│   │       └── typo/           # NeutraText web fonts (TTF, WOFF, WOFF2)
│   └── Private/                # Internal employee portal
│       ├── index.html          # Building guide (very large, ~124K tokens)
│       ├── logo.png
│       ├── assets/
│       │   ├── app.js          # Interactive floor plan logic
│       │   ├── bullet.png/svg
│       │   ├── noise.png
│       │   ├── plan.jpg        # Building photo
│       │   └── *.svg           # Floor plan SVGs (11 floors)
│       └── styles/
│           ├── scss/
│           │   └── components/ # SCSS partials
│           ├── styles.css
│           └── typo/           # NeutraText web fonts
├── .gitlab-ci.yml              # GitLab CI/CD
├── gitlab-ci-custom.yml        # Custom S3 sync overrides
├── CHANGELOG.md                # Conventional commits changelog (both repos)
├── README.md
├── .eslintrc.json
└── sonar-project.properties.tpl
```

---

## Public Site (External)

**Path:** `src/Public/`
**Purpose:** Landing page for visitors of 8 Penthièvre

**Features:**
- Animated logo (GIF)
- Custom cursor with smooth follow animation (desktop only)
- Touch detection for mobile adaptation
- JWT-based login via Hermes OAuth2 (`login.js`, `jwt-decode.js`)
- Noise texture background
- Hermes brand typography (NeutraText)

**Auth Flow (login.js):**
- Redirects to Hermes OAuth2 authorization endpoint
- Receives authorization code
- Exchanges code for tokens
- Stores JWT in sessionStorage
- Redirects back to app with token

---

## Private Site (Internal)

**Path:** `src/Private/`
**Purpose:** Internal building guide for Hermes employees

**Sections:**
- Le 8 Penthièvre (building overview)
- Art de Vivre (lifestyle/amenities)
- Informations pratiques (practical info)
- Interactive floor plan map

**Interactive Floor Plans:**
- 11 SVG floor plans: RDC, R1, R2, R3, R5, R6, R7, R8, R9, SS1, SS2
- Custom cursor with zone highlighting
- Level navigation (prev/next floor buttons)
- Zone info popups on hover/click
- Mobile selector dropdown
- Touch screen support

**SCSS Components:**
- `_bg-visuals.scss` — background textures/noise
- `_global.scss` — global styles
- `_header.scss` — navigation/header
- `_info-blocks.scss` — content panels
- `_map.scss` — interactive floor plan styles
- `_styleguide.scss` — design system
- `_utils.scss` — utilities
- `_variables.scss` — SCSS variables

---

## CI/CD (GitLab)

**Template Library:** Same Hermes `template-cicd` as HB8/KLM/CNF

**Base templates:**
- `baseline-component` — SonarQube analysis (language: unset, tool: unset)
- `s3-push-files@feat/jfrog-migration` — S3 deployment for CHK and PRD

**S3 Buckets:**
- CHK: `s3://hsi-penthievre-chk-frontend/`
- PRD: `s3://hsi-penthievre-prd-frontend/`

**Custom Overrides** (`gitlab-ci-custom.yml`):
- `custom_s3-upload-content:chk` — `aws s3 sync src/ $S3_BUCKET --delete` on merge requests
- `custom_s3-upload-content:prd` — manual trigger on git tag
- `custom_list-s3-content:chk/prd` — manual S3 listing (on demand)

**Variables:**
- `PUBLISH_REPOSITORY: artifactory`
- `GITGUARDIAN_FULL_SCAN: true`
- `DEV_ENABLED: false`, `TST_ENABLED: false`, `ACC_ENABLED: false`
- `CHK_ENABLED: true`

**Security:**
- Vault secrets for AWS credentials
- OIDC token-based Vault auth
- Assume role pattern
- SonarQube quality gate
- Gitleaks secret scanning

---

## Auth Architecture

**Protocol:** OAuth2 Authorization Code + JWT
**Identity Provider:** Hermes SSO

**Flow:**
1. User clicks login → redirect to Hermes OAuth2 `/authorize`
2. User authenticates → redirect back with `?code=`
3. App exchanges code for tokens via `POST /token`
4. JWT stored in sessionStorage
5. User info decoded and displayed

---

## Version History

**Current repo** (`hpt/projects/app/source`):
- `1.1.2` (2026-05-21) — login update
- `1.1.1` (2026-05-21) — CI custom prod fix
- `1.1.0` (2026-05-20) — PRD environment added
- `1.0.0` (2026-04-28) — init + custom CI

**Previous repo** (`applications-departementales/livret-penthievre/livret-penthievre-sources`):
- `1.3.10` (2024-05-29) — CI updates
- `1.2.0` (2021-03-19) — livraison intermédiaire
- `1.1.0` (2021-03-19) — livraison initiale
- `1.0.0` (2021-03-19) — initial release
