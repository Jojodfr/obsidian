---
type: entity
title: "8 Penthièvre"
status: current
tags:
  - entity
  - hermes
  - static-site
  - s3
  - paris
  - building
related:
  - "[[Hermes International]]"
  - "[[livret-penthievre-sources]]"
  - "[[hb8-app-repo]]"
  - "[[klm-repo]]"
  - "[[cnf-repo]]"
---

# 8 Penthièvre

**Address:** 8 rue de Penthièvre, 75008 Paris | **Type:** Hermes office building
**Website:** `https://8penthievre.hermes.com`

---

## Website

The building has a dual static website managed by the [[livret-penthievre-sources]] repository.

### Public Site (`/Public`)
- External visitor landing page
- Animated logo, custom cursor
- JWT-based login via Hermes OAuth2

### Private Site (`/Private`)
- Internal employee building guide
- Interactive SVG floor plans (11 floors)
- Sections: Le 8 Penthièvre, Art de Vivre, Informations pratiques

### Floor Plans
| Floor | Code | Description |
|-------|------|-------------|
| RDC | Ground floor | Rez-de-chaussée |
| R1 | Floor 1 | 1er étage |
| R2 | Floor 2 | 2ème étage |
| R3 | Floor 3 | 3ème étage |
| R5 | Floor 5 | 5ème étage |
| R6 | Floor 6 | 6ème étage |
| R7 | Floor 7 | 7ème étage |
| R8 | Floor 8 | 8ème étage |
| R9 | Floor 9 | 9ème étage |
| SS1 | Sub-basement 1 | Sous-sol 1 |
| SS2 | Sub-basement 2 | Sous-sol 2 |

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | Vanilla HTML/CSS/JS |
| Styling | SCSS |
| Fonts | NeutraText (Hermes brand) |
| Auth | OAuth2 JWT (Hermes SSO) |
| Hosting | AWS S3 static |
| CI/CD | GitLab CI with Hermes template-cicd |

---

## CI/CD

Uses the same Hermes CI/CD template library as [[hb8-app-repo]], [[klm-repo]], and [[cnf-repo]]:
- SonarQube + Gitleaks for security
- Vault for AWS secrets
- S3 sync deployment via custom overrides (`gitlab-ci-custom.yml`)
- Environments: CHK (auto on MR) and PRD (manual on tag)
- S3 buckets: `hsi-penthievre-chk-frontend` / `hsi-penthievre-prd-frontend`

---

## Source

- [[livret-penthievre-sources]] — source page
- `.raw/livret-penthievre-sources.md` — raw source
