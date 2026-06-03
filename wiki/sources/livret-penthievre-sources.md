---
type: source
title: "Livret Penthievre Sources"
ingested: 2026-06-03
corrected: 2026-06-03
source_path: "gitlab.com/hermesintl/hpt/projects/app/source"
raw_file: ".raw/livret-penthievre-sources.md"
tags:
  - source
  - hermes
  - static-site
  - s3
  - html
status: current
related:
  - "[[8 Penthièvre]]"
  - "[[Hermes International]]"
  - "[[hb8-app-repo]]"
  - "[[klm-repo]]"
  - "[[cnf-repo]]"
---

# Livret Penthievre Sources

**Ingested:** 2026-06-03 | **Corrected:** 2026-06-03 | **Type:** Static HTML site | **GitLab:** `hermesintl/hpt/projects/app/source`

---

## What This Is

Source code for the [[8 Penthièvre]] building website — a dual static site (public landing page + private employee portal with interactive floor plans) hosted on AWS S3.

---

## Key Facts

| Property | Value |
|----------|-------|
| Type | Vanilla HTML/CSS/JS (no framework) |
| Hosting | AWS S3 static |
| Auth | OAuth2 JWT via Hermes SSO |
| CI/CD | GitLab CI with Hermes template-cicd + custom S3 sync |
| Typography | NeutraText (Hermes brand) |
| Environments | CHK, PRD |
| Deploy | `aws s3 sync src/` |

---

## CI/CD Details

**Custom overrides** in `gitlab-ci-custom.yml`:
- CHK deploy: `aws s3 sync src/ $S3_BUCKET --delete` on merge requests
- PRD deploy: manual trigger on git tag
- S3 buckets: `hsi-penthievre-chk-frontend` / `hsi-penthievre-prd-frontend`

---

## Wiki Pages

- [[8 Penthièvre]] — main entity

---

## Raw Source

Full analysis: `.raw/livret-penthievre-sources.md`
